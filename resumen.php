<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener datos educativos desde la base de datos
$datosEducativos = obtenerDatosEducativos();

// Calcular totales para resumen
$totales = calcularTotales($datosEducativos);
$totalEscuelas = $totales['escuelas'];
$totalAlumnos = $totales['alumnos'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estadístico Educativo Corregidora| SEDEQ</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bibliotecas para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</head>

<body> <!-- Overlay para cerrar el menú en móviles -->
    <div class="sidebar-overlay"></div>

    <div class="sidebar">
        <div class="logo-container">
            <img src="./img/layout_set_logo.png" alt="Logo SEDEQ" class="logo">
        </div>
        <div class="sidebar-links">
            <a href="home.php" class="sidebar-link"><i class="fas fa-home"></i> <span>Regresar al Home</span></a>
            <a href="#" class="sidebar-link active"><i class="fas fa-chart-bar"></i> <span>Resumen</span></a>
            <a href="escuelas_detalle.php" class="sidebar-link"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <a href="estudiantes.php" class="sidebar-link"><i class="fas fa-user-graduate"></i>
                <span>Estudiantes</span></a>
            <a href="#" class="sidebar-link"><i class="fas fa-chalkboard-teacher"></i> <span>Docentes</span></a>
            <a href="historicos.php" class="sidebar-link"><i class="fas fa-history"></i> <span>Históricos</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title">
                <h1>Dashboard Estadístico Educativo Corregidora Ciclo 2023 - 2024</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo date('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>
        <div class="dashboard-grid">
            <div class="card summary-card animate-fade">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Resumen Ejecutivo</h2>
                </div>
                <div class="card-body">
                    <div class="metric animate-left delay-1">
                        <div class="metric-icon decline">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="metric-details">
                            <h3 class="metric-title">Total Alumnos <i class="fas fa-info-circle info-icon"
                                    data-tooltip="* Los datos de alumnos y escuelas de los servicios USAER no se suman en básica ya que se cuentan en los niveles correspondientes."></i>
                            </h3>
                            <p class="metric-value" id="metricDecline">
                                <?php echo number_format($totalAlumnos, 0, '.', ','); ?>
                            </p>
                            <p class="metric-change" id="metricDeclineChange">Ciclo escolar 2023-2024</p>
                        </div>
                    </div>
                    <div class="metric animate-left delay-2">
                        <div class="metric-icon growth">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="metric-details">
                            <h3 class="metric-title">Total Escuelas <i class="fas fa-info-circle info-icon"
                                    data-tooltip="** En el total de Escuelas de Media Superior se cuantifican planteles y en Superior se cuantifican instituciones
                                *** El total de Escuelas y docentes de Superior en el Estado no corresponde a la suma de escuelas en los municipios, debido a que en algunos casos sólo se registra la institución en la capital del Estado y no se desglosan las unidades académicas en los municipios donde se imparten estudios"></i>
                            </h3>
                            <p class="metric-value" id="metricGrowth">
                                <?php echo number_format($totalEscuelas, 0, '.', ','); ?>
                            </p>
                            <p class="metric-change" id="metricGrowthChange">Ciclo escolar 2023-2024</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-icon investment">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="metric-details">
                            <h3 class="metric-title">Porcentaje Matrícula <i class="fas fa-info-circle info-icon"
                                    data-tooltip="DEL TOTAL DE MATRÍCULA DEL ESTADO CORREGIDORA TIENE EL:
                                7.57 % de la matrícula del nivel preescolar
                                7.79 % de la matrícula del nivel primaria
                                7.71 % de la matrícula del nivel secundaria
                                9.86 % de la matrícula del nivel bachillerato"></i>
                            </h3>
                            <p class="metric-value">7.98%</p>
                            <p class="metric-change">Respecto al Estado</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card analysis-card animate-fade delay-3">
                <div class="card-header">
                    <h2><i class="fas fa-analytics"></i> Análisis de Tendencias</h2>
                </div>
                <div class="card-body">
                    <p id="analisisDinamico" class="animate-up delay-4">
                        El análisis muestra que <span class="highlight">Primaria</span> tiene el mayor número de
                        escuelas con <span class="highlight">180</span>
                        instituciones y <span class="highlight">45,000</span> alumnos en total. El nivel <span
                            class="highlight">Superior</span> cuenta con menos
                        planteles (<span class="highlight">25</span>) pero mantiene una proporción alta de alumnos por
                        escuela.
                    </p>
                </div>
            </div>
            <div class="card chart-card animate-fade delay-4">
                <div class="card-header">
                    <h2><i class="fas fa-chart-bar"></i> Estadística Educativa por Tipo</h2>
                    <div class="card-actions">
                        <button id="exportChartPNG" class="action-button" title="Exportar gráfico como PNG">
                            <i class="fas fa-image"></i>
                        </button>
                        <button id="exportChartPDF" class="action-button" title="Exportar gráfico como PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chart_div" class="animate-scale delay-5"></div>
                </div>
            </div>
            <div class="card controls-card animate-right delay-5">
                <div class="card-header">
                    <h2><i class="fas fa-sliders-h"></i> Ajustes de Visualización</h2>
                </div>
                <div class="card-body">
                    <div class="control-group animate-fade">
                        <label class="slider-label">
                            <i class="fas fa-eye"></i> Mostrar:
                        </label>
                        <div class="control-options">
                            <label class="radio-container">
                                <input type="radio" name="visualizacion" value="ambos" checked> Ambos
                            </label>
                            <label class="radio-container">
                                <input type="radio" name="visualizacion" value="escuelas"> Solo Escuelas
                            </label>
                            <label class="radio-container">
                                <input type="radio" name="visualizacion" value="alumnos"> Solo Alumnos
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

            <div class="card table-card">
                <div class="card-header">
                    <h2><i class="fas fa-table"></i> Datos Numéricos</h2>
                    <div class="card-actions">
                        <button id="exportExcel" class="action-button" title="Exportar a Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                        <button id="exportPDF" class="action-button" title="Exportar a PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-container">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr>
                                <th>Tipo Educativo</th>
                                <th>Escuelas</th>
                                <th>Alumnos</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            <!-- Los datos se cargarán dinámicamente desde script.js -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
        </footer>
    </div> <!-- Script con datos desde PHP -->
    <script>
        <?php
        // Convertir a formato JSON para usar en JavaScript
        echo "const datosEducativos = " . json_encode($datosEducativos) . ";\n";
        echo "const totalEscuelas = " . $totalEscuelas . ";\n";
        echo "const totalAlumnos = " . $totalAlumnos . ";\n";
        echo "const totalEscuelasFormateado = '" . number_format($totalEscuelas, 0, '.', ',') . "';\n";
        echo "const totalAlumnosFormateado = '" . number_format($totalAlumnos, 0, '.', ',') . "';\n";
        ?>    </script> <!-- Script del dashboard -->
    <script src="./js/script.js"></script>
    <script src="./js/export-graficos-mejorado.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
</body>

</html>