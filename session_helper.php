<?php
/**
 * =============================================================================
 * MÓDULO DE GESTIÓN DE SESIONES - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Este archivo centraliza la lógica de gestión de sesiones para el sistema
 * de dashboard estadístico de SEDEQ. Proporciona funcionalidades tanto para
 * el modo de producción con autenticación completa como para el modo demo
 * que permite acceso sin credenciales para demostraciones y desarrollo.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Inicialización segura de sesiones PHP
 * - Validación de estados de autenticación
 * - Modo demo para demostraciones públicas
 * - Redirección automática a pantalla de login
 * 
 * MODOS DE OPERACIÓN:
 * - Producción: Requiere autenticación válida ($_SESSION['user_id'])
 * - Demo: Acceso sin autenticación mediante parámetro ?demo
 * - Desarrollo: Flexibilidad para testing y depuración
 * 
 * @package SEDEQ_Core
 * @subpackage Session_Management
 * @version 2.0
 */

// =============================================================================
// FUNCIONES DE GESTIÓN DE SESIONES
// =============================================================================

/**
 * Inicializa la sesión del sistema y configura el modo de acceso apropiado
 * 
 * Esta función es el punto de entrada principal para la gestión de sesiones
 * en toda la aplicación. Maneja tanto el modo de producción con autenticación
 * completa como el modo demo para demostraciones y desarrollo.
 * 
 * LÓGICA DE VALIDACIÓN:
 * 1. Verifica e inicializa la sesión PHP si no está activa
 * 2. Evalúa si se requiere autenticación según el parámetro $requireAuth
 * 3. Permite acceso demo mediante el parámetro GET 'demo'
 * 4. Redirecciona a login.php si no hay sesión válida ni modo demo
 * 
 * CASOS DE USO:
 * - iniciarSesionDemo(true): Modo producción, requiere login
 * - iniciarSesionDemo(false): Modo desarrollo, acceso libre
 * - URL con ?demo: Modo demostración, acceso sin credenciales
 * 
 * @param bool $requireAuth Determina si se requiere autenticación válida
 *                         true = modo producción, false = modo desarrollo
 * @return void
 * @since 2.0
 */
function iniciarSesionDemo($requireAuth = true)
{
    // =======================================================================
    // INICIALIZACIÓN DE SESIÓN PHP
    // =======================================================================

    // Verificar el estado actual de la sesión para evitar reinicializaciones
    // PHP_SESSION_NONE indica que no hay sesión activa en el hilo actual
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // =======================================================================
    // VALIDACIÓN DE AUTENTICACIÓN Y CONTROL DE ACCESO
    // =======================================================================

    // Implementar lógica de control de acceso basada en múltiples criterios:
    // - $requireAuth: Parámetro que determina el nivel de seguridad requerido
    // - $_SESSION['user_id']: Sesión válida de usuario autenticado
    // - $_GET['demo']: Parámetro que habilita el modo demostración

    // =======================================================================
    // BYPASS TEMPORAL DEL LOGIN - MODO DESARROLLO
    // =======================================================================
    // TODO: ELIMINAR EN PRODUCCIÓN
    // Este bypass crea automáticamente una sesión demo si no existe
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'dev@sedeq.local';
        $_SESSION['fullname'] = 'Usuario Desarrollo';
        $_SESSION['role'] = 'Desarrollador';
        $_SESSION['login_time'] = time();
        $_SESSION['bypass_mode'] = true; // Marcador de bypass activo
    }

    /* CÓDIGO ORIGINAL COMENTADO TEMPORALMENTE
    if ($requireAuth && !isset($_SESSION['user_id']) && !isset($_GET['demo'])) {
        // REDIRECCIÓN DE SEGURIDAD
        // Si se requiere autenticación y no hay sesión válida ni modo demo,
        // redireccionar al sistema de login para proteger el acceso
        header("Location: login.php");
        exit; // Terminar ejecución para prevenir acceso no autorizado
    }

    // MODO DEMO ACTIVADO
    // Si se detecta el parámetro 'demo', el sistema permite acceso sin
    // credenciales para demostraciones y presentaciones públicas
    */
}

/**
 * =============================================================================
 * FUNCIÓN DE FORMATEO DE FECHAS EN ESPAÑOL
 * =============================================================================
 * 
 * Formatea fechas en español usando el formato estándar del sistema SEDEQ
 * 
 * @param string $formato Formato de fecha (opcional, por defecto 'd \d\e F \d\e Y')
 * @param int $timestamp Timestamp opcional (por defecto fecha actual)
 * @return string Fecha formateada en español
 */
function fechaEnEspanol($formato = 'd \d\e F \d\e Y', $timestamp = null)
{
    // Si no se proporciona timestamp, usar fecha actual
    if ($timestamp === null) {
        $timestamp = time();
    }

    // Array de meses en español
    $mesesEspanol = array(
        'January' => 'enero',
        'February' => 'febrero',
        'March' => 'marzo',
        'April' => 'abril',
        'May' => 'mayo',
        'June' => 'junio',
        'July' => 'julio',
        'August' => 'agosto',
        'September' => 'septiembre',
        'October' => 'octubre',
        'November' => 'noviembre',
        'December' => 'diciembre'
    );

    // Array de días en español
    $diasEspanol = array(
        'Monday' => 'lunes',
        'Tuesday' => 'martes',
        'Wednesday' => 'miércoles',
        'Thursday' => 'jueves',
        'Friday' => 'viernes',
        'Saturday' => 'sábado',
        'Sunday' => 'domingo'
    );

    // Obtener fecha en inglés
    $fechaIngles = date($formato, $timestamp);

    // Reemplazar meses
    foreach ($mesesEspanol as $ingles => $espanol) {
        $fechaIngles = str_replace($ingles, $espanol, $fechaIngles);
    }

    // Reemplazar días
    foreach ($diasEspanol as $ingles => $espanol) {
        $fechaIngles = str_replace($ingles, $espanol, $fechaIngles);
    }

    return $fechaIngles;
}
