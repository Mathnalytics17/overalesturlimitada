<?php

namespace app\Services\Admin\Sales;

use app\Models\SalesOpportunity;
use app\Models\SalesOrder;
use app\Models\SalesPayment;
use app\Models\SalesQuote;
use app\Models\SalesOpportunityEvent;

class SalesOrderService
{
    public function createFromOpportunity(int $opportunityId, ?int $adminId = null): array
    {
        $opportunity = SalesOpportunity::find($opportunityId);

        if (!$opportunity) {
            return [
                'success' => false,
                'message' => 'Oportunidad no encontrada.',
                'errors' => ['opportunity' => ['Oportunidad no encontrada.']],
            ];
        }

        $existing = SalesOrder::findByOpportunityId($opportunityId);
        if ($existing) {
            return [
                'success' => false,
                'message' => 'La oportunidad ya tiene una venta/reserva creada.',
                'errors' => ['order' => ['La oportunidad ya tiene una venta/reserva creada.']],
                'order' => $existing,
            ];
        }

        $currentQuote = SalesQuote::acceptedByOpportunity($opportunityId);
        if (!$currentQuote) {
            return [
                'success' => false,
                'message' => 'Antes de crear la venta/reserva debes aceptar una cotización con valor mayor a cero.',
                'errors' => ['quote' => ['No hay cotización aceptada para crear la venta/reserva.']],
            ];
        }

        $verifiedPaid = SalesPayment::sumVerifiedByOpportunity($opportunityId);
        $totalAmount = (float)$currentQuote->amount;
        $currency = (string)$currentQuote->currency ?: 'COP';
        $balance = max(0, $totalAmount - $verifiedPaid);

        if ($totalAmount <= 0) {
            return [
                'success' => false,
                'message' => 'La venta/reserva no se puede crear porque el valor cotizado está en cero.',
                'errors' => ['amount' => ['El valor cotizado debe ser mayor a cero.']],
            ];
        }

        $order = SalesOrder::create([
            'uuid' => \uuid(),
            'sales_opportunity_id' => (int)$opportunity->id,
            'lead_id' => !empty($opportunity->lead_id) ? (int)$opportunity->lead_id : null,
            'order_number' => $this->generateOrderNumber(),
            'customer_name' => trim((string)($opportunity->customer_name ?? '')),
            'customer_phone' => trim((string)($opportunity->customer_phone ?? '')),
            'customer_email' => trim((string)($opportunity->customer_email ?? '')),
            'product_type' => $this->mapProductType((string)($opportunity->interest_type ?? 'other')),
            'package_id' => !empty($opportunity->package_id) ? (int)$opportunity->package_id : null,
            'package_slug' => trim((string)($opportunity->package_slug ?? '')),
            'extra_service_id' => !empty($opportunity->extra_service_id) ? (int)$opportunity->extra_service_id : null,
            'extra_service_slug' => trim((string)($opportunity->extra_service_slug ?? '')),
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'paid_amount' => $verifiedPaid,
            'balance_amount' => $balance,
            'commercial_status' => $this->resolveCommercialStatus($verifiedPaid, $totalAmount),
            'operational_status' => 'pending_documents',
            'travelers_count' => !empty($opportunity->travelers_count) ? (int)$opportunity->travelers_count : null,
            'travel_date_estimate' => $this->normalizeDate($opportunity->travel_date_estimate ?? null),
            'return_date_estimate' => $this->normalizeDate($opportunity->return_date_estimate ?? null),
            'assigned_admin_user_id' => !empty($opportunity->assigned_admin_user_id) ? (int)$opportunity->assigned_admin_user_id : null,
            'notes' => trim((string)($opportunity->notes_summary ?? '')),
            'created_by_admin_id' => $adminId,
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$order) {
            return [
                'success' => false,
                'message' => 'No fue posible crear la venta/reserva.',
                'errors' => ['system' => ['No fue posible crear la venta/reserva.']],
            ];
        }

        $opportunity->update([
            'sales_stage' => 'won',
            'quoted_amount' => $totalAmount,
            'quoted_currency' => $currency,
            'won_at' => !empty($opportunity->won_at) ? $opportunity->won_at : date('Y-m-d H:i:s'),
            'updated_by_admin_id' => $adminId,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logEvent(
            (int)$opportunity->id,
            $adminId,
            'won',
            'Venta/reserva creada',
            'Se creó la venta/reserva ' . $order->order_number . '.',
            [
                'order_id' => (int)$order->id,
                'order_number' => (string)$order->order_number,
            ]
        );

        return [
            'success' => true,
            'message' => 'Venta/reserva creada correctamente.',
            'errors' => [],
            'order' => $order,
        ];
    }

    public function updateOperationalStatus(int $orderId, string $status, ?int $adminId = null): bool
    {
        $allowed = [
            'pending_documents',
            'pending_booking',
            'booked',
            'delivered',
            'completed',
            'cancelled',
        ];

        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $order = SalesOrder::find($orderId);
        if (!$order) {
            return false;
        }

        $this->refreshFinancialStatus($orderId, $adminId);
        $order = SalesOrder::find($orderId);
        if (!$order) {
            return false;
        }

        if ($status === 'completed' && (float)($order->balance_amount ?? 0) > 0.01) {
            return false;
        }

        return $order->update([
            'operational_status' => $status,
            'updated_by_admin_id' => $adminId,
        ]);
    }

    protected function mapProductType(string $interestType): string
    {
        return match ($interestType) {
            'package' => 'package',
            'tickets' => 'tickets',
            'extra_service',
            'visa_passport',
            'medical_assistance',
            'simcard',
            'language_course',
            'receptive_tour' => 'extra_service',
            default => 'other',
        };
    }

    protected function resolveCommercialStatus(float $paid, float $total): string
    {
        if ($total <= 0) {
            return 'open';
        }

        if ($paid <= 0) {
            return 'open';
        }

        if ($paid < $total) {
            return 'paid_partial';
        }

        return 'paid_full';
    }

    protected function normalizeDate($value): ?string
    {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    protected function generateOrderNumber(): string
    {
        return 'AL-' . date('Ymd') . '-' . strtoupper(substr(md5((string)microtime(true) . random_int(1000, 9999)), 0, 6));
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

    public function updateNotes(int $orderId, string $notes, ?int $adminId = null): bool
{
    $order = SalesOrder::find($orderId);
    if (!$order) {
        return false;
    }

    return $order->update([
        'notes' => trim($notes),
        'updated_by_admin_id' => $adminId,
    ]);
}

public function refreshFinancialStatus(int $orderId, ?int $adminId = null): bool
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
}