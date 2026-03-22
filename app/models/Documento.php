<?php

// Modelo que representa la tabla 'documento' de la base de datos.
// Gestiona los apuntes y documentos del usuario dentro de sus carpetas.
class Documento
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Devuelve todos los documentos de un usuario
    public function obtenerPorUsuario(int $usuario_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM documento WHERE usuario_id = ? ORDER BY titulo ASC"
        );
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve los documentos de una carpeta concreta
    public function obtenerPorCarpeta(int $carpeta_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM documento WHERE carpeta_id = ? ORDER BY titulo ASC"
        );
        $stmt->execute([$carpeta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve un documento concreto por su id
    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM documento WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crea un documento nuevo
    public function crear(int $usuario_id, string $titulo, ?int $carpeta_id = null, string $contenido = ''): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO documento (usuario_id, carpeta_id, titulo, contenido) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$usuario_id, $carpeta_id, $titulo, $contenido]);
    }

    // Actualiza el contenido y título de un documento
    public function actualizar(int $id, string $titulo, string $contenido): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE documento SET titulo = ?, contenido = ? WHERE id = ?"
        );
        return $stmt->execute([$titulo, $contenido, $id]);
    }

    // Elimina un documento por su id
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM documento WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
