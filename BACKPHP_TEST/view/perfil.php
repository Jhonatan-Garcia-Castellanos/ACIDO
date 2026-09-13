<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["user"])) {
    header("Location: index.php?action=login");
    exit();
}
$u = $_SESSION["user"];
$nombre = $u["nombre_completo"] ?? $u["nombre"] ?? trim(($u["Nombres"] ?? "") . " " . ($u["Apellidos"] ?? "")) ?: explode('@', $u["Email"] ?? $u["email"] ?? '')[0];
$email = $u["Email"] ?? $u["email"] ?? '';
$rol = $u["Rol"] ?? $u["rol"] ?? 'Cliente';
$id = $u["ID_Usuario"] ?? $u["id"] ?? '-';
$foto = $u["foto"] ?? $u["Foto"] ?? null;
$iniPerfil = trim((string)$nombre) !== '' ? (function_exists('mb_strtoupper') ? mb_strtoupper(mb_substr(trim((string)$nombre), 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr(trim((string)$nombre), 0, 1))) : 'U';
$msgOk = $_GET["status"] ?? '';
$msgErr = $_GET["error"] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - ÁCIDO COLOMBIA</title>
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
            <?php if (in_array($rol, ['Administrador','Empleado'], true)): ?>
            <a href="index.php?action=dashboard" class="nav-item"><i class="fa-solid fa-gauge-high"></i><span class="sidebar-text">Dashboard</span></a>
            <hr class="sidebar-divider">
            <?php endif; ?>
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
                            <span class="user-badge"><strong><?php echo htmlspecialchars($nombre); ?></strong><small><?php echo htmlspecialchars($rol); ?></small></span>
                            <?php $___avNb = $nombre ?? $_SESSION["user"]["nombre_completo"] ?? 'U'; $___avNbT = trim((string)$___avNb); $___avIni = $___avNbT !== '' ? (function_exists('mb_strtoupper') ? mb_strtoupper(mb_substr($___avNbT, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr($___avNbT, 0, 1))) : 'U'; $___avFoto = $_SESSION["user"]["foto"] ?? $_SESSION["user"]["Foto"] ?? null; ?>
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
                <div class="page-header"><h2>MI PERFIL</h2></div>
                <?php if ($msgOk): ?><div class="alert-msg alert-success" style="display:block;max-width:560px;"><?php echo htmlspecialchars($msgOk); ?></div><?php endif; ?>
                <?php if ($msgErr): ?><div class="alert-msg alert-error" style="display:block;max-width:560px;"><?php echo htmlspecialchars($msgErr); ?></div><?php endif; ?>
                <div class="crud-modern-card" style="max-width:560px;margin-bottom:16px;">
                    <div class="crud-modern-header"><i class="fa-solid fa-camera"></i><span>Foto de perfil</span></div>
                    <div class="crud-form-body" style="display:flex;gap:16px;align-items:center;">
                        <div class="avatar avatar-big" title="<?php echo htmlspecialchars($nombre); ?>"><?php if (!empty($foto)): ?><img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil"><?php else: ?><?php echo htmlspecialchars($iniPerfil); ?><?php endif; ?></div>
                        <div style="flex:1;min-width:0;">
                            <p style="margin:0 0 8px;font-size:12px;color:#718096;">JPG, PNG o WEBP. Máximo 2MB. Sin foto se muestra la inicial de tu nombre.</p>
                            <form method="POST" action="index.php?action=profile" enctype="multipart/form-data" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                <input type="hidden" name="photo_action" value="upload">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" required style="font-size:12px;max-width:220px;">
                                <button type="submit" class="btn-action-edit">Subir foto</button>
                            </form>
                            <?php if (!empty($foto)): ?>
                            <form method="POST" action="index.php?action=profile" style="margin-top:8px;">
                                <input type="hidden" name="photo_action" value="remove">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <button type="submit" class="btn-crud-cancel">Quitar foto</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="crud-modern-card" style="max-width:560px;margin-bottom:16px;">
                    <div class="crud-modern-header"><i class="fa-solid fa-id-card"></i><span>Mis datos personales</span></div>
                    <div class="crud-form-body">
                        <?php $pd = $perfilDetalle ?? ['documento' => '', 'nombres' => '', 'apellidos' => '', 'telefono' => '']; ?>
                        <form method="POST" action="index.php?action=profile">
                            <input type="hidden" name="profile_action" value="save">
                            <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">DOCUMENTO (6-12 DÍGITOS)</label>
                                    <input type="text" class="crud-input" name="documento" value="<?php echo htmlspecialchars($pd['documento'] ?? ''); ?>" required inputmode="numeric" maxlength="12" style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">TELÉFONO (7 O 10 DÍGITOS)</label>
                                    <input type="text" class="crud-input" name="telefono" value="<?php echo htmlspecialchars($pd['telefono'] ?? ''); ?>" required inputmode="numeric" maxlength="10" style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">NOMBRES (MÁX. 70)</label>
                                    <input type="text" class="crud-input" name="nombres" value="<?php echo htmlspecialchars($pd['nombres'] ?? ''); ?>" required maxlength="70" style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:6px;">APELLIDOS (MÁX. 70)</label>
                                    <input type="text" class="crud-input" name="apellidos" value="<?php echo htmlspecialchars($pd['apellidos'] ?? ''); ?>" required maxlength="70" style="width:100%;">
                                </div>
                            </div>
                            <button type="submit" class="btn-action-edit" style="margin-top:12px;">Guardar mis datos</button>
                        </form>
                        <p style="margin:8px 0 0;font-size:11px;color:#a0aec0;">La foto de perfil se cambia en la tarjeta de arriba. El correo y el rol solo los edita un administrador desde Usuarios.</p>
                    </div>
                </div>
                <div class="crud-modern-card" style="max-width:560px;">
                    <div class="crud-modern-header"><i class="fa-solid fa-user"></i><span>Datos de la cuenta</span></div>
                    <div class="crud-form-body">
                        <p><strong>ID:</strong> <?php echo htmlspecialchars($id); ?></p>
                        <p><strong>Nombre y apellido:</strong> <?php echo htmlspecialchars($nombre); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <p><strong>Rol:</strong> <?php echo htmlspecialchars($rol); ?></p>
                        <div style="margin-top:14px;display:flex;gap:10px;">
                            <a href="index.php?action=change_password" class="btn-action-edit" style="text-decoration:none;">Cambiar contraseña</a>
                            <a href="index.php?action=config" class="btn-crud-cancel" style="text-decoration:none;">Configuración</a>
                        </div>
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
