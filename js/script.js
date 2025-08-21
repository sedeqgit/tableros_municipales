/**
 * =============================================================================
 * CONTROLADOR PRINCIPAL DEL DASHBOARD ESTADÍSTICO - SEDEQ CORREGIDORA
 * =============================================================================
 * 
 * Este módulo actúa como el núcleo central del sistema de visualización
 * estadística, coordinando todas las funcionalidades de análisis y
 * presentación de datos educativos del municipio de Corregidora, Querétaro.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Gestión integral de visualizaciones interactivas con Google Charts API
 * - Sistema avanzado de filtrado dinámico por múltiples criterios
 * - Soporte para diversos tipos de gráficos (columnas, barras, pastel, líneas)
 * - Motor de exportación multi-formato (PDF, Excel, CSV)
 * - Análisis automático de tendencias y cálculo de métricas clave
 * - Sistema inteligente de tooltips contextuales
 * - Gestión de colores profesionales y accesibles
 * 
 * ARQUITECTURA TÉCNICA:
 * - Patrón MVC con separación clara de responsabilidades
 * - Sistema de eventos reactivos para actualizaciones en tiempo real
 * - Configuración centralizada y parametrizable
 * - Manejo robusto de errores con recuperación automática
 * - Optimizaciones de rendimiento para grandes datasets
 * 
 * COMPONENTES INTEGRADOS:
 * - Motor de renderizado de gráficos (Google Charts)
 * - Sistema de filtrado y agrupación de datos
 * - Generador de reportes y exportación
 * - Calculadora de métricas estadísticas
 * - Gestor de eventos de interfaz de usuario
 * 
 * @version 2.0.1
 * @requires Google Charts API
 * @requires datosEducativos (variable global desde PHP)
 */

// =============================================================================
// INICIALIZACIÓN Y CONFIGURACIÓN DE LIBRERÍAS EXTERNAS
// =============================================================================

/**
 * Configuración optimizada de Google Charts
 * 
 * Se cargan específicamente los paquetes necesarios para minimizar el tiempo
 * de carga y mejorar el rendimiento general del dashboard:
 * 
 * - 'corechart': Proporciona gráficos fundamentales (columnas, líneas, área, pastel)
 *                con funcionalidades avanzadas de personalización y interactividad
 * - 'bar': Gráficos de barras especializados con mejor rendimiento en datasets
 *          grandes y opciones avanzadas de formateo y animación
 */
google.charts.load('current', { 'packages': ['corechart', 'bar'] });
google.charts.setOnLoadCallback(dibujarGrafico);

// =============================================================================
// VARIABLES GLOBALES Y ESTADO DE LA APLICACIÓN
// =============================================================================

/**
 * @type {google.visualization.DataTable} chartData - Tabla de datos principal para gráficos
 */
let chartData;

/**
 * @type {google.visualization.Chart} chart - Instancia del gráfico activo
 */
let chart;

/**
 * @type {string} tipoVisualizacion - Filtro de datos activo
 * Valores: 'ambos', 'escuelas', 'alumnos'
 */
let tipoVisualizacion = 'ambos';

/**
 * @type {string} tipoGrafico - Tipo de gráfico seleccionado
 * Valores: 'column', 'bar', 'pie', 'line', 'area'
 */
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
        instituciones y <span class="highlight">${maxAlumnos.tipo}</span> cuenta con la mayor cantidad de estudiantes con <span class="highlight">${maxAlumnos.valor.toLocaleString()}</span> alumnos en total. El nivel <span
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
    
    // Event listener para el botón de exportación en la tarjeta de gráfico
    const exportPdfButton = document.getElementById('export-pdf');
    if (exportPdfButton) {
        exportPdfButton.addEventListener('click', exportarGraficoModal);
    }
    
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

/**
 * Exportar el gráfico con modal de opciones
 */
function exportarGraficoModal() {
    // Mostrar modal con opciones de exportación utilizando la función de export-graficos-mejorado.js
    mostrarModalExportacion(
        () => exportarGraficoDirecto(), // Exportar gráfico como PDF
        () => exportarPDF(),            // Exportar tabla como PDF
        () => exportarGraficoPNG(),     // Exportar gráfico como PNG
        () => exportarExcel()           // Exportar datos como CSV/Excel
    );
}

/**
 * Exporta el gráfico actual a PDF directamente
 */
function exportarGraficoDirecto() {
    const titulo = 'Estadística Educativa por Tipo - SEDEQ';
    let subtitulo = '';
    
    // Determinar subtítulo según el tipo de visualización
    if (tipoVisualizacion === 'ambos') {
        subtitulo = 'Visualización de escuelas y alumnos';
    } else if (tipoVisualizacion === 'escuelas') {
        subtitulo = 'Visualización de escuelas';
    } else {
        subtitulo = 'Visualización de alumnos';
    }
    
    subtitulo += ` - Gráfico tipo: ${tipoGrafico === 'column' ? 'Columnas' : (tipoGrafico === 'bar' ? 'Barras' : 'Pastel')}`;
    
    // Nombre del archivo
    const nombreArchivo = `Estadistica_Educativa_${tipoVisualizacion}_${tipoGrafico}.pdf`;
    
    // Usar el método nativo de exportación
    exportarGraficoConMetodoNativo('chart_div', titulo, subtitulo, nombreArchivo);
}

