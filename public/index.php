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

    // Muestra el formulario de inicio de sesión
    case 'login':
        require_once "../app/controllers/AuthController.php";
        $controller = new AuthController();
        $controller->loginForm();
        break;

    // Procesa las credenciales enviadas desde el formulario de login
    case 'doLogin':
        require_once "../app/controllers/AuthController.php";
        $controller = new AuthController();
        $controller->login();
        break;

    // Cierra la sesión activa del usuario y redirige al inicio
    case 'logout':
        require_once "../app/controllers/AuthController.php";
        $controller = new AuthController();
        $controller->logout();
        break;

    // Muestra el formulario de registro de nuevos usuarios
    case 'register':
        require_once "../app/controllers/RegisterController.php";
        $controller = new RegisterController();
        $controller->registerForm();
        break;

    // Procesa los datos enviados desde el formulario de registro
    case 'doRegister':
        require_once "../app/controllers/RegisterController.php";
        $controller = new RegisterController();
        $controller->register();
        break;

    // Muestra la página de planes y precios de suscripción
    case 'suscripciones':
        require_once "../app/controllers/SuscripcionController.php";
        $controller = new SuscripcionController();
        $controller->index();
        break;

    // Ruta por defecto: muestra el listado de cursos disponibles (página de inicio)
    default:
        require_once "../app/controllers/CursoController.php";
        $controller = new CursoController();
        $controller->index();
        break;
}
