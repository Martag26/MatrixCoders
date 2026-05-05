<?php
require_once __DIR__ . '/../../helpers/documento_preview.php';
$documento = $documento ?: null;
$archivoSubido = $documento ? matrixcoders_documento_archivo_subido($documento) : null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Documento') ?></title>
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
                    <div class="shared-document-page">
                        <section class="shared-document-card">
                            <?php if (!$documento): ?>
                                <h1>Documento no encontrado</h1>
                                <p>El documento no existe o no pertenece a tu cuenta.</p>
                            <?php else: ?>
                                <div class="editor-head-actions">
                                    <a class="section-link" href="<?= BASE_URL ?>/index.php?url=nube">Volver a documentos</a>
                                </div>
                                <span class="template-kicker">DOCUMENTO</span>
                                <h1><?= htmlspecialchars($documento['titulo'] ?? 'Documento') ?></h1>
                                <?php if (!empty($documento['carpeta_nombre'])): ?>
                                    <p class="shared-document-meta"><?= htmlspecialchars($documento['carpeta_nombre']) ?></p>
                                <?php endif; ?>

                                <?php if ($archivoSubido): ?>
                                    <div class="document-preview-shell">
                                        <div class="document-preview-head">
                                            <div>
                                                <h2>Vista previa</h2>
                                                <p><?= htmlspecialchars($archivoSubido['original_name']) ?> · <?= htmlspecialchars($archivoSubido['type_label']) ?></p>
                                            </div>
                                            <div class="editor-head-actions">
                                                <a class="section-link" href="<?= htmlspecialchars($archivoSubido['public_path']) ?>" target="_blank" rel="noopener">Abrir archivo</a>
                                                <a class="section-link" href="<?= htmlspecialchars($archivoSubido['public_path']) ?>" download>Descargar</a>
                                            </div>
                                        </div>

                                        <?php if (!$archivoSubido['exists']): ?>
                                            <div class="document-preview-fallback">
                                                <p>No hemos encontrado el archivo físico en el servidor. Puedes revisar si sigue existiendo en la carpeta de subidas.</p>
                                            </div>
                                        <?php elseif ($archivoSubido['preview_type'] === 'iframe'): ?>
                                            <iframe
                                                class="document-preview-frame"
                                                src="<?= htmlspecialchars($archivoSubido['public_path']) ?>"
                                                title="Vista previa del documento"></iframe>
                                        <?php elseif ($archivoSubido['preview_type'] === 'image'): ?>
                                            <div class="document-preview-media">
                                                <img src="<?= htmlspecialchars($archivoSubido['public_path']) ?>" alt="<?= htmlspecialchars($archivoSubido['original_name']) ?>">
                                            </div>
                                        <?php elseif ($archivoSubido['preview_type'] === 'video'): ?>
                                            <video class="document-preview-player" controls preload="metadata">
                                                <source src="<?= htmlspecialchars($archivoSubido['public_path']) ?>">
                                            </video>
                                        <?php elseif ($archivoSubido['preview_type'] === 'audio'): ?>
                                            <audio class="document-preview-audio" controls preload="metadata">
                                                <source src="<?= htmlspecialchars($archivoSubido['public_path']) ?>">
                                            </audio>
                                        <?php else: ?>
                                            <div class="document-preview-fallback">
                                                <p>Este tipo de archivo no se puede mostrar embebido en todos los navegadores.</p>
                                                <p>Te dejamos acceso directo para abrirlo o descargarlo.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <pre class="shared-document-content"><?= htmlspecialchars($documento['contenido'] ?? '') ?></pre>
                                <?php endif; ?>
                            <?php endif; ?>
                        </section>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
