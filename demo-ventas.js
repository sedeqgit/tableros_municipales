// Demo Ventas JavaScript
// Sistema de demostración para ExportManager con gráfico circular

// Datos de demostración de ventas por categoría
const datosVentas = {
    'Q1-2024': {
        'Electrónicos': { ventas: 125000, productos: 45 },
        'Ropa y Accesorios': { ventas: 89000, productos: 78 },
        'Hogar y Jardín': { ventas: 67000, productos: 52 },
        'Deportes': { ventas: 54000, productos: 34 },
        'Libros': { ventas: 32000, productos: 156 },
        'Juguetes': { ventas: 28000, productos: 67 }
    },
    'Q2-2024': {
        'Electrónicos': { ventas: 145000, productos: 52 },
        'Ropa y Accesorios': { ventas: 95000, productos: 83 },
        'Hogar y Jardín': { ventas: 78000, productos: 61 },
        'Deportes': { ventas: 71000, productos: 41 },
        'Libros': { ventas: 29000, productos: 142 },
        'Juguetes': { ventas: 35000, productos: 73 }
    },
    'Q3-2024': {
        'Electrónicos': { ventas: 168000, productos: 58 },
        'Ropa y Accesorios': { ventas: 112000, productos: 91 },
        'Hogar y Jardín': { ventas: 89000, productos: 68 },
        'Deportes': { ventas: 85000, productos: 47 },
        'Libros': { ventas: 31000, productos: 138 },
        'Juguetes': { ventas: 42000, productos: 81 }
    },
    'Q4-2024': {
        'Electrónicos': { ventas: 195000, productos: 67 },
        'Ropa y Accesorios': { ventas: 135000, productos: 105 },
        'Hogar y Jardín': { ventas: 98000, productos: 74 },
        'Deportes': { ventas: 92000, productos: 53 },
        'Libros': { ventas: 38000, productos: 165 },
        'Juguetes': { ventas: 58000, productos: 95 }
    }
};

// Variables globales
let currentChart = null;
let currentData = null;
let filtrosActivos = {
    periodo: '',
    minVenta: 0
};

let configuracionGrafico = {
    tipo: 'pie',
    mostrar3D: false,
    mostrarPorcentajes: true
};

// Configuración del ExportManager para esta página
const exportConfig = {
    pageId: 'demo-ventas',
    title: 'Demo Ventas - Análisis por Categoría',
    chartSelector: '#chart_div',
    dataCallback: () => getCurrentExportData()
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando Demo Ventas...');
    
    // Cargar Google Charts
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(initChart);      // Configurar ExportManager con Anotaciones
    if (typeof ExportManagerAnnotations !== 'undefined') {
        ExportManagerAnnotations.configure({
            pageId: 'demo-ventas',
            title: 'Dashboard Demo - Ventas por Categoría',
            chartSelector: '#chart_div',
            dataCallback: () => getCurrentExportData(),
            chartInstance: currentChart,
            getChartData: () => getChartData(),
            getChartOptions: () => getChartOptions(),
            restoreChart: () => actualizarGrafico()
        });
        console.log('✅ ExportManager con Anotaciones configurado para ventas');
    } else {
        console.error('❌ ExportManager con Anotaciones no encontrado');
    }
    
    // Event listeners
    setupEventListeners();
    
    // Cargar datos iniciales
    actualizarEstadisticas();
    actualizarTopProductos();
});

function initChart() {
    console.log('📊 Inicializando gráfico de ventas...');
    mostrarLoading(true);
    
    setTimeout(() => {
        actualizarGrafico();
        actualizarTablaDetalle();
        mostrarLoading(false);
    }, 1200);
}

