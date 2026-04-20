<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle ?? 'Mi perfil') ?></title>
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
                        <h1 class="perfil-titulo">Mi perfil</h1>
                        <p class="perfil-subtitulo">Información personal de tu cuenta</p>
                    </div>

                    <div class="perfil-card">

                        <!-- Sección avatar + nombre -->
                        <div class="perfil-avatar-section">
                            <?php if (!empty($usuario['foto'])): ?>
                                <img
                                    src="<?= BASE_URL ?>/uploads/fotos/<?= htmlspecialchars($usuario['foto']) ?>"
                                    alt="Foto de perfil"
                                    class="perfil-avatar-img">
                            <?php else: ?>
                                <div class="perfil-avatar-placeholder">
                                    <?= mb_strtoupper(mb_substr($usuario['nombre'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                </div>
                            <?php endif; ?>

                            <div class="perfil-avatar-info">
                                <h2 class="perfil-nombre"><?= htmlspecialchars($usuario['nombre']) ?></h2>
                                <span class="perfil-rol"><?= htmlspecialchars($usuario['rol']) ?></span>
                                <span class="perfil-email"><?= htmlspecialchars($usuario['email']) ?></span>
                            </div>
                        </div>

                        <!-- Datos personales -->
                        <div class="perfil-datos">
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Nombre completo</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($usuario['nombre']) ?></span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Correo electrónico</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($usuario['email']) ?></span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Biografía</span>
                                <span class="perfil-campo-valor">
                                    <?= !empty($usuario['bio'])
                                        ? htmlspecialchars($usuario['bio'])
                                        : '<em class="text-muted">Sin biografía</em>' ?>
                                </span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Miembro desde</span>
                                <span class="perfil-campo-valor">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($usuario['creado_en']))) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Formulario de edición -->
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Editar perfil</h3>

                            <form method="post"
                                  action="<?= BASE_URL ?>/index.php?url=guardarPerfil"
                                  enctype="multipart/form-data"
                                  class="perfil-form"
                                  novalidate>

                                <div class="perfil-form-group">
                                    <label for="nombre">Nombre completo <span class="req">*</span></label>
                                    <input type="text"
                                           id="nombre"
                                           name="nombre"
                                           maxlength="80"
                                           required
                                           value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                           placeholder="Tu nombre">
                                </div>

                                <div class="perfil-form-group">
                                    <label for="bio">Biografía <span class="perfil-hint">(máx. 300 caracteres)</span></label>
                                    <textarea id="bio"
                                              name="bio"
                                              rows="3"
                                              maxlength="300"
                                              placeholder="Cuéntanos algo sobre ti..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="foto">Foto de perfil <span class="perfil-hint">(JPG, PNG, WebP · máx. 2 MB)</span></label>
                                    <input type="file"
                                           id="foto"
                                           name="foto"
                                           accept="image/jpeg,image/png,image/gif,image/webp"
                                           class="perfil-file-input">
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit">Guardar cambios</button>
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
