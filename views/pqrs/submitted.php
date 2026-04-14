<?php
$case = $case ?? null;
?>

<div class="container-pqrs">
  <div style="max-width:900px;margin:30px auto;padding:32px;border:1px solid #e5e7eb;border-radius:20px;background:#fff;">
    <h1 style="margin-bottom:12px;">¡PQRS recibida!</h1>

    <p style="font-size:18px;color:#334155;margin-bottom:24px;">
      Hemos registrado tu solicitud correctamente. Nuestro equipo la revisará y te responderá lo antes posible.
    </p>

    <div style="padding:18px;border-radius:16px;background:#f8fafc;border:1px solid #e5e7eb;margin-bottom:24px;">
      <div style="margin-bottom:8px;">
        <strong>Radicado:</strong>
        <?= e((string) ($case->radicado ?? '')) ?>
      </div>

      <div style="margin-bottom:8px;">
        <strong>Tipo:</strong>
        <?= e((string) ($case->request_type ?? '')) ?>
      </div>

      <div style="margin-bottom:8px;">
        <strong>Estado inicial:</strong>
        <?= e((string) ($case->status ?? 'new')) ?>
      </div>

      <div>
        <strong>Asunto:</strong>
        <?= e((string) ($case->subject ?? '')) ?>
      </div>
    </div>

    <div style="display:flex;gap:14px;flex-wrap:wrap;">
      <a href="/" style="display:inline-block;padding:14px 24px;border-radius:12px;background:#0f172a;color:#fff;text-decoration:none;font-weight:700;">
        Volver al inicio
      </a>

      <a href="/pqrs" style="display:inline-block;padding:14px 24px;border-radius:12px;background:#e11d48;color:#fff;text-decoration:none;font-weight:700;">
        Crear otra PQRS
      </a>
    </div>
  </div>
</div>