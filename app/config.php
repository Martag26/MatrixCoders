<?php
// Cargar variables de entorno desde .env
(function() {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) return;
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key); $val = trim($val);
        if ($key !== '' && $val !== '') putenv("$key=$val");
    }
})();

/*
 * BASE_URL dinámico — se detecta a partir del SCRIPT_NAME para que la app
 * funcione tanto en XAMPP (servida en /matrixcoders/public) como tras el
 * servidor integrado de PHP / ngrok (servida en la raíz "/").
 *
 * Override opcional: define APP_BASE_URL en .env si quieres forzar un valor.
 */
(function () {
    $override = getenv('APP_BASE_URL');
    if ($override !== false && $override !== '') {
        define('BASE_URL', rtrim($override, '/'));
        define('ADMIN_BASE_URL', rtrim($override, '/') === ''
            ? '/admin'
            : dirname(rtrim($override, '/')) . '/admin');
        return;
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $script = str_replace('\\', '/', $script);
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');

    // Si estamos dentro de /admin/ (CRM), el BASE_URL público es el hermano /public.
    if (preg_match('#^(.*)/admin$#', $dir, $m)) {
        $publicBase = $m[1] === '' ? '' : $m[1] . '/public';
        $adminBase  = $dir;
    } else {
        // Caso normal: estamos dentro de public (o en la raíz si ngrok+php -S -t public)
        $publicBase = $dir; // p.ej. '/matrixcoders/public' o ''
        // admin es hermano de public
        if ($publicBase === '') {
            $adminBase = '/admin';
        } else {
            $parent = dirname($publicBase);
            $adminBase = ($parent === '/' || $parent === '\\' || $parent === '.') ? '/admin' : $parent . '/admin';
        }
    }

    define('BASE_URL', $publicBase);
    define('ADMIN_BASE_URL', $adminBase);
})();

// Clave de Google AI Studio para generar apuntes con Gemini
// Obtén la tuya gratis en: https://aistudio.google.com/app/apikey
define('GEMINI_API_KEY', '');

// Client ID de Google Cloud Console para vincular cuentas Google (NotebookLM)
// Configúralo en: https://console.cloud.google.com → APIs → Credenciales → OAuth 2.0
define('GOOGLE_CLIENT_ID', '');
