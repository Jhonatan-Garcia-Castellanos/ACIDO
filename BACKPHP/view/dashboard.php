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
    header("Location: /ACIDO/BACKPHP/index.php?action=login");
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
    <link rel="stylesheet" href="/ACIDO/BACKPHP/public/css/global.css?v=<?php echo time(); ?>">
    <!-- Cargar Layout Base (Sidebar, Topbar y Scroll Interno) -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP/public/css/layout.css?v=<?php echo time(); ?>">
    <!-- Cargar Métricas y Gráficas del Dashboard -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP/public/css/dashboard.css?v=<?php echo time(); ?>">
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
                    <img src="/ACIDO/BACKPHP/public/LOGO2.png" class="brand-icon" alt="Logo Ácido Colombia">
                    <span class="brand-name">ACIDO</span>
                </div>
            </div>

            <hr class="sidebar-divider">

            <a href="#" class="nav-item active">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading sidebar-text">INTERFACE</div>

            <a href="/ACIDO/BACKPHP/index.php?action=crud" class="nav-item">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-text">Usuarios</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-wrench"></i>
                <span class="sidebar-text">Utilidades</span>
            </a>

            <hr class="sidebar-divider">

            <div class="sidebar-heading sidebar-text">COMPLEMENTOS</div>

            <!-- OPCCIÓN DESPLEGABLE: PÁGINAS -->
            <div class="sidebar-dropdown">
                <button type="button" class="nav-item dropdown-toggle" onclick="toggleSubmenu(this)">
                    <div class="nav-label">
                        <i class="fa-solid fa-folder"></i>
                        <span class="sidebar-text">Páginas</span>
                    </div>
                    <i class="fa-solid fa-chevron-right arrow-icon sidebar-text"></i>
                </button>

                <div class="sidebar-submenu">
                    <a href="/ACIDO/BACKPHP/index.php?action=login">
                        <i class="fa-solid fa-right-to-bracket"></i> <span class="sidebar-text">Iniciar Sesión</span>
                    </a>
                    <a href="/ACIDO/BACKPHP/index.php?action=register">
                        <i class="fa-solid fa-user-plus"></i> <span class="sidebar-text">Registro</span>
                    </a>
                    <a href="/ACIDO/BACKPHP/index.php?action=crud">
                        <i class="fa-solid fa-users"></i> <span class="sidebar-text">Usuarios</span>
                    </a>
                </div>
            </div>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-chart-area"></i>
                <span class="sidebar-text">Gráficas</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fa-solid fa-table"></i>
                <span class="sidebar-text">Tablas</span>
            </a>

            <div class="sidebar-promo sidebar-text">
                <i class="fa-solid fa-rocket promo-icon"></i>
                <p><strong>ÁCIDO Pro</strong> incluye funciones avanzadas y componentes exclusivos.</p>
            </div>
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
                    <div class="divider-vertical"></div>

                    <!-- Menú Desplegable con Usuario de Sesión BD -->
                    <div class="user-info-dropdown" style="position: relative;">
                        <div class="user-info" id="userMenuBtn" style="cursor: pointer;">
                            <span><?php echo htmlspecialchars($_SESSION["user"]["nombre"] ?? $_SESSION["user"]["email"] ?? 'Usuario Demo'); ?></span>
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

                <!-- TITULO Y BOTÓN GENERAR -->
                <div class="page-header">
                    <h2>DASHBOARD</h2>
                    <button class="btn-report"><i class="fa-solid fa-download"></i> Generar Reporte</button>
                </div>

                <!-- METRICAS / TARJETAS SUPERIORES -->
                <div class="cards-grid">

                    <!-- Tarjeta Azul -->
                    <div class="metric-card border-blue">
                        <div class="metric-info">
                            <span class="metric-title text-blue">GANANCIAS (MENSUAL)</span>
                            <span class="metric-value">$40,000</span>
                        </div>
                        <i class="fa-solid fa-calendar metric-icon"></i>
                    </div>

                    <!-- Tarjeta Verde -->
                    <div class="metric-card border-green">
                        <div class="metric-info">
                            <span class="metric-title text-green">GANANCIAS (ANUAL)</span>
                            <span class="metric-value">$215,000</span>
                        </div>
                        <i class="fa-solid fa-dollar-sign metric-icon"></i>
                    </div>

                    <!-- Tarjeta Cyan (Con Barra de Progreso) -->
                    <div class="metric-card border-cyan">
                        <div class="metric-info" style="width: 100%;">
                            <span class="metric-title text-cyan">TAREAS</span>
                            <div class="progress-container">
                                <span class="metric-value">50%</span>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill" style="width: 50%;"></div>
                                </div>
                            </div>
                        </div>
                        <i class="fa-solid fa-clipboard-list metric-icon"></i>
                    </div>

                    <!-- Tarjeta Amarilla -->
                    <div class="metric-card border-yellow">
                        <div class="metric-info">
                            <span class="metric-title text-yellow">SOLICITUDES PENDIENTES</span>
                            <span class="metric-value">18</span>
                        </div>
                        <i class="fa-solid fa-comments metric-icon"></i>
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
                            <h3>Fuentes de Ingresos</h3>
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </div>
                        <div class="chart-body">
                            <canvas id="donutChart"></canvas>
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

        // --- 3. RENDERING DE GRÁFICAS (Chart.js) ---
        const ctxArea = document.getElementById('areaChart').getContext('2d');
        new Chart(ctxArea, {
            type: 'line',
            data: {
                labels: ['Ene', 'Mar', 'May', 'Jul', 'Sep', 'Nov'],
                datasets: [{
                    label: 'Ganancias',
                    data: [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000, 25000, 40000],
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
                labels: ['Directo', 'Social', 'Referido'],
                datasets: [{
                    data: [55, 30, 15],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
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
    </script>
</body>

</html>