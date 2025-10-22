<?php
/**
 * =============================================================================
 * PÁGINA DE ESTADÍSTICAS DE MATRÍCULA ESTUDIANTIL - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Esta página presenta las estadísticas consolidadas de matrícula estudiantil
 * por nivel educativo en el municipio de Corregidora, Querétaro.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Visualización de matrícula por nivel educativo
 * - Comparativo entre sector público y privado
 * - Gráficos interactivos con Google Charts
 * - Exportación de datos en múltiples formatos
 * - Análisis de distribución por sectores
 * 
 * ARQUITECTURA DE DATOS:
 * - Datos consolidados desde múltiples tablas PostgreSQL
 * - Sistema de fallback con datos representativos
 * - Procesamiento de datos con totales automáticos
 * - Optimización de consultas para rendimiento
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Alumnos
 * @version 2.0
 */

// =============================================================================
// CONFIGURACIÓN E INICIALIZACIÓN DEL SISTEMA
// =============================================================================

// Incluir el helper de sesiones para manejo de autenticación
require_once 'session_helper.php';

// Inicializar sesión y configurar usuario de demostración si es necesario
iniciarSesionDemo();

// Incluir módulo de conexión actualizado para consultas dinámicas
require_once 'conexion_prueba_2024.php';

// =============================================================================
// OBTENCIÓN DE PARÁMETROS Y VALIDACIÓN
// =============================================================================

// Obtener el municipio desde el parámetro GET, por defecto Corregidora
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'CORREGIDORA';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'CORREGIDORA'; // Fallback a Corregidora si el municipio no es válido
}

// =============================================================================
// PROCESAMIENTO DE DATOS DE MATRÍCULA
// =============================================================================

// Obtener datos completos del municipio usando las funciones dinámicas
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Obtener datos de desglose público/privado para el municipio
$datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

// Verificar si hay datos
$hayError = !$datosCompletosMunicipio;
$tieneDatos = $datosCompletosMunicipio && isset($datosCompletosMunicipio['total_matricula']) && $datosCompletosMunicipio['total_matricula'] > 0;

// Convertir datos al formato que espera el frontend (compatible con formato anterior)
if ($tieneDatos) {
    // Preparar datos en el formato esperado por la interfaz existente
    $datosMatricula = [
        'totales' => [
            'general' => isset($datosCompletosMunicipio['total_matricula']) ? (int) $datosCompletosMunicipio['total_matricula'] : 0,
            'publico' => 0,
            'privado' => 0
        ],
        'datos_por_nivel' => []
    ];

    // Calcular totales público/privado desde los datos de desglose
    if (!empty($datosPublicoPrivado)) {
        foreach ($datosPublicoPrivado as $nivel => $datos) {
            $datosMatricula['totales']['publico'] += isset($datos['tot_mat_pub']) ? (int) $datos['tot_mat_pub'] : 0;
            $datosMatricula['totales']['privado'] += isset($datos['tot_mat_priv']) ? (int) $datos['tot_mat_priv'] : 0;

            // Agregar datos por nivel
            $datosMatricula['datos_por_nivel'][$datos['titulo_fila']] = [
                'publico' => isset($datos['tot_mat_pub']) ? (int) $datos['tot_mat_pub'] : 0,
                'privado' => isset($datos['tot_mat_priv']) ? (int) $datos['tot_mat_priv'] : 0,
                'total' => isset($datos['tot_mat']) ? (int) $datos['tot_mat'] : 0
            ];
        }
    }

    // Preparar datos de género (formato simplificado para compatibilidad)
    $matriculaPorGenero = [];
    if (!empty($datosPublicoPrivado)) {
        foreach ($datosPublicoPrivado as $nivel => $datos) {
            $matriculaPorGenero[$datos['titulo_fila']] = [
                'titulo_fila' => $datos['titulo_fila'],
                'hombres' => isset($datos['mat_h']) ? (int) $datos['mat_h'] : 0,
                'mujeres' => isset($datos['mat_m']) ? (int) $datos['mat_m'] : 0,
                'total' => isset($datos['tot_mat']) ? (int) $datos['tot_mat'] : 0
            ];
        }
    }

    // Datos de discapacidad (usar datos básicos disponibles)
    $alumnosDiscapacidad = []; // Se puede expandir cuando se tengan estos datos en el sistema dinámico

} else {
    // Datos vacíos si no hay información del municipio
    $datosMatricula = [
        'totales' => [
            'general' => 0,
            'publico' => 0,
            'privado' => 0
        ],
        'datos_por_nivel' => []
    ];

    $matriculaPorGenero = [];
    $alumnosDiscapacidad = [];
}

