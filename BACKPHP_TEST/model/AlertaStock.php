<?php
// model/AlertaStock.php — Campana de stock bajo (RF 2.3).
// Lee alertas_sistema + producto. Funciona ANTES y DESPUÉS de
// migrate_stock_minimo.sql (fallback al umbral 10 si aún no existe
// Stock_Minimo / alertas_sistema.ID_Producto).
require_once __DIR__ . "/../config/conexion.php";

class AlertaStock
{
    private $db;
    private $hasStockMinimo = null;
    private $hasAlertaProducto = null;

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
    }

    private function colExiste($tabla, $columna)
    {
        try {
            $st = $this->db->prepare(
                "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS " .
                "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c"
            );
            $st->execute([':t' => $tabla, ':c' => $columna]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            return ((int)($r['c'] ?? 0)) > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function tieneStockMinimo()
    {
        if ($this->hasStockMinimo === null) {
            $this->hasStockMinimo = $this->colExiste('producto', 'Stock_Minimo');
        }
        return $this->hasStockMinimo;
    }

    private function alertaTieneProducto()
    {
        if ($this->hasAlertaProducto === null) {
            $this->hasAlertaProducto = $this->colExiste('alertas_sistema', 'ID_Producto');
        }
        return $this->hasAlertaProducto;
    }

    /** N° de alertas STOCK_BAJO sin leer (badge de la campana). */
    public function contarNoLeidas()
    {        try {
            $r = $this->db->query(
                "SELECT COUNT(*) AS c FROM alertas_sistema WHERE Tipo = 'STOCK_BAJO' AND Leido = 0"
            )->fetch(PDO::FETCH_ASSOC);
            return (int)($r['c'] ?? 0);
        } catch (PDOException $e) {
            error_log("AlertaStock::contar: " . $e->getMessage());
            return 0;
        }
    }

    /** Últimas N alertas sin leer, con datos del producto si hay FK. */
    public function listarNoLeidas($limit = 10)
    {
        $limit = max(1, min(50, (int)$limit));
        try {
            if ($this->alertaTieneProducto()) {
                $sql = "SELECT a.ID_Alerta, a.Tipo, a.Mensaje, a.Fecha_Creacion, a.ID_Producto,
                               p.Nombre_Producto, p.Stock_Actual" .
                    ($this->tieneStockMinimo() ? ", p.Stock_Minimo" : "") .
                    " FROM alertas_sistema a LEFT JOIN producto p ON p.ID_Producto = a.ID_Producto" .
                    " WHERE a.Tipo = 'STOCK_BAJO' AND a.Leido = 0" .
                    " ORDER BY a.ID_Alerta DESC LIMIT $limit";
            } else {
                $sql = "SELECT ID_Alerta, Tipo, Mensaje, Fecha_Creacion FROM alertas_sistema" .
                    " WHERE Tipo = 'STOCK_BAJO' AND Leido = 0" .
                    " ORDER BY ID_Alerta DESC LIMIT $limit";
            }
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AlertaStock::listar: " . $e->getMessage());
            return [];
        }
    }

    /** Productos bajo mínimo directo de producto (no depende del trigger). */
    public function listarStockBajo($limit = 50)
    {
        $limit = max(1, min(200, (int)$limit));
        try {
            if ($this->tieneStockMinimo()) {
                $sql = "SELECT p.ID_Producto, p.Nombre_Producto, p.Stock_Actual, p.Stock_Minimo,
                               p.Precio_Actual, p.Imagen_URL, c.Nombre_Categoria
                        FROM producto p LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                        WHERE p.deleted_at IS NULL AND p.Stock_Actual <= p.Stock_Minimo
                        ORDER BY p.Stock_Actual ASC LIMIT $limit";
            } else {
                $sql = "SELECT p.ID_Producto, p.Nombre_Producto, p.Stock_Actual, 10 AS Stock_Minimo,
                               p.Precio_Actual, p.Imagen_URL, c.Nombre_Categoria
                        FROM producto p LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                        WHERE p.deleted_at IS NULL AND p.Stock_Actual < 10
                        ORDER BY p.Stock_Actual ASC LIMIT $limit";
            }
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AlertaStock::bajo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sincroniza la campana con la realidad (RF 2.3): crea STOCK_BAJO para
     * todo producto con Stock <= Mínimo que no tenga alerta sin leer en 24h.
     * Cubre productos que ya estaban bajo mínimo antes del trigger o que
     * se editaron sin "cruzar" el umbral.
     * Retorna los items CREADOS (para el email al admin): cada uno con
     * ID_Producto, Nombre_Producto, Stock_Actual, Stock_Minimo y Mensaje.
     */
    public function sincronizarBajoMinimo()
    {
        if (!$this->tieneStockMinimo() || !$this->alertaTieneProducto()) return [];
        try {
            $cond = "p.deleted_at IS NULL AND p.Stock_Actual <= p.Stock_Minimo " .
                "AND NOT EXISTS (SELECT 1 FROM alertas_sistema a WHERE a.ID_Producto = p.ID_Producto " .
                "AND a.Tipo = 'STOCK_BAJO' AND a.Leido = 0 " .
                "AND a.Fecha_Creacion > NOW() - INTERVAL 24 HOUR)";
            $candidatos = $this->db->query(
                "SELECT p.ID_Producto, p.Nombre_Producto, p.Stock_Actual, p.Stock_Minimo " .
                "FROM producto p WHERE $cond"
            )->fetchAll(PDO::FETCH_ASSOC);
            if (empty($candidatos)) return [];
            $this->db->exec(
                "INSERT INTO alertas_sistema (Tipo, Mensaje, ID_Producto) " .
                "SELECT 'STOCK_BAJO', CONCAT('Stock bajo: ', p.Nombre_Producto, " .
                "' (', p.Stock_Actual, '/', p.Stock_Minimo, ')'), p.ID_Producto " .
                "FROM producto p WHERE $cond"
            );
            foreach ($candidatos as &$c) {
                $c['Mensaje'] = 'Stock bajo: ' . $c['Nombre_Producto'] .
                    ' (' . $c['Stock_Actual'] . '/' . $c['Stock_Minimo'] . ')';
            }
            return $candidatos;
        } catch (PDOException $e) {
            error_log("AlertaStock::sincronizar: " . $e->getMessage());
            return [];
        }
    }

    public function marcarLeida($id)
    {
        if (!ctype_digit((string)$id)) return false;
        try {
            $st = $this->db->prepare("UPDATE alertas_sistema SET Leido = 1 WHERE ID_Alerta = :id");
            $st->bindParam(":id", $id, PDO::PARAM_INT);
            return $st->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function marcarTodas()
    {
        try {
            return $this->db->exec("UPDATE alertas_sistema SET Leido = 1 WHERE Tipo = 'STOCK_BAJO' AND Leido = 0") !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /** Emails de administradores para el aviso por correo (RF 2.3 + Mailer). */
    public function emailsAdmins()
    {
        try {
            return $this->db->query(
                "SELECT Email FROM usuario WHERE Rol = 'Administrador' AND deleted_at IS NULL"
            )->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
}
