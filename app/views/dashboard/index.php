<?php
/**
 * Vista del panel principal (Dashboard).
 *
 * Este archivo realiza el preprocesamiento del calendario antes de
 * renderizar el HTML. Recibe las variables $calYear, $calMonth y
 * $diasEventos desde DashboardController.
 */

// Array con los nombres abreviados de los meses en español
$monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

// Calcular el timestamp del primer día del mes para obtener sus propiedades
$firstDayTs  = strtotime(sprintf('%04d-%02d-01', $calYear, $calMonth));
$daysInMonth = (int)date('t', $firstDayTs);  // Número total de días del mes

// Día de la semana en que empieza el mes (0 = domingo, 6 = sábado)
$firstWeekday = (int)date('w', $firstDayTs);

// Timestamps del mes anterior y siguiente para la navegación del calendario
$prevTs = strtotime('-1 month', $firstDayTs);
$nextTs = strtotime('+1 month', $firstDayTs);

// Año y mes del mes anterior
$prevYear  = (int)date('Y', $prevTs);
$prevMonth = (int)date('n', $prevTs);

// Año y mes del mes siguiente
$nextYear  = (int)date('Y', $nextTs);
$nextMonth = (int)date('n', $nextTs);

// Fecha actual para marcar el día de hoy en el calendario
$todayY = (int)date('Y');
$todayM = (int)date('n');
$todayD = (int)date('j');

