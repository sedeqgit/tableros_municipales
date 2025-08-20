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

// Incluir módulo de conexión a base de datos con funciones de consulta
require_once 'conexion.php';

// =============================================================================
// PROCESAMIENTO DE DATOS DE MATRÍCULA
// =============================================================================

// Obtener datos consolidados de matrícula por nivel educativo
$datosMatricula = obtenerMatriculaConsolidadaPorNivel();

// Obtener desglose por género (matrícula por nivel y género)
$matriculaPorGenero = obtenerMatriculaPorNivelYGenero();

// Obtener datos de alumnos con discapacidad por nivel y género
$alumnosDiscapacidad = obtenerAlumnosDiscapacidadPorNivelYGenero();

// Extraer datos organizados
$datosPorNivel = $datosMatricula['datos_por_nivel'];
$totales = $datosMatricula['totales'];
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
            <a href="resumen.php" class="sidebar-link"><i class="fas fa-chart-bar"></i><span>Resumen</span></a>
            <a href="escuelas_detalle.php" class="sidebar-link"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <a href="#" class="sidebar-link active"><i class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="docentes.php" class="sidebar-link"><i
                    class="fas fa-chalkboard-teacher"></i><span>Docentes</span></a>
            <!--<a href="estudiantes.php" class="sidebar-link"><i class="fas fa-history"></i> <span>Históricos</span></a>
            <a href="historicos.php" class="sidebar-link"><i class="fas fa-history"></i> <span>Demo
                    Históricos</span></a>-->

        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Matrícula Estudiantil por Nivel Educativo (En proceso)</h1>
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
            <div class="matricula-panel animate-fade delay-1">
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
            <div class="matricula-panel animate-fade delay-2">
                <div class="matricula-header">
                    <h3 class="matricula-title"><i class="fas fa-table"></i> Desglose por Sostenimiento</h3>
                </div>
                <div class="matricula-body">
                    <div class="table-container">
                        <table id="tabla-matricula" class="data-table">
                            <thead>
                                <tr>
                                    <th>Nivel Educativo</th>
                                    <th>Sector Público</th>
                                    <th>Sector Privado</th>
                                    <th>Total</th>
                                    <th>% Público</th>
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
                                        <td class="sector-publico"><?php echo number_format($datos['publico']); ?></td>
                                        <td class="sector-privado"><?php echo number_format($datos['privado']); ?></td>
                                        <td class="total-nivel"><?php echo number_format($datos['total']); ?></td>
                                        <td class="porcentaje-publico"><?php echo $porcentajePublico; ?>%</td>
                                        <td class="porcentaje-privado"><?php echo $porcentajePrivado; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td><strong>TOTAL GENERAL</strong></td>
                                    <td class="sector-publico">
                                        <strong><?php echo number_format($totales['publico']); ?></strong>
                                    </td>
                                    <td class="sector-privado">
                                        <strong><?php echo number_format($totales['privado']); ?></strong>
                                    </td>
                                    <td class="total-nivel">
                                        <strong><?php echo number_format($totales['general']); ?></strong>
                                    </td>
                                    <td class="porcentaje-publico">
                                        <strong><?php echo round(($totales['publico'] / $totales['general']) * 100, 1); ?>%</strong>
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
            <div class="matricula-panel animate-fade delay-3 panel-nivelaislado">
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
            <div class="matricula-panel animate-fade delay-2">
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
                                    <th><i class="fas fa-mars"></i> Hombres</th>
                                    <th><i class="fas fa-venus"></i> Mujeres</th>
                                    <th>Total</th>
                                    <th>% Hombres</th>
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
                                        <td><?php echo htmlspecialchars($fila['nivel']); ?></td>
                                        <td class="col-hombres"><?php echo number_format($fila['hombres']); ?></td>
                                        <td class="col-mujeres"><?php echo number_format($fila['mujeres']); ?></td>
                                        <td><?php echo number_format($fila['total']); ?></td>
                                        <td><?php echo $porcH; ?>%</td>
                                        <td><?php echo $porcM; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td><strong>TOTAL GENERAL</strong></td>
                                    <td class="col-hombres"><strong><?php echo number_format($totalHombres); ?></strong>
                                    </td>
                                    <td class="col-mujeres"><strong><?php echo number_format($totalMujeres); ?></strong>
                                    </td>
                                    <td><strong><?php echo number_format($totalGeneralGenero); ?></strong></td>
                                    <td><strong><?php echo $totalGeneralGenero > 0 ? round(($totalHombres / $totalGeneralGenero) * 100, 1) : 0; ?>%</strong>
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
                                    <h4><?php echo htmlspecialchars($fila['nivel']); ?></h4>
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
        </div>

        <!-- Panel de resumen general de alumnos con discapacidad -->
        <div class="matricula-panel animate-fade delay-6">
            <div class="matricula-header">
                <h3 class="matricula-title"><i class="fas fa-wheelchair"></i> Resumen General de Alumnos con
                    Barreras de Aprendizaje (datos demo)</h3>
            </div>
            <div class="matricula-body">
                <div class="stats-row">
                    <?php
                    // Calcular totales de alumnos con discapacidad
                    $totalHombresDiscapacidad = 0;
                    $totalMujeresDiscapacidad = 0;
                    $totalGeneralDiscapacidad = 0;
                    foreach ($alumnosDiscapacidad as $fila) {
                        $totalHombresDiscapacidad += $fila['hombres'];
                        $totalMujeresDiscapacidad += $fila['mujeres'];
                        $totalGeneralDiscapacidad += $fila['total'];
                    }
                    ?>
                    <div class="stat-box total-general">
                        <div class="stat-value"><?php echo number_format($totalGeneralDiscapacidad); ?></div>
                        <div class="stat-label">Total con Discapacidad</div>
                        <div class="stat-icon"><i class="fas fa-wheelchair"></i></div>
                    </div>
                    <div class="stat-box sector-hombres">
                        <div class="stat-value"><?php echo number_format($totalHombresDiscapacidad); ?></div>
                        <div class="stat-label">Hombres</div>
                        <div class="stat-percentage">
                            <?php echo $totalGeneralDiscapacidad > 0 ? round(($totalHombresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%
                        </div>
                        <div class="stat-icon"><i class="fas fa-mars"></i></div>
                    </div>
                    <div class="stat-box sector-mujeres">
                        <div class="stat-value"><?php echo number_format($totalMujeresDiscapacidad); ?></div>
                        <div class="stat-label">Mujeres</div>
                        <div class="stat-percentage">
                            <?php echo $totalGeneralDiscapacidad > 0 ? round(($totalMujeresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%
                        </div>
                        <div class="stat-icon"><i class="fas fa-venus"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de tabla detallada de alumnos con discapacidad por género -->
        <div class="matricula-panel animate-fade delay-7 matricula-genero">
            <div class="matricula-header">
                <h3 class="matricula-title"><i class="fas fa-wheelchair"></i> Alumnos con Barreras de Aprendizaje por
                    Nivel
                    Educativo (datos demo)</h3>
            </div>
            <div class="matricula-body">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nivel Educativo</th>
                                <th><i class="fas fa-mars"></i> Hombres</th>
                                <th><i class="fas fa-venus"></i> Mujeres</th>
                                <th>Total</th>
                                <th>% Hombres</th>
                                <th>% Mujeres</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalHombresDiscapacidad = 0;
                            $totalMujeresDiscapacidad = 0;
                            $totalGeneralDiscapacidad = 0;
                            foreach ($alumnosDiscapacidad as $fila):
                                $totalHombresDiscapacidad += $fila['hombres'];
                                $totalMujeresDiscapacidad += $fila['mujeres'];
                                $totalGeneralDiscapacidad += $fila['total'];
                                $porcH = $fila['total'] > 0 ? round(($fila['hombres'] / $fila['total']) * 100, 1) : 0;
                                $porcM = $fila['total'] > 0 ? round(($fila['mujeres'] / $fila['total']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fila['nivel']); ?></td>
                                    <td class="col-hombres"><?php echo number_format($fila['hombres']); ?></td>
                                    <td class="col-mujeres"><?php echo number_format($fila['mujeres']); ?></td>
                                    <td><?php echo number_format($fila['total']); ?></td>
                                    <td><?php echo $porcH; ?>%</td>
                                    <td><?php echo $porcM; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td><strong>TOTAL GENERAL</strong></td>
                                <td class="col-hombres">
                                    <strong><?php echo number_format($totalHombresDiscapacidad); ?></strong>
                                </td>
                                <td class="col-mujeres">
                                    <strong><?php echo number_format($totalMujeresDiscapacidad); ?></strong>
                                </td>
                                <td><strong><?php echo number_format($totalGeneralDiscapacidad); ?></strong></td>
                                <td><strong><?php echo $totalGeneralDiscapacidad > 0 ? round(($totalHombresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%</strong>
                                </td>
                                <td><strong><?php echo $totalGeneralDiscapacidad > 0 ? round(($totalMujeresDiscapacidad / $totalGeneralDiscapacidad) * 100, 1) : 0; ?>%</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel de análisis de tendencias de discapacidad por nivel educativo -->
        <div class="matricula-panel animate-fade delay-8 panel-nivelaislado panel-discapacidad">
            <div class="header-nivelaislado">
                <h3 class="title-nivelaislado"><i class="fas fa-wheelchair"></i> Análisis de Barreras de Aprendizaje por
                    Nivel
                    Educativo (datos demo)</h3>
            </div>
            <div class="body-nivelaislado">
                <div id="cards-view-discapacidad" class="grid-nivelaislado">
                    <?php foreach ($alumnosDiscapacidad as $fila): ?>
                        <?php
                        $porcH = $fila['total'] > 0 ? round(($fila['hombres'] / $fila['total']) * 100, 1) : 0;
                        $porcM = $fila['total'] > 0 ? round(($fila['mujeres'] / $fila['total']) * 100, 1) : 0;
                        $dominante = $fila['hombres'] > $fila['mujeres'] ? 'hombres' : 'mujeres';
                        $participacion = $totalGeneralDiscapacidad > 0 ? round(($fila['total'] / $totalGeneralDiscapacidad) * 100, 1) : 0;
                        ?>
                        <div class="card-nivelaislado">
                            <div class="header-card-nivelaislado">
                                <h4><?php echo htmlspecialchars($fila['nivel']); ?></h4>
                                <span class="participacion-nivelaislado" style="color: var(--accent-orange);">
                                    <?php echo $participacion; ?>% del total con barreras del aprendizaje
                                </span>
                            </div>
                            <div class="content-nivelaislado">
                                <div class="sectorinfo-nivelaislado">
                                    <div class="sectordom-nivelaislado <?php echo $dominante; ?>" style="font-weight:bold;">
                                        <span class="sectorlabel-nivelaislado">Género dominante:</span>
                                        <span class="sectorvalue-nivelaislado"
                                            style="color:<?php echo $dominante == 'hombres' ? '#5b8df6' : '#f472b6'; ?>;">
                                            <?php echo ucfirst($dominante); ?>
                                        </span>
                                    </div>
                                    <div class="sectorstats-nivelaislado">
                                        <div class="statmini-nivelaislado">
                                            <span class="valuenivelaislado" style="color:#5b8df6;">
                                                <?php echo number_format($fila['hombres']); ?>
                                            </span>
                                            <span class="labelnivelaislado">Hombres</span>
                                        </div>
                                        <div class="statmini-nivelaislado">
                                            <span class="valuenivelaislado" style="color:#f472b6;">
                                                <?php echo number_format($fila['mujeres']); ?>
                                            </span>
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