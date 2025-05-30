<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener datos de matrícula de escuelas públicas
$datosMatricula = obtenerMatriculaPorEscuelasPublicas();

// Extraer años escolares disponibles
$añosEscolares = array_keys($datosMatricula);

// Calcular totales por año
$totalesPorAño = [];
foreach ($datosMatricula as $año => $datos) {
    $totalesPorAño[$año] = array_sum(array_values($datos));
}

// Obtener total general (suma de todos los años más recientes)
if (isset($datosMatricula['2023-2024'])) {
    $totalGeneral = $totalesPorAño['2023-2024'];
} else {
    $totalGeneral = end($totalesPorAño);
}

// Calcular la tendencia general (comparar con el año anterior)
$tendencia = 0;
$años = array_keys($totalesPorAño);
if (count($años) >= 2) {
    $últimoAño = end($años);
    $penúltimoAño = prev($años);
    if (isset($totalesPorAño[$penúltimoAño]) && $totalesPorAño[$penúltimoAño] > 0) {
        $tendencia = (($totalesPorAño[$últimoAño] - $totalesPorAño[$penúltimoAño]) / $totalesPorAño[$penúltimoAño]) * 100;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Estudiantes | SEDEQ</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/estudiantes.css">
    <link rel="stylesheet" href="./css/improvements.css">
    <link rel="stylesheet" href="./css/animations_global.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <style>
        /* Estos estilos se han movido al archivo sidebar.css */
    </style>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">    <!-- Bibliotecas para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <!-- Biblioteca para capturar elementos como imagen -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<body> <!-- Overlay para cerrar el menú en móviles -->
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
            <a href="#" class="sidebar-link active"><i class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
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
                <h1>Estadísticas de Estudiantes en Escuelas Públicas ciclo 2023 - 2024</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo date('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Panel de resumen de estudiantes -->
            <div class="matricula-panel animate-fade delay-1">                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-user-graduate"></i> Matrícula Total en Escuelas
                        Públicas</h3>
                    <div class="export-buttons">
                        <button id="export-pdf" class="export-button">
                            <i class="fas fa-file-pdf"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="matricula-body">
                    <div class="stats-row">
                        <div class="stat-box">
                            <div class="stat-value"><?php echo number_format($totalGeneral); ?></div>
                            <div class="stat-label">Total de Estudiantes (Ciclo más reciente)</div>
                        </div>
                    </div>
                    <div class="chart-controls">
                        <div class="selector-container">
                            <div class="selector-label"><i class="fas fa-filter"></i> Filtrar por año:</div>
                            <div class="year-selector">
                                <div class="year-option active" data-year="todos">Todos los años</div>
                                <?php foreach ($añosEscolares as $año): ?>
                                    <div class="year-option" data-year="<?php echo $año; ?>"><?php echo $año; ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="selector-container">
                            <div class="selector-label"><i class="fas fa-filter"></i> Filtrar por nivel:</div>
                            <div class="level-selector">
                                <div class="level-option active" data-level="todos">Todos los niveles</div>
                                <div class="level-option" data-level="Inicial NE">Inicial NE</div>
                                <div class="level-option" data-level="CAM">CAM</div>
                                <div class="level-option" data-level="Preescolar">Preescolar</div>
                                <div class="level-option" data-level="Primaria">Primaria</div>
                                <div class="level-option" data-level="Secundaria">Secundaria</div>
                                <div class="level-option" data-level="Media superior">Media Superior</div>
                                <div class="level-option" data-level="Superior">Superior</div>
                            </div>
                        </div>
                    </div>

                    <div id="chart-matricula-container" class="chart-container" style="height: 520px; min-height: 400px;">
                        <div id="chart-matricula" style="width:100%; height:100%"></div>
                    </div>

                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #5C6BC0"></div>
                            <span>Inicial NE</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #26A69A"></div>
                            <span>CAM</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #FFA726"></div>
                            <span>Preescolar</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #EF5350"></div>
                            <span>Primaria</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #7E57C2"></div>
                            <span>Secundaria</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #66BB6A"></div>
                            <span>Media Superior</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #29B6F6"></div>
                            <span>Superior</span>
                        </div>
                    </div>

                    <div class="data-table-container">
                        <h4>Datos numéricos de matrícula por nivel y año escolar</h4>
                        <table id="tabla-matricula" class="data-table"></table>
                    </div>
                </div>
            </div>

            <!-- Panel de análisis de tendencias (se puede agregar en el futuro) -->
            <div class="matricula-panel animate-fade delay-2">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-chart-line"></i> Análisis de Tendencias</h3>
                </div>
                <div class="matricula-body">
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-tools" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                        <h3>Sección en Desarrollo</h3>
                        <p>Próximamente se incluirá un análisis detallado de tendencias de matrícula por nivel
                            educativo.</p>
                    </div>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
        </footer>
    </div>
    <script>
        <?php
        // Convertir los datos de matrícula a formato JSON para usar en JavaScript
        echo "const datosMatricula = " . json_encode($datosMatricula) . ";\n";
        ?>
    </script>    <script src="./js/script.js"></script>
    <script src="./js/export-graficos-mejorado.js"></script>
    <script src="./js/estudiantes.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
    <script>
        /* La funcionalidad del sidebar se ha movido al archivo sidebar.js */
    </script>
</body>

</html>