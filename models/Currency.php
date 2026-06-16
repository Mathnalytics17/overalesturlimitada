<?php

namespace app\Models;

use app\Core\Database;
use app\Core\Model;
use app\Core\QueryBuilder;
use PDO;

class Currency extends Model
{
    protected string $table = 'currencies';

    protected array $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'sort_order',
    ];

    public static function activeList(): array
    {
        $rows = static::query()
            ->where('is_active', '=', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('code', 'ASC')
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function adminList(array $filters = []): array
    {
        $query = static::query()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('code', 'ASC');

        if (($filters['status'] ?? '') === 'active') {
            $query->where('is_active', '=', 1);
        }

        if (($filters['status'] ?? '') === 'inactive') {
            $query->where('is_active', '=', 0);
        }

        $rows = $query->get();
        $items = array_map(fn($row) => new static($row), $rows ?: []);

        $q = mb_strtolower(trim((string)($filters['q'] ?? '')));
        if ($q !== '') {
            $items = array_values(array_filter($items, function ($item) use ($q) {
                $haystack = mb_strtolower(
                    (string)($item->code ?? '') . ' ' .
                    (string)($item->name ?? '') . ' ' .
                    (string)($item->symbol ?? '')
                );

                return str_contains($haystack, $q);
            }));
        }

        return $items;
    }

    public static function findByCode(string $code): ?static
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        $row = static::query()
            ->where('code', '=', $code)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function idByCode(string $code): ?int
    {
        $currency = static::findByCode($code);
        return $currency ? (int)$currency->id : null;
    }

    public static function usageCounts(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query(
            "SELECT currency_id, COUNT(*) AS total
             FROM tour_packages
             WHERE deleted_at IS NULL AND currency_id IS NOT NULL
             GROUP BY currency_id"
        );

        $counts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $counts[(int)$row['currency_id']] = (int)$row['total'];
        }

        return $counts;
    }

    public static function seedDefaults(): int
    {
        $defaults = [
            ['COP', 'Peso colombiano', '$', 1],
            ['USD', 'Dólar estadounidense', 'US$', 2],
            ['EUR', 'Euro', '€', 3],
            ['MXN', 'Peso mexicano', 'MX$', 4],
            ['BRL', 'Real brasileño', 'R$', 5],
            ['GBP', 'Libra esterlina', '£', 6],
            ['CAD', 'Dólar canadiense', 'CA$', 7],
        ];

        $created = 0;
        foreach ($defaults as [$code, $name, $symbol, $order]) {
            if (static::findByCode($code)) {
                continue;
            }

            if (static::create([
                'code' => $code,
                'name' => $name,
                'symbol' => $symbol,
                'is_active' => 1,
                'sort_order' => $order,
            ])) {
                $created++;
            }
        }

        return $created;
    }
}
