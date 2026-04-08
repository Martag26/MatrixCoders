<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

        <!-- IZQUIERDA -->
        <div class="header-left">
            <a class="header-logo" href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/img/logo.png" alt="logo">
            </a>

            <!-- NAV -->
            <nav class="header-nav">
                <a href="<?= BASE_URL ?>/index.php?url=dashboard">Espacio de trabajo</a>
                <a href="<?= BASE_URL ?>/index.php?url=suscripciones">Precios y planes de subscripción</a>
            </nav>
        </div>

        <!-- DERECHA -->
        <div class="header-right">

            <!-- Iconos -->
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
                <!-- NO LOGUEADO -->
                <div class="header-auth d-none d-md-flex">
                    <a href="<?= BASE_URL ?>/index.php?url=login" id="inicioSesion">Iniciar sesión</a>
                    <a class="btn-mc" href="<?= BASE_URL ?>/index.php?url=register">Registrarse</a>
                </div>

                <a href="<?= BASE_URL ?>/index.php?url=login" aria-label="perfil">
                    <img src="<?= BASE_URL ?>/img/usuario.png" alt="perfil usuario" width="26" height="26">
                </a>
            <?php else: ?>
                <!-- LOGUEADO -->
                <div class="dropdown d-none d-md-block user-menu">
                    <button class="user-menu-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="user-menu-meta">
                            <span class="user-menu-label">Mi cuenta</span>
                            <span class="user-menu-name"><?= htmlspecialchars($nombreMenu) ?></span>
                        </span>
                        <span class="user-menu-avatar">
                            <img src="<?= BASE_URL ?>/img/usuario.png" alt="perfil usuario" width="26" height="26">
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=perfil">Mi perfil</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=logout">Cerrar sesión</a></li>
                    </ul>
                </div>

                <div class="dropdown d-md-none">
                    <button class="user-menu-trigger user-menu-trigger-mobile" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= BASE_URL ?>/img/usuario.png" alt="perfil usuario" width="26" height="26">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=perfil">Mi perfil</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?url=logout">Cerrar sesión</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Menú móvil -->
            <button class="btn btn-outline-secondary btn-sm d-md-none"
                data-bs-toggle="offcanvas" data-bs-target="#menuMobile">
                Menú
            </button>

        </div>
    </div>
</header>

<!-- Offcanvas móvil -->
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
            <hr>
            <a href="<?= BASE_URL ?>/index.php?url=login">Iniciar sesión</a>
            <a href="<?= BASE_URL ?>/index.php?url=register">Registrarse</a>
        <?php else: ?>
            <hr>
            <a href="<?= BASE_URL ?>/index.php?url=logout">Cerrar sesión</a>
        <?php endif; ?>
    </div>
</div>
