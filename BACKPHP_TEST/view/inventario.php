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
            <?php if (in_array($__rol, ['Administrador','Empleado'], true)): ?>
            <div class="sidebar-heading sidebar-text" style="margin-top:12px;">GESTIÓN</div>
            <a href="index.php?action=inventario" class="nav-item active">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span class="sidebar-text">Inventario</span>
            </a>
            <a href="index.php?action=crud" class="nav-item">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-text">Usuarios</span>
            </a>
            <?php endif; ?>
            <!-- Sidebar estándar -->
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <input type="text" id="invSearch" placeholder="Buscar producto...">
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
                    <a href="index.php?action=carrito" class="icon-badge" title="Mi carrito" style="text-decoration:none;color:inherit;">
                        <i class="fa-solid fa-cart-shopping" style="color:#fff;"></i>
                        <?php $ncartTop=array_sum($_SESSION['cart']??[]); if($ncartTop>0): ?><span class="badge red"><?php echo $ncartTop; ?></span><?php endif; ?>
                    </a>
                    <div class="divider-vertical"></div>
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span><?php echo htmlspecialchars($_SESSION["user"]["nombre"] ?? $_SESSION["user"]["Email"] ?? 'Usuario'); ?> (<?php echo htmlspecialchars($__rol); ?>)</span>
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
                                <small style="color:#a0aec0;display:block;margin-top:12px;font-size:11px;">* Sin borrado fisico por trigger <code>trg_bloquear_borrado_producto</code> y terminos legales: solo inactivar/activar (Admin).</small>
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
                                    <th><b>Estado</b></th>
                                    <?php if ($__canEdit): ?><th style="text-align:right;"><b>Acciones</b></th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($productos)): ?>
                                    <?php foreach ($productos as $row):
                                        $pActivo = (int)($row['Activo'] ?? 1);
                                        $isAdminInv = ($__rol === 'Administrador');
                                    ?>
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
                                            <td>
                                                <?php if ($pActivo === 1): ?>
                                                    <span style="background:#c6f6d5;color:#22543d;padding:4px 8px;border-radius:4px;font-weight:700;">Activo</span>
                                                <?php else: ?>
                                                    <span style="background:#fed7d7;color:#9b2c2c;padding:4px 8px;border-radius:4px;font-weight:700;">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($__canEdit): ?>
                                            <td style="text-align:right;">
                                                <button type="button" class="btn-action-edit"
                                                    onclick="editarProducto('<?php echo $row['ID_Producto']; ?>','<?php echo htmlspecialchars($row['Nombre_Producto'], ENT_QUOTES); ?>','<?php echo $row['Precio_Actual']; ?>','<?php echo $row['Stock_Actual']; ?>','<?php echo $row['ID_Categoria']; ?>','<?php echo $row['ID_Proveedor']; ?>')">
                                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                                </button>
                                                <?php if ($isAdminInv): ?>
                                                <?php if ($pActivo === 1): ?>
                                                <button type="button" class="btn-action-delete"
                                                    onclick="askToggleInv('index.php?action=inventario&inv_toggle=<?php echo urlencode($row['ID_Producto']); ?>&estado=0', 'Inactivar producto', '¿Quieres inactivar <?php echo htmlspecialchars(str_replace("'", "", $row['Nombre_Producto']), ENT_QUOTES); ?>? No se borra por ley/trigger, solo se desactiva.')">
                                                    <i class="fa-solid fa-ban"></i> Inactivar
                                                </button>
                                                <?php else: ?>
                                                <button type="button" class="btn-crud-save"
                                                    onclick="askToggleInv('index.php?action=inventario&inv_toggle=<?php echo urlencode($row['ID_Producto']); ?>&estado=1', 'Activar producto', '¿Quieres reactivar <?php echo htmlspecialchars(str_replace("'", "", $row['Nombre_Producto']), ENT_QUOTES); ?>? Volverá a estar disponible.')">
                                                    <i class="fa-solid fa-check"></i> Activar
                                                </button>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">No hay productos. Crea categorías/proveedores y luego el primer producto.</td></tr>
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
        // Modal personalizada ACIDO (inactivar/activar) - sin "localhost dice"
        let confirmHrefInv = '';
        function askToggleInv(href, title, message) {
            confirmHrefInv = href;
            document.getElementById('acidoModalTitleInv').innerText = title;
            document.getElementById('acidoModalMsgInv').innerText = message;
            var btn = document.getElementById('acidoModalConfirmInv');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar';
            btn.className = title.toLowerCase().includes('inactivar') ? 'btn-action-delete' : 'btn-crud-save';
            document.getElementById('acidoModalInv').style.display = 'flex';
        }
        function closeAcidoModalInv() {
            document.getElementById('acidoModalInv').style.display = 'none';
            confirmHrefInv = '';
        }
        function confirmAcidoModalInv() {
            if (confirmHrefInv) window.location.href = confirmHrefInv;
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAcidoModalInv();
        });
    </script>
    <div id="acidoModalInv" style="display:none;position:fixed;inset:0;background:rgba(28,59,74,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)closeAcidoModalInv()">
        <div style="background:#fff;border-radius:14px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1C3B4A,#004BA0);color:#fff;padding:16px 20px;display:flex;align-items:center;gap:10px;">
                <img src="/ACIDO/BACKPHP_TEST/public/img/LOGO2.png" alt="ACIDO" style="width:28px;height:28px;object-fit:contain;background:#fff;border-radius:6px;padding:2px;">
                <strong id="acidoModalTitleInv" style="font-size:15px;">Confirmar</strong>
            </div>
            <div style="padding:20px;color:#2d3748;font-size:14px;line-height:1.5;">
                <p id="acidoModalMsgInv" style="margin:0;"></p>
            </div>
            <div style="padding:0 20px 20px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn-crud-cancel" onclick="closeAcidoModalInv()">Cancelar</button>
                <button type="button" id="acidoModalConfirmInv" class="btn-crud-save" onclick="confirmAcidoModalInv()">Confirmar</button>
            </div>
        </div>
    </div>
</body>
</html>
