<?php
$errors = $errors ?? [];
$message = $message ?? null;
$success = (bool) ($success ?? false);

$primaryText = $success ? 'Cuenta verificada' : 'No se pudo verificar la cuenta';
$primaryColor = $success ? '#166534' : '#991b1b';
$softBg = $success ? '#dcfce7' : '#fee2e2';
$softBorder = $success ? '#bbf7d0' : '#fecaca';
$iconBg = $success ? '#22c55e' : '#ef4444';
$icon = $success ? '✓' : '!';
?>
<link rel="stylesheet" href="/styles/admin.css" />
<div class="login">
  <div class="login-left">
    <div class="login-card" style="width:min(560px, 94vw);">
      <div style="display:flex;justify-content:center;margin-bottom:18px;">
        <div style="
          width:72px;
          height:72px;
          border-radius:999px;
          background:<?= e($iconBg) ?>;
          color:#fff;
          display:flex;
          align-items:center;
          justify-content:center;
          font-size:34px;
          font-weight:900;
          box-shadow:0 10px 24px rgba(15, 23, 42, 0.12);
        ">
          <?= e($icon) ?>
        </div>
      </div>

      <h1 class="login-title" style="text-align:center;"><?= e($primaryText) ?></h1>

      <p class="login-sub" style="text-align:center;max-width:420px;margin:0 auto 18px;">
        <?php if ($success): ?>
          Tu correo fue confirmado correctamente. Si ya tienes contraseña, puedes iniciar sesión. Si fuiste invitado sin contraseña, usa el enlace de creación de contraseña enviado al correo o solicita recuperación desde el login.
        <?php else: ?>
          El enlace no es válido, ya fue usado o ha expirado. Puedes solicitar uno nuevo para continuar.
        <?php endif; ?>
      </p>

      <?php if ($message): ?>
        <div style="
          margin-bottom:14px;
          padding:14px 16px;
          border-radius:14px;
          background:<?= e($softBg) ?>;
          border:1px solid <?= e($softBorder) ?>;
          color:<?= e($primaryColor) ?>;
          font-weight:700;
          line-height:1.5;
        ">
          <?= e($message) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div style="
          margin-bottom:16px;
          padding:14px 16px;
          border-radius:14px;
          background:#fff;
          border:1px solid #e5e7eb;
          color:#334155;
        ">
          <div style="font-weight:800;margin-bottom:8px;">Detalle</div>
          <ul style="margin:0;padding-left:18px;line-height:1.6;">
            <?php foreach ($errors as $fieldErrors): ?>
              <?php foreach ((array) $fieldErrors as $error): ?>
                <li><?= e((string) $error) ?></li>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:6px;">
        <?php if ($success): ?>
          <a href="/admin/users/login" class="btn primary" style="text-decoration:none;">
            Ir al login
          </a>
        <?php else: ?>
          <a href="/admin/users/resendVerification" class="btn primary" style="text-decoration:none;">
            Solicitar nuevo enlace
          </a>

          <a href="/admin/users/login" class="btn" style="text-decoration:none;">
            Volver al login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="login-right"></div>
</div>