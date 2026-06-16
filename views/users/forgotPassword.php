<?php use app\Core\Csrf; ?>
<link rel="stylesheet" href="/styles/user-account.css">
<main class="account-flow"><section class="account-flow-card">
  <h1>Recuperar contrasena</h1>
  <p>Te enviaremos un enlace seguro para crear una nueva contrasena.</p>
  <?php if (!empty($message)): ?><div class="account-flow-alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <form class="account-flow-form" method="POST" action="/users/forgotPassword">
    <?= Csrf::input(); ?>
    <div class="account-flow-field"><label for="email">Correo electronico</label><input id="email" type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" autocomplete="email" required></div>
    <div><?= turnstile_widget_html(); ?></div>
    <div class="account-flow-actions"><a class="account-flow-btn" href="/users/login">Volver</a><button class="account-flow-btn primary" type="submit">Enviar enlace</button></div>
  </form>
</section></main>
