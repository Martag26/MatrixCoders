<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($titulo) ?> — Matrix CRM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/crm.css">
<style>
.crm-notif-item:hover{background:rgba(124,58,237,.06)!important;border-color:rgba(124,58,237,.15)!important}
#notifPanel{display:none}
#notifPanel.open{display:flex!important}
@keyframes notif-slide-in{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
#notifPanel[style*="flex"]{animation:notif-slide-in .15s ease}
.crm-notif-btn{position:relative}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="crm-body">

<!-- Mobile overlay -->
<div class="crm-overlay" id="crmOverlay"></div>

<!-- ================================================================ -->
<!-- SIDEBAR                                                            -->
<!-- ================================================================ -->
<aside class="crm-sidebar" id="crmSidebar">

  <!-- Brand -->
  <div class="crm-sidebar__brand">
    <div class="crm-sidebar__brand-icon">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
      </svg>
    </div>
    <div class="crm-sidebar__brand-text">
      <span class="crm-sidebar__brand-name">Matrix CRM</span>
      <span class="crm-sidebar__brand-sub">Panel de control</span>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="crm-sidebar__nav">

    <div class="crm-sidebar__section">
      <div class="crm-sidebar__section-label">Principal</div>

      <a href="<?= $crmBase ?>dashboard"
         class="crm-sidebar__link <?= $seccion==='dashboard'?'active':'' ?>"
         data-tooltip="Dashboard">
        <svg class="crm-sl-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span class="crm-sl-label">Dashboard</span>
      </a>

      <?php if ($esAdmin): ?>
      <a href="<?= $crmBase ?>usuarios"
         class="crm-sidebar__link <?= $seccion==='usuarios'?'active':'' ?>"
         data-tooltip="Usuarios">
        <svg class="crm-sl-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
        <span class="crm-sl-label">Usuarios</span>
      </a>
      <?php endif; ?>

      <?php if ($esAdmin || $esInstructor): ?>
      <a href="<?= $crmBase ?>cursos"
         class="crm-sidebar__link <?= in_array($seccion,['cursos','editor'])?'active':'' ?>"
         data-tooltip="Cursos">
        <svg class="crm-sl-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <span class="crm-sl-label">Mis Cursos</span>
      </a>
      <?php endif; ?>

      <?php if ($esAdmin): ?>
      <a href="<?= $crmBase ?>campanas"
         class="crm-sidebar__link <?= $seccion==='campanas'?'active':'' ?>"
         data-tooltip="Campañas">
        <svg class="crm-sl-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
        <span class="crm-sl-label">Campañas</span>
      </a>
      <?php endif; ?>

      <a href="<?= $crmBase ?>comunicacion"
         class="crm-sidebar__link <?= $seccion==='comunicacion'?'active':'' ?>"
         data-tooltip="Comunicación">
        <svg class="crm-sl-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <span class="crm-sl-label">Comunicación</span>
      </a>
    </div>

    <hr class="crm-sidebar__divider">

    <div class="crm-sidebar__section">
      <div class="crm-sidebar__section-label">Cuenta</div>

      <a href="<?= $crmBase ?>logs"
         class="crm-sidebar__link <?= $seccion==='logs'?'active':'' ?>"
         data-tooltip="Logs de actividad">
        <svg class="crm-sl-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        <span class="crm-sl-label">Logs</span>
      </a>

    </div>

  </nav>

  <!-- Footer: logout -->
  <div class="crm-sidebar__footer">
    <a href="<?= $crmLogoutUrl ?>" class="crm-sidebar__link" data-tooltip="Cerrar sesión" style="color:var(--crm-danger,#ef4444)">
      <svg class="crm-sl-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      <span class="crm-sl-label">Cerrar sesión</span>
    </a>
  </div>

</aside>

<!-- ================================================================ -->
<!-- MAIN                                                               -->
<!-- ================================================================ -->
<div class="crm-main" id="crmMain">

  <!-- HEADER -->
  <header class="crm-header">
    <div class="crm-header__left">
      <button class="crm-toggle" id="crmToggle" title="Colapsar menú" aria-label="Menú">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <nav class="crm-breadcrumb" aria-label="Breadcrumb">
        <span>CRM</span>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($titulo) ?></span>
      </nav>
    </div>

    <div class="crm-header__right">
      <!-- Notification bell -->
      <div class="dropdown" style="position:relative">
        <button class="crm-notif-btn" id="notifBellBtn" title="Notificaciones" onclick="toggleNotifPanel()" style="position:relative">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
          <span id="notifBadge" style="display:none;position:absolute;top:-3px;right:-3px;min-width:16px;height:16px;background:var(--crm-danger,#ef4444);color:#fff;font-size:9px;font-weight:800;border-radius:99px;display:flex;align-items:center;justify-content:center;padding:0 3px;line-height:1"></span>
        </button>
        <!-- Notification panel -->
        <div id="notifPanel" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:360px;max-height:480px;background:var(--crm-card);border:1px solid var(--crm-border);border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.25);z-index:1050;overflow:hidden;display:none;flex-direction:column">
          <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--crm-border)">
            <span style="font-size:13.5px;font-weight:700">Notificaciones</span>
            <button onclick="marcarTodasLeidas()" style="font-size:11.5px;color:var(--crm-primary);background:none;border:none;cursor:pointer;font-weight:600">Marcar todas leídas</button>
          </div>
          <div id="notifList" style="overflow-y:auto;max-height:380px;padding:6px">
            <div style="text-align:center;padding:28px;color:var(--crm-muted)">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;display:block;margin:0 auto 8px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg>
              Cargando…
            </div>
          </div>
        </div>
      </div>

      <!-- User dropdown -->
      <?php
        $rolLabel = match(true) {
          ($esSuperAdmin)              => 'Superadmin',
          ($esAdmin)                   => 'Administrador',
          ($esModerador)               => 'Moderador',
          (isset($esInstructor) && $esInstructor) => 'Instructor',
          default                      => 'Usuario',
        };
        $avatarInitial = mb_strtoupper(mb_substr($usuario['nombre'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8');
      ?>
      <div class="dropdown">
        <button class="crm-user-btn" data-bs-toggle="dropdown" aria-expanded="false">
          <?php if (!empty($usuario['foto'])): ?>
            <div class="crm-user-avatar"><img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($usuario['foto']) ?>" alt="avatar"></div>
          <?php else: ?>
            <div class="crm-user-avatar"><?= $avatarInitial ?></div>
          <?php endif; ?>
          <div class="crm-user-info d-none d-md-flex">
            <span class="crm-user-name"><?= htmlspecialchars($usuario['nombre'] ?? '') ?></span>
            <span class="crm-user-role"><?= $rolLabel ?></span>
          </div>
          <svg class="crm-user-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 9l6 6 6-6"/></svg>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;border-radius:12px;border:1px solid var(--crm-border);box-shadow:var(--crm-shadow-md);padding:6px;">
          <li style="padding:10px 14px 6px;">
            <div style="font-size:13px;font-weight:700;color:var(--crm-text)"><?= htmlspecialchars($usuario['nombre'] ?? '') ?></div>
            <div style="font-size:11px;color:var(--crm-muted)"><?= htmlspecialchars($usuario['email'] ?? '') ?></div>
          </li>
          <li><hr class="dropdown-divider" style="margin:4px 0;"></li>
          <li><a class="dropdown-item" style="font-size:13px;border-radius:7px;" href="<?= $crmBase ?>perfil">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:7px"><path stroke-linecap="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Mi Perfil</a></li>
          <li><a class="dropdown-item" style="font-size:13px;border-radius:7px;" href="<?= $crmBase ?>ajustes">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:7px"><path stroke-linecap="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>Ajustes</a></li>
          <li><a class="dropdown-item" style="font-size:13px;border-radius:7px;" href="<?= $crmSiteUrl ?>">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:7px"><path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Volver al sitio</a></li>
        </ul>
      </div>
    </div>
  </header>

  <!-- CONTENT -->
  <main class="crm-content">
    <?php
      $viewFile = __DIR__ . "/../{$seccion}.php";
      if (file_exists($viewFile)) include $viewFile;
      else echo '<div class="crm-empty"><h3>Sección no encontrada</h3></div>';
    ?>
  </main>

</div>

<!-- Toast container -->
<div class="crm-toast-container" id="crmToastContainer"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.CRM_BASE_URL = '<?= BASE_URL ?>';
window.CRM_API_URL  = '<?= $crmApiUrl ?>';
window.CRM_NAV_BASE = '<?= $crmBase ?>';
</script>
<script src="<?= BASE_URL ?>/js/crm.js"></script>
<script>
/* ── CRM Notifications ── */
const NOTIF_TIPO_ICONS = {
  crm:        '📣',
  incidencia: '🎫',
  revision:   '📝',
  mensaje:    '💬',
  info:       'ℹ️',
  tarea:      '✅',
  expiracion: '⚠️',
};
const NOTIF_BADGE_COLORS = {
  danger:  'var(--crm-danger)',
  warning: 'var(--crm-warning)',
  info:    'var(--crm-info)',
  success: 'var(--crm-success)',
};

let notifPanelOpen = false;
let notifLoaded    = false;

function toggleNotifPanel() {
  notifPanelOpen = !notifPanelOpen;
  const panel = document.getElementById('notifPanel');
  panel.style.display = notifPanelOpen ? 'flex' : 'none';
  if (notifPanelOpen && !notifLoaded) loadNotifs();
}

// Close panel when clicking outside
document.addEventListener('click', function(e) {
  if (!e.target.closest('#notifBellBtn') && !e.target.closest('#notifPanel')) {
    if (notifPanelOpen) {
      notifPanelOpen = false;
      document.getElementById('notifPanel').style.display = 'none';
    }
  }
});

async function loadNotifs() {
  notifLoaded = true;
  try {
    const res = await fetch(`${window.CRM_API_URL}&action=get_crm_notifs`).then(r=>r.json());
    if (!res.ok) { renderNotifError(); return; }
    renderNotifs(res.alerts || [], res.notifs || [], res.unread || 0);
  } catch(e) { renderNotifError(); }
}

function renderNotifError() {
  document.getElementById('notifList').innerHTML = '<p style="text-align:center;padding:20px;color:var(--crm-muted);font-size:13px">Error al cargar notificaciones</p>';
}

function renderNotifs(alerts, notifs, unread) {
  // Update badge
  const badge = document.getElementById('notifBadge');
  if (unread > 0) {
    badge.style.display = 'flex';
    badge.textContent = unread > 99 ? '99+' : String(unread);
  } else {
    badge.style.display = 'none';
  }

  const list = document.getElementById('notifList');
  let html = '';

  // Admin alerts (system-generated, no id to mark as read)
  if (alerts.length) {
    html += '<div style="font-size:10px;font-weight:700;color:var(--crm-muted);text-transform:uppercase;letter-spacing:.6px;padding:6px 8px 4px">Alertas del sistema</div>';
    html += alerts.map(a => `
      <a href="${a.url_accion || '#'}" style="display:flex;align-items:flex-start;gap:10px;padding:10px 10px;border-radius:9px;text-decoration:none;color:inherit;background:rgba(124,58,237,.05);margin-bottom:4px;border:1px solid rgba(124,58,237,.12)" onclick="toggleNotifPanel()">
        <span style="font-size:16px;flex-shrink:0;margin-top:1px">${NOTIF_TIPO_ICONS[a.tipo] || 'ℹ️'}</span>
        <div style="flex:1;min-width:0">
          <div style="font-size:12.5px;font-weight:700;color:var(--crm-text)">${escHtml(a.titulo)}</div>
          ${a.cuerpo ? `<div style="font-size:11.5px;color:var(--crm-muted);margin-top:2px">${escHtml(a.cuerpo)}</div>` : ''}
        </div>
        ${a.badge ? `<span style="width:8px;height:8px;border-radius:50%;background:${NOTIF_BADGE_COLORS[a.badge]||'var(--crm-info)'};flex-shrink:0;margin-top:4px"></span>` : ''}
      </a>`).join('');
  }

  // User notifications from DB
  if (notifs.length) {
    html += '<div style="font-size:10px;font-weight:700;color:var(--crm-muted);text-transform:uppercase;letter-spacing:.6px;padding:6px 8px 4px;margin-top:4px">Mis notificaciones</div>';
    html += notifs.map(n => `
      <div class="crm-notif-item ${n.leido ? '' : 'unread'}" data-id="${n.id}"
        style="display:flex;align-items:flex-start;gap:10px;padding:10px;border-radius:9px;cursor:pointer;margin-bottom:2px;background:${n.leido ? 'transparent' : 'rgba(124,58,237,.04)'};border:1px solid ${n.leido ? 'transparent' : 'rgba(124,58,237,.1)'}"
        onclick="notifClick(${n.id}, '${(n.url_accion||'').replace(/'/g,"\\'")}')">
        <span style="font-size:15px;flex-shrink:0;margin-top:1px">${NOTIF_TIPO_ICONS[n.tipo] || 'ℹ️'}</span>
        <div style="flex:1;min-width:0">
          <div style="font-size:12.5px;font-weight:${n.leido ? '500' : '700'};color:var(--crm-text)">${escHtml(n.titulo)}</div>
          ${n.cuerpo ? `<div style="font-size:11.5px;color:var(--crm-muted);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(n.cuerpo)}</div>` : ''}
          <div style="font-size:10.5px;color:var(--crm-muted);margin-top:3px">${fmtNotifDate(n.creado_en)}</div>
        </div>
        ${!n.leido ? '<span style="width:7px;height:7px;border-radius:50%;background:var(--crm-primary);flex-shrink:0;margin-top:5px"></span>' : ''}
      </div>`).join('');
  }

  if (!alerts.length && !notifs.length) {
    html = '<div style="text-align:center;padding:32px 16px;color:var(--crm-muted)"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 10px;opacity:.4"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg><p style="font-size:13px;margin:0">Sin notificaciones nuevas</p></div>';
  }

  list.innerHTML = html;
}

async function notifClick(id, url) {
  await fetch(`${window.CRM_API_URL}&action=marcar_notif_leida`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  });
  const el = document.querySelector(`.crm-notif-item[data-id="${id}"]`);
  if (el) { el.style.background = 'transparent'; el.style.border = '1px solid transparent'; el.querySelector('span:last-child')?.remove(); }
  updateNotifBadge(-1);
  if (url) { toggleNotifPanel(); window.location.href = url; }
}

async function marcarTodasLeidas() {
  await fetch(`${window.CRM_API_URL}&action=marcar_todas_leidas`, { method: 'POST', headers: {'Content-Type':'application/json'}, body: '{}' });
  document.querySelectorAll('.crm-notif-item.unread').forEach(el => {
    el.classList.remove('unread');
    el.style.background = 'transparent'; el.style.border = '1px solid transparent';
    el.querySelector('span:last-child')?.remove();
    el.querySelector('div:first-of-type div')?.style.setProperty('font-weight','500');
  });
  const badge = document.getElementById('notifBadge');
  badge.style.display = 'none';
}

function updateNotifBadge(delta) {
  const badge = document.getElementById('notifBadge');
  const cur   = parseInt(badge.textContent) || 0;
  const next  = Math.max(0, cur + delta);
  badge.textContent = next > 99 ? '99+' : String(next);
  badge.style.display = next > 0 ? 'flex' : 'none';
}

function fmtNotifDate(dt) {
  if (!dt) return '';
  const d = new Date(dt.replace(' ', 'T'));
  const diff = Date.now() - d.getTime();
  if (diff < 60000)   return 'Ahora mismo';
  if (diff < 3600000) return Math.floor(diff/60000) + ' min';
  if (diff < 86400000) return Math.floor(diff/3600000) + 'h';
  return d.toLocaleDateString('es-ES', { day:'2-digit', month:'short' });
}

function escHtml(str) {
  const d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML;
}

// Auto-refresh badge every 60 seconds (just unread count)
(async function initNotifBadge() {
  try {
    const res = await fetch(`${window.CRM_API_URL}&action=get_crm_notifs`).then(r=>r.json());
    if (res.ok && res.unread > 0) {
      const badge = document.getElementById('notifBadge');
      badge.style.display = 'flex';
      badge.textContent = res.unread > 99 ? '99+' : String(res.unread);
    }
  } catch(e) {}
})();
setInterval(async () => {
  if (notifPanelOpen) return;
  try {
    const res = await fetch(`${window.CRM_API_URL}&action=get_crm_notifs`).then(r=>r.json());
    if (res.ok) {
      const badge = document.getElementById('notifBadge');
      if (res.unread > 0) { badge.style.display = 'flex'; badge.textContent = res.unread > 99 ? '99+' : String(res.unread); }
      else badge.style.display = 'none';
    }
  } catch(e) {}
}, 60000);

/* Global keyboard shortcuts */
let lastKey = '', lastKeyTime = 0;
document.addEventListener('keydown', function(e) {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
  const now = Date.now();
  if (e.key === '/') { e.preventDefault(); document.getElementById('headerSearch')?.focus(); return; }
  if (e.key === 'g' || (now - lastKeyTime < 800 && lastKey === 'g')) {
    const map = { 'd':'dashboard','u':'usuarios','c':'cursos','p':'campanas','m':'comunicacion','s':'ajustes' };
    if (lastKey === 'g' && map[e.key]) { window.location.href = window.CRM_NAV_BASE + map[e.key]; }
  }
  lastKey = e.key; lastKeyTime = now;
});
</script>

</body>
</html>
