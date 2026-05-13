# Documentación técnica — MatrixCoders

Plataforma web pensada para estudiantes de DAW: catálogo de cursos, lecciones, exámenes (tipo test y prácticos), entrega de tareas, nube de documentos, calendario, chatbot, notificaciones, mensajería interna, sistema de suscripciones con Stripe y panel CRM para administradores y moderadores.

Este documento describe el flujo completo del programa y cómo están implementadas las tecnologías que lo componen. Se ha dividido en bloques para que sea posible verificar y completar cada sección de forma independiente.

> **Stack resumido**
> - **Backend:** PHP (sin framework, MVC propio) sobre XAMPP.
> - **Base de datos:** SQLite (archivo `app/data/database.sqlite`) con acceso vía PDO.
> - **Frontend:** HTML5, CSS3 (varios módulos separados en `public/css/`), JavaScript vanilla, Bootstrap.
> - **Pagos:** Stripe (`stripe/stripe-php` 13.0) para cursos sueltos y suscripciones.
> - **IA:** Google Gemini para apuntes asistidos; integración con Google OAuth (NotebookLM).
> - **Configuración:** variables de entorno con `vlucas/phpdotenv`.

## Índice de bloques

1. **Bloque 1 — Estructura del proyecto, front controller y base de datos.**
2. **Bloque 2 — Autenticación y registro.**
3. **Bloque 3 — Dashboard, documentos y nube.**
4. **Bloque 4 — Cursos: catálogo, detalle, lecciones y exámenes.**
5. **Bloque 5 — Carrito, pagos con Stripe y suscripciones.**
6. **Bloque 6 — Calendario, eventos, notificaciones y buzón.**
7. **Bloque 7 — Perfil, ajustes, chatbot, apuntes IA, vinculación Google, búsqueda.**
8. **Bloque 8 — CRM y panel de administración.**
9. **Bloque 9 — Modelos, helpers y esquema de base de datos.**
10. **Bloque 10 — Frontend: CSS, JS y vistas compartidas.**
11. **Bloque 11 — Tecnologías externas y dependencias.**

---

## Bloque 1 — Estructura del proyecto, front controller y base de datos

### 1.1 Árbol de carpetas

```
MatrixCoders/
├── admin/                    Entrada del panel CRM (login admin).
│   └── index.php
├── app/                      Código de la aplicación (MVC).
│   ├── config.php            Carga de .env y constantes globales (BASE_URL, claves API).
│   ├── db.php                Clase Database — conexión PDO a SQLite + migraciones.
│   ├── controllers/          Un controlador por área funcional.
│   ├── models/               Modelos de dominio (Curso, Tarea, Mensaje, Documento, etc.).
│   ├── views/                Vistas PHP organizadas por área (auth, dashboard, cursos, crm…).
│   ├── helpers/              Helpers compartidos (p. ej. curso_imagen.php).
│   └── data/                 SQLite + scripts SQL (init.sql, migrate.sql, crm_migrate.sql).
├── public/                   Document root público.
│   ├── index.php             Front Controller (única puerta de entrada HTTP).
│   ├── css/                  Hojas de estilo modulares por sección.
│   ├── js/                   JavaScript del CRM y otros widgets.
│   ├── img/                  Imágenes estáticas (iconos, miniaturas de cursos).
│   └── uploads/              Documentos subidos por usuarios.
├── composer.json/.lock       Dependencias PHP (Stripe, phpdotenv).
├── .env.example              Plantilla de variables de entorno.
└── README.md
```

La separación es la clásica de un proyecto PHP con MVC manual: el directorio `public/` es el único expuesto al servidor web, y todo `app/` queda fuera del document root para que no pueda servirse por HTTP. La base de datos vive en `app/data/` precisamente por la misma razón.

### 1.2 Punto de entrada — `public/index.php`

`public/index.php` actúa como **Front Controller**. Toda petición HTTP entra por aquí (gracias a la URL `BASE_URL = /matrixcoders/public`) y se enruta mediante un `switch` sobre el parámetro `?url=`:

```
/matrixcoders/public/index.php?url=login        → AuthController::loginForm()
/matrixcoders/public/index.php?url=doLogin      → AuthController::login()
/matrixcoders/public/index.php?url=dashboard    → DashboardController::index()
/matrixcoders/public/index.php?url=carrito      → CarritoController::index()
/matrixcoders/public/index.php?url=stripe-webhook → CarritoController::webhook()
/matrixcoders/public/index.php?url=crm          → CrmController::index()
/matrixcoders/public/index.php (sin url)        → CursoController::index()  (home)
```

**Flujo en cada petición** ([public/index.php](public/index.php)):

1. `require_once '../app/config.php'` carga `.env` con un mini-parser propio, define `BASE_URL` y las claves `GEMINI_API_KEY` / `GOOGLE_CLIENT_ID`.
2. `session_start()` arranca o reanuda la sesión PHP, donde se almacena el usuario autenticado (`$_SESSION['usuario_id']`, `rol`, `nombre`, etc.) y datos volátiles como el carrito.
3. Lectura de `$_GET['url']` (ruta lógica).
4. `switch ($url)` con ~50 rutas; cada caso hace `require_once` del controlador correspondiente, instancia la clase y llama al método. Algunos controladores (los que terminan en *página completa*) se ejecutan en el propio `require_once` (su archivo contiene código top-level), por ejemplo `LeccionController`, `ExamenController`, `CalendarioController`, `NotificacionController`, etc. Esa es la razón de que en ciertos `case` no haya `new Controller`.
5. La ruta `default` muestra la home pública (catálogo de cursos vía `CursoController::index()`).

**Rutas especiales destacadas:**

- `case 'buzon'`: instancia el controlador inyectando la conexión PDO y `$_SESSION`, único caso con inyección manual de dependencias en el front controller.
- `case 'upgrade'`: la única ruta cuya vista (`app/views/upgrade/index.php`) se incluye directamente sin pasar por un controlador; protege la sesión y redirige a `login` si no hay usuario.
- `case 'crm-logout'`: destruye la sesión completa y la cookie, y redirige a `/matrixcoders/admin/` (panel CRM).
- `case 'stripe-webhook'`: endpoint público sin sesión, donde Stripe entrega los eventos firmados.

### 1.3 Configuración — `app/config.php`

[app/config.php](app/config.php) hace tres cosas:

1. **Carga manual de `.env`**: una IIFE recorre `.env` línea a línea, ignora comentarios y líneas sin `=`, y publica cada par como variable de entorno con `putenv()`. Aunque `vlucas/phpdotenv` está en `composer.json`, el código actual usa este parser ligero propio. Las variables resultantes se leen luego en los controladores que las necesitan (Stripe, Gemini, OAuth…) con `getenv('CLAVE')`.
2. **`BASE_URL`**: constante con el prefijo de URL de la aplicación (`/matrixcoders/public`). Toda redirección, formulario y enlace de la app se compone como `BASE_URL . '/index.php?url=...'`.
3. **Claves de integraciones**: `GEMINI_API_KEY` (Google AI Studio, para apuntes IA) y `GOOGLE_CLIENT_ID` (Google OAuth, para vinculación con NotebookLM). En el repo están vacías; deben rellenarse en local.

`.env.example` describe el resto de variables esperadas (URLs Stripe, secretos webhooks, etc., que se documentan en el Bloque 5).

### 1.4 Capa de datos — `app/db.php`

[app/db.php](app/db.php) define la clase `Database` con un único método `connect(): PDO`. Es la **fuente única de la conexión**: todos los controladores y modelos hacen `new Database()` y `connect()` para obtener su PDO.

Características relevantes:

- **Motor: SQLite embebido**, archivo `app/data/database.sqlite`. Esto evita configurar MySQL/MariaDB y permite que el proyecto funcione "out of the box" con XAMPP. (El README aún menciona phpMyAdmin/MariaDB, pero el código real usa SQLite.)
- **Modo `ERRMODE_EXCEPTION`** en PDO: cualquier error SQL lanza excepción.
- **`PRAGMA foreign_keys = ON`** en cada conexión, porque SQLite ignora claves foráneas por defecto.
- **`PRAGMA journal_mode = WAL`** para mejor concurrencia (lectores no bloquean al escritor).
- **Bootstrap automático del esquema:** si `database.sqlite` no existe al conectar, se ejecuta `app/data/init.sql` para crear todas las tablas y datos de ejemplo. Si ya existe, se aplica `migrate.sql` y `crm_migrate.sql` en cada conexión.
- **Migraciones progresivas in-line:** un array de `ALTER TABLE … ADD COLUMN …` envueltos en `try/catch` añade columnas nuevas a tablas existentes (SQLite no soporta `ADD COLUMN IF NOT EXISTS`, así que se ignora silenciosamente el error de columna duplicada). Esto cubre añadidos posteriores: `intentos` en `resultado_examen`, `estado` en `matricula`, `url_accion`/`ref_id` en `notificacion`, `tipo`/`fecha_entrega`/`modo_entrega` en `examen`, `reply_to_id`/`hilo_id` en `mensaje`, campos de incidencia, y un conjunto amplio de campos extendidos en `usuario` (`tipo_persona`, `areas_interes`, `tecnologias`, `github`, `objetivo`, `nivel_experiencia`, `frecuencia_estudio`, `ultimo_estudio`, `tipo_curso_preferido`).
- **Migraciones tipo "rebuild" controladas por tabla-flag:**
  - `_mig_examen_tipo`: recrea `examen` cambiando `UNIQUE(curso_id)` por `UNIQUE(curso_id, tipo)`. Necesario para que un mismo curso pueda tener examen tipo *test* y examen *practico* simultáneamente. Desactiva temporalmente las FK con `PRAGMA foreign_keys = OFF` para que el `DROP TABLE` no propague a `resultado_examen`.
  - `_mig_notif_v2`: recrea `notificacion` eliminando el `CHECK` antiguo que restringía los tipos permitidos (insuficiente para los nuevos tipos del sistema de exámenes), añadiendo `url_accion` y `ref_id`. Mismo patrón de tabla-marca para que sólo corra una vez.
- **Roles avanzados:** añade `es_superadmin` y `es_moderador` en `usuario`, e inserta (con `INSERT OR IGNORE`) un superadmin fijo `isidoro@admin.com` con su hash bcrypt para no perder el acceso al CRM nunca.

Esto convierte a `db.php` no solo en una factoría de conexión, sino en un **migrador idempotente** que se ejecuta en cada arranque y mantiene el esquema al día sin necesidad de un comando aparte.

### 1.5 Diagrama general de una petición

```
Navegador
   │  GET /matrixcoders/public/index.php?url=dashboard
   ▼
public/index.php  ── require ──▶ app/config.php  (env + BASE_URL)
   │
   │  session_start();
   │  switch ($_GET['url'])
   ▼
app/controllers/DashboardController.php
   │  new Database()->connect()  ──▶ app/db.php  (PDO + migraciones)
   │  $modelo = new Tarea(...); $modelo->listar()
   │  require '.../views/dashboard/index.php'
   ▼
app/views/layout/header.php + dashboard/index.php + layout/footer.php
   ▼
HTML al navegador
```

Este es el patrón que se repite en prácticamente todas las rutas: **Front Controller → Controller → Model (PDO) → View**. Los siguientes bloques desglosarán cada área funcional siguiendo este mismo esquema.

---

## Bloque 2 — Autenticación y registro

Esta área cubre cómo un visitante se convierte en usuario y cómo se identifica en cada petición posterior. Implicados:

- Controladores: [AuthController](app/controllers/AuthController.php), [RegisterController](app/controllers/RegisterController.php).
- Vistas: [login.php](app/views/auth/login.php), [register.php](app/views/auth/register.php).
- Rutas (en [public/index.php](public/index.php)): `login`, `doLogin`, `logout`, `register`, `doRegister`.
- Tabla de BD principal: `usuario` (más campos extendidos añadidos por las migraciones del Bloque 1).

### 2.1 Modelo de identidad y roles

No existe una clase `Usuario.php` aparte: los controladores trabajan directamente con la tabla `usuario` vía PDO. Los campos relevantes son:

- `id`, `nombre`, `email`, `contraseña` (hash bcrypt vía `password_hash`).
- `rol` con valores `USUARIO` (alumno), `MODERADOR`, `ADMINISTRADOR`.
- `plan` (`gratuito`, `premium`, etc., usado por `SuscripcionController`).
- `es_superadmin`, `es_moderador` (flags numéricos añadidos por las migraciones del Bloque 1, complementan a `rol` para el CRM).
- Perfil extendido recogido en el registro: `tipo_persona`, `areas_interes`, `tecnologias`, `github`, `objetivo`, `nivel_experiencia`, `frecuencia_estudio`, `ultimo_estudio`, `tipo_curso_preferido`.

La identidad en runtime se guarda en `$_SESSION`:

- `$_SESSION['usuario_id']` — identificador autoritativo para todas las consultas.
- `$_SESSION['usuario_nombre']` — usado para mostrar nombre en la UI.
- `$_SESSION['usuario_plan']` — gating de funcionalidades premium.
- `$_SESSION['usuario_rol']` — discriminador para acceso a CRM.

> Nota: el nombre de la columna `contraseña` lleva acento (carácter no ASCII). Los `INSERT`/`SELECT` la entrecomillan correctamente, pero hay que tenerlo presente al añadir nuevas consultas.

### 2.2 Flujo de login

Rutas: `GET ?url=login` muestra el formulario, `POST ?url=doLogin` lo procesa.

`AuthController::loginForm()` ([app/controllers/AuthController.php:21](app/controllers/AuthController.php)) simplemente establece `$pageTitle` e incluye `app/views/auth/login.php`.

La vista [login.php](app/views/auth/login.php):

- Carga Bootstrap 5.3.2 desde CDN y los CSS locales `header.css`, `footer.css`, `auth.css`.
- Incluye los parciales compartidos `layout/header.php` y `layout/footer.php`.
- Lee y consume *flash messages* de sesión:
  - `$_SESSION['login_error']` — error del intento anterior (lo borra con `unset`).
  - `$_SESSION['register_ok']` — confirmación de que la cuenta acaba de crearse.
- Renderiza un `<form method="POST" action="…?url=doLogin">` con campos `email` y `password`.
- Incluye un botón "Login with Google" maquetado pero **sin lógica conectada** (no envía nada): la vinculación con Google está implementada como integración aparte (NotebookLM) en `VincularGoogleController`, no como provider OAuth de login.
- Enlace al registro y al panel admin (`/matrixcoders/admin/`).

`AuthController::login()` ([app/controllers/AuthController.php:37](app/controllers/AuthController.php)) ejecuta:

1. `session_start()` defensivo.
2. Sanitiza `email` y `password` con `trim()` desde `$_POST`.
3. Abre conexión PDO con `new Database()->connect()` (lo que dispara también todas las migraciones del Bloque 1).
4. **Consulta preparada** `SELECT * FROM usuario WHERE email = ? LIMIT 1` — uso de prepared statements para evitar SQL injection.
5. Verifica con `password_verify($pass, $user['contraseña'])` que el hash bcrypt coincide. Si falla (o no existe el usuario), guarda `$_SESSION['login_error']` y redirige a `?url=login`.
6. Si tiene éxito, vuelca los campos clave en `$_SESSION` (id, nombre, plan, rol).
7. **Bifurcación por rol:** si `rol !== 'USUARIO'` (es decir, MODERADOR o ADMINISTRADOR), redirige directamente al CRM (`?url=crm`) marcando `$_SESSION['crm_bienvenida'] = true`. Los alumnos van al `dashboard`. El portal de aprendizaje y el CRM son experiencias mutuamente excluyentes.

### 2.3 Flujo de registro

Rutas: `GET ?url=register` muestra el formulario; `POST ?url=doRegister` lo procesa.

[register.php](app/views/auth/register.php) es bastante más rico que el login:

- Layout de dos columnas (`auth-grid`): datos básicos a la izquierda, "perfil de aprendizaje" a la derecha.
- Reutilización de Bootstrap + `auth.css` y los parciales `header`/`footer`.
- Tres helpers locales (`reg_val`, `reg_sel`, `reg_radio`) que repueblan el formulario con `$_SESSION['register_old']` para no perder lo escrito cuando hay un error.
- Banner de error `reg-error-banner` con SVG de aviso que se muestra solo si llega `$_SESSION['register_error']`.
- Campos extendidos (todos opcionales): `tipo_persona`, `areas_interes`, `tecnologias`, `github`, `objetivo`, `nivel_experiencia`, `frecuencia_estudio`, `ultimo_estudio`, `tipo_curso_preferido`.

`RegisterController::register()` ([app/controllers/RegisterController.php:13](app/controllers/RegisterController.php)) hace validación en cadena: si cualquier paso falla, llama a `errorRedirigir()` que guarda el mensaje + el `$_POST` completo en sesión y redirige a `?url=register` (`never`-return).

Validaciones realizadas:

1. Campos obligatorios no vacíos (`nombre`, `email`, `password`, `password2`).
2. Email con formato válido (`filter_var(..., FILTER_VALIDATE_EMAIL)`).
3. Contraseña de al menos 6 caracteres.
4. Confirmación coincide con la contraseña.
5. Email no existe ya en `usuario` (consulta preparada).
6. **Whitelisting** de cada campo extendido contra arrays de valores válidos (`$planesValidos`, `$nivelesValidos`, `$frecuenciasValidas`, `$estudiosValidos`, `$tiposCursoValidos`) — defensa contra valores arbitrarios.
7. Strings libres limitadas a 255 caracteres con `substr(trim(...), 0, 255) ?: null`.

