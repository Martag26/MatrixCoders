<?php
// Helpers
$titulo   = htmlspecialchars($curso['titulo'] ?? 'Sin título');
$desc     = $curso['descripcion'] ?? '';
$precio   = (float)($curso['precio'] ?? 0);
$imagen   = !empty($curso['imagen']) ? BASE_URL . '/img/' . $curso['imagen'] : null;
$duracion = isset($curso['duracion_min']) ? (int)$curso['duracion_min'] : null;
$alumnos  = (int)($curso['total_matriculas'] ?? 0);

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
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <style>
        :root {
            --mc-green: #4a7c59;
            --mc-green-d: #3a6347;
            --mc-dark: #111827;
            --mc-gray: #f3f4f6;
            --mc-border: #e5e7eb;
            --mc-text: #374151;
            --mc-muted: #6b7280;
        }

        body {
            background: #fff;
            color: var(--mc-dark);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        /* ── HERO ── */
        .curso-hero {
            background: linear-gradient(135deg, #0f1a14 0%, #1a2e1f 100%);
            color: #fff;
            padding: 2.5rem 0 2rem;
            border-bottom: 3px solid var(--mc-green);
        }

        .curso-hero h1 {
            font-size: clamp(1.35rem, 3vw, 1.9rem);
            font-weight: 800;
            margin-bottom: .5rem;
        }

        .curso-hero p {
            color: #d1d5db;
            font-size: .95rem;
            max-width: 680px;
            line-height: 1.65;
        }

        .tag {
            display: inline-block;
            font-size: .78rem;
            background: rgba(255, 255, 255, .1);
            border-radius: 20px;
            padding: 3px 11px;
            margin: 2px 4px 2px 0;
        }

        .precio-hero {
            font-size: 1.8rem;
            font-weight: 900;
        }

        .precio-hero.gratis {
            color: #6ee7b7;
        }

        /* ── LAYOUT ── */
        .curso-body {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 2rem;
            padding: 2rem 0 3rem;
            align-items: start;
        }

        @media(max-width:900px) {
            .curso-body {
                grid-template-columns: 1fr;
            }

            .sidebar-sticky {
                position: static !important;
            }
        }

        /* ── ALERTA ÉXITO MATRÍCULA ── */
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

        /* ── TABS ── */
        .curso-tabs .nav-link {
            color: var(--mc-text);
            font-weight: 500;
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: .6rem 1.1rem;
        }

        .curso-tabs .nav-link.active {
            color: var(--mc-green);
            border-bottom-color: var(--mc-green);
            background: transparent;
        }

        .tab-content {
            padding: 1.5rem 0;
        }

        /* ── UNIDADES / LECCIONES ── */
        .unidad-bloque {
            border: 1px solid var(--mc-border);
            border-radius: 10px;
            margin-bottom: .75rem;
            overflow: hidden;
        }

        .unidad-titulo {
            padding: .85rem 1.1rem;
            font-weight: 700;
            font-size: .9rem;
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

        .leccion-row .dur {
            margin-left: auto;
            color: var(--mc-muted);
            font-size: .78rem;
            white-space: nowrap;
        }

        /* ── TAREAS ── */
        .tarea-item {
            background: var(--mc-gray);
            border-radius: 9px;
            padding: .75rem 1rem;
            margin-bottom: .5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .tarea-item .tarea-titulo {
            font-weight: 600;
            font-size: .9rem;
        }

        .tarea-item .tarea-desc {
            font-size: .82rem;
            color: var(--mc-muted);
            margin-top: 2px;
        }

        .tarea-fecha {
            font-size: .78rem;
            font-weight: 700;
            white-space: nowrap;
            background: #fff;
            border: 1px solid var(--mc-border);
            border-radius: 6px;
            padding: 3px 9px;
            color: var(--mc-green-d);
        }

        .tarea-fecha.vencida {
            color: #dc2626;
            border-color: #fca5a5;
            background: #fff7f7;
        }

        /* ── NOTAS ── */
        .notas-textarea {
            width: 100%;
            min-height: 130px;
            border: 1px solid var(--mc-border);
            border-radius: 10px;
            padding: .8rem;
            font-size: .9rem;
            resize: vertical;
            font-family: inherit;
            color: var(--mc-dark);
        }

        .notas-textarea:focus {
            outline: 2px solid var(--mc-green);
            border-color: transparent;
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
        }

        .sidebar-img {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
            display: block;
        }

        .sidebar-body {
            padding: 1.3rem;
        }

        .btn-mc {
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: .7rem 1.4rem;
            font-weight: 700;
            width: 100%;
            font-size: .95rem;
            transition: background .2s;
            cursor: pointer;
            text-align: center;
            display: block;
            text-decoration: none;
        }

        .btn-mc:hover {
            background: var(--mc-green-d);
            color: #fff;
        }

        .btn-mc:disabled,
        .btn-mc.disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .btn-mc-outline {
            background: transparent;
            border: 2px solid var(--mc-green);
            color: var(--mc-green);
            border-radius: 9px;
            padding: .65rem 1.4rem;
            font-weight: 700;
            width: 100%;
            font-size: .95rem;
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

        .sidebar-meta {
            font-size: .82rem;
            color: var(--mc-muted);
            margin-top: .9rem;
        }

        .sidebar-meta li {
            padding: 3px 0;
        }
    </style>
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <!-- ── HERO ── -->
    <section class="curso-hero">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <h1><?= $titulo ?></h1>
                    <?php if ($desc): ?>
                        <p><?= htmlspecialchars($desc) ?></p>
                    <?php endif; ?>
                    <div class="mt-2">
                        <span class="tag">👥 <?= $alumnos ?> <?= $alumnos === 1 ? 'estudiante' : 'estudiantes' ?></span>
                        <?php if ($duracion): ?><span class="tag">⏱ <?= fmtDur($duracion) ?></span><?php endif; ?>
                        <?php if ($totalLecciones): ?><span class="tag">📖 <?= $totalLecciones ?> lección<?= $totalLecciones !== 1 ? 'es' : '' ?></span><?php endif; ?>
                        <?php if (!empty($tareas)): ?><span class="tag">📋 <?= count($tareas) ?> tarea<?= count($tareas) !== 1 ? 's' : '' ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="precio-hero <?= $precio <= 0 ? 'gratis' : '' ?>">
                        <?= $precio > 0 ? number_format($precio, 2) . '€' : 'Gratis' ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── BODY ── -->
    <div class="container">
        <div class="curso-body">

            <!-- COLUMNA PRINCIPAL -->
            <div>

                <?php if ($mensajeMatricula === 'exito'): ?>
                    <div class="matricula-ok">
                        ✅ ¡Te has matriculado correctamente en <strong><?= $titulo ?></strong>!
                        Ya puedes acceder a todo el contenido del curso.
                    </div>
                <?php endif; ?>

                <!-- TABS -->
                <ul class="nav curso-tabs border-bottom mb-1" id="cursoTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">Información</button>
                    </li>
                    <?php if (!empty($unidades)): ?>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-temario">Temario</button>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($tareas)): ?>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tareas">Tareas</button>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notas">Notas</button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- INFO -->
                    <div class="tab-pane fade show active" id="tab-info">
                        <h5 class="fw-bold mb-2">Sobre este curso</h5>
                        <p style="color:var(--mc-text); line-height:1.7;">
                            <?= $desc ? nl2br(htmlspecialchars($desc)) : 'No hay descripción disponible.' ?>
                        </p>
                        <ul class="list-unstyled mt-3" style="color:var(--mc-text); font-size:.92rem;">
                            <?php if ($duracion): ?><li>⏱ <strong>Duración:</strong> <?= fmtDur($duracion) ?></li><?php endif; ?>
                            <?php if ($totalLecciones): ?><li>📖 <strong>Lecciones:</strong> <?= $totalLecciones ?></li><?php endif; ?>
                            <li>👥 <strong>Estudiantes:</strong> <?= $alumnos ?></li>
                            <li>💰 <strong>Precio:</strong> <?= $precio > 0 ? number_format($precio, 2) . '€' : 'Gratis' ?></li>
                        </ul>
                    </div>

                    <!-- TEMARIO -->
                    <?php if (!empty($unidades)): ?>
                        <div class="tab-pane fade" id="tab-temario">
                            <p class="text-muted mb-3" style="font-size:.88rem;">
                                <?= count($unidades) ?> unidad<?= count($unidades) !== 1 ? 'es' : '' ?> · <?= $totalLecciones ?> lección<?= $totalLecciones !== 1 ? 'es' : '' ?>
                            </p>
                            <?php foreach ($unidades as $i => $u): ?>
                                <div class="unidad-bloque">
                                    <div class="unidad-titulo <?= $i === 0 ? 'abierta' : '' ?>" onclick="toggleUnidad(this)">
                                        <span><?= htmlspecialchars($u['titulo'] ?? 'Unidad ' . ($i + 1)) ?></span>
                                        <span>
                                            <small style="color:var(--mc-muted);font-weight:400;font-size:.78rem;margin-right:.5rem;">
                                                <?= count($u['lecciones'] ?? []) ?> lección<?= count($u['lecciones'] ?? []) !== 1 ? 'es' : '' ?>
                                            </small>
                                            <span class="chevron">▼</span>
                                        </span>
                                    </div>
                                    <div class="unidad-lecciones <?= $i === 0 ? 'visible' : '' ?>">
                                        <?php foreach (($u['lecciones'] ?? []) as $lec): ?>
                                            <div class="leccion-row">
                                                <span>📄 <?= htmlspecialchars($lec['titulo'] ?? 'Lección') ?></span>
                                                <?php if (!empty($lec['duracion_min'])): ?>
                                                    <span class="dur"><?= fmtDur((int)$lec['duracion_min']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- TAREAS -->
                    <?php if (!empty($tareas)): ?>
                        <div class="tab-pane fade" id="tab-tareas">
                            <?php foreach ($tareas as $t):
                                $vencida = !empty($t['fecha_limite']) && strtotime($t['fecha_limite']) < time();
                            ?>
                                <div class="tarea-item">
                                    <div>
                                        <div class="tarea-titulo"><?= htmlspecialchars($t['titulo'] ?? '') ?></div>
                                        <?php if (!empty($t['descripcion'])): ?>
                                            <div class="tarea-desc"><?= htmlspecialchars($t['descripcion']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($t['fecha_limite'])): ?>
                                        <span class="tarea-fecha <?= $vencida ? 'vencida' : '' ?>">
                                            <?= $vencida ? '⚠ ' : '📅 ' ?><?= fmtFecha($t['fecha_limite']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- NOTAS -->
                    <div class="tab-pane fade" id="tab-notas">
                        <h6 class="fw-semibold mb-2">Tus apuntes</h6>
                        <textarea class="notas-textarea"
                            id="notasCurso"
                            data-curso="<?= $curso['id'] ?>"
                            placeholder="Escribe aquí tus notas mientras aprendes..."></textarea>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <button class="btn btn-sm" style="background:var(--mc-green);color:#fff;border-radius:7px;" onclick="guardarNotas()">
                                Guardar notas
                            </button>
                            <span id="notas-ok" style="display:none;color:var(--mc-green);font-size:.85rem;">✔ Guardado</span>
                        </div>
                    </div>

                </div><!-- /tab-content -->
            </div><!-- /columna principal -->

            <!-- SIDEBAR -->
            <aside class="sidebar-sticky">
                <div class="sidebar-card">
                    <?php if ($imagen): ?>
                        <img src="<?= htmlspecialchars($imagen) ?>" class="sidebar-img" alt="<?= $titulo ?>">
                    <?php endif; ?>

                    <div class="sidebar-body">
                        <div class="fw-black mb-3" style="font-size:1.5rem;">
                            <?= $precio > 0 ? number_format($precio, 2) . '€' : '<span style="color:var(--mc-green)">Gratis</span>' ?>
                        </div>

                        <?php if ($estaMatriculado): ?>
                            <!-- Ya matriculado: botón "Ir al curso" -->
                            <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="btn-mc">
                                ▶ Ir al curso
                            </a>

                        <?php elseif (!$planPermiteAcceso): ?>
                            <!-- Plan insuficiente -->
                            <button class="btn-mc disabled" disabled>Matricularme</button>
                            <div class="plan-aviso">
                                ⚠ Tu plan actual no incluye este curso.
                                <a href="<?= BASE_URL ?>/index.php?url=suscripciones" style="color:var(--mc-green-d);font-weight:700;">
                                    Ver planes →
                                </a>
                            </div>

                        <?php else: ?>
                            <!-- Puede matricularse -->
                            <form method="POST" action="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>">
                                <input type="hidden" name="accion" value="matricular">
                                <?php if (!$usuarioId): ?>
                                    <!-- No logueado: redirige al login -->
                                    <a href="<?= BASE_URL ?>/index.php?url=login" class="btn-mc">
                                        Matricularme
                                    </a>
                                    <p class="text-muted mt-2" style="font-size:.82rem; text-align:center;">
                                        Inicia sesión para matricularte
                                    </p>
                                <?php else: ?>
                                    <button type="submit" class="btn-mc">Matricularme</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>

                        <!-- Carrito solo si no está matriculado -->
                        <?php if (!$estaMatriculado && $precio > 0): ?>
                            <button class="btn-mc-outline"
                                onclick="abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precio ?>)">
                                🛒 Añadir al carrito
                            </button>
                        <?php endif; ?>

                        <ul class="sidebar-meta list-unstyled">
                            <?php if ($duracion): ?><li>⏱ <?= fmtDur($duracion) ?> de contenido</li><?php endif; ?>
                            <?php if ($totalLecciones): ?><li>📖 <?= $totalLecciones ?> lecciones</li><?php endif; ?>
                            <?php if (!empty($tareas)): ?><li>📋 <?= count($tareas) ?> tareas</li><?php endif; ?>
                            <li>📱 Acceso en todos los dispositivos</li>
                            <li>🏆 Certificado de finalización</li>
                        </ul>
                    </div>
                </div>
            </aside>

        </div><!-- /curso-body -->
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
                    <p id="modal-texto" class="mb-1" style="font-size:1rem;"></p>
                    <p id="modal-precio" class="fw-bold" style="font-size:1.2rem;color:#111827;"></p>
                    <p class="text-muted" style="font-size:.9rem;">¿Quieres añadir este curso a tu carrito?</p>
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
        const BASE_URL = '<?= BASE_URL ?>';

        // Acordeón unidades
        function toggleUnidad(el) {
            const lista = el.nextElementSibling;
            el.classList.toggle('abierta');
            lista.classList.toggle('visible', el.classList.contains('abierta'));
        }

        // Modal carrito
        let cursoSeleccionado = null;

        function abrirModal(id, titulo, precio) {
            cursoSeleccionado = id;
            document.getElementById('modal-texto').textContent = titulo;
            document.getElementById('modal-precio').textContent =
                precio > 0 ? precio.toFixed(2) + '€' : 'Gratis';
            new bootstrap.Modal(document.getElementById('modalCarrito')).show();
        }
        document.getElementById('btn-confirmar-carrito').addEventListener('click', function() {
            if (!cursoSeleccionado) return;
            const fd = new FormData();
            fd.append('curso_id', cursoSeleccionado);
            fetch(BASE_URL + '/index.php?url=carrito-añadir', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        let badge = document.querySelector('.carrito-badge');
                        if (data.total > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.className = 'carrito-badge';
                                document.querySelector('a[aria-label="carrito"]').appendChild(badge);
                            }
                            badge.textContent = data.total;
                        } else {
                            if (badge) badge.remove();
                        }
                        bootstrap.Modal.getInstance(document.getElementById('modalCarrito')).hide();
                    }
                });
        });

        // Notas locales
        const notasEl = document.getElementById('notasCurso');
        if (notasEl) {
            notasEl.value = localStorage.getItem('notas_curso_' + notasEl.dataset.curso) || '';
        }

        function guardarNotas() {
            localStorage.setItem('notas_curso_' + notasEl.dataset.curso, notasEl.value);
            const ok = document.getElementById('notas-ok');
            ok.style.display = 'inline';
            setTimeout(() => ok.style.display = 'none', 2000);
        }
    </script>
</body>

</html>