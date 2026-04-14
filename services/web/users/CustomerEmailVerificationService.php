<?php

namespace app\Services\Web\Users;

use app\Models\CustomerAccount;
use app\Models\CustomerEmailVerification;

class CustomerEmailVerificationService
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

        $verification = CustomerEmailVerification::findValidToken($token);

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

        $account = CustomerAccount::find((int) $verification->customer_account_id);

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

        $verification->markAsVerified();
        $account->markEmailVerified();

        return [
            'success' => true,
            'message' => 'Correo verificado correctamente.',
            'errors' => [],
            'data' => [
                'account' => $account->toArray(),
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

        $account = CustomerAccount::findByEmail($email);

        if (!$account) {
            return [
                'success' => true,
                'message' => 'Si la cuenta existe, se ha generado un nuevo enlace.',
                'errors' => [],
                'data' => [],
            ];
        }

        if (!empty($account->email_verified_at)) {
            return [
                'success' => true,
                'message' => 'La cuenta ya está verificada.',
                'errors' => [],
                'data' => [],
            ];
        }

        $plainToken = \random_token(32);

        CustomerEmailVerification::createToken(
            customerAccountId: (int) $account->id,
            plainToken: $plainToken,
            ttlHours: 24
        );

        return [
            'success' => true,
            'message' => 'Se ha generado un nuevo enlace de verificación.',
            'errors' => [],
            'data' => [
                'verification_token' => $plainToken,
                'verification_url' => '/users/confirmUser?token=' . urlencode($plainToken),
            ],
        ];
    }
}