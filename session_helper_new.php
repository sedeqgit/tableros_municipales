<?php
/**
 * =============================================================================
 * MÓDULO HELPER DE GESTIÓN DE SESIONES - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Este módulo centraliza la lógica de gestión de sesiones para el sistema,
 * proporcionando funcionalidades de autenticación y modo demostración.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Inicialización automática de sesiones PHP
 * - Modo demostración para presentaciones y pruebas
 * - Redirección automática a login para páginas protegidas
 * - Validación de estado de sesión y autenticación
 * 
 * CASOS DE USO:
 * - Desarrollo y testing sin base de datos de usuarios
 * - Demostraciones del sistema para stakeholders
 * - Prototipado rápido de funcionalidades
 * - Validación de flujos de autenticación
 * 
 * ARQUITECTURA DE SEGURIDAD:
 * - Verificación de estado de sesión antes de inicialización
 * - Redirección automática para páginas que requieren autenticación
 * - Soporte para parámetros de bypass en modo demo
 * - Separación de lógica de autenticación del código de aplicación
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Security
 * @version 1.0
 * @since 2024
 */

/**
 * =============================================================================
 * FUNCIÓN PRINCIPAL DE INICIALIZACIÓN DE SESIÓN
 * =============================================================================
 * 
 * Inicializa la sesión PHP si no está activa y configura el entorno para
 * funcionamiento con autenticación real o en modo demostración.
 * 
 * LÓGICA DE FUNCIONAMIENTO:
 * 1. Verifica si la sesión PHP ya está iniciada para evitar conflictos
 * 2. Inicia sesión PHP si es necesario
 * 3. Evalúa si la página requiere autenticación
 * 4. Permite bypass con parámetro GET 'demo' para demostraciones
 * 5. Redirecciona a login.php si no hay sesión válida
 * 
 * PARÁMETROS DE CONTROL:
 * - $_SESSION['user_id']: Indica usuario autenticado válido
 * - $_GET['demo']: Permite acceso sin autenticación para demos
 * - $requireAuth: Controla si la página requiere autenticación
 * 
 * @param bool $requireAuth Si es verdadero, requiere autenticación o modo demo
 * @return void
 * @since 1.0
 */
function iniciarSesionDemo($requireAuth = true)
{
    // =============================================================================
    // INICIALIZACIÓN SEGURA DE SESIÓN PHP
    // =============================================================================
    
    // Verificar estado actual de la sesión para prevenir errores de reinicialización
    // PHP_SESSION_NONE indica que no hay sesión activa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // =============================================================================
    // VALIDACIÓN DE AUTENTICACIÓN Y MODO DEMO
    // =============================================================================
    
    // Evaluar si se requiere autenticación para la página actual
    // Permite acceso si existe sesión válida O si se especifica modo demo
    if ($requireAuth && !isset($_SESSION['user_id']) && !isset($_GET['demo'])) {
        // Redireccionar a página de login para autenticación
        header("Location: login.php");
        exit; // Terminar ejecución para prevenir acceso no autorizado
    }
}
?>
