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
        'INSTRUCTOR'    => ['Instructor',    '#2563eb', '#dbeafe'],
        'MODERADOR'     => ['Moderador',     '#0891b2', '#e0f2fe'],
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
.buzon-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap}
.buzon-title{font-size:1.45rem;font-weight:900;color:#1B2336;margin:0;display:flex;align-items:center;gap:10px}
.buzon-badge-new{background:#3b82f6;color:#fff;font-size:.68rem;font-weight:800;border-radius:99px;padding:2px 8px}

/* Tabs */
.buzon-tabs{display:flex;gap:4px;background:#e2e8f0;border-radius:12px;padding:4px;margin-bottom:24px}
.buzon-tab-btn{flex:1;padding:9px 14px;border:none;border-radius:9px;font-family:inherit;font-size:.86rem;font-weight:700;cursor:pointer;background:transparent;color:#6b7280;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:7px}
.buzon-tab-btn.active{background:#fff;color:#1B2336;box-shadow:0 1px 5px rgba(0,0,0,.1)}

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

/* Volver */
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:700;color:#6b7280;text-decoration:none;margin-bottom:18px;transition:color .15s}
.back-link:hover{color:#1B2336}

/* Compose mensaje */
.msg-compose-wrap{background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;padding:22px;margin-bottom:18px}
.msg-compose-wrap h3{font-size:.95rem;font-weight:800;color:#1B2336;margin:0 0 14px}
.msg-toolbar{display:flex;justify-content:flex-end;margin-bottom:16px}
.msg-btn-new{display:inline-flex;align-items:center;gap:6px;font-size:.84rem;font-weight:700;background:#1B2336;color:#fff;border:none;border-radius:10px;padding:9px 18px;cursor:pointer;transition:background .15s;font-family:inherit}
.msg-btn-new:hover{background:#2d3a52}

/* ── Incidencias ── */
.inc-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap}
.inc-btn-new{display:inline-flex;align-items:center;gap:6px;font-size:.84rem;font-weight:700;background:#1B2336;color:#fff;border:none;border-radius:10px;padding:9px 18px;cursor:pointer;transition:background .15s;font-family:inherit}
.inc-btn-new:hover{background:#2d3a52}
.inc-list{display:flex;flex-direction:column;gap:7px}
.inc-item{background:#fff;border:1.5px solid #e5e7eb;border-radius:13px;padding:14px 18px;cursor:pointer;transition:box-shadow .15s,border-color .15s}
.inc-item:hover{box-shadow:0 4px 18px rgba(0,0,0,.07);border-color:#d1d5db}
.inc-item.active{border-color:#3b82f6;background:#eff6ff}
.inc-item-top{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px}
.inc-asunto{font-size:.9rem;font-weight:800;color:#1B2336;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.inc-estado{font-size:.68rem;font-weight:700;border-radius:99px;padding:2px 9px;border:1px solid transparent;white-space:nowrap}
.inc-estado-abierta{background:#fef9c3;color:#854d0e;border-color:#fde68a}
.inc-estado-en_proceso{background:#dbeafe;color:#1d4ed8;border-color:#93c5fd}
.inc-estado-cerrada{background:#f0fdf4;color:#166534;border-color:#86efac}
.inc-meta{font-size:.75rem;color:#9ca3af}
.inc-detail{background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;overflow:hidden;margin-bottom:16px}
.inc-detail-head{padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap}
.inc-detail-title{font-size:1rem;font-weight:800;color:#1B2336;margin:0 0 5px}
.inc-detail-back{display:inline-flex;align-items:center;gap:5px;font-size:.8rem;font-weight:700;color:#6b7280;background:none;border:1.5px solid #e5e7eb;border-radius:9px;padding:6px 13px;cursor:pointer;font-family:inherit;transition:all .15s}
.inc-detail-back:hover{border-color:#3b82f6;color:#3b82f6}
.inc-respuestas{padding:12px 20px;display:flex;flex-direction:column;gap:10px;max-height:360px;overflow-y:auto}
.inc-resp{background:#f8fafc;border-radius:10px;padding:10px 14px}
.inc-resp.admin{background:#eff6ff}
.inc-resp-autor{font-size:.72rem;font-weight:700;color:#374151;margin-bottom:3px}
.inc-resp-cuerpo{font-size:.86rem;color:#374151;white-space:pre-wrap;word-break:break-word}
.inc-no-resp{padding:20px;text-align:center;font-size:.84rem;color:#9ca3af}
.inc-new-form{background:#fff;border:1.5px solid #e5e7eb;border-radius:16px;padding:22px;margin-bottom:18px}
.inc-new-form h3{font-size:.95rem;font-weight:800;color:#1B2336;margin:0 0 14px}
.inc-label{display:block;font-size:.8rem;font-weight:700;color:#374151;margin-bottom:5px}
.inc-input{width:100%;padding:9px 13px;border:1.5px solid #e5e7eb;border-radius:10px;font-family:inherit;font-size:.9rem;color:#1B2336;transition:border-color .15s;outline:none}
.inc-input:focus{border-color:#3b82f6}
.inc-textarea{resize:vertical;min-height:90px}
.inc-form-row{margin-bottom:14px}
.inc-form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:6px}
.inc-btn-cancel{background:none;border:1.5px solid #e5e7eb;border-radius:10px;padding:8px 18px;font-family:inherit;font-size:.84rem;font-weight:700;color:#6b7280;cursor:pointer;transition:all .15s}
.inc-btn-cancel:hover{border-color:#d1d5db;color:#374151}
.inc-btn-submit{background:#3b82f6;color:#fff;border:none;border-radius:10px;padding:8px 20px;font-family:inherit;font-size:.84rem;font-weight:700;cursor:pointer;transition:background .15s}
.inc-btn-submit:hover{background:#2563eb}
.inc-btn-submit:disabled{opacity:.5;cursor:not-allowed}
.inc-loading{text-align:center;padding:40px;color:#9ca3af;font-size:.88rem}
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
    <h1 class="buzon-title">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      Buzón
      <?php if ($noLeidos > 0): ?>
        <span class="buzon-badge-new"><?= $noLeidos ?> nuevo<?= $noLeidos !== 1 ? 's' : '' ?></span>
      <?php endif; ?>
    </h1>
  </div>

  <!-- Pestañas -->
  <div class="buzon-tabs" role="tablist">
    <button class="buzon-tab-btn active" id="btn-tab-mensajes" onclick="buzonTab('mensajes')">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      Mensajes recibidos
      <?php if ($noLeidos > 0): ?><span class="buzon-badge-new"><?= $noLeidos ?></span><?php endif; ?>
    </button>
    <button class="buzon-tab-btn" id="btn-tab-incidencias" onclick="buzonTab('incidencias')">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      Mis incidencias
    </button>
  </div>

  <!-- ══════════ TAB: MENSAJES ══════════ -->
  <div id="tab-mensajes">

    <!-- Formulario nuevo mensaje (oculto por defecto) -->
    <div class="msg-compose-wrap" id="msg-compose-wrap" style="display:none">
      <h3>Nuevo mensaje</h3>
      <div class="inc-form-row">
        <label class="inc-label" for="msg-dest">Destinatario</label>
        <select class="inc-input" id="msg-dest">
          <option value="">Cargando…</option>
        </select>
      </div>
      <div class="inc-form-row">
        <label class="inc-label" for="msg-asunto">Asunto</label>
        <input class="inc-input" type="text" id="msg-asunto" maxlength="150" placeholder="Asunto del mensaje">
      </div>
      <div class="inc-form-row">
        <label class="inc-label" for="msg-cuerpo">Mensaje</label>
        <textarea class="inc-input inc-textarea" id="msg-cuerpo" placeholder="Escribe tu mensaje aquí…"></textarea>
      </div>
      <div class="inc-form-actions">
        <button class="inc-btn-cancel" onclick="msgCancelarCompose()">Cancelar</button>
        <button class="inc-btn-submit" id="msg-btn-enviar" onclick="msgEnviar()">Enviar mensaje</button>
      </div>
    </div>

    <div class="msg-toolbar" id="msg-toolbar">
      <button class="msg-btn-new" onclick="msgMostrarCompose()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nuevo mensaje
      </button>
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

  </div><!-- /tab-mensajes -->

  <!-- ══════════ TAB: INCIDENCIAS ══════════ -->
  <div id="tab-incidencias" style="display:none">

    <!-- Formulario nueva incidencia (oculto por defecto) -->
    <div class="inc-new-form" id="inc-form-wrapper" style="display:none">
      <h3>Nueva incidencia</h3>
      <div class="inc-form-row">
        <label class="inc-label" for="inc-asunto">Asunto</label>
        <input class="inc-input" type="text" id="inc-asunto" maxlength="200" placeholder="Describe el problema brevemente">
      </div>
      <div class="inc-form-row">
        <label class="inc-label" for="inc-cuerpo">Descripción</label>
        <textarea class="inc-input inc-textarea" id="inc-cuerpo" placeholder="Explica con detalle qué ha pasado, cuándo y qué estabas haciendo…"></textarea>
      </div>
      <div class="inc-form-actions">
        <button class="inc-btn-cancel" onclick="incCancelarNueva()">Cancelar</button>
        <button class="inc-btn-submit" id="inc-btn-enviar" onclick="incEnviar()">Enviar incidencia</button>
      </div>
    </div>

    <!-- Vista detalle de una incidencia (oculta por defecto) -->
    <div id="inc-detail-wrapper" style="display:none">
      <div class="inc-detail">
        <div class="inc-detail-head">
          <div>
            <div class="inc-detail-title" id="inc-detail-asunto"></div>
            <div class="inc-meta" id="inc-detail-meta"></div>
          </div>
          <button class="inc-detail-back" onclick="incVolverLista()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Volver a la lista
          </button>
        </div>
        <div id="inc-detail-cuerpo" style="padding:14px 20px 0;font-size:.88rem;color:#374151;white-space:pre-wrap;word-break:break-word;line-height:1.65;border-bottom:1px solid #f0f0f0;margin-bottom:0;padding-bottom:14px"></div>
        <div style="padding:10px 20px 6px;font-size:.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em">Respuestas</div>
        <div class="inc-respuestas" id="inc-respuestas-container"></div>
      </div>
    </div>

    <!-- Lista de incidencias -->
    <div id="inc-list-wrapper">
      <div class="inc-toolbar">
        <span style="font-size:.84rem;color:#6b7280;font-weight:600" id="inc-count-label">Cargando…</span>
        <button class="inc-btn-new" onclick="incMostrarNueva()">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nueva incidencia
        </button>
      </div>
      <div id="inc-list" class="inc-list">
        <div class="inc-loading">Cargando incidencias…</div>
      </div>
    </div>

  </div><!-- /tab-incidencias -->

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BUZON_BASE = '<?= BASE_URL ?>/index.php?url=buzon&action=';

// ── Compose mensaje ───────────────────────────────────────────────────────────
let msgAdminsCargados = false;

function msgMostrarCompose() {
    document.getElementById('msg-compose-wrap').style.display = '';
    document.getElementById('msg-toolbar').style.display = 'none';
    document.getElementById('msg-asunto').value = '';
    document.getElementById('msg-cuerpo').value  = '';
    if (!msgAdminsCargados) {
        fetch(BUZON_BASE + 'admins')
            .then(r => r.json())
            .then(admins => {
                msgAdminsCargados = true;
                const sel = document.getElementById('msg-dest');
                if (!admins.length) {
                    sel.innerHTML = '<option value="">Sin administradores disponibles</option>';
                    return;
                }
                sel.innerHTML = '<option value="">Selecciona destinatario…</option>' +
                    admins.map(a => `<option value="${a.id}">${escHtml(a.nombre)} (${escHtml(a.email)})</option>`).join('');
            })
            .catch(() => {
                document.getElementById('msg-dest').innerHTML = '<option value="">Error al cargar</option>';
            });
    }
    document.getElementById('msg-asunto').focus();
}

function msgCancelarCompose() {
    document.getElementById('msg-compose-wrap').style.display = 'none';
    document.getElementById('msg-toolbar').style.display = '';
}

function msgEnviar() {
    const receptor_id = document.getElementById('msg-dest').value;
    const asunto = document.getElementById('msg-asunto').value.trim();
    const cuerpo = document.getElementById('msg-cuerpo').value.trim();
    if (!receptor_id) { document.getElementById('msg-dest').focus(); return; }
    if (!cuerpo) { document.getElementById('msg-cuerpo').focus(); return; }

    const btn = document.getElementById('msg-btn-enviar');
    btn.disabled = true;
    btn.textContent = 'Enviando…';

    fetch(BUZON_BASE + 'enviar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ receptor_id: parseInt(receptor_id), asunto, cuerpo })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            msgCancelarCompose();
            // mostrar confirmación breve
            const ok = document.createElement('div');
            ok.textContent = 'Mensaje enviado correctamente.';
            ok.style.cssText = 'background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:12px 16px;font-size:.86rem;font-weight:700;color:#166534;margin-bottom:14px';
            document.getElementById('tab-mensajes').prepend(ok);
            setTimeout(() => ok.remove(), 4000);
        } else {
            alert('Error: ' + (data.error || 'inténtalo de nuevo'));
        }
    })
    .catch(() => alert('Error de red. Inténtalo de nuevo.'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Enviar mensaje';
    });
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
function buzonTab(tab) {
    document.getElementById('tab-mensajes').style.display    = tab === 'mensajes'    ? '' : 'none';
    document.getElementById('tab-incidencias').style.display = tab === 'incidencias' ? '' : 'none';
    document.getElementById('btn-tab-mensajes').classList.toggle('active',    tab === 'mensajes');
    document.getElementById('btn-tab-incidencias').classList.toggle('active', tab === 'incidencias');
    if (tab === 'incidencias' && !incCargadas) incCargarLista();
}

// ── Estado incidencias ────────────────────────────────────────────────────────
let incCargadas = false;

function incEstadoChip(estado) {
    const map = {
        abierta:    ['Abierta',     'inc-estado-abierta'],
        en_proceso: ['En proceso',  'inc-estado-en_proceso'],
        cerrada:    ['Cerrada',     'inc-estado-cerrada'],
    };
    const [label, cls] = map[estado] || ['Desconocido', ''];
    return `<span class="inc-estado ${cls}">${label}</span>`;
}

function incTimeAgo(str) {
    if (!str) return '';
    const diff = (Date.now() - new Date(str.replace(' ', 'T') + 'Z')) / 1000;
    if (diff < 60)     return 'ahora mismo';
    if (diff < 3600)   return 'hace ' + Math.floor(diff / 60) + 'm';
    if (diff < 86400)  return 'hace ' + Math.floor(diff / 3600) + 'h';
    if (diff < 604800) return 'hace ' + Math.floor(diff / 86400) + 'd';
    return new Date(str.replace(' ', 'T')).toLocaleDateString('es-ES');
}

// ── Cargar lista ──────────────────────────────────────────────────────────────
function incCargarLista() {
    fetch(BUZON_BASE + 'mis_incidencias')
        .then(r => r.json())
        .then(lista => {
            incCargadas = true;
            const el = document.getElementById('inc-list');
            const label = document.getElementById('inc-count-label');

            if (!Array.isArray(lista) || lista.length === 0) {
                label.textContent = 'Sin incidencias';
                el.innerHTML = `
                    <div class="buzon-empty">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <h3>Sin incidencias</h3>
                        <p>Aún no has enviado ninguna incidencia de soporte.</p>
                    </div>`;
                return;
            }

            label.textContent = lista.length + ' incidencia' + (lista.length !== 1 ? 's' : '');
            el.innerHTML = lista.map(inc => `
                <div class="inc-item" onclick="incVerDetalle(${inc.id})" title="Ver detalle">
                    <div style="display:flex;align-items:flex-start;gap:10px">
                        <div style="flex:1;min-width:0">
                            <div class="inc-item-top">
                                <span class="inc-asunto">${escHtml(inc.asunto)}</span>
                                ${incEstadoChip(inc.estado)}
                            </div>
                            <div class="inc-meta">
                                ${inc.nombre_asignado ? 'Asignada a ' + escHtml(inc.nombre_asignado) + ' · ' : ''}
                                ${inc.num_respuestas} respuesta${inc.num_respuestas != 1 ? 's' : ''} · ${incTimeAgo(inc.creado_en)}
                            </div>
                        </div>
                        <svg style="flex-shrink:0;margin-top:2px;color:#9ca3af" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                </div>`).join('');
        })
        .catch(() => {
            document.getElementById('inc-list').innerHTML =
                '<div class="buzon-empty"><h3>Error al cargar</h3><p>Inténtalo de nuevo.</p></div>';
        });
}

// ── Ver detalle ───────────────────────────────────────────────────────────────
function incVerDetalle(id) {
    document.getElementById('inc-list-wrapper').style.display  = 'none';
    document.getElementById('inc-form-wrapper').style.display  = 'none';
    const wrapper = document.getElementById('inc-detail-wrapper');
    wrapper.style.display = '';
    document.getElementById('inc-detail-asunto').textContent   = 'Cargando…';
    document.getElementById('inc-detail-meta').textContent     = '';
    document.getElementById('inc-respuestas-container').innerHTML = '<div class="inc-loading">Cargando…</div>';

    fetch(BUZON_BASE + 'mi_incidencia_detalle&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { incVolverLista(); return; }
            const inc = data.incidencia;
            document.getElementById('inc-detail-asunto').textContent = inc.asunto;
            document.getElementById('inc-detail-meta').innerHTML =
                incEstadoChip(inc.estado) + ' &nbsp;·&nbsp; Abierta ' + incTimeAgo(inc.creado_en) +
                (inc.nombre_asignado ? ' &nbsp;·&nbsp; Asignada a ' + escHtml(inc.nombre_asignado) : '');
            const cuerpoEl = document.getElementById('inc-detail-cuerpo');
            if (inc.cuerpo) {
                cuerpoEl.textContent = inc.cuerpo;
                cuerpoEl.style.display = '';
            } else {
                cuerpoEl.style.display = 'none';
            }

            const respEl = document.getElementById('inc-respuestas-container');
            if (!data.respuestas || data.respuestas.length === 0) {
                respEl.innerHTML = '<div class="inc-no-resp">Sin respuestas todavía. Te responderemos lo antes posible.</div>';
            } else {
                const esAdmin = r => r.rol === 'ADMINISTRADOR' || r.rol === 'MODERADOR' || r.rol === 'INSTRUCTOR';
                respEl.innerHTML = data.respuestas.map(r => `
                    <div class="inc-resp ${esAdmin(r) ? 'admin' : ''}">
                        <div class="inc-resp-autor">${escHtml(r.autor)} · ${incTimeAgo(r.creado_en)}</div>
                        <div class="inc-resp-cuerpo">${escHtml(r.mensaje)}</div>
                    </div>`).join('');
                respEl.scrollTop = respEl.scrollHeight;
            }
        })
        .catch(() => incVolverLista());
}

function incVolverLista() {
    document.getElementById('inc-detail-wrapper').style.display = 'none';
    document.getElementById('inc-list-wrapper').style.display   = '';
}

// ── Nueva incidencia ──────────────────────────────────────────────────────────
function incMostrarNueva() {
    document.getElementById('inc-asunto').value = '';
    document.getElementById('inc-cuerpo').value  = '';
    document.getElementById('inc-form-wrapper').style.display  = '';
    document.getElementById('inc-list-wrapper').style.display  = 'none';
    document.getElementById('inc-detail-wrapper').style.display = 'none';
    document.getElementById('inc-asunto').focus();
}

function incCancelarNueva() {
    document.getElementById('inc-form-wrapper').style.display = 'none';
    document.getElementById('inc-list-wrapper').style.display = '';
}

function incEnviar() {
    const asunto = document.getElementById('inc-asunto').value.trim();
    const cuerpo = document.getElementById('inc-cuerpo').value.trim();
    if (!asunto) { document.getElementById('inc-asunto').focus(); return; }
    if (!cuerpo) { document.getElementById('inc-cuerpo').focus(); return; }

    const btn = document.getElementById('inc-btn-enviar');
    btn.disabled = true;
    btn.textContent = 'Enviando…';

    fetch(BUZON_BASE + 'crear_incidencia', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ asunto, cuerpo })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            incCancelarNueva();
            incCargadas = false;
            incCargarLista();
        } else {
            alert('Error al enviar: ' + (data.error || 'inténtalo de nuevo'));
        }
    })
    .catch(() => alert('Error de red. Inténtalo de nuevo.'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Enviar incidencia';
    });
}

// ── Utilidad ──────────────────────────────────────────────────────────────────
function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>
