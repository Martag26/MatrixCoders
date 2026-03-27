<?php
<<<<<<< HEAD

/**
 * Controlador de suscripciones.
 *
 * Gestiona las páginas relacionadas con los planes de suscripción
 * disponibles en la plataforma.
 */

=======
>>>>>>> develop-marta
require_once __DIR__ . '/../config.php';

class SuscripcionController
{
<<<<<<< HEAD
    /**
     * Muestra la página de planes de suscripción.
     *
     * Establece el título de la página y carga la vista
     * que presenta los distintos planes disponibles al usuario.
     *
     * @return void
     */
=======
>>>>>>> develop-marta
    public function index()
    {
        $pageTitle = "Suscripciones";
        require __DIR__ . '/../views/suscripciones/index.php';
    }
}
