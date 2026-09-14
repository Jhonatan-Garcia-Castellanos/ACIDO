<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user"])) {
    header("Location: index.php?action=login");
    exit();
}
// Refresca rol desde DB por si te promovieron sin cerrar sesion
try {
    require_once __DIR__ . "/../model/Usuario.php";
    $__id = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
    if ($__id && ctype_digit((string)$__id)) {
        $__m = new Usuario();
        $__f = $__m->obtenerPorId($__id);
        if ($__f && isset($__f["Rol"])) {
            $_SESSION["user"]["Rol"] = $__f["Rol"];
            $_SESSION["user"]["rol"] = $__f["Rol"];
        }
    }
} catch (Exception $e) {}
$__rol = $_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente';
$__isAdmin = ($__rol === 'Administrador');
// Defensa en profundidad: CRUD = Admin+Empleado (index.php ya lo exige)
if (!in_array($__rol, ['Administrador', 'Empleado'], true)) {
    header("Location: index.php?action=dashboard&error=forbidden");
    exit();
}

// Vista pura MVC: sin conexión ni SQL.
// $registros lo provee index.php via UsuarioController->listar().
if (!isset($registros)) {
    $registros = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Usuarios - ÁCIDO COLOMBIA</title>
    <!-- Evita caché del archivo de estilos -->
    <!-- Cargar Tipografía y Alertas Globales -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <!-- Cargar Layout Base (Sidebar, Topbar y Scroll Interno) -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/layout.css?v=<?php echo time(); ?>">
    <!-- Cargar Tablas y Formulario del CRUD -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/crud.css?v=<?php echo time(); ?>">
    <!-- FontAwesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        window.addEventListener("pageshow", function (event) {
            if (event.persisted || (typeof window.performance != "undefined" && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
</head>

<body class="dashboard-body crud-body">

    <div class="dashboard-container crud-container">

        <!-- SIDEBAR COLAPSABLE -->
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-brand">
                <button type="button" id="toggleSidebar" class="toggle-btn" title="Contraer/Expandir Menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="brand-info sidebar-text">
                    <img src="/ACIDO/BACKPHP_TEST/public/img/LOGO2.png" class="brand-icon" alt="Logo Ácido Colombia">
                    <span class="brand-name">ACIDO</span>
                </div>
            </div>

            <hr class="sidebar-divider">

            <a href="index.php?action=dashboard" class="nav-item">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading sidebar-text">TIENDA</div>
            <a href="index.php?action=catalogo" class="nav-item">
                <i class="fa-solid fa-store"></i>
                <span class="sidebar-text">Catálogo</span>
            </a>
            <a href="index.php?action=carrito" class="nav-item">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-text">Carrito<?php $nc=array_sum($_SESSION['cart']??[]); if($nc>0) echo " ($nc)"; ?></span>
            </a>
            <a href="index.php?action=ventas" class="nav-item">
                <i class="fa-solid fa-receipt"></i>
                <span class="sidebar-text">Ventas</span>
            </a>
            <a href="index.php?action=pqr" class="nav-item">
                <i class="fa-solid fa-headset"></i>
                <span class="sidebar-text">PQR y Ayuda</span>
            </a>
            <div class="sidebar-heading sidebar-text" style="margin-top:12px;">GESTIÓN</div>
            <a href="index.php?action=inventario" class="nav-item">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="sidebar-text">Inventario</span>
            </a>
            <a href="index.php?action=crud" class="nav-item active">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-text">Usuarios</span>
            </a>
            <!-- Sidebar estándar -->
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">

            <!-- TOPBAR -->
            <header class="topbar">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar...">
                    <button><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>

                <div class="topbar-user">
                    <span id="liveClock" title="Hora del sistema" style="font-size:12px;font-weight:700;color:#fff;background:rgba(255,255,255,.15);padding:6px 10px;border-radius:6px;">--:--:--</span>
                    <?php require_once __DIR__ . "/partials/stock_bell.php"; ?>
                    <a href="index.php?action=carrito" class="icon-badge" title="Mi carrito" style="text-decoration:none;color:inherit;">
                        <i class="fa-solid fa-cart-shopping" style="color:#fff;"></i>
                        <?php $ncartTop=array_sum($_SESSION['cart']??[]); if($ncartTop>0): ?><span class="badge red"><?php echo $ncartTop; ?></span><?php endif; ?>
                    </a>
                    <div class="divider-vertical"></div>
                    <!-- Dropdown de Usuario -->
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span class="user-badge"><strong><?php echo htmlspecialchars($_SESSION["user"]["nombre_completo"] ?? $_SESSION["user"]["nombre"] ?? $_SESSION["user"]["Email"] ?? $_SESSION["user"]["email"] ?? 'Usuario Demo'); ?></strong><small><?php echo htmlspecialchars($_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente'); ?></small></span>
                            <?php $___avNb = $_SESSION["user"]["nombre_completo"] ?? $_SESSION["user"]["nombre"] ?? 'U'; $___avNbT = trim((string)$___avNb); $___avIni = $___avNbT !== '' ? (function_exists('mb_strtoupper') ? mb_strtoupper(mb_substr($___avNbT, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr($___avNbT, 0, 1))) : 'U'; $___avFoto = $_SESSION["user"]["foto"] ?? $_SESSION["user"]["Foto"] ?? null; ?>
                            <div class="avatar" title="<?php echo htmlspecialchars($___avNbT); ?>"><?php if (!empty($___avFoto)): ?><img src="<?php echo htmlspecialchars($___avFoto); ?>" alt="Foto de perfil"><?php else: ?><?php echo htmlspecialchars($___avIni); ?><?php endif; ?></div>
                        </div>
                        <div class="dropdown-menu-user" id="userDropdownMenu">
                            <a href="index.php?action=profile" class="dropdown-user-item">
                                <i class="fa-solid fa-user"></i> Ver Perfil
                            </a>
                            <a href="index.php?action=config" class="dropdown-user-item">
                                <i class="fa-solid fa-gear"></i> Configuración
                            </a>
                            <div class="dropdown-user-divider"></div>
                            <a href="index.php?action=logout" class="dropdown-user-item text-danger">
                                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-padding-crud">

                <div class="page-header">
                    <h2 id="form-title-text">GESTIÓN DE USUARIOS</h2>
                </div>
                <?php $flashErr = $_GET['error'] ?? ''; $flashOk = $_GET['status'] ?? ''; ?>
                <?php if ($flashErr !== ''): ?><div class="alert-msg alert-error" style="display:block;"><?php echo htmlspecialchars($flashErr); ?></div><?php endif; ?>
                <?php if ($flashOk !== ''): ?><div class="alert-msg alert-success" style="display:block;"><?php echo htmlspecialchars($flashOk); ?></div><?php endif; ?>

                <div class="crud-container-box">
                    <!-- Formulario de Registro -->
                    <div class="crud-modern-card" style="margin-top: 0;">
                        <div class="crud-modern-header">
                            <i class="fa-solid fa-user-pen"></i>
                            <span id="form-card-title">Formulario de Registro de Usuario</span>
                        </div>
                        <div class="crud-form-body">
                            <form action="index.php?action=crud" method="POST">
                                <input type="hidden" name="crud_action" value="save">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="hidden" name="id" id="form-id">

                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: start;">
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Correo
                                            Electrónico</label>
                                        <input type="email" class="crud-input" name="email" id="form-email"
                                            placeholder="Correo Electrónico" required>
                                    </div>
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Contraseña</label>
                                        <input type="password" class="crud-input" name="password" id="form-password"
                                            placeholder="Ingrese la contraseña">
                                    </div>
                                    <?php if ($__isAdmin): ?>
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Rol (DB.sql)</label>
                                        <select class="crud-input" name="rol" id="form-rol">
                                            <option value="Cliente">Cliente</option>
                                            <option value="Empleado">Empleado</option>
                                            <option value="Administrador">Administrador</option>
                                        </select>
                                    </div>
                                    <?php else: ?>
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Rol</label>
                                        <input type="text" class="crud-input" value="Bloqueado (solo Admin)" disabled title="Empleado no puede cambiar roles">
                                        <input type="hidden" name="rol" id="form-rol" value="Cliente">
                                    </div>
                                    <?php endif; ?>
                                    <div style="padding-top: 24px; display: flex; gap: 8px; align-self: start;">
                                        <button type="submit" class="btn-crud-save"
                                            id="btn-submit-text">Guardar</button>
                                        <button type="button" class="btn-crud-cancel" id="btn-cancelar"
                                            onclick="limpiarFormulario()" style="display: none;">Cancelar</button>
                                    </div>
                                </div>
                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; align-items: start; margin-top: 15px;">
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Documento
                                            (6-12 dígitos)</label>
                                        <input type="text" class="crud-input" name="documento" id="form-documento"
                                            placeholder="Documento" inputmode="numeric" maxlength="12">
                                    </div>
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Nombres
                                            (máx. 70)</label>
                                        <input type="text" class="crud-input" name="nombres" id="form-nombres"
                                            placeholder="Nombres" maxlength="70">
                                    </div>
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Apellidos
                                            (máx. 70)</label>
                                        <input type="text" class="crud-input" name="apellidos" id="form-apellidos"
                                            placeholder="Apellidos" maxlength="70">
                                    </div>
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Teléfono
                                            (7 o 10 dígitos)</label>
                                        <input type="text" class="crud-input" name="telefono" id="form-telefono"
                                            placeholder="Teléfono" inputmode="numeric" maxlength="10">
                                    </div>
                                </div>
                                <small style="color: #a0aec0; display: block; margin-top: 12px; font-size: 11px;">* Al
                                    editar, si dejas la contraseña en blanco, se mantendrá la contraseña cifrada
                                    actual. Para crear con datos personales, completa los 4 campos (documento, nombres,
                                    apellidos y teléfono); al editar se guardan junto con la cuenta. La foto de perfil
                                    solo la cambia cada usuario desde su perfil.</small>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de Usuarios Registrados -->
                    <div class="crud-modern-card">
                        <div class="crud-modern-header">
                            <i class="fa-solid fa-table-list"></i>
                            <span>Usuarios Registrados (<?php echo (int)($crudTotal ?? count($registros)); ?>)</span>
                        </div>
                        <?php $crudPage = $crudPage ?? 1; $crudPer = $crudPer ?? 10; $crudQ = $crudQ ?? ''; $crudOrder = $crudOrder ?? 'ID_Usuario'; $crudDir = $crudDir ?? 'DESC'; $crudPages = $crudPages ?? 1;
                        $crudArrow = function($col) use ($crudOrder, $crudDir) { return ($crudOrder === $col) ? ($crudDir === 'ASC' ? ' ▲' : ' ▼') : ''; };
                        $crudLink = function($col) use ($crudOrder, $crudDir, $crudQ, $crudPer) { $nd = ($crudOrder === $col && $crudDir === 'DESC') ? 'ASC' : 'DESC'; return 'index.php?action=crud&' . http_build_query(['page' => 1, 'per' => $crudPer, 'q' => $crudQ, 'order' => $col, 'dir' => $nd]); }; ?>
                        <form method="GET" action="index.php" style="display:flex;gap:8px;padding:12px 16px;flex-wrap:wrap;align-items:center;">
                            <input type="hidden" name="action" value="crud">
                            <input type="text" name="q" placeholder="Buscar correo, nombre, documento... (LIKE)" class="crud-input" style="max-width:260px;" value="<?php echo htmlspecialchars($crudQ); ?>">
                            <select name="per" class="crud-input" style="max-width:90px;" onchange="this.form.submit()">
                                <?php foreach ([5,10,20,50] as $pp): ?><option value="<?php echo $pp; ?>" <?php echo ((int)$crudPer === $pp) ? 'selected' : ''; ?>><?php echo $pp; ?> / pág</option><?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-crud-save">Buscar</button>
                            <?php if ($crudQ !== ''): ?><a href="index.php?action=crud" class="btn-crud-cancel" style="text-decoration:none;">Limpiar</a><?php endif; ?>
                            <input type="text" id="crudSearch" placeholder="Filtrar en página..." class="crud-input" style="margin-left:auto;max-width:200px;">
                        </form>
                        <div style="overflow-x: auto;">
                            <table class="crud-table" id="crudTable">
                                <thead>
                                    <tr>
                                        <th><a href="<?php echo $crudLink('ID_Usuario'); ?>" style="color:inherit;text-decoration:none;"><b>ID_Usuario<?php echo $crudArrow('ID_Usuario'); ?></b></a></th>
                                        <th><a href="<?php echo $crudLink('Email'); ?>" style="color:inherit;text-decoration:none;"><b>Correo (Email)<?php echo $crudArrow('Email'); ?></b></a></th>
                                        <th><b>Nombre</b></th>
                                        <th><b>Documento</b></th>
                                        <th><b>Teléfono</b></th>
                                        <th><a href="<?php echo $crudLink('Rol'); ?>" style="color:inherit;text-decoration:none;"><b>Rol<?php echo $crudArrow('Rol'); ?></b></a></th>
                                        <th><b>Estado</b></th>
                                        <th style="text-align: right;"><b>Acciones</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($registros)): ?>
                                        <?php foreach ($registros as $row):
                                            $uid = $row['ID_Usuario'] ?? $row['id'] ?? '';
                                            $uemail = $row['Email'] ?? $row['email'] ?? '';
                                            $urol = $row['Rol'] ?? $row['rol'] ?? 'Cliente';
                                            $unombre = $row['nombre'] ?? explode('@', $uemail)[0] ?? '';
                                            $unom = $row['Nombres'] ?? $row['nombres'] ?? '';
                                            $uape = $row['Apellidos'] ?? $row['apellidos'] ?? '';
                                            $udoc = $row['Documento'] ?? '';
                                            $utel = $row['Telefono'] ?? '';
                                            $uactivo = (int)($row['Activo'] ?? 1);
                                            $selfId = $_SESSION["user"]["ID_Usuario"] ?? $_SESSION["user"]["id"] ?? null;
                                            $isSelf = ((string)$uid === (string)$selfId);
                                        ?>
                                            <tr>
                                                <td style="color: #4a5568; font-weight: bold;">
                                                    <?php echo htmlspecialchars($uid); ?>
                                                </td>
                                                <td>
                                                    <strong
                                                        style="color: #1a202c;"><?php echo htmlspecialchars($uemail); ?></strong>
                                                </td>
                                                <td style="color: #4a5568;">
                                                    <?php echo htmlspecialchars($unombre); ?>
                                                </td>
                                                <td style="color: #4a5568;">
                                                    <?php echo htmlspecialchars($udoc !== '' ? $udoc : '-'); ?>
                                                </td>
                                                <td style="color: #4a5568;">
                                                    <?php echo htmlspecialchars($utel !== '' ? $utel : '-'); ?>
                                                </td>
                                                <td>
                                                    <span
                                                        style="background: #edf2f7; padding: 4px 8px; border-radius: 4px; color: #4a5568;"><?php echo htmlspecialchars($urol); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($uactivo === 1): ?>
                                                        <span style="background:#c6f6d5;color:#22543d;padding:4px 8px;border-radius:4px;font-weight:700;">Activo</span>
                                                    <?php else: ?>
                                                        <span style="background:#fed7d7;color:#9b2c2c;padding:4px 8px;border-radius:4px;font-weight:700;">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align: right;">
                                                    <button type="button" class="btn-action-edit"
                                                        onclick="editarRegistro('<?php echo htmlspecialchars($uid, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($uemail, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($urol, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($unom, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($uape, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($utel, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($udoc, ENT_QUOTES); ?>')">
                                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                                    </button>
                                                    <?php if ($__isAdmin && !$isSelf): ?>
                                                    <?php if ($uactivo === 1): ?>
                                                    <form method="POST" action="index.php?action=crud" class="toggle-user-form" style="display:inline;">
                                                        <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                                        <input type="hidden" name="user_toggle" value="1">
                                                        <input type="hidden" name="toggle_id" value="<?php echo htmlspecialchars($uid, ENT_QUOTES); ?>">
                                                        <input type="hidden" name="estado" value="0">
                                                        <button type="button" class="btn-action-delete"
                                                            onclick="askToggleUser(this, 'Inactivar usuario', '¿Quieres inactivar a <?php echo htmlspecialchars(str_replace("'", "", $uemail), ENT_QUOTES); ?>? No se borra por términos legales, solo se desactiva el acceso.')">
                                                            <i class="fa-solid fa-ban"></i> Inactivar
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <form method="POST" action="index.php?action=crud" class="toggle-user-form" style="display:inline;">
                                                        <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                                        <input type="hidden" name="user_toggle" value="1">
                                                        <input type="hidden" name="toggle_id" value="<?php echo htmlspecialchars($uid, ENT_QUOTES); ?>">
                                                        <input type="hidden" name="estado" value="1">
                                                        <button type="button" class="btn-crud-save"
                                                            onclick="askToggleUser(this, 'Activar usuario', '¿Quieres reactivar a <?php echo htmlspecialchars(str_replace("'", "", $uemail), ENT_QUOTES); ?>? Volverá a tener acceso al sistema.')">
                                                            <i class="fa-solid fa-check"></i> Activar
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8"
                                                style="text-align: center; color: #a0aec0; padding: 40px; font-style: italic;">
                                                No hay usuarios registrados actualmente.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;justify-content:center;padding:14px;flex-wrap:wrap;">
                            <?php if ($crudPage > 1): ?><a href="index.php?action=crud&<?php echo http_build_query(['page' => $crudPage - 1, 'per' => $crudPer, 'q' => $crudQ, 'order' => $crudOrder, 'dir' => $crudDir]); ?>" class="btn-crud-cancel" style="text-decoration:none;">« Anterior</a><?php endif; ?>
                            <?php for ($p = max(1, $crudPage - 2); $p <= min($crudPages, $crudPage + 2); $p++): ?>
                                <a href="index.php?action=crud&<?php echo http_build_query(['page' => $p, 'per' => $crudPer, 'q' => $crudQ, 'order' => $crudOrder, 'dir' => $crudDir]); ?>" class="btn-crud-save" style="text-decoration:none;<?php echo ($p === (int)$crudPage) ? '' : 'opacity:.55;'; ?>"><?php echo $p; ?></a>
                            <?php endfor; ?>
                            <?php if ($crudPage < $crudPages): ?><a href="index.php?action=crud&<?php echo http_build_query(['page' => $crudPage + 1, 'per' => $crudPer, 'q' => $crudQ, 'order' => $crudOrder, 'dir' => $crudDir]); ?>" class="btn-crud-cancel" style="text-decoration:none;">Siguiente »</a><?php endif; ?>
                            <small style="color:#a0aec0;">Pág. <?php echo (int)$crudPage; ?> de <?php echo (int)$crudPages; ?> · <?php echo (int)$crudTotal; ?> registros (LIKE en BD)</small>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>

    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>
    <script>
        function editarRegistro(id, email, rol, nombres, apellidos, telefono, documento) {
            document.getElementById('form-title-text').innerText = "MODIFICAR USUARIO";
            document.getElementById('form-card-title').innerText = "Editando a " + email;
            document.getElementById('form-id').value = id;
            document.getElementById('form-email').value = email;
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').placeholder = "Nueva contraseña (opcional)";
            document.getElementById('form-documento').value = documento || '';
            document.getElementById('form-nombres').value = nombres || '';
            document.getElementById('form-apellidos').value = apellidos || '';
            document.getElementById('form-telefono').value = telefono || '';
            var rolEl = document.getElementById('form-rol');
            // Solo Admin tiene SELECT editable; Empleado tiene hidden bloqueado
            if (rolEl && rol && rolEl.tagName === 'SELECT') {
                rolEl.value = rol;
            }
            document.getElementById('btn-submit-text').innerText = "Actualizar Cambios";
            document.getElementById('btn-cancelar').style.display = 'inline-block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function limpiarFormulario() {
            document.getElementById('form-title-text').innerText = "GESTIÓN DE USUARIOS";
            document.getElementById('form-card-title').innerText = "Formulario de Registro de Usuario";
            document.getElementById('form-id').value = '';
            document.getElementById('form-email').value = '';
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').placeholder = "Ingrese la contraseña";
            document.getElementById('form-documento').value = '';
            document.getElementById('form-nombres').value = '';
            document.getElementById('form-apellidos').value = '';
            document.getElementById('form-telefono').value = '';
            var rolEl2 = document.getElementById('form-rol');
            if (rolEl2 && rolEl2.tagName === 'SELECT') {
                rolEl2.value = 'Cliente';
            }
            document.getElementById('btn-submit-text').innerText = "Guardar";
            document.getElementById('btn-cancelar').style.display = 'none';
        }

        // Modal personalizada ACIDO (inactivar/activar) - envía el form POST del botón
        let confirmForm = null;
        function askToggleUser(btn, title, message) {
            confirmForm = btn ? btn.closest('form') : null;
            document.getElementById('acidoModalTitle').innerText = title;
            document.getElementById('acidoModalMsg').innerText = message;
            var btn = document.getElementById('acidoModalConfirm');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar';
            btn.className = title.toLowerCase().includes('inactivar') ? 'btn-action-delete' : 'btn-crud-save';
            document.getElementById('acidoModal').style.display = 'flex';
        }
        function closeAcidoModal() {
            document.getElementById('acidoModal').style.display = 'none';
            confirmForm = null;
        }
        function confirmAcidoModal() {
            if (confirmForm) confirmForm.submit();
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAcidoModal();
        });

        // Buscador en vivo de usuarios
        const cs = document.getElementById('crudSearch');
        if (cs) cs.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#crudTable tbody tr').forEach(tr => {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
    <div id="acidoModal" style="display:none;position:fixed;inset:0;background:rgba(28,59,74,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)closeAcidoModal()">
        <div style="background:#fff;border-radius:14px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1C3B4A,#004BA0);color:#fff;padding:16px 20px;display:flex;align-items:center;gap:10px;">
                <img src="/ACIDO/BACKPHP_TEST/public/img/LOGO2.png" alt="ACIDO" style="width:28px;height:28px;object-fit:contain;background:#fff;border-radius:6px;padding:2px;">
                <strong id="acidoModalTitle" style="font-size:15px;">Confirmar</strong>
            </div>
            <div style="padding:20px;color:#2d3748;font-size:14px;line-height:1.5;">
                <p id="acidoModalMsg" style="margin:0;"></p>
            </div>
            <div style="padding:0 20px 20px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn-crud-cancel" onclick="closeAcidoModal()">Cancelar</button>
                <button type="button" id="acidoModalConfirm" class="btn-crud-save" onclick="confirmAcidoModal()">Confirmar</button>
            </div>
        </div>
    </div>
    <script src="/ACIDO/BACKPHP_TEST/public/js/notif-stock.js?v=<?php echo time(); ?>"></script>
</body>

</html>