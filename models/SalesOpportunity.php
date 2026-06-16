<?php

namespace app\Models;

use app\Core\Model;
use app\Core\Paginator;

class SalesOpportunity extends Model
{
    protected string $table = 'sales_opportunities';
    protected bool $softDeletes = true;

    protected array $fillable = [
    'uuid',
    'lead_id',
    'source_origin',
    'source_channel',
    'source_reference',
    'assigned_admin_user_id',
    'customer_name',
    'customer_phone',
    'customer_email',
    'interest_type',
    'package_id',
    'package_slug',
    'extra_service_id',
    'extra_service_slug',
    'sales_stage',
    'sales_temperature',
    'closing_probability',
    'travelers_count',
    'travel_date_estimate',
    'return_date_estimate',
    'budget_min',
    'budget_max',
    'quoted_amount',
    'quoted_currency',
    'next_follow_up_at',
    'last_contact_at',
    'won_at',
    'lost_at',
    'cancelled_at',
    'lost_reason',
    'lost_reason_code',
    'lost_reason_detail',
    'notes_summary',
    'created_by_admin_id',
    'updated_by_admin_id',
];
    public static function findByLeadId(int $leadId): ?static
    {
        $row = static::query()
            ->where('lead_id', '=', $leadId)
            ->first();

        return $row ? new static($row) : null;
    }

   public static function adminList(?string $search = null, ?string $stage = null, ?int $assignedAdminId = null): array
{
    $query = static::query();

    if ($stage !== null && $stage !== '') {
        $query->where('sales_stage', '=', $stage);
    }

    if ($assignedAdminId !== null) {
        $query->where('assigned_admin_user_id', '=', $assignedAdminId);
    }

    $rows = $query->get();
    $items = array_map(fn($row) => new static($row), $rows ?: []);

    if ($search) {
        $search = mb_strtolower(trim($search));
        $items = array_filter($items, function ($item) use ($search) {
            $haystack = mb_strtolower(
                ($item->customer_name ?? '') . ' ' .
                ($item->customer_phone ?? '') . ' ' .
                ($item->customer_email ?? '') . ' ' .
                ($item->interest_type ?? '') . ' ' .
                ($item->package_slug ?? '')
            );

            return str_contains($haystack, $search);
        });
    }

    usort($items, function ($a, $b) {
        $aFollow = strtotime((string)($a->next_follow_up_at ?? '')) ?: PHP_INT_MAX;
        $bFollow = strtotime((string)($b->next_follow_up_at ?? '')) ?: PHP_INT_MAX;

        if ($aFollow !== $bFollow) {
            return $aFollow <=> $bFollow;
        }

        return (int)$b->id <=> (int)$a->id;
    });

    return array_values($items);
}

