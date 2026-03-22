<?php
require_once __DIR__ . '/../db.php';

class AuthController
{
    public function loginForm()
    {
        $pageTitle = "Iniciar sesión";
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $email = trim($_POST['email'] ?? '');
        $pass  = trim($_POST['password'] ?? '');

        $database = new Database();
        $conexion = $database->connect();

        $stmt = $conexion->prepare("SELECT * FROM usuario WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['contraseña'] !== $pass) {
            $_SESSION['login_error'] = "Email o contraseña incorrectos";
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];

        header("Location: " . BASE_URL . "/index.php?url=dashboard");
        exit;
    }

    public function logout()
    {
        session_destroy();
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}
