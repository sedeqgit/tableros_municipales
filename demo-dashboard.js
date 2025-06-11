// Demo Dashboard JavaScript
// Sistema de demostración para ExportManager

// Datos de demostración
const datosEstudiantes = {
    2020: {
        'Preescolar': 1200,
        'Primaria': 3500,
        'Secundaria': 2800,
        'Bachillerato': 1800
    },
    2021: {
        'Preescolar': 1350,
        'Primaria': 3800,
        'Secundaria': 3200,
        'Bachillerato': 2100
    },
    2022: {
        'Preescolar': 1450,
        'Primaria': 4100,
        'Secundaria': 3400,
        'Bachillerato': 2300
    },
    2023: {
        'Preescolar': 1600,
        'Primaria': 4300,
        'Secundaria': 3600,
        'Bachillerato': 2500
    },
    2024: {
        'Preescolar': 1750,
        'Primaria': 4500,
        'Secundaria': 3800,
        'Bachillerato': 2700
    }
};

// Variables globales
let currentChart = null;
let currentData = null;
let filtrosActivos = {
    anio: '',
    nivel: ''
};

// Configuración del ExportManager para esta página
const exportConfig = {
    pageId: 'demo-dashboard',
    title: 'Dashboard Demo - Estudiantes por Nivel',
    chartSelector: '#chart_div',
    dataCallback: () => getCurrentExportData()
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando Demo Dashboard...');
    
    // Cargar Google Charts
    google.charts.load('current', {'packages':['corechart', 'bar']});
    google.charts.setOnLoadCallback(initChart);      // Configurar ExportManager con Anotaciones
    if (typeof ExportManagerAnnotations !== 'undefined') {
        ExportManagerAnnotations.configure({
            pageId: 'demo-dashboard',
            title: 'Dashboard Demo - Estudiantes por Nivel',
            chartSelector: '#chart_div',
            dataCallback: () => getCurrentExportData(),
            chartInstance: currentChart,
            getChartData: () => getChartData(),
            getChartOptions: () => getChartOptions(),
            restoreChart: () => actualizarGrafico()
        });
        console.log('✅ ExportManager con Anotaciones configurado correctamente');
    } else {
        console.error('❌ ExportManager con Anotaciones no encontrado');
    }
    
    // Event listeners
    setupEventListeners();
    
    // Cargar datos iniciales
    actualizarEstadisticas();
});

function initChart() {
    console.log('📊 Inicializando gráfico...');
    mostrarLoading(true);
    
    setTimeout(() => {
        actualizarGrafico();
        mostrarLoading(false);
    }, 1000);
}

function setupEventListeners() {
    // Filtros
    document.getElementById('btnAplicarFiltros').addEventListener('click', aplicarFiltros);
    document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
    
    // Exportación
    document.getElementById('btnExportPNG').addEventListener('click', exportarPNG);
    document.getElementById('btnExportExcel').addEventListener('click', exportarExcel);
    
    // Cambios en filtros
    document.getElementById('filtroAnio').addEventListener('change', onFiltroChange);
    document.getElementById('filtroNivel').addEventListener('change', onFiltroChange);
}

function onFiltroChange() {
    // Auto-aplicar filtros después de un breve delay
    clearTimeout(window.filtroTimeout);
    window.filtroTimeout = setTimeout(aplicarFiltros, 500);
}

function aplicarFiltros() {
    const anio = document.getElementById('filtroAnio').value;
    const nivel = document.getElementById('filtroNivel').value;
    
    filtrosActivos = { anio, nivel };
    
    console.log('🔍 Aplicando filtros:', filtrosActivos);
    
    mostrarLoading(true);
    setTimeout(() => {
        actualizarGrafico();
        actualizarEstadisticas();
        mostrarLoading(false);
    }, 800);
}

function limpiarFiltros() {
    document.getElementById('filtroAnio').value = '';
    document.getElementById('filtroNivel').value = '';
    filtrosActivos = { anio: '', nivel: '' };
    
    console.log('🧹 Limpiando filtros');
    aplicarFiltros();
}

function actualizarGrafico() {
    const datosFiltrados = filtrarDatos();
    currentData = datosFiltrados;
    
    let chartData, options;
    
    if (filtrosActivos.anio) {
        // Vista por año específico - mostrar niveles
        chartData = prepararDatosNiveles(datosFiltrados);
        options = getOpcionesGraficoNiveles();
    } else {
        // Vista general - mostrar años
        chartData = prepararDatosAnios(datosFiltrados);
        options = getOpcionesGraficoAnios();
    }
    
    const dataTable = google.visualization.arrayToDataTable(chartData);
    currentChart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
    
    currentChart.draw(dataTable, options);
}

