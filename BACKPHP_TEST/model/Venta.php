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
            return $this->db->query("SELECT ID_Metodo, Tipo_Metodo, Logo FROM metodo_pago ORDER BY ID_Metodo")->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->db->prepare("SELECT ID_Producto, Nombre_Producto, Precio_Actual, Stock_Actual, deleted_at, (deleted_at IS NULL) AS Activo, Imagen_URL FROM producto WHERE ID_Producto IN ($place)");
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
     * $pagoDetalle = ['entidad' => 'Visa/Bancolombia/Nequi', 'referencia' => numero enmascarado]
     * Retorna ['ID_Venta'=>, 'total'=>, 'factura'=>] o ['error'=>msg].
     */
    public function checkout($idUsuario, $cart, $idMetodo, $pagoDetalle = [])
    {
        if (empty($cart)) return ['error' => 'Carrito vacío'];
        if (!ctype_digit((string)$idMetodo)) return ['error' => 'Método de pago inválido'];
        $entidad = trim($pagoDetalle['entidad'] ?? '');
        $referencia = trim($pagoDetalle['referencia'] ?? '');
        if ($entidad === '' || $referencia === '') return ['error' => 'Completa la información del pago (banco y número).'];
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
                $p = $this->db->prepare("SELECT Precio_Actual, Stock_Actual, deleted_at FROM producto WHERE ID_Producto = :p LIMIT 1");
                $p->execute([':p' => $idProd]);
                $prod = $p->fetch(PDO::FETCH_ASSOC);
                if (!$prod) { $this->db->rollBack(); return ['error' => "Producto $idProd no existe"]; }
                if (!empty($prod['deleted_at'])) { $this->db->rollBack(); return ['error' => "Producto $idProd está inactivo"]; }
                // El trigger trg_bloquear_compra_sin_stock valida stock de nuevo; chequeo previo para mensaje claro
                if ((int)$prod['Stock_Actual'] < $qty) { $this->db->rollBack(); return ['error' => "Sin stock suficiente para producto $idProd (hay {$prod['Stock_Actual']})"]; }
                $precio = (float)$prod['Precio_Actual'];
                $total += $precio * $qty;
                // Precio_Venta_Historico lo congela el trigger, pero enviamos el real
                $d = $this->db->prepare("INSERT INTO detalle_venta (ID_Venta, ID_Producto, Cantidad, Precio_Venta_Historico) VALUES (:v,:p,:q,:pr)");
                $d->execute([':v' => $idVenta, ':p' => $idProd, ':q' => $qty, ':pr' => $precio]);
            }
            if ($total <= 0) { $this->db->rollBack(); return ['error' => 'Total inválido']; }
            // RF 2.8: el pedido nace Pendiente (reserva) y se confirma a Pagado
            // ANTES del pago/descuento, para que el guard RF 2.10 valide stock real.
            $dir = $this->direccionEnvio($idCliente);
            if (empty($dir[1])) { $this->db->rollBack(); return ['error' => 'Sin cobertura de envío configurada']; }
            $insP = $this->db->prepare("INSERT INTO pedido (ID_Venta, Direccion_Envio, Ciudad_Envio, Tipo_Envio, Estado_Pedido) VALUES (:v,:d,:c,'Estándar','Pendiente')");
            $insP->execute([':v' => $idVenta, ':d' => $dir[0], ':c' => $dir[1]]);
            $idPedido = (int)$this->db->lastInsertId();
            $upP = $this->db->prepare("UPDATE pedido SET Estado_Pedido = 'Pagado' WHERE ID_Pedido = :p");
            $upP->execute([':p' => $idPedido]);
            $pg = $this->db->prepare("INSERT INTO pago (ID_Venta, ID_Metodo, Monto_Pagado, Entidad_Bancaria, Numero_Referencia) VALUES (:v,:m,:t,:e,:r)");
            $pg->execute([':v' => $idVenta, ':m' => $idMetodo, ':t' => $total, ':e' => $entidad, ':r' => $referencia]);
            // Factura auto via trg_generar_factura_auto
            $f = $this->db->prepare("SELECT Numero_Factura FROM factura WHERE ID_Venta = :v LIMIT 1");
            $f->execute([':v' => $idVenta]);
            $fac = $f->fetch(PDO::FETCH_ASSOC);
            $this->db->commit();
            // RF 2.3: aviso email best-effort si algo quedó bajo mínimo (no rompe la venta si falla).
            try {
                $this->avisarStockBajo(array_keys($cart));
            } catch (Exception $e) {}
            // RF 2.7: trazabilidad Salida en Kardex (best-effort).
            try {
                require_once __DIR__ . "/Movimiento.php";
                $mv = new Movimiento();
                $idEmp = $mv->resolverEmpleado($idUsuario);
                foreach ($cart as $idProd => $qty) {
                    if (ctype_digit((string)$idProd)) $mv->registrarSalidaVenta($idProd, $qty, $idVenta, $idEmp);
                }
            } catch (Exception $e) {}
            return ['ID_Venta' => $idVenta, 'ID_Pedido' => $idPedido, 'total' => $total, 'factura' => $fac['Numero_Factura'] ?? null];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Venta::checkout: " . $e->getMessage());
            $msg = $e->getMessage();
            // Mensajes de triggers SIGNAL 45000
            if (strpos($msg, 'Sin disponibilidad') !== false) return ['error' => 'Sin stock disponible'];
            if (strpos($msg, 'Máximo 10') !== false) return ['error' => 'Máximo 10 unidades por producto'];
            if (strpos($msg, 'ya fue pagada') !== false) return ['error' => 'Venta ya pagada'];
            if (strpos($msg, 'RF 2.10') !== false) return ['error' => 'Sin stock suficiente para confirmar el pedido'];
            if (strpos($msg, 'Desde Pendiente') !== false || strpos($msg, 'Desde Pagado') !== false
                || strpos($msg, 'desde Preparando') !== false || strpos($msg, 'Desde En camino') !== false) {
                return ['error' => 'Transición de pedido no permitida'];
            }
            return ['error' => 'No se pudo completar la compra'];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /** Dirección de envío: principal del cliente o fallback auditable. Retorna [direccion, idCiudad]. */
    private function direccionEnvio($idCliente)
    {
        try {
            $st = $this->db->prepare(
                "SELECT Direccion_Exacta, ID_Ciudad FROM libreta_direcciones " .
                "WHERE ID_Cliente = :c ORDER BY Es_Principal DESC, ID_Direccion LIMIT 1"
            );
            $st->execute([':c' => $idCliente]);
            if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                return [$r['Direccion_Exacta'], $r['ID_Ciudad']];
            }
            $ciu = $this->db->query("SELECT ID_Ciudad FROM ciudad ORDER BY ID_Ciudad LIMIT 1")->fetchColumn();
            return ['Dirección por confirmar (Cliente #' . (int)$idCliente . ')', $ciu ?: null];
        } catch (PDOException $e) {
            return ['Dirección por confirmar', null];
        }
    }

    /**
     * Avanza/retrocede un pedido de forma controlada (RF 2.8/2.10).
     * La secuencia y el stock los hacen cumplir los triggers; aquí solo se
     * traduce el error SQL a mensaje claro. Retorna ['ok'=>bool,'message'=>].
     */
    public function cambiarEstadoPedido($idPedido, $nuevoEstado)
    {
        $permitidos = ['Pagado', 'Preparando', 'En camino', 'Entregado', 'Cancelado'];
        if (!ctype_digit((string)$idPedido)) return ['ok' => false, 'message' => 'Pedido inválido'];
        if (!in_array($nuevoEstado, $permitidos, true)) return ['ok' => false, 'message' => 'Estado inválido'];
        try {
            $st = $this->db->prepare("UPDATE pedido SET Estado_Pedido = :e WHERE ID_Pedido = :p");
            $st->execute([':e' => $nuevoEstado, ':p' => $idPedido]);
            if ($st->rowCount() === 0) return ['ok' => false, 'message' => 'Pedido no existe o ya está en ese estado'];
            return ['ok' => true, 'message' => "Pedido #$idPedido → $nuevoEstado"];
        } catch (PDOException $e) {
            error_log("Venta::cambiarEstadoPedido: " . $e->getMessage());
            $msg = $e->getMessage();
            if (strpos($msg, 'RF 2.10') !== false) return ['ok' => false, 'message' => 'Bloqueado (RF 2.10): stock insuficiente para ese estado'];
            if (preg_match('/Error:\s*(.+?)(\s*\.|$)/', $msg, $m)) return ['ok' => false, 'message' => trim($m[1], '. ')];
            return ['ok' => false, 'message' => 'No se pudo cambiar el estado'];
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
                           (SELECT p.Entidad_Bancaria FROM pago p WHERE p.ID_Venta = v.ID_Venta LIMIT 1) AS Entidad,
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

    /** RF 2.2: items con imagen/código/precio + comprador (nombre, documento, usuario). */
    public function detalleCompleto($idVenta)
    {
        try {
            $st = $this->db->prepare(
                "SELECT dv.ID_Producto, dv.Cantidad, dv.Precio_Venta_Historico,
                        p.Nombre_Producto, p.Imagen_URL
                 FROM detalle_venta dv JOIN producto p ON p.ID_Producto = dv.ID_Producto
                 WHERE dv.ID_Venta = :v"
            );
            $st->execute([':v' => $idVenta]);
            $items = $st->fetchAll(PDO::FETCH_ASSOC);
            $cl = $this->db->prepare(
                "SELECT v.ID_Cliente, c.Nombres, c.Apellidos, c.Documento, c.Telefono,
                        u.Email, u.Seudonimo
                 FROM venta v JOIN cliente c ON c.ID_Cliente = v.ID_Cliente
                 LEFT JOIN usuario u ON u.ID_Cliente = v.ID_Cliente
                 WHERE v.ID_Venta = :v LIMIT 1"
            );
            $cl->execute([':v' => $idVenta]);
            return ['items' => $items, 'cliente' => $cl->fetch(PDO::FETCH_ASSOC) ?: []];
        } catch (PDOException $e) { return ['items' => [], 'cliente' => []]; }
    }

    /** RF 2.5: ventas pendientes por entregar (Preparando/En camino). */
    public function listarPendientes($idCliente = null)
    {
        try {
            $where = "WHERE pe.Estado_Pedido IN ('Pendiente','Pagado','Preparando','En camino')";
            $params = [];
            if ($idCliente !== null) { $where .= ' AND v.ID_Cliente = :c'; $params[':c'] = $idCliente; }
            $sql = "SELECT pe.ID_Pedido, pe.Estado_Pedido, pe.Direccion_Envio, pe.Tipo_Envio,
                           ci.Nombre_Ciudad, v.ID_Venta, v.Fecha_Venta AS Fecha_Compra,
                           c.Nombres, c.Apellidos,
                           (SELECT COALESCE(SUM(dv.Cantidad),0) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Unidades,
                           (SELECT COALESCE(SUM(dv.Cantidad*dv.Precio_Venta_Historico),0) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Total
                    FROM pedido pe JOIN venta v ON v.ID_Venta = pe.ID_Venta
                    JOIN cliente c ON c.ID_Cliente = v.ID_Cliente
                    LEFT JOIN ciudad ci ON ci.ID_Ciudad = pe.Ciudad_Envio
                    $where ORDER BY v.ID_Venta DESC LIMIT 100";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    /** RF 2.4: compras entregadas. */
    public function listarEntregadas($idCliente = null)
    {
        try {
            $where = "WHERE pe.Estado_Pedido = 'Entregado'";
            $params = [];
            if ($idCliente !== null) { $where .= ' AND v.ID_Cliente = :c'; $params[':c'] = $idCliente; }
            $sql = "SELECT pe.ID_Pedido, pe.Estado_Pedido, pe.Direccion_Envio,
                           v.ID_Venta, v.Fecha_Venta AS Fecha_Compra,
                           c.Nombres, c.Apellidos,
                           (SELECT COALESCE(SUM(dv.Cantidad*dv.Precio_Venta_Historico),0) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Total,
                           (SELECT COUNT(*) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Items
                    FROM pedido pe JOIN venta v ON v.ID_Venta = pe.ID_Venta
                    JOIN cliente c ON c.ID_Cliente = v.ID_Cliente
                    $where ORDER BY v.ID_Venta DESC LIMIT 100";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    /** RF 2.6: facturas generadas con cliente, total y fecha. */
    public function listarFacturas($idCliente = null)
    {
        try {
            $where = '';
            $params = [];
            if ($idCliente !== null) { $where = 'WHERE v.ID_Cliente = :c'; $params[':c'] = $idCliente; }
            $sql = "SELECT f.Numero_Factura, f.Fecha_Emision, v.ID_Venta,
                           c.Nombres, c.Apellidos,
                           (SELECT COALESCE(SUM(dv.Cantidad*dv.Precio_Venta_Historico),0) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Total,
                           (SELECT COUNT(*) FROM detalle_venta dv WHERE dv.ID_Venta = v.ID_Venta) AS Items
                    FROM factura f JOIN venta v ON v.ID_Venta = f.ID_Venta
                    JOIN cliente c ON c.ID_Cliente = v.ID_Cliente
                    $where ORDER BY f.ID_Factura DESC LIMIT 100";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
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

    /** RF 2.3: tras commit, email a admins SOLO por episodios nuevos de stock bajo.
     *  Usa AlertaStock::sincronizarBajoMinimo (dedup 24h): si la alerta ya
     *  existía sin leer, el admin ya fue notificado y no se reenvía. */
    private function avisarStockBajo($ids)
    {
        try {
            require_once __DIR__ . "/AlertaStock.php";
            require_once __DIR__ . "/../lib/Mailer.php";
            $al = new AlertaStock();
            $nuevas = $al->sincronizarBajoMinimo();
            if (empty($nuevas)) return;
            $adm = $al->emailsAdmins();
            $adm = array_values(array_filter((array)$adm));
            if (empty($adm)) return;
            $m = new Mailer();
            if ($m->isConfigured()) $m->enviarStockBajo($adm, $nuevas);
        } catch (Exception $e) {
            error_log("Venta::avisarStockBajo: " . $e->getMessage());
        }
    }
}
