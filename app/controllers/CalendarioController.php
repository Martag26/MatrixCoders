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

        // Tareas urgentes (vencen hoy o en los próximos 3 días, sin entregar)
        $tareasUrgentes = array_values(array_filter($tareasUsuario, function ($t) {
            if ($t['estado_visual'] === 'entregada') return false;
            $d = $t['dias_restantes'];
            return $d !== null && $d >= 0 && $d <= 3;
        }));

        // Tareas vencidas sin entregar
        $tareasVencidas = array_values(array_filter($tareasUsuario, fn($t) =>
            $t['estado_visual'] === 'vencida'
        ));

        // Eventos personales del usuario
        $eventoModel    = new EventoUsuario($conexion);
        $eventosPersonales = $eventoModel->obtenerPorUsuario($usuario_id);

        // Eventos de hoy (tareas + personales)
        $hoyStr = date('Y-m-d');
        $eventosHoy = array_values(array_filter($tareasUsuario, fn($t) =>
            !empty($t['fecha_limite']) && substr($t['fecha_limite'], 0, 10) === $hoyStr
        ));
        $eventosPersonalesHoy = array_values(array_filter($eventosPersonales, fn($e) =>
            !empty($e['fecha_inicio']) && substr($e['fecha_inicio'], 0, 10) === $hoyStr
        ));

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

        // ── SMART SLOTS ────────────────────────────────────────────────────────

        // 1. Racha de estudio: días consecutivos con al menos una lección vista
        $stmtDias = $conexion->prepare("
            SELECT DISTINCT date(visto_at) AS dia
            FROM leccion_vista
            WHERE usuario_id = ?
              AND visto_at IS NOT NULL
            ORDER BY dia DESC
            LIMIT 90
        ");
        $stmtDias->execute([$usuario_id]);
        $diasEstudio = array_column($stmtDias->fetchAll(PDO::FETCH_ASSOC), 'dia');

        $rachaActual = 0;
        if (!empty($diasEstudio)) {
            $hoyRacha = date('Y-m-d');
            $ayerRacha = date('Y-m-d', strtotime('-1 day'));
            // La racha solo cuenta si el último estudio fue hoy o ayer
            if ($diasEstudio[0] === $hoyRacha || $diasEstudio[0] === $ayerRacha) {
                $check = $diasEstudio[0];
                foreach ($diasEstudio as $dia) {
                    if ($dia === $check) {
                        $rachaActual++;
                        $check = date('Y-m-d', strtotime($check . ' -1 day'));
                    } else {
                        break;
                    }
                }
            }
        }

        // 2. Patrones de estudio: día de semana + hora más frecuentes (últimos 60 días)
        $stmtPattern = $conexion->prepare("
            SELECT
                CAST(strftime('%w', visto_at) AS INTEGER) AS dia_semana,
                CAST(strftime('%H', visto_at) AS INTEGER) AS hora,
                COUNT(*) AS frecuencia
            FROM leccion_vista
            WHERE usuario_id = ?
              AND visto_at IS NOT NULL
              AND visto_at >= date('now', '-60 days')
            GROUP BY dia_semana, hora
            ORDER BY frecuencia DESC
            LIMIT 3
        ");
        $stmtPattern->execute([$usuario_id]);
        $patronesEstudio = $stmtPattern->fetchAll(PDO::FETCH_ASSOC);

        // 3. Lecciones pendientes para bloques de estudio sugeridos
        // EXTENSIÓN FUTURA: cuando se añada leccion.duracion_min a la BD, sustituir
        // NULL AS duracion_min_real por l.duracion_min AS duracion_min_real
        // y añadir la lógica CASE para usar el valor real o el fallback.
        $stmtPend = $conexion->prepare("
            SELECT l.id, l.titulo,
                   NULL AS duracion_min_real,
                   30   AS duracion_min,
                   c.titulo AS curso_titulo,
                   c.id     AS curso_id
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            JOIN unidad u ON u.curso_id = c.id
            JOIN leccion l ON l.unidad_id = u.id
            LEFT JOIN leccion_vista lv ON lv.leccion_id = l.id AND lv.usuario_id = m.usuario_id
            WHERE m.usuario_id = ? AND lv.id IS NULL
            ORDER BY u.orden ASC, l.orden ASC
            LIMIT 5
        ");
        $stmtPend->execute([$usuario_id]);
        $leccionesPendientes = $stmtPend->fetchAll(PDO::FETCH_ASSOC);

        // 4. Generar eventos de sugerencia para FullCalendar
        $smartSlots = [];
        $diasSemanaEs = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        // 4a. Smart slot basado en patrón de estudio más frecuente
        if (!empty($patronesEstudio)) {
            $top = $patronesEstudio[0];
            $diaTarget  = (int)$top['dia_semana'];
            $horaTarget = (int)$top['hora'];

            for ($i = 1; $i <= 7; $i++) {
                $fecha = date('Y-m-d', strtotime("+$i days"));
                if ((int)date('w', strtotime($fecha)) === $diaTarget) {
                    $horaIni = sprintf('%02d:00:00', $horaTarget);
                    $horaFin = sprintf('%02d:00:00', min($horaTarget + 2, 22));

                    // Nombre del curso menos avanzado para la sugerencia
                    $cursoSugerido = '';
                    foreach ($cursosEnProgreso as $cp) {
                        if ($cp['progreso'] < 100) {
                            $cursoSugerido = mb_substr($cp['titulo'], 0, 26);
                            break;
                        }
                    }
                    $tituloSlot = $cursoSugerido
                        ? '💡 Estudia: ' . $cursoSugerido
                        : '💡 Tu momento de estudio';

                    $smartSlots[] = [
                        'id'         => 'smart_slot_patron',
                        'title'      => $tituloSlot,
                        'start'      => $fecha . 'T' . $horaIni,
                        'end'        => $fecha . 'T' . $horaFin,
                        'allDay'     => false,
                        'color'      => '#6B8F71',
                        'classNames' => ['smart-suggestion'],
                        'extendedProps' => [
                            'tipo'        => 'smart_slot',
                            'descripcion' => 'Sugerido según tus ' . $top['frecuencia'] . ' sesiones habituales los ' . ($diasSemanaEs[$diaTarget] ?? '') . '.',
                            'editable'    => false,
                            'isSuggestion'=> true,
                        ],
                    ];
                    break;
                }
            }
        }

        // 4b. Bloques de estudio sugeridos para las lecciones pendientes
        // Usa duracion_min real del vídeo si existe; si no, fallback a 30 min
        $slotPalette = ['#8b5cf6', '#0ea5e9', '#14b8a6', '#f59e0b', '#ec4899'];
        foreach (array_values($leccionesPendientes) as $idx => $lec) {
            $fechaBloque = date('Y-m-d', strtotime('+' . ($idx + 2) . ' days'));
            $durReal = (int)($lec['duracion_min_real'] ?? 0) > 0;
            $durMin  = max(5, (int)$lec['duracion_min']);
            $durDesc = $durReal
                ? 'Vídeo · ' . $durMin . ' min'
                : 'Vídeo · ~' . $durMin . ' min (estimado)';

            $minIni  = 18 * 60; // 18:00 por defecto
            $minFin  = $minIni + $durMin;
            $horaIni = sprintf('%02d:%02d:00', intdiv($minIni, 60), $minIni % 60);
            $horaFin = sprintf('%02d:%02d:00', min(intdiv($minFin, 60), 22), $minFin % 60);

            $smartSlots[] = [
                'id'         => 'bloque_sug_' . $lec['id'],
                'title'      => '📚 ' . mb_substr($lec['titulo'], 0, 34),
                'start'      => $fechaBloque . 'T' . $horaIni,
                'end'        => $fechaBloque . 'T' . $horaFin,
                'allDay'     => false,
                'color'      => $slotPalette[$idx % count($slotPalette)],
                'classNames' => ['bloque-sugerido'],
                'extendedProps' => [
                    'tipo'        => 'bloque_sugerido',
                    'curso'       => $lec['curso_titulo'] ?? '',
                    'descripcion' => $durDesc . ' · ' . mb_substr($lec['curso_titulo'] ?? '', 0, 30),
                    'duracion'    => $durMin,
                    'durReal'     => $durReal,
                    'editable'    => false,
                    'isSuggestion'=> true,
                ],
            ];
        }

        // ── SKILLS RADAR ───────────────────────────────────────────────────────
        // Taxonomía desacoplada: skill → keywords para detectar en títulos de curso
        // Extensible: añade nuevas skills o cursos aquí sin tocar la vista
        $skillTaxonomy = [
            'HTML/CSS'       => ['html', 'css', 'maquetación', 'diseño web', 'responsive', 'sass', 'tailwind'],
            'JavaScript'     => ['javascript', 'js ', 'ecmascript', 'vanilla js', 'vanilla'],
            'TypeScript'     => ['typescript', ' ts '],
            'React'          => ['react', 'next.js', 'nextjs', 'next js'],
            'Vue'            => ['vue', 'vuejs', 'nuxt'],
            'Angular'        => ['angular'],
            'Node.js'        => ['node.js', 'nodejs', 'node js', 'express'],
            'PHP'            => ['php', 'laravel', 'symfony'],
            'Python'         => ['python', 'django', 'flask', 'fastapi'],
            'Bases de datos' => ['base de datos', 'bases de datos', 'sql', 'mysql', 'postgresql', 'mongodb', 'sqlite', 'bbdd'],
            'Backend'        => ['backend', 'back-end', 'back end', 'api rest', 'servidor', 'api '],
            'Frontend'       => ['frontend', 'front-end', 'front end', 'interfaz'],
            'DevOps'         => ['devops', 'docker', 'kubernetes', 'deploy', 'ci/cd', 'despliegue'],
            'Git'            => ['git', 'github', 'control de versiones'],
            'Testing'        => ['testing', 'jest', 'cypress', 'pruebas', 'calidad'],
            'UX/UI'          => ['ux ', ' ui ', 'figma', 'diseño de interfaces', 'diseño ui'],
        ];

        // Acumula progreso por skill según los cursos matriculados
        $skillAccum = []; // skill → ['sum' => int, 'count' => int, 'courses' => string[]]
        foreach ($cursosEnProgreso as $cp) {
            $tituloLc     = mb_strtolower($cp['titulo']);
            $matchedSkills = [];
            foreach ($skillTaxonomy as $skillName => $keywords) {
                foreach ($keywords as $kw) {
                    if (mb_strpos($tituloLc, $kw) !== false) {
                        $matchedSkills[$skillName] = true;
                        break;
                    }
                }
            }
            // Fallback: si ninguna skill coincide, usar el título del curso como eje
            if (empty($matchedSkills)) {
                $matchedSkills[mb_substr($cp['titulo'], 0, 20)] = true;
            }
            foreach (array_keys($matchedSkills) as $skill) {
                if (!isset($skillAccum[$skill])) {
                    $skillAccum[$skill] = ['sum' => 0, 'count' => 0, 'courses' => []];
                }
                $skillAccum[$skill]['sum']     += $cp['progreso'];
                $skillAccum[$skill]['count']++;
                $skillAccum[$skill]['courses'][] = mb_substr($cp['titulo'], 0, 24);
            }
        }

        // Construye el array final para Chart.js (nivel = media de progreso por skill)
        $skillsRadar = [];
        foreach ($skillAccum as $skill => $data) {
            $skillsRadar[] = [
                'skill'  => $skill,
                'nivel'  => $data['count'] > 0 ? round($data['sum'] / $data['count']) : 0,
                'cursos' => implode(', ', array_unique($data['courses'])),
            ];
        }
        usort($skillsRadar, fn($a, $b) => $b['nivel'] - $a['nivel']);
        $skillsRadar = array_slice($skillsRadar, 0, 8); // máximo 8 ejes en el radar

        require __DIR__ . '/../views/calendario/index.php';
    }
}

$controller = new CalendarioController();
$controller->index();
