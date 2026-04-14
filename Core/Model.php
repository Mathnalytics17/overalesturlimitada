<?php

namespace app\Core;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected bool $timestamps = true;
    protected bool $softDeletes = false;

    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function query(): QueryBuilder
    {
        $instance = new static();
        $query = (new QueryBuilder())->table($instance->table);

        if ($instance->softDeletes) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    public static function rawQuery(): QueryBuilder
    {
        $instance = new static();
        return (new QueryBuilder())->table($instance->table);
    }

    public static function all(): array
    {
        $rows = static::query()->get();
        return array_map(fn($row) => new static($row), $rows);
    }

    public static function find(int|string $id): ?static
    {
        $instance = new static();

        $row = static::query()
            ->where($instance->primaryKey, '=', $id)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function where(string $column, string $operator, mixed $value): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function create(array $data): ?static
    {
        $instance = new static();
        $data = $instance->filterFillable($data);

        if ($instance->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        $id = static::rawQuery()->insert($data);

        return static::find($id);
    }

    public function update(array $data): bool
    {
        $data = $this->filterFillable($data);

        if (empty($data)) {
            return false;
        }

        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $result = static::rawQuery()
            ->where($this->primaryKey, '=', $this->attributes[$this->primaryKey] ?? null)
            ->update($data);

        if ($result) {
            $this->fill(array_merge($this->attributes, $data));
        }

        return $result;
    }

    public function delete(): bool
    {
        $id = $this->attributes[$this->primaryKey] ?? null;

        if (!$id) {
            return false;
        }

        if ($this->softDeletes) {
            $data = ['deleted_at' => date('Y-m-d H:i:s')];

            if ($this->timestamps) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            return static::rawQuery()
                ->where($this->primaryKey, '=', $id)
                ->update($data);
        }

        return static::rawQuery()
            ->where($this->primaryKey, '=', $id)
            ->delete();
    }

    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $this->castAttribute($key, $value);
        }
    }

    public function toArray(): array
    {
        $data = $this->attributes;

        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function castAttribute(string $key, mixed $value): mixed
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }

        return match ($this->casts[$key]) {
            'int' => $value !== null ? (int) $value : null,
            'float' => $value !== null ? (float) $value : null,
            'bool' => $value !== null ? (bool) $value : null,
            'string' => $value !== null ? (string) $value : null,
            'array' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }
}