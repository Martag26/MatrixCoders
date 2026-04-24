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

        $q      = trim($_GET['q'] ?? '');
        $pagina = max(1, (int)($_GET['p'] ?? 1));
        $porPagina = 9;

        // Precio (single)
        $filtroPrecio = in_array($_GET['precio'] ?? '', ['gratis', 'pago'])
            ? $_GET['precio'] : '';

        // Nivel (multi)
        $filtroNiveles = array_values(array_filter(
            (array)($_GET['nivel'] ?? []),
            fn($n) => in_array($n, ['principiante', 'estudiante', 'profesional'], true)
        ));

        // Categoría (multi)
        $filtroCategorias = array_values(array_filter(
            (array)($_GET['categoria'] ?? []),
            fn($c) => $c !== ''
        ));

        $ordenar = in_array($_GET['orden'] ?? '', ['popular', 'recientes', 'precio_asc', 'precio_desc'])
            ? $_GET['orden'] : 'popular';

        $cursoModel = new Curso($db);

        // Categorías disponibles
        $stmtCats = $db->query(
            "SELECT DISTINCT categoria FROM curso WHERE categoria IS NOT NULL AND categoria <> '' ORDER BY categoria ASC"
        );
        $categorias = $stmtCats ? $stmtCats->fetchAll(PDO::FETCH_COLUMN) : [];

        // WHERE dinámico
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
        if (!empty($filtroNiveles)) {
            $ph      = implode(',', array_fill(0, count($filtroNiveles), '?'));
            $where[] = "c.nivel IN ($ph)";
            $params  = array_merge($params, $filtroNiveles);
        }
        if (!empty($filtroCategorias)) {
            $ph      = implode(',', array_fill(0, count($filtroCategorias), '?'));
            $where[] = "c.categoria IN ($ph)";
            $params  = array_merge($params, $filtroCategorias);
        }

        $whereSQL   = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $hayFiltros = ($q !== '' || $filtroPrecio !== '' || !empty($filtroNiveles) || !empty($filtroCategorias) || $ordenar !== 'popular');

        // Sin filtros → destacados
        if (!$hayFiltros) {
            $cursos       = $cursoModel->obtenerDestacados(9);
            $total        = count($cursos);
            $totalPaginas = 1;
            $pagina       = 1;
            $pageTitle    = 'Buscar cursos';
            require __DIR__ . '/../views/buscar/index.php';
            return;
        }

        // Total
        $stmtCount = $db->prepare(
            "SELECT COUNT(*) FROM curso c LEFT JOIN matricula m ON m.curso_id = c.id $whereSQL"
        );
        $stmtCount->execute($params);
        $total        = (int)$stmtCount->fetchColumn();
        $totalPaginas = $total > 0 ? (int)ceil($total / $porPagina) : 1;
        $offset       = ($pagina - 1) * $porPagina;

        $orderSQL = match($ordenar) {
            'recientes'   => 'c.id DESC',
            'precio_asc'  => 'c.precio ASC, c.id DESC',
            'precio_desc' => 'c.precio DESC, c.id DESC',
            default       => 'total_matriculas DESC, c.id DESC',
        };

        $sql = "
            SELECT c.*, COUNT(DISTINCT m.id) AS total_matriculas,
                   (SELECT cc.descuento FROM campana_curso cc
                    JOIN campana_crm cm ON cm.id=cc.campana_id
                    WHERE cc.curso_id=c.id AND cm.activa=1
                      AND (cm.fecha_fin IS NULL OR cm.fecha_fin >= date('now'))
                    LIMIT 1) AS descuento_activo
            FROM curso c
            LEFT JOIN matricula m ON m.curso_id = c.id
            $whereSQL
            GROUP BY c.id
            ORDER BY $orderSQL
            LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        $pos  = 1;
        foreach ($params as $val) { $stmt->bindValue($pos++, $val); }
        $stmt->bindValue($pos++, $porPagina, PDO::PARAM_INT);
        $stmt->bindValue($pos,   $offset,    PDO::PARAM_INT);
        $stmt->execute();
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Buscar cursos';
        require __DIR__ . '/../views/buscar/index.php';
    }
}
