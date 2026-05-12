<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/GeminiService.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Sesión no iniciada']);
    } else {
        header('Location: ' . BASE_URL . '/index.php?url=login');
    }
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$db = (new Database())->connect();

// POST: endpoint AJAX (pregunta normal o reiniciar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Acción de reinicio
    if (($_POST['accion'] ?? '') === 'reiniciar') {
        unset($_SESSION['chatbot_historial']);
        echo json_encode(['ok' => true]);
        exit;
    }

    $pregunta = trim($_POST['pregunta'] ?? '');
    if (!$pregunta || mb_strlen($pregunta) < 3) {
        echo json_encode(['ok' => false, 'error' => 'Escribe una pregunta más larga']);
        exit;
    }
    if (mb_strlen($pregunta) > 600) {
        echo json_encode(['ok' => false, 'error' => 'La pregunta es demasiado larga']);
        exit;
    }

    // Contexto del alumno
    $stmtU = $db->prepare("SELECT nombre FROM usuario WHERE id=?");
    $stmtU->execute([$usuarioId]);
    $nombreAlumno = $stmtU->fetchColumn() ?: 'alumno';

    $stmtCursos = $db->prepare("
        SELECT c.titulo FROM matricula m JOIN curso c ON c.id = m.curso_id
        WHERE m.usuario_id = ? AND m.estado = 'activa' LIMIT 8
    ");
    $stmtCursos->execute([$usuarioId]);
    $cursosAlumno = $stmtCursos->fetchAll(PDO::FETCH_COLUMN, 0);
    $listaCursos  = empty($cursosAlumno) ? 'ningún curso aún' : implode(', ', $cursosAlumno);

    $systemPrompt = "Eres Oráculo, el asistente virtual de MatrixCoders, una plataforma educativa de programación y desarrollo web.
Ayudas con: dudas sobre la plataforma (cursos, lecciones, exámenes, certificados, buzón), orientación sobre qué cursos estudiar, problemas técnicos, motivación y consejos de aprendizaje, y explicaciones sobre programación y tecnología.
El alumno se llama {$nombreAlumno} y está matriculado en: {$listaCursos}.
Responde siempre en español, de forma amigable y concisa. Máximo 3-4 párrafos.";

    // Recuperar historial de sesión (máximo 20 turnos = 10 intercambios)
    $historial = $_SESSION['chatbot_historial'] ?? [];
    if (count($historial) > 20) {
        $historial = array_slice($historial, -20);
    }

    $gemini    = new GeminiService();
    $resultado = $gemini->chatbotConHistorial($pregunta, $systemPrompt, $historial);

    if ($resultado['ok']) {
        // Guardar en historial de sesión
        $historial[] = ['role' => 'user',  'text' => $pregunta];
        $historial[] = ['role' => 'model', 'text' => $resultado['respuesta']];
        $_SESSION['chatbot_historial'] = $historial;
    }

    echo json_encode($resultado);
    exit;
}

// GET → página del chatbot (con botón reiniciar)
$pageTitle = 'Oráculo — Asistente de soporte';
require __DIR__ . '/../views/chatbot/index.php';
