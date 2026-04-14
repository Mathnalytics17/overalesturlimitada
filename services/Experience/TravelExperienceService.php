<?php

namespace app\Services\Experience;

use app\Models\Lead;
use app\Models\SalesOpportunity;
use app\Models\SalesOrder;
use app\Models\TravelExperience;
use app\Models\TravelExperienceImage;
use app\Services\Experience\TravelExperienceImageUploadService;

class TravelExperienceService
{
    public function createFromPublicForm(array $payload, array $files = []): array
    {
        $data = $this->normalize($payload);
        $errors = $this->validatePublic($data);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $errors,
                'old' => $payload,
            ];
        }

        $matches = $this->findRelatedEntities($data);

        $item = TravelExperience::create([
            'uuid' => \uuid(),
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'display_name' => $data['display_name'],
            'title' => $data['title'],
            'story' => $data['story'],
            'rating' => $data['rating'],
            'city_destination' => $data['city_destination'],
            'country_destination' => $data['country_destination'],
            'experience_type' => $data['experience_type'],
            'package_id' => $data['package_id'],
            'package_slug' => $data['package_slug'],
            'extra_service_id' => $data['extra_service_id'],
            'extra_service_slug' => $data['extra_service_slug'],
            'lead_id' => $matches['lead_id'],
            'sales_opportunity_id' => $matches['sales_opportunity_id'],
            'sales_order_id' => $matches['sales_order_id'],
            'status' => 'pending_review',
            'is_featured' => 0,
            'admin_notes' => null,
            'approved_at' => null,
            'approved_by_admin_id' => null,
            'rejected_at' => null,
            'rejected_by_admin_id' => null,
        ]);
        $uploadService = new TravelExperienceImageUploadService();
        $uploadResult = $uploadService->uploadImages((int)$item->id, $files);

