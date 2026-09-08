<?php
require_once "controller/UsuarioController.php";

session_start();
$controller = new UsuarioController();

// 1. PROCESAR FORMULARIOS (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {

    if ($_POST["action"] === "register") {
        $email = trim($_POST["email"] ?? $_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';

        if (empty($email)) {
            header("Location: index.php?action=register&error=empty_email");
            exit();
        }

        if ($controller->registrar($email, $password)) {
            header("Location: index.php?action=login&status=success_register");
            exit();
        } else {
            header("Location: index.php?action=register&error=register_failed");
            exit();
        }
    }

    if ($_POST["action"] === "login") {
        $email = trim($_POST["email"] ?? $_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';

        $user = $controller->login($email, $password);

        if ($user) {
            $_SESSION["user"] = $user;
            // CAMBIO AQUÍ: Redirigir explícitamente a action=dashboard
            header("Location: index.php?action=dashboard");
            exit();
        } else {
            header("Location: index.php?action=login&error=invalid_credentials");
            exit();
        }
    }
}

// 2. PROCESAR ACCIONES POR URL (GET)
if (isset($_GET["action"])) {

    if ($_GET["action"] === "logout") {
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }

    if ($_GET["action"] === "login") {
        require_once "view/login.php";
        exit();
    }

    if ($_GET["action"] === "register") {
        require_once "view/register.php";
        exit();
    }

    // CAMBIO AQUÍ: Cargar dashboard si se pide por URL y hay sesión
    if ($_GET["action"] === "dashboard") {
        if (isset($_SESSION["user"])) {
            require_once "view/dashboard.php";
            exit();
        } else {
            header("Location: index.php?action=login");
            exit();
        }
    }

    if ($_GET["action"] === "crud") {
        if (isset($_SESSION["user"])) {
            require_once "view/crud.php";
            exit();
        } else {
            header("Location: index.php?action=login");
            exit();
        }
    }
}

// 3. CARGAR VISTA POR DEFECTO
// Si el usuario entra a index.php sin parámetros y tiene sesión, redirigir a action=dashboard
if (isset($_SESSION["user"])) {
    header("Location: index.php?action=dashboard");
    exit();
} else {
    header("Location: index.php?action=login");
    exit();
}
?>