<?php
use app\Core\Csrf;
use app\Models\SalesPayment;
use app\Services\Admin\Sales\SalesOpportunityService;
use app\Models\SalesQuote;
use app\Models\SalesOrder;

$item = $item ?? null;
$events = $events ?? [];
$returnTo = $returnTo ?? '/admin/sales';
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
$currentQuote = SalesQuote::acceptedByOpportunity((int) ($item->id ?? 0));
$currentQuote = $currentQuote ?: SalesQuote::currentForOpportunity((int) ($item->id ?? 0));
$acceptedQuote = SalesQuote::acceptedByOpportunity((int) ($item->id ?? 0));
$payments = SalesPayment::byOpportunity((int) ($item->id ?? 0));
$verifiedTotal = SalesPayment::sumVerifiedByOpportunity((int) ($item->id ?? 0));
$committedTotal = method_exists(SalesPayment::class, 'sumCommittedByOpportunity')
    ? SalesPayment::sumCommittedByOpportunity((int) ($item->id ?? 0))
    : $verifiedTotal;
$pendingReportedTotal = method_exists(SalesPayment::class, 'sumPendingReportedByOpportunity')
    ? SalesPayment::sumPendingReportedByOpportunity((int) ($item->id ?? 0))
    : 0;
$quoteTotal = $acceptedQuote ? (float) ($acceptedQuote->amount ?? 0) : ($currentQuote ? (float) ($currentQuote->amount ?? 0) : (float) ($item->quoted_amount ?? 0));
$quoteCurrency = $acceptedQuote ? (string) ($acceptedQuote->currency ?? 'COP') : ($currentQuote ? (string) ($currentQuote->currency ?? 'COP') : (string) ($item->quoted_currency ?? 'COP'));
$quoteCurrency = $quoteCurrency !== '' ? $quoteCurrency : 'COP';
$currencies = $currencies ?? [];
if (empty($currencies)) {
    $fallbackCurrencies = [
        ['COP', 'Peso colombiano', '$'],
        ['USD', 'Dólar estadounidense', 'US$'],
        ['EUR', 'Euro', '€'],
    ];
    $currencies = array_map(function ($row) {
        return (object) [
            'code' => $row[0],
            'name' => $row[1],
            'symbol' => $row[2],
            'is_active' => 1,
        ];
    }, $fallbackCurrencies);
}
$selectedQuoteCurrency = strtoupper((string)($quoteCurrency ?: 'COP'));
$balanceAmount = max(0, $quoteTotal - (float) $verifiedTotal);
$availableToReport = max(0, $quoteTotal - (float) $committedTotal);
$hasAcceptedQuote = $acceptedQuote && $quoteTotal > 0;
$hasOrder = (bool) $order;
$hasPendingPayments = $pendingReportedTotal > 0;
$canCreateOrder = $hasAcceptedQuote && !$hasOrder;
$canReportPayment = $hasOrder && $hasAcceptedQuote && $availableToReport > 0;
$canCompleteOrder = $hasOrder && $balanceAmount <= 0.01;

$waService = new SalesOpportunityService();
$customerWhatsAppUrl = $item ? $waService->buildCustomerWhatsAppUrl($item) : '';
$currentUrl = $_SERVER['REQUEST_URI'] ?? ('/admin/sales/show?id=' . (int) $item->id);

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
        'won' => 'Venta abierta',
        'lost' => 'Perdida',
        'cancelled' => 'Cancelada',
        default => ucfirst($stage),
    };
}

function sales_badge_class(string $status): string
{
    return match ($status) {
        'new', 'draft', 'open' => 'badge blue',
        'contacted', 'sent', 'reported' => 'badge cyan',
        'profiled', 'quoted' => 'badge indigo',
        'follow_up', 'pending_payment', 'pending_documents', 'pending_booking', 'paid_partial' => 'badge amber',
        'payment_reported' => 'badge yellow',
        'payment_validated', 'won', 'accepted', 'verified', 'paid_full', 'booked', 'delivered', 'completed' => 'badge green',
        'lost', 'cancelled', 'rejected' => 'badge red',
        default => 'badge gray',
    };
}

function quote_status_label(string $status): string
{
    return match ($status) {
        'draft' => 'Borrador',
        'sent' => 'Enviada',
        'accepted' => 'Aceptada por cliente',
        'rejected' => 'Rechazada',
        default => ucfirst($status),
    };
}

function payment_kind_label(string $kind): string
{
    return match ($kind) {
        'deposit' => 'Abono inicial',
        'partial' => 'Pago parcial',
        'full' => 'Pago total',
        'balance' => 'Saldo',
        'refund' => 'Devolución / ajuste',
        default => ucfirst($kind),
    };
}

function payment_status_label(string $status): string
{
    return match ($status) {
        'reported' => 'Pendiente de validar',
        'verified' => 'Validado',
        'rejected' => 'Rechazado',
        default => ucfirst($status),
    };
}

function operational_label(?string $status): string
{
    return match ((string) $status) {
        'pending_documents' => 'Pendiente documentos',
        'pending_booking' => 'Pendiente reserva',
        'booked' => 'Reservado',
        'delivered' => 'Entregado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
        default => 'Sin iniciar',
    };
}

