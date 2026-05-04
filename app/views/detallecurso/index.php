<?php
$titulo   = htmlspecialchars($curso['titulo'] ?? 'Sin título');
$desc     = $curso['descripcion'] ?? '';
$precio   = (float)($curso['precio'] ?? 0);
$imagen   = !empty($curso['imagen']) ? BASE_URL . '/img/' . $curso['imagen'] : null;
$duracion = isset($curso['duracion_min']) ? (int)$curso['duracion_min'] : null;
$alumnos  = (int)($curso['total_matriculas'] ?? 0);
$tieneAccesoCurso = $estaMatriculado || ($usuarioId && $planPermiteAcceso);
$mostrarSidebarCompra = !$tieneAccesoCurso;

function fmtDur(?int $min): string
{
    if (!$min || $min <= 0) return '';
    return sprintf('%dh %02dm', intdiv($min, 60), $min % 60);
}
function fmtFecha(?string $fecha): string
{
    if (!$fecha) return '—';
    $d = DateTime::createFromFormat('Y-m-d', substr($fecha, 0, 10));
    return $d ? $d->format('d/m/Y') : $fecha;
}
$totalLecciones = array_sum(array_map(fn($u) => count($u['lecciones'] ?? []), $unidades));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-dark: #1B2336;
            --mc-navy: #0f172a;
            --mc-gray: #f3f4f6;
            --mc-border: #e5e7eb;
            --mc-text: #374151;
            --mc-muted: #6b7280;
            --mc-soft: #F8F8F8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: #fff;
            color: var(--mc-dark);
            font-family: 'Saira', sans-serif;
            margin: 0;
        }

        /* ── HERO ── */
        .curso-hero {
            background: linear-gradient(135deg, var(--mc-navy) 0%, #1a2e3a 100%);
            color: #fff;
            padding: 2.5rem 0 2rem;
            border-bottom: 3px solid var(--mc-green);
            position: relative;
            overflow: hidden;
        }

        .curso-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%236B8F71' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .curso-hero h1 {
            font-family: Georgia, serif;
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 700;
            letter-spacing: -.3px;
            margin-bottom: .5rem;
        }

        .curso-hero .desc-hero {
            color: #c9d5cb;
            font-size: .95rem;
            max-width: 680px;
            line-height: 1.65;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .78rem;
            background: rgba(107, 143, 113, .2);
            border: 1px solid rgba(107, 143, 113, .3);
            border-radius: 20px;
            padding: 3px 12px;
            margin: 3px 4px 3px 0;
            color: #b8d4bc;
        }

        .precio-grande {
            font-size: 2rem;
            font-weight: 900;
            color: #fff;
        }

        .precio-grande.gratis {
            color: #6ee7b7;
        }

        /* ── LAYOUT ── */
        .curso-body {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 2rem;
            padding: 2rem 0 3rem;
            align-items: start;
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 18px;
            padding-right: 18px;
        }

        .curso-body.sin-sidebar {
            grid-template-columns: minmax(0, 1fr);
            max-width: 980px;
        }

        @media(max-width: 900px) {
            .curso-body {
                grid-template-columns: 1fr;
            }

            .sidebar-sticky {
                position: static !important;
            }

            .tabs-header-row {
                flex-wrap: wrap;
            }
        }

        /* ── AVISO EXPIRACIÓN ── */
        .expiry-strip {
            display: flex; align-items: center; gap: 10px;
            padding: .6rem .9rem; border-radius: 10px;
            margin-top: .6rem; margin-bottom: 1.1rem;
            font-size: .82rem;
        }
        .expiry-strip--ok     { background: #f0fdf4; color: #166534; }
        .expiry-strip--warn   { background: #fffbeb; color: #92400e; }
        .expiry-strip--danger { background: #fef2f2; color: #991b1b; }
        .expiry-strip svg { flex-shrink: 0; }
        .expiry-strip-text { flex: 1; min-width: 0; }
        .expiry-strip-label { font-weight: 700; }
        .expiry-strip-sub { font-size: .76rem; opacity: .78; }
        .expiry-ring { flex-shrink: 0; width: 44px; height: 44px; }
        .expiry-ring circle { fill: none; stroke-width: 4; stroke-linecap: round; }
        .expiry-ring .ring-bg { stroke: rgba(0,0,0,.08); }
        .expiry-ring .ring-fill { transition: stroke-dashoffset .5s ease; transform: rotate(-90deg); transform-origin: center; }

        /* ── MATRICULA OK ── */
        .matricula-ok {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            border-radius: 10px;
            padding: .85rem 1.2rem;
            color: #065f46;
            font-weight: 600;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }


        .tabs-header-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: .25rem;
        }

        .acceso-chip-btn-solo {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--mc-green, #6B8F71);
            color: #fff;
            border-radius: 8px;
            padding: .35rem .85rem;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background .13s;
        }

        .acceso-chip-btn-solo:hover { background: #557a5c; color: #fff; }

        /* ── TABS ── */
        .curso-tabs .nav-link {
            font-family: 'Saira', sans-serif;
            font-weight: 600;
            color: var(--mc-muted);
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: .65rem 1.1rem;
            font-size: .9rem;
            transition: color .15s;
        }

        .curso-tabs .nav-link.active {
            color: var(--mc-green);
            border-bottom-color: var(--mc-green);
            background: transparent;
        }

        .tab-content {
            padding: 1.5rem 0;
        }

        /* ── INFORMACIÓN ── */
        .info-seccion-titulo {
            font-family: Georgia, serif;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: .75rem;
            color: var(--mc-dark);
        }

        .info-texto {
            color: var(--mc-text);
            line-height: 1.75;
            font-size: .94rem;
        }

        .que-aprenderas {
            background: var(--mc-soft);
            border-radius: 12px;
            padding: 1.2rem 1.4rem;
            margin-top: 1.5rem;
            border: 1px solid var(--mc-border);
        }

        .que-aprenderas h6 {
            font-weight: 700;
            margin-bottom: .75rem;
            color: var(--mc-dark);
        }

        .que-aprenderas ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .4rem .75rem;
        }

        @media(max-width: 600px) {
            .que-aprenderas ul {
                grid-template-columns: 1fr;
            }
        }

        .que-aprenderas li {
            font-size: .88rem;
            color: var(--mc-text);
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: .4rem;
        }

        .que-aprenderas li::before {
            content: '✔';
            color: var(--mc-green);
            flex-shrink: 0;
            font-size: .85rem;
            margin-top: 1px;
        }

        .info-datos {
            list-style: none;
            padding: 0;
            margin: 1.25rem 0 0;
            border-top: 1px solid var(--mc-border);
            padding-top: 1.25rem;
            font-size: .9rem;
            color: var(--mc-text);
        }

        .info-datos li {
            padding: 4px 0;
        }

        .certificado-box {
            margin-top: 1.5rem;
            background: linear-gradient(135deg, #d1fae5, #ecfdf5);
            border: 1px solid #6ee7b7;
            border-radius: 12px;
            padding: 1.1rem 1.3rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .certificado-box .cert-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }

        .certificado-box h6 {
            font-weight: 700;
            color: #065f46;
            margin-bottom: .25rem;
        }

        .certificado-box p {
            font-size: .84rem;
            color: #047857;
            margin: 0;
            line-height: 1.6;
        }

        /* ── TEMARIO ── */
        .unidad-bloque {
            border: 1px solid var(--mc-border);
            border-radius: 10px;
            margin-bottom: .6rem;
            overflow: hidden;
        }

        .unidad-titulo {
            padding: .85rem 1.1rem;
            font-weight: 700;
            font-size: .88rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--mc-gray);
            user-select: none;
            transition: background .15s;
        }

        .unidad-titulo:hover {
            background: #e5e7eb;
        }

        .unidad-titulo .chevron {
            transition: transform .25s;
            color: var(--mc-muted);
            font-size: .75rem;
        }

        .unidad-titulo.abierta .chevron {
            transform: rotate(180deg);
        }

        .unidad-lecciones {
            display: none;
        }

        .unidad-lecciones.visible {
            display: block;
        }

        .leccion-row {
            padding: .6rem 1.1rem .6rem 1.8rem;
            font-size: .86rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            border-top: 1px solid var(--mc-border);
            color: var(--mc-text);
        }

        .leccion-row a {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: .6rem;
            width: 100%;
            transition: color .15s;
        }

        .leccion-row a:hover {
            color: var(--mc-green);
        }

        .leccion-row .licon {
            color: var(--mc-green);
            font-size: .85rem;
        }

        .leccion-row .licon-lock {
            color: #9ca3af;
        }

        /* ── TAREAS ── */
        .tarea-item {
            background: linear-gradient(180deg, #ffffff 0%, #fafbfd 100%);
            border: 1px solid var(--mc-border);
            border-radius: 16px;
            padding: 1rem 1.1rem;
            margin-bottom: .8rem;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
        }

        .tarea-titulo {
            font-weight: 800;
            font-size: .98rem;
        }

        .tarea-desc {
            font-size: .84rem;
            color: var(--mc-muted);
            margin-top: 6px;
        }

        .tarea-fecha {
            font-size: .78rem;
            font-weight: 700;
            white-space: nowrap;
            background: #fff;
            border: 1px solid var(--mc-border);
            border-radius: 999px;
            padding: 6px 10px;
            color: var(--mc-green-d);
            flex-shrink: 0;
        }

        .tarea-fecha.vencida {
            color: #dc2626;
            border-color: #fca5a5;
            background: #fff7f7;
        }

        .tarea-panel-top {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            align-items: center;
            margin-bottom: .55rem;
        }

        .tarea-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: .72rem;
            font-weight: 800;
        }

        .tarea-chip-soft {
            background: #eef3f7;
            color: var(--mc-dark);
        }

        .tarea-chip-accent {
            background: rgba(107, 143, 113, .12);
            color: var(--mc-green-d);
        }

        .tarea-body-meta {
            display: grid;
            gap: .45rem;
        }

        .tarea-lesson-ref {
            font-size: .78rem;
            color: var(--mc-muted);
            font-weight: 700;
        }

        .tarea-empty {
            padding: 1.2rem 1.3rem;
            border-radius: 16px;
            border: 1px solid var(--mc-border);
            background: #f8fafc;
            color: var(--mc-muted);
        }

        /* ── SIDEBAR ── */
        .sidebar-sticky {
            position: sticky;
            top: 1.5rem;
        }

        .sidebar-card {
            border: 1px solid var(--mc-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(0, 0, 0, .07);
        }

        .sidebar-body {
            padding: 1.3rem;
        }

        .sidebar-price {
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--mc-dark);
            margin-bottom: 1rem;
        }

        .sidebar-price.gratis {
            color: var(--mc-green);
        }

        .btn-mc {
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: .72rem 1.4rem;
            font-weight: 700;
            width: 100%;
            font-size: .95rem;
            font-family: 'Saira', sans-serif;
            transition: background .2s, transform .1s;
            cursor: pointer;
            text-align: center;
            display: block;
            text-decoration: none;
        }

        .btn-mc:hover {
            background: var(--mc-green-d);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-mc:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .btn-mc-outline {
            background: transparent;
            border: 2px solid var(--mc-green);
            color: var(--mc-green);
            border-radius: 9px;
            padding: .65rem 1.4rem;
            font-weight: 700;
            width: 100%;
            font-size: .92rem;
            font-family: 'Saira', sans-serif;
            transition: all .2s;
            cursor: pointer;
            text-align: center;
            display: block;
            text-decoration: none;
            margin-top: .6rem;
        }

        .btn-mc-outline:hover {
            background: var(--mc-green);
            color: #fff;
        }

        .plan-aviso {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: .65rem .9rem;
            font-size: .82rem;
            color: #92400e;
            margin-top: .75rem;
        }

        .plan-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            font-size: .78rem;
            font-weight: 700;
            border-radius: 20px;
            padding: 2px 10px;
            margin-bottom: .75rem;
        }

        .sidebar-meta {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
            font-size: .82rem;
            color: var(--mc-muted);
        }

        .sidebar-meta li {
            padding: 3px 0;
        }
    </style>
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <!-- HERO -->
    <section class="curso-hero">
        <div style="max-width:1220px;margin:0 auto;padding:0 18px">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <h1><?= $titulo ?></h1>
                    <?php if ($desc): ?>
                        <p class="desc-hero"><?= htmlspecialchars(mb_strimwidth($desc, 0, 220, '…')) ?></p>
                    <?php endif; ?>
                    <div class="mt-3">
                        <span class="hero-tag">👥 <?= $alumnos ?> <?= $alumnos === 1 ? 'estudiante' : 'estudiantes' ?></span>
                        <?php if ($duracion): ?><span class="hero-tag">⏱ <?= fmtDur($duracion) ?></span><?php endif; ?>
                        <?php if ($totalLecciones): ?><span class="hero-tag">📖 <?= $totalLecciones ?> lección<?= $totalLecciones !== 1 ? 'es' : '' ?></span><?php endif; ?>
                        <?php if (!empty($tareas)): ?><span class="hero-tag">📋 <?= count($tareas) ?> tarea<?= count($tareas) !== 1 ? 's' : '' ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BODY -->
    <div class="curso-body<?= $mostrarSidebarCompra ? '' : ' sin-sidebar' ?>">

        <!-- COLUMNA PRINCIPAL -->
        <div>
            <?php if ($mensajeMatricula === 'exito'): ?>
                <div class="matricula-ok">
                    ✅ ¡Te has matriculado en <strong><?= $titulo ?></strong>!
                    <?php
                    $primeraLeccion = $modeloCurso->getPrimeraLeccion($curso['id']);
                    $urlCurso = $primeraLeccion
                        ? BASE_URL . '/index.php?url=leccion&id=' . $primeraLeccion['id']
                        : BASE_URL . '/index.php?url=detallecurso&id=' . $curso['id'];
                    ?>
                    <a href="<?= $urlCurso ?>" class="btn-mc">▶ Ir al curso</a>
                </div>
            <?php elseif ($estaMatriculado): ?>
                <?php
                $primeraLeccion = $modeloCurso->getPrimeraLeccion($curso['id']);
                $urlCurso = $primeraLeccion
                    ? BASE_URL . '/index.php?url=leccion&id=' . $primeraLeccion['id']
                    : BASE_URL . '/index.php?url=detallecurso&id=' . $curso['id'];
                ?>
                <?php if ($fechaExpiracion): ?>
                    <?php
                    $d = $diasParaExpirar;
                    if ($d <= 0) {
                        $cls = 'expiry-notice--danger';
                        $barColor = '#ef4444';
                        $pctUsed = 100;
                        $msg = 'Tu acceso a este curso ha expirado.';
                        $sub = 'El plazo de 90 días desde la matrícula venció el ' . $fechaExpiracion . '.';
                    } elseif ($d <= 14) {
                        $cls = 'expiry-notice--warn';
                        $barColor = '#f59e0b';
                        $pctUsed = max(0, min(100, round((1 - $d/90)*100)));
                        $msg = 'Acceso expira pronto — quedan ' . $d . ' día' . ($d !== 1 ? 's' : '') . '.';
                        $sub = 'Fecha límite: ' . $fechaExpiracion . '. Organiza tus sesiones para terminar a tiempo.';
                    } else {
                        $cls = 'expiry-notice--ok';
                        $barColor = '#22c55e';
                        $pctUsed = max(0, min(100, round((1 - $d/90)*100)));
                        $msg = 'Acceso activo — ' . $d . ' días restantes.';
                        $sub = 'Este acceso expira el ' . $fechaExpiracion . ' (90 días desde la matrícula).';
                    }
                    ?>
                    <?php
                    $circumference = 2 * M_PI * 16; // r=16
                    $pctLeft = 100 - $pctUsed;
                    $dashOffset = $circumference * (1 - $pctLeft / 100);
                    $stripCls = str_replace('expiry-notice', 'expiry-strip', $cls);
                    ?>
                    <div class="expiry-strip <?= $stripCls ?>">
                        <svg class="expiry-ring" viewBox="0 0 44 44">
                            <circle class="ring-bg"   cx="22" cy="22" r="16" />
                            <circle class="ring-fill" cx="22" cy="22" r="16"
                                stroke="<?= $barColor ?>"
                                stroke-dasharray="<?= $circumference ?>"
                                stroke-dashoffset="<?= $dashOffset ?>" />
                        </svg>
                        <div class="expiry-strip-text">
                            <div class="expiry-strip-label"><?= $msg ?></div>
                            <div class="expiry-strip-sub"><?= htmlspecialchars($sub) ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ($usuarioId && $planPermiteAcceso): ?>
                <div class="curso-acceso">
                    <div class="curso-acceso-copy">
                        <strong>Este curso está incluido en tu plan</strong>
                        <span>Activa el acceso y añádelo a tu espacio de aprendizaje.</span>
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>">
                        <input type="hidden" name="accion" value="matricular">
                        <button type="submit" class="btn-mc">Activar acceso</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- TABS -->
            <div class="tabs-header-row">
                <ul class="nav curso-tabs border-bottom mb-1" style="flex:1;border-bottom:none!important;margin-bottom:0!important">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">Información</button>
                    </li>
                    <?php if (!empty($unidades)): ?>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-temario">
                                Temario
                                <span class="badge ms-1" style="background:var(--mc-green);font-size:.68rem;"><?= count($unidades) ?></span>
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php if ($estaMatriculado): ?>
                    <?php
                    $_pl = $modeloCurso->getPrimeraLeccion($curso['id']);
                    $_urlChip = $_pl
                        ? BASE_URL . '/index.php?url=leccion&id=' . $_pl['id']
                        : BASE_URL . '/index.php?url=detallecurso&id=' . $curso['id'];
                    ?>
                    <a href="<?= $_urlChip ?>" class="acceso-chip-btn-solo">▶ Ir al curso</a>
                <?php endif; ?>
            </div>
            <div class="border-bottom mb-1"></div>

            <div class="tab-content">

                <!-- ── INFORMACIÓN ── -->
                <div class="tab-pane fade show active" id="tab-info">

                    <p class="info-seccion-titulo">Sobre este curso</p>
                    <p class="info-texto">
                        <?= $desc ? nl2br(htmlspecialchars($desc)) : '' ?>
                    </p>

                    <?php if (!empty($curso['info_extra'])): ?>
                        <p class="info-texto" style="margin-top:.85rem;">
                            <?= nl2br(htmlspecialchars($curso['info_extra'])) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($curso['que_aprenderas'])): ?>
                        <div class="que-aprenderas">
                            <h6>¿Qué aprenderás?</h6>
                            <ul>
                                <?php foreach (explode("\n", $curso['que_aprenderas']) as $punto): ?>
                                    <?php if (trim($punto)): ?>
                                        <li><?= htmlspecialchars(trim($punto)) ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="certificado-box">
                        <div class="cert-icon">🏆</div>
                        <div>
                            <h6>Certificado de finalización</h6>
                            <p>
                                Al completar todas las lecciones recibirás un <strong>certificado oficial de MatrixCoders</strong>
                                que acredita tus conocimientos. Puedes añadirlo a tu perfil de LinkedIn, tu portfolio o
                                adjuntarlo en procesos de selección. Reconocido como formación complementaria
                                en el sector tecnológico.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ── TEMARIO ── -->
                <?php if (!empty($unidades)): ?>
                    <div class="tab-pane fade" id="tab-temario">
                        <p style="font-size:.85rem;color:var(--mc-muted);margin-bottom:1rem;">
                            <?= count($unidades) ?> unidad<?= count($unidades) !== 1 ? 'es' : '' ?> · <?= $totalLecciones ?> lección<?= $totalLecciones !== 1 ? 'es' : '' ?>
                        </p>
                        <?php foreach ($unidades as $i => $u): ?>
                            <div class="unidad-bloque">
                                <div class="unidad-titulo <?= $i === 0 ? 'abierta' : '' ?>" onclick="toggleUnidad(this)">
                                    <div>
                                        <div><?= htmlspecialchars($u['titulo'] ?? 'Unidad ' . ($i + 1)) ?></div>
                                        <small style="color:var(--mc-muted);font-weight:400;font-size:.78rem;">
                                            <?= count($u['lecciones'] ?? []) ?> lección<?= count($u['lecciones'] ?? []) !== 1 ? 'es' : '' ?>
                                        </small>
                                    </div>
                                    <span class="chevron">▼</span>
                                </div>
                                <div class="unidad-lecciones <?= $i === 0 ? 'visible' : '' ?>">
                                    <?php foreach (($u['lecciones'] ?? []) as $lec): ?>
                                        <div class="leccion-row">
                                            <?php if ($estaMatriculado): ?>
                                                <a href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $lec['id'] ?>">
                                                    <span class="licon">▶</span>
                                                    <?= htmlspecialchars($lec['titulo'] ?? 'Lección') ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="licon-lock">🔒</span>
                                                <span style="color:#9ca3af;"><?= htmlspecialchars($lec['titulo'] ?? 'Lección') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- ── TAREAS ── -->
                <div class="tab-pane fade" id="tab-tareas">
                    <?php if (empty($tareas)): ?>
                        <div class="tarea-empty">
                            <p style="font-weight:700;margin:0 0 4px">Sin tareas asignadas</p>
                            <p style="font-size:.82rem;margin:0;line-height:1.6">
                                Este curso todavía no tiene tareas publicadas.
                                Cuando el instructor añada tareas con fecha límite aparecerán aquí.
                            </p>
                        </div>
                    <?php else: ?>
                        <?php
                        // Estadísticas rápidas de tareas (solo si el usuario está matriculado)
                        if ($estaMatriculado):
                            $totalT     = count($tareas);
                            $entregadas = count(array_filter($tareas, fn($t) => $t['estado_visual'] === 'entregada'));
                            $vencidas   = count(array_filter($tareas, fn($t) => $t['estado_visual'] === 'vencida'));
                            $proximas   = count(array_filter($tareas, fn($t) => $t['estado_visual'] === 'proxima'));
                        ?>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:1.2rem">
                            <span style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;padding:5px 13px;font-size:.77rem;font-weight:700;color:#166534">
                                ✔ <?= $entregadas ?>/<?= $totalT ?> entregadas
                            </span>
                            <?php if ($proximas > 0): ?>
                            <span style="background:#fffbeb;border:1px solid #fde68a;border-radius:99px;padding:5px 13px;font-size:.77rem;font-weight:700;color:#92400e">
                                ⚡ <?= $proximas ?> próxima<?= $proximas !== 1 ? 's' : '' ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($vencidas > 0): ?>
                            <span style="background:#fef2f2;border:1px solid #fecaca;border-radius:99px;padding:5px 13px;font-size:.77rem;font-weight:700;color:#991b1b">
                                ⚠ <?= $vencidas ?> vencida<?= $vencidas !== 1 ? 's' : '' ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php foreach ($tareas as $t):
                            $estadoVisual  = $t['estado_visual'] ?? 'pendiente';
                            $vencida       = $estadoVisual === 'vencida';
                            $entregada     = $estadoVisual === 'entregada';
                            $proxima       = $estadoVisual === 'proxima';
                            $diasRestantes = $t['dias_restantes'] ?? null;

                            $chipLabel = match($estadoVisual) {
                                'entregada' => '✔ Entregada',
                                'vencida'   => '⚠ Vencida',
                                'proxima'   => $diasRestantes === 0 ? 'Entrega hoy' : 'En '.$diasRestantes.'d',
                                default     => 'Tarea del curso',
                            };
                            $chipStyle = match($estadoVisual) {
                                'entregada' => 'background:#d1fae5;color:#065f46',
                                'vencida'   => 'background:#fee2e2;color:#991b1b',
                                'proxima'   => 'background:#fef9c3;color:#854d0e',
                                default     => '',
                            };
                        ?>
                            <div class="tarea-item">
                                <div class="tarea-body-meta">
                                    <div class="tarea-panel-top">
                                        <span class="tarea-chip tarea-chip-soft" style="<?= $chipStyle ?>"><?= $chipLabel ?></span>
                                        <?php if (!empty($t['leccion_titulo'])): ?>
                                            <span class="tarea-chip tarea-chip-accent"><?= htmlspecialchars($t['leccion_titulo']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tarea-titulo"><?= htmlspecialchars($t['titulo'] ?? '') ?></div>
                                    <?php if (!empty($t['descripcion'])): ?>
                                        <div class="tarea-desc"><?= htmlspecialchars($t['descripcion']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($entregada && !empty($t['entregado_en'])): ?>
                                        <span class="tarea-lesson-ref" style="color:#15803d">
                                            Entregada el <?= fmtFecha($t['entregado_en']) ?>
                                            <?= $t['entrega_nota'] !== null ? ' · Nota: ' . htmlspecialchars($t['entrega_nota']) : '' ?>
                                        </span>
                                    <?php elseif (!empty($t['leccion_titulo'])): ?>
                                        <span class="tarea-lesson-ref">Asociada a: <?= htmlspecialchars($t['leccion_titulo']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($t['fecha_limite'])): ?>
                                    <span class="tarea-fecha <?= $vencida ? 'vencida' : '' ?>">
                                        <?= $vencida ? '⚠ ' : '📅 ' ?><?= fmtFecha($t['fecha_limite']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!$estaMatriculado): ?>
                            <p style="font-size:.8rem;color:var(--mc-muted);margin-top:.5rem;text-align:center">
                                Matricúlate para ver tu progreso en las tareas.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div><!-- /tab-content -->
        </div><!-- /columna principal -->

        <?php if ($mostrarSidebarCompra): ?>
            <!-- SIDEBAR -->
            <aside class="sidebar-sticky">
                <div class="sidebar-card">
                    <div class="sidebar-body">
                        <?php if ($precio <= 0): ?>
                            <div class="sidebar-price gratis">Gratis</div>
                        <?php elseif (($descuentoActivo ?? 0) > 0): ?>
                            <div style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;margin-bottom:4px">
                                <span style="font-size:1.1rem;color:#6b7280;text-decoration:line-through"><?= number_format($precio, 2) ?>€</span>
                                <span class="sidebar-price" style="margin-bottom:0"><?= number_format($precioFinal, 2) ?>€</span>
                                <span style="background:#ef4444;color:#fff;font-size:.75rem;font-weight:800;padding:2px 8px;border-radius:99px">-<?= round($descuentoActivo) ?>%</span>
                            </div>
                            <p style="font-size:.78rem;color:#ef4444;font-weight:600;margin:0 0 12px">¡Oferta por tiempo limitado!</p>
                        <?php else: ?>
                            <div class="sidebar-price"><?= number_format($precio, 2) ?>€</div>
                        <?php endif; ?>

                        <?php if ($precio <= 0): ?>
                            <form method="POST" action="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>">
                                <input type="hidden" name="accion" value="matricular">
                                <?php if (!$usuarioId): ?>
                                    <a href="<?= BASE_URL ?>/index.php?url=login" class="btn-mc">Matricularme gratis</a>
                                <?php else: ?>
                                    <button type="submit" class="btn-mc">Matricularme gratis</button>
                                <?php endif; ?>
                            </form>

                        <?php else: ?>
                            <?php if (!$usuarioId): ?>
                                <a href="<?= BASE_URL ?>/index.php?url=login" class="btn-mc">Iniciar sesión</a>
                            <?php else: ?>
                                <button class="btn-mc"
                                    onclick="abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precioFinal ?? $precio ?>, <?= $precio ?>, <?= $descuentoActivo ?? 0 ?>, '<?= htmlspecialchars(addslashes($imagen ?? '')) ?>')">
                                    🛒 Añadir al carrito
                                </button>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/index.php?url=suscripciones" class="btn-mc-outline">Ver planes</a>
                            <div class="plan-aviso">
                                Con el <strong>Plan Estudiantes</strong> tienes acceso a todos los cursos.
                            </div>
                        <?php endif; ?>

                        <ul class="sidebar-meta">
                            <?php if ($duracion): ?><li>⏱ <?= fmtDur($duracion) ?> de contenido</li><?php endif; ?>
                            <?php if ($totalLecciones): ?><li>📖 <?= $totalLecciones ?> lecciones</li><?php endif; ?>
                            <?php if (!empty($tareas)): ?><li>📋 <?= count($tareas) ?> tareas</li><?php endif; ?>
                            <li>📱 Acceso en todos los dispositivos</li>
                            <li>🏆 Certificado de finalización</li>
                        </ul>
                    </div>
                </div>
            </aside>
        <?php endif; ?>

    </div><!-- /curso-body -->

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

                <!-- Close -->
                <button type="button" class="mc-modal-x" data-bs-dismiss="modal" aria-label="Cerrar">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Course card -->
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

                <!-- Divider -->
                <div class="mc-modal-divider"></div>

                <!-- Trust chips -->
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

                <!-- Error -->
                <div id="modal-error" class="mc-modal-error" style="display:none"></div>

                <!-- Actions -->
                <div class="mc-modal-actions">
                    <button type="button" id="btn-confirmar-carrito" class="mc-btn-add">
                        <span class="btn-cc-spinner"></span>
                        <svg class="btn-cc-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="btn-cc-label">Añadir al carrito</span>
                    </button>
                    <button type="button" class="mc-btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                </div>

                <!-- Security note -->
                <p class="mc-modal-security">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Pago seguro con <strong>Stripe</strong> · Cifrado SSL
                </p>

            </div>
        </div>
    </div>

    <style>
    /* ── Toast ── */
    #toastCarrito{position:fixed;bottom:28px;right:28px;z-index:9999;opacity:0;transform:translateY(20px);transition:opacity .3s ease,transform .3s ease;pointer-events:none}
    #toastCarrito.show{opacity:1;transform:translateY(0);pointer-events:auto}
    .mc-toast-inner{background:#1B2336;color:#fff;border-radius:14px;padding:13px 20px;display:flex;align-items:center;gap:10px;font-size:.88rem;font-weight:600;box-shadow:0 8px 36px rgba(0,0,0,.25);font-family:'Saira',sans-serif;white-space:nowrap}
    .mc-toast-check{background:#6B8F71;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
    .mc-toast-link{color:#6B8F71;font-weight:800;margin-left:6px;text-decoration:none;white-space:nowrap}
    .mc-toast-link:hover{text-decoration:underline}

    /* ── Dialog ── */
    .mc-cart-dialog{max-width:460px}
    .mc-cart-modal{border-radius:20px!important;border:none!important;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.18)!important;font-family:'Saira',sans-serif;padding:0}

    /* ── Close button ── */
    .mc-modal-x{position:absolute;top:14px;right:14px;z-index:10;width:30px;height:30px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6b7280;transition:all .15s}
    .mc-modal-x:hover{background:#f3f4f6;color:#1B2336}

    /* ── Course card row ── */
    .mc-modal-course{display:flex;gap:16px;align-items:flex-start;padding:28px 28px 20px}
    .mc-modal-thumb-wrap{position:relative;width:88px;height:72px;border-radius:12px;overflow:hidden;flex-shrink:0;background:#f3f4f6}
    .mc-modal-thumb{width:100%;height:100%;object-fit:cover;display:block}
    .mc-modal-thumb-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center}
    .mc-modal-thumb[src=''],.mc-modal-thumb:not([src]){display:none}
    .mc-modal-thumb[src=''] ~ .mc-modal-thumb-placeholder,.mc-modal-thumb:not([src]) ~ .mc-modal-thumb-placeholder{display:flex}
    .mc-modal-course-info{flex:1;min-width:0}
    .mc-modal-pretitle{font-size:.7rem;font-weight:700;color:#6B8F71;text-transform:uppercase;letter-spacing:.8px;display:block;margin-bottom:5px}
    .mc-modal-course-title{font-size:.95rem;font-weight:800;color:#1B2336;margin:0 0 10px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .mc-modal-pricing{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
    .mc-price-old{font-size:.83rem;color:#9ca3af;text-decoration:line-through;font-weight:500}
    .mc-price-main{font-size:1.35rem;font-weight:900;color:#1B2336;line-height:1}
    .mc-disc-badge{background:#ef4444;color:#fff;font-size:.65rem;font-weight:800;border-radius:6px;padding:3px 7px;letter-spacing:.3px}

    /* ── Divider ── */
    .mc-modal-divider{height:1px;background:#f0f0f0;margin:0 28px}

    /* ── Trust chips ── */
    .mc-modal-trust{display:flex;gap:6px;flex-wrap:wrap;padding:16px 28px}
    .mc-trust-chip{display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:600;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:99px;padding:4px 10px}

    /* ── Error ── */
    .mc-modal-error{margin:0 28px 12px;padding:10px 14px;border-radius:10px;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;font-size:.82rem;font-weight:600}

    /* ── Actions ── */
    .mc-modal-actions{padding:4px 28px 0;display:flex;flex-direction:column;gap:10px}
    .mc-btn-add{width:100%;padding:15px;border-radius:13px;border:none;background:#1B2336;color:#fff;font-weight:800;font-size:.95rem;font-family:'Saira',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .18s,transform .1s;position:relative;overflow:hidden}
    .mc-btn-add:hover:not(:disabled){background:#0f172a;transform:translateY(-1px)}
    .mc-btn-add:active:not(:disabled){transform:translateY(0)}
    .mc-btn-add:disabled{opacity:.6;cursor:not-allowed;transform:none}
    .mc-btn-add.success{background:#16a34a}
    .btn-cc-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:mcSpin .65s linear infinite;flex-shrink:0}
    .mc-btn-add.loading .btn-cc-spinner{display:block}
    .mc-btn-add.loading .btn-cc-icon,.mc-btn-add.loading .btn-cc-label{display:none}
    .mc-btn-cancel{width:100%;padding:11px;border-radius:10px;border:none;background:transparent;color:#6b7280;font-size:.85rem;font-weight:600;font-family:'Saira',sans-serif;cursor:pointer;transition:color .15s}
    .mc-btn-cancel:hover{color:#1B2336}

    /* ── Security note ── */
    .mc-modal-security{text-align:center;font-size:.72rem;color:#9ca3af;margin:8px 28px 22px;display:flex;align-items:center;justify-content:center;gap:5px}
    .mc-modal-security strong{color:#6b7280}

    @keyframes mcSpin{to{transform:rotate(360deg)}}
    </style>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const TAB_KEY = 'tab_curso_' + <?= $curso['id'] ?>;

        // Al cargar, restaurar tab guardado
        const tabGuardado = localStorage.getItem(TAB_KEY);
        if (tabGuardado) {
            const tabEl = document.querySelector(`[data-bs-target="${tabGuardado}"]`);
            if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
        }

        // Al cambiar tab, guardarlo
        document.querySelectorAll('.curso-tabs .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', e => {
                localStorage.setItem(TAB_KEY, e.target.getAttribute('data-bs-target'));
            });
        });

        function toggleUnidad(el) {
            const lista = el.nextElementSibling;
            el.classList.toggle('abierta');
            lista.classList.toggle('visible', el.classList.contains('abierta'));
        }

        let cursoSeleccionado = null;

        function abrirModal(id, titulo, precioFinal, precioOriginal = 0, descuento = 0, imagen = '') {
            cursoSeleccionado = id;
            const btn     = document.getElementById('btn-confirmar-carrito');
            const elError = document.getElementById('mc-modal-error') || document.getElementById('modal-error');

            // Reset button state
            btn.disabled = false;
            btn.classList.remove('loading', 'success');
            if (elError) elError.style.display = 'none';

            // Course image
            const img = document.getElementById('mc-modal-img');
            const placeholder = img?.nextElementSibling;
            if (img) {
                if (imagen) {
                    img.src = imagen;
                    img.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                    img.onerror = () => { img.style.display = 'none'; if (placeholder) placeholder.style.display = 'flex'; };
                } else {
                    img.src = '';
                    img.style.display = 'none';
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
            fetch(BASE_URL + '/index.php?url=carrito-añadir', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        // Update badge
                        let badge = document.querySelector('.carrito-badge');
                        if (data.total > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.className = 'notif-bell-badge carrito-badge';
                                document.querySelector('a[aria-label="carrito"]')?.appendChild(badge);
                            }
                            badge.textContent = data.total;
                            badge.classList.add('visible');
                        }
                        // Success state on button
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
                        elError.textContent = data.mensaje || (data.estado === 'ya_en_carrito'
                            ? 'Este curso ya está en tu cesta.' : 'No se ha podido añadir.');
                        elError.style.display = 'block';
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.classList.remove('loading');
                });
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
