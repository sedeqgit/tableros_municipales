<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener todos los datos usando las funciones centralizadas en conexion.php
$datosMatricula = obtenerDatosMatricula();
$datosMatriculaSexo = obtenerDatosMatriculaSexo();
$indicadoresRendimiento = obtenerIndicadoresRendimiento();
$indicadoresCantidad = obtenerIndicadoresCantidad();
$indicadoresPorcentaje = obtenerIndicadoresPorcentaje();

// Calcular totales y análisis
$totalMatricula2122 = 0;
$totalMatricula2324 = 0;
$totalCrecimiento = 0;

foreach ($datosMatricula as $fila) {
    $totalMatricula2122 += $fila['tot_21_22'];
    $totalMatricula2324 += $fila['tot_23_24'];
}

if ($totalMatricula2122 > 0) {
    $totalCrecimiento = (($totalMatricula2324 - $totalMatricula2122) / $totalMatricula2122) * 100;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis Histórico Educativo - SEDEQ</title> <!-- Estilos CSS -->
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/historicos.css">
    <link rel="stylesheet" href="./css/improvements.css">
    <link rel="stylesheet" href="./css/animations_global.css">
    <link rel="stylesheet" href="./css/sidebar.css">

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <a href="dashboard_restructurado.php" class="sidebar-link"><i
                    class="fas fa-chart-bar"></i><span>Resumen</span></a>
            <a href="escuelas_detalle.php" class="sidebar-link"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <a href="estudiantes.php" class="sidebar-link"><i
                    class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="#" class="sidebar-link"><i class="fas fa-chalkboard-teacher"></i> <span>Docentes</span></a>
            <a href="historicos.php" class="sidebar-link active"><i class="fas fa-history"></i>
                <span>Históricos</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title">
                <h1>Análisis Histórico Educativo - SEDEQ</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo date('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <!-- Panel de resumen estadístico -->
            <div class="panel animate-up">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-chart-bar"></i> Resumen General</h3>
                    <p>Indicadores clave del sistema educativo en Corregidora</p>
                </div>
                <div class="panel-body">
                    <div class="stats-container">
                        <div class="stat-card stat-primary">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo number_format($totalMatricula2122); ?></h3>
                                <p>Matrícula 2021-22</p>
                            </div>
                        </div>

                        <div class="stat-card stat-success">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo number_format($totalMatricula2324); ?></h3>
                                <p>Matrícula 2023-24</p>
                            </div>
                        </div>

                        <div class="stat-card <?php echo $totalCrecimiento >= 0 ? 'stat-success' : 'stat-warning'; ?>">
                            <div class="stat-icon">
                                <i class="fas fa-chart<?php echo $totalCrecimiento >= 0 ? '' : ''; ?>"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo number_format($totalCrecimiento, 1); ?>%</h3>
                                <p>Crecimiento Total</p>
                            </div>
                        </div>

                        <div class="stat-card stat-info">
                            <div class="stat-icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo count($datosMatricula); ?></h3>
                                <p>Niveles Educativos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Evolución de Matrícula -->
            <div class="panel animate-up delay-1">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-chart-line"></i> Evolución de la Matrícula por Nivel
                        Educativo</h3>
                    <p>Comparativa 2021-22 vs 2023-24</p>
                </div>
                <div class="panel-body">
                    <div id="matricula-evolution-chart" style="width: 100%; height: 400px;"></div>
                    <div class="chart-analysis">
                        <h4>Análisis de Tendencias:</h4>
                        <div class="trend-items">
                            <?php foreach ($datosMatricula as $fila):
                                $crecimiento = floatval($fila['dif_tot']);
                                $iconoTendencia = $crecimiento >= 0 ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
                                ?>
                                <div class="trend-item">
                                    <span class="trend-level"><?php echo $fila['nivel']; ?></span>
                                    <span class="trend-value">
                                        <i class="fas <?php echo $iconoTendencia; ?>"></i>
                                        <?php echo number_format($crecimiento, 1); ?>%
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Análisis por Género y Público vs Privado -->
            <div class="panel animate-up delay-2">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-balance-scale"></i> Análisis Comparativo</h3>
                    <p>Distribución por género y tipo de sostenimiento</p>
                </div>
                <div class="panel-body">
                    <div class="charts-container">
                        <div class="chart-panel half-width">
                            <h4><i class="fas fa-venus-mars"></i> Distribución por Género 2023-24</h4>
                            <div id="gender-distribution-chart" style="width: 100%; height: 350px;"></div>
                        </div>

                        <div class="chart-panel half-width">
                            <h4><i class="fas fa-building"></i> Matrícula Pública vs Privada</h4>
                            <div id="public-private-chart" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Indicadores de Rendimiento -->
            <div class="panel animate-up delay-3">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-graduation-cap"></i> Indicadores de Rendimiento Académico
                    </h3>
                    <p>Métricas de eficiencia, reprobación y deserción por nivel educativo</p>
                </div>
                <div class="panel-body">
                    <div class="performance-indicators">
                        <?php foreach ($indicadoresRendimiento as $indicador): ?>
                            <div class="performance-card">
                                <h4><?php echo ucfirst(strtolower($indicador['tipo_educativo'])); ?></h4>
                                <div class="performance-metrics">
                                    <div class="metric">
                                        <span class="metric-label">Reprobación</span>
                                        <span
                                            class="metric-value metric-warning"><?php echo number_format($indicador['reprobacion_tot'], 1); ?>%</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Deserción</span>
                                        <span
                                            class="metric-value metric-danger"><?php echo number_format($indicador['desercion_tot'], 1); ?>%</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Eficiencia</span>
                                        <span
                                            class="metric-value metric-success"><?php echo number_format($indicador['eficiencia_tot'], 1); ?>%</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Panel de Tendencias Históricas -->
            <div class="panel animate-up delay-4">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-history"></i> Tendencias Históricas de Indicadores</h3>
                    <p>Evolución temporal de los indicadores educativos</p>
                </div>
                <div class="panel-body">
                    <div id="historical-trends-chart" style="width: 100%; height: 400px;"></div>
                    <div class="chart-analysis">
                        <h4>Interpretación de Tendencias:</h4>
                        <p>Este gráfico muestra la evolución temporal de los diferentes indicadores educativos,
                            permitiendo identificar patrones de crecimiento o decrecimiento en el sistema educativo
                            municipal.</p>
                    </div>
                </div>
            </div>

            <!-- Panel de Exportación -->
            <div class="panel animate-up delay-5">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-download"></i> Exportar Datos</h3>
                    <p>Descarga los datos en diferentes formatos</p>
                </div>
                <div class="panel-body">
                    <div class="export-buttons">
                        <button onclick="exportToExcel()" class="export-btn excel">
                            <i class="fas fa-file-excel"></i> Exportar a Excel
                        </button>
                        <button onclick="exportToPDF()" class="export-btn pdf">
                            <i class="fas fa-file-pdf"></i> Exportar a PDF
                        </button>
                        <button onclick="printReport()" class="export-btn">
                            <i class="fas fa-print"></i> Imprimir Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- Scripts -->
    <script src="js/animations_global.js"></script>
    <script src="js/sidebar.js"></script>
    <script>
        // Datos para JavaScript
        const datosMatricula = <?php echo json_encode($datosMatricula); ?>;
        const datosMatriculaSexo = <?php echo json_encode($datosMatriculaSexo); ?>;
        const indicadoresRendimiento = <?php echo json_encode($indicadoresRendimiento); ?>;
        const indicadoresCantidad = <?php echo json_encode($indicadoresCantidad); ?>;
        const indicadoresPorcentaje = <?php echo json_encode($indicadoresPorcentaje); ?>;

        // Funciones de exportación placeholder
        function exportToExcel() {
            console.log('Exportar a Excel - En desarrollo');
            alert('Función de exportar a Excel en desarrollo');
        }

        function exportToPDF() {
            console.log('Exportar a PDF - En desarrollo');
            alert('Función de exportar a PDF en desarrollo');
        }

        function printReport() {
            console.log('Imprimir reporte');
            window.print();
        }
    </script>
    <script src="js/historicos.js"></script>
</body>

</html>