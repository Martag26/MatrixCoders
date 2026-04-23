<?php
/* Módulo de Usuarios — ADMINISTRADOR */
function crmRolLabel(array $u): string {
  if (!empty($u['es_superadmin'])) return 'superadmin';
  if ($u['rol'] === 'ADMINISTRADOR') return 'admin';
  if (!empty($u['es_moderador'])) return 'moderador';
  if ($u['rol'] === 'EDITOR') return 'instructor';
  return 'alumno';
}
function crmRolDisplay(string $rolKey): string {
  return match($rolKey) {
    'superadmin' => 'Superadmin', 'admin' => 'Administrador',
    'moderador' => 'Moderador', 'instructor' => 'Instructor', default => 'Alumno'
  };
}
$statsRow = $statsRow ?? ['total'=>0,'alumnos'=>0,'instructores'=>0,'admins'=>0,'nuevos7d'=>0];
$periodo  = $periodo ?? '';
$perPage  = $perPage ?? 15;
?>

<div class="crm-page-header">
  <div>
    <h1>Gestión de Usuarios</h1>
    <p>Administra usuarios, roles y permisos. Total: <strong><?= number_format($totalRows) ?></strong></p>
  </div>
  <div class="crm-page-actions">
    <button class="crm-btn crm-btn-primary" onclick="openModalCrear()">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
      Nuevo usuario
    </button>
  </div>
</div>

<!-- Stats strip -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:20px">
  <?php
  $strips = [
    ['label'=>'Total usuarios',  'value'=>$statsRow['total'],        'icon'=>'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8z M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75', 'color'=>'var(--crm-primary)'],
    ['label'=>'Alumnos',         'value'=>$statsRow['alumnos'],      'icon'=>'M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7zM12 11a4 4 0 100-8 4 4 0 000 8z', 'color'=>'var(--crm-info)'],
    ['label'=>'Instructores',    'value'=>$statsRow['instructores'], 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color'=>'var(--crm-success)'],
    ['label'=>'Admins',          'value'=>$statsRow['admins'],       'icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color'=>'var(--crm-warning)'],
    ['label'=>'Nuevos (7 días)', 'value'=>$statsRow['nuevos7d'],     'icon'=>'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color'=>'var(--crm-success)'],
  ];
  foreach ($strips as $s): ?>
  <div class="crm-card" style="padding:14px 16px;display:flex;align-items:center;gap:12px">
    <div style="width:34px;height:34px;border-radius:9px;background:<?= $s['color'] ?>18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="16" height="16" fill="none" stroke="<?= $s['color'] ?>" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="<?= $s['icon'] ?>"/></svg>
    </div>
    <div>
      <div style="font-size:20px;font-weight:700;color:var(--crm-text);line-height:1"><?= number_format((int)$s['value']) ?></div>
      <div style="font-size:11px;color:var(--crm-muted);margin-top:2px"><?= $s['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Toolbar -->
<form method="GET" action="<?= $crmFormBase ?>" id="filtroForm">
  <?= $crmFormHidden ?>
  <input type="hidden" name="sec" value="usuarios">
  <div class="crm-toolbar" style="flex-wrap:wrap;gap:8px">
    <div class="crm-search-wrap" style="flex:1;min-width:200px">
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
    <select name="periodo" class="crm-filter-select" onchange="this.form.submit()">
      <option value="">Cualquier fecha</option>
      <option value="hoy"    <?= $periodo==='hoy'    ?'selected':'' ?>>Hoy</option>
      <option value="semana" <?= $periodo==='semana' ?'selected':'' ?>>Última semana</option>
      <option value="mes"    <?= $periodo==='mes'    ?'selected':'' ?>>Último mes</option>
    </select>
    <select name="per" class="crm-filter-select" onchange="this.form.submit()" title="Usuarios por página">
      <?php foreach ([15,25,50,100] as $n): ?>
        <option value="<?= $n ?>" <?= $perPage===$n?'selected':'' ?>><?= $n ?> por página</option>
      <?php endforeach; ?>
    </select>
    <?php if ($q || $rol || $periodo): ?>
    <a href="<?= $crmBase ?>usuarios" class="crm-btn crm-btn-secondary">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      Limpiar
    </a>
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
        $colorMap = ['superadmin'=>'','admin'=>'blue','moderador'=>'orange','instructor'=>'green','alumno'=>''];
        $colorClass = $colorMap[$rolKey] ?? '';
        $isNew = strtotime($u['creado_en']) >= strtotime('-7 days');
      ?>
      <tr>
        <td>
          <div class="crm-user-row">
            <div class="crm-user-row-avatar <?= $colorClass ?>"><?= $initial ?></div>
            <div>
              <div class="crm-user-row-name" style="display:flex;align-items:center;gap:6px">
                <?= htmlspecialchars($u['nombre']) ?>
                <?php if ($isNew): ?>
                <span style="font-size:9px;font-weight:700;background:var(--crm-success);color:#fff;padding:1px 6px;border-radius:99px;letter-spacing:.4px">NUEVO</span>
                <?php endif; ?>
              </div>
              <div class="crm-user-row-email"><?= htmlspecialchars($u['email']) ?></div>
            </div>
          </div>
        </td>
        <td><span class="crm-badge <?= $rolKey ?>"><?= crmRolDisplay($rolKey) ?></span></td>
        <td>
          <div style="display:flex;align-items:center;gap:6px">
            <span style="font-weight:600;font-size:14px"><?= $u['cursos_count'] ?></span>
            <span style="font-size:11px;color:var(--crm-muted)">matriculacion<?= $u['cursos_count'] !== 1 ? 'es' : '' ?></span>
          </div>
        </td>
        <td style="font-size:12.5px;color:var(--crm-muted)"><?= date('d/m/Y', strtotime($u['creado_en'])) ?></td>
        <td style="text-align:right">
          <div style="display:flex;gap:6px;justify-content:flex-end">
            <button class="crm-btn-icon" title="Editar" onclick='openModalEditar(<?= htmlspecialchars(json_encode([
              "id"=>$u['id'],"nombre"=>$u['nombre'],"email"=>$u['email'],"rol"=>$rolKey
            ]), JSON_UNESCAPED_UNICODE) ?>)'>
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <?php if ($u['id'] != $usuario['id']): ?>
            <button class="crm-btn-icon danger" title="Eliminar usuario" onclick="pedirEliminar(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['nombre'])) ?>', '<?= addslashes(htmlspecialchars($u['email'])) ?>')">
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
      Mostrando <?= count($usuarios) ?> de <?= number_format($totalRows) ?> usuarios
    </div>
    <div class="crm-pag-btns">
      <?php if ($page > 1): ?>
        <a class="crm-pag-btn" href="<?= $crmBase ?>usuarios&pag=<?= $page-1 ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>&rol=<?= urlencode($rol) ?>&periodo=<?= urlencode($periodo) ?>">‹</a>
      <?php endif; ?>
      <?php for ($i = max(1,$page-2); $i <= min($totalPags,$page+2); $i++): ?>
        <a class="crm-pag-btn <?= $i===$page?'active':'' ?>" href="<?= $crmBase ?>usuarios&pag=<?= $i ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>&rol=<?= urlencode($rol) ?>&periodo=<?= urlencode($periodo) ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPags): ?>
        <a class="crm-pag-btn" href="<?= $crmBase ?>usuarios&pag=<?= $page+1 ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>&rol=<?= urlencode($rol) ?>&periodo=<?= urlencode($periodo) ?>">›</a>
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
        <h5 class="modal-title">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Nuevo usuario
        </h5>
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
              <?php if ($esSuperAdmin): ?>
              <option value="superadmin">Superadmin</option>
              <?php endif; ?>
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
              <?php if ($esSuperAdmin): ?>
              <option value="superadmin">Superadmin</option>
              <?php endif; ?>
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

