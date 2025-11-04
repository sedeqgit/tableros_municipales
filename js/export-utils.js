/**
 * =============================================================================
 * MÓDULO DE UTILIDADES DE EXPORTACIÓN - SISTEMA SEDEQ
 * =============================================================================
 *
 * Módulo flexible con funciones reutilizables para exportación de datos.
 * Cada página mantiene su lógica de datos pero usa estas utilidades comunes.
 *
 * FUNCIONALIDADES:
 * - Notificaciones visuales elegantes (éxito, error, info, advertencia)
 * - Helpers para crear y descargar archivos Excel
 * - Helpers para crear y descargar archivos PDF
 * - Estilos dinámicos para componentes visuales
 *
 * @package SEDEQ_Dashboard
 * @version 1.0
 */

// =============================================================================
// SISTEMA DE NOTIFICACIONES
// =============================================================================

const ExportNotifications = {
    /**
     * Muestra una notificación de éxito
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (default: 3000)
     */
    showSuccess(message, duration = 3000) {
        this._showNotification(message, 'success', duration);
    },

    /**
     * Muestra una notificación de error
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (default: 4000)
     */
    showError(message, duration = 4000) {
        this._showNotification(message, 'error', duration);
    },

    /**
     * Muestra una notificación de información
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (default: 3000)
     */
    showInfo(message, duration = 3000) {
        this._showNotification(message, 'info', duration);
    },

    /**
     * Muestra una notificación de advertencia
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en ms (default: 3500)
     */
    showWarning(message, duration = 3500) {
        this._showNotification(message, 'warning', duration);
    },

    /**
     * Método interno para crear y mostrar notificaciones
     * @private
     */
    _showNotification(message, type, duration) {
        const notification = document.createElement('div');
        notification.className = `export-notification ${type}`;

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle',
            warning: 'fas fa-exclamation-triangle'
        };

        notification.innerHTML = `
            <i class="${icons[type]}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, duration);
    }
};

// =============================================================================
// UTILIDADES PARA EXCEL
// =============================================================================

const ExcelUtils = {
    /**
     * Crea un nuevo libro de Excel
     * @returns {Object} Workbook de SheetJS
     */
    createWorkbook() {
        if (typeof XLSX === 'undefined') {
            throw new Error('La biblioteca XLSX no está disponible');
        }
        return XLSX.utils.book_new();
    },

    /**
     * Agrega una hoja al libro con datos en formato array
     * @param {Object} workbook - Libro de Excel
     * @param {Array} data - Datos en formato array de arrays
     * @param {string} sheetName - Nombre de la hoja
     */
    addSheetFromArray(workbook, data, sheetName) {
        const worksheet = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);
    },

    /**
     * Agrega una hoja al libro con datos en formato JSON
     * @param {Object} workbook - Libro de Excel
     * @param {Array} data - Datos en formato JSON
     * @param {string} sheetName - Nombre de la hoja
     */
    addSheetFromJSON(workbook, data, sheetName) {
        const worksheet = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);
    },

    /**
     * Descarga el libro de Excel
     * @param {Object} workbook - Libro de Excel
     * @param {string} filename - Nombre del archivo (sin extensión)
     */
    downloadWorkbook(workbook, filename) {
        const fullFilename = filename.endsWith('.xlsx') ? filename : `${filename}.xlsx`;
        XLSX.writeFile(workbook, fullFilename);
    },

    /**
     * Método completo: crea, llena y descarga un Excel con múltiples hojas
     * @param {Array} sheets - Array de objetos {name: string, data: Array}
     * @param {string} filename - Nombre del archivo
     */
    exportMultiSheet(sheets, filename) {
        try {
            const workbook = this.createWorkbook();

            sheets.forEach(sheet => {
                this.addSheetFromArray(workbook, sheet.data, sheet.name);
            });

            this.downloadWorkbook(workbook, filename);
            ExportNotifications.showSuccess('Archivo Excel exportado exitosamente');
            return true;
        } catch (error) {
            console.error('Error al exportar Excel:', error);
            ExportNotifications.showError('Error al exportar el archivo Excel');
            return false;
        }
    }
};

// =============================================================================
// UTILIDADES PARA PDF
// =============================================================================

const PDFUtils = {
    /**
     * Crea un nuevo documento PDF
     * @param {string} orientation - 'portrait' o 'landscape'
     * @returns {Object} Documento jsPDF
     */
    createDocument(orientation = 'portrait') {
        if (typeof jsPDF === 'undefined' && typeof window.jspdf === 'undefined') {
            throw new Error('La biblioteca jsPDF no está disponible');
        }

        const { jsPDF } = window.jspdf || { jsPDF };
        return new jsPDF(orientation);
    },

    /**
     * Agrega un título al PDF
     * @param {Object} doc - Documento jsPDF
     * @param {string} title - Título
     * @param {Object} options - Opciones de estilo
     */
    addTitle(doc, title, options = {}) {
        const defaults = {
            fontSize: 18,
            color: [0, 73, 144],
            x: 14,
            y: 20
        };
        const opts = { ...defaults, ...options };

        doc.setFontSize(opts.fontSize);
        doc.setTextColor(opts.color[0], opts.color[1], opts.color[2]);
        doc.text(title, opts.x, opts.y);
    },

    /**
     * Agrega una tabla al PDF usando autoTable
     * @param {Object} doc - Documento jsPDF
     * @param {Array} headers - Array de encabezados
     * @param {Array} data - Datos de la tabla
     * @param {Object} options - Opciones de autoTable
     */
    addTable(doc, headers, data, options = {}) {
        if (typeof doc.autoTable === 'undefined') {
            throw new Error('El plugin autoTable no está disponible');
        }

        const defaults = {
            head: [headers],
            body: data,
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
            alternateRowStyles: {
                fillColor: [245, 247, 250]
            }
        };

        doc.autoTable({ ...defaults, ...options });
    },

    /**
     * Agrega un pie de página con numeración
     * @param {Object} doc - Documento jsPDF
     * @param {string} text - Texto del pie de página
     */
    addFooter(doc, text) {
        const pageCount = doc.internal.getNumberOfPages();
        doc.setFontSize(8);
        doc.setTextColor(100, 100, 100);

        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.text(
                `${text} - Página ${i} de ${pageCount}`,
                14,
                doc.internal.pageSize.height - 10
            );
        }
    },

    /**
     * Descarga el documento PDF
     * @param {Object} doc - Documento jsPDF
     * @param {string} filename - Nombre del archivo
     */
    downloadDocument(doc, filename) {
        const fullFilename = filename.endsWith('.pdf') ? filename : `${filename}.pdf`;
        doc.save(fullFilename);
    },

    /**
     * Método completo: crea un PDF con título, tabla y pie de página
     * @param {Object} config - Configuración completa del PDF
     */
    exportTablePDF(config) {
        try {
            const doc = this.createDocument(config.orientation || 'portrait');

            if (config.title) {
                this.addTitle(doc, config.title, config.titleOptions);
            }

            if (config.table) {
                this.addTable(
                    doc,
                    config.table.headers,
                    config.table.data,
                    config.table.options
                );
            }

            if (config.footer) {
                this.addFooter(doc, config.footer);
            }

            this.downloadDocument(doc, config.filename);
            ExportNotifications.showSuccess('Archivo PDF exportado exitosamente');
            return true;
        } catch (error) {
            console.error('Error al exportar PDF:', error);
            ExportNotifications.showError('Error al exportar el archivo PDF');
            return false;
        }
    }
};

// =============================================================================
// UTILIDADES DE FORMATEO
// =============================================================================

const FormatUtils = {
    /**
     * Formatea un número con separadores de miles
     * @param {number} value - Valor a formatear
     * @returns {string} Valor formateado
     */
    formatNumber(value) {
        return value.toLocaleString('es-MX');
    },

    /**
     * Formatea un porcentaje
     * @param {number} value - Valor a formatear
     * @param {number} decimals - Número de decimales
     * @returns {string} Porcentaje formateado
     */
    formatPercentage(value, decimals = 1) {
        return value.toFixed(decimals) + '%';
    },

    /**
     * Obtiene la fecha actual en formato español
     * @returns {string} Fecha formateada
     */
    getCurrentDate() {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date().toLocaleDateString('es-MX', options);
    },

    /**
     * Genera un nombre de archivo con fecha
     * @param {string} baseName - Nombre base del archivo
     * @returns {string} Nombre con fecha
     */
    generateFilenameWithDate(baseName) {
        const fecha = new Date().toISOString().split('T')[0];
        return `${baseName}_${fecha}`;
    }
};

// =============================================================================
// INICIALIZACIÓN DE ESTILOS
// =============================================================================

function initExportUtilsStyles() {
    // Evitar duplicar estilos
    if (document.getElementById('export-utils-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'export-utils-styles';
    style.textContent = `
        .export-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            color: #333;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Hanken Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 10000;
            border-left: 4px solid transparent;
            min-width: 300px;
            max-width: 500px;
        }

        .export-notification.success {
            border-left-color: #4CAF50;
        }

        .export-notification.success i {
            color: #4CAF50;
            font-size: 18px;
        }

        .export-notification.error {
            border-left-color: #F44336;
        }

        .export-notification.error i {
            color: #F44336;
            font-size: 18px;
        }

        .export-notification.info {
            border-left-color: #2196F3;
        }

        .export-notification.info i {
            color: #2196F3;
            font-size: 18px;
        }

        .export-notification.warning {
            border-left-color: #FF9800;
        }

        .export-notification.warning i {
            color: #FF9800;
            font-size: 18px;
        }

        .export-notification.show {
            transform: translateX(0);
        }

        .export-notification span {
            flex: 1;
            line-height: 1.4;
        }
    `;
    document.head.appendChild(style);
}

// Inicializar estilos cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initExportUtilsStyles);
} else {
    initExportUtilsStyles();
}

// =============================================================================
// EXPORTAR UTILIDADES GLOBALMENTE
// =============================================================================

window.ExportNotifications = ExportNotifications;
window.ExcelUtils = ExcelUtils;
window.PDFUtils = PDFUtils;
window.FormatUtils = FormatUtils;
