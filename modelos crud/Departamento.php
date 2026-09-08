<?php
require_once 'Conexion.php';

class Departamento {

    // --- CREATE (Insertar departamento) ---
    public function crear($nombre_departamento) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO departamento (Nombre_Departamento) VALUES (:nombre_departamento)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombre_departamento' => $nombre_departamento
        ]);
    }

    // --- READ (Ver todos los departamentos) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM departamento";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver departamento por ID) ---
    public function obtenerPorId($id_departamento) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM departamento WHERE ID_Departamento = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_departamento]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar departamento) ---
    public function actualizar($id_departamento, $nombre_departamento) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE departamento SET Nombre_Departamento = :nombre_departamento WHERE ID_Departamento = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_departamento,
            'nombre_departamento' => $nombre_departamento
        ]);
    }

    // --- DELETE (Eliminar departamento) ---
    public function eliminar($id_departamento) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM departamento WHERE ID_Departamento = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_departamento]);
    }
}