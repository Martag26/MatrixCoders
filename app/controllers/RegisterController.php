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

        $nombre   = trim($_POST['nombre']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $pass     = trim($_POST['password'] ?? '');
        $pass2    = trim($_POST['password2'] ?? '');

        if ($nombre === '' || $email === '' || $pass === '' || $pass2 === '') {
            $this->errorRedirigir("Rellena todos los campos obligatorios.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errorRedirigir("El correo electrónico no tiene un formato válido.");
        }

        if (strlen($pass) < 6) {
            $this->errorRedirigir("La contraseña debe tener al menos 6 caracteres.");
        }

        if ($pass !== $pass2) {
            $this->errorRedirigir("Las contraseñas no coinciden.");
        }

        $database = new Database();
        $conexion = $database->connect();

        $stmt = $conexion->prepare("SELECT id FROM usuario WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $this->errorRedirigir("Este correo electrónico ya está registrado.");
        }

        $passHash = password_hash($pass, PASSWORD_DEFAULT);

        $planesValidos     = ['natural', 'juridica'];
        $nivelesValidos    = ['principiante', 'intermedio', 'avanzado', ''];
        $frecuenciasValidas = ['1-2_dias', '3-4_dias', 'diario', ''];
        $estudiosValidos   = ['ESO', 'Bachillerato', 'FP', 'Universidad', ''];
        $tiposCursoValidos = ['autodidacta', 'tutor', 'clases_vivo', 'videos', ''];

        $tipo_persona         = in_array($_POST['tipo_persona'] ?? '', $planesValidos)      ? $_POST['tipo_persona']         : 'natural';
        $areas_interes        = substr(trim($_POST['areas_interes']        ?? ''), 0, 255) ?: null;
        $tecnologias          = substr(trim($_POST['tecnologias']          ?? ''), 0, 255) ?: null;
        $github               = substr(trim($_POST['github']               ?? ''), 0, 255) ?: null;
        $objetivo             = substr(trim($_POST['objetivo']             ?? ''), 0, 255) ?: null;
        $nivel_experiencia    = in_array($_POST['nivel_experiencia']    ?? '', $nivelesValidos)    ? ($_POST['nivel_experiencia']    ?: null) : null;
        $frecuencia_estudio   = in_array($_POST['frecuencia_estudio']   ?? '', $frecuenciasValidas) ? ($_POST['frecuencia_estudio']   ?: null) : null;
        $ultimo_estudio       = in_array($_POST['ultimo_estudio']       ?? '', $estudiosValidos)    ? ($_POST['ultimo_estudio']       ?: null) : null;
        $tipo_curso_preferido = in_array($_POST['tipo_curso_preferido'] ?? '', $tiposCursoValidos)  ? ($_POST['tipo_curso_preferido'] ?: null) : null;

        $stmt = $conexion->prepare("
            INSERT INTO usuario
                (nombre, email, contraseña, tipo_persona, areas_interes, tecnologias,
                 github, objetivo, nivel_experiencia, frecuencia_estudio,
                 ultimo_estudio, tipo_curso_preferido)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $ok = $stmt->execute([
            $nombre, $email, $passHash, $tipo_persona,
            $areas_interes, $tecnologias, $github, $objetivo,
            $nivel_experiencia, $frecuencia_estudio, $ultimo_estudio, $tipo_curso_preferido,
        ]);

        if (!$ok) {
            $this->errorRedirigir("No se pudo registrar la cuenta. Inténtalo de nuevo.");
        }

        $_SESSION['register_ok'] = "Cuenta creada correctamente. Ya puedes iniciar sesión.";
        header("Location: " . BASE_URL . "/index.php?url=login");
        exit;
    }

    private function errorRedirigir(string $mensaje): never
    {
        $_SESSION['register_error'] = $mensaje;
        $_SESSION['register_old']   = $_POST;
        header("Location: " . BASE_URL . "/index.php?url=register");
        exit;
    }
}
