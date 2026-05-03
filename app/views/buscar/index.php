<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../helpers/curso_imagen.php';

// Defaults
$q                = $q                ?? '';
$filtroPrecio     = $filtroPrecio     ?? '';
$filtroNiveles    = $filtroNiveles    ?? [];
$filtroCategorias = $filtroCategorias ?? [];
$hayFiltros       = $hayFiltros       ?? false;
$ordenar          = $ordenar          ?? 'popular';
$total            = $total            ?? 0;
$totalPaginas     = $totalPaginas     ?? 1;
$pagina           = $pagina           ?? 1;
$matriculasUsuario = $matriculasUsuario ?? [];

function nivelLabel(string $nivel): array
{
    return match ($nivel) {
        'principiante' => ['Fundamentos',       '#16a34a', '#dcfce7'],
        'estudiante'   => ['Ruta académica',     '#2563eb', '#dbeafe'],
        'profesional'  => ['Perfil profesional', '#ea580c', '#ffedd5'],
        default        => ['', '', ''],
    };
}

// buildUrl: genera URL preservando todos los filtros activos (incluidos arrays)
function buildUrl(array $overrides = []): string
{
    global $q, $filtroPrecio, $filtroNiveles, $filtroCategorias, $pagina, $ordenar;
    $params = [];
    $params['url']    = 'buscar';
    $params['q']      = $overrides['q']      ?? $q;
    $params['precio'] = $overrides['precio'] ?? $filtroPrecio;
    $params['orden']  = array_key_exists('orden', $overrides) ? $overrides['orden'] : $ordenar;
    $params['p']      = $overrides['p']      ?? $pagina;
    // Arrays
    $params['nivel']     = $overrides['nivel']     ?? $filtroNiveles;
    $params['categoria'] = $overrides['categoria'] ?? $filtroCategorias;
    // Limpiar vacíos
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null && $v !== [] && $v !== 1);
    if (isset($params['p']) && (int)$params['p'] === 1) unset($params['p']);
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

        /* Filtro opción — label clicable que envuelve un input oculto */
        .filtro-opcion {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .12s;
            color: var(--mc-dark);
            font-size: .83rem;
            user-select: none;
        }

        .filtro-opcion:hover { background: var(--mc-soft); }

        .filtro-opcion.activo {
            background: #f0fdf4;
            color: var(--mc-green);
            font-weight: 700;
        }

        /* Ocultar checkbox/radio nativo; el .dot hace de indicador visual */
        .filtro-opcion input[type="checkbox"],
        .filtro-opcion input[type="radio"] { display: none; }

        .filtro-opcion .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            transition: box-shadow .12s;
        }

        .filtro-opcion.activo .dot {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px currentColor;
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

        /* ── SEARCH BAR (estilo página de inicio) ── */
        .search-wrap {
            position: relative;
            margin-bottom: 20px;
        }

        .hero-search {
            position: relative;
            display: flex;
            align-items: center;
            background: #fff;
            border: 1.5px solid var(--mc-border);
            border-radius: 16px;
            box-shadow: 0 2px 14px rgba(15, 23, 42, .06);
            overflow: hidden;
            transition: border-color .18s, box-shadow .18s;
        }

        .hero-search:focus-within {
            border-color: var(--mc-green);
            box-shadow: 0 4px 22px rgba(107, 143, 113, .13);
        }

        .hero-search .icon {
            position: absolute;
            left: 15px;
            width: 17px;
            height: 17px;
            color: #9ca3af;
            pointer-events: none;
            flex-shrink: 0;
        }

        .hero-search input {
            flex: 1;
            border: none;
            outline: none;
            padding: 14px 14px 14px 44px;
            font-family: 'Saira', sans-serif;
            font-size: .95rem;
            color: var(--mc-dark);
            background: transparent;
            width: 100%;
        }

        .hero-search input::placeholder { color: #b0b7c0; }

        .hero-search button {
            flex-shrink: 0;
            border: none;
            background: var(--mc-green);
            color: #fff;
            margin: 5px 5px 5px 0;
            padding: 9px 20px;
            border-radius: 12px;
            font-family: 'Saira', sans-serif;
            font-weight: 700;
            font-size: .84rem;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s;
        }

        .hero-search button:hover { background: var(--mc-green-d); }

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

        .course-badge-discount {
            color: #fff;
            background: #ef4444;
            border-color: #ef4444;
            font-weight: 800;
        }

        .course-price-original {
            font-size: .8rem;
            color: var(--mc-muted);
            text-decoration: line-through;
            margin-right: 4px;
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

        <!-- ── PANEL FILTROS (form multi-select, auto-submit on change) ── -->
        <aside class="filtros-panel">
            <div class="filtros-panel-head">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                <h3>Filtros</h3>
                <?php $nFiltros = (int)($filtroPrecio !== '') + count($filtroNiveles) + count($filtroCategorias); ?>
                <?php if ($nFiltros > 0): ?>
                    <span style="margin-left:auto;background:var(--mc-green);color:#fff;font-size:.65rem;font-weight:800;border-radius:99px;padding:1px 7px;"><?= $nFiltros ?></span>
                <?php endif; ?>
            </div>

            <form class="filtros-inner" id="filtrosForm" method="GET" action="<?= BASE_URL ?>/index.php">
                <input type="hidden" name="url" value="buscar">
                <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                <?php if ($ordenar !== 'popular'): ?>
                    <input type="hidden" name="orden" value="<?= htmlspecialchars($ordenar) ?>">
                <?php endif; ?>

                <!-- Precio (radio — mutuamente exclusivo) -->
                <div class="filtro-grupo">
                    <h4>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v2m0 8v2M8.5 9.5A3.5 1.5 0 0 1 12 8a3.5 1.5 0 0 1 3.5 1.5 3.5 1.5 0 0 1-3.5 1.5 3.5 1.5 0 0 1-3.5 1.5A3.5 1.5 0 0 1 12 14"/></svg>
                        Precio
                    </h4>
                    <label class="filtro-opcion <?= $filtroPrecio === '' ? 'activo' : '' ?>">
                        <input type="radio" name="precio" value="" <?= $filtroPrecio === '' ? 'checked' : '' ?>>
                        <span class="dot" style="background:#9ca3af"></span> Todos
                    </label>
                    <label class="filtro-opcion <?= $filtroPrecio === 'gratis' ? 'activo' : '' ?>">
                        <input type="radio" name="precio" value="gratis" <?= $filtroPrecio === 'gratis' ? 'checked' : '' ?>>
                        <span class="dot" style="background:#10b981"></span> Gratis
                    </label>
                    <label class="filtro-opcion <?= $filtroPrecio === 'pago' ? 'activo' : '' ?>">
                        <input type="radio" name="precio" value="pago" <?= $filtroPrecio === 'pago' ? 'checked' : '' ?>>
                        <span class="dot" style="background:#f59e0b"></span> De pago
                    </label>
                </div>

                <div class="filtro-sep"></div>

                <!-- Nivel (checkboxes — selección múltiple) -->
                <div class="filtro-grupo">
                    <h4>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Nivel
                    </h4>
                    <?php foreach ([
                        'principiante' => ['Fundamentos',       'nivel-dot-principiante'],
                        'estudiante'   => ['Ruta académica',     'nivel-dot-estudiante'],
                        'profesional'  => ['Perfil profesional', 'nivel-dot-profesional'],
                    ] as $val => [$lbl, $dotCls]): ?>
                        <label class="filtro-opcion <?= in_array($val, $filtroNiveles, true) ? 'activo' : '' ?>">
                            <input type="checkbox" name="nivel[]" value="<?= $val ?>"
                                <?= in_array($val, $filtroNiveles, true) ? 'checked' : '' ?>>
                            <span class="dot <?= $dotCls ?>"></span> <?= $lbl ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($categorias)): ?>
                    <div class="filtro-sep"></div>
                    <div class="filtro-grupo">
                        <h4>
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            Categoría
                        </h4>
                        <?php foreach ($categorias as $cat): ?>
                            <label class="filtro-opcion <?= in_array($cat, $filtroCategorias, true) ? 'activo' : '' ?>">
                                <input type="checkbox" name="categoria[]" value="<?= htmlspecialchars($cat) ?>"
                                    <?= in_array($cat, $filtroCategorias, true) ? 'checked' : '' ?>>
                                <span class="dot" style="background:var(--mc-green)"></span>
                                <?= htmlspecialchars($cat) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($nFiltros > 0): ?>
                    <div class="filtro-sep"></div>
                    <a class="limpiar-btn" href="<?= BASE_URL ?>/index.php?url=buscar<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Limpiar filtros
                    </a>
                <?php endif; ?>
            </form>
        </aside>

        <!-- ── CONTENIDO ── -->
        <main class="buscar-main">

            <!-- Barra de búsqueda -->
            <div class="search-wrap">
                <form class="hero-search" id="searchForm" method="GET" action="<?= BASE_URL ?>/index.php">
                    <input type="hidden" name="url" value="buscar">
                    <?php if ($filtroPrecio): ?><input type="hidden" name="precio" value="<?= htmlspecialchars($filtroPrecio) ?>"><?php endif; ?>
                    <?php foreach ($filtroNiveles as $n): ?><input type="hidden" name="nivel[]" value="<?= htmlspecialchars($n) ?>"><?php endforeach; ?>
                    <?php foreach ($filtroCategorias as $c): ?><input type="hidden" name="categoria[]" value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?>
                    <?php if ($ordenar !== 'popular'): ?><input type="hidden" name="orden" value="<?= htmlspecialchars($ordenar) ?>"><?php endif; ?>
                    <img class="icon" src="<?= BASE_URL ?>/img/lupa.png" alt="buscar">
                    <input class="form-control w-100" type="text" name="q" id="searchInput"
                        value="<?= htmlspecialchars($q) ?>"
                        placeholder="Busca el curso que desees…"
                        autocomplete="off">
                </form>
                <ul id="sugerencias"></ul>
            </div>

            <!-- Chips filtros activos -->
            <?php
            $chipsActivos = [];
            if ($q !== '') {
                $chipsActivos[] = ['Búsqueda: ' . $q, buildUrl(['q' => ''])];
            }
            if ($filtroPrecio !== '') {
                $chipsActivos[] = [ucfirst($filtroPrecio), buildUrl(['precio' => ''])];
            }
            foreach ($filtroNiveles as $n) {
                [$lbl] = nivelLabel($n);
                $resto = array_values(array_diff($filtroNiveles, [$n]));
                $chipsActivos[] = [$lbl, buildUrl(['nivel' => $resto])];
            }
            foreach ($filtroCategorias as $c) {
                $resto = array_values(array_diff($filtroCategorias, [$c]));
                $chipsActivos[] = [$c, buildUrl(['categoria' => $resto])];
            }
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

            <?php if (empty($cursos)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24" style="color:#d1d5db;margin-bottom:4px"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    <h3>Sin resultados<?= $q !== '' ? ' para "' . htmlspecialchars($q) . '"' : '' ?></h3>
                    <p>Prueba con otro término o elimina algunos filtros.</p>
                    <a href="<?= BASE_URL ?>/index.php?url=buscar">Ver todos los cursos</a>
                </div>
            <?php else: ?>
                <div class="resultados-header">
                    <?php if ($hayFiltros || $q !== ''): ?>
                        <h2><?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?><?= $q !== '' ? ' para <em>"' . htmlspecialchars($q) . '"</em>' : '' ?></h2>
                    <?php else: ?>
                        <h2><em><?= $total ?></em> curso<?= $total !== 1 ? 's' : '' ?> disponible<?= $total !== 1 ? 's' : '' ?></h2>
                    <?php endif; ?>
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
                        $descuento = (float)($curso['descuento_activo'] ?? 0);
                        $precioFinal = ($descuento > 0 && $precio > 0) ? round($precio * (1 - $descuento/100), 2) : $precio;
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
                                            <?php if ($precio <= 0): ?>
                                                <span class="course-price gratis">Gratis</span>
                                                <span class="course-price-note">Acceso sin coste</span>
                                            <?php elseif ($descuento > 0): ?>
                                                <del class="course-price-original"><?= number_format($precio, 2) ?>€</del>
                                                <span class="course-price"><?= number_format($precioFinal, 2) ?>€</span>
                                            <?php else: ?>
                                                <span class="course-price"><?= number_format($precio, 2) ?>€</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (in_array($curso['id'], $matriculasUsuario)): ?>
                                            <button class="btn-course-enrolled" onclick="event.stopPropagation();">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                                Matriculado
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-carrito" title="Añadir al carrito"
                                                onclick="event.stopPropagation(); abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precioFinal ?>, <?= $precio ?>, <?= (float)($descuento ?? 0) ?>, '<?= htmlspecialchars(addslashes($img)) ?>')">
                                                <img src="<?= BASE_URL ?>/img/carrito-de-compras.png" alt="">
                                                <span>Añadir</span>
                                            </button>
                                        <?php endif; ?>
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

    <!-- Toast éxito -->
    <div id="toastCarrito">
        <div class="mc-toast-inner">
            <span class="mc-toast-check">
                <svg width="12" height="12" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
            <span id="toastMsg">¡Curso añadido al carrito!</span>
            <a href="<?= BASE_URL ?>/index.php?url=carrito" class="mc-toast-link">Ver cesta →</a>
        </div>
    </div>

    <!-- Modal carrito -->
    <div class="modal fade" id="modalCarrito" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mc-cart-dialog">
            <div class="modal-content mc-cart-modal">

                <button type="button" class="mc-modal-x" data-bs-dismiss="modal" aria-label="Cerrar">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <div class="mc-modal-course">
                    <div class="mc-modal-thumb-wrap">
                        <img id="mc-modal-img" src="" alt="" class="mc-modal-thumb">
                        <div class="mc-modal-thumb-placeholder">
                            <svg width="28" height="28" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    </div>
                    <div class="mc-modal-course-info">
                        <span class="mc-modal-pretitle">Añadir al carrito</span>
                        <p id="modal-texto" class="mc-modal-course-title"></p>
                        <div class="mc-modal-pricing">
                            <span id="modal-precio-original" class="mc-price-old" style="display:none"></span>
                            <span id="modal-precio" class="mc-price-main"></span>
                            <span id="modal-desc-badge" class="mc-disc-badge" style="display:none"></span>
                        </div>
                    </div>
                </div>

                <div class="mc-modal-divider"></div>

                <div class="mc-modal-trust">
                    <span class="mc-trust-chip">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                        Acceso inmediato
                    </span>
                    <span class="mc-trust-chip">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                        Garantía 30 días
                    </span>
                    <span class="mc-trust-chip">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                        Certificado incluido
                    </span>
                </div>

                <div id="modal-error" class="mc-modal-error" style="display:none"></div>

                <div class="mc-modal-actions">
                    <button type="button" id="btn-confirmar-carrito" class="mc-btn-add">
                        <span class="btn-cc-spinner"></span>
                        <svg class="btn-cc-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="btn-cc-label">Añadir al carrito</span>
                    </button>
                    <button type="button" class="mc-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                </div>

                <p class="mc-modal-security">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Pago seguro con <strong>Stripe</strong> · Cifrado SSL
                </p>

            </div>
        </div>
    </div>

    <style>
    #toastCarrito{position:fixed;bottom:28px;right:28px;z-index:9999;opacity:0;transform:translateY(20px);transition:opacity .3s ease,transform .3s ease;pointer-events:none}
    #toastCarrito.show{opacity:1;transform:translateY(0);pointer-events:auto}
    .mc-toast-inner{background:#1B2336;color:#fff;border-radius:14px;padding:13px 20px;display:flex;align-items:center;gap:10px;font-size:.88rem;font-weight:600;box-shadow:0 8px 36px rgba(0,0,0,.25);font-family:'Saira',sans-serif;white-space:nowrap}
    .mc-toast-check{background:#6B8F71;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
    .mc-toast-link{color:#6B8F71;font-weight:800;margin-left:6px;text-decoration:none}
    .mc-toast-link:hover{text-decoration:underline}
    .mc-cart-dialog{max-width:460px}
    .mc-cart-modal{border-radius:20px!important;border:none!important;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.18)!important;font-family:'Saira',sans-serif;padding:0}
    .mc-modal-x{position:absolute;top:14px;right:14px;z-index:10;width:30px;height:30px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6b7280;transition:all .15s}
    .mc-modal-x:hover{background:#f3f4f6;color:#1B2336}
    .mc-modal-course{display:flex;gap:16px;align-items:flex-start;padding:28px 28px 20px}
    .mc-modal-thumb-wrap{position:relative;width:88px;height:72px;border-radius:12px;overflow:hidden;flex-shrink:0;background:#f3f4f6}
    .mc-modal-thumb{width:100%;height:100%;object-fit:cover;display:none}
    .mc-modal-thumb.loaded{display:block}
    .mc-modal-thumb-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center}
    .mc-modal-course-info{flex:1;min-width:0}
    .mc-modal-pretitle{font-size:.7rem;font-weight:700;color:#6B8F71;text-transform:uppercase;letter-spacing:.8px;display:block;margin-bottom:5px}
    .mc-modal-course-title{font-size:.95rem;font-weight:800;color:#1B2336;margin:0 0 10px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .mc-modal-pricing{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
    .mc-price-old{font-size:.83rem;color:#9ca3af;text-decoration:line-through;font-weight:500}
    .mc-price-main{font-size:1.35rem;font-weight:900;color:#1B2336;line-height:1}
    .mc-disc-badge{background:#ef4444;color:#fff;font-size:.65rem;font-weight:800;border-radius:6px;padding:3px 7px;letter-spacing:.3px}
    .mc-modal-divider{height:1px;background:#f0f0f0;margin:0 28px}
    .mc-modal-trust{display:flex;gap:6px;flex-wrap:wrap;padding:16px 28px}
    .mc-trust-chip{display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:600;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;padding:4px 10px}
    .mc-modal-error{margin:0 28px 12px;padding:10px 14px;border-radius:10px;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;font-size:.82rem;font-weight:600}
    .mc-modal-actions{padding:4px 28px 0;display:flex;flex-direction:column;gap:10px}
    .mc-btn-add{width:100%;padding:15px;border-radius:13px;border:none;background:#1B2336;color:#fff;font-weight:800;font-size:.95rem;font-family:'Saira',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .18s,transform .1s;position:relative}
    .mc-btn-add:hover:not(:disabled){background:#0f172a;transform:translateY(-1px)}
    .mc-btn-add:active:not(:disabled){transform:translateY(0)}
    .mc-btn-add:disabled{opacity:.6;cursor:not-allowed;transform:none}
    .mc-btn-add.success{background:#16a34a}
    .btn-cc-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:mcSpin .65s linear infinite;flex-shrink:0}
    .mc-btn-add.loading .btn-cc-spinner{display:block}
    .mc-btn-add.loading .btn-cc-icon,.mc-btn-add.loading .btn-cc-label{display:none}
    .mc-btn-cancel{width:100%;padding:11px;border-radius:10px;border:none;background:transparent;color:#6b7280;font-size:.85rem;font-weight:600;font-family:'Saira',sans-serif;cursor:pointer;transition:color .15s}
    .mc-btn-cancel:hover{color:#1B2336}
    .mc-modal-security{text-align:center;font-size:.72rem;color:#9ca3af;margin:8px 28px 22px;display:flex;align-items:center;justify-content:center;gap:5px}
    .mc-modal-security strong{color:#6b7280}
    @keyframes mcSpin{to{transform:rotate(360deg)}}
    </style>

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

        // Auto-submit filtros al cambiar cualquier checkbox/radio
        (function () {
            const form = document.getElementById('filtrosForm');
            if (!form) return;
            form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (inp) {
                inp.addEventListener('change', function () {
                    // Marcar visualmente antes de submit
                    inp.closest('label').classList.toggle('activo', inp.checked || (inp.type === 'radio' && inp.value === ''));
                    form.submit();
                });
            });
        })();

        // Modal carrito
        let cursoSeleccionado = null;

        function abrirModal(id, titulo, precioFinal, precioOriginal = 0, descuento = 0, imagen = '') {
            cursoSeleccionado = id;
            const btn     = document.getElementById('btn-confirmar-carrito');
            const elError = document.getElementById('modal-error');

            // Reset
            btn.disabled = false;
            btn.classList.remove('loading', 'success');
            btn.innerHTML = `<span class="btn-cc-spinner"></span><svg class="btn-cc-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span class="btn-cc-label">Añadir al carrito</span>`;
            if (elError) elError.style.display = 'none';

            // Image
            const img = document.getElementById('mc-modal-img');
            const placeholder = img?.nextElementSibling;
            if (img) {
                if (imagen) {
                    img.onload = () => { img.classList.add('loaded'); if (placeholder) placeholder.style.display = 'none'; };
                    img.onerror = () => { img.classList.remove('loaded'); if (placeholder) placeholder.style.display = 'flex'; };
                    img.src = imagen;
                    if (img.complete && img.naturalWidth) { img.classList.add('loaded'); if (placeholder) placeholder.style.display = 'none'; }
                } else {
                    img.src = ''; img.classList.remove('loaded');
                    if (placeholder) placeholder.style.display = 'flex';
                }
            }

            // Title
            document.getElementById('modal-texto').textContent = titulo;

            // Pricing
            const elPrecio   = document.getElementById('modal-precio');
            const elOriginal = document.getElementById('modal-precio-original');
            const elBadge    = document.getElementById('modal-desc-badge');

            if (precioFinal <= 0) {
                elPrecio.textContent = 'Gratis';
                elOriginal.style.display = 'none';
                elBadge.style.display = 'none';
            } else if (descuento > 0 && precioOriginal > precioFinal) {
                elPrecio.textContent = precioFinal.toFixed(2) + '€';
                elOriginal.textContent = precioOriginal.toFixed(2) + '€';
                elOriginal.style.display = '';
                elBadge.textContent = '-' + Math.round(descuento) + '%';
                elBadge.style.display = '';
            } else {
                elPrecio.textContent = precioFinal.toFixed(2) + '€';
                elOriginal.style.display = 'none';
                elBadge.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('modalCarrito')).show();
        }

        document.getElementById('btn-confirmar-carrito').addEventListener('click', function () {
            if (!cursoSeleccionado) return;
            const btn     = this;
            const elError = document.getElementById('modal-error');

            btn.disabled = true;
            btn.classList.add('loading');

            const fd = new FormData();
            fd.append('curso_id', cursoSeleccionado);
            fetch(baseUrl + '/index.php?url=carrito-añadir', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        let badge = document.querySelector('.carrito-badge');
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'notif-bell-badge carrito-badge';
                            document.querySelector('a[aria-label="carrito"]')?.appendChild(badge);
                        }
                        badge.textContent = data.total;
                        badge.classList.add('visible');

                        btn.classList.remove('loading');
                        btn.classList.add('success');
                        btn.innerHTML = `<svg width="18" height="18" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>¡Añadido!</span>`;
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('modalCarrito')).hide();
                            showToast('¡Curso añadido al carrito!');
                        }, 900);
                        return;
                    }
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    if (elError) {
                        elError.textContent = data.mensaje || (data.estado === 'matriculado'
                            ? 'Ya estás matriculado en este curso.'
                            : 'Este curso ya está en tu cesta.');
                        elError.style.display = 'block';
                    }
                })
                .catch(() => { btn.disabled = false; btn.classList.remove('loading'); });
        });

        function showToast(msg) {
            const t = document.getElementById('toastCarrito');
            document.getElementById('toastMsg').textContent = msg;
            t.classList.add('show');
            clearTimeout(t._hideTimer);
            t._hideTimer = setTimeout(() => t.classList.remove('show'), 4000);
        }
    </script>
</body>

</html>
