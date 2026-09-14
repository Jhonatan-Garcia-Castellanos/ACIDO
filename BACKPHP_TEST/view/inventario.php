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
                    <span id="liveClock" title="Hora del sistema" style="font-size:12px;font-weight:700;color:#fff;background:rgba(255,255,255,.15);padding:6px 10px;border-radius:6px;">--:--:--</span>
                    <?php $alertCount = $alertCount ?? 0; $alertItems = $alertItems ?? []; ?>
                    <div class="icon-badge" id="stockBell" title="Alertas de stock bajo" style="position:relative;">
                        <i class="fa-solid fa-bell"></i>
                        <span class="badge red" id="stockBadge" data-count="<?php echo (int)$alertCount; ?>" style="<?php echo ((int)$alertCount > 0) ? '' : 'display:none;'; ?>"><?php echo ((int)$alertCount > 9) ? '9+' : (int)$alertCount; ?></span>
                        <div id="stockDropdown" class="dropdown-menu-user" style="display:none;position:absolute;right:0;top:28px;background:#fff;color:#2d3748;border-radius:10px;box-shadow:0 15px 40px rgba(0,0,0,.25);width:340px;max-height:380px;overflow:auto;z-index:9999;">
                            <div style="padding:12px 14px;font-weight:800;border-bottom:1px solid #edf2f7;display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="fa-solid fa-triangle-exclamation" style="color:#e53e3e;"></i> Stock bajo (<?php echo (int)$alertCount; ?>)</span>
                                <a href="index.php?action=inventario&filtro=bajo" style="font-size:12px;">Ver bajos</a>
                            </div>
                            <div id="stockDropdownList">
                                <?php if (!empty($alertItems)): ?>
                                    <?php foreach ($alertItems as $a): ?>
                                        <div style="padding:10px 14px;border-bottom:1px solid #edf2f7;font-size:13px;">
                                            <div style="font-weight:700;color:#9b2c2c;"><?php echo htmlspecialchars($a['Mensaje'] ?? $a['Nombre_Producto'] ?? 'Stock bajo'); ?></div>
                                            <div style="color:#a0aec0;font-size:12px;"><?php echo htmlspecialchars($a['Fecha_Creacion'] ?? ''); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="padding:14px;color:#718096;font-size:13px;">Sin alertas pendientes.</div>
                                <?php endif; ?>
                            </div>
                            <?php if ((int)$alertCount > 0): ?>
                            <form method="POST" action="index.php?action=inventario" style="padding:10px 14px;">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="hidden" name="alerta_action" value="todas">
                                <input type="hidden" name="back" value="index.php?action=inventario">
                                <button type="submit" class="btn-crud-cancel" style="width:100%;">Marcar todas como leídas</button>
                            </form>
                            <?php endif; ?>
                        </div>
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
                <div class="page-header">
                    <h2 id="form-title-text">INVENTARIO</h2>
                </div>
                <?php $flashErr = $_GET['error'] ?? ''; $flashOk = $_GET['status'] ?? ''; ?>
                <?php if ($flashErr !== ''): ?>
                    <div class="alert-msg alert-error" style="display:block;">
                        <?php echo htmlspecialchars($flashErr === 'save_fail' ? 'No se pudo guardar. Verifica precio > 0, stock ≥ 0 y categoría/proveedor válidos.' : $flashErr); ?>
                    </div>
                <?php endif; ?>
                <?php if ($flashOk !== ''): ?>
                    <div class="alert-msg alert-success" style="display:block;"><?php echo htmlspecialchars($flashOk); ?></div>
                <?php endif; ?>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-bottom:20px;">
                    <div class="crud-modern-card" style="margin:0;padding:16px;">
                        <small style="color:#4a5568;font-weight:700;">VALORIZACIÓN STOCK</small>
                        <div class="count-up" data-money="1" data-value="<?php echo (float)($resumen['valorizacion'] ?? 0); ?>" style="font-size:22px;font-weight:800;">$<?php echo number_format($resumen['valorizacion'] ?? 0, 0, ',', '.'); ?></div>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;">
                        <small style="color:#4a5568;font-weight:700;">AGOTADOS (stock 0)</small>
                        <div class="count-up" data-value="<?php echo (int)($resumen['agotados'] ?? 0); ?>" style="font-size:22px;font-weight:800;"><?php echo (int)($resumen['agotados'] ?? 0); ?></div>
                    </div>
                    <div class="crud-modern-card" style="margin:0;padding:16px;">
                        <small style="color:#4a5568;font-weight:700;">STOCK BAJO (&lt;10)</small>
                        <div class="count-up" data-value="<?php echo (int)($resumen['stock_bajo'] ?? 0); ?>" style="font-size:22px;font-weight:800;"><?php echo (int)($resumen['stock_bajo'] ?? 0); ?></div>
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
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="hidden" name="ID_Producto" id="form-id">
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr 1fr auto;gap:12px;align-items:start;">
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
                                        <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">STOCK MÍNIMO (RF 2.3)</label>
                                        <input type="number" min="0" class="crud-input" name="Stock_Minimo" id="form-minimo" value="10" required>
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
                                    <th><b>Stock / Mín</b></th>
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
                                                <?php $st = (int)$row['Stock_Actual']; $mn = (int)($row['Stock_Minimo'] ?? 10); ?>
                                                <span style="background:<?php echo $st==0?'#fed7d7;color:#9b2c2c':($st<=$mn?'#fed7d7;color:#9b2c2c':'#c6f6d5;color:#22543d'); ?>;padding:4px 8px;border-radius:4px;font-weight:700;" title="Mínimo: <?php echo $mn; ?>"><?php echo $st; ?> / <?php echo $mn; ?></span>
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
                                                    onclick="editarProducto('<?php echo $row['ID_Producto']; ?>','<?php echo htmlspecialchars($row['Nombre_Producto'], ENT_QUOTES); ?>','<?php echo $row['Precio_Actual']; ?>','<?php echo $row['Stock_Actual']; ?>','<?php echo $row['ID_Categoria']; ?>','<?php echo $row['ID_Proveedor']; ?>','<?php echo (int)($row['Stock_Minimo'] ?? 10); ?>')">
                                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                                </button>
                                                <?php if ($isAdminInv): ?>
                                                <?php if ($pActivo === 1): ?>
                                                <form method="POST" action="index.php?action=inventario" style="display:inline;">
                                                    <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                                    <input type="hidden" name="inv_toggle_id" value="<?php echo htmlspecialchars($row['ID_Producto'], ENT_QUOTES); ?>">
                                                    <input type="hidden" name="estado" value="0">
                                                    <button type="button" class="btn-action-delete"
                                                        onclick="askToggleInv(this, 'Inactivar producto', '¿Quieres inactivar <?php echo htmlspecialchars(str_replace("'", "", $row['Nombre_Producto']), ENT_QUOTES); ?>? No se borra por ley/trigger, solo se desactiva.')">
                                                        <i class="fa-solid fa-ban"></i> Inactivar
                                                    </button>
                                                </form>
                                                <?php else: ?>
                                                <form method="POST" action="index.php?action=inventario" style="display:inline;">
                                                    <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                                    <input type="hidden" name="inv_toggle_id" value="<?php echo htmlspecialchars($row['ID_Producto'], ENT_QUOTES); ?>">
                                                    <input type="hidden" name="estado" value="1">
                                                    <button type="button" class="btn-crud-save"
                                                        onclick="askToggleInv(this, 'Activar producto', '¿Quieres reactivar <?php echo htmlspecialchars(str_replace("'", "", $row['Nombre_Producto']), ENT_QUOTES); ?>? Volverá a estar disponible.')">
                                                        <i class="fa-solid fa-check"></i> Activar
                                                    </button>
                                                </form>
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
                <?php if ($__canEdit && ($__rol === 'Administrador')): ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <span>Ajuste manual — Kardex (RF 2.9: Motivo obligatorio)</span>
                    </div>
                    <form action="index.php?action=inventario" method="POST" style="padding:16px;">
                        <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                        <input type="hidden" name="inv_ajuste" value="1">
                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 2fr auto;gap:12px;align-items:end;">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">PRODUCTO</label>
                                <select class="crud-input" name="aj_producto" required>
                                    <?php foreach (($productos ?? []) as $pr): ?>
                                        <option value="<?php echo $pr['ID_Producto']; ?>"><?php echo htmlspecialchars('#'.$pr['ID_Producto'].' '.$pr['Nombre_Producto'].' (stock '.$pr['Stock_Actual'].')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">TIPO</label>
                                <select class="crud-input" name="aj_tipo" required>
                                    <option value="Entrada">Entrada (suma)</option>
                                    <option value="Ajuste">Ajuste (pérdida/daño/dev.)</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">CANTIDAD</label>
                                <input type="number" min="1" class="crud-input" name="aj_cantidad" value="1" required>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">MOTIVO (obligatorio)</label>
                                <input type="text" class="crud-input" name="aj_motivo" placeholder="Ej: daño en bodega, devolución cliente…" required maxlength="255">
                            </div>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <label style="font-size:12px;"><input type="checkbox" name="es_resta" value="1"> Resta</label>
                                <button type="submit" class="btn-crud-save">Registrar</button>
                            </div>
                        </div>
                        <small style="color:#a0aec0;font-size:11px;">* Entrada siempre suma. Ajuste + "Resta" (o motivo pérdida/daño) resta. Todo queda en Kardex sin edición ni borrado.</small>
                    </form>
                </div>
                <?php endif; ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header">
                        <i class="fa-solid fa-book"></i>
                        <span>Kardex — trazabilidad (RF 2.7: entradas, salidas, ajustes)</span>                        <form method="GET" action="index.php" style="margin-left:auto;display:flex;gap:8px;">
                            <input type="hidden" name="action" value="inventario">
                            <select class="crud-input" name="kardex_prod" onchange="this.form.submit()">
                                <option value="">Todos los productos</option>
                                <?php foreach (($productos ?? []) as $pr): ?>
                                    <option value="<?php echo $pr['ID_Producto']; ?>" <?php echo (isset($filtroKardex) && (string)$filtroKardex === (string)$pr['ID_Producto']) ? 'selected' : ''; ?>><?php echo htmlspecialchars('#'.$pr['ID_Producto'].' '.$pr['Nombre_Producto']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="crud-table">
                            <thead><tr><th><b>Fecha</b></th><th><b>Producto</b></th><th><b>Tipo</b></th><th><b>Cant.</b></th><th><b>Motivo</b></th><th><b>Responsable</b></th></tr></thead>
                            <tbody>
                                <?php if (!empty($kardex)): ?>
                                    <?php foreach ($kardex as $k): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($k['Fecha_Movimiento']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($k['Nombre_Producto'] ?? ''); ?></strong></td>
                                        <td><span style="padding:4px 8px;border-radius:4px;font-weight:700;background:<?php echo $k['Tipo_Movimiento']==='Entrada'?'#c6f6d5':($k['Tipo_Movimiento']==='Salida'?'#fed7d7':'#fefcbf'); ?>;"><?php echo htmlspecialchars($k['Tipo_Movimiento']); ?></span></td>
                                        <td style="font-weight:800;color:<?php echo ((int)$k['Cantidad'] < 0) ? '#c53030' : '#22543d'; ?>"><?php echo (int)$k['Cantidad']; ?></td>
                                        <td><?php echo htmlspecialchars($k['Motivo']); ?></td>
                                        <td><?php echo htmlspecialchars($k['Responsable'] ?? '—'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" style="text-align:center;color:#a0aec0;padding:30px;font-style:italic;">Sin movimientos. Los ajustes y las ventas (salidas) aparecerán aquí.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($__rol === 'Administrador'): ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header">
                        <i class="fa-solid fa-envelope-circle-check"></i>
                        <span>Historial de correos al admin (¿sí llegó la notificación?)</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="crud-table">
                            <thead><tr><th><b>Fecha</b></th><th><b>Tipo</b></th><th><b>Destinatarios</b></th><th><b>Asunto</b></th><th><b>Estado</b></th><th><b>Detalle</b></th></tr></thead>
                            <tbody>
                                <?php $notifLog = $notifLog ?? []; ?>
                                <?php if (!empty($notifLog)): ?>
                                    <?php foreach ($notifLog as $nl): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($nl['Fecha']); ?></td>
                                        <td><?php echo htmlspecialchars($nl['Tipo']); ?></td>
                                        <td style="font-size:12px;"><?php echo htmlspecialchars($nl['Destinatarios']); ?></td>
                                        <td><?php echo htmlspecialchars($nl['Asunto']); ?></td>
                                        <td>
                                            <?php if (($nl['Resultado'] ?? '') === 'ok'): ?>
                                                <span style="background:#c6f6d5;color:#22543d;padding:4px 8px;border-radius:4px;font-weight:700;">ENVIADO</span>
                                            <?php else: ?>
                                                <span style="background:#fed7d7;color:#9b2c2c;padding:4px 8px;border-radius:4px;font-weight:700;">FALLÓ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size:12px;color:#718096;"><?php echo htmlspecialchars($nl['Detalle'] ?? ''); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" style="text-align:center;color:#a0aec0;padding:30px;font-style:italic;">Sin envíos aún. Ejecuta migrate_notif_log.sql si ves este mensaje siempre: activa el historial.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>
    <script>
        function editarProducto(id, nombre, precio, stock, cat, prov, minimo) {
            document.getElementById('form-id').value = id;
            document.getElementById('form-nombre').value = nombre;
            document.getElementById('form-precio').value = precio;
            document.getElementById('form-stock').value = stock;
            document.getElementById('form-cat').value = cat;
            document.getElementById('form-prov').value = prov;
            if (document.getElementById('form-minimo')) document.getElementById('form-minimo').value = (minimo !== undefined ? minimo : 10);
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
            if (document.getElementById('form-minimo')) document.getElementById('form-minimo').value = '10';
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
        // Filtro ?filtro=bajo (desde campana / toast RF 2.3)
        (function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('filtro') === 'bajo') {
                document.querySelectorAll('#invTable tbody tr').forEach(tr => {
                    const t = tr.textContent;
                    const m = t.match(/(\d+)\s*\/\s*(\d+)/);
                    if (m) {
                        const st = parseInt(m[1], 10), mn = parseInt(m[2], 10);
                        tr.style.display = (st <= mn) ? '' : 'none';
                    }
                });
                if (invSearch) { invSearch.value = ''; invSearch.placeholder = 'Filtrando: stock bajo (borra para ver todo)'; }
            }
        })();
        // Modal personalizada ACIDO (inactivar/activar) - envía el form POST del botón
        let confirmFormInv = null;
        function askToggleInv(btn, title, message) {
            confirmFormInv = btn ? btn.closest('form') : null;
            document.getElementById('acidoModalTitleInv').innerText = title;
            document.getElementById('acidoModalMsgInv').innerText = message;
            var btn = document.getElementById('acidoModalConfirmInv');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar';
            btn.className = title.toLowerCase().includes('inactivar') ? 'btn-action-delete' : 'btn-crud-save';
            document.getElementById('acidoModalInv').style.display = 'flex';
        }
        function closeAcidoModalInv() {
            document.getElementById('acidoModalInv').style.display = 'none';
            confirmFormInv = null;
        }
        function confirmAcidoModalInv() {
            if (confirmFormInv) confirmFormInv.submit();
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAcidoModalInv();
        });
    </script>
    <script src="/ACIDO/BACKPHP_TEST/public/js/notif-stock.js?v=<?php echo time(); ?>"></script>
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
