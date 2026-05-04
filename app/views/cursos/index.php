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
    require_once __DIR__ . '/../../helpers/curso_imagen.php';

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

    function imageFallback(?string $img, string $title = ''): string
    {
        return matrixcoders_curso_image($img, $title);
    }

    function nivelLabelHome(string $nivel): array
    {
        return match ($nivel) {
            'principiante' => ['Fundamentos', '#166534', '#dcfce7'],
            'estudiante'   => ['Ruta academica', '#1d4ed8', '#dbeafe'],
            'profesional'  => ['Perfil profesional', '#9a3412', '#ffedd5'],
            default        => ['', '', ''],
        };
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

            <!-- BLOQUE DE CURSOS: listado dinámico cargado desde la base de datos -->
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
                            $titulo  = $curso['titulo'] ?? '';
                            $desc    = $curso['descripcion'] ?? '';
                            $img     = imageFallback($curso['imagen'] ?? null, $titulo);
                            $imgFallback = imageFallback('', '');
                            $stu     = (int)($curso['total_matriculas'] ?? 0);
                            $precio  = isset($curso['precio']) ? (float)$curso['precio'] : 0;
                            $descuento = (float)($curso['descuento_activo'] ?? 0);
                            $precioFinal = ($descuento > 0 && $precio > 0) ? round($precio * (1 - $descuento/100), 2) : $precio;
                            $nivel   = $curso['nivel'] ?? '';
                            $cat     = $curso['categoria'] ?? '';
                            [$nivelTxt, $nivelColor, $nivelBg] = nivelLabelHome($nivel);
                            ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="course-card h-100"
                                    onclick="window.location.href='<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>'">

                                    <div class="course-thumb-wrap">
                                        <img src="<?= htmlspecialchars($img) ?>"
                                            class="course-thumb w-100"
                                            alt="<?= htmlspecialchars($titulo) ?>"
                                            onerror="this.src='<?= htmlspecialchars($imgFallback) ?>'">

                                        <?php if ($descuento > 0): ?>
                                            <div class="course-badges-corner">
                                                <span class="course-badge-corner course-badge-discount">-<?= round($descuento) ?>%</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-body-inner-home">
                                        <?php if ($cat !== '' || $nivelTxt !== ''): ?>
                                            <div class="tags-row">
                                                <?php if ($nivelTxt !== ''): ?>
                                                    <span class="nivel-badge-home" style="color:<?= $nivelColor ?>;background:<?= $nivelBg ?>;border-color:<?= $nivelColor ?>33">
                                                        <?= htmlspecialchars($nivelTxt) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($cat !== ''): ?>
                                                    <span class="tag tag-soft"><?= htmlspecialchars($cat) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="course-title"><?= htmlspecialchars($titulo) ?></div>

                                        <?php if ($desc): ?>
                                            <p class="course-desc-home"><?= htmlspecialchars($desc) ?></p>
                                        <?php endif; ?>

                                        <div class="course-meta" style="font-size:.75rem">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:3px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                            <?= $stu ?> <?= $stu === 1 ? 'estudiante' : 'estudiantes' ?>
                                        </div>

                                        <div class="card-footer-row-home">
                                            <div>
                                                <?php if ($precio <= 0): ?>
                                                    <span class="course-price" style="color:#16a34a">Gratis</span>
                                                    <div style="font-size:.7rem;font-weight:700;color:#047857">Acceso sin coste</div>
                                                <?php elseif ($descuento > 0): ?>
                                                    <del style="font-size:.8rem;color:#9ca3af;margin-right:4px"><?= number_format($precio, 2) ?>€</del>
                                                    <span class="course-price"><?= number_format($precioFinal, 2) ?>€</span>
                                                <?php else: ?>
                                                    <span class="course-price"><?= number_format($precio, 2) ?>€</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!in_array($curso['id'], $matriculasUsuario ?? [])): ?>
                                            <button class="btn-course-cart"
                                                onclick="event.stopPropagation(); abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precioFinal ?>)">
                                                <img src="<?= BASE_URL ?>/img/carrito-de-compras.png" alt="">
                                                <span>Añadir</span>
                                            </button>
                                            <?php else: ?>
                                            <span class="btn-course-enrolled">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                Matriculado
                                            </span>
                                            <?php endif; ?>
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
                        let badge = document.querySelector('.carrito-badge');
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'carrito-badge';
                            document.querySelector('a[aria-label="carrito"]').appendChild(badge);
                        }
                        badge.textContent = data.total;
                        bootstrap.Modal.getInstance(document.getElementById('modalCarrito')).hide();
                        return;
                    }

                    document.getElementById('modal-texto').textContent = 'No se ha añadido ningun curso';
                    document.getElementById('modal-precio').textContent = data.mensaje || 'Este curso ya esta en tu cesta.';
                });
        });
    </script>

</body>

</html>
