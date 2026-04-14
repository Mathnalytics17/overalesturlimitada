<?php

namespace app\Controllers\admin\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\AdminAuth;
use app\Services\Admin\Users\AdminRegisterService;
use app\Core\Flash;
class RegisterController extends Controller
{
    public function showRegister()
    {
        if (!AdminAuth::can('create_users')) {
            http_response_code(403);
            exit('No autorizado.');
        }

        return $this->render('admin/users/register', [
            'errors' => [],
            'message' => null,
            'old' => [],
        ], 'adminUserLayout');
    }

    public function register()
    {
        if (!AdminAuth::can('create_users')) {
            http_response_code(403);
            exit('No autorizado.');
        }

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $service = new AdminRegisterService();
        $result = $service->register($_POST);

        if ($result['success']) {
            return $this->render('admin/users/confirmAccount', [
                'message' => $result['message'] ?? null,
            ], 'adminUserLayout');
        }

        return $this->render('admin/users/register', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $_POST,
        ], 'adminUserLayout');
    }
}