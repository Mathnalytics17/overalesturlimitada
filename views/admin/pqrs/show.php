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

<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:18px;align-items:start;">
  <div class="card">
    <div class="card-head">
      <div>
        <h2><?= e((string) ($case->radicado ?? 'PQRS')) ?></h2>
        <div class="card-sub">
          <?= e((string) ($case->request_type ?? '')) ?> · <?= e((string) ($case->status ?? '')) ?>
        </div>
      </div>
    </div>

    <div class="sep"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div><strong>Cliente:</strong><br><?= e((string) ($case->full_name ?? '')) ?></div>
      <div><strong>Correo:</strong><br><?= e((string) ($case->email ?? '')) ?></div>
      <div><strong>Teléfono:</strong><br><?= e((string) ($case->phone ?? '')) ?></div>
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

    <div style="margin-top:16px;">
      <strong>Descripción</strong>
      <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;margin-top:8px;">
        <?= nl2br(e((string) ($case->message ?? ''))) ?>
      </div>
    </div>

<div style="margin-top:18px;">
  <strong>Adjuntos</strong>

  <div style="margin-top:12px;">
    <div style="font-size:12px;color:#64748b;margin-bottom:10px;">
      Caso ID: <?= (int) ($case->id ?? 0) ?> · Adjuntos encontrados: <?= is_array($attachments) ? count($attachments) : 0 ?>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px;">
      <?php foreach ($attachments as $file): ?>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
          <div style="font-weight:700;"><?= e((string) ($file->original_name ?? 'Archivo')) ?></div>

          <div style="font-size:12px;color:#64748b;margin-top:4px;">
            ID adjunto: <?= (int) ($file->id ?? 0) ?>
            · Caso: <?= (int) ($file->pqrs_case_id ?? 0) ?>
            · <?= e((string) ($file->mime_type ?? '')) ?>
            <?php if (!empty($file->file_size)): ?>
              · <?= e((string) $file->file_size) ?> bytes
            <?php endif; ?>
          </div>

          <div style="font-size:12px;color:#64748b;margin-top:4px;">
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
        <div style="color:#64748b;">No hay adjuntos registrados en este caso.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

    <div style="margin-top:18px;">
      <strong>Trazabilidad</strong>
      <div style="display:flex;flex-direction:column;gap:10px;margin-top:12px;">
        <?php foreach ($events as $event): ?>
          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
            <div style="font-size:12px;color:#64748b;">
              <?= e((string) ($event->created_at ?? '')) ?> · <?= e((string) ($event->event_type ?? '')) ?>
            </div>
            <div style="margin-top:6px;"><?= nl2br(e((string) ($event->message ?? ''))) ?></div>
          </div>
        <?php endforeach; ?>

        <?php if ($events === []): ?>
          <div style="color:#64748b;">No hay eventos registrados todavía.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:18px;">
    <?php if (!$isAssigned): ?>
      <div class="card">
        <h3>Tomar caso</h3>
        <p style="margin:0 0 12px 0;color:#64748b;">Este caso aún no tiene responsable. Puedes tomarlo para gestionarlo.</p>
        <form method="post" action="/admin/pqrs/take">
          <?= \app\Core\Csrf::input(); ?>
          <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
          <button class="btn primary" type="submit">Tomar caso</button>
        </form>
      </div>

    <?php elseif ($isMine): ?>
      <div class="card">
        <h3>Responsabilidad del caso</h3>
        <p style="margin:0 0 12px 0;color:#16a34a;font-weight:700;">Este caso está asignado a ti.</p>

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
          Este caso está asignado a
          <strong><?= e((string) ($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? 'otro asesor')) ?></strong>.
        </p>
      </div>
    <?php endif; ?>

    <div class="card">
      <h3>Actualizar estado</h3>
      <form method="post" action="/admin/pqrs/status">
        <?= \app\Core\Csrf::input(); ?>
        <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">

        <select name="status" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin:10px 0;">
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
      <form method="post" action="/admin/pqrs/note">
        <?= \app\Core\Csrf::input(); ?>
        <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
        <textarea name="message" style="width:100%;min-height:120px;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin:10px 0;"></textarea>
        <button class="btn primary" type="submit">Guardar nota</button>
      </form>
    </div>

    <div class="card">
      <h3>Tareas</h3>
      <form method="post" action="/admin/pqrs/task" style="margin-bottom:14px;">
        <?= \app\Core\Csrf::input(); ?>
        <input type="hidden" name="case_id" value="<?= (int) ($case->id ?? 0) ?>">
        <input type="text" name="title" placeholder="Título" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:8px;">
        <textarea name="description" placeholder="Descripción" style="width:100%;min-height:80px;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:8px;"></textarea>
        <input type="datetime-local" name="due_at" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:8px;">
        <button class="btn primary" type="submit">Crear tarea</button>
      </form>

      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($tasks as $task): ?>
          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
            <div style="font-weight:700;"><?= e((string) ($task->title ?? '')) ?></div>
            <div style="font-size:12px;color:#64748b;">
              Estado: <?= e((string) ($task->status ?? '')) ?>
              <?= !empty($task->due_at) ? ' · vence ' . e((string) $task->due_at) : '' ?>
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
          <div style="color:#64748b;">No hay tareas registradas.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>