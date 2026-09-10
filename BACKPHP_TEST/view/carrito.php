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
if (!isset($cartData)) { $cartData = ['items'=>[], 'total'=>0]; }
if (!isset($metodos)) { $metodos = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - ÁCIDO COLOMBIA</title>
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/crud.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body crud-body">
    <div class="dashboard-container crud-container">
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-brand">
                <button type="button" id="toggleSidebar" class="toggle-btn"><i class="fa-solid fa-bars"></i></button>
                <div class="brand-info sidebar-text">
                    <img src="/ACIDO/BACKPHP_TEST/public/img/LOGO2.png" class="brand-icon" alt="Logo">
                    <span class="brand-name">ACIDO</span>
                </div>
            </div>
            <hr class="sidebar-divider">
            <?php if (in_array($__rol, ['Administrador','Empleado'], true)): ?>
            <a href="index.php?action=dashboard" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span class="sidebar-text">Dashboard</span></a>
            <hr class="sidebar-divider">
            <?php endif; ?>
            <div class="sidebar-heading sidebar-text">TIENDA</div>
            <a href="index.php?action=catalogo" class="nav-item"><i class="fa-solid fa-store"></i><span class="sidebar-text">Catálogo</span></a>
            <a href="index.php?action=carrito" class="nav-item active"><i class="fa-solid fa-cart-shopping"></i><span class="sidebar-text">Carrito<?php $n=array_sum($_SESSION['cart']??[]); if($n>0) echo " ($n)"; ?></span></a>
            <a href="index.php?action=ventas" class="nav-item"><i class="fa-solid fa-receipt"></i><span class="sidebar-text"><?php echo in_array($__rol,['Administrador','Empleado'],true)?'Ventas':'Mis compras'; ?></span></a>
            <?php if (in_array($__rol, ['Administrador','Empleado'], true)): ?>
            <div class="sidebar-heading sidebar-text" style="margin-top:12px;">GESTIÓN</div>
            <a href="index.php?action=inventario" class="nav-item"><i class="fa-solid fa-boxes-stacked"></i><span class="sidebar-text">Inventario</span></a>
            <a href="index.php?action=crud" class="nav-item"><i class="fa-solid fa-users"></i><span class="sidebar-text">Usuarios</span></a>
            <?php endif; ?>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar"><input type="text" placeholder="Carrito..."><button><i class="fa-solid fa-magnifying-glass"></i></button></div>
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
                <div class="page-header"><h2>CARRITO</h2></div>
                <?php if (isset($_GET['error'])): ?>
                    <div style="background:#fed7d7;color:#9b2c2c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['ok'])): ?>
                    <div style="background:#c6f6d5;color:#22543d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;">
                        ¡Compra exitosa! Venta #<?php echo htmlspecialchars($_GET['ok']); ?><?php if(isset($_GET['fac'])) echo " · Factura ".htmlspecialchars($_GET['fac']); ?>. <a href="index.php?action=ventas">Ver en ventas</a>
                    </div>
                <?php endif; ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header"><i class="fa-solid fa-cart-shopping"></i><span>Productos en el carrito (<?php echo count($cartData['items']); ?>)</span></div>
                    <div style="overflow-x:auto;">
                        <table class="crud-table">
                            <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad (máx 10)</th><th>Subtotal</th><th style="text-align:right;">Acciones</th></tr></thead>
                            <tbody>
                                <?php if (!empty($cartData['items'])): ?>
                                    <?php foreach ($cartData['items'] as $it): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($it['Nombre_Producto']); ?></strong><br><small style="color:#a0aec0;">Stock: <?php echo (int)$it['Stock_Actual']; ?><?php if((int)$it['Activo']!==1) echo " · INACTIVO"; ?></small></td>
                                        <td>$<?php echo number_format($it['Precio_Actual'],0,',','.'); ?></td>
                                        <td>
                                            <form action="index.php?action=carrito" method="POST" style="display:flex;gap:6px;align-items:center;">
                                                <input type="hidden" name="cart_action" value="update">
                                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                                <input type="number" name="qty" value="<?php echo $it['cantidad']; ?>" min="1" max="10" class="crud-input" style="width:70px;">
                                                <button type="submit" class="btn-action-edit">Actualizar</button>
                                            </form>
                                        </td>
                                        <td><strong>$<?php echo number_format($it['subtotal'],0,',','.'); ?></strong></td>
                                        <td style="text-align:right;">
                                            <form action="index.php?action=carrito" method="POST" style="display:inline;">
                                                <input type="hidden" name="cart_action" value="remove">
                                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                                <button type="submit" class="btn-action-delete"><i class="fa-solid fa-trash"></i> Quitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Carrito vacío. <a href="index.php?action=catalogo">Ir al catálogo</a></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (!empty($cartData['items'])): ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header"><i class="fa-solid fa-cash-register"></i><span>Finalizar compra</span></div>
                    <div class="crud-form-body">
                        <div style="font-size:20px;font-weight:800;margin-bottom:12px;">Total: $<?php echo number_format($cartData['total'],0,',','.'); ?></div>
                        <form action="index.php?action=carrito" method="POST" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
                            <input type="hidden" name="cart_action" value="checkout">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">MÉTODO DE PAGO</label>
                                <select name="id_metodo" class="crud-input" required>
                                    <?php foreach ($metodos as $m): ?>
                                        <option value="<?php echo $m['ID_Metodo']; ?>"><?php echo htmlspecialchars($m['Tipo_Metodo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-crud-save"><i class="fa-solid fa-check"></i> Confirmar compra</button>
                        </form>
                        <small style="color:#a0aec0;">Se crea venta + detalle + pago + factura automática. Stock se descuenta al pagar.</small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        const sb=document.getElementById('sidebar'),tb=document.getElementById('toggleSidebar');
        if(tb&&sb)tb.addEventListener('click',()=>sb.classList.toggle('collapsed'));
        const ub=document.getElementById('userMenuBtn'),um=document.getElementById('userDropdownMenu');
        if(ub&&um){ub.addEventListener('click',(e)=>{e.stopPropagation();um.classList.toggle('show');});document.addEventListener('click',(e)=>{if(!um.contains(e.target)&&!ub.contains(e.target))um.classList.remove('show');});}
    </script>
</body>
</html>
