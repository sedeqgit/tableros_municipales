<?php
/**
 * =============================================================================
 * PÁGINA DE DIRECTORIO DE ESCUELAS - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Esta página presenta el directorio completo de instituciones educativas
 * por municipio y nivel educativo en el estado de Querétaro.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Directorio completo de escuelas por municipio
 * - Filtros por nivel educativo (Inicial, Preescolar, Primaria, etc.)
 * - Información detallada: CCT, nombre, localidad, alumnos, control
 * - Exportación de directorios en múltiples formatos
 * - Ajustes especiales para nivel Superior en Querétaro
 * 
 * COMPONENTES ANALÍTICOS:
 * - Distribución por nivel educativo
 * - Análisis de escuelas públicas vs privadas
 * - Totales de alumnos por institución
 * - Notas explicativas para ajustes especiales
 * 
 * VISUALIZACIONES:
 * - Tablas interactivas y ordenables
 * - Filtros dinámicos por nivel
 * - Búsqueda en tiempo real
 * - Paginación de resultados
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Directorio_Escuelas
 * @version 3.0
 */

// =============================================================================
// CONFIGURACIÓN E INICIALIZACIÓN DEL SISTEMA
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
// OBTENCIÓN DE PARÁMETROS Y VALIDACIÓN
// =============================================================================

// Incluir módulo de conexión actualizado con funciones dinámicas
require_once 'conexion_prueba_2024.php';

// Obtener el municipio desde el parámetro GET, por defecto Querétaro
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'QUERÉTARO';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'QUERÉTARO'; // Fallback a Querétaro si el municipio no es válido
}

// =============================================================================
// OBTENCIÓN Y PROCESAMIENTO DE DATOS EDUCATIVOS
// =============================================================================

// Obtener datos completos del municipio usando funciones dinámicas
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Obtener datos de público/privado y distribución por subcontrol
$datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

// Verificar si hay datos
$hayError = !$datosCompletosMunicipio;
$tieneDatos = $datosCompletosMunicipio &&
    isset($datosCompletosMunicipio['total_matricula']) &&
    $datosCompletosMunicipio['total_matricula'] > 0;

// =============================================================================
// CÁLCULOS ESTADÍSTICOS GENERALES
// =============================================================================

// Extraer totales de manera segura y convertir a enteros
$totalEscuelas = $tieneDatos ? (int) $datosCompletosMunicipio['total_escuelas'] : 0;
$totalAlumnos = $tieneDatos ? (int) $datosCompletosMunicipio['total_matricula'] : 0;
$totalDocentes = $tieneDatos ? (int) $datosCompletosMunicipio['total_docentes'] : 0;

// Calcular totales de escuelas públicas y privadas
$escuelasPublicas = 0;
$escuelasPrivadas = 0;

if ($datosPublicoPrivado && !empty($datosPublicoPrivado)) {
    foreach ($datosPublicoPrivado as $nivel => $datos) {
        if (isset($datos['tot_esc_pub'])) {
            $escuelasPublicas += (int) $datos['tot_esc_pub'];
        }
        if (isset($datos['tot_esc_priv'])) {
            $escuelasPrivadas += (int) $datos['tot_esc_priv'];
        }
    }
}

// Calcular porcentajes
$porcentajePublicas = $totalEscuelas > 0 ? round(($escuelasPublicas / $totalEscuelas) * 100, 1) : 0;
$porcentajePrivadas = $totalEscuelas > 0 ? round(($escuelasPrivadas / $totalEscuelas) * 100, 1) : 0;

// Crear estructura de escuelas por nivel y sostenimiento para JavaScript
$escuelasNivelSostenimiento = [];

if ($datosPublicoPrivado && !empty($datosPublicoPrivado)) {
    // Mapeo de claves del backend a nombres visuales
    $mapeoNiveles = [
        'inicial_esc' => 'Inicial (Escolarizado)',
        'inicial_no_esc' => 'Inicial (No Escolarizado)',
        'especial_tot' => 'Especial (CAM)',
        'preescolar' => 'Preescolar',
        'primaria' => 'Primaria',
        'secundaria' => 'Secundaria',
        'media_sup' => 'Media Superior',
        'superior' => 'Superior'
    ];

    foreach ($datosPublicoPrivado as $clave => $datos) {
        if (isset($mapeoNiveles[$clave])) {
            $nombreNivel = $mapeoNiveles[$clave];
            $escuelasNivelSostenimiento[$nombreNivel] = [
                'publicas' => isset($datos['tot_esc_pub']) ? (int) $datos['tot_esc_pub'] : 0,
                'privadas' => isset($datos['tot_esc_priv']) ? (int) $datos['tot_esc_priv'] : 0,
                'total' => isset($datos['tot_esc']) ? (int) $datos['tot_esc'] : 0
            ];
        }
    }
}

