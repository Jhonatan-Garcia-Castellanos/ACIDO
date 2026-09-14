<?php
// 1. DESACTIVAR CACHÉ HTTP (Para evitar que guarde la vista previa)
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario haya iniciado sesión
if (!isset($_SESSION["user"])) {
    header("Location: /ACIDO/BACKPHP_TEST/index.php?action=login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ÁCIDO COLOMBIA</title>
    <!-- Estilos generales y del dashboard -->
    <!-- Cargar Tipografía y Alertas Globales -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <!-- Cargar Layout Base (Sidebar, Topbar y Scroll Interno) -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/layout.css?v=<?php echo time(); ?>">
    <!-- Cargar Métricas y Gráficas del Dashboard -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/dashboard.css?v=<?php echo time(); ?>">
    <!-- FontAwesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Iconos de FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js para las gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- 2. SCRIPT PARA FORZAR RECARGA AL VOLVER ATRÁS -->
    <script>
        window.addEventListener("pageshow", function (event) {
            // Detecta si la página se carga desde el caché del navegador
            if (event.persisted || (typeof window.performance != "undefined" && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
</head>

<body class="dashboard-body">

    <div class="dashboard-container">

        <!-- SIDEBAR (Barra Lateral Azul Colapsable) -->
        <!-- SIDEBAR (Barra Lateral Azul Colapsable) -->
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

            <a href="index.php?action=dashboard" class="nav-item active">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <hr class="sidebar-divider">

            <?php $__r = $_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente'; ?>
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
                <span class="sidebar-text"><?php echo in_array($__r,['Administrador','Empleado'],true)?'Ventas':'Mis compras'; ?></span>
            </a>
            <?php if (in_array($__r, ['Administrador','Empleado'], true)): ?>
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

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">

            <!-- NAVBAR SUPERIOR -->
            <header class="topbar">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar...">
                    <button><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>

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
                    <!-- Menú Desplegable con Usuario de Sesión BD -->
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span class="user-badge"><strong><?php echo htmlspecialchars($_SESSION["user"]["nombre_completo"] ?? $_SESSION["user"]["nombre"] ?? $_SESSION["user"]["Email"] ?? $_SESSION["user"]["email"] ?? 'Usuario Demo'); ?></strong><small><?php echo htmlspecialchars($_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente'); ?></small></span>
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

            <!-- CONTENIDO INTERNO -->
            <div class="content-padding">
                <?php
                $dashResumen = $dashData['resumen'] ?? ['ganancias_mes'=>0,'ventas_mes'=>0,'ganancias_anio'=>0,'ganancias_hoy'=>0,'ventas_hoy'=>0,'pedidos_pendientes'=>0,'stock_bajo'=>0];
                $dashMes = $dashData['porMes'] ?? ['labels'=>[],'data'=>[]];
                $dashCat = $dashData['porCategoria'] ?? ['labels'=>[],'data'=>[]];
                $dashTop = $dashData['topProductos'] ?? [];
                $dashDia = $dashData['porDia'] ?? ['labels'=>[],'data'=>[]];
                ?>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'forbidden'): ?>
                    <div style="background:#fed7d7;color:#9b2c2c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;">
                        No tienes permiso para ver esa sección (solo Administrador).
                    </div>
                <?php endif; ?>

                <!-- TITULO Y BOTÓN GENERAR -->
                <div class="page-header">
                    <h2>DASHBOARD
                        <span class="live-badge" title="Los datos se actualizan solos cada 30 segundos"><span class="live-dot"></span>EN VIVO</span>
                    </h2>
                    <div style="display:flex;align-items:center;gap:14px;">
                        <small id="updatedAt" style="color:#858796;">actualizado ahora mismo</small>
                        <a class="btn-report" style="text-decoration:none;" href="index.php?action=reporte_csv"><i class="fa-solid fa-download"></i> Generar Reporte</a>
                    </div>
                </div>

                <!-- METRICAS / TARJETAS SUPERIORES -->
                <div class="cards-grid">

                    <!-- Tarjeta Azul: ganancia diaria -->
                    <div class="metric-card border-blue anim-card" style="animation-delay:.05s" data-href="index.php?action=ventas" title="Ver en Ventas">
                        <div class="metric-info">
                            <span class="metric-title text-blue">GANANCIAS (DIARIA)</span>
                            <span class="metric-value count-up" id="mGanHoy" data-value="<?php echo (float)($dashResumen['ganancias_hoy'] ?? 0); ?>" data-money="1">$<?php echo number_format($dashResumen['ganancias_hoy'] ?? 0, 0, ',', '.'); ?></span>
                            <small style="color:#858796;"><span id="mVentasHoySub"><?php echo (int)($dashResumen['ventas_hoy'] ?? 0); ?></span> ventas hoy</small>
                        </div>
                        <i class="fa-solid fa-calendar metric-icon"></i>
                    </div>

                    <!-- Tarjeta Verde: ganancia mensual -->
                    <div class="metric-card border-green anim-card" style="animation-delay:.12s" data-href="index.php?action=ventas" title="Ver en Ventas">
                        <div class="metric-info">
                            <span class="metric-title text-green">GANANCIAS (MENSUAL)</span>
                            <span class="metric-value count-up" id="mGanMes" data-value="<?php echo (float)($dashResumen['ganancias_mes'] ?? 0); ?>" data-money="1">$<?php echo number_format($dashResumen['ganancias_mes'] ?? 0, 0, ',', '.'); ?></span>
                            <small style="color:#858796;"><span id="mVentasMesSub"><?php echo (int)($dashResumen['ventas_mes'] ?? 0); ?></span> ventas este mes</small>
                        </div>
                        <i class="fa-solid fa-dollar-sign metric-icon"></i>
                    </div>

                    <!-- Tarjeta Cyan: ventas de hoy -->
                    <div class="metric-card border-cyan anim-card" style="animation-delay:.19s" data-href="index.php?action=ventas" title="Ver en Ventas">
                        <div class="metric-info" style="width: 100%;">
                            <span class="metric-title text-cyan">VENTAS HOY</span>
                            <span class="metric-value count-up" id="mVentasHoy" data-value="<?php echo (int)($dashResumen['ventas_hoy'] ?? 0); ?>"><?php echo (int)($dashResumen['ventas_hoy'] ?? 0); ?></span>
                            <small style="color:#858796;">ventas realizadas hoy</small>
                        </div>
                        <i class="fa-solid fa-clipboard-list metric-icon"></i>
                    </div>

                    <!-- Tarjeta Amarilla: ganancia anual -->
                    <div class="metric-card border-yellow anim-card" style="animation-delay:.26s" data-href="index.php?action=ventas" title="Ver en Ventas">
                        <div class="metric-info">
                            <span class="metric-title text-yellow">GANANCIAS (ANUAL)</span>
                            <span class="metric-value count-up" id="mGanAnio" data-value="<?php echo (float)($dashResumen['ganancias_anio'] ?? 0); ?>" data-money="1">$<?php echo number_format($dashResumen['ganancias_anio'] ?? 0, 0, ',', '.'); ?></span>
                            <small style="color:#858796;">acumulado <?php echo date('Y'); ?></small>
                        </div>
                        <i class="fa-solid fa-chart-line metric-icon"></i>
                    </div>

                    <!-- Tarjeta stock bajo -->
                    <div class="metric-card border-blue anim-card" style="animation-delay:.33s" data-href="index.php?action=inventario" title="Ver en Inventario">
                        <div class="metric-info" style="width: 100%;">
                            <span class="metric-title text-blue">STOCK BAJO (&lt;10)</span>
                            <span class="metric-value count-up" id="mStockBajo" data-value="<?php echo (int)($dashResumen['stock_bajo'] ?? 0); ?>"><?php echo (int)($dashResumen['stock_bajo'] ?? 0); ?></span>
                            <small style="color:#858796;">productos por reponer</small>
                        </div>
                        <i class="fa-solid fa-boxes-stacked metric-icon"></i>
                    </div>

                    <!-- Tarjeta pedidos pendientes -->
                    <div class="metric-card border-green anim-card" style="animation-delay:.40s" data-href="index.php?action=ventas" title="Ver en Ventas">
                        <div class="metric-info" style="width: 100%;">
                            <span class="metric-title text-green">PEDIDOS PENDIENTES</span>
                            <span class="metric-value count-up" id="mPedidos" data-value="<?php echo (int)($dashResumen['pedidos_pendientes'] ?? 0); ?>"><?php echo (int)($dashResumen['pedidos_pendientes'] ?? 0); ?></span>
                            <small style="color:#858796;">preparando / en camino</small>
                        </div>
                        <i class="fa-solid fa-truck-fast metric-icon"></i>
                    </div>

                </div>

                <!-- SECCIÓN DE GRÁFICAS -->
                <div class="charts-grid">

                    <!-- Gráfica de Líneas -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Resumen de Ganancias</h3>
                            <div class="range-group" role="group" aria-label="Rango de meses">
                                <button type="button" class="range-btn" data-n="3">3M</button>
                                <button type="button" class="range-btn active" data-n="6">6M</button>
                                <button type="button" class="range-btn" data-n="12">12M</button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="areaChart"></canvas>
                        </div>
                    </div>

                    <!-- Gráfica de Dona -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Ingresos por Categoría</h3>
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </div>
                        <div class="chart-body">
                            <canvas id="donutChart"></canvas>
                        </div>
                    </div>

                </div>

                <!-- SEGUNDA FILA: ventas diarias + top productos (datos reales) -->
                <div class="charts-grid" style="margin-top:20px;">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Ventas últimos 7 días</h3>
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </div>
                        <div class="chart-body">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Top productos (v_top_productos)</h3>
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </div>
                        <div class="chart-body" style="height:auto;max-height:300px;overflow:auto;">
                            <?php if (!empty($dashTop)): ?>
                            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                                <thead><tr style="color:#858796;text-align:left;"><th>Producto</th><th style="text-align:right;">Vendidos</th></tr></thead>
                                <tbody id="topBody">
                                <?php foreach ($dashTop as $t): ?>
                                    <tr style="border-top:1px solid #e3e6f0;">
                                        <td style="padding:6px 0;"><?php echo htmlspecialchars($t['Nombre_Producto'] ?? ''); ?></td>
                                        <td style="text-align:right;font-weight:700;"><?php echo (int)($t['Vendidos'] ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p style="color:#858796;font-size:13px;">Sin ventas registradas.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
        </main>

    </div>

    <!-- app.js compartido: sidebar persistente, menú usuario, reloj y contadores -->
    <script src="/ACIDO/BACKPHP_TEST/public/js/app.js?v=1"></script>
    <!-- SCRIPT PARA RENDERIZAR GRÁFICAS -->
    <script>
        // --- RENDERING DE GRÁFICAS (Chart.js) — datos reales de la BD ---
        const fmtMoney = (v) => '$' + Number(v || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 });
        const fmtNum = (v) => Number(v || 0).toLocaleString('es-CO', { maximumFractionDigits: 0 });

        // Contadores animados (count-up) en las tarjetas.
        // Marca dataset.animated para que app.js no los anime dos veces.
        function countUp(el, target, money) {
            el.dataset.animated = '1';
            const dur = 900, t0 = performance.now(), from = 0, to = Number(target || 0);
            function tick(t) {
                const p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
                el.textContent = money ? fmtMoney(from + (to - from) * e) : fmtNum(from + (to - from) * e);
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }
        // Tarjetas clicables → navegan a la página origen de sus datos
        document.querySelectorAll('[data-href]').forEach((card) => {
            card.addEventListener('click', () => { window.location.href = card.dataset.href; });
        });

        // Instancias (se guardan para actualizarlas sin recargar)
        let areaChart = null, donutChart = null, barChart = null;
        let rangoMeses = 6;

        function buildCharts(mes, cat, dia) {
            const areaLabels = mes.labels || [], areaData = mes.data || [];
            const donutLabels = (cat.labels && cat.labels.length) ? cat.labels : ['Sin datos'];
            const donutData = (cat.data && cat.data.length) ? cat.data : [1];
            const barLabels = dia.labels || [], barData = dia.data || [];

            if (areaChart) {
                areaChart.data.labels = areaLabels;
                areaChart.data.datasets[0].data = areaData;
                areaChart.update();
            } else {
                areaChart = new Chart(document.getElementById('areaChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: areaLabels,
                        datasets: [{
                            label: 'Ganancias',
                            data: areaData,
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.15)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#4e73df'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (c) => ' ' + fmtMoney(c.parsed.y) } }
                        },
                        scales: { y: { ticks: { callback: (v) => '$' + Number(v).toLocaleString('es-CO', { notation: 'compact' }) } } }
                    }
                });
            }

            if (donutChart) {
                donutChart.data.labels = donutLabels;
                donutChart.data.datasets[0].data = donutData;
                donutChart.update();
            } else {
                donutChart = new Chart(document.getElementById('donutChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: donutLabels,
                        datasets: [{
                            data: donutData,
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                            hoverOffset: 10,
                            borderWidth: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { animateRotate: true, duration: 1000 },
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: { callbacks: { label: (c) => ' ' + c.label + ': ' + fmtMoney(c.parsed) } }
                        },
                        cutout: '78%'
                    }
                });
            }

            const ctxBar = document.getElementById('barChart');
            if (ctxBar) {
                if (barChart) {
                    barChart.data.labels = barLabels;
                    barChart.data.datasets[0].data = barData;
                    barChart.update();
                } else {
                    barChart = new Chart(ctxBar.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: barLabels,
                            datasets: [{
                                label: 'Ventas',
                                data: barData,
                                backgroundColor: '#1cc88a',
                                hoverBackgroundColor: '#17a673',
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });
                }
            }
        }

        function renderTop(rows) {
            const tb = document.getElementById('topBody');
            if (!tb) return;
            if (!rows || !rows.length) {
                tb.innerHTML = '<tr><td colspan="2" style="color:#858796;font-size:13px;">Sin ventas registradas.</td></tr>';
                return;
            }
            tb.innerHTML = rows.map((t) => {
                const n = document.createElement('div'); n.textContent = t.Nombre_Producto || '';
                return '<tr style="border-top:1px solid #e3e6f0;"><td style="padding:6px 0;">' + n.innerHTML +
                    '</td><td style="text-align:right;font-weight:700;">' + fmtNum(t.Vendidos) + '</td></tr>';
            }).join('');
        }

        // Aplica un paquete completo de datos (inicial + cada refresco)
        function applyData(d) {
            const r = d.resumen || {};
            const set = (id, v, money) => {
                const el = document.getElementById(id);
                if (el) countUp(el, v, money);
            };
            set('mGanHoy', r.ganancias_hoy, true);
            set('mGanMes', r.ganancias_mes, true);
            set('mVentasHoy', r.ventas_hoy, false);
            set('mGanAnio', r.ganancias_anio, true);
            set('mStockBajo', r.stock_bajo, false);
            set('mPedidos', r.pedidos_pendientes, false);
            const s1 = document.getElementById('mVentasHoySub');
            if (s1) s1.textContent = fmtNum(r.ventas_hoy);
            const s2 = document.getElementById('mVentasMesSub');
            if (s2) s2.textContent = fmtNum(r.ventas_mes);
            buildCharts(d.porMes || {}, d.porCategoria || {}, d.porDia || {});
            renderTop(d.topProductos || []);
            const up = document.getElementById('updatedAt');
            if (up) up.textContent = 'actualizado ' + new Date().toLocaleTimeString('es-CO', { hour12: false });
        }

        // Datos iniciales que ya trajo PHP
        applyData({
            resumen: <?php echo json_encode($dashResumen); ?>,
            porMes: <?php echo json_encode($dashMes); ?>,
            porCategoria: <?php echo json_encode($dashCat); ?>,
            topProductos: <?php echo json_encode($dashTop); ?>,
            porDia: <?php echo json_encode($dashDia); ?>
        });

        // Selector de rango 3M / 6M / 12M
        document.querySelectorAll('.range-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.range-btn').forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');
                rangoMeses = parseInt(btn.dataset.n, 10) || 6;
                refreshDashboard();
            });
        });

        // Auto-refresh cada 30 segundos (respeta el rango elegido)
        let refreshing = false;
        async function refreshDashboard() {
            if (refreshing) return;
            refreshing = true;
            try {
                const res = await fetch('index.php?action=dashboard_data&n=' + rangoMeses, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) applyData(await res.json());
            } catch (e) { /* sin conexión: se conserva lo último */ }
            refreshing = false;
        }
        setInterval(refreshDashboard, 30000);
    </script>
</body>

</html>