    public static function dueFollowUps(): array
    {
        $now = date('Y-m-d H:i:s');

        $rows = static::query()
            ->where('next_follow_up_at', '<=', $now)
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

public static function groupedByStage(?string $search = null, ?int $assignedAdminId = null): array
{
    $items = static::adminList($search, null, $assignedAdminId);

    $groups = [
        'new' => [],
        'contacted' => [],
        'profiled' => [],
        'quoted' => [],
        'follow_up' => [],
        'pending_payment' => [],
        'payment_reported' => [],
        'payment_validated' => [],
        'won' => [],
        'lost' => [],
        'cancelled' => [],
    ];

    foreach ($items as $item) {
        $stage = (string)($item->sales_stage ?? 'new');

        if (!isset($groups[$stage])) {
            $groups[$stage] = [];
        }

        $groups[$stage][] = $item;
    }

    return $groups;
}

public static function countActive(): int
{
    $rows = static::query()->get();
    $count = 0;

    foreach ($rows as $row) {
        $stage = (string)($row['sales_stage'] ?? '');
        if (!in_array($stage, ['won', 'lost', 'cancelled'], true)) {
            $count++;
        }
    }

    return $count;
}

public static function countWonThisMonth(): int
{
    $prefix = date('Y-m');
    $rows = static::query()->get();
    $count = 0;

    foreach ($rows as $row) {
        if ((string)($row['sales_stage'] ?? '') !== 'won') {
            continue;
        }

        $wonAt = (string)($row['won_at'] ?? '');
        if ($wonAt !== '' && str_starts_with($wonAt, $prefix)) {
            $count++;
        }
    }

    return $count;
}

public static function countByStage(): array
{
    $rows = static::query()->get();
    $counts = [];

    foreach ($rows as $row) {
        $stage = (string)($row['sales_stage'] ?? 'new');
        if (!isset($counts[$stage])) {
            $counts[$stage] = 0;
        }
        $counts[$stage]++;
    }

    return $counts;
}

public static function followUpsOverdue(int $limit = 5): array
{
    $now = date('Y-m-d H:i:s');
    $rows = static::query()->get();

    $items = array_filter(array_map(fn($row) => new static($row), $rows ?: []), function ($item) use ($now) {
        $next = (string)($item->next_follow_up_at ?? '');
        $stage = (string)($item->sales_stage ?? '');
        return $next !== ''
            && $next < $now
            && !in_array($stage, ['won', 'lost', 'cancelled'], true);
    });

    usort($items, fn($a, $b) => strcmp((string)($a->next_follow_up_at ?? ''), (string)($b->next_follow_up_at ?? '')));

    return array_slice(array_values($items), 0, $limit);
}

public static function unassigned(int $limit = 5): array
{
    $rows = static::query()->get();

    $items = array_filter(array_map(fn($row) => new static($row), $rows ?: []), function ($item) {
        $stage = (string)($item->sales_stage ?? '');
        return empty($item->assigned_admin_user_id)
            && !in_array($stage, ['won', 'lost', 'cancelled'], true);
    });

    usort($items, fn($a, $b) => (int)$b->id <=> (int)$a->id);

    return array_slice(array_values($items), 0, $limit);
}

public static function topInterestTypes(int $limit = 5): array
{
    $rows = static::query()->get();
    $counts = [];

    foreach ($rows as $row) {
        $label = trim((string)($row['interest_type'] ?? 'Sin tipo'));
        if ($label === '') {
            $label = 'Sin tipo';
        }

        if (!isset($counts[$label])) {
            $counts[$label] = 0;
        }

        $counts[$label]++;
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

public static function sumQuotedOpen(): float
{
    $rows = static::query()->get();
    $sum = 0;

    foreach ($rows as $row) {
        $stage = (string)($row['sales_stage'] ?? '');
        if (in_array($stage, ['won', 'lost', 'cancelled'], true)) {
            continue;
        }

        $sum += (float)($row['quoted_amount'] ?? 0);
    }

    return $sum;
}

public static function countWonLastMonth(): int
{
    $prefix = date('Y-m', strtotime('first day of last month'));
    $rows = static::query()->get();
    $count = 0;

    foreach ($rows as $row) {
        if ((string)($row['sales_stage'] ?? '') !== 'won') {
            continue;
        }

        $wonAt = (string)($row['won_at'] ?? '');
        if ($wonAt !== '' && str_starts_with($wonAt, $prefix)) {
            $count++;
        }
    }

    return $count;
}

    public static function paginateAdmin(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $query = static::query()->orderBy('id', 'DESC');

        if (!empty($filters['stage'])) {
            $query->where('sales_stage', '=', (string) $filters['stage']);
        }

        if (!empty($filters['assigned_admin_id'])) {
            $query->where('assigned_admin_user_id', '=', (int) $filters['assigned_admin_id']);
        }

        if (!empty($filters['q'])) {
            $query->whereAnyLike(
                ['customer_name', 'customer_phone', 'customer_email', 'interest_type', 'package_slug', 'extra_service_slug'],
                trim((string) $filters['q'])
            );
        }

        return Paginator::fromQuery(
            $query,
            $page,
            $perPage,
            fn(array $row) => new static($row)
        );
    }
}
