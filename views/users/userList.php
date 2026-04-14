<?php
  $page_title = "Usuarios";
  $page_subtitle = "Administración y roles";
  $active = "users";
  require __DIR__ . "../../../shared/partials/layout_top.php";
  require __DIR__ . "../../../shared/partials/layout_side.php";
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Listado de usuarios</h2>
      <div class="card-sub">Editar, eliminar y crear</div>
    </div>
    <button class="btn primary" data-open="#modalCreateUser">+ Crear usuario</button>
  </div>

  <div class="sep"></div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nombre</th><th>Apellido</th><th>Correo</th><th>Número</th><th>Rol</th><th>Status</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>John</td><td>Doe</td><td>correo@overalestur.com.co</td><td>+57 322 234 3233</td>
          <td>Administrador</td>
          <td><span class="badge ok">Activo</span></td>
          <td>
            <div class="row-actions">
              <button class="chip" title="Editar" data-open="#modalCreateUser">✏️</button>
              <button class="chip" title="Eliminar">🗑️</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <span>page 1-13</span>
    <div class="pager">
      <button>←</button><button>→</button>
    </div>
  </div>
</div>

<!-- Modal crear/editar usuario -->
<div class="backdrop" id="modalCreateUser">
  <div class="modal">
    <div class="modal-head">
      <h3>Crear usuario</h3>
      <button class="icon-btn" data-close>✕</button>
    </div>
    <div class="modal-body">
      <div class="form">
        <div class="field">
          <label>Nombre</label>
          <input placeholder="Nombre" />
        </div>
        <div class="field">
          <label>Apellido</label>
          <input placeholder="Apellido" />
        </div>
        <div class="field">
          <label>Correo</label>
          <input placeholder="correo@dominio.com" />
        </div>
        <div class="field">
          <label>Teléfono</label>
          <input placeholder="+57..." />
        </div>
        <div class="field">
          <label>Rol</label>
          <select>
            <option>administrador</option>
            <option>asesor</option>
          </select>
        </div>
        <div class="field">
          <label>Foto de perfil</label>
          <input type="file" />
          <div class="t-muted" style="margin-top:6px;">png/jpg</div>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn" data-close>Cancelar</button>
      <button class="btn primary" data-open="#modalOk">Crear</button>
    </div>
  </div>
</div>

<!-- Modal OK -->
<div class="backdrop" id="modalOk">
  <div class="modal" style="width:min(440px, 94vw);">
    <div class="modal-head">
      <h3>✅ Listo</h3>
      <button class="icon-btn" data-close>✕</button>
    </div>
    <div class="modal-body">
      <div style="font-weight:900; font-size:16px;">El usuario fue creado satisfactoriamente</div>
      <div class="t-muted" style="margin-top:6px;">Se envió un link de confirmación al correo.</div>
    </div>
    <div class="modal-foot">
      <button class="btn primary" data-close>Continuar</button>
    </div>
  </div>
</div>

<?php require __DIR__ . "../../../shared/partials/layout_bottom.php"; ?>