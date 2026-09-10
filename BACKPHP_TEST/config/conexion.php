<?php
// config/conexion.php
class Conexion {
    private $host = "localhost";
    private $dbname = "plojecto";
    private $user = "root";
    private $password = "";
    public $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4", $this->user, $this->password);
            // Corrección: PDO con mayúsculas sostenidas
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            error_log("Conexion: " . $e->getMessage());
            die("Error en la conexión. Intenta más tarde.");
        }
    }
}