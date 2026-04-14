<div style="max-width:780px;margin:40px auto;padding:24px;border:1px solid #e5e7eb;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.05);font-family:system-ui,sans-serif;">
  <?php if (!empty($success)): ?>
    <h1 style="margin-top:0;">¡Tu interés por el paquete ya quedó registrado!</h1>
    <p>Uno de nuestros asesores verá este caso en el panel administrativo y podrá responderte mejor.</p>
    <p><strong>Radicado interno:</strong> #<?= (int) ($lead->id ?? 0) ?></p>
    <?php if (!empty($whatsappUrl ?? null)): ?>
      <a href="<?= e((string) $whatsappUrl) ?>" target="_blank" rel="noopener" style="background:#16a34a;color:#fff;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:700;display:inline-block;margin-top:16px;">Abrir WhatsApp</a>
    <?php endif; ?>
  <?php else: ?>
    <h1 style="margin-top:0;">No pudimos registrar tu consulta</h1>
    <ul>
      <?php foreach (($errors ?? []) as $error): ?>
        <li><?= e((string) $error) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
