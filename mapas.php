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
require_once 'conexion.php';

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
    <!-- Estilos específicos para la página de resumen (reutilizados para paneles) -->
    <link rel="stylesheet" href="./css/resumen.css">
    <!-- Estilos específicos para la página de mapas -->
    <link rel="stylesheet" href="./css/mapas.css">
    <!-- Font Awesome para iconografía -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="fixed-header-page">
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
                    <a href="home.php" class="header-nav-link ">Inicio</a>
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


            <?php include 'includes/header_end.php'; ?>
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
                    <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="sidebar-link">
                        <i class="fas fa-chart-bar"></i>
                        <span>Resumen</span>
                    </a>
                    <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="sidebar-link">
                        <i class="fas fa-school"></i>
                        <span>Escuelas</span>
                    </a>
                    <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="sidebar-link">
                        <i class="fas fa-user-graduate"></i>
                        <span>Matrícula</span>
                    </a>
                    <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="sidebar-link">
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
                                        educativas en el estado de Querétaro. Puede explorar diferentes niveles
                                        educativos,
                                        obtener información detallada de cada escuela y visualizar la distribución
                                        territorial
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
                                                <span
                                                    class="stat-value"><?php echo number_format($totalEscuelas); ?></span>
                                                <span class="stat-label">Escuelas</span>
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-icon students">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <div class="stat-content">
                                                <span
                                                    class="stat-value"><?php echo number_format($totalAlumnos); ?></span>
                                                <span class="stat-label">Alumnos</span>
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-icon teachers">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </div>
                                            <div class="stat-content">
                                                <span
                                                    class="stat-value"><?php echo number_format($totalDocentes); ?></span>
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
                                        <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>. Los datos se
                                        actualizan
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
                                        src="<?php echo htmlspecialchars($urlMapaIframe, ENT_QUOTES, 'UTF-8'); ?>"
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

                <!-- Botón volver al inicio -->
                <?php include 'includes/back_to_top.php'; ?>
</body>

</html>