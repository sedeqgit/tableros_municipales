<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Incluir archivo de conexión actualizado
require_once 'conexion_prueba_2024.php';

// Obtener el municipio desde el parámetro GET, por defecto Querétaro (municipio con más datos)
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'QUERÉTARO';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'QUERÉTARO'; // Fallback a Querétaro si el municipio no es válido
}

// Obtener datos completos del municipio
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Obtener datos de USAER
$datosUSAER = obtenerDatosUSAER($municipioSeleccionado);

// Verificar si hay datos
$hayError = !$datosCompletosMunicipio;
$tieneDatos = $datosCompletosMunicipio &&
    isset($datosCompletosMunicipio['total_matricula']) &&
    $datosCompletosMunicipio['total_matricula'] > 0;

// Inicializar variables por defecto
$totalEscuelas = 0;
$totalAlumnos = 0;
$totalDocentes = 0;
$datosEducativos = [['Tipo Educativo', 'Escuelas', 'Alumnos']];
$datosDocentes = [['Nivel Educativo', 'Subnivel', 'Docentes']];

if ($tieneDatos) {
    // Extraer totales de manera segura y convertir a enteros
    $totalEscuelas = isset($datosCompletosMunicipio['total_escuelas']) ? (int) $datosCompletosMunicipio['total_escuelas'] : 0;
    $totalAlumnos = isset($datosCompletosMunicipio['total_matricula']) ? (int) $datosCompletosMunicipio['total_matricula'] : 0;
    $totalDocentes = isset($datosCompletosMunicipio['total_docentes']) ? (int) $datosCompletosMunicipio['total_docentes'] : 0;

    // Convertir datos al formato que espera el frontend (compatible con conexion.php)
    $datosEducativos = [
        ['Tipo Educativo', 'Escuelas', 'Alumnos']
    ];

    // Función auxiliar para obtener datos de manera segura y convertir a números
    $obtenerDatoSeguro = function ($datos, $nivel, $campo, $default = 0) {
        $valor = isset($datos[$nivel][$campo]) ? $datos[$nivel][$campo] : $default;
        return is_numeric($valor) ? intval($valor) : $default;
    };

    // Solo agregar niveles que tengan datos
    $inicialEscMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_esc', 'tot_mat');
    if ($inicialEscMat > 0) {
        $datosEducativos[] = [
            'Inicial (Escolarizado)',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_esc', 'tot_esc'),
            $inicialEscMat
        ];
    }

    $inicialNoEscMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_no_esc', 'tot_mat');
    if ($inicialNoEscMat > 0) {
        $datosEducativos[] = [
            'Inicial (No Escolarizado)',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_no_esc', 'tot_esc'),
            $inicialNoEscMat
        ];
    }

    $especialMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'especial', 'tot_mat');
    if ($especialMat > 0) {
        $datosEducativos[] = [
            'Especial CAM',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'especial', 'tot_esc'),
            $especialMat
        ];
    }

    // Agregar USAER inmediatamente después de CAM si hay datos disponibles (datos informativos, no se suman en totales)
    if ($datosUSAER && isset($datosUSAER['tot_mat']) && $datosUSAER['tot_mat'] > 0) {
        $datosEducativos[] = [
            'Especial USAER',
            isset($datosUSAER['tot_esc']) ? (int) $datosUSAER['tot_esc'] : 0,
            (int) $datosUSAER['tot_mat']
        ];
    }

    $preescolarMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'preescolar', 'tot_mat');
    if ($preescolarMat > 0) {
        $datosEducativos[] = [
            'Preescolar',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'preescolar', 'tot_esc'),
            $preescolarMat
        ];
    }

    $primariaMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'primaria', 'tot_mat');
    if ($primariaMat > 0) {
        $datosEducativos[] = [
            'Primaria',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'primaria', 'tot_esc'),
            $primariaMat
        ];
    }

    $secundariaMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'secundaria', 'tot_mat');
    if ($secundariaMat > 0) {
        $datosEducativos[] = [
            'Secundaria',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'secundaria', 'tot_esc'),
            $secundariaMat
        ];
    }

    $mediaSupMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'media_sup', 'tot_mat');
    if ($mediaSupMat > 0) {
        $datosEducativos[] = [
            'Media Superior',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'media_sup', 'tot_esc'),
            $mediaSupMat
        ];
    }

    $superiorMat = $obtenerDatoSeguro($datosCompletosMunicipio, 'superior', 'tot_mat');
    if ($superiorMat > 0) {
        $datosEducativos[] = [
            'Superior',
            $obtenerDatoSeguro($datosCompletosMunicipio, 'superior', 'tot_esc'),
            $superiorMat
        ];
    }

    // Convertir datos de docentes al formato esperado
    $datosDocentes = [
        ['Nivel Educativo', 'Subnivel', 'Docentes']
    ];

    // Solo agregar niveles que tengan docentes
    $inicialEscDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_esc', 'tot_doc');
    if ($inicialEscDoc > 0) {
        $datosDocentes[] = ['Inicial Escolarizada', 'General', $inicialEscDoc];
    }

    $inicialNoEscDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'inicial_no_esc', 'tot_doc');
    if ($inicialNoEscDoc > 0) {
        $datosDocentes[] = ['Inicial No Escolarizada', 'Comunitario', $inicialNoEscDoc];
    }

    $especialDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'especial', 'tot_doc');
    if ($especialDoc > 0) {
        $datosDocentes[] = ['Especial', 'CAM', $especialDoc];
    }

    // Agregar USAER inmediatamente después de CAM si hay datos disponibles
    if ($datosUSAER && isset($datosUSAER['tot_doc']) && $datosUSAER['tot_doc'] > 0) {
        $datosDocentes[] = [
            'Especial',
            'USAER',
            (int) $datosUSAER['tot_doc']
        ];
    }

    $preescolarDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'preescolar', 'tot_doc');
    if ($preescolarDoc > 0) {
        $datosDocentes[] = ['Preescolar', 'General', $preescolarDoc];
    }

    $primariaDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'primaria', 'tot_doc');
    if ($primariaDoc > 0) {
        $datosDocentes[] = ['Primaria', 'General', $primariaDoc];
    }

    $secundariaDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'secundaria', 'tot_doc');
    if ($secundariaDoc > 0) {
        $datosDocentes[] = ['Secundaria', 'General', $secundariaDoc];
    }

    $mediaSupDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'media_sup', 'tot_doc');
    if ($mediaSupDoc > 0) {
        $datosDocentes[] = ['Media Superior', 'Plantel', $mediaSupDoc];
    }

    $superiorDoc = $obtenerDatoSeguro($datosCompletosMunicipio, 'superior', 'tot_doc');
    if ($superiorDoc > 0) {
        $datosDocentes[] = ['Superior', 'Licenciatura', $superiorDoc];
    }
}

