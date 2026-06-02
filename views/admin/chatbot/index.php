<?php
$filters = $filters ?? [];
$contacts = $contacts ?? [];
$summary = $summary ?? [];
function h($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function consent_badge(array $c): array {
    $v = $c['accepted_policy'] ?? $c['policy_accepted'] ?? $c['consent'] ?? $c['accepted'] ?? null;
    if ($v === true || $v === 1 || $v === '1' || $v === 'accepted' || $v === 'Acepto') return ['Aceptó', 'success'];
    if ($v === false || $v === 0 || $v === '0' || $v === 'rejected' || $v === 'No acepto') return ['No aceptó', 'danger'];
    return ['Pendiente', 'warning'];
}
$query = http_build_query(array_filter($filters, static fn($v) => $v !== null && $v !== ''));
?>

<style>
.chatbot-page{padding:18px}.chatbot-header{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:18px}.chatbot-title h1{font-size:26px;margin:0;color:#183b3f}.chatbot-title p{margin:6px 0 0;color:#667085}.chatbot-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.05);padding:16px;margin-bottom:16px}.chatbot-filters{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end}.chatbot-filters label{font-size:13px;font-weight:700;color:#344054}.chatbot-filters input,.chatbot-filters select{width:100%;padding:10px 12px;border:1px solid #d0d5dd;border-radius:10px}.chatbot-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:10px;padding:10px 14px;text-decoration:none;font-weight:700;cursor:pointer}.chatbot-btn-primary{background:#488B8F;color:white}.chatbot-btn-light{background:#f2f4f7;color:#344054}.chatbot-btn-success{background:#12b76a;color:white}.chatbot-table{width:100%;border-collapse:collapse}.chatbot-table th{background:#f8fafc;color:#475467;font-size:12px;text-transform:uppercase;text-align:left;padding:12px}.chatbot-table td{border-top:1px solid #eef2f6;padding:12px;color:#344054;vertical-align:middle}.badge{display:inline-flex;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:800}.badge-success{background:#dcfae6;color:#067647}.badge-danger{background:#fee4e2;color:#b42318}.badge-warning{background:#fef0c7;color:#b54708}.empty{text-align:center;padding:30px;color:#667085}.alert{padding:12px 14px;border-radius:12px;background:#fff3cd;color:#664d03;margin-bottom:16px}@media(max-width:900px){.chatbot-header,.chatbot-filters{display:block}.chatbot-filters>*{margin-bottom:10px}.chatbot-table{font-size:13px}.chatbot-page{padding:10px}}
</style>

<div class="chatbot-page">
    <div class="chatbot-header">
        <div class="chatbot-title">
            <h1>Chatbot WhatsApp</h1>
            <p>Contactos que escriben, aceptación de política y conversaciones.</p>
        </div>
        <a class="chatbot-btn chatbot-btn-success" href="/admin/chatbot/export?<?= h($query) ?>">Descargar Excel/CSV</a>
    </div>

    <?php if (!empty($error)): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>

    <div class="chatbot-card">
        <form class="chatbot-filters" method="get" action="/admin/chatbot">
            <div><label>Buscar</label><input name="q" placeholder="Nombre, teléfono o sesión" value="<?= h($filters['q'] ?? '') ?>"></div>
            <div><label>Sesión</label><input name="bot_session" placeholder="alestur_ventas" value="<?= h($filters['bot_session'] ?? '') ?>"></div>
            <div><label>Política</label><select name="consent"><option value="">Todas</option><option value="accepted" <?= ($filters['consent'] ?? '')==='accepted'?'selected':'' ?>>Aceptó</option><option value="rejected" <?= ($filters['consent'] ?? '')==='rejected'?'selected':'' ?>>No aceptó</option><option value="pending" <?= ($filters['consent'] ?? '')==='pending'?'selected':'' ?>>Pendiente</option></select></div>
            <button class="chatbot-btn chatbot-btn-primary" type="submit">Filtrar</button>
        </form>
    </div>

    <div class="chatbot-card" style="overflow:auto">
        <table class="chatbot-table">
            <thead><tr><th>Contacto</th><th>Sesión</th><th>Política</th><th>Estado</th><th>Último mensaje</th><th></th></tr></thead>
            <tbody>
            <?php if (!$contacts): ?>
                <tr><td colspan="6" class="empty">No hay registros para mostrar.</td></tr>
            <?php endif; ?>
            <?php foreach ($contacts as $c): [$label,$class]=consent_badge($c); $id=$c['id'] ?? ''; $session=$c['bot_session'] ?? ''; ?>
                <tr>
                    <td><strong><?= h($c['name'] ?? $c['notify_name'] ?? 'Sin nombre') ?></strong><br><small><?= h($c['phone_number'] ?? $c['phone'] ?? $c['from'] ?? '') ?></small></td>
                    <td><?= h($session) ?></td>
                    <td><span class="badge badge-<?= h($class) ?>"><?= h($label) ?></span></td>
                    <td><?= h($c['state_name'] ?? $c['current_state'] ?? $c['status'] ?? '-') ?></td>
                    <td><?= h($c['last_message_at'] ?? $c['updated_at'] ?? $c['created_at'] ?? '-') ?></td>
                    <td><a class="chatbot-btn chatbot-btn-light" href="/admin/chatbot/show?id=<?= urlencode((string)$id) ?>&bot_session=<?= urlencode((string)$session) ?>">Ver chat</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