Si pasa todo, hashea la contraseña con `password_hash($pass, PASSWORD_DEFAULT)` (bcrypt por defecto en PHP actual), inserta el usuario con `INSERT INTO usuario (...)` parametrizado, marca `$_SESSION['register_ok']` y redirige a `?url=login`. El registro no auto-loga: el alumno debe iniciar sesión explícitamente, lo que sirve también como verificación implícita de que recuerda la contraseña que acaba de crear.

### 2.4 Cierre de sesión

`AuthController::logout()` ([app/controllers/AuthController.php:90](app/controllers/AuthController.php)) es minimalista: `session_destroy()` y redirección a `BASE_URL/index.php` (la home pública). No invalida la cookie de sesión a nivel HTTP (a diferencia de `crm-logout`, que sí lo hace). Es suficiente para los usuarios estándar porque al perder la sesión PHP las páginas autenticadas redirigen al login.

El logout administrativo (`?url=crm-logout`, en el propio [public/index.php](public/index.php)) es más estricto: vacía `$_SESSION`, borra la cookie de sesión usando `session_get_cookie_params()` + `setcookie(...) ` con timestamp en el pasado, y luego `session_destroy()`. Se usa cuando un admin o moderador termina su jornada en el CRM.

### 2.5 Flash messages: contrato

El módulo de autenticación define el patrón de *flash messages* que se reutiliza en todo el proyecto. Cada controlador escribe en una clave de `$_SESSION`, la vista la lee y la borra con `unset` para que solo aparezca una vez:

| Clave                        | Origen                            | Consumo            |
|------------------------------|-----------------------------------|--------------------|
| `login_error`                | `AuthController::login()`         | `login.php`        |
| `register_ok`                | `RegisterController::register()`  | `login.php`        |
| `register_error`             | `RegisterController::errorRedirigir()` | `register.php` |
| `register_old`               | `RegisterController::errorRedirigir()` | `register.php` (repuebla formulario) |
| `crm_bienvenida`             | `AuthController::login()`         | Vista del CRM      |

### 2.6 Aspectos de seguridad y observaciones

- **Hashing seguro:** `password_hash` + `password_verify` (bcrypt). Se acepta cualquier hash válido — al cambiar a Argon2 más adelante no haría falta migrar datos manualmente.
- **Prepared statements** en todas las consultas que reciben input del usuario (login, búsqueda de email duplicado, insert).
- **Escape de salida** en las vistas con `htmlspecialchars` para los mensajes flash y los valores repueblados.
- **Sin tokens CSRF** en los formularios de login/registro. Es una superficie aceptable para la entrega del proyecto pero conviene tenerlo presente si se llevase a producción real.
- **No hay rate limiting** ni bloqueo tras N intentos fallidos.
- **Recuperación de contraseña no implementada**: el enlace `¿Has olvidado la contraseña?` en `login.php` apunta a `#`.
- **"Login with Google"** maquetado, sin lógica. La integración Google del proyecto (Bloque 7) cubre la vinculación de cuenta para NotebookLM, no el SSO.
- **Superadmin garantizado:** en cada arranque, `Database::connect()` reinsertar el usuario `isidoro@admin.com` con `INSERT OR IGNORE`, lo que asegura que siempre exista al menos una vía de acceso al CRM aunque se haya manipulado la BD.

### 2.7 Diagrama del flujo

```
[Visitante] ── GET ?url=register ──▶ register.php
              POST ?url=doRegister ─▶ RegisterController::register()
                                       │ valida + INSERT
                                       └─▶ flash register_ok + redirect ?url=login

[Visitante] ── GET ?url=login    ──▶ login.php
              POST ?url=doLogin  ──▶ AuthController::login()
                                       │ password_verify
                                       │ vuelca $_SESSION (id, nombre, plan, rol)
                                       ├─▶ rol == USUARIO   → ?url=dashboard
                                       └─▶ rol != USUARIO   → ?url=crm

[Usuario] ──── GET ?url=logout    ─▶ AuthController::logout()  → session_destroy → home
[Admin]   ──── GET ?url=crm-logout ▶ borra cookie + session_destroy → /matrixcoders/admin/
```

---

## Bloque 3 — Dashboard, documentos y nube

El "espacio de trabajo" del alumno. Una vez logueado (`rol = USUARIO`), todas las rutas autenticadas del portal cuelgan de aquí. Implicados:

- Controlador: [DashboardController](app/controllers/DashboardController.php) (un solo controlador con 6 métodos públicos).
- Modelos: [Documento](app/models/Documento.php), [Carpeta](app/models/Carpeta.php), [Tarea](app/models/Tarea.php).
- Vistas: [dashboard/index.php](app/views/dashboard/index.php), [documentos.php](app/views/dashboard/documentos.php), [tareas.php](app/views/dashboard/tareas.php), [ver_documento.php](app/views/dashboard/ver_documento.php), [documento_compartido.php](app/views/dashboard/documento_compartido.php).
- Rutas: `dashboard`, `mis-documentos`, `nube`, `nube-api`, `documento`, `documento-compartido`, `tareas`.
- Tablas BD: `documento`, `carpeta`, `matricula`, `curso`, `leccion`, `leccion_vista`, `unidad`, `tarea`, `mensaje`, `suscripcion`.
- Almacenamiento físico: `public/uploads/documentos/`.

### 3.1 Guarda de acceso

Todos los métodos del controlador comienzan con el mismo guard:

```php
if (empty($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/index.php?url=login");
    exit;
}
```

`index()` añade una segunda comprobación: si el rol no es `USUARIO`, redirige a `?url=crm`. Es la pareja de la bifurcación que hace `AuthController::login()` — un moderador o admin nunca aterriza en el dashboard de alumno aunque manipule la URL.

`documentoCompartido()` es la única ruta que **no requiere sesión**: en su lugar valida un token HMAC-like construido por el servidor (ver §3.6). `nubeApi()` exige sesión pero responde con JSON en lugar de redirigir.

### 3.2 `index()` — composición del panel principal

`DashboardController::index()` ([app/controllers/DashboardController.php:29](app/controllers/DashboardController.php)) es el método más cargado de la app. Reúne **un montón de datos heterogéneos** en una sola petición para alimentar la vista:

1. **Plan de suscripción en sesión:** si `$_SESSION['usuario_plan']` está vacío, consulta `suscripcion WHERE usuario_id = ? AND status = 'activa'` y lo cachea. Esto evita una consulta por cada vista que necesite saber si el alumno es premium.
2. **Parámetros del mes del calendario:** lee `?y=` y `?m=` (con valores por defecto al mes actual) y los clamp-ea a `[2000-2100]` × `[1-12]` para que no se puedan generar fechas absurdas.
3. **Documentos:** `Documento::obtenerConCarpetaPorUsuario($uid)` y se queda con los 4 más recientes para el widget.
4. **Tareas:** `Tarea::obtenerPorUsuario($uid)` y `Tarea::obtenerDiasConEventos($uid, $year, $month)` para pintar puntos en los días del calendario.
5. **Cursos en progreso (consulta clave):** un único `SELECT` con dos subconsultas correlacionadas que para cada matrícula `activa` devuelve:
   - `total_lecciones` (lecciones del curso vía `leccion → unidad → curso`).
   - `lecciones_vistas` (lecciones marcadas en `leccion_vista` por el usuario).
   - `ultima_leccion_id` (la última `leccion_vista.visto_at` del usuario en ese curso, para el botón "Continuar").
   Luego en PHP calcula el porcentaje (`progreso = round(vistos/total*100)`) y, si no hay `ultima_leccion_id`, busca la primera lección del curso ordenada por `unidad.orden`, `leccion.orden` para empezar desde el principio.
6. **Perfil profesional inferido:** una pieza singular del dashboard. A partir de los títulos/descripciones/categorías de los cursos en progreso, calcula:
   - `catFreq`: frecuencia de categorías. `arsort()` para ordenar.
   - `nivelFreq`: frecuencia de niveles (principiante/intermedio/avanzado).
   - `roleScores`: matchea un diccionario hardcoded `$rolesKeywords` (Frontend / Backend / Full Stack / Data Scientist / DevOps / UX / DBA / Móvil / Videojuegos) contra el texto plano de los cursos. Cada keyword vale 2 puntos por aparición (`substr_count`). El rol con más puntuación es el "perfil sugerido" del alumno.
   - Variables finales: `$perfilRol`, `$perfilTopCats`, `$perfilNivel`, `$perfilCursos`.
   No es ML — es heurística textual barata pero suficiente para mostrar al alumno una "etiqueta" de orientación.
7. **Mensajería en widget:** cuenta `mensajes WHERE receptor_id = ? AND leido = 0` y trae los 4 últimos ordenados primero por no-leídos y después por fecha descendente, para el bloque de "Buzón" del dashboard.
8. **Flash** `dashboard_flash` (estructura `{type, message}`, ver §3.7) y `pageTitle`.
9. `require __DIR__ . '/../views/dashboard/index.php'` — la vista recibe todas estas variables ya preparadas y se limita a renderizar HTML (calendario, sidebar, widgets de cursos, documentos, mensajes y perfil).

### 3.3 `documentos()` / `nube` — listado de documentos y carpetas

Misma ruta servida por dos alias (`mis-documentos` y `nube`). La vista [documentos.php](app/views/dashboard/documentos.php) es la "nube personal" del alumno.

Acción soportada vía POST clásico (resto de operaciones van por la API JSON, ver §3.5):

- `dashboard_action=create_folder` con `folder_name`: valida que no esté vacío, llama a `Carpeta::crear($uid, $nombre)` y deja un flash success/error. Tras procesar el POST hace `Location: ?url=mis-documentos` (patrón Post/Redirect/Get).

GET normal: trae las carpetas con conteos (`Carpeta::obtenerConTotalesPorUsuario`) y todos los documentos del usuario con el nombre de su carpeta, y renderiza la vista.

### 3.4 `tareas()` — panel de tareas

Trae todas las tareas del usuario con `Tarea::obtenerPanelUsuario($uid)` (devuelve cada tarea con un campo derivado `estado_visual`: `pendiente`, `proxima`, `vencida`, `entregada`). Sobre ese array compone:

- `$resumen` (contadores por estado, para las tarjetas KPI superiores).
- `$tareasPorCurso` (agrupación por la columna `curso`, para mostrar bloques por curso).

Vista: [tareas.php](app/views/dashboard/tareas.php).

### 3.5 `nubeApi()` — endpoint AJAX JSON

Punto de entrada para todas las operaciones de la nube que no son simple navegación: subida de archivo, mover documento, eliminar documento, eliminar carpeta. Diseño:

- `header('Content-Type: application/json')` siempre.
- Si no hay sesión, responde `{ok:false, error:'No autenticado'}` (no redirige).
- Detecta la acción de forma flexible: si la petición es `multipart/form-data` (subida), lee `$_POST['nube_action']`; en otro caso, parsea `php://input` como JSON. Esto permite que el frontend use `FormData` para subir archivos y `fetch` con `body: JSON.stringify(...)` para el resto.
- `switch ($action)` con cuatro casos:

| Acción              | Validaciones                                                                                         | Efecto                                                                                                         |
|---------------------|-----------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------|
| `subir_archivo`     | `$_FILES['archivo']['error'] === UPLOAD_ERR_OK`, ≤ 50 MB, extensión en lista blanca.                  | `move_uploaded_file` a `public/uploads/documentos/u{uid}_{uniqid}.{ext}`, INSERT en `documento` con `contenido` describiendo el archivo. |
| `mover_documento`   | El documento pertenece al usuario.                                                                   | `UPDATE documento SET carpeta_id = ? WHERE id = ? AND usuario_id = ?`.                                          |
| `eliminar_documento`| El documento pertenece al usuario.                                                                   | Parsea la `Ruta del archivo:` del campo `contenido` con regex, hace `@unlink` del fichero físico y `DELETE` del registro. |
| `eliminar_carpeta`  | La carpeta pertenece al usuario.                                                                     | Desasocia los documentos (`UPDATE … SET carpeta_id = NULL`) y luego `DELETE` la carpeta.                       |

Extensiones permitidas: `pdf, doc, docx, zip, rar, txt, png, jpg, jpeg, gif, webp, mp4, mp3, wav, xlsx, pptx, csv`. El nombre original se sanitiza con `preg_replace('/[^a-z0-9_\-\.]/i', '_', basename(...))` y el nombre físico final se basa en `uniqid()` para evitar colisiones y filtrar caracteres peligrosos del filesystem.

**Patrón "el contenido guarda metadata":** en lugar de añadir columnas `ruta`, `mime`, `tamano` a la tabla `documento`, el controlador escribe un string formateado en `contenido`:

```
Archivo original: foo.pdf
Ruta del archivo: /uploads/documentos/u4_658c....pdf
Tipo de archivo: pdf
```

Y luego lo parsea con regex al eliminar. Es una decisión pragmática que evita migraciones de esquema, aunque acopla los apuntes (texto) con los ficheros adjuntos.

### 3.6 `verDocumento()` y `documentoCompartido()` — vista y enlace público

`verDocumento()` ([app/controllers/DashboardController.php:270](app/controllers/DashboardController.php)) abre un documento propio:

- Requiere sesión.
- `Documento::obtenerPorIdYUsuario($id, $uid)` garantiza que solo el dueño puede ver el documento (filtra por `usuario_id`).
- Si no existe, responde con `http_response_code(404)` antes de renderizar la vista, que mostrará un mensaje de "no encontrado".

`documentoCompartido()` ([app/controllers/DashboardController.php:293](app/controllers/DashboardController.php)) — **el único endpoint público para acceder a contenido de usuario**:

- Lee `?id=…&token=…` de la query string.
- Carga el documento sin filtrar por usuario.
- Compara con `hash_equals(buildShareToken($documento), $token)` (timing-safe).
- `buildShareToken` calcula `sha256(id|usuario_id|mc_share_secret)`. Es un secreto **hardcodeado en el código** (`'mc_share_secret'`); como solo se firma con esa constante, cualquiera que tenga acceso al repo puede generar enlaces compartidos para cualquier documento. Es funcional para la entrega académica pero conviene moverlo a `.env` y rotarlo si se publica.

Esto da una URL del estilo `?url=documento-compartido&id=123&token=…` que se puede pegar a alguien sin que tenga cuenta. Renderiza una versión limpia en `documento_compartido.php`.

### 3.7 Flash messages y patrón "Post/Redirect/Get"

El dashboard introduce un flash más estructurado que el de auth:

```php
$_SESSION['dashboard_flash'] = ['type' => 'success'|'error', 'message' => '...'];
```

Se setea con `setFlash()` y se consume + `unset()` en la vista correspondiente. Cada acción POST termina con `header('Location: ...')` + `exit`, así un refresh no reenvía el formulario (PRG).

### 3.8 Modelos asociados

[Documento](app/models/Documento.php) — wrapper sobre la tabla `documento`. Métodos relevantes:

- `obtenerPorUsuario`, `obtenerRecientesPorUsuario(?$limite)`, `obtenerConCarpetaPorUsuario` — joins con `carpeta` para incluir el nombre.
- `obtenerPorId`, `obtenerPorIdYUsuario` (la versión segura, que filtra por dueño).
- `obtenerPorCarpeta`, `crear`, `actualizar`, `eliminar`.

[Carpeta](app/models/Carpeta.php) — wrapper sobre `carpeta`. Soporta **carpetas anidadas** vía `padre_id` (NULL = raíz), aunque en el dashboard actual solo se usan carpetas raíz:

- `obtenerPorUsuario` (solo raíz), `obtenerConTotalesPorUsuario` (raíz con `COUNT(documento)`), `obtenerSubcarpetas($padre)`, `crear`, `eliminar`.

(El modelo `Tarea` se documenta en detalle en el Bloque 4 junto al sistema de exámenes y entregables.)

### 3.9 Diagrama de flujo del dashboard

```
[Alumno autenticado] ── GET ?url=dashboard ──▶ DashboardController::index()
   │
   ├── SELECT suscripcion (si plan no en sesión)
   ├── Documento::obtenerConCarpetaPorUsuario   →  widget "últimos documentos"
   ├── Tarea::obtenerPorUsuario                  →  lista próximas tareas
   ├── Tarea::obtenerDiasConEventos              →  puntos en calendario
   ├── SELECT matrículas + lecciones vistas      →  widget "cursos en progreso"
   ├── Heurística rolesKeywords                  →  "perfil profesional sugerido"
   ├── SELECT mensajes recientes / no leídos     →  widget "buzón"
   └── require views/dashboard/index.php

[Alumno] ── GET  ?url=mis-documentos ──▶ documentos()  → vista nube
[Alumno] ── POST ?url=mis-documentos  ──▶ create_folder → flash → PRG
[Alumno] ── fetch ?url=nube-api (FormData/JSON) ──▶ nubeApi() → JSON
                          ├── subir_archivo      (uploads/documentos/)
                          ├── mover_documento
                          ├── eliminar_documento (+ unlink del fichero)
                          └── eliminar_carpeta   (desasocia + delete)

[Alumno] ── GET ?url=documento&id=N         ──▶ verDocumento()        (ownership)
[Cualquiera] GET ?url=documento-compartido&id=N&token=T
                                            ──▶ documentoCompartido() (sha256 token)
[Alumno] ── GET ?url=tareas                 ──▶ tareas()  → resumen + agrupación
```

