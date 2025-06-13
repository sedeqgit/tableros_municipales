/**
 * =============================================================================
 * CONTROLADOR DE NAVEGACIÓN LATERAL - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Este módulo gestiona toda la funcionalidad del menú de navegación lateral
 * en el dashboard estadístico de SEDEQ, proporcionando una experiencia de
 * usuario consistente y responsiva en todas las páginas del sistema.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Apertura y cierre del menú lateral en dispositivos móviles
 * - Gestión de estados colapsado/expandido del sidebar
 * - Sistema de overlay para cerrar menú en dispositivos táctiles
 * - Adaptación automática según el tamaño de la ventana
 * - Preservación del icono hamburguesa para consistencia visual
 * 
 * COMPORTAMIENTOS RESPONSIVOS:
 * - <= 992px: Modo colapsado por defecto con overlay
 * - > 992px: Sidebar visible permanentemente
 * - Transiciones suaves para cambios de estado
 * - Eventos de resize para adaptación dinámica
 * 
 * ELEMENTOS DOM REQUERIDOS:
 * - #sidebarToggle: Botón para alternar estado del menú
 * - .sidebar: Contenedor principal del menú lateral
 * - .main-content: Área principal que se ajusta según el estado del sidebar
 * - .sidebar-overlay: Capa de overlay para cerrar el menú en móviles
 * 
 * @version 2.0
 * @requires Font Awesome para iconos
 */

// =============================================================================
// INICIALIZACIÓN DEL CONTROLADOR DE SIDEBAR
// =============================================================================

/**
 * Punto de entrada principal del sistema de navegación
 * 
 * Se ejecuta cuando el DOM está completamente cargado para garantizar que
 * todos los elementos estén disponibles antes de asignar event listeners.
 */
document.addEventListener('DOMContentLoaded', function() {    
    // Toggle para la barra lateral en dispositivos móviles
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Mostrar/ocultar overlay en dispositivos móviles
            if (window.innerWidth <= 992) {
                overlay.classList.toggle('active');
            }
              // Mantenemos el icono como hamburguesa siempre
            const icon = this.querySelector('i');
            if (icon) {
                // Aseguramos que siempre sea el icono de barras (hamburguesa)
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Cerrar menú al hacer clic en el overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                overlay.classList.remove('active');
                  // Aseguramos que el icono sea siempre hamburguesa
                const icon = sidebarToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
        
        // Ajustar menú según ancho de la ventana
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 992) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                overlay.classList.remove('active');
            }
        });
        
        // Inicialmente colapsar en dispositivos móviles
        if (window.innerWidth <= 992) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    }
});
