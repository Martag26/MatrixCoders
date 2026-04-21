<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/EventoUsuario.php';

if (session_status() === PHP_SESSION_NONE) session_start();

class EventoUsuarioController
{
    public function handle(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        $usuario_id = (int)$_SESSION['usuario_id'];
        $db         = (new Database())->connect();
        $model      = new EventoUsuario($db);
        $method     = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            echo json_encode($model->obtenerPorUsuario($usuario_id));
            return;
        }

        $raw    = file_get_contents('php://input');
        $data   = json_decode($raw, true) ?: $_POST;
        $action = $_GET['action'] ?? ($data['action'] ?? '');

        switch ($action) {
            case 'crear':
                if (empty($data['titulo']) || empty($data['fecha_inicio'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos obligatorios']);
                    return;
                }
                $id = $model->crear($usuario_id, $data);
                echo json_encode($id !== false
                    ? ['ok' => true, 'id' => $id]
                    : ['ok' => false, 'error' => 'No se pudo crear el evento']
                );
                break;

            case 'actualizar':
                $id = (int)($data['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID inválido']);
                    return;
                }
                // Merge con el registro existente para soportar actualizaciones parciales
                // (p.ej. drag-and-drop sólo envía fecha_inicio)
                $existing = $model->obtenerPorId($id, $usuario_id);
                if (!$existing) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Evento no encontrado']);
                    return;
                }
                $merged = array_merge($existing, array_filter($data, fn($v) => $v !== null && $v !== ''));
                if (empty($merged['titulo']) || empty($merged['fecha_inicio'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos obligatorios']);
                    return;
                }
                echo json_encode(['ok' => $model->actualizar($id, $usuario_id, $merged)]);
                break;

            case 'eliminar':
                $id = (int)($data['id'] ?? 0);
                echo json_encode(['ok' => $model->eliminar($id, $usuario_id)]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción desconocida']);
        }
    }
}

$controller = new EventoUsuarioController();
$controller->handle();
