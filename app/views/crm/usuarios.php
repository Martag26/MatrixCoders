<?php
/* Módulo de Usuarios — solo SUPERADMIN */
$avatarColors = ['blue','green','orange','red',''];
function crmRolLabel(array $u): string {
  if ($u['es_superadmin']) return 'superadmin';
  if ($u['rol'] === 'ADMINISTRADOR') return 'admin';
  if ($u['es_moderador']) return 'moderador';
  if ($u['rol'] === 'EDITOR') return 'instructor';
  return 'alumno';
}
function crmRolDisplay(string $rolKey): string {
  return match($rolKey) {
    'superadmin' => 'Superadmin', 'admin' => 'Administrador',
    'moderador' => 'Moderador', 'instructor' => 'Instructor', default => 'Alumno'
  };
}
?>

<div class="crm-page-header">
  <div>
    <h1>Gestión de Usuarios</h1>
    <p>Crea, edita y gestiona todos los usuarios de la plataforma. Total: <strong><?= $totalRows ?></strong></p>
  </div>
  <div class="crm-page-actions">
    <button class="crm-btn crm-btn-primary" onclick="openModalCrear()">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
      Nuevo usuario
    </button>
  </div>
</div>

<!-- Toolbar -->
<form method="GET" action="<?= $crmFormBase ?>" id="filtroForm">
  <?= $crmFormHidden ?>
  <input type="hidden" name="sec" value="usuarios">
  <div class="crm-toolbar">
    <div class="crm-search-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre o email…" class="crm-search-input" id="searchInput">
    </div>
    <select name="rol" class="crm-filter-select" onchange="this.form.submit()">
      <option value="">Todos los roles</option>
      <option value="superadmin"  <?= $rol==='superadmin' ?'selected':'' ?>>Superadmin</option>
      <option value="admin"       <?= $rol==='admin'      ?'selected':'' ?>>Administrador</option>
      <option value="moderador"   <?= $rol==='moderador'  ?'selected':'' ?>>Moderador</option>
      <option value="instructor"  <?= $rol==='instructor' ?'selected':'' ?>>Instructor</option>
      <option value="alumno"      <?= $rol==='alumno'     ?'selected':'' ?>>Alumno</option>
    </select>
    <button type="submit" class="crm-btn crm-btn-secondary">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
      Filtrar
    </button>
    <?php if ($q || $rol): ?>
    <a href="<?= $crmBase ?>usuarios" class="crm-btn crm-btn-secondary">Limpiar</a>
    <?php endif; ?>
  </div>
</form>

<!-- Table -->
<div class="crm-table-wrap">
  <table class="crm-table">
    <thead>
      <tr>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Cursos</th>
        <th>Registrado</th>
        <th style="text-align:right">Acciones</th>
      </tr>
    </thead>
    <tbody id="usuariosBody">
      <?php if (empty($usuarios)): ?>
      <tr><td colspan="5" style="text-align:center;padding:50px;color:var(--crm-muted)">
        <div class="crm-empty">
          <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <h3>Sin usuarios</h3>
          <p>No se encontraron usuarios con ese filtro.</p>
        </div>
      </td></tr>
      <?php else: ?>
      <?php foreach ($usuarios as $u):
        $rolKey  = crmRolLabel($u);
        $initial = mb_strtoupper(mb_substr($u['nombre'], 0, 1, 'UTF-8'), 'UTF-8');
        $colors  = ['superadmin'=>'','admin'=>'blue','moderador'=>'orange','instructor'=>'green','alumno'=>''];
        $colorClass = $colors[$rolKey] ?? '';
      ?>
      <tr data-search-row>
        <td>
          <div class="crm-user-row">
            <div class="crm-user-row-avatar <?= $colorClass ?>"><?= $initial ?></div>
            <div>
              <div class="crm-user-row-name"><?= htmlspecialchars($u['nombre']) ?></div>
              <div class="crm-user-row-email"><?= htmlspecialchars($u['email']) ?></div>
            </div>
          </div>
        </td>
        <td><span class="crm-badge <?= $rolKey ?>"><?= crmRolDisplay($rolKey) ?></span></td>
        <td><span style="font-weight:600"><?= $u['cursos_count'] ?></span> cursos</td>
        <td style="font-size:12.5px;color:var(--crm-muted)"><?= date('d/m/Y', strtotime($u['creado_en'])) ?></td>
        <td style="text-align:right">
          <div style="display:flex;gap:6px;justify-content:flex-end">
            <button class="crm-btn-icon" title="Editar" onclick='openModalEditar(<?= htmlspecialchars(json_encode([
              "id"=>$u['id'],"nombre"=>$u['nombre'],"email"=>$u['email'],"rol"=>$rolKey
            ]), JSON_UNESCAPED_UNICODE) ?>)'>
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <?php if ($u['id'] != $usuario['id']): ?>
            <button class="crm-btn-icon danger" title="Eliminar" onclick="confirmarEliminar(<?= $u['id'] ?>, '<?= addslashes($u['nombre']) ?>')">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($totalPags > 1): ?>
  <div class="crm-pagination">
    <div class="crm-pagination-info">
      Mostrando <?= count($usuarios) ?> de <?= $totalRows ?> usuarios
    </div>
    <div class="crm-pag-btns">
      <?php if ($page > 1): ?>
        <a class="crm-pag-btn" href="<?= $crmBase ?>usuarios&pag=<?= $page-1 ?>&q=<?= urlencode($q) ?>&rol=<?= urlencode($rol) ?>">‹</a>
      <?php endif; ?>
      <?php for ($i = max(1,$page-2); $i <= min($totalPags,$page+2); $i++): ?>
        <a class="crm-pag-btn <?= $i===$page?'active':'' ?>" href="<?= $crmBase ?>usuarios&pag=<?= $i ?>&q=<?= urlencode($q) ?>&rol=<?= urlencode($rol) ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPags): ?>
        <a class="crm-pag-btn" href="<?= $crmBase ?>usuarios&pag=<?= $page+1 ?>&q=<?= urlencode($q) ?>&rol=<?= urlencode($rol) ?>">›</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ===== MODAL CREAR ===== -->
