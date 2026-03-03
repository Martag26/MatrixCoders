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

    // --- NUEVO: LOGIN ---
    case 'login':
        require_once "../app/controllers/AuthController.php";
        $controller = new AuthController();
        $controller->loginForm();
        break;

    case 'doLogin':
        require_once "../app/controllers/AuthController.php";
        $controller = new AuthController();
        $controller->login();
        break;

    case 'logout':
        require_once "../app/controllers/AuthController.php";
        $controller = new AuthController();
        $controller->logout();
        break;

    // --- NUEVO: REGISTER ---
    case 'register':
        require_once "../app/controllers/RegisterController.php";
        $controller = new RegisterController();
        $controller->registerForm();
        break;

    case 'doRegister':
        require_once "../app/controllers/RegisterController.php";
        $controller = new RegisterController();
        $controller->register();
        break;

    // --- NUEVO: SUSCRIPCIONES ---
    case 'suscripciones':
        require_once "../app/controllers/SuscripcionController.php";
        $controller = new SuscripcionController();
        $controller->index();
        break;

    default:
        require_once "../app/controllers/CursoController.php";
        $controller = new CursoController();
        $controller->index();
        break;
}
