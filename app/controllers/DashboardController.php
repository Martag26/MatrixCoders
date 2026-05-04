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

        $database = new Database();
        $conexion = $database->connect();
        $documentoModel = new Documento($conexion);
        $tareaModel = new Tarea($conexion);

        // Cargar el plan activo del usuario en sesión si no está ya cargado
        if (empty($_SESSION['usuario_plan'])) {
            $stmtPlan = $conexion->prepare("SELECT plan FROM suscripcion WHERE usuario_id = ? AND status = 'activa' LIMIT 1");
            $stmtPlan->execute([$usuario_id]);
            $filaPlan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
            $_SESSION['usuario_plan'] = $filaPlan['plan'] ?? null;
        }

        $calYear  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $calMonth = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
        if ($calMonth < 1)   $calMonth = 1;
        if ($calMonth > 12)  $calMonth = 12;
        if ($calYear < 2000) $calYear  = 2000;
        if ($calYear > 2100) $calYear  = 2100;

        $documentos = $documentoModel->obtenerConCarpetaPorUsuario($usuario_id);
        $documentosRecientes = array_slice($documentos, 0, 4);
        $tareasUsuario = $tareaModel->obtenerPorUsuario($usuario_id);
        $diasConTareas = $tareaModel->obtenerDiasConEventos($usuario_id, $calYear, $calMonth);

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

        // ── Perfil profesional (basado en cursos matriculados) ───────────────
        $catFreq   = [];
        $nivelFreq = [];
        foreach ($cursosEnProgreso as $c) {
            $cat = trim(strtolower($c['categoria'] ?? ''));
            if ($cat !== '') $catFreq[$cat] = ($catFreq[$cat] ?? 0) + 1;
            $niv = $c['nivel'] ?? '';
            if ($niv !== '') $nivelFreq[$niv] = ($nivelFreq[$niv] ?? 0) + 1;
        }
        arsort($catFreq);
        arsort($nivelFreq);

        $rolesKeywords = [
            'Desarrollador Frontend'         => ['javascript','html','css','react','vue','angular','frontend','typescript'],
            'Desarrollador Backend'          => ['php','python','java','node','backend','api','laravel','symfony','go','ruby'],
            'Full Stack Developer'           => ['fullstack','full stack','full-stack'],
            'Data Scientist / Analista'      => ['data','machine learning','ia','inteligencia artificial','análisis','estadística','pandas','r '],
            'DevOps / Cloud Engineer'        => ['docker','linux','cloud','devops','kubernetes','aws','ci/cd','ansible'],
            'Diseñador UX/UI'               => ['diseño','ux','ui','figma','prototipado','usabilidad','accesibilidad'],
            'Especialista en Bases de Datos' => ['sql','mysql','bases de datos','postgresql','mongodb','redis','oracle'],
            'Desarrollador Móvil'            => ['android','ios','swift','kotlin','flutter','react native','móvil'],
            'Desarrollador de Videojuegos'   => ['unity','unreal','videojuegos','game','godot','c#'],
        ];

        $haystack = strtolower(
            implode(' ', array_column($cursosEnProgreso, 'titulo')) . ' ' .
            implode(' ', array_column($cursosEnProgreso, 'descripcion')) . ' ' .
            implode(' ', array_keys($catFreq))
        );
        $roleScores = [];
        foreach ($rolesKeywords as $rol => $kws) {
            $score = 0;
            foreach ($kws as $kw) {
                $score += substr_count($haystack, $kw) * 2;
            }
            if ($score > 0) $roleScores[$rol] = $score;
        }
        arsort($roleScores);

        $perfilRol     = array_key_first($roleScores) ?? null;
        $perfilTopCats = array_slice($catFreq, 0, 4, true);
        $perfilNivel   = array_key_first($nivelFreq) ?? null;
        $perfilCursos  = count($cursosEnProgreso);
        // ─────────────────────────────────────────────────────────────────────

        $pageTitle = "Espacio de trabajo";
        $flash = $_SESSION['dashboard_flash'] ?? null;
        unset($_SESSION['dashboard_flash']);

        // Cargar la vista del panel principal con todas las variables preparadas
        require __DIR__ . '/../views/dashboard/index.php';
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

    public function tareas()
    {
        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $database = new Database();
        $conexion = $database->connect();
        $tareaModel = new Tarea($conexion);

        $tareas = $tareaModel->obtenerPanelUsuario($usuario_id);
        $resumen = [
            'total' => count($tareas),
            'pendientes' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'pendiente')),
            'proximas' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'proxima')),
            'vencidas' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'vencida')),
            'entregadas' => count(array_filter($tareas, fn($t) => ($t['estado_visual'] ?? '') === 'entregada')),
        ];

        $tareasPorCurso = [];
        foreach ($tareas as $tarea) {
            $cursoKey = (string)($tarea['curso'] ?? 'Curso');
            if (!isset($tareasPorCurso[$cursoKey])) {
                $tareasPorCurso[$cursoKey] = [];
            }
            $tareasPorCurso[$cursoKey][] = $tarea;
        }

        $pageTitle = 'Tareas';
        require __DIR__ . '/../views/dashboard/tareas.php';
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

    public function nubeApi(): void
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['usuario_id'])) {
            echo json_encode(['ok' => false, 'error' => 'No autenticado']); exit;
        }
        $uid = (int)$_SESSION['usuario_id'];

        // Detect action from multipart (file upload) or JSON body
        $action = $_POST['nube_action'] ?? null;
        if (!$action) {
            $body   = json_decode(file_get_contents('php://input'), true) ?? [];
            $action = $body['nube_action'] ?? '';
        } else {
            $body = $_POST;
        }

        $database = new Database();
        $db = $database->connect();

        switch ($action) {

            // ── Upload file ──────────────────────────────────────────────
            case 'subir_archivo':
                if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['ok' => false, 'error' => 'No se recibió el archivo o hubo un error en la subida.']); exit;
                }
                $file    = $_FILES['archivo'];
                $maxSize = 50 * 1024 * 1024;
                if ($file['size'] > $maxSize) {
                    echo json_encode(['ok' => false, 'error' => 'El archivo supera 50 MB.']); exit;
                }
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['pdf','doc','docx','zip','rar','txt','png','jpg','jpeg','gif','webp','mp4','mp3','wav','xlsx','pptx','csv'];
                if (!in_array($ext, $allowed, true)) {
                    echo json_encode(['ok' => false, 'error' => 'Tipo de archivo no permitido.']); exit;
                }
                $destDir = __DIR__ . '/../../public/uploads/documentos/';
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                $safeOrig = preg_replace('/[^a-z0-9_\-\.]/i', '_', basename($file['name']));
                $fname    = 'u' . $uid . '_' . uniqid() . '.' . $ext;
                $destPath = $destDir . $fname;
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el archivo.']); exit;
                }
                $nombre    = trim($body['nombre'] ?? '') ?: pathinfo($file['name'], PATHINFO_FILENAME);
                $carpetaId = !empty($body['carpeta_id']) ? (int)$body['carpeta_id'] : null;
                $rutaPub   = '/uploads/documentos/' . $fname;
                $contenido = "Archivo original: {$safeOrig}\nRuta del archivo: {$rutaPub}\nTipo de archivo: {$ext}";
                $stmt = $db->prepare("INSERT INTO documento (usuario_id, carpeta_id, titulo, contenido) VALUES (?,?,?,?)");
                $ok   = $stmt->execute([$uid, $carpetaId, $nombre, $contenido]);
                echo json_encode(['ok' => $ok, 'error' => $ok ? null : 'Error al registrar el archivo.']);
                break;

            // ── Move document to folder ──────────────────────────────────
            case 'mover_documento':
                $id        = (int)($body['id'] ?? 0);
                $carpetaId = isset($body['carpeta_id']) && $body['carpeta_id'] !== '' ? (int)$body['carpeta_id'] : null;
                if (!$id) { echo json_encode(['ok' => false, 'error' => 'ID inválido.']); exit; }
                // Verify ownership
                $chk = $db->prepare("SELECT id FROM documento WHERE id = ? AND usuario_id = ?");
                $chk->execute([$id, $uid]);
                if (!$chk->fetch()) { echo json_encode(['ok' => false, 'error' => 'Documento no encontrado.']); exit; }
                $stmt = $db->prepare("UPDATE documento SET carpeta_id = ? WHERE id = ? AND usuario_id = ?");
                $ok   = $stmt->execute([$carpetaId, $id, $uid]);
                echo json_encode(['ok' => $ok]);
                break;

            // ── Delete document ──────────────────────────────────────────
            case 'eliminar_documento':
                $id = (int)($body['id'] ?? 0);
                if (!$id) { echo json_encode(['ok' => false, 'error' => 'ID inválido.']); exit; }
                $chk = $db->prepare("SELECT contenido FROM documento WHERE id = ? AND usuario_id = ?");
                $chk->execute([$id, $uid]);
                $doc = $chk->fetch(PDO::FETCH_ASSOC);
                if (!$doc) { echo json_encode(['ok' => false, 'error' => 'Documento no encontrado.']); exit; }
                // Delete physical file if exists
                if (preg_match('/Ruta del archivo:\s*(\S+)/i', $doc['contenido'], $m)) {
                    $abs = __DIR__ . '/../../public/' . ltrim($m[1], '/');
                    if (file_exists($abs)) @unlink($abs);
                }
                $stmt = $db->prepare("DELETE FROM documento WHERE id = ? AND usuario_id = ?");
                $ok   = $stmt->execute([$id, $uid]);
                echo json_encode(['ok' => $ok]);
                break;

            // ── Delete folder ────────────────────────────────────────────
            case 'eliminar_carpeta':
                $id = (int)($body['id'] ?? 0);
                if (!$id) { echo json_encode(['ok' => false, 'error' => 'ID inválido.']); exit; }
                // Verify ownership
                $chk = $db->prepare("SELECT id FROM carpeta WHERE id = ? AND usuario_id = ?");
                $chk->execute([$id, $uid]);
                if (!$chk->fetch()) { echo json_encode(['ok' => false, 'error' => 'Carpeta no encontrada.']); exit; }
                // Unassign documents (don't delete them)
                $db->prepare("UPDATE documento SET carpeta_id = NULL WHERE carpeta_id = ? AND usuario_id = ?")->execute([$id, $uid]);
                $stmt = $db->prepare("DELETE FROM carpeta WHERE id = ? AND usuario_id = ?");
                $ok   = $stmt->execute([$id, $uid]);
                echo json_encode(['ok' => $ok]);
                break;

            default:
                echo json_encode(['ok' => false, 'error' => 'Acción desconocida.']);
        }
        exit;
    }

    private function buildShareToken(array $documento): string
    {
        return hash('sha256', $documento['id'] . '|' . ($documento['usuario_id'] ?? '') . '|mc_share_secret');
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['dashboard_flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

}
