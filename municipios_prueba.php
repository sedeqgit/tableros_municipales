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

// Definir municipios principales que se mostrarán inicialmente (en mayúsculas para coincidir con nuestro mapeo)
$municipiosPrincipales = ['CORREGIDORA', 'QUERÉTARO', 'EL MARQUÉS', 'SAN JUAN DEL RÍO'];

// Filtrar municipios adicionales (excluyendo los principales)
$municipiosAdicionales = array_filter($todosLosMunicipios, function ($municipio) use ($municipiosPrincipales) {
    return !in_array($municipio, $municipiosPrincipales);
});

// Ordenar alfabéticamente los municipios adicionales
sort($municipiosAdicionales);

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
        // Usar la nueva función de resumen completo que replica la lógica de bolsillo
        $resumenCompleto = obtenerResumenMunicipioCompleto($municipio, '24');

        if (!$resumenCompleto) {
            // Si no hay datos, devolver estructura vacía
            return [
                'escuelas' => 0,
                'alumnos' => 0,
                'docentes' => 0,
                'ciclo_escolar' => '24',
                'tiene_error' => true
            ];
        }

        return [
            'escuelas' => $resumenCompleto['total_escuelas'],
            'alumnos' => $resumenCompleto['total_matricula'],
            'docentes' => $resumenCompleto['total_docentes'],
            'ciclo_escolar' => '24',
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
        return [
            'escuelas' => 0,
            'alumnos' => 0,
            'docentes' => 0,
            'ciclo_escolar' => '24',
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
    </style>
</head>

<body>
    <div class="municipios-container">
        <!-- Header de la página -->
        <div class="municipios-header">
            <h1><i class="fas fa-map-marker-alt"></i> Municipios de Querétaro</h1>
            <p>Datos educativos usando consultas dinámicas del esquema 2024</p>
        </div>

        <!-- Botón para regresar -->
        <a href="home.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar al Home
        </a>

        <!-- Estadísticas generales -->
        <div class="municipios-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($todosLosMunicipios); ?></div>
                <div class="stat-label">Municipios Disponibles</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24</div>
                <div class="stat-label">Ciclo Escolar</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">2024</div>
                <div class="stat-label">Esquema DB</div>
            </div>
        </div>

        <!-- Grid de municipios -->
        <div class="municipios-grid">
            <?php
            // Generar tarjetas para municipios principales
            foreach ($municipiosPrincipales as $municipio) {
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
                        <p>Estadísticas educativas usando consultas tipo bolsillo para
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

            // Mostrar municipios adicionales
            foreach ($municipiosAdicionales as $municipio) {
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
                        <p>Estadísticas educativas usando consultas tipo bolsillo para
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

        <!-- Footer informativo -->
        <div
            style="background-color: var(--white); border-radius: var(--card-border-radius); padding: 20px; margin-top: 30px; box-shadow: var(--shadow-sm); text-align: center; color: var(--text-secondary);">
            <p><strong>Municipios disponibles:</strong> <?php echo count($todosLosMunicipios); ?> de 18 oficiales de
                Querétaro</p>
            <p><strong>Esquema utilizado:</strong> nonce_pano_24 (Datos 2024)</p>
            <p><strong>Estructura de datos:</strong> nivel_detalle como bolsillo (tot_mat, tot_doc, tot_esc)</p>
            <p><strong>Fecha de consulta:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>

</html>