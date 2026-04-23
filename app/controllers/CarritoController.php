<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class CarritoController
{
    // ── Página principal del carrito ─────────────────────────────────
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $db          = (new Database())->connect();
        $usuario_id  = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
        $carrito     = $this->normalizarCarrito();
        $cursos_carrito = $this->obtenerCursosPorIds($db, array_keys($carrito));
        $ya_matriculados = $usuario_id
            ? $this->obtenerMatriculados($db, $usuario_id, array_column($cursos_carrito, 'id'))
            : [];

        // Descuentos de campaña activos para ítems del carrito
        $discounts = $this->fetchDiscounts($db, array_keys($carrito));

        $subtotalOriginal = 0.0;
        $subtotalFinal    = 0.0;
        foreach ($cursos_carrito as $c) {
            if (isset($ya_matriculados[$c['id']])) continue;
            $precio      = (float)($c['precio'] ?? 0);
            $pct         = $discounts[$c['id']] ?? 0;
            $precioFinal = $pct > 0 ? round($precio * (1 - $pct / 100), 2) : $precio;
            $subtotalOriginal += $precio;
            $subtotalFinal    += $precioFinal;
        }

        $ahorro   = round($subtotalOriginal - $subtotalFinal, 2);
        $iva      = round($subtotalFinal * 0.21, 2);
        $total    = round($subtotalFinal + $iva, 2);
        // Mantener variables heredadas para compatibilidad con la vista
        $subtotal = $subtotalFinal;

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

        $db    = (new Database())->connect();
        $curso = $this->obtenerCurso($db, $curso_id);
        if (!$curso) {
            echo json_encode(['ok' => false, 'estado' => 'no_existe', 'mensaje' => 'El curso ya no está disponible.']);
            exit;
        }

        if (!empty($_SESSION['usuario_id'])) {
            $s = $db->prepare("SELECT 1 FROM matricula WHERE usuario_id=? AND curso_id=?");
            $s->execute([$_SESSION['usuario_id'], $curso_id]);
            if ($s->fetchColumn()) {
                echo json_encode([
                    'ok'     => false,
                    'estado' => 'matriculado',
                    'mensaje'=> 'Ya estás matriculado en este curso.',
                    'total'  => count($_SESSION['carrito'] ?? []),
                ]);
                exit;
            }
        }

        if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

        if (isset($_SESSION['carrito'][$curso_id])) {
            echo json_encode([
                'ok'     => false,
                'estado' => 'ya_en_carrito',
                'mensaje'=> 'Este curso ya está en tu cesta.',
                'total'  => count($_SESSION['carrito']),
            ]);
            exit;
        }

        $_SESSION['carrito'][$curso_id] = 1;

        // Devolver también info de descuento para mostrar en el badge
        $discounts = $this->fetchDiscounts($db, [$curso_id]);
        $descuento = $discounts[$curso_id] ?? 0;
        $precio    = (float)($curso['precio'] ?? 0);
        $precioFinal = $descuento > 0 ? round($precio * (1 - $descuento / 100), 2) : $precio;

        echo json_encode([
            'ok'          => true,
            'estado'      => 'añadido',
            'mensaje'     => 'Curso añadido a la cesta.',
            'total'       => count($_SESSION['carrito']),
            'descuento'   => $descuento,
            'precio'      => $precio,
            'precioFinal' => $precioFinal,
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

        $db              = (new Database())->connect();
        $carrito         = $this->normalizarCarrito();
        $cursos          = $this->obtenerCursosPorIds($db, array_keys($carrito));
        $usuario_id      = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
        $ya_matriculados = $usuario_id
            ? $this->obtenerMatriculados($db, $usuario_id, array_column($cursos, 'id'))
            : [];
        $discounts = $this->fetchDiscounts($db, array_keys($carrito));

        $subtotalOriginal = 0.0;
        $subtotalFinal    = 0.0;
        $cantidadValida   = 0;
        foreach ($cursos as $c) {
            if (isset($ya_matriculados[$c['id']])) continue;
            $precio      = (float)($c['precio'] ?? 0);
            $pct         = $discounts[$c['id']] ?? 0;
            $precioFinal = $pct > 0 ? round($precio * (1 - $pct / 100), 2) : $precio;
            $subtotalOriginal += $precio;
            $subtotalFinal    += $precioFinal;
            $cantidadValida++;
        }

        $ahorro = round($subtotalOriginal - $subtotalFinal, 2);
        $iva    = round($subtotalFinal * 0.21, 2);
        $total  = round($subtotalFinal + $iva, 2);

        echo json_encode([
            'ok'               => true,
            'cantidad'         => count($_SESSION['carrito'] ?? []),
            'cantidad_valida'  => $cantidadValida,
            'subtotal_fmt'     => number_format($subtotalOriginal, 2),
            'ahorro_fmt'       => number_format($ahorro, 2),
            'tiene_descuento'  => $ahorro > 0,
            'iva_fmt'          => number_format($iva, 2),
            'total_fmt'        => number_format($total, 2),
        ]);
        exit;
    }

    // ── Crear sesión de Stripe y redirigir ───────────────────────────
    public function checkout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/index.php?url=login&retorno=carrito');
            exit;
        }

        $db      = (new Database())->connect();
        $carrito = $this->normalizarCarrito();
        if (empty($carrito)) {
            $this->setFlash('Tu cesta está vacía.', 'info');
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }

        $cursos         = $this->obtenerCursosPorIds($db, array_keys($carrito));
        $yaMatriculados = $this->obtenerMatriculados($db, (int)$_SESSION['usuario_id'], array_column($cursos, 'id'));
        $discounts      = $this->fetchDiscounts($db, array_keys($carrito));

        $lineItems      = [];
        $cursoIdsValidos = [];
        $cursosGratis   = [];
        $totalDescuento = 0.0;

        foreach ($cursos as $c) {
            if (isset($yaMatriculados[$c['id']])) continue;

            $cursoIdsValidos[] = (int)$c['id'];
            $precio      = (float)($c['precio'] ?? 0);
            $pct         = $discounts[$c['id']] ?? 0;
            $precioFinal = $pct > 0 ? round($precio * (1 - $pct / 100), 2) : $precio;
            $totalDescuento += ($precio - $precioFinal);

            if ($precioFinal <= 0) {
                $cursosGratis[] = (int)$c['id'];
                continue;
            }

            $productData = ['name' => $c['titulo']];
            if ($pct > 0) {
                $productData['description'] = "Descuento {$pct}% aplicado · Precio original " . number_format($precio, 2) . '€';
            }
            if (!empty($c['imagen'])) {
                $imageUrl = $this->absoluteBaseUrl() . BASE_URL . '/img/' . $c['imagen'];
                $productData['images'] = [$imageUrl];
            }

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => $productData,
                    'unit_amount'  => (int)round($precioFinal * 1.21 * 100),
                ],
                'quantity' => 1,
            ];
        }

        if (empty($cursoIdsValidos)) {
            $this->setFlash('Los cursos de tu cesta ya están asociados a tu cuenta.', 'info');
            header('Location: ' . BASE_URL . '/index.php?url=carrito');
            exit;
        }

        $stripeSecret = $this->stripeSecretKey();
        if ($stripeSecret === '') {
            // Modo sin Stripe: matriculamos directamente
            $this->matricularCursos($db, (int)$_SESSION['usuario_id'], $cursoIdsValidos);
            $this->retirarCursosDelCarrito($cursoIdsValidos);
            header('Location: ' . BASE_URL . '/index.php?url=pago-ok&simulado=1&ids=' . implode(',', $cursoIdsValidos));
            exit;
        }

        require_once __DIR__ . '/../../vendor/autoload.php';
        \Stripe\Stripe::setApiKey($stripeSecret);

        if (empty($lineItems)) {
            // Todos gratuitos
            $this->matricularCursos($db, (int)$_SESSION['usuario_id'], $cursoIdsValidos);
            $this->retirarCursosDelCarrito($cursoIdsValidos);
            header('Location: ' . BASE_URL . '/index.php?url=pago-ok&gratis=1&ids=' . implode(',', $cursoIdsValidos));
            exit;
        }

        $metaDescuento = $totalDescuento > 0
            ? number_format($totalDescuento, 2) . ' EUR'
            : '0';

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $this->absoluteBaseUrl() . BASE_URL . '/index.php?url=pago-ok&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $this->absoluteBaseUrl() . BASE_URL . '/index.php?url=carrito',
            'metadata'             => [
                'usuario_id'     => (string)$_SESSION['usuario_id'],
                'curso_ids'      => implode(',', $cursoIdsValidos),
                'cursos_gratis'  => implode(',', $cursosGratis),
                'ahorro_total'   => $metaDescuento,
            ],
            'locale' => 'es',
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

        $cursosComprados = [];

        if (!empty($_GET['simulado']) || !empty($_GET['gratis'])) {
            // Los IDs vienen por GET al redirigir sin Stripe
            $ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')));
            if (!empty($ids)) {
                $db = (new Database())->connect();
                $cursosComprados = $this->obtenerCursosPorIds($db, $ids);
            }
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

        if (
            $stripeSession->payment_status === 'paid'
            && (int)($stripeSession->metadata->usuario_id ?? 0) === (int)$usuario_id
        ) {
            $db  = (new Database())->connect();
            $ids = array_filter(array_map('intval', explode(',', (string)($stripeSession->metadata->curso_ids ?? ''))));
            $this->matricularCursos($db, (int)$usuario_id, $ids);
            $this->retirarCursosDelCarrito($ids);
            $cursosComprados = $this->obtenerCursosPorIds($db, $ids);
        }

        require __DIR__ . '/../views/carrito/pago_ok.php';
    }

    // ── Webhook de Stripe ─────────────────────────────────────────────
    public function webhook()
    {
        $payload       = (string)file_get_contents('php://input');
        $sigHeader     = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $webhookSecret = trim((string)(getenv('STRIPE_WEBHOOK_SECRET') ?: ($_ENV['STRIPE_WEBHOOK_SECRET'] ?? '')));
        $stripeSecret  = $this->stripeSecretKey();

        if ($stripeSecret === '') {
            http_response_code(200);
            exit;
        }

        require_once __DIR__ . '/../../vendor/autoload.php';
        \Stripe\Stripe::setApiKey($stripeSecret);

        try {
            if ($webhookSecret !== '') {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                $data  = json_decode($payload, true);
                if (!$data) throw new \UnexpectedValueException('Invalid JSON');
                $event = \Stripe\Event::constructFrom($data);
            }
        } catch (\Exception $e) {
            http_response_code(400);
            exit;
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;
            if ($session->payment_status === 'paid') {
                $usuarioId = (int)($session->metadata->usuario_id ?? 0);
                $ids       = array_filter(array_map('intval', explode(',', (string)($session->metadata->curso_ids ?? ''))));
                if ($usuarioId > 0 && !empty($ids)) {
                    $db = (new Database())->connect();
                    $this->matricularCursos($db, $usuarioId, $ids);
                }
            }
        }

        http_response_code(200);
        exit;
    }

    // ── Helpers privados ─────────────────────────────────────────────

    private function normalizarCarrito(): array
    {
        $carrito     = $_SESSION['carrito'] ?? [];
        $normalizado = [];
        foreach (array_keys($carrito) as $cursoId) {
            $id = (int)$cursoId;
            if ($id > 0) $normalizado[$id] = 1;
        }
        $_SESSION['carrito'] = $normalizado;
        return $normalizado;
    }

    /** Devuelve [curso_id => descuento_pct] para los ítems con campaña activa */
    private function fetchDiscounts(PDO $db, array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($ids)) return [];
        $ph = implode(',', array_fill(0, count($ids), '?'));
        try {
            $stmt = $db->prepare("
                SELECT cc.curso_id, cc.descuento
                FROM campana_curso cc
                JOIN campana_crm cm ON cm.id = cc.campana_id
                WHERE cc.curso_id IN ($ph)
                  AND cm.activa = 1
                  AND (cm.fecha_fin IS NULL OR cm.fecha_fin >= date('now'))
                GROUP BY cc.curso_id
            ");
            $stmt->execute($ids);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function obtenerCurso(PDO $db, int $cursoId): ?array
    {
        $stmt = $db->prepare("SELECT * FROM curso WHERE id = ? LIMIT 1");
        $stmt->execute([$cursoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function obtenerCursosPorIds(PDO $db, array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($ids)) return [];
        $ph   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT * FROM curso WHERE id IN ($ph) ORDER BY id DESC");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerMatriculados(PDO $db, int $usuarioId, array $cursoIds): array
    {
        $cursoIds = array_values(array_filter(array_map('intval', $cursoIds), fn($id) => $id > 0));
        if ($usuarioId <= 0 || empty($cursoIds)) return [];
        $ph     = implode(',', array_fill(0, count($cursoIds), '?'));
        $params = array_merge([$usuarioId], $cursoIds);
        $stmt   = $db->prepare("
            SELECT curso_id FROM matricula
            WHERE usuario_id = ? AND curso_id IN ($ph) AND estado = 'activa'
        ");
        $stmt->execute($params);
        return array_fill_keys(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
    }

    private function matricularCursos(PDO $db, int $usuarioId, array $cursoIds): void
    {
        $cursoIds = array_values(array_filter(array_map('intval', $cursoIds), fn($id) => $id > 0));
        if ($usuarioId <= 0 || empty($cursoIds)) return;
        $stmt = $db->prepare("
            INSERT OR IGNORE INTO matricula (usuario_id, curso_id, fecha, estado)
            VALUES (?, ?, datetime('now'), 'activa')
        ");
        foreach ($cursoIds as $id) {
            $stmt->execute([$usuarioId, $id]);
        }
    }

    private function retirarCursosDelCarrito(array $cursoIds): void
    {
        foreach ($cursoIds as $id) {
            unset($_SESSION['carrito'][(int)$id]);
        }
    }

    private function setFlash(string $mensaje, string $tipo = 'info'): void
    {
        $_SESSION['carrito_flash'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
    }

    private function stripeSecretKey(): string
    {
        return trim((string)(getenv('STRIPE_SECRET_KEY') ?: ($_ENV['STRIPE_SECRET_KEY'] ?? '')));
    }

    private function absoluteBaseUrl(): string
    {
        $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
