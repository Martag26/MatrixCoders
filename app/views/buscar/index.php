<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../helpers/curso_imagen.php';

// Helpers
$q             = $q             ?? '';
$filtroPrecio  = $filtroPrecio  ?? '';
$filtroNivel   = $filtroNivel   ?? '';
$filtroCategoria = $filtroCategoria ?? '';
$hayFiltros    = $hayFiltros    ?? false;

// Etiqueta de nivel → texto + color
function nivelLabel(string $nivel): array
{
    return match ($nivel) {
        'principiante'  => ['Fundamentos',       '#16a34a', '#dcfce7'],
        'estudiante'    => ['Ruta académica',     '#2563eb', '#dbeafe'],
        'profesional'   => ['Perfil profesional', '#ea580c', '#ffedd5'],
        default         => ['', '', ''],
    };
}

$ordenar = $ordenar ?? 'popular';

function buildUrl(array $overrides = []): string
{
    global $q, $filtroPrecio, $filtroNivel, $filtroCategoria, $pagina, $ordenar;
    $params = array_merge([
        'url'       => 'buscar',
        'q'         => $q,
        'precio'    => $filtroPrecio,
        'nivel'     => $filtroNivel,
        'categoria' => $filtroCategoria,
        'orden'     => $ordenar !== 'popular' ? $ordenar : '',
        'p'         => $pagina,
    ], $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return BASE_URL . '/index.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/inicio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-dark: #1B2336;
            --mc-border: #e5e7eb;
            --mc-soft: #f8fafc;
            --mc-muted: #6b7280;
            --mc-shadow: 0 4px 18px rgba(0, 0, 0, .07);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Saira', sans-serif;
        }

        .buscar-layout {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 28px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 18px 60px;
            align-items: start;
        }

        /* ── FILTROS PANEL ── */
        .filtros-panel {
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(15,23,42,.06);
            overflow: hidden;
            position: sticky;
            top: 20px;
        }

        .filtros-panel-head {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 14px 18px 12px;
            border-bottom: 1px solid var(--mc-border);
            background: #fafbfc;
        }

        .filtros-panel-head svg {
            color: var(--mc-muted);
        }

        .filtros-panel h3 {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--mc-muted);
            margin: 0;
        }

        .filtros-inner {
            padding: 16px 18px 18px;
        }

        .filtro-grupo {
            margin-bottom: 16px;
        }

        .filtro-grupo h4 {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .75rem;
            font-weight: 800;
            color: var(--mc-dark);
            margin: 0 0 8px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .filtro-grupo h4 svg {
            color: var(--mc-muted);
            flex-shrink: 0;
        }

        /* Dots de nivel */
        .nivel-dot-principiante { background: #16a34a; }
        .nivel-dot-estudiante   { background: #2563eb; }
        .nivel-dot-profesional  { background: #ea580c; }

        .filtro-opcion {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s;
            text-decoration: none;
            color: var(--mc-dark);
            font-size: .83rem;
        }

        .filtro-opcion:hover {
            background: var(--mc-soft);
        }

        .filtro-opcion.activo {
            background: #f0fdf4;
            color: var(--mc-green);
            font-weight: 700;
        }

        .filtro-opcion .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .filtro-sep {
            height: 1px;
            background: var(--mc-border);
            margin: 16px 0;
        }

        .limpiar-btn {
            display: block;
            text-align: center;
            font-size: .75rem;
            font-weight: 700;
            color: var(--mc-muted);
            text-decoration: none;
            padding: 6px;
            border-radius: 8px;
            border: 1px solid var(--mc-border);
        }

        .limpiar-btn:hover {
            color: var(--mc-dark);
            background: var(--mc-soft);
        }

        /* ── CONTENIDO ── */
        .buscar-main {
            min-width: 0;
        }

        /* Search bar */
        .search-wrap {
            position: relative;
            margin-bottom: 20px;
        }

        .search-hero-label {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--mc-muted);
            margin-bottom: 8px;
        }

        .hero-search {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 2px solid var(--mc-border);
            border-radius: 999px;
            padding: 10px 12px 10px 18px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .06);
            transition: border-color .18s, box-shadow .18s;
        }

        .hero-search:focus-within {
            border-color: var(--mc-green);
            box-shadow: 0 6px 24px rgba(107, 143, 113, .14);
        }

        .hero-search .icon {
            width: 17px;
            height: 17px;
            color: var(--mc-muted);
            flex-shrink: 0;
        }

        .hero-search input {
            border: none;
            outline: none;
            flex: 1;
            font-family: 'Saira', sans-serif;
            font-size: .95rem;
            color: var(--mc-dark);
            background: transparent;
        }

        .hero-search input::placeholder { color: #adb5bd; }

        .hero-search button {
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 9px 22px;
            font-family: 'Saira', sans-serif;
            font-weight: 700;
            font-size: .84rem;
            cursor: pointer;
            transition: background .15s, transform .1s;
            white-space: nowrap;
        }

        .hero-search button:hover {
            background: var(--mc-green-d);
            transform: translateY(-1px);
        }

        #sugerencias {
            display: none;
            background: #fff;
            border: 1px solid var(--mc-border);
            border-radius: 16px;
            list-style: none;
            padding: 4px 0;
            margin: 0;
            width: 100%;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            z-index: 9999;
        }

        #sugerencias li {
            padding: 10px 16px;
            cursor: pointer;
            font-size: .9rem;
            color: var(--mc-dark);
        }

        #sugerencias li:hover,
        #sugerencias li.activo {
            background: var(--mc-soft);
        }

        /* Filtros activos chips */
        .chips-activos {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 16px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .75rem;
            font-weight: 700;
            color: var(--mc-green-d);
            text-decoration: none;
        }

        .chip:hover {
            background: #dcfce7;
        }

        .chip .x {
            font-size: .8rem;
            color: var(--mc-muted);
        }

        /* Resultados header */
        .resultados-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .resultados-header h2 {
            font-size: .9rem;
            font-weight: 700;
            color: var(--mc-muted);
            margin: 0;
        }

        .resultados-header h2 em {
            color: var(--mc-dark);
            font-style: normal;
        }

        /* Sort */
        .sort-wrap {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
        }

        .sort-label {
            font-size: .75rem;
            font-weight: 700;
            color: var(--mc-muted);
            white-space: nowrap;
        }

        .sort-select {
            border: 1px solid var(--mc-border);
            border-radius: 10px;
            padding: 6px 28px 6px 10px;
            font-family: 'Saira', sans-serif;
            font-size: .78rem;
            font-weight: 600;
            color: var(--mc-dark);
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 8px center;
            -webkit-appearance: none;
            cursor: pointer;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .sort-select:focus {
            border-color: var(--mc-green);
            box-shadow: 0 0 0 3px rgba(107,143,113,.1);
        }

        /* Tarjetas */
        .course-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--mc-shadow);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s, transform .2s;
            cursor: pointer;
        }

        .course-card:hover {
            box-shadow: 0 8px 28px rgba(0, 0, 0, .13);
            transform: translateY(-3px);
        }

        .course-thumb {
            width: 100%;
            height: 160px;
            object-fit: cover;
            background: #e5e7eb;
            display: block;
        }

        .course-thumb-wrap {
            position: relative;
        }

        .course-thumb-empty {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, #e5e7eb, #f3f4f6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .card-body-inner {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        /* Tags row */
        .tags-row {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            align-items: center;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            font-size: .65rem;
            font-weight: 700;
            border-radius: 20px;
            padding: 2px 8px;
            border: 1px solid transparent;
        }

        /* Nivel badge inline */
        .nivel-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .63rem;
            font-weight: 800;
            border-radius: 20px;
            padding: 3px 9px;
            border: 1px solid;
            text-transform: uppercase;
            letter-spacing: .2px;
        }
        .nivel-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            flex-shrink: 0;
        }

        .course-badges-corner {
            position: absolute;
            top: 12px;
            right: 12px;
            display: grid;
            gap: 6px;
            justify-items: end;
        }

        .course-badge-corner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 6px 10px;
            border-radius: 10px;
            border: 1px solid transparent;
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(8px);
            font-size: .67rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
        }

        .course-badge-free {
            color: #047857;
            background: rgba(209, 250, 229, .96);
            border-color: #6ee7b7;
        }

        .course-title {
            font-size: .9rem;
            font-weight: 800;
            color: var(--mc-dark);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-desc {
            font-size: .78rem;
            color: var(--mc-muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 0;
        }

        .course-meta {
            font-size: .75rem;
            color: var(--mc-muted);
        }

        .card-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid var(--mc-border);
        }

        .course-price {
            font-size: .95rem;
            font-weight: 800;
            color: var(--mc-dark);
        }

        .course-price.gratis {
            color: var(--mc-green);
        }

        .course-price-wrap {
            display: grid;
            gap: 2px;
        }

        .course-price-note {
            font-size: .68rem;
            font-weight: 700;
            color: #047857;
        }

        .btn-carrito {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #d8e1da;
            background: #fff;
            color: var(--mc-dark);
            border-radius: 999px;
            padding: 8px 12px;
            font-size: .78rem;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        }

        .btn-carrito:hover {
            background: #f7faf8;
            border-color: rgba(107, 143, 113, .4);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
            transform: translateY(-1px);
        }

        .btn-carrito img {
            width: 14px;
            height: 14px;
            object-fit: contain;
            opacity: .85;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--mc-muted);
        }

        .empty-state .ei {
            font-size: 3rem;
            margin-bottom: 14px;
        }

        .empty-state h3 {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--mc-dark);
            margin-bottom: 6px;
        }

        .empty-state p {
            font-size: .88rem;
            margin-bottom: 16px;
        }

        .empty-state a {
            display: inline-block;
            padding: 9px 20px;
            background: var(--mc-green);
            color: #fff;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            font-size: .85rem;
        }

        .empty-state a:hover {
            background: var(--mc-green-d);
        }

        /* Paginación custom */
        .pag-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .pag-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 10px;
            border: 1px solid var(--mc-border);
            background: #fff;
            color: var(--mc-dark);
            font-size: .82rem;
            font-weight: 700;
            text-decoration: none;
            transition: border-color .15s, background .15s, color .15s, box-shadow .15s;
        }

        .pag-btn:hover:not(.pag-disabled):not(.pag-activo) {
            border-color: rgba(107,143,113,.4);
            background: #f7faf8;
            box-shadow: 0 4px 10px rgba(15,23,42,.06);
        }

        .pag-activo {
            background: var(--mc-green);
            border-color: var(--mc-green);
            color: #fff;
            box-shadow: 0 6px 16px rgba(107,143,113,.28);
        }

        .pag-disabled {
            opacity: .35;
            cursor: default;
            pointer-events: none;
        }

        .pag-arrow {
            padding: 0 8px;
        }

        .pag-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 36px;
            font-size: .82rem;
            color: var(--mc-muted);
        }

        .pag-info {
            font-size: .73rem;
            color: var(--mc-muted);
            margin-left: 8px;
        }

        /* Sección destacados title */
        .seccion-titulo {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--mc-dark);
            margin-bottom: 6px;
        }

        .seccion-sub {
            font-size: .85rem;
            color: var(--mc-muted);
            margin-bottom: 20px;
        }

        @media(max-width:768px) {
            .buscar-layout {
                grid-template-columns: 1fr;
            }

            .filtros-panel {
                position: static;
            }
        }
    </style>
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="buscar-layout">

        <!-- ── PANEL FILTROS ── -->
        <aside class="filtros-panel">
            <div class="filtros-panel-head">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                <h3>Filtros</h3>
                <?php $nFiltros = (int)($filtroPrecio !== '') + (int)($filtroNivel !== '') + (int)($filtroCategoria !== ''); ?>
                <?php if ($nFiltros > 0): ?>
                    <span style="margin-left:auto;background:var(--mc-green);color:#fff;font-size:.65rem;font-weight:800;border-radius:99px;padding:1px 7px;"><?= $nFiltros ?></span>
                <?php endif; ?>
            </div>
            <div class="filtros-inner">

            <!-- Precio -->
            <div class="filtro-grupo">
                <h4>
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v2m0 8v2M8.5 9.5A3.5 1.5 0 0 1 12 8a3.5 1.5 0 0 1 3.5 1.5 3.5 1.5 0 0 1-3.5 1.5 3.5 1.5 0 0 1-3.5 1.5A3.5 1.5 0 0 1 12 14"/></svg>
                    Precio
                </h4>
                <a class="filtro-opcion <?= $filtroPrecio === '' ? 'activo' : '' ?>" href="<?= buildUrl(['precio' => '', 'p' => 1]) ?>">
                    <span class="dot" style="background:#9ca3af"></span> Todos
                </a>
                <a class="filtro-opcion <?= $filtroPrecio === 'gratis' ? 'activo' : '' ?>" href="<?= buildUrl(['precio' => 'gratis', 'p' => 1]) ?>">
                    <span class="dot" style="background:#10b981"></span> Gratis
                </a>
                <a class="filtro-opcion <?= $filtroPrecio === 'pago' ? 'activo' : '' ?>" href="<?= buildUrl(['precio' => 'pago', 'p' => 1]) ?>">
                    <span class="dot" style="background:#f59e0b"></span> De pago
                </a>
            </div>

            <div class="filtro-sep"></div>

            <!-- Nivel -->
            <div class="filtro-grupo">
                <h4>
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Nivel
                </h4>
                <a class="filtro-opcion <?= $filtroNivel === '' ? 'activo' : '' ?>" href="<?= buildUrl(['nivel' => '', 'p' => 1]) ?>">
                    <span class="dot" style="background:#9ca3af"></span> Todos los niveles
                </a>
                <a class="filtro-opcion <?= $filtroNivel === 'principiante' ? 'activo' : '' ?>" href="<?= buildUrl(['nivel' => 'principiante', 'p' => 1]) ?>">
                    <span class="dot nivel-dot-principiante"></span> Fundamentos
                </a>
                <a class="filtro-opcion <?= $filtroNivel === 'estudiante' ? 'activo' : '' ?>" href="<?= buildUrl(['nivel' => 'estudiante', 'p' => 1]) ?>">
                    <span class="dot nivel-dot-estudiante"></span> Ruta académica
                </a>
                <a class="filtro-opcion <?= $filtroNivel === 'profesional' ? 'activo' : '' ?>" href="<?= buildUrl(['nivel' => 'profesional', 'p' => 1]) ?>">
                    <span class="dot nivel-dot-profesional"></span> Perfil profesional
                </a>
            </div>

            <?php if (!empty($categorias)): ?>
                <div class="filtro-sep"></div>
                <div class="filtro-grupo">
                    <h4>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        Categoría
                    </h4>
                    <a class="filtro-opcion <?= $filtroCategoria === '' ? 'activo' : '' ?>" href="<?= buildUrl(['categoria' => '', 'p' => 1]) ?>">
                        <span class="dot" style="background:#9ca3af"></span> Todas
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                        <a class="filtro-opcion <?= $filtroCategoria === $cat ? 'activo' : '' ?>" href="<?= buildUrl(['categoria' => $cat, 'p' => 1]) ?>">
                            <span class="dot" style="background:var(--mc-green)"></span>
                            <?= htmlspecialchars($cat) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($filtroPrecio !== '' || $filtroNivel !== '' || $filtroCategoria !== ''): ?>
                <div class="filtro-sep"></div>
                <a class="limpiar-btn" href="<?= buildUrl(['precio' => '', 'nivel' => '', 'categoria' => '', 'p' => 1]) ?>">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Limpiar filtros
                </a>
            <?php endif; ?>
            </div><!-- /.filtros-inner -->
        </aside>

        <!-- ── CONTENIDO ── -->
        <main class="buscar-main">

            <!-- Barra de búsqueda -->
            <div class="search-wrap">
                <form class="hero-search" method="GET" action="<?= BASE_URL ?>/index.php">
                    <input type="hidden" name="url" value="buscar">
                    <?php if ($filtroPrecio):    ?><input type="hidden" name="precio" value="<?= htmlspecialchars($filtroPrecio) ?>"><?php endif; ?>
                    <?php if ($filtroNivel):     ?><input type="hidden" name="nivel" value="<?= htmlspecialchars($filtroNivel) ?>"><?php endif; ?>
                    <?php if ($filtroCategoria): ?><input type="hidden" name="categoria" value="<?= htmlspecialchars($filtroCategoria) ?>"><?php endif; ?>
                    <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" name="q" id="searchInput"
                        value="<?= htmlspecialchars($q) ?>"
                        placeholder="Busca el curso que desees…"
                        autocomplete="off">
                    <button type="submit">Buscar</button>
                </form>
                <ul id="sugerencias"></ul>
            </div>

            <!-- Chips filtros activos -->
            <?php
            $chipsActivos = [];
            if ($q !== '')              $chipsActivos[] = ['Búsqueda: ' . $q,    buildUrl(['q' => '', 'p' => 1])];
            if ($filtroPrecio !== '')   $chipsActivos[] = [ucfirst($filtroPrecio), buildUrl(['precio' => '', 'p' => 1])];
            if ($filtroNivel !== '')    $chipsActivos[] = [ucfirst($filtroNivel),   buildUrl(['nivel' => '', 'p' => 1])];
            if ($filtroCategoria !== '') $chipsActivos[] = [$filtroCategoria,       buildUrl(['categoria' => '', 'p' => 1])];
            ?>
            <?php if (!empty($chipsActivos)): ?>
                <div class="chips-activos">
                    <?php foreach ($chipsActivos as [$label, $url]): ?>
                        <a class="chip" href="<?= $url ?>">
                            <?= htmlspecialchars($label) ?> <span class="x">✕</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!$hayFiltros && empty($q)): ?>
                <!-- Sin búsqueda activa: mostrar destacados -->
                <div class="resultados-header">
                    <div>
                        <h2 class="seccion-titulo" style="margin:0 0 2px;">Cursos Destacados</h2>
                        <p class="seccion-sub" style="margin:0;">Los más populares de la plataforma</p>
                    </div>
                </div>
            <?php elseif (empty($cursos)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24" style="color:#d1d5db;margin-bottom:4px"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    <h3>Sin resultados<?= $q !== '' ? ' para "' . htmlspecialchars($q) . '"' : '' ?></h3>
                    <p>Prueba con otro término o elimina algunos filtros.</p>
                    <a href="<?= BASE_URL ?>/index.php?url=buscar">Ver todos los cursos</a>
                </div>
            <?php else: ?>
                <div class="resultados-header">
                    <h2><?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?><?= $q !== '' ? ' para <em>"' . htmlspecialchars($q) . '"</em>' : '' ?></h2>
                    <div class="sort-wrap">
                        <label class="sort-label" for="sortSelect">Ordenar:</label>
                        <select id="sortSelect" class="sort-select" onchange="location.href=this.value">
                            <option value="<?= buildUrl(['orden' => 'popular',    'p' => 1]) ?>" <?= $ordenar === 'popular'    ? 'selected' : '' ?>>Más populares</option>
                            <option value="<?= buildUrl(['orden' => 'recientes',  'p' => 1]) ?>" <?= $ordenar === 'recientes'  ? 'selected' : '' ?>>Más recientes</option>
                            <option value="<?= buildUrl(['orden' => 'precio_asc', 'p' => 1]) ?>" <?= $ordenar === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
                            <option value="<?= buildUrl(['orden' => 'precio_desc','p' => 1]) ?>" <?= $ordenar === 'precio_desc'? 'selected' : '' ?>>Precio: mayor a menor</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($cursos)): ?>
                <!-- Grid de cursos -->
                <div class="row g-3">
                    <?php foreach ($cursos as $curso):
                        $img      = matrixcoders_curso_image($curso['imagen'] ?? '', $curso['titulo'] ?? '');
                        $imgFallback = matrixcoders_curso_image('', '');
                        $precio   = (float)($curso['precio'] ?? 0);
                        $titulo   = $curso['titulo'] ?? '';
                        $desc     = $curso['descripcion'] ?? '';
                        $stu      = (int)($curso['total_matriculas'] ?? 0);
                        $nivel    = $curso['nivel'] ?? '';
                        $cat      = $curso['categoria'] ?? '';
                        [$nivelTxt, $nivelColor, $nivelBg] = nivelLabel($nivel);
                    ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="course-card"
                                onclick="window.location.href='<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>'">

                                <div class="course-thumb-wrap">
                                    <img src="<?= htmlspecialchars($img) ?>" class="course-thumb"
                                        alt="<?= htmlspecialchars($titulo) ?>"
                                        onerror="this.src='<?= htmlspecialchars($imgFallback) ?>'">

                                    <?php if ($precio <= 0): ?>
                                        <div class="course-badges-corner">
                                            <span class="course-badge-corner course-badge-free">Gratis</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body-inner">
                                    <?php if ($cat !== '' || $nivelTxt !== ''): ?>
                                        <div class="tags-row">
                                            <?php if ($nivelTxt !== ''): ?>
                                                <span class="nivel-badge" style="color:<?= $nivelColor ?>;background:<?= $nivelBg ?>;border-color:<?= $nivelColor ?>33">
                                                    <?= htmlspecialchars($nivelTxt) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($cat !== ''): ?>
                                                <span class="tag" style="color:var(--mc-muted);background:var(--mc-soft);border-color:var(--mc-border)">
                                                    <?= htmlspecialchars($cat) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="course-title"><?= htmlspecialchars($titulo) ?></div>

                                    <?php if ($desc): ?>
                                        <p class="course-desc"><?= htmlspecialchars($desc) ?></p>
                                    <?php endif; ?>

                                    <div class="course-meta">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:3px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        <?= $stu ?> <?= $stu === 1 ? 'estudiante' : 'estudiantes' ?>
                                        <?php if (!empty($curso['duracion_min']) && $curso['duracion_min'] > 0):
                                            $h = intdiv((int)$curso['duracion_min'], 60);
                                            $m = (int)$curso['duracion_min'] % 60;
                                        ?>
                                            &nbsp;·&nbsp;
                                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:2px"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                                            <?= $h > 0 ? $h . 'h ' : '' ?><?= $m > 0 ? $m . 'min' : '' ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-footer-row">
                                        <div class="course-price-wrap">
                                            <span class="course-price <?= $precio <= 0 ? 'gratis' : '' ?>">
                                                <?= $precio > 0 ? number_format($precio, 2) . '€' : 'Gratis' ?>
                                            </span>
                                            <?php if ($precio <= 0): ?>
                                                <span class="course-price-note">Acceso sin coste</span>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn-carrito" title="Añadir al carrito"
                                            onclick="event.stopPropagation(); abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precio ?>)">
                                            <img src="<?= BASE_URL ?>/img/carrito-de-compras.png" alt="">
                                            <span>Añadir</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación custom -->
                <?php if ($totalPaginas > 1): ?>
                    <nav class="pag-nav">
                        <?php if ($pagina > 1): ?>
                            <a class="pag-btn pag-arrow" href="<?= buildUrl(['p' => $pagina - 1]) ?>">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                            </a>
                        <?php else: ?>
                            <span class="pag-btn pag-arrow pag-disabled">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                            </span>
                        <?php endif; ?>

                        <?php
                        $inicio = max(1, $pagina - 2);
                        $fin    = min($totalPaginas, $pagina + 2);
                        if ($inicio > 1): ?>
                            <a class="pag-btn" href="<?= buildUrl(['p' => 1]) ?>">1</a>
                            <?php if ($inicio > 2): ?><span class="pag-ellipsis">…</span><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                            <a class="pag-btn <?= $i === $pagina ? 'pag-activo' : '' ?>" href="<?= buildUrl(['p' => $i]) ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($fin < $totalPaginas): ?>
                            <?php if ($fin < $totalPaginas - 1): ?><span class="pag-ellipsis">…</span><?php endif; ?>
                            <a class="pag-btn" href="<?= buildUrl(['p' => $totalPaginas]) ?>"><?= $totalPaginas ?></a>
                        <?php endif; ?>

                        <?php if ($pagina < $totalPaginas): ?>
                            <a class="pag-btn pag-arrow" href="<?= buildUrl(['p' => $pagina + 1]) ?>">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        <?php else: ?>
                            <span class="pag-btn pag-arrow pag-disabled">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                            </span>
                        <?php endif; ?>

                        <span class="pag-info">Página <?= $pagina ?> de <?= $totalPaginas ?></span>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

        </main>
    </div>

    <!-- Modal carrito -->
    <div class="modal fade" id="modalCarrito" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Añadir al carrito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p id="modal-texto" class="mb-1" style="font-size:1rem;font-weight:700;"></p>
                    <p id="modal-precio" class="fw-bold" style="font-size:1.2rem;color:#111827;"></p>
                    <p class="text-muted" style="font-size:.88rem;">¿Quieres añadir este curso a tu carrito?</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-confirmar-carrito">Añadir al carrito</button>
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const input = document.getElementById('searchInput');
        const lista = document.getElementById('sugerencias');
        const baseUrl = '<?= BASE_URL ?>';
        let timer = null;

        input.addEventListener('input', function() {
            const q = this.value.trim();
            if (!q) {
                ocultarLista();
                return;
            }
            clearTimeout(timer);
            timer = setTimeout(() => buscarSugerencias(q), 250);
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            if (input.value.trim() === '') {
                e.preventDefault();
                window.location.href = baseUrl + '/index.php?url=buscar';
            }
        });

        function buscarSugerencias(q) {
            fetch(baseUrl + '/index.php?url=autocomplete&q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!data.length) {
                        ocultarLista();
                        return;
                    }
                    lista.innerHTML = '';
                    data.forEach(curso => {
                        const li = document.createElement('li');
                        li.textContent = curso.titulo;
                        li.addEventListener('mouseenter', () => li.classList.add('activo'));
                        li.addEventListener('mouseleave', () => li.classList.remove('activo'));
                        li.addEventListener('click', () => {
                            input.value = curso.titulo;
                            ocultarLista();
                            window.location.href = baseUrl + '/index.php?url=buscar&q=' + encodeURIComponent(curso.titulo);
                        });
                        lista.appendChild(li);
                    });
                    lista.style.display = 'block';
                })
                .catch(() => ocultarLista());
        }

        function ocultarLista() {
            lista.style.display = 'none';
            lista.innerHTML = '';
        }

        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !lista.contains(e.target)) ocultarLista();
        });

        input.addEventListener('keydown', function(e) {
            const items = [...lista.querySelectorAll('li')];
            const activo = lista.querySelector('li.activo');
            let idx = items.indexOf(activo);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activo?.classList.remove('activo');
                idx = (idx + 1) % items.length;
                items[idx]?.classList.add('activo');
                input.value = items[idx]?.textContent || input.value;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                activo?.classList.remove('activo');
                idx = (idx - 1 + items.length) % items.length;
                items[idx]?.classList.add('activo');
                input.value = items[idx]?.textContent || input.value;
            }
            if (e.key === 'Escape') ocultarLista();
            if (e.key === 'Enter' && activo) {
                e.preventDefault();
                window.location.href = baseUrl + '/index.php?url=buscar&q=' + encodeURIComponent(activo.textContent);
            }
        });

        // Modal carrito
        let cursoSeleccionado = null;

        function abrirModal(id, titulo, precio) {
            cursoSeleccionado = id;
            document.getElementById('modal-texto').textContent = titulo;
            document.getElementById('modal-precio').textContent = precio > 0 ? precio.toFixed(2) + '€' : 'Gratis';
            new bootstrap.Modal(document.getElementById('modalCarrito')).show();
        }

        document.getElementById('btn-confirmar-carrito').addEventListener('click', function() {
            if (!cursoSeleccionado) return;
            const fd = new FormData();
            fd.append('curso_id', cursoSeleccionado);
            fetch(baseUrl + '/index.php?url=carrito-añadir', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        let badge = document.querySelector('.carrito-badge');
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'carrito-badge';
                            document.querySelector('a[aria-label="carrito"]')?.appendChild(badge);
                        }
                        badge.textContent = data.total;
                        bootstrap.Modal.getInstance(document.getElementById('modalCarrito')).hide();
                        return;
                    }

                    const label = data.mensaje || (data.estado === 'matriculado'
                        ? 'Ya estas matriculado en este curso.'
                        : 'Este curso ya esta en tu cesta.');
                    document.getElementById('modal-precio').textContent = label;
                    document.getElementById('modal-texto').textContent = 'No se ha añadido ningun curso';
                });
        });
    </script>
</body>

</html>
