<?php

namespace app\Models;

use app\Core\Model;

class SalesQuote extends Model
{
    protected string $table = 'sales_quotes';

    protected array $fillable = [
        'uuid',
        'sales_opportunity_id',
        'title',
        'summary',
        'amount',
        'currency',
        'valid_until',
        'sent_at',
        'status',
        'created_by_admin_id',
        'updated_by_admin_id',
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
}