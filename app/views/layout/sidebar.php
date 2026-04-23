<?php
// Shared sidebar — incluido en todas las vistas del área privada
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUrl     = $_GET['url'] ?? 'dashboard';
$isWorkspace   = in_array($currentUrl, ['dashboard'], true);
$isNube             = in_array($currentUrl, ['nube', 'mis-documentos', 'documento'], true);
$isCalendario  = in_array($currentUrl, ['calendario'], true);
$isCuenta    = in_array($currentUrl, ['perfil', 'ajustes'], true);
?>
<aside class="sidebar" id="mainSidebar">

    <button class="sidebar__toggle" id="sidebarToggle" title="Colapsar menú" aria-label="Colapsar menú">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <nav class="sidebar__nav">
        <ul class="sidebar__list">

            <li>
                <a href="<?= BASE_URL ?>/index.php?url=dashboard"
                   class="sidebar__link <?= $isWorkspace ? 'active' : '' ?>"
                   title="Mi espacio de trabajo">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span class="sidebar__label">Mi espacio</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/index.php?url=nube"
                   class="sidebar__link <?= $isNube ? 'active' : '' ?>"
                   title="Mis documentos en la nube">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="16 16 12 12 8 16"/>
                        <line x1="12" y1="12" x2="12" y2="21"/>
                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                    </svg>
                    <span class="sidebar__label">Nube</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/index.php?url=calendario"
                   class="sidebar__link <?= $isCalendario ? 'active' : '' ?>"
                   title="Planificador de estudio">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span class="sidebar__label">Calendario</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/index.php" class="sidebar__link" title="Buzón">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span class="sidebar__label">Buzón</span>
                </a>
            </li>

        </ul>
    </nav>

</aside>

<script>
(function () {
    const sidebar   = document.getElementById('mainSidebar');
    const toggle    = document.getElementById('sidebarToggle');
    const container = document.querySelector('.contenedor-dashboard, .contenedor-dashboard-content');
    const KEY       = 'mc_sidebar_col';

    function apply(col) {
        sidebar.classList.toggle('sidebar--collapsed', col);
        if (container) container.classList.toggle('sidebar--collapsed', col);
        localStorage.setItem(KEY, col ? '1' : '0');
    }

    apply(localStorage.getItem(KEY) === '1');
    toggle.addEventListener('click', () => apply(!sidebar.classList.contains('sidebar--collapsed')));
})();
</script>