---

## Bloque 4 — Cursos: catálogo, detalle, lecciones y exámenes

El núcleo del producto. Va desde el catálogo público en la home hasta el certificado emitido al finalizar el examen práctico, pasando por matriculación, lecciones, notas personales, recursos del instructor, RAG sobre el curso, exámenes test y prácticos, y tareas entregables.

### 4.1 Mapa de controladores, modelos y rutas

| Ruta                   | Controlador                                   | Función principal                                                                 |
|------------------------|------------------------------------------------|------------------------------------------------------------------------------------|
| `(default)`            | [CursoController::index](app/controllers/CursoController.php) | Home pública — cursos destacados.                                                |
| `curso` / `detallecurso`| [DetalleCursoController](app/controllers/DetalleCursoController.php) (script top-level) | Página de detalle del curso, matriculación y temario.                            |
| `leccion`              | [LeccionController](app/controllers/LeccionController.php) (top-level)         | Reproductor de lecciones + AJAX múltiple (vistas, notas, RAG, nube).             |
| `examen`               | [ExamenController](app/controllers/ExamenController.php) (top-level)           | Examen tipo test, intentos, certificado.                                          |
| `examen-practico`      | [ExamenPracticoController](app/controllers/ExamenPracticoController.php) (top-level) | Entregas prácticas, corrección por instructor, certificado final.                |
| `tarea-entregable`     | [TareaEntregableController](app/controllers/TareaEntregableController.php) (top-level) | Entregas por unidad ligadas al temario.                                          |
| `mis-cursos`           | [MisCursosController](app/controllers/MisCursosController.php) | Listado del alumno con filtros (en progreso / completados / sin empezar).        |
| `curso-completado`     | [CursoCompletadoController](app/controllers/CursoCompletadoController.php) (top-level) | Pantalla final con certificado.                                                  |

Modelo principal: [Curso](app/models/Curso.php). Tablas implicadas: `curso`, `unidad`, `leccion`, `leccion_vista`, `leccion_recurso`, `leccion_notebook`, `leccion_apuntes_ia`, `matricula`, `nota`, `tarea`, `tarea_entregable`, `entrega`, `entrega_entregable`, `examen`, `pregunta`, `opcion`, `resultado_examen`, `tarea_practica`, `entrega_practica`, `certificado`, `campana_crm`, `campana_curso`.

### 4.2 Estructura de un curso

Cada curso (`curso`) se descompone en:

- `unidad` (módulos) con `orden` para listarlas.
- `leccion` dentro de cada unidad, también con `orden`. Cada lección tiene `titulo`, `video_url`, `contenido` (texto), etc.
- `tarea_entregable` por unidad: entregas opcionales asociadas a la unidad (no a una lección concreta).
- `examen` por curso, ahora con clave compuesta `UNIQUE(curso_id, tipo)` para permitir un test y un práctico (ver migración `_mig_examen_tipo` del Bloque 1).
- Para examen tipo `test`: tabla `pregunta` con sus `opcion` (una con `correcta=1`).
- Para examen tipo `practico`: tabla `tarea_practica` con N tareas que el alumno entrega, y `entrega_practica` con la respuesta del alumno y la corrección del instructor.

Ese diseño permite navegar el árbol del curso desde un único helper: `Curso::getUnidadesConLecciones($cursoId)` ([app/models/Curso.php:121](app/models/Curso.php)) ya devuelve cada unidad con `lecciones` y `tareas_entregables` anidadas.

### 4.3 Catálogo público — `CursoController::index`

[CursoController::index](app/controllers/CursoController.php) sirve la home pública:

1. Conecta a BD y llama a `Curso::obtenerDestacados(3)`.
2. `obtenerDestacados` ([app/models/Curso.php:62](app/models/Curso.php)) hace un único `SELECT` agregando `COUNT(matriculas)` para ordenar por popularidad y une por subconsulta el `descuento_activo` de la campaña vigente (`campana_curso` × `campana_crm.activa = 1`). Filtra por `COALESCE(c.activo,1)=1`.
3. Si hay sesión `USUARIO`, carga el set `[curso_id => estado]` de las matrículas (activa/completado) para que la vista pueda mostrar "Continuar" en lugar de "Matricularse" en las tarjetas.
4. Renderiza `views/cursos/index.php`.

El modelo `Curso` además expone:

- `buscar($q, $pagina, $porPagina)` y `contarBusqueda($q)` — usadas por `BuscarController` (Bloque 7).
- `sugerencias($q, $limite=6)` — usada por `AutocompleteController`.

### 4.4 Página de detalle y matriculación

[DetalleCursoController](app/controllers/DetalleCursoController.php) **no es una clase**: el archivo se ejecuta como script top-level cuando el front controller hace `require_once` (por eso el `case 'curso'/'detallecurso'` no instancia nada). Esa convención se repite en lección, exámenes, calendario, etc.

Flujo del archivo:

1. `session_start()` defensivo, conexión PDO, lectura de `?id=` (redirige a home si es 0).
2. `Curso::getById($id)` carga el curso (con `COUNT(matriculas)`).
3. **Acción POST `accion=matricular`:**
   - Si no hay sesión, redirige a login.
   - Si la hay, `Curso::matricular($uid, $id)` ejecuta un `INSERT INTO matricula … ON CONFLICT(usuario_id, curso_id) DO UPDATE SET estado='activa'` cuando la matrícula previa estaba `revocada`. Esto soporta la rematriculación tras suspender el examen.
4. Calcula `$estaMatriculado` y, si no, comprueba si tiene matrícula `revocada` (para mostrar el banner "perdiste el acceso").
5. Si hay matrícula activa, calcula `fechaMatricula`, `fechaExpiracion = matrícula + 90 días`, y `diasParaExpirar`. **Cada matrícula tiene caducidad de 90 días**.
6. `$planPermiteAcceso`: regla simple — gratis (`precio<=0`), plan `estudiantes` o `empresas` → acceso. Resto → solo accede si pagó/matriculó.
7. `Curso::getUnidadesConLecciones($id)` — temario completo.
8. Trae el set de tareas entregables ya enviadas por el alumno para marcar visualmente las hechas.
9. `Curso::getTareasByCurso($id)` → para cada tarea recoge la entrega del alumno (si existe), calcula `dias_restantes` y deriva `estado_visual`: `entregada` | `vencida` | `proxima` (≤3 días) | `pendiente`.
10. **Lección activa:** si `?leccion=N` la abre, si no, `Curso::getPrimeraLeccion($id)`.
11. **Descuento de campaña** (similar a `obtenerDestacados`): porcentaje que se resta al precio para mostrar `$precioFinal`.
12. Render `views/detallecurso/index.php`.

### 4.5 Reproductor de lección — `LeccionController`

[LeccionController](app/controllers/LeccionController.php) es uno de los archivos más densos del repo porque concentra muchas operaciones AJAX a través de una sola URL `?url=leccion&id=N`. Cada AJAX se distingue por `$_POST['accion']`.

**Guardas iniciales:**

- Sesión y rol `USUARIO`. Si no hay matrícula `activa` → redirige al detalle del curso. Si está `revocada` → redirige con `&acceso=revocado`.
- Hay un bypass para planes `plan_estudiantes`/`plan_empresas`: `$estaMatriculado = true` sin consultar `matricula`. Es coherente con la regla de acceso del detalle (planes con acceso total al catálogo).

**Acciones POST AJAX (todas responden JSON):**

| `accion`            | Efecto                                                                          |
|---------------------|---------------------------------------------------------------------------------|
| `marcar_vista`      | `INSERT OR IGNORE INTO leccion_vista (...)` para el `leccion_id` indicado.       |
| `desmarcar_vista`   | `DELETE FROM leccion_vista …`.                                                  |
| `marcar_unidad`     | Marca como vistas todas las lecciones de una unidad.                            |
| `desmarcar_unidad`  | Borra las vistas de todas las lecciones de la unidad.                           |
| `rag_chat`          | Chat tipo RAG sobre la lección — ver §4.5.1.                                    |
| `guardar_en_nube`   | Crea un `documento` en la nube del usuario con el recurso (`nombre`, `url`).    |
| (campo `nota`)      | Si el POST trae `nota`, hace `upsert` en la tabla `nota` (notas personales).    |

**Guardado de notas:** `Curso::guardarNota` usa `INSERT … ON CONFLICT(usuario_id, leccion_id) DO UPDATE` (upsert SQLite). Una nota por usuario y lección.

**Marcar visto:** `Curso::marcarVista` y `getLeccionesVistas` proporcionan el modelo de progreso. `getLeccionesVistas` devuelve un *set* (`array_flip` sobre los ids) para que la vista pueda hacer `isset($vistas[$leccionId])` en O(1).

**Navegación entre lecciones:** `Curso::getLeccionAnterior` y `getLeccionSiguiente` ejecutan un SELECT que combina `unidad.orden` y `leccion.orden` para encontrar la lección inmediatamente anterior/siguiente dentro del mismo curso, atravesando las fronteras de unidad.

**Datos cargados para el render:**

- Curso, unidad, lección, anterior, siguiente.
- Lecciones vistas (set), unidades completas para el sidebar.
- Tareas entregables del curso ya entregadas (set).
- Recursos del instructor (`leccion_recurso`): nombre, tipo, ruta/URL, descargable o no. Se consulta dos veces seguidas en el código (duplicado leve).
- URL de NotebookLM asociada (`leccion_notebook.notebook_url`).
- Apuntes IA cacheados (`leccion_apuntes_ia.contenido`).
- Si el curso tiene `examen` test y/o `tarea_practica` para mostrar accesos en el sidebar.
- Último resultado del examen test (`resultado_examen` ordenado por `realizado_en DESC`).

#### 4.5.1 RAG: chat sobre el contenido del curso

La acción `rag_chat` construye un "contexto" textual:

```
Curso: <titulo>
Descripción: <descripcion>
Lección actual: <titulo>

Estructura del curso:
- <unidad>: leccion1 | leccion2 | ...
- ...

Apuntes generados de la lección:
<apuntes IA cacheados, recortados a 2000 chars>
```

Y llama a `GeminiService::preguntaConContexto($pregunta, $contexto)` (helper en `app/helpers/GeminiService.php`, detallado en el Bloque 7). El JSON devuelto por el servicio se vuelca tal cual al cliente. Esto da un chat in-page que responde restringido al material del curso.

### 4.6 Mis cursos — listado del alumno

[MisCursosController::index](app/controllers/MisCursosController.php) trae todas las matrículas (`activa` o `completado`) con un SELECT muy similar al del dashboard pero ordenado por fecha de matriculación. Calcula el estado derivado del curso para cada uno:

- `completado` si la matrícula ya lo está en BD o si todas las lecciones están vistas y (no hay examen o está aprobado).
- `en_progreso` si hay al menos una lección vista.
- `sin_empezar` en otro caso.

Acepta `?filtro=todos|en_progreso|completados|sin_empezar` para filtrar el array antes de renderizar `views/mis-cursos/index.php`.

### 4.7 Tareas entregables (por unidad) — `TareaEntregableController`

[TareaEntregableController](app/controllers/TareaEntregableController.php) gestiona las entregas asociadas a una unidad concreta (`tarea_entregable`).

Guardas: sesión, rol, matrícula `activa`. Si `revocada` → redirige al detalle del curso. **Plazo de 90 días**: si la `matrícula activa` superó esa fecha, se considera plazo vencido salvo que ya haya entregado antes.

POST (JSON):

- Bloquea si el plazo está vencido y no hay entrega previa.
- Bloquea si la entrega ya fue revisada (`revisado=1`).
- Recibe `respuesta` (texto) y/o `archivo` (multipart). Validaciones de extensión y tamaño ≤ 50 MB.
- Mueve el fichero a `public/uploads/entregables/` (o similar — ver código completo para detalles), e inserta/actualiza `entrega_entregable`.

Hace `INSERT OR REPLACE` para permitir reenvíos mientras la entrega no esté revisada.

### 4.8 Examen tipo test — `ExamenController`

[ExamenController](app/controllers/ExamenController.php) es uno de los flujos más críticos y largos. Resumen del control de acceso:

1. Sesión + rol `USUARIO`.
2. Lee `?curso=`. Si no existe, redirige.
3. Comprueba matrícula. Si `revocada` → muestra `views/examen/acceso_perdido.php`.
4. **Gating de progreso:** cuenta total de lecciones del curso y cuántas ha visto el alumno; si faltan, calcula `progresoExamen = round(vistas/total*100)` y muestra `views/examen/bloqueado.php`. Igual para `tarea_entregable` (`total` vs `entregadas`). Solo si **todas las lecciones y todos los entregables están hechos** puede llegar al examen.
5. Carga el `examen` (`tipo='test'`).
6. Si no hay notificación previa "examen teórico disponible", la inserta. Patrón idempotente con `SELECT COUNT … ref_id` para evitar duplicados (se usa en todos los disparos de notificación).
7. Trae `resultadoPrevio` y calcula `intentosUsados`. Constante: `$maxIntentos = 2`.

**Cálculo de nota:** si llega POST y no se ha agotado intentos ni se aprobó, itera todas las preguntas comparando la opción seleccionada (`$_POST['p'.$id]`) con `opcion.correcta`. Nota = `round(correctas/total * 10, 1)`. Aprueba si `nota >= examen.nota_minima`.

**Persistencia:** `INSERT` o `UPDATE … intentos=intentos+1` en `resultado_examen`.

**Consecuencias:**

- **Suspendido y agotó intentos:** `UPDATE matricula SET estado='revocada'` + notificación `curso_fallido`. Para volver a hacerlo tiene que rematricularse (lo que reactiva la matrícula vía el `ON CONFLICT` de `Curso::matricular`).
- **Aprobado sin examen práctico en el curso:** genera certificado con código `strtoupper(substr(md5(uid-curso-microtime), 0, 12))`, `UPDATE matricula SET estado='completado'` y notificación `curso_completado`.
- **Aprobado con examen práctico:** no genera certificado todavía; lanza notificación `examen_practico` con URL `?url=examen-practico&curso=…`.

**Nota final ponderada** (mostrada cuando el práctico ya está corregido):

- Si hay tareas entregables (`mediaEntregables`): `Test 20% + Entregables 30% + Práctico 50%`.
- Si no: `Test 40% + Práctico 60%`.

La vista usa `views/examen/resultado.php` (resultado post-envío y para repeticiones bloqueadas) o pinta el formulario con preguntas + opciones cargadas desde la BD.

### 4.9 Examen práctico — `ExamenPracticoController`

[ExamenPracticoController](app/controllers/ExamenPracticoController.php) — entrega final que sí requiere corrección manual del instructor.

Guardas adicionales:

- Matrícula `activa` o `completado`.
- **Debe haber aprobado el examen teórico** (`resultado_examen.aprobado=1`); si no, redirige al examen con `&pendiente_teoria=1`.
- Debe existir al menos un registro en `tarea_practica` del curso.

Estado: trae la lista de `tarea_practica`, las `entrega_practica` existentes y compone:

- `totalEntregadas`, `todasEntregadas`.
- Si todas están revisadas (`revisado=1`), calcula la media. Compara con `examen.nota_minima`:
  - **Aprobado:** genera certificado (mismo patrón que el test), `matricula = 'completado'`, notificación `curso_completado` (idempotente).
  - **Reprobado:** `matricula = 'revocada'`, notificación `curso_fallido`.

POST: una entrega por tarea (`tarea_id`, `respuesta_texto`, `archivo`). Sube a `public/uploads/practicos/u{uid}_t{tareaId}_{time}.{ext}`. Bloquea si pasó `examenPractico.fecha_entrega`.

### 4.10 Curso completado — `CursoCompletadoController`

[CursoCompletadoController](app/controllers/CursoCompletadoController.php) es la pantalla "tarjeta de fin de curso": comprueba matrícula `activa|completado`, trae el certificado y la nota media del práctico, y renderiza `views/examen/completado.php` con el nombre del alumno.

### 4.11 Sistema de certificados

- Tabla `certificado` con `usuario_id`, `curso_id`, `emitido_en`, `codigo` único.
- Código `MD5(uid-curso-microtime)` truncado a 12 caracteres en mayúsculas — colisiones casi imposibles dado el `microtime()`.
- Se inserta con `INSERT OR IGNORE` para que volver a la página no duplique.
- Hay dos puertas de emisión:
  1. Aprobar el test cuando el curso **no** tiene práctico.
  2. Aprobar la corrección del práctico (media ≥ nota mínima).

### 4.12 Sistema de notificaciones internas (en este bloque)

Cuatro tipos aparecen aquí; el catálogo completo se centraliza en el Bloque 6:

- `examen_teorico` (referencia: `examen.id`).
- `examen_practico` (referencia: `curso.id`).
- `curso_completado` (referencia: `curso.id`).
- `curso_fallido` (referencia: `curso.id`).

Patrón estable en todo el código: antes de insertar se hace `SELECT COUNT(*) FROM notificacion WHERE tipo=? AND ref_id=?`. Si la migración `_mig_notif_v2` no se hubiese hecho, los tipos nuevos como `examen_teorico` violarían el viejo `CHECK`.