// Convertir el array de días con eventos en un mapa clave => true para búsquedas O(1)
$eventDays = array_flip($diasEventos ?? []);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle ?? 'Espacio de trabajo') ?></title>

    <!-- Bootstrap y hojas de estilo propias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">

            <div class="contenedor-dashboard">

                <!-- BARRA LATERAL IZQUIERDA: menú de navegación del dashboard -->
                <aside class="barra-herramientas">
                    <h3>BARRA DE HERRAMIENTAS</h3>

                    <!-- Menú de secciones del panel de trabajo -->
                    <ul class="menu-lateral">
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=dashboard">
                                <img src="<?= BASE_URL ?>/img/hogar.png" alt="icono" class="icono-menu">
                                Mi espacio de trabajo
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=buzon">
                                <img src="<?= BASE_URL ?>/img/bandeja-de-entrada.png" alt="icono" class="icono-menu">
                                Buzón de entrada
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=lecciones">
                                <img src="<?= BASE_URL ?>/img/leccion.png" alt="icono" class="icono-menu">
                                Lecciones
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/index.php?url=tareas">
                                <img src="<?= BASE_URL ?>/img/portapapeles.png" alt="icono" class="icono-menu">
                                Tareas
                            </a>
                        </li>
                    </ul>

                    <!-- Enlace de cierre de sesión al final de la barra lateral -->
                    <a class="cerrar-sesion" href="<?= BASE_URL ?>/index.php?url=logout">
                        <img src="<?= BASE_URL ?>/img/cerrar-sesion.png" alt="cerrar" class="icono-cerrar">
                        Cerrar sesión
                    </a>
                </aside>

                <!-- CONTENIDO CENTRAL del dashboard -->
                <section class="contenido-dashboard">

                    <!-- Banner promocional de la herramienta NotebookLMN -->
                    <div class="banner-dashboard">
                        <div class="banner-texto">
                            <span class="etiqueta-banner">NOTEBOOKLMN</span>
                            <h1>Todo tu aprendizaje, centralizado con NotebookLMN</h1>
                        </div>
                        <a class="btn-abrir" href="https://notebooklm.google.com/" target="_blank">Abrir ahora</a>
                    </div>

                    <!-- Botones de acciones rápidas sobre documentos -->
                    <div class="acciones">
                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/crear.png" alt="Nuevo Documento" class="icono-accion">
                            <span>Nuevo<br>Documento</span>
                        </button>

                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/subir.png" alt="Subir Documento" class="icono-accion">
                            <span>Subir<br>Documento</span>
                        </button>

                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/compartir-archivo.png" alt="Compartir Documento" class="icono-accion">
                            <span>Compartir<br>Documento</span>
                        </button>

                        <button class="accion-btn" type="button">
                            <img src="<?= BASE_URL ?>/img/plantilla.png" alt="Usar Plantilla" class="icono-accion">
                            <span>Usar<br>Plantilla</span>
                        </button>
                    </div>

                    <!-- Sección de carpetas del usuario -->
                    <div class="documentos">
                        <h2>Mis Carpetas</h2>

                        <?php
                        // Asegurar que $carpetas es un array aunque no venga del controlador
                        $carpetas = is_array($carpetas ?? null) ? $carpetas : [];
                        ?>

                        <?php if (count($carpetas) === 0): ?>
                            <!-- Mensaje cuando el usuario no tiene carpetas creadas todavía -->
                            <p class="text-muted">
                                Aún no tienes carpetas creadas.
                            </p>
                        <?php else: ?>
                            <!-- Listado de carpetas del usuario con enlace a cada una -->
                            <div class="documentos-container">
                                <?php foreach ($carpetas as $c): ?>
                                    <div class="documento">
                                        <a href="<?= BASE_URL ?>/index.php?url=carpeta&id=<?= $c['id'] ?>">
                                            <img src="<?= BASE_URL ?>/img/carpeta.png"
                                                class="imagen-carpeta"
                                                alt="Carpeta">
                                        </a>
                                        <p><?= htmlspecialchars($c['nombre']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Widget "Seguir viendo": muestra el último curso en el que el usuario estuvo activo -->
                    <div class="seguimiento">
                        <div class="seguimiento-cabecera">
                            <h2>Seguir viendo</h2>
                        </div>

                        <div class="seguimiento-container">
                            <?php if (!empty($seguirCurso)): ?>
                                <!-- Tarjeta del último curso con imagen, título y datos del alumno -->
                                <article class="curso">
                                    <img class="curso-imagen" src="<?= BASE_URL ?>/img/curso1.jpg" alt="Curso">

                                    <div class="curso-cuerpo">
                                        <h3><?= htmlspecialchars($seguirCurso['titulo']) ?></h3>

                                        <div class="curso-autor">
                                            <img class="avatar" src="<?= BASE_URL ?>/img/usuario.png" alt="perfil">
                                            <div>
                                                <!-- Nombre del usuario autenticado tomado de la sesión -->
                                                <p class="autor-nombre"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></p>
                                                <p class="autor-rol">Alumno</p>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php else: ?>
                                <!-- Mensaje cuando el usuario aún no se ha matriculado en ningún curso -->
                                <p class="text-muted">Aún no tienes cursos para continuar.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </section>

                <!-- COLUMNA DERECHA: calendario y lista de próximos eventos -->
                <aside class="calendario">
                    <button class="btn-calendario" type="button">Abrir Calendario</button>

                    <!-- Tarjeta del calendario mensual -->
                    <div class="tarjeta-calendario">
                        <div class="calendario-header">
                            <!-- Botón para ir al mes anterior -->
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $prevYear ?>&m=<?= $prevMonth ?>">&lt;</a>

                            <!-- Nombre del mes y año actuales -->
                            <div class="selector-mes">
                                <span class="mes"><?= $monthNames[$calMonth] ?></span>
                                <span class="anyo"><?= $calYear ?></span>
                            </div>

                            <!-- Botón para ir al mes siguiente -->
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $nextYear ?>&m=<?= $nextMonth ?>">&gt;</a>
                        </div>

                        <!-- Cabecera con los días de la semana -->
                        <div class="calendario-semana">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        </div>

                        <!-- Cuadrícula de días del mes -->
                        <div class="calendario-grid">
                            <?php
                            // Rellenar las celdas vacías antes del primer día del mes
                            for ($i = 0; $i < $firstWeekday; $i++) {
                                echo '<span class="dia apagado"></span>';
                            }

                            // Renderizar cada día del mes con las clases CSS correspondientes
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                // Comprobar si el día es hoy
                                $isToday  = ($calYear === $todayY && $calMonth === $todayM && $d === $todayD);
                                // Comprobar si el día tiene alguna tarea con fecha límite
                                $hasEvent = isset($eventDays[$d]);

                                $classes = 'dia';
                                if ($isToday)  $classes .= ' seleccionado'; // Resaltar el día actual
                                if ($hasEvent) $classes .= ' marcado';      // Marcar días con eventos

                                echo '<span class="' . $classes . '">' . $d . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Lista de próximos eventos (tareas con fecha límite) obtenidos desde BD -->
                    <div class="lista-eventos">
                        <?php if (!empty($eventos)): ?>
                            <?php foreach ($eventos as $e): ?>
                                <div class="evento">
                                    <span class="punto punto-azul"></span>
                                    <div class="evento-texto">
                                        <!-- Título de la tarea y nombre del curso al que pertenece -->
                                        <p class="evento-titulo"><?= htmlspecialchars($e['titulo']) ?></p>
                                        <p class="evento-sub">
                                            <?= htmlspecialchars($e['curso']) ?> · <?= htmlspecialchars(date('d/m/Y', strtotime($e['fecha_limite']))) ?>
                                        </p>
                                    </div>
                                    <span class="evento-flecha">&gt;</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Mensaje cuando no hay tareas con fecha límite próximas -->
                            <div class="evento">
                                <div class="evento-texto">
                                    <p class="evento-titulo">Sin eventos</p>
                                    <p class="evento-sub">No hay tareas con fecha límite</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
