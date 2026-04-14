<?php

namespace app\Models;

use app\Core\Model;

class LeadInteraction extends Model
{
    protected string $table = 'crm_lead_interactions';

    protected array $fillable = [
        'lead_id',
        'admin_user_id',
        'channel',
        'direction',
        'event_type',
        'message',
        'meta_json',
    ];

    protected array $casts = [
        'id' => 'int',
        'lead_id' => 'int',
        'admin_user_id' => 'int',
        'meta_json' => 'array',
    ];

    public static function byLead(int $leadId): array
    {
        $rows = static::query()->where('lead_id', '=', $leadId)->orderBy('id', 'DESC')->get();
        return array_map(fn(array $row) => new static($row), $rows);
    }
}