### 4.13 Ciclo de vida de la matrícula

`matricula.estado` tiene cuatro valores efectivos:

- `activa` (recién creada o reactivada).
- `completado` (aprobó test sin práctico, o aprobó práctico).
- `revocada` (agotó intentos del test, o suspendió práctico, o no se rematriculó).
- Caducidad implícita: 90 días desde `fecha` para entregables/práctico.

`Curso::matricular` ([app/models/Curso.php:195](app/models/Curso.php)) reactiva una matrícula `revocada` cuando el alumno vuelve a pulsar "Matricularse" (`ON CONFLICT(usuario_id, curso_id) DO UPDATE SET estado='activa', fecha=datetime('now')`), reseteando así su periodo de 90 días.

### 4.14 Diagrama del flujo completo

```
Catálogo               Detalle                    Lección                   Examen test                 Examen práctico              Certificado
─────────              ───────                    ────────                  ──────────                  ───────────────              ───────────
CursoController        DetalleCursoController     LeccionController         ExamenController           ExamenPracticoController     CursoCompletadoController
obtenerDestacados ──▶  POST matricular        ┌▶  marcar_vista/desmarcar  ┌▶ Gating progreso          │ Requiere test aprobado     │
                       ├─ matricula activa? ──┘   guardar nota              completado                │ Sube entrega_practica       │
                       ├─ planPermiteAcceso?      RAG chat (Gemini)         POST envío → resultado    │ Instructor corrige          │
                       └─ getUnidadesConLecciones guardar_en_nube           ├─ aprobado / fallido     │ Aprobado → certificado     │
                                                  marcar_unidad             └─ revoca matricula tras  │ Reprobado → revoca matrícula │
                                                                              max_intentos (2)         └─────────────────────────────┘
                                                                            Si curso sin práctico:
                                                                            cert + matricula completada
                                                                            Si curso con práctico:
                                                                            avisa "examen práctico
                                                                            disponible"
```

---

## Bloque 5 — Carrito, pagos con Stripe y suscripciones

Dos flujos de pago coexisten en el proyecto:

- **Carrito (compra única)**: alumno mete cursos sueltos y paga vía Stripe Checkout en modo `payment`. Cada curso comprado genera una matrícula `activa`.
- **Suscripciones (pago recurrente)**: el alumno contrata un plan (`plan_estudiantes`, `plan_empresas`) que le da acceso ilimitado a todos los cursos del catálogo, vía Stripe Checkout en modo `subscription`.

Ambos flujos usan `stripe/stripe-php` 13.0 (via `vendor/autoload.php`) y son **degradables**: si no hay `STRIPE_SECRET_KEY` configurada en `.env`, hacen una activación simulada localmente (útil para entrega académica sin claves reales).

### 5.1 Mapa de rutas

| Ruta                | Método                                                            | Función                                                                      |
|---------------------|-------------------------------------------------------------------|-------------------------------------------------------------------------------|
| `carrito`           | [CarritoController::index](app/controllers/CarritoController.php) | Vista del carrito con totales (subtotal, descuentos, IVA, total).            |
| `carrito-añadir`    | `CarritoController::añadir` (AJAX JSON)                            | Inserta `curso_id` en `$_SESSION['carrito']`.                                |
| `carrito-eliminar`  | `CarritoController::eliminar` (AJAX JSON)                          | Elimina e informa nuevos totales.                                            |
| `pagar`             | `CarritoController::checkout`                                      | Crea sesión Stripe Checkout `payment` y redirige.                            |
| `pago-ok`           | `CarritoController::pagoOk`                                        | Página de éxito; matricula cursos si `session_id` confirma `paid`.            |
| `stripe-webhook`    | `CarritoController::webhook`                                       | Endpoint que recibe eventos firmados de Stripe.                              |
| `suscripciones`     | [SuscripcionController::index](app/controllers/SuscripcionController.php) | Página comparativa de planes.                                                |
| `pagarSuscripcion`  | `SuscripcionController::iniciarPago`                               | Crea sesión Stripe Checkout `subscription` y redirige.                       |
| `suscripcion-ok`    | `SuscripcionController::pagoOk`                                    | Activa el plan tras volver de Stripe.                                        |
| `doSuscripcion`     | `SuscripcionController::contratar`                                 | Activación directa sin pago (compatibilidad).                                |
| `upgrade`           | (sin controlador, inline en `index.php`)                          | Vista `views/upgrade/index.php` para promocionar planes.                     |

Tablas: `suscripcion (usuario_id, plan, status)`, `matricula`, `campana_crm`, `campana_curso`, además de `usuario.plan` que reflejan el plan activo en sesión.

### 5.2 El carrito como sesión PHP

Se almacena en `$_SESSION['carrito']` como mapa `[curso_id => 1]` (cantidad fija de 1 por curso). El método privado `normalizarCarrito()` ([app/controllers/CarritoController.php:377](app/controllers/CarritoController.php)) garantiza siempre IDs enteros positivos y se llama en cada operación para sanear la sesión.

#### Añadir curso (`carrito-añadir`)

- Verifica `curso_id` válido y que el curso existe.
- Si hay sesión, comprueba que el usuario no esté ya matriculado (`estado IN activa, completado`) y rechaza con `estado='matriculado'`.
- Si el curso ya está en el carrito, devuelve `estado='ya_en_carrito'`.
- En éxito, además devuelve `descuento`, `precio` y `precioFinal` (precio - %descuento) para que el frontend pueda actualizar el badge con el ahorro.

Todas las respuestas siguen el contrato `{ ok: bool, estado: string, mensaje: string, total?: int, ... }`.

#### Listado y totales (`carrito`)

`index()` calcula:

- `subtotalOriginal` (suma de precios brutos).
- `subtotalFinal` (suma de precios con descuento de campaña aplicado).
- `ahorro = subtotalOriginal - subtotalFinal`.
- `iva = round(subtotalFinal * 0.21, 2)` (IVA fijo del 21%).
- `total = subtotalFinal + iva`.

Cursos ya matriculados se ignoran del cómputo aunque sigan en la sesión (la vista los mostrará con un aviso).

#### Eliminar (`carrito-eliminar`)

`eliminar()` no devuelve solo OK: recalcula y devuelve `subtotal_fmt`, `ahorro_fmt`, `tiene_descuento`, `iva_fmt`, `total_fmt`, todos pre-formateados con `number_format(.., 2)`, para que el frontend repinte la cesta sin recargar la página.

#### Descuentos de campaña

`fetchDiscounts(PDO, ids[])` consulta `campana_curso × campana_crm` filtrando por `cm.activa=1 AND (cm.fecha_fin IS NULL OR cm.fecha_fin >= date('now'))` y devuelve `[curso_id => descuento_pct]`. La estructura `campana_crm` es la base del módulo de marketing del CRM (Bloque 8) — aquí solo se consume.

### 5.3 Checkout único — `CarritoController::checkout`

Pasos del método:

1. Requiere sesión; si no, redirige a `?url=login&retorno=carrito`.
2. Carrito vacío → flash `'Tu cesta está vacía.'` y vuelta al carrito.
3. Trae cursos, filtra los ya matriculados, calcula descuentos por curso.
4. Para cada curso válido construye un `line_item` para Stripe:
   - `currency = eur`.
   - `product_data.name = curso.titulo`, opcional `description` con descuento y `images` con la URL absoluta del icono.
   - `unit_amount = (int) round(precioFinal * 1.21 * 100)` — **IVA incluido en el unit amount** (Stripe trabaja en céntimos).
5. Los cursos con `precioFinal <= 0` se separan en `$cursosGratis` (no van como line item).
6. **Branches:**
   - **No hay `STRIPE_SECRET_KEY`:** matricula directamente, vacía esos IDs del carrito y redirige a `?url=pago-ok&simulado=1&ids=…`. Modo "demo".
   - **Todos los cursos son gratis:** matricula y redirige con `&gratis=1`.
   - **Hay line items:** llama a `\Stripe\Checkout\Session::create([...])` con `mode='payment'`, `success_url` apuntando a `?url=pago-ok&session_id={CHECKOUT_SESSION_ID}`, `cancel_url` al carrito, `locale='es'`, y guarda en `metadata` el `usuario_id`, la lista CSV de `curso_ids`, los `cursos_gratis` y el `ahorro_total`. Redirige a `$session->url`.

### 5.4 Página de éxito — `pagoOk`

`pagoOk()` distingue tres escenarios:

- **Modo simulado o gratis** (`?simulado=1` / `?gratis=1` + `ids=`): trae los cursos, renderiza `views/carrito/pago_ok.php` (los cursos ya fueron matriculados antes de redirigir).
- **Modo Stripe normal**:
  - Recupera la `Checkout\Session` por `session_id`.
  - Si `payment_status === 'paid'` **y** `metadata.usuario_id === usuario_id de sesión` (defensa contra suplantación), procede a matricular los `curso_ids` y vaciarlos del carrito.

Esto significa que el matriculation real se hace en `pagoOk` y/o en el webhook (idempotente).

### 5.5 Webhook — `stripe-webhook`

Endpoint público (`?url=stripe-webhook`, sin sesión). Implementa:

1. Lee `php://input` y la cabecera `Stripe-Signature`.
2. Lee `STRIPE_WEBHOOK_SECRET` de `.env`.
3. Si no hay `STRIPE_SECRET_KEY`, responde `200` y sale (Stripe necesita 2xx para no reintentar).
4. Si hay `STRIPE_WEBHOOK_SECRET`, valida la firma con `\Stripe\Webhook::constructEvent`. Si no, parsea el JSON crudo y reconstruye con `\Stripe\Event::constructFrom` (modo desarrollo/local sin firma).
5. Solo reacciona a `checkout.session.completed` con `payment_status='paid'`: lee `metadata.usuario_id` y `metadata.curso_ids`, y matricula. Responde `200`.

El webhook duplica lo que ya hace `pagoOk()` pero garantiza la matriculación incluso si el usuario cierra la pestaña tras pagar (el flujo `pagoOk` requiere que vuelva al `success_url`). `Curso::matricularCursos` es seguro frente a reentradas porque usa `ON CONFLICT` y `INSERT OR IGNORE`.

### 5.6 Helpers internos del carrito

- `obtenerCurso`, `obtenerCursosPorIds` — wrappers con `IN (?, ?, …)`.
- `obtenerMatriculados($db, $uid, $cursoIds)` — devuelve set `[curso_id => true]` de matrículas activas/completadas, para filtrar cursos ya inscritos.
- `matricularCursos($db, $uid, $cursoIds)` — verifica usuario y curso, luego ejecuta el mismo `INSERT INTO matricula … ON CONFLICT(usuario_id, curso_id) DO UPDATE SET estado='activa', fecha=datetime('now')` que `Curso::matricular`. Se envuelve cada ejecución en try/catch para que un error con un curso no bloquee los demás.
- `retirarCursosDelCarrito(ids[])` — `unset` en `$_SESSION['carrito']`.
- `setFlash`, `stripeSecretKey` (lee `STRIPE_SECRET_KEY` desde `getenv()`/`$_ENV`), `absoluteBaseUrl` (construye scheme+host para `success_url`).

### 5.7 Suscripciones — planes y precios

Tabla `suscripcion(usuario_id, plan, status)` — un registro por usuario; `status='activa'` o `'cancelada'`. Planes válidos hardcoded:

- `curso_individual` — gratis (0 €), pago por curso.
- `plan_estudiantes` — 19,99 €/mes (`1999` céntimos).
- `plan_empresas` — 49,99 €/mes (`4999` céntimos).

`SuscripcionController::index()`:

- Si hay sesión, consulta el plan activo y lo guarda en `$_SESSION['usuario_plan']`. Esto refresca el cache de plan en cada visita a la página.
- Muestra `views/suscripciones/index.php` con la tabla comparativa.

### 5.8 `iniciarPago` — Stripe Subscription

`iniciarPago()` ([app/controllers/SuscripcionController.php:53](app/controllers/SuscripcionController.php)):

1. Requiere sesión.
2. Valida `plan` contra el whitelist.
3. **Branch simulado:** si `STRIPE_SECRET_KEY` está vacío o el plan es `curso_individual` (gratis), llama a `activarPlan($plan)` y redirige a `?url=suscripcion-ok&plan=…&simulado=1`.
4. **Branch Stripe:** crea sesión Checkout con:
   - `mode = 'subscription'`.
   - `line_items[0].price_data.recurring.interval = 'month'`.
   - `metadata.usuario_id` y `metadata.plan`.
   - `success_url` con `session_id`.

> Nota: aquí se usa `defined('STRIPE_SECRET_KEY')` (constante PHP) en lugar de `getenv()`. La constante no se define en `config.php` actualmente — eso significa que en la práctica este branch siempre cae en "simulado" salvo que se añada `define('STRIPE_SECRET_KEY', …)` en `config.php`. El carrito sí lee desde `getenv('STRIPE_SECRET_KEY')`. Asimetría a documentar para quien despliegue el proyecto.

### 5.9 `pagoOk` de suscripción

Lee `?simulado=1` (activa directamente) o, si vino de Stripe, recupera la `Checkout\Session` y comprueba `payment_status='paid'` / `status='complete'`. Si valida, llama a `activarPlan($planStripe)`. Render: `views/suscripciones/pago_ok.php`.

### 5.10 `activarPlan` — el upsert simple

```php
SELECT id FROM suscripcion WHERE usuario_id = ?  → existe?
  UPDATE suscripcion SET plan = ?, status = 'activa' WHERE usuario_id = ?
o INSERT INTO suscripcion (usuario_id, plan, status) VALUES (?, ?, 'activa')
$_SESSION['usuario_plan'] = $plan;
```

Es la única vía de cambiar de plan dentro de la app (no hay downgrade explícito). Una cancelación tendría que hacerse desde el CRM o desde Stripe Dashboard.

### 5.11 Interacción con el resto de la app

El plan activo influye en varias decisiones:

- `DetalleCursoController` y `LeccionController` bypassean la matrícula si el plan es `plan_estudiantes` o `plan_empresas` — acceso total.
- El dashboard precachea `$_SESSION['usuario_plan']` al entrar.
- Los precios mostrados en el catálogo/detalle se filtran por descuento de campaña (`fetchDiscounts`) pero no aplican el "todo gratis" si el alumno tiene plan total: simplemente la matrícula es libre desde el flujo de detalle.

### 5.12 Diagrama de flujo

```
Carrito (compra única)                              Suscripciones (recurrente)
──────────────────────                              ──────────────────────────
add ─▶ $_SESSION['carrito'][id]=1
view ─▶ subtotalOriginal/Final, ahorro, IVA 21%
checkout ─▶ fetchDiscounts
            ├── sin Stripe / todo gratis ─▶ matricularCursos ─▶ pago-ok
            └── Stripe Checkout (mode=payment)
                ├── line_items con IVA en unit_amount
                ├── metadata.usuario_id, curso_ids
                └── success_url → pago-ok?session_id=
                                    │
                                    ▼
                                  retrieve session
                                  payment_status=paid && usuario_id coincide
                                  ─▶ matricularCursos + retirar del carrito
                                    │
        webhook (paralelo)          │
        ──────────────              │
        Stripe POST → webhook       │
        verify firma (si secreto)   │
        checkout.session.completed  │
        ─▶ matricularCursos (idempotente)

iniciarPago ─▶ planesValidos
              ├── STRIPE_SECRET_KEY vacío o plan=curso_individual
              │    └── activarPlan ─▶ suscripcion-ok?simulado=1
              └── Stripe Checkout (mode=subscription, recurring=month)
                   metadata.usuario_id, metadata.plan
                   success_url → suscripcion-ok?session_id=
                                   │
                                   ▼
                                 retrieve session
                                 paid|complete ─▶ activarPlan
```

---

## Bloque 6 — Calendario, eventos, notificaciones y buzón

La capa de comunicación interna y planificación del alumno. Agrupa cuatro subsistemas que interactúan entre sí:

- **Calendario / Planificador** — vista FullCalendar con tareas, expiraciones, eventos personales y sugerencias inteligentes.
- **Eventos personales** — CRUD del alumno sobre su calendario.
- **Notificaciones** — feed in-app que se autosincroniza desde varias fuentes (tareas, expiraciones, mensajes, campañas, etc.).
- **Buzón** — mensajería bidireccional entre usuarios y staff, más sistema de incidencias.

### 6.1 Rutas y archivos

| Ruta                  | Archivo                                                                   | Tipo                  |
|-----------------------|---------------------------------------------------------------------------|-----------------------|
| `calendario`          | [CalendarioController](app/controllers/CalendarioController.php) (top-level) | Vista HTML            |
| `api-eventos-usuario` | [EventoUsuarioController](app/controllers/EventoUsuarioController.php) (top-level) | JSON CRUD             |
| `api-notificaciones`  | [NotificacionController](app/controllers/NotificacionController.php) (top-level) | JSON API              |
| `notificaciones`      | [NotificacionesPageController](app/controllers/NotificacionesPageController.php) (top-level) | Vista HTML paginada   |
| `buzon`               | [BuzonController](app/controllers/BuzonController.php) (clase con DI)     | Mixto (HTML + JSON API)|

Modelos: [Notificacion](app/models/Notificacion.php), [EventoUsuario](app/models/EventoUsuario.php), [Mensaje](app/models/Mensaje.php), [Tarea](app/models/Tarea.php).

