/**
 * JavaScript específico para la página de Docentes
 * Archivo: docentes.js
 * Versión: 1.0
 */

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todas las funcionalidades
    initializeAnimations();
    initializeTooltips();
    initializeInteractiveElements();
    
    // Configurar redimensionamiento de gráficos
    window.addEventListener('resize', debounce(resizeCharts, 250));
});

/**
 * Inicializar animaciones secuenciales
 */
function initializeAnimations() {
    const animatedElements = document.querySelectorAll('.animate-up, .animate-fade, .animate-sequence');
    
    // Observador de intersección para animaciones al hacer scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

/**
 * Inicializar tooltips informativos
 */
function initializeTooltips() {
    // Crear tooltips para elementos con información adicional
    const statBoxes = document.querySelectorAll('.stat-box');
    
    statBoxes.forEach(box => {
        box.addEventListener('mouseenter', function() {
            // Agregar efecto visual al pasar el mouse
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 25px rgba(36, 43, 87, 0.15)';
        });
        
        box.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
}

/**
 * Inicializar elementos interactivos
 */
function initializeInteractiveElements() {
    // Hacer las filas de la tabla clickeables para más información
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            // Resaltar la fila seleccionada
            tableRows.forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');
            
            // Aquí se podría agregar funcionalidad para mostrar más detalles
            const nivel = this.cells[0].textContent;
            const subnivel = this.cells[1].textContent;
            showDocenteDetails(nivel, subnivel);
        });
    });
    
    // Agregar funcionalidad de filtro rápido
    addQuickFilter();
}

/**
 * Mostrar detalles adicionales de un nivel educativo
 */
function showDocenteDetails(nivel, subnivel) {
    // Crear modal o panel con información adicional
    const details = `
        <div class="docente-details-modal">
            <h3>Detalles: ${nivel} - ${subnivel}</h3>
            <p>Información adicional sobre este nivel educativo podría incluir:</p>
            <ul>
                <li>Distribución por género</li>
                <li>Años de experiencia promedio</li>
                <li>Formación académica</li>
                <li>Capacitaciones recientes</li>
            </ul>
        </div>
    `;
    
    // Por ahora, mostrar en consola (se puede expandir con un modal real)
    console.log(`Detalles solicitados para: ${nivel} - ${subnivel}`);
}

/**
 * Agregar filtro rápido para la tabla
 */
function addQuickFilter() {
    const tableContainer = document.querySelector('.detailed-table');
    if (!tableContainer) return;
    
    // Crear input de filtro
    const filterContainer = document.createElement('div');
    filterContainer.className = 'table-filter';
    filterContainer.innerHTML = `
        <div class="filter-input-group">
            <i class="fas fa-search"></i>
            <input type="text" id="docente-filter" placeholder="Buscar por nivel o subnivel...">
            <button type="button" id="clear-filter" title="Limpiar filtro">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Insertar antes de la tabla
    tableContainer.insertBefore(filterContainer, tableContainer.querySelector('.table-responsive'));
    
    // Funcionalidad del filtro
    const filterInput = document.getElementById('docente-filter');
    const clearButton = document.getElementById('clear-filter');
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    
    filterInput.addEventListener('input', function() {
        const filterValue = this.value.toLowerCase();
        
        tableRows.forEach(row => {
            const nivel = row.cells[0].textContent.toLowerCase();
            const subnivel = row.cells[1].textContent.toLowerCase();
            
            if (nivel.includes(filterValue) || subnivel.includes(filterValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Mostrar/ocultar botón de limpiar
        clearButton.style.display = filterValue ? 'block' : 'none';
    });
    
    clearButton.addEventListener('click', function() {
        filterInput.value = '';
        tableRows.forEach(row => row.style.display = '');
        this.style.display = 'none';
    });
}

/**
 * Redimensionar gráficos de Google Charts
 */
function resizeCharts() {
    if (typeof google !== 'undefined' && google.visualization) {
        // Redibujar gráficos principales
        if (window.docentesChart) {
            drawDocentesNivelChart();
        }
    }
}

/**
 * Función debounce para optimizar eventos de redimensionamiento
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Funciones de exportación mejoradas
 */
function exportarExcelMejorado() {
    try {
        // Mostrar indicador de carga
        const button = event.target.closest('.export-btn');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exportando...';
        button.disabled = true;
        
        // Datos para exportar
        const data = [
            ['Nivel Educativo', 'Subnivel', 'Cantidad de Docentes', 'Porcentaje del Total'],
            // Los datos se agregan dinámicamente desde PHP
        ];
        
        // Agregar datos de la tabla
        const tableRows = document.querySelectorAll('.data-table tbody tr');
        tableRows.forEach(row => {
            if (row.style.display !== 'none') { // Solo filas visibles
                const cells = row.querySelectorAll('td');
                data.push([
                    cells[0].textContent,
                    cells[1].textContent,
                    cells[2].textContent,
                    cells[3].textContent
                ]);
            }
        });
        
        // Crear y descargar archivo
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        
        // Establecer ancho de columnas
        ws['!cols'] = [
            { width: 25 },
            { width: 20 },
            { width: 20 },
            { width: 20 }
        ];
        
        XLSX.utils.book_append_sheet(wb, ws, 'Docentes Corregidora');
        XLSX.writeFile(wb, `docentes_corregidora_${new Date().toISOString().split('T')[0]}.xlsx`);
        
        // Restaurar botón
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 1500);
        
    } catch (error) {
        console.error('Error al exportar Excel:', error);
        alert('Error al exportar el archivo Excel. Por favor, intente nuevamente.');
    }
}

/**
 * Añadir estilos CSS para elementos dinámicos
 */
function addDynamicStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .table-filter {
            margin-bottom: 1rem;
        }
        
        .filter-input-group {
            position: relative;
            max-width: 300px;
        }
        
        .filter-input-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .filter-input-group input {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 2.5rem;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition-normal);
        }
        
        .filter-input-group input:focus {
            outline: none;
            border-color: var(--docentes-primary);
            box-shadow: 0 0 0 3px rgba(73, 150, 196, 0.1);
        }
        
        .filter-input-group button {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            display: none;
        }
        
        .filter-input-group button:hover {
            background: var(--light-gray);
            color: var(--text-primary);
        }
        
        .data-table tbody tr.selected {
            background-color: rgba(73, 150, 196, 0.1) !important;
        }
        
        .data-table tbody tr {
            cursor: pointer;
        }
    `;
    document.head.appendChild(style);
}

// Inicializar estilos dinámicos
addDynamicStyles();
