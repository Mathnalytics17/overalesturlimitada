<?php

namespace app\Models;

use app\Core\Model;

class AdminSession extends Model
{
    protected string $table = 'admin_sessions';
    protected bool $timestamps = false;
    protected array $fillable = [
        'admin_user_id',
        'session_token_hash',
        'ip_address',
        'user_agent',
        'last_activity_at',
        'expires_at',
        'created_at',
        'revoked_at',
    ];

    protected array $hidden = [
        'session_token_hash',
    ];

    protected array $casts = [
        'id' => 'int',
        'admin_user_id' => 'int',
    ];

    public static function createSession(
        int $adminUserId,
        string $plainToken,
        ?string $ipAddress,
        ?string $userAgent,
        int $ttlHours = 24
    ): ?static {
        return static::create([
            'admin_user_id' => $adminUserId,
            'session_token_hash' => hash('sha256', $plainToken),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_activity_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$ttlHours} hours")),
            'created_at' => date('Y-m-d H:i:s'),
            'revoked_at' => null,
        ]);
    }

    public static function findValidSession(string $plainToken): ?static
    {
        $tokenHash = hash('sha256', $plainToken);

        $row = static::query()
            ->where('session_token_hash', '=', $tokenHash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        return $row ? new static($row) : null;
    }

    public function touchActivity(): bool
    {
        return $this->update([
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function revoke(): bool
    {
        return $this->update([
            'revoked_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function revokeAllByUser(int $adminUserId): bool
    {
        return static::rawQuery()
            ->where('admin_user_id', '=', $adminUserId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => date('Y-m-d H:i:s'),
            ]);
    }
}