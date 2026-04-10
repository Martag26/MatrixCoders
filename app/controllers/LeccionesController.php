<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Curso.php';

if (session_status() === PHP_SESSION_NONE) session_start();

class LeccionesController
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
        $modeloCurso = new Curso($conexion);

        // Todos los cursos en los que está matriculado el usuario
        $stmt = $conexion->prepare("
            SELECT c.*, m.fecha AS fecha_matricula
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            WHERE m.usuario_id = ?
            ORDER BY m.fecha DESC
        ");
        $stmt->execute([$usuario_id]);
        $misCursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Para cada curso: unidades+lecciones + progreso
        foreach ($misCursos as &$curso) {
            $curso['unidades'] = $modeloCurso->getUnidadesConLecciones($curso['id']);

            // Total lecciones
            $total = 0;
            foreach ($curso['unidades'] as $u) {
                $total += count($u['lecciones'] ?? []);
            }
            $curso['total_lecciones'] = $total;

            // Lecciones vistas
            $vistas = $modeloCurso->getLeccionesVistas($usuario_id, $curso['id']);
            $curso['lecciones_vistas']  = count($vistas);
            $curso['vistas_ids']        = $vistas; // [id => true]
            $curso['progreso']          = $total > 0 ? round((count($vistas) / $total) * 100) : 0;

            // Última lección vista
            $stmt2 = $conexion->prepare("
                SELECT lv.leccion_id FROM leccion_vista lv
                JOIN leccion l ON l.id = lv.leccion_id
                JOIN unidad  u ON u.id = l.unidad_id
                WHERE lv.usuario_id = ? AND u.curso_id = ?
                ORDER BY lv.visto_at DESC LIMIT 1
            ");
            $stmt2->execute([$usuario_id, $curso['id']]);
            $ultimaId = $stmt2->fetchColumn();

            if ($ultimaId) {
                $curso['ultima_leccion_id'] = $ultimaId;
            } else {
                // Primera lección
                $primera = $modeloCurso->getPrimeraLeccion($curso['id']);
                $curso['ultima_leccion_id'] = $primera['id'] ?? null;
            }
        }
        unset($curso);

        $pageTitle = "Mis lecciones";
        require __DIR__ . '/../views/lecciones/index.php';
    }
}

$controller = new LeccionesController();
$controller->index();
