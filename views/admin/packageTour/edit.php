<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar paquete</title>
  <link rel="stylesheet" href="/styles/admin.css">
</head>
<body>
  <div class="app">
    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Editar paquete</h1>
            <p>Actualiza el contenido, las imágenes y el estado de publicación.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <?php
          $mode = 'edit';
          $action = '/admin/packageTour/edit';
          $submitText = 'Actualizar paquete';
          $cancelUrl = '/admin/packageTour';
          include __DIR__ . '/_form.php';
        ?>
      </section>
    </main>
  </div>
</body>
</html>