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

-- Añadir columna intentos a resultado_examen si no existe (idempotente vía INSERT OR IGNORE no aplica en ALTER,
-- SQLite no soporta IF NOT EXISTS en ALTER TABLE; se maneja con try/catch en la aplicación)
-- La columna se añade con una query defensiva ejecutada desde PHP al conectar (ver db.php o ExamenController)

-- Columna intentos para resultado_examen
-- SQLite: ALTER TABLE no permite IF NOT EXISTS, así que se hace desde PHP con try/catch

-- Notebook de NotebookLM asociado a una lección (URL externa, gestionado por instructor)
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
