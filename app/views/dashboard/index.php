<?php
require_once __DIR__ . '/../../helpers/curso_imagen.php';
$monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

$firstDayTs   = strtotime(sprintf('%04d-%02d-01', $calYear, $calMonth));
$daysInMonth  = (int)date('t', $firstDayTs);
$firstWeekday = (int)date('w', $firstDayTs);

$prevTs    = strtotime('-1 month', $firstDayTs);
$nextTs    = strtotime('+1 month', $firstDayTs);
$prevYear  = (int)date('Y', $prevTs);
$prevMonth = (int)date('n', $prevTs);
$nextYear  = (int)date('Y', $nextTs);
$nextMonth = (int)date('n', $nextTs);

// Fecha actual para marcar el día de hoy en el calendario
$todayY = (int)date('Y');
$todayM = (int)date('n');
$todayD = (int)date('j');
$documentosRecientes = is_array($documentosRecientes ?? null) ? $documentosRecientes : [];
$flash = $flash ?? null;
$diasConTareas = is_array($diasConTareas ?? null) ? $diasConTareas : [];
$tareasUsuario = is_array($tareasUsuario ?? null) ? $tareasUsuario : [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle ?? 'Espacio de trabajo') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard">

                <!-- ── SIDEBAR ── -->
                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <!-- ── CONTENIDO CENTRAL ── -->
                <section class="contenido-dashboard">
                    <?php if (!empty($flash['message'])): ?>
                        <div class="dashboard-flash dashboard-flash-<?= htmlspecialchars($flash['type']) ?>">
                            <?= htmlspecialchars($flash['message']) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Banner promocional de la herramienta NotebookLMN -->
                    <div class="banner-dashboard">
                        <div class="banner-texto">
                            <span class="etiqueta-banner">NOTEBOOKLMN</span>
                            <h1>Todo tu aprendizaje, centralizado con NotebookLMN</h1>
                        </div>
                        <a class="btn-abrir" href="https://notebooklm.google.com/" target="_blank">Abrir ahora</a>
                    </div>

                    <div class="documentos">
                        <div class="dashboard-section-head">
                            <h2>Mis documentos</h2>
                            <a class="section-link" href="<?= BASE_URL ?>/index.php?url=mis-documentos">Ver todo</a>
                        </div>
                        <?php if (count($documentosRecientes) === 0): ?>
                            <p class="text-muted">Todavía no tienes documentos creados.</p>
                        <?php else: ?>
                            <div class="documentos-recientes-grid compact-doc-strip">
                                <?php foreach ($documentosRecientes as $doc): ?>
                                    <a class="documento-mini-item documento-mini-link" href="<?= BASE_URL ?>/index.php?url=documento&id=<?= (int)$doc['id'] ?>">
                                        <div class="documento-mini-icono">
                                            <img src="<?= BASE_URL ?>/img/portapapeles.png" alt="Documento">
                                        </div>
                                        <div class="documento-mini-body">
                                            <h3><?= htmlspecialchars($doc['titulo']) ?></h3>
                                            <?php if (!empty($doc['carpeta_nombre'])): ?>
                                                <p><?= htmlspecialchars($doc['carpeta_nombre']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── SEGUIR VIENDO ── -->
                    <div class="seguimiento">
                        <div class="seguimiento-cabecera">
                            <h2>Seguir viendo</h2>
                            <?php if (count($cursosEnProgreso ?? []) > 3): ?>
                                <div class="sv-flechas">
                                    <button class="sv-arrow" id="svPrev">&#8592;</button>
                                    <button class="sv-arrow" id="svNext">&#8594;</button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($cursosEnProgreso)): ?>
                            <p class="text-muted" style="margin-top:.5rem;">
                                Aún no tienes cursos.
                                <a href="<?= BASE_URL ?>/index.php" style="color:var(--mc-green);font-weight:700;">Explorar cursos →</a>
                            </p>
                        <?php else: ?>
                            <div class="sv-track-wrap">
                                <div class="sv-track" id="svTrack">
                                    <?php foreach ($cursosEnProgreso as $sc):
                                        $progreso   = $sc['progreso'];
                                        $leccionUrl = $sc['ultima_leccion_id']
                                            ? BASE_URL . '/index.php?url=leccion&id=' . $sc['ultima_leccion_id']
                                            : BASE_URL . '/index.php?url=detallecurso&id=' . $sc['id'];
                                        $imgSrc = matrixcoders_curso_image($sc['imagen'] ?? '', $sc['titulo'] ?? '');
                                    ?>
                                        <a class="sv-card" href="<?= $leccionUrl ?>">
                                            <div class="sv-thumb">
                                                <img src="<?= htmlspecialchars($imgSrc) ?>"
                                                    alt="<?= htmlspecialchars($sc['titulo']) ?>"
                                                    onerror="this.src='<?= BASE_URL ?>/img/aprendiendo.png'">
                                                <span class="sv-badge"><?= $progreso ?>%</span>
                                            </div>
                                            <div class="sv-body">
                                                <p class="sv-titulo"><?= htmlspecialchars($sc['titulo']) ?></p>
                                                <div class="sv-progress-wrap">
                                                    <div class="sv-progress-bar">
                                                        <div class="sv-progress-fill" style="width:<?= $progreso ?>%"></div>
                                                    </div>
                                                    <span class="sv-progress-label">
                                                        <?= $sc['lecciones_vistas'] ?>/<?= $sc['total_lecciones'] ?> lecciones
                                                    </span>
                                                </div>
                                                <span class="sv-continuar">Continuar →</span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>


                </section>

                <!-- ── MINI CALENDARIO (solo visual, sin eventos) ── -->
                <aside class="calendario">

                    <!-- Tarjeta del calendario mensual -->
                    <div class="tarjeta-calendario">
                        <div class="calendario-header">
                            <!-- Botón para ir al mes anterior -->
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $prevYear ?>&m=<?= $prevMonth ?>">&lt;</a>
                            <div class="selector-mes">
                                <span class="mes"><?= $monthNames[$calMonth] ?></span>
                                <span class="anyo"><?= $calYear ?></span>
                            </div>
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $nextYear ?>&m=<?= $nextMonth ?>">&gt;</a>
                        </div>

                        <!-- Cabecera con los días de la semana -->
                        <div class="calendario-semana">
                            <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span>
                            <span>Vi</span><span>Sa</span><span>Do</span>
                        </div>

                        <!-- Cuadrícula de días del mes -->
                        <div class="calendario-grid">
                            <?php
                            $offset = $firstWeekday === 0 ? 6 : $firstWeekday - 1;
                            for ($i = 0; $i < $offset; $i++) {
                                echo '<span class="dia apagado"></span>';
                            }
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $isToday = ($calYear === $todayY && $calMonth === $todayM && $d === $todayD);
                                $hasTask = in_array($d, $diasConTareas, true);
                                $classes = 'dia' . ($isToday ? ' seleccionado' : '') . ($hasTask ? ' marcado' : '');
                                echo '<span class="' . $classes . '">' . $d . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <?php if (!empty($tareasUsuario)): ?>
                        <div class="lista-eventos">
                            <div class="dashboard-section-head dashboard-section-head-inline">
                                <h2>Próximas tareas</h2>
                            </div>
                            <?php foreach (array_slice($tareasUsuario, 0, 4) as $tarea): ?>
                                <div class="evento">
                                    <span class="punto" style="background:var(--mc-green);width:8px;height:8px;border-radius:50%;flex-shrink:0;"></span>
                                    <div class="evento-texto">
                                        <p class="evento-titulo"><?= htmlspecialchars($tarea['titulo']) ?></p>
                                        <p class="evento-sub">
                                            <?= htmlspecialchars($tarea['curso'] ?? 'Curso') ?>
                                            <?php if (!empty($tarea['fecha_limite'])): ?>
                                                · <?= htmlspecialchars(date('d/m/Y', strtotime($tarea['fecha_limite']))) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="evento-badge">Tarea</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="lista-eventos">
                            <div class="dashboard-section-head dashboard-section-head-inline">
                                <h2>Tareas</h2>
                            </div>
                            <div class="evento evento-empty">
                                <div class="evento-texto">
                                    <p class="evento-titulo">No hay tareas pendientes</p>
                                    <p class="evento-sub">Cuando tengas entregas o ejercicios con fecha, aparecerán aquí.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Plan activo del usuario -->
                    <?php
                    $nombresPlan = [
                        'curso_individual' => 'Curso Individual',
                        'plan_estudiantes' => 'Plan estudiantes',
                        'plan_empresas'    => 'Plan empresas',
                    ];
                    $planActivo = $_SESSION['usuario_plan'] ?? null;
                    ?>
                    <div class="lista-eventos">
                        <div class="dashboard-section-head dashboard-section-head-inline">
                            <h2>Tu suscripción</h2>
                        </div>
                        <?php if ($planActivo && isset($nombresPlan[$planActivo])): ?>
                            <div class="evento">
                                <span class="punto" style="background:var(--mc-green);width:8px;height:8px;border-radius:50%;flex-shrink:0;"></span>
                                <div class="evento-texto">
                                    <p class="evento-titulo"><?= htmlspecialchars($nombresPlan[$planActivo]) ?></p>
                                    <p class="evento-sub">Plan activo</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="evento evento-empty">
                                <div class="evento-texto">
                                    <p class="evento-titulo">Sin suscripción activa</p>
                                    <p class="evento-sub">
                                        <a href="<?= BASE_URL ?>/index.php?url=suscripciones" style="color:var(--mc-green);font-weight:700;">Ver planes →</a>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const track = document.getElementById('svTrack');
            const btnPrev = document.getElementById('svPrev');
            const btnNext = document.getElementById('svNext');
            if (!track || !btnPrev || !btnNext) return;

            let page = 0;
            const PER_PAGE = 3;

            function cardWidth() {
                const card = track.querySelector('.sv-card');
                if (!card) return 0;
                return card.offsetWidth + (parseFloat(getComputedStyle(track).gap) || 16);
            }

            function totalPages() {
                return Math.ceil(track.children.length / PER_PAGE);
            }

            function goTo(p) {
                page = Math.max(0, Math.min(p, totalPages() - 1));
                track.style.transform = `translateX(-${page * PER_PAGE * cardWidth()}px)`;
                btnPrev.disabled = page === 0;
                btnNext.disabled = page >= totalPages() - 1;
            }

            btnPrev.addEventListener('click', () => goTo(page - 1));
            btnNext.addEventListener('click', () => goTo(page + 1));
            goTo(0);
        })();
    </script>
</body>

</html>
