<?php
$items = $items ?? [];
$search = $search ?? '';
$pagination = $pagination ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ventas / Reservas</title>
  <link rel="stylesheet" href="/styles/admin.css">
  <style>
    .orders-page { display:flex; flex-direction:column; gap:20px; }
    .orders-header {
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:16px;
      flex-wrap:wrap;
    }
    .orders-toolbar,
    .orders-card {
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:20px;
      padding:20px;
    }

    .orders-toolbar form {
      display:grid;
      grid-template-columns: 1fr auto;
      gap:12px;
      align-items:end;
    }

    .orders-toolbar-actions {
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      align-items:center;
    }

    .orders-toolbar label {
      display:block;
      margin-bottom:6px;
      font-weight:800;
      color:#0f172a;
      font-size:14px;
    }

    .orders-toolbar input,
    .orders-status-form select {
      width:100%;
      border:1px solid #cbd5e1;
      border-radius:14px;
      padding:12px 14px;
      font:inherit;
      background:#fff;
    }

    .btn-main,
    .btn-outline {
      border:none;
      border-radius:14px;
      padding:12px 18px;
      font:inherit;
      font-weight:800;
      cursor:pointer;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      justify-content:center;
    }

    .btn-main { background:#0ea5e9; color:#fff; }
    .btn-outline { background:#fff; color:#4c1d95; border:1px solid #d1d5db; }

    .orders-table-wrap {
      overflow:auto;
      border:1px solid #e5e7eb;
      border-radius:18px;
    }

    .orders-table {
      width:100%;
      border-collapse:collapse;
      min-width:1100px;
      background:#fff;
    }

    .orders-table th,
    .orders-table td {
      padding:16px 14px;
      border-bottom:1px solid #eef2f7;
      text-align:left;
      vertical-align:top;
    }

    .orders-table th {
      background:#f8fafc;
      color:#334155;
      font-size:13px;
      font-weight:900;
      text-transform:uppercase;
    }

    .badge {
      display:inline-flex;
      align-items:center;
      border-radius:999px;
      padding:7px 12px;
      font-size:12px;
      font-weight:900;
      border:1px solid transparent;
      white-space:nowrap;
    }

    .badge.green { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
    .badge.amber { background:#fffbeb; color:#b45309; border-color:#fde68a; }
    .badge.blue { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
    .badge.red { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .badge.gray { background:#f3f4f6; color:#4b5563; border-color:#d1d5db; }

    .mini-text { color:#64748b; font-size:14px; line-height:1.5; }

    .orders-link {
      font-weight:900;
      color:#2563eb;
      text-decoration:none;
    }

    .orders-status-form {
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      align-items:center;
    }

    @media (max-width: 900px) {
      .orders-header {
        flex-direction:column;
        align-items:stretch;
      }

      .orders-toolbar form {
        grid-template-columns: 1fr;
      }

      .orders-toolbar-actions > * {
        flex:1 1 100%;
      }
    }

    @media (max-width: 720px) {
      .orders-toolbar,
      .orders-card {
        padding:16px;
        border-radius:18px;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <main class="main">
      <section class="content orders-page">
        <div class="orders-header">
          <div class="top-left">
            <div class="page-title">
              <h1>Ventas / Reservas</h1>
              <p>Controla las ventas cerradas y su avance operativo.</p>
            </div>
          </div>
        </div>

        <div class="orders-toolbar">
          <form method="GET" action="/admin/sales-orders">
            <div>
              <label for="q">Buscar</label>
              <input
                type="text"
                id="q"
                name="q"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="NÃºmero, cliente, paquete..."
              >
            </div>

            <div class="orders-toolbar-actions">
              <button type="submit" class="btn-main">Filtrar</button>
              <a href="/admin/sales-orders" class="btn-outline">Limpiar</a>
            </div>
          </form>
        </div>

        <div class="orders-card">
          <div class="orders-table-wrap table-wrap keep-scroll">
            <table class="orders-table">
              <thead>
                <tr>
                  <th>NÃºmero</th>
                  <th>Cliente</th>
                  <th>Producto</th>
                  <th>Valor</th>
                  <th>Pago</th>
                  <th>OperaciÃ³n</th>
                  <th>Actualizar</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr>
                    <td colspan="7" data-label="Estado" class="mini-text">No hay ventas / reservas registradas todavÃ­a.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($items as $item): ?>
                    <?php
                      $cStatus = (string)($item->commercial_status ?? 'open');
                      $cClass = match ($cStatus) {
                          'paid_full' => 'green',
                          'paid_partial' => 'amber',
                          'cancelled' => 'red',
                          default => 'blue',
                      };

                      $oStatus = (string)($item->operational_status ?? 'pending_documents');
                      $oClass = match ($oStatus) {
                          'completed' => 'green',
                          'booked' => 'blue',
                          'delivered' => 'amber',
                          'cancelled' => 'red',
                          default => 'gray',
                      };
                    ?>
                    <tr>
                      <td data-label="Numero">
                        <a href="/admin/sales-orders/show?id=<?= (int) $item->id ?>" class="orders-link">
                          <?= htmlspecialchars($item->order_number ?? '') ?>
                        </a>
                      </td>

                      <td data-label="Cliente">
                        <strong><?= htmlspecialchars($item->customer_name ?? '') ?></strong>
                        <div class="mini-text">
                          <?= htmlspecialchars($item->customer_phone ?? '') ?><br>
                          <?= htmlspecialchars($item->customer_email ?? '') ?>
                        </div>
                      </td>

                      <td data-label="Producto">
                        <strong><?= htmlspecialchars($item->product_type ?? 'other') ?></strong>
                        <div class="mini-text">
                          <?= htmlspecialchars($item->package_slug ?? $item->extra_service_slug ?? '') ?>
                        </div>
                      </td>

                      <td data-label="Valor">
                        <strong><?= number_format((float) ($item->total_amount ?? 0), 0, ',', '.') ?> <?= htmlspecialchars($item->currency ?? 'COP') ?></strong>
                        <div class="mini-text">
                          Saldo: <?= number_format((float) ($item->balance_amount ?? 0), 0, ',', '.') ?>
                        </div>
                      </td>

                      <td data-label="Pago">
                        <span class="badge <?= $cClass ?>">
                          <?= htmlspecialchars($cStatus) ?>
                        </span>
                      </td>

                      <td data-label="Operacion">
                        <span class="badge <?= $oClass ?>">
                          <?= htmlspecialchars($oStatus) ?>
                        </span>
                      </td>

                      <td data-label="Actualizar">
                        <form method="POST" action="/admin/sales-orders/update-operational-status" class="orders-status-form">
                          <?= \app\Core\Csrf::input(); ?>
                          <input type="hidden" name="id" value="<?= (int) $item->id ?>">

                          <select name="operational_status">
                            <option value="pending_documents" <?= $oStatus === 'pending_documents' ? 'selected' : '' ?>>Pendiente documentos</option>
                            <option value="pending_booking" <?= $oStatus === 'pending_booking' ? 'selected' : '' ?>>Pendiente reserva</option>
                            <option value="booked" <?= $oStatus === 'booked' ? 'selected' : '' ?>>Reservado</option>
                            <option value="delivered" <?= $oStatus === 'delivered' ? 'selected' : '' ?>>Entregado</option>
                            <option value="completed" <?= $oStatus === 'completed' ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelled" <?= $oStatus === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                          </select>

                          <button type="submit" class="btn-outline">Guardar</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?= render_pagination($pagination, ['q' => $search]) ?>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