$nextTitle = 'Crear cotización';
$nextBody = 'Primero arma una propuesta con valor para enviarla al cliente.';
$nextHref = '#quote-form';
$nextLabel = 'Ir a cotización';

if ($currentQuote && (string)($currentQuote->status ?? '') === 'draft') {
    $nextTitle = 'Enviar cotización';
    $nextBody = 'La cotización está en borrador. Márcala como enviada cuando ya la compartas con el cliente.';
    $nextHref = '#quotes-section';
    $nextLabel = 'Ver cotización';
} elseif ($currentQuote && (string)($currentQuote->status ?? '') === 'sent') {
    $nextTitle = 'Esperar respuesta del cliente';
    $nextBody = 'Cuando el cliente confirme que acepta la propuesta, márcala como aceptada.';
    $nextHref = '#quotes-section';
    $nextLabel = 'Aceptar o rechazar';
} elseif ($canCreateOrder) {
    $nextTitle = 'Confirmar venta/reserva';
    $nextBody = 'La cotización ya fue aceptada. Este paso abre la venta operativa; no significa que esté pagada.';
    $nextHref = '#order-section';
    $nextLabel = 'Confirmar venta';
} elseif ($hasOrder && $availableToReport > 0 && !$hasPendingPayments) {
    $nextTitle = 'Registrar pago';
    $nextBody = 'La venta ya está abierta. Registra el abono, pago parcial o saldo informado por el cliente.';
    $nextHref = '#payment-form';
    $nextLabel = 'Ir a pagos';
} elseif ($hasOrder && $hasPendingPayments) {
    $nextTitle = 'Validar pagos pendientes';
    $nextBody = 'Hay pagos reportados pendientes de validar o rechazar. Atiéndelos antes de registrar más dinero.';
    $nextHref = '#payments-section';
    $nextLabel = 'Ver pagos';
} elseif ($hasOrder && $canCompleteOrder && (string)($order->operational_status ?? '') !== 'completed') {
    $nextTitle = 'Cerrar operación';
    $nextBody = 'La venta ya está pagada. Actualiza documentos, reserva, entrega y finaliza cuando corresponda.';
    $nextHref = '/admin/sales-orders/show?id=' . (int)$order->id;
    $nextLabel = 'Abrir operación';
} elseif ($hasOrder && (string)($order->operational_status ?? '') === 'completed') {
    $nextTitle = 'Flujo completo';
    $nextBody = 'La venta está pagada y la operación aparece como completada.';
    $nextHref = '/admin/sales-orders/show?id=' . (int)$order->id;
    $nextLabel = 'Ver detalle';
}
?>

