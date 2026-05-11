<?php
$tituloCurso  = htmlspecialchars($curso['titulo'] ?? 'el curso');
$imagenCurso  = $curso['imagen'] ?? '';
$certCodigo   = $certificado['codigo'] ?? null;
$certFecha    = $certificado ? date('d/m/Y', strtotime($certificado['emitido_en'])) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>¡Curso completado! — MatrixCoders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
<style>
:root{--mc-gold:#f59e0b;--mc-gold-d:#d97706;--mc-navy:#0f172a;--mc-dark:#1e3a5f;--mc-green:#22c55e;}
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Saira',sans-serif;background:#0f172a;color:#fff;margin:0;min-height:100vh;overflow-x:hidden;}

/* Confetti canvas */
#confetti-canvas{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:1;}

/* Main wrapper */
.comp-page{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:calc(100vh - 120px);padding:40px 20px;}

/* Trophy */
.trophy-ring{width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#fbbf24,#f59e0b);box-shadow:0 0 0 8px rgba(245,158,11,.25),0 0 60px rgba(245,158,11,.35);display:flex;align-items:center;justify-content:center;font-size:3.4rem;margin-bottom:28px;animation:pulse-ring 2s ease-in-out infinite;}
@keyframes pulse-ring{0%,100%{box-shadow:0 0 0 8px rgba(245,158,11,.25),0 0 60px rgba(245,158,11,.35);}50%{box-shadow:0 0 0 16px rgba(245,158,11,.15),0 0 80px rgba(245,158,11,.5);}}

/* Title */
.comp-eyebrow{font-size:.8rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--mc-gold);margin-bottom:10px;}
.comp-title{font-size:clamp(2rem,5vw,3.2rem);font-weight:900;text-align:center;line-height:1.15;margin:0 0 10px;}
.comp-subtitle{font-size:1.05rem;color:#94a3b8;text-align:center;max-width:520px;margin:0 auto 36px;}

/* Stats strip */
.stats-strip{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:36px;}
.stat-pill{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:40px;padding:10px 22px;display:flex;align-items:center;gap:10px;}
.stat-pill .stat-icon{font-size:1.3rem;}
.stat-pill .stat-val{font-size:1.1rem;font-weight:800;color:#fff;}
.stat-pill .stat-lbl{font-size:.75rem;color:#94a3b8;margin-top:1px;}

/* Card */
.comp-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:32px 36px;max-width:560px;width:100%;text-align:center;backdrop-filter:blur(8px);margin-bottom:32px;}
.comp-card h3{font-size:1rem;font-weight:700;color:var(--mc-gold);margin:0 0 8px;letter-spacing:.5px;text-transform:uppercase;}
.comp-card p{color:#cbd5e1;font-size:.92rem;margin:0;}

/* Certificate badge */
.cert-badge{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,rgba(245,158,11,.2),rgba(251,191,36,.1));border:1px solid rgba(245,158,11,.4);border-radius:12px;padding:10px 18px;margin:14px 0;font-size:.85rem;color:#fbbf24;font-weight:600;}
.cert-code{font-family:monospace;font-size:.8rem;background:rgba(245,158,11,.15);padding:2px 8px;border-radius:6px;color:#fde68a;}

/* Action buttons */
.comp-actions{display:flex;gap:14px;flex-wrap:wrap;justify-content:center;}
.btn-cert{display:inline-flex;align-items:center;gap:9px;padding:15px 32px;border-radius:14px;font-family:'Saira',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-cert-primary{background:linear-gradient(135deg,var(--mc-gold),var(--mc-gold-d));color:#0f172a;box-shadow:0 4px 20px rgba(245,158,11,.4);}
.btn-cert-primary:hover{transform:translateY(-2px);box-shadow:0 6px 28px rgba(245,158,11,.55);color:#0f172a;}
.btn-cert-ghost{background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.15);}
.btn-cert-ghost:hover{background:rgba(255,255,255,.14);color:#fff;}

/* Stars decoration */
.stars{text-align:center;font-size:1.2rem;margin-bottom:20px;opacity:.6;letter-spacing:4px;}

@media(max-width:600px){.comp-card{padding:24px 20px;}.btn-cert{width:100%;justify-content:center;}}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>
<canvas id="confetti-canvas"></canvas>

<div class="comp-page">
  <div class="trophy-ring">🏆</div>

  <p class="comp-eyebrow">¡Felicidades, <?= htmlspecialchars($nombreAlumno) ?>!</p>
  <h1 class="comp-title">Has completado<br><?= $tituloCurso ?></h1>
  <p class="comp-subtitle">Has superado todas las etapas del curso. Tu esfuerzo y dedicación han dado sus frutos.</p>

  <!-- Stats -->
  <div class="stats-strip">
    <?php if ($mediaNotas !== null): ?>
    <div class="stat-pill">
      <span class="stat-icon">📊</span>
      <div>
        <div class="stat-val"><?= number_format($mediaNotas, 1) ?>/10</div>
        <div class="stat-lbl">Nota práctico</div>
      </div>
    </div>
    <?php endif; ?>
    <div class="stat-pill">
      <span class="stat-icon">🎓</span>
      <div>
        <div class="stat-val">Completado</div>
        <div class="stat-lbl">Estado del curso</div>
      </div>
    </div>
    <?php if ($certFecha): ?>
    <div class="stat-pill">
      <span class="stat-icon">📅</span>
      <div>
        <div class="stat-val"><?= $certFecha ?></div>
        <div class="stat-lbl">Certificado emitido</div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Certificate card -->
  <?php if ($certificado): ?>
  <div class="comp-card">
    <h3>🎖️ Certificado disponible</h3>
    <p>Tu certificado oficial de MatrixCoders está listo. Puedes descargarlo o imprimirlo en PDF.</p>
    <?php if ($certCodigo): ?>
    <div class="cert-badge">
      🔐 Código de verificación: <span class="cert-code"><?= htmlspecialchars($certCodigo) ?></span>
    </div>
    <?php endif; ?>
    <div class="comp-actions" style="margin-top:20px">
      <a href="<?= BASE_URL ?>/index.php?url=examen&curso=<?= $cursoId ?>" class="btn-cert btn-cert-primary">
        ⬇️ Descargar certificado
      </a>
    </div>
  </div>
  <?php else: ?>
  <div class="comp-card">
    <h3>🎓 ¡Curso superado!</h3>
    <p>Has completado el curso con éxito. Tu certificado se generará en breve.</p>
  </div>
  <?php endif; ?>

  <!-- Navigation -->
  <div class="comp-actions">
    <a href="<?= BASE_URL ?>/index.php?url=mis-cursos" class="btn-cert btn-cert-ghost">📚 Mis cursos</a>
    <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="btn-cert btn-cert-ghost">🏠 Ir al inicio</a>
  </div>

  <div class="stars" style="margin-top:36px">⭐ ⭐ ⭐ ⭐ ⭐</div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script>
// Simple confetti animation
(function () {
  const canvas = document.getElementById('confetti-canvas');
  const ctx    = canvas.getContext('2d');
  canvas.width  = window.innerWidth;
  canvas.height = window.innerHeight;
  window.addEventListener('resize', () => {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
  });

  const colors = ['#f59e0b','#fbbf24','#22c55e','#60a5fa','#f472b6','#a78bfa','#fb923c'];
  const pieces = Array.from({length: 120}, () => ({
    x:     Math.random() * canvas.width,
    y:     Math.random() * canvas.height - canvas.height,
    w:     6 + Math.random() * 8,
    h:     10 + Math.random() * 8,
    color: colors[Math.floor(Math.random() * colors.length)],
    rot:   Math.random() * Math.PI * 2,
    vx:    (Math.random() - .5) * 2.5,
    vy:    2 + Math.random() * 3.5,
    vr:    (Math.random() - .5) * .12,
    alpha: .85 + Math.random() * .15,
  }));

  let running = true;
  let frame   = 0;

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    pieces.forEach(p => {
      p.x  += p.vx;
      p.y  += p.vy;
      p.rot += p.vr;
      if (p.y > canvas.height + 20) {
        p.y  = -20;
        p.x  = Math.random() * canvas.width;
      }
      ctx.save();
      ctx.globalAlpha = p.alpha;
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rot);
      ctx.fillStyle = p.color;
      ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
      ctx.restore();
    });
    frame++;
    // Stop after 6 seconds (~360 frames at 60fps)
    if (frame < 360 && running) requestAnimationFrame(draw);
    else ctx.clearRect(0, 0, canvas.width, canvas.height);
  }
  draw();
})();
</script>
</body>
</html>
