-- =============================================================================
-- CRM Migration — Tablas y datos para el panel de administración
-- Idempotente: CREATE TABLE IF NOT EXISTS, INSERT OR IGNORE
-- =============================================================================

-- Incidencias (soporte/tickets moderados)
CREATE TABLE IF NOT EXISTS incidencia (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id  INTEGER NOT NULL,
    asignado_a  INTEGER DEFAULT NULL,
    asunto      TEXT    NOT NULL,
    estado      TEXT    NOT NULL DEFAULT 'abierta'
                CHECK (estado IN ('abierta','en_proceso','cerrada')),
    prioridad   TEXT    NOT NULL DEFAULT 'normal'
                CHECK (prioridad IN ('baja','normal','alta','urgente')),
    creado_en   TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_a) REFERENCES usuario(id) ON DELETE SET NULL
);

-- Respuestas a incidencias
CREATE TABLE IF NOT EXISTS incidencia_respuesta (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    incidencia_id INTEGER NOT NULL,
    usuario_id    INTEGER NOT NULL,
    mensaje       TEXT    NOT NULL,
    creado_en     TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (incidencia_id) REFERENCES incidencia(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id)   REFERENCES usuario(id) ON DELETE CASCADE
);

-- Relación campaña ↔ curso (descuento porcentual por curso)
CREATE TABLE IF NOT EXISTS campana_curso (
    campana_id INTEGER NOT NULL,
    curso_id   INTEGER NOT NULL,
    descuento  REAL    NOT NULL DEFAULT 10.0,
    PRIMARY KEY (campana_id, curso_id),
    FOREIGN KEY (campana_id) REFERENCES campana_crm(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id)   REFERENCES curso(id)       ON DELETE CASCADE
);

-- Mensajes internos curso (alumno ↔ instructor dentro de un curso)
CREATE TABLE IF NOT EXISTS mensaje_curso (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    curso_id        INTEGER NOT NULL,
    remitente_id    INTEGER NOT NULL,
    destinatario_id INTEGER DEFAULT NULL,
    cuerpo          TEXT    NOT NULL,
    leido           INTEGER NOT NULL DEFAULT 0,
    creado_en       TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (curso_id)        REFERENCES curso(id)   ON DELETE CASCADE,
    FOREIGN KEY (remitente_id)    REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES usuario(id) ON DELETE SET NULL
);

