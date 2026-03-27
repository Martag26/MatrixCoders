<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders - Registro</title>

    <!-- Bootstrap y hojas de estilo propias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="auth-page">
        <div class="mc-container">
            <div class="auth-grid">

                <!-- Columna izquierda: formulario de creación de cuenta -->
                <section class="auth-card">
                    <h1 class="auth-title">Regístrate</h1>

                    <?php
                    // Iniciar sesión si no hay ninguna activa
                    if (session_status() === PHP_SESSION_NONE) session_start();

                    // Recoger y limpiar el mensaje de error de registro (si existe) y eliminarlo de la sesión
                    $error = $_SESSION['register_error'] ?? '';
                    unset($_SESSION['register_error']);
                    ?>

                    <?php if ($error): ?>
                        <!-- Mensaje de error de validación del formulario -->
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <!-- Formulario de registro: envía por POST al controlador doRegister -->
                    <form method="POST" action="<?= BASE_URL ?>/index.php?url=doRegister" class="auth-form">
                        <input class="form-control input-mc" type="text"     name="nombre"    placeholder="Nombre">
                        <input class="form-control input-mc" type="email"    name="email"     placeholder="Correo electrónico">
                        <input class="form-control input-mc" type="password" name="password"  placeholder="Contraseña">
                        <input class="form-control input-mc" type="password" name="password2" placeholder="Confirmar contraseña">

                        <!-- Botón de envío del formulario -->
                        <button class="btn btn-mc w-100 mt-2" type="submit">Registrarse</button>

                        <!-- Enlace a la página de login para usuarios que ya tienen cuenta -->
                        <p class="auth-small mt-3">
                            ¿Ya tienes una cuenta?
                            <a href="<?= BASE_URL ?>/index.php?url=login">Inicia sesión</a>
                        </p>
                    </form>
                </section>

                <!-- Columna derecha: datos académicos del usuario (informativa, no guarda en BD por ahora) -->
                <section class="auth-card">
                    <h2 class="auth-subtitle">Datos académicos</h2>

                    <!-- Selector de tipo de persona: natural o jurídica -->
                    <div class="tipo-persona">
                        <button type="button" class="btn btn-persona active">Persona natural</button>
                        <button type="button" class="btn btn-persona">Persona jurídica</button>
                    </div>

                    <!-- Indicadores de si es cliente nuevo o ya tiene cuenta -->
                    <div class="check-row">
                        <label><input type="checkbox"> Cliente Nuevo</label>
                        <label><input type="checkbox" checked> Ya poseo cuenta</label>
                    </div>

                    <!-- Cuadrícula de campos académicos dividida en dos columnas -->
                    <div class="form-grid">

                        <!-- Columna izquierda: intereses y perfil técnico -->
                        <div class="form-col">
                            <label class="label-mc">Áreas de interés</label>
                            <input class="form-control input-mc" type="text">

                            <label class="label-mc">Tecnologías conocidas</label>
                            <input class="form-control input-mc" type="text">

                            <label class="label-mc">Link a GitHub</label>
                            <input class="form-control input-mc" type="text">

                            <label class="label-mc">Objetivo principal</label>
                            <input class="form-control input-mc" type="text">
                        </div>

                        <!-- Columna derecha: nivel, frecuencia y preferencias de estudio -->
                        <div class="form-col">
                            <label class="label-mc">Nivel de experiencia</label>
                            <select class="form-select input-mc">
                                <option value="">Selecciona</option>
                                <option>Principiante</option>
                                <option>Intermedio</option>
                                <option>Avanzado</option>
                            </select>

                            <label class="label-mc">Frecuencia de estudio</label>
                            <select class="form-select input-mc">
                                <option value="">Selecciona</option>
                                <option>1-2 días/semana</option>
                                <option>3-4 días/semana</option>
                                <option>Diario</option>
                            </select>

                            <label class="label-mc">Últimos estudios</label>
                            <select class="form-select input-mc">
                                <option value="">Selecciona</option>
                                <option>ESO</option>
                                <option>Bachillerato</option>
                                <option>FP</option>
                                <option>Universidad</option>
                            </select>

                            <label class="label-mc">Tipo de curso preferido</label>
                            <select class="form-select input-mc">
                                <option>Autodidacta</option>
                                <option>Guiado con tutor</option>
                                <option>Clases en vivo</option>
                                <option>Vídeos explicativos</option>
                            </select>
                        </div>
                    </div>

                    <!-- Aviso: esta sección es solo informativa y no persiste datos en BD -->
                    <div class="auth-note">
                        * Esta parte es informativa por ahora. No hace falta que guarde en BD.
                    </div>
                </section>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
