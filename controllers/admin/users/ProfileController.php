<?php

namespace app\Controllers\admin\users;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Admin\Users\AdminProfileService;

class ProfileController extends Controller
{
    public function index()
    {
        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
            exit;
        }

        return $this->render('admin/users/profile', [
            'user' => $user,
            'page_title' => 'Mi perfil',
            'page_subtitle' => 'Información general de tu cuenta',
            'active' => 'profile',
        ], 'adminUserLayout');
    }

    public function showEditProfile()
    {
        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
            exit;
        }

        return $this->render('admin/users/profileEdit', [
            'user' => $user,
            'errors' => [],
            'message' => null,
            'old' => [],
            'page_title' => 'Editar perfil',
            'page_subtitle' => 'Actualiza tu información',
            'active' => 'profile',
        ], 'adminUserLayout');
    }

    public function updateProfile()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
            exit;
        }

        $service = new AdminProfileService();

        $result = $service->updateProfile(
            adminUserId: (int) $user->id,
            input: $_POST,
            files: $_FILES
        );

        $freshUser = AdminAuth::user();

        return $this->render('admin/users/profileEdit', [
            'user' => $freshUser,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $_POST,
            'page_title' => 'Editar perfil',
            'page_subtitle' => 'Actualiza tu información',
            'active' => 'profile',
        ], 'adminUserLayout');
    }

    public function showChangePassword()
    {
        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
            exit;
        }

        return $this->render('admin/users/changePassword', [
            'user' => $user,
            'errors' => [],
            'message' => null,
            'page_title' => 'Cambiar contraseña',
            'page_subtitle' => 'Actualiza la contraseña de tu cuenta',
            'active' => 'profile',
        ], 'adminUserLayout');
    }

    public function changePassword()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
            exit;
        }

        $service = new AdminProfileService();

        $result = $service->changePassword(
            adminUserId: (int) $user->id,
            currentPassword: $_POST['current_password'] ?? '',
            newPassword: $_POST['new_password'] ?? '',
            newPasswordConfirmation: $_POST['new_password_confirmation'] ?? ''
        );

        if (!empty($result['success']) && !empty($result['data']['force_relogin'])) {
            AdminAuth::logout(
                ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
                userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            return $this->render('admin/users/login', [
                'errors' => [],
                'message' => 'Tu contrasena fue actualizada. Inicia sesion nuevamente.',
                'old' => [],
            ], null);
        }

        return $this->render('admin/users/changePassword', [
            'user' => $user,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'page_title' => 'Cambiar contraseña',
            'page_subtitle' => 'Actualiza la contraseña de tu cuenta',
            'active' => 'profile',
        ], 'adminUserLayout');
    }
}
