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
<link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
<style>
:root{
  --bz-bg:#f1f5f9;
  --bz-frame:#0f172a;
  --bz-frame-2:#1e293b;
  --bz-frame-3:#334155;
  --bz-text-light:#e2e8f0;
  --bz-muted:#94a3b8;
  --bz-card:#fff;
  --bz-border:#e2e8f0;
  --bz-border-soft:#f1f5f9;
  --bz-text:#0f172a;
  --bz-text-2:#475569;
  --bz-text-3:#94a3b8;
  --bz-primary:#2563eb;
  --bz-primary-d:#1d4ed8;
  --bz-primary-soft:#eff6ff;
}
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Saira',sans-serif;background:var(--bz-bg);margin:0;color:var(--bz-text)}

/* Bajar un poco el sidebar */
.contenedor-dashboard-content > .sidebar{margin-top:20px}

/* Layout principal */
.buzon-layout{max-width:1240px;margin:0 auto;padding:14px 24px 50px}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:700;color:var(--bz-text-2);text-decoration:none;margin-bottom:12px;transition:color .15s;text-transform:uppercase;letter-spacing:.5px}
.back-link:hover{color:var(--bz-text)}

/* Cabecera externa */
.buzon-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;gap:12px;flex-wrap:wrap}
.buzon-title{font-size:1.4rem;font-weight:900;color:var(--bz-text);margin:0;display:flex;align-items:center;gap:10px;letter-spacing:-.3px}
.buzon-badge-new{background:var(--bz-primary);color:#fff;font-size:.66rem;font-weight:800;border-radius:99px;padding:3px 9px;letter-spacing:.3px}

/* ═══════════════ FRAME PRINCIPAL OSCURO (Outlook) ═══════════════ */
.msg-frame{background:var(--bz-frame);border-radius:18px;padding:16px;display:flex;flex-direction:column;box-shadow:0 20px 50px -10px rgba(15,23,42,.35);border:1px solid var(--bz-frame-2)}

/* Topbar del frame */
.msg-frame-topbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid var(--bz-frame-2);margin-bottom:14px}
.msg-frame-actions{display:flex;align-items:center;gap:10px}

/* Tabs estilo Outlook */
.buzon-tabs{display:flex;gap:2px;background:rgba(255,255,255,.04);border-radius:10px;padding:3px;margin:0}
.buzon-tab-btn{padding:8px 16px;border:none;border-radius:7px;font-family:inherit;font-size:.8rem;font-weight:700;cursor:pointer;background:transparent;color:var(--bz-muted);transition:all .18s;display:flex;align-items:center;gap:7px}
.buzon-tab-btn svg{opacity:.7}
.buzon-tab-btn:hover{color:#fff}
.buzon-tab-btn:hover svg{opacity:1}
.buzon-tab-btn.active{background:rgba(255,255,255,.1);color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.25)}
.buzon-tab-btn.active svg{opacity:1}

