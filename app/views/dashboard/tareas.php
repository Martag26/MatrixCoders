<?php
$tareas = is_array($tareas ?? null) ? $tareas : [];
$tareasPorCurso = is_array($tareasPorCurso ?? null) ? $tareasPorCurso : [];
$resumen = is_array($resumen ?? null) ? $resumen : ['total' => 0, 'pendientes' => 0, 'proximas' => 0, 'vencidas' => 0, 'entregadas' => 0];

function tarea_fecha_legible(?string $fecha): string
{
    if (!$fecha) {
        return 'Sin fecha';
    }

    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y', $ts) : $fecha;
}

function tarea_estado_label(string $estado): string
{
    return match ($estado) {
        'entregada' => 'Entregada',
        'vencida' => 'Vencida',
        'proxima' => 'Próxima entrega',
        default => 'Pendiente',
    };
}

function tarea_estado_meta(array $tarea): string
{
    $estado = $tarea['estado_visual'] ?? 'pendiente';
    $dias = $tarea['dias_restantes'] ?? null;

    if ($estado === 'entregada') {
        if (!empty($tarea['entregado_en'])) {
            return 'Entregada el ' . date('d/m/Y', strtotime($tarea['entregado_en']));
        }
        return 'Entrega registrada';
    }

    if ($dias === null) {
        return 'Sin fecha límite definida';
    }

    if ($dias < 0) {
        return 'Venció hace ' . abs($dias) . ' día' . (abs($dias) !== 1 ? 's' : '');
    }

    if ($dias === 0) {
        return 'Entrega hoy';
    }

    return 'Faltan ' . $dias . ' día' . ($dias !== 1 ? 's' : '');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Tareas') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard contenedor-dashboard-content">
                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <section class="workspace-page-shell tareas-page">
                    <div class="tareas-page-head">
                        <div>
                            <span class="template-kicker">Workspace</span>
                            <h1>Panel de tareas</h1>
                            <p>Consulta entregas por curso, detecta vencimientos y entra rápido a la lección relacionada.</p>
                        </div>
                        <a class="btn-panel-submit" href="<?= BASE_URL ?>/index.php?url=calendario">Ver calendario</a>
                    </div>

                    <div class="tareas-summary-grid">
                        <article class="tareas-summary-card">
                            <span>Total</span>
                            <strong><?= (int)$resumen['total'] ?></strong>
                            <p>Tareas visibles en tus cursos activos.</p>
                        </article>
                        <article class="tareas-summary-card">
                            <span>Próximas</span>
                            <strong><?= (int)$resumen['proximas'] ?></strong>
                            <p>Entregas con fecha cercana.</p>
                        </article>
                        <article class="tareas-summary-card">
                            <span>Vencidas</span>
                            <strong><?= (int)$resumen['vencidas'] ?></strong>
                            <p>Requieren revisión o nueva planificación.</p>
                        </article>
                        <article class="tareas-summary-card">
                            <span>Entregadas</span>
                            <strong><?= (int)$resumen['entregadas'] ?></strong>
                            <p>Quedan registradas como completadas.</p>
                        </article>
                    </div>

                    <?php if (empty($tareas)): ?>
                        <div class="tareas-empty-card">
                            <h2>No hay tareas activas</h2>
                            <p>Cuando los cursos tengan entregas asociadas, aparecerán aquí con su estado y acceso rápido.</p>
                        </div>
                    <?php else: ?>
                        <div class="tareas-board">
                            <div class="tareas-list-card">
                                <div class="dashboard-section-head">
                                    <h2>Próximas entregas</h2>
                                    <span class="section-link-static"><?= count($tareas) ?> registradas</span>
                                </div>

                                <div class="tareas-list">
                                    <?php foreach ($tareas as $tarea): ?>
                                        <?php
                                        $estado = $tarea['estado_visual'] ?? 'pendiente';
                                        $leccionUrl = !empty($tarea['leccion_id'])
                                            ? BASE_URL . '/index.php?url=leccion&id=' . (int)$tarea['leccion_id']
                                            : BASE_URL . '/index.php?url=detallecurso&id=' . (int)($tarea['curso_id'] ?? 0);
                                        ?>
                                        <article class="tarea-prof-card estado-<?= htmlspecialchars($estado) ?>">
                                            <div class="tarea-prof-main">
                                                <div class="tarea-prof-topline">
                                                    <span class="tarea-estado-badge"><?= htmlspecialchars(tarea_estado_label($estado)) ?></span>
                                                    <span class="tarea-curso-chip"><?= htmlspecialchars($tarea['curso'] ?? 'Curso') ?></span>
                                                </div>
                                                <h3><?= htmlspecialchars($tarea['titulo'] ?? 'Tarea') ?></h3>
                                                <p><?= htmlspecialchars(tarea_estado_meta($tarea)) ?></p>
                                                <?php if (!empty($tarea['leccion'])): ?>
                                                    <span class="tarea-leccion-ref">Lección: <?= htmlspecialchars($tarea['leccion']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="tarea-prof-side">
                                                <strong><?= htmlspecialchars(tarea_fecha_legible($tarea['fecha_limite'] ?? null)) ?></strong>
                                                <a class="section-link" href="<?= $leccionUrl ?>">Abrir contexto</a>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="tareas-cursos-card">
                                <div class="dashboard-section-head">
                                    <h2>Carga por curso</h2>
                                    <span class="section-link-static">Vista agrupada</span>
                                </div>

                                <div class="tareas-course-groups">
                                    <?php foreach ($tareasPorCurso as $curso => $items): ?>
                                        <section class="tareas-course-group">
                                            <div class="tareas-course-head">
                                                <h3><?= htmlspecialchars($curso) ?></h3>
                                                <span><?= count($items) ?> tarea<?= count($items) !== 1 ? 's' : '' ?></span>
                                            </div>
                                            <div class="tareas-course-mini-list">
                                                <?php foreach ($items as $item): ?>
                                                    <article class="tareas-course-mini estado-<?= htmlspecialchars($item['estado_visual'] ?? 'pendiente') ?>">
                                                        <strong><?= htmlspecialchars($item['titulo'] ?? 'Tarea') ?></strong>
                                                        <span><?= htmlspecialchars(tarea_fecha_legible($item['fecha_limite'] ?? null)) ?></span>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                        </section>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
</body>

</html>
