<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Carpeta.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/Tarea.php';

class DashboardController
{
    public function index()
    {
        // Si el usuario no ha iniciado sesión, lo redirigimos al login
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];

        $database = new Database();
        $conexion = $database->connect();

        // Leemos el mes y año del calendario desde la URL, o usamos el actual
        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');

        // Validamos que el mes y año estén en rangos razonables
        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        // Días del mes con tareas (para marcar en el calendario)
        $tareaModel  = new Tarea($conexion);
        $diasEventos = $tareaModel->obtenerDiasConEventos($usuario_id, $calYear, $calMonth);

        // Próximos 5 eventos del usuario ordenados por fecha límite
        $eventos = $tareaModel->obtenerPorUsuario($usuario_id);
        $eventos = array_slice($eventos, 0, 5);

        // Carpetas del usuario para la zona de notas
        $carpetaModel = new Carpeta($conexion);
        $carpetas     = $carpetaModel->obtenerPorUsuario($usuario_id);

        // Documentos del usuario para la zona de notas
        $documentoModel = new Documento($conexion);
        $documentos     = $documentoModel->obtenerPorUsuario($usuario_id);

        // Último curso matriculado para el widget "Seguir viendo"
        $stmt = $conexion->prepare("
            SELECT c.id, c.titulo
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            WHERE m.usuario_id = ?
            ORDER BY m.fecha DESC
            LIMIT 1
        ");
        $stmt->execute([$usuario_id]);
        $seguirCurso = $stmt->fetch(PDO::FETCH_ASSOC);

        $pageTitle = "Espacio de trabajo";
        $pageCss   = BASE_URL . "/css/dashboard.css";

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
