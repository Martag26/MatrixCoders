<?php
$tituloExamen = htmlspecialchars($examen['titulo'] ?? 'Examen Final');
$tituloCurso  = htmlspecialchars($curso['titulo']  ?? 'Curso');
$nPreguntas   = count($preguntas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $tituloExamen ?> — MatrixCoders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<style>
:root{--mc-green:#6B8F71;--mc-green-d:#4a6b50;--mc-navy:#0f172a;--mc-dark:#1B2336;--mc-border:#e5e7eb;--mc-soft:#f8fafc;--mc-muted:#6b7280;}
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Saira',sans-serif;background:#f1f5f9;color:var(--mc-dark);margin:0;}

/* Layout */
.exam-layout{display:grid;grid-template-columns:1fr 280px;gap:24px;max-width:1160px;margin:0 auto;padding:28px 20px 60px;}
@media(max-width:900px){.exam-layout{grid-template-columns:1fr;}.exam-sidebar{display:none;}}

/* Sticky header */
.exam-sticky-header{position:sticky;top:66px;z-index:50;background:#fff;border-bottom:1px solid var(--mc-border);padding:0;}
.exam-progress-track{display:flex;align-items:center;gap:14px;padding:10px 24px;max-width:1160px;margin:0 auto;}
.exam-progress-bar-wrap{flex:1;height:10px;background:#e5e7eb;border-radius:99px;overflow:visible;position:relative;}
.exam-progress-bar-fill{height:100%;background:linear-gradient(90deg,#4a6b50,var(--mc-green));border-radius:99px;transition:width .3s;position:relative;min-width:0;}
.exam-progress-bar-fill::after{content:'';position:absolute;right:-5px;top:50%;transform:translateY(-50%);width:14px;height:14px;border-radius:50%;background:var(--mc-green);border:2px solid #fff;box-shadow:0 0 0 2px var(--mc-green);display:var(--dot-display,none);}
.exam-progress-text{font-size:.8rem;font-weight:700;color:var(--mc-muted);white-space:nowrap;}

/* Hero */
.exam-hero{background:linear-gradient(135deg,var(--mc-navy) 0%,#1e3a5f 100%);border-radius:16px;padding:28px 32px;color:#fff;margin-bottom:24px;}
.exam-hero-kicker{display:inline-block;background:rgba(107,143,113,.3);color:#a3d9a8;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:3px 10px;border-radius:20px;margin-bottom:10px;}
.exam-hero h1{font-size:1.3rem;font-weight:800;margin:0 0 6px;}
.exam-hero p{font-size:.88rem;color:#94a3b8;margin:0 0 16px;line-height:1.6;}
.exam-hero-meta{display:flex;gap:20px;flex-wrap:wrap;}
.exam-hero-meta span{font-size:.8rem;color:#cbd5e1;display:flex;align-items:center;gap:5px;}

/* Question cards */
.q-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:28px 32px;margin-bottom:16px;border:2px solid transparent;transition:border-color .15s;}
.q-card.answered{border-color:#86efac;}
.q-num-tag{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--mc-green);margin-bottom:10px;display:flex;align-items:center;gap:8px;}
.q-num-badge{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:#f0fdf4;color:var(--mc-green);font-weight:800;font-size:.8rem;}
.q-enunciado{font-size:1rem;font-weight:700;color:var(--mc-dark);margin-bottom:18px;line-height:1.5;}

/* Options */
.opciones-grid{display:flex;flex-direction:column;gap:8px;}
.opcion-btn{display:flex;align-items:center;gap:12px;padding:13px 18px;border:2px solid var(--mc-border);border-radius:11px;cursor:pointer;background:#fff;transition:all .15s;text-align:left;width:100%;font-family:'Saira',sans-serif;font-size:.9rem;color:var(--mc-dark);}
.opcion-btn:hover{border-color:var(--mc-green);background:#f0fdf4;}
.opcion-btn.selected{border-color:var(--mc-green);background:#f0fdf4;color:var(--mc-green-d);}
.opcion-btn.selected .opcion-letter{background:var(--mc-green);color:#fff;border-color:var(--mc-green);}
.opcion-letter{width:28px;height:28px;border-radius:7px;background:var(--mc-soft);border:1.5px solid var(--mc-border);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;flex-shrink:0;transition:all .15s;}
.opcion-text{flex:1;}
input[type=radio].opcion-radio{display:none;}

/* Submit footer */
.exam-footer{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:22px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;}
.exam-footer-info{font-size:.88rem;color:var(--mc-muted);}
.exam-footer-info strong{color:var(--mc-dark);}
.btn-submit-exam{background:var(--mc-green);color:#fff;border:none;border-radius:10px;padding:.7rem 2rem;font-size:.95rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;transition:background .15s;display:flex;align-items:center;gap:8px;}
.btn-submit-exam:hover{background:var(--mc-green-d);}
.btn-submit-exam:disabled{opacity:.5;cursor:not-allowed;}
.btn-back-exam{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border:1.5px solid var(--mc-border);border-radius:9px;text-decoration:none;font-size:.85rem;font-weight:600;color:var(--mc-muted);transition:all .15s;}
.btn-back-exam:hover{border-color:var(--mc-green);color:var(--mc-green);}

/* Sidebar */
.exam-sidebar{position:sticky;top:100px;align-self:start;}
.sidebar-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:20px;}
.sidebar-card h3{font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--mc-muted);margin:0 0 14px;}
.q-nav-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;}
.q-nav-btn{width:100%;aspect-ratio:1;border:2px solid var(--mc-border);border-radius:8px;background:#fff;font-size:.82rem;font-weight:700;color:var(--mc-muted);cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;}
.q-nav-btn:hover{border-color:var(--mc-green);color:var(--mc-green);}
.q-nav-btn.answered{background:#f0fdf4;border-color:#86efac;color:var(--mc-green-d);}
.q-nav-btn.current{border-color:var(--mc-green);background:var(--mc-green);color:#fff;}
.sidebar-legend{display:flex;flex-direction:column;gap:6px;margin-top:14px;padding-top:14px;border-top:1px solid var(--mc-border);}
.legend-row{display:flex;align-items:center;gap:8px;font-size:.78rem;color:var(--mc-muted);}
.legend-dot{width:12px;height:12px;border-radius:3px;flex-shrink:0;}

/* Confirm modal */
.exam-confirm-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;z-index:9000;opacity:0;pointer-events:none;transition:opacity .2s;}
.exam-confirm-overlay.show{opacity:1;pointer-events:all;}
.exam-confirm-box{background:#fff;border-radius:16px;padding:32px 36px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);transform:translateY(12px);transition:transform .2s;}
.exam-confirm-overlay.show .exam-confirm-box{transform:translateY(0);}
.exam-confirm-box h3{font-size:1.1rem;font-weight:800;margin:0 0 10px;}
.exam-confirm-box p{font-size:.9rem;color:var(--mc-muted);margin:0 0 6px;line-height:1.6;}
.exam-confirm-box .warn{color:#92400e;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:8px 12px;font-size:.83rem;margin-bottom:20px;}
.confirm-actions{display:flex;gap:10px;}
.confirm-btn-ok{flex:1;background:var(--mc-green);color:#fff;border:none;border-radius:9px;padding:.6rem 1.2rem;font-size:.9rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;transition:background .15s;}
.confirm-btn-ok:hover{background:var(--mc-green-d);}
.confirm-btn-cancel{flex:1;background:#fff;color:var(--mc-dark);border:1.5px solid var(--mc-border);border-radius:9px;padding:.6rem 1.2rem;font-size:.9rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;transition:all .15s;}
.confirm-btn-cancel:hover{border-color:var(--mc-green);}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<!-- Sticky progress -->
<div class="exam-sticky-header">
  <div class="exam-progress-track">
    <span class="exam-progress-text" id="progressText">0 / <?= $nPreguntas ?> respondidas</span>
    <div class="exam-progress-bar-wrap">
      <div class="exam-progress-bar-fill" id="progressFill" style="width:0%"></div>
    </div>
    <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= (int)$curso['id'] ?>" class="btn-back-exam">← Curso</a>
  </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/index.php?url=examen&curso=<?= (int)$curso['id'] ?>" id="examForm">
<div class="exam-layout">

  <!-- Main column -->
  <div>
    <!-- Hero -->
    <div class="exam-hero">
      <div class="exam-hero-kicker">📝 Evaluación final</div>
      <h1><?= $tituloExamen ?></h1>
      <p><?= htmlspecialchars($examen['descripcion'] ?? 'Responde todas las preguntas con calma. Solo tienes una respuesta correcta por pregunta.') ?></p>
      <div class="exam-hero-meta">
        <span>❓ <?= $nPreguntas ?> preguntas</span>
        <span>✅ Nota mínima <?= number_format((float)$examen['nota_minima'],1) ?>/10</span>
        <span>📚 <?= $tituloCurso ?></span>
        <span>⏱️ Sin límite de tiempo</span>
      </div>
    </div>

    <!-- Questions -->
    <?php foreach ($preguntas as $idx => $p): ?>
    <div class="q-card" id="qcard-<?= $p['id'] ?>" data-idx="<?= $idx ?>">
      <div class="q-num-tag">
        <span class="q-num-badge"><?= $idx+1 ?></span>
        Pregunta <?= $idx+1 ?> de <?= $nPreguntas ?>
      </div>
      <div class="q-enunciado"><?= htmlspecialchars($p['enunciado']) ?></div>
      <div class="opciones-grid">
        <?php foreach ($p['opciones'] as $oi => $op): ?>
        <label class="opcion-btn" id="lbl-<?= $op['id'] ?>" for="radio-<?= $op['id'] ?>">
          <input type="radio" class="opcion-radio" id="radio-<?= $op['id'] ?>"
                 name="p<?= $p['id'] ?>" value="<?= $op['id'] ?>"
                 onchange="onSelect(this, <?= $p['id'] ?>, <?= $idx ?>)">
          <span class="opcion-letter"><?= chr(65+$oi) ?></span>
          <span class="opcion-text"><?= htmlspecialchars($op['texto']) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Footer -->
    <div class="exam-footer">
      <div class="exam-footer-info">
        <strong id="footerAnswered">0</strong> de <strong><?= $nPreguntas ?></strong> preguntas respondidas
      </div>
      <button type="button" class="btn-submit-exam" id="btnSubmit" onclick="confirmarEnvio()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
        Enviar examen
      </button>
    </div>
  </div>

  <!-- Sidebar -->
  <aside class="exam-sidebar">
    <div class="sidebar-card">
      <h3>Navegador de preguntas</h3>
      <div class="q-nav-grid">
        <?php foreach ($preguntas as $idx => $p): ?>
        <button type="button" class="q-nav-btn" id="nav-<?= $p['id'] ?>" data-qid="<?= $p['id'] ?>"
                onclick="scrollToQuestion(<?= $p['id'] ?>)">
          <?= $idx+1 ?>
        </button>
        <?php endforeach; ?>
      </div>
      <div class="sidebar-legend">
        <div class="legend-row"><span class="legend-dot" style="background:#f0fdf4;border:2px solid #86efac"></span>Respondida</div>
        <div class="legend-row"><span class="legend-dot" style="background:#fff;border:2px solid #e5e7eb"></span>Sin responder</div>
      </div>
    </div>
  </aside>

</div>
</form>

<!-- Confirm modal -->
<div class="exam-confirm-overlay" id="confirmOverlay">
  <div class="exam-confirm-box">
    <h3>¿Enviar el examen?</h3>
    <p id="confirmMsg">Estás a punto de enviar tu examen.</p>
    <p class="warn" id="confirmWarn"></p>
    <div class="confirm-actions">
      <button class="confirm-btn-cancel" onclick="closeConfirm()">Revisar respuestas</button>
      <button class="confirm-btn-ok" id="confirmOkBtn">Enviar ahora</button>
    </div>
  </div>
</div>

<script>
const TOTAL = <?= $nPreguntas ?>;
let answered = 0;
const answeredSet = new Set();

function onSelect(radio, preguntaId, idx) {
  // Deselect all options for this question
  document.querySelectorAll(`input[name="p${preguntaId}"]`).forEach(r => {
    document.getElementById('lbl-' + r.value)?.classList.remove('selected');
  });
  // Select the clicked one
  radio.closest('label').classList.add('selected');

  const wasAnswered = answeredSet.has(preguntaId);
  answeredSet.add(preguntaId);
  answered = answeredSet.size;

  // Update card style
  const card = document.getElementById('qcard-' + preguntaId);
  if (card) card.classList.add('answered');

  // Update nav button
  const navBtn = document.getElementById('nav-' + preguntaId);
  if (navBtn) navBtn.classList.add('answered');

  // Update progress
  const pct = Math.round((answered / TOTAL) * 100);
  const fill = document.getElementById('progressFill');
  fill.style.width = pct + '%';
  fill.style.setProperty('--dot-display', answered > 0 ? 'block' : 'none');
  document.getElementById('progressText').textContent = answered + ' / ' + TOTAL + ' respondidas';
  document.getElementById('footerAnswered').textContent = answered;
}

function scrollToQuestion(qid) {
  const card = document.getElementById('qcard-' + qid);
  if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function confirmarEnvio() {
  const sin = TOTAL - answered;
  const msg = document.getElementById('confirmMsg');
  const warn = document.getElementById('confirmWarn');
  const okBtn = document.getElementById('confirmOkBtn');

  if (sin === 0) {
    msg.textContent = 'Has respondido todas las preguntas. ¿Deseas enviar el examen?';
    warn.style.display = 'none';
  } else {
    msg.textContent = 'Estás a punto de enviar el examen.';
    warn.textContent = `Atención: tienes ${sin} pregunta${sin > 1 ? 's' : ''} sin responder. Las preguntas sin respuesta contarán como incorrectas.`;
    warn.style.display = 'block';
  }

  okBtn.onclick = () => {
    okBtn.textContent = 'Enviando…';
    okBtn.disabled = true;
    document.getElementById('examForm').submit();
  };

  document.getElementById('confirmOverlay').classList.add('show');
}

function closeConfirm() {
  document.getElementById('confirmOverlay').classList.remove('show');
}

document.getElementById('confirmOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeConfirm();
});
</script>
</body>
</html>
