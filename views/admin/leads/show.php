<?php
$page_title = 'Caso CRM';
$page_subtitle = 'Detalle, trazabilidad y tareas';
$active = 'leads';

$lead = $lead ?? null;
$interactions = $interactions ?? [];
$tasks = $tasks ?? [];
$assignedAdvisor = $assignedAdvisor ?? null;
$currentAdminId = (int) ($currentAdminId ?? 0);
$returnTo = $returnTo ?? '/admin/leads';
$focusNote = !empty($focusNote);

$metadata = $lead?->metadata_json ?? [];
$assignedAdminId = (int) ($lead->assigned_admin_user_id ?? 0);
$isAssigned = $assignedAdminId > 0;
$isMine = $isAssigned && $assignedAdminId === $currentAdminId;

$sourceType = (string) ($lead->source_type ?? '');

$isTickets = $sourceType === 'tickets';
$isPackage = $sourceType === 'package';
$isContact = $sourceType === 'contact';

$country = trim((string) ($metadata['country'] ?? ''));
$city = trim((string) ($metadata['city'] ?? ''));
$departureDate = trim((string) ($metadata['departureDate'] ?? ''));
$returnDate = trim((string) ($metadata['returnDate'] ?? ''));
$oneWay = !empty($metadata['oneWay']);

$travelSummary = [];
if ($country !== '') $travelSummary[] = $country;
if ($city !== '') $travelSummary[] = $city;
$travelSummaryText = implode(' · ', $travelSummary);

$canConvert = in_array((string) ($lead->source_type ?? ''), ['package', 'tickets', 'extra_service', 'contact'], true);
?>

