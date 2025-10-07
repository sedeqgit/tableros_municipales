<?php
/**
 * =============================================================================
 * PÁGINA DE DETALLE DE DOCENTES - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Esta página presenta un análisis detallado del personal docente en el
 * municipio de Corregidora, Querétaro, incluyendo distribución por niveles
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

// Obtener municipio desde parámetro GET, por defecto CORREGIDORA
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'CORREGIDORA';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'CORREGIDORA'; // Fallback a Corregidora si el municipio no es válido
}

// Obtener datos completos del municipio usando la función correcta
$datosCompletos = obtenerResumenMunicipioCompleto($municipioSeleccionado);
$datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

// =============================================================================
// PROCESAMIENTO DE DATOS POR NIVEL EDUCATIVO
// =============================================================================

// Procesar datos para obtener totales por nivel y subnivel
$datosDocentesGenero = array();
$datosDocentesGenero[] = array('Nivel Educativo', 'Subnivel', 'Total Docentes', 'Hombres', 'Mujeres', '% Hombres', '% Mujeres');

$docentesPorNivel = array(); // Total por nivel principal
$totalDocentes = 0;

// Definir estructura de niveles con sus subniveles
$estructuraNiveles = [
    'Inicial Escolarizada' => [
        ['clave' => 'inicial_esc', 'nombre' => 'General']
    ],
    'Inicial No Escolarizada' => [
        ['clave' => 'inicial_no_esc', 'nombre' => 'General']
    ],
    'Especial' => [
        ['clave' => 'especial', 'nombre' => 'CAM']
    ],
    'Preescolar' => [
        ['clave' => 'preescolar', 'nombre' => 'General']
    ],
    'Primaria' => [
        ['clave' => 'primaria', 'nombre' => 'General']
    ],
    'Secundaria' => [
        ['clave' => 'secundaria', 'nombre' => 'General']
    ],
    'Media Superior' => [
        ['clave' => 'media_sup', 'nombre' => 'Bachillerato']
    ],
    'Superior' => [
        ['clave' => 'superior', 'nombre' => 'Licenciatura']
    ]
];

// Procesar datos de docentes desde la estructura completa
if ($datosCompletos && is_array($datosCompletos)) {
    foreach ($estructuraNiveles as $nivelPrincipal => $subniveles) {
        foreach ($subniveles as $subnivel) {
            $clave = $subnivel['clave'];
            $nombreSubnivel = $subnivel['nombre'];

            if (isset($datosCompletos[$clave]) && is_array($datosCompletos[$clave])) {
                $dato = $datosCompletos[$clave];
                $docentes = isset($dato['tot_doc']) ? $dato['tot_doc'] : 0;
                $docentesH = isset($dato['doc_h']) ? $dato['doc_h'] : 0;
                $docentesM = isset($dato['doc_m']) ? $dato['doc_m'] : 0;

                // Calcular porcentajes de género
                $porcH = $docentes > 0 ? round(($docentesH / $docentes) * 100, 1) : 0;
                $porcM = $docentes > 0 ? round(($docentesM / $docentes) * 100, 1) : 0;

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
    }
}

// Si no hay datos, usar valores de fallback
if ($totalDocentes == 0) {
    $totalDocentes = 2808;
    $docentesPorNivel = array(
        'Inicial Escolarizada' => 36,
        'Inicial No Escolarizada' => 25,
        'Especial' => 22,
        'Preescolar' => 352,
        'Primaria' => 750,
        'Secundaria' => 571,
        'Media Superior' => 607,
        'Superior' => 467
    );
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
$porcentajePublicos = $totalGeneral > 0 ? round(($docentesPublicos / $totalGeneral) * 100, 1) : 0;
$porcentajePrivados = $totalGeneral > 0 ? round(($docentesPrivados / $totalGeneral) * 100, 1) : 0;

// =============================================================================
// CÁLCULOS COMPLEMENTARIOS
// =============================================================================

// Calcular distribución porcentual para análisis comparativo
$porcentajesDocentes = array();
foreach ($docentesPorNivel as $nivel => $cantidad) {
    $porcentajesDocentes[$nivel] = round(($cantidad / $totalDocentes) * 100, 1);
}

// Preparar datos para gráficos (formato Google Charts)
$datosGraficoNivel = array();
$datosGraficoNivel[] = array('Nivel', 'Docentes');
foreach ($docentesPorNivel as $nivel => $cantidad) {
    $datosGraficoNivel[] = array($nivel, $cantidad);
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
    <title>Detalle de Docentes Corregidora | SEDEQ</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <link rel="stylesheet" href="./css/escuelas_detalle.css">
    <link rel="stylesheet" href="./css/docentes.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="docentes-page">
    <!-- Overlay para cerrar el menú en móviles -->
    <div class="sidebar-overlay"></div>

    <div class="sidebar">
        <div class="logo-container">
            <img src="./img/layout_set_logo.png" alt="Logo SEDEQ" class="logo">
        </div>
        <div class="sidebar-links">
            <?php
            // Construir parámetro de municipio para mantener persistencia en navegación
            $paramMunicipio = '?municipio=' . urlencode($municipioSeleccionado);
            ?>
            <a href="home.php" class="sidebar-link"><i class="fas fa-home"></i> <span>Regresar al Home</span></a>
            <a href="resumen.php<?php echo $paramMunicipio; ?>" class="sidebar-link"><i
                    class="fas fa-chart-bar"></i><span>Resumen</span></a>
            <a href="alumnos.php<?php echo $paramMunicipio; ?>" class="sidebar-link"><i
                    class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="escuelas_detalle.php<?php echo $paramMunicipio; ?>" class="sidebar-link"><i
                    class="fas fa-school"></i> <span>Escuelas</span></a>
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
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Detalle de Docentes - <?php echo ucwords(strtolower($municipioSeleccionado)); ?> - Ciclo 2024-2025
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
                    <h3 class="panel-title"><i class="fas fa-chalkboard-teacher"></i> Resumen de docentes en
                        <?php echo ucwords(strtolower($municipioSeleccionado)); ?>
                    </h3>
                </div>
                <div class="panel-body"> <?php
                // Debug - mostrar valores para verificar
                echo "<!-- DEBUG: Total Docentes: $totalDocentes -->";
                echo "<!-- DEBUG: Nivel Mayor: $nivelMayorConcentracion -->";
                echo "<!-- DEBUG: Porcentaje Mayor: $porcentajeMayorConcentracion -->";
                echo "<!-- DEBUG: Count Niveles: " . count($docentesPorNivel) . " -->";

                // Verificar que los valores no estén vacíos
                if (empty($totalDocentes) || $totalDocentes <= 0) {
                    echo "<!-- ERROR: Total Docentes es 0 o vacío -->";
                    $totalDocentes = 2808; // Valor de fallback
                }
                if (empty($nivelMayorConcentracion)) {
                    echo "<!-- ERROR: Nivel Mayor Concentración vacío -->";
                    $nivelMayorConcentracion = "Primaria";
                    $porcentajeMayorConcentracion = 26.7;
                }
                ?>
                    <div class="stats-row">
                        <div class="stat-box animate-fade delay-1">
                            <div class="stat-value"><?php echo number_format($totalDocentes); ?></div>
                            <div class="stat-label">Total Docentes</div>
                        </div>
                        <div class="stat-box animate-fade delay-2">
                            <div class="stat-value">
                                <span class="public-schools"><?php echo $docentesPublicos; ?></span>
                                <span class="separator"> / </span>
                                <span class="private-schools"><?php echo $docentesPrivados; ?></span>
                            </div>
                            <div class="stat-label">Docentes Públicos / Privados</div>
                        </div>
                        <div class="stat-box animate-fade delay-3">
                            <div class="stat-value">
                                <span class="highlight-text"><?php echo $nivelMayorConcentracion; ?></span>
                            </div>
                            <div class="stat-label">Nivel con Mayor Concentración
                                (<?php echo $porcentajeMayorConcentracion; ?>%)</div>
                        </div>
                    </div>

                    <!-- Gráfico de distribución pública vs privada -->
                    <div class="sostenimiento-chart animate-fade delay-3">
                        <h4>Distribución por Sostenimiento</h4>
                        <div class="sostenimiento-filters">
                            <button class="filter-btn active" data-filter="total">Total</button>
                            <button class="filter-btn" data-filter="publico">Público</button>
                            <button class="filter-btn" data-filter="privado">Privado</button>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill public" style="width: <?php echo $porcentajePublicos; ?>%">
                                    <span class="progress-label"><?php echo $porcentajePublicos; ?>% Públicos</span>
                                </div>
                                <div class="progress-fill private" style="width: <?php echo $porcentajePrivados; ?>%">
                                    <span class="progress-label"><?php echo $porcentajePrivados; ?>% Privados</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Barras de progreso por nivel -->
                    <div id="distribucion-nivel" class="level-bars animate-sequence">
                        <div class="nivel-header">
                            <h4>Distribución por Nivel</h4>
                            <div class="view-toggle-buttons">
                                <button class="view-toggle-btn active" data-view="barras">
                                    <i class="fas fa-chart-bar"></i> Vista Barras
                                </button>
                                <button class="view-toggle-btn" data-view="grafico">
                                    <i class="fas fa-chart-pie"></i> Vista Gráfico
                                </button>
                            </div>
                        </div>

                        <!-- Vista de Barras (Por defecto) -->
                        <div id="vista-barras" class="visualization-container">
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
                            <div class="level-bar" data-nivel="<?php echo htmlspecialchars($nivel); ?>"
                                data-publicos="<?php echo $publicos; ?>" data-privados="<?php echo $privados; ?>"
                                data-total="<?php echo $cantidad; ?>">
                                <span class="level-name"><?php echo $nivel; ?></span>
                                <div class="level-track">
                                    <div class="level-fill" style="width: <?php echo $porcentaje; ?>%">
                                        <span class="escuelas-count"><?php echo number_format($cantidad); ?></span>
                                    </div>
                                </div>
                                <span class="level-percent"><?php echo $porcentaje; ?>%</span>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <!-- Fin Vista Barras -->

                        <!-- Vista Gráfico (Oculto por defecto) -->
                        <div id="vista-grafico" class="visualization-container" style="display: none;">
                            <div id="pie-chart-nivel" style="width: 100%; height: 400px;"></div>
                        </div>
                        <!-- Fin Vista Gráfico -->
                    </div>

                    <!-- Tabla detallada -->
                    <div id="tabla-detallada" class="detailed-table animate-fade delay-4">
                        <h4>Detalle por Subnivel Educativo</h4>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nivel Educativo</th>
                                        <th>Subnivel</th>
                                        <th>Total Docentes</th>
                                        <th>Docentes Hombres</th>
                                        <th>Docentes Mujeres</th>
                                        <th>% Hombres</th>
                                        <th>% Mujeres</th>
                                        <th>% del Total General</th>
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

                                        // PREESCOLAR
                                        if (strpos($nivel, 'preescolar') !== false && strpos($subnivel, 'general') !== false)
                                            return 4;
                                        if (strpos($nivel, 'preescolar') !== false && strpos($subnivel, 'comunitario') !== false)
                                            return 5;

                                        // PRIMARIA
                                        if (strpos($nivel, 'primaria') !== false && strpos($subnivel, 'general') !== false)
                                            return 6;
                                        if (strpos($nivel, 'primaria') !== false && strpos($subnivel, 'comunitaria') !== false)
                                            return 7;

                                        // SECUNDARIA
                                        if (strpos($nivel, 'secundaria') !== false)
                                            return 8;

                                        // MEDIA SUPERIOR
                                        if (strpos($nivel, 'media') !== false || strpos($nivel, 'medio') !== false)
                                            return 9;

                                        // SUPERIOR
                                        if (strpos($nivel, 'superior') !== false)
                                            return 10;

                                        return 11; // Para niveles no reconocidos
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
                                            <td><?php echo $nivel; ?></td>
                                            <td><?php echo $subnivel; ?></td>
                                            <td class="text-center"><?php echo number_format($totalNivel); ?></td>
                                            <td class="text-center docentes-hombres"><?php echo number_format($hombres); ?>
                                            </td>
                                            <td class="text-center docentes-mujeres"><?php echo number_format($mujeres); ?>
                                            </td>
                                            <td class="text-center porcentaje-hombres"><?php echo $porcentajeHombres; ?>%
                                            </td>
                                            <td class="text-center porcentaje-mujeres"><?php echo $porcentajeMujeres; ?>%
                                            </td>
                                            <td class="text-center"><?php echo $porcentajeDelTotal; ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                                        <td class="text-center">
                                            <strong><?php echo number_format($totalDocentes); ?></strong>
                                        </td>
                                        <?php
                                        // Calcular totales de género
                                        $totalHombres = 0;
                                        $totalMujeres = 0;
                                        for ($i = 1; $i < count($datosDocentesGenero); $i++) {
                                            $totalHombres += $datosDocentesGenero[$i][3];
                                            $totalMujeres += $datosDocentesGenero[$i][4];
                                        }
                                        $porcentajeTotalHombres = $totalDocentes > 0 ? round(($totalHombres / $totalDocentes) * 100, 1) : 0;
                                        $porcentajeTotalMujeres = $totalDocentes > 0 ? round(($totalMujeres / $totalDocentes) * 100, 1) : 0;
                                        ?>
                                        <td class="text-center">
                                            <strong><?php echo number_format($totalHombres); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <strong><?php echo number_format($totalMujeres); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <strong><?php echo $porcentajeTotalHombres; ?>%</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong><?php echo $porcentajeTotalMujeres; ?>%</strong>
                                        </td>
                                        <td class="text-center"><strong>100.0%</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
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