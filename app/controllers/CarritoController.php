<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class CarritoController
{
    // ── Página principal del carrito ─────────────────────────────────
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $db = (new Database())->connect();
        $usuario_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
        $carrito = $this->normalizarCarrito();
        $cursos_carrito = $this->obtenerCursosPorIds($db, array_keys($carrito));
        $ya_matriculados = $usuario_id ? $this->obtenerMatriculados($db, $usuario_id, array_column($cursos_carrito, 'id')) : [];

        $subtotal = 0.0;
        foreach ($cursos_carrito as $curso) {
            if (!isset($ya_matriculados[$curso['id']])) {
                $subtotal += (float)($curso['precio'] ?? 0);
            }
        }

        $iva      = round($subtotal * 0.21, 2);
        $total    = round($subtotal + $iva, 2);
        $flash = $_SESSION['carrito_flash'] ?? null;
        unset($_SESSION['carrito_flash']);

        require __DIR__ . '/../views/carrito/index.php';
    }

    // ── Añadir curso al carrito ───────────────────────────────────────
    public function añadir()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $curso_id = (int)($_POST['curso_id'] ?? 0);
        if ($curso_id <= 0) {
            echo json_encode(['ok' => false, 'estado' => 'invalido', 'mensaje' => 'Curso no válido.']);
            exit;
        }

        $db = (new Database())->connect();
        $curso = $this->obtenerCurso($db, $curso_id);
        if (!$curso) {
            echo json_encode(['ok' => false, 'estado' => 'no_existe', 'mensaje' => 'El curso ya no está disponible.']);
            exit;
        }

        // Comprobar si ya está matriculado
        if (!empty($_SESSION['usuario_id'])) {
            $s = $db->prepare("SELECT 1 FROM matricula WHERE usuario_id=? AND curso_id=?");
            $s->execute([$_SESSION['usuario_id'], $curso_id]);
            if ($s->fetchColumn()) {
                echo json_encode([
                    'ok' => false,
                    'estado' => 'matriculado',
                    'mensaje' => 'Ya estás matriculado en este curso.',
                    'total' => count($_SESSION['carrito'] ?? []),
                ]);
                exit;
            }
        }

        if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

        $yaEnCarrito = isset($_SESSION['carrito'][$curso_id]);
        if ($yaEnCarrito) {
            echo json_encode([
                'ok' => false,
                'estado' => 'ya_en_carrito',
                'mensaje' => 'Este curso ya está en tu cesta.',
                'total' => count($_SESSION['carrito']),
            ]);
            exit;
        }

        $_SESSION['carrito'][$curso_id] = 1;

        echo json_encode([
            'ok'          => true,
            'estado'      => 'añadido',
            'mensaje'     => 'Curso añadido a la cesta.',
            'total'       => count($_SESSION['carrito']),
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
        $db = (new Database())->connect();
        $carrito = $this->normalizarCarrito();
        $cursos = $this->obtenerCursosPorIds($db, array_keys($carrito));
        $usuario_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
        $ya_matriculados = $usuario_id ? $this->obtenerMatriculados($db, $usuario_id, array_column($cursos, 'id')) : [];
        $subtotal = 0.0;
        $cantidadValida = 0;
        foreach ($cursos as $curso) {
            if (isset($ya_matriculados[$curso['id']])) {
                continue;
            }
            $subtotal += (float)($curso['precio'] ?? 0);
            $cantidadValida++;
        }

        $iva   = round($subtotal * 0.21, 2);
        $total = round($subtotal + $iva, 2);

        echo json_encode([
            'ok'            => true,
            'cantidad'      => count($_SESSION['carrito'] ?? []),
            'cantidad_valida' => $cantidadValida,
            'subtotal_fmt'  => number_format($subtotal, 2),
            'iva_fmt'       => number_format($iva, 2),
            'total_fmt'     => number_format($total, 2),
        ]);
        exit;
    }

    public function checkout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login&retorno=carrito');
            exit;
        }

        $db = (new Database())->connect();
        $carrito = $this->normalizarCarrito();
        if (empty($carrito)) {
            $this->setFlash('Tu cesta está vacía.');
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }

        $cursos = $this->obtenerCursosPorIds($db, array_keys($carrito));
        $yaMatriculados = $this->obtenerMatriculados($db, (int)$_SESSION['usuario_id'], array_column($cursos, 'id'));
        $lineItems = [];
        $cursoIdsValidos = [];
        $cursosGratis = [];

        foreach ($cursos as $c) {
            if (isset($yaMatriculados[$c['id']])) {
                continue;
            }

            $cursoIdsValidos[] = (int)$c['id'];
            $precio = (float)($c['precio'] ?? 0);
            if ($precio <= 0) {
                $cursosGratis[] = (int)$c['id'];
                continue;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => ['name' => $c['titulo']],
                    'unit_amount'  => (int)round($precio * 1.21 * 100),
                ],
                'quantity' => 1,
            ];
        }

        if (empty($cursoIdsValidos)) {
            $this->setFlash('Los cursos de tu cesta ya están asociados a tu cuenta.');
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }

        $stripeSecret = $this->stripeSecretKey();
        if ($stripeSecret === '') {
            $this->matricularCursos($db, (int)$_SESSION['usuario_id'], $cursoIdsValidos);
            $this->retirarCursosDelCarrito($cursoIdsValidos);
            header('Location: ' . BASE_URL . '/index.php?url=pago-ok&simulado=1');
            exit;
        }

        require_once __DIR__ . '/../../vendor/autoload.php';
        \Stripe\Stripe::setApiKey($stripeSecret);

        if (empty($lineItems)) {
            $this->matricularCursos($db, (int)$_SESSION['usuario_id'], $cursoIdsValidos);
            $this->retirarCursosDelCarrito($cursoIdsValidos);
            header('Location: ' . BASE_URL . '/index.php?url=pago-ok&gratis=1');
            exit;
        }

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $this->absoluteBaseUrl() . BASE_URL . '/index.php?url=pago-ok&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $this->absoluteBaseUrl() . BASE_URL . '/index.php?url=carrito',
            'metadata'             => [
                'usuario_id'  => (string)$_SESSION['usuario_id'],
                'curso_ids'   => implode(',', $cursoIdsValidos),
                'cursos_gratis' => implode(',', $cursosGratis),
            ],
        ]);

        header('Location: ' . $session->url);
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

        if (!empty($_GET['simulado'])) {
            require __DIR__ . '/../views/carrito/pago_ok.php';
            return;
        }

        if (!empty($_GET['gratis'])) {
            require __DIR__ . '/../views/carrito/pago_ok.php';
            return;
        }

        require_once __DIR__ . '/../../vendor/autoload.php';
        $stripeSecret = $this->stripeSecretKey();
        if ($stripeSecret === '') {
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }
        \Stripe\Stripe::setApiKey($stripeSecret);

        $session_id = $_GET['session_id'] ?? '';
        if (!$session_id) {
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }

        try {
            $stripeSession = \Stripe\Checkout\Session::retrieve($session_id);
        } catch (\Exception $e) {
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }

        // Solo matricular si el pago está completo
        if (
            $stripeSession->payment_status === 'paid'
            && (int)($stripeSession->metadata->usuario_id ?? 0) === (int)$usuario_id
        ) {
            $db = (new Database())->connect();
            $ids = array_filter(array_map('intval', explode(',', (string)($stripeSession->metadata->curso_ids ?? ''))));
            $this->matricularCursos($db, (int)$usuario_id, $ids);
            $this->retirarCursosDelCarrito($ids);
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

    private function normalizarCarrito(): array
    {
        $carrito = $_SESSION['carrito'] ?? [];
        $normalizado = [];
        foreach (array_keys($carrito) as $cursoId) {
            $id = (int)$cursoId;
            if ($id > 0) {
                $normalizado[$id] = 1;
            }
        }
        $_SESSION['carrito'] = $normalizado;
        return $normalizado;
    }

    private function obtenerCurso(PDO $db, int $cursoId): ?array
    {
        $stmt = $db->prepare("SELECT * FROM curso WHERE id = ? LIMIT 1");
        $stmt->execute([$cursoId]);
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        return $curso ?: null;
    }

    private function obtenerCursosPorIds(PDO $db, array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT * FROM curso WHERE id IN ($placeholders) ORDER BY id DESC");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerMatriculados(PDO $db, int $usuarioId, array $cursoIds): array
    {
        $cursoIds = array_values(array_filter(array_map('intval', $cursoIds), fn($id) => $id > 0));
        if ($usuarioId <= 0 || empty($cursoIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cursoIds), '?'));
        $params = array_merge([$usuarioId], $cursoIds);
        $stmt = $db->prepare("
            SELECT curso_id
            FROM matricula
            WHERE usuario_id = ?
              AND curso_id IN ($placeholders)
              AND estado = 'activa'
        ");
        $stmt->execute($params);
        return array_fill_keys(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
    }

    private function matricularCursos(PDO $db, int $usuarioId, array $cursoIds): void
    {
        $cursoIds = array_values(array_filter(array_map('intval', $cursoIds), fn($id) => $id > 0));
        if ($usuarioId <= 0 || empty($cursoIds)) {
            return;
        }

        $stmt = $db->prepare("
            INSERT OR IGNORE INTO matricula (usuario_id, curso_id, fecha, estado)
            VALUES (?, ?, datetime('now'), 'activa')
        ");

        foreach ($cursoIds as $cursoId) {
            $stmt->execute([$usuarioId, $cursoId]);
        }
    }

    private function retirarCursosDelCarrito(array $cursoIds): void
    {
        foreach ($cursoIds as $cursoId) {
            unset($_SESSION['carrito'][(int)$cursoId]);
        }
    }

    private function setFlash(string $mensaje): void
    {
        $_SESSION['carrito_flash'] = $mensaje;
    }

    private function stripeSecretKey(): string
    {
        return trim((string)(getenv('STRIPE_SECRET_KEY') ?: ($_ENV['STRIPE_SECRET_KEY'] ?? '')));
    }

    private function absoluteBaseUrl(): string
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
