/**
 * =============================================================================
 * CONTROLADOR DE FUNCIONALIDAD DE MAPAS - SISTEMA SEDEQ
 * =============================================================================
 *
 * Este módulo gestiona la funcionalidad específica de la página de mapas
 * educativos, proporcionando interactividad y mejora de la experiencia de usuario.
 *
 * FUNCIONALIDADES PRINCIPALES:
 * - Modo de pantalla completa para el mapa
 * - Manejo de eventos de teclado (ESC para salir)
 * - Actualización dinámica del título según municipio
 * - Integración con el sistema de scroll suave del sidebar
 * - Compatibilidad cross-browser para Fullscreen API
 *
 * ELEMENTOS DOM REQUERIDOS:
 * - #fullscreen-btn: Botón para alternar pantalla completa
 * - #map-container: Contenedor del iframe del mapa
 * - .mapa-panel: Panel principal que contiene el mapa
 * - #map-iframe: Iframe de Google Maps
 *
 * @version 1.0
 * @requires animations_global.js (animaciones del sistema)
 * @requires sidebar.js (navegación y scroll suave)
 */

// =============================================================================
// INICIALIZACIÓN DEL CONTROLADOR DE MAPAS
// =============================================================================

/**
 * Punto de entrada principal del sistema de mapas
 *
 * Se ejecuta cuando el DOM está completamente cargado para garantizar que
 * todos los elementos estén disponibles antes de asignar event listeners.
 */
