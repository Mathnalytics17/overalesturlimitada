<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function login_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="" />
</head>
<body>
  <div class="login">
    <div class="login-left">
      <div class="login-card">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
          <div class="logo" style="color:white; background:var(--primary);">OA</div>
          <div>
            <div style="font-weight:1000;">Over Alestur</div>
            <div class="t-muted">Expertos en viajes</div>
          </div>
        </div>

        <h1 class="login-title">Inicia sesión</h1>
        <p class="login-sub">Accede a tu cuenta</p>

        <?php if ($message): ?>
          <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="/users/login">
          <?= Csrf::input(); ?>

          <div class="field" style="margin-bottom:12px;">
            <label for="email">Correo electrónico</label>
            <input
              id="email"
              name="email"
              type="email"
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            />
            <?php if (login_error($errors, 'email')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(login_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

       <div class="field" style="margin-bottom:12px;">
  <label for="password">Contraseña</label>
  <input id="password" name="password" type="password" />
  <?php if (login_error($errors, 'password')): ?>
    <small style="color:#b91c1c;"><?= htmlspecialchars(login_error($errors, 'password')) ?></small>
  <?php endif; ?>
</div>

<div style="text-align:right; margin-bottom:12px;">
  <a href="/users/forgotPassword" style="font-size:14px; text-decoration:none;">
    ¿Olvidaste tu contraseña?
  </a>
</div>

          <?php if (login_error($errors, 'auth')): ?>
            <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
              <?= htmlspecialchars(login_error($errors, 'auth')) ?>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn primary" style="width:100%;">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>