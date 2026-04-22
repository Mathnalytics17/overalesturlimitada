<?php
$items = $items ?? [];
$filters = $filters ?? [];
$counts = $counts ?? [];
$pagination = $pagination ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Experiencias</title>
  <link rel="stylesheet" href="/public/styles/admin.css">
  <style>
    .exp-admin-page {
      display:flex;
      flex-direction:column;
      gap:18px;
    }

    .exp-admin-stats {
      display:grid;
      grid-template-columns:repeat(4, minmax(0, 1fr));
      gap:12px;
      margin-bottom:18px;
    }

    .exp-admin-stat {
      border:1px solid #e5e7eb;
      border-radius:18px;
      padding:16px;
      background:#f8fafc;
    }

    .exp-admin-stat div {
      color:#64748b;
      font-size:13px;
      margin-bottom:8px;
    }

    .exp-admin-stat strong {
      display:block;
      font-size:30px;
      line-height:1;
      color:#0f172a;
    }

    @media (max-width: 980px) {
      .exp-admin-stats {
        grid-template-columns:repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 720px) {
      .exp-admin-stats {
        grid-template-columns:1fr;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <main class="main">
      <section class="content exp-admin-page">
        <div class="top-left">
          <div class="page-title">
            <h1>Experiencias</h1>
            <p>ModeraciÃ³n de testimonios y relatos de clientes.</p>
          </div>
        </div>

        <div class="card">
          <div class="exp-admin-stats">
            <div class="exp-admin-stat"><div>Pendientes</div><strong><?= (int)($counts['pending_review'] ?? 0) ?></strong></div>
            <div class="exp-admin-stat"><div>Aprobadas</div><strong><?= (int)($counts['approved'] ?? 0) ?></strong></div>
            <div class="exp-admin-stat"><div>Rechazadas</div><strong><?= (int)($counts['rejected'] ?? 0) ?></strong></div>
            <div class="exp-admin-stat"><div>Archivadas</div><strong><?= (int)($counts['archived'] ?? 0) ?></strong></div>
          </div>

          <form method="GET" action="/admin/experiences" class="admin-filter-grid" style="margin-bottom:18px;">
            <div class="admin-filter-field full">
              <label style="display:block;margin-bottom:6px;font-weight:600;">Buscar</label>
              <input type="text" name="q" placeholder="Buscar por cliente, tÃ­tulo o texto" value="<?= htmlspecialchars((string)($filters['q'] ?? '')) ?>">
            </div>
            <div class="admin-filter-field">
              <label style="display:block;margin-bottom:6px;font-weight:600;">Estado</label>
              <select name="status">
                <option value="">Todos los estados</option>
                <option value="pending_review">Pendiente</option>
                <option value="approved">Aprobada</option>
                <option value="rejected">Rechazada</option>
                <option value="archived">Archivada</option>
              </select>
            </div>
            <div class="admin-filter-field">
              <label style="display:block;margin-bottom:6px;font-weight:600;">Tipo</label>
              <select name="experience_type">
                <option value="">Todos los tipos</option>
                <option value="general">General</option>
                <option value="package">Paquete</option>
                <option value="tickets">Tiquetes</option>
                <option value="extra_service">Servicio extra</option>
              </select>
            </div>
            <div class="admin-filter-actions">
              <button class="btn primary" type="submit">Filtrar</button>
              <a class="btn" href="/admin/experiences" style="text-decoration:none;">Limpiar</a>
            </div>
          </form>

          <div class="table-wrap keep-scroll">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cliente</th>
                  <th>TÃ­tulo</th>
                  <th>Tipo</th>
                  <th>Rating</th>
                  <th>Estado</th>
                  <th>VÃ­nculos</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr><td colspan="8" data-label="Estado">No hay experiencias para los filtros aplicados.</td></tr>
                <?php else: ?>
                  <?php foreach ($items as $item): ?>
                    <tr>
                      <td data-label="#">#<?= (int)$item->id ?></td>
                      <td data-label="Cliente">
                        <strong><?= htmlspecialchars($item->customer_name ?? '') ?></strong><br>
                        <small><?= htmlspecialchars($item->customer_email ?? '') ?><br><?= htmlspecialchars($item->customer_phone ?? '') ?></small>
                      </td>
                      <td data-label="Titulo"><?= htmlspecialchars($item->title ?? '') ?></td>
                      <td data-label="Tipo"><?= htmlspecialchars($item->experience_type ?? '') ?></td>
                      <td data-label="Rating"><?= (int)($item->rating ?? 5) ?>/5</td>
                      <td data-label="Estado"><span class="badge neutral"><?= htmlspecialchars($item->status ?? '') ?></span></td>
                      <td data-label="Vinculos">
                        <small>
                          Lead: <?= !empty($item->lead_id) ? (int)$item->lead_id : '-' ?><br>
                          Venta: <?= !empty($item->sales_opportunity_id) ? (int)$item->sales_opportunity_id : '-' ?><br>
                          Reserva: <?= !empty($item->sales_order_id) ? (int)$item->sales_order_id : '-' ?>
                        </small>
                      </td>
                      <td data-label="Acciones">
                        <a class="btn small" href="/admin/experiences/show?id=<?= (int)$item->id ?>" style="text-decoration:none;">Ver</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
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
