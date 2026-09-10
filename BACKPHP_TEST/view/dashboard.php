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
                            <span><?php echo htmlspecialchars($_SESSION["user"]["nombre"] ?? $_SESSION["user"]["Email"] ?? $_SESSION["user"]["email"] ?? 'Usuario Demo'); ?> (<?php echo htmlspecialchars($_SESSION["user"]["Rol"] ?? $_SESSION["user"]["rol"] ?? 'Cliente'); ?>)</span>
                            <div class="avatar"></div>
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
                    <h2>DASHBOARD</h2>
                    <button class="btn-report"><i class="fa-solid fa-download"></i> Generar Reporte</button>
                </div>

                <!-- METRICAS / TARJETAS SUPERIORES -->
                <div class="cards-grid">

                    <!-- Tarjeta Azul: ganancia diaria -->
                    <div class="metric-card border-blue">
                        <div class="metric-info">
                            <span class="metric-title text-blue">GANANCIAS (DIARIA)</span>
                            <span class="metric-value">$<?php echo number_format($dashResumen['ganancias_hoy'] ?? 0, 0, ',', '.'); ?></span>
                            <small style="color:#858796;"><?php echo (int)($dashResumen['ventas_hoy'] ?? 0); ?> ventas hoy</small>
                        </div>
                        <i class="fa-solid fa-calendar metric-icon"></i>
                    </div>

                    <!-- Tarjeta Verde: ganancia mensual -->
                    <div class="metric-card border-green">
                        <div class="metric-info">
                            <span class="metric-title text-green">GANANCIAS (MENSUAL)</span>
                            <span class="metric-value">$<?php echo number_format($dashResumen['ganancias_mes'] ?? 0, 0, ',', '.'); ?></span>
                            <small style="color:#858796;"><?php echo (int)($dashResumen['ventas_mes'] ?? 0); ?> ventas este mes</small>
                        </div>
                        <i class="fa-solid fa-dollar-sign metric-icon"></i>
                    </div>

                    <!-- Tarjeta Cyan: ventas de hoy -->
                    <div class="metric-card border-cyan">
                        <div class="metric-info" style="width: 100%;">
                            <span class="metric-title text-cyan">VENTAS HOY</span>
                            <span class="metric-value"><?php echo (int)($dashResumen['ventas_hoy'] ?? 0); ?></span>
                            <small style="color:#858796;">ventas realizadas hoy</small>
                        </div>
                        <i class="fa-solid fa-clipboard-list metric-icon"></i>
                    </div>

                    <!-- Tarjeta Amarilla: ganancia anual -->
                    <div class="metric-card border-yellow">
                        <div class="metric-info">
                            <span class="metric-title text-yellow">GANANCIAS (ANUAL)</span>
                            <span class="metric-value">$<?php echo number_format($dashResumen['ganancias_anio'] ?? 0, 0, ',', '.'); ?></span>
                            <small style="color:#858796;">acumulado <?php echo date('Y'); ?></small>
                        </div>
                        <i class="fa-solid fa-chart-line metric-icon"></i>
                    </div>

                    <!-- Tarjeta stock bajo -->
                    <div class="metric-card border-blue">
                        <div class="metric-info" style="width: 100%;">
                            <span class="metric-title text-blue">STOCK BAJO (&lt;10)</span>
                            <span class="metric-value"><?php echo (int)($dashResumen['stock_bajo'] ?? 0); ?></span>
                            <small style="color:#858796;">productos por reponer</small>
                        </div>
                        <i class="fa-solid fa-boxes-stacked metric-icon"></i>
                    </div>

                    <!-- Tarjeta pedidos pendientes -->
                    <div class="metric-card border-green">
                        <div class="metric-info" style="width: 100%;">
                            <span class="metric-title text-green">PEDIDOS PENDIENTES</span>
                            <span class="metric-value"><?php echo (int)($dashResumen['pedidos_pendientes'] ?? 0); ?></span>
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
                            <i class="fa-solid fa-ellipsis-vertical"></i>
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
                                <tbody>
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

    <!-- SCRIPT PARA RENDERIZAR GRÁFICAS, SIDEBAR COLAPSABLE Y MENÚ DE USUARIO -->
    <script>
        // --- 1. LÓGICA DE SIDEBAR DESPLEGABLE / COLAPSABLE ---
        const sidebar = document.getElementById('sidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');

        if (toggleSidebarBtn && sidebar) {
            toggleSidebarBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }

        // Función para abrir/cerrar el submenú de Páginas
        function toggleSubmenu(button) {
            const dropdown = button.parentElement;
            // Si el sidebar está colapsado, opcionalmente lo expande al hacer clic en submenú
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
            }
            dropdown.classList.toggle('open');
        }

        // --- 2. LÓGICA DEL MENÚ DESPLEGABLE DE USUARIO ---
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userMenuBtn && userDropdownMenu) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!userDropdownMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                }
            });
        }

        // --- 3. RENDERING DE GRÁFICAS (Chart.js) — datos reales de la BD ---
        const areaLabels = <?php echo json_encode($dashMes['labels'] ?? []); ?>;
        const areaData = <?php echo json_encode($dashMes['data'] ?? []); ?>;
        const donutLabels = <?php echo json_encode(!empty($dashCat['labels']) ? $dashCat['labels'] : ['Sin datos']); ?>;
        const donutData = <?php echo json_encode(!empty($dashCat['data']) ? $dashCat['data'] : [1]); ?>;
        const barLabels = <?php echo json_encode($dashDia['labels'] ?? []); ?>;
        const barData = <?php echo json_encode($dashDia['data'] ?? []); ?>;
        const ctxArea = document.getElementById('areaChart').getContext('2d');
        new Chart(ctxArea, {
            type: 'line',
            data: {
                labels: areaLabels,
                datasets: [{
                    label: 'Ganancias',
                    data: areaData,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#4e73df'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        const ctxDonut = document.getElementById('donutChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutData,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                    borderWidth: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: '80%'
            }
        });

        const ctxBar = document.getElementById('barChart');
        if (ctxBar) {
            new Chart(ctxBar.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'Ventas',
                        data: barData,
                        backgroundColor: '#1cc88a'
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
    </script>
</body>

</html>