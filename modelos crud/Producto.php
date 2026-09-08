<?php
require_once 'Conexion.php';

class Producto {

    // --- CREATE (Insertar producto) ---
    public function crear($nombre_producto, $precio_actual, $stock_actual, $id_categoria, $id_proveedor, $imagen_url = null, $qr_code_url = null) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO producto (Nombre_Producto, Precio_Actual, Stock_Actual, ID_Categoria, ID_Proveedor, Imagen_URL, QR_Code_URL) 
                VALUES (:nombre_producto, :precio_actual, :stock_actual, :id_categoria, :id_proveedor, :imagen_url, :qr_code_url)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'nombre_producto' => $nombre_producto,
            'precio_actual' => $precio_actual,
            'stock_actual' => $stock_actual,
            'id_categoria' => $id_categoria,
            'id_proveedor' => $id_proveedor,
            'imagen_url' => $imagen_url,
            'qr_code_url' => $qr_code_url
        ]);
    }

    // --- READ (Ver todos los productos) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM producto";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver producto por ID) ---
    public function obtenerPorId($id_producto) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM producto WHERE ID_Producto = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_producto]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar producto) ---
    public function actualizar($id_producto, $nombre_producto, $precio_actual, $stock_actual, $id_categoria, $id_proveedor, $imagen_url, $qr_code_url) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE producto 
                SET Nombre_Producto = :nombre_producto, Precio_Actual = :precio_actual, Stock_Actual = :stock_actual, 
                    ID_Categoria = :id_categoria, ID_Proveedor = :id_proveedor, Imagen_URL = :imagen_url, QR_Code_URL = :qr_code_url 
                WHERE ID_Producto = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_producto,
            'nombre_producto' => $nombre_producto,
            'precio_actual' => $precio_actual,
            'stock_actual' => $stock_actual,
            'id_categoria' => $id_categoria,
            'id_proveedor' => $id_proveedor,
            'imagen_url' => $imagen_url,
            'qr_code_url' => $qr_code_url
        ]);
    }

    // --- DELETE (Eliminar producto) ---
    public function eliminar($id_producto) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM producto WHERE ID_Producto = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_producto]);
    }
}