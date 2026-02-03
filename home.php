<?php
/**
 * =============================================================================
 * PÁGINA PRINCIPAL DEL SISTEMA - CENTRO DE DASHBOARDS
 * Sistema de Dashboard Estadístico - SEDEQ 
 * =============================================================================
 * 
 * Esta página funciona como el centro de navegación principal del sistema,
 * proporcionando acceso a todos los dashboards estadísticos por municipio
 * y herramientas de análisis rápido del estado de Querétaro.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Centro de navegación para dashboards municipales
 * - Visualización de métricas resumidas por municipio
 * - Acceso rápido a herramientas de análisis
 * - Sistema de autenticación con sesiones demo
 * - Interfaz responsiva y animaciones optimizadas
 * 
 * ARQUITECTURA DE SEGURIDAD:
 * - Validación de sesiones activas
 * - Modo demo para acceso sin credenciales
 * - Integración con sistema de autenticación centralizado
 * - Manejo seguro de datos de usuario
 * 
 * @author Sistema SEDEQ
 * @version 1.2.1
 * @since 2024
 */

// =============================================================================
// CONFIGURACIÓN DE SESIÓN Y AUTENTICACIÓN
// =============================================================================

// Incluir helper de gestión de sesiones con modo demo
require_once 'session_helper.php';

// Inicializar sesión y configurar usuario demo si es necesario
// Esta función maneja tanto sesiones reales como modo demostración
iniciarSesionDemo();

// =============================================================================
// CONEXIÓN A BASE DE DATOS Y OBTENCIÓN DE DATOS
// =============================================================================

// Incluir archivo de conexión de prueba 2024 (NUEVA FUNCIONALIDAD)
require_once 'conexion.php';

// Obtener lista de municipios usando la función de prueba - con validación
$todosLosMunicipios = obtenerMunicipiosPrueba2024();

