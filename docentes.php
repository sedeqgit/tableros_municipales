<?php
/**
 * =============================================================================
 * PÁGINA DE DETALLE DE DOCENTES - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Esta página presenta un análisis detallado del personal docente en
 * Querétaro, incluyendo distribución por niveles
 * educativos, modalidades y análisis comparativo.
 * 
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Docentes_Detalle
 */

// =============================================================================
// CONFIGURACIÓN DEL ENTORNO DE DESARROLLO
// =============================================================================

// Incluir el helper de sesiones para manejo de autenticación
require_once 'session_helper.php';

// Inicializar sesión y configurar usuario de demostración si es necesario
iniciarSesionDemo();

// CONFIGURACIÓN DE DEPURACIÓN (remover en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =============================================================================
// OBTENCIÓN Y PROCESAMIENTO DE DATOS DE DOCENTES
// =============================================================================

// Incluir módulo de conexión actualizado con soporte de municipios dinámicos
require_once 'conexion_prueba_2024.php';

// Configurar codificación UTF-8 para evitar problemas con acentos
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Obtener municipio desde parámetro GET, por defecto CORREGIDORA
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

/**
 * Formatea porcentajes con un número fijo de decimales.
 */
function formatPercent($value, $decimals = 2)
{
    return number_format((float) $value, $decimals, '.', '');
}

// Obtener datos completos del municipio usando la función correcta
$datosCompletos = obtenerResumenMunicipioCompleto($municipioSeleccionado);
$datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

// Obtener datos de USAER
$datosUSAER = obtenerDatosUSAER($municipioSeleccionado);

// =============================================================================
// PROCESAMIENTO DE DATOS POR NIVEL EDUCATIVO
// =============================================================================

// Obtener datos de docentes por nivel y subnivel directamente de la base de datos
$datosDocentesPorSubnivel = obtenerDocentesPorNivelYSubnivel($municipioSeleccionado);

// Procesar datos para obtener totales por nivel y subnivel
$datosDocentesGenero = array();
$datosDocentesGenero[] = array('Nivel Educativo', 'Subnivel', 'Total Docentes', 'Hombres', 'Mujeres', '% Hombres', '% Mujeres');

$docentesPorNivel = array(); // Total por nivel principal
$totalDocentes = 0;

// NOTA: Esta función ya NO se usa porque la normalización se hace directamente en SQL
// en la función obtenerDocentesPorNivelYSubnivel() del archivo conexion_prueba_2024.php
// Se mantiene comentada por si se necesita en el futuro para otros propósitos

/*
function normalizarTextoEducativo($texto)
{
    // Limpiar el texto y remover caracteres invisibles/control
    $texto = trim($texto);

    // Usar expresiones regulares para detectar patrones con caracteres corruptos
    // Patrón para detectar "T[cualquier carácter corrupto]CNICA" (incluyendo variantes como T?:Cnica)
    if (preg_match('/^T.{0,3}CNICA$/i', $texto)) {
        return 'Técnica';
    }

    // Patrón para detectar "IND[cualquier carácter corrupto]GENA"
    if (preg_match('/^IND.{0,3}GENA$/i', $texto)) {
        return 'Indígena';
    }

    // Correcciones exactas para otros casos
    $correcciones = array(
        'TECNICA' => 'Técnica',
        'INDIGENA' => 'Indígena',
        'TELESECUNDARIA' => 'Telesecundaria',
        'GENERAL' => 'General',
        'COMUNITARIO' => 'Comunitario',
        'CAM' => 'Cam',
        'LACTANTE Y MATERNAL' => 'Lactante Y Maternal',
        'NO ESCOLARIZADA' => 'No Escolarizada',
        'INICIAL ESCOLARIZADA' => 'Inicial Escolarizada',
        'INICIAL NO ESCOLARIZADA' => 'Inicial No Escolarizada',
        'ESPECIAL CAM' => 'Especial Cam',
        'PREESCOLAR' => 'Preescolar',
        'PRIMARIA' => 'Primaria',
        'SECUNDARIA' => 'Secundaria',
        'MEDIA SUPERIOR' => 'Media Superior',
        'SUPERIOR' => 'Superior'
    );

    // Buscar coincidencia exacta
    $textoUpper = strtoupper($texto);
    if (isset($correcciones[$textoUpper])) {
        return $correcciones[$textoUpper];
    }

    // Si no hay coincidencia, aplicar formato título
    return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
}
*/

