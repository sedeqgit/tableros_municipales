/**
 * Archivo JavaScript para el Dashboard Estadístico - SEDEQ
 * Gestiona las visualizaciones, gráficos y exportaciones
 */

// Cargar la librería de Google Charts y configurar
google.charts.load('current', { 'packages': ['corechart', 'bar'] });
google.charts.setOnLoadCallback(dibujarGrafico);

// Variables globales
let chartData;
let chart;
let tipoVisualizacion = 'ambos';
let tipoGrafico = 'column';

/**
 * Función para dibujar el gráfico principal
 */
function dibujarGrafico() {
    // Obtener los datos
    chartData = new google.visualization.arrayToDataTable(datosEducativos);
    
    // Configurar opciones del gráfico
    const options = configurarOpciones();
    
    // Crear el gráfico según el tipo seleccionado
    chart = crearGrafico(options);
    
    // Actualizar la tabla con los mismos datos
    actualizarTabla();
      // Actualizar el texto del análisis
    actualizarAnalisis();
    
    // Actualizar los totales de la tarjeta de resumen
    document.getElementById('metricGrowth').textContent = totalEscuelasFormateado;
    document.getElementById('metricDecline').textContent = totalAlumnosFormateado;
}

/**
 * Configura las opciones del gráfico basado en preferencias
 * @returns {Object} Opciones de configuración para el gráfico
 */
function configurarOpciones() {
    // Ajustar datos según la visualización
    let datosAjustados = new google.visualization.DataTable();
    datosAjustados.addColumn('string', 'Tipo Educativo');
    
    if (tipoVisualizacion === 'ambos' || tipoVisualizacion === 'escuelas') {
        datosAjustados.addColumn('number', 'Escuelas');
    }
    
    if (tipoVisualizacion === 'ambos' || tipoVisualizacion === 'alumnos') {
        datosAjustados.addColumn('number', 'Alumnos');
    }
    
    // Añadir los datos según la visualización seleccionada
    for (let i = 1; i < datosEducativos.length; i++) {
        let row = [datosEducativos[i][0]];
        
        if (tipoVisualizacion === 'ambos') {
            row.push(datosEducativos[i][1], datosEducativos[i][2]);
        } else if (tipoVisualizacion === 'escuelas') {
            row.push(datosEducativos[i][1]);
        } else if (tipoVisualizacion === 'alumnos') {
            row.push(datosEducativos[i][2]);
        }
        
        datosAjustados.addRow(row);
    }
    
    chartData = datosAjustados;
    
    // Definir opciones base del gráfico
    let options = {
        title: 'Estadística Educativa por Tipo',
        titleTextStyle: {
            color: '#004990',
            fontSize: 16,
            bold: true
        },
        colors: ['#004990', '#9D2449'],
        animation: {
            startup: true,
            duration: 1000,
            easing: 'out'
        },
        chartArea: {
            width: '80%',
            height: '70%'
        },
        legend: {
            position: 'top'
        },
        hAxis: {
            title: 'Tipo Educativo',
            titleTextStyle: { color: '#333', italic: false, bold: true }
        },
        vAxis: {
            minValue: 0,
            titleTextStyle: { color: '#333', italic: false, bold: true }
        }
    };
    
    // Ajustar opciones según el tipo de gráfico
    if (tipoGrafico === 'column') {
        options.vAxis.title = 'Cantidad';
        options.bar = { groupWidth: '70%' };
    } else if (tipoGrafico === 'bar') {
        options.hAxis.title = 'Cantidad';
        options.vAxis.title = 'Tipo Educativo';
        options.bar = { groupWidth: '70%' };
    } else if (tipoGrafico === 'pie') {
        options.pieSliceText = 'percentage';
        options.pieSliceTextStyle = { fontSize: 14 };
        options.tooltip = { text: 'value' };
        options.slices = {
            0: { offset: 0.05 },
            1: { offset: 0 },
            2: { offset: 0 },
            3: { offset: 0 },
            4: { offset: 0 }
        };
    }
    
    return options;
}

/**
 * Crea el gráfico según el tipo seleccionado
 * @param {Object} options Opciones de configuración
 * @returns {Object} Objeto del gráfico creado
 */
function crearGrafico(options) {
    const chartDiv = document.getElementById('chart_div');
    let chart;
    
    if (tipoGrafico === 'column') {
        chart = new google.visualization.ColumnChart(chartDiv);
    } else if (tipoGrafico === 'bar') {
        chart = new google.visualization.BarChart(chartDiv);
    } else if (tipoGrafico === 'pie') {
        // Para pastel, solo podemos mostrar una serie a la vez
        if (tipoVisualizacion === 'ambos') {
            // Si se seleccionaron ambos, mostramos escuelas por defecto
            let datosEscuelas = new google.visualization.DataTable();
            datosEscuelas.addColumn('string', 'Tipo Educativo');
            datosEscuelas.addColumn('number', 'Escuelas');
            
            for (let i = 1; i < datosEducativos.length; i++) {
                datosEscuelas.addRow([datosEducativos[i][0], datosEducativos[i][1]]);
            }
            
            chartData = datosEscuelas;
            options.title = 'Distribución de Escuelas por Tipo Educativo';
        }
        
        chart = new google.visualization.PieChart(chartDiv);
    }
    
    chart.draw(chartData, options);
    return chart;
}

