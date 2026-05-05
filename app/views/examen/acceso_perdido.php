<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso perdido — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <style>
        body { font-family:'Saira',sans-serif; background:#f1f5f9; color:#1B2336; }
        .ap-wrap { max-width:520px; margin:80px auto; padding:20px; text-align:center; }
        .ap-icon { font-size:3.5rem; margin-bottom:1.25rem; }
        .ap-title { font-size:1.5rem; font-weight:800; margin:0 0 .75rem; color:#dc2626; }
        .ap-text  { font-size:.93rem; color:#6b7280; line-height:1.7; margin:0 0 1.75rem; }
        .ap-card  { background:#fff; border:1.5px solid #fecaca; border-radius:16px; padding:2rem; margin-bottom:1.5rem; }
        .ap-btn   { display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1.4rem; border-radius:10px; font-weight:700; font-size:.9rem; font-family:'Saira',sans-serif; text-decoration:none; background:#1B2336; color:#fff; transition:background .15s; }
        .ap-btn:hover { background:#0f172a; color:#fff; }
        .ap-btn-secondary { background:#f3f4f6; color:#374151; border:1.5px solid #e5e7eb; margin-left:.5rem; }
        .ap-btn-secondary:hover { background:#e5e7eb; color:#374151; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="ap-wrap">
    <div class="ap-card">
        <div class="ap-icon">🚫</div>
        <h1 class="ap-title">Has perdido el acceso a este curso</h1>
        <p class="ap-text">
            Has agotado los <strong>2 intentos</strong> disponibles para el examen de
            <strong><?= htmlspecialchars($cursoRevocado['titulo'] ?? 'este curso') ?></strong>
            sin obtener la nota mínima requerida.<br><br>
            Según las normas de la plataforma, perder los 2 intentos implica la <strong>revocación de tu matrícula</strong>
            y la pérdida del derecho a certificado en este curso.
        </p>
        <p style="font-size:.82rem;color:#9ca3af;margin:0">
            Si crees que esto es un error o deseas solicitar una excepción, contacta con soporte.
        </p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?url=mis-cursos" class="ap-btn">← Mis cursos</a>
    <a href="<?= BASE_URL ?>/index.php" class="ap-btn ap-btn-secondary">Ver catálogo</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
