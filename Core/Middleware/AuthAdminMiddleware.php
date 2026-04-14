<?php

namespace app\Core\Middleware;

use app\Core\AdminAuth;
use app\Core\Request;

class AuthAdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (!AdminAuth::check()) {
            \redirect('/admin/users/login');
        }
    }
}