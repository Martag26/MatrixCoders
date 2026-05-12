<?php

/**
 * Controlador del buzón de mensajes.
 *
 * Gestiona la mensajería bidireccional entre usuarios.
 */

require_once __DIR__ . '/../db.php';

class BuzonController
{
    private $db;
    private $session;

    public function __construct(PDO $db, array $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    public function handle()
    {
        if (!isset($this->session['usuario_id'])) {
            header('Location: index.php');
            exit;
        }

        $action = $_REQUEST['action'] ?? 'index';

        switch ($action) {
            case '':
            case 'index':
                $this->index();
                break;
            case 'bandeja':
                $this->apiBandeja();
                break;
            case 'mensaje':
                $this->apiMensaje();
                break;
            case 'enviar':
                $this->apiEnviar();
                break;
            case 'marcar_leido':
                $this->apiMarcarLeido();
                break;
            case 'no_leidos':
                $this->apiNoLeidos();
                break;
            case 'admins':
                $this->apiAdmins();
                break;
            case 'crear_incidencia':
                $this->apiCrearIncidencia();
                break;
            case 'mis_incidencias':
                $this->apiMisIncidencias();
                break;
            case 'mi_incidencia_detalle':
                $this->apiMiIncidenciaDetalle();
                break;
            default:
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'acción desconocida']);
                break;
        }
    }

    private function index()
    {
        $uid     = (int)$this->session['usuario_id'];
        $perPage = 15;
        $page    = max(1, (int)($_GET['p'] ?? 1));
        $offset  = ($page - 1) * $perPage;
        $msgId   = (int)($_GET['msg'] ?? 0);

        // Contar total de mensajes recibidos de admins/moderadores/instructores
        $stmtTotal = $this->db->prepare("
            SELECT COUNT(*) FROM mensaje m
            JOIN usuario e ON e.id = m.emisor_id
            WHERE m.receptor_id = :uid
        ");
        $stmtTotal->execute(['uid' => $uid]);
        $totalRows = (int)$stmtTotal->fetchColumn();
        $totalPags = max(1, (int)ceil($totalRows / $perPage));

        // Mensajes de la página actual
        $stmtList = $this->db->prepare("
            SELECT m.id, m.asunto, m.cuerpo, m.leido, m.enviado_en,
                   e.nombre AS nombre_emisor, e.rol AS rol_emisor
            FROM mensaje m
            JOIN usuario e ON e.id = m.emisor_id
            WHERE m.receptor_id = :uid
            ORDER BY m.enviado_en DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmtList->bindValue(':uid',    $uid,     PDO::PARAM_INT);
        $stmtList->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmtList->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmtList->execute();
        $mensajes = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        // Contar no leídos
        $stmtNL = $this->db->prepare("SELECT COUNT(*) FROM mensaje WHERE receptor_id = :uid AND leido = 0");
        $stmtNL->execute(['uid' => $uid]);
        $noLeidos = (int)$stmtNL->fetchColumn();

        // Mensaje activo (si se pasa ?msg=id)
        $msgActivo = null;
        if ($msgId > 0) {
            $stmtMsg = $this->db->prepare("
                SELECT m.*, e.nombre AS nombre_emisor, e.rol AS rol_emisor
                FROM mensaje m
                JOIN usuario e ON e.id = m.emisor_id
                WHERE m.id = :id AND m.receptor_id = :uid
            ");
            $stmtMsg->execute(['id' => $msgId, 'uid' => $uid]);
            $msgActivo = $stmtMsg->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($msgActivo && !$msgActivo['leido']) {
                $this->db->prepare("UPDATE mensaje SET leido=1 WHERE id=:id")->execute(['id' => $msgId]);
            }
        }

        $pageTitle = 'Buzón de entrada';
        require_once __DIR__ . '/../views/buzon/index.php';
    }

    private function apiBandeja()
    {
        header('Content-Type: application/json');

        $tab = $_GET['tab'] ?? 'recibidos';
        $uid = $this->session['usuario_id'];

        if ($tab === 'recibidos') {
            $where = 'm.receptor_id = :uid';
        } elseif ($tab === 'enviados') {
            $where = 'm.emisor_id = :uid';
        } else {
            echo json_encode([]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT
                m.id, m.asunto, SUBSTR(m.cuerpo, 1, 120) AS resumen, m.leido,
                m.enviado_en, m.reply_to_id, m.hilo_id,
                m.emisor_id, e.nombre AS emisor_nombre,
                m.receptor_id, r.nombre AS receptor_nombre
            FROM mensaje m
            JOIN usuario e ON e.id = m.emisor_id
            JOIN usuario r ON r.id = m.receptor_id
            WHERE $where
            ORDER BY m.enviado_en DESC
            LIMIT 50
        ");
        $stmt->execute(['uid' => $uid]);
        $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($mensajes);
    }

    private function apiMensaje()
    {
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $uid = $this->session['usuario_id'];

        $stmt = $this->db->prepare("
            SELECT m.*, e.nombre AS emisor_nombre, r.nombre AS receptor_nombre
            FROM mensaje m
            JOIN usuario e ON e.id = m.emisor_id
            JOIN usuario r ON r.id = m.receptor_id
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $mensaje = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mensaje || ($mensaje['emisor_id'] != $uid && $mensaje['receptor_id'] != $uid)) {
            http_response_code(403);
            echo json_encode(['error' => 'no autorizado']);
            return;
        }

        if ($mensaje['receptor_id'] == $uid && $mensaje['leido'] == 0) {
            $stmt = $this->db->prepare("UPDATE mensaje SET leido = 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }

        echo json_encode($mensaje);
    }

    private function apiEnviar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'método no permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $receptor_id = $input['receptor_id'] ?? null;
        $asunto = trim($input['asunto'] ?? '');
        $cuerpo = trim($input['cuerpo'] ?? '');
        $reply_to_id = $input['reply_to_id'] ?? null;
        $uid = $this->session['usuario_id'];

        if (empty($receptor_id) || empty($cuerpo) || strlen($asunto) > 150) {
            http_response_code(400);
            echo json_encode(['error' => 'datos inválidos']);
            return;
        }

        // Validar receptor existe y no es el mismo usuario
        $stmt = $this->db->prepare("SELECT id FROM usuario WHERE id = :rid AND id != :uid");
        $stmt->execute(['rid' => $receptor_id, 'uid' => $uid]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'receptor inválido']);
            return;
        }

        // Calcular hilo_id
        $hilo_id = null;
        if ($reply_to_id) {
            $stmt = $this->db->prepare("SELECT hilo_id FROM mensaje WHERE id = :rid");
            $stmt->execute(['rid' => $reply_to_id]);
            $padre = $stmt->fetch(PDO::FETCH_ASSOC);
            $hilo_id = $padre ? ($padre['hilo_id'] ?: $reply_to_id) : null;
        }

        $stmt = $this->db->prepare("
            INSERT INTO mensaje (emisor_id, receptor_id, asunto, cuerpo, reply_to_id, hilo_id)
            VALUES (:emisor_id, :receptor_id, :asunto, :cuerpo, :reply_to_id, :hilo_id)
        ");
        $stmt->execute([
            'emisor_id' => $uid,
            'receptor_id' => $receptor_id,
            'asunto' => $asunto,
            'cuerpo' => $cuerpo,
            'reply_to_id' => $reply_to_id,
            'hilo_id' => $hilo_id
        ]);

        $nuevo_id = $this->db->lastInsertId();

        echo json_encode(['ok' => true, 'id' => $nuevo_id]);
    }

    private function apiMarcarLeido()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'método no permitido']);
            return;
        }

        $id = $_POST['id'] ?? null;
        $uid = $this->session['usuario_id'];

        $stmt = $this->db->prepare("UPDATE mensaje SET leido = 1 WHERE id = :id AND receptor_id = :uid");
        $stmt->execute(['id' => $id, 'uid' => $uid]);

        echo json_encode(['ok' => true]);
    }

    private function apiNoLeidos()
    {
        header('Content-Type: application/json');

        $uid = $this->session['usuario_id'];

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM mensaje WHERE receptor_id = :uid AND leido = 0");
        $stmt->execute(['uid' => $uid]);
        $count = $stmt->fetchColumn();

        echo json_encode(['count' => (int)$count]);
    }

    private function apiAdmins()
    {
        header('Content-Type: application/json');

        $uid = $this->session['usuario_id'];

        $stmt = $this->db->prepare("
            SELECT id, nombre, email FROM usuario
            WHERE rol IN ('ADMINISTRADOR', 'MODERADOR') AND id != :uid
            ORDER BY nombre
        ");
        $stmt->execute(['uid' => $uid]);
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($admins);
    }

    private function apiCrearIncidencia()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['error' => 'método no permitido']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $asunto = trim($data['asunto'] ?? '');
        $cuerpo = trim($data['cuerpo'] ?? '');
        $uid = (int)$this->session['usuario_id'];

        if ($asunto === '' || strlen($asunto) > 200 || $cuerpo === '') {
            http_response_code(400);
            echo json_encode(['error' => 'asunto o cuerpo inválidos']);
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO incidencia (usuario_id, asunto, cuerpo, estado, prioridad, creado_en) VALUES (:uid, :asunto, :cuerpo, 'abierta', 'normal', datetime('now'))"
        );
        $stmt->execute(['uid' => $uid, 'asunto' => $asunto, 'cuerpo' => $cuerpo]);

        echo json_encode(['ok' => true, 'id' => (int)$this->db->lastInsertId()]);
    }

    private function apiMisIncidencias()
    {
        header('Content-Type: application/json');

        $uid = (int)$this->session['usuario_id'];

        $stmt = $this->db->prepare("
            SELECT i.id, i.asunto, i.estado, i.prioridad, i.creado_en,
                   a.nombre AS nombre_asignado,
                   (SELECT COUNT(*) FROM incidencia_respuesta r WHERE r.incidencia_id = i.id) AS num_respuestas
            FROM incidencia i
            LEFT JOIN usuario a ON a.id = i.asignado_a
            WHERE i.usuario_id = :uid
            ORDER BY i.creado_en DESC
            LIMIT 20
        ");
        $stmt->execute(['uid' => $uid]);
        $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($incidencias);
    }

    private function apiMiIncidenciaDetalle()
    {
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $uid = (int)$this->session['usuario_id'];

        $stmt = $this->db->prepare("
            SELECT i.*, a.nombre AS nombre_asignado
            FROM incidencia i
            LEFT JOIN usuario a ON a.id = i.asignado_a
            WHERE i.id = :id AND i.usuario_id = :uid
        ");
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$incidencia) {
            http_response_code(403);
            echo json_encode(['error' => 'no autorizado']);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT r.mensaje, r.creado_en, u.nombre AS autor, u.rol
            FROM incidencia_respuesta r
            JOIN usuario u ON u.id = r.usuario_id
            WHERE r.incidencia_id = :id
            ORDER BY r.creado_en ASC
        ");
        $stmt->execute(['id' => $id]);
        $respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'incidencia' => $incidencia, 'respuestas' => $respuestas]);
    }
}
