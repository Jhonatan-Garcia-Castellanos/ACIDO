<?php
require_once "controller/UsuarioController.php";

$controller = new UsuarioController();

// 1. Enviamos un correo con formato válido para pasar la validación del Trigger de MySQL
$email = "nuevo_usuario@gmail.com";
$password = "clave_secreta";

// 2. Ejecutamos el registro
if ($controller->registrar($email, $password)) {
    echo "Usuario registrado correctamente. El nombre se extrajo automáticamente de tu correo.";
} else {
    echo "Error al registrar el usuario. Revisa la conexión o si el correo ya existe.";
}
?>