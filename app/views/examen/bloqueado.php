<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Examen bloqueado — <?= htmlspecialchars($curso['titulo'] ?? 'Curso') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
<style>
*,*::before,*::after{box-sizing:border-box;}
body{font-family:'Saira',sans-serif;background:#f6f6f6;color:#1B2336;margin:0;}
.blk-wrap{max-width:600px;margin:60px auto;padding:0 20px 60px;}
.blk-card{background:#fff;border-radius:18px;box-shadow:0 4px 24px rgba(0,0,0,.08);overflow:hidden;}
.blk-header{background:linear-gradient(135deg,#1e3a5f,#0f172a);padding:36px 40px;text-align:center;color:#fff;}
.blk-header .icon{font-size:3rem;margin-bottom:14px;}
.blk-header h1{font-size:1.4rem;font-weight:800;margin:0 0 8px;}
.blk-header p{font-size:.9rem;color:#94a3b8;margin:0;}
.blk-body{padding:32px 36px;}
.blk-progress-wrap{margin-bottom:24px;}
.blk-progress-label{display:flex;justify-content:space-between;font-size:.85rem;font-weight:700;margin-bottom:8px;}
.blk-progress-bar{height:10px;background:#e5e7eb;border-radius:99px;overflow:hidden;}
.blk-progress-fill{height:100%;background:linear-gradient(90deg,#6B8F71,#4a9d5e);border-radius:99px;transition:width .5s;}
.blk-info{display:flex;flex-direction:column;gap:10px;margin-bottom:28px;}
.blk-info-row{display:flex;align-items:center;gap:12px;padding:12px 16px;background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb;}
.blk-info-row .ri{font-size:1.2rem;flex-shrink:0;}
.blk-info-row div{font-size:.88rem;}
.blk-info-row strong{display:block;font-weight:700;color:#1B2336;margin-bottom:1px;}
.blk-info-row span{color:#6b7280;}
.blk-actions{display:flex;gap:10px;flex-wrap:wrap;}
.btn-primary-mc{flex:1;background:#6B8F71;color:#fff;border:none;border-radius:10px;padding:.65rem 1.4rem;font-size:.92rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;text-align:center;text-decoration:none;display:inline-block;transition:background .15s;}
.btn-primary-mc:hover{background:#4a6b50;color:#fff;}
.btn-outline-mc{flex:1;background:#fff;color:#1B2336;border:1.5px solid #e5e7eb;border-radius:10px;padding:.65rem 1.4rem;font-size:.92rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;text-align:center;text-decoration:none;display:inline-block;transition:all .15s;}
.btn-outline-mc:hover{border-color:#6B8F71;color:#6B8F71;}
</style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="blk-wrap">
  <div class="blk-card">
    <div class="blk-header">
      <div class="icon">🔒</div>
      <h1>Examen bloqueado</h1>
      <p>Debes completar todas las lecciones del curso antes de acceder al examen final.</p>
    </div>

    <div class="blk-body">
      <div class="blk-progress-wrap">
        <div class="blk-progress-label">
          <span>Progreso del curso</span>
          <span><?= $progresoExamen ?>%</span>
        </div>
        <div class="blk-progress-bar">
          <div class="blk-progress-fill" style="width:<?= $progresoExamen ?>%"></div>
        </div>
      </div>

      <div class="blk-info">
        <div class="blk-info-row">
          <div class="ri">📚</div>
          <div>
            <strong><?= htmlspecialchars($curso['titulo'] ?? '') ?></strong>
            <span>Curso en progreso</span>
          </div>
        </div>
        <div class="blk-info-row">
          <div class="ri">✅</div>
          <div>
            <strong><?= $leccionesVistas ?> de <?= $totalLecciones ?> lecciones completadas</strong>
            <span>Te <?= $leccionesRestantes === 1 ? 'queda 1 lección' : "quedan $leccionesRestantes lecciones" ?> por ver</span>
          </div>
        </div>
        <div class="blk-info-row" style="background:#fff7ed;border-color:#fed7aa;">
          <div class="ri">💡</div>
          <div>
            <strong style="color:#92400e">Marca las lecciones como vistas</strong>
            <span style="color:#b45309">Al terminar cada vídeo el progreso se registra automáticamente.</span>
          </div>
        </div>
      </div>

      <div class="blk-actions">
        <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= (int)$cursoId ?>" class="btn-outline-mc">
          ← Volver al curso
        </a>
        <?php
        // Find the first unwatched lesson
        try {
            $stmtPrimera = $db->prepare("
                SELECT l.id FROM leccion l
                JOIN unidad u ON l.unidad_id=u.id
                WHERE u.curso_id=?
                  AND l.id NOT IN (
                    SELECT lv.leccion_id FROM leccion_vista lv WHERE lv.usuario_id=?
                  )
                ORDER BY u.orden, l.orden LIMIT 1
            ");
            $stmtPrimera->execute([$cursoId, $usuarioId]);
            $primeraLeccionId = $stmtPrimera->fetchColumn();
        } catch (Exception $e) { $primeraLeccionId = null; }
        ?>
        <?php if ($primeraLeccionId): ?>
        <a href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= (int)$primeraLeccionId ?>" class="btn-primary-mc">
          Continuar lecciones →
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
</body>
</html>
