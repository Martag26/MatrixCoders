<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers/GeminiService.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$db = (new Database())->connect();

// Solo endpoint AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $pregunta = trim($_POST['pregunta'] ?? '');
    if (!$pregunta || mb_strlen($pregunta) < 3) {
        echo json_encode(['ok' => false, 'error' => 'Escribe una pregunta más larga']);
        exit;
    }
    if (mb_strlen($pregunta) > 600) {
        echo json_encode(['ok' => false, 'error' => 'La pregunta es demasiado larga']);
        exit;
    }

    // Contexto del alumno para personalizar respuestas
    $stmtU = $db->prepare("SELECT nombre FROM usuario WHERE id=?");
    $stmtU->execute([$usuarioId]);
    $nombreAlumno = $stmtU->fetchColumn() ?: 'alumno';

    $stmtCursos = $db->prepare("
        SELECT c.titulo, c.categoria, c.nivel
        FROM matricula m JOIN curso c ON c.id = m.curso_id
        WHERE m.usuario_id = ? AND m.estado = 'activa'
        LIMIT 8
    ");
    $stmtCursos->execute([$usuarioId]);
    $cursosAlumno = $stmtCursos->fetchAll(PDO::FETCH_COLUMN, 0);

    $listaCursos = empty($cursosAlumno)
        ? 'ningún curso aún'
        : implode(', ', $cursosAlumno);

    $sistemPrompt = "Eres el asistente virtual de MatrixCoders, una plataforma educativa online especializada en programación, desarrollo web y tecnología. Tu nombre es Oráculo.

Tu rol es dar soporte y orientación a los alumnos. Ayudas con:
- Dudas sobre el funcionamiento de la plataforma (cursos, lecciones, exámenes, certificados, buzón, repositorio de recursos)
- Orientación sobre qué cursos estudiar según los objetivos del alumno
- Resolución de problemas técnicos comunes en la plataforma
- Motivación y consejos de aprendizaje
- Explicaciones generales sobre temas de programación y tecnología

El alumno con el que hablas se llama {$nombreAlumno} y está matriculado en: {$listaCursos}.

Normas:
- Responde siempre en español, de forma amigable y concisa
- Si no sabes la respuesta con seguridad, dilo y sugiere contactar con soporte
- No des información personal de otros usuarios
- No hagas nada que no sea dar soporte educativo
- Máximo 3-4 párrafos por respuesta";

    $gemini = new GeminiService();
    $resultado = $gemini->preguntaConContexto($pregunta, $sistemPrompt);

    echo json_encode($resultado);
    exit;
}

// GET → mostrar página del chatbot
$pageTitle = 'Oráculo — Asistente de soporte';
require __DIR__ . '/../views/chatbot/index.php';
