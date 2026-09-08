<?php
require_once 'Conexion.php';

class MetodoPago {

    // --- CREATE (Insertar método de pago) ---
    public function crear($tipo_metodo) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO metodo_pago (Tipo_Metodo) VALUES (:tipo_metodo)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'tipo_metodo' => $tipo_metodo
        ]);
    }

    // --- READ (Ver todos los métodos de pago) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM metodo_pago";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver método de pago por ID) ---
    public function obtenerPorId($id_metodo) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM metodo_pago WHERE ID_Metodo = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_metodo]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar método de pago) ---
    public function actualizar($id_metodo, $tipo_metodo) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE metodo_pago SET Tipo_Metodo = :tipo_metodo WHERE ID_Metodo = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_metodo,
            'tipo_metodo' => $tipo_metodo
        ]);
    }

    // --- DELETE (Eliminar método de pago) ---
    public function eliminar($id_metodo) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM metodo_pago WHERE ID_Metodo = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_metodo]);
    }
}