/* ============================================================
   app.js — JS compartido de toda la app ACIDO
   1. Sidebar persistente: si se colapsa, sigue colapsado al
      navegar entre vistas (localStorage). Igual al expandir.
   2. Menú desplegable de usuario del topbar.
   3. Reloj en vivo (#liveClock donde exista).
   4. Contadores animados (.count-up donde existan).
   5. toggleSubmenu global (compatibilidad con vistas que lo usan).
   ============================================================ */
(function () {
    'use strict';

    var LS_KEY = 'acido_sidebar_collapsed';

    function $(id) { return document.getElementById(id); }

    function saveState(collapsed) {
        try { localStorage.setItem(LS_KEY, collapsed ? '1' : '0'); } catch (e) {}
    }

    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = $('sidebar');
        var toggle = $('toggleSidebar');

        // 1. Restaurar preferencia guardada antes de que se note el cambio
        try {
            if (sidebar && localStorage.getItem(LS_KEY) === '1') {
                sidebar.classList.add('collapsed');
            }
        } catch (e) {}

        // 2. Colapsar/expandir + persistir
        if (toggle && sidebar) {
            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                saveState(sidebar.classList.contains('collapsed'));
            });
        }

        // 3. Menú de usuario
        var ub = $('userMenuBtn'), um = $('userDropdownMenu');
        if (ub && um) {
            ub.addEventListener('click', function (e) {
                e.stopPropagation();
                um.classList.toggle('show');
            });
            document.addEventListener('click', function (e) {
                if (!um.contains(e.target) && !ub.contains(e.target)) {
                    um.classList.remove('show');
                }
            });
        }

        // 4. Reloj en vivo
        var clock = $('liveClock');
        if (clock) {
            var tick = function () {
                clock.textContent = new Date().toLocaleTimeString('es-CO', { hour12: false });
            };
            tick();
            setInterval(tick, 1000);
        }

        // 5. Contadores animados genéricos (una sola vez; el dashboard
        //    re-anima por su cuenta en cada refresco con su countUp local)
        var counters = document.querySelectorAll('.count-up');
        for (var i = 0; i < counters.length; i++) {
            (function (el) {
                if (el.dataset.animated) return;
                el.dataset.animated = '1';
                var target = Number(el.dataset.value || 0);
                var money = el.dataset.money === '1';
                var fmt = function (v) {
                    var s = Number(v).toLocaleString('es-CO', { maximumFractionDigits: 0 });
                    return money ? '$' + s : s;
                };
                var t0 = performance.now(), dur = 900;
                var step = function (t) {
                    var p = Math.min(1, (t - t0) / dur);
                    var e2 = 1 - Math.pow(1 - p, 3);
                    el.textContent = fmt(target * e2);
                    if (p < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            })(counters[i]);
        }
    });

    // 6. Toasts globales + auto-toast desde ?status= / ?error=
    function ensureToastBox() {
        var box = $('acidoToasts');
        if (!box) {
            box = document.createElement('div');
            box.id = 'acidoToasts';
            box.className = 'acido-toasts';
            document.body.appendChild(box);
        }
        return box;
    }
    window.acidoToast = function (msg, type) {
        if (!msg) return;
        var box = ensureToastBox();
        var t = document.createElement('div');
        t.className = 'acido-toast ' + (type === 'error' ? 'toast-error' : 'toast-ok');
        t.textContent = msg;
        box.appendChild(t);
        setTimeout(function () { t.classList.add('toast-show'); }, 10);
        setTimeout(function () {
            t.classList.remove('toast-show');
            setTimeout(function () { t.remove(); }, 300);
        }, 3500);
    };
    document.addEventListener('DOMContentLoaded', function () {
        try {
            var q = new URLSearchParams(window.location.search);
            var ok = q.get('status'), err = q.get('error');
            // Solo toast automático para mensajes cortos (los banners HTML siguen existiendo)
            if (ok && ok.length < 120 && ok !== 'success_register' && ok !== 'password_updated') {
                window.acidoToast(decodeURIComponent(ok), 'ok');
            }
            if (err && err.length < 120 && ['save_fail', 'forbidden'].indexOf(err) === -1) {
                window.acidoToast(decodeURIComponent(err), 'error');
            }
        } catch (e) {}
    });

    // 7. Actualiza los contadores del carrito (sidebar + topbar) sin recargar
    window.updateCartBadge = function (n) {
        n = parseInt(n, 10) || 0;
        var side = document.querySelector('a.nav-item[href*="action=carrito"] .sidebar-text');
        if (side) side.textContent = n > 0 ? 'Carrito (' + n + ')' : 'Carrito';
        var top = document.querySelector('a.icon-badge[href*="action=carrito"]');
        if (top) {
            var b = top.querySelector('.badge');
            if (n > 0) {
                if (b) { b.textContent = n; }
                else {
                    b = document.createElement('span');
                    b.className = 'badge red';
                    b.textContent = n;
                    top.appendChild(b);
                }
            } else if (b) { b.remove(); }
        }
    };

    // 8. Submenús del sidebar (global para compatibilidad)
    window.toggleSubmenu = function (button) {
        var sidebar = $('sidebar');
        var dropdown = button ? button.parentElement : null;
        if (sidebar && sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            saveState(false);
        }
        if (dropdown) dropdown.classList.toggle('open');
    };
})();
