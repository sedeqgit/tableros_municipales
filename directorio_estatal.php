<?php
/**
 * =============================================================================
 * PÁGINA DE DIRECTORIO ESTATAL DE ESCUELAS - SISTEMA SEDEQ (PRUEBA)
 * =============================================================================
 *
 * Esta página presenta el directorio completo de instituciones educativas
 * a nivel ESTATAL (todo Querétaro) sin filtro por municipio.
 *
 * FUNCIONALIDADES PRINCIPALES:
 * - Directorio completo de escuelas de todo el estado
 * - Filtros por nivel educativo (Inicial, Preescolar, Primaria, etc.)
 * - Información detallada: CCT, nombre, municipio, localidad, alumnos, control
 * - Exportación de directorios en múltiples formatos
 *
 * @package SEDEQ_Dashboard
 * @subpackage Directorio_Escuelas_Estatal
 * @version 1.0 (PRUEBA)
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

// =============================================================================
// OBTENCIÓN Y PROCESAMIENTO DE DATOS EDUCATIVOS A NIVEL ESTATAL
// =============================================================================

// Obtener datos completos del estado
$datosCompletosEstado = obtenerResumenEstadoCompleto();

// Verificar si hay datos
$hayError = !$datosCompletosEstado;
$tieneDatos = $datosCompletosEstado &&
    isset($datosCompletosEstado['total_matricula']) &&
    $datosCompletosEstado['total_matricula'] > 0;

// =============================================================================
// CÁLCULOS ESTADÍSTICOS GENERALES
// =============================================================================

// Extraer totales de manera segura y convertir a enteros
$totalEscuelas = $tieneDatos ? (int) $datosCompletosEstado['total_escuelas'] : 0;
$totalAlumnos = $tieneDatos ? (int) $datosCompletosEstado['total_matricula'] : 0;
$totalDocentes = $tieneDatos ? (int) $datosCompletosEstado['total_docentes'] : 0;

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
// CARGAR DIRECTORIOS DE ESCUELAS POR NIVEL (ESTATAL)
// =============================================================================

// Inicializar array para almacenar directorios por nivel
$directoriosPorNivelEstatal = [];
$notasEspeciales = [];

// Obtener lista de todos los municipios
$todosMunicipios = obtenerMunicipiosPrueba2024();

// Cargar directorio para cada nivel educativo (consolidando todos los municipios)
foreach ($nivelesDisponibles as $codigoNivel => $nombreNivel) {
    $escuelasNivel = [];

    // Recorrer todos los municipios y consolidar escuelas
    foreach ($todosMunicipios as $municipio) {
        $directorio = obtenerDirectorioEscuelas($municipio, $codigoNivel);

        if ($directorio && isset($directorio['escuelas']) && !empty($directorio['escuelas'])) {
            foreach ($directorio['escuelas'] as $escuela) {
                // Agregar el municipio a cada escuela para mostrarlo en la tabla
                $escuela['municipio'] = $municipio;
                $escuelasNivel[] = $escuela;
            }
        }
    }

    // Guardar las escuelas consolidadas para este nivel
    if (!empty($escuelasNivel)) {
        $directoriosPorNivelEstatal[$codigoNivel] = [
            'escuelas' => $escuelasNivel,
            'total_escuelas' => count($escuelasNivel)
        ];
    }
}

// Separar escuelas públicas y privadas para cada nivel
$escuelasPublicasPorNivel = [];
$escuelasPrivadasPorNivel = [];

// Inicializar contadores por subcontrol
$conteoSubcontrol = [];
$totalEscuelasPublicas = 0;
$totalEscuelasPrivadas = 0;

foreach ($directoriosPorNivelEstatal as $nivel => $datos) {
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
            $totalEscuelasPrivadas++;
        } else {
            $escuelasPublicasPorNivel[$nivel][] = $escuela;
            $totalEscuelasPublicas++;
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

// Calcular porcentajes
$porcentajePublicas = $totalEscuelas > 0 ? round(($totalEscuelasPublicas / $totalEscuelas) * 100, 1) : 0;
$porcentajePrivadas = $totalEscuelas > 0 ? round(($totalEscuelasPrivadas / $totalEscuelas) * 100, 1) : 0;

// Obtener información del ciclo escolar
$infoCiclo = obtenerInfoCicloEscolar();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio Estatal de Escuelas | SEDEQ</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/resumen.css">
    <link rel="stylesheet" href="./css/escuelas_detalle.css">
    <link rel="stylesheet" href="./css/sidebar.css">
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
                    <a href="home.php" class="header-nav-link">Inicio</a>
                    <a href="directorio_estatal.php" class="header-nav-link active">Escuelas</a>
                    <a href="bibliotecas.php" class="header-nav-link">Bibliotecas</a>
                    <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                        target="_blank" class="header-nav-link">Mapa</a>
                    <a href="settings.php" class="header-nav-link">Configuración</a>
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

    <!-- Sidebar desplegable (solo móviles) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Menú</h2>
            <button class="sidebar-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="home.php" class="sidebar-link">
                <i class="fas fa-home"></i> <span>Inicio</span>
            </a>
            <a href="directorio_estatal.php" class="sidebar-link active">
                <i class="fas fa-school"></i> <span>Escuelas</span>
            </a>
            <a href="bibliotecas.php" class="sidebar-link">
                <i class="fas fa-book"></i> <span>Bibliotecas</span>
            </a>
            <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                target="_blank" class="sidebar-link">
                <i class="fas fa-map-marked-alt"></i> <span>Mapa</span>
            </a>
            <a href="settings.php" class="sidebar-link">
                <i class="fas fa-cog"></i> <span>Configuración</span>
            </a>
        </nav>
    </aside>

    <!-- Contenedor principal de la aplicación -->
    <div class="app-container">
        <div class="main-content">
            <div class="page-title top-bar-title" style="padding: 20px 20px 10px;">
                <h1>Directorio Estatal de Escuelas - Querétaro</h1>
            </div>

        <div class="container-fluid">
            <!-- Panel de resumen de escuelas -->
            <div id="resumen-escuelas" class="panel animate-up">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <i class="fas fa-school"></i> Resumen de Escuelas en el Estado de Querétaro
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="stats-row">
                        <div class="stat-box animate-fade delay-1">
                            <div class="stat-value"><?php echo number_format($totalEscuelas); ?></div>
                            <div class="stat-label">Total Escuelas Ciclo escolar
                                <?php echo $infoCiclo['ciclo_completo'] ?? '2024-2025'; ?>
                            </div>
                        </div>
                        <div class="stat-box animate-fade delay-2">
                            <div class="stat-value">
                                <span class="public-schools"><?php echo number_format($totalEscuelasPublicas); ?></span>
                                <span class="separator"> / </span>
                                <span
                                    class="private-schools"><?php echo number_format($totalEscuelasPrivadas); ?></span>
                            </div>
                            <div class="stat-label">Escuelas Públicas / Privadas</div>
                        </div>
                        <div class="stat-box animate-fade delay-3">
                            <div class="stat-value"><?php echo number_format($totalAlumnos); ?></div>
                            <div class="stat-label">Matrícula</div>
                        </div>
                    </div>

                    <!-- Gráfico de distribución pública vs privada -->
                    <div class="sostenimiento-chart animate-fade delay-3">
                        <h4>Distribución por Sostenimiento</h4>
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
                </div>
            </div>

            <!-- Panel de Búsqueda Maestra -->
            <div id="busqueda-maestra" class="panel animate-fade delay-3">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <i class="fas fa-search"></i> Búsqueda General de Escuelas
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="master-search-container">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="master-search"
                                placeholder="Busca por CCT, nombre de escuela, municipio o localidad..."
                                class="master-search-input">
                            <button id="clear-master-search" class="clear-search-btn" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="search-results-summary" class="search-summary" style="display: none;">
                            <div class="summary-content">
                                <i class="fas fa-info-circle"></i>
                                <span class="summary-text">
                                    Encontradas: <strong id="total-results">0</strong> escuelas
                                    (<span class="public-count"><strong id="publicas-results">0</strong>
                                        públicas</span>,
                                    <span class="private-count"><strong id="privadas-results">0</strong>
                                        privadas</span>)
                                </span>
                            </div>
                        </div>
                        <div class="search-hints">
                            <small>
                                <i class="fas fa-lightbulb"></i>
                                Tip: Escribe cualquier información que tengas. La búsqueda es inteligente y encontrará
                                escuelas en ambas categorías.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Directorio de Escuelas Públicas -->
            <div id="directorio-publicas" class="matricula-panel animate-fade delay-4">
                <div class="matricula-header">
                    <h3 class="matricula-title">
                        <i class="fas fa-landmark"></i>
                        Directorio de Escuelas Públicas - Estado de Querétaro
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
                        <select id="municipio-filter-publicas" class="nivel-filter">
                            <option value="todos">Todos los municipios</option>
                            <?php foreach ($todosMunicipios as $municipio):
                                // Formatear nombre del municipio
                                $municipioFormateado = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');
                                $municipioFormateado = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $municipioFormateado);
                                ?>
                                <option value="<?php echo strtolower($municipio); ?>">
                                    <?php echo $municipioFormateado; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="export-buttons">
                            <button class="export-btn export-excel"
                                onclick="exportarDirectorioEstatal('excel', 'publicas')" title="Exportar a Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="export-btn export-pdf" onclick="exportarDirectorioEstatal('pdf', 'publicas')"
                                title="Exportar a PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                        <div class="school-count">
                            <span class="count-label">Total:</span>
                            <span class="count-number" id="count-publicas">
                                <?php echo $totalEscuelasPublicas; ?>
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
                                    <th>Turno</th>
                                    <th>Municipio</th>
                                    <th>Localidad</th>
                                    <th>Total Matrícula</th>
                                    <th>Hombres</th>
                                    <th>Mujeres</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-publicas">
                                <?php
                                foreach ($nivelesDisponibles as $codigo => $nombreNivel):
                                    if (!isset($escuelasPublicasPorNivel[$codigo]))
                                        continue;
                                    foreach ($escuelasPublicasPorNivel[$codigo] as $escuela):
                                        // Formatear nombre del municipio
                                        $municipioFormateado = mb_convert_case(strtolower($escuela['municipio']), MB_CASE_TITLE, 'UTF-8');
                                        $municipioFormateado = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $municipioFormateado);
                                        ?>
                                        <tr data-nivel="<?php echo $codigo; ?>"
                                            data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                            data-cct="<?php echo $escuela['cv_cct']; ?>"
                                            data-turno="<?php echo isset($escuela['turno']) ? strtolower($escuela['turno']) : ''; ?>"
                                            data-municipio="<?php echo strtolower($escuela['municipio']); ?>"
                                            data-hombres="<?php echo $escuela['alumnos_hombres']; ?>"
                                            data-mujeres="<?php echo $escuela['alumnos_mujeres']; ?>">
                                            <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                            <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                            <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                            <td class="turno-escuela">
                                                <?php echo isset($escuela['turno']) ? $escuela['turno'] : 'N/A'; ?>
                                            </td>
                                            <td class="municipio-nombre"><?php echo $municipioFormateado; ?></td>
                                            <td class="localidad-nombre"><?php echo $escuela['localidad']; ?></td>
                                            <td class="sector-publico"><?php echo number_format($escuela['total_alumnos']); ?>
                                            </td>
                                            <td class="alumnos-hombres">
                                                <?php echo number_format($escuela['alumnos_hombres']); ?>
                                            </td>
                                            <td class="alumnos-mujeres">
                                                <?php echo number_format($escuela['alumnos_mujeres']); ?>
                                            </td>
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
                        Directorio de Escuelas Privadas - Estado de Querétaro
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
                        <select id="municipio-filter-privadas" class="nivel-filter">
                            <option value="todos">Todos los municipios</option>
                            <?php foreach ($todosMunicipios as $municipio):
                                // Formatear nombre del municipio
                                $municipioFormateado = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');
                                $municipioFormateado = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $municipioFormateado);
                                ?>
                                <option value="<?php echo strtolower($municipio); ?>">
                                    <?php echo $municipioFormateado; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="export-buttons">
                            <button class="export-btn export-excel"
                                onclick="exportarDirectorioEstatal('excel', 'privadas')" title="Exportar a Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="export-btn export-pdf" onclick="exportarDirectorioEstatal('pdf', 'privadas')"
                                title="Exportar a PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                        <div class="school-count">
                            <span class="count-label">Total:</span>
                            <span class="count-number" id="count-privadas">
                                <?php echo $totalEscuelasPrivadas; ?>
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
                                    <th>Turno</th>
                                    <th>Municipio</th>
                                    <th>Localidad</th>
                                    <th>Total Alumnos</th>
                                    <th>Hombres</th>
                                    <th>Mujeres</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-privadas">
                                <?php
                                foreach ($nivelesDisponibles as $codigo => $nombreNivel):
                                    if (!isset($escuelasPrivadasPorNivel[$codigo]))
                                        continue;
                                    foreach ($escuelasPrivadasPorNivel[$codigo] as $escuela):
                                        // Formatear nombre del municipio
                                        $municipioFormateado = mb_convert_case(strtolower($escuela['municipio']), MB_CASE_TITLE, 'UTF-8');
                                        $municipioFormateado = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $municipioFormateado);
                                        ?>
                                        <tr data-nivel="<?php echo $codigo; ?>"
                                            data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                            data-cct="<?php echo $escuela['cv_cct']; ?>"
                                            data-turno="<?php echo isset($escuela['turno']) ? strtolower($escuela['turno']) : ''; ?>"
                                            data-municipio="<?php echo strtolower($escuela['municipio']); ?>"
                                            data-hombres="<?php echo $escuela['alumnos_hombres']; ?>"
                                            data-mujeres="<?php echo $escuela['alumnos_mujeres']; ?>">
                                            <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                            <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                            <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                            <td class="turno-escuela">
                                                <?php echo isset($escuela['turno']) ? $escuela['turno'] : 'N/A'; ?>
                                            </td>
                                            <td class="municipio-nombre"><?php echo $municipioFormateado; ?></td>
                                            <td class="localidad-nombre"><?php echo $escuela['localidad']; ?></td>
                                            <td class="sector-privado"><?php echo number_format($escuela['total_alumnos']); ?>
                                            </td>
                                            <td class="alumnos-hombres">
                                                <?php echo number_format($escuela['alumnos_hombres']); ?>
                                            </td>
                                            <td class="alumnos-mujeres">
                                                <?php echo number_format($escuela['alumnos_mujeres']); ?>
                                            </td>
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
                                                        href="https://www.instagram.com/gobiernoqueretaro/"
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
    </div>

    <script src="./js/sidebar.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/directorio_estatal.js"></script>
</body></html>