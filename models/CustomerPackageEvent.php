<?php

namespace app\Models;

use app\Core\Model;

class CustomerPackageEvent extends Model
{
    protected string $table = 'customer_package_events';

    protected array $fillable = [
        'customer_id',
        'tour_package_id',
        'event_type',
        'visitor_key',
        'metadata_json',
    ];

    protected array $casts = [
        'id' => 'int',
        'customer_id' => 'int',
        'tour_package_id' => 'int',
        'metadata_json' => 'array',
    ];

    public static function byCustomer(int $customerId, int $limit = 100): array
    {
        $rows = static::query()
            ->where('customer_id', '=', $customerId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn(array $row) => new static($row), $rows ?: []);
    }

    public static function viewCounts(): array
    {
        $rows = static::query()
            ->where('event_type', '=', 'view')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $packageId = (int) ($row['tour_package_id'] ?? 0);
            if ($packageId > 0) {
                $counts[$packageId] = ($counts[$packageId] ?? 0) + 1;
            }
        }

        return $counts;
    }

    public static function deleteByCustomer(int $customerId): bool
    {
        return static::rawQuery()
            ->where('customer_id', '=', $customerId)
            ->delete();
    }
}
