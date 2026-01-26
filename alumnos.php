<?php
/**
 * =============================================================================
 * PÁGINA DE ESTADÍSTICAS DE MATRÍCULA ESTUDIANTIL - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Esta página presenta las estadísticas consolidadas de matrícula estudiantil
 * por nivel educativo en Querétaro.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Visualización de matrícula por nivel educativo
 * - Comparativo entre sector público y privado
 * - Gráficos interactivos con Google Charts
 * - Exportación de datos en múltiples formatos
 * - Análisis de distribución por sectores
 * 
 * ARQUITECTURA DE DATOS:
 * - Datos consolidados desde múltiples tablas PostgreSQL
 * - Sistema de fallback con datos representativos
 * - Procesamiento de datos con totales automáticos
 * - Optimización de consultas para rendimiento
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Alumnos
 * @version 2.0
 */

// =============================================================================
// CONFIGURACIÓN E INICIALIZACIÓN DEL SISTEMA
// =============================================================================

// Incluir el helper de sesiones para manejo de autenticación
require_once 'session_helper.php';

// Inicializar sesión y configurar usuario de demostración si es necesario
iniciarSesionDemo();

// Incluir módulo de conexión actualizado para consultas dinámicas
require_once 'conexion_prueba_2024.php';

// =============================================================================
// OBTENCIÓN DE PARÁMETROS Y VALIDACIÓN
// =============================================================================

// Obtener el municipio desde el parámetro GET, por defecto Corregidora
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'CORREGIDORA';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'CORREGIDORA'; // Fallback a Corregidora si el municipio no es válido
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

function formatPercent($value, $decimals = 2)
{
    return number_format((float) $value, $decimals, '.', ',');
}

// =============================================================================
// PROCESAMIENTO DE DATOS DE MATRÍCULA
// =============================================================================

// Obtener datos completos del municipio usando las funciones dinámicas
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Obtener datos de desglose público/privado para el municipio
$datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

// Obtener datos de USAER
$datosUSAER = obtenerDatosUSAER($municipioSeleccionado);

// Verificar si hay datos
$hayError = !$datosCompletosMunicipio;
$tieneDatos = $datosCompletosMunicipio && isset($datosCompletosMunicipio['total_matricula']) && $datosCompletosMunicipio['total_matricula'] > 0;

// Convertir datos al formato que espera el frontend (compatible con formato anterior)
if ($tieneDatos) {
    // Preparar datos en el formato esperado por la interfaz existente
    $datosMatricula = [
        'totales' => [
            'general' => isset($datosCompletosMunicipio['total_matricula']) ? (int) $datosCompletosMunicipio['total_matricula'] : 0,
            'publico' => 0,
            'privado' => 0
        ],
        'datos_por_nivel' => []
    ];

    // Calcular totales público/privado desde los datos de desglose
    // NOTA: Se excluye USAER de los totales (no se suma en público ni privado)
    if (!empty($datosPublicoPrivado)) {
        foreach ($datosPublicoPrivado as $nivel => $datos) {
            $datosMatricula['totales']['publico'] += isset($datos['tot_mat_pub']) ? (int) $datos['tot_mat_pub'] : 0;
            $datosMatricula['totales']['privado'] += isset($datos['tot_mat_priv']) ? (int) $datos['tot_mat_priv'] : 0;

            // Agregar datos por nivel
            $datosMatricula['datos_por_nivel'][$datos['titulo_fila']] = [
                'publico' => isset($datos['tot_mat_pub']) ? (int) $datos['tot_mat_pub'] : 0,
                'privado' => isset($datos['tot_mat_priv']) ? (int) $datos['tot_mat_priv'] : 0,
                'total' => isset($datos['tot_mat']) ? (int) $datos['tot_mat'] : 0
            ];
        }
    }

    // Restar USAER del total de público si existe
    if ($datosUSAER && isset($datosUSAER['tot_mat_pub'])) {
        $datosMatricula['totales']['publico'] -= (int) $datosUSAER['tot_mat_pub'];
    }
    if ($datosUSAER && isset($datosUSAER['tot_mat_priv'])) {
        $datosMatricula['totales']['privado'] -= (int) $datosUSAER['tot_mat_priv'];
    }

    // Preparar datos de género (formato simplificado para compatibilidad)
    $matriculaPorGenero = [];
    if (!empty($datosPublicoPrivado)) {
        foreach ($datosPublicoPrivado as $nivel => $datos) {
            $matriculaPorGenero[$datos['titulo_fila']] = [
                'titulo_fila' => $datos['titulo_fila'],
                'hombres' => isset($datos['mat_h']) ? (int) $datos['mat_h'] : 0,
                'mujeres' => isset($datos['mat_m']) ? (int) $datos['mat_m'] : 0,
                'total' => isset($datos['tot_mat']) ? (int) $datos['tot_mat'] : 0
            ];
        }
    }

    // Datos de discapacidad (usar datos básicos disponibles)
    $alumnosDiscapacidad = []; // Se puede expandir cuando se tengan estos datos en el sistema dinámico

} else {
    // Datos vacíos si no hay información del municipio
    $datosMatricula = [
        'totales' => [
            'general' => 0,
            'publico' => 0,
            'privado' => 0
        ],
        'datos_por_nivel' => []
    ];

    $matriculaPorGenero = [];
    $alumnosDiscapacidad = [];
}

