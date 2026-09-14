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
if (!isset($tabVentas)) { $tabVentas = $_GET['tab'] ?? 'ventas'; }
if (!isset($pendientes)) { $pendientes = []; }
if (!isset($entregadas)) { $entregadas = []; }
if (!isset($facturas)) { $facturas = []; }
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
                    <span id="liveClock" title="Hora del sistema" style="font-size:12px;font-weight:700;color:#fff;background:rgba(255,255,255,.15);padding:6px 10px;border-radius:6px;">--:--:--</span>
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
                <div class="page-header"><h2><?php echo $__esGest?'VENTAS':'MIS COMPRAS'; ?></h2><small style="color:#718096;">Registro diario · <?php echo date('Y-m-d'); ?></small></div>
                <?php $flashErrV = $_GET['error'] ?? ''; $flashOkV = $_GET['status'] ?? ''; ?>
                <?php if ($flashErrV !== ''): ?><div style="background:#fed7d7;color:#9b2c2c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;"><?php echo htmlspecialchars($flashErrV); ?></div><?php endif; ?>
                <?php if ($flashOkV !== ''): ?><div style="background:#c6f6d5;color:#22543d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;"><?php echo htmlspecialchars($flashOkV); ?></div><?php endif; ?>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-bottom:20px;">
                    <div class="crud-modern-card" style="margin:0;padding:16px;border-left:4px solid #3182ce;">
                        <small style="color:#4a5568;font-weight:700;"><?php echo $__esGest?'GANANCIAS HOY':'MIS COMPRAS HOY'; ?></small>
                        <div class="count-up" data-money="1" data-value="<?php echo (float)($resumenHoy['ganancias'] ?? 0); ?>" style="font-size:24px;font-weight:800;color:#004BA0;">$<?php echo number_format($resumenHoy['ganancias'] ?? 0, 0, ',', '.'); ?></div>
                        <small style="color:#a0aec0;">Suma automática de lo vendido hoy. Se reinicia cada día.</small>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;border-left:4px solid #38a169;">
                        <small style="color:#4a5568;font-weight:700;">VENTAS HOY</small>
                        <div class="count-up" data-value="<?php echo (int)($resumenHoy['ventas'] ?? 0); ?>" style="font-size:24px;font-weight:800;"><?php echo (int)($resumenHoy['ventas'] ?? 0); ?></div>
                        <small style="color:#a0aec0;">N° de ventas con fecha de hoy.</small>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;border-left:4px solid #d69e2e;">
                        <small style="color:#4a5568;font-weight:700;">TICKET PROMEDIO HOY</small>
                        <div class="count-up" data-money="1" data-value="<?php echo (float)($resumenHoy['ticket'] ?? 0); ?>" style="font-size:24px;font-weight:800;">$<?php echo number_format($resumenHoy['ticket'] ?? 0, 0, ',', '.'); ?></div>
                        <small style="color:#a0aec0;">Promedio por venta de hoy.</small>
                    </div>
                </div>
                <div class="crud-modern-card">
                    <div class="crud-modern-header">
                        <i class="fa-solid fa-receipt"></i><span>Historial</span>
                        <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="index.php?action=ventas&tab=ventas" class="btn-crud-save" style="text-decoration:none;<?php echo $tabVentas==='ventas'?'':'opacity:.6;'; ?>">Ventas (<?php echo count($ventas); ?>)</a>
                            <a href="index.php?action=ventas&tab=pendientes" class="btn-crud-save" style="text-decoration:none;<?php echo $tabVentas==='pendientes'?'':'opacity:.6;'; ?>">Pendientes (<?php echo count($pendientes); ?>)</a>
                            <a href="index.php?action=ventas&tab=entregadas" class="btn-crud-save" style="text-decoration:none;<?php echo $tabVentas==='entregadas'?'':'opacity:.6;'; ?>">Entregadas (<?php echo count($entregadas); ?>)</a>
                            <a href="index.php?action=ventas&tab=facturas" class="btn-crud-save" style="text-decoration:none;<?php echo $tabVentas==='facturas'?'':'opacity:.6;'; ?>">Facturas (<?php echo count($facturas); ?>)</a>
                        </div>
                    </div>
                    <?php if ($tabVentas === 'pendientes'): ?>
                    <?php
                    // RF 2.8/2.10: avance logístico. Pendiente se paga por checkout (no manual,
                    // para no desincronizar pago/factura/stock). Preparando = Listo para envío.
                    $nextMap = ['Pagado' => 'Preparando', 'Preparando' => 'En camino', 'En camino' => 'Entregado'];
                    $badgeMap = ['Pendiente' => '#e2e8f0;#4a5568', 'Pagado' => '#bee3f8;#2a4365', 'Preparando' => '#fefcbf;#744210', 'En camino' => '#feebc8;#7b341e'];
                    ?>
                    <div style="overflow-x:auto;">
                        <table class="crud-table" id="ventasTable">
                            <thead><tr><th>Pedido</th><th>Venta / Compra</th><th>Cliente</th><th>Dirección / Ciudad</th><th>Uds.</th><th>Total</th><th>Estado</th><?php if ($__esGest): ?><th style="text-align:right;">Logística</th><?php endif; ?></tr></thead>
                            <tbody>
                                <?php if (!empty($pendientes)): foreach ($pendientes as $p): ?>
                                <?php $estP = $p['Estado_Pedido']; $colP = explode(';', $badgeMap[$estP] ?? '#edf2f7;#4a5568'); $nextP = $nextMap[$estP] ?? null; ?>
                                <tr>
                                    <td style="font-weight:bold;">#<?php echo $p['ID_Pedido']; ?> <small>(<?php echo htmlspecialchars($p['Tipo_Envio'] ?? ''); ?>)</small></td>
                                    <td>#<?php echo $p['ID_Venta']; ?> · <?php echo htmlspecialchars($p['Fecha_Compra']); ?></td>
                                    <td><?php echo htmlspecialchars(trim(($p['Nombres'] ?? '').' '.($p['Apellidos'] ?? ''))); ?></td>
                                    <td><?php echo htmlspecialchars(($p['Direccion_Envio'] ?? '').' — '.($p['Nombre_Ciudad'] ?? '')); ?></td>
                                    <td><?php echo (int)$p['Unidades']; ?></td>
                                    <td><strong>$<?php echo number_format($p['Total'],0,',','.'); ?></strong></td>
                                    <td><span style="background:<?php echo $colP[0]; ?>;color:<?php echo $colP[1]; ?>;padding:4px 8px;border-radius:4px;font-weight:700;"><?php echo htmlspecialchars($estP); ?></span></td>
                                    <?php if ($__esGest): ?>
                                    <td style="text-align:right;white-space:nowrap;">
                                        <?php if ($nextP): ?>
                                        <form method="POST" action="index.php?action=ventas" style="display:inline;">
                                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                            <input type="hidden" name="pedido_id" value="<?php echo $p['ID_Pedido']; ?>">
                                            <input type="hidden" name="pedido_estado" value="<?php echo $nextP; ?>">
                                            <button type="submit" class="btn-action-edit" title="Avanzar a <?php echo $nextP; ?>"><i class="fa-solid fa-forward"></i> <?php echo $nextP; ?></button>
                                        </form>
                                        <?php elseif ($estP === 'Pendiente'): ?>
                                        <small style="color:#a0aec0;">Pendiente de pago</small>
                                        <?php endif; ?>
                                        <?php if (in_array($estP, ['Pendiente','Pagado','Preparando'], true)): ?>
                                        <form method="POST" action="index.php?action=ventas" style="display:inline;" onsubmit="return confirm('¿Cancelar el pedido #<?php echo $p['ID_Pedido']; ?>?');">
                                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                            <input type="hidden" name="pedido_id" value="<?php echo $p['ID_Pedido']; ?>">
                                            <input type="hidden" name="pedido_estado" value="Cancelado">
                                            <button type="submit" class="btn-action-delete" title="Cancelar pedido"><i class="fa-solid fa-ban"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="8" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Sin ventas pendientes por entregar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif ($tabVentas === 'entregadas'): ?>
                    <div style="overflow-x:auto;">
                        <table class="crud-table" id="ventasTable">
                            <thead><tr><th>Pedido</th><th>Venta / Fecha</th><th>Cliente</th><th>Dirección</th><th>Items</th><th>Total</th><th>Estado</th></tr></thead>
                            <tbody>
                                <?php if (!empty($entregadas)): foreach ($entregadas as $p): ?>
                                <tr>
                                    <td style="font-weight:bold;">#<?php echo $p['ID_Pedido']; ?></td>
                                    <td>#<?php echo $p['ID_Venta']; ?> · <?php echo htmlspecialchars($p['Fecha_Compra']); ?></td>
                                    <td><?php echo htmlspecialchars(trim(($p['Nombres'] ?? '').' '.($p['Apellidos'] ?? ''))); ?></td>
                                    <td><?php echo htmlspecialchars($p['Direccion_Envio'] ?? ''); ?></td>
                                    <td><?php echo (int)$p['Items']; ?></td>
                                    <td><strong>$<?php echo number_format($p['Total'],0,',','.'); ?></strong></td>
                                    <td><span style="background:#c6f6d5;padding:4px 8px;border-radius:4px;font-weight:700;"><?php echo htmlspecialchars($p['Estado_Pedido']); ?></span></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="7" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Sin compras entregadas aún.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif ($tabVentas === 'facturas'): ?>
                    <div style="overflow-x:auto;">
                        <table class="crud-table" id="ventasTable">
                            <thead><tr><th>Factura</th><th>Emisión</th><th>Venta</th><th>Cliente</th><th>Items</th><th>Total</th></tr></thead>
                            <tbody>
                                <?php if (!empty($facturas)): foreach ($facturas as $f): ?>
                                <tr>
                                    <td><code style="background:#edf2f7;padding:4px 8px;border-radius:4px;"><?php echo htmlspecialchars($f['Numero_Factura']); ?></code></td>
                                    <td><?php echo htmlspecialchars($f['Fecha_Emision']); ?></td>
                                    <td>#<?php echo $f['ID_Venta']; ?></td>
                                    <td><?php echo htmlspecialchars(trim(($f['Nombres'] ?? '').' '.($f['Apellidos'] ?? ''))); ?></td>
                                    <td><?php echo (int)$f['Items']; ?></td>
                                    <td><strong>$<?php echo number_format($f['Total'],0,',','.'); ?></strong></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="6" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Sin facturas generadas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="crud-table" id="ventasTable">
                            <thead><tr><th>ID Venta</th><th>Fecha</th><th>Cliente</th><th>Items</th><th>Total</th><th>Pago</th><th>Factura</th><th>Detalle</th></tr></thead>
                            <tbody>
                                <?php if (!empty($ventas)): ?>
                                    <?php foreach ($ventas as $v): ?>
                                    <tr>
                                        <td style="font-weight:bold;">#<?php echo $v['ID_Venta']; ?></td>
                                        <td><?php echo htmlspecialchars($v['Fecha_Venta']); ?></td>
                                        <td><?php echo htmlspecialchars($v['ClienteEmail'] ?? ('Cli '.$v['ID_Cliente'])); ?></td>
                                        <td><?php echo (int)$v['Items']; ?></td>
                                        <td><strong>$<?php echo number_format($v['Total'],0,',','.'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($v['Metodo'] ?? '-'); ?><?php if (!empty($v['Entidad'])) echo ' · ' . htmlspecialchars($v['Entidad']); ?></td>
                                        <td><code style="background:#edf2f7;padding:4px 8px;border-radius:4px;"><?php echo htmlspecialchars($v['Factura'] ?? '—'); ?></code></td>
                                        <td><button type="button" class="btn-action-edit btn-detalle" data-id="<?php echo $v['ID_Venta']; ?>" title="Ver productos"><i class="fa-solid fa-eye"></i></button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Sin ventas aún. <a href="index.php?action=catalogo">Ir al catálogo</a></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>
    <script>
        const s=document.getElementById('vSearch');
        if(s)s.addEventListener('input',function(){
            const q=this.value.toLowerCase();
            document.querySelectorAll('#ventasTable tbody tr:not(.detail-row)').forEach(tr=>{tr.style.display=tr.textContent.toLowerCase().includes(q)?'':'none';});
        });
        // Filas expandibles: ver productos de la venta sin recargar
        document.querySelectorAll('.btn-detalle').forEach(btn => {
            btn.addEventListener('click', async () => {
                const tr = btn.closest('tr');
                const next = tr.nextElementSibling;
                if (next && next.classList.contains('detail-row')) { next.remove(); return; }
                btn.disabled = true;
                try {
                    const res = await fetch('index.php?action=venta_detalle&id=' + btn.dataset.id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const d = await res.json();
                    const det = document.createElement('tr');
                    det.className = 'detail-row';
                    if (d.success && d.items.length) {
                        const lis = d.items.map(it => {
                            const dv = document.createElement('div'); dv.textContent = it.Nombre_Producto || '';
                            return '<li>' + dv.innerHTML + ' × ' + it.Cantidad + ' — $' + Number(it.Precio_Venta_Historico).toLocaleString('es-CO') + '</li>';
                        }).join('');
                        det.innerHTML = '<td colspan="8"><ul style="margin:4px 0;padding-left:18px;">' + lis + '</ul></td>';
                    } else {
                        det.innerHTML = '<td colspan="8" style="color:#a0aec0;">Sin detalle disponible.</td>';
                    }
                    tr.after(det);
                } catch (err) { window.acidoToast('Sin conexión, intenta de nuevo', 'error'); }
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
