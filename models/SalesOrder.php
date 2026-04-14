<?php

namespace app\Models;

use app\Core\Model;

class SalesOrder extends Model
{
    protected string $table = 'sales_orders';

    protected array $fillable = [
        'uuid',
        'sales_opportunity_id',
        'lead_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'product_type',
        'package_id',
        'package_slug',
        'extra_service_id',
        'extra_service_slug',
        'total_amount',
        'currency',
        'paid_amount',
        'balance_amount',
        'commercial_status',
        'operational_status',
        'travelers_count',
        'travel_date_estimate',
        'return_date_estimate',
        'assigned_admin_user_id',
        'notes',
        'created_by_admin_id',
        'updated_by_admin_id',
    ];

    public static function findByOpportunityId(int $opportunityId): ?static
    {
        $row = static::query()
            ->where('sales_opportunity_id', '=', $opportunityId)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function adminList(?string $search = null): array
    {
        $rows = static::query()->get();
        $items = array_map(fn($row) => new static($row), $rows ?: []);

        if ($search) {
            $search = mb_strtolower(trim($search));
            $items = array_filter($items, function ($item) use ($search) {
                $haystack = mb_strtolower(
                    ($item->order_number ?? '') . ' ' .
                    ($item->customer_name ?? '') . ' ' .
                    ($item->customer_phone ?? '') . ' ' .
                    ($item->customer_email ?? '') . ' ' .
                    ($item->package_slug ?? '')
                );

                return str_contains($haystack, $search);
            });
        }

        usort($items, fn($a, $b) => (int)$b->id <=> (int)$a->id);

        return array_values($items);
    }
public static function pendingOperational(int $limit = 5): array
{
    $rows = static::query()->get();

    $items = array_filter(array_map(fn($row) => new static($row), $rows ?: []), function ($item) {
        return !in_array((string)($item->operational_status ?? ''), ['completed', 'cancelled'], true);
    });

    usort($items, fn($a, $b) => (int)$b->id <=> (int)$a->id);

    return array_slice(array_values($items), 0, $limit);
}

public static function sumOpenBalance(): float
{
    $rows = static::query()->get();
    $sum = 0;

    foreach ($rows as $row) {
        if (!in_array((string)($row['commercial_status'] ?? ''), ['cancelled'], true)) {
            $sum += (float)($row['balance_amount'] ?? 0);
        }
    }

    return $sum;
}
    
}