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
