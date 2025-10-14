/**
 * =============================================================================
 * JAVASCRIPT PARA MÓDULO DE MATRÍCULA POR NIVEL EDUCATIVO
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Archivo JavaScript que maneja la funcionalidad interactiva de la página
 * de análisis de matrícula estudiantil consolidada por nivel educativo.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Renderizado de gráficos con Google Charts
 * - Exportación de datos a Excel/CSV
 * - Animaciones y transiciones suaves
 * - Manejo de eventos interactivos
 * - Responsive design y adaptación móvil
 * 
 * @author Sistema SEDEQ
 * @version 2.0
 * @requires Google Charts API
 * @requires Export Manager (export-manager-annotations.js)
 */

// =============================================================================
// VARIABLES GLOBALES Y CONFIGURACIÓN
// =============================================================================

// Configuración de colores del tema - usando variables CSS del sistema
const THEME_COLORS = {
    primary: '#242B57',
    secondary: '#4996C4',
    success: '#28a745',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#17a2b8',
    publico: '#28a745',
    privado: '#FF3E8D',
    light: '#f8f9fa',
    dark: '#343a40'
};

// Configuración de gráficos
const CHART_CONFIG = {
    backgroundColor: 'transparent',
    fontName: 'Hanken Grotesk, Arial, sans-serif',
    fontSize: 12,
    titleTextStyle: {
        color: THEME_COLORS.primary,
        fontSize: 16,
        bold: true
    },
    legendTextStyle: {
        color: THEME_COLORS.dark,
        fontSize: 12
    },
    hAxis: {
        textStyle: {
            color: THEME_COLORS.dark,
            fontSize: 11
        },
        titleTextStyle: {
            color: THEME_COLORS.primary,
            fontSize: 13,
            bold: true
        }
    },
    vAxis: {
        textStyle: {
            color: THEME_COLORS.dark,
            fontSize: 11
        },
        titleTextStyle: {
            color: THEME_COLORS.primary,
            fontSize: 13,
            bold: true
        }
    }
};

// Variables para almacenar instancias de gráficos
let chartComparativo = null;
let chartDistribucion = null;

// =============================================================================
// INICIALIZACIÓN DE LA APLICACIÓN
// =============================================================================

/**
 * Inicializa la aplicación cuando el DOM está listo
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Iniciando módulo de matrícula por nivel educativo...');
    
    // Cargar librerías de Google Charts
    if (typeof google !== 'undefined' && google.charts) {
        google.charts.load('current', {
            'packages': ['corechart', 'bar'],
            'language': 'es'
        });
        google.charts.setOnLoadCallback(inicializarGraficos);
    } else {
        console.error('❌ Google Charts no está disponible');
        mostrarErrorCarga();
    }
    
    // Inicializar componentes
    inicializarEventos();
    inicializarAnimaciones();
    inicializarExportacion();
    
    // Inicializar filtro de búsqueda - SIMPLE Y DIRECTO
    inicializarFiltroBusqueda();
    
    console.log('✅ Módulo de matrícula inicializado correctamente');
});

// =============================================================================
// FUNCIONES DE INICIALIZACIÓN
// =============================================================================

/**
 * Inicializa todos los gráficos de la página
 */
function inicializarGraficos() {
    try {
        console.log('📊 Inicializando gráficos...');
        
        // Crear gráfico comparativo
        if (typeof datosGrafico !== 'undefined') {
            crearGraficoComparativo();
        }
        
        // Crear gráfico de distribución
        if (typeof datosDistribucion !== 'undefined') {
            crearGraficoDistribucion();
        }
        
        // Configurar redimensionamiento automático
        window.addEventListener('resize', redimensionarGraficos);
        
        console.log('✅ Gráficos inicializados correctamente');
    } catch (error) {
        console.error('❌ Error al inicializar gráficos:', error);
        mostrarErrorCarga();
    }
}

/**
 * Inicializa los eventos de la interfaz
 */
