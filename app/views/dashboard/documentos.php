<?php
$carpetas = is_array($carpetas ?? null) ? $carpetas : [];
$documentos = is_array($documentos ?? null) ? $documentos : [];
$flash = $flash ?? null;
$currentUrl = $_GET['url'] ?? 'mis-documentos';
$isCloudView = $currentUrl === 'nube';
$pageKicker = $isCloudView ? 'NUBE' : 'DOCUMENTOS';
$pageTitleText = $isCloudView ? 'Nube' : 'Mis documentos';
$pageDescription = $isCloudView
    ? 'Centraliza tus archivos, organízalos por carpetas y mantén tu biblioteca siempre lista para abrir, compartir y continuar trabajando.'
    : 'Gestiona tus archivos, revisa carpetas y crea nuevas categorías desde aquí.';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Mis documentos') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard contenedor-dashboard-content">
                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <section class="contenido-dashboard workspace-page-shell">
                    <div class="documents-page">
                        <div class="documents-page-head">
                            <div>
                                <span class="template-kicker"><?= $pageKicker ?></span>
                                <h1><?= $pageTitleText ?></h1>
                                <p><?= $pageDescription ?></p>
                            </div>
                            <div class="editor-head-actions">
                                <a class="section-link" href="<?= BASE_URL ?>/index.php?url=nuevo-documento">Nuevo documento</a>
                                <a class="section-link" href="<?= BASE_URL ?>/index.php?url=dashboard">Volver al dashboard</a>
                            </div>
                        </div>

                        <?php if ($isCloudView): ?>
                            <section class="cloud-hero-card">
                                <div class="cloud-hero-copy">
                                    <span class="cloud-status-pill">Biblioteca central</span>
                                    <h2>Tu nube de trabajo, organizada en un solo sitio</h2>
                                    <p>
                                        Reúne tus documentos, consulta tus carpetas y entra en cada archivo desde una vista pensada para moverte rápido y tener todo a mano.
                                    </p>
                                </div>
                                <div class="cloud-hero-stats">
                                    <article class="cloud-stat-card">
                                        <strong><?= count($documentos) ?></strong>
                                        <span>Archivos disponibles</span>
                                    </article>
                                    <article class="cloud-stat-card">
                                        <strong><?= count($carpetas) ?></strong>
                                        <span>Carpetas organizadas</span>
                                    </article>
                                    <article class="cloud-stat-card cloud-stat-card-muted">
                                        <strong><?= count($documentos) ?></strong>
                                        <span>Listos para compartir</span>
                                    </article>
                                </div>
                            </section>
                        <?php endif; ?>

                        <?php if (!empty($flash['message'])): ?>
                            <div class="dashboard-flash dashboard-flash-<?= htmlspecialchars($flash['type']) ?>">
                                <?= htmlspecialchars($flash['message']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="documents-layout<?= $isCloudView ? ' documents-layout-cloud' : '' ?>">
                            <section class="documents-main-card">
                                <div class="documents-main-head">
                                    <div>
                                        <h2><?= $isCloudView ? 'Archivos disponibles' : 'Todos los documentos' ?></h2>
                                        <p><?= $isCloudView ? 'Explora tu biblioteca completa y abre cualquier documento desde aquí.' : 'Vista general de tus archivos recientes y organizados.' ?></p>
                                    </div>
                                    <span class="section-link section-link-static"><?= count($documentos) ?> documentos</span>
                                </div>

                                <?php if (empty($documentos)): ?>
                                    <div class="documents-empty-state">
                                        <h3>No tienes documentos todavía</h3>
                                        <p>Crea uno nuevo o sube un archivo para empezar a llenar este espacio.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="documents-list-grid">
                                        <?php foreach ($documentos as $doc): ?>
                                            <a class="document-row-card document-row-link" href="<?= BASE_URL ?>/index.php?url=documento&id=<?= (int)$doc['id'] ?>">
                                                <div class="document-row-icon">
                                                    <img src="<?= BASE_URL ?>/img/portapapeles.png" alt="Documento">
                                                </div>
                                                <div class="document-row-body">
                                                    <h3><?= htmlspecialchars($doc['titulo']) ?></h3>
                                                    <?php if (!empty($doc['carpeta_nombre'])): ?>
                                                        <p><?= htmlspecialchars($doc['carpeta_nombre']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="document-row-tag">Abrir</span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </section>

                            <aside class="documents-side-card">
                                <div class="dashboard-section-head">
                                    <h2>Carpetas</h2>
                                    <span class="section-link section-link-static"><?= count($carpetas) ?></span>
                                </div>

                                <form method="post" class="folder-form">
                                    <input type="hidden" name="dashboard_action" value="create_folder">
                                    <input type="text" name="folder_name" placeholder="Nueva carpeta">
                                    <button type="submit">Crear carpeta</button>
                                </form>

                                <div class="documents-folder-stack">
                                    <?php if (empty($carpetas)): ?>
                                        <p class="text-muted">No tienes carpetas todavía.</p>
                                    <?php else: ?>
                                        <?php foreach ($carpetas as $carpeta): ?>
                                            <div class="carpeta-card carpeta-card-soft">
                                                <img src="<?= BASE_URL ?>/img/carpeta.png" class="imagen-carpeta" alt="Carpeta">
                                                <div class="carpeta-body">
                                                    <p><?= htmlspecialchars($carpeta['nombre']) ?></p>
                                                    <span><?= (int)($carpeta['total_documentos'] ?? 0) ?> documentos</span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isCloudView): ?>
                                    <div class="cloud-roadmap-card">
                                        <span class="template-kicker">RESUMEN</span>
                                        <h3>Una vista clara para gestionar toda tu biblioteca</h3>
                                        <p>Consulta tus carpetas, localiza documentos al instante y mantén tu espacio de trabajo ordenado desde un único panel.</p>
                                    </div>
                                <?php endif; ?>
                            </aside>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
