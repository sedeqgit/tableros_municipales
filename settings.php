<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Variables para mostrar retroalimentación al usuario
$preferencesMessage = null;
$preferencesStatus = 'success';

// Procesar envío del formulario de actualización de ciclo escolar ANTES de cargar el archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_ciclo') {
    // LOG DE DEPURACIÓN
    error_log("===== INICIO ACTUALIZACIÓN CICLO =====");
    error_log("POST recibido: " . print_r($_POST, true));

    $nuevoCiclo = isset($_POST['ciclo_escolar']) ? trim($_POST['ciclo_escolar']) : '';
    error_log("Nuevo ciclo después de trim: '$nuevoCiclo'");

    if (preg_match('/^\d{2}$/', $nuevoCiclo)) {
        error_log("Validación de patrón exitosa");

        $archivoConexion = __DIR__ . '/conexion_prueba_2024.php';
        error_log("Ruta del archivo: $archivoConexion");

        // Leer el ciclo actual del archivo directamente
        $contenidoConexion = file_get_contents($archivoConexion);
        $cicloActualEnArchivo = '24'; // Valor por defecto
        if (preg_match("/define\s*\(\s*['\"]CICLO_ESCOLAR_ACTUAL['\"]\s*,\s*['\"](\d{2})['\"]\s*\)\s*;/", $contenidoConexion, $matches)) {
            $cicloActualEnArchivo = $matches[1];
        }
        error_log("Ciclo actual en archivo: '$cicloActualEnArchivo'");

        if ($nuevoCiclo === $cicloActualEnArchivo) {
            error_log("El ciclo es el mismo, no se actualizará");
            $preferencesMessage = 'El ciclo escolar ya está configurado con el valor proporcionado.';
            $preferencesStatus = 'info';
        } else {
            error_log("Procediendo con actualización de '$cicloActualEnArchivo' a '$nuevoCiclo'");

            // Verificar que el archivo existe
            if (!file_exists($archivoConexion)) {
                error_log("ERROR: El archivo no existe");
                $preferencesMessage = 'El archivo de conexión no existe: ' . $archivoConexion;
                $preferencesStatus = 'error';
            } else {
                // Verificar permisos de lectura
                if (!is_readable($archivoConexion)) {
                    error_log("ERROR: El archivo no es legible");
                    $preferencesMessage = 'No se puede leer el archivo de conexión. Verifique los permisos.';
                    $preferencesStatus = 'error';
                } else {
                    if ($contenidoConexion === false) {
                        error_log("ERROR: No se pudo leer el contenido");
                        $preferencesMessage = 'Error al leer el contenido del archivo de conexión.';
                        $preferencesStatus = 'error';
                    } else {
                        $reemplazos = 0;
                        $patron = "/define\s*\(\s*['\"]CICLO_ESCOLAR_ACTUAL['\"]\s*,\s*['\"](\d{2})['\"]\s*\)\s*;/";
                        $nuevoContenido = preg_replace($patron, "define('CICLO_ESCOLAR_ACTUAL', '$nuevoCiclo');", $contenidoConexion, 1, $reemplazos);

                        error_log("Reemplazos realizados: $reemplazos");

                        if ($nuevoContenido === null) {
                            error_log("ERROR: preg_replace devolvió null");
                            $preferencesMessage = 'Error en el patrón de búsqueda. Contacte al administrador.';
                            $preferencesStatus = 'error';
                        } elseif ($reemplazos !== 1) {
                            error_log("ERROR: No se hizo exactamente 1 reemplazo");
                            $preferencesMessage = 'No se pudo localizar la constante CICLO_ESCOLAR_ACTUAL en el archivo de conexión. Reemplazos: ' . $reemplazos;
                            $preferencesStatus = 'error';
                        } else {
                            // Verificar permisos de escritura
                            if (!is_writable($archivoConexion)) {
                                error_log("ERROR: El archivo no tiene permisos de escritura");
                                $preferencesMessage = 'El archivo no tiene permisos de escritura. Contacte al administrador.';
                                $preferencesStatus = 'error';
                            } else {
                                error_log("Intentando escribir archivo...");
                                $resultado = file_put_contents($archivoConexion, $nuevoContenido, LOCK_EX);

                                error_log("Resultado de file_put_contents: " . ($resultado === false ? 'FALSE' : $resultado . ' bytes'));

                                if ($resultado === false) {
                                    error_log("ERROR: No se pudo escribir el archivo");
                                    $preferencesMessage = 'Error al guardar los cambios en el archivo de conexión.';
                                    $preferencesStatus = 'error';
                                } else {
                                    error_log("ÉXITO: Archivo actualizado correctamente");
                                    $preferencesMessage = "El ciclo escolar se actualizó correctamente de {$cicloActualEnArchivo} a {$nuevoCiclo}. Los cambios se aplicarán en la próxima carga de la página.";
                                    $preferencesStatus = 'success';
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        error_log("ERROR: El valor no cumple con el patrón de 2 dígitos");
        $preferencesMessage = 'Ingrese únicamente dos dígitos para el ciclo escolar (ejemplo: 24).';
        $preferencesStatus = 'error';
    }

    error_log("===== FIN ACTUALIZACIÓN CICLO =====");
}

// AHORA cargar el archivo de conexión (después de procesarlo si hubo POST)
require_once 'conexion_prueba_2024.php';

// Obtener el ciclo escolar actual definido en el sistema
$currentCycle = defined('CICLO_ESCOLAR_ACTUAL') ? CICLO_ESCOLAR_ACTUAL : '24';
$nextCycleDisplay = (string) ((int) $currentCycle + 1);

$preferencesIcons = [
    'success' => 'fa-check-circle',
    'error' => 'fa-exclamation-triangle',
    'info' => 'fa-info-circle'
];

// Obtener información del usuario de la sesión
$userFullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Usuario SEDEQ';
$userEmail = isset($_SESSION['username']) ? $_SESSION['username'] : 'usuario@sedeq.gob.mx';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'Analista de Datos';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | SEDEQ - Sistema de Estadística Educativa</title>
    <link rel="icon" type="image/png" href="https://queretaro.gob.mx/o/queretaro-theme/images/favicon.png">

    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/settings.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <a href="home.php" class="header-nav-link">Inicio</a>
                    <a href="directorio_estatal.php" class="header-nav-link">Escuelas</a>
                    <a href="bibliotecas.php" class="header-nav-link">Bibliotecas</a>
                    <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                        target="_blank" class="header-nav-link">Mapa</a>
                    <a href="settings.php" class="header-nav-link active">Configuración</a>
                </nav>
            </div>


            <?php include 'includes/header_end.php'; ?>

            <!-- Sidebar desplegable (solo móviles) -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <h2>Menú</h2>
                    <button class="sidebar-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <nav class="sidebar-nav">
                    <a href="home.php" class="sidebar-link">
                        <i class="fas fa-home"></i> <span>Inicio</span>
                    </a>
                    <a href="directorio_estatal.php" class="sidebar-link">
                        <i class="fas fa-school"></i> <span>Escuelas</span>
                    </a>
                    <a href="bibliotecas.php" class="sidebar-link">
                        <i class="fas fa-book"></i> <span>Bibliotecas</span>
                    </a>
                    <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                        target="_blank" class="sidebar-link">
                        <i class="fas fa-map-marked-alt"></i> <span>Mapa</span>
                    </a>
                    <a href="settings.php" class="sidebar-link active">
                        <i class="fas fa-cog"></i> <span>Configuración</span>
                    </a>
                </nav>
            </aside>

            <!-- Contenedor principal de la aplicación -->
            <div class="app-container">
                <div class="main-content">
                    <div class="page-title" style="padding: 20px 20px 10px;">
                        <h1 class="section-title">Configuración del Sistema</h1>
                    </div>
                    <div class="container-fluid">
                        <!-- Contenido de configuración -->
                        <div class="settings-container animate-fade"> <!-- Sección: Mi Perfil -->
                            <div class="settings-panel animate-up">
                                <h2 class="settings-title"><i class="fas fa-user-circle"></i> Mi Perfil</h2>
                                <div class="settings-content">
                                    <div class="form-group animate-fade delay-1">
                                        <label for="fullname">Nombre Completo</label>
                                        <input type="text" id="fullname" class="form-control"
                                            value="<?php echo htmlspecialchars($userFullname); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Correo Electrónico</label>
                                        <input type="email" id="email" class="form-control"
                                            value="<?php echo htmlspecialchars($userEmail); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="role">Rol en el Sistema</label>
                                        <input type="text" id="role" class="form-control"
                                            value="<?php echo htmlspecialchars($userRole); ?>" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="institution">Institución</label>
                                        <input type="text" id="institution" class="form-control"
                                            value="Secretaría de Educación del Estado de Querétaro" disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección: Seguridad -->
                            <div class="settings-panel animate-up delay-1">
                                <h2 class="settings-title"><i class="fas fa-shield-alt"></i> Seguridad y Contraseña</h2>
                                <div class="settings-content">
                                    <div class="form-group animate-fade delay-2">
                                        <label for="current_password">Contraseña Actual</label>
                                        <input type="password" id="current_password" class="form-control"
                                            placeholder="Ingrese su contraseña actual">
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password">Nueva Contraseña</label>
                                        <input type="password" id="new_password" class="form-control"
                                            placeholder="Ingrese su nueva contraseña">
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password">Confirmar Nueva Contraseña</label>
                                        <input type="password" id="confirm_password" class="form-control"
                                            placeholder="Confirme su nueva contraseña">
                                    </div>

                                    <div class="form-group">
                                        <small class="form-text text-muted">
                                            La contraseña debe tener al menos 8 caracteres, incluir mayúsculas,
                                            minúsculas,
                                            números
                                            y símbolos.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección: Preferencias -->
                            <div class="settings-panel animate-up delay-2">
                                <h2 class="settings-title"><i class="fas fa-sliders-h"></i> Preferencias del Sistema
                                </h2>
                                <div class="settings-content">
                                    <?php if ($preferencesMessage): ?>
                                        <div
                                            class="settings-alert settings-alert-<?php echo htmlspecialchars($preferencesStatus); ?>">
                                            <i
                                                class="fas <?php echo htmlspecialchars($preferencesIcons[$preferencesStatus] ?? 'fa-info-circle'); ?>"></i>
                                            <span><?php echo htmlspecialchars($preferencesMessage); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group animate-fade delay-3">
                                        <label for="language">Idioma del Sistema</label>
                                        <select id="language" class="form-control">
                                            <option value="es" selected>Español</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="notifications">Notificaciones</label>
                                        <div class="checkbox-wrapper">
                                            <input type="checkbox" id="email_notifications" checked>
                                            <label for="email_notifications">Recibir notificaciones por correo</label>
                                        </div>
                                    </div>

                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                                        class="preferences-form" novalidate>
                                        <input type="hidden" name="accion" value="actualizar_ciclo">
                                        <div class="form-group">
                                            <label for="ciclo_escolar">Ciclo escolar actual (dos dígitos)</label>
                                            <div class="cycle-input-wrapper">
                                                <input type="text" id="ciclo_escolar" name="ciclo_escolar"
                                                    class="form-control cycle-input"
                                                    value="<?php echo htmlspecialchars($currentCycle); ?>" maxlength="2"
                                                    pattern="\d{2}" required>
                                                <span
                                                    class="input-suffix">/<?php echo htmlspecialchars($nextCycleDisplay); ?></span>
                                            </div>
                                            <small class="form-text text-muted">Ejemplo: 24 representa el ciclo
                                                2024-2025.</small>
                                        </div>

                                        <div class="form-actions form-actions-inline">
                                            <button type="submit" class="btn-update-cycle save-button-secondary"
                                                onclick="console.log('Formulario enviado:', document.querySelector('input[name=ciclo_escolar]').value);">
                                                <i class="fas fa-sync-alt"></i> Actualizar ciclo escolar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div> <!-- Acciones de formulario -->
                            <div class="form-actions animate-fade delay-3">
                                <button type="button" class="cancel-button animate-hover">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <button type="button" class="save-button animate-hover">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Sistema de Notificaciones -->
                    <div id="notification-container" class="notification-container"></div>
                    <?php include 'includes/footer.php'; ?>
                </div>
            </div>

            <!-- Scripts -->
            <script src="./js/sidebar.js"></script>
            <script src="./js/settings.js"></script>
            <script src="./js/animations_global.js"></script>

            <!-- Botón volver al inicio -->
            <?php include 'includes/back_to_top.php'; ?>
</body>

</html>