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
// getTareasByCurso siempre devuelve array (vacío si no hay tareas)
$tareas = $modeloCurso->getTareasByCurso($id);

// Si el usuario está matriculado, enriquecemos las tareas con su estado de entrega
if ($estaMatriculado && $usuarioId && !empty($tareas)) {
    $tareaIds = array_column($tareas, 'id');
    $placeholders = implode(',', array_fill(0, count($tareaIds), '?'));
    $stmtEnt = $db->prepare("
        SELECT tarea_id, id AS entrega_id, nota, entregado_en
        FROM entrega
        WHERE usuario_id = ? AND tarea_id IN ($placeholders)
    ");
    $stmtEnt->execute(array_merge([$usuarioId], $tareaIds));
    $entregas = [];
    foreach ($stmtEnt->fetchAll(PDO::FETCH_ASSOC) as $e) {
        $entregas[$e['tarea_id']] = $e;
    }
    foreach ($tareas as &$t) {
        $entrega = $entregas[$t['id']] ?? null;
        $hoy = strtotime(date('Y-m-d'));
        $fl  = !empty($t['fecha_limite']) ? strtotime(substr($t['fecha_limite'], 0, 10)) : null;
        $t['entregada']      = $entrega !== null;
        $t['entrega_nota']   = $entrega['nota'] ?? null;
        $t['entregado_en']   = $entrega['entregado_en'] ?? null;
        $t['dias_restantes'] = $fl !== null ? (int)floor(($fl - $hoy) / 86400) : null;
        if ($entrega) {
            $t['estado_visual'] = 'entregada';
        } elseif ($fl !== null && $fl < $hoy) {
            $t['estado_visual'] = 'vencida';
        } elseif ($fl !== null && $fl - $hoy <= 3 * 86400) {
            $t['estado_visual'] = 'proxima';
        } else {
            $t['estado_visual'] = 'pendiente';
        }
    }
    unset($t);
} else {
    foreach ($tareas as &$t) {
        $t['entregada']      = false;
        $t['entrega_nota']   = null;
        $t['entregado_en']   = null;
        $t['dias_restantes'] = null;
        $t['estado_visual']  = 'pendiente';
    }
    unset($t);
}

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
