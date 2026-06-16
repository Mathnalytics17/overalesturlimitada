<?php

namespace app\Models;

use app\Core\Model;
use app\Core\Paginator;
use app\Core\QueryBuilder;

class Lead extends Model
{
    protected string $table = 'crm_leads';
    protected bool $softDeletes = true;

    protected array $fillable = [
        'customer_id',
        'full_name',
        'email',
        'phone',
        'source_type',
        'channel',
        'subject',
        'message',
        'package_id',
        'package_slug',
        'sales_opportunity_id',
        'status',
        'priority',
        'assigned_admin_user_id',
        'whatsapp_opt_in',
        'consent_accepted_at',
        'metadata_json',
        'last_contact_at',
        'closed_at',
    ];
protected array $casts = [
    'id' => 'int',
    'customer_id' => 'int',
    'package_id' => 'int',
    'sales_opportunity_id' => 'int',
    'assigned_admin_user_id' => 'int',
    'whatsapp_opt_in' => 'bool',
    'metadata_json' => 'array',
];

    public static function recent(int $limit = 50): array
    {
        $rows = static::query()->orderBy('id', 'DESC')->limit($limit)->get();
        return array_map(fn(array $row) => new static($row), $rows);
    }

    public static function byCustomer(int $customerId, int $limit = 50): array
    {
        $rows = static::query()
            ->where('customer_id', '=', $customerId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn(array $row) => new static($row), $rows ?: []);
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

        if (!empty($filters['source_type'])) {
            $query->where('source_type', '=', (string) $filters['source_type']);
        }

        if (!empty($filters['assigned_admin_user_id'])) {
            $query->where('assigned_admin_user_id', '=', (int) $filters['assigned_admin_user_id']);
        }

        if (!empty($filters['q'])) {
            $query->whereAnyLike(['full_name', 'email', 'phone', 'subject'], trim((string) $filters['q']));
        }

        return $query;
    }

    public static function countsByStatus(): array
    {
        $all = static::recent(500);
        $counts = [
            'new' => 0,
            'in_progress' => 0,
            'waiting_customer' => 0,
            'closed' => 0,
        ];

        foreach ($all as $lead) {
            $status = (string) ($lead->status ?? 'new');
            if (!isset($counts[$status])) {
                $counts[$status] = 0;
            }
            $counts[$status]++;
        }

        return $counts;
    }
public function salesOpportunity(): ?\app\Models\SalesOpportunity
{
    $id = (int)($this->sales_opportunity_id ?? 0);

    if ($id <= 0) {
        return null;
    }

    return \app\Models\SalesOpportunity::find($id);
}

public static function countToday(): int
{
    $today = date('Y-m-d');
    $rows = static::query()->get();

    $count = 0;
    foreach ($rows as $row) {
        $createdAt = (string)($row['created_at'] ?? '');
        if (str_starts_with($createdAt, $today)) {
            $count++;
        }
    }

    return $count;
}

public static function countThisMonth(): int
{
    $prefix = date('Y-m');
    $rows = static::query()->get();

    $count = 0;
    foreach ($rows as $row) {
        $createdAt = (string)($row['created_at'] ?? '');
        if (str_starts_with($createdAt, $prefix)) {
            $count++;
        }
    }

    return $count;
}

public static function countYesterday(): int
{
    $date = date('Y-m-d', strtotime('-1 day'));
    $rows = static::query()->get();

    $count = 0;
    foreach ($rows as $row) {
        $createdAt = (string)($row['created_at'] ?? '');
        if (str_starts_with($createdAt, $date)) {
            $count++;
        }
    }

    return $count;
}

public static function countLastMonth(): int
{
    $prefix = date('Y-m', strtotime('first day of last month'));
    $rows = static::query()->get();

    $count = 0;
    foreach ($rows as $row) {
        $createdAt = (string)($row['created_at'] ?? '');
        if (str_starts_with($createdAt, $prefix)) {
            $count++;
        }
    }

    return $count;
}
}
