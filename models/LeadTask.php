<?php

namespace app\Models;

use app\Core\Model;

class LeadTask extends Model
{
    protected string $table = 'crm_lead_tasks';

    protected array $fillable = [
        'lead_id',
        'admin_user_id',
        'title',
        'description',
        'status',
        'due_at',
        'completed_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'lead_id' => 'int',
        'admin_user_id' => 'int',
    ];

    public static function byLead(int $leadId): array
    {
        $rows = static::query()->where('lead_id', '=', $leadId)->orderBy('id', 'DESC')->get();
        return array_map(fn(array $row) => new static($row), $rows);
    }
}
