<?php
// config/conexion.php — Lee credenciales del .env (ver .env.example).
require_once __DIR__ . "/../lib/Env.php";
Env::load(dirname(__DIR__));

class Conexion {
    public $conn;

    public function __construct() {
        $host    = Env::get("DB_HOST", "localhost");
        $port    = (int)Env::get("DB_PORT", 3306);
        $dbname  = Env::get("DB_NAME", "proyecto_acido");
        $user    = Env::get("DB_USER", "root");
        $pass    = Env::get("DB_PASS", "");
        $charset = Env::get("DB_CHARSET", "utf8mb4");
        try {
            $this->conn = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}", $user, $pass);
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