if (!empty($uploadResult['paths'])) {
    foreach ($uploadResult['paths'] as $index => $path) {
        TravelExperienceImage::create([
            'uuid' => \uuid(),
            'travel_experience_id' => (int)$item->id,
            'image_path' => $path,
            'sort_order' => $index + 1,
            'is_cover' => $index === 0 ? 1 : 0,
        ]);
    }
}
        if (!$item) {
            return [
                'success' => false,
                'message' => 'No fue posible registrar la experiencia.',
                'errors' => [
                    'system' => ['No fue posible registrar la experiencia.'],
                ],
                'old' => $payload,
            ];
        }

        return [
            'success' => true,
            'message' => 'Tu experiencia fue enviada y quedará pendiente de revisión.',
            'errors' => [],
            'old' => [],
            'item' => $item,
        ];
    }

    public function approve(int $id, ?int $adminId = null): bool
    {
        $item = TravelExperience::find($id);
        if (!$item) {
            return false;
        }

        return $item->update([
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by_admin_id' => $adminId,
            'rejected_at' => null,
            'rejected_by_admin_id' => null,
        ]);
    }

    public function reject(int $id, string $adminNotes = '', ?int $adminId = null): bool
    {
        $item = TravelExperience::find($id);
        if (!$item) {
            return false;
        }

        return $item->update([
            'status' => 'rejected',
            'admin_notes' => trim($adminNotes) !== '' ? trim($adminNotes) : null,
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejected_by_admin_id' => $adminId,
        ]);
    }

    public function updateByAdmin(int $id, array $payload): array
    {
        $item = TravelExperience::find($id);

        if (!$item) {
            return [
                'success' => false,
                'message' => 'Experiencia no encontrada.',
                'errors' => ['experience' => ['Experiencia no encontrada.']],
                'old' => $payload,
            ];
        }

        $data = $this->normalize($payload);
        $errors = $this->validateAdmin($data);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $errors,
                'old' => $payload,
            ];
        }

        $ok = $item->update([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'display_name' => $data['display_name'],
            'title' => $data['title'],
            'story' => $data['story'],
            'rating' => $data['rating'],
            'city_destination' => $data['city_destination'],
            'country_destination' => $data['country_destination'],
            'experience_type' => $data['experience_type'],
            'package_id' => $data['package_id'],
            'package_slug' => $data['package_slug'],
            'extra_service_id' => $data['extra_service_id'],
            'extra_service_slug' => $data['extra_service_slug'],
            'is_featured' => $data['is_featured'],
            'admin_notes' => $data['admin_notes'],
        ]);

        if (!$ok) {
            return [
                'success' => false,
                'message' => 'No fue posible actualizar la experiencia.',
                'errors' => ['system' => ['No fue posible actualizar la experiencia.']],
                'old' => $payload,
            ];
        }

        return [
            'success' => true,
            'message' => 'Experiencia actualizada correctamente.',
            'errors' => [],
            'old' => [],
            'item' => $item,
        ];
    }

    protected function normalize(array $payload): array
    {
        return [
            'customer_name' => trim((string)($payload['customer_name'] ?? '')),
            'customer_email' => trim((string)($payload['customer_email'] ?? '')),
            'customer_phone' => trim((string)($payload['customer_phone'] ?? '')),
            'display_name' => trim((string)($payload['display_name'] ?? '')),
            'title' => trim((string)($payload['title'] ?? '')),
            'story' => trim((string)($payload['story'] ?? '')),
            'rating' => max(1, min(5, (int)($payload['rating'] ?? 5))),
            'city_destination' => trim((string)($payload['city_destination'] ?? '')),
            'country_destination' => trim((string)($payload['country_destination'] ?? '')),
            'experience_type' => trim((string)($payload['experience_type'] ?? 'general')),
            'package_id' => !empty($payload['package_id']) ? (int)$payload['package_id'] : null,
            'package_slug' => trim((string)($payload['package_slug'] ?? '')),
            'extra_service_id' => !empty($payload['extra_service_id']) ? (int)$payload['extra_service_id'] : null,
            'extra_service_slug' => trim((string)($payload['extra_service_slug'] ?? '')),
            'is_featured' => !empty($payload['is_featured']) ? 1 : 0,
            'admin_notes' => trim((string)($payload['admin_notes'] ?? '')) ?: null,
        ];
    }

    protected function validatePublic(array $data): array
    {
        $errors = [];

        if ($data['customer_name'] === '') {
            $errors['customer_name'][] = 'El nombre es obligatorio.';
        }

        if ($data['title'] === '') {
            $errors['title'][] = 'El título es obligatorio.';
        }

        if ($data['story'] === '') {
            $errors['story'][] = 'La experiencia es obligatoria.';
        }

        if ($data['customer_email'] === '' && $data['customer_phone'] === '') {
            $errors['contact'][] = 'Debes indicar al menos correo o teléfono.';
        }

        if ($data['customer_email'] !== '' && !filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'][] = 'El correo no es válido.';
        }

        return $errors;
    }

    protected function validateAdmin(array $data): array
    {
        return $this->validatePublic($data);
    }

    protected function findRelatedEntities(array $data): array
    {
        $leadId = null;
        $salesOpportunityId = null;
        $salesOrderId = null;

        $leads = Lead::recent(500);
        foreach ($leads as $lead) {
            $sameEmail = $data['customer_email'] !== '' && mb_strtolower((string)$lead->email) === mb_strtolower($data['customer_email']);
            $samePhone = $data['customer_phone'] !== '' && trim((string)$lead->phone) === trim($data['customer_phone']);

            if ($sameEmail || $samePhone) {
                $leadId = (int)$lead->id;
                if (!empty($lead->sales_opportunity_id)) {
                    $salesOpportunityId = (int)$lead->sales_opportunity_id;
                }
                break;
            }
        }

        if ($salesOpportunityId === null) {
            $ops = SalesOpportunity::query()->get();
            foreach ($ops as $row) {
                $sameEmail = $data['customer_email'] !== '' && mb_strtolower((string)($row['customer_email'] ?? '')) === mb_strtolower($data['customer_email']);
                $samePhone = $data['customer_phone'] !== '' && trim((string)($row['customer_phone'] ?? '')) === trim($data['customer_phone']);

                if ($sameEmail || $samePhone) {
                    $salesOpportunityId = (int)($row['id'] ?? 0);
                    break;
                }
            }
        }

        if ($salesOpportunityId !== null) {
            $orders = SalesOrder::adminList();
            foreach ($orders as $order) {
                if ((int)($order->sales_opportunity_id ?? 0) === $salesOpportunityId) {
                    $salesOrderId = (int)$order->id;
                    break;
                }
            }
        }

        return [
            'lead_id' => $leadId,
            'sales_opportunity_id' => $salesOpportunityId,
            'sales_order_id' => $salesOrderId,
        ];
    }
}