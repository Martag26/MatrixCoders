<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders - Registro</title>

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

                <!-- Columna izquierda -->
                <section class="auth-card">
                    <h1 class="auth-title">Regístrate</h1>

                    <?php
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $error = $_SESSION['register_error'] ?? '';
                    unset($_SESSION['register_error']);
                    ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/index.php?url=doRegister" class="auth-form">
                        <input class="form-control input-mc" type="text" name="nombre" placeholder="Nombre">
                        <input class="form-control input-mc" type="email" name="email" placeholder="Correo electrónico">
                        <input class="form-control input-mc" type="password" name="password" placeholder="Contraseña">
                        <input class="form-control input-mc" type="password" name="password2" placeholder="Confirmar contraseña">

                        <button class="btn btn-mc w-100 mt-2" type="submit">Registrarse</button>

                        <p class="auth-small mt-3">
                            ¿Ya tienes una cuenta?
                            <a href="<?= BASE_URL ?>/index.php?url=login">Inicia sesión</a>
                        </p>
                    </form>
                </section>

                <!-- Columna derecha: Datos académicos -->
                <section class="auth-card">
                    <h2 class="auth-subtitle">Datos académicos</h2>

                    <div class="tipo-persona">
                        <button type="button" class="btn btn-persona active">Persona natural</button>
                        <button type="button" class="btn btn-persona">Persona jurídica</button>
                    </div>

                    <div class="check-row">
                        <label><input type="checkbox"> Cliente Nuevo</label>
                        <label><input type="checkbox" checked> Ya poseo cuenta</label>
                    </div>

                    <div class="form-grid">
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