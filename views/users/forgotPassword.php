<?php use app\Core\Csrf; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Olvidé mi contraseña</title>
</head>
<body>
  <h1>Recuperar contraseña</h1>

  <?php if (!empty($message)): ?>
    <p><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="POST" action="/users/forgotPassword">
    <?= Csrf::input(); ?>

    <label>Correo</label>
    <input
      type="email"
      name="email"
      value="<?= htmlspecialchars($old['email'] ?? '') ?>"
      required
    >

    <button type="submit">Enviar enlace</button>
  </form>
</body>
</html>