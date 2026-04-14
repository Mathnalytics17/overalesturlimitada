<?php

namespace app\Controllers\admin\users;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Services\Admin\Users\AdminProfileService;
use app\Core\Flash;
class ProfileController extends Controller
{
    public function index()
    {
        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
        }

        return $this->render('admin/home', [
            'user' => $user,
        ], 'adminUserLayout');
    }

    public function showEditProfile()
    {
        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
        }

        return $this->render('admin/users/editUser', [
            'user' => $user,
            'errors' => [],
            'message' => null,
            'old' => [],
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
        }

        $service = new AdminProfileService();

        $result = $service->updateProfile(
            adminUserId: (int) $user->id,
            input: $_POST
        );

        $freshUser = AdminAuth::user();

        return $this->render('admin/users/editUser', [
            'user' => $freshUser,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $_POST,
        ], 'adminUserLayout');
    }

    public function showChangePassword()
    {
        $user = AdminAuth::user();

        if (!$user) {
            \redirect('/admin/users/login');
        }

        return $this->render('admin/users/changePassword', [
            'errors' => [],
            'message' => null,
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
        }

        $service = new AdminProfileService();

        $result = $service->changePassword(
            adminUserId: (int) $user->id,
            currentPassword: $_POST['current_password'] ?? '',
            newPassword: $_POST['new_password'] ?? '',
            newPasswordConfirmation: $_POST['new_password_confirmation'] ?? ''
        );

        return $this->render('admin/users/changePassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
        ], 'adminUserLayout');
    }
}