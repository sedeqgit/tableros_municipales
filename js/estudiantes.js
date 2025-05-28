/**
 * Archivo JavaScript para la página de estudiantes
 * Sistema de Dashboard Estadístico - SEDEQ
 */

// Cargar la biblioteca de Google Charts
google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(inicializarGraficos);

// Variables globales para los datos y gráficos
let datosMatriculaAgrupados;
let chartMatricula;
let añoSeleccionado = 'todos';
let nivelSeleccionado = 'todos';

/**
 * Inicializa los gráficos una vez que Google Charts esté cargado
 */
function inicializarGraficos() {
    // Preparar los datos desde PHP
    prepararDatosMatricula();
    
    // Inicializar el gráfico
    chartMatricula = new google.visualization.ColumnChart(document.getElementById('chart-matricula'));
    
    // Mostrar gráfico de barras por defecto
    actualizarVisualizacion();
    
    // Configurar los selectores de año
    document.querySelectorAll('.year-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.year-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            añoSeleccionado = this.getAttribute('data-year');
            actualizarVisualizacion();
        });
    });
    
    // Configurar los selectores de nivel
    document.querySelectorAll('.level-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.level-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            nivelSeleccionado = this.getAttribute('data-level');
            actualizarVisualizacion();
        });
    });
    
    // Botones de exportación
    document.getElementById('export-excel').addEventListener('click', exportarExcel);
    document.getElementById('export-pdf').addEventListener('click', exportarPDF);
}

/**
 * Prepara los datos de matrícula desde el formato PHP al formato requerido por Google Charts
 */
function prepararDatosMatricula() {
    datosMatriculaAgrupados = {
        'todos': prepararDatosTodos(),
        'anual': {},
        'nivel': {}
    };
    
    // Preparar datos por año
    for (const año in datosMatricula) {
        datosMatriculaAgrupados['anual'][año] = prepararDatosAño(año);
    }
    
    // Preparar datos por nivel
    const niveles = ['Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior'];
    niveles.forEach(nivel => {
        datosMatriculaAgrupados['nivel'][nivel] = prepararDatosNivel(nivel);
    });
}

/**
 * Prepara los datos para la visualización general (todos los años)
 */
function prepararDatosTodos() {
    // Crear encabezados de la tabla
    const data = [['Año Escolar', 'Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior', 'Total']];
    
    // Para cada año, agregar una fila con los datos de cada nivel
    for (const año in datosMatricula) {
        const fila = [año];
        let total = 0;
        
        // Para cada nivel, agregar el dato o 0 si no existe
        const niveles = ['Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior'];
        niveles.forEach(nivel => {
            const valor = datosMatricula[año][nivel] || 0;
            fila.push(valor);
            total += valor;
        });
        
        // Agregar el total por año
        fila.push(total);
        
        data.push(fila);
    }
    
    return data;
}

/**
 * Prepara los datos para un año específico
 */
function prepararDatosAño(año) {
    // Crear encabezados de la tabla
    const data = [['Nivel Educativo', 'Cantidad de Alumnos']];
    
    // Para cada nivel, agregar una fila
    const niveles = ['Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior'];
    niveles.forEach(nivel => {
        const valor = datosMatricula[año][nivel] || 0;
        data.push([nivel, valor]);
    });
    
    return data;
}

/**
 * Prepara los datos para un nivel específico a lo largo de los años
 */
function prepararDatosNivel(nivel) {
    // Crear encabezados de la tabla
    const data = [['Año Escolar', 'Cantidad de Alumnos']];
    
    // Para cada año, agregar el dato del nivel
    for (const año in datosMatricula) {
        const valor = datosMatricula[año][nivel] || 0;
        data.push([año, valor]);
    }
    
    return data;
}

/**
 * Actualiza la visualización según las opciones seleccionadas
 */
