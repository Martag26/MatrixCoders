<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

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
$cursoId   = isset($_GET['curso']) ? (int)$_GET['curso'] : 0;

if ($cursoId <= 0) {
    header('Location: ' . BASE_URL . '/index.php?url=dashboard');
    exit;
}

// Verificar que el alumno tiene matrícula activa o completada
$stmtM = $db->prepare("SELECT estado FROM matricula WHERE usuario_id=? AND curso_id=? AND estado IN ('activa','completado')");
$stmtM->execute([$usuarioId, $cursoId]);
$matricula = $stmtM->fetch(PDO::FETCH_ASSOC);
if (!$matricula) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Cargar datos del curso
$stmtC = $db->prepare("SELECT * FROM curso WHERE id=?");
$stmtC->execute([$cursoId]);
$curso = $stmtC->fetch(PDO::FETCH_ASSOC);
if (!$curso) {
    header('Location: ' . BASE_URL . '/index.php?url=dashboard');
    exit;
}

// Cargar certificado
$certificado = null;
try {
    $stmtCert = $db->prepare("SELECT * FROM certificado WHERE usuario_id=? AND curso_id=?");
    $stmtCert->execute([$usuarioId, $cursoId]);
    $certificado = $stmtCert->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Exception $e) {}

// Nota media del práctico
$mediaNotas = null;
try {
    $stmtAvg = $db->prepare("SELECT AVG(nota) FROM entrega_practica WHERE alumno_id=? AND curso_id=? AND nota IS NOT NULL");
    $stmtAvg->execute([$usuarioId, $cursoId]);
    $avg = $stmtAvg->fetchColumn();
    if ($avg !== false && $avg !== null) {
        $mediaNotas = round((float)$avg, 1);
    }
} catch (Exception $e) {}

// Nombre del alumno
$nombreAlumno = $_SESSION['usuario_nombre'] ?? 'Alumno';

require __DIR__ . '/../views/examen/completado.php';
