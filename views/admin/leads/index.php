<?php
$page_title = 'CRM y Leads';
$page_subtitle = 'Casos, trazabilidad y seguimiento';
$active = 'leads';
$filters = $filters ?? [];
$counts = $counts ?? [];
$leads = $leads ?? [];
$pagination = $pagination ?? [];

function lead_status_label(string $status): string
{
    return match ($status) {
        'new' => 'Nuevo',
        'in_progress' => 'En curso',
        'waiting_customer' => 'Esperando cliente',
        'closed' => 'Cerrado',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

function lead_status_badge(string $status): string
{
    return match ($status) {
        'new' => 'badge badge-blue',
        'in_progress' => 'badge badge-amber',
        'waiting_customer' => 'badge badge-purple',
        'closed' => 'badge badge-gray',
        default => 'badge badge-gray',
    };
}

function lead_source_label(string $source): string
{
    return match ($source) {
        'contact' => 'Contacto',
        'pqrs' => 'PQRS',
        'tickets' => 'Tickets',
        'package' => 'Paquete',
        default => ucfirst($source),
    };
}
?>

<style>
  .crm-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 24px;
    padding: 22px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
  }

  .crm-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .crm-head h2 {
    margin: 0;
    font-size: 18px;
    color: #0f172a;
  }

  .crm-sub {
    margin-top: 6px;
    color: #64748b;
    font-size: 14px;
    line-height: 1.5;
  }

  .crm-sep {
    height: 1px;
    background: #e5e7eb;
    margin: 14px 0 18px;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
  }

  .stat-card {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 16px 18px;
    background: #f8fafc;
  }

  .stat-label {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 8px;
    font-weight: 700;
  }

  .stat-value {
    font-size: 34px;
    line-height: 1;
    font-weight: 900;
    color: #0f172a;
  }

  .filters-bar {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(180px, 1fr) minmax(180px, 1fr) auto;
    gap: 12px;
    margin-bottom: 18px;
  }

  .field,
  .filter-btn {
    height: 50px;
    border-radius: 14px;
    font: inherit;
    box-sizing: border-box;
  }

  .field {
    width: 100%;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #0f172a;
    padding: 0 14px;
  }

  .filter-btn {
    border: none;
    background: #0ea5e9;
    color: #fff;
    padding: 0 20px;
    font-weight: 800;
    cursor: pointer;
    min-width: 110px;
  }

  .filter-btn:hover {
    transform: translateY(-1px);
  }

  .table-shell {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    overflow: hidden;
    background: #fff;
  }

  .table-wrap {
    overflow-x: auto;
  }

  .crm-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1120px;
  }

  .crm-table thead th {
    text-align: left;
    padding: 16px 14px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #334155;
    background: #eaf5fb;
    border-bottom: 1px solid #dbeafe;
    font-weight: 900;
  }

  .crm-table tbody td {
    padding: 18px 14px;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
    color: #0f172a;
  }

  .crm-table tbody tr:last-child td {
    border-bottom: none;
  }

  .crm-table tbody tr:hover {
    background: #fcfdff;
  }

  .lead-id {
    font-weight: 800;
    color: #334155;
    white-space: nowrap;
  }

  .client-name {
    font-weight: 900;
    font-size: 15px;
    color: #0f172a;
    line-height: 1.35;
    margin-bottom: 6px;
  }

  .client-meta {
    color: #475569;
    font-size: 13px;
    line-height: 1.55;
  }

  .subject-text {
    font-weight: 700;
    color: #0f172a;
    line-height: 1.45;
    min-width: 180px;
  }

  .date-text {
    color: #334155;
    font-size: 13px;
    line-height: 1.5;
    white-space: nowrap;
  }

  .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    border: 1px solid transparent;
    white-space: nowrap;
  }

  .badge-blue {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
  }

  .badge-amber {
    background: #fffbeb;
    color: #b45309;
    border-color: #fde68a;
  }

  .badge-purple {
    background: #f5f3ff;
    color: #7c3aed;
    border-color: #ddd6fe;
  }

  .badge-gray {
    background: #f3f4f6;
    color: #4b5563;
    border-color: #d1d5db;
  }

  .badge-green {
    background: #ecfdf5;
    color: #15803d;
    border-color: #bbf7d0;
  }

  .badge-sky {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
  }

  .actions-cell {
    min-width: 200px;
  }

  .row-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .chip-link,
  .chip-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #4c1d95;
    box-sizing: border-box;
  }

  .chip-link:hover,
  .chip-btn:hover {
    background: #faf5ff;
  }

  .chip-btn {
    cursor: pointer;
  }

  .chip-btn.primary {
    background: #0ea5e9;
    color: #fff;
    border-color: #0ea5e9;
  }

  .chip-btn.primary:hover {
    background: #0284c7;
  }

  .empty-box {
    padding: 28px 20px;
    text-align: center;
    color: #64748b;
    background: #fff;
  }

  @media (max-width: 1100px) {
    .stats-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .filters-bar {
      grid-template-columns: 1fr 1fr;
    }

    .filters-bar .search-field {
      grid-column: 1 / -1;
    }

    .filters-bar .filter-submit {
      grid-column: 1 / -1;
    }
  }

  @media (max-width: 720px) {
    .crm-card {
      padding: 16px;
      border-radius: 18px;
    }

    .stats-grid,
    .filters-bar {
      grid-template-columns: 1fr;
    }

    .filters-bar .search-field,
    .filters-bar .filter-submit {
      grid-column: auto;
    }

    .stat-value {
      font-size: 28px;
    }
  }
