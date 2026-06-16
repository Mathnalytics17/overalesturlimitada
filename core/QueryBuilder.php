<?php

namespace app\Core;

use PDO;
use PDOException;

class QueryBuilder
{
    protected PDO $pdo;
    protected string $table;
    protected string $select = '*';
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orderBy = [];
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function select(string $select): static
    {
        $this->select = $select;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): static
    {
        $param = $this->newParamName('w');
        $this->wheres[] = [
            'boolean' => 'AND',
            'sql' => "{$column} {$operator} {$param}",
        ];
        $this->bindings[$param] = $value;

        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): static
    {
        $param = $this->newParamName('w');
        $this->wheres[] = [
            'boolean' => 'OR',
            'sql' => "{$column} {$operator} {$param}",
        ];
        $this->bindings[$param] = $value;

        return $this;
    }

    public function whereNull(string $column): static
    {
        $this->wheres[] = [
            'boolean' => 'AND',
            'sql' => "{$column} IS NULL",
        ];

        return $this;
    }

    public function whereNotNull(string $column): static
    {
        $this->wheres[] = [
            'boolean' => 'AND',
            'sql' => "{$column} IS NOT NULL",
        ];

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        if (empty($values)) {
            $this->wheres[] = [
                'boolean' => 'AND',
                'sql' => '1 = 0',
            ];
            return $this;
        }

        $params = [];
        foreach ($values as $value) {
            $param = $this->newParamName('in');
            $params[] = $param;
            $this->bindings[$param] = $value;
        }

        $this->wheres[] = [
            'boolean' => 'AND',
            'sql' => "{$column} IN (" . implode(', ', $params) . ")",
        ];

        return $this;
    }

    public function whereAnyLike(array $columns, string $value): static
    {
        $parts = [];

        foreach ($columns as $column) {
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', (string) $column)) {
                continue;
            }

            $param = $this->newParamName('like');
            $parts[] = "{$column} LIKE {$param}";
            $this->bindings[$param] = '%' . $value . '%';
        }

        if ($parts !== []) {
            $this->wheres[] = [
                'boolean' => 'AND',
                'sql' => '(' . implode(' OR ', $parts) . ')',
            ];
        }

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limitValue = max(0, $limit);
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offsetValue = max(0, $offset);
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelectSql();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        if ($this->limitValue === null) {
            $this->limit(1);
        }

        $sql = $this->buildSelectSql();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function count(): int
    {
        $originalSelect = $this->select;
        $this->select = 'COUNT(*) as aggregate';

        $sql = $this->buildSelectSql();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        $row = $stmt->fetch();
        $this->select = $originalSelect;

        return (int) ($row['aggregate'] ?? 0);
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $params = array_map(fn($column) => ':' . $column, $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $params) . ")";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $setParts = [];
        $updateBindings = [];

        foreach ($data as $column => $value) {
            $param = ':u_' . $column;
            $setParts[] = "{$column} = {$param}";
            $updateBindings[$param] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(array_merge($updateBindings, $this->bindings));
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($this->bindings);
    }

    protected function buildSelectSql(): string
    {
        $sql = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        return $sql;
    }

    protected function compileWheres(): string
    {
        $sql = '';

        foreach ($this->wheres as $index => $where) {
            if ($index === 0) {
                $sql .= $where['sql'];
            } else {
                $sql .= ' ' . $where['boolean'] . ' ' . $where['sql'];
            }
        }

        return $sql;
    }

    protected function newParamName(string $prefix = 'p'): string
    {
        return ':' . $prefix . '_' . count($this->bindings);
    }
}
