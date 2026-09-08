<?php
require_once 'Conexion.php';

class Pago {

    // --- CREATE (Insertar pago) ---
    public function crear($id_venta, $id_metodo, $monto_pagado) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO pago (ID_Venta, ID_Metodo, Monto_Pagado) VALUES (:id_venta, :id_metodo, :monto_pagado)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_venta' => $id_venta,
            'id_metodo' => $id_metodo,
            'monto_pagado' => $monto_pagado
        ]);
    }

    // --- READ (Ver todos los pagos) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM pago";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver pago por ID) ---
    public function obtenerPorId($id_pago) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM pago WHERE ID_Pago = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_pago]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar pago) ---
    public function actualizar($id_pago, $id_venta, $id_metodo, $monto_pagado) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE pago SET ID_Venta = :id_venta, ID_Metodo = :id_metodo, Monto_Pagado = :monto_pagado WHERE ID_Pago = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_pago,
            'id_venta' => $id_venta,
            'id_metodo' => $id_metodo,
            'monto_pagado' => $monto_pagado
        ]);
    }

    // --- DELETE (Eliminar pago) ---
    public function eliminar($id_pago) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM pago WHERE ID_Pago = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_pago]);
    }
}