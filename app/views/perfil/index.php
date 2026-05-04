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

                        <!-- ── Avatar + nombre ── -->
                        <div class="perfil-avatar-section">
                            <div class="perfil-avatar-wrap" id="avatarWrap">
                                <?php if (!empty($usuario['foto'])): ?>
                                    <img
                                        src="<?= BASE_URL ?>/uploads/fotos/<?= htmlspecialchars($usuario['foto']) ?>"
                                        alt="Foto de perfil"
                                        class="perfil-avatar-img"
                                        id="avatarPreview">
                                <?php else: ?>
                                    <div class="perfil-avatar-placeholder" id="avatarPlaceholder">
                                        <?= mb_strtoupper(mb_substr($usuario['nombre'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                    </div>
                                    <img src="" alt="Preview" class="perfil-avatar-img" id="avatarPreview"
                                         style="display:none;">
                                <?php endif; ?>
                            </div>

                            <div class="perfil-avatar-info">
                                <h2 class="perfil-nombre"><?= htmlspecialchars($usuario['nombre']) ?></h2>
                                <span class="perfil-rol"><?= htmlspecialchars($usuario['rol']) ?></span>
                                <span class="perfil-email"><?= htmlspecialchars($usuario['email']) ?></span>

                                <?php
                                // Plan de suscripción
                                $nombresPlan = [
                                    'curso_individual' => 'Curso Individual',
                                    'plan_estudiantes' => 'Plan Estudiantes',
                                    'plan_empresas'    => 'Plan Empresas',
                                ];
                                $planActivo = $_SESSION['usuario_plan'] ?? null;
                                ?>
                                <?php if ($planActivo && isset($nombresPlan[$planActivo])): ?>
                                    <span class="perfil-plan perfil-plan-activo">
                                        <?= htmlspecialchars($nombresPlan[$planActivo]) ?>
                                    </span>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/index.php?url=suscripciones"
                                       class="perfil-plan perfil-plan-libre">
                                        Plan gratuito · Ver planes
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ── Datos personales (solo lectura) ── -->
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
                                <span class="perfil-campo-label">Suscripción activa</span>
                                <span class="perfil-campo-valor">
                                    <?= isset($nombresPlan[$planActivo])
                                        ? htmlspecialchars($nombresPlan[$planActivo])
                                        : 'Sin suscripción' ?>
                                </span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Miembro desde</span>
                                <span class="perfil-campo-valor">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($usuario['creado_en']))) ?>
                                </span>
                            </div>
                            <div class="perfil-campo" style="grid-column: 1 / -1;">
                                <span class="perfil-campo-label">Biografía</span>
                                <span class="perfil-campo-valor">
                                    <?= !empty($usuario['bio'])
                                        ? htmlspecialchars($usuario['bio'])
                                        : '<em style="color:var(--mc-muted)">Sin biografía</em>' ?>
                                </span>
                            </div>
                        </div>

                        <!-- ── Formulario de edición ── -->
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Editar perfil</h3>

                            <form method="post"
                                  action="<?= BASE_URL ?>/index.php?url=guardarPerfil"
                                  enctype="multipart/form-data"
                                  class="perfil-form"
                                  id="formPerfil"
                                  novalidate>

                                <div class="perfil-form-group">
                                    <label for="nombre">
                                        Nombre completo <span class="req">*</span>
                                    </label>
                                    <input type="text"
                                           id="nombre"
                                           name="nombre"
                                           maxlength="80"
                                           required
                                           value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                           placeholder="Tu nombre">
                                    <span class="perfil-field-error" id="errNombre"></span>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="bio">
                                        Biografía
                                        <span class="perfil-hint">(<span id="bioCount">0</span>/300 caracteres)</span>
                                    </label>
                                    <textarea id="bio"
                                              name="bio"
                                              rows="3"
                                              maxlength="300"
                                              placeholder="Cuéntanos algo sobre ti..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
                                    <span class="perfil-field-error" id="errBio"></span>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="foto">
                                        Foto de perfil
                                        <span class="perfil-hint">(JPG, PNG, WebP · máx. 2 MB)</span>
                                    </label>
                                    <input type="file"
                                           id="foto"
                                           name="foto"
                                           accept="image/jpeg,image/png,image/gif,image/webp"
                                           class="perfil-file-input">
                                    <span class="perfil-field-error" id="errFoto"></span>
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit" id="btnGuardarPerfil">
                                        Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div><!-- /.perfil-card -->

                    <div class="perfil-card">
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Cursos matriculados</h3>

                            <?php if (empty($cursosMatriculados)): ?>
                                <div class="sv-empty">
                                    <div>
                                        <p class="sv-empty-title">No estás matriculado en ningún curso todavía.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="perfil-datos">
                                    <?php foreach ($cursosMatriculados as $curso): ?>
                                        <div class="perfil-campo">
                                            <span class="perfil-campo-label">Curso</span>
                                            <span class="perfil-campo-valor">
                                                <a href="<?= BASE_URL ?>/index.php?url=curso&id=<?= (int)$curso['id'] ?>">
                                                    <?= htmlspecialchars($curso['titulo']) ?>
                                                </a>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="perfil-card">
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Cambiar contraseña</h3>

                            <form method="post"
                                  action="<?= BASE_URL ?>/index.php?url=cambiar-password"
                                  class="perfil-form"
                                  novalidate>

                                <div class="perfil-form-group">
                                    <label for="password_actual">Contraseña actual</label>
                                    <input type="password"
                                           id="password_actual"
                                           name="password_actual"
                                           required>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="password_nueva">Nueva contraseña</label>
                                    <input type="password"
                                           id="password_nueva"
                                           name="password_nueva"
                                           minlength="8"
                                           required>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="password_confirmar">Confirmar nueva contraseña</label>
                                    <input type="password"
                                           id="password_confirmar"
                                           name="password_confirmar"
                                           minlength="8"
                                           required>
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit">
                                        Actualizar contraseña
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </section>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        // ── Contador de bio ──────────────────────────────────────────────────
        const bioArea  = document.getElementById('bio');
        const bioCount = document.getElementById('bioCount');

        function actualizarContadorBio() {
            const len = bioArea.value.length;
            bioCount.textContent = len;
            bioCount.style.color = len >= 280 ? 'var(--mc-danger)' : '';
        }

        if (bioArea && bioCount) {
            actualizarContadorBio();
            bioArea.addEventListener('input', actualizarContadorBio);
        }

        // ── Preview de foto antes de subir ───────────────────────────────────
        const fotoInput      = document.getElementById('foto');
        const avatarPreview  = document.getElementById('avatarPreview');
        const avatarPlaceholder = document.getElementById('avatarPlaceholder');
        const errFoto        = document.getElementById('errFoto');
        const MAX_BYTES      = 2 * 1024 * 1024;
        const TIPOS_OK       = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (fotoInput) {
            fotoInput.addEventListener('change', function () {
                const file = this.files[0];
                errFoto.textContent = '';

                if (!file) return;

                if (!TIPOS_OK.includes(file.type)) {
                    errFoto.textContent = 'Formato no permitido. Usa JPG, PNG, GIF o WebP.';
                    this.value = '';
                    return;
                }

                if (file.size > MAX_BYTES) {
                    errFoto.textContent = 'La imagen no puede superar los 2 MB.';
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (avatarPlaceholder) avatarPlaceholder.style.display = 'none';
                    avatarPreview.src     = e.target.result;
                    avatarPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }

        // ── Validación del formulario antes de enviar ────────────────────────
        const formPerfil  = document.getElementById('formPerfil');
        const inputNombre = document.getElementById('nombre');
        const errNombre   = document.getElementById('errNombre');

        if (formPerfil) {
            formPerfil.addEventListener('submit', function (e) {
                let valido = true;

                // Nombre
                const nombre = inputNombre.value.trim();
                if (nombre === '') {
                    errNombre.textContent = 'El nombre no puede estar vacío.';
                    inputNombre.classList.add('campo-invalido');
                    valido = false;
                } else if (nombre.length > 80) {
                    errNombre.textContent = 'Máximo 80 caracteres.';
                    inputNombre.classList.add('campo-invalido');
                    valido = false;
                } else {
                    errNombre.textContent = '';
                    inputNombre.classList.remove('campo-invalido');
                }

                if (!valido) e.preventDefault();
            });

            inputNombre.addEventListener('input', function () {
                if (this.value.trim() !== '') {
                    errNombre.textContent = '';
                    this.classList.remove('campo-invalido');
                }
            });
        }
    })();
    </script>
</body>

</html>
