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
            <a href="index.php?action=pqr" class="nav-item">
                <i class="fa-solid fa-headset"></i>
                <span class="sidebar-text">PQR y Ayuda</span>
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
                    <span id="liveClock" title="Hora del sistema" style="font-size:12px;font-weight:700;color:#fff;background:rgba(255,255,255,.15);padding:6px 10px;border-radius:6px;">--:--:--</span>
                    <?php require_once __DIR__ . "/partials/stock_bell.php"; ?>
                    <a href="index.php?action=carrito" class="icon-badge" title="Mi carrito" style="text-decoration:none;color:inherit;">
                        <i class="fa-solid fa-cart-shopping" style="color:#fff;"></i>
                        <?php $ncartTop=array_sum($_SESSION['cart']??[]); if($ncartTop>0): ?><span class="badge red"><?php echo $ncartTop; ?></span><?php endif; ?>
                    </a>
                    <div class="divider-vertical"></div>
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span class="user-badge"><strong><?php echo htmlspecialchars($_SESSION["user"]["nombre_completo"] ?? $_SESSION["user"]["nombre"] ?? $_SESSION["user"]["Email"] ?? 'Usuario'); ?></strong><small><?php echo htmlspecialchars($__rol); ?></small></span>
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
                    <h2>CATÁLOGO</h2>
                </div>
                <?php if (empty($items)): ?>
                    <div class="crud-modern-card" style="padding:40px;text-align:center;color:#a0aec0;font-style:italic;">
                        No hay productos disponibles por ahora.
                    </div>
                <?php else: ?>
                <div id="catGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
                    <?php foreach ($items as $it): ?>
                    <?php $imgUrl = function_exists('imagen_producto_url') ? imagen_producto_url($it['Imagen_URL'] ?? null) : (!empty($it['Imagen_URL']) ? $it['Imagen_URL'] : null); $imgAlt = $it['Nombre_Producto'] ?? 'Producto'; ?>
                    <div class="crud-modern-card" style="margin:0;overflow:hidden;display:flex;flex-direction:column;">
                        <div style="height:220px;background:#f8f9fc;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;padding:10px;">
                            <?php if (!empty($imgUrl)): ?>
                                <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($imgAlt); ?>" style="width:100%;height:100%;object-fit:contain;" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                                <i class="fa-solid fa-shirt" style="display:none;font-size:48px;color:#cbd5e0;"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-shirt" style="font-size:48px;color:#cbd5e0;"></i>
                            <?php endif; ?>
                        </div>
                        <div style="padding:14px;display:flex;flex-direction:column;flex:1;">
                            <div style="font-size:11px;font-weight:700;color:#4a5568;text-transform:uppercase;"><?php echo htmlspecialchars($it['Nombre_Categoria'] ?? 'General'); ?></div>
                            <div style="font-weight:800;font-size:15px;margin:4px 0;min-height:44px;"><?php echo htmlspecialchars($it['Nombre_Producto']); ?></div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
                                <span style="font-weight:800;color:#004BA0;">$<?php echo number_format($it['Precio_Actual'], 0, ',', '.'); ?></span>
                                <?php if ((int)$it['Stock_Actual'] > 0): ?>
                                <span style="font-size:11px;background:#c6f6d5;color:#22543d;padding:4px 8px;border-radius:12px;font-weight:700;">Stock <?php echo (int)$it['Stock_Actual']; ?></span>
                                <?php else: ?>
                                <span style="font-size:11px;background:#fed7d7;color:#9b2c2c;padding:4px 8px;border-radius:12px;font-weight:700;">Agotado</span>
                                <?php endif; ?>
                            </div>
                            <?php if ((int)$it['Stock_Actual'] > 0): ?>
                            <form action="index.php?action=catalogo" method="POST" class="ajax-cart-form" data-nombre="<?php echo htmlspecialchars($it['Nombre_Producto'], ENT_QUOTES); ?>" style="display:flex;gap:8px;margin-top:auto;padding-top:10px;align-items:center;">
                                <input type="hidden" name="cart_action" value="add">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                <input type="number" name="qty" value="1" min="1" max="10" class="crud-input" style="width:65px;">
                                <button type="submit" class="btn-crud-save" style="flex:1;"><i class="fa-solid fa-cart-plus"></i> Agregar</button>
                            </form>
                            <form action="index.php?action=catalogo" method="POST" style="display:flex;gap:8px;padding-top:4px;">
                                <input type="hidden" name="cart_action" value="buy_now">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn-buy-now" style="flex:1;"><i class="fa-solid fa-bolt"></i> Comprar ahora</button>
                            </form>
                            <?php else: ?>
                            <form action="index.php?action=catalogo" method="POST" class="ajax-espera-form" data-nombre="<?php echo htmlspecialchars($it['Nombre_Producto'], ENT_QUOTES); ?>" style="margin-top:auto;padding-top:10px;">
                                <input type="hidden" name="espera_action" value="anotar">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                <button type="submit" class="btn-crud-cancel" style="width:100%;background:#4e73df;"><i class="fa-solid fa-bell"></i> Avísame cuando vuelva</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>
    <script>
        const s = document.getElementById('catSearch');
        if (s) s.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#catGrid > div').forEach(d => {
                d.style.display = d.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
        // Lista de espera (RF 5.8): avísame sin recargar
        document.querySelectorAll('.ajax-espera-form').forEach(f => {
            f.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = f.querySelector('button[type=submit]');
                if (btn) btn.disabled = true;
                try {
                    const res = await fetch(f.action, { method: 'POST', body: new FormData(f), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const d = await res.json();
                    window.acidoToast(d.message || (d.success ? 'Anotado' : 'No se pudo'), d.success ? 'ok' : 'error');
                    if (d.success && btn) { btn.innerHTML = '<i class="fa-solid fa-check"></i> Anotado'; }
                } catch (err) {
                    window.acidoToast('Sin conexión, intenta de nuevo', 'error');
                }
                if (btn) btn.disabled = false;
            });
        });
        document.querySelectorAll('.ajax-cart-form').forEach(f => {
            f.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = f.querySelector('button[type=submit]');
                if (btn) btn.disabled = true;
                try {
                    const res = await fetch(f.action, { method: 'POST', body: new FormData(f), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const d = await res.json();
                    if (d.success) {
                        window.updateCartBadge(d.cartCount);
                        window.acidoToast('Agregado: ' + (f.dataset.nombre || 'producto'), 'ok');
                    } else {
                        window.acidoToast('Sin stock disponible', 'error');
                    }
                } catch (err) {
                    window.acidoToast('Sin conexión, intenta de nuevo', 'error');
                }
                if (btn) btn.disabled = false;
            });
        });
    </script>
    <script src="/ACIDO/BACKPHP_TEST/public/js/notif-stock.js?v=<?php echo time(); ?>"></script>
</body>
</html>
