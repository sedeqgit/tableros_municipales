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

// Incluir el archivo de conexión de prueba y session helper
require_once 'conexion_prueba_2024.php';
require_once 'session_helper.php';

// Inicializar sesión demo
iniciarSesionDemo();

// Obtener lista de municipios usando la función de prueba
$todosLosMunicipios = obtenerMunicipiosPrueba2024();

// Definir municipios principales que se mostrarán inicialmente
$municipiosPrincipales = ['CORREGIDORA', 'QUERÉTARO', 'EL MARQUÉS', 'SAN JUAN DEL RÍO'];

// Filtrar municipios adicionales (excluyendo los principales)
$municipiosAdicionales = array_filter($todosLosMunicipios, function ($municipio) use ($municipiosPrincipales) {
    return !in_array(strtoupper($municipio), $municipiosPrincipales);
});

/**
 * Formatea nombres de municipios para display en formato título
 */
function formatearNombreMunicipioPrueba($municipio)
{
    // Convertir a minúsculas y luego aplicar ucwords
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');
    
    // Corrección específica para preposiciones que deben estar en minúsculas
    $formatted = str_replace([' De ', ' Del '], [' de ', ' del '], $formatted);
    
    return $formatted;
}

/**
 * Obtiene datos básicos de un municipio para la tarjeta
 */
function obtenerDatosMunicipioPrueba($municipio)
{
    if (strtoupper($municipio) === 'CORREGIDORA') {
        $datos = obtenerDatosCompletos2024($municipio);
        return [
            'escuelas' => $datos['datos_educativos']['total_escuelas'] ?? 0,
            'alumnos' => $datos['datos_educativos']['total_alumnos'] ?? 0,
            'docentes' => $datos['docentes']['total_general'] ?? 0
        ];
    } else {
        // Datos de placeholder para otros municipios
        return [
            'escuelas' => 0,
            'alumnos' => 0,
            'docentes' => 0
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
    </style>
</head>

<body>
    <div class="municipios-container">
        <!-- Header de la página -->
        <div class="municipios-header">
            <h1><i class="fas fa-map-marker-alt"></i> Municipios de Querétaro</h1>
            <p>Sistema de prueba con datos del esquema 2024</p>
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
                <div class="stat-number">4</div>
                <div class="stat-label">Con datos activos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">14</div>
                <div class="stat-label">En desarrollo</div>
            </div>
        </div>

        <!-- Grid de municipios -->
        <div class="municipios-grid">
            <?php
            // Generar tarjetas para municipios principales
            foreach ($municipiosPrincipales as $municipio) {
                $municipioNormalizado = formatearNombreMunicipioPrueba($municipio);
                $isCorregidora = (strtoupper($municipio) === 'CORREGIDORA');
                $datosMunicipio = obtenerDatosMunicipioPrueba($municipio);
                ?>
                <div class="municipality-card">
                    <div class="municipality-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="municipality-info">
                        <h3><?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p>Estadísticas educativas del municipio de
                            <?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?>.
                        </p>
                        <div class="municipality-stats">
                            <div class="stat">
                                <i class="fas fa-school"></i>
                                <?php
                                if ($isCorregidora) {
                                    echo number_format($datosMunicipio['escuelas'], 0, '.', ',');
                                } else {
                                    echo '<span class="coming-soon">Próximamente</span>';
                                }
                                ?>
                            </div>
                            <div class="stat">
                                <i class="fas fa-user-graduate"></i>
                                <?php
                                if ($isCorregidora) {
                                    echo number_format($datosMunicipio['alumnos'], 0, '.', ',');
                                } else {
                                    echo '<span class="coming-soon">Próximamente</span>';
                                }
                                ?>
                            </div>
                            <div class="stat">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <?php
                                if ($isCorregidora) {
                                    echo number_format($datosMunicipio['docentes'], 0, '.', ',');
                                } else {
                                    echo '<span class="coming-soon">Próximamente</span>';
                                }
                                ?>
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
                ?>
                <div class="municipality-card">
                    <div class="municipality-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="municipality-info">
                        <h3><?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p>Estadísticas educativas del municipio de
                            <?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?>.
                        </p>
                        <div class="municipality-stats">
                            <div class="stat">
                                <i class="fas fa-school"></i>
                                <span class="coming-soon">Pendiente</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-user-graduate"></i>
                                <span class="coming-soon">Pendiente</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span class="coming-soon">Pendiente</span>
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
        <div style="background-color: var(--white); border-radius: var(--card-border-radius); padding: 20px; margin-top: 30px; box-shadow: var(--shadow-sm); text-align: center; color: var(--text-secondary);">
            <p><strong>Nota:</strong> Esta es una versión de prueba que utiliza el esquema de datos 2024.</p>
            <p><strong>Municipios encontrados:</strong> <?php echo count($todosLosMunicipios); ?> de 18 oficiales</p>
            <p><strong>Fecha de consulta:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>

</html>