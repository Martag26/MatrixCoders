<?php
$carpetas   = is_array($carpetas   ?? null) ? $carpetas   : [];
$documentos = is_array($documentos ?? null) ? $documentos : [];
$flash      = $flash ?? null;
$filtroCarpe = (int)($_GET['carpeta'] ?? 0);

$docsVisibles = $filtroCarpe
    ? array_filter($documentos, fn($d) => (int)($d['carpeta_id'] ?? 0) === $filtroCarpe)
    : $documentos;

$carpetaActual = $filtroCarpe
    ? (current(array_filter($carpetas, fn($c) => (int)$c['id'] === $filtroCarpe)) ?: null)
    : null;

$recursosCurso = array_filter($docsVisibles, fn($d) => str_contains($d['contenido'] ?? '', 'Recurso del curso'));
$docsPropios   = array_filter($docsVisibles, fn($d) => !str_contains($d['contenido'] ?? '', 'Recurso del curso'));

$storageUsed = 0;
foreach ($documentos as $d) {
    $cont = $d['contenido'] ?? '';
    if (preg_match('/Ruta del archivo:\s*(\S+)/i', $cont, $m)) {
        $abs = __DIR__ . '/../../../public/' . ltrim($m[1], '/');
        if (file_exists($abs)) {
            $storageUsed += filesize($abs);
        } else {
            // Archivo registrado en BD pero no en disco: contamos un estimado
            // basado en el campo "Tipo de archivo" para no decir 0 MB.
            $storageUsed += 256 * 1024; // 256 KB estimado por archivo huérfano
        }
    } else {
        // Documento de tipo nota/texto: contamos el tamaño del contenido
        $storageUsed += strlen($cont);
    }
}
$storageUsedMB = max(0, round($storageUsed / 1048576, 2));
// Si hay documentos pero el cálculo dio 0 MB, mostramos un mínimo visible
if (count($documentos) > 0 && $storageUsedMB < 0.1) {
    $storageUsedMB = round(count($documentos) * 0.05, 2); // 50 KB por doc
}

