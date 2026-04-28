<?php
class Notificacion
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerRecientes(int $usuario_id, int $limite = 25): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM notificacion
            WHERE usuario_id = ?
            ORDER BY leido ASC, creado_en DESC
            LIMIT ?
        ");
        $stmt->execute([$usuario_id, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarNoLeidas(int $usuario_id): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notificacion WHERE usuario_id = ? AND leido = 0"
        );
        $stmt->execute([$usuario_id]);
        return (int)$stmt->fetchColumn();
    }

    public function marcarLeida(int $id, int $usuario_id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE notificacion SET leido = 1 WHERE id = ? AND usuario_id = ?"
        );
        return $stmt->execute([$id, $usuario_id]);
    }

    public function marcarTodasLeidas(int $usuario_id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE notificacion SET leido = 1 WHERE usuario_id = ?"
        );
        return $stmt->execute([$usuario_id]);
    }

    /**
     * Genera notificaciones automáticas sin duplicar.
     * Deduplica por tipo + ref_id para evitar repeticiones en cada petición.
     */
    public function sincronizarAutomaticas(int $usuario_id): void
    {
        // Tareas próximas sin entregar (próximos 3 días)
        $stmt = $this->db->prepare("
            SELECT t.id, t.titulo, t.fecha_limite, c.titulo AS curso
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            JOIN tarea t ON t.curso_id = c.id
            LEFT JOIN entrega e  ON e.tarea_id  = t.id  AND e.usuario_id = m.usuario_id
            LEFT JOIN notificacion n ON n.usuario_id = ? AND n.tipo = 'tarea' AND n.ref_id = t.id
            WHERE m.usuario_id = ?
              AND e.id IS NULL
              AND n.id IS NULL
              AND t.fecha_limite BETWEEN date('now') AND date('now', '+3 days')
        ");
        $stmt->execute([$usuario_id, $usuario_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
            $this->insertar(
                $usuario_id,
                'tarea',
                'Tarea próxima: ' . $t['titulo'],
                'Curso: ' . $t['curso'] . ' · Entrega el ' . substr($t['fecha_limite'], 0, 10),
                BASE_URL . '/index.php?url=tareas',
                (int)$t['id']
            );
        }

        // Tareas vencidas sin entregar (aviso único por tarea)
        $stmtVen = $this->db->prepare("
            SELECT t.id, t.titulo, t.fecha_limite, c.titulo AS curso
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            JOIN tarea t ON t.curso_id = c.id
            LEFT JOIN entrega e ON e.tarea_id = t.id AND e.usuario_id = m.usuario_id
            LEFT JOIN notificacion n ON n.usuario_id = ? AND n.tipo = 'tarea_vencida' AND n.ref_id = t.id
            WHERE m.usuario_id = ?
              AND e.id IS NULL
              AND n.id IS NULL
              AND t.fecha_limite < date('now')
        ");
        $stmtVen->execute([$usuario_id, $usuario_id]);
        foreach ($stmtVen->fetchAll(PDO::FETCH_ASSOC) as $t) {
            $this->insertar(
                $usuario_id,
                'tarea_vencida',
                'Tarea vencida: ' . $t['titulo'],
                'Curso: ' . $t['curso'] . ' · Venció el ' . substr($t['fecha_limite'], 0, 10),
                BASE_URL . '/index.php?url=calendario',
                (int)$t['id']
            );
        }

        // Eventos personales del calendario con recordatorio mañana o pasado
        $stmtEv = $this->db->prepare("
            SELECT ev.id, ev.titulo, ev.fecha_inicio, ev.tipo
            FROM evento_usuario ev
            LEFT JOIN notificacion n ON n.usuario_id = ? AND n.tipo = 'evento_calendario' AND n.ref_id = ev.id
            WHERE ev.usuario_id = ?
              AND n.id IS NULL
              AND ev.fecha_inicio BETWEEN date('now', '+1 day') AND date('now', '+2 days')
        ");
        $stmtEv->execute([$usuario_id, $usuario_id]);
        foreach ($stmtEv->fetchAll(PDO::FETCH_ASSOC) as $ev) {
            $tipoLabel = match($ev['tipo'] ?? '') {
                'sesion'       => 'Sesión de estudio',
                'hito'         => 'Hito personal',
                'recordatorio' => 'Recordatorio',
                'bloqueo'      => 'Bloqueo de tiempo',
                default        => 'Evento',
            };
            $this->insertar(
                $usuario_id,
                'evento_calendario',
                $tipoLabel . ': ' . $ev['titulo'],
                'Programado para el ' . substr($ev['fecha_inicio'], 0, 10),
                BASE_URL . '/index.php?url=calendario',
                (int)$ev['id']
            );
        }

        // Cursos que expiran en los próximos 7 días
        $stmt2 = $this->db->prepare("
            SELECT m.id AS matricula_id, c.titulo,
                   date(m.fecha, '+90 days') AS fecha_expiracion
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            LEFT JOIN notificacion n ON n.usuario_id = ? AND n.tipo = 'expiracion' AND n.ref_id = m.id
            WHERE m.usuario_id = ?
              AND n.id IS NULL
              AND date(m.fecha, '+90 days') BETWEEN date('now') AND date('now', '+7 days')
        ");
        $stmt2->execute([$usuario_id, $usuario_id]);
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $this->insertar(
                $usuario_id,
                'expiracion',
                'Curso expira pronto: ' . $c['titulo'],
                'Tu acceso termina el ' . $c['fecha_expiracion'],
                BASE_URL . '/index.php?url=calendario',
                (int)$c['matricula_id']
            );
        }

        // Mensajes no leídos aún sin notificación
        $stmt3 = $this->db->prepare("
            SELECT msg.id, msg.asunto, u.nombre AS emisor
            FROM mensaje msg
            JOIN usuario u ON u.id = msg.emisor_id
            LEFT JOIN notificacion n ON n.usuario_id = ? AND n.tipo = 'mensaje' AND n.ref_id = msg.id
            WHERE msg.receptor_id = ?
              AND msg.leido = 0
              AND n.id IS NULL
        ");
        $stmt3->execute([$usuario_id, $usuario_id]);
        foreach ($stmt3->fetchAll(PDO::FETCH_ASSOC) as $msg) {
            $this->insertar(
                $usuario_id,
                'mensaje',
                'Nuevo mensaje: ' . $msg['asunto'],
                'De: ' . $msg['emisor'],
                BASE_URL . '/index.php?url=buzon',
                (int)$msg['id']
            );
        }

        // Campañas CRM activas para el perfil del usuario
        $stmt4 = $this->db->prepare("
            SELECT crm.id, crm.titulo, crm.cuerpo
            FROM campana_crm crm
            LEFT JOIN usuario_preferencias pref ON pref.usuario_id = ?
            LEFT JOIN notificacion n ON n.usuario_id = ? AND n.tipo = 'crm' AND n.ref_id = crm.id
            WHERE crm.activa = 1
              AND n.id IS NULL
              AND (crm.fecha_inicio IS NULL OR crm.fecha_inicio <= date('now'))
              AND (crm.fecha_fin   IS NULL OR crm.fecha_fin   >= date('now'))
              AND (crm.perfil_target IS NULL
                   OR crm.perfil_target = COALESCE(pref.perfil, 'estudiante'))
        ");
        $stmt4->execute([$usuario_id, $usuario_id]);
        foreach ($stmt4->fetchAll(PDO::FETCH_ASSOC) as $crm) {
            $this->insertar(
                $usuario_id,
                'crm',
                $crm['titulo'],
                $crm['cuerpo'],
                null,
                (int)$crm['id']
            );
        }
    }

    private function insertar(
        int $uid,
        string $tipo,
        string $titulo,
        ?string $cuerpo,
        ?string $url,
        ?int $ref_id
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO notificacion (usuario_id, tipo, titulo, cuerpo, url_accion, ref_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$uid, $tipo, $titulo, $cuerpo, $url, $ref_id]);
    }
}
