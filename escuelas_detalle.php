<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Mostrar todos los errores (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener datos educativos desde la base de datos
$datosEducativos = obtenerDatosEducativos();

// Calcular totales para resumen
$totales = calcularTotales($datosEducativos);
$totalEscuelas = $totales['escuelas']; // Usar la misma lógica que el dashboard principal
$totalAlumnos = $totales['alumnos']; // Usar la misma lógica que el dashboard principal

// Extraer datos por nivel educativo directamente desde $datosEducativos
$escuelasPorNivel = [];
for ($i = 1; $i < count($datosEducativos); $i++) {
    $tipoEducativo = $datosEducativos[$i][0];
    $escuelas = $datosEducativos[$i][1];
    $escuelasPorNivel[$tipoEducativo] = $escuelas;
}

// Calcular los porcentajes para cada nivel
$porcentajes = [];
foreach ($escuelasPorNivel as $nivel => $cantidad) {
    $porcentajes[$nivel] = round(($cantidad / $totalEscuelas) * 100);
}

// Obtener datos de escuelas por sostenimiento (públicas y privadas)
$escuelasPorSostenimiento = obtenerEscuelasPorSostenimiento();
$escuelasPublicas = $escuelasPorSostenimiento['publicas'];
$escuelasPrivadas = $escuelasPorSostenimiento['privadas'];
$porcentajePublicas = $escuelasPorSostenimiento['porcentaje_publicas'];
$porcentajePrivadas = $escuelasPorSostenimiento['porcentaje_privadas'];
$escuelasNivelSostenimiento = $escuelasPorSostenimiento['por_nivel'];

// Datos para el diagrama de eficiencia escolar
$datosEficiencia = [
    'primaria' => [
        'ingreso' => 100,
        'egreso' => 111,
        'diferencia' => 11,
        'cicloIngreso' => '2006-2007',
        'cicloEgreso' => '2011-2012'
    ],
    'secundaria' => [
        'ingreso' => 68,
        'egreso' => 90,
        'diferencia' => 22,
        'cicloIngreso' => '2012-2013',
        'cicloEgreso' => '2014-2015'
    ],
    'bachillerato' => [
        'ingreso' => 150,
        'egreso' => 105,
        'diferencia' => -45,
        'cicloIngreso' => '2015-2016',
        'cicloEgreso' => '2017-2018'
    ],
    'superior' => [
        'ingreso' => 32,
        'egreso' => 34,
        'diferencia' => 2,
        'cicloIngreso' => '2018-2019',
        'cicloEgreso' => '2022-2023'
    ],
    'transiciones' => [
        'primaria_secundaria' => -43,
        'secundaria_bachillerato' => 60,
        'bachillerato_superior' => -73
    ]
];


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Escuelas Corregidora | SEDEQ</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <link rel="stylesheet" href="./css/escuelas_detalle.css">
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
            <a href="resumen.php" class="sidebar-link"><i class="fas fa-chart-bar"></i><span>Resumen</span></a>
            <a href="#" class="sidebar-link active"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <a href="estudiantes.php" class="sidebar-link"><i
                    class="fas fa-user-graduate"></i><span>Estudiantes</span></a> <a href="#" class="sidebar-link"><i
                    class="fas fa-chalkboard-teacher"></i> <span>Docentes</span></a>
            <a href="historicos.php" class="sidebar-link"><i class="fas fa-history"></i> <span>Históricos</span></a>
        </div>
    </div>
    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title">
                <h1>Detalle de Escuelas Ciclo 2023 - 2024</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo date('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Panel de resumen de escuelas -->
            <div class="panel animate-up">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-school"></i> Resumen de Escuelas en Corregidora</h3>
                </div>
                <div class="panel-body">
                    <div class="stats-row">
                        <div class="stat-box animate-fade delay-1">
                            <div class="stat-value"><?php echo $totalEscuelas; ?> </div>
                            <div class="stat-label">Total Escuelas Ciclo escolar 2023-2024</div>
                        </div>
                        <div class="stat-box animate-fade delay-2">
                            <div class="stat-value">
                                <span class="public-schools"><?php echo $escuelasPublicas; ?></span>
                                <span class="separator"> / </span>
                                <span class="private-schools"><?php echo $escuelasPrivadas; ?></span>
                            </div>
                            <div class="stat-label">Escuelas Públicas / Privadas</div>
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
                                <div class="progress-fill public" style="width: <?php echo $porcentajePublicas; ?>%">
                                    <span class="progress-label"><?php echo $porcentajePublicas; ?>% Públicas</span>
                                </div>
                                <div class="progress-fill private" style="width: <?php echo $porcentajePrivadas; ?>%">
                                    <span class="progress-label"><?php echo $porcentajePrivadas; ?>% Privadas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="level-bars animate-sequence">
                        <h4>Distribución por Nivel</h4>
                        <div class="level-bar">
                            <span class="level-name">Inicial (E)</span>
                            <div class="level-track">
                                <div class="level-fill"
                                    style="width: <?php echo $porcentajes['Inicial (Escolarizado)']; ?>%">
                                    <span
                                        class="escuelas-count"><?php echo $escuelasPorNivel['Inicial (Escolarizado)']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Inicial (Escolarizado)'])): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Inicial (Escolarizado)']; ?>%</span>
                        </div>
                        <div class="level-bar">
                            <span class="level-name">Inicial (NE)</span>
                            <div class="level-track">
                                <div class="level-fill"
                                    style="width: <?php echo $porcentajes['Inicial (No Escolarizado)']; ?>%">
                                    <span
                                        class="escuelas-count"><?php echo $escuelasPorNivel['Inicial (No Escolarizado)']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Inicial (No Escolarizado)'])): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Inicial (No Escolarizado)']; ?>%</span>
                        </div>
                        <div class="level-bar">
                            <span class="level-name">Especial (CAM)</span>
                            <div class="level-track">
                                <div class="level-fill" style="width: <?php echo $porcentajes['Especial (CAM)']; ?>%">
                                    <span
                                        class="escuelas-count"><?php echo $escuelasPorNivel['Especial (CAM)']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Especial (CAM)'])): ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Especial (CAM)']; ?>%</span>
                        </div>
                        <div class="level-bar">
                            <span class="level-name">Preescolar</span>
                            <div class="level-track">
                                <div class="level-fill" style="width: <?php echo $porcentajes['Preescolar']; ?>%">
                                    <span class="escuelas-count"><?php echo $escuelasPorNivel['Preescolar']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Preescolar'])): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Preescolar']; ?>%</span>
                        </div>
                        <div class="level-bar">
                            <span class="level-name">Primaria</span>
                            <div class="level-track">
                                <div class="level-fill" style="width: <?php echo $porcentajes['Primaria']; ?>%">
                                    <span class="escuelas-count"><?php echo $escuelasPorNivel['Primaria']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Primaria'])): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Primaria']; ?>%</span>
                        </div>
                        <div class="level-bar">
                            <span class="level-name">Secundaria</span>
                            <div class="level-track">
                                <div class="level-fill" style="width: <?php echo $porcentajes['Secundaria']; ?>%">
                                    <span class="escuelas-count"><?php echo $escuelasPorNivel['Secundaria']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Secundaria'])): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Secundaria']; ?>%</span>
                        </div>
                        <div class="level-bar">
                            <span class="level-name">Media Sup.</span>
                            <div class="level-track">
                                <div class="level-fill" style="width: <?php echo $porcentajes['Media Superior']; ?>%">
                                    <span
                                        class="escuelas-count"><?php echo $escuelasPorNivel['Media Superior']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Media Superior'])): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Media Superior']; ?>%</span>
                        </div>
                        <div class="level-bar">
                            <span class="level-name">Superior</span>
                            <div class="level-track">
                                <div class="level-fill" style="width: <?php echo $porcentajes['Superior']; ?>%">
                                    <span class="escuelas-count"><?php echo $escuelasPorNivel['Superior']; ?></span>
                                    <?php if (isset($escuelasNivelSostenimiento['Superior'])): ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="level-percent"><?php echo $porcentajes['Superior']; ?>%</span>
                        </div>
                    </div>
                </div>
            </div> <!-- Panel de eficiencia educativa -->
            <div class="panel animate-up delay-2">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-chart-line"></i> Eficiencia del Sistema Educativo en
                        Corregidora</h3>
                    <div class="panel-actions">
                        <label class="radio-option">
                            <input type="radio" name="view-type" value="diagram" checked>
                            <span>Vista Diagrama</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="view-type" value="data">
                            <span>Vista Datos</span>
                        </label>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="efficiency-diagram-view" class="education-flow">
                        <div id="flow-container">
                            <!-- El diagrama de flujo se renderizará aquí con JS -->
                        </div>
                    </div>
                    <div id="efficiency-chart-view" style="display:none; height:350px;">
                        <div id="efficiency-chart" style="width:100%; height:100%;"></div>
                    </div>
                </div>
            </div> <!-- Panel de análisis de trayectorias -->
            <div class="panel animate-up delay-3">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fa-solid fa-magnifying-glass"></i> Análisis de Trayectorias
                        Educativas</h3>
                </div>
                <div class="panel-body">
                    <div class="analysis-tabs animate-fade delay-3">
                        <div class="tab active animate-hover" data-tab="primaria-tab">Primaria</div>
                        <div class="tab animate-hover" data-tab="secundaria-tab">Secundaria</div>
                        <div class="tab animate-hover" data-tab="bachillerato-tab">Bachillerato</div>
                        <div class="tab animate-hover" data-tab="superior-tab">Superior</div>
                    </div>

                    <div id="primaria-tab" class="tab-content active">
                        <div class="stats-card">
                            <div class="stat-indicator up">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div>
                                <strong>Incremento:</strong> +11 estudiantes<br>
                            </div>
                        </div>
                        <h4>Primaria (2006-2007 → 2011-2012)</h4>
                        <p><strong>Ingreso:</strong> 100 estudiantes en 1° grado</p>
                        <p><strong>Egreso:</strong> 111 estudiantes de 6° grado</p>
                        <div class="interpretation">
                            <strong>Interpretación:</strong> En primaria hay un ligero incremento en la transición de
                            ingreso a egreso, lo que indica una muy buena retención en esos seis años.
                        </div>
                    </div>

                    <div id="secundaria-tab" class="tab-content">
                        <div class="stats-card">
                            <div class="stat-indicator down">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div>
                                <strong>Transición:</strong> -43 estudiantes<br>
                                <strong>Incremento Interno:</strong> +22 estudiantes<br>
                            </div>
                        </div>
                        <h4>Secundaria (2012-2013 → 2014-2015)</h4>
                        <p><strong>Ingreso:</strong> 68 estudiantes en 1° grado</p>
                        <p><strong>Egreso:</strong> 90 estudiantes de 3° grado</p>
                        <div class="interpretation">
                            <strong>Interpretación:</strong> Hay una fuga importante al inicio de secundaria
                            (posiblemente por cambio de escuela, traslados o abandono), pero de quienes sí inician, la
                            mayoría alcanza el egreso de tercer grado.
                        </div>
                    </div>

                    <div id="bachillerato-tab" class="tab-content">
                        <div class="stats-card">
                            <div class="stat-indicator up">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div>
                                <strong>Transición:</strong> +60 estudiantes<br>
                                <strong>Decremento Interno:</strong> -45 estudiantes<br>
                            </div>
                        </div>
                        <h4>Bachillerato (2015-2016 → 2017-2018)</h4>
                        <p><strong>Ingreso:</strong> 150 estudiantes en 1° semestre</p>
                        <p><strong>Egreso:</strong> 105 estudiantes de 3° año</p>
                        <div class="interpretation">
                            <strong>Interpretación:</strong> La atracción hacia el bachillerato es fuerte (quizá por
                            oferta o convenios), pero la deserción durante los años de preparación técnica o
                            preuniversitaria es significativa.
                        </div>
                    </div>

                    <div id="superior-tab" class="tab-content">
                        <div class="stats-card">
                            <div class="stat-indicator down">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div>
                                <strong>Transición:</strong> -73 estudiantes<br>
                                <strong>Incremento Interno:</strong> +2 estudiantes<br>
                            </div>
                        </div>
                        <h4>Educación Superior (2018-2019 → 2022-2023)</h4>
                        <p><strong>Ingreso:</strong> 32 estudiantes en 1° año</p>
                        <p><strong>Egreso:</strong> 34 estudiantes graduados</p>
                        <div class="interpretation">
                            <strong>Interpretación:</strong> Existe una enorme brecha entre quienes finalizan
                            bachillerato y quienes acceden a la universidad dentro del municipio (posiblemente por
                            traslado a otras ciudades o falta de oferta local). Sin embargo, quienes sí entran tienen
                            muy buena probabilidad de graduarse.
                        </div>
                    </div>
                </div>
            </div> <!-- Panel de conclusiones -->
            <div class="panel animate-up delay-4">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-clipboard-check"></i> Conclusiones del Análisis</h3>
                </div>
                <div class="panel-body">
                    <div class="conclusion-list">
                        <div class="conclusion-item animate-left delay-5">
                            <div class="conclusion-icon strength">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h4>Fortalezas</h4>
                                <ul>
                                    <li>Alta retención en educación primaria</li>
                                    <li>Excelente retención al final de secundaria</li>
                                    <li>Fuerte atracción inicial en bachillerato</li>
                                    <li>Alta tasa de graduación universitaria para quienes ingresan</li>
                                </ul>
                            </div>
                        </div>
                        <div class="conclusion-item animate-right delay-5">
                            <div class="conclusion-icon weakness">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h4>Áreas de Oportunidad</h4>
                                <ul>
                                    <li>Deserción significativa al entrar a secundaria</li>
                                    <li>Pérdida importante de matrícula durante bachillerato</li>
                                    <li>Gran brecha entre egresados de bachillerato y acceso a educación superior local
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h1>Continuará....</h1>
            <h1>Escuelas detalle will return</h1>
        </div>

        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
        </footer>
    </div>
    <script>
        <?php
        // Convertir datos de eficiencia a formato JSON para usar en JavaScript
        echo "const datosEficiencia = " . json_encode($datosEficiencia) . ";\n";
        echo "const totalEscuelas = " . $totalEscuelas . ";\n";
        echo "const totalAlumnos = " . $totalAlumnos . ";\n";
        echo "const escuelasPublicas = " . $escuelasPublicas . ";\n";
        echo "const escuelasPrivadas = " . $escuelasPrivadas . ";\n";
        echo "const porcentajePublicas = " . $porcentajePublicas . ";\n";
        echo "const porcentajePrivadas = " . $porcentajePrivadas . ";\n";
        echo "const escuelasNivelSostenimiento = " . json_encode($escuelasNivelSostenimiento) . ";\n";
        echo "const escuelasPorNivel = " . json_encode($escuelasPorNivel) . ";\n";
        ?>
    </script>
    <script src="./js/script.js"></script>
    <script src="./js/escuelas_diagram.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
    <script src="./js/escuelas_publicas_privadas.js"></script>
    <script>
        // Variables de datos específicos para esta página
        // (No hay código del sidebar aquí para evitar conflictos con sidebar.js)
    </script>
</body>

</html>