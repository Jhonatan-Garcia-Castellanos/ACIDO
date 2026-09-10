<?php
require_once __DIR__ . "/../config/conexion.php";

class Venta
{
    private $db;

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
    }

    public function metodosPago()
    {
        try {
            return $this->db->query("SELECT ID_Metodo, Tipo_Metodo FROM metodo_pago ORDER BY ID_Metodo")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    /** Crea fila cliente si el usuario no tiene ID_Cliente y lo enlaza. Retorna ID_Cliente. */
    public function asegurarClienteParaUsuario($idUsuario)
    {
        $stmt = $this->db->prepare("SELECT ID_Usuario, Email, ID_Cliente FROM usuario WHERE ID_Usuario = :id LIMIT 1");
        $stmt->execute([':id' => $idUsuario]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u) throw new Exception("Usuario no existe");
        if (!empty($u['ID_Cliente'])) {
            $chk = $this->db->prepare("SELECT ID_Cliente FROM cliente WHERE ID_Cliente = :c LIMIT 1");
            $chk->execute([':c' => $u['ID_Cliente']]);
            if ($chk->fetch()) return (int)$u['ID_Cliente'];
        }
        $base = explode('@', strtolower($u['Email']))[0] ?? 'cliente';
        $base = preg_replace('/[^a-z0-9]/i', ' ', $base);
        $nombres = ucwords(trim($base) !== '' ? trim($base) : 'Cliente');
        $ins = $this->db->prepare("INSERT INTO cliente (Nombres, Apellidos) VALUES (:n, 'Cliente')");
        $ins->execute([':n' => substr($nombres, 0, 50)]);
        $idCli = (int)$this->db->lastInsertId();
        $up = $this->db->prepare("UPDATE usuario SET ID_Cliente = :c WHERE ID_Usuario = :u");
        $up->execute([':c' => $idCli, ':u' => $idUsuario]);
        return $idCli;
    }

    public function detalleCarrito($cart)
    {
        if (empty($cart)) return ['items' => [], 'total' => 0];
        $ids = array_keys($cart);
        $place = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT ID_Producto, Nombre_Producto, Precio_Actual, Stock_Actual, Activo, Imagen_URL FROM producto WHERE ID_Producto IN ($place)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        $total = 0;
        foreach ($rows as $r) {
            $id = (int)$r['ID_Producto'];
            $qty = max(1, min(10, (int)($cart[$id] ?? 1)));
            $sub = $qty * (float)$r['Precio_Actual'];
            $total += $sub;
            $items[] = array_merge($r, ['cantidad' => $qty, 'subtotal' => $sub]);
        }
        return ['items' => $items, 'total' => $total];
    }

    /**
     * Checkout: crea venta + detalles + pago (factura y descuento stock via triggers).
     * Retorna ['ID_Venta'=>, 'total'=>, 'factura'=>] o ['error'=>msg].
     */
    public function checkout($idUsuario, $cart, $idMetodo)
    {
        if (empty($cart)) return ['error' => 'Carrito vacío'];
        if (!ctype_digit((string)$idMetodo)) return ['error' => 'Método de pago inválido'];
        try {
            $this->db->beginTransaction();
            $mm = $this->db->prepare("SELECT ID_Metodo FROM metodo_pago WHERE ID_Metodo = :m LIMIT 1");
            $mm->execute([':m' => $idMetodo]);
            if (!$mm->fetch()) { $this->db->rollBack(); return ['error' => 'Método de pago no existe']; }

            $idCliente = $this->asegurarClienteParaUsuario($idUsuario);

            $insV = $this->db->prepare("INSERT INTO venta (ID_Cliente) VALUES (:c)");
            $insV->execute([':c' => $idCliente]);
            $idVenta = (int)$this->db->lastInsertId();

            $total = 0;
            foreach ($cart as $idProd => $qty) {
                if (!ctype_digit((string)$idProd)) continue;
                $qty = (int)$qty;
                if ($qty < 1 || $qty > 10) { $this->db->rollBack(); return ['error' => "Cantidad inválida (1-10) en producto $idProd"]; }
                $p = $this->db->prepare("SELECT Precio_Actual, Stock_Actual, Activo FROM producto WHERE ID_Producto = :p LIMIT 1");
                $p->execute([':p' => $idProd]);
                $prod = $p->fetch(PDO::FETCH_ASSOC);
                if (!$prod) { $this->db->rollBack(); return ['error' => "Producto $idProd no existe"]; }
                if ((int)$prod['Activo'] !== 1) { $this->db->rollBack(); return ['error' => "Producto $idProd está inactivo"]; }
                // El trigger trg_bloquear_compra_sin_stock valida stock de nuevo; chequeo previo para mensaje claro
                if ((int)$prod['Stock_Actual'] < $qty) { $this->db->rollBack(); return ['error' => "Sin stock suficiente para producto $idProd (hay {$prod['Stock_Actual']})"]; }
                $precio = (float)$prod['Precio_Actual'];
                $total += $precio * $qty;
                // Precio_Venta_Historico lo congela el trigger, pero enviamos el real
                $d = $this->db->prepare("INSERT INTO detalle_venta (ID_Venta, ID_Producto, Cantidad, Precio_Venta_Historico) VALUES (:v,:p,:q,:pr)");
                $d->execute([':v' => $idVenta, ':p' => $idProd, ':q' => $qty, ':pr' => $precio]);
            }
            if ($total <= 0) { $this->db->rollBack(); return ['error' => 'Total inválido']; }
            $pg = $this->db->prepare("INSERT INTO pago (ID_Venta, ID_Metodo, Monto_Pagado) VALUES (:v,:m,:t)");
            $pg->execute([':v' => $idVenta, ':m' => $idMetodo, ':t' => $total]);
            // Factura auto via trg_generar_factura_auto
            $f = $this->db->prepare("SELECT Numero_Factura FROM factura WHERE ID_Venta = :v LIMIT 1");
            $f->execute([':v' => $idVenta]);
            $fac = $f->fetch(PDO::FETCH_ASSOC);
            $this->db->commit();
            return ['ID_Venta' => $idVenta, 'total' => $total, 'factura' => $fac['Numero_Factura'] ?? null];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Venta::checkout: " . $e->getMessage());
            $msg = $e->getMessage();
            // Mensajes de triggers SIGNAL 45000
            if (strpos($msg, 'Sin disponibilidad') !== false) return ['error' => 'Sin stock disponible'];
            if (strpos($msg, 'Máximo 10') !== false) return ['error' => 'Máximo 10 unidades por producto'];
            if (strpos($msg, 'ya fue pagada') !== false) return ['error' => 'Venta ya pagada'];
            return ['error' => 'No se pudo completar la compra'];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    public function listarVentas($idCliente = null)
    {
        try {
            $where = '';
            $params = [];
            if ($idCliente !== null) { $where = 'WHERE v.ID_Cliente = :c'; $params[':c'] = $idCliente; }
            $sql = "SELECT v.ID_Venta, v.Fecha_Venta, v.ID_Cliente,
                           u.Email AS ClienteEmail,
                           (SELECT COALESCE(SUM(dv.Cantidad * dv.Precio_Venta_Historico),0) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Total,
                           (SELECT COUNT(*) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Items,
                           (SELECT f.Numero_Factura FROM factura f WHERE f.ID_Venta = v.ID_Venta LIMIT 1) AS Factura,
                           (SELECT mp.Tipo_Metodo FROM pago p JOIN metodo_pago mp ON p.ID_Metodo = mp.ID_Metodo WHERE p.ID_Venta = v.ID_Venta LIMIT 1) AS Metodo
                    FROM venta v
                    LEFT JOIN usuario u ON u.ID_Cliente = v.ID_Cliente
                    $where
                    ORDER BY v.ID_Venta DESC LIMIT 100";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Venta::listar: " . $e->getMessage());
            return [];
        }
    }

    public function detalleVenta($idVenta)
    {
        try {
            $stmt = $this->db->prepare("SELECT dv.Cantidad, dv.Precio_Venta_Historico, p.Nombre_Producto
                                        FROM detalle_venta dv JOIN producto p ON dv.ID_Producto = p.ID_Producto
                                        WHERE dv.ID_Venta = :v");
            $stmt->execute([':v' => $idVenta]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    /** Resumen automático del día (se recalcula en cada carga). Respeta filtro cliente. */
    public function resumenHoy($idCliente = null)
    {
        try {
            $where = 'WHERE DATE(v.Fecha_Venta) = CURDATE()';
            $params = [];
            if ($idCliente !== null) { $where .= ' AND v.ID_Cliente = :c'; $params[':c'] = $idCliente; }
            $sql = "SELECT COUNT(DISTINCT v.ID_Venta) AS ventas,
                           COALESCE(SUM(dv.Cantidad * dv.Precio_Venta_Historico),0) AS ganancias,
                           COALESCE(AVG(dv.Cantidad * dv.Precio_Venta_Historico),0) AS ticket
                    FROM venta v LEFT JOIN detalle_venta dv ON dv.ID_Venta = v.ID_Venta
                    $where";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['ventas' => (int)($r['ventas'] ?? 0), 'ganancias' => (float)($r['ganancias'] ?? 0), 'ticket' => (float)($r['ticket'] ?? 0)];
        } catch (PDOException $e) { return ['ventas'=>0,'ganancias'=>0,'ticket'=>0]; }
    }

    public function idClienteDeUsuario($idUsuario)
    {
        try {
            $stmt = $this->db->prepare("SELECT ID_Cliente FROM usuario WHERE ID_Usuario = :u LIMIT 1");
            $stmt->execute([':u' => $idUsuario]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return $r['ID_Cliente'] ?? null;
        } catch (PDOException $e) { return null; }
    }
}
