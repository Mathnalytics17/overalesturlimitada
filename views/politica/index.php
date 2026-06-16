<style>
  .policy-page {
    min-height: calc(100vh - 120px);
    background:
      radial-gradient(circle at top right, rgba(225, 0, 55, 0.08), transparent 32%),
      linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    padding: 56px 20px 70px;
  }

  .policy-wrapper {
    width: min(1180px, 100%);
    margin: 0 auto;
  }

  .policy-header {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 24px;
    align-items: end;
    margin-bottom: 24px;
  }

  .policy-kicker {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(225, 0, 55, 0.10);
    color: #e10037;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 14px;
  }

  .policy-header h1 {
    margin: 0;
    color: #111827;
    font-size: clamp(34px, 5vw, 58px);
    line-height: 0.95;
    letter-spacing: -0.05em;
    font-weight: 900;
  }

  .policy-header p {
    margin: 16px 0 0;
    color: #64748b;
    font-size: 18px;
    line-height: 1.6;
    max-width: 720px;
  }

  .policy-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }

  .policy-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 0 22px;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 800;
    font-size: 15px;
    border: 1px solid #d8e0ee;
    color: #111827;
    background: #ffffff;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
  }

  .policy-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.10);
    border-color: #cbd5e1;
  }

  .policy-btn.primary {
    border-color: #e10037;
    background: #e10037;
    color: #ffffff;
    box-shadow: 0 18px 34px rgba(225, 0, 55, 0.22);
  }

  .policy-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 30px;
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.10);
    overflow: hidden;
  }

  .policy-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 18px 22px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
  }

  .policy-card-title {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .policy-card-title strong {
    color: #111827;
    font-size: 17px;
  }

  .policy-card-title span {
    color: #64748b;
    font-size: 14px;
  }

  .pdf-container {
    width: 100%;
    height: min(72vh, 760px);
    background: #f1f5f9;
  }

  .pdf-container iframe {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
    background: #ffffff;
  }

  .policy-note {
    margin-top: 18px;
    padding: 18px 20px;
    border-radius: 22px;
    background: #fff7f9;
    border: 1px solid rgba(225, 0, 55, 0.16);
    color: #475569;
    line-height: 1.6;
  }

  .policy-note strong {
    color: #111827;
  }

  @media (max-width: 900px) {
    .policy-page {
      padding: 36px 14px 50px;
    }

    .policy-header {
      grid-template-columns: 1fr;
      align-items: start;
    }

    .policy-actions {
      justify-content: flex-start;
    }

    .policy-card {
      border-radius: 24px;
    }

    .policy-card-top {
      flex-direction: column;
      align-items: flex-start;
    }

    .pdf-container {
      height: 68vh;
    }
  }

  @media (max-width: 560px) {
    .policy-header h1 {
      font-size: 38px;
    }

    .policy-header p {
      font-size: 16px;
    }

    .policy-btn {
      width: 100%;
    }

    .pdf-container {
      height: 62vh;
    }
  }
</style>

<section class="policy-page">
  <div class="policy-wrapper">

    <div class="policy-header">
      <div>
        <div class="policy-kicker">Documento legal</div>

        <h1>Política de Tratamiento de Datos</h1>

        <p>
          Consulta nuestra política de tratamiento de datos personales.
          Puedes leer el documento directamente desde esta página o abrirlo en una nueva pestaña.
        </p>
      </div>

      <div class="policy-actions">
        <a
          class="policy-btn"
          href="/files/POLÍTICADETRATAMIENTODEDATOSPERSONALES.pdf"
          target="_blank"
          rel="noopener">
          Abrir en nueva pestaña
        </a>

        <a
          class="policy-btn primary"
          href="/files/POLÍTICADETRATAMIENTODEDATOSPERSONALES.pdf"
          download>
          Descargar PDF
        </a>
      </div>
    </div>

    <div class="policy-card">
      <div class="policy-card-top">
        <div class="policy-card-title">
          <strong>Vista previa del documento</strong>
          <span>Política de Tratamiento de Datos Personales de Alestur Ltda.</span>
        </div>

        <a
          class="policy-btn"
          href="/files/POLÍTICADETRATAMIENTODEDATOSPERSONALES.pdf"
          target="_blank"
          rel="noopener">
          Ver completo
        </a>
      </div>

      <div class="pdf-container">
        <iframe
          src="/files/POLÍTICADETRATAMIENTODEDATOSPERSONALES.pdf"
          title="Política de Tratamiento de Datos Personales"></iframe>
      </div>
    </div>

    <div class="policy-note">
      <strong>Importante:</strong>
      al continuar usando nuestros canales de atención, aceptas que Alestur Ltda. trate tus datos personales conforme a esta política.
    </div>

  </div>
</section>