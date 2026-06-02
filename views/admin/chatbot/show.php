<?php
function h($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
$contact = is_array($contact ?? null) ? $contact : [];
$messages = $messages ?? [];
$name = $contact['name'] ?? $contact['notify_name'] ?? 'Contacto';
$phone = $contact['phone_number'] ?? $contact['phone'] ?? $contact['from'] ?? '';
$session = $contact['bot_session'] ?? $botSession ?? '';
?>
<style>
.chatbot-page{padding:18px}.chatbot-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.05);padding:16px;margin-bottom:16px}.chatbot-btn{display:inline-flex;border:0;border-radius:10px;padding:10px 14px;text-decoration:none;font-weight:700}.chatbot-btn-light{background:#f2f4f7;color:#344054}.chatbot-btn-success{background:#12b76a;color:#fff}.chat-head{display:flex;justify-content:space-between;gap:12px;align-items:center}.msg{max-width:78%;padding:10px 12px;border-radius:14px;margin:10px 0;white-space:pre-wrap}.msg-client{background:#f2f4f7;color:#344054}.msg-bot{background:#dff5f6;color:#183b3f;margin-left:auto}.msg small{display:block;margin-top:6px;color:#667085}.alert{padding:12px 14px;border-radius:12px;background:#fff3cd;color:#664d03;margin-bottom:16px}@media(max-width:900px){.chatbot-page{padding:10px}.chat-head{display:block}.msg{max-width:100%}}
</style>
<div class="chatbot-page">
    <div class="chatbot-card chat-head">
        <div>
            <a class="chatbot-btn chatbot-btn-light" href="/admin/chatbot">← Volver</a>
            <h1 style="margin:14px 0 4px;color:#183b3f"><?= h($name) ?></h1>
            <p style="margin:0;color:#667085"><?= h($phone) ?> · <?= h($session) ?></p>
        </div>
        <a class="chatbot-btn chatbot-btn-success" href="/admin/chatbot/export-messages?id=<?= urlencode((string)$id) ?>&bot_session=<?= urlencode((string)$session) ?>">Descargar conversación</a>
    </div>

    <?php if (!empty($error)): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <?php if (!empty($messagesError)): ?><div class="alert"><?= h($messagesError) ?></div><?php endif; ?>

    <div class="chatbot-card">
        <?php if (!$messages): ?><p style="color:#667085;text-align:center">No hay mensajes para este contacto.</p><?php endif; ?>
        <?php foreach ($messages as $m):
            $fromMe = $m['from_me'] ?? $m['fromMe'] ?? false;
            $isBot = $fromMe === true || $fromMe === 1 || $fromMe === '1';
            $body = $m['body'] ?? $m['message'] ?? $m['content'] ?? '';
            $date = $m['created_at'] ?? $m['timestamp'] ?? '';
        ?>
            <div class="msg <?= $isBot ? 'msg-bot' : 'msg-client' ?>">
                <strong><?= $isBot ? 'Alestur / Bot' : 'Cliente' ?></strong><br>
                <?= h($body) ?>
                <small><?= h($date) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>
