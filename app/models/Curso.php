<?php

class Curso
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function obtenerTodos()
    {

        $sql = "SELECT * FROM curso";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
