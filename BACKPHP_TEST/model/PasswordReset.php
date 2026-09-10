<?php
require_once __DIR__ . "/../config/conexion.php";

class PasswordReset
{
    private $db;

    public function __construct()
    {
        $this->db = (new Conexion())->conn;
    }

    public function usuarioPorEmail($email)
    {
        $email = strtolower(trim($email ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        $stmt = $this->db->prepare("SELECT ID_Usuario, Email FROM usuario WHERE Email = :e AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([':e' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    /** Genera token, invalida anteriores y retorna token plano (solo se envía por correo). */
    public function crearToken($idUsuario, $horas = 1)
    {
        $token = bin2hex(random_bytes(32)); // 64 chars
        $hash = hash('sha256', $token);
        $this->db->prepare("UPDATE password_resets SET usado_en = NOW() WHERE ID_Usuario = :u AND usado_en IS NULL")
                  ->execute([':u' => $idUsuario]);
        $stmt = $this->db->prepare("INSERT INTO password_resets (ID_Usuario, token_hash, expira_en) VALUES (:u, :h, DATE_ADD(NOW(), INTERVAL :hh HOUR))");
        $stmt->bindValue(':u', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':h', $hash);
        $stmt->bindValue(':hh', max(1, min(24, (int)$horas)), PDO::PARAM_INT);
        $stmt->execute();
        return $token;
    }

    public function validarToken($tokenPlano)
    {
        if (!is_string($tokenPlano) || !preg_match('/^[a-f0-9]{64}$/', $tokenPlano)) return false;
        $hash = hash('sha256', $tokenPlano);
        $stmt = $this->db->prepare("SELECT r.ID_Reset, r.ID_Usuario, u.Email
                                    FROM password_resets r JOIN usuario u ON u.ID_Usuario = r.ID_Usuario
                                    WHERE r.token_hash = :h AND r.usado_en IS NULL AND r.expira_en > NOW()
                                      AND u.deleted_at IS NULL LIMIT 1");
        $stmt->execute([':h' => $hash]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    /** Cambia Password_Hash y marca token como usado. Retorna bool. */
    public function consumirToken($tokenPlano, $nuevaPassword)
    {
        $row = $this->validarToken($tokenPlano);
        if (!$row) return false;
        $hash = password_hash($nuevaPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        try {
            $this->db->beginTransaction();
            $up = $this->db->prepare("UPDATE usuario SET Password_Hash = :h WHERE ID_Usuario = :u");
            $up->execute([':h' => $hash, ':u' => $row['ID_Usuario']]);
            $mk = $this->db->prepare("UPDATE password_resets SET usado_en = NOW() WHERE ID_Reset = :r");
            $mk->execute([':r' => $row['ID_Reset']]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("PasswordReset::consumir: " . $e->getMessage());
            return false;
        }
    }
}
