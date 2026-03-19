<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders - Cursos</title>

    <!-- Bootstrap y hojas de estilo propias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/matrixcoders/public/css/inicio.css">
    <link rel="stylesheet" href="/matrixcoders/public/css/header.css">
    <link rel="stylesheet" href="/matrixcoders/public/css/footer.css">
</head>

<body>

    <?php
    // Variables de página que el header puede necesitar
    $pageTitle = 'Inicio';
    $pageCss   = '/css/inicio.css';

    require __DIR__ . '/../layout/header.php';

    /**
     * Formatea la duración de un curso en horas y minutos.
     * Si el valor es nulo o inválido, devuelve un valor de ejemplo por defecto.
     *
     * @param int|null $min Duración en minutos.
     * @return string Duración formateada como "HHh MMm".
     */
    function formatDuracionFallback(?int $min): string
    {
        if (!$min || $min <= 0) return '01h 49m';
        $h = intdiv($min, 60);
        $m = $min % 60;
        return sprintf("%02dh %02dm", $h, $m);
    }

    /**
     * Devuelve el número de estudiantes del curso.
     * Si el valor es nulo o cero, devuelve un número de ejemplo por defecto.
     *
     * @param mixed $val Número de estudiantes almacenado en BD.
     * @return int Número de estudiantes (real o de ejemplo).
     */
    function studentsFallback($val): int
    {
        $n = (int)($val ?? 0);
        return $n > 0 ? $n : 157;
    }

    /**
     * Devuelve la ruta de la imagen del curso.
     * Si no hay imagen guardada en BD, devuelve una imagen de ejemplo por defecto.
     *
     * @param string|null $img Nombre del archivo de imagen almacenado en BD.
     * @return string Ruta relativa a la imagen del curso.
     */
    function imageFallback(?string $img): string
    {
        return $img ? '/img/' . $img : '/img/curso1.jpg';
    }

    // Si $cursos no está definido (llamada directa a la vista), inicializarlo como array vacío
    $cursos = $cursos ?? [];

    // Título del bloque de cursos: se puede personalizar desde el controlador
    $tituloBloque = $tituloBloque ?? 'Cursos Destacados';

    // Recoger el término de búsqueda introducido por el usuario (si existe)
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    ?>

    <!-- SECCIÓN HERO / PRINCIPAL: buscador y listado de cursos -->
    <section class="seccionPrincipal">
        <div class="mc-container py-4">

            <h1 class="hero-title text-center">
                Aprende, <span>crece</span> y avanza
            </h1>

            <!-- Formulario de búsqueda de cursos por texto -->
            <form class="hero-search" method="GET" action="/index.php">
                <input type="hidden" name="r" value="home/index">
                <img class="icon" src="/matrixcoders/public/img/lupa.png" alt="buscar">
                <input
                    class="form-control"
                    type="text"
                    name="q"
                    value="<?= htmlspecialchars($q) ?>"
                    placeholder="Busca el curso que desees">
            </form>

            <!-- BLOQUE DE CURSOS: listado dinámico cargado desde la base de datos -->
            <div class="mt-4">
                <h3 class="section-title"><?= htmlspecialchars($tituloBloque) ?></h3>

                <?php if (empty($cursos)): ?>
                    <!-- Mensaje mostrado cuando no hay cursos disponibles o no hay resultados de búsqueda -->
                    <div class="alert alert-warning mt-3">
                        No se encontraron cursos.
                    </div>
                <?php else: ?>
                    <div class="row g-4 mt-1">
                        <?php foreach ($cursos as $curso): ?>
                            <?php
                            // Obtener los datos del curso con valores de respaldo si faltan campos en BD
                            $img    = imageFallback($curso['imagen'] ?? null);
                            $dur    = formatDuracionFallback($curso['duracion_min'] ?? null);
                            $stu    = studentsFallback($curso['estudiantes'] ?? null);
                            $precio = isset($curso['precio']) ? (float)$curso['precio'] : 33.99;
                            $titulo = $curso['titulo'] ?? 'Programación avanzada en PHP y MySQL';
                            ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="course-card h-100">
                                    <!-- Imagen de portada del curso -->
                                    <img src="<?= htmlspecialchars($img) ?>" class="course-thumb w-100" alt="Curso">

                                    <div class="p-3 d-flex flex-column gap-2">
                                        <!-- Metadatos: número de estudiantes y duración -->
                                        <div class="course-meta">
                                            <span><?= $stu ?> Students</span>
                                            <span><?= htmlspecialchars($dur) ?></span>
                                        </div>

                                        <!-- Título del curso -->
                                        <div class="course-title">
                                            <?= htmlspecialchars($titulo) ?>
                                        </div>

                                        <!-- Precio y botón para añadir al carrito -->
                                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                            <div class="course-price"><?= number_format($precio, 2) ?>€</div>
                                            <a class="btn btn-outline-secondary btn-sm" href="#" title="Añadir al carrito" aria-label="Añadir al carrito">
                                                🛒
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- SECCIÓN BENEFICIOS: razones para aprender en la plataforma -->
    <section class="seccionBeneficios">
        <div class="mc-container text-center">
            <h2 class="benefits-title">
                Por qué <span>aprender</span> con nuestros cursos?
            </h2>

            <p class="benefits-desc">
                Habilidades prácticas, proyectos reales y certificación,<br>
                a tu ritmo.
            </p>

            <!-- Tres pasos: Aprende, Gradúate, Trabaja -->
            <div class="row justify-content-center mt-4 g-4">
                <div class="col-12 col-md-4">
                    <div class="benefit-item">
                        <img src="/matrixcoders/public/img/aprendiendo.png" alt="Aprende">
                        <h5>01. Aprende</h5>
                        <p>Domina habilidades prácticas con proyectos reales y apoyo experto.</p>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="benefit-item">
                        <img src="/matrixcoders/public/img/gorro-de-graduacion.png" alt="Gradúate">
                        <h5>02. Gradúate</h5>
                        <p>Consigue certificación y un portafolio listo para mostrar.</p>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="benefit-item">
                        <img src="/matrixcoders/public/img/trabajapng.png" alt="Trabaja">
                        <h5>03. Trabaja</h5>
                        <p>Conecta con empresas, mejora tu CV y accede a ofertas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN UBICACIÓN: mapa de Google Maps con la localización del centro -->
    <section class="seccionUbicacion">
        <div class="mc-container text-center">
            <h2 class="ubicacion-title">Nuestra Ubicación</h2>

            <p class="ubicacion-desc">
                Aquí puedes ver nuestra ubicación en el mapa. ¡Te esperamos en nuestro centro de formación!
            </p>

            <!-- Mapa embebido de Google Maps -->
            <div class="mapaContainer">
                <iframe
                    width="100%"
                    frameborder="0"
                    scrolling="no"
                    marginheight="0"
                    marginwidth="0"
                    src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=es&amp;q=Vtar%20Don%20Benito+(MatrixCoders%20&amp;%20Co.)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
                </iframe>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