/* Buscador */
.msg-search{display:flex;align-items:center;background:rgba(255,255,255,.05);border:1px solid var(--bz-frame-2);border-radius:8px;padding:7px 11px;gap:8px;min-width:260px;transition:border-color .15s,background .15s}
.msg-search:focus-within{border-color:var(--bz-primary);background:rgba(255,255,255,.08)}
.msg-search svg{color:var(--bz-muted);flex-shrink:0}
.msg-search input{background:transparent;border:none;color:#fff;font-family:inherit;font-size:.82rem;outline:none;width:100%}
.msg-search input::placeholder{color:var(--bz-muted)}

/* Botón primario */
.msg-btn-new{display:inline-flex;align-items:center;gap:7px;font-size:.8rem;font-weight:700;background:var(--bz-primary);color:#fff;border:none;border-radius:8px;padding:8px 16px;cursor:pointer;transition:all .15s;font-family:inherit;white-space:nowrap;box-shadow:0 1px 3px rgba(37,99,235,.4)}
.msg-btn-new:hover{background:var(--bz-primary-d);transform:translateY(-1px);box-shadow:0 4px 12px rgba(37,99,235,.4)}

/* ═══════════════ TABLA DE MENSAJES ═══════════════ */
.msg-table-container{background:var(--bz-card);border-radius:12px;overflow:hidden;flex:1;display:flex;flex-direction:column;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.msg-table-header{display:grid;grid-template-columns:150px 1.2fr 2fr 130px 50px;background:#f8fafc;border-bottom:1px solid var(--bz-border);font-size:.7rem;font-weight:800;color:var(--bz-text-2);text-transform:uppercase;letter-spacing:.5px}
.msg-table-header > span{padding:14px 18px;display:flex;align-items:center}
.msg-table-body{display:flex;flex-direction:column;flex:1}
.msg-row{display:grid;grid-template-columns:150px 1.2fr 2fr 130px 50px;border-bottom:1px solid var(--bz-border-soft);text-decoration:none;color:inherit;transition:background .12s;position:relative;min-height:64px}
.msg-row > div{padding:14px 18px;display:flex;align-items:center;min-width:0}
.msg-row:hover{background:#f8fafc}
.msg-row.unread{background:linear-gradient(to right,rgba(37,99,235,.06),transparent 70%)}
.msg-row.unread::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--bz-primary);z-index:1}
.msg-row.active-msg{background:var(--bz-primary-soft)}
.msg-row-empty{display:grid;grid-template-columns:150px 1.2fr 2fr 130px 50px;border-bottom:1px solid var(--bz-border-soft);min-height:64px;background:repeating-linear-gradient(0deg,transparent 0,transparent 63px,var(--bz-border-soft) 63px,var(--bz-border-soft) 64px)}
.msg-row-empty > div{padding:14px 18px}
.msg-row-empty:last-child{border-bottom:none}

.msg-cell-tipo{display:flex;align-items:center}
.msg-cell-emisor{display:flex;align-items:center;gap:11px;min-width:0}
.buzon-avatar{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:800;flex-shrink:0}
.msg-emisor-nombre{font-size:.86rem;font-weight:700;color:var(--bz-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.msg-cell-asunto{min-width:0}
.msg-asunto-text{font-size:.88rem;font-weight:600;color:var(--bz-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}
.msg-row.unread .msg-asunto-text{font-weight:800}
.msg-preview-text{font-size:.76rem;color:var(--bz-text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;margin-top:3px}
.msg-cell-fecha{font-size:.78rem;color:var(--bz-text-3);font-weight:600}
.msg-cell-status{display:flex;justify-content:center}
.buzon-unread-dot{width:9px;height:9px;border-radius:50%;background:var(--bz-primary);flex-shrink:0;box-shadow:0 0 0 3px rgba(37,99,235,.2)}

.rol-chip{display:inline-flex;align-items:center;font-size:.66rem;font-weight:700;border-radius:99px;padding:3px 10px;border:1px solid transparent;white-space:nowrap;letter-spacing:.2px}

/* Panel mensaje activo (cuando se abre un mensaje) */
.buzon-msg-panel{background:var(--bz-card);border-radius:12px;margin-bottom:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.buzon-msg-panel-head{padding:18px 22px 14px;border-bottom:1px solid var(--bz-border);display:flex;align-items:flex-start;gap:14px}
.buzon-msg-avatar{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;flex-shrink:0}
.buzon-msg-meta h2{font-size:1.05rem;font-weight:800;color:var(--bz-text);margin:0 0 5px;letter-spacing:-.2px}
.buzon-msg-meta p{font-size:.82rem;color:var(--bz-text-2);margin:0;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.buzon-msg-body{padding:20px 22px;font-size:.92rem;color:var(--bz-text-2);line-height:1.7;white-space:pre-wrap;word-break:break-word}
.buzon-msg-actions{display:flex;align-items:center;gap:10px;padding:0 22px 18px}
.buzon-msg-close{display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:700;color:var(--bz-text-2);text-decoration:none;padding:7px 14px;border:1px solid var(--bz-border);border-radius:8px;transition:all .15s;background:#fff}
.buzon-msg-close:hover{border-color:var(--bz-primary);color:var(--bz-primary);background:var(--bz-primary-soft)}
.msg-btn-reply{display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:700;color:#fff;padding:7px 14px;border:none;border-radius:8px;cursor:pointer;background:var(--bz-primary);transition:background .15s}
.msg-btn-reply:hover{background:var(--bz-primary-d)}
.inc-input-static{display:block;padding:9px 13px;font-size:.86rem;color:var(--bz-text-2);background:var(--bz-border-soft);border-radius:8px}

/* Empty state */
.buzon-empty{text-align:center;padding:80px 20px;color:var(--bz-text-3)}
.buzon-empty svg{opacity:.25;display:block;margin:0 auto 18px}
.buzon-empty h3{font-size:1rem;font-weight:700;color:var(--bz-text-2);margin:0 0 6px}
.buzon-empty p{font-size:.85rem;margin:0}

/* Paginación */
.buzon-pag{display:flex;gap:6px;margin-top:14px;align-items:center;padding:4px}
.buzon-pag-btn{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 12px;border-radius:8px;font-size:.78rem;font-weight:700;border:1px solid var(--bz-frame-2);background:rgba(255,255,255,.04);color:var(--bz-muted);text-decoration:none;transition:all .15s}
.buzon-pag-btn:hover{border-color:var(--bz-primary);color:#fff;background:rgba(37,99,235,.15)}
.buzon-pag-btn.active{background:var(--bz-primary);color:#fff;border-color:var(--bz-primary)}
.buzon-pag-btn.disabled{opacity:.35;pointer-events:none}

/* Compose mensaje */
.msg-compose-wrap{background:var(--bz-card);border-radius:12px;padding:22px;margin-bottom:14px;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.msg-compose-wrap h3{font-size:.95rem;font-weight:800;color:var(--bz-text);margin:0 0 14px}

/* ═══════════════ INCIDENCIAS ═══════════════ */
.inc-list{display:flex;flex-direction:column;gap:8px}
.inc-item{background:var(--bz-card);border:1px solid var(--bz-border);border-radius:10px;padding:14px 18px;cursor:pointer;transition:all .15s;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.inc-item:hover{box-shadow:0 4px 14px rgba(0,0,0,.07);border-color:#cbd5e1;transform:translateY(-1px)}
.inc-item.active{border-color:var(--bz-primary);background:var(--bz-primary-soft)}
.inc-item-top{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px}
.inc-asunto{font-size:.9rem;font-weight:800;color:var(--bz-text);flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.inc-estado{font-size:.66rem;font-weight:700;border-radius:99px;padding:3px 10px;border:1px solid transparent;white-space:nowrap;letter-spacing:.3px}
.inc-estado-abierta{background:#fef9c3;color:#854d0e;border-color:#fde68a}
.inc-estado-en_proceso{background:#dbeafe;color:#1d4ed8;border-color:#93c5fd}
.inc-estado-cerrada{background:#f0fdf4;color:#166534;border-color:#86efac}
.inc-meta{font-size:.74rem;color:var(--bz-text-3)}
.inc-detail{background:var(--bz-card);border-radius:12px;overflow:hidden;margin-bottom:14px;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.inc-detail-head{padding:16px 20px;border-bottom:1px solid var(--bz-border);display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap}
.inc-detail-title{font-size:1rem;font-weight:800;color:var(--bz-text);margin:0 0 5px}
.inc-detail-back{display:inline-flex;align-items:center;gap:5px;font-size:.78rem;font-weight:700;color:var(--bz-text-2);background:#fff;border:1px solid var(--bz-border);border-radius:8px;padding:6px 13px;cursor:pointer;font-family:inherit;transition:all .15s}
.inc-detail-back:hover{border-color:var(--bz-primary);color:var(--bz-primary)}
.inc-respuestas{padding:14px 20px;display:flex;flex-direction:column;gap:10px;max-height:380px;overflow-y:auto}
.inc-resp{background:#f8fafc;border-radius:10px;padding:11px 14px;border:1px solid var(--bz-border-soft)}
.inc-resp.admin{background:var(--bz-primary-soft);border-color:#dbeafe}
.inc-resp-autor{font-size:.7rem;font-weight:700;color:var(--bz-text-2);margin-bottom:3px}
.inc-resp-cuerpo{font-size:.86rem;color:var(--bz-text-2);white-space:pre-wrap;word-break:break-word}
.inc-no-resp{padding:24px;text-align:center;font-size:.84rem;color:var(--bz-text-3)}
.inc-new-form{background:var(--bz-card);border-radius:12px;padding:22px;margin-bottom:14px;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.inc-new-form h3{font-size:.95rem;font-weight:800;color:var(--bz-text);margin:0 0 14px}
.inc-label{display:block;font-size:.78rem;font-weight:700;color:var(--bz-text-2);margin-bottom:6px}
.inc-input{width:100%;padding:10px 13px;border:1px solid var(--bz-border);border-radius:8px;font-family:inherit;font-size:.9rem;color:var(--bz-text);transition:all .15s;outline:none;background:#fff}
.inc-input:focus{border-color:var(--bz-primary);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.inc-textarea{resize:vertical;min-height:100px}
.inc-form-row{margin-bottom:14px}
.inc-form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
.inc-btn-cancel{background:#fff;border:1px solid var(--bz-border);border-radius:8px;padding:9px 18px;font-family:inherit;font-size:.82rem;font-weight:700;color:var(--bz-text-2);cursor:pointer;transition:all .15s}
.inc-btn-cancel:hover{border-color:#cbd5e1;color:var(--bz-text)}
.inc-btn-submit{background:var(--bz-primary);color:#fff;border:none;border-radius:8px;padding:9px 22px;font-family:inherit;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .15s;box-shadow:0 1px 3px rgba(37,99,235,.4)}
.inc-btn-submit:hover{background:var(--bz-primary-d);box-shadow:0 4px 12px rgba(37,99,235,.4)}
.inc-btn-submit:disabled{opacity:.5;cursor:not-allowed}
.inc-loading{text-align:center;padding:50px;color:var(--bz-text-3);font-size:.86rem;background:#fff;border-radius:10px}

/* Texto de paginación */
.buzon-pag-info{margin-left:auto;font-size:.76rem;color:var(--bz-muted);align-self:center;font-weight:600}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="main-dashboard">
  <div class="mc-container">
    <div class="contenedor-dashboard-content">

      <?php require __DIR__ . '/../layout/sidebar.php'; ?>

      <div class="buzon-layout" style="flex:1;min-width:0;max-width:none;min-height:calc(100vh - 80px);">

  <a class="back-link" href="<?= BASE_URL ?>/index.php?url=dashboard">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Volver al espacio de trabajo
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

  <!-- ══════════ FRAME OSCURO PRINCIPAL ══════════ -->
  <div class="msg-frame">

    <!-- Topbar del frame: tabs + acciones -->
    <div class="msg-frame-topbar">
      <div class="buzon-tabs" role="tablist">
        <button class="buzon-tab-btn active" id="btn-tab-mensajes" onclick="buzonTab('mensajes')">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          Mensajes recibidos
          <?php if ($noLeidos > 0): ?><span class="buzon-badge-new"><?= $noLeidos ?></span><?php endif; ?>
        </button>
        <button class="buzon-tab-btn" id="btn-tab-incidencias" onclick="buzonTab('incidencias')">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          Mis incidencias
        </button>
      </div>
      <div class="msg-frame-actions">
        <div class="msg-search">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" placeholder="Buscar en el buzón...">
        </div>
        <button class="msg-btn-new" id="btn-nuevo-mensajes" onclick="msgMostrarCompose()">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nuevo mensaje
        </button>
        <button class="msg-btn-new" id="btn-nuevo-incidencia" onclick="incMostrarNueva()" style="display:none">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nueva incidencia
        </button>
      </div>
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
      <div class="buzon-msg-actions">
        <button class="msg-btn-reply" onclick="msgMostrarReply(<?= (int)$msgActivo['id'] ?>, <?= (int)$msgActivo['emisor_id'] ?>, '<?= addslashes(htmlspecialchars($msgActivo['asunto'] ?: 'Sin asunto')) ?>')">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
          Responder
        </button>
        <a class="buzon-msg-close" href="<?= buildBuzonUrl() ?>">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
          Volver al listado
        </a>
      </div>

      <!-- Formulario de respuesta (oculto por defecto) -->
      <div class="msg-compose-wrap" id="msg-reply-wrap" style="display:none">
        <h3>Responder</h3>
        <input type="hidden" id="reply-dest-id">
        <div class="inc-form-row">
          <label class="inc-label">Para</label>
          <span class="inc-input-static" id="reply-dest-name"></span>
        </div>
        <div class="inc-form-row">
          <label class="inc-label">Asunto</label>
          <span class="inc-input-static" id="reply-asunto"></span>
        </div>
        <div class="inc-form-row">
          <label class="inc-label" for="reply-cuerpo">Mensaje</label>
          <textarea class="inc-input inc-textarea" id="reply-cuerpo" placeholder="Escribe tu respuesta…"></textarea>
        </div>
        <div class="inc-form-actions">
          <button class="inc-btn-cancel" onclick="msgCancelarReply()">Cancelar</button>
          <button class="inc-btn-submit" id="reply-btn-enviar" onclick="msgEnviarReply()">Enviar respuesta</button>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="msg-table-container">
        <div class="msg-table-header">
          <span>Tipo</span>
          <span>Remitente</span>
          <span>Asunto</span>
          <span>Fecha</span>
          <span></span>
        </div>
        <div class="msg-table-body">
          <?php
          $filasOcupadas = count($mensajes);
          foreach ($mensajes as $msg):
            [$rolLbl, $rolColor, $rolBg] = buzonRolLabel($msg['rol_emisor']);
            $esLeido  = (bool)$msg['leido'];
            $esActivo = $msgActivo && $msgActivo['id'] === $msg['id'];
            $href     = buildBuzonUrl(['msg' => $msg['id']]);
          ?>
          <a href="<?= htmlspecialchars($href) ?>"
             class="msg-row <?= !$esLeido ? 'unread' : '' ?> <?= $esActivo ? 'active-msg' : '' ?>">
            <div class="msg-cell-tipo">
              <span class="rol-chip" style="background:<?= $rolBg ?>;color:<?= $rolColor ?>;border-color:<?= $rolColor ?>44"><?= $rolLbl ?></span>
            </div>
            <div class="msg-cell-emisor">
              <div class="buzon-avatar" style="background:<?= $rolBg ?>;color:<?= $rolColor ?>">
                <?= mb_strtoupper(mb_substr($msg['nombre_emisor'], 0, 1, 'UTF-8'), 'UTF-8') ?>
              </div>
              <span class="msg-emisor-nombre"><?= htmlspecialchars($msg['nombre_emisor']) ?></span>
            </div>
            <div class="msg-cell-asunto">
              <div style="min-width:0;display:flex;flex-direction:column">
                <span class="msg-asunto-text"><?= htmlspecialchars($msg['asunto'] ?: 'Sin asunto') ?></span>
                <span class="msg-preview-text"><?= htmlspecialchars(mb_substr($msg['cuerpo'], 0, 80)) ?></span>
              </div>
            </div>
            <div class="msg-cell-fecha"><?= buzonTimeAgo($msg['enviado_en']) ?></div>
            <div class="msg-cell-status" style="justify-content:center">
              <?php if (!$esLeido): ?>
                <span class="buzon-unread-dot" title="No leído"></span>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>

          <?php
          // Filas vacías para rellenar como hoja de cálculo
          $filasVacias = max(0, 6 - $filasOcupadas);
          for ($i = 0; $i < $filasVacias; $i++):
          ?>
          <div class="msg-row-empty">
            <div></div><div></div><div></div><div></div><div></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>

    <div class="buzon-pag">
      <a href="<?= buildBuzonUrl(['p' => max(1, $page - 1)]) ?>" class="buzon-pag-btn <?= $page <= 1 ? 'disabled' : '' ?>">‹ Anterior</a>
      <?php for ($i = max(1, $page - 2); $i <= min(max(1,$totalPags), $page + 2); $i++): ?>
      <a href="<?= buildBuzonUrl(['p' => $i]) ?>" class="buzon-pag-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a href="<?= buildBuzonUrl(['p' => min(max(1,$totalPags), $page + 1)]) ?>" class="buzon-pag-btn <?= $page >= $totalPags ? 'disabled' : '' ?>">Siguiente ›</a>
      <span class="buzon-pag-info">Página <?= $page ?> de <?= max(1,$totalPags) ?> · <?= count($mensajes) ?> mensaje<?= count($mensajes) !== 1 ? 's' : '' ?></span>
    </div>

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
      <div style="margin-bottom:12px;padding:8px 12px;background:#fff;border-radius:8px">
        <span style="font-size:.78rem;color:#6b7280;font-weight:600" id="inc-count-label">Cargando…</span>
      </div>
      <div id="inc-list" class="inc-list">
        <div class="inc-loading">Cargando incidencias…</div>
      </div>
    </div>

  </div><!-- /tab-incidencias -->

  </div><!-- /msg-frame -->

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

// ── Reply ─────────────────────────────────────────────────────────────────────
function msgMostrarReply(msgId, destId, asunto) {
    const wrap = document.getElementById('msg-reply-wrap');
    if (!wrap) return;
    document.getElementById('reply-dest-id').value = destId;
    document.getElementById('reply-dest-name').textContent = document.querySelector('.buzon-msg-meta h2') ? document.querySelector('.buzon-msg-meta p strong')?.textContent || '' : '';
    document.getElementById('reply-asunto').textContent = 'Re: ' + asunto;
    document.getElementById('reply-cuerpo').value = '';
    wrap.style.display = '';
    document.getElementById('reply-cuerpo').focus();
}

function msgCancelarReply() {
    const wrap = document.getElementById('msg-reply-wrap');
    if (wrap) wrap.style.display = 'none';
}

function msgEnviarReply() {
    const receptor_id = document.getElementById('reply-dest-id').value;
    const asunto = document.getElementById('reply-asunto').textContent.trim();
    const cuerpo = document.getElementById('reply-cuerpo').value.trim();
    if (!cuerpo) { document.getElementById('reply-cuerpo').focus(); return; }

    const btn = document.getElementById('reply-btn-enviar');
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
            msgCancelarReply();
            const ok = document.createElement('div');
            ok.textContent = 'Respuesta enviada correctamente.';
            ok.style.cssText = 'background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:12px 16px;font-size:.86rem;font-weight:700;color:#166534;margin:0 22px 14px';
            document.querySelector('.buzon-msg-panel').appendChild(ok);
            setTimeout(() => ok.remove(), 4000);
        } else {
            alert('Error: ' + (data.error || 'inténtalo de nuevo'));
        }
    })
    .catch(() => alert('Error de red. Inténtalo de nuevo.'))
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Enviar respuesta';
    });
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
    document.getElementById('btn-nuevo-mensajes').style.display   = tab === 'mensajes'    ? '' : 'none';
    document.getElementById('btn-nuevo-incidencia').style.display = tab === 'incidencias' ? '' : 'none';
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

      </div><!-- /buzon-layout -->
    </div><!-- /contenedor-dashboard-content -->
  </div><!-- /mc-container -->
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>

</body>
</html>
