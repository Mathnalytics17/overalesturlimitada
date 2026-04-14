<?php
$packages = $packages ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administración de paquetes</title>
  <link rel="stylesheet" href="/styles/admin.css">
</head>
<body>
  <div class="app">
  

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Paquetes turísticos</h1>
            <p>Crear y administrar paquetes turísticos</p>
          </div>
        </div>

        <div class="top-right">
          <a href="/admin/packageTour/create" class="btn primary" style="text-decoration:none;">+ Crear paquete</a>
        </div>
      </header>

      <section class="content">
        <div class="card">
          <div class="card-head">
            <div>
              <h2>Paquetes</h2>
              <div class="card-sub">Listado con acciones</div>
            </div>
          </div>

          <div class="sep"></div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Ubicación</th>
                  <th>Precio</th>
                  <th>Status</th>
                  <th>Destacado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($packages as $package): ?>
                  <tr>
                    <td><?= htmlspecialchars($package->title ?? '') ?></td>
                    <td class="t-muted"><?= htmlspecialchars($package->location_name ?? '') ?></td>
                    <td>$ <?= number_format((float)($package->price_from ?? 0), 0, ',', '.') ?></td>
                    <td>
                      <span class="badge <?= ($package->status ?? '') === 'published' ? 'ok' : 'warn' ?>">
                        <?= htmlspecialchars($package->status ?? '') ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge <?= !empty($package->is_featured) ? 'info' : 'neutral' ?>">
                        <?= !empty($package->is_featured) ? 'Sí' : 'No' ?>
                      </span>
                    </td>
                    <td>
                      <div class="row-actions">
                        <a class="chip" title="Ver" href="/packagesTourist/package?slug=<?= urlencode($package->slug ?? '') ?>" style="display:inline-grid; place-items:center; text-decoration:none;">👁️</a>
                        <a class="chip" title="Editar" href="/admin/packageTour/edit?id=<?= (int)$package->id ?>" style="display:inline-grid; place-items:center; text-decoration:none;">✏️</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>

                <?php if (empty($packages)): ?>
                  <tr>
                    <td colspan="6" class="t-muted">No hay paquetes registrados.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>