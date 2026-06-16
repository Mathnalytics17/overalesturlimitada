<?php

namespace app\Services\Package;

use app\Core\CustomerAuth;
use app\Models\CustomerPackageEvent;
use app\Models\CustomerPackageFavorite;
use app\Models\CustomerPackageNotification;
use app\Models\CustomerTravelPreference;
use app\Models\Lead;
use app\Models\TourPackage;
use app\Models\TourPackageTag;
use app\Models\TourPackageTagItem;

class CustomerPackageService
{
    public function currentCustomerId(): ?int
    {
        $account = CustomerAuth::user();
        return $account ? (int) $account->customer_id : null;
    }

    public function toggleFavorite(int $customerId, int $packageId): bool
    {
        $favorite = CustomerPackageFavorite::findForCustomerAndPackage($customerId, $packageId);

        if ($favorite) {
            $favorite->delete();
            $this->recordEvent($packageId, 'favorite_removed', $customerId);
            return false;
        }

        CustomerPackageFavorite::create([
            'customer_id' => $customerId,
            'tour_package_id' => $packageId,
        ]);

        $this->recordEvent($packageId, 'favorite_added', $customerId);
        return true;
    }

    public function isFavorite(?int $customerId, int $packageId): bool
    {
        return $customerId !== null
            && CustomerPackageFavorite::findForCustomerAndPackage($customerId, $packageId) !== null;
    }

    public function favoritePackageIds(?int $customerId): array
    {
        return $customerId ? CustomerPackageFavorite::packageIdsByCustomer($customerId) : [];
    }

    public function favoritePackages(int $customerId): array
    {
        $packages = [];

        foreach (CustomerPackageFavorite::byCustomer($customerId) as $favorite) {
            $package = TourPackage::find((int) $favorite->tour_package_id);
            if ($package && (string) $package->status === 'published') {
                $packages[] = $package;
            }
        }

        return $packages;
    }

    public function recordView(TourPackage $package): void
    {
        $this->recordEvent((int) $package->id, 'view', $this->currentCustomerId());
    }

    public function recordInquiry(int $packageId, ?int $customerId): void
    {
        if ($packageId > 0) {
            $this->recordEvent($packageId, 'inquiry', $customerId);
        }
    }

    public function mostVisitedPublished(?string $search = null): array
    {
        $packages = TourPackage::publishedList($search);
        $viewCounts = CustomerPackageEvent::viewCounts();

        usort($packages, static function (TourPackage $a, TourPackage $b) use ($viewCounts): int {
            $aViews = $viewCounts[(int) $a->id] ?? 0;
            $bViews = $viewCounts[(int) $b->id] ?? 0;

            if ($aViews !== $bViews) {
                return $bViews <=> $aViews;
            }

            return (int) ($b->is_featured ?? 0) <=> (int) ($a->is_featured ?? 0);
        });

        return $packages;
    }