Tablas: `notificacion`, `evento_usuario`, `mensaje`, `incidencia`, `incidencia_respuesta`, `leccion_vista` (para racha/patrones), `tarea`, `tarea_entregable`, `entrega`, `entrega_entregable`, `campana_crm`.

### 6.2 Calendario / Planificador — `CalendarioController`

Es la vista más ambiciosa del proyecto. Renderiza un FullCalendar.js con varias capas superpuestas y dos paneles adicionales: **Smart Slots** (sugerencias) y **Skills Radar** (gráfico de habilidades por curso).

#### 6.2.1 Datos cargados para la vista

1. **Cursos en progreso + expiración:** misma consulta del dashboard (lecciones vistas / totales, última lección), añadiendo `fecha_expiracion = fecha_matricula + 90 días` y `dias_restantes`.
2. **Tareas del usuario:** `Tarea::obtenerPanelUsuario($uid)` con `estado_visual`.
3. **Tareas urgentes:** vencen hoy o en ≤ 3 días, no entregadas.
4. **Tareas vencidas:** `estado_visual === 'vencida'`.
5. **Eventos personales:** `EventoUsuario::obtenerPorUsuario($uid)`.
6. **Eventos de hoy:** tareas con `fecha_limite` ≡ hoy + eventos personales con `fecha_inicio` ≡ hoy.

#### 6.2.2 Construcción del array `fcEvents` para FullCalendar

Cada item respeta la estructura de FullCalendar: `{id, title, start, end?, color, allDay?, classNames?, extendedProps}`. Las capas:

- **Tareas de cursos** (`tarea_<id>`, color = color del curso). `extendedProps.tipo='tarea'`, `editable=false`.
- **Tareas entregables** (`te_<id>`): la **fecha de vencimiento es `matrícula + 90 días`** (calculada con `date(m.fecha, '+90 days')`). Titulado con `✓` si entregada, `📝` si no. Verde / ámbar según estado.
- **Expiraciones de curso** (`exp_<id>`): rojo (`#ef4444`), `⏰ Expira: …`, `allDay=true`.
- **Eventos personales** (`ev_<id>`): pintados con el color almacenado o el del tipo (`sesion`/`hito`/`recordatorio`/`bloqueo`). Únicos con `editable=true` (drag-and-drop permitido).

Asignación de colores por curso: paleta de 8 colores rotando con `% count($palette)` para que cada título de curso siempre reciba el mismo color durante la sesión.

#### 6.2.3 Smart Slots — sugerencias inteligentes

Dos heurísticas conviven:

**(a) Patrón de estudio (últimos 60 días):**

```sql
SELECT strftime('%w', visto_at) AS dia_semana,
       strftime('%H', visto_at) AS hora,
       COUNT(*) AS frecuencia
FROM leccion_vista WHERE usuario_id = ? AND visto_at >= date('now','-60 days')
GROUP BY dia_semana, hora ORDER BY frecuencia DESC LIMIT 3
```

El top-1 se proyecta sobre los próximos 7 días: en el siguiente día de la semana que coincida con el `dia_semana` más frecuente, crea un slot `[hora, hora+2)` titulado "💡 Estudia: <curso menos avanzado>". `extendedProps.descripcion` incluye el contador (`Sugerido según tus N sesiones habituales los Lun`).

**(b) Bloques sugeridos para lecciones pendientes:**

Trae las 5 primeras lecciones que aún no ha visto (`LEFT JOIN leccion_vista … IS NULL`, ordenadas por `unidad.orden, leccion.orden`). Para cada una, crea un slot el día `+i+2` a las 18:00 con duración fija de 30 min (el código deja preparado el uso de `leccion.duracion_min` real cuando exista la columna). Colores rotando entre 5 tonos.

Los slots se concatenan al final del `fcEvents` con `classNames=['smart-suggestion']` o `['bloque-sugerido']` para que CSS los pinte distintos (línea punteada, opacidad, etc.).

#### 6.2.4 Racha de estudio

Días consecutivos con al menos una `leccion_vista`. Sólo cuenta si el último día estudiado es hoy o ayer (rotura de racha si pasan 2+ días sin actividad). Se calcula iterando `diasEstudio` y comparando con `date('Y-m-d', '-1 day')` recursivamente.

#### 6.2.5 Skills Radar

Taxonomía hardcodeada `skillTaxonomy` (HTML/CSS, JavaScript, TypeScript, React, Vue, Angular, Node.js, PHP, Python, Bases de datos, Backend, Frontend, DevOps, Git, Testing, UX/UI), cada una con keywords. Para cada curso matriculado:

- Detecta las skills que matchean el título.
- Si ninguna matchea, usa el título como eje (fallback).
- Acumula `sum`/`count`/`courses` y calcula `nivel = round(sum/count)` (porcentaje medio de progreso de los cursos asociados).

Devuelve los 8 ejes con mayor `nivel`, ordenados desc, para alimentar un Chart.js radar en la vista. Extender la taxonomía es trivial — solo se toca el array.

### 6.3 Eventos personales — `EventoUsuarioController`

Endpoint JSON puro accesible vía `?url=api-eventos-usuario`. Soporta `GET` para listar y `POST` con `action` (`crear`, `actualizar`, `eliminar`).

Características:

- Lee el cuerpo como JSON (`php://input`) con fallback a `$_POST` para compatibilidad.
- **Actualización parcial:** `actualizar` carga el evento existente y hace `array_merge($existing, array_filter($data, fn($v) => $v !== null && $v !== ''))`. Esto permite que un drag-and-drop de FullCalendar (que solo envía `id` + `fecha_inicio`) no borre el `titulo`, `tipo`, etc.
- Valida `titulo` y `fecha_inicio` obligatorios.
- `eliminar`, `obtenerPorId`, `obtenerPorUsuario`, `crear` viven en el modelo [EventoUsuario](app/models/EventoUsuario.php).

Campos de `evento_usuario`: `id`, `usuario_id`, `titulo`, `tipo` (`sesion|hito|recordatorio|bloqueo`), `descripcion`, `fecha_inicio`, `fecha_fin`, `todo_el_dia`, `color`.

### 6.4 Notificaciones — modelo y API

#### 6.4.1 `Notificacion::sincronizarAutomaticas`

[Notificacion::sincronizarAutomaticas](app/models/Notificacion.php) corre 8 generadores en cadena. Todos se basan en el patrón **idempotente "LEFT JOIN notificacion … IS NULL"**: la consulta deja fuera lo que ya generó una notificación con el mismo `tipo` + `ref_id`. Por eso puede ejecutarse en cada petición sin duplicar.

Tipos generados automáticamente:

- `tarea` — tareas próximas (3 días) sin entrega — `ref_id = tarea.id`.
- `tarea_vencida` — tareas pasadas sin entrega.
- `evento_calendario` — recordatorios de eventos personales próximos.
- `expiracion` — cursos próximos a expirar.
- `mensaje` — mensajes nuevos recibidos en el buzón.
- `crm` — campañas dirigidas al usuario (`campana_crm`).
- `examen_teorico` — gating cumplido + matricula activa.
- `examen_practico` — test aprobado.

Estas notificaciones se complementan con las que **insertan los propios controladores** (Bloque 4 ya documenta `curso_completado`, `curso_fallido`, `examen_teorico`, `examen_practico`). El feed final es la unión de ambas fuentes.

#### 6.4.2 API JSON — `?url=api-notificaciones`

[NotificacionController::handle](app/controllers/NotificacionController.php) responde JSON con tres acciones (vía `?action=`):

| Acción         | Efecto                                                                          |
|----------------|---------------------------------------------------------------------------------|
| `list` (def.)  | Llama a `sincronizarAutomaticas` y devuelve `notificaciones` (25 más recientes, no leídas primero) y `no_leidas` (contador).|
| `leer`         | `POST id` → marca como leída si pertenece al usuario.                            |
| `leer-todas`   | Marca todas las del usuario como leídas.                                         |

Esto alimenta el icono de campana del header (badge con `no_leidas`) y los menús desplegables.

#### 6.4.3 Vista paginada — `?url=notificaciones`

[NotificacionesPageController](app/controllers/NotificacionesPageController.php) sirve la página dedicada. Acepta `?tipo=` para filtrar, `?p=` para paginar (20 por página) y dos formas de marcar leído:

- `POST accion=marcar-todas` (botón global).
- `GET ?leer=<id>&goto=<url>` — abrir una notificación: marca leída y redirige al destino (`url_accion` de la notificación o la propia página).

El `ORDER BY leido ASC, creado_en DESC` mantiene las no leídas arriba (idéntico al feed AJAX).

### 6.5 Buzón — mensajería e incidencias

`BuzonController` ([app/controllers/BuzonController.php](app/controllers/BuzonController.php)) es el único controlador que el front-controller instancia con **inyección de dependencias explícita**: `new BuzonController((new Database())->connect(), $_SESSION ?? [])`.

#### 6.5.1 Vista del buzón (`?url=buzon`)

`index()` ([app/controllers/BuzonController.php:71](app/controllers/BuzonController.php)) prepara:

- Lista de mensajes recibidos paginada (15 por página) con `nombre_emisor` + `rol_emisor`.
- Contador de no leídos.
- Mensaje activo si se pasa `?msg=<id>`.
- Renderiza la vista del buzón con el panel maestro/detalle.

#### 6.5.2 API JSON del buzón

Todas las demás acciones del controlador son endpoints JSON. Discriminadas por `?action=`:

| Acción                  | Método | Función                                                                          |
|-------------------------|--------|----------------------------------------------------------------------------------|
| `bandeja`               | GET    | Lista mensajes con `tab=recibidos|enviados` (limit 50). Resumen del cuerpo (`SUBSTR(.., 1, 120)`). |
| `mensaje`               | GET    | Detalle de un mensaje; verifica que el usuario es emisor o receptor (403 si no). Marca leído si era receptor. |
| `enviar`                | POST   | JSON body. Valida receptor (no es uno mismo) y calcula `hilo_id` para mantener hilos.|
| `marcar_leido`          | POST   | Marca leído un mensaje del receptor.                                              |
| `no_leidos`             | GET    | Contador para el badge.                                                          |
| `admins`                | GET    | Lista usuarios con rol `ADMINISTRADOR`/`MODERADOR` (para que el alumno escoja a quién escribir). |
| `crear_incidencia`      | POST   | Inserta `incidencia` con `estado='abierta'`, `prioridad='normal'`.                |
| `mis_incidencias`       | GET    | Lista las 20 incidencias del usuario con contador de respuestas y asignado.       |
| `mi_incidencia_detalle` | GET    | Detalle de la incidencia + todas las respuestas (`incidencia_respuesta`).         |

#### 6.5.3 Modelo de hilos

`mensaje.reply_to_id` apunta al mensaje al que se responde; `mensaje.hilo_id` apunta al **primer mensaje del hilo**. Cuando se responde a un mensaje, `apiEnviar` calcula: si el padre tiene `hilo_id`, lo hereda; si no, el `hilo_id` pasa a ser el `id` del padre. Así, agrupar un hilo es `WHERE hilo_id = X OR id = X`.

#### 6.5.4 Sistema de incidencias

Tablas:

- `incidencia (id, usuario_id, asunto, cuerpo, estado, prioridad, asignado_a, creado_en, cerrado_en, actualizado_en)`.
- `incidencia_respuesta (id, incidencia_id, usuario_id, mensaje, creado_en)`.

El alumno solo puede **crear** y **listar/leer** sus propias incidencias. La asignación a un staff, el cambio de estado/prioridad y las respuestas las gestiona el CRM (Bloque 8).

### 6.6 Diagrama del flujo

```
Calendario (vista)
─────────────────
CalendarioController::index
   ├── SELECT matriculas + lecciones vistas + fecha expiración
   ├── Tarea::obtenerPanelUsuario           → eventos "tarea"
   ├── tarea_entregable JOIN matricula     → eventos con deadline = matr+90d
   ├── EventoUsuario::obtenerPorUsuario    → eventos personales (editables)
   ├── Smart slots (patrón + lecciones pendientes)
   ├── Skills radar (taxonomía + cursos)
   └── render → vistas/calendario/index.php (FullCalendar + Chart.js)

Eventos personales (CRUD JSON)
──────────────────────────────
GET  ?url=api-eventos-usuario               → obtenerPorUsuario
POST ?url=api-eventos-usuario   action=crear/actualizar/eliminar
       └── actualizar: merge parcial con registro previo

Notificaciones
──────────────
GET  ?url=api-notificaciones   action=list
        ├── sincronizarAutomaticas()  (8 generadores idempotentes)
        └── obtenerRecientes + contarNoLeidas
POST action=leer | leer-todas
GET  ?url=notificaciones        → vista paginada con ?tipo=&p=

Buzón / Incidencias
───────────────────
GET ?url=buzon                  → maestro/detalle paginado
GET ?url=buzon&action=bandeja|mensaje|no_leidos|admins
POST ?url=buzon&action=enviar | marcar_leido | crear_incidencia
GET ?url=buzon&action=mis_incidencias | mi_incidencia_detalle
```

---

## Bloque 7 — Perfil, ajustes, chatbot, apuntes IA, Google y búsqueda

Funcionalidades transversales que no encajan en los flujos principales de aprendizaje pero son parte de la experiencia diaria.

### 7.1 Mapa

| Ruta                     | Controlador / archivo                                                | Responsabilidad                                         |
|--------------------------|----------------------------------------------------------------------|---------------------------------------------------------|
| `perfil`, `guardarPerfil`, `cambiar-password` | [PerfilController](app/controllers/PerfilController.php) | Edición de perfil + cambio de contraseña.              |
| `api-perfil`             | `PerfilController` (vía ruta dedicada del front-controller)          | (Pensado para llamadas AJAX al perfil.)                 |
| `ajustes`, `guardarAjustes`, `cambiarContrasena`, `eliminarCuenta` | [AjustesController](app/controllers/AjustesController.php) | Idioma, notificaciones, privacidad, baja.              |
| `chatbot`                | [ChatbotController](app/controllers/ChatbotController.php) (top-level)| Asistente "Oráculo" con Gemini.                         |
| `apuntes-ia`             | [ApuntesIaController](app/controllers/ApuntesIaController.php) (top-level) | Genera apuntes Markdown de la lección desde su vídeo YouTube.|
| `vincular-google`        | [VincularGoogleController](app/controllers/VincularGoogleController.php) (top-level) | Asocia/desasocia una cuenta Google (NotebookLM).      |
| `buscar`                 | [BuscarController](app/controllers/BuscarController.php)             | Catálogo filtrable y paginado.                          |
| `autocomplete`           | [AutocompleteController](app/controllers/AutocompleteController.php) | Sugerencias JSON para la búsqueda.                      |

Helper transversal: [GeminiService](app/helpers/GeminiService.php) — encapsula tres llamadas a la API de Google Gemini.

### 7.2 Perfil — `PerfilController`

Pasos clave:

- **Constructor con auto-migración**: ejecuta cinco `ALTER TABLE usuario ADD COLUMN` envueltos en try/catch para `foto`, `bio`, `idioma`, `notificaciones`, `privacidad`. Esto **duplica parcialmente** la migración global de `db.php` (Bloque 1), pero asegura que el perfil funcione aunque alguien borre la migración central. `AjustesController` hace lo mismo.
- `index()` (`?url=perfil`): exige sesión, carga el usuario; si ya no existe, destruye la sesión (cuenta eliminada) y redirige al login. Trae los cursos matriculados y consume el flash `flash`.
- `guardar()` (`?url=guardarPerfil`, POST):
  - Valida `nombre` (1-80 chars) y `bio` (≤300).
  - **Foto:** delega en `subirFoto()`: tipos MIME `image/jpeg|png|gif|webp`, máx 2 MB, comprueba MIME real con `mime_content_type`. Guarda en `public/uploads/fotos/avatar_<id>_<uniqid>.<ext>` y borra la anterior si existía.
  - Whitelist de los mismos campos académicos que el registro (`nivel_experiencia`, `frecuencia_estudio`, `ultimo_estudio`, `tipo_curso_preferido`) más libres (`areas_interes`, `tecnologias`, `github`, `objetivo`).
  - `UPDATE usuario SET …` y refresca `$_SESSION['usuario_nombre']`.
- `cambiarPassword()` (`?url=cambiar-password`, POST): valida `password_actual` con `password_verify`, exige nueva ≥6 caracteres y confirmación coincidente, hashea con `PASSWORD_BCRYPT`.

Todos los errores y confirmaciones usan el flash `$_SESSION['flash']` con estructura `{type, message}` (mismo patrón que `dashboard_flash`).

### 7.3 Ajustes — `AjustesController`

Tres preferencias (`idioma`, `notificaciones`, `privacidad`) + cambio de contraseña + **baja de cuenta**.

- `guardar()` valida `idioma ∈ {es, en}` y `privacidad ∈ {publico, privado}`; `notificaciones` se interpreta como checkbox (`isset` → 1, si no 0).
- `cambiarContrasena()` similar al `cambiarPassword` del perfil, con nombres de POST distintos (`contrasena_actual`, `contrasena_nueva`, `contrasena_confirmar`) — son dos formularios paralelos en distintas vistas.
- `eliminarCuenta()` (`?url=eliminarCuenta`, POST):
  - Exige escribir literalmente `"eliminar"` en `confirmar_texto` (insensitive) — segunda barrera además de la contraseña.
  - Verifica contraseña con `password_verify`.
  - Borra registros relacionados (`matricula`, `suscripcion`, `documento`, `notificacion`, `evento_usuario`) y finalmente el `usuario`. Cada `DELETE` envuelto en try/catch (algunas tablas pueden no existir aún).
  - `session_destroy()` y redirige a `?url=login&eliminada=1`.

