/**
 * Script global de animaciones para el sistema SEDEQ
 * 
 * Este script gestiona la aplicación de animaciones a los elementos del sitio,
 * asegurando una experiencia visual atractiva pero ligera.
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', () => {
    // Iniciar animaciones con un pequeño retraso para asegurar que todo esté cargado
    setTimeout(initAnimations, 100);
});

/**
 * Inicializa todas las animaciones del sitio
 */
function initAnimations() {
    // Animar los elementos con clases específicas
    animateElements('.animate-fade');
    animateElements('.animate-up');
    animateElements('.animate-right');
    animateElements('.animate-left');
    animateElements('.animate-scale');
    
    // Iniciar animaciones secuenciales
    initSequences();
    
    // Animaciones específicas para diferentes tipos de contenido
    animatePanels();
    animateLevelBars();
    initTabsAnimation();
}

/**
 * Anima elementos con clases específicas
 * @param {string} selector - Selector CSS para los elementos
 */
function animateElements(selector) {
    const elements = document.querySelectorAll(selector);
    
    elements.forEach((el, index) => {
        // Si no tiene un retraso específico, aplica uno automático basado en el índice
        if (!hasDelayClass(el)) {
            el.style.animationDelay = `${index * 80}ms`;
        }
    });
}

/**
 * Verifica si un elemento ya tiene una clase de retraso
 * @param {Element} element - Elemento a verificar
 * @returns {boolean} - Verdadero si ya tiene una clase de retraso
 */
function hasDelayClass(element) {
    return element.classList.contains('delay-1') || 
           element.classList.contains('delay-2') || 
           element.classList.contains('delay-3') || 
           element.classList.contains('delay-4') || 
           element.classList.contains('delay-5');
}

/**
 * Inicia animaciones secuenciales
 */
function initSequences() {
    const containers = document.querySelectorAll('.animate-sequence');
    
    containers.forEach(container => {
        const children = Array.from(container.children);
        
        children.forEach((child, index) => {
            // Añadir la clase de animación fade y configurar el retraso
            if (!child.classList.contains('animate-fade')) {
                child.classList.add('animate-fade');
            }
            
            child.style.animationDelay = `${100 + (index * 100)}ms`;
        });
    });
}

/**
 * Anima los paneles del sitio
 */
function animatePanels() {
    const panels = document.querySelectorAll('.panel');
    
    panels.forEach((panel, index) => {
        if (!panel.classList.contains('animate-fade') && 
            !panel.classList.contains('animate-up')) {
            
            panel.classList.add('animate-up');
            panel.style.animationDelay = `${index * 150}ms`;
        }
        
        // Anima header del panel si existe
        const header = panel.querySelector('.panel-header');
        if (header && !header.classList.contains('animate-fade')) {
            header.classList.add('animate-fade');
            header.style.animationDelay = `${(index * 150) + 100}ms`;
        }
        
        // Anima el cuerpo del panel
        const body = panel.querySelector('.panel-body');
        if (body && !body.classList.contains('animate-fade')) {
            body.classList.add('animate-fade');
            body.style.animationDelay = `${(index * 150) + 200}ms`;
        }
    });
}

/**
 * Anima las barras de nivel (específicas para gráficos de barras)
 */
function animateLevelBars() {
    const levelBars = document.querySelectorAll('.level-track');
    
    levelBars.forEach((bar, index) => {
        const fill = bar.querySelector('.level-fill');
        
        if (fill) {
            // Obtener el ancho final que debería tener
            const targetWidth = fill.style.width;
            
            // Reiniciar el ancho para la animación
            fill.style.width = '0%';
            
            // Animar después de un retraso
            setTimeout(() => {
                fill.style.transition = 'width 0.8s ease';
                fill.style.width = targetWidth;
            }, 300 + (index * 100));
        }
    });
}

/**
 * Inicializa la animación para pestañas
 */
function initTabsAnimation() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            const targetContent = document.getElementById(tabId);
            
            // Añadir animación al cambiar de pestaña
            tabContents.forEach(content => {
                if (content.classList.contains('active') && content !== targetContent) {
                    content.style.animation = 'fadeIn 0.4s ease';
                }
            });
            
            if (targetContent) {
                targetContent.style.animation = 'fadeIn 0.4s ease';
            }
        });
    });
}

// Exportar funciones públicas
window.SEDEQ_Animations = {
    init: initAnimations,
    animateElements: animateElements,
    animatePanels: animatePanels,
    animateLevelBars: animateLevelBars
};
