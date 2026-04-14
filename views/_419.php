<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitud expirada</title>
</head>
<body>
  <main style="max-width:720px;margin:60px auto;padding:0 16px;font-family:Arial,sans-serif;">
    <h1>La solicitud expiró</h1>
    <p><?= e($message ?? 'El token de seguridad del formulario expiró. Vuelve a intentarlo.') ?></p>
    <p><a href="javascript:history.back()">Volver</a></p>
  </main>
</body>
</html>
