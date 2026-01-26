<?php
/**
 * =============================================================================
 * PÁGINA DE MAPAS EDUCATIVOS - SISTEMA SEDEQ
 * =============================================================================
 *
 * Esta página presenta un mapa interactivo de Google Maps con la ubicación
 * de instituciones educativas en el estado de Querétaro.
 *
 * FUNCIONALIDADES PRINCIPALES:
 * - Visualización de mapa interactivo mediante iframe de Google Maps
 * - Modo de pantalla completa para mejor visualización
 * - Información contextual y leyenda del mapa
 * - Integración con el sistema de navegación del dashboard
 *
 * @package SEDEQ_Dashboard
 * @subpackage Mapas
 * @version 1.0
 */

// =============================================================================
// CONFIGURACIÓN E INICIALIZACIÓN DEL SISTEMA
// =============================================================================

// Incluir el helper de sesiones para manejo de autenticación
require_once 'session_helper.php';

// Inicializar sesión y configurar usuario de demostración si es necesario
iniciarSesionDemo();

// Incluir archivo de conexión actualizado
require_once 'conexion_prueba_2024.php';

// =============================================================================
// OBTENCIÓN DE PARÁMETROS Y VALIDACIÓN
// =============================================================================

// Obtener el municipio desde el parámetro GET, por defecto Querétaro
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'QUERÉTARO';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'QUERÉTARO'; // Fallback a Querétaro si el municipio no es válido
}

// =============================================================================
// OBTENCIÓN DE DATOS PARA INFORMACIÓN DEL MAPA
// =============================================================================

// Obtener datos completos del municipio
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Verificar si hay datos
$tieneDatos = $datosCompletosMunicipio &&
    isset($datosCompletosMunicipio['total_matricula']) &&
    $datosCompletosMunicipio['total_matricula'] > 0;

// Inicializar variables
$totalEscuelas = 0;
$totalAlumnos = 0;
$totalDocentes = 0;

if ($tieneDatos) {
    $totalEscuelas = isset($datosCompletosMunicipio['total_escuelas']) ? (int) $datosCompletosMunicipio['total_escuelas'] : 0;
    $totalAlumnos = isset($datosCompletosMunicipio['total_matricula']) ? (int) $datosCompletosMunicipio['total_matricula'] : 0;
    $totalDocentes = isset($datosCompletosMunicipio['total_docentes']) ? (int) $datosCompletosMunicipio['total_docentes'] : 0;
}

// =============================================================================
// OBTENCIÓN DEL MAPA CORRESPONDIENTE AL MUNICIPIO
// =============================================================================

/**
 * Obtiene la URL del mapa de Google Maps para el municipio especificado
 * Lee desde archivo JSON que contiene los mapas de todos los municipios
 */
function obtenerMapaMunicipio($municipio)
{
    // Ruta al archivo JSON con los mapas
    $archivoMapas = __DIR__ . '/data/mapas_municipios.json';

    // Verificar si existe el archivo
    if (!file_exists($archivoMapas)) {
        error_log("Archivo de mapas no encontrado: $archivoMapas");
        return null;
    }

    // Leer y decodificar el JSON
    $contenidoJson = file_get_contents($archivoMapas);
    $datosMapas = json_decode($contenidoJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error al decodificar JSON de mapas: " . json_last_error_msg());
        return null;
    }

    // Buscar el mapa del municipio específico
    if (isset($datosMapas['mapas_por_municipio'][$municipio])) {
        $mapaData = $datosMapas['mapas_por_municipio'][$municipio];

        // Verificar que tenga URL configurada
        if (!empty($mapaData['url'])) {
            return $mapaData;
        }
    }

    // No hay mapa disponible para este municipio
    return null;
}

// Obtener el mapa correspondiente al municipio seleccionado
$mapaActual = obtenerMapaMunicipio($municipioSeleccionado);
$urlMapaIframe = $mapaActual ? $mapaActual['url'] : '';
$tieneMapaDisponible = !empty($urlMapaIframe);

