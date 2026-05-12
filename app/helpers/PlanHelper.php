<?php

/**
 * PlanHelper — control de acceso por plan de suscripción.
 *
 * Uso:
 *   require_once __DIR__ . '/../helpers/PlanHelper.php';
 *   PlanHelper::requiere('plan_estudiantes');  // redirige si no tiene el plan
 *   if (PlanHelper::tiene('plan_empresas')) { ... }
 */
class PlanHelper
{
    // Jerarquía de planes: cuanto mayor el índice, más privilegios
    private const JERARQUIA = [
        null                => 0,
        ''                  => 0,
        'curso_individual'  => 1,
        'plan_estudiantes'  => 2,
        'plan_empresas'     => 3,
    ];

    public static function planActivo(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['usuario_plan'] ?? null;
    }

    /** True si el usuario tiene al menos el plan indicado. */
    public static function tiene(string $planMinimo): bool
    {
        $actual    = self::planActivo();
        $nivelAct  = self::JERARQUIA[$actual]     ?? 0;
        $nivelMin  = self::JERARQUIA[$planMinimo] ?? 99;
        return $nivelAct >= $nivelMin;
    }

    /**
     * Redirige a la pantalla de upgrade si el usuario no tiene el plan requerido.
     * Llama a exit() internamente.
     */
    public static function requiere(string $planMinimo): void
    {
        if (!self::tiene($planMinimo)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['upgrade_requerido'] = $planMinimo;
            $base = defined('BASE_URL') ? BASE_URL : '';
            header("Location: {$base}/index.php?url=upgrade");
            exit;
        }
    }

    /** Etiqueta legible del plan. */
    public static function etiqueta(?string $plan): string
    {
        return match ($plan) {
            'curso_individual' => 'Curso Individual',
            'plan_estudiantes' => 'Plan Estudiantes',
            'plan_empresas'    => 'Plan Empresas',
            default            => 'Plan gratuito',
        };
    }

    /** Color CSS del badge del plan. */
    public static function badgeClass(?string $plan): string
    {
        return match ($plan) {
            'plan_empresas'    => 'plan-badge-empresas',
            'plan_estudiantes' => 'plan-badge-estudiantes',
            'curso_individual' => 'plan-badge-individual',
            default            => 'plan-badge-free',
        };
    }
}
