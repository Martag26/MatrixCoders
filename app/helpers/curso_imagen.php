<?php

if (!function_exists('matrixcoders_curso_image')) {
    function matrixcoders_curso_image(?string $imageName, string $title = ''): string
    {
        $dir = dirname(__DIR__, 2) . '/public/img/cursos';

        static $availableFiles = null;
        if ($availableFiles === null) {
            $availableFiles = [];
            foreach (glob($dir . '/*') ?: [] as $file) {
                $availableFiles[strtolower(basename($file))] = basename($file);
            }
        }

        $baseFallback = isset($availableFiles['nodejs.jpg'])
            ? BASE_URL . '/img/cursos/' . $availableFiles['nodejs.jpg']
            : BASE_URL . '/img/aprendiendo.png';

        $candidate = trim((string)$imageName);
        if ($candidate !== '') {
            $lower = strtolower($candidate);
            if (isset($availableFiles[$lower])) {
                return BASE_URL . '/img/cursos/' . $availableFiles[$lower];
            }
        }

        $haystack = strtolower($candidate . ' ' . $title);
        $map = [
            'git' => 'git.jpg',
            'github' => 'git.jpg',
            'node' => 'nodejs.jpg',
            'nodejs' => 'nodejs.jpg',
            'sql' => 'bbdd.jpg',
            'mysql' => 'bbdd.jpg',
            'bbdd' => 'bbdd.jpg',
            'base de datos' => 'bbdd.jpg',
            'database' => 'bbdd.jpg',
        ];

        foreach ($map as $keyword => $file) {
            if (str_contains($haystack, $keyword) && isset($availableFiles[strtolower($file)])) {
                return BASE_URL . '/img/cursos/' . $availableFiles[strtolower($file)];
            }
        }

        return $baseFallback;
    }
}
