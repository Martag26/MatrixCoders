<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders — Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>

<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$error = $_SESSION['register_error'] ?? '';
$old   = $_SESSION['register_old']   ?? [];
unset($_SESSION['register_error'], $_SESSION['register_old']);

function reg_val(array $old, string $key): string {
    return htmlspecialchars($old[$key] ?? '');
}
function reg_sel(array $old, string $key, string $val): string {
    return ($old[$key] ?? '') === $val ? 'selected' : '';
}
function reg_radio(array $old, string $key, string $val, string $default = ''): string {
    return (($old[$key] ?? $default) === $val) ? 'checked' : '';
}
?>

<main class="auth-page">
    <div class="mc-container">

        <?php if ($error): ?>
            <div class="reg-error-banner">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:6px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/index.php?url=doRegister" novalidate>
            <div class="auth-grid">

                <!-- ── Columna izquierda: datos de cuenta ── -->
                <section class="auth-card">
                    <h1 class="auth-title">Regístrate</h1>

                    <div class="mb-3">
                        <label class="label-mc">
                            Nombre completo <span class="reg-req">*</span>
                        </label>
                        <input class="form-control input-mc"
                               type="text"
                               name="nombre"
                               value="<?= reg_val($old, 'nombre') ?>"
                               placeholder="Tu nombre completo"
                               autocomplete="name"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="label-mc">
                            Correo electrónico <span class="reg-req">*</span>
                        </label>
                        <input class="form-control input-mc"
                               type="email"
                               name="email"
                               value="<?= reg_val($old, 'email') ?>"
                               placeholder="correo@ejemplo.com"
                               autocomplete="email"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="label-mc">
                            Contraseña <span class="reg-req">*</span>
                        </label>
                        <div class="reg-pass-wrap">
                            <input class="form-control input-mc"
                                   type="password"
                                   name="password"
                                   id="regPass"
                                   placeholder="Mínimo 6 caracteres"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="reg-eye-btn" onclick="togglePass('regPass')" tabindex="-1" aria-label="Mostrar contraseña">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="label-mc">
                            Confirmar contraseña <span class="reg-req">*</span>
                        </label>
                        <div class="reg-pass-wrap">
                            <input class="form-control input-mc"
                                   type="password"
                                   name="password2"
                                   id="regPass2"
                                   placeholder="Repite la contraseña"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="reg-eye-btn" onclick="togglePass('regPass2')" tabindex="-1" aria-label="Mostrar contraseña">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-mc w-100 mt-2" type="submit">
                        Crear cuenta
                    </button>

                    <p class="auth-small mt-3 text-center">
                        ¿Ya tienes cuenta?
                        <a href="<?= BASE_URL ?>/index.php?url=login">Inicia sesión</a>
                    </p>
                </section>

                <!-- ── Columna derecha: perfil académico ── -->
                <section class="auth-card">
                    <h2 class="auth-subtitle">Perfil académico</h2>
                    <p style="font-size:.82rem;color:#6b7280;margin:0 0 18px;line-height:1.5">
                        Información opcional que nos ayuda a personalizar tu experiencia de aprendizaje.
                    </p>

                    <!-- Tipo de persona -->
                    <div class="mb-3">
                        <label class="label-mc">Tipo de registro</label>
                        <div class="reg-tipo-wrap">
                            <label class="reg-tipo-btn <?= ($old['tipo_persona'] ?? 'natural') === 'natural' ? 'active' : '' ?>">
                                <input type="radio" name="tipo_persona" value="natural"
                                       <?= reg_radio($old, 'tipo_persona', 'natural', 'natural') ?>>
                                Persona natural
                            </label>
                            <label class="reg-tipo-btn <?= ($old['tipo_persona'] ?? '') === 'juridica' ? 'active' : '' ?>">
                                <input type="radio" name="tipo_persona" value="juridica"
                                       <?= reg_radio($old, 'tipo_persona', 'juridica', 'natural') ?>>
                                Persona jurídica
                            </label>
                        </div>
                    </div>

                    <!-- Cuadrícula de datos académicos -->
                    <div class="form-grid">

                        <div class="form-col">
                            <label class="label-mc">Áreas de interés</label>
                            <input class="form-control input-mc"
                                   type="text"
                                   name="areas_interes"
                                   value="<?= reg_val($old, 'areas_interes') ?>"
                                   placeholder="ej. Web, IA, Ciberseguridad">

                            <label class="label-mc">Tecnologías conocidas</label>
                            <input class="form-control input-mc"
                                   type="text"
                                   name="tecnologias"
                                   value="<?= reg_val($old, 'tecnologias') ?>"
                                   placeholder="ej. PHP, Python, JavaScript">

                            <label class="label-mc">Perfil de GitHub</label>
                            <input class="form-control input-mc"
                                   type="url"
                                   name="github"
                                   value="<?= reg_val($old, 'github') ?>"
                                   placeholder="https://github.com/usuario">

                            <label class="label-mc">Objetivo principal</label>
                            <input class="form-control input-mc"
                                   type="text"
                                   name="objetivo"
                                   value="<?= reg_val($old, 'objetivo') ?>"
                                   placeholder="ej. Conseguir empleo en tech">
                        </div>

                        <div class="form-col">
                            <label class="label-mc">Nivel de experiencia</label>
                            <select class="form-select input-mc" name="nivel_experiencia">
                                <option value="">Selecciona</option>
                                <option value="principiante" <?= reg_sel($old, 'nivel_experiencia', 'principiante') ?>>Principiante</option>
                                <option value="intermedio"   <?= reg_sel($old, 'nivel_experiencia', 'intermedio')   ?>>Intermedio</option>
                                <option value="avanzado"     <?= reg_sel($old, 'nivel_experiencia', 'avanzado')     ?>>Avanzado</option>
                            </select>

                            <label class="label-mc">Frecuencia de estudio</label>
                            <select class="form-select input-mc" name="frecuencia_estudio">
                                <option value="">Selecciona</option>
                                <option value="1-2_dias" <?= reg_sel($old, 'frecuencia_estudio', '1-2_dias') ?>>1–2 días/semana</option>
                                <option value="3-4_dias" <?= reg_sel($old, 'frecuencia_estudio', '3-4_dias') ?>>3–4 días/semana</option>
                                <option value="diario"   <?= reg_sel($old, 'frecuencia_estudio', 'diario')   ?>>Diario</option>
                            </select>

                            <label class="label-mc">Últimos estudios</label>
                            <select class="form-select input-mc" name="ultimo_estudio">
                                <option value="">Selecciona</option>
                                <option value="ESO"          <?= reg_sel($old, 'ultimo_estudio', 'ESO')          ?>>ESO</option>
                                <option value="Bachillerato" <?= reg_sel($old, 'ultimo_estudio', 'Bachillerato') ?>>Bachillerato</option>
                                <option value="FP"           <?= reg_sel($old, 'ultimo_estudio', 'FP')           ?>>FP</option>
                                <option value="Universidad"  <?= reg_sel($old, 'ultimo_estudio', 'Universidad')  ?>>Universidad</option>
                            </select>

                            <label class="label-mc">Tipo de curso preferido</label>
                            <select class="form-select input-mc" name="tipo_curso_preferido">
                                <option value="">Selecciona</option>
                                <option value="autodidacta"  <?= reg_sel($old, 'tipo_curso_preferido', 'autodidacta')  ?>>Autodidacta</option>
                                <option value="tutor"        <?= reg_sel($old, 'tipo_curso_preferido', 'tutor')        ?>>Guiado con tutor</option>
                                <option value="clases_vivo"  <?= reg_sel($old, 'tipo_curso_preferido', 'clases_vivo')  ?>>Clases en vivo</option>
                                <option value="videos"       <?= reg_sel($old, 'tipo_curso_preferido', 'videos')       ?>>Vídeos explicativos</option>
                            </select>
                        </div>

                    </div>
                </section>

            </div><!-- /.auth-grid -->
        </form>

    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

document.querySelectorAll('.reg-tipo-btn input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.reg-tipo-btn').forEach(l => l.classList.remove('active'));
        radio.closest('.reg-tipo-btn').classList.add('active');
    });
});
</script>
</body>
</html>
