<?php
require_once __DIR__ . '/../../config.php';

function buzonTimeAgo(?string $str): string {
    if (!$str) return '';
    $diff = time() - strtotime($str);
    if ($diff < 60)     return 'ahora mismo';
    if ($diff < 3600)   return 'hace ' . floor($diff / 60) . 'm';
    if ($diff < 86400)  return 'hace ' . floor($diff / 3600) . 'h';
    if ($diff < 604800) return 'hace ' . floor($diff / 86400) . 'd';
    return date('d/m/Y', strtotime($str));
}

function buzonRolLabel(string $rol): array {
    return match($rol) {
        'ADMINISTRADOR' => ['Administrador', '#7c3aed', '#f5f3ff'],
        'EDITOR'        => ['Editor',        '#2563eb', '#dbeafe'],
        default         => ['Usuario',       '#6b7280', '#f3f4f6'],
    };
}

function buildBuzonUrl(array $overrides = []): string {
    global $page;
    $p = ['url' => 'buzon', 'p' => $overrides['p'] ?? $page];
    if (isset($overrides['msg'])) $p['msg'] = $overrides['msg'];
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null);
    if (isset($p['p']) && (int)$p['p'] === 1) unset($p['p']);
    return BASE_URL . '/index.php?' . http_build_query($p);
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

.buzon-layout{max-width:900px;margin:0 auto;padding:36px 20px 60px}

