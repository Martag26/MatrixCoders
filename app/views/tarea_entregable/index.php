<?php
$tituloCurso = htmlspecialchars($tarea['curso_titulo'] ?? 'Curso');
$tituloTarea = htmlspecialchars($tarea['titulo'] ?? 'Tarea');
$unidadTit   = htmlspecialchars($tarea['unidad_titulo'] ?? '');
$descripcion = $tarea['descripcion'] ?? '';
$fechaLimite = $tarea['fecha_limite'] ?? null;

function fmtFechaTE(?string $dt): string {
    if (!$dt) return '—';
    return date('d/m/Y', strtotime($dt));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $tituloTarea ?> — MatrixCoders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<style>
:root{--mc-green:#6B8F71;--mc-green-d:#4a6b50;--mc-dark:#1B2336;--mc-navy:#0f172a;--mc-border:#e5e7eb;--mc-soft:#f8fafc;--mc-muted:#6b7280;--mc-text:#374151;--header-h:66px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;overflow:hidden;font-family:'Saira',sans-serif;background:#f6f6f6;color:var(--mc-dark);}

/* Layout igual que leccion */
.te-layout{
    display:grid;
    grid-template-columns:minmax(0,1fr) 320px;
    height:calc(100vh - var(--header-h));
    max-width:1280px;
    margin:0 auto;
    overflow:hidden;
    background:#fff;
}
@media(max-width:900px){
    .te-layout{grid-template-columns:1fr;}
    .temario-sidebar{display:none;}
}

/* Columna principal scrollable */
.te-main{overflow-y:auto;overflow-x:hidden;background:#fff;}

/* Hero */
.te-hero{background:linear-gradient(135deg,#1e3a5f 0%,#0f172a 100%);padding:22px 28px;color:#fff;}
.te-hero .badge-tipo{display:inline-block;background:rgba(245,158,11,.25);color:#fcd34d;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:3px 10px;border-radius:20px;margin-bottom:8px;}
.te-hero h1{font-size:1.25rem;font-weight:800;margin:0 0 6px;}
.te-hero .meta{font-size:.8rem;color:#94a3b8;display:flex;gap:14px;flex-wrap:wrap;}

/* Contenido */
.te-body{padding:22px 24px 48px;}
.card-section{background:#fff;border:1px solid var(--mc-border);border-radius:12px;padding:20px;margin-bottom:16px;}
.card-section h2{font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--mc-muted);margin:0 0 10px;}
.descripcion-box{background:var(--mc-soft);border-left:3px solid #f59e0b;border-radius:8px;padding:12px 14px;font-size:.9rem;line-height:1.75;color:var(--mc-text);white-space:pre-wrap;position:relative;transition:max-height .35s ease;}
.descripcion-box.collapsed{max-height:7.5em;overflow:hidden;}
.descripcion-box.collapsed::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2.5em;background:linear-gradient(transparent,var(--mc-soft));border-radius:0 0 8px 8px;pointer-events:none;}
.btn-desc-toggle{background:none;border:none;color:#92400e;font-size:.82rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;padding:4px 0 0;display:block;}
.estado-badge{display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:700;padding:5px 13px;border-radius:99px;}
.estado-entregada{background:#d1fae5;color:#065f46;}
.estado-pendiente{background:#fef3c7;color:#92400e;}
.estado-vencida{background:#fee2e2;color:#991b1b;}
.entrega-prev{background:var(--mc-soft);border:1px solid var(--mc-border);border-radius:8px;padding:12px;font-size:.88rem;white-space:pre-wrap;line-height:1.7;margin-top:10px;}
.btn-entrega-accion{display:inline-flex;align-items:center;gap:6px;padding:.45rem 1rem;border-radius:8px;font-size:.83rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;border:1.5px solid;transition:all .15s;}
.btn-ver{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;}.btn-ver:hover{background:#dbeafe;}
.btn-mod{background:#fefce8;color:#854d0e;border-color:#fde68a;}.btn-mod:hover{background:#fef08a;}
.btn-cancelar-mod{background:#f1f5f9;color:#475569;border:1.5px solid #cbd5e1;border-radius:8px;padding:.4rem .9rem;font-size:.82rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;margin-top:10px;transition:background .15s;}.btn-cancelar-mod:hover{background:#e2e8f0;}
.archivo-actual{background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:8px 12px;font-size:.83rem;color:#166534;margin-bottom:10px;display:flex;align-items:center;gap:6px;}
.nota-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-top:12px;}
.nota-box .nota-num{font-size:1.6rem;font-weight:800;color:#1e40af;line-height:1;}
.nota-box .feedback{font-size:.87rem;color:var(--mc-text);margin-top:6px;line-height:1.6;}
label.field-label{display:block;font-size:.85rem;font-weight:700;color:var(--mc-dark);margin-bottom:6px;}
textarea.te-textarea{width:100%;min-height:140px;border:1.5px solid var(--mc-border);border-radius:10px;padding:.8rem;font-family:'Saira',sans-serif;font-size:.9rem;resize:vertical;line-height:1.6;transition:border-color .15s;}
textarea.te-textarea:focus{outline:none;border-color:#f59e0b;}
.file-drop{border:2px dashed var(--mc-border);border-radius:10px;padding:12px;text-align:center;cursor:pointer;transition:all .2s;margin-top:8px;position:relative;}
.file-drop:hover,.file-drop.drag-over{border-color:#f59e0b;background:#fffbeb;}
.file-drop input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.file-drop p{font-size:.8rem;color:var(--mc-muted);margin:0;}
.file-selected{font-size:.82rem;color:#92400e;font-weight:600;margin-top:6px;}
.btn-entregar{background:#f59e0b;color:#fff;border:none;border-radius:10px;padding:.6rem 1.4rem;font-size:.9rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;margin-top:12px;transition:background .15s;}
.btn-entregar:hover{background:#d97706;}
.btn-entregar:disabled{opacity:.5;cursor:not-allowed;}
.alert-vencida{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:10px 14px;font-size:.85rem;color:#7f1d1d;margin-bottom:12px;}

/* ── SIDEBAR (igual que leccion) ── */
.temario-sidebar{
    border-left:1px solid var(--mc-border);
    overflow-y:auto;
    overflow-x:hidden;
    background:#fff;
    display:flex;
    flex-direction:column;
}
.temario-head{
    padding:.9rem 1.1rem;background:var(--mc-navy);color:#fff;font-weight:700;font-size:.85rem;
    position:sticky;top:0;z-index:2;display:flex;justify-content:space-between;align-items:center;
    flex-shrink:0;
}
.temario-head small{color:#94a3b8;font-weight:400;font-size:.75rem;}
.volver-curso{
    display:flex;align-items:center;gap:.5rem;padding:.75rem 1.1rem;font-size:.82rem;
    color:var(--mc-muted);text-decoration:none;border-bottom:1px solid var(--mc-border);
    transition:color .15s,background .15s;flex-shrink:0;
}
.volver-curso:hover{background:#eef2f7;color:var(--mc-dark);}
.t-unidad-btn{
    width:100%;display:flex;align-items:center;justify-content:space-between;
    padding:.6rem 1rem;font-size:.75rem;font-weight:700;color:var(--mc-muted);
    text-transform:uppercase;letter-spacing:.5px;background:#eef2f7;
    border:none;border-top:1px solid var(--mc-border);cursor:pointer;text-align:left;
    transition:background .15s;font-family:'Saira',sans-serif;
}
.t-unidad-btn:hover{background:#e2e8f0;}
.u-meta{display:inline-flex;align-items:center;gap:.45rem;min-width:0;flex-shrink:0;}
.u-progress{
    display:inline-flex;align-items:center;justify-content:center;min-width:38px;
    padding:2px 7px;border-radius:999px;background:rgba(107,143,113,.12);
    color:var(--mc-green-d);font-size:.68rem;font-weight:700;text-transform:none;letter-spacing:0;
}
.u-chevron{font-size:.7rem;transition:transform .2s;flex-shrink:0;}
.t-unidad-btn.collapsed .u-chevron{transform:rotate(-90deg);}
.u-check-all{width:13px;height:13px;cursor:pointer;accent-color:var(--mc-green);flex-shrink:0;}
.t-lecciones-list{overflow:hidden;transition:max-height .25s ease;}
.t-lecciones-list.cerrado{max-height:0 !important;}
.t-leccion-row{display:flex;align-items:center;border-top:1px solid var(--mc-border);}
.lec-check{width:14px;height:14px;margin:0 0 0 .85rem;flex-shrink:0;cursor:pointer;accent-color:var(--mc-green);}
.t-leccion-row .t-leccion{border-top:none;flex:1;min-width:0;padding-left:.45rem;}
.tl-te-icon{font-size:.9rem;margin:0 0 0 .85rem;flex-shrink:0;line-height:1;}
.t-leccion{
    display:flex;align-items:flex-start;gap:.5rem;padding:.6rem .9rem .6rem 1.3rem;
    text-decoration:none;color:var(--mc-text);font-size:.83rem;transition:background .15s;
}
.t-leccion:hover{background:#e4ebe5;color:var(--mc-dark);}
.t-leccion.activa{color:var(--mc-green-d);font-weight:700;}
.t-tarea-row{border-left:3px solid #f59e0b;}
.t-tarea-row.t-tarea-entregada{border-left:3px solid var(--mc-green);}
.t-leccion-row:has(.t-leccion.activa){background:#d4e6d6;border-left:3px solid var(--mc-green) !important;}
.t-leccion.vista{color:var(--mc-text);opacity:.9;}
.t-leccion span{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.35;}

/* Toast */
.mc-toast-wrap{position:fixed;bottom:24px;right:360px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
.mc-toast{background:#1e293b;color:#fff;border-radius:10px;padding:12px 18px;font-size:.88rem;font-weight:600;font-family:'Saira',sans-serif;box-shadow:0 4px 20px rgba(0,0,0,.25);opacity:0;transform:translateY(8px);transition:opacity .2s,transform .2s;pointer-events:none;max-width:320px;}
.mc-toast.show{opacity:1;transform:translateY(0);}
.mc-toast.success{background:#166534;}.mc-toast.error{background:#991b1b;}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>
<div class="mc-toast-wrap" id="mcToastWrap"></div>

<div class="te-layout">

  <!-- ── COLUMNA PRINCIPAL ── -->
  <div class="te-main">

    <!-- Hero -->
    <div class="te-hero">
      <div class="badge-tipo">📝 Tarea evaluable</div>
      <h1><?= $tituloTarea ?></h1>
      <div class="meta">
        <span>📚 <?= $tituloCurso ?></span>
        <?php if ($unidadTit): ?><span>📂 <?= $unidadTit ?></span><?php endif; ?>
        <?php if ($fechaLimite): ?><span>📅 Entrega: <?= fmtFechaTE($fechaLimite) ?></span><?php endif; ?>
      </div>
    </div>

    <div class="te-body">

      <!-- Enunciado colapsable -->
      <?php if ($descripcion): ?>
      <div class="card-section">
        <h2>Enunciado</h2>
        <div class="descripcion-box collapsed" id="desc-box"><?= nl2br(htmlspecialchars($descripcion)) ?></div>
        <button class="btn-desc-toggle" id="btn-desc" onclick="toggleDesc()">Mostrar más ▼</button>
      </div>
      <?php endif; ?>

      <!-- Estado y entrega -->
      <div class="card-section">
        <h2>Tu entrega</h2>

        <?php if ($plazoSuperado && !$entrega): ?>
          <div class="alert-vencida">⚠ El plazo de entrega finalizó el <?= fmtFechaTE($fechaLimite) ?>. Ya no es posible enviar esta tarea.</div>

        <?php elseif ($entrega && $entrega['revisado']): ?>
          <span class="estado-badge estado-entregada">✅ Revisada</span>
          <?php if (!empty($entrega['respuesta'])): ?>
            <div class="entrega-prev"><?= htmlspecialchars($entrega['respuesta']) ?></div>
          <?php endif; ?>
          <?php if (!empty($entrega['archivo'])): ?>
            <div style="margin-top:8px"><a href="<?= htmlspecialchars($entrega['archivo']) ?>" target="_blank" style="color:#2563eb;font-size:.85rem;font-weight:600">📎 Ver archivo adjunto</a></div>
          <?php endif; ?>
          <div class="nota-box">
            <?php if ($entrega['nota'] !== null): ?>
              <div class="nota-num"><?= number_format((float)$entrega['nota'],1) ?><span style="font-size:.95rem;font-weight:400;color:#64748b">/10</span></div>
            <?php endif; ?>
            <?php if (!empty($entrega['feedback'])): ?>
              <div class="feedback"><strong>Feedback:</strong><br><?= nl2br(htmlspecialchars($entrega['feedback'])) ?></div>
            <?php endif; ?>
          </div>

        <?php elseif ($entrega): ?>
          <span class="estado-badge estado-entregada">✓ Entregada · Pendiente de revisión</span>
          <p style="font-size:.83rem;color:var(--mc-muted);margin-top:8px">Enviada el <?= fmtFechaTE($entrega['entregado_en']) ?>. Recibirás una notificación cuando se corrija.</p>
          <?php
            $soloArchivo = empty($entrega['respuesta']) && !empty($entrega['archivo']);
          ?>
          <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
            <?php if ($soloArchivo): ?>
              <a href="<?= htmlspecialchars($entrega['archivo']) ?>" target="_blank" class="btn-entrega-accion btn-ver">👁 Ver entrega</a>
            <?php else: ?>
              <button class="btn-entrega-accion btn-ver" onclick="togglePanel('panel-ver')">👁 Ver entrega</button>
            <?php endif; ?>
            <button class="btn-entrega-accion btn-mod" onclick="togglePanel('panel-mod')">✏ Modificar entrega</button>
          </div>

          <!-- Panel: Ver entrega (solo si hay texto) -->
          <?php if (!$soloArchivo): ?>
          <div id="panel-ver" style="display:none;margin-top:12px">
            <?php if (!empty($entrega['respuesta'])): ?>
              <div class="entrega-prev"><?= htmlspecialchars($entrega['respuesta']) ?></div>
            <?php endif; ?>
            <?php if (!empty($entrega['archivo'])): ?>
              <div style="margin-top:8px"><a href="<?= htmlspecialchars($entrega['archivo']) ?>" target="_blank" style="color:#2563eb;font-size:.85rem;font-weight:600">📎 Ver archivo adjunto</a></div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Panel: Modificar entrega -->
          <div id="panel-mod" style="display:none;margin-top:12px">
            <?php if (!empty($entrega['archivo'])): ?>
              <div class="archivo-actual">📎 Archivo actual: <a href="<?= htmlspecialchars($entrega['archivo']) ?>" target="_blank" style="color:#166534;font-weight:600;text-decoration:underline"><?= htmlspecialchars(basename($entrega['archivo'])) ?></a></div>
            <?php endif; ?>
            <?php include __DIR__ . '/partials/form_entrega.php'; ?>
            <button class="btn-cancelar-mod" onclick="togglePanel('panel-mod')">✕ Cancelar modificación</button>
          </div>

        <?php else: ?>
          <span class="estado-badge estado-pendiente">⏳ Pendiente de entrega</span>
          <div style="margin-top:14px"><?php include __DIR__ . '/partials/form_entrega.php'; ?></div>
        <?php endif; ?>
      </div>

    </div><!-- /te-body -->
  </div><!-- /te-main -->

  <!-- ── SIDEBAR ── -->
  <aside class="temario-sidebar">
    <div class="temario-head">
      <span>Contenido del curso</span>
      <small>
        <?php
        $totalLecSb = array_sum(array_map(fn($u) => count($u['lecciones'] ?? []), $unidades));
        $totalTESb  = array_sum(array_map(fn($u) => count($u['tareas_entregables'] ?? []), $unidades));
        echo $totalLecSb . ' lección' . ($totalLecSb !== 1 ? 'es' : '');
        if ($totalTESb > 0) echo ' · ' . $totalTESb . ' tarea' . ($totalTESb !== 1 ? 's' : '');
        ?>
      </small>
    </div>

    <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $cursoId ?>" class="volver-curso">
      ← Volver al curso
    </a>

    <?php foreach ($unidades as $uIdx => $u):
      $lecsU     = $u['lecciones'] ?? [];
      $vistasU   = 0;
      foreach ($lecsU as $lec) { if (isset($leccionesVistas[$lec['id']])) $vistasU++; }
      $totalLecsU = count($lecsU);
      $todasVistasU = $totalLecsU > 0 && $vistasU >= $totalLecsU;
      $unidadId = 'sb-unidad-' . $uIdx;

      // Abrir la unidad que contiene la tarea activa
      $tieneActiva = false;
      foreach (($u['tareas_entregables'] ?? []) as $te) {
          if ($te['id'] == ($tareaActivaId ?? 0)) $tieneActiva = true;
      }
    ?>
    <button
        class="t-unidad-btn <?= $tieneActiva ? '' : 'collapsed' ?>"
        data-target="<?= $unidadId ?>"
        aria-expanded="<?= $tieneActiva ? 'true' : 'false' ?>">
      <span style="min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($u['titulo'] ?? 'Unidad') ?></span>
      <span class="u-meta">
        <input type="checkbox" class="u-check-all"
               title="Marcar todas las lecciones"
               data-unidad="<?= (int)$u['id'] ?>"
               data-lista="<?= $unidadId ?>"
               <?= $todasVistasU ? 'checked' : '' ?>
               onclick="event.stopPropagation()"
               onchange="toggleUnidadAll(this)">
        <span class="u-progress" id="prog-<?= $unidadId ?>"><?= $vistasU ?>/<?= $totalLecsU ?></span>
        <span class="u-chevron">▼</span>
      </span>
    </button>

    <div class="t-lecciones-list <?= $tieneActiva ? '' : 'cerrado' ?>" id="<?= $unidadId ?>">
      <?php foreach ($lecsU as $lec):
        $esVista = isset($leccionesVistas[$lec['id']]);
      ?>
      <div class="t-leccion-row">
        <input type="checkbox" class="lec-check"
               id="lc-<?= $lec['id'] ?>"
               <?= $esVista ? 'checked' : '' ?>
               onchange="toggleLeccion(this, <?= (int)$lec['id'] ?>)"
               onclick="event.stopPropagation()"
               title="Marcar como vista">
        <a href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $lec['id'] ?>"
           class="t-leccion <?= $esVista ? 'vista' : '' ?>">
          <span><?= htmlspecialchars($lec['titulo'] ?? 'Lección') ?></span>
        </a>
      </div>
      <?php endforeach; ?>
      <?php foreach (($u['tareas_entregables'] ?? []) as $te):
        $teEntregada = isset($tareasEntregablesEntregadas[$te['id']]);
        $esActiva    = $te['id'] == ($tareaActivaId ?? 0);
      ?>
      <div class="t-leccion-row t-tarea-row <?= $teEntregada ? 't-tarea-entregada' : '' ?>">
        <div class="tl-te-icon"><?= $teEntregada ? '✅' : '📝' ?></div>
        <a href="<?= BASE_URL ?>/index.php?url=tarea-entregable&id=<?= $te['id'] ?>"
           class="t-leccion <?= $esActiva ? 'activa' : '' ?> <?= $teEntregada ? 'vista' : '' ?>">
          <span><?= htmlspecialchars($te['titulo'] ?? 'Tarea') ?></span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php if ($tieneExamen): ?>
    <div style="border-top:1px solid var(--mc-border);padding:.85rem 1.1rem;background:var(--mc-soft);">
      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--mc-muted);margin-bottom:.6rem;">Evaluación final</div>
      <?php
      $testAprobado = !empty($resultadoExamenTest['aprobado']);
      $testHecho    = $resultadoExamenTest !== null;
      ?>
      <a href="<?= BASE_URL ?>/index.php?url=examen&curso=<?= $cursoId ?>"
         style="display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border-radius:9px;text-decoration:none;font-size:.83rem;font-weight:600;margin-bottom:.4rem;
                background:<?= $testAprobado ? '#f0fdf4' : ($testHecho ? '#fff7ed' : '#fff') ?>;
                color:<?= $testAprobado ? '#166534' : ($testHecho ? '#7c2d12' : 'var(--mc-dark)') ?>;
                border:1.5px solid <?= $testAprobado ? '#86efac' : ($testHecho ? '#fed7aa' : 'var(--mc-border)') ?>;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h3m-3 4h3m-6-4h.01m0 4h.01"/></svg>
        Examen tipo test
        <?php if ($testAprobado): ?>
          <span style="margin-left:auto;font-size:.7rem;background:#dcfce7;color:#166534;border-radius:99px;padding:1px 7px"><?= number_format((float)$resultadoExamenTest['nota'],1) ?>/10</span>
        <?php elseif ($testHecho): ?>
          <span style="margin-left:auto;font-size:.7rem;background:#fef3c7;color:#92400e;border-radius:99px;padding:1px 7px">Repetir</span>
        <?php endif; ?>
      </a>
      <?php if (!empty($tieneExamenPractico)): ?>
      <a href="<?= BASE_URL ?>/index.php?url=examen-practico&curso=<?= $cursoId ?>"
         style="display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border-radius:9px;text-decoration:none;font-size:.83rem;font-weight:600;background:#fff;color:var(--mc-dark);border:1.5px solid var(--mc-border);">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        Examen práctico
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </aside>

</div><!-- /te-layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL   = '<?= BASE_URL ?>';
const LECCION_ID = <?= $apiLeccionId ?>; // endpoint para marcar lecciones desde el sidebar

function mcToast(msg, type = 'default', duration = 3500) {
    const wrap  = document.getElementById('mcToastWrap');
    const toast = document.createElement('div');
    toast.className = 'mc-toast' + (type !== 'default' ? ' ' + type : '');
    toast.textContent = msg;
    wrap.appendChild(toast);
    requestAnimationFrame(() => { requestAnimationFrame(() => toast.classList.add('show')); });
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 250); }, duration);
}

/* ── Sidebar acordeón ── */
document.querySelectorAll('.t-unidad-btn').forEach(btn => {
    const lista = document.getElementById(btn.dataset.target);
    if (lista && !lista.classList.contains('cerrado')) {
        lista.style.maxHeight = lista.scrollHeight + 'px';
    }
    btn.addEventListener('click', () => {
        if (!lista) return;
        const abierto = !lista.classList.contains('cerrado');
        if (abierto) {
            lista.style.maxHeight = lista.scrollHeight + 'px';
            requestAnimationFrame(() => {
                lista.style.maxHeight = '0';
                lista.classList.add('cerrado');
                btn.classList.add('collapsed');
                btn.setAttribute('aria-expanded', 'false');
            });
        } else {
            lista.classList.remove('cerrado');
            lista.style.maxHeight = lista.scrollHeight + 'px';
            btn.classList.remove('collapsed');
            btn.setAttribute('aria-expanded', 'true');
            lista.addEventListener('transitionend', () => { lista.style.maxHeight = 'none'; }, { once: true });
        }
    });
});

/* ── Marcar/desmarcar lección individual ── */
async function toggleLeccion(checkbox, leccionId) {
    if (!LECCION_ID) return;
    const marcado = checkbox.checked;
    const fd = new FormData();
    fd.append('accion', marcado ? 'marcar_vista' : 'desmarcar_vista');
    fd.append('leccion_id', leccionId);
    try {
        await fetch(BASE_URL + '/index.php?url=leccion&id=' + LECCION_ID, { method: 'POST', body: fd });
        const link = checkbox.nextElementSibling;
        if (link) { marcado ? link.classList.add('vista') : link.classList.remove('vista'); }
        actualizarProgresoUnidad(checkbox);
    } catch(e) {
        checkbox.checked = !marcado;
    }
}

/* ── Marcar/desmarcar todas las lecciones de una unidad ── */
async function toggleUnidadAll(checkbox) {
    if (!LECCION_ID) return;
    const unidadId = checkbox.dataset.unidad;
    const listaId  = checkbox.dataset.lista;
    const marcado  = checkbox.checked;
    const fd = new FormData();
    fd.append('accion', marcado ? 'marcar_unidad' : 'desmarcar_unidad');
    fd.append('unidad_id', unidadId);
    try {
        await fetch(BASE_URL + '/index.php?url=leccion&id=' + LECCION_ID, { method: 'POST', body: fd });
        const lista = document.getElementById(listaId);
        if (!lista) return;
        lista.querySelectorAll('.lec-check').forEach(cb => {
            cb.checked = marcado;
            const link = cb.nextElementSibling;
            if (link) { marcado ? link.classList.add('vista') : link.classList.remove('vista'); }
        });
        const prog = document.getElementById('prog-' + listaId);
        if (prog) {
            const total = lista.querySelectorAll('.lec-check').length;
            prog.textContent = (marcado ? total : 0) + '/' + total;
        }
    } catch(e) { checkbox.checked = !marcado; }
}

function actualizarProgresoUnidad(anyCheckbox) {
    const lista = anyCheckbox.closest('.t-lecciones-list');
    if (!lista) return;
    const todas  = lista.querySelectorAll('.lec-check');
    const vistas = [...todas].filter(c => c.checked).length;
    const prog = document.getElementById('prog-' + lista.id);
    if (prog) prog.textContent = vistas + '/' + todas.length;
    const uCheck = document.querySelector(`[data-lista="${lista.id}"] .u-check-all`);
    if (uCheck) uCheck.checked = vistas === todas.length && todas.length > 0;
}

/* ── Enunciado colapsable ── */
function toggleDesc() {
    const box = document.getElementById('desc-box');
    const btn = document.getElementById('btn-desc');
    const col = box.classList.toggle('collapsed');
    btn.textContent = col ? 'Mostrar más ▼' : 'Mostrar menos ▲';
}

/* ── Paneles ver/modificar entrega ── */
function togglePanel(id) {
    const panel = document.getElementById(id);
    if (!panel) return;
    const visible = panel.style.display !== 'none';
    // Ocultar todos los paneles
    ['panel-ver','panel-mod'].forEach(pid => {
        const p = document.getElementById(pid);
        if (p) p.style.display = 'none';
    });
    if (!visible) panel.style.display = 'block';
}

/* ── Formulario entrega ── */
function showFile(input) {
    const el = document.getElementById('fname-te');
    if (input.files[0]) { el.textContent = '📎 ' + input.files[0].name; el.style.display = 'block'; }
    else el.style.display = 'none';
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('drop-te').classList.remove('drag-over');
    const f = e.dataTransfer.files[0];
    if (!f) return;
    const input = document.getElementById('file-te');
    const dt = new DataTransfer(); dt.items.add(f); input.files = dt.files;
    showFile(input);
}
async function entregar() {
    const btn  = document.getElementById('btn-entregar');
    const txt  = document.getElementById('txt-te')?.value || '';
    const file = document.getElementById('file-te')?.files[0];
    if (!txt.trim() && !file) { mcToast('Escribe una respuesta o adjunta un archivo.', 'error'); return; }
    btn.disabled = true; btn.textContent = 'Enviando…';
    const fd = new FormData();
    fd.append('respuesta', txt);
    if (file) fd.append('archivo', file);
    try {
        const res = await fetch(window.location.href, { method: 'POST', body: fd }).then(r => r.json());
        if (res.ok) { mcToast('Entrega guardada correctamente', 'success'); setTimeout(() => window.location.reload(), 1200); }
        else { mcToast(res.error || 'Error al enviar.', 'error'); btn.disabled = false; btn.textContent = 'Enviar tarea →'; }
    } catch { mcToast('Error de conexión.', 'error'); btn.disabled = false; btn.textContent = 'Enviar tarea →'; }
}
</script>
</body>
</html>
