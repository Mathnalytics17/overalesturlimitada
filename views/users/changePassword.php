<?php
use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;

function cp_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<link rel="stylesheet" href="/styles/user-account.css">
<main class="account-flow"><section class="account-flow-card">
  <h1>Cambiar contrasena</h1>
  <p>Actualiza la clave de acceso de tu cuenta.</p>
  <?php if ($message): ?><div class="account-flow-alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <form class="account-flow-form" method="POST" action="/users/changePassword">
    <?= Csrf::input(); ?>
    <?php foreach ([
      'current_password' => ['Contrasena actual', 'current-password'],
      'new_password' => ['Nueva contrasena', 'new-password'],
      'new_password_confirmation' => ['Confirmar nueva contrasena', 'new-password'],
    ] as $field => [$label, $autocomplete]): ?>
      <div class="account-flow-field">
        <label for="<?= $field ?>"><?= $label ?></label>
        <input type="password" id="<?= $field ?>" name="<?= $field ?>" autocomplete="<?= $autocomplete ?>">
        <?php if (cp_error($errors, $field)): ?><small class="account-flow-error"><?= htmlspecialchars(cp_error($errors, $field)) ?></small><?php endif; ?>
      </div>
    <?php endforeach; ?>
    <div class="account-flow-actions"><a class="account-flow-btn" href="/users/user">Cancelar</a><button class="account-flow-btn primary" type="submit">Actualizar contrasena</button></div>
  </form>
</section></main>
