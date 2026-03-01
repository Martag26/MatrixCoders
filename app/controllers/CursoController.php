<?php

require_once "../app/db.php";
require_once "../app/models/Curso.php";

class CursoController
{

    public function index()
    {

        $database = new Database();
        $db = $database->connect();

        $cursoModel = new Curso($db);
        $cursos = $cursoModel->obtenerTodos();

        require "../app/views/cursos/index.php";
    }
}