function inicializarEventos() {
    console.log('🔧 Inicializando eventos...');
    
    // Botón de exportación
    const botonExportar = document.getElementById('export-btn');
    if (botonExportar) {
        botonExportar.addEventListener('click', handleExportClick);
    }
    
    // Hover effects en elementos
    const elementos = document.querySelectorAll('.stat-box, .chart-container, .analysis-card');
    elementos.forEach(elemento => {
        elemento.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        
        elemento.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    console.log('✅ Eventos inicializados correctamente');
}

/**
 * Inicializa las animaciones de entrada
 */
function inicializarAnimaciones() {
    console.log('🎨 Inicializando animaciones...');
    
    // Las animaciones están definidas en global.css y se aplican automáticamente
    // con las clases animate-fade y delay-*
    
    console.log('✅ Animaciones inicializadas correctamente');
}

/**
 * Inicializa la funcionalidad de exportación
 */
function inicializarExportacion() {
    console.log('📤 Inicializando exportación...');
    
    // Verificar si el export manager está disponible
    if (typeof ExportManager !== 'undefined') {
        console.log('✅ Export Manager disponible');
    } else {
        console.warn('⚠️ Export Manager no disponible - usando funciones básicas');
    }
}

// =============================================================================
// FUNCIONES DE GRÁFICOS
// =============================================================================

/**
 * Crea el gráfico comparativo por nivel educativo
 */
function crearGraficoComparativo() {
    try {
        console.log('📊 Creando gráfico comparativo...');
        
        const data = google.visualization.arrayToDataTable(datosGrafico);
        
        const options = {
            ...CHART_CONFIG,
            title: 'Matrícula por Nivel Educativo',
            titlePosition: 'out',
            hAxis: {
                ...CHART_CONFIG.hAxis,
                title: 'Número de Estudiantes',
                format: '#,###'
            },
            vAxis: {
                ...CHART_CONFIG.vAxis,
                title: 'Nivel Educativo'
            },
            colors: [THEME_COLORS.publico, THEME_COLORS.privado],
            chartArea: {
                left: 150,
                top: 60,
                width: '70%',
                height: '70%'
            },
            legend: {
                position: 'top',
                alignment: 'center',
                textStyle: CHART_CONFIG.legendTextStyle
            },
            animation: {
                duration: 1000,
                easing: 'out',
                startup: true
            },
            isStacked: false
        };
        
        chartComparativo = new google.visualization.ColumnChart(document.getElementById('chart-comparativo'));
        chartComparativo.draw(data, options);
        
        console.log('✅ Gráfico comparativo creado correctamente');
    } catch (error) {
        console.error('❌ Error al crear gráfico comparativo:', error);
        mostrarErrorGrafico('chart-comparativo');
    }
}

/**
 * Crea el gráfico de distribución por sectores
 */
function crearGraficoDistribucion() {
    try {
        console.log('📊 Creando gráfico de distribución...');
        
        const data = google.visualization.arrayToDataTable(datosDistribucion);
        
        const options = {
            ...CHART_CONFIG,
            title: 'Distribución por Sectores',
            titlePosition: 'out',
            pieHole: 0.4,
            colors: [THEME_COLORS.publico, THEME_COLORS.privado],
            chartArea: {
                left: 20,
                top: 60,
                width: '90%',
                height: '70%'
            },
            legend: {
                position: 'bottom',
                alignment: 'center',
                textStyle: CHART_CONFIG.legendTextStyle
            },
            pieSliceText: 'percentage',
            pieSliceTextStyle: {
                color: 'white',
                fontSize: 14,
                bold: true
            },
            animation: {
                duration: 1000,
                easing: 'out',
                startup: true
            }
        };
        
        chartDistribucion = new google.visualization.PieChart(document.getElementById('chart-distribucion'));
        chartDistribucion.draw(data, options);
        
        console.log('✅ Gráfico de distribución creado correctamente');
    } catch (error) {
        console.error('❌ Error al crear gráfico de distribución:', error);
        mostrarErrorGrafico('chart-distribucion');
    }
}

/**
 * Redimensiona los gráficos cuando cambia el tamaño de la ventana
 */
function redimensionarGraficos() {
    clearTimeout(window.resizeTimeout);
    window.resizeTimeout = setTimeout(() => {
        try {
            if (chartComparativo) {
                crearGraficoComparativo();
            }
            
            if (chartDistribucion) {
                crearGraficoDistribucion();
            }
            
            console.log('📊 Gráficos redimensionados correctamente');
        } catch (error) {
            console.error('❌ Error al redimensionar gráficos:', error);
        }
    }, 250);
}

// =============================================================================
// FUNCIONES DE EXPORTACIÓN
// =============================================================================

/**
 * Maneja el clic en el botón de exportación
 */
function handleExportClick(event) {
    event.preventDefault();
    
    console.log('📤 Iniciando exportación...');
    
    // Mostrar opciones de exportación
    mostrarOpcionesExportacion(event.target);
}

/**
 * Muestra las opciones de exportación
 */
function mostrarOpcionesExportacion(boton) {
    // Crear menú de opciones
    const menu = document.createElement('div');
    menu.className = 'export-menu';
    menu.innerHTML = `
        <div class="export-option" data-type="excel">
            <i class="fas fa-file-excel"></i> Exportar a Excel
        </div>
        <div class="export-option" data-type="csv">
            <i class="fas fa-file-csv"></i> Exportar a CSV
        </div>
        <div class="export-option" data-type="pdf">
            <i class="fas fa-file-pdf"></i> Exportar a PDF
        </div>
    `;
    
    // Posicionar el menú
    const rect = boton.getBoundingClientRect();
    menu.style.position = 'absolute';
    menu.style.top = (rect.bottom + 5) + 'px';
    menu.style.left = rect.left + 'px';
    menu.style.zIndex = '1000';
    menu.style.background = 'white';
    menu.style.border = '1px solid #ddd';
    menu.style.borderRadius = '4px';
    menu.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    
    document.body.appendChild(menu);
    
    // Agregar eventos
    menu.querySelectorAll('.export-option').forEach(option => {
        option.addEventListener('click', function() {
            const tipo = this.dataset.type;
            exportarDatos(tipo);
            document.body.removeChild(menu);
        });
    });
    
    // Cerrar menú al hacer clic fuera
    setTimeout(() => {
        document.addEventListener('click', function closeMenu(e) {
            if (!menu.contains(e.target) && e.target !== boton) {
                document.body.removeChild(menu);
                document.removeEventListener('click', closeMenu);
            }
        });
    }, 100);
}

/**
 * Exporta los datos según el tipo especificado
 */
function exportarDatos(tipo) {
    console.log(`📤 Exportando datos a ${tipo}...`);
    
    switch (tipo) {
        case 'excel':
            exportarExcel();
            break;
        case 'csv':
            exportarCSV();
            break;
        case 'pdf':
            exportarPDF();
            break;
        default:
            console.warn('⚠️ Tipo de exportación no reconocido:', tipo);
    }
}

/**
 * Exporta los datos a Excel
 */
function exportarExcel() {
    try {
        const datos = prepararDatosExportacion();
        
        if (typeof XLSX !== 'undefined') {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(datos);
            XLSX.utils.book_append_sheet(wb, ws, 'Matrícula por Nivel');
            XLSX.writeFile(wb, 'matricula-por-nivel-educativo.xlsx');
        } else {
            exportarCSV();
        }
        
        console.log('✅ Exportación a Excel completada');
    } catch (error) {
        console.error('❌ Error al exportar a Excel:', error);
        mostrarError('Error al exportar a Excel');
    }
}

/**
 * Exporta los datos a CSV
 */
function exportarCSV() {
    try {
        const datos = prepararDatosExportacion();
        const csv = convertirACSV(datos);
        descargarArchivo(csv, 'matricula-por-nivel-educativo.csv', 'text/csv');
        
        console.log('✅ Exportación a CSV completada');
    } catch (error) {
        console.error('❌ Error al exportar a CSV:', error);
        mostrarError('Error al exportar a CSV');
    }
}

/**
 * Exporta los datos a PDF
 */
function exportarPDF() {
    try {
        window.print();
        console.log('✅ Exportación a PDF iniciada');
    } catch (error) {
        console.error('❌ Error al exportar a PDF:', error);
        mostrarError('Error al exportar a PDF');
    }
}

/**
 * Prepara los datos para exportación
 */
function prepararDatosExportacion() {
    const datos = [
        ['Nivel Educativo', 'Sector Público', 'Sector Privado', 'Total', '% Público', '% Privado']
    ];
    
    // Obtener datos de la tabla
    const tabla = document.getElementById('tabla-matricula');
    if (tabla) {
        const filas = tabla.querySelectorAll('tbody tr');
        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length >= 6) {
                datos.push([
                    celdas[0].textContent.trim(),
                    celdas[1].textContent.trim(),
                    celdas[2].textContent.trim(),
                    celdas[3].textContent.trim(),
                    celdas[4].textContent.trim(),
                    celdas[5].textContent.trim()
                ]);
            }
        });
    }
    
    return datos;
}

