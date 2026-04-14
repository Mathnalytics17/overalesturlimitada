<?php

namespace app\Models;

use app\Core\Model;

class CustomerEmailVerification extends Model
{
    protected string $table = 'customer_email_verifications';
    protected bool $timestamps = false;

    protected array $fillable = [
        'customer_account_id',
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
        'customer_account_id' => 'int',
    ];

    public static function createToken(int $customerAccountId, string $plainToken, int $ttlHours = 24): ?static
    {
        return static::create([
            'customer_account_id' => $customerAccountId,
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