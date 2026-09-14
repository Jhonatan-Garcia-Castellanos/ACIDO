<?php
// model/Movimiento.php — Kardex y ajustes manuales (RF 2.7 / RF 2.9).
// La tabla movimiento_inventario es INMUTABLE desde la app: solo INSERT
// y lectura. Sin UPDATE/DELETE (auditoría inalterable).
require_once __DIR__ . "/../config/conexion.php";

class Movimiento
{
    private $db;
    const TIPOS = ['Entrada', 'Salida', 'Ajuste'];

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
    }

    /** Resuelve ID_Empleado para el responsable. Empleado > primer empleado > empleado Sistema (auto). */
    public function resolverEmpleado($idUsuario)
    {
        try {
            if (!empty($idUsuario) && ctype_digit((string)$idUsuario)) {
                $st = $this->db->prepare("SELECT ID_Empleado FROM usuario WHERE ID_Usuario = :u LIMIT 1");
                $st->execute([':u' => $idUsuario]);
                $r = $st->fetch(PDO::FETCH_ASSOC);
                if (!empty($r['ID_Empleado'])) return (int)$r['ID_Empleado'];
            }
            return $this->asegurarEmpleadoSistema();
        } catch (PDOException $e) {
            return null;
        }
    }

    /** Garantiza un empleado responsable: reusa el primero o crea "Sistema Kardex" una sola vez. */
    public function asegurarEmpleadoSistema()
    {
        try {
            $f = $this->db->query("SELECT ID_Empleado FROM empleado WHERE deleted_at IS NULL ORDER BY ID_Empleado LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($f) return (int)$f['ID_Empleado'];
            $cargo = $this->db->query("SELECT ID_Cargo FROM cargo ORDER BY ID_Cargo LIMIT 1")->fetchColumn();
            $ciu = $this->db->query("SELECT ID_Ciudad FROM ciudad ORDER BY ID_Ciudad LIMIT 1")->fetchColumn();
            if (empty($cargo) || empty($ciu)) return null;
            $ins = $this->db->prepare("INSERT INTO empleado (Nombres, Apellidos, ID_Cargo, ID_Ciudad) VALUES ('Sistema','Kardex',:c,:ciu)");
            $ins->execute([':c' => $cargo, ':ciu' => $ciu]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Movimiento::asegurarEmpleado: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ajuste/Entrada manual con Motivo obligatorio (RF 2.9).
     * $tipo: Entrada (suma) | Ajuste (suma o resta según $cantidad con signo, o $esResta).
     * Retorna ['ok'=>bool,'message'=>string].
     */
    public function ajustar($idProducto, $tipo, $cantidad, $motivo, $idUsuario)
    {
        $motivo = trim((string)$motivo);
        if (!ctype_digit((string)$idProducto)) return ['ok' => false, 'message' => 'Producto inválido'];
        if (!in_array($tipo, ['Entrada', 'Ajuste'], true)) return ['ok' => false, 'message' => 'Tipo inválido (Entrada/Ajuste)'];
        if (!is_numeric($cantidad) || (int)$cantidad <= 0) return ['ok' => false, 'message' => 'Cantidad debe ser > 0'];
        if ($motivo === '') return ['ok' => false, 'message' => 'El Motivo es obligatorio (pérdida, daño, devolución, reposición)'];
        if (mb_strlen($motivo) > 255) return ['ok' => false, 'message' => 'Motivo máximo 255 caracteres'];
        $cantidad = (int)$cantidad;
        $esResta = isset($_POST['es_resta']) ? (string)$_POST['es_resta'] : null;
        // Convención: Ajuste con prefijo "resta" o motivo pérdida/daño resta; Entrada siempre suma.
        $resta = ($tipo === 'Ajuste' && ($esResta === '1' || preg_match('/p[eé]rdida|da[ñn]o|merma|vencim/i', $motivo)));

        $idEmpleado = $this->resolverEmpleado($idUsuario);
        if (empty($idEmpleado)) return ['ok' => false, 'message' => 'Sin empleado responsable: crea un empleado para registrar el Kardex'];

        try {
            $this->db->beginTransaction();
            $p = $this->db->prepare("SELECT Stock_Actual FROM producto WHERE ID_Producto = :p LIMIT 1 FOR UPDATE");
            $p->execute([':p' => $idProducto]);
            $prod = $p->fetch(PDO::FETCH_ASSOC);
            if (!$prod) { $this->db->rollBack(); return ['ok' => false, 'message' => 'Producto no existe']; }
            $nuevo = (int)$prod['Stock_Actual'] + ($resta ? -$cantidad : $cantidad);
            if ($nuevo < 0) { $this->db->rollBack(); return ['ok' => false, 'message' => "Stock insuficiente (hay {$prod['Stock_Actual']})"]; }

            $ins = $this->db->prepare(
                "INSERT INTO movimiento_inventario (ID_Producto, Tipo_Movimiento, Cantidad, Motivo, ID_Empleado) VALUES (:p,:t,:c,:m,:e)"
            );
            $cantDb = $resta ? -$cantidad : $cantidad;
            $ins->execute([':p' => $idProducto, ':t' => $tipo, ':c' => $cantDb, ':m' => $motivo, ':e' => $idEmpleado]);
            $up = $this->db->prepare("UPDATE producto SET Stock_Actual = :s WHERE ID_Producto = :p");
            $up->execute([':s' => $nuevo, ':p' => $idProducto]);
            $this->db->commit();
            // Datos para el correo al admin (best-effort, no afecta el ajuste).
            $info = ['nombre' => ('ID ' . $idProducto), 'minimo' => 10];
            try {
                try {
                    $q = $this->db->prepare("SELECT Nombre_Producto, Stock_Minimo FROM producto WHERE ID_Producto = :p LIMIT 1");
                } catch (PDOException $e2) {
                    $q = $this->db->prepare("SELECT Nombre_Producto FROM producto WHERE ID_Producto = :p LIMIT 1");
                }
                $q->execute([':p' => $idProducto]);
                if ($r = $q->fetch(PDO::FETCH_ASSOC)) {
                    $info['nombre'] = $r['Nombre_Producto'] ?? $info['nombre'];
                    if (isset($r['Stock_Minimo'])) $info['minimo'] = (int)$r['Stock_Minimo'];
                }
            } catch (Exception $e2) {}
            return ['ok' => true,
                'message' => $resta ? "Ajuste registrado (resta $cantidad). Stock: $nuevo" : "Movimiento registrado. Stock: $nuevo",
                'producto' => $info['nombre'], 'stock_nuevo' => $nuevo, 'stock_minimo' => $info['minimo'],
                'tipo' => $tipo, 'cantidad' => $cantidad, 'motivo' => $motivo, 'resta' => $resta];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Movimiento::ajustar: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Stock insuficiente') !== false) return ['ok' => false, 'message' => 'Stock insuficiente'];
            return ['ok' => false, 'message' => 'No se pudo registrar el movimiento'];
        }
    }

    /** Salida automática por venta (RF 2.7 trazabilidad, best-effort, no rompe checkout). */
    public function registrarSalidaVenta($idProducto, $cantidad, $idVenta, $idEmpleado = null)
    {
        try {
            if (empty($idEmpleado)) $idEmpleado = $this->asegurarEmpleadoSistema();
            if (empty($idEmpleado)) return false;
            $ins = $this->db->prepare(
                "INSERT INTO movimiento_inventario (ID_Producto, Tipo_Movimiento, Cantidad, Motivo, ID_Empleado) VALUES (:p,'Salida',:c,:m,:e)"
            );
            return $ins->execute([
                ':p' => $idProducto,
                ':c' => -abs((int)$cantidad),
                ':m' => substr("Salida por venta #$idVenta", 0, 255),
                ':e' => $idEmpleado,
            ]);
        } catch (PDOException $e) {
            error_log("Movimiento::salida: " . $e->getMessage());
            return false;
        }
    }

    /** Kardex general con responsable y producto (solo lectura). */
    public function listar($limit = 100, $idProducto = null)
    {
        $limit = max(1, min(300, (int)$limit));
        try {
            $where = '';
            $params = [];
            if (!empty($idProducto) && ctype_digit((string)$idProducto)) {
                $where = 'WHERE m.ID_Producto = :p';
                $params[':p'] = $idProducto;
            }
            $sql = "SELECT m.ID_Movimiento, m.Fecha_Movimiento, m.Tipo_Movimiento, m.Cantidad, m.Motivo,
                           p.Nombre_Producto, CONCAT(e.Nombres,' ',e.Apellidos) AS Responsable
                    FROM movimiento_inventario m
                    JOIN producto p ON p.ID_Producto = m.ID_Producto
                    LEFT JOIN empleado e ON e.ID_Empleado = m.ID_Empleado
                    $where ORDER BY m.ID_Movimiento DESC LIMIT $limit";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Movimiento::listar: " . $e->getMessage());
            return [];
        }
    }
}
