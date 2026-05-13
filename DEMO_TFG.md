# Guion de demo — MatrixCoders (TFG, 8 minutos)

Documento de apoyo para la presentación en vivo, **basado solo en funcionalidades comprobadas que están operativas en este repositorio**. La IA (apuntes Gemini, chat RAG y chatbot Oráculo) y la vinculación con Google quedan **fuera de la demo** porque las claves `GEMINI_API_KEY` y `GOOGLE_CLIENT_ID` están vacías en [app/config.php](app/config.php). Si en el entorno de presentación están configuradas, se pueden añadir como extra opcional al final.

## Antes de empezar (no cuenta en los 8 minutos)

1. **Comprobar Stripe.** Mirar si existe `.env` en la raíz con `STRIPE_SECRET_KEY=…`. En este repo `.env` está en `.gitignore` y **no se sube** a GitHub, así que cada equipo lo crea localmente. Dos escenarios posibles:
   - **Hay `.env` con clave de test de Stripe** → el botón "Pagar" lleva al **Stripe Checkout real** (`checkout.stripe.com`). Tarjeta de prueba: `4242 4242 4242 4242`, cualquier fecha futura, cualquier CVV.
   - **No hay `.env`** → el sistema cae en **modo simulado**: pulsar "Pagar" matricula al instante y redirige a `?url=pago-ok&simulado=1`. La demo funciona igual, solo cambia el comentario que haces (ver §2).
2. Datos mínimos en BD: al menos un alumno con un curso matriculado, ese curso con unidades + lecciones + examen test + examen práctico, una entrega ya corregida (para enseñar nota), 2-3 notificaciones, 2-3 mensajes en el buzón.
3. **Dos pestañas abiertas y logueadas:**
   - Pestaña 1 — Alumno: `http://localhost/matrixcoders/public/`.
   - Pestaña 2 — CRM: `http://localhost/matrixcoders/admin/` con `isidoro@admin.com` (superadmin).
4. Zoom del navegador 110-125%.

---

## Reparto de tiempo

| Sección                                              | Tiempo  | Acumulado |
|------------------------------------------------------|---------|-----------|
| 0. Pitch inicial                                     | 0:30    | 0:30      |
| 1. Registro y login del alumno                        | 0:45    | 1:15      |
| 2. Catálogo, carrito y pago                           | 1:15    | 2:30      |
| 3. Dashboard, "Mis cursos" y reproductor de lección  | 1:15    | 3:45      |
| 4. Examen, certificado y ciclo de matrícula           | 1:00    | 4:45      |
| 5. Calendario inteligente y notificaciones            | 0:45    | 5:30      |
| 6. Buzón e incidencias                                | 1:00    | 6:30      |
| 7. CRM — gestión, edición de cursos y correcciones    | 1:15    | 7:45      |
| 8. Cierre                                             | 0:15    | 8:00      |

---

## 0. Pitch inicial — 30 s

**Con la home a la vista, sin tocar nada:**

> "MatrixCoders es una **plataforma educativa para estudiantes de DAW** que integra todo el ciclo de aprendizaje: catálogo, lecciones con notas, dos tipos de examen, certificación automática, mensajería e incidencias, calendario inteligente, pagos con Stripe y un CRM completo. Está hecha en **PHP con arquitectura MVC propia, base de datos SQLite, Bootstrap y JavaScript vanilla**. Os la enseño en vivo."

---

## 1. Registro y login del alumno — 45 s

**Rutas:** `?url=register` → `?url=login` → dashboard.

**Pasos:**

1. Abrir `?url=register`. **Scrollear** sin enviar para enseñar las dos columnas: datos básicos a la izquierda, **"perfil de aprendizaje"** a la derecha (áreas de interés, tecnologías, nivel, frecuencia de estudio, objetivo, GitHub).
2. Volver a `?url=login`. Entrar con un alumno preexistente.

**Qué decir:**

> "El registro pide email y contraseña (guardada con **bcrypt**) y un perfil opcional que la plataforma usa después para inferir un rol profesional sugerido y personalizar el dashboard. El sistema distingue cuatro roles —**USUARIO, INSTRUCTOR, MODERADOR, ADMINISTRADOR**— y el portal del alumno y el CRM son experiencias separadas: el login te lleva a una u otra según tu rol."

---

## 2. Catálogo, carrito y pago — 1:15

