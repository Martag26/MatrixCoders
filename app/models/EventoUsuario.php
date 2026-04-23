<?php
class EventoUsuario
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerPorUsuario(int $usuario_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM evento_usuario WHERE usuario_id = ? ORDER BY fecha_inicio ASC"
        );
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id, int $usuario_id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM evento_usuario WHERE id = ? AND usuario_id = ?"
        );
        $stmt->execute([$id, $usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear(int $usuario_id, array $d): int|false
    {
        $stmt = $this->db->prepare("
            INSERT INTO evento_usuario
                (usuario_id, titulo, descripcion, fecha_inicio, fecha_fin, tipo, color, todo_el_dia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ok = $stmt->execute([
            $usuario_id,
            trim($d['titulo']),
            $d['descripcion'] ?? null,
            $d['fecha_inicio'],
            $d['fecha_fin'] ?? null,
            $d['tipo'] ?? 'sesion',
            $d['color'] ?? null,
            (int)($d['todo_el_dia'] ?? 1),
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function actualizar(int $id, int $usuario_id, array $d): bool
    {
        $stmt = $this->db->prepare("
            UPDATE evento_usuario
            SET titulo = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?,
                tipo = ?, color = ?, todo_el_dia = ?
            WHERE id = ? AND usuario_id = ?
        ");
        return $stmt->execute([
            trim($d['titulo']),
            $d['descripcion'] ?? null,
            $d['fecha_inicio'],
            $d['fecha_fin'] ?? null,
            $d['tipo'] ?? 'sesion',
            $d['color'] ?? null,
            (int)($d['todo_el_dia'] ?? 1),
            $id,
            $usuario_id,
        ]);
    }

    public function eliminar(int $id, int $usuario_id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM evento_usuario WHERE id = ? AND usuario_id = ?"
        );
        return $stmt->execute([$id, $usuario_id]);
    }
}
