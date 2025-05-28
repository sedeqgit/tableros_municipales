/**
 * Script para la funcionalidad del sidebar en el Sistema SEDEQ
 * Este archivo controla la apertura y cierre del menú lateral en todas las páginas
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
