/**
 * =============================================================================
 * SCRIPT DE FUNCIONALIDAD PARA DIRECTORIO DE ESCUELAS
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Este archivo maneja toda la funcionalidad interactiva del directorio de 
 * escuelas, incluyendo filtros, búsquedas, navegación entre pestañas y 
 * funciones de exportación.
 * 
 * FUNCIONALIDADES:
 * - Navegación entre pestañas (públicas/privadas)
 * - Filtrado por nivel educativo
 * - Búsqueda de texto en tiempo real
 * - Exportación a Excel y PDF
 * - Responsive design y animaciones
 * 
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    initDirectorioEscuelas();
});

function initDirectorioEscuelas() {
    // Inicializar textos originales para búsqueda
    initOriginalTexts();
    
    // Inicializar filtros (ya no hay pestañas)
    initFilters();
    
    // Inicializar búsquedas
    initSearch();
    
    // Mostrar estadísticas iniciales
    updateStats();
}

/**
 * Actualiza el contador de escuelas visibles
 * @param {string} type - Tipo de escuelas ('publicas' o 'privadas')
 */
function updateSchoolCount(type) {
    const table = document.getElementById('tabla-' + type);
    const countElement = document.getElementById('count-' + type);
    
    if (!table || !countElement) return;
    
    const allRows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const visibleRows = Array.from(allRows).filter(row => row.style.display !== 'none');
    
    countElement.textContent = visibleRows.length;
}

/**
 * Inicializa los filtros por nivel educativo
 */
function initFilters() {
    const publicasFilter = document.getElementById('nivel-filter-publicas');
    const privadasFilter = document.getElementById('nivel-filter-privadas');
    
    if (publicasFilter) {
        publicasFilter.addEventListener('change', function() {
            filterByLevel('publicas', this.value);
        });
    }
    
    if (privadasFilter) {
        privadasFilter.addEventListener('change', function() {
            filterByLevel('privadas', this.value);
        });
    }
}

/**
 * Inicializa la funcionalidad de búsqueda
 */
function initSearch() {
    const publicasSearch = document.getElementById('search-publicas');
    const privadasSearch = document.getElementById('search-privadas');
    
    if (publicasSearch) {
        publicasSearch.addEventListener('input', function() {
            searchSchools('publicas', this.value);
        });
    }
    
    if (privadasSearch) {
        privadasSearch.addEventListener('input', function() {
            searchSchools('privadas', this.value);
        });
    }
}

/**
 * Filtra las escuelas por nivel educativo
 * @param {string} type - Tipo de escuelas ('publicas' o 'privadas')
 * @param {string} level - Nivel educativo a filtrar ('todos' o código de nivel)
 */
function filterByLevel(type, level) {
    const table = document.getElementById('tabla-' + type);
    if (!table) return;

    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));

    // Obtener el término de búsqueda actual
    const searchInput = document.getElementById('search-' + type);
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';

    let visibleCount = 0;

    // Si se selecciona "Todos los niveles", ordenar por nivel y luego por alumnos descendente
    if (!level || level === 'todos') {
        sortTableByLevelAndStudents(rows, tbody);
    }

    rows.forEach(row => {
        const rowLevel = row.getAttribute('data-nivel');

        // Verificar si cumple con el filtro de nivel
        const matchesLevel = !level || level === 'todos' || rowLevel === level;

        // Verificar si cumple con el filtro de búsqueda
        let matchesSearch = true;
        if (searchTerm) {
            const cells = row.getElementsByTagName('td');
            matchesSearch = false;
            Array.from(cells).forEach(cell => {
                const originalText = cell.dataset.originalText || cell.textContent;
                if (originalText.toLowerCase().includes(searchTerm)) {
                    matchesSearch = true;
                }
            });
        }

        // Mostrar solo si cumple AMBOS filtros
        const shouldShow = matchesLevel && matchesSearch;

        if (shouldShow) {
            row.style.display = '';
            visibleCount++;

            // Aplicar animación de entrada
            row.style.animation = 'fadeInUp 0.3s ease-in-out';

            // Mantener resaltado de búsqueda si existe
            highlightSearchTerm(row, searchTerm);
        } else {
            row.style.display = 'none';
        }
    });

    // Actualizar contador
    updateSchoolCount(type);

    // Mostrar mensaje si no hay resultados
    showNoResultsMessage(type, visibleCount === 0);
}

/**
 * Busca escuelas por texto
 * @param {string} type - Tipo de escuelas ('publicas' o 'privadas')
 * @param {string} searchTerm - Término de búsqueda
 */
