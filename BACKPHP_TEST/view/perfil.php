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
            <a href="index.php?action=pqr" class="nav-item"><i class="fa-solid fa-headset"></i><span class="sidebar-text">PQR y Ayuda</span></a>
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
                    <span id="liveClock" title="Hora del sistema" style="font-size:12px;font-weight:700;color:#fff;background:rgba(255,255,255,.15);padding:6px 10px;border-radius:6px;">--:--:--</span>
                    <?php require_once __DIR__ . "/partials/stock_bell.php"; ?>
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
                            <form method="POST" action="index.php?action=profile" id="fotoForm" enctype="multipart/form-data" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                <input type="hidden" name="photo_action" value="upload">
                                <?php echo class_exists('Csrf') ? Csrf::field() : ''; ?>
                                <input type="file" name="foto" id="fotoInput" accept=".jpg,.jpeg,.png,.webp" required style="font-size:12px;max-width:220px;">
                                <button type="submit" class="btn-action-edit">Subir foto</button>
                                <small id="cropHint" style="display:none;width:100%;color:#2f855a;font-size:11px;font-weight:700;">Al elegir la imagen podrás encuadrarla a tu gusto antes de guardar.</small>
                                <div id="fotoPreview" style="display:none;margin-top:8px;">
                                    <small style="color:#718096;">Vista previa:</small><br>
                                    <img id="fotoPreviewImg" alt="Vista previa" style="max-width:120px;border-radius:8px;margin-top:4px;">
                                </div>
                                <script>
                                (function(){
                                    const fi = document.getElementById('fotoInput');
                                    if (fi) fi.addEventListener('change', () => {
                                        const f = fi.files && fi.files[0];
                                        const box = document.getElementById('fotoPreview');
                                        const img = document.getElementById('fotoPreviewImg');
                                        if (f && img && box) {
                                            img.src = URL.createObjectURL(f);
                                            box.style.display = 'block';
                                        } else if (box) { box.style.display = 'none'; }
                                    });
                                })();
                                </script>
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
    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>

    <!-- MODAL DE ENCUADRE DE FOTO (recorte cuadrado 512px con zoom y arrastre) -->
    <div id="cropModal" style="display:none;position:fixed;inset:0;background:rgba(28,59,74,.6);z-index:99999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:14px;max-width:340px;width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.35);">
            <div style="background:linear-gradient(135deg,#1C3B4A,#004BA0);color:#fff;padding:14px 18px;display:flex;align-items:center;gap:10px;">
                <i class="fa-solid fa-crop-simple"></i><strong style="font-size:14px;">Encuadra tu foto</strong>
            </div>
            <div style="padding:18px;">
                <div id="cropView" style="width:260px;height:260px;margin:0 auto;position:relative;overflow:hidden;background:#1a202c;border-radius:12px;cursor:grab;touch-action:none;">
                    <img id="cropImg" alt="" draggable="false" style="position:absolute;max-width:none;user-select:none;">
                    <div style="position:absolute;inset:0;pointer-events:none;display:flex;align-items:center;justify-content:center;">
                        <div style="width:220px;height:220px;border:2px dashed rgba(255,255,255,.9);border-radius:50%;box-shadow:0 0 0 999px rgba(0,0,0,.45);"></div>
                    </div>
                </div>
                <p style="font-size:11px;color:#718096;text-align:center;margin:10px 0 6px;">Arrastra para mover · ajusta el zoom · el círculo es tu foto</p>
                <div style="display:flex;align-items:center;gap:10px;justify-content:center;">
                    <button type="button" id="zoomOut" class="btn-action-edit" style="padding:6px 12px;" title="Reducir"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                    <input type="range" id="zoomRange" min="1" max="3" step="0.01" value="1" style="flex:1;" aria-label="Zoom">
                    <button type="button" id="zoomIn" class="btn-action-edit" style="padding:6px 12px;" title="Ampliar"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                </div>
                <div style="display:flex;gap:10px;margin-top:14px;">
                    <button type="button" id="cropCancel" class="btn-crud-cancel" style="flex:1;">Cancelar</button>
                    <button type="button" id="cropSave" class="btn-crud-save" style="flex:1;"><i class="fa-solid fa-check"></i> Guardar foto</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const S = 260, OUT = 512;
        const form = document.getElementById('fotoForm');
        const fileInput = document.getElementById('fotoInput');
        const modal = document.getElementById('cropModal');
        const view = document.getElementById('cropView');
        const img = document.getElementById('cropImg');
        const range = document.getElementById('zoomRange');
        if (!form || !fileInput || !modal || !view || !img || !range) return;
        // Con JS el envío directo se reemplaza por encuadre (sin JS sigue funcionando)
        const directBtn = form.querySelector('button[type=submit]');
        if (directBtn) directBtn.style.display = 'none';
        const hint = document.getElementById('cropHint');
        if (hint) hint.style.display = 'block';

        let baseW = 0, baseH = 0, zoom = 1, x = 0, y = 0, objUrl = null;

        function clamp() {
            const w = baseW * zoom, h = baseH * zoom;
            const mx = Math.max(0, (w - S) / 2), my = Math.max(0, (h - S) / 2);
            x = Math.min(mx, Math.max(-mx, x));
            y = Math.min(my, Math.max(-my, y));
        }
        function paint() {
            clamp();
            const w = baseW * zoom, h = baseH * zoom;
            img.style.width = w + 'px';
            img.style.height = h + 'px';
            img.style.left = ((S - w) / 2 + x) + 'px';
            img.style.top = ((S - h) / 2 + y) + 'px';
        }
        function setZoom(z) {
            zoom = Math.min(3, Math.max(1, z));
            range.value = String(zoom);
            paint();
        }
        function openCrop(file) {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (['jpg', 'jpeg', 'png', 'webp'].indexOf(ext) === -1) {
                window.acidoToast('Formato inválido: solo JPG, PNG o WEBP', 'error');
                fileInput.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                window.acidoToast('La imagen supera 2MB', 'error');
                fileInput.value = '';
                return;
            }
            if (objUrl) URL.revokeObjectURL(objUrl);
            objUrl = URL.createObjectURL(file);
            zoom = 1; x = 0; y = 0; range.value = '1';
            img.onload = function () {
                const k = S / Math.min(img.naturalWidth, img.naturalHeight);
                baseW = img.naturalWidth * k;
                baseH = img.naturalHeight * k;
                paint();
                modal.style.display = 'flex';
            };
            img.src = objUrl;
        }
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files[0]) openCrop(fileInput.files[0]);
        });

        // Arrastre con mouse y táctil
        let drag = null;
        view.addEventListener('pointerdown', function (e) {
            drag = { sx: e.clientX - x, sy: e.clientY - y };
            try { view.setPointerCapture(e.pointerId); } catch (err) {}
            view.style.cursor = 'grabbing';
        });
        view.addEventListener('pointermove', function (e) {
            if (!drag) return;
            x = e.clientX - drag.sx;
            y = e.clientY - drag.sy;
            paint();
        });
        ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (ev) {
            view.addEventListener(ev, function () { drag = null; view.style.cursor = 'grab'; });
        });
        view.addEventListener('wheel', function (e) {
            e.preventDefault();
            setZoom(zoom * (e.deltaY < 0 ? 1.1 : 0.9));
        }, { passive: false });
        range.addEventListener('input', function () { setZoom(parseFloat(range.value)); });
        document.getElementById('zoomIn').addEventListener('click', function () { setZoom(zoom + 0.2); });
        document.getElementById('zoomOut').addEventListener('click', function () { setZoom(zoom - 0.2); });

        function closeCrop() { modal.style.display = 'none'; fileInput.value = ''; }
        document.getElementById('cropCancel').addEventListener('click', closeCrop);
        modal.addEventListener('click', function (e) { if (e.target === modal) closeCrop(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.style.display === 'flex') closeCrop(); });

        // Recorta a 512x512 y sube al endpoint existente (photo_action=upload)
        document.getElementById('cropSave').addEventListener('click', function () {
            const btn = document.getElementById('cropSave');
            btn.disabled = true;
            try {
                const dispW = baseW * zoom, dispH = baseH * zoom;
                const left = (S - dispW) / 2 + x, top = (S - dispH) / 2 + y;
                const fx = img.naturalWidth / dispW, fy = img.naturalHeight / dispH;
                const sx = Math.max(0, -left * fx), sy = Math.max(0, -top * fy);
                const sw = Math.min(img.naturalWidth - sx, S * fx);
                const sh = Math.min(img.naturalHeight - sy, S * fy);
                const cv = document.createElement('canvas');
                cv.width = OUT; cv.height = OUT;
                cv.getContext('2d').drawImage(img, sx, sy, sw, sh, 0, 0, OUT, OUT);
                cv.toBlob(function (blob) {
                    if (!blob) {
                        window.acidoToast('No se pudo procesar la imagen', 'error');
                        btn.disabled = false;
                        return;
                    }
                    const fd = new FormData();
                    fd.append('photo_action', 'upload');
                    const csrf = form.querySelector('input[name=csrf_token]');
                    if (csrf) fd.append('csrf_token', csrf.value);
                    fd.append('foto', blob, 'perfil.jpg');
                    fetch(form.action, { method: 'POST', body: fd })
                        .then(function (res) { window.location.href = res.url; })
                        .catch(function () {
                            window.acidoToast('Sin conexión, intenta de nuevo', 'error');
                            btn.disabled = false;
                        });
                }, 'image/jpeg', 0.92);
            } catch (err) {
                window.acidoToast('No se pudo procesar la imagen', 'error');
                btn.disabled = false;
            }
        });
    })();
    </script>
    <script src="/ACIDO/BACKPHP_TEST/public/js/notif-stock.js?v=<?php echo time(); ?>"></script>
</body>
</html>
