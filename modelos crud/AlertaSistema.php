<?php
require_once 'Conexion.php';

class AlertaSistema {

    // --- CREATE (Insertar) ---
    public function crear($tipo, $mensaje, $leido = 0) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO alertas_sistema (Tipo, Mensaje, Leido) VALUES (:tipo, :mensaje, :leido)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'leido' => $leido
        ]);
    }

    // --- READ (Obtener todas) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM alertas_sistema";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Obtener por ID) ---
    public function obtenerPorId($id_alerta) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM alertas_sistema WHERE ID_Alerta = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_alerta]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar) ---
    public function actualizar($id_alerta, $tipo, $mensaje, $leido) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE alertas_sistema SET Tipo = :tipo, Mensaje = :mensaje, Leido = :leido WHERE ID_Alerta = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_alerta,
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'leido' => $leido
        ]);
    }

    // --- DELETE (Eliminar) ---
    public function eliminar($id_alerta) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM alertas_sistema WHERE ID_Alerta = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_alerta]);
    }
}