<?php

namespace app\Models;

use app\Core\Model;

class CustomerLoginLog extends Model
{
    protected string $table = 'customer_login_logs';
    protected bool $timestamps = false;

    protected array $fillable = [
        'customer_account_id',
        'email_attempted',
        'ip_address',
        'user_agent',
        'status',
        'reason',
        'created_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'customer_account_id' => 'int',
    ];

    public static function log(
        ?int $customerAccountId,
        ?string $emailAttempted,
        ?string $ipAddress,
        ?string $userAgent,
        string $status,
        ?string $reason = null
    ): ?static {
        return static::create([
            'customer_account_id' => $customerAccountId,
            'email_attempted' => $emailAttempted,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => $status,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}