<?php
require_once __DIR__ . '/../config.php';

class SuscripcionController
{
    public function index()
    {
        $pageTitle = "Suscripciones";
        require __DIR__ . '/../views/suscripciones/index.php';
    }
}
