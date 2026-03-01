<?php
require_once __DIR__ . '/../app/config.php';
session_start();
$url = $_GET['url'] ?? '';

switch ($url) {

    case 'dashboard':
        require_once "../app/controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->index();
        break;

    default:
        require_once "../app/controllers/CursoController.php";
        $controller = new CursoController();
        $controller->index();
        break;
}