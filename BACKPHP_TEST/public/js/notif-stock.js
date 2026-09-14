// public/js/notif-stock.js — Polling campana stock bajo (RF 2.3).
// Solo actúa si existen #stockBell/#stockBadge (vistas Admin/Empleado).
(function () {
    var bell = document.getElementById('stockBell');
    var badge = document.getElementById('stockBadge');
    var list = document.getElementById('stockDropdownList');
    if (!bell || !badge) return;
    var lastCount = parseInt(badge.getAttribute('data-count') || '0', 10) || 0;
    var firstPoll = true;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function render(items) {
        if (!list) return;
        if (!items || !items.length) {
            list.innerHTML = '<div style="padding:14px;color:#718096;font-size:13px;">Sin alertas pendientes.</div>';
            return;
        }
        var html = '';
        items.slice(0, 10).forEach(function (a) {
            var msg = a.Mensaje || a.Nombre_Producto || 'Stock bajo';
            var fecha = a.Fecha_Creacion || '';
            var prod = a.Nombre_Producto ? ' · ' + a.Nombre_Producto : '';
            // Click en la alerta -> inventario filtrado (y resaltado si hay ID producto)
            var href = 'index.php?action=inventario&filtro=bajo';
            var pid = parseInt(a.ID_Producto || '0', 10) || 0;
            if (pid > 0) href += '&hl=' + pid;
            html += '<a href="' + href + '" style="display:block;text-decoration:none;padding:10px 14px;border-bottom:1px solid #edf2f7;font-size:13px;" onmouseover="this.style.background=\'#f8f9fc\'" onmouseout="this.style.background=\'\'">'
                + '<div style="font-weight:700;color:#9b2c2c;"><i class="fa-solid fa-triangle-exclamation"></i> ' + esc(msg) + '</div>'
                + '<div style="color:#a0aec0;font-size:12px;">' + esc(fecha) + esc(prod) + ' <span style="color:#4e73df;font-weight:700;">→ Ver en inventario</span></div>'
                + '</a>';
        });
        list.innerHTML = html;
    }

    function toast(count) {
        var t = document.getElementById('stockToast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'stockToast';
            t.style.cssText = 'position:fixed;bottom:20px;right:20px;left:auto;max-width:min(340px,calc(100vw - 24px));background:#9b2c2c;color:#fff;padding:12px 16px;border-radius:10px;z-index:99999;font-size:13px;box-shadow:0 10px 30px rgba(0,0,0,.3);';
            document.body.appendChild(t);
        }
        t.innerHTML = '<i class="fa-solid fa-bell"></i> Stock bajo: <b>' + count + '</b> alerta(s). <a href="index.php?action=inventario&filtro=bajo" style="color:#fed7d7;font-weight:700;">Ver</a>';
        t.style.display = 'block';
        setTimeout(function () { t.style.display = 'none'; }, 8000);
    }

    function poll() {
        fetch('index.php?action=api_alertas', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d || d.success === false) return;
                var c = parseInt(d.count || 0, 10) || 0;
                badge.textContent = c > 0 ? (c > 9 ? '9+' : c) : '0';
                badge.setAttribute('data-count', c);
                badge.style.display = c > 0 ? 'inline-block' : 'none';
                render(d.items);
                if (c > lastCount && !firstPoll) toast(c);
                lastCount = c;
                firstPoll = false;
            })
            .catch(function () { firstPoll = false; });
    }

    bell.addEventListener('click', function (e) {
        e.stopPropagation();
        var dd = document.getElementById('stockDropdown');
        if (!dd) return;
        // En móvil la campana no está al borde: el dropdown anclado a ella se
        // saldría del viewport. Se fija al viewport debajo de la campana.
        if (window.innerWidth <= 900) {
            var r = bell.getBoundingClientRect();
            dd.style.position = 'fixed';
            dd.style.top = (r.bottom + 8) + 'px';
            dd.style.left = '12px';
            dd.style.right = '12px';
            dd.style.width = 'auto';
            dd.style.maxWidth = 'none';
            dd.style.zIndex = '99999';
        } else {
            dd.style.position = '';
            dd.style.top = '';
            dd.style.left = '';
            dd.style.right = '';
            dd.style.width = '';
            dd.style.maxWidth = '';
            dd.style.zIndex = '';
        }
        dd.classList.toggle('show');
    });
    document.addEventListener('click', function (e) {
        var dd = document.getElementById('stockDropdown');
        if (dd && !dd.contains(e.target) && !bell.contains(e.target)) dd.classList.remove('show');
    });

    setInterval(poll, 60000);
    poll(); // primera lectura inmediata (corrige el conteo sin esperar 60s)

    // En móvil el navegador congela los intervalos en segundo plano: al volver
    // a la app se refresca de inmediato para no mostrar datos viejos.
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) poll();
    });
    window.addEventListener('pageshow', function () { poll(); });
})();
