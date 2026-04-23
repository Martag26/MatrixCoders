<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';

class CrmController
{
    private PDO    $db;
    private array  $usuario;
    private bool   $esSuperAdmin;
    private bool   $esAdmin;
    private bool   $esModerador;

    /* Dynamic URL bases — set in constructor based on entry point */
    public string $crmBase;
    public string $crmApiUrl;
    public string $crmFormBase;
    public string $crmFormHidden;
    public string $crmLogoutUrl;
    public string $crmSiteUrl;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $standalone = defined('CRM_STANDALONE') && CRM_STANDALONE;

        if ($standalone) {
            $this->crmBase       = '/matrixcoders/admin/index.php?sec=';
            $this->crmApiUrl     = '/matrixcoders/admin/index.php?crm_api=1';
            $this->crmFormBase   = '/matrixcoders/admin/index.php';
            $this->crmFormHidden = '';
            $this->crmLogoutUrl  = '/matrixcoders/admin/index.php?auth=logout';
            $this->crmSiteUrl    = BASE_URL . '/index.php';
        } else {
            $this->crmBase       = BASE_URL . '/index.php?url=crm&sec=';
            $this->crmApiUrl     = BASE_URL . '/index.php?url=crm-api';
            $this->crmFormBase   = BASE_URL . '/index.php';
            $this->crmFormHidden = '<input type="hidden" name="url" value="crm">';
            $this->crmLogoutUrl  = BASE_URL . '/index.php?url=logout';
            $this->crmSiteUrl    = BASE_URL . '/index.php';
        }

        if (empty($_SESSION['usuario_id'])) {
            if ($standalone) {
                header('Location: /matrixcoders/admin/index.php');
            } else {
                header('Location: ' . BASE_URL . '/index.php?url=login');
            }
            exit;
        }

        $database = new Database();
        $this->db = $database->connect();

        $this->runCrmMigrations();

        $stmt = $this->db->prepare('SELECT * FROM usuario WHERE id = ?');
        $stmt->execute([(int)$_SESSION['usuario_id']]);
        $this->usuario = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $rol            = $this->usuario['rol'] ?? 'USUARIO';
        $this->esSuperAdmin = ($rol === 'ADMINISTRADOR' && !empty($this->usuario['es_superadmin']));
        $this->esAdmin      = ($rol === 'ADMINISTRADOR');
        $this->esModerador  = !empty($this->usuario['es_moderador']);

