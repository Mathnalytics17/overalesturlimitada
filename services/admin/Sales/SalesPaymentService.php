<?php

namespace app\Services\Admin\Sales;

use app\Models\SalesOpportunity;
use app\Models\SalesPayment;
use app\Models\SalesOpportunityEvent;

class SalesPaymentService
{

 protected function normalizeDate($value): ?string
    {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

protected function normalizeManualPayload(array $payload): array
{
    $sourceOrigin = trim((string)($payload['source_origin'] ?? 'direct_whatsapp'));
    $sourceChannel = trim((string)($payload['source_channel'] ?? 'whatsapp'));
    $interestType = trim((string)($payload['interest_type'] ?? 'other'));
    $salesTemperature = trim((string)($payload['sales_temperature'] ?? 'warm'));

    return [
        'source_origin' => in_array($sourceOrigin, ['web_form', 'web_whatsapp', 'chatbot_whatsapp', 'direct_whatsapp', 'manual_admin', 'other'], true)
            ? $sourceOrigin
            : 'direct_whatsapp',

        'source_channel' => in_array($sourceChannel, ['web', 'whatsapp', 'facebook', 'instagram', 'phone', 'email', 'manual', 'other'], true)
            ? $sourceChannel
            : 'whatsapp',

        'source_reference' => trim((string)($payload['source_reference'] ?? '')),
        'customer_name' => trim((string)($payload['customer_name'] ?? '')),
        'customer_phone' => trim((string)($payload['customer_phone'] ?? '')),
        'customer_email' => trim((string)($payload['customer_email'] ?? '')),

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

        'package_slug' => trim((string)($payload['package_slug'] ?? '')),
        'extra_service_slug' => trim((string)($payload['extra_service_slug'] ?? '')),

        'sales_temperature' => in_array($salesTemperature, ['cold', 'warm', 'hot'], true)
            ? $salesTemperature
            : 'warm',

        'closing_probability' => max(0, min(100, (int)($payload['closing_probability'] ?? 15))),
        'travelers_count' => !empty($payload['travelers_count']) ? (int)$payload['travelers_count'] : null,
        'travel_date_estimate' => $this->normalizeDate($payload['travel_date_estimate'] ?? null),
        'return_date_estimate' => $this->normalizeDate($payload['return_date_estimate'] ?? null),
        'budget_min' => $payload['budget_min'] !== '' ? (float)$payload['budget_min'] : null,
        'budget_max' => $payload['budget_max'] !== '' ? (float)$payload['budget_max'] : null,
        'next_follow_up_at' => trim((string)($payload['next_follow_up_at'] ?? '')) ?: null,
        'notes_summary' => trim((string)($payload['notes_summary'] ?? '')),
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

    public function reportPayment(int $opportunityId, array $payload, ?int $adminId = null): array
    {
        $opportunity = SalesOpportunity::find($opportunityId);

        if (!$opportunity) {
            return [
                'success' => false,
                'message' => 'Oportunidad no encontrada.',
                'errors' => ['opportunity' => ['Oportunidad no encontrada.']],
            ];
        }

        $data = $this->normalize($payload);
        $errors = $this->validateReport($data);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los datos del pago.',
                'errors' => $errors,
            ];
        }

        $payment = SalesPayment::create([
            'uuid' => \uuid(),
            'sales_opportunity_id' => $opportunityId,
            'payment_kind' => $data['payment_kind'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'],
            'proof_file_path' => null,
            'status' => 'reported',
            'reported_by_admin_id' => $adminId,
            'verified_by_admin_id' => null,
            'reported_at' => date('Y-m-d H:i:s'),
            'verified_at' => null,
            'notes' => $data['notes'],
        ]);

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'No fue posible registrar el pago.',
                'errors' => ['system' => ['No fue posible registrar el pago.']],
            ];
        }

        $opportunity->update([
            'sales_stage' => 'payment_reported',
            'last_contact_at' => date('Y-m-d H:i:s'),
            'updated_by_admin_id' => $adminId,
        ]);

        $this->logEvent(
            $opportunityId,
            $adminId,
            'payment_reported',
            'Pago reportado',
            'Se registró un pago reportado por ' . number_format((float)$data['amount'], 0, ',', '.') . ' ' . $data['currency'] . '.',
            [
                'payment_id' => (int)$payment->id,
                'payment_kind' => $data['payment_kind'],
                'payment_method' => $data['payment_method'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
            ]
        );

        return [
            'success' => true,
            'message' => 'Pago reportado correctamente.',
            'errors' => [],
            'payment' => $payment,
        ];
    }

    public function verifyPayment(int $paymentId, ?int $adminId = null): bool
    {
        $payment = SalesPayment::find($paymentId);
        if (!$payment) {
            return false;
        }

        $ok = $payment->update([
            'status' => 'verified',
            'verified_by_admin_id' => $adminId,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            return false;
        }

        $opportunity = SalesOpportunity::find((int)$payment->sales_opportunity_id);
        if ($opportunity) {
            $opportunity->update([
                'sales_stage' => 'payment_validated',
                'last_contact_at' => date('Y-m-d H:i:s'),
                'updated_by_admin_id' => $adminId,
            ]);

            $this->logEvent(
                (int)$opportunity->id,
                $adminId,
                'payment_validated',
                'Pago validado',
                'Se validó un pago por ' . number_format((float)$payment->amount, 0, ',', '.') . ' ' . (string)$payment->currency . '.',
                [
                    'payment_id' => (int)$payment->id,
                    'amount' => (float)$payment->amount,
                    'currency' => (string)$payment->currency,
                ]
            );
        }

        return true;
    }

    public function rejectPayment(int $paymentId, string $reason, ?int $adminId = null): bool
    {
        $payment = SalesPayment::find($paymentId);
        if (!$payment || trim($reason) === '') {
            return false;
        }

        $notes = trim((string)($payment->notes ?? ''));
        $notes = $notes === '' ? 'Rechazado: ' . trim($reason) : $notes . "\nRechazado: " . trim($reason);

        $ok = $payment->update([
            'status' => 'rejected',
            'verified_by_admin_id' => $adminId,
            'verified_at' => date('Y-m-d H:i:s'),
            'notes' => $notes,
        ]);

        if (!$ok) {
            return false;
        }

        $opportunity = SalesOpportunity::find((int)$payment->sales_opportunity_id);
        if ($opportunity) {
            $this->logEvent(
                (int)$opportunity->id,
                $adminId,
                'note',
                'Pago rechazado',
                'Se rechazó el pago #' . (int)$payment->id . '. Motivo: ' . trim($reason),
                [
                    'payment_id' => (int)$payment->id,
                    'reason' => trim($reason),
                ]
            );
        }

        return true;
    }

    protected function normalize(array $payload): array
    {
        return [
            'payment_kind' => trim((string)($payload['payment_kind'] ?? 'partial')),
            'amount' => (float)($payload['amount'] ?? 0),
            'currency' => trim((string)($payload['currency'] ?? 'COP')) ?: 'COP',
            'payment_method' => trim((string)($payload['payment_method'] ?? 'transfer')),
            'payment_reference' => trim((string)($payload['payment_reference'] ?? '')),
            'notes' => trim((string)($payload['notes'] ?? '')),
        ];
    }

    protected function validateReport(array $data): array
    {
        $errors = [];

        if ($data['amount'] <= 0) {
            $errors['amount'][] = 'El monto debe ser mayor a cero.';
        }

        $allowedKinds = ['deposit', 'partial', 'full', 'balance', 'refund'];
        if (!in_array($data['payment_kind'], $allowedKinds, true)) {
            $errors['payment_kind'][] = 'Tipo de pago inválido.';
        }

        $allowedMethods = ['transfer', 'cash', 'consignment', 'card', 'external_link', 'other'];
        if (!in_array($data['payment_method'], $allowedMethods, true)) {
            $errors['payment_method'][] = 'Método de pago inválido.';
        }

        return $errors;
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
        (int)$opportunity->id,
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
}