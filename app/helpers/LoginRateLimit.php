<?php
/**
 * Limitador de intentos de login.
 *
 * Guarda los intentos fallidos en una tabla SQLite con (clave, intentos, bloqueo_hasta).
 * La clave combina IP + email para no bloquear a otros usuarios desde la misma IP
 * (típico en redes universitarias, NAT corporativo, etc.).
 *
 * Configuración por defecto: 8 intentos fallidos → bloqueo 15 min.
 */
class LoginRateLimit
{
    private PDO $db;
    private int $maxAttempts;
    private int $lockSeconds;

    public function __construct(PDO $db, int $maxAttempts = 8, int $lockSeconds = 900)
    {
        $this->db          = $db;
        $this->maxAttempts = $maxAttempts;
        $this->lockSeconds = $lockSeconds;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS login_intentos (
                clave         TEXT PRIMARY KEY,
                intentos      INTEGER NOT NULL DEFAULT 0,
                ultimo_intento INTEGER NOT NULL DEFAULT 0,
                bloqueo_hasta INTEGER NOT NULL DEFAULT 0
            )
        ");
    }

    private function key(string $email): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        return strtolower(trim($email)) . '|' . $ip;
    }

    /**
     * Devuelve 0 si puede intentar; si está bloqueado, devuelve los segundos
     * que faltan para que se desbloquee.
     */
    public function bloqueadoSegundos(string $email): int
    {
        $stmt = $this->db->prepare("SELECT bloqueo_hasta FROM login_intentos WHERE clave = ?");
        $stmt->execute([$this->key($email)]);
        $hasta = (int)($stmt->fetchColumn() ?: 0);
        $ahora = time();
        return $hasta > $ahora ? ($hasta - $ahora) : 0;
    }

    public function registrarFallo(string $email): void
    {
        $clave = $this->key($email);
        $ahora = time();

        $stmt = $this->db->prepare("SELECT intentos FROM login_intentos WHERE clave = ?");
        $stmt->execute([$clave]);
        $intentos = (int)($stmt->fetchColumn() ?: 0) + 1;

        $bloqueoHasta = ($intentos >= $this->maxAttempts) ? ($ahora + $this->lockSeconds) : 0;

        $this->db->prepare("
            INSERT INTO login_intentos (clave, intentos, ultimo_intento, bloqueo_hasta)
            VALUES (?, ?, ?, ?)
            ON CONFLICT(clave) DO UPDATE SET
                intentos = excluded.intentos,
                ultimo_intento = excluded.ultimo_intento,
                bloqueo_hasta = CASE WHEN excluded.bloqueo_hasta > 0
                                     THEN excluded.bloqueo_hasta
                                     ELSE login_intentos.bloqueo_hasta END
        ")->execute([$clave, $intentos, $ahora, $bloqueoHasta]);
    }

    public function registrarExito(string $email): void
    {
        $this->db->prepare("DELETE FROM login_intentos WHERE clave = ?")
                 ->execute([$this->key($email)]);
    }
}
