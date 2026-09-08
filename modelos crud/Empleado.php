<?php
require_once 'Conexion.php';

class Empleado {

    // --- CREATE (Insertar empleado) ---
    public function crear($nombres, $apellidos, $id_cargo, $id_ciudad) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO empleado (Nombres, Apellidos, ID_Cargo, ID_Ciudad) VALUES (:nombres, :apellidos, :id_cargo, :id_ciudad)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'id_cargo' => $id_cargo,
            'id_ciudad' => $id_ciudad
        ]);
    }

    // --- READ (Ver todos los empleados) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM empleado";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver empleado por ID) ---
    public function obtenerPorId($id_empleado) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM empleado WHERE ID_Empleado = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_empleado]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar empleado) ---
    public function actualizar($id_empleado, $nombres, $apellidos, $id_cargo, $id_ciudad) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE empleado SET Nombres = :nombres, Apellidos = :apellidos, ID_Cargo = :id_cargo, ID_Ciudad = :id_ciudad WHERE ID_Empleado = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_empleado,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'id_cargo' => $id_cargo,
            'id_ciudad' => $id_ciudad
        ]);
    }

    // --- DELETE (Eliminar empleado) ---
    public function eliminar($id_empleado) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM empleado WHERE ID_Empleado = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_empleado]);
    }
}