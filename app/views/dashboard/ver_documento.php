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
    <style>
    .doc-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        font-family: 'Saira', sans-serif;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all .15s;
        white-space: nowrap;
    }
    .doc-btn-back {
        background: #f1f5f9;
        color: #374151;
        border: 1.5px solid #e5e7eb;
    }
    .doc-btn-back:hover { background: #e5e7eb; color: #111827; }
    .doc-btn-open {
        background: #fff;
        color: #2563eb;
        border: 1.5px solid #bfdbfe;
    }
    .doc-btn-open:hover { background: #eff6ff; color: #1d4ed8; }
    .doc-btn-download {
        background: #5f8766;
        color: #fff;
    }
    .doc-btn-download:hover { background: #4a6b50; color: #fff; }
    .doc-actions-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .doc-actions-bar-right {
        margin-left: auto;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    </style>
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
                                <a class="doc-action-btn doc-btn-back" href="<?= BASE_URL ?>/index.php?url=nube">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
                                    Volver a la nube
                                </a>
                            <?php else: ?>
                                <div class="doc-actions-bar">
                                    <a class="doc-action-btn doc-btn-back" href="<?= BASE_URL ?>/index.php?url=nube">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
                                        Volver a la nube
                                    </a>
                                    <?php if ($archivoSubido && $archivoSubido['exists']): ?>
                                    <div class="doc-actions-bar-right">
                                        <a class="doc-action-btn doc-btn-open"
                                           href="<?= htmlspecialchars($archivoSubido['public_path']) ?>"
                                           target="_blank" rel="noopener">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            Abrir documento
                                        </a>
                                        <a class="doc-action-btn doc-btn-download"
                                           href="<?= htmlspecialchars($archivoSubido['public_path']) ?>"
                                           download>
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Descargar
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <span class="template-kicker">DOCUMENTO</span>
                                <h1><?= htmlspecialchars($documento['titulo'] ?? 'Documento') ?></h1>
                                <?php if (!empty($documento['carpeta_nombre'])): ?>
                                    <p class="shared-document-meta" style="color:#6b7280;font-size:13px;margin:0 0 16px;">
                                        📁 <?= htmlspecialchars($documento['carpeta_nombre']) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ($archivoSubido): ?>
                                    <div class="document-preview-shell">
                                        <div class="document-preview-head">
                                            <div>
                                                <h2>Vista previa</h2>
                                                <p><?= htmlspecialchars($archivoSubido['original_name']) ?> · <?= htmlspecialchars($archivoSubido['type_label']) ?></p>
                                            </div>
                                        </div>

                                        <?php if (!$archivoSubido['exists']): ?>
                                            <div class="document-preview-fallback">
                                                <p>No hemos encontrado el archivo físico en el servidor.</p>
                                            </div>
                                        <?php elseif ($archivoSubido['preview_type'] === 'iframe'): ?>
                                            <iframe class="document-preview-frame"
                                                src="<?= htmlspecialchars($archivoSubido['public_path']) ?>"
                                                title="Vista previa del documento"></iframe>
                                        <?php elseif ($archivoSubido['preview_type'] === 'image'): ?>
                                            <div class="document-preview-media">
                                                <img src="<?= htmlspecialchars($archivoSubido['public_path']) ?>"
                                                     alt="<?= htmlspecialchars($archivoSubido['original_name']) ?>">
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
                                                <p>Este tipo de archivo no se puede mostrar embebido.</p>
                                                <p>Usa los botones de arriba para abrirlo o descargarlo.</p>
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
