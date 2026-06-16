<?php
$page_title = 'Perfil de usuario';
$page_subtitle = 'Información general de tu cuenta.';

$account = $account ?? null;
$customer = $customer ?? null;
$favorites = $favorites ?? [];
$recommendations = $recommendations ?? [];
$inquiries = $inquiries ?? [];
$preference = $preference ?? null;
$preferenceTags = $preferenceTags ?? [];
$preferenceErrors = $preferenceErrors ?? [];
$preferenceMessage = $preferenceMessage ?? null;
$selectedPreferenceTags = (array) ($preference?->preferred_tag_slugs_json ?? []);
$profilePhotoUrl = profile_photo_url($customer->profile_photo_path ?? null);

function account_package_asset(?string $path): string
{
  $path = trim((string) $path);
  return $path === '' ? '/img/packages/default.jpg' : '/' . ltrim(str_replace('\\', '/', $path), '/');
}

function account_lead_status(?string $status): string
{
  return match ((string) $status) {
    'new' => 'Recibida',
    'in_progress' => 'En atención',
    'waiting_customer' => 'Esperando respuesta',
    'closed' => 'Cerrada',
    'lost' => 'Finalizada',
    default => ucfirst((string) $status),
  };
}

function account_preference_error(array $errors, string $field): ?string
{
  return $errors[$field][0] ?? null;
}

function account_bool_text($value): string
{
  return !empty($value) ? 'Sí' : 'No';
}

function account_money($value): string
{
  if ($value === null || $value === '') {
    return '';
  }

  return number_format((float) $value, 0, ',', '.');
}

