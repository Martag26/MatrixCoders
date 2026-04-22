<?php /* Ajustes del CRM */ ?>

<div class="crm-page-header">
  <div><h1>Ajustes</h1><p>Preferencias de tu cuenta en el CRM.</p></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start" class="crm-charts-grid">

  <!-- Notificaciones y privacidad -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
      Notificaciones y privacidad
    </h3>
    <div class="crm-form-group">
      <label class="crm-label">Recibir notificaciones</label>
      <div style="display:flex;align-items:center;gap:10px">
        <label class="crm-toggle-switch">
          <input type="checkbox" id="ajNotif" <?= ($usuario['notificaciones']??1)?'checked':'' ?>>
          <span class="crm-toggle-slider"></span>
        </label>
        <span style="font-size:13px;color:var(--crm-muted)" id="notifLabel">
          <?= ($usuario['notificaciones']??1)?'Activadas':'Desactivadas' ?>
        </span>
      </div>
    </div>
    <div class="crm-form-group">
      <label class="crm-label">Privacidad del perfil</label>
      <select class="crm-select" id="ajPrivacidad">
        <option value="publico"  <?= ($usuario['privacidad']??'publico')==='publico' ?'selected':'' ?>>Público</option>
        <option value="privado"  <?= ($usuario['privacidad']??'publico')==='privado' ?'selected':'' ?>>Privado</option>
      </select>
    </div>
    <button class="crm-btn crm-btn-primary" onclick="guardarAjustes()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
      Guardar preferencias
    </button>
  </div>

  <!-- Información del sistema -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
      Información del sistema
    </h3>
    <div style="display:flex;flex-direction:column;gap:10px;font-size:13.5px">
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--crm-border)">
        <span style="color:var(--crm-muted)">Versión CRM</span>
        <strong>1.0.0</strong>
      </div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--crm-border)">
        <span style="color:var(--crm-muted)">Base de datos</span>
        <strong>SQLite 3</strong>
      </div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--crm-border)">
        <span style="color:var(--crm-muted)">Framework</span>
        <strong>PHP MVC Custom</strong>
      </div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--crm-border)">
        <span style="color:var(--crm-muted)">PHP</span>
        <strong><?= phpversion() ?></strong>
      </div>
      <div style="display:flex;justify-content:space-between;padding:8px 0">
        <span style="color:var(--crm-muted)">Servidor</span>
        <strong><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></strong>
      </div>
    </div>
    <hr class="crm-divider">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="<?= $crmSiteUrl ?>" class="crm-btn crm-btn-secondary crm-btn-sm">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Volver al sitio
      </a>
      <a href="<?= $crmLogoutUrl ?>" class="crm-btn crm-btn-danger crm-btn-sm">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Cerrar sesión
      </a>
    </div>
  </div>

  <!-- Sidebar behaviour -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
      Comportamiento del sidebar
    </h3>
    <div class="crm-form-group">
      <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
        <label class="crm-toggle-switch">
          <input type="checkbox" id="sidebarDefault" <?= localStorage_get() ?>>
          <span class="crm-toggle-slider"></span>
        </label>
        <span style="font-size:13px">Iniciar con sidebar colapsado</span>
      </label>
    </div>
    <p style="font-size:12.5px;color:var(--crm-muted);margin:0">
      El estado del sidebar se guarda automáticamente en tu navegador.
    </p>
  </div>

  <!-- Atajos de teclado -->
  <div class="crm-card">
    <h3 class="crm-card-title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><path stroke-linecap="round" d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M8 14h8"/></svg>
      Atajos de teclado
    </h3>
    <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
      <?php foreach ([
        ['G + D', 'Ir al Dashboard'],
        ['G + U', 'Ir a Usuarios'],
        ['G + C', 'Ir a Cursos'],
        ['G + P', 'Ir a Campañas'],
        ['G + M', 'Ir a Comunicación'],
        ['/', 'Buscar'],
      ] as [$key, $desc]): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--crm-border)">
        <span style="color:var(--crm-muted)"><?= $desc ?></span>
        <span style="background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:5px;padding:2px 8px;font-family:monospace;font-size:12px"><?= $key ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<script>
async function guardarAjustes() {
  const res = await CRM.api('actualizar_ajustes', {
    notificaciones: document.getElementById('ajNotif').checked ? 1 : 0,
    privacidad:     document.getElementById('ajPrivacidad').value,
  });
  if (res.ok) CRM.toast(res.mensaje, 'success');
  else CRM.toast(res.error, 'error');
}

document.getElementById('ajNotif')?.addEventListener('change', function() {
  document.getElementById('notifLabel').textContent = this.checked ? 'Activadas' : 'Desactivadas';
});

const sidebarCb = document.getElementById('sidebarDefault');
if (sidebarCb) sidebarCb.checked = localStorage.getItem('crm_sidebar') === '1';
sidebarCb?.addEventListener('change', function() {
  localStorage.setItem('crm_sidebar', this.checked ? '1' : '0');
});

// Keyboard shortcuts
document.addEventListener('keydown', e => {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
  const routes = {
    'd': 'dashboard', 'u': 'usuarios', 'c': 'cursos', 'p': 'campanas', 'm': 'comunicacion'
  };
  if (e.key === '/' && !e.ctrlKey) {
    e.preventDefault();
    document.getElementById('headerSearch')?.focus();
  }
  if (e.altKey && routes[e.key]) {
    window.location.href = window.CRM_NAV_BASE + routes[e.key];
  }
});
</script>
<?php function localStorage_get(): string { return ''; } ?>
