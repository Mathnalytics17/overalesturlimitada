<?php
$page_title = 'Detalle PQRS';
$page_subtitle = 'Seguimiento completo del caso';
$active = 'pqrs';

$case = $case ?? null;
$attachments = $attachments ?? [];
$events = $events ?? [];
$tasks = $tasks ?? [];
$assignedAdvisor = $assignedAdvisor ?? null;
$currentAdminId = (int) ($currentAdminId ?? 0);

$assignedAdminId = (int) ($case->assigned_admin_user_id ?? 0);
$isAssigned = $assignedAdminId > 0;
$isMine = $isAssigned && $assignedAdminId === $currentAdminId;
?>

<style>
  .pqrs-detail-page {
    display:flex;
    flex-direction:column;
    gap:18px;
  }

  .pqrs-detail-layout {
    display:grid;
    grid-template-columns:1.2fr .8fr;
    gap:18px;
    align-items:start;
  }

  .pqrs-detail-main,
  .pqrs-detail-sidebar {
    display:flex;
    flex-direction:column;
    gap:18px;
  }

  .pqrs-detail-meta {
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:12px;
  }

  .pqrs-detail-section {
    margin-top:18px;
  }

  .pqrs-detail-body,
  .pqrs-detail-item {
    padding:12px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    background:#fff;
  }

  .pqrs-detail-body {
    background:#f8fafc;
    margin-top:8px;
  }

  .pqrs-detail-list {
    display:flex;
    flex-direction:column;
    gap:10px;
    margin-top:12px;
  }

  .pqrs-detail-small {
    font-size:12px;
    color:#64748b;
  }

  .pqrs-detail-form textarea,
  .pqrs-detail-form input,
  .pqrs-detail-form select {
    margin:10px 0;
  }

  @media (max-width: 980px) {
    .pqrs-detail-layout {
      grid-template-columns:1fr;
    }
  }

  @media (max-width: 720px) {
    .pqrs-detail-meta {
      grid-template-columns:1fr;
    }
  }
</style>

