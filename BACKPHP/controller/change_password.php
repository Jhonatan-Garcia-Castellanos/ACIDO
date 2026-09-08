<?php
// BACKPHP/controller/change_password.php
require_once __DIR__ . '/../model/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura y limpieza de entradas desde el $_POST de la vista
    $email = trim($_POST['email'] ?? '');
    $actualPassword = $_POST['actual_password'] ?? '';
    $nuevaPassword = $_POST['nueva_password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';

    // Validar que no existan campos vacíos
    if (empty($email) || empty($actualPassword) || empty($nuevaPassword) || empty($confirmarPassword)) {
        header("Location: ../../view/change_password.php?error=" . urlencode("Todos los campos son obligatorios."));
        exit();
    }

    // Validar que la nueva contraseña coincida con su confirmación
    if ($nuevaPassword !== $confirmarPassword) {
        header("Location: ../../view/change_password.php?error=" . urlencode("Las nuevas contraseñas no coinciden."));
        exit();
    }

    // Instancia del modelo y ejecución del cambio en MySQL
    $usuarioModel = new Usuario();
    $resultado = $usuarioModel->cambiarPassword($email, $actualPassword, $nuevaPassword);

    if ($resultado['status']) {
        // Redirección exitosa a la pantalla de login con parámetro status
        header("Location: ../../view/login.php?status=password_updated");
    } else {
        // Redirección en caso de error (correo no registrado o clave actual errónea)
        header("Location: ../../view/change_password.php?error=" . urlencode($resultado['message']));
    }
    exit();
}
?>