/**
 * Actualiza la tabla de datos numéricos
 */
function actualizarTabla() {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '';
    
    let totalEscuelas = 0;
    let totalAlumnos = 0;
    
    // Añadir filas de datos
    for (let i = 1; i < datosEducativos.length; i++) {
        const fila = document.createElement('tr');
        
        const tipoCell = document.createElement('td');
        tipoCell.textContent = datosEducativos[i][0];
        fila.appendChild(tipoCell);
        
        const escuelasCell = document.createElement('td');
        escuelasCell.textContent = Number(datosEducativos[i][1]).toLocaleString();
        fila.appendChild(escuelasCell);
        totalEscuelas += Number(datosEducativos[i][1]);
        
        const alumnosCell = document.createElement('td');
        alumnosCell.textContent = Number(datosEducativos[i][2]).toLocaleString();
        fila.appendChild(alumnosCell);
        totalAlumnos += Number(datosEducativos[i][2]);
        
        tbody.appendChild(fila);
    }
    
    // Añadir fila de totales
    const filaTotal = document.createElement('tr');
    filaTotal.className = 'total-row';
    
    const totalLabel = document.createElement('td');
    totalLabel.textContent = 'Total';
    filaTotal.appendChild(totalLabel);
    
    const totalEscuelasCell = document.createElement('td');
    totalEscuelasCell.textContent = totalEscuelas.toLocaleString();
    filaTotal.appendChild(totalEscuelasCell);
    
    const totalAlumnosCell = document.createElement('td');
    totalAlumnosCell.textContent = totalAlumnos.toLocaleString();
    filaTotal.appendChild(totalAlumnosCell);
    
    tbody.appendChild(filaTotal);
}

/**
 * Actualiza el texto de análisis con información dinámica
 */
function actualizarAnalisis() {
    // Encontrar tipo educativo con más escuelas
    let maxEscuelas = { tipo: '', valor: 0 };
    let maxAlumnos = { tipo: '', valor: 0 };
    
    for (let i = 1; i < datosEducativos.length; i++) {
        if (datosEducativos[i][1] > maxEscuelas.valor) {
            maxEscuelas.tipo = datosEducativos[i][0];
            maxEscuelas.valor = datosEducativos[i][1];
        }
        
        if (datosEducativos[i][2] > maxAlumnos.valor) {
            maxAlumnos.tipo = datosEducativos[i][0];
            maxAlumnos.valor = datosEducativos[i][2];
        }
    }
    
    // Encontrar relación alumnos/escuela más alta
    let maxRelacion = { tipo: '', valor: 0 };
    
    for (let i = 1; i < datosEducativos.length; i++) {
        const relacion = datosEducativos[i][2] / datosEducativos[i][1];
        if (relacion > maxRelacion.valor) {
            maxRelacion.tipo = datosEducativos[i][0];
            maxRelacion.valor = relacion;
        }
    }
    
    // Generar texto de análisis
    const analisisHTML = `
        El análisis muestra que <span class="highlight">${maxEscuelas.tipo}</span> tiene el mayor número de
        escuelas con <span class="highlight">${maxEscuelas.valor.toLocaleString()}</span>
        instituciones y <span class="highlight">${maxAlumnos.valor.toLocaleString()}</span> alumnos en total. El nivel <span
            class="highlight">${maxRelacion.tipo}</span> cuenta con la proporción más alta de alumnos por
        escuela (<span class="highlight">${Math.round(maxRelacion.valor).toLocaleString()}</span> alumnos por escuela).
    `;
    
    document.getElementById('analisisDinamico').innerHTML = analisisHTML;
}

/**
 * Exporta los datos a Excel
 */
function exportarExcel() {
    const nombreArchivo = 'Estadistica_Educativa_Queretaro.xlsx';
    const wb = XLSX.utils.book_new();
    
    // Preparar datos para exportación
    const datosExport = [
        ['Tipo Educativo', 'Escuelas', 'Alumnos']
    ];
    
    for (let i = 1; i < datosEducativos.length; i++) {
        datosExport.push([
            datosEducativos[i][0],
            datosEducativos[i][1],
            datosEducativos[i][2]
        ]);
    }
    
    // Añadir fila de totales
    let totalEscuelas = 0;
    let totalAlumnos = 0;
    
    for (let i = 1; i < datosEducativos.length; i++) {
        totalEscuelas += datosEducativos[i][1];
        totalAlumnos += datosEducativos[i][2];
    }
    
    datosExport.push(['Total', totalEscuelas, totalAlumnos]);
    
    const ws = XLSX.utils.aoa_to_sheet(datosExport);
    XLSX.utils.book_append_sheet(wb, ws, 'Estadística Educativa');
    
    // Descargar archivo
    XLSX.writeFile(wb, nombreArchivo);
}

