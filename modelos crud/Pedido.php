<?php
require_once 'Conexion.php';

class Pedido {

    // --- CREATE (Insertar pedido) ---
    public function crear($id_venta, $direccion_envio, $ciudad_envio, $tipo_envio, $estado_pedido = 'Preparando', $guia_seguimiento = null) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO pedido (ID_Venta, Direccion_Envio, Ciudad_Envio, Tipo_Envio, Estado_Pedido, Guia_Seguimiento) 
                VALUES (:id_venta, :direccion_envio, :ciudad_envio, :tipo_envio, :estado_pedido, :guia_seguimiento)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_venta' => $id_venta,
            'direccion_envio' => $direccion_envio,
            'ciudad_envio' => $ciudad_envio,
            'tipo_envio' => $tipo_envio,
            'estado_pedido' => $estado_pedido,
            'guia_seguimiento' => $guia_seguimiento
        ]);
    }

    // --- READ (Ver todos los pedidos) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM pedido";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver pedido por ID) ---
    public function obtenerPorId($id_pedido) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM pedido WHERE ID_Pedido = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_pedido]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar pedido) ---
    public function actualizar($id_pedido, $id_venta, $direccion_envio, $ciudad_envio, $tipo_envio, $estado_pedido, $guia_seguimiento) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE pedido 
                SET ID_Venta = :id_venta, Direccion_Envio = :direccion_envio, Ciudad_Envio = :ciudad_envio, 
                    Tipo_Envio = :tipo_envio, Estado_Pedido = :estado_pedido, Guia_Seguimiento = :guia_seguimiento 
                WHERE ID_Pedido = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_pedido,
            'id_venta' => $id_venta,
            'direccion_envio' => $direccion_envio,
            'ciudad_envio' => $ciudad_envio,
            'tipo_envio' => $tipo_envio,
            'estado_pedido' => $estado_pedido,
            'guia_seguimiento' => $guia_seguimiento
        ]);
    }

    // --- DELETE (Eliminar pedido) ---
    public function eliminar($id_pedido) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM pedido WHERE ID_Pedido = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_pedido]);
    }
}