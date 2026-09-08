<?php
if (!isset($_SESSION["user"])) {
    header("Location: index.php?action=login");
    exit();
}

$host = 'localhost';
$dbname = 'plojecto';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 1. PROCESAR GUARDAR / ACTUALIZAR
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crud_action"]) && $_POST["crud_action"] === "save") {
    $original_email = $_POST['original_email'] ?? '';
    $email_input = trim($_POST['email'] ?? '');
    $pass_input = $_POST['password'] ?? '';

    // Extraer el nombre automáticamente para mantener consistencia con el registro
    $partes = explode('@', $email_input);
    $nombreAutomatico = ucfirst($partes[0]);

    if (!empty($original_email)) {
        // ACTUALIZAR USUARIO EXISTENTE
        if (!empty($pass_input)) {
            $hash = password_hash($pass_input, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuario SET email = ?, nombre = ?, password = ? WHERE email = ?");
            $stmt->execute([$email_input, $nombreAutomatico, $hash, $original_email]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuario SET email = ?, nombre = ? WHERE email = ?");
            $stmt->execute([$email_input, $nombreAutomatico, $original_email]);
        }
    } else {
        // INSERTAR NUEVO USUARIO
        if (!empty($email_input) && !empty($pass_input)) {
            $hash = password_hash($pass_input, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuario (email, nombre, password) VALUES (?, ?, ?)");
            $stmt->execute([$email_input, $nombreAutomatico, $hash]);
        }
    }
    header("Location: index.php?action=crud");
    exit();
}

// 2. PROCESAR ELIMINAR
if (isset($_GET["delete_email"])) {
    $email_delete = $_GET["delete_email"];
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE email = ?");
    $stmt->execute([$email_delete]);
    header("Location: index.php?action=crud");
    exit();
}

// 3. CONSULTAR REGISTROS (CORREGIDO: Se elimina ORDER BY id)
$stmt = $pdo->query("SELECT * FROM usuario");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Usuarios - ÁCIDO COLOMBIA</title>
    <link rel="stylesheet" href="/ACIDO/BACKPHP/public/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="dashboard-body crud-body">

    <div class="dashboard-container crud-container">

        <aside class="sidebar">
            <div class="sidebar-brand">
                <img src="/ACIDO/BACKPHP/public/LOGO2.png" class="brand-icon" alt="Logo Ácido Colombia">
                <span>ACIDO</span>
            </div>

            <hr class="sidebar-divider">

            <a href="index.php?action=dashboard" class="nav-item">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">INTERFACE</div>

            <a href="index.php?action=crud" class="nav-item active">
                <i class="fa-solid fa-gear"></i>
                <span>CRUD</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-wrench"></i>
                <span>Utilidades</span>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">COMPLEMENTOS</div>

            <div class="sidebar-dropdown open">
                <button type="button" class="nav-item dropdown-toggle" onclick="toggleSubmenu(this)">
                    <div class="nav-label">
                        <i class="fa-solid fa-folder"></i>
                        <span>Páginas</span>
                    </div>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </button>

                <div class="sidebar-submenu">
                    <a href="index.php?action=login">
                        <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión
                    </a>
                    <a href="index.php?action=register">
                        <i class="fa-solid fa-user-plus"></i> Registro
                    </a>
                    <a href="index.php?action=crud" class="active">
                        <i class="fa-solid fa-users"></i> Usuarios
                    </a>
                </div>
            </div>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-chart-area"></i>
                <span>Gráficas</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-table"></i>
                <span>Tablas</span>
            </a>

            <div class="sidebar-promo">
                <i class="fa-solid fa-rocket promo-icon"></i>
                <p><strong>ÁCIDO Pro</strong> incluye funciones avanzadas y componentes exclusivos.</p>
            </div>
        </aside>

        <main class="main-content">

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
                            <span><?php echo htmlspecialchars($_SESSION["user"]["nombre"] ?? $_SESSION["user"]["email"] ?? 'Usuario Demo'); ?></span>
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
                    <div class="crud-modern-card" style="margin-top: 0;">
                        <div class="crud-modern-header">
                            <i class="fa-solid fa-user-pen"></i>
                            <span id="form-card-title">Formulario de Registro de Usuario</span>
                        </div>
                        <div class="crud-form-body">
                            <form action="index.php?action=crud" method="POST">
                                <input type="hidden" name="crud_action" value="save">
                                <input type="hidden" name="original_email" id="form-original-email">

                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: center;">
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Correo
                                            Electrónico</label>
                                        <input type="email" class="crud-input" name="email" id="form-email"
                                            placeholder="ejemplo@correo.com" required>
                                    </div>
                                    <div>
                                        <label
                                            style="display: block; font-size: 11px; font-weight: 700; color: #4a5568; margin-bottom: 6px; text-transform: uppercase;">Contraseña</label>
                                        <input type="password" class="crud-input" name="password" id="form-password"
                                            placeholder="Ingrese la contraseña">
                                    </div>
                                    <div style="padding-top: 18px; display: flex; gap: 8px;">
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

                    <div class="crud-modern-card">
                        <div class="crud-modern-header">
                            <i class="fa-solid fa-table-list"></i>
                            <span>Usuarios Registrados en el Sistema</span>
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="crud-table">
                                <thead>
                                    <tr>
                                        <th><b>Correo</b></th>
                                        <th><b>Nombre</b></th>
                                        <th><b>Password (Hash)</b></th>
                                        <th style="text-align: right;"><b>Acciones</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($registros)): ?>
                                        <?php foreach ($registros as $row): ?>
                                            <tr>
                                                <td><strong
                                                        style="color: #1a202c;"><?php echo htmlspecialchars($row['email']); ?></strong>
                                                </td>
                                                <td style="color: #4a5568;">
                                                    <?php echo htmlspecialchars($row['nombre'] ?? ''); ?></td>
                                                <td><code
                                                        style="background: #edf2f7; padding: 4px 8px; border-radius: 4px; color: #4a5568; word-break: break-all; max-width: 200px; display: inline-block;"><?php echo htmlspecialchars(substr($row['password'], 0, 20) . '...'); ?></code>
                                                </td>
                                                <td style="text-align: right;">
                                                    <button type="button" class="btn-action-edit"
                                                        onclick="editarRegistro('<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>')"><i
                                                            class="fa-solid fa-pen-to-square"></i> Editar</button>
                                                    <a href="index.php?action=crud&delete_email=<?php echo urlencode($row['email']); ?>"
                                                        class="btn-action-delete"
                                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?');"><i
                                                            class="fa-solid fa-trash"></i> Eliminar</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4"
                                                style="text-align: center; color: #a0aec0; padding: 40px; font-style: italic;">
                                                No hay usuarios registrados actualmente.</td>
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
        function toggleSubmenu(button) {
            const dropdown = button.parentElement;
            dropdown.classList.toggle('open');
        }

        function editarRegistro(email) {
            document.getElementById('form-title-text').innerText = "MODIFICAR USUARIO";
            document.getElementById('form-card-title').innerText = "Editando a " + email;
            document.getElementById('form-original-email').value = email;
            document.getElementById('form-email').value = email;
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').placeholder = "Nueva contraseña (opcional)";
            document.getElementById('btn-submit-text').innerText = "Actualizar Cambios";
            document.getElementById('btn-cancelar').style.display = 'inline-block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function limpiarFormulario() {
            document.getElementById('form-title-text').innerText = "GESTIÓN DE USUARIOS";
            document.getElementById('form-card-title').innerText = "Formulario de Registro de Usuario";
            document.getElementById('form-original-email').value = '';
            document.getElementById('form-email').value = '';
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').placeholder = "Ingrese la contraseña";
            document.getElementById('btn-submit-text').innerText = "Guardar";
            document.getElementById('btn-cancelar').style.display = 'none';
        }

        // Función para abrir/cerrar submenús del sidebar
        function toggleSubmenu(button) {
            const dropdown = button.parentElement;
            dropdown.classList.toggle('open');
        }

        // Función para editar un registro en el CRUD
        function editarRegistro(email) {
            document.getElementById('form-title-text').innerText = "MODIFICAR USUARIO";
            document.getElementById('form-card-title').innerText = "Editando a " + email;
            document.getElementById('form-original-email').value = email;
            document.getElementById('form-email').value = email;
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').placeholder = "Nueva contraseña (opcional)";
            document.getElementById('btn-submit-text').innerText = "Actualizar Cambios";
            document.getElementById('btn-cancelar').style.display = 'inline-block';
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        // Función para limpiar el formulario del CRUD
        function limpiarFormulario() {
            document.getElementById('form-title-text').innerText = "GESTIÓN DE USUARIOS";
            document.getElementById('form-card-title').innerText = "Formulario de Registro de Usuario";
            document.getElementById('form-original-email').value = '';
            document.getElementById('form-email').value = '';
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').placeholder = "Ingrese la contraseña";
            document.getElementById('btn-submit-text').innerText = "Guardar";
            document.getElementById('btn-cancelar').style.display = 'none';
        }

        // --- MENÚ DESPLEGABLE DE USUARIO (TOPBAR) ---
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userMenuBtn && userDropdownMenu) {
            // Abrir o cerrar al hacer clic en el nombre/avatar
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });

            // Cerrar el menú si se hace clic en cualquier otro lado de la pantalla
            document.addEventListener('click', (e) => {
                if (!userDropdownMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                }
            });
        }
    </script>
</body>

</html>
