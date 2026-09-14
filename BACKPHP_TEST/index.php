<?php
require_once "controller/UsuarioController.php";
require_once "controller/ProductoController.php";
require_once "controller/VentaController.php";
require_once "controller/DashboardController.php";
require_once "controller/PasswordResetController.php";
require_once "controller/NotificacionController.php";
require_once "model/AlertaStock.php";
require_once "lib/Mailer.php";
require_once "lib/Csrf.php";

session_start();
$controller = new UsuarioController();
$productoController = new ProductoController();
$ventaController = new VentaController();
$dashboardController = new DashboardController();
$resetController = new PasswordResetController();
$notifController = new NotificacionController();
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];

// =========================================================================
// RBAC - Control de acceso por rol (DB.sql: Cliente, Empleado, Administrador)
// Matriz central: cambia aqui que rol ve que vista.
// =========================================================================
$ACCESS = [
    'dashboard'   => ['Administrador', 'Empleado'], // Cliente va directo a catálogo
    'crud'        => ['Administrador', 'Empleado'], // Ver + editar usuarios
    'crud_delete' => ['Administrador'], // Solo Admin inactiva/activa usuarios (sin borrado)
    'inventario'  => ['Administrador', 'Empleado'], // Gestion stock (sin Cliente)
    'inventario_save' => ['Administrador', 'Empleado'], // Crear/editar stock
    'inventario_estado' => ['Administrador'], // Solo Admin inactiva/activa productos
    'notificaciones' => ['Administrador', 'Empleado'], // Campana stock bajo RF 2.3
    'api_alertas' => ['Administrador', 'Empleado'], // Polling JSON campana
    'catalogo'    => ['Administrador', 'Empleado', 'Cliente'], // Vitrina para Cliente
    'carrito'     => ['Administrador', 'Empleado', 'Cliente'],
    'ventas'      => ['Administrador', 'Empleado', 'Cliente'], // Admin/Empl ven todo, Cliente solo suyas
];

function homeForRole($rol) {
    return ($rol === 'Cliente') ? 'index.php?action=catalogo' : 'index.php?action=dashboard';
}

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
// Sincroniza Rol + nombre/apellido de la sesion con la DB
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
            if (!empty($fresh["nombre"])) {
                $_SESSION["user"]["nombre"] = $fresh["nombre"];
                $_SESSION["user"]["nombre_completo"] = $fresh["nombre"];
            }
            foreach (["Nombres", "Apellidos", "nombres", "apellidos"] as $k) {
                if (isset($fresh[$k])) $_SESSION["user"][$k] = $fresh[$k];
            }
            if (array_key_exists("Foto", (array)$fresh) || array_key_exists("foto", (array)$fresh)) {
                $_SESSION["user"]["Foto"] = $fresh["Foto"] ?? $fresh["foto"] ?? null;
                $_SESSION["user"]["foto"] = $fresh["foto"] ?? $fresh["Foto"] ?? null;
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
        // 403: Cliente -> catálogo, resto -> dashboard
        $home = homeForRole(currentRole());
        $sep = (strpos($home, '?') !== false) ? '&' : '?';
        header("Location: " . $home . $sep . "error=forbidden");
        exit();
    }
}

// CSRF: valida el token en cada POST que cambia estado. Si falla, se rechaza
// sin ejecutar nada (el atacante no conoce este valor aunque use tu sesión).
function checkCsrf() {
    $ok = Csrf::validate($_POST['csrf_token'] ?? null);
    if ($ok) return;
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesión vencida. Recarga la página e intenta de nuevo.']);
        exit();
    }
    $back = $_GET['action'] ?? 'login';
    header("Location: index.php?action=" . urlencode($back) . "&error=" . urlencode("Sesión vencida. Recarga la página e intenta de nuevo."));
    exit();
}