function filtrarDatos() {
    let datos = JSON.parse(JSON.stringify(datosEstudiantes)); // Deep copy
    
    if (filtrosActivos.anio) {
        // Filtrar por año específico
        datos = { [filtrosActivos.anio]: datos[filtrosActivos.anio] };
    }
    
    if (filtrosActivos.nivel) {
        // Filtrar por nivel específico
        Object.keys(datos).forEach(anio => {
            const valorNivel = datos[anio][filtrosActivos.nivel];
            datos[anio] = { [filtrosActivos.nivel]: valorNivel };
        });
    }
    
    return datos;
}

function prepararDatosNiveles(datos) {
    const anio = Object.keys(datos)[0];
    const nivelesData = datos[anio];
    
    const chartData = [['Nivel Educativo', 'Estudiantes']];
    
    Object.entries(nivelesData).forEach(([nivel, cantidad]) => {
        chartData.push([nivel, cantidad]);
    });
    
    return chartData;
}

function prepararDatosAnios(datos) {
    const anios = Object.keys(datos).sort();
    const niveles = Object.keys(datos[anios[0]]);
    
    // Header
    const chartData = [['Año', ...niveles]];
    
    // Data rows
    anios.forEach(anio => {
        const row = [anio];
        niveles.forEach(nivel => {
            row.push(datos[anio][nivel] || 0);
        });
        chartData.push(row);
    });
    
    return chartData;
}

function getOpcionesGraficoNiveles() {
    return {
        title: `Estudiantes por Nivel Educativo - ${filtrosActivos.anio}`,
        titleTextStyle: {
            fontSize: 18,
            bold: true,
            color: '#2c3e50'
        },
        hAxis: {
            title: 'Nivel Educativo',
            titleTextStyle: { color: '#2c3e50', fontSize: 14 }
        },
        vAxis: {
            title: 'Número de Estudiantes',
            titleTextStyle: { color: '#2c3e50', fontSize: 14 },
            format: '#,###'
        },
        colors: ['#3498db', '#27ae60', '#f39c12', '#e74c3c'],
        backgroundColor: 'transparent',
        chartArea: {
            left: 80,
            top: 60,
            width: '75%',
            height: '70%'
        },
        legend: {
            position: 'none'
        },
        bar: {
            groupWidth: '60%'
        },
        animation: {
            startup: true,
            duration: 1000,
            easing: 'out'
        }
    };
}

function getOpcionesGraficoAnios() {
    return {
        title: 'Evolución de Estudiantes por Año y Nivel Educativo',
        titleTextStyle: {
            fontSize: 18,
            bold: true,
            color: '#2c3e50'
        },
        hAxis: {
            title: 'Año',
            titleTextStyle: { color: '#2c3e50', fontSize: 14 }
        },
        vAxis: {
            title: 'Número de Estudiantes',
            titleTextStyle: { color: '#2c3e50', fontSize: 14 },
            format: '#,###'
        },
        colors: ['#3498db', '#27ae60', '#f39c12', '#e74c3c'],
        backgroundColor: 'transparent',
        chartArea: {
            left: 80,
            top: 60,
            width: '75%',
            height: '70%'
        },
        legend: {
            position: 'bottom',
            alignment: 'center',
            textStyle: { color: '#2c3e50', fontSize: 12 }
        },
        bar: {
            groupWidth: '70%'
        },
        animation: {
            startup: true,
            duration: 1000,
            easing: 'out'
        },
        isStacked: false
    };
}

function getChartOptions() {
    // Retornar opciones según el filtro activo
    if (filtrosActivos.anio) {
        return getOpcionesGraficoNiveles();
    } else {
        return getOpcionesGraficoAnios();
    }
}

// Función nueva para ExportManagerAnnotations - convierte currentData a formato array bidimensional
function getChartData() {
    if (!currentData) {
        console.warn('⚠️ No hay currentData disponible para convertir');
        return null;
    }

    try {
        if (filtrosActivos.anio) {
            // Vista por niveles - año específico
            const anio = Object.keys(currentData)[0];
            const nivelesData = currentData[anio];
            
            const chartData = [['Nivel Educativo', 'Estudiantes']];
            
            Object.entries(nivelesData).forEach(([nivel, cantidad]) => {
                chartData.push([nivel, cantidad]);
            });
            
            console.log('✅ Datos convertidos (por nivel):', chartData);
            return chartData;
            
        } else {
            // Vista por años - vista general
            const anios = Object.keys(currentData).sort();
            const niveles = Object.keys(currentData[anios[0]]);
            
            // Header
            const chartData = [['Año', ...niveles]];
            
            // Data rows
            anios.forEach(anio => {
                const row = [anio];
                niveles.forEach(nivel => {
                    row.push(currentData[anio][nivel] || 0);
                });
                chartData.push(row);
            });
            
            console.log('✅ Datos convertidos (por años):', chartData);
            return chartData;
        }
        
    } catch (error) {
        console.error('❌ Error al convertir datos para anotaciones:', error);
        return null;
    }
}

