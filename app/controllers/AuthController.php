<?php

/**
 * Controlador de autenticación.
 *
 * Gestiona el inicio y cierre de sesión de los usuarios
 * registrados en la plataforma.
 */

require_once __DIR__ . '/../db.php';

class AuthController
{
    /**
     * Muestra el formulario de inicio de sesión.
     *
     * Establece el título de la página y carga la vista correspondiente.
     *
     * @return void
     */
    public function loginForm()
    {
        $pageTitle = "Iniciar sesión";
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesa el formulario de inicio de sesión.
     *
     * Recoge las credenciales enviadas por POST, las compara contra
     * la base de datos y, si son correctas, inicia la sesión del usuario
     * almacenando su ID y nombre. En caso de error redirige al formulario
     * con un mensaje de error en sesión.
     *
     * @return void
     */
    public function login()
    {
        // Iniciar sesión solo si no hay ninguna activa
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Recoger y limpiar los datos del formulario
        $email = trim($_POST['email'] ?? '');
        $pass  = trim($_POST['password'] ?? '');

        // Conectar a la base de datos
        $database = new Database();
        $conexion = $database->connect();

        // Buscar el usuario por su email (máximo 1 resultado)
        $stmt = $conexion->prepare("SELECT * FROM usuario WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar que el usuario existe y que la contraseña coincide
        if (!$user || $user['contraseña'] !== $pass) {
            // Guardar mensaje de error en sesión y redirigir al login
            $_SESSION['login_error'] = "Email o contraseña incorrectos";
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        // Almacenar datos del usuario en la sesión
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_plan'] = $user['plan'];

        // Redirigir al panel principal tras un login correcto
        header("Location: " . BASE_URL . "/index.php?url=dashboard");
        exit;
    }

    /**
     * Cierra la sesión activa del usuario.
     *
     * Destruye todos los datos de sesión y redirige
     * a la página de inicio de la aplicación.
     *
     * @return void
     */
    public function logout()
    {
        // Eliminar todos los datos almacenados en la sesión
        session_destroy();

        // Redirigir a la página principal
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}