// 1. PROCESAR FORMULARIOS (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {

    // Detectar si la petición viene por AJAX (Fetch desde JS)
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // A. REGISTRO DE USUARIO — RF 1.1: validaciones estrictas (documento, nombres 70, correo 80, tel 7/10, clave 8-20)
    if ($_POST["action"] === "register") {
        checkCsrf();
        $datosReg = [
            'email'     => trim($_POST["email"] ?? $_POST["username"] ?? ''),
            'password'  => $_POST["password"] ?? '',
            'documento' => trim($_POST["documento"] ?? ''),
            'nombres'   => trim($_POST["nombres"] ?? ''),
            'apellidos' => trim($_POST["apellidos"] ?? ''),
            'telefono'  => trim($_POST["telefono"] ?? ''),
            'seudonimo' => trim($_POST["seudonimo"] ?? $_POST["username_alias"] ?? ''),
        ];
        // Compat: formulario viejo solo traía email+password -> usa registro simple
        $esRegistroCompleto = ($datosReg['documento'] !== '' || $datosReg['nombres'] !== '' || $datosReg['telefono'] !== '');
        if ($esRegistroCompleto) {
            $res = $controller->registrarCompleto($datosReg);
            if ($res['ok']) {
                if ($isAjax) {
                    while (ob_get_level()) { ob_end_clean(); }
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => '¡Usuario registrado con éxito!', 'redirect' => 'index.php?action=login']);
                    exit();
                }
                header("Location: index.php?action=login&status=success_register");
                exit();
            }
            if ($isAjax) {
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $res['message']]);
                exit();
            }
            header("Location: index.php?action=register&error=" . urlencode($res['message']));
            exit();
        }
        $email = $datosReg['email'];
        $password = $datosReg['password'];

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
    
    // B. INICIO DE SESIÓN — RF 1.2 (correo/seudónimo) + RF 1.3 (genérico + bloqueo 5 intentos)
    elseif ($_POST["action"] === "login") {
        checkCsrf();
        $loginId = trim($_POST["email"] ?? $_POST["username"] ?? $_POST["login"] ?? '');
        $password = $_POST["password"] ?? '';

        $usuario = $controller->login($loginId, $password);

        if ($usuario) {
            session_regenerate_id(true);
            unset($usuario["Password_Hash"], $usuario["password"]);
            // Sesion con nombre + apellido reales y rol (el rol va debajo del nombre en la vista)
            $nomFull = $usuario["nombre"] ?? $usuario["nombre_completo"] ?? trim(($usuario["Nombres"] ?? "") . " " . ($usuario["Apellidos"] ?? ""));
            if ($nomFull === "") $nomFull = explode("@", $loginId)[0];
            $_SESSION["user"] = [
                "ID_Usuario" => $usuario["ID_Usuario"] ?? $usuario["id"] ?? null,
                "id" => $usuario["ID_Usuario"] ?? $usuario["id"] ?? null,
                "Email" => $usuario["Email"] ?? $usuario["email"] ?? $loginId,
                "email" => $usuario["Email"] ?? $usuario["email"] ?? $loginId,
                "Rol" => $usuario["Rol"] ?? $usuario["rol"] ?? "Cliente",
                "rol" => $usuario["Rol"] ?? $usuario["rol"] ?? "Cliente",
                "Nombres" => $usuario["Nombres"] ?? $usuario["nombres"] ?? "",
                "Apellidos" => $usuario["Apellidos"] ?? $usuario["apellidos"] ?? "",
                "nombres" => $usuario["Nombres"] ?? $usuario["nombres"] ?? "",
                "apellidos" => $usuario["Apellidos"] ?? $usuario["apellidos"] ?? "",
                "nombre" => $nomFull,
                "nombre_completo" => $nomFull,
                "Foto" => $usuario["Foto"] ?? $usuario["foto"] ?? null,
                "foto" => $usuario["foto"] ?? $usuario["Foto"] ?? null,
            ];

            $homeOk = homeForRole($_SESSION["user"]["Rol"] ?? 'Cliente');
            if ($isAjax) {
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => $homeOk]);
                exit();
            }

            header("Location: " . $homeOk);
            exit();
        } else {
            // RF 1.3: mensaje genérico + aviso de bloqueo temporal tras 5 intentos
            $bloqHasta = $controller->estaBloqueado($loginId);
            $msgFail = $bloqHasta
                ? 'Cuenta bloqueada temporalmente por 5 intentos fallidos. Intenta de nuevo después de las ' . $bloqHasta . '.'
                : 'usuario o contraseña incorrectos.';
            if ($isAjax) {
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msgFail, 'blocked' => (bool)$bloqHasta]);
                exit();
            }

            header("Location: index.php?action=login&error=invalid_credentials");
            exit();
        }
    }
}

// cambio de contraseña

