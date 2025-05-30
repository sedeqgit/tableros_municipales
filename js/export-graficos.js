/**
 * Módulo de Exportación de Gráficos a PDF
 * Sistema de Dashboard Estadístico - SEDEQ
 * 
 * Este archivo contiene todas las funciones relacionadas con la exportación
 * de gráficos dinámicos a formato PDF usando html2canvas y jsPDF.
 */

/**
 * Función global para exportar gráficos a PDF
 * Usa el método nativo de Google Charts getImageURI() para mejor compatibilidad
 * @param {string} chartElementId - ID del elemento que contiene el gráfico
 * @param {string} titulo - Título del documento PDF
 * @param {string} subtitulo - Subtítulo del documento PDF
 * @param {string} nombreArchivo - Nombre del archivo PDF a generar
 */
function exportarGraficoPDF(chartElementId, titulo, subtitulo, nombreArchivo) {
    // Mostrar indicador de carga
    const exportButton = document.getElementById('export-pdf');
    const originalText = exportButton.innerHTML;
    exportButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    exportButton.disabled = true;
    
    // Verificar que existe la función de preparación
    if (typeof prepararGraficoParaExportacion !== 'function') {
        console.error('Función prepararGraficoParaExportacion no encontrada');
        exportarConHtml2Canvas(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
        return;
    }
    
    // Preparar el gráfico para exportación
    prepararGraficoParaExportacion().then(() => {
        // Verificar que el gráfico global existe
        if (typeof chartMatricula === 'undefined' || !chartMatricula) {
            console.error('Variable chartMatricula no encontrada');
            exportarConHtml2Canvas(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
            return;
        }
        
        try {
            // Usar el método nativo de Google Charts para obtener la imagen
            const imageURI = chartMatricula.getImageURI();
            
            if (!imageURI || !imageURI.startsWith('data:image/png;base64,')) {
                console.error('No se pudo obtener la imagen del gráfico con getImageURI()');
                exportarConHtml2Canvas(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
                return;
            }
            
            // Crear el PDF con la imagen obtenida
            crearPDFConImagen(imageURI, titulo, subtitulo, nombreArchivo, exportButton, originalText);
            
        } catch (error) {
            console.error('Error al usar getImageURI():', error);
            exportarConHtml2Canvas(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
        }
    }).catch(error => {
        console.error('Error al preparar el gráfico:', error);
        exportarConHtml2Canvas(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
    });
}

/**
 * Fallback: Exportar usando html2canvas cuando getImageURI() no funciona
 */
function exportarConHtml2Canvas(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText) {
    const chartElement = document.getElementById(chartElementId);
    
    if (!chartElement) {
        console.error('No se encontró el elemento del gráfico:', chartElementId);
        restaurarBotonExport(exportButton, originalText);
        return;
    }
    
    // Configurar opciones para html2canvas optimizadas para texto horizontal
    const options = {
        backgroundColor: '#ffffff',
        scale: 2.5, // Buena calidad de imagen
        logging: false,
        useCORS: true,
        allowTaint: true,
        height: chartElement.offsetHeight + 100, // Más espacio para texto horizontal
        width: chartElement.offsetWidth + 80,    // Espacio lateral
        x: -40, // Offset para capturar más área
        y: -50, // Mayor offset vertical para texto horizontal
        scrollX: 0,
        scrollY: 0,
        windowWidth: chartElement.offsetWidth + 160,
        windowHeight: chartElement.offsetHeight + 200 // Más altura para el texto
    };
    
    // Capturar el gráfico como imagen
    html2canvas(chartElement, options).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        crearPDFConImagen(imgData, titulo, subtitulo, nombreArchivo, exportButton, originalText);
    }).catch(error => {
        console.error('Error al capturar con html2canvas:', error);
        restaurarBotonExport(exportButton, originalText);
        mostrarMensajeError('Error al capturar el gráfico');
    });
}

/**
 * Crea el PDF con la imagen proporcionada
 */
function crearPDFConImagen(imgData, titulo, subtitulo, nombreArchivo, exportButton, originalText) {
        try {
            // Crear nueva instancia de jsPDF
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'landscape', // Orientación horizontal para mejor visualización de gráficos
                unit: 'mm',
                format: 'a4'
            });
            
            // Configurar título del documento
            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text(titulo, doc.internal.pageSize.getWidth() / 2, 20, { align: 'center' });
            
            // Subtítulo
            doc.setFontSize(14);
            doc.setFont('helvetica', 'normal');
            doc.text(subtitulo, doc.internal.pageSize.getWidth() / 2, 30, { align: 'center' });
            
            // Fecha de generación
            doc.setFontSize(10);
            doc.setFont('helvetica', 'italic');
            const fechaActual = new Date().toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            doc.text(`Generado el: ${fechaActual}`, doc.internal.pageSize.getWidth() / 2, 40, { align: 'center' });
            
            // Convertir canvas a imagen
            const imgData = canvas.toDataURL('image/png');
            
            // Calcular dimensiones para centrar la imagen
            const imgWidth = 250; // Ancho máximo en mm
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            
            // Posición centrada
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const x = (pageWidth - imgWidth) / 2;
            const y = 50; // Posición vertical después del título
            
            // Verificar si la imagen cabe en la página
            if (y + imgHeight > pageHeight - 20) {
                // Si no cabe, ajustar el tamaño
                const maxHeight = pageHeight - y - 20;
                const adjustedWidth = (canvas.width * maxHeight) / canvas.height;
                const adjustedX = (pageWidth - adjustedWidth) / 2;
                doc.addImage(imgData, 'PNG', adjustedX, y, adjustedWidth, maxHeight);
            } else {
                // Agregar la imagen al PDF
                doc.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
            }
            
            // Agregar información adicional en el pie de página
            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');
            doc.text('Secretaría de Educación del Estado de Querétaro (SEDEQ)', 15, pageHeight - 10);
            doc.text(`Página 1 de 1`, pageWidth - 15, pageHeight - 10, { align: 'right' });
            
            // Guardar el archivo
            doc.save(nombreArchivo);
            
            // Restaurar el botón
            restaurarBotonExport(exportButton, originalText);
            
            // Mostrar mensaje de éxito
            mostrarMensajeExito('Gráfico exportado correctamente');
            
        } catch (error) {
            console.error('Error al generar el PDF:', error);
            restaurarBotonExport(exportButton, originalText);
            mostrarMensajeError('Error al generar el PDF');
        }
    }).catch(error => {
        console.error('Error al capturar el gráfico:', error);
        restaurarBotonExport(exportButton, originalText);
        mostrarMensajeError('Error al capturar el gráfico');
    });
}

