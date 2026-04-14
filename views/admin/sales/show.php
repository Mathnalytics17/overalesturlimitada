<?php
use app\Core\Csrf;
use app\Models\SalesPayment;
use app\Services\Admin\Sales\SalesOpportunityService;
use app\Models\SalesQuote;
use app\Models\SalesOrder;

$item = $item ?? null;
$events = $events ?? [];
$returnTo = $returnTo ?? '/admin/sales';
$focusNote = !empty($focusNote);
$assignedAdvisor = $assignedAdvisor ?? null;
$currentAdminId = (int) ($currentAdminId ?? 0);
$assignedAdminId = (int) ($item->assigned_admin_user_id ?? 0);
$isAssigned = $assignedAdminId > 0;
$isMine = $isAssigned && $assignedAdminId === $currentAdminId;


if (!$item) {
    echo 'Oportunidad no encontrada';
    return;
}

$order = SalesOrder::findByOpportunityId((int) ($item->id ?? 0));
$quotes = SalesQuote::byOpportunity((int) ($item->id ?? 0));
$payments = SalesPayment::byOpportunity((int) ($item->id ?? 0));
$verifiedTotal = SalesPayment::sumVerifiedByOpportunity((int) ($item->id ?? 0));

$waService = new SalesOpportunityService();
$customerWhatsAppUrl = $item ? $waService->buildCustomerWhatsAppUrl($item) : '';

function sales_stage_label_detail(string $stage): string
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

function sales_stage_badge_detail(string $stage): string
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
?>

