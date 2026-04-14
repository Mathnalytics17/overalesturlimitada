<?php
$groups = $groups ?? [];
$search = $search ?? '';
$assignedAdminId = $assignedAdminId ?? 0;
$admins = $admins ?? [];

$adminMap = [];
foreach ($admins as $admin) {
    $adminMap[(int) ($admin->id ?? 0)] = $admin;
}

$currentUrl = $_SERVER['REQUEST_URI'] ?? '/admin/sales/kanban';

$statsOverdue = 0;
$statsToday = 0;
$statsNoFollow = 0;

foreach ($groups as $stageItems) {
    foreach ($stageItems as $card) {
        $status = kanban_followup_status($card->next_follow_up_at ?? null);

        if ($status === 'overdue') {
            $statsOverdue++;
        } elseif ($status === 'today') {
            $statsToday++;
        } elseif ($status === 'none') {
            $statsNoFollow++;
        }
    }
}

function kanban_followup_status(?string $datetime): string
{
    $datetime = trim((string) $datetime);

    if ($datetime === '') {
        return 'none';
    }

    $ts = strtotime($datetime);
    if (!$ts) {
        return 'none';
    }

    $today = date('Y-m-d');
    $targetDate = date('Y-m-d', $ts);
    $now = time();

    if ($ts < $now && $targetDate < $today) {
        return 'overdue';
    }

    if ($targetDate === $today) {
        return 'today';
    }

    if ($ts < $now) {
        return 'overdue';
    }

    return 'upcoming';
}

function kanban_followup_label(?string $datetime): string
{
    return match (kanban_followup_status($datetime)) {
        'overdue' => 'Vencido',
        'today' => 'Hoy',
        'upcoming' => 'Próximo',
        default => 'Sin seguimiento',
    };
}

function kanban_followup_class(?string $datetime): string
{
    return match (kanban_followup_status($datetime)) {
        'overdue' => 'followup-overdue',
        'today' => 'followup-today',
        'upcoming' => 'followup-upcoming',
        default => 'followup-none',
    };
}

function kanban_next_actions(string $stage, bool $isAssigned): array
{
    return match ($stage) {
        'new' => array_values(array_filter([
            !$isAssigned ? ['type' => 'assign', 'label' => 'Tomar'] : null,
            ['type' => 'stage', 'stage' => 'contacted', 'label' => 'Contactado'],
        ])),
        'contacted' => [
            ['type' => 'stage', 'stage' => 'profiled', 'label' => 'Perfilado'],
            ['type' => 'stage', 'stage' => 'follow_up', 'label' => 'Seguimiento'],
        ],
        'profiled' => [
            ['type' => 'stage', 'stage' => 'quoted', 'label' => 'Cotizado'],
            ['type' => 'stage', 'stage' => 'follow_up', 'label' => 'Seguimiento'],
        ],
        'quoted' => [
            ['type' => 'stage', 'stage' => 'follow_up', 'label' => 'Seguimiento'],
            ['type' => 'stage', 'stage' => 'pending_payment', 'label' => 'Pago'],
        ],
        'follow_up' => [
            ['type' => 'stage', 'stage' => 'quoted', 'label' => 'Cotizado'],
            ['type' => 'stage', 'stage' => 'pending_payment', 'label' => 'Pago'],
        ],
        'pending_payment' => [
            ['type' => 'stage', 'stage' => 'payment_reported', 'label' => 'Reportó pago'],
            ['type' => 'won', 'label' => 'Ganada'],
        ],
        'payment_reported' => [
            ['type' => 'stage', 'stage' => 'payment_validated', 'label' => 'Validado'],
            ['type' => 'won', 'label' => 'Ganada'],
        ],
        'payment_validated' => [
            ['type' => 'won', 'label' => 'Ganada'],
        ],
        'won' => [],
        'lost' => [],
        'cancelled' => [],
        default => [],
    };
}

function kanban_stage_title(string $stage): string
{
    return match ($stage) {
        'new' => 'Nuevos',
        'contacted' => 'Contactados',
        'profiled' => 'Perfilados',
        'quoted' => 'Cotizados',
        'follow_up' => 'Seguimiento',
        'pending_payment' => 'Pago pendiente',
        'payment_reported' => 'Pago reportado',
        'payment_validated' => 'Pago validado',
        'won' => 'Ganados',
        'lost' => 'Perdidos',
        'cancelled' => 'Cancelados',
        default => ucfirst($stage),
    };
}

