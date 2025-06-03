<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener datos educativos desde la base de datos
$datosEducativos = obtenerDatosEducativos();

// Calcular totales para resumen
$totales = calcularTotales($datosEducativos);
$totalEscuelas = $totales['escuelas'];
$totalAlumnos = $totales['alumnos'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Dashboards | SEDEQ - Estadística Educativa</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="app-container">
        <!-- Overlay para cerrar el menú en móviles -->
        <div class="sidebar-overlay"></div> <!-- Barra lateral -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="home.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
                    </li>
                    <li class="nav-item">
                        <a href="#"><i class="fas fa-map-marked-alt"></i> <span>Mapa Educativo</span></a>
                    </li>
                    <li class="nav-item">
                        <a href="#"><i class="fas fa-file-alt"></i> <span>Reportes</span></a>
                    </li>
                    <li class="nav-item">
                        <a href="settings.php"><i class="fas fa-cog"></i> <span>Configuración</span></a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Cerrar
                        Sesión</span></a>
            </div>
        </aside>

        <!-- Contenido principal -->
        <main class="main-content">
            <!-- Barra superior -->
            <header class="top-bar">
                <div class="menu-toggle">
                    <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
                </div>
                <div class="top-bar-title">
                    <h1>Centro de Dashboards Estadísticos</h1>
                </div>
                <div class="user-menu">
                    <span class="user-greeting">Hola,
                        <?php echo isset($_SESSION['fullname']) ? explode(' ', $_SESSION['fullname'])[0] : 'Usuario'; ?></span>
                    <div class="user-avatar">
                        <img src="./img/user-avatar.jpg" alt="Avatar">
                    </div>
                    <div class="user-dropdown">
                        <ul>
                            <li><a href="settings.php"><i class="fas fa-user-cog"></i> Mi Perfil</a></li>
                            <li><a href="#"><i class="fas fa-bell"></i> Notificaciones</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Panel de contenido -->
            <div class="content-wrapper"> <!-- Sección de bienvenida -->
                <section class="welcome-section">
                    <div class="welcome-card animate-fade">
                        <div class="welcome-text">
                            <h2>Bienvenido al Sistema de Estadística Educativa</h2>
                            <p>Accede a los dashboards estadísticos de los diferentes municipios del Estado de
                                Querétaro.</p>
                            <p class="welcome-date"><?php echo date('d \d\e F \d\e Y'); ?></p>
                        </div>
                        <div class="welcome-image animate-scale delay-1">
                            <img src="./img/layout_set_logo.png" alt="Querétaro">
                        </div>
                    </div>
                </section> <!-- Sección de municipios -->
                <section class="dashboard-section animate-up delay-2">
                    <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Dashboards por Municipio</h2>
                    <div class="dashboard-grid animate-sequence"> <!-- Tarjeta de municipio - Corregidora -->
                        <div class="municipality-card">
                            <div class="municipality-icon">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="municipality-info">
                                <h3>Corregidora</h3>
                                <p>Estadísticas educativas del municipio de Corregidora.</p>
                                <div class="municipality-stats">
                                    <div class="stat">
                                        <i class="fas fa-school"></i>
                                        <?php echo number_format($totalEscuelas, 0, '.', ','); ?>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-user-graduate"></i>
                                        <?php echo number_format($totalAlumnos, 0, '.', ','); ?>
                                    </div>
                                </div>
                            </div>
                            <a href="./resumen.php" class="municipality-link">
                                Ver Dashboard <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Tarjeta de municipio - Querétaro -->
                        <div class="municipality-card">
                            <div class="municipality-icon">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="municipality-info">
                                <h3>Querétaro</h3>
                                <p>Estadísticas educativas del municipio de Querétaro.</p>
                                <div class="municipality-stats">
                                    <div class="stat">
                                        <i class="fas fa-school"></i> 752
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-user-graduate"></i> 307,645
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="municipality-link">
                                Ver Dashboard <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Tarjeta de municipio - El Marqués -->
                        <div class="municipality-card">
                            <div class="municipality-icon">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="municipality-info">
                                <h3>El Marqués</h3>
                                <p>Estadísticas educativas del municipio de El Marqués.</p>
                                <div class="municipality-stats">
                                    <div class="stat">
                                        <i class="fas fa-school"></i> 165
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-user-graduate"></i> 42,183
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="municipality-link">
                                Ver Dashboard <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Tarjeta de municipio - San Juan del Río -->
                        <div class="municipality-card">
                            <div class="municipality-icon">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="municipality-info">
                                <h3>San Juan del Río</h3>
                                <p>Estadísticas educativas del municipio de San Juan del Río.</p>
                                <div class="municipality-stats">
                                    <div class="stat">
                                        <i class="fas fa-school"></i> 284
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-user-graduate"></i> 73,945
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="municipality-link">
                                Ver Dashboard <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Tarjeta "Ver más" -->
                        <div class="municipality-card view-more-card">
                            <div class="view-more-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <h3>Ver más municipios</h3>
                            <p>Accede a la información de todos los municipios del estado.</p>
                            <a href="#" class="view-more-link">
                                Ver todos <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </section> <!-- Sección de análisis rápidos -->
                <section class="quick-access-section animate-up delay-3">
                    <h2 class="section-title"><i class="fas fa-bolt"></i> Acceso Rápido</h2>
                    <div class="quick-access-grid animate-sequence">
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-search"></i>
                            <h3>Búsqueda por Escuela</h3>
                        </a>
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-chart-line"></i>
                            <h3>Tendencias Anuales</h3>
                        </a>
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-download"></i>
                            <h3>Descarga de Reportes</h3>
                        </a>
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-table"></i>
                            <h3>Tablas Comparativas</h3>
                        </a>
                    </div>
                </section>
            </div>

            <!-- Pie de página -->
            <footer class="main-footer">
                <p>&copy; <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                    reservados</p>
            </footer>
        </main>
    </div>
    <script src="./js/sidebar.js"></script>
    <script src="./js/home.js"></script>
    <script src="./js/animations_global.js"></script>
</body>

</html>