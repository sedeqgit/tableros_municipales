<?php
/**
 * =============================================================================
 * PÁGINA DE ANÁLISIS HISTÓRICOS - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Esta página presenta un análisis temporal completo del sistema educativo 
 * de Corregidora, incluyendo tendencias históricas, comparativas de ciclos
 * escolares y proyecciones basadas en datos históricos.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Análisis de tendencias temporales de matrícula y escuelas
 * - Comparativas históricas entre ciclos escolares
 * - Visualización de evolución de docentes por nivel educativo
 * - Métricas de crecimiento y indicadores de desempeño histórico
 * - Exportación de datos históricos en múltiples formatos
 * 
 * COMPONENTES DE VISUALIZACIÓN:
 * - Gráficos de líneas temporales
 * - Gráficos de barras comparativas por ciclos
 * - Tablas de datos históricos interactivas
 * - Indicadores de tendencias y variaciones porcentuales
 * - Paneles de métricas históricas clave
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Historicos
 * @version 2.0
 */

// =============================================================================
// CONFIGURACIÓN E INICIALIZACIÓN DEL SISTEMA
// =============================================================================

// Incluir el helper de sesiones para manejo de autenticación
require_once 'session_helper.php';

// Inicializar sesión y configurar usuario de demostración si es necesario
iniciarSesionDemo();

// Incluir módulo de conexión a base de datos con funciones de consulta
require_once 'conexion.php';

// =============================================================================
// DATOS HISTÓRICOS SIMULADOS BASADOS EN TENDENCIAS REALES
// =============================================================================

// Datos históricos de matrícula total por ciclo escolar
$matriculaHistorica = array(
    '2015-2016' => array(
        'total_estudiantes' => 89420,
        'total_escuelas' => 445,
        'docentes' => 2650,
        'escuelas_publicas' => 315,
        'escuelas_privadas' => 130
    ),
    '2016-2017' => array(
        'total_estudiantes' => 91580,
        'total_escuelas' => 452,
        'docentes' => 2720,
        'escuelas_publicas' => 318,
        'escuelas_privadas' => 134
    ),
    '2017-2018' => array(
        'total_estudiantes' => 93240,
        'total_escuelas' => 461,
        'docentes' => 2785,
        'escuelas_publicas' => 322,
        'escuelas_privadas' => 139
    ),
    '2018-2019' => array(
        'total_estudiantes' => 95180,
        'total_escuelas' => 468,
        'docentes' => 2840,
        'escuelas_publicas' => 326,
        'escuelas_privadas' => 142
    ),
    '2019-2020' => array(
        'total_estudiantes' => 97320,
        'total_escuelas' => 475,
        'docentes' => 2895,
        'escuelas_publicas' => 330,
        'escuelas_privadas' => 145
    ),
    '2020-2021' => array(
        'total_estudiantes' => 94680, // Reducción por pandemia
        'total_escuelas' => 478,
        'docentes' => 2920,
        'escuelas_publicas' => 332,
        'escuelas_privadas' => 146
    ),
    '2021-2022' => array(
        'total_estudiantes' => 96850,
        'total_escuelas' => 485,
        'docentes' => 2975,
        'escuelas_publicas' => 336,
        'escuelas_privadas' => 149
    ),
    '2022-2023' => array(
        'total_estudiantes' => 98420,
        'total_escuelas' => 491,
        'docentes' => 3020,
        'escuelas_publicas' => 340,
        'escuelas_privadas' => 151
    ),
    '2023-2024' => array(
        'total_estudiantes' => 100180,
        'total_escuelas' => 496,
        'docentes' => 3065,
        'escuelas_publicas' => 344,
        'escuelas_privadas' => 152
    )
);

// Evolución histórica por nivel educativo
$evolucionPorNivel = array(
    'Preescolar' => array(
        '2018-2019' => 11500,
        '2019-2020' => 11850,
        '2020-2021' => 11200, // Impacto pandemia
        '2021-2022' => 11650,
        '2022-2023' => 11950,
        '2023-2024' => 12200
    ),
    'Primaria' => array(
        '2018-2019' => 44200,
        '2019-2020' => 44800,
        '2020-2021' => 43500,
        '2021-2022' => 44100,
        '2022-2023' => 44650,
        '2023-2024' => 45000
    ),
    'Secundaria' => array(
        '2018-2019' => 27200,
        '2019-2020' => 27650,
        '2020-2021' => 26800,
        '2021-2022' => 27300,
        '2022-2023' => 27750,
        '2023-2024' => 28000
    ),
    'Media Superior' => array(
        '2018-2019' => 18100,
        '2019-2020' => 18450,
        '2020-2021' => 17900,
        '2021-2022' => 18250,
        '2022-2023' => 18650,
        '2023-2024' => 19000
    ),
    'Superior' => array(
        '2018-2019' => 13800,
        '2019-2020' => 14250,
        '2020-2021' => 14100,
        '2021-2022' => 14500,
        '2022-2023' => 14800,
        '2023-2024' => 15000
    )
);

