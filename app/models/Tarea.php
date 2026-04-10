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
            SELECT t.*, c.titulo AS curso
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            JOIN tarea t ON t.curso_id = c.id
            WHERE m.usuario_id = ?
              AND t.fecha_limite IS NOT NULL
            ORDER BY t.fecha_limite ASC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve las tareas de un curso concreto
    public function obtenerPorCurso(int $curso_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tarea WHERE curso_id = ? ORDER BY fecha_limite ASC"
        );
        $stmt->execute([$curso_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
