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
        'principiante'  => ['Principiante',  '#16a34a', '#dcfce7'],
        'estudiante'    => ['Estudiante',    '#2563eb', '#dbeafe'],
        'profesional'   => ['Trabajador',    '#7c3aed', '#ede9fe'],
        default         => ['', '', ''],
    };
}

function buildUrl(array $overrides = []): string
{
    global $q, $filtroPrecio, $filtroNivel, $filtroCategoria, $pagina;
    $params = array_merge([
        'url'       => 'buscar',
        'q'         => $q,
        'precio'    => $filtroPrecio,
        'nivel'     => $filtroNivel,
        'categoria' => $filtroCategoria,
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
            border-radius: 14px;
            box-shadow: var(--mc-shadow);
            padding: 20px;
            position: sticky;
            top: 20px;
        }

        .filtros-panel h3 {
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--mc-muted);
            margin: 0 0 14px;
        }

        .filtro-grupo {
            margin-bottom: 20px;
        }

        .filtro-grupo h4 {
            font-size: .78rem;
            font-weight: 700;
            color: var(--mc-dark);
            margin: 0 0 8px;
        }

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
            margin-bottom: 24px;
        }

        .hero-search {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 2px solid var(--mc-border);
            border-radius: 18px;
            padding: 12px 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }

        .hero-search:focus-within {
            border-color: var(--mc-green);
            box-shadow: 0 14px 28px rgba(107, 143, 113, .12);
        }

        .hero-search .icon {
            width: 18px;
            height: 18px;
            opacity: .5;
            flex-shrink: 0;
        }

        .hero-search input {
            border: none;
            outline: none;
            flex: 1;
            font-family: 'Saira', sans-serif;
            font-size: .92rem;
            color: var(--mc-dark);
            background: transparent;
        }

        .hero-search button {
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 8px 16px;
            font-family: 'Saira', sans-serif;
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }

        .hero-search button:hover {
            background: var(--mc-green-d);
        }

        #sugerencias {
            display: none;
            background: #fff;
            border: 1px solid var(--mc-border);
            border-radius: 12px;
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
            margin-bottom: 16px;
        }

        .resultados-header h2 {
            font-size: .9rem;
            font-weight: 700;
            color: var(--mc-muted);
            margin: 0;
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

        /* Paginación */
        .pagination .page-link {
            color: var(--mc-dark);
            border-color: var(--mc-border);
        }

        .pagination .page-item.active .page-link {
            background: var(--mc-green);
            border-color: var(--mc-green);
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
            <h3>Filtros</h3>

            <!-- Precio -->
            <div class="filtro-grupo">
                <h4>💰 Precio</h4>
                <a class="filtro-opcion <?= $filtroPrecio === '' ? 'activo' : '' ?>"
                    href="<?= buildUrl(['precio' => '', 'p' => 1]) ?>">
                    <span class="dot" style="background:#9ca3af"></span> Todos
                </a>
                <a class="filtro-opcion <?= $filtroPrecio === 'gratis' ? 'activo' : '' ?>"
                    href="<?= buildUrl(['precio' => 'gratis', 'p' => 1]) ?>">
                    <span class="dot" style="background:#10b981"></span> Gratis
                </a>
                <a class="filtro-opcion <?= $filtroPrecio === 'pago' ? 'activo' : '' ?>"
                    href="<?= buildUrl(['precio' => 'pago', 'p' => 1]) ?>">
                    <span class="dot" style="background:#f59e0b"></span> De pago
                </a>
            </div>

            <div class="filtro-sep"></div>

            <!-- Nivel / tipo de usuario -->
            <div class="filtro-grupo">
                <h4>🎯 A quién va dirigido</h4>
                <a class="filtro-opcion <?= $filtroNivel === '' ? 'activo' : '' ?>"
                    href="<?= buildUrl(['nivel' => '', 'p' => 1]) ?>">
                    <span class="dot" style="background:#9ca3af"></span> Todos los niveles
                </a>
                <a class="filtro-opcion <?= $filtroNivel === 'principiante' ? 'activo' : '' ?>"
                    href="<?= buildUrl(['nivel' => 'principiante', 'p' => 1]) ?>">
                    <span class="dot" style="background:#16a34a"></span> Principiante
                </a>
                <a class="filtro-opcion <?= $filtroNivel === 'estudiante' ? 'activo' : '' ?>"
                    href="<?= buildUrl(['nivel' => 'estudiante', 'p' => 1]) ?>">
                    <span class="dot" style="background:#2563eb"></span> Estudiante
                </a>
                <a class="filtro-opcion <?= $filtroNivel === 'profesional' ? 'activo' : '' ?>"
                    href="<?= buildUrl(['nivel' => 'profesional', 'p' => 1]) ?>">
                    <span class="dot" style="background:#7c3aed"></span> Trabajador / Profesional
                </a>
            </div>

            <?php if (!empty($categorias)): ?>
                <div class="filtro-sep"></div>

                <!-- Categoría -->
                <div class="filtro-grupo">
                    <h4>📂 Categoría</h4>
                    <a class="filtro-opcion <?= $filtroCategoria === '' ? 'activo' : '' ?>"
                        href="<?= buildUrl(['categoria' => '', 'p' => 1]) ?>">
                        <span class="dot" style="background:#9ca3af"></span> Todas
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                        <a class="filtro-opcion <?= $filtroCategoria === $cat ? 'activo' : '' ?>"
                            href="<?= buildUrl(['categoria' => $cat, 'p' => 1]) ?>">
                            <span class="dot" style="background:var(--mc-green)"></span>
                            <?= htmlspecialchars($cat) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($filtroPrecio !== '' || $filtroNivel !== '' || $filtroCategoria !== ''): ?>
                <div class="filtro-sep"></div>
                <a class="limpiar-btn" href="<?= buildUrl(['precio' => '', 'nivel' => '', 'categoria' => '', 'p' => 1]) ?>">
                    ✕ Limpiar filtros
                </a>
            <?php endif; ?>
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
                    <img class="icon" src="<?= BASE_URL ?>/img/lupa.png" alt="">
                    <input type="text" name="q" id="searchInput"
                        value="<?= htmlspecialchars($q) ?>"
                        placeholder="Busca el curso que desees"
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
                <h2 class="seccion-titulo">Cursos Destacados</h2>
                <p class="seccion-sub">Los cursos más populares de nuestra plataforma</p>
            <?php elseif (empty($cursos)): ?>
                <!-- Empty state -->
                <div class="empty-state">
                    <div class="ei">🔍</div>
                    <h3>Sin resultados<?= $q !== '' ? ' para "' . $q . '"' : '' ?></h3>
                    <p>Prueba con otro término, o elimina algunos filtros para ver más resultados.</p>
                    <a href="<?= BASE_URL ?>/index.php?url=buscar">Ver todos los cursos</a>
                </div>
            <?php else: ?>
                <!-- Header con total -->
                <div class="resultados-header">
                    <h2>
                        <?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?>
                        <?= $q !== '' ? ' para "' . htmlspecialchars($q) . '"' : '' ?>
                    </h2>
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

                                    <?php if ($nivelTxt !== '' || $precio <= 0): ?>
                                        <div class="course-badges-corner">
                                            <?php if ($nivelTxt !== ''): ?>
                                                <span class="course-badge-corner" style="color:<?= $nivelColor ?>;background:<?= $nivelBg ?>;border-color:<?= $nivelColor ?>22">
                                                    <?= htmlspecialchars($nivelTxt) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($precio <= 0): ?>
                                                <span class="course-badge-corner course-badge-free">Gratis</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body-inner">
                                    <?php if ($cat !== ''): ?>
                                        <div class="tags-row">
                                            <span class="tag" style="color:var(--mc-muted);background:var(--mc-soft);border-color:var(--mc-border)">
                                                <?= htmlspecialchars($cat) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="course-title"><?= htmlspecialchars($titulo) ?></div>

                                    <?php if ($desc): ?>
                                        <p class="course-desc"><?= htmlspecialchars($desc) ?></p>
                                    <?php endif; ?>

                                    <div class="course-meta">
                                        👥 <?= $stu ?> <?= $stu === 1 ? 'estudiante' : 'estudiantes' ?>
                                        <?php if (!empty($curso['duracion_min']) && $curso['duracion_min'] > 0):
                                            $h = intdiv((int)$curso['duracion_min'], 60);
                                            $m = (int)$curso['duracion_min'] % 60;
                                        ?>
                                            &nbsp;·&nbsp; ⏱ <?= $h > 0 ? $h . 'h ' : '' ?><?= $m > 0 ? $m . 'min' : '' ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-footer-row">
                                        <span class="course-price <?= $precio <= 0 ? 'gratis' : '' ?>">
                                            <?= $precio > 0 ? number_format($precio, 2) . '€' : 'Gratis' ?>
                                        </span>
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

                <!-- Paginación -->
                <?php if ($totalPaginas > 1): ?>
                    <nav class="mt-5 d-flex justify-content-center">
                        <ul class="pagination">
                            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildUrl(['p' => $pagina - 1]) ?>">&laquo;</a>
                            </li>
                            <?php
                            // Mostrar máximo 7 páginas centradas en la actual
                            $rango = 3;
                            $inicio = max(1, $pagina - $rango);
                            $fin    = min($totalPaginas, $pagina + $rango);
                            if ($inicio > 1): ?>
                                <li class="page-item"><a class="page-link" href="<?= buildUrl(['p' => 1]) ?>">1</a></li>
                                <?php if ($inicio > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                            <?php endif; ?>
                            <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= buildUrl(['p' => $i]) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($fin < $totalPaginas): ?>
                                <?php if ($fin < $totalPaginas - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                                <li class="page-item"><a class="page-link" href="<?= buildUrl(['p' => $totalPaginas]) ?>"><?= $totalPaginas ?></a></li>
                            <?php endif; ?>
                            <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildUrl(['p' => $pagina + 1]) ?>">&raquo;</a>
                            </li>
                        </ul>
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
                    }
                });
        });
    </script>
</body>

</html>
