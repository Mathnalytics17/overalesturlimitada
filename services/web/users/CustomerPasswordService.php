<?php

namespace app\Services\Web\Users;

use app\Models\CustomerAccount;
use app\Models\CustomerPasswordReset;
use app\Services\Mail\CustomerMailService;

class CustomerPasswordService
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

        $account = CustomerAccount::findByEmail($email);

        if ($account) {
            CustomerPasswordReset::invalidateAllByEmail($email);

            $plainToken = \random_token(32);

            CustomerPasswordReset::createToken(
                customerAccountId: (int) $account->id,
                email: $email,
                plainToken: $plainToken,
                ttlMinutes: 30
            );

            $appUrl = 'http://localhost:8001';
            $resetUrl = $appUrl . '/users/resetPassword?token=' . urlencode($plainToken);

            $mailService = new CustomerMailService();
            $mailService->sendResetPasswordEmail(
                email: $email,
                name: 'Usuario',
                resetUrl: $resetUrl
            );
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

        $reset = CustomerPasswordReset::findValidToken($token);

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
        $account = CustomerAccount::find((int) $reset->customer_account_id);

        if (!$account) {
            return [
                'success' => false,
                'message' => 'No se encontró la cuenta asociada.',
                'errors' => [
                    'account' => ['Cuenta no encontrada.'],
                ],
                'data' => [],
            ];
        }

        $updated = $account->update([
            'password_hash' => CustomerAccount::hashPassword($password),
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

        return [
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
            'errors' => [],
            'data' => [],
        ];
    }
}