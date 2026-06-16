<?php use app\Core\Csrf; ?>
<link rel="stylesheet" href="/styles/user-account.css">
<main class="account-flow"><section class="account-flow-card">
  <h1>Nueva contrasena</h1>
  <p>Elige una contrasena segura de al menos ocho caracteres.</p>
  <?php if (!empty($message)): ?><div class="account-flow-alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <form class="account-flow-form" method="POST" action="/users/resetPassword">
    <?= Csrf::input(); ?><input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    <div class="account-flow-field"><label for="password">Nueva contrasena</label><input id="password" type="password" name="password" autocomplete="new-password"></div>
    <div class="account-flow-field"><label for="password_confirmation">Confirmar contrasena</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"></div>
    <div class="account-flow-actions"><button class="account-flow-btn primary" type="submit">Guardar contrasena</button></div>
  </form>
</section></main>
