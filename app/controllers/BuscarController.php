<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Curso.php';

class BuscarController
{
    public function index()
    {
        $database = new Database();
        $db       = $database->connect();

        $q         = trim($_GET['q'] ?? '');
        $pagina    = max(1, (int)($_GET['p'] ?? 1));
        $porPagina = 9;

        // Filtros
        $filtroPrecio    = $_GET['precio']    ?? '';
        $filtroNivel     = $_GET['nivel']     ?? '';
        $filtroCategoria = $_GET['categoria'] ?? '';
        $ordenar         = in_array($_GET['orden'] ?? '', ['popular', 'recientes', 'precio_asc', 'precio_desc'])
                           ? $_GET['orden'] : 'popular';

        $cursoModel = new Curso($db);

        // Categorías disponibles
        $stmtCats = $db->query(
            "SELECT DISTINCT categoria FROM curso WHERE categoria IS NOT NULL AND categoria <> '' ORDER BY categoria ASC"
        );
        $categorias = $stmtCats ? $stmtCats->fetchAll(PDO::FETCH_COLUMN) : [];

        // Construir WHERE dinámico
        $where  = [];
        $params = [];

        if ($q !== '') {
            $where[]  = '(c.titulo LIKE ? OR c.descripcion LIKE ?)';
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        if ($filtroPrecio === 'gratis') {
            $where[] = '(c.precio IS NULL OR c.precio = 0)';
        } elseif ($filtroPrecio === 'pago') {
            $where[] = 'c.precio > 0';
        }
        if ($filtroNivel !== '') {
            $where[]  = 'c.nivel = ?';
            $params[] = $filtroNivel;
        }
        if ($filtroCategoria !== '') {
            $where[]  = 'c.categoria = ?';
            $params[] = $filtroCategoria;
        }

        $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $hayFiltros = ($q !== '' || $filtroPrecio !== '' || $filtroNivel !== '' || $filtroCategoria !== '' || $ordenar !== 'popular');

        // Si no hay nada activo → mostrar destacados
        if (!$hayFiltros) {
            $cursos       = $cursoModel->obtenerDestacados(9);
            $total        = count($cursos);
            $totalPaginas = 1;
            $pagina       = 1;

            $pageTitle = "Buscar cursos";
            require __DIR__ . '/../views/buscar/index.php';
            return;
        }

        // Total con filtros
        $stmtCount = $db->prepare(
            "SELECT COUNT(*) FROM curso c LEFT JOIN matricula m ON m.curso_id = c.id $whereSQL"
        );
        $stmtCount->execute($params);
        $total        = (int)$stmtCount->fetchColumn();
        $totalPaginas = $total > 0 ? (int)ceil($total / $porPagina) : 1;
        $offset       = ($pagina - 1) * $porPagina;

        // Resultados paginados — LIMIT y OFFSET con PARAM_INT para MariaDB
        $orderSQL = match($ordenar) {
            'recientes'   => 'c.id DESC',
            'precio_asc'  => 'c.precio ASC, c.id DESC',
            'precio_desc' => 'c.precio DESC, c.id DESC',
            default       => 'total_matriculas DESC, c.id DESC',
        };

        $sql = "
            SELECT c.*, COUNT(DISTINCT m.id) AS total_matriculas
            FROM curso c
            LEFT JOIN matricula m ON m.curso_id = c.id
            $whereSQL
            GROUP BY c.id
            ORDER BY $orderSQL
            LIMIT ? OFFSET ?
        ";
        $stmtCursos = $db->prepare($sql);

        // Bindear params dinámicos
        $bindPos = 1;
        foreach ($params as $val) {
            $stmtCursos->bindValue($bindPos++, $val);
        }
        // Bindear LIMIT y OFFSET explícitamente como enteros
        $stmtCursos->bindValue($bindPos++, $porPagina, PDO::PARAM_INT);
        $stmtCursos->bindValue($bindPos,   $offset,    PDO::PARAM_INT);
        $stmtCursos->execute();
        $cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Buscar cursos";
        require __DIR__ . '/../views/buscar/index.php';
    }
}
