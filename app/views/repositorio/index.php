<?php
$tiposIconSvg = [
    'pdf'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
    'doc'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path stroke-linecap="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
    'zip'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>',
    'link'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6B8F71" stroke-width="2"><path stroke-linecap="round" d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>',
    'actividad'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h3m-3 4h3m-6-4h.01m0 4h.01"/></svg>',
    'video'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
];
$tipoLabel  = ['pdf'=>'PDF','doc'=>'Documento','zip'=>'Archivo ZIP','link'=>'Enlace','actividad'=>'Actividad práctica','video'=>'Vídeo'];
$tipoColors = ['pdf'=>'#fef2f2','doc'=>'#eff6ff','zip'=>'#fffbeb','link'=>'#f0fdf4','actividad'=>'#faf5ff','video'=>'#f0f9ff'];
$tipoBorders= ['pdf'=>'#fecaca','doc'=>'#bfdbfe','zip'=>'#fde68a','link'=>'#bbf7d0','actividad'=>'#ddd6fe','video'=>'#bae6fd'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-navy: #0f172a;
            --mc-dark: #1B2336;
            --mc-border: #e5e7eb;
            --mc-soft: #f8fafc;
            --mc-text: #374151;
            --mc-muted: #6b7280;
        }
        .repo-main { flex: 1; min-width: 0; padding: 2rem 1.5rem; }
        .repo-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.75rem; flex-wrap:wrap; }
        .repo-titulo { font-size:1.45rem; font-weight:800; color:var(--mc-dark); margin:0 0 4px; font-family:'Saira',sans-serif; }
        .repo-sub { font-size:.9rem; color:var(--mc-muted); margin:0; }
        .repo-filters { display:flex; gap:.6rem; flex-wrap:wrap; margin-bottom:1.5rem; align-items:center; }
        .repo-filter-btn { padding:.38rem .85rem; border-radius:20px; border:1.5px solid var(--mc-border); background:#fff; color:var(--mc-text); font-size:.82rem; font-weight:600; font-family:'Saira',sans-serif; cursor:pointer; text-decoration:none; transition:all .15s; }
        .repo-filter-btn:hover, .repo-filter-btn.active { border-color:var(--mc-green); background:var(--mc-green); color:#fff; }
        .repo-filter-btn.active-actividad { border-color:#7c3aed; background:#7c3aed; color:#fff; }
        .repo-filter-select { padding:.38rem .75rem; border-radius:8px; border:1.5px solid var(--mc-border); background:#fff; font-size:.82rem; font-family:'Saira',sans-serif; color:var(--mc-text); cursor:pointer; }
        .repo-section { margin-bottom:2rem; }
        .repo-section-title { font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.6px; color:var(--mc-muted); margin-bottom:1rem; display:flex; align-items:center; gap:8px; }
        .repo-badge { font-size:.7rem; font-weight:700; border-radius:99px; padding:2px 9px; background:#eff6ff; color:#2563eb; text-transform:none; letter-spacing:0; }
        .repo-badge.actividad { background:#f3e8ff; color:#7c3aed; }
        .repo-empty { text-align:center; padding:3.5rem 1rem; color:var(--mc-muted); }
        .repo-empty svg { display:block; margin:0 auto 1rem; opacity:.3; }
        .repo-empty h6 { font-size:.95rem; font-weight:700; margin:0 0 6px; color:var(--mc-dark); }
        .repo-empty p { font-size:.85rem; margin:0; }
        /* cards */
        .res-doc-card { display:flex; align-items:center; gap:14px; padding:14px 16px; background:var(--bg,#f8fafc); border:1.5px solid var(--bdr,#e5e7eb); border-radius:12px; margin-bottom:8px; }
        .res-doc-icon { width:42px; height:42px; border-radius:10px; background:#fff; border:1px solid var(--bdr,#e5e7eb); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .res-doc-info { flex:1; min-width:0; }
        .res-doc-name { font-size:.9rem; font-weight:700; color:var(--mc-dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .res-doc-desc { font-size:.78rem; color:var(--mc-muted); margin-top:2px; }
        .res-doc-breadcrumb { font-size:.72rem; color:var(--mc-muted); margin-top:3px; }
        .res-doc-breadcrumb a { color:var(--mc-green-d); text-decoration:none; font-weight:600; }
        .res-doc-breadcrumb a:hover { text-decoration:underline; }
        .res-doc-type-tag { display:inline-block; font-size:.7rem; font-weight:700; background:rgba(107,143,113,.12); color:var(--mc-green-d); border-radius:99px; padding:2px 8px; margin-top:4px; text-transform:uppercase; letter-spacing:.3px; }
        .res-doc-actions { display:flex; flex-direction:column; gap:6px; flex-shrink:0; }
        @media(min-width:640px){ .res-doc-actions{ flex-direction:row; } }
        .res-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:7px; font-size:.78rem; font-weight:700; font-family:'Saira',sans-serif; cursor:pointer; text-decoration:none; white-space:nowrap; transition:all .15s; }
        .res-btn-cloud { background:#fff; border:1.5px solid #bfdbfe; color:#1d4ed8; }
        .res-btn-cloud:hover { background:#eff6ff; border-color:#93c5fd; }
        .res-btn-download { background:var(--mc-green); border:1.5px solid var(--mc-green); color:#fff; }
        .res-btn-download:hover { background:var(--mc-green-d); border-color:var(--mc-green-d); color:#fff; }
        /* grouping by course */
        .repo-course-group { margin-bottom:2rem; }
        .repo-course-label { font-size:.83rem; font-weight:700; color:var(--mc-dark); border-left:3px solid var(--mc-green); padding:.2rem .75rem; margin-bottom:.75rem; background:#f0fdf4; border-radius:0 8px 8px 0; }
        /* toast */
        .mc-toast-wrap { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px; pointer-events:none; }
        .mc-toast { background:var(--mc-dark); color:#fff; border-radius:10px; padding:12px 18px; font-size:.88rem; font-weight:600; font-family:'Saira',sans-serif; box-shadow:0 4px 20px rgba(0,0,0,.25); opacity:0; transform:translateY(8px); transition:opacity .2s,transform .2s; pointer-events:none; max-width:320px; }
        .mc-toast.show { opacity:1; transform:translateY(0); }
        .mc-toast.success { background:#166534; }
        .mc-toast.error { background:#991b1b; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="main-dashboard">
    <div class="mc-container">
        <div class="contenedor-dashboard-content">

            <?php require __DIR__ . '/../layout/sidebar.php'; ?>

            <div class="repo-main">

                <div class="repo-header">
                    <div>
                        <h1 class="repo-titulo">Repositorio de recursos</h1>
                        <p class="repo-sub">Todos los materiales y actividades de práctica de tus cursos matriculados.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/index.php?url=mis-cursos" style="display:inline-flex;align-items:center;gap:.4rem;font-size:.83rem;font-weight:600;color:var(--mc-muted);text-decoration:none;border:1.5px solid var(--mc-border);padding:.4rem .9rem;border-radius:8px;background:#fff">
                        ← Mis cursos
                    </a>
                </div>

                <!-- Filtros -->
                <div class="repo-filters">
                    <span style="font-size:.8rem;font-weight:700;color:var(--mc-muted);white-space:nowrap">Tipo:</span>
                    <?php
                    $tipos = ['todos'=>'Todos','pdf'=>'PDF','doc'=>'Documento','zip'=>'ZIP','link'=>'Enlace','video'=>'Vídeo','actividad'=>'Actividades'];
                    foreach ($tipos as $k => $label):
                        $clsActiva = ($filtroTipo === $k) ? ('active' . ($k === 'actividad' ? ' active-actividad' : '')) : '';
                    ?>
                    <a href="<?= BASE_URL ?>/index.php?url=repositorio&tipo=<?= $k ?><?= $filtroCurso ? '&curso='.$filtroCurso : '' ?>"
                       class="repo-filter-btn <?= $clsActiva ?>"><?= $label ?></a>
                    <?php endforeach; ?>

                    <?php if (!empty($cursosMatriculados)): ?>
                    <span style="font-size:.8rem;font-weight:700;color:var(--mc-muted);margin-left:.5rem;white-space:nowrap">Curso:</span>
                    <select class="repo-filter-select" onchange="location.href=this.value">
                        <option value="<?= BASE_URL ?>/index.php?url=repositorio&tipo=<?= $filtroTipo ?>">Todos los cursos</option>
                        <?php foreach ($cursosMatriculados as $c): ?>
                        <option value="<?= BASE_URL ?>/index.php?url=repositorio&tipo=<?= $filtroTipo ?>&curso=<?= $c['id'] ?>"
                                <?= $filtroCurso === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['titulo']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>

                <?php if (empty($materiales) && empty($actividades)): ?>
                <div class="repo-empty">
                    <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h6>Sin recursos disponibles</h6>
                    <p>Los instructores aún no han añadido material<?= $filtroTipo !== 'todos' ? ' de este tipo' : '' ?><?= $filtroCurso ? ' en este curso' : '' ?>.</p>
                </div>

                <?php else: ?>

                <?php if (!empty($materiales)): ?>
                <!-- Materiales de lección -->
                <section class="repo-section">
                    <div class="repo-section-title">
                        Material de lecciones
                        <span class="repo-badge"><?= count($materiales) ?></span>
                    </div>
                    <?php
                    $porCurso = [];
                    foreach ($materiales as $r) {
                        $porCurso[$r['curso_titulo']][] = $r;
                    }
                    foreach ($porCurso as $cursoTit => $recs): ?>
                    <div class="repo-course-group">
                        <div class="repo-course-label">📚 <?= htmlspecialchars($cursoTit) ?></div>
                        <?php foreach ($recs as $rec):
                            $svgIcon = $tiposIconSvg[$rec['tipo']] ?? $tiposIconSvg['link'];
                            $bg      = $tipoColors[$rec['tipo']] ?? '#f8fafc';
                            $border  = $tipoBorders[$rec['tipo']] ?? '#e5e7eb';
                            $label   = $tipoLabel[$rec['tipo']] ?? ucfirst($rec['tipo']);
                        ?>
                        <div class="res-doc-card" style="--bg:<?= $bg ?>;--bdr:<?= $border ?>">
                            <div class="res-doc-icon"><?= $svgIcon ?></div>
                            <div class="res-doc-info">
                                <div class="res-doc-name"><?= htmlspecialchars($rec['nombre']) ?></div>
                                <?php if ($rec['descripcion']): ?>
                                <div class="res-doc-desc"><?= htmlspecialchars($rec['descripcion']) ?></div>
                                <?php endif; ?>
                                <div class="res-doc-breadcrumb">
                                    <?= htmlspecialchars($rec['unidad_titulo']) ?> → <?= htmlspecialchars($rec['leccion_titulo']) ?>
                                </div>
                                <span class="res-doc-type-tag"><?= $label ?></span>
                            </div>
                            <div class="res-doc-actions">
                                <button class="res-btn res-btn-cloud" onclick="addToCloud('<?= addslashes(htmlspecialchars($rec['nombre'])) ?>','<?= addslashes(htmlspecialchars($rec['url_o_ruta'])) ?>')">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    Mi nube
                                </button>
                                <a class="res-btn res-btn-download" href="<?= htmlspecialchars($rec['url_o_ruta']) ?>" target="_blank"
                                   <?= $rec['descargable'] ? 'download' : '' ?>>
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Descargar
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>

                <?php if (!empty($actividades)): ?>
                <!-- Actividades de práctica no evaluables -->
                <section class="repo-section">
                    <div class="repo-section-title">
                        Actividades de práctica
                        <span class="repo-badge actividad">No evaluables · <?= count($actividades) ?></span>
                    </div>
                    <?php
                    $porCursoAct = [];
                    foreach ($actividades as $r) {
                        $porCursoAct[$r['curso_titulo']][] = $r;
                    }
                    foreach ($porCursoAct as $cursoTit => $recs): ?>
                    <div class="repo-course-group">
                        <div class="repo-course-label" style="border-color:#7c3aed;background:#faf5ff">🎯 <?= htmlspecialchars($cursoTit) ?></div>
                        <?php foreach ($recs as $rec): ?>
                        <div class="res-doc-card" style="--bg:#faf5ff;--bdr:#ddd6fe">
                            <div class="res-doc-icon"><?= $tiposIconSvg['actividad'] ?></div>
                            <div class="res-doc-info">
                                <div class="res-doc-name"><?= htmlspecialchars($rec['nombre']) ?></div>
                                <?php if ($rec['descripcion']): ?>
                                <div class="res-doc-desc"><?= htmlspecialchars($rec['descripcion']) ?></div>
                                <?php endif; ?>
                                <div class="res-doc-breadcrumb">
                                    <?= htmlspecialchars($rec['unidad_titulo']) ?> → <?= htmlspecialchars($rec['leccion_titulo']) ?>
                                </div>
                                <span class="res-doc-type-tag" style="background:#f3e8ff;color:#7c3aed">Actividad práctica</span>
                            </div>
                            <div class="res-doc-actions">
                                <button class="res-btn res-btn-cloud" onclick="addToCloud('<?= addslashes(htmlspecialchars($rec['nombre'])) ?>','<?= addslashes(htmlspecialchars($rec['url_o_ruta'])) ?>')">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    Mi nube
                                </button>
                                <a class="res-btn res-btn-download" href="<?= htmlspecialchars($rec['url_o_ruta']) ?>" target="_blank">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Abrir
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>

                <?php endif; ?>

            </div><!-- /.repo-main -->
        </div>
    </div>
</main>

<div class="mc-toast-wrap" id="mcToastWrap"></div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';

function mcToast(msg, type = 'default') {
    const wrap  = document.getElementById('mcToastWrap');
    const toast = document.createElement('div');
    toast.className = 'mc-toast' + (type !== 'default' ? ' ' + type : '');
    toast.textContent = msg;
    wrap.appendChild(toast);
    requestAnimationFrame(() => { requestAnimationFrame(() => toast.classList.add('show')); });
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 250); }, 3000);
}

async function addToCloud(nombre, url) {
    const btn = event.currentTarget;
    const origHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '…';
    try {
        const fd = new FormData();
        fd.append('accion', 'guardar_en_nube');
        fd.append('nombre', nombre);
        fd.append('url', url);
        // Usamos un endpoint auxiliar — guardamos via dashboard nube-api
        const res = await fetch(BASE_URL + '/index.php?url=nube-api', { method: 'POST', body: fd }).then(r => r.json());
        if (res.ok) {
            mcToast('Guardado en tu nube', 'success');
            btn.innerHTML = '✓ En nube';
            btn.style.background = '#f0fdf4';
            btn.style.borderColor = '#86efac';
            btn.style.color = '#166534';
        } else {
            mcToast(res.error || 'No se pudo guardar', 'error');
            btn.innerHTML = origHTML;
            btn.disabled = false;
        }
    } catch(e) {
        mcToast('Error de conexión', 'error');
        btn.innerHTML = origHTML;
        btn.disabled = false;
    }
}
</script>
</body>
</html>
