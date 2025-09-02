<?php
/**
 * =============================================================================
 * PÁGINA DE PRUEBA DE MUNICIPIOS - ESQUEMA 2024
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Página simplificada que muestra las tarjetas de todos los municipios
 * usando las mismas consultas del archivo home.php pero con datos del esquema 2024.
 * 
 * @author Sistema SEDEQ
 * @version 1.0
 * @since 2024
 */

// Incluir solo el archivo de conexión de prueba
require_once 'conexion_prueba_2024.php';

// Inicializar sesión simple para pruebas
if (!isset($_SESSION)) {
    session_start();
}

// Obtener lista de municipios usando la función de prueba
$todosLosMunicipios = obtenerMunicipiosPrueba2024();

// Obtener datos estatales completos
$datosEstado = obtenerResumenEstadoCompleto();
$infoCiclo = obtenerInfoCicloEscolar();

// Ordenar todos los municipios alfabéticamente en una sola lista
$todosLosMunicipiosOrdenados = $todosLosMunicipios;
sort($todosLosMunicipiosOrdenados);

/**
 * Formatea nombres de municipios para display en formato título
 * Convierte de MAYÚSCULAS (nuestro formato interno) a Formato Título para mostrar
 */
function formatearNombreMunicipioPrueba($municipio)
{
    // Convertir de mayúsculas a formato título
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');

    // Correcciones específicas para preposiciones y artículos
    $formatted = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $formatted);

    return $formatted;
}

/**
 * Obtiene datos básicos de un municipio usando la nueva estructura bolsillo
 */
