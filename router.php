<?php
/**
 * Router para el servidor integrado de PHP — usado con ngrok.
 *
 *   php -S 0.0.0.0:8000 router.php
 *
 *  - "/"              -> public/index.php (frontend del tirón)
 *  - "/admin", "/admin/..." -> admin/...
 *  - resto            -> public/<ruta>
 */

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$root = __DIR__;
$path = ($uri === '' ? '/' : $uri);

/**
 * Sirve un archivo estático con su MIME correcto (el built-in lo haría
 * automáticamente si la CWD fuese la del archivo, pero como mapeamos
 * `/` -> public/ a mano, lo hacemos nosotros).
 */
function serve_static(string $file): bool {
    static $mimes = [
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'json'=> 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg'=> 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp'=> 'image/webp',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        'mp4' => 'video/mp4',
        'mp3' => 'audio/mpeg',
        'woff'=> 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'html'=> 'text/html',
        'txt' => 'text/plain',
        'map' => 'application/json',
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (isset($mimes[$ext])) header('Content-Type: ' . $mimes[$ext]);
    header('Content-Length: ' . filesize($file));
    readfile($file);
    return true;
}

/* ── /admin → carpeta admin/ ─────────────────────────────────── */
if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    $rel = substr($path, strlen('/admin'));
    if ($rel === '' || $rel === '/') {
        $target = $root . '/admin/index.php';
    } else {
        $target = $root . '/admin' . $rel;
    }

    if (is_file($target)) {
        if (substr($target, -4) === '.php') {
            chdir(dirname($target));
            require $target;
            return true;
        }
        return serve_static($target);
    }
    chdir($root . '/admin');
    require $root . '/admin/index.php';
    return true;
}

/* ── /uploads, /css, /img, /js, etc. → public/ ───────────────── */
$publicTarget = $root . '/public' . ($path === '/' ? '/index.php' : $path);

if ($path !== '/' && is_file($publicTarget)) {
    if (substr($publicTarget, -4) === '.php') {
        chdir(dirname($publicTarget));
        require $publicTarget;
        return true;
    }
    return serve_static($publicTarget);
}

/* ── Cualquier otra ruta → public/index.php (front controller) ── */
chdir($root . '/public');
require $root . '/public/index.php';
return true;
