<?php

namespace app\Models;

use app\Core\Model;

class CustomerPackageFavorite extends Model
{
    protected string $table = 'customer_package_favorites';

    protected array $fillable = [
        'customer_id',
        'tour_package_id',
    ];

    protected array $casts = [
        'id' => 'int',
        'customer_id' => 'int',
        'tour_package_id' => 'int',
    ];

    public static function findForCustomerAndPackage(int $customerId, int $packageId): ?static
    {
        $row = static::query()
            ->where('customer_id', '=', $customerId)
            ->where('tour_package_id', '=', $packageId)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function byCustomer(int $customerId): array
    {
        $rows = static::query()
            ->where('customer_id', '=', $customerId)
            ->orderBy('id', 'DESC')
            ->get();

        return array_map(fn(array $row) => new static($row), $rows ?: []);
    }

    public static function packageIdsByCustomer(int $customerId): array
    {
        return array_map(
            fn(CustomerPackageFavorite $favorite): int => (int) $favorite->tour_package_id,
            static::byCustomer($customerId)
        );
    }

    public static function deleteByCustomer(int $customerId): bool
    {
        return static::rawQuery()
            ->where('customer_id', '=', $customerId)
            ->delete();
    }
}
