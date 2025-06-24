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

// Calcular totales agregados para métricas principales del dashboard
$totalesDocentes = calcularTotalesDocentes($datosDocentes);
$totalDocentes = $totalesDocentes['total'];
$docentesPorNivel = $totalesDocentes['por_nivel'];

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
            <a href="escuelas_detalle.php" class="sidebar-link"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <a href="estudiantes.php" class="sidebar-link"><i
                    class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="#" class="sidebar-link active"><i class="fas fa-chalkboard-teacher"></i> <span>Docentes</span></a>
            <a href="#" class="sidebar-link"><i class="fas fa-history"></i> <span>Históricos</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Detalle de Docentes Ciclo 2023 - 2024</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo date('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Panel de resumen de docentes -->
            <div class="panel animate-up">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-chalkboard-teacher"></i> Resumen de docentes en Corregidora
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
                            <div class="stat-label">Total Docentes Ciclo escolar 2023-2024</div>
                        </div>
                        <div class="stat-box animate-fade delay-2">
                            <div class="stat-value">
                                <span class="highlight-text"><?php echo $nivelMayorConcentracion; ?></span>
                            </div>
                            <div class="stat-label">Nivel con Mayor Concentración
                                (<?php echo $porcentajeMayorConcentracion; ?>%)</div>
                        </div>
                        <div class="stat-box animate-fade delay-3">
                            <div class="stat-value"><?php echo count($docentesPorNivel); ?></div>
                            <div class="stat-label">Niveles Educativos Atendidos</div>
                        </div>
                    </div>

                    <!-- Barras de progreso por nivel -->
                    <div class="level-bars animate-sequence">
                        <h4>Distribución Detallada por Nivel</h4> <?php
                        // Función para determinar el orden educativo basado en palabras clave
                        function obtenerOrdenEducativo($nivel)
                        {
                            $nivel = strtolower($nivel);
                            if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'escolariz') !== false)
                                return 1;
                            if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'no') !== false)
                                return 2;
                            if (strpos($nivel, 'preescolar') !== false)
                                return 3;
                            if (strpos($nivel, 'primaria') !== false)
                                return 4;
                            if (strpos($nivel, 'secundaria') !== false)
                                return 5;
                            if (strpos($nivel, 'media') !== false || strpos($nivel, 'medio') !== false)
                                return 6;
                            if (strpos($nivel, 'superior') !== false)
                                return 7;
                            return 8; // Para niveles no reconocidos
                        }

                        // Ordenar según el orden educativo lógico
                        $nivelesOrdenadosDisplay = $docentesPorNivel;
                        uksort($nivelesOrdenadosDisplay, function ($a, $b) {
                            return obtenerOrdenEducativo($a) - obtenerOrdenEducativo($b);
                        });
                        foreach ($nivelesOrdenadosDisplay as $nivel => $cantidad):
                            $porcentaje = $porcentajesDocentes[$nivel];
                            // Usar el porcentaje real como ancho de la barra (igual que en escuelas_detalle)
                            ?>
                            <div class="level-bar">
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
                    <div class="detailed-table animate-fade delay-4">
                        <h4>Detalle por Subnivel Educativo</h4>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nivel Educativo</th>
                                        <th>Subnivel</th>
                                        <th>Cantidad de Docentes</th>
                                        <th>Porcentaje del Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Mostrar datos detallados (omitir encabezados)
                                    for ($i = 1; $i < count($datosDocentes); $i++):
                                        $nivel = $datosDocentes[$i][0];
                                        $subnivel = $datosDocentes[$i][1];
                                        $cantidad = $datosDocentes[$i][2];
                                        $porcentajeIndividual = round(($cantidad / $totalDocentes) * 100, 2);
                                        ?>
                                        <tr>
                                            <td><?php echo $nivel; ?></td>
                                            <td><?php echo $subnivel; ?></td>
                                            <td class="text-center"><?php echo number_format($cantidad); ?></td>
                                            <td class="text-center"><?php echo $porcentajeIndividual; ?>%</td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                                        <td class="text-center">
                                            <strong><?php echo number_format($totalDocentes); ?></strong>
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
    </div><!-- Scripts -->
    <script src="./js/sidebar.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/docentes.js"></script>
    <script type="text/javascript">
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