// =============================================================================
// PROCESAMIENTO DE DATOS POR NIVEL EDUCATIVO
// =============================================================================

// Extraer y estructurar datos por nivel educativo
$escuelasPorNivel = [];
$alumnosPorNivel = [];

if ($tieneDatos) {
    $niveles = [
        'Inicial (Escolarizado)' => 'inicial_esc',
        'Inicial (No Escolarizado)' => 'inicial_no_esc',
        'Preescolar' => 'preescolar',
        'Primaria' => 'primaria',
        'Secundaria' => 'secundaria',
        'Media Superior' => 'media_sup',
        'Superior' => 'superior',
        'Especial' => 'especial'
    ];

    foreach ($niveles as $nombreNivel => $clave) {
        if (isset($datosCompletosMunicipio[$clave])) {
            $datos = $datosCompletosMunicipio[$clave];
            $escuelasPorNivel[$nombreNivel] = isset($datos['tot_esc']) ? (int) $datos['tot_esc'] : 0;
            $alumnosPorNivel[$nombreNivel] = isset($datos['tot_mat']) ? (int) $datos['tot_mat'] : 0;
        } else {
            $escuelasPorNivel[$nombreNivel] = 0;
            $alumnosPorNivel[$nombreNivel] = 0;
        }
    }
}

// Mapear nivel Especial a Especial (CAM) para visualización
if (isset($escuelasPorNivel['Especial'])) {
    $escuelasPorNivel['Especial (CAM)'] = $escuelasPorNivel['Especial'];
    $alumnosPorNivel['Especial (CAM)'] = $alumnosPorNivel['Especial'];
    // Mantener también el valor en 'Especial' para compatibilidad
} else {
    $escuelasPorNivel['Especial (CAM)'] = 0;
    $alumnosPorNivel['Especial (CAM)'] = 0;
}

// Calcular distribución porcentual
$porcentajes = [];
if ($totalEscuelas > 0) {
    foreach ($escuelasPorNivel as $nivel => $cantidad) {
        $porcentajes[$nivel] = round(($cantidad / $totalEscuelas) * 100, 1);
    }
} else {
    foreach ($escuelasPorNivel as $nivel => $cantidad) {
        $porcentajes[$nivel] = 0;
    }
}

// =============================================================================
// MAPEO DE NIVELES PARA DIRECTORIO
// =============================================================================

// Definir los niveles educativos disponibles para el directorio
$nivelesDisponibles = [
    'inicial_esc' => 'Inicial Escolarizada',
    'inicial_no_esc' => 'Inicial No Escolarizada',
    'especial_tot' => 'Especial (CAM)',
    'preescolar' => 'Preescolar',
    'primaria' => 'Primaria',
    'secundaria' => 'Secundaria',
    'media_sup' => 'Media Superior',
    'superior' => 'Superior'
];

// =============================================================================
// CARGAR DIRECTORIOS DE ESCUELAS POR NIVEL
// =============================================================================

// Inicializar array para almacenar directorios por nivel
$directoriosPorNivel = [];
$notasEspeciales = [];

// Cargar directorio para cada nivel educativo
foreach ($nivelesDisponibles as $codigoNivel => $nombreNivel) {
    $directorio = obtenerDirectorioEscuelas($municipioSeleccionado, $codigoNivel);

    if ($directorio && isset($directorio['escuelas'])) {
        $directoriosPorNivel[$codigoNivel] = $directorio;

        // Almacenar nota especial si existe (para Superior en Querétaro)
        if (isset($directorio['tiene_ajuste_unidades']) && $directorio['tiene_ajuste_unidades']) {
            $notasEspeciales[$codigoNivel] = [
                'total_ajustado' => $directorio['total_alumnos_ajustado'],
                'total_sin_ajuste' => $directorio['total_alumnos_sin_ajuste'],
                'ajuste' => $directorio['ajuste_unidades'],
                'nota' => $directorio['nota_explicativa']
            ];
        }
    }
}

