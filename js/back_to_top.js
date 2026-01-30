/**
 * =============================================================================
 * FUNCIONALIDAD: BOTÓN VOLVER AL INICIO
 * =============================================================================
 *
 * Maneja la lógica y comportamiento del botón flotante "volver al inicio".
 *
 * CARACTERÍSTICAS:
 * - Mostrar/ocultar según posición del scroll
 * - Scroll suave al hacer clic
 * - Soporte para teclado (accesibilidad)
 * - Throttling para mejor performance
 *
 * @package SEDEQ_Scripts
 * @version 1.0
 */

(function () {
    'use strict';

    // =============================================================================
    // CONFIGURACIÓN
    // =============================================================================

    const CONFIG = {
        scrollThreshold: 300,        // Píxeles de scroll para mostrar el botón
        scrollDuration: 800,         // Duración de la animación (ms)
        throttleDelay: 100           // Delay para throttle del scroll event (ms)
    };

    // =============================================================================
    // VARIABLES GLOBALES
    // =============================================================================

    let backToTopButton = null;
    let lastScrollPosition = 0;
    let ticking = false;

    // =============================================================================
    // FUNCIONES AUXILIARES
    // =============================================================================

    /**
     * Función de throttling para limitar la frecuencia de ejecución
     * Mejora el rendimiento en eventos que se disparan frecuentemente
     */
    function throttle(func, delay) {
        let lastCall = 0;
        return function (...args) {
            const now = Date.now();
            if (now - lastCall >= delay) {
                lastCall = now;
                func(...args);
            }
        };
    }

    /**
     * Scroll suave hasta el inicio de la página
     * Usa requestAnimationFrame para animación fluida
     */
    function smoothScrollToTop() {
        const startPosition = window.pageYOffset;
        const startTime = performance.now();

        function animation(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / CONFIG.scrollDuration, 1);

            // Función de easing (ease-in-out)
            const easing = progress < 0.5
                ? 2 * progress * progress
                : -1 + (4 - 2 * progress) * progress;

            window.scrollTo(0, startPosition * (1 - easing));

            if (progress < 1) {
                requestAnimationFrame(animation);
            }
        }

        requestAnimationFrame(animation);
    }

    /**
     * Alternativa: Scroll usando behavior smooth (más simple pero menos control)
     */
    function scrollToTopNative() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    /**
     * Actualiza la visibilidad del botón según el scroll
     */
    function updateButtonVisibility() {
        const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollPosition > CONFIG.scrollThreshold) {
            backToTopButton.classList.add('visible');
        } else {
            backToTopButton.classList.remove('visible');
        }

        lastScrollPosition = scrollPosition;
        ticking = false;
    }

    /**
     * Manejador del evento scroll (con throttling)
     */
    function handleScroll() {
        if (!ticking) {
            window.requestAnimationFrame(updateButtonVisibility);
            ticking = true;
        }
    }

    /**
     * Manejador del click en el botón
     */
    function handleClick(event) {
        event.preventDefault();

        // Usar scroll nativo si el navegador lo soporta bien
        if ('scrollBehavior' in document.documentElement.style) {
            scrollToTopNative();
        } else {
            // Fallback a animación personalizada
            smoothScrollToTop();
        }

        // Analytics (opcional - descomentar si se usa Google Analytics)
        // if (typeof gtag !== 'undefined') {
        //     gtag('event', 'click', {
        //         'event_category': 'navigation',
        //         'event_label': 'back_to_top'
        //     });
        // }
    }

    /**
     * Manejador de teclado para accesibilidad
     */
    function handleKeyboard(event) {
        // Enter o Espacio
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            handleClick(event);
        }
    }

    // =============================================================================
    // INICIALIZACIÓN
    // =============================================================================

    /**
     * Inicializa el botón y sus event listeners
     */
    function init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        // Obtener referencia al botón
        backToTopButton = document.getElementById('back-to-top');

        if (!backToTopButton) {
            console.warn('Botón "back-to-top" no encontrado en el DOM');
            return;
        }

        // Event Listeners
        backToTopButton.addEventListener('click', handleClick);
        backToTopButton.addEventListener('keydown', handleKeyboard);

        // Scroll event con throttling
        const throttledScroll = throttle(handleScroll, CONFIG.throttleDelay);
        window.addEventListener('scroll', throttledScroll, { passive: true });

        // Verificar posición inicial al cargar
        updateButtonVisibility();

        console.log('✓ Botón "Volver al inicio" inicializado correctamente');
    }

    // =============================================================================
    // LIMPIEZA (opcional)
    // =============================================================================

    /**
     * Limpia los event listeners (útil para SPAs)
     */
    function destroy() {
        if (backToTopButton) {
            backToTopButton.removeEventListener('click', handleClick);
            backToTopButton.removeEventListener('keydown', handleKeyboard);
        }
        window.removeEventListener('scroll', handleScroll);
    }

    // Exponer función de limpieza globalmente (opcional)
    window.backToTopDestroy = destroy;

    // =============================================================================
    // EJECUTAR INICIALIZACIÓN
    // =============================================================================

    init();

})();
