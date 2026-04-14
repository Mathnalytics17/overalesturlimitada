<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function admin_resend_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reenviar verificación admin</title>
  <link rel="stylesheet" href="/public/styles/admin.css" />
</head>
<body>
  <div class="login">
    <div class="login-left">
      <div class="login-card">
        <h1 class="login-title">Reenviar verificación</h1>
        <p class="login-sub">Ingresa el correo del administrador para reenviar el enlace.</p>

        <?php if (!empty($message)): ?>
          <div style="margin-bottom:12px; font-weight:700; color:#065f46;">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="/admin/users/resendVerification">
          <?= Csrf::input(); ?>

          <div class="field" style="margin-bottom:14px;">
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            <?php if (admin_resend_error($errors, 'email')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_resend_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn primary">Reenviar enlace</button>
        </form>
      </div>
    </div>

    <div class="login-right"></div>
  </div>
</body>
</html>