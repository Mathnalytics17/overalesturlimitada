<?php

use app\Core\Csrf;

$account = $account ?? null;
$customer = $customer ?? null;
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function ep_value($old, $customer, $field)
{
    return htmlspecialchars($old[$field] ?? $customer->$field ?? '');
}

function ep_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar usuario</title>
  <link rel="stylesheet" href="/styles/mainLayout.css">
</head>
<body>
  <div class="app">
 

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Editar usuario</h1>
            <p>Actualiza la información de tu perfil.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <div class="card">
          <div class="card-head">
            <div>
              <h2>Formulario de edición</h2>
              <p class="card-sub">Modifica tus datos personales.</p>
            </div>
          </div>

          <div class="sep"></div>

          <?php if ($message): ?>
            <div style="margin-bottom:16px; color:#b91c1c; font-weight:700;">
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>

          <form class="form" method="POST" action="/users/editUser">
            <?= Csrf::input(); ?>

            <div class="field">
              <label for="first_name">Nombres</label>
              <input type="text" id="first_name" name="first_name" value="<?= ep_value($old, $customer, 'first_name') ?>">
              <?php if (ep_error($errors, 'first_name')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(ep_error($errors, 'first_name')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field">
              <label for="last_name">Apellidos</label>
              <input type="text" id="last_name" name="last_name" value="<?= ep_value($old, $customer, 'last_name') ?>">
              <?php if (ep_error($errors, 'last_name')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(ep_error($errors, 'last_name')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field">
              <label for="email">Correo</label>
              <input type="email" id="email" value="<?= htmlspecialchars($account->email ?? '') ?>" readonly>
            </div>

            <div class="field">
              <label for="phone">Teléfono</label>
              <input type="text" id="phone" name="phone" value="<?= ep_value($old, $customer, 'phone') ?>">
            </div>

            <div class="field">
              <label for="whatsapp">WhatsApp</label>
              <input type="text" id="whatsapp" name="whatsapp" value="<?= ep_value($old, $customer, 'whatsapp') ?>">
            </div>

            <div class="field">
              <label for="document_type">Tipo de documento</label>
              <input type="text" id="document_type" name="document_type" value="<?= ep_value($old, $customer, 'document_type') ?>">
            </div>

            <div class="field">
              <label for="document_number">Número de documento</label>
              <input type="text" id="document_number" name="document_number" value="<?= ep_value($old, $customer, 'document_number') ?>">
            </div>

            <div class="field">
              <label for="birth_date">Fecha de nacimiento</label>
              <input type="date" id="birth_date" name="birth_date" value="<?= ep_value($old, $customer, 'birth_date') ?>">
              <?php if (ep_error($errors, 'birth_date')): ?>
                <small style="color:#b91c1c;"><?= htmlspecialchars(ep_error($errors, 'birth_date')) ?></small>
              <?php endif; ?>
            </div>

            <div class="field" style="grid-column:1/-1;">
              <label for="address">Dirección</label>
              <input type="text" id="address" name="address" value="<?= ep_value($old, $customer, 'address') ?>">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px; grid-column:1/-1;">
              <a href="/users" class="btn" style="text-decoration:none;">Cancelar</a>
              <button class="btn primary" type="submit">Guardar cambios</button>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>