<?php
$stats = $stats ?? [];
$pipeline = $pipeline ?? [];
$alerts = $alerts ?? [];
$products = $products ?? [];
$finance = $finance ?? [];
$pqrs = $pqrs ?? [];

function dash_money($value): string
{
    return '$' . number_format((float)$value, 0, ',', '.');
}

function dash_stage_label(string $stage): string
{
    return match ($stage) {
        'new' => 'Nuevas',
        'contacted' => 'Contactadas',
        'profiled' => 'Perfiladas',
        'quoted' => 'Cotizadas',
        'follow_up' => 'Seguimiento',
        'pending_payment' => 'Pago pendiente',
        'payment_reported' => 'Pago reportado',
        'payment_validated' => 'Pago validado',
        'won' => 'Ganadas',
        'lost' => 'Perdidas',
        'cancelled' => 'Canceladas',
        default => ucfirst($stage),
    };
}

function dash_delta_class($value): string
{
    if ($value > 0) return 'delta up';
    if ($value < 0) return 'delta down';
    return 'delta neutral';
}

function dash_delta_text($value, bool $money = false): string
{
    if ($money) {
        $abs = '$' . number_format(abs((float)$value), 0, ',', '.');
        if ($value > 0) return '+' . $abs;
        if ($value < 0) return '-' . $abs;
        return '$0';
    }

    if ($value > 0) return '+' . (int)$value;
    if ($value < 0) return (string)(int)$value;
    return '0';
}