<!-- ===== MODAL ELIMINAR USUARIO ===== -->
<div class="modal fade crm-modal" id="modalEliminarUsuario" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:16px;overflow:hidden">
      <div style="background:rgba(239,68,68,.06);padding:28px 24px 16px;text-align:center">
        <div style="width:52px;height:52px;border-radius:50%;background:rgba(239,68,68,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
          <svg width="24" height="24" fill="none" stroke="#ef4444" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </div>
        <h5 style="font-size:16px;font-weight:700;color:#111;margin-bottom:6px">Eliminar usuario</h5>
        <p style="font-size:13px;color:#6b7280;margin:0">Esta acción es <strong>irreversible</strong>.<br>Se eliminarán todos sus datos.</p>
      </div>
      <div style="padding:16px 24px;background:#fff">
        <input type="hidden" id="delUserId">
        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px 16px;margin-bottom:16px">
          <div style="font-weight:700;font-size:14px;color:#111" id="delUserName"></div>
          <div style="font-size:12px;color:#6b7280;margin-top:2px" id="delUserEmail"></div>
        </div>
        <div style="display:flex;gap:10px">
          <button class="crm-btn crm-btn-secondary" style="flex:1" data-bs-dismiss="modal">Cancelar</button>
          <button class="crm-btn crm-btn-danger" style="flex:1" id="btnEliminarUsuario" onclick="confirmarEliminarUsuario()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/></svg>
            Eliminar
          </button>
        </div>
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

function pedirEliminar(id, nombre, email) {
  document.getElementById('delUserId').value = id;
  document.getElementById('delUserName').textContent = nombre;
  document.getElementById('delUserEmail').textContent = email;
  openModal('modalEliminarUsuario');
}

async function confirmarEliminarUsuario() {
  const id = parseInt(document.getElementById('delUserId').value);
  const btn = document.getElementById('btnEliminarUsuario');
  btn.disabled = true;
  const res = await CRM.api('eliminar_usuario', { id });
  btn.disabled = false;
  if (res.ok) { CRM.toast(res.mensaje, 'success'); closeModal('modalEliminarUsuario'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

document.getElementById('searchInput')?.addEventListener('input', CRM.debounce(() => {
  document.getElementById('filtroForm').submit();
}, 600));
</script>
