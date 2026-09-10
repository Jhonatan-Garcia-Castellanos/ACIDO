<?php
require_once __DIR__ . "/../config/conexion.php";

class Producto
{
    private $db;

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
    }

    public function obtenerTodos()
    {
        try {
            $sql = "SELECT p.ID_Producto, p.Nombre_Producto, p.Precio_Actual, p.Stock_Actual,
                           p.ID_Categoria, p.ID_Proveedor, p.Imagen_URL, p.deleted_at,
                           (p.deleted_at IS NULL) AS Activo,
                           c.Nombre_Categoria, pr.Nombre_Empresa AS Proveedor
                    FROM producto p
                    LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                    LEFT JOIN proveedor pr ON p.ID_Proveedor = pr.ID_Proveedor
                    ORDER BY p.ID_Producto DESC LIMIT 200";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Producto::obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId($id)
    {
        if (!ctype_digit((string)$id)) return false;
        try {
            $stmt = $this->db->prepare("SELECT *, (deleted_at IS NULL) AS Activo FROM producto WHERE ID_Producto = :id LIMIT 1");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Producto::obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    public function listarCategorias()
    {
        try {
            return $this->db->query("SELECT ID_Categoria, Nombre_Categoria FROM categoria ORDER BY Nombre_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function listarProveedores()
    {
        try {
            return $this->db->query("SELECT ID_Proveedor, Nombre_Empresa FROM proveedor ORDER BY Nombre_Empresa")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function resumen()
    {
        try {
            $val = $this->db->query("SELECT SUM(Stock_Actual * Precio_Actual) AS total FROM producto WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
            $cero = $this->db->query("SELECT COUNT(*) AS c FROM producto WHERE Stock_Actual = 0 AND deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
            $bajo = $this->db->query("SELECT COUNT(*) AS c FROM producto WHERE Stock_Actual > 0 AND Stock_Actual < 10 AND deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
            return [
                'valorizacion' => $val['total'] ?? 0,
                'agotados' => $cero['c'] ?? 0,
                'stock_bajo' => $bajo['c'] ?? 0,
            ];
        } catch (PDOException $e) { return ['valorizacion'=>0,'agotados'=>0,'stock_bajo'=>0]; }
    }

    public function eliminar($id)
    {
        if (!ctype_digit((string)$id)) return false;
        try {
            $stmt = $this->db->prepare("UPDATE producto SET deleted_at = NOW() WHERE ID_Producto = :id AND deleted_at IS NULL");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Producto::eliminar: " . $e->getMessage());
            return false;
        }
    }

    public function guardar($datos)
    {
        $id = trim((string)($datos['ID_Producto'] ?? $datos['id'] ?? ''));
        $nombre = trim($datos['Nombre_Producto'] ?? $datos['nombre'] ?? '');
        $precio = $datos['Precio_Actual'] ?? $datos['precio'] ?? null;
        $stock = $datos['Stock_Actual'] ?? $datos['stock'] ?? 0;
        $cat = $datos['ID_Categoria'] ?? $datos['categoria'] ?? null;
        $prov = $datos['ID_Proveedor'] ?? $datos['proveedor'] ?? null;

        if ($nombre === '' || !is_numeric($precio) || $precio <= 0) return false;
        if (!is_numeric($stock) || $stock < 0) return false;
        if (!ctype_digit((string)$cat) || !ctype_digit((string)$prov)) return false;

        try {
            if (!empty($id)) {
                if (!ctype_digit($id)) return false;
                $stmt = $this->db->prepare("UPDATE producto SET Nombre_Producto=:n, Precio_Actual=:p, Stock_Actual=:s, ID_Categoria=:c, ID_Proveedor=:pr WHERE ID_Producto=:id AND deleted_at IS NULL");
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare("INSERT INTO producto (Nombre_Producto, Precio_Actual, Stock_Actual, ID_Categoria, ID_Proveedor) VALUES (:n,:p,:s,:c,:pr)");
            }
            $stmt->bindParam(":n", $nombre);
            $stmt->bindParam(":p", $precio);
            $stmt->bindParam(":s", $stock, PDO::PARAM_INT);
            $stmt->bindParam(":c", $cat, PDO::PARAM_INT);
            $stmt->bindParam(":pr", $prov, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Triggers: trg_validar_precio_positivo, trg_impedir_stock_negativo, FK inexistente
            error_log("Producto::guardar: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerCatalogo()
    {
        try {
            $sql = "SELECT p.ID_Producto, p.Nombre_Producto, p.Precio_Actual, p.Stock_Actual,
                           p.Imagen_URL, c.Nombre_Categoria
                    FROM producto p
                    LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                    WHERE p.deleted_at IS NULL AND p.Stock_Actual > 0
                    ORDER BY p.ID_Producto DESC LIMIT 100";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Producto::obtenerCatalogo: " . $e->getMessage());
            return [];
        }
    }

    public function cambiarEstado($id, $activo)
    {
        if (!ctype_digit((string)$id)) return false;
        try {
            if ((int)$activo === 1) {
                $stmt = $this->db->prepare("UPDATE producto SET deleted_at = NULL WHERE ID_Producto = :id");
            } else {
                $stmt = $this->db->prepare("UPDATE producto SET deleted_at = NOW() WHERE ID_Producto = :id AND deleted_at IS NULL");
            }
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Producto::cambiarEstado: " . $e->getMessage());
            return false;
        }
    }
}
