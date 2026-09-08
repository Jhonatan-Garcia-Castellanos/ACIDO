<?php
require_once 'Conexion.php';

class Proveedor {

    // --- CREATE (Insertar proveedor) ---
    public function crear($nombre_empresa, $id_ciudad) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO proveedor (Nombre_Empresa, ID_Ciudad) VALUES (:nombre_empresa, :id_ciudad)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombre_empresa' => $nombre_empresa,
            'id_ciudad' => $id_ciudad
        ]);
    }

    // --- READ (Ver todos los proveedores) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM proveedor";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver proveedor por ID) ---
    public function obtenerPorId($id_proveedor) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM proveedor WHERE ID_Proveedor = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_proveedor]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar proveedor) ---
    public function actualizar($id_proveedor, $nombre_empresa, $id_ciudad) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE proveedor SET Nombre_Empresa = :nombre_empresa, ID_Ciudad = :id_ciudad WHERE ID_Proveedor = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_proveedor,
            'nombre_empresa' => $nombre_empresa,
            'id_ciudad' => $id_ciudad
        ]);
    }

    // --- DELETE (Eliminar proveedor) ---
    public function eliminar($id_proveedor) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM proveedor WHERE ID_Proveedor = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_proveedor]);
    }
}