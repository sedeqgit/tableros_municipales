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

// Incluir el archivo de conexión de prueba
require_once 'conexion_prueba_2024.php';

// Obtener el municipio desde el parámetro GET, por defecto Corregidora
$municipioSeleccionado = isset($_GET['municipio']) ? strtoupper(trim($_GET['municipio'])) : 'CORREGIDORA';

// Validar que el municipio esté en la lista de municipios válidos
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'CORREGIDORA'; // Fallback a Corregidora si el municipio no es válido
}

// Obtener los datos para el municipio seleccionado
$datosMunicipio = obtenerDatosCompletos2024($municipioSeleccionado);

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
    </style>
</head>

<body>
    <div class="prueba-container">
        <!-- Header de la página -->
        <div class="prueba-header">
            <h1><i class="fas fa-database"></i> Prueba de Consultas Esquema 2024</h1>
            <p>Verificación de datos educativos usando las consultas actualizadas al esquema 2024</p>
            <p><strong>Municipio:</strong>
                <?php echo htmlspecialchars($datosMunicipio['municipio'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <!-- Botón para regresar -->
        <a href="municipios_prueba.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Regresar a Municipios
        </a>

        <!-- Grid principal de datos -->
        <div class="datos-grid">
            <!-- Tarjeta de Docentes (Datos simulados) -->
            <div class="datos-card docentes-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="card-title">Docentes</h3>
                </div>

                <?php if (isset($datosMunicipio['docentes']['error'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($datosMunicipio['docentes']['error'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php else: ?>
                    <div class="total-general">
                        <?php echo formatearNumero($datosMunicipio['docentes']['total_general']); ?>
                    </div>

                    <div class="detalles-niveles">
                        <?php foreach ($datosMunicipio['docentes']['por_nivel'] as $docente): ?>
                            <div class="detalle-nivel">
                                <span class="nivel-nombre">
                                    <?php echo htmlspecialchars($docente['nivel_educativo'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (isset($docente['subnivel']) && !empty($docente['subnivel'])): ?>
                                        <small style="color: #666;">(<?php echo htmlspecialchars($docente['subnivel'], ENT_QUOTES, 'UTF-8'); ?>)</small>
                                    <?php endif; ?>
                                </span>
                                <span class="nivel-cantidad"><?php echo formatearNumero($docente['total_docentes']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (isset($datosMunicipio['docentes']['nota'])): ?>
                        <div class="error-message"
                            style="background-color: #fff3cd; color: #856404; border-left-color: #ffc107;">
                            <i class="fas fa-info-circle"></i>
                            <?php echo htmlspecialchars($datosMunicipio['docentes']['nota'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Tarjeta de Escuelas (Datos reales) -->
            <div class="datos-card escuelas-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <h3 class="card-title">Escuelas</h3>
                </div>

                <?php if (isset($datosMunicipio['datos_educativos']['error'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($datosMunicipio['datos_educativos']['error'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php else: ?>
                    <div class="total-general">
                        <?php echo formatearNumero($datosMunicipio['datos_educativos']['total_escuelas']); ?>
                    </div>

                    <div class="detalles-niveles">
                        <?php foreach ($datosMunicipio['datos_educativos']['datos'] as $dato): ?>
                            <div class="detalle-nivel">
                                <span
                                    class="nivel-nombre"><?php echo htmlspecialchars($dato['tipo_educativo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="nivel-cantidad"><?php echo formatearNumero($dato['escuelas']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tarjeta de Matrícula (Datos reales) -->
            <div class="datos-card matricula-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="card-title">Matrícula de Alumnos</h3>
                </div>

                <?php if (isset($datosMunicipio['datos_educativos']['error'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($datosMunicipio['datos_educativos']['error'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php else: ?>
                    <div class="total-general">
                        <?php echo formatearNumero($datosMunicipio['datos_educativos']['total_alumnos']); ?>
                    </div>

                    <div class="detalles-niveles">
                        <?php foreach ($datosMunicipio['datos_educativos']['datos'] as $dato): ?>
                            <div class="detalle-nivel">
                                <span
                                    class="nivel-nombre"><?php echo htmlspecialchars($dato['tipo_educativo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="nivel-cantidad"><?php echo formatearNumero($dato['alumnos']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="info-footer">
            <p><strong>Fecha de consulta:</strong> <?php echo $datosMunicipio['fecha_consulta']; ?></p>
            <p><strong>Esquema utilizado:</strong> nonce_pano_24 (Datos 2024)</p>
            <p><strong>Filtros aplicados:</strong> cv_estatus_captura = 0 OR 10, cv_motivo = 0</p>
        </div>
    </div>
</body>

</html>