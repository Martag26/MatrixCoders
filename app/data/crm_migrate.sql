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
