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
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];

        $database = new Database();
        $conexion = $database->connect();

        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');

        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        $tareaModel  = new Tarea($conexion);
        $diasEventos = $tareaModel->obtenerDiasConEventos($usuario_id, $calYear, $calMonth);

        $eventos = $tareaModel->obtenerPorUsuario($usuario_id);
        $eventos = array_slice($eventos, 0, 5);

        $carpetaModel = new Carpeta($conexion);
        $carpetas     = $carpetaModel->obtenerPorUsuario($usuario_id);

        $documentoModel = new Documento($conexion);
        $documentos     = $documentoModel->obtenerPorUsuario($usuario_id);

        // ── SEGUIR VIENDO: todos los cursos matriculados con progreso ──
        $stmt = $conexion->prepare("
            SELECT
                c.id,
                c.titulo,
                c.imagen,
                m.fecha AS fecha_matricula,

                /* Total de lecciones del curso */
                (
                    SELECT COUNT(l.id)
                    FROM leccion l
                    JOIN unidad u ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id
                ) AS total_lecciones,

                /* Lecciones con nota guardada = lecciones vistas */
                (
                    SELECT COUNT(DISTINCT n.leccion_id)
                    FROM leccion_vista n
                    JOIN leccion l ON l.id = n.leccion_id
                    JOIN unidad u  ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id
                      AND n.usuario_id = ?
                ) AS lecciones_vistas,

                /* Última lección visitada (la que tiene la nota más reciente) */
                (
                    SELECT n2.leccion_id
                    FROM leccion_vista n2
                    JOIN leccion l2 ON l2.id = n2.leccion_id
                    JOIN unidad u2  ON l2.unidad_id = u2.id
                    WHERE u2.curso_id = c.id
                      AND n2.usuario_id = ?
                    ORDER BY n2.visto_at DESC
                    LIMIT 1
                ) AS ultima_leccion_id

            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            WHERE m.usuario_id = ?
            ORDER BY m.fecha DESC
        ");
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        $cursosEnProgreso = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* Calcular porcentaje de progreso para cada curso */
        foreach ($cursosEnProgreso as &$c) {
            $total   = (int)$c['total_lecciones'];
            $vistos  = (int)$c['lecciones_vistas'];
            $c['progreso'] = $total > 0 ? round(($vistos / $total) * 100) : 0;

            /* Si no hay última lección, apuntar a la primera del curso */
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
        $pageCss   = BASE_URL . "/css/dashboard.css";

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