</style>

<div class="crm-card">
  <div class="crm-head">
    <div>
      <h2>Embudo operativo</h2>
      <div class="crm-sub">Cada formulario crea un caso con seguimiento, historial y tareas.</div>
    </div>
  </div>

  <div class="crm-sep"></div>

  <div class="stats-grid">
    <?php foreach ([
      'new' => 'Nuevos',
      'in_progress' => 'En curso',
      'waiting_customer' => 'Esperando cliente',
      'closed' => 'Cerrados'
    ] as $key => $label): ?>
      <div class="stat-card">
        <div class="stat-label"><?= e($label) ?></div>
        <div class="stat-value"><?= (int) ($counts[$key] ?? 0) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <form method="get" action="/admin/leads" class="filters-bar">
    <input
      class="field search-field"
      type="text"
      name="q"
      placeholder="Buscar por nombre, correo, teléfono o asunto"
      value="<?= e((string) ($filters['q'] ?? '')) ?>"
    >

    <select class="field" name="status">
      <option value="">Todos los estados</option>
      <?php foreach (['new','in_progress','waiting_customer','closed'] as $status): ?>
        <option value="<?= e($status) ?>" <?= (($filters['status'] ?? '') === $status) ? 'selected' : '' ?>>
          <?= e(lead_status_label($status)) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select class="field" name="source_type">
      <option value="">Todos los orígenes</option>
      <?php foreach (['contact','pqrs','tickets','package'] as $type): ?>
        <option value="<?= e($type) ?>" <?= (($filters['source_type'] ?? '') === $type) ? 'selected' : '' ?>>
          <?= e(lead_source_label($type)) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <button class="filter-btn filter-submit" type="submit">Filtrar</button>
  </form>

  <div class="table-shell">
    <div class="table-wrap keep-scroll">
      <table class="crm-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Origen</th>
            <th>Asunto</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Lead</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($leads === []): ?>
            <tr>
              <td colspan="8" data-label="Estado">
                <div class="empty-box">No hay casos para los filtros aplicados.</div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($leads as $lead): ?>
              <tr>
                <td class="lead-id" data-label="#">#<?= (int) $lead->id ?></td>

                <td data-label="Cliente">
                  <div class="client-name"><?= e((string) $lead->full_name) ?></div>
                  <div class="client-meta">
                    <?= e((string) $lead->email) ?><br>
                    <?= e((string) $lead->phone) ?>
                  </div>
                </td>

                <td data-label="Origen">
                  <span class="badge badge-gray">
                    <?= e(lead_source_label((string) $lead->source_type)) ?>
                  </span>
                </td>

                <td data-label="Asunto">
                  <div class="subject-text"><?= e((string) $lead->subject) ?></div>
                </td>

                <td data-label="Estado">
                  <span class="<?= e(lead_status_badge((string) $lead->status)) ?>">
                    <?= e(lead_status_label((string) $lead->status)) ?>
                  </span>
                </td>

                <td data-label="Fecha">
                  <div class="date-text"><?= e((string) $lead->created_at) ?></div>
                </td>

                <td data-label="Lead">
                  <?php if ((int)($lead->sales_opportunity_id ?? 0) > 0): ?>
                    <span class="badge badge-green">En ventas</span>
                  <?php else: ?>
                    <span class="badge badge-sky">Solo lead</span>
                  <?php endif; ?>
                </td>

                <td class="actions-cell" data-label="Acciones">
                  <div class="row-actions">
                    <a class="chip-link" href="/admin/leads/show?id=<?= (int)$lead->id ?>">Ver</a>

                    <?php if ((int)($lead->sales_opportunity_id ?? 0) > 0): ?>
                      <a class="chip-link" href="/admin/sales/show?id=<?= (int)$lead->sales_opportunity_id ?>">
                        Ver oportunidad
                      </a>
                    <?php else: ?>
                      <form method="POST" action="/admin/sales/create-from-lead" style="display:inline;">
                        <?= \app\Core\Csrf::input(); ?>
                        <input type="hidden" name="lead_id" value="<?= (int)$lead->id ?>">
                        <button type="submit" class="chip-btn primary">
                          Convertir
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?= render_pagination($pagination, $filters) ?>
</div>
