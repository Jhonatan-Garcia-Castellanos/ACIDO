<?php
require_once 'Conexion.php';

class Usuario {

    // --- CREATE (Insertar usuario) ---
    public function crear($email, $password_hash, $rol = 'Cliente', $id_empleado = null, $id_cliente = null) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO usuario (Email, Password_Hash, Rol, ID_Empleado, ID_Cliente) 
                VALUES (:email, :password_hash, :rol, :id_empleado, :id_cliente)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'email' => $email,
            'password_hash' => $password_hash,
            'rol' => $rol,
            'id_empleado' => $id_empleado,
            'id_cliente' => $id_cliente
        ]);
    }

    // --- READ (Ver todos los usuarios) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM usuario";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver usuario por ID) ---
    public function obtenerPorId($id_usuario) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM usuario WHERE ID_Usuario = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_usuario]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar usuario) ---
    public function actualizar($id_usuario, $email, $password_hash, $rol, $id_empleado, $id_cliente) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE usuario 
                SET Email = :email, Password_Hash = :password_hash, Rol = :rol, 
                    ID_Empleado = :id_empleado, ID_Cliente = :id_cliente 
                WHERE ID_Usuario = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_usuario,
            'email' => $email,
            'password_hash' => $password_hash,
            'rol' => $rol,
            'id_empleado' => $id_empleado,
            'id_cliente' => $id_cliente
        ]);
    }

    // --- DELETE (Eliminar usuario) ---
    public function eliminar($id_usuario) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM usuario WHERE ID_Usuario = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_usuario]);
    }
}