function setupEventListeners() {
    // Filtros
    document.getElementById('btnAplicarFiltros').addEventListener('click', aplicarFiltros);
    document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
    
    // Configuración
    document.getElementById('tipoGrafico').addEventListener('change', cambiarTipoGrafico);
    document.getElementById('mostrar3D').addEventListener('change', cambiarConfiguracion);
    document.getElementById('mostrarPorcentajes').addEventListener('change', cambiarConfiguracion);
    
    // Exportación
    document.getElementById('btnExportPNG').addEventListener('click', exportarPNG);
    document.getElementById('btnExportExcel').addEventListener('click', exportarExcel);
    
    // Auto-aplicar filtros
    document.getElementById('filtroPeriodo').addEventListener('change', onFiltroChange);
    document.getElementById('filtroMinVenta').addEventListener('input', onFiltroChange);
}

function onFiltroChange() {
    clearTimeout(window.filtroTimeout);
    window.filtroTimeout = setTimeout(aplicarFiltros, 800);
}

function aplicarFiltros() {
    const periodo = document.getElementById('filtroPeriodo').value;
    const minVenta = parseInt(document.getElementById('filtroMinVenta').value) || 0;
    
    filtrosActivos = { periodo, minVenta };
    
    console.log('🔍 Aplicando filtros ventas:', filtrosActivos);
    
    mostrarLoading(true);
    setTimeout(() => {
        actualizarGrafico();
        actualizarEstadisticas();
        actualizarTopProductos();
        actualizarTablaDetalle();
        mostrarLoading(false);
    }, 1000);
}

function limpiarFiltros() {
    document.getElementById('filtroPeriodo').value = '';
    document.getElementById('filtroMinVenta').value = '';
    filtrosActivos = { periodo: '', minVenta: 0 };
    
    console.log('🧹 Limpiando filtros ventas');
    aplicarFiltros();
}

function cambiarTipoGrafico() {
    configuracionGrafico.tipo = document.getElementById('tipoGrafico').value;
    console.log('🔄 Cambiando tipo de gráfico:', configuracionGrafico.tipo);
    actualizarGrafico();
}

function cambiarConfiguracion() {
    configuracionGrafico.mostrar3D = document.getElementById('mostrar3D').checked;
    configuracionGrafico.mostrarPorcentajes = document.getElementById('mostrarPorcentajes').checked;
    
    console.log('⚙️ Actualizando configuración:', configuracionGrafico);
    actualizarGrafico();
}

function actualizarGrafico() {
    const datosFiltrados = filtrarDatos();
    currentData = datosFiltrados;
    
    const chartData = prepararDatosGrafico(datosFiltrados);
    const options = getOpcionesGrafico();
    
    const dataTable = google.visualization.arrayToDataTable(chartData);
    
    // Seleccionar tipo de gráfico
    switch (configuracionGrafico.tipo) {
        case 'pie':
            currentChart = new google.visualization.PieChart(document.getElementById('chart_div'));
            break;
        case 'donut':
            options.pieHole = 0.4;
            currentChart = new google.visualization.PieChart(document.getElementById('chart_div'));
            break;
        case 'bar':
            currentChart = new google.visualization.BarChart(document.getElementById('chart_div'));
            break;
        default:
            currentChart = new google.visualization.PieChart(document.getElementById('chart_div'));
    }
    
    currentChart.draw(dataTable, options);
}

function filtrarDatos() {
    let datos = JSON.parse(JSON.stringify(datosVentas)); // Deep copy
    
    if (filtrosActivos.periodo) {
        datos = { [filtrosActivos.periodo]: datos[filtrosActivos.periodo] };
    }
    
    // Aplicar filtro de venta mínima
    if (filtrosActivos.minVenta > 0) {
        Object.keys(datos).forEach(periodo => {
            Object.keys(datos[periodo]).forEach(categoria => {
                if (datos[periodo][categoria].ventas < filtrosActivos.minVenta) {
                    delete datos[periodo][categoria];
                }
            });
        });
    }
    
    return datos;
}

