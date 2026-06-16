<?php

namespace app\Models;

use app\Core\Model;
use app\Core\Paginator;
use app\Core\QueryBuilder;

class TravelExperience extends Model
{
    protected string $table = 'travel_experiences';

    protected array $fillable = [
        'uuid',
        'customer_name',
        'customer_email',
        'customer_phone',
        'display_name',
        'title',
        'story',
        'rating',
        'city_destination',
        'country_destination',
        'experience_type',
        'package_id',
        'package_slug',
        'extra_service_id',
        'extra_service_slug',
        'lead_id',
        'sales_opportunity_id',
        'sales_order_id',
        'status',
        'is_featured',
        'admin_notes',
        'approved_at',
        'approved_by_admin_id',
        'rejected_at',
        'rejected_by_admin_id',
    ];

    protected array $casts = [
        'id' => 'int',
        'rating' => 'int',
        'package_id' => 'int',
        'extra_service_id' => 'int',
        'lead_id' => 'int',
        'sales_opportunity_id' => 'int',
        'sales_order_id' => 'int',
        'is_featured' => 'bool',
        'approved_by_admin_id' => 'int',
        'rejected_by_admin_id' => 'int',
    ];

    public static function approvedList(int $limit = 50): array
    {
        $rows = static::query()
            ->where('status', '=', 'approved')
            ->orderBy('is_featured', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function adminList(array $filters = [], int $limit = 100): array
    {
        $rows = static::filteredAdminQuery($filters)->limit($limit)->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function paginateAdmin(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        return Paginator::fromQuery(
            static::filteredAdminQuery($filters),
            $page,
            $perPage,
            fn(array $row) => new static($row)
        );
    }

    protected static function filteredAdminQuery(array $filters): QueryBuilder
    {
        $query = static::query()->orderBy('id', 'DESC');

        if (!empty($filters['status'])) {
            $query->where('status', '=', (string)$filters['status']);
        }

        if (!empty($filters['experience_type'])) {
            $query->where('experience_type', '=', (string)$filters['experience_type']);
        }

        if (!empty($filters['q'])) {
            $query->whereAnyLike(['customer_name', 'title', 'story', 'package_slug', 'extra_service_slug'], trim((string) $filters['q']));
        }

        return $query;
    }

    public static function countByStatus(string $status): int
    {
        $rows = static::query()->where('status', '=', $status)->get();
        return count($rows ?: []);
    }

    public static function featured(int $limit = 6): array
    {
        $rows = static::query()
            ->where('status', '=', 'approved')
            ->where('is_featured', '=', 1)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }


    public static function approvedByPackageSlug(string $packageSlug, int $limit = 6): array
{
    $rows = static::query()
        ->where('status', '=', 'approved')
        ->where('package_slug', '=', $packageSlug)
        ->orderBy('is_featured', 'DESC')
        ->orderBy('id', 'DESC')
        ->limit($limit)
        ->get();

    return array_map(fn($row) => new static($row), $rows ?: []);
}
}
