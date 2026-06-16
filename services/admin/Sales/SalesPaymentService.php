<?php

namespace app\Services\Admin\Sales;

use app\Models\SalesOpportunity;
use app\Models\SalesPayment;
use app\Models\SalesOrder;
use app\Models\SalesQuote;
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
        $currentQuote = SalesQuote::acceptedByOpportunity($opportunityId);
        $order = SalesOrder::findByOpportunityId($opportunityId);
        $quotedAmount = $currentQuote ? (float)$currentQuote->amount : (float)($opportunity->quoted_amount ?? 0);
        $quotedCurrency = $currentQuote ? (string)$currentQuote->currency : ((string)($opportunity->quoted_currency ?? 'COP') ?: 'COP');
        $verifiedTotal = SalesPayment::sumVerifiedByOpportunity($opportunityId);
        $committedTotal = SalesPayment::sumCommittedByOpportunity($opportunityId);
        $verifiedBalance = max(0, $quotedAmount - $verifiedTotal);
        $availableToReport = max(0, $quotedAmount - $committedTotal);

        if (in_array($data['payment_kind'], ['full', 'balance'], true) && $data['amount'] <= 0 && $availableToReport > 0) {
            $data['amount'] = $availableToReport;
        }

        if ($data['currency'] === '') {
            $data['currency'] = $quotedCurrency;
        }

        $errors = $this->validateReport($data, $quotedAmount, $availableToReport, $verifiedTotal);

        if (!$currentQuote && $data['payment_kind'] !== 'refund') {
            $errors['quote'][] = 'Primero debes aceptar una cotización antes de registrar pagos.';
        }

        if (!$order && $data['payment_kind'] !== 'refund') {
            $errors['order'][] = 'Primero confirma la venta/reserva. El pago debe quedar asociado a una venta activa.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => $this->firstError($errors) ?: 'Revisa los datos del pago.',
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
            'payment_reference' => null,
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

        $internalReference = $this->buildInternalReference((int)$payment->id, (string)($payment->reported_at ?? date('Y-m-d H:i:s')));
        $externalReference = trim((string)$data['payment_reference']);
        $finalReference = $internalReference . ($externalReference !== '' ? ' | Ref. externa: ' . $externalReference : '');
        $payment->update([
            'payment_reference' => $finalReference,
        ]);
        $payment->payment_reference = $finalReference;

        $nextStage = $data['payment_kind'] === 'refund' ? (string)($opportunity->sales_stage ?? 'payment_validated') : 'payment_reported';
        $opportunity->update([
            'sales_stage' => $nextStage,
            'last_contact_at' => date('Y-m-d H:i:s'),
            'updated_by_admin_id' => $adminId,
        ]);

        $this->logEvent(
            $opportunityId,
            $adminId,
            'payment_reported',
            $data['payment_kind'] === 'refund' ? 'Devolución reportada' : 'Pago reportado',
            'Se registró ' . ($data['payment_kind'] === 'refund' ? 'una devolución' : 'un pago') . ' por ' . number_format((float)$data['amount'], 0, ',', '.') . ' ' . $data['currency'] . '.',
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
            'message' => $data['payment_kind'] === 'refund' ? 'Devolución reportada correctamente.' : 'Pago reportado correctamente.',
            'errors' => [],
            'payment' => $payment,
        ];
    }

    public function verifyPayment(int $paymentId, ?int $adminId = null): array
    {
        $payment = SalesPayment::find($paymentId);
        if (!$payment) {
            return ['success' => false, 'message' => 'Pago no encontrado.'];
        }

        if ((string)($payment->status ?? '') !== 'reported') {
            return ['success' => false, 'message' => 'Este pago ya fue procesado.'];
        }

        $opportunityId = (int)$payment->sales_opportunity_id;
        $opportunity = SalesOpportunity::find($opportunityId);
        $currentQuote = SalesQuote::acceptedByOpportunity($opportunityId);
        $order = SalesOrder::findByOpportunityId($opportunityId);
        $quotedAmount = $order ? (float)$order->total_amount : ($currentQuote ? (float)$currentQuote->amount : (float)($opportunity->quoted_amount ?? 0));
        $verifiedTotal = SalesPayment::sumVerifiedByOpportunity($opportunityId);
        $kind = (string)($payment->payment_kind ?? 'partial');
        $amount = (float)($payment->amount ?? 0);

        if ($kind !== 'refund' && $quotedAmount > 0 && ($verifiedTotal + $amount) > ($quotedAmount + 0.01)) {
            return [
                'success' => false,
                'message' => 'No se puede validar: este pago supera el saldo pendiente actual.',
            ];
        }

        if ($kind === 'refund' && $amount > ($verifiedTotal + 0.01)) {
            return [
                'success' => false,
                'message' => 'No se puede validar: la devolución supera el total validado.',
            ];
        }

        $ok = $payment->update([
            'status' => 'verified',
            'verified_by_admin_id' => $adminId,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            return ['success' => false, 'message' => 'No fue posible validar el pago.'];
        }

        if ($opportunity) {
            if ($order) {
                $this->refreshOrderFinancialStatus((int)$order->id, $adminId);
            }

            $paidAfter = SalesPayment::sumVerifiedByOpportunity($opportunityId);
            $stageAfter = ($quotedAmount > 0 && $paidAfter >= ($quotedAmount - 0.01)) ? 'payment_validated' : 'payment_validated';

            $opportunity->update([
                'sales_stage' => $stageAfter,
                'last_contact_at' => date('Y-m-d H:i:s'),
                'updated_by_admin_id' => $adminId,
            ]);

            $this->logEvent(
                (int)$opportunity->id,
                $adminId,
                'payment_validated',
                $kind === 'refund' ? 'Devolución validada' : 'Pago validado',
                'Se validó ' . ($kind === 'refund' ? 'una devolución' : 'un pago') . ' por ' . number_format((float)$payment->amount, 0, ',', '.') . ' ' . (string)$payment->currency . '.',
                [
                    'payment_id' => (int)$payment->id,
                    'amount' => (float)$payment->amount,
                    'currency' => (string)$payment->currency,
                    'payment_kind' => $kind,
                ]
            );
        }

        return ['success' => true, 'message' => $kind === 'refund' ? 'Devolución validada correctamente.' : 'Pago validado correctamente.'];
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

    protected function validateReport(array $data, float $quotedAmount = 0, float $availableToReport = 0, float $verifiedTotal = 0): array
    {
        $errors = [];
        $kind = (string)$data['payment_kind'];

        if ($data['amount'] <= 0) {
            $errors['amount'][] = 'El monto debe ser mayor a cero.';
        }

        $allowedKinds = ['deposit', 'partial', 'full', 'balance', 'refund'];
        if (!in_array($kind, $allowedKinds, true)) {
            $errors['payment_kind'][] = 'Tipo de pago inválido.';
        }

        $allowedMethods = ['transfer', 'cash', 'consignment', 'card', 'external_link', 'other'];
        if (!in_array($data['payment_method'], $allowedMethods, true)) {
            $errors['payment_method'][] = 'Método de pago inválido.';
        }

        if ($kind !== 'refund') {
            if ($quotedAmount <= 0) {
                $errors['quote'][] = 'Primero debes aceptar una cotización con valor para poder reportar pagos.';
            }

            if ($quotedAmount > 0 && $availableToReport <= 0) {
                $errors['amount'][] = 'No hay saldo pendiente para reportar otro pago. Si hubo un error, rechaza pagos pendientes o registra una devolución/ajuste.';
            }

            if ($availableToReport > 0 && (float)$data['amount'] > ($availableToReport + 0.01)) {
                $errors['amount'][] = 'El monto no puede superar el saldo pendiente disponible.';
            }
        } else {
            if ($verifiedTotal <= 0) {
                $errors['amount'][] = 'No puedes registrar una devolución si no hay pagos validados.';
            }

            if ($verifiedTotal > 0 && (float)$data['amount'] > ($verifiedTotal + 0.01)) {
                $errors['amount'][] = 'La devolución no puede superar el total validado.';
            }
        }

        return $errors;
    }

    protected function firstError(array $errors): string
    {
        foreach ($errors as $messages) {
            if (is_array($messages) && !empty($messages[0])) {
                return (string)$messages[0];
            }
        }

        return '';
    }



    protected function buildInternalReference(int $paymentId, string $dateTime): string
    {
        $timestamp = strtotime($dateTime) ?: time();
        return 'CMP-' . date('Ymd', $timestamp) . '-' . str_pad((string)$paymentId, 6, '0', STR_PAD_LEFT);
    }

    protected function refreshOrderFinancialStatus(int $orderId, ?int $adminId = null): bool
    {
        $order = SalesOrder::find($orderId);
        if (!$order) {
            return false;
        }

        $paid = SalesPayment::sumVerifiedByOpportunity((int)$order->sales_opportunity_id);
        $total = (float)($order->total_amount ?? 0);
        $balance = max(0, $total - $paid);

        return $order->update([
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'commercial_status' => $this->resolveCommercialStatus($paid, $total),
            'updated_by_admin_id' => $adminId,
        ]);
    }

    protected function resolveCommercialStatus(float $paid, float $total): string
    {
        if ($total <= 0 || $paid <= 0) {
            return 'open';
        }

        if ($paid < $total) {
            return 'paid_partial';
        }

        return 'paid_full';
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