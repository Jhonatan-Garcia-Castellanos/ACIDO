<?php
require_once 'Conexion.php';

class MovimientoInventario {

    // --- CREATE (Insertar movimiento de inventario) ---
    public function crear($id_producto, $tipo_movimiento, $cantidad, $motivo, $id_empleado) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO movimiento_inventario (ID_Producto, Tipo_Movimiento, Cantidad, Motivo, ID_Empleado) 
                VALUES (:id_producto, :tipo_movimiento, :cantidad, :motivo, :id_empleado)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_producto' => $id_producto,
            'tipo_movimiento' => $tipo_movimiento,
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'id_empleado' => $id_empleado
        ]);
    }

    // --- READ (Ver todos los movimientos de inventario) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM movimiento_inventario";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver movimiento por ID) ---
    public function obtenerPorId($id_movimiento) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM movimiento_inventario WHERE ID_Movimiento = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_movimiento]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar movimiento de inventario) ---
    public function actualizar($id_movimiento, $id_producto, $tipo_movimiento, $cantidad, $motivo, $id_empleado) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE movimiento_inventario 
                SET ID_Producto = :id_producto, Tipo_Movimiento = :tipo_movimiento, Cantidad = :cantidad, 
                    Motivo = :motivo, ID_Empleado = :id_empleado 
                WHERE ID_Movimiento = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_movimiento,
            'id_producto' => $id_producto,
            'tipo_movimiento' => $tipo_movimiento,
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'id_empleado' => $id_empleado
        ]);
    }

    // --- DELETE (Eliminar movimiento de inventario) ---
    public function eliminar($id_movimiento) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM movimiento_inventario WHERE ID_Movimiento = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_movimiento]);
    }
}