# Demo MatrixCoders — 7 minutos · Dos personas

## Preparación previa (no cuenta en el tiempo)
- Servidor corriendo en `localhost:8000`
- **Pestaña 1 (Alumno):** `localhost:8000` — logueado con `usuario@usuario.es`
- **Pestaña 2 (CRM):** `localhost:8000/admin/` — logueado con `isidoro@admin.com`
- Zoom del navegador al 115 %
- Examen reseteado (2 intentos disponibles)

---

## PERSONA A — Parte del alumno · ~3:30

### 0. Pitch — 20 s
*(Home visible, sin tocar nada)*

> "MatrixCoders es una plataforma educativa para DAW: catálogo con buscador, lecciones con notas, dos tipos de examen, certificación automática, mensajería, calendario inteligente y un CRM completo para el staff. Construida en PHP con arquitectura MVC propia, SQLite y Stripe. Os la enseñamos en vivo."

---

### 1. Registro y login — 40 s
**Ir a:** `?url=register` → `?url=login`

1. Abrir el registro. Scrollear sin enviar para mostrar las dos columnas: datos básicos a la izquierda, **perfil de aprendizaje** a la derecha (intereses, tecnologías, nivel, objetivo, GitHub).
2. > "La contraseña se guarda con bcrypt. El perfil personaliza el dashboard y sugiere un rol profesional. Hay cuatro roles: usuario, instructor, moderador y administrador — login te lleva a una experiencia u otra según el rol."
3. Sin registrarse — ir al login e iniciar sesión con `usuario@usuario.es`.

---

### 2. Catálogo, carrito y pago — 1:10
**Ir a:** home → `?url=buscar` → detalle → carrito → pago

1. En la home señalar los **cursos destacados** (ordenados por número de matrículas).
2. `?url=buscar` → escribir dos letras → **autocomplete en tiempo real**. Aplicar un filtro (categoría + nivel) → grid filtrado.
3. Abrir una ficha → **temario plegable**, precio y, si hay campaña, **precio tachado con descuento**.
4. "Añadir al carrito" → **badge actualizado sin recargar**.
5. `?url=carrito` → desglose: subtotal, ahorro de campaña, IVA 21 %, total.
6. Pulsar **"Pagar"**.
   > "El carrito vive en sesión PHP. El checkout va a Stripe con los line_items con IVA incluido. El webhook firmado matricula al alumno aunque cierre la pestaña."
7. Página de éxito → "Mis cursos" → el curso aparece ya matriculado.

---

### 3. Dashboard, lección y recursos — 1:00
**Ir a:** `?url=dashboard` → `?url=mis-cursos` → lección

1. Dashboard: cursos con **% de avance**, tareas próximas, mini-calendario, mensajes recientes, **perfil profesional sugerido**.
2. "Mis cursos" → "Continuar" → abre exactamente la última lección vista.
3. En la lección:
   - **Sidebar** con unidades y lecciones (✓ marcadas las vistas).
   - **Vídeo de YouTube** embebido.
   - Panel **"Mis notas"**: escribir algo → se guarda automáticamente.
   - Panel **"Recursos"**: archivos descargables o con botón "Guardar en mi nube".
   > "El dashboard corre una sola consulta con subconsultas correlacionadas. Las lecciones hacen INSERT OR IGNORE en leccion_vista — eso alimenta el % de progreso y el gating del examen: sin terminar todas las lecciones no se puede examinar."

---

### 4. Examen y certificado — 1:00
**Ir a:** sidebar → Examen → `?url=examen&curso=11`

1. Si faltan lecciones: mostrar la **pantalla de bloqueo** con barra de progreso y "te faltan X lecciones".
2. Rellenar el test y enviar → resultado: nota, aprobado/no, **intentos usados de 2**.
3. Mostrar el **certificado** con su código único verificable.
   > "Solo dos intentos. Si los agota sin aprobar, la matrícula pasa a 'revocada'. Si aprueba, el certificado se emite al instante con un código único generado sobre uid+curso+microtime. La matrícula caduca a los 90 días — esa fecha aparecerá en rojo en el calendario."

