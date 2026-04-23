<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/GeminiService.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (empty($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$leccionId = isset($_GET['leccion']) ? (int)$_GET['leccion'] : 0;
$forzar    = ($_GET['forzar'] ?? '0') === '1';

if ($leccionId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Lección no válida']);
    exit;
}

$db = (new Database())->connect();

// Verificar que el usuario está matriculado en el curso de esta lección
$stmtAcceso = $db->prepare("
    SELECT l.video_url, l.titulo
    FROM leccion l
    JOIN unidad u   ON u.id  = l.unidad_id
    JOIN matricula m ON m.curso_id = u.curso_id
    WHERE l.id = ? AND m.usuario_id = ? AND m.estado = 'activa'
    LIMIT 1
");
$stmtAcceso->execute([$leccionId, $usuarioId]);
$leccion = $stmtAcceso->fetch(PDO::FETCH_ASSOC);

if (!$leccion) {
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado a esta lección']);
    exit;
}

// Servir desde caché si existe y no se pide regeneración
if (!$forzar) {
    $stmtCache = $db->prepare("SELECT contenido FROM leccion_apuntes_ia WHERE leccion_id = ?");
    $stmtCache->execute([$leccionId]);
    $cached = $stmtCache->fetchColumn();
    if ($cached) {
        echo json_encode(['ok' => true, 'contenido' => $cached, 'cache' => true]);
        exit;
    }
}

// Extraer el ID de YouTube del video_url
$videoUrl = $leccion['video_url'] ?? '';
preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $m);
$ytId = $m[1] ?? null;

if (!$ytId) {
    echo json_encode(['ok' => false, 'error' => 'Esta lección no tiene un vídeo de YouTube válido para analizar']);
    exit;
}

// Llamar a Gemini API
$gemini    = new GeminiService();
$resultado = $gemini->generarApuntesDesdeYoutube(
    "https://www.youtube.com/watch?v={$ytId}",
    $leccion['titulo']
);

if (!$resultado['ok']) {
    echo json_encode($resultado);
    exit;
}

// Guardar en caché (una sola generación por lección, compartida entre todos los usuarios)
$stmtSave = $db->prepare("
    INSERT OR REPLACE INTO leccion_apuntes_ia (leccion_id, contenido, generado_en)
    VALUES (?, ?, datetime('now'))
");
$stmtSave->execute([$leccionId, $resultado['contenido']]);

echo json_encode(['ok' => true, 'contenido' => $resultado['contenido'], 'cache' => false]);