**Rutas:** home → `?url=buscar` → `?url=detallecurso&id=N` → `?url=carrito` → checkout.

**Pasos:**

1. En la home, señalar los **cursos destacados** (ordenados por número de matrículas).
2. Abrir `?url=buscar`. Probar el **autocomplete** escribiendo dos letras. Aplicar **un filtro multivalor** (p. ej. categoría + nivel) y enseñar el grid filtrado con paginación.
3. Entrar en una tarjeta de curso → enseñar el **temario plegable**, el precio y, si hay campaña, el **precio con descuento tachado**.
4. Pulsar **"Añadir al carrito"** — el badge se actualiza por AJAX sin recargar.
5. Ir al `?url=carrito`. Mostrar el desglose: **subtotal, ahorro de campaña, IVA 21%, total**.
6. Pulsar **"Pagar"**.

**Qué decir mientras se procesa el pago:**

> "La búsqueda funciona con `WHERE` dinámico sobre SQLite y prepared statements. El carrito vive en sesión PHP. El checkout es una sesión de **Stripe en modo `payment`** con los `line_items` que llevan el IVA incluido."

**Adaptación según el entorno:**

- **Si `.env` tiene clave Stripe:** "Como veis, esto nos lleva al **Stripe Checkout real** en `checkout.stripe.com`. Pago con la tarjeta de pruebas `4242 4242 4242 4242` (la pones rápido) — Stripe nos redirige al `success_url`, **y en paralelo el webhook firmado** matricula al alumno por si cerrase la pestaña."
- **Si no hay `.env`:** "El sistema detecta que no hay clave Stripe configurada y **cae en modo simulado**: matricula directamente y redirige a la página de éxito. En producción, ese mismo botón abre el Stripe Checkout real con `4242 4242 4242 4242` y un webhook firmado confirma el pago."

7. Mostrar la página de "Pago realizado" → ir a "Mis cursos" y enseñar que **el curso recién comprado ya aparece matriculado**.

**Adicional opcional (1 frase):** "Además del pago suelto, hay **suscripciones recurrentes** con planes Estudiantes (19,99 €/mes) y Empresas (49,99 €/mes) que dan acceso total al catálogo, también en Stripe."

---

## 3. Dashboard, "Mis cursos" y reproductor de lección — 1:15

**Rutas:** `?url=dashboard` → `?url=mis-cursos` → `?url=leccion&id=N`.

**Pasos:**

1. Volver al **dashboard**: mostrar de un vistazo los widgets — **cursos en progreso con % de avance**, próximas tareas, **mini-calendario con días marcados**, mensajes recientes, panel de "Perfil profesional sugerido".
2. Abrir `?url=mis-cursos`: filtros **en progreso / completados / sin empezar**, barras de progreso, botón "Continuar" que abre exactamente la **última lección vista** o la primera si no ha empezado el curso.
3. Entrar en una lección: enseñar:
   - **Sidebar** con unidades y lecciones (✓ marcadas las vistas, botón para marcar manualmente toda una unidad).
   - **Vídeo embebido** de YouTube.
   - Panel **"Mis notas"**: escribir algo y enseñar cómo se guarda automáticamente (upsert por usuario + lección).
   - Panel **"Recursos del instructor"**: lista de archivos descargables o con botón "Guardar en mi nube" que copia el recurso al `documento` personal del alumno.

**Qué decir:**

> "El dashboard ejecuta **una sola consulta con subconsultas correlacionadas** para sacar progreso y última lección por curso. Las lecciones marcan progreso con `INSERT OR IGNORE` en `leccion_vista` y eso alimenta tanto los % del dashboard como el calendario y, lo más importante, el **gating del examen**: hasta que no estén todas vistas, no se puede examinar."

---

## 4. Examen, certificado y ciclo de matrícula — 1:00

**Rutas:** desde la lección, sidebar → "Examen" → `?url=examen&curso=N`.

**Pasos:**

1. Pulsar "Examen". Si todavía faltan lecciones, mostrar la **pantalla de bloqueo** con barra de progreso y mensaje "te falta X lecciones / Y tareas".
2. Si está todo completado, **rellenar el examen tipo test** (un par de preguntas basta) y enviar. Mostrar la pantalla de resultado con **nota, aprobado/no, intentos usados de 2**.
3. Si el curso tiene **examen práctico**: ir a `?url=examen-practico&curso=N` y enseñar el listado de tareas a entregar (texto + archivo, máx 50 MB).
4. Cuando todas las entregas están corregidas por el instructor con media ≥ nota mínima → mostrar **el certificado** con su **código único**.

