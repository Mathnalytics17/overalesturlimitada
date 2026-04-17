<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function field_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de usuario</title>
  <link rel="stylesheet" href="/styles/home.css">
  <style>
    .auth-shell {
      min-height: calc(100vh - 220px);
      padding: 56px 20px 72px;
      background:
        radial-gradient(circle at top left, rgba(225,29,72,.08), transparent 28%),
        linear-gradient(180deg, #fff 0%, #fff5f7 100%);
    }

    .auth-wrap {
      width: min(1180px, 100%);
      margin: 0 auto;
      display: grid;
      grid-template-columns: .95fr 1.05fr;
      gap: 28px;
      align-items: stretch;
    }

    .auth-panel,
    .auth-card {
      background: rgba(255,255,255,.96);
      border: 1px solid rgba(225,229,235,.95);
      border-radius: 28px;
      box-shadow: var(--shadow);
    }

    .auth-panel {
      position: relative;
      overflow: hidden;
      padding: 42px;
      background:
        linear-gradient(135deg, rgba(225,29,72,.95) 0%, rgba(190,18,60,.95) 100%),
        #be123c;
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 720px;
    }

    .auth-panel::after {
      content: "";
      position: absolute;
      inset: auto auto -50px -50px;
      width: 240px;
      height: 240px;
      border-radius: 999px;
      background: rgba(255,255,255,.08);
    }

    .auth-brand {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 34px;
    }

    .auth-brand-logo {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      font-weight: 900;
      font-size: 22px;
      color: var(--primary-dark);
      background: #fff;
      box-shadow: 0 14px 30px rgba(0,0,0,.15);
    }

    .auth-brand small {
      display: block;
      color: rgba(255,255,255,.78);
      font-size: 14px;
      margin-top: 4px;
    }

    .auth-panel h1 {
      position: relative;
      z-index: 1;
      margin: 0 0 16px;
      font-size: clamp(34px, 5vw, 56px);
      line-height: 1.02;
      font-weight: 900;
    }

    .auth-panel p {
      position: relative;
      z-index: 1;
      margin: 0;
      font-size: 17px;
      line-height: 1.8;
      color: rgba(255,255,255,.9);
      max-width: 540px;
    }

    .auth-panel-list {
      position: relative;
      z-index: 1;
      display: grid;
      gap: 14px;
      margin-top: 34px;
    }

    .auth-panel-list span {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      width: fit-content;
      padding: 11px 16px;
      border-radius: 999px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.16);
      font-size: 14px;
      font-weight: 700;
    }

    .auth-card {
      padding: 34px;
      align-self: center;
    }

    .auth-card h2 {
      margin: 0 0 8px;
      font-size: 38px;
      line-height: 1.05;
      color: var(--text);
    }

    .auth-subtitle {
      margin: 0 0 26px;
      color: var(--muted);
      line-height: 1.7;
      font-size: 16px;
    }

    .auth-alert {
      margin-bottom: 16px;
      padding: 13px 15px;
      border-radius: 16px;
      font-size: 14px;
      line-height: 1.55;
      font-weight: 700;
      border: 1px solid #fecaca;
      background: #fff1f2;
      color: #be123c;
    }

    .auth-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .auth-field label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 800;
      color: var(--text);
    }

    .auth-field input {
      width: 100%;
      min-height: 54px;
      border: 1px solid #d1d5db;
      border-radius: 16px;
      padding: 0 16px;
      font-size: 16px;
      color: var(--text);
      background: #fff;
      outline: none;
      transition: border-color .2s ease, box-shadow .2s ease;
    }

    .auth-field input:focus {
      border-color: #f43f5e;
      box-shadow: 0 0 0 4px rgba(225,29,72,.12);
    }

    .auth-error {
      display: block;
      margin-top: 7px;
      color: #be123c;
      font-size: 13px;
      font-weight: 700;
    }

    .auth-check {
      grid-column: 1 / -1;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 2px 0 2px;
    }

    .auth-check input {
      width: 18px;
      height: 18px;
      margin-top: 2px;
      accent-color: var(--primary);
    }

    .auth-check label {
      margin: 0;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.6;
    }

    .auth-actions {
      grid-column: 1 / -1;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      margin-top: 4px;
    }

    .auth-link {
      text-decoration: none;
      color: var(--primary-dark);
      font-weight: 800;
    }

    .auth-link:hover {
      text-decoration: underline;
    }

    .auth-buttons {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .auth-submit {
      border: 0;
      cursor: pointer;
    }

    @media (max-width: 980px) {
      .auth-wrap {
        grid-template-columns: 1fr;
      }

      .auth-panel {
        min-height: auto;
        padding: 34px 28px;
      }
    }

    @media (max-width: 720px) {
      .auth-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 640px) {
      .auth-shell {
        padding: 28px 16px 44px;
      }

      .auth-card,
      .auth-panel {
        padding: 24px 20px;
        border-radius: 22px;
      }

      .auth-card h2 {
        font-size: 30px;
      }

      .auth-buttons {
        width: 100%;
      }

      .auth-buttons .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <section class="auth-shell">
    <div class="auth-wrap">
      <aside class="auth-panel">
        <div>
          <div class="auth-brand">
            <div class="auth-brand-logo">OA</div>
            <div>
              <div style="font-weight:900; font-size:22px;">Over Alestur</div>
              <small>Expertos en viajes</small>
            </div>
          </div>

          <h1>Crea tu cuenta</h1>
          <p>
            Regístrate para consultar tus servicios, guardar tus datos y tener una experiencia más ágil con Over Alestur.
          </p>

          <div class="auth-panel-list">
            <span>Registro rápido y seguro</span>
            <span>Acceso a tus solicitudes y reservas</span>
            <span>Promociones y novedades si así lo deseas</span>
          </div>
        </div>

        <p style="font-size:14px; color:rgba(255,255,255,.75); margin-top:28px;">
          Al crear tu cuenta podrás seguir tu actividad y recibir atención más personalizada.
        </p>
      </aside>

      <div class="auth-card">
        <h2>Registro de usuario</h2>
        <p class="auth-subtitle">Completa la información para crear tu cuenta.</p>

        <?php if ($message): ?>
          <div class="auth-alert">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <form class="auth-grid" method="POST" action="/users/register" novalidate>
          <?= Csrf::input(); ?>

          <div class="auth-field">
            <label for="first_name">Nombres</label>
            <input
              type="text"
              id="first_name"
              name="first_name"
              placeholder="Juan"
              value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'first_name')): ?>
              <small class="auth-error"><?= htmlspecialchars(field_error($errors, 'first_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-field">
            <label for="last_name">Apellidos</label>
            <input
              type="text"
              id="last_name"
              name="last_name"
              placeholder="Pérez"
              value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'last_name')): ?>
              <small class="auth-error"><?= htmlspecialchars(field_error($errors, 'last_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-field">
            <label for="phone">Teléfono</label>
            <input
              type="text"
              id="phone"
              name="phone"
              placeholder="+57 3000000000"
              value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'phone')): ?>
              <small class="auth-error"><?= htmlspecialchars(field_error($errors, 'phone')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-field">
            <label for="whatsapp">WhatsApp</label>
            <input
              type="text"
              id="whatsapp"
              name="whatsapp"
              placeholder="+57 3000000000"
              value="<?= htmlspecialchars($old['whatsapp'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'whatsapp')): ?>
              <small class="auth-error"><?= htmlspecialchars(field_error($errors, 'whatsapp')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-field" style="grid-column:1 / -1;">
            <label for="email">Correo</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="correo@empresa.com"
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            >
            <?php if (field_error($errors, 'email')): ?>
              <small class="auth-error"><?= htmlspecialchars(field_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-field">
            <label for="password">Contraseña</label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="********"
            >
            <?php if (field_error($errors, 'password')): ?>
              <small class="auth-error"><?= htmlspecialchars(field_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              placeholder="********"
            >
            <?php if (field_error($errors, 'password_confirmation')): ?>
              <small class="auth-error"><?= htmlspecialchars(field_error($errors, 'password_confirmation')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-check">
            <input
              type="checkbox"
              id="accepts_marketing"
              name="accepts_marketing"
              value="1"
              <?= !empty($old['accepts_marketing']) ? 'checked' : '' ?>
            >
            <label for="accepts_marketing">
              Acepto recibir información y promociones de Over Alestur.
            </label>
          </div>

          <div style="grid-column:1 / -1;">
            <?= turnstile_widget_html(); ?>
          </div>

          <div class="auth-actions">
            <div style="font-size:14px; color:var(--muted);">
              ¿Ya tienes cuenta?
              <a href="/users/login" class="auth-link">Inicia sesión</a>
            </div>

            <div class="auth-buttons">
              <a href="/users/login" class="btn btn-light">Cancelar</a>
              <button type="submit" class="btn btn-primary auth-submit">Crear cuenta</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</body>
</html>