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

        $migrateSql = __DIR__ . '/data/migrate.sql';
        if (file_exists($migrateSql)) {
            $conexion->exec(file_get_contents($migrateSql));
        }

        $crmMigrateSql = __DIR__ . '/data/crm_migrate.sql';
        if (file_exists($crmMigrateSql)) {
            try { $conexion->exec(file_get_contents($crmMigrateSql)); } catch (\Exception $e) {}
        }

        // Columnas opcionales que se añaden progresivamente (SQLite no soporta IF NOT EXISTS en ALTER TABLE)
        foreach ([
            "ALTER TABLE resultado_examen ADD COLUMN intentos INTEGER NOT NULL DEFAULT 1",
            "ALTER TABLE matricula        ADD COLUMN estado   TEXT    NOT NULL DEFAULT 'activa'",
            "ALTER TABLE notificacion     ADD COLUMN url_accion TEXT   DEFAULT NULL",
            "ALTER TABLE notificacion     ADD COLUMN ref_id    INTEGER DEFAULT NULL",
            "ALTER TABLE examen           ADD COLUMN tipo         TEXT    NOT NULL DEFAULT 'test'",
            "ALTER TABLE examen           ADD COLUMN fecha_entrega TEXT   DEFAULT NULL",
            "ALTER TABLE examen           ADD COLUMN modo_entrega  TEXT   NOT NULL DEFAULT 'cualquiera'",
            "ALTER TABLE mensaje          ADD COLUMN reply_to_id  INTEGER DEFAULT NULL",
            "ALTER TABLE mensaje          ADD COLUMN hilo_id      INTEGER DEFAULT NULL",
            "ALTER TABLE incidencia       ADD COLUMN cuerpo       TEXT    DEFAULT NULL",
            "ALTER TABLE incidencia       ADD COLUMN cerrado_en   TEXT    DEFAULT NULL",
            "ALTER TABLE incidencia       ADD COLUMN actualizado_en TEXT  DEFAULT (datetime('now'))",
        ] as $sql) {
            try { $conexion->exec($sql); } catch (\Exception $e) {}
        }

        // ── Migración: examen — cambiar UNIQUE(curso_id) por UNIQUE(curso_id, tipo) ──────────────
        // Necesario para que un mismo curso pueda tener examen tipo 'test' y tipo 'practico'.
        // FK se desactivan temporalmente para evitar que DROP TABLE CASCADE elimine resultado_examen.
        try {
            $done = $conexion->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='_mig_examen_tipo'")->fetchColumn();
            if (!$done) {
                $conexion->exec("PRAGMA foreign_keys = OFF");
                $conexion->exec("
                    CREATE TABLE examen_new (
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
                    )
                ");
                $conexion->exec("INSERT OR IGNORE INTO examen_new (id, curso_id, titulo, descripcion, nota_minima, tipo)
                                 SELECT id, curso_id, titulo, descripcion, nota_minima, COALESCE(tipo,'test') FROM examen");
                $conexion->exec("DROP TABLE examen");
                $conexion->exec("ALTER TABLE examen_new RENAME TO examen");
                $conexion->exec("CREATE TABLE _mig_examen_tipo (done INTEGER DEFAULT 1)");
                $conexion->exec("PRAGMA foreign_keys = ON");
            }
        } catch (\Exception $e) {
            try { $conexion->exec("PRAGMA foreign_keys = ON"); } catch (\Exception $e2) {}
        }

        // ── Migración: notificacion — eliminar CHECK restrictivo, añadir url_accion y ref_id ─────
        // El CHECK original sólo permitía tipos básicos; el sistema de exámenes usa tipos adicionales.
        try {
            $done = $conexion->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='_mig_notif_v2'")->fetchColumn();
            if (!$done) {
                $conexion->exec("PRAGMA foreign_keys = OFF");
                $conexion->exec("
                    CREATE TABLE notificacion_new (
                        id         INTEGER PRIMARY KEY AUTOINCREMENT,
                        usuario_id INTEGER NOT NULL,
                        tipo       TEXT    NOT NULL DEFAULT 'info',
                        titulo     TEXT    NOT NULL,
                        cuerpo     TEXT    DEFAULT NULL,
                        leido      INTEGER NOT NULL DEFAULT 0,
                        url_accion TEXT    DEFAULT NULL,
                        ref_id     INTEGER DEFAULT NULL,
                        creado_en  TEXT    NOT NULL DEFAULT (datetime('now')),
                        FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
                    )
                ");
                // url_accion y ref_id ya existen porque el ALTER TABLE se ejecutó antes
                $conexion->exec("INSERT OR IGNORE INTO notificacion_new (id, usuario_id, tipo, titulo, cuerpo, leido, url_accion, ref_id, creado_en)
                                 SELECT id, usuario_id, tipo, titulo, cuerpo, leido, url_accion, ref_id, creado_en FROM notificacion");
                $conexion->exec("DROP TABLE notificacion");
                $conexion->exec("ALTER TABLE notificacion_new RENAME TO notificacion");
                $conexion->exec("CREATE TABLE _mig_notif_v2 (done INTEGER DEFAULT 1)");
                $conexion->exec("PRAGMA foreign_keys = ON");
            }
        } catch (\Exception $e) {
            try { $conexion->exec("PRAGMA foreign_keys = ON"); } catch (\Exception $e2) {}
        }

        // Columnas de rol avanzado (las añade CrmController, pero las necesitamos antes del INSERT)
        foreach ([
            "ALTER TABLE usuario ADD COLUMN es_superadmin INTEGER NOT NULL DEFAULT 0",
            "ALTER TABLE usuario ADD COLUMN es_moderador  INTEGER NOT NULL DEFAULT 0",
        ] as $sql) {
            try { $conexion->exec($sql); } catch (\Exception $e) {}
        }

        // Superadmin fijo — isidoro@admin.com / contraseña: usuario
        $conexion->prepare(
            "INSERT OR IGNORE INTO usuario (nombre, email, contraseña, rol, es_superadmin)
             VALUES ('Isidoro Admin', 'isidoro@admin.com',
                     '\$2y\$10\$/UWnFCC4Z/qsCsy1OLGrWOXc7tn1bnejZhxohRHDonSZ3gAy64jn2',
                     'ADMINISTRADOR', 1)"
        )->execute();

        return $conexion;
    }
}
