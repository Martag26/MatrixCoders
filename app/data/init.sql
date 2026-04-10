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
-- carpeta
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
-- curso — esquema completo con columnas de Marta y las originales
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS curso (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo         TEXT    NOT NULL,
    descripcion    TEXT    DEFAULT NULL,
    info_extra     TEXT    DEFAULT NULL,
    que_aprenderas TEXT    DEFAULT NULL,
    imagen         TEXT    DEFAULT NULL,
    destacado      INTEGER NOT NULL DEFAULT 0,
    precio         REAL    NOT NULL DEFAULT 0.00,
    nivel          TEXT    DEFAULT NULL,
    categoria      TEXT    DEFAULT NULL,
    duracion_min   INTEGER DEFAULT NULL,
    estudiantes    INTEGER DEFAULT NULL
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
-- leccion_vista
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leccion_vista (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    leccion_id INTEGER NOT NULL,
    visto_at   TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE (usuario_id, leccion_id)
);

-- -----------------------------------------------------------------------------
-- nota
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS nota (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    leccion_id INTEGER NOT NULL,
    contenido  TEXT    NOT NULL,
    updated_at TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE (usuario_id, leccion_id)
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
    FOREIGN KEY (tarea_id)     REFERENCES tarea(id)     ON DELETE CASCADE  ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id)   REFERENCES usuario(id)   ON DELETE CASCADE  ON UPDATE CASCADE,
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
(16, 'marta_admin',    'marta_admin@g.educaand.es',    '$2y$10$dJWtStpQeNL03tkMv5vFyuWlfLhdW2XBcqdPHkkaCIxQBUdVEaa16', '2026-02-24 08:00:50', 'ADMINISTRADOR'),
(17, 'isidoro_editor', 'isidoro_editor@g.educaand.es', '$2y$10$dJWtStpQeNL03tkMv5vFyuWlfLhdW2XBcqdPHkkaCIxQBUdVEaa16', '2026-02-24 08:00:50', 'EDITOR'),
(18, 'usuario',        'usuario@usuario.es',            '$2y$10$6kT86LNkfuL/QmCAUtoh5.nKYdQU9XgjtHR24OAZfD9WhEeBuacIa', '2026-02-24 08:00:50', 'USUARIO'),
(19, 'usuario2',       'usuario2@usuario2.es',          '$2y$10$LgSFVonOoulaVNRKHR.37OlYyeyEzdrkl2BIjqi05JlkBYak4YqKu', '2026-02-24 08:11:30', 'USUARIO'),
(20, 'usuario3',       'usuario3@usuario3.es',          '$2y$10$Qc6p1h1Dz2vo4aHURADw2uKGnDR1TP8PfHSqix22UcwAtSNAzeB1.', '2026-02-24 08:11:30', 'USUARIO'),
(21, 'Pablo',          'Pablo@pablo.pablo',             '$2y$10$Prxu9Lw8qv2Ro07zI7p7Leo28/pIK0yYf4o6QvGKpJKH/c6TA0tYS', '2026-03-13 12:03:47', 'USUARIO');

INSERT INTO curso (id, titulo, descripcion, info_extra, que_aprenderas, imagen, destacado, precio, nivel, categoria, duracion_min, estudiantes) VALUES
(1,  'Desarrollo Web Completo',
     'Aprende HTML, CSS, JavaScript, PHP y MySQL desde cero hasta nivel profesional.',
     NULL, NULL, NULL, 0, 199.99, NULL, NULL, 109, 243),
(2,  'Frontend Profesional con React',
     'Domina React, hooks, rutas y consumo de APIs para crear aplicaciones modernas.',
     NULL, NULL, NULL, 0, 149.99, NULL, NULL, 95, 157),
(3,  'Backend con Node.js y Bases de Datos',
     'Construye APIs REST con Node.js, Express y conexión a bases de datos SQL.',
     NULL, NULL, NULL, 0, 179.99, NULL, NULL, 130, 198),
(4,  'HTML y CSS',
     'Fundamentos de maquetación web. Etiquetas, selectores, Flexbox y Grid.',
     NULL, NULL, 'cursos/html.jpg', 0, 0.00, NULL, NULL, NULL, NULL),
(5,  'JavaScript',
     'Programación en el navegador. DOM, eventos, fetch y ES6+.',
     NULL, NULL, 'cursos/javascript.jpg', 0, 49.99, NULL, NULL, NULL, NULL),
(6,  'PHP y MySQL',
     'Desarrollo backend con PHP. Conexión a bases de datos y CRUD completo.',
     NULL, NULL, 'cursos/php.jpg', 0, 59.99, NULL, NULL, NULL, NULL),
(7,  'Java',
     'Programación orientada a objetos con Java. Clases, herencia e interfaces.',
     NULL, NULL, 'cursos/java.jpg', 0, 59.99, NULL, NULL, NULL, NULL),
(8,  'React',
     'Desarrollo frontend moderno con React, hooks y consumo de APIs.',
     NULL, NULL, 'cursos/react.jpg', 0, 69.99, NULL, NULL, NULL, NULL),
(9,  'Node.js',
     'Backend con JavaScript. Express, APIs REST y conexión a bases de datos.',
     NULL, NULL, 'cursos/nodejs.jpg', 0, 69.99, NULL, NULL, NULL, NULL),
(10, 'Git y GitHub',
     'Control de versiones. Ramas, merges, pull requests y flujo de trabajo en equipo.',
     NULL, NULL, 'cursos/git.jpg', 0, 0.00, NULL, NULL, NULL, NULL),
(11, 'Bases de datos',
     'Diseño relacional, SQL avanzado, índices y optimización de consultas.',
     'Este curso está diseñado para llevarte desde cero hasta un nivel sólido y práctico en bases de datos relacionales. Cada lección combina teoría clara con ejemplos reales para que puedas aplicar lo aprendido desde el primer día. Aprenderás a tu ritmo, con acceso ilimitado a todos los materiales en cualquier dispositivo.

Al finalizar habrás trabajado con consultas reales, diseñado esquemas de bases de datos y tendrás una base sólida para seguir avanzando en tu carrera tecnológica.',
     'Qué es una base de datos relacional y cómo funciona
Instalar y configurar MySQL y phpMyAdmin
Diseñar esquemas con el modelo Entidad-Relación
Escribir consultas SELECT, INSERT, UPDATE y DELETE
Usar JOIN para combinar tablas
Aplicar GROUP BY y funciones de agregado
Crear subconsultas y consultas avanzadas
Buenas prácticas en diseño de bases de datos',
     'cursos/bbdd.jpg', 0, 49.99, 'principiante', NULL, NULL, NULL);

INSERT INTO unidad (id, curso_id, titulo, orden) VALUES
(1,  1,  'Introducción al Curso',              1),
(2,  1,  'HTML y Estructura Web',              2),
(3,  1,  'CSS y Estilos',                      3),
(12, 11, 'Introducción y consultas básicas',   1),
(13, 11, 'Consultas multitabla',               2),
(14, 11, 'Consultas de acción y DDL',          3),
(15, 11, 'Índices, triggers y procedimientos', 4);

INSERT INTO leccion (id, unidad_id, titulo, orden, video_url) VALUES
(1,  1,  'Bienvenida',                          1, 'https://video-demo.com/bienvenida'),
(2,  1,  'Cómo funciona el curso',              2, 'https://video-demo.com/funcionamiento'),
(3,  2,  'Etiquetas básicas HTML',              1, 'https://video-demo.com/html-basico'),
(4,  2,  'Formularios en HTML',                 2, 'https://video-demo.com/html-formularios'),
(5,  3,  'Selectores CSS',                      1, 'https://video-demo.com/css-selectores'),
(6,  3,  'Flexbox y Layout',                    2, 'https://video-demo.com/css-flexbox'),
(19, 12, 'Presentación del curso',              1, 'https://www.youtube.com/watch?v=iOiyJgnN71c&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=1'),
(20, 12, 'Introducción a SQL',                  2, 'https://www.youtube.com/watch?v=Bk3rY_ICgPo&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=2'),
(21, 12, 'Consultas SELECT básicas',            3, 'https://www.youtube.com/watch?v=np6PH_vs-GI&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=3'),
(22, 12, 'Consultas con criterios',             4, 'https://www.youtube.com/watch?v=yZk9NdxFUrk&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=4'),
(23, 12, 'Consultas de resumen',                5, 'https://www.youtube.com/watch?v=TPn1200-fbc&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=5'),
(24, 12, 'Consultas de cálculo',                6, 'https://www.youtube.com/watch?v=qwfzpXI_Qyw&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=6'),
(25, 13, 'Consultas multitabla I - UNION',      1, 'https://www.youtube.com/watch?v=M2Ee0HnSPOU&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=7'),
(26, 13, 'Consultas multitabla II - INNER JOIN',2, 'https://www.youtube.com/watch?v=2LtcWYdVx_I&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=8'),
(27, 13, 'Consultas multitabla III - LEFT JOIN', 3, 'https://www.youtube.com/watch?v=N99-7gvjy6o&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=9'),
(28, 13, 'Subconsultas I',                      4, 'https://www.youtube.com/watch?v=rGPb5E1UAJA&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=10'),
(29, 13, 'Subconsultas II',                     5, 'https://www.youtube.com/watch?v=lCpMJ2LFdLg&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=11'),
(30, 14, 'Consultas de acción I - UPDATE',      1, 'https://www.youtube.com/watch?v=_XvIenBYJc8&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=12'),
(31, 14, 'Consultas de acción II - DELETE',     2, 'https://www.youtube.com/watch?v=TTlBR2jLmn0&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=13'),
(32, 14, 'Consultas de acción III - INSERT',    3, 'https://www.youtube.com/watch?v=3iLHq04FuQs&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=14'),
(33, 14, 'DDL - Creación de bases de datos',    4, 'https://www.youtube.com/watch?v=_kIWDzZUdA8&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=15'),
(34, 14, 'DDL - Creación de tablas',            5, 'https://www.youtube.com/watch?v=XJb6qflbsx4&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=16'),
(35, 14, 'DDL - Modificación de tablas',        6, 'https://www.youtube.com/watch?v=mLosHjJdc54&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=17'),
(36, 15, 'Índices',                             1, 'https://www.youtube.com/watch?v=k__6BKdCaQ8&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=18'),
(37, 15, 'Funciones',                           2, 'https://www.youtube.com/watch?v=tzYpnIu8sSE&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=19'),
(38, 15, 'Triggers I',                          3, 'https://www.youtube.com/watch?v=kDu_5F159QA&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=20'),
(39, 15, 'Triggers II',                         4, 'https://www.youtube.com/watch?v=bGQbgejFyBo&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=21'),
(40, 15, 'Procedimientos almacenados I',        5, 'https://www.youtube.com/watch?v=_Gy8-hCA8a0&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=23'),
(41, 15, 'Procedimientos almacenados II',       6, 'https://www.youtube.com/watch?v=sNHZhXeVA4c&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=24'),
(42, 15, 'Vistas - Final del curso',            7, 'https://www.youtube.com/watch?v=dfF4WAaUX5c&list=PLU8oAlHdN5Bmx-LChV4K3MbHrpZKefNwn&index=25');

INSERT INTO tarea (id, curso_id, leccion_id, titulo, fecha_limite) VALUES
(1, 1, 3, 'Crear tu primera página HTML',    '2026-03-10 00:00:00'),
(2, 1, 6, 'Maquetar una página con Flexbox', '2026-03-15 00:00:00');

INSERT INTO matricula (id, usuario_id, curso_id, fecha, estado) VALUES
(1, 18, 11, '2026-03-31 22:24:39', 'activa'),
(4, 18, 10, '2026-04-06 23:32:07', 'activa'),
(5, 18,  9, '2026-04-09 01:55:30', 'activa');

INSERT INTO leccion_vista (id, usuario_id, leccion_id, visto_at) VALUES
(1,  18, 19, '2026-04-02 01:09:15'),
(4,  18, 20, '2026-04-02 01:09:22'),
(5,  18, 21, '2026-04-02 01:09:23'),
(10, 18, 22, '2026-04-09 01:34:32'),
(11, 18, 23, '2026-04-09 01:34:34'),
(18, 18, 24, '2026-04-09 19:59:56');

INSERT INTO carpeta (id, usuario_id, padre_id, nombre) VALUES
(4, 19, NULL, 'Mis Cursos'),
(5, 19, NULL, 'Entregas'),
(6, 19, NULL, 'Apuntes');

INSERT INTO documento (id, usuario_id, carpeta_id, plantilla_id, titulo, contenido, estado, destacado) VALUES
(1, 19, 6, NULL, 'Resumen HTML',       'Apuntes sobre etiquetas HTML...',  'borrador',  0),
(2, 19, 5, NULL, 'Entrega Página HTML', 'Aquí va mi ejercicio práctico...', 'publicado', 1),
(3, 19, 6, NULL, 'Notas CSS',          'Propiedades importantes de CSS...','borrador',  0),
(4, 18, NULL, NULL, 'DWES - UT.5 - CASOS DE LÓGICA DE NEGOCIOS',
   'Archivo original: DWES - UT.5 - CASOS DE LÓGICA DE NEGOCIOS.pdf
Ruta del archivo: /matrixcoders/public/uploads/documentos/DWES---UT.5---CASOS-DE-L--GICA-DE-NEGOCIOS-69d6b602cd0686.39432515.pdf
Tipo de archivo: PDF

Este archivo se subió desde el dashboard. Si necesitas su contenido completo, abre el archivo original.',
   'borrador', 0),
(5, 18, NULL, NULL, 'Código Java básico',
   'public class Main {
    public static void main(String[] args) {
        System.out.println("Hola, MatrixCoders");
    }
}',
   'borrador', 0);

INSERT INTO entrega (id, tarea_id, usuario_id, documento_id, nota, entregado_en) VALUES
(3, 1, 19, NULL, 8.50, '2026-02-24 08:11:38'),
(4, 2, 19, NULL, NULL, '2026-02-24 08:11:38');
