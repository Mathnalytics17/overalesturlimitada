<?php

namespace app\Models;

use app\Core\Model;

class SalesPayment extends Model
{
    protected string $table = 'sales_payments';

    protected array $fillable = [
        'uuid',
        'sales_opportunity_id',
        'payment_kind',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'proof_file_path',
        'status',
        'reported_by_admin_id',
        'verified_by_admin_id',
        'reported_at',
        'verified_at',
        'notes',
    ];

    public static function byOpportunity(int $opportunityId): array
    {
        $rows = static::query()
            ->where('sales_opportunity_id', '=', $opportunityId)
            ->get();

        $items = array_map(fn($row) => new static($row), $rows ?: []);

        usort($items, fn($a, $b) => (int)$b->id <=> (int)$a->id);

        return array_values($items);
    }

    public static function sumVerifiedByOpportunity(int $opportunityId): float
    {
        $items = static::byOpportunity($opportunityId);

        $sum = 0.0;
        foreach ($items as $item) {
            if ((string)($item->status ?? '') === 'verified') {
                $sum += (float)($item->amount ?? 0);
            }
        }

        return $sum;
    }

    public static function sumVerifiedThisMonth(): float
{
    $prefix = date('Y-m');
    $rows = static::query()->get();
    $sum = 0;

    foreach ($rows as $row) {
        if ((string)($row['status'] ?? '') !== 'verified') {
            continue;
        }

        $verifiedAt = (string)($row['verified_at'] ?? '');
        if ($verifiedAt !== '' && str_starts_with($verifiedAt, $prefix)) {
            $sum += (float)($row['amount'] ?? 0);
        }
    }

    return $sum;
}

public static function sumVerifiedAll(): float
{
    $rows = static::query()->get();
    $sum = 0;

    foreach ($rows as $row) {
        if ((string)($row['status'] ?? '') === 'verified') {
            $sum += (float)($row['amount'] ?? 0);
        }
    }

    return $sum;
}

public static function pendingValidation(int $limit = 5): array
{
    $rows = static::query()->get();

    $items = array_filter(array_map(fn($row) => new static($row), $rows ?: []), function ($item) {
        return (string)($item->status ?? '') === 'reported';
    });

    usort($items, fn($a, $b) => (int)$b->id <=> (int)$a->id);

    return array_slice(array_values($items), 0, $limit);
}

public static function sumVerifiedLastMonth(): float
{
    $prefix = date('Y-m', strtotime('first day of last month'));
    $rows = static::query()->get();
    $sum = 0;

    foreach ($rows as $row) {
        if ((string)($row['status'] ?? '') !== 'verified') {
            continue;
        }

        $verifiedAt = (string)($row['verified_at'] ?? '');
        if ($verifiedAt !== '' && str_starts_with($verifiedAt, $prefix)) {
            $sum += (float)($row['amount'] ?? 0);
        }
    }

    return $sum;
}
}