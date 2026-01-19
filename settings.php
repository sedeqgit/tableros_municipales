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
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Overlay para cerrar el menú en móviles -->
    <div class="sidebar-overlay"></div>

    <!-- ======================================== -->
    <!-- BARRA LATERAL DE NAVEGACIÓN             -->
    <!-- ======================================== -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <ul>
                <!-- Enlace a página principal -->
                <li class="nav-item">
                    <a href="home.php"><i class="fas fa-home"></i> <span>Inicio</span></a>
                </li>
                <!-- Enlace a mapa educativo -->
                <li class="nav-item">
                    <a href="https://www.google.com/maps/d/edit?mid=1LLMZpgMl4X4QSjzNlHQsHgZoNLj1kv4&usp=sharing"
                        target="_blank"><i class="fas fa-map-marked-alt"></i> <span>Mapa Educativo</span></a>
                </li>
                <!-- Enlace a bibliotecas -->
                <li class="nav-item">
                    <a href="bibliotecas.php"><i class="fas fa-book"></i> <span>Bibliotecas</span></a>
                </li>
                <!-- Enlace a búsqueda de escuelas -->
                <li class="nav-item">
                    <a href="directorio_estatal.php"><i class="fas fa-search"></i> <span>Búsqueda de Escuelas</span></a>
                </li>
                <!-- Enlace a configuración (ACTIVO) -->
                <li class="nav-item active">
                    <a href="settings.php"><i class="fas fa-cog"></i> <span>Configuración</span></a>
                </li>
            </ul>
        </nav>
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
                <div class="settings-panel animate-up delay-2">
                    <h2 class="settings-title"><i class="fas fa-sliders-h"></i> Preferencias del Sistema</h2>
                    <div class="settings-content">
                        <?php if ($preferencesMessage): ?>
                            <div class="settings-alert settings-alert-<?php echo htmlspecialchars($preferencesStatus); ?>">
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
                                <small class="form-text text-muted">Ejemplo: 24 representa el ciclo 2024-2025.</small>
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
                                                Preguntas dudas, comentarios sobre el contenido del portal.</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6 col-6">
                                        <div class="link-footer">
                                            <img src="https://queretaro.gob.mx/o/queretaro-theme/images/correo.png"
                                                width="100px" height="auto">
                                            <h3 class="tf-title">WEB MASTER</h3>
                                            <p class="p-footer">
                                                Preguntas dudas, comentarios sobre el funcionamiento del portal.</p>
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
                                                <li class="social_"><a href="https://www.facebook.com/GobQro?fref=ts"
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
                                                        href="https://www.instagram.com/gobiernoqueretaro/"
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

    <!-- Scripts -->
    <script src="./js/sidebar.js"></script>
    <script src="./js/settings.js"></script>
    <script src="./js/animations_global.js"></script>
</body>

</html>