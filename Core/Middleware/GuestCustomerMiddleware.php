<?php

namespace app\Core\Middleware;

use app\Core\CustomerAuth;
use app\Core\Request;

class GuestCustomerMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (CustomerAuth::check()) {
            \redirect('/users');
        }
    }
}