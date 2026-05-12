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
                        <h1 class="perfil-titulo">Ajustes</h1>
                        <p class="perfil-subtitulo">Personaliza tu experiencia en MatrixCoders</p>
                    </div>

                    <!-- ── TARJETA 1: Información de cuenta ── -->
                    <div class="perfil-card">
                        <h3 class="perfil-section-title" style="border-top:none;padding-top:0;margin-bottom:16px;">Información de cuenta</h3>

                        <div class="perfil-datos">
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Correo electrónico</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars($usuario['email']) ?></span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Rol</span>
                                <span class="perfil-campo-valor" style="text-transform:capitalize;"><?= htmlspecialchars($usuario['rol']) ?></span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Miembro desde</span>
                                <span class="perfil-campo-valor"><?= htmlspecialchars(date('d/m/Y', strtotime($usuario['creado_en']))) ?></span>
                            </div>
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Plan activo</span>
                                <span class="perfil-campo-valor">
                                    <?php
                                    $nombresPlan = [
                                        'curso_individual' => 'Curso Individual',
                                        'plan_estudiantes' => 'Plan Estudiantes',
                                        'plan_empresas'    => 'Plan Empresas',
                                    ];
                                    $planActivo = $_SESSION['usuario_plan'] ?? null;
                                    if ($planActivo && isset($nombresPlan[$planActivo])):
                                    ?>
                                        <span class="perfil-plan perfil-plan-activo"><?= htmlspecialchars($nombresPlan[$planActivo]) ?></span>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/index.php?url=suscripciones" class="perfil-plan perfil-plan-libre">Plan gratuito · Ver planes</a>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <div class="perfil-editar-section">
                            <p style="font-size:.85rem;color:var(--mc-muted);margin:0;">
                                ¿Quieres cambiar tu correo electrónico? Contacta con
                                <a href="<?= BASE_URL ?>/index.php?url=chatbot" style="color:var(--mc-green);">soporte a través del Oráculo</a>.
                            </p>
                        </div>
                    </div><!-- /.perfil-card cuenta -->

                    <!-- ── TARJETA 2: Preferencias ── -->
                    <div class="perfil-card" style="margin-top:16px;">
                        <h3 class="perfil-section-title" style="border-top:none;padding-top:0;margin-bottom:16px;">Preferencias</h3>

                        <div class="perfil-datos" style="margin-bottom:4px;">
                            <div class="perfil-campo">
                                <span class="perfil-campo-label">Idioma</span>
                                <span class="perfil-campo-valor"><?= $usuario['idioma'] === 'en' ? 'English' : 'Español' ?></span>
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
                                <span class="perfil-campo-valor"><?= $usuario['privacidad'] === 'privado' ? 'Privado' : 'Público' ?></span>
                            </div>
                        </div>

                        <div class="perfil-editar-section">
                            <h3 class="perfil-section-title">Editar preferencias</h3>

                            <form method="post" action="<?= BASE_URL ?>/index.php?url=guardarAjustes" class="perfil-form" novalidate>

                                <div class="perfil-form-group">
                                    <label for="idioma">Idioma de la plataforma</label>
                                    <select id="idioma" name="idioma">
                                        <option value="es" <?= $usuario['idioma'] === 'es' ? 'selected' : '' ?>>Español</option>
                                        <option value="en" <?= $usuario['idioma'] === 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>

                                <div class="perfil-form-group">
                                    <label class="ajustes-toggle-label">
                                        <span>Recibir notificaciones</span>
                                        <div class="ajustes-toggle-wrap">
                                            <input type="checkbox" id="notificaciones" name="notificaciones"
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

                                <div class="perfil-form-group">
                                    <label>Privacidad del perfil</label>
                                    <div class="ajustes-radio-group">
                                        <label class="ajustes-radio-item">
                                            <input type="radio" name="privacidad" value="publico"
                                                   <?= $usuario['privacidad'] === 'publico' ? 'checked' : '' ?>>
                                            <span>
                                                <strong>Público</strong>
                                                <small>Otros usuarios pueden ver tu perfil</small>
                                            </span>
                                        </label>
                                        <label class="ajustes-radio-item">
                                            <input type="radio" name="privacidad" value="privado"
                                                   <?= $usuario['privacidad'] === 'privado' ? 'checked' : '' ?>>
                                            <span>
                                                <strong>Privado</strong>
                                                <small>Solo tú puedes ver tu perfil</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit">Guardar preferencias</button>
                                </div>
                            </form>
                        </div>
                    </div><!-- /.perfil-card preferencias -->

                    <!-- ── TARJETA 3: Cambiar contraseña ── -->
                    <div class="perfil-card" style="margin-top:16px;">
                        <div class="perfil-editar-section" style="border-top:none;padding-top:0;">
                            <h3 class="perfil-section-title">Cambiar contraseña</h3>

                            <form method="post"
                                  action="<?= BASE_URL ?>/index.php?url=cambiarContrasena"
                                  class="perfil-form"
                                  id="formContrasena"
                                  novalidate>

                                <div class="perfil-form-group">
                                    <label for="contrasena_actual">Contraseña actual <span class="req">*</span></label>
                                    <input type="password" id="contrasena_actual" name="contrasena_actual"
                                           placeholder="Tu contraseña actual" autocomplete="current-password">
                                    <span class="perfil-field-error" id="errActual"></span>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="contrasena_nueva">Nueva contraseña <span class="req">*</span></label>
                                    <input type="password" id="contrasena_nueva" name="contrasena_nueva"
                                           placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                                    <span class="perfil-field-error" id="errNueva"></span>
                                </div>

                                <div class="perfil-form-group">
                                    <label for="contrasena_confirmar">Confirmar nueva contraseña <span class="req">*</span></label>
                                    <input type="password" id="contrasena_confirmar" name="contrasena_confirmar"
                                           placeholder="Repite la nueva contraseña" autocomplete="new-password">
                                    <span class="perfil-field-error" id="errConfirmar"></span>
                                </div>

                                <div class="perfil-form-actions">
                                    <button type="submit" class="btn-panel-submit">Cambiar contraseña</button>
                                </div>
                            </form>
                        </div>
                    </div><!-- /.perfil-card contraseña -->

                    <!-- ── TARJETA 4: Zona de peligro ── -->
                    <div class="perfil-card ajustes-danger-card" style="margin-top:16px;">
                        <div style="border-top:none;padding-top:0;">
                            <div class="ajustes-danger-header">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                                <h3 class="ajustes-danger-title">Zona de peligro</h3>
                            </div>
                            <p class="ajustes-danger-desc">
                                Eliminar tu cuenta es una acción irreversible. Se borrarán tus matrículas, documentos,
                                notificaciones y todos tus datos. Tus certificados emitidos no se recuperarán.
                            </p>

                            <button type="button" class="ajustes-danger-btn" id="btnAbrirEliminar">
                                Eliminar mi cuenta permanentemente
                            </button>
                        </div>
                    </div><!-- /.danger-card -->

                    <!-- ── MODAL: Confirmar eliminación ── -->
                    <div class="ajustes-modal-overlay" id="modalEliminar" style="display:none;">
                        <div class="ajustes-modal">
                            <div class="ajustes-modal-header">
                                <svg width="20" height="20" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                                <h4>¿Seguro que quieres eliminar tu cuenta?</h4>
                            </div>
                            <p style="font-size:.88rem;color:#374151;margin:0 0 20px;">
                                Esta acción <strong>no se puede deshacer</strong>. Para confirmar escribe
                                <strong>eliminar</strong> abajo e introduce tu contraseña.
                            </p>

                            <form method="post" action="<?= BASE_URL ?>/index.php?url=eliminarCuenta" id="formEliminar" novalidate>
                                <div class="perfil-form-group" style="margin-bottom:12px;">
                                    <label style="font-size:.85rem;font-weight:600;">Escribe <em>eliminar</em> para confirmar</label>
                                    <input type="text" name="confirmar_texto" id="confirmarTexto"
                                           placeholder="eliminar" autocomplete="off">
                                    <span class="perfil-field-error" id="errEliminarTexto"></span>
                                </div>
                                <div class="perfil-form-group" style="margin-bottom:20px;">
                                    <label style="font-size:.85rem;font-weight:600;">Tu contraseña</label>
                                    <input type="password" name="confirmar_password" id="confirmarPassword"
                                           placeholder="Introduce tu contraseña" autocomplete="current-password">
                                    <span class="perfil-field-error" id="errEliminarPass"></span>
                                </div>

                                <div style="display:flex;gap:10px;">
                                    <button type="button" class="ajustes-modal-cancel" id="btnCancelarEliminar">Cancelar</button>
                                    <button type="submit" class="ajustes-modal-confirm" id="btnConfirmarEliminar">Eliminar cuenta</button>
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
        // Validación contraseña
        const formContrasena = document.getElementById('formContrasena');
        const inActual  = document.getElementById('contrasena_actual');
        const inNueva   = document.getElementById('contrasena_nueva');
        const inConf    = document.getElementById('contrasena_confirmar');
        const errActual = document.getElementById('errActual');
        const errNueva  = document.getElementById('errNueva');
        const errConf   = document.getElementById('errConfirmar');

        function setErr(inp, el, msg) { if (el) el.textContent = msg; if (inp) inp.classList.toggle('campo-invalido', !!msg); }

        if (formContrasena) {
            formContrasena.addEventListener('submit', function (e) {
                let ok = true;
                if (!inActual.value.trim()) { setErr(inActual, errActual, 'Introduce tu contraseña actual.'); ok = false; } else { setErr(inActual, errActual, ''); }
                if (inNueva.value.length < 6) { setErr(inNueva, errNueva, 'Mínimo 6 caracteres.'); ok = false; } else { setErr(inNueva, errNueva, ''); }
                if (inConf.value !== inNueva.value) { setErr(inConf, errConf, 'Las contraseñas no coinciden.'); ok = false; } else { setErr(inConf, errConf, ''); }
                if (!ok) e.preventDefault();
            });
            inConf.addEventListener('input', () => { if (inConf.value === inNueva.value) setErr(inConf, errConf, ''); });
            inNueva.addEventListener('input', () => {
                if (inNueva.value.length >= 6) setErr(inNueva, errNueva, '');
                if (inConf.value && inConf.value !== inNueva.value) setErr(inConf, errConf, 'Las contraseñas no coinciden.');
                else if (inConf.value === inNueva.value) setErr(inConf, errConf, '');
            });
        }

        // Modal eliminar cuenta
        const modal         = document.getElementById('modalEliminar');
        const btnAbrir      = document.getElementById('btnAbrirEliminar');
        const btnCancelar   = document.getElementById('btnCancelarEliminar');
        const formEliminar  = document.getElementById('formEliminar');
        const inTexto       = document.getElementById('confirmarTexto');
        const inPass        = document.getElementById('confirmarPassword');
        const errTexto      = document.getElementById('errEliminarTexto');
        const errPass       = document.getElementById('errEliminarPass');

        if (btnAbrir) btnAbrir.addEventListener('click', () => { modal.style.display = 'flex'; inTexto.focus(); });
        if (btnCancelar) btnCancelar.addEventListener('click', () => { modal.style.display = 'none'; inTexto.value = ''; inPass.value = ''; setErr(inTexto, errTexto, ''); setErr(inPass, errPass, ''); });
        modal.addEventListener('click', e => { if (e.target === modal) { modal.style.display = 'none'; } });

        if (formEliminar) {
            formEliminar.addEventListener('submit', function (e) {
                let ok = true;
                if (inTexto.value.trim().toLowerCase() !== 'eliminar') { setErr(inTexto, errTexto, 'Escribe exactamente "eliminar".'); ok = false; } else { setErr(inTexto, errTexto, ''); }
                if (!inPass.value.trim()) { setErr(inPass, errPass, 'Introduce tu contraseña.'); ok = false; } else { setErr(inPass, errPass, ''); }
                if (!ok) e.preventDefault();
            });
        }
    })();
    </script>
</body>

</html>
