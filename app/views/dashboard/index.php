<?php
require_once __DIR__ . '/../../helpers/curso_imagen.php';
$monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

$firstDayTs   = strtotime(sprintf('%04d-%02d-01', $calYear, $calMonth));
$daysInMonth  = (int)date('t', $firstDayTs);
$firstWeekday = (int)date('w', $firstDayTs);

$prevTs    = strtotime('-1 month', $firstDayTs);
$nextTs    = strtotime('+1 month', $firstDayTs);
$prevYear  = (int)date('Y', $prevTs);
$prevMonth = (int)date('n', $prevTs);
$nextYear  = (int)date('Y', $nextTs);
$nextMonth = (int)date('n', $nextTs);

$todayY = (int)date('Y');
$todayM = (int)date('n');
$todayD = (int)date('j');
$carpetas = is_array($carpetas ?? null) ? $carpetas : [];
$documentosRecientes = is_array($documentosRecientes ?? null) ? $documentosRecientes : [];
$documentos = is_array($documentos ?? null) ? $documentos : [];
$mostrarTodosDocumentos = (bool)($mostrarTodosDocumentos ?? false);
$flash = $flash ?? null;
$diasConTareas = is_array($diasConTareas ?? null) ? $diasConTareas : [];
$tareasUsuario = is_array($tareasUsuario ?? null) ? $tareasUsuario : [];
$documentosCompartibles = is_array($documentosCompartibles ?? null) ? $documentosCompartibles : [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle ?? 'Espacio de trabajo') ?></title>
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

                <!-- ── SIDEBAR ── -->
                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <!-- ── CONTENIDO CENTRAL ── -->
                <section class="contenido-dashboard">
                    <?php if (!empty($flash['message'])): ?>
                        <div class="dashboard-flash dashboard-flash-<?= htmlspecialchars($flash['type']) ?>">
                            <?= htmlspecialchars($flash['message']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="banner-dashboard">
                        <div class="banner-texto">
                            <span class="etiqueta-banner">NOTEBOOKLMN</span>
                            <h1>Todo tu aprendizaje, centralizado con NotebookLMN</h1>
                        </div>
                        <a class="btn-abrir" href="<?= BASE_URL ?>/index.php?url=app">Abrir ahora</a>
                    </div>

                    <div class="acciones">
                        <a class="accion-btn" href="<?= BASE_URL ?>/index.php?url=nuevo-documento">
                            <img src="<?= BASE_URL ?>/img/crear.png" alt="" class="icono-accion">
                            <span>Nuevo<br>Documento</span>
                        </a>
                        <button class="accion-btn" type="button" data-bs-toggle="modal" data-bs-target="#modalSubirDocumento">
                            <img src="<?= BASE_URL ?>/img/subir.png" alt="" class="icono-accion">
                            <span>Subir<br>Documento</span>
                        </button>
                        <button class="accion-btn" type="button" data-bs-toggle="modal" data-bs-target="#modalCompartirDocumento">
                            <img src="<?= BASE_URL ?>/img/compartir-archivo.png" alt="" class="icono-accion">
                            <span>Compartir<br>Documento</span>
                        </button>
                        <a class="accion-btn" href="<?= BASE_URL ?>/index.php?url=plantillas-documento">
                            <img src="<?= BASE_URL ?>/img/plantilla.png" alt="" class="icono-accion">
                            <span>Usar<br>Plantilla</span>
                        </a>
                    </div>

                    <div class="documentos">
                        <div class="dashboard-section-head">
                            <h2>Mis documentos</h2>
                            <a class="section-link" href="<?= BASE_URL ?>/index.php?url=mis-documentos">Ver todo</a>
                        </div>
                        <?php if (count($documentosRecientes) === 0): ?>
                            <p class="text-muted">Todavía no tienes documentos creados.</p>
                        <?php else: ?>
                            <div class="documentos-recientes-grid compact-doc-strip">
                                <?php foreach ($documentosRecientes as $doc): ?>
                                    <a class="documento-mini-item documento-mini-link" href="<?= BASE_URL ?>/index.php?url=documento&id=<?= (int)$doc['id'] ?>">
                                        <div class="documento-mini-icono">
                                            <img src="<?= BASE_URL ?>/img/portapapeles.png" alt="Documento">
                                        </div>
                                        <div class="documento-mini-body">
                                            <h3><?= htmlspecialchars($doc['titulo']) ?></h3>
                                            <?php if (!empty($doc['carpeta_nombre'])): ?>
                                                <p><?= htmlspecialchars($doc['carpeta_nombre']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── SEGUIR VIENDO ── -->
                    <div class="seguimiento">
                        <div class="seguimiento-cabecera">
                            <h2>Seguir viendo</h2>
                            <?php if (count($cursosEnProgreso ?? []) > 3): ?>
                                <div class="sv-flechas">
                                    <button class="sv-arrow" id="svPrev">&#8592;</button>
                                    <button class="sv-arrow" id="svNext">&#8594;</button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($cursosEnProgreso)): ?>
                            <p class="text-muted" style="margin-top:.5rem;">
                                Aún no tienes cursos.
                                <a href="<?= BASE_URL ?>/index.php" style="color:var(--mc-green);font-weight:700;">Explorar cursos →</a>
                            </p>
                        <?php else: ?>
                            <div class="sv-track-wrap">
                                <div class="sv-track" id="svTrack">
                                    <?php foreach ($cursosEnProgreso as $sc):
                                        $progreso   = $sc['progreso'];
                                        $leccionUrl = $sc['ultima_leccion_id']
                                            ? BASE_URL . '/index.php?url=leccion&id=' . $sc['ultima_leccion_id']
                                            : BASE_URL . '/index.php?url=detallecurso&id=' . $sc['id'];
                                        $imgSrc = matrixcoders_curso_image($sc['imagen'] ?? '', $sc['titulo'] ?? '');
                                    ?>
                                        <a class="sv-card" href="<?= $leccionUrl ?>">
                                            <div class="sv-thumb">
                                                <img src="<?= htmlspecialchars($imgSrc) ?>"
                                                    alt="<?= htmlspecialchars($sc['titulo']) ?>"
                                                    onerror="this.src='<?= BASE_URL ?>/img/aprendiendo.png'">
                                                <span class="sv-badge"><?= $progreso ?>%</span>
                                            </div>
                                            <div class="sv-body">
                                                <p class="sv-titulo"><?= htmlspecialchars($sc['titulo']) ?></p>
                                                <div class="sv-progress-wrap">
                                                    <div class="sv-progress-bar">
                                                        <div class="sv-progress-fill" style="width:<?= $progreso ?>%"></div>
                                                    </div>
                                                    <span class="sv-progress-label">
                                                        <?= $sc['lecciones_vistas'] ?>/<?= $sc['total_lecciones'] ?> lecciones
                                                    </span>
                                                </div>
                                                <span class="sv-continuar">Continuar →</span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </section>

                <!-- ── MINI CALENDARIO (solo visual, sin eventos) ── -->
                <aside class="calendario">
                    <a class="btn-calendario" href="<?= BASE_URL ?>/index.php?url=calendario">
                        Abrir Calendario
                    </a>

                    <div class="tarjeta-calendario">
                        <div class="calendario-header">
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $prevYear ?>&m=<?= $prevMonth ?>">&lt;</a>
                            <div class="selector-mes">
                                <span class="mes"><?= $monthNames[$calMonth] ?></span>
                                <span class="anyo"><?= $calYear ?></span>
                            </div>
                            <a class="btn-mini" href="<?= BASE_URL ?>/index.php?url=dashboard&y=<?= $nextYear ?>&m=<?= $nextMonth ?>">&gt;</a>
                        </div>

                        <div class="calendario-semana">
                            <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span>
                            <span>Vi</span><span>Sa</span><span>Do</span>
                        </div>

                        <div class="calendario-grid">
                            <?php
                            $offset = $firstWeekday === 0 ? 6 : $firstWeekday - 1;
                            for ($i = 0; $i < $offset; $i++) {
                                echo '<span class="dia apagado"></span>';
                            }
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $isToday = ($calYear === $todayY && $calMonth === $todayM && $d === $todayD);
                                $hasTask = in_array($d, $diasConTareas, true);
                                $classes = 'dia' . ($isToday ? ' seleccionado' : '') . ($hasTask ? ' marcado' : '');
                                echo '<span class="' . $classes . '">' . $d . '</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <?php if (!empty($tareasUsuario)): ?>
                        <div class="lista-eventos">
                            <?php foreach (array_slice($tareasUsuario, 0, 4) as $tarea): ?>
                                <div class="evento">
                                    <span class="punto" style="background:var(--mc-green);width:8px;height:8px;border-radius:50%;flex-shrink:0;"></span>
                                    <div class="evento-texto">
                                        <p class="evento-titulo"><?= htmlspecialchars($tarea['titulo']) ?></p>
                                        <p class="evento-sub">
                                            <?= htmlspecialchars($tarea['curso'] ?? 'Curso') ?>
                                            <?php if (!empty($tarea['fecha_limite'])): ?>
                                                · <?= htmlspecialchars(date('d/m/Y', strtotime($tarea['fecha_limite']))) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="evento-badge">Tarea</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="lista-eventos">
                            <div class="evento evento-empty">
                                <div class="evento-texto">
                                    <p class="evento-titulo">No hay tareas pendientes</p>
                                    <p class="evento-sub">Cuando tengas entregas o ejercicios con fecha, aparecerán aquí.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <div class="modal fade" id="modalSubirDocumento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content dashboard-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Subir documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form method="post" class="dashboard-form" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="dashboard_action" value="upload_document">
                        <div class="form-grid">
                            <label>
                                <span>Nombre del documento</span>
                                <input type="text" name="doc_title" placeholder="Ej. Resumen del tema 4">
                            </label>
                            <label>
                                <span>Guardar en</span>
                                <select name="folder_id">
                                    <option value="">Guardar fuera de carpeta</option>
                                    <?php foreach ($carpetas as $carpeta): ?>
                                        <option value="<?= (int)$carpeta['id'] ?>"><?= htmlspecialchars($carpeta['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <label>
                            <span>Archivo del ordenador</span>
                            <input type="file" name="document_file" class="file-input" accept=".txt,.md,.pdf,.doc,.docx,.csv,.json,.xml,.php,.js,.html,.css">
                        </label>
                        <p class="upload-help">Si subes un archivo de texto, intentaremos guardar también su contenido dentro del documento.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn-panel-submit" type="submit">Guardar documento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCompartirDocumento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content dashboard-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Compartir documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="share-stepper">
                        <div class="share-step-item activo" id="shareStepIndicator1">1. Seleccionar archivos</div>
                        <div class="share-step-item" id="shareStepIndicator2">2. Elegir plataforma</div>
                    </div>

                    <div class="share-step-panel activo" id="shareStep1">
                        <div class="share-search-wrap">
                            <input type="search" id="shareSearchInput" placeholder="Buscar entre tus documentos">
                        </div>
                        <div class="share-results compact" id="shareResults">
                            <?php if (empty($documentosCompartibles)): ?>
                                <div class="share-empty-state">
                                    Todavía no tienes documentos para compartir.
                                </div>
                            <?php else: ?>
                                <?php foreach ($documentosCompartibles as $doc): ?>
                                    <label
                                        class="share-doc-item share-doc-item-compact"
                                        data-doc-title="<?= htmlspecialchars($doc['titulo']) ?>"
                                        data-doc-folder="<?= htmlspecialchars($doc['carpeta_nombre'] ?? '') ?>"
                                        data-doc-url="<?= htmlspecialchars($doc['share_url']) ?>">
                                        <input class="share-doc-checkbox" type="checkbox" value="<?= htmlspecialchars($doc['share_url']) ?>">
                                        <span class="share-doc-meta">
                                            <strong><?= htmlspecialchars($doc['titulo']) ?></strong>
                                            <?php if (!empty($doc['carpeta_nombre'])): ?>
                                                <span><?= htmlspecialchars($doc['carpeta_nombre']) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="share-step-panel" id="shareStep2">
                        <div class="share-preview-card compact">
                            <p class="share-preview-kicker">Documento seleccionado</p>
                            <h6 id="shareSelectedTitle">Selecciona uno o varios documentos</h6>
                            <p id="shareSelectedFolder">Primero elige archivos en el paso anterior.</p>
                            <div class="share-platform-grid">
                                <a href="#" class="share-platform-btn is-disabled" id="shareByEmail" target="_blank" rel="noopener">Email</a>
                                <a href="#" class="share-platform-btn is-disabled" id="shareByWhatsapp" target="_blank" rel="noopener">WhatsApp</a>
                                <button type="button" class="share-platform-btn is-disabled" id="shareByDrive">Drive</button>
                                <a href="#" class="share-platform-btn is-disabled" id="shareByTelegram" target="_blank" rel="noopener">Telegram</a>
                                <button type="button" class="share-platform-btn is-disabled" id="shareCopyLink">Copiar enlace</button>
                                <button type="button" class="share-platform-btn is-disabled" id="shareOtherApps">Otros</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary" id="shareBackStep">Volver</button>
                    <button type="button" class="btn-panel-submit" id="shareNextStep">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const shareSearchInput = document.getElementById('shareSearchInput');
            const shareDocItems = Array.from(document.querySelectorAll('.share-doc-item'));
            const shareDocCheckboxes = Array.from(document.querySelectorAll('.share-doc-checkbox'));
            const shareSelectedTitle = document.getElementById('shareSelectedTitle');
            const shareSelectedFolder = document.getElementById('shareSelectedFolder');
            const shareByEmail = document.getElementById('shareByEmail');
            const shareByWhatsapp = document.getElementById('shareByWhatsapp');
            const shareByDrive = document.getElementById('shareByDrive');
            const shareByTelegram = document.getElementById('shareByTelegram');
            const shareCopyLink = document.getElementById('shareCopyLink');
            const shareOtherApps = document.getElementById('shareOtherApps');
            const shareStep1 = document.getElementById('shareStep1');
            const shareStep2 = document.getElementById('shareStep2');
            const shareNextStep = document.getElementById('shareNextStep');
            const shareBackStep = document.getElementById('shareBackStep');
            const shareStepIndicator1 = document.getElementById('shareStepIndicator1');
            const shareStepIndicator2 = document.getElementById('shareStepIndicator2');
            const shareModal = document.getElementById('modalCompartirDocumento');
            let currentShare = null;
            let currentStep = 1;

            function toggleShareButtons(enabled) {
                [shareByEmail, shareByWhatsapp, shareByDrive, shareByTelegram, shareCopyLink, shareOtherApps]
                    .forEach((button) => button.classList.toggle('is-disabled', !enabled));
            }

            function getSelectedDocs() {
                return shareDocItems
                    .filter((item) => item.querySelector('.share-doc-checkbox')?.checked)
                    .map((item) => ({
                        title: item.dataset.docTitle,
                        folder: item.dataset.docFolder || '',
                        url: item.dataset.docUrl,
                    }));
            }

            function setStep(step) {
                currentStep = step;
                shareStep1?.classList.toggle('activo', step === 1);
                shareStep2?.classList.toggle('activo', step === 2);
                shareStepIndicator1?.classList.toggle('activo', step === 1);
                shareStepIndicator2?.classList.toggle('activo', step === 2);
                if (shareBackStep) shareBackStep.style.visibility = step === 1 ? 'hidden' : 'visible';
                if (shareNextStep) shareNextStep.textContent = step === 1 ? 'Continuar' : 'Compartir';
            }

            function applyShareLinks(data) {
                currentShare = data;
                shareSelectedTitle.textContent = data.title;
                shareSelectedFolder.textContent = data.folder || 'Documento listo para compartir';

                const encodedText = encodeURIComponent(`${data.title} ${data.url}`);
                const encodedBody = encodeURIComponent(`Te comparto este documento: ${data.title}\n${data.url}`);

                shareByEmail.href = `mailto:?subject=${encodeURIComponent(data.title)}&body=${encodedBody}`;
                shareByWhatsapp.href = `https://wa.me/?text=${encodedText}`;
                shareByTelegram.href = `https://t.me/share/url?url=${encodeURIComponent(data.url)}&text=${encodeURIComponent(data.title)}`;
                toggleShareButtons(true);
            }

            function updateSelectionSummary() {
                const selectedDocs = getSelectedDocs();
                shareDocItems.forEach((item) => {
                    const checked = item.querySelector('.share-doc-checkbox')?.checked;
                    item.classList.toggle('activo', !!checked);
                });

                if (!selectedDocs.length) {
                    currentShare = null;
                    toggleShareButtons(false);
                    shareSelectedTitle.textContent = 'Selecciona uno o varios documentos';
                    shareSelectedFolder.textContent = 'Primero elige archivos en el paso anterior.';
                    return;
                }

                const title = selectedDocs.length === 1
                    ? selectedDocs[0].title
                    : `${selectedDocs.length} documentos seleccionados`;
                const folder = selectedDocs.length === 1
                    ? selectedDocs[0].folder
                    : selectedDocs.map((doc) => doc.folder).filter(Boolean).join(' · ');
                const urls = selectedDocs.map((doc) => doc.url);
                const summaryUrl = urls.join('\n');
                const composedTitle = selectedDocs.map((doc) => doc.title).join(', ');
                applyShareLinks({
                    title,
                    folder,
                    url: summaryUrl,
                    rawUrls: urls,
                    composedTitle,
                });
            }

            shareDocCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', updateSelectionSummary);
            });

            if (shareSearchInput) {
                shareSearchInput.addEventListener('input', () => {
                    const term = shareSearchInput.value.toLowerCase().trim();
                    shareDocItems.forEach((item) => {
                        const text = `${item.dataset.docTitle} ${item.dataset.docFolder}`.toLowerCase();
                        item.style.display = term === '' || text.includes(term) ? '' : 'none';
                    });
                });
            }

            async function copyText(value) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(value);
                    return true;
                }

                const helper = document.createElement('textarea');
                helper.value = value;
                document.body.appendChild(helper);
                helper.select();
                const copied = document.execCommand('copy');
                document.body.removeChild(helper);
                return copied;
            }

            shareNextStep?.addEventListener('click', async () => {
                if (currentStep === 1) {
                    const selectedDocs = getSelectedDocs();
                    if (!selectedDocs.length) {
                        shareSelectedFolder.textContent = 'Selecciona al menos un documento para continuar.';
                        return;
                    }
                    updateSelectionSummary();
                    setStep(2);
                    return;
                }

                if (currentShare?.url) {
                    await copyText(currentShare.url);
                    shareSelectedFolder.textContent = 'El enlace ya está listo. Elige una plataforma o pégalo donde quieras.';
                }
            });

            shareBackStep?.addEventListener('click', () => setStep(1));

            shareCopyLink?.addEventListener('click', async () => {
                if (!currentShare) return;
                await copyText(currentShare.url);
                shareSelectedFolder.textContent = 'Enlace copiado al portapapeles.';
            });

            shareByDrive?.addEventListener('click', async () => {
                if (!currentShare) return;
                await copyText(currentShare.url);
                window.open('https://drive.google.com/drive/u/0/home', '_blank', 'noopener');
                shareSelectedFolder.textContent = 'Enlace copiado. Ya puedes pegarlo en Google Drive.';
            });

            shareOtherApps?.addEventListener('click', async () => {
                if (!currentShare) return;
                if (navigator.share) {
                    await navigator.share({
                        title: currentShare.title,
                        text: currentShare.title,
                        url: currentShare.url,
                    });
                    return;
                }

                await copyText(currentShare.url);
                shareSelectedFolder.textContent = 'Tu navegador no soporta compartir directo. Hemos copiado el enlace.';
            });

            shareModal?.addEventListener('show.bs.modal', () => {
                shareDocCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });
                if (shareSearchInput) {
                    shareSearchInput.value = '';
                }
                shareDocItems.forEach((item) => {
                    item.style.display = '';
                    item.classList.remove('activo');
                });
                setStep(1);
                updateSelectionSummary();
            });

            const track = document.getElementById('svTrack');
            const btnPrev = document.getElementById('svPrev');
            const btnNext = document.getElementById('svNext');
            if (!track || !btnPrev || !btnNext) return;

            let page = 0;
            const PER_PAGE = 3;

            function cardWidth() {
                const card = track.querySelector('.sv-card');
                if (!card) return 0;
                return card.offsetWidth + (parseFloat(getComputedStyle(track).gap) || 16);
            }

            function totalPages() {
                return Math.ceil(track.children.length / PER_PAGE);
            }

            function goTo(p) {
                page = Math.max(0, Math.min(p, totalPages() - 1));
                track.style.transform = `translateX(-${page * PER_PAGE * cardWidth()}px)`;
                btnPrev.disabled = page === 0;
                btnNext.disabled = page >= totalPages() - 1;
            }

            btnPrev.addEventListener('click', () => goTo(page - 1));
            btnNext.addEventListener('click', () => goTo(page + 1));
            goTo(0);
        })();
    </script>
</body>

</html>
