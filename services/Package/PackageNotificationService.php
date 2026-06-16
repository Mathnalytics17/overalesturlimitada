<?php

namespace app\Services\Package;

use app\Models\Customer;
use app\Models\CustomerAccount;
use app\Models\CustomerPackageNotification;
use app\Models\CustomerTravelPreference;
use app\Models\TourPackage;
use app\Models\TourPackageTag;
use app\Models\TourPackageTagItem;
use app\Services\Mail\CustomerMailService;

class PackageNotificationService
{
    /**
     * Encola los avisos asociados a la publicación de un paquete.
     *
     * - new_package: clientes que pidieron avisos de paquetes nuevos.
     * - tag_match: clientes que pidieron recomendaciones y tienen al menos N etiquetas
     *   de preferencia coincidentes con las etiquetas del paquete.
     */
    public function queuePublishedPackageNotifications(TourPackage $package, ?int $minTagMatches = null): array
    {
        if ((string) $package->status !== 'published') {
            return [
                'new_package' => 0,
                'tag_match' => 0,
                'total' => 0,
            ];
        }

        $minTagMatches = $minTagMatches ?? env_int('PACKAGE_NOTIFICATION_MIN_TAG_MATCHES', 3);
        $minTagMatches = max(1, $minTagMatches);

        $newPackage = $this->queueForSubscribers($package, 'new_package', 'notify_new_packages');
        $tagMatch = $this->queueForTagMatches($package, $minTagMatches);

        return [
            'new_package' => $newPackage,
            'tag_match' => $tagMatch,
            'total' => $newPackage + $tagMatch,
        ];
    }

    public function queueNewPublishedPackage(TourPackage $package): int
    {
        return $this->queuePublishedPackageNotifications($package)['total'];
    }

    public function queueRecommendationsForSubscribers(int $limitPerCustomer = 1): int
    {
        $queued = 0;
        $packageService = new CustomerPackageService();

        foreach (CustomerTravelPreference::subscribedToRecommendations() as $preference) {
            $customerId = (int) $preference->customer_id;
            if (!$this->activeAccountForCustomer($customerId)) {
                continue;
            }

            foreach ($packageService->recommendations($customerId, $limitPerCustomer) as $package) {
                if ($this->queue($customerId, (int) $package->id, 'recommendation', true)) {
                    $queued++;
                }
            }
        }

        return $queued;
    }

    public function processPending(int $limit = 50, bool $dryRun = false): array
    {
        $result = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];

        if (!$dryRun && !env_bool('PACKAGE_NOTIFICATION_SEND_ENABLED', false)) {
            throw new \RuntimeException('Activa PACKAGE_NOTIFICATION_SEND_ENABLED=true antes de enviar correos.');
        }

        foreach (CustomerPackageNotification::pending($limit) as $notification) {
            $result['processed']++;
            $customer = Customer::find((int) $notification->customer_id);
            $account = $this->activeAccountForCustomer((int) $notification->customer_id);
            $package = TourPackage::find((int) $notification->tour_package_id);

            if (!$customer || !$account || !$package || (string) $package->status !== 'published') {
                if (!$dryRun) {
                    $notification->update([
                        'status' => 'skipped',
                        'last_error' => 'Cliente o paquete no disponible para envío.',
                    ]);
                }
                $result['skipped']++;
                continue;
            }

            if ($dryRun) {
                $result['sent']++;
                continue;
            }

            $mailResult = (new CustomerMailService())->sendPackageNotification(
                customer: $customer,
                package: $package,
                type: (string) $notification->notification_type
            );

            if (!empty($mailResult['success'])) {
                $notification->update([
                    'status' => 'sent',
                    'attempts' => (int) $notification->attempts + 1,
                    'last_error' => null,
                    'sent_at' => date('Y-m-d H:i:s'),
                ]);
                $result['sent']++;
                continue;
            }

            $notification->update([
                'status' => 'pending',
                'attempts' => (int) $notification->attempts + 1,
                'last_error' => mb_substr((string) ($mailResult['message'] ?? 'Error de envío.'), 0, 500),
            ]);
            $result['failed']++;
        }

        return $result;
    }

    public function counts(): array
    {
        return CustomerPackageNotification::statusCounts();
    }

    protected function queueForSubscribers(TourPackage $package, string $type, string $preferenceField): int
    {
        $queued = 0;

        foreach (CustomerTravelPreference::subscribedTo($preferenceField) as $preference) {
            $customerId = (int) $preference->customer_id;
            if ($this->activeAccountForCustomer($customerId) && $this->queue($customerId, (int) $package->id, $type, true)) {
                $queued++;
            }
        }

        return $queued;
    }

    protected function queueForTagMatches(TourPackage $package, int $minTagMatches): int
    {
        $packageTagSlugs = $this->tagSlugsForPackage((int) $package->id);
        if ($packageTagSlugs === []) {
            return 0;
        }

        $queued = 0;

        foreach (CustomerTravelPreference::subscribedToRecommendations() as $preference) {
            $customerId = (int) $preference->customer_id;
            if (!$this->activeAccountForCustomer($customerId)) {
                continue;
            }

            $preferredSlugs = $this->normalizeSlugList($preference->preferred_tag_slugs_json ?: []);
            if ($preferredSlugs === []) {
                continue;
            }

            $matches = array_intersect($packageTagSlugs, $preferredSlugs);
            if (count($matches) < $minTagMatches) {
                continue;
            }

            if ($this->queue($customerId, (int) $package->id, 'tag_match', true)) {
                $queued++;
            }
        }

        return $queued;
    }

    protected function queue(int $customerId, int $packageId, string $type, bool $avoidAnyDuplicateForPackage = false): bool
    {
        if ($avoidAnyDuplicateForPackage && CustomerPackageNotification::findExistingForPackage($customerId, $packageId)) {
            return false;
        }

        if (CustomerPackageNotification::findExisting($customerId, $packageId, $type)) {
            return false;
        }

        return CustomerPackageNotification::create([
            'customer_id' => $customerId,
            'tour_package_id' => $packageId,
            'notification_type' => $type,
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => null,
            'queued_at' => date('Y-m-d H:i:s'),
            'sent_at' => null,
        ]) !== null;
    }

    protected function activeAccountForCustomer(int $customerId): ?CustomerAccount
    {
        $row = CustomerAccount::query()
            ->where('customer_id', '=', $customerId)
            ->where('status', '=', 'active')
            ->whereNotNull('email_verified_at')
            ->first();

        return $row ? new CustomerAccount($row) : null;
    }

    protected function tagSlugsForPackage(int $packageId): array
    {
        $slugs = [];
        foreach (TourPackageTagItem::byPackage($packageId) as $tagItem) {
            $tag = TourPackageTag::find((int) $tagItem->tag_id);
            if ($tag && (int) ($tag->is_active ?? 0) === 1 && trim((string) $tag->slug) !== '') {
                $slugs[] = mb_strtolower(trim((string) $tag->slug));
            }
        }

        return array_values(array_unique($slugs));
    }

    protected function normalizeSlugList(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            $slug = mb_strtolower(trim((string) $value));
            if ($slug !== '') {
                $normalized[] = $slug;
            }
        }

        return array_values(array_unique($normalized));
    }
}
