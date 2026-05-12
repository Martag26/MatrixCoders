<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes · MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/suscripciones.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="plans-page">
        <div class="mc-container">

            <div class="plans-hero">
                <p class="plans-eyebrow">Planes y precios</p>
                <h1 class="plans-title">Elige el plan que impulsa tu carrera</h1>
                <p class="plans-subtitle">
                    Accede a cursos, certificados y herramientas de IA adaptadas a tu ritmo de aprendizaje.
                </p>
            </div>

            <?php if (!empty($okMsg)): ?>
                <div class="plans-flash plans-flash-ok"><?= htmlspecialchars($okMsg) ?></div>
            <?php endif; ?>

            <?php if (!empty($planActivo)): ?>
                <?php
                $nombresPlan = [
                    'curso_individual'  => 'Curso Individual',
                    'plan_estudiantes'  => 'Plan Estudiantes',
                    'plan_empresas'     => 'Plan Empresas',
                ];
                ?>
                <div class="plans-flash plans-flash-info">
                    Tu plan activo: <strong><?= htmlspecialchars($nombresPlan[$planActivo] ?? $planActivo) ?></strong>
                </div>
            <?php endif; ?>

            <div class="plans-grid">

                <!-- ── Plan Gratuito ── -->
                <div class="plan-card <?= empty($planActivo) ? 'plan-activo' : '' ?>">
                    <div class="plan-head plan-head-free">
                        <p class="plan-tag">Para empezar</p>
                        <h3 class="plan-name">Gratuito</h3>
                        <div class="plan-price">
                            <span class="plan-price-amount">0 €</span>
                            <span class="plan-price-period">/ mes</span>
                        </div>
                        <p class="plan-desc">Explora la plataforma sin compromiso</p>
                    </div>
                    <ul class="plan-list">
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Acceso al catálogo de cursos
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Compra de cursos individuales
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Certificados verificables
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Resumen IA de lecciones
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Nube personal de documentos
                        </li>
                    </ul>
                    <div class="plan-action">
                        <?php if (empty($_SESSION['usuario_id'])): ?>
                            <a href="<?= BASE_URL ?>/index.php?url=register" class="btn-plan btn-plan-outline">Crear cuenta gratis</a>
                        <?php elseif (empty($planActivo)): ?>
                            <span class="btn-plan btn-plan-current">Plan actual</span>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/index.php?url=register" class="btn-plan btn-plan-outline" style="opacity:.4;pointer-events:none;">Plan gratuito</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Plan Curso Individual ── -->
                <div class="plan-card <?= $planActivo === 'curso_individual' ? 'plan-activo' : '' ?>">
                    <div class="plan-head plan-head-individual">
                        <p class="plan-tag">Aprendizaje dirigido</p>
                        <h3 class="plan-name">Curso Individual</h3>
                        <div class="plan-price">
                            <span class="plan-price-amount" style="font-size:1.3rem;">Precio del curso</span>
                        </div>
                        <p class="plan-desc">Compra cursos sueltos al precio de cada uno, sin cuota mensual</p>
                    </div>
                    <ul class="plan-list">
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Compra cursos uno a uno
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Descarga de materiales PDF
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Certificado al finalizar
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Acceso ilimitado a todos los cursos
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Soporte prioritario
                        </li>
                    </ul>
                    <div class="plan-action">
                        <?php if (!empty($_SESSION['usuario_id'])): ?>
                            <?php if ($planActivo === 'curso_individual'): ?>
                                <span class="btn-plan btn-plan-current">Plan actual</span>
                            <?php else: ?>
                                <form method="POST" action="<?= BASE_URL ?>/index.php?url=pagarSuscripcion">
                                    <input type="hidden" name="plan" value="curso_individual">
                                    <button type="submit" class="btn-plan btn-plan-primary w-100">Contratar</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/index.php?url=register" class="btn-plan btn-plan-primary">Empezar</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Plan Estudiantes (RECOMENDADO) ── -->
                <div class="plan-card plan-card-featured <?= $planActivo === 'plan_estudiantes' ? 'plan-activo' : '' ?>">
                    <div class="plan-featured-badge">Más popular</div>
                    <div class="plan-head plan-head-estudiantes">
                        <p class="plan-tag">Para estudiantes</p>
                        <h3 class="plan-name">Plan Estudiantes</h3>
                        <div class="plan-price">
                            <span class="plan-price-amount">19,99 €</span>
                            <span class="plan-price-period">/ mes</span>
                        </div>
                        <p class="plan-desc">Aprende sin límites con acceso total a la plataforma</p>
                    </div>
                    <ul class="plan-list">
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Acceso ilimitado a todos los cursos
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Resumen IA de lecciones (Gemini)
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Certificados verificables
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Nube personal de documentos
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Licencias múltiples (equipos)
                        </li>
                        <li class="plan-item plan-item-no">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Reportes de progreso del equipo
                        </li>
                    </ul>
                    <div class="plan-action">
                        <?php if (!empty($_SESSION['usuario_id'])): ?>
                            <?php if ($planActivo === 'plan_estudiantes'): ?>
                                <span class="btn-plan btn-plan-current">Plan actual</span>
                            <?php else: ?>
                                <form method="POST" action="<?= BASE_URL ?>/index.php?url=pagarSuscripcion">
                                    <input type="hidden" name="plan" value="plan_estudiantes">
                                    <button type="submit" class="btn-plan btn-plan-featured w-100">Contratar ahora</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/index.php?url=register" class="btn-plan btn-plan-featured">Empezar</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Plan Empresas ── -->
                <div class="plan-card <?= $planActivo === 'plan_empresas' ? 'plan-activo' : '' ?>">
                    <div class="plan-head plan-head-empresas">
                        <p class="plan-tag">Para equipos</p>
                        <h3 class="plan-name">Plan Empresas</h3>
                        <div class="plan-price">
                            <span class="plan-price-amount">49,99 €</span>
                            <span class="plan-price-period">/ mes</span>
                        </div>
                        <p class="plan-desc">Formación corporativa con gestión centralizada</p>
                    </div>
                    <ul class="plan-list">
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Todo lo del Plan Estudiantes
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Soporte prioritario
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Licencias múltiples (varios usuarios)
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Reportes de progreso del equipo
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Dashboard de métricas avanzado
                        </li>
                        <li class="plan-item plan-item-ok">
                            <svg class="pi-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Gestor de campañas y descuentos
                        </li>
                    </ul>
                    <div class="plan-action">
                        <?php if (!empty($_SESSION['usuario_id'])): ?>
                            <?php if ($planActivo === 'plan_empresas'): ?>
                                <span class="btn-plan btn-plan-current">Plan actual</span>
                            <?php else: ?>
                                <form method="POST" action="<?= BASE_URL ?>/index.php?url=pagarSuscripcion">
                                    <input type="hidden" name="plan" value="plan_empresas">
                                    <button type="submit" class="btn-plan btn-plan-primary w-100">Contratar</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/index.php?url=register" class="btn-plan btn-plan-primary">Empezar</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.plans-grid -->

            <!-- FAQ strip -->
            <div class="plans-faq">
                <div class="plans-faq-item">
                    <strong>¿Puedo cambiar de plan en cualquier momento?</strong>
                    <p>Sí. Puedes actualizar o cancelar tu plan desde la página de suscripciones.</p>
                </div>
                <div class="plans-faq-item">
                    <strong>¿Los certificados caducan?</strong>
                    <p>No. Los certificados emitidos son permanentes con código único verificable.</p>
                </div>
                <div class="plans-faq-item">
                    <strong>¿Necesito tarjeta para el plan gratuito?</strong>
                    <p>No. Crea tu cuenta gratis sin introducir ningún dato de pago.</p>
                </div>
            </div>

        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
