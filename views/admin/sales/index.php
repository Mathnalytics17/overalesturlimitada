<?php
$items = $items ?? [];
$search = $search ?? '';
$stage = $stage ?? '';
$assignedAdminId = $assignedAdminId ?? 0;
$admins = $admins ?? [];

$adminMap = [];
foreach ($admins as $admin) {
    $adminMap[(int) ($admin->id ?? 0)] = $admin;
}

$currentUrl = $_SERVER['REQUEST_URI'] ?? '/admin/sales';

function sales_stage_label(string $stage): string
{
    return match ($stage) {
        'new' => 'Nuevo',
        'contacted' => 'Contactado',
        'profiled' => 'Perfilado',
        'quoted' => 'Cotizado',
        'follow_up' => 'Seguimiento',
        'pending_payment' => 'Pago pendiente',
        'payment_reported' => 'Pago reportado',
        'payment_validated' => 'Pago validado',
        'won' => 'Ganada',
        'lost' => 'Perdida',
        'cancelled' => 'Cancelada',
        default => ucfirst($stage),
    };
}

function sales_stage_badge_class(string $stage): string
{
    return match ($stage) {
        'new' => 'badge blue',
        'contacted' => 'badge cyan',
        'profiled' => 'badge purple',
        'quoted' => 'badge indigo',
        'follow_up' => 'badge amber',
        'pending_payment' => 'badge orange',
        'payment_reported' => 'badge yellow',
        'payment_validated' => 'badge teal',
        'won' => 'badge green',
        'lost' => 'badge red',
        'cancelled' => 'badge gray',
        default => 'badge gray',
    };
}

function sales_temperature_label(string $temp): string
{
    return match ($temp) {
        'cold' => 'Frío',
        'warm' => 'Medio',
        'hot' => 'Caliente',
        default => ucfirst($temp),
    };
}
?>

