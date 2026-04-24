<?php
$notif     = (int)($usuario['notificaciones'] ?? 1);
$privacidad = $usuario['privacidad'] ?? 'publico';
?>

<div class="crm-page-header">
  <div>
    <h1>Ajustes</h1>
    <p>Preferencias, apariencia y configuración del CRM.</p>
  </div>
  <div class="crm-page-actions">
    <a href="<?= $crmBase ?>perfil" class="crm-btn crm-btn-secondary crm-btn-sm">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Mi Perfil
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

  <!-- ── Notificaciones ── -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
      Notificaciones
    </h3>

    <?php
    $notifItems = [
      ['ajNotifGlobal',   'Notificaciones globales',       'Recibir todas las notificaciones del sistema', $notif],
      ['ajNotifCampana',  'Nuevas campañas',                'Avisar cuando se crea o modifica una campaña', 1],
      ['ajNotifIncidencia','Incidencias y soporte',         'Avisar cuando llega una nueva incidencia', 1],
      ['ajNotifUsuario',  'Nuevos usuarios',               'Avisar cuando se registra un usuario nuevo', 0],
    ];
    foreach ($notifItems as [$id, $label, $hint, $checked]): ?>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--crm-border);gap:12px">
      <div>
        <div style="font-size:13.5px;font-weight:600;color:var(--crm-text)"><?= $label ?></div>
        <div style="font-size:11.5px;color:var(--crm-muted);margin-top:1px"><?= $hint ?></div>
      </div>
      <label class="crm-toggle-switch" style="flex-shrink:0;margin-top:2px">
        <input type="checkbox" id="<?= $id ?>" <?= $checked ? 'checked' : '' ?>>
        <span class="crm-toggle-slider"></span>
      </label>
    </div>
    <?php endforeach; ?>

    <button class="crm-btn crm-btn-primary" style="margin-top:14px" onclick="guardarAjustes()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
      Guardar notificaciones
    </button>
  </div>

  <!-- ── Privacidad y visualización ── -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      Privacidad y visualización
    </h3>

    <div class="crm-form-group">
      <label class="crm-label">Privacidad del perfil</label>
      <select class="crm-select" id="ajPrivacidad">
        <option value="publico"  <?= $privacidad === 'publico'  ? 'selected' : '' ?>>Público — visible para otros admins</option>
        <option value="privado"  <?= $privacidad === 'privado'  ? 'selected' : '' ?>>Privado — solo tú</option>
      </select>
    </div>

    <div class="crm-form-group">
      <label class="crm-label">Idioma de la interfaz</label>
      <select class="crm-select" id="ajIdioma">
        <option value="es" selected>Español</option>
        <option value="en">English</option>
      </select>
      <div class="crm-form-hint">Actualmente solo disponible en Español.</div>
    </div>

    <div class="crm-form-group" style="margin-bottom:0">
      <label class="crm-label">Sidebar al iniciar</label>
      <div style="display:flex;align-items:center;gap:10px;margin-top:6px">
        <label class="crm-toggle-switch">
          <input type="checkbox" id="sidebarDefault">
          <span class="crm-toggle-slider"></span>
        </label>
        <span style="font-size:13px;color:var(--crm-muted)" id="sidebarLabel">Expandido</span>
      </div>
      <div class="crm-form-hint">Estado guardado automáticamente en tu navegador.</div>
    </div>

    <button class="crm-btn crm-btn-primary" style="margin-top:14px" onclick="guardarPreferencias()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
      Guardar preferencias
    </button>
  </div>

  <!-- ── Atajos de teclado ── -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><path stroke-linecap="round" d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M8 14h8"/></svg>
      Atajos de teclado
    </h3>

    <p style="font-size:12.5px;color:var(--crm-muted);margin:0 0 14px">
      Combina <kbd style="background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:4px;padding:1px 5px;font-size:11px">G</kbd> seguido de otra tecla para navegar rápidamente.
    </p>

    <?php foreach ([
      ['G + D', 'Dashboard',    'Ir al panel principal'],
      ['G + U', 'Usuarios',     'Gestión de usuarios'],
      ['G + C', 'Cursos',       'Listado de cursos'],
      ['G + P', 'Campañas',     'Panel de campañas'],
      ['G + M', 'Comunicación', 'Mensajes e incidencias'],
      ['G + S', 'Ajustes',      'Esta página'],
      ['/',     'Buscar',       'Enfocar barra de búsqueda'],
    ] as [$key, $dest, $desc]): ?>
    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--crm-border);font-size:13px">
      <kbd style="background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:5px;padding:3px 8px;font-family:monospace;font-size:11.5px;font-weight:700;min-width:56px;text-align:center;flex-shrink:0"><?= $key ?></kbd>
      <div style="flex:1">
        <span style="font-weight:600;color:var(--crm-text)"><?= $dest ?></span>
        <span style="color:var(--crm-muted);margin-left:6px">— <?= $desc ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Información del sistema ── -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path stroke-linecap="round" d="M8 21h8m-4-4v4"/></svg>
      Información del sistema
    </h3>

    <?php
    $dbFile = __DIR__ . '/../../../app/data/database.sqlite';
    $dbSize = file_exists($dbFile) ? round(filesize($dbFile) / 1024, 1) . ' KB' : 'N/A';
    $sysItems = [
      ['Versión CRM',    '1.1.0'],
      ['PHP',            phpversion()],
      ['Base de datos',  'SQLite 3 (' . $dbSize . ')'],
      ['Servidor',       htmlspecialchars(explode('/', $_SERVER['SERVER_SOFTWARE'] ?? 'Apache')[0])],
      ['Sistema',        PHP_OS_FAMILY . ' ' . PHP_OS],
      ['Entorno',        defined('BASE_URL') ? (str_contains(BASE_URL, 'localhost') ? 'Desarrollo' : 'Producción') : 'Desconocido'],
    ];
    foreach ($sysItems as [$label, $val]): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--crm-border);font-size:13px">
      <span style="color:var(--crm-muted)"><?= $label ?></span>
      <strong style="color:var(--crm-text)"><?= $val ?></strong>
    </div>
    <?php endforeach; ?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px">
      <a href="<?= $crmSiteUrl ?>" class="crm-btn crm-btn-secondary crm-btn-sm">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Volver al sitio
      </a>
      <a href="<?= $crmBase ?>logs" class="crm-btn crm-btn-secondary crm-btn-sm">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
        Ver logs
      </a>
    </div>
  </div>

