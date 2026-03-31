<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class CarritoController
{
    // Muestra la página del carrito
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $database = new Database();
        $db       = $database->connect();

        require __DIR__ . '/../views/carrito/index.php';
    }

    // Añade un curso al carrito (llamada AJAX)
    public function añadir()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        $curso_id = (int)($_POST['curso_id'] ?? 0);

        if ($curso_id > 0) {
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }
            // Solo añadimos si no está ya en el carrito
            if (!isset($_SESSION['carrito'][$curso_id])) {
                $_SESSION['carrito'][$curso_id] = 1;
            }
        }

        echo json_encode([
            'ok'    => true,
            'total' => count($_SESSION['carrito'] ?? []),
        ]);
        exit;
    }

    // Elimina un curso del carrito (llamada AJAX)
    public function eliminar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        header('Content-Type: application/json');

        $curso_id = (int)($_POST['curso_id'] ?? 0);

        if ($curso_id > 0 && isset($_SESSION['carrito'][$curso_id])) {
            unset($_SESSION['carrito'][$curso_id]);
        }

        // Calculamos el nuevo total desde la BD
        $total = 0;
        if (!empty($_SESSION['carrito'])) {
            $database = new Database();
            $db       = $database->connect();
            $ids      = implode(',', array_map('intval', array_keys($_SESSION['carrito'])));
            $stmt     = $db->query("SELECT SUM(precio) as total FROM curso WHERE id IN ($ids)");
            $row      = $stmt->fetch(PDO::FETCH_ASSOC);
            $total    = (float)($row['total'] ?? 0);
        }

        echo json_encode([
            'ok'        => true,
            'cantidad'  => count($_SESSION['carrito'] ?? []),
            'total_fmt' => number_format($total, 2),
        ]);
        exit;
    }
}
