<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatrixCoders - Cursos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/matrixcoders/public/css/inicio.css">
    <link rel="stylesheet" href="/matrixcoders/public/css/header.css">
    <link rel="stylesheet" href="/matrixcoders/public/css/footer.css">
</head>

<body>

    <?php
    $pageTitle = 'Inicio';
    $pageCss   = '/css/inicio.css';

    require __DIR__ . '/../layout/header.php';

    function formatDuracionFallback(?int $min): string
    {
        if (!$min || $min <= 0) return '01h 49m';
        $h = intdiv($min, 60);
        $m = $min % 60;
        return sprintf("%02dh %02dm", $h, $m);
    }

    function studentsFallback($val): int
    {
        $n = (int)($val ?? 0);
        return $n > 0 ? $n : 157;
    }

    function imageFallback(?string $img): string
    {
        return $img ? '/img/' . $img : '/img/curso1.jpg';
    }

    $cursos = $cursos ?? [];
    $tituloBloque = $tituloBloque ?? 'Cursos Destacados';

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    ?>

    <!-- HERO / PRINCIPAL -->
    <section class="seccionPrincipal">
        <div class="mc-container py-4">

            <h1 class="hero-title text-center">
                Aprende, <span>crece</span> y avanza
            </h1>

            <!-- Buscador -->
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

            <!-- BLOQUE CURSOS -->
            <div class="mt-4">
                <h3 class="section-title"><?= htmlspecialchars($tituloBloque) ?></h3>

                <?php if (empty($cursos)): ?>
                    <div class="alert alert-warning mt-3">
                        No se encontraron cursos.
                    </div>
                <?php else: ?>
                    <div class="row g-4 mt-1">
                        <?php foreach ($cursos as $curso): ?>
                            <?php
                            $img = imageFallback($curso['imagen'] ?? null);
                            $dur = formatDuracionFallback($curso['duracion_min'] ?? null);
                            $stu = studentsFallback($curso['estudiantes'] ?? null);
                            $precio = isset($curso['precio']) ? (float)$curso['precio'] : 33.99;
                            $titulo = $curso['titulo'] ?? 'Programación avanzada en PHP y MySQL';
                            ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="course-card h-100">
                                    <img src="<?= htmlspecialchars($img) ?>" class="course-thumb w-100" alt="Curso">

                                    <div class="p-3 d-flex flex-column gap-2">
                                        <div class="course-meta">
                                            <span><?= $stu ?> Students</span>
                                            <span><?= htmlspecialchars($dur) ?></span>
                                        </div>

                                        <div class="course-title">
                                            <?= htmlspecialchars($titulo) ?>
                                        </div>

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

    <!-- BENEFICIOS -->
    <section class="seccionBeneficios">
        <div class="mc-container text-center">
            <h2 class="benefits-title">
                Por qué <span>aprender</span> con nuestros cursos?
            </h2>

            <p class="benefits-desc">
                Habilidades prácticas, proyectos reales y certificación,<br>
                a tu ritmo.
            </p>

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

    <!-- UBICACIÓN -->
    <section class="seccionUbicacion">
        <div class="mc-container text-center">
            <h2 class="ubicacion-title">Nuestra Ubicación</h2>

            <p class="ubicacion-desc">
                Aquí puedes ver nuestra ubicación en el mapa. ¡Te esperamos en nuestro centro de formación!
            </p>

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

    <?php

    require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>