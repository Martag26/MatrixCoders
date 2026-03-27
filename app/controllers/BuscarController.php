<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Curso.php';

class BuscarController
{
    public function index()
    {
        $database = new Database();
        $db       = $database->connect();

        $q       = trim($_GET['q'] ?? '');
        $pagina  = max(1, (int)($_GET['p'] ?? 1));
        $porPagina = 9;

        $cursoModel  = new Curso($db);
        $cursos      = $q !== '' ? $cursoModel->buscar($q, $pagina, $porPagina) : [];
        $total       = $q !== '' ? $cursoModel->contarBusqueda($q) : 0;
        $totalPaginas = $total > 0 ? ceil($total / $porPagina) : 1;

        $pageTitle = "Buscar cursos";

        require __DIR__ . '/../views/buscar/index.php';
    }
}
