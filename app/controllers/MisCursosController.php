<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class MisCursosController
{
    public function index(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $rolSesion = $_SESSION['usuario_rol'] ?? 'USUARIO';
        if ($rolSesion !== 'USUARIO') {
            header("Location: " . BASE_URL . "/index.php?url=crm");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];

        $database = new Database();
        $conexion = $database->connect();

        $filtro = $_GET['filtro'] ?? 'todos';

        $stmt = $conexion->prepare("
            SELECT
                c.id,
                c.titulo,
                c.descripcion,
                c.imagen,
                c.nivel,
                c.categoria,
                c.duracion_min,
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
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursos as &$c) {
            $total  = (int)$c['total_lecciones'];
            $vistos = (int)$c['lecciones_vistas'];
            $c['progreso'] = $total > 0 ? round(($vistos / $total) * 100) : 0;

            if ($total > 0 && $vistos >= $total) {
                $c['estado'] = 'completado';
            } elseif ($vistos > 0) {
                $c['estado'] = 'en_progreso';
            } else {
                $c['estado'] = 'sin_empezar';
            }

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

        if ($filtro === 'en_progreso') {
            $cursos = array_values(array_filter($cursos, fn($c) => $c['estado'] === 'en_progreso'));
        } elseif ($filtro === 'completados') {
            $cursos = array_values(array_filter($cursos, fn($c) => $c['estado'] === 'completado'));
        } elseif ($filtro === 'sin_empezar') {
            $cursos = array_values(array_filter($cursos, fn($c) => $c['estado'] === 'sin_empezar'));
        }

        $pageTitle = "Mis cursos";
        require __DIR__ . '/../views/mis-cursos/index.php';
    }
}
