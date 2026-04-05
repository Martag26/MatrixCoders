<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Carpeta.php';
require_once __DIR__ . '/../models/Documento.php';

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

        // Mini calendario — solo navegación visual, sin eventos de tareas
        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        // Carpetas del usuario
        $carpetaModel = new Carpeta($conexion);
        $carpetas     = $carpetaModel->obtenerPorUsuario($usuario_id);

        // Documentos del usuario
        $documentoModel = new Documento($conexion);
        $documentos     = $documentoModel->obtenerPorUsuario($usuario_id);

        // Cursos matriculados con progreso (sección "Seguir viendo")
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
        $pageCss   = BASE_URL . "/css/dashboard.css";

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
