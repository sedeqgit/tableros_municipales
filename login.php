<?php
// Iniciar sesión
session_start();

// Si ya hay una sesión activa, redirigir al home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | SEDEQ - Estadística Educativa</title>
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="./img/layout_set_logo.png" alt="Logo SEDEQ" class="login-logo">
                <h1>SISTEMA DE ESTADÍSTICA EDUCATIVA</h1>
                <p>Secretaría de Educación del Estado de Querétaro</p>
            </div>
            <div class="login-form">
                <form id="loginForm" action="process_login.php" method="post">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Usuario</label>
                        <input type="text" id="username" name="username" placeholder="Ingrese su nombre de usuario"
                            class="input-focus-effect" required>
                        <div id="usernameError" class="error-message">
                            <i class="fas fa-exclamation-circle"></i> Por favor ingrese su nombre de usuario
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" placeholder="Ingrese su contraseña"
                                class="input-focus-effect" required>
                            <button type="button" id="togglePassword" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordError" class="error-message">
                            <i class="fas fa-exclamation-circle"></i> Por favor ingrese su contraseña
                        </div>
                    </div>
                    <div id="loginError" class="error-message">
                        <i class="fas fa-exclamation-triangle"></i> Credenciales incorrectas. Por favor, intente de
                        nuevo.
                    </div>
                    <div class="form-group remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recordar credenciales</label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="login-button">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                    </div>
                    <div class="login-credentials-demo">
                        <p>Credenciales de demostración:</p>
                        <code>practicas25.dppee@gmail.com / Balluff254</code>
                    </div>
                    <div class="form-links">
                        <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> - Secretaría de Educación del Estado de Querétaro</p>
            <div class="help-link">
                <a href="https://portal.queretaro.gob.mx/educacion/"><i class="fas fa-question-circle"></i> Ayuda</a>
            </div>
        </div>
    </div>
    <script src="./js/login.js"></script>
</body>

</html>