/**
 * EXPORT MANAGER CON ANOTACIONES - SISTEMA CENTRALIZADO
 * ====================================================
 * 
 * Sistema avanzado para manejar exportaciones PNG con anotaciones y Excel
 * Implementado como singleton para fácil uso
 * Inspirado en exports-estudiantes-v2.js para mostrar valores sobre las barras
 * 
 * @version 4.0 (Con Anotaciones)
 * @date 2025-06-11
 */

const ExportManagerAnnotations = {
    // Configuración actual
    config: {
        pageId: 'default',
        title: 'Gráfico',
        chartSelector: '#chart_div',
        dataCallback: null,
        chartInstance: null,
        createAnnotatedChart: null, // Callback para crear gráfico con anotaciones
        getChartData: null,         // Callback para obtener datos del gráfico
        getChartOptions: null       // Callback para obtener opciones del gráfico
    },
    
    // Estado interno
    isExporting: false,
    currentModal: null,
    
    /**
     * Configurar el ExportManager
     */
    configure(newConfig) {
        this.config = { ...this.config, ...newConfig };
        console.log(`✅ ExportManager con Anotaciones configurado para: ${this.config.pageId}`);
        return this;
    },
    
    /**
     * Exportar como PNG con anotaciones sobre las barras
     */
    async exportPNG() {
        if (this.isExporting) return;
        
        try {
            this.isExporting = true;
            console.log('📸 Iniciando exportación PNG con anotaciones...');
            
            const chartElement = document.querySelector(this.config.chartSelector);
            if (!chartElement) {
                throw new Error(`No se encontró el elemento: ${this.config.chartSelector}`);
            }
            
            this.showMessage('Generando imagen con anotaciones...', 'info');
            
            // Crear gráfico temporal con anotaciones
            const annotatedElement = await this.createAnnotatedChartElement();
            
            if (!annotatedElement) {
                throw new Error('No se pudo crear el gráfico con anotaciones');
            }
            
            // Usar html2canvas con el elemento con anotaciones
            if (typeof html2canvas !== 'undefined') {
                const canvas = await html2canvas(annotatedElement, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    foreignObjectRendering: false
                });
                
                // Limpiar elemento temporal
                if (annotatedElement.parentNode) {
                    annotatedElement.parentNode.removeChild(annotatedElement);
                }
                
                // Restaurar gráfico original
                await this.restoreOriginalChart();
                
                this.downloadCanvas(canvas, this.generateFileName('png'));
                this.showMessage('Imagen con anotaciones descargada exitosamente', 'success');
            } else {
                throw new Error('html2canvas no está disponible');
            }
            
        } catch (error) {
            console.error('❌ Error en exportación PNG:', error);
            this.showMessage('Error al generar imagen: ' + error.message, 'error');
            
            // Asegurar restauración del gráfico original
            await this.restoreOriginalChart();
        } finally {
            this.isExporting = false;
        }
    },
    
    /**
     * Crear elemento de gráfico temporal con anotaciones
     */
    async createAnnotatedChartElement() {
        console.log('🎨 Creando gráfico temporal con anotaciones...');
        
        try {
            // Crear contenedor temporal invisible
            const tempContainer = document.createElement('div');
            tempContainer.id = 'temp-chart-annotations';
            tempContainer.style.cssText = `
                position: absolute;
                top: -9999px;
                left: -9999px;
                width: 800px;
                height: 500px;
                background: white;
                padding: 20px;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            `;
            
            // Agregar al DOM
            document.body.appendChild(tempContainer);
            
            // Obtener datos del gráfico
            let chartData = null;
            if (this.config.getChartData && typeof this.config.getChartData === 'function') {
                chartData = this.config.getChartData();
            }
            
            if (!chartData) {
                console.warn('⚠️ No se pudieron obtener datos del gráfico');
                return null;
            }
            
            // Agregar anotaciones a los datos
            const dataWithAnnotations = this.addAnnotationsToData(chartData);
            
            if (!dataWithAnnotations) {
                console.warn('⚠️ No se pudieron agregar anotaciones');
                return null;
            }
            
            // Obtener opciones optimizadas para anotaciones
            const annotationOptions = this.getAnnotationOptions();
              // Crear nuevo gráfico temporal con anotaciones
            if (typeof google !== 'undefined' && google.visualization) {
                const dataTable = google.visualization.arrayToDataTable(dataWithAnnotations);
                
                // Detectar tipo de gráfico basado en las opciones
                let ChartClass = google.visualization.ColumnChart; // Default
                
                if (this.config.getChartOptions) {
                    const options = this.config.getChartOptions();
                    // Si se detecta configuración de gráfico de barras horizontales
                    if (options && (options.orientation === 'horizontal' || 
                                  this.config.pageId === 'demo-ventas')) {
                        ChartClass = google.visualization.BarChart;
                    }
                }
                
                const chart = new ChartClass(tempContainer);
                
                // Dibujar gráfico con anotaciones
                chart.draw(dataTable, annotationOptions);
                
                // Esperar a que el gráfico se renderice
                await new Promise(resolve => setTimeout(resolve, 500));
                
                console.log('✅ Gráfico temporal con anotaciones creado');
                return tempContainer;
            }
            
        } catch (error) {
            console.error('❌ Error al crear gráfico con anotaciones:', error);
            return null;
        }
        
        return null;
    },
    
    /**
     * Agregar anotaciones a los datos del gráfico
     */
    addAnnotationsToData(originalData) {
        try {
            if (!originalData || !Array.isArray(originalData) || originalData.length < 2) {
                console.warn('⚠️ Datos insuficientes para agregar anotaciones');
                return null;
            }
            
            console.log('📝 Agregando anotaciones a los datos...');
            
            const dataWithAnnotations = [];
            const originalHeaders = originalData[0];
            const newHeaders = [];
            
            // Primer elemento es siempre la etiqueta
            newHeaders.push(originalHeaders[0]);
            
            // Para cada columna de datos, agregar columna de valor y anotación
            for (let i = 1; i < originalHeaders.length; i++) {
                const column = originalHeaders[i];
                if (column && column.toString().toLowerCase() !== 'total') {
                    newHeaders.push(column); // Valor de la columna
                    newHeaders.push({type: 'string', role: 'annotation'}); // Anotación
                }
            }
            
            dataWithAnnotations.push(newHeaders);
            
            // Procesar filas de datos
            for (let i = 1; i < originalData.length; i++) {
                const originalRow = originalData[i];
                const newRow = [];
                
                // Agregar etiqueta (primer elemento)
                newRow.push(originalRow[0]);
                
                // Procesar cada valor de datos
                for (let j = 1; j < originalRow.length; j++) {
                    const value = originalRow[j];
                    const column = originalHeaders[j];
                    
                    if (column && column.toString().toLowerCase() !== 'total') {
                        newRow.push(value); // Valor numérico
                        
                        // Agregar anotación con el valor (solo si > 0)
                        if (value && value > 0) {
                            newRow.push(value.toString());
                        } else {
                            newRow.push('');
                        }
                    }
                }
                
                dataWithAnnotations.push(newRow);
            }
            
            console.log('✅ Anotaciones agregadas correctamente');
            console.log('📊 Datos originales:', originalData.length, 'x', originalData[0].length);
            console.log('📊 Datos con anotaciones:', dataWithAnnotations.length, 'x', dataWithAnnotations[0].length);
            
            return dataWithAnnotations;
            
        } catch (error) {
            console.error('❌ Error al agregar anotaciones:', error);
            return null;
        }
    },
    
    /**
     * Obtener opciones optimizadas para mostrar anotaciones
     */
    getAnnotationOptions() {
        // Opciones base del gráfico
        let options = {
            title: this.config.title,
            titleTextStyle: {
                fontSize: 18,
                bold: true,
                color: '#333',
                fontName: 'Arial'
            },
            width: 760,
            height: 460,
            chartArea: {
                width: '85%',
                height: '70%',
                left: '10%',
                top: '15%'
            },
            legend: { position: 'none' },
            hAxis: {
                title: 'Categorías',
                gridlines: {color: '#f5f5f5'},
                textStyle: {
                    fontSize: 11,
                    color: '#555',
                    fontName: 'Arial'
                },
                slantedText: false,
                maxTextLines: 2,
                showTextEvery: 1,
                minTextSpacing: 0
            },
            vAxis: {
                title: 'Valores',
                format: '#,###',
                gridlines: {color: '#f5f5f5'},
                baselineColor: '#ddd',
                textStyle: {
                    fontSize: 12,
                    color: '#555',
                    fontName: 'Arial'
                },
                minValue: 0  // Asegurar que la escala siempre inicie en 0
            },
            bar: { groupWidth: '75%' },
            backgroundColor: {
                fill: '#ffffff',
                stroke: '#f5f5f5',
                strokeWidth: 1
            },
            // CONFIGURACIÓN ESPECÍFICA PARA ANOTACIONES
            annotations: {
                alwaysOutside: true,
                textStyle: {
                    fontSize: 11,
                    color: '#333',
                    fontName: 'Arial',
                    bold: true
                },
                stemColor: 'transparent', // Ocultar líneas de conexión
                stemLength: 0
            },
            // Optimizar para exportación
            forceIFrame: false,
            allowHtml: true,
            enableInteractivity: false
        };
        
        // Si hay callback personalizado para opciones, usarlo
        if (this.config.getChartOptions && typeof this.config.getChartOptions === 'function') {
            try {
                const customOptions = this.config.getChartOptions();
                options = { ...options, ...customOptions };
            } catch (error) {
                console.warn('⚠️ Error al obtener opciones personalizadas:', error);
            }
        }
        
        return options;
    },
    
    /**
     * Restaurar el gráfico original
     */
    async restoreOriginalChart() {
        try {
            console.log('🔄 Restaurando gráfico original...');
            
            // Si hay un callback personalizado para restaurar, usarlo
            if (this.config.restoreChart && typeof this.config.restoreChart === 'function') {
                this.config.restoreChart();
                return;
            }
            
            // Fallback: redibujar gráfico original si tenemos la instancia
            if (this.config.chartInstance && this.config.getChartData && this.config.getChartOptions) {
                const originalData = this.config.getChartData();
                const originalOptions = this.config.getChartOptions();
                
                if (originalData && originalOptions) {
                    const dataTable = google.visualization.arrayToDataTable(originalData);
                    this.config.chartInstance.draw(dataTable, originalOptions);
                }
            }
            
            console.log('✅ Gráfico original restaurado');
            
        } catch (error) {
            console.error('❌ Error al restaurar gráfico original:', error);
        }
    },
    
    /**
     * Exportar como Excel (método directo)
     */
    async exportExcel() {
        if (this.isExporting) return;
        
        try {
            this.isExporting = true;
            console.log('📊 Iniciando exportación Excel...');
            
            if (typeof XLSX === 'undefined') {
                throw new Error('Librería XLSX no está cargada');
            }
            
            if (!this.config.dataCallback) {
                throw new Error('No se definió dataCallback en la configuración');
            }
            
            this.showMessage('Generando archivo Excel...', 'info');
            
            const data = this.config.dataCallback();
            if (!data || !Array.isArray(data)) {
                throw new Error('Los datos para exportar no son válidos');
            }
            
            // Crear workbook
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Ajustar ancho de columnas
            this.applyExcelStyles(ws);
            
            XLSX.utils.book_append_sheet(wb, ws, 'Datos');
            
            // Descargar archivo
            const fileName = this.generateFileName('xlsx');
            XLSX.writeFile(wb, fileName);
            
            this.showMessage('Archivo Excel descargado exitosamente', 'success');
            
        } catch (error) {
            console.error('❌ Error en exportación Excel:', error);
            this.showMessage('Error al generar Excel: ' + error.message, 'error');
        } finally {
            this.isExporting = false;
        }
    },
    
    /**
     * Aplicar estilos básicos a Excel
     */
    applyExcelStyles(ws) {
        try {
            const range = XLSX.utils.decode_range(ws['!ref']);
            
            // Ajustar ancho de columnas
            const colWidths = [];
            for (let col = range.s.c; col <= range.e.c; col++) {
                let maxWidth = 10;
                for (let row = range.s.r; row <= range.e.r; row++) {
                    const cellAddress = XLSX.utils.encode_cell({r: row, c: col});
                    const cell = ws[cellAddress];
                    if (cell && cell.v) {
                        const width = cell.v.toString().length + 2;
                        maxWidth = Math.max(maxWidth, Math.min(width, 50));
                    }
                }
                colWidths.push({wch: maxWidth});
            }
            ws['!cols'] = colWidths;
            
        } catch (error) {
            console.warn('⚠️ No se pudieron aplicar estilos Excel:', error);
        }
    },
    
    /**
     * Descargar canvas como imagen
     */
    downloadCanvas(canvas, fileName) {
        try {
            canvas.toBlob((blob) => {
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }, 'image/png', 1.0);
            return true;
        } catch (error) {
            console.error('Error al descargar canvas:', error);
            return false;
        }
    },
    
    /**
     * Generar nombre de archivo
     */
    generateFileName(extension) {
        const now = new Date();
        const timestamp = now.toISOString().slice(0, 19).replace(/[T:]/g, '_');
        return `${this.config.title.replace(/\s+/g, '_')}_${this.config.pageId}_${timestamp}.${extension}`;
    },
    
    /**
     * Mostrar mensaje temporal
     */
    showMessage(message, type = 'info') {
        // Remover mensaje anterior si existe
        const existing = document.getElementById('export-message-annotations');
        if (existing) {
            existing.remove();
        }
        
        const colors = {
            success: '#27ae60',
            error: '#e74c3c',
            info: '#3498db',
            warning: '#f39c12'
        };
        
        const messageDiv = document.createElement('div');
        messageDiv.id = 'export-message-annotations';
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${colors[type] || colors.info};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 10001;
            font-weight: 500;
            animation: slideInRight 0.3s ease-out;
        `;
        messageDiv.textContent = message;
        
        // Agregar estilos de animación si no existen
        if (!document.getElementById('export-annotations-animations')) {
            const style = document.createElement('style');
            style.id = 'export-annotations-animations';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        opacity: 0;
                        transform: translateX(100px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(messageDiv);
        
        // Auto-remover después de 4 segundos
        setTimeout(() => {
            if (messageDiv && messageDiv.parentNode) {
                messageDiv.style.animation = 'slideInRight 0.3s ease-out reverse';
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 300);
            }
        }, 4000);
    },
    
    /**
     * Función de debug
     */
    debug() {
        console.log('🔍 Debug ExportManager con Anotaciones:');
        console.log('- Configuración actual:', this.config);
        console.log('- Estado exportación:', this.isExporting);
        console.log('- Elemento gráfico:', document.querySelector(this.config.chartSelector) ? 'Encontrado' : 'No encontrado');
        console.log('- Google Charts disponible:', typeof google !== 'undefined' && google.visualization);
        console.log('- XLSX disponible:', typeof XLSX !== 'undefined');
        console.log('- html2canvas disponible:', typeof html2canvas !== 'undefined');
        console.log('- Callbacks configurados:', {
            dataCallback: !!this.config.dataCallback,
            getChartData: !!this.config.getChartData,
            getChartOptions: !!this.config.getChartOptions,
            chartInstance: !!this.config.chartInstance
        });
    }
};

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
    window.ExportManagerAnnotations = ExportManagerAnnotations;
}

console.log('✅ ExportManager con Anotaciones (v4.0) cargado correctamente');
