<?php
require_once 'Conexion.php';

class LibretaDirecciones {

    // --- CREATE (Insertar dirección) ---
    public function crear($id_cliente, $alias, $ciudad_id, $codigo_postal, $direccion_exacta, $referencias, $es_principal) {
        $conexion = Conexion::conectar();
        $sql = "INSERT INTO libreta_direcciones (ID_Cliente, Alias, Ciudad_Id, Codigo_Postal, Direccion_Exacta, Referencias, Es_Principal) 
                VALUES (:id_cliente, :alias, :ciudad_id, :codigo_postal, :direccion_exacta, :referencias, :es_principal)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id_cliente' => $id_cliente,
            'alias' => $alias,
            'ciudad_id' => $ciudad_id,
            'codigo_postal' => $codigo_postal,
            'direccion_exacta' => $direccion_exacta,
            'referencias' => $referencias,
            'es_principal' => $es_principal
        ]);
    }

    // --- READ (Ver todas las direcciones) ---
    public function obtenerTodas() {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM libreta_direcciones";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Ver dirección por ID) ---
    public function obtenerPorId($id_direccion) {
        $conexion = Conexion::conectar();
        $sql = "SELECT * FROM libreta_direcciones WHERE ID_Direccion = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_direccion]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar dirección) ---
    public function actualizar($id_direccion, $id_cliente, $alias, $ciudad_id, $codigo_postal, $direccion_exacta, $referencias, $es_principal) {
        $conexion = Conexion::conectar();
        $sql = "UPDATE libreta_direcciones 
                SET ID_Cliente = :id_cliente, Alias = :alias, Ciudad_Id = :ciudad_id, Codigo_Postal = :codigo_postal, 
                    Direccion_Exacta = :direccion_exacta, Referencias = :referencias, Es_Principal = :es_principal 
                WHERE ID_Direccion = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([
            'id' => $id_direccion,
            'id_cliente' => $id_cliente,
            'alias' => $alias,
            'ciudad_id' => $ciudad_id,
            'codigo_postal' => $codigo_postal,
            'direccion_exacta' => $direccion_exacta,
            'referencias' => $referencias,
            'es_principal' => $es_principal
        ]);
    }

    // --- DELETE (Eliminar dirección) ---
    public function eliminar($id_direccion) {
        $conexion = Conexion::conectar();
        $sql = "DELETE FROM libreta_direcciones WHERE ID_Direccion = :id";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute(['id' => $id_direccion]);
    }
}