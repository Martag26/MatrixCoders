<?php
/**
 * Respuestas predefinidas para el chatbot Oráculo cuando NO hay
 * GEMINI_API_KEY configurada. Detecta palabras clave en la pregunta
 * del alumno y devuelve una respuesta razonable de la plataforma.
 *
 * Permite que la funcionalidad "vista" del chatbot se mantenga
 * disponible para demos sin tener que pagar/configurar Gemini.
 */
class ChatbotFallback
{
    /**
     * @return array{ok:bool,respuesta?:string,error?:string}
     */
    public static function responder(string $pregunta, string $nombre = 'alumno', array $cursos = []): array
    {
        $p = mb_strtolower($pregunta);
        $listaCursos = empty($cursos) ? 'aún no estás matriculado en ningún curso' : implode(', ', $cursos);

        $reglas = [
            // Saludos
            ['/\b(hola|buenas|hey|qué tal|que tal|buenos días|buenas tardes|buenas noches)\b/u',
             "¡Hola {$nombre}! Soy Oráculo, el asistente de MatrixCoders. ¿En qué puedo ayudarte? Puedo orientarte sobre cursos, exámenes, certificados, el calendario o cualquier otra duda sobre la plataforma."],

            // Cursos / matrículas
            ['/\b(mis cursos|qué cursos|que cursos|matriculad|inscrit)/u',
             "Estás matriculado en: {$listaCursos}.\n\nPuedes ver tu progreso completo en **Mis cursos** desde el menú lateral, y continuar desde la última lección que dejaste a medias."],

            ['/\b(comprar|matricular|inscribir|añadir curso|nuevo curso|catálogo|catalogo)\b/u',
             "Para apuntarte a un curso nuevo:\n\n1. Entra en el **catálogo** desde la home o usa la lupa para buscar.\n2. Abre el curso que te interese y pulsa **Añadir al carrito**.\n3. Ve al carrito y pulsa **Pagar** — el pago se procesa con Stripe.\n\nUna vez pagado, el curso aparecerá automáticamente en **Mis cursos**."],

            // Exámenes
            ['/\b(examen|tipo test|preguntas|nota|aprobar|aprobado|suspend)/u',
             "Sobre los exámenes:\n\n- Para acceder al examen tienes que tener **todas las lecciones marcadas como vistas**.\n- Tienes **2 intentos** por curso. Si los agotas sin aprobar, pierdes la matrícula y tienes que volver a inscribirte.\n- Si el curso tiene **examen práctico** (entregables), la nota final se calcula como: Test 20% + Entregables 30% + Práctico 50%."],

            // Certificado
            ['/\b(certificado|diploma|título|titulo|acreditación)\b/u',
             "Cuando apruebas un curso, se emite automáticamente un **certificado con código único** que puedes descargar desde el detalle del curso o desde **Mis cursos**. El código sirve para que terceros verifiquen la autenticidad."],

            // Buzón / mensajes
            ['/\b(buzón|buzon|mensajes|mensaje|escribir al profe|contactar|comunicación)\b/u',
             "Tienes dos canales:\n\n- **Buzón**: para conversaciones bidireccionales con el equipo (mensajes con hilo).\n- **Incidencias**: para tickets formales (problema técnico, queja, sugerencia). Se gestionan desde *Buzón → Mis incidencias*."],

            // Calendario
            ['/\b(calendario|fecha|tarea|entrega|deadline|expira|caducidad)/u',
             "El **Calendario** te muestra tus tareas, entregables y la fecha de expiración de cada matrícula (90 días desde la inscripción). Puedes arrastrar tus eventos personales para reorganizarte. Las sugerencias 'Smart Slots' te proponen huecos basándose en tu patrón de estudio."],

            // Notificaciones
            ['/\b(notificacion|campana|aviso|recordatorio)/u',
             "Recibirás notificaciones automáticas por: tarea cercana a vencer, examen ya disponible, mensaje nuevo, curso completado, etc. Las puedes consultar pulsando la **campana** en la cabecera."],

            // Suscripciones / planes
            ['/\b(plan|suscripción|suscripcion|premium|gratis|gratuito|precio|cuánto cuesta|cuanto cuesta)/u',
             "Hay tres opciones:\n\n- **Plan gratuito**: acceso limitado a algunos cursos.\n- **Estudiantes** (19,99 €/mes): acceso total al catálogo.\n- **Empresas** (49,99 €/mes): acceso total + recursos para equipos.\n\nPuedes contratar desde **Precios y planes de subscripción** en la cabecera. También puedes comprar cursos sueltos."],

            // Reset password / cuenta
            ['/\b(contraseña|contrasena|password|olvid|recuperar|cambiar mi)/u',
             "Para cambiar tu contraseña entra en **Ajustes → Seguridad** o en **Perfil**. Necesitarás confirmar la actual. La nueva debe tener al menos 10 caracteres, una mayúscula, una minúscula y un número."],

            // Perfil
            ['/\b(perfil|foto|avatar|biograf|datos personales)/u',
             "Desde **Perfil** puedes subir tu foto, escribir una bio, indicar tus tecnologías y áreas de interés. Esto ayuda a que la plataforma personalice mejor el dashboard y las sugerencias del calendario."],

            // Programación general
            ['/\b(html|css|javascript|js|php|python|java|sql|programar|lenguaje|framework|react|node)/u',
             "Buen tema. En el **catálogo** encontrarás cursos sobre ese lenguaje/tecnología. Si ya estás matriculado en un curso relacionado, te recomiendo abrir el reproductor de lecciones y usar el panel **'Mis notas'** para apuntar lo importante mientras estudias."],

            // Despedidas
            ['/\b(gracias|ok|vale|perfecto|adiós|adios|hasta luego|chao)\b/u',
             "¡A ti! Si te surge cualquier otra duda, vuelve a preguntarme. Mucho ánimo con los cursos 💪"],
        ];

        foreach ($reglas as [$regex, $resp]) {
            if (preg_match($regex, $p)) {
                return ['ok' => true, 'respuesta' => $resp];
            }
        }

        // Respuesta genérica si nada matcheó
        return ['ok' => true, 'respuesta' =>
            "He recibido tu pregunta sobre \"" . mb_substr($pregunta, 0, 80) . "\" pero no tengo información específica al respecto. Puedo ayudarte con:\n\n- 📚 Cursos y matrículas\n- 📝 Exámenes y certificados\n- 💬 Buzón e incidencias\n- 📅 Calendario y tareas\n- 💳 Suscripciones y pagos\n- 👤 Perfil y ajustes\n\n¿Sobre cuál quieres saber más?"
        ];
    }
}
