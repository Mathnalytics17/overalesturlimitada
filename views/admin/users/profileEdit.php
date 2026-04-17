<?php

use app\Core\Csrf;

$user = $user ?? null;
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function profile_value($old, $user, $field)
{
    return htmlspecialchars($old[$field] ?? $user->$field ?? '');
}

function profile_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}

$hasErrors = !empty($errors);
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Editar perfil</h2>
      <p class="card-sub">Actualiza tu información de administrador.</p>
    </div>
  </div>

  <div class="sep"></div>

  <?php if ($message): ?>
    <div style="margin-bottom:16px; font-weight:700; color:<?= $hasErrors ? '#b91c1c' : '#065f46' ?>;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form class="form" method="POST" action="/admin/profile/edit">
    <?= Csrf::input(); ?>

    <div class="field">
      <label for="first_name">Nombres</label>
      <input type="text" id="first_name" name="first_name" value="<?= profile_value($old, $user, 'first_name') ?>">
      <?php if (profile_error($errors, 'first_name')): ?>
        <small style="color:#b91c1c;"><?= htmlspecialchars(profile_error($errors, 'first_name')) ?></small>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="last_name">Apellidos</label>
      <input type="text" id="last_name" name="last_name" value="<?= profile_value($old, $user, 'last_name') ?>">
      <?php if (profile_error($errors, 'last_name')): ?>
        <small style="color:#b91c1c;"><?= htmlspecialchars(profile_error($errors, 'last_name')) ?></small>
      <?php endif; ?>
    </div>

    <div class="field" style="grid-column:1/-1;">
      <label for="email">Correo</label>
      <input type="email" id="email" value="<?= htmlspecialchars($user->email ?? '') ?>" readonly>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:18px; grid-column:1/-1;">
      <a href="/admin/profile" class="btn">Cancelar</a>
      <button class="btn primary" type="submit">Guardar cambios</button>
    </div>
  </form>
</div>