/**
 * EXPORT MANAGER - SISTEMA CENTRALIZADO (SINGLETON)
 * ================================================
 * 
 * Sistema simplificado para manejar exportaciones PNG y Excel
 * Implementado como singleton para fácil uso
 * 
 * @version 3.1 (Singleton)
 * @date 2025-06-11
 */

const ExportManager = {
    // Configuración actual
    config: {
        pageId: 'default',
        title: 'Gráfico',
        chartSelector: '#chart_div',
        dataCallback: null
    },
    
    // Estado interno
    isExporting: false,
    currentModal: null,
    
    /**
     * Configurar el ExportManager
     */
    configure(newConfig) {
        this.config = { ...this.config, ...newConfig };
        console.log(`✅ ExportManager configurado para: ${this.config.pageId}`);
        return this;
    },
      /**
     * Exportar como PNG con valores incluidos
     */
    async exportPNG() {
        if (this.isExporting) return;
        
        try {
            this.isExporting = true;
            console.log('📸 Iniciando exportación PNG con valores...');
            
            const chartElement = document.querySelector(this.config.chartSelector);
            if (!chartElement) {
                throw new Error(`No se encontró el elemento: ${this.config.chartSelector}`);
            }
            
            this.showMessage('Generando imagen con valores...', 'info');
            
            // Crear contenedor temporal con valores
            const enhancedElement = await this.createEnhancedChartElement(chartElement);
            
            // Usar html2canvas con el elemento mejorado
            if (typeof html2canvas !== 'undefined') {
                const canvas = await html2canvas(enhancedElement, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    width: enhancedElement.offsetWidth,
                    height: enhancedElement.offsetHeight
                });
                
                // Limpiar elemento temporal
                document.body.removeChild(enhancedElement);
                
                this.downloadCanvas(canvas, this.generateFileName('png'));
                this.showMessage('Imagen con valores descargada exitosamente', 'success');
            } else {
                throw new Error('html2canvas no está disponible');
            }
            
        } catch (error) {
            console.error('❌ Error en exportación PNG:', error);
            this.showMessage('Error al generar imagen: ' + error.message, 'error');
        } finally {
            this.isExporting = false;
        }
    },
    
    /**
     * Crear elemento de gráfico mejorado con valores incluidos
     */
    async createEnhancedChartElement(originalElement) {
        // Clonar el elemento original
        const clonedElement = originalElement.cloneNode(true);
        clonedElement.style.position = 'absolute';
        clonedElement.style.top = '-9999px';
        clonedElement.style.left = '-9999px';
        clonedElement.style.width = originalElement.offsetWidth + 'px';
        clonedElement.style.height = (originalElement.offsetHeight + 150) + 'px'; // Espacio extra para valores
        clonedElement.style.backgroundColor = '#ffffff';
        clonedElement.style.padding = '20px';
        clonedElement.style.boxSizing = 'border-box';
        
        // Agregar al DOM temporalmente
        document.body.appendChild(clonedElement);
        
        // Obtener datos para mostrar valores
        const data = this.config.dataCallback ? this.config.dataCallback() : null;
        
        if (data && Array.isArray(data) && data.length > 0) {
            // Crear tabla de valores
            const valuesTable = this.createValuesTable(data);
            clonedElement.appendChild(valuesTable);
        }
        
        // Agregar información de exportación
        const exportInfo = this.createExportInfo();
        clonedElement.appendChild(exportInfo);
        
        return clonedElement;
    },
    
    /**
     * Crear tabla de valores
     */
    createValuesTable(data) {
        const tableContainer = document.createElement('div');
        tableContainer.style.cssText = `
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            font-family: Arial, sans-serif;
        `;
        
        // Título de la tabla
        const title = document.createElement('h4');
        title.textContent = 'Valores del Gráfico';
        title.style.cssText = `
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 14px;
            font-weight: bold;
        `;
        tableContainer.appendChild(title);
        
        // Crear tabla
        const table = document.createElement('table');
        table.style.cssText = `
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            background: white;
        `;
        
        // Encontrar la fila de encabezados (primera fila con texto)
        let headerRowIndex = -1;
        for (let i = 0; i < data.length; i++) {
            if (Array.isArray(data[i]) && data[i].length > 1 && 
                typeof data[i][0] === 'string' && typeof data[i][1] === 'string') {
                headerRowIndex = i;
                break;
            }
        }
        
        // Procesar filas de datos
        let hasHeader = false;
        for (let i = Math.max(0, headerRowIndex); i < data.length && i < headerRowIndex + 10; i++) {
            if (!Array.isArray(data[i]) || data[i].length < 2) continue;
            
            const row = document.createElement('tr');
            
            data[i].forEach((cell, index) => {
                const cellElement = document.createElement(hasHeader ? 'td' : 'th');
                cellElement.textContent = cell;
                cellElement.style.cssText = `
                    padding: 6px 8px;
                    border: 1px solid #dee2e6;
                    text-align: ${index === 0 ? 'left' : 'center'};
                    ${hasHeader ? 'background: white;' : 'background: #2c3e50; color: white; font-weight: bold;'}
                `;
                row.appendChild(cellElement);
            });
            
            table.appendChild(row);
            hasHeader = true;
        }
        
        tableContainer.appendChild(table);
        return tableContainer;
    },
    
    /**
     * Crear información de exportación
     */
    createExportInfo() {
        const infoContainer = document.createElement('div');
        infoContainer.style.cssText = `
            margin-top: 15px;
            padding: 10px;
            background: #e3f2fd;
            border-radius: 6px;
            border-left: 4px solid #2196f3;
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #1565c0;
        `;
        
        const timestamp = new Date().toLocaleString('es-ES');
        infoContainer.innerHTML = `
            <strong>📊 ${this.config.title}</strong><br>
            📅 Exportado: ${timestamp}<br>
            🔧 Sistema: ExportManager v3.1
        `;
        
        return infoContainer;
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
        const existing = document.getElementById('export-message');
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
        messageDiv.id = 'export-message';
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
        
        // Agregar estilos de animación
        if (!document.getElementById('export-animations')) {
            const style = document.createElement('style');
            style.id = 'export-animations';
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
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            if (messageDiv && messageDiv.parentNode) {
                messageDiv.style.animation = 'slideInRight 0.3s ease-out reverse';
                setTimeout(() => {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 300);
            }
        }, 3000);
    },
    
    /**
     * Función de debug
     */
    debug() {
        console.log('🔍 Debug ExportManager:');
        console.log('- Configuración actual:', this.config);
        console.log('- Estado exportación:', this.isExporting);
        console.log('- Elemento gráfico:', document.querySelector(this.config.chartSelector) ? 'Encontrado' : 'No encontrado');
        console.log('- XLSX disponible:', typeof XLSX !== 'undefined');
        console.log('- html2canvas disponible:', typeof html2canvas !== 'undefined');
    }
};

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
    window.ExportManager = ExportManager;
}

console.log('✅ ExportManager (Singleton) cargado correctamente');
