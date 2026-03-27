-- =============================================================================
-- MatrixCoders — Base de datos SQLite
-- Se ejecuta automáticamente la primera vez que arranca la aplicación.
-- =============================================================================

PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;

-- -----------------------------------------------------------------------------
-- usuario
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuario (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre     TEXT    NOT NULL,
    email      TEXT    NOT NULL UNIQUE,
    contraseña TEXT    NOT NULL,
    creado_en  TEXT    NOT NULL DEFAULT (datetime('now')),
    rol        TEXT    NOT NULL DEFAULT 'USUARIO'
               CHECK (rol IN ('USUARIO','EDITOR','ADMINISTRADOR'))
);

-- -----------------------------------------------------------------------------
-- plantilla
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS plantilla (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre    TEXT NOT NULL,
    categoria TEXT DEFAULT NULL,
    contenido TEXT NOT NULL
);

-- -----------------------------------------------------------------------------
-- carpeta  (referencia a usuario y a sí misma para subcarpetas)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS carpeta (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    padre_id   INTEGER DEFAULT NULL,
    nombre     TEXT    NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)  ON DELETE CASCADE  ON UPDATE CASCADE,
    FOREIGN KEY (padre_id)   REFERENCES carpeta(id)  ON DELETE SET NULL ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- documento
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS documento (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id   INTEGER NOT NULL,
    carpeta_id   INTEGER DEFAULT NULL,
    plantilla_id INTEGER DEFAULT NULL,
    titulo       TEXT    NOT NULL,
    contenido    TEXT    DEFAULT NULL,
    estado       TEXT    NOT NULL DEFAULT 'borrador',
    destacado    INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (usuario_id)   REFERENCES usuario(id)   ON DELETE CASCADE  ON UPDATE CASCADE,
    FOREIGN KEY (carpeta_id)   REFERENCES carpeta(id)   ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (plantilla_id) REFERENCES plantilla(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- curso  — incluye imagen, duracion_min y estudiantes que usa el código
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS curso (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo       TEXT NOT NULL,
    descripcion  TEXT DEFAULT NULL,
    precio       REAL NOT NULL DEFAULT 0.00,
    imagen       TEXT DEFAULT NULL,
    duracion_min INTEGER DEFAULT NULL,
    estudiantes  INTEGER DEFAULT NULL
);

-- -----------------------------------------------------------------------------
-- unidad
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS unidad (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    curso_id INTEGER NOT NULL,
    titulo   TEXT    NOT NULL,
    orden    INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- leccion
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leccion (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    unidad_id INTEGER NOT NULL,
    titulo    TEXT    NOT NULL,
    orden     INTEGER NOT NULL DEFAULT 1,
    video_url TEXT    DEFAULT NULL,
    FOREIGN KEY (unidad_id) REFERENCES unidad(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- tarea
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tarea (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    curso_id     INTEGER NOT NULL,
    leccion_id   INTEGER NOT NULL,
    titulo       TEXT    NOT NULL,
    fecha_limite TEXT    DEFAULT NULL,
    FOREIGN KEY (curso_id)   REFERENCES curso(id)   ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (leccion_id) REFERENCES leccion(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- matricula
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS matricula (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    curso_id   INTEGER NOT NULL,
    fecha      TEXT    NOT NULL DEFAULT (datetime('now')),
    estado     TEXT    NOT NULL DEFAULT 'activa',
    UNIQUE (usuario_id, curso_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (curso_id)   REFERENCES curso(id)   ON DELETE CASCADE ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- entrega
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS entrega (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    tarea_id      INTEGER NOT NULL,
    usuario_id    INTEGER NOT NULL,
    documento_id  INTEGER DEFAULT NULL,
    nota          REAL    DEFAULT NULL,
    entregado_en  TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE (tarea_id, usuario_id),
    FOREIGN KEY (tarea_id)     REFERENCES tarea(id)    ON DELETE CASCADE  ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id)   REFERENCES usuario(id)  ON DELETE CASCADE  ON UPDATE CASCADE,
    FOREIGN KEY (documento_id) REFERENCES documento(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- mensaje
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mensaje (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    emisor_id   INTEGER NOT NULL,
    receptor_id INTEGER NOT NULL,
    asunto      TEXT    DEFAULT NULL,
    cuerpo      TEXT    NOT NULL,
    enviado_en  TEXT    NOT NULL DEFAULT (datetime('now')),
    leido       INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (emisor_id)   REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (receptor_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- suscripcion
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS suscripcion (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id            INTEGER NOT NULL,
    plan                  TEXT    NOT NULL,
    status                TEXT    NOT NULL,
    cliente_id_stripe     TEXT    DEFAULT NULL,
    suscripcion_id_stripe TEXT    DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- pago
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pago (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    suscripcion_id INTEGER NOT NULL,
    importe        REAL    NOT NULL DEFAULT 0.00,
    estado         TEXT    NOT NULL,
    fecha          TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (suscripcion_id) REFERENCES suscripcion(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =============================================================================
-- DATOS INICIALES
-- =============================================================================

INSERT INTO usuario (id, nombre, email, contraseña, creado_en, rol) VALUES
(16, 'marta_admin',    'marta_admin@g.educaand.es',    '@w@w@',    '2026-02-24 08:00:50', 'ADMINISTRADOR'),
(17, 'isidoro_editor', 'isidoro_editor@g.educaand.es', '@w@w@',    '2026-02-24 08:00:50', 'EDITOR'),
(18, 'usuario',        'usuario@usuario.es',            'usuario',  '2026-02-24 08:00:50', 'USUARIO'),
(19, 'usuario2',       'usuario2@usuario2.es',          'usuario2', '2026-02-24 08:11:30', 'USUARIO'),
(20, 'usuario3',       'usuario3@usuario3.es',          'usuario3', '2026-02-24 08:11:30', 'USUARIO'),
(21, 'Pablo',          'Pablo@pablo.pablo',             'pablo',    '2026-03-13 12:03:47', 'USUARIO');

-- imagen y duracion_min / estudiantes añadidos respecto al SQL original
INSERT INTO curso (id, titulo, descripcion, precio, imagen, duracion_min, estudiantes) VALUES
(1, 'Desarrollo Web Completo',
    'Aprende HTML, CSS, JavaScript, PHP y MySQL desde cero hasta nivel profesional.',
    199.99, NULL, 109, 243),
(2, 'Frontend Profesional con React',
    'Domina React, hooks, rutas y consumo de APIs para crear aplicaciones modernas.',
    149.99, NULL, 95, 157),
(3, 'Backend con Node.js y Bases de Datos',
    'Construye APIs REST con Node.js, Express y conexión a bases de datos SQL.',
    179.99, NULL, 130, 198);

INSERT INTO unidad (id, curso_id, titulo, orden) VALUES
(1, 1, 'Introducción al Curso', 1),
(2, 1, 'HTML y Estructura Web', 2),
(3, 1, 'CSS y Estilos',         3);

INSERT INTO leccion (id, unidad_id, titulo, orden, video_url) VALUES
(1, 1, 'Bienvenida',             1, 'https://video-demo.com/bienvenida'),
(2, 1, 'Cómo funciona el curso', 2, 'https://video-demo.com/funcionamiento'),
(3, 2, 'Etiquetas básicas HTML', 1, 'https://video-demo.com/html-basico'),
(4, 2, 'Formularios en HTML',    2, 'https://video-demo.com/html-formularios'),
(5, 3, 'Selectores CSS',         1, 'https://video-demo.com/css-selectores'),
(6, 3, 'Flexbox y Layout',       2, 'https://video-demo.com/css-flexbox');

INSERT INTO tarea (id, curso_id, leccion_id, titulo, fecha_limite) VALUES
(1, 1, 3, 'Crear tu primera página HTML',    '2026-03-10 00:00:00'),
(2, 1, 6, 'Maquetar una página con Flexbox', '2026-03-15 00:00:00');

INSERT INTO carpeta (id, usuario_id, padre_id, nombre) VALUES
(4, 19, NULL, 'Mis Cursos'),
(5, 19, NULL, 'Entregas'),
(6, 19, NULL, 'Apuntes');

INSERT INTO documento (id, usuario_id, carpeta_id, plantilla_id, titulo, contenido, estado, destacado) VALUES
(1, 19, 6, NULL, 'Resumen HTML',       'Apuntes sobre etiquetas HTML...',  'borrador',  0),
(2, 19, 5, NULL, 'Entrega Página HTML', 'Aquí va mi ejercicio práctico...', 'publicado', 1),
(3, 19, 6, NULL, 'Notas CSS',          'Propiedades importantes de CSS...','borrador',  0);

INSERT INTO entrega (id, tarea_id, usuario_id, documento_id, nota, entregado_en) VALUES
(3, 1, 19, NULL, 8.50, '2026-02-24 08:11:38'),
(4, 2, 19, NULL, NULL, '2026-02-24 08:11:38');
