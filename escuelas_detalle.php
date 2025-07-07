<?php
/**
 * =============================================================================
 * PÁGINA DE DETALLE DE ESCUELAS - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Esta página presenta un análisis detallado del sistema educativo en el
 * municipio de Corregidora, Querétaro, incluyendo di                    <div class="subcontrol-intro animate-fade delay-1">
                        <p>Análisis detallado de las <strong><?php echo $totalEscuelasSubcontrol; ?> escuelas</strong> 
                           del municipio de Corregidora según su subcontrol administrativo y fuente de financiamiento.</p>
                    </div>bución por niveles,
 * sostenimiento (público/privado) y diagramas de flujo educativo.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Resumen ejecutivo de escuelas por nivel educativo
 * - Análisis comparativo entre escuelas públicas y privadas
 * - Diagramas de flujo del sistema educativo
 * - Métricas de eficiencia y retención escolar
 * - Filtros dinámicos por tipo de sostenimiento
 * 
 * COMPONENTES ANALÍTICOS:
 * - Distribución porcentual por nivel educativo
 * - Barras de progreso comparativas
 * - Conclusiones automáticas basadas en datos
 * - Recomendaciones para política educativa
 * 
 * VISUALIZACIONES ESPECIALIZADAS:
 * - Gráficos de barras horizontales con mini-indicadores
 * - Diagramas de flujo interactivos
 * - Paneles de análisis con pestañas navegables
 * - Indicadores de tendencias y variaciones
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Escuelas_Detalle
 * @version 2.0
 */

// =============================================================================
// CONFIGURACIÓN DEL ENTORNO DE DESARROLLO
// =============================================================================

// Incluir el helper de sesiones para manejo de autenticación
require_once 'session_helper.php';

// Inicializar sesión y configurar usuario de demostración si es necesario
iniciarSesionDemo();

// CONFIGURACIÓN DE DEPURACIÓN (remover en producción)
// Estas configuraciones permiten ver errores durante el desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// =============================================================================
// OBTENCIÓN Y PROCESAMIENTO DE DATOS EDUCATIVOS
// =============================================================================

// Incluir módulo de conexión con funciones especializadas de consulta
require_once 'conexion.php';

// Obtener conjunto completo de datos educativos desde la base de datos
// Incluye información de todas las escuelas por nivel y modalidad
$datosEducativos = obtenerDatosEducativos();

// =============================================================================
// CÁLCULOS ESTADÍSTICOS GENERALES
// =============================================================================

// Calcular totales agregados para métricas principales del dashboard
// Utiliza la misma lógica de cálculo que el dashboard principal para consistencia
$totales = calcularTotales($datosEducativos);
$totalEscuelas = $totales['escuelas']; // Total de instituciones educativas
$totalAlumnos = $totales['alumnos'];   // Total de estudiantes matriculados

// =============================================================================
// PROCESAMIENTO DE DATOS POR NIVEL EDUCATIVO
// =============================================================================

// Extraer y estructurar datos por nivel educativo desde el dataset principal
// Se omite la primera fila que contiene encabezados de la consulta
$escuelasPorNivel = [];
for ($i = 1; $i < count($datosEducativos); $i++) {
    $tipoEducativo = $datosEducativos[$i][0]; // Nombre del nivel (ej: "Primaria")
    $escuelas = $datosEducativos[$i][1];      // Cantidad de escuelas
    $escuelasPorNivel[$tipoEducativo] = $escuelas;
}

// Calcular distribución porcentual para análisis comparativo
// Cada nivel se expresa como porcentaje del total de escuelas
$porcentajes = [];
foreach ($escuelasPorNivel as $nivel => $cantidad) {
    $porcentajes[$nivel] = round(($cantidad / $totalEscuelas) * 100);
}

// =============================================================================
// ANÁLISIS POR TIPO DE SOSTENIMIENTO
// =============================================================================