function alert_priority_class(string $priority): string
{
    return match ($priority) {
        'high' => 'alert-item high',
        'medium' => 'alert-item medium',
        default => 'alert-item low',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="/styles/admin.css">
  <style>
    .dashboard-page {
      display:flex;
      flex-direction:column;
      gap:20px;
    }

    .dashboard-grid-top {
      display:grid;
      grid-template-columns: repeat(6, minmax(0, 1fr));
      gap:16px;
    }

    .dashboard-grid-main {
      display:grid;
      grid-template-columns: 1.2fr .8fr;
      gap:20px;
    }

    .dashboard-grid-bottom {
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:20px;
    }

    .dash-card {
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:22px;
      padding:20px;
      box-shadow:0 10px 24px rgba(15,23,42,.04);
    }

    .dash-stat-label {
      font-size:13px;
      color:#64748b;
      font-weight:700;
      margin-bottom:10px;
    }

    .dash-stat-value {
      font-size:34px;
      line-height:1;
      font-weight:900;
      color:#0f172a;
      margin-bottom:10px;
    }

    .dash-stat-sub {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
      font-size:13px;
      color:#64748b;
      flex-wrap:wrap;
    }

    .delta {
      display:inline-flex;
      align-items:center;
      border-radius:999px;
      padding:5px 10px;
      font-size:12px;
      font-weight:900;
    }

    .delta.up {
      background:#ecfdf5;
      color:#15803d;
      border:1px solid #bbf7d0;
    }

    .delta.down {
      background:#fef2f2;
      color:#b91c1c;
      border:1px solid #fecaca;
    }

    .delta.neutral {
      background:#f3f4f6;
      color:#4b5563;
      border:1px solid #d1d5db;
    }

    .dash-section-head {
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:12px;
      margin-bottom:16px;
      flex-wrap:wrap;
    }

    .dash-section-head h2,
    .dash-section-head h3 {
      margin:0;
      color:#0f172a;
    }

    .dash-section-head p {
      margin:6px 0 0;
      color:#64748b;
      font-size:14px;
    }

    .pipeline-grid {
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap:12px;
    }

    .pipeline-item {
      border:1px solid #e5e7eb;
      border-radius:16px;
      padding:14px;
      background:#f8fafc;
    }

    .pipeline-label {
      font-size:12px;
      color:#64748b;
      font-weight:800;
      text-transform:uppercase;
      margin-bottom:8px;
    }

    .pipeline-value {
      font-size:28px;
      font-weight:900;
      color:#0f172a;
    }

    .alert-group {
      display:flex;
      flex-direction:column;
      gap:16px;
    }

    .alert-box {
      border:1px solid #e5e7eb;
      border-radius:16px;
      padding:14px;
      background:#fafafa;
    }

    .alert-box h4 {
      margin:0 0 10px;
      color:#0f172a;
      font-size:15px;
    }

    .alert-list {
      display:flex;
      flex-direction:column;
      gap:10px;
    }

    .alert-item {
      display:block;
      border:1px solid #e5e7eb;
      border-radius:14px;
      padding:10px 12px;
      background:#fff;
      text-decoration:none;
      transition:.15s ease;
    }

    .alert-item:hover {
      transform:translateY(-1px);
      box-shadow:0 8px 18px rgba(15,23,42,.06);
    }

    .alert-item.high {
      border-left:5px solid #dc2626;
    }

    .alert-item.medium {
      border-left:5px solid #f59e0b;
    }

    .alert-item.low {
      border-left:5px solid #16a34a;
    }

    .alert-item strong {
      display:block;
      color:#0f172a;
      margin-bottom:4px;
    }

    .alert-item small {
      color:#64748b;
      line-height:1.5;
    }

    .empty-lite {
      color:#64748b;
      font-size:14px;
      padding:6px 0;
    }

    .simple-list {
      display:flex;
      flex-direction:column;
      gap:10px;
    }

    .simple-row {
      display:flex;
      justify-content:space-between;
      gap:14px;
      padding:12px 14px;
      border:1px solid #e5e7eb;
      border-radius:14px;
      background:#f8fafc;
      align-items:center;
    }

    .simple-row-label {
      color:#0f172a;
      font-weight:700;
    }

    .simple-row-value {
      color:#0f172a;
      font-weight:900;
    }

    .finance-grid {
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap:12px;
    }

    .finance-box {
      border:1px solid #e5e7eb;
      border-radius:16px;
      padding:16px;
      background:#f8fafc;
    }

    .finance-box .label {
      font-size:12px;
      color:#64748b;
      text-transform:uppercase;
      font-weight:800;
      margin-bottom:8px;
    }

    .finance-box .value {
      font-size:28px;
      font-weight:900;
      color:#0f172a;
      line-height:1.1;
    }

    @media (max-width: 1350px) {
      .dashboard-grid-top {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    @media (max-width: 1100px) {
      .dashboard-grid-main,
      .dashboard-grid-bottom {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 760px) {
      .dashboard-grid-top,
      .pipeline-grid,
      .finance-grid {
        grid-template-columns: 1fr;
      }

      .dash-stat-value,
      .pipeline-value,
      .finance-box .value {
        font-size:26px;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <main class="main">
    
        <div class="top-left">
          <div class="page-title">
            <h1>Dashboard</h1>
            <p>Resumen general comercial, operativo y financiero.</p>
          </div>
        </div>
      

      <section class="content dashboard-page">

        <div class="dashboard-grid-top">
          <div class="dash-card">
            <div class="dash-stat-label">Leads hoy</div>
            <div class="dash-stat-value"><?= (int)($stats['leads_today']['value'] ?? 0) ?></div>
            <div class="dash-stat-sub">
              <span><?= htmlspecialchars($stats['leads_today']['compare_label'] ?? '') ?></span>
              <span class="<?= dash_delta_class($stats['leads_today']['compare_value'] ?? 0) ?>">
                <?= dash_delta_text($stats['leads_today']['compare_value'] ?? 0) ?>
              </span>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-stat-label">Leads del mes</div>
            <div class="dash-stat-value"><?= (int)($stats['leads_month']['value'] ?? 0) ?></div>
            <div class="dash-stat-sub">
              <span><?= htmlspecialchars($stats['leads_month']['compare_label'] ?? '') ?></span>
              <span class="<?= dash_delta_class($stats['leads_month']['compare_value'] ?? 0) ?>">
                <?= dash_delta_text($stats['leads_month']['compare_value'] ?? 0) ?>
              </span>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-stat-label">Oportunidades activas</div>
            <div class="dash-stat-value"><?= (int)($stats['active_opportunities']['value'] ?? 0) ?></div>
            <div class="dash-stat-sub">
              <span><?= htmlspecialchars($stats['active_opportunities']['compare_label'] ?? '') ?></span>
              <span class="<?= dash_delta_class($stats['active_opportunities']['compare_value'] ?? 0) ?>">
                <?= dash_delta_text($stats['active_opportunities']['compare_value'] ?? 0) ?>
              </span>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-stat-label">Ventas ganadas mes</div>
            <div class="dash-stat-value"><?= (int)($stats['won_month']['value'] ?? 0) ?></div>
            <div class="dash-stat-sub">
              <span><?= htmlspecialchars($stats['won_month']['compare_label'] ?? '') ?></span>
              <span class="<?= dash_delta_class($stats['won_month']['compare_value'] ?? 0) ?>">
                <?= dash_delta_text($stats['won_month']['compare_value'] ?? 0) ?>
              </span>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-stat-label">Pagos validados mes</div>
            <div class="dash-stat-value"><?= dash_money($stats['verified_payments_month']['value'] ?? 0) ?></div>
            <div class="dash-stat-sub">
              <span><?= htmlspecialchars($stats['verified_payments_month']['compare_label'] ?? '') ?></span>
              <span class="<?= dash_delta_class($stats['verified_payments_month']['compare_value'] ?? 0) ?>">
                <?= dash_delta_text($stats['verified_payments_month']['compare_value'] ?? 0, true) ?>
              </span>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-stat-label">PQRS abiertas</div>
            <div class="dash-stat-value"><?= (int)($stats['open_pqrs']['value'] ?? 0) ?></div>
            <div class="dash-stat-sub">
              <span><?= htmlspecialchars($stats['open_pqrs']['compare_label'] ?? '') ?></span>
              <span class="<?= dash_delta_class($stats['open_pqrs']['compare_value'] ?? 0) ?>">
                <?= dash_delta_text($stats['open_pqrs']['compare_value'] ?? 0) ?>
              </span>
            </div>
          </div>
        </div>

        <div class="dashboard-grid-main">
          <div class="dash-card">
            <div class="dash-section-head">
              <div>
                <h2>Embudo comercial</h2>
                <p>Conteo por etapa actual de las oportunidades.</p>
              </div>
            </div>

            <div class="pipeline-grid">
              <?php foreach ($pipeline as $stage => $count): ?>
                <div class="pipeline-item">
                  <div class="pipeline-label"><?= htmlspecialchars(dash_stage_label((string)$stage)) ?></div>
                  <div class="pipeline-value"><?= (int)$count ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-section-head">
              <div>
                <h2>Atención inmediata</h2>
                <p>Elementos que necesitan revisión prioritaria.</p>
              </div>
            </div>

            <div class="alert-group">
              <?php
              $alertSections = [
                'followups_overdue' => 'Seguimientos vencidos',
                'unassigned_opportunities' => 'Oportunidades sin asesor',
                'payments_pending_validation' => 'Pagos pendientes de validación',
                'orders_pending_operation' => 'Reservas pendientes',
                'open_pqrs' => 'PQRS abiertas',
              ];
              ?>

              <?php foreach ($alertSections as $key => $title): ?>
                <div class="alert-box">
                  <h4><?= htmlspecialchars($title) ?></h4>

                  <?php if (empty($alerts[$key])): ?>
                    <div class="empty-lite">No hay elementos en esta sección.</div>
                  <?php else: ?>
                    <div class="alert-list">
                      <?php foreach ($alerts[$key] as $row): ?>
                        <a href="<?= htmlspecialchars($row['url'] ?? '#') ?>" class="<?= alert_priority_class((string)($row['priority'] ?? 'low')) ?>">
                          <strong><?= htmlspecialchars($row['title'] ?? '') ?></strong>
                          <small><?= htmlspecialchars($row['meta'] ?? '') ?></small>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="dashboard-grid-bottom">
          <div class="dash-card">
            <div class="dash-section-head">
              <div>
                <h3>Intereses y productos más movidos</h3>
                <p>Qué está pidiendo más la gente.</p>
              </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:18px;">
              <div>
                <h4 style="margin:0 0 10px;">Tipos de interés</h4>
                <?php if (empty($products['interest_types'])): ?>
                  <div class="empty-lite">No hay datos todavía.</div>
                <?php else: ?>
                  <div class="simple-list">
                    <?php foreach ($products['interest_types'] as $row): ?>
                      <div class="simple-row">
                        <div class="simple-row-label"><?= htmlspecialchars($row['label'] ?? '') ?></div>
                        <div class="simple-row-value"><?= (int)($row['count'] ?? 0) ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div>
                <h4 style="margin:0 0 10px;">Paquetes con más interés</h4>
                <?php if (empty($products['packages'])): ?>
                  <div class="empty-lite">No hay paquetes con datos todavía.</div>
                <?php else: ?>
                  <div class="simple-list">
                    <?php foreach ($products['packages'] as $row): ?>
                      <div class="simple-row">
                        <div class="simple-row-label"><?= htmlspecialchars($row['label'] ?? '') ?></div>
                        <div class="simple-row-value"><?= (int)($row['count'] ?? 0) ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="dash-card">
            <div class="dash-section-head">
              <div>
                <h3>Resumen financiero y PQRS</h3>
                <p>Indicadores rápidos para toma de decisiones.</p>
              </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:20px;">
              <div class="finance-grid">
                <div class="finance-box">
                  <div class="label">Cotizado abierto</div>
                  <div class="value"><?= dash_money($finance['quoted_total'] ?? 0) ?></div>
                </div>

                <div class="finance-box">
                  <div class="label">Validado total</div>
                  <div class="value"><?= dash_money($finance['verified_total'] ?? 0) ?></div>
                </div>

                <div class="finance-box">
                  <div class="label">Saldo en reservas</div>
                  <div class="value"><?= dash_money($finance['orders_balance_total'] ?? 0) ?></div>
                </div>
              </div>

              <div>
                <h4 style="margin:0 0 10px;">Estado de PQRS</h4>
                <div class="simple-list">
                  <div class="simple-row">
                    <div class="simple-row-label">Abiertas</div>
                    <div class="simple-row-value"><?= (int)($pqrs['open'] ?? 0) ?></div>
                  </div>
                  <div class="simple-row">
                    <div class="simple-row-label">En progreso</div>
                    <div class="simple-row-value"><?= (int)($pqrs['in_progress'] ?? 0) ?></div>
                  </div>
                  <div class="simple-row">
                    <div class="simple-row-label">Cerradas</div>
                    <div class="simple-row-value"><?= (int)($pqrs['closed'] ?? 0) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </section>
    </main>
  </div>
</body>
</html>