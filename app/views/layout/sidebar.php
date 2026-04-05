<?php
// Shared sidebar — incluir en todas las vistas del área privada
$currentUrl = $_GET['url'] ?? 'dashboard';
?>
<aside class="barra-herramientas">
    <h3>BARRA DE HERRAMIENTAS</h3>
    <ul class="menu-lateral">
        <li>
            <a href="<?= BASE_URL ?>/index.php?url=dashboard"
                class="<?= $currentUrl === 'dashboard' ? 'activo' : '' ?>">
                <img src="<?= BASE_URL ?>/img/hogar.png" alt="" class="icono-menu">
                Mi espacio de trabajo
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?url=buzon"
                class="<?= $currentUrl === 'buzon' ? 'activo' : '' ?>">
                <img src="<?= BASE_URL ?>/img/bandeja-de-entrada.png" alt="" class="icono-menu">
                Buzón de entrada
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?url=lecciones"
                class="<?= $currentUrl === 'lecciones' ? 'activo' : '' ?>">
                <img src="<?= BASE_URL ?>/img/leccion.png" alt="" class="icono-menu">
                Lecciones
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?url=tareas"
                class="<?= $currentUrl === 'tareas' ? 'activo' : '' ?>">
                <img src="<?= BASE_URL ?>/img/portapapeles.png" alt="" class="icono-menu">
                Tareas
            </a>
        </li>
    </ul>
    <a class="cerrar-sesion" href="<?= BASE_URL ?>/index.php?url=logout">
        <img src="<?= BASE_URL ?>/img/cerrar-sesion.png" alt="cerrar" class="icono-cerrar">
        Cerrar sesión
    </a>
</aside>