/**
 * Mejoras para la captura de gráficos
 * Sistema de Dashboard Estadístico - SEDEQ
 * 
 * Este archivo contiene funciones auxiliares para mejorar la calidad
 * de captura de gráficos y resolver problemas de solapamiento.
 */

/**
 * Aplica mejoras temporales para la captura de gráficos con contenedor reducido
 * @param {HTMLElement} chartElement - Elemento del gráfico
 * @returns {Function} - Función para restaurar el estado original
 */
function aplicarMejorasCaptura(chartElement) {
    const container = chartElement.closest('.chart-container');
    const originalStyles = new Map();
    
    // Guardar estilos originales
    if (container) {
        originalStyles.set(container, {
            className: container.className,
            style: container.style.cssText
        });
        
        // Aplicar mejoras temporales optimizadas para contenedor reducido
        container.classList.add('chart-capture-mode');
        container.style.padding = '30px'; // Reducido de 40px
        container.style.margin = '15px';  // Reducido de 20px
        container.style.backgroundColor = '#ffffff';
        container.style.border = 'none';
        container.style.boxShadow = 'none';
        container.style.minHeight = '380px'; // Altura mínima para buena captura
    }
    
    // Mejorar elementos de texto del gráfico
    const textElements = chartElement.querySelectorAll('text');
    textElements.forEach(text => {
        originalStyles.set(text, text.style.cssText);
        text.style.fontSize = '12px';
        text.style.fontFamily = 'Arial, sans-serif';
        text.style.fill = '#333';
    });
    
    // Función para restaurar estilos
    return function restaurarEstilos() {
        originalStyles.forEach((originalStyle, element) => {
            if (element === container) {
                element.className = originalStyle.className;
                element.style.cssText = originalStyle.style;
            } else {
                element.style.cssText = originalStyle;
            }
        });
    };
}

/**
 * Configuración optimizada para html2canvas con texto horizontal
 * @param {HTMLElement} chartElement - Elemento del gráfico
 * @returns {Object} - Opciones optimizadas
 */
function getOpcionesOptimizadas(chartElement) {
    const rect = chartElement.getBoundingClientRect();
    
    return {
        backgroundColor: '#ffffff',
        scale: 2.5, // Mejor calidad para contenedores más pequeños
        logging: false,
        useCORS: true,
        allowTaint: true,
        height: rect.height + 140, // Más espacio para texto horizontal
        width: rect.width + 120,
        x: -60, // Mayor offset para capturar completamente
        y: -70, // Mayor offset vertical para texto horizontal
        scrollX: 0,
        scrollY: 0,
        windowWidth: rect.width + 240,
        windowHeight: rect.height + 280, // Más altura para el texto
        removeContainer: false,
        foreignObjectRendering: true,
        ignoreElements: function(element) {
            return element.tagName === 'BUTTON' || 
                   element.classList.contains('export-button') ||
                   element.classList.contains('chart-controls') ||
                   element.classList.contains('export-buttons');
        },
        onclone: function(clonedDoc) {
            // Mejorar elementos en el documento clonado
            const clonedChart = clonedDoc.getElementById(chartElement.id);
            if (clonedChart) {
                clonedChart.style.padding = '25px';
                clonedChart.style.margin = '15px';
                clonedChart.style.minHeight = '350px'; // Asegurar altura mínima
                clonedChart.style.paddingBottom = '40px'; // Más espacio para texto horizontal
                
                // Mejorar textos en el documento clonado
                const texts = clonedChart.querySelectorAll('text');
                texts.forEach(text => {
                    text.style.fontSize = '11px';
                    text.style.fontFamily = 'Arial, sans-serif';
                    text.style.fill = '#333';
                });
            }
        }
    };
}

/**
 * Fuerza un redibujado del gráfico de Google Charts
 * @param {google.visualization.ColumnChart} chart - Instancia del gráfico
 * @param {google.visualization.DataTable} dataTable - Datos del gráfico
 * @param {Object} options - Opciones del gráfico
 */
function forzarRedibujado(chart, dataTable, options) {
    if (chart && dataTable && options) {
        // Pequeña pausa para asegurar el redibujado
        setTimeout(() => {
            chart.draw(dataTable, options);
        }, 100);
    }
}

// Exportar funciones si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        aplicarMejorasCaptura,
        getOpcionesOptimizadas,
        forzarRedibujado
    };
}
