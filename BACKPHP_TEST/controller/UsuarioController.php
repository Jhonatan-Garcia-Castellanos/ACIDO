<?php
// BACKPHP/controller/UsuarioController.php
require_once __DIR__ . "/../model/Usuario.php";

class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function login($email, $password) {
        return $this->usuarioModel->login($email, $password);
    }

    public function registrar($email, $password, $rol = 'Cliente', array $extra = []) {
        return $this->usuarioModel->registrar($email, $password, $rol, $extra);
    }

    public function registrarCompleto(array $datos) {
        return $this->usuarioModel->registrarCompleto($datos);
    }

    public function estaBloqueado($login) {
        return $this->usuarioModel->estaBloqueado($login);
    }

    public function cambiarPassword($email, $actualPassword, $nuevaPassword) {
        return $this->usuarioModel->cambiarPassword($email, $actualPassword, $nuevaPassword);
    }

    public function listar($page = 1, $per = 10, $q = '', $order = 'ID_Usuario', $dir = 'DESC') {
        return $this->usuarioModel->obtenerTodos($page, $per, $q, $order, $dir);
    }

    public function contar($q = '') {
        return $this->usuarioModel->contarUsuarios($q);
    }

    public function obtenerPorId($id) {
        return $this->usuarioModel->obtenerPorId($id);
    }

    public function guardar($datos) {
        $id = trim((string)($datos['id'] ?? $datos['ID_Usuario'] ?? ''));
        $email = strtolower(trim($datos['email'] ?? $datos['Email'] ?? ''));
        $password = $datos['password'] ?? '';
        $rol = trim($datos['rol'] ?? $datos['Rol'] ?? 'Cliente');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!empty($id)) {
            if (!ctype_digit($id)) {
                return false;
            }
            // Cuenta (email/clave/rol) + datos personales (documento/nombres/apellidos/teléfono)
            $okCuenta = $this->usuarioModel->actualizarPorId($id, $email, $password, $rol);
            if (!$okCuenta) return false;
            if (!Usuario::personaVacia($datos)) {
                $rp = $this->usuarioModel->actualizarPersona($id, $datos);
                if (!$rp['ok']) return false;
            }
            return true;
        }

        if (empty($password)) {
            return false;
        }

        // Crear: si trae datos personales usa registro completo, si no el simple
        if (!Usuario::personaVacia($datos)) {
            $r = $this->usuarioModel->registrarCompleto([
                'email' => $email, 'password' => $password, 'rol' => $rol,
                'documento' => trim((string)($datos['documento'] ?? '')),
                'nombres' => trim((string)($datos['nombres'] ?? '')),
                'apellidos' => trim((string)($datos['apellidos'] ?? '')),
                'telefono' => trim((string)($datos['telefono'] ?? '')),
                'seudonimo' => trim((string)($datos['seudonimo'] ?? '')),
            ]);
            return !empty($r['ok']);
        }

        return $this->usuarioModel->registrar($email, $password, $rol);
    }

    public function eliminar($id) {
        // Legal: inactivar, no borrar
        if (!ctype_digit((string)$id)) {
            return false;
        }
        return $this->usuarioModel->cambiarEstado($id, 0);
    }

    public function cambiarEstado($id, $activo) {
        if (!ctype_digit((string)$id)) {
            return false;
        }
        return $this->usuarioModel->cambiarEstado($id, $activo);
    }

    // Datos personales (documento/nombres/apellidos/teléfono). Nunca toca la foto.
    public function actualizarPersona($id, array $datos) {
        return $this->usuarioModel->actualizarPersona($id, $datos);
    }

    public function datosPersona($id) {
        return $this->usuarioModel->datosPersona($id);
    }

    // =====================================================================
    // FOTO DE PERFIL: JPG/PNG/WEBP max 2MB -> public/img/perfiles/
    // Retorna ['ok'=>bool,'message'=>string,'ruta'=>?string]
    // =====================================================================
    public function subirFoto($idUsuario, array $file) {
        if (!ctype_digit((string)$idUsuario)) {
            return ['ok' => false, 'message' => 'Sesión inválida.'];
        }
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'message' => 'Elige una imagen primero.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'No se pudo subir la imagen. Intenta de nuevo.'];
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'La imagen supera 2MB.'];
        }
        $permitidas = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!isset($permitidas[$ext])) {
            return ['ok' => false, 'message' => 'Formato inválido: solo JPG, PNG o WEBP.'];
        }
        $info = @getimagesize($file['tmp_name']);
        if ($info === false || !in_array($info['mime'], $permitidas, true)) {
            return ['ok' => false, 'message' => 'El archivo no es una imagen válida.'];
        }
        $dirFs = __DIR__ . '/../public/img/perfiles';
        if (!is_dir($dirFs) && !@mkdir($dirFs, 0755, true)) {
            return ['ok' => false, 'message' => 'No se pudo crear la carpeta de fotos.'];
        }
        // Borra foto anterior del usuario (si vive en perfiles/)
        $anterior = $this->usuarioModel->fotoActual($idUsuario);
        $nuevo = 'perfil_' . $idUsuario . '_' . time() . '.' . $ext;
        $destFs = $dirFs . '/' . $nuevo;
        if (!@move_uploaded_file($file['tmp_name'], $destFs)) {
            return ['ok' => false, 'message' => 'No se pudo guardar la imagen.'];
        }
        $rutaWeb = '/ACIDO/BACKPHP_TEST/public/img/perfiles/' . $nuevo;
        if (!$this->usuarioModel->actualizarFoto($idUsuario, $rutaWeb)) {
            @unlink($destFs);
            return ['ok' => false, 'message' => 'No se pudo guardar en la base de datos.'];
        }
        if (!empty($anterior) && strpos($anterior, '/public/img/perfiles/') !== false) {
            $oldFs = __DIR__ . '/../' . ltrim(preg_replace('#^/ACIDO/BACKPHP_TEST/#', '', $anterior), '/');
            if (is_file($oldFs) && realpath($oldFs) !== realpath($destFs)) @unlink($oldFs);
        }
        return ['ok' => true, 'message' => 'Foto actualizada.', 'ruta' => $rutaWeb];
    }

    public function eliminarFoto($idUsuario) {
        if (!ctype_digit((string)$idUsuario)) {
            return ['ok' => false, 'message' => 'Sesión inválida.'];
        }
        $anterior = $this->usuarioModel->fotoActual($idUsuario);
        if (!$this->usuarioModel->actualizarFoto($idUsuario, null)) {
            return ['ok' => false, 'message' => 'No se pudo quitar la foto.'];
        }
        if (!empty($anterior) && strpos($anterior, '/public/img/perfiles/') !== false) {
            $oldFs = __DIR__ . '/../' . ltrim(preg_replace('#^/ACIDO/BACKPHP_TEST/#', '', $anterior), '/');
            if (is_file($oldFs)) @unlink($oldFs);
        }
        return ['ok' => true, 'message' => 'Foto eliminada. Ahora se muestra tu inicial.'];
    }
}
?>
