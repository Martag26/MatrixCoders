<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../helpers/GeminiService.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Solo alumnos (USUARIO) pueden acceder al portal de lecciones
if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login');
    exit;
}
if (($_SESSION['usuario_rol'] ?? '') !== 'USUARIO') {
    header('Location: ' . BASE_URL . '/index.php?url=crm');
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
    $estaMatriculado = true;
} else {
    // Verificar estado de matrícula (activa o revocada)
    $stmtEst = $db->prepare("SELECT estado FROM matricula WHERE usuario_id=? AND curso_id=?");
    $stmtEst->execute([$usuarioId, $cursoId]);
    $estadoMatricula = $stmtEst->fetchColumn();

    if (!$estadoMatricula) {
        header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId);
        exit;
    }
    if ($estadoMatricula === 'revocada') {
        header('Location: ' . BASE_URL . '/index.php?url=detallecurso&id=' . $cursoId . '&acceso=revocado');
        exit;
    }
    $estaMatriculado = true;
}

// ── AJAX: marcar lección como vista ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'marcar_vista') {
    header('Content-Type: application/json');
    $modeloCurso->marcarVista($usuarioId, $leccionId);
    echo json_encode(['ok' => true]);
    exit;
}

// ── AJAX: chat RAG sobre contenido del curso ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'rag_chat') {
    header('Content-Type: application/json');
    $pregunta = trim($_POST['pregunta'] ?? '');
    if (!$pregunta) { echo json_encode(['ok' => false, 'error' => 'Pregunta vacía']); exit; }

    // Construir contexto: título del curso, unidades y lecciones, apuntes IA si existen
    $stmtCtx = $db->prepare("
        SELECT c.titulo AS curso_titulo, c.descripcion AS curso_descripcion,
               l.titulo AS leccion_titulo, l.video_url
        FROM leccion l
        JOIN unidad u ON u.id = l.unidad_id
        JOIN curso c  ON c.id = u.curso_id
        WHERE l.id = ?
    ");
    $stmtCtx->execute([$leccionId]);
    $ctx = $stmtCtx->fetch(PDO::FETCH_ASSOC);

    $stmtUnidades = $db->prepare("
        SELECT u.titulo, GROUP_CONCAT(l2.titulo, ' | ') AS lecciones
        FROM unidad u
        JOIN leccion l2 ON l2.unidad_id = u.id
        WHERE u.curso_id = (SELECT unidad.curso_id FROM unidad JOIN leccion ON leccion.unidad_id = unidad.id WHERE leccion.id = ?)
        GROUP BY u.id ORDER BY u.orden
    ");
    $stmtUnidades->execute([$leccionId]);
    $unidadesCtx = $stmtUnidades->fetchAll(PDO::FETCH_ASSOC);

    $stmtApuntes = $db->prepare("SELECT contenido FROM leccion_apuntes_ia WHERE leccion_id = ?");
    $stmtApuntes->execute([$leccionId]);
    $apuntesIa = $stmtApuntes->fetchColumn() ?: '';

    $contexto  = "Curso: {$ctx['curso_titulo']}\n";
    $contexto .= "Descripción del curso: " . ($ctx['curso_descripcion'] ?? 'No disponible') . "\n";
    $contexto .= "Lección actual: {$ctx['leccion_titulo']}\n\n";
    $contexto .= "Estructura del curso:\n";
    foreach ($unidadesCtx as $u) {
        $contexto .= "- {$u['titulo']}: {$u['lecciones']}\n";
    }
    if ($apuntesIa) {
        $contexto .= "\nApuntes generados de la lección:\n" . substr($apuntesIa, 0, 2000);
    }

    $gemini    = new GeminiService();
    $resultado = $gemini->preguntaConContexto($pregunta, $contexto);
    echo json_encode($resultado);
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

// Recursos descargables de esta lección (subidos por el instructor via CRM)
$stmtRec = $db->prepare("
    SELECT id, nombre, tipo, url_o_ruta, descripcion, descargable
    FROM leccion_recurso
    WHERE leccion_id = ?
    ORDER BY orden ASC, id ASC
");
$stmtRec->execute([$leccionId]);
$recursosInstructor = $stmtRec->fetchAll(PDO::FETCH_ASSOC);

// URL del notebook de NotebookLM asociado a esta lección (gestionado por el instructor)
$stmtNb = $db->prepare("SELECT notebook_url FROM leccion_notebook WHERE leccion_id = ?");
$stmtNb->execute([$leccionId]);
$notebookUrl = $stmtNb->fetchColumn() ?: null;

// Recursos del instructor para esta lección
try {
    $stmtRec = $db->prepare('SELECT * FROM leccion_recurso WHERE leccion_id=? ORDER BY orden, id');
    $stmtRec->execute([$leccionId]);
    $recursosInstructor = $stmtRec->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $recursosInstructor = []; }

// Apuntes IA en caché (si existen)
$stmtAi = $db->prepare("SELECT contenido FROM leccion_apuntes_ia WHERE leccion_id = ?");
$stmtAi->execute([$leccionId]);
$apuntesIaCached = $stmtAi->fetchColumn() ?: null;

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
