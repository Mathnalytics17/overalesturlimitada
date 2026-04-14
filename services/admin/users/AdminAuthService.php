<?php

namespace app\Services\Admin\Users;

use app\Core\AdminAuth;
use  app\Models\AdminUser;

class AdminAuthService
{
    public function login(string $email, string $password, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $email = trim(mb_strtolower($email));

        if ($email === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Debes ingresar correo y contraseña.',
                'errors' => [
                    'email' => ['El correo es obligatorio.'],
                    'password' => ['La contraseña es obligatoria.'],
                ],
            ];
        }

        $success = AdminAuth::attempt(
            email: $email,
            password: $password,
            ipAddress: $ipAddress,
            userAgent: $userAgent
        );

        if (!$success) {
            return [
                'success' => false,
                'message' => 'Credenciales inválidas o acceso no permitido.',
                'errors' => [
                    'auth' => ['No fue posible iniciar sesión.'],
                ],
            ];
        }

        $user = AdminAuth::user();

        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'user' => $user?->toArray(),
            ],
            'errors' => [],
        ];
    }

    public function logout(?string $ipAddress = null, ?string $userAgent = null): array
    {
        AdminAuth::logout($ipAddress, $userAgent);

        return [
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
            'data' => [],
            'errors' => [],
        ];
    }

    public function me(): array
    {
        $user = AdminAuth::user();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'No hay sesión activa.',
                'data' => [],
                'errors' => [
                    'auth' => ['Usuario no autenticado.'],
                ],
            ];
        }

        return [
            'success' => true,
            'message' => 'Usuario autenticado.',
            'data' => [
                'user' => $user->toArray(),
            ],
            'errors' => [],
        ];
    }
}