// Separar escuelas públicas y privadas para cada nivel
$escuelasPublicasPorNivel = [];
$escuelasPrivadasPorNivel = [];

// Inicializar contadores por subcontrol
$conteoSubcontrol = [];

foreach ($directoriosPorNivel as $nivel => $datos) {
    $escuelasPublicasPorNivel[$nivel] = [];
    $escuelasPrivadasPorNivel[$nivel] = [];

    foreach ($datos['escuelas'] as $escuela) {
        $control = strtoupper($escuela['tipo_control']);

        // Contar por subcontrol
        if (!isset($conteoSubcontrol[$control])) {
            $conteoSubcontrol[$control] = 0;
        }
        $conteoSubcontrol[$control]++;

        if ($control === 'PRIVADO') {
            $escuelasPrivadasPorNivel[$nivel][] = $escuela;
        } else {
            $escuelasPublicasPorNivel[$nivel][] = $escuela;
        }
    }

    // Ordenar escuelas dentro de cada nivel por número de alumnos (descendente)
    usort($escuelasPublicasPorNivel[$nivel], function ($a, $b) {
        return $b['total_alumnos'] - $a['total_alumnos'];
    });

    usort($escuelasPrivadasPorNivel[$nivel], function ($a, $b) {
        return $b['total_alumnos'] - $a['total_alumnos'];
    });
}

// Construir distribución por subcontrol con porcentajes
$distribucionSubcontrol = [];
foreach ($conteoSubcontrol as $subcontrol => $total) {
    $distribucionSubcontrol[$subcontrol] = [
        'total' => $total,
        'porcentaje' => $totalEscuelas > 0 ? round(($total / $totalEscuelas) * 100, 1) : 0
    ];
}

// Asegurar que existan todas las categorías necesarias
$subcontrolesEsperados = ['PRIVADO', 'FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUTÓNOMO'];
foreach ($subcontrolesEsperados as $subcontrol) {
    if (!isset($distribucionSubcontrol[$subcontrol])) {
        $distribucionSubcontrol[$subcontrol] = [
            'total' => 0,
            'porcentaje' => 0
        ];
    }
}

// =============================================================================
// VARIABLES ADICIONALES PARA JAVASCRIPT
// =============================================================================

// Variable de eficiencia (puede implementarse en el futuro)
$datosEficiencia = [
    'promedio_alumnos_escuela' => $totalEscuelas > 0 ? round($totalAlumnos / $totalEscuelas, 1) : 0,
    'promedio_alumnos_docente' => $totalDocentes > 0 ? round($totalAlumnos / $totalDocentes, 1) : 0
];

