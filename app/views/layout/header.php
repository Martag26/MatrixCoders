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

            <!-- Iconos de ajustes, notificaciones y carrito (ocultos en móvil) -->
            <div class="header-icons d-none d-sm-flex">
                <a href="<?= BASE_URL ?>/index.php?url=ajustes" aria-label="ajustes">
                    <img src="<?= BASE_URL ?>/img/engranaje.png" alt="engranaje">
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=notificaciones" aria-label="notificaciones">
                    <img src="<?= BASE_URL ?>/img/campana.png" alt="campana">
                </a>
                <?php $totalCarrito = !empty($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0; ?>
                <a href="<?= BASE_URL ?>/index.php?url=carrito" aria-label="carrito" style="position:relative;">
                    <img src="<?= BASE_URL ?>/img/carrito-de-compras.png" alt="cesta">
                    <?php if ($totalCarrito > 0): ?>
                        <span class="carrito-badge"><?= $totalCarrito ?></span>
                    <?php endif; ?>
                </a>
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