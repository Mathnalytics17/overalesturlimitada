<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear paquete</title>
  <link rel="stylesheet" href="/styles/admin.css">
</head>
<body>
  <div class="app">
    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Crear paquete</h1>
            <p>Crea el paquete por bloques y guárdalo como borrador o publícalo.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <?php
          $mode = 'create';
          $action = '/admin/packageTour/create';
          $submitText = 'Guardar paquete';
          $cancelUrl = '/admin/packageTour';
          include __DIR__ . '/_form.php';
        ?>
      </section>
    </main>
  </div>
</body>
</html>