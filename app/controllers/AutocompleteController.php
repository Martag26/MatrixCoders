<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Curso.php';

class AutocompleteController
{
    public function index()
    {
        // Solo respondemos peticiones AJAX
        header('Content-Type: application/json');

        $q = trim($_GET['q'] ?? '');

        // Si hay menos de 2 caracteres no buscamos
        if (strlen($q) < 1) {
            echo json_encode([]);
            exit;
        }

        $database = new Database();
        $db       = $database->connect();

        $cursoModel    = new Curso($db);
        $sugerencias   = $cursoModel->sugerencias($q);

        echo json_encode($sugerencias);
        exit;
    }
}
