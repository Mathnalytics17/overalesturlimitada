<?php

namespace app\Services\Package;

use app\Models\CustomerPackageEvent;
use app\Models\CustomerPackageFavorite;
use app\Models\Lead;
use app\Models\TourPackage;

class PackageAnalyticsService
{
    public function report(?string $from = null, ?string $to = null, int $page = 1, int $perPage = 25): array
    {
        $from = $this->normalizeDate($from);
        $to = $this->normalizeDate($to);

        if ($from !== null && $to !== null && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $packages = TourPackage::adminList();
        $rows = [];

        foreach ($packages as $package) {
            $rows[(int) $package->id] = [
                'package' => $package,
                'views' => 0,
                'favorite_adds' => 0,
                'favorites_current' => 0,
                'inquiries' => 0,
                'favorite_rate' => 0.0,
                'inquiry_rate' => 0.0,
            ];
        }

        foreach (CustomerPackageEvent::all() as $event) {
            $packageId = (int) $event->tour_package_id;
            if (!isset($rows[$packageId]) || !$this->withinRange((string) $event->created_at, $from, $to)) {
                continue;
            }

            if ((string) $event->event_type === 'view') {
                $rows[$packageId]['views']++;
            }

            if ((string) $event->event_type === 'favorite_added') {
                $rows[$packageId]['favorite_adds']++;
            }
        }

        foreach (CustomerPackageFavorite::all() as $favorite) {
            $packageId = (int) $favorite->tour_package_id;
            if (isset($rows[$packageId])) {
                $rows[$packageId]['favorites_current']++;
            }
        }

        foreach (Lead::query()->where('source_type', '=', 'package')->get() as $lead) {
            $packageId = (int) ($lead['package_id'] ?? 0);
            if (!isset($rows[$packageId]) || !$this->withinRange((string) ($lead['created_at'] ?? ''), $from, $to)) {
                continue;
            }

            $rows[$packageId]['inquiries']++;
        }

        foreach ($rows as &$row) {
            $views = max(0, (int) $row['views']);
            $row['favorite_rate'] = $views > 0 ? round(((int) $row['favorite_adds'] / $views) * 100, 1) : 0.0;
            $row['inquiry_rate'] = $views > 0 ? round(((int) $row['inquiries'] / $views) * 100, 1) : 0.0;
        }
        unset($row);

        $rows = array_values($rows);
        usort($rows, static function (array $a, array $b): int {
            if ($a['views'] !== $b['views']) {
                return $b['views'] <=> $a['views'];
            }

            if ($a['inquiries'] !== $b['inquiries']) {
                return $b['inquiries'] <=> $a['inquiries'];
            }

            return (int) $b['package']->id <=> (int) $a['package']->id;
        });
        $total = count($rows);
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 25;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);
        $pagedRows = array_slice($rows, ($page - 1) * $perPage, $perPage);

        return [
            'from' => $from,
            'to' => $to,
            'summary' => [
                'views' => array_sum(array_column($rows, 'views')),
                'favorite_adds' => array_sum(array_column($rows, 'favorite_adds')),
                'favorites_current' => array_sum(array_column($rows, 'favorites_current')),
                'inquiries' => array_sum(array_column($rows, 'inquiries')),
            ],
            'rows' => $pagedRows,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => $lastPage,
                'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => min($page * $perPage, $total),
            ],
        ];
    }

    protected function normalizeDate(?string $date): ?string
    {
        $date = trim((string) $date);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
    }

    protected function withinRange(string $datetime, ?string $from, ?string $to): bool
    {
        if ($datetime === '') {
            return false;
        }

        $date = substr($datetime, 0, 10);

        if ($from !== null && $date < $from) {
            return false;
        }

        return $to === null || $date <= $to;
    }
}