    public function recommendations(int $customerId, int $limit = 6, array $excludePackageIds = []): array
    {
        $packages = TourPackage::publishedList();
        $favoriteIds = CustomerPackageFavorite::packageIdsByCustomer($customerId);
        $scores = [];
        $reasons = [];
        $tagWeights = [];
        $preference = CustomerTravelPreference::findByCustomer($customerId);
        $preferredTagIds = [];
        $desiredDestinations = [];

        if ($preference) {
            $selectedSlugs = $preference->preferred_tag_slugs_json ?: [];
            foreach (TourPackageTag::activeList() as $tag) {
                if (in_array((string) $tag->slug, $selectedSlugs, true)) {
                    $preferredTagIds[(int) $tag->id] = true;
                    $tagWeights[(int) $tag->id] = ($tagWeights[(int) $tag->id] ?? 0) + 8;
                }
            }

            $desiredDestinations = array_values(array_filter(array_map(
                fn(string $value): string => $this->normalizeSearchText($value),
                preg_split('/[\r\n,]+/', (string) ($preference->desired_destinations ?? '')) ?: []
            )));
        }

        foreach ($favoriteIds as $packageId) {
            $this->addTagWeights($tagWeights, $packageId, 6);
        }

        foreach (CustomerPackageEvent::byCustomer($customerId, 100) as $event) {
            $packageId = (int) $event->tour_package_id;
            $weight = match ((string) $event->event_type) {
                'inquiry' => 10,
                'favorite_added' => 6,
                'view' => 1,
                default => 0,
            };

            if ($weight > 0) {
                $this->addTagWeights($tagWeights, $packageId, $weight);
            }
        }

        foreach (Lead::byCustomer($customerId, 50) as $lead) {
            if ((string) $lead->source_type === 'package' && !empty($lead->package_id)) {
                $this->addTagWeights($tagWeights, (int) $lead->package_id, 10);
            }
        }

        foreach ($packages as $package) {
            $packageId = (int) $package->id;
            if (in_array($packageId, $favoriteIds, true) || in_array($packageId, $excludePackageIds, true)) {
                continue;
            }

            $score = !empty($package->is_featured) ? 2 : 0;
            $score += !empty($package->is_popular) ? 1 : 0;
            $matchedTags = [];
            $reasonParts = [];

            foreach (TourPackageTagItem::byPackage($packageId) as $tagItem) {
                $tagId = (int) $tagItem->tag_id;
                if (!empty($tagWeights[$tagId])) {
                    $score += $tagWeights[$tagId];
                    $matchedTags[] = $tagId;
                }

                if (!empty($preferredTagIds[$tagId])) {
                    $reasonParts[] = 'coincide con el estilo de viaje que elegiste';
                }
            }

            $haystack = $this->normalizeSearchText((string) ($package->title ?? '') . ' ' . (string) ($package->location_name ?? ''));
            foreach ($desiredDestinations as $destination) {
                if ($destination !== '' && str_contains($haystack, $destination)) {
                    $score += 12;
                    $reasonParts[] = 'incluye uno de tus destinos deseados';
                    break;
                }
            }

            $budgetMax = (float) ($preference->budget_max ?? 0);
            if ($budgetMax > 0 && (float) ($package->price_from ?? 0) <= $budgetMax) {
                $score += 4;
                $reasonParts[] = 'encaja con tu presupuesto máximo';
            }

            $scores[$packageId] = $score;
            $reasons[$packageId] = $reasonParts !== []
                ? ucfirst(implode(', ', array_values(array_unique($reasonParts)))) . '.'
                : ($matchedTags !== []
                    ? 'Coincide con destinos y estilos de viaje que te interesaron.'
                    : 'Es uno de nuestros paquetes destacados.');
        }

        usort($packages, static function (TourPackage $a, TourPackage $b) use ($scores): int {
            return ($scores[(int) $b->id] ?? 0) <=> ($scores[(int) $a->id] ?? 0);
        });

        $result = [];
        foreach ($packages as $package) {
            $packageId = (int) $package->id;
            if (in_array($packageId, $favoriteIds, true) || in_array($packageId, $excludePackageIds, true)) {
                continue;
            }

            $package->recommendation_reason = $reasons[$packageId] ?? 'Explora una alternativa para tu próximo viaje.';
            $result[] = $package;

            if (count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }

    public function clearPersonalization(int $customerId): void
    {
        CustomerPackageFavorite::deleteByCustomer($customerId);
        CustomerPackageEvent::deleteByCustomer($customerId);
        CustomerTravelPreference::deleteByCustomer($customerId);
        CustomerPackageNotification::deleteByCustomer($customerId);
    }

    protected function addTagWeights(array &$tagWeights, int $packageId, int $weight): void
    {
        foreach (TourPackageTagItem::byPackage($packageId) as $tagItem) {
            $tagId = (int) $tagItem->tag_id;
            $tagWeights[$tagId] = ($tagWeights[$tagId] ?? 0) + $weight;
        }
    }

    protected function normalizeSearchText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $ascii !== false ? strtolower($ascii) : $value;
    }

    protected function recordEvent(int $packageId, string $eventType, ?int $customerId): void
    {
        secure_session_start();

        CustomerPackageEvent::create([
            'customer_id' => $customerId,
            'tour_package_id' => $packageId,
            'event_type' => $eventType,
            'visitor_key' => hash('sha256', session_id()),
            'metadata_json' => null,
        ]);
    }
}