function obtenerDatosMunicipioPrueba($municipio)
{
    try {
        // Obtener información del ciclo escolar actual
        $infoCiclo = obtenerInfoCicloEscolar();

        // Usar la nueva función de resumen completo que replica la lógica de bolsillo
        $resumenCompleto = obtenerResumenMunicipioCompleto($municipio);

        if (!$resumenCompleto) {
            // Si no hay datos, devolver estructura vacía
            return [
                'escuelas' => 0,
                'alumnos' => 0,
                'docentes' => 0,
                'ciclo_escolar' => $infoCiclo['ciclo_corto'],
                'tiene_error' => true
            ];
        }

        return [
            'escuelas' => $resumenCompleto['total_escuelas'],
            'alumnos' => $resumenCompleto['total_matricula'],
            'docentes' => $resumenCompleto['total_docentes'],
            'ciclo_escolar' => $infoCiclo['ciclo_corto'],
            'tiene_error' => false,
            // Datos adicionales por nivel (para uso futuro)
            'detalle_niveles' => [
                'inicial_esc' => $resumenCompleto['inicial_esc'],
                'inicial_no_esc' => $resumenCompleto['inicial_no_esc'],
                'preescolar' => $resumenCompleto['preescolar'],
                'primaria' => $resumenCompleto['primaria'],
                'secundaria' => $resumenCompleto['secundaria'],
                'media_sup' => $resumenCompleto['media_sup'],
                'superior' => $resumenCompleto['superior'],
                'especial' => $resumenCompleto['especial']
            ]
        ];
    } catch (Exception $e) {
        // Manejo de errores para municipios sin datos
        error_log("Error obteniendo datos para $municipio: " . $e->getMessage());
        $infoCiclo = obtenerInfoCicloEscolar();
        return [
            'escuelas' => 0,
            'alumnos' => 0,
            'docentes' => 0,
            'ciclo_escolar' => $infoCiclo['ciclo_corto'],
            'tiene_error' => true
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipios de Querétaro - Prueba Esquema 2024 | SEDEQ</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos específicos para la página de municipios de prueba */
        .municipios-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--light-gray);
            min-height: 100vh;
        }

        .municipios-header {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
            text-align: center;
        }

        .municipios-header h1 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .municipios-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all var(--transition-speed);
            margin-bottom: 20px;
        }

        .back-button:hover {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-aqua));
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .back-button i {
            margin-right: 8px;
        }

        .municipios-stats {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .stat-item {
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .stat-label {
            color: var(--text-secondary);
            margin-top: 5px;
        }

        /* Reutilizar estilos del grid de home.php */
        .municipios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        /* Estilos uniformes para todas las tarjetas de municipio */
        .municipality-card {
            transition: all var(--transition-speed);
        }

        .municipality-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(51, 153, 204, 0.3);
        }

        /* Indicador visual para municipios con datos */
        .municipality-card.has-data {
            border-left: 4px solid var(--primary-blue);
        }

        .municipality-card.no-data {
            opacity: 0.7;
            border-left: 4px solid var(--text-secondary);
        }

        /* Estilos para la sección estatal */
        .estadisticas-estado {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            border-radius: var(--card-border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-lg);
            color: var(--white);
        }

        .estado-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .estado-header h2 {
            color: var(--white);
            margin-bottom: 8px;
            font-size: 1.8rem;
        }

        .estado-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
        }

        .estado-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .estado-stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            transition: all var(--transition-speed);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .estado-stat-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .estado-stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--accent-aqua);
        }

        .estado-stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 5px;
        }

        .estado-stat-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>
    <div class="municipios-container">
        <!-- Header de la página -->
        <div class="municipios-header">
            <h1><i class="fas fa-map-marker-alt"></i> Municipios de Querétaro</h1>
            <p>Estadística Educativa</p>
        </div>

        <!-- Botón para regresar -->
        <a href="home.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar al Home
        </a>

        <!-- Estadísticas generales -->
        <div class="municipios-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($todosLosMunicipios); ?></div>
                <div class="stat-label">Municipios</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">2024 - 2025</div>
                <div class="stat-label">Ciclo Escolar</div>
            </div>
        </div>

        <!-- NUEVA SECCIÓN: Estadísticas Estatales -->
        <?php if ($datosEstado && !empty($datosEstado)): ?>
            <div class="estadisticas-estado">
                <div class="estado-header">
                    <h2><i class="fas fa-chart-bar"></i> Estadísticas del Estado de Querétaro</h2>
                    <p><?php echo $infoCiclo['descripcion']; ?> - Totales Estatales</p>
                </div>
                <div class="estado-stats-grid">
                    <div class="estado-stat-card">
                        <div class="estado-stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="estado-stat-number">
                            <?php echo number_format($datosEstado['total_matricula'], 0, '.', ','); ?>
                        </div>
                        <div class="estado-stat-label">Alumnos</div>
                    </div>
                    <div class="estado-stat-card">
                        <div class="estado-stat-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="estado-stat-number">
                            <?php echo number_format($datosEstado['total_escuelas'], 0, '.', ','); ?>
                        </div>
                        <div class="estado-stat-label">Escuelas</div>
                    </div>
                    <div class="estado-stat-card">
                        <div class="estado-stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="estado-stat-number">
                            <?php echo number_format($datosEstado['total_docentes'], 0, '.', ','); ?>
                        </div>
                        <div class="estado-stat-label">Docentes</div>
                    </div>
                    <div class="estado-stat-card">
                        <div class="estado-stat-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="estado-stat-number">
                            <?php echo count($todosLosMunicipios); ?>
                        </div>
                        <div class="estado-stat-label">Municipios</div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="estadisticas-estado" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                <div class="estado-header">
                    <h2><i class="fas fa-exclamation-triangle"></i> Estadísticas del Estado</h2>
                    <p>No se pudieron cargar los datos estatales</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Grid de municipios -->
        <div class="municipios-grid">
            <?php
            // Generar tarjetas para todos los municipios en orden alfabético
            foreach ($todosLosMunicipiosOrdenados as $municipio) {
                $municipioNormalizado = formatearNombreMunicipioPrueba($municipio);
                $datosMunicipio = obtenerDatosMunicipioPrueba($municipio);
                $tieneDatos = ($datosMunicipio['alumnos'] > 0 || $datosMunicipio['escuelas'] > 0);
                $claseCard = $tieneDatos ? 'has-data' : 'no-data';
                ?>
                <div class="municipality-card <?php echo $claseCard; ?>">
                    <div class="municipality-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="municipality-info">
                        <h3><?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p>Estadísticas educativas para el municipio de
                            <?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?>.
                        </p>
                        <div class="municipality-stats">
                            <div class="stat">
                                <i class="fas fa-school"></i>
                                <?php echo number_format($datosMunicipio['escuelas'], 0, '.', ','); ?>
                            </div>
                            <div class="stat">
                                <i class="fas fa-user-graduate"></i>
                                <?php echo number_format($datosMunicipio['alumnos'], 0, '.', ','); ?>
                            </div>
                            <div class="stat">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <?php echo number_format($datosMunicipio['docentes'], 0, '.', ','); ?>
                            </div>
                        </div>
                    </div>
                    <a href="prueba_consultas_2024.php?municipio=<?php echo urlencode($municipio); ?>"
                        class="municipality-link">
                        Ver Datos Detallados <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Footer -->
        <div
            style="background-color: var(--white); border-radius: var(--card-border-radius); padding: 20px; margin-top: 30px; box-shadow: var(--shadow-sm); text-align: center; color: var(--text-secondary);">
            <p><strong>Municipios disponibles:</strong> <?php echo count($todosLosMunicipiosOrdenados); ?> de 18
                oficiales de
                Querétaro</p>
            <p><strong>Fecha de consulta:</strong> <?php
            // Configurar zona horaria de México
            date_default_timezone_set('America/Mexico_City');

            // Configurar idioma español para fechas
            $meses = [
                1 => 'enero',
                2 => 'febrero',
                3 => 'marzo',
                4 => 'abril',
                5 => 'mayo',
                6 => 'junio',
                7 => 'julio',
                8 => 'agosto',
                9 => 'septiembre',
                10 => 'octubre',
                11 => 'noviembre',
                12 => 'diciembre'
            ];

            $dia = date('j');
            $mes = $meses[date('n')];
            $año = date('Y');
            $hora = date('H:i:s');

            echo "$dia de $mes de $año, $hora hrs";
            ?></p>
        </div>
    </div>
</body>

</html>