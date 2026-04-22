<?php
use app\Core\Csrf;

$item = $item ?? null;
$opportunity = $opportunity ?? null;
$payments = $payments ?? [];
$events = $events ?? [];

if (!$item) {
    echo 'Venta / reserva no encontrada';
    return;
}

function order_badge_class(string $status): string
{
    return match ($status) {
        'paid_full', 'completed' => 'badge green',
        'paid_partial', 'delivered' => 'badge amber',
        'booked' => 'badge blue',
        'cancelled' => 'badge red',
        default => 'badge gray',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalle de venta / reserva</title>
  <link rel="stylesheet" href="/styles/admin.css">
  <style>
    .order-show {
      display:grid;
      grid-template-columns:minmax(0, 1fr) 360px;
      gap:20px;
    }

    .panel-card {
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:20px;
      padding:20px;
    }

    .panel-card h2,
    .panel-card h3 {
      margin:0 0 14px;
      color:#0f172a;
    }

    .hero-head {
      display:flex;
      justify-content:space-between;
      align-items:start;
      gap:18px;
      flex-wrap:wrap;
      margin-bottom:18px;
    }

    .hero-title {
      font-size:32px;
      font-weight:900;
      color:#0f172a;
      margin:0;
    }

    .hero-sub {
      color:#64748b;
      margin-top:6px;
      line-height:1.6;
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

    .hero-badges,
    .order-actions {
      display:flex;
      gap:10px;
      flex-wrap:wrap;
    }

    .info-grid {
      display:grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap:16px;
    }

    .info-item {
      border:1px solid #e5e7eb;
      border-radius:16px;
      padding:14px;
      background:#fff;
    }

    .info-item .label {
      font-size:12px;
      color:#64748b;
      text-transform:uppercase;
      font-weight:800;
      margin-bottom:6px;
    }

    .info-item .value {
      color:#0f172a;
      font-size:16px;
      font-weight:700;
      line-height:1.5;
    }

    .stack {
      display:flex;
      flex-direction:column;
      gap:18px;
    }

    .timeline {
      display:flex;
      flex-direction:column;
      gap:14px;
    }

    .timeline-item {
      border:1px solid #e5e7eb;
      border-radius:16px;
      padding:14px;
      background:#f8fafc;
    }

    .timeline-title {
      font-weight:900;
      color:#0f172a;
      margin-bottom:6px;
    }

    .timeline-meta {
      font-size:12px;
      color:#64748b;
      margin-bottom:8px;
    }

    .timeline-body {
      color:#334155;
      line-height:1.6;
      white-space:pre-wrap;
    }

    .form-block {
      display:flex;
      flex-direction:column;
      gap:10px;
    }

    .form-block label {
      font-weight:800;
      color:#0f172a;
      font-size:14px;
    }

    .form-block input,
    .form-block select,
    .form-block textarea {
      width:100%;
      border:1px solid #cbd5e1;
      border-radius:14px;
      padding:12px 14px;
      font:inherit;
      background:#fff;
    }

    .form-block textarea {
      min-height:130px;
      resize:vertical;
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
      width:100%;
    }

    .btn-main { background:#0ea5e9; color:#fff; }
    .btn-outline { background:#fff; color:#4c1d95; border:1px solid #d1d5db; }

    .order-main-actions {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      flex-wrap:wrap;
      margin-bottom:18px;
    }

    @media (max-width: 1100px) {
      .order-show {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 760px) {
      .info-grid {
        grid-template-columns: 1fr;
      }

      .hero-title {
        font-size:26px;
      }

      .order-main-actions > *,
      .order-actions > * {
        width:100%;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <main class="main">
      <section class="content">
        <div class="order-main-actions">
          <div class="top-left">
            <div class="page-title">
              <h1>Detalle de venta / reserva</h1>
              <p>Control comercial y operativo de la venta cerrada.</p>
            </div>
          </div>

          <a href="/admin/sales-orders" class="btn-outline" style="width:auto;text-decoration:none;">
            Volver al listado
          </a>
        </div>

        <section class="order-show">
          <div class="stack">
            <div class="panel-card">
              <div class="hero-head">
                <div>
                  <h2 class="hero-title"><?= htmlspecialchars($item->order_number ?? '') ?></h2>
                  <div class="hero-sub">
                    <?= htmlspecialchars($item->customer_name ?? '') ?><br>
                    <?= htmlspecialchars($item->customer_phone ?? '') ?><br>
                    <?= htmlspecialchars($item->customer_email ?? '') ?>
                  </div>
                </div>

                <div class="hero-badges">
                  <span class="<?= order_badge_class((string)($item->commercial_status ?? 'open')) ?>">
                    <?= htmlspecialchars($item->commercial_status ?? 'open') ?>
                  </span>
                  <span class="<?= order_badge_class((string)($item->operational_status ?? 'pending_documents')) ?>">
                    <?= htmlspecialchars($item->operational_status ?? 'pending_documents') ?>
                  </span>
                </div>
              </div>

              <div class="info-grid">
                <div class="info-item">
                  <div class="label">Producto</div>
                  <div class="value">
                    <?= htmlspecialchars($item->product_type ?? 'other') ?><br>
                    <?= htmlspecialchars($item->package_slug ?? $item->extra_service_slug ?? 'Sin referencia') ?>
                  </div>
                </div>

                <div class="info-item">
                  <div class="label">Monto total</div>
                  <div class="value">
                    <?= number_format((float)($item->total_amount ?? 0), 0, ',', '.') ?>
                    <?= htmlspecialchars($item->currency ?? 'COP') ?>
                  </div>
                </div>

                <div class="info-item">
                  <div class="label">Pagado</div>
                  <div class="value">
                    <?= number_format((float)($item->paid_amount ?? 0), 0, ',', '.') ?>
                    <?= htmlspecialchars($item->currency ?? 'COP') ?>
                  </div>
                </div>

                <div class="info-item">
                  <div class="label">Saldo</div>
                  <div class="value">
                    <?= number_format((float)($item->balance_amount ?? 0), 0, ',', '.') ?>
                    <?= htmlspecialchars($item->currency ?? 'COP') ?>
                  </div>
                </div>

                <div class="info-item">
                  <div class="label">Viajeros</div>
                  <div class="value">
                    <?= htmlspecialchars((string)($item->travelers_count ?? 'Sin dato')) ?>
                  </div>
                </div>

                <div class="info-item">
                  <div class="label">Fechas estimadas</div>
                  <div class="value">
                    Ida: <?= htmlspecialchars($item->travel_date_estimate ?? 'Sin fecha') ?><br>
                    Regreso: <?= htmlspecialchars($item->return_date_estimate ?? 'Sin fecha') ?>
                  </div>
                </div>

                <div class="info-item">
                  <div class="label">Asesor</div>
                  <div class="value">
                    <?= !empty($item->assigned_admin_user_id) ? 'Asesor #' . (int)$item->assigned_admin_user_id : 'Sin asignar' ?>
                  </div>
                </div>

                <div class="info-item">
                  <div class="label">Oportunidad origen</div>
                  <div class="value">
                    <?php if ($opportunity): ?>
                      <a href="/admin/sales/show?id=<?= (int)$opportunity->id ?>" style="color:#2563eb;text-decoration:none;font-weight:900;">
                        Ver oportunidad #<?= (int)$opportunity->id ?>
                      </a>
                    <?php else: ?>
                      Sin vÃ­nculo
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="panel-card">
              <h3>Pagos vinculados</h3>

              <?php if (empty($payments)): ?>
                <div style="color:#64748b;">No hay pagos vinculados todavÃ­a.</div>
              <?php else: ?>
                <div class="timeline">
                  <?php foreach ($payments as $payment): ?>
                    <div class="timeline-item">
                      <div class="timeline-title">
                        <?= htmlspecialchars($payment->payment_kind ?? 'payment') ?> Â·
                        <?= number_format((float)($payment->amount ?? 0), 0, ',', '.') ?>
                        <?= htmlspecialchars($payment->currency ?? 'COP') ?>
                      </div>
                      <div class="timeline-meta">
                        Estado: <?= htmlspecialchars($payment->status ?? '') ?> Â·
                        MÃ©todo: <?= htmlspecialchars($payment->payment_method ?? '') ?><br>
                        Referencia: <?= htmlspecialchars($payment->payment_reference ?? 'Sin referencia') ?><br>
                        Reportado: <?= htmlspecialchars($payment->reported_at ?? '') ?>
                      </div>
                      <?php if (!empty($payment->notes)): ?>
                        <div class="timeline-body"><?= nl2br(htmlspecialchars($payment->notes)) ?></div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="panel-card">
              <h3>Historial comercial vinculado</h3>

              <?php if (empty($events)): ?>
                <div style="color:#64748b;">No hay eventos registrados.</div>
              <?php else: ?>
                <div class="timeline">
                  <?php foreach ($events as $event): ?>
                    <div class="timeline-item">
                      <div class="timeline-title"><?= htmlspecialchars($event->title ?? '') ?></div>
                      <div class="timeline-meta">
                        <?= htmlspecialchars($event->event_type ?? '') ?> Â· <?= htmlspecialchars($event->created_at ?? '') ?>
                      </div>
                      <?php if (!empty($event->message)): ?>
                        <div class="timeline-body"><?= nl2br(htmlspecialchars($event->message)) ?></div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="stack">
            <div class="panel-card">
              <h3>Acciones operativas</h3>

              <div class="stack">
                <form method="POST" action="/admin/sales-orders/update-operational-status" class="form-block">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= (int)$item->id ?>">
                  <input type="hidden" name="return_to" value="show">

                  <label for="operational_status">Estado operativo</label>
                  <select id="operational_status" name="operational_status">
                    <option value="pending_documents" <?= ($item->operational_status ?? '') === 'pending_documents' ? 'selected' : '' ?>>Pendiente documentos</option>
                    <option value="pending_booking" <?= ($item->operational_status ?? '') === 'pending_booking' ? 'selected' : '' ?>>Pendiente reserva</option>
                    <option value="booked" <?= ($item->operational_status ?? '') === 'booked' ? 'selected' : '' ?>>Reservado</option>
                    <option value="delivered" <?= ($item->operational_status ?? '') === 'delivered' ? 'selected' : '' ?>>Entregado</option>
                    <option value="completed" <?= ($item->operational_status ?? '') === 'completed' ? 'selected' : '' ?>>Completado</option>
                    <option value="cancelled" <?= ($item->operational_status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                  </select>

                  <button type="submit" class="btn-main">Actualizar estado</button>
                </form>

                <form method="POST" action="/admin/sales-orders/update-notes" class="form-block">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= (int)$item->id ?>">

                  <label for="notes">Notas operativas</label>
                  <textarea id="notes" name="notes"><?= htmlspecialchars($item->notes ?? '') ?></textarea>
                  <a
                    href="/experiences/share?order=<?= (int)$item->id ?>"
                    target="_blank"
                    class="btn-outline"
                    style="text-decoration:none;"
                  >
                    Enlace para pedir experiencia
                  </a>
                  <button type="submit" class="btn-outline">Guardar notas</button>
                </form>
              </div>
            </div>
          </div>
        </section>
      </section>
    </main>
  </div>
</body>
</html>
