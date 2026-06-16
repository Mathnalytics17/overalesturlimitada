<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$token = $token ?? '';

function admin_reset_error(array $errors, string $field): ?string
{
  return $errors[$field][0] ?? null;
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Nueva contraseña admin</title>
  <link rel="stylesheet" href="/styles/admin.css" />
</head>

<body>
  <div class="login">
    <div class="login-left">
      <div class="login-card">
        <h1 class="login-title">Nueva contraseña</h1>
        <p class="login-sub">Define la nueva contraseña para tu cuenta administrativa.</p>

        <?php if (!empty($message)): ?>
          <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="/admin/users/resetPassword">
          <?= Csrf::input(); ?>
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <div class="field" style="margin-bottom:14px;">
            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password">
            <?php if (admin_reset_error($errors, 'password')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_reset_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field" style="margin-bottom:14px;">
            <label for="password_confirmation">Confirmar nueva contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
            <?php if (admin_reset_error($errors, 'password_confirmation')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_reset_error($errors, 'password_confirmation')) ?></small>
            <?php endif; ?>
          </div>

          <?php if (admin_reset_error($errors, 'token')): ?>
            <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
              <?= htmlspecialchars(admin_reset_error($errors, 'token')) ?>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn primary">Actualizar contraseña</button>
        </form>
      </div>
    </div>

    <div class="login-right"></div>
  </div>
</body>

</html>