function actualizarVisualizacion() {
    let datos;
    let titulo = '';
    
    // Determinar qué conjunto de datos usar según los filtros seleccionados
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        // Todos los años, todos los niveles
        datos = datosMatriculaAgrupados.todos;
        titulo = 'Matrícula por Nivel Educativo y Año Escolar';
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        // Un año específico, todos los niveles
        datos = datosMatriculaAgrupados.anual[añoSeleccionado];
        titulo = 'Matrícula por Nivel Educativo - ' + añoSeleccionado;
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        // Todos los años, un nivel específico
        datos = datosMatriculaAgrupados.nivel[nivelSeleccionado];
        titulo = 'Evolución de Matrícula en ' + nivelSeleccionado + ' (2018-2024)';
    } else {
        // Un año específico, un nivel específico - mostramos solo ese dato puntual
        datos = [['Nivel Educativo', 'Cantidad de Alumnos']];
        const valorNivel = datosMatricula[añoSeleccionado][nivelSeleccionado] || 0;
        datos.push([nivelSeleccionado, valorNivel]);
        titulo = 'Matrícula en ' + nivelSeleccionado + ' - ' + añoSeleccionado;
    }
    
    // Convertir los datos a un objeto DataTable de Google
    const dataTable = google.visualization.arrayToDataTable(datos);
      // Opciones para el gráfico
    let opciones = {
        title: titulo,
        titleTextStyle: {
            fontSize: 18,
            bold: true,
            color: '#333',
            fontName: 'Arial'
        },
        height: 450,
        chartArea: {
            width: '85%',
            height: '70%',
            left: '10%',
            top: '10%'
        },
        animation: {
            duration: 1200,
            easing: 'out',
            startup: true
        },
        legend: { position: 'none' },
        colors: getColoresGrafica(),
        hAxis: {
            title: getEtiquetaEjeX(),
            gridlines: {color: '#f5f5f5'},
            textStyle: {
                fontSize: 12,
                color: '#555',
                fontName: 'Arial'
            },
            slantedText: nivelSeleccionado !== 'todos',
            slantedTextAngle: 45
        },
        vAxis: {
            title: 'Cantidad de Alumnos',
            format: '#,###',
            gridlines: {color: '#f5f5f5'},
            baselineColor: '#ddd',
            textStyle: {
                fontSize: 12,
                color: '#555',
                fontName: 'Arial'
            }
        },
        bar: { groupWidth: '75%' },
        tooltip: { 
            showColorCode: true, 
            textStyle: {
                fontName: 'Arial',
                fontSize: 13
            }
        },
        focusTarget: 'category',
        backgroundColor: {
            fill: '#ffffff',
            stroke: '#f5f5f5',
            strokeWidth: 1
        }
    };
    
    // Dibujar el gráfico
    chartMatricula.draw(dataTable, opciones);
      // Actualizar la tabla de datos
    actualizarTablaDeMatricula(datos);
    
    // Actualizar visibilidad de la leyenda de colores
    actualizarVisibilidadLeyenda();
}

/**
 * Obtiene los colores para el gráfico según los filtros actuales
 */
function getColoresGrafica() {
    // Colores profesionales más armónicos y con mejor contraste
    const coloresBase = {
        'Inicial NE': '#3949AB',      // Azul más profundo
        'CAM': '#00897B',             // Verde azulado más saturado
        'Preescolar': '#FB8C00',      // Naranja más cálido
        'Primaria': '#E53935',        // Rojo más profesional
        'Secundaria': '#5E35B1',      // Púrpura más elegante
        'Media superior': '#43A047',  // Verde más claro y elegante
        'Superior': '#0288D1',        // Azul claro más profesional
        'Total': '#546E7A'            // Gris azulado más elegante
    };
    
    // Cuando se filtra por nivel, usar un degradado de tonos del mismo color base
    if (nivelSeleccionado !== 'todos') {
        // Si estamos viendo la evolución por años de un solo nivel
        if (añoSeleccionado === 'todos') {
            // Crear un degradado del color base para mostrar evolución temporal
            const colorBase = coloresBase[nivelSeleccionado];
            return generarDegradadoColor(colorBase, 6);
        } else {
            // Para un único dato de un nivel y año específico
            return [coloresBase[nivelSeleccionado]];
        }
    }
    
    // Array de colores para todos los niveles (cuando no hay filtro de nivel)
    return [
        coloresBase['Inicial NE'],
        coloresBase['CAM'],
        coloresBase['Preescolar'],
        coloresBase['Primaria'],
        coloresBase['Secundaria'],
        coloresBase['Media superior'],
        coloresBase['Superior'],
        coloresBase['Total']
    ];
}

