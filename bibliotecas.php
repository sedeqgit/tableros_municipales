<?php
/**
 * =============================================================================
 * PÁGINA DE MAPA DE BIBLIOTECAS - SISTEMA SEDEQ
 * =============================================================================
 *
 * Esta página muestra el mapa interactivo de bibliotecas del estado de Querétaro
 * integrando Google Maps mediante iframe y proporcionando información contextual.
 *
 * FUNCIONALIDADES PRINCIPALES:
 * - Visualización del mapa de bibliotecas del estado
 * - Panel informativo sobre el mapa
 * - Funcionalidad de pantalla completa
 * - Diseño responsivo y accesible
 *
 * @version 1.0
 * @since 2025
 */

// Cargar configuración de sesión
require_once 'session_helper.php';
iniciarSesionDemo();

// Función para obtener datos del mapa de bibliotecas
function obtenerMapaBibliotecas()
{
    $archivoMapas = __DIR__ . '/data/mapas_municipios.json';

    if (!file_exists($archivoMapas)) {
        error_log("Archivo de mapas no encontrado: $archivoMapas");
        return null;
    }

    $contenidoJson = file_get_contents($archivoMapas);
    $datosMapas = json_decode($contenidoJson, true);

    if (isset($datosMapas['bibliotecas']) && !empty($datosMapas['bibliotecas']['url'])) {
        return $datosMapas['bibliotecas'];
    }

    return null;
}