<style>
  .sales-flow-page { display:flex; flex-direction:column; gap:20px; }
  .sales-flow-layout { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:22px; align-items:start; }
  .left-stack,.right-stack { display:flex; flex-direction:column; gap:20px; min-width:0; }
  .right-stack { position:sticky; top:20px; }
  .panel-card { background:#fff; border:1px solid #e5e7eb; border-radius:22px; padding:22px; box-shadow:0 10px 24px rgba(15,23,42,.04); }
  .panel-card h2,.panel-card h3,.panel-card h4 { margin:0; color:#0f172a; }
  .section-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
  .section-head p,.muted-text { margin:6px 0 0; color:#64748b; font-size:14px; line-height:1.55; }
  .top-actions { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
  .top-actions .cluster { display:flex; gap:10px; flex-wrap:wrap; }
  .hero-card { padding:26px; }
  .hero-top { display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap; margin-bottom:20px; }
  .hero-title { margin:0; font-size:32px; line-height:1.05; font-weight:900; color:#0f172a; }
  .hero-contact { margin-top:10px; color:#64748b; line-height:1.7; font-size:14px; }
  .badge { display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:7px 12px; font-size:12px; font-weight:900; border:1px solid transparent; white-space:nowrap; }
  .badge.blue{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}.badge.cyan{background:#ecfeff;color:#0f766e;border-color:#a5f3fc}.badge.indigo{background:#eef2ff;color:#4338ca;border-color:#c7d2fe}.badge.amber{background:#fffbeb;color:#b45309;border-color:#fde68a}.badge.yellow{background:#fefce8;color:#a16207;border-color:#fde047}.badge.green{background:#f0fdf4;color:#15803d;border-color:#bbf7d0}.badge.red{background:#fef2f2;color:#b91c1c;border-color:#fecaca}.badge.gray{background:#f3f4f6;color:#4b5563;border-color:#d1d5db}
  .summary-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
  .summary-item,.quick-stat { border:1px solid #e5e7eb; border-radius:18px; padding:16px; background:#f8fafc; min-width:0; }
  .summary-label,.quick-stat .k { font-size:12px; color:#64748b; text-transform:uppercase; font-weight:900; letter-spacing:.04em; margin-bottom:8px; }
  .summary-value,.quick-stat .v { font-size:15px; font-weight:800; color:#0f172a; line-height:1.6; word-break:break-word; }
  .summary-value.muted { color:#475569; font-weight:600; }
  .flow-steps { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; }
  .flow-step { border:1px solid #e5e7eb; border-radius:18px; padding:14px; background:#f8fafc; min-height:104px; position:relative; overflow:hidden; }
  .flow-step.done { border-color:#bbf7d0; background:#f0fdf4; }
  .flow-step.active { border-color:#bae6fd; background:#eff6ff; box-shadow:0 8px 20px rgba(14,165,233,.12); }
  .flow-step.blocked { opacity:.72; }
  .flow-num { width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; background:#fff; border:1px solid #cbd5e1; font-weight:900; color:#0f172a; margin-bottom:10px; }
  .flow-title { font-weight:900; color:#0f172a; margin-bottom:6px; }
  .flow-copy { font-size:12px; color:#64748b; line-height:1.4; }
  .quote-card,.payment-card,.timeline-item { border:1px solid #e5e7eb; border-radius:18px; padding:16px; background:#fafafa; }
  .quote-card.is-current { border-color:#bbf7d0; background:#f0fdf4; }
  .timeline,.cards-list { display:flex; flex-direction:column; gap:14px; }
  .timeline-title { margin:0 0 6px; font-size:15px; font-weight:900; color:#0f172a; }
  .timeline-meta { font-size:12px; color:#64748b; margin-bottom:8px; line-height:1.55; }
  .timeline-body { color:#334155; line-height:1.65; white-space:pre-wrap; font-size:14px; }
  .empty-state,.notice-state { padding:16px; border:1px dashed #cbd5e1; border-radius:16px; color:#64748b; background:#fff; font-size:14px; line-height:1.5; }
  .notice-state.warning { border-color:#fde68a; background:#fffbeb; color:#92400e; }
  .notice-state.success { border-color:#bbf7d0; background:#f0fdf4; color:#166534; }
  .form-block { display:flex; flex-direction:column; gap:10px; }
  .form-block label { font-weight:800; color:#0f172a; font-size:13px; }
  .form-block input,.form-block select,.form-block textarea { width:100%; border:1px solid #cbd5e1; border-radius:14px; padding:12px 14px; font:inherit; background:#fff; color:#0f172a; box-sizing:border-box; }
  .form-block textarea { min-height:96px; resize:vertical; }
  .form-section-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
  .btn-main,.btn-outline,.btn-green,.btn-red { border:none; border-radius:14px; padding:12px 18px; font:inherit; font-weight:900; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; width:100%; transition:.18s ease; box-sizing:border-box; text-align:center; }
  .btn-main:hover,.btn-outline:hover,.btn-green:hover,.btn-red:hover { transform:translateY(-1px); }
  .btn-main{background:#0ea5e9;color:#fff}.btn-outline{background:#fff;color:#4c1d95;border:1px solid #d1d5db}.btn-green{background:#16a34a;color:#fff}.btn-red{background:#b91c1c;color:#fff}
  .btn-inline { width:auto; align-self:flex-start; min-width:180px; }
  .quote-actions,.payment-actions { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin-top:12px; }
  .quick-stats { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
  .finance-row { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
  details.admin-details { border-top:1px solid #e5e7eb; padding-top:16px; margin-top:16px; }
  details.admin-details summary { cursor:pointer; font-weight:900; color:#4c1d95; }
  .side-next { border:1px solid #bae6fd; background:linear-gradient(180deg,#eff6ff,#fff); }
  .side-next h3 { font-size:20px; }
  @media (max-width:1200px){.sales-flow-layout{grid-template-columns:1fr}.right-stack{position:static}.flow-steps{grid-template-columns:repeat(2,minmax(0,1fr))}.finance-row{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media (max-width:760px){.summary-grid,.quick-stats,.quote-actions,.payment-actions,.form-section-grid,.finance-row{grid-template-columns:1fr}.flow-steps{grid-template-columns:1fr}.hero-title{font-size:26px}.panel-card,.hero-card{padding:18px}.btn-inline{width:100%;align-self:stretch}.top-actions{align-items:stretch}.top-actions .cluster{width:100%}.top-actions .cluster a{flex:1}}
</style>

<section class="content sales-flow-page">
  <div class="top-actions">
    <div class="cluster">
      <a href="<?= htmlspecialchars($returnTo) ?>" class="btn-outline" style="width:auto;">← Volver</a>
      <a href="/admin/sales-orders" class="btn-outline" style="width:auto;">Ventas / Reservas</a>
      <?php if ($order): ?>
        <a href="/admin/sales-orders/show?id=<?= (int)$order->id ?>" class="btn-outline" style="width:auto;">Ver orden</a>
      <?php endif; ?>
    </div>

    <?php if ($customerWhatsAppUrl !== ''): ?>
      <a href="<?= htmlspecialchars($customerWhatsAppUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-outline" style="width:auto;">Abrir WhatsApp</a>
    <?php endif; ?>
  </div>

  <div class="panel-card">
    <div class="section-head">
      <div>
        <h3>Flujo comercial de la oportunidad</h3>
        <p>Este es el orden recomendado: primero cotizas, luego confirmas la venta/reserva, después registras pagos y finalmente actualizas la operación.</p>
      </div>
    </div>

    <div class="flow-steps">
      <div class="flow-step done">
        <div class="flow-num">1</div>
        <div class="flow-title">Cliente</div>
        <div class="flow-copy">Datos, interés y seguimiento inicial.</div>
      </div>
      <div class="flow-step <?= $hasAcceptedQuote ? 'done' : (($currentQuote ? 'active' : 'blocked')) ?>">
        <div class="flow-num">2</div>
        <div class="flow-title">Cotización</div>
        <div class="flow-copy"><?= $hasAcceptedQuote ? 'Aceptada por el cliente.' : ($currentQuote ? 'Pendiente de aceptación.' : 'Aún no creada.') ?></div>
      </div>
      <div class="flow-step <?= $hasOrder ? 'done' : ($canCreateOrder ? 'active' : 'blocked') ?>">
        <div class="flow-num">3</div>
        <div class="flow-title">Venta / reserva</div>
        <div class="flow-copy"><?= $hasOrder ? 'Proceso operativo abierto.' : ($canCreateOrder ? 'Lista para confirmar.' : 'Requiere cotización aceptada.') ?></div>
      </div>
      <div class="flow-step <?= ($balanceAmount <= 0 && $quoteTotal > 0) ? 'done' : (($hasOrder && $quoteTotal > 0) ? 'active' : 'blocked') ?>">
        <div class="flow-num">4</div>
        <div class="flow-title">Pagos</div>
        <div class="flow-copy"><?= $quoteTotal > 0 ? ('Saldo: ' . number_format($balanceAmount, 0, ',', '.') . ' ' . $quoteCurrency) : 'Sin valor cotizado.' ?></div>
      </div>
      <div class="flow-step <?= ($order && (string)($order->operational_status ?? '') === 'completed') ? 'done' : ($hasOrder ? 'active' : 'blocked') ?>">
        <div class="flow-num">5</div>
        <div class="flow-title">Operación</div>
        <div class="flow-copy"><?= $order ? operational_label($order->operational_status ?? '') : 'Se activa con la venta.' ?></div>
      </div>
    </div>
  </div>

  <div class="sales-flow-layout">
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
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <span class="<?= sales_badge_class((string)($item->sales_stage ?? '')) ?>"><?= htmlspecialchars(sales_stage_label_detail((string)($item->sales_stage ?? ''))) ?></span>
            <span class="badge gray"><?= htmlspecialchars($item->sales_temperature ?? 'warm') ?></span>
          </div>
        </div>

        <div class="summary-grid">
          <div class="summary-item">
            <div class="summary-label">Origen</div>
            <div class="summary-value"><?= htmlspecialchars($item->source_origin ?? 'Sin origen') ?><br><span class="summary-value muted">Canal: <?= htmlspecialchars($item->source_channel ?? 'Sin canal') ?></span></div>
          </div>
          <div class="summary-item">
            <div class="summary-label">Interés</div>
            <div class="summary-value">
              <?= htmlspecialchars($item->interest_type ?? 'Sin interés') ?><br>
              <span class="summary-value muted">
                <?php if (!empty($item->package_slug)): ?>Paquete: <?= htmlspecialchars($item->package_slug) ?><?php elseif (!empty($item->extra_service_slug)): ?>Servicio: <?= htmlspecialchars($item->extra_service_slug) ?><?php else: ?>Sin referencia<?php endif; ?>
              </span>
            </div>
          </div>
          <div class="summary-item">
            <div class="summary-label">Asesor asignado</div>
            <div class="summary-value"><?php if ($assignedAdvisor): ?><?= htmlspecialchars((string) ($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? ('Asesor #' . $assignedAdminId))) ?><?php elseif ($isAssigned): ?>Asesor #<?= (int)$assignedAdminId ?><?php else: ?>Sin asignar<?php endif; ?></div>
          </div>
          <div class="summary-item">
            <div class="summary-label">Seguimiento</div>
            <div class="summary-value">Próximo: <?= htmlspecialchars($item->next_follow_up_at ?? 'Sin fecha') ?><br><span class="summary-value muted">Último contacto: <?= htmlspecialchars($item->last_contact_at ?? 'Sin registro') ?></span></div>
          </div>
          <div class="summary-item">
            <div class="summary-label">Resumen comercial</div>
            <div class="summary-value muted"><?= nl2br(htmlspecialchars($item->notes_summary ?? 'Sin resumen')) ?></div>
          </div>
          <div class="summary-item">
            <div class="summary-label">Venta / reserva</div>
            <div class="summary-value">
              <?php if ($order): ?>
                <?= htmlspecialchars($order->order_number ?? ('Orden #' . (int)$order->id)) ?><br>
                <span class="summary-value muted"><?= htmlspecialchars(operational_label($order->operational_status ?? '')) ?> · <?= htmlspecialchars($order->commercial_status ?? 'open') ?></span>
              <?php else: ?>
                Aún no creada
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="panel-card" id="quotes-section">
        <div class="section-head">
          <div>
            <h3>2. Cotización</h3>
            <p>La cotización representa la propuesta enviada al cliente. Solo cuando el cliente la acepta se debe abrir la venta/reserva.</p>
          </div>
          <?php if ($acceptedQuote): ?><span class="badge green">Cotización aceptada</span><?php endif; ?>
        </div>

        <?php if (empty($quotes)): ?>
          <div class="empty-state">No hay cotizaciones registradas todavía.</div>
        <?php else: ?>
          <div class="cards-list">
            <?php foreach ($quotes as $quote): ?>
              <?php $quoteStatus = (string)($quote->status ?? 'draft'); ?>
              <div class="quote-card <?= $acceptedQuote && (int)$acceptedQuote->id === (int)$quote->id ? 'is-current' : '' ?>">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                  <div>
                    <div class="timeline-title"><?= htmlspecialchars($quote->title ?? '') ?></div>
                    <div class="timeline-meta">
                      <?= number_format((float)($quote->amount ?? 0), 0, ',', '.') ?> <?= htmlspecialchars($quote->currency ?? 'COP') ?> ·
                      <span class="<?= sales_badge_class($quoteStatus) ?>"><?= htmlspecialchars(quote_status_label($quoteStatus)) ?></span>
                      <?php if (!empty($quote->valid_until)): ?><br>Vigencia: <?= htmlspecialchars($quote->valid_until) ?><?php endif; ?>
                      <?php if (!empty($quote->sent_at)): ?><br>Enviada: <?= htmlspecialchars($quote->sent_at) ?><?php endif; ?>
                    </div>
                  </div>
                </div>

                <?php if (!empty($quote->summary)): ?><div class="timeline-body"><?= nl2br(htmlspecialchars($quote->summary)) ?></div><?php endif; ?>

                <?php if ($quoteStatus === 'draft'): ?>
                  <div class="quote-actions" style="grid-template-columns:1fr;">
                    <form method="POST" action="/admin/sales/quote/mark-sent">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="quote_id" value="<?= (int)$quote->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                      <button type="submit" class="btn-outline">Marcar como enviada</button>
                    </form>
                  </div>
                <?php elseif ($quoteStatus === 'sent'): ?>
                  <div class="quote-actions">
                    <form method="POST" action="/admin/sales/quote/mark-accepted">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="quote_id" value="<?= (int)$quote->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                      <button type="submit" class="btn-green">Cliente aceptó</button>
                    </form>
                    <form method="POST" action="/admin/sales/quote/mark-rejected">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="quote_id" value="<?= (int)$quote->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                      <button type="submit" class="btn-red">Cliente rechazó</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!$order): ?>
          <div id="quote-form" style="margin-top:22px;">
            <div class="section-head">
              <div>
                <h4>Nueva cotización</h4>
                <p>Usa esta sección para crear una nueva propuesta antes de confirmar la venta.</p>
              </div>
            </div>
            <form method="POST" action="/admin/sales/quote/create" class="form-block">
              <?= Csrf::input(); ?>
              <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
              <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
              <div class="form-section-grid">
                <div class="form-block"><label for="quote_title">Título</label><input type="text" id="quote_title" name="title" placeholder="Ej: Propuesta Canadá junio"></div>
                <div class="form-block"><label for="quote_amount">Monto</label><input type="number" step="0.01" id="quote_amount" name="amount" placeholder="Ej: 1850000"></div>
                <div class="form-block">
                  <label for="quote_currency">Moneda</label>
                  <select id="quote_currency" name="currency" required>
                    <?php foreach ($currencies as $currencyOption): ?>
                      <?php
                        $code = strtoupper((string)($currencyOption->code ?? ''));
                        if ($code === '') { continue; }
                        $name = (string)($currencyOption->name ?? $code);
                        $symbol = (string)($currencyOption->symbol ?? '');
                        $selected = $code === $selectedQuoteCurrency ? 'selected' : '';
                      ?>
                      <option value="<?= htmlspecialchars($code) ?>" <?= $selected ?>>
                        <?= htmlspecialchars($code . ' - ' . $name . ($symbol !== '' ? ' (' . $symbol . ')' : '')) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <small class="help">Estas monedas se administran desde <a href="/admin/currencies">Monedas</a>.</small>
                </div>
                <div class="form-block"><label for="quote_valid_until">Vigencia</label><input type="date" id="quote_valid_until" name="valid_until"></div>
              </div>
              <div class="form-block"><label for="quote_summary">Resumen</label><textarea id="quote_summary" name="summary" placeholder="Resumen de lo cotizado, condiciones y observaciones."></textarea></div>
              <button type="submit" class="btn-main btn-inline">Crear cotización</button>
            </form>
          </div>
        <?php else: ?>
          <div class="notice-state warning" style="margin-top:18px;">Esta oportunidad ya tiene una venta/reserva abierta. Si necesitas cambiar el valor, lo recomendable es registrar una nota operativa y manejar el ajuste con cuidado para no duplicar cotizaciones.</div>
        <?php endif; ?>
      </div>

      <div class="panel-card" id="order-section">
        <div class="section-head">
          <div>
            <h3>3. Venta / reserva</h3>
            <p>Este paso se usa cuando el cliente ya dijo “sí” a la cotización. No significa pago completo; solo abre el control operativo y de pagos.</p>
          </div>
        </div>

        <?php if ($order): ?>
          <div class="summary-grid">
            <div class="summary-item"><div class="summary-label">Número</div><div class="summary-value"><?= htmlspecialchars($order->order_number ?? '') ?></div></div>
            <div class="summary-item"><div class="summary-label">Valor</div><div class="summary-value"><?= number_format((float)($order->total_amount ?? 0), 0, ',', '.') ?> <?= htmlspecialchars($order->currency ?? $quoteCurrency) ?></div></div>
            <div class="summary-item"><div class="summary-label">Pago</div><div class="summary-value"><?= number_format((float)($order->paid_amount ?? 0), 0, ',', '.') ?> <?= htmlspecialchars($order->currency ?? $quoteCurrency) ?></div></div>
            <div class="summary-item"><div class="summary-label">Operación</div><div class="summary-value"><?= htmlspecialchars(operational_label($order->operational_status ?? '')) ?></div></div>
          </div>
          <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
            <a href="/admin/sales-orders/show?id=<?= (int)$order->id ?>" class="btn-green" style="width:auto;">Abrir detalle operativo</a>
            <a href="/admin/sales-orders" class="btn-outline" style="width:auto;">Ver listado de ventas</a>
          </div>
        <?php elseif ($canCreateOrder): ?>
          <div class="notice-state success" style="margin-bottom:16px;">La cotización fue aceptada. Confirma la venta/reserva para empezar a registrar pagos y avanzar documentos, reserva, entrega y cierre.</div>
          <form method="POST" action="/admin/sales-orders/create-from-opportunity" class="form-block">
            <?= Csrf::input(); ?>
            <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
            <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
            <button type="submit" class="btn-green btn-inline">Confirmar venta/reserva</button>
          </form>
        <?php else: ?>
          <div class="empty-state">Primero crea, envía y acepta una cotización con valor. Después podrás abrir la venta/reserva.</div>
        <?php endif; ?>
      </div>

      <div class="panel-card" id="payments-section">
        <div class="section-head">
          <div>
            <h3>4. Pagos</h3>
            <p>Registra pagos solo después de abrir la venta/reserva. Los pagos reportados deben validarse o rechazarse.</p>
          </div>
        </div>

        <div class="finance-row">
          <div class="quick-stat"><div class="k">Cotizado</div><div class="v"><?= number_format((float)$quoteTotal, 0, ',', '.') ?> <?= htmlspecialchars($quoteCurrency) ?></div></div>
          <div class="quick-stat"><div class="k">Validado</div><div class="v"><?= number_format((float)$verifiedTotal, 0, ',', '.') ?> <?= htmlspecialchars($quoteCurrency) ?></div></div>
          <div class="quick-stat"><div class="k">En validación</div><div class="v"><?= number_format((float)$pendingReportedTotal, 0, ',', '.') ?> <?= htmlspecialchars($quoteCurrency) ?></div></div>
          <div class="quick-stat"><div class="k">Saldo disponible</div><div class="v"><?= number_format((float)$availableToReport, 0, ',', '.') ?> <?= htmlspecialchars($quoteCurrency) ?></div></div>
        </div>

        <?php if (empty($payments)): ?>
          <div class="empty-state">No hay pagos registrados todavía.</div>
        <?php else: ?>
          <div class="cards-list">
            <?php foreach ($payments as $payment): ?>
              <?php $paymentStatus = (string)($payment->status ?? 'reported'); $paymentKind = (string)($payment->payment_kind ?? 'payment'); ?>
              <div class="payment-card">
                <div class="timeline-title"><?= htmlspecialchars(payment_kind_label($paymentKind)) ?> · <?= number_format((float)($payment->amount ?? 0), 0, ',', '.') ?> <?= htmlspecialchars($payment->currency ?? 'COP') ?></div>
                <div class="timeline-meta">
                  <span class="<?= sales_badge_class($paymentStatus) ?>"><?= htmlspecialchars(payment_status_label($paymentStatus)) ?></span> ·
                  Método: <?= htmlspecialchars($payment->payment_method ?? '') ?><br>
                  Referencia: <?= htmlspecialchars($payment->payment_reference ?? 'Sin referencia') ?><br>
                  Reportado: <?= htmlspecialchars($payment->reported_at ?? '') ?>
                  <?php if (!empty($payment->verified_at)): ?><br>Validado: <?= htmlspecialchars($payment->verified_at) ?><?php endif; ?>
                </div>
                <?php if (!empty($payment->notes)): ?><div class="timeline-body"><?= nl2br(htmlspecialchars($payment->notes)) ?></div><?php endif; ?>

                <?php if ($paymentStatus === 'reported'): ?>
                  <div class="payment-actions">
                    <form method="POST" action="/admin/sales/payment/verify">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="payment_id" value="<?= (int)$payment->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                      <button type="submit" class="btn-green">Validar pago</button>
                    </form>
                    <form method="POST" action="/admin/sales/payment/reject" class="form-block">
                      <?= Csrf::input(); ?>
                      <input type="hidden" name="payment_id" value="<?= (int)$payment->id ?>">
                      <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
                      <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
                      <input type="text" name="reason" placeholder="Motivo del rechazo">
                      <button type="submit" class="btn-red">Rechazar pago</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div id="payment-form" style="margin-top:22px;">
          <div class="section-head">
            <div>
              <h4>Registrar nuevo pago</h4>
              <p>El monto no puede superar el saldo disponible. Si ya existe un pago pendiente de validación, primero valídalo o recházalo.</p>
            </div>
          </div>

          <?php if (!$hasOrder): ?>
            <div class="empty-state">Primero confirma la venta/reserva para asociar correctamente los pagos.</div>
          <?php elseif (!$hasAcceptedQuote): ?>
            <div class="empty-state">Primero acepta una cotización con valor.</div>
          <?php elseif ($availableToReport <= 0): ?>
            <div class="notice-state success">No hay saldo disponible para registrar otro pago. Si hay un error, rechaza pagos pendientes o revisa el detalle antes de registrar ajustes.</div>
          <?php else: ?>
            <form method="POST" action="/admin/sales/payment/report" class="form-block">
              <?= Csrf::input(); ?>
              <input type="hidden" name="sales_opportunity_id" value="<?= (int)$item->id ?>">
              <input type="hidden" name="return_to" value="<?= htmlspecialchars($currentUrl) ?>">
              <div class="form-section-grid">
                <div class="form-block">
                  <label for="payment_kind">Tipo de pago</label>
                  <select id="payment_kind" name="payment_kind">
                    <option value="deposit">Abono inicial</option>
                    <option value="partial">Pago parcial</option>
                    <option value="full">Pago total</option>
                    <option value="balance">Saldo</option>
                  </select>
                </div>
                <div class="form-block"><label for="amount">Monto</label><input type="number" step="0.01" id="amount" name="amount" placeholder="Ej: 350000" data-balance="<?= htmlspecialchars((string)$availableToReport) ?>"></div>
                <div class="form-block">
                  <label for="currency">Moneda</label>
                  <input type="hidden" name="currency" value="<?= htmlspecialchars($quoteCurrency) ?>">
                  <select id="currency" disabled>
                    <?php foreach ($currencies as $currencyOption): ?>
                      <?php
                        $code = strtoupper((string)($currencyOption->code ?? ''));
                        if ($code === '') { continue; }
                        $name = (string)($currencyOption->name ?? $code);
                        $symbol = (string)($currencyOption->symbol ?? '');
                        $selected = $code === strtoupper((string)$quoteCurrency) ? 'selected' : '';
                      ?>
                      <option value="<?= htmlspecialchars($code) ?>" <?= $selected ?>>
                        <?= htmlspecialchars($code . ' - ' . $name . ($symbol !== '' ? ' (' . $symbol . ')' : '')) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <small class="help">Los pagos heredan la moneda de la cotización aceptada.</small>
                </div>
                <div class="form-block">
                  <label for="payment_method">Método</label>
                  <select id="payment_method" name="payment_method"><option value="transfer">Transferencia</option><option value="cash">Efectivo</option><option value="consignment">Consignación</option><option value="card">Tarjeta</option><option value="external_link">Link externo</option><option value="other">Otro</option></select>
                </div>
              </div>
              <div class="form-block"><label for="payment_reference">Referencia externa opcional</label><input type="text" id="payment_reference" name="payment_reference" placeholder="Ej: comprobante bancario"></div>
              <div class="form-block"><label for="payment_notes">Notas</label><textarea id="payment_notes" name="notes" placeholder="Observaciones del pago reportado."></textarea></div>
              <button type="submit" class="btn-main btn-inline">Registrar pago</button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="panel-card">
        <div class="section-head">
          <div>
            <h3>Timeline comercial</h3>
            <p>Historial de cambios, seguimientos, cotizaciones, pagos y cierres de la oportunidad.</p>
          </div>
        </div>
        <?php if (empty($events)): ?>
          <div class="empty-state">No hay eventos registrados todavía.</div>
        <?php else: ?>
          <div class="timeline">
            <?php foreach ($events as $event): ?>
              <div class="timeline-item">
                <div class="timeline-title"><?= htmlspecialchars($event->title ?? '') ?></div>
                <div class="timeline-meta"><?= htmlspecialchars($event->event_type ?? '') ?> · <?= htmlspecialchars($event->created_at ?? '') ?><?php if (!empty($event->admin_user_id)): ?> · Admin #<?= (int)$event->admin_user_id ?><?php endif; ?></div>
                <?php if (!empty($event->message)): ?><div class="timeline-body"><?= nl2br(htmlspecialchars($event->message)) ?></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="right-stack">
      <div class="panel-card side-next">
        <div class="section-head">
          <div>
            <h3>Siguiente paso</h3>
            <p>Acción sugerida según el estado actual.</p>
          </div>
        </div>
        <h4><?= htmlspecialchars($nextTitle) ?></h4>
        <p class="muted-text"><?= htmlspecialchars($nextBody) ?></p>
        <div style="margin-top:14px;">
          <a href="<?= htmlspecialchars($nextHref) ?>" class="btn-main"><?= htmlspecialchars($nextLabel) ?></a>
        </div>
      </div>

      <div class="panel-card">
        <div class="section-head"><div><h3>Resumen financiero</h3><p>Control rápido de cotización, pagos y saldo.</p></div></div>
        <div class="quick-stats">
          <div class="quick-stat"><div class="k">Cotización</div><div class="v"><?= $quoteTotal > 0 ? number_format($quoteTotal, 0, ',', '.') . ' ' . htmlspecialchars($quoteCurrency) : 'Sin cotización' ?></div></div>
          <div class="quick-stat"><div class="k">Validado</div><div class="v"><?= number_format($verifiedTotal, 0, ',', '.') ?> <?= htmlspecialchars($quoteCurrency) ?></div></div>
          <div class="quick-stat"><div class="k">Pendiente validar</div><div class="v"><?= number_format($pendingReportedTotal, 0, ',', '.') ?> <?= htmlspecialchars($quoteCurrency) ?></div></div>
          <div class="quick-stat"><div class="k">Saldo real</div><div class="v"><?= number_format($balanceAmount, 0, ',', '.') ?> <?= htmlspecialchars($quoteCurrency) ?></div></div>
        </div>
      </div>

      <div class="panel-card">
        <div class="section-head"><div><h3>Gestión rápida</h3><p>Acciones de seguimiento sin romper el flujo principal.</p></div></div>
        <div class="form-block">
          <a href="/admin/sales/edit?id=<?= (int)$item->id ?>&return_to=<?= urlencode($returnTo) ?>" class="btn-outline">Editar oportunidad</a>
          <?php if (!$isAssigned): ?>
            <form method="POST" action="/admin/sales/assign" class="form-block">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$item->id ?>">
              <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">
              <button type="submit" class="btn-outline" name="action" value="stay">Asignarme</button>
            </form>
          <?php elseif ($isMine): ?>
            <div class="notice-state success">Esta oportunidad está asignada a ti.</div>
          <?php else: ?>
            <div class="notice-state warning">Asignada a <?= htmlspecialchars((string)($assignedAdvisor->full_name ?? $assignedAdvisor->email ?? ('Asesor #' . $assignedAdminId))) ?></div>
          <?php endif; ?>
        </div>

        <details class="admin-details">
          <summary>Más acciones administrativas</summary>
          <div style="margin-top:14px;display:flex;flex-direction:column;gap:16px;">
            <form method="POST" action="/admin/sales/schedule-followup" class="form-block">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$item->id ?>">
              <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">
              <label for="next_follow_up_at">Programar seguimiento</label>
              <input type="datetime-local" id="next_follow_up_at" name="next_follow_up_at">
              <label for="followup_message">Nota</label>
              <textarea id="followup_message" name="message" placeholder="Qué vas a hacer o recordar"></textarea>
              <button type="submit" class="btn-outline">Programar seguimiento</button>
            </form>

            <form method="POST" action="/admin/sales/add-note" class="form-block">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$item->id ?>">
              <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">
              <label for="note_message">Agregar nota</label>
              <textarea id="note_message" name="message" placeholder="Escribe una nota comercial o de seguimiento."></textarea>
              <button type="submit" class="btn-outline">Agregar nota</button>
            </form>

            <?php if (!$order): ?>
              <form method="POST" action="/admin/sales/mark-lost" class="form-block">
                <?= Csrf::input(); ?>
                <input type="hidden" name="id" value="<?= (int)$item->id ?>">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo) ?>">
                <label for="lost_reason_code">Motivo de pérdida</label>
                <select id="lost_reason_code" name="lost_reason_code"><option value="">Selecciona un motivo</option><option value="no_response">No respondió</option><option value="no_budget">Sin presupuesto</option><option value="better_price">Encontró mejor precio</option><option value="postponed_trip">Aplazó el viaje</option><option value="no_availability">Sin disponibilidad</option><option value="documents_issue">Problema de documentación</option><option value="lost_interest">Perdió interés</option><option value="bought_elsewhere">Compró en otra parte</option><option value="not_qualified">No califica</option><option value="other">Otro</option></select>
                <label for="lost_reason_detail">Detalle adicional</label>
                <textarea id="lost_reason_detail" name="lost_reason_detail" placeholder="Opcional: agrega contexto adicional."></textarea>
                <button type="submit" class="btn-red">Marcar como perdida</button>
              </form>
            <?php endif; ?>
          </div>
        </details>
      </div>
    </div>
  </div>

<script>
(function () {
  const kind = document.getElementById('payment_kind');
  const amount = document.getElementById('amount');
  if (!kind || !amount) return;
  const balance = Number(amount.dataset.balance || '0');
  const fillBalance = function () {
    if ((kind.value === 'full' || kind.value === 'balance') && balance > 0) {
      amount.value = balance.toFixed(2);
    }
  };
  kind.addEventListener('change', fillBalance);
  fillBalance();
})();
</script>
</section>