<style>
  .sales-show-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .sales-show-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 24px;
    align-items: start;
  }

  .left-stack,
  .right-stack {
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-width: 0;
  }

  .right-stack {
    position: sticky;
    top: 20px;
    align-self: start;
  }

  .panel-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    padding: 22px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
  }

  .panel-card h2,
  .panel-card h3,
  .panel-card h4 {
    margin: 0;
    color: #0f172a;
  }

  .section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    flex-wrap: wrap;
  }

  .section-head p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.5;
  }

  .hero-card {
    padding: 26px;
  }

  .hero-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  .hero-title {
    margin: 0;
    font-size: 32px;
    line-height: 1.05;
    font-weight: 900;
    color: #0f172a;
  }

  .hero-contact {
    margin-top: 10px;
    color: #64748b;
    line-height: 1.7;
    font-size: 14px;
  }

  .hero-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 900;
    border: 1px solid transparent;
    white-space: nowrap;
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

  .summary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }

  .summary-item {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 16px;
    background: #f8fafc;
    min-width: 0;
  }

  .summary-label {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: .04em;
    margin-bottom: 8px;
  }

  .summary-value {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.6;
    word-break: break-word;
  }

  .summary-value.muted {
    color: #475569;
    font-weight: 600;
  }

  .timeline {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .timeline-item {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 15px 16px;
    background: #f8fafc;
  }

  .timeline-title {
    margin: 0 0 6px;
    font-size: 15px;
    font-weight: 900;
    color: #0f172a;
  }

  .timeline-meta {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 8px;
    line-height: 1.5;
  }

  .timeline-body {
    color: #334155;
    line-height: 1.65;
    white-space: pre-wrap;
    font-size: 14px;
  }

  .empty-state {
    padding: 16px;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    color: #64748b;
    background: #fff;
    font-size: 14px;
  }

  .action-group {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .action-group + .action-group {
    padding-top: 18px;
    border-top: 1px solid #e5e7eb;
    margin-top: 18px;
  }

  .action-group-title {
    margin: 0;
    font-size: 15px;
    font-weight: 900;
    color: #0f172a;
  }

  .action-group-sub {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
  }

  .action-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .quick-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .quick-stat {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 14px;
    background: #f8fafc;
    min-width: 0;
  }

  .quick-stat .k {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 900;
    color: #64748b;
    margin-bottom: 6px;
    letter-spacing: .04em;
  }

  .quick-stat .v {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.4;
    word-break: break-word;
  }

  .form-block {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .form-block label {
    font-weight: 800;
    color: #0f172a;
    font-size: 13px;
  }

  .form-block input,
  .form-block select,
  .form-block textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 12px 14px;
    font: inherit;
    background: #fff;
    color: #0f172a;
    box-sizing: border-box;
  }

  .form-block textarea {
    min-height: 96px;
    resize: vertical;
  }

  .form-section-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
  }

  .btn-main,
  .btn-outline,
  .btn-green,
  .btn-red {
    border: none;
    border-radius: 14px;
    padding: 12px 18px;
    font: inherit;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    transition: .18s ease;
    box-sizing: border-box;
  }

  .btn-main:hover,
  .btn-outline:hover,
  .btn-green:hover,
  .btn-red:hover {
    transform: translateY(-1px);
  }

  .btn-main { background: #0ea5e9; color: #fff; }
  .btn-outline { background: #fff; color: #4c1d95; border: 1px solid #d1d5db; }
  .btn-green { background: #16a34a; color: #fff; }
  .btn-red { background: #b91c1c; color: #fff; }

  .btn-inline {
    width: auto;
    align-self: flex-start;
    min-width: 180px;
  }

  .mini-stat {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 14px 16px;
    background: #f8fafc;
  }

  .mini-stat .label {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 900;
    color: #64748b;
    margin-bottom: 8px;
  }

  .mini-stat .value {
    font-size: 24px;
    font-weight: 900;
    color: #0f172a;
    word-break: break-word;
  }

  .quote-card,
  .payment-card {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 16px;
    background: #fafafa;
  }

  .quote-actions,
  .payment-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
  }

  @media (max-width: 1180px) {
    .sales-show-layout {
      grid-template-columns: 1fr;
    }

    .right-stack {
      position: static;
    }
  }

  @media (max-width: 760px) {
    .summary-grid,
    .quick-stats,
    .quote-actions,
    .payment-actions,
    .form-section-grid {
      grid-template-columns: 1fr;
    }

    .hero-title {
      font-size: 26px;
    }

    .panel-card,
    .hero-card {
      padding: 18px;
    }

    .btn-inline {
      width: 100%;
      align-self: stretch;
    }
  }
</style>

<section class="content sales-show-page">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
    <a href="<?= htmlspecialchars($returnTo) ?>" class="btn-outline" style="text-decoration:none;width:auto;">
      ← Volver
    </a>

    <?php if ($customerWhatsAppUrl !== ''): ?>
      <a
        href="<?= htmlspecialchars($customerWhatsAppUrl) ?>"
        target="_blank"
        rel="noopener noreferrer"
        class="btn-outline"
        style="text-decoration:none;width:auto;"
      >
        Abrir WhatsApp
      </a>
    <?php endif; ?>
  </div>

  <div class="sales-show-layout">

    <div class="left-stack">
      <div class="panel-card hero-card">
        <div class="hero-top">
          <div>
            <h2 class="hero-title"><?= htmlspecialchars($item->customer_name ?? '') ?></h2>
            <div class="hero-contact">
              <?= htmlspecialchars($item->customer_phone ?? 'Sin teléfono') ?><br>
              <?= htmlspecialchars($item->customer_email ?? 'Sin correo') ?>
            </div>
          </div>

          <div class="hero-badges">
            <span class="<?= sales_stage_badge_detail((string) ($item->sales_stage ?? '')) ?>">
              <?= htmlspecialchars(sales_stage_label_detail((string) ($item->sales_stage ?? ''))) ?>
            </span>
            <span class="badge gray">
              <?= htmlspecialchars($item->sales_temperature ?? 'warm') ?>
            </span>
          </div>
        </div>

        <div class="summary-grid">
          <div class="summary-item">
            <div class="summary-label">Origen</div>
            <div class="summary-value">
              <?= htmlspecialchars($item->source_origin ?? 'Sin origen') ?><br>
              <span class="summary-value muted">Canal: <?= htmlspecialchars($item->source_channel ?? 'Sin canal') ?></span>
            </div>
          </div>

          <div class="summary-item">
            <div class="summary-label">Interés</div>
            <div class="summary-value">
              <?= htmlspecialchars($item->interest_type ?? 'Sin interés') ?><br>
              <span class="summary-value muted">
                <?php if (!empty($item->package_slug)): ?>
                  Paquete: <?= htmlspecialchars($item->package_slug) ?>
                <?php elseif (!empty($item->extra_service_slug)): ?>
                  Servicio: <?= htmlspecialchars($item->extra_service_slug) ?>
                <?php else: ?>
                  Sin referencia
                <?php endif; ?>
              </span>
            </div>
          </div>

        <div class="summary-item">
  <div class="summary-label">Asesor asignado</div>
  <div class="summary-value">
    <?php if ($assignedAdvisor): ?>
      <?= htmlspecialchars((string) ($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? ('Asesor #' . $assignedAdminId))) ?>
    <?php elseif ($isAssigned): ?>
      Asesor #<?= (int) $assignedAdminId ?>
    <?php else: ?>
      Sin asignar
    <?php endif; ?>
  </div>
</div>

          <div class="summary-item">
            <div class="summary-label">Seguimiento</div>
            <div class="summary-value">
              Próximo: <?= htmlspecialchars($item->next_follow_up_at ?? 'Sin fecha') ?><br>
              <span class="summary-value muted">Último contacto: <?= htmlspecialchars($item->last_contact_at ?? 'Sin registro') ?></span>
            </div>
          </div>

          <div class="summary-item">
            <div class="summary-label">Cotización actual</div>
            <div class="summary-value">
              <?= !empty($item->quoted_amount)
                ? number_format((float) $item->quoted_amount, 0, ',', '.') . ' ' . htmlspecialchars($item->quoted_currency ?? 'COP')
                : 'Sin cotización' ?>
            </div>
          </div>

          <div class="summary-item">
            <div class="summary-label">Resumen comercial</div>
            <div class="summary-value muted">
              <?= nl2br(htmlspecialchars($item->notes_summary ?? 'Sin resumen')) ?>
            </div>
          </div>

          <?php if ((string) ($item->sales_stage ?? '') === 'lost'): ?>
            <div class="summary-item" style="grid-column:1/-1;">
              <div class="summary-label">Motivo de pérdida</div>
              <div class="summary-value">
                <?= htmlspecialchars($item->lost_reason ?? 'Sin motivo') ?>
                <?php if (!empty($item->lost_reason_detail)): ?>
                  <div class="summary-value muted" style="margin-top:8px;">
                    <?= nl2br(htmlspecialchars($item->lost_reason_detail)) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel-card">
        <div class="section-head">
          <div>
            <h3>Timeline comercial</h3>
            <p>Historial de cambios, seguimiento y eventos de la oportunidad.</p>
          </div>
        </div>

        <?php if (empty($events)): ?>
          <div class="empty-state">No hay eventos registrados todavía.</div>
        <?php else: ?>
          <div class="timeline">
            <?php foreach ($events as $event): ?>
              <div class="timeline-item">
                <div class="timeline-title"><?= htmlspecialchars($event->title ?? '') ?></div>
                <div class="timeline-meta">
                  <?= htmlspecialchars($event->event_type ?? '') ?> · <?= htmlspecialchars($event->created_at ?? '') ?>
                  <?php if (!empty($event->admin_user_id)): ?>
                    · Admin #<?= (int) $event->admin_user_id ?>
                  <?php endif; ?>
                </div>
                <?php if (!empty($event->message)): ?>
                  <div class="timeline-body"><?= nl2br(htmlspecialchars($event->message)) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="panel-card">
        <div class="section-head">
          <div>
            <h3>Cotizaciones registradas</h3>
            <p>Historial de propuestas enviadas al cliente.</p>
          </div>
        </div>

        <?php if (empty($quotes)): ?>
          <div class="empty-state">No hay cotizaciones registradas todavía.</div>
        <?php else: ?>
          <div class="timeline">
            <?php foreach ($quotes as $quote): ?>
              <div class="quote-card">
                <div class="timeline-title"><?= htmlspecialchars($quote->title ?? '') ?></div>
                <div class="timeline-meta">
                  <?= number_format((float) ($quote->amount ?? 0), 0, ',', '.') ?>
                  <?= htmlspecialchars($quote->currency ?? 'COP') ?> ·
                  Estado: <?= htmlspecialchars($quote->status ?? 'draft') ?><br>
                  <?php if (!empty($quote->valid_until)): ?>
                    Vigencia: <?= htmlspecialchars($quote->valid_until) ?><br>
                  <?php endif; ?>
                  <?php if (!empty($quote->sent_at)): ?>
                    Enviada: <?= htmlspecialchars($quote->sent_at) ?>
                  <?php endif; ?>
                </div>

                <?php if (!empty($quote->summary)): ?>
                  <div class="timeline-body"><?= nl2br(htmlspecialchars($quote->summary)) ?></div>
                <?php endif; ?>

                <?php if (in_array((string) ($quote->status ?? ''), ['draft', 'sent'], true)): ?>
                  <div class="quote-actions">
                    <?php if ((string) ($quote->status ?? '') === 'draft'): ?>
                      <form method="POST" action="/admin/sales/quote/mark-sent">
                        <?= Csrf::input(); ?>
                        <input type="hidden" name="quote_id" value="<?= (int) $quote->id ?>">
                        <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
                        <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">
                        <button type="submit" class="btn-outline">Marcar enviada</button>
                      </form>
                    <?php endif; ?>

                    <form method="POST" action="/admin/sales/quote/mark-accepted">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="quote_id" value="<?= (int) $quote->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">
                      <button type="submit" class="btn-green">Aceptar</button>
                    </form>

                    <form method="POST" action="/admin/sales/quote/mark-rejected">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="quote_id" value="<?= (int) $quote->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">
                      <button type="submit" class="btn-red">Rechazar</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="panel-card">
        <div class="section-head">
          <div>
            <h3>Nueva cotización</h3>
            <p>Crea una propuesta comercial rápida para el cliente.</p>
          </div>
        </div>

        <form method="POST" action="/admin/sales/quote/create" class="form-block">
          <?= Csrf::input(); ?>
          <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
          <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">

          <div class="form-section-grid">
            <div class="form-block">
              <label for="quote_title">Título</label>
              <input type="text" id="quote_title" name="title" placeholder="Ej: Propuesta Cartagena abril">
            </div>

            <div class="form-block">
              <label for="quote_amount">Monto</label>
              <input type="number" step="0.01" id="quote_amount" name="amount" placeholder="Ej: 1850000">
            </div>

            <div class="form-block">
              <label for="quote_currency">Moneda</label>
              <input type="text" id="quote_currency" name="currency" value="COP">
            </div>

            <div class="form-block">
              <label for="quote_valid_until">Vigencia</label>
              <input type="date" id="quote_valid_until" name="valid_until">
            </div>
          </div>

          <div class="form-block">
            <label for="quote_summary">Resumen</label>
            <textarea id="quote_summary" name="summary" placeholder="Resumen de lo cotizado, condiciones y observaciones."></textarea>
          </div>

          <button type="submit" class="btn-main btn-inline">Crear cotización</button>
        </form>
      </div>

      <div class="panel-card">
        <div class="section-head">
          <div>
            <h3>Pagos reportados</h3>
            <p>Registros manuales de abonos, pagos y validaciones.</p>
          </div>
          <div class="mini-stat">
            <div class="label">Total validado</div>
            <div class="value"><?= number_format((float) $verifiedTotal, 0, ',', '.') ?> COP</div>
          </div>
        </div>

        <?php if (empty($payments)): ?>
          <div class="empty-state">No hay pagos registrados todavía.</div>
        <?php else: ?>
          <div class="timeline">
            <?php foreach ($payments as $payment): ?>
              <div class="payment-card">
                <div class="timeline-title">
                  <?= htmlspecialchars($payment->payment_kind ?? 'payment') ?> ·
                  <?= number_format((float) ($payment->amount ?? 0), 0, ',', '.') ?>
                  <?= htmlspecialchars($payment->currency ?? 'COP') ?>
                </div>

                <div class="timeline-meta">
                  Estado: <?= htmlspecialchars($payment->status ?? '') ?> ·
                  Método: <?= htmlspecialchars($payment->payment_method ?? '') ?><br>
                  Referencia: <?= htmlspecialchars($payment->payment_reference ?? 'Sin referencia') ?><br>
                  Reportado: <?= htmlspecialchars($payment->reported_at ?? '') ?>
                </div>

                <?php if (!empty($payment->notes)): ?>
                  <div class="timeline-body"><?= nl2br(htmlspecialchars($payment->notes)) ?></div>
                <?php endif; ?>

                <?php if ((string) ($payment->status ?? '') === 'reported'): ?>
                  <div class="payment-actions">
                    <form method="POST" action="/admin/sales/payment/verify">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="payment_id" value="<?= (int) $payment->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">
                      <button type="submit" class="btn-green">Validar pago</button>
                    </form>

                    <form method="POST" action="/admin/sales/payment/reject" class="form-block">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="payment_id" value="<?= (int) $payment->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">
                      <input type="text" name="reason" placeholder="Motivo del rechazo">
                      <button type="submit" class="btn-red">Rechazar pago</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="panel-card">
        <div class="section-head">
          <div>
            <h3>Registrar pago</h3>
            <p>Reporta manualmente el pago informado por el cliente.</p>
          </div>
        </div>

        <form method="POST" action="/admin/sales/payment/report" class="form-block">
          <?= Csrf::input(); ?>
          <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
          <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">

          <div class="form-section-grid">
            <div class="form-block">
              <label for="payment_kind">Tipo de pago</label>
              <select id="payment_kind" name="payment_kind">
                <option value="deposit">Abono inicial</option>
                <option value="partial">Pago parcial</option>
                <option value="full">Pago total</option>
                <option value="balance">Saldo</option>
                <option value="refund">Devolución</option>
              </select>
            </div>

            <div class="form-block">
              <label for="amount">Monto</label>
              <input type="number" step="0.01" id="amount" name="amount" placeholder="Ej: 350000">
            </div>

            <div class="form-block">
              <label for="currency">Moneda</label>
              <input type="text" id="currency" name="currency" value="COP">
            </div>

            <div class="form-block">
              <label for="payment_method">Método</label>
              <select id="payment_method" name="payment_method">
                <option value="transfer">Transferencia</option>
                <option value="cash">Efectivo</option>
                <option value="consignment">Consignación</option>
                <option value="card">Tarjeta</option>
                <option value="external_link">Link externo</option>
                <option value="other">Otro</option>
              </select>
            </div>
          </div>

          <div class="form-block">
            <label for="payment_reference">Referencia</label>
            <input type="text" id="payment_reference" name="payment_reference" placeholder="Ej: COMPROBANTE 92831">
          </div>

          <div class="form-block">
            <label for="payment_notes">Notas</label>
            <textarea id="payment_notes" name="notes" placeholder="Observaciones del pago reportado."></textarea>
          </div>

          <button type="submit" class="btn-main btn-inline">Registrar pago</button>
        </form>
      </div>
    </div>

    <div class="right-stack">
      <div class="panel-card">
        <div class="section-head">
          <div>
            <h3>Resumen y acciones</h3>
            <p>Acciones rápidas sobre la oportunidad.</p>
          </div>
        </div>

        <div class="quick-stats" style="margin-bottom:18px;">
          <div class="quick-stat">
            <div class="k">Etapa</div>
            <div class="v"><?= htmlspecialchars(sales_stage_label_detail((string) ($item->sales_stage ?? ''))) ?></div>
          </div>

          <div class="quick-stat">
            <div class="k">Temperatura</div>
            <div class="v"><?= htmlspecialchars($item->sales_temperature ?? 'warm') ?></div>
          </div>

          <div class="quick-stat">
            <div class="k">Cotización</div>
            <div class="v">
              <?= !empty($item->quoted_amount)
                ? number_format((float) $item->quoted_amount, 0, ',', '.') . ' ' . htmlspecialchars($item->quoted_currency ?? 'COP')
                : 'Sin cotización' ?>
            </div>
          </div>

          <div class="quick-stat">
            <div class="k">Pagos validados</div>
            <div class="v"><?= number_format((float) $verifiedTotal, 0, ',', '.') ?> COP</div>
          </div>
        </div>

        <div class="action-group">
          <div class="action-grid">
            <a href="/admin/sales/edit?id=<?= (int) $item->id ?>&return_to=<?= urlencode($returnTo) ?>" class="btn-main" style="text-decoration:none;">
              Editar oportunidad
            </a>

            <?php if (!$isAssigned): ?>
  <form method="POST" action="/admin/sales/assign" class="form-block">
    <?= Csrf::input(); ?>
    <input type="hidden" name="id" value="<?= (int) $item->id ?>">
    <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

    <button type="submit" class="btn-outline" name="action" value="stay">Asignarme y gestionar</button>
    <button type="submit" class="btn-outline" name="action" value="back">Asignarme y volver</button>
  </form>
<?php elseif ($isMine): ?>
  <div class="quick-stat" style="grid-column:1/-1;">
    <div class="k">Responsabilidad</div>
    <div class="v">Esta oportunidad está asignada a ti</div>
  </div>
<?php else: ?>
  <div class="quick-stat" style="grid-column:1/-1;">
    <div class="k">Responsabilidad</div>
    <div class="v">
      Asignada a
      <?= htmlspecialchars((string) ($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? ('Asesor #' . $assignedAdminId))) ?>
    </div>
  </div>
<?php endif; ?>

            <?php if ($order): ?>
              <a href="/admin/sales-orders/show?id=<?= (int) $order->id ?>" class="btn-outline" style="text-decoration:none;">
                Ver venta / reserva
              </a>
            <?php else: ?>
              <form method="POST" action="/admin/sales-orders/create-from-opportunity" class="form-block">
                <?= Csrf::input(); ?>
                <input type="hidden" name="sales_opportunity_id" value="<?= (int) $item->id ?>">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id)) ?>">
                <button type="submit" class="btn-green">Crear venta / reserva</button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <div class="action-group">
          <div>
            <h4 class="action-group-title">Mover etapa</h4>
            <p class="action-group-sub">Cambio rápido del estado comercial.</p>
          </div>

          <form method="POST" action="/admin/sales/change-stage" class="form-block">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" value="<?= (int) $item->id ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

            <label for="sales_stage">Cambiar etapa</label>
            <select id="sales_stage" name="sales_stage">
              <option value="new" <?= (($item->sales_stage ?? '') === 'new') ? 'selected' : '' ?>>Nuevo</option>
              <option value="contacted" <?= (($item->sales_stage ?? '') === 'contacted') ? 'selected' : '' ?>>Contactado</option>
              <option value="profiled" <?= (($item->sales_stage ?? '') === 'profiled') ? 'selected' : '' ?>>Perfilado</option>
              <option value="quoted" <?= (($item->sales_stage ?? '') === 'quoted') ? 'selected' : '' ?>>Cotizado</option>
              <option value="follow_up" <?= (($item->sales_stage ?? '') === 'follow_up') ? 'selected' : '' ?>>Seguimiento</option>
              <option value="pending_payment" <?= (($item->sales_stage ?? '') === 'pending_payment') ? 'selected' : '' ?>>Pago pendiente</option>
              <option value="payment_reported" <?= (($item->sales_stage ?? '') === 'payment_reported') ? 'selected' : '' ?>>Pago reportado</option>
              <option value="payment_validated" <?= (($item->sales_stage ?? '') === 'payment_validated') ? 'selected' : '' ?>>Pago validado</option>
              <option value="won" <?= (($item->sales_stage ?? '') === 'won') ? 'selected' : '' ?>>Ganada</option>
              <option value="lost" <?= (($item->sales_stage ?? '') === 'lost') ? 'selected' : '' ?>>Perdida</option>
              <option value="cancelled" <?= (($item->sales_stage ?? '') === 'cancelled') ? 'selected' : '' ?>>Cancelada</option>
            </select>

            <label for="stage_message">Nota del cambio</label>
            <textarea id="stage_message" name="message" placeholder="Nota breve del cambio"></textarea>

            <button type="submit" class="btn-main">Guardar cambio</button>
          </form>
        </div>

        <div class="action-group">
          <div>
            <h4 class="action-group-title">Seguimiento rápido</h4>
            <p class="action-group-sub">Agenda el próximo paso.</p>
          </div>

          <form method="POST" action="/admin/sales/schedule-followup" class="form-block">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" value="<?= (int) $item->id ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

            <label for="next_follow_up_at">Programar seguimiento</label>
            <input type="datetime-local" id="next_follow_up_at" name="next_follow_up_at">

            <label for="followup_message">Nota</label>
            <textarea id="followup_message" name="message" placeholder="Qué vas a hacer o recordar"></textarea>

            <button type="submit" class="btn-outline">Programar seguimiento</button>
          </form>

          <form method="POST" action="/admin/sales/add-note" class="form-block">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" value="<?= (int) $item->id ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

            <label for="note_message">Agregar nota</label>
            <textarea id="note_message" name="message" placeholder="Escribe una nota comercial o de seguimiento."></textarea>

            <button type="submit" class="btn-outline">Agregar nota</button>
          </form>
        </div>

        <div class="action-group">
          <div>
            <h4 class="action-group-title">Cierre</h4>
            <p class="action-group-sub">Marca la oportunidad como ganada o perdida.</p>
          </div>

          <form method="POST" action="/admin/sales/mark-won" class="form-block">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" value="<?= (int) $item->id ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">
            <button type="submit" class="btn-green">Marcar como ganada</button>
          </form>

          <form method="POST" action="/admin/sales/mark-lost" class="form-block">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" value="<?= (int) $item->id ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">

            <label for="lost_reason_code">Motivo de pérdida</label>
            <select id="lost_reason_code" name="lost_reason_code">
              <option value="">Selecciona un motivo</option>
              <option value="no_response">No respondió</option>
              <option value="no_budget">Sin presupuesto</option>
              <option value="better_price">Encontró mejor precio</option>
              <option value="postponed_trip">Aplazó el viaje</option>
              <option value="no_availability">Sin disponibilidad</option>
              <option value="documents_issue">Problema de documentación</option>
              <option value="lost_interest">Perdió interés</option>
              <option value="bought_elsewhere">Compró en otra parte</option>
              <option value="not_qualified">No califica</option>
              <option value="other">Otro</option>
            </select>

            <label for="lost_reason_detail">Detalle adicional</label>
            <textarea id="lost_reason_detail" name="lost_reason_detail" placeholder="Opcional: agrega contexto adicional."></textarea>

            <button type="submit" class="btn-red">Marcar como perdida</button>
          </form>
        </div>
      </div>
    </div>

  </div>
</section>

<?php if ($focusNote): ?>
<script>
  window.addEventListener('load', function () {
    const noteField = document.getElementById('note_message');
    if (!noteField) return;

    noteField.focus();

    try {
      noteField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } catch (e) {}
  });
</script>
<?php endif; ?>