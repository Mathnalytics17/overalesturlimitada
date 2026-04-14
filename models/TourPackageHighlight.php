<?php

namespace app\Models;

use app\Core\Model;

class TourPackageHighlight extends Model
{
    protected string $table = 'tour_package_highlights';

    protected array $fillable = [
        'uuid',
        'tour_package_id',
        'title',
        'icon',
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