/**
 * Exporta los datos a PDF
 */
function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Configurar cabecera
    doc.setFontSize(18);
    doc.setTextColor(0, 73, 144);
    doc.text('Estadística Educativa de Querétaro', 14, 20);
    
    // Preparar datos para la tabla
    const datosTabla = [];
    
    for (let i = 1; i < datosEducativos.length; i++) {
        datosTabla.push([
            datosEducativos[i][0],
            datosEducativos[i][1].toString(),
            datosEducativos[i][2].toString()
        ]);
    }
    
    // Calcular totales
    let totalEscuelas = 0;
    let totalAlumnos = 0;
    
    for (let i = 1; i < datosEducativos.length; i++) {
        totalEscuelas += datosEducativos[i][1];
        totalAlumnos += datosEducativos[i][2];
    }
    
    datosTabla.push(['Total', totalEscuelas.toString(), totalAlumnos.toString()]);
    
    // Generar tabla
    doc.autoTable({
        head: [['Tipo Educativo', 'Escuelas', 'Alumnos']],
        body: datosTabla,
        startY: 30,
        theme: 'grid',
        styles: {
            fontSize: 10,
            cellPadding: 3,
            lineColor: [0, 73, 144],
            lineWidth: 0.1
        },
        headStyles: {
            fillColor: [0, 73, 144],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        footStyles: {
            fillColor: [220, 220, 220],
            fontStyle: 'bold'
        },
        alternateRowStyles: {
            fillColor: [245, 247, 250]
        }
    });
    
    // Añadir pie de página
    const pageCount = doc.internal.getNumberOfPages();
    doc.setFontSize(8);
    doc.setTextColor(100, 100, 100);
    
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.text(`Secretaría de Educación del Estado de Querétaro - Página ${i} de ${pageCount}`, 14, doc.internal.pageSize.height - 10);
    }
    
    // Descargar archivo
    doc.save('Estadistica_Educativa_Queretaro.pdf');
}

/**
 * Alterna el modo oscuro
 */

// Event Listeners cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para los controles de visualización
    const radiosVisualizacion = document.querySelectorAll('input[name="visualizacion"]');
    radiosVisualizacion.forEach(radio => {
        radio.addEventListener('change', function() {
            tipoVisualizacion = this.value;
            const options = configurarOpciones();
            chart = crearGrafico(options);
        });
    });
      // Event listeners para tipo de gráfico
    const radiosTipoGrafico = document.querySelectorAll('input[name="tipo_grafico"]');
    radiosTipoGrafico.forEach(radio => {
        radio.addEventListener('change', function() {
            tipoGrafico = this.value;
            const options = configurarOpciones();
            chart = crearGrafico(options);
        });    });
    
    // Event listeners para exportación
    document.getElementById('exportExcel').addEventListener('click', exportarExcel);
    document.getElementById('exportPDF').addEventListener('click', exportarPDF);
    
    // Inicializar tooltips personalizados
    initCustomTooltips();
});

/**
 * Inicializa tooltips personalizados para los iconos de información
 */
function initCustomTooltips() {
    const infoIcons = document.querySelectorAll('.info-icon[data-tooltip]');
    
    // Crear el contenedor del tooltip solo una vez
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    document.body.appendChild(tooltip);
    
    // Agregar listeners a cada icono de información
    infoIcons.forEach(icon => {
        // Mostrar tooltip al pasar el mouse
        icon.addEventListener('mouseenter', function(e) {
            const tooltipText = this.getAttribute('data-tooltip');
            tooltip.textContent = tooltipText;
            
            // Posicionar el tooltip
            const iconRect = this.getBoundingClientRect();
            tooltip.style.left = `${iconRect.left}px`;
            tooltip.style.top = `${iconRect.bottom + 8}px`;
            
            // Asegurarse de que el tooltip no se salga de la ventana
            const tooltipRect = tooltip.getBoundingClientRect();
            if (tooltipRect.right > window.innerWidth) {
                tooltip.style.left = `${window.innerWidth - tooltipRect.width - 10}px`;
            }
            
            // Mostrar tooltip con animación
            tooltip.style.display = 'block';
            setTimeout(() => {
                tooltip.style.opacity = '1';
            }, 10);
        });
        
        // Ocultar tooltip al quitar el mouse
        icon.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
            setTimeout(() => {
                tooltip.style.display = 'none';
            }, 300);
        });
    });
}
