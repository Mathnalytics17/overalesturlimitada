<?php

namespace app\Core;

class Paginator
{
    public static function fromQuery(QueryBuilder $query, int $page = 1, int $perPage = 25, ?callable $mapper = null): array
    {
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 25;
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);

        $rows = (clone $query)
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return [
            'items' => $mapper ? array_map($mapper, $rows ?: []) : ($rows ?: []),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
        ];
    }
}
