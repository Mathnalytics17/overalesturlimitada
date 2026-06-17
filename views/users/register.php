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

<style>
  :root {
    --alestur-red: #e4073d;
    --alestur-red-dark: #c90034;
    --alestur-text: #111827;
    --alestur-muted: #64748b;
    --alestur-border: #dbe3ef;
    --alestur-bg: #f8fafc;
  }

  body {
    background:
      radial-gradient(circle at 82% 12%, rgba(228, 7, 61, .10), transparent 28%),
      linear-gradient(90deg, var(--alestur-red) 0%, var(--alestur-red-dark) 46%, #f8fafc 46%, #ffffff 100%);
  }

  .register-page {
    min-height: auto;
    padding: 54px 32px 64px;
    display: grid;
    grid-template-columns: minmax(320px, 0.9fr) minmax(420px, 0.95fr);
    gap: 64px;
    align-items: center;
    max-width: 1780px;
    margin: 0 auto;
  }

  .register-hero {
    color: #fff;
    position: relative;
    overflow: hidden;
    padding: 40px 24px 40px 24px;
  }

  .register-hero::before {
    content: "";
    position: absolute;
    width: 460px;
    height: 460px;
    border-radius: 999px;
    left: -220px;
    bottom: -230px;
    background: rgba(255, 255, 255, .13);
  }

  .register-hero::after {
    content: "";
    position: absolute;
    width: 220px;
    height: 220px;
    border-radius: 999px;
    right: -78px;
    top: 36px;
    border: 36px solid rgba(255, 255, 255, .14);
  }

  .register-badge {
    display: inline-flex;
    align-items: center;
    padding: 10px 22px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .16);
    font-weight: 800;
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
  }

  .register-hero h1 {
    position: relative;
    z-index: 1;
    margin: 0;
    max-width: 720px;
    font-size: clamp(48px, 5.2vw, 88px);
    line-height: .98;
    letter-spacing: -0.055em;
    font-weight: 900;
  }

  .register-hero p {
    position: relative;
    z-index: 1;
    max-width: 650px;
    margin: 28px 0 0;
    font-size: clamp(18px, 1.45vw, 28px);
    line-height: 1.6;
    font-weight: 500;
  }

  .register-benefits {
    position: relative;
    z-index: 1;
    list-style: none;
    padding: 0;
    margin: 48px 0 0;
    display: grid;
    gap: 22px;
  }

  .register-benefits li {
    display: flex;
    align-items: center;
    gap: 18px;
    font-size: clamp(18px, 1.35vw, 26px);
    line-height: 1.3;
    font-weight: 900;
  }

  .register-check {
    flex: 0 0 auto;
    width: 52px;
    height: 52px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .18);
    display: grid;
    place-items: center;
    font-size: 34px;
    line-height: 1;
    font-weight: 900;
  }

  .register-panel {
    width: 100%;
    max-width: 760px;
    justify-self: center;
    background: rgba(255, 255, 255, .94);
    border: 1px solid var(--alestur-border);
    border-radius: 30px;
    box-shadow: 0 34px 90px rgba(15, 23, 42, .16);
    padding: 42px 44px 34px;
  }

  .register-panel h2 {
    margin: 0;
    color: var(--alestur-text);
    font-size: clamp(34px, 2.6vw, 52px);
    line-height: 1.04;
    letter-spacing: -0.05em;
    font-weight: 850;
  }

  .register-panel .subtitle {
    margin: 12px 0 28px;
    color: var(--alestur-muted);
    font-size: 17px;
    line-height: 1.45;
  }

  .register-alert {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: #fff1f2;
    border: 1px solid #fecdd3;
    color: #be123c;
    font-weight: 800;
  }

  .register-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 20px;
  }

  .register-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .register-field.full {
    grid-column: 1 / -1;
  }

  .register-field label {
    color: #334155;
    font-size: 15px;
    font-weight: 900;
  }

  .register-field input {
    width: 100%;
    height: 54px;
    border: 1px solid #cbd7e7;
    border-radius: 18px;
    padding: 0 18px;
    color: #0f172a;
    font-size: 16px;
    outline: none;
    background: #fff;
    box-sizing: border-box;
    transition: border-color .2s ease, box-shadow .2s ease;
  }

  .register-field input:focus {
    border-color: var(--alestur-red);
    box-shadow: 0 0 0 4px rgba(228, 7, 61, .10);
  }

  .register-field input::placeholder {
    color: #94a3b8;
  }

  .register-error {
    color: #b91c1c;
    font-size: 13px;
    font-weight: 800;
  }

  .register-checkbox {
    grid-column: 1 / -1;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 20px;
    margin-top: 4px;
  }

  .register-checkbox input {
    width: 26px;
    height: 26px;
    margin-top: 2px;
    flex: 0 0 auto;
  }

  .register-checkbox label {
    margin: 0;
    color: #334155;
    font-size: 17px;
    font-weight: 900;
    line-height: 1.45;
  }

  .turnstile-box {
    grid-column: 1 / -1;
    margin-top: 4px;
  }

  .register-actions {
    grid-column: 1 / -1;
    border-top: 1px solid #e2e8f0;
    margin-top: 14px;
    padding-top: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .register-login-link {
    color: #64748b;
    font-size: 16px;
    font-weight: 900;
  }

  .register-login-link a {
    color: #111827;
    text-decoration: none;
  }

  .register-login-link a:hover {
    color: var(--alestur-red);
  }

  .register-buttons {
    display: flex;
    gap: 12px;
    align-items: center;
  }

  .btn {
    min-height: 52px;
    border-radius: 999px;
    border: 1px solid #cbd7e7;
    padding: 0 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #111827;
    font-weight: 900;
    text-decoration: none;
    cursor: pointer;
  }

  .btn.primary {
    border-color: var(--alestur-red);
    background: var(--alestur-red);
    color: #fff;
    box-shadow: 0 18px 36px rgba(228, 7, 61, .22);
  }

  .btn.primary:hover {
    background: var(--alestur-red-dark);
  }

  @media (max-width: 1100px) {
    body {
      background: #f8fafc;
    }

    .register-page {
      grid-template-columns: 1fr;
      gap: 28px;
      padding: 28px 18px 44px;
    }

    .register-hero {
      border-radius: 28px;
      background: linear-gradient(135deg, var(--alestur-red), var(--alestur-red-dark));
      padding: 34px 28px;
    }

    .register-panel {
      max-width: none;
      padding: 30px 24px;
    }
  }

  @media (max-width: 680px) {
    .register-form {
      grid-template-columns: 1fr;
    }

    .register-actions {
      align-items: stretch;
    }

    .register-buttons,
    .register-buttons .btn {
      width: 100%;
    }

    .register-buttons {
      flex-direction: column-reverse;
    }

    .register-login-link {
      width: 100%;
      text-align: center;
    }
  }
</style>

<section class="register-page">
  <aside class="register-hero">
    <div class="register-badge">Alestur Ltda.</div>

    <h1>Crea tu cuenta y planea tu próximo viaje.</h1>

    <p>
      Guarda tus preferencias, consulta paquetes, conserva tus favoritos
      y recibe una atención más rápida por nuestros canales.
    </p>

    <ul class="register-benefits">
      <li>
        <span class="register-check">✓</span>
        <span>Acceso a tus consultas y favoritos.</span>
      </li>
      <li>
        <span class="register-check">✓</span>
        <span>Preferencias de viaje personalizadas.</span>
      </li>
      <li>
        <span class="register-check">✓</span>
        <span>Contacto directo con el equipo Alestur.</span>
      </li>
    </ul>
  </aside>

  <main class="register-panel">
    <h2>Registro de usuario</h2>
    <p class="subtitle">
      Completa tus datos para crear tu cuenta y gestionar tus solicitudes desde la página.
    </p>

    <?php if ($message): ?>
      <div class="register-alert">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form class="register-form" method="POST" action="/users/register">
      <?= Csrf::input(); ?>

      <div class="register-field">
        <label for="first_name">Nombres</label>
        <input
          type="text"
          id="first_name"
          name="first_name"
          placeholder="Juan"
          value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
        <?php if (field_error($errors, 'first_name')): ?>
          <small class="register-error"><?= htmlspecialchars(field_error($errors, 'first_name')) ?></small>
        <?php endif; ?>
      </div>

      <div class="register-field">
        <label for="last_name">Apellidos</label>
        <input
          type="text"
          id="last_name"
          name="last_name"
          placeholder="Pérez"
          value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
        <?php if (field_error($errors, 'last_name')): ?>
          <small class="register-error"><?= htmlspecialchars(field_error($errors, 'last_name')) ?></small>
        <?php endif; ?>
      </div>

      <div class="register-field">
        <label for="phone">Teléfono</label>
        <input
          type="text"
          id="phone"
          name="phone"
          placeholder="+57 3000000000"
          value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
        <?php if (field_error($errors, 'phone')): ?>
          <small class="register-error"><?= htmlspecialchars(field_error($errors, 'phone')) ?></small>
        <?php endif; ?>
      </div>

      <div class="register-field">
        <label for="whatsapp">WhatsApp</label>
        <input
          type="text"
          id="whatsapp"
          name="whatsapp"
          placeholder="+57 3000000000"
          value="<?= htmlspecialchars($old['whatsapp'] ?? '') ?>">
        <?php if (field_error($errors, 'whatsapp')): ?>
          <small class="register-error"><?= htmlspecialchars(field_error($errors, 'whatsapp')) ?></small>
        <?php endif; ?>
      </div>

      <div class="register-field full">
        <label for="email">Correo electrónico</label>
        <input
          type="email"
          id="email"
          name="email"
          placeholder="correo@ejemplo.com"
          value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        <?php if (field_error($errors, 'email')): ?>
          <small class="register-error"><?= htmlspecialchars(field_error($errors, 'email')) ?></small>
        <?php endif; ?>
        <small style="color:#64748b;font-weight:700;line-height:1.35;">Te enviaremos a este correo un enlace para activar tu cuenta.</small>
      </div>

      <div class="register-field">
        <label for="password">Contraseña</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Mínimo 8 caracteres">
        <?php if (field_error($errors, 'password')): ?>
          <small class="register-error"><?= htmlspecialchars(field_error($errors, 'password')) ?></small>
        <?php endif; ?>
      </div>

      <div class="register-field">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input
          type="password"
          id="password_confirmation"
          name="password_confirmation"
          placeholder="Repite tu contraseña">
        <?php if (field_error($errors, 'password_confirmation')): ?>
          <small class="register-error"><?= htmlspecialchars(field_error($errors, 'password_confirmation')) ?></small>
        <?php endif; ?>
      </div>

      <div class="register-checkbox">
        <input
          type="checkbox"
          id="accepts_marketing"
          name="accepts_marketing"
          value="1"
          <?= !empty($old['accepts_marketing']) ? 'checked' : '' ?>>
        <label for="accepts_marketing">
          Acepto recibir información, novedades y promociones de Alestur.
        </label>
      </div>

      <div class="turnstile-box">
        <?= turnstile_widget_html(); ?>
      </div>

      <div class="register-actions">
        <div class="register-login-link">
          ¿Ya tienes cuenta? <a href="/users/login">Inicia sesión</a>
        </div>

        <div class="register-buttons">
          <a href="/" class="btn">Cancelar</a>
          <button type="submit" class="btn primary">Crear cuenta y verificar correo</button>
        </div>
      </div>
    </form>
  </main>
</section>