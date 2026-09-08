<?php
require_once 'Conexion.php';

class ListaDeseos {

    // --- CREATE (Insertar en lista de deseos) ---
    public function crear($id_cliente, $id_producto) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO lista_deseos (ID_Cliente, ID_Producto) VALUES (:id_cliente, :id_producto)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_cliente' => $id_cliente,
            'id_producto' => $id_producto
        ]);
    }

    // --- READ (Ver todos los registros de lista de deseos) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM lista_deseos";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver por ID de Wishlist) ---
    public function obtenerPorId($id_wishlist) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM lista_deseos WHERE ID_Wishlist = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_wishlist]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar lista de deseos) ---
    public function actualizar($id_wishlist, $id_cliente, $id_producto) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE lista_deseos SET ID_Cliente = :id_cliente, ID_Producto = :id_producto WHERE ID_Wishlist = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_wishlist,
            'id_cliente' => $id_cliente,
            'id_producto' => $id_producto
        ]);
    }

    // --- DELETE (Eliminar de lista de deseos) ---
    public function eliminar($id_wishlist) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM lista_deseos WHERE ID_Wishlist = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_wishlist]);
    }
}