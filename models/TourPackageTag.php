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
            ->orderBy('name', 'ASC')
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function adminList(array $filters = []): array
    {
        $query = static::query()
            ->orderBy('is_active', 'DESC')
            ->orderBy('name', 'ASC');

        if (($filters['status'] ?? '') !== '') {
            $query->where('is_active', '=', (string) $filters['status'] === 'active' ? 1 : 0);
        }

        if (!empty($filters['q'])) {
            $query->whereAnyLike(['name', 'slug'], trim((string) $filters['q']));
        }

        $rows = $query->get();
        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function findBySlug(string $slug): ?static
    {
        $row = static::query()
            ->where('slug', '=', $slug)
            ->first();

        return $row ? new static($row) : null;
    }
}