// Obtener datos del mapa
$datosMapaBibliotecas = obtenerMapaBibliotecas();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Bibliotecas | SEDEQ</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">

    <!-- ========================================== -->
    <!-- HOJAS DE ESTILO MODULARIZADAS             -->
    <!-- ========================================== -->
    <link rel="stylesheet" href="./css/home.css">
    <!-- Estilos globales compartidos por todo el sistema -->
    <link rel="stylesheet" href="./css/global.css">
    <!-- Estilos para el menú lateral responsive -->
    <link rel="stylesheet" href="./css/sidebar.css">
    <!-- Estilos específicos para la página de resumen (reutilizados para paneles) -->
    <link rel="stylesheet" href="./css/resumen.css">
    <!-- Estilos específicos para la página de mapas -->
    <link rel="stylesheet" href="./css/mapas.css">
    <!-- Font Awesome para iconografía -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include 'includes/institutional_bar.php'; ?>
    <!-- ============================================================================ -->
    <!-- HEADER PRINCIPAL CON LOGO Y NAVEGACIÓN                                      -->
    <!-- ============================================================================ -->
    <header class="main-header">
        <div class="header-content">
            <?php include 'includes/header_logo.php'; ?>

            <!-- Menú de navegación horizontal (desktop) -->
            <div class="header-nav">
                <nav>
                    <a href="home.php" class="header-nav-link">Inicio</a>
                    <a href="directorio_estatal.php" class="header-nav-link">Escuelas</a>
                    <a href="bibliotecas.php" class="header-nav-link active">Bibliotecas</a>
                    <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                        target="_blank" class="header-nav-link">Mapa</a>
                    <a href="settings.php" class="header-nav-link">Configuración</a>
                </nav>
            </div>

            <?php include 'includes/header_end.php'; ?>

            <!-- ======================================== -->
            <!-- BARRA LATERAL DE NAVEGACIÓN              -->
            <!-- ======================================== -->
            <aside class="sidebar">
                <!-- Logo en el sidebar -->
                <div class="sidebar-header">
                    <img src="./img/layout_set_logo.png" alt="SEDEQ" class="sidebar-logo">
                </div>

                <nav class="sidebar-nav">
                    <ul>
                        <!-- Enlace a página principal (estado activo) -->
                        <li class="nav-item active">
                            <a href="home.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
                        </li>
                        <!-- Enlaces a funcionalidades futuras -->
                        <li class="nav-item">
                            <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                                target="_blank"><i class="fas fa-map-marked-alt"></i> <span>Mapa Educativo</span></a>
                        </li>
                        <li class="nav-item">
                            <a href="bibliotecas.php"><i class="fas fa-book"></i> <span>Bibliotecas</span></a>
                        </li>
                        <li class="nav-item">
                            <a href="directorio_estatal.php"><i class="fas fa-search"></i> <span>Búsqueda de
                                    Escuelas</span></a>
                        </li>
                        <!-- Enlace a configuraciones del sistema -->
                        <li class="nav-item">
                            <a href="settings.php"><i class="fas fa-cog"></i> <span>Configuración</span></a>
                        </li>
                    </ul>
                </nav>

                <!-- Pie de la barra lateral con opción de logout -->
            </aside>

            <!-- ========================================== -->
            <!-- CONTENIDO PRINCIPAL                       -->
            <!-- ========================================== -->
            <div class="main-content">
                <!-- Barra superior -->
                <div class="topbar">
                    <div class="menu-toggle">
                        <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    </div>
                    <div class="page-title">
                        <h1 class="section-title">
                            Mapa de Bibliotecas del Estado de Querétaro
                        </h1>
                    </div>
                    <div class="utilities">
                        <div class="date-display">
                            <i class="far fa-calendar-alt"></i>
                            <span id="current-date"><?php echo fechaEnEspanol('d \\d\\e F \\d\\e Y'); ?></span>
                        </div>
                    </div>
                </div>
                <!-- Panel de Información del Mapa -->
                <div id="informacion-mapa" class="panel info-panel animate-fade delay-1">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-info-circle"></i>
                            Información del Mapa
                        </h2>
                    </div>
                    <div class="panel-body">
                        <div class="info-grid">
                            <!-- Descripción del Mapa -->
                            <div class="info-section">
                                <h3><i class="fas fa-book"></i> Acerca de este Mapa</h3>
                                <p>
                                    Este mapa interactivo presenta la ubicación geográfica de las bibliotecas públicas
                                    en el estado de Querétaro. Puede explorar las diferentes bibliotecas disponibles,
                                    obtener información detallada de cada una y visualizar la distribución territorial
                                    del sistema bibliotecario estatal.
                                </p>
                            </div>

                            <!-- Características del Sistema de Bibliotecas -->
                            <div class="info-section">
                                <h3><i class="fas fa-landmark"></i> Sistema de Bibliotecas de Querétaro</h3>
                                <p>
                                    La Red Estatal de Bibliotecas Públicas de Querétaro ofrece servicios bibliotecarios
                                    gratuitos a toda la población, promoviendo el acceso a la información, la lectura
                                    y el desarrollo cultural de las comunidades.
                                </p>
                            </div>

                            <!-- Leyenda e Instrucciones -->
                            <div class="info-section">
                                <h3><i class="fas fa-compass"></i> Cómo usar el Mapa</h3>
                                <ul class="instructions-list">
                                    <li>
                                        <i class="fas fa-mouse-pointer"></i>
                                        Haga clic en los marcadores para ver información de cada biblioteca
                                    </li>
                                    <li>
                                        <i class="fas fa-search-plus"></i>
                                        Use los controles de zoom para acercar o alejar la vista
                                    </li>
                                    <li>
                                        <i class="fas fa-map-marker-alt"></i>
                                        Explore las diferentes ubicaciones en todo el estado
                                    </li>
                                    <li>
                                        <i class="fas fa-expand"></i>
                                        Presione el botón de pantalla completa para una mejor visualización
                                    </li>
                                </ul>
                            </div>

                            <!-- Nota Importante -->
                            <div class="info-section note-section">
                                <h3><i class="fas fa-exclamation-circle"></i> Nota Importante</h3>
                                <p>
                                    La información mostrada en el mapa se actualiza periódicamente para reflejar
                                    los cambios en la red estatal de bibliotecas públicas. Para más información
                                    sobre horarios y servicios específicos, consulte directamente con cada biblioteca.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Mapa Interactivo -->
                <div id="mapa-interactivo" class="panel mapa-panel animate-fade delay-2">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-map-marked-alt"></i>
                            Mapa de Bibliotecas Públicas
                        </h2>
                        <button id="fullscreen-btn" class="fullscreen-btn" title="Pantalla completa">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                    <div class="panel-body">
                        <?php if ($datosMapaBibliotecas): ?>
                            <div id="map-container" class="map-container">
                                <iframe id="map-iframe"
                                    src="<?php echo htmlspecialchars($datosMapaBibliotecas['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                    allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <div class="no-map-message error-404">
                                <div class="error-image-container">
                                    <img src="./img/ERROR.png" alt="Error 404 - Mapa no encontrado" class="error-image">
                                </div>
                                <div class="error-content">
                                    <h4 class="error-title">Mapa No Encontrado</h4>
                                    <p class="error-description">
                                        El mapa de bibliotecas aún no está disponible en nuestro sistema.
                                    </p>
                                    <div class="error-actions">
                                        <a href="home.php" class="btn-primary">
                                            <i class="fas fa-home"></i>
                                            Volver al Inicio
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php include 'includes/footer.php'; ?>
            </div>

            <!-- ========================================== -->
            <!-- SCRIPTS                                    -->
            <!-- ========================================== -->
            <!-- Script de animaciones globales -->
            <script src="./js/animations_global.js"></script>
            <!-- Script del sidebar y navegación -->
            <script src="./js/sidebar.js"></script>
            <!-- Script específico de mapas -->
            <script src="./js/mapas.js"></script>
</body>

</html>