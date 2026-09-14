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
        if (isset($row['Seudonimo']) && !isset($row['seudonimo'])) {
            $row['seudonimo'] = $row['Seudonimo'];
        } elseif (isset($row['seudonimo']) && !isset($row['Seudonimo'])) {
            $row['Seudonimo'] = $row['seudonimo'];
        }
        // Foto de perfil (NULL = mostrar inicial del nombre)
        if (isset($row['Foto']) && !isset($row['foto'])) {
            $row['foto'] = $row['Foto'];
        } elseif (isset($row['foto']) && !isset($row['Foto'])) {
            $row['Foto'] = $row['foto'];
        }
        // Nombre + apellido reales desde cliente (si viene del JOIN)
        $nom = trim((string)($row['ClienteNombres'] ?? $row['Nombres'] ?? $row['nombres'] ?? ''));
        $ape = trim((string)($row['ClienteApellidos'] ?? $row['Apellidos'] ?? $row['apellidos'] ?? ''));
        if ($nom !== '' || $ape !== '') {
            $full = trim($nom . ' ' . $ape);
            $row['Nombres'] = $nom;
            $row['Apellidos'] = $ape;
            $row['nombres'] = $nom;
            $row['apellidos'] = $ape;
            $row['nombre'] = $full;
            $row['nombre_completo'] = $full;
        } elseif (!isset($row['nombre'])) {
            $row['nombre'] = $this->nombreDesdeEmail($email);
        }
        if (!array_key_exists('Activo', $row)) {
            $row['Activo'] = empty($row['deleted_at']) ? 1 : 0;
        }
        return $row;
    }

    // =========================================================================
    // RF 1.1 — VALIDACIONES ESTRICTAS DE REGISTRO
    // documento: 6-12 dígitos | nombres/apellidos: 2-70 letras
    // correo: válido max 80 | teléfono: 7 o 10 dígitos
    // seudónimo: 3-50 [A-Za-z0-9_.-] opcional pero único
    // clave: 8-20, mayúscula, minúscula, número y símbolo
    // =========================================================================
    public const PWD_REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._\-#])[A-Za-z\d@$!%*?&._\-#]{8,20}$/';

    public function validarRegistro(array $d)
    {
        $email     = $this->normalizarEmail($d['email'] ?? $d['Email'] ?? '');
        $documento = trim((string)($d['documento'] ?? $d['Documento'] ?? ''));
        $nombres   = trim((string)($d['nombres'] ?? $d['Nombres'] ?? ''));
        $apellidos = trim((string)($d['apellidos'] ?? $d['Apellidos'] ?? ''));
        $telefono  = trim((string)($d['telefono'] ?? $d['Telefono'] ?? ''));
        $seudonimo = trim((string)($d['seudonimo'] ?? $d['Seudonimo'] ?? ''));
        $password  = (string)($d['password'] ?? $d['Password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Correo electrónico inválido.'];
        }
        if (strlen($email) > 80) {
            return ['ok' => false, 'message' => 'El correo no puede superar 80 caracteres.'];
        }
        if (!preg_match('/^[0-9]{6,12}$/', $documento)) {
            return ['ok' => false, 'message' => 'Documento inválido: debe tener entre 6 y 12 dígitos numéricos.'];
        }
        $nomRegex = '/^[\p{L} \.\'-]{2,70}$/u';
        if (!preg_match($nomRegex, $nombres)) {
            return ['ok' => false, 'message' => 'Nombres inválidos: 2 a 70 caracteres (solo letras).'];
        }
        if (!preg_match($nomRegex, $apellidos)) {
            return ['ok' => false, 'message' => 'Apellidos inválidos: 2 a 70 caracteres (solo letras).'];
        }
        if (!preg_match('/^[0-9]{7}$|^[0-9]{10}$/', $telefono)) {
            return ['ok' => false, 'message' => 'Teléfono inválido: debe tener 7 o 10 dígitos.'];
        }
        if ($seudonimo !== '' && !preg_match('/^[A-Za-z0-9_.\-]{3,50}$/', $seudonimo)) {
            return ['ok' => false, 'message' => 'Seudónimo inválido: 3 a 50 caracteres (letras, números, _ . -).'];
        }
        if (!preg_match(self::PWD_REGEX, $password)) {
            return ['ok' => false, 'message' => 'La contraseña debe tener 8-20 caracteres, mayúscula, minúscula, número y símbolo (@$!%*?&._-#).'];
        }
        return ['ok' => true, 'message' => 'OK', 'clean' => [
            'email' => $email, 'documento' => $documento, 'nombres' => $nombres,
            'apellidos' => $apellidos, 'telefono' => $telefono,
            'seudonimo' => ($seudonimo !== '' ? $seudonimo : null), 'password' => $password,
        ]];
    }

    /** Asegura fila en control_accesos para el usuario (cubre usuarios viejos). */
    private function asegurarControlAccesos($idUsuario, $email)
    {
        try {
            $this->db->prepare("INSERT IGNORE INTO control_accesos (ID_Usuario, Email, Intentos_Fallidos) VALUES (:u, :e, 0)")
                     ->execute([':u' => $idUsuario, ':e' => $email]);
        } catch (PDOException $e) {}
    }

    /** RF 1.3: retorna Bloqueado_Hasta (string) si está bloqueado, false si no. */
    public function estaBloqueado($login)
    {
        $login = trim((string)$login);
        if ($login === '') return false;
        try {
            $esEmail = (strpos($login, '@') !== false);
            if ($esEmail) {
                $stmt = $this->db->prepare("SELECT ca.Bloqueado_Hasta FROM control_accesos ca JOIN usuario u ON u.ID_Usuario = ca.ID_Usuario WHERE u.Email = :l AND u.deleted_at IS NULL LIMIT 1");
            } else {
                $stmt = $this->db->prepare("SELECT ca.Bloqueado_Hasta FROM control_accesos ca JOIN usuario u ON u.ID_Usuario = ca.ID_Usuario WHERE (u.Seudonimo = :l1 OR u.Email = :l2) AND u.deleted_at IS NULL LIMIT 1");
                $stmt->execute([':l1' => $login, ':l2' => $login]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($r && !empty($r['Bloqueado_Hasta']) && strtotime($r['Bloqueado_Hasta']) > time()) {
                    return $r['Bloqueado_Hasta'];
                }
                return false;
            }
            $stmt->execute([':l' => $this->normalizarEmail($login)]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($r && !empty($r['Bloqueado_Hasta']) && strtotime($r['Bloqueado_Hasta']) > time()) {
                return $r['Bloqueado_Hasta'];
            }
            return false;
        } catch (PDOException $e) { return false; }
    }

    public function login($login, $password)
    {
        $login = trim((string)$login);
        if ($login === '' || empty($password)) {
            return false;
        }
        $esEmail = (strpos($login, '@') !== false);
        if ($esEmail) {
            $login = $this->normalizarEmail($login);
            if (!filter_var($login, FILTER_VALIDATE_EMAIL)) return false;
        }
        try {
            if ($esEmail) {
                $query = "SELECT u.ID_Usuario, u.Email, u.Seudonimo, u.Foto, u.Password_Hash, u.Rol, u.deleted_at, c.Nombres AS ClienteNombres, c.Apellidos AS ClienteApellidos FROM usuario u LEFT JOIN cliente c ON c.ID_Cliente = u.ID_Cliente WHERE u.Email = :login AND u.deleted_at IS NULL LIMIT 1";
            } else {
                // RF 1.2: login con correo O seudónimo (validación exacta)
                // OJO: con prepares nativos (EMULATE_PREPARES=false) no se puede
                // reutilizar el mismo placeholder dos veces → se usan :login1/:login2
                $query = "SELECT u.ID_Usuario, u.Email, u.Seudonimo, u.Foto, u.Password_Hash, u.Rol, u.deleted_at, c.Nombres AS ClienteNombres, c.Apellidos AS ClienteApellidos FROM usuario u LEFT JOIN cliente c ON c.ID_Cliente = u.ID_Cliente WHERE (u.Seudonimo = :login1 OR u.Email = :login2) AND u.deleted_at IS NULL LIMIT 1";
            }
            // Nota: si la columna Seudonimo/Foto aún no existe (sin migrar), reintenta solo por Email.
            try {
                $stmt = $this->db->prepare($query);
                if ($esEmail) {
                    $stmt->execute([':login' => $login]);
                } else {
                    $stmt->execute([':login1' => $login, ':login2' => $login]);
                }
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                if (stripos($e->getMessage(), 'Unknown column') === false) throw $e;
                $stmt = $this->db->prepare("SELECT u.ID_Usuario, u.Email, u.Password_Hash, u.Rol, u.deleted_at, c.Nombres AS ClienteNombres, c.Apellidos AS ClienteApellidos FROM usuario u LEFT JOIN cliente c ON c.ID_Cliente = u.ID_Cliente WHERE u.Email = :login AND u.deleted_at IS NULL LIMIT 1");
                $stmt->execute([':login' => $this->normalizarEmail($login)]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if (!$user) return false;

            $this->asegurarControlAccesos($user['ID_Usuario'], $user['Email']);

            // RF 1.3: verificar bloqueo temporal antes de validar clave
            $chk = $this->db->prepare("SELECT Intentos_Fallidos, Bloqueado_Hasta FROM control_accesos WHERE ID_Usuario = :u LIMIT 1");
            $chk->execute([':u' => $user['ID_Usuario']]);
            $acc = $chk->fetch(PDO::FETCH_ASSOC);
            if ($acc && !empty($acc['Bloqueado_Hasta']) && strtotime($acc['Bloqueado_Hasta']) > time()) {
                return false; // bloqueado: mensaje lo decide index.php via estaBloqueado()
            }

            if (password_verify($password, $user["Password_Hash"])) {
                // Éxito: reset intentos (el trigger trg_limpiar_sesiones limpia Bloqueado_Hasta)
                try {
                    $this->db->prepare("UPDATE control_accesos SET Intentos_Fallidos = 0, Ultimo_Intento = NOW() WHERE ID_Usuario = :u")
                             ->execute([':u' => $user['ID_Usuario']]);
                } catch (PDOException $e) {}
                unset($user["Password_Hash"]);
                return $this->mapearFila($user);
            }
            // Fallo: incrementa (el trigger trg_bloqueo_5_intentos bloquea 15 min al llegar a 5)
            try {
                $this->db->prepare("UPDATE control_accesos SET Intentos_Fallidos = Intentos_Fallidos + 1 WHERE ID_Usuario = :u")
                         ->execute([':u' => $user['ID_Usuario']]);
            } catch (PDOException $e) {}
            return false;
        } catch (PDOException $e) {
            error_log("Usuario::login: " . $e->getMessage());
            return false;
        }
    }

    public function registrar($email, $password, $rol = 'Cliente', array $extra = [])
    {
        // RF 1.1: si vienen datos extendidos, usa registro completo con validaciones estrictas
        if (!empty($extra) || isset($extra['documento']) || isset($extra['nombres'])) {
            $r = $this->registrarCompleto(array_merge($extra, ['email' => $email, 'password' => $password, 'rol' => $rol]));
            return $r['ok'];
        }
        $email = $this->normalizarEmail($email);
        $rol = $this->rolValido($rol);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            return false;
        }
        if (strlen($email) > 80) return false;
        if (!preg_match(self::PWD_REGEX, $password)) return false;
        try {
            $checkStmt = $this->db->prepare("SELECT 1 FROM usuario WHERE Email = :email AND deleted_at IS NULL LIMIT 1");
            $checkStmt->execute([':email' => $email]);
            if ($checkStmt->fetch()) {
                return false;
            }
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->db->prepare("INSERT INTO usuario (Email, Password_Hash, Rol) VALUES (:email, :hash, :rol)");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":hash", $hash);
            $stmt->bindParam(":rol", $rol);
            $ok = $stmt->execute();
            if ($ok) {
                // Crea empleado/cliente según el rol elegido
                $this->asegurarEntidadPorRol((int)$this->db->lastInsertId());
            }
            return $ok;
        } catch (PDOException $e) {
            error_log("Usuario::registrar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * RF 1.1: registro completo — crea cliente + usuario enlazado en transacción.
     * Retorna ['ok'=>bool,'message'=>string].
     */
    public function registrarCompleto(array $datos)
    {
        $v = $this->validarRegistro($datos);
        if (!$v['ok']) return $v;
        $c = $v['clean'];
        $rol = $this->rolValido($datos['rol'] ?? $datos['Rol'] ?? 'Cliente');
        try {
            // Duplicados: email / seudónimo / documento
            $chk = $this->db->prepare("SELECT 1 FROM usuario WHERE Email = :e AND deleted_at IS NULL LIMIT 1");
            $chk->execute([':e' => $c['email']]);
            if ($chk->fetch()) return ['ok' => false, 'message' => 'Este correo electrónico ya se encuentra registrado. Intenta iniciar sesión.'];
            if (!empty($c['seudonimo'])) {
                try {
                    $chkS = $this->db->prepare("SELECT 1 FROM usuario WHERE Seudonimo = :s LIMIT 1");
                    $chkS->execute([':s' => $c['seudonimo']]);
                    if ($chkS->fetch()) return ['ok' => false, 'message' => 'Ese seudónimo ya está en uso. Elige otro.'];
                } catch (PDOException $e) { /* columna aún no migrada: ignora */ }
            }
            try {
                $chkD = $this->db->prepare("SELECT 1 FROM cliente WHERE Documento = :d LIMIT 1");
                $chkD->execute([':d' => $c['documento']]);
                if ($chkD->fetch()) return ['ok' => false, 'message' => 'Ese documento ya se encuentra registrado.'];
            } catch (PDOException $e) { /* columna aún no migrada: sigue sin validar duplicado */ }

            $this->db->beginTransaction();
            // Cliente primero (Nombres/Apellidos 70 + Documento + Teléfono)
            try {
                $insC = $this->db->prepare("INSERT INTO cliente (Nombres, Apellidos, Telefono, Documento) VALUES (:n, :a, :t, :d)");
                $insC->execute([':n' => $c['nombres'], ':a' => $c['apellidos'], ':t' => $c['telefono'], ':d' => $c['documento']]);
            } catch (PDOException $e) {
                // Fallback sin columna Documento (BD sin migrar)
                if (stripos($e->getMessage(), 'Documento') !== false || stripos($e->getMessage(), 'Unknown column') !== false) {
                    $insC = $this->db->prepare("INSERT INTO cliente (Nombres, Apellidos, Telefono) VALUES (:n, :a, :t)");
                    $insC->execute([':n' => substr($c['nombres'], 0, 50), ':a' => substr($c['apellidos'], 0, 50), ':t' => $c['telefono']]);
                } else { throw $e; }
            }
            $idCli = (int)$this->db->lastInsertId();
            $hash = password_hash($c['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            try {
                $insU = $this->db->prepare("INSERT INTO usuario (Email, Password_Hash, Rol, ID_Cliente, Seudonimo) VALUES (:e, :h, :r, :c, :s)");
                $insU->execute([':e' => $c['email'], ':h' => $hash, ':r' => $rol, ':c' => $idCli, ':s' => $c['seudonimo']]);
            } catch (PDOException $e) {
                if (stripos($e->getMessage(), 'Seudonimo') !== false || stripos($e->getMessage(), 'Unknown column') !== false) {
                    $insU = $this->db->prepare("INSERT INTO usuario (Email, Password_Hash, Rol, ID_Cliente) VALUES (:e, :h, :r, :c)");
                    $insU->execute([':e' => $c['email'], ':h' => $hash, ':r' => $rol, ':c' => $idCli]);
                } else { throw $e; }
            }
            $idUser = (int)$this->db->lastInsertId();
            // OJO: lastInsertId aquí es del USUARIO (último INSERT fue $insU)
            $this->db->commit();
            // Si el rol elegido fue Empleado, crea también su fila de empleado
            $this->asegurarEntidadPorRol($idUser);
            return ['ok' => true, 'message' => '¡Usuario registrado con éxito!'];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Usuario::registrarCompleto: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['ok' => false, 'message' => 'Este correo, documento o seudónimo ya se encuentra registrado.'];
            }
            return ['ok' => false, 'message' => 'No se pudo completar el registro. Intenta más tarde.'];
        }
    }

    // =========================================================================
    // CRUD POR ID (MVC: la vista no toca la BD) - Esquema DB.sql
    // =========================================================================
    public function obtenerTodos()
    {
        try {
            // No traer Password_Hash por seguridad. Incluye inactivos para poder reactivar (legal: sin borrado).
            // LEFT JOIN cliente para mostrar/editar datos personales en el crud.
            $stmt = $this->db->query("SELECT u.ID_Usuario, u.Email, u.Rol, u.deleted_at, (u.deleted_at IS NULL) AS Activo, u.ID_Usuario AS id, u.Email AS email, u.Rol AS rol, c.Documento, c.Telefono, c.Nombres AS ClienteNombres, c.Apellidos AS ClienteApellidos FROM usuario u LEFT JOIN cliente c ON c.ID_Cliente = u.ID_Cliente ORDER BY u.ID_Usuario DESC LIMIT 200");
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
            $stmt = $this->db->prepare("SELECT u.ID_Usuario, u.Email, u.Rol, u.Foto, u.deleted_at, (u.deleted_at IS NULL) AS Activo, c.Nombres AS ClienteNombres, c.Apellidos AS ClienteApellidos FROM usuario u LEFT JOIN cliente c ON c.ID_Cliente = u.ID_Cliente WHERE u.ID_Usuario = :id LIMIT 1");
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
            $chk = $this->db->prepare("SELECT ID_Usuario FROM usuario WHERE Email = :email AND deleted_at IS NULL LIMIT 1");
            $chk->execute([':email' => $email]);
            $existe = $chk->fetch(PDO::FETCH_ASSOC);
            if ($existe && (string)$existe['ID_Usuario'] !== (string)$id) {
                return false;
            }
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                if ($rol !== null) {
                    $rol = $this->rolValido($rol);
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email, Password_Hash = :hash, Rol = :rol WHERE ID_Usuario = :id AND deleted_at IS NULL");
                    $stmt->bindParam(":rol", $rol);
                } else {
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email, Password_Hash = :hash WHERE ID_Usuario = :id AND deleted_at IS NULL");
                }
                $stmt->bindParam(":hash", $hash);
            } else {
                if ($rol !== null) {
                    $rol = $this->rolValido($rol);
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email, Rol = :rol WHERE ID_Usuario = :id AND deleted_at IS NULL");
                    $stmt->bindParam(":rol", $rol);
                } else {
                    $stmt = $this->db->prepare("UPDATE usuario SET Email = :email WHERE ID_Usuario = :id AND deleted_at IS NULL");
                }
            }
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $ok = $stmt->execute();
            if ($ok) {
                try {
                    $upd = $this->db->prepare("UPDATE control_accesos SET Email = :email WHERE ID_Usuario = :id");
                    $upd->execute([':email' => $email, ':id' => $id]);
                } catch (PDOException $e) {
                }
            }
            if ($ok) {
                // Si el rol cambió a Empleado/Cliente, crea y enlaza la fila que falte
                $this->asegurarEntidadPorRol($id);
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
        try {
            if ((int)$activo === 1) {
                $stmt = $this->db->prepare("UPDATE usuario SET deleted_at = NULL WHERE ID_Usuario = :id");
            } else {
                $stmt = $this->db->prepare("UPDATE usuario SET deleted_at = NOW() WHERE ID_Usuario = :id AND deleted_at IS NULL");
            }
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

    // =========================================================================
    // MÉTODO: CAMBIO DE CONTRASEÑA (DB.sql: Password_Hash) — RF 1.5
    // =========================================================================
    public function cambiarPassword($email, $actualPassword, $nuevaPassword)
    {
        $email = $this->normalizarEmail($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["status" => false, "message" => "Correo invalido."];
        }
        // RF 1.5: complejidad 8-20 + mayúscula + minúscula + número + símbolo
        if (!preg_match(self::PWD_REGEX, (string)$nuevaPassword)) {
            return ["status" => false, "message" => "La nueva contraseña debe tener 8-20 caracteres, mayúscula, minúscula, número y símbolo."];
        }
        try {
            $query = "SELECT ID_Usuario, Email, Password_Hash FROM usuario WHERE Email = :email AND deleted_at IS NULL LIMIT 1";
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
            // RF 1.5: debe ser diferente a la anterior
            if (password_verify($nuevaPassword, $user["Password_Hash"])) {
                return ["status" => false, "message" => "La nueva contraseña debe ser diferente a la anterior."];
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

    // =========================================================================
    // FOTO DE PERFIL (usuario.Foto: ruta web o NULL = inicial del nombre)
    // =========================================================================
    public function fotoActual($id)
    {
        if (!ctype_digit((string)$id)) return null;
        try {
            $stmt = $this->db->prepare("SELECT Foto FROM usuario WHERE ID_Usuario = :id LIMIT 1");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return $r["Foto"] ?? null;
        } catch (PDOException $e) { return null; }
    }

    public function actualizarFoto($id, $rutaWeb)
    {
        if (!ctype_digit((string)$id)) return false;
        try {
            $stmt = $this->db->prepare("UPDATE usuario SET Foto = :f WHERE ID_Usuario = :id AND deleted_at IS NULL");
            if ($rutaWeb === null) {
                $stmt->bindValue(":f", null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(":f", $rutaWeb, PDO::PARAM_STR);
            }
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Usuario::actualizarFoto: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // SINCRONIZACIÓN ROL -> ENTIDAD: al cambiar el Rol desde el crud se crea
    // la fila que falte (empleado para Empleado, cliente para Cliente) y se
    // enlaza en usuario.ID_Empleado / ID_Cliente. Solo crea, nunca borra
    // (preserva historial de ventas/movimientos).
    // =========================================================================
    public function asegurarEntidadPorRol($idUsuario)
    {
        if (!ctype_digit((string)$idUsuario)) return false;
        try {
            $stmt = $this->db->prepare("SELECT u.ID_Usuario, u.Email, u.Rol, u.ID_Cliente, u.ID_Empleado, c.Nombres AS CN, c.Apellidos AS CA FROM usuario u LEFT JOIN cliente c ON c.ID_Cliente = u.ID_Cliente WHERE u.ID_Usuario = :id AND u.deleted_at IS NULL LIMIT 1");
            $stmt->execute([':id' => $idUsuario]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u) return false;

            if ($u['Rol'] === 'Empleado' && empty($u['ID_Empleado'])) {
                $nom = trim((string)($u['CN'] ?? ''));
                $ape = trim((string)($u['CA'] ?? ''));
                if ($nom === '') $nom = $this->nombreDesdeEmail($u['Email']);
                $idCargo = $this->asegurarCargoDefecto();
                $idCiudad = $this->asegurarCiudadDefecto();
                if (empty($idCargo) || empty($idCiudad)) {
                    error_log("Usuario::asegurarEntidadPorRol: sin cargo/ciudad para empleado $idUsuario");
                    return false;
                }
                $ins = $this->db->prepare("INSERT INTO empleado (Nombres, Apellidos, ID_Cargo, ID_Ciudad) VALUES (:n, :a, :c, :ci)");
                $ins->execute([':n' => substr($nom, 0, 50), ':a' => substr($ape, 0, 50), ':c' => $idCargo, ':ci' => $idCiudad]);
                $idEmp = (int)$this->db->lastInsertId();
                $this->db->prepare("UPDATE usuario SET ID_Empleado = :e WHERE ID_Usuario = :u")->execute([':e' => $idEmp, ':u' => $idUsuario]);
            }

            if ($u['Rol'] === 'Cliente' && empty($u['ID_Cliente'])) {
                $nom = $this->nombreDesdeEmail($u['Email']);
                $ins = $this->db->prepare("INSERT INTO cliente (Nombres, Apellidos) VALUES (:n, '')");
                $ins->execute([':n' => substr($nom, 0, 50)]);
                $idCli = (int)$this->db->lastInsertId();
                $this->db->prepare("UPDATE usuario SET ID_Cliente = :c WHERE ID_Usuario = :u")->execute([':c' => $idCli, ':u' => $idUsuario]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Usuario::asegurarEntidadPorRol: " . $e->getMessage());
            return false;
        }
    }

    /** Primer cargo disponible; si la tabla está vacía crea "General". */
    private function asegurarCargoDefecto()
    {
        try {
            $r = $this->db->query("SELECT ID_Cargo FROM cargo ORDER BY ID_Cargo LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($r) return (int)$r['ID_Cargo'];
            $this->db->prepare("INSERT INTO cargo (Nombre_Cargo, Salario_Base) VALUES ('General', 0)")->execute();
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Usuario::asegurarCargoDefecto: " . $e->getMessage());
            return null;
        }
    }

    /** Primera ciudad disponible (requiere departamentos cargados). */
    private function asegurarCiudadDefecto()
    {
        try {
            $r = $this->db->query("SELECT ID_Ciudad FROM ciudad ORDER BY ID_Ciudad LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            return $r ? (int)$r['ID_Ciudad'] : null;
        } catch (PDOException $e) {
            error_log("Usuario::asegurarCiudadDefecto: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // DATOS PERSONALES (cliente: Documento, Nombres, Apellidos, Teléfono)
    // RF 1.1: documento 6-12 dígitos, nombres/apellidos 2-70 letras,
    // teléfono 7 o 10 dígitos. Sincroniza Nombres/Apellidos en empleado si
    // el usuario tiene ID_Empleado. Nunca toca la foto.
    // =========================================================================
    private function validarPersona(array $d)
    {
        $documento = trim((string)($d['documento'] ?? $d['Documento'] ?? ''));
        $nombres   = trim((string)($d['nombres'] ?? $d['Nombres'] ?? ''));
        $apellidos = trim((string)($d['apellidos'] ?? $d['Apellidos'] ?? ''));
        $telefono  = trim((string)($d['telefono'] ?? $d['Telefono'] ?? ''));
        if (!preg_match('/^[0-9]{6,12}$/', $documento)) {
            return ['ok' => false, 'message' => 'Documento inválido: debe tener entre 6 y 12 dígitos numéricos.'];
        }
        $nomRegex = '/^[\p{L} \.\'-]{2,70}$/u';
        if (!preg_match($nomRegex, $nombres)) {
            return ['ok' => false, 'message' => 'Nombres inválidos: 2 a 70 caracteres (solo letras).'];
        }
        if (!preg_match($nomRegex, $apellidos)) {
            return ['ok' => false, 'message' => 'Apellidos inválidos: 2 a 70 caracteres (solo letras).'];
        }
        if (!preg_match('/^[0-9]{7}$|^[0-9]{10}$/', $telefono)) {
            return ['ok' => false, 'message' => 'Teléfono inválido: debe tener 7 o 10 dígitos.'];
        }
        return ['ok' => true, 'message' => 'OK', 'clean' => [
            'documento' => $documento, 'nombres' => $nombres,
            'apellidos' => $apellidos, 'telefono' => $telefono,
        ]];
    }

    public static function personaVacia(array $d)
    {
        foreach (['documento', 'Documento', 'nombres', 'Nombres', 'apellidos', 'Apellidos', 'telefono', 'Telefono'] as $k) {
            if (trim((string)($d[$k] ?? '')) !== '') return false;
        }
        return true;
    }

    /** Lee los datos personales editables del usuario (cliente o fallback empleado). */
    public function datosPersona($id)
    {
        $base = ['documento' => '', 'nombres' => '', 'apellidos' => '', 'telefono' => ''];
        if (!ctype_digit((string)$id)) return $base;
        try {
            $stmt = $this->db->prepare("SELECT c.Documento, c.Nombres, c.Apellidos, c.Telefono, e.Nombres AS EN, e.Apellidos AS EA FROM usuario u LEFT JOIN cliente c ON c.ID_Cliente = u.ID_Cliente LEFT JOIN empleado e ON e.ID_Empleado = u.ID_Empleado WHERE u.ID_Usuario = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$r) return $base;
            // try/catch por si la columna Documento aún no existe (sin migrar)
            return [
                'documento' => (string)($r['Documento'] ?? ''),
                'nombres'   => (string)($r['Nombres'] ?? $r['EN'] ?? ''),
                'apellidos' => (string)($r['Apellidos'] ?? $r['EA'] ?? ''),
                'telefono'  => (string)($r['Telefono'] ?? ''),
            ];
        } catch (PDOException $e) {
            error_log("Usuario::datosPersona: " . $e->getMessage());
            return $base;
        }
    }

    /**
     * Crea o actualiza los datos personales. Si el usuario no tiene cliente,
     * lo crea y enlaza. Retorna ['ok'=>bool,'message'=>string].
     */
    public function actualizarPersona($id, array $datos)
    {
        if (!ctype_digit((string)$id)) {
            return ['ok' => false, 'message' => 'Usuario inválido.'];
        }
        $v = $this->validarPersona($datos);
        if (!$v['ok']) return $v;
        $c = $v['clean'];
        try {
            $stmt = $this->db->prepare("SELECT ID_Cliente, ID_Empleado FROM usuario WHERE ID_Usuario = :id AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([':id' => $id]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u) return ['ok' => false, 'message' => 'El usuario no existe.'];
            // Documento único (excluye su propio cliente)
            try {
                $chk = $this->db->prepare("SELECT ID_Cliente FROM cliente WHERE Documento = :d LIMIT 1");
                $chk->execute([':d' => $c['documento']]);
                $ex = $chk->fetch(PDO::FETCH_ASSOC);
                if ($ex && (string)$ex['ID_Cliente'] !== (string)($u['ID_Cliente'] ?? '')) {
                    return ['ok' => false, 'message' => 'Ese documento ya está registrado en otro usuario.'];
                }
            } catch (PDOException $e) { /* columna sin migrar: sigue sin validar duplicado */ }
            if (!empty($u['ID_Cliente'])) {
                try {
                    $up = $this->db->prepare("UPDATE cliente SET Documento = :d, Nombres = :n, Apellidos = :a, Telefono = :t WHERE ID_Cliente = :c");
                    $up->execute([':d' => $c['documento'], ':n' => $c['nombres'], ':a' => $c['apellidos'], ':t' => $c['telefono'], ':c' => $u['ID_Cliente']]);
                } catch (PDOException $e) {
                    if (stripos($e->getMessage(), 'Documento') !== false || stripos($e->getMessage(), 'Unknown column') !== false) {
                        $up = $this->db->prepare("UPDATE cliente SET Nombres = :n, Apellidos = :a, Telefono = :t WHERE ID_Cliente = :c");
                        $up->execute([':n' => substr($c['nombres'], 0, 50), ':a' => substr($c['apellidos'], 0, 50), ':t' => $c['telefono'], ':c' => $u['ID_Cliente']]);
                    } else { throw $e; }
                }
            } else {
                try {
                    $ins = $this->db->prepare("INSERT INTO cliente (Nombres, Apellidos, Telefono, Documento) VALUES (:n, :a, :t, :d)");
                    $ins->execute([':n' => $c['nombres'], ':a' => $c['apellidos'], ':t' => $c['telefono'], ':d' => $c['documento']]);
                } catch (PDOException $e) {
                    if (stripos($e->getMessage(), 'Documento') !== false || stripos($e->getMessage(), 'Unknown column') !== false) {
                        $ins = $this->db->prepare("INSERT INTO cliente (Nombres, Apellidos, Telefono) VALUES (:n, :a, :t)");
                        $ins->execute([':n' => substr($c['nombres'], 0, 50), ':a' => substr($c['apellidos'], 0, 50), ':t' => $c['telefono']]);
                    } else { throw $e; }
                }
                $idCli = (int)$this->db->lastInsertId();
                $this->db->prepare("UPDATE usuario SET ID_Cliente = :c WHERE ID_Usuario = :u")->execute([':c' => $idCli, ':u' => $id]);
            }
            // Sincroniza nombres en empleado si tiene
            if (!empty($u['ID_Empleado'])) {
                try {
                    $this->db->prepare("UPDATE empleado SET Nombres = :n, Apellidos = :a WHERE ID_Empleado = :e")
                             ->execute([':n' => substr($c['nombres'], 0, 50), ':a' => substr($c['apellidos'], 0, 50), ':e' => $u['ID_Empleado']]);
                } catch (PDOException $e) {}
            }
            return ['ok' => true, 'message' => 'Datos personales actualizados.'];
        } catch (PDOException $e) {
            error_log("Usuario::actualizarPersona: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return ['ok' => false, 'message' => 'Ese documento ya está registrado en otro usuario.'];
            }
            return ['ok' => false, 'message' => 'No se pudo guardar. Intenta más tarde.'];
        }
    }
}
