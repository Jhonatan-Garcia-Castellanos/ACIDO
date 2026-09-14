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

    public function contarProductos($q = '')
    {
        try {
            $q = trim((string)$q);
            $where = '';
            $params = [];
            if ($q !== '') {
                $where = "WHERE (p.Nombre_Producto LIKE :q1 OR c.Nombre_Categoria LIKE :q2 OR pr.Nombre_Empresa LIKE :q3)";
                $params[':q1'] = '%' . $q . '%'; $params[':q2'] = '%' . $q . '%'; $params[':q3'] = '%' . $q . '%';
            }
            $stmt = $this->db->prepare("SELECT COUNT(*) AS c FROM producto p LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria LEFT JOIN proveedor pr ON p.ID_Proveedor = pr.ID_Proveedor $where");
            $stmt->execute($params);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($r['c'] ?? 0);
        } catch (PDOException $e) { return 0; }
    }

    public function obtenerTodos($page = 1, $per = 10, $q = '', $order = 'ID_Producto', $dir = 'DESC')
    {
        try {
            $page = max(1, (int)$page);
            $per = (int)$per;
            if (!in_array($per, [5, 10, 20, 50], true)) $per = 10;
            $off = ($page - 1) * $per;
            $map = ['ID_Producto' => 'p.ID_Producto', 'Nombre_Producto' => 'p.Nombre_Producto', 'Precio_Actual' => 'p.Precio_Actual', 'Stock_Actual' => 'p.Stock_Actual'];
            $orderSql = $map[$order] ?? 'p.ID_Producto';
            $dir = (strtoupper($dir) === 'ASC') ? 'ASC' : 'DESC';
            $q = trim((string)$q);
            $where = '';
            $params = [];
            if ($q !== '') {
                $where = "WHERE (p.Nombre_Producto LIKE :q1 OR c.Nombre_Categoria LIKE :q2 OR pr.Nombre_Empresa LIKE :q3)";
                $params[':q1'] = '%' . $q . '%'; $params[':q2'] = '%' . $q . '%'; $params[':q3'] = '%' . $q . '%';
            }
            try {
                $sql = "SELECT p.ID_Producto, p.Nombre_Producto, p.Precio_Actual, p.Stock_Actual,
                               p.Stock_Minimo,
                               p.ID_Categoria, p.ID_Proveedor, p.Imagen_URL, p.deleted_at,
                               (p.deleted_at IS NULL) AS Activo,
                               c.Nombre_Categoria, pr.Nombre_Empresa AS Proveedor
                        FROM producto p
                        LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                        LEFT JOIN proveedor pr ON p.ID_Proveedor = pr.ID_Proveedor
                        $where ORDER BY $orderSql $dir LIMIT $per OFFSET $off";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            } catch (PDOException $e) {
                // Pre-migración: sin columna Stock_Minimo
                $sql = "SELECT p.ID_Producto, p.Nombre_Producto, p.Precio_Actual, p.Stock_Actual,
                               10 AS Stock_Minimo,
                               p.ID_Categoria, p.ID_Proveedor, p.Imagen_URL, p.deleted_at,
                               (p.deleted_at IS NULL) AS Activo,
                               c.Nombre_Categoria, pr.Nombre_Empresa AS Proveedor
                        FROM producto p
                        LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                        LEFT JOIN proveedor pr ON p.ID_Proveedor = pr.ID_Proveedor
                        $where ORDER BY $orderSql $dir LIMIT $per OFFSET $off";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }
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
            try {
                $bajo = $this->db->query("SELECT COUNT(*) AS c FROM producto WHERE Stock_Actual > 0 AND Stock_Actual <= Stock_Minimo AND deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Pre-migración: umbral fijo 10
                $bajo = $this->db->query("SELECT COUNT(*) AS c FROM producto WHERE Stock_Actual > 0 AND Stock_Actual < 10 AND deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
            }
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
        $minimo = $datos['Stock_Minimo'] ?? $datos['stock_minimo'] ?? $datos['minimo'] ?? 10;

        if ($nombre === '' || !is_numeric($precio) || $precio <= 0) return false;
        if (!is_numeric($stock) || $stock < 0) return false;
        if (!ctype_digit((string)$cat) || !ctype_digit((string)$prov)) return false;
        if (!is_numeric($minimo) || $minimo < 0) $minimo = 10;
        $minimo = (int)$minimo;

        try {
            $conMinimo = true;
            try {
                $this->db->query("SELECT Stock_Minimo FROM producto LIMIT 0");
            } catch (PDOException $e) {
                $conMinimo = false;
            }
            if (!empty($id)) {
                if (!ctype_digit($id)) return false;
                if ($conMinimo) {
                    $stmt = $this->db->prepare("UPDATE producto SET Nombre_Producto=:n, Precio_Actual=:p, Stock_Actual=:s, ID_Categoria=:c, ID_Proveedor=:pr, Stock_Minimo=:m WHERE ID_Producto=:id AND deleted_at IS NULL");
                } else {
                    $stmt = $this->db->prepare("UPDATE producto SET Nombre_Producto=:n, Precio_Actual=:p, Stock_Actual=:s, ID_Categoria=:c, ID_Proveedor=:pr WHERE ID_Producto=:id AND deleted_at IS NULL");
                }
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            } else {
                if ($conMinimo) {
                    $stmt = $this->db->prepare("INSERT INTO producto (Nombre_Producto, Precio_Actual, Stock_Actual, ID_Categoria, ID_Proveedor, Stock_Minimo) VALUES (:n,:p,:s,:c,:pr,:m)");
                } else {
                    $stmt = $this->db->prepare("INSERT INTO producto (Nombre_Producto, Precio_Actual, Stock_Actual, ID_Categoria, ID_Proveedor) VALUES (:n,:p,:s,:c,:pr)");
                }
            }
            $stmt->bindParam(":n", $nombre);
            $stmt->bindParam(":p", $precio);
            $stmt->bindParam(":s", $stock, PDO::PARAM_INT);
            $stmt->bindParam(":c", $cat, PDO::PARAM_INT);
            $stmt->bindParam(":pr", $prov, PDO::PARAM_INT);
            if ($conMinimo) $stmt->bindParam(":m", $minimo, PDO::PARAM_INT);
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
            // Incluye agotados al final (etiqueta Agotado + lista de espera RF 5.8 / 4.6).
            $sql = "SELECT p.ID_Producto, p.Nombre_Producto, p.Precio_Actual, p.Stock_Actual,
                           p.Imagen_URL, c.Nombre_Categoria
                    FROM producto p
                    LEFT JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
                    WHERE p.deleted_at IS NULL
                    ORDER BY (p.Stock_Actual > 0) DESC, p.ID_Producto DESC LIMIT 100";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Producto::obtenerCatalogo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * RF 5.8: anota interés por un producto agotado (avisar al reponer).
     * Retorna ['ok'=>bool,'message'=>].
     */
    public function registrarEspera($idProducto, $nombre, $email)
    {
        if (!ctype_digit((string)$idProducto)) {
            return ['ok' => false, 'message' => 'Producto inválido.'];
        }
        $nombre = trim((string)$nombre);
        $email = strtolower(trim((string)$email));
        if (strlen($nombre) < 2 || strlen($nombre) > 150) {
            return ['ok' => false, 'message' => 'Indica tu nombre (2-150 caracteres).'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            return ['ok' => false, 'message' => 'Correo electrónico inválido.'];
        }
        try {
            $chk = $this->db->prepare("SELECT Stock_Actual, Nombre_Producto FROM producto WHERE ID_Producto = :p AND deleted_at IS NULL LIMIT 1");
            $chk->execute([':p' => $idProducto]);
            $prod = $chk->fetch(PDO::FETCH_ASSOC);
            if (!$prod) return ['ok' => false, 'message' => 'El producto no existe.'];
            if ((int)$prod['Stock_Actual'] > 0) {
                return ['ok' => false, 'message' => 'Buenas noticias: hay stock disponible, agrégalo al carrito.'];
            }
            $dup = $this->db->prepare("SELECT 1 FROM lista_espera_stock WHERE ID_Producto = :p AND Email = :e LIMIT 1");
            $dup->execute([':p' => $idProducto, ':e' => $email]);
            if ($dup->fetch()) {
                return ['ok' => false, 'message' => 'Ya estás en la lista de espera de este producto. Te avisaremos.'];
            }
            $ins = $this->db->prepare("INSERT INTO lista_espera_stock (Nombre_Completo, Email, ID_Producto) VALUES (:n, :e, :p)");
            $ins->execute([':n' => substr($nombre, 0, 150), ':e' => $email, ':p' => $idProducto]);
            return ['ok' => true, 'message' => 'Anotado. Te avisaremos al correo cuando vuelva a estar disponible.'];
        } catch (PDOException $e) {
            error_log("Producto::registrarEspera: " . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo registrar. Intenta más tarde.'];
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