/**
 * Genera un degradado de colores basado en un color base
 * @param {string} colorBase - El color base en formato hexadecimal
 * @param {number} cantidad - La cantidad de colores a generar
 * @returns {array} - Array de colores en degradado
 */
function generarDegradadoColor(colorBase, cantidad) {
    const resultado = [];
    
    // Convertir el color hexadecimal a RGB
    const r = parseInt(colorBase.substring(1, 3), 16);
    const g = parseInt(colorBase.substring(3, 5), 16);
    const b = parseInt(colorBase.substring(5, 7), 16);
    
    // Crear un degradado del color base, variando la luminosidad
    for (let i = 0; i < cantidad; i++) {
        // Calcular el factor de ajuste para hacer un degradado
        const factor = 0.8 + (i * 0.05);
        
        // Aplicar el factor a los componentes RGB, manteniendo el tono
        let nuevoR = Math.min(255, Math.floor(r * factor));
        let nuevoG = Math.min(255, Math.floor(g * factor));
        let nuevoB = Math.min(255, Math.floor(b * factor));
        
        // Convertir de vuelta a hexadecimal
        const nuevoColor = '#' + 
            nuevoR.toString(16).padStart(2, '0') + 
            nuevoG.toString(16).padStart(2, '0') + 
            nuevoB.toString(16).padStart(2, '0');
            
        resultado.push(nuevoColor);
    }
    
    return resultado;
}

/**
 * Obtiene la etiqueta para el eje X según los filtros actuales
 */
function getEtiquetaEjeX() {
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        return 'Año Escolar';
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        return 'Nivel Educativo';
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        return 'Año Escolar';
    } else {
        return 'Nivel Educativo';
    }
}

/**
 * Actualiza la tabla de datos debajo del gráfico
 */
function actualizarTablaDeMatricula(datos) {
    const tabla = document.getElementById('tabla-matricula');
    
    // Limpiar la tabla existente
    tabla.innerHTML = '';
    
    // Crear encabezado
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    
    datos[0].forEach(col => {
        const th = document.createElement('th');
        th.textContent = col;
        headerRow.appendChild(th);
    });
    
    thead.appendChild(headerRow);
    tabla.appendChild(thead);
    
    // Crear cuerpo de la tabla
    const tbody = document.createElement('tbody');
    
    for (let i = 1; i < datos.length; i++) {
        const row = document.createElement('tr');
        
        datos[i].forEach(val => {
            const td = document.createElement('td');
            
            // Formatear números con separadores de miles
            if (typeof val === 'number') {
                td.textContent = val.toLocaleString();
            } else {
                td.textContent = val;
            }
            
            row.appendChild(td);
        });
        
        tbody.appendChild(row);
    }
    
    tabla.appendChild(tbody);
}

/**
 * Exportar los datos a Excel
 */
function exportarExcel() {
    // Determinar qué datos exportar según los filtros seleccionados
    let datos;
    let nombreArchivo;
    
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        datos = datosMatriculaAgrupados.todos;
        nombreArchivo = 'Matricula_Todos_Los_Años.xlsx';
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        datos = datosMatriculaAgrupados.anual[añoSeleccionado];
        nombreArchivo = `Matricula_${añoSeleccionado}.xlsx`;
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        datos = datosMatriculaAgrupados.nivel[nivelSeleccionado];
        nombreArchivo = `Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_Todos_Los_Años.xlsx`;
    } else {
        // Un año y un nivel específicos
        datos = [['Nivel Educativo', 'Cantidad de Alumnos']];
        const valorNivel = datosMatricula[añoSeleccionado][nivelSeleccionado] || 0;
        datos.push([nivelSeleccionado, valorNivel]);
        nombreArchivo = `Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_${añoSeleccionado}.xlsx`;
    }
    
    // Crear un libro de Excel
    const wb = XLSX.utils.book_new();
    
    // Crear una hoja de datos
    const ws = XLSX.utils.aoa_to_sheet(datos);
    
    // Añadir la hoja al libro
    XLSX.utils.book_append_sheet(wb, ws, "Matrícula Escolar");
    
    // Generar el archivo y descargarlo
    XLSX.writeFile(wb, nombreArchivo);
}