// Variable para datos educativos (usada por otros scripts)
$datosEducativos = $datosCompletosMunicipio;

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
            <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-chart-bar"></i><span>Resumen</span></a>
            <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <div class="sidebar-link-with-submenu">
                <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                    class="sidebar-link active has-submenu">
                    <i class="fas fa-school"></i>
                    <span>Escuelas</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu active">
                    <a href="#resumen-escuelas" class="submenu-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Resumen General</span>
                    </a>
                    <a href="#subcontrol-educativo" class="submenu-link">
                        <i class="fas fa-building"></i>
                        <span>Subcontrol Educativo</span>
                    </a>
                    <a href="#directorio-publicas" class="submenu-link">
                        <i class="fas fa-landmark"></i>
                        <span>Escuelas Públicas</span>
                    </a>
                    <a href="#directorio-privadas" class="submenu-link">
                        <i class="fas fa-building"></i>
                        <span>Escuelas Privadas</span>
                    </a>
                </div>
            </div>
            <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link"><i
                    class="fas fa-chalkboard-teacher"></i>
                <span>Docentes</span></a>
        </div>
    </div>
    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Detalle de Escuelas Ciclo 2024 - 2025 </h1>
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
            <div id="resumen-escuelas" class="panel animate-up">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-school"></i> Resumen de Escuelas en
                        <?php echo $municipioSeleccionado; ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="stats-row">
                        <div class="stat-box animate-fade delay-1">
                            <div class="stat-value"><?php echo $totalEscuelas; ?> </div>
                            <div class="stat-label">Total Escuelas Ciclo escolar 2024-2025</div>
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
                        <div class="nivel-header">
                            <h4>Distribución por Nivel</h4>
                            <div class="view-toggle-buttons">
                                <button class="view-toggle-btn active" data-view="barras">
                                    <i class="fas fa-chart-bar"></i> Vista Barras
                                </button>
                                <button class="view-toggle-btn" data-view="grafico">
                                    <i class="fas fa-chart-pie"></i> Vista Gráfico
                                </button>
                            </div>
                        </div>

                        <!-- Vista de Barras (Por defecto) -->
                        <div id="vista-barras" class="visualization-container">
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
                                <span
                                    class="level-percent"><?php echo $porcentajes['Inicial (Escolarizado)']; ?>%</span>
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
                                <span
                                    class="level-percent"><?php echo $porcentajes['Inicial (No Escolarizado)']; ?>%</span>
                            </div>
                            <div class="level-bar">
                                <span class="level-name">Especial (CAM)</span>
                                <div class="level-track">
                                    <div class="level-fill"
                                        style="width: <?php echo isset($porcentajes['Especial (CAM)']) ? $porcentajes['Especial (CAM)'] : 0; ?>%">
                                        <span
                                            class="escuelas-count"><?php echo isset($escuelasPorNivel['Especial (CAM)']) ? $escuelasPorNivel['Especial (CAM)'] : 0; ?></span>
                                    </div>
                                </div>
                                <span
                                    class="level-percent"><?php echo isset($porcentajes['Especial (CAM)']) ? $porcentajes['Especial (CAM)'] : 0; ?>%</span>
                            </div>
                            <div class="level-bar">
                                <span class="level-name">Preescolar</span>
                                <div class="level-track">
                                    <div class="level-fill" style="width: <?php echo $porcentajes['Preescolar']; ?>%">
                                        <span
                                            class="escuelas-count"><?php echo $escuelasPorNivel['Preescolar']; ?></span>
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
                                        <span
                                            class="escuelas-count"><?php echo $escuelasPorNivel['Secundaria']; ?></span>
                                        <?php if (isset($escuelasNivelSostenimiento['Secundaria'])): ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="level-percent"><?php echo $porcentajes['Secundaria']; ?>%</span>
                            </div>
                            <div class="level-bar">
                                <span class="level-name">Media Sup.</span>
                                <div class="level-track">
                                    <div class="level-fill"
                                        style="width: <?php echo $porcentajes['Media Superior']; ?>%">
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
                        <!-- Fin Vista Barras -->

                        <!-- Vista Gráfico (Oculto por defecto) -->
                        <div id="vista-grafico" class="visualization-container" style="display: none;">
                            <div id="pie-chart-nivel" style="width: 100%; height: 400px;"></div>
                        </div>
                        <!-- Fin Vista Gráfico -->
                    </div>
                </div>
            </div>

            <!-- Panel de distribución por subcontrol educativo -->
            <div id="subcontrol-educativo" class="panel animate-up delay-1">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-building"></i> Distribución por Subcontrol Educativo</h3>
                </div>
                <div class="panel-body">
                    <?php
                    // Obtener distribución por subcontrol usando la nueva función
                    $datosSubcontrol = obtenerEscuelasPorSubcontrolYNivel($municipioSeleccionado);
                    $distribucionSubcontrol = isset($datosSubcontrol['distribucion']) ? $datosSubcontrol['distribucion'] : [];
                    $totalEscuelasSubcontrol = isset($datosSubcontrol['total_escuelas']) ? $datosSubcontrol['total_escuelas'] : 0;

                    if (!empty($distribucionSubcontrol)):
                        ?>
                        <!-- Tarjetas de subcontrol -->
                        <div class="subcontrol-cards animate-sequence">
                            <?php
                            // Orden específico para mostrar los subcontroles
                            $ordenSubcontroles = ['FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUTÓNOMO', 'PRIVADO'];

                            foreach ($ordenSubcontroles as $subcontrol):
                                if (!isset($distribucionSubcontrol[$subcontrol]))
                                    continue;
                                $datos = $distribucionSubcontrol[$subcontrol];

                                // Normalizar nombre para atributo data
                                $dataAttribute = strtolower(str_replace(array(' ', 'Ó'), array('-', 'o'), $subcontrol));
                                ?>

                                <div class="subcontrol-card animate-scale" data-subcontrol="<?php echo $dataAttribute; ?>">
                                    <div class="subcontrol-header">
                                        <div class="subcontrol-info">
                                            <h4 class="subcontrol-name"><?php echo $subcontrol; ?></h4>
                                            <div class="subcontrol-stats">
                                                <span
                                                    class="subcontrol-count"><?php echo number_format($datos['total']); ?></span>
                                                <span class="subcontrol-label">escuelas</span>
                                                <span class="subcontrol-percentage"><?php echo $datos['porcentaje']; ?>%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="subcontrol-progress">
                                        <div class="progress-bar-subcontrol">
                                            <div class="progress-fill-subcontrol animate-width"
                                                data-subcontrol="<?php echo $dataAttribute; ?>"
                                                style="width: <?php echo $datos['porcentaje']; ?>%;">
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($datos['niveles'])): ?>
                                        <div class="subcontrol-details">
                                            <div class="details-header">
                                                <i class="fas fa-layer-group"></i>
                                            </div>
                                            <div class="details-content">
                                                <?php foreach ($datos['niveles'] as $nivel => $cantidad): ?>
                                                    <?php if ($cantidad > 0): ?>
                                                        <div class="detail-item">
                                                            <span class="detail-level"><?php echo $nivel; ?></span>
                                                            <span class="detail-count"><?php echo $cantidad; ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            <?php endforeach; ?>
                        </div>

                        <!-- Resumen estadístico -->
                        <div class="subcontrol-summary animate-fade delay-3">
                            <div class="summary-stats">
                                <div class="summary-item">
                                    <i class="fas fa-school"></i>
                                    <div>
                                        <span
                                            class="summary-value"><?php echo number_format($totalEscuelasSubcontrol); ?></span>
                                        <span class="summary-label">Total de Escuelas</span>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <i class="fas fa-layer-group"></i>
                                    <div>
                                        <span class="summary-value"><?php echo count($distribucionSubcontrol); ?></span>
                                        <span class="summary-label">Tipos de Control</span>
                                    </div>
                                </div>
                                <?php
                                // Calcular escuelas públicas y privadas
                                $escuelasPublicasTotal = 0;
                                $escuelasPrivadasTotal = 0;
                                foreach ($distribucionSubcontrol as $sub => $dat) {
                                    if ($sub === 'PRIVADO') {
                                        $escuelasPrivadasTotal = $dat['total'];
                                    } else {
                                        $escuelasPublicasTotal += $dat['total'];
                                    }
                                }
                                $porcentajePublicas = $totalEscuelasSubcontrol > 0 ? round(($escuelasPublicasTotal / $totalEscuelasSubcontrol) * 100, 1) : 0;
                                $porcentajePrivadas = $totalEscuelasSubcontrol > 0 ? round(($escuelasPrivadasTotal / $totalEscuelasSubcontrol) * 100, 1) : 0;
                                ?>
                                <div class="summary-item">
                                    <i class="fas fa-landmark"></i>
                                    <div>
                                        <span class="summary-value"><?php echo number_format($escuelasPublicasTotal); ?>
                                            (<?php echo $porcentajePublicas; ?>%)</span>
                                        <span class="summary-label">Escuelas Públicas</span>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <i class="fas fa-building"></i>
                                    <div>
                                        <span class="summary-value"><?php echo number_format($escuelasPrivadasTotal); ?>
                                            (<?php echo $porcentajePrivadas; ?>%)</span>
                                        <span class="summary-label">Escuelas Privadas</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Mensaje si no hay datos -->
                        <div style="text-align: center; padding: 40px 20px; color: #666;">
                            <i class="fas fa-info-circle" style="font-size: 48px; color: #999; margin-bottom: 20px;"></i>
                            <h3 style="color: #333; margin-bottom: 15px;">No hay datos disponibles</h3>
                            <p style="font-size: 16px; line-height: 1.6;">
                                No se encontró información de distribución por subcontrol para este municipio.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Panel de eficiencia educativa 
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
                        </div>
                    </div>
                    <div id="efficiency-chart-view" style="display:none; height:350px;">
                        <div id="efficiency-chart" style="width:100%; height:100%;"></div>
                    </div>
                </div>
            </div>
                -->
        </div> <!-- Panel de análisis de trayectorias 
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
        </div>
        -->

        <!-- Panel de Directorio de Escuelas Públicas -->
        <div id="directorio-publicas" class="matricula-panel animate-fade delay-4">
            <div class="matricula-header">
                <h3 class="matricula-title">
                    <i class="fas fa-landmark"></i>
                    Directorio de Escuelas Públicas - <?php echo $municipioSeleccionado; ?>
                </h3>
            </div>
            <div class="matricula-body">
                <div class="directorio-filters">
                    <input type="text" id="search-publicas" placeholder="Buscar escuela pública..."
                        class="search-input">
                    <select id="nivel-filter-publicas" class="nivel-filter">
                        <option value="todos">Todos los niveles</option>
                        <?php foreach ($nivelesDisponibles as $codigo => $nombre):
                            $cantPublicas = isset($escuelasPublicasPorNivel[$codigo]) ? count($escuelasPublicasPorNivel[$codigo]) : 0;
                            if ($cantPublicas > 0):
                                ?>
                                <option value="<?php echo $codigo; ?>">
                                    <?php echo $nombre; ?> (<?php echo $cantPublicas; ?>)
                                </option>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </select>
                    <div class="export-buttons">
                        <button class="export-btn export-excel" onclick="exportarDirectorio('excel', 'publicas')" title="Exportar a Excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="export-btn export-pdf" onclick="exportarDirectorio('pdf', 'publicas')" title="Exportar a PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                    <div class="school-count">
                        <span class="count-label">Total:</span>
                        <span class="count-number" id="count-publicas">
                            <?php
                            $totalPublicas = 0;
                            foreach ($escuelasPublicasPorNivel as $escuelas) {
                                $totalPublicas += count($escuelas);
                            }
                            echo $totalPublicas;
                            ?>
                        </span>
                        <span class="count-text">escuelas</span>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table" id="tabla-publicas">
                        <thead>
                            <tr>
                                <th>Nivel Educativo</th>
                                <th>CCT</th>
                                <th>Nombre de la Escuela</th>
                                <th>Localidad</th>
                                <th>Total Alumnos</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-publicas">
                            <?php
                            foreach ($nivelesDisponibles as $codigo => $nombreNivel):
                                if (!isset($escuelasPublicasPorNivel[$codigo]))
                                    continue;
                                foreach ($escuelasPublicasPorNivel[$codigo] as $escuela):
                                    ?>
                                    <tr data-nivel="<?php echo $codigo; ?>"
                                        data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                        data-cct="<?php echo $escuela['cv_cct']; ?>">
                                        <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                        <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                        <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                        <td class="localidad-nombre"><?php echo $escuela['localidad']; ?></td>
                                        <td class="sector-publico"><?php echo number_format($escuela['total_alumnos']); ?></td>
                                    </tr>
                                    <?php
                                endforeach;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel de Directorio de Escuelas Privadas -->
        <div id="directorio-privadas" class="matricula-panel animate-fade delay-5">
            <div class="matricula-header">
                <h3 class="matricula-title">
                    <i class="fas fa-building"></i>
                    Directorio de Escuelas Privadas - <?php echo $municipioSeleccionado; ?>
                </h3>
            </div>
            <div class="matricula-body">
                <div class="directorio-filters">
                    <input type="text" id="search-privadas" placeholder="Buscar escuela privada..."
                        class="search-input">
                    <select id="nivel-filter-privadas" class="nivel-filter">
                        <option value="todos">Todos los niveles</option>
                        <?php foreach ($nivelesDisponibles as $codigo => $nombre):
                            $cantPrivadas = isset($escuelasPrivadasPorNivel[$codigo]) ? count($escuelasPrivadasPorNivel[$codigo]) : 0;
                            if ($cantPrivadas > 0):
                                ?>
                                <option value="<?php echo $codigo; ?>">
                                    <?php echo $nombre; ?> (<?php echo $cantPrivadas; ?>)
                                </option>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </select>
                    <div class="export-buttons">
                        <button class="export-btn export-excel" onclick="exportarDirectorio('excel', 'privadas')" title="Exportar a Excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="export-btn export-pdf" onclick="exportarDirectorio('pdf', 'privadas')" title="Exportar a PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                    <div class="school-count">
                        <span class="count-label">Total:</span>
                        <span class="count-number" id="count-privadas">
                            <?php
                            $totalPrivadas = 0;
                            foreach ($escuelasPrivadasPorNivel as $escuelas) {
                                $totalPrivadas += count($escuelas);
                            }
                            echo $totalPrivadas;
                            ?>
                        </span>
                        <span class="count-text">escuelas</span>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table" id="tabla-privadas">
                        <thead>
                            <tr>
                                <th>Nivel Educativo</th>
                                <th>CCT</th>
                                <th>Nombre de la Escuela</th>
                                <th>Localidad</th>
                                <th>Total Alumnos</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-privadas">
                            <?php
                            foreach ($nivelesDisponibles as $codigo => $nombreNivel):
                                if (!isset($escuelasPrivadasPorNivel[$codigo]))
                                    continue;
                                foreach ($escuelasPrivadasPorNivel[$codigo] as $escuela):
                                    ?>
                                    <tr data-nivel="<?php echo $codigo; ?>"
                                        data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                        data-cct="<?php echo $escuela['cv_cct']; ?>">
                                        <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                        <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                        <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                        <td class="localidad-nombre"><?php echo $escuela['localidad']; ?></td>
                                        <td class="sector-privado"><?php echo number_format($escuela['total_alumnos']); ?></td>
                                    </tr>
                                    <?php
                                endforeach;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
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
        // Debug: Verificar valores antes de generar JavaScript
        error_log("=== DEBUG ESCUELAS_DETALLE.PHP ===");
        error_log("totalEscuelas: " . $totalEscuelas);
        error_log("escuelasPublicas: " . $escuelasPublicas);
        error_log("escuelasPrivadas: " . $escuelasPrivadas);
        error_log("escuelasNivelSostenimiento: " . print_r($escuelasNivelSostenimiento, true));

        // Convertir datos de eficiencia a formato JSON para usar en JavaScript
        echo "const datosEficiencia = " . json_encode($datosEficiencia) . ";\n";
        echo "const datosEducativos = " . json_encode($datosEducativos) . ";\n";
        echo "const totalEscuelas = " . $totalEscuelas . ";\n";
        echo "const totalAlumnos = " . $totalAlumnos . ";\n";
        echo "const escuelasPublicas = " . $escuelasPublicas . ";\n";
        echo "const escuelasPrivadas = " . $escuelasPrivadas . ";\n";
        echo "const porcentajePublicas = " . $porcentajePublicas . ";\n";
        echo "const porcentajePrivadas = " . $porcentajePrivadas . ";\n";
        echo "const escuelasPorNivel = " . json_encode($escuelasPorNivel) . ";\n";

        // Datos de sostenimiento por nivel educativo (público/privado)
        echo "const escuelasNivelSostenimiento = " . json_encode($escuelasNivelSostenimiento) . ";\n";

        // Nombre del municipio para exportación
        echo "const municipioActual = " . json_encode($municipioSeleccionado) . ";\n";

        // Log de verificación
        echo "console.log('✓ Variables PHP cargadas correctamente');\n";
        echo "console.log('Total escuelas desde PHP:', " . $totalEscuelas . ");\n";
        echo "console.log('Escuelas públicas desde PHP:', " . $escuelasPublicas . ");\n";
        echo "console.log('Escuelas privadas desde PHP:', " . $escuelasPrivadas . ");\n";

        // Datos de subcontrol educativo - COMENTADO TEMPORALMENTE
        // echo "const totalEscuelasSubcontrol = " . $totalEscuelasSubcontrol . ";\n";
        // echo "const distribucionSubcontrol = " . json_encode($distribucionSubcontrol) . ";\n";
        ?>
    </script>
    <script src="./js/script.js"></script>
    <script src="./js/escuelas_diagram.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/sidebar.js"></script>
    <script src="./js/escuelas_publicas_privadas.js"></script>
    <script src="./js/directorio_escuelas.js"></script>
</body>

</html>