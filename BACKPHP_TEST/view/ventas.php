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
$__esGest = in_array($__rol, ['Administrador','Empleado'], true);
if (!isset($ventas)) { $ventas = []; }
if (!isset($resumenHoy)) { $resumenHoy = ['ventas'=>0,'ganancias'=>0,'ticket'=>0]; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__esGest ? 'Ventas' : 'Mis compras'; ?> - ÁCIDO COLOMBIA</title>
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
            <?php if ($__esGest): ?>
            <a href="index.php?action=dashboard" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span class="sidebar-text">Dashboard</span></a>
            <hr class="sidebar-divider">
            <?php endif; ?>
            <div class="sidebar-heading sidebar-text">TIENDA</div>
            <a href="index.php?action=catalogo" class="nav-item"><i class="fa-solid fa-store"></i><span class="sidebar-text">Catálogo</span></a>
            <a href="index.php?action=carrito" class="nav-item"><i class="fa-solid fa-cart-shopping"></i><span class="sidebar-text">Carrito<?php $n=array_sum($_SESSION['cart']??[]); if($n>0) echo " ($n)"; ?></span></a>
            <a href="index.php?action=ventas" class="nav-item active"><i class="fa-solid fa-receipt"></i><span class="sidebar-text"><?php echo $__esGest?'Ventas':'Mis compras'; ?></span></a>
            <?php if ($__esGest): ?>
            <div class="sidebar-heading sidebar-text" style="margin-top:12px;">GESTIÓN</div>
            <a href="index.php?action=inventario" class="nav-item"><i class="fa-solid fa-boxes-stacked"></i><span class="sidebar-text">Inventario</span></a>
            <a href="index.php?action=crud" class="nav-item"><i class="fa-solid fa-users"></i><span class="sidebar-text">Usuarios</span></a>
            <?php endif; ?>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar"><input type="text" id="vSearch" placeholder="Buscar venta, cliente, factura..."><button><i class="fa-solid fa-magnifying-glass"></i></button></div>
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
                <div class="page-header"><h2><?php echo $__esGest?'VENTAS':'MIS COMPRAS'; ?></h2><small style="color:#718096;">Registro diario · <?php echo date('Y-m-d'); ?></small></div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-bottom:20px;">
                    <div class="crud-modern-card" style="margin:0;padding:16px;border-left:4px solid #3182ce;">
                        <small style="color:#4a5568;font-weight:700;"><?php echo $__esGest?'GANANCIAS HOY':'MIS COMPRAS HOY'; ?></small>
                        <div style="font-size:24px;font-weight:800;color:#004BA0;">$<?php echo number_format($resumenHoy['ganancias'] ?? 0, 0, ',', '.'); ?></div>
                        <small style="color:#a0aec0;">Suma automática de lo vendido hoy. Se reinicia cada día.</small>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;border-left:4px solid #38a169;">
                        <small style="color:#4a5568;font-weight:700;">VENTAS HOY</small>
                        <div style="font-size:24px;font-weight:800;"><?php echo (int)($resumenHoy['ventas'] ?? 0); ?></div>
                        <small style="color:#a0aec0;">N° de ventas con fecha de hoy.</small>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;border-left:4px solid #d69e2e;">
                        <small style="color:#4a5568;font-weight:700;">TICKET PROMEDIO HOY</small>
                        <div style="font-size:24px;font-weight:800;">$<?php echo number_format($resumenHoy['ticket'] ?? 0, 0, ',', '.'); ?></div>
                        <small style="color:#a0aec0;">Promedio por venta de hoy.</small>
                    </div>
                </div>
                <div class="crud-modern-card">
                    <div class="crud-modern-header"><i class="fa-solid fa-receipt"></i><span>Historial (<?php echo count($ventas); ?>)</span></div>
                    <div style="overflow-x:auto;">
                        <table class="crud-table" id="ventasTable">
                            <thead><tr><th>ID Venta</th><th>Fecha</th><th>Cliente</th><th>Items</th><th>Total</th><th>Pago</th><th>Factura</th></tr></thead>
                            <tbody>
                                <?php if (!empty($ventas)): ?>
                                    <?php foreach ($ventas as $v): ?>
                                    <tr>
                                        <td style="font-weight:bold;">#<?php echo $v['ID_Venta']; ?></td>
                                        <td><?php echo htmlspecialchars($v['Fecha_Venta']); ?></td>
                                        <td><?php echo htmlspecialchars($v['ClienteEmail'] ?? ('Cli '.$v['ID_Cliente'])); ?></td>
                                        <td><?php echo (int)$v['Items']; ?></td>
                                        <td><strong>$<?php echo number_format($v['Total'],0,',','.'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($v['Metodo'] ?? '-'); ?></td>
                                        <td><code style="background:#edf2f7;padding:4px 8px;border-radius:4px;"><?php echo htmlspecialchars($v['Factura'] ?? '—'); ?></code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Sin ventas aún. <a href="index.php?action=catalogo">Ir al catálogo</a></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const sb=document.getElementById('sidebar'),tb=document.getElementById('toggleSidebar');
        if(tb&&sb)tb.addEventListener('click',()=>sb.classList.toggle('collapsed'));
        const ub=document.getElementById('userMenuBtn'),um=document.getElementById('userDropdownMenu');
        if(ub&&um){ub.addEventListener('click',(e)=>{e.stopPropagation();um.classList.toggle('show');});document.addEventListener('click',(e)=>{if(!um.contains(e.target)&&!ub.contains(e.target))um.classList.remove('show');});}
        const s=document.getElementById('vSearch');
        if(s)s.addEventListener('input',function(){
            const q=this.value.toLowerCase();
            document.querySelectorAll('#ventasTable tbody tr').forEach(tr=>{tr.style.display=tr.textContent.toLowerCase().includes(q)?'':'none';});
        });
    </script>
</body>
</html>