function prepararDatosGrafico(datos) {
    // Agregar datos de todos los períodos
    const ventasAgregadas = {};
    
    Object.values(datos).forEach(periodoData => {
        Object.entries(periodoData).forEach(([categoria, info]) => {
            if (!ventasAgregadas[categoria]) {
                ventasAgregadas[categoria] = 0;
            }
            ventasAgregadas[categoria] += info.ventas;
        });
    });
    
    const chartData = [['Categoría', 'Ventas']];
    
    Object.entries(ventasAgregadas)
        .sort(([,a], [,b]) => b - a) // Ordenar por ventas desc
        .forEach(([categoria, ventas]) => {
            chartData.push([categoria, ventas]);
        });
    
    return chartData;
}

function getOpcionesGrafico() {
    const baseOptions = {
        title: getTituloGrafico(),
        titleTextStyle: {
            fontSize: 18,
            bold: true,
            color: '#8e44ad'
        },
        backgroundColor: 'transparent',
        chartArea: {
            left: 50,
            top: 60,
            width: '85%',
            height: '75%'
        },
        colors: ['#8e44ad', '#e91e63', '#f39c12', '#27ae60', '#3498db', '#e74c3c', '#9b59b6'],
        animation: {
            startup: true,
            duration: 1000,
            easing: 'out'
        }
    };
    
    if (configuracionGrafico.tipo === 'pie' || configuracionGrafico.tipo === 'donut') {
        return {
            ...baseOptions,
            is3D: configuracionGrafico.mostrar3D,
            pieSliceTextStyle: {
                color: 'white',
                fontSize: 12,
                bold: true
            },
            legend: {
                position: 'right',
                alignment: 'center',
                textStyle: { 
                    color: '#2c3e50', 
                    fontSize: 11 
                }
            },
            pieSliceText: configuracionGrafico.mostrarPorcentajes ? 'percentage' : 'none'
        };
    } else {
        return {
            ...baseOptions,
            hAxis: {
                title: 'Ventas ($)',
                titleTextStyle: { color: '#8e44ad', fontSize: 14 },
                format: '$#,###'
            },
            vAxis: {
                title: 'Categoría',
                titleTextStyle: { color: '#8e44ad', fontSize: 14 }
            },
            legend: { position: 'none' },
            bar: { groupWidth: '70%' }
        };
    }
}

function getChartOptions() {
    // Wrapper para el ExportManager con anotaciones
    return getOpcionesGrafico();
}

// Función nueva para ExportManagerAnnotations - convierte currentData a formato array bidimensional
function getChartData() {
    if (!currentData) {
        console.warn('⚠️ No hay currentData disponible para convertir (ventas)');
        return null;
    }

    try {
        // Agregar datos de todos los períodos
        const ventasPorCategoria = {};
        
        Object.values(currentData).forEach(periodoData => {
            Object.entries(periodoData).forEach(([categoria, info]) => {
                if (!ventasPorCategoria[categoria]) {
                    ventasPorCategoria[categoria] = 0;
                }
                ventasPorCategoria[categoria] += info.ventas;
            });
        });
        
        const chartData = [['Categoría', 'Ventas']];
        
        Object.entries(ventasPorCategoria)
            .sort(([,a], [,b]) => b - a) // Ordenar por ventas desc
            .forEach(([categoria, ventas]) => {
                chartData.push([categoria, ventas]);
            });
        
        console.log('✅ Datos de ventas convertidos:', chartData);
        return chartData;
        
    } catch (error) {
        console.error('❌ Error al convertir datos de ventas para anotaciones:', error);
        return null;
    }
}

function getTituloGrafico() {
    let titulo = 'Ventas por Categoría';
    
    if (filtrosActivos.periodo) {
        titulo += ` - ${filtrosActivos.periodo}`;
    } else {
        titulo += ' - Todos los períodos';
    }
    
    if (filtrosActivos.minVenta > 0) {
        titulo += ` (Min: $${filtrosActivos.minVenta.toLocaleString()})`;
    }
    
    return titulo;
}

