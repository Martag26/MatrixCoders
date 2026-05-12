<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Suscripción activada! — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <style>
        :root { --mc-green: #6B8F71; --mc-green-d: #4a6b50; }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Saira', sans-serif; background: #f3f4f6; margin: 0; }

        .ok-page { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }

        .ok-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 12px 50px rgba(0,0,0,.1);
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }

        .ok-circle {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6B8F71, #3d6644);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 28px rgba(107,143,113,.4);
        }
        .ok-circle svg { stroke: #fff; }

        .ok-title { font-size: 1.6rem; font-weight: 900; color: #111827; margin: 0 0 10px; }
        .ok-sub   { font-size: .93rem; color: #6b7280; line-height: 1.65; margin: 0 0 32px; }

        .ok-plan-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: #f0fdf4; color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 99px;
            font-size: .85rem; font-weight: 700;
            padding: 6px 18px;
            margin-bottom: 32px;
        }

        .ok-actions { display: flex; flex-direction: column; gap: 10px; }

        .ok-btn {
            display: block;
            background: var(--mc-green);
            color: #fff;
            border-radius: 12px;
            padding: 13px 20px;
            font-weight: 700;
            font-size: .95rem;
            text-decoration: none;
            transition: background .15s;
        }
        .ok-btn:hover { background: var(--mc-green-d); color: #fff; }

        .ok-btn-outline {
            display: block;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 11px 20px;
            font-weight: 600;
            font-size: .9rem;
            color: #374151;
            text-decoration: none;
            transition: background .12s;
        }
        .ok-btn-outline:hover { background: #f9fafb; color: #111827; }
    </style>
</head>

<body>

<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="ok-page">
    <?php
    require_once __DIR__ . '/../../helpers/PlanHelper.php';
    $plan  = trim($_GET['plan'] ?? ($_SESSION['usuario_plan'] ?? ''));
    $label = PlanHelper::etiqueta($plan);
    ?>
    <div class="ok-card">

        <div class="ok-circle">
            <svg width="36" height="36" fill="none" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>

        <h1 class="ok-title">¡Suscripción activada!</h1>
        <p class="ok-sub">
            Tu plan ha quedado activo de forma inmediata.<br>
            Ya puedes disfrutar de todas las ventajas incluidas.
        </p>

        <?php if ($label): ?>
            <div class="ok-plan-badge">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Plan activo: <?= htmlspecialchars($label) ?>
            </div>
        <?php endif; ?>

        <div class="ok-actions">
            <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="ok-btn">
                Ir al panel de aprendizaje
            </a>
            <a href="<?= BASE_URL ?>/index.php" class="ok-btn-outline">
                Ver todos los cursos
            </a>
        </div>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
