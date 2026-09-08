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
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDo::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error en la conexion: " . $e->getMessage());
        }

    }
}
?>
