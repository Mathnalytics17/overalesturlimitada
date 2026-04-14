<?php

namespace app\Models;

use app\Core\Model;

class PqrsAttachment extends Model
{
    protected string $table = 'pqrs_attachments';
    protected bool $timestamps = false;

    protected array $fillable = [
        'pqrs_case_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'created_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'pqrs_case_id' => 'int',
        'file_size' => 'int',
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