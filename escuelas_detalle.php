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

// =============================================================================
// OBTENCIÓN Y PROCESAMIENTO DE DATOS EDUCATIVOS
// =============================================================================

// Obtener datos completos del municipio usando funciones dinámicas
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Obtener datos de público/privado y distribución por subcontrol
$datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado);

// Obtener datos de USAER
$datosUSAER = obtenerDatosUSAER($municipioSeleccionado);

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

// Agregar datos de USAER al mapeo de sostenimiento
if ($datosUSAER && isset($datosUSAER['tot_esc']) && $datosUSAER['tot_esc'] > 0) {
    $escuelasNivelSostenimiento['Especial (USAER)'] = [
        'publicas' => isset($datosUSAER['tot_esc_pub']) ? (int) $datosUSAER['tot_esc_pub'] : 0,
        'privadas' => isset($datosUSAER['tot_esc_priv']) ? (int) $datosUSAER['tot_esc_priv'] : 0,
        'total' => isset($datosUSAER['tot_esc']) ? (int) $datosUSAER['tot_esc'] : 0
    ];
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

// Reorganizar array para insertar Especial (CAM) y Especial (USAER) en el orden correcto
$escuelasPorNivelOrdenado = [];
$alumnosPorNivelOrdenado = [];

$ordenNiveles = [
    'Inicial (Escolarizado)',
    'Inicial (No Escolarizado)',
    'Especial (CAM)',
    'Especial (USAER)',
    'Preescolar',
    'Primaria',
    'Secundaria',
    'Media Superior',
    'Superior'
];

foreach ($ordenNiveles as $nivel) {
    if ($nivel === 'Especial (CAM)') {
        // Mapear nivel Especial a Especial (CAM)
        if (isset($escuelasPorNivel['Especial'])) {
            $escuelasPorNivelOrdenado[$nivel] = $escuelasPorNivel['Especial'];
            $alumnosPorNivelOrdenado[$nivel] = $alumnosPorNivel['Especial'];
        } else {
            $escuelasPorNivelOrdenado[$nivel] = 0;
            $alumnosPorNivelOrdenado[$nivel] = 0;
        }
    } elseif ($nivel === 'Especial (USAER)') {
        // Agregar USAER (no se cuenta en el total pero se muestra en gráficos)
        $escuelasPorNivelOrdenado[$nivel] = isset($datosUSAER['tot_esc']) ? (int) $datosUSAER['tot_esc'] : 0;
        $alumnosPorNivelOrdenado[$nivel] = isset($datosUSAER['tot_mat']) ? (int) $datosUSAER['tot_mat'] : 0;
    } else {
        // Copiar valores existentes
        if (isset($escuelasPorNivel[$nivel])) {
            $escuelasPorNivelOrdenado[$nivel] = $escuelasPorNivel[$nivel];
            $alumnosPorNivelOrdenado[$nivel] = $alumnosPorNivel[$nivel];
        } else {
            $escuelasPorNivelOrdenado[$nivel] = 0;
            $alumnosPorNivelOrdenado[$nivel] = 0;
        }
    }
}

// Reemplazar arrays originales con los ordenados
$escuelasPorNivel = $escuelasPorNivelOrdenado;
$alumnosPorNivel = $alumnosPorNivelOrdenado;

// Mantener compatibilidad con 'Especial' y 'USAER' sin paréntesis
if (isset($escuelasPorNivel['Especial (CAM)'])) {
    $escuelasPorNivel['Especial'] = $escuelasPorNivel['Especial (CAM)'];
    $alumnosPorNivel['Especial'] = $alumnosPorNivel['Especial (CAM)'];
}
if (isset($escuelasPorNivel['Especial (USAER)'])) {
    $escuelasPorNivel['USAER'] = $escuelasPorNivel['Especial (USAER)'];
    $alumnosPorNivel['USAER'] = $alumnosPorNivel['Especial (USAER)'];
}

// Calcular distribución porcentual
$porcentajes = [];
if ($totalEscuelas > 0) {
    foreach ($escuelasPorNivel as $nivel => $cantidad) {
        // Especial (USAER) no se cuenta en el total, pero se calcula su porcentaje para visualización
        if ($nivel === 'Especial (USAER)' || $nivel === 'USAER') {
            // Calcular porcentaje de USAER basado en el total (aunque no forme parte del 100%)
            $porcentajes[$nivel] = $cantidad > 0 ? round(($cantidad / $totalEscuelas) * 100, 1) : 0;
        } else {
            $porcentajes[$nivel] = round(($cantidad / $totalEscuelas) * 100, 1);
        }
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
    'especial_usaer' => 'Especial (USAER)',
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

// Calcular totales de escuelas públicas y privadas para el directorio unificado
// IMPORTANTE: Contar CCTs únicos, no registros (una escuela puede tener múltiples turnos)
$totalPublicas = 0;
$totalPrivadas = 0;

// Usar arrays para rastrear CCTs únicos
$cctsUnicosPublicos = [];
$cctsUnicosPrivados = [];

foreach ($escuelasPublicasPorNivel as $nivel => $escuelas) {
    foreach ($escuelas as $escuela) {
        $cctsUnicosPublicos[$escuela['cv_cct']] = true;
    }
}

foreach ($escuelasPrivadasPorNivel as $nivel => $escuelas) {
    foreach ($escuelas as $escuela) {
        $cctsUnicosPrivados[$escuela['cv_cct']] = true;
    }
}

$totalPublicas = count($cctsUnicosPublicos);
$totalPrivadas = count($cctsUnicosPrivados);

// Calcular total de registros en el directorio (incluyendo múltiples turnos)
$totalRegistrosDirectorio = 0;
foreach ($escuelasPublicasPorNivel as $nivel => $escuelas) {
    $totalRegistrosDirectorio += count($escuelas);
}
foreach ($escuelasPrivadasPorNivel as $nivel => $escuelas) {
    $totalRegistrosDirectorio += count($escuelas);
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
    <title>Detalle de Escuelas | SEDEQ</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">
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

<body>
    <!-- ============================================================================ -->
    <!-- BARRA SUPERIOR INSTITUCIONAL                                                -->
    <!-- ============================================================================ -->
    <div class="top-institutional-bar">
        <div class="institutional-bar-content">
            <!-- Enlaces institucionales importantes -->
            <div class="institutional-links">
                <a href="https://www.queretaro.gob.mx/transparencia" class="institutional-link">Portal Transparencia</a>
                <a href="https://portal.queretaro.gob.mx/prensa/" class="institutional-link">Portal Prensa</a>
                <a href="https://www.queretaro.gob.mx/covid19" class="institutional-link">COVID19</a>
            </div>

            <!-- Redes sociales y contacto -->
            <div class="social-links">
                <a href="https://www.facebook.com/educacionqro" target="_blank" class="social-link" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://x.com/educacionqro" target="_blank" class="social-link" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.instagram.com/educacionqueretaro?fbclid=IwZXh0bgNhZW0CMTAAYnJpZBExR09OOWJid2NZT2ZTbUJvRHNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR4yi6bwE_6iEuyyUdbWYkjRLv9zjFFWyxwABVKdZSunmMWOwOsHAv_dcFFBOw_aem_t72qtgoL72OI4Pzyj-oILw"
                    target="_blank" class="social-link" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.youtube.com/@SecretariadeEducacionGEQ" target="_blank" class="social-link"
                    title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="tel:4422117070" class="social-link" title="Teléfono">
                    <i class="fas fa-phone"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================================================ -->
    <!-- HEADER PRINCIPAL CON LOGO Y NAVEGACIÓN                                      -->
    <!-- ============================================================================ -->
    <header class="main-header">
        <div class="header-content">
            <!-- Logo institucional -->
            <div class="header-logo">
                <a href="home.php">
                    <img src="./img/layout_set_logo.png" alt="SEDEQ - Secretaría de Educación de Querétaro">
                </a>
            </div>

            <!-- Menú de navegación horizontal (desktop) -->
            <div class="header-nav">
                <nav>
                    <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Resumen</a>
                    <!-- Escuelas con dropdown -->
                    <div class="nav-dropdown">
                        <a href="escuelas_detalle.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                            class="header-nav-link active">Escuelas <i
                                class="fas fa-chevron-down dropdown-arrow"></i></a>
                        <div class="nav-dropdown-content">
                            <a href="#resumen-escuelas" class="nav-dropdown-link">Resumen General</a>
                            <a href="#subcontrol-educativo" class="nav-dropdown-link">Subcontrol Educativo</a>
                            <a href="#directorio-escuelas" class="nav-dropdown-link">Directorio de Escuelas</a>
                            <a href="#usaer-section" class="nav-dropdown-link">USAER</a>
                        </div>
                    </div>
                    <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Matrícula</a>
                    <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Docentes</a>
                    <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>"
                        class="header-nav-link">Mapas</a>
                </nav>
            </div>

            <!-- Botón de búsqueda -->
            <div class="header-search">
                <button id="searchToggle" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- Botón de menú hamburguesa (solo móviles) -->
            <div class="header-menu-toggle">
                <button id="sidebarToggle" class="menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Barra de búsqueda expandible -->
        <div class="search-bar-expanded" id="searchBarExpanded">
            <div class="search-bar-content">
                <input type="text" placeholder="Buscar escuelas, municipios, estadísticas..." class="search-input">
                <button class="search-submit-btn">
                    <i class="fas fa-search"></i>
                </button>
                <button class="search-close-btn" id="searchClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Overlay para cerrar menú en dispositivos móviles -->
    <div class="sidebar-overlay"></div>

    <!-- ======================================== -->
    <!-- BARRA LATERAL DE NAVEGACIÓN              -->
    <!-- ======================================== -->
    <aside class="sidebar">
        <!-- Logo en el sidebar -->
        <div class="sidebar-header">
            <img src="./img/layout_set_logo.png" alt="SEDEQ" class="sidebar-logo">
        </div>

        <div class="sidebar-links">
            <!-- Enlace para regresar al home -->
            <a href="home.php" class="sidebar-link">
                <i class="fas fa-home"></i> <span>Regresar al Inicio</span>
            </a>

            <!-- Enlace a Resumen -->
            <a href="resumen.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-chart-bar"></i> <span>Resumen</span>
            </a>

            <!-- Sección de Escuelas con submenú -->
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
                    <a href="#directorio-escuelas" class="submenu-link">
                        <i class="fas fa-school"></i>
                        <span>Directorio de Escuelas</span>
                    </a>
                    <a href="#usaer-section" class="submenu-link">
                        <i class="fas fa-hands-helping"></i>
                        <span>USAER</span>
                    </a>
                </div>
            </div>

            <!-- Enlaces a otras secciones -->
            <a href="alumnos.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-user-graduate"></i> <span>Matrícula</span>
            </a>
            <a href="docentes.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-chalkboard-teacher"></i> <span>Docentes</span>
            </a>
            <a href="mapas.php?municipio=<?php echo urlencode($municipioSeleccionado); ?>" class="sidebar-link">
                <i class="fas fa-map-marked-alt"></i> <span>Mapas</span>
            </a>
        </div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Detalle de Escuelas <?php echo formatearNombreMunicipio($municipioSeleccionado); ?> - Ciclo
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

        <div class="container-fluid">
            <!-- Panel de resumen de escuelas -->
            <div id="resumen-escuelas" class="panel animate-up">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-school"></i> Resumen General de Escuelas en
                        <?php echo $municipioSeleccionado; ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="stats-row">
                        <div class="stat-box animate-fade delay-1">
                            <div class="stat-value"><?php echo $totalEscuelas; ?> </div>
                            <div class="stat-label">Total Escuelas Ciclo escolar
                                <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?>
                            </div>
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
                        <h4>Distribución por Tipo de Sostenimiento</h4>
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
                            <h4>Distribución por Tipo o Nivel Educativo</h4>


                            <div class="view-toggle-buttons">
                                <button class="view-toggle-btn active" data-view="grafico">
                                    <i class="fas fa-chart-pie"></i> Vista Gráfico
                                </button>
                                <button class="view-toggle-btn" data-view="barras">
                                    <i class="fas fa-chart-bar"></i> Vista Barras
                                </button>

                            </div>
                        </div>

                        <!-- Vista de Barras (Oculta por defecto) -->

                        <div class="sostenimiento-filters">
                            <button class="filter-btn active" data-filter="total">Total</button>
                            <button class="filter-btn" data-filter="publico">Público</button>
                            <button class="filter-btn" data-filter="privado">Privado</button>
                        </div>
                        <div id="vista-barras" class="visualization-container" style="display: none;">
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
                            <?php if (isset($escuelasPorNivel['Especial (USAER)']) && $escuelasPorNivel['Especial (USAER)'] > 0): ?>
                                <div class="level-bar">
                                    <span class="level-name">Especial (USAER)</span>
                                    <div class="level-track">
                                        <div class="level-fill"
                                            style="width: <?php echo $porcentajes['Especial (USAER)']; ?>%">
                                            <span
                                                class="escuelas-count"><?php echo $escuelasPorNivel['Especial (USAER)']; ?></span>
                                            <?php if (isset($escuelasNivelSostenimiento['Especial (USAER)'])): ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="level-percent"><?php echo $porcentajes['Especial (USAER)']; ?>%*</span>
                                </div>
                            <?php endif; ?>
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

                        <!-- Vista Gráfico (Visible por defecto) -->
                        <div id="vista-grafico" class="visualization-container">
                            <div id="pie-chart-nivel" style="width: 100%; height: 550px;"></div>
                        </div>
                        <!-- Fin Vista Gráfico -->
                    </div>
                </div>
            </div>

            <!-- Panel de distribución por subcontrol educativo -->
            <div id="subcontrol-educativo" class="panel animate-up delay-1">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="fas fa-building"></i> Distribución por Tipo de Sostenimiento</h3>
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
                                            <h4 class="subcontrol-name">
                                                <?php echo $subcontrol === 'FEDERAL TRANSFERIDO' ? 'USEBEQ' : $subcontrol; ?>
                                            </h4>
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

            <!-- Panel de Directorio Unificado de Escuelas -->
            <div id="directorio-escuelas" class="matricula-panel animate-fade delay-4">
                <div class="matricula-header">
                    <h3 class="matricula-title">
                        <i class="fas fa-school"></i>
                        Directorio de Escuelas - <?php echo $municipioSeleccionado; ?>
                    </h3>
                </div>
                <div class="matricula-body">
                    <div class="directorio-filters">
                        <input type="text" id="search-escuelas" placeholder="Buscar escuela..." class="search-input">

                        <select id="control-filter" class="nivel-filter">
                            <option value="todas">Todas</option>
                            <option value="publicas">Públicas (<?php echo $totalPublicas; ?>)</option>
                            <option value="privadas">Privadas (<?php echo $totalPrivadas; ?>)</option>
                        </select>

                        <select id="nivel-filter-escuelas" class="nivel-filter">
                            <option value="todos">Todos los niveles</option>
                            <?php foreach ($nivelesDisponibles as $codigo => $nombre):
                                // Contar CCTs únicos en lugar de registros
                                $cctsUnicosNivel = [];
                                if (isset($escuelasPublicasPorNivel[$codigo])) {
                                    foreach ($escuelasPublicasPorNivel[$codigo] as $esc) {
                                        $cctsUnicosNivel[$esc['cv_cct']] = true;
                                    }
                                }
                                if (isset($escuelasPrivadasPorNivel[$codigo])) {
                                    foreach ($escuelasPrivadasPorNivel[$codigo] as $esc) {
                                        $cctsUnicosNivel[$esc['cv_cct']] = true;
                                    }
                                }
                                $cantTotal = count($cctsUnicosNivel);

                                if ($cantTotal > 0):
                                    ?>
                                    <option value="<?php echo $codigo; ?>">
                                        <?php echo $nombre; ?> (<?php echo $cantTotal; ?>)
                                    </option>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        </select>

                        <div class="export-buttons">
                            <button class="export-btn export-excel" onclick="exportarDirectorioUnificado('excel')"
                                title="Exportar a Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="export-btn export-pdf" onclick="exportarDirectorioUnificado('pdf')"
                                title="Exportar a PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                        <div class="school-count">
                            <span class="count-label">Total:</span>
                            <span class="count-number" id="count-escuelas">
                                <?php echo ($totalPublicas + $totalPrivadas); ?>
                            </span>
                            <span class="count-text">escuelas</span>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table" id="tabla-escuelas">
                            <thead>
                                <tr>
                                    <th>Nivel Educativo</th>
                                    <th>CCT</th>
                                    <th>Nombre de la Escuela</th>
                                    <th>Localidad</th>
                                    <th>Control</th>
                                    <th>Total Matrícula</th>
                                    <th>Hombres</th>
                                    <th>Mujeres</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-escuelas">
                                <?php
                                // Combinar escuelas públicas y privadas
                                foreach ($nivelesDisponibles as $codigo => $nombreNivel):
                                    // Primero las públicas
                                    if (isset($escuelasPublicasPorNivel[$codigo])):
                                        foreach ($escuelasPublicasPorNivel[$codigo] as $escuela):
                                            ?>
                                            <tr data-nivel="<?php echo $codigo; ?>" data-control="publicas"
                                                data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                                data-cct="<?php echo $escuela['cv_cct']; ?>"
                                                data-hombres="<?php echo isset($escuela['alumnos_hombres']) ? $escuela['alumnos_hombres'] : 0; ?>"
                                                data-mujeres="<?php echo isset($escuela['alumnos_mujeres']) ? $escuela['alumnos_mujeres'] : 0; ?>">
                                                <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                                <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                                <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                                <td class="localidad-nombre"><?php echo $escuela['localidad']; ?></td>
                                                <td class="control-tipo"><span class="badge-publico">Público</span></td>
                                                <td class="sector-publico"><?php echo number_format($escuela['total_alumnos']); ?>
                                                </td>
                                                <td class="alumnos-hombres">
                                                    <?php echo number_format(isset($escuela['alumnos_hombres']) ? $escuela['alumnos_hombres'] : 0); ?>
                                                </td>
                                                <td class="alumnos-mujeres">
                                                    <?php echo number_format(isset($escuela['alumnos_mujeres']) ? $escuela['alumnos_mujeres'] : 0); ?>
                                                </td>
                                            </tr>
                                            <?php
                                        endforeach;
                                    endif;

                                    // Luego las privadas
                                    if (isset($escuelasPrivadasPorNivel[$codigo])):
                                        foreach ($escuelasPrivadasPorNivel[$codigo] as $escuela):
                                            ?>
                                            <tr data-nivel="<?php echo $codigo; ?>" data-control="privadas"
                                                data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                                data-cct="<?php echo $escuela['cv_cct']; ?>"
                                                data-hombres="<?php echo isset($escuela['alumnos_hombres']) ? $escuela['alumnos_hombres'] : 0; ?>"
                                                data-mujeres="<?php echo isset($escuela['alumnos_mujeres']) ? $escuela['alumnos_mujeres'] : 0; ?>">
                                                <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                                <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                                <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                                <td class="localidad-nombre"><?php echo $escuela['localidad']; ?></td>
                                                <td class="control-tipo"><span class="badge-privado">Privado</span></td>
                                                <td class="sector-privado"><?php echo number_format($escuela['total_alumnos']); ?>
                                                </td>
                                                <td class="alumnos-hombres">
                                                    <?php echo number_format(isset($escuela['alumnos_hombres']) ? $escuela['alumnos_hombres'] : 0); ?>
                                                </td>
                                                <td class="alumnos-mujeres">
                                                    <?php echo number_format(isset($escuela['alumnos_mujeres']) ? $escuela['alumnos_mujeres'] : 0); ?>
                                                </td>
                                            </tr>
                                            <?php
                                        endforeach;
                                    endif;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sección de USAER (Unidad de Servicios de Apoyo a la Educación Regular) -->
            <?php if ($datosUSAER && isset($datosUSAER['tot_esc']) && $datosUSAER['tot_esc'] > 0): ?>
                <div style="margin-top: 40px;"></div>
                <?php
                // Preparar datos de USAER con valores seguros (evitar nulls)
                $totalEscUSAER = isset($datosUSAER['tot_esc']) ? (int) $datosUSAER['tot_esc'] : 0;
                $totalEscPubUSAER = isset($datosUSAER['tot_esc_pub']) ? (int) $datosUSAER['tot_esc_pub'] : 0;
                $totalEscPrivUSAER = isset($datosUSAER['tot_esc_priv']) ? (int) $datosUSAER['tot_esc_priv'] : 0;
                $totalMatUSAER = isset($datosUSAER['tot_mat']) ? (int) $datosUSAER['tot_mat'] : 0;
                $totalMatPubUSAER = isset($datosUSAER['tot_mat_pub']) ? (int) $datosUSAER['tot_mat_pub'] : 0;
                $totalMatPrivUSAER = isset($datosUSAER['tot_mat_priv']) ? (int) $datosUSAER['tot_mat_priv'] : 0;
                $matHUSAER = isset($datosUSAER['mat_h']) ? (int) $datosUSAER['mat_h'] : 0;
                $matHPubUSAER = isset($datosUSAER['mat_h_pub']) ? (int) $datosUSAER['mat_h_pub'] : 0;
                $matHPrivUSAER = isset($datosUSAER['mat_h_priv']) ? (int) $datosUSAER['mat_h_priv'] : 0;
                $matMUSAER = isset($datosUSAER['mat_m']) ? (int) $datosUSAER['mat_m'] : 0;
                $matMPubUSAER = isset($datosUSAER['mat_m_pub']) ? (int) $datosUSAER['mat_m_pub'] : 0;
                $matMPrivUSAER = isset($datosUSAER['mat_m_priv']) ? (int) $datosUSAER['mat_m_priv'] : 0;
                ?>
                <div id="usaer-section" class="matricula-panel animate-fade delay-5">
                    <div class="matricula-header">
                        <h3 class="matricula-title">
                            <i class="fas fa-hands-helping"></i> USAER - Unidad de Servicios de Apoyo a la Educación Regular
                        </h3>
                    </div>
                    <div class="matricula-body">
                        <p class="usaer-subtitle">
                            Datos informativos de las Unidades de Servicios de Apoyo a la Educación Regular.
                            Estos datos no se suman en los totales municipales ya que atienden a alumnos contabilizados en
                            los
                            niveles correspondientes.
                        </p>

                        <div class="usaer-container">
                            <!-- Resumen General de USAER -->
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
                                            <span class="total-tipo">Total Unidades USAER</span>
                                            <span
                                                class="total-valor"><?php echo number_format($totalEscUSAER, 0, '.', ','); ?></span>
                                            <span class="total-subtitulo">unidades</span>
                                        </div>
                                    </div>
                                    <div class="total-municipal-card">
                                        <div class="total-icono">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div class="total-contenido">
                                            <span class="total-tipo">Total Matrícula Atendida</span>
                                            <span
                                                class="total-valor"><?php echo number_format($totalMatUSAER, 0, '.', ','); ?></span>
                                            <span class="total-subtitulo">alumnos</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desglose detallado -->
                            <div class="usaer-desglose-detallado">
                                <h3 style="text-align: center; margin-bottom: 20px; color: var(--text-primary);">
                                    <i class="fas fa-chart-line"></i> Desglose por Sostenimiento
                                </h3>

                                <!-- Desglose Público vs Privado -->
                                <div class="usaer-sostenimiento-grid">
                                    <!-- Público -->
                                    <div class="usaer-sostenimiento-card publico-card">
                                        <h4>
                                            <i class="fas fa-university"></i> Público
                                        </h4>
                                        <div class="usaer-dato-grupo">
                                            <div class="numero-principal">
                                                <?php echo number_format($totalEscPubUSAER, 0, '.', ','); ?> unidades
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo $totalEscUSAER > 0 ? round(($totalEscPubUSAER / $totalEscUSAER) * 100, 1) : 0; ?>%
                                            </div>
                                        </div>
                                        <div class="usaer-dato-grupo">
                                            <div class="numero-principal">
                                                <?php echo number_format($totalMatPubUSAER, 0, '.', ','); ?> Matrícula
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo $totalMatUSAER > 0 ? round(($totalMatPubUSAER / $totalMatUSAER) * 100, 1) : 0; ?>%
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Privado -->
                                    <div class="usaer-sostenimiento-card privado-card">
                                        <h4>
                                            <i class="fas fa-building"></i> Privado
                                        </h4>
                                        <div class="usaer-dato-grupo">
                                            <div class="numero-principal">
                                                <?php echo number_format($totalEscPrivUSAER, 0, '.', ','); ?> unidades
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo $totalEscUSAER > 0 ? round(($totalEscPrivUSAER / $totalEscUSAER) * 100, 1) : 0; ?>%
                                            </div>
                                        </div>
                                        <div class="usaer-dato-grupo">
                                            <div class="numero-principal">
                                                <?php echo number_format($totalMatPrivUSAER, 0, '.', ','); ?> matrícula
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo $totalMatUSAER > 0 ? round(($totalMatPrivUSAER / $totalMatUSAER) * 100, 1) : 0; ?>%
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Desglose por Sexo -->
                                <div class="usaer-sexo-section">
                                    <h4 style="text-align: center; margin: 30px 0 20px 0; color: var(--text-primary);">
                                        <i class="fas fa-venus-mars"></i> Distribución de Matrícula por Sexo
                                    </h4>
                                    <div class="sexo-grid">
                                        <!-- Hombres -->
                                        <div class="hombres-card">
                                            <h4>
                                                <i class="fas fa-mars"></i> Hombres
                                            </h4>
                                            <div class="numero-principal">
                                                <?php echo number_format($matHUSAER, 0, '.', ','); ?> Total
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo $totalMatUSAER > 0 ? round(($matHUSAER / $totalMatUSAER) * 100, 1) : 0; ?>%
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($matHPubUSAER, 0, '.', ','); ?> Público
                                                (<?php echo $matHUSAER > 0 ? round(($matHPubUSAER / $matHUSAER) * 100, 1) : 0; ?>%)
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($matHPrivUSAER, 0, '.', ','); ?> Privado
                                                (<?php echo $matHUSAER > 0 ? round(($matHPrivUSAER / $matHUSAER) * 100, 1) : 0; ?>%)
                                            </div>
                                        </div>

                                        <!-- Mujeres -->
                                        <div class="mujeres-card">
                                            <h4>
                                                <i class="fas fa-venus"></i> Mujeres
                                            </h4>
                                            <div class="numero-principal">
                                                <?php echo number_format($matMUSAER, 0, '.', ','); ?> Total
                                            </div>
                                            <div class="porcentaje">
                                                <?php echo $totalMatUSAER > 0 ? round(($matMUSAER / $totalMatUSAER) * 100, 1) : 0; ?>%
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($matMPubUSAER, 0, '.', ','); ?> Público
                                                (<?php echo $matMUSAER > 0 ? round(($matMPubUSAER / $matMUSAER) * 100, 1) : 0; ?>%)
                                            </div>
                                            <div class="detalles-secundarios">
                                                <?php echo number_format($matMPrivUSAER, 0, '.', ','); ?> Privado
                                                (<?php echo $matMUSAER > 0 ? round(($matMPrivUSAER / $matMUSAER) * 100, 1) : 0; ?>%)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Pie de página -->
        <footer class="main-footer">
            <div>
                <div class="mb-lg-0 ml-lg-0 mr-lg-0 mt-lg-0 pb-lg-0 pl-lg-0 pr-lg-0 pt-lg-0">
                    <div id="fragment-1095-melk">
                        <section class="top-top-ft2">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12 text-center logo-footer">
                                        <img src="./img/heraldicas.png" width="200" height="auto"
                                            alt="Gobierno de Querétaro">
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="top-footer2">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                        <div class="link-footer">
                                            <img src="https://queretaro.gob.mx/o/queretaro-theme/images/lugar.png"
                                                width="100px" height="auto">
                                            <h3 class="tf-title">DIRECCIÓN PALACIO DE GOBIERNO</h3>
                                            <p class="p-footer">
                                                5 de Mayo S/N esq. Luis Pasteur,
                                                Col. Centro Histórico C.P. 76000,
                                                Santiago de Querétaro, Qro.,México.</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                        <div class="link-footer">
                                            <img src="https://queretaro.gob.mx/o/queretaro-theme/images/telefono.png"
                                                width="100px" height="auto">
                                            <h3 class="tf-title">TELÉFONO</h3>
                                            <p class="p-footer">
                                                800 237 2233<br>
                                                Directorio (442) 211 7070</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                        <div class="link-footer">
                                            <img src="https://queretaro.gob.mx/o/queretaro-theme/images/correo.png"
                                                width="100px" height="auto">
                                            <h3 class="tf-title">ATENCIÓN CIUDADANA</h3>
                                            <p class="p-footer">
                                                Preguntas dudas, comentarios sobre el contenido del portal.</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                        <div class="link-footer">
                                            <img src="https://queretaro.gob.mx/o/queretaro-theme/images/correo.png"
                                                width="100px" height="auto">
                                            <h3 class="tf-title">WEB MASTER</h3>
                                            <p class="p-footer">
                                                Preguntas dudas, comentarios sobre el funcionamiento del portal.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="bottom-footer2 page-editor__disabled-area">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-8 col-sm-12 col-12">
                                        <p><a href="https://queretaro.gob.mx/web/aviso-de-privacidad"
                                                class="aviso">Aviso de
                                                privacidad</a></p>
                                        <p>PODER EJECUTIVO DEL ESTADO DE QUERÉTARO Copyright © 2026 Derechos
                                            Reservados. </p>
                                    </div>
                                    <div class="col-md-4 col-sm-12 col-12 text-md-right text-center fin-div">
                                        <div class="social-links">
                                            <ul>
                                                <li class="social_"><a href="https://wa.me/+524421443740"
                                                        target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                            viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                            <path fill="#ffffff"
                                                                d="M416 208C416 305.2 330 384 224 384C197.3 384 171.9 379 148.8 370L67.2 413.2C57.9 418.1 46.5 416.4 39 409C31.5 401.6 29.8 390.1 34.8 380.8L70.4 313.6C46.3 284.2 32 247.6 32 208C32 110.8 118 32 224 32C330 32 416 110.8 416 208zM416 576C321.9 576 243.6 513.9 227.2 432C347.2 430.5 451.5 345.1 463 229.3C546.3 248.5 608 317.6 608 400C608 439.6 593.7 476.2 569.6 505.6L605.2 572.8C610.1 582.1 608.4 593.5 601 601C593.6 608.5 582.1 610.2 572.8 605.2L491.2 562C468.1 571 442.7 576 416 576z" />
                                                        </svg></a></li>
                                                <li class="social_"><a href="https://www.facebook.com/GobQro?fref=ts"
                                                        target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                            viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                            <path fill="#ffffff"
                                                                d="M240 363.3L240 576L356 576L356 363.3L442.5 363.3L460.5 265.5L356 265.5L356 230.9C356 179.2 376.3 159.4 428.7 159.4C445 159.4 458.1 159.8 465.7 160.6L465.7 71.9C451.4 68 416.4 64 396.2 64C289.3 64 240 114.5 240 223.4L240 265.5L174 265.5L174 363.3L240 363.3z" />
                                                        </svg></a></li>
                                                <li class="social_"><a href="https://twitter.com/gobqro"
                                                        target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                            viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                            <path fill="#ffffff"
                                                                d="M453.2 112L523.8 112L369.6 288.2L551 528L409 528L297.7 382.6L170.5 528L99.8 528L264.7 339.5L90.8 112L236.4 112L336.9 244.9L453.2 112zM428.4 485.8L467.5 485.8L215.1 152L173.1 152L428.4 485.8z" />
                                                        </svg></a></li>
                                                <li class="social_"><a
                                                        href="https://www.instagram.com/educacionqueretaro?fbclid=IwZXh0bgNhZW0CMTAAYnJpZBExR09OOWJid2NZT2ZTbUJvRHNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR4yi6bwE_6iEuyyUdbWYkjRLv9zjFFWyxwABVKdZSunmMWOwOsHAv_dcFFBOw_aem_t72qtgoL72OI4Pzyj-oILw"
                                                        target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                            viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                            <path fill="#ffffff"
                                                                d="M320.3 205C256.8 204.8 205.2 256.2 205 319.7C204.8 383.2 256.2 434.8 319.7 435C383.2 435.2 434.8 383.8 435 320.3C435.2 256.8 383.8 205.2 320.3 205zM319.7 245.4C360.9 245.2 394.4 278.5 394.6 319.7C394.8 360.9 361.5 394.4 320.3 394.6C279.1 394.8 245.6 361.5 245.4 320.3C245.2 279.1 278.5 245.6 319.7 245.4zM413.1 200.3C413.1 185.5 425.1 173.5 439.9 173.5C454.7 173.5 466.7 185.5 466.7 200.3C466.7 215.1 454.7 227.1 439.9 227.1C425.1 227.1 413.1 215.1 413.1 200.3zM542.8 227.5C541.1 191.6 532.9 159.8 506.6 133.6C480.4 107.4 448.6 99.2 412.7 97.4C375.7 95.3 264.8 95.3 227.8 97.4C192 99.1 160.2 107.3 133.9 133.5C107.6 159.7 99.5 191.5 97.7 227.4C95.6 264.4 95.6 375.3 97.7 412.3C99.4 448.2 107.6 480 133.9 506.2C160.2 532.4 191.9 540.6 227.8 542.4C264.8 544.5 375.7 544.5 412.7 542.4C448.6 540.7 480.4 532.5 506.6 506.2C532.8 480 541 448.2 542.8 412.3C544.9 375.3 544.9 264.5 542.8 227.5zM495 452C487.2 471.6 472.1 486.7 452.4 494.6C422.9 506.3 352.9 503.6 320.3 503.6C287.7 503.6 217.6 506.2 188.2 494.6C168.6 486.8 153.5 471.7 145.6 452C133.9 422.5 136.6 352.5 136.6 319.9C136.6 287.3 134 217.2 145.6 187.8C153.4 168.2 168.5 153.1 188.2 145.2C217.7 133.5 287.7 136.2 320.3 136.2C352.9 136.2 423 133.6 452.4 145.2C472 153 487.1 168.1 495 187.8C506.7 217.3 504 287.3 504 319.9C504 352.5 506.7 422.6 495 452z" />
                                                        </svg></a></li>
                                                <li class="social_"><a href="https://www.youtube.com/user/GobQro"
                                                        target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                            viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                            <path fill="#ffffff"
                                                                d="M581.7 188.1C575.5 164.4 556.9 145.8 533.4 139.5C490.9 128 320.1 128 320.1 128C320.1 128 149.3 128 106.7 139.5C83.2 145.8 64.7 164.4 58.4 188.1C47 231 47 320.4 47 320.4C47 320.4 47 409.8 58.4 452.7C64.7 476.3 83.2 494.2 106.7 500.5C149.3 512 320.1 512 320.1 512C320.1 512 490.9 512 533.5 500.5C557 494.2 575.5 476.3 581.8 452.7C593.2 409.8 593.2 320.4 593.2 320.4C593.2 320.4 593.2 231 581.8 188.1zM264.2 401.6L264.2 239.2L406.9 320.4L264.2 401.6z" />
                                                        </svg></a></li>
                                                <li class="social_"><a href="tel:4422117070" target="_blank"><svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                            <path fill="#ffffff"
                                                                d="M224.2 89C216.3 70.1 195.7 60.1 176.1 65.4L170.6 66.9C106 84.5 50.8 147.1 66.9 223.3C104 398.3 241.7 536 416.7 573.1C493 589.3 555.5 534 573.1 469.4L574.6 463.9C580 444.2 569.9 423.6 551.1 415.8L453.8 375.3C437.3 368.4 418.2 373.2 406.8 387.1L368.2 434.3C297.9 399.4 241.3 341 208.8 269.3L253 233.3C266.9 222 271.6 202.9 264.8 186.3L224.2 89z" />
                                                        </svg></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
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