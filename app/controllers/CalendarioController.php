<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Tarea.php';

if (session_status() === PHP_SESSION_NONE) session_start();

class CalendarioController
{
    public function index()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $database   = new Database();
        $conexion   = $database->connect();

        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        // Cursos en progreso para el panel derecho
        $stmt = $conexion->prepare("
            SELECT c.id, c.titulo,
                (
                    SELECT COUNT(l.id) FROM leccion l
                    JOIN unidad u ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id
                ) AS total_lecciones,
                (
                    SELECT COUNT(DISTINCT lv.leccion_id) FROM leccion_vista lv
                    JOIN leccion l ON l.id = lv.leccion_id
                    JOIN unidad u  ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id AND lv.usuario_id = ?
                ) AS lecciones_vistas,
                (
                    SELECT lv2.leccion_id FROM leccion_vista lv2
                    JOIN leccion l2 ON l2.id = lv2.leccion_id
                    JOIN unidad u2  ON l2.unidad_id = u2.id
                    WHERE u2.curso_id = c.id AND lv2.usuario_id = ?
                    ORDER BY lv2.visto_at DESC LIMIT 1
                ) AS ultima_leccion_id
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            WHERE m.usuario_id = ?
            ORDER BY m.fecha DESC
        ");
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        $cursosEnProgreso = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosEnProgreso as &$cp) {
            $t = (int)$cp['total_lecciones'];
            $v = (int)$cp['lecciones_vistas'];
            $cp['progreso'] = $t > 0 ? round(($v / $t) * 100) : 0;

            if (!$cp['ultima_leccion_id']) {
                $s = $conexion->prepare("
                    SELECT l.id FROM leccion l JOIN unidad u ON l.unidad_id = u.id
                    WHERE u.curso_id = ? ORDER BY u.orden ASC, l.orden ASC LIMIT 1
                ");
                $s->execute([$cp['id']]);
                $cp['ultima_leccion_id'] = $s->fetchColumn() ?: null;
            }
        }
        unset($cp);

        $pageTitle = "Calendario";
        $tareaModel = new Tarea($conexion);
        $tareasUsuario = $tareaModel->obtenerPanelUsuario($usuario_id);
        $tareasPorDia = [];
        foreach ($tareasUsuario as $tarea) {
            if (empty($tarea['fecha_limite'])) {
                continue;
            }

            $timestamp = strtotime($tarea['fecha_limite']);
            if ((int)date('Y', $timestamp) !== $calYear || (int)date('n', $timestamp) !== $calMonth) {
                continue;
            }

            $dia = (int)date('j', $timestamp);
            if (!isset($tareasPorDia[$dia])) {
                $tareasPorDia[$dia] = [];
            }

            $tareasPorDia[$dia][] = $tarea;
        }
        require __DIR__ . '/../views/calendario/index.php';
    }
}

$controller = new CalendarioController();
$controller->index();
