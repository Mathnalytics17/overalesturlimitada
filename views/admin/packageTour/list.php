<?php
$packages = $packages ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AdministraciÃ³n de paquetes</title>
  <link rel="stylesheet" href="/styles/admin.css">
  <style>
    .package-list-page {
      display:flex;
      flex-direction:column;
      gap:18px;
    }

    .package-list-header {
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:16px;
      flex-wrap:wrap;
    }

    .package-list-table {
      min-width: 860px;
    }

    .package-list-table th,
    .package-list-table td {
      white-space: nowrap;
    }

    .package-list-table td:nth-child(1),
    .package-list-table td:nth-child(2) {
      white-space: normal;
      min-width: 180px;
    }

    .package-list-actions {
      min-width: 120px;
    }

    .package-list-chip {
      min-width: 42px;
      padding: 0 10px;
    }

    @media (max-width: 720px) {
      .package-list-header > * {
        width:100%;
      }

      .package-list-header .btn {
        justify-content:center;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <main class="main">
      <section class="content package-list-page">
        <div class="package-list-header">
          <div class="top-left">
            <div class="page-title">
              <h1>Paquetes turÃ­sticos</h1>
              <p>Crear y administrar paquetes turÃ­sticos</p>
            </div>
          </div>

          <a href="/admin/packageTour/create" class="btn primary" style="text-decoration:none;">+ Crear paquete</a>
        </div>

        <div class="card">
          <div class="card-head">
            <div>
              <h2>Paquetes</h2>
              <div class="card-sub">Listado con acciones</div>
            </div>
          </div>

          <div class="sep"></div>

          <form method="get" action="/admin/packageTour" class="admin-filter-grid" style="margin-bottom:18px;">
            <div class="admin-filter-field full">
              <label style="display:block;margin-bottom:6px;font-weight:600;">Buscar</label>
              <input type="text" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Nombre, ubicacion o slug">
            </div>
            <div class="admin-filter-actions">
              <button class="btn primary" type="submit">Filtrar</button>
              <a class="btn" href="/admin/packageTour" style="text-decoration:none;">Limpiar</a>
            </div>
          </form>

          <div class="table-wrap keep-scroll">
            <table class="package-list-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>UbicaciÃ³n</th>
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
                        <?= !empty($package->is_featured) ? 'SÃ­' : 'No' ?>
                      </span>
                    </td>
                    <td class="package-list-actions">
                      <div class="row-actions">
                        <a class="chip package-list-chip" title="Ver" href="/packagesTourist/package?slug=<?= urlencode($package->slug ?? '') ?>" style="text-decoration:none;">Ver</a>
                        <a class="chip package-list-chip" title="Editar" href="/admin/packageTour/edit?id=<?= (int)$package->id ?>" style="text-decoration:none;">Editar</a>
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

          <?= render_pagination($pagination, $filters) ?>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
