<?php

use app\Core\Csrf;

$packages = $packages ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];
$notificationCounts = $notificationCounts ?? ['pending' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
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

      <div class="top-left">
        <div class="page-title">
          <h1>Paquetes turísticos</h1>
          <p>Crear y administrar paquetes turísticos</p>
        </div>
      </div>

      <div class="top-right">
        <a href="/admin/packageTour/tags" class="btn" style="text-decoration:none;">Etiquetas</a>
        <a href="/admin/packageTour/analytics" class="btn" style="text-decoration:none;">Ver analítica</a>
        <a href="/admin/packageTour/create" class="btn primary" style="text-decoration:none;">+ Crear paquete</a>
      </div>


      <section class="content">

        <div class="card" style="margin-bottom:18px;">
          <div class="card-head" style="align-items:flex-start; gap:16px; flex-wrap:wrap;">
            <div>
              <h2>Notificaciones por correo</h2>
              <div class="card-sub">
                Los paquetes publicados encolan avisos generales y avisos por coincidencia de etiquetas.
                Las recomendaciones semanales se pueden encolar por cron o manualmente.
              </div>
            </div>

            <div class="summary-badges" style="display:flex; gap:8px; flex-wrap:wrap;">
              <span class="badge warn">Pendientes: <?= (int)($notificationCounts['pending'] ?? 0) ?></span>
              <span class="badge ok">Enviadas: <?= (int)($notificationCounts['sent'] ?? 0) ?></span>
              <span class="badge neutral">Omitidas: <?= (int)($notificationCounts['skipped'] ?? 0) ?></span>
              <span class="badge danger">Fallidas: <?= (int)($notificationCounts['failed'] ?? 0) ?></span>
            </div>
          </div>

          <div class="sep"></div>

          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <form method="POST" action="/admin/packageTour/notifications/process" style="margin:0;">
              <?= Csrf::input(); ?>
              <input type="hidden" name="limit" value="50">
              <button class="btn primary" type="submit">Enviar pendientes ahora</button>
            </form>

            <form method="POST" action="/admin/packageTour/notifications/queue-recommendations" style="margin:0;">
              <?= Csrf::input(); ?>
              <input type="hidden" name="limit_per_customer" value="1">
              <button class="btn" type="submit">Encolar recomendaciones semanales</button>
            </form>


          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <div>
              <h2>Paquetes</h2>
              <div class="card-sub">Listado con acciones</div>
            </div>
          </div>

          <div class="sep"></div>

          <form method="GET" action="/admin/packageTour" class="admin-filter-grid">
            <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>" placeholder="Buscar nombre, ubicacion o slug">
            <select name="status">
              <option value="">Todos los estados</option>
              <?php foreach (['draft', 'published', 'archived'] as $status): ?>
                <option value="<?= $status ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="per_page" aria-label="Registros por pagina">
              <?php foreach ([10, 25, 50, 100] as $size): ?>
                <option value="<?= $size ?>" <?= (int) ($pagination['per_page'] ?? 25) === $size ? 'selected' : '' ?>><?= $size ?> por pagina</option>
              <?php endforeach; ?>
            </select>
            <button class="btn primary" type="submit">Filtrar</button>
          </form>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Ubicación</th>
                  <th>Orden</th>
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
                    <td><span class="badge neutral"><?= (int)($package->sort_order ?? 0) ?></span></td>
                    <td><?= htmlspecialchars((string)($package->currency ?? 'COP')) ?> <?= number_format((float)($package->price_from ?? 0), 0, ',', '.') ?></td>
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
                      <div class="row-actions" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        <a class="chip" title="Ver" href="/packagesTourist/package?slug=<?= urlencode($package->slug ?? '') ?>" style="display:inline-grid; place-items:center; text-decoration:none;">👁️</a>
                        <a class="chip" title="Editar" href="/admin/packageTour/edit?id=<?= (int)$package->id ?>" style="display:inline-grid; place-items:center; text-decoration:none;">✏️</a>
                        <a class="chip" title="Copiar" href="/admin/packageTour/create?copy=<?= (int)$package->id ?>" style="display:inline-grid; place-items:center; text-decoration:none;">⧉</a>

                        <form method="POST" action="/admin/packageTour/status" style="display:inline; margin:0;">
                          <?= Csrf::input(); ?>
                          <input type="hidden" name="id" value="<?= (int)$package->id ?>">
                          <input type="hidden" name="status" value="<?= ($package->status ?? '') === 'published' ? 'draft' : 'published' ?>">
                          <button class="chip" type="submit" title="<?= ($package->status ?? '') === 'published' ? 'Pasar a borrador' : 'Publicar' ?>">
                            <?= ($package->status ?? '') === 'published' ? '📝' : '🚀' ?>
                          </button>
                        </form>

                        <form method="POST" action="/admin/packageTour/featured" style="display:inline; margin:0;">
                          <?= Csrf::input(); ?>
                          <input type="hidden" name="id" value="<?= (int)$package->id ?>">
                          <button class="chip" type="submit" title="<?= !empty($package->is_featured) ? 'Quitar destacado' : 'Marcar destacado' ?>">
                            <?= !empty($package->is_featured) ? '⭐' : '☆' ?>
                          </button>
                        </form>

                        <form method="POST" action="/admin/packageTour/delete" style="display:inline; margin:0;" onsubmit="return confirm('¿Eliminar este paquete? Esta acción lo ocultará del sistema.');">
                          <?= Csrf::input(); ?>
                          <input type="hidden" name="id" value="<?= (int)$package->id ?>">
                          <button class="chip" type="submit" title="Eliminar">🗑️</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>

                <?php if (empty($packages)): ?>
                  <tr>
                    <td colspan="7" class="t-muted">No hay paquetes registrados.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <?php
          $paginationBaseUrl = '/admin/packageTour';
          $paginationFilters = array_merge($filters, ['per_page' => $pagination['per_page'] ?? 25]);
          require dirname(__DIR__, 3) . '/shared/partials/admin_pagination.php';
          ?>
        </div>
      </section>
    </main>
  </div>
</body>

</html>