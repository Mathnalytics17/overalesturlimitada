<?php

namespace app\Services\Mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailerService
{
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, ?string $textBody = null): array
    {
        $host = env('SMTP_HOST');
        $port = (int) env('SMTP_PORT', 587);
        $username = env('SMTP_USERNAME');
        $password = env('SMTP_PASSWORD');
        $fromAddress = env('MAIL_FROM_ADDRESS', $username ?: '');
        $fromName = env('MAIL_FROM_NAME', 'Over Alestur');
        $encryption = strtolower((string) env('SMTP_ENCRYPTION', 'tls'));

        if (!$host || !$username || !$password || !$fromAddress) {
            return [
                'success' => false,
                'message' => 'Configura SMTP_HOST, SMTP_USERNAME, SMTP_PASSWORD y MAIL_FROM_ADDRESS en .env.',
            ];
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->Port = $port;
            $mail->Timeout = 10;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPSecure = $encryption === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?? strip_tags($htmlBody);
            $mail->send();

            return [
                'success' => true,
                'message' => 'Correo enviado correctamente.',
            ];
        } catch (Exception $e) {
            app_log('mail', 'Error al enviar correo: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'No fue posible enviar el correo.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
