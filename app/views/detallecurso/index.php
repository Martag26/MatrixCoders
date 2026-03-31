<?php
$titulo   = htmlspecialchars($curso['titulo'] ?? 'Sin título');
$desc     = $curso['descripcion'] ?? '';
$precio   = (float)($curso['precio'] ?? 0);
$imagen   = !empty($curso['imagen']) ? BASE_URL . '/img/' . $curso['imagen'] : null;
$duracion = isset($curso['duracion_min']) ? (int)$curso['duracion_min'] : null;
$alumnos  = (int)($curso['total_matriculas'] ?? 0);

function fmtDur(?int $min): string {
    if (!$min || $min <= 0) return '';
    return sprintf('%dh %02dm', intdiv($min, 60), $min % 60);
}
function fmtFecha(?string $fecha): string {
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
            --mc-green:   #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-dark:    #1B2336;
            --mc-navy:    #0f172a;
            --mc-gray:    #f3f4f6;
            --mc-border:  #e5e7eb;
            --mc-text:    #374151;
            --mc-muted:   #6b7280;
            --mc-soft:    #F8F8F8;
        }
        * { box-sizing: border-box; }
        body { background: #fff; color: var(--mc-dark); font-family: 'Saira', sans-serif; margin: 0; }

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
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%236B8F71' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .curso-hero h1 {
            font-family: Georgia, serif;
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 700; letter-spacing: -.3px; margin-bottom: .5rem;
        }
        .curso-hero .desc-hero { color: #c9d5cb; font-size: .95rem; max-width: 680px; line-height: 1.65; }
        .hero-tag {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .78rem; background: rgba(107,143,113,.2);
            border: 1px solid rgba(107,143,113,.3);
            border-radius: 20px; padding: 3px 12px; margin: 3px 4px 3px 0; color: #b8d4bc;
        }
        .precio-grande { font-size: 2rem; font-weight: 900; color: #fff; }
        .precio-grande.gratis { color: #6ee7b7; }

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
        @media(max-width: 900px) {
            .curso-body { grid-template-columns: 1fr; }
            .sidebar-sticky { position: static !important; }
        }

        /* ── MATRICULA OK ── */
        .matricula-ok {
            background: #d1fae5; border: 1px solid #6ee7b7;
            border-radius: 10px; padding: .85rem 1.2rem;
            color: #065f46; font-weight: 600; margin-bottom: 1.2rem;
            display: flex; align-items: center; gap: .6rem;
        }

        /* ── TABS ── */
        .curso-tabs .nav-link {
            font-family: 'Saira', sans-serif; font-weight: 600;
            color: var(--mc-muted); border: none;
            border-bottom: 2px solid transparent; border-radius: 0;
            padding: .65rem 1.1rem; font-size: .9rem; transition: color .15s;
        }
        .curso-tabs .nav-link.active {
            color: var(--mc-green); border-bottom-color: var(--mc-green); background: transparent;
        }
        .tab-content { padding: 1.5rem 0; }

        /* ── INFORMACIÓN ── */
        .info-seccion-titulo {
            font-family: Georgia, serif; font-weight: 700;
            font-size: 1.1rem; margin-bottom: .75rem; color: var(--mc-dark);
        }
        .info-texto { color: var(--mc-text); line-height: 1.75; font-size: .94rem; }
        .que-aprenderas {
            background: var(--mc-soft); border-radius: 12px;
            padding: 1.2rem 1.4rem; margin-top: 1.5rem;
            border: 1px solid var(--mc-border);
        }
        .que-aprenderas h6 { font-weight: 700; margin-bottom: .75rem; color: var(--mc-dark); }
        .que-aprenderas ul {
            list-style: none; padding: 0; margin: 0;
            display: grid; grid-template-columns: 1fr 1fr; gap: .4rem .75rem;
        }
        @media(max-width: 600px) { .que-aprenderas ul { grid-template-columns: 1fr; } }
        .que-aprenderas li {
            font-size: .88rem; color: var(--mc-text); line-height: 1.5;
            display: flex; align-items: flex-start; gap: .4rem;
        }
        .que-aprenderas li::before { content: '✔'; color: var(--mc-green); flex-shrink: 0; font-size: .85rem; margin-top: 1px; }
        .info-datos {
            list-style: none; padding: 0; margin: 1.25rem 0 0;
            border-top: 1px solid var(--mc-border); padding-top: 1.25rem;
            font-size: .9rem; color: var(--mc-text);
        }
        .info-datos li { padding: 4px 0; }
        .certificado-box {
            margin-top: 1.5rem;
            background: linear-gradient(135deg, #d1fae5, #ecfdf5);
            border: 1px solid #6ee7b7; border-radius: 12px;
            padding: 1.1rem 1.3rem;
            display: flex; align-items: flex-start; gap: 1rem;
        }
        .certificado-box .cert-icon { font-size: 2rem; flex-shrink: 0; }
        .certificado-box h6 { font-weight: 700; color: #065f46; margin-bottom: .25rem; }
        .certificado-box p  { font-size: .84rem; color: #047857; margin: 0; line-height: 1.6; }

        /* ── TEMARIO ── */
        .unidad-bloque { border: 1px solid var(--mc-border); border-radius: 10px; margin-bottom: .6rem; overflow: hidden; }
        .unidad-titulo {
            padding: .85rem 1.1rem; font-weight: 700; font-size: .88rem;
            cursor: pointer; display: flex; justify-content: space-between; align-items: center;
            background: var(--mc-gray); user-select: none; transition: background .15s;
        }
        .unidad-titulo:hover { background: #e5e7eb; }
        .unidad-titulo .chevron { transition: transform .25s; color: var(--mc-muted); font-size: .75rem; }
        .unidad-titulo.abierta .chevron { transform: rotate(180deg); }
        .unidad-lecciones { display: none; }
        .unidad-lecciones.visible { display: block; }
        .leccion-row {
            padding: .6rem 1.1rem .6rem 1.8rem;
            font-size: .86rem; display: flex; align-items: center; gap: .6rem;
            border-top: 1px solid var(--mc-border); color: var(--mc-text);
        }
        .leccion-row a {
            text-decoration: none; color: inherit;
            display: flex; align-items: center; gap: .6rem; width: 100%;
            transition: color .15s;
        }
        .leccion-row a:hover { color: var(--mc-green); }
        .leccion-row .licon { color: var(--mc-green); font-size: .85rem; }
        .leccion-row .licon-lock { color: #9ca3af; }

        /* ── TAREAS ── */
        .tarea-item {
            background: var(--mc-soft); border-left: 3px solid var(--mc-green);
            border-radius: 0 9px 9px 0; padding: .8rem 1rem; margin-bottom: .5rem;
            display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;
        }
        .tarea-titulo { font-weight: 700; font-size: .9rem; }
        .tarea-desc   { font-size: .82rem; color: var(--mc-muted); margin-top: 2px; }
        .tarea-fecha {
            font-size: .78rem; font-weight: 700; white-space: nowrap;
            background: #fff; border: 1px solid var(--mc-border);
            border-radius: 6px; padding: 3px 9px; color: var(--mc-green-d); flex-shrink: 0;
        }
        .tarea-fecha.vencida { color: #dc2626; border-color: #fca5a5; background: #fff7f7; }

        /* ── SIDEBAR ── */
        .sidebar-sticky { position: sticky; top: 1.5rem; }
        .sidebar-card {
            border: 1px solid var(--mc-border); border-radius: 14px;
            overflow: hidden; box-shadow: 0 8px 28px rgba(0,0,0,.07);
        }
        .sidebar-body { padding: 1.3rem; }
        .sidebar-price { font-size: 1.6rem; font-weight: 900; color: var(--mc-dark); margin-bottom: 1rem; }
        .sidebar-price.gratis { color: var(--mc-green); }
        .btn-mc {
            background: var(--mc-green); color: #fff; border: none;
            border-radius: 9px; padding: .72rem 1.4rem; font-weight: 700;
            width: 100%; font-size: .95rem; font-family: 'Saira', sans-serif;
            transition: background .2s, transform .1s; cursor: pointer;
            text-align: center; display: block; text-decoration: none;
        }
        .btn-mc:hover { background: var(--mc-green-d); color: #fff; transform: translateY(-1px); }
        .btn-mc:disabled { background: #9ca3af; cursor: not-allowed; transform: none; }
        .btn-mc-outline {
            background: transparent; border: 2px solid var(--mc-green); color: var(--mc-green);
            border-radius: 9px; padding: .65rem 1.4rem; font-weight: 700; width: 100%;
            font-size: .92rem; font-family: 'Saira', sans-serif; transition: all .2s;
            cursor: pointer; text-align: center; display: block; text-decoration: none; margin-top: .6rem;
        }
        .btn-mc-outline:hover { background: var(--mc-green); color: #fff; }
        .plan-aviso {
            background: #fef3c7; border: 1px solid #fcd34d;
            border-radius: 8px; padding: .65rem .9rem;
            font-size: .82rem; color: #92400e; margin-top: .75rem;
        }
        .plan-badge {
            display: inline-block; background: #d1fae5; color: #065f46;
            font-size: .78rem; font-weight: 700; border-radius: 20px;
            padding: 2px 10px; margin-bottom: .75rem;
        }
        .sidebar-meta { list-style: none; padding: 0; margin: 1rem 0 0; font-size: .82rem; color: var(--mc-muted); }
        .sidebar-meta li { padding: 3px 0; }
    </style>
</head>
<body>

<?php require __DIR__ . '/../layout/header.php'; ?>

<!-- HERO -->
<section class="curso-hero">
    <div class="container">
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
<div class="curso-body">

    <!-- COLUMNA PRINCIPAL -->
    <div>
        <?php if ($mensajeMatricula === 'exito'): ?>
            <div class="matricula-ok">
                ✅ ¡Te has matriculado en <strong><?= $titulo ?></strong>!
                <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>"
                   style="color:var(--mc-green-d);margin-left:.5rem;font-weight:700;">Ir al curso →</a>
            </div>
        <?php endif; ?>

        <!-- TABS -->
        <ul class="nav curso-tabs border-bottom mb-1">
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
            <?php if (!empty($tareas)): ?>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tareas">Tareas</button>
            </li>
            <?php endif; ?>
        </ul>

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
                    <?= count($unidades) ?> unidad<?= count($unidades)!==1?'es':'' ?> · <?= $totalLecciones ?> lección<?= $totalLecciones!==1?'es':'' ?>
                </p>
                <?php foreach ($unidades as $i => $u): ?>
                    <div class="unidad-bloque">
                        <div class="unidad-titulo <?= $i===0?'abierta':'' ?>" onclick="toggleUnidad(this)">
                            <div>
                                <div><?= htmlspecialchars($u['titulo'] ?? 'Unidad '.($i+1)) ?></div>
                                <small style="color:var(--mc-muted);font-weight:400;font-size:.78rem;">
                                    <?= count($u['lecciones']??[]) ?> lección<?= count($u['lecciones']??[])!==1?'es':'' ?>
                                </small>
                            </div>
                            <span class="chevron">▼</span>
                        </div>
                        <div class="unidad-lecciones <?= $i===0?'visible':'' ?>">
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

        </div><!-- /tab-content -->
    </div><!-- /columna principal -->

    <!-- SIDEBAR -->
    <aside class="sidebar-sticky">
        <div class="sidebar-card">
            <div class="sidebar-body">
                <div class="sidebar-price <?= $precio <= 0 ? 'gratis' : '' ?>">
                    <?= $precio > 0 ? number_format($precio,2).'€' : 'Gratis' ?>
                </div>

                <?php if ($mensajeMatricula === 'exito' || $estaMatriculado): ?>
                    <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>" class="btn-mc">
                        ▶ Ir al curso
                    </a>

                <?php elseif ($precio <= 0): ?>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>">
                        <input type="hidden" name="accion" value="matricular">
                        <?php if (!$usuarioId): ?>
                            <a href="<?= BASE_URL ?>/index.php?url=login" class="btn-mc">Matricularme gratis</a>
                        <?php else: ?>
                            <button type="submit" class="btn-mc">Matricularme gratis</button>
                        <?php endif; ?>
                    </form>

                <?php elseif ($planPermiteAcceso): ?>
                    <div class="plan-badge">✔ Incluido en tu plan</div>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>">
                        <input type="hidden" name="accion" value="matricular">
                        <button type="submit" class="btn-mc">Matricularme</button>
                    </form>

                <?php else: ?>
                    <?php if (!$usuarioId): ?>
                        <a href="<?= BASE_URL ?>/index.php?url=login" class="btn-mc">Iniciar sesión</a>
                    <?php else: ?>
                        <button class="btn-mc"
                            onclick="abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precio ?>)">
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

</div><!-- /curso-body -->

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

function toggleUnidad(el) {
    const lista = el.nextElementSibling;
    el.classList.toggle('abierta');
    lista.classList.toggle('visible', el.classList.contains('abierta'));
}

let cursoSeleccionado = null;
function abrirModal(id, titulo, precio) {
    cursoSeleccionado = id;
    document.getElementById('modal-texto').textContent = titulo;
    document.getElementById('modal-precio').textContent =
        precio > 0 ? precio.toFixed(2) + '€' : 'Gratis';
    new bootstrap.Modal(document.getElementById('modalCarrito')).show();
}
document.getElementById('btn-confirmar-carrito').addEventListener('click', function () {
    if (!cursoSeleccionado) return;
    const fd = new FormData();
    fd.append('curso_id', cursoSeleccionado);
    fetch(BASE_URL + '/index.php?url=carrito-añadir', { method: 'POST', body: fd })
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
</script>
</body>
</html>