<?php
// model/Pqr.php — Módulo PQR + Valoraciones (RF 3.1-3.3, 4.8).
// Tablas: pqr (Tipo Queja/Reclamo/Solicitud, Estado Abierto/En Proceso/Cerrado),
// resena_valoracion (Calificacion 1-5, única por cliente+producto, solo comprados).
// Respeta triggers: trg_auto_asignar_pqr, trg_auditar_respuesta_pqr,
// trg_bloquear_resena_sin_compra, trg_unicidad_resena_cliente, trg_validar_rango_estrellas.
require_once __DIR__ . "/../config/conexion.php";

class Pqr
{
    private $db;
    private const TIPOS = ['Queja', 'Reclamo', 'Solicitud'];
    private const ESTADOS = ['Abierto', 'En Proceso', 'Cerrado'];

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
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

    /** Crea fila cliente si el usuario no tiene (igual que Venta::asegurarClienteParaUsuario). */
    public function asegurarCliente($idUsuario)
    {
        try {
            $stmt = $this->db->prepare("SELECT ID_Usuario, Email, ID_Cliente FROM usuario WHERE ID_Usuario = :id LIMIT 1");
            $stmt->execute([':id' => $idUsuario]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u) return null;
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
            $this->db->prepare("UPDATE usuario SET ID_Cliente = :c WHERE ID_Usuario = :u")
                      ->execute([':c' => $idCli, ':u' => $idUsuario]);
            return $idCli;
        } catch (PDOException $e) {
            error_log("Pqr::asegurarCliente: " . $e->getMessage());
            return null;
        }
    }

    // ==================== PQR (RF 3.1) ====================
    public function crear($idUsuario, $tipo, $descripcion)
    {
        $tipo = trim((string)$tipo);
        $descripcion = trim((string)$descripcion);
        if (!in_array($tipo, self::TIPOS, true)) {
            return ['ok' => false, 'message' => 'Tipo inválido: elige Queja, Reclamo o Solicitud.'];
        }
        if (strlen($descripcion) < 10) {
            return ['ok' => false, 'message' => 'Describe tu caso con al menos 10 caracteres.'];
        }
        if (strlen($descripcion) > 1000) {
            return ['ok' => false, 'message' => 'La descripción no puede superar 1000 caracteres.'];
        }
        $idCli = $this->asegurarCliente($idUsuario);
        if (empty($idCli)) {
            return ['ok' => false, 'message' => 'No se pudo identificar tu cuenta.'];
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO pqr (ID_Cliente, Tipo, Descripcion) VALUES (:c, :t, :d)");
            $stmt->execute([':c' => $idCli, ':t' => $tipo, ':d' => $descripcion]);
            return ['ok' => true, 'message' => 'PQR #' . (int)$this->db->lastInsertId() . ' registrado. Te avisaremos por este medio.', 'id' => (int)$this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Pqr::crear: " . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo registrar. Intenta más tarde.'];
        }
    }

    public function misPqr($idCliente, $page = 1, $per = 10)
    {
        try {
            $page = max(1, (int)$page);
            $per = in_array((int)$per, [5, 10, 20, 50], true) ? (int)$per : 10;
            $off = ($page - 1) * $per;
            $stmt = $this->db->prepare("SELECT ID_Pqr, Fecha_Registro, Tipo, Estado, Descripcion, Respuesta FROM pqr WHERE ID_Cliente = :c ORDER BY ID_Pqr DESC LIMIT $per OFFSET $off");
            $stmt->execute([':c' => $idCliente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function contarMis($idCliente)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS c FROM pqr WHERE ID_Cliente = :c");
            $stmt->execute([':c' => $idCliente]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($r['c'] ?? 0);
        } catch (PDOException $e) { return 0; }
    }

    // ==================== PANEL ADMIN (RF 3.3) ====================
    // $filtro: '' todos | 'pend' Abierto+En Proceso | 'res' Cerrado | valor exacto
    public function contarPanel($q = '', $filtro = '', $desde = '', $hasta = '')
    {
        try {
            [$where, $params] = $this->filtroPanel($q, $filtro, $desde, $hasta);
            $stmt = $this->db->prepare("SELECT COUNT(*) AS c FROM pqr p JOIN cliente c ON c.ID_Cliente = p.ID_Cliente $where");
            $stmt->execute($params);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($r['c'] ?? 0);
        } catch (PDOException $e) { return 0; }
    }

    public function panel($page = 1, $per = 10, $q = '', $filtro = '', $desde = '', $hasta = '')
    {
        try {
            $page = max(1, (int)$page);
            $per = in_array((int)$per, [5, 10, 20, 50], true) ? (int)$per : 10;
            $off = ($page - 1) * $per;
            [$where, $params] = $this->filtroPanel($q, $filtro, $desde, $hasta);
            $sql = "SELECT p.ID_Pqr, p.Fecha_Registro, p.Tipo, p.Estado, p.Descripcion, p.Respuesta,
                           c.Nombres, c.Apellidos,
                           (SELECT u.Email FROM usuario u WHERE u.ID_Cliente = p.ID_Cliente LIMIT 1) AS ClienteEmail
                    FROM pqr p JOIN cliente c ON c.ID_Cliente = p.ID_Cliente
                    $where ORDER BY p.ID_Pqr DESC LIMIT $per OFFSET $off";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Pqr::panel: " . $e->getMessage());
            return [];
        }
    }

    private function filtroPanel($q, $filtro, $desde, $hasta)
    {
        $conds = [];
        $params = [];
        $q = trim((string)$q);
        if ($q !== '') {
            $conds[] = "(p.ID_Pqr LIKE :q1 OR p.Descripcion LIKE :q2 OR c.Nombres LIKE :q3 OR c.Apellidos LIKE :q4)";
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
            $params[':q4'] = '%' . $q . '%';
        }
        if ($filtro === 'pend') {
            $conds[] = "p.Estado IN ('Abierto','En Proceso')";
        } elseif ($filtro === 'res') {
            $conds[] = "p.Estado = 'Cerrado'";
        } elseif (in_array($filtro, self::ESTADOS, true)) {
            $conds[] = "p.Estado = :est";
            $params[':est'] = $filtro;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$desde)) { $conds[] = 'DATE(p.Fecha_Registro) >= :desde'; $params[':desde'] = $desde; }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$hasta)) { $conds[] = 'DATE(p.Fecha_Registro) <= :hasta'; $params[':hasta'] = $hasta; }
        return [$conds ? ('WHERE ' . implode(' AND ', $conds)) : '', $params];
    }