// 1a. RECUPERACIÓN POR CORREO (público, sin login): solicita link y restablece con token
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reset_action"])) {
    if ($_POST["reset_action"] === "request") {
        checkCsrf();
        $r = $resetController->solicitar(trim($_POST["email"] ?? ''));
        // ?debug=1 muestra el link en pantalla cuando el SMTP aún no está configurado
        if (!$r['sent'] && !empty($r['link']) && isset($_GET['debug'])) {
            $debugLink = $r['link'];
            require_once "view/forgot_password.php";
            exit();
        }
        $sep = $r['sent'] ? 'info' : 'error';
        header("Location: index.php?action=forgot_password&" . $sep . "=" . urlencode($r['message']));
        exit();
    }
    if ($_POST["reset_action"] === "reset") {
        checkCsrf();
        $tok = $_POST["token"] ?? $_GET["token"] ?? '';
        $res = $resetController->restablecer($tok, $_POST["nueva_password"] ?? '', $_POST["confirmar_password"] ?? '');
        if ($res['ok']) {
            header("Location: index.php?action=login&status=password_updated");
        } else {
            header("Location: index.php?action=reset_password&token=" . urlencode($tok) . "&error=" . urlencode($res['message']));
        }
        exit();
    }
}

// 1b. FOTO DE PERFIL (requiere login): subir o quitar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["photo_action"])) {
    requireLogin();
    checkCsrf();
    $uidFoto = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
    if ($_POST["photo_action"] === "upload") {
        $r = $controller->subirFoto($uidFoto, $_FILES["foto"] ?? []);
        if (!empty($r['ok']) && !empty($r['ruta'])) {
            $_SESSION["user"]["foto"] = $r['ruta'];
            $_SESSION["user"]["Foto"] = $r['ruta'];
        }
        header("Location: index.php?action=profile&" . (!empty($r['ok']) ? "status" : "error") . "=" . urlencode($r['message']));
        exit();
    }
    if ($_POST["photo_action"] === "remove") {
        $r = $controller->eliminarFoto($uidFoto);
        if (!empty($r['ok'])) {
            unset($_SESSION["user"]["foto"], $_SESSION["user"]["Foto"]);
        }
        header("Location: index.php?action=profile&" . (!empty($r['ok']) ? "status" : "error") . "=" . urlencode($r['message']));
        exit();
    }
}

// 1b2. CAMBIO DE CONTRASEÑA LOGUEADO (front-controller; antes POST directo al controller)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pass_action"]) && $_POST["pass_action"] === "change") {
    requireLogin();
    checkCsrf();
    $email = trim($_POST['email'] ?? '');
    $actual = $_POST['actual_password'] ?? '';
    $nueva = $_POST['nueva_password'] ?? '';
    $conf = $_POST['confirmar_password'] ?? '';
    if (empty($email) || empty($actual) || empty($nueva) || empty($conf)) {
        header("Location: index.php?action=change_password&error=" . urlencode("Todos los campos son obligatorios."));
        exit();
    }
    if ($nueva !== $conf) {
        header("Location: index.php?action=change_password&error=" . urlencode("Las nuevas contraseñas no coinciden."));
        exit();
    }
    $resultado = $controller->cambiarPassword($email, $actual, $nueva);
    if (!empty($resultado['status'])) {
        header("Location: index.php?action=login&status=password_updated");
    } else {
        header("Location: index.php?action=change_password&error=" . urlencode($resultado['message'] ?? 'No se pudo cambiar la contraseña.'));
    }
    exit();
}

// 1b2b. MIS DATOS (perfil propio): el usuario edita documento/nombres/apellidos/teléfono.
// Seguridad: el id siempre es el de la sesión, se ignora cualquier id posteado.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["profile_action"]) && $_POST["profile_action"] === "save") {
    requireLogin();
    checkCsrf();
    $uidPropio = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
    $rp = $controller->actualizarPersona($uidPropio, $_POST);
    if (!empty($rp['ok'])) {
        syncRoleFromDb();
    }
    header("Location: index.php?action=profile&" . (!empty($rp['ok']) ? "status" : "error") . "=" . urlencode($rp['message'] ?? ''));
    exit();
}

// 1b. CRUD: ver/editar = Admin+Empleado, eliminar = solo Admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crud_action"]) && $_POST["crud_action"] === "save") {
    requireRole('crud');
    checkCsrf();
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
    $okSave = $controller->guardar($_POST);
    if (!$okSave) {
        header("Location: index.php?action=crud&error=" . urlencode("No se pudo guardar. Verifica el correo, documento único y los formatos."));
        exit();
    }
    header("Location: index.php?action=crud&status=" . urlencode("Usuario guardado."));
    exit();
}

