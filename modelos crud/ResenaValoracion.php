<?php
require_once 'Conexion.php';

class ResenaValoracion {

    // --- CREATE (Insertar reseña o valoración) ---
    public function crear($id_cliente, $id_producto, $calificacion, $comentario = null, $estado_moderacion = 'Pendiente') {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO resena_valoracion (ID_Cliente, ID_Producto, Calificacion, Comentario, Estado_Moderacion) 
                VALUES (:id_cliente, :id_producto, :calificacion, :comentario, :estado_moderacion)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_cliente' => $id_cliente,
            'id_producto' => $id_producto,
            'calificacion' => $calificacion,
            'comentario' => $comentario,
            'estado_moderacion' => $estado_moderacion
        ]);
    }

    // --- READ (Ver todas las reseñas) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM resena_valoracion";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver reseña por ID) ---
    public function obtenerPorId($id_resena) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM resena_valoracion WHERE ID_Resena = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_resena]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar reseña) ---
    public function actualizar($id_resena, $id_cliente, $id_producto, $calificacion, $comentario, $estado_moderacion) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE resena_valoracion 
                SET ID_Cliente = :id_cliente, ID_Producto = :id_producto, Calificacion = :calificacion, 
                    Comentario = :comentario, Estado_Moderacion = :estado_moderacion 
                WHERE ID_Resena = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_resena,
            'id_cliente' => $id_cliente,
            'id_producto' => $id_producto,
            'calificacion' => $calificacion,
            'comentario' => $comentario,
            'estado_moderacion' => $estado_moderacion
        ]);
    }

    // --- DELETE (Eliminar reseña) ---
    public function eliminar($id_resena) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM resena_valoracion WHERE ID_Resena = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_resena]);
    }
}