// Funciones de compatibilidad para que el resto del código funcione igual
function calcularTotales($datosEducativos)
{
    return [
        'escuelas' => $GLOBALS['totalEscuelas'],
        'alumnos' => $GLOBALS['totalAlumnos']
    ];
}

function calcularTotalesDocentes($datosDocentes)
{
    return [
        'total' => $GLOBALS['totalDocentes']
    ];
}

/**
 * Formatea nombres de municipios para display en formato título
 */
function formatearNombreMunicipio($municipio)
{
    // Convertir de mayúsculas a formato título
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');

    // Correcciones específicas para preposiciones y artículos
    $formatted = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $formatted);

    return $formatted;
}

/**
 * Formatea porcentajes con un número fijo de decimales.
 */
function formatPercent($value, $decimals = 2)
{
    return number_format((float) $value, $decimals, '.', '');
}

// Variables de compatibilidad
$totales = calcularTotales($datosEducativos);
$totalesDocentes = calcularTotalesDocentes($datosDocentes);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> (Estadística Educativa)- Ciclo
        <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?> | SEDEQ
    </title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">

    <!-- ========================================== -->
    <!-- HOJAS DE ESTILO MODULARIZADAS             -->
    <!-- ========================================== -->
    <!-- Estilos globales compartidos por todo el sistema -->
    <link rel="stylesheet" href="./css/global.css">
    <!-- Estilos específicos para la página de resumen -->
    <link rel="stylesheet" href="./css/resumen.css">
    <!-- Estilos para el menú lateral responsive -->
    <link rel="stylesheet" href="./css/sidebar.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bibliotecas para exportación -->
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
            <div class="sidebar-link-with-submenu">
                <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                    class="sidebar-link active has-submenu">
                    <i class="fas fa-chart-bar"></i>
                    <span>Resumen</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu active">
                    <a href="#resumen-ejecutivo" class="submenu-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Resumen Ejecutivo</span>
                    </a>
                    <a href="#desglose-detallado" class="submenu-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Desglose Detallado por Nivel o Tipo Educativo</span>
                    </a>
                    <a href="#publico-privado" class="submenu-link">
                        <i class="fas fa-balance-scale"></i>
                        <span>Desglose Detallado por Nivel o Tipo Educativo</span>
                    </a>
                    <a href="#desglose-sexo" class="submenu-link">
                        <i class="fas fa-user-graduate"></i>
                        <span>Desglose de Matrícula por Sexo y Sostenimiento</span>
                    </a>
                    <a href="#totales-municipales" class="submenu-link">
                        <i class="fas fa-percentage"></i>
                        <span>Porcentaje por Nivel o Tipo Educativo de los Totales del Municipio</span>
                    </a>
                    <a href="#usaer-section" class="submenu-link">
                        <i class="fas fa-user-graduate"></i>
                        <span>USAER</span>
                    </a>
                </div>
            </div>
            <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-user-graduate"></i><span>Matrícula</span></a>
            <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                class="sidebar-link"><i class="fas fa-school"></i> <span>Escuelas</span></a>
            <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-chalkboard-teacher"></i>
                <span>Docentes</span></a>
            <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-map-marked-alt"></i>
                <span>Mapas</span></a>
        </div>
        <!--  <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
            </a>
        </div>-->
    </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title">
                <h1 class="section-title">Tablero
                    <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> (Estadística Educativa) - Ciclo
                    <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>
                </h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <!-- Sección de Resumen Ejecutivo -->
        <div id="resumen-ejecutivo" class="resumen-ejecutivo-section">
            <h2 class="resumen-ejecutivo-title">
                <i class=""></i> Resumen Ejecutivo
            </h2>
        </div>

        <div class="dashboard-grid">
            <div class="card analysis-card animate-fade delay-3">
                <div class="card-header">
                    <h2 class="panel-title"><i class="fas fa-table"></i> Datos por Tipo o Nivel Educativo <i
                            class="fas fa-info-circle info-icon"
                            data-tooltip="Los datos de matrícula y escuelas de los servicios USAER no se suman ya que se cuentan en los niveles correspondientes"></i>
                    </h2>
                    <!--                     <div class="card-actions">
                        <button id="exportExcel" class="action-button" title="Exportar a Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                        <button id="exportPDF" class="action-button" title="Exportar a PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>-->
                </div>
                <div class="card-body table-container">
                    <table class="data-table animate-up delay-7" id="dataTable">
                        <thead>
                            <tr>
                                <th>Tipo o Nivel Educativo</th>
                                <th>Escuelas</th>
                                <th>Matrícula</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            <!-- Los datos se cargarán dinámicamente desde script.js -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card chart-card animate-fade delay-4">
                <div class="card-header">
                    <h2 class="panel-title"><i class="fas fa-chart-bar"></i> Estadística por Tipo o Nivel Educativo</h2>
                    <div class="export-buttons">
                        <button id="export-chart-btn" class="export-button" title="Exportar gráfico">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chart_div" class="animate-scale delay-5"></div>
                </div>
            </div>

            <div class="card summary-card animate-fade">
                <div class="card-header">
                    <h2 class="panel-title"><i class="fas fa-info-circle"></i> Resumen Ejecutivo</h2>
                </div>
                <div class="card-body">
                    <div class="metric animate-left delay-2">
                        <div class="metric-icon growth">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="metric-details">
                            <h3 class="metric-title">Total Matrícula <i class="fas fa-info-circle info-icon"
                                    data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                            </h3>
                            <p class="metric-value" id="metricDecline">
                                <?php echo number_format($totalAlumnos, 0, '.', ','); ?>
                            </p>
                            <p class="metric-change" id="metricDeclineChange">Ciclo escolar 2024-2025</p>
                        </div>
                    </div>
                    <div class="metric animate-left delay-1">
                        <div class="metric-icon decline">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="metric-details">


                            <h3 class="metric-title">Total Escuelas <i class="fas fa-info-circle info-icon"
                                    data-tooltip="1. En el total de Escuelas de Media Superior se cuantifican planteles y en Superior se cuantifican instituciones
                                2. El total de Escuelas de Superior en el Estado no corresponde a la suma de escuelas en los municipios, debido a que en algunos casos sólo se registra la institución en la capital del Estado y no se desglosan las unidades académicas en los municipios donde se imparten estudios
                                3. Los datos de escuela de los servicios USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                            </h3>
                            <p class="metric-value" id="metricGrowth">
                                <?php echo number_format($totalEscuelas, 0, '.', ','); ?>
                            </p>
                            <p class="metric-change" id="metricGrowthChange">Ciclo escolar 2024-2025</p>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-icon investment">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="metric-details">
                            <h3 class="metric-title">Total Docentes <i class="fas fa-info-circle info-icon"
                                    data-tooltip="1. El total de docentes de Superior en el
                                        estado no corresponde a la suma de
                                        docentes en los municipios, debido a que
                                        en algunos casos sólo se registra la
                                        institución en la capital del Estado y no se
                                        desglosan las unidades académicas en los
                                        municipios donde se imparten estudios"></i>
                            </h3>
                            <p class="metric-value"><?php echo number_format($totalDocentes, 0, '.', ','); ?></p>
                            <p class="metric-change">Ciclo escolar 2024-2025</p>
                        </div>
                    </div>


                </div>
            </div>
            <div class="card controls-card animate-right delay-5">
                <div class="card-header">
                    <h2 class="panel-title"><i class="fas fa-sliders-h"></i> Ajustes de Visualización de Gráfica</h2>
                </div>
                <div class="card-body">
                    <div class="control-group animate-fade">
                        <label class="slider-label">
                            <i class="fas fa-eye"></i> Mostrar:
                        </label>
                        <div class="control-options">
                            <label class="radio-container">
                                <input type="radio" name="visualizacion" value="escuelas" checked> Solo Escuelas
                            </label>
                            <label class="radio-container">
                                <input type="radio" name="visualizacion" value="alumnos"> Solo Matrícula
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

        </div>

        <!-- Sección de Datos Detallados por Categoría -->
        <div id="desglose-detallado" class="datos-section-title">
            <h2>Desglose Detallado por Nivel o Tipo Educativo</h2>
            <p>Distribución específica de escuelas, alumnos y docentes según el nivel o tipo educativo</p>
            <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
        </div>

        <!-- Grid de tarjetas de datos detallados -->
        <div class="datos-grid">
            <?php if ($tieneDatos): ?>
                <!-- Tarjeta de Escuelas -->
                <div class="datos-card escuelas-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <h3 class="card-title">Escuelas <i class="fas fa-info-circle info-icon"
                                data-tooltip="1. En el total de Escuelas de Media Superior se cuantifican planteles y en Superior se cuantifican instituciones
                                2. El total de Escuelas de Superior en el Estado no corresponde a la suma de escuelas en los municipios, debido a que en algunos casos sólo se registra la institución en la capital del Estado y no se desglosan las unidades académicas en los municipios donde se imparten estudios
                                3. Los datos de escuela de los servicios USAER no se suman ya que se encuentran en los niveles correspondientes"></i>

                        </h3>
                    </div>
                    <div class="card-subtitle">No incluye USAER</div>
                    <div class="total-general">
                        <?php echo number_format($totalEscuelas, 0, '.', ','); ?>
                    </div>
                    <div class="detalles-niveles">
                        <?php
                        // Mostrar detalles por nivel educativo para escuelas
                        $nivelesEscuelas = [
                            'inicial_esc' => 'Inicial (Escolarizada)',
                            'inicial_no_esc' => 'Inicial (No Escolarizada)',
                            'especial' => 'Especial (CAM)',
                            'preescolar' => 'Preescolar',
                            'primaria' => 'Primaria',
                            'secundaria' => 'Secundaria',
                            'media_sup' => 'Media Superior',
                            'superior' => 'Superior'
                        ];

                        foreach ($nivelesEscuelas as $nivel => $nombre) {
                            $cantidad = $obtenerDatoSeguro($datosCompletosMunicipio, $nivel, 'tot_esc');
                            if ($cantidad > 0) {
                                echo "<div class='detalle-nivel'>";
                                echo "<span class='nivel-nombre'>" . htmlspecialchars($nombre) . "</span>";
                                echo "<span class='nivel-cantidad'>" . number_format($cantidad, 0, '.', ',') . "</span>";
                                echo "</div>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Tarjeta de Alumnos -->
                <div class="datos-card alumnos-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="card-title">Matrícula <i class="fas fa-info-circle info-icon"
                                data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>

                        </h3>
                    </div>
                    <div class="card-subtitle">No incluye USAER</div>
                    <div class="total-general">
                        <?php echo number_format($totalAlumnos, 0, '.', ','); ?>
                    </div>
                    <div class="detalles-niveles">
                        <?php
                        // Mostrar detalles por nivel educativo para alumnos
                        $nivelesAlumnos = [
                            'inicial_esc' => 'Inicial (Escolarizada)',
                            'inicial_no_esc' => 'Inicial (No Escolarizada)',
                            'especial' => 'Especial (CAM)',
                            'preescolar' => 'Preescolar',
                            'primaria' => 'Primaria',
                            'secundaria' => 'Secundaria',
                            'media_sup' => 'Media Superior',
                            'superior' => 'Superior'
                        ];

                        foreach ($nivelesAlumnos as $nivel => $nombre) {
                            $cantidad = $obtenerDatoSeguro($datosCompletosMunicipio, $nivel, 'tot_mat');
                            if ($cantidad > 0) {
                                echo "<div class='detalle-nivel'>";
                                echo "<span class='nivel-nombre'>" . htmlspecialchars($nombre) . "</span>";
                                echo "<span class='nivel-cantidad'>" . number_format($cantidad, 0, '.', ',') . "</span>";
                                echo "</div>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Tarjeta de Docentes -->
                <div class="datos-card docentes-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3 class="card-title">Docentes <i class="fas fa-info-circle info-icon" data-tooltip="1. El total de docentes de Superior en el
                                        estado no corresponde a la suma de
                                        docentes en los municipios, debido a que
                                        en algunos casos sólo se registra la
                                        institución en la capital del Estado y no se
                                        desglosan las unidades académicas en los
                                        municipios donde se imparten estudios"></i>

                        </h3>
                    </div>
                    <div class="card-subtitle">Incluye todos los niveles educativos</div>
                    <div class="total-general">
                        <?php echo number_format($totalDocentes, 0, '.', ','); ?>
                    </div>
                    <div class="detalles-niveles">
                        <?php
                        // Mostrar detalles por nivel educativo para docentes
                        $nivelesDocentes = [
                            'inicial_esc' => 'Inicial (Escolarizada)',
                            'inicial_no_esc' => 'Inicial (No Escolarizada)',
                            'especial' => 'Especial (CAM)',
                            'preescolar' => 'Preescolar',
                            'primaria' => 'Primaria',
                            'secundaria' => 'Secundaria',
                            'media_sup' => 'Media Superior',
                            'superior' => 'Superior'
                        ];

                        foreach ($nivelesDocentes as $nivel => $nombre) {
                            $cantidad = $obtenerDatoSeguro($datosCompletosMunicipio, $nivel, 'tot_doc');
                            if ($cantidad > 0) {
                                echo "<div class='detalle-nivel'>";
                                echo "<span class='nivel-nombre'>" . htmlspecialchars($nombre) . "</span>";
                                echo "<span class='nivel-cantidad'>" . number_format($cantidad, 0, '.', ',') . "</span>";
                                echo "</div>";
                            }

                            // Agregar USAER inmediatamente después de Especial (CAM)
                            if ($nivel === 'especial' && $datosUSAER && isset($datosUSAER['tot_doc']) && $datosUSAER['tot_doc'] > 0) {
                                echo "<div class='detalle-nivel'>";
                                echo "<span class='nivel-nombre'>Especial (USAER)</span>";
                                echo "<span class='nivel-cantidad'>" . number_format($datosUSAER['tot_doc'], 0, '.', ',') . "</span>";
                                echo "</div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Mensaje cuando no hay datos -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <div
                        style="background-color: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107;">
                        <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 10px;"></i>
                        <h3>No hay datos disponibles</h3>
                        <p>No se encontraron datos para el municipio de
                            <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> en el ciclo escolar actual.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!--  -->
        <div id="publico-privado" class="datos-section-title">
            <h2>
                <i class="fas fa-chart-pie"></i> Desglose Detallado por Nivel y Tipo de Sostenimiento
            </h2>
            <div>
                <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
            </div>

            <?php
            // Obtener datos con desglose público/privado
            $datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

            // Obtener datos de USAER
            $datosUSAER = obtenerDatosUSAER($municipioSeleccionado);
            ?>

            <?php if (!empty($datosPublicoPrivado)): ?>
                <div class="datos-grid">
                    <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                        <div class="publico-privado-card">
                            <div class="publico-privado-header">
                                <div class="card-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <h3 class="card-title">
                                    <?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                            </div>

                            <!-- Totales -->
                            <div class="totales-generales">
                                <div class="total-escuelas">
                                    Total: <?php echo number_format($datos['tot_esc'], 0, '.', ','); ?> escuelas <i
                                        class="fas fa-info-circle info-icon"
                                        data-tooltip="1. En el total de Escuelas de Media Superior se cuantifican planteles y en Superior se cuantifican instituciones
                                2. El total de Escuelas de Superior en el Estado no corresponde a la suma de escuelas en los municipios, debido a que en algunos casos sólo se registra la institución en la capital del Estado y no se desglosan las unidades académicas en los municipios donde se imparten estudios
                                3. Los datos de escuela de los servicios USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                </div>
                                <div class="total-secundarios">
                                    <?php echo number_format($datos['tot_mat'], 0, '.', ','); ?> matrícula <i
                                        class="fas fa-info-circle info-icon"
                                        data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                    |
                                    <?php echo number_format($datos['tot_doc'], 0, '.', ','); ?> docentes <i
                                        class="fas fa-info-circle info-icon" data-tooltip="1. El total de docentes de Superior en el
                                        estado no corresponde a la suma de
                                        docentes en los municipios, debido a que
                                        en algunos casos sólo se registra la
                                        institución en la capital del Estado y no se
                                        desglosan las unidades académicas en los
                                        municipios donde se imparten estudios"></i>

                                </div>
                            </div>

                            <!-- Desglose Público/Privado -->
                            <div class="publico-privado-grid">
                                <!-- Públicas -->
                                <div class="publico-card">
                                    <h4>
                                        <i class="fas fa-university"></i> Público
                                    </h4>
                                    <div class="detalle-stack">
                                        <div class="detalle-item">
                                            <span class="detalle-label">Escuelas</span>
                                            <div class="detalle-values">
                                                <span class="detalle-porcentaje">
                                                    <?php echo formatPercent($datos['tot_esc'] > 0 ? ($datos['tot_esc_pub'] / $datos['tot_esc']) * 100 : 0); ?>%
                                                </span>
                                                <span
                                                    class="detalle-numero"><?php echo number_format($datos['tot_esc_pub'], 0, '.', ','); ?></span>
                                            </div>
                                        </div>
                                        <div class="detalle-item">
                                            <span class="detalle-label">Matrícula</span>
                                            <div class="detalle-values">
                                                <span class="detalle-porcentaje">
                                                    <?php echo formatPercent($datos['tot_mat'] > 0 ? ($datos['tot_mat_pub'] / $datos['tot_mat']) * 100 : 0); ?>%
                                                </span>
                                                <span
                                                    class="detalle-numero"><?php echo number_format($datos['tot_mat_pub'], 0, '.', ','); ?></span>
                                            </div>
                                        </div>
                                        <div class="detalle-item">
                                            <span class="detalle-label">Docentes</span>
                                            <div class="detalle-values">
                                                <span class="detalle-porcentaje">
                                                    <?php echo formatPercent($datos['tot_doc'] > 0 ? ($datos['tot_doc_pub'] / $datos['tot_doc']) * 100 : 0); ?>%
                                                </span>
                                                <span
                                                    class="detalle-numero"><?php echo number_format($datos['tot_doc_pub'], 0, '.', ','); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Privadas -->
                                <div class="privado-card">
                                    <h4>
                                        <i class="fas fa-building"></i> Privado
                                    </h4>
                                    <div class="detalle-stack">
                                        <div class="detalle-item">
                                            <span class="detalle-label">Escuelas</span>
                                            <div class="detalle-values">
                                                <span class="detalle-porcentaje">
                                                    <?php echo formatPercent($datos['tot_esc'] > 0 ? ($datos['tot_esc_priv'] / $datos['tot_esc']) * 100 : 0); ?>%
                                                </span>
                                                <span
                                                    class="detalle-numero"><?php echo number_format($datos['tot_esc_priv'], 0, '.', ','); ?></span>
                                            </div>
                                        </div>
                                        <div class="detalle-item">
                                            <span class="detalle-label">Matrícula</span>
                                            <div class="detalle-values">
                                                <span class="detalle-porcentaje">
                                                    <?php echo formatPercent($datos['tot_mat'] > 0 ? ($datos['tot_mat_priv'] / $datos['tot_mat']) * 100 : 0); ?>%
                                                </span>
                                                <span
                                                    class="detalle-numero"><?php echo number_format($datos['tot_mat_priv'], 0, '.', ','); ?></span>
                                            </div>
                                        </div>
                                        <div class="detalle-item">
                                            <span class="detalle-label">Docentes</span>
                                            <div class="detalle-values">
                                                <span class="detalle-porcentaje">
                                                    <?php echo formatPercent($datos['tot_doc'] > 0 ? ($datos['tot_doc_priv'] / $datos['tot_doc']) * 100 : 0); ?>%
                                                </span>
                                                <span
                                                    class="detalle-numero"><?php echo number_format($datos['tot_doc_priv'], 0, '.', ','); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="publico-privado-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>No hay datos disponibles</h3>
                    <p>No se pudieron obtener datos de desglose público/privado para el municipio de
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección de Desglose de Alumnos por Sexo -->
        <div class="datos-section-title" id="desglose-sexo">
            <h2 class="desglose-sexo-title">
                <i class="fas fa-user-graduate"></i> Desglose de Matrícula por Sexo y Tipo de Sostenimiento
            </h2>
            <div>
                <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
            </div>


            <?php if (!empty($datosPublicoPrivado)): ?>
                <div class="datos-grid">
                    <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                        <?php
                        // Calcular total de alumnos del nivel
                        $totalAlumnosNivel = $datos['tot_mat'];

                        // Solo mostrar si hay alumnos
                        if ($totalAlumnosNivel > 0):
                            ?>
                            <div class="desglose-sexo-card">
                                <div class="card-header">
                                    <div class="card-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h3 class="card-title">
                                        <?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?> <i
                                            class="fas fa-info-circle info-icon"
                                            data-tooltip="1. Los datos de matrícula de los servicios de USAER no se suman ya que se encuentran en los niveles correspondientes"></i>
                                    </h3>
                                </div>

                                <!-- Total de Alumnos -->
                                <div class="total-alumnos">
                                    <div class="numero-total">
                                        Matrícula Total: <?php echo number_format($totalAlumnosNivel, 0, '.', ','); ?>
                                    </div>
                                </div>

                                <!-- Desglose Hombres/Mujeres -->
                                <div class="sexo-grid">
                                    <!-- Hombres -->
                                    <div class="hombres-card">
                                        <h4>
                                            <i class="fas fa-mars"></i> Hombres
                                        </h4>
                                        <div class="detalle-stack">
                                            <div class="detalle-item">
                                                <span class="detalle-label">Total</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($totalAlumnosNivel > 0 ? ($datos['mat_h'] / $totalAlumnosNivel) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['mat_h'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Público</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['mat_h'] > 0 ? ($datos['mat_h_pub'] / $datos['mat_h']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['mat_h_pub'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Privado</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['mat_h'] > 0 ? ($datos['mat_h_priv'] / $datos['mat_h']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['mat_h_priv'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mujeres -->
                                    <div class="mujeres-card">
                                        <h4>
                                            <i class="fas fa-venus"></i> Mujeres
                                        </h4>
                                        <div class="detalle-stack">
                                            <div class="detalle-item">
                                                <span class="detalle-label">Total</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($totalAlumnosNivel > 0 ? ($datos['mat_m'] / $totalAlumnosNivel) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['mat_m'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Público</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['mat_m'] > 0 ? ($datos['mat_m_pub'] / $datos['mat_m']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['mat_m_pub'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                            <div class="detalle-item">
                                                <span class="detalle-label">Privado</span>
                                                <div class="detalle-values">
                                                    <span class="detalle-porcentaje">
                                                        <?php echo formatPercent($datos['mat_m'] > 0 ? ($datos['mat_m_priv'] / $datos['mat_m']) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="detalle-numero"><?php echo number_format($datos['mat_m_priv'], 0, '.', ','); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="desglose-sexo-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>No hay datos disponibles</h3>
                    <p>No se pudieron obtener datos de desglose por sexo para el municipio de
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección de Totales Municipales por Nivel -->
        <div id="totales-municipales" class="datos-section-title">
            <h2 class="totales-municipales-title">
                <i class="fas fa-percentage"></i> Proporción de los totales por Nivel o Tipo Educativo de los Totales
                del
                Municipio
            </h2>
            <div>
                <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
            </div>


            <?php if ($tieneDatos && !empty($datosCompletosMunicipio)): ?>
                <div class="totales-municipales-container">
                    <!-- Resumen de Totales Municipales -->
                    <div class="totales-resumen">
                        <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                            <i class="fas fa-chart-bar"></i> Resumen General del Municipio
                        </h3>
                        <div class="totales-generales-grid">
                            <div class="total-municipal-card">
                                <div class="total-icono">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div class="total-contenido">
                                    <span class="total-tipo">Total Escuelas</span>
                                    <span
                                        class="total-valor"><?php echo number_format($totalEscuelas, 0, '.', ','); ?></span>
                                    <span class="total-subtitulo">escuelas</span>
                                </div>
                            </div>
                            <div class="total-municipal-card">
                                <div class="total-icono">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="total-contenido">
                                    <span class="total-tipo">Total Matrícula</span>
                                    <span
                                        class="total-valor"><?php echo number_format($totalAlumnos, 0, '.', ','); ?></span>
                                    <span class="total-subtitulo">alumnos</span>
                                </div>
                            </div>

                            <div class="total-municipal-card">
                                <div class="total-icono">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="total-contenido">
                                    <span class="total-tipo">Total Docentes</span>
                                    <span
                                        class="total-valor"><?php echo number_format($totalDocentes, 0, '.', ','); ?></span>
                                    <span class="total-subtitulo">profesores</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desglose por Nivel Educativo con Porcentajes Municipales -->
                    <?php if (!empty($datosPublicoPrivado)): ?>
                        <div class="porcentajes-niveles-detalle">
                            <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                <i class="fas fa-chart-line"></i> Distribución por Nivel Educativo
                            </h3>
                            <div class="niveles-municipales-grid">
                                <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                                    <?php if ($datos['tot_mat'] > 0): ?>
                                        <div class="nivel-municipal-card">
                                            <div class="nivel-header">
                                                <h4><?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                            </div>
                                            <div class="nivel-totales-detalle">
                                                <div class="total-item-detalle">
                                                    <span class="total-label">Matrícula:</span>
                                                    <span
                                                        class="porcentaje-municipal"><?php echo formatPercent($totalAlumnos > 0 ? ($datos['tot_mat'] / $totalAlumnos) * 100 : 0); ?>%

                                                    </span>
                                                    <span
                                                        class="total-numero"><?php echo number_format($datos['tot_mat'], 0, '.', ','); ?>

                                                    </span>
                                                </div>
                                                <div class="total-item-detalle">
                                                    <span class="total-label">Escuelas:</span>
                                                    <span
                                                        class="porcentaje-municipal"><?php echo formatPercent($totalEscuelas > 0 ? ($datos['tot_esc'] / $totalEscuelas) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="total-numero"><?php echo number_format($datos['tot_esc'], 0, '.', ','); ?>

                                                    </span>
                                                </div>
                                                <div class="total-item-detalle">
                                                    <span class="total-label">Docentes:</span>
                                                    <span
                                                        class="porcentaje-municipal"><?php echo formatPercent($totalDocentes > 0 ? ($datos['tot_doc'] / $totalDocentes) * 100 : 0); ?>%
                                                    </span>
                                                    <span
                                                        class="total-numero"><?php echo number_format($datos['tot_doc'], 0, '.', ','); ?>

                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="totales-municipales-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>No hay datos disponibles</h3>
                    <p>No se pudieron obtener datos totales para el municipio de
                        <?php echo formatearNombreMunicipio($municipioSeleccionado); ?>.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección de USAER (Unidad de Servicios de Apoyo a la Educación Regular) -->
        <?php if ($datosUSAER): ?>
            <div id="usaer-section" class="datos-section-title">
                <h2 class="usaer-title">
                    <i class="fas fa-hands-helping"></i> USAER - Unidad de Servicios de Apoyo a la Educación Regular
                </h2>
                <div>
                    <p><?php echo formatearNombreMunicipio($municipioSeleccionado); ?></p>
                </div>
                <p class="usaer-subtitle">
                    Datos informativos de las Unidades de Servicios de Apoyo a la Educación Regular.
                    Estos datos no se suman en los totales municipales ya que atienden a alumnos contabilizados en los
                    niveles correspondientes.
                </p>

                <div class="usaer-container">
                    <!-- Resumen General de USAER (estilo similar a Totales Municipales) -->
                    <div class="usaer-resumen">
                        <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                            <i class="fas fa-chart-bar"></i> Resumen General USAER
                        </h3>
                        <div class="totales-generales-grid">
                            <div class="total-municipal-card">
                                <div class="total-icono">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div class="total-contenido">
                                    <span class="total-tipo">Total Unidades USAER </span>
                                    <span
                                        class="total-valor"><?php echo number_format($datosUSAER['tot_esc'], 0, '.', ','); ?></span>
                                    <span class="total-subtitulo">unidades </span>
                                </div>
                            </div>
                            <div class="total-municipal-card">
                                <div class="total-icono">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="total-contenido">
                                    <span class="total-tipo">Total Matrícula Atendida</span>
                                    <span
                                        class="total-valor"><?php echo number_format($datosUSAER['tot_mat'], 0, '.', ','); ?></span>
                                    <span class="total-subtitulo">alumnos</span>
                                </div>
                            </div>
                            <div class="total-municipal-card">
                                <div class="total-icono">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="total-contenido">
                                    <span class="total-tipo">Total Personal</span>
                                    <span
                                        class="total-valor"><?php echo number_format($datosUSAER['tot_doc'], 0, '.', ','); ?></span>
                                    <span class="total-subtitulo">personal</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desglose detallado por sostenimiento y sexo (estilo similar a Desglose por Sexo) -->
                    <div class="usaer-desglose-detallado">
                        <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                            <i class="fas fa-chart-line"></i> Desglose Detallado por Sostenimiento
                        </h3>

                        <!-- Desglose Público vs Privado -->
                        <div class="usaer-sostenimiento-grid">
                            <!-- Públicas -->
                            <div class="usaer-sostenimiento-card publico-card">
                                <h4>
                                    <i class="fas fa-university"></i> Público
                                </h4>
                                <div class="usaer-dato-grupo">
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['tot_esc_pub'], 0, '.', ','); ?> unidades
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_esc'] > 0 ? ($datosUSAER['tot_esc_pub'] / $datosUSAER['tot_esc']) * 100 : 0); ?>%
                                    </div>
                                </div>
                                <div class="usaer-dato-grupo">
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['tot_mat_pub'], 0, '.', ','); ?> Matrícula
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['tot_mat_pub'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                    </div>
                                </div>
                                <div class="usaer-dato-grupo">
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['tot_doc_pub'], 0, '.', ','); ?> personal
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['tot_doc_pub'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                    </div>
                                </div>
                            </div>

                            <!-- Privadas -->
                            <div class="usaer-sostenimiento-card privado-card">
                                <h4>
                                    <i class="fas fa-building"></i> Privado
                                </h4>
                                <div class="usaer-dato-grupo">
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['tot_esc_priv'], 0, '.', ','); ?> unidades
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_esc'] > 0 ? ($datosUSAER['tot_esc_priv'] / $datosUSAER['tot_esc']) * 100 : 0); ?>%
                                    </div>
                                </div>
                                <div class="usaer-dato-grupo">
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['tot_mat_priv'], 0, '.', ','); ?> matrícula
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['tot_mat_priv'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                    </div>
                                </div>
                                <div class="usaer-dato-grupo">
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['tot_doc_priv'], 0, '.', ','); ?> personal
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['tot_doc_priv'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desglose por Sexo -->
                        <div class="usaer-sexo-section">
                            <h4 style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
                                <i class="fas fa-venus-mars"></i> Distribución de Matrícula por Sexo y Sostenimiento
                            </h4>
                            <div class="sexo-grid">
                                <!-- Hombres -->
                                <div class="hombres-card">
                                    <h4>
                                        <i class="fas fa-mars"></i> Hombres
                                    </h4>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['mat_h'], 0, '.', ','); ?> Total
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['mat_h'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['mat_h_pub'], 0, '.', ','); ?> Público
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['mat_h'] > 0 ? ($datosUSAER['mat_h_pub'] / $datosUSAER['mat_h']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['mat_h_priv'], 0, '.', ','); ?> Privado
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['mat_h'] > 0 ? ($datosUSAER['mat_h_priv'] / $datosUSAER['mat_h']) * 100 : 0); ?>%
                                    </div>
                                </div>

                                <!-- Mujeres -->
                                <div class="mujeres-card">
                                    <h4>
                                        <i class="fas fa-venus"></i> Mujeres
                                    </h4>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['mat_m'], 0, '.', ','); ?> Total
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_mat'] > 0 ? ($datosUSAER['mat_m'] / $datosUSAER['tot_mat']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['mat_m_pub'], 0, '.', ','); ?> Público
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['mat_m'] > 0 ? ($datosUSAER['mat_m_pub'] / $datosUSAER['mat_m']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['mat_m_priv'], 0, '.', ','); ?> Privado
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['mat_m'] > 0 ? ($datosUSAER['mat_m_priv'] / $datosUSAER['mat_m']) * 100 : 0); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desglose de Personal por Sexo -->
                        <div class="usaer-personal-section">
                            <h4 style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
                                <i class="fas fa-user-tie"></i> Distribución de Personal por Sexo y Sostenimiento
                            </h4>
                            <div class="sexo-grid">
                                <!-- Hombres -->
                                <div class="hombres-card">
                                    <h4>
                                        <i class="fas fa-mars"></i> Hombres
                                    </h4>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['doc_h'], 0, '.', ','); ?> Total
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['doc_h'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['doc_h_pub'], 0, '.', ','); ?> Público
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['doc_h'] > 0 ? ($datosUSAER['doc_h_pub'] / $datosUSAER['doc_h']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['doc_h_priv'], 0, '.', ','); ?> Privado
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['doc_h'] > 0 ? ($datosUSAER['doc_h_priv'] / $datosUSAER['doc_h']) * 100 : 0); ?>%
                                    </div>
                                </div>

                                <!-- Mujeres -->
                                <div class="mujeres-card">
                                    <h4>
                                        <i class="fas fa-venus"></i> Mujeres
                                    </h4>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['doc_m'], 0, '.', ','); ?> Total
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['tot_doc'] > 0 ? ($datosUSAER['doc_m'] / $datosUSAER['tot_doc']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['doc_m_pub'], 0, '.', ','); ?> Público
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['doc_m'] > 0 ? ($datosUSAER['doc_m_pub'] / $datosUSAER['doc_m']) * 100 : 0); ?>%
                                    </div>
                                    <div class="numero-principal">
                                        <?php echo number_format($datosUSAER['doc_m_priv'], 0, '.', ','); ?> Privado
                                    </div>
                                    <div class="porcentaje">
                                        <?php echo formatPercent($datosUSAER['doc_m'] > 0 ? ($datosUSAER['doc_m_priv'] / $datosUSAER['doc_m']) * 100 : 0); ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Poder Ejecutivo del Estado de Querétaro - Todos
                los derechos
                reservados</p>
        </footer>
    </div>

    <!-- Modal de selección de formato de exportación -->
    <div id="exportChartModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-download"></i> Exportar Gráfico</h3>
                <button class="modal-close" id="closeExportModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-message">
                    Selecciona el formato de exportación:
                </p>
                <div class="export-options"
                    style="display: flex; gap: 15px; margin-top: 20px; justify-content: center;">
                    <button class="btn-export-option" id="exportPNGBtn"
                        style="flex: 1; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fas fa-image" style="font-size: 24px;"></i>
                        <span>PNG (Imagen)</span>
                    </button>
                    <button class="btn-export-option" id="exportExcelChartBtn"
                        style="flex: 1; padding: 15px; background: #2196F3; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fas fa-file-excel" style="font-size: 24px;"></i>
                        <span>Excel</span>
                    </button>
                    <button class="btn-export-option" id="exportPDFChartBtn"
                        style="flex: 1; padding: 15px; background: #FF5722; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fas fa-file-pdf" style="font-size: 24px;"></i>
                        <span>PDF</span>
                    </button>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" id="cancelExportBtn">Cancelar</button>
            </div>
        </div>
    </div> <!-- Script con datos desde PHP -->
    <script>
        <?php
        // Debugging: Mostrar municipio seleccionado
        echo "console.log('Municipio seleccionado: " . $municipioSeleccionado . "');\n";
        echo "console.log('Tiene datos: " . ($tieneDatos ? 'true' : 'false') . "');\n";
        echo "console.log('Total escuelas: " . $totalEscuelas . "');\n";
        echo "console.log('Total alumnos: " . $totalAlumnos . "');\n";
        echo "console.log('Total docentes: " . $totalDocentes . "');\n";

        // Asegurar que tenemos al menos la estructura básica
        if (count($datosEducativos) <= 1) {
            // Si no hay datos, crear datos por defecto
            $datosEducativos = [
                ['Tipo Educativo', 'Escuelas', 'Alumnos'],
                ['Sin datos', 0, 0]
            ];
        }

        // Convertir a formato JSON para usar en JavaScript
        echo "const datosEducativos = " . json_encode($datosEducativos, JSON_NUMERIC_CHECK) . ";\n";
        echo "const totalEscuelas = " . $totalEscuelas . ";\n";
        echo "const totalAlumnos = " . $totalAlumnos . ";\n";
        echo "const totalDocentes = " . $totalDocentes . ";\n";
        echo "const totalEscuelasFormateado = '" . number_format($totalEscuelas, 0, '.', ',') . "';\n";
        echo "const totalAlumnosFormateado = '" . number_format($totalAlumnos, 0, '.', ',') . "';\n";
        echo "const totalDocentesFormateado = '" . number_format($totalDocentes, 0, '.', ',') . "';\n";
        ?>

    </script>

    <!-- Script para manejar el modal de exportación -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar tooltips personalizados
            if (typeof initCustomTooltips === 'function') {
                initCustomTooltips();
            }

            // Manejo del modal de exportación de gráfico
            const exportChartButton = document.getElementById('export-chart-btn');
            const exportChartModal = document.getElementById('exportChartModal');
            const closeExportModal = document.getElementById('closeExportModal');
            const cancelExportBtn = document.getElementById('cancelExportBtn');

            // Botones de exportación
            const exportPNGBtn = document.getElementById('exportPNGBtn');
            const exportExcelChartBtn = document.getElementById('exportExcelChartBtn');
            const exportPDFChartBtn = document.getElementById('exportPDFChartBtn');

            // Mostrar modal de exportación
            if (exportChartButton) {
                exportChartButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    exportChartModal.classList.add('show');
                });
            }

            // Cerrar modal
            function closeExportModalFunction() {
                exportChartModal.classList.remove('show');
            }

            if (closeExportModal) {
                closeExportModal.addEventListener('click', closeExportModalFunction);
            }

            if (cancelExportBtn) {
                cancelExportBtn.addEventListener('click', closeExportModalFunction);
            }

            // Exportar como PNG
            if (exportPNGBtn) {
                exportPNGBtn.addEventListener('click', function () {
                    closeExportModalFunction();
                    exportarGraficoComoImagen();
                });
            }

            // Exportar como Excel
            if (exportExcelChartBtn) {
                exportExcelChartBtn.addEventListener('click', function () {
                    closeExportModalFunction();
                    exportarGraficoExcel();
                });
            }

            // Exportar como PDF
            if (exportPDFChartBtn) {
                exportPDFChartBtn.addEventListener('click', function () {
                    closeExportModalFunction();
                    exportarGraficoPDF();
                });
            }

            // Cerrar modal al hacer clic fuera del contenido
            exportChartModal.addEventListener('click', function (e) {
                if (e.target === exportChartModal) {
                    closeExportModalFunction();
                }
            });

            // Cerrar modal con tecla ESC
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && exportChartModal.classList.contains('show')) {
                    closeExportModalFunction();
                }
            });
        });
    </script> <!-- Script del dashboard -->
    <script src="./js/export-utils.js"></script>
    <script src="./js/script.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
</body>

</html>