<section class="pqrs-detail-page">
  <div class="pqrs-detail-layout">
    <div class="pqrs-detail-main">
      <div class="card">
        <div class="card-head">
          <div>
            <h2><?= e((string) ($case->radicado ?? 'PQRS')) ?></h2>
            <div class="card-sub">
              <?= e((string) ($case->request_type ?? '')) ?> Â· <?= e((string) ($case->status ?? '')) ?>
            </div>
          </div>
        </div>

        <div class="sep"></div>

        <div class="pqrs-detail-meta">
          <div><strong>Cliente:</strong><br><?= e((string) ($case->full_name ?? '')) ?></div>
          <div><strong>Correo:</strong><br><?= e((string) ($case->email ?? '')) ?></div>
          <div><strong>TelÃ©fono:</strong><br><?= e((string) ($case->phone ?? '')) ?></div>
          <div>
            <strong>Responsable:</strong><br>
            <?php if ($assignedAdvisor): ?>
              <?= e((string) ($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? 'Asesor')) ?>
            <?php else: ?>
              Sin asignar
            <?php endif; ?>
          </div>
          <div><strong>Asunto:</strong><br><?= e((string) ($case->subject ?? '')) ?></div>
          <div><strong>Creado:</strong><br><?= e((string) ($case->created_at ?? '')) ?></div>
        </div>

        <div class="pqrs-detail-section">
          <strong>DescripciÃ³n</strong>
          <div class="pqrs-detail-body">
            <?= nl2br(e((string) ($case->message ?? ''))) ?>
          </div>
        </div>

        <div class="pqrs-detail-section">
          <strong>Adjuntos</strong>

          <div class="pqrs-detail-list">
            <div class="pqrs-detail-small" style="margin-top:2px;">
              Caso ID: <?= (int) ($case->id ?? 0) ?> Â· Adjuntos encontrados: <?= is_array($attachments) ? count($attachments) : 0 ?>
            </div>

            <?php foreach ($attachments as $file): ?>
              <div class="pqrs-detail-item">
                <div style="font-weight:700;"><?= e((string) ($file->original_name ?? 'Archivo')) ?></div>

                <div class="pqrs-detail-small" style="margin-top:4px;">
                  ID adjunto: <?= (int) ($file->id ?? 0) ?>
                  Â· Caso: <?= (int) ($file->pqrs_case_id ?? 0) ?>
                  Â· <?= e((string) ($file->mime_type ?? '')) ?>
                  <?php if (!empty($file->file_size)): ?>
                    Â· <?= e((string) $file->file_size) ?> bytes
                  <?php endif; ?>
                </div>

                <div class="pqrs-detail-small" style="margin-top:4px;">
                  Ruta: <?= e((string) ($file->file_path ?? '')) ?>
                </div>

                <div style="margin-top:8px;">
                  <a class="btn" target="_blank" rel="noopener" href="/admin/pqrs/attachment?id=<?= (int) ($file->id ?? 0) ?>">
                    Ver / Descargar
                  </a>
                </div>
              </div>
            <?php endforeach; ?>

            <?php if ($attachments === []): ?>
              <div class="pqrs-detail-small">No hay adjuntos registrados en este caso.</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="pqrs-detail-section">
          <strong>Trazabilidad</strong>
          <div class="pqrs-detail-list">
            <?php foreach ($events as $event): ?>
              <div class="pqrs-detail-item">
                <div class="pqrs-detail-small">
                  <?= e((string) ($event->created_at ?? '')) ?> Â· <?= e((string) ($event->event_type ?? '')) ?>
                </div>
                <div style="margin-top:6px;"><?= nl2br(e((string) ($event->message ?? ''))) ?></div>
              </div>
            <?php endforeach; ?>

            <?php if ($events === []): ?>
              <div class="pqrs-detail-small">No hay eventos registrados todavÃ­a.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="pqrs-detail-sidebar">
      <?php if (!$isAssigned): ?>
        <div class="card">
          <h3>Tomar caso</h3>
          <p style="margin:0 0 12px 0;color:#64748b;">Este caso aÃºn no tiene responsable. Puedes tomarlo para gestionarlo.</p>
          <form method="post" action="/admin/pqrs/take">
            <?= \app\Core\Csrf::input(); ?>
            <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
            <button class="btn primary" type="submit">Tomar caso</button>
          </form>
        </div>

      <?php elseif ($isMine): ?>
        <div class="card">
          <h3>Responsabilidad del caso</h3>
          <p style="margin:0 0 12px 0;color:#16a34a;font-weight:700;">Este caso estÃ¡ asignado a ti.</p>

          <?php if (!in_array((string) ($case->status ?? ''), ['closed'], true)): ?>
            <form method="post" action="/admin/pqrs/release">
              <?= \app\Core\Csrf::input(); ?>
              <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
              <button class="btn" type="submit">Soltar caso</button>
            </form>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <div class="card">
          <h3>Responsabilidad del caso</h3>
          <p style="margin:0;color:#64748b;">
            Este caso estÃ¡ asignado a
            <strong><?= e((string) ($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? 'otro asesor')) ?></strong>.
          </p>
        </div>
      <?php endif; ?>

      <div class="card">
        <h3>Actualizar estado</h3>
        <form method="post" action="/admin/pqrs/status" class="pqrs-detail-form">
          <?= \app\Core\Csrf::input(); ?>
          <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">

          <select name="status">
            <?php foreach (['new','in_progress','waiting_customer','resolved','closed'] as $status): ?>
              <option value="<?= e($status) ?>" <?= (($case->status ?? '') === $status) ? 'selected' : '' ?>>
                <?= e($status) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button class="btn primary" type="submit">Guardar</button>
        </form>
      </div>

      <div class="card">
        <h3>Agregar nota</h3>
        <form method="post" action="/admin/pqrs/note" class="pqrs-detail-form">
          <?= \app\Core\Csrf::input(); ?>
          <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
          <textarea name="message" style="min-height:120px;"></textarea>
          <button class="btn primary" type="submit">Guardar nota</button>
        </form>
      </div>

      <div class="card">
        <h3>Tareas</h3>
        <form method="post" action="/admin/pqrs/task" class="pqrs-detail-form" style="margin-bottom:14px;">
          <?= \app\Core\Csrf::input(); ?>
          <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
          <input type="text" name="title" placeholder="TÃ­tulo">
          <textarea name="description" placeholder="DescripciÃ³n" style="min-height:80px;"></textarea>
          <input type="datetime-local" name="due_at">
          <button class="btn primary" type="submit">Crear tarea</button>
        </form>

        <div class="pqrs-detail-list" style="margin-top:0;">
          <?php foreach ($tasks as $task): ?>
            <div class="pqrs-detail-item">
              <div style="font-weight:700;"><?= e((string) ($task->title ?? '')) ?></div>
              <div class="pqrs-detail-small">
                Estado: <?= e((string) ($task->status ?? '')) ?>
                <?= !empty($task->due_at) ? ' Â· vence ' . e((string) $task->due_at) : '' ?>
              </div>

              <?php if (!empty($task->description)): ?>
                <div style="margin-top:6px;"><?= nl2br(e((string) $task->description)) ?></div>
              <?php endif; ?>

              <?php if (($task->status ?? '') !== 'done'): ?>
                <form method="post" action="/admin/pqrs/task/complete" style="margin-top:8px;">
                  <?= \app\Core\Csrf::input(); ?>
                  <input type="hidden" name="task_id" value="<?= (int) ($task->id ?? 0) ?>">
                  <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
                  <button class="btn" type="submit">Marcar completada</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <?php if ($tasks === []): ?>
            <div class="pqrs-detail-small">No hay tareas registradas.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
