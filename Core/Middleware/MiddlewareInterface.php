<?php

namespace app\Core\Middleware;

use app\Core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}