// Indicadores clave de desempeño histórico
$indicadoresHistoricos = array(
    'crecimiento_promedio_anual' => 2.8,
    'variacion_2020_2021' => -2.7, // Impacto pandemia
    'recuperacion_2021_2024' => 5.8,
    'tasa_cobertura_preescolar' => 94.5,
    'tasa_cobertura_primaria' => 98.2,
    'tasa_cobertura_secundaria' => 89.7,
    'ratio_estudiantes_docente' => 32.7,
    'crecimiento_escuelas_privadas' => 16.9
);

// Calcular métricas comparativas
$cicloActual = '2023-2024';
$cicloAnterior = '2022-2023';
$estudiantesActuales = $matriculaHistorica[$cicloActual]['total_estudiantes'];
$estudiantesAnteriores = $matriculaHistorica[$cicloAnterior]['total_estudiantes'];
$crecimientoAnual = (($estudiantesActuales - $estudiantesAnteriores) / $estudiantesAnteriores) * 100;

// Obtener datos actuales de base de datos para comparación
$datosEducativosActuales = obtenerDatosEducativos();
$totalesActuales = calcularTotales($datosEducativosActuales);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis Históricos | SEDEQ</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <link rel="stylesheet" href="./css/estudiantes.css">
    <link rel="stylesheet" href="./css/historicos.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bibliotecas para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<body>
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
            <a href="estudiantes.php" class="sidebar-link"><i class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="docentes.php" class="sidebar-link"><i class="fas fa-chalkboard-teacher"></i> <span>Docentes</span></a>
            <a href="#" class="sidebar-link active"><i class="fas fa-history"></i> <span>Históricos</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Análisis Históricos del Sistema Educativo Corregidora</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Panel de resumen histórico -->
            <div class="historical-panel animate-fade delay-1">
                <div class="historical-header">
                    <h3 class="historical-title">
                        <i class="fas fa-chart-line"></i> Indicadores Históricos Clave (2015-2024)
                    </h3>
                    <div class="export-buttons">
                        <button id="export-historical-btn" class="export-button">
                            <i class="fas fa-download"></i> Exportar Históricos
                        </button>
                    </div>
                </div>
                <div class="historical-body">
                    <div class="historical-metrics">
                        <div class="metric-card growth">
                            <div class="metric-icon">
                                <i class="fas fa-trending-up"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?php echo number_format($crecimientoAnual, 1); ?>%</div>
                                <div class="metric-label">Crecimiento Anual</div>
                                <div class="metric-period">2022-23 vs 2023-24</div>
                            </div>
                        </div>
                        <div class="metric-card students">
                            <div class="metric-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?php echo number_format($estudiantesActuales); ?></div>
                                <div class="metric-label">Total Estudiantes</div>
                                <div class="metric-period">Ciclo 2023-2024</div>
                            </div>
                        </div>
                        <div class="metric-card schools">
                            <div class="metric-icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?php echo $matriculaHistorica[$cicloActual]['total_escuelas']; ?></div>
                                <div class="metric-label">Total Escuelas</div>
                                <div class="metric-period">Histórico máximo</div>
                            </div>
                        </div>
                        <div class="metric-card teachers">
                            <div class="metric-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?php echo number_format($matriculaHistorica[$cicloActual]['docentes']); ?></div>
                                <div class="metric-label">Total Docentes</div>
                                <div class="metric-period">Proyección 2024</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de evolución temporal -->
            <div class="historical-panel animate-fade delay-2">
                <div class="historical-header">
                    <h3 class="historical-title">
                        <i class="fas fa-chart-area"></i> Evolución Temporal de la Matrícula
                    </h3>
                    <div class="chart-controls">
                        <div class="time-selector">
                            <button class="time-btn active" data-period="all">Todo el período</button>
                            <button class="time-btn" data-period="recent">Últimos 5 años</button>
                            <button class="time-btn" data-period="pandemic">Impacto COVID</button>
                        </div>
                    </div>
                </div>
                <div class="historical-body">
                    <div id="chart-evolution-container" class="chart-container">
                        <div id="chart-evolution" style="width:100%; height:450px;"></div>
                    </div>
                </div>
            </div>

            <!-- Panel de análisis por nivel educativo -->
            <div class="historical-panel animate-fade delay-3">
                <div class="historical-header">
                    <h3 class="historical-title">
                        <i class="fas fa-layer-group"></i> Evolución por Nivel Educativo
                    </h3>
                    <div class="level-controls">
                        <div class="level-selector">
                            <button class="level-btn active" data-level="all">Todos los niveles</button>
                            <button class="level-btn" data-level="Preescolar">Preescolar</button>
                            <button class="level-btn" data-level="Primaria">Primaria</button>
                            <button class="level-btn" data-level="Secundaria">Secundaria</button>
                            <button class="level-btn" data-level="Media Superior">Media Superior</button>
                            <button class="level-btn" data-level="Superior">Superior</button>
                        </div>
                    </div>
                </div>
                <div class="historical-body">
                    <div class="dual-chart-container">
                        <div class="chart-section">
                            <h4>Tendencias Históricas</h4>
                            <div id="chart-levels-container" class="chart-container">
                                <div id="chart-levels" style="width:100%; height:400px;"></div>
                            </div>
                        </div>
                        <div class="trends-section">
                            <h4>Análisis de Tendencias</h4>
                            <div class="trends-list">
                                <div class="trend-item positive">
                                    <div class="trend-icon">
                                        <i class="fas fa-arrow-up"></i>
                                    </div>
                                    <div class="trend-content">
                                        <h5>Educación Superior</h5>
                                        <p>Crecimiento sostenido del <strong>8.7%</strong> en los últimos 6 años</p>
                                        <span class="trend-value">+1,200 estudiantes</span>
                                    </div>
                                </div>
                                <div class="trend-item stable">
                                    <div class="trend-icon">
                                        <i class="fas fa-minus"></i>
                                    </div>
                                    <div class="trend-content">
                                        <h5>Educación Primaria</h5>
                                        <p>Estabilidad en matrícula con <strong>variación mínima</strong></p>
                                        <span class="trend-value">+1.8% promedio</span>
                                    </div>
                                </div>
                                <div class="trend-item recovery">
                                    <div class="trend-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="trend-content">
                                        <h5>Recuperación Post-COVID</h5>
                                        <p>Todos los niveles muestran <strong>recuperación completa</strong></p>
                                        <span class="trend-value">+5.8% desde 2021</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de comparativas por sostenimiento -->
            <div class="historical-panel animate-fade delay-4">
                <div class="historical-header">
                    <h3 class="historical-title">
                        <i class="fas fa-balance-scale"></i> Evolución Público vs Privado
                    </h3>
                    <div class="comparison-controls">
                        <div class="view-selector">
                            <label class="radio-option">
                                <input type="radio" name="comparison-view" value="absolute" checked>
                                <span>Valores Absolutos</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="comparison-view" value="percentage">
                                <span>Porcentajes</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="historical-body">
                    <div id="chart-comparison-container" class="chart-container">
                        <div id="chart-comparison" style="width:100%; height:400px;"></div>
                    </div>
                    <div class="comparison-insights">
                        <div class="insight-card">
                            <div class="insight-header">
                                <i class="fas fa-university"></i>
                                <h4>Sector Público</h4>
                            </div>
                            <div class="insight-content">
                                <p>Mantiene el <strong>69.4%</strong> de la matrícula total</p>
                                <p>Crecimiento estable del <strong>2.1%</strong> anual</p>
                            </div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-header">
                                <i class="fas fa-building"></i>
                                <h4>Sector Privado</h4>
                            </div>
                            <div class="insight-content">
                                <p>Representa el <strong>30.6%</strong> de la matrícula</p>
                                <p>Crecimiento acelerado del <strong>4.2%</strong> anual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de tabla histórica detallada -->
            <div class="historical-panel animate-fade delay-5">
                <div class="historical-header">
                    <h3 class="historical-title">
                        <i class="fas fa-table"></i> Datos Históricos Detallados
                    </h3>
                    <div class="table-controls">
                        <div class="period-selector">
                            <select id="period-filter">
                                <option value="all">Todos los períodos</option>
                                <option value="2020-2024">2020-2024</option>
                                <option value="2015-2019">2015-2019</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="historical-body">
                    <div class="table-container">
                        <table id="historical-data-table" class="data-table">
                            <thead>
                                <tr>
                                    <th>Ciclo Escolar</th>
                                    <th>Total Estudiantes</th>
                                    <th>Total Escuelas</th>
                                    <th>Total Docentes</th>
                                    <th>Escuelas Públicas</th>
                                    <th>Escuelas Privadas</th>
                                    <th>Variación Anual</th>
                                    <th>Ratio Estudiante/Docente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($matriculaHistorica as $ciclo => $datos): 
                                    // Calcular variación anual
                                    $ciclosOrdenados = array_keys($matriculaHistorica);
                                    $indiceCiclo = array_search($ciclo, $ciclosOrdenados);
                                    $variacion = 0;
                                    
                                    if ($indiceCiclo > 0) {
                                        $cicloAnterior = $ciclosOrdenados[$indiceCiclo - 1];
                                        $estudiantesActuales = $datos['total_estudiantes'];
                                        $estudiantesAnteriores = $matriculaHistorica[$cicloAnterior]['total_estudiantes'];
                                        $variacion = (($estudiantesActuales - $estudiantesAnteriores) / $estudiantesAnteriores) * 100;
                                    }
                                    
                                    $ratio = round($datos['total_estudiantes'] / $datos['docentes'], 1);
                                    $variacionClass = $variacion > 0 ? 'positive' : ($variacion < 0 ? 'negative' : 'neutral');
                                ?>
                                <tr>
                                    <td class="period-cell"><?php echo $ciclo; ?></td>
                                    <td class="number-cell"><?php echo number_format($datos['total_estudiantes']); ?></td>
                                    <td class="number-cell"><?php echo $datos['total_escuelas']; ?></td>
                                    <td class="number-cell"><?php echo number_format($datos['docentes']); ?></td>
                                    <td class="number-cell public"><?php echo $datos['escuelas_publicas']; ?></td>
                                    <td class="number-cell private"><?php echo $datos['escuelas_privadas']; ?></td>
                                    <td class="variation-cell <?php echo $variacionClass; ?>">
                                        <?php if ($variacion != 0): ?>
                                            <?php echo ($variacion > 0 ? '+' : '') . number_format($variacion, 1) . '%'; ?>
                                        <?php else: ?>
                                            --
                                        <?php endif; ?>
                                    </td>
                                    <td class="ratio-cell"><?php echo $ratio; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de proyecciones -->
            <div class="historical-panel animate-fade delay-6">
                <div class="historical-header">
                    <h3 class="historical-title">
                        <i class="fas fa-crystal-ball"></i> Proyecciones y Análisis Predictivo
                    </h3>
                </div>
                <div class="historical-body">
                    <div class="projections-container">
                        <div class="projection-card">
                            <div class="projection-header">
                                <i class="fas fa-calendar-plus"></i>
                                <h4>Proyección 2024-2025</h4>
                            </div>
                            <div class="projection-content">
                                <div class="projection-metric">
                                    <span class="projection-label">Estudiantes esperados:</span>
                                    <span class="projection-value">102,380</span>
                                </div>
                                <div class="projection-metric">
                                    <span class="projection-label">Nuevas escuelas necesarias:</span>
                                    <span class="projection-value">8-12</span>
                                </div>
                                <div class="projection-metric">
                                    <span class="projection-label">Docentes adicionales:</span>
                                    <span class="projection-value">75-90</span>
                                </div>
                            </div>
                        </div>
                        <div class="recommendation-card">
                            <div class="recommendation-header">
                                <i class="fas fa-lightbulb"></i>
                                <h4>Recomendaciones Estratégicas</h4>
                            </div>
                            <div class="recommendation-content">
                                <ul class="recommendation-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        Incrementar la inversión en educación superior por alta demanda
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        Fortalecer la infraestructura de escuelas públicas
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        Implementar programas de retención estudiantil
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        Desarrollar alianzas público-privadas en educación
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos reservados</p>
        </footer>
    </div>

    <!-- Scripts de datos para JavaScript -->
    <script>
        <?php
        // Convertir datos PHP a JavaScript
        echo "const matriculaHistorica = " . json_encode($matriculaHistorica) . ";\n";
        echo "const evolucionPorNivel = " . json_encode($evolucionPorNivel) . ";\n";
        echo "const indicadoresHistoricos = " . json_encode($indicadoresHistoricos) . ";\n";
        ?>
    </script>

    <!-- Scripts del sistema -->
    <script src="./js/script.js"></script>
    <script src="./js/historicos.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
</body>

</html>
