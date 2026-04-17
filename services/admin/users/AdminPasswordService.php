<?php

namespace app\Services\Admin\Users;

use app\Models\AdminPasswordReset;
use app\Models\AdminSession;
use app\Models\AdminUser;
use app\Services\Mail\AdminMailService;

class AdminPasswordService
{
    public function requestReset(string $email): array
    {
        $email = trim(mb_strtolower($email));

        if ($email === '') {
            return [
                'success' => false,
                'message' => 'Debes ingresar un correo.',
                'errors' => [
                    'email' => ['El correo es obligatorio.'],
                ],
                'data' => [],
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Correo inválido.',
                'errors' => [
                    'email' => ['El correo no es válido.'],
                ],
                'data' => [],
            ];
        }

        $user = AdminUser::findByEmail($email);

        if ($user) {
            AdminPasswordReset::invalidateAllByEmail($email);

            $plainToken = \random_token(32);

            AdminPasswordReset::createToken(
                adminUserId: (int) $user->id,
                email: $email,
                plainToken: $plainToken,
                ttlMinutes: 30
            );

            $resetUrl = app_url('/admin/users/resetPassword?token=' . urlencode($plainToken));

            $mailService = new AdminMailService();
            $mailService->sendResetPasswordEmail(
                email: $email,
                name: $user->full_name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Administrador',
                resetUrl: $resetUrl
            );

            security_event('admin_password_reset_requested', [
                'email' => $email,
                'admin_user_id' => (int) $user->id,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Si el correo existe, se ha enviado un enlace de recuperación.',
            'errors' => [],
            'data' => [],
        ];
    }

    public function validateResetToken(string $token): array
    {
        if (trim($token) === '') {
            return [
                'success' => false,
                'message' => 'Token inválido.',
                'errors' => [
                    'token' => ['El token es obligatorio.'],
                ],
                'data' => [],
            ];
        }

        $reset = AdminPasswordReset::findValidToken($token);

        if (!$reset) {
            return [
                'success' => false,
                'message' => 'El enlace no es válido o ha expirado.',
                'errors' => [
                    'token' => ['Token inválido o expirado.'],
                ],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'Token válido.',
            'errors' => [],
            'data' => [
                'reset' => $reset,
            ],
        ];
    }

    public function resetPassword(string $token, string $password, string $passwordConfirmation): array
    {
        $validation = $this->validateResetToken($token);

        if (!$validation['success']) {
            return $validation;
        }

        $errors = [];

        if ($password === '') {
            $errors['password'][] = 'La contraseña es obligatoria.';
        } elseif (strlen($password) < 8) {
            $errors['password'][] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'][] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $errors,
                'data' => [],
            ];
        }

        $reset = $validation['data']['reset'];
        $user = AdminUser::find((int) $reset->admin_user_id);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'No se encontró la cuenta asociada.',
                'errors' => [
                    'account' => ['Cuenta no encontrada.'],
                ],
                'data' => [],
            ];
        }

        $updated = $user->update([
            'password_hash' => AdminUser::hashPassword($password),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'No fue posible actualizar la contraseña.',
                'errors' => [
                    'system' => ['Error interno al actualizar la contraseña.'],
                ],
                'data' => [],
            ];
        }

        $reset->markAsUsed();
        AdminSession::revokeAllByUser((int) $user->id);
        security_event('admin_password_reset_completed', [
            'admin_user_id' => (int) $user->id,
            'email' => (string) ($user->email ?? ''),
        ]);

        return [
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
            'errors' => [],
            'data' => [],
        ];
    }
}
