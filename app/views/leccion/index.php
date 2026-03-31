<?php
$tituloLeccion = htmlspecialchars($leccion['titulo'] ?? 'Sin título');
$tituloCurso   = htmlspecialchars($curso['titulo']  ?? 'Curso');
$videoUrl      = $leccion['video_url'] ?? null;
$cursoId       = $curso['id'] ?? 0;
$tareas        = $modeloCurso->getTareasByCurso($cursoId);

function ytId(?string $url): ?string
{
    if (!$url) return null;
    preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return $m[1] ?? null;
}
function fmtDurL(?int $min): string
{
    if (!$min || $min <= 0) return '';
    return sprintf('%dh %02dm', intdiv($min, 60), $min % 60);
}
$ytId = ytId($videoUrl);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloLeccion ?> — <?= $tituloCurso ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-navy: #0f172a;
            --mc-dark: #1B2336;
            --mc-border: #e5e7eb;
            --mc-soft: #f8fafc;
            --mc-text: #374151;
            --mc-muted: #6b7280;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Saira', sans-serif;
            background: #fff;
            color: var(--mc-dark);
        }

        /* ── LAYOUT PRINCIPAL ── */
        .leccion-wrap {
            display: grid;
            grid-template-columns: 1fr 300px;
            min-height: calc(100vh - 62px);
        }

        @media(max-width: 900px) {
            .leccion-wrap {
                grid-template-columns: 1fr;
            }

            .temario-sidebar {
                display: none;
            }
        }

        /* ── COLUMNA IZQUIERDA ── */
        .leccion-main {
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        /* Video */
        .video-container {
            background: #000;
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            max-height: 65vh;
        }

        .video-container iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        .video-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            color: #4b5563;
            background: #111;
        }

        .video-placeholder .play-circle {
            width: 72px;
            height: 72px;
            background: rgba(107, 143, 113, .15);
            border: 2px solid rgba(107, 143, 113, .3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--mc-green);
        }

        .video-placeholder p {
            font-size: .9rem;
            color: #6b7280;
        }

        /* Barra de navegación entre lecciones */
        .leccion-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .85rem 1.5rem;
            background: var(--mc-soft);
            border-bottom: 1px solid var(--mc-border);
            gap: 1rem;
        }

        .leccion-nav .titulo-wrap {
            flex: 1;
            min-width: 0;
            text-align: center;
        }

        .leccion-nav h2 {
            font-size: .97rem;
            font-weight: 700;
            color: var(--mc-dark);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .leccion-nav .curso-sub {
            font-size: .78rem;
            color: var(--mc-muted);
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .9rem;
            border-radius: 8px;
            font-size: .83rem;
            font-weight: 600;
            font-family: 'Saira', sans-serif;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
            border: 1px solid var(--mc-border);
            background: #fff;
            color: var(--mc-text);
            flex-shrink: 0;
        }

        .nav-btn:hover {
            background: var(--mc-green);
            color: #fff;
            border-color: var(--mc-green);
        }

        .nav-btn.disabled {
            opacity: .35;
            pointer-events: none;
        }

        .nav-btn-primary {
            background: var(--mc-green);
            color: #fff;
            border-color: var(--mc-green);
        }

        .nav-btn-primary:hover {
            background: var(--mc-green-d);
            border-color: var(--mc-green-d);
            color: #fff;
        }

        /* ── TABS ── */
        .leccion-tabs-wrap {
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--mc-border);
        }

        .leccion-tabs .nav-link {
            font-family: 'Saira', sans-serif;
            font-weight: 600;
            color: var(--mc-muted);
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: .7rem 1rem;
            font-size: .87rem;
            transition: color .15s;
        }

        .leccion-tabs .nav-link.active {
            color: var(--mc-green);
            border-bottom-color: var(--mc-green);
            background: transparent;
        }

        .tab-body {
            padding: 1.5rem;
            flex: 1;
        }

        /* ── INFO TAB ── */
        .info-titulo {
            font-family: Georgia, serif;
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: .75rem;
            color: var(--mc-dark);
        }

        .info-texto {
            color: var(--mc-text);
            line-height: 1.75;
            font-size: .93rem;
        }

        .info-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--mc-border);
        }

        .info-tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .8rem;
            background: var(--mc-soft);
            border: 1px solid var(--mc-border);
            border-radius: 20px;
            padding: 4px 12px;
            color: var(--mc-text);
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
            line-height: 1.55;
        }

        /* ── NOTAS TAB ── */
        .notas-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .notas-header h6 {
            font-weight: 700;
            font-size: .95rem;
            margin: 0;
        }

        .btn-notebook {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--mc-navy);
            color: #fff;
            font-size: .8rem;
            font-weight: 600;
            font-family: 'Saira', sans-serif;
            padding: .4rem .9rem;
            border-radius: 7px;
            text-decoration: none;
            transition: background .15s;
            border: none;
            cursor: pointer;
        }

        .btn-notebook:hover {
            background: #1e2d4a;
            color: #fff;
        }

        .notas-area {
            width: 100%;
            min-height: 160px;
            border: 1px solid var(--mc-border);
            border-radius: 10px;
            padding: .9rem;
            font-family: 'Saira', sans-serif;
            font-size: .9rem;
            resize: vertical;
            color: var(--mc-dark);
            line-height: 1.6;
            transition: outline .15s;
        }

        .notas-area:focus {
            outline: 2px solid var(--mc-green);
            border-color: transparent;
        }

        .notas-footer {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-top: .75rem;
        }

        .btn-guardar {
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .45rem 1.1rem;
            font-weight: 600;
            font-size: .85rem;
            font-family: 'Saira', sans-serif;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-guardar:hover {
            background: var(--mc-green-d);
        }

        .notas-saved {
            font-size: .82rem;
            color: var(--mc-green);
            display: none;
        }

        .notas-hint {
            margin-top: .85rem;
            padding: .7rem .9rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            font-size: .82rem;
            color: #166534;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            line-height: 1.5;
        }

        /* ── TAREAS TAB ── */
        .tarea-card {
            background: var(--mc-soft);
            border-left: 3px solid var(--mc-green);
            border-radius: 0 9px 9px 0;
            padding: .8rem 1rem;
            margin-bottom: .6rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .tarea-card .tt {
            font-weight: 700;
            font-size: .9rem;
        }

        .tarea-card .td {
            font-size: .82rem;
            color: var(--mc-muted);
            margin-top: 2px;
        }

        .tfecha {
            white-space: nowrap;
            font-size: .78rem;
            font-weight: 700;
            background: #fff;
            border: 1px solid var(--mc-border);
            border-radius: 6px;
            padding: 3px 9px;
            color: var(--mc-green-d);
            flex-shrink: 0;
        }

        .tfecha.vencida {
            color: #dc2626;
            border-color: #fca5a5;
            background: #fff7f7;
        }

        /* ── SIDEBAR TEMARIO ── */
        .temario-sidebar {
            background: #f8fafc;
            border-left: 1px solid var(--mc-border);
            overflow-y: auto;
            max-height: calc(100vh - 62px);
            position: sticky;
            top: 62px;
        }

        .temario-head {
            padding: .9rem 1.1rem;
            background: var(--mc-navy);
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .temario-head small {
            color: #94a3b8;
            font-weight: 400;
            font-size: .75rem;
        }

        .t-unidad-titulo {
            padding: .6rem 1rem;
            font-size: .75rem;
            font-weight: 700;
            color: var(--mc-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            background: #eef2f7;
            border-top: 1px solid var(--mc-border);
        }

        .t-leccion {
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            padding: .6rem 1rem .6rem 1.3rem;
            border-top: 1px solid var(--mc-border);
            text-decoration: none;
            color: var(--mc-text);
            font-size: .83rem;
            transition: background .15s;
            cursor: pointer;
        }

        .t-leccion:hover {
            background: #e4ebe5;
            color: var(--mc-dark);
        }

        .t-leccion.activa {
            background: #d4e6d6;
            color: var(--mc-green-d);
            font-weight: 700;
            border-left: 3px solid var(--mc-green);
            padding-left: calc(1.3rem - 3px);
        }

        .t-leccion .tl-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid var(--mc-border);
            background: #fff;
            flex-shrink: 0;
            margin-top: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .t-leccion.activa .tl-dot {
            background: var(--mc-green);
            border-color: var(--mc-green);
        }

        .tl-dot-inner {
            width: 6px;
            height: 6px;
            background: #fff;
            border-radius: 50%;
        }

        /* Volver al curso */
        .volver-curso {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1.1rem;
            font-size: .82rem;
            color: var(--mc-muted);
            text-decoration: none;
            border-bottom: 1px solid var(--mc-border);
            transition: color .15s, background .15s;
        }

        .volver-curso:hover {
            background: #eef2f7;
            color: var(--mc-dark);
        }
    </style>
</head>

<body>

    <?php require __DIR__ . '/../../layout/header.php'; ?>

    <div class="leccion-wrap">

        <!-- ── COLUMNA PRINCIPAL ── -->
        <div class="leccion-main">

            <!-- VIDEO -->
            <div class="video-container">
                <?php if ($ytId): ?>
                    <iframe
                        src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId) ?>?rel=0&modestbranding=1&color=white"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <div class="video-placeholder">
                        <div class="play-circle">▶</div>
                        <p>Sin vídeo disponible para esta lección</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- BARRA NAVEGACIÓN -->
            <div class="leccion-nav">
                <?php if ($leccionAnterior): ?>
                    <a class="nav-btn"
                        href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $leccionAnterior['id'] ?>">
                        ← Anterior
                    </a>
                <?php else: ?>
                    <span class="nav-btn disabled">← Anterior</span>
                <?php endif; ?>

                <div class="titulo-wrap">
                    <h2><?= $tituloLeccion ?></h2>
                    <div class="curso-sub">📚 <?= $tituloCurso ?></div>
                </div>

                <?php if ($leccionSiguiente): ?>
                    <a class="nav-btn nav-btn-primary"
                        href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $leccionSiguiente['id'] ?>">
                        Siguiente →
                    </a>
                <?php else: ?>
                    <a class="nav-btn nav-btn-primary"
                        href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $cursoId ?>">
                        Finalizar ✓
                    </a>
                <?php endif; ?>
            </div>

            <!-- TABS -->
            <div class="leccion-tabs-wrap">
                <ul class="nav leccion-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">
                            Información
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notas">
                            📝 Notas
                        </button>
                    </li>
                    <?php if (!empty($tareas)): ?>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tareas">
                                Tareas
                                <span class="badge ms-1" style="background:var(--mc-green);font-size:.68rem;"><?= count($tareas) ?></span>
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- TAB CONTENT -->
            <div class="tab-content tab-body">

                <!-- INFORMACIÓN -->
                <div class="tab-pane fade show active" id="tab-info">
                    <p class="info-titulo"><?= $tituloLeccion ?></p>
                    <p class="info-texto">
                        <?= $curso['descripcion']
                            ? nl2br(htmlspecialchars($curso['descripcion']))
                            : 'En esta lección aprenderás los conceptos fundamentales de este tema. Sigue el vídeo y toma notas de los puntos más importantes para consolidar tu aprendizaje.' ?>
                    </p>
                    <p class="info-texto" style="margin-top:.85rem;">
                        Recuerda que puedes pausar el vídeo en cualquier momento, tomar notas en la pestaña correspondiente
                        y retomar la lección cuando quieras. Tu progreso se guarda automáticamente.
                    </p>

                    <div class="info-meta">
                        <span class="info-tag">📚 <?= $tituloCurso ?></span>
                        <?php if (!empty($unidad['titulo'])): ?>
                            <span class="info-tag">📂 <?= htmlspecialchars($unidad['titulo']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($leccion['orden'])): ?>
                            <span class="info-tag">🔢 Lección <?= $leccion['orden'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Certificado -->
                    <div class="certificado-box">
                        <div class="cert-icon">🏆</div>
                        <div>
                            <h6>Certificado de finalización</h6>
                            <p>
                                Al completar todas las lecciones del curso obtendrás un certificado oficial de
                                <strong>MatrixCoders</strong>. Este certificado acredita tus conocimientos y puede
                                añadirse a tu perfil de LinkedIn o portfolio profesional. Válido como formación
                                complementaria ante empleadores del sector tecnológico.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- NOTAS -->
                <div class="tab-pane fade" id="tab-notas">
                    <div class="notas-header">
                        <h6>Mis notas</h6>
                        <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="btn-notebook" target="_blank">
                            📓 Abrir NotebookLM
                        </a>
                    </div>

                    <textarea class="notas-area" id="notasArea"
                        placeholder="Escribe aquí tus apuntes para esta lección... Puedes anotar ideas, dudas, conceptos clave o cualquier cosa que quieras recordar."><?= htmlspecialchars($nota) ?></textarea>

                    <div class="notas-footer">
                        <button class="btn-guardar" onclick="guardarNota()">Guardar notas</button>
                        <span class="notas-saved" id="notasSaved">✔ Guardado</span>
                        <span style="font-size:.78rem;color:var(--mc-muted);margin-left:auto;" id="notasChars">
                            <?= strlen($nota) ?> caracteres
                        </span>
                    </div>

                    <div class="notas-hint">
                        <span>💡</span>
                        <span>
                            Las notas rápidas se guardan aquí lección por lección.
                            Para notas más elaboradas, organización por temas y trabajo en profundidad
                            usa <strong>NotebookLM</strong> desde el botón de arriba.
                        </span>
                    </div>
                </div>

                <!-- TAREAS -->
                <?php if (!empty($tareas)): ?>
                    <div class="tab-pane fade" id="tab-tareas">
                        <p style="font-size:.88rem;color:var(--mc-muted);margin-bottom:1rem;">
                            Completa estas tareas para reforzar lo aprendido en el curso.
                        </p>
                        <?php foreach ($tareas as $t):
                            $vencida = !empty($t['fecha_limite']) && strtotime($t['fecha_limite']) < time();
                        ?>
                            <div class="tarea-card">
                                <div>
                                    <div class="tt"><?= htmlspecialchars($t['titulo'] ?? '') ?></div>
                                    <?php if (!empty($t['descripcion'])): ?>
                                        <div class="td"><?= htmlspecialchars($t['descripcion']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($t['fecha_limite'])): ?>
                                    <span class="tfecha <?= $vencida ? 'vencida' : '' ?>">
                                        <?= $vencida ? '⚠ ' : '📅 ' ?>
                                        <?php
                                        $d = DateTime::createFromFormat('Y-m-d', substr($t['fecha_limite'], 0, 10));
                                        echo $d ? $d->format('d/m/Y') : $t['fecha_limite'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div><!-- /tab-content -->
        </div><!-- /leccion-main -->

        <!-- ── SIDEBAR TEMARIO ── -->
        <aside class="temario-sidebar">
            <div class="temario-head">
                <span>Contenido del curso</span>
                <small>
                    <?php
                    $totalLec = array_sum(array_map(fn($u) => count($u['lecciones'] ?? []), $unidades));
                    echo $totalLec . ' lecciones';
                    ?>
                </small>
            </div>

            <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $cursoId ?>" class="volver-curso">
                ← Volver al curso
            </a>

            <?php foreach ($unidades as $u): ?>
                <div class="t-unidad-titulo">
                    <?= htmlspecialchars($u['titulo'] ?? 'Unidad') ?>
                </div>
                <?php foreach (($u['lecciones'] ?? []) as $lec):
                    $esActiva = $lec['id'] == $leccion['id'];
                ?>
                    <a href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $lec['id'] ?>"
                        class="t-leccion <?= $esActiva ? 'activa' : '' ?>">
                        <div class="tl-dot">
                            <?php if ($esActiva): ?>
                                <div class="tl-dot-inner"></div>
                            <?php endif; ?>
                        </div>
                        <span><?= htmlspecialchars($lec['titulo'] ?? 'Lección') ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </aside>

    </div><!-- /leccion-wrap -->

    <?php require __DIR__ . '/../../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const LECCION_ID = <?= (int)$leccion['id'] ?>;

        // ── Contador de caracteres ──
        const notasArea = document.getElementById('notasArea');
        const notasChars = document.getElementById('notasChars');
        if (notasArea) {
            notasArea.addEventListener('input', () => {
                notasChars.textContent = notasArea.value.length + ' caracteres';
            });
        }

        // ── Guardar nota via AJAX ──
        let saveTimer = null;

        function guardarNota(manual = true) {
            if (!notasArea) return;
            const contenido = notasArea.value;

            fetch(BASE_URL + '/index.php?url=leccion&id=' + LECCION_ID, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'nota=' + encodeURIComponent(contenido)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok && manual) {
                        const ok = document.getElementById('notasSaved');
                        ok.style.display = 'inline';
                        setTimeout(() => ok.style.display = 'none', 2500);
                    }
                });
        }

        // Autoguardado cada 30 segundos si hay cambios
        if (notasArea) {
            notasArea.addEventListener('input', () => {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(() => guardarNota(false), 30000);
            });
        }
    </script>
</body>

</html>