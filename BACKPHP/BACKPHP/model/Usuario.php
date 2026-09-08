<?php
require_once "config/conexion.php";

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->conn;
    }

    public function login($email, $password)
    {
        $query = "SELECT * FROM usuario WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario existe y la contraseña encriptada coincide
        if ($user && password_verify($password, $user["password"])) {
            return $user;
        }
        
        return false;
    }

    public function registrar($email, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Extraer el nombre automáticamente del correo (ej. "jhonatan" de "jhonatan@gmail.com")
        $partes = explode('@', $email);
        $nombreAutomatico = ucfirst($partes[0]);

        $query = "INSERT INTO usuario (email, nombre, password) VALUES (:email, :nombre, :password)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":nombre", $nombreAutomatico);
        $stmt->bindParam(":password", $hash);
        return $stmt->execute();
    }
}
?>