// Procesar datos dinámicos desde la base de datos
if ($datosDocentesPorSubnivel && is_array($datosDocentesPorSubnivel)) {
    foreach ($datosDocentesPorSubnivel as $fila) {
        // Los datos ya vienen normalizados desde SQL, no necesitamos normalizar en PHP
        $nivelPrincipal = $fila['nivel'];
        $nombreSubnivel = $fila['subnivel'];
        $docentes = intval($fila['total_docentes']);
        $docentesH = intval($fila['doc_hombres']);
        $docentesM = intval($fila['doc_mujeres']);

        // Calcular porcentajes de género
        $porcH = $docentes > 0 ? round(($docentesH / $docentes) * 100, 2) : 0;
        $porcM = $docentes > 0 ? round(($docentesM / $docentes) * 100, 2) : 0;

        // Agregar a datos de género
        $datosDocentesGenero[] = array($nivelPrincipal, $nombreSubnivel, $docentes, $docentesH, $docentesM, $porcH, $porcM);

        // Acumular por nivel principal
        if (!isset($docentesPorNivel[$nivelPrincipal])) {
            $docentesPorNivel[$nivelPrincipal] = 0;
        }
        $docentesPorNivel[$nivelPrincipal] += $docentes;
        $totalDocentes += $docentes;
    }
}

// =============================================================================
// ANÁLISIS POR TIPO DE SOSTENIMIENTO
// =============================================================================

// Procesar datos de sostenimiento (público vs privado)
$docentesPublicos = 0;
$docentesPrivados = 0;
$docentesNivelSostenimiento = array();

if (isset($datosPublicoPrivado) && is_array($datosPublicoPrivado)) {
    foreach ($datosPublicoPrivado as $key => $nivel) {
        $publicos = isset($nivel['tot_doc_pub']) ? $nivel['tot_doc_pub'] : 0;
        $privados = isset($nivel['tot_doc_priv']) ? $nivel['tot_doc_priv'] : 0;

        $docentesPublicos += $publicos;
        $docentesPrivados += $privados;

        // Almacenar por nivel para visualización
        $nombreNivel = isset($nivel['titulo_fila']) ? $nivel['titulo_fila'] : $key;
        $docentesNivelSostenimiento[$nombreNivel] = array(
            'publicos' => $publicos,
            'privados' => $privados
        );
    }
}

// Calcular porcentajes
$totalGeneral = $docentesPublicos + $docentesPrivados;
$porcentajePublicos = $totalGeneral > 0 ? round(($docentesPublicos / $totalGeneral) * 100, 2) : 0;
$porcentajePrivados = $totalGeneral > 0 ? round(($docentesPrivados / $totalGeneral) * 100, 2) : 0;

// =============================================================================
// CÁLCULOS COMPLEMENTARIOS
// =============================================================================

// Calcular distribución porcentual para análisis comparativo
$porcentajesDocentes = array();
foreach ($docentesPorNivel as $nivel => $cantidad) {
    $porcentajesDocentes[$nivel] = round(($cantidad / $totalDocentes) * 100, 2);
}

