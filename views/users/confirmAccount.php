<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Confirmar cuenta</title>
</head>
<body>
  <h1>Confirmación de cuenta</h1>

  <?php if (!empty($message)): ?>
    <p><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <p><a href="/users/login">Ir al login</a></p>
  <?php else: ?>
    <p><a href="/users/resendVerification">Solicitar un nuevo enlace</a></p>
  <?php endif; ?>
</body>
</html>