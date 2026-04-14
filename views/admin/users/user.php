<?php
$user = $user ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil admin</title>
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
        <a href="/admin" class="active"><span class="icon">🙍</span><span class="nav-text">Mi perfil</span></a>
        <a href="/admin/users/editUser"><span class="icon">✏️</span><span class="nav-text">Editar perfil</span></a>
        <a href="/admin/users/changePassword"><span class="icon">🔒</span><span class="nav-text">Cambiar contraseña</span></a>
      </nav>

      <div class="sidebar-footer">
        <div class="pill">
          <span>Mi cuenta</span>
          <span>●</span>
        </div>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Mi perfil</h1>
            <p>Información general del administrador.</p>
          </div>
        </div>

        <div class="top-right">
          <form method="POST" action="/admin/users/logout" style="margin:0;">
            <?= \app\Core\Csrf::input(); ?>
            <button class="btn danger" type="submit">Cerrar sesión</button>
          </form>
        </div>
      </header>

      <section class="content">
        <div class="grid two-col">
          <div class="card">
            <div class="card-head">
              <div>
                <h2>Información personal</h2>
                <p class="card-sub">Datos del administrador autenticado.</p>
              </div>
              <a href="/admin/users/editUser" class="btn small" style="text-decoration:none;">Editar</a>
            </div>

            <div class="sep"></div>

            <div class="form">
              <div class="field">
                <label>Nombres</label>
                <input type="text" value="<?= htmlspecialchars($user->first_name ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>Apellidos</label>
                <input type="text" value="<?= htmlspecialchars($user->last_name ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>Correo</label>
                <input type="text" value="<?= htmlspecialchars($user->email ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>Estado</label>
                <input type="text" value="<?= htmlspecialchars($user->status ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-head">
              <div>
                <h2>Estado de cuenta</h2>
                <p class="card-sub">Resumen rápido del perfil.</p>
              </div>
            </div>

            <div class="sep"></div>

            <p>
              <strong>Estado:</strong>
              <span class="badge ok"><?= htmlspecialchars($user->status ?? 'Sin estado') ?></span>
            </p>

            <p>
              <strong>Último acceso:</strong>
              <?= htmlspecialchars($user->last_login_at ?? 'Sin registro') ?>
            </p>

            <p>
              <strong>Verificación:</strong>
              <?php if (($user->email_verified_at ?? null) !== null && ($user->email_verified_at ?? '') !== ''): ?>
                <span class="badge info">Correo confirmado</span>
              <?php else: ?>
                <span class="badge warn">Pendiente</span>
              <?php endif; ?>
            </p>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>