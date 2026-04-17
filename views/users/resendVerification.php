<?php use app\Core\Csrf; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reenviar verificación</title>
</head>
<body>
  <h1>Reenviar verificación</h1>

  <?php if (!empty($message)): ?>
    <p><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="POST" action="/users/resendVerification">
    <?= Csrf::input(); ?>
    <label>Correo</label>
    <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
    <div style="margin:12px 0;">
      <?= turnstile_widget_html(); ?>
    </div>
    <button type="submit">Reenviar enlace</button>
  </form>
</body>
</html>
