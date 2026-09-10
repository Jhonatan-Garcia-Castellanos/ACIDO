<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["user"])) {
    header("Location: index.php?action=login");
    exit();
}
$__rol = $_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente';
if (!isset($items)) { $items = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - ÁCIDO COLOMBIA</title>
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/crud.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <?php if (in_array($__rol, ['Administrador','Empleado'], true)): ?>
            <a href="index.php?action=dashboard" class="nav-item">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <hr class="sidebar-divider">
            <?php endif; ?>
            <div class="sidebar-heading sidebar-text">TIENDA</div>
            <a href="index.php?action=catalogo" class="nav-item active">
                <i class="fa-solid fa-store"></i>
                <span class="sidebar-text">Catálogo</span>
            </a>
            <a href="index.php?action=carrito" class="nav-item">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-text">Carrito<?php $nc=array_sum($_SESSION['cart']??[]); if($nc>0) echo " ($nc)"; ?></span>
            </a>
            <a href="index.php?action=ventas" class="nav-item">
                <i class="fa-solid fa-receipt"></i>
                <span class="sidebar-text"><?php echo in_array($__rol,['Administrador','Empleado'],true)?'Ventas':'Mis compras'; ?></span>
            </a>
            <?php if (in_array($__rol, ['Administrador','Empleado'], true)): ?>
            <div class="sidebar-heading sidebar-text" style="margin-top:12px;">GESTIÓN</div>
            <a href="index.php?action=inventario" class="nav-item">
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
                    <input type="text" id="catSearch" placeholder="Buscar en catálogo...">
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
                    <h2>CATÁLOGO</h2>
                </div>
                <?php if (empty($items)): ?>
                    <div class="crud-modern-card" style="padding:40px;text-align:center;color:#a0aec0;font-style:italic;">
                        No hay productos disponibles por ahora.
                    </div>
                <?php else: ?>
                <div id="catGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
                    <?php foreach ($items as $it): ?>
                    <div class="crud-modern-card" style="margin:0;overflow:hidden;">
                        <div style="height:150px;background:#f8f9fc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            <?php if (!empty($it['Imagen_URL'])): ?>
                                <img src="<?php echo htmlspecialchars($it['Imagen_URL']); ?>" alt="" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                            <?php else: ?>
                                <i class="fa-solid fa-shirt" style="font-size:48px;color:#cbd5e0;"></i>
                            <?php endif; ?>
                        </div>
                        <div style="padding:14px;">
                            <div style="font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;"><?php echo htmlspecialchars($it['Nombre_Categoria'] ?? 'General'); ?></div>
                            <div style="font-weight:800;font-size:15px;margin:4px 0;"><?php echo htmlspecialchars($it['Nombre_Producto']); ?></div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
                                <span style="font-weight:800;color:#004BA0;">$<?php echo number_format($it['Precio_Actual'], 0, ',', '.'); ?></span>
                                <span style="font-size:11px;background:#c6f6d5;color:#22543d;padding:4px 8px;border-radius:12px;font-weight:700;">Stock <?php echo (int)$it['Stock_Actual']; ?></span>
                            </div>
                            <form action="index.php?action=catalogo" method="POST" style="display:flex;gap:8px;margin-top:10px;align-items:center;">
                                <input type="hidden" name="cart_action" value="add">
                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                <input type="number" name="qty" value="1" min="1" max="10" class="crud-input" style="width:65px;">
                                <button type="submit" class="btn-crud-save" style="flex:1;"><i class="fa-solid fa-cart-plus"></i> Agregar</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const tb = document.getElementById('toggleSidebar');
        if (tb && sidebar) tb.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
        const ub = document.getElementById('userMenuBtn'), um = document.getElementById('userDropdownMenu');
        if (ub && um) {
            ub.addEventListener('click', (e) => { e.stopPropagation(); um.classList.toggle('show'); });
            document.addEventListener('click', (e) => { if (!um.contains(e.target) && !ub.contains(e.target)) um.classList.remove('show'); });
        }
        const s = document.getElementById('catSearch');
        if (s) s.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#catGrid > div').forEach(d => {
                d.style.display = d.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
