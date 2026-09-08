<?php
require_once 'Conexion.php';

class Venta {

    // --- CREATE (Insertar venta) ---
    public function crear($id_cliente, $id_empleado = null) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO venta (ID_Cliente, ID_Empleado) VALUES (:id_cliente, :id_empleado)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_cliente' => $id_cliente,
            'id_empleado' => $id_empleado
        ]);
    }

    // --- READ (Ver todas las ventas) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM venta";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver venta por ID) ---
    public function obtenerPorId($id_venta) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM venta WHERE ID_Venta = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_venta]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar venta) ---
    public function actualizar($id_venta, $id_cliente, $id_empleado) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE venta SET ID_Cliente = :id_cliente, ID_Empleado = :id_empleado WHERE ID_Venta = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_venta,
            'id_cliente' => $id_cliente,
            'id_empleado' => $id_empleado
        ]);
    }

    // --- DELETE (Eliminar venta) ---
    public function eliminar($id_venta) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM venta WHERE ID_Venta = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_venta]);
    }
}