/**
 * Script para el Home y Settings del Sistema SEDEQ
 * Nota: La funcionalidad del sidebar se movió a sidebar.js para centralizar la funcionalidad
 */
document.addEventListener('DOMContentLoaded', function() {
    // El código del sidebar se ha movido a sidebar.js
    
    // Dropdown del menú de usuario
    const userAvatar = document.querySelector('.user-avatar');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (userAvatar && userDropdown) {
        userAvatar.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
        });
        
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && !userAvatar.contains(e.target)) {
                userDropdown.style.display = 'none';
            }
        });
    }
    
    // Inicializar tooltips si existen
    const tooltipTriggers = document.querySelectorAll('[data-toggle="tooltip"]');
    
    if (tooltipTriggers.length > 0) {
        tooltipTriggers.forEach(trigger => {
            trigger.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = this.getAttribute('data-tooltip');
                document.body.appendChild(tooltip);
                
                const triggerRect = this.getBoundingClientRect();
                tooltip.style.top = triggerRect.bottom + 10 + 'px';
                tooltip.style.left = triggerRect.left + (triggerRect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.opacity = '1';
                
                this.addEventListener('mouseleave', function() {
                    tooltip.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(tooltip);
                    }, 300);
                });
            });
        });
    }
});

/**
 * Función para mostrar municipios adicionales
 * Se ejecuta cuando el usuario hace clic en "Ver más municipios"
 */
function mostrarMasMunicipios() {
    const municipiosAdicionales = document.querySelectorAll('.municipio-adicional');
    const btnVerMas = document.getElementById('btn-ver-mas');
    
    if (municipiosAdicionales.length > 0 && btnVerMas) {
        // Ocultar el botón "Ver más" primero
        btnVerMas.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        btnVerMas.style.opacity = '0';
        btnVerMas.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            btnVerMas.style.display = 'none';
        }, 300);
        
        // Mostrar municipios adicionales con animaciones escalonadas
        municipiosAdicionales.forEach((tarjeta, index) => {
            // Preparar la tarjeta para la animación
            tarjeta.style.opacity = '0';
            tarjeta.style.transform = 'translateY(20px) scale(0.9)';
            tarjeta.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            
            // Mostrar la tarjeta con delay escalonado
            setTimeout(() => {
                tarjeta.style.display = 'block';
                
                // Pequeño delay para que se aplique el display antes de la animación
                setTimeout(() => {
                    tarjeta.style.opacity = '1';
                    tarjeta.style.transform = 'translateY(0) scale(1)';
                }, 10);
                
            }, index * 120 + 400); // Delay escalonado más suave
        });
        
        // Aplicar efecto de reflow del grid después de que se muestren las tarjetas
        setTimeout(() => {
            const grid = document.querySelector('.dashboard-grid');
            if (grid) {
                grid.style.transition = 'all 0.3s ease-out';
                // Forzar reflow del grid
                grid.style.gridTemplateColumns = grid.style.gridTemplateColumns;
            }
        }, municipiosAdicionales.length * 120 + 600);
    }
}
