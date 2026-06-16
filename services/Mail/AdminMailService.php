<?php

namespace app\Services\Mail;

class AdminMailService
{
    protected MailerService $mailer;

    public function __construct()
    {
        $this->mailer = new MailerService();
    }

    public function sendVerificationEmail(string $email, string $name, string $verificationUrl): array
    {
        $subject = 'Confirma tu cuenta administrativa en Over Alestur';

        $html = '
            <h2>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>
            <p>Se ha creado tu cuenta administrativa en Over Alestur.</p>
            <p>Para activarla, haz clic en el siguiente enlace:</p>
            <p><a href="' . htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8') . '">Confirmar cuenta admin</a></p>
            <p>Si no esperabas este mensaje, ignóralo.</p>
        ';

        $text = "Hola {$name}\n\nConfirma tu cuenta administrativa aquí:\n{$verificationUrl}\n\nSi no esperabas este mensaje, ignóralo.";

        return $this->mailer->send($email, $name, $subject, $html, $text);
    }



    public function sendInvitationEmail(
        string $email,
        string $name,
        string $verificationUrl,
        ?string $passwordSetupUrl = null,
        bool $passwordWasProvided = false
    ): array {
        $subject = 'Invitación administrativa a Over Alestur';
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeVerificationUrl = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');

        $html = '
            <h2>Hola ' . $safeName . '</h2>
            <p>Se ha creado tu cuenta administrativa en Over Alestur.</p>
            <p><strong>Paso 1:</strong> confirma tu correo haciendo clic en el siguiente enlace:</p>
            <p><a href="' . $safeVerificationUrl . '">Confirmar cuenta admin</a></p>
        ';

        $text = "Hola {$name}\n\nSe ha creado tu cuenta administrativa en Over Alestur.\n\nPaso 1: confirma tu correo:\n{$verificationUrl}\n";

        if (!$passwordWasProvided && $passwordSetupUrl !== null && $passwordSetupUrl !== '') {
            $safePasswordSetupUrl = htmlspecialchars($passwordSetupUrl, ENT_QUOTES, 'UTF-8');
            $html .= '
                <p><strong>Paso 2:</strong> crea tu contraseña administrativa desde este enlace:</p>
                <p><a href="' . $safePasswordSetupUrl . '">Crear contraseña</a></p>
                <p>Por seguridad, la contraseña no se muestra al administrador ni se envía en texto plano.</p>
            ';
            $text .= "\nPaso 2: crea tu contraseña:\n{$passwordSetupUrl}\n\nPor seguridad, la contraseña no se muestra al administrador ni se envía en texto plano.\n";
        } else {
            $html .= '
                <p>El administrador te asignó una contraseña temporal. Después de verificar tu correo podrás iniciar sesión con esa contraseña.</p>
            ';
            $text .= "\nEl administrador te asignó una contraseña temporal. Después de verificar tu correo podrás iniciar sesión con esa contraseña.\n";
        }

        $html .= '<p>Si no esperabas este mensaje, ignóralo.</p>';
        $text .= "\nSi no esperabas este mensaje, ignóralo.";

        return $this->mailer->send($email, $name, $subject, $html, $text);
    }

    public function sendResetPasswordEmail(string $email, string $name, string $resetUrl): array
    {
        $subject = 'Restablece tu contraseña administrativa en Over Alestur';

        $html = '
            <h2>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>
            <p>Recibimos una solicitud para restablecer tu contraseña administrativa.</p>
            <p>Haz clic en el siguiente enlace para continuar:</p>
            <p><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '">Restablecer contraseña</a></p>
            <p>Si no fuiste tú, ignora este mensaje.</p>
        ';

        $text = "Hola {$name}\n\nRestablece tu contraseña aquí:\n{$resetUrl}\n\nSi no fuiste tú, ignora este mensaje.";

        return $this->mailer->send($email, $name, $subject, $html, $text);
    }
}