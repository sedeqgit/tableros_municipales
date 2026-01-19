<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Incluir archivo de conexión actualizado
require_once 'conexion_prueba_2024.php';

// Obtener el municipio desde el parámetro GET, por defecto Querétaro (municipio con más datos)
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'QUERÉTARO';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'QUERÉTARO'; // Fallback a Querétaro si el municipio no es válido
}

// Obtener datos completos del municipio
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Obtener datos de USAER
$datosUSAER = obtenerDatosUSAER($municipioSeleccionado);

// Verificar si hay datos
$hayError = !$datosCompletosMunicipio;
$tieneDatos = $datosCompletosMunicipio &&
    isset($datosCompletosMunicipio['total_matricula']) &&
    $datosCompletosMunicipio['total_matricula'] > 0;

// Inicializar variables por defecto
$totalEscuelas = 0;
$totalAlumnos = 0;
$totalDocentes = 0;
$datosEducativos = [['Tipo Educativo', 'Escuelas', 'Alumnos']];
$datosDocentes = [['Nivel Educativo', 'Subnivel', 'Docentes']];

if ($tieneDatos) {
    // Extraer totales de manera segura y convertir a enteros
    $totalEscuelas = isset($datosCompletosMunicipio['total_escuelas']) ? (int) $datosCompletosMunicipio['total_escuelas'] : 0;
    $totalAlumnos = isset($datosCompletosMunicipio['total_matricula']) ? (int) $datosCompletosMunicipio['total_matricula'] : 0;
    $totalDocentes = isset($datosCompletosMunicipio['total_docentes']) ? (int) $datosCompletosMunicipio['total_docentes'] : 0;

    // Convertir datos al formato que espera el frontend (compatible con conexion.php)
    $datosEducativos = [
        ['Tipo Educativo', 'Escuelas', 'Alumnos']
    ];

    // Función auxiliar para obtener datos de manera segura y convertir a números
    $obtenerDatoSeguro = function ($datos, $nivel, $campo, $default = 0) {
        $valor = isset($datos[$nivel][$campo]) ? $datos[$nivel][$campo] : $default;
        return is_numeric($valor) ? intval($valor) : $default;
    };

    // Solo agregar niveles que tengan datos
    $inicialEscMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_esc', 'tot_mat');
    if ($inicialEscMat > 0) {
        $datosEducativos[] = [
            'Inicial (Escolarizado)',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_esc', 'tot_esc'),
            $inicialEscMat
        ];
    }

    $inicialNoEscMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_no_esc', 'tot_mat');
    if ($inicialNoEscMat > 0) {
        $datosEducativos[] = [
            'Inicial (No Escolarizado)',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_no_esc', 'tot_esc'),
            $inicialNoEscMat
        ];
    }

    $especialMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'especial', 'tot_mat');
    if ($especialMat > 0) {
        $datosEducativos[] = [
            'Especial CAM',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'especial', 'tot_esc'),
            $especialMat
        ];
    }

    // Agregar USAER inmediatamente después de CAM si hay datos disponibles (datos informativos, no se suman en totales)
    if ($datosUSAER && isset($datosUSAER['tot_mat']) && $datosUSAER['tot_mat'] > 0) {
        $datosEducativos[] = [
            'Especial USAER',
            isset($datosUSAER['tot_esc']) ? (int) $datosUSAER['tot_esc'] : 0,
            (int) $datosUSAER['tot_mat']
        ];
    }

    $preescolarMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'preescolar', 'tot_mat');
    if ($preescolarMat > 0) {
        $datosEducativos[] = [
            'Preescolar',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'preescolar', 'tot_esc'),
            $preescolarMat
        ];
    }

    $primariaMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'primaria', 'tot_mat');
    if ($primariaMat > 0) {
        $datosEducativos[] = [
            'Primaria',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'primaria', 'tot_esc'),
            $primariaMat
        ];
    }

    $secundariaMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'secundaria', 'tot_mat');
    if ($secundariaMat > 0) {
        $datosEducativos[] = [
            'Secundaria',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'secundaria', 'tot_esc'),
            $secundariaMat
        ];
    }

    $mediaSupMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'media_sup', 'tot_mat');
    if ($mediaSupMat > 0) {
        $datosEducativos[] = [
            'Media Superior',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'media_sup', 'tot_esc'),
            $mediaSupMat
        ];
    }

    $superiorMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'superior', 'tot_mat');
    if ($superiorMat > 0) {
        $datosEducativos[] = [
            'Superior',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'superior', 'tot_esc'),
            $superiorMat
        ];
    }

    // Convertir datos de docentes al formato esperado
    $datosDocentes = [
        ['Nivel Educativo', 'Subnivel', 'Docentes']
    ];

    // Solo agregar niveles que tengan docentes
    $inicialEscDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_esc', 'tot_doc');
    if ($inicialEscDoc > 0) {
        $datosDocentes[] = ['Inicial Escolarizada', 'General', $inicialEscDoc];
    }

    $inicialNoEscDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_no_esc', 'tot_doc');
    if ($inicialNoEscDoc > 0) {
        $datosDocentes[] = ['Inicial No Escolarizada', 'Comunitario', $inicialNoEscDoc];
    }

    $especialDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'especial', 'tot_doc');
    if ($especialDoc > 0) {
        $datosDocentes[] = ['Especial', 'CAM', $especialDoc];
    }

    // Agregar USAER inmediatamente después de CAM si hay datos disponibles
    if ($datosUSAER && isset($datosUSAER['tot_doc']) && $datosUSAER['tot_doc'] > 0) {
        $datosDocentes[] = [
            'Especial',
            'USAER',
            (int) $datosUSAER['tot_doc']
        ];
    }

    $preescolarDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'preescolar', 'tot_doc');
    if ($preescolarDoc > 0) {
        $datosDocentes[] = ['Preescolar', 'General', $preescolarDoc];
    }

    $primariaDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'primaria', 'tot_doc');
    if ($primariaDoc > 0) {
        $datosDocentes[] = ['Primaria', 'General', $primariaDoc];
    }

    $secundariaDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'secundaria', 'tot_doc');
    if ($secundariaDoc > 0) {
        $datosDocentes[] = ['Secundaria', 'General', $secundariaDoc];
    }

    $mediaSupDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'media_sup', 'tot_doc');
    if ($mediaSupDoc > 0) {
        $datosDocentes[] = ['Media Superior', 'Plantel', $mediaSupDoc];
    }

    $superiorDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'superior', 'tot_doc');
    if ($superiorDoc > 0) {
        $datosDocentes[] = ['Superior', 'Licenciatura', $superiorDoc];
    }
}

