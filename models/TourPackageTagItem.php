<?php

namespace app\Models;

use app\Core\Model;

class TourPackageTagItem extends Model
{
    protected string $table = 'tour_package_tag_items';

    protected array $fillable = [
        'tour_package_id',
        'tag_id',
    ];

    public static function byPackage(int $packageId): array
    {
        $rows = static::query()
            ->where('tour_package_id', '=', $packageId)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }
}