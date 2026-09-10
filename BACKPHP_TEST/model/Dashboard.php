<?php
require_once __DIR__ . "/../config/conexion.php";

class Dashboard
{
    private $db;

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
    }

    private function sumaPeriodo($where, $params = [])
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(dv.Cantidad * dv.Precio_Venta_Historico),0) AS total,
                                               COUNT(DISTINCT v.ID_Venta) AS ventas
                                        FROM venta v JOIN detalle_venta dv ON dv.ID_Venta = v.ID_Venta
                                        $where");
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Dashboard::suma: " . $e->getMessage());
            return ['total' => 0, 'ventas' => 0];
        }
    }

    public function resumen()
    {
        $mes = $this->sumaPeriodo("WHERE YEAR(v.Fecha_Venta) = YEAR(CURDATE()) AND MONTH(v.Fecha_Venta) = MONTH(CURDATE())");
        $anio = $this->sumaPeriodo("WHERE YEAR(v.Fecha_Venta) = YEAR(CURDATE())");
        $hoy = $this->sumaPeriodo("WHERE DATE(v.Fecha_Venta) = CURDATE()");
        try {
            $pend = $this->db->query("SELECT COUNT(*) AS c FROM pedido WHERE Estado_Pedido IN ('Preparando','En camino')")->fetch(PDO::FETCH_ASSOC);
            $stockBajo = $this->db->query("SELECT COUNT(*) AS c FROM producto WHERE deleted_at IS NULL AND Stock_Actual < 10")->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $pend = ['c' => 0]; $stockBajo = ['c' => 0];
        }
        return [
            'ganancias_mes' => (float)($mes['total'] ?? 0),
            'ventas_mes' => (int)($mes['ventas'] ?? 0),
            'ganancias_anio' => (float)($anio['total'] ?? 0),
            'ganancias_hoy' => (float)($hoy['total'] ?? 0),
            'ventas_hoy' => (int)($hoy['ventas'] ?? 0),
            'pedidos_pendientes' => (int)($pend['c'] ?? 0),
            'stock_bajo' => (int)($stockBajo['c'] ?? 0),
        ];
    }

    /** Últimos N meses con etiqueta corta en español + total. */
    public function ventasPorMes($n = 6)
    {
        try {
            $n = max(1, min(12, (int)$n));
            $stmt = $this->db->prepare("SELECT DATE_FORMAT(v.Fecha_Venta, '%Y-%m') AS ym,
                                               COALESCE(SUM(dv.Cantidad * dv.Precio_Venta_Historico),0) AS total
                                        FROM venta v JOIN detalle_venta dv ON dv.ID_Venta = v.ID_Venta
                                        WHERE v.Fecha_Venta >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL $n MONTH), '%Y-%m-01')
                                        GROUP BY ym ORDER BY ym");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map = [];
            foreach ($rows as $r) $map[$r['ym']] = (float)$r['total'];
            $mesesEs = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
            $labels = []; $data = [];
            for ($i = $n - 1; $i >= 0; $i--) {
                $ts = strtotime("first day of -$i months");
                $ym = date('Y-m', $ts);
                $labels[] = $mesesEs[(int)date('n', $ts)];
                $data[] = $map[$ym] ?? 0;
            }
            return ['labels' => $labels, 'data' => $data];
        } catch (PDOException $e) {
            error_log("Dashboard::porMes: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    /** Top categorías por ingreso para la dona. */
    public function ingresosPorCategoria($limit = 5)
    {
        try {
            $limit = (int)$limit;
            $rows = $this->db->query("SELECT c.Nombre_Categoria, SUM(dv.Cantidad * dv.Precio_Venta_Historico) AS total
                                        FROM detalle_venta dv
                                        JOIN producto p ON dv.ID_Producto = p.ID_Producto
                                        JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                                        GROUP BY c.ID_Categoria, c.Nombre_Categoria ORDER BY total DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
            $labels = []; $data = [];
            foreach ($rows as $r) { $labels[] = $r['Nombre_Categoria']; $data[] = (float)$r['total']; }
            return ['labels' => $labels, 'data' => $data];
        } catch (PDOException $e) {
            error_log("Dashboard::porCat: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    /** Top productos por unidades vendidas. Usa v_top_productos con fallback directo. */
    public function topProductos($limit = 5)
    {
        $limit = max(1, min(20, (int)$limit));
        try {
            $rows = $this->db->query("SELECT Nombre_Producto, Vendidos FROM v_top_productos ORDER BY Vendidos DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) return $rows;
        } catch (PDOException $e) {
            error_log("Dashboard::topProductos(vista): " . $e->getMessage());
        }
        try {
            return $this->db->query("SELECT p.Nombre_Producto, SUM(dv.Cantidad) AS Vendidos
                                        FROM detalle_venta dv
                                        JOIN producto p ON dv.ID_Producto = p.ID_Producto
                                        GROUP BY p.ID_Producto, p.Nombre_Producto
                                        ORDER BY Vendidos DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Dashboard::topProductos(fallback): " . $e->getMessage());
            return [];
        }
    }

    /** Ventas diarias últimos N días. Usa v_ventas_diarias con relleno de ceros. */
    public function ventasDiarias($n = 7)
    {
        $n = max(1, min(30, (int)$n));
        try {
            $rows = [];
            try {
                $rows = $this->db->query("SELECT Fecha, Total_Ventas FROM v_ventas_diarias WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL $n DAY) ORDER BY Fecha")->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Dashboard::ventasDiarias(vista): " . $e->getMessage());
            }
            $map = [];
            foreach ($rows as $r) $map[substr((string)$r['Fecha'], 0, 10)] = (int)$r['Total_Ventas'];
            $labels = []; $data = [];
            for ($i = $n - 1; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('d/m', strtotime($d));
                $data[] = $map[$d] ?? 0;
            }
            return ['labels' => $labels, 'data' => $data];
        } catch (PDOException $e) {
            error_log("Dashboard::ventasDiarias: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }
}
