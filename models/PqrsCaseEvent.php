<?php

namespace app\Models;

use app\Core\Model;

class PqrsCaseEvent extends Model
{
    protected string $table = 'pqrs_case_events';

    protected array $fillable = [
        'pqrs_case_id',
        'admin_user_id',
        'event_type',
        'message',
        'meta_json',
    ];

    protected array $casts = [
        'id' => 'int',
        'pqrs_case_id' => 'int',
        'admin_user_id' => 'int',
        'meta_json' => 'array',
    ];

    public static function byCase(int $caseId): array
    {
        $rows = static::query()
            ->where('pqrs_case_id', '=', $caseId)
            ->orderBy('id', 'DESC')
            ->get();

        return array_map(fn(array $row) => new static($row), $rows);
    }
}