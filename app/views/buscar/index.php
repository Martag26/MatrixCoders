<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = 'Buscar cursos';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/inicio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

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
                        value="<?= htmlspecialchars($q) ?>"
                        placeholder="Busca el curso que desees"
                        autofocus>
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

            <div class="mt-4">

                <?php if ($q === ''): ?>
                    <p class="text-muted text-center mt-5">Escribe algo para buscar cursos.</p>

                <?php elseif (empty($cursos)): ?>
                    <div style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <p class="section-title">Sin resultados para "<?= htmlspecialchars($q) ?>"</p>
                        <p class="text-muted">Prueba con otro término o revisa la ortografía.</p>
                    </div>
                <?php else: ?>
                    <h3 class="section-title">
                        <?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?>
                        para "<?= htmlspecialchars($q) ?>"
                    </h3>

                    <div class="row g-4 mt-1">
                        <?php foreach ($cursos as $curso): ?>
                            <?php
                            $img    = !empty($curso['imagen'])
                                ? BASE_URL . '/img/' . $curso['imagen']
                                : null;
                            $precio = isset($curso['precio']) ? (float)$curso['precio'] : 0;
                            $titulo = $curso['titulo'] ?? '';
                            $desc   = $curso['descripcion'] ?? '';
                            $stu    = (int)($curso['total_matriculas'] ?? 0);
                            ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <!-- Card clickable -->
                                <div class="course-card h-100"
                                    style="cursor:pointer;"
                                    onclick="window.location.href='<?= BASE_URL ?>/index.php?url=curso&id=<?= $curso['id'] ?>'">

                                    <?php if ($img): ?>
                                        <img src="<?= htmlspecialchars($img) ?>"
                                            class="course-thumb w-100"
                                            alt="<?= htmlspecialchars($titulo) ?>">
                                    <?php else: ?>
                                        <div class="course-thumb w-100"></div>
                                    <?php endif; ?>

                                    <div class="p-3 d-flex flex-column gap-2">
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
                                                class="btn btn-outline-secondary btn-sm"
                                                title="Añadir al carrito"
                                                onclick="event.stopPropagation(); abrirModal(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($titulo)) ?>', <?= $precio ?>)">
                                                🛒
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPaginas > 1): ?>
                        <nav class="mt-5 d-flex justify-content-center">
                            <ul class="pagination">
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link"
                                        href="<?= BASE_URL ?>/index.php?url=buscar&q=<?= urlencode($q) ?>&p=<?= $pagina - 1 ?>">
                                        &laquo;
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="<?= BASE_URL ?>/index.php?url=buscar&q=<?= urlencode($q) ?>&p=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link"
                                        href="<?= BASE_URL ?>/index.php?url=buscar&q=<?= urlencode($q) ?>&p=<?= $pagina + 1 ?>">
                                        &raquo;
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Modal carrito -->
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

        // Si se envía el formulario con el campo vacío, vuelve al inicio
        document.querySelector('form.hero-search').addEventListener('submit', function(e) {
            if (input.value.trim() === '') {
                e.preventDefault();
                window.location.href = baseUrl + '/index.php';
            }
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
                            input.value = curso.titulo;
                            ocultarLista();
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