/**
 * Convierte datos a formato CSV
 */
function convertirACSV(datos) {
    return datos.map(fila => {
        return fila.map(celda => {
            const valor = String(celda).replace(/"/g, '""');
            return valor.includes(',') ? `"${valor}"` : valor;
        }).join(',');
    }).join('\n');
}

/**
 * Descarga un archivo
 */
function descargarArchivo(contenido, nombreArchivo, tipoMIME) {
    const blob = new Blob([contenido], { type: tipoMIME });
    const url = URL.createObjectURL(blob);
    
    const enlace = document.createElement('a');
    enlace.href = url;
    enlace.download = nombreArchivo;
    enlace.style.display = 'none';
    
    document.body.appendChild(enlace);
    enlace.click();
    document.body.removeChild(enlace);
    
    URL.revokeObjectURL(url);
}

// =============================================================================
// FUNCIONES DE UTILIDAD
// =============================================================================

/**
 * Muestra un error en el área de gráficos
 */
function mostrarErrorGrafico(idElemento) {
    const elemento = document.getElementById(idElemento);
    if (elemento) {
        elemento.innerHTML = `
            <div style="display: flex; justify-content: center; align-items: center; min-height: 200px; color: ${THEME_COLORS.warning};">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-right: 15px;"></i>
                <div>
                    <strong>Error al cargar el gráfico</strong><br>
                    <small>Por favor, recarga la página</small>
                </div>
            </div>
        `;
    }
}

/**
 * Muestra un error de carga general
 */
function mostrarErrorCarga() {
    const contenedoresGraficos = document.querySelectorAll('[id^="chart-"]');
    contenedoresGraficos.forEach(contenedor => {
        mostrarErrorGrafico(contenedor.id);
    });
}

/**
 * Muestra un mensaje de error general
 */
function mostrarError(mensaje) {
    console.error('Error:', mensaje);
    
    // Aquí podrías implementar una notificación toast o modal
    alert(mensaje);
}

// =============================================================================
// FUNCIONES DE DEBUGGING (SOLO DESARROLLO)
// =============================================================================

/**
 * Función para debugging - muestra información del estado actual
 */
function debug() {
    console.log('🔍 Estado actual del módulo de matrícula:');
    console.log('- Gráfico comparativo:', chartComparativo);
    console.log('- Gráfico distribución:', chartDistribucion);
    console.log('- Datos gráfico:', typeof datosGrafico !== 'undefined' ? datosGrafico : 'No disponible');
    console.log('- Datos distribución:', typeof datosDistribucion !== 'undefined' ? datosDistribucion : 'No disponible');
    console.log('- Totales:', typeof totales !== 'undefined' ? totales : 'No disponible');
    console.log('- Google Charts:', typeof google !== 'undefined' ? 'Cargado' : 'No cargado');
    console.log('- XLSX:', typeof XLSX !== 'undefined' ? 'Disponible' : 'No disponible');
}

// Hacer la función debug disponible globalmente en modo desarrollo
if (typeof window !== 'undefined') {
    window.debugAlumnos = debug;
}

// =============================================================================
// ESTILOS DINÁMICOS PARA MENÚ DE EXPORTACIÓN
// =============================================================================

// Agregar estilos CSS para el menú de exportación
const style = document.createElement('style');
style.textContent = `
    .export-menu {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
    }
    
    .export-option {
        padding: 12px 16px;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .export-option:hover {
        background-color: #f8f9fa;
    }
    
    .export-option:first-child {
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
    
    .export-option:last-child {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }
    
    .export-option i {
        width: 16px;
        text-align: center;
    }
`;
document.head.appendChild(style);

console.log('📚 Módulo de JavaScript para matrícula cargado correctamente');

/**
 * Inicializa los eventos de la interfaz
 */
function inicializarEventos() {
    console.log('🔧 Inicializando eventos...');
    
    // Botones de exportación
    const botonesExportar = document.querySelectorAll('.export-btn');
    botonesExportar.forEach(boton => {
        boton.addEventListener('click', handleExportClick);
    });
    
    // Hover effects en tarjetas
    const tarjetas = document.querySelectorAll('.summary-card, .chart-card');
    tarjetas.forEach(tarjeta => {
        tarjeta.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        tarjeta.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Efecto de click en botones
    const botones = document.querySelectorAll('.export-btn');
    botones.forEach(boton => {
        boton.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
    
    console.log('✅ Eventos inicializados correctamente');
}

/**
 * Inicializa las animaciones de entrada
 */
function inicializarAnimaciones() {
    console.log('🎨 Inicializando animaciones...');
    
    // Animación de entrada para tarjetas
    const elementos = document.querySelectorAll('.summary-card, .chart-card, .data-table-section, .analysis-section');
    
    // Usar Intersection Observer para animaciones suaves
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    elementos.forEach(elemento => {
        observer.observe(elemento);
    });
    
    console.log('✅ Animaciones inicializadas correctamente');
}

/**
 * Inicializa la funcionalidad de exportación
 */
function inicializarExportacion() {
    console.log('📤 Inicializando exportación...');
    
    // Verificar si el export manager está disponible
    if (typeof ExportManager !== 'undefined') {
        console.log('✅ Export Manager disponible');
    } else {
        console.warn('⚠️ Export Manager no disponible - usando funciones básicas');
    }
}

// =============================================================================
// FUNCIONES DE GRÁFICOS
// =============================================================================

/**
 * Crea el gráfico de barras horizontales
 */
function crearGraficoBarras() {
    try {
        console.log('📊 Creando gráfico de barras...');
        
        const data = google.visualization.arrayToDataTable(datosGraficoBarras);
        
        const options = {
            ...CHART_CONFIG,
            title: 'Matrícula por Nivel Educativo',
            titlePosition: 'out',
            orientation: 'horizontal',
            bars: 'horizontal',
            hAxis: {
                ...CHART_CONFIG.hAxis,
                title: 'Número de Estudiantes',
                format: '#,###'
            },
            vAxis: {
                ...CHART_CONFIG.vAxis,
                title: 'Nivel Educativo'
            },
            colors: [THEME_COLORS.publico, THEME_COLORS.privado],
            chartArea: {
                left: 150,
                top: 60,
                width: '70%',
                height: '70%'
            },
            legend: {
                position: 'top',
                alignment: 'center',
                textStyle: CHART_CONFIG.legendTextStyle
            },
            animation: {
                duration: 1000,
                easing: 'out',
                startup: true
            }
        };
        
        chartBarras = new google.visualization.BarChart(document.getElementById('grafico-barras'));
        chartBarras.draw(data, options);
        
        console.log('✅ Gráfico de barras creado correctamente');
    } catch (error) {
        console.error('❌ Error al crear gráfico de barras:', error);
        mostrarErrorGrafico('grafico-barras');
    }
}

/**
 * Crea el gráfico de pastel
 */
function crearGraficoPastel() {
    try {
        console.log('📊 Creando gráfico de pastel...');
        
        const data = google.visualization.arrayToDataTable(datosGraficoPastel);
        
        const options = {
            ...CHART_CONFIG,
            title: 'Distribución por Sector',
            titlePosition: 'out',
            pieHole: 0.4,
            colors: [THEME_COLORS.publico, THEME_COLORS.privado],
            chartArea: {
                left: 20,
                top: 60,
                width: '90%',
                height: '70%'
            },
            legend: {
                position: 'bottom',
                alignment: 'center',
                textStyle: CHART_CONFIG.legendTextStyle
            },
            pieSliceText: 'percentage',
            pieSliceTextStyle: {
                color: 'white',
                fontSize: 14,
                bold: true
            },
            animation: {
                duration: 1000,
                easing: 'out',
                startup: true
            }
        };
        
        chartPastel = new google.visualization.PieChart(document.getElementById('grafico-pastel'));
        chartPastel.draw(data, options);
        
        console.log('✅ Gráfico de pastel creado correctamente');
    } catch (error) {
        console.error('❌ Error al crear gráfico de pastel:', error);
        mostrarErrorGrafico('grafico-pastel');
    }
}

/**
 * Redimensiona los gráficos cuando cambia el tamaño de la ventana
 */
function redimensionarGraficos() {
    // Debounce para evitar múltiples redimensionamientos
    clearTimeout(window.resizeTimeout);
    window.resizeTimeout = setTimeout(() => {
        try {
            if (chartBarras) {
                chartBarras.draw(google.visualization.arrayToDataTable(datosGraficoBarras), 
                    obtenerOpcionesGrafico('barras'));
            }
            
            if (chartPastel) {
                chartPastel.draw(google.visualization.arrayToDataTable(datosGraficoPastel), 
                    obtenerOpcionesGrafico('pastel'));
            }
            
            console.log('📊 Gráficos redimensionados correctamente');
        } catch (error) {
            console.error('❌ Error al redimensionar gráficos:', error);
        }
    }, 250);
}

/**
 * Obtiene las opciones de configuración para un tipo de gráfico
 */
function obtenerOpcionesGrafico(tipo) {
    const esMovil = window.innerWidth < 768;
    
    if (tipo === 'barras') {
        return {
            ...CHART_CONFIG,
            title: 'Matrícula por Nivel Educativo',
            orientation: 'horizontal',
            bars: 'horizontal',
            hAxis: {
                ...CHART_CONFIG.hAxis,
                title: esMovil ? '' : 'Número de Estudiantes',
                format: '#,###'
            },
            vAxis: {
                ...CHART_CONFIG.vAxis,
                title: esMovil ? '' : 'Nivel Educativo'
            },
            colors: [THEME_COLORS.publico, THEME_COLORS.privado],
            chartArea: {
                left: esMovil ? 100 : 150,
                top: esMovil ? 40 : 60,
                width: esMovil ? '80%' : '70%',
                height: esMovil ? '80%' : '70%'
            },
            legend: {
                position: esMovil ? 'bottom' : 'top',
                alignment: 'center',
                textStyle: CHART_CONFIG.legendTextStyle
            }
        };
    } else if (tipo === 'pastel') {
        return {
            ...CHART_CONFIG,
            title: 'Distribución por Sector',
            pieHole: 0.4,
            colors: [THEME_COLORS.publico, THEME_COLORS.privado],
            chartArea: {
                left: 20,
                top: esMovil ? 40 : 60,
                width: '90%',
                height: esMovil ? '70%' : '70%'
            },
            legend: {
                position: 'bottom',
                alignment: 'center',
                textStyle: CHART_CONFIG.legendTextStyle
            },
            pieSliceText: esMovil ? 'none' : 'percentage',
            pieSliceTextStyle: {
                color: 'white',
                fontSize: esMovil ? 12 : 14,
                bold: true
            }
        };
    }
}

// =============================================================================
// FUNCIONES DE EXPORTACIÓN
// =============================================================================

/**
 * Maneja el clic en botones de exportación
 */
function handleExportClick(event) {
    event.preventDefault();
    
    const boton = event.currentTarget;
    const tipo = boton.dataset.tipo;
    
    console.log(`📤 Exportando datos a ${tipo}...`);
    
    // Mostrar indicador de carga
    mostrarIndicadorCarga(boton);
    
    // Llamar a la función de exportación apropiada
    switch (tipo) {
        case 'excel':
            exportarExcel();
            break;
        case 'csv':
            exportarCSV();
            break;
        case 'pdf':
            exportarPDF();
            break;
        default:
            console.warn('⚠️ Tipo de exportación no reconocido:', tipo);
    }
    
    // Ocultar indicador de carga después de un delay
    setTimeout(() => {
        ocultarIndicadorCarga(boton);
    }, 2000);
}

/**
 * Exporta los datos a Excel
 */
function exportarExcel() {
    try {
        if (typeof ExportManager !== 'undefined') {
            // Usar el export manager si está disponible
            const datos = prepararDatosExportacion();
            ExportManager.exportToExcel(datos, 'matricula-por-nivel-educativo.xlsx');
        } else {
            // Fallback básico
            exportarCSV();
        }
        
        console.log('✅ Exportación a Excel completada');
    } catch (error) {
        console.error('❌ Error al exportar a Excel:', error);
        mostrarError('Error al exportar a Excel');
    }
}

/**
 * Exporta los datos a CSV
 */
function exportarCSV() {
    try {
        const datos = prepararDatosExportacion();
        const csv = convertirACSV(datos);
        descargarArchivo(csv, 'matricula-por-nivel-educativo.csv', 'text/csv');
        
        console.log('✅ Exportación a CSV completada');
    } catch (error) {
        console.error('❌ Error al exportar a CSV:', error);
        mostrarError('Error al exportar a CSV');
    }
}

/**
 * Exporta los datos a PDF
 */
function exportarPDF() {
    try {
        // Implementación básica de impresión
        window.print();
        
        console.log('✅ Exportación a PDF iniciada');
    } catch (error) {
        console.error('❌ Error al exportar a PDF:', error);
        mostrarError('Error al exportar a PDF');
    }
}

/**
 * Prepara los datos para exportación
 */
function prepararDatosExportacion() {
    const datos = [
        ['Nivel Educativo', 'Sector Público', 'Sector Privado', 'Total', 'Porcentaje']
    ];
    
    // Obtener datos de la tabla
    const tabla = document.querySelector('.data-table tbody');
    if (tabla) {
        const filas = tabla.querySelectorAll('tr');
        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length >= 5) {
                datos.push([
                    celdas[0].textContent.trim(),
                    celdas[1].textContent.trim(),
                    celdas[2].textContent.trim(),
                    celdas[3].textContent.trim(),
                    celdas[4].textContent.trim()
                ]);
            }
        });
    }
    
    return datos;
}

/**
 * Convierte datos a formato CSV
 */
function convertirACSV(datos) {
    return datos.map(fila => {
        return fila.map(celda => {
            // Escapar comillas y agregar comillas si contiene comas
            const valor = String(celda).replace(/"/g, '""');
            return valor.includes(',') ? `"${valor}"` : valor;
        }).join(',');
    }).join('\n');
}

/**
 * Descarga un archivo
 */
function descargarArchivo(contenido, nombreArchivo, tipoMIME) {
    const blob = new Blob([contenido], { type: tipoMIME });
    const url = URL.createObjectURL(blob);
    
    const enlace = document.createElement('a');
    enlace.href = url;
    enlace.download = nombreArchivo;
    enlace.style.display = 'none';
    
    document.body.appendChild(enlace);
    enlace.click();
    document.body.removeChild(enlace);
    
    URL.revokeObjectURL(url);
}

// =============================================================================
// FUNCIONES DE UTILIDAD
// =============================================================================

/**
 * Muestra un indicador de carga en un botón
 */
function mostrarIndicadorCarga(boton) {
    const textoOriginal = boton.innerHTML;
    boton.dataset.textoOriginal = textoOriginal;
    boton.innerHTML = '<div class="loading-spinner"></div> Procesando...';
    boton.disabled = true;
}

/**
 * Oculta el indicador de carga de un botón
 */
function ocultarIndicadorCarga(boton) {
    boton.innerHTML = boton.dataset.textoOriginal;
    boton.disabled = false;
}

/**
 * Muestra un error en el área de gráficos
 */
function mostrarErrorGrafico(idElemento) {
    const elemento = document.getElementById(idElemento);
    if (elemento) {
        elemento.innerHTML = `
            <div class="loading-indicator">
                <i class="fas fa-exclamation-triangle" style="color: ${THEME_COLORS.warning}; font-size: 2rem;"></i>
                <div style="margin-left: 15px;">
                    <strong>Error al cargar el gráfico</strong><br>
                    <small>Por favor, recarga la página</small>
                </div>
            </div>
        `;
    }
}

/**
 * Muestra un error de carga general
 */
function mostrarErrorCarga() {
    const contenedoresGraficos = document.querySelectorAll('.chart-container');
    contenedoresGraficos.forEach(contenedor => {
        contenedor.innerHTML = `
            <div class="loading-indicator">
                <i class="fas fa-exclamation-triangle" style="color: ${THEME_COLORS.warning}; font-size: 2rem;"></i>
                <div style="margin-left: 15px;">
                    <strong>Error al cargar los gráficos</strong><br>
                    <small>Verifique la conexión a internet y recargue la página</small>
                </div>
            </div>
        `;
    });
}

/**
 * Muestra un mensaje de error general
 */
function mostrarError(mensaje) {
    // Crear notificación temporal
    const notificacion = document.createElement('div');
    notificacion.className = 'alert alert-danger';
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 15px;
        background-color: ${THEME_COLORS.danger};
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        animation: fadeIn 0.3s ease;
    `;
    notificacion.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <strong>Error:</strong> ${mensaje}
    `;
    
    document.body.appendChild(notificacion);
    
    // Eliminar después de 5 segundos
    setTimeout(() => {
        notificacion.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notificacion);
        }, 300);
    }, 5000);
}