/**
 * Exportar los datos a PDF
 */
function exportarPDF() {
    // Inicializar jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Determinar qué datos exportar según los filtros seleccionados
    let datos;
    let subtitulo;
    let nombreArchivo;
    
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        datos = datosMatriculaAgrupados.todos;
        subtitulo = 'Todos los niveles - Todos los años escolares';
        nombreArchivo = 'Matricula_Todos_Los_Años.pdf';
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        datos = datosMatriculaAgrupados.anual[añoSeleccionado];
        subtitulo = `Todos los niveles - Año escolar: ${añoSeleccionado}`;
        nombreArchivo = `Matricula_${añoSeleccionado}.pdf`;
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        datos = datosMatriculaAgrupados.nivel[nivelSeleccionado];
        subtitulo = `Nivel: ${nivelSeleccionado} - Todos los años escolares`;
        nombreArchivo = `Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_Todos_Los_Años.pdf`;
    } else {
        // Un año y un nivel específicos
        datos = [['Nivel Educativo', 'Cantidad de Alumnos']];
        const valorNivel = datosMatricula[añoSeleccionado][nivelSeleccionado] || 0;
        datos.push([nivelSeleccionado, valorNivel]);
        subtitulo = `Nivel: ${nivelSeleccionado} - Año escolar: ${añoSeleccionado}`;
        nombreArchivo = `Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_${añoSeleccionado}.pdf`;
    }
    
    // Configurar título del documento
    doc.setFontSize(18);
    doc.text('Matrícula Escolar - SEDEQ', 105, 15, { align: 'center' });
    
    // Subtítulo
    doc.setFontSize(14);
    doc.text(subtitulo, 105, 25, { align: 'center' });
    
    // Fecha de generación
    doc.setFontSize(10);
    doc.text(`Generado el: ${new Date().toLocaleDateString()}`, 105, 32, { align: 'center' });
    
    // Convertir datos para autoTable
    const cabeceras = datos[0];
    const cuerpo = datos.slice(1);
    
    // Crear tabla
    doc.autoTable({
        head: [cabeceras],
        body: cuerpo,
        startY: 40,
        styles: {
            fontSize: 10,
            cellPadding: 3,
            lineColor: [0, 0, 0],
            lineWidth: 0.1
        },
        headStyles: {
            fillColor: [0, 102, 102],
            textColor: 255
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        }
    });
    
    // Generar el archivo y descargarlo
    doc.save(nombreArchivo);
}

/**
 * Actualiza la visibilidad de la leyenda de colores según los filtros seleccionados
 */
function actualizarVisibilidadLeyenda() {
    const leyenda = document.querySelector('.chart-legend');
    const items = document.querySelectorAll('.legend-item');
    
    // Si es un único nivel y un único año, ocultar la leyenda
    if (nivelSeleccionado !== 'todos' && añoSeleccionado !== 'todos') {
        leyenda.style.display = 'none';
        return;
    }
    
    // Mostramos la leyenda
    leyenda.style.display = 'flex';
    
    // Si es un solo nivel con todos los años, mostramos solo ese nivel
    if (nivelSeleccionado !== 'todos' && añoSeleccionado === 'todos') {
        items.forEach(item => {
            const textoNivel = item.querySelector('span').textContent;
            if (textoNivel === nivelSeleccionado) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
        return;
    }
    
    // Si son todos los niveles, mostramos todos
    items.forEach(item => {
        item.style.display = 'flex';
    });
}

/**
 * Función que se ejecuta cuando la ventana se ha cargado completamente
 */
window.addEventListener('load', function() {
    // Animar la aparición del contenido
    const paneles = document.querySelectorAll('.matricula-panel');
    paneles.forEach(panel => {
        panel.classList.add('animate-fade');
    });
    
    // Activa las animaciones globales si existen
    if (typeof activarAnimacionesGlobales === 'function') {
        activarAnimacionesGlobales();
    }
    
    // Activar clase para animar el crecimiento del gráfico
    setTimeout(() => {
        document.getElementById('chart-matricula-container').classList.add('chart-grow');
    }, 300);
});
