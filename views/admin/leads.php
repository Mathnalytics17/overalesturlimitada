<?php
  $page_title = "Clientes potenciales";
  $page_subtitle = "Contactos y acciones rápidas";
  $active = "leads";
 
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Listado</h2>
      <div class="card-sub">WhatsApp / Email / Iniciar proceso</div>
    </div>
    <div class="card-actions">
      <button class="btn" data-open="#modalFilters">Filtros</button>
      <button class="btn primary" data-open="#modalEmail">Enviar correo</button>
    </div>
  </div>

  <div class="sep"></div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nombre</th><th>Apellido</th><th>Fecha</th><th>Teléfono</th><th>Motivo</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>John</td><td>Doe</td><td>2026-03-05</td><td>+57 322 234 3233</td><td>Contacto</td>
          <td>
            <div class="row-actions">
              <button class="chip" title="WhatsApp">💬</button>
              <button class="chip" title="Email" data-open="#modalEmail">✉️</button>
              <button class="chip" title="Iniciar proceso">✅</button>
            </div>
          </td>
        </tr>
        <tr>
          <td>Maria</td><td>Lopez</td><td>2026-03-03</td><td>+57 300 000 0000</td><td>Tiquete</td>
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

<!-- Modal Email reutilizable -->
<div class="backdrop" id="modalEmail">
  <div class="modal">
    <div class="modal-head">
      <h3>Enviar correo</h3>
      <button class="icon-btn" data-close>✕</button>
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

<!-- Modal filtros (demo) -->
<div class="backdrop" id="modalFilters">
  <div class="modal">
    <div class="modal-head">
      <h3>Filtros</h3>
      <button class="icon-btn" data-close>✕</button>
    </div>
    <div class="modal-body">
      <div class="form">
        <div class="field">
          <label>Motivo</label>
          <select>
            <option>Todos</option>
            <option>Contacto</option>
            <option>Tiquete</option>
            <option>Visa / tramites</option>
          </select>
        </div>
        <div class="field">
          <label>Status</label>
          <select>
            <option>Todos</option>
            <option>Nuevo</option>
            <option>Pendiente</option>
            <option>Cerrado</option>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn" data-close>Cancelar</button>
      <button class="btn primary">Aplicar</button>
    </div>
  </div>
</div>

