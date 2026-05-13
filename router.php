<?php
/**
 * Router para el servidor integrado de PHP — usado con ngrok.
 *
 *   php -S 0.0.0.0:8000 router.php
 *
 *  - "/"              -> public/index.php (frontend)
 *  - "/admin", "/admin/..." -> admin/...
 *  - resto            -> public/<ruta>
 *
 * Seguridad:
 *  - Bloquea acceso directo a archivos sensibles (BD, .env, .git, etc.).
 *  - En /uploads/ nunca ejecuta PHP: lo sirve como text/plain (evita
 *    RCE si alguien lograse subir un .php).
 *  - Añade cabeceras de seguridad básicas.
 */

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$root = __DIR__;
$path = ($uri === '' ? '/' : $uri);

/* ── Cabeceras de seguridad globales ─────────────────────────── */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
// HSTS: ngrok ya da HTTPS, así que indicamos al navegador que use solo HTTPS
header('Strict-Transport-Security: max-age=31536000');

/* ── Bloqueo de rutas sensibles (BD, secretos, control de versiones) ── */
$blockedPatterns = [
    '~^/\.env~i',
    '~^/\.git(/|$)~i',
    '~^/composer\.(json|lock)$~i',
    '~^/app(/|$)~i',                   // /app/* nunca debe ser accesible
    '~\.sqlite(?:[-./?#]|$)~i',
    '~\.bak(?:[?#]|$)~i',
    '~\.sql(?:[?#]|$)~i',
    '~\.log(?:[?#]|$)~i',
    '~^/router\.php$~i',
    '~^/serve\.bat$~i',
    '~^/_tmp_~i',                      // archivos temporales de auditoría
    '~^/CREDENCIALES~i',               // archivo con contraseñas
    '~^/[^/]+\.md$~i',                 // *.md en la raíz (DOCUMENTACION, README, etc.)
    '~^/\.gitignore$~i',
    '~^/\.htaccess$~i',
];
foreach ($blockedPatterns as $pat) {
    if (preg_match($pat, $path)) {
        http_response_code(404);
        echo "Not Found";
        return true;
    }
}

/**
 * Sirve un archivo estático con su MIME correcto.
 * Si está dentro de /uploads/, fuerza text/plain para evitar ejecución
 * accidental de PHP/HTML/JS subidos (defensa en profundidad).
 */
function serve_static(string $file, string $publicPath = ''): bool {
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

    // Defensa en profundidad: dentro de /uploads/ nunca interpretamos como código
    if (stripos($publicPath, '/uploads/') === 0) {
        $dangerous = ['php','phtml','phar','pl','py','rb','sh','cgi','asp','aspx','jsp','htaccess'];
        if (in_array($ext, $dangerous, true)) {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        } else {
            header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        }
    } else {
        if (isset($mimes[$ext])) header('Content-Type: ' . $mimes[$ext]);
    }
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
        return serve_static($target, $path);
    }
    chdir($root . '/admin');
    require $root . '/admin/index.php';
    return true;
}

/* ── /uploads, /css, /img, /js, etc. → public/ ───────────────── */
$publicTarget = $root . '/public' . ($path === '/' ? '/index.php' : $path);

if ($path !== '/' && is_file($publicTarget)) {
    // Dentro de /uploads/: NUNCA ejecutar PHP, siempre servir como estático.
    if (stripos($path, '/uploads/') === 0) {
        return serve_static($publicTarget, $path);
    }
    if (substr($publicTarget, -4) === '.php') {
        chdir(dirname($publicTarget));
        require $publicTarget;
        return true;
    }
    return serve_static($publicTarget, $path);
}

/* ── Cualquier otra ruta → public/index.php (front controller) ── */
chdir($root . '/public');
require $root . '/public/index.php';
return true;
