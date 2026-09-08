<?php
require_once 'Conexion.php';

class Factura {

    // --- CREATE (Insertar factura) ---
    public function crear($numero_factura, $id_venta) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO factura (Numero_Factura, ID_Venta) VALUES (:numero_factura, :id_venta)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'numero_factura' => $numero_factura,
            'id_venta' => $id_venta
        ]);
    }

    // --- READ (Ver todas las facturas) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM factura";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver factura por ID) ---
    public function obtenerPorId($id_factura) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM factura WHERE ID_Factura = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_factura]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar factura) ---
    public function actualizar($id_factura, $numero_factura, $id_venta) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE factura SET Numero_Factura = :numero_factura, ID_Venta = :id_venta WHERE ID_Factura = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_factura,
            'numero_factura' => $numero_factura,
            'id_venta' => $id_venta
        ]);
    }

    // --- DELETE (Eliminar factura) ---
    public function eliminar($id_factura) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM factura WHERE ID_Factura = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_factura]);
    }
}