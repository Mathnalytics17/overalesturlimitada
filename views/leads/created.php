<?php
$lead = $lead ?? null;
?>

<div style="max-width:900px;margin:30px auto;padding:32px;border:1px solid #e5e7eb;border-radius:20px;background:#fff;">
  <h1 style="margin-bottom:12px;">¡Solicitud recibida!</h1>

  <p style="font-size:18px;color:#334155;margin-bottom:24px;">
    Ya registramos tu caso en nuestro CRM para que el equipo te responda más rápido y con contexto.
  </p>

  <div style="padding:18px;border-radius:16px;background:#f8fafc;border:1px solid #e5e7eb;margin-bottom:24px;">
    <div style="margin-bottom:8px;"><strong>Radicado interno:</strong> #<?= (int) ($lead->id ?? 0) ?></div>
    <div style="margin-bottom:8px;"><strong>Tipo:</strong> <?= e((string) ($lead->source_type ?? '')) ?></div>
    <div style="margin-bottom:8px;"><strong>Estado inicial:</strong> <?= e((string) ($lead->status ?? 'new')) ?></div>
    <div><strong>Asunto:</strong> <?= e((string) ($lead->subject ?? '')) ?></div>
  </div>

  <?php if (!empty($whatsappUrl ?? null)): ?>
    <p style="margin-bottom:18px;">
      Te recomendamos continuar por WhatsApp para que uno de nuestros asesores pueda atenderte enseguida.
    </p>

    <div style="display:flex;gap:14px;flex-wrap:wrap;">
      <a href="<?= e((string) $whatsappUrl) ?>" target="_blank" rel="noopener" style="display:inline-block;padding:14px 24px;border-radius:12px;background:#16a34a;color:#fff;text-decoration:none;font-weight:700;">
        Continuar por WhatsApp
      </a>

      <a href="/" style="display:inline-block;padding:14px 24px;border-radius:12px;background:#0f172a;color:#fff;text-decoration:none;font-weight:700;">
        Volver al inicio
      </a>
    </div>
  <?php endif; ?>
</div>