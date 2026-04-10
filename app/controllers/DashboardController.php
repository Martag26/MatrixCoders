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
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        // Obtener el ID del usuario de la sesión y forzar tipo entero
        $usuario_id = (int)$_SESSION['usuario_id'];

        // Conectar a la base de datos
        $database = new Database();
        $conexion = $database->connect();
        $carpetaModel = new Carpeta($conexion);
        $documentoModel = new Documento($conexion);
        $tareaModel = new Tarea($conexion);

        $this->procesarAcciones($usuario_id, $carpetaModel, $documentoModel);

        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        $mostrarTodosDocumentos = isset($_GET['ver']) && $_GET['ver'] === 'todos';
        $carpetas = $carpetaModel->obtenerConTotalesPorUsuario($usuario_id);
        $documentos = $documentoModel->obtenerConCarpetaPorUsuario($usuario_id);
        $documentosRecientes = $mostrarTodosDocumentos ? $documentos : array_slice($documentos, 0, 4);
        $tareasUsuario = $tareaModel->obtenerPorUsuario($usuario_id);
        $diasConTareas = $tareaModel->obtenerDiasConEventos($usuario_id, $calYear, $calMonth);
        $documentosCompartibles = array_map(fn($doc) => [
            'id' => (int)$doc['id'],
            'titulo' => $doc['titulo'],
            'carpeta_nombre' => $doc['carpeta_nombre'] ?? '',
            'share_url' => $this->buildPublicShareUrl($doc),
        ], $documentos);

        $stmt = $conexion->prepare("
            SELECT
                c.id,
                c.titulo,
                c.imagen,
                m.fecha AS fecha_matricula,
                (
                    SELECT COUNT(l.id)
                    FROM leccion l
                    JOIN unidad u ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id
                ) AS total_lecciones,
                (
                    SELECT COUNT(DISTINCT lv.leccion_id)
                    FROM leccion_vista lv
                    JOIN leccion l ON l.id = lv.leccion_id
                    JOIN unidad u  ON l.unidad_id = u.id
                    WHERE u.curso_id = c.id
                      AND lv.usuario_id = ?
                ) AS lecciones_vistas,
                (
                    SELECT lv2.leccion_id
                    FROM leccion_vista lv2
                    JOIN leccion l2 ON l2.id = lv2.leccion_id
                    JOIN unidad u2  ON l2.unidad_id = u2.id
                    WHERE u2.curso_id = c.id
                      AND lv2.usuario_id = ?
                    ORDER BY lv2.visto_at DESC
                    LIMIT 1
                ) AS ultima_leccion_id
            FROM matricula m
            JOIN curso c ON c.id = m.curso_id
            WHERE m.usuario_id = ?
            ORDER BY m.fecha DESC
        ");
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        $cursosEnProgreso = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosEnProgreso as &$c) {
            $total  = (int)$c['total_lecciones'];
            $vistos = (int)$c['lecciones_vistas'];
            $c['progreso'] = $total > 0 ? round(($vistos / $total) * 100) : 0;

            if (!$c['ultima_leccion_id']) {
                $stmt2 = $conexion->prepare("
                    SELECT l.id FROM leccion l
                    JOIN unidad u ON l.unidad_id = u.id
                    WHERE u.curso_id = ?
                    ORDER BY u.orden ASC, l.orden ASC
                    LIMIT 1
                ");
                $stmt2->execute([$c['id']]);
                $c['ultima_leccion_id'] = $stmt2->fetchColumn() ?: null;
            }
        }
        unset($c);

        // Definir el título de la página y la hoja de estilos específica del dashboard
        $pageTitle = "Espacio de trabajo";
        $pageCss   = BASE_URL . "/css/dashboard.css";
        $flash = $_SESSION['dashboard_flash'] ?? null;
        unset($_SESSION['dashboard_flash']);

        // Cargar la vista del panel principal con todas las variables preparadas
        require __DIR__ . '/../views/dashboard/index.php';
    }

    public function plantilla()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $database = new Database();
        $conexion = $database->connect();
        $carpetaModel = new Carpeta($conexion);
        $carpetas = $carpetaModel->obtenerConTotalesPorUsuario($usuario_id);
        $templates = $this->templateCatalogo();
        $pageTitle = 'Plantillas';
        require __DIR__ . '/../views/dashboard/plantilla.php';
    }

    public function nuevoDocumento()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $database = new Database();
        $conexion = $database->connect();
        $carpetaModel = new Carpeta($conexion);
        $documentoModel = new Documento($conexion);

        $carpetas = $carpetaModel->obtenerConTotalesPorUsuario($usuario_id);
        $templates = $this->templateCatalogo();
        $templateKey = trim($_GET['template'] ?? '');
        $selectedTemplate = $templates[$templateKey] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = trim($_POST['doc_title'] ?? '');
            $contenido = trim($_POST['doc_content'] ?? '');
            $carpetaId = isset($_POST['folder_id']) && $_POST['folder_id'] !== ''
                ? (int)$_POST['folder_id']
                : null;

            if ($titulo === '') {
                $titulo = 'Nuevo documento ' . date('d/m/Y H:i');
            }

            $creado = $documentoModel->crear($usuario_id, $titulo, $carpetaId, $contenido);
            $this->setFlash(
                $creado ? 'success' : 'error',
                $creado ? 'Documento creado correctamente.' : 'No se pudo crear el documento.'
            );

            header("Location: " . BASE_URL . "/index.php?url=dashboard");
            exit;
        }

        $pageTitle = 'Nuevo documento';
        require __DIR__ . '/../views/dashboard/nuevo.php';
    }

    public function documentos()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $database = new Database();
        $conexion = $database->connect();
        $carpetaModel = new Carpeta($conexion);
        $documentoModel = new Documento($conexion);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['dashboard_action'] ?? '') === 'create_folder') {
            $nombre = trim($_POST['folder_name'] ?? '');
            if ($nombre === '') {
                $this->setFlash('error', 'La carpeta necesita un nombre.');
            } else {
                $creada = $carpetaModel->crear($usuario_id, $nombre);
                $this->setFlash(
                    $creada ? 'success' : 'error',
                    $creada ? 'Carpeta creada correctamente.' : 'No se pudo crear la carpeta.'
                );
            }

            header("Location: " . BASE_URL . "/index.php?url=mis-documentos");
            exit;
        }

        $carpetas = $carpetaModel->obtenerConTotalesPorUsuario($usuario_id);
        $documentos = $documentoModel->obtenerConCarpetaPorUsuario($usuario_id);
        $flash = $_SESSION['dashboard_flash'] ?? null;
        unset($_SESSION['dashboard_flash']);
        $pageTitle = 'Mis documentos';
        require __DIR__ . '/../views/dashboard/documentos.php';
    }

    public function verDocumento()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $documentoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $database = new Database();
        $conexion = $database->connect();
        $documentoModel = new Documento($conexion);
        $documento = $documentoId > 0 ? $documentoModel->obtenerPorIdYUsuario($documentoId, $usuario_id) : false;

        if (!$documento) {
            http_response_code(404);
        }

        $pageTitle = $documento ? ($documento['titulo'] ?? 'Documento') : 'Documento no encontrado';
        require __DIR__ . '/../views/dashboard/ver_documento.php';
    }

    public function documentoCompartido()
    {
        $documentoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $token = trim($_GET['token'] ?? '');

        $database = new Database();
        $conexion = $database->connect();
        $documentoModel = new Documento($conexion);
        $documento = $documentoId > 0 ? $documentoModel->obtenerPorId($documentoId) : false;

        $documentoValido = $documento && hash_equals($this->buildShareToken($documento), $token);
        if (!$documentoValido) {
            http_response_code(404);
        }

        $pageTitle = $documentoValido ? ($documento['titulo'] ?? 'Documento') : 'Documento no disponible';
        require __DIR__ . '/../views/dashboard/documento_compartido.php';
    }

    private function procesarAcciones(int $usuario_id, Carpeta $carpetaModel, Documento $documentoModel): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $accion = $_POST['dashboard_action'] ?? '';
        if ($accion === '') {
            return;
        }

        switch ($accion) {
            case 'upload_document':
                [$creado, $mensaje] = $this->procesarSubidaDocumento($usuario_id, $documentoModel);
                $this->setFlash(
                    $creado ? 'success' : 'error',
                    $mensaje
                );
                break;
        }

        header("Location: " . BASE_URL . "/index.php?url=dashboard");
        exit;
    }

    private function procesarSubidaDocumento(int $usuario_id, Documento $documentoModel): array
    {
        if (empty($_FILES['document_file']) || ($_FILES['document_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [false, 'Selecciona un archivo antes de subirlo.'];
        }

        $file = $_FILES['document_file'];
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return [false, 'No se pudo subir el archivo.'];
        }

        $titulo = trim($_POST['doc_title'] ?? '');
        $carpetaId = isset($_POST['folder_id']) && $_POST['folder_id'] !== ''
            ? (int)$_POST['folder_id']
            : null;

        $originalName = $file['name'] ?? 'archivo';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', pathinfo($originalName, PATHINFO_FILENAME));
        $safeName = trim((string)$safeName, '-');
        if ($safeName === '') {
            $safeName = 'documento';
        }

        $finalName = $safeName . '-' . uniqid('', true) . ($extension !== '' ? '.' . $extension : '');
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/documentos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $absoluteFile = $uploadDir . '/' . $finalName;
        if (!move_uploaded_file($file['tmp_name'], $absoluteFile)) {
            return [false, 'No se pudo guardar el archivo en el servidor.'];
        }

        $relativePublicPath = BASE_URL . '/uploads/documentos/' . $finalName;
        $tituloFinal = $titulo !== '' ? $titulo : pathinfo($originalName, PATHINFO_FILENAME);
        $contenidoFinal = $this->leerContenidoSubido($absoluteFile, $originalName, $relativePublicPath, $extension);

        $creado = $documentoModel->crear($usuario_id, $tituloFinal, $carpetaId, $contenidoFinal);
        if (!$creado) {
            return [false, 'El archivo se subió pero no se pudo registrar como documento.'];
        }

        return [true, 'Documento subido y guardado en Mis documentos.'];
    }

    private function leerContenidoSubido(string $absoluteFile, string $originalName, string $publicPath, string $extension): string
    {
        $textExtensions = ['txt', 'md', 'csv', 'json', 'xml', 'html', 'css', 'js', 'php', 'log'];
        $contenido = '';

        if (in_array($extension, $textExtensions, true)) {
            $raw = @file_get_contents($absoluteFile);
            if ($raw !== false) {
                $contenido = trim(substr($raw, 0, 15000));
            }
        }

        if ($contenido === '') {
            $contenido = "Archivo original: {$originalName}\n"
                . "Ruta del archivo: {$publicPath}\n"
                . "Tipo de archivo: " . ($extension !== '' ? strtoupper($extension) : 'desconocido') . "\n\n"
                . "Este archivo se subió desde el dashboard. Si necesitas su contenido completo, abre el archivo original.";
        }

        return $contenido;
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['dashboard_flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    private function templateCatalogo(): array
    {
        return [
            'informe-general' => [
                'id' => 'informe-general',
                'titulo' => 'Informe general',
                'tipo' => 'Documento',
                'etiquetas' => ['Documentación', 'Apuntes'],
                'descripcion' => 'Documento base para redactar informes, apuntes o trabajos.',
                'icono' => 'document',
                'contenido' => "Informe\n\nResumen ejecutivo:\n- \n\nDesarrollo:\n- \n\nConclusiones:\n- \n\nSiguientes pasos:\n- ",
            ],
            'codigo-java-basico' => [
                'id' => 'codigo-java-basico',
                'titulo' => 'Código Java básico',
                'tipo' => 'Código',
                'etiquetas' => ['Java', 'Código'],
                'descripcion' => 'Plantilla con estructura inicial de un programa en Java.',
                'icono' => 'code',
                'contenido' => "public class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hola, MatrixCoders\");\n    }\n}\n",
            ],
            'plan-estudio' => [
                'id' => 'plan-estudio',
                'titulo' => 'Plan de estudio',
                'tipo' => 'Planificación',
                'etiquetas' => ['Organización', 'Estudio'],
                'descripcion' => 'Organiza objetivos, bloques y tareas de estudio por semana.',
                'icono' => 'plan',
                'contenido' => "Plan semanal de estudio\n\nObjetivo de la semana:\n- \n\nBloques:\n- Lunes:\n- Martes:\n- Miércoles:\n\nPendientes:\n- ",
            ],
            'brief-proyecto' => [
                'id' => 'brief-proyecto',
                'titulo' => 'Brief de proyecto',
                'tipo' => 'Proyecto',
                'etiquetas' => ['Equipo', 'Proyecto'],
                'descripcion' => 'Resume alcance, responsables y entregables de un proyecto.',
                'icono' => 'brief',
                'contenido' => "Brief del proyecto\n\nContexto:\n- \n\nObjetivo:\n- \n\nResponsables:\n- \n\nEntregables:\n- \n\nFechas clave:\n- ",
            ],
            'bbdd-sql-practica' => [
                'id' => 'bbdd-sql-practica',
                'titulo' => 'Práctica SQL',
                'tipo' => 'Base de datos',
                'etiquetas' => ['SQL', 'MySQL', 'BBDD'],
                'descripcion' => 'Plantilla para consultas, resultados esperados y validaciones de base de datos.',
                'icono' => 'code',
                'contenido' => "-- Practica SQL\n\n-- Objetivo:\n-- \n\n-- Consultas:\nSELECT * FROM tabla;\n\n-- Resultado esperado:\n-- \n\n-- Observaciones:\n-- \n",
            ],
            'diagrama-casos-uso' => [
                'id' => 'diagrama-casos-uso',
                'titulo' => 'Casos de uso',
                'tipo' => 'Análisis',
                'etiquetas' => ['UML', 'Entornos', 'Diseño'],
                'descripcion' => 'Organiza actores, casos de uso y reglas funcionales para proyectos DAW.',
                'icono' => 'brief',
                'contenido' => "Casos de uso\n\nActores:\n- \n\nCasos principales:\n- \n\nFlujo principal:\n- \n\nReglas de negocio:\n- \n",
            ],
            'ficha-despliegue' => [
                'id' => 'ficha-despliegue',
                'titulo' => 'Ficha de despliegue',
                'tipo' => 'Despliegue',
                'etiquetas' => ['Servidor', 'DAW', 'Producción'],
                'descripcion' => 'Checklist para desplegar proyectos web en local o producción.',
                'icono' => 'plan',
                'contenido' => "Ficha de despliegue\n\nEntorno:\n- \n\nDependencias:\n- \n\nVariables de entorno:\n- \n\nChecklist:\n- \n\nResultado final:\n- \n",
            ],
            'esquema-interfaces' => [
                'id' => 'esquema-interfaces',
                'titulo' => 'Esquema de interfaces',
                'tipo' => 'Frontend',
                'etiquetas' => ['UI', 'UX', 'Diseño'],
                'descripcion' => 'Plantilla para definir pantallas, componentes y comportamiento de la interfaz.',
                'icono' => 'document',
                'contenido' => "Esquema de interfaces\n\nPantallas:\n- \n\nComponentes:\n- \n\nEstados:\n- \n\nInteracciones:\n- \n",
            ],
            'api-rest-notas' => [
                'id' => 'api-rest-notas',
                'titulo' => 'API REST notas',
                'tipo' => 'Backend',
                'etiquetas' => ['API', 'PHP', 'Backend'],
                'descripcion' => 'Base para documentar endpoints, payloads y respuestas de una API REST.',
                'icono' => 'code',
                'contenido' => "API REST\n\nEndpoint:\n- Metodo:\n- Ruta:\n- Descripcion:\n\nRequest:\n{\n}\n\nResponse:\n{\n}\n",
            ],
        ];
    }

    private function buildShareToken(array $documento): string
    {
        return hash_hmac('sha256', (string)$documento['id'] . '|' . (string)$documento['titulo'], 'matrixcoders-share-key');
    }

    private function buildPublicShareUrl(array $documento): string
    {
        return $this->absoluteBaseUrl()
            . BASE_URL
            . '/index.php?url=documento-compartido&id='
            . (int)$documento['id']
            . '&token='
            . $this->buildShareToken($documento);
    }

    private function absoluteBaseUrl(): string
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
