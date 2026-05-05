<?php

class GeminiService
{
    private string $apiKey;
    private string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
    }

    public function generarApuntesDesdeYoutube(string $youtubeUrl, string $tituloLeccion): array
    {
        if (!$this->apiKey) {
            return ['ok' => false, 'error' => 'API key de Gemini no configurada. Añade GEMINI_API_KEY en config.php'];
        }

        $prompt = $this->buildPrompt($tituloLeccion);

        $body = json_encode([
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['file_data' => ['file_uri' => $youtubeUrl, 'mime_type' => 'video/mp4']],
                ],
            ]],
            'generationConfig' => [
                'temperature'     => 0.3,
                'maxOutputTokens' => 2048,
            ],
        ]);

        $url = $this->endpoint . '?key=' . urlencode($this->apiKey);

        $context = stream_context_create([
            'http' => [
                'method'         => 'POST',
                'header'         => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content'        => $body,
                'timeout'        => 90,
                'ignore_errors'  => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return ['ok' => false, 'error' => 'No se pudo conectar con la API de Gemini'];
        }

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            $msg = $data['error']['message'] ?? 'Error desconocido de la API';
            return ['ok' => false, 'error' => $msg];
        }

        $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!$texto) {
            return ['ok' => false, 'error' => 'La IA no generó contenido. Inténtalo de nuevo.'];
        }

        return ['ok' => true, 'contenido' => trim($texto)];
    }

    public function preguntaConContexto(string $pregunta, string $contexto): array
    {
        if (!$this->apiKey) {
            return ['ok' => false, 'error' => 'API key de Gemini no configurada'];
        }

        $prompt = "Eres un tutor experto de la plataforma educativa MatrixCoders. Un alumno tiene una duda sobre el siguiente contenido del curso:\n\n---\n{$contexto}\n---\n\nResponde la siguiente pregunta de forma clara, precisa y en español. Si la respuesta no está en el contexto, di que no tienes información suficiente pero intenta ayudar con tu conocimiento general.\n\nPregunta del alumno: {$pregunta}";

        $body = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 1024],
        ]);

        $url = $this->endpoint . '?key=' . urlencode($this->apiKey);
        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content'       => $body,
                'timeout'       => 60,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return ['ok' => false, 'error' => 'No se pudo conectar con Gemini'];
        }

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            return ['ok' => false, 'error' => $data['error']['message'] ?? 'Error de la API'];
        }

        $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!$texto) {
            return ['ok' => false, 'error' => 'La IA no generó respuesta'];
        }

        return ['ok' => true, 'respuesta' => trim($texto)];
    }

    private function buildPrompt(string $titulo): string
    {
        return "Eres un asistente educativo experto. Analiza el contenido de este vídeo educativo titulado \"{$titulo}\" y genera apuntes estructurados, completos y útiles en español para estudiantes universitarios.

Usa exactamente este formato Markdown:

## 📋 Resumen
[2-3 frases que resumen el contenido del vídeo de forma clara]

## 🎯 Conceptos clave
- **[Término o concepto]**: [Definición clara y precisa]
[Incluye todos los términos técnicos y conceptos importantes del vídeo]

## 📌 Puntos importantes
1. [Punto relevante bien explicado]
2. [Siguiente punto]
[Lista los contenidos más importantes del vídeo en orden lógico]

## 💡 Ideas para recordar
- [Idea o conclusión principal 1]
- [Idea o conclusión principal 2]
- [Idea o conclusión principal 3]

## ❓ Preguntas de repaso
1. [Pregunta para comprobar que se entendió el contenido]
2. [Otra pregunta]
3. [Otra pregunta]

Sé específico con el contenido real del vídeo. Los apuntes deben ser suficientes para que un estudiante entienda la lección sin necesidad de ver el vídeo de nuevo.";
    }
}
