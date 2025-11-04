<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

// Cargar utilidades de conexión para acceder al ciclo escolar actual
require_once 'conexion_prueba_2024.php';

// Variables para mostrar retroalimentación al usuario
$preferencesMessage = null;
$preferencesStatus = 'success';

// Obtener el ciclo escolar actual definido en el sistema
$currentCycle = defined('CICLO_ESCOLAR_ACTUAL') ? CICLO_ESCOLAR_ACTUAL : '24';

// Procesar envío del formulario de actualización de ciclo escolar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_ciclo') {
    $nuevoCiclo = isset($_POST['ciclo_escolar']) ? trim($_POST['ciclo_escolar']) : '';

    if (preg_match('/^\d{2}$/', $nuevoCiclo)) {
        if ($nuevoCiclo === $currentCycle) {
            $preferencesMessage = 'El ciclo escolar ya está configurado con el valor proporcionado.';
            $preferencesStatus = 'info';
        } else {
            $archivoConexion = __DIR__ . '/conexion_prueba_2024.php';
            $contenidoConexion = @file_get_contents($archivoConexion);

            if ($contenidoConexion === false) {
                $preferencesMessage = 'No fue posible leer la configuración actual. Verifique los permisos del archivo.';
                $preferencesStatus = 'error';
            } else {
                $reemplazos = 0;
                $patron = "/define\(\s*'CICLO_ESCOLAR_ACTUAL'\s*,\s*'\d{2}'\s*\);/";
                $nuevoContenido = preg_replace($patron, "define('CICLO_ESCOLAR_ACTUAL', '$nuevoCiclo');", $contenidoConexion, 1, $reemplazos);

                if ($nuevoContenido === null || $reemplazos !== 1) {
                    $preferencesMessage = 'No se pudo localizar la constante CICLO_ESCOLAR_ACTUAL en el archivo de conexión.';
                    $preferencesStatus = 'error';
                } else {
                    $resultado = @file_put_contents($archivoConexion, $nuevoContenido, LOCK_EX);

                    if ($resultado === false) {
                        $preferencesMessage = 'Ocurrió un error al guardar la nueva configuración. Verifique permisos de escritura.';
                        $preferencesStatus = 'error';
                    } else {
                        $preferencesMessage = "El ciclo escolar se actualizó correctamente a {$nuevoCiclo}.";
                        $preferencesStatus = 'success';
                        $currentCycle = $nuevoCiclo;
                    }
                }
            }
        }
    } else {
        $preferencesMessage = 'Ingrese únicamente dos dígitos para el ciclo escolar (ejemplo: 24).';
        $preferencesStatus = 'error';
    }
}

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
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/settings.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Overlay para cerrar el menú en móviles -->
    <div class="sidebar-overlay"></div> <!-- Barra lateral -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item">
                    <a href="home.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
                </li>
                <li class="nav-item">
                    <a href="#"><i class="fas fa-map-marked-alt"></i> <span>Mapa Educativo</span></a>
                </li>
                <li class="nav-item">
                    <a href="historicos.php"><i class="fas fa-history"></i> <span>Históricos</span></a>
                </li>
                <li class="nav-item">
                    <a href="#"><i class="fas fa-file-alt"></i> <span>Reportes</span></a>
                </li>
                <li class="nav-item active">
                    <a href="settings.php"><i class="fas fa-cog"></i> <span>Configuración</span></a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Cerrar
                    Sesión</span></a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="menu-toggle">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title">
                <h1 class="section-title">Configuración del Sistema</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo fechaEnEspanol('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
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
                                La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números
                                y símbolos.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Sección: Preferencias -->
                <div                         <?php if ($preferencesMessage): ?>
                        <div class="settings-alert settings-alert-<?php echo htmlspecialchars($preferencesStatus); ?>">
                            <i class="fas <?php echo htmlspecialchars($preferencesIcons[$preferencesStatus] ?? 'fa-info-circle'); ?>"></i>
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
                                <input type="checkbox                        <form method="POST" class="preferences-form">
email_notifications">Recibir notificaciones por correo</label>
                            </div>
                        </div>

                        <form method="POST" class="preferences-form" novalidate>
                            <input type="hidden" name="accion" value="actualizar_ciclo">
                            <div class="form-group">
                                <label for="ciclo_escolar">Ciclo escolar actual (dos dígitos)</label>
                                <div class="cycle-input-wrapper">
                                                                   <span class="input-suffix">/<?php echo htmlspecialchars($nextCycleDisplay); ?></span>
echo htmlspecialchars($currentCycle); ?>" maxlength="2" pattern="\d{2}" required>
                                    <span class="input-suffix">/<?php echo htmlspecialchars((string) ((int) $currentCycle + 1)); ?></span>
                                </div>
                                <small class="form-text text-muted">Ejemplo: 24 representa el ciclo 2024-2025.</small>
                            </div>

                            <div class="form-actions form-actions-inline">
                                <button type="submit" class="save-button save-button-secondary">
                                    <i class="fas fa-sync-alt"></i> Actualizar ciclo escolar
                                </button>
                            </div>
                        </form>
d="email_notifications" checked>
                                <label for="email_notifications">Recibir notificaciones por correo</label>
                            </div>
                        </div>
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

                <!-- Zona de peligro -->
                <div class="danger-zone animate-up delay-4">
                    <h3><i class="fas fa-exclamation-triangle"></i> Zona de Peligro</h3>
                    <p>Las siguientes acciones son irreversibles. Por favor, proceda con precaución.</p>
                    <button type="button" class="danger-button animate-hover">
                        <i class="fas fa-user-slash"></i> Desactivar Cuenta
                    </button>
                </div>
            </div>
        </div>

        <!-- Sistema de Notificaciones -->
        <div id="notification-container" class="notification-container"></div>
        <footer class="main-footer">
            <p>&copy; <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="./js/sidebar.js"></script>
    <script src="./js/settings.js"></script>
    <script src="./js/animations_global.js"></script>
</body>

</html>