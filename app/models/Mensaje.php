<?php

// Modelo que representa la tabla 'mensaje' de la base de datos.
// Gestiona la mensajería interna entre usuarios.
class Mensaje
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Devuelve todos los mensajes recibidos por un usuario, del más reciente al más antiguo
    public function obtenerRecibidos(int $usuario_id): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nombre AS nombre_emisor
            FROM mensaje m
            JOIN usuario u ON u.id = m.emisor_id
            WHERE m.receptor_id = ?
            ORDER BY m.enviado_en DESC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve todos los mensajes enviados por un usuario
    public function obtenerEnviados(int $usuario_id): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nombre AS nombre_receptor
            FROM mensaje m
            JOIN usuario u ON u.id = m.receptor_id
            WHERE m.emisor_id = ?
            ORDER BY m.enviado_en DESC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve el número de mensajes no leídos de un usuario
    public function contarNoLeidos(int $usuario_id): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM mensaje WHERE receptor_id = ? AND leido = 0"
        );
        $stmt->execute([$usuario_id]);
        return (int)$stmt->fetchColumn();
    }

    // Marca un mensaje como leído
    public function marcarLeido(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE mensaje SET leido = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Envía un mensaje nuevo
    public function enviar(int $emisor_id, int $receptor_id, string $asunto, string $cuerpo): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO mensaje (emisor_id, receptor_id, asunto, cuerpo) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$emisor_id, $receptor_id, $asunto, $cuerpo]);
    }
}