    /** Responder / cambiar estado (solo Admin/Empleado). RF 3.3. */
    public function responder($idPqr, $respuesta, $estado, $idUsuarioGestor = null)
    {
        if (!ctype_digit((string)$idPqr)) {
            return ['ok' => false, 'message' => 'PQR inválido.'];
        }
        $respuesta = trim((string)$respuesta);
        $estado = trim((string)$estado);
        if (!in_array($estado, ['En Proceso', 'Cerrado'], true)) {
            return ['ok' => false, 'message' => 'Estado inválido: usa En Proceso o Cerrado.'];
        }
        if (strlen($respuesta) < 5) {
            return ['ok' => false, 'message' => 'La respuesta debe tener al menos 5 caracteres.'];
        }
        try {
            $idEmp = null;
            if (!empty($idUsuarioGestor) && ctype_digit((string)$idUsuarioGestor)) {
                $ge = $this->db->prepare("SELECT ID_Empleado FROM usuario WHERE ID_Usuario = :u LIMIT 1");
                $ge->execute([':u' => $idUsuarioGestor]);
                $gr = $ge->fetch(PDO::FETCH_ASSOC);
                if (!empty($gr['ID_Empleado'])) $idEmp = (int)$gr['ID_Empleado'];
            }
            $stmt = $this->db->prepare("UPDATE pqr SET Respuesta = :r, Estado = :e, ID_Empleado = :emp WHERE ID_Pqr = :p");
            $stmt->bindValue(':r', $respuesta);
            $stmt->bindValue(':e', $estado);
            if ($idEmp === null) $stmt->bindValue(':emp', null, PDO::PARAM_NULL);
            else $stmt->bindValue(':emp', $idEmp, PDO::PARAM_INT);
            $stmt->bindValue(':p', $idPqr, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                return ['ok' => false, 'message' => 'PQR no existe o ya estaba en ese estado con esa respuesta.'];
            }
            return ['ok' => true, 'message' => "PQR #$idPqr → $estado."];
        } catch (PDOException $e) {
            error_log("Pqr::responder: " . $e->getMessage());
            return ['ok' => false, 'message' => 'No se pudo guardar la respuesta.'];
        }
    }

