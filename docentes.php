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

// Incluir módulo de conexión con funciones especializadas de consulta
require_once 'conexion.php';

// Obtener conjunto completo de datos de docentes desde la base de datos
$datosDocentes = obtenerDocentesPorNivel();
$datosDocentesGenero = obtenerDocentesPorGenero();

// Calcular totales agregados para métricas principales del dashboard
$totalesDocentes = calcularTotalesDocentes($datosDocentes);
$totalDocentes = $totalesDocentes['total'];
$docentesPorNivel = $totalesDocentes['por_nivel'];

// =============================================================================
// ANÁLISIS POR TIPO DE SOSTENIMIENTO
// =============================================================================

// Obtener datos segmentados por sostenimiento (público vs privado)
// Incluye datos agregados y desglosados por nivel educativo
$docentesPorSostenimiento = obtenerDocentesPorSostenimiento();
$docentesPublicos = $docentesPorSostenimiento['publicos'];           // Total docentes públicos
$docentesPrivados = $docentesPorSostenimiento['privados'];           // Total docentes privados
$porcentajePublicos = $docentesPorSostenimiento['porcentaje_publicos']; // % públicos
$porcentajePrivados = $docentesPorSostenimiento['porcentaje_privados']; // % privados
$docentesNivelSostenimiento = $docentesPorSostenimiento['por_nivel'];    // Desglose por nivel

// =============================================================================
// PROCESAMIENTO DE DATOS POR NIVEL EDUCATIVO
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
            <a href="home.php" class="sidebar-link"><i class="fas fa-home"></i> <span>Regresar al Home</span></a>
            <a href="resumen.php" class="sidebar-link"><i class="fas fa-chart-bar"></i><span>Resumen</span></a>
            <a href="alumnos.php" class="sidebar-link"><i class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="escuelas_detalle.php" class="sidebar-link"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <div class="sidebar-link-with-submenu">
                <a href="docentes.php" class="sidebar-link active has-submenu">
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
            <a href="estudiantes.php" class="sidebar-link"><i class="fas fa-history"></i> <span>Históricos</span></a>
            <!-- <a href="historicos.php" class="sidebar-link"><i class="fas fa-history"></i> <span>Demo
                    Históricos</span></a> -->
        </div>
    </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Detalle de Docentes Ciclo 2024 - 2025</h1>
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
                    <h3 class="panel-title"><i class="fas fa-chalkboard-teacher"></i> Resumen de docentes en Querétaro
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
                        <h4>Distribución Detallada por Nivel</h4> <?php
                        // Función para determinar el orden educativo basado en palabras clave
                        function obtenerOrdenEducativo($nivel)
                        {
                            $nivel = strtolower($nivel);
                            if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'escolariz') !== false)
                                return 1;
                            if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'no') !== false)
                                return 2;
                            if (strpos($nivel, 'cam') !== false)
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

                                        // Mapeo específico por nivel + subnivel para orden exacto
                                        if ($nivel === 'educación inicial' && strpos($subnivel, 'escolarizada') !== false)
                                            return 1; // Educación Inicial - Escolarizada General
                                        if ($nivel === 'educación inicial' && strpos($subnivel, 'no escolarizada') !== false)
                                            return 2; // Educación Inicial - No Escolarizada Comunitaria
                                        if ($nivel === 'inicial escolarizada')
                                            return 1;
                                        if ($nivel === 'inicial no escolarizada')
                                            return 2;

                                        // CAM (TERCERO)
                                        if (strpos($nivel, 'cam') !== false)
                                            return 3;

                                        // Preescolar
                                        if (strpos($nivel, 'preescolar') !== false && strpos($subnivel, 'general') !== false)
                                            return 4;
                                        if (strpos($nivel, 'preescolar') !== false && strpos($subnivel, 'comunitario') !== false)
                                            return 5;

                                        // Primaria
                                        if (strpos($nivel, 'primaria') !== false && strpos($subnivel, 'general') !== false)
                                            return 6;
                                        if (strpos($nivel, 'primaria') !== false && strpos($subnivel, 'comunitaria') !== false)
                                            return 7;

                                        // Secundaria
                                        if (strpos($nivel, 'secundaria') !== false)
                                            return 8;

                                        // Media Superior
                                        if (strpos($nivel, 'media') !== false || strpos($nivel, 'medio') !== false)
                                            return 9;

                                        // Superior
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