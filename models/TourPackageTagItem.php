<?php

namespace app\Models;

use app\Core\Model;

class TourPackageTagItem extends Model
{
    protected string $table = 'tour_package_tag_items';
    protected bool $timestamps = false;

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

    public static function countByTagId(int $tagId): int
    {
        return static::query()
            ->where('tag_id', '=', $tagId)
            ->count();
    }

    public static function usageCounts(): array
    {
        $rows = static::query()->get();
        $counts = [];

        foreach ($rows ?: [] as $row) {
            $tagId = (int) ($row['tag_id'] ?? 0);
            if ($tagId <= 0) {
                continue;
            }

            $counts[$tagId] = ($counts[$tagId] ?? 0) + 1;
        }

        return $counts;
    }
}