/**
 * Formatea números con separadores de miles
 */
function formatearNumero(numero) {
    return Number(numero).toLocaleString('es-MX');
}

/**
 * Formatea porcentajes
 */
function formatearPorcentaje(numero) {
    return Number(numero).toFixed(1) + '%';
}

// =============================================================================
// FUNCIONES DE DEBUGGING (SOLO DESARROLLO)
// =============================================================================

/**
 * Función para debugging - muestra información del estado actual
 */
function debug() {
    console.log('🔍 Estado actual del módulo de matrícula:');
    console.log('- Gráfico de barras:', chartBarras);
    console.log('- Gráfico de pastel:', chartPastel);
    console.log('- Datos de barras:', typeof datosGraficoBarras !== 'undefined' ? datosGraficoBarras : 'No disponible');
    console.log('- Datos de pastel:', typeof datosGraficoPastel !== 'undefined' ? datosGraficoPastel : 'No disponible');
    console.log('- Google Charts:', typeof google !== 'undefined' ? 'Cargado' : 'No cargado');
    console.log('- Export Manager:', typeof ExportManager !== 'undefined' ? 'Disponible' : 'No disponible');
}

// Hacer la función debug disponible globalmente en modo desarrollo
if (typeof window !== 'undefined') {
    window.debugAlumnos = debug;
}

