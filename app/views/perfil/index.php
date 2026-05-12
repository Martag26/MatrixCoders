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
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/perfil.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard contenedor-dashboard-content">

                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <section class="contenido-dashboard">

                    <?php if (!empty($flash['message'])): ?>
                        <div class="dashboard-flash dashboard-flash-<?= htmlspecialchars($flash['type']) ?>">
                            <?= htmlspecialchars($flash['message']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="perfil-header">
                        <h1 class="perfil-titulo">Mi perfil</h1>
                        <p class="perfil-subtitulo">Información personal y académica de tu cuenta</p>
                    </div>

                    <!-- ── TARJETA 1: Datos personales + Avatar ── -->
                    <div class="perfil-card">

                        <div class="perfil-avatar-section">
                            <div class="perfil-avatar-wrap" id="avatarWrap">
                                <?php if (!empty($usuario['foto'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/fotos/<?= htmlspecialchars($usuario['foto']) ?>"
                                         alt="Foto de perfil" class="perfil-avatar-img" id="avatarPreview">
                                <?php else: ?>
                                    <div class="perfil-avatar-placeholder" id="avatarPlaceholder">
                                        <?= mb_strtoupper(mb_substr($usuario['nombre'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                    </div>
                                    <img src="" alt="Preview" class="perfil-avatar-img" id="avatarPreview" style="display:none;">
                                <?php endif; ?>
                            </div>

                            <div class="perfil-avatar-info">
                                <h2 class="perfil-nombre"><?= htmlspecialchars($usuario['nombre']) ?></h2>
                                <span class="perfil-rol"><?= htmlspecialchars($usuario['rol']) ?></span>
                                <span class="perfil-email"><?= htmlspecialchars($usuario['email']) ?></span>

                                <?php
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

                        <!-- Datos personales (solo lectura) -->
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
                                <span class="perfil-campo-label">Tipo de registro</span>
                                <span class="perfil-campo-valor">
                                    <?= ($usuario['tipo_persona'] ?? 'natural') === 'juridica' ? 'Persona jurídica' : 'Persona natural' ?>
                                </span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Miembro desde</span>
                                <span class="perfil-campo-valor">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($usuario['creado_en']))) ?>
                                </span>
                            </div>
                            <?php if (!empty($usuario['bio'])): ?>
                            <div class="perfil-campo" style="grid-column: 1 / -1;">
                                <span class="perfil-campo-label">Biografía</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($usuario['bio']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div><!-- /.perfil-card -->

                    <!-- ── TARJETA 2: Perfil académico (solo lectura) ── -->
                    <div class="perfil-card" style="margin-top:16px;">
                        <h3 class="perfil-section-title" style="border-top:none;padding-top:0;margin-bottom:16px;">Perfil académico</h3>

                        <?php
                        $nivelesLabel    = ['principiante'=>'Principiante','intermedio'=>'Intermedio','avanzado'=>'Avanzado'];
                        $frecuenciasLabel = ['1-2_dias'=>'1–2 días/semana','3-4_dias'=>'3–4 días/semana','diario'=>'Diario'];
                        $estudiosLabel   = ['ESO'=>'ESO','Bachillerato'=>'Bachillerato','FP'=>'FP','Universidad'=>'Universidad'];
                        $tiposCursoLabel = ['autodidacta'=>'Autodidacta','tutor'=>'Guiado con tutor','clases_vivo'=>'Clases en vivo','videos'=>'Vídeos explicativos'];

                        $tieneAcademicos = !empty($usuario['areas_interes']) || !empty($usuario['tecnologias'])
                            || !empty($usuario['github']) || !empty($usuario['objetivo'])
                            || !empty($usuario['nivel_experiencia']) || !empty($usuario['frecuencia_estudio'])
                            || !empty($usuario['ultimo_estudio']) || !empty($usuario['tipo_curso_preferido']);
                        ?>

                        <?php if (!$tieneAcademicos): ?>
                            <p style="color:var(--mc-muted);font-size:.88rem;margin:0 0 14px;">
                                Aún no has completado tu perfil académico. Edítalo para personalizar tu experiencia.
                            </p>
                        <?php else: ?>
                        <div class="perfil-datos">
                            <?php if (!empty($usuario['nivel_experiencia'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Nivel de experiencia</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($nivelesLabel[$usuario['nivel_experiencia']] ?? $usuario['nivel_experiencia']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($usuario['frecuencia_estudio'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Frecuencia de estudio</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($frecuenciasLabel[$usuario['frecuencia_estudio']] ?? $usuario['frecuencia_estudio']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($usuario['ultimo_estudio'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Últimos estudios</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($estudiosLabel[$usuario['ultimo_estudio']] ?? $usuario['ultimo_estudio']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($usuario['tipo_curso_preferido'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Tipo de curso preferido</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($tiposCursoLabel[$usuario['tipo_curso_preferido']] ?? $usuario['tipo_curso_preferido']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($usuario['areas_interes'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Áreas de interés</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($usuario['areas_interes']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($usuario['tecnologias'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Tecnologías conocidas</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($usuario['tecnologias']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($usuario['objetivo'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Objetivo principal</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($usuario['objetivo']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($usuario['github'])): ?>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Perfil de GitHub</span>
                                <span class="perfil-campo-valor">
                                    <a href="<?= htmlspecialchars($usuario['github']) ?>" target="_blank" rel="noopener noreferrer">
                                        <?= htmlspecialchars($usuario['github']) ?>
                                    </a>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div><!-- /.perfil-card académico -->

                    <!-- ── TARJETA 3: Editar perfil ── -->
                    <div class="perfil-card" style="margin-top:16px;">
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Editar perfil</h3>

                            <form method="post"
                                  action="<?= BASE_URL ?>/index.php?url=guardarPerfil"
                                  enctype="multipart/form-data"
                                  id="formPerfil"
                                  novalidate>

                                <!-- Datos básicos -->
                                <div class="perfil-form-2col">
                                    <div class="perfil-form-group">
                                        <label for="nombre">Nombre completo <span class="req">*</span></label>
                                        <input type="text" id="nombre" name="nombre" maxlength="80" required
                                               value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                               placeholder="Tu nombre">
                                        <span class="perfil-field-error" id="errNombre"></span>
                                    </div>
                                    <div class="perfil-form-group">
                                        <label for="foto">
                                            Foto de perfil
                                            <span class="perfil-hint">(JPG, PNG, WebP · máx. 2 MB)</span>
                                        </label>
                                        <input type="file" id="foto" name="foto"
                                               accept="image/jpeg,image/png,image/gif,image/webp"
                                               class="perfil-file-input">
                                        <span class="perfil-field-error" id="errFoto"></span>
                                    </div>
                                </div>

                                <div class="perfil-form-group" style="max-width:100%;">
                                    <label for="bio">
                                        Biografía
                                        <span class="perfil-hint">(<span id="bioCount">0</span>/300 caracteres)</span>
                                    </label>
                                    <textarea id="bio" name="bio" rows="3" maxlength="300"
                                              placeholder="Cuéntanos algo sobre ti..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
                                </div>

                                <!-- Separador académico -->
                                <div class="perfil-academic-sep">
                                    <span>Datos académicos</span>
                                </div>

                                <!-- Fila 1 -->
                                <div class="perfil-form-2col">
                                    <div class="perfil-form-group">
                                        <label for="areas_interes">Áreas de interés</label>
                                        <input type="text" id="areas_interes" name="areas_interes" maxlength="255"
                                               value="<?= htmlspecialchars($usuario['areas_interes'] ?? '') ?>"
                                               placeholder="ej. Web, IA, Ciberseguridad">
                                    </div>
                                    <div class="perfil-form-group">
                                        <label for="tecnologias">Tecnologías conocidas</label>
                                        <input type="text" id="tecnologias" name="tecnologias" maxlength="255"
                                               value="<?= htmlspecialchars($usuario['tecnologias'] ?? '') ?>"
                                               placeholder="ej. PHP, Python, JavaScript">
                                    </div>
                                </div>

                                <!-- Fila 2 -->
                                <div class="perfil-form-2col">
                                    <div class="perfil-form-group">
                                        <label for="github">Perfil de GitHub</label>
                                        <input type="url" id="github" name="github" maxlength="255"
                                               value="<?= htmlspecialchars($usuario['github'] ?? '') ?>"
                                               placeholder="https://github.com/usuario">
                                    </div>
                                    <div class="perfil-form-group">
                                        <label for="objetivo">Objetivo principal</label>
                                        <input type="text" id="objetivo" name="objetivo" maxlength="255"
                                               value="<?= htmlspecialchars($usuario['objetivo'] ?? '') ?>"
                                               placeholder="ej. Conseguir empleo en tech">
                                    </div>
                                </div>

                                <!-- Fila 3: selects -->
                                <div class="perfil-form-3col">
                                    <div class="perfil-form-group">
                                        <label for="nivel_experiencia">Nivel de experiencia</label>
                                        <select id="nivel_experiencia" name="nivel_experiencia">
                                            <option value="">Selecciona</option>
                                            <option value="principiante" <?= ($usuario['nivel_experiencia'] ?? '') === 'principiante' ? 'selected' : '' ?>>Principiante</option>
                                            <option value="intermedio"   <?= ($usuario['nivel_experiencia'] ?? '') === 'intermedio'   ? 'selected' : '' ?>>Intermedio</option>
                                            <option value="avanzado"     <?= ($usuario['nivel_experiencia'] ?? '') === 'avanzado'     ? 'selected' : '' ?>>Avanzado</option>
                                        </select>
                                    </div>
                                    <div class="perfil-form-group">
                                        <label for="frecuencia_estudio">Frecuencia de estudio</label>
                                        <select id="frecuencia_estudio" name="frecuencia_estudio">
                                            <option value="">Selecciona</option>
                                            <option value="1-2_dias" <?= ($usuario['frecuencia_estudio'] ?? '') === '1-2_dias' ? 'selected' : '' ?>>1–2 días/semana</option>
                                            <option value="3-4_dias" <?= ($usuario['frecuencia_estudio'] ?? '') === '3-4_dias' ? 'selected' : '' ?>>3–4 días/semana</option>
                                            <option value="diario"   <?= ($usuario['frecuencia_estudio'] ?? '') === 'diario'   ? 'selected' : '' ?>>Diario</option>
                                        </select>
                                    </div>
                                    <div class="perfil-form-group">
                                        <label for="ultimo_estudio">Últimos estudios</label>
                                        <select id="ultimo_estudio" name="ultimo_estudio">
                                            <option value="">Selecciona</option>
                                            <option value="ESO"          <?= ($usuario['ultimo_estudio'] ?? '') === 'ESO'          ? 'selected' : '' ?>>ESO</option>
                                            <option value="Bachillerato" <?= ($usuario['ultimo_estudio'] ?? '') === 'Bachillerato' ? 'selected' : '' ?>>Bachillerato</option>
                                            <option value="FP"           <?= ($usuario['ultimo_estudio'] ?? '') === 'FP'           ? 'selected' : '' ?>>FP</option>
                                            <option value="Universidad"  <?= ($usuario['ultimo_estudio'] ?? '') === 'Universidad'  ? 'selected' : '' ?>>Universidad</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Fila 4: tipo de curso -->
                                <div class="perfil-form-group" style="max-width:340px;">
                                    <label for="tipo_curso_preferido">Tipo de curso preferido</label>
                                    <select id="tipo_curso_preferido" name="tipo_curso_preferido">
                                        <option value="">Selecciona</option>
                                        <option value="autodidacta"  <?= ($usuario['tipo_curso_preferido'] ?? '') === 'autodidacta'  ? 'selected' : '' ?>>Autodidacta</option>
                                        <option value="tutor"        <?= ($usuario['tipo_curso_preferido'] ?? '') === 'tutor'        ? 'selected' : '' ?>>Guiado con tutor</option>
                                        <option value="clases_vivo"  <?= ($usuario['tipo_curso_preferido'] ?? '') === 'clases_vivo'  ? 'selected' : '' ?>>Clases en vivo</option>
                                        <option value="videos"       <?= ($usuario['tipo_curso_preferido'] ?? '') === 'videos'       ? 'selected' : '' ?>>Vídeos explicativos</option>
                                    </select>
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit" id="btnGuardarPerfil">
                                        Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div><!-- /.perfil-card editar -->

                    <!-- ── TARJETA 4: Cursos matriculados ── -->
                    <div class="perfil-card" style="margin-top:16px;">
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Cursos matriculados</h3>

                            <?php if (empty($cursosMatriculados)): ?>
                                <p style="color:var(--mc-muted);font-size:.88rem;margin:0;">
                                    No estás matriculado en ningún curso todavía.
                                    <a href="<?= BASE_URL ?>/index.php" style="color:var(--mc-green);">Explorar cursos</a>
                                </p>
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

                    <!-- ── TARJETA 5: Cambiar contraseña ── -->
                    <div class="perfil-card" style="margin-top:16px;">
                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Cambiar contraseña</h3>

                            <form method="post"
                                  action="<?= BASE_URL ?>/index.php?url=cambiar-password"
                                  class="perfil-form"
                                  id="formPassword"
                                  novalidate>

                                <div class="perfil-form-group">
                                    <label for="password_actual">Contraseña actual <span class="req">*</span></label>
                                    <input type="password" id="password_actual" name="password_actual"
                                           placeholder="Tu contraseña actual" autocomplete="current-password">
                                    <span class="perfil-field-error" id="errPassActual"></span>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="password_nueva">Nueva contraseña <span class="req">*</span></label>
                                    <input type="password" id="password_nueva" name="password_nueva"
                                           placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                                    <span class="perfil-field-error" id="errPassNueva"></span>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="password_confirmar">Confirmar nueva contraseña <span class="req">*</span></label>
                                    <input type="password" id="password_confirmar" name="password_confirmar"
                                           placeholder="Repite la nueva contraseña" autocomplete="new-password">
                                    <span class="perfil-field-error" id="errPassConfirmar"></span>
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit">Cambiar contraseña</button>
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
        // Bio counter
        const bioArea  = document.getElementById('bio');
        const bioCount = document.getElementById('bioCount');
        if (bioArea && bioCount) {
            const upd = () => { const n = bioArea.value.length; bioCount.textContent = n; bioCount.style.color = n >= 280 ? 'var(--mc-danger)' : ''; };
            upd(); bioArea.addEventListener('input', upd);
        }

        // Avatar preview
        const fotoInput      = document.getElementById('foto');
        const avatarPreview  = document.getElementById('avatarPreview');
        const avatarPlaceholder = document.getElementById('avatarPlaceholder');
        const errFoto        = document.getElementById('errFoto');
        if (fotoInput) {
            fotoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (errFoto) errFoto.textContent = '';
                if (!file) return;
                if (!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)) {
                    if (errFoto) errFoto.textContent = 'Formato no permitido. Usa JPG, PNG, GIF o WebP.';
                    this.value = ''; return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    if (errFoto) errFoto.textContent = 'La imagen no puede superar los 2 MB.';
                    this.value = ''; return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    if (avatarPlaceholder) avatarPlaceholder.style.display = 'none';
                    avatarPreview.src = e.target.result;
                    avatarPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }

        // Form perfil validation
        const formPerfil  = document.getElementById('formPerfil');
        const inputNombre = document.getElementById('nombre');
        const errNombre   = document.getElementById('errNombre');
        if (formPerfil) {
            formPerfil.addEventListener('submit', function (e) {
                const nom = inputNombre.value.trim();
                if (!nom) { errNombre.textContent = 'El nombre no puede estar vacío.'; inputNombre.classList.add('campo-invalido'); e.preventDefault(); }
                else if (nom.length > 80) { errNombre.textContent = 'Máximo 80 caracteres.'; inputNombre.classList.add('campo-invalido'); e.preventDefault(); }
                else { errNombre.textContent = ''; inputNombre.classList.remove('campo-invalido'); }
            });
            inputNombre.addEventListener('input', function () {
                if (this.value.trim()) { errNombre.textContent = ''; this.classList.remove('campo-invalido'); }
            });
        }

        // Form password validation
        const formPwd   = document.getElementById('formPassword');
        const pActual   = document.getElementById('password_actual');
        const pNueva    = document.getElementById('password_nueva');
        const pConf     = document.getElementById('password_confirmar');
        const eActual   = document.getElementById('errPassActual');
        const eNueva    = document.getElementById('errPassNueva');
        const eConf     = document.getElementById('errPassConfirmar');

        function setErr(inp, el, msg) { if (el) el.textContent = msg; if (inp) inp.classList.toggle('campo-invalido', !!msg); }

        if (formPwd) {
            formPwd.addEventListener('submit', function (e) {
                let ok = true;
                if (!pActual.value.trim()) { setErr(pActual, eActual, 'Introduce tu contraseña actual.'); ok = false; } else { setErr(pActual, eActual, ''); }
                if (pNueva.value.length < 6) { setErr(pNueva, eNueva, 'Mínimo 6 caracteres.'); ok = false; } else { setErr(pNueva, eNueva, ''); }
                if (pConf.value !== pNueva.value) { setErr(pConf, eConf, 'Las contraseñas no coinciden.'); ok = false; } else { setErr(pConf, eConf, ''); }
                if (!ok) e.preventDefault();
            });
            pConf.addEventListener('input', () => { if (pConf.value === pNueva.value) setErr(pConf, eConf, ''); });
            pNueva.addEventListener('input', () => { if (pNueva.value.length >= 6) setErr(pNueva, eNueva, ''); if (pConf.value && pConf.value !== pNueva.value) setErr(pConf, eConf, 'Las contraseñas no coinciden.'); else if (pConf.value === pNueva.value) setErr(pConf, eConf, ''); });
        }
    })();
    </script>
</body>

</html>
