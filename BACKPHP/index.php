<?php
require_once "controller/UsuarioController.php";

session_start();
$controller = new UsuarioController();

// 1. PROCESAR FORMULARIOS (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {

    // Detectar si la petición viene por AJAX (Fetch desde JS)
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // A. REGISTRO DE USUARIO
    if ($_POST["action"] === "register") {
        $email = trim($_POST["email"] ?? $_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';

        if (empty($email) || empty($password)) {
            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
                exit();
            }
            header("Location: index.php?action=register&error=empty_email");
            exit();
        }

        if ($controller->registrar($email, $password)) {
            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => '¡Usuario registrado con éxito!',
                    'redirect' => 'index.php?action=login'
                ]);
                exit();
            }
            header("Location: index.php?action=login&status=success_register");
            exit();
        } else {
            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'El correo ya existe o fallo el registro.']);
                exit();
            }
            header("Location: index.php?action=register&error=register_failed");
            exit();
        }
    } 
    
    // B. INICIO DE SESIÓN
    elseif ($_POST["action"] === "login") {
        $email = trim($_POST["email"] ?? $_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';

        $usuario = $controller->login($email, $password);

        if ($usuario) {
            $_SESSION["user"] = $usuario;

            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => 'index.php?action=dashboard']);
                exit();
            }

            header("Location: index.php?action=dashboard");
            exit();
        } else {
            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
                exit();
            }

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
if (isset($_SESSION["user"])) {
    header("Location: index.php?action=dashboard");
    exit();
} else {
    header("Location: index.php?action=login");
    exit();
}
?>
