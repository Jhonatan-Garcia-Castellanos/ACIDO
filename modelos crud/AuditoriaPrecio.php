<?php
require_once 'Conexion.php';

class AuditoriaPrecio {

    // --- CREATE (Insertar registro de auditoría) ---
    public function crear($id_producto, $precio_anterior, $precio_nuevo, $usuario_responsable) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO auditoria_precios (ID_Producto, Precio_Anterior, Precio_Nuevo, Usuario_Responsable) VALUES (:id_producto, :precio_anterior, :precio_nuevo, :usuario_responsable)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_producto' => $id_producto,
            'precio_anterior' => $precio_anterior,
            'precio_nuevo' => $precio_nuevo,
            'usuario_responsable' => $usuario_responsable
        ]);
    }

    // --- READ (Ver todos los registros) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM auditoria_precios";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver por ID de auditoría) ---
    public function obtenerPorId($id_auditoria) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM auditoria_precios WHERE ID_Auditoria = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_auditoria]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar registro) ---
    public function actualizar($id_auditoria, $id_producto, $precio_anterior, $precio_nuevo, $usuario_responsable) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE auditoria_precios SET ID_Producto = :id_producto, Precio_Anterior = :precio_anterior, Precio_Nuevo = :precio_nuevo, Usuario_Responsable = :usuario_responsable WHERE ID_Auditoria = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_auditoria,
            'id_producto' => $id_producto,
            'precio_anterior' => $precio_anterior,
            'precio_nuevo' => $precio_nuevo,
            'usuario_responsable' => $usuario_responsable
        ]);
    }

    // --- DELETE (Eliminar registro) ---
    public function eliminar($id_auditoria) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM auditoria_precios WHERE ID_Auditoria = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_auditoria]);
    }
}