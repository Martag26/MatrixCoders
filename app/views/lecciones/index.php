<!DOCTYPE html>
<html lang="es">

<?php require_once __DIR__ . '/../../helpers/curso_imagen.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Mis Lecciones — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-navy: #0f172a;
            --mc-dark: #1B2336;
            --mc-border: #e5e7eb;
            --mc-soft: #f8fafc;
            --mc-muted: #6b7280;
            --mc-bg: #f6f6f6;
            --mc-card: #fff;
            --mc-shadow: 0 4px 18px rgba(0, 0, 0, .07);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Saira', sans-serif;
            background: var(--mc-bg);
            color: var(--mc-dark);
            margin: 0;
        }

        .page-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 22px;
            max-width: 1200px;
            margin: 22px auto 40px;
            padding: 0 18px;
            align-items: start;
        }

        .lecciones-main {
            min-width: 0;
        }

        /* Filter bar */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-bar input {
            flex: 1;
            min-width: 200px;
            padding: 9px 14px;
            border: 1px solid var(--mc-border);
            border-radius: 10px;
            font-family: 'Saira', sans-serif;
            font-size: .87rem;
            outline: none;
            background: var(--mc-card);
        }

        .filter-bar input:focus {
            border-color: var(--mc-green);
        }

        .filter-btn {
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid var(--mc-border);
            background: var(--mc-card);
            font-family: 'Saira', sans-serif;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            color: var(--mc-dark);
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--mc-green);
            color: #fff;
            border-color: var(--mc-green);
        }

        /* Curso block */
        .curso-block {
            background: var(--mc-card);
            border-radius: 14px;
            box-shadow: var(--mc-shadow);
            margin-bottom: 16px;
            overflow: hidden;
        }

        .curso-block-head {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 16px;
            align-items: center;
            padding: 16px 20px;
            cursor: pointer;
            transition: background .15s;
        }

        .curso-block-head:hover {
            background: #f8fafc;
        }

        .curso-thumb {
            width: 72px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            background: #e5e7eb;
            flex-shrink: 0;
        }

        .curso-info {
            min-width: 0;
        }

        .curso-titulo {
            font-size: .95rem;
            font-weight: 800;
            margin: 0 0 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .curso-progress-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .curso-prog-bar {
            flex: 1;
            height: 6px;
            background: #e5e7eb;
            border-radius: 99px;
            overflow: hidden;
        }

        .curso-prog-fill {
            height: 100%;
            background: var(--mc-green);
            border-radius: 99px;
        }

        .curso-prog-label {
            font-size: .73rem;
            color: var(--mc-muted);
            white-space: nowrap;
        }

        .curso-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-continuar {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 8px;
            background: var(--mc-green);
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            font-family: 'Saira', sans-serif;
            text-decoration: none;
            border: none;
            transition: background .15s;
            white-space: nowrap;
        }

        .btn-continuar:hover {
            background: var(--mc-green-d);
            color: #fff;
        }

        .curso-chevron {
            font-size: .8rem;
            color: var(--mc-muted);
            transition: transform .2s;
        }

        .curso-block.open .curso-chevron {
            transform: rotate(180deg);
        }

        /* Unidades y lecciones */
        .curso-body {
            display: none;
            border-top: 1px solid var(--mc-border);
        }

        .curso-block.open .curso-body {
            display: block;
        }

        .unidad-block {
            border-bottom: 1px solid var(--mc-border);
        }

        .unidad-block:last-child {
            border-bottom: none;
        }

        .unidad-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px 10px 24px;
            background: #f8fafc;
            cursor: pointer;
            gap: 10px;
        }

        .unidad-titulo {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--mc-muted);
            flex: 1;
        }

        .unidad-progress {
            font-size: .7rem;
            color: var(--mc-muted);
        }

        .unidad-chevron {
            font-size: .65rem;
            color: var(--mc-muted);
            transition: transform .2s;
        }

        .unidad-block.open .unidad-chevron {
            transform: rotate(180deg);
        }

        .lecciones-list {
            display: none;
        }

        .unidad-block.open .lecciones-list {
            display: block;
        }

        .leccion-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 20px 9px 36px;
            border-top: 1px solid var(--mc-border);
            text-decoration: none;
            color: var(--mc-dark);
            font-size: .84rem;
            transition: background .15s;
        }

        .leccion-item:hover {
            background: #f0fdf4;
        }

        .leccion-item.vista {
            color: var(--mc-muted);
        }

        .lec-check {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid var(--mc-border);
            background: #fff;
            display: grid;
            place-items: center;
            font-size: .7rem;
            flex-shrink: 0;
            transition: all .15s;
        }

        .leccion-item.vista .lec-check {
            background: #d1fae5;
            border-color: #6ee7b7;
            color: var(--mc-green-d);
        }

        .lec-titulo {
            flex: 1;
        }

        .leccion-item.vista .lec-titulo {
            text-decoration: line-through;
            opacity: .55;
        }

        .lec-play {
            font-size: .72rem;
            color: var(--mc-green);
            font-weight: 700;
            opacity: 0;
            transition: opacity .15s;
        }

        .leccion-item:hover .lec-play {
            opacity: 1;
        }

        .empty-courses {
            text-align: center;
            padding: 60px 20px;
            color: var(--mc-muted);
        }

        .empty-courses .icon {
            font-size: 3rem;
            margin-bottom: 12px;
        }

        .badge-completado {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .68rem;
            font-weight: 700;
            background: #d1fae5;
            color: var(--mc-green-d);
            border-radius: 20px;
            padding: 2px 8px;
        }

        .menu-lateral a.activo {
            background: #f0fdf4;
            color: var(--mc-green);
            font-weight: 700;
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="page-layout">
        <?php require __DIR__ . '/../layout/sidebar.php'; ?>

        <main class="lecciones-main">

            <?php if (empty($misCursos)): ?>
                <div class="empty-courses">
                    <div class="icon">📚</div>
                    <p>Aún no estás matriculado en ningún curso.</p>
                    <a href="<?= BASE_URL ?>/index.php" style="color:var(--mc-green);font-weight:700;">Explorar cursos →</a>
                </div>
            <?php else: ?>

                <!-- Filter bar -->
                <div class="filter-bar">
                    <input type="text" id="searchInput" placeholder="🔍 Buscar lección o curso...">
                    <button class="filter-btn active" data-filter="todos">Todos</button>
                    <button class="filter-btn" data-filter="en-progreso">En progreso</button>
                    <button class="filter-btn" data-filter="completados">Completados</button>
                </div>

                <?php foreach ($misCursos as $idx => $curso):
                    $completado = $curso['progreso'] >= 100;
                    $imgSrc = matrixcoders_curso_image($curso['imagen'] ?? '', $curso['titulo'] ?? '');
                    $leccionUrl = $curso['ultima_leccion_id']
                        ? BASE_URL . '/index.php?url=leccion&id=' . $curso['ultima_leccion_id']
                        : BASE_URL . '/index.php?url=detallecurso&id=' . $curso['id'];
                ?>
                    <div class="curso-block <?= $idx === 0 ? 'open' : '' ?>"
                        data-progreso="<?= $curso['progreso'] ?>"
                        data-titulo="<?= strtolower(htmlspecialchars($curso['titulo'])) ?>">

                        <div class="curso-block-head"
                            onclick="this.closest('.curso-block').classList.toggle('open')">

                            <img class="curso-thumb"
                                src="<?= htmlspecialchars($imgSrc) ?>"
                                alt=""
                                onerror="this.src='<?= BASE_URL ?>/img/aprendiendo.png'">

                            <div class="curso-info">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                                    <p class="curso-titulo"><?= htmlspecialchars($curso['titulo']) ?></p>
                                    <?php if ($completado): ?>
                                        <span class="badge-completado">✓ Completado</span>
                                    <?php endif; ?>
                                </div>
                                <div class="curso-progress-row">
                                    <div class="curso-prog-bar">
                                        <div class="curso-prog-fill" style="width:<?= $curso['progreso'] ?>%"></div>
                                    </div>
                                    <span class="curso-prog-label">
                                        <?= $curso['lecciones_vistas'] ?>/<?= $curso['total_lecciones'] ?> · <?= $curso['progreso'] ?>%
                                    </span>
                                </div>
                            </div>

                            <div class="curso-actions" onclick="event.stopPropagation()">
                                <a class="btn-continuar" href="<?= $leccionUrl ?>">
                                    ▶ <?= $curso['progreso'] > 0 ? 'Continuar' : 'Empezar' ?>
                                </a>
                                <span class="curso-chevron">▼</span>
                            </div>
                        </div>

                        <div class="curso-body">
                            <?php foreach ($curso['unidades'] as $uIdx => $unidad):
                                $totalU  = count($unidad['lecciones'] ?? []);
                                $vistasU = 0;
                                foreach (($unidad['lecciones'] ?? []) as $lec) {
                                    if (isset($curso['vistas_ids'][$lec['id']])) $vistasU++;
                                }
                            ?>
                                <div class="unidad-block <?= $uIdx === 0 ? 'open' : '' ?>">
                                    <div class="unidad-head"
                                        onclick="this.closest('.unidad-block').classList.toggle('open')">
                                        <span class="unidad-titulo">
                                            <?= htmlspecialchars($unidad['titulo'] ?? 'Unidad') ?>
                                        </span>
                                        <span class="unidad-progress"><?= $vistasU ?>/<?= $totalU ?></span>
                                        <span class="unidad-chevron">▼</span>
                                    </div>

                                    <div class="lecciones-list">
                                        <?php foreach (($unidad['lecciones'] ?? []) as $lec):
                                            $esVista = isset($curso['vistas_ids'][$lec['id']]);
                                        ?>
                                            <a class="leccion-item <?= $esVista ? 'vista' : '' ?>"
                                                href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $lec['id'] ?>">
                                                <div class="lec-check"><?= $esVista ? '✓' : '' ?></div>
                                                <span class="lec-titulo"><?= htmlspecialchars($lec['titulo'] ?? 'Lección') ?></span>
                                                <span class="lec-play">▶ Ver</span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </main>
    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search filter
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const q = searchInput.value.toLowerCase();
                document.querySelectorAll('.curso-block').forEach(block => {
                    const titulo = block.dataset.titulo || '';
                    const lecciones = [...block.querySelectorAll('.lec-titulo')]
                        .map(el => el.textContent.toLowerCase());
                    const match = titulo.includes(q) || lecciones.some(l => l.includes(q));
                    block.style.display = match ? '' : 'none';
                });
            });
        }

        // Status filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const f = btn.dataset.filter;
                document.querySelectorAll('.curso-block').forEach(block => {
                    const p = parseInt(block.dataset.progreso);
                    if (f === 'todos') block.style.display = '';
                    else if (f === 'completados') block.style.display = p >= 100 ? '' : 'none';
                    else block.style.display = (p > 0 && p < 100) ? '' : 'none';
                });
            });
        });
    </script>
</body>

</html>
