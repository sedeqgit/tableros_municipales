<?php
/**
 * Archivo helper para la gestión de sesiones en el Sistema SEDEQ
 * Centraliza la lógica de simulación de sesiones para el modo demo del proyecto
 */

/**
 * Inicializa la sesión si no está iniciada y configura un usuario demo si es necesario
 * @param bool $requireAuth - Si es verdadero, redirecciona a login.php si no hay sesión
 * @return void
 */
function iniciarSesionDemo($requireAuth = true)
{
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Para el prototipo, verificar si se requiere autenticación
    if ($requireAuth && !isset($_SESSION['user_id']) && !isset($_GET['demo'])) {
        // Redireccionar a login.php si no hay sesión ni parámetro demo
        header("Location: login.php");
        exit;
    }
}
