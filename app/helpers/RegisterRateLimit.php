<?php
/**
 * Limita el número de registros por IP en una ventana de tiempo.
 *
 * Por defecto: máximo 5 registros desde la misma IP en 1 hora.
 * Sirve para que un bot no cree miles de cuentas basura.
 */
class RegisterRateLimit
{
    private PDO $db;
    private int $maxPerWindow;
    private int $windowSeconds;

    public function __construct(PDO $db, int $maxPerWindow = 5, int $windowSeconds = 3600)
    {
        $this->db = $db;
        $this->maxPerWindow = $maxPerWindow;
        $this->windowSeconds = $windowSeconds;
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS registro_intentos (
                ip       TEXT NOT NULL,
                creado_en INTEGER NOT NULL
            )
        ");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_reg_ip_creado ON registro_intentos(ip, creado_en)");
    }

    public function permitido(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $desde = time() - $this->windowSeconds;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM registro_intentos WHERE ip = ? AND creado_en >= ?");
        $stmt->execute([$ip, $desde]);
        return ((int)$stmt->fetchColumn()) < $this->maxPerWindow;
    }

    public function registrar(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $this->db->prepare("INSERT INTO registro_intentos (ip, creado_en) VALUES (?, ?)")
                 ->execute([$ip, time()]);

        // Limpieza oportunista: borra registros viejos
        $this->db->prepare("DELETE FROM registro_intentos WHERE creado_en < ?")
                 ->execute([time() - $this->windowSeconds * 24]);
    }
}
