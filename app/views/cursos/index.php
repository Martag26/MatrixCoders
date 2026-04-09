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
    require_once __DIR__ . '/../../helpers/curso_imagen.php';

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

    function imageFallback(?string $img, string $title = ''): string
    {
        return matrixcoders_curso_image($img, $title);
    }

    function nivelLabelHome(string $nivel): array
    {
        return match ($nivel) {
            'principiante' => ['Principiante', '#166534', '#dcfce7'],
            'estudiante'   => ['Estudiante', '#1d4ed8', '#dbeafe'],
            'profesional'  => ['Trabajador', '#7c3aed', '#ede9fe'],
            default        => ['', '', ''],
        };
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

            <div style="position: relative; max-width: 560px; margin: 0 auto;">
                <form class="hero-search" method="GET" action="<?= BASE_URL ?>/index.php"
                    style="margin-bottom: 0 !important;">
                    <input type="hidden" name="url" value="buscar">
                    <img class="icon" src="<?= BASE_URL ?>/img/lupa.png" alt="buscar">
                    <input
                        class="form-control w-100"
                        type="text"
                        name="q"
                        placeholder="Busca el curso que desees">
                </form>
                <ul id="sugerencias" style="
                    display: none;
                    background: #fff;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    list-style: none;
                    padding: 4px 0;
                    margin: 0;
                    width: 100%;
                    box-shadow: 0 8px 24px rgba(0,0,0,.08);
                    position: absolute !important;
                    top: calc(100% + 4px);
                    left: 0;
                    z-index: 9999;
                "></ul>
            </div>

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
                            $titulo = $curso['titulo'] ?? 'Programación avanzada en PHP y MySQL';
                            $desc   = $curso['descripcion'] ?? '';
                            $img = imageFallback($curso['imagen'] ?? null, $titulo);
                            $dur = formatDuracionFallback($curso['duracion_min'] ?? null);
                            $stu = (int)($curso['total_matriculas'] ?? 0);
                            $precio = isset($curso['precio']) ? (float)$curso['precio'] : 33.99;
                            $nivel  = $curso['nivel'] ?? '';
                            $cat    = $curso['categoria'] ?? '';
                            [$nivelTxt, $nivelColor, $nivelBg] = nivelLabelHome($nivel);
                            ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <!-- Card clickable -->
                                <div class="course-card h-100"
                                    style="cursor:pointer;"
                                    onclick="window.location.href='<?= BASE_URL ?>/index.php?url=curso&id=<?= $curso['id'] ?>'">

                                    <div class="course-thumb-wrap">
                                        <?php if ($img): ?>
                                            <img src="<?= htmlspecialchars($img) ?>"
                                                class="course-thumb w-100"
                                                alt="<?= htmlspecialchars($titulo) ?>"
                                                onerror="this.src='<?= matrixcoders_curso_image('', '') ?>'">
                                        <?php else: ?>
                                            <div class="course-thumb w-100"></div>
                                        <?php endif; ?>

                                        <?php if ($nivelTxt !== '' || $precio <= 0): ?>
                                            <div class="course-badges-corner">
                                                <?php if ($nivelTxt !== ''): ?>
                                                    <span class="course-badge-corner" style="color:<?= $nivelColor ?>;background:<?= $nivelBg ?>;border-color:<?= $nivelColor ?>22">
                                                        <?= htmlspecialchars($nivelTxt) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($precio <= 0): ?>
                                                    <span class="course-badge-corner course-badge-free">Gratis</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="p-3 d-flex flex-column gap-2">
                                        <?php if ($cat !== ''): ?>
                                            <div class="tags-row">
                                                <span class="tag tag-soft">
                                                    <?= htmlspecialchars($cat) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="course-meta">
                                            <span><?= $stu ?> <?= $stu === 1 ? 'estudiante' : 'estudiantes' ?></span>
                                        </div>
                                        <div class="course-title"><?= htmlspecialchars($titulo) ?></div>
                                        <p class="text-muted" style="font-size:0.85rem; margin:0;">
                                            <?= htmlspecialchars($desc) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                            <div class="course-price">
                                                <?= $precio > 0 ? number_format($precio, 2) . '€' : 'Gratis' ?>
                                            </div>
                                            <!-- Botón cesta — stopPropagation para que no active el onclick de la card -->
                                            <button
                                                class="btn-course-cart"
                                                title="Añadir al carrito"
                                                onclick="event.stopPropagation(); abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precio ?>)">
                                                <img src="<?= BASE_URL ?>/img/carrito-de-compras.png" alt="">
                                                <span>Añadir</span>
                                            </button>
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
                    src="https://maps.google.com/maps?width=100%25&amp;height=800&amp;hl=es&amp;q=Vtar%20Don%20Benito+(MatrixCoders%20&amp;%20Co.)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
                </iframe>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalCarrito" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Añadir al carrito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p id="modal-texto" class="mb-1" style="font-size:1rem;"></p>
                    <p id="modal-precio" class="fw-bold" style="font-size:1.2rem; color:#111827;"></p>
                    <p class="text-muted" style="font-size:.9rem;">¿Quieres añadir este curso a tu carrito?</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="btn-confirmar-carrito">
                        Añadir al carrito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const input = document.querySelector('input[name="q"]');
        const lista = document.getElementById('sugerencias');
        const baseUrl = '<?= BASE_URL ?>';
        let timer = null;

        input.addEventListener('input', function() {
            const q = this.value.trim();
            if (q === '') {
                ocultarLista();
                return;
            }
            clearTimeout(timer);
            timer = setTimeout(() => buscarSugerencias(q), 250);
        });

        function buscarSugerencias(q) {
            if (q.length < 1) {
                ocultarLista();
                return;
            }

            fetch(baseUrl + '/index.php?url=autocomplete&q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        ocultarLista();
                        return;
                    }

                    lista.innerHTML = '';
                    data.forEach(curso => {
                        const li = document.createElement('li');
                        li.textContent = curso.titulo;
                        li.style.cssText = 'padding: 10px 16px; cursor: pointer; font-size: .95rem; color: #111827;';
                        li.addEventListener('mouseenter', () => li.style.background = '#f3f4f6');
                        li.addEventListener('mouseleave', () => li.style.background = '#fff');
                        li.addEventListener('click', () => {
                            window.location.href = baseUrl + '/index.php?url=buscar&q=' + encodeURIComponent(curso.titulo);
                        });
                        lista.appendChild(li);
                    });
                    lista.style.display = 'block';
                })
                .catch(() => ocultarLista());
        }

        function ocultarLista() {
            lista.style.display = 'none';
            lista.innerHTML = '';
        }

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !lista.contains(e.target)) ocultarLista();
        });

        input.addEventListener('keydown', function(e) {
            const items = lista.querySelectorAll('li');
            const activo = lista.querySelector('li.activo');
            let idx = Array.from(items).indexOf(activo);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (activo) {
                    activo.classList.remove('activo');
                    activo.style.background = '#fff';
                }
                idx = (idx + 1) % items.length;
                items[idx].classList.add('activo');
                items[idx].style.background = '#f3f4f6';
                input.value = items[idx].textContent;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (activo) {
                    activo.classList.remove('activo');
                    activo.style.background = '#fff';
                }
                idx = (idx - 1 + items.length) % items.length;
                items[idx].classList.add('activo');
                items[idx].style.background = '#f3f4f6';
                input.value = items[idx].textContent;
            }
            if (e.key === 'Escape') ocultarLista();
        });

        let cursoSeleccionado = null;

        function abrirModal(id, titulo, precio) {
            cursoSeleccionado = id;
            document.getElementById('modal-texto').textContent = titulo;
            document.getElementById('modal-precio').textContent =
                precio > 0 ? precio.toFixed(2) + '€' : 'Gratis';
            new bootstrap.Modal(document.getElementById('modalCarrito')).show();
        }

        document.getElementById('btn-confirmar-carrito').addEventListener('click', function() {
            if (!cursoSeleccionado) return;

            const formData = new FormData();
            formData.append('curso_id', cursoSeleccionado);

            fetch('<?= BASE_URL ?>/index.php?url=carrito-añadir', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        // Actualiza el badge sin recargar la página
                        let badge = document.querySelector('.carrito-badge');
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'carrito-badge';
                            document.querySelector('a[aria-label="carrito"]').appendChild(badge);
                        }
                        badge.textContent = data.total;
                        bootstrap.Modal.getInstance(document.getElementById('modalCarrito')).hide();
                    }
                });
        });
    </script>

</body>

</html>
