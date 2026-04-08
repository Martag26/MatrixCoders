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

    case 'plantillas-documento':
        require_once "../app/controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->plantilla();
        break;

    case 'nuevo-documento':
        require_once "../app/controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->nuevoDocumento();
        break;

    case 'documento-compartido':
        require_once "../app/controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->documentoCompartido();
        break;

    case 'mis-documentos':
        require_once "../app/controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->documentos();
        break;

    case 'nube':
        require_once "../app/controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->documentos();
        break;

    case 'documento':
        require_once "../app/controllers/DashboardController.php";
        $controller = new DashboardController();
        $controller->verDocumento();
        break;

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


    case 'pagar':
        require_once "../app/controllers/CarritoController.php";
        $controller = new CarritoController();
        $controller->checkout();
        break;

    case 'pago-ok':
        require_once "../app/controllers/CarritoController.php";
        $controller = new CarritoController();
        $controller->pagoOk();
        break;

    case 'stripe-webhook':
        require_once "../app/controllers/CarritoController.php";
        $controller = new CarritoController();
        $controller->webhook();
        break;

    case 'calendario':
        require_once "../app/controllers/CalendarioController.php";
        break;

    case 'tareas':
        require_once "../app/controllers/CalendarioController.php";
        break;

    case 'lecciones':
        require_once "../app/controllers/LeccionesController.php";
        break;

    default:
        require_once "../app/controllers/CursoController.php";
        $controller = new CursoController();
        $controller->index();
        break;
}
