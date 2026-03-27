<?php
/**
 * Front Controller principal de la aplicación.
 *
 * Punto de entrada único de MatrixCoders. Recibe todas las peticiones HTTP,
 * lee el parámetro 'url' de la query string y redirige la ejecución al
 * controlador y método correspondiente mediante un switch.
 *
 * Rutas disponibles:
 *  - dashboard      → DashboardController::index()
 *  - login          → AuthController::loginForm()
 *  - doLogin        → AuthController::login()
 *  - logout         → AuthController::logout()
 *  - register       → RegisterController::registerForm()
 *  - doRegister     → RegisterController::register()
 *  - suscripciones  → SuscripcionController::index()
 *  - (default)      → CursoController::index()
 */

// Cargar la configuración global de la aplicación (BASE_URL, constantes, etc.)
require_once __DIR__ . '/../app/config.php';

// Iniciar la sesión para que los controladores y vistas puedan acceder a ella
session_start();

// Leer el segmento de ruta enviado por GET; si no existe, cadena vacía (página de inicio)
$url = $_GET['url'] ?? '';

// Enrutar la petición al controlador adecuado según el valor de 'url'
switch ($url) {

    // Muestra el panel principal del usuario autenticado
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

    default:
        require_once "../app/controllers/CursoController.php";
        $controller = new CursoController();
        $controller->index();
        break;
}
