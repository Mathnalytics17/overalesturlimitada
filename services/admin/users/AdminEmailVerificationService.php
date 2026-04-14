<?php

namespace app\Services\Admin\Users;

use app\Models\AdminEmailVerification;
use app\Models\AdminUser;
use app\Services\Mail\AdminMailService;

class AdminEmailVerificationService
{
    public function verify(string $token): array
    {
        $token = trim($token);

        if ($token === '') {
            return [
                'success' => false,
                'message' => 'Token inválido.',
                'errors' => [
                    'token' => ['El token es obligatorio.'],
                ],
                'data' => [],
            ];
        }

        $verification = AdminEmailVerification::findValidToken($token);

        if (!$verification) {
            return [
                'success' => false,
                'message' => 'El enlace de verificación no es válido o ha expirado.',
                'errors' => [
                    'token' => ['Token inválido o expirado.'],
                ],
                'data' => [],
            ];
        }

        $user = AdminUser::find((int) $verification->admin_user_id);

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

        $verification->markAsVerified();
        $user->markEmailVerified();

        return [
            'success' => true,
            'message' => 'Correo verificado correctamente.',
            'errors' => [],
            'data' => [
                'user' => $user->toArray(),
            ],
        ];
    }

    public function resend(string $email): array
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

        $user = AdminUser::findByEmail($email);

        if (!$user) {
            return [
                'success' => true,
                'message' => 'Si la cuenta existe, se ha enviado un nuevo enlace.',
                'errors' => [],
                'data' => [],
            ];
        }

        $verifiedAt = $user->email_verified_at ?? null;
        if ($verifiedAt !== null && $verifiedAt !== '') {
            return [
                'success' => true,
                'message' => 'La cuenta ya está verificada.',
                'errors' => [],
                'data' => [],
            ];
        }

        $plainToken = \random_token(32);

        AdminEmailVerification::createToken(
            adminUserId: (int) $user->id,
            plainToken: $plainToken,
            ttlHours: 24
        );

        $appUrl = 'http://localhost:8001';
        $verificationUrl = $appUrl . '/admin/users/confirmUser?token=' . urlencode($plainToken);

        $mailService = new AdminMailService();
        $mailResult = $mailService->sendVerificationEmail(
            email: $user->email,
            name: $user->full_name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Administrador',
            verificationUrl: $verificationUrl
        );

        return [
            'success' => true,
            'message' => $mailResult['success']
                ? 'Se ha enviado un nuevo enlace de verificación.'
                : 'Se generó el enlace, pero no fue posible enviar el correo.',
            'errors' => [],
            'data' => [
                'mail_result' => $mailResult,
            ],
        ];
    }
}