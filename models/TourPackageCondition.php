<?php

namespace app\Models;

use app\Core\Model;

class TourPackageCondition extends Model
{
    protected string $table = 'tour_package_conditions';

    protected array $fillable = [
        'uuid',
        'tour_package_id',
        'title',
        'content',
        'sort_order',
        'is_active',
    ];

    public static function byPackage(int $packageId): array
    {
        $rows = static::query()
            ->where('tour_package_id', '=', $packageId)
            ->where('is_active', '=', 1)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }
}