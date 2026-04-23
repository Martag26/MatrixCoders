<?php
$carpetas   = is_array($carpetas   ?? null) ? $carpetas   : [];
$documentos = is_array($documentos ?? null) ? $documentos : [];
$flash      = $flash ?? null;
$currentUrl = $_GET['url'] ?? 'mis-documentos';
$filtroCarpe = (int)($_GET['carpeta'] ?? 0);

// Filter by folder if requested
$docsVisibles = $filtroCarpe
    ? array_filter($documentos, fn($d) => (int)($d['carpeta_id'] ?? 0) === $filtroCarpe)
    : $documentos;

// File type detection helper
function docTypeInfo(array $d): array {
    $t = $d['titulo'] ?? '';
    $c = $d['contenido'] ?? '';
    if (str_contains($c, 'Recurso del curso')) return ['icon'=>'🔗','color'=>'#eff6ff','border'=>'#bfdbfe','label'=>'Recurso de curso'];
    if (preg_match('/\.pdf/i', $c.$t))         return ['icon'=>'📄','color'=>'#fef2f2','border'=>'#fecaca','label'=>'PDF'];
    if (preg_match('/\.doc/i', $c.$t))         return ['icon'=>'📝','color'=>'#eff6ff','border'=>'#bfdbfe','label'=>'Documento'];
    if (preg_match('/\.zip|\.rar/i', $c.$t))   return ['icon'=>'🗜️','color'=>'#fffbeb','border'=>'#fde68a','label'=>'Archivo'];
    return ['icon'=>'📋','color'=>'#f8fafc','border'=>'#e5e7eb','label'=>'Nota'];
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
:root{--navy:#0f172a;--dark:#1B2336;--green:#6B8F71;--green-d:#4a6b50;--border:#e5e7eb;--soft:#f8fafc;--muted:#6b7280;--header-h:66px;}
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Saira',sans-serif;background:#f1f5f9;color:var(--dark);margin:0;}
.nube-shell{display:grid;grid-template-columns:240px 1fr;gap:0;min-height:calc(100vh - var(--header-h));}
@media(max-width:900px){.nube-shell{grid-template-columns:1fr;}.nube-sidebar{display:none;}}

/* Left sidebar */
.nube-sidebar{background:#fff;border-right:1px solid var(--border);padding:24px 16px;display:flex;flex-direction:column;gap:4px;}
.nube-sidebar-head{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);padding:4px 10px 8px;margin-bottom:2px;}
.nube-nav-link{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;text-decoration:none;font-size:.88rem;font-weight:600;color:var(--muted);transition:all .15s;border:1.5px solid transparent;}
.nube-nav-link:hover{background:#f0fdf4;color:var(--green-d);}
.nube-nav-link.active{background:#f0fdf4;color:var(--green-d);border-color:#bbf7d0;}
.nube-nav-link .count{margin-left:auto;background:var(--soft);border:1px solid var(--border);color:var(--muted);font-size:.72rem;font-weight:700;border-radius:99px;padding:0 7px;line-height:20px;}
.nube-folder-divider{height:1px;background:var(--border);margin:12px 0;}
.nube-new-folder{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:9px;background:none;border:1.5px dashed var(--border);width:100%;color:var(--muted);font-size:.83rem;font-weight:600;font-family:'Saira',sans-serif;cursor:pointer;transition:all .15s;margin-top:4px;}
.nube-new-folder:hover{border-color:var(--green);color:var(--green);}

/* Main content */
.nube-main{display:flex;flex-direction:column;}
.nube-header{background:#fff;border-bottom:1px solid var(--border);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;}
.nube-header-left h1{font-size:1.2rem;font-weight:800;margin:0 0 2px;color:var(--dark);}
.nube-header-left p{font-size:.83rem;color:var(--muted);margin:0;}
.nube-header-actions{display:flex;gap:8px;}
.nube-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;font-size:.83rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;text-decoration:none;transition:all .15s;border:none;}
.nube-btn-primary{background:var(--green);color:#fff;}
.nube-btn-primary:hover{background:var(--green-d);color:#fff;}
.nube-btn-secondary{background:#fff;color:var(--dark);border:1.5px solid var(--border);}
.nube-btn-secondary:hover{border-color:var(--green);color:var(--green);}

/* Stats strip */
.nube-stats{display:flex;gap:0;border-bottom:1px solid var(--border);}
.nube-stat{flex:1;padding:14px 20px;text-align:center;border-right:1px solid var(--border);}
.nube-stat:last-child{border-right:none;}
.nube-stat .val{font-size:1.5rem;font-weight:800;color:var(--dark);}
.nube-stat .lbl{font-size:.75rem;color:var(--muted);margin-top:1px;}

/* Content area */
.nube-content{padding:24px 28px;flex:1;}
.nube-toolbar{display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap;}
.nube-search{flex:1;min-width:200px;position:relative;}
.nube-search input{width:100%;border:1.5px solid var(--border);border-radius:9px;padding:8px 14px 8px 36px;font-family:'Saira',sans-serif;font-size:.85rem;background:#fff;color:var(--dark);outline:none;transition:border-color .15s;}
.nube-search input:focus{border-color:var(--green);}
.nube-search svg{position:absolute;left:11px;top:50%;transform:translateY(-50%);}
.nube-sort{border:1.5px solid var(--border);border-radius:9px;padding:7px 10px;font-family:'Saira',sans-serif;font-size:.82rem;color:var(--dark);background:#fff;cursor:pointer;outline:none;}

/* Section title */
.nube-section-title{font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:12px;}

/* Document grid */
.nube-docs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:28px;}
.nube-doc-card{background:#fff;border:1.5px solid var(--border);border-radius:13px;padding:16px;display:flex;flex-direction:column;gap:10px;transition:all .15s;text-decoration:none;color:inherit;cursor:pointer;position:relative;}
.nube-doc-card:hover{border-color:var(--green);box-shadow:0 4px 16px rgba(107,143,113,.12);transform:translateY(-1px);}
.nube-doc-card-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;border:1.5px solid;}
.nube-doc-card-name{font-size:.88rem;font-weight:700;color:var(--dark);overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}
.nube-doc-card-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;}
.nube-doc-card-folder{font-size:.72rem;color:var(--muted);background:var(--soft);border-radius:99px;padding:2px 8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px;}
.nube-doc-card-type{font-size:.68rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;}
.nube-doc-card-actions{display:flex;gap:6px;margin-top:4px;}
.nube-doc-action{flex:1;display:flex;align-items:center;justify-content:center;gap:4px;padding:5px;border-radius:7px;font-size:.75rem;font-weight:700;text-decoration:none;transition:all .15s;border:1.5px solid var(--border);background:#fff;color:var(--muted);font-family:'Saira',sans-serif;cursor:pointer;}
.nube-doc-action:hover{border-color:var(--green);color:var(--green);}
.nube-doc-action.danger:hover{border-color:#fca5a5;color:#dc2626;}

/* Empty state */
.nube-empty{text-align:center;padding:3rem 1rem;}
.nube-empty svg{opacity:.25;display:block;margin:0 auto 14px;}
.nube-empty h3{font-size:.98rem;font-weight:700;margin:0 0 6px;}
.nube-empty p{font-size:.84rem;color:var(--muted);margin:0;}

/* Flash */
.nube-flash{padding:10px 16px;border-radius:9px;font-size:.85rem;font-weight:600;margin-bottom:16px;}
.nube-flash.success{background:#f0fdf4;border:1px solid #86efac;color:#166534;}
.nube-flash.error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}

/* New folder modal */
.nf-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);display:flex;align-items:center;justify-content:center;z-index:9000;opacity:0;pointer-events:none;transition:opacity .2s;}
.nf-overlay.show{opacity:1;pointer-events:all;}
.nf-box{background:#fff;border-radius:14px;padding:28px 32px;max-width:360px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.15);}
.nf-box h3{font-size:1rem;font-weight:800;margin:0 0 14px;}
.nf-box input{width:100%;border:1.5px solid var(--border);border-radius:9px;padding:9px 14px;font-family:'Saira',sans-serif;font-size:.9rem;color:var(--dark);outline:none;margin-bottom:14px;}
.nf-box input:focus{border-color:var(--green);}
.nf-box-actions{display:flex;gap:8px;}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main style="min-height:calc(100vh - 66px);background:#f1f5f9;">
  <div style="max-width:1280px;margin:0 auto;display:flex;height:calc(100vh - 66px);overflow:hidden;">

    <!-- App sidebar (main nav) -->
    <?php require __DIR__ . '/../layout/sidebar.php'; ?>

    <!-- Nube layout -->
    <div class="nube-shell" style="flex:1;overflow:hidden;">

      <!-- Left: folders sidebar -->
      <div class="nube-sidebar" style="overflow-y:auto;">
        <div class="nube-sidebar-head">Mi nube</div>

        <a href="<?= BASE_URL ?>/index.php?url=nube" class="nube-nav-link <?= !$filtroCarpe ? 'active' : '' ?>">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
          Todos los archivos
          <span class="count"><?= count($documentos) ?></span>
        </a>

        <?php if (!empty($carpetas)): ?>
        <div class="nube-folder-divider"></div>
        <div class="nube-sidebar-head">Carpetas</div>
        <?php foreach ($carpetas as $carp): ?>
        <a href="?url=nube&carpeta=<?= $carp['id'] ?>" class="nube-nav-link <?= $filtroCarpe === (int)$carp['id'] ? 'active' : '' ?>">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
          <?= htmlspecialchars($carp['nombre']) ?>
          <span class="count"><?= (int)($carp['total_documentos'] ?? 0) ?></span>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="nube-folder-divider"></div>
        <button class="nube-new-folder" onclick="openNewFolder()">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Nueva carpeta
        </button>
      </div>

      <!-- Right: main content -->
      <div class="nube-main" style="overflow-y:auto;">

        <!-- Header -->
        <div class="nube-header">
          <div class="nube-header-left">
            <h1><?= $filtroCarpe && ($nf = current(array_filter($carpetas, fn($c)=>(int)$c['id']===$filtroCarpe))) ? '📁 '.htmlspecialchars($nf['nombre']) : '☁️ Mi nube' ?></h1>
            <p><?= count($docsVisibles) ?> archivo<?= count($docsVisibles) !== 1 ? 's' : '' ?><?= $filtroCarpe ? ' en esta carpeta' : ' en total' ?></p>
          </div>
          <div class="nube-header-actions">
            <a href="<?= BASE_URL ?>/index.php?url=nuevo-documento" class="nube-btn nube-btn-primary">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
              Nuevo documento
            </a>
          </div>
        </div>

        <!-- Stats -->
        <?php if (!$filtroCarpe): ?>
        <div class="nube-stats">
          <div class="nube-stat"><div class="val"><?= count($documentos) ?></div><div class="lbl">Archivos</div></div>
          <div class="nube-stat"><div class="val"><?= count($carpetas) ?></div><div class="lbl">Carpetas</div></div>
          <div class="nube-stat"><div class="val"><?= count(array_filter($documentos, fn($d) => str_contains($d['contenido'] ?? '', 'Recurso del curso'))) ?></div><div class="lbl">Recursos de cursos</div></div>
          <div class="nube-stat"><div class="val"><?= count(array_filter($documentos, fn($d) => !str_contains($d['contenido'] ?? '', 'Recurso del curso'))) ?></div><div class="lbl">Documentos propios</div></div>
        </div>
        <?php endif; ?>

        <div class="nube-content">

          <?php if (!empty($flash)): ?>
          <div class="nube-flash <?= htmlspecialchars($flash['type'] ?? 'success') ?>"><?= htmlspecialchars($flash['message'] ?? '') ?></div>
          <?php endif; ?>

          <!-- Search toolbar -->
          <?php if (!empty($docsVisibles)): ?>
          <div class="nube-toolbar">
            <div class="nube-search">
              <svg width="15" height="15" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
              <input type="text" id="searchDocs" placeholder="Buscar archivo…" oninput="filtrarDocs()">
            </div>
          </div>
          <?php endif; ?>

          <?php if (empty($docsVisibles)): ?>
          <div class="nube-empty">
            <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
            <h3>Sin archivos todavía</h3>
            <p>Guarda recursos desde tus lecciones o crea un nuevo documento.</p>
          </div>
          <?php else: ?>

          <?php
          // Separate into course resources and own documents
          $recursosCurso = array_filter($docsVisibles, fn($d) => str_contains($d['contenido'] ?? '', 'Recurso del curso'));
          $docsPropios   = array_filter($docsVisibles, fn($d) => !str_contains($d['contenido'] ?? '', 'Recurso del curso'));
          ?>

          <?php if (!empty($recursosCurso)): ?>
          <div class="nube-section-title" style="margin-bottom:10px">Recursos guardados de cursos</div>
          <div class="nube-docs-grid" id="gridRecursos">
            <?php foreach ($recursosCurso as $doc):
              $ti = docTypeInfo($doc);
              // Extract URL from contenido
              preg_match('/URL:\s*(https?:\/\/\S+)/i', $doc['contenido'] ?? '', $urlMatch);
              $docUrl = $urlMatch[1] ?? null;
            ?>
            <div class="nube-doc-card doc-item" data-nombre="<?= htmlspecialchars(strtolower($doc['titulo'])) ?>">
              <div style="display:flex;align-items:flex-start;gap:10px">
                <div class="nube-doc-card-icon" style="background:<?= $ti['color'] ?>;border-color:<?= $ti['border'] ?>"><?= $ti['icon'] ?></div>
                <div style="flex:1;min-width:0">
                  <div class="nube-doc-card-name"><?= htmlspecialchars($doc['titulo']) ?></div>
                  <?php if (!empty($doc['carpeta_nombre'])): ?>
                  <div class="nube-doc-card-folder">📁 <?= htmlspecialchars($doc['carpeta_nombre']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="nube-doc-card-meta">
                <span class="nube-doc-card-type"><?= $ti['label'] ?></span>
              </div>
              <div class="nube-doc-card-actions">
                <?php if ($docUrl): ?>
                <a href="<?= htmlspecialchars($docUrl) ?>" target="_blank" class="nube-doc-action">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                  Descargar
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php?url=documento&id=<?= (int)$doc['id'] ?>" class="nube-doc-action">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Ver
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if (!empty($docsPropios)): ?>
          <div class="nube-section-title" style="margin-bottom:10px;<?= !empty($recursosCurso) ? 'margin-top:8px' : '' ?>">Mis documentos</div>
          <div class="nube-docs-grid" id="gridPropios">
            <?php foreach ($docsPropios as $doc):
              $ti = docTypeInfo($doc);
            ?>
            <div class="nube-doc-card doc-item" data-nombre="<?= htmlspecialchars(strtolower($doc['titulo'])) ?>">
              <div style="display:flex;align-items:flex-start;gap:10px">
                <div class="nube-doc-card-icon" style="background:<?= $ti['color'] ?>;border-color:<?= $ti['border'] ?>"><?= $ti['icon'] ?></div>
                <div style="flex:1;min-width:0">
                  <div class="nube-doc-card-name"><?= htmlspecialchars($doc['titulo']) ?></div>
                  <?php if (!empty($doc['carpeta_nombre'])): ?>
                  <div class="nube-doc-card-folder" style="margin-top:3px">📁 <?= htmlspecialchars($doc['carpeta_nombre']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="nube-doc-card-meta">
                <span class="nube-doc-card-type"><?= $ti['label'] ?></span>
              </div>
              <div class="nube-doc-card-actions">
                <a href="<?= BASE_URL ?>/index.php?url=documento&id=<?= (int)$doc['id'] ?>" class="nube-doc-action">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  Editar
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- New folder modal -->
<div class="nf-overlay" id="nfOverlay">
  <div class="nf-box">
    <h3>Nueva carpeta</h3>
    <form method="POST" action="<?= BASE_URL ?>/index.php?url=mis-documentos">
      <input type="hidden" name="dashboard_action" value="create_folder">
      <input type="text" name="folder_name" id="nfInput" placeholder="Nombre de la carpeta" required autofocus>
      <div class="nf-box-actions">
        <button type="button" class="nube-btn nube-btn-secondary" onclick="closeNewFolder()">Cancelar</button>
        <button type="submit" class="nube-btn nube-btn-primary">Crear carpeta</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openNewFolder() {
  document.getElementById('nfOverlay').classList.add('show');
  setTimeout(() => document.getElementById('nfInput').focus(), 50);
}
function closeNewFolder() {
  document.getElementById('nfOverlay').classList.remove('show');
}
document.getElementById('nfOverlay').addEventListener('click', function(e){
  if(e.target===this) closeNewFolder();
});

function filtrarDocs() {
  const q = document.getElementById('searchDocs').value.toLowerCase().trim();
  document.querySelectorAll('.doc-item').forEach(card => {
    const n = card.dataset.nombre || '';
    card.style.display = n.includes(q) ? '' : 'none';
  });
}
</script>
</body>
</html>
