<?php
namespace app\Models;

use app\Core\Model;

class CustomerAccount extends Model
{
    protected string $table = 'customer_accounts';
    protected bool $softDeletes = true;

    protected array $fillable = [
        'customer_id',
        'username',
        'email',
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
        'customer_id' => 'int',
        'failed_login_attempts' => 'int',
    ];

    public static function findByEmail(string $email): ?static
    {
        $row = static::query()
            ->where('email', '=', $email)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function findActiveByEmail(string $email): ?static
    {
        $row = static::query()
            ->where('email', '=', $email)
            ->where('status', '=', 'active')
            ->first();

        return $row ? new static($row) : null;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password_hash);
    }

    public function isLocked(): bool
    {
        return !empty($this->locked_until) && strtotime($this->locked_until) > time();
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
}
