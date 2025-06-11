/**
 * EXPORT MANAGER - SISTEMA CENTRALIZADO DE EXPORTACIONES (SINGLETON)
 * ================================================================
 * 
 * Sistema modular para manejar exportaciones de gráficos en todo el proyecto.
 * Implementado como singleton para facilitar su uso con métodos estáticos.
 * 
 * @author GitHub Copilot
 * @version 3.1 (Singleton)
 * @date 2025-06-11
 */

const ExportManager = {
    // Configuración actual
    config: {
        pageId: 'default',
        title: 'Gráfico',
        chartSelector: '#chart',
        dataCallback: null,
        customStyles: {
            excel: {
                headerStyle: {
                    fill: { fgColor: { rgb: "FF2C3E50" } },
                    font: { color: { rgb: "FFFFFFFF" }, bold: true }
                },
                dataStyle: {
                    alignment: { horizontal: "center" }
                }
            }
        }
    },
    
    // Estado interno
    isExporting: false,
    currentModal: null,
    
    /**
     * Configurar el ExportManager para una página específica
     */
    configure(newConfig) {
        this.config = { ...this.config, ...newConfig };
        console.log(`✅ ExportManager configurado para: ${this.config.pageId}`);
        return this;
    },
    
    /**
     * Mostrar modal de exportación
     */
    showExportModal() {
        if (this.currentModal) {
            this.closeModal();
        }
        
        const modalHTML = `
            <div id="export-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                backdrop-filter: blur(5px);
            ">
                <div style="
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                    max-width: 500px;
                    width: 90%;
                    animation: modalSlideIn 0.3s ease-out;
                ">
                    <h3 style="
                        margin-bottom: 30px;
                        color: #2c3e50;
                        font-size: 24px;
                        font-weight: bold;
                    ">
                        <i class="fas fa-download"></i>
                        Opciones de Exportación
                    </h3>
                    <p style="
                        margin-bottom: 30px;
                        color: #7f8c8d;
                        font-size: 16px;
                    ">
                        Selecciona el formato de exportación:
                    </p>
                    <div style="
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 15px;
                        margin-bottom: 20px;
                    ">
                        <button id="export-png-btn" style="
                            background: #3498db;
                            color: white;
                            border: none;
                            padding: 15px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            transition: all 0.3s ease;
                            font-weight: 500;
                        ">
                            <i class="fas fa-image"></i>
                            Gráfico PNG
                        </button>
                        <button id="export-excel-btn" style="
                            background: #27ae60;
                            color: white;
                            border: none;
                            padding: 15px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 14px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                            transition: all 0.3s ease;
                            font-weight: 500;
                        ">
                            <i class="fas fa-file-excel"></i>
                            Datos Excel
                        </button>
                    </div>
                    <button id="export-cancel-btn" style="
                        background: #e74c3c;
                        color: white;
                        border: none;
                        padding: 10px 30px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 14px;
                        transition: all 0.3s ease;
                    ">
                        Cancelar
                    </button>
                </div>
            </div>
            
            <style>
                @keyframes modalSlideIn {
                    from {
                        opacity: 0;
                        transform: translateY(-50px) scale(0.9);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
            </style>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.currentModal = document.getElementById('export-modal');
        
        // Event listeners
        document.getElementById('export-png-btn').addEventListener('click', () => {
            this.exportPNG();
        });
        
        document.getElementById('export-excel-btn').addEventListener('click', () => {
            this.exportExcel();
        });
        
        document.getElementById('export-cancel-btn').addEventListener('click', () => {
            this.closeModal();
        });
        
        // Cerrar con click fuera del modal
        this.currentModal.addEventListener('click', (e) => {
            if (e.target.id === 'export-modal') {
                this.closeModal();
            }
        });
    },
    
    /**
     * Cerrar modal actual
     */
    closeModal() {
        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }
    },
    
    /**
     * Exportar como PNG
     */
    async exportPNG() {
        if (this.isExporting) return;
        
        try {
            this.isExporting = true;
            console.log('📸 Iniciando exportación PNG...');
            
            const chartElement = document.querySelector(this.config.chartSelector);
            if (!chartElement) {
                throw new Error(`No se encontró el elemento: ${this.config.chartSelector}`);
            }
            
            this.closeModal();
            this.showMessage('Generando imagen...', 'info');
            
            // Usar html2canvas si está disponible
            if (typeof html2canvas !== 'undefined') {
                const canvas = await html2canvas(chartElement, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    height: chartElement.offsetHeight,
                    width: chartElement.offsetWidth
                });
                
                this.downloadCanvas(canvas, this.generateFileName('png'));
            } else {
                // Fallback: captura básica con canvas
                await this.basicPNGExport(chartElement);
            }
            
            this.showMessage('Imagen descargada exitosamente', 'success');
            
        } catch (error) {
            console.error('❌ Error en exportación PNG:', error);
            this.showMessage('Error al generar imagen: ' + error.message, 'error');
        } finally {
            this.isExporting = false;
        }
    },
    
    /**
     * Exportar como Excel
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
            
            this.closeModal();
            this.showMessage('Generando archivo Excel...', 'info');
            
            const data = this.config.dataCallback();
            if (!data || !Array.isArray(data)) {
                throw new Error('Los datos para exportar no son válidos');
            }
            
            // Crear workbook
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Aplicar estilos si están definidos
            if (this.config.customStyles && this.config.customStyles.excel) {
                this.applyExcelStyles(ws, data);
            }
            
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
    applyExcelStyles(ws, data) {
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
     * Exportación PNG básica (fallback)
     */
    async basicPNGExport(element) {
        return new Promise((resolve, reject) => {
            try {
                // Crear canvas temporal
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                canvas.width = element.offsetWidth;
                canvas.height = element.offsetHeight;
                
                // Fondo blanco
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                
                // Intentar capturar SVG si existe
                const svg = element.querySelector('svg');
                if (svg) {
                    const svgData = new XMLSerializer().serializeToString(svg);
                    const img = new Image();
                    const blob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
                    const url = URL.createObjectURL(blob);
                    
                    img.onload = () => {
                        ctx.drawImage(img, 0, 0);
                        URL.revokeObjectURL(url);
                        this.downloadCanvas(canvas, this.generateFileName('png'));
                        resolve();
                    };
                    
                    img.onerror = () => {
                        reject(new Error('No se pudo procesar el gráfico SVG'));
                    };
                    
                    img.src = url;
                } else {
                    reject(new Error('No se encontró gráfico para exportar'));
                }
                
            } catch (error) {
                reject(error);
            }
        });
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
        
        const messageHTML = `
            <div id="export-message" style="
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
            ">
                ${message}
            </div>
            
            <style>
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
            </style>
        `;
        
        document.body.insertAdjacentHTML('beforeend', messageHTML);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            const msg = document.getElementById('export-message');
            if (msg) {
                msg.style.animation = 'slideInRight 0.3s ease-out reverse';
                setTimeout(() => msg.remove(), 300);
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
        console.log('- Modal activo:', this.currentModal !== null);
        console.log('- Elemento gráfico:', document.querySelector(this.config.chartSelector) ? 'Encontrado' : 'No encontrado');
        console.log('- XLSX disponible:', typeof XLSX !== 'undefined');
        console.log('- html2canvas disponible:', typeof html2canvas !== 'undefined');
    }
};

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
    window.ExportManager = ExportManager;
    
    // Función de conveniencia para mostrar modal
    window.exportarDatos = function() {
        ExportManager.showExportModal();
    };
}

console.log('✅ ExportManager (Singleton) cargado correctamente');
    }

    /**
     * Función principal para iniciar exportación
     */
    exportData() {
        if (this.isExporting) {
            this.showMessage('Ya se está procesando una exportación...', 'warning');
            return;
        }
        
        this.showExportModal();
    }

    /**
     * Muestra el modal de exportación con opciones dinámicas
     */
    showExportModal() {
        // Eliminar modal existente
        this.removeExistingModal();
        
        const modalHTML = this.buildModalHTML();
        
        // Agregar al DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.currentModal = document.getElementById('export-modal');
        
        // Configurar eventos
        this.setupModalEvents();
        
        // Agregar estilos si no existen
        this.ensureModalStyles();
    }

    /**
     * Construye el HTML del modal dinámicamente
     */
    buildModalHTML() {
        const buttons = this.buildExportButtons();
        
        return `
            <div id="export-modal" class="export-modal-overlay">
                <div class="export-modal-content">
                    <div class="export-modal-header">
                        <h3>
                            <i class="fas fa-download"></i>
                            ${this.config.modalTitle}
                        </h3>
                        <p>Selecciona el formato de exportación que prefieras:</p>
                    </div>
                    <div class="export-modal-buttons">
                        ${buttons}
                    </div>
                    <div class="export-modal-footer">
                        <button id="export-cancel-btn" class="export-button export-button-cancel">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Construye los botones de exportación según la configuración
     */
    buildExportButtons() {
        let buttons = '';
        
        if (this.config.enablePNG) {
            buttons += `
                <button id="export-png-btn" class="export-button export-button-png">
                    <i class="fas fa-image"></i>
                    Gráfico como PNG
                </button>
            `;
        }
        
        if (this.config.enableExcel) {
            buttons += `
                <button id="export-excel-btn" class="export-button export-button-excel">
                    <i class="fas fa-file-excel"></i>
                    Datos como Excel
                </button>
            `;
        }
        
        if (this.config.enablePDF) {
            buttons += `
                <button id="export-pdf-btn" class="export-button export-button-pdf">
                    <i class="fas fa-file-pdf"></i>
                    Reporte PDF
                </button>
            `;
        }
        
        return buttons;
    }

    /**
     * Configura los eventos del modal
     */
    setupModalEvents() {
        // Botón de cancelar
        const cancelBtn = document.getElementById('export-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeModal());
        }
        
        // Botón PNG
        if (this.config.enablePNG) {
            const pngBtn = document.getElementById('export-png-btn');
            if (pngBtn) {
                pngBtn.addEventListener('click', () => {
                    this.closeModal();
                    this.exportToPNG();
                });
            }
        }
        
        // Botón Excel
        if (this.config.enableExcel) {
            const excelBtn = document.getElementById('export-excel-btn');
            if (excelBtn) {
                excelBtn.addEventListener('click', () => {
                    this.closeModal();
                    this.exportToExcel();
                });
            }
        }
        
        // Botón PDF
        if (this.config.enablePDF) {
            const pdfBtn = document.getElementById('export-pdf-btn');
            if (pdfBtn) {
                pdfBtn.addEventListener('click', () => {
                    this.closeModal();
                    this.exportToPDF();
                });
            }
        }
        
        // Cerrar con click fuera del modal
        this.currentModal.addEventListener('click', (e) => {
            if (e.target.id === 'export-modal') {
                this.closeModal();
            }
        });
        
        // Cerrar con tecla ESC
        const handleEsc = (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
                document.removeEventListener('keydown', handleEsc);
            }
        };
        document.addEventListener('keydown', handleEsc);
        
        // Efectos hover en botones
        this.addButtonHoverEffects();
    }

    /**
     * Agrega efectos hover a los botones
     */
    addButtonHoverEffects() {
        const buttons = document.querySelectorAll('.export-button:not(.export-button-cancel)');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', () => {
                button.style.transform = 'translateY(-2px)';
                button.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.15)';
            });
            button.addEventListener('mouseleave', () => {
                button.style.transform = 'translateY(0)';
                button.style.boxShadow = 'none';
            });
        });
    }

    /**
     * EXPORTACIÓN PNG - Método principal con fallbacks
     */
    async exportToPNG() {
        this.isExporting = true;
        
        try {
            // Verificar dependencias
            if (typeof html2canvas === 'undefined') {
                throw new Error('html2canvas no está disponible');
            }
            
            // Obtener elemento del gráfico
            const chartElement = document.querySelector(this.config.chartSelector);
            if (!chartElement) {
                throw new Error('No se encontró el elemento del gráfico');
            }
            
            // Callback antes de exportar PNG
            if (this.config.onBeforePNG) {
                await this.config.onBeforePNG();
            }
            
            this.showMessage('Generando imagen PNG...', 'info');
            
            // Intentar exportación con múltiples métodos
            const success = await this.executePNGSequence(chartElement);
            
            if (!success) {
                throw new Error('Todos los métodos de captura fallaron');
            }
            
        } catch (error) {
            console.error('❌ Error en exportación PNG:', error);
            this.showMessage(`Error: ${error.message}`, 'error');
        } finally {
            this.isExporting = false;
            
            // Callback después de exportar PNG
            if (this.config.onAfterPNG) {
                await this.config.onAfterPNG();
            }
        }
    }

    /**
     * Ejecuta secuencia de métodos para captura PNG
     */
    async executePNGSequence(chartElement) {
        const fileName = this.generateFileName('png');
        
        // Método 1: Captura directa
        try {
            console.log('🔄 Intentando captura directa...');
            const success = await this.directPNGCapture(chartElement, fileName);
            if (success) return true;
        } catch (error) {
            console.warn('⚠️ Método 1 falló:', error.message);
        }
        
        // Método 2: Con mejoras DOM
        try {
            console.log('🔄 Intentando con mejoras DOM...');
            const success = await this.enhancedPNGCapture(chartElement, fileName);
            if (success) return true;
        } catch (error) {
            console.warn('⚠️ Método 2 falló:', error.message);
        }
        
        // Método 3: Con contenedor temporal
        try {
            console.log('🔄 Intentando con contenedor temporal...');
            const success = await this.containerPNGCapture(chartElement, fileName);
            if (success) return true;
        } catch (error) {
            console.warn('⚠️ Método 3 falló:', error.message);
        }
        
        return false;
    }

    /**
     * Método 1: Captura directa
     */
    async directPNGCapture(chartElement, fileName) {
        const canvas = await html2canvas(chartElement, {
            backgroundColor: '#ffffff',
            scale: 2,
            logging: false,
            useCORS: true,
            allowTaint: true,
            removeContainer: false
        });
        
        return this.downloadCanvas(canvas, fileName);
    }

    /**
     * Método 2: Captura con mejoras DOM
     */
    async enhancedPNGCapture(chartElement, fileName) {
        const restoreFunction = this.applyDOMEnhancements(chartElement);
        
        try {
            await new Promise(resolve => setTimeout(resolve, 300)); // Esperar renderizado
            
            const canvas = await html2canvas(chartElement, {
                backgroundColor: '#ffffff',
                scale: 2,
                logging: false,
                useCORS: true,
                allowTaint: true,
                removeContainer: false,
                foreignObjectRendering: false
            });
            
            const success = this.downloadCanvas(canvas, fileName);
            return success;
        } finally {
            restoreFunction(); // Restaurar DOM original
        }
    }

    /**
     * Método 3: Captura con contenedor temporal (incluye leyenda)
     */
    async containerPNGCapture(chartElement, fileName) {
        // Solo usar este método si hay configuración de leyenda
        if (!this.config.legend || this.config.legend.length === 0) {
            return false;
        }
        
        const container = await this.createTemporaryContainer(chartElement);
        if (!container) return false;
        
        try {
            const canvas = await html2canvas(container, {
                backgroundColor: '#ffffff',
                scale: 2,
                logging: false,
                useCORS: true,
                allowTaint: true
            });
            
            const success = this.downloadCanvas(canvas, fileName);
            return success;
        } finally {
            // Limpiar contenedor temporal
            if (container.parentNode) {
                container.parentNode.removeChild(container);
            }
        }
    }

    /**
     * EXPORTACIÓN EXCEL
     */
    async exportToExcel() {
        this.isExporting = true;
        
        try {
            // Verificar dependencias
            if (typeof XLSX === 'undefined') {
                throw new Error('XLSX no está disponible');
            }
            
            this.showMessage('Generando archivo Excel...', 'info');
            
            // Obtener datos
            let data = null;
            if (this.config.dataProvider) {
                data = await this.config.dataProvider();
            } else if (this.config.onDataPrepare) {
                data = await this.config.onDataPrepare();
            } else {
                throw new Error('No se configuró un proveedor de datos');
            }
            
            if (!data || !Array.isArray(data)) {
                throw new Error('Los datos no tienen el formato correcto');
            }
            
            // Crear archivo Excel
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, this.config.pageId || "Datos");
            
            // Descargar
            const fileName = this.generateFileName('xlsx');
            XLSX.writeFile(wb, fileName);
            
            this.showMessage('Archivo Excel generado exitosamente', 'success');
            
        } catch (error) {
            console.error('❌ Error en exportación Excel:', error);
            this.showMessage(`Error: ${error.message}`, 'error');
        } finally {
            this.isExporting = false;
        }
    }

    /**
     * EXPORTACIÓN PDF (para futuro)
     */
    async exportToPDF() {
        this.showMessage('Exportación PDF estará disponible próximamente', 'info');
    }

    /**
     * FUNCIONES DE UTILIDAD
     */

    /**
     * Genera nombres de archivo basados en configuración y contexto
     */
    generateFileName(extension) {
        const timestamp = new Date().toISOString().slice(0, 19).replace(/[:-]/g, '');
        let fileName = `${this.config.filePrefix}_${this.config.pageId}`;
        
        // Permitir personalización del nombre via callback
        if (this.config.generateFileName) {
            fileName = this.config.generateFileName(extension);
        }
        
        return `${fileName}_${timestamp}.${extension}`;
    }

    /**
     * Aplica mejoras temporales al DOM
     */
    applyDOMEnhancements(chartElement) {
        const changes = [];
        
        // Mejorar contenedor
        const container = chartElement.closest('.chart-container');
        if (container) {
            const originalStyle = container.style.cssText;
            changes.push(() => container.style.cssText = originalStyle);
            
            container.style.padding = '20px';
            container.style.backgroundColor = '#ffffff';
        }
        
        // Mejorar textos
        const textElements = chartElement.querySelectorAll('text');
        textElements.forEach(text => {
            const originalStyle = text.style.cssText;
            changes.push(() => text.style.cssText = originalStyle);
            
            text.style.fontSize = '12px';
            text.style.fontFamily = 'Arial, sans-serif';
            text.style.fill = '#333';
        });
        
        // Función para restaurar cambios
        return function restore() {
            changes.forEach(changeRestore => changeRestore());
        };
    }

    /**
     * Crea contenedor temporal con leyenda
     */
    async createTemporaryContainer(chartElement) {
        if (!this.config.legend || this.config.legend.length === 0) {
            return null;
        }
        
        try {
            // Capturar gráfico primero
            const chartCanvas = await html2canvas(chartElement, {
                backgroundColor: '#ffffff',
                scale: 2,
                logging: false
            });
            
            // Crear contenedor
            const container = document.createElement('div');
            container.style.cssText = `
                position: absolute;
                top: -9999px;
                left: -9999px;
                background: #ffffff;
                padding: 30px;
                border-radius: 8px;
                font-family: Arial, sans-serif;
            `;
            
            // Agregar imagen del gráfico
            const imgElement = document.createElement('img');
            imgElement.src = chartCanvas.toDataURL('image/png');
            imgElement.style.cssText = `
                width: ${chartElement.offsetWidth}px;
                height: ${chartElement.offsetHeight}px;
                margin-bottom: 20px;
                display: block;
            `;
            
            // Crear leyenda
            const legendElement = this.createLegendElement();
            
            // Ensamblar
            container.appendChild(imgElement);
            if (legendElement) {
                container.appendChild(legendElement);
            }
            
            // Agregar temporalmente al DOM
            document.body.appendChild(container);
            
            return container;
        } catch (error) {
            console.error('Error creando contenedor temporal:', error);
            return null;
        }
    }

    /**
     * Crea elemento de leyenda
     */
    createLegendElement() {
        if (!this.config.legend || this.config.legend.length === 0) {
            return null;
        }
        
        const legendContainer = document.createElement('div');
        legendContainer.style.cssText = `
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eaeaea;
        `;
        
        // Título de leyenda
        if (this.config.legendTitle) {
            const title = document.createElement('div');
            title.textContent = this.config.legendTitle;
            title.style.cssText = `
                width: 100%;
                text-align: center;
                font-weight: 600;
                color: #333;
                margin-bottom: 10px;
                font-size: 14px;
            `;
            legendContainer.appendChild(title);
        }
        
        // Items de leyenda
        this.config.legend.forEach(item => {
            const legendItem = document.createElement('div');
            legendItem.style.cssText = `
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                padding: 6px 12px;
                border-radius: 20px;
                background-color: #ffffff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            `;
            
            const colorBox = document.createElement('div');
            colorBox.style.cssText = `
                width: 12px;
                height: 12px;
                border-radius: 2px;
                background-color: ${item.color};
                flex-shrink: 0;
            `;
            
            const label = document.createElement('span');
            label.textContent = item.name;
            label.style.cssText = `
                font-weight: 500;
                color: #333;
                white-space: nowrap;
            `;
            
            legendItem.appendChild(colorBox);
            legendItem.appendChild(label);
            legendContainer.appendChild(legendItem);
        });
        
        return legendContainer;
    }

    /**
     * Descarga un canvas como imagen
     */
    downloadCanvas(canvas, fileName) {
        try {
            if (!canvas || canvas.width === 0 || canvas.height === 0) {
                throw new Error('Canvas inválido o vacío');
            }
            
            // Crear enlace de descarga
            const link = document.createElement('a');
            link.download = fileName;
            link.href = canvas.toDataURL('image/png');
            
            // Simular click
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            this.showMessage('Imagen PNG generada exitosamente', 'success');
            return true;
        } catch (error) {
            console.error('Error descargando canvas:', error);
            return false;
        }
    }

    /**
     * Sistema de mensajes mejorado
     */
    showMessage(message, type = 'info') {
        // Remover mensajes existentes
        const existingMessages = document.querySelectorAll('.export-message');
        existingMessages.forEach(msg => msg.remove());
        
        const messageElement = document.createElement('div');
        messageElement.className = 'export-message';
        
        const colors = {
            success: { bg: '#4CAF50', icon: 'check-circle' },
            error: { bg: '#f44336', icon: 'exclamation-circle' },
            warning: { bg: '#ff9800', icon: 'exclamation-triangle' },
            info: { bg: '#2196F3', icon: 'info-circle' }
        };
        
        const config = colors[type] || colors.info;
        
        messageElement.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${config.bg};
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            z-index: 10001;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 14px;
            max-width: 300px;
            word-wrap: break-word;
        `;
        
        messageElement.innerHTML = `<i class="fas fa-${config.icon}"></i> ${message}`;
        
        document.body.appendChild(messageElement);
        
        // Auto remove
        const duration = type === 'error' ? 5000 : 3000;
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.remove();
            }
        }, duration);
    }

    /**
     * Funciones de gestión de modal
     */
    removeExistingModal() {
        const existing = document.getElementById('export-modal');
        if (existing) {
            existing.remove();
        }
    }

    closeModal() {
        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }
    }

    /**
     * Asegura que los estilos del modal estén disponibles
     */
    ensureModalStyles() {
        if (document.getElementById('export-manager-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'export-manager-styles';
        styles.textContent = `
            .export-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                backdrop-filter: blur(5px);
                animation: modalFadeIn 0.3s ease-out;
            }
            
            .export-modal-content {
                background: white;
                padding: 40px;
                border-radius: 15px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                width: 90%;
                animation: modalSlideIn 0.3s ease-out;
            }
            
            .export-modal-header h3 {
                margin-bottom: 10px;
                color: #2c3e50;
                font-size: 24px;
                font-weight: bold;
            }
            
            .export-modal-header p {
                margin-bottom: 30px;
                color: #7f8c8d;
                font-size: 16px;
            }
            
            .export-modal-buttons {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-bottom: 30px;
            }
            
            .export-button {
                border: none;
                padding: 15px 20px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                transition: all 0.3s ease;
                font-weight: 500;
                font-family: inherit;
            }
            
            .export-button-png {
                background: #3498db;
                color: white;
            }
            
            .export-button-excel {
                background: #27ae60;
                color: white;
            }
            
            .export-button-pdf {
                background: #e74c3c;
                color: white;
            }
            
            .export-button-cancel {
                background: #95a5a6;
                color: white;
                margin: 0 auto;
                padding: 12px 30px;
            }
            
            @keyframes modalFadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        `;
        
        document.head.appendChild(styles);
    }

    /**
     * Función estática para crear instancia rápida
     */
    static create(config) {
        return new ExportManager(config);
    }

    /**
     * Función para verificar dependencias
     */
    static checkDependencies() {
        const dependencies = {
            html2canvas: typeof html2canvas !== 'undefined',
            XLSX: typeof XLSX !== 'undefined'
        };
        
        return dependencies;
    }
}

/**
 * CONFIGURACIONES PRE-DEFINIDAS PARA DIFERENTES TIPOS DE GRÁFICOS
 */
const ExportPresets = {
    /**
     * Configuración para gráficos de Google Charts
     */
    googleCharts: {
        enablePNG: true,
        enableExcel: true,
        modalTitle: 'Exportar Gráfico',
        
        // Mejoras específicas para Google Charts
        onBeforePNG: async function() {
            // Verificar que el gráfico esté completamente renderizado
            await new Promise(resolve => setTimeout(resolve, 500));
        }
    },
    
    /**
     * Configuración para dashboards con filtros
     */
    dashboard: {
        enablePNG: true,
        enableExcel: true,
        enablePDF: false,
        modalTitle: 'Exportar Dashboard',
        
        generateFileName: function(extension) {
            const timestamp = new Date().toISOString().slice(0, 10);
            return `Dashboard_${this.pageId}_${timestamp}.${extension}`;
        }
    },
    
    /**
     * Configuración básica para gráficos simples
     */
    simple: {
        enablePNG: true,
        enableExcel: false,
        modalTitle: 'Exportar Imagen'
    }
};

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.ExportManager = ExportManager;
    window.ExportPresets = ExportPresets;
}
