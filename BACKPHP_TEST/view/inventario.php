<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["user"])) {
    header("Location: index.php?action=login");
    exit();
}
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
$__canEdit = in_array($__rol, ['Administrador', 'Empleado'], true);
if (!isset($productos)) { $productos = []; }
if (!isset($categorias)) { $categorias = []; }
if (!isset($proveedores)) { $proveedores = []; }
if (!isset($resumen)) { $resumen = ['valorizacion'=>0,'agotados'=>0,'stock_bajo'=>0]; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - ÁCIDO COLOMBIA</title>
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/crud.css?v=<?php echo time(); ?>">
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
            <?php if (in_array($__rol, ['Administrador','Empleado'], true)): ?>
            <a href="index.php?action=crud" class="nav-item">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-text">Usuarios</span>
            </a>
            <?php endif; ?>
            <a href="index.php?action=inventario" class="nav-item active">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="sidebar-text">Inventario</span>
            </a>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <input type="text" id="invSearch" placeholder="Buscar producto...">
                    <button><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
                <div class="topbar-user">
                    <div class="divider-vertical"></div>
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span><?php echo htmlspecialchars($_SESSION["user"]["nombre"] ?? $_SESSION["user"]["Email"] ?? 'Usuario'); ?> (<?php echo htmlspecialchars($__rol); ?>)</span>
                            <div class="avatar"></div>
                        </div>
                        <div class="dropdown-menu-user" id="userDropdownMenu">
                            <a href="index.php?action=logout" class="dropdown-user-item text-danger">
                                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <div class="content-padding-crud">
                <div class="page-header">
                    <h2 id="form-title-text">INVENTARIO</h2>
                </div>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'save_fail'): ?>
                    <div style="background:#fed7d7;color:#9b2c2c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;">
                        No se pudo guardar. Verifica precio &gt; 0, stock ≥ 0 y categoría/proveedor válidos.
                    </div>
                <?php endif; ?>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-bottom:20px;">
                    <div class="crud-modern-card" style="margin:0;padding:16px;">
                        <small style="color:#4a5568;font-weight:700;">VALORIZACIÓN STOCK</small>
                        <div style="font-size:22px;font-weight:800;">$<?php echo number_format($resumen['valorizacion'] ?? 0, 0, ',', '.'); ?></div>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;">
                        <small style="color:#4a5568;font-weight:700;">AGOTADOS (stock 0)</small>
                        <div style="font-size:22px;font-weight:800;"><?php echo (int)($resumen['agotados'] ?? 0); ?></div>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;">
                        <small style="color:#4a5568;font-weight:700;">STOCK BAJO (&lt;10)</small>
                        <div style="font-size:22px;font-weight:800;"><?php echo (int)($resumen['stock_bajo'] ?? 0); ?></div>
                    </div>
                </div>
                <?php if ($__canEdit): ?>
                <div class="crud-container-box">
                    <div class="crud-modern-card" style="margin-top: 0;">
                        <div class="crud-modern-header">
                            <i class="fa-solid fa-box-open"></i>
                            <span id="form-card-title">Formulario de Producto</span>
                        </div>
                        <div class="crud-form-body">
                            <?php if (empty($categorias) || empty($proveedores)): ?>
                                <div style="background:#fefcbf;color:#744210;padding:12px;border-radius:8px;font-size:12px;">
                                    Primero crea al menos 1 categoría y 1 proveedor en la DB para poder guardar productos.
                                    (Tablas <code>categoria</code> y <code>proveedor</code> vacías)
                                </div>
                            <?php endif; ?>
                            <form action="index.php?action=inventario" method="POST">
                                <input type="hidden" name="inv_action" value="save">
                                <input type="hidden" name="ID_Producto" id="form-id">
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;gap:12px;align-items:start;">
                                    <div>
                                        <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">NOMBRE</label>
                                        <input type="text" class="crud-input" name="Nombre_Producto" id="form-nombre" placeholder="Nombre producto" required>
                                    </div>
                                    <div>
                                        <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">PRECIO (&gt;0)</label>
                                        <input type="number" step="0.01" min="1" class="crud-input" name="Precio_Actual" id="form-precio" required>
                                    </div>
                                    <div>
                                        <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">STOCK (≥0)</label>
                                        <input type="number" min="0" class="crud-input" name="Stock_Actual" id="form-stock" value="0" required>
                                    </div>
                                    <div>
                                        <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">CATEGORÍA</label>
                                        <select class="crud-input" name="ID_Categoria" id="form-cat" required>
                                            <?php foreach ($categorias as $c): ?>
                                                <option value="<?php echo htmlspecialchars($c['ID_Categoria']); ?>"><?php echo htmlspecialchars($c['Nombre_Categoria']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">PROVEEDOR</label>
                                        <select class="crud-input" name="ID_Proveedor" id="form-prov" required>
                                            <?php foreach ($proveedores as $p): ?>
                                                <option value="<?php echo htmlspecialchars($p['ID_Proveedor']); ?>"><?php echo htmlspecialchars($p['Nombre_Empresa']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div style="padding-top:24px;display:flex;gap:8px;">
                                        <button type="submit" class="btn-crud-save" id="btn-submit-text">Guardar</button>
                                        <button type="button" class="btn-crud-cancel" id="btn-cancelar" onclick="limpiarFormulario()" style="display:none;">Cancelar</button>
                                    </div>
                                </div>
                                <small style="color:#a0aec0;display:block;margin-top:12px;font-size:11px;">* El borrado está bloqueado por trigger <code>trg_bloquear_borrado_producto</code>. Para descontinuar deja stock en 0.</small>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header">
                        <i class="fa-solid fa-table-list"></i>
                        <span>Productos (<?php echo count($productos); ?>)</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="crud-table" id="invTable">
                            <thead>
                                <tr>
                                    <th><b>ID</b></th>
                                    <th><b>Producto</b></th>
                                    <th><b>Precio</b></th>
                                    <th><b>Stock</b></th>
                                    <th><b>Categoría</b></th>
                                    <th><b>Proveedor</b></th>
                                    <?php if ($__canEdit): ?><th style="text-align:right;"><b>Acciones</b></th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($productos)): ?>
                                    <?php foreach ($productos as $row): ?>
                                        <tr>
                                            <td style="font-weight:bold;"><?php echo htmlspecialchars($row['ID_Producto']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['Nombre_Producto']); ?></strong></td>
                                            <td>$<?php echo number_format($row['Precio_Actual'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php $st = (int)$row['Stock_Actual']; ?>
                                                <span style="background:<?php echo $st==0?'#fed7d7':($st<10?'#fefcbf':'#c6f6d5'); ?>;padding:4px 8px;border-radius:4px;"><?php echo $st; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['Nombre_Categoria'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Proveedor'] ?? '-'); ?></td>
                                            <?php if ($__canEdit): ?>
                                            <td style="text-align:right;">
                                                <button type="button" class="btn-action-edit"
                                                    onclick="editarProducto('<?php echo $row['ID_Producto']; ?>','<?php echo htmlspecialchars($row['Nombre_Producto'], ENT_QUOTES); ?>','<?php echo $row['Precio_Actual']; ?>','<?php echo $row['Stock_Actual']; ?>','<?php echo $row['ID_Categoria']; ?>','<?php echo $row['ID_Proveedor']; ?>')">
                                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                                </button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">No hay productos. Crea categorías/proveedores y luego el primer producto.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        if (toggleSidebarBtn && sidebar) {
            toggleSidebarBtn.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
        }
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        if (userMenuBtn && userDropdownMenu) {
            userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdownMenu.classList.toggle('show'); });
            document.addEventListener('click', (e) => {
                if (!userDropdownMenu.contains(e.target) && !userMenuBtn.contains(e.target)) userDropdownMenu.classList.remove('show');
            });
        }
        function editarProducto(id, nombre, precio, stock, cat, prov) {
            document.getElementById('form-id').value = id;
            document.getElementById('form-nombre').value = nombre;
            document.getElementById('form-precio').value = precio;
            document.getElementById('form-stock').value = stock;
            document.getElementById('form-cat').value = cat;
            document.getElementById('form-prov').value = prov;
            document.getElementById('form-card-title').innerText = "Editando: " + nombre;
            document.getElementById('btn-submit-text').innerText = "Actualizar";
            document.getElementById('btn-cancelar').style.display = 'inline-block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        function limpiarFormulario() {
            document.getElementById('form-id').value = '';
            document.getElementById('form-nombre').value = '';
            document.getElementById('form-precio').value = '';
            document.getElementById('form-stock').value = '0';
            document.getElementById('form-card-title').innerText = "Formulario de Producto";
            document.getElementById('btn-submit-text').innerText = "Guardar";
            document.getElementById('btn-cancelar').style.display = 'none';
        }
        const invSearch = document.getElementById('invSearch');
        if (invSearch) {
            invSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('#invTable tbody tr').forEach(tr => {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>
