<?php
$tiposConfig = [
    'tarea'             => ['label'=>'Tarea',          'color'=>'#d97706','bg'=>'#fffbeb','border'=>'#fde68a','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    'tarea_vencida'     => ['label'=>'Vencida',        'color'=>'#dc2626','bg'=>'#fef2f2','border'=>'#fecaca','icon'=>'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    'expiracion'        => ['label'=>'Expiración',     'color'=>'#ea580c','bg'=>'#fff7ed','border'=>'#fed7aa','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    'evento_calendario' => ['label'=>'Evento',         'color'=>'#059669','bg'=>'#f0fdf4','border'=>'#bbf7d0','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    'crm'               => ['label'=>'Campaña',        'color'=>'#7c3aed','bg'=>'#f5f3ff','border'=>'#ddd6fe','icon'=>'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
    'mensaje'           => ['label'=>'Mensaje',        'color'=>'#2563eb','bg'=>'#eff6ff','border'=>'#bfdbfe','icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    'info'              => ['label'=>'Info',           'color'=>'#6b7280','bg'=>'#f9fafb','border'=>'#e5e7eb','icon'=>'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];

function notifTimeAgo(?string $str): string {
    if (!$str) return '';
    $diff = time() - strtotime($str);
    if ($diff < 60)     return 'ahora mismo';
    if ($diff < 3600)   return 'hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)  return 'hace ' . floor($diff/3600) . ' h';
    if ($diff < 604800) return 'hace ' . floor($diff/86400) . ' días';
    return date('d/m/Y', strtotime($str));
}

function notifDateGroup(?string $str): string {
    if (!$str) return 'Sin fecha';
    $ts   = strtotime($str);
    $diff = (int)floor((mktime(0,0,0) - mktime(0,0,0,date('n',$ts),date('j',$ts),date('Y',$ts))) / 86400);
    if ($diff <= 0)  return 'Hoy';
    if ($diff === 1) return 'Ayer';
    if ($diff < 7)   return 'Esta semana';
    if ($diff < 30)  return 'Este mes';
    return date('F Y', $ts);
}

function buildNotifUrl(array $overrides = []): string {
    global $filtroTipo, $page;
    $p = ['url'=>'notificaciones','tipo'=>$overrides['tipo']??$filtroTipo,'p'=>$overrides['p']??$page];
    $p = array_filter($p, fn($v)=>$v!==''&&$v!==null);
    if (isset($p['p'])&&(int)$p['p']===1) unset($p['p']);
    return BASE_URL.'/index.php?'.http_build_query($p);
}

$grouped = [];
foreach ($notificaciones as $n) {
    $grouped[notifDateGroup($n['creado_en'])][] = $n;
}

// Conteo total por tipo (sobre TODAS las notificaciones, no solo la página)
/** @var PDO $db */ /** @var int $usuario_id */
$cntPorTipo = [];
$rowsCnt = $db->prepare("SELECT tipo, COUNT(*) AS c FROM notificacion WHERE usuario_id = ? GROUP BY tipo");
$rowsCnt->execute([$usuario_id]);
foreach ($rowsCnt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $cntPorTipo[$r['tipo']] = (int)$r['c'];
}
$totalTodas = array_sum($cntPorTipo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — MatrixCoders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
<style>
*,*::before,*::after{box-sizing:border-box}
html,body{height:100%}
body{font-family:'Saira',sans-serif;background:#f0f2f5;margin:0;color:#1B2336;display:flex;flex-direction:column;min-height:100vh}
body > header{flex-shrink:0}
.notif-page-wrap{flex:1;padding:32px 20px 48px}
footer{flex-shrink:0;margin-top:auto}

.notif-layout{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:260px 1fr;gap:24px;align-items:start}
@media(max-width:820px){.notif-layout{grid-template-columns:1fr}.notif-sidebar{display:none}}

/* ── SIDEBAR ── */
.notif-sidebar{position:sticky;top:82px}
.sidebar-card{background:#fff;border-radius:16px;border:1px solid #e8eaed;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05)}

.sidebar-head{padding:16px 18px 12px;border-bottom:1px solid #f0f2f5}
.sidebar-head h3{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin:0}

.sidebar-item{display:flex;align-items:center;gap:10px;padding:11px 18px;text-decoration:none;color:#374151;font-size:.855rem;font-weight:600;transition:background .13s;border-bottom:1px solid #f7f8fa;position:relative}
.sidebar-item:last-child{border-bottom:none}
.sidebar-item:hover{background:#fafbfc}
.sidebar-item.active{background:#f5f3ff;color:#6d28d9}
.sidebar-item.active::before{content:'';position:absolute;left:0;top:20%;height:60%;width:3px;background:#6d28d9;border-radius:0 3px 3px 0}

.sidebar-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:transform .15s}
.sidebar-item:hover .sidebar-icon{transform:scale(1.08)}

.sidebar-label{flex:1}
.sidebar-cnt{font-size:.72rem;font-weight:800;background:#f0f2f5;color:#6b7280;border-radius:99px;padding:2px 8px;min-width:22px;text-align:center}
.sidebar-item.active .sidebar-cnt{background:#ede9fe;color:#6d28d9}

.sidebar-actions{padding:14px 18px;border-top:1px solid #f0f2f5}
.btn-mark-all{width:100%;display:flex;align-items:center;justify-content:center;gap:7px;background:#6d28d9;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-size:.82rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;transition:background .15s,transform .1s}
.btn-mark-all:hover{background:#5b21b6;transform:translateY(-1px)}
.btn-mark-all:active{transform:translateY(0)}
.btn-mark-all svg{flex-shrink:0}

/* ── MAIN ── */
.notif-main-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap}
.notif-page-title{font-size:1.5rem;font-weight:900;color:#1B2336;margin:0;display:flex;align-items:center;gap:10px}
.notif-new-badge{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:.68rem;font-weight:800;border-radius:99px;padding:3px 10px;box-shadow:0 3px 10px rgba(239,68,68,.35)}

.notif-stats{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}
.stat-pill{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:99px;font-size:.78rem;font-weight:700;border:1.5px solid transparent;background:#fff}
.stat-pill.total{border-color:#e5e7eb;color:#6b7280}
.stat-pill.unread{background:#fef2f2;border-color:#fecaca;color:#dc2626}
.stat-pill.filter{border-color:var(--tc-border,#e5e7eb);background:var(--tc-bg,#fff);color:var(--tc-color,#374151)}

/* ── DATE GROUP ── */
.notif-group{margin-bottom:28px}
.notif-group-label{display:flex;align-items:center;gap:10px;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:#9ca3af;margin-bottom:12px;padding:0 2px}
.notif-group-label::after{content:'';flex:1;height:1px;background:#e8eaed}

/* ── CARD ── */
.notif-card{display:flex;gap:16px;align-items:flex-start;background:#fff;border:1.5px solid #e8eaed;border-radius:16px;padding:18px 20px;margin-bottom:10px;transition:box-shadow .18s,transform .18s,border-color .18s;position:relative;overflow:hidden}
.notif-card:hover{box-shadow:0 6px 28px rgba(0,0,0,.08);transform:translateY(-1px);border-color:#d1d5db}
.notif-card.unread{border-left:3px solid var(--tc,#6d28d9)}
.notif-card.unread::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(90deg,rgba(109,40,217,.03) 0%,transparent 50%);pointer-events:none}
.notif-card.collapsing{opacity:0;transform:translateX(16px) scaleY(.9);transition:opacity .22s,transform .22s}

.notif-icon-wrap{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--tc-border,#e8eaed)}
.notif-icon-wrap svg{flex-shrink:0}

.notif-body{flex:1;min-width:0}
.notif-title{font-size:.92rem;font-weight:700;color:#111827;margin:0 0 5px;line-height:1.4}
.notif-card:not(.unread) .notif-title{color:#374151;font-weight:600}
.notif-sub{font-size:.82rem;color:#4b5563;margin:0 0 10px;line-height:1.6;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.notif-footer-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.notif-type-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;font-size:.68rem;font-weight:800;border:1px solid transparent;text-transform:uppercase;letter-spacing:.2px}
.notif-time{font-size:.72rem;color:#9ca3af;display:flex;align-items:center;gap:3px}
.notif-read-check{font-size:.72rem;color:#10b981;font-weight:700;display:flex;align-items:center;gap:3px}

.notif-actions{display:flex;flex-direction:column;gap:6px;flex-shrink:0;align-items:flex-end}
.notif-btn{display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:8px;font-size:.75rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;text-decoration:none;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;transition:all .15s;white-space:nowrap;line-height:1}
.notif-btn:hover{background:#f9fafb;border-color:#d1d5db;color:#374151}
.notif-btn.primary{background:var(--tc-bg,#f5f3ff);border-color:var(--tc-border,#ddd6fe);color:var(--tc-color,#6d28d9)}
.notif-btn.primary:hover{filter:brightness(.96)}
.notif-btn.ghost{background:transparent;border-color:transparent;color:#9ca3af;padding:6px 8px}
.notif-btn.ghost:hover{background:#f3f4f6;color:#374151;border-color:transparent}

/* ── EMPTY ── */
.notif-empty{text-align:center;padding:72px 20px;background:#fff;border-radius:16px;border:1.5px dashed #e5e7eb}
.notif-empty-icon{width:72px;height:72px;border-radius:20px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
.notif-empty h3{font-size:1.1rem;font-weight:800;color:#1B2336;margin:0 0 8px}
.notif-empty p{font-size:.875rem;color:#6b7280;margin:0}

/* ── PAG ── */
.notif-pag{display:flex;justify-content:center;align-items:center;gap:5px;margin-top:28px;flex-wrap:wrap}
.pag-btn{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:9px;font-size:.83rem;font-weight:700;border:1.5px solid #e5e7eb;background:#fff;color:#374151;text-decoration:none;transition:all .15s}
.pag-btn:hover:not(.pag-dis):not(.pag-act){border-color:#c4b5fd;color:#6d28d9;background:#f5f3ff}
.pag-btn.pag-act{background:#6d28d9;border-color:#6d28d9;color:#fff;box-shadow:0 4px 14px rgba(109,40,217,.3)}
.pag-btn.pag-dis{opacity:.35;pointer-events:none}
.pag-info{font-size:.75rem;color:#9ca3af;margin-left:6px}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="notif-page-wrap">
<div class="notif-layout">

  <!-- ── SIDEBAR ── -->
  <aside class="notif-sidebar">
    <div class="sidebar-card">
      <div class="sidebar-head"><h3>Filtrar</h3></div>

      <a href="<?= buildNotifUrl(['tipo'=>'','p'=>1]) ?>" class="sidebar-item <?= $filtroTipo==='' ? 'active' : '' ?>">
        <span class="sidebar-icon" style="background:#f0f2f5">
          <svg width="16" height="16" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
        </span>
        <span class="sidebar-label">Todas</span>
        <span class="sidebar-cnt"><?= $totalTodas ?></span>
      </a>

      <?php foreach ($tiposConfig as $tKey => $tc): ?>
      <a href="<?= buildNotifUrl(['tipo'=>$tKey,'p'=>1]) ?>" class="sidebar-item <?= $filtroTipo===$tKey ? 'active' : '' ?>">
        <span class="sidebar-icon" style="background:<?= $tc['bg'] ?>;border:1px solid <?= $tc['border'] ?>">
          <svg width="15" height="15" fill="none" stroke="<?= $tc['color'] ?>" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $tc['icon'] ?>"/></svg>
        </span>
        <span class="sidebar-label"><?= $tc['label'] ?></span>
        <?php if (!empty($cntPorTipo[$tKey])): ?>
        <span class="sidebar-cnt"><?= $cntPorTipo[$tKey] ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>

      <?php if ($noLeidas > 0): ?>
      <div class="sidebar-actions">
        <form method="POST" action="<?= BASE_URL ?>/index.php?url=notificaciones">
          <input type="hidden" name="accion" value="marcar-todas">
          <?php if ($filtroTipo): ?><input type="hidden" name="tipo" value="<?= htmlspecialchars($filtroTipo) ?>"><?php endif; ?>
          <button type="submit" class="btn-mark-all">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
            Marcar todo como leído
          </button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <main>
    <div class="notif-main-header">
      <div>
        <h1 class="notif-page-title">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
          Notificaciones
          <?php if ($noLeidas > 0): ?><span class="notif-new-badge"><?= $noLeidas ?> nueva<?= $noLeidas!==1?'s':'' ?></span><?php endif; ?>
        </h1>
      </div>
    </div>

    <?php if (!empty($notificaciones)): ?>
    <div class="notif-stats">
      <span class="stat-pill total">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
        <?= $totalRows ?> en total
      </span>
      <?php if ($noLeidas > 0): ?>
      <span class="stat-pill unread">
        <svg width="11" height="11" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/></svg>
        <?= $noLeidas ?> sin leer
      </span>
      <?php endif; ?>
      <?php if ($filtroTipo && isset($tiposConfig[$filtroTipo])): $tc=$tiposConfig[$filtroTipo]; ?>
      <span class="stat-pill filter" style="--tc-bg:<?= $tc['bg'] ?>;--tc-border:<?= $tc['border'] ?>;--tc-color:<?= $tc['color'] ?>">
        <?= $tc['label'] ?>
      </span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($notificaciones)): ?>
    <div class="notif-empty">
      <div class="notif-empty-icon">
        <svg width="32" height="32" fill="none" stroke="#6d28d9" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
      </div>
      <h3>Sin notificaciones<?= $filtroTipo?' de este tipo':'' ?></h3>
      <p>Cuando tengas actividad nueva aparecerá aquí.</p>
    </div>

    <?php else: ?>
    <?php foreach ($grouped as $grupLabel => $items): ?>
    <div class="notif-group">
      <div class="notif-group-label"><?= htmlspecialchars($grupLabel) ?></div>

      <?php foreach ($items as $n):
        $tc      = $tiposConfig[$n['tipo']] ?? $tiposConfig['info'];
        $esLeida = (bool)$n['leido'];
        $leerUrl = BASE_URL . '/index.php?url=notificaciones&leer=' . $n['id']
                 . ($n['url_accion'] ? '&goto=' . urlencode($n['url_accion']) : '')
                 . ($filtroTipo ? '&tipo=' . urlencode($filtroTipo) : '')
                 . '&p=' . $page;
      ?>
      <div class="notif-card <?= $esLeida?'':'unread' ?>"
           id="ncard-<?= $n['id'] ?>"
           style="--tc:<?= $tc['color'] ?>;--tc-bg:<?= $tc['bg'] ?>;--tc-border:<?= $tc['border'] ?>;--tc-color:<?= $tc['color'] ?>">

        <div class="notif-icon-wrap" style="background:<?= $tc['bg'] ?>;border-color:<?= $tc['border'] ?>">
          <svg width="20" height="20" fill="none" stroke="<?= $tc['color'] ?>" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $tc['icon'] ?>"/>
          </svg>
        </div>

        <div class="notif-body">
          <p class="notif-title"><?= htmlspecialchars($n['titulo']) ?></p>
          <?php if (!empty($n['cuerpo'])): ?>
          <p class="notif-sub"><?= htmlspecialchars($n['cuerpo']) ?></p>
          <?php endif; ?>
          <div class="notif-footer-row">
            <span class="notif-type-chip" style="background:<?= $tc['bg'] ?>;color:<?= $tc['color'] ?>;border-color:<?= $tc['border'] ?>"><?= $tc['label'] ?></span>
            <span class="notif-time">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
              <?= notifTimeAgo($n['creado_en']) ?>
            </span>
            <?php if ($esLeida): ?>
            <span class="notif-read-check">
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
              Leída
            </span>
            <?php endif; ?>
          </div>
        </div>

        <div class="notif-actions">
          <?php if ($n['url_accion']): ?>
          <a href="<?= htmlspecialchars($leerUrl) ?>" class="notif-btn primary">
            Ver
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
          </a>
          <?php endif; ?>
          <?php if (!$esLeida): ?>
          <button class="notif-btn ghost" onclick="marcarLeida(<?= $n['id'] ?>, this)" type="button" title="Marcar como leída">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php if ($totalPags > 1): ?>
    <div class="notif-pag">
      <a href="<?= buildNotifUrl(['p'=>max(1,$page-1)]) ?>" class="pag-btn pag-arrow <?= $page<=1?'pag-dis':'' ?>">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
      <?php
      $pi = max(1,$page-2); $pf = min($totalPags,$page+2);
      if ($pi>1): ?><a href="<?= buildNotifUrl(['p'=>1]) ?>" class="pag-btn">1</a><?php if($pi>2): ?><span style="padding:0 4px;color:#9ca3af">…</span><?php endif; endif; ?>
      <?php for($i=$pi;$i<=$pf;$i++): ?>
      <a href="<?= buildNotifUrl(['p'=>$i]) ?>" class="pag-btn <?= $i===$page?'pag-act':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if($pf<$totalPags): ?><?php if($pf<$totalPags-1): ?><span style="padding:0 4px;color:#9ca3af">…</span><?php endif; ?><a href="<?= buildNotifUrl(['p'=>$totalPags]) ?>" class="pag-btn"><?= $totalPags ?></a><?php endif; ?>
      <a href="<?= buildNotifUrl(['p'=>min($totalPags,$page+1)]) ?>" class="pag-btn pag-arrow <?= $page>=$totalPags?'pag-dis':'' ?>">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
      <span class="pag-info">Página <?= $page ?> de <?= $totalPags ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </main>

</div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function marcarLeida(id, btn) {
  btn.disabled = true;
  try {
    await fetch('<?= BASE_URL ?>/index.php?url=api-notificaciones', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`action=leer&id=${id}`
    });
    const card = document.getElementById('ncard-' + id);
    if (card) {
      card.classList.add('collapsing');
      setTimeout(() => {
        const h = card.offsetHeight;
        card.style.height = h + 'px';
        card.style.overflow = 'hidden';
        requestAnimationFrame(() => {
          card.style.transition = 'height .22s ease, margin .22s ease, padding .22s ease, opacity .22s ease';
          card.style.height = '0';
          card.style.marginBottom = '0';
          card.style.paddingTop = '0';
          card.style.paddingBottom = '0';
        });
        setTimeout(() => card.remove(), 230);
      }, 180);
    }
  } catch(e) { btn.disabled = false; }
}
</script>
</body>
</html>
