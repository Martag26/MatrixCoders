<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logged = !empty($_SESSION['usuario_id']);
$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
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
                <a href="<?= BASE_URL ?>/index.php?url=carrito" aria-label="carrito">
                    <img src="<?= BASE_URL ?>/img/carrito-de-compras.png" alt="cesta">
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
                <div class="d-none d-md-flex align-items-center gap-2">
                    <span style="font-weight:600;"><?= htmlspecialchars($nombre) ?></span>

                    <a href="<?= BASE_URL ?>/index.php?url=perfil" aria-label="perfil">
                        <img src="<?= BASE_URL ?>/img/usuario.png" alt="perfil usuario" width="26" height="26">
                    </a>
                </div>

                <a class="d-md-none" href="<?= BASE_URL ?>/index.php?url=perfil" aria-label="perfil">
                    <img src="<?= BASE_URL ?>/img/usuario.png" alt="perfil usuario" width="26" height="26">
                </a>
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
        <?php endif; ?>
    </div>
</div>