<?php
require_once "controller/UsuarioController.php";
require_once "controller/ProductoController.php";
require_once "controller/VentaController.php";

session_start();
$controller = new UsuarioController();
$productoController = new ProductoController();
$ventaController = new VentaController();
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];

// =========================================================================
// RBAC - Control de acceso por rol (DB.sql: Cliente, Empleado, Administrador)
// Matriz central: cambia aqui que rol ve que vista.
// =========================================================================
$ACCESS = [
    'dashboard'   => ['Administrador', 'Empleado', 'Cliente'],
    'crud'        => ['Administrador', 'Empleado'], // Ver + editar usuarios
    'crud_delete' => ['Administrador'], // Solo Admin inactiva/activa usuarios (sin borrado)
    'inventario'  => ['Administrador', 'Empleado'], // Gestion stock (sin Cliente)
    'inventario_save' => ['Administrador', 'Empleado'], // Crear/editar stock
    'inventario_estado' => ['Administrador'], // Solo Admin inactiva/activa productos
    'catalogo'    => ['Administrador', 'Empleado', 'Cliente'], // Vitrina para Cliente
    'carrito'     => ['Administrador', 'Empleado', 'Cliente'],
    'ventas'      => ['Administrador', 'Empleado', 'Cliente'], // Admin/Empl ven todo, Cliente solo suyas
];

function currentRole() {
    return $_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente';
}
function isLogged() {
    return isset($_SESSION["user"]);
}
function requireLogin() {
    if (!isLogged()) {
        header("Location: index.php?action=login");
        exit();
    }
}
// Sincroniza el Rol de la sesion con la DB para que un UPDATE a Administrador aplique con solo recargar
function syncRoleFromDb() {
    global $controller;
    if (!isLogged()) return;
    $id = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
    if (empty($id) || !ctype_digit((string)$id)) return;
    try {
        $fresh = $controller->obtenerPorId($id);
        if ($fresh && isset($fresh["Rol"])) {
            $_SESSION["user"]["Rol"] = $fresh["Rol"];
            $_SESSION["user"]["rol"] = $fresh["Rol"];
            if (isset($fresh["Email"])) {
                $_SESSION["user"]["Email"] = $fresh["Email"];
                $_SESSION["user"]["email"] = $fresh["Email"];
            }
        }
    } catch (Exception $e) {}
}
function requireRole($vista) {
    global $ACCESS;
    requireLogin();
    syncRoleFromDb();
    $permitidos = $ACCESS[$vista] ?? [];
    if (!in_array(currentRole(), $permitidos, true)) {
        // 403: sin permiso -> al dashboard con aviso
        header("Location: index.php?action=dashboard&error=forbidden");
        exit();
    }
}

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
            session_regenerate_id(true);
            unset($usuario["Password_Hash"], $usuario["password"]);
            // Sesion ligera compatible con DB.sql: ID_Usuario, Email, Rol + alias
            $_SESSION["user"] = [
                "ID_Usuario" => $usuario["ID_Usuario"] ?? $usuario["id"] ?? null,
                "id" => $usuario["ID_Usuario"] ?? $usuario["id"] ?? null,
                "Email" => $usuario["Email"] ?? $usuario["email"] ?? $email,
                "email" => $usuario["Email"] ?? $usuario["email"] ?? $email,
                "Rol" => $usuario["Rol"] ?? $usuario["rol"] ?? "Cliente",
                "rol" => $usuario["Rol"] ?? $usuario["rol"] ?? "Cliente",
                "nombre" => $usuario["nombre"] ?? explode("@", $email)[0],
            ];

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

// 1b. CRUD: ver/editar = Admin+Empleado, eliminar = solo Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crud_action"]) && $_POST["crud_action"] === "save") {
    requireRole('crud');
    // Anti-escalada: solo Admin puede cambiar Rol. Empleado conserva el Rol original.
    if (currentRole() !== 'Administrador') {
        $targetId = $_POST['id'] ?? $_POST['ID_Usuario'] ?? '';
        if (!empty($targetId) && ctype_digit((string)$targetId)) {
            $orig = $controller->obtenerPorId($targetId);
            $_POST['rol'] = $orig['Rol'] ?? 'Cliente';
            $_POST['Rol'] = $orig['Rol'] ?? 'Cliente';
        } else {
            // Empleado no puede crear con rol distinto a Cliente
            $_POST['rol'] = 'Cliente';
            $_POST['Rol'] = 'Cliente';
        }
        // Empleado tampoco puede editar a un Administrador
        if (isset($orig['Rol']) && $orig['Rol'] === 'Administrador') {
            header("Location: index.php?action=crud&error=forbidden");
            exit();
        }
    }
    $controller->guardar($_POST);
    header("Location: index.php?action=crud");
    exit();
}

