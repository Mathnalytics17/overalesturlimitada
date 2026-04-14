<?php

namespace app\Models;

use app\Core\Model;

class TourPackageInclusion extends Model
{
    protected string $table = 'tour_package_inclusions';

    protected array $fillable = [
        'uuid',
        'tour_package_id',
        'type',
        'title',
        'content',
        'sort_order',
        'is_active',
    ];

    public static function byPackageAndType(int $packageId, string $type): array
    {
        $rows = static::query()
            ->where('tour_package_id', '=', $packageId)
            ->where('type', '=', $type)
            ->where('is_active', '=', 1)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }
}