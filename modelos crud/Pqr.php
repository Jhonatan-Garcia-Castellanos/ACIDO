<?php
require_once 'Conexion.php';

class Pqr {

    // --- CREATE (Insertar PQR) ---
    public function crear($descripcion, $id_cliente, $estado = 'Abierto', $id_empleado = null) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO pqr (Descripcion, Estado, ID_Cliente, ID_Empleado) 
                VALUES (:descripcion, :estado, :id_cliente, :id_empleado)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'descripcion' => $descripcion,
            'estado' => $estado,
            'id_cliente' => $id_cliente,
            'id_empleado' => $id_empleado
        ]);
    }

    // --- READ (Ver todos los PQR) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM pqr";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver PQR por ID) ---
    public function obtenerPorId($id_pqr) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM pqr WHERE ID_Pqr = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_pqr]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar PQR) ---
    public function actualizar($id_pqr, $descripcion, $estado, $id_cliente, $id_empleado) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE pqr 
                SET Descripcion = :descripcion, Estado = :estado, ID_Cliente = :id_cliente, ID_Empleado = :id_empleado 
                WHERE ID_Pqr = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_pqr,
            'descripcion' => $descripcion,
            'estado' => $estado,
            'id_cliente' => $id_cliente,
            'id_empleado' => $id_empleado
        ]);
    }

    // --- DELETE (Eliminar PQR) ---
    public function eliminar($id_pqr) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM pqr WHERE ID_Pqr = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_pqr]);
    }
}