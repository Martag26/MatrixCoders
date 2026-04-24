-- Migración: sistema de evaluación, certificación y NotebookLM
-- Se ejecuta en cada conexión (CREATE TABLE IF NOT EXISTS es idempotente)

-- Caché de apuntes generados por Gemini IA a partir del vídeo de cada lección
CREATE TABLE IF NOT EXISTS leccion_apuntes_ia (
    leccion_id  INTEGER PRIMARY KEY,
    contenido   TEXT NOT NULL,
    generado_en TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (leccion_id) REFERENCES leccion(id) ON DELETE CASCADE
);

-- Vinculación de cuenta Google por usuario (para NotebookLM)
CREATE TABLE IF NOT EXISTS usuario_google (
    usuario_id    INTEGER PRIMARY KEY,
    google_id     TEXT NOT NULL,
    google_email  TEXT NOT NULL,
    google_nombre TEXT,
    vinculado_en  TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS examen (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    curso_id    INTEGER NOT NULL UNIQUE,
    titulo      TEXT    NOT NULL,
    descripcion TEXT    DEFAULT NULL,
    nota_minima REAL    NOT NULL DEFAULT 5.0,
    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pregunta (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    examen_id INTEGER NOT NULL,
    enunciado TEXT    NOT NULL,
    orden     INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (examen_id) REFERENCES examen(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS opcion (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    pregunta_id INTEGER NOT NULL,
    texto       TEXT    NOT NULL,
    correcta    INTEGER NOT NULL DEFAULT 0,
    orden       INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (pregunta_id) REFERENCES pregunta(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS resultado_examen (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id   INTEGER NOT NULL,
    examen_id    INTEGER NOT NULL,
    nota         REAL    NOT NULL,
    aprobado     INTEGER NOT NULL DEFAULT 0,
    realizado_en TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE (usuario_id, examen_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (examen_id)  REFERENCES examen(id)  ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS certificado (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    curso_id   INTEGER NOT NULL,
    emitido_en TEXT    NOT NULL DEFAULT (datetime('now')),
    codigo     TEXT    NOT NULL UNIQUE,
    UNIQUE (usuario_id, curso_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id)   REFERENCES curso(id)   ON DELETE CASCADE
);

-- Examen de ejemplo para curso 11 (Bases de datos)
INSERT OR IGNORE INTO examen (id, curso_id, titulo, descripcion, nota_minima) VALUES
(1, 11, 'Examen Final: Bases de Datos',
 'Evalúa tus conocimientos sobre SQL, diseño relacional y consultas avanzadas. Necesitas un mínimo de 5 sobre 10 para obtener el certificado.',
 5.0);

INSERT OR IGNORE INTO pregunta (id, examen_id, enunciado, orden) VALUES
(1,  1, '¿Qué sentencia SQL se utiliza para recuperar datos de una tabla?', 1),
(2,  1, '¿Cuál de las siguientes define correctamente una clave primaria?', 2),
(3,  1, '¿Qué tipo de JOIN devuelve todas las filas de la tabla izquierda aunque no haya coincidencia?', 3),
(4,  1, '¿Qué cláusula se usa para filtrar grupos en una consulta con GROUP BY?', 4),
(5,  1, '¿Cuál es el propósito principal de un índice en una base de datos?', 5),
(6,  1, '¿Qué sentencia se usa para modificar datos existentes en una tabla?', 6),
(7,  1, '¿Qué es una subconsulta en SQL?', 7),
(8,  1, '¿Qué hace DELETE sin cláusula WHERE?', 8),
(9,  1, '¿Cuál es la función de un trigger (disparador)?', 9),
(10, 1, '¿Qué proceso elimina redundancias dividiendo una tabla en varias relacionadas?', 10);

INSERT OR IGNORE INTO opcion (id, pregunta_id, texto, correcta, orden) VALUES
(1,  1, 'INSERT', 0, 1), (2,  1, 'SELECT', 1, 2), (3,  1, 'UPDATE', 0, 3), (4,  1, 'DELETE', 0, 4),
(5,  2, 'Una columna que puede tener valores nulos', 0, 1),
(6,  2, 'Una columna que identifica de forma única cada fila', 1, 2),
(7,  2, 'Una columna que referencia otra tabla', 0, 3),
(8,  2, 'Un índice automático sobre todas las columnas', 0, 4),
(9,  3, 'INNER JOIN', 0, 1), (10, 3, 'RIGHT JOIN', 0, 2), (11, 3, 'LEFT JOIN', 1, 3), (12, 3, 'CROSS JOIN', 0, 4),
(13, 4, 'WHERE', 0, 1), (14, 4, 'HAVING', 1, 2), (15, 4, 'ORDER BY', 0, 3), (16, 4, 'LIMIT', 0, 4),
(17, 5, 'Aumentar el tamaño de almacenamiento', 0, 1),
(18, 5, 'Mejorar la velocidad de búsqueda y recuperación', 1, 2),
(19, 5, 'Eliminar datos duplicados automáticamente', 0, 3),
(20, 5, 'Crear relaciones entre tablas', 0, 4),
(21, 6, 'INSERT INTO', 0, 1), (22, 6, 'UPDATE ... SET', 1, 2), (23, 6, 'ALTER TABLE', 0, 3), (24, 6, 'CREATE', 0, 4),
(25, 7, 'Una tabla temporal creada al ejecutar una consulta', 0, 1),
(26, 7, 'Una consulta anidada dentro de otra consulta SQL', 1, 2),
(27, 7, 'Un tipo especial de índice compuesto', 0, 3),
(28, 7, 'Una vista materializada de solo lectura', 0, 4),
(29, 8, 'Elimina la estructura de la tabla completa', 0, 1),
(30, 8, 'Elimina todas las filas de la tabla', 1, 2),
(31, 8, 'No ejecuta ninguna acción sin WHERE', 0, 3),
(32, 8, 'Elimina únicamente la primera fila', 0, 4),
(33, 9, 'Se ejecuta automáticamente ante ciertos eventos de la BD', 1, 1),
(34, 9, 'Es un tipo de índice para tablas de gran tamaño', 0, 2),
(35, 9, 'Es una restricción de integridad referencial', 0, 3),
(36, 9, 'Es un sinónimo de procedimiento almacenado', 0, 4),
(37, 10, 'Denormalización', 0, 1), (38, 10, 'Normalización', 1, 2),
(39, 10, 'Indexación', 0, 3),    (40, 10, 'Particionamiento', 0, 4);

-- =============================================================================
-- Migración v2: Sidebar, Perfiles, Calendario personal, Notificaciones, CRM
-- =============================================================================

-- Preferencias de usuario: perfil de aprendizaje y estado del sidebar
CREATE TABLE IF NOT EXISTS usuario_preferencias (
    usuario_id        INTEGER PRIMARY KEY,
    perfil            TEXT    NOT NULL DEFAULT 'estudiante'
                      CHECK (perfil IN ('principiante','estudiante','trabajador')),
    sidebar_colapsado INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Eventos personales del calendario (sesiones de estudio, hitos, recordatorios)
CREATE TABLE IF NOT EXISTS evento_usuario (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id   INTEGER NOT NULL,
    titulo       TEXT    NOT NULL,
    descripcion  TEXT    DEFAULT NULL,
    fecha_inicio TEXT    NOT NULL,
    fecha_fin    TEXT    DEFAULT NULL,
    tipo         TEXT    NOT NULL DEFAULT 'sesion'
                 CHECK (tipo IN ('sesion','hito','recordatorio','bloqueo')),
    color        TEXT    DEFAULT NULL,
    todo_el_dia  INTEGER NOT NULL DEFAULT 1,
    creado_en    TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Sistema de notificaciones unificado (sistema automático + CRM)
CREATE TABLE IF NOT EXISTS notificacion (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    tipo       TEXT    NOT NULL DEFAULT 'info'
               CHECK (tipo IN ('info','tarea','mensaje','expiracion','crm')),
    titulo     TEXT    NOT NULL,
    cuerpo     TEXT    DEFAULT NULL,
    leido      INTEGER NOT NULL DEFAULT 0,
    url_accion TEXT    DEFAULT NULL,
    ref_id     INTEGER DEFAULT NULL,
    creado_en  TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

-- =============================================================================
-- Migración v3: Nivel y categoría de cursos
-- =============================================================================

UPDATE curso SET nivel = 'principiante', categoria = 'Frontend'      WHERE id = 4  AND nivel IS NULL;
UPDATE curso SET nivel = 'principiante', categoria = 'Frontend'      WHERE id = 5  AND nivel IS NULL;
UPDATE curso SET nivel = 'principiante', categoria = 'Herramientas'  WHERE id = 10 AND nivel IS NULL;
UPDATE curso SET nivel = 'principiante', categoria = 'Bases de datos' WHERE id = 11 AND nivel IS NULL;
UPDATE curso SET nivel = 'estudiante',   categoria = 'Backend'       WHERE id = 6  AND nivel IS NULL;
UPDATE curso SET nivel = 'estudiante',   categoria = 'Programación'  WHERE id = 7  AND nivel IS NULL;
UPDATE curso SET nivel = 'estudiante',   categoria = 'Frontend'      WHERE id = 8  AND nivel IS NULL;
UPDATE curso SET nivel = 'estudiante',   categoria = 'Backend'       WHERE id = 9  AND nivel IS NULL;
UPDATE curso SET nivel = 'estudiante',   categoria = 'Full Stack'    WHERE id = 1  AND nivel IS NULL;
UPDATE curso SET nivel = 'profesional',  categoria = 'Frontend'      WHERE id = 2  AND nivel IS NULL;
UPDATE curso SET nivel = 'profesional',  categoria = 'Backend'       WHERE id = 3  AND nivel IS NULL;

-- Módulo CRM: campañas automatizadas (ofertas, avisos, novedades)
CREATE TABLE IF NOT EXISTS campana_crm (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo        TEXT    NOT NULL,
    cuerpo        TEXT    NOT NULL,
    tipo          TEXT    NOT NULL DEFAULT 'oferta'
                  CHECK (tipo IN ('oferta','aviso','evento','novedad')),
    perfil_target TEXT    DEFAULT NULL,
    activa        INTEGER NOT NULL DEFAULT 1,
    fecha_inicio  TEXT    DEFAULT NULL,
    fecha_fin     TEXT    DEFAULT NULL,
    creado_en     TEXT    NOT NULL DEFAULT (datetime('now'))
);
