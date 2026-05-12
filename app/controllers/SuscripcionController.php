<?php

/**
 * Controlador de suscripciones.
 *
 * Gestiona las páginas relacionadas con los planes de suscripción
 * disponibles en la plataforma.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

class SuscripcionController
{
    /**
     * Muestra la página de planes de suscripción.
     *
     * Si el usuario está logueado, carga también su plan activo
     * para mostrarlo en la vista.
     *
     * @return void
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $planActivo = null;

        if (!empty($_SESSION['usuario_id'])) {
            $database = new Database();
            $conexion = $database->connect();
            $stmt = $conexion->prepare("SELECT plan FROM suscripcion WHERE usuario_id = ? AND status = 'activa' LIMIT 1");
            $stmt->execute([(int)$_SESSION['usuario_id']]);
            $suscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
            $planActivo = $suscripcion['plan'] ?? null;
            $_SESSION['usuario_plan'] = $planActivo;
        }

        $okMsg = $_SESSION['suscripcion_ok'] ?? '';
        unset($_SESSION['suscripcion_ok']);

        $pageTitle = "Suscripciones";
        require __DIR__ . '/../views/suscripciones/index.php';
    }

    /**
     * Inicia el proceso de pago para una suscripción.
     *
     * Si hay clave de Stripe, crea una sesión de pago recurrente y redirige.
     * Si no hay clave configurada, activa el plan directamente (modo simulado)
     * y redirige a la página de confirmación.
     */
    public function iniciarPago()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $plan = trim($_POST['plan'] ?? '');
        $planesValidos = ['curso_individual', 'plan_estudiantes', 'plan_empresas'];

        if (!in_array($plan, $planesValidos)) {
            header("Location: " . BASE_URL . "/index.php?url=suscripciones");
            exit;
        }

        $preciosCentimos = [
            'curso_individual' => 0,
            'plan_estudiantes' => 1999,
            'plan_empresas'    => 4999,
        ];

        $stripeSecret = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';

        if ($stripeSecret === '' || $plan === 'curso_individual') {
            // Modo simulado: activar directamente
            $this->activarPlan($plan);
            header("Location: " . BASE_URL . "/index.php?url=suscripcion-ok&plan=" . urlencode($plan) . "&simulado=1");
            exit;
        }

        // Modo Stripe — crear sesión de suscripción
        require_once __DIR__ . '/../../vendor/autoload.php';
        \Stripe\Stripe::setApiKey($stripeSecret);

        $nombres = [
            'plan_estudiantes' => 'Plan Estudiantes · MatrixCoders',
            'plan_empresas'    => 'Plan Empresas · MatrixCoders',
        ];

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode'                 => 'subscription',
            'line_items'           => [[
                'price_data' => [
                    'currency'    => 'eur',
                    'product_data' => ['name' => $nombres[$plan]],
                    'unit_amount' => $preciosCentimos[$plan],
                    'recurring'   => ['interval' => 'month'],
                ],
                'quantity' => 1,
            ]],
            'success_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
                             . BASE_URL . '/index.php?url=suscripcion-ok&plan=' . urlencode($plan) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
                             . BASE_URL . '/index.php?url=suscripciones',
            'metadata'    => ['usuario_id' => (string)$_SESSION['usuario_id'], 'plan' => $plan],
            'locale'      => 'es',
        ]);

        header('Location: ' . $session->url);
        exit;
    }

    /**
     * Página de confirmación tras contratar una suscripción.
     *
     * En modo simulado activa el plan directamente.
     * Con Stripe verifica la sesión antes de activar.
     */
    public function pagoOk()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $plan = trim($_GET['plan'] ?? '');
        $planesValidos = ['curso_individual', 'plan_estudiantes', 'plan_empresas'];

        if (!empty($_GET['simulado'])) {
            if (in_array($plan, $planesValidos)) {
                $this->activarPlan($plan);
            }
            $pageTitle = 'Suscripción activada';
            require __DIR__ . '/../views/suscripciones/pago_ok.php';
            return;
        }

        // Verificación Stripe
        $stripeSecret = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
        if ($stripeSecret !== '' && !empty($_GET['session_id'])) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            \Stripe\Stripe::setApiKey($stripeSecret);
            try {
                $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);
                if ($session->payment_status === 'paid' || $session->status === 'complete') {
                    $planStripe = $session->metadata->plan ?? $plan;
                    if (in_array($planStripe, $planesValidos)) {
                        $this->activarPlan($planStripe);
                        $plan = $planStripe;
                    }
                }
            } catch (\Exception $e) { /* ignorar */ }
        }

        $pageTitle = 'Suscripción activada';
        require __DIR__ . '/../views/suscripciones/pago_ok.php';
    }

    // ── Helpers privados ──────────────────────────────────────────────

    private function activarPlan(string $plan): void
    {
        $database   = new Database();
        $conexion   = $database->connect();
        $usuario_id = (int)$_SESSION['usuario_id'];

        $stmt = $conexion->prepare("SELECT id FROM suscripcion WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuario_id]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $stmt = $conexion->prepare("UPDATE suscripcion SET plan = ?, status = 'activa' WHERE usuario_id = ?");
            $stmt->execute([$plan, $usuario_id]);
        } else {
            $stmt = $conexion->prepare("INSERT INTO suscripcion (usuario_id, plan, status) VALUES (?, ?, 'activa')");
            $stmt->execute([$usuario_id, $plan]);
        }

        $_SESSION['usuario_plan'] = $plan;
    }

    /**
     * Guarda el plan elegido por el usuario en la tabla suscripcion (activación directa sin pago).
     * Mantenido por compatibilidad con la ruta doSuscripcion.
     */
    public function contratar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $plan = trim($_POST['plan'] ?? '');
        $planesValidos = ['curso_individual', 'plan_estudiantes', 'plan_empresas'];

        if (!in_array($plan, $planesValidos)) {
            header("Location: " . BASE_URL . "/index.php?url=suscripciones");
            exit;
        }

        $this->activarPlan($plan);
        $_SESSION['suscripcion_ok'] = "Plan activado correctamente.";

        header("Location: " . BASE_URL . "/index.php?url=suscripciones");
        exit;
    }
}
