<?php
$tiposConfig = [
    'tarea'      => ['label'=>'Tarea',     'color'=>'#f59e0b','bg'=>'#fffbeb','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    'expiracion' => ['label'=>'Expiración','color'=>'#ef4444','bg'=>'#fef2f2','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    'crm'        => ['label'=>'Campaña',   'color'=>'#7c3aed','bg'=>'#f5f3ff','icon'=>'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
    'mensaje'    => ['label'=>'Mensaje',   'color'=>'#3b82f6','bg'=>'#eff6ff','icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
    'info'       => ['label'=>'Info',      'color'=>'#6b7280','bg'=>'#f9fafb','icon'=>'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];

function notifTimeAgo(?string $str): string {
    if (!$str) return '';
    $diff = time() - strtotime($str);
    if ($diff < 60)     return 'ahora mismo';
    if ($diff < 3600)   return 'hace '.floor($diff/60).'m';
    if ($diff < 86400)  return 'hace '.floor($diff/3600).'h';
    if ($diff < 604800) return 'hace '.floor($diff/86400).'d';
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

// Group by date
$grouped = [];
foreach ($notificaciones as $n) {
    $grp = notifDateGroup($n['creado_en']);
    $grouped[$grp][] = $n;
}

// Count per type
$cntPorTipo = [];
foreach ($notificaciones as $n) {
    $cntPorTipo[$n['tipo']] = ($cntPorTipo[$n['tipo']] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — MatrixCoders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
<style>
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Saira',sans-serif;background:#f1f5f9;margin:0;color:#1B2336}

.notif-layout{max-width:1080px;margin:0 auto;padding:36px 20px 60px;display:grid;grid-template-columns:220px 1fr;gap:24px;}
@media(max-width:800px){.notif-layout{grid-template-columns:1fr;}.notif-sidebar{display:none;}}

/* Sidebar */
.notif-sidebar{position:sticky;top:84px;align-self:start;}
.notif-sidebar-card{background:#fff;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden;}
.notif-sidebar-head{padding:14px 16px 10px;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#9ca3af;border-bottom:1px solid #f0f0f0;}
.notif-sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 16px;text-decoration:none;color:#374151;font-size:.85rem;font-weight:600;transition:background .13s;border-bottom:1px solid #f7f7f7;}
.notif-sidebar-link:last-child{border-bottom:none;}
.notif-sidebar-link:hover{background:#f5f5f5;}
.notif-sidebar-link.active{background:#f5f3ff;color:#7c3aed;}
.notif-sidebar-link .dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
.notif-sidebar-link .badge{margin-left:auto;background:#f3f4f6;color:#6b7280;font-size:.68rem;font-weight:700;border-radius:99px;padding:1px 7px;}
.notif-sidebar-link.active .badge{background:#ede9fe;color:#7c3aed;}

/* Summary pills */
.notif-summary{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;}
.notif-pill{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:99px;font-size:.78rem;font-weight:700;background:#fff;border:1.5px solid #e5e7eb;color:#374151;}
.notif-pill.unread{background:#fef3c7;border-color:#fde68a;color:#92400e;}

/* Header */
.notif-main-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap;}
.notif-main-title{font-size:1.45rem;font-weight:900;color:#1B2336;margin:0;display:flex;align-items:center;gap:10px;}
.notif-main-title .new-badge{background:#ef4444;color:#fff;font-size:.68rem;font-weight:800;border-radius:99px;padding:2px 8px;}
.btn-mark-all{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;padding:8px 14px;font-size:.82rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;color:#374151;transition:all .15s;}
.btn-mark-all:hover{border-color:#7c3aed;color:#7c3aed;}

/* Date group */
.notif-date-group{margin-bottom:20px;}
.notif-group-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#9ca3af;margin-bottom:8px;padding:0 2px;}

/* Cards */
.notif-card{display:flex;gap:13px;align-items:flex-start;background:#fff;border:1.5px solid #e5e7eb;border-radius:13px;padding:14px 16px;margin-bottom:7px;transition:box-shadow .15s,border-color .15s;position:relative;overflow:hidden;}
.notif-card:hover{box-shadow:0 4px 18px rgba(0,0,0,.07);border-color:#d1d5db;}
.notif-card.unread{border-left:3px solid var(--tip,#7c3aed);background:linear-gradient(to right,rgba(124,58,237,.02),transparent 40%);}
.notif-card.dismissed{opacity:0;transform:translateX(18px);transition:opacity .25s,transform .25s;pointer-events:none;}
.notif-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.notif-body{flex:1;min-width:0;}
.notif-title{font-size:.9rem;font-weight:700;color:#1B2336;margin:0 0 3px;line-height:1.4;}
.notif-card:not(.unread) .notif-title{font-weight:600;}
.notif-sub{font-size:.8rem;color:#6b7280;margin:0 0 7px;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.notif-meta{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.notif-type-chip{display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:99px;font-size:.7rem;font-weight:700;}
.notif-time{font-size:.72rem;color:#9ca3af;}
.notif-read-mark{font-size:.75rem;color:#10b981;font-weight:700;}
.notif-actions{display:flex;flex-direction:column;gap:5px;flex-shrink:0;align-items:flex-end;}
.notif-btn{display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:7px;font-size:.75rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;text-decoration:none;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;transition:all .15s;white-space:nowrap;}
.notif-btn:hover{border-color:#7c3aed;color:#7c3aed;}
.notif-btn.action{background:#f5f3ff;border-color:#ddd6fe;color:#7c3aed;}
.notif-btn.action:hover{background:#ede9fe;}

/* Empty */
.notif-empty{text-align:center;padding:60px 20px;color:#9ca3af;}
.notif-empty svg{opacity:.22;display:block;margin:0 auto 18px;}
.notif-empty h3{font-size:1.05rem;font-weight:700;color:#374151;margin:0 0 6px;}
.notif-empty p{font-size:.86rem;margin:0;}

/* Pagination */
.notif-pag{display:flex;justify-content:center;gap:5px;margin-top:24px;}
.notif-pag-btn{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border-radius:8px;font-size:.83rem;font-weight:700;border:1.5px solid #e5e7eb;background:#fff;color:#374151;text-decoration:none;transition:all .15s;}
.notif-pag-btn:hover{border-color:#7c3aed;color:#7c3aed;}
.notif-pag-btn.active{background:#7c3aed;color:#fff;border-color:#7c3aed;}
.notif-pag-btn.disabled{opacity:.35;pointer-events:none;}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="notif-layout">

  <!-- Sidebar -->
  <aside class="notif-sidebar">
    <div class="notif-sidebar-card">
      <div class="notif-sidebar-head">Filtrar por tipo</div>
      <a href="<?= buildNotifUrl(['tipo'=>'','p'=>1]) ?>" class="notif-sidebar-link <?= $filtroTipo===''?'active':'' ?>">
        <span class="dot" style="background:#1B2336"></span>
        Todas
        <span class="badge"><?= $totalRows ?></span>
      </a>
      <?php foreach ($tiposConfig as $tKey => $tc): ?>
      <a href="<?= buildNotifUrl(['tipo'=>$tKey,'p'=>1]) ?>" class="notif-sidebar-link <?= $filtroTipo===$tKey?'active':'' ?>">
        <span class="dot" style="background:<?= $tc['color'] ?>"></span>
        <?= $tc['label'] ?>
        <?php if (!empty($cntPorTipo[$tKey])): ?>
        <span class="badge"><?= $cntPorTipo[$tKey] ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($noLeidas > 0): ?>
    <div style="margin-top:12px">
      <form method="POST" action="<?= BASE_URL ?>/index.php?url=notificaciones">
        <input type="hidden" name="accion" value="marcar-todas">
        <?php if ($filtroTipo): ?><input type="hidden" name="tipo" value="<?= htmlspecialchars($filtroTipo) ?>"><?php endif; ?>
        <button type="submit" class="btn-mark-all" style="width:100%;justify-content:center">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
          Marcar todas leídas
        </button>
      </form>
    </div>
    <?php endif; ?>
  </aside>

  <!-- Main -->
  <main>
    <div class="notif-main-head">
      <h1 class="notif-main-title">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
        Notificaciones
        <?php if ($noLeidas > 0): ?><span class="new-badge"><?= $noLeidas ?> nuevas</span><?php endif; ?>
      </h1>
    </div>

    <?php if (!empty($notificaciones)): ?>
    <div class="notif-summary">
      <span class="notif-pill"><?= $totalRows ?> en total</span>
      <?php if ($noLeidas > 0): ?>
      <span class="notif-pill unread"><?= $noLeidas ?> sin leer</span>
      <?php endif; ?>
      <?php if ($filtroTipo && isset($tiposConfig[$filtroTipo])): ?>
      <span class="notif-pill" style="background:<?= $tiposConfig[$filtroTipo]['bg'] ?>;border-color:<?= $tiposConfig[$filtroTipo]['color'] ?>22;color:<?= $tiposConfig[$filtroTipo]['color'] ?>">
        <?= $tiposConfig[$filtroTipo]['label'] ?>
      </span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($notificaciones)): ?>
    <div class="notif-empty">
      <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/></svg>
      <h3>Sin notificaciones</h3>
      <p>No hay notificaciones<?= $filtroTipo?' de este tipo':'' ?> por el momento.</p>
    </div>

    <?php else: ?>
    <?php foreach ($grouped as $grupLabel => $items): ?>
    <div class="notif-date-group">
      <div class="notif-group-label"><?= htmlspecialchars($grupLabel) ?></div>

      <?php foreach ($items as $n):
        $tc      = $tiposConfig[$n['tipo']] ?? $tiposConfig['info'];
        $esLeida = (bool)$n['leido'];
        $leerUrl = BASE_URL . '/index.php?url=notificaciones&leer=' . $n['id']
                   . ($n['url_accion'] ? '&goto='.urlencode($n['url_accion']) : '')
                   . ($filtroTipo ? '&tipo='.urlencode($filtroTipo) : '')
                   . '&p=' . $page;
      ?>
      <div class="notif-card <?= $esLeida?'':'unread' ?>"
           id="ncard-<?= $n['id'] ?>"
           style="<?= !$esLeida ? '--tip:'.$tc['color'] : '' ?>">

        <div class="notif-icon" style="background:<?= $tc['bg'] ?>">
          <svg width="18" height="18" fill="none" stroke="<?= $tc['color'] ?>" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $tc['icon'] ?>"/>
          </svg>
        </div>

        <div class="notif-body">
          <p class="notif-title"><?= htmlspecialchars($n['titulo']) ?></p>
          <?php if (!empty($n['cuerpo'])): ?>
          <p class="notif-sub"><?= htmlspecialchars($n['cuerpo']) ?></p>
          <?php endif; ?>
          <div class="notif-meta">
            <span class="notif-type-chip" style="background:<?= $tc['bg'] ?>;color:<?= $tc['color'] ?>"><?= $tc['label'] ?></span>
            <span class="notif-time"><?= notifTimeAgo($n['creado_en']) ?></span>
            <?php if ($esLeida): ?><span class="notif-read-mark">✓ Leída</span><?php endif; ?>
          </div>
        </div>

        <div class="notif-actions">
          <?php if ($n['url_accion']): ?>
          <a href="<?= htmlspecialchars($leerUrl) ?>" class="notif-btn action">Ver →</a>
          <?php endif; ?>
          <?php if (!$esLeida): ?>
          <button class="notif-btn" onclick="marcarLeida(<?= $n['id'] ?>, this)" type="button">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
            Leída
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php if ($totalPags > 1): ?>
    <div class="notif-pag">
      <a href="<?= buildNotifUrl(['p'=>max(1,$page-1)]) ?>" class="notif-pag-btn <?= $page<=1?'disabled':'' ?>">‹</a>
      <?php for ($i=max(1,$page-2); $i<=min($totalPags,$page+2); $i++): ?>
      <a href="<?= buildNotifUrl(['p'=>$i]) ?>" class="notif-pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a href="<?= buildNotifUrl(['p'=>min($totalPags,$page+1)]) ?>" class="notif-pag-btn <?= $page>=$totalPags?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </main>
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
      card.classList.add('dismissed');
      setTimeout(() => {
        card.style.height = card.offsetHeight + 'px';
        requestAnimationFrame(() => { card.style.height = '0'; card.style.padding = '0'; card.style.margin = '0'; card.style.overflow = 'hidden'; card.style.transition = 'height .25s,padding .25s,margin .25s'; });
        setTimeout(() => card.remove(), 280);
      }, 200);
    }
  } catch(e) { btn.disabled = false; }
}
</script>
</body>
</html>
