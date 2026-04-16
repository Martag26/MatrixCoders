<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders - Suscripciones</title>

    <!-- Bootstrap y hojas de estilo propias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/suscripciones.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="plans-page">
        <div class="mc-container">

            <h1 class="plans-title">Elige el mejor plan para ti</h1>

            <p class="plans-subtitle">
                ¿Aún no sabes cuál es la mejor opción? consulta nuestro chatbot Oráculo para obtener ayuda
            </p>

            <?php if (!empty($okMsg)): ?>
                <div class="alert alert-success text-center mb-4"><?= htmlspecialchars($okMsg) ?></div>
            <?php endif; ?>

            <?php if (!empty($planActivo)): ?>
                <?php
                $nombresPlan = [
                    'curso_individual'  => 'Curso Individual',
                    'plan_estudiantes'  => 'Plan estudiantes',
                    'plan_empresas'     => 'Plan empresas',
                ];
                ?>
                <div class="alert alert-info text-center mb-4">
                    Tu plan activo: <strong><?= htmlspecialchars($nombresPlan[$planActivo] ?? $planActivo) ?></strong>
                </div>
            <?php endif; ?>

            <!-- Cuadrícula con las tres tarjetas de planes disponibles -->
            <div class="plans-grid">

                <!-- Plan 1: Curso Individual -->
                <div class="plan-card <?= $planActivo === 'curso_individual' ? 'plan-activo' : '' ?>">
                    <div class="plan-head pink">
                        <h3>Curso Individual</h3>
                        <p>Perfecto para quienes quieren iniciarse en el mundo del desarrollo software</p>
                    </div>
                    <ul class="plan-list">
                        <li><span class="x">✖</span> Acceso ilimitado a todos los cursos</li>
                        <li><span class="ok">✔</span> Descarga de materiales en PDF</li>
                        <li><span class="ok">✔</span> Certificado al finalizar</li>
                        <li><span class="x">✖</span> Clases en vivo con instructores</li>
                        <li><span class="x">✖</span> Soporte prioritario</li>
                        <li><span class="x">✖</span> Licencias múltiples (varios usuarios)</li>
                        <li><span class="x">✖</span> Reportes de progreso</li>
                    </ul>
                    <?php if (!empty($_SESSION['usuario_id'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?url=doSuscripcion" style="margin:14px 18px 18px;">
                            <input type="hidden" name="plan" value="curso_individual">
                            <button type="submit" class="btn btn-plan w-100">
                                <?= $planActivo === 'curso_individual' ? 'Plan actual' : 'Contratar' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-plan" href="<?= BASE_URL ?>/index.php?url=register">Iniciarse en desarrollo software</a>
                    <?php endif; ?>
                </div>

                <!-- Plan 2: Plan Estudiantes -->
                <div class="plan-card <?= $planActivo === 'plan_estudiantes' ? 'plan-activo' : '' ?>">
                    <div class="plan-head pink">
                        <h3>Plan estudiantes</h3>
                        <p>Perfecto para quienes quieren mejorar sus calificaciones</p>
                    </div>
                    <ul class="plan-list">
                        <li><span class="ok">✔</span> Acceso ilimitado a todos los cursos</li>
                        <li><span class="ok">✔</span> Descarga de materiales en PDF</li>
                        <li><span class="ok">✔</span> Certificado al finalizar</li>
                        <li><span class="ok">✔</span> Clases en vivo con instructores</li>
                        <li><span class="x">✖</span> Soporte prioritario</li>
                        <li><span class="x">✖</span> Licencias múltiples (varios usuarios)</li>
                        <li><span class="x">✖</span> Reportes de progreso</li>
                    </ul>
                    <?php if (!empty($_SESSION['usuario_id'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?url=doSuscripcion" style="margin:14px 18px 18px;">
                            <input type="hidden" name="plan" value="plan_estudiantes">
                            <button type="submit" class="btn btn-plan w-100">
                                <?= $planActivo === 'plan_estudiantes' ? 'Plan actual' : 'Contratar' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-plan" href="<?= BASE_URL ?>/index.php?url=register">Obtener plan estudiantes</a>
                    <?php endif; ?>
                </div>

                <!-- Plan 3: Plan Empresas -->
                <div class="plan-card <?= $planActivo === 'plan_empresas' ? 'plan-activo' : '' ?>">
                    <div class="plan-head pink">
                        <h3>Plan empresas</h3>
                        <p>Perfecto para quienes quieren mejorar la formación de sus trabajadores</p>
                    </div>
                    <ul class="plan-list">
                        <li><span class="ok">✔</span> Acceso ilimitado a todos los cursos</li>
                        <li><span class="ok">✔</span> Descarga de materiales en PDF</li>
                        <li><span class="ok">✔</span> Certificado al finalizar</li>
                        <li><span class="ok">✔</span> Clases en vivo con instructores</li>
                        <li><span class="ok">✔</span> Soporte prioritario</li>
                        <li><span class="ok">✔</span> Licencias múltiples (varios usuarios)</li>
                        <li><span class="ok">✔</span> Reportes de progreso</li>
                    </ul>
                    <?php if (!empty($_SESSION['usuario_id'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?url=doSuscripcion" style="margin:14px 18px 18px;">
                            <input type="hidden" name="plan" value="plan_empresas">
                            <button type="submit" class="btn btn-plan w-100">
                                <?= $planActivo === 'plan_empresas' ? 'Plan actual' : 'Contratar' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-plan" href="<?= BASE_URL ?>/index.php?url=register">Contratar plan empresas</a>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
