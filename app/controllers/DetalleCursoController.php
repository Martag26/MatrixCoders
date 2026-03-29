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

// ── Comprobación de plan ──────────────────────────────────────────
// Ajusta la lógica según tus planes (gratuito, basico, premium…)
$precio = (float)($curso['precio'] ?? 0);
$planPermiteAcceso = match (true) {
    $precio <= 0               => true,  // Gratis: todos pueden
    $usuarioPlan === 'premium' => true,  // Premium: acceso total
    default                    => false, // TODO: añade más condiciones si tienes más planes
};

// ── Unidades con lecciones ────────────────────────────────────────
$unidades = $modeloCurso->getUnidadesConLecciones($id);

// ── Tareas ────────────────────────────────────────────────────────
$tareas = $modeloCurso->getTareasByCurso($id);

// ── Título de página ──────────────────────────────────────────────
$pageTitle = htmlspecialchars($curso['titulo'] ?? 'Detalle del curso');

require __DIR__ . '/../views/detallecurso/index.php';
