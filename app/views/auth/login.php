<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders - Iniciar sesión</title>

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
            <div class="auth-center">

                <section class="auth-box">
                    <h1 class="auth-title text-center">Inicio de sesión</h1>
                    <p class="text-center auth-welcome">¡Bienvenido/a de vuelta!</p>

                    <?php
                    // Iniciar sesión si no hay ninguna activa
                    if (session_status() === PHP_SESSION_NONE) session_start();

                    // Recoger y limpiar el mensaje de error de login (si existe) y eliminarlo de la sesión
                    $error = $_SESSION['login_error'] ?? '';
                    unset($_SESSION['login_error']);

                    // Recoger y limpiar el mensaje de éxito de registro (si viene de registrarse) y eliminarlo de la sesión
                    $ok = $_SESSION['register_ok'] ?? '';
                    unset($_SESSION['register_ok']);
                    ?>

                    <?php if ($ok): ?>
                        <!-- Mensaje de éxito tras un registro correcto -->
                        <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <!-- Mensaje de error si las credenciales son incorrectas -->
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <!-- Formulario de inicio de sesión: envía por POST al controlador doLogin -->
                    <form method="POST" action="<?= BASE_URL ?>/index.php?url=doLogin" class="auth-form">
                        <label class="label-mc">Correo electrónico</label>
                        <input class="form-control input-mc" type="email" name="email">

                        <label class="label-mc mt-2">Contraseña</label>
                        <input class="form-control input-mc" type="password" name="password">

                        <div class="login-row">
                            <!-- Opción para recordar la contraseña (funcionalidad pendiente de implementar) -->
                            <label class="remember">
                                <input type="checkbox"> Recordar contraseña
                            </label>

                            <!-- Enlace de recuperación de contraseña (funcionalidad pendiente de implementar) -->
                            <a class="forgot" href="#">¿Has olvidado la contraseña?</a>
                        </div>

                        <!-- Botón de login con Google (funcionalidad pendiente de implementar) -->
                        <button class="btn btn-google w-100" type="button">
                            <img src="<?= BASE_URL ?>/img/google.png" alt="google" onerror="this.style.display='none'">
                            Login with Google
                        </button>

                        <!-- Botón de envío del formulario -->
                        <button class="btn btn-mc w-100 mt-2" type="submit">Iniciar Sesión</button>

                        <!-- Enlace a la página de registro para nuevos usuarios -->
                        <p class="auth-small text-center mt-3">
                            ¿No estás registrado todavía?
                            <a href="<?= BASE_URL ?>/index.php?url=register">Regístrate</a>
                        </p>
                    </form>
                </section>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
