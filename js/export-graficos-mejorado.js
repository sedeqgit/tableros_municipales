/**
 * Módulo de Exportación de Gráficos Mejorado - SEDEQ
 * Soluciona problemas específicos de exportación de Google Charts usando método nativo
 * 
 * CAMBIOS PRINCIPALES:
 * - Usa getImageURI() de Google Charts para evitar problemas SVG
 * - Integra función prepararGraficoParaExportacion()
 * - Fallback a html2canvas cuando sea necesario
 * - Soporte completo para gráficos individuales y todos los niveles
 */

/**
 * Función principal para exportar gráficos a PDF con método nativo de Google Charts
 * @param {string} chartElementId - ID del elemento que contiene el gráfico
 * @param {string} titulo - Título del documento PDF
 * @param {string} subtitulo - Subtítulo del documento PDF
 * @param {string} nombreArchivo - Nombre del archivo PDF a generar
 */
function exportarGraficoConMetodoNativo(chartElementId, titulo, subtitulo, nombreArchivo) {
    // Mostrar indicador de carga
    const exportButton = document.getElementById('export-pdf');
    const originalText = exportButton.innerHTML;
    exportButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    exportButton.disabled = true;
    
    // Verificar que existe la función de preparación
    if (typeof prepararGraficoParaExportacion !== 'function') {
        console.warn('Función prepararGraficoParaExportacion no encontrada, usando método estándar');
        exportarConHtml2CanvasFallback(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
        return;
    }
    
    // Preparar el gráfico para exportación
    prepararGraficoParaExportacion().then(() => {
        // Verificar que el gráfico global existe
        if (typeof chartMatricula === 'undefined' || !chartMatricula) {
            console.warn('Variable chartMatricula no encontrada, usando html2canvas');
            exportarConHtml2CanvasFallback(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
            return;
        }
        
        // Esperar a que el gráfico esté completamente renderizado
        google.visualization.events.addOneTimeListener(chartMatricula, 'ready', function() {
            try {
                // Usar el método nativo de Google Charts para obtener la imagen
                const imageURI = chartMatricula.getImageURI();
                
                if (!imageURI || !imageURI.startsWith('data:image/png;base64,')) {
                    console.warn('getImageURI() no devolvió una imagen válida, usando html2canvas');
                    exportarConHtml2CanvasFallback(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
                    return;
                }
                
                // Crear el PDF con la imagen obtenida
                crearPDFConImagenBase64(imageURI, titulo, subtitulo, nombreArchivo, exportButton, originalText);
                
                // Restaurar el gráfico a su estado normal
                if (typeof restaurarGraficoNormal === 'function') {
                    setTimeout(() => restaurarGraficoNormal(), 1000);
                }
                
            } catch (error) {
                console.error('Error al usar getImageURI():', error);
                exportarConHtml2CanvasFallback(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
            }
        });
          // Forzar re-renderizado para activar el evento 'ready'
        // Usar datos con anotaciones para exportación PDF
        const datos = obtenerDatosConAnotaciones();
        const dataTable = google.visualization.arrayToDataTable(datos);
        chartMatricula.draw(dataTable, chartMatricula.options);
        
    }).catch(error => {
        console.error('Error al preparar el gráfico:', error);
        exportarConHtml2CanvasFallback(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText);
    });
}

/**
 * Fallback: Exportar usando html2canvas cuando getImageURI() no funciona
 */
function exportarConHtml2CanvasFallback(chartElementId, titulo, subtitulo, nombreArchivo, exportButton, originalText) {
    const chartElement = document.getElementById(chartElementId);
    
    if (!chartElement) {
        console.error('No se encontró el elemento del gráfico:', chartElementId);
        restaurarBotonExport(exportButton, originalText);
        return;
    }
    
    // Preparar el gráfico con anotaciones también en el fallback
    prepararGraficoParaExportacion().then(() => {
        // Configurar opciones para html2canvas optimizadas
        const options = {
            backgroundColor: '#ffffff',
            scale: 2.5,
            logging: false,
            useCORS: true,
            allowTaint: true,
            height: chartElement.offsetHeight + 100,
            width: chartElement.offsetWidth + 80,
            x: -40,
            y: -50,
            scrollX: 0,
            scrollY: 0,
            windowWidth: chartElement.offsetWidth + 160,
            windowHeight: chartElement.offsetHeight + 200
        };
        
        // Capturar el gráfico como imagen
        html2canvas(chartElement, options).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            crearPDFConImagenBase64(imgData, titulo, subtitulo, nombreArchivo, exportButton, originalText);
            
            // Restaurar el gráfico si la función existe
            if (typeof restaurarGraficoNormal === 'function') {
                setTimeout(() => restaurarGraficoNormal(), 1000);
            }
        }).catch(error => {
            console.error('Error al capturar con html2canvas:', error);
            restaurarBotonExport(exportButton, originalText);
            mostrarMensajeError('Error al capturar el gráfico');
            
            // Restaurar el gráfico en caso de error
            if (typeof restaurarGraficoNormal === 'function') {
                restaurarGraficoNormal();
            }
        });
    }).catch(error => {
        console.error('Error al preparar gráfico para fallback:', error);
        restaurarBotonExport(exportButton, originalText);
        mostrarMensajeError('Error al preparar el gráfico');
    });
}

/**
 * Crea el PDF con la imagen en formato base64
 */
function crearPDFConImagenBase64(imgData, titulo, subtitulo, nombreArchivo, exportButton, originalText) {
    try {
        // Crear nueva instancia de jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'landscape',
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
        
        // Para getImageURI(), la imagen ya está en base64, para html2canvas también
        // Crear una imagen temporal para obtener dimensiones
        const img = new Image();
        img.onload = function() {
            // Calcular dimensiones para centrar la imagen
            const imgWidth = 250; // Ancho máximo en mm
            const imgHeight = (img.height * imgWidth) / img.width;
            
            // Posición centrada
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const x = (pageWidth - imgWidth) / 2;
            const y = 50; // Posición vertical después del título
            
            // Verificar si la imagen cabe en la página
            if (y + imgHeight > pageHeight - 20) {
                // Si no cabe, ajustar el tamaño
                const maxHeight = pageHeight - y - 20;
                const adjustedWidth = (img.width * maxHeight) / img.height;
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
        };
        
        img.onerror = function() {
            console.error('Error al cargar la imagen para el PDF');
            restaurarBotonExport(exportButton, originalText);
            mostrarMensajeError('Error al procesar la imagen del gráfico');
        };
        
        img.src = imgData;
        
    } catch (error) {
        console.error('Error al generar el PDF:', error);
        restaurarBotonExport(exportButton, originalText);
        mostrarMensajeError('Error al generar el PDF');
    }
}

/**
 * Función de compatibilidad para reemplazar la función original
 * Detecta automáticamente el mejor método de exportación
 */
function exportarGraficoPDF(chartElementId, titulo, subtitulo, nombreArchivo) {
    // Intentar usar el método nativo mejorado primero
    exportarGraficoConMetodoNativo(chartElementId, titulo, subtitulo, nombreArchivo);
}

/**
 * Función para exportar específicamente el gráfico de matrícula
 * Usa las configuraciones específicas del contexto de estudiantes
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
    
    // Exportar usando el método mejorado
    exportarGraficoConMetodoNativo('chart-matricula', titulo, subtitulo, nombreArchivo);
}

// Funciones auxiliares que deben existir en el contexto global
// (Se mantienen como referencias para evitar errores)

function restaurarBotonExport(button, originalText) {
    if (button) {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

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
    }, 4000);
}

/**
 * Función para mostrar el modal de exportación con múltiples opciones
 * @param {Function} onExportGrafico - Función para exportar gráfico
 * @param {Function} onExportTabla - Función para exportar tabla
 * @param {Function} onExportPNG - Función para exportar PNG
 * @param {Function} onExportCSV - Función para exportar CSV
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
                    </button>
                    <button id="export-tabla-btn" class="export-modal-button" style="
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
        if (onExportCSV) {
            onExportCSV();
        } else {
            console.warn('Función de exportación CSV no disponible');
        }
    });

    document.getElementById('export-cancel-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
    });

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('export-modal').addEventListener('click', (e) => {
        if (e.target.id === 'export-modal') {
            e.target.remove();
        }
    });

    // Cerrar modal con tecla ESC
    const handleEscKey = (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('export-modal');
            if (modal) {
                modal.remove();
                document.removeEventListener('keydown', handleEscKey);
            }
        }
    };
    document.addEventListener('keydown', handleEscKey);
}

// Log de inicialización
console.log('📊 Módulo de exportación mejorado cargado - Soporte nativo para Google Charts');
console.log('✅ Función mostrarModalExportacion disponible');
