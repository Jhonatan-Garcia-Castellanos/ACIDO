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

    public function registrar($email, $password) {
        return $this->usuarioModel->registrar($email, $password);
    }

    public function cambiarPassword($email, $actualPassword, $nuevaPassword) {
        return $this->usuarioModel->cambiarPassword($email, $actualPassword, $nuevaPassword);
    }

    public function listar() {
        return $this->usuarioModel->obtenerTodos();
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
            return $this->usuarioModel->actualizarPorId($id, $email, $password, $rol);
        }

        if (empty($password)) {
            return false;
        }

        return $this->usuarioModel->registrar($email, $password, $rol);
    }

    public function eliminar($id) {
        if (!ctype_digit((string)$id)) {
            return false;
        }
        return $this->usuarioModel->eliminarPorId($id);
    }
}
?>