<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function admin_register_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}

function admin_old(array $old, string $field): string
{
    return htmlspecialchars($old[$field] ?? '');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Registro admin</title>
  <link rel="stylesheet" href="/public/styles/admin.css" />
</head>
<body>
  <div class="login">
    <div class="login-left">
      <div class="login-card" style="width:min(560px, 94vw);">
        <h1 class="login-title">Registro de administrador</h1>
        <p class="login-sub">Crea una nueva cuenta para el panel administrativo.</p>

        <?php if ($message): ?>
          <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form class="form" method="POST" action="/admin/users/register">
          <?= Csrf::input(); ?>

          <div class="field">
            <label for="first_name">Nombres</label>
            <input type="text" id="first_name" name="first_name" value="<?= admin_old($old, 'first_name') ?>">
            <?php if (admin_register_error($errors, 'first_name')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_register_error($errors, 'first_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="last_name">Apellidos</label>
            <input type="text" id="last_name" name="last_name" value="<?= admin_old($old, 'last_name') ?>">
            <?php if (admin_register_error($errors, 'last_name')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_register_error($errors, 'last_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field" style="grid-column:1/-1;">
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" value="<?= admin_old($old, 'email') ?>">
            <?php if (admin_register_error($errors, 'email')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_register_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password">
            <?php if (admin_register_error($errors, 'password')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_register_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
            <?php if (admin_register_error($errors, 'password_confirmation')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(admin_register_error($errors, 'password_confirmation')) ?></small>
            <?php endif; ?>
          </div>

          <div style="display:flex; gap:10px; justify-content:flex-end; grid-column:1/-1; margin-top:8px;">
            <a href="/admin/users/login" class="btn" style="text-decoration:none;">Cancelar</a>
            <button type="submit" class="btn primary">Crear cuenta</button>
          </div>
        </form>
      </div>
    </div>

    <div class="login-right"></div>
  </div>
</body>
</html>