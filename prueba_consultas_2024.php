<?php
/**
 * =============================================================================
 * PÁGINA DE PRUEBA PARA CONSULTAS ESQUEMA 2024
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Esta página muestra los datos obtenidos usando las consultas actualizadas
 * al esquema 2024 para docentes, escuelas y matrícula de alumnos.
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

// Obtener el municipio desde el parámetro GET, por defecto Corregidora
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'CORREGIDORA';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'CORREGIDORA'; // Fallback a Corregidora si el municipio no es válido
}

try {
    // Obtener datos completos usando la nueva función que replica bolsillo exactamente
    $datosCompletos = obtenerResumenMunicipioCompleto($municipioSeleccionado, '24');

    // Verificar si hay datos
    $hayError = !$datosCompletos;
    $tieneDatos = $datosCompletos && $datosCompletos['total_matricula'] > 0;

    // Consolidar información del municipio
    if ($tieneDatos) {
        $datosMunicipio = [
            'municipio' => $municipioSeleccionado,
            'datos_completos' => [
                'totales' => [
                    'total_alumnos' => $datosCompletos['total_matricula'],
                    'total_docentes' => $datosCompletos['total_docentes'],
                    'total_escuelas' => $datosCompletos['total_escuelas']
                ]
            ],
            'datos_por_nivel' => [
                'niveles' => [
                    $datosCompletos['inicial_esc'],
                    $datosCompletos['inicial_no_esc'],
                    $datosCompletos['preescolar'],
                    $datosCompletos['primaria'],
                    $datosCompletos['secundaria'],
                    $datosCompletos['media_sup'],
                    $datosCompletos['superior'],
                    $datosCompletos['especial']
                ]
            ],
            'fecha_consulta' => date('Y-m-d H:i:s'),
            'tiene_datos' => true
        ];
    } else {
        $datosMunicipio = [
            'municipio' => $municipioSeleccionado,
            'datos_completos' => ['totales' => ['total_alumnos' => 0, 'total_docentes' => 0, 'total_escuelas' => 0]],
            'datos_por_nivel' => ['niveles' => []],
            'fecha_consulta' => date('Y-m-d H:i:s'),
            'tiene_datos' => false,
            'error' => 'No se encontraron datos para este municipio'
        ];
    }

} catch (Exception $e) {
    // Manejo de errores
    error_log("Error obteniendo datos para $municipioSeleccionado: " . $e->getMessage());
    $datosMunicipio = [
        'municipio' => $municipioSeleccionado,
        'datos_completos' => ['totales' => ['total_alumnos' => 0, 'total_docentes' => 0, 'total_escuelas' => 0]],
        'datos_por_nivel' => ['niveles' => []],
        'fecha_consulta' => date('Y-m-d H:i:s'),
        'tiene_datos' => false,
        'error' => $e->getMessage()
    ];
}

// Función auxiliar para formatear números
function formatearNumero($numero)
{
    return number_format($numero, 0, '.', ',');
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Consultas 2024 | SEDEQ</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos específicos para la página de pruebas */
        .prueba-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--light-gray);
            min-height: 100vh;
        }

        .prueba-header {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
            text-align: center;
        }

        .prueba-header h1 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .prueba-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .datos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .datos-card {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 25px;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }

        .datos-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
            margin-right: 15px;
        }

        .docentes-card .card-icon {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        }

        .escuelas-card .card-icon {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-aqua));
        }

        .matricula-card .card-icon {
            background: linear-gradient(135deg, var(--accent-aqua), var(--primary-blue));
        }

        .card-title {
            color: var(--text-primary);
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }

        .total-general {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 20px;
        }

        .detalle-nivel {
            margin-bottom: 12px;
            padding: 10px;
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nivel-nombre {
            color: var(--text-primary);
            font-weight: 500;
        }

        .nivel-cantidad {
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .info-footer {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            text-align: center;
            color: var(--text-secondary);
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 15px;
            border-radius: var(--border-radius);
            border-left: 4px solid #c33;
            margin-bottom: 20px;
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

        /* Estilos uniformes para todas las tarjetas */
        .datos-card {
            position: relative;
            border-left: 4px solid var(--primary-blue);
        }

        .datos-card.no-data {
            opacity: 0.6;
            border-left: 4px solid var(--text-secondary);
        }

        /* Indicador de estructura bolsillo */
        .bolsillo-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="prueba-container">
        <!-- Header de la página -->
        <div class="prueba-header">
            <h1><i class="fas fa-database"></i> Sistema de Consultas Dinámicas 2024 - 2025</h1>
            <p><strong>Municipio:</strong>
                <?php echo htmlspecialchars($datosMunicipio['municipio'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Esquema utilizado:</strong> nonce_pano_24 (Datos 2024) - Estructura bolsillo</p>
            <?php if (!$datosMunicipio['tiene_datos']): ?>
                <p style="color: #856404; background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> Este municipio no tiene datos disponibles en el ciclo escolar 2024
                </p>
            <?php endif; ?>
        </div>

        <!-- Botón para regresar -->
        <a href="municipios_prueba.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar a Municipios
        </a>

        <!-- Grid principal de datos -->
        <div class="datos-grid">
            <!-- Tarjeta de Docentes -->
            <div class="datos-card docentes-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="card-title">Docentes</h3>
                </div>

                <div class="bolsillo-indicator">Bolsillo</div>

                <div class="total-general">
                    <?php
                    $totalDocentes = isset($datosMunicipio['datos_completos']['totales']['total_docentes'])
                        ? $datosMunicipio['datos_completos']['totales']['total_docentes']
                        : 0;
                    echo formatearNumero($totalDocentes);
                    ?>
                </div>

                <?php if (isset($datosMunicipio['datos_por_nivel']['niveles']) && !empty($datosMunicipio['datos_por_nivel']['niveles'])): ?>
                    <div class="detalles-niveles">
                        <?php foreach ($datosMunicipio['datos_por_nivel']['niveles'] as $nivel): ?>
                            <?php if (isset($nivel['tot_doc']) && $nivel['tot_doc'] > 0): ?>
                                <div class="detalle-nivel">
                                    <span class="nivel-nombre">
                                        <?php echo htmlspecialchars($nivel['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <span class="nivel-cantidad"><?php echo formatearNumero($nivel['tot_doc']); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="error-message"
                        style="background-color: #fff3cd; color: #856404; border-left-color: #ffc107;">
                        <i class="fas fa-info-circle"></i>
                        No hay datos disponibles para este municipio en el ciclo 2024.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tarjeta de Escuelas -->
            <div class="datos-card escuelas-card <?php echo !$datosMunicipio['tiene_datos'] ? 'no-data' : ''; ?>">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <h3 class="card-title">Escuelas</h3>
                </div>

                <div class="bolsillo-indicator">Bolsillo</div>

                <div class="total-general">
                    <?php
                    $totalEscuelas = isset($datosMunicipio['datos_completos']['totales']['total_escuelas'])
                        ? $datosMunicipio['datos_completos']['totales']['total_escuelas']
                        : 0;
                    echo formatearNumero($totalEscuelas);
                    ?>
                </div>

                <?php if (isset($datosMunicipio['datos_por_nivel']['niveles']) && !empty($datosMunicipio['datos_por_nivel']['niveles'])): ?>
                    <div class="detalles-niveles">
                        <?php foreach ($datosMunicipio['datos_por_nivel']['niveles'] as $nivel): ?>
                            <?php if (isset($nivel['tot_esc']) && $nivel['tot_esc'] > 0): ?>
                                <div class="detalle-nivel">
                                    <span
                                        class="nivel-nombre"><?php echo htmlspecialchars($nivel['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="nivel-cantidad"><?php echo formatearNumero($nivel['tot_esc']); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="error-message"
                        style="background-color: #fff3cd; color: #856404; border-left-color: #ffc107;">
                        <i class="fas fa-info-circle"></i>
                        No hay datos disponibles para este municipio.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tarjeta de Matrícula -->
            <div class="datos-card matricula-card <?php echo !$datosMunicipio['tiene_datos'] ? 'no-data' : ''; ?>">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="card-title">Matrícula de Alumnos</h3>
                </div>

                <div class="bolsillo-indicator">Bolsillo</div>

                <div class="total-general">
                    <?php
                    $totalAlumnos = isset($datosMunicipio['datos_completos']['totales']['total_alumnos'])
                        ? $datosMunicipio['datos_completos']['totales']['total_alumnos']
                        : 0;
                    echo formatearNumero($totalAlumnos);
                    ?>
                </div>

                <?php if (isset($datosMunicipio['datos_por_nivel']['niveles']) && !empty($datosMunicipio['datos_por_nivel']['niveles'])): ?>
                    <div class="detalles-niveles">
                        <?php foreach ($datosMunicipio['datos_por_nivel']['niveles'] as $nivel): ?>
                            <?php if (isset($nivel['tot_mat']) && $nivel['tot_mat'] > 0): ?>
                                <div class="detalle-nivel">
                                    <span
                                        class="nivel-nombre"><?php echo htmlspecialchars($nivel['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="nivel-cantidad"><?php echo formatearNumero($nivel['tot_mat']); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="error-message"
                        style="background-color: #fff3cd; color: #856404; border-left-color: #ffc107;">
                        <i class="fas fa-info-circle"></i>
                        No hay datos disponibles para este municipio.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- NUEVA SECCIÓN: Desglose Público/Privado -->
        <div style="margin-top: 40px;">
            <h2 style="text-align: center; color: var(--primary-blue); margin-bottom: 25px;">
                <i class="fas fa-chart-pie"></i> Desglose Público vs Privado
            </h2>

            <?php
            // Obtener datos con desglose público/privado
            $datosPublicoPrivado = obtenerDatosPublicoPrivado($municipioSeleccionado, '24');
            ?>

            <?php if (!empty($datosPublicoPrivado)): ?>
                <div class="datos-grid">
                    <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                        <div class="datos-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <h3 class="card-title">
                                    <?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                            </div>

                            <!-- Totales -->
                            <div style="text-align: center; margin-bottom: 20px;">
                                <div
                                    style="font-size: 1.5rem; font-weight: bold; color: var(--primary-blue); margin-bottom: 10px;">
                                    Total: <?php echo formatearNumero($datos['tot_esc']); ?> escuelas
                                </div>
                                <div style="font-size: 1.1rem; color: var(--text-secondary);">
                                    <?php echo formatearNumero($datos['tot_mat']); ?> alumnos |
                                    <?php echo formatearNumero($datos['tot_doc']); ?> docentes
                                </div>
                            </div>

                            <!-- Desglose Público/Privado -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <!-- Públicas -->
                                <div style="background-color: #e8f5e8; padding: 15px; border-radius: 8px; text-align: center;">
                                    <h4 style="color: #155724; margin-bottom: 10px;">
                                        <i class="fas fa-university"></i> Públicas
                                    </h4>
                                    <div style="font-weight: bold; color: #155724; font-size: 1.2rem;">
                                        <?php echo formatearNumero($datos['tot_esc_pub']); ?> escuelas
                                    </div>
                                    <div style="font-size: 0.9rem; color: #155724; margin-top: 5px;">
                                        <?php echo formatearNumero($datos['tot_mat_pub']); ?> alumnos<br>
                                        <?php echo formatearNumero($datos['tot_doc_pub']); ?> docentes
                                    </div>
                                </div>

                                <!-- Privadas -->
                                <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; text-align: center;">
                                    <h4 style="color: #856404; margin-bottom: 10px;">
                                        <i class="fas fa-building"></i> Privadas
                                    </h4>
                                    <div style="font-weight: bold; color: #856404; font-size: 1.2rem;">
                                        <?php echo formatearNumero($datos['tot_esc_priv']); ?> escuelas
                                    </div>
                                    <div style="font-size: 0.9rem; color: #856404; margin-top: 5px;">
                                        <?php echo formatearNumero($datos['tot_mat_priv']); ?> alumnos<br>
                                        <?php echo formatearNumero($datos['tot_doc_priv']); ?> docentes
                                    </div>
                                </div>
                            </div>

                            <!-- Porcentajes -->
                            <?php if ($datos['tot_esc'] > 0): ?>
                                <div style="margin-top: 15px; font-size: 0.9rem; color: var(--text-secondary); text-align: center;">
                                    Público: <?php echo round(($datos['tot_esc_pub'] / $datos['tot_esc']) * 100, 1); ?>% |
                                    Privado: <?php echo round(($datos['tot_esc_priv'] / $datos['tot_esc']) * 100, 1); ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    No se pudieron obtener datos de desglose público/privado para este municipio.
                </div>
            <?php endif; ?>
        </div>

        <!-- NUEVA SECCIÓN: Desglose por Sexo -->
        <div style="margin-top: 40px;">
            <h2 style="text-align: center; color: var(--primary-blue); margin-bottom: 25px;">
                <i class="fas fa-venus-mars"></i> Desglose por Sexo - Alumnos y Docentes
            </h2>

            <?php if (!empty($datosPublicoPrivado)): ?>
                <div class="datos-grid">
                    <?php foreach ($datosPublicoPrivado as $nivel => $datos): ?>
                        <?php
                        // Calcular totales de alumnos y docentes
                        $totalAlumnos = $datos['tot_mat'];
                        $totalDocentes = $datos['tot_doc'];

                        // Solo mostrar si hay datos
                        if ($totalAlumnos > 0 || $totalDocentes > 0):
                            ?>
                            <div class="datos-card">
                                <div class="card-header">
                                    <div class="card-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h3 class="card-title">
                                        <?php echo htmlspecialchars($datos['titulo_fila'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h3>
                                </div>

                                <!-- ALUMNOS POR SEXO -->
                                <?php if ($totalAlumnos > 0): ?>
                                    <div style="margin-bottom: 25px;">
                                        <h4 style="text-align: center; color: var(--secondary-blue); margin-bottom: 15px;">
                                            <i class="fas fa-user-graduate"></i> Alumnos
                                        </h4>

                                        <!-- Total de Alumnos -->
                                        <div style="text-align: center; margin-bottom: 15px;">
                                            <div style="font-size: 1.3rem; font-weight: bold; color: var(--primary-blue);">
                                                Total: <?php echo formatearNumero($totalAlumnos); ?>
                                            </div>
                                        </div>

                                        <!-- Desglose Hombres/Mujeres -->
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                            <!-- Hombres -->
                                            <div
                                                style="background-color: #e3f2fd; padding: 12px; border-radius: 8px; text-align: center;">
                                                <h5 style="color: #1565c0; margin-bottom: 8px;">
                                                    <i class="fas fa-mars"></i> Hombres
                                                </h5>
                                                <div style="font-weight: bold; color: #1565c0; font-size: 1.1rem;">
                                                    <?php echo formatearNumero($datos['mat_h']); ?>
                                                </div>
                                                <div style="font-size: 0.85rem; color: #1565c0;">
                                                    <?php echo $totalAlumnos > 0 ? round(($datos['mat_h'] / $totalAlumnos) * 100, 1) : 0; ?>%
                                                </div>
                                                <!-- Desglose público/privado -->
                                                <div style="font-size: 0.75rem; color: #1565c0; margin-top: 5px;">
                                                    Púb: <?php echo formatearNumero($datos['mat_h_pub']); ?> |
                                                    Priv: <?php echo formatearNumero($datos['mat_h_priv']); ?>
                                                </div>
                                            </div>

                                            <!-- Mujeres -->
                                            <div
                                                style="background-color: #fce4ec; padding: 12px; border-radius: 8px; text-align: center;">
                                                <h5 style="color: #c2185b; margin-bottom: 8px;">
                                                    <i class="fas fa-venus"></i> Mujeres
                                                </h5>
                                                <div style="font-weight: bold; color: #c2185b; font-size: 1.1rem;">
                                                    <?php echo formatearNumero($datos['mat_m']); ?>
                                                </div>
                                                <div style="font-size: 0.85rem; color: #c2185b;">
                                                    <?php echo $totalAlumnos > 0 ? round(($datos['mat_m'] / $totalAlumnos) * 100, 1) : 0; ?>%
                                                </div>
                                                <!-- Desglose público/privado -->
                                                <div style="font-size: 0.75rem; color: #c2185b; margin-top: 5px;">
                                                    Púb: <?php echo formatearNumero($datos['mat_m_pub']); ?> |
                                                    Priv: <?php echo formatearNumero($datos['mat_m_priv']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- DOCENTES POR SEXO -->
                                <?php if ($totalDocentes > 0): ?>
                                    <div>
                                        <h4 style="text-align: center; color: var(--secondary-blue); margin-bottom: 15px;">
                                            <i class="fas fa-chalkboard-teacher"></i> Docentes
                                        </h4>

                                        <!-- Total de Docentes -->
                                        <div style="text-align: center; margin-bottom: 15px;">
                                            <div style="font-size: 1.3rem; font-weight: bold; color: var(--primary-blue);">
                                                Total: <?php echo formatearNumero($totalDocentes); ?>
                                            </div>
                                        </div>

                                        <!-- Desglose Hombres/Mujeres -->
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                            <!-- Hombres -->
                                            <div
                                                style="background-color: #e8f5e8; padding: 12px; border-radius: 8px; text-align: center;">
                                                <h5 style="color: #2e7d32; margin-bottom: 8px;">
                                                    <i class="fas fa-mars"></i> Hombres
                                                </h5>
                                                <div style="font-weight: bold; color: #2e7d32; font-size: 1.1rem;">
                                                    <?php echo formatearNumero($datos['doc_h']); ?>
                                                </div>
                                                <div style="font-size: 0.85rem; color: #2e7d32;">
                                                    <?php echo $totalDocentes > 0 ? round(($datos['doc_h'] / $totalDocentes) * 100, 1) : 0; ?>%
                                                </div>
                                                <!-- Desglose público/privado -->
                                                <div style="font-size: 0.75rem; color: #2e7d32; margin-top: 5px;">
                                                    Púb: <?php echo formatearNumero($datos['doc_h_pub']); ?> |
                                                    Priv: <?php echo formatearNumero($datos['doc_h_priv']); ?>
                                                </div>
                                            </div>

                                            <!-- Mujeres -->
                                            <div
                                                style="background-color: #fff3e0; padding: 12px; border-radius: 8px; text-align: center;">
                                                <h5 style="color: #f57c00; margin-bottom: 8px;">
                                                    <i class="fas fa-venus"></i> Mujeres
                                                </h5>
                                                <div style="font-weight: bold; color: #f57c00; font-size: 1.1rem;">
                                                    <?php echo formatearNumero($datos['doc_m']); ?>
                                                </div>
                                                <div style="font-size: 0.85rem; color: #f57c00;">
                                                    <?php echo $totalDocentes > 0 ? round(($datos['doc_m'] / $totalDocentes) * 100, 1) : 0; ?>%
                                                </div>
                                                <!-- Desglose público/privado -->
                                                <div style="font-size: 0.75rem; color: #f57c00; margin-top: 5px;">
                                                    Púb: <?php echo formatearNumero($datos['doc_m_pub']); ?> |
                                                    Priv: <?php echo formatearNumero($datos['doc_m_priv']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Mensaje si no hay datos -->
                                <?php if ($totalAlumnos == 0 && $totalDocentes == 0): ?>
                                    <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                                        <i class="fas fa-info-circle"></i>
                                        No hay datos de alumnos ni docentes para este nivel.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    No se pudieron obtener datos de desglose por sexo para este municipio.
                </div>
            <?php endif; ?>
        </div>

        <!-- Información adicional -->
        <div class="info-footer">
            <p><strong>Fecha de consulta:</strong> <?php echo $datosMunicipio['fecha_consulta']; ?></p>
        </div>
    </div>
</body>

</html>