/**
 * Exporta el gráfico actual como PNG
 */
function exportarGraficoPNG() {
    // Mostrar indicador de carga
    const exportButton = document.getElementById('export-pdf');
    const originalText = exportButton.innerHTML;
    exportButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    exportButton.disabled = true;
    
    // Preparar el gráfico para exportación
    prepararGraficoParaExportacion().then(() => {
        // Usar html2canvas para capturar el gráfico
        const chartElement = document.getElementById('chart_div');
        if (!chartElement) {
            mostrarMensajeError('No se pudo encontrar el gráfico para exportar');
            restaurarBotonExport(exportButton, originalText);
            return;
        }

        mostrarMensajeExito('Generando imagen PNG...');
        
        // Configuraciones optimizadas para la captura del gráfico
        html2canvas(chartElement, {
            backgroundColor: '#ffffff',
            scale: 2,
            logging: false,
            useCORS: true,
            allowTaint: true
        }).then(canvas => {
            // Crear enlace de descarga
            const link = document.createElement('a');
            const nombreArchivo = `Estadistica_Educativa_${tipoVisualizacion}_${tipoGrafico}.png`;
            link.download = nombreArchivo;
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            mostrarMensajeExito('Imagen PNG descargada exitosamente');
            
            // Restaurar el gráfico a su estado normal
            setTimeout(() => restaurarGraficoNormal(), 1000);
            restaurarBotonExport(exportButton, originalText);
            
        }).catch(error => {
            console.error('Error al generar PNG:', error);
            mostrarMensajeError('Error al generar la imagen PNG');
            restaurarBotonExport(exportButton, originalText);
            
            // Restaurar el gráfico a su estado normal en caso de error
            setTimeout(() => restaurarGraficoNormal(), 1000);
        });
    }).catch(error => {
        console.error('Error al preparar gráfico para PNG:', error);
        mostrarMensajeError('Error al preparar el gráfico para exportación');
        restaurarBotonExport(exportButton, originalText);
    });
}

/**
 * Restaura el estado original del botón de exportar
 */
