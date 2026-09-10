<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["user"])) {
    header("Location: index.php?action=login");
    exit();
}
$u = $_SESSION["user"];
$nombre = $u["nombre"] ?? 'Usuario';
$rol = $u["Rol"] ?? $u["rol"] ?? 'Cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - ÁCIDO COLOMBIA</title>
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
            <a href="index.php?action=dashboard" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span class="sidebar-text">Dashboard</span></a>
            <hr class="sidebar-divider">
            <div class="sidebar-heading sidebar-text">TIENDA</div>
            <a href="index.php?action=catalogo" class="nav-item"><i class="fa-solid fa-store"></i><span class="sidebar-text">Catálogo</span></a>
            <a href="index.php?action=carrito" class="nav-item"><i class="fa-solid fa-cart-shopping"></i><span class="sidebar-text">Carrito<?php $nc=array_sum($_SESSION['cart']??[]); if($nc>0) echo " ($nc)"; ?></span></a>
            <a href="index.php?action=ventas" class="nav-item"><i class="fa-solid fa-receipt"></i><span class="sidebar-text"><?php echo in_array($rol,['Administrador','Empleado'],true)?'Ventas':'Mis compras'; ?></span></a>
            <?php if (in_array($rol, ['Administrador','Empleado'], true)): ?>
            <div class="sidebar-heading sidebar-text" style="margin-top:12px;">GESTIÓN</div>
            <a href="index.php?action=inventario" class="nav-item"><i class="fa-solid fa-boxes-stacked"></i><span class="sidebar-text">Inventario</span></a>
            <a href="index.php?action=crud" class="nav-item"><i class="fa-solid fa-users"></i><span class="sidebar-text">Usuarios</span></a>
            <?php endif; ?>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar"><input type="text" placeholder="Buscar..."><button><i class="fa-solid fa-magnifying-glass"></i></button></div>
                <div class="topbar-user">
                    <a href="index.php?action=carrito" class="icon-badge" title="Mi carrito" style="text-decoration:none;color:inherit;">
                        <i class="fa-solid fa-cart-shopping" style="color:#fff;"></i>
                        <?php $ncart=array_sum($_SESSION['cart']??[]); if($ncart>0): ?><span class="badge red"><?php echo $ncart; ?></span><?php endif; ?>
                    </a>
                    <div class="divider-vertical"></div>
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span><?php echo htmlspecialchars($nombre); ?> (<?php echo htmlspecialchars($rol); ?>)</span>
                            <div class="avatar"></div>
                        </div>
                        <div class="dropdown-menu-user" id="userDropdownMenu">
                            <a href="index.php?action=profile" class="dropdown-user-item"><i class="fa-solid fa-user"></i> Ver Perfil</a>
                            <a href="index.php?action=config" class="dropdown-user-item"><i class="fa-solid fa-gear"></i> Configuración</a>
                            <div class="dropdown-user-divider"></div>
                            <a href="index.php?action=logout" class="dropdown-user-item text-danger"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </header>
            <div class="content-padding-crud">
                <div class="page-header"><h2>CONFIGURACIÓN</h2></div>
                <div class="crud-modern-card" style="max-width:560px;">
                    <div class="crud-modern-header"><i class="fa-solid fa-gear"></i><span>Ajustes de cuenta</span></div>
                    <div class="crud-form-body">
                        <p><a href="index.php?action=profile">Ver mi perfil</a></p>
                        <p><a href="index.php?action=change_password">Cambiar contraseña</a></p>
                        <p style="color:#a0aec0;font-size:12px;">Más opciones próximamente.</p>
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
    </script>
</body>
</html>
