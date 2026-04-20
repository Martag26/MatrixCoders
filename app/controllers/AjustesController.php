<?php

/**
 * Controlador de ajustes de usuario.
 *
 * Gestiona la visualización y edición de las preferencias
 * del usuario autenticado (idioma, notificaciones, privacidad, contraseña).
 */

require_once __DIR__ . '/../db.php';

class AjustesController
{
    /** @var PDO */
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->migrarColumnas();
    }

    /**
     * Añade las columnas de perfil/ajustes a la tabla usuario si no existen.
     */
    private function migrarColumnas(): void
    {
        $nuevas = [
            "foto           TEXT    DEFAULT NULL",
            "bio            TEXT    DEFAULT NULL",
            "idioma         TEXT    NOT NULL DEFAULT 'es'",
            "notificaciones INTEGER NOT NULL DEFAULT 1",
            "privacidad     TEXT    NOT NULL DEFAULT 'publico'",
        ];

        foreach ($nuevas as $definicion) {
            try {
                $this->db->exec("ALTER TABLE usuario ADD COLUMN $definicion");
            } catch (PDOException) {
                // La columna ya existe, se ignora el error
            }
        }
    }

    /**
     * Muestra la página de ajustes del usuario autenticado.
     */
    public function index(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login');
            exit;
        }

        $usuario = $this->obtenerUsuario((int)$_SESSION['usuario_id']);

        if (!$usuario) {
            session_destroy();
            header('Location: ' . BASE_URL . '/index.php?url=login');
            exit;
        }

        $pageTitle = 'Ajustes';
        $flash     = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/ajustes/index.php';
    }

    /**
     * Procesa el formulario de ajustes (idioma, notificaciones, privacidad).
     */
    public function guardar(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login');
            exit;
        }

        $id             = (int)$_SESSION['usuario_id'];
        $idioma         = $_POST['idioma']         ?? 'es';
        $notificaciones = isset($_POST['notificaciones']) ? 1 : 0;
        $privacidad     = $_POST['privacidad']     ?? 'publico';
        $errors         = [];

        // Validaciones
        $idiomasValidos    = ['es', 'en'];
        $privacidadValidas = ['publico', 'privado'];

        if (!in_array($idioma, $idiomasValidos, true)) {
            $errors[] = 'Idioma no válido.';
        }

        if (!in_array($privacidad, $privacidadValidas, true)) {
            $errors[] = 'Opción de privacidad no válida.';
        }

        if ($errors) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
            header('Location: ' . BASE_URL . '/index.php?url=ajustes');
            exit;
        }

        $stmt = $this->db->prepare(
            "UPDATE usuario SET idioma = ?, notificaciones = ?, privacidad = ? WHERE id = ?"
        );
        $stmt->execute([$idioma, $notificaciones, $privacidad, $id]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ajustes guardados correctamente.'];
        header('Location: ' . BASE_URL . '/index.php?url=ajustes');
        exit;
    }

    /**
     * Procesa el cambio de contraseña del usuario.
     */
    public function cambiarContrasena(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login');
            exit;
        }

        $id              = (int)$_SESSION['usuario_id'];
        $actual          = $_POST['contrasena_actual']   ?? '';
        $nueva           = $_POST['contrasena_nueva']    ?? '';
        $confirmacion    = $_POST['contrasena_confirmar'] ?? '';
        $errors          = [];

        // Validaciones
        if ($actual === '') {
            $errors[] = 'Introduce tu contraseña actual.';
        }

        if (mb_strlen($nueva) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        }

        if ($nueva !== $confirmacion) {
            $errors[] = 'Las contraseñas nuevas no coinciden.';
        }

        if (!$errors) {
            $usuario = $this->obtenerUsuario($id);
            if (!$usuario || !password_verify($actual, $usuario['contraseña'])) {
                $errors[] = 'La contraseña actual no es correcta.';
            }
        }

        if ($errors) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
            header('Location: ' . BASE_URL . '/index.php?url=ajustes');
            exit;
        }

        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Contraseña actualizada correctamente.'];
        header('Location: ' . BASE_URL . '/index.php?url=ajustes');
        exit;
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function obtenerUsuario(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
