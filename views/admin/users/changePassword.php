<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;

function admin_cp_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}

$hasErrors = !empty($errors);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambiar contraseña admin</title>
  <link rel="stylesheet" href="/public/styles/admin.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="brand">
        <div class="logo">SE</div>
        <div class="brand-text">
          <div class="brand-title">Smart Evolution</div>
          <div class="brand-sub">Panel administrativo</div>
        </div>
      </div>

      <nav class="nav">
        <a href="/admin"><span class="icon">🙍</span><span class="nav-text">Mi perfil</span></a>
        <a href="/admin/users/editUser"><span class="icon">✏️</span><span class="nav-text">Editar perfil</span></a>
        <a href="/admin/users/changePassword" class="active"><span class="icon">🔒</span><span class="nav-text">Cambiar contraseña</span></a>
      </nav>

      <div class="sidebar-footer">
        <div class="pill">
          <span>Seguridad</span>
          <span>●</span>
        </div>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Cambiar contraseña</h1>
            <p>Actualiza la contraseña de tu cuenta administrativa.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <div class="card" style="max-width:720px;">
          <?php if ($message): ?>
            <div style="margin-bottom:16px; color:<?= $hasErrors ? '#b91c1c' : '#065f46' ?>; font-weight:700;">
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="/admin/users/changePassword">
            <?= Csrf::input(); ?>

            <div class="field" style="margin-bottom:14px;">
              <label for="current_password">Contraseña actual</label>
              <input type="password" id="current_password" name="current_password">
              <?php if (admin_cp_error($errors, 'current_password')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(admin_cp_error($errors, 'current_password')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field" style="margin-bottom:14px;">
              <label for="new_password">Nueva contraseña</label>
              <input type="password" id="new_password" name="new_password">
              <?php if (admin_cp_error($errors, 'new_password')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(admin_cp_error($errors, 'new_password')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field" style="margin-bottom:14px;">
              <label for="new_password_confirmation">Confirmar nueva contraseña</label>
              <input type="password" id="new_password_confirmation" name="new_password_confirmation">
              <?php if (admin_cp_error($errors, 'new_password_confirmation')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(admin_cp_error($errors, 'new_password_confirmation')) ?></small>
              <?php endif; ?>
            </div>

            <button type="submit" class="btn primary">Actualizar contraseña</button>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>