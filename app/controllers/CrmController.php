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
    private bool   $esInstructor;

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
        $this->esInstructor = ($rol === 'EDITOR');

        // Block only USUARIO role — admins, moderators and instructors can enter
        if (!$this->esAdmin && !$this->esModerador && !$this->esInstructor) {
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
            'ALTER TABLE curso   ADD COLUMN apuntes_json   TEXT    DEFAULT NULL',
            'ALTER TABLE campana_crm ADD COLUMN descuento_pct REAL NOT NULL DEFAULT 0',
            'ALTER TABLE leccion ADD COLUMN orden INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE unidad  ADD COLUMN orden INTEGER NOT NULL DEFAULT 0',
            'ALTER TABLE examen  ADD COLUMN tipo  TEXT    NOT NULL DEFAULT "test"',
            'ALTER TABLE leccion ADD COLUMN apuntes TEXT DEFAULT NULL',
            'ALTER TABLE campana_crm ADD COLUMN audiencia     TEXT    NOT NULL DEFAULT "todos"',
            'ALTER TABLE campana_crm ADD COLUMN dias_registro INTEGER DEFAULT NULL',
            'ALTER TABLE examen        ADD COLUMN fecha_entrega TEXT    DEFAULT NULL',
            'ALTER TABLE examen        ADD COLUMN modo_entrega  TEXT    NOT NULL DEFAULT "cualquiera"',
            'ALTER TABLE notificacion  ADD COLUMN url_accion    TEXT    DEFAULT NULL',
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
        if (in_array($sec, ['campanas', 'logs'], true) && !$this->esAdmin) {
            $sec = $this->esInstructor ? 'dashboard' : 'comunicacion';
        }
        // Instructors: only dashboard, editor (own courses), perfil, comunicacion
        if ($this->esInstructor && !in_array($sec, ['dashboard', 'editor', 'perfil', 'comunicacion'], true)) {
            $sec = 'dashboard';
        }
        // Moderators: no cursos/editor access
        if ($this->esModerador && !$this->esAdmin && in_array($sec, ['cursos', 'editor'], true)) {
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
            'perfil'       => 'Mi Perfil',
            'ajustes'      => 'Ajustes',
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
            'perfil'       => $this->getPerfilData(),
            'ajustes'      => $this->getAjustesData(),
            default        => $this->getDashboardData(),
        };

        $usuario       = $this->usuario;
        $esSuperAdmin  = $this->esSuperAdmin;
        $esAdmin       = $this->esAdmin;
        $esModerador   = $this->esModerador;
        $esInstructor  = $this->esInstructor;
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
            'toggle_all_cursos'     => $this->apiToggleAllCursos(),
            'actualizar_curso'      => $this->apiActualizarCurso(),
            'guardar_unidades'      => $this->apiGuardarUnidades(),
            'crear_unidad'          => $this->apiCrearUnidad(),
            'eliminar_unidad'       => $this->apiEliminarUnidad(),
            'crear_leccion'         => $this->apiCrearLeccion(),
            'editar_leccion'        => $this->apiEditarLeccion(),
            'eliminar_leccion'      => $this->apiEliminarLeccion(),
            'guardar_examen'          => $this->apiGuardarExamen(),
            'guardar_examen_practico' => $this->apiGuardarExamenPractico(),
            'subir_imagen_curso'      => $this->apiSubirImagenCurso(),
            'guardar_apuntes'           => $this->apiGuardarApuntes(),
            'guardar_apuntes_leccion'   => $this->apiGuardarApuntesLeccion(),
            'subir_recurso_leccion'     => $this->apiSubirRecursoLeccion(),
            'eliminar_recurso'          => $this->apiEliminarRecurso(),
            'get_recursos_leccion'      => $this->apiGetRecursosLeccion(),
            'get_resultados_curso'      => $this->apiGetResultadosCurso(),
            'get_entregas_alumno'       => $this->apiGetEntregasAlumno(),
            'revisar_practica'          => $this->apiRevisarPractica(),
            'crear_campana'             => $this->apiCrearCampana(),
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
            'get_crm_notifs'        => $this->apiGetCrmNotifs(),
            'marcar_notif_leida'    => $this->apiMarcarNotifLeida(),
            'marcar_todas_leidas'   => $this->apiMarcarTodasLeidas(),
            'check_campana_conflicto' => $this->apiCheckCampanaConflicto(),
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
        $perPage = in_array((int)($_GET['per'] ?? 10), [10,15,20,25,30]) ? (int)($_GET['per'] ?? 10) : 10;
        $limit   = $perPage;
        $q       = trim($_GET['q'] ?? '');
        $cat     = $_GET['cat'] ?? '';
        $nivel   = $_GET['nivel'] ?? '';
        $estado  = $_GET['estado'] ?? '';    // '' | 'activo' | 'inactivo'

        $where = 'WHERE 1=1'; $params = [];
        if ($q)                { $where .= ' AND c.titulo LIKE ?'; $params[] = "%$q%"; }
        if ($cat)              { $where .= ' AND c.categoria = ?'; $params[] = $cat; }
        if ($nivel)            { $where .= ' AND c.nivel = ?';     $params[] = $nivel; }
        if ($estado === 'activo')   { $where .= ' AND c.activo = 1'; }
        if ($estado === 'inactivo') { $where .= ' AND c.activo = 0'; }
        // Instructors can only see their own courses
        if ($this->esInstructor && !$this->esAdmin) {
            $where .= ' AND EXISTS (SELECT 1 FROM curso_instructor ci WHERE ci.curso_id=c.id AND ci.usuario_id=?)';
            $params[] = (int)$this->usuario['id'];
        }

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

        return compact('cursos','totalRows','totalPags','page','perPage','q','cat','nivel','estado','categorias','niveles','instructores','cursosStats');
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
            foreach ($u['lecciones'] as &$lec) {
                try {
                    $rs = $this->db->prepare('SELECT * FROM leccion_recurso WHERE leccion_id=? ORDER BY orden,id');
                    $rs->execute([$lec['id']]);
                    $lec['recursos'] = $rs->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) { $lec['recursos'] = []; }
            }
            unset($lec);
        }
        unset($u);

        // Test exam (tipo='test' or legacy rows without tipo)
        $stmt = $this->db->prepare("SELECT * FROM examen WHERE curso_id=? AND (tipo='test' OR tipo IS NULL OR tipo='') LIMIT 1");
        $stmt->execute([$cursoId]);
        $examenTest = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $preguntas = [];
        if ($examenTest) {
            $stmt = $this->db->prepare('SELECT * FROM pregunta WHERE examen_id=? ORDER BY orden,id');
            $stmt->execute([$examenTest['id']]);
            $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($preguntas as &$p) {
                $os = $this->db->prepare('SELECT * FROM opcion WHERE pregunta_id=? ORDER BY orden,id');
                $os->execute([$p['id']]);
                $p['opciones'] = $os->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($p);
        }

        // Practical exam
        $stmt = $this->db->prepare("SELECT * FROM examen WHERE curso_id=? AND tipo='practico' LIMIT 1");
        $stmt->execute([$cursoId]);
        $examenPractico = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $tareasPracticas = [];
        try {
            $stmt = $this->db->prepare('SELECT * FROM tarea_practica WHERE curso_id=? ORDER BY orden,id');
            $stmt->execute([$cursoId]);
            $tareasPracticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $tareasPracticas = []; }

        // Apuntes (JSON stored in curso row)
        $apuntesRaw = $curso['apuntes_json'] ?? null;
        $apuntes = ($apuntesRaw && $apuntesRaw !== 'null') ? (json_decode($apuntesRaw, true) ?? []) : [];

        $instructores = $this->db->query("SELECT id, nombre FROM usuario WHERE rol='EDITOR' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

        $stmtIns = $this->db->prepare("SELECT usuario_id FROM curso_instructor WHERE curso_id=?");
        $stmtIns->execute([$cursoId]);
        $instructoresAsignados = $stmtIns->fetchAll(PDO::FETCH_COLUMN);

        return compact('curso','unidades','examenTest','examenPractico','preguntas','tareasPracticas','apuntes','instructores','instructoresAsignados');
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
        $db = $this->db;
        $uid = (int)$this->usuario['id'];

        $totalCursos = (int)$db->query("SELECT COUNT(*) FROM matricula WHERE usuario_id=$uid")->fetchColumn();

        try {
            $totalActividad = (int)$db->query("SELECT COUNT(*) FROM crm_actividad WHERE usuario_id=$uid")->fetchColumn();
        } catch (Exception $e) { $totalActividad = 0; }

        try {
            $stmt = $db->prepare("SELECT titulo, creado_en FROM crm_actividad WHERE usuario_id=? ORDER BY creado_en DESC LIMIT 5");
            $stmt->execute([$uid]);
            $actividadReciente = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $actividadReciente = []; }

        $diasCuenta = max(1, (int)round((time() - strtotime($this->usuario['creado_en'] ?? 'now')) / 86400));

        return [
            'usuarioPerfil'    => $this->usuario,
            'totalCursos'      => $totalCursos,
            'totalActividad'   => $totalActividad,
            'actividadReciente'=> $actividadReciente,
            'diasCuenta'       => $diasCuenta,
        ];
    }

    private function getAjustesData(): array
    {
        return [];
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

    private function apiToggleAllCursos(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d      = $this->input();
        $activo = isset($d['activo']) ? (int)(bool)$d['activo'] : null;
        if ($activo === null) return ['ok' => false, 'error' => 'Valor no especificado'];
        $this->db->prepare("UPDATE curso SET activo=?")->execute([$activo]);
        $total = (int)$this->db->query("SELECT COUNT(*) FROM curso")->fetchColumn();
        $msg   = $activo ? "Todos los cursos activados" : "Todos los cursos desactivados";
        $this->logActividad($msg, 'info');
        return ['ok' => true, 'activo' => $activo, 'total' => $total, 'mensaje' => $msg];
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
        $activoVal   = isset($d['activo']) ? (int)$d['activo'] : null;

        if (!$titulo) return ['ok' => false, 'error' => 'El título es obligatorio'];

        $sql = "UPDATE curso SET titulo=?,descripcion=?,precio=?,nivel=?,categoria=?,destacado=?";
        $params = [$titulo, $descripcion, $precio, $nivel, $categoria, $destacado];
        if ($activoVal !== null) { $sql .= ',activo=?'; $params[] = $activoVal; }
        $sql .= ' WHERE id=?';
        $params[] = $id;
        $this->db->prepare($sql)->execute($params);

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
        $apuntes  = $d['apuntes'] ?? null;
        if (!$id || !$titulo) return ['ok' => false, 'error' => 'Datos incompletos'];
        $this->db->prepare("UPDATE leccion SET titulo=?,video_url=?,apuntes=? WHERE id=?")->execute([$titulo,$videoUrl,$apuntes,$id]);
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

        $stmt = $this->db->prepare("SELECT id FROM examen WHERE curso_id=? AND (tipo='test' OR tipo IS NULL OR tipo='')");
        $stmt->execute([$cursoId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $examenId = $existing['id'];
            $this->db->prepare("UPDATE examen SET titulo=?,descripcion=?,nota_minima=?,tipo='test' WHERE id=?")->execute([$titulo,$desc,$nota,$examenId]);
            $this->db->prepare("DELETE FROM pregunta WHERE examen_id=?")->execute([$examenId]);
        } else {
            $this->db->prepare("INSERT INTO examen (curso_id,titulo,descripcion,nota_minima,tipo) VALUES(?,?,?,?,?)")->execute([$cursoId,$titulo,$desc,$nota,'test']);
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

    private function apiGuardarExamenPractico(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d            = $this->input();
        $cursoId      = (int)($d['curso_id'] ?? 0);
        $titulo       = trim($d['titulo'] ?? '');
        $desc         = trim($d['descripcion'] ?? '');
        $nota         = (float)($d['nota_minima'] ?? 5.0);
        $fechaEntrega = !empty($d['fecha_entrega']) ? $d['fecha_entrega'] : null;
        $modoEntrega  = in_array($d['modo_entrega'] ?? '', ['cualquiera','texto','archivo','url','texto_y_archivo']) ? $d['modo_entrega'] : 'cualquiera';
        $tareas       = $d['tareas'] ?? [];

        if (!$cursoId) return ['ok' => false, 'error' => 'ID de curso inválido'];

        if ($titulo) {
            $stmt = $this->db->prepare("SELECT id FROM examen WHERE curso_id=? AND tipo='practico'");
            $stmt->execute([$cursoId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $this->db->prepare("UPDATE examen SET titulo=?,descripcion=?,nota_minima=?,fecha_entrega=?,modo_entrega=? WHERE id=?")->execute([$titulo,$desc,$nota,$fechaEntrega,$modoEntrega,$existing['id']]);
            } else {
                $this->db->prepare("INSERT INTO examen (curso_id,titulo,descripcion,nota_minima,tipo,fecha_entrega,modo_entrega) VALUES(?,?,?,?,?,?,?)")->execute([$cursoId,$titulo,$desc,$nota,'practico',$fechaEntrega,$modoEntrega]);
            }
        }

        $this->db->prepare("DELETE FROM tarea_practica WHERE curso_id=?")->execute([$cursoId]);
        $ins = $this->db->prepare("INSERT INTO tarea_practica (curso_id,titulo,enunciado,tipo,puntos,criterios,orden) VALUES(?,?,?,?,?,?,?)");
        foreach ($tareas as $idx => $t) {
            $tituloT = trim($t['titulo'] ?? '');
            if (!$tituloT) continue;
            $ins->execute([
                $cursoId, $tituloT,
                trim($t['enunciado'] ?? ''),
                in_array($t['tipo'] ?? '', ['texto','codigo','diseno','proyecto']) ? $t['tipo'] : 'texto',
                (float)($t['puntos'] ?? 10),
                trim($t['criterios'] ?? ''),
                $idx,
            ]);
        }
        return ['ok' => true, 'mensaje' => 'Examen práctico guardado correctamente'];
    }

    private function apiSubirImagenCurso(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $cursoId = (int)($_POST['curso_id'] ?? 0);
        if (!$cursoId) return ['ok' => false, 'error' => 'ID de curso inválido'];
        if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'No se recibió imagen válida'];
        }
        $file    = $_FILES['imagen'];
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) return ['ok' => false, 'error' => 'Tipo de imagen no permitido (jpg/png/webp/gif)'];
        if ($file['size'] > 5 * 1024 * 1024) return ['ok' => false, 'error' => 'Imagen demasiado grande (máx. 5 MB)'];

        $ext     = match($mime) { 'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif',default=>'jpg' };
        $nombre  = 'curso_' . $cursoId . '_' . time() . '.' . $ext;
        $destDir = __DIR__ . '/../../public/img/';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $destDir . $nombre)) {
            return ['ok' => false, 'error' => 'Error al mover el archivo'];
        }
        $this->db->prepare("UPDATE curso SET imagen=? WHERE id=?")->execute([$nombre, $cursoId]);
        return ['ok' => true, 'imagen' => $nombre, 'url' => BASE_URL . '/img/' . $nombre, 'mensaje' => 'Imagen actualizada'];
    }

    private function apiGuardarApuntes(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d       = $this->input();
        $cursoId = (int)($d['curso_id'] ?? 0);
        $apuntes = $d['apuntes'] ?? [];
        if (!$cursoId) return ['ok' => false, 'error' => 'ID de curso inválido'];
        $this->db->prepare("UPDATE curso SET apuntes_json=? WHERE id=?")->execute([json_encode($apuntes, JSON_UNESCAPED_UNICODE), $cursoId]);
        return ['ok' => true, 'mensaje' => 'Apuntes guardados correctamente'];
    }

    /* ── Lesson resources ── */
    private function apiGuardarApuntesLeccion(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d        = $this->input();
        $id       = (int)($d['leccion_id'] ?? 0);
        $apuntes  = trim($d['apuntes'] ?? '');
        if (!$id) return ['ok' => false, 'error' => 'ID de lección inválido'];
        $this->db->prepare("UPDATE leccion SET apuntes=? WHERE id=?")->execute([$apuntes, $id]);
        return ['ok' => true, 'mensaje' => 'Apuntes de lección guardados'];
    }

    private function apiGetRecursosLeccion(): array
    {
        $leccionId = (int)($_GET['leccion_id'] ?? 0);
        if (!$leccionId) return ['ok' => false, 'error' => 'ID inválido'];
        try {
            $stmt = $this->db->prepare('SELECT * FROM leccion_recurso WHERE leccion_id=? ORDER BY orden,id');
            $stmt->execute([$leccionId]);
            return ['ok' => true, 'recursos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['ok' => true, 'recursos' => []];
        }
    }

    private function apiSubirRecursoLeccion(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $leccionId  = (int)($_POST['leccion_id'] ?? 0);
        $nombre     = trim($_POST['nombre'] ?? '');
        $tipoRec    = $_POST['tipo'] ?? 'link';
        $urlDirecta = trim($_POST['url'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        if (!$leccionId || !$nombre) return ['ok' => false, 'error' => 'Datos incompletos'];

        $tipos = ['pdf','doc','zip','link','actividad','video'];
        if (!in_array($tipoRec, $tipos)) $tipoRec = 'link';

        $urlFinal   = $urlDirecta;
        $descargable = 0;

        if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['archivo'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf','doc','docx','zip','rar','txt','png','jpg','xlsx','pptx','mp4'];
            if (!in_array($ext, $allowed)) return ['ok' => false, 'error' => 'Tipo de archivo no permitido'];
            if ($file['size'] > 50 * 1024 * 1024) return ['ok' => false, 'error' => 'Archivo muy grande (máx. 50 MB)'];
            $destDir = __DIR__ . '/../../public/uploads/recursos/';
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);
            $nombreArchivo = 'lec' . $leccionId . '_' . time() . '_' . preg_replace('/[^a-z0-9._-]/i', '', $file['name']);
            if (!move_uploaded_file($file['tmp_name'], $destDir . $nombreArchivo)) {
                return ['ok' => false, 'error' => 'Error al guardar el archivo'];
            }
            $urlFinal   = BASE_URL . '/uploads/recursos/' . $nombreArchivo;
            $descargable = 1;
        }

        if (!$urlFinal) return ['ok' => false, 'error' => 'URL o archivo requerido'];

        $stmt = $this->db->prepare("SELECT COALESCE(MAX(orden),0)+1 FROM leccion_recurso WHERE leccion_id=?");
        $stmt->execute([$leccionId]);
        $orden = (int)$stmt->fetchColumn();

        $this->db->prepare("INSERT INTO leccion_recurso (leccion_id,nombre,tipo,url_o_ruta,descripcion,descargable,orden) VALUES(?,?,?,?,?,?,?)")
                 ->execute([$leccionId, $nombre, $tipoRec, $urlFinal, $descripcion, $descargable, $orden]);
        $newId = (int)$this->db->lastInsertId();

        return ['ok' => true, 'id' => $newId, 'url' => $urlFinal, 'nombre' => $nombre, 'tipo' => $tipoRec, 'descripcion' => $descripcion, 'descargable' => $descargable, 'orden' => $orden, 'mensaje' => 'Recurso añadido'];
    }

    private function apiEliminarRecurso(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        $this->db->prepare("DELETE FROM leccion_recurso WHERE id=?")->execute([$id]);
        return ['ok' => true, 'mensaje' => 'Recurso eliminado'];
    }

    /* ── CRM exam results ── */
    private function apiGetResultadosCurso(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $cursoId = (int)($_GET['curso_id'] ?? 0);
        if (!$cursoId) return ['ok' => false, 'error' => 'ID de curso inválido'];

        // Enrolled students
        $stmt = $this->db->prepare("
            SELECT u.id, u.nombre, u.email, m.creado_en AS matriculado_en
            FROM matricula m JOIN usuario u ON u.id=m.usuario_id
            WHERE m.curso_id=? ORDER BY u.nombre ASC
        ");
        $stmt->execute([$cursoId]);
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Test exam result for each
        $stmtTest = $this->db->prepare("
            SELECT re.nota, re.aprobado, re.realizado_en
            FROM resultado_examen re
            JOIN examen e ON e.id=re.examen_id
            WHERE re.usuario_id=? AND e.curso_id=? AND (e.tipo='test' OR e.tipo IS NULL OR e.tipo='')
            ORDER BY re.realizado_en DESC LIMIT 1
        ");

        // Practical submissions count
        $stmtPrac = $this->db->prepare("
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN revisado=1 THEN 1 ELSE 0 END) AS revisadas,
                   AVG(CASE WHEN nota IS NOT NULL THEN nota END) AS nota_media
            FROM entrega_practica
            WHERE alumno_id=? AND curso_id=?
        ");

        // Total practical tasks
        $stmtTareas = $this->db->prepare("SELECT COUNT(*) FROM tarea_practica WHERE curso_id=?");
        $stmtTareas->execute([$cursoId]);
        $totalTareas = (int)$stmtTareas->fetchColumn();

        foreach ($alumnos as &$al) {
            $stmtTest->execute([$al['id'], $cursoId]);
            $al['test'] = $stmtTest->fetch(PDO::FETCH_ASSOC) ?: null;

            $stmtPrac->execute([$al['id'], $cursoId]);
            $al['practico'] = $stmtPrac->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'revisadas'=>0,'nota_media'=>null];
            $al['practico']['total_tareas'] = $totalTareas;
        }
        unset($al);

        return ['ok' => true, 'alumnos' => $alumnos, 'total_tareas' => $totalTareas];
    }

    private function apiGetEntregasAlumno(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $alumnoId = (int)($_GET['alumno_id'] ?? 0);
        $cursoId  = (int)($_GET['curso_id']  ?? 0);
        if (!$alumnoId || !$cursoId) return ['ok' => false, 'error' => 'Parámetros inválidos'];
        try {
            $stmt = $this->db->prepare("
                SELECT ep.*, tp.titulo AS tarea_titulo
                FROM entrega_practica ep
                JOIN tarea_practica tp ON tp.id=ep.tarea_id
                WHERE ep.alumno_id=? AND ep.curso_id=?
                ORDER BY tp.orden, ep.id
            ");
            $stmt->execute([$alumnoId, $cursoId]);
            return ['ok' => true, 'entregas' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['ok' => true, 'entregas' => []];
        }
    }

    private function apiRevisarPractica(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d        = $this->input();
        $entregaId = (int)($d['entrega_id'] ?? 0);
        $nota     = $d['nota'] !== '' && $d['nota'] !== null ? (float)$d['nota'] : null;
        $feedback = trim($d['feedback'] ?? '');
        if (!$entregaId) return ['ok' => false, 'error' => 'ID de entrega inválido'];

        $this->db->prepare("
            UPDATE entrega_practica
            SET nota=?, feedback=?, revisado=1, revisado_por_id=?, revisado_en=datetime('now')
            WHERE id=?
        ")->execute([$nota, $feedback, (int)$this->usuario['id'], $entregaId]);

        return ['ok' => true, 'mensaje' => 'Entrega calificada correctamente'];
    }

    private function apiCrearCampana(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d         = $this->input();
        $titulo    = trim($d['titulo'] ?? '');
        $cuerpo    = trim($d['cuerpo'] ?? '');
        $tipo      = $d['tipo'] ?? 'oferta';
        $inicio    = !empty($d['fecha_inicio']) ? $d['fecha_inicio'] : null;
        $fin       = !empty($d['fecha_fin'])    ? $d['fecha_fin']    : null;
        $desc      = (float)($d['descuento_pct'] ?? 0);
        $cursos    = $d['cursos'] ?? [];
        $audiencia = in_array($d['audiencia'] ?? '', ['todos','nuevos','matriculados']) ? $d['audiencia'] : 'todos';
        $diasReg   = ($audiencia === 'nuevos' && isset($d['dias_registro'])) ? max(1,(int)$d['dias_registro']) : null;

        if (!$titulo || !$cuerpo) return ['ok' => false, 'error' => 'Título y cuerpo son obligatorios'];

        $conflicto = $this->detectarConflictoCampana($titulo, $tipo, $inicio, $fin, $cursos, null);
        if ($conflicto) return ['ok' => false, 'error' => $conflicto];

        $this->db->prepare("INSERT INTO campana_crm (titulo,cuerpo,tipo,fecha_inicio,fecha_fin,descuento_pct,activa,audiencia,dias_registro) VALUES(?,?,?,?,?,?,1,?,?)")
                 ->execute([$titulo,$cuerpo,$tipo,$inicio,$fin,$desc,$audiencia,$diasReg]);
        $id = (int)$this->db->lastInsertId();

        foreach ($cursos as $cid) {
            $cid = (int)$cid;
            if ($cid) {
                $this->db->prepare("INSERT OR IGNORE INTO campana_curso (campana_id,curso_id,descuento) VALUES(?,?,?)")->execute([$id,$cid,$desc]);
            }
        }

        $enviados = $this->notificarUsuarios($titulo, $cuerpo, $id, $audiencia, $diasReg);

        $this->logActividad("Campaña creada: $titulo", 'info');
        return ['ok' => true, 'id' => $id, 'mensaje' => "Campaña creada y notificación enviada a $enviados usuario(s)"];
    }

    private function apiEditarCampana(): array
    {
        if (!$this->esAdmin) return ['ok' => false, 'error' => 'Sin permisos'];
        $d         = $this->input();
        $id        = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        $titulo    = trim($d['titulo'] ?? '');
        $cuerpo    = trim($d['cuerpo'] ?? '');
        $tipo      = $d['tipo'] ?? 'oferta';
        $inicio    = !empty($d['fecha_inicio']) ? $d['fecha_inicio'] : null;
        $fin       = !empty($d['fecha_fin'])    ? $d['fecha_fin']    : null;
        $desc      = (float)($d['descuento_pct'] ?? 0);
        $activa    = (int)($d['activa'] ?? 1);
        $cursos    = $d['cursos'] ?? [];
        $audiencia = in_array($d['audiencia'] ?? '', ['todos','nuevos','matriculados']) ? $d['audiencia'] : 'todos';
        $diasReg   = ($audiencia === 'nuevos' && isset($d['dias_registro'])) ? max(1,(int)$d['dias_registro']) : null;
        if (!$titulo || !$cuerpo) return ['ok' => false, 'error' => 'Faltan campos'];

        $conflicto = $this->detectarConflictoCampana($titulo, $tipo, $inicio, $fin, $cursos, $id);
        if ($conflicto) return ['ok' => false, 'error' => $conflicto];

        $this->db->prepare("UPDATE campana_crm SET titulo=?,cuerpo=?,tipo=?,fecha_inicio=?,fecha_fin=?,descuento_pct=?,activa=?,audiencia=?,dias_registro=? WHERE id=?")
                 ->execute([$titulo,$cuerpo,$tipo,$inicio,$fin,$desc,$activa,$audiencia,$diasReg,$id]);

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

        // Notify all admins & moderators
        try {
            $admins = $this->db->query("SELECT id FROM usuario WHERE rol='ADMINISTRADOR' OR es_moderador=1")->fetchAll(PDO::FETCH_COLUMN);
            $ins = $this->db->prepare("INSERT OR IGNORE INTO notificacion (usuario_id,tipo,titulo,cuerpo,ref_id,url_accion) VALUES(?,?,?,?,?,?)");
            $prioLabel = ['urgente'=>'⚠️ URGENTE','alta'=>'🔴 Alta','normal'=>'🟡 Normal','baja'=>'🟢 Baja'][$prior] ?? $prior;
            foreach ($admins as $aid) {
                if ($aid != (int)$this->usuario['id']) {
                    $ins->execute([$aid,'crm',"Nueva incidencia: $asunto","Prioridad: $prioLabel · De: {$this->usuario['nombre']}",$id,'?url=crm&sec=comunicacion&tab=incidencias']);
                }
            }
        } catch (Exception $e) {}

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

    /* ── Campaign conflict detection ── */
    private function detectarConflictoCampana(string $titulo, string $tipo, ?string $inicio, ?string $fin, array $cursos, ?int $excludeId): ?string
    {
        // 1. Fecha inicio posterior a fecha fin
        if ($inicio && $fin && $inicio > $fin) {
            return 'La fecha de inicio no puede ser posterior a la fecha de fin.';
        }

        // 2. Título exactamente duplicado (misma campaña con mismo título)
        $dupQ = "SELECT id FROM campana_crm WHERE titulo=? AND activa=1" . ($excludeId ? " AND id<>$excludeId" : '');
        $dup  = $this->db->prepare($dupQ);
        $dup->execute([$titulo]);
        if ($dup->fetch()) {
            return "Ya existe una campaña activa con el título \"$titulo\". Usa un nombre diferente.";
        }

        // 3. Para campañas de tipo 'oferta': solapamiento de fechas sobre los mismos cursos
        if ($tipo === 'oferta' && !empty($cursos)) {
            foreach ($cursos as $cid) {
                $cid = (int)$cid;
                if (!$cid) continue;

                $overlapQ = "
                    SELECT c.titulo FROM campana_crm cam
                    JOIN campana_curso cc ON cc.campana_id=cam.id
                    WHERE cc.curso_id=?
                      AND cam.tipo='oferta'
                      AND cam.activa=1
                      " . ($excludeId ? "AND cam.id<>$excludeId" : '') . "
                      AND (
                        -- cam solapada con el nuevo rango
                        (? IS NULL OR cam.fecha_fin IS NULL OR cam.fecha_fin >= ?)
                        AND (? IS NULL OR cam.fecha_inicio IS NULL OR cam.fecha_inicio <= ?)
                      )
                    LIMIT 1
                ";
                $ov = $this->db->prepare($overlapQ);
                $ov->execute([$cid, $fin, $inicio ?? '2000-01-01', $inicio, $fin ?? '2099-12-31']);
                $cursoRow = $ov->fetch(PDO::FETCH_ASSOC);
                if ($cursoRow) {
                    $cursoNombre = $cursoRow['titulo'] ?? "ID $cid";
                    return "El curso \"" . mb_strimwidth($cursoNombre, 0, 40, '…') . "\" ya tiene una oferta activa que se solapa con estas fechas. Desactiva la campaña existente o elige otro rango.";
                }
            }
        }

        // 4. Mismas fechas exactas + mismo tipo (posible duplicado de campaña global)
        if ($inicio && $fin) {
            $sameQ = "SELECT titulo FROM campana_crm WHERE tipo=? AND fecha_inicio=? AND fecha_fin=? AND activa=1" . ($excludeId ? " AND id<>$excludeId" : '') . " LIMIT 1";
            $same  = $this->db->prepare($sameQ);
            $same->execute([$tipo, $inicio, $fin]);
            if ($row = $same->fetch(PDO::FETCH_ASSOC)) {
                return "Ya existe una campaña de tipo \"$tipo\" con exactamente el mismo rango de fechas: \"{$row['titulo']}\". Combínalas o ajusta las fechas.";
            }
        }

        return null;
    }

    private function apiCheckCampanaConflicto(): array
    {
        $d      = $this->input();
        $titulo = trim($d['titulo'] ?? '');
        $tipo   = $d['tipo'] ?? 'oferta';
        $inicio = !empty($d['fecha_inicio']) ? $d['fecha_inicio'] : null;
        $fin    = !empty($d['fecha_fin'])    ? $d['fecha_fin']    : null;
        $cursos = $d['cursos'] ?? [];
        $excl   = isset($d['id']) ? (int)$d['id'] : null;

        $error = $this->detectarConflictoCampana($titulo, $tipo, $inicio, $fin, $cursos, $excl);
        return $error ? ['ok' => false, 'error' => $error] : ['ok' => true];
    }

    /* ── CRM Notifications ── */
    private function apiGetCrmNotifs(): array
    {
        $uid = (int)$this->usuario['id'];

        // For admins/moderators: get system CRM activity notifications
        $notifs = [];

        // 1. Unread platform notifications for this user (type crm or any)
        try {
            $stmt = $this->db->prepare("
                SELECT id, tipo, titulo, cuerpo, leido, url_accion, creado_en
                FROM notificacion
                WHERE usuario_id=?
                ORDER BY creado_en DESC LIMIT 20
            ");
            $stmt->execute([$uid]);
            $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $notifs = []; }

        // 2. For admins: inject recent high-priority CRM events not yet seen
        $adminAlerts = [];
        if ($this->esAdmin || $this->esModerador) {
            // New open incidencias
            try {
                $s = $this->db->query("
                    SELECT i.id, i.asunto, u.nombre AS usuario_nombre, i.prioridad, i.creado_en
                    FROM incidencia i JOIN usuario u ON u.id=i.usuario_id
                    WHERE i.estado='abierta'
                    ORDER BY CASE i.prioridad WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 ELSE 3 END, i.creado_en DESC
                    LIMIT 5
                ");
                foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $inc) {
                    $adminAlerts[] = [
                        'id'       => 'inc_' . $inc['id'],
                        'tipo'     => 'incidencia',
                        'titulo'   => 'Incidencia abierta: ' . mb_strimwidth($inc['asunto'], 0, 60, '…'),
                        'cuerpo'   => 'De ' . $inc['usuario_nombre'] . ' · ' . ucfirst($inc['prioridad']),
                        'leido'    => 0,
                        'creado_en'=> $inc['creado_en'],
                        'url_accion'=> '?url=crm&sec=comunicacion&tab=incidencias',
                        'badge'    => $inc['prioridad'] === 'urgente' ? 'danger' : ($inc['prioridad'] === 'alta' ? 'warning' : 'info'),
                    ];
                }
            } catch (Exception $e) {}

            // Pending practical reviews
            try {
                $s = $this->db->prepare("
                    SELECT COUNT(*) AS total FROM entrega_practica WHERE revisado=0
                ");
                $s->execute();
                $pendientes = (int)$s->fetchColumn();
                if ($pendientes > 0) {
                    $adminAlerts[] = [
                        'id'        => 'prac_review',
                        'tipo'      => 'revision',
                        'titulo'    => "$pendientes entrega(s) prácticas pendientes de revisión",
                        'cuerpo'    => 'Accede al editor de cada curso → pestaña Resultados',
                        'leido'     => 0,
                        'creado_en' => date('Y-m-d H:i:s'),
                        'url_accion'=> '?url=crm&sec=cursos',
                        'badge'     => 'warning',
                    ];
                }
            } catch (Exception $e) {}

            // Unread course messages
            try {
                $s = $this->db->query("SELECT COUNT(*) FROM mensaje_curso WHERE leido=0");
                $noLeidos = (int)$s->fetchColumn();
                if ($noLeidos > 0) {
                    $adminAlerts[] = [
                        'id'        => 'msg_unread',
                        'tipo'      => 'mensaje',
                        'titulo'    => "$noLeidos mensaje(s) sin leer en cursos",
                        'cuerpo'    => 'Ver en la sección Comunicación',
                        'leido'     => 0,
                        'creado_en' => date('Y-m-d H:i:s'),
                        'url_accion'=> '?url=crm&sec=comunicacion',
                        'badge'     => 'info',
                    ];
                }
            } catch (Exception $e) {}
        }

        $unread = count(array_filter($notifs, fn($n) => !$n['leido'])) + count($adminAlerts);

        return ['ok' => true, 'notifs' => $notifs, 'alerts' => $adminAlerts, 'unread' => $unread];
    }

    private function apiMarcarNotifLeida(): array
    {
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) return ['ok' => false, 'error' => 'ID inválido'];
        try {
            $this->db->prepare("UPDATE notificacion SET leido=1 WHERE id=? AND usuario_id=?")->execute([$id, (int)$this->usuario['id']]);
        } catch (Exception $e) {}
        return ['ok' => true];
    }

    private function apiMarcarTodasLeidas(): array
    {
        try {
            $this->db->prepare("UPDATE notificacion SET leido=1 WHERE usuario_id=?")->execute([(int)$this->usuario['id']]);
        } catch (Exception $e) {}
        return ['ok' => true];
    }

    private function notificarUsuarios(string $titulo, string $cuerpo, int $campanaId, string $audiencia = 'todos', ?int $diasRegistro = null): int
    {
        $audienciaFilter = '';
        $params = [$campanaId];

        if ($audiencia === 'nuevos' && $diasRegistro !== null) {
            $audienciaFilter = "AND u.creado_en >= date('now','-{$diasRegistro} days')";
        } elseif ($audiencia === 'matriculados') {
            $audienciaFilter = "AND EXISTS (SELECT 1 FROM matricula m WHERE m.usuario_id=u.id AND m.estado='activa')";
        }

        $stmt = $this->db->prepare("
            SELECT u.id FROM usuario u
            LEFT JOIN notificacion n ON n.usuario_id=u.id AND n.tipo='crm' AND n.ref_id=?
            WHERE (u.notificaciones IS NULL OR u.notificaciones=1)
              AND n.id IS NULL
              $audienciaFilter
        ");
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $ins = $this->db->prepare("INSERT OR IGNORE INTO notificacion (usuario_id,tipo,titulo,cuerpo,ref_id) VALUES(?,?,?,?,?)");
        foreach ($usuarios as $uid) {
            $ins->execute([$uid, 'crm', $titulo, $cuerpo, $campanaId]);
        }
        return count($usuarios);
    }
}