// =============================================================================
// FILTRO DE BÚSQUEDA PARA TABLA DE SUBNIVELES
// =============================================================================

// Variables para guardar los valores originales de la tabla
let valoresOriginalesTabla = null;

/**
 * Inicializa el filtro de búsqueda para la tabla de subniveles
 */
function inicializarFiltroBusqueda() {
    console.log('🔍 [ALUMNOS] Inicializando filtro de búsqueda...');
    
    const searchInput = document.getElementById('searchAlumnos');
    const tableRows = document.querySelectorAll('#tabla-detallada-alumnos .data-table tbody tr');
    const tfoot = document.querySelector('#tabla-detallada-alumnos .data-table tfoot');
    
    console.log('[DEBUG] Elementos encontrados:', {
        searchInput: searchInput ? 'SÍ' : 'NO',
        tableRows: tableRows.length + ' filas',
        tfoot: tfoot ? 'SÍ' : 'NO'
    });
    
    if (!searchInput) {
        console.error('❌ [ALUMNOS] No se encontró el input #searchAlumnos');
        return false;
    }
    
    if (!tableRows.length) {
        console.error('❌ [ALUMNOS] No se encontraron filas en la tabla');
        return false;
    }
    
    if (!tfoot) {
        console.error('❌ [ALUMNOS] No se encontró el tfoot de la tabla');
        return false;
    }
    
    // Guardar valores originales
    guardarValoresOriginales(tfoot);
    console.log('💾 [ALUMNOS] Valores originales guardados');
    
    // Agregar evento de input
    searchInput.addEventListener('input', function(e) {
        const filterValue = e.target.value.toLowerCase().trim();
        console.log('🔎 [ALUMNOS] Filtrando por:', filterValue);
        
        let totalAlumnos = 0;
        let totalHombres = 0;
        let totalMujeres = 0;
        let filasVisibles = 0;
        
        tableRows.forEach(row => {
            const nivel = row.cells[0].textContent.toLowerCase();
            const subnivel = row.cells[1].textContent.toLowerCase();
            
            if (filterValue === '' || nivel.includes(filterValue) || subnivel.includes(filterValue)) {
                row.style.display = '';
                filasVisibles++;
                
                // Acumular totales
                const alumnos = parseInt(row.cells[2].textContent.replace(/,/g, '')) || 0;
                const hombres = parseInt(row.cells[4].textContent.replace(/,/g, '')) || 0;
                const mujeres = parseInt(row.cells[6].textContent.replace(/,/g, '')) || 0;
                
                totalAlumnos += alumnos;
                totalHombres += hombres;
                totalMujeres += mujeres;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Actualizar o restaurar totales
        if (filterValue !== '') {
            actualizarTotalesTabla(tfoot, totalAlumnos, totalHombres, totalMujeres, filasVisibles);
        } else {
            restaurarValoresOriginales(tfoot);
        }
        
        console.log('✅ [ALUMNOS] Filtrado completo:', filasVisibles, 'filas visibles');
    });
    
    console.log('✅ [ALUMNOS] Filtro inicializado correctamente');
    return true;
}

/**
 * Guarda los valores originales del pie de tabla
 */
function guardarValoresOriginales(tfoot) {
    const totalRow = tfoot.querySelector('.total-row');
    
    if (!totalRow || valoresOriginalesTabla) {
        return; // Ya guardados
    }
    
    // La fila total tiene colspan="2" en cells[0], así que los índices son:
    // cells[0] = TOTAL GENERAL (colspan=2)
    // cells[1] = Total Alumnos
    // cells[2] = % Total
    // cells[3] = Hombres
    // cells[4] = % Hombres
    // cells[5] = Mujeres
    // cells[6] = % Mujeres
    
    valoresOriginalesTabla = {
        titulo: totalRow.cells[0].textContent,
        totalAlumnos: totalRow.cells[1].textContent,
        porcentajeTotal: totalRow.cells[2].textContent,
        totalHombres: totalRow.cells[3].textContent,
        porcentajeHombres: totalRow.cells[4].textContent,
        totalMujeres: totalRow.cells[5].textContent,
        porcentajeMujeres: totalRow.cells[6].textContent
    };
    
    console.log('💾 [ALUMNOS] Valores originales guardados:', valoresOriginalesTabla);
}

/**
 * Restaura los valores originales del pie de tabla
 */
function restaurarValoresOriginales(tfoot) {
    const totalRow = tfoot.querySelector('.total-row');
    
    if (!totalRow || !valoresOriginalesTabla) {
        return;
    }
    
    totalRow.cells[0].textContent = valoresOriginalesTabla.titulo;
    totalRow.cells[1].textContent = valoresOriginalesTabla.totalAlumnos;
    totalRow.cells[2].textContent = valoresOriginalesTabla.porcentajeTotal;
    totalRow.cells[3].textContent = valoresOriginalesTabla.totalHombres;
    totalRow.cells[4].textContent = valoresOriginalesTabla.porcentajeHombres;
    totalRow.cells[5].textContent = valoresOriginalesTabla.totalMujeres;
    totalRow.cells[6].textContent = valoresOriginalesTabla.porcentajeMujeres;
    
    console.log('♻️ [ALUMNOS] Valores originales restaurados');
}

/**
 * Actualiza los totales en el pie de la tabla
 */
function actualizarTotalesTabla(tfoot, totalAlumnos, totalHombres, totalMujeres, filasVisibles) {
    const totalRow = tfoot.querySelector('.total-row');
    
    if (!totalRow) {
        return;
    }
    
    // Calcular porcentajes
    const porcentajeHombres = totalAlumnos > 0 ? ((totalHombres / totalAlumnos) * 100).toFixed(1) : '0.0';
    const porcentajeMujeres = totalAlumnos > 0 ? ((totalMujeres / totalAlumnos) * 100).toFixed(1) : '0.0';
    
    // Formatear números con comas
    const formatearNumero = (num) => num.toLocaleString('es-MX');
    
    // Actualizar celdas (recordar que cells[0] tiene colspan="2")
    totalRow.cells[0].textContent = `TOTAL FILTRADO (${filasVisibles} registro${filasVisibles !== 1 ? 's' : ''})`;
    totalRow.cells[1].textContent = formatearNumero(totalAlumnos);
    totalRow.cells[2].textContent = '100.0%';
    totalRow.cells[3].textContent = formatearNumero(totalHombres);
    totalRow.cells[4].textContent = porcentajeHombres + '%';
    totalRow.cells[5].textContent = formatearNumero(totalMujeres);
    totalRow.cells[6].textContent = porcentajeMujeres + '%';
    
    console.log('📊 [ALUMNOS] Totales actualizados:', {
        alumnos: totalAlumnos,
        hombres: totalHombres,
        mujeres: totalMujeres,
        filasVisibles: filasVisibles
    });
}

// =============================================================================
// EXPORTAR FUNCIONES PÚBLICAS
// =============================================================================

// Hacer disponibles las funciones principales
window.AlumnosModule = {
    inicializarGraficos,
    redimensionarGraficos,
    exportarExcel,
    exportarCSV,
    exportarPDF,
    inicializarFiltroBusqueda,
    debug
};

console.log('📚 Módulo de JavaScript para matrícula cargado correctamente');
