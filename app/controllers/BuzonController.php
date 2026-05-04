<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php?url=login&retorno=buzon');
    exit;
}

$db         = (new Database())->connect();
$usuario_id = (int)$_SESSION['usuario_id'];

// Abrir y marcar como leído un mensaje concreto
$msgActivo = null;
if (isset($_GET['msg']) && (int)$_GET['msg'] > 0) {
    $msgId = (int)$_GET['msg'];
    $stmtMsg = $db->prepare("
        SELECT m.*, u.nombre AS nombre_emisor, u.rol AS rol_emisor
        FROM mensaje m
        JOIN usuario u ON u.id = m.emisor_id
        WHERE m.id = ? AND m.receptor_id = ?
    ");
    $stmtMsg->execute([$msgId, $usuario_id]);
    $msgActivo = $stmtMsg->fetch(PDO::FETCH_ASSOC);

    if ($msgActivo) {
        // Marcar como leído
        $db->prepare("UPDATE mensaje SET leido = 1 WHERE id = ? AND receptor_id = ?")->execute([$msgId, $usuario_id]);
        $msgActivo['leido'] = 1;

        // Marcar también la notificación asociada como leída
        $db->prepare("UPDATE notificacion SET leido = 1 WHERE usuario_id = ? AND tipo = 'mensaje' AND ref_id = ?")
           ->execute([$usuario_id, $msgId]);
    }
}

// Paginación
$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

// Total de mensajes recibidos
$stmtCount = $db->prepare("SELECT COUNT(*) FROM mensaje WHERE receptor_id = ?");
$stmtCount->execute([$usuario_id]);
$totalRows = (int)$stmtCount->fetchColumn();
$totalPags = max(1, (int)ceil($totalRows / $perPage));

// Listado paginado con datos del emisor
$stmtList = $db->prepare("
    SELECT m.id, m.asunto, m.cuerpo, m.enviado_en, m.leido,
           u.nombre AS nombre_emisor, u.rol AS rol_emisor
    FROM mensaje m
    JOIN usuario u ON u.id = m.emisor_id
    WHERE m.receptor_id = ?
    ORDER BY m.leido ASC, m.enviado_en DESC
    LIMIT ? OFFSET ?
");
$stmtList->execute([$usuario_id, $perPage, $offset]);
$mensajes = $stmtList->fetchAll(PDO::FETCH_ASSOC);

// Contador de no leídos
$stmtUnread = $db->prepare("SELECT COUNT(*) FROM mensaje WHERE receptor_id = ? AND leido = 0");
$stmtUnread->execute([$usuario_id]);
$noLeidos = (int)$stmtUnread->fetchColumn();

$pageTitle = 'Buzón de entrada';
require __DIR__ . '/../views/buzon/index.php';
