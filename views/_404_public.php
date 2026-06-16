<?php $title = $title ?? 'Página no encontrada | Over Alestur'; ?>
<section class="public-404-page" aria-labelledby="public404-title">
  <div class="public-404-shell">
    <div class="public-404-visual" aria-hidden="true">
      <span class="public-404-code">404</span>
      <span class="public-404-dot public-404-dot-one"></span>
      <span class="public-404-dot public-404-dot-two"></span>
      <span class="public-404-dot public-404-dot-three"></span>
    </div>

    <div class="public-404-content">
      <p class="public-404-kicker">Over Alestur</p>
      <h1 id="public404-title">No encontramos esta página</h1>
      <p>
        El enlace que abriste no existe, cambió de ubicación o ya no está disponible.
        Puedes volver al inicio, revisar los paquetes disponibles o regresar a la página anterior.
      </p>

      <div class="public-404-actions">
        <a class="public-404-btn public-404-btn-primary" href="/">Ir al inicio</a>
        <a class="public-404-btn public-404-btn-secondary" href="/packagesTourist">Ver paquetes</a>
        <button class="public-404-btn public-404-btn-ghost" type="button" onclick="history.back()">Regresar</button>
      </div>
    </div>
  </div>
</section>

<style>
  .public-404-page {
    min-height: 68vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 56px 18px;
    background:
      radial-gradient(circle at 15% 20%, rgba(190, 26, 49, .10), transparent 30%),
      radial-gradient(circle at 90% 10%, rgba(31, 164, 207, .12), transparent 30%),
      #f8fafc;
  }

  .public-404-shell {
    width: min(100%, 980px);
    display: grid;
    grid-template-columns: .9fr 1.1fr;
    gap: 34px;
    align-items: center;
    padding: 42px;
    border-radius: 34px;
    border: 1px solid #e7edf3;
    background: rgba(255, 255, 255, .92);
    box-shadow: 0 28px 70px rgba(15, 23, 42, .10);
    overflow: hidden;
  }

  .public-404-visual {
    position: relative;
    min-height: 300px;
    display: grid;
    place-items: center;
    border-radius: 30px;
    background: linear-gradient(135deg, #0f172a, #193b65 58%, #be1a31);
    color: #fff;
  }

  .public-404-code {
    position: relative;
    z-index: 2;
    font-size: clamp(74px, 12vw, 132px);
    font-weight: 950;
    line-height: 1;
    letter-spacing: -6px;
    text-shadow: 0 14px 36px rgba(0,0,0,.35);
  }

  .public-404-dot {
    position: absolute;
    border-radius: 999px;
    background: rgba(255, 255, 255, .22);
  }

  .public-404-dot-one { width: 120px; height: 120px; top: 28px; left: 28px; }
  .public-404-dot-two { width: 74px; height: 74px; bottom: 42px; right: 40px; }
  .public-404-dot-three { width: 42px; height: 42px; top: 58px; right: 84px; }

  .public-404-kicker {
    margin: 0 0 10px;
    color: #be1a31;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: .12em;
    text-transform: uppercase;
  }

  .public-404-content h1 {
    margin: 0 0 14px;
    color: #0f172a;
    font-size: clamp(34px, 5vw, 56px);
    line-height: 1.03;
    font-weight: 950;
  }

  .public-404-content p:not(.public-404-kicker) {
    margin: 0;
    max-width: 560px;
    color: #64748b;
    font-size: 18px;
    line-height: 1.65;
  }

  .public-404-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 28px;
  }

  .public-404-btn {
    min-height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 22px;
    border-radius: 15px;
    font-weight: 900;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    font-family: inherit;
    font-size: 15px;
  }

  .public-404-btn-primary {
    background: #be1a31;
    color: #fff;
  }

  .public-404-btn-secondary {
    background: #0f172a;
    color: #fff;
  }

  .public-404-btn-ghost {
    background: #fff;
    color: #be1a31;
    border-color: #f1c5cd;
  }

  @media (max-width: 820px) {
    .public-404-shell {
      grid-template-columns: 1fr;
      padding: 24px;
    }

    .public-404-visual {
      min-height: 220px;
    }

    .public-404-actions {
      flex-direction: column;
    }

    .public-404-btn {
      width: 100%;
    }
  }
</style>
