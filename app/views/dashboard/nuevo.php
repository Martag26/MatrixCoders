<?php
$carpetas = is_array($carpetas ?? null) ? $carpetas : [];
$selectedTemplate = is_array($selectedTemplate ?? null) ? $selectedTemplate : null;
$prefillTitle = $selectedTemplate['titulo'] ?? '';
$prefillContent = $selectedTemplate['contenido'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Nuevo documento') ?></title>
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

                <section class="contenido-dashboard workspace-page-shell">
                    <div class="editor-page">
                        <div class="editor-page-head">
                            <div>
                                <span class="template-kicker">DOCUMENTO</span>
                                <h1>Crear archivo</h1>
                                <p>
                                    <?= $selectedTemplate ? 'Partiendo de la plantilla seleccionada, ya puedes editar y guardar tu documento.' : 'Crea un documento desde cero y guárdalo directamente en tu espacio de trabajo.' ?>
                                </p>
                            </div>
                            <div class="editor-head-actions">
                                <a class="section-link" href="<?= BASE_URL ?>/index.php?url=plantillas-documento">Ver plantillas</a>
                                <a class="section-link" href="<?= BASE_URL ?>/index.php?url=dashboard">Volver al dashboard</a>
                            </div>
                        </div>

                        <div class="editor-layout">
                            <section class="editor-card">
                                <form method="post" class="dashboard-form">
                                    <div class="form-grid">
                                        <label>
                                            <span>Título</span>
                                            <input type="text" name="doc_title" value="<?= htmlspecialchars($prefillTitle) ?>" placeholder="Ej. Documento nuevo">
                                        </label>
                                        <label>
                                            <span>Guardar en carpeta</span>
                                            <select name="folder_id">
                                                <option value="">Guardar fuera de carpeta</option>
                                                <?php foreach ($carpetas as $carpeta): ?>
                                                    <option value="<?= (int)$carpeta['id'] ?>"><?= htmlspecialchars($carpeta['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                    </div>

                                    <label>
                                        <span>Contenido</span>
                                        <textarea name="doc_content" rows="18" class="editor-textarea" placeholder="Empieza a escribir aquí..."><?= htmlspecialchars($prefillContent) ?></textarea>
                                    </label>

                                    <button class="btn-panel-submit" type="submit">Guardar documento</button>
                                </form>
                            </section>

                            <aside class="editor-side-card">
                                <h2><?= $selectedTemplate ? 'Plantilla activa' : 'Documento nuevo' ?></h2>
                                <?php if ($selectedTemplate): ?>
                                    <p><?= htmlspecialchars($selectedTemplate['descripcion']) ?></p>
                                    <div class="template-chip-row">
                                        <?php foreach (($selectedTemplate['etiquetas'] ?? []) as $tag): ?>
                                            <span class="template-chip"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p>Puedes empezar desde cero o abrir la galería de plantillas para cargar una base lista para usar.</p>
                                <?php endif; ?>
                            </aside>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
