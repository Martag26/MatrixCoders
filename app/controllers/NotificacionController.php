<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Notificacion.php';

if (session_status() === PHP_SESSION_NONE) session_start();

class NotificacionController
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
        $model      = new Notificacion($db);
        $action     = $_GET['action'] ?? ($_POST['action'] ?? 'list');

        switch ($action) {
            case 'list':
                $model->sincronizarAutomaticas($usuario_id);
                echo json_encode([
                    'notificaciones' => $model->obtenerRecientes($usuario_id),
                    'no_leidas'      => $model->contarNoLeidas($usuario_id),
                ]);
                break;

            case 'leer':
                $id = (int)($_POST['id'] ?? 0);
                echo json_encode(['ok' => $model->marcarLeida($id, $usuario_id)]);
                break;

            case 'leer-todas':
                echo json_encode(['ok' => $model->marcarTodasLeidas($usuario_id)]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción desconocida']);
        }
    }
}

$controller = new NotificacionController();
$controller->handle();
