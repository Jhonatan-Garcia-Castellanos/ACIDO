<?php
// Incluir la ruta correcta a la configuración
require_once __DIR__ . '/../config/conexion.php';

class AlertaSistema {

    private $db;

    // Inicializar la conexión PDO en el constructor
    public function __construct() {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
    }

    // --- CREATE (Insertar) ---
    public function crear($tipo, $mensaje, $leido = 0) {
        $sql = "INSERT INTO alertas_sistema (Tipo, Mensaje, Leido) VALUES (:tipo, :mensaje, :leido)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'leido' => $leido
        ]);
    }

    // --- READ (Obtener todas) ---
    public function obtenerTodas() {
        $sql = "SELECT * FROM alertas_sistema";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // --- READ (Obtener por ID) ---
    public function obtenerPorId($id_alerta) {
        $sql = "SELECT * FROM alertas_sistema WHERE ID_Alerta = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id_alerta]);
        return $stmt->fetch();
    }

    // --- UPDATE (Actualizar) ---
    public function actualizar($id_alerta, $tipo, $mensaje, $leido) {
        $sql = "UPDATE alertas_sistema SET Tipo = :tipo, Mensaje = :mensaje, Leido = :leido WHERE ID_Alerta = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id_alerta,
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'leido' => $leido
        ]);
    }

    // --- DELETE (Eliminar) ---
    public function eliminar($id_alerta) {
        $sql = "DELETE FROM alertas_sistema WHERE ID_Alerta = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id_alerta]);
    }
}
?>