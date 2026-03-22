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
}
