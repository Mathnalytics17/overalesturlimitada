<?php

namespace app\Models;

use app\Core\Model;

class PqrsCaseTask extends Model
{
    protected string $table = 'pqrs_case_tasks';

    protected array $fillable = [
        'pqrs_case_id',
        'admin_user_id',
        'title',
        'description',
        'status',
        'due_at',
        'completed_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'pqrs_case_id' => 'int',
        'admin_user_id' => 'int',
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