/**
 * Restaura el estado original del botón de exportar
 * @param {HTMLElement} button - El botón a restaurar
 * @param {string} originalText - El texto original del botón
 */
function restaurarBotonExport(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
}

/**
 * Muestra un mensaje de éxito temporal
 * @param {string} mensaje - El mensaje a mostrar
 */
function mostrarMensajeExito(mensaje) {
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
 * @param {string} mensaje - El mensaje a mostrar
 */
function mostrarMensajeError(mensaje) {
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
    }, 5000);
}

/**
 * Crea y muestra el modal de selección de tipo de exportación
 * @param {Function} onExportGrafico - Callback para exportar gráfico
 * @param {Function} onExportTabla - Callback para exportar tabla
 * @param {Function} onExportPNG - Callback para exportar como PNG
 * @param {Function} onExportCSV - Callback para exportar como CSV
 */
function mostrarModalExportacion(onExportGrafico, onExportTabla, onExportPNG, onExportCSV) {
    const modalHTML = `
        <div id="export-modal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            font-family: Arial, sans-serif;
        ">
            <div style="
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                max-width: 400px;
                width: 90%;
            ">
                <h3 style="margin-top: 0; color: #333; text-align: center;">
                    <i class="fas fa-file-pdf" style="color: #e74c3c;"></i>
                    Exportar
                </h3>
                <p style="color: #666; text-align: center; margin-bottom: 25px;">
                    Selecciona el tipo de exportación que deseas:
                </p>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button id="export-grafico-btn" class="export-modal-button" style="
                        background: #3498db;
                        color: white;
                        border: none;
                        padding: 12px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-chart-bar"></i>
                        Exportar Gráfico Actual (PDF)
                    </button>
                    <button id="export-png-btn" class="export-modal-button" style="
                        background: #f39c12;
                        color: white;
                        border: none;
                        padding: 12px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-image"></i>
                        Exportar Gráfico como PNG
                    </button>                    <button id="export-tabla-btn" class="export-modal-button" style="
                        background: #27ae60;
                        color: white;
                        border: none;
                        padding: 12px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-file-pdf"></i>
                        Exportar Tabla como PDF
                    </button>
                    <button id="export-csv-btn" class="export-modal-button" style="
                        background: #2ecc71;
                        color: white;
                        border: none;
                        padding: 12px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-file-csv"></i>
                        Exportar Datos como CSV
                    </button>
                    <button id="export-cancel-btn" class="export-modal-button" style="
                        background: #95a5a6;
                        color: white;
                        border: none;
                        padding: 12px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Agregar el modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Agregar efectos hover a los botones
    const modalButtons = document.querySelectorAll('.export-modal-button');
    modalButtons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-2px)';
            button.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        });
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0)';
            button.style.boxShadow = 'none';
        });
    });
      // Configurar eventos de los botones
    document.getElementById('export-grafico-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
        onExportGrafico();
    });
    document.getElementById('export-png-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
        onExportPNG();
    });
    document.getElementById('export-tabla-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
        onExportTabla();
    });
    document.getElementById('export-csv-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
        if (onExportCSV) onExportCSV();
    });
    
    document.getElementById('export-cancel-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
    });
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('export-modal').addEventListener('click', (e) => {
        if (e.target.id === 'export-modal') {
            document.getElementById('export-modal').remove();
        }
    });
}

/**
 * Función específica para exportar gráficos de matrícula
 * Utiliza las variables globales del contexto de estudiantes
 * @param {string} añoSeleccionado - Año escolar seleccionado
 * @param {string} nivelSeleccionado - Nivel educativo seleccionado
 */
function exportarGraficoMatricula(añoSeleccionado, nivelSeleccionado) {
    // Determinar título y subtítulo según los filtros seleccionados
    let titulo = 'Gráfico de Matrícula Escolar - SEDEQ';
    let subtitulo;
    let nombreArchivo;
    
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        subtitulo = 'Todos los niveles educativos - Todos los años escolares';
        nombreArchivo = 'Grafico_Matricula_Completo.pdf';
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        subtitulo = `Todos los niveles educativos - Año escolar: ${añoSeleccionado}`;
        nombreArchivo = `Grafico_Matricula_${añoSeleccionado}.pdf`;
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        subtitulo = `Nivel: ${nivelSeleccionado} - Todos los años escolares`;
        nombreArchivo = `Grafico_Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}.pdf`;
    } else {
        subtitulo = `Nivel: ${nivelSeleccionado} - Año escolar: ${añoSeleccionado}`;
        nombreArchivo = `Grafico_Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_${añoSeleccionado}.pdf`;
    }
    
    // Llamar a la función global de exportación
    exportarGraficoPDF('chart-matricula', titulo, subtitulo, nombreArchivo);
}

/**
 * Exporta el gráfico actual a PNG
 * @param {string} chartElementId - ID del contenedor del gráfico
 * @param {string} nombreArchivo - Nombre del archivo PNG
 */
function exportarGraficoPNG(chartElementId, nombreArchivo) {
    const chartElement = document.getElementById(chartElementId);
    if (!chartElement) {
        mostrarMensajeError('No se encontró el gráfico para exportar');
        return;
    }
    
    // Configurar opciones optimizadas para PNG con texto horizontal
    const options = {
        backgroundColor: '#ffffff',
        scale: 3, // Mayor calidad para PNG
        logging: false,
        useCORS: true,
        allowTaint: true,
        height: chartElement.offsetHeight + 100,
        width: chartElement.offsetWidth + 80,
        x: -40,
        y: -50, // Mayor offset vertical para texto horizontal
        scrollX: 0,
        scrollY: 0,
        windowWidth: chartElement.offsetWidth + 160,
        windowHeight: chartElement.offsetHeight + 200 // Más altura para el texto
    };
    
    html2canvas(chartElement, options).then(canvas => {
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png', 1.0);
        link.download = nombreArchivo;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        mostrarMensajeExito('Gráfico exportado como imagen PNG');
    }).catch(error => {
        console.error('Error al exportar PNG:', error);
        mostrarMensajeError('Error al exportar el gráfico como imagen');
    });
}

/**
 * Exporta datos a CSV
 * @param {Array} datos - Array de datos para exportar
 * @param {string} nombreArchivo - Nombre del archivo CSV
 * @param {Array} headers - Headers para las columnas
 */
function exportarDatosCSV(datos, nombreArchivo, headers = null) {
    try {
        let csvContent = '';
        
        // Si se proporcionan headers, agregarlos
        if (headers && headers.length > 0) {
            csvContent += headers.join(',') + '\n';
        }
        
        // Procesar los datos
        if (datos && datos.length > 0) {
            datos.forEach(fila => {
                if (Array.isArray(fila)) {
                    // Si la fila es un array, unir con comas
                    csvContent += fila.map(valor => {
                        // Escapar comillas y manejar valores con comas
                        if (typeof valor === 'string' && (valor.includes(',') || valor.includes('"'))) {
                            return '"' + valor.replace(/"/g, '""') + '"';
                        }
                        return valor;
                    }).join(',') + '\n';
                } else if (typeof fila === 'object') {
                    // Si la fila es un objeto, extraer valores
                    const valores = Object.values(fila);
                    csvContent += valores.map(valor => {
                        if (typeof valor === 'string' && (valor.includes(',') || valor.includes('"'))) {
                            return '"' + valor.replace(/"/g, '""') + '"';
                        }
                        return valor;
                    }).join(',') + '\n';
                }
            });
        }
        
        // Crear blob y descargar
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', nombreArchivo);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        mostrarMensajeExito('Datos exportados a CSV correctamente');
    } catch (error) {
        console.error('Error al exportar CSV:', error);
        mostrarMensajeError('Error al exportar datos a CSV');
    }
}

/**
 * Exporta los datos de matrícula actual a CSV
 * @param {Array} datosMatriculaAgrupados - Datos del gráfico de matrícula
 * @param {string} añoSeleccionado - Año seleccionado
 * @param {string} nivelSeleccionado - Nivel seleccionado
 */
function exportarMatriculaCSV(datosMatriculaAgrupados, añoSeleccionado, nivelSeleccionado) {
    try {
        // Preparar nombre del archivo
        let nombreArchivo;
        if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
            nombreArchivo = 'Datos_Matricula_Completo.csv';
        } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
            nombreArchivo = `Datos_Matricula_${añoSeleccionado}.csv`;
        } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
            nombreArchivo = `Datos_Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}.csv`;
        } else {
            nombreArchivo = `Datos_Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_${añoSeleccionado}.csv`;
        }
        
        // Si no hay datos, mostrar error
        if (!datosMatriculaAgrupados || datosMatriculaAgrupados.length <= 1) {
            mostrarMensajeError('No hay datos disponibles para exportar');
            return;
        }
        
        // El primer elemento contiene los headers, el resto son los datos
        const headers = datosMatriculaAgrupados[0];
        const datos = datosMatriculaAgrupados.slice(1);
        
        // Exportar a CSV
        exportarDatosCSV(datos, nombreArchivo, headers);
        
    } catch (error) {
        console.error('Error al procesar datos para CSV:', error);
        mostrarMensajeError('Error al procesar los datos para exportación');
    }
}

// Exportar funciones para uso global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        exportarGraficoPDF,
        mostrarModalExportacion,
        exportarGraficoMatricula,
        exportarGraficoPNG,
        exportarDatosCSV,
        exportarMatriculaCSV,
        restaurarBotonExport,
        mostrarMensajeExito,
        mostrarMensajeError
    };
}
