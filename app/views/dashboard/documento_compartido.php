<?php
require_once __DIR__ . '/../../helpers/documento_preview.php';
$archivoSubido = !empty($documentoValido) ? matrixcoders_documento_archivo_subido($documento) : null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Documento compartido') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="shared-document-page">
                <?php if (empty($documentoValido)): ?>
                    <section class="shared-document-card">
                        <h1>Documento no disponible</h1>
                        <p>El enlace no es válido o el documento ya no está disponible.</p>
                    </section>
                <?php else: ?>
                    <section class="shared-document-card">
                        <span class="template-kicker">DOCUMENTO COMPARTIDO</span>
                        <h1><?= htmlspecialchars($documento['titulo'] ?? 'Documento') ?></h1>
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
                                        <p>El archivo original ya no está disponible en el servidor.</p>
                                    </div>
                                <?php elseif ($archivoSubido['preview_type'] === 'iframe'): ?>
                                    <iframe
                                        class="document-preview-frame"
                                        src="<?= htmlspecialchars($archivoSubido['public_path']) ?>"
                                        title="Vista previa del documento compartido"></iframe>
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
                                        <p>Este formato no tiene vista embebida universal.</p>
                                        <p>Puedes abrirlo o descargarlo desde los accesos superiores.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <pre class="shared-document-content"><?= htmlspecialchars($documento['contenido'] ?? '') ?></pre>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