// 2. Inactivar/Activar usuario = Admin (Empleado no cambia estado). Sin borrado fisico por ley.
if (isset($_GET["toggle_id"])) {
    requireRole('crud_delete');
    $tid = $_GET["toggle_id"];
    $est = $_GET["estado"] ?? '0';
    if (ctype_digit((string)$tid) && ($est === '0' || $est === '1')) {
        // No permitir auto-inactivarse
        $selfId = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
        if ((string)$tid !== (string)$selfId) {
            $controller->cambiarEstado($tid, $est);
        }
    }
    header("Location: index.php?action=crud");
    exit();
}
// Compat: delete_id antiguo ahora inactiva (no borra)
if (isset($_GET["delete_id"])) {
    requireRole('crud_delete');
    $delId = $_GET["delete_id"];
    if (ctype_digit((string)$delId)) {
        $selfId = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
        if ((string)$delId !== (string)$selfId) {
            $controller->cambiarEstado($delId, 0);
        }
    }
    header("Location: index.php?action=crud");
    exit();
}

if (isset($_GET["action"])) {

    if ($_GET["action"] === "logout") {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
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
        requireRole('dashboard');
        require_once "view/dashboard.php";
        exit();
    }

    if ($_GET["action"] === "crud") {
        requireRole('crud');
        $registros = $controller->listar();
        require_once "view/crud.php";
        exit();
    }

    if ($_GET["action"] === "inventario") {
        // Guardar producto (solo Admin/Empleado)
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["inv_action"]) && $_POST["inv_action"] === "save") {
            requireRole('inventario_save');
            $ok = $productoController->guardar($_POST);
            if (!$ok) {
                header("Location: index.php?action=inventario&error=save_fail");
                exit();
            }
            header("Location: index.php?action=inventario");
            exit();
        }
        // Inactivar/Activar producto (solo Admin). Sin borrado fisico: trigger lo bloquea.
        if (isset($_GET["inv_toggle"])) {
            requireRole('inventario_estado');
            $pid = $_GET["inv_toggle"];
            $est = $_GET["estado"] ?? '0';
            if (ctype_digit((string)$pid) && ($est === '0' || $est === '1')) {
                $productoController->cambiarEstado($pid, $est);
            }
            header("Location: index.php?action=inventario");
            exit();
        }
        requireRole('inventario');
        $productos = $productoController->listar();
        $categorias = $productoController->categorias();
        $proveedores = $productoController->proveedores();
        $resumen = $productoController->resumen();
        require_once "view/inventario.php";
        exit();
    }

    if ($_GET["action"] === "catalogo") {
        requireRole('catalogo');
        // Agregar al carrito desde catálogo
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cart_action"]) && $_POST["cart_action"] === "add") {
            $ventaController->agregar($_POST["id"] ?? '', $_POST["qty"] ?? 1);
            header("Location: index.php?action=catalogo");
            exit();
        }
        $items = $productoController->catalogo();
        require_once "view/catalogo.php";
        exit();
    }

    if ($_GET["action"] === "carrito") {
        requireRole('carrito');
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cart_action"])) {
            if ($_POST["cart_action"] === "add") {
                $ventaController->agregar($_POST["id"] ?? '', $_POST["qty"] ?? 1);
                header("Location: index.php?action=carrito");
                exit();
            }
            if ($_POST["cart_action"] === "update") {
                $ventaController->actualizar($_POST["id"] ?? '', $_POST["qty"] ?? 1);
                header("Location: index.php?action=carrito");
                exit();
            }
            if ($_POST["cart_action"] === "remove") {
                $ventaController->quitar($_POST["id"] ?? '');
                header("Location: index.php?action=carrito");
                exit();
            }
            if ($_POST["cart_action"] === "checkout") {
                $uid = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
                $res = $ventaController->checkout($uid, $_POST["id_metodo"] ?? '');
                if (isset($res['ID_Venta'])) {
                    $url = "index.php?action=carrito&ok=" . $res['ID_Venta'];
                    if (!empty($res['factura'])) $url .= "&fac=" . urlencode($res['factura']);
                    header("Location: " . $url);
                    exit();
                }
                header("Location: index.php?action=carrito&error=" . urlencode($res['error'] ?? 'No se pudo comprar'));
                exit();
            }
        }
        $cartData = $ventaController->detalle();
        $metodos = $ventaController->metodos();
        require_once "view/carrito.php";
        exit();
    }

    if ($_GET["action"] === "ventas") {
        requireRole('ventas');
        $uid = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
        $ventas = $ventaController->listarPara(currentRole(), $uid);
        require_once "view/ventas.php";
        exit();
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