function searchSchools(type, searchTerm) {
    const table = document.getElementById('tabla-' + type);
    if (!table) return;

    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const term = searchTerm.toLowerCase().trim();

    // Obtener el filtro de nivel actual
    const levelFilter = document.getElementById('nivel-filter-' + type);
    const selectedLevel = levelFilter ? levelFilter.value : 'todos';

    let visibleCount = 0;

    Array.from(rows).forEach(row => {
        const cells = row.getElementsByTagName('td');
        const rowLevel = row.getAttribute('data-nivel');

        // Verificar si cumple con el filtro de nivel
        const matchesLevel = !selectedLevel || selectedLevel === 'todos' || rowLevel === selectedLevel;

        // Verificar si cumple con el filtro de búsqueda
        let matchesSearch = true;
        if (term) {
            matchesSearch = false;
            Array.from(cells).forEach(cell => {
                const originalText = cell.dataset.originalText || cell.textContent;
                if (originalText.toLowerCase().includes(term)) {
                    matchesSearch = true;
                }
            });
        }

        // Mostrar solo si cumple AMBOS filtros
        const shouldShow = matchesLevel && matchesSearch;

        if (shouldShow) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }

        // Aplicar resaltado después de determinar visibilidad
        highlightSearchTerm(row, term);
    });

    // Actualizar contador
    updateSchoolCount(type);

    // Mostrar mensaje si no hay resultados
    showNoResultsMessage(type, visibleCount === 0);
}

/**
 * Resalta los términos de búsqueda en una fila
 * @param {HTMLElement} row - Fila de la tabla
 * @param {string} term - Término a resaltar
 */
function highlightSearchTerm(row, term) {
    const cells = row.getElementsByTagName('td');
    Array.from(cells).forEach(cell => {
        // Primero limpiamos cualquier resaltado previo y guardamos el texto original
        if (!cell.dataset.originalText) {
            cell.dataset.originalText = cell.textContent;
        }
        
        // Restaurar texto original
        cell.innerHTML = cell.dataset.originalText;
        
        // Si hay término de búsqueda, resaltar
        if (term && cell.dataset.originalText.toLowerCase().includes(term)) {
            const regex = new RegExp(`(${term})`, 'gi');
            const highlightedText = cell.dataset.originalText.replace(regex, '<mark style="background: #fff3cd; padding: 2px 4px; border-radius: 3px;">$1</mark>');
            cell.innerHTML = highlightedText;
        }
    });
}

/**
 * Muestra u oculta mensaje de "no hay resultados"
 * @param {string} type - Tipo de escuelas
 * @param {boolean} show - Si mostrar el mensaje
 */