function actualizarEstadisticas() {
    const datosFiltrados = filtrarDatos();
    
    let totalVentas = 0;
    let categorias = new Set();
    let ventasPorCategoria = {};
    
    Object.values(datosFiltrados).forEach(periodoData => {
        Object.entries(periodoData).forEach(([categoria, info]) => {
            totalVentas += info.ventas;
            categorias.add(categoria);
            if (!ventasPorCategoria[categoria]) {
                ventasPorCategoria[categoria] = 0;
            }
            ventasPorCategoria[categoria] += info.ventas;
        });
    });
    
    const categoriaTop = Object.keys(ventasPorCategoria).reduce((a, b) => 
        ventasPorCategoria[a] > ventasPorCategoria[b] ? a : b, '');
    
    const promedioCategoria = categorias.size > 0 ? totalVentas / categorias.size : 0;
    
    // Actualizar elementos con animación
    animateNumber('totalVentas', totalVentas, true);
    animateNumber('totalCategorias', categorias.size);
    animateNumber('promedioCategoria', promedioCategoria, true);
    
    // Actualizar categoría top
    const elementoTop = document.getElementById('categoriaTop');
    elementoTop.textContent = categoriaTop || '-';
    elementoTop.parentElement.classList.add('bounce');
    setTimeout(() => elementoTop.parentElement.classList.remove('bounce'), 500);
}

function actualizarTopProductos() {
    const datosFiltrados = filtrarDatos();
    const ventasPorCategoria = {};
    
    Object.values(datosFiltrados).forEach(periodoData => {
        Object.entries(periodoData).forEach(([categoria, info]) => {
            if (!ventasPorCategoria[categoria]) {
                ventasPorCategoria[categoria] = { ventas: 0, productos: 0 };
            }
            ventasPorCategoria[categoria].ventas += info.ventas;
            ventasPorCategoria[categoria].productos += info.productos;
        });
    });
    
    const topContainer = document.getElementById('topProductos');
    topContainer.innerHTML = '';
    
    Object.entries(ventasPorCategoria)
        .sort(([,a], [,b]) => b.ventas - a.ventas)
        .slice(0, 5)
        .forEach(([categoria, info], index) => {
            const item = document.createElement('div');
            item.className = 'producto-item fade-in';
            item.style.animationDelay = `${index * 0.1}s`;
            
            item.innerHTML = `
                <div>
                    <div class="producto-nombre">${categoria}</div>
                    <small class="text-muted">${info.productos} productos</small>
                </div>
                <div class="producto-valor">$${info.ventas.toLocaleString()}</div>
            `;
            
            topContainer.appendChild(item);
        });
}

function actualizarTablaDetalle() {
    const datosFiltrados = filtrarDatos();
    const ventasPorCategoria = {};
    let totalGeneral = 0;
    
    Object.values(datosFiltrados).forEach(periodoData => {
        Object.entries(periodoData).forEach(([categoria, info]) => {
            if (!ventasPorCategoria[categoria]) {
                ventasPorCategoria[categoria] = { ventas: 0, productos: 0 };
            }
            ventasPorCategoria[categoria].ventas += info.ventas;
            ventasPorCategoria[categoria].productos += info.productos;
            totalGeneral += info.ventas;
        });
    });
    
    const tbody = document.querySelector('#tablaDetalle tbody');
    tbody.innerHTML = '';
    
    Object.entries(ventasPorCategoria)
        .sort(([,a], [,b]) => b.ventas - a.ventas)
        .forEach(([categoria, info]) => {
            const participacion = ((info.ventas / totalGeneral) * 100).toFixed(1);
            const promedioProducto = (info.ventas / info.productos).toFixed(0);
            
            const row = document.createElement('tr');
            row.className = 'slide-in';
            row.innerHTML = `
                <td><strong>${categoria}</strong></td>
                <td>$${info.ventas.toLocaleString()}</td>
                <td>${participacion}%</td>
                <td>${info.productos}</td>
                <td>$${parseInt(promedioProducto).toLocaleString()}</td>
            `;
            
            tbody.appendChild(row);
        });
}

