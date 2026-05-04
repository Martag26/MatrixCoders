<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login');
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$db        = (new Database())->connect();
$cursoId   = isset($_GET['curso']) ? (int)$_GET['curso'] : 0;

if ($cursoId <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Verify enrollment
$stmtM = $db->prepare("SELECT COUNT(*) FROM matricula WHERE usuario_id=? AND curso_id=? AND estado='activa'");
$stmtM->execute([$usuarioId, $cursoId]);
if (!(int)$stmtM->fetchColumn()) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Load course
$stmtC = $db->prepare("SELECT * FROM curso WHERE id=?");
$stmtC->execute([$cursoId]);
$curso = $stmtC->fetch(PDO::FETCH_ASSOC);
if (!$curso) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Load practical exam metadata
$stmtEx = $db->prepare("SELECT * FROM examen WHERE curso_id=? AND tipo='practico' LIMIT 1");
$stmtEx->execute([$cursoId]);
$examenPractico = $stmtEx->fetch(PDO::FETCH_ASSOC);

// Load practical tasks
try {
    $stmtT = $db->prepare("SELECT * FROM tarea_practica WHERE curso_id=? ORDER BY orden,id");
    $stmtT->execute([$cursoId]);
    $tareas = $stmtT->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tareas = [];
}

if (empty($tareas)) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Load existing submissions for this student
$entregasExistentes = [];
try {
    $stmtE = $db->prepare("SELECT * FROM entrega_practica WHERE alumno_id=? AND curso_id=?");
    $stmtE->execute([$usuarioId, $cursoId]);
    foreach ($stmtE->fetchAll(PDO::FETCH_ASSOC) as $e) {
        $entregasExistentes[$e['tarea_id']] = $e;
    }
} catch (Exception $e) {}

// Check if all tasks already submitted
$totalTareas      = count($tareas);
$totalEntregadas  = count($entregasExistentes);
$todasEntregadas  = $totalEntregadas >= $totalTareas;

// Check deadline
$plazoSuperado = false;
if ($examenPractico && !empty($examenPractico['fecha_entrega'])) {
    $plazoSuperado = (new DateTime()) > (new DateTime($examenPractico['fecha_entrega']));
}

// Load user info
$stmtU = $db->prepare("SELECT nombre FROM usuario WHERE id=?");
$stmtU->execute([$usuarioId]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

// ── Handle POST: save submissions ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if ($plazoSuperado) {
        echo json_encode(['ok' => false, 'error' => 'El plazo de entrega ha finalizado']);
        exit;
    }

    $tareaId  = (int)($_POST['tarea_id'] ?? 0);
    $respTxt  = trim($_POST['respuesta_texto'] ?? '');

    if (!$tareaId) {
        echo json_encode(['ok' => false, 'error' => 'ID de tarea inválido']);
        exit;
    }

    // Verify task belongs to this course
    $stmtVer = $db->prepare("SELECT id FROM tarea_practica WHERE id=? AND curso_id=?");
    $stmtVer->execute([$tareaId, $cursoId]);
    if (!$stmtVer->fetchColumn()) {
        echo json_encode(['ok' => false, 'error' => 'Tarea no encontrada']);
        exit;
    }

    $archivoPath = null;
    if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['archivo'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','zip','rar','txt','png','jpg','jpeg','mp4','py','js','html','css','php'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(['ok' => false, 'error' => 'Tipo de archivo no permitido']);
            exit;
        }
        if ($file['size'] > 50 * 1024 * 1024) {
            echo json_encode(['ok' => false, 'error' => 'Archivo muy grande (máx. 50 MB)']);
            exit;
        }
        $destDir = __DIR__ . '/../../public/uploads/practicos/';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        $nombreArchivo = 'u' . $usuarioId . '_t' . $tareaId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $destDir . $nombreArchivo)) {
            $archivoPath = BASE_URL . '/uploads/practicos/' . $nombreArchivo;
        }
    }

    if (!$respTxt && !$archivoPath) {
        echo json_encode(['ok' => false, 'error' => 'Debes escribir una respuesta o adjuntar un archivo']);
        exit;
    }

    try {
        $db->prepare("
            INSERT INTO entrega_practica (alumno_id, tarea_id, curso_id, respuesta_texto, archivo)
            VALUES (?,?,?,?,?)
            ON CONFLICT(alumno_id, tarea_id) DO UPDATE SET
              respuesta_texto=excluded.respuesta_texto,
              archivo=COALESCE(excluded.archivo, archivo),
              entregado_en=datetime('now'),
              revisado=0
        ")->execute([$usuarioId, $tareaId, $cursoId, $respTxt, $archivoPath]);
    } catch (Exception $e) {
        // Fallback for SQLite versions without ON CONFLICT DO UPDATE
        $check = $db->prepare("SELECT id FROM entrega_practica WHERE alumno_id=? AND tarea_id=?");
        $check->execute([$usuarioId, $tareaId]);
        if ($check->fetchColumn()) {
            $db->prepare("UPDATE entrega_practica SET respuesta_texto=?,archivo=COALESCE(?,archivo),entregado_en=datetime('now'),revisado=0 WHERE alumno_id=? AND tarea_id=?")
               ->execute([$respTxt, $archivoPath, $usuarioId, $tareaId]);
        } else {
            $db->prepare("INSERT INTO entrega_practica (alumno_id,tarea_id,curso_id,respuesta_texto,archivo) VALUES(?,?,?,?,?)")
               ->execute([$usuarioId, $tareaId, $cursoId, $respTxt, $archivoPath]);
        }
    }

    // Recount
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM entrega_practica WHERE alumno_id=? AND curso_id=?");
    $stmtCount->execute([$usuarioId, $cursoId]);
    $nuevasEntregadas = (int)$stmtCount->fetchColumn();

    echo json_encode([
        'ok'          => true,
        'mensaje'     => 'Entrega guardada correctamente',
        'entregadas'  => $nuevasEntregadas,
        'total'       => $totalTareas,
        'completado'  => $nuevasEntregadas >= $totalTareas,
    ]);
    exit;
}

require __DIR__ . '/../views/examen/practico.php';
