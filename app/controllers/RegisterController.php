<?php
<<<<<<< HEAD

/**
 * Controlador de registro de usuarios.
 *
 * Gestiona la visualización del formulario de registro y el procesamiento
 * de los datos introducidos para crear nuevas cuentas de usuario.
 */

=======
>>>>>>> develop-marta
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class RegisterController
{
<<<<<<< HEAD
    /**
     * Muestra el formulario de registro de nuevos usuarios.
     *
     * Establece el título de la página y carga la vista correspondiente.
     *
     * @return void
     */
=======
>>>>>>> develop-marta
    public function registerForm()
    {
        $pageTitle = "Registro";
        require __DIR__ . '/../views/auth/register.php';
    }

<<<<<<< HEAD
    /**
     * Procesa el formulario de registro.
     *
     * Recoge los datos enviados por POST, aplica validaciones básicas
     * (campos vacíos, coincidencia de contraseñas, email único) y,
     * si todo es correcto, inserta el nuevo usuario en la base de datos.
     * Redirige al login con un mensaje de éxito, o de vuelta al registro
     * con el mensaje de error correspondiente.
     *
     * @return void
     */
    public function register()
    {
        // Iniciar sesión solo si no hay ninguna activa
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Recoger y limpiar los datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $pass   = trim($_POST['password'] ?? '');
        $pass2  = trim($_POST['password2'] ?? '');

        // Validar que ningún campo obligatorio esté vacío
=======
    public function register()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = trim($_POST['password'] ?? '');
        $pass2 = trim($_POST['password2'] ?? '');

        // Validaciones simples (nivel estudiante)
>>>>>>> develop-marta
        if ($nombre === '' || $email === '' || $pass === '' || $pass2 === '') {
            $_SESSION['register_error'] = "Rellena todos los campos obligatorios.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

<<<<<<< HEAD
        // Validar que ambas contraseñas introducidas coinciden
=======
>>>>>>> develop-marta
        if ($pass !== $pass2) {
            $_SESSION['register_error'] = "Las contraseñas no coinciden.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

<<<<<<< HEAD
        // Conectar a la base de datos
        $database = new Database();
        $conexion = $database->connect();

        // Comprobar si el email ya está registrado en la base de datos
=======
        $database = new Database();
        $conexion = $database->connect();

        // Comprobar email ya existente
>>>>>>> develop-marta
        $stmt = $conexion->prepare("SELECT id FROM usuario WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $_SESSION['register_error'] = "Este email ya está registrado.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

<<<<<<< HEAD
        /**
         * Insertar el nuevo usuario en la base de datos.
         * NOTA: la contraseña se guarda en texto plano para mantener
         * compatibilidad con el login actual, que también compara en plano.
         * Se recomienda usar password_hash() en producción.
         */
        $stmt = $conexion->prepare("INSERT INTO usuario (nombre, email, contraseña) VALUES (?, ?, ?)");
        $ok = $stmt->execute([$nombre, $email, $pass]);

        // Comprobar si la inserción se realizó correctamente
=======
        // Insertar usuario (sin hash porque vuestro login compara en plano)
        // OJO: la columna en vuestra BD parece ser 'contraseña'
        $stmt = $conexion->prepare("INSERT INTO usuario (nombre, email, contraseña) VALUES (?, ?, ?)");
        $ok = $stmt->execute([$nombre, $email, $pass]);

>>>>>>> develop-marta
        if (!$ok) {
            $_SESSION['register_error'] = "No se pudo registrar. Inténtalo de nuevo.";
            header("Location: " . BASE_URL . "/index.php?url=register");
            exit;
        }

<<<<<<< HEAD
        // Registro exitoso: guardar mensaje informativo y redirigir al login
=======
>>>>>>> develop-marta
        $_SESSION['register_ok'] = "Cuenta creada correctamente. Ya puedes iniciar sesión.";
        header("Location: " . BASE_URL . "/index.php?url=login");
        exit;
    }
}