<div class="modal fade crm-modal" id="modalCrear" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Nombre completo *</label>
            <input type="text" class="crm-input" id="crNombre" placeholder="Ej: María García">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Email *</label>
            <input type="email" class="crm-input" id="crEmail" placeholder="usuario@ejemplo.com">
          </div>
        </div>
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Contraseña *</label>
            <input type="password" class="crm-input" id="crPass" placeholder="Mínimo 6 caracteres">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Rol *</label>
            <select class="crm-select" id="crRol">
              <option value="alumno">Alumno</option>
              <option value="instructor">Instructor</option>
              <option value="moderador">Moderador</option>
              <option value="admin">Administrador</option>
              <option value="superadmin">Superadmin</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" id="btnCrear" onclick="crearUsuario()">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Crear usuario
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL EDITAR ===== -->
<div class="modal fade crm-modal" id="modalEditar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edId">
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Nombre completo *</label>
            <input type="text" class="crm-input" id="edNombre">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Email *</label>
            <input type="email" class="crm-input" id="edEmail">
          </div>
        </div>
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Nueva contraseña</label>
            <input type="password" class="crm-input" id="edPass" placeholder="Dejar vacío para no cambiar">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Rol *</label>
            <select class="crm-select" id="edRol">
              <option value="alumno">Alumno</option>
              <option value="instructor">Instructor</option>
              <option value="moderador">Moderador</option>
              <option value="admin">Administrador</option>
              <option value="superadmin">Superadmin</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" onclick="editarUsuario()">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>

<script>
function openModalCrear() {
  document.getElementById('crNombre').value = '';
  document.getElementById('crEmail').value  = '';
  document.getElementById('crPass').value   = '';
  document.getElementById('crRol').value    = 'alumno';
  openModal('modalCrear');
}

function openModalEditar(u) {
  document.getElementById('edId').value     = u.id;
  document.getElementById('edNombre').value = u.nombre;
  document.getElementById('edEmail').value  = u.email;
  document.getElementById('edPass').value   = '';
  document.getElementById('edRol').value    = u.rol;
  openModal('modalEditar');
}

async function crearUsuario() {
  const btn = document.getElementById('btnCrear');
  btn.disabled = true;
  const res = await CRM.api('crear_usuario', {
    nombre:   document.getElementById('crNombre').value,
    email:    document.getElementById('crEmail').value,
    password: document.getElementById('crPass').value,
    rol:      document.getElementById('crRol').value,
  });
  btn.disabled = false;
  if (res.ok) { CRM.toast(res.mensaje, 'success'); closeModal('modalCrear'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

async function editarUsuario() {
  const res = await CRM.api('editar_usuario', {
    id:       parseInt(document.getElementById('edId').value),
    nombre:   document.getElementById('edNombre').value,
    email:    document.getElementById('edEmail').value,
    password: document.getElementById('edPass').value,
    rol:      document.getElementById('edRol').value,
  });
  if (res.ok) { CRM.toast(res.mensaje, 'success'); closeModal('modalEditar'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

async function confirmarEliminar(id, nombre) {
  if (!CRM.confirm(`¿Eliminar al usuario "${nombre}"? Esta acción es irreversible.`)) return;
  const res = await CRM.api('eliminar_usuario', { id });
  if (res.ok) { CRM.toast(res.mensaje, 'success'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

// Search with debounce
document.getElementById('searchInput')?.addEventListener('input', CRM.debounce(e => {
  document.getElementById('filtroForm').submit();
}, 600));
</script>
