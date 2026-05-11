<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Curso.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login');
    exit;
}
if (($_SESSION['usuario_rol'] ?? '') !== 'USUARIO') {
    header('Location: ' . BASE_URL . '/index.php?url=crm');
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$db        = (new Database())->connect();

$tareaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($tareaId <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Cargar la tarea entregable con su unidad y curso
$stmtT = $db->prepare("
    SELECT te.*, u.titulo AS unidad_titulo, u.curso_id, c.titulo AS curso_titulo
    FROM tarea_entregable te
    JOIN unidad u ON u.id = te.unidad_id
    JOIN curso c  ON c.id = u.curso_id
    WHERE te.id = ?
");
$stmtT->execute([$tareaId]);
$tarea = $stmtT->fetch(PDO::FETCH_ASSOC);
if (!$tarea) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$cursoId = (int)$tarea['curso_id'];

// Verificar matrícula activa
$stmtM = $db->prepare("SELECT estado FROM matricula WHERE usuario_id=? AND curso_id=?");
$stmtM->execute([$usuarioId, $cursoId]);
$estadoMatricula = $stmtM->fetchColumn();

if (!$estadoMatricula) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}
if ($estadoMatricula === 'revocada') {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId . '&acceso=revocado');
    exit;
}

// Plazo vencido = curso expirado (matrícula + 90 días)
$plazoSuperado = false;
try {
    $stmtExp = $db->prepare("SELECT fecha FROM matricula WHERE usuario_id=? AND curso_id=? AND estado='activa'");
    $stmtExp->execute([$usuarioId, $cursoId]);
    $fechaMatricula = $stmtExp->fetchColumn();
    if ($fechaMatricula) {
        $expiracion = new DateTime($fechaMatricula);
        $expiracion->modify('+90 days');
        $plazoSuperado = new DateTime() > $expiracion;
    }
} catch (\Exception $e) {}

// Cargar entrega existente
$stmtE = $db->prepare("SELECT * FROM entrega_entregable WHERE tarea_id=? AND alumno_id=?");
$stmtE->execute([$tareaId, $usuarioId]);
$entrega = $stmtE->fetch(PDO::FETCH_ASSOC) ?: null;

// ── Procesar envío ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if ($plazoSuperado && !$entrega) {
        echo json_encode(['ok' => false, 'error' => 'El plazo de entrega ha finalizado']);
        exit;
    }
    if ($entrega && $entrega['revisado']) {
        echo json_encode(['ok' => false, 'error' => 'Tu entrega ya ha sido revisada y no puede modificarse']);
        exit;
    }

    $respTxt     = trim($_POST['respuesta'] ?? '');
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
        $destDir = __DIR__ . '/../../public/uploads/entregables/';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        $nombreArchivo = 'u' . $usuarioId . '_te' . $tareaId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $destDir . $nombreArchivo)) {
            $archivoPath = BASE_URL . '/uploads/entregables/' . $nombreArchivo;
        }
    }

    if (!$respTxt && !$archivoPath) {
        echo json_encode(['ok' => false, 'error' => 'Debes escribir una respuesta o adjuntar un archivo']);
        exit;
    }

    try {
        if ($entrega) {
            $db->prepare("
                UPDATE entrega_entregable
                SET respuesta=?, archivo=COALESCE(?,archivo), entregado_en=datetime('now')
                WHERE tarea_id=? AND alumno_id=?
            ")->execute([$respTxt, $archivoPath, $tareaId, $usuarioId]);
        } else {
            $db->prepare("
                INSERT INTO entrega_entregable (tarea_id, alumno_id, respuesta, archivo, entregado_en)
                VALUES (?,?,?,?,datetime('now'))
            ")->execute([$tareaId, $usuarioId, $respTxt, $archivoPath]);
        }
        echo json_encode(['ok' => true, 'mensaje' => 'Entrega guardada correctamente']);
    } catch (\Exception $e) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar la entrega']);
    }
    exit;
}

// Recargar entrega actualizada
$stmtE2 = $db->prepare("SELECT * FROM entrega_entregable WHERE tarea_id=? AND alumno_id=?");
$stmtE2->execute([$tareaId, $usuarioId]);
$entrega = $stmtE2->fetch(PDO::FETCH_ASSOC) ?: null;

// Datos del usuario para mostrar
$stmtU = $db->prepare("SELECT nombre FROM usuario WHERE id=?");
$stmtU->execute([$usuarioId]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

// ── Datos para el sidebar ────────────────────────────────────────
$modeloCurso = new Curso($db);
$unidades    = $modeloCurso->getUnidadesConLecciones($cursoId);

$leccionesVistas = $modeloCurso->getLeccionesVistas($usuarioId, $cursoId);

$tareasEntregablesEntregadas = [];
try {
    $stmtAllTE = $db->prepare("
        SELECT ee.tarea_id FROM entrega_entregable ee
        JOIN tarea_entregable te ON te.id = ee.tarea_id
        WHERE ee.alumno_id = ? AND te.curso_id = ?
    ");
    $stmtAllTE->execute([$usuarioId, $cursoId]);
    $tareasEntregablesEntregadas = array_flip($stmtAllTE->fetchAll(PDO::FETCH_COLUMN));
} catch (\Exception $e) {}

$tieneExamen = false;
try {
    $stEx = $db->prepare("SELECT COUNT(*) FROM examen WHERE curso_id=? AND (tipo='test' OR tipo IS NULL OR tipo='')");
    $stEx->execute([$cursoId]);
    $tieneExamen = (int)$stEx->fetchColumn() > 0;
} catch (\Exception $e) {}

$tieneExamenPractico = false;
try {
    $stExP = $db->prepare("SELECT COUNT(*) FROM tarea_practica WHERE curso_id=?");
    $stExP->execute([$cursoId]);
    $tieneExamenPractico = (int)$stExP->fetchColumn() > 0;
} catch (\Exception $e) {}

$resultadoExamenTest = null;
if ($tieneExamen) {
    try {
        $stR = $db->prepare("
            SELECT re.nota, re.aprobado FROM resultado_examen re
            JOIN examen e ON e.id = re.examen_id
            WHERE re.usuario_id=? AND e.curso_id=? AND (e.tipo='test' OR e.tipo IS NULL)
            ORDER BY re.realizado_en DESC LIMIT 1
        ");
        $stR->execute([$usuarioId, $cursoId]);
        $resultadoExamenTest = $stR->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (\Exception $e) {}
}

// ID de una lección del curso para usar como endpoint de marcado en el sidebar
$apiLeccionId = 0;
try {
    $stLec = $db->prepare("SELECT l.id FROM leccion l JOIN unidad u ON u.id=l.unidad_id WHERE u.curso_id=? ORDER BY u.orden, l.orden LIMIT 1");
    $stLec->execute([$cursoId]);
    $apiLeccionId = (int)($stLec->fetchColumn() ?: 0);
} catch (\Exception $e) {}

$tareaActivaId = $tareaId; // para resaltar esta tarea en el sidebar

require __DIR__ . '/../views/tarea_entregable/index.php';
