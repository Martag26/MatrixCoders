<?php
/**
 * Matrix CRM — Standalone entry point
 * Accessible at: /matrixcoders/admin/index.php
 */

define('CRM_STANDALONE', true);

/* ── Bootstrap ─────────────────────────────────────────────── */
$appRoot = dirname(__DIR__) . '/app';

require_once $appRoot . '/config.php';   // defines BASE_URL, GEMINI_API_KEY …
require_once $appRoot . '/db.php';       // Database class

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ── Logout ─────────────────────────────────────────────────── */
if (isset($_GET['auth']) && $_GET['auth'] === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: /matrixcoders/admin/index.php');
    exit;
}

/* ── Login POST ─────────────────────────────────────────────── */
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['crm_login'])) {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $loginError = 'Por favor completa todos los campos.';
    } else {
        try {
            $db   = (new Database())->connect();
            $stmt = $db->prepare('SELECT * FROM usuario WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $loginError = 'Credenciales incorrectas.';
            } elseif (!password_verify($password, $user['contraseña'] ?? '')) {
                $loginError = 'Credenciales incorrectas.';
            } else {
                $rol          = $user['rol']          ?? 'USUARIO';
                $esAdmin      = ($rol === 'ADMINISTRADOR');
                $esModerador  = ($rol === 'MODERADOR');
                $esInstructor = ($rol === 'INSTRUCTOR');

                if (!$esAdmin && !$esModerador && !$esInstructor) {
                    $loginError = 'No tienes permisos para acceder al CRM.';
                } else {
                    $_SESSION['usuario_id']     = $user['id'];
                    $_SESSION['usuario_nombre'] = $user['nombre'];
                    $_SESSION['usuario_rol']    = $rol;
                    session_regenerate_id(true);
                    header('Location: /matrixcoders/admin/index.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            $loginError = 'Error de conexión. Inténtalo de nuevo.';
        }
    }
}

/* ── Show login if not authenticated or access denied ──────── */
if (empty($_SESSION['usuario_id'])) {
    if (isset($_GET['error']) && $_GET['error'] === 'acceso') {
        $loginError = 'Tu cuenta no tiene permisos para acceder al CRM.';
    }
    require_once $appRoot . '/views/crm/login.php';
    exit;
}

/* ── Load CRM controller ────────────────────────────────────── */
require_once $appRoot . '/controllers/CrmController.php';

$crm = new CrmController();

/* ── API requests ───────────────────────────────────────────── */
if (isset($_GET['crm_api'])) {
    $crm->api();
    exit;
}

/* ── Normal page request ────────────────────────────────────── */
$crm->index();
