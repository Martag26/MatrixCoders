<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Curso.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Crear la conexión igual que el resto de controllers
$db = (new Database())->connect();
$modeloCurso = new Curso($db);

$usuarioId   = $_SESSION['usuario_id']   ?? null;
$usuarioPlan = $_SESSION['usuario_plan'] ?? 'gratuito'; // TODO: ajusta el campo de plan en tu sesión

// ── ID del curso ──────────────────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// ── Instancia del modelo ──────────────────────────────────────────
global $db; // TODO: ajusta si obtienes la conexión de otra forma
$modeloCurso = new Curso($db);

// ── Datos del curso ───────────────────────────────────────────────
$curso = $modeloCurso->getById($id);
if (!$curso) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// ── Acción: matricularse ──────────────────────────────────────────
$mensajeMatricula = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'matricular') {
    if (!$usuarioId) {
        header('Location: ' . BASE_URL . '/index.php?url=login');
        exit;
    }
    $ok = $modeloCurso->matricular($usuarioId, $id);
    $mensajeMatricula = $ok ? 'exito' : 'ya_matriculado';
}

// ── Estado de matriculación ───────────────────────────────────────
$estaMatriculado = $usuarioId
    ? $modeloCurso->estaMatriculado($usuarioId, $id)
    : false;

// ── Fecha de matriculación y expiración (90 días) ─────────────────
$fechaMatricula   = null;
$fechaExpiracion  = null;
$diasParaExpirar  = null;
if ($estaMatriculado && $usuarioId) {
    $stmtFecha = $db->prepare("SELECT fecha FROM matricula WHERE usuario_id = ? AND curso_id = ? LIMIT 1");
    $stmtFecha->execute([$usuarioId, $id]);
    $fechaMatricula = $stmtFecha->fetchColumn() ?: null;
    if ($fechaMatricula) {
        $expTs           = strtotime($fechaMatricula) + (90 * 86400);
        $fechaExpiracion = date('d/m/Y', $expTs);
        $diasParaExpirar = (int)ceil(($expTs - time()) / 86400);
    }
}

// ── Comprobación de plan ──────────────────────────────────────────
$precio = (float)($curso['precio'] ?? 0);
$planPermiteAcceso = match (true) {
    $precio <= 0                        => true,  // gratis: todos
    $usuarioPlan === 'estudiantes'      => true,  // plan estudiantes: todos
    $usuarioPlan === 'empresas'         => true,  // plan empresas: todos
    default                             => false, // individual o sin plan: solo comprados
};

// ── Unidades con lecciones ────────────────────────────────────────
$unidades = $modeloCurso->getUnidadesConLecciones($id);

// ── Tareas ────────────────────────────────────────────────────────
$tareas = $modeloCurso->getTareasByCurso($id);

// ── Lección activa (solo si está matriculado) ─────────────────────
$leccionActiva = null;
if ($estaMatriculado) {
    $leccionId = isset($_GET['leccion']) ? (int)$_GET['leccion'] : 0;
    if ($leccionId > 0) {
        $leccionActiva = $modeloCurso->getLeccionById($leccionId);
    }
    // Si no se pidió ninguna, abre la primera
    if (!$leccionActiva) {
        $leccionActiva = $modeloCurso->getPrimeraLeccion($id);
    }
}

// ── Descuento activo de campaña ───────────────────────────────────
$descuentoActivo = 0.0;
try {
    $stmtDesc = $db->prepare("
        SELECT cc.descuento FROM campana_curso cc
        JOIN campana_crm cm ON cm.id = cc.campana_id
        WHERE cc.curso_id = ? AND cm.activa = 1
          AND (cm.fecha_fin IS NULL OR cm.fecha_fin >= date('now'))
        LIMIT 1
    ");
    $stmtDesc->execute([$id]);
    $descuentoActivo = (float)($stmtDesc->fetchColumn() ?: 0);
} catch (Exception $e) { /* tabla no existe aún */ }

$precioFinal = ($descuentoActivo > 0 && $precio > 0)
    ? round($precio * (1 - $descuentoActivo / 100), 2)
    : $precio;

// ── Título de página ──────────────────────────────────────────────
$pageTitle = htmlspecialchars($curso['titulo'] ?? 'Detalle del curso');

require __DIR__ . '/../views/detallecurso/index.php';
