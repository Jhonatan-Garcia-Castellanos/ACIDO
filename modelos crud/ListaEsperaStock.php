<?php
require_once 'Conexion.php';

class ListaEsperaStock {

    // --- CREATE (Insertar en lista de espera de stock) ---
    public function crear($nombre_completo, $email, $id_producto, $notificado = 0) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO lista_espera_stock (Nombre_Completo, Email, ID_Producto, Notificado) VALUES (:nombre_completo, :email, :id_producto, :notificado)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombre_completo' => $nombre_completo,
            'email' => $email,
            'id_producto' => $id_producto,
            'notificado' => $notificado
        ]);
    }

    // --- READ (Ver todos los registros de lista de espera) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM lista_espera_stock";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver por ID de Espera) ---
    public function obtenerPorId($id_espera) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM lista_espera_stock WHERE ID_Espera = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_espera]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar registro de lista de espera) ---
    public function actualizar($id_espera, $nombre_completo, $email, $id_producto, $notificado) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE lista_espera_stock SET Nombre_Completo = :nombre_completo, Email = :email, ID_Producto = :id_producto, Notificado = :notificado WHERE ID_Espera = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_espera,
            'nombre_completo' => $nombre_completo,
            'email' => $email,
            'id_producto' => $id_producto,
            'notificado' => $notificado
        ]);
    }

    // --- DELETE (Eliminar de lista de espera) ---
    public function eliminar($id_espera) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM lista_espera_stock WHERE ID_Espera = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_espera]);
    }
}