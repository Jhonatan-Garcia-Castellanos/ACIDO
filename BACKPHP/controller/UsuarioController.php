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
}
?>