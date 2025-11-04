<?php
// Iniciar sesión
session_start();

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $remember = isset($_POST['remember']) ? true : false;
    $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

    // =======================================================================
    // BYPASS TEMPORAL DEL CAPTCHA - MODO DESARROLLO
    // =======================================================================
    // TODO: ELIMINAR EN PRODUCCIÓN
    $bypass_captcha = true; // Cambiar a false para activar validación de captcha

    if (!$bypass_captcha) {
        // Verificar CAPTCHA (código original)
        $secret_key = "6LfWfvwrAAAAAJtKHMY_7-KmFD45CADhEH57Tolq";

        if (empty($recaptcha_response)) {
            echo json_encode(['success' => false, 'message' => 'Por favor complete el CAPTCHA', 'type' => 'captcha']);
            exit;
        }

        // Verificar el CAPTCHA con Google
        $verify_url = "https://www.google.com/recaptcha/api/siteverify";
        $response = file_get_contents($verify_url . "?secret=" . $secret_key . "&response=" . $recaptcha_response);
        $response_data = json_decode($response);

        if (!$response_data->success) {
            echo json_encode(['success' => false, 'message' => 'CAPTCHA inválido. Por favor intente nuevamente.', 'type' => 'captcha']);
            exit;
        }
    }
    // Si bypass_captcha = true, se omite toda la validación del captcha

    // Datos de usuario para demostración (normalmente estarían en una base de datos)
    $demo_username = 'practicas25.dppee@gmail.com';
    $demo_password = 'Balluff254';

    // Validar las credenciales
    if ($username === $demo_username && $password === $demo_password) {
        // Credenciales válidas, crear sesión
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['fullname'] = 'Usuario SEDEQ';
        $_SESSION['role'] = 'Analista de Datos';
        $_SESSION['login_time'] = time();

        // Si se seleccionó "recordar", crear una cookie que dure 30 días
        if ($remember) {
            setcookie('remember_user', $username, time() + (86400 * 30), "/"); // 86400 = 1 día
        }

        // Devolver respuesta de éxito en formato JSON
        echo json_encode(['success' => true, 'redirect' => 'home.php']);
        exit;
    } else {
        // Credenciales inválidas
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas', 'type' => 'credentials']);
        exit;
    }
} else {
    // Si se accede directamente a este archivo sin enviar el formulario
    header("Location: login.php");
    exit;
}
?>