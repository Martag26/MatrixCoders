<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (empty($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$accion    = $_POST['accion'] ?? '';
$db        = (new Database())->connect();

// ── Vincular: recibe el JWT de Google Identity Services ───────────
if ($accion === 'vincular') {
    $credential = trim($_POST['credential'] ?? '');
    if (!$credential) {
        echo json_encode(['ok' => false, 'error' => 'Token de Google no recibido']);
        exit;
    }

    // Decodificar el payload del JWT (base64url → JSON)
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        echo json_encode(['ok' => false, 'error' => 'Token con formato incorrecto']);
        exit;
    }

    $padded  = str_pad(strtr($parts[1], '-_', '+/'), (int)ceil(strlen($parts[1]) / 4) * 4, '=');
    $payload = json_decode(base64_decode($padded), true);

    if (!$payload || empty($payload['sub'])) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo leer el token de Google']);
        exit;
    }

    $googleId     = $payload['sub'];
    $googleEmail  = $payload['email']   ?? '';
    $googleNombre = $payload['name']    ?? '';

    $stmt = $db->prepare("
        INSERT OR REPLACE INTO usuario_google (usuario_id, google_id, google_email, google_nombre, vinculado_en)
        VALUES (?, ?, ?, ?, datetime('now'))
    ");
    $stmt->execute([$usuarioId, $googleId, $googleEmail, $googleNombre]);

    echo json_encode(['ok' => true, 'email' => $googleEmail, 'nombre' => $googleNombre]);
    exit;
}

// ── Desvincular ──────────────────────────────────────────────────
if ($accion === 'desvincular') {
    $stmt = $db->prepare("DELETE FROM usuario_google WHERE usuario_id = ?");
    $stmt->execute([$usuarioId]);
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Acción desconocida']);
