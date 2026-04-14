<?php

namespace app\Models;

use app\Core\Model;

class AdminLoginLog extends Model
{
    protected string $table = 'admin_login_logs';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admin_user_id',
        'email_attempted',
        'ip_address',
        'user_agent',
        'status',
        'reason',
        'created_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'admin_user_id' => 'int',
    ];

    public static function log(
        ?int $adminUserId,
        ?string $emailAttempted,
        ?string $ipAddress,
        ?string $userAgent,
        string $status,
        ?string $reason = null
    ): ?static {
        return static::create([
            'admin_user_id' => $adminUserId,
            'email_attempted' => $emailAttempted,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => $status,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}