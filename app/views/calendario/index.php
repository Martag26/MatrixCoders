<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Calendario — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
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
            margin: 0;
            color: var(--mc-dark);
        }

        /* Layout: 2 columnas — calendario grande + panel */
        .cal-page {
            max-width: 1300px;
            margin: 0 auto;
            padding: 22px 24px 48px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 22px;
            align-items: start;
        }

        @media(max-width:900px) {
            .cal-page {
                grid-template-columns: 1fr;
            }
        }

        /* Top bar */
        .cal-topbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .cal-topbar h1 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0;
            flex: 1;
        }

        .cal-nav-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid var(--mc-border);
            background: #fff;
            display: grid;
            place-items: center;
            text-decoration: none;
            color: var(--mc-dark);
            font-size: 1rem;
            transition: all .15s;
        }

        .cal-nav-btn:hover {
            background: var(--mc-green);
            color: #fff;
            border-color: var(--mc-green);
        }

        .volver-link {
            font-size: .78rem;
            color: var(--mc-muted);
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--mc-border);
            border-radius: 8px;
            background: #fff;
            white-space: nowrap;
        }

        .volver-link:hover {
            background: var(--mc-soft);
            color: var(--mc-dark);
        }

        /* Calendar card */
        .cal-card {
            background: var(--mc-card);
            border-radius: 16px;
            box-shadow: var(--mc-shadow);
            overflow: hidden;
        }

        .cal-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #f1f5f9;
            border-bottom: 1px solid var(--mc-border);
        }

        .cal-weekdays span {
            text-align: center;
            padding: 12px 0;
            font-size: .73rem;
            font-weight: 800;
            color: var(--mc-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .cal-cell {
            min-height: 110px;
            border-right: 1px solid var(--mc-border);
            border-bottom: 1px solid var(--mc-border);
            padding: 7px 6px;
            vertical-align: top;
        }

        .cal-cell:nth-child(7n) {
            border-right: none;
        }

        .cal-cell:nth-last-child(-n+7) {
            border-bottom: none;
        }

        .cal-day-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: .84rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .cal-cell.today .cal-day-num {
            background: var(--mc-green);
            color: #fff;
            font-weight: 800;
        }

        .cal-cell.inactive {
            background: #fafafa;
        }

        .cal-cell.inactive .cal-day-num {
            color: #d1d5db;
        }

        .cal-cell.has-events {
            cursor: pointer;
        }

        .cal-cell.has-events:hover {
            background: #f0fdf4;
        }

        .cal-event {
            display: block;
            font-size: .68rem;
            font-weight: 600;
            color: #fff;
            border-radius: 4px;
            padding: 2px 6px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: filter .15s;
        }

        .cal-event:hover {
            filter: brightness(.88);
        }

        .cal-more {
            font-size: .65rem;
            color: var(--mc-muted);
            padding-left: 2px;
        }

        /* Leyenda */
        .cal-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .74rem;
            color: var(--mc-dark);
        }

        .legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Right panel */
        .right-panel {
            display: grid;
            gap: 16px;
        }

        .rp-card {
            background: var(--mc-card);
            border-radius: 14px;
            box-shadow: var(--mc-shadow);
            overflow: hidden;
        }

        .rp-head {
            padding: 13px 16px;
            font-size: .74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid var(--mc-border);
            color: var(--mc-dark);
        }

        .ec-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--mc-border);
        }

        .ec-item:last-child {
            border-bottom: none;
        }

        .ec-titulo {
            font-size: .82rem;
            font-weight: 700;
            margin: 0 0 7px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ec-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 99px;
            overflow: hidden;
        }

        .ec-fill {
            height: 100%;
            background: var(--mc-green);
            border-radius: 99px;
        }

        .ec-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
        }

        .ec-pct {
            font-size: .71rem;
            font-weight: 700;
            color: var(--mc-green);
        }

        .ec-lec {
            font-size: .71rem;
            color: var(--mc-muted);
        }

        .ec-link {
            display: block;
            padding: 11px 16px;
            text-align: center;
            font-size: .78rem;
            font-weight: 700;
            color: var(--mc-green);
            text-decoration: none;
            border-top: 1px solid var(--mc-border);
        }

        .ec-link:hover {
            background: #f0fdf4;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: var(--mc-border);
        }

        .stat-box {
            background: #fff;
            padding: 14px;
            text-align: center;
        }

        .stat-num {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-lbl {
            font-size: .68rem;
            color: var(--mc-muted);
            margin-top: 3px;
        }

        .empty-rp {
            padding: 20px 16px;
            text-align: center;
            color: var(--mc-muted);
            font-size: .83rem;
        }

        /* Modal */
        .modal-ov {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 999;
        }

        .modal-ov.open {
            display: block;
        }

        .modal-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 430px;
            max-width: 92vw;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
            z-index: 1000;
            display: none;
        }

        .modal-box.open {
            display: block;
        }

        .modal-head {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--mc-border);
        }

        .modal-head h3 {
            margin: 0;
            font-size: .98rem;
            font-weight: 800;
        }

        .modal-close {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #f1f5f9;
            cursor: pointer;
            font-size: .9rem;
            display: grid;
            place-items: center;
        }

        .modal-body {
            padding: 16px 20px;
            max-height: 55vh;
            overflow-y: auto;
        }

        .modal-tarea {
            border-left: 4px solid;
            border-radius: 0 10px 10px 0;
            background: #f8fafc;
            padding: 10px 14px;
            margin-bottom: 10px;
        }

        .modal-tarea h4 {
            font-size: .87rem;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .modal-tarea p {
            font-size: .8rem;
            color: var(--mc-muted);
            margin: 0;
        }

        .modal-tag {
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    <?php
    $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $dayShort = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    $firstDayTs = strtotime(sprintf('%04d-%02d-01', $calYear, $calMonth));
    $daysInMonth = (int)date('t', $firstDayTs);
    $firstWeekday = (int)date('w', $firstDayTs);
    $prevTs = strtotime('-1 month', $firstDayTs);
    $nextTs = strtotime('+1 month', $firstDayTs);
    $prevYear = (int)date('Y', $prevTs);
    $prevMonth = (int)date('n', $prevTs);
    $nextYear = (int)date('Y', $nextTs);
    $nextMonth = (int)date('n', $nextTs);
    $todayY = (int)date('Y');
    $todayM = (int)date('n');
    $todayD = (int)date('j');

    // Colores por curso (si hay tareasPorDia del módulo de tareas del compañero)
    $tareasPorDia = $tareasPorDia ?? [];
    $palette = ['#6B8F71', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#0ea5e9', '#14b8a6'];
    $cursoColors = [];
    $ci = 0;
    foreach ($tareasPorDia as $dia => $tareas) {
        foreach ($tareas as $t) {
            $key = $t['curso'] ?? 'Curso';
            if (!isset($cursoColors[$key])) $cursoColors[$key] = $palette[$ci++ % count($palette)];
        }
    }
    ?>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="cal-page">

        <!-- ── COLUMNA PRINCIPAL (calendario) ── -->
        <div>
            <div class="cal-topbar">
                <a class="cal-nav-btn" href="<?= BASE_URL ?>/index.php?url=calendario&y=<?= $prevYear ?>&m=<?= $prevMonth ?>">‹</a>
                <a class="cal-nav-btn" href="<?= BASE_URL ?>/index.php?url=calendario&y=<?= $nextYear ?>&m=<?= $nextMonth ?>">›</a>
                <h1><?= $monthNames[$calMonth - 1] ?> <?= $calYear ?></h1>
                <a class="volver-link" href="<?= BASE_URL ?>/index.php?url=dashboard">← Dashboard</a>
            </div>

            <div class="cal-card">
                <div class="cal-weekdays">
                    <?php foreach ($dayShort as $d): ?><span><?= $d ?></span><?php endforeach; ?>
                </div>
                <div class="cal-grid">
                    <?php for ($i = 0; $i < $firstWeekday; $i++): ?>
                        <div class="cal-cell inactive"><span class="cal-day-num"></span></div>
                    <?php endfor; ?>
                    <?php for ($d = 1; $d <= $daysInMonth; $d++):
                        $isToday = ($calYear === $todayY && $calMonth === $todayM && $d === $todayD);
                        $tareasDia = $tareasPorDia[$d] ?? [];
                        $hasEvents = !empty($tareasDia);
                    ?>
                        <div class="cal-cell <?= $isToday ? 'today' : '' ?> <?= $hasEvents ? 'has-events' : '' ?>"
                            <?= $hasEvents ? 'data-dia="' . $d . '"' : '' ?>>
                            <span class="cal-day-num"><?= $d ?></span>
                            <?php foreach (array_slice($tareasDia, 0, 3) as $t): ?>
                                <span class="cal-event"
                                    style="background:<?= $cursoColors[$t['curso'] ?? ''] ?? '#6B8F71' ?>"
                                    data-dia="<?= $d ?>">
                                    <?= htmlspecialchars($t['titulo']) ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($tareasDia) > 3): ?>
                                <span class="cal-more">+<?= count($tareasDia) - 3 ?> más</span>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <?php if (!empty($cursoColors)): ?>
                <div class="cal-legend">
                    <?php foreach ($cursoColors as $nc => $color): ?>
                        <span class="legend-item">
                            <span class="legend-dot" style="background:<?= $color ?>"></span>
                            <?= htmlspecialchars($nc) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── PANEL DERECHO ── -->
        <aside class="right-panel">

            <!-- En curso -->
            <div class="rp-card">
                <div class="rp-head">📊 En curso</div>
                <?php if (empty($cursosEnProgreso)): ?>
                    <div class="empty-rp">
                        No tienes cursos activos.<br>
                        <a href="<?= BASE_URL ?>/index.php" style="color:var(--mc-green);font-weight:700;">Explorar cursos →</a>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($cursosEnProgreso, 0, 5) as $sc):
                        $lecUrl = $sc['ultima_leccion_id']
                            ? BASE_URL . '/index.php?url=leccion&id=' . $sc['ultima_leccion_id']
                            : BASE_URL . '/index.php?url=detallecurso&id=' . $sc['id'];
                    ?>
                        <div class="ec-item">
                            <p class="ec-titulo"><?= htmlspecialchars($sc['titulo']) ?></p>
                            <div class="ec-bar">
                                <div class="ec-fill" style="width:<?= $sc['progreso'] ?>%"></div>
                            </div>
                            <div class="ec-meta">
                                <span class="ec-pct"><?= $sc['progreso'] ?>%</span>
                                <span class="ec-lec"><?= $sc['lecciones_vistas'] ?>/<?= $sc['total_lecciones'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <a class="ec-link" href="<?= BASE_URL ?>/index.php?url=dashboard">Ir a mis cursos →</a>
                <?php endif; ?>
            </div>

            <!-- Resumen / progreso global -->
            <div class="rp-card">
                <div class="rp-head">📈 Mi progreso</div>
                <?php
                $totalC      = count($cursosEnProgreso ?? []);
                $completados = count(array_filter($cursosEnProgreso ?? [], fn($c) => $c['progreso'] >= 100));
                $enProgreso  = count(array_filter($cursosEnProgreso ?? [], fn($c) => $c['progreso'] > 0 && $c['progreso'] < 100));
                ?>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-num" style="color:var(--mc-green)"><?= $totalC ?></div>
                        <div class="stat-lbl">Cursos</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-num" style="color:#3b82f6"><?= $enProgreso ?></div>
                        <div class="stat-lbl">En progreso</div>
                    </div>
                    <div class="stat-box" style="grid-column:1/-1">
                        <div class="stat-num" style="color:#10b981"><?= $completados ?></div>
                        <div class="stat-lbl">Completados</div>
                    </div>
                </div>
            </div>

        </aside>
    </div>

    <!-- Modal detalle día (usado si el módulo de tareas aporta datos) -->
    <div class="modal-ov" id="ov"></div>
    <div class="modal-box" id="modal">
        <div class="modal-head">
            <h3 id="mTitle"></h3>
            <button class="modal-close" id="mClose">✕</button>
        </div>
        <div class="modal-body" id="mBody"></div>
    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tpd = <?= json_encode($tareasPorDia, JSON_HEX_TAG) ?>;
        const cc = <?= json_encode($cursoColors,  JSON_HEX_TAG) ?>;
        const mn = <?= json_encode($monthNames[$calMonth - 1]) ?>;
        const cy = <?= $calYear ?>;
        const ov = document.getElementById('ov');
        const modal = document.getElementById('modal');

        function openDay(dia) {
            const tareas = tpd[dia] || [];
            if (!tareas.length) return;
            document.getElementById('mTitle').textContent = dia + ' de ' + mn + ' ' + cy;
            document.getElementById('mBody').innerHTML = tareas.map(t => {
                const c = cc[t.curso] || '#6B8F71';
                return `<div class="modal-tarea" style="border-color:${c}">
                <div class="modal-tag" style="color:${c}">${t.curso||''}</div>
                <h4>${t.titulo}</h4>
                <p>${t.descripcion||'Sin descripción.'}</p>
            </div>`;
            }).join('');
            ov.classList.add('open');
            modal.classList.add('open');
        }

        document.querySelectorAll('.cal-cell[data-dia], .cal-event[data-dia]').forEach(el =>
            el.addEventListener('click', e => {
                e.stopPropagation();
                openDay(el.dataset.dia);
            })
        );
        [ov, document.getElementById('mClose')].forEach(el =>
            el.addEventListener('click', () => {
                ov.classList.remove('open');
                modal.classList.remove('open');
            })
        );
    </script>
</body>

</html>