</div>

<!-- ── Zona de peligro ── -->
<div class="crm-card" style="margin-top:20px;border-color:rgba(239,68,68,.25)">
  <h3 class="crm-card-title" style="color:var(--crm-danger)">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    Zona de peligro
  </h3>
  <div style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:center">
    <div>
      <div style="font-size:13.5px;font-weight:600;color:var(--crm-text)">Cerrar sesión en todos los dispositivos</div>
      <div style="font-size:12px;color:var(--crm-muted);margin-top:2px">Cierra la sesión activa en este dispositivo y redirige al login.</div>
    </div>
    <a href="<?= $crmLogoutUrl ?>" class="crm-btn crm-btn-danger crm-btn-sm">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Cerrar sesión
    </a>
  </div>
</div>

<script>
/* Sidebar toggle state */
const sidebarCb = document.getElementById('sidebarDefault');
const sidebarLbl = document.getElementById('sidebarLabel');
if (sidebarCb) {
  const collapsed = localStorage.getItem('crm_sidebar') === '1';
  sidebarCb.checked = collapsed;
  sidebarLbl.textContent = collapsed ? 'Colapsado' : 'Expandido';
  sidebarCb.addEventListener('change', function() {
    localStorage.setItem('crm_sidebar', this.checked ? '1' : '0');
    sidebarLbl.textContent = this.checked ? 'Colapsado' : 'Expandido';
    const sb = document.getElementById('crmSidebar');
    const mn = document.getElementById('crmMain');
    if (sb) sb.classList.toggle('collapsed', this.checked);
    if (mn) mn.classList.toggle('expanded', this.checked);
  });
}

async function guardarAjustes() {
  const res = await CRM.api('actualizar_ajustes', {
    notificaciones: document.getElementById('ajNotifGlobal').checked ? 1 : 0,
    privacidad:     document.getElementById('ajPrivacidad').value,
  });
  if (res.ok) CRM.toast(res.mensaje, 'success');
  else CRM.toast(res.error || 'Error al guardar', 'error');
}

async function guardarPreferencias() {
  const res = await CRM.api('actualizar_ajustes', {
    notificaciones: document.getElementById('ajNotifGlobal')?.checked ? 1 : 0,
    privacidad:     document.getElementById('ajPrivacidad').value,
  });
  if (res.ok) CRM.toast('Preferencias guardadas', 'success');
  else CRM.toast(res.error || 'Error al guardar', 'error');
}
</script>
