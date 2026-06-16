<?php
$page_title = $page_title ?? 'Página no encontrada';
$page_subtitle = $page_subtitle ?? 'La ruta solicitada no existe dentro del panel administrativo.';
?>
<section class="admin-404-page" aria-labelledby="admin404-title">
  <div class="admin-404-card">
    <div class="admin-404-badge" aria-hidden="true">404</div>

    <div class="admin-404-copy">
      <p class="admin-404-kicker">Panel administrativo</p>
      <h2 id="admin404-title">Página no encontrada</h2>
      <p>
        La sección que intentas abrir no existe, fue movida o no está disponible para tu usuario.
        Usa el menú lateral para volver a un módulo válido o regresa a la pantalla anterior.
      </p>
    </div>

    <div class="admin-404-actions">
      <a class="admin-404-btn admin-404-btn-primary" href="/admin">Ir al dashboard</a>
      <button class="admin-404-btn admin-404-btn-secondary" type="button" onclick="history.back()">Regresar</button>
    </div>
  </div>

  <div class="admin-404-help">
    <h3>Qué puedes revisar</h3>
    <ul>
      <li>Confirma que la dirección escrita sea correcta.</li>
      <li>Ingresa desde el menú lateral si el módulo existe.</li>
      <li>Si el acceso debería existir, reporta la ruta al administrador del sistema.</li>
    </ul>
  </div>
</section>

<style>
  .admin-404-page {
    min-height: calc(100vh - 160px);
    display: grid;
    place-items: center;
    gap: 18px;
    padding: 34px 16px;
  }

  .admin-404-card,
  .admin-404-help {
    width: min(100%, 760px);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 28px;
    background: var(--card, #fff);
    box-shadow: var(--shadow2, 0 8px 22px rgba(15, 23, 42, .08));
  }

  .admin-404-card {
    padding: 38px;
    text-align: center;
  }

  .admin-404-badge {
    width: 104px;
    height: 104px;
    margin: 0 auto 22px;
    display: grid;
    place-items: center;
    border-radius: 30px;
    color: #fff;
    font-size: 40px;
    font-weight: 900;
    letter-spacing: -1px;
    background: linear-gradient(135deg, var(--primary, #1FA4CF), var(--primary-2, #1688ad));
    box-shadow: 0 18px 34px rgba(31, 164, 207, .25);
  }

  .admin-404-kicker {
    margin: 0 0 8px;
    color: var(--primary-2, #1688ad);
    font-size: 13px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
  }

  .admin-404-copy h2 {
    margin: 0 0 12px;
    color: var(--text, #111827);
    font-size: clamp(30px, 4vw, 44px);
    line-height: 1.05;
    font-weight: 950;
  }

  .admin-404-copy p:not(.admin-404-kicker) {
    max-width: 560px;
    margin: 0 auto;
    color: var(--muted, #6b7280);
    font-size: 17px;
    line-height: 1.6;
  }

  .admin-404-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 28px;
  }

  .admin-404-btn {
    min-height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 24px;
    border-radius: 15px;
    font-weight: 900;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    font-family: inherit;
    font-size: 15px;
  }

  .admin-404-btn-primary {
    background: var(--primary, #1FA4CF);
    color: #fff;
  }

  .admin-404-btn-secondary {
    background: #fff;
    color: #4b148c;
    border-color: #d8e1ea;
  }

  .admin-404-help {
    padding: 24px 28px;
  }

  .admin-404-help h3 {
    margin: 0 0 12px;
    color: var(--text, #111827);
    font-size: 18px;
    font-weight: 900;
  }

  .admin-404-help ul {
    margin: 0;
    padding-left: 20px;
    color: var(--muted, #6b7280);
    line-height: 1.7;
  }

  @media (max-width: 640px) {
    .admin-404-card { padding: 30px 20px; }
    .admin-404-btn { width: 100%; }
    .admin-404-help { padding: 22px 20px; }
  }
</style>
