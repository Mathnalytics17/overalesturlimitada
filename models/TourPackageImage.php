<?php

namespace app\Models;

use app\Core\Model;

class TourPackageImage extends Model
{
    protected string $table = 'tour_package_images';
    protected bool $softDeletes = true;

    protected array $fillable = [
        'uuid',
        'tour_package_id',
        'image_path',
        'alt_text',
        'title',
        'sort_order',
        'is_cover',
    ];

    public static function byPackage(int $packageId): array
    {
        $rows = static::query()
            ->where('tour_package_id', '=', $packageId)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function coverByPackage(int $packageId): ?static
    {
        $row = static::query()
            ->where('tour_package_id', '=', $packageId)
            ->where('is_cover', '=', 1)
            ->first();

        return $row ? new static($row) : null;
    }
}