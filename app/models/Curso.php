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

    /**
     * Obtiene todos los cursos disponibles en la base de datos.
     *
     * Ejecuta una consulta SELECT sobre la tabla "curso" y devuelve
     * todos los registros encontrados como un array asociativo.
     *
     * @return array Array de cursos, cada uno como array asociativo.
     */
    public function obtenerTodos()
    {
        // Consulta para recuperar todos los cursos sin filtro
        $sql = "SELECT * FROM curso";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        // Devolver todos los resultados como array asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
