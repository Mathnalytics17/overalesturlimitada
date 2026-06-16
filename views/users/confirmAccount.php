<link rel="stylesheet" href="/styles/user-account.css">
<main class="account-flow"><section class="account-flow-card">
  <h1>Confirmacion de cuenta</h1>
  <?php if (!empty($message)): ?><div class="account-flow-alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <div class="account-flow-actions">
    <?php if (!empty($success)): ?><a class="account-flow-btn primary" href="/users/login">Ir al login</a>
    <?php else: ?><a class="account-flow-btn primary" href="/users/resendVerification">Solicitar un nuevo enlace</a><?php endif; ?>
  </div>
</section></main>
