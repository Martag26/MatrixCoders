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
$logged = !empty($_SESSION['usuario_id']);
$nombre = trim((string)($_SESSION['usuario_nombre'] ?? 'Usuario'));
if ($nombre === '') {
    $nombre = 'Usuario';
}
$nombreMenu = function_exists('mb_convert_case')
    ? mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8')
    : ucwords(strtolower($nombre));
?>

<style>
.notif-bell-wrap{position:relative;display:inline-flex}
.notif-bell-btn{width:30px;height:30px;border-radius:9px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;color:#374151;padding:0}
.notif-bell-btn:hover{background:#f3f4f6}
.notif-bell-badge{position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;font-size:9px;font-weight:700;min-width:16px;height:16px;border-radius:99px;padding:0 3px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;line-height:1;opacity:0;transform:scale(0);transition:opacity .2s,transform .2s}
.notif-bell-badge.visible{opacity:1;transform:scale(1)}
.notif-dropdown{position:absolute;top:calc(100% + 12px);right:-10px;width:340px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.16);z-index:9999;opacity:0;pointer-events:none;transform:translateY(-8px);transition:opacity .18s,transform .18s}
.notif-dropdown.open{opacity:1;pointer-events:auto;transform:translateY(0)}
.notif-dropdown-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px 10px;border-bottom:1px solid #f0f0f0}
.notif-dropdown-title{font-size:14px;font-weight:700;color:#111}
.notif-mark-all{font-size:11px;color:#7c3aed;background:none;border:none;cursor:pointer;padding:0}
.notif-mark-all:hover{text-decoration:underline}
.notif-list{max-height:320px;overflow-y:auto}
.notif-item{display:flex;gap:10px;padding:11px 16px;border-bottom:1px solid #f9f9f9;cursor:pointer;transition:background .1s}
.notif-item:hover{background:#fafafa}
.notif-item.unread{background:#f5f3ff}
.notif-item.unread:hover{background:#ede9fe}
.notif-item-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:4px}
.notif-item-dot.crm{background:#7c3aed}
.notif-item-dot.tarea{background:#f59e0b}
.notif-item-dot.expiracion{background:#ef4444}
.notif-item-dot.mensaje{background:#3b82f6}
.notif-item-dot.info{background:#6b7280}
.notif-item-body{flex:1;min-width:0}
.notif-item-title{font-size:12.5px;font-weight:600;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.notif-item-sub{font-size:11px;color:#6b7280;margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.notif-item-time{font-size:10px;color:#9ca3af;margin-top:3px}
.notif-empty{padding:28px 16px;text-align:center;color:#9ca3af;font-size:13px}
.notif-footer{padding:10px 16px;text-align:center;border-top:1px solid #f0f0f0}
.notif-footer a{font-size:12px;color:#7c3aed;text-decoration:none;font-weight:500}
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
                        <div class="notif-dropdown-header">
                            <span class="notif-dropdown-title">Notificaciones</span>
                            <button class="notif-mark-all" onclick="marcarTodasLeidas()">Marcar todas como leídas</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <div class="notif-empty">Cargando…</div>
                        </div>
                        <div class="notif-footer">
                            <a href="<?= BASE_URL ?>/index.php?url=notificaciones">Ver todas las notificaciones →</a>
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
                        data-bs-toggle="dropdown" aria-expanded="false">
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
                        data-bs-toggle="dropdown" aria-expanded="false">
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
  const BASE = '<?= BASE_URL ?>';
  const badge = document.getElementById('notifBadge');
  const list  = document.getElementById('notifList');
  const panel = document.getElementById('notifDropdown');
  if (!badge) return;

  let loaded = false;
  let notifData = [];

  function timeAgo(str) {
    if (!str) return '';
    const diff = Math.floor((Date.now() - new Date(str)) / 1000);
    if (diff < 60) return 'ahora mismo';
    if (diff < 3600) return `hace ${Math.floor(diff/60)}m`;
    if (diff < 86400) return `hace ${Math.floor(diff/3600)}h`;
    return `hace ${Math.floor(diff/86400)}d`;
  }

  function renderList(items) {
    if (!items.length) { list.innerHTML = '<div class="notif-empty">No tienes notificaciones</div>'; return; }
    list.innerHTML = items.slice(0,10).map(n => `
      <div class="notif-item ${!n.leido?'unread':''}" onclick="leerNotif(${n.id}, '${n.url_accion||''}')">
        <div class="notif-item-dot ${n.tipo}"></div>
        <div class="notif-item-body">
          <div class="notif-item-title">${n.titulo.replace(/</g,'&lt;')}</div>
          ${n.cuerpo ? `<div class="notif-item-sub">${n.cuerpo.replace(/</g,'&lt;')}</div>` : ''}
          <div class="notif-item-time">${timeAgo(n.creado_en)}</div>
        </div>
      </div>`).join('');
  }

  async function fetchNotifs() {
    try {
      const r = await fetch(`${BASE}/index.php?url=api-notificaciones&action=list`);
      const d = await r.json();
      notifData = d.notificaciones || [];
      const count = d.no_leidas || 0;
      if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.add('visible');
      } else {
        badge.classList.remove('visible');
      }
      if (panel.classList.contains('open')) renderList(notifData);
    } catch(e) {}
  }

  window.leerNotif = async function(id, url) {
    await fetch(`${BASE}/index.php?url=api-notificaciones`, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`action=leer&id=${id}` });
    if (url) { window.location.href = url; return; }
    fetchNotifs();
  };

  window.marcarTodasLeidas = async function() {
    await fetch(`${BASE}/index.php?url=api-notificaciones`, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=leer-todas' });
    badge.classList.remove('visible');
    fetchNotifs();
  };

  window.toggleNotifPanel = function(e) {
    e.stopPropagation();
    const open = panel.classList.toggle('open');
    if (open && !loaded) { loaded = true; renderList(notifData); }
    if (open) renderList(notifData);
  };

  document.addEventListener('click', e => {
    if (!document.getElementById('notifWrap')?.contains(e.target)) panel.classList.remove('open');
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