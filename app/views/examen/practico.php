<?php
$tituloCurso  = htmlspecialchars($curso['titulo']  ?? 'Curso');
$tituloPrac   = htmlspecialchars($examenPractico['titulo'] ?? 'Examen Práctico');
$nTareas      = count($tareas);
$tiposIcono   = ['texto'=>'✏️','codigo'=>'💻','diseno'=>'🎨','proyecto'=>'🚀'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $tituloPrac ?> — MatrixCoders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
<style>
:root{--mc-green:#6B8F71;--mc-green-d:#4a6b50;--mc-dark:#1B2336;--mc-navy:#0f172a;--mc-border:#e5e7eb;--mc-soft:#f8fafc;--mc-muted:#6b7280;}
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Saira',sans-serif;background:#f6f6f6;color:var(--mc-dark);margin:0;}
.prac-wrap{max-width:820px;margin:0 auto;padding:28px 20px 60px;}
.prac-hero{background:linear-gradient(135deg,#1e3a5f 0%,#0f172a 100%);border-radius:18px;padding:28px 32px;color:#fff;margin-bottom:28px;}
.prac-hero .badge-tipo{display:inline-block;background:rgba(37,99,235,.3);color:#93c5fd;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:3px 10px;border-radius:20px;margin-bottom:10px;}
.prac-hero h1{font-size:1.35rem;font-weight:800;margin:0 0 8px;}
.prac-hero p{font-size:.88rem;color:#94a3b8;margin:0;line-height:1.6;}
.prac-meta{display:flex;gap:18px;margin-top:14px;flex-wrap:wrap;}
.prac-meta span{font-size:.8rem;color:#cbd5e1;}
.progress-sticky{position:sticky;top:66px;z-index:10;background:#fff;border-bottom:1px solid var(--mc-border);padding:8px 20px;display:flex;align-items:center;gap:10px;font-size:.8rem;color:var(--mc-muted);}
.progress-bar-wrap{flex:1;height:5px;background:var(--mc-border);border-radius:99px;overflow:hidden;}
.progress-bar-fill{height:100%;background:#2563eb;border-radius:99px;transition:width .3s;}
.tarea-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:24px;margin-bottom:20px;position:relative;transition:box-shadow .2s;}
.tarea-card.entregada{border-left:4px solid var(--mc-green);}
.tarea-card.pendiente{border-left:4px solid #e5e7eb;}
.tarea-num{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#2563eb;margin-bottom:6px;display:flex;align-items:center;gap:6px;}
.tarea-titulo{font-size:1rem;font-weight:700;color:var(--mc-dark);margin-bottom:10px;}
.tarea-enunciado{font-size:.9rem;color:#4b5563;line-height:1.7;margin-bottom:14px;padding:10px 14px;background:#f8fafc;border-radius:8px;border-left:3px solid #2563eb;}
.tarea-criterios{font-size:.82rem;color:var(--mc-muted);background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;margin-bottom:14px;}
.entrega-form textarea{width:100%;min-height:140px;border:1.5px solid var(--mc-border);border-radius:10px;padding:.85rem;font-family:'Saira',sans-serif;font-size:.9rem;resize:vertical;color:var(--mc-dark);line-height:1.6;transition:border-color .15s;}
.entrega-form textarea:focus{outline:none;border-color:#2563eb;}
.file-drop{border:2px dashed var(--mc-border);border-radius:10px;padding:14px;text-align:center;cursor:pointer;transition:all .2s;margin-top:10px;position:relative;}
.file-drop:hover,.file-drop.drag-over{border-color:#2563eb;background:#eff6ff;}
.file-drop input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.file-drop p{font-size:.82rem;color:var(--mc-muted);margin:0;}
.file-selected{font-size:.82rem;color:#2563eb;font-weight:600;margin-top:6px;}
.btn-entregar{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:.6rem 1.4rem;font-size:.9rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;transition:background .15s;margin-top:12px;}
.btn-entregar:hover{background:#1d4ed8;}
.btn-entregar:disabled{opacity:.5;cursor:not-allowed;}
.entregada-badge{display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #86efac;color:#166534;font-size:.8rem;font-weight:700;padding:4px 12px;border-radius:99px;margin-bottom:12px;}
.entregada-resp{background:#f8fafc;border-radius:8px;padding:10px 12px;font-size:.88rem;color:var(--mc-text);white-space:pre-wrap;line-height:1.6;margin-top:8px;border:1px solid var(--mc-border);}
.nota-revision{display:inline-flex;align-items:center;gap:8px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;font-size:.82rem;font-weight:700;padding:6px 14px;border-radius:99px;margin-top:8px;}
.completed-banner{background:linear-gradient(135deg,#166534,#15803d);color:#fff;border-radius:16px;padding:24px 28px;text-align:center;margin-bottom:24px;}
.completed-banner h2{font-size:1.4rem;font-weight:800;margin:0 0 8px;}
.completed-banner p{font-size:.9rem;opacity:.85;margin:0 0 16px;}
.btn-back{display:inline-flex;align-items:center;gap:.5rem;background:#fff;color:var(--mc-dark);border:1.5px solid var(--mc-border);border-radius:10px;padding:.55rem 1.1rem;font-size:.88rem;font-weight:700;text-decoration:none;font-family:'Saira',sans-serif;transition:all .15s;}
.btn-back:hover{border-color:var(--mc-green);color:var(--mc-green);}
.mc-toast-wrap{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
.mc-toast{background:#1e293b;color:#fff;border-radius:10px;padding:12px 18px;font-size:.88rem;font-weight:600;font-family:'Saira',sans-serif;box-shadow:0 4px 20px rgba(0,0,0,.25);opacity:0;transform:translateY(8px);transition:opacity .2s,transform .2s;pointer-events:none;max-width:340px;}
.mc-toast.show{opacity:1;transform:translateY(0);}
.mc-toast.success{background:#166534;}.mc-toast.error{background:#991b1b;}.mc-toast.info{background:#1e40af;}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>
<div class="mc-toast-wrap" id="mcToastWrap"></div>

<!-- Sticky progress -->
<div class="progress-sticky">
  <span id="progressLabel"><?= $totalEntregadas ?> / <?= $nTareas ?> entregadas</span>
  <div class="progress-bar-wrap">
    <div class="progress-bar-fill" id="progressFill" style="width:<?= $nTareas > 0 ? round(($totalEntregadas/$nTareas)*100) : 0 ?>%"></div>
  </div>
</div>

<div class="prac-wrap">

  <?php if ($todasEntregadas): ?>
  <div class="completed-banner">
    <div style="font-size:2.5rem;margin-bottom:10px">🎉</div>
    <h2>¡Examen práctico entregado!</h2>
    <p>Has enviado todas las tareas. El equipo docente las revisará y recibirás tu calificación próximamente.</p>
    <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $cursoId ?>" class="btn-back">← Volver al curso</a>
  </div>
  <?php endif; ?>

  <!-- Hero -->
  <div class="prac-hero">
    <div class="badge-tipo">🛠️ Examen práctico</div>
    <h1><?= $tituloPrac ?></h1>
    <p><?= htmlspecialchars($examenPractico['descripcion'] ?? '') ?></p>
    <div class="prac-meta">
      <span>📚 <?= $tituloCurso ?></span>
      <span>📋 <?= $nTareas ?> tarea<?= $nTareas !== 1 ? 's' : '' ?></span>
      <?php if (!empty($examenPractico['nota_minima'])): ?>
      <span>✅ Aprobado con <?= number_format((float)$examenPractico['nota_minima'],1) ?>/10 por tarea</span>
      <?php endif; ?>
      <span>⏱️ Sin límite de tiempo</span>
    </div>
  </div>

  <!-- Tasks -->
  <?php foreach ($tareas as $idx => $tarea):
    $entrega = $entregasExistentes[$tarea['id']] ?? null;
    $icono   = $tiposIcono[$tarea['tipo']] ?? '📝';
  ?>
  <div class="tarea-card <?= $entrega ? 'entregada' : 'pendiente' ?>" id="tarea-<?= $tarea['id'] ?>">
    <div class="tarea-num">
      <?= $icono ?> Tarea <?= $idx+1 ?> de <?= $nTareas ?>
      <span style="font-size:.68rem;background:#eff6ff;color:#2563eb;padding:1px 7px;border-radius:99px;font-weight:700;text-transform:capitalize"><?= htmlspecialchars($tarea['tipo']) ?></span>
      <span style="font-size:.68rem;background:#faf5ff;color:#7c3aed;padding:1px 7px;border-radius:99px;font-weight:700"><?= $tarea['puntos'] ?> pts</span>
    </div>

    <div class="tarea-titulo"><?= htmlspecialchars($tarea['titulo']) ?></div>

    <?php if (!empty($tarea['enunciado'])): ?>
    <div class="tarea-enunciado"><?= nl2br(htmlspecialchars($tarea['enunciado'])) ?></div>
    <?php endif; ?>

    <?php if (!empty($tarea['criterios'])): ?>
    <div class="tarea-criterios">
      <strong>Criterios de evaluación:</strong> <?= htmlspecialchars($tarea['criterios']) ?>
    </div>
    <?php endif; ?>

    <?php if ($entrega): ?>
      <!-- Already submitted -->
      <div class="entregada-badge">
        ✓ Entregada el <?= date('d/m/Y H:i', strtotime($entrega['entregado_en'])) ?>
      </div>
      <?php if (!empty($entrega['respuesta_texto'])): ?>
        <div class="entregada-resp"><?= htmlspecialchars($entrega['respuesta_texto']) ?></div>
      <?php endif; ?>
      <?php if (!empty($entrega['archivo'])): ?>
        <div style="margin-top:8px"><a href="<?= htmlspecialchars($entrega['archivo']) ?>" target="_blank" style="color:#2563eb;font-size:.85rem;font-weight:600">📎 Ver archivo adjunto</a></div>
      <?php endif; ?>
      <?php if ($entrega['revisado']): ?>
        <div class="nota-revision">
          ✅ Revisada
          <?php if ($entrega['nota'] !== null): ?> · Nota: <strong><?= number_format((float)$entrega['nota'],1) ?>/10</strong><?php endif; ?>
          <?php if (!empty($entrega['feedback'])): ?> · <?= htmlspecialchars($entrega['feedback']) ?><?php endif; ?>
        </div>
      <?php else: ?>
        <div style="font-size:.8rem;color:var(--mc-muted);margin-top:8px">⏳ Pendiente de revisión</div>
      <?php endif; ?>

      <!-- Allow re-submission if not yet reviewed -->
      <?php if (!$entrega['revisado']): ?>
      <details style="margin-top:12px">
        <summary style="font-size:.82rem;color:#2563eb;cursor:pointer;font-weight:600">Modificar entrega</summary>
        <div style="margin-top:10px"><?php include __DIR__ . '/partials/_entrega_form.php'; // reuse below ?></div>
      </details>
      <?php endif; ?>

    <?php else: ?>
      <!-- Submission form -->
      <div class="entrega-form" id="form-<?= $tarea['id'] ?>">
        <label style="display:block;font-size:.85rem;font-weight:700;margin-bottom:6px;color:var(--mc-dark)">Tu respuesta *</label>
        <textarea id="txt-<?= $tarea['id'] ?>" placeholder="Escribe aquí tu solución, razonamiento o descripción del trabajo realizado…"></textarea>

        <div class="file-drop" id="drop-<?= $tarea['id'] ?>"
             ondragover="this.classList.add('drag-over');event.preventDefault()"
             ondragleave="this.classList.remove('drag-over')"
             ondrop="handleDrop(event,<?= $tarea['id'] ?>)">
          <input type="file" id="file-<?= $tarea['id'] ?>"
                 accept=".pdf,.doc,.docx,.zip,.rar,.txt,.png,.jpg,.jpeg,.mp4,.py,.js,.html,.css,.php"
                 onchange="showFile(this,<?= $tarea['id'] ?>)">
          <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 6px"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
          <p>Arrastra un archivo o <strong style="color:#2563eb">haz clic</strong> para adjuntar</p>
          <p style="font-size:.75rem;margin-top:2px">PDF, DOC, ZIP, imágenes, código… Máx. 50 MB</p>
          <p id="fname-<?= $tarea['id'] ?>" class="file-selected" style="display:none"></p>
        </div>

        <button class="btn-entregar" onclick="entregar(<?= $tarea['id'] ?>)" id="btn-<?= $tarea['id'] ?>">
          Enviar tarea <?= $idx+1 ?> →
        </button>
      </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $cursoId ?>" class="btn-back">← Volver al curso</a>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL  = '<?= BASE_URL ?>';
const CURSO_ID  = <?= $cursoId ?>;
let entregadas  = <?= $totalEntregadas ?>;
const total     = <?= $nTareas ?>;

function mcToast(msg, type = 'default', duration = 3500) {
    const wrap  = document.getElementById('mcToastWrap');
    const toast = document.createElement('div');
    toast.className = 'mc-toast' + (type !== 'default' ? ' ' + type : '');
    toast.textContent = msg;
    wrap.appendChild(toast);
    requestAnimationFrame(() => { requestAnimationFrame(() => toast.classList.add('show')); });
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 250); }, duration);
}

function updateProgress(n) {
  entregadas = n;
  document.getElementById('progressLabel').textContent = n + ' / ' + total + ' entregadas';
  document.getElementById('progressFill').style.width  = Math.round((n / total) * 100) + '%';
}

function showFile(input, tareaId) {
  const fname = document.getElementById('fname-' + tareaId);
  if (input.files[0]) {
    fname.textContent = '📎 ' + input.files[0].name;
    fname.style.display = 'block';
  } else {
    fname.style.display = 'none';
  }
}

function handleDrop(e, tareaId) {
  e.preventDefault();
  document.getElementById('drop-' + tareaId).classList.remove('drag-over');
  const f = e.dataTransfer.files[0];
  if (!f) return;
  const input = document.getElementById('file-' + tareaId);
  const dt = new DataTransfer();
  dt.items.add(f);
  input.files = dt.files;
  showFile(input, tareaId);
}

async function entregar(tareaId) {
  const btn     = document.getElementById('btn-' + tareaId);
  const txt     = document.getElementById('txt-' + tareaId)?.value || '';
  const fileInp = document.getElementById('file-' + tareaId);
  const file    = fileInp?.files[0];

  if (!txt.trim() && !file) {
    mcToast('Debes escribir una respuesta o adjuntar un archivo antes de enviar.', 'info');
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Enviando…';

  const fd = new FormData();
  fd.append('tarea_id', tareaId);
  fd.append('respuesta_texto', txt);
  if (file) fd.append('archivo', file);

  try {
    const res = await fetch(`${BASE_URL}/index.php?url=examen-practico&curso=${CURSO_ID}`, { method: 'POST', body: fd }).then(r => r.json());

    if (res.ok) {
      mcToast('Tarea enviada correctamente', 'success');
      const card = document.getElementById('tarea-' + tareaId);
      card.classList.replace('pendiente', 'entregada');
      card.querySelector('.entrega-form').innerHTML = `
        <div class="entregada-badge">✓ Entregada · En espera de revisión</div>
        ${txt ? `<div class="entregada-resp">${txt.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>` : ''}
        <div style="font-size:.8rem;color:var(--mc-muted);margin-top:8px">⏳ Pendiente de revisión</div>`;
      updateProgress(res.entregadas);
      if (res.completado) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        document.querySelector('.prac-hero').insertAdjacentHTML('beforebegin', `
          <div class="completed-banner">
            <div style="font-size:2.5rem;margin-bottom:10px">🎉</div>
            <h2>¡Examen práctico entregado!</h2>
            <p>Has enviado todas las tareas. El equipo docente las revisará y recibirás tu calificación próximamente.</p>
            <a href="${BASE_URL}/index.php?url=detallecurso&id=${CURSO_ID}" class="btn-back">← Volver al curso</a>
          </div>`);
      }
    } else {
      mcToast(res.error || 'Error al enviar. Inténtalo de nuevo.', 'error');
      btn.disabled = false;
      btn.textContent = 'Enviar tarea →';
    }
  } catch (err) {
    mcToast('Error de conexión. Comprueba tu red e inténtalo de nuevo.', 'error');
    btn.disabled = false;
    btn.textContent = 'Enviar tarea →';
  }
}
</script>
</body>
</html>
