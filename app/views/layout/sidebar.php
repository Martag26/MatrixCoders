<?php
// Shared sidebar — incluir en todas las vistas del área privada
$currentUrl = $_GET['url'] ?? 'dashboard';
$isWorkspace = in_array($currentUrl, ['dashboard', 'nuevo-documento', 'plantillas-documento'], true);
$isNube = in_array($currentUrl, ['nube', 'mis-documentos', 'documento'], true);
?>
<aside class="barra-herramientas">
    <h3>BARRA DE HERRAMIENTAS</h3>
    <ul class="menu-lateral">
        <li>
            <a href="<?= BASE_URL ?>/index.php?url=dashboard"
                class="<?= $isWorkspace ? 'activo' : '' ?>">
                <img src="<?= BASE_URL ?>/img/hogar.png" alt="" class="icono-menu">
                Mi espacio de trabajo
            </a>
        </li>
        <li>
            <a href="#"
                onclick="return false;"
                class="<?= $currentUrl === 'buzon' ? 'activo' : '' ?>">
                <img src="<?= BASE_URL ?>/img/bandeja-de-entrada.png" alt="" class="icono-menu">
                Buzón de entrada
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/index.php?url=nube"
                class="<?= $isNube ? 'activo' : '' ?>">
                <img src="<?= BASE_URL ?>/img/subir.png" alt="" class="icono-menu">
                Nube
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
</aside>
