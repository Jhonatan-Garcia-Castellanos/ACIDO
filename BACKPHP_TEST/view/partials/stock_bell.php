<?php
// view/partials/stock_bell.php — Campana de stock bajo compartida (RF 2.3).
// Requiere: $alertCount (opcional, default 0; lo provee index.php vía stockBellData()).
// Visible solo para Administrador/Empleado. El conteo y la lista se
// auto-actualizan vía public/js/notif-stock.js (api_alertas cada 60s).
$__sbRol = $_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente';
if (in_array($__sbRol, ['Administrador', 'Empleado'], true)):
$alertCount = $alertCount ?? 0;
?>
<div class="icon-badge" id="stockBell" title="Alertas de stock bajo" style="position:relative;">
    <i class="fa-solid fa-bell"></i>
    <span class="badge red" id="stockBadge" data-count="<?php echo (int)$alertCount; ?>" style="<?php echo ((int)$alertCount > 0) ? '' : 'display:none;'; ?>"><?php echo ((int)$alertCount > 9) ? '9+' : (int)$alertCount; ?></span>
    <div id="stockDropdown" class="dropdown-menu-user" style="display:none;position:absolute;right:0;top:28px;background:#fff;color:#2d3748;border-radius:10px;box-shadow:0 15px 40px rgba(0,0,0,.25);width:340px;max-height:380px;overflow:auto;z-index:9999;">
        <div style="padding:12px 14px;font-weight:800;border-bottom:1px solid #edf2f7;"><i class="fa-solid fa-triangle-exclamation" style="color:#e53e3e;"></i> Stock bajo (<?php echo (int)$alertCount; ?>) — <a href="index.php?action=inventario&filtro=bajo" style="font-size:12px;">Ver</a></div>
        <div id="stockDropdownList"><div style="padding:14px;color:#718096;font-size:13px;">Cargando…</div></div>
    </div>
</div>
<?php endif; ?>
