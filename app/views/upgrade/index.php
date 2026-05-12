<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mejora tu plan · MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
    <style>
        .upgrade-main { flex:1; min-width:0; padding:2.5rem 1.5rem; display:flex; flex-direction:column; align-items:center; justify-content:center; }
        .upgrade-card { background:#fff; border:1.5px solid #e5e7eb; border-radius:20px; padding:40px 36px; max-width:520px; width:100%; text-align:center; box-shadow:0 12px 40px rgba(0,0,0,.08); }
        .upgrade-icon { width:64px; height:64px; border-radius:18px; background:#fef9c3; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:1.8rem; }
        .upgrade-title { font-size:1.5rem; font-weight:800; color:#1B2336; margin:0 0 10px; }
        .upgrade-desc { font-size:.92rem; color:#6b7280; margin:0 0 28px; line-height:1.65; }
        .upgrade-plan-req { display:inline-block; font-size:.8rem; font-weight:700; padding:4px 14px; border-radius:99px; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; margin-bottom:24px; }
        .upgrade-btn { display:block; background:#6B8F71; color:#fff; border-radius:12px; padding:13px 20px; font-weight:700; font-size:.95rem; text-decoration:none; margin-bottom:12px; transition:background .15s; }
        .upgrade-btn:hover { background:#4a6b50; color:#fff; }
        .upgrade-back { font-size:.85rem; color:#6b7280; text-decoration:none; }
        .upgrade-back:hover { color:#1B2336; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="main-dashboard">
    <div class="mc-container">
        <div class="contenedor-dashboard-content">

            <?php require __DIR__ . '/../layout/sidebar.php'; ?>

            <div class="upgrade-main">
                <div class="upgrade-card">
                    <div class="upgrade-icon">⭐</div>
                    <h1 class="upgrade-title">Necesitas un plan superior</h1>

                    <?php
                    require_once __DIR__ . '/../../helpers/PlanHelper.php';
                    $planReq = $_SESSION['upgrade_requerido'] ?? '';
                    unset($_SESSION['upgrade_requerido']);
                    ?>

                    <?php if ($planReq): ?>
                        <div class="upgrade-plan-req">
                            Requiere: <?= htmlspecialchars(PlanHelper::etiqueta($planReq)) ?>
                        </div>
                    <?php endif; ?>

                    <p class="upgrade-desc">
                        Esta funcionalidad no está disponible en tu plan actual
                        (<strong><?= htmlspecialchars(PlanHelper::etiqueta(PlanHelper::planActivo())) ?></strong>).
                        Mejora tu plan para acceder a todas las funcionalidades de MatrixCoders.
                    </p>

                    <a href="<?= BASE_URL ?>/index.php?url=suscripciones" class="upgrade-btn">
                        Ver planes de suscripción
                    </a>
                    <a href="javascript:history.back()" class="upgrade-back">Volver atrás</a>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