// Validar que la lista de municipios sea un array válido
if (!$todosLosMunicipios || !is_array($todosLosMunicipios)) {
    $todosLosMunicipios = [
        'AMEALCO DE BONFIL',
        'ARROYO SECO',
        'CADEREYTA DE MONTES',
        'CORREGIDORA',
        'EL MARQUES',
        'EZEQUIEL MONTES',
        'HUIMILPAN',
        'JALPAN DE SERRA',
        'LANDA DE MATAMOROS',
        'PEDRO ESCOBEDO',
        'PEÑON',
        'PINAL DE AMOLES',
        'QUERETARO',
        'SAN JOAQUIN',
        'SAN JUAN DEL RIO',
        'TEQUISQUIAPAN',
        'TOLIMAN',
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

// Ordenar todos los municipios alfabéticamente
$todosLosMunicipiosOrdenados = $todosLosMunicipios;
sort($todosLosMunicipiosOrdenados);

// Obtener los primeros 4 municipios del estado (ordenados alfabéticamente)
$primerosCuatroMunicipios = array_slice($todosLosMunicipiosOrdenados, 0, 10); // Cambiado a 10 para mostrar más municipios inicialmente

// Filtrar municipios adicionales (excluyendo los primeros 4)
$municipiosAdicionales = array_slice($todosLosMunicipiosOrdenados, 4);

/**
 * Formatea nombres de municipios para display en formato título
 * Convierte de MAYÚSCULAS (nuestro formato interno) a Formato Título para mostrar
 */
function formatearNombreMunicipio($municipio)
{
    // Convertir de mayúsculas a formato título
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');

    // Correcciones específicas para preposiciones y artículos
    $formatted = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $formatted);

    return $formatted;
}

/**
 * Obtiene datos básicos de un municipio usando la nueva estructura de conexión
 */
function obtenerDatosMunicipio($municipio)
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
            'tiene_error' => false
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
    <title>Centro de Tableros | SEDEQ - Estadística Educativa</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">

    <!-- ========================================== -->
    <!-- HOJAS DE ESTILO MODULARIZADAS             -->
    <!-- ========================================== -->
    <!-- Estilos globales compartidos por todo el sistema -->
    <link rel="stylesheet" href="./css/global.css">
    <!-- Estilos específicos para la página principal -->
    <link rel="stylesheet" href="./css/home.css">
    <!-- Iconografía Font Awesome 6.0 para elementos visuales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Estilos adicionales para funcionalidad de municipios mejorada -->
    <style></style>
</head>

<body>
    <?php include 'includes/institutional_bar.php'; ?>

    <!-- ============================================================================ -->
    <!-- HEADER PRINCIPAL CON LOGO Y NAVEGACIÓN                                      -->
    <!-- ============================================================================ -->
    <header class="main-header">
        <div class="header-content">
            <?php include 'includes/header_logo.php'; ?>

            <!-- Menú de navegación horizontal (desktop) -->
            <div class="header-nav">
                <nav>
                    <a href="home.php" class="header-nav-link active">Inicio</a>
                    <a href="directorio_estatal.php" class="header-nav-link">Escuelas</a>
                    <a href="bibliotecas.php" class="header-nav-link">Bibliotecas</a>
                    <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                        target="_blank" class="header-nav-link">Mapa</a>
                    <a href="settings.php" class="header-nav-link">Configuración</a>
                </nav>
            </div>

            <?php include 'includes/header_end.php'; ?>

            <!-- ======================================== -->
            <!-- BARRA LATERAL DE NAVEGACIÓN              -->
            <!-- ======================================== -->
            <aside class="sidebar">
                <!-- Logo en el sidebar -->
                <div class="sidebar-header">
                    <img src="./img/layout_set_logo.png" alt="SEDEQ" class="sidebar-logo">
                </div>

                <nav class="sidebar-nav">
                    <ul>
                        <!-- Enlace a página principal (estado activo) -->
                        <li class="nav-item active">
                            <a href="home.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
                        </li>
                        <!-- Enlaces a funcionalidades futuras -->
                        <li class="nav-item">
                            <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                                target="_blank"><i class="fas fa-map-marked-alt"></i> <span>Mapa Educativo</span></a>
                        </li>
                        <li class="nav-item">
                            <a href="bibliotecas.php"><i class="fas fa-book"></i> <span>Bibliotecas</span></a>
                        </li>
                        <li class="nav-item">
                            <a href="directorio_estatal.php"><i class="fas fa-search"></i> <span>Búsqueda de
                                    Escuelas</span></a>
                        </li>
                        <!-- Enlace a configuraciones del sistema -->
                        <li class="nav-item">
                            <a href="settings.php"><i class="fas fa-cog"></i> <span>Configuración</span></a>
                        </li>
                    </ul>
                </nav>

                <!-- Pie de la barra lateral con opción de logout -->
            </aside>

            <!-- ============================================================================ -->
            <!-- CONTENEDOR PRINCIPAL DE LA APLICACIÓN                                       -->
            <!-- ============================================================================ -->
            <div class="app-container"> <!-- ======================================== -->
                <!-- CONTENIDO PRINCIPAL DE LA APLICACIÓN    -->
                <!-- ======================================== -->
                <main class="main-content">
                    <!-- ===================================== -->
                    <!-- WRAPPER PRINCIPAL DEL CONTENIDO      -->
                    <!-- ===================================== -->
                    <div class="content-wrapper">
                        <!-- ================================ -->
                        <!-- SECCIÓN DE BIENVENIDA           -->
                        <!-- ================================ -->

                        <!-- NUEVA SECCIÓN: Estadísticas Estatales -->
                        <?php if ($datosEstado && !empty($datosEstado)): ?>
                            <section class="estadisticas-estado animate-up delay-2">
                                <div class="estado-header">
                                    <h2><i class="fas fa-chart-bar"></i> Estadística del Estado de Querétaro</h2>
                                    <p><?php echo isset($infoCiclo['descripcion']) ? $infoCiclo['descripcion'] : 'Ciclo Escolar 2024-2025'; ?>
                                        - Totales Estatales</p>
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
                                        <div class="estado-stat-label">Escuelas *</div>
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
                                </div>
                                <div class="estado-note">* En el total, se cuantifican escuelas, no planteles ni
                                    instituciones</div>
                            </section>
                        <?php endif; ?>

                        <!-- Sección de municipios (FUNCIONALIDAD MEJORADA) -->
                        <section class="dashboard-section animate-up delay-3">
                            <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Tableros por Municipio</h2>

                            <!-- Grid de los primeros 4 municipios del estado (mostrados inicialmente) -->
                            <div class="dashboard-grid animate-sequence">
                                <?php
                                // Generar tarjetas para los primeros 4 municipios con datos reales
                                foreach ($primerosCuatroMunicipios as $municipio) {
                                    $municipioNormalizado = formatearNombreMunicipio($municipio);
                                    $datosMunicipio = obtenerDatosMunicipio($municipio);
                                    $tieneDatos = ($datosMunicipio['alumnos'] > 0 || $datosMunicipio['escuelas'] > 0);
                                    $claseCard = $tieneDatos ? 'has-data' : 'no-data';
                                    ?>
                                    <div class="municipality-card <?php echo $claseCard; ?>"
                                        data-municipio="<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                                        <!-- Checkbox de selección -->
                                        <div class="municipality-checkbox">
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox"
                                                    id="municipio_<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>"
                                                    class="municipality-selector"
                                                    value="<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                                                <label
                                                    for="municipio_<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                                                    Comparar
                                                </label>
                                            </div>
                                        </div>

                                        <div class="municipality-icon">
                                            <i class="fas fa-city"></i>
                                        </div>
                                        <div class="municipality-info">
                                            <h3><?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?>
                                            </h3>
                                            <p>Estadísticas educativas para el municipio de
                                                <?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?>.
                                            </p>
                                            <div class="municipality-stats">
                                                <div class="stat">
                                                    <i class="fas fa-user-graduate"></i>
                                                    <?php echo number_format($datosMunicipio['alumnos'], 0, '.', ','); ?>
                                                </div>
                                                <div class="stat">
                                                    <i class="fas fa-school"></i>
                                                    <?php echo number_format($datosMunicipio['escuelas'], 0, '.', ','); ?>
                                                </div>
                                                <div class="stat">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                    <?php echo number_format($datosMunicipio['docentes'], 0, '.', ','); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="resumen.php?municipio=<?php echo urlencode($municipio); ?>"
                                            class="municipality-link">
                                            Acceder <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>

                                <!-- Municipios adicionales -->
                                <?php
                                if (!empty($municipiosAdicionales)) {
                                    foreach ($municipiosAdicionales as $municipio) {
                                        $municipioNormalizado = formatearNombreMunicipio($municipio);
                                        $datosMunicipio = obtenerDatosMunicipio($municipio);
                                        $tieneDatos = ($datosMunicipio['alumnos'] > 0 || $datosMunicipio['escuelas'] > 0);
                                        $claseCard = $tieneDatos ? 'has-data' : 'no-data';
                                        ?>
                                        <div class="municipality-card municipio-adicional <?php echo $claseCard; ?>"
                                            style="display: none;"
                                            data-municipio="<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                                            <!-- Checkbox de selección -->
                                            <div class="municipality-checkbox">
                                                <div class="checkbox-wrapper">
                                                    <input type="checkbox"
                                                        id="municipio_<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>"
                                                        class="municipality-selector"
                                                        value="<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <label
                                                        for="municipio_<?php echo htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8'); ?>">
                                                        Comparar
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="municipality-icon">
                                                <i class="fas fa-city"></i>
                                            </div>
                                            <div class="municipality-info">
                                                <h3><?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?>
                                                </h3>
                                                <p>Estadísticas educativas para el municipio de
                                                    <?php echo htmlspecialchars($municipioNormalizado, ENT_QUOTES, 'UTF-8'); ?>.
                                                </p>
                                                <div class="municipality-stats">
                                                    <div class="stat">
                                                        <i class="fas fa-user-graduate"></i>
                                                        <?php echo number_format($datosMunicipio['alumnos'], 0, '.', ','); ?>
                                                    </div>
                                                    <div class="stat">
                                                        <i class="fas fa-school"></i>
                                                        <?php echo number_format($datosMunicipio['escuelas'], 0, '.', ','); ?>
                                                    </div>
                                                    <div class="stat">
                                                        <i class="fas fa-chalkboard-teacher"></i>
                                                        <?php echo number_format($datosMunicipio['docentes'], 0, '.', ','); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="resumen.php?municipio=<?php echo urlencode($municipio); ?>"
                                                class="municipality-link">
                                                Acceder <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                                <!-- Tarjeta "Ver más municipios" -->
                                <?php if (!empty($municipiosAdicionales)): ?>
                                    <div class="municipality-card view-more-card" id="btn-ver-mas"
                                        onclick="mostrarMasMunicipios()" style="cursor: pointer;">
                                        <div class="view-more-icon">
                                            <i class="fas fa-plus-circle"></i>
                                        </div>
                                        <h3>Ver más municipios</h3>
                                        <p>Accede a la información de todos los municipios del estado.</p>
                                        <div class="view-more-link">
                                            Ver todos <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Botón flotante de comparación -->
                            <a href="#" class="compare-floating-button" id="compareButton">
                                <i class="fas fa-balance-scale"></i>
                                <span>Comparar</span>
                                <span class="selected-count" id="selectedCount">0</span>
                            </a>
                        </section> <!-- Sección de municipios -->

                    </div>

                    <?php include 'includes/footer.php'; ?>
            </div>
            </main>
        </div>

        <script src="./js/sidebar.js"></script>
        <script src="./js/home.js"></script>
        <script src="./js/animations_global.js"></script>

        <!-- Botón volver al inicio -->
        <?php include 'includes/back_to_top.php'; ?>

        <!-- Script para funcionalidad de municipios mejorada -->
        <script>
            // Manejo de selección de municipios para comparación
            document.addEventListener('DOMContentLoaded', function () {
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
                        if (checkbox && checkbox.checked) {
                            card.classList.add('selected');
                        } else {
                            card.classList.remove('selected');
                        }
                    });
                }

                // Agregar event listeners a todos los checkboxes
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        updateCompareButton();
                        updateCardStyles();
                    });
                });

                // Prevenir navegación si no hay municipios seleccionados
                if (compareButton) {
                    compareButton.addEventListener('click', function (e) {
                        const selectedCheckboxes = document.querySelectorAll('.municipality-selector:checked');
                        if (selectedCheckboxes.length < 2) {
                            e.preventDefault();
                            alert('Debes seleccionar entre 2 y 3 municipios para comparar.');
                        }
                    });
                }

                // Click en tarjeta también selecciona
                document.querySelectorAll('.municipality-card').forEach(card => {
                    card.addEventListener('click', function (e) {
                        // Solo si no se hizo click en el checkbox, link o label
                        if (!e.target.matches('input[type="checkbox"]') &&
                            !e.target.matches('a') &&
                            !e.target.matches('label') &&
                            !e.target.closest('a')) {

                            const checkbox = this.querySelector('.municipality-selector');
                            if (checkbox && !checkbox.disabled) {
                                checkbox.checked = !checkbox.checked;
                                checkbox.dispatchEvent(new Event('change'));
                            }
                        }
                    });
                });

                // Inicializar estado
                if (compareButton && selectedCount) {
                    updateCompareButton();
                    updateCardStyles();
                }
            });
        </script>
</body>

</html>