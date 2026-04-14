<?php

namespace app\Models;

use app\Core\Model;

class AdminPasswordReset extends Model
{
    protected string $table = 'admin_password_resets';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admin_user_id',
        'email',
        'token_hash',
        'expires_at',
        'used_at',
        'created_at',
    ];

    protected array $hidden = [
        'token_hash',
    ];

    protected array $casts = [
        'id' => 'int',
        'admin_user_id' => 'int',
    ];

    public static function createToken(?int $adminUserId, string $email, string $plainToken, int $ttlMinutes = 30): ?static
    {
        return static::create([
            'admin_user_id' => $adminUserId,
            'email' => $email,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$ttlMinutes} minutes")),
            'used_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function findValidToken(string $plainToken): ?static
    {
        $tokenHash = hash('sha256', $plainToken);

        $row = static::query()
            ->where('token_hash', '=', $tokenHash)
            ->whereNull('used_at')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        return $row ? new static($row) : null;
    }

    public function markAsUsed(): bool
    {
        return $this->update([
            'used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function invalidateAllByEmail(string $email): bool
    {
        return static::rawQuery()
            ->where('email', '=', $email)
            ->whereNull('used_at')
            ->update([
                'used_at' => date('Y-m-d H:i:s'),
            ]);
    }
}