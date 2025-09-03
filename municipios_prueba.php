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
 * @since 2025
 */

// Incluir solo el archivo de conexión de prueba
require_once 'conexion_prueba_2024.php';

// Inicializar sesión simple para pruebas
if (!isset($_SESSION)) {
    session_start();
}

// Obtener lista de municipios usando la función de prueba - con validación
$todosLosMunicipios = obtenerMunicipiosPrueba2024();

// Validar que la lista de municipios sea un array válido
if (!$todosLosMunicipios || !is_array($todosLosMunicipios)) {
    $todosLosMunicipios = [
        'AMEALCO DE BONFIL', 'ARROYO SECO', 'CADEREYTA DE MONTES', 'CORREGIDORA',
        'EL MARQUES', 'EZEQUIEL MONTES', 'HUIMILPAN', 'JALPAN DE SERRA',
        'LANDA DE MATAMOROS', 'PEDRO ESCOBEDO', 'PEÑON', 'PINAL DE AMOLES',
        'QUERETARO', 'SAN JOAQUIN', 'SAN JUAN DEL RIO', 'TEQUISQUIAPAN',
        'TOLIMAN', 'VILLA CORREGIDORA'
    ];
}

// Obtener datos estatales completos - manejo de errores
$datosEstado = obtenerResumenEstadoCompleto();
$infoCiclo = obtenerInfoCicloEscolar();

