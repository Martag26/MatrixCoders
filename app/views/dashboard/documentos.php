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

// Calculate storage used
$storageUsed = 0;
$uploadsDir = __DIR__ . '/../../../public/uploads/documentos/';
foreach ($documentos as $d) {
    if (preg_match('/Ruta del archivo:\s*(\S+)/i', $d['contenido'] ?? '', $m)) {
        $ruta = ltrim($m[1], '/');
        $abs  = __DIR__ . '/../../../public/' . $ruta;
        if (file_exists($abs)) $storageUsed += filesize($abs);
    }
}
$storageUsedMB = round($storageUsed / 1048576, 1);

function nubeTypeInfo(array $d): array {
    $t = $d['titulo'] ?? '';
    $c = $d['contenido'] ?? '';
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
    if (preg_match('/Ruta del archivo:\s*(\S+)/i', $d['contenido'] ?? '', $m)) return BASE_URL . '/' . ltrim($m[1], '/');
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
<style>
:root{
  --navy:#0f172a;--dark:#1B2336;--green:#6B8F71;--green-d:#4a6b50;--green-light:#f0fdf4;
  --border:#e5e7eb;--soft:#f8fafc;--muted:#6b7280;--danger:#dc2626;--header-h:66px;
  --blue:#2563eb;--blue-light:#eff6ff;
}
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Saira',sans-serif;background:#f1f5f9;color:var(--dark);margin:0;}

/* ── Layout ── */
.nube-shell{display:flex;height:calc(100vh - var(--header-h));overflow:hidden;}
.nube-sidebar{width:240px;flex-shrink:0;background:#fff;border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;}
.nube-body{flex:1;display:flex;flex-direction:column;overflow:hidden;}
@media(max-width:900px){.nube-sidebar{display:none;}.nube-body{width:100%;}}

/* ── Sidebar ── */
.ns-section{padding:16px 14px 4px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);}
.ns-link{display:flex;align-items:center;gap:9px;padding:8px 14px;font-size:.875rem;font-weight:600;color:var(--muted);text-decoration:none;border-radius:8px;margin:1px 8px;transition:all .15s;white-space:nowrap;overflow:hidden;}
.ns-link:hover{background:var(--green-light);color:var(--green-d);}
.ns-link.active{background:var(--green-light);color:var(--green-d);}
.ns-link .ns-count{margin-left:auto;font-size:.7rem;font-weight:700;background:var(--soft);border:1px solid var(--border);border-radius:99px;padding:0 7px;line-height:19px;flex-shrink:0;}
.ns-divider{height:1px;background:var(--border);margin:8px 14px;}
.ns-folder-link{display:flex;align-items:center;gap:9px;padding:7px 14px;font-size:.85rem;font-weight:500;color:var(--muted);text-decoration:none;border-radius:8px;margin:1px 8px;transition:all .15s;min-width:0;}
.ns-folder-link:hover{background:var(--green-light);color:var(--green-d);}
.ns-folder-link.active{background:var(--green-light);color:var(--green-d);font-weight:700;}
.ns-folder-link .ns-count{margin-left:auto;font-size:.68rem;font-weight:700;background:var(--soft);border:1px solid var(--border);border-radius:99px;padding:0 6px;line-height:18px;flex-shrink:0;}
.ns-folder-name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ns-folder-del{opacity:0;flex-shrink:0;background:none;border:none;color:#f87171;cursor:pointer;padding:0 2px;line-height:1;transition:opacity .15s;}
.ns-folder-link:hover .ns-folder-del{opacity:1;}
.ns-new-folder{display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;background:none;border:1.5px dashed var(--border);width:calc(100% - 16px);margin:4px 8px;color:var(--muted);font-size:.82rem;font-weight:600;font-family:'Saira',sans-serif;cursor:pointer;transition:all .15s;}
.ns-new-folder:hover{border-color:var(--green);color:var(--green);background:var(--green-light);}

/* ── Topbar ── */
.nube-topbar{background:#fff;border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;flex-shrink:0;}
.nube-topbar-title{font-size:1.05rem;font-weight:800;color:var(--dark);flex-shrink:0;}
.nube-topbar-sub{font-size:.8rem;color:var(--muted);flex-shrink:0;}
.nube-topbar-actions{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;}
.nb-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 15px;border-radius:8px;font-size:.82rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;text-decoration:none;transition:all .15s;border:none;white-space:nowrap;}
.nb-btn-primary{background:var(--green);color:#fff;}
.nb-btn-primary:hover{background:var(--green-d);color:#fff;}
.nb-btn-secondary{background:#fff;color:var(--dark);border:1.5px solid var(--border);}
.nb-btn-secondary:hover{border-color:var(--green);color:var(--green);}
.nb-btn-danger{background:#fff;color:var(--danger);border:1.5px solid #fca5a5;}
.nb-btn-danger:hover{background:#fef2f2;}

/* ── Stats strip ── */
.nube-stats{display:flex;background:#fff;border-bottom:1px solid var(--border);flex-shrink:0;}
.nube-stat{flex:1;padding:12px 16px;text-align:center;border-right:1px solid var(--border);}
.nube-stat:last-child{border-right:none;}
.nube-stat .sv{font-size:1.3rem;font-weight:800;color:var(--dark);line-height:1;}
.nube-stat .sl{font-size:.72rem;color:var(--muted);margin-top:2px;}
.nube-stat .sv.blue{color:var(--blue);}
.nube-stat .sv.green{color:var(--green-d);}

/* ── Content area ── */
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

/* ── Grid view ── */
.nube-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:24px;}
.nube-card{background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:8px;transition:all .15s;position:relative;cursor:default;}
.nube-card:hover{border-color:var(--green);box-shadow:0 4px 14px rgba(107,143,113,.1);transform:translateY(-1px);}
.nube-card-icon{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;border:1.5px solid;}
.nube-card-name{font-size:.85rem;font-weight:700;color:var(--dark);overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;line-height:1.3;}
.nube-card-meta{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.nube-card-badge{font-size:.67rem;font-weight:700;padding:2px 7px;border-radius:99px;text-transform:uppercase;letter-spacing:.3px;}
.nube-card-folder{font-size:.72rem;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:120px;}
.nube-card-actions{display:flex;gap:5px;margin-top:2px;}
.nca{flex:1;display:flex;align-items:center;justify-content:center;gap:3px;padding:5px 4px;border-radius:7px;font-size:.72rem;font-weight:700;text-decoration:none;border:1.5px solid var(--border);background:#fff;color:var(--muted);font-family:'Saira',sans-serif;cursor:pointer;transition:all .15s;}
.nca:hover{border-color:var(--green);color:var(--green);}
.nca.danger:hover{border-color:#fca5a5;color:var(--danger);}
.nca.blue:hover{border-color:#93c5fd;color:var(--blue);}

/* ── List view ── */
.nube-list{display:flex;flex-direction:column;gap:4px;margin-bottom:24px;}
.nube-list-row{display:flex;align-items:center;gap:12px;padding:10px 14px;background:#fff;border:1.5px solid var(--border);border-radius:10px;transition:all .15s;}
.nube-list-row:hover{border-color:var(--green);box-shadow:0 2px 8px rgba(107,143,113,.08);}
.nube-list-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;border:1.5px solid;flex-shrink:0;}
.nube-list-name{flex:1;font-size:.875rem;font-weight:700;color:var(--dark);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;}
.nube-list-meta{font-size:.75rem;color:var(--muted);white-space:nowrap;flex-shrink:0;}
.nube-list-actions{display:flex;gap:4px;flex-shrink:0;}

/* ── Empty state ── */
.nube-empty{text-align:center;padding:3.5rem 1rem;}
.nube-empty svg{opacity:.2;display:block;margin:0 auto 16px;}
.nube-empty h3{font-size:.98rem;font-weight:700;margin:0 0 6px;}
.nube-empty p{font-size:.83rem;color:var(--muted);margin:0;}

/* ── Upload drop zone ── */
.nube-dropzone{border:2px dashed var(--border);border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:all .2s;background:#fff;position:relative;margin-bottom:20px;}
.nube-dropzone:hover,.nube-dropzone.drag-over{border-color:var(--green);background:var(--green-light);}
.nube-dropzone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.nube-dropzone-label{font-size:.85rem;color:var(--muted);font-weight:600;pointer-events:none;}
.nube-dropzone-label span{color:var(--green-d);text-decoration:underline;}

/* ── Upload progress ── */
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

/* ── Storage bar ── */
.nube-storage{display:flex;align-items:center;gap:10px;padding:12px 14px;border-top:1px solid var(--border);margin-top:auto;background:#fff;}
.nube-storage-bar{flex:1;height:5px;background:var(--border);border-radius:99px;overflow:hidden;}
.nube-storage-fill{height:100%;background:var(--green);border-radius:99px;}
.nube-storage-lbl{font-size:.72rem;color:var(--muted);font-weight:600;white-space:nowrap;}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main style="height:calc(100vh - var(--header-h,66px));overflow:hidden;background:#f1f5f9;">
  <div style="display:flex;height:100%;max-width:1400px;margin:0 auto;">

    <?php require __DIR__ . '/../layout/sidebar.php'; ?>

    <div class="nube-shell" style="flex:1;min-width:0;">

      <!-- ── Folders sidebar ── -->
      <aside class="nube-sidebar">
        <div class="ns-section" style="padding-top:20px;">Mi nube</div>

        <a href="<?= BASE_URL ?>/index.php?url=nube" class="ns-link <?= !$filtroCarpe ? 'active' : '' ?>">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
          Todos los archivos
          <span class="ns-count"><?= count($documentos) ?></span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?url=nube&tipo=recurso" class="ns-link <?= ($_GET['tipo'] ?? '') === 'recurso' ? 'active' : '' ?>">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          Recursos de cursos
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

        <!-- Storage meter -->
        <div class="nube-storage">
          <svg width="13" height="13" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path stroke-linecap="round" d="M21 12c0 1.657-4.03 3-9 3s-9-1.343-9-3M3 5v14c0 1.657 4.03 3 9 3s9-1.343 9-3V5"/></svg>
          <div class="nube-storage-bar"><div class="nube-storage-fill" style="width:<?= min(100, $storageUsedMB * 2) ?>%"></div></div>
          <span class="nube-storage-lbl"><?= $storageUsedMB ?> MB</span>
        </div>
      </aside>

      <!-- ── Main body ── -->
      <div class="nube-body">

        <!-- Topbar -->
        <div class="nube-topbar">
          <?php if ($carpetaActual): ?>
          <a href="<?= BASE_URL ?>/index.php?url=nube" style="color:var(--muted);text-decoration:none;font-size:.85rem;">
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
            <button class="nb-btn nb-btn-secondary" onclick="openModal('nmUpload')">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              Subir archivo
            </button>
            <a href="<?= BASE_URL ?>/index.php?url=nuevo-documento" class="nb-btn nb-btn-primary">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
              Nueva nota
            </a>
          </div>
        </div>

        <!-- Stats (only on root view) -->
        <?php if (!$filtroCarpe && !($_GET['tipo'] ?? '')): ?>
        <div class="nube-stats">
          <div class="nube-stat"><div class="sv"><?= count($documentos) ?></div><div class="sl">Archivos</div></div>
          <div class="nube-stat"><div class="sv green"><?= count($carpetas) ?></div><div class="sl">Carpetas</div></div>
          <div class="nube-stat"><div class="sv blue"><?= count($recursosCurso) ?></div><div class="sl">Recursos de cursos</div></div>
          <div class="nube-stat"><div class="sv"><?= $storageUsedMB ?> MB</div><div class="sl">Almacenamiento</div></div>
        </div>
        <?php endif; ?>

        <!-- Content -->
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

          <?php if (empty($docsVisibles)): ?>
          <!-- Empty state -->
          <div class="nube-empty">
            <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
            <h3>Sin archivos aquí todavía</h3>
            <p>Sube un archivo, crea una nota o guarda recursos desde tus lecciones.</p>
            <div style="display:flex;gap:10px;justify-content:center;margin-top:18px;">
              <button class="nb-btn nb-btn-secondary" onclick="openModal('nmUpload')">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Subir archivo
              </button>
              <a href="<?= BASE_URL ?>/index.php?url=nuevo-documento" class="nb-btn nb-btn-primary">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
                Nueva nota
              </a>
            </div>
          </div>

          <?php else: ?>

          <?php
          // Apply tipo filter in PHP too for non-JS
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
            <?php if (count($bySection) > 1): ?>
            <div class="nube-section-lbl"><?= $secLabel ?></div>
            <?php endif; ?>
            <div class="nube-grid">
              <?php foreach ($secDocs as $doc):
                $ti     = nubeTypeInfo($doc);
                $fileUrl = nubeGetFileUrl($doc);
                $isFile  = nubeIsFile($doc);
                $orig   = nubeGetOrigName($doc);
              ?>
              <div class="nube-card doc-item"
                   data-nombre="<?= htmlspecialchars(strtolower($doc['titulo'] . ' ' . $orig)) ?>"
                   data-carpeta="<?= (int)($doc['carpeta_id'] ?? 0) ?>">
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
                <div class="nube-card-actions">
                  <?php if ($fileUrl): ?>
                  <a href="<?= htmlspecialchars($fileUrl) ?>" <?= str_starts_with($fileUrl, 'http') ? 'target="_blank"' : '' ?> class="nca blue" title="Descargar / Abrir">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <?= $isFile ? 'Descargar' : 'Abrir' ?>
                  </a>
                  <?php endif; ?>
                  <a href="<?= BASE_URL ?>/index.php?url=documento&id=<?= (int)$doc['id'] ?>" class="nca">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Ver
                  </a>
                  <button class="nca" title="Mover a carpeta" onclick='openMoverModal(<?= (int)$doc['id'] ?>, "<?= htmlspecialchars(addslashes($doc['titulo'])) ?>", <?= (int)($doc['carpeta_id'] ?? 0) ?>)'>
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                  </button>
                  <button class="nca danger" title="Eliminar" onclick="pedirEliminarDoc(<?= (int)$doc['id'] ?>, '<?= htmlspecialchars(addslashes($doc['titulo'])) ?>')">
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
            <?php if (count($bySection) > 1): ?>
            <div class="nube-section-lbl"><?= $secLabel ?></div>
            <?php endif; ?>
            <div class="nube-list">
              <?php foreach ($secDocs as $doc):
                $ti      = nubeTypeInfo($doc);
                $fileUrl = nubeGetFileUrl($doc);
                $isFile  = nubeIsFile($doc);
              ?>
              <div class="nube-list-row doc-item"
                   data-nombre="<?= htmlspecialchars(strtolower($doc['titulo'])) ?>"
                   data-carpeta="<?= (int)($doc['carpeta_id'] ?? 0) ?>">
                <div class="nube-list-icon" style="background:<?= $ti['bg'] ?>;border-color:<?= $ti['border'] ?>"><?= $ti['icon'] ?></div>
                <div class="nube-list-name" title="<?= htmlspecialchars($doc['titulo']) ?>"><?= htmlspecialchars($doc['titulo']) ?></div>
                <?php if (!empty($doc['carpeta_nombre'])): ?>
                <div class="nube-list-meta">📁 <?= htmlspecialchars($doc['carpeta_nombre']) ?></div>
                <?php endif; ?>
                <span class="nube-card-badge" style="background:<?= $ti['bg'] ?>;color:<?= $ti['color'] ?>;border:1px solid <?= $ti['border'] ?>;"><?= $ti['label'] ?></span>
                <div class="nube-list-actions">
                  <?php if ($fileUrl): ?>
                  <a href="<?= htmlspecialchars($fileUrl) ?>" <?= str_starts_with($fileUrl, 'http') ? 'target="_blank"' : '' ?> class="nca blue" style="padding:5px 8px;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                  </a>
                  <?php endif; ?>
                  <a href="<?= BASE_URL ?>/index.php?url=documento&id=<?= (int)$doc['id'] ?>" class="nca" style="padding:5px 8px;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>
                  <button class="nca" style="padding:5px 8px;" onclick='openMoverModal(<?= (int)$doc['id'] ?>, "<?= htmlspecialchars(addslashes($doc['titulo'])) ?>", <?= (int)($doc['carpeta_id'] ?? 0) ?>)'>
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                  </button>
                  <button class="nca danger" style="padding:5px 8px;" onclick="pedirEliminarDoc(<?= (int)$doc['id'] ?>, '<?= htmlspecialchars(addslashes($doc['titulo'])) ?>')">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div>

          <?php endif; ?>
        </div>
      </div>

    </div><!-- /nube-shell -->
  </div>
</main>

<!-- ══════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════ -->

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
    <div id="uploadPreview" style="display:none;padding:8px 12px;background:var(--soft);border:1.5px solid var(--border);border-radius:8px;font-size:.84rem;font-weight:600;margin-top:8px;color:var(--dark);display:flex;align-items:center;gap:8px;"></div>
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

<!-- Move to folder modal -->
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

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
<script>
const BASE_URL = '<?= BASE_URL ?>';

/* ── Modal helpers ── */
function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.add('show'); m.querySelector('input:not([type=hidden]),select')?.focus(); }
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('show');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.nm-overlay.show').forEach(m => m.classList.remove('show')); });

/* ── View toggle ── */
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

/* ── Search ── */
function filtrarNube(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('.doc-item').forEach(el => {
    const n = el.dataset.nombre || '';
    el.style.display = n.includes(q) ? '' : 'none';
  });
}

/* ── Folder filter (select in toolbar) ── */
function filtrarPorCarpeta(val) {
  window.location.href = BASE_URL + '/index.php?url=nube' + (val ? '&carpeta=' + val : '');
}

/* ── File preview on select ── */
function previewFile(input) {
  const file = input.files[0];
  if (!file) return;
  const prev = document.getElementById('uploadPreview');
  const icons = { 'pdf':'📄','doc':'📝','docx':'📝','zip':'🗜️','rar':'🗜️','png':'🖼️','jpg':'🖼️','jpeg':'🖼️','webp':'🖼️','gif':'🖼️','mp4':'🎬','mp3':'🎵','txt':'📋','xlsx':'📊','pptx':'📊' };
  const ext = file.name.split('.').pop().toLowerCase();
  const icon = icons[ext] || '📎';
  const sizeMB = (file.size / 1048576).toFixed(1);
  prev.style.display = 'flex';
  prev.innerHTML = `<span style="font-size:1.2rem">${icon}</span><div style="flex:1;min-width:0"><div style="font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${file.name}</div><div style="font-size:.75rem;color:var(--muted)">${sizeMB} MB</div></div>`;
  if (!document.getElementById('uploadNombre').value) {
    document.getElementById('uploadNombre').value = file.name.replace(/\.[^.]+$/, '');
  }
}

/* ── Drag & drop on dropzone ── */
const dz = document.getElementById('dropzone');
if (dz) {
  dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
  dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('drag-over');
    const fi = document.getElementById('uploadFile');
    fi.files = e.dataTransfer.files;
    previewFile(fi);
  });
}

/* ── Upload ── */
async function subirArchivo() {
  const fileInput = document.getElementById('uploadFile');
  const file = fileInput.files[0];
  if (!file) { alert('Selecciona un archivo primero.'); return; }
  if (file.size > 52428800) { alert('El archivo supera el límite de 50 MB.'); return; }

  const nombre   = document.getElementById('uploadNombre').value.trim() || file.name.replace(/\.[^.]+$/, '');
  const carpetaId = document.getElementById('uploadCarpeta').value;

  const bar  = document.getElementById('uploadBar');
  const fill = document.getElementById('uploadFill');
  const pct  = document.getElementById('uploadPct');
  const btn  = document.getElementById('btnUpload');
  bar.classList.add('show');
  btn.disabled = true;

  const fd = new FormData();
  fd.append('archivo', file);
  fd.append('nombre', nombre);
  fd.append('carpeta_id', carpetaId);
  fd.append('nube_action', 'subir_archivo');

  const xhr = new XMLHttpRequest();
  xhr.upload.onprogress = e => {
    if (e.lengthComputable) {
      const p = Math.round(e.loaded / e.total * 100);
      fill.style.width = p + '%';
      pct.textContent = p + '%';
    }
  };
  xhr.onload = () => {
    bar.classList.remove('show');
    btn.disabled = false;
    try {
      const res = JSON.parse(xhr.responseText);
      if (res.ok) { closeModal('nmUpload'); window.location.reload(); }
      else { alert(res.error || 'Error al subir el archivo.'); }
    } catch(e) { alert('Error inesperado al subir.'); }
  };
  xhr.onerror = () => { bar.classList.remove('show'); btn.disabled = false; alert('Error de red.'); };
  xhr.open('POST', BASE_URL + '/index.php?url=nube-api');
  xhr.send(fd);
}

/* ── Move to folder ── */
function openMoverModal(id, nombre, carpetaActual) {
  document.getElementById('nmMoverId').value = id;
  document.getElementById('nmMoverNombre').textContent = '"' + nombre + '"';
  const sel = document.getElementById('nmMoverCarpeta');
  sel.value = carpetaActual || '';
  openModal('nmMover');
}

async function moverDocumento() {
  const id = document.getElementById('nmMoverId').value;
  const carpetaId = document.getElementById('nmMoverCarpeta').value;
  const res = await fetch(BASE_URL + '/index.php?url=nube-api', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nube_action: 'mover_documento', id, carpeta_id: carpetaId || null })
  }).then(r => r.json());
  if (res.ok) { closeModal('nmMover'); window.location.reload(); }
  else alert(res.error || 'Error al mover.');
}

/* ── Delete document ── */
async function pedirEliminarDoc(id, nombre) {
  if (!confirm('¿Eliminar "' + nombre + '"? Esta acción no se puede deshacer.')) return;
  const res = await fetch(BASE_URL + '/index.php?url=nube-api', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nube_action: 'eliminar_documento', id })
  }).then(r => r.json());
  if (res.ok) window.location.reload();
  else alert(res.error || 'Error al eliminar.');
}

/* ── Delete folder ── */
async function pedirEliminarCarpeta(id, nombre) {
  if (!confirm('¿Eliminar la carpeta "' + nombre + '"? Los archivos dentro quedarán sin carpeta.')) return;
  const res = await fetch(BASE_URL + '/index.php?url=nube-api', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nube_action: 'eliminar_carpeta', id })
  }).then(r => r.json());
  if (res.ok) window.location.href = BASE_URL + '/index.php?url=nube';
  else alert(res.error || 'Error al eliminar la carpeta.');
}
</script>
</body>
</html>