    // ==================== VALORACIONES (RF 3.2 + 4.8) ====================
    /** Productos comprados por el cliente aún no calificados (selector del form). */
    public function productosPorCalificar($idCliente)
    {
        try {
            $sql = "SELECT DISTINCT p.ID_Producto, p.Nombre_Producto
                    FROM detalle_venta dv
                    JOIN venta v ON v.ID_Venta = dv.ID_Venta
                    JOIN producto p ON p.ID_Producto = dv.ID_Producto
                    LEFT JOIN resena_valoracion r ON r.ID_Cliente = v.ID_Cliente AND r.ID_Producto = p.ID_Producto
                    WHERE v.ID_Cliente = :c AND r.ID_Resena IS NULL
                    ORDER BY p.Nombre_Producto LIMIT 50";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':c' => $idCliente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function misResenas($idCliente)
    {
        try {
            $stmt = $this->db->prepare("SELECT r.ID_Resena, r.Calificacion, r.Comentario, r.Fecha_Publicacion, r.Estado_Moderacion, p.Nombre_Producto FROM resena_valoracion r JOIN producto p ON p.ID_Producto = r.ID_Producto WHERE r.ID_Cliente = :c ORDER BY r.ID_Resena DESC LIMIT 50");
            $stmt->execute([':c' => $idCliente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function calificar($idUsuario, $idProducto, $estrellas, $comentario = '')
    {
        if (!ctype_digit((string)$idProducto)) {
            return ['ok' => false, 'message' => 'Producto inválido.'];
        }
        $estrellas = (int)$estrellas;
        if ($estrellas < 1 || $estrellas > 5) {
            return ['ok' => false, 'message' => 'Califica de 1 a 5 estrellas.'];
        }
        $comentario = trim((string)$comentario);
        if (strlen($comentario) > 500) {
            return ['ok' => false, 'message' => 'El comentario no puede superar 500 caracteres.'];
        }
        $idCli = $this->asegurarCliente($idUsuario);
        if (empty($idCli)) {
            return ['ok' => false, 'message' => 'No se pudo identificar tu cuenta.'];
        }
        try {
            // Amable antes que el trigger: solo comprados y sin duplicar
            $chk = $this->db->prepare("SELECT 1 FROM detalle_venta dv JOIN venta v ON v.ID_Venta = dv.ID_Venta WHERE v.ID_Cliente = :c AND dv.ID_Producto = :p LIMIT 1");
            $chk->execute([':c' => $idCli, ':p' => $idProducto]);
            if (!$chk->fetch()) {
                return ['ok' => false, 'message' => 'Solo puedes calificar productos que hayas comprado.'];
            }
            $dup = $this->db->prepare("SELECT 1 FROM resena_valoracion WHERE ID_Cliente = :c AND ID_Producto = :p LIMIT 1");
            $dup->execute([':c' => $idCli, ':p' => $idProducto]);
            if ($dup->fetch()) {
                return ['ok' => false, 'message' => 'Ya calificaste este producto (sin duplicados).'];
            }
            $ins = $this->db->prepare("INSERT INTO resena_valoracion (ID_Cliente, ID_Producto, Calificacion, Comentario) VALUES (:c, :p, :e, :m)");
            $ins->execute([':c' => $idCli, ':p' => $idProducto, ':e' => $estrellas, ':m' => ($comentario !== '' ? $comentario : null)]);
            return ['ok' => true, 'message' => '¡Gracias por tu calificación de ' . $estrellas . '★!'];
        } catch (PDOException $e) {
            error_log("Pqr::calificar: " . $e->getMessage());
            $msg = $e->getMessage();
            if (strpos($msg, 'comprar la prenda') !== false) return ['ok' => false, 'message' => 'Solo puedes calificar productos que hayas comprado.'];
            if (strpos($msg, 'ya rese') !== false) return ['ok' => false, 'message' => 'Ya calificaste este producto (sin duplicados).'];
            if (strpos($msg, 'rango') !== false) return ['ok' => false, 'message' => 'Califica de 1 a 5 estrellas.'];
            return ['ok' => false, 'message' => 'No se pudo guardar tu calificación.'];
        }
    }
}
