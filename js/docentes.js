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
    initializeSostenimientoFilters(); // Nueva funcionalidad
    
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

/**
 * =============================================
 * SISTEMA DE FILTRADO POR SOSTENIMIENTO
 * =============================================
 */

// Variables globales para filtrado
let valoresOriginalesDocentes = {};

/**
 * Inicializar sistema de filtrado por sostenimiento
 */
function initializeSostenimientoFilters() {
    console.log('=== Sistema de Filtrado de Docentes por Sostenimiento ===');
    
    // Almacenar los valores originales de cada nivel
    const barrasNivel = document.querySelectorAll('.level-bar');
    console.log(`Se encontraron ${barrasNivel.length} barras de nivel educativo`);

    // Guardar los valores iniciales
    barrasNivel.forEach(bar => {
        const nombreNivel = bar.querySelector('.level-name').textContent.trim();
        const docentesCount = bar.querySelector('.escuelas-count');
        const levelFill = bar.querySelector('.level-fill');
        const levelPercent = bar.querySelector('.level-percent');

        if (docentesCount && levelFill && levelPercent) {
            valoresOriginalesDocentes[nombreNivel] = {
                cantidad: docentesCount.textContent,
                porcentaje: levelPercent.textContent,
                ancho: levelPercent.textContent
            };
        }
    });

    // Configurar los botones de filtro
    const filterButtons = document.querySelectorAll('.sostenimiento-filters .filter-btn');
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const filterType = this.getAttribute('data-filter');
                aplicarFiltroDocentes(filterType);
            });
        });
    }

    // Animación inicial
    animateProgressBars();
}

function animateProgressBars() {
    setTimeout(function() {
        let publicBar = document.querySelector('.progress-fill.public');
        let privateBar = document.querySelector('.progress-fill.private');
        
        if (publicBar && privateBar) {
            setTimeout(() => {
                publicBar.style.transition = 'width 1.5s ease-in-out';
                privateBar.style.transition = 'width 1.5s ease-in-out';
                
                if (typeof porcentajePublicos !== 'undefined') {
                    publicBar.style.width = porcentajePublicos + '%';
                }
                if (typeof porcentajePrivados !== 'undefined') {
                    privateBar.style.width = porcentajePrivados + '%';
                }
            }, 500);
        }
    }, 1000);
}

function aplicarFiltroDocentes(tipo) {
    console.log(`Aplicando filtro de docentes: ${tipo}`);
    
    const barrasNivel = document.querySelectorAll('.level-bar');
    
    barrasNivel.forEach(bar => {
        const nombreNivel = bar.querySelector('.level-name').textContent.trim();
        const docentesCount = bar.querySelector('.escuelas-count');
        const levelFill = bar.querySelector('.level-fill');
        const levelPercent = bar.querySelector('.level-percent');
        
        try {
            if (tipo === 'total') {
                // Restaurar valores originales
                if (docentesCount && valoresOriginalesDocentes[nombreNivel]) {
                    docentesCount.textContent = valoresOriginalesDocentes[nombreNivel].cantidad;
                }
                if (levelFill && valoresOriginalesDocentes[nombreNivel]) {
                    levelFill.style.width = valoresOriginalesDocentes[nombreNivel].porcentaje;
                }
                if (levelPercent && valoresOriginalesDocentes[nombreNivel]) {
                    levelPercent.textContent = valoresOriginalesDocentes[nombreNivel].porcentaje;
                }
                bar.classList.remove('filtered', 'highlighted');
                
            } else {
                // Aplicar filtro específico
                const nivelData = buscarDatosDocentesSostenimiento(nombreNivel);
                
                if (nivelData) {
                    let cantidad = 0;
                    let porcentaje = 0;
                    let totalReferencia = 0;
                    
                    if (tipo === 'publico') {
                        cantidad = nivelData.publicos || 0;
                        totalReferencia = typeof docentesPublicos !== 'undefined' ? docentesPublicos : 0;
                    } else if (tipo === 'privado') {
                        cantidad = nivelData.privados || 0;
                        totalReferencia = typeof docentesPrivados !== 'undefined' ? docentesPrivados : 0;
                    }
                    
                    if (totalReferencia > 0) {
                        porcentaje = Math.round((cantidad / totalReferencia) * 100);
                    }
                    
                    // Actualizar interfaz
                    if (docentesCount) docentesCount.textContent = cantidad.toLocaleString();
                    if (levelFill) levelFill.style.width = porcentaje + '%';
                    if (levelPercent) levelPercent.textContent = porcentaje + '%';
                    
                    if (cantidad > 0) {
                        bar.classList.add('highlighted');
                        bar.classList.remove('filtered');
                    } else {
                        bar.classList.add('filtered');
                        bar.classList.remove('highlighted');
                    }
                } else {
                    // No hay datos - mostrar cero
                    if (docentesCount) docentesCount.textContent = '0';
                    if (levelFill) levelFill.style.width = '0%';
                    if (levelPercent) levelPercent.textContent = '0%';
                    bar.classList.add('filtered');
                    bar.classList.remove('highlighted');
                }
            }
        } catch (error) {
            console.error(`Error al procesar el nivel ${nombreNivel}:`, error);
        }
    });
}

function buscarDatosDocentesSostenimiento(nombreNivel) {
    if (typeof docentesNivelSostenimiento === 'undefined') {
        return null;
    }
    
    // Mapeo de nombres
    const mapaCoincidencias = {
        'Inicial E': 'Inicial (Escolarizado)',
        'Inicial NE': 'Inicial (No Escolarizado)',
        'Inicial (E)': 'Inicial (Escolarizado)',
        'Inicial (NE)': 'Inicial (No Escolarizado)',
        'Especial': 'Especial (CAM)',
        'Media Sup.': 'Media Superior'
    };
    
    // Buscar coincidencia exacta
    let datosEncontrados = docentesNivelSostenimiento[nombreNivel];
    
    // Buscar usando mapa
    if (!datosEncontrados && mapaCoincidencias[nombreNivel]) {
        datosEncontrados = docentesNivelSostenimiento[mapaCoincidencias[nombreNivel]];
    }
    
    // Búsqueda flexible
    if (!datosEncontrados) {
        for (let clave in docentesNivelSostenimiento) {
            if (clave.toLowerCase().includes(nombreNivel.toLowerCase()) || 
                nombreNivel.toLowerCase().includes(clave.toLowerCase())) {
                datosEncontrados = docentesNivelSostenimiento[clave];
                break;
            }
        }
    }
    
    return datosEncontrados;
}
