<?php
require_once 'Conexion.php';

class Ciudad {

    // --- CREATE (Insertar ciudad) ---
    public function crear($nombre_ciudad, $id_departamento) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO ciudad (Nombre_Ciudad, ID_Departamento) VALUES (:nombre_ciudad, :id_departamento)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombre_ciudad' => $nombre_ciudad,
            'id_departamento' => $id_departamento
        ]);
    }

    // --- READ (Ver todas las ciudades) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM ciudad";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver ciudad por ID) ---
    public function obtenerPorId($id_ciudad) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM ciudad WHERE ID_Ciudad = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_ciudad]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar ciudad) ---
    public function actualizar($id_ciudad, $nombre_ciudad, $id_departamento) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE ciudad SET Nombre_Ciudad = :nombre_ciudad, ID_Departamento = :id_departamento WHERE ID_Ciudad = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_ciudad,
            'nombre_ciudad' => $nombre_ciudad,
            'id_departamento' => $id_departamento
        ]);
    }

    // --- DELETE (Eliminar ciudad) ---
    public function eliminar($id_ciudad) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM ciudad WHERE ID_Ciudad = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_ciudad]);
    }
}