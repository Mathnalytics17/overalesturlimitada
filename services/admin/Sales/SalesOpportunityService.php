<?php

namespace app\Services\Admin\Sales;

use app\Models\Lead;
use app\Models\SalesOpportunity;
use app\Models\SalesOpportunityEvent;

class SalesOpportunityService
{
    protected function lostReasonLabel(string $code): string
    {
        return match ($code) {
            'no_response' => 'No respondió',
            'no_budget' => 'Sin presupuesto',
            'better_price' => 'Encontró mejor precio',
            'postponed_trip' => 'Aplazó el viaje',
            'no_availability' => 'Sin disponibilidad',
            'documents_issue' => 'Problema de documentación',
            'lost_interest' => 'Perdió interés',
            'bought_elsewhere' => 'Compró en otra parte',
            'not_qualified' => 'No califica',
            'other' => 'Otro',
            default => 'Otro',
        };
    }

    public function createFromLead(int $leadId, ?int $adminId = null): array
    {
        $lead = Lead::find($leadId);

        if (!$lead) {
            return [
                'success' => false,
                'message' => 'Lead no encontrado.',
                'errors' => ['lead' => ['Lead no encontrado.']],
            ];
        }

        if (!empty($lead->sales_opportunity_id)) {
            $linkedOpportunity = SalesOpportunity::find((int) $lead->sales_opportunity_id);

            if ($linkedOpportunity) {
                return [
                    'success' => false,
                    'message' => 'Este lead ya tiene una oportunidad creada.',
                    'errors' => ['lead' => ['Este lead ya tiene una oportunidad creada.']],
                    'opportunity' => $linkedOpportunity,
                ];
            }
        }

        $existing = SalesOpportunity::findByLeadId($leadId);
        if ($existing) {
            if ((int) ($lead->sales_opportunity_id ?? 0) !== (int) $existing->id) {
                $lead->update([
                    'sales_opportunity_id' => (int) $existing->id,
                ]);
            }

            return [
                'success' => false,
                'message' => 'Este lead ya tiene una oportunidad creada.',
                'errors' => ['lead' => ['Este lead ya tiene una oportunidad creada.']],
                'opportunity' => $existing,
            ];
        }

        $metadata = $lead->metadata_json ?? [];
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        $opportunity = SalesOpportunity::create([
            'uuid' => \uuid(),
            'lead_id' => (int) $lead->id,
            'source_origin' => $this->detectSourceOriginFromLead($lead),
            'source_channel' => $this->detectSourceChannelFromLead($lead),
            'source_reference' => $this->buildSourceReference($lead, $metadata),
            'assigned_admin_user_id' => null,
            'customer_name' => trim((string) ($lead->full_name ?? 'Cliente sin nombre')),
            'customer_phone' => trim((string) ($lead->phone ?? '')),
            'customer_email' => trim((string) ($lead->email ?? '')),
            'interest_type' => $this->mapInterestType((string) ($lead->source_type ?? ''), $metadata),
            'package_id' => !empty($lead->package_id) ? (int) $lead->package_id : null,
            'package_slug' => trim((string) ($lead->package_slug ?? '')),
            'extra_service_id' => !empty($metadata['extra_service_id']) ? (int) $metadata['extra_service_id'] : null,
            'extra_service_slug' => trim((string) ($metadata['extra_service_slug'] ?? '')),
            'sales_stage' => 'new',
            'sales_temperature' => 'warm',
            'closing_probability' => 15,
            'travelers_count' => !empty($metadata['travelers']) ? (int) $metadata['travelers'] : null,
            'travel_date_estimate' => $this->normalizeDate($metadata['departureDate'] ?? null),
            'return_date_estimate' => $this->normalizeDate($metadata['returnDate'] ?? null),
            'budget_min' => null,
            'budget_max' => null,
            'quoted_amount' => null,
            'quoted_currency' => 'COP',
            'next_follow_up_at' => null,
            'last_contact_at' => date('Y-m-d H:i:s'),
            'won_at' => null,
            'lost_at' => null,
            'cancelled_at' => null,
            'lost_reason' => null,
            'lost_reason_code' => null,
            'lost_reason_detail' => null,
            'notes_summary' => trim((string) ($lead->message ?? '')),
            'created_by_admin_id' => $adminId,
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$opportunity) {
            return [
                'success' => false,
                'message' => 'No fue posible crear la oportunidad.',
                'errors' => ['opportunity' => ['No fue posible crear la oportunidad.']],
            ];
        }

        $lead->update([
            'sales_opportunity_id' => (int) $opportunity->id,
        ]);

        $this->logEvent(
            (int) $opportunity->id,
            $adminId,
            'created',
            'Oportunidad creada',
            'Se creó la oportunidad a partir del lead #' . (int) $lead->id . '.',
            [
                'lead_id' => (int) $lead->id,
                'source_type' => (string) ($lead->source_type ?? ''),
            ]
        );

        return [
            'success' => true,
            'message' => 'Oportunidad creada correctamente.',
            'errors' => [],
            'opportunity' => $opportunity,
        ];
    }

    public function assign(int $opportunityId, int $adminId): bool
    {
        if ($adminId <= 0) {
            return false;
        }

        $opportunity = SalesOpportunity::find($opportunityId);
        if (!$opportunity) {
            return false;
        }

        $ok = $opportunity->update([
            'assigned_admin_user_id' => $adminId,
            'updated_by_admin_id' => $adminId,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ok) {
            $this->logEvent(
                $opportunityId,
                $adminId,
                'assigned',
                'Oportunidad asignada',
                'La oportunidad fue asignada al asesor #' . $adminId . '.'
            );
        }

        return $ok;
    }

    public function changeStage(int $opportunityId, string $stage, ?int $adminId = null, ?string $message = null): bool
    {
        $allowed = [
            'new',
            'contacted',
            'profiled',
            'quoted',
            'follow_up',
            'pending_payment',
            'payment_reported',
            'payment_validated',
            'won',
            'lost',
            'cancelled',
        ];

        if (!in_array($stage, $allowed, true)) {
            return false;
        }

        $opportunity = SalesOpportunity::find($opportunityId);
        if (!$opportunity) {
            return false;
        }

        $oldStage = (string) $opportunity->sales_stage;

        $updateData = [
            'sales_stage' => $stage,
            'updated_by_admin_id' => $adminId,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ];

        if ($stage === 'won') {
            $updateData['won_at'] = date('Y-m-d H:i:s');
        }

        if ($stage === 'lost') {
            $updateData['lost_at'] = date('Y-m-d H:i:s');
        }

        if ($stage === 'cancelled') {
            $updateData['cancelled_at'] = date('Y-m-d H:i:s');
        }

        $ok = $opportunity->update($updateData);

        if ($ok) {
            $this->logEvent(
                $opportunityId,
                $adminId,
                'stage_changed',
                'Cambio de etapa',
                $message ?: "La etapa cambió de {$oldStage} a {$stage}.",
                [
                    'old_stage' => $oldStage,
                    'new_stage' => $stage,
                ]
            );
        }

        return $ok;
    }

    public function scheduleFollowUp(int $opportunityId, string $datetime, ?int $adminId = null, string $message = ''): bool
    {
        $opportunity = SalesOpportunity::find($opportunityId);
        if (!$opportunity) {
            return false;
        }

        $ok = $opportunity->update([
            'next_follow_up_at' => $datetime,
            'updated_by_admin_id' => $adminId,
        ]);

        if ($ok) {
            $this->logEvent(
                $opportunityId,
                $adminId,
                'follow_up_scheduled',
                'Seguimiento programado',
                $message !== '' ? $message : 'Se programó seguimiento para ' . $datetime . '.',
                [
                    'next_follow_up_at' => $datetime,
                ]
            );
        }

        return $ok;
    }

    public function addNote(int $opportunityId, string $message, ?int $adminId = null): bool
    {
        if (trim($message) === '') {
            return false;
        }

        $opportunity = SalesOpportunity::find($opportunityId);
        if (!$opportunity) {
            return false;
        }

        $summary = trim((string) ($opportunity->notes_summary ?? ''));
        $newSummary = $summary === ''
            ? trim($message)
            : $summary . "\n" . trim($message);

        $opportunity->update([
            'notes_summary' => $newSummary,
            'last_contact_at' => date('Y-m-d H:i:s'),
            'updated_by_admin_id' => $adminId,
        ]);

        $this->logEvent(
            $opportunityId,
            $adminId,
            'note',
            'Nota agregada',
            trim($message)
        );

        return true;
    }

    public function markWon(int $opportunityId, ?int $adminId = null): bool
    {
        return $this->changeStage($opportunityId, 'won', $adminId, 'La oportunidad fue marcada como ganada.');
    }

    public function markLost(
        int $opportunityId,
        string $reasonCode,
        ?string $reasonDetail = null,
        ?int $adminId = null
    ): bool {
        $opportunity = SalesOpportunity::find($opportunityId);

        if (!$opportunity || trim($reasonCode) === '') {
            return false;
        }

        $allowedReasons = [
            'no_response',
            'no_budget',
            'better_price',
            'postponed_trip',
            'no_availability',
            'documents_issue',
            'lost_interest',
            'bought_elsewhere',
            'not_qualified',
            'other',
        ];

        if (!in_array($reasonCode, $allowedReasons, true)) {
            return false;
        }

        $humanReason = $this->lostReasonLabel($reasonCode);
        $detail = trim((string) $reasonDetail);

        $ok = $opportunity->update([
            'sales_stage' => 'lost',
            'lost_reason' => $humanReason,
            'lost_reason_code' => $reasonCode,
            'lost_reason_detail' => $detail !== '' ? $detail : null,
            'lost_at' => date('Y-m-d H:i:s'),
            'updated_by_admin_id' => $adminId,
        ]);

        if ($ok) {
            $message = 'Motivo: ' . $humanReason;
            if ($detail !== '') {
                $message .= '. Detalle: ' . $detail;
            }

            $this->logEvent(
                $opportunityId,
                $adminId,
                'lost',
                'Oportunidad perdida',
                $message,
                [
                    'lost_reason_code' => $reasonCode,
                    'lost_reason_label' => $humanReason,
                    'lost_reason_detail' => $detail,
                ]
            );
        }

        return $ok;
    }

    protected function logEvent(
        int $opportunityId,
        ?int $adminUserId,
        string $eventType,
        string $title,
        ?string $message = null,
        ?array $meta = null
    ): void {
        SalesOpportunityEvent::create([
            'uuid' => \uuid(),
            'sales_opportunity_id' => $opportunityId,
            'admin_user_id' => $adminUserId,
            'event_type' => $eventType,
            'title' => $title,
            'message' => $message,
            'meta_json' => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    protected function detectSourceOriginFromLead(Lead $lead): string
    {
        $sourceType = (string) ($lead->source_type ?? '');
        $metadata = $lead->metadata_json ?? [];
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        if (!empty($metadata['chatbot_session_id'])) {
            return 'chatbot_whatsapp';
        }

        if (!empty($metadata['wa_origin']) && $metadata['wa_origin'] === 'web') {
            return 'web_whatsapp';
        }

        if (in_array($sourceType, ['contact', 'pqrs', 'tickets', 'package', 'extra_service'], true)) {
            return 'web_form';
        }

        return 'other';
    }

    protected function detectSourceChannelFromLead(Lead $lead): string
    {
        $channel = trim((string) ($lead->channel ?? 'web'));
        $allowed = ['web', 'whatsapp', 'facebook', 'instagram', 'phone', 'email', 'manual', 'other'];

        return in_array($channel, $allowed, true) ? $channel : 'other';
    }

    protected function buildSourceReference(Lead $lead, array $metadata = []): ?string
    {
        if (!empty($lead->package_slug)) {
            return 'package:' . trim((string) $lead->package_slug);
        }

        if (!empty($metadata['extra_service_slug'])) {
            return 'extra_service:' . trim((string) $metadata['extra_service_slug']);
        }

        return null;
    }

    protected function mapInterestType(string $sourceType, array $metadata = []): string
    {
        if ($sourceType === 'package') {
            return 'package';
        }

        if ($sourceType === 'tickets') {
            return 'tickets';
        }

        if ($sourceType === 'extra_service') {
            $slug = trim((string) ($metadata['extra_service_slug'] ?? ''));

            return match ($slug) {
                'pasaportes-visas' => 'visa_passport',
                'asistencias-medicas' => 'medical_assistance',
                'simcards-viajes-exterior' => 'simcard',
                'curso-idiomas' => 'language_course',
                'receptivo-tours-internos' => 'receptive_tour',
                default => 'extra_service',
            };
        }

        return 'other';
    }

    protected function normalizeDate($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    protected function normalizeManualPayload(array $payload): array
    {
        $sourceOrigin = trim((string) ($payload['source_origin'] ?? 'direct_whatsapp'));
        $sourceChannel = trim((string) ($payload['source_channel'] ?? 'whatsapp'));
        $interestType = trim((string) ($payload['interest_type'] ?? 'other'));
        $salesTemperature = trim((string) ($payload['sales_temperature'] ?? 'warm'));

        return [
            'source_origin' => in_array($sourceOrigin, ['web_form', 'web_whatsapp', 'chatbot_whatsapp', 'direct_whatsapp', 'manual_admin', 'other'], true)
                ? $sourceOrigin
                : 'direct_whatsapp',

            'source_channel' => in_array($sourceChannel, ['web', 'whatsapp', 'facebook', 'instagram', 'phone', 'email', 'manual', 'other'], true)
                ? $sourceChannel
                : 'whatsapp',

            'source_reference' => trim((string) ($payload['source_reference'] ?? '')),
            'customer_name' => trim((string) ($payload['customer_name'] ?? '')),
            'customer_phone' => trim((string) ($payload['customer_phone'] ?? '')),
            'customer_email' => trim((string) ($payload['customer_email'] ?? '')),

            'interest_type' => in_array($interestType, [
                'package',
                'tickets',
                'extra_service',
                'visa_passport',
                'medical_assistance',
                'simcard',
                'language_course',
                'receptive_tour',
                'other'
            ], true) ? $interestType : 'other',

            'package_slug' => trim((string) ($payload['package_slug'] ?? '')),
            'extra_service_slug' => trim((string) ($payload['extra_service_slug'] ?? '')),

            'sales_temperature' => in_array($salesTemperature, ['cold', 'warm', 'hot'], true)
                ? $salesTemperature
                : 'warm',

            'closing_probability' => max(0, min(100, (int) ($payload['closing_probability'] ?? 15))),
            'travelers_count' => !empty($payload['travelers_count']) ? (int) $payload['travelers_count'] : null,
            'travel_date_estimate' => $this->normalizeDate($payload['travel_date_estimate'] ?? null),
            'return_date_estimate' => $this->normalizeDate($payload['return_date_estimate'] ?? null),
            'budget_min' => $payload['budget_min'] !== '' ? (float) $payload['budget_min'] : null,
            'budget_max' => $payload['budget_max'] !== '' ? (float) $payload['budget_max'] : null,
            'next_follow_up_at' => trim((string) ($payload['next_follow_up_at'] ?? '')) ?: null,
            'notes_summary' => trim((string) ($payload['notes_summary'] ?? '')),
        ];
    }

    protected function validateManualPayload(array $data): array
    {
        $errors = [];

        if ($data['customer_name'] === '') {
            $errors['customer_name'][] = 'El nombre del cliente es obligatorio.';
        }

        if ($data['customer_phone'] === '' && $data['customer_email'] === '') {
            $errors['customer_phone'][] = 'Debes registrar al menos teléfono o correo.';
        }

        if ($data['customer_email'] !== '' && !filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'][] = 'El correo no es válido.';
        }

        if ($data['budget_min'] !== null && $data['budget_min'] < 0) {
            $errors['budget_min'][] = 'El presupuesto mínimo no puede ser negativo.';
        }

        if ($data['budget_max'] !== null && $data['budget_max'] < 0) {
            $errors['budget_max'][] = 'El presupuesto máximo no puede ser negativo.';
        }

        if ($data['budget_min'] !== null && $data['budget_max'] !== null && $data['budget_max'] < $data['budget_min']) {
            $errors['budget_max'][] = 'El presupuesto máximo no puede ser menor al mínimo.';
        }

        return $errors;
    }

    public function createManual(array $payload, ?int $adminId = null): array
    {
        $data = $this->normalizeManualPayload($payload);
        $errors = $this->validateManualPayload($data);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los datos de la oportunidad.',
                'errors' => $errors,
                'old' => $payload,
            ];
        }

        $opportunity = SalesOpportunity::create([
            'uuid' => \uuid(),
            'lead_id' => null,
            'source_origin' => $data['source_origin'],
            'source_channel' => $data['source_channel'],
            'source_reference' => $data['source_reference'],
            'assigned_admin_user_id' => $adminId,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'],
            'interest_type' => $data['interest_type'],
            'package_id' => null,
            'package_slug' => $data['package_slug'],
            'extra_service_id' => null,
            'extra_service_slug' => $data['extra_service_slug'],
            'sales_stage' => 'new',
            'sales_temperature' => $data['sales_temperature'],
            'closing_probability' => $data['closing_probability'],
            'travelers_count' => $data['travelers_count'],
            'travel_date_estimate' => $data['travel_date_estimate'],
            'return_date_estimate' => $data['return_date_estimate'],
            'budget_min' => $data['budget_min'],
            'budget_max' => $data['budget_max'],
            'quoted_amount' => null,
            'quoted_currency' => 'COP',
            'next_follow_up_at' => $data['next_follow_up_at'],
            'last_contact_at' => date('Y-m-d H:i:s'),
            'won_at' => null,
            'lost_at' => null,
            'cancelled_at' => null,
            'lost_reason' => null,
            'lost_reason_code' => null,
            'lost_reason_detail' => null,
            'notes_summary' => $data['notes_summary'],
            'created_by_admin_id' => $adminId,
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$opportunity) {
            return [
                'success' => false,
                'message' => 'No fue posible crear la oportunidad.',
                'errors' => ['system' => ['No fue posible crear la oportunidad.']],
                'old' => $payload,
            ];
        }

        $this->logEvent(
            (int) $opportunity->id,
            $adminId,
            'created',
            'Oportunidad creada manualmente',
            'Se creó una oportunidad desde el panel administrativo.',
            [
                'source_origin' => $data['source_origin'],
                'source_channel' => $data['source_channel'],
            ]
        );

        return [
            'success' => true,
            'message' => 'Oportunidad creada correctamente.',
            'errors' => [],
            'opportunity' => $opportunity,
            'old' => [],
        ];
    }

    public function updateProfile(int $opportunityId, array $payload, ?int $adminId = null): array
    {
        $opportunity = SalesOpportunity::find($opportunityId);

        if (!$opportunity) {
            return [
                'success' => false,
                'message' => 'Oportunidad no encontrada.',
                'errors' => ['opportunity' => ['Oportunidad no encontrada.']],
                'old' => $payload,
            ];
        }

        $data = $this->normalizeManualPayload($payload);
        $errors = $this->validateManualPayload($data);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los datos de la oportunidad.',
                'errors' => $errors,
                'old' => $payload,
            ];
        }

        $ok = $opportunity->update([
            'source_origin' => $data['source_origin'],
            'source_channel' => $data['source_channel'],
            'source_reference' => $data['source_reference'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'],
            'interest_type' => $data['interest_type'],
            'package_slug' => $data['package_slug'],
            'extra_service_slug' => $data['extra_service_slug'],
            'sales_temperature' => $data['sales_temperature'],
            'closing_probability' => $data['closing_probability'],
            'travelers_count' => $data['travelers_count'],
            'travel_date_estimate' => $data['travel_date_estimate'],
            'return_date_estimate' => $data['return_date_estimate'],
            'budget_min' => $data['budget_min'],
            'budget_max' => $data['budget_max'],
            'next_follow_up_at' => $data['next_follow_up_at'],
            'notes_summary' => $data['notes_summary'],
            'updated_by_admin_id' => $adminId,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            return [
                'success' => false,
                'message' => 'No fue posible actualizar la oportunidad.',
                'errors' => ['system' => ['No fue posible actualizar la oportunidad.']],
                'old' => $payload,
            ];
        }

        $this->logEvent(
            (int) $opportunity->id,
            $adminId,
            'note',
            'Perfil comercial actualizado',
            'Se actualizó la información comercial de la oportunidad.'
        );

        return [
            'success' => true,
            'message' => 'Oportunidad actualizada correctamente.',
            'errors' => [],
            'old' => [],
            'opportunity' => $opportunity,
        ];
    }

    public function buildCustomerWhatsAppUrl(SalesOpportunity $opportunity, ?string $customMessage = null): string
    {
        $phone = normalize_phone((string) ($opportunity->customer_phone ?? ''));

        if ($phone === '') {
            return '';
        }

        $message = trim((string) $customMessage);
        if ($message === '') {
            $message = $this->buildCustomerWhatsAppMessage($opportunity);
        }

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    public function buildCustomerWhatsAppMessage(SalesOpportunity $opportunity): string
    {
        $parts = [
            'Hola ' . trim((string) ($opportunity->customer_name ?? '')) . ', te saluda un asesor de Alestur.',
        ];

        if (!empty($opportunity->package_slug)) {
            $parts[] = 'Te escribo por tu interés en el paquete: ' . trim((string) $opportunity->package_slug) . '.';
        } elseif (!empty($opportunity->extra_service_slug)) {
            $parts[] = 'Te escribo por tu interés en el servicio: ' . trim((string) $opportunity->extra_service_slug) . '.';
        } elseif (!empty($opportunity->interest_type)) {
            $parts[] = 'Te escribo por tu solicitud sobre: ' . trim((string) $opportunity->interest_type) . '.';
        }

        $parts[] = 'Quedo atento para ayudarte con tu proceso.';

        return implode("\n", array_filter($parts));
    }
}