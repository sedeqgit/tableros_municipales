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
require_once 'conexion.php';

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
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="docentes-page fixed-header-page">
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


            <?php include 'includes/header_end.php'; ?>

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
                            <h3 class="panel-title"><i class="fas fa-chalkboard-teacher"></i> Resumen General de
                                Docentes
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
                                            <span
                                                class="progress-label"><?php echo formatPercent($porcentajePublicos); ?>%
                                                Públicos</span>
                                        </div>
                                        <div class="progress-fill private"
                                            style="width: <?php echo formatPercent($porcentajePrivados); ?>%">
                                            <span
                                                class="progress-label"><?php echo formatPercent($porcentajePrivados); ?>%
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
                                            data-publicos="<?php echo $publicos; ?>"
                                            data-privados="<?php echo $privados; ?>" data-total="<?php echo $cantidad; ?>">
                                            <span
                                                class="level-name"><?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <div class="level-track">
                                                <div class="level-fill"
                                                    style="width: <?php echo formatPercent($porcentaje); ?>%">
                                                    <span
                                                        class="escuelas-count"><?php echo number_format($cantidad); ?></span>
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
                                    <strong>Nota:</strong> El subnivel "General" contabiliza tanto docentes públicos
                                    como
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
                                                    <td class="text-center">
                                                        <?php echo formatPercent($porcentajeDelTotal); ?>%
                                                    </td>
                                                    <td class="text-center docentes-hombres">
                                                        <?php echo number_format($hombres); ?>
                                                    </td>
                                                    <td class="text-center porcentaje-hombres">
                                                        <?php echo formatPercent($porcentajeHombres); ?>%
                                                    </td>
                                                    <td class="text-center docentes-mujeres">
                                                        <?php echo number_format($mujeres); ?>
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
                                                <td class="text-center">
                                                    <strong><?php echo formatPercent(100); ?>%</strong>
                                                </td>
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
                                    <i class="fas fa-hands-helping"></i> USAER - Unidad de Servicios de Apoyo a la Educación
                                    Regular
                                </h3>
                            </div>
                            <div class="panel-body">
                                <p class="note-info" style="margin-bottom: 20px;">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Nota:</strong> Datos informativos de las Unidades de Servicios de Apoyo a la
                                    Educación
                                    Regular.
                                    Estos datos no se suman en los totales municipales ya que atienden a alumnos
                                    contabilizados en
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
                                                        <?php echo number_format($totalDocPubUSAER, 0, '.', ','); ?>
                                                        Personal
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
                                                        <?php echo number_format($totalDocPrivUSAER, 0, '.', ','); ?>
                                                        personal
                                                    </div>
                                                    <div class="porcentaje">
                                                        <?php echo formatPercent($totalDocUSAER > 0 ? ($totalDocPrivUSAER / $totalDocUSAER) * 100 : 0); ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Desglose por Sexo -->
                                        <div class="usaer-sexo-section">
                                            <h4
                                                style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
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
                <?php include './includes/footer.php'; ?>
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

            <!-- Botón volver al inicio -->
            <?php include 'includes/back_to_top.php'; ?>
</body>

</html>