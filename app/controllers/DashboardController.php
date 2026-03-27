<?php

/**
 * Controlador del panel principal (Dashboard).
 *
 * Carga y prepara toda la información necesaria para el espacio
 * de trabajo del usuario: calendario con eventos, carpetas,
 * documentos, último curso visto y próximas tareas.
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Carpeta.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/Tarea.php';

class DashboardController
{
    /**
     * Muestra el panel principal del usuario autenticado.
     *
     * Comprueba que hay una sesión activa antes de cargar los datos.
     * Recupera de la base de datos el calendario de tareas del mes,
     * las carpetas y documentos del usuario, el último curso en el que
     * estuvo matriculado y los próximos 5 eventos con fecha límite.
     *
     * @return void
     */
    public function index()
    {
<<<<<<< HEAD
        // Redirigir al login si el usuario no ha iniciado sesión
=======
        // Si el usuario no ha iniciado sesión, lo redirigimos al login
>>>>>>> develop-marta
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        // Obtener el ID del usuario de la sesión y forzar tipo entero
        $usuario_id = (int)$_SESSION['usuario_id'];

        // Conectar a la base de datos
        $database = new Database();
        $conexion = $database->connect();

<<<<<<< HEAD
        // Valores por defecto para el calendario (año y mes actuales)
        $calYear  = (int)date('Y');
        $calMonth = (int)date('n');

        /**
         * Consulta para obtener los días del mes actual que tienen
         * al menos una tarea con fecha límite asociada a los cursos
         * en los que el usuario está matriculado.
         */
        $sqlDiasEventos = "
            SELECT DISTINCT DAY(t.fecha_limite) AS dia
            FROM matricula m
            JOIN tarea t ON t.curso_id = m.curso_id
            WHERE m.usuario_id = ?
            AND t.fecha_limite IS NOT NULL
            AND YEAR(t.fecha_limite) = ?
            AND MONTH(t.fecha_limite) = ?
        ";

        // Sobreescribir con los valores recibidos por GET si existen
        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');

        // Validar que los valores del calendario están dentro de rangos aceptables
        if ($calMonth < 1)  $calMonth = 1;
        if ($calMonth > 12) $calMonth = 12;
        if ($calYear < 2000) $calYear = 2000;
        if ($calYear > 2100) $calYear = 2100;

        // Ejecutar la consulta de días con eventos y mapear el resultado a un array de enteros
        $stmt = $conexion->prepare($sqlDiasEventos);
        $stmt->execute([$usuario_id, $calYear, $calMonth]);
        $diasEventos = array_map(fn($r) => (int)$r['dia'], $stmt->fetchAll(PDO::FETCH_ASSOC));

        // Obtener todas las carpetas pertenecientes al usuario
        $stmt = $conexion->prepare("SELECT * FROM carpeta WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $carpetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener todos los documentos pertenecientes al usuario
        $stmt = $conexion->prepare("SELECT * FROM documento WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Obtener el último curso en el que el usuario estuvo matriculado,
         * ordenado por la fecha de matriculación más reciente.
         * Se usa para el widget "Seguir viendo".
         */
=======
        // Leemos el mes y año del calendario desde la URL, o usamos el actual
        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');

        // Validamos que el mes y año estén en rangos razonables
        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        // Días del mes con tareas (para marcar en el calendario)
        $tareaModel  = new Tarea($conexion);
        $diasEventos = $tareaModel->obtenerDiasConEventos($usuario_id, $calYear, $calMonth);

        // Próximos 5 eventos del usuario ordenados por fecha límite
        $eventos = $tareaModel->obtenerPorUsuario($usuario_id);
        $eventos = array_slice($eventos, 0, 5);

        // Carpetas del usuario para la zona de notas
        $carpetaModel = new Carpeta($conexion);
        $carpetas     = $carpetaModel->obtenerPorUsuario($usuario_id);

        // Documentos del usuario para la zona de notas
        $documentoModel = new Documento($conexion);
        $documentos     = $documentoModel->obtenerPorUsuario($usuario_id);

        // Último curso matriculado para el widget "Seguir viendo"
>>>>>>> develop-marta
        $stmt = $conexion->prepare("
            SELECT c.id, c.titulo
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            WHERE m.usuario_id = ?
            ORDER BY m.fecha DESC
            LIMIT 1
        ");
        $stmt->execute([$usuario_id]);
        $seguirCurso = $stmt->fetch(PDO::FETCH_ASSOC);

<<<<<<< HEAD
        /**
         * Obtener los próximos 5 eventos (tareas con fecha límite)
         * de los cursos en los que el usuario está matriculado,
         * ordenados de más próximo a más lejano.
         */
        $stmt = $conexion->prepare("
            SELECT t.titulo, t.fecha_limite, c.titulo AS curso
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            JOIN tarea t ON t.curso_id = c.id
            WHERE m.usuario_id = ?
              AND t.fecha_limite IS NOT NULL
            ORDER BY t.fecha_limite ASC
            LIMIT 5
        ");
        $stmt->execute([$usuario_id]);
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Definir el título de la página y la hoja de estilos específica del dashboard
=======
>>>>>>> develop-marta
        $pageTitle = "Espacio de trabajo";
        $pageCss   = BASE_URL . "/css/dashboard.css";

        // Cargar la vista del panel principal con todas las variables preparadas
        require __DIR__ . '/../views/dashboard/index.php';
    }
}
