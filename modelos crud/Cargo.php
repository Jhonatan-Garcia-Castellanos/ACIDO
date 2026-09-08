<?php
require_once 'Conexion.php';

class Cargo {

    // --- CREATE (Insertar cargo) ---
    public function crear($nombre_cargo, $salario_base) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO cargo (Nombre_Cargo, Salario_Base) VALUES (:nombre_cargo, :salario_base)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombre_cargo' => $nombre_cargo,
            'salario_base' => $salario_base
        ]);
    }

    // --- READ (Ver todos los cargos) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM cargo";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver cargo por ID) ---
    public function obtenerPorId($id_cargo) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM cargo WHERE ID_Cargo = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_cargo]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar cargo) ---
    public function actualizar($id_cargo, $nombre_cargo, $salario_base) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE cargo SET Nombre_Cargo = :nombre_cargo, Salario_Base = :salario_base WHERE ID_Cargo = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_cargo,
            'nombre_cargo' => $nombre_cargo,
            'salario_base' => $salario_base
        ]);
    }

    // --- DELETE (Eliminar cargo) ---
    public function eliminar($id_cargo) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM cargo WHERE ID_Cargo = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_cargo]);
    }
}