// Validar que los datos estatales sean válidos
if (!$datosEstado || !is_array($datosEstado)) {
    $datosEstado = [
        'total_matricula' => 0,
        'total_docentes' => 0,
        'total_escuelas' => 0,
        'total_grupos' => 0
    ];
}

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

        if (!$resumenCompleto || !is_array($resumenCompleto)) {
            // Si no hay datos, devolver estructura vacía
            return [
                'escuelas' => 0,
                'alumnos' => 0,
                'docentes' => 0,
                'grupos' => 0,
                'ciclo_escolar' => $infoCiclo['ciclo_corto'] ?? '24',
                'tiene_error' => true
            ];
        }

        // Validar que las keys existan antes de usarlas
        $escuelas = isset($resumenCompleto['total_escuelas']) ? intval($resumenCompleto['total_escuelas']) : 0;
        $alumnos = isset($resumenCompleto['total_matricula']) ? intval($resumenCompleto['total_matricula']) : 0;
        $docentes = isset($resumenCompleto['total_docentes']) ? intval($resumenCompleto['total_docentes']) : 0;
        $grupos = isset($resumenCompleto['total_grupos']) ? intval($resumenCompleto['total_grupos']) : 0;

        return [
            'escuelas' => $escuelas,
            'alumnos' => $alumnos,
            'docentes' => $docentes,
            'grupos' => $grupos,
            'ciclo_escolar' => $infoCiclo['ciclo_corto'] ?? '24',
            'tiene_error' => false,
            // Datos adicionales por nivel (para uso futuro) - con validación
            'detalle_niveles' => [
                'inicial_esc' => isset($resumenCompleto['inicial_esc']) ? $resumenCompleto['inicial_esc'] : 0,
                'inicial_no_esc' => isset($resumenCompleto['inicial_no_esc']) ? $resumenCompleto['inicial_no_esc'] : 0,
                'preescolar' => isset($resumenCompleto['preescolar']) ? $resumenCompleto['preescolar'] : 0,
                'primaria' => isset($resumenCompleto['primaria']) ? $resumenCompleto['primaria'] : 0,
                'secundaria' => isset($resumenCompleto['secundaria']) ? $resumenCompleto['secundaria'] : 0,
                'media_sup' => isset($resumenCompleto['media_sup']) ? $resumenCompleto['media_sup'] : 0,
                'superior' => isset($resumenCompleto['superior']) ? $resumenCompleto['superior'] : 0,
                'especial' => isset($resumenCompleto['especial']) ? $resumenCompleto['especial'] : 0
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
            'grupos' => 0,
            'ciclo_escolar' => isset($infoCiclo['ciclo_corto']) ? $infoCiclo['ciclo_corto'] : '24',
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
            position: relative;
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

        /* Checkbox de selección */
        .municipality-checkbox {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin: 0;
            cursor: pointer;
            accent-color: var(--primary-blue);
        }

        .checkbox-wrapper label {
            margin-left: 5px;
            color: var(--text-secondary);
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        /* Tarjeta seleccionada */
        .municipality-card.selected {
            border: 2px solid var(--primary-blue);
            background-color: rgba(51, 153, 204, 0.05);
        }

        .municipality-card.selected .municipality-checkbox label {
            color: var(--primary-blue);
            font-weight: 600;
        }

        /* Botón flotante de comparación */
        .compare-floating-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(51, 153, 204, 0.4);
            transition: all var(--transition-speed);
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .compare-floating-button:hover {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-aqua));
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(51, 153, 204, 0.5);
        }

        .compare-floating-button.show {
            display: flex;
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .selected-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.85rem;
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
                    <p><?php echo isset($infoCiclo['descripcion']) ? $infoCiclo['descripcion'] : 'Ciclo Escolar 2024-2025'; ?> - Totales Estatales</p>
                </div>
                <div class="estado-stats-grid">
                    <div class="estado-stat-card">
                        <div class="estado-stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="estado-stat-number">
                            <?php echo number_format(isset($datosEstado['total_matricula']) ? $datosEstado['total_matricula'] : 0, 0, '.', ','); ?>
                        </div>
                        <div class="estado-stat-label">Alumnos</div>
                    </div>
                    <div class="estado-stat-card">
                        <div class="estado-stat-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="estado-stat-number">
                            <?php echo number_format(isset($datosEstado['total_escuelas']) ? $datosEstado['total_escuelas'] : 0, 0, '.', ','); ?>
                        </div>
                        <div class="estado-stat-label">Escuelas</div>
                    </div>
                    <div class="estado-stat-card">
                        <div class="estado-stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="estado-stat-number">
                            <?php echo number_format(isset($datosEstado['total_docentes']) ? $datosEstado['total_docentes'] : 0, 0, '.', ','); ?>
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
                <div class="municipality-card <?php echo $claseCard; ?>" data-municipio="<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                    <!-- Checkbox de selección -->
                    <div class="municipality-checkbox">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" 
                                   id="municipio_<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>" 
                                   class="municipality-selector" 
                                   value="<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                            <label for="municipio_<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                                Comparar
                            </label>
                        </div>
                    </div>
                    
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

        <!-- Botón flotante de comparación -->
        <a href="#" class="compare-floating-button" id="compareButton">
            <i class="fas fa-balance-scale"></i>
            <span>Comparar</span>
            <span class="selected-count" id="selectedCount">0</span>
        </a>
    </div>

    <script>
        // Manejo de selección de municipios para comparación
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.municipality-selector');
            const compareButton = document.getElementById('compareButton');
            const selectedCount = document.getElementById('selectedCount');
            const municipalityCards = document.querySelectorAll('.municipality-card');

            // Función para actualizar el estado del botón de comparación
            function updateCompareButton() {
                const selectedCheckboxes = document.querySelectorAll('.municipality-selector:checked');
                const count = selectedCheckboxes.length;
                
                selectedCount.textContent = count;
                
                if (count >= 2) {
                    compareButton.classList.add('show');
                    
                    // Construir URL de comparación con hasta 3 municipios seleccionados
                    const params = new URLSearchParams();
                    for (let i = 0; i < Math.min(count, 3); i++) {
                        params.append(`municipio${i + 1}`, selectedCheckboxes[i].value);
                    }
                    compareButton.href = `comparacion_municipios.php?${params.toString()}`;
                } else {
                    compareButton.classList.remove('show');
                    compareButton.href = '#';
                }

                // Si se seleccionan más de 3, deshabilitar el resto
                if (count >= 3) {
                    checkboxes.forEach(checkbox => {
                        if (!checkbox.checked) {
                            checkbox.disabled = true;
                            checkbox.parentElement.parentElement.style.opacity = '0.5';
                        }
                    });
                } else {
                    // Habilitar todos los checkboxes
                    checkboxes.forEach(checkbox => {
                        checkbox.disabled = false;
                        checkbox.parentElement.parentElement.style.opacity = '1';
                    });
                }
            }

            // Función para actualizar estilos visuales de las tarjetas
            function updateCardStyles() {
                municipalityCards.forEach(card => {
                    const checkbox = card.querySelector('.municipality-selector');
                    if (checkbox.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });
            }

            // Agregar event listeners a todos los checkboxes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateCompareButton();
                    updateCardStyles();
                });
            });

            // Prevenir navegación si no hay municipios seleccionados
            compareButton.addEventListener('click', function(e) {
                const selectedCheckboxes = document.querySelectorAll('.municipality-selector:checked');
                if (selectedCheckboxes.length < 2) {
                    e.preventDefault();
                    alert('Debes seleccionar entre 2 y 3 municipios para comparar.');
                }
            });

            // Inicializar estado
            updateCompareButton();
            updateCardStyles();
        });

        // Funcionalidad adicional: Click en tarjeta también selecciona
        document.querySelectorAll('.municipality-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Solo si no se hizo click en el checkbox, link o label
                if (!e.target.matches('input[type="checkbox"]') && 
                    !e.target.matches('a') && 
                    !e.target.matches('label') &&
                    !e.target.closest('a')) {
                    
                    const checkbox = this.querySelector('.municipality-selector');
                    if (!checkbox.disabled) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });

        // Mejorar accesibilidad con teclado
        document.addEventListener('keydown', function(e) {
            // Tecla 'C' para abrir comparación si hay 2-3 seleccionados
            if (e.key.toLowerCase() === 'c' && !e.ctrlKey && !e.altKey) {
                const selectedCheckboxes = document.querySelectorAll('.municipality-selector:checked');
                if (selectedCheckboxes.length >= 2 && selectedCheckboxes.length <= 3) {
                    document.getElementById('compareButton').click();
                }
            }
            
            // Escape para limpiar selección
            if (e.key === 'Escape') {
                document.querySelectorAll('.municipality-selector:checked').forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change'));
                });
            }
        });

        // Tooltip informativo
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.municipality-card');
            
            cards.forEach(card => {
                card.title = 'Click para seleccionar/deseleccionar municipio para comparación';
            });

            // Tooltip para el botón flotante
            const compareBtn = document.getElementById('compareButton');
            compareBtn.title = 'Comparar municipios seleccionados (2 a 3 municipios)';
        });
    </script>
</body>

</html>