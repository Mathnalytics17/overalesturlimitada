<?php

namespace app\Controllers\admin\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Admin\Users\AdminAuthService;
use app\Core\Flash;
class AuthController extends Controller
{
    public function showLogin()
    {
        return $this->render('admin/users/login', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ], null);
    }

    public function login()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $service = new AdminAuthService();

        $result = $service->login(
            email: $_POST['email'] ?? '',
            password: $_POST['password'] ?? '',
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        if ($result['success']) {
            \redirect('/admin');
        }

        return $this->render('admin/users/login', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => [
                'email' => $_POST['email'] ?? '',
            ],
        ],null);
    }

    public function logout()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $service = new AdminAuthService();

        $service->logout(
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        \redirect('/admin/users/login');
    }
}