function kanban_stage_class(string $stage): string
{
    return match ($stage) {
        'new' => 'col-blue',
        'contacted' => 'col-cyan',
        'profiled' => 'col-purple',
        'quoted' => 'col-indigo',
        'follow_up' => 'col-amber',
        'pending_payment' => 'col-orange',
        'payment_reported' => 'col-yellow',
        'payment_validated' => 'col-teal',
        'won' => 'col-green',
        'lost' => 'col-red',
        'cancelled' => 'col-gray',
        default => 'col-gray',
    };
}

function kanban_temp_label(string $temp): string
{
    return match ($temp) {
        'cold' => 'Frío',
        'warm' => 'Medio',
        'hot' => 'Caliente',
        default => ucfirst($temp),
    };
}

$orderedStages = [
    'new',
    'contacted',
    'profiled',
    'quoted',
    'follow_up',
    'pending_payment',
    'payment_reported',
    'payment_validated',
    'won',
    'lost',
    'cancelled',
];
?>

<style>
  .kanban-page {
    display:flex;
    flex-direction:column;
    gap:20px;
  }

  .kanban-toolbar,
  .kanban-board-wrap {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:20px;
    padding:20px;
  }

  .kanban-toolbar form {
    display:grid;
    grid-template-columns: 1fr .9fr auto auto;
    gap:12px;
    align-items:end;
  }

  .kanban-toolbar label {
    display:block;
    margin-bottom:6px;
    font-weight:800;
    color:#0f172a;
    font-size:14px;
  }

  .kanban-toolbar input,
  .kanban-toolbar select {
    width:100%;
    border:1px solid #cbd5e1;
    border-radius:14px;
    padding:12px 14px;
    font:inherit;
    background:#fff;
  }

  .kanban-card.followup-overdue {
    border:1px solid #fca5a5;
    background:#fff7f7;
    box-shadow:0 0 0 1px rgba(185, 28, 28, 0.04);
  }

  .kanban-card.followup-today {
    border:1px solid #fcd34d;
    background:#fffdf3;
    box-shadow:0 0 0 1px rgba(180, 83, 9, 0.04);
  }

  .kanban-card.followup-upcoming {
    border:1px solid #86efac;
    background:#f8fff9;
    box-shadow:0 0 0 1px rgba(21, 128, 61, 0.04);
  }

  .kanban-card.followup-none {
    border:1px solid #e5e7eb;
    background:#fff;
  }

  .followup-pill {
    display:inline-flex;
    align-items:center;
    border-radius:999px;
    padding:6px 10px;
    font-size:11px;
    font-weight:900;
    border:1px solid transparent;
  }

  .followup-pill.overdue {
    background:#fef2f2;
    color:#b91c1c;
    border-color:#fecaca;
  }

  .followup-pill.today {
    background:#fffbeb;
    color:#b45309;
    border-color:#fde68a;
  }

  .followup-pill.upcoming {
    background:#f0fdf4;
    color:#15803d;
    border-color:#bbf7d0;
  }

  .followup-pill.none {
    background:#f3f4f6;
    color:#4b5563;
    border-color:#d1d5db;
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

  .kanban-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
    margin-bottom:16px;
  }

  .kanban-header h2 {
    margin:0;
    font-size:28px;
    color:#0f172a;
  }

  .kanban-header p {
    margin:6px 0 0;
    color:#64748b;
  }

  .kanban-board {
    display:grid;
    grid-template-columns: repeat(11, minmax(280px, 1fr));
    gap:16px;
    overflow-x:auto;
    padding-bottom:4px;
  }

  .kanban-col {
    border-radius:18px;
    background:#f8fafc;
    border:1px solid #e5e7eb;
    min-height:300px;
    display:flex;
    flex-direction:column;
    max-height:calc(100vh - 260px);
  }

  .kanban-col-head {
    padding:14px 14px 12px;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    position:sticky;
    top:0;
    z-index:1;
    background:inherit;
    border-top-left-radius:18px;
    border-top-right-radius:18px;
  }

  .kanban-col-head h3 {
    margin:0;
    font-size:15px;
    font-weight:900;
    color:#0f172a;
  }

  .kanban-count {
    font-size:12px;
    font-weight:900;
    padding:6px 10px;
    border-radius:999px;
    background:rgba(255,255,255,.75);
    border:1px solid rgba(0,0,0,.06);
    color:#334155;
  }

  .kanban-col-body {
    padding:14px;
    display:flex;
    flex-direction:column;
    gap:12px;
    overflow:auto;
  }

  .kanban-card {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:16px;
    padding:14px;
    box-shadow:0 2px 8px rgba(15, 23, 42, 0.04);
    display:flex;
    flex-direction:column;
    gap:10px;
  }

  .kanban-card-name {
    font-size:16px;
    font-weight:900;
    color:#0f172a;
    margin:0;
  }

  .kanban-card-sub {
    color:#64748b;
    font-size:13px;
    line-height:1.5;
  }

  .kanban-tags {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
  }

  .mini-badge {
    display:inline-flex;
    align-items:center;
    border-radius:999px;
    padding:6px 10px;
    font-size:11px;
    font-weight:900;
    background:#f1f5f9;
    color:#334155;
    border:1px solid #e2e8f0;
  }

  .kanban-meta {
    font-size:12px;
    color:#475569;
    line-height:1.6;
  }

  .kanban-actions {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:4px;
  }

  .kanban-actions a {
    color:#2563eb;
    text-decoration:none;
    font-weight:800;
    font-size:13px;
  }

  .kanban-empty {
    color:#94a3b8;
    font-size:13px;
    text-align:center;
    padding:24px 10px;
    border:1px dashed #cbd5e1;
    border-radius:14px;
    background:#fff;
  }

  .col-blue .kanban-col-head { background:#eff6ff; }
  .col-cyan .kanban-col-head { background:#ecfeff; }
  .col-purple .kanban-col-head { background:#f5f3ff; }
  .col-indigo .kanban-col-head { background:#eef2ff; }
  .col-amber .kanban-col-head { background:#fffbeb; }
  .col-orange .kanban-col-head { background:#fff7ed; }
  .col-yellow .kanban-col-head { background:#fefce8; }
  .col-teal .kanban-col-head { background:#f0fdfa; }
  .col-green .kanban-col-head { background:#f0fdf4; }
  .col-red .kanban-col-head { background:#fef2f2; }
  .col-gray .kanban-col-head { background:#f3f4f6; }

  .quick-actions {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-top:6px;
  }

  .quick-actions form {
    margin:0;
  }

  .quick-btn {
    border:none;
    border-radius:10px;
    padding:8px 10px;
    font-size:12px;
    font-weight:900;
    cursor:pointer;
    line-height:1;
  }

  .quick-btn.blue {
    background:#eff6ff;
    color:#1d4ed8;
    border:1px solid #bfdbfe;
  }

  .quick-btn.green {
    background:#f0fdf4;
    color:#15803d;
    border:1px solid #bbf7d0;
  }

  .quick-btn.red {
    background:#fef2f2;
    color:#b91c1c;
    border:1px solid #fecaca;
  }

  .quick-btn.gray {
    background:#f8fafc;
    color:#475569;
    border:1px solid #cbd5e1;
  }

  @media (max-width: 900px) {
    .kanban-toolbar form {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="app">
  <main class="main">
    <header class="topbar">
      <div class="top-left">
        <div class="page-title">
          <h1>Seguimiento de ventas</h1>
          <p>Vista kanban del pipeline comercial.</p>
        </div>
      </div>
    </header>

    <a href="/admin/sales/create" class="btn-main">Nueva oportunidad</a>

    <section class="content kanban-page">
      <div class="kanban-toolbar">
        <form method="GET" action="/admin/sales/kanban">
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
          <a href="/admin/sales?q=<?= urlencode((string) $search) ?>&assigned_admin_id=<?= (int) $assignedAdminId ?>" class="btn-outline">Ver tabla</a>
        </form>
      </div>

      <div class="kanban-board-wrap">
        <div class="kanban-header">
          <div>
            <h2>Pipeline comercial</h2>
            <p>Visualiza fácilmente en qué etapa está cada oportunidad.</p>
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
              <span class="followup-pill overdue">Vencidos: <?= (int) $statsOverdue ?></span>
              <span class="followup-pill today">Hoy: <?= (int) $statsToday ?></span>
              <span class="followup-pill none">Sin seguimiento: <?= (int) $statsNoFollow ?></span>
            </div>
          </div>
        </div>

        <div class="kanban-board">
          <?php foreach ($orderedStages as $stageKey): ?>
            <?php $stageItems = $groups[$stageKey] ?? []; ?>

            <div class="kanban-col <?= kanban_stage_class($stageKey) ?>">
              <div class="kanban-col-head">
                <h3><?= htmlspecialchars(kanban_stage_title($stageKey)) ?></h3>
                <span class="kanban-count"><?= count($stageItems) ?></span>
              </div>

              <div class="kanban-col-body">
                <?php if (empty($stageItems)): ?>
                  <div class="kanban-empty">
                    Sin oportunidades.
                  </div>
                <?php else: ?>
                  <?php foreach ($stageItems as $item): ?>
                    <?php
                      $assignedId = (int) ($item->assigned_admin_user_id ?? 0);
                      $advisor = $assignedId > 0 ? ($adminMap[$assignedId] ?? null) : null;
                      $isAssigned = $assignedId > 0;
                      $actions = kanban_next_actions((string) ($item->sales_stage ?? 'new'), $isAssigned);
                      $followStatus = kanban_followup_status($item->next_follow_up_at ?? null);
                    ?>

                    <div class="kanban-card <?= kanban_followup_class($item->next_follow_up_at ?? null) ?>">
                      <div>
                        <p class="kanban-card-name"><?= htmlspecialchars($item->customer_name ?? '') ?></p>
                        <div class="kanban-card-sub">
                          <?= htmlspecialchars($item->customer_phone ?? '') ?><br>
                          <?= htmlspecialchars($item->customer_email ?? '') ?>
                        </div>
                      </div>

                      <div class="kanban-tags">
                        <span class="mini-badge"><?= htmlspecialchars($item->interest_type ?? 'other') ?></span>
                        <span class="mini-badge"><?= htmlspecialchars(kanban_temp_label((string) ($item->sales_temperature ?? 'warm'))) ?></span>
                        <span class="followup-pill <?= htmlspecialchars($followStatus) ?>">
                          <?= htmlspecialchars(kanban_followup_label($item->next_follow_up_at ?? null)) ?>
                        </span>
                      </div>

                      <div class="kanban-meta">
                        <?php if (!empty($item->package_slug)): ?>
                          <strong>Paquete:</strong> <?= htmlspecialchars($item->package_slug) ?><br>
                        <?php endif; ?>

                        <?php if (!empty($item->extra_service_slug)): ?>
                          <strong>Servicio:</strong> <?= htmlspecialchars($item->extra_service_slug) ?><br>
                        <?php endif; ?>

                        <strong>Origen:</strong> <?= htmlspecialchars($item->source_origin ?? '') ?><br>

                        <strong>Seguimiento:</strong>
                        <?php if (!empty($item->next_follow_up_at)): ?>
                          <?= htmlspecialchars($item->next_follow_up_at) ?><br>
                        <?php else: ?>
                          Sin fecha<br>
                        <?php endif; ?>

                        <strong>Asesor:</strong>
                        <?php if ($advisor): ?>
                          <?= htmlspecialchars($advisor->full_name ?? $advisor->name ?? $advisor->email ?? ('Admin #' . $assignedId)) ?>
                        <?php elseif ($assignedId > 0): ?>
                          Admin #<?= (int) $assignedId ?>
                        <?php else: ?>
                          Sin asignar
                        <?php endif; ?>
                      </div>

                      <div class="kanban-actions">
                        <a href="/admin/sales/show?id=<?= (int) $item->id ?>&return_to=<?= urlencode($currentUrl) ?>">Ver detalle</a>
                      </div>

                      <?php if (!empty($actions)): ?>
                        <div class="quick-actions">
                          <?php foreach ($actions as $action): ?>
                            <?php if (($action['type'] ?? '') === 'assign'): ?>
                              <form method="POST" action="/admin/sales/assign">
                                <?= \app\Core\Csrf::input(); ?>
                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                                <input type="hidden" name="action" value="back">
                                <button type="submit" class="quick-btn gray">
                                  <?= htmlspecialchars($action['label']) ?>
                                </button>
                              </form>

                            <?php elseif (($action['type'] ?? '') === 'stage'): ?>
                              <form method="POST" action="/admin/sales/change-stage">
                                <?= \app\Core\Csrf::input(); ?>
                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                <input type="hidden" name="sales_stage" value="<?= htmlspecialchars($action['stage']) ?>">
                                <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                                <button type="submit" class="quick-btn blue">
                                  <?= htmlspecialchars($action['label']) ?>
                                </button>
                              </form>

                            <?php elseif (($action['type'] ?? '') === 'won'): ?>
                              <form method="POST" action="/admin/sales/mark-won">
                                <?= \app\Core\Csrf::input(); ?>
                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                                <button type="submit" class="quick-btn green">
                                  <?= htmlspecialchars($action['label']) ?>
                                </button>
                              </form>
                            <?php endif; ?>
                          <?php endforeach; ?>

                          <?php if (!in_array((string) ($item->sales_stage ?? ''), ['won', 'lost', 'cancelled'], true)): ?>
                            <form method="POST" action="/admin/sales/mark-lost" onsubmit="return confirm('¿Marcar esta oportunidad como perdida?');">
                              <?= \app\Core\Csrf::input(); ?>
                              <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                              <input type="hidden" name="lost_reason_code" value="other">
                              <input type="hidden" name="lost_reason_detail" value="Marcada como perdida desde kanban">
                              <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                              <button type="submit" class="quick-btn red">Perdida</button>
                            </form>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>
</div>