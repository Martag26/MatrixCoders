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

    case 'buscar':
        require_once "../app/controllers/BuscarController.php";
        $controller = new BuscarController();
        $controller->index();
        break;

    case 'autocomplete':
        require_once "../app/controllers/AutocompleteController.php";
        $controller = new AutocompleteController();
        $controller->index();
        break;

    case 'carrito-añadir':
        require_once "../app/controllers/CarritoController.php";
        $controller = new CarritoController();
        $controller->añadir();
        break;
        
    case 'carrito':
        require_once "../app/controllers/CarritoController.php";
        $controller = new CarritoController();
        $controller->index();
        break;

    case 'carrito-eliminar':
        require_once "../app/controllers/CarritoController.php";
        $controller = new CarritoController();
        $controller->eliminar();
        break;

    case 'curso':
    case 'detallecurso':
        require_once "../app/controllers/detallecursocontroller.php";
        break;

    case 'leccion':
        require_once "../app/controllers/LeccionController.php";
        break;

    default:
        require_once "../app/controllers/CursoController.php";
        $controller = new CursoController();
        $controller->index();
        break;
}