**Qué decir:**

> "El examen tiene **gating de progreso** y solo **dos intentos**. Si los agota sin aprobar, la matrícula pasa a `revocada` y el alumno pierde el acceso — para volver tiene que rematricularse. Si aprueba: si el curso no tiene práctico, **se emite el certificado al instante** con un código único; si tiene práctico, se desbloquea la entrega y la nota final se calcula con una fórmula ponderada: **Test 20% + Entregables 30% + Práctico 50%**. La matrícula caduca a los **90 días**, que es la fecha que veremos pintada en rojo en el calendario."

---

## 5. Calendario inteligente y notificaciones — 45 s

**Rutas:** `?url=calendario` → campana del header.

**Pasos:**

1. Abrir el calendario. Señalar las capas superpuestas: **tareas** (un color por curso), **entregables** (📝 pendiente / ✓ entregada), **expiraciones** del curso (rojo), **eventos personales** del alumno (con drag-and-drop, son los únicos editables).
2. Apuntar al panel lateral del calendario: **racha de estudio** (días consecutivos con al menos una lección), **Smart Slots** ("💡 Estudia React el martes a las 18:00", basados en el patrón histórico del alumno), y **Skills Radar** de Chart.js con sus habilidades inferidas.
3. Cerrar el calendario, ir a cualquier página y pulsar la **campana del header**: aparecen notificaciones generadas automáticamente (tarea próxima, examen disponible, mensaje nuevo, curso completado).

**Qué decir:**

> "El calendario combina FullCalendar.js con un **motor de sugerencias en backend**: analizamos qué día y hora estudia más el alumno con `strftime` sobre sus visualizaciones y le proponemos un hueco para la siguiente sesión. Las notificaciones se generan con un patrón **idempotente** — ocho generadores que se ejecutan en cada petición pero solo insertan lo que aún no existe, así nunca se duplican."

---

## 6. Buzón e incidencias — 1:00

**Rutas:** `?url=buzon`.

**Pasos:**

1. Abrir el buzón. Enseñar la bandeja con mensajes recibidos del staff, los **no leídos** arriba.
2. Abrir un mensaje: queda marcado como leído automáticamente. Pulsar **"Responder"** y enviar una respuesta — explicar que se mantiene **agrupada por hilo** (`hilo_id`).
3. Cambiar a la pestaña **"Mis incidencias"** y abrir el formulario de **"Nueva incidencia"**. Crearla con un asunto y un cuerpo cualquiera. Mostrar que aparece en estado `abierta`.
4. Abrirla y enseñar el detalle: las **respuestas se irán encadenando** cuando el staff conteste desde el CRM (lo veremos en el siguiente bloque).

**Qué decir:**

> "Hay **dos canales de comunicación** entre alumnos y plataforma. Los **mensajes** son bidireccionales, con hilos: cuando respondes a un mensaje hereda el `hilo_id` del padre y el buzón puede agrupar la conversación. Las **incidencias** son tickets formales: el alumno los abre, el CRM los asigna a un staff, se responden, cambian de estado (`abierta → en_proceso → cerrada`) y el alumno ve todo el historial. Toda la API es JSON, todo va con prepared statements y verificación de propiedad."

---

## 7. CRM — gestión, edición de cursos y correcciones — 1:15

**Cambia a la pestaña 2 (CRM ya logueado).**

**Pasos:**

1. **Dashboard del CRM:** KPIs (usuarios, cursos activos, campañas vigentes, matrículas totales, incidencias abiertas), gráfico de registros últimos 6 meses, top de cursos por matrículas.
2. **Usuarios** (`?sec=usuarios`): listado con búsqueda y filtro de rol, abrir un usuario y mostrar el modal de edición (cambio de rol, reset de contraseña). Apunta: **solo `ADMINISTRADOR` accede a esta sección**.
3. **Editor de un curso** (`?sec=editor&id=N`): enseñar el árbol de **unidades y lecciones reordenables por drag-and-drop**, el botón para subir imagen del curso, el formulario del **examen test** con preguntas y opciones, y la lista de **tareas del examen práctico**.
4. **Comunicación / Incidencias** (`?sec=comunicacion`): abrir **la incidencia que acabas de crear como alumno**, responderla aquí desde el CRM y cambiarla a estado `en_proceso`. Si tienes una entrega práctica pendiente, abrir la **corrección**: poner una nota y comentario → guardar → mostrar que **se registra en `crm_actividad`**.
5. **Campañas** (`?sec=campanas`): listado con sus descuentos y fechas. Si hay tiempo, abrir el formulario para enseñar la **detección automática de conflictos** (solapes, títulos duplicados).
6. **Logs** (`?sec=logs`): tabla de auditoría con todo lo que hace el staff, paginada y filtrable.

