<?php

namespace app\Core\Middleware;

use app\Core\AdminAuth;
use app\Core\Request;

class GuestAdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (AdminAuth::check()) {
            \redirect('/admin');
        }
    }
}