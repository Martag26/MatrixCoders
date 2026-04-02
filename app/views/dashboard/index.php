<?php
$monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

$firstDayTs  = strtotime(sprintf('%04d-%02d-01', $calYear, $calMonth));
$daysInMonth = (int)date('t', $firstDayTs);
$firstWeekday = (int)date('w', $firstDayTs);

$prevTs   = strtotime('-1 month', $firstDayTs);
$nextTs   = strtotime('+1 month', $firstDayTs);
$prevYear = (int)date('Y', $prevTs);
$prevMonth = (int)date('n', $prevTs);
$nextYear = (int)date('Y', $nextTs);
$nextMonth = (int)date('n', $nextTs);

$todayY = (int)date('Y');
$todayM = (int)date('n');
$todayD = (int)date('j');

$eventDays = array_flip($diasEventos ?? []);
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
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard">

                <!-- ── SIDEBAR IZQUIERDA ── -->
                <aside class="barra-herramientas">
                    <h3>BARRA DE HERRAMIENTAS</h3>
                    <ul class="menu-lateral">
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=dashboard">
                                <img src="<?= BASE_URL ?>/img/hogar.png" alt="icono" class="icono-menu">
                                Mi espacio de trabajo
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=buzon">
                                <img src="<?= BASE_URL ?>/img/bandeja-de-entrada.png" alt="icono" class="icono-menu">
                                Buzón de entrada
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=lecciones">
                                <img src="<?= BASE_URL ?>/img/leccion.png" alt="icono" class="icono-menu">
                                Lecciones
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=tareas">
                                <img src="<?= BASE_URL ?>/img/portapapeles.png" alt="icono" class="icono-menu">
                                Tareas
                            </a>
                        </li>
                    </ul>
                    <a class="cerrar-sesion" href="<?= BASE_URL ?>/index.php?url=logout">
                        <img src="<?= BASE_URL ?>/img/cerrar-sesion.png" alt="cerrar" class="icono-cerrar">
                        Cerrar sesión
                    </a>
                </aside>

                <!-- ── CONTENIDO CENTRAL ── -->
                <section class="contenido-dashboard">

                    <div class="banner-dashboard">
                        <div class="banner-texto">
                            <span class="etiqueta-banner">NOTEBOOKLMN</span>
                            <h1>Todo tu aprendizaje, centralizado con NotebookLMN</h1>
                        </div>
                        <a class="btn-abrir" href="<?= BASE_URL ?>/index.php?url=app">Abrir ahora</a>
                    </div>

                    <!-- Acciones -->
                    <div class="acciones">
                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/crear.png" alt="Nuevo Documento" class="icono-accion">
                            <span>Nuevo<br>Documento</span>
                        </button>
                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/subir.png" alt="Subir Documento" class="icono-accion">
                            <span>Subir<br>Documento</span>
                        </button>
                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/compartir-archivo.png" alt="Compartir" class="icono-accion">
                            <span>Compartir<br>Documento</span>
                        </button>
                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/plantilla.png" alt="Plantilla" class="icono-accion">
                            <span>Usar<br>Plantilla</span>
                        </button>
                    </div>

                    <!-- Carpetas -->
                    <div class="documentos">
                        <h2>Mis Carpetas</h2>
                        <?php $carpetas = is_array($carpetas ?? null) ? $carpetas : []; ?>
                        <?php if (count($carpetas) === 0): ?>
                            <p class="text-muted">Aún no tienes carpetas creadas.</p>
                        <?php else: ?>
                            <div class="documentos-container">
                                <?php foreach ($carpetas as $c): ?>
                                    <div class="documento">
                                        <a href="<?= BASE_URL ?>/index.php?url=carpeta&id=<?= $c['id'] ?>">
                                            <img src="<?= BASE_URL ?>/index.php?url=curso&id=<?= $curso['id'] ?>" class="imagen-carpeta" alt="Carpeta">
                                        </a>
                                        <p><?= htmlspecialchars($c['nombre']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── SEGUIR VIENDO ── -->
                    <div class="seguimiento">
                        <div class="seguimiento-cabecera">
                            <h2>Seguir viendo</h2>
                            <?php $totalCursos = count($cursosEnProgreso ?? []); ?>
                            <?php if ($totalCursos > 3): ?>
                                <div class="sv-flechas">
                                    <button class="sv-arrow" id="svPrev" aria-label="Anterior">&#8592;</button>
                                    <button class="sv-arrow" id="svNext" aria-label="Siguiente">&#8594;</button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($cursosEnProgreso)): ?>
                            <p class="text-muted" style="margin-top:.5rem;">Aún no tienes cursos para continuar.</p>
                        <?php else: ?>
                            <div class="sv-track-wrap">
                                <div class="sv-track" id="svTrack">
                                    <?php foreach ($cursosEnProgreso as $sc):
                                        $progreso  = $sc['progreso'];
                                        $leccionUrl = $sc['ultima_leccion_id']
                                            ? BASE_URL . '/index.php?url=leccion&id=' . $sc['ultima_leccion_id']
                                            : BASE_URL . '/index.php?url=detallecurso&id=' . $sc['id'];

                                        /* Imagen: usa la columna 'imagen' del curso si existe, si no el placeholder */
                                        $imgSrc = !empty($sc['imagen'])
                                            ? BASE_URL . '/' . ltrim($sc['imagen'], '/')
                                            : BASE_URL . '/img/curso1.jpg';
                                    ?>
                                        <a class="sv-card" href="<?= $leccionUrl ?>">
                                            <div class="sv-thumb">
                                                <img src="<?= htmlspecialchars($imgSrc) ?>"
                                                    alt="<?= htmlspecialchars($sc['titulo']) ?>"
                                                    onerror="this.src='<?= BASE_URL ?>/img/curso1.jpg'">
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

                <!-- ── COLUMNA DERECHA ── -->
                <aside class="calendario">
                    <button class="btn-calendario" type="button">Abrir Calendario</button>

                    <div class="tarjeta-calendario">
                        <div class="calendario-header">
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $prevYear ?>&m=<?= $prevMonth ?>">&lt;</a>
                            <div class="selector-mes">
                                <span class="mes"><?= $monthNames[$calMonth] ?></span>
                                <span class="anyo"><?= $calYear ?></span>
                            </div>
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $nextYear ?>&m=<?= $nextMonth ?>">&gt;</a>
                        </div>

                        <div class="calendario-semana">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span>
                            <span>Th</span><span>Fr</span><span>Sa</span>
                        </div>

                        <div class="calendario-grid">
                            <?php
                            for ($i = 0; $i < $firstWeekday; $i++) {
                                echo '<span class="dia apagado"></span>';
                            }
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $isToday  = ($calYear === $todayY && $calMonth === $todayM && $d === $todayD);
                                $hasEvent = isset($eventDays[$d]);
                                $classes  = 'dia' . ($isToday ? ' seleccionado' : '') . ($hasEvent ? ' marcado' : '');
                                echo '<span class="' . $classes . '">' . $d . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="lista-eventos">
                        <?php if (!empty($eventos)): ?>
                            <?php foreach ($eventos as $e): ?>
                                <div class="evento">
                                    <span class="punto punto-azul"></span>
                                    <div class="evento-texto">
                                        <p class="evento-titulo"><?= htmlspecialchars($e['titulo']) ?></p>
                                        <p class="evento-sub">
                                            <?= htmlspecialchars($e['curso']) ?> · <?= htmlspecialchars(date('d/m/Y', strtotime($e['fecha_limite']))) ?>
                                        </p>
                                    </div>
                                    <span class="evento-flecha">&gt;</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="evento">
                                <div class="evento-texto">
                                    <p class="evento-titulo">Sin eventos</p>
                                    <p class="evento-sub">No hay tareas con fecha límite</p>
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
                const style = getComputedStyle(track);
                const gap = parseFloat(style.gap) || 16;
                return card.offsetWidth + gap;
            }

            function totalPages() {
                return Math.ceil(track.children.length / PER_PAGE);
            }

            function goTo(p) {
                page = Math.max(0, Math.min(p, totalPages() - 1));
                const offset = page * PER_PAGE * cardWidth();
                track.style.transform = `translateX(-${offset}px)`;
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