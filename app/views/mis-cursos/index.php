<?php
require_once __DIR__ . '/../../helpers/curso_imagen.php';

$filtroActual = $_GET['filtro'] ?? 'todos';
$cursos = $cursos ?? [];

$totalCursos      = count($cursos ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle ?? 'Mis cursos') ?> — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/mis-cursos.css">
</head>
<body>

<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="main-dashboard">
    <div class="mc-container">
        <div class="contenedor-dashboard-content">

            <?php require __DIR__ . '/../layout/sidebar.php'; ?>

            <div class="mis-cursos-main">

                <!-- Cabecera -->
                <div class="mis-cursos-header">
                    <div>
                        <h1 class="mis-cursos-titulo">Mis cursos</h1>
                        <p class="mis-cursos-subtitulo">
                            <?= $totalCursos ?> <?= $totalCursos === 1 ? 'curso matriculado' : 'cursos matriculados' ?>
                        </p>
                    </div>
                    <div style="display:flex;gap:.6rem;flex-wrap:wrap">
                        <a href="<?= BASE_URL ?>/index.php?url=repositorio" class="mis-cursos-btn-explorar" style="background:#f0fdf4;color:#166534;border:1.5px solid #86efac">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            Repositorio
                        </a>
                        <a href="<?= BASE_URL ?>/index.php" class="mis-cursos-btn-explorar">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                            </svg>
                            Explorar cursos
                        </a>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="mis-cursos-filtros">
                    <?php
                    $filtros = [
                        'todos'       => 'Todos',
                        'en_progreso' => 'En progreso',
                        'completados' => 'Completados',
                        'sin_empezar' => 'Sin empezar',
                    ];
                    foreach ($filtros as $key => $label):
                    ?>
                    <a href="<?= BASE_URL ?>/index.php?url=mis-cursos&filtro=<?= $key ?>"
                       class="mis-cursos-filtro-btn <?= $filtroActual === $key ? 'active' : '' ?>">
                        <?= $label ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($cursos)): ?>
                <!-- Estado vacío -->
                <div class="mis-cursos-vacio">
                    <div class="mis-cursos-vacio-icono">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </div>
                    <?php if ($filtroActual === 'todos'): ?>
                    <p class="mis-cursos-vacio-titulo">Aún no estás matriculado en ningún curso</p>
                    <p class="mis-cursos-vacio-sub">Explora nuestro catálogo y encuentra el curso perfecto para ti.</p>
                    <a href="<?= BASE_URL ?>/index.php" class="mis-cursos-btn-explorar">Ver catálogo de cursos</a>
                    <?php else: ?>
                    <p class="mis-cursos-vacio-titulo">No hay cursos en esta categoría</p>
                    <p class="mis-cursos-vacio-sub">Prueba con otro filtro o explora más cursos.</p>
                    <a href="<?= BASE_URL ?>/index.php?url=mis-cursos" class="mis-cursos-btn-explorar">Ver todos mis cursos</a>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <!-- Grid de cursos -->
                <div class="mis-cursos-grid">
                    <?php foreach ($cursos as $curso): ?>
                    <?php
                        $imgSrc   = matrixcoders_curso_image($curso['imagen'] ?? null, $curso['titulo'] ?? '');
                        $progreso = (int)($curso['progreso'] ?? 0);
                        $estado   = $curso['estado'] ?? 'sin_empezar';
                        $urlLeccion = $curso['ultima_leccion_id']
                            ? BASE_URL . '/index.php?url=leccion&id=' . (int)$curso['ultima_leccion_id']
                            : BASE_URL . '/index.php?url=detallecurso&id=' . (int)$curso['id'];
                    ?>
                    <div class="mis-cursos-card">
                        <a href="<?= htmlspecialchars($urlLeccion) ?>" class="mis-cursos-card-img-wrap">
                            <img src="<?= htmlspecialchars($imgSrc) ?>"
                                 alt="<?= htmlspecialchars($curso['titulo'] ?? '') ?>"
                                 class="mis-cursos-card-img">
                            <?php if ($estado === 'completado'): ?>
                            <span class="mis-cursos-badge completado">Completado</span>
                            <?php elseif ($estado === 'en_progreso'): ?>
                            <span class="mis-cursos-badge en-progreso">En progreso</span>
                            <?php else: ?>
                            <span class="mis-cursos-badge sin-empezar">Sin empezar</span>
                            <?php endif; ?>
                        </a>

                        <div class="mis-cursos-card-body">
                            <?php if (!empty($curso['categoria'])): ?>
                            <span class="mis-cursos-categoria"><?= htmlspecialchars($curso['categoria']) ?></span>
                            <?php endif; ?>

                            <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= (int)$curso['id'] ?>"
                               class="mis-cursos-card-titulo">
                                <?= htmlspecialchars($curso['titulo'] ?? '') ?>
                            </a>

                            <!-- Barra de progreso -->
                            <div class="mis-cursos-progreso-wrap">
                                <div class="mis-cursos-progreso-barra">
                                    <div class="mis-cursos-progreso-fill" style="width: <?= $progreso ?>%"></div>
                                </div>
                                <span class="mis-cursos-progreso-label">
                                    <?= $progreso ?>% completado
                                    (<?= (int)$curso['lecciones_vistas'] ?>/<?= (int)$curso['total_lecciones'] ?> lecciones)
                                </span>
                            </div>

                            <!-- Meta info -->
                            <div class="mis-cursos-meta">
                                <?php if (!empty($curso['nivel'])): ?>
                                <span class="mis-cursos-meta-item">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                                    </svg>
                                    <?= htmlspecialchars($curso['nivel']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($curso['duracion_min'])): ?>
                                <span class="mis-cursos-meta-item">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <?= round((int)$curso['duracion_min'] / 60, 1) ?>h
                                </span>
                                <?php endif; ?>
                                <span class="mis-cursos-meta-item">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <?= date('d/m/Y', strtotime($curso['fecha_matricula'] ?? 'now')) ?>
                                </span>
                            </div>

                            <!-- Acciones -->
                            <div class="mis-cursos-acciones">
                                <a href="<?= htmlspecialchars($urlLeccion) ?>" class="mis-cursos-btn-continuar">
                                    <?php if ($estado === 'completado'): ?>
                                        Repasar
                                    <?php elseif ($estado === 'en_progreso'): ?>
                                        Continuar
                                    <?php else: ?>
                                        Empezar
                                    <?php endif; ?>
                                </a>
                                <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= (int)$curso['id'] ?>"
                                   class="mis-cursos-btn-detalle" title="Ver detalles del curso">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div><!-- /.mis-cursos-main -->
        </div><!-- /.contenedor-dashboard-content -->
    </div><!-- /.mc-container -->
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
