<?php

/**
 * Controlador del panel principal (Dashboard).
 *
 * Carga y prepara toda la información necesaria para el espacio
 * de trabajo del usuario: calendario con eventos, carpetas,
 * documentos, último curso visto y próximas tareas.
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Carpeta.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/Tarea.php';

class DashboardController
{
    /**
     * Muestra el panel principal del usuario autenticado.
     *
     * Comprueba que hay una sesión activa antes de cargar los datos.
     * Recupera de la base de datos el calendario de tareas del mes,
     * las carpetas y documentos del usuario, el último curso en el que
     * estuvo matriculado y los próximos 5 eventos con fecha límite.
     *
     * @return void
     */
    public function index()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        // Obtener el ID del usuario de la sesión y forzar tipo entero
        $usuario_id = (int)$_SESSION['usuario_id'];

        $database = new Database();
        $conexion = $database->connect();
        $documentoModel = new Documento($conexion);
        $tareaModel = new Tarea($conexion);

        // Cargar el plan activo del usuario en sesión si no está ya cargado
        if (empty($_SESSION['usuario_plan'])) {
            $stmtPlan = $conexion->prepare("SELECT plan FROM suscripcion WHERE usuario_id = ? AND status = 'activa' LIMIT 1");
            $stmtPlan->execute([$usuario_id]);
            $filaPlan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
            $_SESSION['usuario_plan'] = $filaPlan['plan'] ?? null;
        }

        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        $documentos = $documentoModel->obtenerConCarpetaPorUsuario($usuario_id);
        $documentosRecientes = array_slice($documentos, 0, 4);
        $tareasUsuario = $tareaModel->obtenerPorUsuario($usuario_id);
        $diasConTareas = $tareaModel->obtenerDiasConEventos($usuario_id, $calYear, $calMonth);

        $stmt = $conexion->prepare("
            SELECT
                c.id,
                c.titulo,
                c.imagen,
                m.fecha AS fecha_matricula,
                (
                    SELECT COUNT(l.id)
                    FROM leccion l
                    JOIN unidad u ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id
                ) AS total_lecciones,
                (
                    SELECT COUNT(DISTINCT lv.leccion_id)
                    FROM leccion_vista lv
                    JOIN leccion l ON l.id = lv.leccion_id
                    JOIN unidad u  ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id
                      AND lv.usuario_id = ?
                ) AS lecciones_vistas,
                (
                    SELECT lv2.leccion_id
                    FROM leccion_vista lv2
                    JOIN leccion l2 ON l2.id = lv2.leccion_id
                    JOIN unidad u2  ON l2.unidad_id = u2.id
                    WHERE u2.curso_id = c.id
                      AND lv2.usuario_id = ?
                    ORDER BY lv2.visto_at DESC
                    LIMIT 1
                ) AS ultima_leccion_id
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            WHERE m.usuario_id = ?
            ORDER BY m.fecha DESC
        ");
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        $cursosEnProgreso = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosEnProgreso as &$c) {
            $total  = (int)$c['total_lecciones'];
            $vistos = (int)$c['lecciones_vistas'];
            $c['progreso'] = $total > 0 ? round(($vistos / $total) * 100) : 0;

            if (!$c['ultima_leccion_id']) {
                $stmt2 = $conexion->prepare("
                    SELECT l.id FROM leccion l
                    JOIN unidad u ON l.unidad_id = u.id
                    WHERE u.curso_id = ?
                    ORDER BY u.orden ASC, l.orden ASC
                    LIMIT 1
                ");
                $stmt2->execute([$c['id']]);
                $c['ultima_leccion_id'] = $stmt2->fetchColumn() ?: null;
            }
        }
        unset($c);

        $pageTitle = "Espacio de trabajo";
        $flash = $_SESSION['dashboard_flash'] ?? null;
        unset($_SESSION['dashboard_flash']);

        // Cargar la vista del panel principal con todas las variables preparadas
        require __DIR__ . '/../views/dashboard/index.php';
    }

    public function documentos()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $database = new Database();
        $conexion = $database->connect();
        $carpetaModel = new Carpeta($conexion);
        $documentoModel = new Documento($conexion);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['dashboard_action'] ?? '') === 'create_folder') {
            $nombre = trim($_POST['folder_name'] ?? '');
            if ($nombre === '') {
                $this->setFlash('error', 'La carpeta necesita un nombre.');
            } else {
                $creada = $carpetaModel->crear($usuario_id, $nombre);
                $this->setFlash(
                    $creada ? 'success' : 'error',
                    $creada ? 'Carpeta creada correctamente.' : 'No se pudo crear la carpeta.'
                );
            }

            header("Location: " . BASE_URL . "/index.php?url=mis-documentos");
            exit;
        }

        $carpetas = $carpetaModel->obtenerConTotalesPorUsuario($usuario_id);
        $documentos = $documentoModel->obtenerConCarpetaPorUsuario($usuario_id);
        $flash = $_SESSION['dashboard_flash'] ?? null;
        unset($_SESSION['dashboard_flash']);
        $pageTitle = 'Mis documentos';
        require __DIR__ . '/../views/dashboard/documentos.php';
    }

    public function tareas()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $database = new Database();
        $conexion = $database->connect();
        $tareaModel = new Tarea($conexion);

        $tareas = $tareaModel->obtenerPanelUsuario($usuario_id);
        $resumen = [
            'total' => count($tareas),
            'pendientes' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'pendiente')),
            'proximas' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'proxima')),
            'vencidas' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'vencida')),
            'entregadas' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'entregada')),
        ];

        $tareasPorCurso = [];
        foreach ($tareas as $tarea) {
            $cursoKey = (string)($tarea['curso'] ?? 'Curso');
            if (!isset($tareasPorCurso[$cursoKey])) {
                $tareasPorCurso[$cursoKey] = [];
            }
            $tareasPorCurso[$cursoKey][] = $tarea;
        }

        $pageTitle = 'Tareas';
        require __DIR__ . '/../views/dashboard/tareas.php';
    }

    public function verDocumento()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $documentoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $database = new Database();
        $conexion = $database->connect();
        $documentoModel = new Documento($conexion);
        $documento = $documentoId > 0 ? $documentoModel->obtenerPorIdYUsuario($documentoId, $usuario_id) : false;

        if (!$documento) {
            http_response_code(404);
        }

        $pageTitle = $documento ? ($documento['titulo'] ?? 'Documento') : 'Documento no encontrado';
        require __DIR__ . '/../views/dashboard/ver_documento.php';
    }

    public function documentoCompartido()
    {
        $documentoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $token = trim($_GET['token'] ?? '');

        $database = new Database();
        $conexion = $database->connect();
        $documentoModel = new Documento($conexion);
        $documento = $documentoId > 0 ? $documentoModel->obtenerPorId($documentoId) : false;

        $documentoValido = $documento && hash_equals($this->buildShareToken($documento), $token);
        if (!$documentoValido) {
            http_response_code(404);
        }

        $pageTitle = $documentoValido ? ($documento['titulo'] ?? 'Documento') : 'Documento no disponible';
        require __DIR__ . '/../views/dashboard/documento_compartido.php';
    }

    private function buildShareToken(array $documento): string
    {
        return hash('sha256', $documento['id'] . '|' . ($documento['usuario_id'] ?? '') . '|mc_share_secret');
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['dashboard_flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

}