**Para cerrar el bloque del CRM, volver al portal del alumno:**

7. **Pestaña 1** → abrir la incidencia: **la respuesta del staff ya está**. Volver al buzón: **ha llegado la notificación**.

**Qué decir:**

> "El CRM es una aplicación independiente con su propio login en `/admin/`. Hay **tres roles del staff**: administrador (todo), moderador (no usuarios) e instructor (sus cursos asignados). Toda acción se registra en `crm_actividad` para auditoría. El backend del CRM expone **una única API JSON** con unas 45 acciones discriminadas por `?action=`, lo que mantiene el frontend en JavaScript vanilla muy ligero, sin frameworks."

---

## 8. Cierre — 15 s

> "En resumen: **PHP MVC manual sobre SQLite**, autenticación bcrypt, pagos con Stripe, mensajería bidireccional con sistema de incidencias, calendario inteligente con heurística de patrones, exámenes con dos modalidades y certificación automática, y un CRM completo para que el staff opere de forma independiente. Todo el ciclo del alumno y del personal está implementado y funcionando. Gracias."

---

## Plan B — si algo falla en vivo

- **Stripe no responde / pago falla:** explica que en producción funciona, vuelve a `?url=mis-cursos` y sigue con el dashboard. Si estás en modo simulado, ni te enteras.
- **No carga una lección concreta:** abre otra que sepas que tiene vídeo. Si todas fallan, salta al examen y a "Mis cursos".
- **Sin Internet:** **todo el flujo offline funciona** (registro, login, dashboard, lecciones con vídeo cacheado por YouTube no, pero el resto sí, exámenes, CRM, notificaciones, calendario, buzón, incidencias). Solo se cae el embed de YouTube en lecciones.
- **Si te quedas corto de tiempo:** sacrifica la sección 5 (calendario) antes que la 7 (CRM), porque el CRM es el elemento más diferenciador en una presentación.

## Cosas que **no** mostrar en vivo (pero tener listas para preguntas)

- Migraciones automáticas idempotentes en `Database::connect()`: cómo el esquema se evoluciona en cada arranque con `ALTER TABLE` + try/catch y dos rebuilds controlados (`_mig_examen_tipo`, `_mig_notif_v2`).
- Webhook de Stripe y verificación de firma con `Webhook::constructEvent`.
- Patrón "Set" con `array_flip` para chequeos O(1) de lecciones vistas.
- Fórmula exacta de la nota ponderada y emisión del certificado con código `md5(uid-curso-microtime)`.
- Tabla `crm_actividad` y patrón idempotente de generación de notificaciones.
- Tokens compartidos de documentos con `hash_equals` para enlaces públicos sin sesión.

Estas piezas son **ideales para preguntas técnicas del tribunal** sobre arquitectura, seguridad o decisiones de diseño.

## Funcionalidades implementadas pero **no demostrables sin claves externas**

Por transparencia: el código de estas integraciones está terminado y se ve en el repo, pero requieren claves que **no están configuradas por defecto**. Mejor no enseñarlas en vivo salvo que se hayan configurado expresamente:

- **Apuntes IA por lección** (`?url=apuntes-ia&leccion=N`) con Google Gemini — requiere `GEMINI_API_KEY` en [app/config.php](app/config.php).
- **Chat RAG dentro de la lección** — mismo requisito.
- **Chatbot flotante Oráculo** (`?url=chatbot`) — mismo requisito.
- **Vinculación de cuenta Google** (`?url=vincular-google`) — requiere `GOOGLE_CLIENT_ID`.

Si en el momento de la presentación están configuradas, se pueden añadir entre la sección 3 y la 4 con un par de pinceladas (≤ 30 s) sin desmontar el guion.
