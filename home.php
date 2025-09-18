<?php
/**
 * =============================================================================
 * PÁGINA PRINCIPAL DEL SISTEMA - CENTRO DE DASHBOARDS
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
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
require_once 'conexion_prueba_2024.php';

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

// Definir municipios principales que se mostrarán inicialmente (mantener funcionalidad existente)
$municipiosPrincipales = ['QUERÉTARO', 'CORREGIDORA', 'EL MARQUÉS', 'SAN JUAN DEL RÍO'];

// Filtrar municipios adicionales (excluyendo los principales)
$municipiosAdicionales = array_filter($todosLosMunicipios, function ($municipio) use ($municipiosPrincipales) {
    return !in_array(strtoupper($municipio), $municipiosPrincipales);
});

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

    <!-- ========================================== -->
    <!-- HOJAS DE ESTILO MODULARIZADAS             -->
    <!-- ========================================== -->
    <!-- Estilos globales compartidos por todo el sistema -->
    <link rel="stylesheet" href="./css/global.css">
    <!-- Estilos específicos para la página principal -->
    <link rel="stylesheet" href="./css/home.css">
    <!-- Estilos para el menú lateral responsive -->
    <link rel="stylesheet" href="./css/sidebar.css">
    <!-- Iconografía Font Awesome 6.0 para elementos visuales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Estilos adicionales para funcionalidad de municipios mejorada -->
    <style>
        /* Estilos para estadísticas estatales */
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

        .estado-note {
            text-align: center;
            margin-top: 1rem;
            font-style: italic;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        /* Estilos para estadísticas de municipios */
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

        /* Estilos para tarjetas de municipios mejoradas */
        .municipality-card {
            position: relative;
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

        /* Responsive para móviles */
        @media (max-width: 768px) {
            .estado-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .municipios-stats {
                flex-direction: column;
                gap: 15px;
            }

            .municipality-checkbox {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 10px;
            }

            .compare-floating-button {
                bottom: 20px;
                right: 20px;
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- ============================================================================ -->
    <!-- CONTENEDOR PRINCIPAL DE LA APLICACIÓN                                       -->
    <!-- ============================================================================ -->
    <div class="app-container">
        <!-- Overlay para cerrar menú en dispositivos móviles -->
        <div class="sidebar-overlay"></div>

        <!-- ======================================== -->
        <!-- BARRA LATERAL DE NAVEGACIÓN              -->
        <!-- ======================================== -->
        <aside class="sidebar">
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
                        <a href="historicos.php"><i class="fas fa-history"></i> <span>Históricos</span></a>
                    </li>
                    <!-- Enlace a configuraciones del sistema -->
                    <li class="nav-item">
                        <a href="settings.php"><i class="fas fa-cog"></i> <span>Configuración</span></a>
                    </li>
                </ul>
            </nav>
            <!-- Pie de la barra lateral con opción de logout -->
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </a>
            </div>
        </aside> <!-- ======================================== -->
        <!-- CONTENIDO PRINCIPAL DE LA APLICACIÓN    -->
        <!-- ======================================== -->
        <main class="main-content">
            <!-- ===================================== -->
            <!-- BARRA SUPERIOR CON NAVEGACIÓN Y USER -->
            <!-- ===================================== -->
            <header class="top-bar">
                <!-- Botón toggle para menú lateral en móviles -->
                <div class="menu-toggle">
                    <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
                </div>

                <!-- Título principal de la sección -->
                <div class="top-bar-title">
                    <h1>Centro de Tableros Estadísticos</h1>
                </div>

                <!-- Menú de usuario con avatar y opciones -->
                <div class="user-menu">
                    <span class="user-greeting">Hola,
                        <?php
                        // Mostrar nombre del usuario desde sesión o "Usuario" por defecto
                        echo isset($_SESSION['fullname']) ? explode(' ', $_SESSION['fullname'])[0] : 'Usuario';
                        ?>
                    </span>
                    <div class="user-avatar">
                        <img src="./img/user-avatar.jpg" alt="Avatar">
                    </div>
                    <!-- Dropdown con opciones de usuario -->
                    <div class="user-dropdown">
                        <ul>
                            <li><a href="settings.php"><i class="fas fa-user-cog"></i> Mi Perfil</a></li>
                            <li><a href="#"><i class="fas fa-bell"></i> Notificaciones</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- ===================================== -->
            <!-- WRAPPER PRINCIPAL DEL CONTENIDO      -->
            <!-- ===================================== -->
            <div class="content-wrapper">
                <!-- ================================ -->
                <!-- SECCIÓN DE BIENVENIDA           -->
                <!-- ================================ -->
                <section class="welcome-section">
                    <div class="welcome-card animate-fade">
                        <div class="welcome-text">
                            <h2>Bienvenido al Sistema de Estadística Educativa</h2>
                            <p>Accede a los tableros estadísticos de los diferentes municipios del Estado de
                                Querétaro.</p>
                            <!-- Fecha dinámica del sistema -->
                            <p class="welcome-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></p>
                        </div>
                        <!-- Logo institucional con animación -->
                        <div class="welcome-image animate-scale delay-1">
                            <img src="./img/layout_set_logo.png" alt="Querétaro">
                        </div>
                    </div>
                </section>

                <!-- NUEVA SECCIÓN: Estadísticas Estatales -->
                <?php if ($datosEstado && !empty($datosEstado)): ?>
                    <section class="estadisticas-estado animate-up delay-2">
                        <div class="estado-header">
                            <h2><i class="fas fa-chart-bar"></i> Estadísticas del Estado de Querétaro</h2>
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
                        </div>
                        <div class="estado-note">* En el total, se cuantifican escuelas, no planteles ni instituciones</div>
                    </section>
                <?php endif; ?>

                <!-- Sección de municipios (FUNCIONALIDAD MEJORADA) -->
                <section class="dashboard-section animate-up delay-3">
                    <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Tableros por Municipio</h2>

                    <!-- Grid de municipios principales (mostrados inicialmente) -->
                    <div class="dashboard-grid animate-sequence">
                        <?php
                        // Generar tarjetas para municipios principales con datos reales
                        foreach ($municipiosPrincipales as $municipio) {
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
                        }
                        ?>

                        <!-- Tarjeta "Ver más municipios" -->
                        <?php if (!empty($municipiosAdicionales)): ?>
                            <div class="municipality-card view-more-card" id="btn-ver-mas" onclick="mostrarMasMunicipios()"
                                style="cursor: pointer;">
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
                <section class="quick-access-section animate-up delay-3">
                    <h2 class="section-title"><i class="fas fa-bolt"></i> Acceso Rápido</h2>
                    <div class="quick-access-grid animate-sequence">
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-search"></i>
                            <h3>Búsqueda por Escuela</h3>
                        </a>
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-chart-line"></i>
                            <h3>Tendencias Anuales</h3>
                        </a>
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-download"></i>
                            <h3>Descarga de Reportes</h3>
                        </a>
                        <a href="#" class="quick-access-card">
                            <i class="fas fa-table"></i>
                            <h3>Tablas Comparativas</h3>
                        </a>
                    </div>
                </section>
            </div>

            <!-- Pie de página -->
            <footer class="main-footer">
                <p>&copy; <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                    reservados</p>
            </footer>
        </main>
    </div>
    <script src="./js/sidebar.js"></script>
    <script src="./js/home.js"></script>
    <script src="./js/animations_global.js"></script>

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