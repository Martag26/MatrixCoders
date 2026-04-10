<?php

/**
 * Modelo de Curso.
 *
 * Representa la entidad "curso" de la plataforma y encapsula
 * todas las operaciones de acceso a datos relacionadas con los cursos.
 */

class Curso
{
    /**
     * Instancia de la conexión a la base de datos.
     *
     * @var PDO
     */
    private $db;

    /**
     * Constructor del modelo.
     *
     * Recibe e inyecta la conexión a la base de datos para
     * que pueda ser utilizada en los métodos de la clase.
     *
     * @param PDO $db Conexión activa a la base de datos.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    // Busca cursos por título o descripción con paginación
    public function buscar(string $q, int $pagina = 1, int $porPagina = 9): array
    {
        $offset = ($pagina - 1) * $porPagina;

        $stmt = $this->db->prepare("
        SELECT * FROM curso
        WHERE titulo LIKE ? OR descripcion LIKE ?
        ORDER BY titulo ASC
        LIMIT ? OFFSET ?
    ");
        $stmt->bindValue(1, "%$q%");
        $stmt->bindValue(2, "%$q%");
        $stmt->bindValue(3, $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(4, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cuenta el total de resultados para calcular las páginas
    public function contarBusqueda(string $q): int
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM curso
        WHERE titulo LIKE ? OR descripcion LIKE ?
    ");
        $stmt->execute(["%$q%", "%$q%"]);
        return (int)$stmt->fetchColumn();
    }

    public function obtenerDestacados(int $limite = 3): array
    {
        $stmt = $this->db->prepare("
        SELECT c.*, COUNT(m.id) AS total_matriculas
        FROM curso c
        LEFT JOIN matricula m ON m.curso_id = c.id
        GROUP BY c.id
        ORDER BY total_matriculas DESC, c.id DESC
        LIMIT ?
    ");
        // Forzamos el tipo entero para que PDO no lo trate como string
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve títulos de cursos que coincidan con el texto — máximo 6 sugerencias
    public function sugerencias(string $q, int $limite = 6): array
    {
        $stmt = $this->db->prepare("
        SELECT id, titulo FROM curso
        WHERE titulo LIKE ?
        ORDER BY titulo ASC
        LIMIT ?
    ");
        $stmt->bindValue(1, "%$q%");
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve los datos completos de un curso junto con el total de matriculados.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(m.id) AS total_matriculas
            FROM curso c
            LEFT JOIN matricula m ON m.curso_id = c.id
            WHERE c.id = ?
            GROUP BY c.id
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Devuelve las unidades del curso con sus lecciones anidadas.
     * Asume tablas: unidad (id, curso_id, titulo, orden)
     *               leccion (id, unidad_id, titulo, duracion_min, orden)
     * TODO: ajusta los nombres de tabla si son diferentes en tu BD.
     */
    public function getUnidadesConLecciones(int $cursoId): array
    {
        // Unidades
        $stmt = $this->db->prepare("
            SELECT * FROM unidad
            WHERE curso_id = ?
            ORDER BY orden ASC, id ASC
        ");
        $stmt->execute([$cursoId]);
        $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lecciones de cada unidad
        foreach ($unidades as &$u) {
            $stmt2 = $this->db->prepare("
                SELECT * FROM leccion
                WHERE unidad_id = ?
                ORDER BY orden ASC, id ASC
            ");
            $stmt2->execute([$u['id']]);
            $u['lecciones'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $unidades;
    }

    /**
     * Devuelve las tareas del curso con fecha límite.
     * Asume tabla: tarea (id, curso_id, titulo, descripcion, fecha_limite)
     * TODO: ajusta el nombre de tabla si es diferente en tu BD.
     */
    public function getTareasByCurso(int $cursoId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM tarea
            WHERE curso_id = ?
            ORDER BY fecha_limite ASC
        ");
        $stmt->execute([$cursoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Comprueba si un usuario está matriculado en un curso.
     */
    public function estaMatriculado(int $usuarioId, int $cursoId): bool
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM matricula
        WHERE usuario_id = ? AND curso_id = ? AND estado = 'activa'
    ");
        $stmt->execute([$usuarioId, $cursoId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Matricula a un usuario en un curso.
     * Devuelve true si se insertó, false si ya existía u ocurrió un error.
     */
    public function matricular(int $usuarioId, int $cursoId): bool
    {
        if ($this->estaMatriculado($usuarioId, $cursoId)) {
            return false;
        }
        $stmt = $this->db->prepare("
        INSERT INTO matricula (usuario_id, curso_id, fecha, estado)
        VALUES (?, ?, NOW(), 'activa')
    ");
        return $stmt->execute([$usuarioId, $cursoId]);
    }

    /**
     * Devuelve la primera lección del curso (para abrir al entrar).
     */
    public function getPrimeraLeccion(int $cursoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT l.* FROM leccion l
            JOIN unidad u ON l.unidad_id = u.id
            WHERE u.curso_id = ?
            ORDER BY u.orden ASC, l.orden ASC
            LIMIT 1
        ");
        $stmt->execute([$cursoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Devuelve una lección por su ID.
     */
    public function getLeccionById(int $leccionId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM leccion WHERE id = ?");
        $stmt->execute([$leccionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getUnidadById(int $unidadId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM unidad WHERE id = ?");
        $stmt->execute([$unidadId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Lección anterior dentro del mismo curso (por orden de unidad y lección).
     */
    public function getLeccionAnterior(int $leccionId, int $cursoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT l.* FROM leccion l
            JOIN unidad u ON l.unidad_id = u.id
            WHERE u.curso_id = ?
              AND (u.orden < (SELECT u2.orden FROM unidad u2 JOIN leccion l2 ON l2.unidad_id = u2.id WHERE l2.id = ?)
                  OR (u.orden = (SELECT u2.orden FROM unidad u2 JOIN leccion l2 ON l2.unidad_id = u2.id WHERE l2.id = ?)
                      AND l.orden < (SELECT orden FROM leccion WHERE id = ?)))
            ORDER BY u.orden DESC, l.orden DESC
            LIMIT 1
        ");
        $stmt->execute([$cursoId, $leccionId, $leccionId, $leccionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Lección siguiente dentro del mismo curso.
     */
    public function getLeccionSiguiente(int $leccionId, int $cursoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT l.* FROM leccion l
            JOIN unidad u ON l.unidad_id = u.id
            WHERE u.curso_id = ?
              AND (u.orden > (SELECT u2.orden FROM unidad u2 JOIN leccion l2 ON l2.unidad_id = u2.id WHERE l2.id = ?)
                  OR (u.orden = (SELECT u2.orden FROM unidad u2 JOIN leccion l2 ON l2.unidad_id = u2.id WHERE l2.id = ?)
                      AND l.orden > (SELECT orden FROM leccion WHERE id = ?)))
            ORDER BY u.orden ASC, l.orden ASC
            LIMIT 1
        ");
        $stmt->execute([$cursoId, $leccionId, $leccionId, $leccionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Guarda o actualiza la nota de un usuario para una lección.
     */
    public function guardarNota(int $usuarioId, int $leccionId, string $contenido): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO nota (usuario_id, leccion_id, contenido, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE contenido = VALUES(contenido), updated_at = NOW()
        ");
        return $stmt->execute([$usuarioId, $leccionId, $contenido]);
    }

    /**
     * Recupera la nota de un usuario para una lección.
     */
    public function getNota(int $usuarioId, int $leccionId): string
    {
        $stmt = $this->db->prepare("
            SELECT contenido FROM nota WHERE usuario_id = ? AND leccion_id = ?
        ");
        $stmt->execute([$usuarioId, $leccionId]);
        return (string)($stmt->fetchColumn() ?: '');
    }

    /**
     * Marca una lección como vista por el usuario.
     * Usa INSERT IGNORE para no duplicar si ya estaba marcada.
     */
    public function marcarVista(int $usuarioId, int $leccionId): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO leccion_vista (usuario_id, leccion_id, visto_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$usuarioId, $leccionId]);
    }

    /**
     * Devuelve un array indexado de leccion_id que el usuario ya ha visto
     * dentro de un curso concreto.
     */
    public function getLeccionesVistas(int $usuarioId, int $cursoId): array
    {
        $stmt = $this->db->prepare("
            SELECT lv.leccion_id
            FROM leccion_vista lv
            JOIN leccion l  ON l.id  = lv.leccion_id
            JOIN unidad  u  ON u.id  = l.unidad_id
            WHERE lv.usuario_id = ?
              AND u.curso_id    = ?
        ");
        $stmt->execute([$usuarioId, $cursoId]);
        // Devuelve un Set: [leccion_id => true, ...]
        return array_flip($stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
