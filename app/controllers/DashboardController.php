<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class DashboardController
{
    public function index()
    {

        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];

        $database = new Database();
        $conexion = $database->connect();

        // Mes/año actual
        $calYear  = (int)date('Y');
        $calMonth = (int)date('n');

        // Días con eventos en el mes actual
        $sqlDiasEventos = "
            SELECT DISTINCT DAY(t.fecha_limite) AS dia
            FROM matricula m
            JOIN tarea t ON t.curso_id = m.curso_id
            WHERE m.usuario_id = ?
            AND t.fecha_limite IS NOT NULL
            AND YEAR(t.fecha_limite) = ?
            AND MONTH(t.fecha_limite) = ?
        ";

        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');

        if ($calMonth < 1) $calMonth = 1;
        if ($calMonth > 12) $calMonth = 12;
        if ($calYear < 2000) $calYear = 2000;
        if ($calYear > 2100) $calYear = 2100;

        $stmt = $conexion->prepare($sqlDiasEventos);
        $stmt->execute([$usuario_id, $calYear, $calMonth]);
        $diasEventos = array_map(fn($r) => (int)$r['dia'], $stmt->fetchAll(PDO::FETCH_ASSOC));

        // Carpetas
        $stmt = $conexion->prepare("SELECT * FROM carpeta WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $carpetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Documentos
        $stmt = $conexion->prepare("SELECT * FROM documento WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Seguir viendo (
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

        // Eventos 
        $stmt = $conexion->prepare("
            SELECT t.titulo, t.fecha_limite, c.titulo AS curso
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            JOIN tarea t ON t.curso_id = c.id
            WHERE m.usuario_id = ?
              AND t.fecha_limite IS NOT NULL
            ORDER BY t.fecha_limite ASC
            LIMIT 5
        ");
        $stmt->execute([$usuario_id]);
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Espacio de trabajo";
        $pageCss = BASE_URL . "/css/dashboard.css";

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
