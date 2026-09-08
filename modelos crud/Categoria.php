<?php
require_once 'Conexion.php';

class Categoria {

    // --- CREATE (Insertar categoría) ---
    public function crear($nombre_categoria) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO categoria (Nombre_Categoria) VALUES (:nombre_categoria)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombre_categoria' => $nombre_categoria
        ]);
    }

    // --- READ (Ver todas las categorías) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM categoria";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver categoría por ID) ---
    public function obtenerPorId($id_categoria) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM categoria WHERE ID_Categoria = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_categoria]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar categoría) ---
    public function actualizar($id_categoria, $nombre_categoria) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE categoria SET Nombre_Categoria = :nombre_categoria WHERE ID_Categoria = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_categoria,
            'nombre_categoria' => $nombre_categoria
        ]);
    }

    // --- DELETE (Eliminar categoría) ---
    public function eliminar($id_categoria) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM categoria WHERE ID_Categoria = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_categoria]);
    }
}
