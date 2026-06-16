<?php

namespace app\Models;

use app\Core\Database;
use app\Core\Model;

class CustomerPackageNotification extends Model
{
    protected string $table = 'customer_package_notifications';

    protected array $fillable = [
        'customer_id',
        'tour_package_id',
        'notification_type',
        'status',
        'attempts',
        'last_error',
        'queued_at',
        'sent_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'customer_id' => 'int',
        'tour_package_id' => 'int',
        'attempts' => 'int',
    ];

    public static function findExisting(int $customerId, int $packageId, string $type): ?static
    {
        $row = static::query()
            ->where('customer_id', '=', $customerId)
            ->where('tour_package_id', '=', $packageId)
            ->where('notification_type', '=', $type)
            ->first();

        return $row ? new static($row) : null;
    }


    public static function findExistingForPackage(int $customerId, int $packageId): ?static
    {
        $row = static::query()
            ->where('customer_id', '=', $customerId)
            ->where('tour_package_id', '=', $packageId)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function statusCounts(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT status, COUNT(*) AS total FROM customer_package_notifications GROUP BY status');
        $rows = $stmt ? $stmt->fetchAll() : [];

        $counts = ['pending' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        foreach ($rows ?: [] as $row) {
            $status = (string) ($row['status'] ?? '');
            if ($status === '') {
                continue;
            }
            $counts[$status] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    public static function pending(int $limit = 50): array
    {
        $rows = static::query()
            ->where('status', '=', 'pending')
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->get();

        return array_map(fn(array $row) => new static($row), $rows ?: []);
    }

    public static function deleteByCustomer(int $customerId): bool
    {
        return static::rawQuery()
            ->where('customer_id', '=', $customerId)
            ->delete();
    }
}
