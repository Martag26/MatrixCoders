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
$proximasTareas = array_values(array_filter(
    $tareasUsuario,
    static fn($tarea) => in_array($tarea['estado_visual'] ?? '', ['pendiente', 'proxima'], true)
));
$proximasTareas = array_slice($proximasTareas, 0, 5);
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
    <style>
        .task-dot {
            display: block;
            width: 6px;
            height: 6px;
            margin: 4px auto 0;
            border-radius: 50%;
            background: #f97316;
        }

        .tareas-dashboard-list {
            display: grid;
            gap: 12px;
        }

        .tarea-dashboard-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #fff;
        }

        .tarea-dashboard-item h3 {
            margin: 0 0 4px;
            font-size: 1rem;
        }

        .tarea-dashboard-item p {
            margin: 0;
            color: #64748b;
        }

        .tarea-dashboard-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            text-align: right;
            white-space: nowrap;
        }
    </style>
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
                            <div class="sv-empty">
                                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline stroke-linecap="round" points="14 2 14 8 20 8"/>
                                    <line stroke-linecap="round" x1="9" y1="13" x2="15" y2="13"/>
                                    <line stroke-linecap="round" x1="9" y1="17" x2="12" y2="17"/>
                                </svg>
                                <div>
                                    <p class="sv-empty-title">Aún no tienes documentos</p>
                                    <p class="sv-empty-sub">Crea tu primer documento o sube uno desde la nube.</p>
                                </div>
                                <a class="sv-empty-btn" href="<?= BASE_URL ?>/index.php?url=mis-documentos">Crear documento →</a>
                            </div>
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

                    <div class="documentos">
                        <div class="dashboard-section-head">
                            <h2>Próximas tareas</h2>
                            <a class="section-link" href="<?= BASE_URL ?>/index.php?url=tareas">Ver todas</a>
                        </div>
                        <?php if (count($proximasTareas) === 0): ?>
                            <div class="sv-empty">
                                <div>
                                    <p class="sv-empty-title">No tienes tareas pendientes</p>
                                    <p class="sv-empty-sub">Cuando tengas nuevas entregas, aparecerán aquí para ayudarte a organizarte.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="tareas-dashboard-list">
                                <?php foreach ($proximasTareas as $tarea): ?>
                                    <div class="tarea-dashboard-item">
                                        <div>
                                            <h3><?= htmlspecialchars($tarea['titulo']) ?></h3>
                                            <p><?= htmlspecialchars($tarea['curso']) ?></p>
                                        </div>
                                        <div class="tarea-dashboard-meta">
                                            <span><?= htmlspecialchars(date('d M Y', strtotime($tarea['fecha_limite']))) ?></span>
                                            <?php if (($tarea['estado_visual'] ?? '') === 'proxima'): ?>
                                                <span class="badge text-bg-warning text-danger-emphasis">¡Vence pronto!</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── BUZÓN DE MENSAJES ── -->
                    <?php
                    $mensajesNoLeidos  = $mensajesNoLeidos  ?? 0;
                    $mensajesRecientes = $mensajesRecientes ?? [];
                    ?>
                    <div class="documentos">
                        <div class="dashboard-section-head">
                            <h2 style="display:flex;align-items:center;gap:.5rem">
                                Buzón de mensajes
                                <?php if ($mensajesNoLeidos > 0): ?>
                                <span style="background:#3b82f6;color:#fff;font-size:.65rem;font-weight:800;border-radius:99px;padding:1px 8px"><?= $mensajesNoLeidos ?> nuevo<?= $mensajesNoLeidos !== 1 ? 's' : '' ?></span>
                                <?php endif; ?>
                            </h2>
                            <a class="section-link" href="<?= BASE_URL ?>/index.php?url=buzon">Ver todos</a>
                        </div>
                        <?php if (empty($mensajesRecientes)): ?>
                        <div class="sv-empty">
                            <div>
                                <p class="sv-empty-title">Sin mensajes</p>
                                <p class="sv-empty-sub">Cuando recibas mensajes de administradores o instructores aparecerán aquí.</p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <?php foreach ($mensajesRecientes as $msg):
                                $esLeido = (bool)$msg['leido'];
                                $diffSec = time() - strtotime($msg['enviado_en'] ?? 'now');
                                $timeAgo = $diffSec < 60 ? 'ahora' : ($diffSec < 3600 ? floor($diffSec/60).'m' : ($diffSec < 86400 ? floor($diffSec/3600).'h' : date('d/m', strtotime($msg['enviado_en']))));
                            ?>
                            <a href="<?= BASE_URL ?>/index.php?url=buzon&msg=<?= (int)$msg['id'] ?>"
                               style="display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:12px;border:1.5px solid <?= $esLeido ? '#e5e7eb' : '#bfdbfe' ?>;background:<?= $esLeido ? '#fff' : '#eff6ff' ?>;text-decoration:none;transition:border-color .15s">
                                <div style="width:36px;height:36px;border-radius:9px;background:<?= $esLeido ? '#f3f4f6' : '#dbeafe' ?>;color:<?= $esLeido ? '#6b7280' : '#1d4ed8' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.95rem;flex-shrink:0">
                                    <?= mb_strtoupper(mb_substr($msg['nombre_emisor'] ?? 'X', 0, 1, 'UTF-8'), 'UTF-8') ?>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div style="font-size:.85rem;font-weight:<?= $esLeido ? '600' : '800' ?>;color:#1B2336;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        <?= htmlspecialchars($msg['asunto'] ?: 'Sin asunto') ?>
                                    </div>
                                    <div style="font-size:.75rem;color:#6b7280;margin-top:1px">
                                        <?= htmlspecialchars($msg['nombre_emisor']) ?> · <?= $timeAgo ?>
                                    </div>
                                </div>
                                <?php if (!$esLeido): ?>
                                <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0"></span>
                                <?php endif; ?>
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
                            <div class="sv-empty">
                                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                                    <path stroke-linecap="round" d="M8 21h8M12 17v4"/>
                                    <path stroke-linecap="round" d="M10 10l2-2 4 4"/>
                                </svg>
                                <div>
                                    <p class="sv-empty-title">Aún no tienes cursos en progreso</p>
                                    <p class="sv-empty-sub">Empieza un curso y aquí verás tu progreso.</p>
                                </div>
                                <a class="sv-empty-btn" href="<?= BASE_URL ?>/index.php">Explorar cursos →</a>
                            </div>
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

                <!-- ── COLUMNA DERECHA: calendario + perfil ── -->
                <aside class="calendario">

                    <!-- Mini calendario mensual -->
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
                            <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span>
                            <span>Vi</span><span>Sa</span><span>Do</span>
                        </div>
                        <div class="calendario-grid">
                            <?php
                            $offset = $firstWeekday === 0 ? 6 : $firstWeekday - 1;
                            for ($i = 0; $i < $offset; $i++) echo '<span class="dia apagado"></span>';
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $isToday = ($calYear === $todayY && $calMonth === $todayM && $d === $todayD);
                                $hasTask = in_array($d, $diasConTareas, true);
                                $cls = 'dia' . ($isToday ? ' seleccionado' : '') . ($hasTask ? ' marcado' : '');
                                echo '<span class="' . $cls . '">' . $d . ($hasTask ? '<span class="task-dot"></span>' : '') . '</span>';
                            }
                            ?>
                        </div>
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
