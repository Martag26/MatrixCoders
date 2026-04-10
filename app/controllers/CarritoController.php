<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class CarritoController
{
    // ── Página principal del carrito ─────────────────────────────────
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $database = new Database();
        $db       = $database->connect();

        $usuario_id     = $_SESSION['usuario_id'] ?? null;
        $carrito        = $_SESSION['carrito'] ?? [];
        $cursos_carrito = [];
        $ya_matriculados = [];
        $subtotal       = 0;

        if (!empty($carrito)) {
            $ids  = implode(',', array_map('intval', array_keys($carrito)));
            $stmt = $db->query("SELECT * FROM curso WHERE id IN ($ids) ORDER BY id");
            $cursos_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Detectar cursos en los que el usuario ya está matriculado
            if ($usuario_id) {
                foreach ($cursos_carrito as $c) {
                    $s = $db->prepare("SELECT 1 FROM matricula WHERE usuario_id=? AND curso_id=?");
                    $s->execute([$usuario_id, $c['id']]);
                    if ($s->fetchColumn()) {
                        $ya_matriculados[$c['id']] = true;
                    }
                }
            }

            foreach ($cursos_carrito as $c) {
                if (!isset($ya_matriculados[$c['id']])) {
                    $subtotal += (float)$c['precio'];
                }
            }
        }

        $iva      = round($subtotal * 0.21, 2);
        $total    = round($subtotal + $iva, 2);

        require __DIR__ . '/../views/carrito/index.php';
    }

    // ── Añadir curso al carrito ───────────────────────────────────────
    public function añadir()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $curso_id = (int)($_POST['curso_id'] ?? 0);
        if ($curso_id > 0) {
            if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
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

    // ── Eliminar curso del carrito (AJAX) ────────────────────────────
    public function eliminar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $curso_id = (int)($_POST['curso_id'] ?? 0);
        if ($curso_id > 0 && isset($_SESSION['carrito'][$curso_id])) {
            unset($_SESSION['carrito'][$curso_id]);
        }

        // Recalcular totales
        $subtotal = 0.0;
        if (!empty($_SESSION['carrito'])) {
            $database = new Database();
            $db       = $database->connect();
            $ids      = implode(',', array_map('intval', array_keys($_SESSION['carrito'])));
            $stmt     = $db->query("SELECT SUM(precio) AS s FROM curso WHERE id IN ($ids)");
            $subtotal = (float)($stmt->fetchColumn() ?: 0);
        }

        $iva   = round($subtotal * 0.21, 2);
        $total = round($subtotal + $iva, 2);

        echo json_encode([
            'ok'            => true,
            'cantidad'      => count($_SESSION['carrito'] ?? []),
            'subtotal_fmt'  => number_format($subtotal, 2),
            'iva_fmt'       => number_format($iva, 2),
            'total_fmt'     => number_format($total, 2),
        ]);
        exit;
    }

    public function checkout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // ── Validar login ──
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login&retorno=carrito');
            exit;
        }

        $carrito = $_SESSION['carrito'] ?? [];
        if (empty($carrito)) {
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }

       
        // Simulación temporal hasta tener Stripe instalado
        header('Location: ' . BASE_URL . '/index.php?url=pago-ok');
        exit;
    }

    // ── Página de éxito tras pagar ────────────────────────────────────
    public function pagoOk()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $usuario_id = $_SESSION['usuario_id'] ?? null;
        if (!$usuario_id) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        /* ── Con Stripe Webhook (recomendado) el webhook matricula.
           ── Con simulación, matriculamos aquí directamente: ── */
        $carrito = $_SESSION['carrito'] ?? [];
        if (!empty($carrito)) {
            $database = new Database();
            $db       = $database->connect();
            foreach (array_keys($carrito) as $curso_id) {
                $s = $db->prepare("SELECT 1 FROM matricula WHERE usuario_id=? AND curso_id=?");
                $s->execute([$usuario_id, $curso_id]);
                if (!$s->fetchColumn()) {
                    $db->prepare("INSERT INTO matricula (usuario_id, curso_id, fecha, estado) VALUES (?,?,NOW(),'activa')")
                        ->execute([$usuario_id, $curso_id]);
                }
            }
            $_SESSION['carrito'] = [];
        }

        require __DIR__ . '/../views/carrito/pago_ok.php';
    }

    // ── Webhook de Stripe (POST desde Stripe) ─────────────────────────
    // Registra este endpoint en el dashboard de Stripe:
    // URL: https://tudominio.com/index.php?url=stripe-webhook
    public function webhook()
    {
        http_response_code(200);
        exit;
    }
}
