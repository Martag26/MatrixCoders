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
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
    <!-- Aplicar estado del sidebar ANTES del primer render para evitar flash -->
    <script>
    (function(){
        if(localStorage.getItem('mc_sidebar_col')==='1'){
            document.documentElement.setAttribute('data-sb','collapsed');
        }
    })();
    </script>
    <style>
        /* Flash-prevention: aplica el estado colapsado antes del JS del sidebar */
        html[data-sb="collapsed"] .cal-page{grid-template-columns:1fr 280px!important}
        html[data-sb="collapsed"] .right-panel{opacity:1!important;pointer-events:auto!important;overflow:visible!important}

        :root {
            --mc-green:#6B8F71; --mc-green-d:#4a6b50; --mc-green-lt:#f0f5f1;
            --mc-dark:#1B2336; --mc-border:#e5e7eb; --mc-soft:#f8fafc;
            --mc-muted:#6b7280; --mc-bg:#f6f6f6; --mc-card:#fff;
            --mc-shadow:0 4px 18px rgba(0,0,0,.07);
        }
        *,*::before,*::after{box-sizing:border-box}
        body{font-family:'Saira',sans-serif;background:var(--mc-bg);margin:0;color:var(--mc-dark)}

        /* ── PAGE LAYOUT: 3 columnas con espacio compartido ──
           Sidebar expandido  → columna derecha contraída a 0 (prioriza calendario)
           Sidebar colapsado  → columna derecha a 280px visible
           El calendario central ocupa siempre el espacio restante (1fr)          */
        .cal-page{
            display:grid;
            /* Sidebar expandido por defecto: right-panel oculto */
            grid-template-columns:1fr 0;
            gap:0 20px;
            align-items:start;
            transition:grid-template-columns .2s cubic-bezier(.4,0,.2,1);
        }

        /* Sidebar colapsado → right-panel visible */
        .contenedor-dashboard-content.sidebar--collapsed .cal-page{
            grid-template-columns:1fr 280px;
        }
        .contenedor-dashboard-content.sidebar--collapsed .right-panel{
            opacity:1;
            pointer-events:auto;
            overflow:visible;
        }

        /* Right-panel: invisible cuando el grid-column es 0 */
        .right-panel{
            display:grid;gap:16px;min-width:0;
            opacity:0;pointer-events:none;overflow:hidden;
            transition:opacity .18s ease .05s;
        }

        @media(max-width:768px){
            .cal-page{grid-template-columns:1fr!important}
            .right-panel{display:none!important}
        }

        /* ── TOP BAR ── */
        .cal-topbar{
            display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap;
        }
        .cal-topbar-left{flex:1;min-width:0}
        .cal-topbar-title{
            font-size:1.35rem;font-weight:800;margin:0;color:var(--mc-dark);line-height:1.2;
        }
        .cal-topbar-sub{
            font-size:.77rem;color:var(--mc-muted);margin:3px 0 0;
        }
        .cal-stats-strip{
            display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;
        }
        .cal-stat-pill{
            display:inline-flex;align-items:center;gap:7px;
            background:var(--mc-card);border:1px solid var(--mc-border);
            border-radius:10px;padding:7px 13px;font-size:.78rem;font-weight:700;color:var(--mc-dark);
            box-shadow:0 1px 4px rgba(0,0,0,.05);
        }
        .cal-stat-pill span{color:var(--mc-muted);font-weight:500}
        .cal-stat-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .btn-nuevo-ev{
            display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
            background:var(--mc-green);color:#fff;border:none;border-radius:10px;
            font-family:'Saira',sans-serif;font-size:.83rem;font-weight:700;cursor:pointer;
            transition:background .15s,transform .1s;white-space:nowrap;box-shadow:0 2px 8px rgba(107,143,113,.35);
        }
        .btn-nuevo-ev:hover{background:var(--mc-green-d);transform:translateY(-1px)}

        /* ── CALENDAR WRAPPER ── */
        .cal-wrap{
            background:var(--mc-card);border-radius:16px;
            box-shadow:0 2px 12px rgba(0,0,0,.06),0 8px 30px rgba(0,0,0,.05);
            padding:20px 20px 16px;
        }

        /* FullCalendar overrides */
        .fc .fc-toolbar{margin-bottom:14px!important}
        .fc .fc-toolbar-title{font-family:'Saira',sans-serif;font-size:1.05rem;font-weight:800;color:var(--mc-dark)}
        .fc .fc-button{
            font-family:'Saira',sans-serif;font-size:.78rem;font-weight:600;
            padding:5px 11px!important;border-radius:7px!important;
        }
        .fc .fc-button-primary{background:var(--mc-dark)!important;border-color:var(--mc-dark)!important}
        .fc .fc-button-primary:hover{background:var(--mc-green)!important;border-color:var(--mc-green)!important}
        .fc .fc-button-primary:not(:disabled).fc-button-active{background:var(--mc-green)!important;border-color:var(--mc-green)!important}
        .fc .fc-daygrid-day-number{font-size:.8rem;font-weight:600;color:var(--mc-dark);padding:4px 6px!important}
        .fc .fc-col-header-cell-cushion{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--mc-muted);padding:6px 0!important}
        .fc-day-today{background:#f0fdf4!important}
        .fc-day-today .fc-daygrid-day-number{
            background:var(--mc-green);color:#fff;border-radius:50%;
            width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;
        }
        .fc-event{border-radius:5px!important;font-size:.72rem!important;font-weight:600!important;border:none!important;padding:1px 5px!important}
        .fc-daygrid-day{cursor:pointer}
        .fc-daygrid-day:hover{background:#fafafa}
        .fc .fc-scrollgrid{border-radius:10px;overflow:hidden;border-color:var(--mc-border)!important}
        .fc .fc-scrollgrid td,.fc .fc-scrollgrid th{border-color:var(--mc-border)!important}
        .fc-theme-standard .fc-list-day-cushion{background:var(--mc-soft)!important}

        /* Drag ghost */
        .fc-event-dragging{opacity:.75!important;transform:scale(1.02);box-shadow:0 8px 20px rgba(0,0,0,.18)!important}

        /* ── LEYENDA ── */
        .cal-legend{
            display:flex;flex-wrap:wrap;gap:10px 16px;margin-top:14px;
            padding-top:12px;border-top:1px solid var(--mc-border);
        }
        .legend-item{display:inline-flex;align-items:center;gap:6px;font-size:.73rem;color:var(--mc-muted)}
        .legend-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}

        /* ── RIGHT PANEL ── */
        .right-panel{display:grid;gap:18px}
        .rp-card{background:var(--mc-card);border-radius:14px;box-shadow:var(--mc-shadow);overflow:hidden}
        .rp-head{
            padding:12px 16px;font-size:.72rem;font-weight:800;text-transform:uppercase;
            letter-spacing:.6px;border-bottom:1px solid var(--mc-border);color:var(--mc-muted);
            display:flex;align-items:center;justify-content:space-between;gap:8px;
        }
        .rp-head-btn{
            background:var(--mc-green);color:#fff;border:none;border-radius:6px;
            font-size:.7rem;font-weight:700;padding:4px 10px;cursor:pointer;
            font-family:'Saira',sans-serif;transition:background .15s;
        }
        .rp-head-btn:hover{background:var(--mc-green-d)}

        /* Evento items */
        .ev-item{padding:11px 16px;border-bottom:1px solid var(--mc-border);display:flex;align-items:flex-start;gap:10px}
        .ev-item:last-child{border-bottom:none}
        .ev-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;margin-top:5px}
        .ev-titulo{font-size:.82rem;font-weight:700;margin:0 0 3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .ev-meta{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
        .ev-tag{display:inline-flex;align-items:center;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;padding:1px 7px;border-radius:20px}
        .ev-date{font-size:.7rem;color:var(--mc-muted)}
        .ev-edit-btn{background:transparent;border:none;color:var(--mc-muted);cursor:pointer;padding:0 0 0 4px;font-size:.72rem;line-height:1;transition:color .15s}
        .ev-edit-btn:hover{color:var(--mc-green)}
        .empty-rp{padding:22px 16px;text-align:center;color:var(--mc-muted);font-size:.83rem;line-height:1.6}

        /* Tipo badges */
        .ev-tipo-sesion      {background:#dcfce7;color:#166534}
        .ev-tipo-hito        {background:#fef9c3;color:#854d0e}
        .ev-tipo-recordatorio{background:#dbeafe;color:#1e40af}
        .ev-tipo-bloqueo     {background:#f1f5f9;color:#475569}
        .ev-tipo-tarea       {background:#ede9fe;color:#5b21b6}
        .ev-tipo-expiracion  {background:#fee2e2;color:#991b1b}

        /* ── RADAR CHART ── */
        .chart-wrap{padding:16px;position:relative}
        .chart-canvas-wrap{position:relative;height:220px}
        .chart-no-data{
            height:180px;display:flex;flex-direction:column;align-items:center;
            justify-content:center;gap:8px;color:var(--mc-muted);font-size:.82rem;text-align:center;
        }
        .chart-no-data svg{opacity:.35}

        /* ── EXPIRY RULE ── */
        .expiry-info{padding:14px 16px;font-size:.81rem;color:var(--mc-muted);line-height:1.75}
        .expiry-info strong{color:var(--mc-dark)}
        .expiry-bar{display:flex;align-items:center;gap:8px;margin-top:10px}
        .expiry-bar-track{flex:1;height:5px;background:#fee2e2;border-radius:99px}
        .expiry-bar-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,#fca5a5,#ef4444)}
        .expiry-note{margin-top:8px;font-size:.73rem;color:#991b1b;font-weight:600}

        /* ── MODALES ── */
        .ev-modal-ov{display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:1100;backdrop-filter:blur(2px)}
        .ev-modal-ov.open{display:block}
        .ev-modal-box{
            position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
            width:460px;max-width:94vw;background:#fff;border-radius:16px;
            box-shadow:0 24px 64px rgba(0,0,0,.22);z-index:1101;display:none;
        }
        .ev-modal-box.open{display:block}
        .ev-modal-head{
            padding:14px 18px;display:flex;align-items:center;justify-content:space-between;
            border-bottom:1px solid var(--mc-border);
        }
        .ev-modal-head h3{margin:0;font-size:.95rem;font-weight:800}
        .ev-modal-close{
            width:28px;height:28px;border-radius:50%;border:none;background:#f1f5f9;
            cursor:pointer;font-size:.9rem;display:grid;place-items:center;color:var(--mc-muted);
            transition:background .15s,color .15s;
        }
        .ev-modal-close:hover{background:#e2e8f0;color:var(--mc-dark)}
        .ev-modal-body{padding:16px 18px;font-size:.87rem}
        .ev-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
        .ev-btn{padding:7px 16px;border-radius:8px;font-family:'Saira',sans-serif;font-size:.8rem;font-weight:700;cursor:pointer;transition:background .15s;border:none}
        .ev-btn--primary{background:var(--mc-green);color:#fff}
        .ev-btn--primary:hover{background:var(--mc-green-d)}
        .ev-btn--danger{background:#fee2e2;color:#991b1b}
        .ev-btn--danger:hover{background:#fecaca}
        .ev-btn--ghost{background:#f1f5f9;color:var(--mc-dark)}
        .ev-btn--ghost:hover{background:#e2e8f0}

        /* Form */
        .ev-form label{display:block;font-size:.77rem;font-weight:700;margin-bottom:4px;color:var(--mc-dark)}
        .ev-form input,.ev-form select,.ev-form textarea{
            width:100%;padding:8px 10px;border:1px solid var(--mc-border);border-radius:8px;
            font-family:'Saira',sans-serif;font-size:.83rem;color:var(--mc-dark);
            background:#fff;transition:border-color .15s;
        }
        .ev-form input:focus,.ev-form select:focus,.ev-form textarea:focus{outline:none;border-color:var(--mc-green)}
        .ev-form textarea{resize:vertical;min-height:56px}
        .ev-form .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .ev-form .form-group{margin-bottom:12px}
        .ev-color-row{display:flex;gap:7px;flex-wrap:wrap;margin-top:5px}
        .ev-color-swatch{
            width:22px;height:22px;border-radius:50%;cursor:pointer;border:2px solid transparent;
            transition:transform .1s,border-color .1s;
        }
        .ev-color-swatch.selected,.ev-color-swatch:hover{transform:scale(1.25);border-color:rgba(0,0,0,.28)}

        /* Toast */
        .cal-toast{
            position:fixed;bottom:24px;right:24px;
            background:var(--mc-dark);color:#fff;
            padding:10px 18px;border-radius:10px;font-size:.82rem;font-weight:600;
            box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:2000;
            opacity:0;transform:translateY(10px);transition:opacity .25s,transform .25s;
            pointer-events:none;
        }
        .cal-toast.show{opacity:1;transform:translateY(0)}
    </style>
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard-content">

                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <div class="contenido-dashboard" style="min-width:0">
                <div class="cal-page">

        <!-- ── COLUMNA PRINCIPAL ── -->
        <div>
            <div class="cal-topbar">
                <div class="cal-topbar-left">
                    <h1 class="cal-topbar-title">Planificador</h1>
                    <p class="cal-topbar-sub">Clic en un día para crear · Arrastra eventos para moverlos</p>
                </div>
                <button class="btn-nuevo-ev" id="btnNuevoEvento">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nuevo evento
                </button>
            </div>

            <?php
            $hoy = date('Y-m-d');
            $evMes = array_filter($fcEvents, fn($e) => substr($e['start'] ?? '', 0, 7) === date('Y-m'));
            $evProximos = array_filter($eventosPersonales, fn($e) => ($e['fecha_inicio'] ?? '') >= $hoy);
            $evVencidos = array_filter($fcEvents, fn($e) => ($e['extendedProps']['tipo'] ?? '') === 'expiracion' && ($e['start'] ?? '') <= $hoy);
            ?>
            <div class="cal-stats-strip">
                <div class="cal-stat-pill">
                    <span class="cal-stat-dot" style="background:var(--mc-green)"></span>
                    <?= count($evMes) ?> <span>este mes</span>
                </div>
                <div class="cal-stat-pill">
                    <span class="cal-stat-dot" style="background:#3b82f6"></span>
                    <?= count($evProximos) ?> <span>próximos</span>
                </div>
                <?php if (count($evVencidos) > 0): ?>
                <div class="cal-stat-pill" style="border-color:#fecaca;color:#991b1b">
                    <span class="cal-stat-dot" style="background:#ef4444"></span>
                    <?= count($evVencidos) ?> <span style="color:#dc2626">expirados</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($cursosEnProgreso)): ?>
                <div class="cal-stat-pill">
                    <span class="cal-stat-dot" style="background:#8b5cf6"></span>
                    <?= count($cursosEnProgreso) ?> <span>cursos activos</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="cal-wrap">
                <div id="fc-calendar"></div>

                <?php if (!empty($cursoColors)): ?>
                    <div class="cal-legend">
                        <?php foreach ($cursoColors as $nombre => $color): ?>
                            <span class="legend-item">
                                <span class="legend-dot" style="background:<?= htmlspecialchars($color) ?>"></span>
                                <?= htmlspecialchars($nombre) ?>
                            </span>
                        <?php endforeach; ?>
                        <span class="legend-item"><span class="legend-dot" style="background:#ef4444"></span>Expiración</span>
                        <span class="legend-item"><span class="legend-dot" style="background:#6B8F71"></span>Sesión</span>
                        <span class="legend-item"><span class="legend-dot" style="background:#f59e0b"></span>Hito</span>
                        <span class="legend-item"><span class="legend-dot" style="background:#3b82f6"></span>Recordatorio</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── PANEL DERECHO ── -->
        <aside class="right-panel">

            <!-- Próximos eventos personales -->
            <div class="rp-card">
                <div class="rp-head">
                    Próximos eventos
                    <button class="rp-head-btn" id="btnNuevoEvRp">+ Añadir</button>
                </div>
                <?php
                $tipoLabels = ['sesion'=>'Sesión','hito'=>'Hito','recordatorio'=>'Recordatorio','bloqueo'=>'Bloqueo'];
                $tipoBadges = ['sesion'=>'ev-tipo-sesion','hito'=>'ev-tipo-hito','recordatorio'=>'ev-tipo-recordatorio','bloqueo'=>'ev-tipo-bloqueo'];
                $evOrdenados = $eventosPersonales;
                usort($evOrdenados, fn($a,$b) => strcmp($a['fecha_inicio'], $b['fecha_inicio']));
                $proximos = array_slice(array_values(array_filter($evOrdenados, fn($e) => $e['fecha_inicio'] >= date('Y-m-d'))), 0, 6);
                ?>
                <?php if (empty($proximos)): ?>
                    <div class="empty-rp">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Sin eventos planificados.<br>
                        <button class="rp-head-btn" style="margin-top:10px" onclick="abrirFormNuevo(null)">Crear evento</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($proximos as $ev):
                        $badgeClass = $tipoBadges[$ev['tipo']] ?? 'ev-tipo-sesion';
                        $label      = $tipoLabels[$ev['tipo']] ?? 'Evento';
                        $fecha      = date('d M', strtotime($ev['fecha_inicio']));
                    ?>
                        <div class="ev-item">
                            <span class="ev-dot" style="background:<?= htmlspecialchars($ev['color'] ?? '#6B8F71') ?>"></span>
                            <div style="flex:1;min-width:0">
                                <p class="ev-titulo"><?= htmlspecialchars($ev['titulo']) ?>
                                    <button class="ev-edit-btn" data-ev-id="<?= (int)$ev['id'] ?>" title="Editar">✏</button>
                                </p>
                                <div class="ev-meta">
                                    <span class="ev-tag <?= $badgeClass ?>"><?= $label ?></span>
                                    <span class="ev-date"><?= $fecha ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Gráfico de telaraña: progreso por curso -->
            <div class="rp-card">
                <div class="rp-head">Progreso de cursos</div>
                <?php
                $cursosConProgreso = array_filter($cursosEnProgreso ?? [], fn($c) => $c['total_lecciones'] > 0);
                ?>
                <div class="chart-wrap">
                    <?php if (empty($cursosConProgreso)): ?>
                        <div class="chart-no-data">
                            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Sin cursos matriculados
                        </div>
                    <?php else: ?>
                        <div class="chart-canvas-wrap">
                            <canvas id="radarChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Expiración de cursos -->
            <div class="rp-card">
                <div class="rp-head">Expiración de cursos</div>
                <?php
                $eventosExpiracion = array_values(array_filter($fcEvents, fn($e) => ($e['extendedProps']['tipo'] ?? '') === 'expiracion'));
                usort($eventosExpiracion, fn($a,$b) => strcmp($a['start'], $b['start']));
                $hoyTs = strtotime(date('Y-m-d'));
                ?>
                <?php if (empty($eventosExpiracion)): ?>
                    <div class="expiry-info">
                        Sin cursos con fecha de expiración próxima. Sigue así.
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($eventosExpiracion, 0, 4) as $exp):
                        $expTs   = strtotime($exp['start']);
                        $diasRest = max(0, (int)ceil(($expTs - $hoyTs) / 86400));
                        $pctUsed  = max(0, min(100, round((1 - $diasRest/90) * 100)));
                        $urgente  = $diasRest <= 14;
                    ?>
                    <div class="expiry-info" style="border-bottom:1px solid var(--mc-border)">
                        <strong><?= htmlspecialchars($exp['title'] ?? 'Curso') ?></strong>
                        <div class="expiry-bar">
                            <div class="expiry-bar-track">
                                <div class="expiry-bar-fill" style="width:<?= $pctUsed ?>%"></div>
                            </div>
                        </div>
                        <?php if ($diasRest === 0): ?>
                            <p class="expiry-note">Expirado hoy</p>
                        <?php elseif ($urgente): ?>
                            <p class="expiry-note"><?= $diasRest ?> día<?= $diasRest !== 1 ? 's' : '' ?> restante<?= $diasRest !== 1 ? 's' : '' ?></p>
                        <?php else: ?>
                            <p style="margin-top:6px;font-size:.72rem;color:var(--mc-muted)">Expira el <?= date('d/m/Y', $expTs) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </aside>
    </div><!-- /.cal-page -->
    </div><!-- /.contenido-dashboard -->
    </div><!-- /.contenedor-dashboard-content -->
    </div><!-- /.mc-container -->
    </main><!-- /.main-dashboard -->

    <!-- ── MODAL: VER EVENTO ── -->
    <div class="ev-modal-ov" id="evOv"></div>
    <div class="ev-modal-box" id="evModal">
        <div class="ev-modal-head">
            <h3 id="evTitle"></h3>
            <button class="ev-modal-close" id="evClose">✕</button>
        </div>
        <div class="ev-modal-body" id="evBody"></div>
    </div>

    <!-- ── MODAL: CREAR / EDITAR EVENTO ── -->
    <div class="ev-modal-ov" id="formOv"></div>
    <div class="ev-modal-box" id="formModal">
        <div class="ev-modal-head">
            <h3 id="formTitle">Nuevo evento</h3>
            <button class="ev-modal-close" id="formClose">✕</button>
        </div>
        <div class="ev-modal-body">
            <form class="ev-form" id="eventoForm">
                <input type="hidden" id="evIdField" name="id" value="">

                <div class="form-group">
                    <label for="evTitulo">Título *</label>
                    <input type="text" id="evTitulo" name="titulo" placeholder="Ej: Repasar CSS Grid" required maxlength="120">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="evTipo">Tipo</label>
                        <select id="evTipo" name="tipo">
                            <option value="sesion">Sesión de estudio</option>
                            <option value="hito">Hito personal</option>
                            <option value="recordatorio">Recordatorio</option>
                            <option value="bloqueo">Bloqueo de tiempo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="evFechaInicio">Fecha *</label>
                        <input type="date" id="evFechaInicio" name="fecha_inicio" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="evFechaFin">Fecha fin (opcional)</label>
                        <input type="date" id="evFechaFin" name="fecha_fin">
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <div class="ev-color-row" id="colorSwatches">
                            <?php
                            $colores = ['#6B8F71','#3b82f6','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#ef4444','#f97316','#94a3b8'];
                            foreach ($colores as $c): ?>
                                <span class="ev-color-swatch <?= $c === '#6B8F71' ? 'selected' : '' ?>"
                                    style="background:<?= $c ?>"
                                    data-color="<?= $c ?>"></span>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="evColor" name="color" value="#6B8F71">
                    </div>
                </div>

                <div class="form-group">
                    <label for="evDescripcion">Descripción (opcional)</label>
                    <textarea id="evDescripcion" name="descripcion" placeholder="Notas adicionales…"></textarea>
                </div>

                <div class="ev-actions">
                    <button type="submit" class="ev-btn ev-btn--primary">Guardar</button>
                    <button type="button" class="ev-btn ev-btn--ghost" id="formCancelBtn">Cancelar</button>
                    <button type="button" class="ev-btn ev-btn--danger" id="eliminarEvBtn" style="display:none;margin-left:auto">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast de confirmación -->
    <div class="cal-toast" id="calToast"></div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/es.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
    const BASE     = '<?= BASE_URL ?>';
    const fcEvents = <?= json_encode($fcEvents, JSON_HEX_TAG | JSON_HEX_APOS) ?>;

    const tipLabels  = {sesion:'Sesión de estudio',hito:'Hito personal',recordatorio:'Recordatorio',bloqueo:'Bloqueo',tarea:'Tarea del curso',expiracion:'Expiración'};
    const tipClasses = {sesion:'ev-tipo-sesion',hito:'ev-tipo-hito',recordatorio:'ev-tipo-recordatorio',bloqueo:'ev-tipo-bloqueo',tarea:'ev-tipo-tarea',expiracion:'ev-tipo-expiracion'};

    // ── TOAST ──
    function showToast(msg) {
        const t = document.getElementById('calToast');
        t.textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2800);
    }

    // ── MODAL VER EVENTO ──
    const evOv    = document.getElementById('evOv');
    const evModal = document.getElementById('evModal');

    function openViewEvent(info) {
        const p     = info.event.extendedProps;
        const tipo  = p.tipo || 'info';
        const color = info.event.backgroundColor || '#6B8F71';
        document.getElementById('evTitle').textContent = info.event.title;

        let actionsHtml = '';
        if (p.editable && p.ev_id) {
            actionsHtml = `<div class="ev-actions"><button class="ev-btn ev-btn--primary" onclick="abrirFormEditar(${p.ev_id})">Editar</button></div>`;
        }

        document.getElementById('evBody').innerHTML =
            `<span class="ev-tag ${tipClasses[tipo] || ''}" style="background:${color}22;color:${color}">${tipLabels[tipo] || tipo}</span>` +
            (p.curso       ? `<p style="margin:6px 0 4px;font-size:.78rem;color:var(--mc-muted)">${escHtml(p.curso)}</p>` : '') +
            (p.descripcion ? `<p style="margin:4px 0 8px">${escHtml(p.descripcion)}</p>` : '') +
            actionsHtml;

        evOv.classList.add('open'); evModal.classList.add('open');
    }

    function closeViewModal() { evOv.classList.remove('open'); evModal.classList.remove('open'); }
    [evOv, document.getElementById('evClose')].forEach(el => el.addEventListener('click', closeViewModal));

    // ── FULLCALENDAR ──
    let calendar;
    document.addEventListener('DOMContentLoaded', function () {
        const calEl = document.getElementById('fc-calendar');
        calendar = new FullCalendar.Calendar(calEl, {
            initialView:  'dayGridMonth',
            locale:       'es',
            headerToolbar:{left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,listMonth'},
            buttonText:   {today:'Hoy', month:'Mes', week:'Semana', list:'Lista'},
            events:       fcEvents,
            height:       'auto',
            editable:     false,   // controla sólo los NO-personales; overridimos por evento
            eventStartEditable: true,
            dayMaxEvents: 3,
            eventClick:   openViewEvent,
            dateClick:    function(info) { abrirFormNuevo(info.dateStr); },

            // Permitir drag solo a eventos personales (extendedProps.editable)
            eventAllow: function(dropInfo, draggedEvent) {
                return !!draggedEvent.extendedProps.editable;
            },

            eventDrop: function(info) {
                const p = info.event.extendedProps;
                if (!p.editable || !p.ev_id) { info.revert(); return; }

                const nuevaFecha = info.event.startStr.substring(0, 10);
                fetch(BASE + '/index.php?url=api-eventos-usuario&action=actualizar', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({id: p.ev_id, fecha_inicio: nuevaFecha})
                })
                .then(r => r.json())
                .then(res => {
                    if (res.ok) {
                        showToast('Evento movido a ' + nuevaFecha.split('-').reverse().join('/'));
                    } else {
                        info.revert();
                        showToast('No se pudo mover el evento');
                    }
                })
                .catch(() => { info.revert(); showToast('Error de red'); });
            },

            eventDidMount: function(info) {
                const tipo = info.event.extendedProps.tipo;
                if (tipo === 'expiracion') info.el.style.borderLeft = '3px solid #b91c1c';
                if (tipo === 'hito')       info.el.style.borderLeft = '3px solid #d97706';
                if (info.event.extendedProps.editable) {
                    info.el.style.cursor = 'grab';
                    info.el.title = 'Arrastra para cambiar la fecha';
                }
            }
        });
        calendar.render();

        fcEvents.forEach(ev => {
            if (ev.extendedProps && ev.extendedProps.editable) {
                const calEv = calendar.getEventById(ev.id);
                if (calEv) calEv.setProp('startEditable', true);
            }
        });

        // Reajustar FullCalendar cuando el sidebar cambia de tamaño
        const sidebarToggleBtn = document.getElementById('sidebarToggle');
        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', function () {
                // Esperar a que termine la transición CSS (~220ms) y re-renderizar
                setTimeout(() => calendar && calendar.updateSize(), 230);
            });
        }

        // ── RADAR CHART ──
        const radarCanvas = document.getElementById('radarChart');
        if (radarCanvas) {
            const cursosData  = <?= json_encode(array_values(array_map(
                fn($c) => ['titulo' => mb_substr($c['titulo'], 0, 22), 'progreso' => $c['progreso']],
                array_filter($cursosEnProgreso ?? [], fn($c) => $c['total_lecciones'] > 0)
            )), JSON_HEX_TAG | JSON_HEX_APOS) ?>;

            const labels   = cursosData.map(c => c.titulo);
            const valores  = cursosData.map(c => c.progreso);

            // Si sólo hay 1 curso, usar doughnut; si hay 2+, radar
            if (cursosData.length === 1) {
                new Chart(radarCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completado', 'Pendiente'],
                        datasets: [{
                            data: [valores[0], 100 - valores[0]],
                            backgroundColor: ['#6B8F71', '#e5e7eb'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        cutout: '70%',
                        plugins: {
                            legend: {display: false},
                            tooltip: {callbacks: {label: ctx => ctx.label + ': ' + ctx.raw + '%'}},
                        },
                        animation: {duration: 700}
                    },
                    plugins: [{
                        id: 'centerText',
                        afterDraw(chart) {
                            const {ctx, chartArea:{left,top,right,bottom}} = chart;
                            ctx.save();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            const cx = (left + right) / 2, cy = (top + bottom) / 2;
                            ctx.font = 'bold 22px Saira,sans-serif';
                            ctx.fillStyle = '#1B2336';
                            ctx.fillText(valores[0] + '%', cx, cy - 4);
                            ctx.font = '600 10px Saira,sans-serif';
                            ctx.fillStyle = '#6b7280';
                            ctx.fillText(labels[0].length > 16 ? labels[0].substring(0,16)+'…' : labels[0], cx, cy + 16);
                            ctx.restore();
                        }
                    }]
                });
            } else {
                new Chart(radarCanvas, {
                    type: 'radar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Progreso (%)',
                            data: valores,
                            backgroundColor: 'rgba(107,143,113,.18)',
                            borderColor: '#6B8F71',
                            borderWidth: 2.2,
                            pointBackgroundColor: '#6B8F71',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                min: 0, max: 100,
                                ticks: {
                                    stepSize: 25,
                                    font: {family:"'Saira',sans-serif", size:9},
                                    color: '#9ca3af',
                                    backdropColor: 'transparent',
                                    callback: v => v + '%',
                                },
                                pointLabels: {
                                    font: {family:"'Saira',sans-serif", size:10, weight:'700'},
                                    color: '#374151',
                                    callback: lbl => lbl.length > 14 ? lbl.substring(0,14)+'…' : lbl,
                                },
                                grid:  {color:'#e5e7eb'},
                                angleLines:{color:'#e5e7eb'},
                            }
                        },
                        plugins: {
                            legend: {display: false},
                            tooltip: {
                                callbacks: {label: ctx => ' ' + ctx.raw + '% completado'},
                                titleFont: {family:"'Saira',sans-serif"},
                                bodyFont:  {family:"'Saira',sans-serif"},
                            }
                        },
                        animation: {duration: 800}
                    }
                });
            }
        }
    });

    // ── MODAL CREAR / EDITAR ──
    const formOv      = document.getElementById('formOv');
    const formModal   = document.getElementById('formModal');
    const eventoForm  = document.getElementById('eventoForm');
    const formTitle   = document.getElementById('formTitle');
    const evIdField   = document.getElementById('evIdField');
    const eliminarBtn = document.getElementById('eliminarEvBtn');
    let selectedColor = '#6B8F71';

    document.querySelectorAll('.ev-color-swatch').forEach(sw => {
        sw.addEventListener('click', () => {
            document.querySelectorAll('.ev-color-swatch').forEach(s => s.classList.remove('selected'));
            sw.classList.add('selected');
            selectedColor = sw.dataset.color;
            document.getElementById('evColor').value = selectedColor;
        });
    });

    function openFormModal() { formOv.classList.add('open'); formModal.classList.add('open'); closeViewModal(); }
    function closeFormModal() {
        formOv.classList.remove('open'); formModal.classList.remove('open');
        eventoForm.reset(); evIdField.value = ''; eliminarBtn.style.display = 'none';
        selectedColor = '#6B8F71';
        document.querySelectorAll('.ev-color-swatch').forEach(s => s.classList.toggle('selected', s.dataset.color === '#6B8F71'));
        document.getElementById('evColor').value = '#6B8F71';
    }

    function abrirFormNuevo(fecha) {
        formTitle.textContent = 'Nuevo evento';
        evIdField.value = ''; eliminarBtn.style.display = 'none';
        if (fecha) document.getElementById('evFechaInicio').value = fecha;
        openFormModal();
    }

    function abrirFormEditar(evId) {
        closeViewModal();
        fetch(BASE + '/index.php?url=api-eventos-usuario', {headers:{'Accept':'application/json'}})
        .then(r => r.json())
        .then(lista => {
            const ev = lista.find(e => parseInt(e.id) === evId);
            if (!ev) return;
            formTitle.textContent = 'Editar evento';
            evIdField.value = ev.id;
            document.getElementById('evTitulo').value      = ev.titulo;
            document.getElementById('evTipo').value        = ev.tipo;
            document.getElementById('evFechaInicio').value = ev.fecha_inicio.substring(0,10);
            document.getElementById('evFechaFin').value    = ev.fecha_fin ? ev.fecha_fin.substring(0,10) : '';
            document.getElementById('evDescripcion').value = ev.descripcion || '';
            const color = ev.color || '#6B8F71';
            selectedColor = color;
            document.getElementById('evColor').value = color;
            document.querySelectorAll('.ev-color-swatch').forEach(s => s.classList.toggle('selected', s.dataset.color === color));
            eliminarBtn.style.display = 'block';
            openFormModal();
        });
    }

    document.getElementById('btnNuevoEvento').addEventListener('click', () => abrirFormNuevo(null));
    document.getElementById('btnNuevoEvRp')?.addEventListener('click', () => abrirFormNuevo(null));
    [formOv, document.getElementById('formClose'), document.getElementById('formCancelBtn')].forEach(el => el?.addEventListener('click', closeFormModal));

    document.querySelectorAll('.ev-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => abrirFormEditar(parseInt(btn.dataset.evId)));
    });

    eventoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(eventoForm);
        const id = fd.get('id');
        const data = Object.fromEntries(fd);
        data.color = selectedColor;
        const action = id ? 'actualizar' : 'crear';
        const resp = await fetch(BASE + '/index.php?url=api-eventos-usuario&action=' + action, {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)
        });
        const result = await resp.json();
        if (result.ok) { closeFormModal(); location.reload(); }
        else alert('No se pudo guardar el evento. ' + (result.error || ''));
    });

    eliminarBtn.addEventListener('click', async function() {
        if (!confirm('¿Eliminar este evento?')) return;
        const resp = await fetch(BASE + '/index.php?url=api-eventos-usuario&action=eliminar', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({id:parseInt(evIdField.value)})
        });
        const result = await resp.json();
        if (result.ok) { closeFormModal(); location.reload(); }
    });

    function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    </script>
</body>
</html>
