<?php

/**
 * Layout parcial: cabecera (header) de la aplicación.
 *
 * Se incluye en todas las vistas. Comprueba el estado de sesión del usuario
 * y renderiza el menú de navegación adaptándose a si el usuario está
 * autenticado o no. También incluye el menú lateral para dispositivos móviles.
 */

// Iniciar sesión solo si no hay ninguna activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determinar si el usuario ha iniciado sesión y obtener su nombre
$logged = !empty($_SESSION['usuario_id']) && ($_SESSION['usuario_rol'] ?? '') === 'USUARIO';
$nombre = trim((string)($_SESSION['usuario_nombre'] ?? 'Usuario'));
if ($nombre === '') {
    $nombre = 'Usuario';
}
$nombreMenu = function_exists('mb_convert_case')
    ? mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8')
    : ucwords(strtolower($nombre));
?>

<style>
/* ── Bell button ── */
.notif-bell-wrap{position:relative;display:inline-flex}
.notif-bell-btn{width:36px;height:36px;border-radius:10px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;color:#6b7280;padding:0}
.notif-bell-btn:hover{background:#f3f4f6;color:#374151}
.notif-bell-badge{position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;font-size:9px;font-weight:800;min-width:18px;height:18px;border-radius:99px;padding:0 4px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;line-height:1;opacity:0;transform:scale(0);transition:opacity .2s,transform .25s cubic-bezier(.34,1.56,.64,1)}
.notif-bell-badge.visible{opacity:1;transform:scale(1)}

/* ── Dropdown ── */
.notif-dropdown{position:absolute;top:calc(100% + 10px);right:0;width:348px;background:#fff;border-radius:18px;border:1px solid #e5e7eb;box-shadow:0 20px 50px rgba(15,23,42,.14);z-index:9999;opacity:0;pointer-events:none;transform:translateY(-8px);transition:opacity .18s ease,transform .18s ease;display:flex;flex-direction:column;overflow:hidden}
.notif-dropdown.open{opacity:1;pointer-events:auto;transform:translateY(0)}

/* ── Header ── */
.nd-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px 14px;border-bottom:1px solid #f3f4f6}
.nd-head-title{font-size:15px;font-weight:800;color:#111827;margin:0}
.nd-head-right{display:flex;align-items:center;gap:8px}
.nd-unread-pill{background:#ef4444;color:#fff;font-size:.62rem;font-weight:800;border-radius:99px;padding:2px 8px;display:none;letter-spacing:.2px}
.nd-unread-pill.on{display:inline-block}
.nd-mark-all{font-size:.72rem;font-weight:700;color:#6b7280;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:7px;transition:color .12s,background .12s}
.nd-mark-all:hover{color:#111827;background:#f3f4f6}

/* ── List ── */
.nd-list{flex:1;overflow-y:auto;scrollbar-width:thin;scrollbar-color:#e5e7eb transparent;max-height:340px}
.nd-list::-webkit-scrollbar{width:3px}
.nd-list::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:99px}

/* Section label */
.nd-section{padding:10px 16px 4px;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af}

/* ── Item ── */
.nd-item{display:flex;align-items:flex-start;gap:11px;padding:11px 16px;cursor:pointer;transition:background .12s;text-decoration:none;color:inherit;position:relative;border-bottom:1px solid #f9fafb}
.nd-item:last-child{border-bottom:none}
.nd-item:hover{background:#f9fafb;color:inherit}
.nd-item.unread{background:#f8fdf9}
.nd-item.unread::before{content:'';position:absolute;left:0;top:8px;bottom:8px;width:3px;background:#6B8F71;border-radius:0 3px 3px 0}
.nd-item.unread:hover{background:#f0faf1}

/* Icon chip */
.nd-icon-chip{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* Body */
.nd-body{flex:1;min-width:0;padding-top:1px}
.nd-title{font-size:13px;font-weight:600;color:#111827;line-height:1.4;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.nd-item:not(.unread) .nd-title{font-weight:500;color:#4b5563}
.nd-sub{font-size:.72rem;color:#6b7280;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0 0 3px}
.nd-time{font-size:.67rem;color:#9ca3af;display:block}

/* ── Empty state ── */
.nd-empty{padding:36px 24px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:0}
.nd-empty-icon{width:56px;height:56px;border-radius:16px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.nd-empty-title{font-size:.9rem;font-weight:800;color:#111827;margin:0 0 4px}
.nd-empty-sub{font-size:.75rem;color:#9ca3af;margin:0;line-height:1.5}

/* ── Footer ── */
.nd-footer{display:grid;grid-template-columns:1fr 1fr;border-top:1px solid #f3f4f6;background:#fafafa}
.nd-footer-link{display:flex;align-items:center;justify-content:center;gap:6px;padding:12px 10px;font-size:13px;font-weight:700;color:#6b7280;text-decoration:none;transition:background .12s,color .12s}
.nd-footer-link:first-child{border-right:1px solid #f3f4f6}
.nd-footer-link:hover{background:#f3f4f6;color:#111827}
.nd-footer-link.primary{color:#6B8F71}
.nd-footer-link.primary:hover{background:#f0fdf4;color:#4a6b50}
</style>

<header>
    <div class="header-wrap">

        <!-- IZQUIERDA: logo y navegación principal -->
        <div class="header-left">
            <a class="header-logo" href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/img/logo.png" alt="logo">
            </a>

            <!-- Menú de navegación principal (visible en escritorio) -->
            <nav class="header-nav">
                <a href="<?= BASE_URL ?>/index.php?url=dashboard">Espacio de trabajo</a>
                <a href="<?= BASE_URL ?>/index.php?url=suscripciones">Precios y planes de subscripción</a>
            </nav>
        </div>

        <!-- DERECHA: iconos de acción y controles de sesión -->
        <div class="header-right">

            <!-- Iconos de notificaciones y carrito (ocultos en móvil) -->
            <div class="header-icons d-none d-sm-flex">
                <?php if ($logged): ?>
                <!-- Notification bell with dropdown -->
                <div class="notif-bell-wrap" id="notifWrap">
                    <button class="notif-bell-btn" id="notifBtn" aria-label="notificaciones" onclick="toggleNotifPanel(event)">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
                        <span class="notif-bell-badge" id="notifBadge"></span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="nd-head">
                            <p class="nd-head-title">Notificaciones</p>
                            <div class="nd-head-right">
                                <span class="nd-unread-pill" id="ndUnreadPill"></span>
                                <button class="nd-mark-all" onclick="marcarTodasLeidas()">Marcar leídas</button>
                            </div>
                        </div>
                        <div class="nd-list" id="notifList">
                            <div class="nd-empty">
                                <div class="nd-empty-icon">
                                    <svg width="24" height="24" fill="none" stroke="#6B8F71" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
                                </div>
                                <p class="nd-empty-title">Cargando…</p>
                                <p class="nd-empty-sub">Obteniendo tus notificaciones</p>
                            </div>
                        </div>
                        <div class="nd-footer">
                            <a href="<?= BASE_URL ?>/index.php?url=buzon" class="nd-footer-link">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Buzón
                            </a>
                            <a href="<?= BASE_URL ?>/index.php?url=notificaciones" class="nd-footer-link primary">
                                Ver todas
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php?url=login" class="notif-bell-btn" aria-label="notificaciones">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
                </a>
                <?php endif; ?>
                <!-- Carrito (mismo estilo que campana) -->
                <?php $totalCarrito = !empty($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0; ?>
                <div class="notif-bell-wrap">
                    <a href="<?= BASE_URL ?>/index.php?url=carrito" class="notif-bell-btn" aria-label="carrito">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="notif-bell-badge carrito-badge <?= $totalCarrito > 0 ? 'visible' : '' ?>" id="carritoBadge"><?= $totalCarrito > 0 ? $totalCarrito : '' ?></span>
                    </a>
                </div>
            </div>

            <?php if (!$logged): ?>
                <!-- Usuario NO autenticado: mostrar botones de login y registro -->
                <div class="header-auth d-none d-md-flex">
                    <a href="<?= BASE_URL ?>/index.php?url=login" id="inicioSesion">Iniciar sesión</a>
                    <a class="btn-mc" href="<?= BASE_URL ?>/index.php?url=register">Registrarse</a>
                </div>

                <!-- Icono de perfil que lleva al login en móvil -->
                <a href="<?= BASE_URL ?>/index.php?url=login" aria-label="perfil">
                    <img src="<?= BASE_URL ?>/img/usuario.png" alt="perfil usuario" width="26" height="26">
                </a>
            <?php else: ?>
                <!-- LOGUEADO: desktop -->
                <div class="dropdown d-none d-md-block user-menu">
                    <button class="user-menu-trigger" type="button"
                        data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false">
                        <span class="user-menu-avatar">
                            <?= mb_strtoupper(mb_substr($nombreMenu, 0, 1, 'UTF-8'), 'UTF-8') ?>
                        </span>
                        <span class="user-menu-name"><?= htmlspecialchars($nombreMenu) ?></span>
                        <span class="user-menu-chevron">▾</span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                        <!-- Cabecera con nombre -->
                        <li class="user-dropdown-header">
                            <p class="udh-name"><?= htmlspecialchars($nombreMenu) ?></p>
                            <span class="udh-label">Cuenta personal</span>
                        </li>

                        <!-- Mi perfil -->
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=perfil">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5.121 17.804A8 8 0 0112 15a8 8 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Mi perfil
                            </a>
                        </li>

                        <!-- Ajustes -->
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=ajustes">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Ajustes
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- Cerrar sesión -->
                        <li>
                            <a class="dropdown-item item-danger" href="<?= BASE_URL ?>/index.php?url=logout">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                                </svg>
                                Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- LOGUEADO: móvil -->
                <div class="dropdown d-md-none">
                    <button class="user-menu-trigger" type="button"
                        data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false">
                        <span class="user-menu-avatar">
                            <?= mb_strtoupper(mb_substr($nombreMenu, 0, 1, 'UTF-8'), 'UTF-8') ?>
                        </span>
                        <span class="user-menu-name"><?= htmlspecialchars($nombreMenu) ?></span>
                        <span class="user-menu-chevron">▾</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                        <li class="user-dropdown-header">
                            <p class="udh-name"><?= htmlspecialchars($nombreMenu) ?></p>
                            <span class="udh-label">Cuenta personal</span>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=perfil">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5.121 17.804A8 8 0 0112 15a8 8 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Mi perfil
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item item-danger" href="<?= BASE_URL ?>/index.php?url=logout">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                                </svg>
                                Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Botón que abre el menú lateral (offcanvas) en dispositivos móviles -->
            <button class="btn btn-outline-secondary btn-sm d-md-none"
                data-bs-toggle="offcanvas" data-bs-target="#menuMobile">
                Menú
            </button>

        </div>
    </div>
</header>

<?php if ($logged): ?>
<script>
(function(){
  const BASE  = '<?= BASE_URL ?>';
  const badge = document.getElementById('notifBadge');
  const pill  = document.getElementById('ndUnreadPill');
  const list  = document.getElementById('notifList');
  const panel = document.getElementById('notifDropdown');
  if (!badge) return;

  let notifData = [];

  // Configuración visual por tipo
  const TIPOS = {
    tarea:             { color:'#d97706', bg:'#fffbeb', border:'#fde68a', label:'Tarea',     icon:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    tarea_vencida:     { color:'#dc2626', bg:'#fef2f2', border:'#fecaca', label:'Vencida',   icon:'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    expiracion:        { color:'#ea580c', bg:'#fff7ed', border:'#fed7aa', label:'Expira',    icon:'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    evento_calendario: { color:'#059669', bg:'#f0fdf4', border:'#bbf7d0', label:'Evento',    icon:'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    crm:               { color:'#7c3aed', bg:'#f5f3ff', border:'#ddd6fe', label:'Campaña',  icon:'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z' },
    mensaje:           { color:'#2563eb', bg:'#eff6ff', border:'#bfdbfe', label:'Mensaje',  icon:'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    info:              { color:'#6b7280', bg:'#f9fafb', border:'#e5e7eb', label:'Info',     icon:'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  };
  const DEF = TIPOS.info;

  function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') }

  function timeAgo(str) {
    if (!str) return '';
    const diff = Math.floor((Date.now() - new Date(str)) / 1000);
    if (diff < 60)    return 'ahora';
    if (diff < 3600)  return `${Math.floor(diff/60)}m`;
    if (diff < 86400) return `${Math.floor(diff/3600)}h`;
    return `${Math.floor(diff/86400)}d`;
  }

  function renderList(items) {
    if (!items.length) {
      list.innerHTML = `
        <div class="nd-empty">
          <div class="nd-empty-icon">
            <svg width="26" height="26" fill="none" stroke="#6B8F71" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
          </div>
          <p class="nd-empty-title">Todo al día</p>
          <p class="nd-empty-sub">No tienes notificaciones pendientes</p>
        </div>`;
      return;
    }

    const unread  = items.filter(n => !n.leido);
    const read    = items.filter(n =>  n.leido);
    let html = '';

    if (unread.length) {
      html += `<div class="nd-section">Sin leer</div>`;
      html += unread.slice(0, 5).map(n => itemHtml(n)).join('');
    }
    if (read.length) {
      const visibles = read.slice(0, unread.length ? 3 : 6);
      if (visibles.length) {
        html += `<div class="nd-section">Recientes</div>`;
        html += visibles.map(n => itemHtml(n)).join('');
      }
    }

    list.innerHTML = html;
  }

  function itemHtml(n) {
    const t   = TIPOS[n.tipo] || DEF;
    const url = (n.url_accion || '').replace(/'/g, "\\'");
    const titulo = n.titulo || n.cuerpo || 'Sin título';
    return `
      <a class="nd-item ${!n.leido ? 'unread' : ''}"
         href="javascript:void(0)"
         onclick="leerNotif(${n.id},'${url}')">
        <div class="nd-icon-chip" style="background:${t.bg};border:1px solid ${t.border}">
          <svg width="15" height="15" fill="none" stroke="${t.color}" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="${t.icon}"/></svg>
        </div>
        <div class="nd-body">
          <div class="nd-title">${esc(titulo)}</div>
          ${n.cuerpo && n.cuerpo !== titulo ? `<div class="nd-sub">${esc(n.cuerpo)}</div>` : ''}
          <span class="nd-time">${timeAgo(n.creado_en)}</span>
        </div>
      </a>`;
  }

  async function fetchNotifs() {
    try {
      const r = await fetch(`${BASE}/index.php?url=api-notificaciones&action=list`);
      const d = await r.json();
      notifData = d.notificaciones || [];
      const count = d.no_leidas || 0;

      // Badge campana
      if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.add('visible');
      } else {
        badge.classList.remove('visible');
      }

      // Pill en header oscuro
      if (pill) {
        if (count > 0) { pill.textContent = count + ' nueva' + (count > 1 ? 's' : ''); pill.classList.add('on'); }
        else           { pill.classList.remove('on'); }
      }

      if (panel.classList.contains('open')) renderList(notifData);
    } catch(e) {}
  }

  window.leerNotif = async function(id, url) {
    try {
      await fetch(`${BASE}/index.php?url=api-notificaciones`, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=leer&id=${id}`
      });
    } catch(e) {}
    if (url) { window.location.href = url; return; }
    fetchNotifs();
  };

  window.marcarTodasLeidas = async function() {
    try {
      await fetch(`${BASE}/index.php?url=api-notificaciones`, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=leer-todas'
      });
    } catch(e) {}
    badge.classList.remove('visible');
    if (pill) pill.classList.remove('on');
    notifData = notifData.map(n => ({...n, leido: 1}));
    renderList(notifData);
  };

  window.toggleNotifPanel = function(e) {
    e.stopPropagation();
    const open = panel.classList.toggle('open');
    if (open) renderList(notifData);
  };

  document.addEventListener('click', e => {
    if (!document.getElementById('notifWrap')?.contains(e.target)) {
      panel.classList.remove('open');
    }
  });

  fetchNotifs();
  setInterval(fetchNotifs, 60000);
})();
</script>
<?php endif; ?>

<!-- Menú lateral deslizante para navegación en dispositivos móviles (Bootstrap Offcanvas) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="menuMobile">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Navegación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column gap-2">
        <a href="<?= BASE_URL ?>/index.php">Inicio</a>
        <a href="<?= BASE_URL ?>/index.php?url=dashboard">Espacio de trabajo</a>
        <a href="<?= BASE_URL ?>/index.php?url=suscripciones">Precios y planes</a>
        <a href="<?= BASE_URL ?>/index.php?url=carrito">Carrito</a>
        <a href="<?= BASE_URL ?>/index.php?url=perfil">Perfil</a>

        <?php if (!$logged): ?>
            <!-- Opciones de autenticación solo si el usuario no está logueado -->
            <hr>
            <a href="<?= BASE_URL ?>/index.php?url=login">Iniciar sesión</a>
            <a href="<?= BASE_URL ?>/index.php?url=register">Registrarse</a>
        <?php else: ?>
            <hr>
            <a href="<?= BASE_URL ?>/index.php?url=logout">Cerrar sesión</a>
        <?php endif; ?>
    </div>
</div>
