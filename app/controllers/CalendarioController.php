<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Tarea.php';
require_once __DIR__ . '/../models/EventoUsuario.php';

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

        // Cursos en progreso con expiración
        $stmt = $conexion->prepare("
            SELECT c.id, c.titulo, m.fecha AS fecha_matricula,
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

            $tsMatricula = strtotime($cp['fecha_matricula'] ?? 'now');
            $tsExpira    = $tsMatricula + (90 * 86400);
            $cp['fecha_expiracion'] = date('Y-m-d', $tsExpira);
            $cp['dias_restantes']   = (int)ceil(($tsExpira - time()) / 86400);
        }
        unset($cp);

        $pageTitle     = "Planificador";
        $tareaModel    = new Tarea($conexion);
        $tareasUsuario = $tareaModel->obtenerPanelUsuario($usuario_id);

        // Eventos personales del usuario
        $eventoModel    = new EventoUsuario($conexion);
        $eventosPersonales = $eventoModel->obtenerPorUsuario($usuario_id);

        // Paleta de colores por curso
        $palette     = ['#6B8F71','#3b82f6','#f59e0b','#8b5cf6','#ec4899','#0ea5e9','#14b8a6','#ef4444'];
        $cursoColors = [];
        $ci = 0;
        foreach ($cursosEnProgreso as $cp) {
            $cursoColors[$cp['titulo']] = $palette[$ci++ % count($palette)];
        }
        foreach ($tareasUsuario as $t) {
            $key = $t['curso'] ?? 'Curso';
            if (!isset($cursoColors[$key])) $cursoColors[$key] = $palette[$ci++ % count($palette)];
        }

        // Colores por tipo de evento personal
        $tipoColores = [
            'sesion'       => '#6B8F71',
            'hito'         => '#f59e0b',
            'recordatorio' => '#3b82f6',
            'bloqueo'      => '#94a3b8',
        ];

        // Eventos FullCalendar: tareas de cursos
        $fcEvents = [];
        foreach ($tareasUsuario as $t) {
            if (empty($t['fecha_limite'])) continue;
            $fcEvents[] = [
                'id'            => 'tarea_' . $t['id'],
                'title'         => $t['titulo'],
                'start'         => substr($t['fecha_limite'], 0, 10),
                'color'         => $cursoColors[$t['curso'] ?? ''] ?? '#6B8F71',
                'extendedProps' => [
                    'tipo'        => 'tarea',
                    'curso'       => $t['curso'] ?? '',
                    'descripcion' => $t['descripcion'] ?? '',
                    'editable'    => false,
                ],
            ];
        }

        // Eventos FullCalendar: expiraciones de cursos
        foreach ($cursosEnProgreso as $cp) {
            $fcEvents[] = [
                'id'            => 'exp_' . $cp['id'],
                'title'         => '⏰ Expira: ' . $cp['titulo'],
                'start'         => $cp['fecha_expiracion'],
                'color'         => '#ef4444',
                'allDay'        => true,
                'extendedProps' => [
                    'tipo'        => 'expiracion',
                    'curso'       => $cp['titulo'],
                    'descripcion' => 'Fecha límite del curso (3 meses desde la matrícula).',
                    'editable'    => false,
                ],
            ];
        }

        // Eventos FullCalendar: eventos personales
        foreach ($eventosPersonales as $ev) {
            $color = $ev['color'] ?? ($tipoColores[$ev['tipo']] ?? '#6B8F71');
            $fcEvents[] = [
                'id'            => 'ev_' . $ev['id'],
                'title'         => $ev['titulo'],
                'start'         => $ev['fecha_inicio'],
                'end'           => $ev['fecha_fin'] ?? null,
                'allDay'        => (bool)(int)$ev['todo_el_dia'],
                'color'         => $color,
                'extendedProps' => [
                    'tipo'        => $ev['tipo'],
                    'descripcion' => $ev['descripcion'] ?? '',
                    'editable'    => true,
                    'ev_id'       => (int)$ev['id'],
                ],
            ];
        }

        require __DIR__ . '/../views/calendario/index.php';
    }
}

$controller = new CalendarioController();
$controller->index();