<style>
  .sales-page { display:flex; flex-direction:column; gap:20px; }
  .sales-toolbar,
  .sales-card,
  .sales-empty {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:20px;
    padding:20px;
  }

  .sales-toolbar form {
    display:grid;
    grid-template-columns: 1.2fr .8fr .9fr auto auto;
    gap:12px;
    align-items:end;
  }

  .sales-toolbar label {
    display:block;
    margin-bottom:6px;
    font-weight:800;
    color:#0f172a;
    font-size:14px;
  }

  .sales-toolbar input,
  .sales-toolbar select {
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

  .btn-main {
    background:#0ea5e9;
    color:#fff;
  }

  .btn-outline {
    background:#fff;
    color:#4c1d95;
    border:1px solid #d1d5db;
  }

  .sales-card-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    margin-bottom:16px;
    flex-wrap:wrap;
  }

  .sales-card-header h2 {
    margin:0;
    font-size:28px;
    color:#0f172a;
  }

  .sales-card-header p {
    margin:6px 0 0;
    color:#64748b;
  }

  .sales-table-wrap {
    overflow:auto;
    border:1px solid #e5e7eb;
    border-radius:18px;
  }

  .sales-table {
    width:100%;
    border-collapse:collapse;
    min-width:1100px;
    background:#fff;
  }

  .sales-table th,
  .sales-table td {
    padding:16px 14px;
    border-bottom:1px solid #eef2f7;
    text-align:left;
    vertical-align:top;
  }

  .sales-table th {
    background:#f8fafc;
    color:#334155;
    font-size:13px;
    font-weight:900;
    letter-spacing:.02em;
    text-transform:uppercase;
  }

  .sales-name {
    font-weight:900;
    color:#0f172a;
    font-size:18px;
  }

  .sales-sub {
    color:#64748b;
    font-size:14px;
    margin-top:4px;
    line-height:1.5;
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

  .badge.blue { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
  .badge.cyan { background:#ecfeff; color:#0f766e; border-color:#a5f3fc; }
  .badge.purple { background:#f5f3ff; color:#7c3aed; border-color:#ddd6fe; }
  .badge.indigo { background:#eef2ff; color:#4338ca; border-color:#c7d2fe; }
  .badge.amber { background:#fffbeb; color:#b45309; border-color:#fde68a; }
  .badge.orange { background:#fff7ed; color:#c2410c; border-color:#fdba74; }
  .badge.yellow { background:#fefce8; color:#a16207; border-color:#fde047; }
  .badge.teal { background:#f0fdfa; color:#0f766e; border-color:#99f6e4; }
  .badge.green { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
  .badge.red { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
  .badge.gray { background:#f3f4f6; color:#4b5563; border-color:#d1d5db; }

  .action-links {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }

  .action-links a {
    color:#2563eb;
    font-weight:800;
    text-decoration:none;
  }

  .sales-empty {
    text-align:center;
    color:#64748b;
    padding:40px 20px;
  }

  @media (max-width: 900px) {
    .sales-toolbar form {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="app">
  <main class="main">
    
      <div class="top-left">
        <div class="page-title">
          <h1>Seguimiento de ventas</h1>
          <p>Gestiona oportunidades comerciales, seguimiento y cierre.</p>
        </div>
      </div>
    

    <a href="/admin/sales/create" class="btn-main">Nueva oportunidad</a>

    <section class="content sales-page">
      <div class="sales-toolbar">
        <form method="GET" action="/admin/sales">
          <div>
            <label for="q">Buscar</label>
            <input
              type="text"
              id="q"
              name="q"
              value="<?= htmlspecialchars($search) ?>"
              placeholder="Nombre, teléfono, email, paquete..."
            >
          </div>

          <div>
            <label for="stage">Etapa</label>
            <select id="stage" name="stage">
              <option value="">Todas</option>
              <option value="new" <?= $stage === 'new' ? 'selected' : '' ?>>Nuevo</option>
              <option value="contacted" <?= $stage === 'contacted' ? 'selected' : '' ?>>Contactado</option>
              <option value="profiled" <?= $stage === 'profiled' ? 'selected' : '' ?>>Perfilado</option>
              <option value="quoted" <?= $stage === 'quoted' ? 'selected' : '' ?>>Cotizado</option>
              <option value="follow_up" <?= $stage === 'follow_up' ? 'selected' : '' ?>>Seguimiento</option>
              <option value="pending_payment" <?= $stage === 'pending_payment' ? 'selected' : '' ?>>Pago pendiente</option>
              <option value="payment_reported" <?= $stage === 'payment_reported' ? 'selected' : '' ?>>Pago reportado</option>
              <option value="payment_validated" <?= $stage === 'payment_validated' ? 'selected' : '' ?>>Pago validado</option>
              <option value="won" <?= $stage === 'won' ? 'selected' : '' ?>>Ganada</option>
              <option value="lost" <?= $stage === 'lost' ? 'selected' : '' ?>>Perdida</option>
              <option value="cancelled" <?= $stage === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
            </select>
          </div>

          <div>
            <label for="assigned_admin_id">Asesor</label>
            <select id="assigned_admin_id" name="assigned_admin_id">
              <option value="0">Todos</option>
              <?php foreach ($admins as $admin): ?>
                <option value="<?= (int) $admin->id ?>" <?= (int) $assignedAdminId === (int) $admin->id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($admin->full_name ?? $admin->name ?? $admin->email ?? ('Admin #' . (int) $admin->id)) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn-main">Filtrar</button>
          <a href="/admin/sales" class="btn-outline">Limpiar</a>
        </form>
      </div>

      <div class="sales-card">
        <div class="sales-card-header">
          <div>
            <h2>Oportunidades</h2>
            <p><?= count($items) ?> registro(s) encontrados.</p>
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a
              href="/admin/sales/kanban?q=<?= urlencode((string) $search) ?>&assigned_admin_id=<?= (int) $assignedAdminId ?>"
              class="btn-outline"
            >
              Ver kanban
            </a>
          </div>
        </div>

        <?php if (empty($items)): ?>
          <div class="sales-empty">
            No hay oportunidades de venta para mostrar.
          </div>
        <?php else: ?>
          <div class="sales-table-wrap">
            <table class="sales-table">
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Interés</th>
                  <th>Etapa</th>
                  <th>Temperatura</th>
                  <th>Seguimiento</th>
                  <th>Origen</th>
                  <th>Asesor</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                  <?php
                    $assignedId = (int) ($item->assigned_admin_user_id ?? 0);
                    $advisor = $assignedId > 0 ? ($adminMap[$assignedId] ?? null) : null;
                  ?>
                  <tr>
                    <td>
                      <div class="sales-name"><?= htmlspecialchars($item->customer_name ?? '') ?></div>
                      <div class="sales-sub">
                        <?= htmlspecialchars($item->customer_phone ?? '') ?><br>
                        <?= htmlspecialchars($item->customer_email ?? '') ?>
                      </div>
                    </td>

                    <td>
                      <div class="sales-name" style="font-size:16px;">
                        <?= htmlspecialchars($item->interest_type ?? 'other') ?>
                      </div>
                      <div class="sales-sub">
                        <?php if (!empty($item->package_slug)): ?>
                          Paquete: <?= htmlspecialchars($item->package_slug) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($item->extra_service_slug)): ?>
                          Servicio: <?= htmlspecialchars($item->extra_service_slug) ?>
                        <?php endif; ?>
                      </div>
                    </td>

                    <td>
                      <span class="<?= sales_stage_badge_class((string) ($item->sales_stage ?? '')) ?>">
                        <?= htmlspecialchars(sales_stage_label((string) ($item->sales_stage ?? ''))) ?>
                      </span>
                    </td>

                    <td>
                      <span class="badge gray">
                        <?= htmlspecialchars(sales_temperature_label((string) ($item->sales_temperature ?? 'warm'))) ?>
                      </span>
                    </td>

                    <td>
                      <div class="sales-sub">
                        <?php if (!empty($item->next_follow_up_at)): ?>
                          Próximo: <?= htmlspecialchars($item->next_follow_up_at) ?><br>
                        <?php else: ?>
                          Sin programación<br>
                        <?php endif; ?>

                        <?php if (!empty($item->last_contact_at)): ?>
                          Último: <?= htmlspecialchars($item->last_contact_at) ?>
                        <?php endif; ?>
                      </div>
                    </td>

                    <td>
                      <div class="sales-sub">
                        <?= htmlspecialchars($item->source_origin ?? '') ?><br>
                        Canal: <?= htmlspecialchars($item->source_channel ?? '') ?>
                      </div>
                    </td>

                    <td>
                      <div class="sales-sub">
                        <?php if ($advisor): ?>
                          <?= htmlspecialchars($advisor->full_name ?? $advisor->name ?? $advisor->email ?? ('Admin #' . $assignedId)) ?>
                        <?php elseif ($assignedId > 0): ?>
                          Admin #<?= (int) $assignedId ?>
                        <?php else: ?>
                          Sin asignar
                        <?php endif; ?>
                      </div>
                    </td>

                    <td>
                      <div class="action-links">
                        <a href="/admin/sales/show?id=<?= (int) $item->id ?>&return_to=<?= urlencode($currentUrl) ?>">
                          Ver detalle
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>
</div>