-- Múltiples instructores por curso (junction table)
CREATE TABLE IF NOT EXISTS curso_instructor (
    curso_id    INTEGER NOT NULL,
    usuario_id  INTEGER NOT NULL,
    PRIMARY KEY (curso_id, usuario_id),
    FOREIGN KEY (curso_id)   REFERENCES curso(id)   ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Migrar instructor_id existente a la junction table (idempotente)
INSERT OR IGNORE INTO curso_instructor (curso_id, usuario_id)
SELECT id, instructor_id FROM curso WHERE instructor_id IS NOT NULL;

-- Recursos descargables por lección (apuntes PDF, actividades, enlaces, etc.)
CREATE TABLE IF NOT EXISTS leccion_recurso (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    leccion_id  INTEGER NOT NULL,
    nombre      TEXT    NOT NULL,
    tipo        TEXT    NOT NULL DEFAULT 'link'
                CHECK (tipo IN ('pdf','doc','zip','link','actividad','video')),
    url_o_ruta  TEXT    NOT NULL,
    descripcion TEXT,
    descargable INTEGER NOT NULL DEFAULT 1,
    orden       INTEGER NOT NULL DEFAULT 0,
    creado_en   TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Entregas del examen práctico por alumno
CREATE TABLE IF NOT EXISTS entrega_practica (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    alumno_id       INTEGER NOT NULL,
    tarea_id        INTEGER NOT NULL,
    curso_id        INTEGER NOT NULL,
    respuesta_texto TEXT,
    archivo         TEXT,
    nota            REAL    DEFAULT NULL,
    feedback        TEXT,
    revisado        INTEGER NOT NULL DEFAULT 0,
    revisado_por_id INTEGER DEFAULT NULL,
    entregado_en    TEXT    NOT NULL DEFAULT (datetime('now')),
    revisado_en     TEXT    DEFAULT NULL,
    UNIQUE(alumno_id, tarea_id)
);

-- Tareas del examen práctico
CREATE TABLE IF NOT EXISTS tarea_practica (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    curso_id  INTEGER NOT NULL,
    titulo    TEXT    NOT NULL,
    enunciado TEXT,
    tipo      TEXT    NOT NULL DEFAULT 'texto'
              CHECK (tipo IN ('texto','codigo','diseno','proyecto')),
    puntos    REAL    NOT NULL DEFAULT 10,
    criterios TEXT,
    orden     INTEGER NOT NULL DEFAULT 0,
    creado_en TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Tareas entregables del curso (asignadas a una unidad, no al examen práctico)
CREATE TABLE IF NOT EXISTS tarea_entregable (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    curso_id     INTEGER NOT NULL,
    unidad_id    INTEGER DEFAULT NULL,
    titulo       TEXT    NOT NULL,
    descripcion  TEXT    DEFAULT NULL,
    fecha_limite TEXT    DEFAULT NULL,
    creado_en    TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (curso_id)  REFERENCES curso(id)  ON DELETE CASCADE,
    FOREIGN KEY (unidad_id) REFERENCES unidad(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS entrega_entregable (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    tarea_id     INTEGER NOT NULL,
    alumno_id    INTEGER NOT NULL,
    respuesta    TEXT    DEFAULT NULL,
    archivo      TEXT    DEFAULT NULL,
    nota         REAL    DEFAULT NULL,
    feedback     TEXT    DEFAULT NULL,
    revisado     INTEGER NOT NULL DEFAULT 0,
    entregado_en TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE(tarea_id, alumno_id),
    FOREIGN KEY (tarea_id)  REFERENCES tarea_entregable(id) ON DELETE CASCADE,
    FOREIGN KEY (alumno_id) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Notebook de NotebookLM asociado a una lección (URL externa, gestionado por instructor)
-- Columna intentos para resultado_examen se añade desde PHP con try/catch (SQLite no soporta IF NOT EXISTS en ALTER TABLE)
CREATE TABLE IF NOT EXISTS leccion_notebook (
    leccion_id   INTEGER PRIMARY KEY,
    notebook_url TEXT    NOT NULL,
    creado_en    TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (leccion_id) REFERENCES leccion(id) ON DELETE CASCADE
);

-- Log de actividad CRM (para el feed del dashboard)
CREATE TABLE IF NOT EXISTS crm_actividad (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER DEFAULT NULL,
    tipo       TEXT    NOT NULL DEFAULT 'info',
    titulo     TEXT    NOT NULL,
    detalle    TEXT    DEFAULT NULL,
    creado_en  TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE SET NULL
);

-- =============================================================================
-- Datos de ejemplo: examen práctico del curso 11 (Bases de datos)
-- =============================================================================

-- Examen práctico consolidado del curso 11 (uno por curso)
INSERT OR IGNORE INTO tarea_practica (id, curso_id, titulo, enunciado, tipo, puntos, criterios, orden) VALUES
(1, 11, 'Examen práctico: Diseño y consulta de una base de datos',
 'Pondrás en práctica todo lo aprendido en el curso aplicándolo a un caso real: una base de datos para gestionar una biblioteca universitaria.

1) DISEÑO RELACIONAL
Diseña el modelo entidad-relación (ER) con al menos 5 entidades, sus atributos y las relaciones entre ellas. Indica claves primarias, claves foráneas y la cardinalidad de cada relación.

2) DDL — CREACIÓN DEL ESQUEMA
Escribe el script SQL completo (CREATE TABLE) que genera el esquema diseñado, respetando los tipos de datos y las restricciones de integridad.

3) CONSULTAS SQL
Sobre el esquema anterior escribe las siguientes consultas:
  · Los 5 libros más prestados en los últimos 6 meses.
  · Alumnos con más de 3 préstamos activos.
  · Tiempo medio de préstamo agrupado por categoría de libro.
  · Libros que nunca han sido prestados (usa LEFT JOIN o subconsulta).

4) OPTIMIZACIÓN
Identifica al menos un índice o vista que mejoraría el rendimiento del esquema y justifica por qué.

ENTREGABLES:
  · Un documento (PDF o ZIP) con el diagrama ER, el script DDL y las consultas SQL.
  · Breve explicación (máx. 1 página) de las decisiones de diseño y la optimización propuesta.',
 'proyecto', 10,
 'Modelo ER correcto (2 pts) · Script DDL ejecutable (2 pts) · Consultas SQL correctas (4 pts) · Optimización justificada (2 pts)',
 1);

-- Todas las lecciones del curso 11 como vistas por el usuario 18 (para poder acceder al examen en pruebas)
INSERT OR IGNORE INTO leccion_vista (usuario_id, leccion_id, visto_at) VALUES
(18, 19, '2026-05-01 09:00:00'),
(18, 20, '2026-05-01 09:10:00'),
(18, 21, '2026-05-01 09:20:00'),
(18, 22, '2026-05-01 09:30:00'),
(18, 23, '2026-05-01 09:40:00'),
(18, 24, '2026-05-01 09:50:00'),
(18, 25, '2026-05-01 10:00:00'),
(18, 26, '2026-05-01 10:10:00'),
(18, 27, '2026-05-01 10:20:00'),
(18, 28, '2026-05-01 10:30:00'),
(18, 29, '2026-05-01 10:40:00'),
(18, 30, '2026-05-01 10:50:00'),
(18, 31, '2026-05-01 11:00:00'),
(18, 32, '2026-05-01 11:10:00'),
(18, 33, '2026-05-01 11:20:00'),
(18, 34, '2026-05-01 11:30:00'),
(18, 35, '2026-05-01 11:40:00'),
(18, 36, '2026-05-01 11:50:00'),
(18, 37, '2026-05-01 12:00:00'),
(18, 38, '2026-05-01 12:10:00'),
(18, 39, '2026-05-01 12:20:00'),
(18, 40, '2026-05-01 12:30:00'),
(18, 41, '2026-05-01 12:40:00'),
(18, 42, '2026-05-01 12:50:00');

-- =============================================================================
-- Datos de prueba: mensajería bidireccional y soporte
-- FK desactivadas temporalmente: los IDs de prueba pueden no existir en esta BD
-- =============================================================================
PRAGMA foreign_keys = OFF;

INSERT OR IGNORE INTO mensaje (id, emisor_id, receptor_id, asunto, cuerpo, enviado_en, leido)
VALUES (1, 1, 18, 'Bienvenido a la plataforma',
        'Hola, bienvenido a MatrixCoders. Si tienes alguna duda no dudes en contactarnos.',
        '2026-05-01 08:00:00', 0);

INSERT OR IGNORE INTO incidencia (id, usuario_id, asunto, estado, prioridad, creado_en)
VALUES (1, 18, 'Problema al acceder al curso', 'abierta', 'normal', '2026-05-01 08:30:00');

INSERT OR IGNORE INTO incidencia_respuesta (id, incidencia_id, usuario_id, mensaje, creado_en)
VALUES (1, 1, 1, 'Hola, estamos revisando tu incidencia. Te respondemos en breve.', '2026-05-01 08:45:00');

PRAGMA foreign_keys = ON;
