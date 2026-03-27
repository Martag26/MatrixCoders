<?php

// Modelo que representa la tabla 'carpeta' de la base de datos.
// Gestiona las carpetas de notas de cada usuario.
class Carpeta
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Devuelve todas las carpetas raíz de un usuario (sin carpeta padre)
    public function obtenerPorUsuario(int $usuario_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM carpeta WHERE usuario_id = ? AND padre_id IS NULL ORDER BY nombre ASC"
        );
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve las subcarpetas de una carpeta concreta
    public function obtenerSubcarpetas(int $padre_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM carpeta WHERE padre_id = ? ORDER BY nombre ASC"
        );
        $stmt->execute([$padre_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crea una carpeta nueva para un usuario
    public function crear(int $usuario_id, string $nombre, ?int $padre_id = null): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO carpeta (usuario_id, padre_id, nombre) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$usuario_id, $padre_id, $nombre]);
    }

    // Elimina una carpeta por su id
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM carpeta WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
