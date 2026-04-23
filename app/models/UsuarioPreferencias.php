<?php
class UsuarioPreferencias
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtener(int $usuario_id): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuario_preferencias WHERE usuario_id = ?"
        );
        $stmt->execute([$usuario_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [
            'usuario_id'        => $usuario_id,
            'perfil'            => 'estudiante',
            'sidebar_colapsado' => 0,
        ];
    }

    public function upsert(int $usuario_id, array $datos): bool
    {
        $pref      = $this->obtener($usuario_id);
        $perfil    = $datos['perfil']            ?? $pref['perfil'];
        $colapsado = array_key_exists('sidebar_colapsado', $datos)
            ? (int)$datos['sidebar_colapsado']
            : (int)$pref['sidebar_colapsado'];

        if (!in_array($perfil, ['principiante', 'estudiante', 'trabajador'], true)) {
            $perfil = 'estudiante';
        }

        $stmt = $this->db->prepare("
            INSERT INTO usuario_preferencias (usuario_id, perfil, sidebar_colapsado)
            VALUES (?, ?, ?)
            ON CONFLICT(usuario_id) DO UPDATE SET
                perfil            = excluded.perfil,
                sidebar_colapsado = excluded.sidebar_colapsado
        ");
        return $stmt->execute([$usuario_id, $perfil, $colapsado]);
    }
}
