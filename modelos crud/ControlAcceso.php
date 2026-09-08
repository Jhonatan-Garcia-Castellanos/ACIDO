<?php
require_once 'Conexion.php';

class ControlAcceso {

    // --- CREATE (Insertar control de acceso) ---
    public function crear($email, $intentos_fallidos = 0, $bloqueado_hasta = null) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO control_accesos (Email, Intentos_Fallidos, Bloqueado_Hasta) VALUES (:email, :intentos_fallidos, :bloqueado_hasta)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'email' => $email,
            'intentos_fallidos' => $intentos_fallidos,
            'bloqueado_hasta' => $bloqueado_hasta
        ]);
    }

    // --- READ (Ver todos los registros de accesos) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM control_accesos";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver por Email) ---
    public function obtenerPorId($email) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM control_accesos WHERE Email = :email";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar intentos o bloqueo) ---
    public function actualizar($email, $intentos_fallidos, $bloqueado_hasta) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE control_accesos SET Intentos_Fallidos = :intentos_fallidos, Bloqueado_Hasta = :bloqueado_hasta WHERE Email = :email";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'email' => $email,
            'intentos_fallidos' => $intentos_fallidos,
            'bloqueado_hasta' => $bloqueado_hasta
        ]);
    }

    // --- DELETE (Eliminar registro) ---
    public function eliminar($email) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM control_accesos WHERE Email = :email";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['email' => $email]);
    }
}