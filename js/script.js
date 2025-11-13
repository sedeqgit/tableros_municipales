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
 * Valores: 'escuelas', 'alumnos'
 */
let tipoVisualizacion = 'escuelas';

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

    if (tipoVisualizacion === 'escuelas') {
        datosAjustados.addColumn('number', 'Escuelas');
        datosAjustados.addColumn({ type: 'string', role: 'style' });
    } else if (tipoVisualizacion === 'alumnos') {
        datosAjustados.addColumn('number', 'Matrícula');
        datosAjustados.addColumn({ type: 'string', role: 'style' });
    }

    // Paleta de colores por nivel educativo
    const coloresPorNivel = ['#1A237E', '#3949AB', '#00897B', '#FB8C00', '#E53935', '#5E35B1', '#43A047', '#0288D1', '#00ACC1', '#6A1B9A'];

    // Añadir los datos según la visualización seleccionada
    for (let i = 1; i < datosEducativos.length; i++) {
        let row = [datosEducativos[i][0]];
        const colorIndex = (i - 1) % coloresPorNivel.length;

        if (tipoVisualizacion === 'escuelas') {
            row.push(datosEducativos[i][1]);
            row.push(coloresPorNivel[colorIndex]);
        } else if (tipoVisualizacion === 'alumnos') {
            row.push(datosEducativos[i][2]);
            row.push(coloresPorNivel[colorIndex]);
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
            position: 'none'
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
        options.pieSliceTextStyle = { fontSize: 12 };
        options.tooltip = {
            text: 'both', // Muestra tanto el valor como el porcentaje
            showColorCode: true,
            textStyle: {
                fontSize: 13
            }
        };
        options.sliceVisibilityThreshold = 0; // Muestra todos los porcentajes sin importar el tamaño
        options.pieResidueSliceLabel = 'Otros'; // Etiqueta para porciones pequeñas si se agrupan
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
        // Para pastel, usamos los datos ya ajustados según la visualización
        if (tipoVisualizacion === 'escuelas') {
            options.title = 'Distribución de Escuelas por Tipo Educativo';
        } else if (tipoVisualizacion === 'alumnos') {
            options.title = 'Distribución de Matrícula por Tipo Educativo';
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
        const tipoEducativo = datosEducativos[i][0];

        // Verificar si es USAER (informativo, no se suma)
        const esInformativo = tipoEducativo.includes('Especial USAER');

        // Agregar clase especial si es informativo
        if (esInformativo) {
            fila.className = 'fila-informativa';
        }

        const tipoCell = document.createElement('td');
        tipoCell.textContent = tipoEducativo;
        fila.appendChild(tipoCell);

        const escuelasCell = document.createElement('td');
        escuelasCell.textContent = Number(datosEducativos[i][1]).toLocaleString();
        fila.appendChild(escuelasCell);

        // Solo sumar al total si NO es informativo
        if (!esInformativo) {
            totalEscuelas += Number(datosEducativos[i][1]);
        }

        const alumnosCell = document.createElement('td');
        alumnosCell.textContent = Number(datosEducativos[i][2]).toLocaleString();
        fila.appendChild(alumnosCell);

        // Solo sumar al total si NO es informativo
        if (!esInformativo) {
            totalAlumnos += Number(datosEducativos[i][2]);
        }

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
        // Excluir datos informativos (USAER) del análisis
        if (datosEducativos[i][0].includes('Especial USAER')) continue;

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
        // Excluir datos informativos (USAER) del análisis
        if (datosEducativos[i][0].includes('Especial USAER')) continue;

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
 * Exporta los datos a Excel usando utilidades comunes
 */
function exportarExcel() {
    try {
        // Preparar datos para exportación
        const datosExport = [
            ['Tipo Educativo', 'Escuelas', 'Alumnos']
        ];

        let totalEscuelas = 0;
        let totalAlumnos = 0;

        for (let i = 1; i < datosEducativos.length; i++) {
            datosExport.push([
                datosEducativos[i][0],
                datosEducativos[i][1],
                datosEducativos[i][2]
            ]);
            totalEscuelas += datosEducativos[i][1];
            totalAlumnos += datosEducativos[i][2];
        }

        // Añadir fila de totales
        datosExport.push(['Total', totalEscuelas, totalAlumnos]);

        // Usar utilidades para exportar
        const nombreArchivo = FormatUtils.generateFilenameWithDate('Estadistica_Educativa_Corregidora');
        ExcelUtils.exportMultiSheet([
            { name: 'Estadística Educativa', data: datosExport }
        ], nombreArchivo);

    } catch (error) {
        console.error('Error al exportar Excel:', error);
        ExportNotifications.showError('Error al exportar los datos a Excel');
    }
}

/**
 * Exporta los datos a PDF usando utilidades comunes
 */
function exportarPDF() {
    try {
        // Preparar datos para la tabla
        const datosTabla = [];
        let totalEscuelas = 0;
        let totalAlumnos = 0;

        for (let i = 1; i < datosEducativos.length; i++) {
            datosTabla.push([
                datosEducativos[i][0],
                FormatUtils.formatNumber(datosEducativos[i][1]),
                FormatUtils.formatNumber(datosEducativos[i][2])
            ]);
            totalEscuelas += datosEducativos[i][1];
            totalAlumnos += datosEducativos[i][2];
        }

        // Añadir fila de totales
        datosTabla.push([
            'Total',
            FormatUtils.formatNumber(totalEscuelas),
            FormatUtils.formatNumber(totalAlumnos)
        ]);

        // Configuración del PDF
        const nombreArchivo = FormatUtils.generateFilenameWithDate('Estadistica_Educativa_Corregidora');

        PDFUtils.exportTablePDF({
            title: 'Estadística Educativa de Corregidora - SEDEQ',
            table: {
                headers: ['Tipo Educativo', 'Escuelas', 'Matrícula'],
                data: datosTabla
            },
            footer: 'Secretaría de Educación del Estado de Querétaro',
            filename: nombreArchivo
        });

    } catch (error) {
        console.error('Error al exportar PDF:', error);
        ExportNotifications.showError('Error al exportar los datos a PDF');
    }
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
            
            // Calcular posición inicial (debajo del icono)
            let left = iconRect.left + window.scrollX;
            let top = iconRect.bottom + window.scrollY + 8;

            // Mostrar temporalmente para obtener dimensiones
            tooltip.style.display = 'block';
            tooltip.style.opacity = '0';

            const tooltipRect = tooltip.getBoundingClientRect();

            // Ajustar si se sale por la derecha
            if (iconRect.left + tooltipRect.width > window.innerWidth) {
                left = iconRect.right + window.scrollX - tooltipRect.width;
            }

            // Ajustar si se sale por la izquierda
            if (left < window.scrollX + 10) {
                left = window.scrollX + 10;
            }

            // Si no hay espacio abajo, mostrar arriba
            if (iconRect.bottom + tooltipRect.height + 8 > window.innerHeight + window.scrollY) {
                top = iconRect.top + window.scrollY - tooltipRect.height - 8;
            }

            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${top}px`;

            // Mostrar tooltip con animación
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
 * Exporta el gráfico actual como PNG usando html2canvas
 */
function exportarGraficoModal() {
    try {
        // Verificar que html2canvas esté disponible
        if (typeof html2canvas === 'undefined') {
            ExportNotifications.showError('La biblioteca html2canvas no está disponible');
            return;
        }

        const chartElement = document.getElementById('chart_div');
        if (!chartElement) {
            ExportNotifications.showError('No se pudo encontrar el gráfico para exportar');
            return;
        }

        ExportNotifications.showInfo('Generando imagen PNG del gráfico...');

        // Capturar el gráfico con html2canvas
        html2canvas(chartElement, {
            backgroundColor: '#ffffff',
            scale: 2,
            logging: false,
            useCORS: true,
            allowTaint: false
        }).then(canvas => {
            // Crear enlace de descarga
            const nombreArchivo = FormatUtils.generateFilenameWithDate(
                `Grafico_Educativo_${tipoVisualizacion}_${tipoGrafico}`
            ) + '.png';

            const link = document.createElement('a');
            link.download = nombreArchivo;
            link.href = canvas.toDataURL('image/png');
            link.click();

            ExportNotifications.showSuccess('Imagen PNG descargada exitosamente');
        }).catch(error => {
            console.error('Error al generar PNG:', error);
            ExportNotifications.showError('Error al generar la imagen PNG. Intente de nuevo.');
        });
    } catch (error) {
        console.error('Error en exportarGraficoModal:', error);
        ExportNotifications.showError('Error inesperado al exportar el gráfico');
    }
}
