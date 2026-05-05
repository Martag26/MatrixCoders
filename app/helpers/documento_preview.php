<?php

function matrixcoders_documento_archivo_subido(?array $documento): ?array
{
    $contenido = (string)($documento['contenido'] ?? '');
    if ($contenido === '') {
        return null;
    }

    if (!preg_match('/^Ruta del archivo:\s*(.+)$/mi', $contenido, $pathMatch)) {
        return null;
    }

    $publicPath = trim($pathMatch[1]);
    if ($publicPath === '') {
        return null;
    }

    preg_match('/^Archivo original:\s*(.+)$/mi', $contenido, $nameMatch);
    preg_match('/^Tipo de archivo:\s*(.+)$/mi', $contenido, $typeMatch);

    $pathForExtension = parse_url($publicPath, PHP_URL_PATH) ?: $publicPath;
    $extension = strtolower((string)pathinfo($pathForExtension, PATHINFO_EXTENSION));

    $previewType = 'download';
    if ($extension === 'pdf') {
        $previewType = 'iframe';
    } elseif (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], true)) {
        $previewType = 'image';
    } elseif (in_array($extension, ['mp4', 'webm', 'ogg'], true)) {
        $previewType = 'video';
    } elseif (in_array($extension, ['mp3', 'wav', 'oga'], true)) {
        $previewType = 'audio';
    }

    $baseUrl = rtrim((string)BASE_URL, '/');

    // Resolve to relative server path and full public URL
    if (preg_match('/^https?:\/\//i', $publicPath)) {
        $relativePath = $publicPath;
        if ($baseUrl !== '' && strncmp($publicPath, $baseUrl, strlen($baseUrl)) === 0) {
            $relativePath = substr($publicPath, strlen($baseUrl));
        }
        $publicUrl = $publicPath;
    } else {
        $relativePath = '/' . ltrim($publicPath, '/');
        $publicUrl = $baseUrl . $relativePath;
    }

    $absolutePath = dirname(__DIR__, 2) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $exists = is_file($absolutePath);

    $parts = preg_split("/\R\R+/", trim($contenido), 2);
    $notes = $parts[1] ?? '';

    return [
        'public_path' => $publicUrl,
        'absolute_path' => $absolutePath,
        'exists' => $exists,
        'original_name' => trim($nameMatch[1] ?? basename($pathForExtension)),
        'type_label' => trim($typeMatch[1] ?? strtoupper($extension ?: 'archivo')),
        'extension' => $extension,
        'preview_type' => $previewType,
        'notes' => trim($notes),
    ];
}
