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
                // =========================================================================
                // AJUSTE REALIZADO: LIMPIEZA TOTAL DE BÚFER
                // =========================================================================
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
                exit();
            }
            header("Location: index.php?action=register&error=empty_email");
            exit();
        }

        // Expresión regular: entre 8 y 20 caracteres, una mayúscula, una minúscula, un número y un carácter especial
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@\$!%*?&._\-#])[A-Za-z\d@\$!%*?&._\-#]{8,20}$/';

        if (!preg_match($passwordRegex, $password)) {
            if ($isAjax) {
                // =========================================================================
                // AJUSTE REALIZADO: LIMPIEZA TOTAL DE BÚFER
                // =========================================================================
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'La contraseña debe tener entre 8 y 20 caracteres, incluir una mayúscula, una minúscula, un número y un carácter especial.'
                ]);
                exit();
            }
            header("Location: index.php?action=register&error=invalid_password");
            exit();
        }
        
        if ($controller->registrar($email, $password)) {
            if ($isAjax) {
                // =========================================================================
                // AJUSTE REALIZADO: LIMPIEZA TOTAL DE BÚFER
                // =========================================================================
                while (ob_get_level()) { ob_end_clean(); }
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
                // =========================================================================
                // AJUSTE REALIZADO: MENSAJE PERSONALIZADO SI EL USUARIO YA EXISTE + BÚFER
                // =========================================================================
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Este correo electrónico ya se encuentra registrado. Intenta iniciar sesión.'
                ]);
                exit();
            }
            header("Location: index.php?action=register&error=user_exists");
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
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => 'index.php?action=dashboard']);
                exit();
            }

            header("Location: index.php?action=dashboard");
            exit();
        } else {
            if ($isAjax) {
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
                exit();
            }

            header("Location: index.php?action=login&error=invalid_credentials");
            exit();
        }
    }
}

// cambio de contraseña 

// 11
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
    if ($_GET["action"] === "change_password") {
        require_once "view/change_password.php";
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
    header("Location: index.php?action=login");
    exit();
}
?>