function animateNumber(elementId, targetValue, isFormat = false) {
    const element = document.getElementById(elementId);
    const startValue = parseFloat(element.textContent.replace(/[$,]/g, '')) || 0;
    const increment = (targetValue - startValue) / 60;
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if ((increment > 0 && currentValue >= targetValue) || 
            (increment < 0 && currentValue <= targetValue)) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        
        const displayValue = Math.round(currentValue);
        element.textContent = isFormat ? 
            `$${displayValue.toLocaleString()}` : 
            displayValue.toLocaleString();
    }, 16);
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
    console.log('📸 Exportando PNG con anotaciones desde ventas...');
    
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
    console.log('📊 Exportando Excel desde ventas...');
    
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
    
    const incluirDetalles = document.getElementById('incluirDetalles').checked;
    const exportData = [];
    
    // Metadata
    exportData.push(['REPORTE DE VENTAS POR CATEGORÍA']);
    exportData.push(['Fecha de generación:', new Date().toLocaleString()]);
    exportData.push(['Período:', filtrosActivos.periodo || 'Todos los períodos']);
    exportData.push(['Venta mínima:', filtrosActivos.minVenta ? `$${filtrosActivos.minVenta.toLocaleString()}` : 'Sin filtro']);
    exportData.push(['']); // Separador
    
    // Resumen por categoría
    const ventasPorCategoria = {};
    let totalGeneral = 0;
    
    Object.values(currentData).forEach(periodoData => {
        Object.entries(periodoData).forEach(([categoria, info]) => {
            if (!ventasPorCategoria[categoria]) {
                ventasPorCategoria[categoria] = { ventas: 0, productos: 0 };
            }
            ventasPorCategoria[categoria].ventas += info.ventas;
            ventasPorCategoria[categoria].productos += info.productos;
            totalGeneral += info.ventas;
        });
    });
    
    exportData.push(['RESUMEN POR CATEGORÍA']);
    exportData.push(['Categoría', 'Ventas ($)', 'Participación (%)', 'Productos', 'Promedio/Producto']);
    
    Object.entries(ventasPorCategoria)
        .sort(([,a], [,b]) => b.ventas - a.ventas)
        .forEach(([categoria, info]) => {
            const participacion = ((info.ventas / totalGeneral) * 100).toFixed(1);
            const promedioProducto = (info.ventas / info.productos).toFixed(0);
            
            exportData.push([
                categoria,
                info.ventas,
                `${participacion}%`,
                info.productos,
                parseInt(promedioProducto)
            ]);
        });
    
    // Detalles por período (si está habilitado)
    if (incluirDetalles && Object.keys(currentData).length > 1) {
        exportData.push(['']); // Separador
        exportData.push(['DETALLE POR PERÍODO']);
        
        Object.entries(currentData).forEach(([periodo, periodoData]) => {
            exportData.push(['']); // Separador
            exportData.push([`Período: ${periodo}`]);
            exportData.push(['Categoría', 'Ventas ($)', 'Productos']);
            
            Object.entries(periodoData)
                .sort(([,a], [,b]) => b.ventas - a.ventas)
                .forEach(([categoria, info]) => {
                    exportData.push([categoria, info.ventas, info.productos]);
                });
        });
    }
    
    return exportData;
}

// Función de debug
window.debugVentas = function() {
    console.log('🔍 Debug Ventas:');
    console.log('- Datos actuales:', currentData);
    console.log('- Filtros activos:', filtrosActivos);
    console.log('- Configuración gráfico:', configuracionGrafico);
    console.log('- ExportManager disponible:', typeof ExportManager !== 'undefined');
    console.log('- Gráfico cargado:', currentChart !== null);
};
