<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Notificacion.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login&retorno=notificaciones');
    exit;
}

$db         = (new Database())->connect();
$usuario_id = (int)$_SESSION['usuario_id'];
$model      = new Notificacion($db);

// Sync automatic notifications before loading
$model->sincronizarAutomaticas($usuario_id);

// Filters
$filtroTipo = $_GET['tipo'] ?? '';
$page       = max(1, (int)($_GET['p'] ?? 1));
$perPage    = 20;

$tiposValidos = ['tarea', 'tarea_vencida', 'expiracion', 'evento_calendario', 'crm', 'mensaje', 'info'];

$where  = 'WHERE usuario_id = ?';
$params = [$usuario_id];

if ($filtroTipo && in_array($filtroTipo, $tiposValidos, true)) {
    $where  .= ' AND tipo = ?';
    $params[] = $filtroTipo;
}

// Total count
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM notificacion $where");
$stmtTotal->execute($params);
$totalRows = (int)$stmtTotal->fetchColumn();
$totalPags = max(1, (int)ceil($totalRows / $perPage));
$offset    = ($page - 1) * $perPage;

// Fetch page
$stmtList = $db->prepare("
    SELECT * FROM notificacion
    $where
    ORDER BY leido ASC, creado_en DESC
    LIMIT $perPage OFFSET $offset
");
$stmtList->execute($params);
$notificaciones = $stmtList->fetchAll(PDO::FETCH_ASSOC);

// Unread count
$noLeidas = $model->contarNoLeidas($usuario_id);

// Mark all read if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'marcar-todas') {
    $model->marcarTodasLeidas($usuario_id);
    header('Location: ' . BASE_URL . '/index.php?url=notificaciones' . ($filtroTipo ? '&tipo=' . urlencode($filtroTipo) : ''));
    exit;
}

// Mark one read if GET action
if (isset($_GET['leer']) && (int)$_GET['leer'] > 0) {
    $model->marcarLeida((int)$_GET['leer'], $usuario_id);
    $redirect = $_GET['goto'] ?? '';
    if ($redirect) {
        header('Location: ' . $redirect);
        exit;
    }
    header('Location: ' . BASE_URL . '/index.php?url=notificaciones' . ($filtroTipo ? '&tipo=' . urlencode($filtroTipo) : '') . '&p=' . $page);
    exit;
}

$pageTitle = 'Notificaciones';
require __DIR__ . '/../views/notificaciones/index.php';
