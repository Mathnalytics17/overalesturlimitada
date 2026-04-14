<?php

namespace app\Models;

use app\Core\Model;

class AdminEmailVerification extends Model
{
    protected string $table = 'admin_email_verifications';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admin_user_id',
        'token_hash',
        'expires_at',
        'verified_at',
        'created_at',
    ];

    protected array $hidden = [
        'token_hash',
    ];

    protected array $casts = [
        'id' => 'int',
        'admin_user_id' => 'int',
    ];

    public static function createToken(int $adminUserId, string $plainToken, int $ttlHours = 24): ?static
    {
        return static::create([
            'admin_user_id' => $adminUserId,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$ttlHours} hours")),
            'verified_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function findValidToken(string $plainToken): ?static
    {
        $tokenHash = hash('sha256', $plainToken);

        $row = static::query()
            ->where('token_hash', '=', $tokenHash)
            ->whereNull('verified_at')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        return $row ? new static($row) : null;
    }

    public function markAsVerified(): bool
    {
        return $this->update([
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
    }
}