// Extraer datos organizados
$datosPorNivel = $datosMatricula['datos_por_nivel'];
$totales = $datosMatricula['totales'];

// =============================================================================
// PROCESAMIENTO DE DATOS POR NIVEL Y SUBNIVEL EDUCATIVO
// =============================================================================

// Obtener datos de alumnos por nivel y subnivel directamente de la base de datos
$datosAlumnosPorSubnivel = obtenerAlumnosPorNivelYSubnivel($municipioSeleccionado);

// Procesar datos para obtener totales por nivel y subnivel
$datosAlumnosGenero = array();
$datosAlumnosGenero[] = array('Nivel Educativo', 'Subnivel', 'Total Alumnos', 'Hombres', 'Mujeres', '% Hombres', '% Mujeres');

$alumnosPorNivel = array(); // Total por nivel principal
$totalAlumnosSubnivel = 0;

// Procesar datos dinámicos desde la base de datos
if ($datosAlumnosPorSubnivel && is_array($datosAlumnosPorSubnivel)) {
    foreach ($datosAlumnosPorSubnivel as $fila) {
        $nivelPrincipal = $fila['nivel'];
        $nombreSubnivel = $fila['subnivel'];
        $alumnos = intval($fila['total_alumnos']);
        $alumnosH = intval($fila['alumnos_hombres']);
        $alumnosM = intval($fila['alumnos_mujeres']);

        // Calcular porcentajes de género
        $porcH = $alumnos > 0 ? round(($alumnosH / $alumnos) * 100, 1) : 0;
        $porcM = $alumnos > 0 ? round(($alumnosM / $alumnos) * 100, 1) : 0;

        // Agregar a datos de género
        $datosAlumnosGenero[] = array($nivelPrincipal, $nombreSubnivel, $alumnos, $alumnosH, $alumnosM, $porcH, $porcM);

        // Acumular por nivel principal
        if (!isset($alumnosPorNivel[$nivelPrincipal])) {
            $alumnosPorNivel[$nivelPrincipal] = 0;
        }
        $alumnosPorNivel[$nivelPrincipal] += $alumnos;
        $totalAlumnosSubnivel += $alumnos;
    }
}

// =============================================================================
// PREPARACIÓN DE DATOS PARA GRÁFICOS
// =============================================================================

// Convertir datos para gráfico de barras comparativo
$datosGrafico = [];
$datosGrafico[] = ['Nivel Educativo', 'Público', 'Privado'];

