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

// Verificar matrícula activa
$stmtM = $db->prepare("SELECT COUNT(*) FROM matricula WHERE usuario_id=? AND curso_id=? AND estado='activa'");
$stmtM->execute([$usuarioId, $cursoId]);
if (!(int)$stmtM->fetchColumn()) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Cargar examen del curso
$stmtEx = $db->prepare("SELECT * FROM examen WHERE curso_id=?");
$stmtEx->execute([$cursoId]);
$examen = $stmtEx->fetch(PDO::FETCH_ASSOC);
if (!$examen) {
    header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
    exit;
}

// Datos del curso
$stmtC = $db->prepare("SELECT * FROM curso WHERE id=?");
$stmtC->execute([$cursoId]);
$curso = $stmtC->fetch(PDO::FETCH_ASSOC);

// Datos del usuario
$stmtU = $db->prepare("SELECT nombre FROM usuario WHERE id=?");
$stmtU->execute([$usuarioId]);
$usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

// Resultado previo
$stmtR = $db->prepare("SELECT * FROM resultado_examen WHERE usuario_id=? AND examen_id=?");
$stmtR->execute([$usuarioId, $examen['id']]);
$resultadoPrevio = $stmtR->fetch(PDO::FETCH_ASSOC);

// Certificado previo
$certificado = null;
if ($resultadoPrevio && $resultadoPrevio['aprobado']) {
    $stmtCert = $db->prepare("SELECT * FROM certificado WHERE usuario_id=? AND curso_id=?");
    $stmtCert->execute([$usuarioId, $cursoId]);
    $certificado = $stmtCert->fetch(PDO::FETCH_ASSOC);
}

// ── Procesar envío del examen ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !($resultadoPrevio && $resultadoPrevio['aprobado'])) {
    $stmtP = $db->prepare("SELECT * FROM pregunta WHERE examen_id=? ORDER BY orden");
    $stmtP->execute([$examen['id']]);
    $preguntas = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    $correctas = 0;
    $total     = count($preguntas);

    foreach ($preguntas as $p) {
        $opcionId = isset($_POST['p' . $p['id']]) ? (int)$_POST['p' . $p['id']] : 0;
        if ($opcionId) {
            $stmtOp = $db->prepare("SELECT correcta FROM opcion WHERE id=? AND pregunta_id=?");
            $stmtOp->execute([$opcionId, $p['id']]);
            $correctas += (int)($stmtOp->fetchColumn() ?: 0);
        }
    }

    $nota     = $total > 0 ? round(($correctas / $total) * 10, 1) : 0.0;
    $aprobado = $nota >= (float)$examen['nota_minima'] ? 1 : 0;

    // Guardar o actualizar resultado (no sobrescribir si ya aprobó)
    $stmtSave = $db->prepare("
        INSERT OR REPLACE INTO resultado_examen (usuario_id, examen_id, nota, aprobado, realizado_en)
        VALUES (?, ?, ?, ?, datetime('now'))
    ");
    $stmtSave->execute([$usuarioId, $examen['id'], $nota, $aprobado]);

    // Generar certificado si aprueba
    if ($aprobado) {
        $codigo = strtoupper(substr(md5($usuarioId . '-' . $cursoId . '-' . microtime()), 0, 12));
        $stmtCertIns = $db->prepare("
            INSERT OR IGNORE INTO certificado (usuario_id, curso_id, emitido_en, codigo)
            VALUES (?, ?, datetime('now'), ?)
        ");
        $stmtCertIns->execute([$usuarioId, $cursoId, $codigo]);

        $stmtCert = $db->prepare("SELECT * FROM certificado WHERE usuario_id=? AND curso_id=?");
        $stmtCert->execute([$usuarioId, $cursoId]);
        $certificado = $stmtCert->fetch(PDO::FETCH_ASSOC);
    }

    $resultadoPrevio = ['nota' => $nota, 'aprobado' => $aprobado, 'realizado_en' => date('Y-m-d H:i:s')];

    require __DIR__ . '/../views/examen/resultado.php';
    exit;
}

// ── Si ya tiene resultado, mostrar resultado directamente ────────
if ($resultadoPrevio) {
    require __DIR__ . '/../views/examen/resultado.php';
    exit;
}

// ── Cargar preguntas con opciones ────────────────────────────────
$stmtP = $db->prepare("SELECT * FROM pregunta WHERE examen_id=? ORDER BY orden");
$stmtP->execute([$examen['id']]);
$preguntas = $stmtP->fetchAll(PDO::FETCH_ASSOC);

foreach ($preguntas as &$p) {
    $stmtOp = $db->prepare("SELECT * FROM opcion WHERE pregunta_id=? ORDER BY orden");
    $stmtOp->execute([$p['id']]);
    $p['opciones'] = $stmtOp->fetchAll(PDO::FETCH_ASSOC);
}
unset($p);

require __DIR__ . '/../views/examen/index.php';
