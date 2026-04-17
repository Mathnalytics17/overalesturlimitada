<?php
$items = $items ?? [];
$filters = $filters ?? [];
$counts = $counts ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Experiencias</title>
  <link rel="stylesheet" href="/public/styles/admin.css">
</head>
<body>
  <div class="app">
    <main class="main">
      
        <div class="top-left">
          <div class="page-title">
            <h1>Experiencias</h1>
            <p>Moderación de testimonios y relatos de clientes.</p>
          </div>
        </div>
  

      <section class="content">
        <div class="card">
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px;">
            <div class="stat-card"><div>Pendientes</div><strong><?= (int)($counts['pending_review'] ?? 0) ?></strong></div>
            <div class="stat-card"><div>Aprobadas</div><strong><?= (int)($counts['approved'] ?? 0) ?></strong></div>
            <div class="stat-card"><div>Rechazadas</div><strong><?= (int)($counts['rejected'] ?? 0) ?></strong></div>
            <div class="stat-card"><div>Archivadas</div><strong><?= (int)($counts['archived'] ?? 0) ?></strong></div>
          </div>

          <form method="GET" action="/admin/experiences" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;margin-bottom:18px;">
            <input type="text" name="q" placeholder="Buscar por cliente, título o texto" value="<?= htmlspecialchars((string)($filters['q'] ?? '')) ?>">
            <select name="status">
              <option value="">Todos los estados</option>
              <option value="pending_review">Pendiente</option>
              <option value="approved">Aprobada</option>
              <option value="rejected">Rechazada</option>
              <option value="archived">Archivada</option>
            </select>
            <select name="experience_type">
              <option value="">Todos los tipos</option>
              <option value="general">General</option>
              <option value="package">Paquete</option>
              <option value="tickets">Tiquetes</option>
              <option value="extra_service">Servicio extra</option>
            </select>
            <button class="btn primary" type="submit">Filtrar</button>
          </form>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cliente</th>
                  <th>Título</th>
                  <th>Tipo</th>
                  <th>Rating</th>
                  <th>Estado</th>
                  <th>Vínculos</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr><td colspan="8">No hay experiencias para los filtros aplicados.</td></tr>
                <?php else: ?>
                  <?php foreach ($items as $item): ?>
                    <tr>
                      <td>#<?= (int)$item->id ?></td>
                      <td>
                        <strong><?= htmlspecialchars($item->customer_name ?? '') ?></strong><br>
                        <small><?= htmlspecialchars($item->customer_email ?? '') ?><br><?= htmlspecialchars($item->customer_phone ?? '') ?></small>
                      </td>
                      <td><?= htmlspecialchars($item->title ?? '') ?></td>
                      <td><?= htmlspecialchars($item->experience_type ?? '') ?></td>
                      <td><?= (int)($item->rating ?? 5) ?>/5</td>
                      <td><?= htmlspecialchars($item->status ?? '') ?></td>
                      <td>
                        <small>
                          Lead: <?= !empty($item->lead_id) ? (int)$item->lead_id : '-' ?><br>
                          Venta: <?= !empty($item->sales_opportunity_id) ? (int)$item->sales_opportunity_id : '-' ?><br>
                          Reserva: <?= !empty($item->sales_order_id) ? (int)$item->sales_order_id : '-' ?>
                        </small>
                      </td>
                      <td>
                        <a class="chip" href="/admin/experiences/show?id=<?= (int)$item->id ?>">Ver</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
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