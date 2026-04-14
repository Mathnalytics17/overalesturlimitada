<?php

namespace app\Services\Mail;

class InboxMailService
{
    protected MailerService $mailer;

    public function __construct()
    {
        $this->mailer = new MailerService();
    }

    public function sendWebsiteFormSubmission(string $formType, array $payload): array
    {
        $inboxEmail = env('CONTACT_INBOX_EMAIL', env('MAIL_FROM_ADDRESS', ''));
        $inboxName = env('CONTACT_INBOX_NAME', 'Equipo Over Alestur');

        if ($inboxEmail === '') {
            return [
                'success' => false,
                'message' => 'Configura CONTACT_INBOX_EMAIL en .env para recibir formularios.',
            ];
        }

        $title = match ($formType) {
            'pqrs' => 'Nueva PQRS recibida desde la web',
            'tickets' => 'Nueva solicitud de tiquetes desde la web',
            default => 'Nuevo contacto recibido desde la web',
        };

        $safeRows = '';
        $textRows = [];
        foreach ($payload as $key => $value) {
            $label = ucwords(str_replace(['_', '-'], ' ', (string) $key));
            $rendered = is_array($value) ? implode(', ', array_map('strval', $value)) : (string) $value;
            $safeRows .= '<tr><td style="padding:8px;border:1px solid #ddd"><strong>' . e($label) . '</strong></td><td style="padding:8px;border:1px solid #ddd">' . e($rendered) . '</td></tr>';
            $textRows[] = $label . ': ' . $rendered;
        }

        $html = '<h2>' . e($title) . '</h2><table style="border-collapse:collapse;width:100%">' . $safeRows . '</table>';
        $text = $title . PHP_EOL . PHP_EOL . implode(PHP_EOL, $textRows);

        return $this->mailer->send($inboxEmail, $inboxName, $title, $html, $text);
    }
}
