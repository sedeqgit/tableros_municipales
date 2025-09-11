<?php
/**
 * =============================================================================
 * PÁGINA DE COMPARACIÓN ENTRE MUNICIPIOS - ESQUEMA 2024
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Página que permite comparar métricas educativas entre 2 municipios
 * usando las mismas consultas del sistema principal con datos del esquema 2024.
 * 
 * @author Sistema SEDEQ
 * @version 1.0
 * @since 2025
 */

// Incluir archivo de conexión de prueba
require_once 'conexion_prueba_2024.php';

// Inicializar sesión simple para pruebas
if (!isset($_SESSION)) {
    session_start();
}

// Obtener lista de municipios
$todosLosMunicipios = obtenerMunicipiosPrueba2024();
sort($todosLosMunicipios);

// Obtener parámetros de comparación (hasta 3 municipios)
$municipio1 = isset($_GET['municipio1']) ? $_GET['municipio1'] : '';
$municipio2 = isset($_GET['municipio2']) ? $_GET['municipio2'] : '';
$municipio3 = isset($_GET['municipio3']) ? $_GET['municipio3'] : '';

// Variables para los datos de comparación
$datos1 = null;
$datos2 = null;
$datos3 = null;
$mostrarComparacion = false;
$numMunicipios = 0;

// Array de municipios válidos (no vacíos y únicos)
$municipiosSeleccionados = array_filter(array_unique([$municipio1, $municipio2, $municipio3]), function($m) {
    return !empty($m);
});

$numMunicipios = count($municipiosSeleccionados);

// Si se han seleccionado al menos 2 municipios válidos, obtener los datos
if ($numMunicipios >= 2) {
    $datos1 = obtenerResumenMunicipioCompleto($municipiosSeleccionados[0]);
    $datos2 = obtenerResumenMunicipioCompleto($municipiosSeleccionados[1]);
    
    if ($numMunicipios >= 3) {
        $datos3 = obtenerResumenMunicipioCompleto($municipiosSeleccionados[2]);
    }
    
    $mostrarComparacion = ($datos1 && $datos2 && ($numMunicipios < 3 || $datos3));
    
    // Actualizar las variables individuales para mantener compatibilidad
    $municipio1 = $municipiosSeleccionados[0];
    $municipio2 = $municipiosSeleccionados[1];
    if ($numMunicipios >= 3) {
        $municipio3 = $municipiosSeleccionados[2];
    }
}

/**
 * Formatea nombres de municipios para display
 */
function formatearNombreMunicipio($municipio)
{
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');
    $formatted = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $formatted);
    return $formatted;
}

/**
 * Calcula porcentaje de diferencia entre dos valores
 */
function calcularDiferencia($valor1, $valor2)
{
    if ($valor2 == 0) return $valor1 > 0 ? 100 : 0;
    return round((($valor1 - $valor2) / $valor2) * 100, 1);
}

/**
 * Obtiene información del ciclo escolar
 */
