<?php

use app\Core\Csrf;

$user = $user ?? null;
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function admin_edit_value($old, $user, $field)
{
    return htmlspecialchars($old[$field] ?? $user->$field ?? '');
}

function admin_edit_error(array $errors, string $field): ?string
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
  <title>Editar administrador</title>
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
        <a href="/admin/users/editUser" class="active"><span class="icon">✏️</span><span class="nav-text">Editar perfil</span></a>
        <a href="/admin/users/changePassword"><span class="icon">🔒</span><span class="nav-text">Cambiar contraseña</span></a>
      </nav>

      <div class="sidebar-footer">
        <div class="pill">
          <span>Edición</span>
          <span>●</span>
        </div>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Editar administrador</h1>
            <p>Actualiza la información del administrador autenticado.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <div class="card">
          <div class="card-head">
            <div>
              <h2>Formulario de edición</h2>
              <p class="card-sub">Modifica tus datos administrativos.</p>
            </div>
          </div>

          <div class="sep"></div>

          <?php if ($message): ?>
            <div style="margin-bottom:16px; font-weight:700; color:<?= $hasErrors ? '#b91c1c' : '#065f46' ?>;">
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>

          <form class="form" method="POST" action="/admin/users/editUser">
            <?= Csrf::input(); ?>

            <div class="field">
              <label for="first_name">Nombres</label>
              <input type="text" id="first_name" name="first_name" value="<?= admin_edit_value($old, $user, 'first_name') ?>">
              <?php if (admin_edit_error($errors, 'first_name')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(admin_edit_error($errors, 'first_name')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field">
              <label for="last_name">Apellidos</label>
              <input type="text" id="last_name" name="last_name" value="<?= admin_edit_value($old, $user, 'last_name') ?>">
              <?php if (admin_edit_error($errors, 'last_name')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(admin_edit_error($errors, 'last_name')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field" style="grid-column:1/-1;">
              <label for="email">Correo</label>
              <input type="email" id="email" value="<?= htmlspecialchars($user->email ?? '') ?>" readonly>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px; grid-column:1/-1;">
              <a href="/admin" class="btn" style="text-decoration:none;">Cancelar</a>
              <button class="btn primary" type="submit">Guardar cambios</button>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>