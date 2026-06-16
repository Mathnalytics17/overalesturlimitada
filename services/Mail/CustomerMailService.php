<?php

namespace app\Services\Mail;

use app\Models\Customer;
use app\Models\TourPackage;

class CustomerMailService
{
    protected MailerService $mailer;

    public function __construct()
    {
        $this->mailer = new MailerService();
    }

    public function sendVerificationEmail(string $email, string $name, string $verificationUrl): array
    {
        $subject = 'Confirma tu cuenta en Over Alestur';

        $html = '
            <h2>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>
            <p>Gracias por registrarte en Over Alestur.</p>
            <p>Para activar tu cuenta, haz clic en el siguiente enlace:</p>
            <p><a href="' . htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8') . '">Confirmar mi cuenta</a></p>
            <p>Si no solicitaste esta cuenta, puedes ignorar este mensaje.</p>
        ';

        $text = "Hola {$name}\n\nConfirma tu cuenta aquí:\n{$verificationUrl}\n\nSi no solicitaste esta cuenta, ignora este mensaje.";

        return $this->mailer->send($email, $name, $subject, $html, $text);
    }

    public function sendResetPasswordEmail(string $email, string $name, string $resetUrl): array
{
    $subject = 'Restablece tu contraseña en Over Alestur';

    $html = '
        <h2>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>
        <p>Recibimos una solicitud para restablecer tu contraseña.</p>
        <p>Haz clic en el siguiente enlace para continuar:</p>
        <p><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '">Restablecer contraseña</a></p>
        <p>Si no fuiste tú, ignora este mensaje.</p>
    ';

    $text = "Hola {$name}\n\nRestablece tu contraseña aquí:\n{$resetUrl}\n\nSi no fuiste tú, ignora este mensaje.";

    return $this->mailer->send($email, $name, $subject, $html, $text);
}

    public function sendPackageNotification(Customer $customer, TourPackage $package, string $type): array
    {
        $name = $customer->fullName() ?: 'viajero';
        $url = app_url('/packagesTourist/package?slug=' . urlencode((string) $package->slug));
        $isRecommendation = in_array($type, ['recommendation', 'tag_match'], true);

        if ($type === 'tag_match') {
            $subject = 'Nuevo paquete que coincide con tus gustos';
            $intro = 'Publicamos un paquete que coincide con varios estilos de viaje que seleccionaste en tu perfil.';
        } elseif ($type === 'recommendation') {
            $subject = 'Tenemos una recomendación de viaje para ti';
            $intro = 'Encontramos un paquete que puede interesarte según tus preferencias.';
        } else {
            $subject = 'Nuevo paquete disponible en Over Alestur';
            $intro = 'Publicamos un nuevo paquete turístico que puedes explorar.';
        }

        $html = '
            <h2>Hola ' . e($name) . '</h2>
            <p>' . e($intro) . '</p>
            <h3>' . e((string) $package->title) . '</h3>
            <p>' . e((string) ($package->short_description ?? '')) . '</p>
            <p><strong>Ubicación:</strong> ' . e((string) ($package->location_name ?? '')) . '</p>
            <p><a href="' . e($url) . '">Ver paquete</a></p>
            <p style="color:#64748b;font-size:13px;">Puedes cambiar tus notificaciones desde Mi cuenta.</p>
        ';

        $text = "Hola {$name}\n\n{$intro}\n\n"
            . (string) $package->title . "\n"
            . (string) ($package->location_name ?? '') . "\n"
            . $url . "\n\nPuedes cambiar tus notificaciones desde Mi cuenta.";

        return $this->mailer->send((string) $customer->email, $name, $subject, $html, $text);
    }

    
}
