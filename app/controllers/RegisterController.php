<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class RegisterController
{
    public function registerForm()
    {
        $pageTitle = "Registro";
        require __DIR__ . '/../views/auth/register.php';
    }

    public function register()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = trim($_POST['password'] ?? '');
        $pass2 = trim($_POST['password2'] ?? '');

        // Validaciones simples (nivel estudiante)
        if ($nombre === '' || $email === '' || $pass === '' || $pass2 === '') {
            $_SESSION['register_error'] = "Rellena todos los campos obligatorios.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

        if ($pass !== $pass2) {
            $_SESSION['register_error'] = "Las contraseñas no coinciden.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

        $database = new Database();
        $conexion = $database->connect();

        // Comprobar email ya existente
        $stmt = $conexion->prepare("SELECT id FROM usuario WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $_SESSION['register_error'] = "Este email ya está registrado.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

        // Guardar la contraseña cifrada con password_hash
        $passHash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conexion->prepare("INSERT INTO usuario (nombre, email, contraseña) VALUES (?, ?, ?)");
        $ok = $stmt->execute([$nombre, $email, $passHash]);

        if (!$ok) {
            $_SESSION['register_error'] = "No se pudo registrar. Inténtalo de nuevo.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

        $_SESSION['register_ok'] = "Cuenta creada correctamente. Ya puedes iniciar sesión.";
        header("Location: " . BASE_URL . "/index.php?url=login");
        exit;
    }
}
