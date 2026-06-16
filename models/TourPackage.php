<?php

namespace app\Models;

use app\Core\Model;
use app\Core\Paginator;
use app\Core\QueryBuilder;

class TourPackage extends Model
{
    protected string $table = 'tour_packages';
    protected bool $softDeletes = true;

    protected array $fillable = [
        'uuid',
        'slug',
        'title',
        'subtitle',
        'short_description',
        'general_description',
        'location_name',
        'country_id',
        'city_id',
        'price_from',
        'currency_id',
        'currency',
        'duration_days',
        'duration_nights',
        'status',
        'is_featured',
        'is_popular',
        'sort_order',
        'cover_image_id',
        'published_at',
        'created_by_admin_id',
        'updated_by_admin_id',
    ];

    public static function findBySlug(string $slug): ?static
    {
        $row = static::query()
            ->where('slug', '=', $slug)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function adminList(?string $search = null): array
    {
        $rows = static::query()->get();
        $items = array_map(fn($row) => new static($row), $rows ?: []);

        if ($search) {
            $search = mb_strtolower(trim($search));
            $items = array_filter($items, function ($item) use ($search) {
                $haystack = mb_strtolower(
                    ($item->title ?? '') . ' ' .
                    ($item->location_name ?? '') . ' ' .
                    ($item->slug ?? '')
                );
                return str_contains($haystack, $search);
            });
        }

        usort($items, function ($a, $b) {
            $af = (int)($a->sort_order ?? 0);
            $bf = (int)($b->sort_order ?? 0);
            if ($af !== $bf) {
                return $af <=> $bf;
            }
            return (int)$b->id <=> (int)$a->id;
        });

        return array_values($items);
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
        $query = static::query()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC');

        if (!empty($filters['status'])) {
            $query->where('status', '=', (string) $filters['status']);
        }

        if (!empty($filters['q'])) {
            $query->whereAnyLike(['title', 'location_name', 'slug'], trim((string) $filters['q']));
        }

        return $query;
    }

    public static function publishedList(?string $search = null): array
    {
        $rows = static::query()
            ->where('status', '=', 'published')
            ->get();

        $items = array_map(fn($row) => new static($row), $rows ?: []);

        if ($search) {
            $search = mb_strtolower(trim($search));
            $items = array_filter($items, function ($item) use ($search) {
                $haystack = mb_strtolower(
                    ($item->title ?? '') . ' ' .
                    ($item->location_name ?? '') . ' ' .
                    ($item->short_description ?? '')
                );
                return str_contains($haystack, $search);
            });
        }

        usort($items, function ($a, $b) {
            $af = (int)($a->is_featured ?? 0);
            $bf = (int)($b->is_featured ?? 0);

            if ($af !== $bf) {
                return $bf <=> $af;
            }

            $as = (int)($a->sort_order ?? 0);
            $bs = (int)($b->sort_order ?? 0);

            if ($as !== $bs) {
                return $as <=> $bs;
            }

            return (int)$b->id <=> (int)$a->id;
        });

        return array_values($items);
    }

    public static function topRequestedFromSales(int $limit = 5): array
{
    $rows = \app\Models\SalesOpportunity::query()->get();
    $counts = [];

    foreach ($rows as $row) {
        $slug = trim((string)($row['package_slug'] ?? ''));
        if ($slug === '') {
            continue;
        }

        if (!isset($counts[$slug])) {
            $counts[$slug] = 0;
        }

        $counts[$slug]++;
    }

    arsort($counts);

    $result = [];
    foreach (array_slice($counts, 0, $limit, true) as $label => $count) {
        $result[] = [
            'label' => $label,
            'count' => $count,
        ];
    }

    return $result;
}
}
