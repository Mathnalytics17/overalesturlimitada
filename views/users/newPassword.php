<?php use app\Core\Csrf; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Restablecer contraseña</title>
</head>
<body>
  <h1>Nueva contraseña</h1>

  <?php if (!empty($message)): ?>
    <p><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="POST" action="/users/resetPassword">
    <?= Csrf::input(); ?>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

    <label>Nueva contraseña</label>
    <input type="password" name="password">

    <label>Confirmar contraseña</label>
    <input type="password" name="password_confirmation">

    <button type="submit">Guardar contraseña</button>
  </form>
</body>
</html>