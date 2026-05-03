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

define('BASE_URL', '/matrixcoders/public');

// Clave de Google AI Studio para generar apuntes con Gemini
// Obtén la tuya gratis en: https://aistudio.google.com/app/apikey
define('GEMINI_API_KEY', '');

// Client ID de Google Cloud Console para vincular cuentas Google (NotebookLM)
// Configúralo en: https://console.cloud.google.com → APIs → Credenciales → OAuth 2.0
define('GOOGLE_CLIENT_ID', '');
