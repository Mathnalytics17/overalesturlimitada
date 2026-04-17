<?php

namespace app\Services\Admin\Users;

use app\Models\AdminEmailVerification;
use app\Models\AdminUser;
use app\Services\Mail\AdminMailService;

class AdminRegisterService
{
    public function register(array $input): array
    {
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $email = trim(mb_strtolower($input['email'] ?? ''));
        $password = $input['password'] ?? '';
        $passwordConfirmation = $input['password_confirmation'] ?? '';

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'][] = 'El nombre es obligatorio.';
        }

        if ($lastName === '') {
            $errors['last_name'][] = 'El apellido es obligatorio.';
        }

        if ($email === '') {
            $errors['email'][] = 'El correo es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'El correo no es válido.';
        }

        if ($password === '') {
            $errors['password'][] = 'La contraseña es obligatoria.';
        } elseif (strlen($password) < 8) {
            $errors['password'][] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'][] = 'Las contraseñas no coinciden.';
        }

        $existingUser = AdminUser::findByEmail($email);
        if ($existingUser) {
            $errors['email'][] = 'Ya existe una cuenta admin con este correo.';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Error de validación.',
                'data' => [],
                'errors' => $errors,
            ];
        }

        $fullName = trim($firstName . ' ' . $lastName);

     $user = AdminUser::create([
    'uuid' => \uuid(),
    'first_name' => $firstName,
    'last_name' => $lastName,
    'full_name' => $fullName,
    'email' => $email,
    'phone' => null,
    'role' => 'seller',
    'password_hash' => AdminUser::hashPassword($password),
    'status' => 'pending_verification',
    'email_verified_at' => null,
    'last_login_at' => null,
    'failed_login_attempts' => 0,
    'locked_until' => null,
]);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'No fue posible crear el usuario admin.',
                'data' => [],
                'errors' => [
                    'system' => ['Error interno al registrar el usuario admin.'],
                ],
            ];
        }

        $plainVerificationToken = \random_token(32);

        AdminEmailVerification::createToken(
            adminUserId: (int) $user->id,
            plainToken: $plainVerificationToken,
            ttlHours: 24
        );

        $verificationUrl = app_url('/admin/users/confirmUser?token=' . urlencode($plainVerificationToken));

        $mailService = new AdminMailService();
        $mailResult = $mailService->sendVerificationEmail(
            email: $email,
            name: $fullName !== '' ? $fullName : $firstName,
            verificationUrl: $verificationUrl
        );

        return [
            'success' => true,
            'message' => $mailResult['success']
                ? 'Cuenta admin creada correctamente. Revisa tu correo para verificarla.'
                : 'Cuenta admin creada correctamente, pero no fue posible enviar el correo de verificación.',
            'data' => [
                'user' => $user->toArray(),
                'mail_result' => $mailResult,
            ],
            'errors' => [],
        ];
    }
}
