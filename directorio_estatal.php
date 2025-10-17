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
    <link rel="stylesheet" href="./css/global.css">
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
    <!-- Overlay para cerrar el menú en móviles -->
    <div class="sidebar-overlay"></div>

    <div class="sidebar">
        <div class="logo-container">
            <img src="./img/layout_set_logo.png" alt="Logo SEDEQ" class="logo">
        </div>
        <div class="sidebar-links">
            <a href="home.php" class="sidebar-link"><i class="fas fa-home"></i> <span>Regresar al Home</span></a>
            <a href="directorio_estatal_prueba.php" class="sidebar-link active">
                <i class="fas fa-school"></i>
                <span>Directorio Estatal</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title top-bar-title">
                <h1>Directorio Estatal de Escuelas - Querétaro</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \\d\\e F \\d\\e Y'); ?></span>
                </div>
            </div>
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
                            <div class="stat-label">Total Escuelas Ciclo escolar <?php echo $infoCiclo['ciclo_completo'] ?? '2024-2025'; ?></div>
                        </div>
                        <div class="stat-box animate-fade delay-2">
                            <div class="stat-value">
                                <span class="public-schools"><?php echo number_format($totalEscuelasPublicas); ?></span>
                                <span class="separator"> / </span>
                                <span class="private-schools"><?php echo number_format($totalEscuelasPrivadas); ?></span>
                            </div>
                            <div class="stat-label">Escuelas Públicas / Privadas</div>
                        </div>
                        <div class="stat-box animate-fade delay-3">
                            <div class="stat-value"><?php echo number_format($totalAlumnos); ?></div>
                            <div class="stat-label">Total de Alumnos</div>
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
                        <div class="export-buttons">
                            <button class="export-btn export-excel" onclick="exportarDirectorioEstatal('excel', 'publicas')" title="Exportar a Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="export-btn export-pdf" onclick="exportarDirectorioEstatal('pdf', 'publicas')" title="Exportar a PDF">
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
                                    <th>Municipio</th>
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
                                        // Formatear nombre del municipio
                                        $municipioFormateado = mb_convert_case(strtolower($escuela['municipio']), MB_CASE_TITLE, 'UTF-8');
                                        $municipioFormateado = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $municipioFormateado);
                                        ?>
                                        <tr data-nivel="<?php echo $codigo; ?>"
                                            data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                            data-cct="<?php echo $escuela['cv_cct']; ?>"
                                            data-municipio="<?php echo strtolower($escuela['municipio']); ?>">
                                            <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                            <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                            <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                            <td class="municipio-nombre"><?php echo $municipioFormateado; ?></td>
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
                        <div class="export-buttons">
                            <button class="export-btn export-excel" onclick="exportarDirectorioEstatal('excel', 'privadas')" title="Exportar a Excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="export-btn export-pdf" onclick="exportarDirectorioEstatal('pdf', 'privadas')" title="Exportar a PDF">
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
                                    <th>Municipio</th>
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
                                        // Formatear nombre del municipio
                                        $municipioFormateado = mb_convert_case(strtolower($escuela['municipio']), MB_CASE_TITLE, 'UTF-8');
                                        $municipioFormateado = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $municipioFormateado);
                                        ?>
                                        <tr data-nivel="<?php echo $codigo; ?>"
                                            data-nombre="<?php echo strtolower($escuela['nombre_escuela']); ?>"
                                            data-cct="<?php echo $escuela['cv_cct']; ?>"
                                            data-municipio="<?php echo strtolower($escuela['municipio']); ?>">
                                            <td class="nivel-nombre"><?php echo $nombreNivel; ?></td>
                                            <td class="cct-codigo"><?php echo $escuela['cv_cct']; ?></td>
                                            <td class="escuela-nombre"><?php echo $escuela['nombre_escuela']; ?></td>
                                            <td class="municipio-nombre"><?php echo $municipioFormateado; ?></td>
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
            <p>&copy; <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
        </footer>
    </div>

    <script src="./js/sidebar.js"></script>
    <script src="./js/animations_global.js"></script>
    <script src="./js/directorio_estatal.js"></script>
</body>

</html>