function actualizarEstadisticas() {
    const datosFiltrados = filtrarDatos();
    
    let totalEstudiantes = 0;
    let nivelesSet = new Set();
    const anios = Object.keys(datosFiltrados);
    
    Object.values(datosFiltrados).forEach(anioData => {
        Object.entries(anioData).forEach(([nivel, cantidad]) => {
            totalEstudiantes += cantidad;
            nivelesSet.add(nivel);
        });
    });
    
    const promedioAnual = anios.length > 0 ? Math.round(totalEstudiantes / anios.length) : 0;
    
    // Actualizar elementos con animación
    animateNumber('totalEstudiantes', totalEstudiantes);
    animateNumber('totalNiveles', nivelesSet.size);
    animateNumber('totalAnios', anios.length);
    animateNumber('promedioAnual', promedioAnual);
}

function animateNumber(elementId, targetValue) {
    const element = document.getElementById(elementId);
    const startValue = parseInt(element.textContent) || 0;
    const increment = (targetValue - startValue) / 50;
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if ((increment > 0 && currentValue >= targetValue) || 
            (increment < 0 && currentValue <= targetValue)) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        element.textContent = Math.round(currentValue).toLocaleString();
    }, 20);
}

function mostrarLoading(show) {
    const loading = document.getElementById('loading');
    const chart = document.getElementById('chart_div');
    
    if (show) {
        loading.classList.remove('d-none');
        chart.style.opacity = '0.3';
    } else {
        loading.classList.add('d-none');
        chart.style.opacity = '1';
    }
}

// Funciones de exportación
function exportarPNG() {
    console.log('📸 Exportando PNG con anotaciones...');
    
    if (!currentChart) {
        alert('Por favor, espere a que se cargue el gráfico');
        return;
    }
    
    try {
        ExportManagerAnnotations.exportPNG();
    } catch (error) {
        console.error('Error al exportar PNG:', error);
        alert('Error al exportar PNG. Revise la consola para más detalles.');
    }
}

function exportarExcel() {
    console.log('📊 Exportando Excel...');
    
    if (!currentData) {
        alert('No hay datos para exportar');
        return;
    }
    
    try {
        ExportManagerAnnotations.exportExcel();
    } catch (error) {
        console.error('Error al exportar Excel:', error);
        alert('Error al exportar Excel. Revise la consola para más detalles.');
    }
}

function getCurrentExportData() {
    if (!currentData) return null;
    
    const exportData = [];
    const metadata = {
        titulo: exportConfig.title,
        fechaGeneracion: new Date().toLocaleString(),
        filtros: {
            año: filtrosActivos.anio || 'Todos',
            nivel: filtrosActivos.nivel || 'Todos'
        }
    };
    
    // Agregar metadata
    exportData.push(['REPORTE DE ESTUDIANTES']);
    exportData.push(['Fecha de generación:', metadata.fechaGeneracion]);
    exportData.push(['Filtro Año:', metadata.filtros.año]);
    exportData.push(['Filtro Nivel:', metadata.filtros.nivel]);
    exportData.push(['']); // Separador
    
    if (filtrosActivos.anio) {
        // Vista por niveles
        exportData.push(['Nivel Educativo', 'Número de Estudiantes']);
        Object.entries(currentData[filtrosActivos.anio]).forEach(([nivel, cantidad]) => {
            exportData.push([nivel, cantidad]);
        });
    } else {
        // Vista por años
        const anios = Object.keys(currentData).sort();
        const niveles = Object.keys(currentData[anios[0]]);
        
        // Header
        exportData.push(['Año', ...niveles, 'Total']);
        
        // Data
        anios.forEach(anio => {
            const row = [anio];
            let totalAnio = 0;
            niveles.forEach(nivel => {
                const cantidad = currentData[anio][nivel] || 0;
                row.push(cantidad);
                totalAnio += cantidad;
            });
            row.push(totalAnio);
            exportData.push(row);
        });
    }
    
    return exportData;
}

// Función de debug
window.debugDashboard = function() {
    console.log('🔍 Debug Dashboard:');
    console.log('- Datos actuales:', currentData);
    console.log('- Filtros activos:', filtrosActivos);
    console.log('- ExportManager disponible:', typeof ExportManager !== 'undefined');
    console.log('- Gráfico cargado:', currentChart !== null);
};