// Obtener datos segmentados por sostenimiento (público vs privado)
// Incluye datos agregados y desglosados por nivel educativo
$escuelasPorSostenimiento = obtenerEscuelasPorSostenimiento();
$escuelasPublicas = $escuelasPorSostenimiento['publicas'];           // Total escuelas públicas
$escuelasPrivadas = $escuelasPorSostenimiento['privadas'];           // Total escuelas privadas
$porcentajePublicas = $escuelasPorSostenimiento['porcentaje_publicas']; // % públicas
$porcentajePrivadas = $escuelasPorSostenimiento['porcentaje_privadas']; // % privadas
$escuelasNivelSostenimiento = $escuelasPorSostenimiento['por_nivel'];    // Desglose por nivel

// =============================================================================
// ANÁLISIS POR SUBCONTROL EDUCATIVO
// =============================================================================

// Obtener datos segmentados por subcontrol educativo según análisis verificado
// Incluye PRIVADO, FEDERAL TRANSFERIDO, FEDERAL, ESTATAL y AUTÓNOMO
$escuelasPorSubcontrol = obtenerEscuelasPorSubcontrol();
$totalEscuelasSubcontrol = $escuelasPorSubcontrol['total_escuelas'];
$distribucionSubcontrol = $escuelasPorSubcontrol['distribución'];

// =============================================================================
// DATOS PARA ANÁLISIS DE EFICIENCIA EDUCATIVA
// =============================================================================

