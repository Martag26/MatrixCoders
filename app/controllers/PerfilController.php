<?php

/**
 * Controlador de perfil de usuario.
 *
 * Gestiona la visualización y edición de los datos personales
 * del usuario autenticado (nombre, foto, bio).
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/PasswordPolicy.php';

class PerfilController
{
    /** @var PDO */
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database   = new Database();
        $this->db   = $database->connect();
        $this->migrarColumnas();
    }

    /**
     * Añade las columnas de perfil/ajustes a la tabla usuario si no existen.
     * Necesario para bases de datos creadas antes de esta funcionalidad.
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
     * Muestra la página de perfil del usuario autenticado.
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

        $stmt = $this->db->prepare(
            "SELECT c.id, c.titulo
             FROM matricula m
             JOIN curso c ON c.id = m.curso_id
             WHERE m.usuario_id = ?
             ORDER BY m.fecha DESC"
        );
        $stmt->execute([(int)$_SESSION['usuario_id']]);
        $cursosMatriculados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Mi perfil';
        $flash     = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/perfil/index.php';
    }

    /**
     * Procesa el formulario de edición del perfil.
     * Solo acepta peticiones POST.
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?url=perfil');
            exit;
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login');
            exit;
        }

        $id     = (int)$_SESSION['usuario_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $bio    = trim($_POST['bio']    ?? '');
        $errors = [];

        // Validaciones
        if ($nombre === '') {
            $errors[] = 'El nombre no puede estar vacío.';
        } elseif (mb_strlen($nombre) > 80) {
            $errors[] = 'El nombre no puede superar los 80 caracteres.';
        }

        if (mb_strlen($bio) > 300) {
            $errors[] = 'La bio no puede superar los 300 caracteres.';
        }

        if ($errors) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
            header('Location: ' . BASE_URL . '/index.php?url=perfil');
            exit;
        }

        // Gestión de la foto de perfil
        $fotoActual = $this->obtenerUsuario($id)['foto'] ?? null;
        $foto       = $fotoActual;

        if (!empty($_FILES['foto']['tmp_name'])) {
            $resultado = $this->subirFoto($_FILES['foto'], $id);
            if ($resultado['ok']) {
                // Eliminar foto anterior si existía
                if ($fotoActual) {
                    $rutaAnterior = __DIR__ . '/../../public/uploads/fotos/' . $fotoActual;
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                $foto = $resultado['nombre'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => $resultado['error']];
                header('Location: ' . BASE_URL . '/index.php?url=perfil');
                exit;
            }
        }

        // Campos académicos (opcionales)
        $nivelesValidos    = ['principiante', 'intermedio', 'avanzado', ''];
        $frecuenciasValidas = ['1-2_dias', '3-4_dias', 'diario', ''];
        $estudiosValidos   = ['ESO', 'Bachillerato', 'FP', 'Universidad', ''];
        $tiposCursoValidos = ['autodidacta', 'tutor', 'clases_vivo', 'videos', ''];

        $areas_interes        = substr(trim($_POST['areas_interes']        ?? ''), 0, 255) ?: null;
        $tecnologias          = substr(trim($_POST['tecnologias']          ?? ''), 0, 255) ?: null;
        $github               = substr(trim($_POST['github']               ?? ''), 0, 255) ?: null;
        $objetivo             = substr(trim($_POST['objetivo']             ?? ''), 0, 255) ?: null;
        $nivel_experiencia    = in_array($_POST['nivel_experiencia']    ?? '', $nivelesValidos)    ? ($_POST['nivel_experiencia']    ?: null) : null;
        $frecuencia_estudio   = in_array($_POST['frecuencia_estudio']   ?? '', $frecuenciasValidas) ? ($_POST['frecuencia_estudio']   ?: null) : null;
        $ultimo_estudio       = in_array($_POST['ultimo_estudio']       ?? '', $estudiosValidos)    ? ($_POST['ultimo_estudio']       ?: null) : null;
        $tipo_curso_preferido = in_array($_POST['tipo_curso_preferido'] ?? '', $tiposCursoValidos)  ? ($_POST['tipo_curso_preferido'] ?: null) : null;

        $stmt = $this->db->prepare(
            "UPDATE usuario SET nombre = ?, bio = ?, foto = ?,
             areas_interes = ?, tecnologias = ?, github = ?, objetivo = ?,
             nivel_experiencia = ?, frecuencia_estudio = ?, ultimo_estudio = ?,
             tipo_curso_preferido = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $nombre, $bio, $foto,
            $areas_interes, $tecnologias, $github, $objetivo,
            $nivel_experiencia, $frecuencia_estudio, $ultimo_estudio, $tipo_curso_preferido,
            $id,
        ]);

        // Actualizar nombre en sesión
        $_SESSION['usuario_nombre'] = $nombre;

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Perfil actualizado correctamente.'];
        header('Location: ' . BASE_URL . '/index.php?url=perfil');
        exit;
    }

    public function cambiarPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?url=perfil');
            exit;
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login');
            exit;
        }

        $id                = (int)$_SESSION['usuario_id'];
        $passwordActual    = $_POST['password_actual'] ?? '';
        $passwordNueva     = $_POST['password_nueva'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';
        $usuario           = $this->obtenerUsuario($id);

        if (!$usuario || empty($usuario['contraseña']) || !password_verify($passwordActual, $usuario['contraseña'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'La contraseña actual no es correcta.'];
            header('Location: ' . BASE_URL . '/index.php?url=perfil');
            exit;
        }

        if ($err = PasswordPolicy::validar($passwordNueva)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $err];
            header('Location: ' . BASE_URL . '/index.php?url=perfil');
            exit;
        }

        if ($passwordNueva !== $passwordConfirmar) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'La confirmación de la contraseña no coincide.'];
            header('Location: ' . BASE_URL . '/index.php?url=perfil');
            exit;
        }

        $stmt = $this->db->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
        $stmt->execute([password_hash($passwordNueva, PASSWORD_BCRYPT), $id]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Contraseña actualizada.'];
        header('Location: ' . BASE_URL . '/index.php?url=perfil');
        exit;
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function obtenerUsuario(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Valida y sube la foto de perfil al directorio de uploads.
     *
     * @param array $file  Entrada de $_FILES['foto']
     * @param int   $userId  ID del usuario (para el nombre único)
     * @return array{ok:bool, nombre?:string, error?:string}
     */
    private function subirFoto(array $file, int $userId): array
    {
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxBytes        = 2 * 1024 * 1024; // 2 MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Error al subir la imagen.'];
        }

        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'error' => 'La imagen no puede superar los 2 MB.'];
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $tiposPermitidos, true)) {
            return ['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG, GIF o WebP.'];
        }

        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombre    = 'avatar_' . $userId . '_' . uniqid() . '.' . strtolower($ext);
        $directorio = __DIR__ . '/../../public/uploads/fotos/';

        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $directorio . $nombre)) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
        }

        return ['ok' => true, 'nombre' => $nombre];
    }
}
