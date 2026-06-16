<?php

namespace app\Models;

use app\Core\Model;
use app\Core\Paginator;
use app\Core\QueryBuilder;

class PqrsCase extends Model
{
    protected string $table = 'pqrs_cases';
    protected bool $softDeletes = true;

    protected array $fillable = [
        'radicado',
        'full_name',
        'email',
        'phone',
        'request_type',
        'subject',
        'message',
        'status',
        'priority',
        'assigned_admin_user_id',
        'has_attachments',
        'consent_accepted_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'last_contact_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'assigned_admin_user_id' => 'int',
        'has_attachments' => 'bool',
    ];

    public static function recent(int $limit = 100): array
    {
        $rows = static::query()
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn(array $row) => new static($row), $rows);
    }

    public static function filter(array $filters = [], int $limit = 100): array
    {
        $rows = static::filteredQuery($filters)->limit($limit)->get();

        return array_map(fn(array $row) => new static($row), $rows);
    }

    public static function paginate(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        return Paginator::fromQuery(
            static::filteredQuery($filters),
            $page,
            $perPage,
            fn(array $row) => new static($row)
        );
    }

    protected static function filteredQuery(array $filters): QueryBuilder
    {
        $query = static::query()->orderBy('id', 'DESC');

        if (!empty($filters['status'])) {
            $query->where('status', '=', (string) $filters['status']);
        }

        if (!empty($filters['request_type'])) {
            $query->where('request_type', '=', (string) $filters['request_type']);
        }

        if (!empty($filters['assigned_admin_user_id'])) {
            $query->where('assigned_admin_user_id', '=', (int) $filters['assigned_admin_user_id']);
        }

        if (!empty($filters['q'])) {
            $query->whereAnyLike(['radicado', 'full_name', 'email', 'phone', 'subject'], trim((string) $filters['q']));
        }

        return $query;
    }

    public const OPEN_STATUSES = ['new', 'in_progress', 'waiting_customer'];

    public static function countOpen(): int
    {
        return static::query()
            ->whereIn('status', self::OPEN_STATUSES)
            ->count();
    }

    public static function countByStatus(string $status): int
    {
        if ($status === 'open') {
            return static::countOpen();
        }

        return static::query()
            ->where('status', '=', $status)
            ->count();
    }

    public static function recentOpen(int $limit = 5): array
    {
        $rows = static::query()
            ->whereIn('status', self::OPEN_STATUSES)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

}
