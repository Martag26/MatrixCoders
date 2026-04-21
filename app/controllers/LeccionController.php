<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Curso.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Ruta privada: requiere sesión activa
if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login');
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];

$leccionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($leccionId <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$db          = (new Database())->connect();
$modeloCurso = new Curso($db);

// Datos de la lección
$leccion = $modeloCurso->getLeccionById($leccionId);
if (!$leccion) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Unidad y curso al que pertenece
$unidad  = $modeloCurso->getUnidadById($leccion['unidad_id']);
$cursoId = $unidad['curso_id'] ?? 0;
$curso   = $modeloCurso->getById($cursoId);

// Comprobar que el usuario está matriculado
$estaMatriculado = $usuarioId
    ? $modeloCurso->estaMatriculado($usuarioId, $cursoId)
    : false;

if (!$estaMatriculado) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// ── AJAX: marcar lección como vista (trigger desde el frontend al terminar el vídeo) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'marcar_vista') {
    header('Content-Type: application/json');
    $modeloCurso->marcarVista($usuarioId, $leccionId);
    echo json_encode(['ok' => true]);
    exit;
}

// ── Lecciones ya vistas en este curso (para el sidebar) ─────────
$leccionesVistas = $usuarioId
    ? $modeloCurso->getLeccionesVistas($usuarioId, $cursoId)
    : [];

// Todas las unidades + lecciones (para el sidebar)
$unidades = $modeloCurso->getUnidadesConLecciones($cursoId);

// Lección anterior y siguiente
$leccionAnterior  = $modeloCurso->getLeccionAnterior($leccionId, $cursoId);
$leccionSiguiente = $modeloCurso->getLeccionSiguiente($leccionId, $cursoId);

// Nota del usuario para esta lección
$nota = '';
if ($usuarioId) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nota'])) {
        header('Content-Type: application/json');
        $contenido = trim($_POST['nota']);
        $modeloCurso->guardarNota($usuarioId, $leccionId, $contenido);
        echo json_encode(['ok' => true]);
        exit;
    }
    $nota = $modeloCurso->getNota($usuarioId, $leccionId);
}

// URL del notebook de NotebookLM asociado a esta lección (generado por el instructor con IA)
$stmtNb = $db->prepare("SELECT notebook_url FROM leccion_notebook WHERE leccion_id = ?");
$stmtNb->execute([$leccionId]);
$notebookUrl = $stmtNb->fetchColumn() ?: null;

$pageTitle = htmlspecialchars($leccion['titulo'] ?? 'Lección');

// Si es la última lección, comprobar si el curso tiene examen
$tieneExamen = false;
if (!$leccionSiguiente) {
    $stmtEx = $db->prepare("SELECT COUNT(*) FROM examen WHERE curso_id = ?");
    $stmtEx->execute([$cursoId]);
    $tieneExamen = (int)$stmtEx->fetchColumn() > 0;
}

require __DIR__ . '/../views/leccion/index.php';
