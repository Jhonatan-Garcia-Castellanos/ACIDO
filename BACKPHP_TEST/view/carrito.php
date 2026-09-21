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
if (!isset($checkoutStep)) { $checkoutStep = 1; }
if (!isset($shipping)) { $shipping = null; }
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
            <a href="index.php?action=pqr" class="nav-item"><i class="fa-solid fa-headset"></i><span class="sidebar-text">PQR y Ayuda</span></a>
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
                <div class="page-header"><h2>CARRITO</h2></div>
                <?php if (isset($_GET['error'])): ?>
                    <div style="background:#fed7d7;color:#9b2c2c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['ok'])): ?>
                    <div style="background:#c6f6d5;color:#22543d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;">
                        ¡Compra exitosa! Venta #<?php echo htmlspecialchars($_GET['ok']); ?><?php if(isset($_GET['fac'])) echo " · Factura ".htmlspecialchars($_GET['fac']); ?>. <a href="index.php?action=ventas">Ver en ventas</a>
                        <br><small><i class="fa-solid fa-truck-fast"></i> Entrega estimada: <?php echo htmlspecialchars(function_exists('fechaEstimada') ? fechaEstimada(date('Y-m-d'), 'Estándar') : date('Y-m-d', strtotime('+5 days'))); ?> (envío Estándar). Revisa tu correo: te enviamos la confirmación con la factura.</small>
                    </div>
                <?php endif; ?>

                <?php if (!isset($_GET['ok'])): ?>
                <div class="checkout-steps">
                    <div class="checkout-step <?php echo $checkoutStep >= 1 ? ($checkoutStep > 1 ? 'done' : 'active') : ''; ?>">
                        <span class="step-num"><?php echo $checkoutStep > 1 ? '<i class="fa-solid fa-check" style="font-size:13px;"></i>' : '1'; ?></span>
                        <span>Mi carrito</span>
                    </div>
                    <div class="checkout-step-divider <?php echo $checkoutStep > 1 ? 'done' : ''; ?>"></div>
                    <div class="checkout-step <?php echo $checkoutStep >= 2 ? ($checkoutStep > 2 ? 'done' : 'active') : ''; ?>">
                        <span class="step-num"><?php echo $checkoutStep > 2 ? '<i class="fa-solid fa-check" style="font-size:13px;"></i>' : '2'; ?></span>
                        <span>Datos de envío</span>
                    </div>
                    <div class="checkout-step-divider <?php echo $checkoutStep > 2 ? 'done' : ''; ?>"></div>
                    <div class="checkout-step <?php echo $checkoutStep >= 3 ? 'active' : ''; ?>">
                        <span class="step-num">3</span>
                        <span>Pago</span>
                    </div>
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
                                    <tr id="cartRow-<?php echo $it['ID_Producto']; ?>">
                                        <td>
                                            <div style="display:flex;align-items:center;gap:12px;">
                                                <div style="width:54px;height:54px;border-radius:8px;background:#f8f9fc;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #edf2f7;flex-shrink:0;">
                                                    <?php if (!empty($it['Imagen_URL'])): ?>
                                                        <img src="<?php echo htmlspecialchars($it['Imagen_URL']); ?>" alt="<?php echo htmlspecialchars($it['Nombre_Producto']); ?>" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-shirt" style="font-size:20px;color:#cbd5e0;"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($it['Nombre_Producto']); ?></strong><br><small style="color:#a0aec0;">Stock: <?php echo (int)$it['Stock_Actual']; ?><?php if((int)$it['Activo']!==1) echo " · INACTIVO"; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($it['Precio_Actual'],0,',','.'); ?></td>
                                        <td>
                                            <?php if ($checkoutStep === 1): ?>
                                            <form action="index.php?action=carrito" method="POST" class="ajax-cart-update" style="display:flex;gap:6px;align-items:center;">
                                                <input type="hidden" name="cart_action" value="update">
                                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                                <input type="number" name="qty" value="<?php echo $it['cantidad']; ?>" min="1" max="10" class="crud-input qty-input" data-id="<?php echo $it['ID_Producto']; ?>" data-price="<?php echo $it['Precio_Actual']; ?>" style="width:70px;">
                                                <button type="submit" class="btn-action-edit">Actualizar</button>
                                            </form>
                                            <?php else: ?>
                                            <span style="font-weight:700;"><?php echo (int)$it['cantidad']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="row-subtotal" data-id="<?php echo $it['ID_Producto']; ?>"><strong>$<?php echo number_format($it['subtotal'],0,',','.'); ?></strong></td>
                                        <td style="text-align:right;">
                                            <?php if ($checkoutStep === 1): ?>
                                            <form action="index.php?action=carrito" method="POST" class="ajax-cart-remove" data-id="<?php echo $it['ID_Producto']; ?>" style="display:inline;">
                                                <input type="hidden" name="cart_action" value="remove">
                                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                                <input type="hidden" name="id" value="<?php echo $it['ID_Producto']; ?>">
                                                <button type="submit" class="btn-action-delete"><i class="fa-solid fa-trash"></i> Quitar</button>
                                            </form>
                                            <?php endif; ?>
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

                <?php if ($checkoutStep === 1): ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header"><i class="fa-solid fa-arrow-right"></i><span>Continuar con la compra</span></div>
                    <div class="crud-form-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <div>
                            <div style="font-size:20px;font-weight:800;">Total: $<?php echo number_format($cartData['total'],0,',','.'); ?></div>
                            <small style="color:#a0aec0;">Revisa tus productos y luego completa los datos de envío.</small>
                        </div>
                        <a href="index.php?action=carrito&step=2" class="btn-crud-save" style="text-decoration:none;text-align:center;"><i class="fa-solid fa-truck"></i> Continuar al envío</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($checkoutStep === 2): ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header"><i class="fa-solid fa-truck"></i><span>Datos de envío</span></div>
                    <div class="crud-form-body">
                        <small style="color:#a0aec0;display:block;margin-bottom:16px;">Completa los datos para hacer llegar tu pedido a domicilio.</small>
                        <form action="index.php?action=carrito" method="POST" id="shippingForm" autocomplete="off">
                            <input type="hidden" name="cart_action" value="save_shipping">
                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                            <div class="envio-campos">
                                <div class="envio-campo">
                                    <label>Nombres</label>
                                    <input type="text" name="nombres" class="crud-input" placeholder="Ej. Juan Carlos" required maxlength="100" value="<?php echo htmlspecialchars($shipping['nombres'] ?? ''); ?>">
                                </div>
                                <div class="envio-campo">
                                    <label>Apellidos</label>
                                    <input type="text" name="apellidos" class="crud-input" placeholder="Ej. Pérez López" required maxlength="100" value="<?php echo htmlspecialchars($shipping['apellidos'] ?? ''); ?>">
                                </div>
                                <div class="envio-campo">
                                    <label>DNI / Cédula (6-12 dígitos)</label>
                                    <input type="text" name="dni" class="crud-input" placeholder="Ej. 1012345678" required maxlength="12" inputmode="numeric" pattern="[0-9]{6,12}" title="6 a 12 dígitos numéricos" value="<?php echo htmlspecialchars($shipping['dni'] ?? ''); ?>">
                                </div>
                                <div class="envio-campo">
                                    <label>Teléfono (7 o 10 dígitos)</label>
                                    <input type="text" name="telefono" class="crud-input" placeholder="Ej. 3001234567" required maxlength="10" inputmode="tel" pattern="[0-9]{7}|[0-9]{10}" title="7 o 10 dígitos numéricos" value="<?php echo htmlspecialchars($shipping['telefono'] ?? ''); ?>">
                                </div>
                                <div class="envio-campo ancho-completo">
                                    <label>Dirección de envío</label>
                                    <input type="text" name="direccion" class="crud-input" placeholder="Calle, número, barrio, ciudad" required maxlength="200" value="<?php echo htmlspecialchars($shipping['direccion'] ?? ''); ?>">
                                </div>
                                <div class="envio-campo ancho-completo">
                                    <label>Correo electrónico</label>
                                    <input type="email" name="correo" class="crud-input" placeholder="tu@correo.com" required maxlength="120" value="<?php echo htmlspecialchars($shipping['correo'] ?? ''); ?>">
                                </div>
                            </div>
                            <div style="margin-top:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                                <a href="index.php?action=carrito" style="color:#4a5568;font-weight:700;font-size:13px;text-decoration:none;padding:8px 12px;"><i class="fa-solid fa-arrow-left"></i> Volver al carrito</a>
                                <button type="submit" class="btn-crud-save"><i class="fa-solid fa-arrow-right"></i> Continuar al pago</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($checkoutStep === 3): ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header"><i class="fa-solid fa-truck"></i><span>Resumen de envío</span></div>
                    <div class="crud-form-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <div style="font-size:13px;line-height:1.7;">
                            <strong><?php echo htmlspecialchars(($shipping['nombres'] ?? '') . ' ' . ($shipping['apellidos'] ?? '')); ?></strong><br>
                            DNI: <?php echo htmlspecialchars($shipping['dni'] ?? '—'); ?> · Tel: <?php echo htmlspecialchars($shipping['telefono'] ?? '—'); ?><br>
                            <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($shipping['direccion'] ?? '—'); ?><br>
                            <i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($shipping['correo'] ?? '—'); ?>
                        </div>
                        <a href="index.php?action=carrito&step=2" style="color:#4a5568;font-weight:700;font-size:12px;text-decoration:none;"><i class="fa-solid fa-pen"></i> Editar</a>
                    </div>
                </div>
                <div class="crud-modern-card">
                    <div class="crud-modern-header"><i class="fa-solid fa-cash-register"></i><span>Finalizar compra</span></div>
                    <div class="crud-form-body">
                        <div style="font-size:20px;font-weight:800;margin-bottom:4px;">Total: $<?php echo number_format($cartData['total'],0,',','.'); ?></div>
                        <small style="color:#a0aec0;display:block;margin-bottom:16px;">Selecciona tu medio de pago y confirma. El número solo se guarda enmascarado.</small>
                        <form action="index.php?action=carrito" method="POST" id="pagoForm" autocomplete="off" enctype="multipart/form-data" onsubmit="var b=this.querySelector('button[type=submit]');if(b){b.disabled=true;b.innerHTML='<i class=&quot;fa-solid fa-spinner fa-spin&quot;></i> Procesando...';}">
                            <input type="hidden" name="cart_action" value="checkout">
                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                            <input type="hidden" name="entidad" id="entidadInput" value="">
                            <div class="medios-pago">
                                <?php $metodosFirst = true; foreach ($metodos as $m): ?>
                                <?php
                                    $tipoLower = mb_strtolower($m['Tipo_Metodo']);
                                    $claseMedio = (strpos($tipoLower, 'tarjeta') !== false || strpos($tipoLower, 'visa') !== false || strpos($tipoLower, 'card') !== false) ? 'tarjeta' : 'cuenta';
                                ?>
                                <label class="medio-option">
                                    <input type="radio" name="id_metodo" value="<?php echo $m['ID_Metodo']; ?>" class="medio-radio" data-tipo="<?php echo $claseMedio; ?>" data-entidad="<?php echo htmlspecialchars($m['Tipo_Metodo']); ?>" data-logo="<?php echo !empty($m['Logo']) ? '1' : '0'; ?>" <?php echo $metodosFirst ? 'checked' : ''; ?>>
                                    <span class="medio-logo">
                                        <?php if (!empty($m['Logo'])): ?>
                                            <img src="<?php echo htmlspecialchars($m['Logo']); ?>" alt="Logo <?php echo htmlspecialchars($m['Tipo_Metodo']); ?>">
                                        <?php else: ?>
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                        <?php endif; ?>
                                    </span>
                                    <span class="medio-nombre"><?php echo htmlspecialchars($m['Tipo_Metodo']); ?></span>
                                </label>
                                <?php $metodosFirst = false; endforeach; ?>
                            </div>
                            <div id="campoTarjeta" class="pago-campos" style="display:none;">
                                <div class="pago-campo ancho-2-3">
                                    <label>Número de tarjeta</label>
                                    <input type="text" id="numeroTarjeta" name="numero_tarjeta" class="crud-input" inputmode="numeric" maxlength="19" placeholder="4111 1111 1111 1111">
                                </div>
                                <div class="pago-campo ancho-1-3">
                                    <label>CVV</label>
                                    <input type="password" name="cvv" class="crud-input" maxlength="4" inputmode="numeric" placeholder="•••">
                                </div>
                                <div class="pago-campo">
                                    <label>Titular de la tarjeta</label>
                                    <input type="text" name="titular" class="crud-input" placeholder="Nombre como aparece en la tarjeta">
                                </div>
                                <div class="pago-campo ancho-1-2">
                                    <label>Vencimiento</label>
                                    <input type="text" name="fecha_exp" class="crud-input" maxlength="5" placeholder="MM/AA">
                                </div>
                            </div>
                            <div id="campoCuenta" class="pago-campos" style="display:none;">
                                <div class="pago-campo">
                                    <label>Número de cuenta o celular</label>
                                    <input type="text" name="cuenta" class="crud-input" inputmode="numeric" maxlength="20" placeholder="Ej. 3001234567">
                                </div>
                                <small style="color:#a0aec0;">Nequi/Daviplata: exactamente 10 dígitos. La confirmación se envía a tu aplicación bancaria.</small>
                            </div>
                            <div id="campoComprobante" class="pago-campos" style="display:none;">
                                <div class="pago-campo ancho-completo">
                                    <label>Comprobante de transferencia (JPG o PDF, máx. 5MB, obligatorio)</label>
                                    <input type="file" name="comprobante" id="inputComprobante" class="crud-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                </div>
                            </div>
                            <div style="margin-top:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                                <a href="index.php?action=carrito&step=2" style="color:#4a5568;font-weight:700;font-size:13px;text-decoration:none;padding:8px 12px;"><i class="fa-solid fa-arrow-left"></i> Volver</a>
                                <button type="submit" class="btn-crud-save"><i class="fa-solid fa-lock"></i> Confirmar y pagar</button>
                                <small style="color:#a0aec0;">Se crea venta + detalle + pago + factura automática. Stock se descuenta al pagar.</small>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>
    <script>
        const fmtMoney = (v) => '$' + Number(v || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 });
        document.querySelectorAll('.qty-input').forEach(inp => {
            inp.addEventListener('input', () => {
                const q = Math.max(1, Math.min(10, parseInt(inp.value, 10) || 1));
                const cell = document.querySelector('.row-subtotal[data-id="' + inp.dataset.id + '"] strong');
                if (cell) cell.textContent = fmtMoney(q * parseFloat(inp.dataset.price));
            });
        });
        async function postCart(form) {
            const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            return res.json();
        }
        function paintTotals(d) {
            window.updateCartBadge(d.cartCount);
            const t = document.getElementById('cartTotal');
            if (t) t.textContent = fmtMoney(d.total);
            Object.keys(d.items || {}).forEach(id => {
                const cell = document.querySelector('.row-subtotal[data-id="' + id + '"] strong');
                if (cell) cell.textContent = fmtMoney(d.items[id].subtotal);
                const inp = document.querySelector('.qty-input[data-id="' + id + '"]');
                if (inp) inp.value = d.items[id].cantidad;
            });
        }
        document.querySelectorAll('.ajax-cart-update').forEach(f => {
            f.addEventListener('submit', async (e) => {
                e.preventDefault();
                try {
                    const d = await postCart(f);
                    if (d.success) { paintTotals(d); window.acidoToast('Cantidad actualizada', 'ok'); }
                } catch (err) { window.acidoToast('Sin conexión, intenta de nuevo', 'error'); }
            });
        });
        document.querySelectorAll('.ajax-cart-remove').forEach(f => {
            f.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id = f.dataset.id;
                try {
                    const d = await postCart(f);
                    if (d.success) {
                        paintTotals(d);
                        const row = document.getElementById('cartRow-' + id);
                        if (row) { row.classList.add('removing'); setTimeout(() => row.remove(), 300); }
                        window.acidoToast('Producto quitado', 'ok');
                        if ((d.cartCount || 0) === 0) setTimeout(() => window.location.reload(), 400);
                    }
                } catch (err) { window.acidoToast('Sin conexión, intenta de nuevo', 'error'); }
            });
        });
        (function(){
            const radios = document.querySelectorAll('.medio-radio');
            const cajTarjeta = document.getElementById('campoTarjeta');
            const cajCuenta = document.getElementById('campoCuenta');
            const cajComp = document.getElementById('campoComprobante');
            const entidadInput = document.getElementById('entidadInput');
            const numTarjeta = document.getElementById('numeroTarjeta');
            function actualizar() {
                const sel = document.querySelector('.medio-radio:checked');
                if (!sel) return;
                entidadInput.value = sel.dataset.entidad || '';
                const esTransfer = (sel.dataset.entidad || '').toLowerCase().indexOf('transfer') !== -1;
                const req = (box, on) => box.querySelectorAll('input').forEach(i => {
                    if (on) i.setAttribute('required', 'required');
                    else i.removeAttribute('required');
                });
                if (cajComp) {
                    cajComp.style.display = esTransfer ? 'grid' : 'none';
                    req(cajComp, esTransfer);
                }
                if (sel.dataset.tipo === 'tarjeta') {
                    cajTarjeta.style.display = 'grid';
                    cajCuenta.style.display = 'none';
                    req(cajTarjeta, true);
                    req(cajCuenta, false);
                } else if (sel.dataset.logo === '1') {
                    cajTarjeta.style.display = 'none';
                    cajCuenta.style.display = 'grid';
                    req(cajCuenta, true);
                    req(cajTarjeta, false);
                } else {
                    cajTarjeta.style.display = 'none';
                    cajCuenta.style.display = 'none';
                    req(cajTarjeta, false);
                    req(cajCuenta, false);
                }
            }
            radios.forEach(r => r.addEventListener('change', actualizar));
            if (numTarjeta) numTarjeta.addEventListener('input', function() {
                const dig = this.value.replace(/\D/g, '').slice(0, 16);
                this.value = dig.replace(/(\d{4})(?=\d)/g, '$1 ');
            });
            actualizar();
        })();
    </script>
    <script src="/ACIDO/BACKPHP_TEST/public/js/notif-stock.js?v=<?php echo time(); ?>"></script>
</body>
</html>