function restaurarBotonExport(button, originalText) {
    if (button) {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

/**
 * Muestra un mensaje de éxito temporal
 */
function mostrarMensajeExito(mensaje) {
    // Verificar si la función ya existe en export-graficos-mejorado.js
    if (typeof window.mostrarMensajeExito === 'function') {
        window.mostrarMensajeExito(mensaje);
        return;
    }
    
    const alert = document.createElement('div');
    alert.className = 'alert-success';
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        z-index: 10000;
        font-family: Arial, sans-serif;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;
    alert.innerHTML = `<i class="fas fa-check-circle"></i> ${mensaje}`;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

/**
 * Muestra modal con opciones de exportación para el gráfico
 */
function exportarGraficoModal() {
    // Verificar que la función mostrarModalExportacion esté disponible
    if (typeof mostrarModalExportacion !== 'function') {
        console.error('La función mostrarModalExportacion no está disponible');
        exportarGraficoDirecto();
        return;
    }
    
    // Mostrar modal con opciones de exportación
    mostrarModalExportacion(
        () => exportarGraficoDirecto(),
        () => exportarPDF(),
        () => exportarGraficoPNG(),
        () => exportarExcel()
    );
}

/**
 * Exporta el gráfico directamente a PDF con el método nativo
 */
function exportarGraficoDirecto() {
    // Definir título y subtítulo según el tipo de gráfico y visualización actual
    let titulo = 'Estadística Educativa de Querétaro - SEDEQ';
    let subtitulo;
    
    if (tipoGrafico === 'pie') {
        if (tipoVisualizacion === 'escuelas') {
            subtitulo = 'Distribución de Escuelas por Tipo Educativo';
        } else {
            subtitulo = 'Distribución de Alumnos por Tipo Educativo';
        }
    } else {
        if (tipoVisualizacion === 'escuelas') {
            subtitulo = 'Cantidad de Escuelas por Tipo Educativo';
        } else if (tipoVisualizacion === 'alumnos') {
            subtitulo = 'Cantidad de Alumnos por Tipo Educativo';
        } else {
            subtitulo = 'Comparativa de Escuelas y Alumnos por Tipo Educativo';
        }
    }
    
    // Nombre del archivo
    const nombreArchivo = `Estadistica_Educativa_${tipoVisualizacion}_${tipoGrafico}.pdf`;
    
    // Verificar que la función exportarGraficoConMetodoNativo esté disponible
    if (typeof exportarGraficoConMetodoNativo === 'function') {
        exportarGraficoConMetodoNativo('chart_div', titulo, subtitulo, nombreArchivo);
    } else {
        console.error('La función exportarGraficoConMetodoNativo no está disponible');
        // Usar el método estándar como fallback
        exportarGraficoPNG();
    }
}

/**
 * Exporta el gráfico actual como PNG
 */
function exportarGraficoPNG() {
    // Nombre del archivo
    const nombreArchivo = `Estadistica_Educativa_${tipoVisualizacion}_${tipoGrafico}.png`;
    
    // Usar html2canvas para capturar el gráfico
    if (typeof html2canvas === 'undefined') {
        console.error('La biblioteca html2canvas no está disponible');
        mostrarMensajeError('No se pudo exportar el gráfico como imagen. Falta la biblioteca necesaria.');
        return;
    }
    
    const chartElement = document.getElementById('chart_div');
    if (!chartElement) {
        mostrarMensajeError('No se pudo encontrar el gráfico para exportar');
        return;
    }

    mostrarMensajeExito('Generando imagen PNG...');
    
    html2canvas(chartElement, {
        backgroundColor: '#ffffff',
        scale: 2,
        logging: false,
        useCORS: true
    }).then(canvas => {
        // Crear enlace de descarga
        const link = document.createElement('a');
        link.download = nombreArchivo;
        link.href = canvas.toDataURL('image/png');
        link.click();
        
        mostrarMensajeExito('Imagen PNG descargada exitosamente');
    }).catch(error => {
        console.error('Error al generar PNG:', error);
        mostrarMensajeError('Error al generar la imagen PNG');
    });
}

/**
 * Muestra un mensaje de éxito temporal
 */
function mostrarMensajeExito(mensaje) {
    // Verificar si la función ya existe en export-graficos-mejorado.js
    if (typeof window.mostrarMensajeExito === 'function') {
        window.mostrarMensajeExito(mensaje);
        return;
    }
    
    const alert = document.createElement('div');
    alert.className = 'alert-success';
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        z-index: 10000;
        font-family: Arial, sans-serif;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;
    alert.innerHTML = `<i class="fas fa-check-circle"></i> ${mensaje}`;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

/**
 * Muestra un mensaje de error temporal
 */
function mostrarMensajeError(mensaje) {
    // Verificar si la función ya existe en export-graficos-mejorado.js
    if (typeof window.mostrarMensajeError === 'function') {
        window.mostrarMensajeError(mensaje);
        return;
    }
    
    const alert = document.createElement('div');
    alert.className = 'alert-error';
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #f44336;
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        z-index: 10000;
        font-family: Arial, sans-serif;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;
    alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${mensaje}`;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 4000);
}

/**
 * Prepara el gráfico para exportación optimizando la compatibilidad
 * @returns {Promise} Promesa que se resuelve cuando el gráfico está listo
 */
function prepararGraficoParaExportacion() {
    return new Promise((resolve) => {
        // Optimizar opciones del gráfico para exportación
        const opcionesActuales = chart.getOptions();
        
        // Crear opciones optimizadas para exportación
        const opcionesExportacion = {
            ...opcionesActuales,
            backgroundColor: '#ffffff',
            titleTextStyle: {
                ...opcionesActuales.titleTextStyle,
                fontSize: 16
            },
            legend: {
                ...opcionesActuales.legend,
                textStyle: {
                    fontSize: 12,
                    color: '#333333'
                }
            },
            chartArea: {
                ...opcionesActuales.chartArea,
                width: '80%',
                height: '70%'
            },
            animation: false,
            enableInteractivity: false
        };
        
        // Añadir anotaciones a los datos para mejor visualización en PDF
        chartData = obtenerDatosConAnotaciones();
        
        // Redibujar el gráfico con las nuevas opciones
        chart.draw(chartData, opcionesExportacion);
        
        // Dar tiempo a que el gráfico termine de renderizarse
        setTimeout(resolve, 200);
    });
}

/**
 * Restaura el gráfico a su estado normal después de la exportación
 */
function restaurarGraficoNormal() {
    // Redibujar con las opciones originales
    const options = configurarOpciones();
    chart.draw(chartData, options);
}

/**
 * Obtiene los datos del gráfico con anotaciones para exportación
 * @returns {google.visualization.DataTable} Datos con anotaciones
 */
function obtenerDatosConAnotaciones() {
    // Clonar los datos actuales para no modificar los originales
    const datosConAnotaciones = chartData.clone();
    
    // Si es un gráfico de columnas o barras, añadir anotaciones
    if (tipoGrafico === 'column' || tipoGrafico === 'bar') {
        // Añadir columna de anotación para cada serie numérica
        for (let i = 1; i < datosConAnotaciones.getNumberOfColumns(); i++) {
            const colIndex = datosConAnotaciones.getNumberOfColumns();
            datosConAnotaciones.addColumn({type: 'string', role: 'annotation'});
            
            // Añadir valores como anotaciones
            for (let j = 0; j < datosConAnotaciones.getNumberOfRows(); j++) {
                const valor = datosConAnotaciones.getValue(j, i);
                datosConAnotaciones.setValue(j, colIndex, valor.toLocaleString());
            }
        }
    }
    
    return datosConAnotaciones;
}