Diferencias notables con `PerfilController`: aquí no hay foto ni campos académicos, solo configuración + cuenta. Si se quiere unificar la pantalla en el futuro, una sola vista podría servir.

### 7.4 Chatbot "Oráculo" — `ChatbotController`

[ChatbotController](app/controllers/ChatbotController.php) es un script top-level que sirve **tanto la página HTML del chatbot como el endpoint AJAX** sobre la misma URL `?url=chatbot`.

**GET:** carga la vista `views/chatbot/index.php`.

**POST:**

- Acción especial `accion=reiniciar`: borra `$_SESSION['chatbot_historial']` (limpia el contexto conversacional).
- Resto: valida que la pregunta tenga entre 3 y 600 caracteres.
- Recoge el nombre del usuario y hasta 8 cursos activos para construir el `systemPrompt`:

```
Eres Oráculo, el asistente virtual de MatrixCoders…
El alumno se llama {nombre} y está matriculado en: {lista_de_cursos}.
Responde siempre en español, de forma amigable y concisa. Máximo 3-4 párrafos.
```

- Recupera `$_SESSION['chatbot_historial']` (recortado a los últimos 20 turnos = 10 intercambios) y llama a `GeminiService::chatbotConHistorial`.
- Si responde OK, anexa los nuevos turnos al historial en sesión.

El historial es por sesión PHP — no persistente. Cada inicio de sesión limpia la conversación.

### 7.5 Apuntes IA — `ApuntesIaController`

Endpoint JSON puro (`?url=apuntes-ia&leccion=N[&forzar=1]`).

Flujo:

1. Sesión obligatoria.
2. Verifica que el alumno esté matriculado en el curso de esa lección (JOIN matricula activa).
3. **Caché compartido entre usuarios:** si no se pasa `forzar=1`, intenta servir desde `leccion_apuntes_ia` (un registro por lección, los apuntes son los mismos para cualquier alumno). Esto evita llamar repetidas veces a Gemini con el mismo vídeo.
4. Si toca regenerar, extrae el ID de YouTube de `leccion.video_url` con regex `(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})`. Si la lección no es YouTube, devuelve error.
5. Llama a `GeminiService::generarApuntesDesdeYoutube($url, $titulo)`.
6. `INSERT OR REPLACE INTO leccion_apuntes_ia` con el contenido devuelto y `generado_en = datetime('now')`.

Devuelve `{ok, contenido, cache: bool}` para que el cliente pueda saber si los apuntes vienen del caché.

### 7.6 GeminiService — capa HTTP cliente

[GeminiService](app/helpers/GeminiService.php) encapsula la API de Gemini 1.5 Flash (`generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent`). Lee la clave de la constante `GEMINI_API_KEY` definida en `app/config.php`.

Tres métodos públicos:

- `generarApuntesDesdeYoutube($url, $titulo)`: cuerpo con `contents[0].parts[1].file_data = {file_uri: youtube, mime_type: video/mp4}` — usa la capacidad de Gemini para procesar vídeos de YouTube directamente sin descargar. `temperature=0.3`, `maxOutputTokens=2048`. El prompt construido por `buildPrompt()` exige un formato Markdown muy concreto (Resumen / Conceptos clave / Puntos importantes / Ideas para recordar / Preguntas de repaso) con emojis para que sean fáciles de hojear.
- `preguntaConContexto($pregunta, $contexto)`: prompt sencillo con instrucciones de tutor + contexto + pregunta. `temperature=0.4`. Usado por la acción `rag_chat` de `LeccionController` (ver Bloque 4 §4.5.1).
- `chatbotConHistorial($pregunta, $systemPrompt, $historial)`: construye un array `contents` con turnos `role=user|model`. Inyecta el `systemPrompt` como primer turno user + acknowledge model (workaround porque Gemini no tiene un campo `system` separado en este endpoint). `temperature=0.5`. Usado por `ChatbotController`.

HTTP nativo con `file_get_contents` + `stream_context_create` y `ignore_errors=true` para poder leer respuestas no-2xx. Sin curl. Timeouts: 60-90 s según método.

Manejo de errores uniforme: devuelve `['ok' => false, 'error' => …]` ante falta de clave, fallo de red o `data.error.message` no nulo de la API.

### 7.7 Vinculación Google — `VincularGoogleController`

Endpoint JSON (`?url=vincular-google`, POST). Sirve para asociar la cuenta Google del alumno con su cuenta MatrixCoders, **no para login con Google** — se usa para que la plataforma pueda enlazar a NotebookLM con la cuenta correcta del usuario en los recursos del instructor.

Acciones:

- `vincular` con `credential` = JWT que entrega Google Identity Services en el cliente. Decodifica el payload (sin validar firma) — separa por `.`, decodifica base64url, JSON-parsea. Extrae `sub` (Google ID), `email`, `name`. `INSERT OR REPLACE INTO usuario_google (usuario_id, google_id, google_email, google_nombre, vinculado_en)`.
- `desvincular`: `DELETE FROM usuario_google WHERE usuario_id = ?`.

> Limitación: no se valida la firma del JWT contra las JWKS públicas de Google. Eso permite, en teoría, suplantar el `sub` y `email`. Como aquí solo se usa para mostrar etiqueta visual y para que NotebookLM enlace en el navegador del propio usuario, el riesgo es bajo, pero para producción habría que verificar la firma con la librería oficial.

`GOOGLE_CLIENT_ID` se define en `config.php` y se inyecta en la vista que carga el SDK de Google.

### 7.8 Búsqueda — `BuscarController`

`?url=buscar` muestra una página de catálogo con filtros. Lee de la query string:

- `q`: texto libre (LIKE en `titulo` y `descripcion`).
- `precio`: `gratis` (precio NULL o 0) o `pago` (>0).
- `nivel[]`: multi (`principiante|estudiante|profesional`).
- `categoria[]`: multi (cualquier valor existente).
- `orden`: `popular|recientes|precio_asc|precio_desc`.
- `p`: página (9 cursos por página).

Construye el `WHERE` dinámico con placeholders posicionales (`?, ?, …`). Categorías se ofrecen como facetas: una consulta inicial trae `DISTINCT categoria` de los cursos activos para pintar los checkboxes.

El SELECT principal trae también `total_matriculas` y el `descuento_activo` (vía la misma subquery sobre `campana_curso + campana_crm` que usa el catálogo y el carrito). Conteo total se calcula con `SELECT COUNT(*) FROM (SELECT c.id … GROUP BY c.id)` para evitar duplicar filas por el JOIN con `matricula`.

El array `$matriculasUsuario` (si hay sesión) se pasa a la vista para mostrar "Continuar" en los cursos ya matriculados.

### 7.9 Autocomplete — `AutocompleteController`

Endpoint mínimo (`?url=autocomplete&q=…`). Solo responde JSON, devuelve hasta 6 sugerencias del modelo `Curso::sugerencias`. Sin sanitización extra: el LIKE se hace en el modelo con placeholder, no inyectable.

### 7.10 Diagrama

```
Perfil
──────
GET  ?url=perfil          → PerfilController::index
POST ?url=guardarPerfil   → guardar (foto/avatar + campos académicos)
POST ?url=cambiar-password→ cambiarPassword

Ajustes
───────
GET  ?url=ajustes
POST ?url=guardarAjustes      (idioma | notificaciones | privacidad)
POST ?url=cambiarContrasena
POST ?url=eliminarCuenta      (palabra "eliminar" + password)

IA — Chatbot y Apuntes
──────────────────────
GET  ?url=chatbot         → vista
POST ?url=chatbot         → Gemini chat con historial en sesión
POST ?url=chatbot accion=reiniciar
GET  ?url=apuntes-ia&leccion=N[&forzar=1]
    └── caché compartido en leccion_apuntes_ia
    └── GeminiService::generarApuntesDesdeYoutube

Integración Google
──────────────────
POST ?url=vincular-google accion=vincular   credential=<JWT>
POST ?url=vincular-google accion=desvincular

Búsqueda
────────
GET ?url=buscar?q=&precio=&nivel[]=&categoria[]=&orden=&p=
GET ?url=autocomplete&q=…  → JSON []
```

---

## Bloque 8 — CRM y panel de administración

El "back office" de la plataforma. Una aplicación dentro de la aplicación: dashboard de métricas, gestión de usuarios, editor visual de cursos, corrección de prácticas, campañas con descuentos, comunicación (mensajería + incidencias), logs de actividad y ajustes del propio staff.

Punto de entrada principal: `/matrixcoders/admin/index.php` (login dedicado), con un punto de entrada secundario en `?url=crm` desde el front controller para quienes ya tienen sesión válida.

### 8.1 Roles que entran al CRM

- `ADMINISTRADOR` — acceso completo. Si `es_superadmin=1`, marcado visualmente como Superadmin (el `isidoro@admin.com` inicial lo es).
- `MODERADOR` — gestiona cursos, comunicación, campañas, pero **no** usuarios.
- `INSTRUCTOR` — igual que moderador para los cursos asignados (`curso.instructor_id`).

Cualquier otro rol (incluido `USUARIO`) es expulsado del CRM y redirigido al dashboard del alumno.

### 8.2 Bootstrap "standalone" — `admin/index.php`

[admin/index.php](admin/index.php) es la puerta independiente. Sus pasos:

1. Define `CRM_STANDALONE` para que el `CrmController` ajuste todas las URLs al prefijo `/matrixcoders/admin/index.php?…` en vez de `?url=crm…`.
2. Carga `app/config.php` y `app/db.php`, arranca sesión.
3. Si `?auth=logout`, hace el cierre de sesión "duro" (vacía `$_SESSION`, borra la cookie, `session_destroy()`) y vuelve al login.
4. Si llega un POST con `crm_login`: valida email/contraseña con `password_verify`, rechaza el rol `USUARIO`, y si OK regenera el ID de sesión (`session_regenerate_id(true)` — defensa frente a session-fixation) y redirige a la home del CRM.
5. Sin sesión válida: muestra `views/crm/login.php`.
6. Con sesión: instancia `CrmController` y delega:
   - `?crm_api=1`: `$crm->api()` (JSON).
   - Resto: `$crm->index()` (HTML).

La ruta `?url=crm` del front controller hace básicamente lo mismo pero sin `CRM_STANDALONE`; ese flag es el switch entre los dos sets de URLs.

### 8.3 `CrmController` — arquitectura

[CrmController](app/controllers/CrmController.php) (≈ 2 300 líneas) sigue el patrón **router interno por secciones**. El constructor:

- Configura URLs según `CRM_STANDALONE`.
- Conecta a BD, ejecuta `runCrmMigrations` (ALTER TABLE … añadiendo unas 20 columnas que las distintas funciones del CRM necesitan + `fixRolConstraint` que recrea la tabla `usuario` con el `CHECK (rol IN ('USUARIO','INSTRUCTOR','MODERADOR','ADMINISTRADOR'))` actualizado, dejando antes intactas las columnas opcionales).
- Carga el `usuario` actual y calcula los flags `esSuperAdmin`/`esAdmin`/`esModerador`/`esInstructor`.
- Bloquea rol `USUARIO`.

**Dos métodos públicos:** `index()` (página HTML) y `api()` (JSON).

### 8.4 Secciones (HTML) — `index()`

`index()` lee `?sec=`. Cada sección carga sus datos con un `getXxxData()` y delega en una vista del directorio `app/views/crm/`. La vista raíz es `app/views/crm/layout/base.php` que envuelve todo con la barra lateral y el header común.

| `?sec=`        | Loader                | Permisos requeridos             | Función                                                         |
|----------------|------------------------|--------------------------------|-----------------------------------------------------------------|
| `dashboard`    | `getDashboardData`    | cualquiera                      | KPIs (usuarios, cursos, campañas, matrículas, incidencias), top cursos, distribución por rol, registros en 6 meses. |
| `usuarios`     | `getUsuariosData`     | **solo `ADMINISTRADOR`**         | CRUD de usuarios con búsqueda, filtro de rol y paginación.       |
| `cursos`       | `getCursosData`       | admin/moderador                  | Listado con `activo`, instructor asignado, ordenación.           |
| `editor`       | `getEditorData`       | admin/moderador/instructor       | Editor de curso: unidades, lecciones, recursos, exámenes, tareas, apuntes. |
| `campanas`     | `getCampanasData`     | admin/moderador                  | Campañas con descuentos sobre cursos, fechas y audiencia.        |
| `comunicacion` | `getComunicacionData` | admin/moderador                  | Buzón staff: hilos con usuarios e incidencias.                   |
| `logs`         | `getLogsData`         | cualquiera (staff)               | `crm_actividad` paginado con filtro por tipo y texto.            |
| `perfil`       | `getPerfilData`       | el propio usuario staff          | Datos personales + estadísticas de actividad propia.             |
| `ajustes`      | `getAjustesData`      | el propio usuario staff          | Preferencias del staff (idioma, notificaciones, privacidad).     |

Si llega `sec=usuarios` y no es admin, se reemplaza por `sin_permisos`. Resto de bloqueos viven dentro de cada API.

### 8.5 API JSON — `api()`

Endpoint único: `?url=crm-api` (o `?crm_api=1` en standalone). Discrimina por `?action=` y `match` despacha al `apiXxx()`. Resumen de acciones agrupadas por área:

**Usuarios** (sólo admin):
- `crear_usuario`, `editar_usuario`, `eliminar_usuario` (no permite auto-eliminarse).

**Cursos y catálogo:**
- `toggle_curso`, `toggle_all_cursos`, `actualizar_curso`, `asignar_instructor`, `subir_imagen_curso`.

**Editor de cursos:**
- `guardar_unidades`, `crear_unidad`, `eliminar_unidad`.
- `crear_leccion`, `editar_leccion`, `eliminar_leccion`.
- `subir_recurso_leccion`, `eliminar_recurso`, `get_recursos_leccion`.
- `guardar_apuntes` (apuntes del curso) y `guardar_apuntes_leccion`.
- `guardar_examen` (test), `guardar_examen_practico` (con `tarea_practica`), `guardar_tareas_curso`.

**Correcciones y certificados:**
- `get_resultados_curso`, `get_entregas_alumno`, `revisar_practica`, `generar_certificado`.

**Campañas:**
- `crear_campana`, `editar_campana`, `eliminar_campana`, `check_campana_conflicto` (valida solapes, duplicados, fechas inválidas).

**Comunicación:**
- `mensajes_lista`, `mensajes_enviar`, `mensajes_detalle`, `mensajes_no_leidos`, `mensajes_conversacion`, `enviar_mensaje`.
- `usuarios_destinatarios` (sugerencias de destinatarios).
- `incidencias_lista`, `incidencia_detalle`, `crear_incidencia`, `incidencia_responder`, `incidencia_estado`.

**Notificaciones del CRM** (badge del staff):
- `get_crm_notifs`, `marcar_notif_leida`, `marcar_todas_leidas`.

**Perfil del staff:**
- `actualizar_perfil`, `cambiar_contrasena`, `actualizar_ajustes`.

Cada handler vuelve un array. Si lanza una excepción, el `try/catch` exterior la convierte en `{ok:false, error:"Error interno: …"}`. La respuesta se envía con `json_encode(..., JSON_UNESCAPED_UNICODE)` (texto en español sin escapes).

Todos los handlers comienzan con chequeo de rol (`if (!$this->esAdmin) return ['ok'=>false,'error'=>'Sin permisos']`), validan inputs (longitudes, emails, IDs) y registran auditoría en `crm_actividad` cuando procede.

### 8.6 Entrada y validación uniforme

`input()` (línea 817) normaliza la entrada: `json_decode(file_get_contents('php://input'))` con fallback a `$_POST`. Esto permite usar `fetch` con cuerpo JSON o `<form>` clásico sin cambiar el handler.

### 8.7 Auditoría — `crm_actividad`

Cualquier acción significativa llama a `logActividad($titulo, $tipo='info')` que inserta en `crm_actividad (usuario_id, tipo, titulo, creado_en)`. La sección `logs` muestra esa tabla paginada y filtrable. Tipos comunes: `success`, `warning`, `info`. Es el journal interno para revisar quién hizo qué.

### 8.8 Campañas — modelo

Tablas:

- `campana_crm`: `id`, `titulo`, `descripcion`, `tipo`, `audiencia` (`todos|nuevos|usuario_x`), `dias_registro` (filtro de antigüedad), `descuento_pct`, `fecha_inicio`, `fecha_fin`, `activa`.
- `campana_curso`: `campana_id`, `curso_id`, `descuento` — descuentos por curso aplicados (usado por carrito, catálogo, buscador).

`apiCheckCampanaConflicto` ejecuta varias detecciones (orden inválido de fechas, título duplicado en campaña activa, solape sobre los mismos cursos) y devuelve un mensaje legible que la UI muestra como warning antes de guardar.

### 8.9 Editor de cursos

El editor (`sec=editor`) soporta drag-and-drop de unidades/lecciones (los `orden` se guardan con `guardar_unidades`), subida de portadas (`subir_imagen_curso` con validación MIME/tamaño), recursos de lección, y construcción del examen test (preguntas con N opciones, una marcada `correcta=1`) y del examen práctico (lista de `tarea_practica`).

