<?php

namespace app\Models;

use app\Core\Model;

class TourPackageItinerary extends Model
{
    protected string $table = 'tour_package_itineraries';

    protected array $fillable = [
        'uuid',
        'tour_package_id',
        'day_number',
        'title',
        'content',
        'sort_order',
    ];

    public static function byPackage(int $packageId): array
    {
        $rows = static::query()
            ->where('tour_package_id', '=', $packageId)
            ->get();

        $items = array_map(fn($row) => new static($row), $rows ?: []);

        usort($items, function ($a, $b) {
            $as = (int)($a->sort_order ?? 0);
            $bs = (int)($b->sort_order ?? 0);

            if ($as !== $bs) {
                return $as <=> $bs;
            }

            return (int)($a->day_number ?? 0) <=> (int)($b->day_number ?? 0);
        });

        return array_values($items);
    }
}