<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login');
    exit;
}

$rolSesion = $_SESSION['usuario_rol'] ?? 'USUARIO';
if ($rolSesion !== 'USUARIO') {
    header('Location: ' . BASE_URL . '/index.php?url=crm');
    exit;
}

$usuarioId = (int)$_SESSION['usuario_id'];
$db = (new Database())->connect();

$filtroTipo   = $_GET['tipo']   ?? 'todos';
$filtroCurso  = isset($_GET['curso']) ? (int)$_GET['curso'] : 0;

// Cursos matriculados del usuario (para el selector de filtro)
$stmtCursos = $db->prepare("
    SELECT c.id, c.titulo
    FROM matricula m
    JOIN curso c ON c.id = m.curso_id
    WHERE m.usuario_id = ?
    ORDER BY c.titulo ASC
");
$stmtCursos->execute([$usuarioId]);
$cursosMatriculados = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

// Recursos descargables de todos los cursos matriculados
$sqlRec = "
    SELECT lr.id, lr.nombre, lr.tipo, lr.url_o_ruta, lr.descripcion, lr.descargable, lr.orden,
           l.titulo AS leccion_titulo, u.titulo AS unidad_titulo,
           c.id AS curso_id, c.titulo AS curso_titulo
    FROM leccion_recurso lr
    JOIN leccion l ON l.id = lr.leccion_id
    JOIN unidad u  ON u.id = l.unidad_id
    JOIN curso c   ON c.id = u.curso_id
    JOIN matricula m ON m.curso_id = c.id AND m.usuario_id = ?
    WHERE 1=1
";
$params = [$usuarioId];

if ($filtroTipo !== 'todos' && $filtroTipo !== 'actividad') {
    $sqlRec .= " AND lr.tipo = ?";
    $params[] = $filtroTipo;
} elseif ($filtroTipo === 'actividad') {
    $sqlRec .= " AND lr.tipo = 'actividad'";
}

if ($filtroCurso > 0) {
    $sqlRec .= " AND c.id = ?";
    $params[] = $filtroCurso;
}

$sqlRec .= " ORDER BY c.titulo ASC, u.titulo ASC, lr.orden ASC, l.titulo ASC";

$stmtRec = $db->prepare($sqlRec);
$stmtRec->execute($params);
$recursos = $stmtRec->fetchAll(PDO::FETCH_ASSOC);

// Separar materiales normales de actividades no evaluables
$materiales  = array_values(array_filter($recursos, fn($r) => $r['tipo'] !== 'actividad'));
$actividades = array_values(array_filter($recursos, fn($r) => $r['tipo'] === 'actividad'));

$pageTitle = 'Repositorio de recursos';
require __DIR__ . '/../views/repositorio/index.php';
