# POR AQUÍ — Continuación de la demo (MatrixCoders TFG)

Fecha: 13/05/2026  
Cuenta alumno demo: usuario@usuario.es / Qm6!Usr#3vNbG7sR  
Cuenta admin demo:  isidoro@admin.com  / Hd5*Adm!8jXwR4nQ  
Servidor: ejecutar serve.bat y luego ngrok si hace falta  

---

## LO QUE YA ESTÁ COMPROBADO Y FUNCIONA

- ✅ Sección 0 — Pitch inicial (home)
- ✅ Sección 1 — Registro y login del alumno
- ✅ Sección 2 — Catálogo, carrito, Stripe Checkout (imagen aparece), pago simulado
- ✅ Sección 3 — Dashboard, Mis Cursos, reproductor de lección con sidebar y recursos
- ✅ Sección 4 — Examen tipo test (pantalla bloqueada, 2 intentos, No aprobado, Aprobado con desglose), certificado con código único A6C3DC4DCA03
  - NOTA: El botón "Descargar PDF" abre el diálogo de impresión del navegador — es correcto, no hay que arreglar nada.
  - NOTA: Antes de la presentación real resetear el examen con este script:
    php _tmp_reset_exam_DEMO.php  (ver abajo cómo crearlo)

---

## PENDIENTE — SEGUIR DESDE AQUÍ

### SECCIÓN 5 — Calendario inteligente y notificaciones (45 s)

**URL:** `localhost:8000/index.php?url=calendario`

Pasos:
1. Abre el calendario. Señala las capas: tareas por curso, entregables, expiraciones en rojo, eventos personales con drag-and-drop.
2. Mira el panel lateral: racha de estudio, Smart Slots ("💡 Estudia X el martes a las 18:00") y Skills Radar de Chart.js.
3. Cierra el calendario, pulsa la **campana del header** (tiene badge rojo con número). Deben aparecer notificaciones automáticas.

**Comprueba:** ¿Aparecen eventos en el calendario? ¿Funcionan los Smart Slots? ¿La campana abre el panel de notificaciones?

---

### SECCIÓN 6 — Buzón e incidencias (1:00)

**URL:** `localhost:8000/index.php?url=buzon`

Pasos:
1. Abre el buzón — bandeja con mensajes recibidos del staff, los no leídos arriba.
2. Abre un mensaje — queda marcado como leído. Pulsa "Responder" y envía respuesta.
3. Cambia a la pestaña "Mis incidencias" → "Nueva incidencia" → créala con asunto y cuerpo cualquiera.
4. Ábrela y muestra el estado "abierta" y el historial de respuestas.

**Comprueba:** ¿Se marca como leído al abrir? ¿Se puede responder? ¿La incidencia aparece como "abierta"?

---

### SECCIÓN 7 — CRM (1:15)

**Cambia a la Pestaña 2: `localhost:8000/index.php?url=admin` o `localhost:8000/admin/`**  
Login: isidoro@admin.com / Hd5*Adm!8jXwR4nQ

Pasos:
1. **Dashboard CRM** — KPIs (usuarios, cursos, campañas, matrículas, incidencias abiertas), gráfico de registros, top cursos.
2. **Usuarios** (`?sec=usuarios`) — lista con búsqueda, filtro de rol, modal de edición de usuario.
3. **Editor de curso** (`?sec=editor&id=11`) — árbol de unidades/lecciones reordenables, imagen del curso, examen test, tareas prácticas.
4. **Comunicación / Incidencias** (`?sec=comunicacion`) — abrir la incidencia que creaste como alumno → responderla desde el CRM → cambiar estado a "en_proceso".
5. **Campañas** (`?sec=campanas`) — listado con descuentos y fechas, detección de conflictos.
6. **Logs** (`?sec=logs`) — tabla de auditoría paginada.
7. Vuelve a la **pestaña del alumno** → abre la incidencia → la respuesta del staff ya aparece.

**Comprueba cada paso y manda captura si algo falla.**

---

### SECCIÓN 8 — Cierre (15 s)

Solo leer el texto del guion. No hay nada que comprobar técnicamente.

---

## CÓMO RESETEAR EL EXAMEN ANTES DE LA PRESENTACIÓN

Crea el archivo `_tmp_reset_exam_DEMO.php` con este contenido y ejecútalo con:
`C:\xampp\php\php.exe _tmp_reset_exam_DEMO.php`

```php
<?php
$db = new PDO('sqlite:app/data/database.sqlite');
$u = $db->query("SELECT id FROM usuario WHERE email='usuario@usuario.es'")->fetch();
$uid = $u['id'];
$ex = $db->query("SELECT id FROM examen WHERE curso_id=11")->fetch();
$db->prepare("DELETE FROM resultado_examen WHERE usuario_id=? AND examen_id=?")->execute([$uid, $ex['id']]);
$db->prepare("UPDATE matricula SET estado='activa' WHERE usuario_id=? AND curso_id=11")->execute([$uid]);
echo "Examen reseteado. 2 intentos disponibles.\n";
```

Borra el archivo después de ejecutarlo.

---

## NOTAS SUELTAS IMPORTANTES

- El botón "🎓 Ir al Examen →" (dorado) solo aparece en la **última lección** del curso (la que no tiene "Siguiente →"). En Bases de datos es la lección "Vistas - Final del curso".
- Las imágenes de los cursos son SVG en `public/img/cursos/` — todos los cursos tienen ahora SVG propio.
- Stripe solo funciona si hay `.env` con STRIPE_SECRET_KEY. Si no hay .env el pago es simulado (matricula directa).
- El servidor PHP se levanta con `serve.bat`. ngrok con `ngrok http 8000`.
- Para la demo usar zoom 110-125% en el navegador.