document.addEventListener('DOMContentLoaded', function () {
    // =============================================================================
    // MODO PANTALLA COMPLETA
    // =============================================================================

    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const mapaPanel = document.querySelector('.mapa-panel');
    const mapContainer = document.getElementById('map-container');

    if (fullscreenBtn && mapaPanel) {
        /**
         * Alternar modo pantalla completa
         *
         * Esta función maneja el cambio entre modo normal y pantalla completa,
         * actualizando el ícono del botón y las clases CSS correspondientes.
         */
        fullscreenBtn.addEventListener('click', function () {
            toggleFullscreen();
        });

        /**
         * Función para alternar el modo de pantalla completa
         */
        function toggleFullscreen() {
            if (!mapaPanel.classList.contains('fullscreen-active')) {
                // Activar pantalla completa
                enterFullscreen();
            } else {
                // Desactivar pantalla completa
                exitFullscreen();
            }
        }

        /**
         * Entrar en modo pantalla completa
         */
        function enterFullscreen() {
            // Agregar clase de pantalla completa
            mapaPanel.classList.add('fullscreen-active');

            // Cambiar ícono del botón
            const icon = fullscreenBtn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-expand');
                icon.classList.add('fa-compress');
            }

            // Agregar clase al body para prevenir scroll
            document.body.style.overflow = 'hidden';

            // Intentar usar la Fullscreen API si está disponible
            if (mapaPanel.requestFullscreen) {
                mapaPanel.requestFullscreen().catch(err => {
                    console.log('No se pudo activar fullscreen nativo:', err);
                });
            } else if (mapaPanel.webkitRequestFullscreen) {
                mapaPanel.webkitRequestFullscreen();
            } else if (mapaPanel.msRequestFullscreen) {
                mapaPanel.msRequestFullscreen();
            }

            // Log para debugging
            console.log('Modo pantalla completa activado');
        }

        /**
         * Salir del modo pantalla completa
         */
        function exitFullscreen() {
            // Remover clase de pantalla completa
            mapaPanel.classList.remove('fullscreen-active');

            // Cambiar ícono del botón
            const icon = fullscreenBtn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-compress');
                icon.classList.add('fa-expand');
            }

            // Restaurar scroll del body
            document.body.style.overflow = '';

            // Salir de fullscreen API si está activo
            if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }

            // Log para debugging
            console.log('Modo pantalla completa desactivado');
        }

        /**
         * Detectar cuando el usuario sale de fullscreen usando ESC o F11
         */
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('msfullscreenchange', handleFullscreenChange);

        function handleFullscreenChange() {
            if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
                // El usuario salió de fullscreen, actualizar el estado
                if (mapaPanel.classList.contains('fullscreen-active')) {
                    exitFullscreen();
                }
            }
        }

        /**
         * Permitir salir de pantalla completa con la tecla ESC
         */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mapaPanel.classList.contains('fullscreen-active')) {
                exitFullscreen();
            }
        });
    }

    // =============================================================================
    // OBTENER MUNICIPIO DESDE URL
    // =============================================================================

    /**
     * Obtener el parámetro del municipio desde la URL
     * Útil para futuras funcionalidades que dependan del municipio activo
     */
    function getMunicipioFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('municipio') || 'QUERÉTARO';
    }

    // Obtener y almacenar el municipio actual
    const municipioActual = getMunicipioFromURL();
    console.log('Municipio activo:', municipioActual);

    // =============================================================================
    // MEJORAS DE EXPERIENCIA DE USUARIO
    // =============================================================================

    /**
     * Agregar indicador de carga para el iframe
     * Muestra un spinner mientras el mapa está cargando
     */
    const mapIframe = document.getElementById('map-iframe');
    if (mapIframe && mapContainer) {
        // Crear indicador de carga
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'map-loading';
        loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i><p>Cargando mapa...</p>';
        loadingIndicator.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: var(--secondary-blue);
            z-index: 1;
        `;

        // Insertar indicador antes del iframe
        mapContainer.insertBefore(loadingIndicator, mapIframe);

        // Remover indicador cuando el iframe cargue
        mapIframe.addEventListener('load', function () {
            if (loadingIndicator && loadingIndicator.parentNode) {
                loadingIndicator.style.opacity = '0';
                loadingIndicator.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    if (loadingIndicator.parentNode) {
                        loadingIndicator.remove();
                    }
                }, 300);
            }
            console.log('Mapa cargado correctamente');
        });

        // Manejar errores de carga
        mapIframe.addEventListener('error', function () {
            if (loadingIndicator) {
                loadingIndicator.innerHTML = '<i class="fas fa-exclamation-triangle"></i><p>Error al cargar el mapa</p>';
                loadingIndicator.style.color = 'var(--danger-red)';
            }
            console.error('Error al cargar el mapa');
        });
    }

    // =============================================================================
    // ANIMACIONES DE ENTRADA PARA LAS ESTADÍSTICAS
    // =============================================================================

    /**
     * Animar los números de las estadísticas cuando se hacen visibles
     * Utiliza Intersection Observer para detectar cuando entran en viewport
     */
    const statItems = document.querySelectorAll('.stat-item');

    if (statItems.length > 0 && 'IntersectionObserver' in window) {
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        entry.target.style.transition = 'all 0.5s ease';
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);

                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        statItems.forEach(item => observer.observe(item));
    }

    // =============================================================================
    // LOGS DE INICIALIZACIÓN
    // =============================================================================

    console.log('✓ Sistema de mapas inicializado correctamente');
    console.log('✓ Funcionalidad de pantalla completa activa');
    console.log('✓ Event listeners registrados');
});

// =============================================================================
// FUNCIONES AUXILIARES GLOBALES
// =============================================================================

/**
 * Función para formatear nombres de municipios
 * Convierte texto en mayúsculas a formato título
 *
 * @param {string} municipio - Nombre del municipio en mayúsculas
 * @returns {string} Nombre formateado en formato título
 */
function formatearNombreMunicipio(municipio) {
    if (!municipio) return '';

    // Convertir a minúsculas y luego a formato título
    let formatted = municipio.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());

    // Correcciones específicas para preposiciones
    formatted = formatted.replace(/ De /g, ' de ')
                        .replace(/ Del /g, ' del ')
                        .replace(/ La /g, ' la ')
                        .replace(/ Los /g, ' los ')
                        .replace(/ Las /g, ' las ');

    return formatted;
}

/**
 * Función para detectar si el dispositivo es móvil
 * Útil para ajustar comportamientos específicos en dispositivos táctiles
 *
 * @returns {boolean} true si es dispositivo móvil, false en caso contrario
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// =============================================================================
// MANEJO DE REDIMENSIONAMIENTO DE VENTANA
// =============================================================================

/**
 * Ajustar el mapa cuando cambia el tamaño de la ventana
 * Especialmente importante para el modo responsive
 */
let resizeTimer;
window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
        console.log('Ventana redimensionada, ajustando mapa...');

        // Si está en modo pantalla completa, ajustar
        const mapaPanel = document.querySelector('.mapa-panel');
        if (mapaPanel && mapaPanel.classList.contains('fullscreen-active')) {
            console.log('Ajustando modo pantalla completa después de resize');
        }
    }, 250);
});

// =============================================================================
// EXPORTAR FUNCIONES PARA USO EXTERNO (si es necesario)
// =============================================================================

// Hacer disponibles algunas funciones globalmente si se necesitan en otros scripts
window.MapasApp = {
    formatearNombreMunicipio: formatearNombreMunicipio,
    isMobileDevice: isMobileDevice
};

console.log('✓ Módulo de mapas cargado completamente');
