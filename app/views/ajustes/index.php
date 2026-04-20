<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle ?? 'Ajustes') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/perfil.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard contenedor-dashboard-content">

                <!-- ── SIDEBAR ── -->
                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <!-- ── CONTENIDO PRINCIPAL ── -->
                <section class="contenido-dashboard">

                    <!-- Flash message -->
                    <?php if (!empty($flash['message'])): ?>
                        <div class="dashboard-flash dashboard-flash-<?= htmlspecialchars($flash['type']) ?>">
                            <?= htmlspecialchars($flash['message']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="perfil-header">
                        <h1 class="perfil-titulo">Ajustes</h1>
                        <p class="perfil-subtitulo">Personaliza tu experiencia en MatrixCoders</p>
                    </div>

                    <!-- Resumen de preferencias actuales -->
                    <div class="perfil-card">
                        <div class="perfil-datos">
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Idioma</span>
                                <span class="perfil-campo-valor">
                                    <?= $usuario['idioma'] === 'en' ? 'English' : 'Español' ?>
                                </span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Notificaciones</span>
                                <span class="perfil-campo-valor">
                                    <?php if ($usuario['notificaciones']): ?>
                                        <span class="ajustes-badge ajustes-badge-on">Activadas</span>
                                    <?php else: ?>
                                        <span class="ajustes-badge ajustes-badge-off">Desactivadas</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Privacidad del perfil</span>
                                <span class="perfil-campo-valor">
                                    <?= $usuario['privacidad'] === 'privado' ? 'Privado' : 'Público' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Formulario de edición de ajustes -->
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Editar preferencias</h3>

                            <form method="post"
                                  action="<?= BASE_URL ?>/index.php?url=guardarAjustes"
                                  class="perfil-form"
                                  novalidate>

                                <!-- Idioma -->
                                <div class="perfil-form-group">
                                    <label for="idioma">Idioma de la plataforma</label>
                                    <select id="idioma" name="idioma">
                                        <option value="es" <?= $usuario['idioma'] === 'es' ? 'selected' : '' ?>>
                                            Español
                                        </option>
                                        <option value="en" <?= $usuario['idioma'] === 'en' ? 'selected' : '' ?>>
                                            English
                                        </option>
                                    </select>
                                </div>

                                <!-- Notificaciones -->
                                <div class="perfil-form-group">
                                    <label class="ajustes-toggle-label">
                                        <span>Recibir notificaciones</span>
                                        <div class="ajustes-toggle-wrap">
                                            <input type="checkbox"
                                                   id="notificaciones"
                                                   name="notificaciones"
                                                   class="ajustes-toggle-input"
                                                   <?= $usuario['notificaciones'] ? 'checked' : '' ?>>
                                            <label for="notificaciones" class="ajustes-toggle-track">
                                                <span class="ajustes-toggle-thumb"></span>
                                            </label>
                                        </div>
                                    </label>
                                    <p class="perfil-hint" style="margin-top:.25rem;">
                                        Recibe avisos sobre actividad en tus cursos y documentos.
                                    </p>
                                </div>

                                <!-- Privacidad -->
                                <div class="perfil-form-group">
                                    <label>Privacidad del perfil</label>
                                    <div class="ajustes-radio-group">
                                        <label class="ajustes-radio-item">
                                            <input type="radio"
                                                   name="privacidad"
                                                   value="publico"
                                                   <?= $usuario['privacidad'] === 'publico' ? 'checked' : '' ?>>
                                            <span>
                                                <strong>Público</strong>
                                                <small>Otros usuarios pueden ver tu perfil</small>
                                            </span>
                                        </label>
                                        <label class="ajustes-radio-item">
                                            <input type="radio"
                                                   name="privacidad"
                                                   value="privado"
                                                   <?= $usuario['privacidad'] === 'privado' ? 'checked' : '' ?>>
                                            <span>
                                                <strong>Privado</strong>
                                                <small>Solo tú puedes ver tu perfil</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit">Guardar ajustes</button>
                                </div>
                            </form>
                        </div>

                    </div><!-- /.perfil-card -->

                </section>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
