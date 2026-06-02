<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function field_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro</title>
  <link rel="stylesheet" href="">
</head>
<body>
  <section class="login">
    <div class="login-left">
      <div class="login-card" style="width:min(560px, 94vw);">
        <h1 class="login-title">Registro de usuario</h1>
        <p class="login-sub">Completa la información para crear tu cuenta.</p>

        <?php if ($message): ?>
          <div style="margin-bottom:12px; color:#b91c1c; font-weight:700;">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form class="form" method="POST" action="/users/register">
          <?= Csrf::input(); ?>

          <div class="field">
            <label for="first_name">Nombres</label>
            <input
              type="text"
              id="first_name"
              name="first_name"
              placeholder="Juan"
              value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'first_name')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(field_error($errors, 'first_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="last_name">Apellidos</label>
            <input
              type="text"
              id="last_name"
              name="last_name"
              placeholder="Pérez"
              value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'last_name')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(field_error($errors, 'last_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="phone">Teléfono</label>
            <input
              type="text"
              id="phone"
              name="phone"
              placeholder="+57 3000000000"
              value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'phone')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(field_error($errors, 'phone')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="whatsapp">WhatsApp</label>
            <input
              type="text"
              id="whatsapp"
              name="whatsapp"
              placeholder="+57 3000000000"
              value="<?= htmlspecialchars($old['whatsapp'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'whatsapp')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(field_error($errors, 'whatsapp')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="email">Correo</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="correo@empresa.com"
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'email')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(field_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="password">Contraseña</label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="********"
            >
            <?php if (field_error($errors, 'password')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(field_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              placeholder="********"
            >
            <?php if (field_error($errors, 'password_confirmation')): ?>
              <small style="color:#b91c1c;"><?= htmlspecialchars(field_error($errors, 'password_confirmation')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field" style="display:flex; align-items:center; gap:8px;">
            <input
              type="checkbox"
              id="accepts_marketing"
              name="accepts_marketing"
              value="1"
              style="width:auto;"
              <?= !empty($old['accepts_marketing']) ? 'checked' : '' ?>
            >
            <label for="accepts_marketing" style="margin:0;">Acepto recibir información y promociones</label>
          </div>

          <div style="grid-column:1/-1; margin-top:8px;">
            <?= turnstile_widget_html(); ?>
          </div>

          <div style="margin-top:18px; display:flex; gap:10px; justify-content:flex-end; grid-column:1/-1;">
            <a href="/users/login" class="btn" style="text-decoration:none;">Cancelar</a>
            <button type="submit" class="btn primary">Crear cuenta</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</body>
</html>
