<?php

namespace app\Models;

use app\Core\Model;

class TourPackageTag extends Model
{
    protected string $table = 'tour_package_tags';

    protected array $fillable = [
        'uuid',
        'name',
        'slug',
        'color',
        'is_active',
    ];

    public static function activeList(): array
    {
        $rows = static::query()
            ->where('is_active', '=', 1)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }
}