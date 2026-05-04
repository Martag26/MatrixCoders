<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders - Iniciar sesión</title>

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
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $error = $_SESSION['login_error'] ?? '';
                    unset($_SESSION['login_error']);

                    $ok = $_SESSION['register_ok'] ?? '';
                    unset($_SESSION['register_ok']);

                    ?>

                    <?php if ($ok): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/index.php?url=doLogin" class="auth-form">
                        <div class="mb-3">
                            <label class="label-mc">Correo electrónico</label>
                            <input class="form-control input-mc <?= $error ? 'is-invalid' : '' ?>" type="email" name="email">
                        </div>

                        <div class="mb-3">
                            <label class="label-mc">Contraseña</label>
                            <input class="form-control input-mc <?= $error ? 'is-invalid' : '' ?>" type="password" name="password">
                        </div>

                        <div class="login-row">
                            <label class="remember">
                                <input type="checkbox"> Recordar contraseña
                            </label>

                            <a class="forgot fw-bold" href="#">¿Has olvidado la contraseña?</a>
                        </div>

                        <button class="btn btn-google w-100" type="button">
                            <img src="<?= BASE_URL ?>/img/google.png" alt="google" onerror="this.style.display='none'">
                            Login with Google
                        </button>

                        <button class="btn btn-mc w-100 mt-2" type="submit">Iniciar Sesión</button>

                        <p class="auth-small text-center mt-3">
                            ¿No estás registrado todavía?
                            <a class="fw-bold" href="<?= BASE_URL ?>/index.php?url=register">Regístrate</a>
                        </p>
                        <p class="auth-small text-center">
                            ¿Eres administrador?
                            <a class="fw-bold" href="/matrixcoders/admin/">Accede aquí</a>
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
