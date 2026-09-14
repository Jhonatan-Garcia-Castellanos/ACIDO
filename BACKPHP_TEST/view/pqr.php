<?php
// view/pqr.php — Módulo PQR + Valoraciones (RF 3.1-3.3, 4.8).
// Vista pura MVC: $misPqr/$porCalificar/$misResenas/$panelPqr los provee index.php.
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["user"])) {
    header("Location: index.php?action=login");
    exit();
}
$__rol = $_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente';
$__esGest = in_array($__rol, ['Administrador', 'Empleado'], true);
$__isAdmin = ($__rol === 'Administrador'); // Panel PQR solo Admin (Empleado = como Cliente)
if (!isset($misPqr)) { $misPqr = []; }
if (!isset($misTotalPqr)) { $misTotalPqr = count($misPqr); }
if (!isset($porCalificar)) { $porCalificar = []; }
if (!isset($misResenas)) { $misResenas = []; }
if (!isset($tabPqr)) { $tabPqr = 'nuevo'; }
if (!isset($panelPqr)) { $panelPqr = []; }
if (!isset($panelTotalPqr)) { $panelTotalPqr = 0; }
if (!isset($panelPages)) { $panelPages = 1; }
$pqPage = $pqPage ?? 1; $pqPer = $pqPer ?? 10; $pqQ = $pqQ ?? '';
$pqFiltro = $pqFiltro ?? ''; $pqDesde = $pqDesde ?? ''; $pqHasta = $pqHasta ?? '';
$estColor = function ($e) {
    return $e === 'Cerrado' ? '#c6f6d5;#22543d' : ($e === 'En Proceso' ? '#fefcbf;#744210' : '#bee3f8;#2a4365');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQR y Valoraciones - ÁCIDO COLOMBIA</title>
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
            <a href="index.php?action=ventas" class="nav-item"><i class="fa-solid fa-receipt"></i><span class="sidebar-text"><?php echo $__esGest?'Ventas':'Mis compras'; ?></span></a>
            <a href="index.php?action=pqr" class="nav-item active"><i class="fa-solid fa-headset"></i><span class="sidebar-text">PQR y Ayuda</span></a>
            <?php if ($__esGest): ?>
            <div class="sidebar-heading sidebar-text" style="margin-top:12px;">GESTIÓN</div>
            <a href="index.php?action=inventario" class="nav-item"><i class="fa-solid fa-boxes-stacked"></i><span class="sidebar-text">Inventario</span></a>
            <a href="index.php?action=crud" class="nav-item"><i class="fa-solid fa-users"></i><span class="sidebar-text">Usuarios</span></a>
            <?php endif; ?>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar"><input type="text" placeholder="Buscar..."><button><i class="fa-solid fa-magnifying-glass"></i></button></div>
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
                            <?php $___avNb = $_SESSION["user"]["nombre_completo"] ?? $_SESSION["user"]["nombre"] ?? 'U'; $___avNbT = trim((string)$___avNb); $___avIni = $___avNbT !== '' ? strtoupper(substr($___avNbT, 0, 1)) : 'U'; $___avFoto = $_SESSION["user"]["foto"] ?? $_SESSION["user"]["Foto"] ?? null; ?>
                            <div class="avatar" title="<?php echo htmlspecialchars($___avNbT); ?>"><?php if (!empty($___avFoto)): ?><img src="<?php echo htmlspecialchars($___avFoto); ?>" alt="Foto de perfil"><?php else: ?><?php echo htmlspecialchars($___avIni); ?><?php endif; ?></div>
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
                <div class="page-header"><h2>PQR Y VALORACIONES</h2><small style="color:#718096;">Quejas, reclamos, solicitudes y estrellas</small></div>
                <?php $flashErr = $_GET['error'] ?? ''; $flashOk = $_GET['status'] ?? ''; ?>
                <?php if ($flashErr !== ''): ?><div style="background:#fed7d7;color:#9b2c2c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;"><?php echo htmlspecialchars($flashErr); ?></div><?php endif; ?>
                <?php if ($flashOk !== ''): ?><div style="background:#c6f6d5;color:#22543d;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;"><?php echo htmlspecialchars($flashOk); ?></div><?php endif; ?>
                <div class="crud-modern-card">
                    <div class="crud-modern-header">
                        <i class="fa-solid fa-headset"></i><span>Centro de ayuda</span>
                        <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="index.php?action=pqr&tab=nuevo" class="btn-crud-save" style="text-decoration:none;<?php echo $tabPqr==='nuevo'?'':'opacity:.6;'; ?>">Nuevo PQR</a>
                            <a href="index.php?action=pqr&tab=mis" class="btn-crud-save" style="text-decoration:none;<?php echo $tabPqr==='mis'?'':'opacity:.6;'; ?>">Mis PQR (<?php echo (int)$misTotalPqr; ?>)</a>
                            <a href="index.php?action=pqr&tab=resenas" class="btn-crud-save" style="text-decoration:none;<?php echo $tabPqr==='resenas'?'':'opacity:.6;'; ?>">Valorar (★)</a>
                            <?php if ($__isAdmin): ?>
                            <a href="index.php?action=pqr&tab=panel" class="btn-crud-save" style="text-decoration:none;<?php echo $tabPqr==='panel'?'':'opacity:.6;'; ?>">Panel admin (<?php echo (int)$panelTotalPqr; ?>)</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($tabPqr === 'mis'): ?>
                    <div style="overflow-x:auto;">
                        <table class="crud-table">
                            <thead><tr><th>ID</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th>Caso</th><th>Respuesta</th></tr></thead>
                            <tbody>
                                <?php if (!empty($misPqr)): foreach ($misPqr as $m): ?>
                                <?php $cc = explode(';', $estColor($m['Estado'])); ?>
                                <tr>
                                    <td style="font-weight:bold;">#<?php echo $m['ID_Pqr']; ?></td>
                                    <td><?php echo htmlspecialchars(substr($m['Fecha_Registro'], 0, 16)); ?></td>
                                    <td><?php echo htmlspecialchars($m['Tipo']); ?></td>
                                    <td><span style="background:<?php echo $cc[0]; ?>;color:<?php echo $cc[1]; ?>;padding:4px 8px;border-radius:4px;font-weight:700;"><?php echo htmlspecialchars($m['Estado']); ?></span></td>
                                    <td><?php echo htmlspecialchars($m['Descripcion']); ?></td>
                                    <td><?php echo $m['Respuesta'] !== null && $m['Respuesta'] !== '' ? htmlspecialchars($m['Respuesta']) : '<small style="color:#a0aec0;">Sin respuesta aún</small>'; ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="6" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Sin PQR. Crea el primero en la pestaña Nuevo PQR.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif ($tabPqr === 'resenas'): ?>
                    <div style="padding:16px;">
                        <h3 style="margin:0 0 12px;text-align:center;">Califica tus compras (1 a 5 ★, una por producto)</h3>
                        <?php if (!empty($porCalificar)): ?>
                        <form method="POST" action="index.php?action=pqr" style="max-width:560px;margin:0 auto;background:#f8f9fc;border:1px solid #e3e6f0;border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:14px;">
                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                            <input type="hidden" name="rate_action" value="calificar">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;text-align:center;">PRODUCTO COMPRADO</label>
                                <select name="id_producto" class="crud-input" required style="width:100%;text-align:center;">
                                    <?php foreach ($porCalificar as $pc): ?>
                                    <option value="<?php echo $pc['ID_Producto']; ?>"><?php echo htmlspecialchars($pc['Nombre_Producto']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="text-align:center;">
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:8px;">ESTRELLAS (TOCA SEGÚN TU SATISFACCIÓN)</label>
                                <span id="starPicker" style="font-size:34px;cursor:pointer;user-select:none;letter-spacing:8px;line-height:1;" title="1 = muy insatisfecho, 5 = muy satisfecho"><i class="fa-solid fa-star star" data-v="1"></i><i class="fa-solid fa-star star" data-v="2"></i><i class="fa-solid fa-star star" data-v="3"></i><i class="fa-solid fa-star star" data-v="4"></i><i class="fa-solid fa-star star" data-v="5"></i></span>
                                <input type="hidden" name="estrellas" id="estrellasInput" value="0">
                                <small id="starHint" style="display:block;color:#a0aec0;margin-top:6px;">1 = muy insatisfecho … 5 = muy satisfecho</small>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;text-align:center;">COMENTARIO (OPCIONAL)</label>
                                <input type="text" name="comentario" class="crud-input" maxlength="500" placeholder="¿Qué te pareció?" style="width:100%;text-align:center;">
                            </div>
                            <button type="submit" class="btn-crud-save" style="width:100%;padding:12px;font-size:14px;">Calificar</button>
                        </form>
                        <?php else: ?>
                        <p style="color:#a0aec0;">No tienes productos por calificar. Solo se pueden calificar compras realizadas y una vez por producto. <a href="index.php?action=catalogo">Ir al catálogo</a></p>
                        <?php endif; ?>
                        <h3 style="margin:20px 0 12px;">Mis valoraciones (historial)</h3>
                        <?php if (!empty($misResenas)): ?>
                        <div style="overflow-x:auto;">
                            <table class="crud-table">
                                <thead><tr><th>Producto</th><th>Estrellas</th><th>Comentario</th><th>Fecha</th><th>Moderación</th></tr></thead>
                                <tbody>
                                    <?php foreach ($misResenas as $mr): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($mr['Nombre_Producto']); ?></strong></td>
                                        <td style="color:#d69e2e;font-weight:800;white-space:nowrap;"><?php echo str_repeat('★', (int)$mr['Calificacion']) . str_repeat('☆', 5 - (int)$mr['Calificacion']); ?> (<?php echo (int)$mr['Calificacion']; ?>)</td>
                                        <td><?php echo htmlspecialchars($mr['Comentario'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars(substr($mr['Fecha_Publicacion'], 0, 16)); ?></td>
                                        <td><?php echo htmlspecialchars($mr['Estado_Moderacion']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p style="color:#a0aec0;">Aún no calificas nada.</p>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($tabPqr === 'panel' && $__isAdmin): ?>
                    <form method="GET" action="index.php" style="display:flex;gap:8px;padding:12px 16px;flex-wrap:wrap;align-items:end;">
                        <input type="hidden" name="action" value="pqr">
                        <input type="hidden" name="tab" value="panel">
                        <div><label style="display:block;font-size:11px;font-weight:700;color:#4a5568;">ESTADO</label>
                            <select name="filtro" class="crud-input">
                                <option value="">Todos</option>
                                <option value="pend" <?php echo $pqFiltro === 'pend' ? 'selected' : ''; ?>>Pendientes (Abierto+En proceso)</option>
                                <option value="res" <?php echo $pqFiltro === 'res' ? 'selected' : ''; ?>>Resueltos (Cerrado)</option>
                                <?php foreach (['Abierto', 'En Proceso', 'Cerrado'] as $e): ?>
                                <option value="<?php echo $e; ?>" <?php echo $pqFiltro === $e ? 'selected' : ''; ?>><?php echo $e; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div><label style="display:block;font-size:11px;font-weight:700;color:#4a5568;">DESDE</label><input type="date" name="desde" class="crud-input" value="<?php echo htmlspecialchars($pqDesde); ?>"></div>
                        <div><label style="display:block;font-size:11px;font-weight:700;color:#4a5568;">HASTA</label><input type="date" name="hasta" class="crud-input" value="<?php echo htmlspecialchars($pqHasta); ?>"></div>
                        <div><label style="display:block;font-size:11px;font-weight:700;color:#4a5568;">BUSCAR</label><input type="text" name="q" placeholder="ID, caso, cliente..." class="crud-input" value="<?php echo htmlspecialchars($pqQ); ?>"></div>
                        <button type="submit" class="btn-crud-save">Filtrar</button>
                        <a href="index.php?action=pqr&tab=panel" class="btn-crud-cancel" style="text-decoration:none;">Limpiar</a>
                        <span style="margin-left:auto;display:flex;gap:8px;">
                            <a href="index.php?action=pqr_csv&filtro=<?php echo urlencode($pqFiltro); ?>&desde=<?php echo urlencode($pqDesde); ?>&hasta=<?php echo urlencode($pqHasta); ?>&q=<?php echo urlencode($pqQ); ?>" class="btn-crud-save" style="text-decoration:none;"><i class="fa-solid fa-file-csv"></i> CSV</a>
                            <a href="index.php?action=pqr_pdf&filtro=<?php echo urlencode($pqFiltro); ?>&desde=<?php echo urlencode($pqDesde); ?>&hasta=<?php echo urlencode($pqHasta); ?>&q=<?php echo urlencode($pqQ); ?>" class="btn-crud-save" style="text-decoration:none;"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                        </span>
                    </form>
                    <div style="overflow-x:auto;">
                        <table class="crud-table">
                            <thead><tr><th>ID</th><th>Fecha</th><th>Cliente</th><th>Tipo</th><th>Estado</th><th>Caso</th><th style="text-align:right;">Gestión</th></tr></thead>
                            <tbody>
                                <?php if (!empty($panelPqr)): foreach ($panelPqr as $pp): ?>
                                <?php $cc2 = explode(';', $estColor($pp['Estado'])); ?>
                                <tr>
                                    <td style="font-weight:bold;">#<?php echo $pp['ID_Pqr']; ?></td>
                                    <td><?php echo htmlspecialchars(substr($pp['Fecha_Registro'], 0, 16)); ?></td>
                                    <td><?php echo htmlspecialchars(trim(($pp['Nombres'] ?? '') . ' ' . ($pp['Apellidos'] ?? ''))); ?><br><small style="color:#a0aec0;"><?php echo htmlspecialchars($pp['ClienteEmail'] ?? ''); ?></small></td>
                                    <td><?php echo htmlspecialchars($pp['Tipo']); ?></td>
                                    <td><span style="background:<?php echo $cc2[0]; ?>;color:<?php echo $cc2[1]; ?>;padding:4px 8px;border-radius:4px;font-weight:700;"><?php echo htmlspecialchars($pp['Estado']); ?></span></td>
                                    <td style="max-width:280px;"><?php echo htmlspecialchars($pp['Descripcion']); ?><?php if (!empty($pp['Respuesta'])): ?><br><small style="color:#22543d;"><b>Rta:</b> <?php echo htmlspecialchars($pp['Respuesta']); ?></small><?php endif; ?></td>
                                    <td style="text-align:right;">
                                        <form method="POST" action="index.php?action=pqr" style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                            <input type="hidden" name="pqr_action" value="responder">
                                            <input type="hidden" name="id_pqr" value="<?php echo $pp['ID_Pqr']; ?>">
                                            <input type="hidden" name="page" value="<?php echo (int)$pqPage; ?>">
                                            <input type="hidden" name="per" value="<?php echo (int)$pqPer; ?>">
                                            <input type="hidden" name="q" value="<?php echo htmlspecialchars($pqQ, ENT_QUOTES); ?>">
                                            <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($pqFiltro, ENT_QUOTES); ?>">
                                            <input type="hidden" name="desde" value="<?php echo htmlspecialchars($pqDesde, ENT_QUOTES); ?>">
                                            <input type="hidden" name="hasta" value="<?php echo htmlspecialchars($pqHasta, ENT_QUOTES); ?>">
                                            <input type="text" name="respuesta" class="crud-input" placeholder="Respuesta..." required maxlength="1000" style="min-width:160px;" value="<?php echo htmlspecialchars($pp['Respuesta'] ?? '', ENT_QUOTES); ?>">
                                            <select name="estado" class="crud-input" required>
                                                <option value="En Proceso" <?php echo ($pp['Estado'] === 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                                <option value="Cerrado" <?php echo ($pp['Estado'] === 'Cerrado') ? 'selected' : ''; ?>>Cerrado</option>
                                            </select>
                                            <button type="submit" class="btn-crud-save">Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="7" style="text-align:center;color:#a0aec0;padding:40px;font-style:italic;">Sin PQR con esos filtros.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="display:flex;gap:6px;align-items:center;justify-content:center;padding:14px;flex-wrap:wrap;">
                        <?php $pqQs = function ($p) use ($pqPer, $pqQ, $pqFiltro, $pqDesde, $pqHasta) { return 'index.php?action=pqr&tab=panel&' . http_build_query(['page' => $p, 'per' => $pqPer, 'q' => $pqQ, 'filtro' => $pqFiltro, 'desde' => $pqDesde, 'hasta' => $pqHasta]); }; ?>
                        <?php if ($pqPage > 1): ?><a href="<?php echo $pqQs($pqPage - 1); ?>" class="btn-crud-cancel" style="text-decoration:none;">« Anterior</a><?php endif; ?>
                        <?php for ($p = max(1, $pqPage - 2); $p <= min($panelPages, $pqPage + 2); $p++): ?>
                            <a href="<?php echo $pqQs($p); ?>" class="btn-crud-save" style="text-decoration:none;<?php echo ($p === (int)$pqPage) ? '' : 'opacity:.55;'; ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                        <?php if ($pqPage < $panelPages): ?><a href="<?php echo $pqQs($pqPage + 1); ?>" class="btn-crud-cancel" style="text-decoration:none;">Siguiente »</a><?php endif; ?>
                        <small style="color:#a0aec0;">Pág. <?php echo (int)$pqPage; ?> de <?php echo (int)$panelPages; ?> · <?php echo (int)$panelTotalPqr; ?> casos</small>
                    </div>
                    <?php else: ?>
                    <div style="padding:16px;max-width:640px;">
                        <h3 style="margin:0 0 12px;">Radicar queja, reclamo o solicitud</h3>
                        <form method="POST" action="index.php?action=pqr">
                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                            <input type="hidden" name="pqr_action" value="crear">
                            <div style="margin-bottom:12px;">
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">TIPO</label>
                                <select name="tipo" class="crud-input" required style="width:100%;">
                                    <option value="Solicitud">Solicitud</option>
                                    <option value="Queja">Queja</option>
                                    <option value="Reclamo">Reclamo</option>
                                </select>
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">DESCRIPCIÓN (10-1000 CARACTERES)</label>
                                <textarea name="descripcion" class="crud-input" required minlength="10" maxlength="1000" rows="5" placeholder="Cuéntanos tu caso con detalle..." style="width:100%;resize:vertical;"></textarea>
                            </div>
                            <button type="submit" class="btn-crud-save">Radicar PQR</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>
    <script>
        // Selector de estrellas 1-5 (amarillas según satisfacción)
        (function () {
            var picker = document.getElementById('starPicker');
            var input = document.getElementById('estrellasInput');
            var hint = document.getElementById('starHint');
            if (!picker || !input) return;
            var ON = '#f6c23e', OFF = '#d1d3e2';
            var TXT = { 1: '1 ★ = muy insatisfecho', 2: '2 ★★ = insatisfecho', 3: '3 ★★★ = aceptable', 4: '4 ★★★★ = satisfecho', 5: '5 ★★★★★ = muy satisfecho' };
            var stars = picker.querySelectorAll('.star');
            function paint(n) {
                stars.forEach(function (s) {
                    s.style.color = (parseInt(s.dataset.v, 10) <= n) ? ON : OFF;
                });
                if (hint && TXT[n]) hint.textContent = TXT[n];
            }
            paint(0);
            stars.forEach(function (s) {
                s.addEventListener('mouseenter', function () { paint(parseInt(s.dataset.v, 10)); });
                s.addEventListener('click', function () {
                    input.value = s.dataset.v;
                    paint(parseInt(s.dataset.v, 10));
                });
            });
            picker.addEventListener('mouseleave', function () { paint(parseInt(input.value || '0', 10)); });
            var form = picker.closest('form');
            if (form) form.addEventListener('submit', function (e) {
                if (parseInt(input.value || '0', 10) < 1) {
                    e.preventDefault();
                    if (window.acidoToast) window.acidoToast('Toca las estrellas para calificar (1 a 5).', 'error');
                    else if (hint) hint.textContent = 'Toca las estrellas para calificar (1 a 5).';
                }
            });
        })();
    </script>
    <script src="/ACIDO/BACKPHP_TEST/public/js/notif-stock.js?v=<?php echo time(); ?>"></script>
</body>
</html>
