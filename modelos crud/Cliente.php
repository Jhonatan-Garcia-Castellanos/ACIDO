<?php
require_once 'Conexion.php';

class Cliente {

    // --- CREATE (Insertar cliente) ---
    public function crear($nombres, $apellidos, $telefono) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO cliente (Nombres, Apellidos, Telefono) VALUES (:nombres, :apellidos, :telefono)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono
        ]);
    }

    // --- READ (Ver todos los clientes) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM cliente";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver cliente por ID) ---
    public function obtenerPorId($id_cliente) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM cliente WHERE ID_Cliente = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_cliente]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar cliente) ---
    public function actualizar($id_cliente, $nombres, $apellidos, $telefono) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE cliente SET Nombres = :nombres, Apellidos = :apellidos, Telefono = :telefono WHERE ID_Cliente = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_cliente,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono
        ]);
    }

    // --- DELETE (Eliminar cliente) ---
    public function eliminar($id_cliente) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM cliente WHERE ID_Cliente = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_cliente]);
    }
}