        if (!$this->esAdmin && !$this->esModerador) {
            if ($standalone) {
                $_SESSION = [];
                session_destroy();
                header('Location: /matrixcoders/admin/index.php?error=acceso');
            } else {
                header('Location: ' . BASE_URL . '/index.php?url=dashboard');
            }
            exit;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Migrations helper                                                   */
    /* ------------------------------------------------------------------ */
    private function runCrmMigrations(): void
    {
        // Run ALTER TABLE statements FIRST so columns exist before the SQL file uses them
        foreach ([
            'ALTER TABLE usuario ADD COLUMN es_superadmin  INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE usuario ADD COLUMN es_moderador   INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE usuario ADD COLUMN notificaciones INTEGER NOT NULL DEFAULT 1',
            'ALTER TABLE curso   ADD COLUMN activo         INTEGER NOT NULL DEFAULT 1',
            'ALTER TABLE curso   ADD COLUMN instructor_id  INTEGER DEFAULT NULL',
            'ALTER TABLE curso   ADD COLUMN orden          INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE campana_crm ADD COLUMN descuento_pct REAL NOT NULL DEFAULT 0',
            'ALTER TABLE leccion ADD COLUMN orden INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE unidad  ADD COLUMN orden INTEGER NOT NULL DEFAULT 0',
        ] as $sql) {
            try { $this->db->exec($sql); } catch (Exception $e) { /* column already exists */ }
        }

        // Now run the SQL migration file (may reference columns added above)
        $crmSql = __DIR__ . '/../data/crm_migrate.sql';
        if (file_exists($crmSql)) {
            try {
                $this->db->exec(file_get_contents($crmSql));
            } catch (Exception $e) { /* idempotent — tables may already exist */ }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Main router                                                         */
    /* ------------------------------------------------------------------ */
    public function index(): void
    {
        $sec = $_GET['sec'] ?? 'dashboard';

        if ($sec === 'usuarios' && !$this->esAdmin) $sec = 'dashboard';
        if (in_array($sec, ['cursos', 'campanas', 'editor', 'logs'], true) && !$this->esAdmin) {
            $sec = 'comunicacion';
        }

        $titulos = [
            'dashboard'    => 'Dashboard',
            'usuarios'     => 'Usuarios',
            'cursos'       => 'Cursos',
            'editor'       => 'Editor de Curso',
            'campanas'     => 'Campañas',
            'comunicacion' => 'Comunicación',
            'logs'         => 'Logs de Actividad',
        ];

        $titulo = $titulos[$sec] ?? 'Dashboard';

        $data = match ($sec) {
            'dashboard'    => $this->getDashboardData(),
            'usuarios'     => $this->getUsuariosData(),
            'cursos'       => $this->getCursosData(),
            'editor'       => $this->getEditorData(),
            'campanas'     => $this->getCampanasData(),
            'comunicacion' => $this->getComunicacionData(),
            'logs'         => $this->getLogsData(),
            default        => $this->getDashboardData(),
        };

        $usuario       = $this->usuario;
        $esSuperAdmin  = $this->esSuperAdmin;
        $esAdmin       = $this->esAdmin;
        $esModerador   = $this->esModerador;
        $seccion       = $sec;
        $crmBase       = $this->crmBase;
        $crmApiUrl     = $this->crmApiUrl;
        $crmFormBase   = $this->crmFormBase;
        $crmFormHidden = $this->crmFormHidden;
        $crmLogoutUrl  = $this->crmLogoutUrl;
        $crmSiteUrl    = $this->crmSiteUrl;

        extract($data);

        include __DIR__ . '/../views/crm/layout/base.php';
    }

    /* ------------------------------------------------------------------ */
    /*  API router                                                          */
    /* ------------------------------------------------------------------ */
    public function api(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $action = $_GET['action'] ?? '';

        $result = match ($action) {
            'crear_usuario'         => $this->apiCrearUsuario(),
            'editar_usuario'        => $this->apiEditarUsuario(),
            'eliminar_usuario'      => $this->apiEliminarUsuario(),
            'toggle_curso'          => $this->apiToggleCurso(),
            'actualizar_curso'      => $this->apiActualizarCurso(),
            'guardar_unidades'      => $this->apiGuardarUnidades(),
            'crear_unidad'          => $this->apiCrearUnidad(),
            'eliminar_unidad'       => $this->apiEliminarUnidad(),
            'crear_leccion'         => $this->apiCrearLeccion(),
            'editar_leccion'        => $this->apiEditarLeccion(),
            'eliminar_leccion'      => $this->apiEliminarLeccion(),
            'guardar_examen'        => $this->apiGuardarExamen(),
            'crear_campana'         => $this->apiCrearCampana(),
            'editar_campana'        => $this->apiEditarCampana(),
            'eliminar_campana'      => $this->apiEliminarCampana(),
            'mensajes_conversacion' => $this->apiMensajesConversacion(),
            'enviar_mensaje'        => $this->apiEnviarMensaje(),
            'incidencia_responder'  => $this->apiResponderIncidencia(),
            'incidencia_estado'     => $this->apiEstadoIncidencia(),
            'crear_incidencia'      => $this->apiCrearIncidencia(),
            'actualizar_perfil'     => $this->apiActualizarPerfil(),
            'cambiar_contrasena'    => $this->apiCambiarContrasena(),
            'actualizar_ajustes'    => $this->apiActualizarAjustes(),
            'asignar_instructor'    => $this->apiAsignarInstructor(),
            default                 => ['ok' => false, 'error' => 'Acción no reconocida'],
        };

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /* ================================================================== */
    /*  DATA LOADERS                                                        */
    /* ================================================================== */

    private function getDashboardData(): array
    {
        $db = $this->db;

        $totalUsuarios    = (int)$db->query('SELECT COUNT(*) FROM usuario')->fetchColumn();
        $totalCursos      = (int)$db->query('SELECT COUNT(*) FROM curso')->fetchColumn();
        $cursosActivos    = (int)$db->query("SELECT COUNT(*) FROM curso WHERE activo=1")->fetchColumn();
        $totalCampanas    = (int)$db->query("SELECT COUNT(*) FROM campana_crm WHERE activa=1 AND (fecha_fin IS NULL OR fecha_fin >= date('now'))")->fetchColumn();
        $totalMensajes    = (int)$db->query("SELECT COUNT(*) FROM mensaje_curso")->fetchColumn();
        $nuevosEsteMes    = (int)$db->query("SELECT COUNT(*) FROM usuario WHERE strftime('%Y-%m',creado_en)=strftime('%Y-%m','now')")->fetchColumn();
        $totalMatriculas  = (int)$db->query("SELECT COUNT(*) FROM matricula")->fetchColumn();
        try {
            $incidenciasAbiertas = (int)$db->query("SELECT COUNT(*) FROM incidencia WHERE estado='abierta'")->fetchColumn();
        } catch (Exception $e) { $incidenciasAbiertas = 0; }

        $topCursos = $db->query("
            SELECT c.titulo, COUNT(m.id) AS total
            FROM curso c LEFT JOIN matricula m ON m.curso_id = c.id
            GROUP BY c.id ORDER BY total DESC LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        $porRol = $db->query("
            SELECT
              CASE
                WHEN es_superadmin=1 THEN 'Superadmin'
                WHEN rol='ADMINISTRADOR' THEN 'Administrador'
                WHEN es_moderador=1 THEN 'Moderador'
                WHEN rol='EDITOR' THEN 'Instructor'
                ELSE 'Alumno'
              END as etiqueta,
              COUNT(*) as total
            FROM usuario GROUP BY etiqueta
        ")->fetchAll(PDO::FETCH_ASSOC);

        $actividad6m = $db->query("
            SELECT strftime('%Y-%m', creado_en) AS mes, COUNT(*) AS total
            FROM usuario WHERE creado_en >= date('now','-6 months')
            GROUP BY mes ORDER BY mes ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        try {
            $recientes = $db->query("
                SELECT a.titulo, a.tipo, a.creado_en, u.nombre AS usuario_nombre
                FROM crm_actividad a LEFT JOIN usuario u ON u.id=a.usuario_id
                ORDER BY a.creado_en DESC LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $recientes = []; }

        $porNivel = $db->query("
            SELECT COALESCE(nivel,'Sin nivel') AS nivel, COUNT(*) AS total
            FROM curso GROUP BY nivel ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $totalInstructores = (int)$db->query("SELECT COUNT(*) FROM usuario WHERE rol='EDITOR'")->fetchColumn();
        $nuevosEstaSemana  = (int)$db->query("SELECT COUNT(*) FROM usuario WHERE creado_en >= date('now','-7 days')")->fetchColumn();

        $recentUsersStmt = $db->query("
            SELECT u.*, (SELECT COUNT(*) FROM matricula WHERE usuario_id=u.id) AS cursos_count
            FROM usuario u ORDER BY u.creado_en DESC LIMIT 5
        ");
        $recentUsers = $recentUsersStmt ? $recentUsersStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return compact('totalUsuarios','totalCursos','cursosActivos','totalCampanas',
                       'totalMensajes','nuevosEsteMes','totalMatriculas','incidenciasAbiertas',
                       'topCursos','porRol','actividad6m','recientes','porNivel',
                       'totalInstructores','nuevosEstaSemana','recentUsers');
    }

    private function getLogsData(): array
    {
        $page  = max(1, (int)($_GET['pag'] ?? 1));
        $limit = 25;
        $q     = trim($_GET['q'] ?? '');
        $tipo  = $_GET['tipo'] ?? '';

        $where = 'WHERE 1=1'; $params = [];
        if ($q)    { $where .= ' AND (a.titulo LIKE ? OR u.nombre LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        if ($tipo) { $where .= ' AND a.tipo = ?'; $params[] = $tipo; }

        $total = $this->db->prepare("SELECT COUNT(*) FROM crm_actividad a LEFT JOIN usuario u ON u.id=a.usuario_id $where");
        $total->execute($params);
        $totalRows = (int)$total->fetchColumn();
        $totalPags = (int)ceil($totalRows / $limit);
        $offset    = ($page - 1) * $limit;

        $stmt = $this->db->prepare("
            SELECT a.*, u.nombre AS usuario_nombre, u.email AS usuario_email
            FROM crm_actividad a
            LEFT JOIN usuario u ON u.id = a.usuario_id
            $where
            ORDER BY a.creado_en DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tipos = $this->db->query("SELECT DISTINCT tipo FROM crm_actividad ORDER BY tipo")->fetchAll(PDO::FETCH_COLUMN);

        return compact('logs','totalRows','totalPags','page','q','tipo','tipos');
    }

    private function getUsuariosData(): array
    {
        if (!$this->esAdmin) return [];
        $page     = max(1, (int)($_GET['pag'] ?? 1));
        $perPage  = in_array((int)($_GET['per'] ?? 15), [15,25,50,100]) ? (int)($_GET['per'] ?? 15) : 15;
        $limit    = $perPage;
        $q        = trim($_GET['q'] ?? '');
        $rol      = $_GET['rol'] ?? '';
        $periodo  = $_GET['periodo'] ?? '';

        $where = 'WHERE 1=1';
        $params = [];
        if ($q) {
            $where .= ' AND (u.nombre LIKE ? OR u.email LIKE ?)';
            $params[] = "%$q%"; $params[] = "%$q%";
        }
        if ($rol === 'superadmin') {
            $where .= " AND u.rol='ADMINISTRADOR' AND u.es_superadmin=1";
        } elseif ($rol === 'admin') {
            $where .= " AND u.rol='ADMINISTRADOR' AND u.es_superadmin=0";
        } elseif ($rol === 'moderador') {
            $where .= " AND u.es_moderador=1";
        } elseif ($rol === 'instructor') {
            $where .= " AND u.rol='EDITOR'";
        } elseif ($rol === 'alumno') {
            $where .= " AND u.rol='USUARIO' AND u.es_moderador=0";
        }
        if ($periodo === 'hoy') {
            $where .= " AND date(u.creado_en) = date('now')";
        } elseif ($periodo === 'semana') {
            $where .= " AND u.creado_en >= date('now','-7 days')";
        } elseif ($periodo === 'mes') {
            $where .= " AND u.creado_en >= date('now','-30 days')";
        }

        $total = $this->db->prepare("SELECT COUNT(*) FROM usuario u $where");
        $total->execute($params);
        $totalRows = (int)$total->fetchColumn();
        $totalPags = max(1, (int)ceil($totalRows / $limit));
        $offset    = ($page - 1) * $limit;

        $stmt = $this->db->prepare("
            SELECT u.*,
                   (SELECT COUNT(*) FROM matricula WHERE usuario_id=u.id) AS cursos_count
            FROM usuario u $where
            ORDER BY u.creado_en DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cursos = $this->db->query("SELECT id, titulo FROM curso ORDER BY titulo ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Stats by role for the strip
        $statsRow = $this->db->query("
            SELECT
              COUNT(*) AS total,
              SUM(CASE WHEN rol='USUARIO' AND es_moderador=0 THEN 1 ELSE 0 END) AS alumnos,
              SUM(CASE WHEN rol='EDITOR' THEN 1 ELSE 0 END) AS instructores,
              SUM(CASE WHEN rol='ADMINISTRADOR' THEN 1 ELSE 0 END) AS admins,
              SUM(CASE WHEN creado_en >= date('now','-7 days') THEN 1 ELSE 0 END) AS nuevos7d
            FROM usuario
        ")->fetch(PDO::FETCH_ASSOC);

        return compact('usuarios','totalRows','totalPags','page','perPage','q','rol','periodo','cursos','statsRow');
    }

    private function getCursosData(): array
    {
        $page    = max(1, (int)($_GET['pag'] ?? 1));
        $perPage = in_array((int)($_GET['per'] ?? 12), [6,12,24,48]) ? (int)($_GET['per'] ?? 12) : 12;
        $limit   = $perPage;
        $q       = trim($_GET['q'] ?? '');
        $cat     = $_GET['cat'] ?? '';
        $nivel   = $_GET['nivel'] ?? '';

        $where = 'WHERE 1=1'; $params = [];
        if ($q)     { $where .= ' AND c.titulo LIKE ?'; $params[] = "%$q%"; }
        if ($cat)   { $where .= ' AND c.categoria = ?'; $params[] = $cat; }
        if ($nivel) { $where .= ' AND c.nivel = ?';     $params[] = $nivel; }

        $cntStmt = $this->db->prepare("SELECT COUNT(*) FROM curso c $where");
        $cntStmt->execute($params);
        $totalRows = (int)$cntStmt->fetchColumn();
        $totalPags = max(1, (int)ceil($totalRows / $limit));
        $offset    = ($page - 1) * $limit;

        try {
            $stmt = $this->db->prepare("
                SELECT c.*,
                       (SELECT COUNT(*) FROM matricula WHERE curso_id=c.id) AS alumnos,
                       (SELECT GROUP_CONCAT(u2.nombre, ', ') FROM curso_instructor ci JOIN usuario u2 ON u2.id=ci.usuario_id WHERE ci.curso_id=c.id) AS instructor_nombre,
                       (SELECT GROUP_CONCAT(ci2.usuario_id) FROM curso_instructor ci2 WHERE ci2.curso_id=c.id) AS instructor_ids_str,
                       u.nombre AS instructor_nombre_legacy,
                       (SELECT COUNT(*) FROM campana_curso cc
                        JOIN campana_crm cm ON cm.id=cc.campana_id
                        WHERE cc.curso_id=c.id AND cm.activa=1
                          AND (cm.fecha_fin IS NULL OR cm.fecha_fin >= date('now'))) AS campana_activa,
                       (SELECT cc2.descuento FROM campana_curso cc2
                        JOIN campana_crm cm2 ON cm2.id=cc2.campana_id
                        WHERE cc2.curso_id=c.id AND cm2.activa=1
                          AND (cm2.fecha_fin IS NULL OR cm2.fecha_fin >= date('now'))
                        LIMIT 1) AS descuento_activo
                FROM curso c
                LEFT JOIN usuario u ON u.id=c.instructor_id
                $where ORDER BY c.id DESC LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            /* Fallback: query simple sin subqueries avanzadas */
            $stmt = $this->db->prepare("
                SELECT c.*, u.nombre AS instructor_nombre_legacy,
                       0 AS alumnos, NULL AS instructor_nombre,
                       NULL AS instructor_ids_str, 0 AS campana_activa, 0 AS descuento_activo
                FROM curso c
                LEFT JOIN usuario u ON u.id=c.instructor_id
                $where ORDER BY c.id DESC LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $categorias = $this->db->query("SELECT DISTINCT categoria FROM curso WHERE categoria IS NOT NULL ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
        $niveles    = $this->db->query("SELECT DISTINCT nivel FROM curso WHERE nivel IS NOT NULL ORDER BY nivel")->fetchAll(PDO::FETCH_COLUMN);
        $instructores = $this->db->query("SELECT id, nombre FROM usuario WHERE rol='EDITOR' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

        $cursosStats = $this->db->query("
            SELECT
              COUNT(*) AS total,
              SUM(CASE WHEN activo=1 THEN 1 ELSE 0 END) AS activos,
              SUM(CASE WHEN activo=0 THEN 1 ELSE 0 END) AS inactivos,
              SUM(CASE WHEN precio=0 OR precio IS NULL THEN 1 ELSE 0 END) AS gratis,
              (SELECT COUNT(*) FROM matricula) AS total_matriculas
            FROM curso
        ")->fetch(PDO::FETCH_ASSOC);

        return compact('cursos','totalRows','totalPags','page','perPage','q','cat','nivel','categorias','niveles','instructores','cursosStats');
    }

    private function getEditorData(): array
    {
        $cursoId = (int)($_GET['id'] ?? 0);
        if (!$cursoId) {
            header('Location: ' . BASE_URL . '/index.php?url=crm&sec=cursos');
            exit;
        }

        $stmt = $this->db->prepare('SELECT * FROM curso WHERE id = ?');
        $stmt->execute([$cursoId]);
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$curso) {
            header('Location: ' . BASE_URL . '/index.php?url=crm&sec=cursos');
            exit;
        }

        $stmt = $this->db->prepare('SELECT * FROM unidad WHERE curso_id=? ORDER BY orden,id');
        $stmt->execute([$cursoId]);
        $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($unidades as &$u) {
            $ls = $this->db->prepare('SELECT * FROM leccion WHERE unidad_id=? ORDER BY orden,id');
            $ls->execute([$u['id']]);
            $u['lecciones'] = $ls->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($u);

        $stmt = $this->db->prepare('SELECT e.*, (SELECT COUNT(*) FROM pregunta WHERE examen_id=e.id) AS total_preguntas FROM examen e WHERE e.curso_id=?');
        $stmt->execute([$cursoId]);
        $examen = $stmt->fetch(PDO::FETCH_ASSOC);

        $preguntas = [];
        if ($examen) {
            $stmt = $this->db->prepare('SELECT * FROM pregunta WHERE examen_id=? ORDER BY orden,id');
            $stmt->execute([$examen['id']]);
            $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($preguntas as &$p) {
                $os = $this->db->prepare('SELECT * FROM opcion WHERE pregunta_id=? ORDER BY orden,id');
                $os->execute([$p['id']]);
                $p['opciones'] = $os->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($p);
        }

        $instructores = $this->db->query("SELECT id, nombre FROM usuario WHERE rol='EDITOR' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

        $stmtIns = $this->db->prepare("SELECT usuario_id FROM curso_instructor WHERE curso_id=?");
        $stmtIns->execute([$cursoId]);
        $instructoresAsignados = $stmtIns->fetchAll(PDO::FETCH_COLUMN);

        return compact('curso','unidades','examen','preguntas','instructores','instructoresAsignados');
    }

    private function getCampanasData(): array
    {
        $page  = max(1, (int)($_GET['pag'] ?? 1));
        $limit = 9;
        $q     = trim($_GET['q'] ?? '');
        $tipo  = $_GET['tipo'] ?? '';
        $estado = $_GET['estado'] ?? '';

        $where = 'WHERE 1=1'; $params = [];
        if ($q)    { $where .= ' AND (titulo LIKE ? OR cuerpo LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        if ($tipo) { $where .= ' AND tipo=?'; $params[] = $tipo; }
        if ($estado === 'activa')  { $where .= " AND activa=1 AND (fecha_fin IS NULL OR fecha_fin>=date('now'))"; }
        if ($estado === 'inactiva'){ $where .= " AND (activa=0 OR (fecha_fin IS NOT NULL AND fecha_fin<date('now')))"; }

        $cnt = $this->db->prepare("SELECT COUNT(*) FROM campana_crm $where");
        $cnt->execute($params);
        $totalRows = (int)$cnt->fetchColumn();
        $totalPags = (int)ceil($totalRows / $limit);
        $offset    = ($page - 1) * $limit;

        $stmt = $this->db->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM campana_curso WHERE campana_id=c.id) AS cursos_count
            FROM campana_crm c $where
            ORDER BY c.creado_en DESC LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $campanas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($campanas as &$cam) {
            $cs = $this->db->prepare("SELECT cr.titulo, cc.descuento FROM campana_curso cc JOIN curso cr ON cr.id=cc.curso_id WHERE cc.campana_id=?");
            $cs->execute([$cam['id']]);
            $cam['cursos_vinculados'] = $cs->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($cam);

        $cursos = $this->db->query("SELECT id, titulo FROM curso WHERE activo=1 ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = $this->db->query("SELECT id, nombre, email FROM usuario ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

        return compact('campanas','totalRows','totalPags','page','q','tipo','estado','cursos','usuarios');
    }

    private function getComunicacionData(): array
    {
        $tab        = $_GET['tab'] ?? 'mensajes';
        $cursoFiltro = (int)($_GET['curso'] ?? 0);
        $page       = max(1, (int)($_GET['pag'] ?? 1));

        $cursosConMensajes = $this->db->query("
            SELECT DISTINCT c.id, c.titulo, COUNT(mc.id) AS total,
                   SUM(CASE WHEN mc.leido=0 THEN 1 ELSE 0 END) AS no_leidos
            FROM mensaje_curso mc JOIN curso c ON c.id=mc.curso_id
            GROUP BY c.id ORDER BY no_leidos DESC, total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $mensajes = [];
        $cursoSeleccionado = null;
        if ($cursoFiltro) {
            $stmt = $this->db->prepare("
                SELECT mc.*, u.nombre AS remitente_nombre, u.rol AS remitente_rol,
                       d.nombre AS destinatario_nombre
                FROM mensaje_curso mc
                JOIN usuario u ON u.id=mc.remitente_id
                LEFT JOIN usuario d ON d.id=mc.destinatario_id
                WHERE mc.curso_id=? ORDER BY mc.creado_en ASC
            ");
            $stmt->execute([$cursoFiltro]);
            $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->db->prepare("UPDATE mensaje_curso SET leido=1 WHERE curso_id=?")->execute([$cursoFiltro]);

            $sc = $this->db->prepare("SELECT * FROM curso WHERE id=?");
            $sc->execute([$cursoFiltro]);
            $cursoSeleccionado = $sc->fetch(PDO::FETCH_ASSOC);
        }

        $limit = 15;
        $cntInc = $this->db->query("SELECT COUNT(*) FROM incidencia")->fetchColumn();
        $totalPagsInc = (int)ceil($cntInc / $limit);
        $pageInc = max(1, (int)($_GET['pinc'] ?? 1));
        $stmt = $this->db->prepare("
            SELECT i.*, u.nombre AS usuario_nombre, a.nombre AS asignado_nombre
            FROM incidencia i
            JOIN usuario u ON u.id=i.usuario_id
            LEFT JOIN usuario a ON a.id=i.asignado_a
            ORDER BY
              CASE i.prioridad WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END,
              i.creado_en DESC
            LIMIT $limit OFFSET " . (($pageInc-1)*$limit)
        );
        $stmt->execute();
        $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $moderadores = $this->db->query("SELECT id, nombre FROM usuario WHERE rol='ADMINISTRADOR' OR es_moderador=1 ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

        return compact('tab','cursosConMensajes','mensajes','cursoSeleccionado','cursoFiltro',
                       'incidencias','totalPagsInc','pageInc','moderadores');
    }

    private function getPerfilData(): array
    {
        return ['usuarioPerfil' => $this->usuario];
    }

    /* ================================================================== */
    /*  API HANDLERS                                                        */
    /* ================================================================== */

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?: $_POST;
    }

    private function apiCrearUsuario(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d = $this->input();
        $nombre = trim($d['nombre'] ?? '');
        $email  = trim($d['email'] ?? '');
        $pass   = $d['password'] ?? '';
        $rolSel = $d['rol'] ?? 'alumno';

        if (!$nombre || !$email || !$pass) return ['ok' => false, 'error' => 'Faltan campos'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'Email inválido'];
        if (strlen($pass) < 6) return ['ok' => false, 'error' => 'Contraseña mínimo 6 caracteres'];

        $check = $this->db->prepare("SELECT id FROM usuario WHERE email=?");
        $check->execute([$email]);
        if ($check->fetch()) return ['ok' => false, 'error' => 'Email ya registrado'];

        [$rol, $superadmin, $moderador] = match ($rolSel) {
            'superadmin' => ['ADMINISTRADOR', 1, 0],
            'admin'      => ['ADMINISTRADOR', 0, 0],
            'moderador'  => ['USUARIO', 0, 1],
            'instructor' => ['EDITOR', 0, 0],
            default      => ['USUARIO', 0, 0],
        };

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuario (nombre,email,contraseña,rol,es_superadmin,es_moderador) VALUES(?,?,?,?,?,?)");
        $stmt->execute([$nombre, $email, $hash, $rol, $superadmin, $moderador]);
        $id = $this->db->lastInsertId();

        $this->logActividad("Usuario creado: $nombre ($email)", 'success');
        return ['ok' => true, 'id' => $id, 'mensaje' => "Usuario '$nombre' creado correctamente"];
    }

    private function apiEditarUsuario(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];

        $nombre = trim($d['nombre'] ?? '');
        $email  = trim($d['email'] ?? '');
        $rolSel = $d['rol'] ?? 'alumno';
        if (!$nombre || !$email) return ['ok' => false, 'error' => 'Faltan campos'];

        $check = $this->db->prepare("SELECT id FROM usuario WHERE email=? AND id!=?");
        $check->execute([$email, $id]);
        if ($check->fetch()) return ['ok' => false, 'error' => 'Email ya registrado'];

        [$rol, $superadmin, $moderador] = match ($rolSel) {
            'superadmin' => ['ADMINISTRADOR', 1, 0],
            'admin'      => ['ADMINISTRADOR', 0, 0],
            'moderador'  => ['USUARIO', 0, 1],
            'instructor' => ['EDITOR', 0, 0],
            default      => ['USUARIO', 0, 0],
        };

        $params = [$nombre, $email, $rol, $superadmin, $moderador];
        $sql = "UPDATE usuario SET nombre=?,email=?,rol=?,es_superadmin=?,es_moderador=?";

        if (!empty($d['password'])) {
            if (strlen($d['password']) < 6) return ['ok' => false, 'error' => 'Contraseña mínimo 6 caracteres'];
            $sql .= ',contraseña=?';
            $params[] = password_hash($d['password'], PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id=?';
        $params[] = $id;

        $this->db->prepare($sql)->execute($params);
        return ['ok' => true, 'mensaje' => 'Usuario actualizado correctamente'];
    }

    private function apiEliminarUsuario(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        if ($id === (int)$this->usuario['id']) return ['ok' => false, 'error' => 'No puedes eliminarte a ti mismo'];

        $u = $this->db->prepare("SELECT nombre FROM usuario WHERE id=?");
        $u->execute([$id]);
        $row = $u->fetch(PDO::FETCH_ASSOC);
        if (!$row) return ['ok' => false, 'error' => 'Usuario no encontrado'];

        $this->db->prepare("DELETE FROM usuario WHERE id=?")->execute([$id]);
        $this->logActividad("Usuario eliminado: {$row['nombre']}", 'warning');
        return ['ok' => true, 'mensaje' => "Usuario eliminado"];
    }

    private function apiToggleCurso(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];

        $stmt = $this->db->prepare("SELECT activo, titulo FROM curso WHERE id=?");
        $stmt->execute([$id]);
        $cur = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cur) return ['ok' => false, 'error' => 'Curso no encontrado'];

        $nuevo = $cur['activo'] ? 0 : 1;
        $this->db->prepare("UPDATE curso SET activo=? WHERE id=?")->execute([$nuevo, $id]);
        return ['ok' => true, 'activo' => $nuevo, 'mensaje' => "Curso " . ($nuevo ? 'activado' : 'desactivado')];
    }

    private function apiActualizarCurso(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];

        $titulo      = trim($d['titulo'] ?? '');
        $descripcion = trim($d['descripcion'] ?? '');
        $precio      = (float)($d['precio'] ?? 0);
        $nivel       = $d['nivel'] ?? null;
        $categoria   = $d['categoria'] ?? null;
        $destacado   = (int)($d['destacado'] ?? 0);

        if (!$titulo) return ['ok' => false, 'error' => 'El título es obligatorio'];

        $this->db->prepare("UPDATE curso SET titulo=?,descripcion=?,precio=?,nivel=?,categoria=?,destacado=? WHERE id=?")
                 ->execute([$titulo, $descripcion, $precio, $nivel, $categoria, $destacado, $id]);

        return ['ok' => true, 'mensaje' => 'Curso actualizado correctamente'];
    }

    private function apiAsignarInstructor(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d = $this->input();
        $cursoId     = (int)($d['curso_id'] ?? 0);
        if (!$cursoId) return ['ok' => false, 'error' => 'ID de curso inválido'];

        // Accept array of instructor IDs
        $ids = array_values(array_filter(array_map('intval', (array)($d['instructor_ids'] ?? ($d['instructor_id'] ? [$d['instructor_id']] : [])))));

        // Update legacy column with first instructor
        $primary = $ids[0] ?? null;
        $this->db->prepare("UPDATE curso SET instructor_id=? WHERE id=?")->execute([$primary, $cursoId]);

        // Update junction table
        $this->db->prepare("DELETE FROM curso_instructor WHERE curso_id=?")->execute([$cursoId]);
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO curso_instructor (curso_id, usuario_id) VALUES(?,?)");
        foreach ($ids as $uid) { $stmt->execute([$cursoId, $uid]); }

        return ['ok' => true, 'mensaje' => count($ids) . ' instructor(es) asignado(s)'];
    }

    private function apiGuardarUnidades(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d = $this->input();
        $unidades = $d['unidades'] ?? [];
        foreach ($unidades as $idx => $u) {
            $this->db->prepare("UPDATE unidad SET orden=? WHERE id=?")->execute([$idx, (int)$u['id']]);
            foreach (($u['lecciones'] ?? []) as $lidx => $l) {
                $this->db->prepare("UPDATE leccion SET orden=? WHERE id=?")->execute([$lidx, (int)$l['id']]);
            }
        }
        return ['ok' => true, 'mensaje' => 'Orden guardado'];
    }

    private function apiCrearUnidad(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d = $this->input();
        $cursoId = (int)($d['curso_id'] ?? 0);
        $titulo  = trim($d['titulo'] ?? '');
        if (!$cursoId || !$titulo) return ['ok' => false, 'error' => 'Datos incompletos'];
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(orden),0)+1 FROM unidad WHERE curso_id=?");
        $stmt->execute([$cursoId]);
        $orden = (int)$stmt->fetchColumn();
        $this->db->prepare("INSERT INTO unidad (curso_id,titulo,orden) VALUES(?,?,?)")->execute([$cursoId,$titulo,$orden]);
        $id = $this->db->lastInsertId();
        return ['ok' => true, 'id' => $id, 'titulo' => $titulo, 'orden' => $orden];
    }

    private function apiEliminarUnidad(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        $this->db->prepare("DELETE FROM unidad WHERE id=?")->execute([$id]);
        return ['ok' => true, 'mensaje' => 'Unidad eliminada'];
    }

    private function apiCrearLeccion(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d        = $this->input();
        $unidadId = (int)($d['unidad_id'] ?? 0);
        $titulo   = trim($d['titulo'] ?? '');
        $videoUrl = trim($d['video_url'] ?? '');
        if (!$unidadId || !$titulo) return ['ok' => false, 'error' => 'Datos incompletos'];
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(orden),0)+1 FROM leccion WHERE unidad_id=?");
        $stmt->execute([$unidadId]);
        $orden = (int)$stmt->fetchColumn();
        $this->db->prepare("INSERT INTO leccion (unidad_id,titulo,video_url,orden) VALUES(?,?,?,?)")->execute([$unidadId,$titulo,$videoUrl,$orden]);
        $id = $this->db->lastInsertId();
        return ['ok' => true, 'id' => $id, 'titulo' => $titulo, 'video_url' => $videoUrl, 'orden' => $orden];
    }

    private function apiEditarLeccion(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d = $this->input();
        $id       = (int)($d['id'] ?? 0);
        $titulo   = trim($d['titulo'] ?? '');
        $videoUrl = trim($d['video_url'] ?? '');
        if (!$id || !$titulo) return ['ok' => false, 'error' => 'Datos incompletos'];
        $this->db->prepare("UPDATE leccion SET titulo=?,video_url=? WHERE id=?")->execute([$titulo,$videoUrl,$id]);
        return ['ok' => true, 'mensaje' => 'Lección actualizada'];
    }

    private function apiEliminarLeccion(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        $this->db->prepare("DELETE FROM leccion WHERE id=?")->execute([$id]);
        return ['ok' => true, 'mensaje' => 'Lección eliminada'];
    }

    private function apiGuardarExamen(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d       = $this->input();
        $cursoId = (int)($d['curso_id'] ?? 0);
        $titulo  = trim($d['titulo'] ?? '');
        $desc    = trim($d['descripcion'] ?? '');
        $nota    = (float)($d['nota_minima'] ?? 5.0);
        $pregs   = $d['preguntas'] ?? [];

        if (!$cursoId || !$titulo) return ['ok' => false, 'error' => 'Datos incompletos'];

        $stmt = $this->db->prepare("SELECT id FROM examen WHERE curso_id=?");
        $stmt->execute([$cursoId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $examenId = $existing['id'];
            $this->db->prepare("UPDATE examen SET titulo=?,descripcion=?,nota_minima=? WHERE id=?")->execute([$titulo,$desc,$nota,$examenId]);
            $this->db->prepare("DELETE FROM pregunta WHERE examen_id=?")->execute([$examenId]);
        } else {
            $this->db->prepare("INSERT INTO examen (curso_id,titulo,descripcion,nota_minima) VALUES(?,?,?,?)")->execute([$cursoId,$titulo,$desc,$nota]);
            $examenId = (int)$this->db->lastInsertId();
        }

        foreach ($pregs as $orden => $p) {
            $enunciado = trim($p['enunciado'] ?? '');
            if (!$enunciado) continue;
            $this->db->prepare("INSERT INTO pregunta (examen_id,enunciado,orden) VALUES(?,?,?)")->execute([$examenId,$enunciado,$orden+1]);
            $pregId = (int)$this->db->lastInsertId();
            foreach (($p['opciones'] ?? []) as $oidx => $o) {
                $texto   = trim($o['texto'] ?? '');
                $correcta = (int)($o['correcta'] ?? 0);
                if (!$texto) continue;
                $this->db->prepare("INSERT INTO opcion (pregunta_id,texto,correcta,orden) VALUES(?,?,?,?)")->execute([$pregId,$texto,$correcta,$oidx+1]);
            }
        }
        return ['ok' => true, 'mensaje' => 'Examen guardado correctamente'];
    }

    private function apiCrearCampana(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d = $this->input();
        $titulo  = trim($d['titulo'] ?? '');
        $cuerpo  = trim($d['cuerpo'] ?? '');
        $tipo    = $d['tipo'] ?? 'oferta';
        $inicio  = $d['fecha_inicio'] ?? null;
        $fin     = $d['fecha_fin']    ?? null;
        $desc    = (float)($d['descuento_pct'] ?? 0);
        $cursos  = $d['cursos'] ?? [];

        if (!$titulo || !$cuerpo) return ['ok' => false, 'error' => 'Título y cuerpo son obligatorios'];

        $this->db->prepare("INSERT INTO campana_crm (titulo,cuerpo,tipo,fecha_inicio,fecha_fin,descuento_pct,activa) VALUES(?,?,?,?,?,?,1)")
                 ->execute([$titulo,$cuerpo,$tipo,$inicio,$fin,$desc]);
        $id = (int)$this->db->lastInsertId();

        foreach ($cursos as $cid) {
            $cid = (int)$cid;
            if ($cid) {
                $this->db->prepare("INSERT OR IGNORE INTO campana_curso (campana_id,curso_id,descuento) VALUES(?,?,?)")->execute([$id,$cid,$desc]);
            }
        }

        $enviados = $this->notificarUsuarios($titulo, $cuerpo, $id);

        $this->logActividad("Campaña creada: $titulo", 'info');
        return ['ok' => true, 'id' => $id, 'mensaje' => "Campaña creada y notificación enviada a $enviados usuario(s)"];
    }

    private function apiEditarCampana(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        $titulo = trim($d['titulo'] ?? '');
        $cuerpo = trim($d['cuerpo'] ?? '');
        $tipo   = $d['tipo'] ?? 'oferta';
        $inicio = $d['fecha_inicio'] ?? null;
        $fin    = $d['fecha_fin'] ?? null;
        $desc   = (float)($d['descuento_pct'] ?? 0);
        $activa = (int)($d['activa'] ?? 1);
        $cursos = $d['cursos'] ?? [];
        if (!$titulo || !$cuerpo) return ['ok' => false, 'error' => 'Faltan campos'];

        $this->db->prepare("UPDATE campana_crm SET titulo=?,cuerpo=?,tipo=?,fecha_inicio=?,fecha_fin=?,descuento_pct=?,activa=? WHERE id=?")
                 ->execute([$titulo,$cuerpo,$tipo,$inicio,$fin,$desc,$activa,$id]);

        $this->db->prepare("DELETE FROM campana_curso WHERE campana_id=?")->execute([$id]);
        foreach ($cursos as $cid) {
            $cid = (int)$cid;
            if ($cid) {
                $this->db->prepare("INSERT OR IGNORE INTO campana_curso (campana_id,curso_id,descuento) VALUES(?,?,?)")->execute([$id,$cid,$desc]);
            }
        }
        return ['ok' => true, 'mensaje' => 'Campaña actualizada'];
    }

    private function apiEliminarCampana(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        $this->db->prepare("DELETE FROM campana_crm WHERE id=?")->execute([$id]);
        return ['ok' => true, 'mensaje' => 'Campaña eliminada'];
    }

    private function apiMensajesConversacion(): array
    {
        $cursoId = (int)($_GET['curso_id'] ?? 0);
        if (!$cursoId) return ['ok' => false, 'error' => 'Curso inválido'];
        $stmt = $this->db->prepare("
            SELECT mc.*, u.nombre AS remitente_nombre
            FROM mensaje_curso mc JOIN usuario u ON u.id=mc.remitente_id
            WHERE mc.curso_id=? ORDER BY mc.creado_en ASC
        ");
        $stmt->execute([$cursoId]);
        return ['ok' => true, 'mensajes' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function apiEnviarMensaje(): array
    {
        $d       = $this->input();
        $cursoId = (int)($d['curso_id'] ?? 0);
        $cuerpo  = trim($d['cuerpo'] ?? '');
        $destId  = $d['destinatario_id'] ? (int)$d['destinatario_id'] : null;
        if (!$cursoId || !$cuerpo) return ['ok' => false, 'error' => 'Datos incompletos'];
        $this->db->prepare("INSERT INTO mensaje_curso (curso_id,remitente_id,destinatario_id,cuerpo) VALUES(?,?,?,?)")
                 ->execute([$cursoId,(int)$this->usuario['id'],$destId,$cuerpo]);
        $id = $this->db->lastInsertId();
        return ['ok' => true, 'id' => $id, 'cuerpo' => $cuerpo, 'remitente_nombre' => $this->usuario['nombre']];
    }

    private function apiCrearIncidencia(): array
    {
        $d       = $this->input();
        $asunto  = trim($d['asunto'] ?? '');
        $mensaje = trim($d['mensaje'] ?? '');
        $prior   = $d['prioridad'] ?? 'normal';
        if (!$asunto || !$mensaje) return ['ok' => false, 'error' => 'Asunto y mensaje obligatorios'];
        $this->db->prepare("INSERT INTO incidencia (usuario_id,asunto,prioridad) VALUES(?,?,?)")
                 ->execute([(int)$this->usuario['id'],$asunto,$prior]);
        $id = (int)$this->db->lastInsertId();
        $this->db->prepare("INSERT INTO incidencia_respuesta (incidencia_id,usuario_id,mensaje) VALUES(?,?,?)")
                 ->execute([$id,(int)$this->usuario['id'],$mensaje]);
        return ['ok' => true, 'id' => $id, 'mensaje' => 'Incidencia creada'];
    }

    private function apiResponderIncidencia(): array
    {
        $d    = $this->input();
        $id   = (int)($d['id'] ?? 0);
        $msg  = trim($d['mensaje'] ?? '');
        if (!$id || !$msg) return ['ok' => false, 'error' => 'Datos incompletos'];
        $this->db->prepare("INSERT INTO incidencia_respuesta (incidencia_id,usuario_id,mensaje) VALUES(?,?,?)")
                 ->execute([$id,(int)$this->usuario['id'],$msg]);
        return ['ok' => true, 'mensaje' => 'Respuesta enviada'];
    }

    private function apiEstadoIncidencia(): array
    {
        if (!$this->esAdmin && !$this->esModerador) return ['ok' => false, 'error' => 'Sin permisos'];
        $d      = $this->input();
        $id     = (int)($d['id'] ?? 0);
        $estado = $d['estado'] ?? 'abierta';
        if (!in_array($estado, ['abierta','en_proceso','cerrada'], true)) return ['ok' => false, 'error' => 'Estado inválido'];
        $this->db->prepare("UPDATE incidencia SET estado=? WHERE id=?")->execute([$estado,$id]);
        return ['ok' => true, 'mensaje' => "Estado actualizado a '$estado'"];
    }

    private function apiActualizarPerfil(): array
    {
        $d      = $this->input();
        $nombre = trim($d['nombre'] ?? '');
        $bio    = trim($d['bio'] ?? '');
        if (!$nombre) return ['ok' => false, 'error' => 'El nombre es obligatorio'];
        $this->db->prepare("UPDATE usuario SET nombre=?,bio=? WHERE id=?")->execute([$nombre,$bio,(int)$this->usuario['id']]);
        $_SESSION['usuario_nombre'] = $nombre;
        return ['ok' => true, 'mensaje' => 'Perfil actualizado'];
    }

    private function apiCambiarContrasena(): array
    {
        $d       = $this->input();
        $actual  = $d['actual'] ?? '';
        $nueva   = $d['nueva']  ?? '';
        $conf    = $d['confirmar'] ?? '';
        if (!$actual || !$nueva) return ['ok' => false, 'error' => 'Faltan campos'];
        if (strlen($nueva) < 6) return ['ok' => false, 'error' => 'Mínimo 6 caracteres'];
        if ($nueva !== $conf) return ['ok' => false, 'error' => 'Las contraseñas no coinciden'];
        $stmt = $this->db->prepare("SELECT contraseña FROM usuario WHERE id=?");
        $stmt->execute([(int)$this->usuario['id']]);
        $hash = $stmt->fetchColumn();
        if (!password_verify($actual, $hash)) return ['ok' => false, 'error' => 'Contraseña actual incorrecta'];
        $this->db->prepare("UPDATE usuario SET contraseña=? WHERE id=?")->execute([password_hash($nueva, PASSWORD_DEFAULT),(int)$this->usuario['id']]);
        return ['ok' => true, 'mensaje' => 'Contraseña cambiada correctamente'];
    }

    private function apiActualizarAjustes(): array
    {
        $d   = $this->input();
        $not = (int)($d['notificaciones'] ?? 1);
        $priv = $d['privacidad'] ?? 'publico';
        $this->db->prepare("UPDATE usuario SET notificaciones=?,privacidad=? WHERE id=?")->execute([$not,$priv,(int)$this->usuario['id']]);
        return ['ok' => true, 'mensaje' => 'Ajustes guardados'];
    }

    /* ================================================================== */
    /*  HELPERS                                                             */
    /* ================================================================== */

    private function logActividad(string $titulo, string $tipo = 'info'): void
    {
        try {
            $this->db->prepare("INSERT INTO crm_actividad (usuario_id,tipo,titulo) VALUES(?,?,?)")
                     ->execute([(int)$this->usuario['id'], $tipo, $titulo]);
        } catch (Exception $e) { /* ignorar */ }
    }

    private function notificarUsuarios(string $titulo, string $cuerpo, int $campanaId): int
    {
        // Get all users who don't already have this campaign notification
        $stmt = $this->db->prepare("
            SELECT u.id FROM usuario u
            LEFT JOIN notificacion n ON n.usuario_id=u.id AND n.tipo='crm' AND n.ref_id=?
            WHERE (u.notificaciones IS NULL OR u.notificaciones=1)
              AND n.id IS NULL
        ");
        $stmt->execute([$campanaId]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $ins = $this->db->prepare("INSERT OR IGNORE INTO notificacion (usuario_id,tipo,titulo,cuerpo,ref_id) VALUES(?,?,?,?,?)");
        foreach ($usuarios as $uid) {
            $ins->execute([$uid, 'crm', $titulo, $cuerpo, $campanaId]);
        }
        return count($usuarios);
    }
}
