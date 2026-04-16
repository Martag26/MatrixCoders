<?php

/**
 * Controlador de suscripciones.
 *
 * Gestiona las páginas relacionadas con los planes de suscripción
 * disponibles en la plataforma.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

class SuscripcionController
{
    /**
     * Muestra la página de planes de suscripción.
     *
     * Si el usuario está logueado, carga también su plan activo
     * para mostrarlo en la vista.
     *
     * @return void
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $planActivo = null;

        if (!empty($_SESSION['usuario_id'])) {
            $database = new Database();
            $conexion = $database->connect();
            $stmt = $conexion->prepare("SELECT plan FROM suscripcion WHERE usuario_id = ? AND status = 'activa' LIMIT 1");
            $stmt->execute([(int)$_SESSION['usuario_id']]);
            $suscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
            $planActivo = $suscripcion['plan'] ?? null;
            $_SESSION['usuario_plan'] = $planActivo;
        }

        $okMsg = $_SESSION['suscripcion_ok'] ?? '';
        unset($_SESSION['suscripcion_ok']);

        $pageTitle = "Suscripciones";
        require __DIR__ . '/../views/suscripciones/index.php';
    }

    /**
     * Guarda el plan elegido por el usuario en la tabla suscripcion.
     *
     * Si ya tiene una suscripción la actualiza; si no, la crea.
     * Al terminar actualiza la sesión y redirige a la página de planes.
     *
     * @return void
     */
    public function contratar()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=login");
            exit;
        }

        $plan = trim($_POST['plan'] ?? '');
        $planesValidos = ['curso_individual', 'plan_estudiantes', 'plan_empresas'];

        if (!in_array($plan, $planesValidos)) {
            header("Location: " . BASE_URL . "/index.php?url=suscripciones");
            exit;
        }

        $database = new Database();
        $conexion = $database->connect();
        $usuario_id = (int)$_SESSION['usuario_id'];

        // Comprobar si ya tiene una suscripción
        $stmt = $conexion->prepare("SELECT id FROM suscripcion WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuario_id]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $stmt = $conexion->prepare("UPDATE suscripcion SET plan = ?, status = 'activa' WHERE usuario_id = ?");
            $stmt->execute([$plan, $usuario_id]);
        } else {
            $stmt = $conexion->prepare("INSERT INTO suscripcion (usuario_id, plan, status) VALUES (?, ?, 'activa')");
            $stmt->execute([$usuario_id, $plan]);
        }

        $_SESSION['usuario_plan'] = $plan;
        $_SESSION['suscripcion_ok'] = "Plan contratado correctamente.";

        header("Location: " . BASE_URL . "/index.php?url=suscripciones");
        exit;
    }
}
