<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/UsuarioPreferencias.php';

if (session_status() === PHP_SESSION_NONE) session_start();

class PerfilController
{
    public function cambiarPerfil(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        $usuario_id = (int)$_SESSION['usuario_id'];
        $raw        = file_get_contents('php://input');
        $data       = json_decode($raw, true) ?: $_POST;
        $perfil     = $data['perfil'] ?? '';

        if (!in_array($perfil, ['principiante', 'estudiante', 'trabajador'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Perfil inválido']);
            return;
        }

        $db    = (new Database())->connect();
        $model = new UsuarioPreferencias($db);
        $ok    = $model->upsert($usuario_id, ['perfil' => $perfil]);

        if ($ok) {
            $_SESSION['usuario_perfil'] = $perfil;
        }

        echo json_encode(['ok' => $ok, 'perfil' => $perfil]);
    }
}

$controller = new PerfilController();
$controller->cambiarPerfil();