// Extraer datos organizados
$datosPorNivel = $datosMatricula['datos_por_nivel'];
$totales = $datosMatricula['totales'];

// =============================================================================
// PROCESAMIENTO DE DATOS POR NIVEL Y SUBNIVEL EDUCATIVO
// =============================================================================

// Obtener datos de alumnos por nivel y subnivel directamente de la base de datos
$datosAlumnosPorSubnivel = obtenerAlumnosPorNivelYSubnivel($municipioSeleccionado);

// Procesar datos para obtener totales por nivel y subnivel
$datosAlumnosGenero = array();
$datosAlumnosGenero[] = array('Nivel Educativo', 'Subnivel', 'Total Alumnos', 'Hombres', 'Mujeres', '% Hombres', '% Mujeres');

$alumnosPorNivel = array(); // Total por nivel principal
$totalAlumnosSubnivel = 0;

// Procesar datos dinámicos desde la base de datos
if ($datosAlumnosPorSubnivel && is_array($datosAlumnosPorSubnivel)) {
    foreach ($datosAlumnosPorSubnivel as $fila) {
        $nivelPrincipal = $fila['nivel'];
        $nombreSubnivel = $fila['subnivel'];
        $alumnos = intval($fila['total_alumnos']);
        $alumnosH = intval($fila['alumnos_hombres']);
        $alumnosM = intval($fila['alumnos_mujeres']);

        // Calcular porcentajes de género
        $porcH = $alumnos > 0 ? round(($alumnosH / $alumnos) * 100, 1) : 0;
        $porcM = $alumnos > 0 ? round(($alumnosM / $alumnos) * 100, 1) : 0;

        // Agregar a datos de género
        $datosAlumnosGenero[] = array($nivelPrincipal, $nombreSubnivel, $alumnos, $alumnosH, $alumnosM, $porcH, $porcM);

        // Acumular por nivel principal
        if (!isset($alumnosPorNivel[$nivelPrincipal])) {
            $alumnosPorNivel[$nivelPrincipal] = 0;
        }
        $alumnosPorNivel[$nivelPrincipal] += $alumnos;
        $totalAlumnosSubnivel += $alumnos;
    }
}

// =============================================================================
// PREPARACIÓN DE DATOS PARA GRÁFICOS
// =============================================================================

// Convertir datos para gráfico de barras comparativo
$datosGrafico = [];
$datosGrafico[] = ['Nivel Educativo', 'Público', 'Privado'];

