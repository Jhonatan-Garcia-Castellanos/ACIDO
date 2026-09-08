<?php
// controller/UsuarioController.php
require_once "model/Usuario.php";

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
}
?>