// Análisis de concentración (niveles con mayor cantidad de docentes)
$nivelesOrdenados = $docentesPorNivel;
arsort($nivelesOrdenados);
$nivelMayorConcentracion = array_key_first($nivelesOrdenados);
$porcentajeMayorConcentracion = isset($porcentajesDocentes[$nivelMayorConcentracion]) ?
    $porcentajesDocentes[$nivelMayorConcentracion] : 0;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Docentes | SEDEQ</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <link rel="stylesheet" href="./css/escuelas_detalle.css">
    <link rel="stylesheet" href="./css/docentes.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="docentes-page">
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
                    <!-- Docentes con dropdown -->
                    <div class="nav-dropdown">
                        <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                            class="header-nav-link active">Docentes <i
                                class="fas fa-chevron-down dropdown-arrow"></i></a>
                        <div class="nav-dropdown-content">
                            <a href="#resumen-docentes" class="nav-dropdown-link">Resumen General</a>
                            <a href="#distribucion-nivel" class="nav-dropdown-link">Distribución por Nivel</a>
                            <a href="#tabla-detallada" class="nav-dropdown-link">Tabla Detallada</a>
                            <a href="#usaer-section" class="nav-dropdown-link">USAER</a>
                        </div>
                    </div>
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
            <?php
            // Construir parámetro de municipio para mantener persistencia en navegación
            $paramMunicipio = '?municipio=' . urlencode($municipioSeleccionado);
            ?>
            <!-- Enlace para regresar al home -->
            <a href="home.php" class="sidebar-link">
                <i class="fas fa-home"></i> <span>Regresar al Inicio</span>
            </a>

            <!-- Enlaces a otras páginas -->
            <a href="resumen.php<?php echo $paramMunicipio; ?>" class="sidebar-link">
                <i class="fas fa-chart-bar"></i> <span>Resumen</span>
            </a>
            <a href="escuelas_detalle.php<?php echo $paramMunicipio; ?>" class="sidebar-link">
                <i class="fas fa-school"></i> <span>Escuelas</span>
            </a>
            <a href="alumnos.php<?php echo $paramMunicipio; ?>" class="sidebar-link">
                <i class="fas fa-user-graduate"></i> <span>Matrícula</span>
            </a>

            <!-- Sección de Docentes con submenú -->
            <div class="sidebar-link-with-submenu">
                <a href="docentes.php<?php echo $paramMunicipio; ?>" class="sidebar-link active has-submenu">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Docentes</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu active">
                    <a href="#resumen-docentes" class="submenu-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Resumen General</span>
                    </a>
                    <a href="#distribucion-nivel" class="submenu-link">
                        <i class="fas fa-chart-bar"></i>
                        <span>Distribución por Nivel</span>
                    </a>
                    <a href="#tabla-detallada" class="submenu-link">
                        <i class="fas fa-table"></i>
                        <span>Tabla Detallada</span>
                    </a>
                    <a href="#usaer-section" class="submenu-link">
                        <i class="fas fa-hands-helping"></i>
                        <span>USAER</span>
                    </a>
                </div>
            </div>

            <a href="mapas.php<?php echo $paramMunicipio; ?>" class="sidebar-link">
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
                <h1>Detalle de Docentes <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> - Ciclo
                    <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>
                </h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Panel de resumen de docentes -->
            <div id="resumen-docentes" class="panel animate-up">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-chalkboard-teacher"></i> Resumen General de Docentes
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="stats-row">
                        <div class="stat-box animate-fade delay-1">
                            <div class="stat-value"><?php echo number_format($totalDocentes); ?></div>
                            <div class="stat-label">Total Docentes</div>
                        </div>
                        <div class="stat-box animate-fade delay-2">
                            <div class="stat-value">
                                <span class="public-schools"><?php echo $docentesPublicos; ?></span>
                            </div>
                            <div class="stat-label">Público</div>
                        </div>
                        <div class="stat-box animate-fade delay-3">
                            <div class="stat-value">
                                <span class="private-schools"><?php echo $docentesPrivados; ?></span>
                            </div>
                            <div class="stat-label">Privados</div>
                        </div>
                    </div>

                    <!-- Gráfico de distribución pública vs privada -->
                    <div class="sostenimiento-chart animate-fade delay-3">
                        <h4>Distribución por Tipo Sostenimiento</h4>

                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill public"
                                    style="width: <?php echo formatPercent($porcentajePublicos); ?>%">
                                    <span class="progress-label"><?php echo formatPercent($porcentajePublicos); ?>%
                                        Públicos</span>
                                </div>
                                <div class="progress-fill private"
                                    style="width: <?php echo formatPercent($porcentajePrivados); ?>%">
                                    <span class="progress-label"><?php echo formatPercent($porcentajePrivados); ?>%
                                        Privados</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Barras de progreso por nivel -->
                    <div id="distribucion-nivel" class="level-bars animate-sequence">
                        <div class="nivel-header">
                            <h4>Distribución por Nivel o Tipo Educativo</h4>
                            <div class="view-toggle-buttons">
                                <button class="view-toggle-btn active" data-view="grafico">
                                    <i class="fas fa-chart-pie"></i> Vista Gráfico
                                </button>
                                <button class="view-toggle-btn" data-view="barras">
                                    <i class="fas fa-chart-bar"></i> Vista Barras
                                </button>

                            </div>
                        </div>
                        <div class="sostenimiento-filters">
                            <button class="filter-btn active" data-filter="total">Total</button>
                            <button class="filter-btn" data-filter="publico">Público</button>
                            <button class="filter-btn" data-filter="privado">Privado</button>
                        </div>
                        <!-- Vista de Barras -->
                        <div id="vista-barras" class="visualization-container" style="display: none;">
                            <?php
                            // Función para determinar el orden educativo basado en palabras clave
                            function obtenerOrdenEducativo($nivel)
                            {
                                $nivel = strtolower($nivel);
                                if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'escolarizada') !== false)
                                    return 1;
                                if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'no') !== false)
                                    return 2;
                                if (strpos($nivel, 'especial') !== false || strpos($nivel, 'cam') !== false)
                                    return 3;
                                if (strpos($nivel, 'preescolar') !== false)
                                    return 4;
                                if (strpos($nivel, 'primaria') !== false)
                                    return 5;
                                if (strpos($nivel, 'secundaria') !== false)
                                    return 6;
                                if (strpos($nivel, 'media') !== false || strpos($nivel, 'medio') !== false)
                                    return 7;
                                if (strpos($nivel, 'superior') !== false)
                                    return 8;
                                return 9; // Para niveles no reconocidos
                            }

                            // Ordenar según el orden educativo lógico
                            $nivelesOrdenadosDisplay = $docentesPorNivel;
                            uksort($nivelesOrdenadosDisplay, function ($a, $b) {
                                return obtenerOrdenEducativo($a) - obtenerOrdenEducativo($b);
                            });
                            foreach ($nivelesOrdenadosDisplay as $nivel => $cantidad):
                                $porcentaje = $porcentajesDocentes[$nivel];
                                // Obtener datos de sostenimiento para este nivel
                                $publicos = isset($docentesNivelSostenimiento[$nivel]) ? $docentesNivelSostenimiento[$nivel]['publicos'] : 0;
                                $privados = isset($docentesNivelSostenimiento[$nivel]) ? $docentesNivelSostenimiento[$nivel]['privados'] : 0;
                                ?>
                                <div class="level-bar"
                                    data-nivel="<?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-publicos="<?php echo $publicos; ?>" data-privados="<?php echo $privados; ?>"
                                    data-total="<?php echo $cantidad; ?>">
                                    <span
                                        class="level-name"><?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <div class="level-track">
                                        <div class="level-fill" style="width: <?php echo formatPercent($porcentaje); ?>%">
                                            <span class="escuelas-count"><?php echo number_format($cantidad); ?></span>
                                        </div>
                                    </div>
                                    <span class="level-percent"><?php echo formatPercent($porcentaje); ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Fin Vista Barras -->

                        <!-- Vista Gráfico (Por defecto) -->
                        <div id="vista-grafico" class="visualization-container">
                            <div id="pie-chart-nivel" style="width: 100%; height: 400px;"></div>
                        </div>
                        <!-- Fin Vista Gráfico -->
                    </div>

                    <!-- Tabla detallada -->
                    <div id="tabla-detallada" class="detailed-table animate-fade delay-4">
                        <h4>Detalle por Servicio Educativo</h4>
                        <p class="note-info"
                            style="margin-top: 10px; margin-bottom: 15px; font-size: 0.9em; color: #666; font-style: italic;">
                            <strong>Nota:</strong> El subnivel "General" contabiliza tanto docentes públicos como
                            privados.
                        </p>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nivel Educativo</th>
                                        <th>Servicio</th>
                                        <th>Total Docentes</th>
                                        <th>% del Total General</th>
                                        <th>Hombres</th>
                                        <th>% Hombres</th>
                                        <th>Mujeres</th>
                                        <th>% Mujeres</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Función para determinar orden de subniveles (similar a la anterior)
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
                                    for ($i = 1; $i < count($datosDocentesGenero); $i++) {
                                        $datosOrdenados[] = array(
                                            'nivel' => $datosDocentesGenero[$i][0],
                                            'subnivel' => $datosDocentesGenero[$i][1],
                                            'total' => $datosDocentesGenero[$i][2],
                                            'hombres' => $datosDocentesGenero[$i][3],
                                            'mujeres' => $datosDocentesGenero[$i][4],
                                            'porcentaje_hombres' => $datosDocentesGenero[$i][5],
                                            'porcentaje_mujeres' => $datosDocentesGenero[$i][6],
                                            'orden' => obtenerOrdenSubnivel($datosDocentesGenero[$i][0], $datosDocentesGenero[$i][1])
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
                                        $porcentajeDelTotal = round(($totalNivel / $totalDocentes) * 100, 2);
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($subnivel, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center"><?php echo number_format($totalNivel); ?></td>
                                            <td class="text-center"><?php echo formatPercent($porcentajeDelTotal); ?>%</td>
                                            <td class="text-center docentes-hombres"><?php echo number_format($hombres); ?>
                                            </td>
                                            <td class="text-center porcentaje-hombres">
                                                <?php echo formatPercent($porcentajeHombres); ?>%
                                            </td>
                                            <td class="text-center docentes-mujeres"><?php echo number_format($mujeres); ?>
                                            </td>
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
                                            <strong><?php echo number_format($totalDocentes); ?></strong>
                                        </td>
                                        <td class="text-center"><strong><?php echo formatPercent(100); ?>%</strong></td>
                                        <?php
                                        // Calcular totales de género
                                        $totalHombres = 0;
                                        $totalMujeres = 0;
                                        for ($i = 1; $i < count($datosDocentesGenero); $i++) {
                                            $totalHombres += $datosDocentesGenero[$i][3];
                                            $totalMujeres += $datosDocentesGenero[$i][4];
                                        }
                                        $porcentajeTotalHombres = $totalDocentes > 0 ? round(($totalHombres / $totalDocentes) * 100, 2) : 0;
                                        $porcentajeTotalMujeres = $totalDocentes > 0 ? round(($totalMujeres / $totalDocentes) * 100, 2) : 0;
                                        ?>
                                        <td class="text-center">
                                            <strong><?php echo number_format($totalHombres); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <strong><?php echo formatPercent($porcentajeTotalHombres); ?>%</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong><?php echo number_format($totalMujeres); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <strong><?php echo formatPercent($porcentajeTotalMujeres); ?>%</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de USAER (Unidad de Servicios de Apoyo a la Educación Regular) -->
            <?php if ($datosUSAER && isset($datosUSAER['tot_doc']) && $datosUSAER['tot_doc'] > 0): ?>
                <?php
                // Preparar datos de USAER con valores seguros (evitar nulls)
                $totalDocUSAER = isset($datosUSAER['tot_doc']) ? (int) $datosUSAER['tot_doc'] : 0;
                $totalDocPubUSAER = isset($datosUSAER['tot_doc_pub']) ? (int) $datosUSAER['tot_doc_pub'] : 0;
                $totalDocPrivUSAER = isset($datosUSAER['tot_doc_priv']) ? (int) $datosUSAER['tot_doc_priv'] : 0;
                $docHUSAER = isset($datosUSAER['doc_h']) ? (int) $datosUSAER['doc_h'] : 0;
                $docHPubUSAER = isset($datosUSAER['doc_h_pub']) ? (int) $datosUSAER['doc_h_pub'] : 0;
                $docHPrivUSAER = isset($datosUSAER['doc_h_priv']) ? (int) $datosUSAER['doc_h_priv'] : 0;
                $docMUSAER = isset($datosUSAER['doc_m']) ? (int) $datosUSAER['doc_m'] : 0;
                $docMPubUSAER = isset($datosUSAER['doc_m_pub']) ? (int) $datosUSAER['doc_m_pub'] : 0;
                $docMPrivUSAER = isset($datosUSAER['doc_m_priv']) ? (int) $datosUSAER['doc_m_priv'] : 0;
                ?>
                <div id="usaer-section" class="panel animate-fade delay-5">
                    <div class="panel-header">
                        <h3 class="panel-title">
                            <i class="fas fa-hands-helping"></i> USAER - Unidad de Servicios de Apoyo a la Educación Regular
                        </h3>
                    </div>
                    <div class="panel-body">
                        <p class="note-info" style="margin-bottom: 20px;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> Datos informativos de las Unidades de Servicios de Apoyo a la Educación
                            Regular.
                            Estos datos no se suman en los totales municipales ya que atienden a alumnos contabilizados en
                            los
                            niveles correspondientes.
                        </p>

                        <div class="usaer-container">
                            <!-- Resumen General de USAER -->
                            <div class="usaer-resumen">
                                <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                    <i class="fas fa-chart-bar"></i> Resumen de Personal USAER
                                </h3>
                                <div class="totales-generales-grid" style="grid-template-columns: 1fr;">
                                    <div class="total-municipal-card">
                                        <div class="total-icono">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <div class="total-contenido">
                                            <span class="total-tipo">Total Personal</span>
                                            <span
                                                class="total-valor"><?php echo number_format($totalDocUSAER, 0, '.', ','); ?></span>
                                            <span class="total-subtitulo">personal</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desglose detallado -->
                            <div class="usaer-desglose-detallado">
                                <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                    <i class="fas fa-chart-line"></i> Desglose de Personal por Sostenimiento y Sexo
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
                                                <?php echo number_format($totalDocPubUSAER, 0, '.', ','); ?> Personal
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo formatPercent($totalDocUSAER > 0 ? ($totalDocPubUSAER / $totalDocUSAER) * 100 : 0); ?>%
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
                                                <?php echo number_format($totalDocPrivUSAER, 0, '.', ','); ?> personal
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo formatPercent($totalDocUSAER > 0 ? ($totalDocPrivUSAER / $totalDocUSAER) * 100 : 0); ?>%
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Desglose por Sexo -->
                                <div class="usaer-sexo-section">
                                    <h4 style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
                                        <i class="fas fa-venus-mars"></i> Distribución de Personal por Sexo
                                    </h4>
                                    <div class="sexo-grid">
                                        <!-- Hombres -->
                                        <div class="hombres-card">
                                            <h4>
                                                <i class="fas fa-mars"></i> Hombres
                                            </h4>
                                            <div class="numero-principal">
                                                <?php echo number_format($docHUSAER, 0, '.', ','); ?> Total
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo formatPercent($totalDocUSAER > 0 ? ($docHUSAER / $totalDocUSAER) * 100 : 0); ?>%
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($docHPubUSAER, 0, '.', ','); ?> Público
                                                (<?php echo formatPercent($docHUSAER > 0 ? ($docHPubUSAER / $docHUSAER) * 100 : 0); ?>%)
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($docHPrivUSAER, 0, '.', ','); ?> Privado
                                                (<?php echo formatPercent($docHUSAER > 0 ? ($docHPrivUSAER / $docHUSAER) * 100 : 0); ?>%)
                                            </div>
                                        </div>

                                        <!-- Mujeres -->
                                        <div class="mujeres-card">
                                            <h4>
                                                <i class="fas fa-venus"></i> Mujeres
                                            </h4>
                                            <div class="numero-principal">
                                                <?php echo number_format($docMUSAER, 0, '.', ','); ?> Total
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo formatPercent($totalDocUSAER > 0 ? ($docMUSAER / $totalDocUSAER) * 100 : 0); ?>%
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($docMPubUSAER, 0, '.', ','); ?> Público
                                                (<?php echo formatPercent($docMUSAER > 0 ? ($docMPubUSAER / $docMUSAER) * 100 : 0); ?>%)
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($docMPrivUSAER, 0, '.', ','); ?> Privado
                                                (<?php echo formatPercent($docMUSAER > 0 ? ($docMPrivUSAER / $docMUSAER) * 100 : 0); ?>%)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
    </div><!-- Scripts -->
    <script src="./js/sidebar.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/docentes.js"></script>
    <script type="text/javascript">
        // Variables globales para el manejo de sostenimiento
        <?php
        echo "const totalDocentes = " . $totalDocentes . ";\n";
        echo "const docentesPublicos = " . $docentesPublicos . ";\n";
        echo "const docentesPrivados = " . $docentesPrivados . ";\n";
        echo "const porcentajePublicos = " . $porcentajePublicos . ";\n";
        echo "const porcentajePrivados = " . $porcentajePrivados . ";\n";

        // Datos por nivel para filtros
        echo "const docentesPorNivel = " . json_encode($docentesPorNivel) . ";\n";
        echo "const docentesNivelSostenimiento = " . json_encode($docentesNivelSostenimiento) . ";\n";
        ?>

        // Asegurar que las tarjetas sean visibles inmediatamente
        document.addEventListener('DOMContentLoaded', function () {
            // Hacer visibles todas las tarjetas inmediatamente
            const statBoxes = document.querySelectorAll('.stat-box');
            statBoxes.forEach(box => {
                box.style.opacity = '1';
                box.style.visibility = 'visible';
                box.style.transform = 'none';
            });

            // Luego inicializar animaciones normales
            setTimeout(() => {
                document.querySelectorAll('.animate-up, .animate-fade, .animate-sequence').forEach(el => {
                    el.classList.add('visible');
                });
            }, 200);
        });
    </script>
</body>

</html>