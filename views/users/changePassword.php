<?php use app\Core\Csrf; ?>

<?php
$errors = $errors ?? [];
$message = $message ?? null;

function cp_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambiar contraseña</title>
  <link rel="stylesheet" href="/styles/mainLayout.css">
</head>
<body>
  <div class="app">

    </aside>

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Cambiar contraseña</h1>
            <p>Actualiza la contraseña de tu cuenta.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <div class="card" style="max-width:720px;">
          <?php if ($message): ?>
            <div style="margin-bottom:16px; color:#b91c1c; font-weight:700;">
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="/users/changePassword">
            <?= Csrf::input(); ?>

            <div class="field" style="margin-bottom:14px;">
              <label for="current_password">Contraseña actual</label>
              <input type="password" id="current_password" name="current_password">
              <?php if (cp_error($errors, 'current_password')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(cp_error($errors, 'current_password')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field" style="margin-bottom:14px;">
              <label for="new_password">Nueva contraseña</label>
              <input type="password" id="new_password" name="new_password">
              <?php if (cp_error($errors, 'new_password')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(cp_error($errors, 'new_password')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field" style="margin-bottom:14px;">
              <label for="new_password_confirmation">Confirmar nueva contraseña</label>
              <input type="password" id="new_password_confirmation" name="new_password_confirmation">
              <?php if (cp_error($errors, 'new_password_confirmation')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(cp_error($errors, 'new_password_confirmation')) ?></small>
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