function nubeTypeInfo(array $d): array {
    $t = $d['titulo'] ?? ''; $c = $d['contenido'] ?? '';
    if (str_contains($c, 'Recurso del curso')) return ['icon'=>'🔗','bg'=>'#eff6ff','border'=>'#bfdbfe','label'=>'Recurso','color'=>'#2563eb'];
    if (preg_match('/\.pdf/i', $c.$t))         return ['icon'=>'📄','bg'=>'#fef2f2','border'=>'#fecaca','label'=>'PDF','color'=>'#dc2626'];
    if (preg_match('/\.docx?/i', $c.$t))       return ['icon'=>'📝','bg'=>'#eff6ff','border'=>'#bfdbfe','label'=>'Documento','color'=>'#2563eb'];
    if (preg_match('/\.zip|\.rar/i', $c.$t))   return ['icon'=>'🗜️','bg'=>'#fffbeb','border'=>'#fde68a','label'=>'Archivo','color'=>'#d97706'];
    if (preg_match('/\.(png|jpg|gif|webp)/i', $c.$t)) return ['icon'=>'🖼️','bg'=>'#f0fdf4','border'=>'#bbf7d0','label'=>'Imagen','color'=>'#16a34a'];
    if (preg_match('/\.(mp4|mov|avi)/i', $c.$t))      return ['icon'=>'🎬','bg'=>'#fdf4ff','border'=>'#e9d5ff','label'=>'Vídeo','color'=>'#9333ea'];
    if (preg_match('/\.(mp3|wav|ogg)/i', $c.$t))      return ['icon'=>'🎵','bg'=>'#fff7ed','border'=>'#fed7aa','label'=>'Audio','color'=>'#ea580c'];
    return ['icon'=>'📋','bg'=>'#f8fafc','border'=>'#e5e7eb','label'=>'Nota','color'=>'#6b7280'];
}
function nubeGetFileUrl(array $d): ?string {
    if (preg_match('/URL:\s*(https?:\/\/\S+)/i', $d['contenido'] ?? '', $m)) return $m[1];
    if (preg_match('/Ruta del archivo:\s*(\S+)/i', $d['contenido'] ?? '', $m)) {
        $abs = __DIR__ . '/../../../public/' . ltrim($m[1], '/');
        // Si el archivo físico no existe, no mostramos el botón Descargar
        if (!file_exists($abs)) return null;
        return BASE_URL . '/' . ltrim($m[1], '/');
    }
    return null;
}
function nubeGetOrigName(array $d): string {
    if (preg_match('/Archivo original:\s*(.+)/i', $d['contenido'] ?? '', $m)) return trim($m[1]);
    return $d['titulo'];
}
function nubeIsFile(array $d): bool {
    return (bool)preg_match('/Ruta del archivo:/i', $d['contenido'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi nube — MatrixCoders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
<style>
:root{
  --green:#6B8F71;--green-d:#4a6b50;--green-light:#f0fdf4;
  --border:#e5e7eb;--soft:#f8fafc;--muted:#6b7280;--danger:#dc2626;
  --blue:#2563eb;--dark:#1B2336;--header-h:66px;
}
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Saira',sans-serif;background:#f1f5f9;color:var(--dark);margin:0;}

/* ── Shell ── */
.nube-shell{display:flex;height:100%;overflow:hidden;background:#fff;border-radius:14px;box-shadow:0 10px 25px rgba(0,0,0,.08);}
.nube-body{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;}

/* ── Right folders panel ── */
.nube-sidebar{width:220px;flex-shrink:0;background:#fff;border-left:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;border-radius:0 14px 14px 0;}
@media(max-width:900px){.nube-sidebar{display:none;}}

.ns-section{padding:16px 14px 4px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);}
.ns-link{display:flex;align-items:center;gap:9px;padding:8px 14px;font-size:.84rem;font-weight:600;color:var(--muted);text-decoration:none;border-radius:8px;margin:1px 8px;transition:all .15s;white-space:nowrap;overflow:hidden;}
.ns-link:hover,.ns-link.active{background:var(--green-light);color:var(--green-d);}
.ns-link .ns-count{margin-left:auto;font-size:.68rem;font-weight:700;background:var(--soft);border:1px solid var(--border);border-radius:99px;padding:0 6px;line-height:18px;flex-shrink:0;}
.ns-divider{height:1px;background:var(--border);margin:8px 14px;}
.ns-folder-link{display:flex;align-items:center;gap:9px;padding:7px 14px;font-size:.82rem;font-weight:500;color:var(--muted);text-decoration:none;border-radius:8px;margin:1px 8px;transition:all .15s;min-width:0;}
.ns-folder-link:hover,.ns-folder-link.active{background:var(--green-light);color:var(--green-d);}
.ns-folder-link.active{font-weight:700;}
.ns-folder-link .ns-count{margin-left:auto;font-size:.66rem;font-weight:700;background:var(--soft);border:1px solid var(--border);border-radius:99px;padding:0 5px;line-height:17px;flex-shrink:0;}
.ns-folder-name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ns-folder-del{opacity:0;flex-shrink:0;background:none;border:none;color:#f87171;cursor:pointer;padding:0 2px;line-height:1;transition:opacity .15s;}
.ns-folder-link:hover .ns-folder-del{opacity:1;}
.ns-new-folder{display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;background:none;border:1.5px dashed var(--border);width:calc(100% - 16px);margin:4px 8px;color:var(--muted);font-size:.8rem;font-weight:600;font-family:'Saira',sans-serif;cursor:pointer;transition:all .15s;}
.ns-new-folder:hover{border-color:var(--green);color:var(--green);background:var(--green-light);}

/* ── Topbar ── */
.nube-topbar{background:#fff;border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;flex-shrink:0;border-radius:14px 0 0 0;}
.nube-topbar-title{font-size:1.05rem;font-weight:800;color:var(--dark);flex-shrink:0;}
.nube-topbar-sub{font-size:.8rem;color:var(--muted);flex-shrink:0;}
.nube-topbar-actions{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;}
.nb-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 15px;border-radius:8px;font-size:.82rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;text-decoration:none;transition:all .15s;border:none;white-space:nowrap;}
.nb-btn-primary{background:var(--green);color:#fff;}
.nb-btn-primary:hover{background:var(--green-d);color:#fff;}
.nb-btn-secondary{background:#fff;color:var(--dark);border:1.5px solid var(--border);}
.nb-btn-secondary:hover{border-color:var(--green);color:var(--green);}

/* ── Stats ── */
.nube-stats{display:flex;background:#fff;border-bottom:1px solid var(--border);flex-shrink:0;}
.nube-stat{flex:1;padding:12px 16px;text-align:center;border-right:1px solid var(--border);}
.nube-stat:last-child{border-right:none;}
.nube-stat .sv{font-size:1.3rem;font-weight:800;color:var(--dark);line-height:1;}
.nube-stat .sl{font-size:.72rem;color:var(--muted);margin-top:2px;}
.nube-stat .sv.blue{color:var(--blue);}
.nube-stat .sv.green{color:var(--green-d);}

/* ── Content ── */
.nube-content{flex:1;overflow-y:auto;padding:20px 24px;}

/* ── Toolbar ── */
.nube-toolbar{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;}
.nube-search{position:relative;flex:1;min-width:200px;}
.nube-search input{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:7px 12px 7px 34px;font-family:'Saira',sans-serif;font-size:.84rem;background:#fff;color:var(--dark);outline:none;transition:border-color .15s;}
.nube-search input:focus{border-color:var(--green);}
.nube-search svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;}
.nube-view-toggle{display:flex;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:#fff;}
.nube-view-btn{padding:6px 10px;background:none;border:none;cursor:pointer;color:var(--muted);transition:all .15s;}
.nube-view-btn.active,.nube-view-btn:hover{background:var(--soft);color:var(--dark);}

/* ── Section label ── */
.nube-section-lbl{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;gap:8px;}
.nube-section-lbl::after{content:'';flex:1;height:1px;background:var(--border);}

/* ── Grid ── */
.nube-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px;margin-bottom:20px;}

/* ── Folder card (in content) ── */
.nube-folder-card{background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:12px 14px;display:flex;align-items:center;gap:10px;transition:all .15s;text-decoration:none;color:var(--dark);}
.nube-folder-card:hover{border-color:var(--green);box-shadow:0 4px 14px rgba(107,143,113,.1);transform:translateY(-1px);color:var(--dark);}
.nube-folder-card-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;background:#fffbeb;border:1.5px solid #fde68a;flex-shrink:0;}
.nube-folder-card-name{font-size:.84rem;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0;}

/* ── Doc card ── */
.nube-card{background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:8px;transition:all .15s;position:relative;cursor:pointer;text-decoration:none;color:var(--dark);}
.nube-card:hover{border-color:var(--green);box-shadow:0 4px 14px rgba(107,143,113,.1);transform:translateY(-1px);color:var(--dark);}
.nube-card-icon{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;border:1.5px solid;}
.nube-card-name{font-size:.85rem;font-weight:700;color:var(--dark);overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.3;}
.nube-card-meta{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.nube-card-badge{font-size:.67rem;font-weight:700;padding:2px 7px;border-radius:99px;text-transform:uppercase;letter-spacing:.3px;}
.nube-card-actions{display:flex;gap:5px;margin-top:2px;}
.nca{flex:1;display:flex;align-items:center;justify-content:center;gap:3px;padding:5px 4px;border-radius:7px;font-size:.72rem;font-weight:700;text-decoration:none;border:1.5px solid var(--border);background:#fff;color:var(--muted);font-family:'Saira',sans-serif;cursor:pointer;transition:all .15s;}
.nca:hover{border-color:var(--green);color:var(--green);}
.nca.danger:hover{border-color:#fca5a5;color:var(--danger);}
.nca.blue:hover{border-color:#93c5fd;color:var(--blue);}

/* ── List ── */
.nube-list{display:flex;flex-direction:column;gap:4px;margin-bottom:20px;}
.nube-list-row{display:flex;align-items:center;gap:12px;padding:10px 14px;background:#fff;border:1.5px solid var(--border);border-radius:10px;transition:all .15s;text-decoration:none;color:var(--dark);}
.nube-list-row:hover{border-color:var(--green);box-shadow:0 2px 8px rgba(107,143,113,.08);color:var(--dark);}
.nube-list-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;border:1.5px solid;flex-shrink:0;}
.nube-list-name{flex:1;font-size:.875rem;font-weight:700;color:var(--dark);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;}
.nube-list-meta{font-size:.75rem;color:var(--muted);white-space:nowrap;flex-shrink:0;}
.nube-list-actions{display:flex;gap:4px;flex-shrink:0;}

/* ── Empty ── */
.nube-empty{text-align:center;padding:3.5rem 1rem;}
.nube-empty svg{opacity:.2;display:block;margin:0 auto 16px;}
.nube-empty h3{font-size:.98rem;font-weight:700;margin:0 0 6px;}
.nube-empty p{font-size:.83rem;color:var(--muted);margin:0;}

/* ── Dropzone ── */
.nube-dropzone{border:2px dashed var(--border);border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:all .2s;background:#fff;position:relative;margin-bottom:20px;}
.nube-dropzone:hover,.nube-dropzone.drag-over{border-color:var(--green);background:var(--green-light);}
.nube-dropzone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.nube-dropzone-label{font-size:.85rem;color:var(--muted);font-weight:600;pointer-events:none;}
.nube-dropzone-label span{color:var(--green-d);text-decoration:underline;}

/* ── Upload bar ── */
.nube-upload-bar{display:none;background:var(--green-light);border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:.82rem;font-weight:600;color:var(--green-d);align-items:center;gap:10px;}
.nube-upload-bar.show{display:flex;}
.nube-upload-progress{flex:1;height:6px;background:#dcfce7;border-radius:99px;overflow:hidden;}
.nube-upload-progress-fill{height:100%;background:var(--green-d);border-radius:99px;transition:width .2s;}

/* ── Modals ── */
.nm-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);display:flex;align-items:center;justify-content:center;z-index:9100;opacity:0;pointer-events:none;transition:opacity .18s;}
.nm-overlay.show{opacity:1;pointer-events:all;}
.nm-box{background:#fff;border-radius:14px;padding:26px 28px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:nmIn .18s ease;}
@keyframes nmIn{from{transform:scale(.96);opacity:0}to{transform:scale(1);opacity:1}}
.nm-title{font-size:1rem;font-weight:800;margin:0 0 16px;color:var(--dark);}
.nm-label{display:block;font-size:.8rem;font-weight:700;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;}
.nm-input{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:8px 12px;font-family:'Saira',sans-serif;font-size:.88rem;color:var(--dark);outline:none;transition:border-color .15s;}
.nm-input:focus{border-color:var(--green);}
.nm-actions{display:flex;gap:8px;margin-top:18px;justify-content:flex-end;}
.nm-sub{font-size:.8rem;color:var(--muted);margin:4px 0 0;}

/* ── Flash ── */
.nube-flash{padding:10px 16px;border-radius:9px;font-size:.84rem;font-weight:600;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.nube-flash.success{background:#f0fdf4;border:1px solid #86efac;color:#166534;}
.nube-flash.error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}

/* ── Storage meter ── */
.nube-storage{display:flex;align-items:center;gap:10px;padding:12px 14px;border-top:1px solid var(--border);margin-top:auto;}
.nube-storage-bar{flex:1;height:5px;background:var(--border);border-radius:99px;overflow:hidden;}
.nube-storage-fill{height:100%;background:var(--green);border-radius:99px;}
.nube-storage-lbl{font-size:.72rem;color:var(--muted);font-weight:600;white-space:nowrap;}

/* ── Delete modal ── */
.nm-delete-icon{width:44px;height:44px;border-radius:12px;background:#fef2f2;border:1.5px solid #fecaca;display:flex;align-items:center;justify-content:center;margin-bottom:12px;}
.nm-box-title{font-size:.98rem;font-weight:800;color:var(--dark);margin:0 0 6px;}
.nm-box-sub{font-size:.82rem;color:var(--muted);margin:0 0 18px;line-height:1.5;}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="main-dashboard">
  <div class="mc-container">
    <div class="contenedor-dashboard-content">

      <?php require __DIR__ . '/../layout/sidebar.php'; ?>

      <!-- nube-shell: main body + right sidebar -->
      <div class="nube-shell" style="height:calc(100vh - var(--header-h,66px) - 62px);">

        <!-- ── Main body ── -->
        <div class="nube-body">

          <!-- Topbar -->
          <div class="nube-topbar">
            <?php if ($carpetaActual): ?>
            <a href="<?= BASE_URL ?>/index.php?url=nube" style="color:var(--muted);text-decoration:none;font-size:.85rem;display:flex;align-items:center;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <?php endif; ?>
            <span class="nube-topbar-title">
              <?php if ($carpetaActual): ?>
                📁 <?= htmlspecialchars($carpetaActual['nombre']) ?>
              <?php elseif (($_GET['tipo'] ?? '') === 'recurso'): ?>
                🔗 Recursos de cursos
              <?php elseif (($_GET['tipo'] ?? '') === 'propio'): ?>
                📂 Mis archivos
              <?php else: ?>
                ☁️ Mi nube
              <?php endif; ?>
            </span>
            <span class="nube-topbar-sub"><?= count($docsVisibles) ?> elemento<?= count($docsVisibles) !== 1 ? 's' : '' ?></span>
            <div class="nube-topbar-actions">
              <button class="nb-btn nb-btn-primary" onclick="openModal('nmUpload')">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Subir archivo
              </button>
            </div>
          </div>

          <!-- Stats strip -->
          <?php if (!$filtroCarpe && !($_GET['tipo'] ?? '')): ?>
          <div class="nube-stats">
            <div class="nube-stat"><div class="sv"><?= count($documentos) ?></div><div class="sl">Archivos</div></div>
            <div class="nube-stat"><div class="sv green"><?= count($carpetas) ?></div><div class="sl">Carpetas</div></div>
            <div class="nube-stat"><div class="sv blue"><?= count($recursosCurso) ?></div><div class="sl">Recursos</div></div>
            <div class="nube-stat"><div class="sv"><?= $storageUsedMB ?> MB</div><div class="sl">Almacenamiento</div></div>
          </div>
          <?php endif; ?>

          <!-- Content area -->
          <div class="nube-content" id="nubeContent">

            <?php if (!empty($flash)): ?>
            <div class="nube-flash <?= htmlspecialchars($flash['type'] ?? 'success') ?>">
              <?= $flash['type'] === 'success'
                ? '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>'
                : '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>' ?>
              <?= htmlspecialchars($flash['message'] ?? '') ?>
            </div>
            <?php endif; ?>

            <!-- Toolbar -->
            <div class="nube-toolbar">
              <div class="nube-search">
                <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                <input type="text" id="nubeSearch" placeholder="Buscar en mi nube…" oninput="filtrarNube(this.value)">
              </div>
              <div class="nube-view-toggle">
                <button class="nube-view-btn active" id="btnGrid" onclick="setView('grid')" title="Cuadrícula">
                  <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                </button>
                <button class="nube-view-btn" id="btnList" onclick="setView('list')" title="Lista">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
              </div>
              <?php if (!empty($carpetas)): ?>
              <select id="filterCarpeta" class="nm-input" style="width:auto;padding:7px 10px;" onchange="filtrarPorCarpeta(this.value)">
                <option value="">Todas las carpetas</option>
                <?php foreach ($carpetas as $carp): ?>
                <option value="<?= $carp['id'] ?>" <?= $filtroCarpe === (int)$carp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($carp['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php endif; ?>
            </div>

            <!-- Folder cards (only when showing all, no folder filter) -->
            <?php if (!$filtroCarpe && !($_GET['tipo'] ?? '') && !empty($carpetas)): ?>
            <div id="carpetasSection">
              <div class="nube-section-lbl">Carpetas</div>
              <div class="nube-grid" style="margin-bottom:24px;">
                <?php foreach ($carpetas as $carp): ?>
                <a href="<?= BASE_URL ?>/index.php?url=nube&carpeta=<?= $carp['id'] ?>" class="nube-folder-card">
                  <div class="nube-folder-card-icon">📁</div>
                  <div style="flex:1;min-width:0;">
                    <div class="nube-folder-card-name"><?= htmlspecialchars($carp['nombre']) ?></div>
                    <div style="font-size:.69rem;color:var(--muted);margin-top:2px;"><?= (int)($carp['total_documentos'] ?? 0) ?> archivo<?= (int)($carp['total_documentos'] ?? 0) !== 1 ? 's' : '' ?></div>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

            <?php if (empty($docsVisibles)): ?>
            <div class="nube-empty">
              <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
              <h3>Sin archivos aquí todavía</h3>
              <p>Sube un archivo o guarda recursos desde tus lecciones.</p>
              <div style="display:flex;gap:10px;justify-content:center;margin-top:18px;">
                <button class="nb-btn nb-btn-primary" onclick="openModal('nmUpload')">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                  Subir archivo
                </button>
              </div>
            </div>

            <?php else: ?>

            <?php
            $tipoFiltro = $_GET['tipo'] ?? '';
            $docsRender = $docsVisibles;
            if ($tipoFiltro === 'recurso') $docsRender = $recursosCurso;
            if ($tipoFiltro === 'propio')  $docsRender = $docsPropios;

            $bySection = [];
            foreach ($docsRender as $doc) {
                $sec = str_contains($doc['contenido'] ?? '', 'Recurso del curso') ? 'Recursos de cursos' : 'Mis archivos';
                $bySection[$sec][] = $doc;
            }
            if ($tipoFiltro === 'recurso') $bySection = ['Recursos de cursos' => $docsRender];
            if ($tipoFiltro === 'propio')  $bySection = ['Mis archivos' => $docsRender];
            ?>

            <!-- Grid view -->
            <div id="viewGrid">
              <?php foreach ($bySection as $secLabel => $secDocs): ?>
              <?php if (count($bySection) > 1): ?><div class="nube-section-lbl"><?= $secLabel ?></div><?php endif; ?>
              <div class="nube-grid">
                <?php foreach ($secDocs as $doc):
                  $ti      = nubeTypeInfo($doc);
                  $fileUrl = nubeGetFileUrl($doc);
                  $isFile  = nubeIsFile($doc);
                  $orig    = nubeGetOrigName($doc);
                  $docUrl  = BASE_URL . '/index.php?url=documento&id=' . (int)$doc['id'];
                ?>
                <div class="nube-card doc-item"
                   data-nombre="<?= htmlspecialchars(strtolower($doc['titulo'] . ' ' . $orig)) ?>"
                   data-carpeta="<?= (int)($doc['carpeta_id'] ?? 0) ?>"
                   onclick="window.location='<?= $docUrl ?>'" style="cursor:pointer;">
                  <div style="display:flex;align-items:flex-start;gap:10px">
                    <div class="nube-card-icon" style="background:<?= $ti['bg'] ?>;border-color:<?= $ti['border'] ?>"><?= $ti['icon'] ?></div>
                    <div style="flex:1;min-width:0">
                      <div class="nube-card-name" title="<?= htmlspecialchars($doc['titulo']) ?>"><?= htmlspecialchars($doc['titulo']) ?></div>
                      <?php if (!empty($doc['carpeta_nombre'])): ?>
                      <div style="font-size:.7rem;color:var(--muted);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">📁 <?= htmlspecialchars($doc['carpeta_nombre']) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="nube-card-meta">
                    <span class="nube-card-badge" style="background:<?= $ti['bg'] ?>;color:<?= $ti['color'] ?>;border:1px solid <?= $ti['border'] ?>;"><?= $ti['label'] ?></span>
                  </div>
                  <div class="nube-card-actions" onclick="event.stopPropagation()">
                    <?php if ($fileUrl): ?>
                    <a href="<?= htmlspecialchars($fileUrl) ?>" <?= str_starts_with($fileUrl, 'http') ? 'target="_blank"' : '' ?> class="nca blue" title="<?= $isFile ? 'Descargar' : 'Abrir' ?>" onclick="event.stopPropagation()">
                      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                      <?= $isFile ? 'Descargar' : 'Abrir' ?>
                    </a>
                    <?php endif; ?>
                    <button type="button" class="nca" title="Mover" onclick='event.stopPropagation();openMoverModal(<?= (int)$doc['id'] ?>, "<?= htmlspecialchars(addslashes($doc['titulo'])) ?>", <?= (int)($doc['carpeta_id'] ?? 0) ?>)'>
                      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </button>
                    <button type="button" class="nca danger" title="Eliminar" onclick="event.stopPropagation();pedirEliminarDoc(<?= (int)$doc['id'] ?>, '<?= htmlspecialchars(addslashes($doc['titulo'])) ?>')">
                      <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- List view -->
            <div id="viewList" style="display:none;">
              <?php foreach ($bySection as $secLabel => $secDocs): ?>
              <?php if (count($bySection) > 1): ?><div class="nube-section-lbl"><?= $secLabel ?></div><?php endif; ?>
              <div class="nube-list">
                <?php foreach ($secDocs as $doc):
                  $ti      = nubeTypeInfo($doc);
                  $fileUrl = nubeGetFileUrl($doc);
                  $isFile  = nubeIsFile($doc);
                  $docUrl  = BASE_URL . '/index.php?url=documento&id=' . (int)$doc['id'];
                ?>
                <div class="nube-list-row doc-item"
                   data-nombre="<?= htmlspecialchars(strtolower($doc['titulo'])) ?>"
                   data-carpeta="<?= (int)($doc['carpeta_id'] ?? 0) ?>"
                   onclick="window.location='<?= $docUrl ?>'" style="cursor:pointer;">
                  <div class="nube-list-icon" style="background:<?= $ti['bg'] ?>;border-color:<?= $ti['border'] ?>"><?= $ti['icon'] ?></div>
                  <div class="nube-list-name" title="<?= htmlspecialchars($doc['titulo']) ?>"><?= htmlspecialchars($doc['titulo']) ?></div>
                  <?php if (!empty($doc['carpeta_nombre'])): ?>
                  <div class="nube-list-meta">📁 <?= htmlspecialchars($doc['carpeta_nombre']) ?></div>
                  <?php endif; ?>
                  <span class="nube-card-badge" style="background:<?= $ti['bg'] ?>;color:<?= $ti['color'] ?>;border:1px solid <?= $ti['border'] ?>;"><?= $ti['label'] ?></span>
                  <div class="nube-list-actions" onclick="event.stopPropagation()">
                    <?php if ($fileUrl): ?>
                    <a href="<?= htmlspecialchars($fileUrl) ?>" <?= str_starts_with($fileUrl, 'http') ? 'target="_blank"' : '' ?> class="nca blue" style="padding:5px 8px;" onclick="event.stopPropagation()">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    <?php endif; ?>
                    <button type="button" class="nca" style="padding:5px 8px;" onclick='event.stopPropagation();openMoverModal(<?= (int)$doc['id'] ?>, "<?= htmlspecialchars(addslashes($doc['titulo'])) ?>", <?= (int)($doc['carpeta_id'] ?? 0) ?>)'>
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </button>
                    <button type="button" class="nca danger" style="padding:5px 8px;" onclick="event.stopPropagation();pedirEliminarDoc(<?= (int)$doc['id'] ?>, '<?= htmlspecialchars(addslashes($doc['titulo'])) ?>')">
                      <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endforeach; ?>
            </div>

            <?php endif; ?>
          </div><!-- /nube-content -->
        </div><!-- /nube-body -->

        <!-- ── RIGHT folders sidebar ── -->
        <aside class="nube-sidebar">
          <div class="ns-section" style="padding-top:20px;">Mi nube</div>

          <a href="<?= BASE_URL ?>/index.php?url=nube" class="ns-link <?= !$filtroCarpe && !($_GET['tipo'] ?? '') ? 'active' : '' ?>">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
            Todos los archivos
            <span class="ns-count"><?= count($documentos) ?></span>
          </a>
          <a href="<?= BASE_URL ?>/index.php?url=nube&tipo=recurso" class="ns-link <?= ($_GET['tipo'] ?? '') === 'recurso' ? 'active' : '' ?>">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Recursos
            <span class="ns-count"><?= count(array_filter($documentos, fn($d) => str_contains($d['contenido'] ?? '', 'Recurso del curso'))) ?></span>
          </a>
          <a href="<?= BASE_URL ?>/index.php?url=nube&tipo=propio" class="ns-link <?= ($_GET['tipo'] ?? '') === 'propio' ? 'active' : '' ?>">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Mis archivos
            <span class="ns-count"><?= count(array_filter($documentos, fn($d) => !str_contains($d['contenido'] ?? '', 'Recurso del curso'))) ?></span>
          </a>

          <?php if (!empty($carpetas)): ?>
          <div class="ns-divider"></div>
          <div class="ns-section">Carpetas</div>
          <?php foreach ($carpetas as $carp): ?>
          <div style="display:flex;align-items:center;margin:1px 8px;">
            <a href="?url=nube&carpeta=<?= $carp['id'] ?>" class="ns-folder-link <?= $filtroCarpe === (int)$carp['id'] ? 'active' : '' ?>" style="flex:1;min-width:0;margin:0;">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
              <span class="ns-folder-name"><?= htmlspecialchars($carp['nombre']) ?></span>
              <span class="ns-count"><?= (int)($carp['total_documentos'] ?? 0) ?></span>
            </a>
            <button class="ns-folder-del" title="Eliminar carpeta" onclick="pedirEliminarCarpeta(<?= $carp['id'] ?>, '<?= htmlspecialchars(addslashes($carp['nombre'])) ?>')">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>

          <div class="ns-divider"></div>
          <button class="ns-new-folder" onclick="openModal('nmCarpeta')">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Nueva carpeta
          </button>

          <div class="nube-storage">
            <svg width="13" height="13" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path stroke-linecap="round" d="M21 12c0 1.657-4.03 3-9 3s-9-1.343-9-3M3 5v14c0 1.657 4.03 3 9 3s9-1.343 9-3V5"/></svg>
            <div class="nube-storage-bar"><div class="nube-storage-fill" style="width:<?= min(100, $storageUsedMB * 2) ?>%"></div></div>
            <span class="nube-storage-lbl"><?= $storageUsedMB ?> MB</span>
          </div>
        </aside>

      </div><!-- /nube-shell -->
    </div>
  </div>
</main>

<!-- New folder modal -->
<div class="nm-overlay" id="nmCarpeta" onclick="if(event.target===this)closeModal('nmCarpeta')">
  <div class="nm-box" style="max-width:380px">
    <div class="nm-title">📁 Nueva carpeta</div>
    <form method="POST" action="<?= BASE_URL ?>/index.php?url=mis-documentos">
      <input type="hidden" name="dashboard_action" value="create_folder">
      <label class="nm-label">Nombre</label>
      <input type="text" class="nm-input" name="folder_name" id="nmFolderName" placeholder="Ej: Mis apuntes de PHP" required>
      <div class="nm-actions">
        <button type="button" class="nb-btn nb-btn-secondary" onclick="closeModal('nmCarpeta')">Cancelar</button>
        <button type="submit" class="nb-btn nb-btn-primary">Crear carpeta</button>
      </div>
    </form>
  </div>
</div>

<!-- Upload modal -->
<div class="nm-overlay" id="nmUpload" onclick="if(event.target===this)closeModal('nmUpload')">
  <div class="nm-box" style="max-width:460px">
    <div class="nm-title">☁️ Subir archivo</div>
    <div id="uploadBar" class="nube-upload-bar">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin .8s linear infinite;flex-shrink:0"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg>
      <div class="nube-upload-progress"><div class="nube-upload-progress-fill" id="uploadFill" style="width:0%"></div></div>
      <span id="uploadPct">0%</span>
    </div>
    <div class="nube-dropzone" id="dropzone">
      <input type="file" id="uploadFile" accept=".pdf,.doc,.docx,.zip,.rar,.txt,.png,.jpg,.jpeg,.gif,.webp,.mp4,.mp3,.xlsx,.pptx" onchange="previewFile(this)">
      <div class="nube-dropzone-label" id="dropzoneLabel">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 8px;opacity:.4"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        Arrastra aquí o <span>haz clic para seleccionar</span><br>
        <small style="font-size:.75rem;font-weight:500;opacity:.7">PDF, Word, ZIP, imágenes, vídeo · Máx. 50 MB</small>
      </div>
    </div>
    <div id="uploadPreview" style="display:none;padding:8px 12px;background:var(--soft);border:1.5px solid var(--border);border-radius:8px;font-size:.84rem;font-weight:600;margin-top:8px;color:var(--dark);"></div>
    <div style="margin-top:12px;">
      <label class="nm-label">Guardar en carpeta (opcional)</label>
      <select class="nm-input" id="uploadCarpeta">
        <option value="">Sin carpeta</option>
        <?php foreach ($carpetas as $carp): ?>
        <option value="<?= $carp['id'] ?>" <?= $filtroCarpe === (int)$carp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($carp['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-top:10px;">
      <label class="nm-label">Nombre del archivo (opcional)</label>
      <input type="text" class="nm-input" id="uploadNombre" placeholder="Se usa el nombre del archivo por defecto">
    </div>
    <div class="nm-actions">
      <button type="button" class="nb-btn nb-btn-secondary" onclick="closeModal('nmUpload')">Cancelar</button>
      <button type="button" class="nb-btn nb-btn-primary" id="btnUpload" onclick="subirArchivo()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        Subir
      </button>
    </div>
  </div>
</div>

<!-- Move modal -->
<div class="nm-overlay" id="nmMover" onclick="if(event.target===this)closeModal('nmMover')">
  <div class="nm-box" style="max-width:360px">
    <div class="nm-title">📁 Mover archivo</div>
    <p class="nm-sub" id="nmMoverNombre" style="margin:0 0 14px;font-size:.85rem;color:var(--muted);"></p>
    <input type="hidden" id="nmMoverId">
    <label class="nm-label">Mover a carpeta</label>
    <select class="nm-input" id="nmMoverCarpeta">
      <option value="">Sin carpeta (raíz)</option>
      <?php foreach ($carpetas as $carp): ?>
      <option value="<?= $carp['id'] ?>"><?= htmlspecialchars($carp['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="nm-actions">
      <button type="button" class="nb-btn nb-btn-secondary" onclick="closeModal('nmMover')">Cancelar</button>
      <button type="button" class="nb-btn nb-btn-primary" onclick="moverDocumento()">Mover</button>
    </div>
  </div>
</div>

<!-- Delete folder modal -->
<div class="nm-overlay" id="nmEliminarCarpeta" onclick="if(event.target===this)closeModal('nmEliminarCarpeta')">
  <div class="nm-box" style="max-width:400px">
    <div class="nm-delete-icon"><svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
    <p class="nm-box-title">Eliminar carpeta</p>
    <p class="nm-box-sub" id="nmEliminarCarpetaNombre"></p>
    <p class="nm-box-sub" style="margin-top:-10px;">Los archivos dentro <strong>no se eliminarán</strong> — quedarán sin carpeta asignada.</p>
    <input type="hidden" id="nmEliminarCarpetaId">
    <div class="nm-actions">
      <button type="button" class="nb-btn nb-btn-secondary" onclick="closeModal('nmEliminarCarpeta')">Cancelar</button>
      <button id="btnConfirmarEliminarCarpeta" type="button" class="nb-btn" style="background:var(--danger);color:#fff;border:none;" onclick="confirmarEliminarCarpeta()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Eliminar carpeta
      </button>
    </div>
  </div>
</div>

<!-- Delete doc modal -->
<div class="nm-overlay" id="nmEliminarDoc" onclick="if(event.target===this)closeModal('nmEliminarDoc')">
  <div class="nm-box" style="max-width:380px">
    <div class="nm-delete-icon"><svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
    <p class="nm-box-title">Eliminar archivo</p>
    <p class="nm-box-sub" id="nmEliminarDocNombre"></p>
    <p class="nm-box-sub" style="margin-top:-10px;">Esta acción no se puede deshacer.</p>
    <input type="hidden" id="nmEliminarDocId">
    <div class="nm-actions">
      <button type="button" class="nb-btn nb-btn-secondary" onclick="closeModal('nmEliminarDoc')">Cancelar</button>
      <button id="btnConfirmarEliminar" type="button" class="nb-btn" style="background:var(--danger);color:#fff;border:none;" onclick="confirmarEliminarDoc()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Eliminar
      </button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';

function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.add('show'); m.querySelector('input:not([type=hidden]),select')?.focus(); }
}
function closeModal(id) { document.getElementById(id)?.classList.remove('show'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.nm-overlay.show').forEach(m => m.classList.remove('show')); });

let currentView = localStorage.getItem('nube_view') || 'grid';
function setView(v) {
  currentView = v;
  localStorage.setItem('nube_view', v);
  document.getElementById('viewGrid').style.display = v === 'grid' ? '' : 'none';
  document.getElementById('viewList').style.display = v === 'list' ? '' : 'none';
  document.getElementById('btnGrid').classList.toggle('active', v === 'grid');
  document.getElementById('btnList').classList.toggle('active', v === 'list');
}
setView(currentView);

function filtrarNube(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('.doc-item').forEach(el => {
    el.style.display = (el.dataset.nombre || '').includes(q) ? '' : 'none';
  });
  document.querySelectorAll('.nube-grid, .nube-list').forEach(container => {
    const hasVisible = [...container.querySelectorAll('.doc-item')].some(el => el.style.display !== 'none');
    container.style.display = hasVisible ? '' : 'none';
    const prev = container.previousElementSibling;
    if (prev?.classList.contains('nube-section-lbl')) prev.style.display = hasVisible ? '' : 'none';
  });
  const cs = document.getElementById('carpetasSection');
  if (cs) cs.style.display = q ? 'none' : '';
}

function filtrarPorCarpeta(val) {
  const params = new URLSearchParams(window.location.search);
  let url = BASE_URL + '/index.php?url=nube';
  if (val) url += '&carpeta=' + encodeURIComponent(val);
  const tipo = params.get('tipo');
  if (tipo) url += '&tipo=' + encodeURIComponent(tipo);
  window.location.href = url;
}

function previewFile(input) {
  const file = input.files[0]; if (!file) return;
  const prev = document.getElementById('uploadPreview');
  const icons = {pdf:'📄',doc:'📝',docx:'📝',zip:'🗜️',rar:'🗜️',png:'🖼️',jpg:'🖼️',jpeg:'🖼️',webp:'🖼️',gif:'🖼️',mp4:'🎬',mp3:'🎵',txt:'📋',xlsx:'📊',pptx:'📊'};
  const ext = file.name.split('.').pop().toLowerCase();
  prev.style.display = 'flex';
  prev.innerHTML = `<span style="font-size:1.2rem">${icons[ext]||'📎'}</span><div style="flex:1;min-width:0"><div style="font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${file.name}</div><div style="font-size:.75rem;color:var(--muted)">${(file.size/1048576).toFixed(1)} MB</div></div>`;
  if (!document.getElementById('uploadNombre').value)
    document.getElementById('uploadNombre').value = file.name.replace(/\.[^.]+$/, '');
}

const dz = document.getElementById('dropzone');
if (dz) {
  dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
  dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('drag-over'); const fi = document.getElementById('uploadFile'); fi.files = e.dataTransfer.files; previewFile(fi); });
}

async function subirArchivo() {
  const fileInput = document.getElementById('uploadFile');
  const file = fileInput.files[0];
  if (!file) { alert('Selecciona un archivo primero.'); return; }
  if (file.size > 52428800) { alert('El archivo supera el límite de 50 MB.'); return; }
  const nombre = document.getElementById('uploadNombre').value.trim() || file.name.replace(/\.[^.]+$/, '');
  const carpetaId = document.getElementById('uploadCarpeta').value;
  const bar = document.getElementById('uploadBar'), fill = document.getElementById('uploadFill'), pct = document.getElementById('uploadPct'), btn = document.getElementById('btnUpload');
  bar.classList.add('show'); btn.disabled = true;
  const fd = new FormData();
  fd.append('archivo', file); fd.append('nombre', nombre); fd.append('carpeta_id', carpetaId); fd.append('nube_action', 'subir_archivo');
  const xhr = new XMLHttpRequest();
  xhr.upload.onprogress = e => { if (e.lengthComputable) { const p = Math.round(e.loaded/e.total*100); fill.style.width=p+'%'; pct.textContent=p+'%'; } };
  xhr.onload = () => { bar.classList.remove('show'); btn.disabled = false; try { const res = JSON.parse(xhr.responseText); if (res.ok) { closeModal('nmUpload'); window.location.reload(); } else alert(res.error||'Error al subir.'); } catch(e) { alert('Error inesperado.'); } };
  xhr.onerror = () => { bar.classList.remove('show'); btn.disabled = false; alert('Error de red.'); };
  xhr.open('POST', BASE_URL + '/index.php?url=nube-api');
  xhr.send(fd);
}

function openMoverModal(id, nombre, carpetaActual) {
  document.getElementById('nmMoverId').value = id;
  document.getElementById('nmMoverNombre').textContent = '"' + nombre + '"';
  document.getElementById('nmMoverCarpeta').value = carpetaActual || '';
  openModal('nmMover');
}
async function moverDocumento() {
  const id = document.getElementById('nmMoverId').value;
  const carpetaId = document.getElementById('nmMoverCarpeta').value;
  const res = await fetch(BASE_URL + '/index.php?url=nube-api', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({nube_action:'mover_documento', id, carpeta_id: carpetaId||null}) }).then(r=>r.json());
  if (res.ok) { closeModal('nmMover'); window.location.reload(); } else alert(res.error||'Error al mover.');
}

function pedirEliminarDoc(id, nombre) {
  document.getElementById('nmEliminarDocId').value = id;
  document.getElementById('nmEliminarDocNombre').textContent = '"' + nombre + '"';
  openModal('nmEliminarDoc');
}
async function confirmarEliminarDoc() {
  const id = document.getElementById('nmEliminarDocId').value;
  const btn = document.getElementById('btnConfirmarEliminar');
  if (btn) { btn.disabled=true; btn.textContent='Eliminando…'; }
  try {
    const res = await fetch(BASE_URL + '/index.php?url=nube-api', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({nube_action:'eliminar_documento', id}) }).then(r=>r.json());
    if (res.ok) { closeModal('nmEliminarDoc'); window.location.reload(); }
    else { if(btn){btn.disabled=false;btn.textContent='Eliminar';} alert(res.error||'Error al eliminar.'); }
  } catch(e) { if(btn){btn.disabled=false;btn.textContent='Eliminar';} alert('Error de red.'); }
}

function pedirEliminarCarpeta(id, nombre) {
  document.getElementById('nmEliminarCarpetaId').value = id;
  document.getElementById('nmEliminarCarpetaNombre').textContent = '"' + nombre + '"';
  openModal('nmEliminarCarpeta');
}
async function confirmarEliminarCarpeta() {
  const id = document.getElementById('nmEliminarCarpetaId').value;
  const btn = document.getElementById('btnConfirmarEliminarCarpeta');
  if (btn) { btn.disabled=true; btn.textContent='Eliminando…'; }
  try {
    const res = await fetch(BASE_URL + '/index.php?url=nube-api', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({nube_action:'eliminar_carpeta', id}) }).then(r=>r.json());
    if (res.ok) { closeModal('nmEliminarCarpeta'); window.location.href = BASE_URL + '/index.php?url=nube'; }
    else { if(btn){btn.disabled=false;btn.textContent='Eliminar carpeta';} alert(res.error||'Error al eliminar.'); }
  } catch(e) { if(btn){btn.disabled=false;btn.textContent='Eliminar carpeta';} alert('Error de red.'); }
}
</script>
</body>
</html>
