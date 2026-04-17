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

<div class="card" style="max-width:760px;">
  <div class="card-head">
    <div>
      <h2>Cambiar contraseña</h2>
      <p class="card-sub">Actualiza la contraseña de tu cuenta administrativa.</p>
    </div>
  </div>

  <div class="sep"></div>

  <?php if ($message): ?>
    <div
      style="
        margin-bottom:16px;
        padding:12px 14px;
        border-radius:12px;
        font-weight:700;
        background: <?= $hasErrors ? '#fef2f2' : '#ecfdf5' ?>;
        color: <?= $hasErrors ? '#b91c1c' : '#065f46' ?>;
        border: 1px solid <?= $hasErrors ? '#fecaca' : '#bbf7d0' ?>;
      "
    >
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="/admin/profile/change-password">
    <?= Csrf::input(); ?>

    <div class="form" style="grid-template-columns:1fr;">
      <div class="field">
        <label for="current_password">Contraseña actual</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password">
        <?php if (admin_cp_error($errors, 'current_password')): ?>
          <small style="color:#b91c1c; display:block; margin-top:6px;">
            <?= htmlspecialchars(admin_cp_error($errors, 'current_password')) ?>
          </small>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="new_password">Nueva contraseña</label>
        <input type="password" id="new_password" name="new_password" autocomplete="new-password">
        <?php if (admin_cp_error($errors, 'new_password')): ?>
          <small style="color:#b91c1c; display:block; margin-top:6px;">
            <?= htmlspecialchars(admin_cp_error($errors, 'new_password')) ?>
          </small>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="new_password_confirmation">Confirmar nueva contraseña</label>
        <input type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password">
        <?php if (admin_cp_error($errors, 'new_password_confirmation')): ?>
          <small style="color:#b91c1c; display:block; margin-top:6px;">
            <?= htmlspecialchars(admin_cp_error($errors, 'new_password_confirmation')) ?>
          </small>
        <?php endif; ?>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
        <a href="/admin/profile" class="btn">Cancelar</a>
        <button type="submit" class="btn primary">Actualizar contraseña</button>
      </div>
    </div>
  </form>
</div>