foreach ($datosPorNivel as $nivel => $datos) {
    $datosGrafico[] = [$nivel, $datos['publico'], $datos['privado']];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrícula Estudiantil | SEDEQ</title>
    <link rel="stylesheet" href="./css/alumnos.css">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bibliotecas para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
            <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-chart-bar"></i><span>Resumen</span></a>
            <div class="sidebar-link-with-submenu">
                <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                    class="sidebar-link active has-submenu">
                    <i class="fas fa-user-graduate"></i>
                    <span>Estudiantes</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu active">
                    <a href="#resumen-general" class="submenu-link">
                        <i class="fas fa-users"></i>
                        <span>Resumen General</span>
                    </a>
                    <a href="#desglose-sostenimiento" class="submenu-link">
                        <i class="fas fa-table"></i>
                        <span>Desglose por Sostenimiento</span>
                    </a>
                    <a href="#analisis-nivel" class="submenu-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Análisis por Nivel Educativo</span>
                    </a>
                    <a href="#analisis-genero" class="submenu-link">
                        <i class="fas fa-venus-mars"></i>
                        <span>Análisis por Género</span>
                    </a>
                    <a href="#tabla-detallada-alumnos" class="submenu-link">
                        <i class="fas fa-list-alt"></i>
                        <span>Detalle por Subnivel</span>
                    </a>
                </div>
            </div>
            <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                class="sidebar-link"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-chalkboard-teacher"></i>
                <span>Docentes</span></a>
            <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-map-marked-alt"></i>
                <span>Mapas</span></a>
        </div>
    </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Matrícula Estudiantil por Nivel Educativo -
                    <?php echo htmlspecialchars($municipioSeleccionado, ENT_QUOTES, 'UTF-8'); ?>
                </h1>
                <?php if (!$tieneDatos): ?>
                    <div
                        style="color: #856404; background-color: #fff3cd; padding: 8px 12px; border-radius: 4px; margin-top: 8px; font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Este municipio no tiene datos disponibles en el ciclo escolar
                        2024-2025
                    </div>
                <?php endif; ?>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">

            <!-- Panel de resumen general -->
            <div id="resumen-general" class="matricula-panel animate-fade delay-1">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-users"></i> Resumen General de Matrícula</h3>
                    <div class="export-buttons">
                        <button id="export-btn" class="export-button">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="matricula-body">
                    <div class="stats-row">
                        <div class="stat-box total-general">
                            <div class="stat-value"><?php echo number_format($totales['general']); ?></div>
                            <div class="stat-label">Total de Alumnos</div>
                            <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                        </div>
                        <div class="stat-box sector-publico">
                            <div class="stat-value"><?php echo number_format($totales['publico']); ?></div>
                            <div class="stat-label">Sector Público</div>
                            <div class="stat-percentage">
                                <?php echo round(($totales['publico'] / $totales['general']) * 100, 1); ?>%
                            </div>
                            <div class="stat-icon"><i class="fas fa-university"></i></div>
                        </div>
                        <div class="stat-box sector-privado">
                            <div class="stat-value"><?php echo number_format($totales['privado']); ?></div>
                            <div class="stat-label">Sector Privado</div>
                            <div class="stat-percentage">
                                <?php echo round(($totales['privado'] / $totales['general']) * 100, 1); ?>%
                            </div>
                            <div class="stat-icon"><i class="fas fa-building"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Panel de tabla detallada -->
            <div id="desglose-sostenimiento" class="matricula-panel animate-fade delay-2">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-table"></i> Desglose por Sostenimiento</h3>
                </div>
                <div class="matricula-body">
                    <div class="table-container">
                        <table id="tabla-matricula" class="data-table">
                            <thead>
                                <tr>
                                    <th>Nivel Educativo</th>
                                    <th>Total</th>
                                    <th>Sector Público</th>
                                    <th>% Público</th>
                                    <th>Sector Privado</th>
                                    <th>% Privado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datosPorNivel as $nivel => $datos): ?>
                                    <?php
                                    $porcentajePublico = $datos['total'] > 0 ? round(($datos['publico'] / $datos['total']) * 100, 1) : 0;
                                    $porcentajePrivado = $datos['total'] > 0 ? round(($datos['privado'] / $datos['total']) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td class="nivel-nombre"><?php echo $nivel; ?></td>
                                        <td class="total-nivel"><?php echo number_format($datos['total']); ?></td>
                                        <td class="sector-publico"><?php echo number_format($datos['publico']); ?></td>
                                        <td class="porcentaje-publico"><?php echo $porcentajePublico; ?>%</td>
                                        <td class="sector-privado"><?php echo number_format($datos['privado']); ?></td>
                                        <td class="porcentaje-privado"><?php echo $porcentajePrivado; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td><strong>TOTAL GENERAL</strong></td>
                                    <td class="total-nivel">
                                        <strong><?php echo number_format($totales['general']); ?></strong>
                                    </td>
                                    <td class="sector-publico">
                                        <strong><?php echo number_format($totales['publico']); ?></strong>
                                    </td>
                                    <td class="porcentaje-publico">
                                        <strong><?php echo round(($totales['publico'] / $totales['general']) * 100, 1); ?>%</strong>
                                    </td>
                                    <td class="sector-privado">
                                        <strong><?php echo number_format($totales['privado']); ?></strong>
                                    </td>
                                    <td class="porcentaje-privado">
                                        <strong><?php echo round(($totales['privado'] / $totales['general']) * 100, 1); ?>%</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de análisis de tendencias (aislado de herencia global) -->
            <div id="analisis-nivel" class="matricula-panel animate-fade delay-3 panel-nivelaislado">
                <div class="header-nivelaislado">
                    <h3 class="title-nivelaislado"><i class="fas fa-chart-line"></i> Análisis por Nivel Educativo
                        (porcentaje del
                        total del municipio)</h3>
                    <div class="toggle-nivelaislado">
                        <button id="toggle-view" class="toggle-btn-nivelaislado" data-view="cards">
                            <i class="fas fa-chart-bar"></i> Ver Gráfico
                        </button>
                    </div>
                </div>
                <div class="body-nivelaislado">
                    <!-- Vista de tarjetas (por defecto) -->
                    <div id="cards-view" class="grid-nivelaislado">
                        <?php foreach ($datosPorNivel as $nivel => $datos): ?>
                            <?php
                            $porcentajePublico = $datos['total'] > 0 ? round(($datos['publico'] / $datos['total']) * 100, 1) : 0;
                            $dominante = $datos['publico'] > $datos['privado'] ? 'público' : 'privado';
                            $participacion = round(($datos['total'] / $totales['general']) * 100, 1);
                            ?>
                            <div class="card-nivelaislado">
                                <div class="header-card-nivelaislado">
                                    <h4><?php echo $nivel; ?></h4>
                                    <span class="participacion-nivelaislado"><?php echo $participacion; ?>% del total</span>
                                </div>
                                <div class="content-nivelaislado">
                                    <div class="sectorinfo-nivelaislado">
                                        <div class="sectordom-nivelaislado <?php echo $dominante; ?>">
                                            <span class="sectorlabel-nivelaislado">Sector dominante:</span>
                                            <span class="sectorvalue-nivelaislado"><?php echo ucfirst($dominante); ?></span>
                                        </div>
                                        <div class="sectorstats-nivelaislado">
                                            <div class="statmini-nivelaislado">
                                                <span
                                                    class="valuenivelaislado"><?php echo number_format($datos['publico']); ?></span>
                                                <span class="labelnivelaislado">Públicos</span>
                                            </div>
                                            <div class="statmini-nivelaislado">
                                                <span
                                                    class="valuenivelaislado"><?php echo number_format($datos['privado']); ?></span>
                                                <span class="labelnivelaislado">Privados</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progressbar-nivelaislado">
                                        <div class="progresspublico-nivelaislado"
                                            style="width: <?php echo $porcentajePublico; ?>%"></div>
                                        <div class="progressprivado-nivelaislado"
                                            style="width: <?php echo 100 - $porcentajePublico; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Vista de gráfico (oculta por defecto) -->
                    <div id="chart-view" class="chart-container" style="display: none;">
                        <div id="chart-comparativo" style="width:100%; height:500px;"></div>
                    </div>
                </div>
            </div>
            <!-- Panel de resumen general por género -->
            <div id="analisis-genero" class="matricula-panel animate-fade delay-2">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-venus-mars"></i> Resumen General por Género</h3>
                </div>
                <div class="matricula-body">
                    <div class="stats-row">
                        <?php
                        // Calcular totales de hombres y mujeres
                        $totalHombres = 0;
                        $totalMujeres = 0;
                        $totalGeneralGenero = 0;
                        foreach ($matriculaPorGenero as $fila) {
                            $totalHombres += $fila['hombres'];
                            $totalMujeres += $fila['mujeres'];
                            $totalGeneralGenero += $fila['total'];
                        }
                        ?>
                        <div class="stat-box total-general">
                            <div class="stat-value"><?php echo number_format($totalGeneralGenero); ?></div>
                            <div class="stat-label">Total de Alumnos</div>
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="stat-box sector-hombres">
                            <div class="stat-value"><?php echo number_format($totalHombres); ?>
                            </div>
                            <div class="stat-label">Total Hombres</div>
                            <div class="stat-percentage">
                                <?php echo $totalGeneralGenero > 0 ? round(($totalHombres / $totalGeneralGenero) * 100, 1) : 0; ?>%
                            </div>
                            <div class="stat-icon"><i class="fas fa-mars"></i></div>
                        </div>
                        <div class="stat-box sector-mujeres">
                            <div class="stat-value"><?php echo number_format($totalMujeres); ?>
                            </div>
                            <div class="stat-label">Total Mujeres</div>
                            <div class="stat-percentage">
                                <?php echo $totalGeneralGenero > 0 ? round(($totalMujeres / $totalGeneralGenero) * 100, 1) : 0; ?>%
                            </div>
                            <div class="stat-icon"><i class="fas fa-venus"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Panel de tabla detallada por género (diseño igual que Análisis por Nivel) -->
            <div class="matricula-panel animate-fade delay-4 matricula-genero">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-venus-mars"></i> Matrícula por Género</h3>
                </div>
                <div class="matricula-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nivel Educativo</th>
                                    <th>Total</th>
                                    <th><i class="fas fa-mars"></i> Hombres</th>
                                    <th>% Hombres</th>
                                    <th><i class="fas fa-venus"></i> Mujeres</th>
                                    <th>% Mujeres</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalHombres = 0;
                                $totalMujeres = 0;
                                $totalGeneralGenero = 0;
                                foreach ($matriculaPorGenero as $fila):
                                    $totalHombres += $fila['hombres'];
                                    $totalMujeres += $fila['mujeres'];
                                    $totalGeneralGenero += $fila['total'];
                                    $porcH = $fila['total'] > 0 ? round(($fila['hombres'] / $fila['total']) * 100, 1) : 0;
                                    $porcM = $fila['total'] > 0 ? round(($fila['mujeres'] / $fila['total']) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fila['titulo_fila']); ?></td>
                                        <td><?php echo number_format($fila['total']); ?></td>
                                        <td class="col-hombres"><?php echo number_format($fila['hombres']); ?></td>
                                        <td><?php echo $porcH; ?>%</td>
                                        <td class="col-mujeres"><?php echo number_format($fila['mujeres']); ?></td>
                                        <td><?php echo $porcM; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td><strong>TOTAL GENERAL</strong></td>
                                    <td><strong><?php echo number_format($totalGeneralGenero); ?></strong></td>
                                    <td class="col-hombres"><strong><?php echo number_format($totalHombres); ?></strong>
                                    </td>
                                    <td><strong><?php echo $totalGeneralGenero > 0 ? round(($totalHombres / $totalGeneralGenero) * 100, 1) : 0; ?>%</strong>
                                    </td>
                                    <td class="col-mujeres"><strong><?php echo number_format($totalMujeres); ?></strong>
                                    </td>
                                    <td><strong><?php echo $totalGeneralGenero > 0 ? round(($totalMujeres / $totalGeneralGenero) * 100, 1) : 0; ?>%</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de análisis de tendencias por género (tarjetas, diseño igual que Análisis por Nivel) -->
            <div class="matricula-panel animate-fade delay-5 panel-nivelaislado panel-genero">
                <div class="header-nivelaislado">
                    <h3 class="title-nivelaislado"><i class="fas fa-venus-mars"></i> Análisis por Nivel</h3>
                </div>
                <div class="body-nivelaislado">
                    <div id="cards-view-genero" class="grid-nivelaislado">
                        <?php foreach ($matriculaPorGenero as $fila): ?>
                            <?php
                            $porcH = $fila['total'] > 0 ? round(($fila['hombres'] / $fila['total']) * 100, 1) : 0;
                            $porcM = $fila['total'] > 0 ? round(($fila['mujeres'] / $fila['total']) * 100, 1) : 0;
                            $dominante = $fila['hombres'] > $fila['mujeres'] ? 'hombres' : 'mujeres';
                            $participacion = $totalGeneralGenero > 0 ? round(($fila['total'] / $totalGeneralGenero) * 100, 1) : 0;
                            ?>
                            <div class="card-nivelaislado">
                                <div class="header-card-nivelaislado">
                                    <h4><?php echo htmlspecialchars($fila['titulo_fila']); ?></h4>
                                    <span class="participacion-nivelaislado" style="color: var(--text-accent)">
                                        <?php echo $participacion; ?>% del total</span>
                                </div>
                                <div class="content-nivelaislado">
                                    <div class="sectorinfo-nivelaislado">
                                        <div class="sectordom-nivelaislado <?php echo $dominante; ?>"
                                            style="font-weight:bold;">
                                            <span class="sectorlabel-nivelaislado">Género dominante:</span>
                                            <span class="sectorvalue-nivelaislado"
                                                style="color:<?php echo $dominante == 'hombres' ? '#5b8df6' : '#f472b6'; ?>;">
                                                <?php echo ucfirst($dominante); ?>
                                            </span>
                                        </div>
                                        <div class="sectorstats-nivelaislado">
                                            <div class="statmini-nivelaislado">
                                                <span class="valuenivelaislado" style="color:#5b8df6;">
                                                    <?php echo number_format($fila['hombres']); ?> </span>
                                                <span class="labelnivelaislado">Hombres</span>
                                            </div>
                                            <div class="statmini-nivelaislado">
                                                <span class="valuenivelaislado" style="color:#f472b6;">
                                                    <?php echo number_format($fila['mujeres']); ?> </span>
                                                <span class="labelnivelaislado">Mujeres</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progressbar-nivelaislado"
                                        style="height: 18px; background: #ede9fe; border-radius: 8px; overflow: hidden;">
                                        <div class="progresspublico-nivelaislado"
                                            style="width: <?php echo $porcH; ?>%; background: #5b8df6; height: 100%; float:left;">
                                        </div>
                                        <div class="progressprivado-nivelaislado"
                                            style="width: <?php echo $porcM; ?>%; background: #f472b6; height: 100%; float:left;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tabla detallada por subnivel educativo -->
            <div id="tabla-detallada-alumnos" class="detailed-table animate-fade delay-6">
                <h4>Detalle por Subnivel Educativo</h4>
                <p class="note-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> El subnivel "General" contabiliza tanto alumnos de escuelas públicas como
                    privadas.
                </p>
                <!-- Filtro de búsqueda -->
                <div class="search-filter">
                    <input type="text" id="searchAlumnos" placeholder="Buscar por nivel o subnivel..."
                        class="search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nivel Educativo</th>
                                <th>Subnivel</th>
                                <th>Total Alumnos</th>
                                <th>% del Total General</th>
                                <th>Alumnos Hombres</th>
                                <th>% Hombres</th>
                                <th>Alumnas Mujeres</th>
                                <th>% Mujeres</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Reutilizar función de ordenamiento de docentes.php
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
                            for ($i = 1; $i < count($datosAlumnosGenero); $i++) {
                                $datosOrdenados[] = array(
                                    'nivel' => $datosAlumnosGenero[$i][0],
                                    'subnivel' => $datosAlumnosGenero[$i][1],
                                    'total' => $datosAlumnosGenero[$i][2],
                                    'hombres' => $datosAlumnosGenero[$i][3],
                                    'mujeres' => $datosAlumnosGenero[$i][4],
                                    'porcentaje_hombres' => $datosAlumnosGenero[$i][5],
                                    'porcentaje_mujeres' => $datosAlumnosGenero[$i][6],
                                    'orden' => obtenerOrdenSubnivel($datosAlumnosGenero[$i][0], $datosAlumnosGenero[$i][1])
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
                                $porcentajeDelTotal = $totalAlumnosSubnivel > 0 ? round(($totalNivel / $totalAlumnosSubnivel) * 100, 2) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($subnivel, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-center"><?php echo number_format($totalNivel); ?></td>
                                    <td class="text-center"><?php echo $porcentajeDelTotal; ?>%</td>
                                    <td class="text-center alumnos-hombres"><?php echo number_format($hombres); ?></td>
                                    <td class="text-center porcentaje-hombres"><?php echo $porcentajeHombres; ?>%</td>
                                    <td class="text-center alumnos-mujeres"><?php echo number_format($mujeres); ?></td>
                                    <td class="text-center porcentaje-mujeres"><?php echo $porcentajeMujeres; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                                <td class="text-center">
                                    <strong><?php echo number_format($totalAlumnosSubnivel); ?></strong>
                                </td>
                                <td class="text-center"><strong>100.0%</strong></td>
                                <?php
                                // Calcular totales de género
                                $totalHombresSubnivel = 0;
                                $totalMujeresSubnivel = 0;
                                for ($i = 1; $i < count($datosAlumnosGenero); $i++) {
                                    $totalHombresSubnivel += $datosAlumnosGenero[$i][3];
                                    $totalMujeresSubnivel += $datosAlumnosGenero[$i][4];
                                }
                                $porcentajeTotalHombres = $totalAlumnosSubnivel > 0 ? round(($totalHombresSubnivel / $totalAlumnosSubnivel) * 100, 1) : 0;
                                $porcentajeTotalMujeres = $totalAlumnosSubnivel > 0 ? round(($totalMujeresSubnivel / $totalAlumnosSubnivel) * 100, 1) : 0;
                                ?>
                                <td class="text-center alumnos-hombres">
                                    <strong><?php echo number_format($totalHombresSubnivel); ?></strong>
                                </td>
                                <td class="text-center porcentaje-hombres">
                                    <strong><?php echo $porcentajeTotalHombres . '%'; ?></strong>
                                </td>
                                <td class="text-center alumnos-mujeres">
                                    <strong><?php echo number_format($totalMujeresSubnivel); ?></strong>
                                </td>
                                <td class="text-center porcentaje-mujeres">
                                    <strong><?php echo $porcentajeTotalMujeres . '%'; ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
        <!-- BARRERAS DE APRENDIZAJE -->


        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
        </footer>
    </div>

    <script>
        // Datos para los gráficos
        const datosGrafico = <?php echo json_encode($datosGrafico); ?>;
        const datosDistribucion = <?php echo json_encode($datosDistribucion); ?>;
        const totales = <?php echo json_encode($totales); ?>;
    </script>
    <script src="./js/script.js"></script>
    <script src="./js/alumnos.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>

</body>

</html>