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

// Comprobar acceso según plan del usuario
$planUsuario = $_SESSION['usuario_plan'] ?? null;
$planesAccesoTotal = ['plan_estudiantes', 'plan_empresas'];

if (in_array($planUsuario, $planesAccesoTotal)) {
    // Con estos planes puede ver cualquier lección sin necesidad de matrícula
    $estaMatriculado = true;
} else {
    // Sin plan o con curso_individual: solo accede si está matriculado
    $estaMatriculado = $modeloCurso->estaMatriculado($usuarioId, $cursoId);
    if (!$estaMatriculado) {
        header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
        exit;
    }
}

// ── AJAX: marcar lección como vista ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'marcar_vista') {
    header('Content-Type: application/json');
    $modeloCurso->marcarVista($usuarioId, $leccionId);
    echo json_encode(['ok' => true]);
    exit;
}

// ── AJAX: guardar recurso de lección en la nube del usuario ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar_en_nube') {
    header('Content-Type: application/json');
    $nombre = trim($_POST['nombre'] ?? '');
    $url    = trim($_POST['url']    ?? '');
    if (!$nombre || !$url) { echo json_encode(['ok'=>false,'error'=>'Datos incompletos']); exit; }
    $contenido = "Recurso del curso \"{$curso['titulo']}\"\nLección: {$leccion['titulo']}\n\nURL: $url";
    try {
        $db->prepare("INSERT INTO documento (usuario_id, carpeta_id, titulo, contenido) VALUES(?,NULL,?,?)")
           ->execute([$usuarioId, $nombre, $contenido]);
        echo json_encode(['ok'=>true,'mensaje'=>'Guardado en tu nube']);
    } catch (Exception $e) {
        echo json_encode(['ok'=>false,'error'=>'No se pudo guardar']);
    }
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

// Comprobar si el curso tiene examen (siempre, para mostrarlo en el sidebar)
$stmtEx = $db->prepare("SELECT COUNT(*) FROM examen WHERE curso_id=? AND (tipo='test' OR tipo IS NULL OR tipo='')");
$stmtEx->execute([$cursoId]);
$tieneExamen = (int)$stmtEx->fetchColumn() > 0;

// Comprobar si tiene examen práctico
try {
    $stmtExPrac = $db->prepare("SELECT COUNT(*) FROM tarea_practica WHERE curso_id=?");
    $stmtExPrac->execute([$cursoId]);
    $tieneExamenPractico = (int)$stmtExPrac->fetchColumn() > 0;
} catch (Exception $e) { $tieneExamenPractico = false; }

// Resultado previo del examen test (para mostrar estado en sidebar)
$resultadoExamenTest = null;
if ($tieneExamen) {
    try {
        $stmtResTest = $db->prepare("
            SELECT re.nota, re.aprobado FROM resultado_examen re
            JOIN examen e ON e.id=re.examen_id
            WHERE re.usuario_id=? AND e.curso_id=? AND (e.tipo='test' OR e.tipo IS NULL)
            ORDER BY re.realizado_en DESC LIMIT 1
        ");
        $stmtResTest->execute([$usuarioId, $cursoId]);
        $resultadoExamenTest = $stmtResTest->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {}
}

require __DIR__ . '/../views/leccion/index.php';
