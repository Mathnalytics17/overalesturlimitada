<?php
$page_title = 'PQRS';
$page_subtitle = 'GestiÃ³n de peticiones, quejas, reclamos y sugerencias';
$active = 'pqrs';

$cases = $cases ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>PQRS registradas</h2>
      <div class="card-sub">Listado general de solicitudes</div>
    </div>
  </div>

  <div class="sep"></div>

  <form method="get" action="/admin/pqrs" class="admin-filter-grid" style="margin-bottom:18px;">
    <div class="admin-filter-field">
      <label for="status" style="display:block;margin-bottom:6px;font-weight:600;">Estado</label>
      <select name="status" id="status" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
        <option value="">Todos</option>
        <?php foreach (['new','in_progress','waiting_customer','resolved','closed'] as $status): ?>
          <option value="<?= e($status) ?>" <?= (($filters['status'] ?? '') === $status) ? 'selected' : '' ?>>
            <?= e($status) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="admin-filter-field">
      <label for="request_type" style="display:block;margin-bottom:6px;font-weight:600;">Tipo</label>
      <select name="request_type" id="request_type" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
        <option value="">Todos</option>
        <?php foreach (['peticion','queja','reclamo','sugerencia'] as $type): ?>
          <option value="<?= e($type) ?>" <?= (($filters['request_type'] ?? '') === $type) ? 'selected' : '' ?>>
            <?= e(ucfirst($type)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="admin-filter-field full">
      <label for="q" style="display:block;margin-bottom:6px;font-weight:600;">Buscar</label>
      <input
        type="text"
        name="q"
        id="q"
        value="<?= e((string) ($filters['q'] ?? '')) ?>"
        placeholder="Radicado, nombre, correo, asunto..."
        style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;"
      >
    </div>

    <div class="admin-filter-actions">
      <button class="btn primary" type="submit">Filtrar</button>
      <a class="btn" href="/admin/pqrs" style="text-decoration:none;">Limpiar</a>
    </div>
  </form>

  <div class="table-wrap keep-scroll">
    <table>
      <thead>
        <tr>
          <th>Radicado</th>
          <th>Cliente</th>
          <th>Tipo</th>
          <th>Asunto</th>
          <th>Estado</th>
          <th>Adjuntos</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $case): ?>
          <tr>
            <td data-label="Radicado">
              <?= e((string) ($case->radicado ?? '')) ?>
            </td>

            <td data-label="Cliente">
              <div style="font-weight:700;"><?= e((string) ($case->full_name ?? '')) ?></div>
              <div class="t-muted"><?= e((string) ($case->email ?? '')) ?></div>
            </td>

            <td data-label="Tipo">
              <?= e((string) ($case->request_type ?? '')) ?>
            </td>

            <td data-label="Asunto">
              <?= e((string) ($case->subject ?? '')) ?>
            </td>

            <td data-label="Estado">
              <span class="badge neutral"><?= e((string) ($case->status ?? '')) ?></span>
            </td>

            <td data-label="Adjuntos">
              <?= ((int) ($case->has_attachments ?? 0) > 0) ? 'SÃ­' : 'No' ?>
            </td>

            <td data-label="Fecha">
              <?= e((string) ($case->created_at ?? '')) ?>
            </td>

            <td data-label="Acciones">
              <a class="btn small" href="/admin/pqrs/show?id=<?= (int) ($case->id ?? 0) ?>" style="text-decoration:none;">Ver</a>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if ($cases === []): ?>
          <tr>
            <td colspan="8" data-label="Estado" style="padding:18px;text-align:center;color:#64748b;">
              No hay PQRS registradas con esos filtros.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?= render_pagination($pagination, $filters) ?>
</div>
