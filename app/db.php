<?php

/**
 * Clase de conexión a la base de datos.
 *
 * Gestiona la conexión PDO con SQLite (base de datos embebida dentro
 * del propio proyecto en app/data/database.sqlite).
 *
 * Si el archivo todavía no existe, lo crea y ejecuta app/data/init.sql
 * para inicializar el esquema y los datos de ejemplo automáticamente.
 */
class Database
{
    /** Ruta al archivo SQLite de la aplicación */
    private static string $dbPath  = __DIR__ . '/data/database.sqlite';

    /** Ruta al script SQL de inicialización */
    private static string $initSql = __DIR__ . '/data/init.sql';

    /**
     * Crea y devuelve una conexión PDO a la base de datos SQLite.
     *
     * @return PDO Instancia de la conexión activa.
     * @throws PDOException Si la conexión o la inicialización falla.
     */
    public function connect(): PDO
    {
        $isNew = !file_exists(self::$dbPath);

        $conexion = new PDO('sqlite:' . self::$dbPath);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Activar claves foráneas (SQLite las ignora por defecto)
        $conexion->exec('PRAGMA foreign_keys = ON');
        // WAL mejora el rendimiento en lecturas/escrituras concurrentes
        $conexion->exec('PRAGMA journal_mode = WAL');

        if ($isNew) {
            $sql = file_get_contents(self::$initSql);
            $conexion->exec($sql);
        }

        // Recover from incomplete table-rename migrations
        $tables = $conexion->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        $tables = array_flip($tables);
        if (isset($tables['_examen_old']) && !isset($tables['examen'])) {
            $conexion->exec("ALTER TABLE _examen_old RENAME TO examen");
        }
        if (isset($tables['_notificacion_old']) && !isset($tables['notificacion'])) {
            $conexion->exec("ALTER TABLE _notificacion_old RENAME TO notificacion");
        }

        $conexion->exec('PRAGMA foreign_keys = OFF');

        $migrateSql = __DIR__ . '/data/migrate.sql';
        if (file_exists($migrateSql)) {
            $conexion->exec(file_get_contents($migrateSql));
        }

        $crmMigrateSql = __DIR__ . '/data/crm_migrate.sql';
        if (file_exists($crmMigrateSql)) {
            $conexion->exec(file_get_contents($crmMigrateSql));
        }

        $conexion->exec('PRAGMA foreign_keys = ON');

        // Añadir columna intentos a resultado_examen si no existe (SQLite no soporta IF NOT EXISTS en ALTER)
        try { $conexion->exec("ALTER TABLE resultado_examen ADD COLUMN intentos INTEGER NOT NULL DEFAULT 1"); } catch (\Exception $e) {}
        // Añadir columna estado a matricula si no existe
        try { $conexion->exec("ALTER TABLE matricula ADD COLUMN estado TEXT NOT NULL DEFAULT 'activa'"); } catch (\Exception $e) {}

        // Migrar examen: cambiar UNIQUE(curso_id) → UNIQUE(curso_id, tipo) para permitir test + práctico por curso
        $examenSql = $conexion->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='examen'")->fetchColumn();
        if ($examenSql && strpos($examenSql, 'curso_id, tipo') === false && strpos($examenSql, 'curso_id,tipo') === false) {
            try {
                $conexion->exec("PRAGMA foreign_keys = OFF");
                $conexion->exec("BEGIN");
                $conexion->exec("ALTER TABLE examen RENAME TO _examen_old");
                $conexion->exec("CREATE TABLE examen (
                    id            INTEGER PRIMARY KEY AUTOINCREMENT,
                    curso_id      INTEGER NOT NULL,
                    titulo        TEXT    NOT NULL,
                    descripcion   TEXT    DEFAULT NULL,
                    nota_minima   REAL    NOT NULL DEFAULT 5.0,
                    tipo          TEXT    NOT NULL DEFAULT 'test',
                    fecha_entrega TEXT    DEFAULT NULL,
                    modo_entrega  TEXT    NOT NULL DEFAULT 'cualquiera',
                    UNIQUE (curso_id, tipo),
                    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE CASCADE
                )");
                $conexion->exec("INSERT INTO examen SELECT id,curso_id,titulo,descripcion,nota_minima,
                    COALESCE(tipo,'test'),
                    CASE WHEN typeof(fecha_entrega)='text' THEN fecha_entrega ELSE NULL END,
                    COALESCE(modo_entrega,'cualquiera')
                    FROM _examen_old");
                $conexion->exec("DROP TABLE _examen_old");
                $conexion->exec("COMMIT");
                $conexion->exec("PRAGMA foreign_keys = ON");
            } catch (\Exception $e) {
                try { $conexion->exec("ROLLBACK"); } catch (\Exception $_) {}
                $conexion->exec("PRAGMA foreign_keys = ON");
            }
        }

        // Ampliar tipos de notificacion si la restricción CHECK no incluye los nuevos tipos
        $notifSql = $conexion->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='notificacion'")->fetchColumn();
        if ($notifSql && strpos($notifSql, 'examen_teorico') === false) {
            try {
                $conexion->exec("PRAGMA foreign_keys = OFF");
                $conexion->exec("BEGIN");
                $conexion->exec("ALTER TABLE notificacion RENAME TO _notificacion_old");
                $conexion->exec("CREATE TABLE notificacion (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    usuario_id INTEGER NOT NULL,
                    tipo       TEXT    NOT NULL DEFAULT 'info'
                               CHECK (tipo IN ('info','tarea','tarea_vencida','mensaje','expiracion','crm',
                                               'evento_calendario','examen_teorico','examen_practico',
                                               'revision_pendiente','nueva_matricula')),
                    titulo     TEXT    NOT NULL,
                    cuerpo     TEXT    DEFAULT NULL,
                    leido      INTEGER NOT NULL DEFAULT 0,
                    url_accion TEXT    DEFAULT NULL,
                    ref_id     INTEGER DEFAULT NULL,
                    creado_en  TEXT    NOT NULL DEFAULT (datetime('now')),
                    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
                )");
                $conexion->exec("INSERT INTO notificacion SELECT * FROM _notificacion_old");
                $conexion->exec("DROP TABLE _notificacion_old");
                $conexion->exec("COMMIT");
                $conexion->exec("PRAGMA foreign_keys = ON");
            } catch (\Exception $e) {
                try { $conexion->exec("ROLLBACK"); } catch (\Exception $_) {}
                $conexion->exec("PRAGMA foreign_keys = ON");
            }
        }

        return $conexion;
    }
}