/* Header */
.buzon-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;gap:12px;flex-wrap:wrap}
.buzon-title{font-size:1.45rem;font-weight:900;color:#1B2336;margin:0;display:flex;align-items:center;gap:10px}
.buzon-badge-new{background:#3b82f6;color:#fff;font-size:.68rem;font-weight:800;border-radius:99px;padding:2px 8px}
.buzon-meta{font-size:.82rem;color:#6b7280;margin-top:4px}

/* Panel mensaje activo */
.buzon-msg-panel{background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;margin-bottom:20px;overflow:hidden}
.buzon-msg-panel-head{padding:18px 22px 14px;border-bottom:1px solid #f0f0f0;display:flex;align-items:flex-start;gap:14px}
.buzon-msg-avatar{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;flex-shrink:0}
.buzon-msg-meta h2{font-size:1rem;font-weight:800;color:#1B2336;margin:0 0 4px}
.buzon-msg-meta p{font-size:.82rem;color:#6b7280;margin:0;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.rol-chip{display:inline-flex;align-items:center;font-size:.7rem;font-weight:700;border-radius:99px;padding:2px 9px;border:1px solid transparent}
.buzon-msg-body{padding:20px 22px;font-size:.92rem;color:#374151;line-height:1.7;white-space:pre-wrap;word-break:break-word}
.buzon-msg-close{display:inline-flex;align-items:center;gap:5px;font-size:.8rem;font-weight:700;color:#6b7280;text-decoration:none;padding:7px 14px;border:1.5px solid #e5e7eb;border-radius:9px;transition:all .15s;margin:0 22px 18px}
.buzon-msg-close:hover{border-color:#3b82f6;color:#3b82f6}

/* Lista mensajes */
.buzon-list{display:flex;flex-direction:column;gap:7px}
.buzon-item{display:flex;align-items:flex-start;gap:13px;background:#fff;border:1.5px solid #e5e7eb;border-radius:13px;padding:14px 16px;transition:box-shadow .15s,border-color .15s;cursor:pointer;text-decoration:none;color:inherit;position:relative}
.buzon-item:hover{box-shadow:0 4px 18px rgba(0,0,0,.07);border-color:#d1d5db}
.buzon-item.unread{border-left:3px solid #3b82f6;background:linear-gradient(to right,rgba(59,130,246,.03),transparent 40%)}
.buzon-item.active-msg{border-color:#3b82f6;background:#eff6ff}
.buzon-avatar{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:800;flex-shrink:0}
.buzon-body{flex:1;min-width:0}
.buzon-item-top{display:flex;align-items:center;gap:8px;margin-bottom:3px;flex-wrap:wrap}
.buzon-emisor{font-size:.88rem;font-weight:700;color:#1B2336}
.buzon-asunto{font-size:.88rem;font-weight:700;color:#1B2336;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.buzon-item.unread .buzon-asunto{font-weight:800}
.buzon-preview{font-size:.78rem;color:#6b7280;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;margin-top:2px}
.buzon-side{display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0}
.buzon-time{font-size:.7rem;color:#9ca3af;white-space:nowrap}
.buzon-unread-dot{width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0}

/* Vacío */
.buzon-empty{text-align:center;padding:60px 20px;color:#9ca3af}
.buzon-empty svg{opacity:.2;display:block;margin:0 auto 18px}
.buzon-empty h3{font-size:1.05rem;font-weight:700;color:#374151;margin:0 0 6px}
.buzon-empty p{font-size:.86rem;margin:0}

/* Paginación */
.buzon-pag{display:flex;justify-content:center;gap:5px;margin-top:24px}
.buzon-pag-btn{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border-radius:8px;font-size:.83rem;font-weight:700;border:1.5px solid #e5e7eb;background:#fff;color:#374151;text-decoration:none;transition:all .15s}
.buzon-pag-btn:hover{border-color:#3b82f6;color:#3b82f6}
.buzon-pag-btn.active{background:#3b82f6;color:#fff;border-color:#3b82f6}
.buzon-pag-btn.disabled{opacity:.35;pointer-events:none}

/* Volver a notificaciones */
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:700;color:#6b7280;text-decoration:none;margin-bottom:18px;transition:color .15s}
.back-link:hover{color:#1B2336}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="buzon-layout">

  <a class="back-link" href="<?= BASE_URL ?>/index.php?url=notificaciones">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Volver a notificaciones
  </a>

  <div class="buzon-header">
    <div>
      <h1 class="buzon-title">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Buzón de entrada
        <?php if ($noLeidos > 0): ?>
          <span class="buzon-badge-new"><?= $noLeidos ?> nuevo<?= $noLeidos !== 1 ? 's' : '' ?></span>
        <?php endif; ?>
      </h1>
      <p class="buzon-meta"><?= $totalRows ?> mensaje<?= $totalRows !== 1 ? 's' : '' ?> recibido<?= $totalRows !== 1 ? 's' : '' ?> de administradores y editores</p>
    </div>
  </div>

  <?php if ($msgActivo): ?>
  <?php [$rolLbl, $rolColor, $rolBg] = buzonRolLabel($msgActivo['rol_emisor']); ?>
  <div class="buzon-msg-panel">
    <div class="buzon-msg-panel-head">
      <div class="buzon-msg-avatar" style="background:<?= $rolBg ?>;color:<?= $rolColor ?>">
        <?= mb_strtoupper(mb_substr($msgActivo['nombre_emisor'], 0, 1, 'UTF-8'), 'UTF-8') ?>
      </div>
      <div class="buzon-msg-meta">
        <h2><?= htmlspecialchars($msgActivo['asunto'] ?: 'Sin asunto') ?></h2>
        <p>
          <strong><?= htmlspecialchars($msgActivo['nombre_emisor']) ?></strong>
          <span class="rol-chip" style="background:<?= $rolBg ?>;color:<?= $rolColor ?>;border-color:<?= $rolColor ?>44"><?= $rolLbl ?></span>
          <span>·</span>
          <span><?= buzonTimeAgo($msgActivo['enviado_en']) ?></span>
        </p>
      </div>
    </div>
    <div class="buzon-msg-body"><?= htmlspecialchars($msgActivo['cuerpo']) ?></div>
    <a class="buzon-msg-close" href="<?= buildBuzonUrl() ?>">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Volver al listado
    </a>
  </div>
  <?php endif; ?>

  <?php if (empty($mensajes)): ?>
  <div class="buzon-empty">
    <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    <h3>Buzón vacío</h3>
    <p>No hay mensajes de administradores o editores todavía.</p>
  </div>
  <?php else: ?>
  <div class="buzon-list">
    <?php foreach ($mensajes as $msg):
      [$rolLbl, $rolColor, $rolBg] = buzonRolLabel($msg['rol_emisor']);
      $esLeido  = (bool)$msg['leido'];
      $esActivo = $msgActivo && $msgActivo['id'] === $msg['id'];
      $href     = buildBuzonUrl(['msg' => $msg['id']]);
    ?>
    <a href="<?= htmlspecialchars($href) ?>"
       class="buzon-item <?= !$esLeido ? 'unread' : '' ?> <?= $esActivo ? 'active-msg' : '' ?>">

      <div class="buzon-avatar" style="background:<?= $rolBg ?>;color:<?= $rolColor ?>">
        <?= mb_strtoupper(mb_substr($msg['nombre_emisor'], 0, 1, 'UTF-8'), 'UTF-8') ?>
      </div>

      <div class="buzon-body">
        <div class="buzon-item-top">
          <span class="buzon-emisor"><?= htmlspecialchars($msg['nombre_emisor']) ?></span>
          <span class="rol-chip" style="background:<?= $rolBg ?>;color:<?= $rolColor ?>;border-color:<?= $rolColor ?>44"><?= $rolLbl ?></span>
        </div>
        <div class="buzon-asunto"><?= htmlspecialchars($msg['asunto'] ?: 'Sin asunto') ?></div>
        <div class="buzon-preview"><?= htmlspecialchars(mb_substr($msg['cuerpo'], 0, 100)) ?></div>
      </div>

      <div class="buzon-side">
        <span class="buzon-time"><?= buzonTimeAgo($msg['enviado_en']) ?></span>
        <?php if (!$esLeido): ?>
          <span class="buzon-unread-dot" title="No leído"></span>
        <?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPags > 1): ?>
  <div class="buzon-pag">
    <a href="<?= buildBuzonUrl(['p' => max(1, $page - 1)]) ?>" class="buzon-pag-btn <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>
    <?php for ($i = max(1, $page - 2); $i <= min($totalPags, $page + 2); $i++): ?>
    <a href="<?= buildBuzonUrl(['p' => $i]) ?>" class="buzon-pag-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="<?= buildBuzonUrl(['p' => min($totalPags, $page + 1)]) ?>" class="buzon-pag-btn <?= $page >= $totalPags ? 'disabled' : '' ?>">›</a>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