Apuntes del curso (campo `curso.apuntes_json`) y de la lección (`leccion.apuntes`) se guardan como Markdown.

### 8.10 Corrección de prácticas

`apiGetEntregasAlumno` lista las entregas de un alumno. `apiRevisarPractica` recibe `entrega_id`, `nota`, `comentario`, marca `revisado=1` y registra en logs. Si tras revisar **todas** las entregas la media supera la nota mínima, el sistema (a través de `ExamenPracticoController` la próxima vez que el alumno entre, ver Bloque 4) emite el certificado.

`apiGenerarCertificado` permite al staff forzar la emisión manual sin pasar por la corrección (excepciones, casos manuales).

### 8.11 Comunicación

`apiMensajesEnviar` / `apiMensajesDetalle` son la versión "staff" del buzón del Bloque 6, con la posibilidad de iniciar conversación dirigiéndose a cualquier usuario. `usuarios_destinatarios` provee la búsqueda autocompletada.

Para incidencias: `apiIncidenciasLista` filtra por estado/asignado/prioridad; `apiResponderIncidencia` añade respuesta + actualiza `actualizado_en`; `apiEstadoIncidencia` cambia estado (abierta → en_proceso → cerrada → reabierta). Cerrar una incidencia rellena `cerrado_en`.

### 8.12 Notificaciones para staff

`apiGetCrmNotifs` agrega varias fuentes (incidencias abiertas, mensajes recientes, alertas de campañas próximas a expirar, etc.) para alimentar el icono de campana del CRM. `marcar_*_leida` controla el estado leído por usuario staff.

### 8.13 Frontend del CRM

El JavaScript del CRM vive en [public/js/crm.js](public/js/crm.js) (≈ 200 líneas). Maneja menús, modales, fetch a la API, badge de notificaciones, drag-and-drop del editor y filtros. El CSS está en [public/css/crm.css](public/css/crm.css). El layout HTML está en `app/views/crm/layout/base.php` con sidebar y header propios.

### 8.14 Diagrama global del CRM

```
/matrixcoders/admin/index.php           ?url=crm  (alumno staff vía portal)
       │                                       │
       └─────────────┬─────────────────────────┘
                     ▼
          CrmController::__construct
                ├── runCrmMigrations (ALTER TABLE × 20 + fixRolConstraint)
                ├── load usuario, calcular roles
                └── if rol == USUARIO → expulsar

  GET …            ?sec=…                  GET/POST …?crm_api=1&action=…
  ─────                                     ─────────────────────────────
  index()                                   api()
   ├─ getDashboardData                       ├─ usuarios: crear/editar/eliminar
   ├─ getUsuariosData (solo admin)           ├─ cursos: toggle/actualizar/asignar
   ├─ getCursosData                          ├─ editor: unidades/lecciones/recursos
   ├─ getEditorData                          ├─ examen test + práctico
   ├─ getCampanasData                        ├─ correcciones + certificados
   ├─ getComunicacionData                    ├─ campañas + check_conflicto
   ├─ getLogsData (crm_actividad)            ├─ comunicación (mensajes / incidencias)
   ├─ getPerfilData                          ├─ notificaciones CRM
   └─ getAjustesData                         └─ perfil / ajustes staff
   ↓
  views/crm/layout/base.php + sección       JSON {ok, ...}
```

---

## Bloque 9 — Modelos, helpers y esquema de base de datos

Este bloque recoge la "infraestructura de datos" del proyecto: clases del directorio [app/models/](app/models/), helpers compartidos en [app/helpers/](app/helpers/) y el conjunto de tablas SQLite que definen el modelo de dominio (creadas en `init.sql`, ampliadas en `migrate.sql` y `crm_migrate.sql`).

### 9.1 Patrón de modelo

Todos los modelos siguen el mismo patrón:

```php
class X {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function metodo(...): array|bool|...
}
```

- No hay un ORM ni una clase base. Cada modelo es un wrapper fino sobre la conexión PDO con prepared statements.
- Las consultas devuelven arrays asociativos (`PDO::FETCH_ASSOC`) o booleanos para inserts/updates/deletes.
- El controlador inyecta la `PDO` obtenida de `(new Database())->connect()` (Bloque 1).
- Métodos suelen filtrar por `usuario_id` cuando representan datos personales — la verificación de propiedad vive en el modelo, no en el controlador. Esto evita IDOR si alguien manipula la URL.

### 9.2 Catálogo de modelos

| Clase                                            | Tabla principal       | Responsabilidad                                                                 |
|--------------------------------------------------|-----------------------|---------------------------------------------------------------------------------|
| [Curso](app/models/Curso.php)                    | `curso`               | Catálogo, búsqueda, matriculación, navegación lección, notas personales, progreso. Es el más rico.|
| [Carpeta](app/models/Carpeta.php)                | `carpeta`             | Carpetas de la nube (con `padre_id` anidable).                                  |
| [Documento](app/models/Documento.php)            | `documento`           | Notas/archivos del usuario; métodos con/sin filtro por dueño.                   |
| [Tarea](app/models/Tarea.php)                    | `tarea`               | Tareas por curso, panel del usuario con `estado_visual`, días con eventos para calendario.|
| [Mensaje](app/models/Mensaje.php)                | `mensaje`             | Mensajería simple (alternativa a las consultas inline del `BuzonController`).   |
| [Notificacion](app/models/Notificacion.php)      | `notificacion`        | Feed + sincronizador de 8 fuentes idempotente.                                  |
| [EventoUsuario](app/models/EventoUsuario.php)    | `evento_usuario`      | CRUD de eventos personales del calendario.                                      |
| [UsuarioPreferencias](app/models/UsuarioPreferencias.php) | `usuario_preferencias` | Preferencias avanzadas (se complementa con campos directos en `usuario`).  |

> No existe modelo `Usuario.php` — la tabla `usuario` se consulta directamente desde varios controladores (Auth, Register, Perfil, Ajustes, CRM). Sería un refactor natural si el proyecto creciera.

### 9.3 Helpers — `app/helpers/`

#### `GeminiService.php`

Ya documentado en el Bloque 7. Encapsula la API de Google Gemini 1.5 Flash con tres métodos: `generarApuntesDesdeYoutube`, `preguntaConContexto`, `chatbotConHistorial`.

#### `PlanHelper.php`

[PlanHelper](app/helpers/PlanHelper.php) — control de acceso por plan de suscripción. Funcionalidades:

- `JERARQUIA` interna: `null/'' < curso_individual < plan_estudiantes < plan_empresas`.
- `planActivo()`: lee `$_SESSION['usuario_plan']`.
- `tiene($planMinimo)`: comparación jerárquica — `plan_empresas` cubre cualquier mínimo, etc.
- `requiere($planMinimo)`: gating — si no tiene el plan, guarda `$_SESSION['upgrade_requerido']` y redirige a `?url=upgrade`.
- `etiqueta($plan)` y `badgeClass($plan)`: labels y CSS para la UI.

Es la pieza limpia que permitiría reemplazar los checks ad-hoc `$_SESSION['usuario_plan'] === 'plan_estudiantes'` que aparecen en `LeccionController` y `DetalleCursoController` por una API uniforme.

#### `curso_imagen.php`

Función `matrixcoders_curso_image($imageName, $title)` — resolver de imagen para tarjetas de curso:

1. Caché estático de los ficheros disponibles en `public/img/cursos/`.
2. Si `imageName` coincide con un fichero existente (case-insensitive), lo devuelve.
3. Si no, intenta inferir por keywords del título (`git → git.jpg`, `node → nodejs.jpg`, `sql/mysql/database → bbdd.jpg`).
4. Fallback final: `nodejs.jpg` o `aprendiendo.png`.

Garantiza que ningún `<img>` queda roto aunque la columna `curso.imagen` sea `NULL` o apunte a un fichero borrado.

#### `documento_preview.php`

Función `matrixcoders_documento_archivo_subido($documento)` — parsea el campo `contenido` de un documento (donde `nubeApi` guarda el archivo subido, ver Bloque 3 §3.5) y devuelve un array con:

- `public_path`, `absolute_path`, `exists`, `original_name`, `type_label`, `extension`.
- `preview_type`: `iframe` para PDF, `image` para PNG/JPG/etc., `video` para MP4/WebM/Ogg, `audio` para MP3/WAV/OGA, `download` para el resto.
- `notes`: el contenido textual del documento, separado de los metadatos del archivo (split por línea en blanco doble).

La vista `ver_documento.php` lo usa para elegir cómo previsualizar el archivo embebido.

### 9.4 Esquema de base de datos

El esquema se reparte en tres scripts SQL ejecutados por orden desde `Database::connect()`:

1. **`init.sql`** — tablas fundacionales del proyecto.
2. **`migrate.sql`** — añadidos posteriores ejecutados en cada conexión (idempotente, usa `CREATE TABLE IF NOT EXISTS`).
3. **`crm_migrate.sql`** — todas las tablas que necesita el CRM.

A continuación el catálogo agrupado por dominio.

#### 9.4.1 Identidad y configuración

- `usuario` — campos básicos + perfil extendido + `rol` (`USUARIO|INSTRUCTOR|MODERADOR|ADMINISTRADOR`) + flags `es_superadmin`, `es_moderador`. Migrado por `fixRolConstraint` del CRM.
- `usuario_preferencias` — preferencias avanzadas separadas (extensible sin migrar `usuario`).
- `usuario_google` — `(usuario_id, google_id, google_email, google_nombre, vinculado_en)`. Vinculación con Google sin login OAuth.

#### 9.4.2 Plantillas, carpetas, documentos

- `plantilla (id, nombre, categoria, contenido)` — plantillas de documento.
- `carpeta (id, usuario_id, padre_id, nombre)` — soporta carpetas anidadas vía `padre_id` autorreferencial.
- `documento (id, usuario_id, carpeta_id, plantilla_id, titulo, contenido, estado, destacado)` — notas + archivos (metadata embebida en `contenido`).

#### 9.4.3 Cursos y temario

- `curso` — `titulo`, `descripcion`, `info_extra`, `que_aprenderas`, `imagen`, `destacado`, `precio`, `nivel`, `categoria`, `duracion_min`, `estudiantes`, `instructor_id`, `activo`, `orden`, `apuntes_json`.
- `unidad (id, curso_id, titulo, orden)`.
- `leccion (id, unidad_id, titulo, descripcion, video_url, orden, apuntes)`.
- `leccion_recurso` — recursos descargables del instructor.
- `leccion_notebook (leccion_id, notebook_url)` — URL de NotebookLM.
- `leccion_apuntes_ia (leccion_id, contenido, generado_en)` — caché de apuntes generados por Gemini.
- `leccion_vista (usuario_id, leccion_id, visto_at)` — progreso del alumno.
- `nota (usuario_id, leccion_id, contenido, updated_at)` — notas personales.

#### 9.4.4 Matrículas, tareas y exámenes

- `matricula (usuario_id, curso_id, fecha, estado, creado_en)` — `estado ∈ {activa, completado, revocada}`. `UNIQUE(usuario_id, curso_id)`.
- `tarea (id, curso_id, leccion_id, titulo, descripcion, fecha_limite)`.
- `entrega (id, tarea_id, usuario_id, contenido, nota, entregado_en)`.
- `tarea_entregable (id, unidad_id, titulo, descripcion)` y `entrega_entregable (id, tarea_id, alumno_id, respuesta_texto, archivo_path, nota, revisado, entregado_en, comentario)`.
- `examen (id, curso_id, titulo, descripcion, nota_minima, tipo, fecha_entrega, modo_entrega)` con `UNIQUE(curso_id, tipo)`.
- `pregunta (id, examen_id, enunciado, orden)`, `opcion (id, pregunta_id, texto, correcta, orden)`.
- `resultado_examen (id, usuario_id, examen_id, nota, aprobado, realizado_en, intentos)`.
- `tarea_practica (id, curso_id, titulo, descripcion, orden)` y `entrega_practica (id, tarea_id, alumno_id, curso_id, respuesta_texto, archivo_path, nota, revisado, creado_en)`.
- `certificado (id, usuario_id, curso_id, emitido_en, codigo)`.

#### 9.4.5 Mensajería y notificaciones

- `mensaje (id, emisor_id, receptor_id, asunto, cuerpo, leido, enviado_en, reply_to_id, hilo_id)`.
- `incidencia (id, usuario_id, asunto, cuerpo, estado, prioridad, asignado_a, creado_en, cerrado_en, actualizado_en)`.
- `incidencia_respuesta (id, incidencia_id, usuario_id, mensaje, creado_en)`.
- `notificacion (id, usuario_id, tipo, titulo, cuerpo, leido, url_accion, ref_id, creado_en)` — sin el `CHECK` restrictivo gracias a la migración `_mig_notif_v2`.
- `mensaje_curso` — mensajes específicos asociados a un curso.

#### 9.4.6 Pagos, suscripciones y campañas

- `suscripcion (usuario_id, plan, status)` — un registro por usuario.
- `pago (id, usuario_id, …)` — pagos sueltos (registros internos).
- `campana_crm (id, titulo, descripcion, tipo, audiencia, dias_registro, descuento_pct, fecha_inicio, fecha_fin, activa)`.
- `campana_curso (campana_id, curso_id, descuento)`.

#### 9.4.7 Calendario y eventos

- `evento_usuario (id, usuario_id, titulo, descripcion, fecha_inicio, fecha_fin, tipo, color, todo_el_dia)`.

#### 9.4.8 Auditoría y soporte

- `crm_actividad (id, usuario_id, tipo, titulo, creado_en)` — log del staff.
- `curso_instructor (curso_id, instructor_id)` — relación many-to-many opcional.
- Tablas marca de migraciones: `_mig_examen_tipo`, `_mig_notif_v2`.

### 9.5 Convenciones y patrones SQL

- **Funciones de fecha:** `datetime('now')`, `date('now','-3 days')`, `strftime('%Y-%m-%d', col)` — todo SQLite, no MySQL. Esto significa que migrar a MySQL exigiría tocar muchas consultas.
- **Upserts:** `INSERT … ON CONFLICT(uniques) DO UPDATE SET …` (SQLite 3.24+). Usado en `nota`, `matricula`, `usuario_google`, `suscripcion`.
- **`INSERT OR IGNORE`** para inserts opcionales que no deben fallar si ya existen (superadmin, certificados, marca de lección vista).
- **`INSERT OR REPLACE`** para registros con clave única que sí queremos sustituir (`leccion_apuntes_ia`).
- **Foreign keys** activadas con `PRAGMA foreign_keys = ON`. Usadas con `ON DELETE CASCADE` (limpia tareas/entregas al borrar un curso) y `ON DELETE SET NULL` (preserva documentos cuando se borra la carpeta).
- **Idempotencia:** todas las notificaciones automáticas se generan con `LEFT JOIN notificacion … IS NULL`. Las migraciones SQL son `CREATE TABLE IF NOT EXISTS` y los `ALTER TABLE` se enrollan en try/catch.

### 9.6 Patrones recurrentes en los modelos

- **"Set" via `array_flip`:** `Curso::getLeccionesVistas` y `DetalleCursoController` convierten un array de IDs a `[id => true]` para hacer `isset()` en lugar de `in_array()` (O(1) vs O(n)).
- **Subconsultas correlacionadas** para enriquecer un listado en una sola pasada (cursos en progreso del dashboard, mis-cursos, calendario). Trade-off: legibilidad vs número de consultas.
- **Modelos con doble método ownership/sin ownership:** `Documento::obtenerPorId` (libre) vs `obtenerPorIdYUsuario` (filtra). La elección está en el controlador según el caso de uso.
- **Filtros por estado:** modelos como `Tarea::obtenerPanelUsuario` derivan un `estado_visual` desde la fecha límite + entregas, evitando que cada vista repita la misma lógica.

---

## Bloque 10 — Frontend: CSS, JS, vistas y assets

El frontend de MatrixCoders es **server-side rendered con PHP**, sin framework JS. Bootstrap 5.3 desde CDN aporta utilidades y componentes; el resto es CSS propio modular y JavaScript vanilla embebido en las vistas o en `public/js/`.

### 10.1 Directorios

```
public/
├── css/                Hojas de estilo modulares por sección (ver tabla §10.2)
├── js/
│   └── crm.js         Único JS externo (CRM). El resto vive inline en vistas.
├── img/                Iconos y miniaturas
│   ├── logo.png, hogar.png, usuario.png, lupa.png, ...
│   └── cursos/        Miniaturas reutilizables (bbdd, git, nodejs)
└── uploads/
    ├── documentos/    Subidos por usuarios (nube)
    ├── fotos/         Avatares de perfil
    ├── practicos/     Entregas del examen práctico
    └── entregables/   Entregas de tareas por unidad

app/views/
├── layout/            Parciales compartidos (header, sidebar, footer)
├── auth/              login, register
├── dashboard/         index, documentos, ver_documento, documento_compartido, tareas
├── cursos/            home (catálogo)
├── detallecurso/      vista de detalle con matriculación y temario
├── leccion/           reproductor + sidebar + recursos
├── examen/            bloqueado, resultado, acceso_perdido, completado
├── mis-cursos/        listado del alumno
├── tarea_entregable/  vista de entrega por unidad
├── carrito/           index, pago_ok
├── suscripciones/     index, pago_ok
├── upgrade/           página promocional de plan
├── calendario/        FullCalendar + Smart slots + Skills Radar
├── notificaciones/    listado paginado
├── buzon/             maestro/detalle + composer
├── perfil/            edición + estadísticas
├── ajustes/           preferencias + cambio password + baja
├── chatbot/           UI del asistente Oráculo
├── buscar/            grid filtrable
└── crm/               panel completo del staff (login, layout, secciones)
```

