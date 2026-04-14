<?php

namespace app\Models;

use app\Core\Model;

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
        $query = static::query()->orderBy('id', 'DESC')->limit($limit);

        if (!empty($filters['status'])) {
            $query->where('status', '=', (string) $filters['status']);
        }

        if (!empty($filters['request_type'])) {
            $query->where('request_type', '=', (string) $filters['request_type']);
        }

        if (!empty($filters['assigned_admin_user_id'])) {
            $query->where('assigned_admin_user_id', '=', (int) $filters['assigned_admin_user_id']);
        }

        $rows = $query->get();

        if (!empty($filters['q'])) {
            $needle = mb_strtolower((string) $filters['q']);
            $rows = array_values(array_filter($rows, static function (array $row) use ($needle): bool {
                $haystack = mb_strtolower(trim(
                    ($row['radicado'] ?? '') . ' ' .
                    ($row['full_name'] ?? '') . ' ' .
                    ($row['email'] ?? '') . ' ' .
                    ($row['phone'] ?? '') . ' ' .
                    ($row['subject'] ?? '')
                ));

                return $haystack !== '' && str_contains($haystack, $needle);
            }));
        }

        return array_map(fn(array $row) => new static($row), $rows);
    }

    public static function countOpen(): int
{
    return static::countByStatus('open');
}

public static function countByStatus(string $status): int
{
    $rows = static::query()->get();
    $count = 0;

    foreach ($rows as $row) {
        if ((string)($row['status'] ?? '') === $status) {
            $count++;
        }
    }

    return $count;
}

public static function recentOpen(int $limit = 5): array
{
    $rows = static::query()->get();

    $items = array_filter(array_map(fn($row) => new static($row), $rows ?: []), function ($item) {
        return (string)($item->status ?? '') === 'open';
    });

    usort($items, fn($a, $b) => (int)$b->id <=> (int)$a->id);

    return array_slice(array_values($items), 0, $limit);
}
}