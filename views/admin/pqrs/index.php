<?php
$page_title = 'PQRS';
$page_subtitle = 'Gestión de peticiones, quejas, reclamos y sugerencias';
$active = 'pqrs';

$cases = $cases ?? [];
$filters = $filters ?? [];
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>PQRS registradas</h2>
      <div class="card-sub">Listado general de solicitudes</div>
    </div>
  </div>

  <div class="sep"></div>

  <form method="get" action="/admin/pqrs" style="display:grid;grid-template-columns:1fr 1fr 1.4fr auto;gap:12px;align-items:end;margin-bottom:18px;">
    <div>
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

    <div>
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

    <div>
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

    <div>
      <button class="btn primary" type="submit">Filtrar</button>
    </div>
  </form>

  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f8fafc;text-align:left;">
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Radicado</th>
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Cliente</th>
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Tipo</th>
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Asunto</th>
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Estado</th>
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Adjuntos</th>
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Fecha</th>
          <th style="padding:12px;border-bottom:1px solid #e5e7eb;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $case): ?>
          <tr>
            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
              <?= e((string) ($case->radicado ?? '')) ?>
            </td>

            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
              <div style="font-weight:700;"><?= e((string) ($case->full_name ?? '')) ?></div>
              <div style="font-size:12px;color:#64748b;"><?= e((string) ($case->email ?? '')) ?></div>
            </td>

            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
              <?= e((string) ($case->request_type ?? '')) ?>
            </td>

            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
              <?= e((string) ($case->subject ?? '')) ?>
            </td>

            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
              <?= e((string) ($case->status ?? '')) ?>
            </td>

            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
             <?= ((int) ($case->has_attachments ?? 0) > 0) ? 'Sí' : 'No' ?>
            </td>

            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
              <?= e((string) ($case->created_at ?? '')) ?>
            </td>

            <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
              <a class="btn" href="/admin/pqrs/show?id=<?= (int) ($case->id ?? 0) ?>">Ver</a>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if ($cases === []): ?>
          <tr>
            <td colspan="8" style="padding:18px;text-align:center;color:#64748b;">
              No hay PQRS registradas con esos filtros.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>