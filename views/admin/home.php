<?php
  $page_title = "Panel de administrador";
  $page_subtitle = "Resumen general de Alestur LTDA";
  $active = "dashboard";

?>

<div class="grid kpis">
  <div class="card kpi">
    <div class="card-head">
      <h2>Total leads</h2>
      <span class="badge info">Hoy</span>
    </div>
    <div class="kpi-value">1,248</div>
    <div class="kpi-foot">+4.2% vs ayer</div>
  </div>

  <div class="card kpi">
    <div class="card-head">
      <h2>Ventas cerradas</h2>
      <span class="badge ok">OK</span>
    </div>
    <div class="kpi-value">86</div>
    <div class="kpi-foot">+2.1% semanal</div>
  </div>

  <div class="card kpi">
    <div class="card-head">
      <h2>Paquetes más pedidos</h2>
      <span class="badge neutral">Mes</span>
    </div>
    <div class="kpi-value">12</div>
    <div class="kpi-foot">Top categorías</div>
  </div>

  <div class="card kpi">
    <div class="card-head">
      <h2>Status de ventas</h2>
      <span class="badge warn">Atención</span>
    </div>
    <div class="kpi-value">17</div>
    <div class="kpi-foot">3 en riesgo</div>
  </div>
</div>

<div class="grid two-col" style="margin-top:16px;">
  <div class="card">
    <div class="card-head">
      <div>
        <h2>Contactos potenciales recientes</h2>
        <div class="card-sub">Últimos registros y acciones rápidas</div>
      </div>
      <div class="card-actions">
        <a class="btn small" href="leads.php">Ver leads</a>
        <button class="btn small primary" data-open="#modalNewLead">+ Nuevo</button>
      </div>
    </div>

    <div class="sep"></div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nombre</th><th>Contacto</th><th>Motivo</th><th>Status</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Camila R.</td>
            <td>
              <div class="t-muted">+57 300 000 0000</div>
              camila@email.com
            </td>
            <td>Visa / tramites</td>
            <td><span class="badge info">Nuevo</span></td>
            <td>
              <div class="row-actions">
                <button class="chip" title="WhatsApp">💬</button>
                <button class="chip" title="Email" data-open="#modalEmail">✉️</button>
                <button class="chip" title="Iniciar proceso">✅</button>
              </div>
            </td>
          </tr>
          <tr>
            <td>Juan P.</td>
            <td>
              <div class="t-muted">+57 311 111 1111</div>
              juan@email.com
            </td>
            <td>Tiquete</td>
            <td><span class="badge warn">Pendiente</span></td>
            <td>
              <div class="row-actions">
                <button class="chip">💬</button>
                <button class="chip" data-open="#modalEmail">✉️</button>
                <button class="chip">✅</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <span>page 1-13</span>
      <div class="pager">
        <button aria-label="Anterior">←</button>
        <button aria-label="Siguiente">→</button>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head">
      <div>
        <h2>Acciones rápidas</h2>
        <div class="card-sub">Operaciones frecuentes</div>
      </div>
    </div>

    <div class="sep"></div>

    <div class="grid" style="grid-template-columns:1fr; gap:10px;">
      <a class="btn primary" href="users.php">+ Crear usuario</a>
      <a class="btn" href="packages.php">+ Crear paquete</a>
      <button class="btn" data-open="#modalEmail">Enviar correo</button>
      <button class="btn danger">Bloquear lead</button>
    </div>

    <div class="sep"></div>

    <div class="grid" style="grid-template-columns:1fr 1fr; gap:10px;">
      <div class="card" style="box-shadow:none;">
        <div class="t-muted">Servidor</div>
        <div style="font-weight:900; font-size:18px;">OK</div>
      </div>
      <div class="card" style="box-shadow:none;">
        <div class="t-muted">API</div>
        <div style="font-weight:900; font-size:18px;">200ms</div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Email -->
<div class="backdrop" id="modalEmail">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-head">
      <h3>Enviar correo</h3>
      <button class="icon-btn" data-close aria-label="Cerrar">✕</button>
    </div>
    <div class="modal-body">
      <div class="field" style="margin-bottom:12px;">
        <label>Asunto</label>
        <input placeholder="Asunto del correo" />
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>Mensaje</label>
        <textarea placeholder="Escribe el mensaje..."></textarea>
      </div>
      <div class="field">
        <label>Adjunto (máx 10 MB)</label>
        <input type="file" />
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn" data-close>Cancelar</button>
      <button class="btn primary">Enviar</button>
    </div>
  </div>
</div>

<!-- Modal: Nuevo Lead (demo) -->
<div class="backdrop" id="modalNewLead">
  <div class="modal">
    <div class="modal-head">
      <h3>Nuevo contacto potencial</h3>
      <button class="icon-btn" data-close>✕</button>
    </div>
    <div class="modal-body">
      <div class="form">
        <div class="field">
          <label>Nombre</label>
          <input />
        </div>
        <div class="field">
          <label>Apellido</label>
          <input />
        </div>
        <div class="field">
          <label>Teléfono</label>
          <input />
        </div>
        <div class="field">
          <label>Motivo</label>
          <select>
            <option>Tiquete</option>
            <option>Visa / tramites</option>
            <option>Escuela de idiomas</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn" data-close>Cancelar</button>
      <button class="btn primary">Guardar</button>
    </div>
  </div>
</div>

