<?php

/**
 * Controlador de cursos.
 *
 * Gestiona las operaciones relacionadas con los cursos disponibles
 * en la plataforma, como listar todos los cursos existentes.
 */

require_once "../app/db.php";
require_once "../app/models/Curso.php";

class CursoController
{
    /**
     * Muestra el listado completo de cursos.
     *
     * Obtiene todos los cursos disponibles en la base de datos
     * a través del modelo Curso y los pasa a la vista para su presentación.
     *
     * @return void
     */
    public function index()
    {
        // Crear conexión a la base de datos
        $database = new Database();
        $db = $database->connect();

        // Instanciar el modelo Curso pasándole la conexión
        $cursoModel = new Curso($db);
        $cursos = $cursoModel->obtenerDestacados();

        // IDs de cursos en los que el usuario ya está matriculado
        $matriculasUsuario = [];
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if ($usuarioId) {
            $stmtM = $db->prepare('SELECT curso_id FROM matricula WHERE usuario_id = ?');
            $stmtM->execute([(int)$usuarioId]);
            $matriculasUsuario = $stmtM->fetchAll(PDO::FETCH_COLUMN);
        }

        // Cargar la vista que renderiza el listado de cursos
        require "../app/views/cursos/index.php";
    }
}
