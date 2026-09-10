<?php
require_once __DIR__ . "/../config/conexion.php";

class Usuario
{
    private $db;
    private const ROLES_VALIDOS = ['Cliente', 'Empleado', 'Administrador'];

    public function __construct()
    {
        $conexionObj = new Conexion();
        $this->db = $conexionObj->conn;
    }

    private function normalizarEmail($email)
    {
        return strtolower(trim($email ?? ''));
    }

    private function rolValido($rol)
    {
        return in_array($rol, self::ROLES_VALIDOS, true) ? $rol : 'Cliente';
    }

    private function nombreDesdeEmail($email)
    {
        $email = $this->normalizarEmail($email);
        if (strpos($email, '@') === false) {
            return 'Usuario';
        }
        $partes = explode('@', $email);
        $base = preg_replace('/[^a-z0-9._-]/i', '', $partes[0]);
        if ($base === '') {
            return 'Usuario';
        }
        return ucfirst($base);
    }

    /**
     * Enriquece la fila DB.sql (ID_Usuario, Email, Password_Hash, Rol)
     * con alias en minusculas + nombre derivado para compatibilidad con vistas.
     */
    private function mapearFila($row)
    {
        if (!$row) {
            return false;
        }
        $email = $row['Email'] ?? $row['email'] ?? '';
        $row['email'] = $email;
        $row['Email'] = $email;
        if (isset($row['ID_Usuario'])) {
            $row['id'] = $row['ID_Usuario'];
        } elseif (isset($row['id'])) {
            $row['ID_Usuario'] = $row['id'];
        }
        if (isset($row['Rol'])) {
            $row['rol'] = $row['Rol'];
        } elseif (isset($row['rol'])) {
            $row['Rol'] = $row['rol'];
        }
        if (!isset($row['nombre'])) {
            $row['nombre'] = $this->nombreDesdeEmail($email);
        }
        return $row;
    }

    public function login($email, $password)
    {
        $email = $this->normalizarEmail($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            return false;
        }
        try {
            $query = "SELECT ID_Usuario, Email, Password_Hash, Rol, Activo FROM usuario WHERE Email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user["Password_Hash"])) {
                if (isset($user["Activo"]) && (int)$user["Activo"] === 0) {
                    return false; // Inactivo por terminos legales: no puede ingresar
                }
                unset($user["Password_Hash"]);
                return $this->mapearFila($user);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Usuario::login: " . $e->getMessage());
            return false;
        }
    }