---

**→ PASA A PERSONA B**

---

## PERSONA B — Parte de funcionalidades avanzadas y CRM · ~3:30

### 5. Calendario y notificaciones — 40 s
**Ir a:** `?url=calendario` → campana del header

1. Señalar las capas del calendario: tareas por curso, entregables, expiraciones en rojo, eventos personales con **drag-and-drop**.
2. Panel lateral: **racha de estudio**, **Smart Slots** ("💡 Estudia X el martes a las 18:00"), **Skills Radar** de Chart.js.
3. Pulsar la **campana del header** → panel de notificaciones con badge (tarea próxima, examen disponible, mensaje nuevo).
   > "Los Smart Slots analizan en qué día y hora estudia más el alumno con strftime sobre sus visualizaciones y proponen un hueco libre. Las notificaciones usan un patrón idempotente: se generan en cada petición pero solo insertan si aún no existen."

---

### 6. Buzón e incidencias — 50 s
**Ir a:** `?url=buzon`

1. Bandeja: mensajes recibidos del staff, los **no leídos arriba**.
2. Abrir un mensaje → queda marcado leído automáticamente. Pulsar **"Responder"** y enviar.
3. Pestaña **"Mis incidencias"** → **"Nueva incidencia"** → crear con asunto y cuerpo. Aparece en estado `abierta`.
   > "Dos canales: mensajes con hilos (la respuesta hereda el hilo_id del padre) e incidencias formales tipo ticket con estados abierta → en_proceso → cerrada. Todo el API es JSON con prepared statements."

---

### 7. CRM — 1:20
**Cambiar a Pestaña 2:** `localhost:8000/admin/`

1. **Dashboard:** KPIs (usuarios registrados, cursos activos, campañas vigentes, incidencias abiertas), gráfico de registros últimos 6 meses, top cursos.
2. **Usuarios** (`?sec=usuarios`): búsqueda, filtro de rol, **modal de edición** (cambio de rol, reset de contraseña). Solo administrador accede.
3. **Editor de curso** (`?sec=editor&id=11`): árbol de unidades/lecciones reordenable por drag-and-drop, imagen, examen test con preguntas, tareas del práctico.
4. **Comunicación** (`?sec=comunicacion`): abrir la incidencia que acaba de crear el alumno → responderla → cambiar estado a `en_proceso`.
5. **Campañas** (`?sec=campanas`): descuentos y fechas activos, detección automática de conflictos.
6. **Logs** (`?sec=logs`): tabla de auditoría paginada, todo lo que hace el staff registrado.
7. **Volver a Pestaña 1** → abrir la incidencia del alumno → **la respuesta del staff ya aparece**.
   > "CRM independiente en /admin/ con tres niveles de staff. Toda acción queda registrada en crm_actividad. El backend expone una sola API JSON con unas 45 acciones discriminadas por ?action=, lo que mantiene el frontend en JavaScript vanilla sin ningún framework."

---

### 8. Cierre — 20 s

> "En resumen: PHP MVC manual sobre SQLite, autenticación bcrypt, Stripe, mensajería con hilos e incidencias tipo ticket, calendario inteligente con heurística de patrones, exámenes en dos modalidades y certificación automática, y un CRM completo para el staff. Todo el ciclo del alumno y del personal, implementado y funcionando. Gracias."

---

## Plan B — si algo falla en vivo
- **Stripe no carga:** "En producción funciona" → saltar a Mis Cursos y continuar.
- **Sin internet:** todo funciona offline salvo el embed de YouTube en lecciones.
- **Corto de tiempo:** sacrificar la sección 5 (calendario) antes que la 7 (CRM) — el CRM es lo más diferenciador.
