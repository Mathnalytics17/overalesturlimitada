<?php

namespace app\Models;

use app\Core\Model;

class AdminUser extends Model
{
    protected string $table = 'admin_users';
    protected bool $softDeletes = true;

    protected array $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'profile_photo_path',
        'role',
        'password_hash',
        'status',
        'email_verified_at',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
    ];

    protected array $hidden = [
        'password_hash',
    ];

    protected array $casts = [
        'id' => 'int',
        'failed_login_attempts' => 'int',
    ];

    public static function findByEmail(string $email): ?static
    {
        $row = static::query()
            ->where('email', '=', $email)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function recent(int $limit = 100): array
    {
        $rows = static::query()
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn(array $row) => new static($row), $rows);
    }

    public static function filter(array $filters = [], int $limit = 200): array
    {
        $rows = static::filteredQuery($filters)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return array_map(fn(array $row) => new static($row), $rows);
    }

    public static function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage));
        $total = static::filteredQuery($filters)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $rows = static::filteredQuery($filters)
            ->orderBy('id', 'DESC')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return [
            'items' => array_map(fn(array $row) => new static($row), $rows),
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ];
    }

    protected static function filteredQuery(array $filters): \app\Core\QueryBuilder
    {
        $query = static::query();

        if (!empty($filters['status'])) {
            $query->where('status', '=', (string) $filters['status']);
        }

        if (!empty($filters['role'])) {
            $query->where('role', '=', (string) $filters['role']);
        }

        if (!empty($filters['q'])) {
            $query->whereAnyLike(
                ['full_name', 'first_name', 'last_name', 'email', 'phone', 'role'],
                trim((string) $filters['q'])
            );
        }

        return $query;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password_hash);
    }

    public function isLocked(): bool
    {
        return !empty($this->locked_until) && strtotime((string) $this->locked_until) > time();
    }

    public function incrementFailedAttempts(int $maxAttempts = 5, int $lockMinutes = 15): bool
    {
        $attempts = ((int) $this->failed_login_attempts) + 1;

        $data = [
            'failed_login_attempts' => $attempts,
        ];

        if ($attempts >= $maxAttempts) {
            $data['locked_until'] = date('Y-m-d H:i:s', strtotime("+{$lockMinutes} minutes"));
        }

        return $this->update($data);
    }

    public function resetFailedAttempts(): bool
    {
        return $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    public function markEmailVerified(): bool
    {
        return $this->update([
            'email_verified_at' => date('Y-m-d H:i:s'),
            'status' => 'active',
        ]);
    }

    public function updateLastLogin(): bool
    {
        return $this->update([
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function buildFullName(string $firstName, string $lastName): string
    {
        return trim($firstName . ' ' . $lastName);
    }

    public function hasRole(string $role): bool
{
    return (string) ($this->role ?? '') === $role;
}

public function isSuperAdmin(): bool
{
    return $this->hasRole('super_admin');
}

public function isSeller(): bool
{
    return $this->hasRole('seller');
}

public function can(string $permission): bool
{
    $role = (string) ($this->role ?? 'seller');

    if ($role === 'super_admin') {
        return true;
    }

    if ($role === 'seller') {
        return match ($permission) {
            'manage_users' => false,
            default => true,
        };
    }

    return false;
}

public function roleLabel(): string
{
    return $this->isSuperAdmin() ? 'Super administrador' : 'Vendedor';
}
}
