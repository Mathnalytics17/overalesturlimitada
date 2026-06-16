<?php

namespace app\Models;

use app\Core\Model;

class CustomerTravelPreference extends Model
{
    protected string $table = 'customer_travel_preferences';

    protected array $fillable = [
        'customer_id',
        'preferred_tag_slugs_json',
        'desired_destinations',
        'budget_min',
        'budget_max',
        'usual_travelers',
        'notify_new_packages',
        'notify_recommendations',
    ];

    protected array $casts = [
        'id' => 'int',
        'customer_id' => 'int',
        'preferred_tag_slugs_json' => 'array',
        'budget_min' => 'float',
        'budget_max' => 'float',
        'usual_travelers' => 'int',
        'notify_new_packages' => 'bool',
        'notify_recommendations' => 'bool',
    ];

    public static function findByCustomer(int $customerId): ?static
    {
        $row = static::query()
            ->where('customer_id', '=', $customerId)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function saveForCustomer(int $customerId, array $data): ?static
    {
        $preference = static::findByCustomer($customerId);

        if ($preference) {
            return $preference->update($data) ? $preference : null;
        }

        return static::create(array_merge($data, ['customer_id' => $customerId]));
    }

    public static function deleteByCustomer(int $customerId): bool
    {
        return static::rawQuery()
            ->where('customer_id', '=', $customerId)
            ->delete();
    }

    public static function subscribedTo(string $field): array
    {
        if (!in_array($field, ['notify_new_packages', 'notify_recommendations'], true)) {
            return [];
        }

        $rows = static::query()
            ->where($field, '=', 1)
            ->get();

        return array_map(fn(array $row) => new static($row), $rows ?: []);
    }

    public static function subscribedToRecommendations(): array
    {
        return static::subscribedTo('notify_recommendations');
    }
}
