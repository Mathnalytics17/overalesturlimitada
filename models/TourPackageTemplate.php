<?php

namespace app\Models;

use app\Core\Model;

class TourPackageTemplate extends Model
{
    protected string $table = 'tour_package_templates';

    protected array $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'payload_json',
        'is_active',
        'created_by_admin_id',
        'updated_by_admin_id',
    ];

    public static function activeList(): array
    {
        try {
            $rows = static::query()
                ->where('is_active', '=', 1)
                ->orderBy('name', 'ASC')
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function adminList(array $filters = []): array
    {
        try {
            $query = static::query();

            $q = trim((string) ($filters['q'] ?? ''));
            if ($q !== '') {
                $query->whereAnyLike(['name', 'slug', 'description'], $q);
            }

            $status = trim((string) ($filters['status'] ?? ''));
            if ($status === 'active') {
                $query->where('is_active', '=', 1);
            } elseif ($status === 'inactive') {
                $query->where('is_active', '=', 0);
            }

            $rows = $query->orderBy('is_active', 'DESC')->orderBy('name', 'ASC')->get();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function findBySlug(string $slug): ?static
    {
        try {
            $row = static::query()
                ->where('slug', '=', $slug)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }

        return $row ? new static($row) : null;
    }

    public function payload(): array
    {
        $payload = json_decode((string) ($this->payload_json ?? ''), true);
        return is_array($payload) ? $payload : [];
    }
}
