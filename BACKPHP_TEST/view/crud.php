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

            <div class="sidebar-heading sidebar-text">INTERFACE</div>

            <a href="index.php?action=crud" class="nav-item active">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-text">Usuarios</span>
            </a>

            <a href="index.php?action=inventario" class="nav-item">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="sidebar-text">Inventario</span>
            </a>
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
                    <div class="icon-badge">
                        <i class="fa-solid fa-bell"></i>
                        <span class="badge red">3+</span>
                    </div>
                    <div class="icon-badge">
                        <i class="fa-solid fa-envelope"></i>
                        <span class="badge yellow">7</span>
                    </div>
                    <div class="divider-vertical"></div>

                    <!-- Dropdown de Usuario -->
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span><?php echo htmlspecialchars($_SESSION["user"]["nombre"] ?? $_SESSION["user"]["Email"] ?? $_SESSION["user"]["email"] ?? 'Usuario Demo'); ?> (<?php echo htmlspecialchars($_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente'); ?>)</span>
                            <div class="avatar"></div>
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
                                <small style="color: #a0aec0; display: block; margin-top: 12px; font-size: 11px;">* Al
                                    editar, si dejas la contraseña en blanco, se mantendrá la contraseña cifrada
                                    actual.</small>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de Usuarios Registrados -->
                    <div class="crud-modern-card">
                        <div class="crud-modern-header">
                            <i class="fa-solid fa-table-list"></i>
                            <span>Usuarios Registrados en el Sistema</span>
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="crud-table">
                                <thead>
                                    <tr>
                                        <th><b>ID_Usuario</b></th>
                                        <th><b>Correo (Email)</b></th>
                                        <th><b>Nombre</b></th>
                                        <th><b>Rol</b></th>
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
                                                <td>
                                                    <span
                                                        style="background: #edf2f7; padding: 4px 8px; border-radius: 4px; color: #4a5568;"><?php echo htmlspecialchars($urol); ?></span>
                                                </td>
                                                <td style="text-align: right;">
                                                    <button type="button" class="btn-action-edit"
                                                        onclick="editarRegistro('<?php echo htmlspecialchars($uid, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($uemail, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($urol, ENT_QUOTES); ?>')">
                                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                                    </button>
                                                    <?php if ($__isAdmin): ?>
                                                    <a href="index.php?action=crud&delete_id=<?php echo urlencode($uid); ?>"
                                                        class="btn-action-delete"
                                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
                                                        <i class="fa-solid fa-trash"></i> Eliminar
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5"
                                                style="text-align: center; color: #a0aec0; padding: 40px; font-style: italic;">
                                                No hay usuarios registrados actualmente.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>

    <script>
        // LÓGICA PARA COLAPSAR Y EXPANDIR EL SIDEBAR
        const sidebar = document.getElementById('sidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');

        if (toggleSidebarBtn && sidebar) {
            toggleSidebarBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }

        function toggleSubmenu(button) {
            const dropdown = button.parentElement;
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
            }
            dropdown.classList.toggle('open');
        }

        function editarRegistro(id, email, rol) {
            document.getElementById('form-title-text').innerText = "MODIFICAR USUARIO";
            document.getElementById('form-card-title').innerText = "Editando a " + email;
            document.getElementById('form-id').value = id;
            document.getElementById('form-email').value = email;
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').placeholder = "Nueva contraseña (opcional)";
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
            var rolEl2 = document.getElementById('form-rol');
            if (rolEl2 && rolEl2.tagName === 'SELECT') {
                rolEl2.value = 'Cliente';
            }
            document.getElementById('btn-submit-text').innerText = "Guardar";
            document.getElementById('btn-cancelar').style.display = 'none';
        }

        // Menú desplegable de usuario en la topbar
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userMenuBtn && userDropdownMenu) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!userDropdownMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                }
            });
        }
    </script>
</body>

</html>