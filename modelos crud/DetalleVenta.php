<?php
require_once 'Conexion.php';

class DetalleVenta {

    // --- CREATE (Insertar detalle de venta) ---
    public function crear($id_venta, $id_producto, $cantidad, $precio_venta_historico) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO detalle_venta (ID_Venta, ID_Producto, Cantidad, Precio_Venta_Historico) VALUES (:id_venta, :id_producto, :cantidad, :precio_venta_historico)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_venta' => $id_venta,
            'id_producto' => $id_producto,
            'cantidad' => $cantidad,
            'precio_venta_historico' => $precio_venta_historico
        ]);
    }

    // --- READ (Ver todos los detalles de venta) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM detalle_venta";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver detalle por ID) ---
    public function obtenerPorId($id_detalle) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM detalle_venta WHERE ID_Detalle = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_detalle]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar detalle de venta) ---
    public function actualizar($id_detalle, $id_venta, $id_producto, $cantidad, $precio_venta_historico) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE detalle_venta SET ID_Venta = :id_venta, ID_Producto = :id_producto, Cantidad = :cantidad, Precio_Venta_Historico = :precio_venta_historico WHERE ID_Detalle = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_detalle,
            'id_venta' => $id_venta,
            'id_producto' => $id_producto,
            'cantidad' => $cantidad,
            'precio_venta_historico' => $precio_venta_historico
        ]);
    }

    // --- DELETE (Eliminar detalle de venta) ---
    public function eliminar($id_detalle) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM detalle_venta WHERE ID_Detalle = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_detalle]);
    }
}