function account_render_meta_value($value): string
{
  if (is_bool($value)) {
    return $value ? 'Sí' : 'No';
  }

  if (is_array($value) || is_object($value)) {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  }

  return (string) $value;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi perfil | Alestur</title>
  <link rel="stylesheet" href="/styles/mainLayout.css">

  <style>
    :root {
      --brand: #e20f3f;
      --brand-dark: #b90730;
      --brand-soft: #fff1f4;
      --text: #111827;
      --muted: #64748b;
      --line: #e5e7eb;
      --bg: #f8fafc;
      --card: #ffffff;
      --ok: #16a34a;
      --warn: #d97706;
      --info: #2563eb;
      --danger: #dc2626;
      --shadow: 0 18px 45px rgba(15, 23, 42, .08);
      --radius: 22px;
    }

    body {
      margin: 0;
      background: var(--bg);
      color: var(--text);
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .profile-page {
      min-height: 100vh;
      background:
        radial-gradient(circle at top left, rgba(226, 15, 63, .12), transparent 32%),
        linear-gradient(180deg, #fff 0%, #f8fafc 36%, #f8fafc 100%);
    }

    .profile-hero {
      background:
        linear-gradient(135deg, rgba(226, 15, 63, .96), rgba(174, 9, 48, .96)),
        url('/img/bg-profile.jpg');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 54px 24px 42px;
      border-bottom-left-radius: 34px;
      border-bottom-right-radius: 34px;
      box-shadow: 0 20px 48px rgba(226, 15, 63, .25);
    }

    .profile-container {
      width: min(1180px, calc(100% - 32px));
      margin: 0 auto;
    }

    .hero-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 22px;
      flex-wrap: wrap;
    }

    .hero-title h1 {
      margin: 0;
      font-size: clamp(34px, 5vw, 56px);
      line-height: 1;
      letter-spacing: -1.5px;
    }

    .hero-title p {
      margin: 12px 0 0;
      font-size: 18px;
      color: rgba(255, 255, 255, .88);
      max-width: 660px;
    }

    .hero-avatar {
      display: flex;
      align-items: center;
      gap: 14px;
      background: rgba(255, 255, 255, .15);
      border: 1px solid rgba(255, 255, 255, .22);
      border-radius: 999px;
      padding: 10px 16px 10px 10px;
      backdrop-filter: blur(10px);
    }

    .avatar-circle {
      width: 58px;
      height: 58px;
      border-radius: 999px;
      background: white;
      color: var(--brand);
      display: grid;
      place-items: center;
      font-size: 24px;
      font-weight: 900;
      overflow: hidden;
    }

    .avatar-circle img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .hero-avatar strong {
      display: block;
      color: white;
      font-size: 15px;
    }

    .hero-avatar span {
      display: block;
      color: rgba(255, 255, 255, .78);
      font-size: 13px;
    }

    .profile-content {
      width: min(1180px, calc(100% - 32px));
      margin: -28px auto 60px;
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    .grid {
      display: grid;
      gap: 22px;
    }

    .two-col {
      grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr);
    }

    .card {
      background: var(--card);
      border: 1px solid rgba(226, 232, 240, .9);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 24px;
      overflow: hidden;
    }

    .card-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      flex-wrap: wrap;
    }

    .card h2,
    .card h3 {
      margin: 0;
      letter-spacing: -.6px;
    }

    .card h2 {
      font-size: 28px;
    }

    .card h3 {
      font-size: 22px;
    }

    .card-sub {
      margin: 7px 0 0;
      color: var(--muted);
      font-size: 15px;
      line-height: 1.45;
    }

    .sep {
      height: 1px;
      background: var(--line);
      margin: 18px 0;
    }

    .btn {
      border: 1px solid var(--line);
      background: white;
      color: var(--text);
      padding: 10px 14px;
      border-radius: 12px;
      font-weight: 800;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: .18s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 12px 25px rgba(15, 23, 42, .08);
    }

    .btn.primary {
      background: var(--brand);
      border-color: var(--brand);
      color: white;
    }

    .btn.primary:hover {
      background: var(--brand-dark);
    }

    .btn.danger {
      color: var(--danger);
      border-color: rgba(220, 38, 38, .25);
      background: #fff5f5;
    }

    .btn.small {
      padding: 8px 12px;
      font-size: 14px;
      border-radius: 10px;
    }

    .actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .form {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 7px;
    }

    .field.full {
      grid-column: 1 / -1;
    }

    .field label {
      font-size: 14px;
      font-weight: 800;
      color: #334155;
    }

    .field input,
    .field select,
    .field textarea {
      width: 100%;
      box-sizing: border-box;
      border: 1px solid #dbe3ef;
      background: #f8fafc;
      padding: 12px 13px;
      border-radius: 13px;
      font-size: 15px;
      outline: none;
      transition: .18s ease;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      border-color: var(--brand);
      background: white;
      box-shadow: 0 0 0 4px rgba(226, 15, 63, .10);
    }

    .field input[readonly] {
      color: #334155;
      background: #f8fafc;
    }

    .hint {
      color: var(--muted);
      font-size: 13px;
    }

    .error {
      color: #b91c1c;
      font-size: 13px;
      font-weight: 700;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 900;
      line-height: 1;
    }

    .badge.ok {
      background: #dcfce7;
      color: #166534;
    }

    .badge.warn {
      background: #fef3c7;
      color: #92400e;
    }

    .badge.info {
      background: #dbeafe;
      color: #1d4ed8;
    }

    .stat-list {
      display: grid;
      gap: 14px;
    }

    .profile-photo-summary {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 18px;
      padding: 14px;
      border: 1px solid var(--line);
      border-radius: 18px;
      background: #f8fafc;
    }

    .profile-photo-summary-avatar {
      width: 92px;
      height: 92px;
      flex: 0 0 92px;
      display: grid;
      place-items: center;
      overflow: hidden;
      border: 1px solid #dbe3ef;
      border-radius: 999px;
      background: white;
      color: var(--brand);
      font-size: 32px;
      font-weight: 900;
    }

    .profile-photo-summary-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .profile-photo-summary strong,
    .profile-photo-summary span {
      display: block;
    }

    .profile-photo-summary span {
      margin-top: 5px;
      color: var(--muted);
      font-size: 14px;
    }

    .stat-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      padding: 14px;
      border: 1px solid var(--line);
      border-radius: 16px;
      background: #f8fafc;
    }

    .stat-item strong {
      color: #334155;
    }

    .tag-grid {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .tag-check {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 12px;
      border: 1px solid #e5e7eb;
      border-radius: 999px;
      font-weight: 800;
      background: #fff;
      cursor: pointer;
      transition: .18s ease;
    }

    .tag-check:hover {
      border-color: rgba(226, 15, 63, .35);
      background: var(--brand-soft);
    }

    .tag-check input {
      width: auto;
      accent-color: var(--brand);
    }

    .check-list {
      display: grid;
      gap: 11px;
    }

    .check-row {
      display: flex;
      align-items: flex-start;
      gap: 9px;
      font-weight: 800;
      color: #334155;
    }

    .check-row input {
      width: auto;
      margin-top: 3px;
      accent-color: var(--brand);
    }

    .alert {
      padding: 12px 14px;
      border-radius: 14px;
      font-weight: 800;
      margin: 0 0 16px;
    }

    .alert.ok {
      background: #ecfdf5;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .alert.error {
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fecaca;
    }

    .inquiry-list,
    .package-grid {
      display: grid;
      gap: 14px;
    }

    .inquiry-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding: 14px;
      border: 1px solid var(--line);
      border-radius: 16px;
      background: #fff;
    }

    .package-grid {
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    }

    .package-card {
      color: var(--text);
      text-decoration: none;
      border: 1px solid var(--line);
      border-radius: 20px;
      overflow: hidden;
      background: white;
      box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
      transition: .18s ease;
    }

    .package-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
    }

    .package-card img {
      width: 100%;
      height: 170px;
      object-fit: cover;
      display: block;
      background: #f1f5f9;
    }

    .package-card-body {
      padding: 15px;
    }

    .package-card strong {
      display: block;
      font-size: 17px;
      margin-bottom: 6px;
    }

    .package-card p {
      margin: 0;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.45;
    }

    .privacy-text {
      color: #475569;
      line-height: 1.7;
      margin: 0 0 14px;
    }

    .empty-state {
      padding: 22px;
      border: 1px dashed #cbd5e1;
      border-radius: 18px;
      background: #f8fafc;
      color: var(--muted);
      text-align: center;
    }

    .section-title-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    @media (max-width: 900px) {
      .two-col {
        grid-template-columns: 1fr;
      }

      .form {
        grid-template-columns: 1fr;
      }

      .profile-hero {
        padding-top: 40px;
      }
    }

    @media (max-width: 560px) {

      .profile-container,
      .profile-content {
        width: min(calc(100% - 22px), 1180px);
      }

      .card {
        padding: 18px;
        border-radius: 18px;
      }

      .card h2 {
        font-size: 23px;
      }

      .hero-avatar {
        width: 100%;
        border-radius: 20px;
      }

      .inquiry-item {
        align-items: flex-start;
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <div class="profile-page">
    <section class="profile-hero">
      <div class="profile-container hero-content">
        <div class="hero-title">
          <h1>Mi perfil</h1>
          <p>Administra tu información, preferencias de viaje, consultas, favoritos y privacidad desde un solo lugar.</p>
        </div>

        <div class="hero-avatar">
          <div class="avatar-circle">
            <?php if ($profilePhotoUrl): ?>
              <img src="<?= htmlspecialchars($profilePhotoUrl) ?>" alt="Foto de perfil">
            <?php else: ?>
              <?= htmlspecialchars(strtoupper(substr((string) ($customer->first_name ?? $account->email ?? 'U'), 0, 1))) ?>
            <?php endif; ?>
          </div>
          <div>
            <strong><?= htmlspecialchars(trim((string) (($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''))) ?: 'Usuario Alestur') ?></strong>
            <span><?= htmlspecialchars((string) ($account->email ?? 'Cuenta registrada')) ?></span>
          </div>
        </div>
      </div>
    </section>

    <main class="profile-content">
      <div class="grid two-col">
        <section class="card">
          <div class="card-head">
            <div>
              <h2>Información personal</h2>
              <p class="card-sub">Datos principales de tu cuenta en Alestur.</p>
            </div>

            <div class="actions">
              <a href="/users/editUser" class="btn small">Editar</a>
              <a href="/users/changePassword" class="btn small">Cambiar contraseña</a>
            </div>
          </div>

          <div class="sep"></div>

          <div class="profile-photo-summary">
            <div class="profile-photo-summary-avatar">
              <?php if ($profilePhotoUrl): ?>
                <img src="<?= htmlspecialchars($profilePhotoUrl) ?>" alt="Foto de perfil">
              <?php else: ?>
                <?= htmlspecialchars(strtoupper(substr((string) ($customer->first_name ?? $account->email ?? 'U'), 0, 1))) ?>
              <?php endif; ?>
            </div>
            <div>
              <strong>Foto de perfil</strong>
              <span><?= $profilePhotoUrl ? 'Tu foto actual.' : 'Aun no has agregado una foto.' ?></span>
            </div>
          </div>

          <div class="form">
            <div class="field">
              <label>Nombres</label>
              <input type="text" value="<?= htmlspecialchars($customer->first_name ?? '') ?>" readonly>
            </div>

            <div class="field">
              <label>Apellidos</label>
              <input type="text" value="<?= htmlspecialchars($customer->last_name ?? '') ?>" readonly>
            </div>

            <div class="field">
              <label>Correo</label>
              <input type="text" value="<?= htmlspecialchars($account->email ?? '') ?>" readonly>
            </div>

            <div class="field">
              <label>Estado</label>
              <input type="text" value="<?= htmlspecialchars($account->status ?? '') ?>" readonly>
            </div>

            <div class="field">
              <label>Teléfono</label>
              <input type="text" value="<?= htmlspecialchars($customer->phone ?? '') ?>" readonly>
            </div>

            <div class="field">
              <label>WhatsApp</label>
              <input type="text" value="<?= htmlspecialchars($customer->whatsapp ?? '') ?>" readonly>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-head">
            <div>
              <h2>Estado de cuenta</h2>
              <p class="card-sub">Resumen rápido de seguridad y acceso.</p>
            </div>
          </div>

          <div class="sep"></div>

          <div class="stat-list">
            <div class="stat-item">
              <strong>Estado</strong>
              <span class="badge ok"><?= htmlspecialchars($account->status ?? 'Sin estado') ?></span>
            </div>

            <div class="stat-item">
              <strong>Último acceso</strong>
              <span><?= htmlspecialchars($account->last_login_at ?? 'Sin registro') ?></span>
            </div>

            <div class="stat-item">
              <strong>Verificación</strong>
              <?php if (($account->email_verified_at ?? null) !== null && ($account->email_verified_at ?? '') !== ''): ?>
                <span class="badge info">Correo confirmado</span>
              <?php else: ?>
                <span class="badge warn">Pendiente</span>
              <?php endif; ?>
            </div>
          </div>
        </section>
      </div>

      <section class="card">
        <div class="card-head">
          <div>
            <h2>Mis preferencias de viaje</h2>
            <p class="card-sub">Ayúdanos a recomendarte paquetes más cercanos a tus gustos, presupuesto y destinos soñados.</p>
          </div>
        </div>

        <div class="sep"></div>

        <?php if (!empty($_GET['preferences']) && $_GET['preferences'] === 'saved'): ?>
          <p class="alert ok">Preferencias guardadas correctamente.</p>
        <?php endif; ?>

        <?php if ($preferenceMessage): ?>
          <p class="alert error"><?= htmlspecialchars($preferenceMessage) ?></p>
        <?php endif; ?>

        <form class="form" method="POST" action="/users/preferences">
          <?= \app\Core\Csrf::input(); ?>

          <div class="field full">
            <label>Estilos de viaje que te interesan</label>

            <?php if (empty($preferenceTags)): ?>
              <div class="empty-state">Cuando existan etiquetas activas en los paquetes, aparecerán aquí.</div>
            <?php else: ?>
              <div class="tag-grid">
                <?php foreach ($preferenceTags as $tag): ?>
                  <label class="tag-check">
                    <input
                      type="checkbox"
                      name="preferred_tag_slugs[]"
                      value="<?= htmlspecialchars((string) $tag->slug) ?>"
                      <?= in_array((string) $tag->slug, $selectedPreferenceTags, true) ? 'checked' : '' ?>>
                    <?= htmlspecialchars((string) $tag->name) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="field full">
            <label for="desired_destinations">Destinos deseados</label>
            <input
              id="desired_destinations"
              name="desired_destinations"
              type="text"
              value="<?= htmlspecialchars((string) ($preference?->desired_destinations ?? '')) ?>"
              placeholder="Ej: San Andrés, Panamá, Cartagena">
            <small class="hint">Separa varios destinos con comas.</small>

            <?php if (account_preference_error($preferenceErrors, 'desired_destinations')): ?>
              <small class="error"><?= htmlspecialchars(account_preference_error($preferenceErrors, 'desired_destinations')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="budget_min">Presupuesto mínimo</label>
            <input
              id="budget_min"
              name="budget_min"
              type="number"
              min="0"
              step="1000"
              value="<?= htmlspecialchars((string) ($preference?->budget_min ?? '')) ?>"
              placeholder="Ej: 1000000">

            <?php if (account_preference_error($preferenceErrors, 'budget_min')): ?>
              <small class="error"><?= htmlspecialchars(account_preference_error($preferenceErrors, 'budget_min')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="budget_max">Presupuesto máximo</label>
            <input
              id="budget_max"
              name="budget_max"
              type="number"
              min="0"
              step="1000"
              value="<?= htmlspecialchars((string) ($preference?->budget_max ?? '')) ?>"
              placeholder="Ej: 5000000">

            <?php if (account_preference_error($preferenceErrors, 'budget_max')): ?>
              <small class="error"><?= htmlspecialchars(account_preference_error($preferenceErrors, 'budget_max')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="usual_travelers">Viajeros habituales</label>
            <input
              id="usual_travelers"
              name="usual_travelers"
              type="number"
              min="1"
              max="99"
              value="<?= htmlspecialchars((string) ($preference?->usual_travelers ?? '')) ?>"
              placeholder="Ej: 2">

            <?php if (account_preference_error($preferenceErrors, 'usual_travelers')): ?>
              <small class="error"><?= htmlspecialchars(account_preference_error($preferenceErrors, 'usual_travelers')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field full">
            <label>Notificaciones por correo</label>

            <div class="check-list">
              <label class="check-row">
                <input type="checkbox" name="notify_new_packages" value="1" <?= !empty($preference?->notify_new_packages) ? 'checked' : '' ?>>
                <span>Quiero recibir avisos cuando se publiquen paquetes nuevos.</span>
              </label>

              <label class="check-row">
                <input type="checkbox" name="notify_recommendations" value="1" <?= !empty($preference?->notify_recommendations) ? 'checked' : '' ?>>
                <span>Quiero recibir recomendaciones ocasionales según mis preferencias.</span>
              </label>
            </div>

            <small class="hint">Estas opciones están desactivadas por defecto. Puedes retirarlas cuando quieras.</small>
          </div>

          <div class="field full" style="align-items:flex-end;">
            <button class="btn primary" type="submit">Guardar preferencias</button>
          </div>
        </form>
      </section>

      <section class="card">
        <div class="card-head">
          <div>
            <h2>Mis consultas</h2>
            <p class="card-sub">Solicitudes enviadas desde tu cuenta.</p>
          </div>
        </div>

        <div class="sep"></div>

        <?php if (empty($inquiries)): ?>
          <div class="empty-state">
            Aún no tienes consultas registradas. Cuando preguntes por un paquete podrás seguir su estado aquí.
          </div>
        <?php else: ?>
          <div class="inquiry-list">
            <?php foreach ($inquiries as $inquiry): ?>
              <div class="inquiry-item">
                <div>
                  <strong><?= htmlspecialchars((string) ($inquiry->subject ?? 'Consulta')) ?></strong>

                  <?php if (!empty($inquiry->package_slug)): ?>
                    <div class="hint" style="margin-top:5px;">
                      Paquete: <?= htmlspecialchars((string) $inquiry->package_slug) ?>
                    </div>
                  <?php endif; ?>
                </div>

                <span class="badge info"><?= htmlspecialchars(account_lead_status($inquiry->status ?? 'new')) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section class="card">
        <div class="section-title-row">
          <div>
            <h2>Mis favoritos</h2>
            <p class="card-sub">Paquetes que guardaste para revisar después.</p>
          </div>

          <a href="/packagesTourist" class="btn small">Explorar paquetes</a>
        </div>

        <div class="sep"></div>

        <?php if (empty($favorites)): ?>
          <div class="empty-state">Aún no guardaste paquetes favoritos.</div>
        <?php else: ?>
          <div class="package-grid">
            <?php foreach ($favorites as $package): ?>
              <a class="package-card" href="/packagesTourist/package?slug=<?= urlencode((string) $package->slug) ?>">
                <img
                  src="<?= htmlspecialchars(account_package_asset($package->cover_image->image_path ?? null)) ?>"
                  alt="<?= htmlspecialchars((string) $package->title) ?>">

                <div class="package-card-body">
                  <strong><?= htmlspecialchars((string) $package->title) ?></strong>
                  <p>Paquete guardado para revisar después.</p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <?php if (!empty($recommendations)): ?>
        <section class="card">
          <div class="card-head">
            <div>
              <h2>Recomendados para ti</h2>
              <p class="card-sub">Basados en tus favoritos, vistas y consultas.</p>
            </div>
          </div>

          <div class="sep"></div>

          <div class="package-grid">
            <?php foreach ($recommendations as $package): ?>
              <a class="package-card" href="/packagesTourist/package?slug=<?= urlencode((string) $package->slug) ?>">
                <img
                  src="<?= htmlspecialchars(account_package_asset($package->cover_image->image_path ?? null)) ?>"
                  alt="<?= htmlspecialchars((string) $package->title) ?>">

                <div class="package-card-body">
                  <strong><?= htmlspecialchars((string) $package->title) ?></strong>
                  <p><?= htmlspecialchars((string) $package->recommendation_reason) ?></p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <section class="card">
        <div class="card-head">
          <div>
            <h2>Privacidad y recomendaciones</h2>
            <p class="card-sub">Tú decides si quieres conservar las señales usadas para ordenar paquetes.</p>
          </div>
        </div>

        <div class="sep"></div>

        <?php if (!empty($_GET['privacy']) && $_GET['privacy'] === 'cleared'): ?>
          <p class="alert ok">Tus favoritos, preferencias e historial de personalización fueron eliminados.</p>
        <?php elseif (!empty($_GET['privacy']) && $_GET['privacy'] === 'confirm'): ?>
          <p class="alert error">Marca la confirmación antes de eliminar tus datos de personalización.</p>
        <?php endif; ?>

        <p class="privacy-text">
          Usamos tus vistas de paquetes, favoritos, consultas y preferencias para ordenar recomendaciones dentro de la página.
          No usamos inteligencia artificial para este proceso. Puedes revisar nuestra
          <a href="/politica-datos" target="_blank" rel="noopener">política de tratamiento de datos</a>.
        </p>

        <p class="privacy-text">
          Al borrar tu personalización se eliminan tus favoritos, preferencias, historial de vistas y notificaciones pendientes.
          Tu cuenta y las consultas que ya enviaste a los asesores se conservan para mantener la trazabilidad de atención.
        </p>

        <form method="POST" action="/users/personalization/clear" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
          <?= \app\Core\Csrf::input(); ?>

          <label class="check-row">
            <input type="checkbox" name="confirm_clear_personalization" value="1">
            <span>Confirmo que deseo borrar mis datos de personalización.</span>
          </label>

          <button class="btn danger" type="submit">Borrar personalización</button>
        </form>
      </section>
    </main>
  </div>
</body>

</html>