/**
 * Formatea nombres de municipios para display en formato título
 */
function formatearNombreMunicipio($municipio)
{
    // Convertir de mayúsculas a formato título
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');

    // Correcciones específicas para preposiciones y artículos
    $formatted = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $formatted);

    return $formatted;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapas Educativos - <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> | SEDEQ</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">

    <!-- ========================================== -->
    <!-- HOJAS DE ESTILO MODULARIZADAS             -->
    <!-- ========================================== -->
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
    <!-- ============================================================================ -->
    <!-- BARRA SUPERIOR INSTITUCIONAL                                                -->
    <!-- ============================================================================ -->
    <div class="top-institutional-bar">
        <div class="institutional-bar-content">
            <!-- Enlaces institucionales importantes -->
            <div class="institutional-links">
                <a href="https://www.queretaro.gob.mx/transparencia" class="institutional-link">Portal Transparencia</a>
                <a href="https://portal.queretaro.gob.mx/prensa/" class="institutional-link">Portal Prensa</a>
                <a href="https://www.queretaro.gob.mx/covid19" class="institutional-link">COVID19</a>
            </div>

            <!-- Redes sociales y contacto -->
            <div class="social-links">
                <a href="https://www.facebook.com/educacionqro" target="_blank" class="social-link" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://x.com/educacionqro" target="_blank" class="social-link" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.instagram.com/educacionqueretaro?fbclid=IwZXh0bgNhZW0CMTAAYnJpZBExR09OOWJid2NZT2ZTbUJvRHNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR4yi6bwE_6iEuyyUdbWYkjRLv9zjFFWyxwABVKdZSunmMWOwOsHAv_dcFFBOw_aem_t72qtgoL72OI4Pzyj-oILw"
                    target="_blank" class="social-link" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.youtube.com/@SecretariadeEducacionGEQ" target="_blank" class="social-link"
                    title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="tel:4422117070" class="social-link" title="Teléfono">
                    <i class="fas fa-phone"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- HEADER PRINCIPAL CON LOGO Y NAVEGACIÓN                                      -->
    <!-- ============================================================================ -->
    <header class="main-header">
        <div class="header-content">
            <!-- Logo institucional -->
            <div class="header-logo">
                <a href="home.php">
                    <img src="./img/layout_set_logo.png" alt="SEDEQ - Secretaría de Educación de Querétaro">
                </a>
            </div>

            <!-- Menú de navegación horizontal (desktop) -->
            <div class="header-nav">
                <nav>
                    <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Resumen</a>
                    <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Escuelas</a>
                    <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Matrícula</a>
                    <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Docentes</a>
                    <!-- Mapas con dropdown -->
                    <div class="nav-dropdown">
                        <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                            class="header-nav-link active">Mapas <i class="fas fa-chevron-down dropdown-arrow"></i></a>
                        <div class="nav-dropdown-content">
                            <a href="#informacion-mapa" class="nav-dropdown-link">Información del Mapa</a>
                            <a href="#mapa-interactivo" class="nav-dropdown-link">Mapa Interactivo</a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Botón de búsqueda -->
            <div class="header-search">
                <button id="searchToggle" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- Botón de menú hamburguesa (solo móviles) -->
            <div class="header-menu-toggle">
                <button id="sidebarToggle" class="menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Barra de búsqueda expandible -->
        <div class="search-bar-expanded" id="searchBarExpanded">
            <div class="search-bar-content">
                <input type="text" placeholder="Buscar escuelas, municipios, estadísticas..." class="search-input">
                <button class="search-submit-btn">
                    <i class="fas fa-search"></i>
                </button>
                <button class="search-close-btn" id="searchClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Overlay para cerrar menú en dispositivos móviles -->
    <div class="sidebar-overlay"></div>

    <!-- ========================================== -->
    <!-- SIDEBAR - MENÚ DE NAVEGACIÓN LATERAL      -->
    <!-- ========================================== -->
    <aside class="sidebar">
        <!-- Logo en el sidebar -->
        <div class="sidebar-header">
            <img src="./img/layout_set_logo.png" alt="SEDEQ" class="sidebar-logo">
        </div>

        <div class="sidebar-links">
            <a href="home.php" class="sidebar-link">
                <i class="fas fa-home"></i>
                <span>Regresar al Inicio</span>
            </a>
            <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-chart-bar"></i>
                <span>Resumen</span>
            </a>
            <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                class="sidebar-link">
                <i class="fas fa-school"></i>
                <span>Escuelas</span>
            </a>
            <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-user-graduate"></i>
                <span>Matrícula</span>
            </a>
            <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Docentes</span>
            </a>
            <div class="sidebar-link-with-submenu">
                <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                    class="sidebar-link active has-submenu">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Mapas</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu active">
                    <a href="#informacion-mapa" class="submenu-link">
                        <i class="fas fa-info-circle"></i>
                        <span>Información del Mapa</span>
                    </a>
                    <a href="#mapa-interactivo" class="submenu-link">
                        <i class="fas fa-map"></i>
                        <span>Mapa Interactivo</span>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- ========================================== -->
    <!-- CONTENIDO PRINCIPAL                       -->
    <!-- ========================================== -->
    <div class="main-content">
        <div class="container-fluid">

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
                            <h3><i class="fas fa-map"></i> Acerca de este Mapa</h3>
                            <p>
                                Este mapa interactivo presenta la ubicación geográfica de las instituciones
                                educativas en el estado de Querétaro. Puede explorar diferentes niveles educativos,
                                obtener información detallada de cada escuela y visualizar la distribución territorial
                                del sistema educativo estatal.
                            </p>
                        </div>

                        <!-- Estadísticas Relacionadas -->
                        <div class="info-section">
                            <h3><i class="fas fa-chart-bar"></i> Estadísticas de
                                <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                            </h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-icon schools">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-value"><?php echo number_format($totalEscuelas); ?></span>
                                        <span class="stat-label">Escuelas</span>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon students">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-value"><?php echo number_format($totalAlumnos); ?></span>
                                        <span class="stat-label">Alumnos</span>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon teachers">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="stat-content">
                                        <span class="stat-value"><?php echo number_format($totalDocentes); ?></span>
                                        <span class="stat-label">Docentes</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leyenda e Instrucciones -->
                        <div class="info-section">
                            <h3><i class="fas fa-compass"></i> Cómo usar el Mapa</h3>
                            <ul class="instructions-list">
                                <li>
                                    <i class="fas fa-mouse-pointer"></i>
                                    Haga clic en los marcadores para ver información de cada institución
                                </li>
                                <li>
                                    <i class="fas fa-search-plus"></i>
                                    Use los controles de zoom para acercar o alejar la vista
                                </li>
                                <li>
                                    <i class="fas fa-layer-group"></i>
                                    Active o desactive capas para filtrar por nivel educativo
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
                                La información mostrada en el mapa corresponde al ciclo escolar
                                <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>. Los datos se actualizan
                                periódicamente para reflejar los cambios en el sistema educativo estatal.
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
                        Mapa de Instituciones Educativas
                    </h2>
                    <button id="fullscreen-btn" class="fullscreen-btn" title="Pantalla completa">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
                <div class="panel-body">
                    <?php if ($tieneMapaDisponible): ?>
                        <div id="map-container" class="map-container">
                            <iframe id="map-iframe"
                                src="<?php echo htmlspecialchars($urlMapaIframe, ENT_QUOTES, 'UTF-8'); ?>" allowfullscreen
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade">
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
                                    El mapa para el municipio de
                                    <strong><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></strong>
                                    aún no está disponible en nuestro sistema.
                                </p>
                                <div class="error-actions">
                                    <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                                        class="btn-primary">
                                        <i class="fas fa-chart-bar"></i>
                                        Ver Resumen del Municipio
                                    </a>
                                    <a href="home.php" class="btn-secondary">
                                        <i class="fas fa-home"></i>
                                        Volver al Inicio
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pie de página -->
            <footer class="main-footer">
                <div>
                    <div class="mb-lg-0 ml-lg-0 mr-lg-0 mt-lg-0 pb-lg-0 pl-lg-0 pr-lg-0 pt-lg-0">
                        <div id="fragment-1095-melk">
                            <section class="top-top-ft2">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-12 text-center logo-footer">
                                            <img src="./img/heraldicas.png" width="200" height="auto"
                                                alt="Gobierno de Querétaro">
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <section class="top-footer2">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/lugar.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">DIRECCIÓN PALACIO DE GOBIERNO</h3>
                                                <p class="p-footer">
                                                    5 de Mayo S/N esq. Luis Pasteur,
                                                    Col. Centro Histórico C.P. 76000,
                                                    Santiago de Querétaro, Qro.,México.</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/telefono.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">TELÉFONO</h3>
                                                <p class="p-footer">
                                                    800 237 2233<br>
                                                    Directorio (442) 211 7070</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/correo.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">ATENCIÓN CIUDADANA</h3>
                                                <p class="p-footer">
                                                    Preguntas dudas, comentarios sobre el contenido del portal.</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/correo.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">WEB MASTER</h3>
                                                <p class="p-footer">
                                                    Preguntas dudas, comentarios sobre el funcionamiento del portal.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="bottom-footer2 page-editor__disabled-area">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-8 col-sm-12 col-12">
                                            <p><a href="https://queretaro.gob.mx/web/aviso-de-privacidad"
                                                    class="aviso">Aviso de
                                                    privacidad</a></p>
                                            <p>PODER EJECUTIVO DEL ESTADO DE QUERÉTARO Copyright © 2026 Derechos
                                                Reservados. </p>
                                        </div>
                                        <div class="col-md-4 col-sm-12 col-12 text-md-right text-center fin-div">
                                            <div class="social-links">
                                                <ul>
                                                    <li class="social_"><a href="https://wa.me/+524421443740"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M416 208C416 305.2 330 384 224 384C197.3 384 171.9 379 148.8 370L67.2 413.2C57.9 418.1 46.5 416.4 39 409C31.5 401.6 29.8 390.1 34.8 380.8L70.4 313.6C46.3 284.2 32 247.6 32 208C32 110.8 118 32 224 32C330 32 416 110.8 416 208zM416 576C321.9 576 243.6 513.9 227.2 432C347.2 430.5 451.5 345.1 463 229.3C546.3 248.5 608 317.6 608 400C608 439.6 593.7 476.2 569.6 505.6L605.2 572.8C610.1 582.1 608.4 593.5 601 601C593.6 608.5 582.1 610.2 572.8 605.2L491.2 562C468.1 571 442.7 576 416 576z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a
                                                            href="https://www.facebook.com/GobQro?fref=ts"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M240 363.3L240 576L356 576L356 363.3L442.5 363.3L460.5 265.5L356 265.5L356 230.9C356 179.2 376.3 159.4 428.7 159.4C445 159.4 458.1 159.8 465.7 160.6L465.7 71.9C451.4 68 416.4 64 396.2 64C289.3 64 240 114.5 240 223.4L240 265.5L174 265.5L174 363.3L240 363.3z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a href="https://twitter.com/gobqro"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M453.2 112L523.8 112L369.6 288.2L551 528L409 528L297.7 382.6L170.5 528L99.8 528L264.7 339.5L90.8 112L236.4 112L336.9 244.9L453.2 112zM428.4 485.8L467.5 485.8L215.1 152L173.1 152L428.4 485.8z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a
                                                            href="https://www.instagram.com/educacionqueretaro?fbclid=IwZXh0bgNhZW0CMTAAYnJpZBExR09OOWJid2NZT2ZTbUJvRHNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR4yi6bwE_6iEuyyUdbWYkjRLv9zjFFWyxwABVKdZSunmMWOwOsHAv_dcFFBOw_aem_t72qtgoL72OI4Pzyj-oILw"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M320.3 205C256.8 204.8 205.2 256.2 205 319.7C204.8 383.2 256.2 434.8 319.7 435C383.2 435.2 434.8 383.8 435 320.3C435.2 256.8 383.8 205.2 320.3 205zM319.7 245.4C360.9 245.2 394.4 278.5 394.6 319.7C394.8 360.9 361.5 394.4 320.3 394.6C279.1 394.8 245.6 361.5 245.4 320.3C245.2 279.1 278.5 245.6 319.7 245.4zM413.1 200.3C413.1 185.5 425.1 173.5 439.9 173.5C454.7 173.5 466.7 185.5 466.7 200.3C466.7 215.1 454.7 227.1 439.9 227.1C425.1 227.1 413.1 215.1 413.1 200.3zM542.8 227.5C541.1 191.6 532.9 159.8 506.6 133.6C480.4 107.4 448.6 99.2 412.7 97.4C375.7 95.3 264.8 95.3 227.8 97.4C192 99.1 160.2 107.3 133.9 133.5C107.6 159.7 99.5 191.5 97.7 227.4C95.6 264.4 95.6 375.3 97.7 412.3C99.4 448.2 107.6 480 133.9 506.2C160.2 532.4 191.9 540.6 227.8 542.4C264.8 544.5 375.7 544.5 412.7 542.4C448.6 540.7 480.4 532.5 506.6 506.2C532.8 480 541 448.2 542.8 412.3C544.9 375.3 544.9 264.5 542.8 227.5zM495 452C487.2 471.6 472.1 486.7 452.4 494.6C422.9 506.3 352.9 503.6 320.3 503.6C287.7 503.6 217.6 506.2 188.2 494.6C168.6 486.8 153.5 471.7 145.6 452C133.9 422.5 136.6 352.5 136.6 319.9C136.6 287.3 134 217.2 145.6 187.8C153.4 168.2 168.5 153.1 188.2 145.2C217.7 133.5 287.7 136.2 320.3 136.2C352.9 136.2 423 133.6 452.4 145.2C472 153 487.1 168.1 495 187.8C506.7 217.3 504 287.3 504 319.9C504 352.5 506.7 422.6 495 452z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a href="https://www.youtube.com/user/GobQro"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M581.7 188.1C575.5 164.4 556.9 145.8 533.4 139.5C490.9 128 320.1 128 320.1 128C320.1 128 149.3 128 106.7 139.5C83.2 145.8 64.7 164.4 58.4 188.1C47 231 47 320.4 47 320.4C47 320.4 47 409.8 58.4 452.7C64.7 476.3 83.2 494.2 106.7 500.5C149.3 512 320.1 512 320.1 512C320.1 512 490.9 512 533.5 500.5C557 494.2 575.5 476.3 581.8 452.7C593.2 409.8 593.2 320.4 593.2 320.4C593.2 320.4 593.2 231 581.8 188.1zM264.2 401.6L264.2 239.2L406.9 320.4L264.2 401.6z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a href="tel:4422117070" target="_blank"><svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M224.2 89C216.3 70.1 195.7 60.1 176.1 65.4L170.6 66.9C106 84.5 50.8 147.1 66.9 223.3C104 398.3 241.7 536 416.7 573.1C493 589.3 555.5 534 573.1 469.4L574.6 463.9C580 444.2 569.9 423.6 551.1 415.8L453.8 375.3C437.3 368.4 418.2 373.2 406.8 387.1L368.2 434.3C297.9 399.4 241.3 341 208.8 269.3L253 233.3C266.9 222 271.6 202.9 264.8 186.3L224.2 89z" />
                                                            </svg></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </footer>
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