<?php
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

$todayY = (int)date('Y');
$todayM = (int)date('n');
$todayD = (int)date('j');
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

                <!-- ── SIDEBAR ── -->
                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <!-- ── CONTENIDO CENTRAL ── -->
                <section class="contenido-dashboard">

                    <div class="banner-dashboard">
                        <div class="banner-texto">
                            <span class="etiqueta-banner">NOTEBOOKLMN</span>
                            <h1>Todo tu aprendizaje, centralizado con NotebookLMN</h1>
                        </div>
                        <a class="btn-abrir" href="<?= BASE_URL ?>/index.php?url=app">Abrir ahora</a>
                    </div>

                    <!-- Acciones rápidas -->
                    <div class="acciones">
                        <a class="accion-btn" href="<?= BASE_URL ?>/index.php?url=lecciones">
                            <img src="<?= BASE_URL ?>/img/leccion.png" alt="" class="icono-accion">
                            <span>Mis<br>Lecciones</span>
                        </a>
                        <a class="accion-btn" href="<?= BASE_URL ?>/index.php?url=calendario">
                            <img src="<?= BASE_URL ?>/img/portapapeles.png" alt="" class="icono-accion">
                            <span>Mi<br>Calendario</span>
                        </a>
                        <a class="accion-btn" href="<?= BASE_URL ?>/index.php?url=buscar">
                            <img src="<?= BASE_URL ?>/img/lupa.png" alt="" class="icono-accion">
                            <span>Buscar<br>Cursos</span>
                        </a>
                        <a class="accion-btn" href="<?= BASE_URL ?>/index.php?url=buzon">
                            <img src="<?= BASE_URL ?>/img/bandeja-de-entrada.png" alt="" class="icono-accion">
                            <span>Buzón de<br>Entrada</span>
                        </a>
                    </div>

                    <!-- Mis Carpetas -->
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
                                            <img src="<?= BASE_URL ?>/img/carpeta.png" class="imagen-carpeta" alt="Carpeta">
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
                                        $imgSrc = !empty($sc['imagen'])
                                            ? BASE_URL . '/img/cursos/' . $sc['imagen']
                                            : BASE_URL . '/img/aprendiendo.png';
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
                    <a class="btn-calendario" href="<?= BASE_URL ?>/index.php?url=calendario">
                        📅 Abrir Calendario
                    </a>

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
                                $isToday = ($calYear === $todayY && $calMonth === $todayM && $d === $todayD);
                                $classes = 'dia' . ($isToday ? ' seleccionado' : '');
                                echo '<span class="' . $classes . '">' . $d . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Progreso cursos en lugar de eventos -->
                    <?php if (!empty($cursosEnProgreso)): ?>
                        <div class="lista-eventos">
                            <?php foreach (array_slice($cursosEnProgreso, 0, 4) as $sc): ?>
                                <div class="evento">
                                    <span class="punto" style="background:var(--mc-green);width:8px;height:8px;border-radius:50%;flex-shrink:0;"></span>
                                    <div class="evento-texto">
                                        <p class="evento-titulo" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?= htmlspecialchars($sc['titulo']) ?>
                                        </p>
                                        <div style="height:4px;background:#e5e7eb;border-radius:99px;margin-top:4px;overflow:hidden;">
                                            <div style="height:100%;width:<?= $sc['progreso'] ?>%;background:var(--mc-green);border-radius:99px;"></div>
                                        </div>
                                    </div>
                                    <span style="font-size:.7rem;font-weight:700;color:var(--mc-green);flex-shrink:0;"><?= $sc['progreso'] ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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