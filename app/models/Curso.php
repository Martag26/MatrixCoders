<?php

class Curso
{

    private $db;

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
        WHERE usuario_id = ? AND curso_id = ? AND estado = 'activo'
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
        VALUES (?, ?, NOW(), 'activo')
    ");
        return $stmt->execute([$usuarioId, $cursoId]);
    }
}
