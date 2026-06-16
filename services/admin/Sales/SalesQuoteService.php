<?php

namespace app\Services\Admin\Sales;

use app\Models\SalesOpportunity;
use app\Models\SalesQuote;
use app\Models\SalesOpportunityEvent;
use app\Models\Currency;

class SalesQuoteService
{
    public function create(int $opportunityId, array $payload, ?int $adminId = null): array
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
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los datos de la cotización.',
                'errors' => $errors,
            ];
        }

        $quote = SalesQuote::create([
            'uuid' => \uuid(),
            'sales_opportunity_id' => $opportunityId,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'valid_until' => $data['valid_until'],
            'sent_at' => null,
            'status' => 'draft',
            'created_by_admin_id' => $adminId,
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$quote) {
            return [
                'success' => false,
                'message' => 'No fue posible crear la cotización.',
                'errors' => ['system' => ['No fue posible crear la cotización.']],
            ];
        }

        $opportunity->update([
            'quoted_amount' => $data['amount'],
            'quoted_currency' => $data['currency'],
            'sales_stage' => 'quoted',
            'updated_by_admin_id' => $adminId,
            'last_contact_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logEvent(
            $opportunityId,
            $adminId,
            'quote_sent',
            'Cotización creada',
            'Se registró una cotización por ' . number_format((float)$data['amount'], 0, ',', '.') . ' ' . $data['currency'] . '.',
            [
                'quote_id' => (int)$quote->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'valid_until' => $data['valid_until'],
            ]
        );

        return [
            'success' => true,
            'message' => 'Cotización creada correctamente.',
            'errors' => [],
            'quote' => $quote,
        ];
    }

    public function markSent(int $quoteId, ?int $adminId = null): bool
    {
        $quote = SalesQuote::find($quoteId);
        if (!$quote) {
            return false;
        }

        $ok = $quote->update([
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$ok) {
            return false;
        }

        $opportunity = SalesOpportunity::find((int)$quote->sales_opportunity_id);
        if ($opportunity) {
            $opportunity->update([
                'sales_stage' => 'quoted',
                'quoted_amount' => (float)$quote->amount,
                'quoted_currency' => (string)$quote->currency,
                'updated_by_admin_id' => $adminId,
                'last_contact_at' => date('Y-m-d H:i:s'),
            ]);

            $this->logEvent(
                (int)$opportunity->id,
                $adminId,
                'quote_sent',
                'Cotización enviada',
                'Se marcó la cotización #' . (int)$quote->id . ' como enviada.',
                [
                    'quote_id' => (int)$quote->id,
                ]
            );
        }

        return true;
    }

    public function markAccepted(int $quoteId, ?int $adminId = null): bool
    {
        $quote = SalesQuote::find($quoteId);
        if (!$quote) {
            return false;
        }

        $ok = $quote->update([
            'status' => 'accepted',
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$ok) {
            return false;
        }

        $opportunity = SalesOpportunity::find((int)$quote->sales_opportunity_id);
        if ($opportunity) {
            $opportunity->update([
                'sales_stage' => 'pending_payment',
                'quoted_amount' => (float)$quote->amount,
                'quoted_currency' => (string)$quote->currency,
                'updated_by_admin_id' => $adminId,
                'last_contact_at' => date('Y-m-d H:i:s'),
            ]);

            $this->logEvent(
                (int)$opportunity->id,
                $adminId,
                'note',
                'Cotización aceptada',
                'El cliente aceptó la cotización #' . (int)$quote->id . '.',
                [
                    'quote_id' => (int)$quote->id,
                ]
            );
        }

        return true;
    }

    public function markRejected(int $quoteId, ?int $adminId = null): bool
    {
        $quote = SalesQuote::find($quoteId);
        if (!$quote) {
            return false;
        }

        return $quote->update([
            'status' => 'rejected',
            'updated_by_admin_id' => $adminId,
        ]);
    }

    protected function normalize(array $payload): array
    {
        $currency = strtoupper(trim((string)($payload['currency'] ?? 'COP')));

        return [
            'title' => trim((string)($payload['title'] ?? '')),
            'summary' => trim((string)($payload['summary'] ?? '')),
            'amount' => (float)($payload['amount'] ?? 0),
            'currency' => $currency !== '' ? $currency : 'COP',
            'valid_until' => $this->normalizeDate($payload['valid_until'] ?? null),
        ];
    }

    protected function validate(array $data): array
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'][] = 'El título de la cotización es obligatorio.';
        }

        if ($data['amount'] <= 0) {
            $errors['amount'][] = 'El monto debe ser mayor a cero.';
        }

        if ($data['currency'] === '') {
            $errors['currency'][] = 'Selecciona una moneda válida.';
        } else {
            $currency = Currency::findByCode((string)$data['currency']);
            if (!$currency || (int)($currency->is_active ?? 0) !== 1) {
                $errors['currency'][] = 'Selecciona una moneda activa válida.';
            }
        }

        return $errors;
    }

    protected function normalizeDate($value): ?string
    {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
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
}