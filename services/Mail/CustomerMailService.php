<?php

namespace app\Services\Mail;

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

    
}