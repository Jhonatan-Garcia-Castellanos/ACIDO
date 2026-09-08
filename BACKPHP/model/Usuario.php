<?php
require_once __DIR__ . "/../config/conexion.php";

class Usuario
{
    private $db;

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
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
        // Validar si el correo ya existe antes de insertar
        $checkQuery = "SELECT id FROM usuario WHERE email = :email";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(":email", $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return false; // Retorna false si el correo ya existe
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Extraer nombre automáticamente del correo
        $partes = explode('@', $email);
        $nombreAutomatico = ucfirst($partes[0]);

        $query = "INSERT INTO usuario (email, nombre, password) VALUES (:email, :nombre, :password)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":nombre", $nombreAutomatico);
        $stmt->bindParam(":password", $hash);
        return $stmt->execute();
    }

    // =========================================================================
    // MÉTODO AGREGADO: CAMBIO DE CONTRASEÑA
    // =========================================================================
    public function cambiarPassword($email, $actualPassword, $nuevaPassword)
    {
        // 1. Consultar usuario existente
        $query = "SELECT * FROM usuario WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ["status" => false, "message" => "El usuario no se encuentra registrado."];
        }

        // 2. Verificar contraseña actual
        if (!password_verify($actualPassword, $user["password"])) {
            return ["status" => false, "message" => "La contraseña actual es incorrecta."];
        }

        // 3. Encriptar y actualizar la nueva contraseña
        $newHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        $updateQuery = "UPDATE usuario SET password = :password WHERE email = :email";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->bindParam(":password", $newHash);
        $updateStmt->bindParam(":email", $email);

        if ($updateStmt->execute()) {
            return ["status" => true, "message" => "Contraseña actualizada exitosamente."];
        }

        return ["status" => false, "message" => "Error al actualizar la base de datos."];
    }
}
?>