    public function registrar($email, $password, $rol = 'Cliente')
    {
        $email = $this->normalizarEmail($email);
        $rol = $this->rolValido($rol);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            return false;
        }
        try {
            $checkStmt = $this->db->prepare("SELECT 1 FROM usuario WHERE Email = :email LIMIT 1");
            $checkStmt->execute([':email' => $email]);
            if ($checkStmt->fetch()) {
                return false;
            }
            // Limpia huerfano en control_accesos (el trigger trg_registrar_acceso hace INSERT y fallaria si quedo Email huerfano tras un DELETE)
            try {
                $clean = $this->db->prepare("DELETE FROM control_accesos WHERE Email = :email");
                $clean->execute([':email' => $email]);
            } catch (PDOException $e) {
                // Si la tabla no existe en DB temporal, ignorar
            }
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->db->prepare("INSERT INTO usuario (Email, Password_Hash, Rol, Activo) VALUES (:email, :hash, :rol, 1)");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":hash", $hash);
            $stmt->bindParam(":rol", $rol);
            return $stmt->execute();
        } catch (PDOException $e) {
            // 23000 duplicado (UNIQUE + trg_email_unico_global SIGNAL 45000) o email invalido del trigger
            error_log("Usuario::registrar: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // CRUD POR ID (MVC: la vista no toca la BD) - Esquema DB.sql
    // =========================================================================
    public function obtenerTodos()
    {
        try {
            // No traer Password_Hash por seguridad. Alias para compatibilidad con vistas viejas.
            $stmt = $this->db->query("SELECT ID_Usuario, Email, Rol, Activo, ID_Usuario AS id, Email AS email, Rol AS rol FROM usuario ORDER BY ID_Usuario DESC LIMIT 200");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r = $this->mapearFila($r);
            }
            return $rows;
        } catch (PDOException $e) {
            error_log("Usuario::obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId($id)
    {
        if (!ctype_digit((string)$id)) {
            return false;
        }
        try {
            $stmt = $this->db->prepare("SELECT ID_Usuario, Email, Rol, Activo FROM usuario WHERE ID_Usuario = :id LIMIT 1");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $this->mapearFila($row) : false;
        } catch (PDOException $e) {
            error_log("Usuario::obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarPorId($id, $email, $password = null, $rol = null)
    {
        if (!ctype_digit((string)$id)) {
            return false;
        }
        $email = $this->normalizarEmail($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        try {
            // Evita violar UNIQUE Email de otro id
            $chk = $this->db->prepare("SELECT ID_Usuario FROM usuario WHERE Email = :email LIMIT 1");
            $chk->execute([':email' => $email]);
            $existe = $chk->fetch(PDO::FETCH_ASSOC);
            if ($existe && (string)$existe['ID_Usuario'] !== (string)$id) {
                return false;
            }
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                if ($rol !== null) {
                    $rol = $this->rolValido($rol);
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email, Password_Hash = :hash, Rol = :rol WHERE ID_Usuario = :id");
                    $stmt->bindParam(":rol", $rol);
                } else {
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email, Password_Hash = :hash WHERE ID_Usuario = :id");
                }
                $stmt->bindParam(":hash", $hash);
            } else {
                if ($rol !== null) {
                    $rol = $this->rolValido($rol);
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email, Rol = :rol WHERE ID_Usuario = :id");
                    $stmt->bindParam(":rol", $rol);
                } else {
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email WHERE ID_Usuario = :id");
                }
            }
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $ok = $stmt->execute();
            if ($ok) {
                // Mantiene control_accesos sincronizado si cambio el email
                try {
                    $old = $this->db->prepare("SELECT Email FROM control_accesos WHERE Email = :e LIMIT 1");
                    $old->execute([':e' => $email]);
                    if (!$old->fetch()) {
                        $ins = $this->db->prepare("INSERT IGNORE INTO control_accesos (Email) VALUES (:e)");
                        $ins->execute([':e' => $email]);
                    }
                } catch (PDOException $e) {
                }
            }
            return $ok;
        } catch (PDOException $e) {
            error_log("Usuario::actualizarPorId: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado($id, $activo)
    {
        if (!ctype_digit((string)$id)) {
            return false;
        }
        $activo = ((int)$activo === 1) ? 1 : 0;
        try {
            $stmt = $this->db->prepare("UPDATE usuario SET Activo = :a WHERE ID_Usuario = :id");
            $stmt->bindParam(":a", $activo, PDO::PARAM_INT);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Usuario::cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarPorId($id)
    {
        // Por terminos legales NO hay borrado fisico: se inactiva.
        return $this->cambiarEstado($id, 0);
    }

    public function activarPorId($id)
    {
        return $this->cambiarEstado($id, 1);
    }

    // =========================================================================
    // MÉTODO: CAMBIO DE CONTRASEÑA (DB.sql: Password_Hash)
    // =========================================================================
    public function cambiarPassword($email, $actualPassword, $nuevaPassword)
    {
        $email = $this->normalizarEmail($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["status" => false, "message" => "Correo invalido."];
        }
        try {
            $query = "SELECT ID_Usuario, Email, Password_Hash FROM usuario WHERE Email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return ["status" => false, "message" => "El usuario no se encuentra registrado."];
            }
            if (!password_verify($actualPassword, $user["Password_Hash"])) {
                return ["status" => false, "message" => "La contrasena actual es incorrecta."];
            }
            $newHash = password_hash($nuevaPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $updateStmt = $this->db->prepare("UPDATE usuario SET Password_Hash = :hash WHERE Email = :email");
            $updateStmt->bindParam(":hash", $newHash);
            $updateStmt->bindParam(":email", $email);
            if ($updateStmt->execute()) {
                return ["status" => true, "message" => "Contrasena actualizada exitosamente."];
            }
            return ["status" => false, "message" => "Error al actualizar la base de datos."];
        } catch (PDOException $e) {
            error_log("Usuario::cambiarPassword: " . $e->getMessage());
            return ["status" => false, "message" => "Error interno. Intenta mas tarde."];
        }
    }
}
