<?php

// Modelo que representa la tabla 'tarea' de la base de datos.
// Gestiona las tareas de cada curso con su fecha límite.
class Tarea
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Devuelve las tareas pendientes de un usuario a través de sus matrículas,
    // ordenadas por fecha límite más próxima
    public function obtenerPorUsuario(int $usuario_id): array
    {
        $stmt = $this->db->prepare("
            SELECT
                t.*,
                c.id AS curso_id,
                c.titulo AS curso,
                l.id AS leccion_id,
                l.titulo AS leccion,
                e.id AS entrega_id,
                e.nota AS entrega_nota,
                e.entregado_en
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            JOIN tarea t ON t.curso_id = c.id
            LEFT JOIN leccion l ON l.id = t.leccion_id
            LEFT JOIN entrega e ON e.tarea_id = t.id AND e.usuario_id = m.usuario_id
            WHERE m.usuario_id = ?
              AND t.fecha_limite IS NOT NULL
            ORDER BY
                CASE WHEN e.id IS NOT NULL THEN 1 ELSE 0 END ASC,
                t.fecha_limite ASC,
                t.id ASC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve las tareas de un curso concreto
    public function obtenerPorCurso(int $curso_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, l.titulo AS leccion
             FROM tarea t
             LEFT JOIN leccion l ON l.id = t.leccion_id
             WHERE t.curso_id = ?
             ORDER BY t.fecha_limite ASC, t.id ASC"
        );
        $stmt->execute([$curso_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPanelUsuario(int $usuario_id): array
    {
        $tareas = $this->obtenerPorUsuario($usuario_id);
        $hoy = strtotime(date('Y-m-d'));

        foreach ($tareas as &$tarea) {
            $fecha = !empty($tarea['fecha_limite']) ? strtotime(substr($tarea['fecha_limite'], 0, 10)) : null;
            $diasRestantes = $fecha ? (int)floor(($fecha - $hoy) / 86400) : null;
            $entregada = !empty($tarea['entrega_id']);

            if ($entregada) {
                $estado = 'entregada';
            } elseif ($diasRestantes !== null && $diasRestantes < 0) {
                $estado = 'vencida';
            } elseif ($diasRestantes !== null && $diasRestantes <= 3) {
                $estado = 'proxima';
            } else {
                $estado = 'pendiente';
            }

            $tarea['dias_restantes'] = $diasRestantes;
            $tarea['estado_visual'] = $estado;
        }
        unset($tarea);

        return $tareas;
    }

    // Devuelve los días del mes que tienen tareas para marcar en el calendario.
    // Usa strftime() en lugar de YEAR()/MONTH()/DAY() para compatibilidad con SQLite.
    public function obtenerDiasConEventos(int $usuario_id, int $anyo, int $mes): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT CAST(strftime('%d', t.fecha_limite) AS INTEGER) AS dia
            FROM matricula m
            JOIN tarea t ON t.curso_id = m.curso_id
            WHERE m.usuario_id = ?
              AND t.fecha_limite IS NOT NULL
              AND strftime('%Y', t.fecha_limite) = ?
              AND strftime('%m', t.fecha_limite) = ?
        ");
        $stmt->execute([
            $usuario_id,
            sprintf('%04d', $anyo),  // '2026'
            sprintf('%02d', $mes),   // '03'
        ]);
        return array_map(fn($r) => (int)$r['dia'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
