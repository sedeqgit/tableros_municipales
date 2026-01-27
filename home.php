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
    <!-- Estilos para el menú lateral responsive -->
    <link rel="stylesheet" href="./css/sidebar.css">
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


            <!-- Botón de menú hamburguesa (solo móviles) -->
            <div class="header-menu-toggle">
                <button id="sidebarToggle" class="menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

    </header>

    <!-- Overlay para cerrar menú en dispositivos móviles -->
    <div class="sidebar-overlay"></div>

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

            </div>

            <!-- Pie de página -->
            <footer class="main-footer">
                <div>
                    <div class="mb-lg-0 ml-lg-0 mr-lg-0 mt-lg-0 pb-lg-0 pl-lg-0 pr-lg-0 pt-lg-0">
                        <div id="fragment-1095-melk">
                            <section class="top-top-ft2">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-12 text-center logo-footer">
                                            <img src="./img/heraldicas.png" width="200" height="auto"
                                                alt="Gobierno de Querétaro">
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <section class="top-footer2">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/lugar.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">DIRECCIÓN PALACIO DE GOBIERNO</h3>
                                                <p class="p-footer">
                                                    5 de Mayo S/N esq. Luis Pasteur,
                                                    Col. Centro Histórico C.P. 76000,
                                                    Santiago de Querétaro, Qro.,México.</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/telefono.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">TELÉFONO</h3>
                                                <p class="p-footer">
                                                    800 237 2233<br>
                                                    Directorio (442) 211 7070</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/correo.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">ATENCIÓN CIUDADANA</h3>
                                                <p class="p-footer">
                                                    Preguntas dudas, comentarios sobre el contenido del portal.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                            <div class="link-footer">
                                                <img src="https://queretaro.gob.mx/o/queretaro-theme/images/correo.png"
                                                    width="100px" height="auto">
                                                <h3 class="tf-title">WEB MASTER</h3>
                                                <p class="p-footer">
                                                    Preguntas dudas, comentarios sobre el funcionamiento del
                                                    portal.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="bottom-footer2 page-editor__disabled-area">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-8 col-sm-12 col-12">
                                            <p><a href="https://queretaro.gob.mx/web/aviso-de-privacidad"
                                                    class="aviso">Aviso de
                                                    privacidad</a></p>
                                            <p>PODER EJECUTIVO DEL ESTADO DE QUERÉTARO Copyright © 2026 Derechos
                                                Reservados. </p>
                                        </div>
                                        <div class="col-md-4 col-sm-12 col-12 text-md-right text-center fin-div">
                                            <div class="social-links">
                                                <ul>
                                                    <li class="social_"><a href="https://wa.me/+524421443740"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M416 208C416 305.2 330 384 224 384C197.3 384 171.9 379 148.8 370L67.2 413.2C57.9 418.1 46.5 416.4 39 409C31.5 401.6 29.8 390.1 34.8 380.8L70.4 313.6C46.3 284.2 32 247.6 32 208C32 110.8 118 32 224 32C330 32 416 110.8 416 208zM416 576C321.9 576 243.6 513.9 227.2 432C347.2 430.5 451.5 345.1 463 229.3C546.3 248.5 608 317.6 608 400C608 439.6 593.7 476.2 569.6 505.6L605.2 572.8C610.1 582.1 608.4 593.5 601 601C593.6 608.5 582.1 610.2 572.8 605.2L491.2 562C468.1 571 442.7 576 416 576z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a
                                                            href="https://www.facebook.com/GobQro?fref=ts"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M240 363.3L240 576L356 576L356 363.3L442.5 363.3L460.5 265.5L356 265.5L356 230.9C356 179.2 376.3 159.4 428.7 159.4C445 159.4 458.1 159.8 465.7 160.6L465.7 71.9C451.4 68 416.4 64 396.2 64C289.3 64 240 114.5 240 223.4L240 265.5L174 265.5L174 363.3L240 363.3z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a href="https://twitter.com/gobqro"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M453.2 112L523.8 112L369.6 288.2L551 528L409 528L297.7 382.6L170.5 528L99.8 528L264.7 339.5L90.8 112L236.4 112L336.9 244.9L453.2 112zM428.4 485.8L467.5 485.8L215.1 152L173.1 152L428.4 485.8z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a
                                                            href="https://www.instagram.com/educacionqueretaro?fbclid=IwZXh0bgNhZW0CMTAAYnJpZBExR09OOWJid2NZT2ZTbUJvRHNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR4yi6bwE_6iEuyyUdbWYkjRLv9zjFFWyxwABVKdZSunmMWOwOsHAv_dcFFBOw_aem_t72qtgoL72OI4Pzyj-oILw"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M320.3 205C256.8 204.8 205.2 256.2 205 319.7C204.8 383.2 256.2 434.8 319.7 435C383.2 435.2 434.8 383.8 435 320.3C435.2 256.8 383.8 205.2 320.3 205zM319.7 245.4C360.9 245.2 394.4 278.5 394.6 319.7C394.8 360.9 361.5 394.4 320.3 394.6C279.1 394.8 245.6 361.5 245.4 320.3C245.2 279.1 278.5 245.6 319.7 245.4zM413.1 200.3C413.1 185.5 425.1 173.5 439.9 173.5C454.7 173.5 466.7 185.5 466.7 200.3C466.7 215.1 454.7 227.1 439.9 227.1C425.1 227.1 413.1 215.1 413.1 200.3zM542.8 227.5C541.1 191.6 532.9 159.8 506.6 133.6C480.4 107.4 448.6 99.2 412.7 97.4C375.7 95.3 264.8 95.3 227.8 97.4C192 99.1 160.2 107.3 133.9 133.5C107.6 159.7 99.5 191.5 97.7 227.4C95.6 264.4 95.6 375.3 97.7 412.3C99.4 448.2 107.6 480 133.9 506.2C160.2 532.4 191.9 540.6 227.8 542.4C264.8 544.5 375.7 544.5 412.7 542.4C448.6 540.7 480.4 532.5 506.6 506.2C532.8 480 541 448.2 542.8 412.3C544.9 375.3 544.9 264.5 542.8 227.5zM495 452C487.2 471.6 472.1 486.7 452.4 494.6C422.9 506.3 352.9 503.6 320.3 503.6C287.7 503.6 217.6 506.2 188.2 494.6C168.6 486.8 153.5 471.7 145.6 452C133.9 422.5 136.6 352.5 136.6 319.9C136.6 287.3 134 217.2 145.6 187.8C153.4 168.2 168.5 153.1 188.2 145.2C217.7 133.5 287.7 136.2 320.3 136.2C352.9 136.2 423 133.6 452.4 145.2C472 153 487.1 168.1 495 187.8C506.7 217.3 504 287.3 504 319.9C504 352.5 506.7 422.6 495 452z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a href="https://www.youtube.com/user/GobQro"
                                                            target="_blank"><svg xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M581.7 188.1C575.5 164.4 556.9 145.8 533.4 139.5C490.9 128 320.1 128 320.1 128C320.1 128 149.3 128 106.7 139.5C83.2 145.8 64.7 164.4 58.4 188.1C47 231 47 320.4 47 320.4C47 320.4 47 409.8 58.4 452.7C64.7 476.3 83.2 494.2 106.7 500.5C149.3 512 320.1 512 320.1 512C320.1 512 490.9 512 533.5 500.5C557 494.2 575.5 476.3 581.8 452.7C593.2 409.8 593.2 320.4 593.2 320.4C593.2 320.4 593.2 231 581.8 188.1zM264.2 401.6L264.2 239.2L406.9 320.4L264.2 401.6z" />
                                                            </svg></a></li>
                                                    <li class="social_"><a href="tel:4422117070" target="_blank"><svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewbox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                <path fill="#ffffff"
                                                                    d="M224.2 89C216.3 70.1 195.7 60.1 176.1 65.4L170.6 66.9C106 84.5 50.8 147.1 66.9 223.3C104 398.3 241.7 536 416.7 573.1C493 589.3 555.5 534 573.1 469.4L574.6 463.9C580 444.2 569.9 423.6 551.1 415.8L453.8 375.3C437.3 368.4 418.2 373.2 406.8 387.1L368.2 434.3C297.9 399.4 241.3 341 208.8 269.3L253 233.3C266.9 222 271.6 202.9 264.8 186.3L224.2 89z" />
                                                            </svg></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </footer>
    </div>
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