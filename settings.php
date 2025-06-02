<?php
// Incluir el helper de sesiones
require_once 'session_helper.php';

// Iniciar sesión y configurar usuario de demo si es necesario
iniciarSesionDemo();

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
    <title>Configuración | SEDEQ - Sistema de Estadística Educativa</title>    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/settings.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/home.css">
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
                <h1>Configuración del Sistema</h1>
            </div>
            <div class="utilities">
                <div class="date-display">
                    <i class="far fa-calendar-alt"></i>
                    <span id="current-date"><?php echo date('d \d\e F \d\e Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="settings-grid">
                <!-- Navegación de configuración -->
                <div class="settings-nav">
                    <div class="settings-nav-title">
                        <i class="fas fa-cog"></i>
                        Configuración
                    </div>
                    <ul class="settings-nav-items">
                        <li class="settings-nav-item">
                            <a href="#" class="settings-nav-link active" data-section="profile">
                                <i class="fas fa-user"></i>
                                Mi Perfil
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#" class="settings-nav-link" data-section="security">
                                <i class="fas fa-shield-alt"></i>
                                Seguridad
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#" class="settings-nav-link" data-section="preferences">
                                <i class="fas fa-sliders-h"></i>
                                Preferencias
                            </a>
                        </li>
                        <li class="settings-nav-item">
                            <a href="#" class="settings-nav-link" data-section="notifications">
                                <i class="fas fa-bell"></i>
                                Notificaciones
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contenido de configuración -->
                <div class="settings-content">
                    <!-- Sección: Mi Perfil -->
                    <div class="settings-section" id="profile-section">
                        <h2 class="settings-title">Mi Perfil</h2>

                        <div class="form-group">
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

                    <!-- Sección: Seguridad -->
                    <div class="settings-section">
                        <h2 class="settings-title">Seguridad y Contraseña</h2>

                        <div class="form-group">
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

                    <!-- Acciones de formulario -->
                    <div class="form-actions">
                        <button type="button" class="cancel-button">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="save-button">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>

                    <!-- Zona de peligro -->
                    <div class="danger-zone">
                        <h3><i class="fas fa-exclamation-triangle"></i> Zona de Peligro</h3>
                        <p>Las siguientes acciones son irreversibles. Por favor, proceda con precaución.</p>
                        <button type="button" class="danger-button">
                            <i class="fas fa-user-slash"></i> Desactivar Cuenta
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Secretaría de Educación del Estado de Querétaro - Todos los derechos
                reservados</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="./js/sidebar.js"></script>
    <script src="./js/settings.js"></script>
    <script src="./js/animations_global.js"></script>
</body>

</html>