// 2. Inactivar/Activar usuario = Admin vía POST+CSRF (el GET ya no ejecuta nada).
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["user_toggle"])) {
    requireRole('crud_delete');
    checkCsrf();
    $tid = $_POST["toggle_id"] ?? '';
    $est = $_POST["estado"] ?? '0';
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
// 2b. Alertas stock bajo: marcar leída(s) = Admin/Empleado vía POST+CSRF (RF 2.3).
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["alerta_action"])) {
    requireRole('notificaciones');
    checkCsrf();
    try {
        $al = new AlertaStock();
        if ($_POST["alerta_action"] === "leida" && ctype_digit((string)($_POST["id_alerta"] ?? ''))) {
            $al->marcarLeida($_POST["id_alerta"]);
        } elseif ($_POST["alerta_action"] === "todas") {
            $al->marcarTodas();
        }
    } catch (Exception $e) {}
    $back = $_POST["back"] ?? 'index.php?action=inventario';
    header("Location: " . $back);
    exit();
}
// Compat: toggle/delete por GET ya no ejecutan (antes era hueco CSRF) -> redirige sin cambios
if (isset($_GET["toggle_id"]) || isset($_GET["delete_id"])) {
    requireRole('crud_delete');
    header("Location: index.php?action=crud&error=" . urlencode("Acción no permitida por GET. Usa el botón del listado."));
    exit();
}

