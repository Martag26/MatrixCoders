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
            $conexion->exec(file_get_contents($crmMigrateSql));
        }

        // Añadir columna intentos a resultado_examen si no existe (SQLite no soporta IF NOT EXISTS en ALTER)
        try { $conexion->exec("ALTER TABLE resultado_examen ADD COLUMN intentos INTEGER NOT NULL DEFAULT 1"); } catch (\Exception $e) {}
        // Añadir columna estado a matricula si no existe
        try { $conexion->exec("ALTER TABLE matricula ADD COLUMN estado TEXT NOT NULL DEFAULT 'activa'"); } catch (\Exception $e) {}

        return $conexion;
    }
}