// Configurar datos de flujo educativo para diagrama de eficiencia
// Representa tasas de ingreso, permanencia y egreso por nivel educativo
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
            <a href="alumnos.php" class="sidebar-link"><i class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="docentes.php" class="sidebar-link"><i class="fas fa-chalkboard-teacher"></i>
                <span>Docentes</span></a>
            <a href="estudiantes.php" class="sidebar-link"><i class="fas fa-history"></i> <span>Históricos</span></a>
        </div>
    </div>
    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Detalle de Escuelas Ciclo 2023 - 2024</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></span>
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
            </div>

            <!-- Panel de distribución por subcontrol educativo -->
            <div class="panel animate-up delay-1">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-building"></i> Distribución por Subcontrol Educativo</h3>
                </div>
                <div class="panel-body">
                    <div class="subcontrol-cards animate-sequence">
                        <?php foreach ($distribucionSubcontrol as $subcontrol => $datos): ?>
                            <?php
                            // Normalizar el subcontrol para manejar problemas de encoding
                            $subcontrolNormalizado = $subcontrol;
                            if ($subcontrol === 'AUT?NOMO' || strpos($subcontrol, 'AUT') === 0) {
                                $subcontrolNormalizado = 'AUTÓNOMO';
                            }
                            $dataAttribute = strtolower(str_replace(array(' ', 'Ó'), array('-', 'o'), $subcontrolNormalizado));
                            ?>
                            <div class="subcontrol-card animate-scale" data-subcontrol="<?php echo $dataAttribute; ?>">
                                <div class="subcontrol-header">
                                    <div class="subcontrol-info">
                                        <h4 class="subcontrol-name"><?php echo $subcontrolNormalizado; ?></h4>
                                        <div class="subcontrol-stats">
                                            <span class="subcontrol-count"><?php echo $datos['total']; ?></span>
                                            <span class="subcontrol-label">escuelas</span>
                                            <span class="subcontrol-percentage"><?php echo $datos['porcentaje']; ?>%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="subcontrol-progress">
                                    <div class="progress-bar-subcontrol">
                                        <div class="progress-fill-subcontrol"
                                            style="width: <?php echo $datos['porcentaje']; ?>%"
                                            data-subcontrol="<?php echo $dataAttribute; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="subcontrol-details">
                                    <div class="details-header">
                                        <span>Desglose por nivel educativo</span>
                                    </div>
                                    <div class="details-content">
                                        <?php
                                        // Definir orden específico de los niveles educativos
                                        $ordenNiveles = [
                                            'Inicial Escolarizado' => 1,
                                            'Inicial No Escolarizado' => 2,
                                            'Especial (CAM)' => 3,
                                            'Preescolar' => 4,
                                            'Primaria' => 5,
                                            'Secundaria' => 6,
                                            'Media Superior' => 7,
                                            'Superior' => 8
                                        ];

                                        // Crear array ordenado de niveles con sus cantidades
                                        $nivelesOrdenados = [];
                                        foreach ($ordenNiveles as $nivel => $indice) {
                                            // Buscar coincidencias flexibles para el nivel
                                            foreach ($datos['desglose'] as $nivelOriginal => $cantidad) {
                                                if (
                                                    stripos($nivelOriginal, $nivel) !== false ||
                                                    stripos($nivel, $nivelOriginal) !== false ||
                                                    ($nivel === 'Inicial Escolarizado' && stripos($nivelOriginal, 'Inicial') !== false && stripos($nivelOriginal, 'Escolar') !== false) ||
                                                    ($nivel === 'Inicial No Escolarizado' && stripos($nivelOriginal, 'Inicial') !== false && stripos($nivelOriginal, 'No Escolar') !== false) ||
                                                    ($nivel === 'Especial (CAM)' && (stripos($nivelOriginal, 'CAM') !== false || stripos($nivelOriginal, 'Especial') !== false)) ||
                                                    ($nivel === 'Preescolar' && stripos($nivelOriginal, 'Preescolar') !== false) ||
                                                    ($nivel === 'Primaria' && stripos($nivelOriginal, 'Primaria') !== false) ||
                                                    ($nivel === 'Secundaria' && stripos($nivelOriginal, 'Secundaria') !== false) ||
                                                    ($nivel === 'Media Superior' && stripos($nivelOriginal, 'Media Superior') !== false) ||
                                                    ($nivel === 'Superior' && stripos($nivelOriginal, 'Superior') !== false && stripos($nivelOriginal, 'Media') === false)
                                                ) {
                                                    $nivelesOrdenados[$indice] = [
                                                        'nombre' => $nivelOriginal,
                                                        'cantidad' => $cantidad
                                                    ];
                                                    break;
                                                }
                                            }
                                        }

                                        // Ordenar por índice y mostrar
                                        ksort($nivelesOrdenados);
                                        foreach ($nivelesOrdenados as $nivelData): ?>
                                            <div class="detail-item">
                                                <span class="detail-level"><?php echo $nivelData['nombre']; ?></span>
                                                <span class="detail-count"><?php echo $nivelData['cantidad']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Resumen estadístico del subcontrol -->
                    <div class="subcontrol-summary animate-fade delay-3">
                        <div class="summary-stats">
                            <div class="summary-item">
                                <i class="fas fa-chart-pie"></i>
                                <div>
                                    <span
                                        class="summary-value"><?php echo $distribucionSubcontrol['PRIVADO']['porcentaje']; ?>%</span>
                                    <span class="summary-label">Privadas</span>
                                </div>
                            </div>
                            <div class="summary-item">
                                <i class="fas fa-building-columns"></i>
                                <div>
                                    <span
                                        class="summary-value"><?php echo round($distribucionSubcontrol['FEDERAL TRANSFERIDO']['porcentaje'] + $distribucionSubcontrol['FEDERAL']['porcentaje'] + $distribucionSubcontrol['ESTATAL']['porcentaje'] + $distribucionSubcontrol['AUTÓNOMO']['porcentaje'], 1); ?>%</span>
                                    <span class="summary-label">Públicas</span>
                                </div>
                            </div>
                            <div class="summary-item">
                                <i class="fas fa-crown"></i>
                                <div>
                                    <span
                                        class="summary-value"><?php echo $distribucionSubcontrol['FEDERAL TRANSFERIDO']['porcentaje']; ?>%</span>
                                    <span class="summary-label">Fed. Transferido</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de eficiencia educativa -->
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

        // Datos de subcontrol educativo
        echo "const totalEscuelasSubcontrol = " . $totalEscuelasSubcontrol . ";\n";
        echo "const distribucionSubcontrol = " . json_encode($distribucionSubcontrol) . ";\n";
        ?>
    </script>
    <script src="./js/script.js"></script>
    <script src="./js/escuelas_diagram.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
    <script src="./js/escuelas_publicas_privadas.js"></script>
</body>

</html>