foreach ($datosPorNivel as $nivel => $datos) {
    $datosGrafico[] = [$nivel, $datos['publico'], $datos['privado']];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrícula Estudiantil | SEDEQ</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">
    <link rel="stylesheet" href="./css/alumnos.css">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bibliotecas para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
                    <!-- Matrícula con dropdown -->
                    <div class="nav-dropdown">
                        <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                            class="header-nav-link active">Matrícula <i
                                class="fas fa-chevron-down dropdown-arrow"></i></a>
                        <div class="nav-dropdown-content">
                            <a href="#resumen-general" class="nav-dropdown-link">Resumen General</a>
                            <a href="#desglose-sostenimiento" class="nav-dropdown-link">Desglose por Sostenimiento</a>
                            <a href="#analisis-nivel" class="nav-dropdown-link">Análisis por Nivel Educativo</a>
                            <a href="#analisis-genero" class="nav-dropdown-link">Análisis por Género</a>
                            <a href="#tabla-detallada-alumnos" class="nav-dropdown-link">Detalle por Subnivel</a>
                            <a href="#usaer-section" class="nav-dropdown-link">USAER</a>
                        </div>
                    </div>
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

            <!-- Enlace a Resumen -->
            <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-chart-bar"></i> <span>Resumen</span>
            </a>

            <!-- Enlaces a Escuelas -->
            <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                class="sidebar-link">
                <i class="fas fa-school"></i> <span>Escuelas</span>
            </a>

            <!-- Sección de Matrícula con submenú -->
            <div class="sidebar-link-with-submenu">
                <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                    class="sidebar-link active has-submenu">
                    <i class="fas fa-user-graduate"></i>
                    <span>Matrícula</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu active">
                    <a href="#resumen-general" class="submenu-link">
                        <i class="fas fa-users"></i>
                        <span>Resumen General</span>
                    </a>
                    <a href="#desglose-sostenimiento" class="submenu-link">
                        <i class="fas fa-table"></i>
                        <span>Desglose por Sostenimiento</span>
                    </a>
                    <a href="#analisis-nivel" class="submenu-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Análisis por Nivel Educativo</span>
                    </a>
                    <a href="#analisis-genero" class="submenu-link">
                        <i class="fas fa-venus-mars"></i>
                        <span>Análisis por Género</span>
                    </a>
                    <a href="#tabla-detallada-alumnos" class="submenu-link">
                        <i class="fas fa-list-alt"></i>
                        <span>Detalle por Subnivel</span>
                    </a>
                    <a href="#usaer-section" class="submenu-link">
                        <i class="fas fa-hands-helping"></i>
                        <span>USAER</span>
                    </a>
                </div>
            </div>

            <!-- Enlaces a otras secciones -->
            <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-chalkboard-teacher"></i> <span>Docentes</span>
            </a>
            <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-map-marked-alt"></i> <span>Mapas</span>
            </a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Detalle de la Matrícula Estudiantil <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                    - Ciclo <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>
                </h1>
                <?php if (!$tieneDatos): ?>
                    <div
                        style="color: #856404; background-color: #fff3cd; padding: 8px 12px; border-radius: 4px; margin-top: 8px; font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Este municipio no tiene datos disponibles en el ciclo escolar
                        <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">

            <!-- Panel de resumen general -->
            <div id="resumen-general" class="matricula-panel animate-fade delay-1">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-users"></i> Resumen General de Matrícula</h3>
                </div>
                <div class="matricula-body">
                    <div class="municipio-acento"><i class="fas fa-map-marker-alt"></i> Municipio activo:
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                    </div>
                    <div class="stats-row">
                        <div class="stat-box total-general">
                            <div class="stat-value"><?php echo number_format($totales['general']); ?></div>
                            <div class="stat-label">Matrícula</div>
                            <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                        </div>
                        <div class="stat-box sector-publico">
                            <div class="stat-value"><?php echo number_format($totales['publico']); ?></div>
                            <div class="stat-label">Público</div>
                            <div class="stat-percentage">
                                <?php echo formatPercent(($totales['publico'] / $totales['general']) * 100); ?>%
                            </div>

                            <div class="stat-icon"><i class="fas fa-university"></i></div>
                        </div>
                        <div class="stat-box sector-privado">
                            <div class="stat-value"><?php echo number_format($totales['privado']); ?></div>
                            <div class="stat-label">Privado</div>
                            <div class="stat-percentage">
                                <?php echo formatPercent(($totales['privado'] / $totales['general']) * 100); ?>%
                            </div>
                            <div class="stat-icon"><i class="fas fa-building"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Panel de tabla detallada -->
            <div id="desglose-sostenimiento" class="matricula-panel animate-fade delay-2">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-table"></i> Desglose por Tipo de Sostenimiento</h3>
                </div>
                <div class="matricula-body">
                    <div class="municipio-acento"><i class="fas fa-map-marker-alt"></i> Municipio activo:
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                    </div>
                    <div class="table-container">
                        <table id="tabla-matricula" class="data-table">
                            <thead>
                                <tr>
                                    <th>Nivel o Tipo Educativo</th>
                                    <th>Total</th>
                                    <th>Público</th>
                                    <th>% Público</th>
                                    <th>Privado</th>
                                    <th>% Privado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datosPorNivel as $nivel => $datos): ?>
                                    <?php
                                    $porcentajePublico = $datos['total'] > 0 ? round(($datos['publico'] / $datos['total']) * 100, 2) : 0;
                                    $porcentajePrivado = $datos['total'] > 0 ? round(($datos['privado'] / $datos['total']) * 100, 2) : 0;
                                    ?>
                                    <tr>
                                        <td class="nivel-nombre"><?php echo $nivel; ?></td>
                                        <td class="total-nivel"><?php echo number_format($datos['total']); ?></td>
                                        <td class="sector-publico"><?php echo number_format($datos['publico']); ?></td>
                                        <td class="porcentaje-publico"><?php echo formatPercent($porcentajePublico); ?>%
                                        </td>
                                        <td class="sector-privado"><?php echo number_format($datos['privado']); ?></td>
                                        <td class="porcentaje-privado"><?php echo formatPercent($porcentajePrivado); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td><strong>TOTAL GENERAL</strong></td>
                                    <td class="total-nivel">
                                        <strong><?php echo number_format($totales['general']); ?></strong>
                                    </td>
                                    <td class="sector-publico">
                                        <strong><?php echo number_format($totales['publico']); ?></strong>
                                    </td>
                                    <td class="porcentaje-publico">
                                        <strong><?php echo formatPercent(($totales['publico'] / $totales['general']) * 100); ?>%</strong>
                                    </td>
                                    <td class="sector-privado">
                                        <strong><?php echo number_format($totales['privado']); ?></strong>
                                    </td>
                                    <td class="porcentaje-privado">
                                        <strong><?php echo formatPercent(($totales['privado'] / $totales['general']) * 100); ?>%</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de Porcentaje por Nivel Educativo de los Totales del Municipio -->
            <div id="analisis-nivel" class="matricula-panel animate-fade delay-3">
                <div class="matricula-header">
                    <h3><i class="fas fa-percentage"></i> Proporción de los totales por Nivel o Tipo Educativo de la
                        Matrícula Total
                        del Municipio</h3>
                    <div class="export-buttons-section">
                        <button class="export-section-btn" onclick="exportarSeccionPNG('analisis-nivel')"
                            title="Exportar como PNG">
                            <i class="fas fa-image"></i> PNG
                        </button>
                        <button class="export-section-btn" onclick="exportarSeccionPDF('analisis-nivel')"
                            title="Exportar como PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                <div class="matricula-body">
                    <div class="municipio-acento"><i class="fas fa-map-marker-alt"></i> Municipio activo:
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                    </div>
                    <?php if ($tieneDatos && !empty($datosPublicoPrivado)): ?>
                        <div class="totales-municipales-container">
                            <!-- Resumen de Totales Municipales -->
                            <div class="totales-resumen">
                                <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                    <i class="fas fa-chart-bar"></i> Resumen General del Municipio
                                </h3>
                                <div class="totales-generales-grid">
                                    <div class="total-municipal-card">
                                        <div class="total-icono">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div class="total-contenido">
                                            <span class="total-tipo">Total Matrícula</span>
                                            <span
                                                class="total-valor"><?php echo number_format($totales['general'], 0, '.', ','); ?></span>
                                            <span class="total-subtitulo">alumnos</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desglose por Nivel Educativo con Porcentajes Municipales -->
                            <div class="porcentajes-niveles-detalle">
                                <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                    <i class="fas fa-chart-line"></i> Análisis por Nivel Educativo (porcentaje del total del
                                    municipio)
                                </h3>
                                <div class="niveles-municipales-grid">
                                    <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                                        <?php if ($datos['tot_mat'] > 0): ?>
                                            <?php
                                            // Calcular datos de sector dominante
                                            $totalMatricula = isset($datos['tot_mat']) ? (int) $datos['tot_mat'] : 0;
                                            $matriculaPublica = isset($datos['tot_mat_pub']) ? (int) $datos['tot_mat_pub'] : 0;
                                            $matriculaPrivada = isset($datos['tot_mat_priv']) ? (int) $datos['tot_mat_priv'] : 0;
                                            $porcentajePublico = $totalMatricula > 0 ? round(($matriculaPublica / $totalMatricula) * 100, 2) : 0;
                                            $porcentajePrivado = $totalMatricula > 0 ? round(($matriculaPrivada / $totalMatricula) * 100, 2) : 0;
                                            $dominante = $matriculaPublica > $matriculaPrivada ? 'Público' : 'Privado';
                                            $participacion = $totales['general'] > 0 ? round(($totalMatricula / $totales['general']) * 100, 2) : 0;
                                            ?>
                                            <div class="nivel-municipal-card">
                                                <div class="nivel-header">
                                                    <h4><?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </h4>
                                                    <span class="participacion-badge"><?php echo formatPercent($participacion); ?>%
                                                        del
                                                        total</span>
                                                </div>

                                                <!-- Datos detallados -->
                                                <div class="nivel-totales-detalle">
                                                    <div class="total-item-detalle">
                                                        <span class="total-label">Matrícula:</span>
                                                        <span
                                                            class="porcentaje-municipal"><?php echo formatPercent($participacion); ?>%</span>
                                                        <span
                                                            class="total-numero"><?php echo number_format($totalMatricula, 0, '.', ','); ?></span>
                                                    </div>
                                                    <div class="total-item-detalle">
                                                        <span class="total-label">Público:</span>
                                                        <span
                                                            class="porcentaje-municipal"><?php echo formatPercent($porcentajePublico); ?>%</span>
                                                        <span
                                                            class="total-numero"><?php echo number_format($matriculaPublica, 0, '.', ','); ?></span>
                                                    </div>
                                                    <div class="total-item-detalle">
                                                        <span class="total-label">Privado:</span>
                                                        <span
                                                            class="porcentaje-municipal"><?php echo formatPercent($porcentajePrivado); ?>%</span>
                                                        <span
                                                            class="total-numero"><?php echo number_format($matriculaPrivada, 0, '.', ','); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="totales-municipales-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>No hay datos disponibles</h3>
                            <p>No se pudieron obtener datos totales para el municipio de
                                <?php echo strtoupper($municipioSeleccionado); ?>.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Panel de resumen general por género -->
            <div id="analisis-genero" class="matricula-panel animate-fade delay-2">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-venus-mars"></i> Resumen General por Sexo</h3>
                </div>
                <div class="matricula-body">
                    <div class="municipio-acento"><i class="fas fa-map-marker-alt"></i> Municipio activo:
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                    </div>
                    <div class="stats-row">
                        <?php
                        // Calcular totales de hombres y mujeres
                        $totalHombres = 0;
                        $totalMujeres = 0;
                        $totalGeneralGenero = 0;
                        foreach ($matriculaPorGenero as $fila) {
                            $totalHombres += $fila['hombres'];
                            $totalMujeres += $fila['mujeres'];
                            $totalGeneralGenero += $fila['total'];
                        }
                        ?>
                        <div class="stat-box total-general">
                            <div class="stat-value"><?php echo number_format($totalGeneralGenero); ?></div>
                            <div class="stat-label">Matrícula</div>
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="stat-box sector-hombres">
                            <div class="stat-value"><?php echo number_format($totalHombres); ?>
                            </div>
                            <div class="stat-label">Total Hombres</div>
                            <div class="stat-percentage">
                                <?php echo formatPercent($totalGeneralGenero > 0 ? ($totalHombres / $totalGeneralGenero) * 100 : 0); ?>%
                            </div>
                            <div class="stat-icon"><i class="fas fa-mars"></i></div>
                        </div>
                        <div class="stat-box sector-mujeres">
                            <div class="stat-value"><?php echo number_format($totalMujeres); ?>
                            </div>
                            <div class="stat-label">Total Mujeres</div>
                            <div class="stat-percentage">
                                <?php echo formatPercent($totalGeneralGenero > 0 ? ($totalMujeres / $totalGeneralGenero) * 100 : 0); ?>%
                            </div>
                            <div class="stat-icon"><i class="fas fa-venus"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Panel de tabla detallada por género (diseño igual que Análisis por Nivel) -->
            <div class="matricula-panel animate-fade delay-4 matricula-genero">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-venus-mars"></i> Matrícula por Sexo</h3>
                </div>
                <div class="matricula-body">
                    <div class="municipio-acento"><i class="fas fa-map-marker-alt"></i> Municipio activo:
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nivel o Tipo Educativo</th>
                                    <th>Total</th>
                                    <th><i class="fas fa-mars"></i> Hombres</th>
                                    <th>% Hombres</th>
                                    <th><i class="fas fa-venus"></i> Mujeres</th>
                                    <th>% Mujeres</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalHombres = 0;
                                $totalMujeres = 0;
                                $totalGeneralGenero = 0;
                                foreach ($matriculaPorGenero as $fila):
                                    $totalHombres += $fila['hombres'];
                                    $totalMujeres += $fila['mujeres'];
                                    $totalGeneralGenero += $fila['total'];
                                    $porcH = $fila['total'] > 0 ? round(($fila['hombres'] / $fila['total']) * 100, 2) : 0;
                                    $porcM = $fila['total'] > 0 ? round(($fila['mujeres'] / $fila['total']) * 100, 2) : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fila['titulo_fila']); ?></td>
                                        <td><?php echo number_format($fila['total']); ?></td>
                                        <td class="col-hombres"><?php echo number_format($fila['hombres']); ?></td>
                                        <td><?php echo formatPercent($porcH); ?>%</td>
                                        <td class="col-mujeres"><?php echo number_format($fila['mujeres']); ?></td>
                                        <td><?php echo formatPercent($porcM); ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td><strong>TOTAL GENERAL</strong></td>
                                    <td><strong><?php echo number_format($totalGeneralGenero); ?></strong></td>
                                    <td class="col-hombres"><strong><?php echo number_format($totalHombres); ?></strong>
                                    </td>
                                    <td><strong><?php echo formatPercent($totalGeneralGenero > 0 ? ($totalHombres / $totalGeneralGenero) * 100 : 0); ?>%</strong>
                                    </td>
                                    <td class="col-mujeres"><strong><?php echo number_format($totalMujeres); ?></strong>
                                    </td>
                                    <td><strong><?php echo formatPercent($totalGeneralGenero > 0 ? ($totalMujeres / $totalGeneralGenero) * 100 : 0); ?>%</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de análisis de tendencias por género (tarjetas, diseño igual que Análisis por Nivel) -->

            <!-- Tabla detallada por subnivel educativo -->
            <div id="tabla-detallada-alumnos" class="detailed-table animate-fade delay-6">
                <div class="municipio-acento"><i class="fas fa-map-marker-alt"></i> Municipio activo:
                    <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                </div>
                <h4>Detalle por Servicio Educativo</h4>
                <p class="note-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> El servicio "General" contabiliza tanto alumnos de escuelas públicas como
                    privadas.
                </p>
                <!-- Filtro de búsqueda -->
                <div class="search-filter">
                    <input type="text" id="searchAlumnos" placeholder="Buscar por nivel o subnivel..."
                        class="search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nivel o Tipo Educativo</th>
                                <th>Servicio</th>
                                <th>Total Matrícula</th>
                                <th>% del Total General</th>
                                <th>Hombres</th>
                                <th>% Hombres</th>
                                <th>Mujeres</th>
                                <th>% Mujeres</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Reutilizar función de ordenamiento de docentes.php
                            function obtenerOrdenSubnivel($nivel, $subnivel)
                            {
                                $nivel = strtolower($nivel);
                                $subnivel = strtolower($subnivel);

                                // INICIAL ESCOLARIZADA
                                if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'escolarizada') !== false)
                                    return 1;

                                // INICIAL NO ESCOLARIZADA
                                if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'no') !== false)
                                    return 2;

                                // ESPECIAL / CAM
                                if (strpos($nivel, 'especial') !== false || strpos($nivel, 'cam') !== false)
                                    return 3;

                                // PREESCOLAR - Verificar primero General, luego Comunitario, luego Indígena
                                if (strpos($nivel, 'preescolar') !== false) {
                                    if (strpos($subnivel, 'general') !== false)
                                        return 4;
                                    if (strpos($subnivel, 'comunitario') !== false)
                                        return 5;
                                    if (strpos($subnivel, 'indígena') !== false || strpos($subnivel, 'indigena') !== false)
                                        return 6;
                                }

                                // PRIMARIA - Verificar primero General, luego Comunitario, luego Indígena
                                if (strpos($nivel, 'primaria') !== false) {
                                    if (strpos($subnivel, 'general') !== false)
                                        return 7;
                                    if (strpos($subnivel, 'comunitario') !== false)
                                        return 8;
                                    if (strpos($subnivel, 'indígena') !== false || strpos($subnivel, 'indigena') !== false)
                                        return 9;
                                }

                                // SECUNDARIA - Verificar subniveles específicos
                                if (strpos($nivel, 'secundaria') !== false) {
                                    if (strpos($subnivel, 'comunitario') !== false)
                                        return 10;
                                    if (strpos($subnivel, 'general') !== false)
                                        return 11;
                                    if (strpos($subnivel, 'técnica') !== false || strpos($subnivel, 'tecnica') !== false)
                                        return 12;
                                    if (strpos($subnivel, 'telesecundaria') !== false)
                                        return 13;
                                }

                                // MEDIA SUPERIOR
                                if (strpos($nivel, 'media') !== false || strpos($nivel, 'medio') !== false)
                                    return 14;

                                // SUPERIOR
                                if (strpos($nivel, 'superior') !== false)
                                    return 15;

                                return 16; // Para niveles no reconocidos
                            }

                            // Crear array temporal para ordenar
                            $datosOrdenados = array();
                            for ($i = 1; $i < count($datosAlumnosGenero); $i++) {
                                $datosOrdenados[] = array(
                                    'nivel' => $datosAlumnosGenero[$i][0],
                                    'subnivel' => $datosAlumnosGenero[$i][1],
                                    'total' => $datosAlumnosGenero[$i][2],
                                    'hombres' => $datosAlumnosGenero[$i][3],
                                    'mujeres' => $datosAlumnosGenero[$i][4],
                                    'porcentaje_hombres' => $datosAlumnosGenero[$i][5],
                                    'porcentaje_mujeres' => $datosAlumnosGenero[$i][6],
                                    'orden' => obtenerOrdenSubnivel($datosAlumnosGenero[$i][0], $datosAlumnosGenero[$i][1])
                                );
                            }

                            // Ordenar por el campo orden
                            usort($datosOrdenados, function ($a, $b) {
                                return $a['orden'] - $b['orden'];
                            });

                            // Mostrar datos ordenados
                            foreach ($datosOrdenados as $fila):
                                $nivel = $fila['nivel'];
                                $subnivel = $fila['subnivel'];
                                $totalNivel = $fila['total'];
                                $hombres = $fila['hombres'];
                                $mujeres = $fila['mujeres'];
                                $porcentajeHombres = $fila['porcentaje_hombres'];
                                $porcentajeMujeres = $fila['porcentaje_mujeres'];
                                $porcentajeDelTotal = $totalAlumnosSubnivel > 0 ? round(($totalNivel / $totalAlumnosSubnivel) * 100, 2) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($subnivel, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-center"><?php echo number_format($totalNivel); ?></td>
                                    <td class="text-center"><?php echo formatPercent($porcentajeDelTotal); ?>%</td>
                                    <td class="text-center alumnos-hombres"><?php echo number_format($hombres); ?></td>
                                    <td class="text-center porcentaje-hombres">
                                        <?php echo formatPercent($porcentajeHombres); ?>%
                                    </td>
                                    <td class="text-center alumnos-mujeres"><?php echo number_format($mujeres); ?></td>
                                    <td class="text-center porcentaje-mujeres">
                                        <?php echo formatPercent($porcentajeMujeres); ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                                <td class="text-center">
                                    <strong><?php echo number_format($totalAlumnosSubnivel); ?></strong>
                                </td>
                                <td class="text-center"><strong>100.0%</strong></td>
                                <?php
                                // Calcular totales de género
                                $totalHombresSubnivel = 0;
                                $totalMujeresSubnivel = 0;
                                for ($i = 1; $i < count($datosAlumnosGenero); $i++) {
                                    $totalHombresSubnivel += $datosAlumnosGenero[$i][3];
                                    $totalMujeresSubnivel += $datosAlumnosGenero[$i][4];
                                }
                                $porcentajeTotalHombres = $totalAlumnosSubnivel > 0 ? round(($totalHombresSubnivel / $totalAlumnosSubnivel) * 100, 2) : 0;
                                $porcentajeTotalMujeres = $totalAlumnosSubnivel > 0 ? round(($totalMujeresSubnivel / $totalAlumnosSubnivel) * 100, 2) : 0;
                                ?>
                                <td class="text-center alumnos-hombres">
                                    <strong><?php echo number_format($totalHombresSubnivel); ?></strong>
                                </td>
                                <td class="text-center porcentaje-hombres">
                                    <strong><?php echo formatPercent($porcentajeTotalHombres) . '%'; ?></strong>
                                </td>
                                <td class="text-center alumnos-mujeres">
                                    <strong><?php echo number_format($totalMujeresSubnivel); ?></strong>
                                </td>
                                <td class="text-center porcentaje-mujeres">
                                    <strong><?php echo formatPercent($porcentajeTotalMujeres) . '%'; ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

        <!-- Sección de USAER (Unidad de Servicios de Apoyo a la Educación Regular) -->
        <?php if ($datosUSAER): ?>
            <div id="usaer-section" class="matricula-panel animate-fade delay-7">
                <div class="matricula-header">
                    <h3 class="matricula-title">
                        <i class="fas fa-hands-helping"></i> USAER - Unidad de Servicios de Apoyo a la Educación Regular
                    </h3>
                </div>
                <div class="matricula-body">
                    <div class="municipio-acento"><i class="fas fa-map-marker-alt"></i> Municipio activo:
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>
                    </div>
                    <p class="usaer-subtitle">
                        Datos informativos de las Unidades de Servicios de Apoyo a la Educación Regular.
                        Estos datos no se suman en los totales municipales ya que atienden a alumnos contabilizados en los
                        niveles correspondientes.
                    </p>

                    <div class="usaer-container">
                        <!-- Resumen General de USAER -->
                        <div class="usaer-resumen">
                            <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                <i class="fas fa-chart-bar"></i> Resumen de Matrícula USAER
                            </h3>
                            <div class="totales-generales-grid" style="grid-template-columns: 1fr;">
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
                            </div>
                        </div>

                        <!-- Desglose detallado -->
                        <div class="usaer-desglose-detallado">
                            <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                <i class="fas fa-chart-line"></i> Desglose de Matrícula por Sostenimiento y Sexo
                            </h3>

                            <!-- Desglose Público vs Privado -->
                            <div class="usaer-sostenimiento-grid">
                                <!-- Público -->
                                <div class="usaer-sostenimiento-card publico-card">
                                    <h4>
                                        <i class="fas fa-university"></i> Público
                                    </h4>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_mat_pub'], 0, '.', ','); ?> Matrícula
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo $datosUSAER['tot_mat'] > 0 ? round(($datosUSAER['tot_mat_pub'] / $datosUSAER['tot_mat']) * 100, 1) : 0; ?>%
                                        </div>
                                    </div>
                                </div>

                                <!-- Privado -->
                                <div class="usaer-sostenimiento-card privado-card">
                                    <h4>
                                        <i class="fas fa-building"></i> Privado
                                    </h4>
                                    <div class="usaer-dato-grupo">
                                        <div class="numero-principal">
                                            <?php echo number_format($datosUSAER['tot_mat_priv'], 0, '.', ','); ?> Matrícula
                                        </div>
                                        <div class="porcentaje">
                                            <?php echo $datosUSAER['tot_mat'] > 0 ? round(($datosUSAER['tot_mat_priv'] / $datosUSAER['tot_mat']) * 100, 1) : 0; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desglose por Sexo -->
                            <div class="usaer-sexo-section">
                                <h4 style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
                                    <i class="fas fa-venus-mars"></i> Distribución de Matrícula por Sexo
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
                                            <?php echo $datosUSAER['tot_mat'] > 0 ? round(($datosUSAER['mat_h'] / $datosUSAER['tot_mat']) * 100, 1) : 0; ?>%
                                        </div>
                                        <div class="detalles-secundarios">
                                            <?php echo number_format($datosUSAER['mat_h_pub'], 0, '.', ','); ?> Público
                                            (<?php echo $datosUSAER['mat_h'] > 0 ? round(($datosUSAER['mat_h_pub'] / $datosUSAER['mat_h']) * 100, 1) : 0; ?>%)
                                        </div>
                                        <div class="detalles-secundarios">
                                            <?php echo number_format($datosUSAER['mat_h_priv'], 0, '.', ','); ?> Privado
                                            (<?php echo $datosUSAER['mat_h'] > 0 ? round(($datosUSAER['mat_h_priv'] / $datosUSAER['mat_h']) * 100, 1) : 0; ?>%)
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
                                            <?php echo $datosUSAER['tot_mat'] > 0 ? round(($datosUSAER['mat_m'] / $datosUSAER['tot_mat']) * 100, 1) : 0; ?>%
                                        </div>
                                        <div class="detalles-secundarios">
                                            <?php echo number_format($datosUSAER['mat_m_pub'], 0, '.', ','); ?> Público
                                            (<?php echo $datosUSAER['mat_m'] > 0 ? round(($datosUSAER['mat_m_pub'] / $datosUSAER['mat_m']) * 100, 1) : 0; ?>%)
                                        </div>
                                        <div class="detalles-secundarios">
                                            <?php echo number_format($datosUSAER['mat_m_priv'], 0, '.', ','); ?> Privado
                                            (<?php echo $datosUSAER['mat_m'] > 0 ? round(($datosUSAER['mat_m_priv'] / $datosUSAER['mat_m']) * 100, 1) : 0; ?>%)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- BARRERAS DE APRENDIZAJE -->


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
                                                <li class="social_"><a href="https://www.facebook.com/GobQro?fref=ts"
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

    <script>
        // Datos para los gráficos
        const datosGrafico = <?php echo json_encode($datosGrafico); ?>;
        const datosDistribucion = <?php echo json_encode($datosDistribucion); ?>;
        const totales = <?php echo json_encode($totales); ?>;
    </script>
    <script src="./js/script.js"></script>
    <script src="./js/alumnos.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>

</body>

</html>