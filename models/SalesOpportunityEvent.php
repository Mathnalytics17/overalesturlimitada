<?php

namespace app\Models;

use app\Core\Model;

class SalesOpportunityEvent extends Model
{
    protected string $table = 'sales_opportunity_events';

    protected array $fillable = [
        'uuid',
        'sales_opportunity_id',
        'admin_user_id',
        'event_type',
        'title',
        'message',
        'meta_json',
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