// Funciones de compatibilidad para que el resto del código funcione igual
function calcularTotales($datosEducativos)
{
    return [
        'escuelas' => $GLOBALS['totalEscuelas'],
        'alumnos' => $GLOBALS['totalAlumnos']
    ];
}

function calcularTotalesDocentes($datosDocentes)
{
    return [
        'total' => $GLOBALS['totalDocentes']
    ];
}

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

/**
 * Formatea porcentajes con un número fijo de decimales.
 */
function formatPercent($value, $decimals = 2)
{
    return number_format((float) $value, $decimals, '.', '');
}

// Variables de compatibilidad
$totales = calcularTotales($datosEducativos);
$totalesDocentes = calcularTotalesDocentes($datosDocentes);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> (Estadística Educativa)- Ciclo
        <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?> | SEDEQ
    </title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">

    <!-- ========================================== -->
    <!-- HOJAS DE ESTILO MODULARIZADAS             -->
    <!-- ========================================== -->
    <!-- Estilos globales compartidos por todo el sistema -->
    <link rel="stylesheet" href="./css/global.css">
    <!-- Estilos para el header y navegación -->
    <link rel="stylesheet" href="./css/home.css">
    <!-- Estilos específicos para la página de resumen -->
    <link rel="stylesheet" href="./css/resumen.css">
    <!-- Estilos para el menú lateral responsive -->
    <link rel="stylesheet" href="./css/sidebar.css">
    <!-- Iconografía Font Awesome 6.0 para elementos visuales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <!-- Bibliotecas para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <!-- Biblioteca para capturar elementos como imagen -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


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
                    <!-- Resumen con dropdown -->
                    <div class="nav-dropdown">
                        <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                            class="header-nav-link active">Resumen <i
                                class="fas fa-chevron-down dropdown-arrow"></i></a>
                        <div class="nav-dropdown-content">
                            <a href="#resumen-ejecutivo" class="nav-dropdown-link">Resumen Ejecutivo</a>
                            <a href="#desglose-detallado" class="nav-dropdown-link">Desglose por Nivel o Tipo
                                Educativo</a>
                            <a href="#publico-privado" class="nav-dropdown-link">Desglose Detallado por Nivel y Tipo de
                                Sostenimiento</a>
                            <a href="#desglose-sexo" class="nav-dropdown-link">Desglose de Matrícula por Sexo y Tipo de
                                Sostenimiento</a>
                            <a href="#totales-municipales" class="nav-dropdown-link">Porcentajes del Municipio</a>
                            <a href="#usaer-section" class="nav-dropdown-link">USAER</a>
                        </div>
                    </div>
                    <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Escuelas</a>
                    <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Matrícula</a>
                    <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Docentes</a>
                    <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Mapas</a>
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

    <!-- ======================================== -->
    <!-- BARRA LATERAL DE NAVEGACIÓN              -->
    <!-- ======================================== -->
    <aside class="sidebar">
        <!-- Logo en el sidebar -->
        <div class="sidebar-header">
            <img src="./img/layout_set_logo.png" alt="SEDEQ" class="sidebar-logo">
        </div>

        <div class="sidebar-links">
            <!-- Enlace para regresar al home -->
            <a href="home.php" class="sidebar-link">
                <i class="fas fa-home"></i> <span>Regresar al Inicio</span>
            </a>

            <!-- Sección de Resumen con submenú -->
            <div class="sidebar-link-with-submenu">
                <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                    class="sidebar-link active has-submenu">
                    <i class="fas fa-chart-bar"></i>
                    <span>Resumen</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu active">
                    <a href="#resumen-ejecutivo" class="submenu-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Resumen Ejecutivo</span>
                    </a>
                    <a href="#desglose-detallado" class="submenu-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Desglose por Nivel o Tipo Educativo</span>
                    </a>
                    <a href="#publico-privado" class="submenu-link">
                        <i class="fas fa-balance-scale"></i>
                        <span>Público vs Privado</span>
                    </a>
                    <a href="#totales-municipales" class="submenu-link">
                        <i class="fas fa-percentage"></i>
                        <span>Porcentajes del Municipio</span>
                    </a>
                    <a href="#usaer-section" class="submenu-link">
                        <i class="fas fa-user-graduate"></i>
                        <span>USAER</span>
                    </a>
                </div>
            </div>

            <!-- Enlace a Escuelas -->
            <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                class="sidebar-link">
                <i class="fas fa-school"></i> <span>Escuelas</span>
            </a>

            <!-- Enlace a Matrícula -->
            <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-user-graduate"></i> <span>Matrícula</span>
            </a>

            <!-- Enlace a Docentes -->
            <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-chalkboard-teacher"></i> <span>Docentes</span>
            </a>

            <!-- Enlace a Mapas -->
            <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-map-marked-alt"></i> <span>Mapas</span>
            </a>
        </div>

        <!-- Pie de la barra lateral -->
    </aside>

    <!-- ============================================================================ -->
    <!-- CONTENEDOR PRINCIPAL DE LA APLICACIÓN                                       -->
    <!-- ============================================================================ -->
    <div class="app-container">
        <!-- ======================================== -->
        <!-- CONTENIDO PRINCIPAL DE LA APLICACIÓN    -->
        <!-- ======================================== -->
        <main class="main-content">
            <!-- Título de la página -->
            <div class="page-title"
                style="padding: 20px 30px; background: #f8f9fa; margin-bottom: 20px; border-bottom: 2px solid #e9ecef;">
                <h1 class="section-title">
                    Tablero <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> (Estadística Educativa) -
                    Ciclo <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>
                </h1>
            </div>

            <!-- Sección de Resumen Ejecutivo -->
            <div id="resumen-ejecutivo" class="resumen-ejecutivo-section">
                <h2 class="resumen-ejecutivo-title">
                    <i class=""></i> Resumen Ejecutivo
                </h2>
            </div>

            <div class="dashboard-grid">
                <div class="card analysis-card animate-fade delay-3">
                    <div class="card-header">
                        <h2 class="panel-title"><i class="fas fa-table"></i> Datos por Tipo o Nivel Educativo <i
                                class="fas fa-info-circle info-icon"
                                data-tooltip="Los datos de matrícula y escuelas de los servicios USAER no se suman ya que se cuentan en los niveles correspondientes"></i>
                        </h2>
                        <!--                     <div class="card-actions">
                        <button id="exportExcel" class="action-button" title="Exportar a Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                        <button id="exportPDF" class="action-button" title="Exportar a PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>-->
                    </div>
                    <div class="card-body table-container">
                        <table class="data-table animate-up delay-7" id="dataTable">
                            <thead>
                                <tr>
                                    <th>Tipo o Nivel Educativo</th>
                                    <th>Escuelas</th>
                                    <th>Matrícula</th>
                                </tr>
                            </thead>
                            <tbody id="dataTableBody">
                                <!-- Los datos se cargarán dinámicamente desde script.js -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card chart-card animate-fade delay-4">
                    <div class="card-header">
                        <h2 class="panel-title"><i class="fas fa-chart-bar"></i> Estadística por Tipo o Nivel Educativo
                        </h2>
                        <div class="export-buttons">
                            <button id="export-chart-btn" class="export-button" title="Exportar gráfico">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart_div" class="animate-scale delay-5"></div>
                    </div>
                </div>

                <div class="card summary-card animate-fade">
                    <div class="card-header">
                        <h2 class="panel-title"><i class="fas fa-info-circle"></i> Resumen Ejecutivo</h2>
                    </div>
                    <div class="card-body">
                        <div class="metric animate-left delay-2">
                            <div class="metric-icon growth">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title">Total Matrícula <i class="fas fa-info-circle info-icon"
                                        data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                </h3>
                                <p class="metric-value" id="metricDecline">
                                    <?php echo number_format($totalAlumnos, 0, '.', ','); ?>
                                </p>
                                <p class="metric-change" id="metricDeclineChange">Ciclo escolar 2024-2025</p>
                            </div>
                        </div>
                        <div class="metric animate-left delay-1">
                            <div class="metric-icon decline">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="metric-details">


                                <h3 class="metric-title">Total Escuelas <i class="fas fa-info-circle info-icon"
                                        data-tooltip="1. En el total de Escuelas de Media Superior se cuantifican planteles y en Superior se cuantifican instituciones
                                2. El total de Escuelas de Superior en el Estado no corresponde a la suma de escuelas en los municipios, debido a que en algunos casos sólo se registra la institución en la capital del Estado y no se desglosan las unidades académicas en los municipios donde se imparten estudios
                                3. Los datos de escuela de los servicios USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                </h3>
                                <p class="metric-value" id="metricGrowth">
                                    <?php echo number_format($totalEscuelas, 0, '.', ','); ?>
                                </p>
                                <p class="metric-change" id="metricGrowthChange">Ciclo escolar 2024-2025</p>
                            </div>
                        </div>
                        <div class="metric">
                            <div class="metric-icon investment">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="metric-details">
                                <h3 class="metric-title">Total Docentes <i class="fas fa-info-circle info-icon"
                                        data-tooltip="1. El total de docentes de Superior en el
                                        estado no corresponde a la suma de
                                        docentes en los municipios, debido a que
                                        en algunos casos sólo se registra la
                                        institución en la capital del Estado y no se
                                        desglosan las unidades académicas en los
                                        municipios donde se imparten estudios"></i>
                                </h3>
                                <p class="metric-value"><?php echo number_format($totalDocentes, 0, '.', ','); ?></p>
                                <p class="metric-change">Ciclo escolar 2024-2025</p>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="card controls-card animate-right delay-5">
                    <div class="card-header">
                        <h2 class="panel-title"><i class="fas fa-sliders-h"></i> Ajustes de Visualización de Gráfica
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="control-group animate-fade">
                            <label class="slider-label">
                                <i class="fas fa-eye"></i> Mostrar:
                            </label>
                            <div class="control-options">
                                <label class="radio-container">
                                    <input type="radio" name="visualizacion" value="escuelas" checked> Solo Escuelas
                                </label>
                                <label class="radio-container">
                                    <input type="radio" name="visualizacion" value="alumnos"> Solo Matrícula
                                </label>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="slider-label">
                                <i class="fas fa-chart-line"></i> Tipo de gráfico:
                            </label>
                            <div class="control-options">
                                <label class="radio-container">
                                    <input type="radio" name="tipo_grafico" value="column" checked> Columnas
                                </label>
                                <label class="radio-container">
                                    <input type="radio" name="tipo_grafico" value="bar"> Barras
                                </label>
                                <label class="radio-container">
                                    <input type="radio" name="tipo_grafico" value="pie"> Pastel
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sección de Datos Detallados por Categoría -->
            <div id="desglose-detallado" class="datos-section-title">
                <h2>Desglose Detallado por Nivel o Tipo Educativo</h2>
                <p>Distribución específica de escuelas, alumnos y docentes según el nivel o tipo educativo</p>
                <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
            </div>

            <!-- Grid de tarjetas de datos detallados -->
            <div class="datos-grid">
                <?php if ($tieneDatos): ?>
                    <!-- Tarjeta de Escuelas -->
                    <div class="datos-card escuelas-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <h3 class="card-title">Escuelas <i class="fas fa-info-circle info-icon"
                                    data-tooltip="1. En el total de Escuelas de Media Superior se cuantifican planteles y en Superior se cuantifican instituciones
                                2. El total de Escuelas de Superior en el Estado no corresponde a la suma de escuelas en los municipios, debido a que en algunos casos sólo se registra la institución en la capital del Estado y no se desglosan las unidades académicas en los municipios donde se imparten estudios
                                3. Los datos de escuela de los servicios USAER no se suman ya que se encuentran en los niveles correspondientes"></i>

                            </h3>
                        </div>
                        <div class="card-subtitle">No incluye USAER</div>
                        <div class="total-general">
                            <?php echo number_format($totalEscuelas, 0, '.', ','); ?>
                        </div>
                        <div class="detalles-niveles">
                            <?php
                            // Mostrar detalles por nivel educativo para escuelas
                            $nivelesEscuelas = [
                                'inicial_esc' => 'Inicial (Escolarizada)',
                                'inicial_no_esc' => 'Inicial (No Escolarizada)',
                                'especial' => 'Especial (CAM)',
                                'preescolar' => 'Preescolar',
                                'primaria' => 'Primaria',
                                'secundaria' => 'Secundaria',
                                'media_sup' => 'Media Superior',
                                'superior' => 'Superior'
                            ];

                            foreach ($nivelesEscuelas as $nivel => $nombre) {
                                $cantidad = $obtenerDatoSeguro($datosCompletosMunicipio, $nivel, 'tot_esc');
                                if ($cantidad > 0) {
                                    echo "<div class='detalle-nivel'>";
                                    echo "<span class='nivel-nombre'>" . htmlspecialchars($nombre) . "</span>";
                                    echo "<span class='nivel-cantidad'>" . number_format($cantidad, 0, '.', ',') . "</span>";
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Tarjeta de Alumnos -->
                    <div class="datos-card alumnos-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3 class="card-title">Matrícula <i class="fas fa-info-circle info-icon"
                                    data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>

                            </h3>
                        </div>
                        <div class="card-subtitle">No incluye USAER</div>
                        <div class="total-general">
                            <?php echo number_format($totalAlumnos, 0, '.', ','); ?>
                        </div>
                        <div class="detalles-niveles">
                            <?php
                            // Mostrar detalles por nivel educativo para alumnos
                            $nivelesAlumnos = [
                                'inicial_esc' => 'Inicial (Escolarizada)',
                                'inicial_no_esc' => 'Inicial (No Escolarizada)',
                                'especial' => 'Especial (CAM)',
                                'preescolar' => 'Preescolar',
                                'primaria' => 'Primaria',
                                'secundaria' => 'Secundaria',
                                'media_sup' => 'Media Superior',
                                'superior' => 'Superior'
                            ];

                            foreach ($nivelesAlumnos as $nivel => $nombre) {
                                $cantidad = $obtenerDatoSeguro($datosCompletosMunicipio, $nivel, 'tot_mat');
                                if ($cantidad > 0) {
                                    echo "<div class='detalle-nivel'>";
                                    echo "<span class='nivel-nombre'>" . htmlspecialchars($nombre) . "</span>";
                                    echo "<span class='nivel-cantidad'>" . number_format($cantidad, 0, '.', ',') . "</span>";
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Tarjeta de Docentes -->
                    <div class="datos-card docentes-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3 class="card-title">Docentes <i class="fas fa-info-circle info-icon" data-tooltip="1. El total de docentes de Superior en el
                                        estado no corresponde a la suma de
                                        docentes en los municipios, debido a que
                                        en algunos casos sólo se registra la
                                        institución en la capital del Estado y no se
                                        desglosan las unidades académicas en los
                                        municipios donde se imparten estudios"></i>

                            </h3>
                        </div>
                        <div class="card-subtitle">Incluye todos los niveles educativos</div>
                        <div class="total-general">
                            <?php echo number_format($totalDocentes, 0, '.', ','); ?>
                        </div>
                        <div class="detalles-niveles">
                            <?php
                            // Mostrar detalles por nivel educativo para docentes
                            $nivelesDocentes = [
                                'inicial_esc' => 'Inicial (Escolarizada)',
                                'inicial_no_esc' => 'Inicial (No Escolarizada)',
                                'especial' => 'Especial (CAM)',
                                'preescolar' => 'Preescolar',
                                'primaria' => 'Primaria',
                                'secundaria' => 'Secundaria',
                                'media_sup' => 'Media Superior',
                                'superior' => 'Superior'
                            ];

                            foreach ($nivelesDocentes as $nivel => $nombre) {
                                $cantidad = $obtenerDatoSeguro($datosCompletosMunicipio, $nivel, 'tot_doc');
                                if ($cantidad > 0) {
                                    echo "<div class='detalle-nivel'>";
                                    echo "<span class='nivel-nombre'>" . htmlspecialchars($nombre) . "</span>";
                                    echo "<span class='nivel-cantidad'>" . number_format($cantidad, 0, '.', ',') . "</span>";
                                    echo "</div>";
                                }

                                // Agregar USAER inmediatamente después de Especial (CAM)
                                if ($nivel === 'especial' && $datosUSAER && isset($datosUSAER['tot_doc']) && $datosUSAER['tot_doc'] > 0) {
                                    echo "<div class='detalle-nivel'>";
                                    echo "<span class='nivel-nombre'>Especial (USAER)</span>";
                                    echo "<span class='nivel-cantidad'>" . number_format($datosUSAER['tot_doc'], 0, '.', ',') . "</span>";
                                    echo "</div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Mensaje cuando no hay datos -->
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <div
                            style="background-color: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107;">
                            <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 10px;"></i>
                            <h3>No hay datos disponibles</h3>
                            <p>No se encontraron datos para el municipio de
                                <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> en el ciclo escolar actual.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!--  -->
            <div id="publico-privado" class="datos-section-title">
                <h2>
                    <i class=""></i> Desglose Detallado por Nivel y Tipo de Sostenimiento
                </h2>
                <div>
                    <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
                </div>

                <?php
                // Obtener datos con desglose público/privado
                $datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

                // Obtener datos de USAER
                $datosUSAER = obtenerDatosUSAER($municipioSeleccionado);
                ?>

                <?php if (!empty($datosPublicoPrivado)): ?>
                    <div class="datos-grid">
                        <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                            <div class="publico-privado-card">
                                <div class="publico-privado-header">
                                    <div class="card-icon">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <h3 class="card-title">
                                        <?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h3>
                                </div>

                                <!-- Totales -->
                                <div class="totales-generales">
                                    <div class="total-escuelas">
                                        Total: <?php echo number_format($datos['tot_esc'], 0, '.', ','); ?> escuelas <i
                                            class="fas fa-info-circle info-icon"
                                            data-tooltip="1. En el total de Escuelas de Media Superior se cuantifican planteles y en Superior se cuantifican instituciones
                                2. El total de Escuelas de Superior en el Estado no corresponde a la suma de escuelas en los municipios, debido a que en algunos casos sólo se registra la institución en la capital del Estado y no se desglosan las unidades académicas en los municipios donde se imparten estudios
                                3. Los datos de escuela de los servicios USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                    </div>
                                    <div class="total-secundarios">
                                        <?php echo number_format($datos['tot_mat'], 0, '.', ','); ?> matrícula <i
                                            class="fas fa-info-circle info-icon"
                                            data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                        |
                                        <?php echo number_format($datos['tot_doc'], 0, '.', ','); ?> docentes <i
                                            class="fas fa-info-circle info-icon" data-tooltip="1. El total de docentes de Superior en el
                                        estado no corresponde a la suma de
                                        docentes en los municipios, debido a que
                                        en algunos casos sólo se registra la
                                        institución en la capital del Estado y no se
                                        desglosan las unidades académicas en los
                                        municipios donde se imparten estudios"></i>

                                    </div>
                                </div>

                                <!-- Desglose Público/Privado -->
                                <div class="publico-privado-grid">
                                    <!-- Públicas -->
                                    <div class="publico-card">
                                        <h4>
                                            <i class="fas fa-university"></i> Público
                                        </h4>
                                        <div class="detalle-stack">
                                            <div class="detalle-item">
                                                <span class="detalle-label">Escuelas</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['tot_esc'] > 0 ? ($datos['tot_esc_pub'] / $datos['tot_esc']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['tot_esc_pub'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Matrícula</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['tot_mat'] > 0 ? ($datos['tot_mat_pub'] / $datos['tot_mat']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['tot_mat_pub'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Docentes</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['tot_doc'] > 0 ? ($datos['tot_doc_pub'] / $datos['tot_doc']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['tot_doc_pub'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Privadas -->
                                    <div class="privado-card">
                                        <h4>
                                            <i class="fas fa-building"></i> Privado
                                        </h4>
                                        <div class="detalle-stack">
                                            <div class="detalle-item">
                                                <span class="detalle-label">Escuelas</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['tot_esc'] > 0 ? ($datos['tot_esc_priv'] / $datos['tot_esc']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['tot_esc_priv'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Matrícula</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['tot_mat'] > 0 ? ($datos['tot_mat_priv'] / $datos['tot_mat']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['tot_mat_priv'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Docentes</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['tot_doc'] > 0 ? ($datos['tot_doc_priv'] / $datos['tot_doc']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['tot_doc_priv'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="publico-privado-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>No hay datos disponibles</h3>
                        <p>No se pudieron obtener datos de desglose público/privado para el municipio de
                            <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sección de Desglose de Alumnos por Sexo -->
            <div class="datos-section-title" id="desglose-sexo">
                <h2 class="desglose-sexo-title">
                    <i class=""></i> Desglose de Matrícula por Sexo y Tipo de Sostenimiento
                </h2>
                <div>
                    <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
                </div>


                <?php if (!empty($datosPublicoPrivado)): ?>
                    <div class="datos-grid">
                        <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                            <?php
                            // Calcular total de alumnos del nivel
                            $totalAlumnosNivel = $datos['tot_mat'];

                            // Solo mostrar si hay alumnos
                            if ($totalAlumnosNivel > 0):
                                ?>
                                <div class="desglose-sexo-card">
                                    <div class="card-header">
                                        <div class="card-icon">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <h3 class="card-title">
                                            <?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?> <i
                                                class="fas fa-info-circle info-icon"
                                                data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                        </h3>
                                    </div>

                                    <!-- Total de Alumnos -->
                                    <div class="total-alumnos">
                                        <div class="numero-total">
                                            Matrícula Total: <?php echo number_format($totalAlumnosNivel, 0, '.', ','); ?>
                                        </div>
                                    </div>

                                    <!-- Desglose Hombres/Mujeres -->
                                    <div class="sexo-grid">
                                        <!-- Hombres -->
                                        <div class="hombres-card">
                                            <h4>
                                                <i class="fas fa-mars"></i> Hombres
                                            </h4>
                                            <div class="detalle-stack">
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Total</span>
                                                    <div class="detalle-values">
                                                        <span class="detalle-porcentaje">
                                                            <?php echo formatPercent($totalAlumnosNivel > 0 ? ($datos['mat_h'] / $totalAlumnosNivel) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="detalle-numero"><?php echo number_format($datos['mat_h'], 0, '.', ','); ?></span>
                                                    </div>
                                                </div>
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Público</span>
                                                    <div class="detalle-values">
                                                        <span class="detalle-porcentaje">
                                                            <?php echo formatPercent($datos['mat_h'] > 0 ? ($datos['mat_h_pub'] / $datos['mat_h']) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="detalle-numero"><?php echo number_format($datos['mat_h_pub'], 0, '.', ','); ?></span>
                                                    </div>
                                                </div>
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Privado</span>
                                                    <div class="detalle-values">
                                                        <span class="detalle-porcentaje">
                                                            <?php echo formatPercent($datos['mat_h'] > 0 ? ($datos['mat_h_priv'] / $datos['mat_h']) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="detalle-numero"><?php echo number_format($datos['mat_h_priv'], 0, '.', ','); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mujeres -->
                                        <div class="mujeres-card">
                                            <h4>
                                                <i class="fas fa-venus"></i> Mujeres
                                            </h4>
                                            <div class="detalle-stack">
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Total</span>
                                                    <div class="detalle-values">
                                                        <span class="detalle-porcentaje">
                                                            <?php echo formatPercent($totalAlumnosNivel > 0 ? ($datos['mat_m'] / $totalAlumnosNivel) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="detalle-numero"><?php echo number_format($datos['mat_m'], 0, '.', ','); ?></span>
                                                    </div>
                                                </div>
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Público</span>
                                                    <div class="detalle-values">
                                                        <span class="detalle-porcentaje">
                                                            <?php echo formatPercent($datos['mat_m'] > 0 ? ($datos['mat_m_pub'] / $datos['mat_m']) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="detalle-numero"><?php echo number_format($datos['mat_m_pub'], 0, '.', ','); ?></span>
                                                    </div>
                                                </div>
                                                <div class="detalle-item">
                                                    <span class="detalle-label">Privado</span>
                                                    <div class="detalle-values">
                                                        <span class="detalle-porcentaje">
                                                            <?php echo formatPercent($datos['mat_m'] > 0 ? ($datos['mat_m_priv'] / $datos['mat_m']) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="detalle-numero"><?php echo number_format($datos['mat_m_priv'], 0, '.', ','); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="desglose-sexo-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>No hay datos disponibles</h3>
                        <p>No se pudieron obtener datos de desglose por sexo para el municipio de
                            <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sección de Totales Municipales por Nivel -->
            <div id="totales-municipales" class="datos-section-title">
                <h2 class="totales-municipales-title">
                    <i class=""></i> Proporción de los totales por Nivel o Tipo Educativo de los
                    Totales
                    del
                    Municipio
                </h2>
                <div>
                    <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
                </div>


                <?php if ($tieneDatos && !empty($datosCompletosMunicipio)): ?>
                    <div class="totales-municipales-container">
                        <!-- Resumen de Totales Municipales -->
                        <div class="totales-resumen">
                            <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                <i class="fas fa-chart-bar"></i> Resumen General del Municipio
                            </h3>
                            <div class="totales-generales-grid">
                                <div class="total-municipal-card">
                                    <div class="total-icono">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <div class="total-contenido">
                                        <span class="total-tipo">Total Escuelas</span>
                                        <span
                                            class="total-valor"><?php echo number_format($totalEscuelas, 0, '.', ','); ?></span>
                                        <span class="total-subtitulo">escuelas</span>
                                    </div>
                                </div>
                                <div class="total-municipal-card">
                                    <div class="total-icono">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="total-contenido">
                                        <span class="total-tipo">Total Matrícula</span>
                                        <span
                                            class="total-valor"><?php echo number_format($totalAlumnos, 0, '.', ','); ?></span>
                                        <span class="total-subtitulo">alumnos</span>
                                    </div>
                                </div>

                                <div class="total-municipal-card">
                                    <div class="total-icono">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="total-contenido">
                                        <span class="total-tipo">Total Docentes</span>
                                        <span
                                            class="total-valor"><?php echo number_format($totalDocentes, 0, '.', ','); ?></span>
                                        <span class="total-subtitulo">profesores</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desglose por Nivel Educativo con Porcentajes Municipales -->
                        <?php if (!empty($datosPublicoPrivado)): ?>
                            <div class="porcentajes-niveles-detalle">
                                <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                    <i class="fas fa-chart-line"></i> Distribución por Nivel Educativo
                                </h3>
                                <div class="niveles-municipales-grid">
                                    <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                                        <?php if ($datos['tot_mat'] > 0): ?>
                                            <div class="nivel-municipal-card">
                                                <div class="nivel-header">
                                                    <h4><?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                                </div>
                                                <div class="nivel-totales-detalle">
                                                    <div class="total-item-detalle">
                                                        <span class="total-label">Matrícula:</span>
                                                        <span
                                                            class="porcentaje-municipal"><?php echo formatPercent($totalAlumnos > 0 ? ($datos['tot_mat'] / $totalAlumnos) * 100 : 0); ?>%

                                                        </span>
                                                        <span
                                                            class="total-numero"><?php echo number_format($datos['tot_mat'], 0, '.', ','); ?>

                                                        </span>
                                                    </div>
                                                    <div class="total-item-detalle">
                                                        <span class="total-label">Escuelas:</span>
                                                        <span
                                                            class="porcentaje-municipal"><?php echo formatPercent($totalEscuelas > 0 ? ($datos['tot_esc'] / $totalEscuelas) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="total-numero"><?php echo number_format($datos['tot_esc'], 0, '.', ','); ?>

                                                        </span>
                                                    </div>
                                                    <div class="total-item-detalle">
                                                        <span class="total-label">Docentes:</span>
                                                        <span
                                                            class="porcentaje-municipal"><?php echo formatPercent($totalDocentes > 0 ? ($datos['tot_doc'] / $totalDocentes) * 100 : 0); ?>%
                                                        </span>
                                                        <span
                                                            class="total-numero"><?php echo number_format($datos['tot_doc'], 0, '.', ','); ?>

                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="totales-municipales-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>No hay datos disponibles</h3>
                        <p>No se pudieron obtener datos totales para el municipio de
                            <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sección de USAER (Unidad de Servicios de Apoyo a la Educación Regular) -->
            <?php if ($datosUSAER): ?>
                <div id="usaer-section" class="datos-section-title">
                    <h2 class="usaer-title">
                        <i class=""></i> USAER - Unidad de Servicios de Apoyo a la Educación Regular
                    </h2>
                    <div>
                        <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
                    </div>
                    <p class="usaer-subtitle">
                        Datos informativos de las Unidades de Servicios de Apoyo a la Educación Regular.
                        Estos datos no se suman en los totales municipales ya que atienden a alumnos contabilizados en los
                        niveles correspondientes.
                    </p>

                    <div class="usaer-container">
                        <!-- Resumen General de USAER (estilo similar a Totales Municipales) -->
                        <div class="usaer-resumen">
                            <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                <i class="fas fa-chart-bar"></i> Resumen General USAER
                            </h3>
                            <div class="totales-generales-grid">
                                <div class="total-municipal-card">
                                    <div class="total-icono">
                                        <i class="fas fa-school"></i>
                                    </div>
                                    <div class="total-contenido">
                                        <span class="total-tipo">Total Unidades USAER </span>
                                        <span
                                            class="total-valor"><?php echo number_format($datosUSAER['tot_esc'], 0, '.', ','); ?></span>
                                        <span class="total-subtitulo">unidades </span>
                                    </div>
                                </div>
                                <div class="total-municipal-card">
                                    <div class="total-icono">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="total-contenido">
                                        <span class="total-tipo">Total Matrícula Atendida</span>
                                        <span
                                            class="total-valor"><?php echo number_format($datosUSAER['tot_mat'], 0, '.', ','); ?></span>
                                        <span class="total-subtitulo">alumnos</span>
                                    </div>
                                </div>
                                <div class="total-municipal-card">
                                    <div class="total-icono">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="total-contenido">
                                        <span class="total-tipo">Total Personal</span>
                                        <span
                                            class="total-valor"><?php echo number_format($datosUSAER['tot_doc'], 0, '.', ','); ?></span>
                                        <span class="total-subtitulo">personal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desglose detallado por sostenimiento y sexo (estilo similar a Desglose por Sexo) -->
                        <div class="usaer-desglose-detallado">
                            <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                <i class="fas fa-chart-line"></i> Desglose Detallado por Sostenimiento
                            </h3>

                            <!-- Desglose Público vs Privado -->
                            <div class="usaer-sostenimiento-grid">
                                <!-- Públicas -->
                                <div class="usaer-sostenimiento-card publico-card">
                                    <h4>
                                        <i class="fas fa-university"></i> Público
                                    </h4>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_esc_pub'], 0, '.', ','); ?> unidades
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_esc'] > 0 ? ($datosUSAER['tot_esc_pub'] / $datosUSAER['tot_esc']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_mat_pub'], 0, '.', ','); ?> Matrícula
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['tot_mat_pub'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_doc_pub'], 0, '.', ','); ?> personal
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['tot_doc_pub'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                </div>

                                <!-- Privadas -->
                                <div class="usaer-sostenimiento-card privado-card">
                                    <h4>
                                        <i class="fas fa-building"></i> Privado
                                    </h4>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_esc_priv'], 0, '.', ','); ?> unidades
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_esc'] > 0 ? ($datosUSAER['tot_esc_priv'] / $datosUSAER['tot_esc']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_mat_priv'], 0, '.', ','); ?> matrícula
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['tot_mat_priv'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_doc_priv'], 0, '.', ','); ?> personal
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['tot_doc_priv'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desglose por Sexo -->
                            <div class="usaer-sexo-section">
                                <h4 style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
                                    <i class="fas fa-venus-mars"></i> Distribución de Matrícula por Sexo y Sostenimiento
                                </h4>
                                <div class="sexo-grid">
                                    <!-- Hombres -->
                                    <div class="hombres-card">
                                        <h4>
                                            <i class="fas fa-mars"></i> Hombres
                                        </h4>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['mat_h'], 0, '.', ','); ?> Total
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['mat_h'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['mat_h_pub'], 0, '.', ','); ?> Público
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['mat_h'] > 0 ? ($datosUSAER['mat_h_pub'] / $datosUSAER['mat_h']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['mat_h_priv'], 0, '.', ','); ?> Privado
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['mat_h'] > 0 ? ($datosUSAER['mat_h_priv'] / $datosUSAER['mat_h']) * 100 : 0); ?>%
                                        </div>
                                    </div>

                                    <!-- Mujeres -->
                                    <div class="mujeres-card">
                                        <h4>
                                            <i class="fas fa-venus"></i> Mujeres
                                        </h4>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['mat_m'], 0, '.', ','); ?> Total
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['mat_m'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['mat_m_pub'], 0, '.', ','); ?> Público
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['mat_m'] > 0 ? ($datosUSAER['mat_m_pub'] / $datosUSAER['mat_m']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['mat_m_priv'], 0, '.', ','); ?> Privado
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['mat_m'] > 0 ? ($datosUSAER['mat_m_priv'] / $datosUSAER['mat_m']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desglose de Personal por Sexo -->
                            <div class="usaer-personal-section">
                                <h4 style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
                                    <i class="fas fa-user-tie"></i> Distribución de Personal por Sexo y Sostenimiento
                                </h4>
                                <div class="sexo-grid">
                                    <!-- Hombres -->
                                    <div class="hombres-card">
                                        <h4>
                                            <i class="fas fa-mars"></i> Hombres
                                        </h4>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['doc_h'], 0, '.', ','); ?> Total
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['doc_h'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['doc_h_pub'], 0, '.', ','); ?> Público
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['doc_h'] > 0 ? ($datosUSAER['doc_h_pub'] / $datosUSAER['doc_h']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['doc_h_priv'], 0, '.', ','); ?> Privado
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['doc_h'] > 0 ? ($datosUSAER['doc_h_priv'] / $datosUSAER['doc_h']) * 100 : 0); ?>%
                                        </div>
                                    </div>

                                    <!-- Mujeres -->
                                    <div class="mujeres-card">
                                        <h4>
                                            <i class="fas fa-venus"></i> Mujeres
                                        </h4>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['doc_m'], 0, '.', ','); ?> Total
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['doc_m'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['doc_m_pub'], 0, '.', ','); ?> Público
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['doc_m'] > 0 ? ($datosUSAER['doc_m_pub'] / $datosUSAER['doc_m']) * 100 : 0); ?>%
                                        </div>
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['doc_m_priv'], 0, '.', ','); ?> Privado
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo formatPercent($datosUSAER['doc_m'] > 0 ? ($datosUSAER['doc_m_priv'] / $datosUSAER['doc_m']) * 100 : 0); ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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
        </main>
    </div>

    <!-- Modal de selección de formato de exportación -->
    <div id="exportChartModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-download"></i> Exportar Gráfico</h3>
                <button class="modal-close" id="closeExportModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-message">
                    Selecciona el formato de exportación:
                </p>
                <div class="export-options"
                    style="display: flex; gap: 15px; margin-top: 20px; justify-content: center;">
                    <button class="btn-export-option" id="exportPNGBtn"
                        style="flex: 1; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fas fa-image" style="font-size: 24px;"></i>
                        <span>PNG (Imagen)</span>
                    </button>
                    <button class="btn-export-option" id="exportExcelChartBtn"
                        style="flex: 1; padding: 15px; background: #2196F3; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fas fa-file-excel" style="font-size: 24px;"></i>
                        <span>Excel</span>
                    </button>
                    <button class="btn-export-option" id="exportPDFChartBtn"
                        style="flex: 1; padding: 15px; background: #FF5722; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fas fa-file-pdf" style="font-size: 24px;"></i>
                        <span>PDF</span>
                    </button>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" id="cancelExportBtn">Cancelar</button>
            </div>
        </div>
    </div> <!-- Script con datos desde PHP -->
    <script>
        <?php
        // Debugging: Mostrar municipio seleccionado
        echo "console.log('Municipio seleccionado: " . $municipioSeleccionado . "');\n";
        echo "console.log('Tiene datos: " . ($tieneDatos ? 'true' : 'false') . "');\n";
        echo "console.log('Total escuelas: " . $totalEscuelas . "');\n";
        echo "console.log('Total alumnos: " . $totalAlumnos . "');\n";
        echo "console.log('Total docentes: " . $totalDocentes . "');\n";

        // Asegurar que tenemos al menos la estructura básica
        if (count($datosEducativos) <= 1) {
            // Si no hay datos, crear datos por defecto
            $datosEducativos = [
                ['Tipo Educativo', 'Escuelas', 'Alumnos'],
                ['Sin datos', 0, 0]
            ];
        }

        // Convertir a formato JSON para usar en JavaScript
        echo "const datosEducativos = " . json_encode($datosEducativos, JSON_NUMERIC_CHECK) . ";\n";
        echo "const totalEscuelas = " . $totalEscuelas . ";\n";
        echo "const totalAlumnos = " . $totalAlumnos . ";\n";
        echo "const totalDocentes = " . $totalDocentes . ";\n";
        echo "const totalEscuelasFormateado = '" . number_format($totalEscuelas, 0, '.', ',') . "';\n";
        echo "const totalAlumnosFormateado = '" . number_format($totalAlumnos, 0, '.', ',') . "';\n";
        echo "const totalDocentesFormateado = '" . number_format($totalDocentes, 0, '.', ',') . "';\n";
        ?>

    </script>

    <!-- Script para manejar el modal de exportación -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar tooltips personalizados
            if (typeof initCustomTooltips === 'function') {
                initCustomTooltips();
            }

            // Manejo del modal de exportación de gráfico
            const exportChartButton = document.getElementById('export-chart-btn');
            const exportChartModal = document.getElementById('exportChartModal');
            const closeExportModal = document.getElementById('closeExportModal');
            const cancelExportBtn = document.getElementById('cancelExportBtn');

            // Botones de exportación
            const exportPNGBtn = document.getElementById('exportPNGBtn');
            const exportExcelChartBtn = document.getElementById('exportExcelChartBtn');
            const exportPDFChartBtn = document.getElementById('exportPDFChartBtn');

            // Mostrar modal de exportación
            if (exportChartButton) {
                exportChartButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    exportChartModal.classList.add('show');
                });
            }

            // Cerrar modal
            function closeExportModalFunction() {
                exportChartModal.classList.remove('show');
            }

            if (closeExportModal) {
                closeExportModal.addEventListener('click', closeExportModalFunction);
            }

            if (cancelExportBtn) {
                cancelExportBtn.addEventListener('click', closeExportModalFunction);
            }

            // Exportar como PNG
            if (exportPNGBtn) {
                exportPNGBtn.addEventListener('click', function () {
                    closeExportModalFunction();
                    exportarGraficoComoImagen();
                });
            }

            // Exportar como Excel
            if (exportExcelChartBtn) {
                exportExcelChartBtn.addEventListener('click', function () {
                    closeExportModalFunction();
                    exportarGraficoExcel();
                });
            }

            // Exportar como PDF
            if (exportPDFChartBtn) {
                exportPDFChartBtn.addEventListener('click', function () {
                    closeExportModalFunction();
                    exportarGraficoPDF();
                });
            }

            // Cerrar modal al hacer clic fuera del contenido
            exportChartModal.addEventListener('click', function (e) {
                if (e.target === exportChartModal) {
                    closeExportModalFunction();
                }
            });

            // Cerrar modal con tecla ESC
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && exportChartModal.classList.contains('show')) {
                    closeExportModalFunction();
                }
            });
        });
    </script> <!-- Script del dashboard -->
    <script src="./js/export-utils.js"></script>
    <script src="./js/script.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
</body>

</html>