### 10.2 Hojas de estilo

Cada CSS está pensado como módulo independiente. Las vistas importan **solo** los CSS que necesitan, manteniendo el bundle por página pequeño.

| Archivo                                | Propósito                                                                 |
|----------------------------------------|---------------------------------------------------------------------------|
| `header.css`                           | Header principal del portal (logo, nav, perfil, campana).                |
| `footer.css`                           | Pie de página común.                                                     |
| `sidebar.css`                          | Sidebar del área privada (dashboard, calendario, etc.).                  |
| `inicio.css`                           | Home pública: hero, grid de cursos destacados.                            |
| `auth.css`                             | Login + registro, layout `auth-grid` de dos columnas, formularios `input-mc`. |
| `dashboard.css`                        | El más grande (≈ 2 800 líneas) — calendario, widgets, perfil profesional. |
| `mis-cursos.css`                       | Tarjetas con barra de progreso y filtros.                                 |
| `carrito.css`                          | Cesta + checkout.                                                         |
| `suscripciones.css`                    | Tabla comparativa de planes + CTAs.                                      |
| `perfil.css`                           | Edición de perfil, fotografía, badges.                                    |
| `chatbot-widget.css`                   | Burbuja flotante de Oráculo (botón + panel desplegable).                 |
| `crm.css`                              | Sistema visual completo del panel de administración.                     |

Convenciones:

- Prefijos `mc-` o `auth-` para clases propias (`mc-container`, `auth-card`, `input-mc`, `label-mc`). Coexisten con utilidades Bootstrap (`form-control`, `btn`, `alert-*`).
- Paleta principal: verde sage `#6B8F71` (color de marca, presente en el calendario, botones primarios y badges).
- Cada CSS contiene su propio sistema responsive (media queries internas), no hay un breakpoint global compartido.

### 10.3 JavaScript

#### `public/js/crm.js`

Único JS externo del proyecto, exclusivamente para el CRM. Funciones:

- Toggle del sidebar con persistencia en `localStorage` (`crm_sidebar`), respetando modo móvil/escritorio.
- Inicialización de modales, badges, accesos rápidos.
- Helpers de `fetch` hacia `?crm_api=1&action=…`.
- Drag-and-drop para reordenar unidades/lecciones del editor (usa la API HTML5 Drag & Drop nativa).
- Polling ligero del badge de notificaciones.

#### JavaScript inline

El resto del JS está embebido en las propias vistas:

- **Vista del carrito:** `fetch` a `?url=carrito-añadir|carrito-eliminar` y actualización del DOM con los `total_fmt`/`subtotal_fmt` devueltos.
- **Reproductor de lección:** marcar/desmarcar visto, guardar nota con debounce, chat RAG, "guardar en nube".
- **Calendario:** FullCalendar.js + Chart.js (radar) cargados desde CDN. Los handlers de creación/edición de eventos personales hacen `fetch` JSON a `?url=api-eventos-usuario`.
- **Chatbot:** burbuja flotante (incluida en el footer si hay sesión) y página dedicada. Mantiene historial visual y dispara `fetch` a `?url=chatbot`.
- **Búsqueda:** autocomplete con `input` event + `fetch` a `?url=autocomplete`.
- **Buzón:** maestro/detalle con `fetch` a `?url=buzon&action=…`.
- **Notificaciones:** la campana del header consume `?url=api-notificaciones` cada cierto tiempo y marca leídas.
- **Vinculación Google:** carga `accounts.google.com/gsi/client` (Google Identity Services). Tras pulsar el botón Google, el `credential` se envía por POST a `?url=vincular-google&accion=vincular`.

> Decisión consistente: el proyecto evita bundlers. Cualquier código que vive en un par de vistas se escribe inline; solo el CRM, por tamaño, se separa.

### 10.4 Layouts compartidos

#### `views/layout/header.php`

Se incluye al inicio de la mayoría de vistas públicas y autenticadas. Lo característico:

- Comprueba `$_SESSION['usuario_id']` + rol `USUARIO` para decidir si hay alumno logueado (`$logged`).
- Renderiza un nav distinto para visitante vs alumno:
  - Visitante: botones Login/Registro.
  - Alumno: avatar (con `foto` si existe o fallback iconográfico), badge de notificaciones, dropdown con accesos a Perfil, Ajustes, Logout.
- Menú móvil colapsable.
- Carga assets en función de la ruta (`header.css`, Bootstrap, etc.).

#### `views/layout/sidebar.php`

Incluido en las vistas del área privada (dashboard, calendario, mis-cursos, nube, perfil, ajustes…). Detecta la sección activa desde `$_GET['url']`:

```php
$isWorkspace  = in_array($currentUrl, ['dashboard']);
$isNube       = in_array($currentUrl, ['nube','mis-documentos','documento']);
$isCalendario = in_array($currentUrl, ['calendario']);
$isCuenta     = in_array($currentUrl, ['perfil','ajustes']);
$isMisCursos  = in_array($currentUrl, ['mis-cursos']);
$isChatbot    = in_array($currentUrl, ['chatbot']);
```

Y aplica `aria-current="page"` / clases activas. El botón `#sidebarToggle` colapsa/expande con persistencia en `localStorage`.

Importa `PlanHelper` para mostrar el badge del plan activo (`PlanHelper::etiqueta`, `PlanHelper::badgeClass`).

#### `views/layout/footer.php`

Footer global. Si hay sesión, inserta el **widget flotante de Oráculo** (botón circular `#cbFloatBtn` + panel inline). Este widget hace que el chatbot esté disponible desde cualquier página autenticada sin tener que navegar a `?url=chatbot`.

### 10.5 Sistema de imágenes y assets

- **Logo** y emblemas: `public/img/logo.png` + iconos PNG planos. Coexisten con SVG inline en muchas vistas (por flexibilidad de color con `currentColor`).
- **Miniaturas de cursos:** `public/img/cursos/{bbdd,git,nodejs}.jpg`. El helper [curso_imagen.php](app/helpers/curso_imagen.php) resuelve cualquier `curso.imagen` a uno de estos ficheros (o a `aprendiendo.png` como último recurso).
- **Avatares:** `public/uploads/fotos/avatar_<uid>_<uniqid>.<ext>`. Si el usuario no ha subido foto, las vistas pintan un fallback con la inicial del nombre.

### 10.6 Vistas destacadas (resumen)

- `cursos/index.php` — grid responsive con tarjetas de curso (uses Bootstrap utilities + `mc-*` y `inicio.css`).
- `detallecurso/index.php` — hero con título, precio (con descuento tachado si hay campaña), CTA de matriculación, temario plegable, lección activa embebida si está matriculado.
- `leccion/index.php` — sidebar fijo con unidades + reproductor central + paneles laterales (nota, recursos del instructor, apuntes IA con botón "Regenerar", chat RAG).
- `calendario/index.php` — FullCalendar maquetado a pantalla completa, panel lateral con racha, smart slots y Chart.js radar.
- `crm/layout/base.php` — wrapper con `crmSidebar`/`crmMain`/`crmOverlay` ids gobernados por `crm.js`.
- `examen/resultado.php` — pantalla de resultado con tarjeta de nota, intentos restantes y, si aplica, código de certificado.

### 10.7 Accesibilidad y experiencia

- Atributos `aria-label` y `aria-current` presentes en headers/sidebars.
- `<meta name="viewport">` en cada vista.
- Inputs con `autocomplete="name|email"` en formularios.
- Los formularios POST destructivos (eliminar cuenta) tienen confirmaciones inline (texto literal + contraseña).

### 10.8 Diagrama de composición de una vista

```
┌────────────────────────────────────────────────────────┐
│  Bootstrap CDN  +  CSS modular (header.css, …)         │
├────────────────────────────────────────────────────────┤
│  views/layout/header.php       (nav, badge, dropdown)   │
├──────────────┬─────────────────────────────────────────┤
│              │                                          │
│  sidebar.php │   views/<seccion>/index.php             │
│  (privadas) │   (HTML + JS inline si aplica)           │
│              │                                          │
├──────────────┴─────────────────────────────────────────┤
│  views/layout/footer.php   (+ widget Oráculo si auth)   │
└────────────────────────────────────────────────────────┘
```

---

## Bloque 11 — Tecnologías externas, integraciones y dependencias

Cierre del documento: recapitulación de cada tecnología, librería o servicio externo que el proyecto utiliza, dónde se configura, cómo se llama y qué pasa si falta.

### 11.1 Plataforma de ejecución

- **PHP 8.1+** (uso de `match`, `enum`-like literals, `readonly`, tipos union `array|false`, `str_contains`).
- **XAMPP / Apache** en Windows, con `mod_rewrite` no requerido — la app funciona con `?url=` clásico, sin pretty URLs.
- **PowerShell / Bash** indistintamente para mantenimiento; nada del runtime depende del shell.
- **Document root** apuntando a `public/`. El resto del repo queda fuera de la vía HTTP por seguridad.

### 11.2 Base de datos

- **SQLite 3.24+** embebido en `app/data/database.sqlite` (acceso vía PDO).
- PRAGMAs activos por conexión: `foreign_keys=ON`, `journal_mode=WAL`.
- Esquema inicial en `app/data/init.sql`, ampliaciones en `migrate.sql` y `crm_migrate.sql`, aplicadas idempotentemente en cada `Database::connect()`.
- Sin necesidad de servidor MySQL/MariaDB pese a lo que indica el README. Sustituir por MySQL exigiría reescribir las funciones de fecha (`datetime`, `strftime`, `date('now','+N days')`) y los upserts `ON CONFLICT`.

### 11.3 Composer y librerías PHP

`composer.json`:

```json
{ "require": {
    "stripe/stripe-php": "13.0",
    "vlucas/phpdotenv": "^5.6"
}}
```

- **stripe/stripe-php 13.0** — SDK oficial. Usada por `CarritoController` y `SuscripcionController` (Bloque 5). API: `\Stripe\Stripe::setApiKey()`, `\Stripe\Checkout\Session::create|retrieve`, `\Stripe\Webhook::constructEvent`, `\Stripe\Event::constructFrom`. Se carga con `require_once __DIR__ . '/../../vendor/autoload.php'` justo antes del uso (no en `config.php`), para evitar el coste si no hay pagos.
- **vlucas/phpdotenv ^5.6** — declarada como dependencia pero **no instanciada en el código actual**. `config.php` implementa su propio parser ligero. Si se quiere usar `Dotenv\Dotenv::createImmutable(...)`, basta con sustituir el bloque IIFE inicial de `config.php`.

### 11.4 Frontend desde CDN

Cargado por URL en las vistas, sin gestor de paquetes:

- **Bootstrap 5.3.x** (CSS + bundle JS). Usado para utilidades, modales, dropdowns, alerts.
- **FullCalendar.js** (vista `calendario/index.php`) para el planificador.
- **Chart.js** para el radar de habilidades del calendario.
- **Google Identity Services** (`accounts.google.com/gsi/client`) para la vinculación de cuenta Google.

### 11.5 Variables de entorno

Leídas con `getenv()` o `$_ENV` después de la carga manual del `.env`:

| Variable                 | Usada por                                           | Notas                                                                 |
|--------------------------|-----------------------------------------------------|-----------------------------------------------------------------------|
| `STRIPE_SECRET_KEY`      | `CarritoController` (carrito de cursos)             | Si vacía → modo simulado (matricula directamente).                    |
| `STRIPE_WEBHOOK_SECRET`  | `CarritoController::webhook`                        | Si vacía → reconstruye el evento sin verificar firma (modo dev).      |
| `GEMINI_API_KEY`         | `GeminiService` (constante, definida en `config.php`)| Si vacía → respuestas `{ok:false,error:'API key no configurada'}`.    |
| `GOOGLE_CLIENT_ID`       | Vistas que cargan Google Identity Services           | Si vacía → el botón Google no funciona.                               |

> `SuscripcionController` lee `STRIPE_SECRET_KEY` con `defined()` en lugar de `getenv()` — discrepancia que conviene unificar.

### 11.6 Servicios externos (cuando hay credenciales)

- **Stripe** — `dashboard.stripe.com/test/apikeys` para claves, `stripe listen --forward-to localhost/.../stripe-webhook` durante desarrollo.
- **Google Gemini (Google AI Studio)** — `https://aistudio.google.com/app/apikey`. Endpoint usado: `gemini-1.5-flash:generateContent` (admite vídeo de YouTube directamente como input para apuntes).
- **Google Cloud Console (OAuth)** — `https://console.cloud.google.com` para el `GOOGLE_CLIENT_ID` con Authorized JavaScript origins apuntando a `http://localhost` durante desarrollo.
- **NotebookLM** — no se consume vía API. La integración consiste en almacenar `leccion_notebook.notebook_url` (lo configura el instructor desde el CRM) y abrir el enlace en el navegador del alumno; la sesión Google del navegador es la que abre el notebook.

### 11.7 Almacenamiento de ficheros

Todo en `public/uploads/`:

- `documentos/u<uid>_<uniqid>.<ext>` — nube personal (≤ 50 MB, extensiones whitelisted).
- `fotos/avatar_<uid>_<uniqid>.<ext>` — avatares (≤ 2 MB, JPG/PNG/GIF/WebP).
- `practicos/u<uid>_t<tareaId>_<time>.<ext>` — entregas del examen práctico.
- `entregables/` — entregas por unidad.

Las extensiones permitidas se declaran array por array dentro de cada controlador (`DashboardController::nubeApi`, `ExamenPracticoController`, `TareaEntregableController`). No hay una whitelist central.

### 11.8 Seguridad — resumen de prácticas

- **Prepared statements** uniformes (PDO con placeholders posicionales o nombrados).
- **Hashing con bcrypt** (`password_hash` + `password_verify`) en login, registro, cambios de contraseña y baja de cuenta.
- **Verificación de propiedad (ownership)** en consultas de documentos, eventos, notificaciones, mensajes.
- **Sesiones:** `session_regenerate_id(true)` en el login del CRM (defensa frente a session fixation). `crm-logout` borra la cookie explícitamente.
- **Firmas:** `hash_equals` para tokens compartidos (timing-safe); `\Stripe\Webhook::constructEvent` para webhooks.
- **Escape de salida:** `htmlspecialchars` en flash messages y datos repueblados.
- **Lo que falta** en la versión actual (para considerar en producción):
  - Tokens CSRF en los formularios.
  - Validación de firma JWT de Google Identity Services.
  - Rotación del secreto `mc_share_secret` (hardcoded en `DashboardController::buildShareToken`).
  - Rate limiting en login y endpoints AJAX.
  - HTTPS forzado (la app construye URLs con scheme dinámico, pero no fuerza redirección).

### 11.9 Configuración local mínima

Para arrancar el proyecto en limpio:

1. Clonar el repo en `C:/xampp/htdocs/MatrixCoders` (o ajustar `BASE_URL`).
2. `composer install` para los vendors.
3. Copiar `.env.example` a `.env` y rellenar claves Stripe si se quieren probar pagos reales.
4. Editar `app/config.php` para introducir `GEMINI_API_KEY` y `GOOGLE_CLIENT_ID` si se quiere usar IA / vinculación Google.
5. Arrancar XAMPP. La primera petición crea `app/data/database.sqlite` automáticamente con el superadmin `isidoro@admin.com` / contraseña `usuario`.
6. Visitar `http://localhost/matrixcoders/public/` (alumno) o `http://localhost/matrixcoders/admin/` (CRM).

### 11.10 Resumen del stack final

| Capa                 | Tecnología                                                         |
|----------------------|---------------------------------------------------------------------|
| Lenguaje servidor    | PHP 8.1+                                                           |
| Patrón arquitectónico| MVC manual + front controller                                       |
| Acceso a datos       | PDO + SQLite                                                       |
| Servidor             | Apache (XAMPP)                                                     |
| Frontend             | HTML5 + CSS3 modular + JS vanilla + Bootstrap 5.3 (CDN)            |
| Calendario           | FullCalendar.js (CDN)                                              |
| Gráficos             | Chart.js (radar de habilidades)                                    |
| Pagos                | Stripe Checkout (modo `payment` + `subscription`) + webhooks       |
| IA                   | Google Gemini 1.5 Flash (apuntes, RAG, chatbot)                     |
| Integraciones        | Google Identity Services + NotebookLM (URLs vinculadas)            |
| Configuración        | `.env` + constantes en `config.php` + `vlucas/phpdotenv` (instalado, no usado) |
| Build / Deploy       | Ninguno — copia directa de archivos                                 |

Con esto, la documentación cubre los **once bloques** del programa: front controller, autenticación, dashboard, cursos y exámenes, pagos, calendario y comunicaciones, perfil/IA/búsqueda, CRM, modelos/esquema, frontend y tecnologías externas. Cualquier funcionalidad nueva debería poder ubicarse en uno de los bloques existentes y enriquecerlo, manteniendo este documento vivo.