function showNoResultsMessage(type, show) {
    let message = document.getElementById('no-results-' + type);
    
    if (show && !message) {
        // Crear mensaje si no existe
        message = document.createElement('div');
        message.id = 'no-results-' + type;
        message.className = 'no-results-message';
        message.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                <h4>No se encontraron resultados</h4>
                <p>Intenta ajustar los filtros o el término de búsqueda.</p>
            </div>
        `;
        
        const tableContainer = document.getElementById(type + '-content').querySelector('.directorio-table-container');
        tableContainer.appendChild(message);
    }
    
    if (message) {
        message.style.display = show ? 'block' : 'none';
    }
}

/**
 * Actualiza las estadísticas mostradas
 */
function updateStats() {
    // Aplicar ordenamiento inicial a ambas tablas
    ['publicas', 'privadas'].forEach(type => {
        const table = document.getElementById('tabla-' + type);
        if (table) {
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            sortTableByLevelAndStudents(rows, tbody);
        }
    });
    
    // Actualizar contadores de ambas tablas
    updateSchoolCount('publicas');
    updateSchoolCount('privadas');
}

/**
 * Exporta el directorio a Excel o PDF
 * @param {string} format - Formato de exportación ('excel' o 'pdf')
 * @param {string} type - Tipo de escuelas ('publicas' o 'privadas')
 */
function exportarDirectorio(format, type) {
    const tipoEscuelas = type === 'publicas' ? 'Públicas' : 'Privadas';
    const municipio = typeof municipioActual !== 'undefined' ? municipioActual : 'Corregidora';

    // Obtener el filtro de nivel activo
    const nivelFilter = document.getElementById('nivel-filter-' + type);
    const nivelSeleccionado = nivelFilter ? nivelFilter.value : 'todos';
    const nivelTexto = nivelFilter && nivelSeleccionado !== 'todos'
        ? nivelFilter.options[nivelFilter.selectedIndex].text
        : 'Todos los niveles';

    if (format === 'excel') {
        exportToExcel(type, tipoEscuelas, nivelSeleccionado, nivelTexto, municipio);
    } else if (format === 'pdf') {
        exportToPDF(type, tipoEscuelas, nivelSeleccionado, nivelTexto, municipio);
    }
}

/**
 * Exporta a Excel usando SheetJS
 * @param {string} type - Tipo de escuelas
 * @param {string} tipoEscuelas - Nombre del tipo para el archivo
 * @param {string} nivelSeleccionado - Nivel educativo seleccionado
 * @param {string} nivelTexto - Texto descriptivo del nivel
 * @param {string} municipio - Nombre del municipio
 */
function exportToExcel(type, tipoEscuelas, nivelSeleccionado, nivelTexto, municipio) {
    const table = document.getElementById('tabla-' + type);
    if (!table) return;

    // Crear workbook
    const wb = XLSX.utils.book_new();

    // Obtener datos de la tabla (solo filas visibles)
    const data = getTableData(table, false);

    // Verificar si hay datos para exportar
    if (data.length <= 1) {
        alert('No hay datos visibles para exportar. Verifica los filtros aplicados.');
        return;
    }

    // Agregar información de filtros al inicio
    const dataWithHeader = [];
    dataWithHeader.push(['Directorio de Escuelas ' + tipoEscuelas]);
    dataWithHeader.push(['Municipio de ' + municipio + ', Querétaro']);
    dataWithHeader.push(['Ciclo Escolar 2024-2025']);
    dataWithHeader.push(['Nivel: ' + nivelTexto]);
    dataWithHeader.push(['Total de escuelas: ' + (data.length - 1)]);
    dataWithHeader.push(['Fecha de exportación: ' + new Date().toLocaleDateString('es-MX')]);
    dataWithHeader.push([]); // Fila vacía

    // Agregar los datos de la tabla
    dataWithHeader.push(...data);

    // Crear worksheet
    const ws = XLSX.utils.aoa_to_sheet(dataWithHeader);

    // Configurar anchos de columna
    const colWidths = [
        { wch: 25 }, // Nivel
        { wch: 15 }, // CCT
        { wch: 50 }, // Nombre
        { wch: 25 }, // Localidad
        { wch: 15 }  // Alumnos
    ];
    ws['!cols'] = colWidths;

    // Aplicar estilos al encabezado (primeras 6 filas)
    const headerRange = XLSX.utils.decode_range(ws['!ref']);
    for (let row = 0; row < 6; row++) {
        for (let col = headerRange.s.c; col <= headerRange.e.c; col++) {
            const cellAddress = XLSX.utils.encode_cell({ r: row, c: col });
            if (ws[cellAddress]) {
                ws[cellAddress].s = {
                    font: { bold: true },
                    fill: { fgColor: { rgb: "E8F4F8" } }
                };
            }
        }
    }

    // Añadir worksheet al workbook
    XLSX.utils.book_append_sheet(wb, ws, `Escuelas ${tipoEscuelas}`);

    // Generar nombre de archivo con información del filtro
    const nivelSuffix = nivelSeleccionado !== 'todos' ? '_' + nivelSeleccionado : '';
    const fileName = `Directorio_Escuelas_${tipoEscuelas}${nivelSuffix}_${municipio}.xlsx`;

    // Descargar archivo
    XLSX.writeFile(wb, fileName);

    // Mostrar mensaje de éxito
    showExportMessage('Excel', fileName);
}

/**
 * Exporta a PDF usando jsPDF
 * @param {string} type - Tipo de escuelas
 * @param {string} tipoEscuelas - Nombre del tipo para el archivo
 * @param {string} nivelSeleccionado - Nivel educativo seleccionado
 * @param {string} nivelTexto - Texto descriptivo del nivel
 * @param {string} municipio - Nombre del municipio
 */
function exportToPDF(type, tipoEscuelas, nivelSeleccionado, nivelTexto, municipio) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4'); // Orientación horizontal para más espacio

    const table = document.getElementById('tabla-' + type);
    if (!table) return;

    // Obtener datos de la tabla (solo filas visibles)
    const data = getTableData(table, false);

    // Verificar si hay datos para exportar
    if (data.length <= 1) {
        alert('No hay datos visibles para exportar. Verifica los filtros aplicados.');
        return;
    }

    const headers = data[0];
    const rows = data.slice(1);

    // Configurar encabezado del documento
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text(`Directorio de Escuelas ${tipoEscuelas}`, 15, 15);

    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text('Municipio de ' + municipio + ', Querétaro', 15, 22);
    doc.text(`Ciclo Escolar 2024-2025`, 15, 28);

    doc.setFont(undefined, 'bold');
    doc.text(`Nivel: ${nivelTexto}`, 15, 34);

    doc.setFont(undefined, 'normal');
    doc.text(`Total de escuelas: ${rows.length}`, 15, 40);
    doc.text(`Fecha de exportación: ${new Date().toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })}`, 15, 46);

    // Configurar tabla
    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 52,
        styles: {
            fontSize: 8,
            cellPadding: 2.5,
            overflow: 'linebreak',
            halign: 'left'
        },
        headStyles: {
            fillColor: [41, 128, 185],
            textColor: 255,
            fontStyle: 'bold',
            halign: 'center'
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        columnStyles: {
            0: { cellWidth: 40 },  // Nivel Educativo
            1: { cellWidth: 28 },  // CCT
            2: { cellWidth: 120 }, // Nombre de la Escuela
            3: { cellWidth: 50 },  // Localidad
            4: { cellWidth: 25, halign: 'right' } // Total Alumnos
        },
        margin: { top: 52, left: 15, right: 15 },
        didDrawPage: function(data) {
            // Pie de página
            const pageCount = doc.internal.getNumberOfPages();
            doc.setFontSize(8);
            doc.setTextColor(150);
            doc.text(
                `Página ${data.pageNumber} de ${pageCount}`,
                doc.internal.pageSize.width / 2,
                doc.internal.pageSize.height - 10,
                { align: 'center' }
            );
        }
    });

    // Generar nombre de archivo con información del filtro
    const nivelSuffix = nivelSeleccionado !== 'todos' ? '_' + nivelSeleccionado : '';
    const fileName = `Directorio_Escuelas_${tipoEscuelas}${nivelSuffix}_${municipio}.pdf`;

    // Descargar archivo
    doc.save(fileName);

    // Mostrar mensaje de éxito
    showExportMessage('PDF', fileName);
}

/**
 * Obtiene los datos de una tabla como array
 * @param {HTMLElement} table - Elemento tabla
 * @param {boolean} includeHidden - Si incluir filas ocultas
 * @returns {Array} Datos de la tabla
 */
function getTableData(table, includeHidden = true) {
    const data = [];
    
    // Obtener encabezados
    const headers = Array.from(table.querySelectorAll('th')).map(th => th.textContent.trim());
    data.push(headers);
    
    // Obtener filas de datos
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    Array.from(rows).forEach(row => {
        if (!includeHidden && row.style.display === 'none') return;
        
        const cells = Array.from(row.getElementsByTagName('td')).map(cell => {
            // Limpiar HTML y obtener solo texto
            return cell.textContent.trim();
        });
        data.push(cells);
    });
    
    return data;
}

/**
 * Muestra mensaje de confirmación de exportación
 * @param {string} format - Formato exportado
 * @param {string} fileName - Nombre del archivo
 */
function showExportMessage(format, fileName) {
    // Crear toast notification
    const toast = document.createElement('div');
    toast.className = 'export-toast';
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        z-index: 9999;
        font-weight: 600;
        animation: slideIn 0.3s ease-in-out;
    `;
    toast.innerHTML = `
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
        Archivo ${format} exportado: ${fileName}
    `;
    
    document.body.appendChild(toast);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