if (isset($_GET["action"])) {

    // ============ API JSON NOTIFICACIONES (solo Admin/Empleado) ============
    // GET api_alertas: campana stock bajo (polling 60s). RF 2.3.
    if ($_GET["action"] === "api_alertas") {
        requireLogin();
        syncRoleFromDb();
        if (!in_array(currentRole(), $ACCESS['api_alertas'] ?? [], true)) {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'count' => 0, 'items' => []]);
            exit();
        }
        $out = $notifController->resumen();
        // Email al admin SOLO por episodios nuevos (dedup 24h en sync).
        if (!empty($out['nuevas'])) {
            $em = $notifController->notificar('stock');
            $out['email'] = $em;
        }
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode($out);
        exit();
    }

    // GET api_kardex: ?limit=50 & ?producto=ID. Solo lectura. RF 2.7.
    if ($_GET["action"] === "api_kardex") {
        requireLogin();
        syncRoleFromDb();
        if (!in_array(currentRole(), $ACCESS['api_alertas'] ?? [], true)) {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'forbidden']);
            exit();
        }
        $lim = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $prod = $_GET['producto'] ?? null;
        $out = $notifController->kardex($lim, $prod);
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode($out);
        exit();
    }

    // GET api_notif_log: historial de correos (ok/fallo). Solo Admin/Empleado.
    if ($_GET["action"] === "api_notif_log") {
        requireLogin();
        syncRoleFromDb();
        if (!in_array(currentRole(), $ACCESS['api_alertas'] ?? [], true)) {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'forbidden']);
            exit();
        }
        $limLog = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $out = $notifController->historial($limLog);
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode($out);
        exit();
    }

    // POST api_alertas_leida: JSON o form {csrf_token, id_alerta} o {csrf_token, todas:true}.
    if ($_GET["action"] === "api_alertas_leida" && $_SERVER["REQUEST_METHOD"] === "POST") {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        if (!isLogged()) { echo json_encode(['success' => false, 'message' => 'login requerido']); exit(); }
        syncRoleFromDb();
        if (!in_array(currentRole(), $ACCESS['api_alertas'] ?? [], true)) {
            echo json_encode(['success' => false, 'message' => 'forbidden']); exit();
        }
        $body = $_POST;
        if (empty($body)) {
            $raw = file_get_contents('php://input');
            $j = json_decode((string)$raw, true);
            if (is_array($j)) $body = $j;
        }
        if (!Csrf::validate($body['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'CSRF inválido. Recarga e intenta de nuevo.']); exit();
        }
        $todas = !empty($body['todas']);
        $out = $notifController->marcar($body['id_alerta'] ?? null, $todas);
        echo json_encode($out);
        exit();
    }

    // POST api_notificar: JSON o form {csrf_token, tipo, datos}.
    // tipo: stock | stock_forzar | kardex | resumen. Envía correo al admin.
    if ($_GET["action"] === "api_notificar" && $_SERVER["REQUEST_METHOD"] === "POST") {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        if (!isLogged()) { echo json_encode(['success' => false, 'message' => 'login requerido']); exit(); }
        syncRoleFromDb();
        if (!in_array(currentRole(), $ACCESS['api_alertas'] ?? [], true)) {
            echo json_encode(['success' => false, 'message' => 'forbidden']); exit();
        }
        $body = $_POST;
        if (empty($body)) {
            $raw = file_get_contents('php://input');
            $j = json_decode((string)$raw, true);
            if (is_array($j)) $body = $j;
        }
        if (!Csrf::validate($body['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'CSRF inválido. Recarga e intenta de nuevo.']); exit();
        }
        $u = $_SESSION["user"] ?? [];
        $resp = $u["nombre_completo"] ?? $u["nombre"] ?? $u["Email"] ?? 'API';
        $datos = $body['datos'] ?? [];
        if (!is_array($datos)) $datos = [];
        $out = $notifController->notificar($body['tipo'] ?? '', $datos, $resp);
        echo json_encode($out);
        exit();
    }

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
    if ($_GET["action"] === "forgot_password") {
        require_once "view/forgot_password.php";
        exit();
    }
    if ($_GET["action"] === "reset_password") {
        $tok = $_GET["token"] ?? '';
        $row = $resetController->validarToken($tok);
        if (!$row) {
            header("Location: index.php?action=forgot_password&error=" . urlencode("Enlace inválido o vencido. Solicita uno nuevo."));
            exit();
        }
        $resetToken = $tok;
        $resetEmail = $row["Email"] ?? '';
        require_once "view/reset_password.php";
        exit();
    }

    if ($_GET["action"] === "register") {
        require_once "view/register.php";
        exit();
    }

    if ($_GET["action"] === "dashboard") {
        requireRole('dashboard');
        $dashData = $dashboardController->datos((int)($_GET['n'] ?? 6));
        try {
            $alDash = new AlertaStock();
            $nuevasDash = $alDash->sincronizarBajoMinimo();
            $alertCount = $alDash->contarNoLeidas();
            $alertItems = $alDash->listarNoLeidas(10);
            if (!empty($nuevasDash)) {
                try {
                    $admD = $alDash->emailsAdmins();
                    $mmD = new Mailer();
                    if (!empty($admD) && $mmD->isConfigured()) $mmD->enviarStockBajo($admD, $nuevasDash);
                } catch (Exception $eDash2) {}
            }
        } catch (Exception $e) { $alertCount = 0; $alertItems = []; }
        require_once "view/dashboard.php";
        exit();
    }

    // JSON para auto-actualización del dashboard (AJAX cada 30s, sin recargar).
    // ?n=3|6|12 controla el rango de la gráfica mensual.
    if ($_GET["action"] === "dashboard_data") {
        requireRole('dashboard');
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode($dashboardController->datos($_GET['n'] ?? 6));
        exit();
    }

    // Reporte de ventas en CSV (botón "Generar Reporte" del dashboard).
    if ($_GET["action"] === "reporte_csv") {
        requireRole('dashboard');
        $uidRep = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
        $filas = $ventaController->listarPara(currentRole(), $uidRep);
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM para que Excel muestre tildes
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID_Venta', 'Fecha', 'Cliente', 'Items', 'Total', 'Metodo', 'Factura'], ';');
        foreach ($filas as $f) {
            fputcsv($out, [
                $f['ID_Venta'] ?? '', $f['Fecha_Venta'] ?? '',
                $f['ClienteEmail'] ?? ('Cli ' . ($f['ID_Cliente'] ?? '')),
                $f['Items'] ?? 0, $f['Total'] ?? 0,
                $f['Metodo'] ?? '-', $f['Factura'] ?? '-',
            ], ';');
        }
        fclose($out);
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
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["inv_action"]) && $_POST["inv_action"] === "save") {            requireRole('inventario_save');
            checkCsrf();
            $ok = $productoController->guardar($_POST);
            if (!$ok) {
                header("Location: index.php?action=inventario&error=save_fail");
                exit();
            }
            header("Location: index.php?action=inventario");
            exit();
        }
        // Inactivar/Activar producto (solo Admin) vía POST+CSRF. Sin borrado fisico: trigger lo bloquea.
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["inv_toggle_id"])) {
            requireRole('inventario_estado');
            checkCsrf();
            $pid = $_POST["inv_toggle_id"];
            $est = $_POST["estado"] ?? '0';
            if (ctype_digit((string)$pid) && ($est === '0' || $est === '1')) {
                $productoController->cambiarEstado($pid, $est);
            }
            header("Location: index.php?action=inventario");
            exit();
        }
        // Ajuste manual Kardex (solo Admin, RF 2.9: Motivo obligatorio + auditoría).
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["inv_ajuste"])) {
            requireRole('inventario_estado');
            checkCsrf();
            $uidAj = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
            $res = $productoController->ajustar(
                $_POST["aj_producto"] ?? '',
                $_POST["aj_tipo"] ?? 'Ajuste',
                $_POST["aj_cantidad"] ?? 0,
                $_POST["aj_motivo"] ?? '',
                $uidAj
            );
            if (!empty($res['ok'])) {
                // Email al admin por movimiento Kardex (RF 2.7/2.9, best-effort).
                try {
                    $alK = new AlertaStock();
                    $admK = $alK->emailsAdmins();
                    $mmK = new Mailer();
                    if (!empty($admK) && $mmK->isConfigured()) {
                        $uK = $_SESSION["user"] ?? [];
                        $respK = $uK["nombre_completo"] ?? $uK["nombre"] ?? $uK["Email"] ?? 'Administrador';
                        $mmK->enviarMovimientoKardex($admK, [
                            'Producto' => $res['producto'] ?? ('ID ' . ($_POST["aj_producto"] ?? '')),
                            'Tipo' => $res['tipo'] ?? ($_POST["aj_tipo"] ?? 'Ajuste'),
                            'Cantidad' => (!empty($res['resta']) ? -1 : 1) * (int)($res['cantidad'] ?? $_POST["aj_cantidad"] ?? 0),
                            'Motivo' => $res['motivo'] ?? ($_POST["aj_motivo"] ?? ''),
                            'Responsable' => $respK,
                            'StockNuevo' => $res['stock_nuevo'] ?? null,
                            'StockMinimo' => $res['stock_minimo'] ?? null,
                        ]);
                    }
                } catch (Exception $eK) {}
                header("Location: index.php?action=inventario&status=" . urlencode($res['message']));
            } else {
                header("Location: index.php?action=inventario&error=" . urlencode($res['message'] ?? 'No se pudo registrar'));
            }
            exit();
        }
        // Compat: inv_toggle por GET ya no ejecuta (hueco CSRF) -> redirige sin cambios
        if (isset($_GET["inv_toggle"])) {
            requireRole('inventario_estado');
            header("Location: index.php?action=inventario&error=" . urlencode("Acción no permitida por GET. Usa el botón del listado."));
            exit();
        }
        requireRole('inventario');
        $productos = $productoController->listar();
        $categorias = $productoController->categorias();
        $proveedores = $productoController->proveedores();
        $resumen = $productoController->resumen();
        try {
            $alInv = new AlertaStock();
            $nuevasInv = $alInv->sincronizarBajoMinimo();
            $alertCount = $alInv->contarNoLeidas();
            $alertItems = $alInv->listarNoLeidas(10);
            $stockBajoLista = $alInv->listarStockBajo(50);
            if (!empty($nuevasInv)) {
                try {
                    $admI = $alInv->emailsAdmins();
                    $mmI = new Mailer();
                    if (!empty($admI) && $mmI->isConfigured()) $mmI->enviarStockBajo($admI, $nuevasInv);
                } catch (Exception $eInv2) {}
            }
        } catch (Exception $e) { $alertCount = 0; $alertItems = []; $stockBajoLista = []; }
        $filtroKardex = (isset($_GET['kardex_prod']) && ctype_digit((string)$_GET['kardex_prod'])) ? $_GET['kardex_prod'] : null;
        try { $kardex = $productoController->kardex(100, $filtroKardex); }
        catch (Exception $e) { $kardex = []; }
        try { $notifLog = $notifController->historial(20); $notifLog = $notifLog['items'] ?? []; }
        catch (Exception $e) { $notifLog = []; }
        require_once "view/inventario.php";
        exit();
    }

    if ($_GET["action"] === "catalogo") {
        requireRole('catalogo');
        // Agregar al carrito desde catálogo
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cart_action"]) && $_POST["cart_action"] === "add") {
            checkCsrf();
            $okAdd = $ventaController->agregar($_POST["id"] ?? '', $_POST["qty"] ?? 1);
            $isAjaxCat = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjaxCat) {
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                echo json_encode(['success' => (bool)$okAdd, 'cartCount' => $ventaController->contar()]);
                exit();
            }
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
            checkCsrf();
            $isAjaxCart = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            // Respuesta JSON para add/update/remove (el carrito se actualiza sin recargar)
            $cartJson = function () use ($ventaController) {
                while (ob_get_level()) { ob_end_clean(); }
                header('Content-Type: application/json');
                $d = $ventaController->detalle();
                $items = [];
                foreach ($d['items'] as $it) {
                    $items[(string)$it['ID_Producto']] = ['cantidad' => $it['cantidad'], 'subtotal' => $it['subtotal']];
                }
                echo json_encode(['success' => true, 'cartCount' => $ventaController->contar(), 'total' => $d['total'], 'items' => $items]);
                exit();
            };
            if ($_POST["cart_action"] === "add") {
                $ventaController->agregar($_POST["id"] ?? '', $_POST["qty"] ?? 1);
                if ($isAjaxCart) $cartJson();
                header("Location: index.php?action=carrito");
                exit();
            }
            if ($_POST["cart_action"] === "update") {
                $ventaController->actualizar($_POST["id"] ?? '', $_POST["qty"] ?? 1);
                if ($isAjaxCart) $cartJson();
                header("Location: index.php?action=carrito");
                exit();
            }
            if ($_POST["cart_action"] === "remove") {
                $ventaController->quitar($_POST["id"] ?? '');
                if ($isAjaxCart) $cartJson();
                header("Location: index.php?action=carrito");
                exit();
            }
            if ($_POST["cart_action"] === "checkout") {
                $uid = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
                $detallePago = [
                    'entidad'        => trim($_POST["entidad"] ?? ''),
                    'numero_tarjeta' => $_POST["numero_tarjeta"] ?? '',
                    'cuenta'         => $_POST["cuenta"] ?? '',
                ];
                $res = $ventaController->checkout($uid, $_POST["id_metodo"] ?? '', $detallePago);
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
        $resumenHoy = $ventaController->resumenHoyPara(currentRole(), $uid);
        $tabVentas = $_GET['tab'] ?? 'ventas';
        if (!in_array($tabVentas, ['ventas','pendientes','entregadas','facturas'], true)) $tabVentas = 'ventas';
        $pendientes = $ventaController->pendientesPara(currentRole(), $uid);
        $entregadas = $ventaController->entregadasPara(currentRole(), $uid);
        $facturas = $ventaController->facturasPara(currentRole(), $uid);
        // Detalle por venta se carga bajo demanda vía action=venta_detalle (AJAX).
        require_once "view/ventas.php";
        exit();
    }

    // Detalle de una venta en JSON (filas expandibles de ventas; respeta rol)
    if ($_GET["action"] === "venta_detalle") {
        requireRole('ventas');
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        $idV = $_GET['id'] ?? '';
        $out = ['success' => false, 'items' => []];
        if (ctype_digit((string)$idV)) {
            $uidDet = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
            $mine = array_map('intval', array_column($ventaController->listarPara(currentRole(), $uidDet), 'ID_Venta'));
            if (in_array((int)$idV, $mine, true)) {
                $out = ['success' => true, 'items' => $ventaController->detalleVenta($idV)];
            }
        }
        echo json_encode($out);
        exit();
    }

    if ($_GET["action"] === "profile") {
        requireLogin();
        syncRoleFromDb();
        $uidPerfil = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
        $perfilDetalle = $controller->datosPersona($uidPerfil);
        require_once "view/perfil.php";
        exit();
    }

    if ($_GET["action"] === "config") {
        requireLogin();
        syncRoleFromDb();
        require_once "view/config.php";
        exit();
    }
}

// 3. CARGAR VISTA POR DEFECTO (Cliente -> catálogo directo)
if (isset($_SESSION["user"])) {
    syncRoleFromDb();
    header("Location: " . homeForRole(currentRole()));
    exit();
} else {
    header("Location: index.php?action=login");
    exit();
}
?>