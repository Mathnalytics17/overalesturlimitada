<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function admin_login_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Admin</title>
  <link rel="stylesheet" href="/public/styles/admin.css" />
</head>
<body>
  <div class="login">
    <div class="login-left">
      <div class="login-card">
        <h1 class="login-title">Inicia sesión</h1>
        <p class="login-sub">Acceso al panel administrativo</p>

        <?php if ($message): ?>
          <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="/admin/users/login">
          <?= Csrf::input(); ?>

          <div class="field" style="margin-bottom:12px;">
            <label for="email">Correo electrónico</label>
            <input
              id="email"
              name="email"
              type="email"
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            >
            <?php if (admin_login_error($errors, 'email')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_login_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field" style="margin-bottom:12px;">
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password">
            <?php if (admin_login_error($errors, 'password')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_login_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>

          <?php if (admin_login_error($errors, 'auth')): ?>
            <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
              <?= htmlspecialchars(admin_login_error($errors, 'auth')) ?>
            </div>
          <?php endif; ?>

          <button class="btn primary" style="width:100%;" type="submit">Entrar</button>
        </form>
      </div>
    </div>

    <div class="login-right"></div>
  </div>
</body>
</html>