$infoCiclo = obtenerInfoCicloEscolar();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparación de Municipios - SEDEQ | Esquema 2024</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos específicos para comparación */
        .comparacion-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--light-gray);
            min-height: 100vh;
        }

        .comparacion-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-lg);
            text-align: center;
        }

        .comparacion-header h1 {
            color: var(--white);
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .comparacion-header p {
            color: rgba(255, 255, 255, 0.9);
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

        /* Formulario de selección */
        .selector-form {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
        }

        .selector-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 20px;
            align-items: end;
        }

        .selector-group {
            display: flex;
            flex-direction: column;
        }

        .selector-group label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .selector-group select {
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            background-color: var(--white);
            font-size: 1rem;
            color: var(--text-primary);
            transition: all var(--transition-speed);
        }

        .selector-group select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(51, 153, 204, 0.1);
        }

        .compare-button {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .compare-button:hover {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-aqua));
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Resultados de comparación */
        .comparacion-results {
            display: grid;
            grid-template-columns: repeat(var(--municipios-count, 2), 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }

        .comparacion-results.tres-municipios {
            grid-template-columns: repeat(3, 1fr);
        }

        .municipio-card {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 25px;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-speed);
        }

        .municipio-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .municipio-card.municipio-1 {
            border-left: 4px solid var(--primary-blue);
        }

        .municipio-card.municipio-2 {
            border-left: 4px solid var(--accent-aqua);
        }

        .municipio-card.municipio-3 {
            border-left: 4px solid #9b59b6;
        }

        .municipio-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .municipio-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: var(--white);
        }

        .municipio-card.municipio-1 .municipio-icon {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        }

        .municipio-card.municipio-2 .municipio-icon {
            background: linear-gradient(135deg, var(--accent-aqua), var(--secondary-blue));
        }

        .municipio-card.municipio-3 .municipio-icon {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }

        .municipio-name {
            color: var(--text-primary);
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
        }

        .municipio-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tabla de comparación detallada */
        .comparacion-table {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 30px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .table-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .table-header h3 {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .comparison-table th,
        .comparison-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .comparison-table th {
            background-color: var(--light-gray);
            color: var(--text-primary);
            font-weight: 600;
        }

        .comparison-table tr:hover {
            background-color: rgba(51, 153, 204, 0.05);
        }

        .diferencia-positiva {
            color: #27ae60;
            font-weight: 600;
        }

        .diferencia-negativa {
            color: #e74c3c;
            font-weight: 600;
        }

        .diferencia-neutral {
            color: var(--text-secondary);
        }

        /* Gráficos de comparación visual */
        .chart-container {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 30px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .chart-bars {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }

        .chart-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chart-label {
            min-width: 120px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .chart-bars-container {
            flex: 1;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .chart-bar {
            height: 30px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            min-width: 80px;
            transition: all var(--transition-speed);
        }

        .chart-bar.bar-1 {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        }

        .chart-bar.bar-2 {
            background: linear-gradient(135deg, var(--accent-aqua), var(--secondary-blue));
        }

        .chart-bar.bar-3 {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }

        /* Estados vacíos */
        .estado-vacio {
            text-align: center;
            padding: 60px 30px;
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-sm);
            color: var(--text-secondary);
        }

        .estado-vacio i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        .estado-vacio h3 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .selector-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .comparacion-results,
            .comparacion-results.tres-municipios {
                grid-template-columns: 1fr;
            }

            .municipio-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .chart-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .chart-label {
                min-width: auto;
            }

            .chart-bars-container {
                width: 100%;
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="comparacion-container">
        <!-- Header -->
        <div class="comparacion-header">
            <h1><i class="fas fa-balance-scale"></i> Comparación de Municipios</h1>
            <p>Análisis comparativo de estadísticas educativas - <?php echo $infoCiclo['descripcion']; ?></p>
        </div>

        <!-- Botón de regreso -->
        <a href="home.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar a home
        </a>

        <!-- Formulario de selección -->
        <div class="selector-form">
            <form method="GET" action="">
                <div class="selector-grid">
                    <div class="selector-group">
                        <label for="municipio1"><i class="fas fa-city"></i> Primer Municipio:</label>
                        <select name="municipio1" id="municipio1" required>
                            <option value="">Seleccionar municipio...</option>
                            <?php foreach ($todosLosMunicipios as $municipio): ?>
                                <option value="<?php echo htmlspecialchars($municipio); ?>" 
                                        <?php echo ($municipio1 === $municipio) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(formatearNombreMunicipio($municipio)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="selector-group">
                        <label for="municipio2"><i class="fas fa-city"></i> Segundo Municipio:</label>
                        <select name="municipio2" id="municipio2" required>
                            <option value="">Seleccionar municipio...</option>
                            <?php foreach ($todosLosMunicipios as $municipio): ?>
                                <option value="<?php echo htmlspecialchars($municipio); ?>" 
                                        <?php echo ($municipio2 === $municipio) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(formatearNombreMunicipio($municipio)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="selector-group">
                        <label for="municipio3"><i class="fas fa-city"></i> Tercer Municipio (Opcional):</label>
                        <select name="municipio3" id="municipio3">
                            <option value="">Seleccionar municipio...</option>
                            <?php foreach ($todosLosMunicipios as $municipio): ?>
                                <option value="<?php echo htmlspecialchars($municipio); ?>" 
                                        <?php echo (!empty($municipio3) && $municipio3 === $municipio) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(formatearNombreMunicipio($municipio)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="compare-button">
                        <i class="fas fa-chart-bar"></i> Comparar
                    </button>
                </div>
            </form>
        </div>

        <?php if ($mostrarComparacion): ?>
            <!-- Tarjetas de municipios -->
            <div class="comparacion-results <?php echo ($numMunicipios >= 3) ? 'tres-municipios' : ''; ?>">
                <!-- Municipio 1 -->
                <div class="municipio-card municipio-1">
                    <div class="municipio-header">
                        <div class="municipio-icon">
                            <i class="fas fa-city"></i>
                        </div>
                        <h2 class="municipio-name"><?php echo htmlspecialchars(formatearNombreMunicipio($municipio1)); ?></h2>
                    </div>
                    <div class="municipio-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos1['total_escuelas'], 0, '.', ','); ?></div>
                            <div class="stat-label">Escuelas</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos1['total_matricula'], 0, '.', ','); ?></div>
                            <div class="stat-label">Alumnos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos1['total_docentes'], 0, '.', ','); ?></div>
                            <div class="stat-label">Docentes</div>
                        </div>
                    </div>
                </div>

                <!-- Municipio 2 -->
                <div class="municipio-card municipio-2">
                    <div class="municipio-header">
                        <div class="municipio-icon">
                            <i class="fas fa-city"></i>
                        </div>
                        <h2 class="municipio-name"><?php echo htmlspecialchars(formatearNombreMunicipio($municipio2)); ?></h2>
                    </div>
                    <div class="municipio-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos2['total_escuelas'], 0, '.', ','); ?></div>
                            <div class="stat-label">Escuelas</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos2['total_matricula'], 0, '.', ','); ?></div>
                            <div class="stat-label">Alumnos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos2['total_docentes'], 0, '.', ','); ?></div>
                            <div class="stat-label">Docentes</div>
                        </div>
                    </div>
                </div>

                <!-- Municipio 3 (condicional) -->
                <?php if ($numMunicipios >= 3 && $datos3): ?>
                <div class="municipio-card municipio-3">
                    <div class="municipio-header">
                        <div class="municipio-icon">
                            <i class="fas fa-city"></i>
                        </div>
                        <h2 class="municipio-name"><?php echo htmlspecialchars(formatearNombreMunicipio($municipio3)); ?></h2>
                    </div>
                    <div class="municipio-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos3['total_escuelas'], 0, '.', ','); ?></div>
                            <div class="stat-label">Escuelas</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos3['total_matricula'], 0, '.', ','); ?></div>
                            <div class="stat-label">Alumnos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($datos3['total_docentes'], 0, '.', ','); ?></div>
                            <div class="stat-label">Docentes</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tabla de comparación detallada -->
            <div class="comparacion-table">
                <div class="table-header">
                    <h3><i class="fas fa-table"></i> Comparación Detallada</h3>
                    <p>Diferencias porcentuales entre los municipios seleccionados</p>
                </div>
                
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>Métrica</th>
                            <th><?php echo htmlspecialchars(formatearNombreMunicipio($municipio1)); ?></th>
                            <th><?php echo htmlspecialchars(formatearNombreMunicipio($municipio2)); ?></th>
                            <?php if ($numMunicipios >= 3 && $datos3): ?>
                            <th><?php echo htmlspecialchars(formatearNombreMunicipio($municipio3)); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Escuelas</strong></td>
                            <td><?php echo number_format($datos1['total_escuelas'], 0, '.', ','); ?></td>
                            <td><?php echo number_format($datos2['total_escuelas'], 0, '.', ','); ?></td>
                            <?php if ($numMunicipios >= 3 && $datos3): ?>
                            <td><?php echo number_format($datos3['total_escuelas'], 0, '.', ','); ?></td>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <td><strong>Alumnos</strong></td>
                            <td><?php echo number_format($datos1['total_matricula'], 0, '.', ','); ?></td>
                            <td><?php echo number_format($datos2['total_matricula'], 0, '.', ','); ?></td>
                            <?php if ($numMunicipios >= 3 && $datos3): ?>
                            <td><?php echo number_format($datos3['total_matricula'], 0, '.', ','); ?></td>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <td><strong>Docentes</strong></td>
                            <td><?php echo number_format($datos1['total_docentes'], 0, '.', ','); ?></td>
                            <td><?php echo number_format($datos2['total_docentes'], 0, '.', ','); ?></td>
                            <?php if ($numMunicipios >= 3 && $datos3): ?>
                            <td><?php echo number_format($datos3['total_docentes'], 0, '.', ','); ?></td>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Gráfico de barras visual -->
            <div class="chart-container">
                <div class="table-header">
                    <h3><i class="fas fa-chart-bar"></i> Comparación Visual</h3>
                    <p>Representación gráfica de las métricas principales</p>
                </div>
                
                <div class="chart-bars">
                    <?php
                    // Calcular el máximo considerando todos los municipios
                    $valores_comparacion = [$datos1, $datos2];
                    if ($numMunicipios >= 3 && $datos3) {
                        $valores_comparacion[] = $datos3;
                    }
                    
                    $metricas = [
                        'Escuelas' => 'total_escuelas',
                        'Alumnos' => 'total_matricula', 
                        'Docentes' => 'total_docentes'
                    ];
                    
                    foreach ($metricas as $label => $campo):
                        // Encontrar el valor máximo entre todos los municipios para esta métrica
                        $valores_metrica = array_map(function($datos) use ($campo) {
                            return $datos[$campo];
                        }, $valores_comparacion);
                        $max_val = max($valores_metrica);
                        
                        $val1 = $datos1[$campo];
                        $val2 = $datos2[$campo];
                        $width1 = $max_val > 0 ? ($val1 / $max_val) * 100 : 0;
                        $width2 = $max_val > 0 ? ($val2 / $max_val) * 100 : 0;
                        
                        // Valores y ancho para el tercer municipio (si existe)
                        if ($numMunicipios >= 3 && $datos3) {
                            $val3 = $datos3[$campo];
                            $width3 = $max_val > 0 ? ($val3 / $max_val) * 100 : 0;
                        }
                    ?>
                        <div class="chart-row">
                            <div class="chart-label"><?php echo $label; ?>:</div>
                            <div class="chart-bars-container">
                                <div class="chart-bar bar-1" style="width: <?php echo $width1; ?>%;">
                                    <?php echo number_format($val1, 0, '.', ','); ?>
                                </div>
                                <div class="chart-bar bar-2" style="width: <?php echo $width2; ?>%;">
                                    <?php echo number_format($val2, 0, '.', ','); ?>
                                </div>
                                <?php if ($numMunicipios >= 3 && $datos3): ?>
                                <div class="chart-bar bar-3" style="width: <?php echo $width3; ?>%;">
                                    <?php echo number_format($val3, 0, '.', ','); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Leyenda -->
                <div style="display: flex; justify-content: center; gap: 30px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color); flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)); border-radius: 3px;"></div>
                        <span><?php echo htmlspecialchars(formatearNombreMunicipio($municipio1)); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background: linear-gradient(135deg, var(--accent-aqua), var(--secondary-blue)); border-radius: 3px;"></div>
                        <span><?php echo htmlspecialchars(formatearNombreMunicipio($municipio2)); ?></span>
                    </div>
                    <?php if ($numMunicipios >= 3 && $datos3): ?>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #9b59b6, #8e44ad); border-radius: 3px;"></div>
                        <span><?php echo htmlspecialchars(formatearNombreMunicipio($municipio3)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (!empty($municipio1) && !empty($municipio2) && $municipio1 === $municipio2): ?>
            <!-- Error: mismo municipio seleccionado -->
            <div class="estado-vacio">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Municipios Idénticos</h3>
                <p>Por favor selecciona dos municipios diferentes para realizar la comparación.</p>
            </div>

        <?php elseif (!empty($municipio1) || !empty($municipio2)): ?>
            <!-- Error: datos no encontrados -->
            <div class="estado-vacio">
                <i class="fas fa-database"></i>
                <h3>Datos No Disponibles</h3>
                <p>No se pudieron obtener los datos de comparación para los municipios seleccionados. Verifica que ambos municipios tengan información disponible.</p>
            </div>

        <?php else: ?>
            <!-- Estado inicial -->
            <div class="estado-vacio">
                <i class="fas fa-search"></i>
                <h3>Selecciona Dos Municipios</h3>
                <p>Elige dos municipios diferentes en el formulario de arriba para comenzar la comparación de sus estadísticas educativas.</p>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div style="background-color: var(--white); border-radius: var(--card-border-radius); padding: 20px; margin-top: 30px; box-shadow: var(--shadow-sm); text-align: center; color: var(--text-secondary);">
            <p><strong>Sistema de Comparación:</strong> Análisis estadístico entre municipios de Querétaro</p>
            <p><strong>Fecha de consulta:</strong> <?php
            date_default_timezone_set('America/Mexico_City');
            $meses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
                      7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
            $dia = date('j');
            $mes = $meses[date('n')];
            $año = date('Y');
            $hora = date('H:i:s');
            echo "$dia de $mes de $año, $hora hrs";
            ?></p>
        </div>
    </div>

    <script>
        // Prevenir selección del mismo municipio en los 3 selects
        function actualizarOpcionesDisponibles() {
            const municipio1 = document.getElementById('municipio1');
            const municipio2 = document.getElementById('municipio2');
            const municipio3 = document.getElementById('municipio3');
            
            const valores = [municipio1.value, municipio2.value, municipio3.value];
            const selects = [municipio1, municipio2, municipio3];
            
            selects.forEach((select, index) => {
                Array.from(select.options).forEach(option => {
                    // Habilitar todas las opciones primero
                    option.disabled = false;
                    
                    // Deshabilitar si está seleccionada en otro select
                    if (option.value && valores.includes(option.value) && option.value !== select.value) {
                        option.disabled = true;
                    }
                });
            });
        }

        document.getElementById('municipio1').addEventListener('change', actualizarOpcionesDisponibles);
        document.getElementById('municipio2').addEventListener('change', actualizarOpcionesDisponibles);
        document.getElementById('municipio3').addEventListener('change', actualizarOpcionesDisponibles);

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            actualizarOpcionesDisponibles();
        });
    </script>
</body>
</html>