<div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
  <a href="<?= e($returnTo) ?>" class="btn" style="text-decoration:none;">
    ← Volver a la lista
  </a>

  <?php if (!empty($advisorWhatsappUrl ?? null)): ?>
    <a class="btn primary" target="_blank" rel="noopener" href="<?= e((string) $advisorWhatsappUrl) ?>">
      Contactar por WhatsApp
    </a>
  <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:18px;align-items:start;">
  <div class="card">
    <div class="card-head">
      <div>
        <h2 style="margin-bottom:6px;">Caso #<?= (int) ($lead->id ?? 0) ?></h2>
        <div class="card-sub"><?= e((string) ($lead->source_type ?? '')) ?> · <?= e((string) ($lead->status ?? '')) ?></div>
      </div>
    </div>

    <div class="sep"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div><strong>Cliente:</strong><br><?= e((string) ($lead->full_name ?? '')) ?></div>
      <div><strong>Correo:</strong><br><?= e((string) ($lead->email ?? '')) ?></div>
      <div><strong>Teléfono:</strong><br><?= e((string) ($lead->phone ?? '')) ?></div>
      <div>
        <strong>Responsable:</strong><br>
        <?php if ($assignedAdvisor): ?>
          <?= e((string) ($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? 'Asesor')) ?>
        <?php else: ?>
          Sin asignar
        <?php endif; ?>
      </div>
      <div><strong>Asunto:</strong><br><?= e((string) ($lead->subject ?? '')) ?></div>
      <div><strong>Creado:</strong><br><?= e((string) ($lead->created_at ?? '')) ?></div>
    </div>

    <?php if ($isTickets): ?>
      <div style="margin-top:18px;">
        <strong>Detalle de la solicitud de tiquetes</strong>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px;">
          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;">
            <strong>Destino</strong><br>
            <?= $travelSummaryText !== '' ? e($travelSummaryText) : 'No especificado' ?>
          </div>

          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;">
            <strong>Trayecto</strong><br>
            <?= $oneWay ? 'Solo ida' : 'Ida y regreso' ?>
          </div>

          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;">
            <strong>Fecha de ida</strong><br>
            <?= $departureDate !== '' ? e($departureDate) : 'No especificada' ?>
          </div>

          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;">
            <strong>Fecha de regreso</strong><br>
            <?= (!$oneWay && $returnDate !== '') ? e($returnDate) : 'No aplica' ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($isPackage): ?>
      <div style="margin-top:18px;">
        <strong>Detalle de paquete</strong>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;margin-top:10px;">
          <div><strong>Paquete consultado:</strong> <?= e((string) ($lead->package_slug ?? 'No especificado')) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($lead->message)): ?>
      <div style="margin-top:16px;">
        <strong><?= $isTickets ? 'Resumen de la solicitud' : 'Mensaje original' ?></strong>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;margin-top:8px;">
          <?= nl2br(e((string) $lead->message)) ?>
        </div>
      </div>
    <?php endif; ?>

    <?php
      $showMetadata = [];
      foreach ((array) $metadata as $key => $value) {
          if (in_array($key, ['country', 'city', 'departureDate', 'returnDate', 'oneWay'], true)) {
              continue;
          }
          if ($value === null || $value === '' || $value === false) {
              continue;
          }
          $showMetadata[$key] = $value;
      }
    ?>

    <?php if (!empty($showMetadata)): ?>
      <div style="margin-top:16px;">
        <strong>Metadatos adicionales</strong>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;margin-top:8px;">
          <?php foreach ($showMetadata as $key => $value): ?>
            <div style="margin-bottom:6px;">
              <strong><?= e((string) $key) ?>:</strong>
              <?= e(is_bool($value) ? ($value ? 'Sí' : 'No') : (string) $value) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div style="margin-top:18px;">
      <strong>Trazabilidad</strong>
      <div style="display:flex;flex-direction:column;gap:10px;margin-top:12px;">
        <?php foreach ($interactions as $item): ?>
          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
            <div style="font-size:12px;color:#64748b;">
              <?= e((string) $item->created_at) ?> · <?= e((string) $item->event_type) ?> · <?= e((string) $item->channel) ?>
            </div>
            <div style="margin-top:6px;"><?= nl2br(e((string) $item->message)) ?></div>
          </div>
        <?php endforeach; ?>

        <?php if ($interactions === []): ?>
          <div>No hay movimientos registrados todavía.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:18px;">
    <?php if (!$isAssigned): ?>
      <div class="card">
        <h3>Tomar caso</h3>
        <p style="margin:0 0 12px 0;color:#64748b;">
          Este caso aún no tiene responsable. Puedes tomarlo para gestionarlo.
        </p>

        <form method="post" action="/admin/leads/take" style="display:flex;gap:10px;flex-wrap:wrap;">
          <?= \app\Core\Csrf::input(); ?>
          <input type="hidden" name="lead_id" value="<?= (int) ($lead->id ?? 0) ?>">
          <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">

          <button class="btn primary" type="submit" name="action" value="stay">
            Tomar y gestionar
          </button>

          <button class="btn" type="submit" name="action" value="back">
            Tomar y volver
          </button>
        </form>
      </div>

    <?php elseif ($isMine): ?>
      <div class="card">
        <h3>Responsabilidad del caso</h3>
        <p style="margin:0 0 12px 0;color:#16a34a;font-weight:700;">
          Este caso está asignado a ti.
        </p>

        <?php if (!in_array((string) ($lead->status ?? ''), ['closed', 'lost'], true)): ?>
          <form method="post" action="/admin/leads/release" style="display:flex;gap:10px;flex-wrap:wrap;">
            <?= \app\Core\Csrf::input(); ?>
            <input type="hidden" name="lead_id" value="<?= (int) ($lead->id ?? 0) ?>">
            <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">

            <button class="btn" type="submit" name="action" value="back">
              Soltar y volver
            </button>

            <button class="btn" type="submit" name="action" value="stay">
              Soltar y quedarme
            </button>
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
      <form method="post" action="/admin/leads/status">
        <?= \app\Core\Csrf::input(); ?>
        <input type="hidden" name="lead_id" value="<?= (int) ($lead->id ?? 0) ?>">
        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">

        <select name="status" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin:10px 0;">
          <?php foreach (['new', 'in_progress', 'waiting_customer', 'closed', 'lost'] as $status): ?>
            <option value="<?= e($status) ?>" <?= (($lead->status ?? '') === $status) ? 'selected' : '' ?>>
              <?= e($status) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button class="btn primary" type="submit">Guardar</button>
      </form>
    </div>

    <div class="card">
      <h3>Agregar nota</h3>
      <form method="post" action="/admin/leads/note">
        <?= \app\Core\Csrf::input(); ?>
        <input type="hidden" name="lead_id" value="<?= (int) ($lead->id ?? 0) ?>">
        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">

        <textarea
          id="lead-note-message"
          name="message"
          style="width:100%;min-height:120px;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin:10px 0;"
          placeholder="Escribe una nota interna..."
        ></textarea>

        <button class="btn primary" type="submit">Guardar nota</button>
      </form>
    </div>

    <div class="card">
      <h3>Tareas</h3>

      <form method="post" action="/admin/leads/task" style="margin-bottom:14px;">
        <?= \app\Core\Csrf::input(); ?>
        <input type="hidden" name="lead_id" value="<?= (int) ($lead->id ?? 0) ?>">
        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">

        <input
          type="text"
          name="title"
          placeholder="Título"
          style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:8px;"
        >

        <textarea
          name="description"
          placeholder="Descripción"
          style="width:100%;min-height:80px;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:8px;"
        ></textarea>

        <input
          type="datetime-local"
          name="due_at"
          style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;margin-bottom:8px;"
        >

        <button class="btn primary" type="submit">Crear tarea</button>
      </form>

      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($tasks as $task): ?>
          <div style="padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
            <div style="font-weight:700;"><?= e((string) $task->title) ?></div>

            <div style="font-size:12px;color:#64748b;">
              Estado: <?= e((string) $task->status) ?>
              <?= !empty($task->due_at) ? ' · vence ' . e((string) $task->due_at) : '' ?>
            </div>

            <?php if (!empty($task->description)): ?>
              <div style="margin-top:6px;"><?= nl2br(e((string) $task->description)) ?></div>
            <?php endif; ?>

            <?php if (($task->status ?? '') !== 'done'): ?>
              <form method="post" action="/admin/leads/task/complete" style="margin-top:8px;">
                <?= \app\Core\Csrf::input(); ?>
                <input type="hidden" name="task_id" value="<?= (int) $task->id ?>">
                <input type="hidden" name="lead_id" value="<?= (int) ($lead->id ?? 0) ?>">
                <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">

                <button class="btn" type="submit">Marcar completada</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php if ($tasks === []): ?>
          <div>No hay tareas registradas.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <h3>Oportunidad comercial</h3>

      <?php if (!empty($lead->sales_opportunity)): ?>
        <a href="/admin/sales/show?id=<?= (int) $lead->sales_opportunity->id ?>" class="btn" style="text-decoration:none;">
          Ver oportunidad
        </a>
      <?php elseif ($canConvert): ?>
        <form method="POST" action="/admin/sales/create-from-lead" style="display:inline;">
          <?= \app\Core\Csrf::input(); ?>
          <input type="hidden" name="lead_id" value="<?= (int) $lead->id ?>">
          <button type="submit" class="btn primary">Convertir a oportunidad</button>
        </form>
      <?php else: ?>
        <span style="color:#64748b;font-weight:700;">No comercial</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($focusNote): ?>
<script>
  window.addEventListener('load', function () {
    const noteField = document.getElementById('lead-note-message');
    if (!noteField) return;

    noteField.focus();

    try {
      noteField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } catch (e) {}
  });
</script>
<?php endif; ?>