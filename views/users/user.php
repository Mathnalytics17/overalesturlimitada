<?php
$account = $account ?? null;
$customer = $customer ?? null;


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de usuario</title>
  <link rel="stylesheet" href="/styles/mainLayout.css">
</head>
<body>
  <div class="app">


    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Mi perfil</h1>
            <p>Información general de tu cuenta.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <div class="grid two-col">
          <div class="card">
            <div class="card-head">
              <div>
                <h2>Información personal</h2>
                <p class="card-sub">Datos del usuario autenticado.</p>
              </div>
              <a href="/users/editUser" class="btn small" style="text-decoration:none;">Editar</a>
              <a href="/users/changePassword" class="btn small" style="text-decoration:none;">Cambiar contraseña</a>
            </div>

            <div class="sep"></div>

            <div class="form">
              <div class="field">
                <label>Nombres</label>
                <input type="text" value="<?= htmlspecialchars($customer->first_name ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>Apellidos</label>
                <input type="text" value="<?= htmlspecialchars($customer->last_name ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>Correo</label>
                <input type="text" value="<?= htmlspecialchars($account->email ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>Estado</label>
                <input type="text" value="<?= htmlspecialchars($account->status ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>Teléfono</label>
                <input type="text" value="<?= htmlspecialchars($customer->phone ?? '') ?>" readonly>
              </div>

              <div class="field">
                <label>WhatsApp</label>
                <input type="text" value="<?= htmlspecialchars($customer->whatsapp ?? '') ?>" readonly>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-head">
              <div>
                <h2>Estado de cuenta</h2>
                <p class="card-sub">Resumen rápido de tu perfil.</p>
              </div>
            </div>

            <div class="sep"></div>

            <p>
              <strong>Estado:</strong>
              <span class="badge ok"><?= htmlspecialchars($account->status ?? 'Sin estado') ?></span>
            </p>

            <p>
              <strong>Último acceso:</strong>
              <?= htmlspecialchars($account->last_login_at ?? 'Sin registro') ?>
            </p>

            <p>
              <strong>Verificación:</strong>
              <?php if (($account->email_verified_at ?? null) !== null && ($account->email_verified_at ?? '') !== ''): ?>
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