/**
 * Inicializa los textos originales de todas las celdas para búsqueda
 */
function initOriginalTexts() {
    const tablas = ['tabla-publicas', 'tabla-privadas'];
    
    tablas.forEach(tablaId => {
        const table = document.getElementById(tablaId);
        if (!table) return;
        
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        Array.from(rows).forEach(row => {
            const cells = row.getElementsByTagName('td');
            Array.from(cells).forEach(cell => {
                cell.dataset.originalText = cell.textContent.trim();
            });
        });
    });
}

/**
 * Ordena la tabla por nivel educativo y luego por cantidad de alumnos descendente
 * @param {Array} rows - Array de filas de la tabla
 * @param {HTMLElement} tbody - Elemento tbody de la tabla
 */
function sortTableByLevelAndStudents(rows, tbody) {
    // Definir orden correcto de niveles educativos usando códigos
    const levelOrder = {
        'inicial_esc': 1,
        'inicial_no_esc': 2,
        'especial_tot': 3,
        'preescolar': 4,
        'primaria': 5,
        'secundaria': 6,
        'media_sup': 7,
        'superior': 8
    };
    
    // Ordenar filas
    rows.sort((a, b) => {
        const aLevel = a.getAttribute('data-nivel');
        const bLevel = b.getAttribute('data-nivel');
        
        // Primero ordenar por nivel (orden correcto de niveles educativos)
        const aLevelOrder = levelOrder[aLevel] || 99;
        const bLevelOrder = levelOrder[bLevel] || 99;
        
        if (aLevelOrder !== bLevelOrder) {
            return aLevelOrder - bLevelOrder;
        }
        
        // Luego ordenar por cantidad de alumnos (descendente)
        const aStudents = parseInt(a.cells[4].textContent.replace(/,/g, '')) || 0;
        const bStudents = parseInt(b.cells[4].textContent.replace(/,/g, '')) || 0;
        
        return bStudents - aStudents; // Descendente
    });
    
    // Reordenar las filas en el DOM
    rows.forEach(row => {
        tbody.appendChild(row);
    });
}

// Añadir estilos CSS para animaciones de toast
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);