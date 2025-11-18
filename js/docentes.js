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
    initializeViewToggle(); // Cambio de vista barras/gráfico
    initializeGoogleCharts(); // Gráficos de Google Charts

    // Guardar totales originales para restaurar después de filtrar
    setTimeout(guardarTotalesOriginales, 500);

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

        // Variables para acumular totales filtrados
        let totalDocentesFiltrado = 0;
        let totalHombresFiltrado = 0;
        let totalMujeresFiltrado = 0;

        tableRows.forEach(row => {
            const nivel = row.cells[0].textContent.toLowerCase();
            const subnivel = row.cells[1].textContent.toLowerCase();

            if (nivel.includes(filterValue) || subnivel.includes(filterValue)) {
                row.style.display = '';

                // Acumular los totales de las filas visibles
                const totalDocentes = parseInt(row.cells[2].textContent.replace(/,/g, '')) || 0;
                const hombres = parseInt(row.cells[4].textContent.replace(/,/g, '')) || 0;
                const mujeres = parseInt(row.cells[6].textContent.replace(/,/g, '')) || 0;

                totalDocentesFiltrado += totalDocentes;
                totalHombresFiltrado += hombres;
                totalMujeresFiltrado += mujeres;
            } else {
                row.style.display = 'none';
            }
        });

        // Actualizar la fila de totales
        actualizarFilaTotales(totalDocentesFiltrado, totalHombresFiltrado, totalMujeresFiltrado);

        // Mostrar/ocultar botón de limpiar
        clearButton.style.display = filterValue ? 'block' : 'none';
    });
    
    clearButton.addEventListener('click', function() {
        filterInput.value = '';
        tableRows.forEach(row => row.style.display = '');
        this.style.display = 'none';

        // Restaurar totales originales
        restaurarTotalesOriginales();
    });
}

/**
 * Actualizar la fila de totales con los valores filtrados
 */
function actualizarFilaTotales(totalDocentes, totalHombres, totalMujeres) {
    const totalRow = document.querySelector('.data-table tfoot .total-row');
    if (!totalRow) return;

    // Calcular porcentajes
    const porcentajeHombres = totalDocentes > 0 ? ((totalHombres / totalDocentes) * 100).toFixed(1) : 0;
    const porcentajeMujeres = totalDocentes > 0 ? ((totalMujeres / totalDocentes) * 100).toFixed(1) : 0;

    // Actualizar las celdas (índices según la estructura de la tabla)
    const cells = totalRow.querySelectorAll('td');

    // Total docentes (columna 2)
    if (cells[1]) cells[1].querySelector('strong').textContent = totalDocentes.toLocaleString();

    // Total hombres (columna 4)
    if (cells[3]) cells[3].querySelector('strong').textContent = totalHombres.toLocaleString();

    // Porcentaje hombres (columna 5)
    if (cells[4]) cells[4].querySelector('strong').textContent = porcentajeHombres + '%';

    // Total mujeres (columna 6)
    if (cells[5]) cells[5].querySelector('strong').textContent = totalMujeres.toLocaleString();

    // Porcentaje mujeres (columna 7)
    if (cells[6]) cells[6].querySelector('strong').textContent = porcentajeMujeres + '%';
}

/**
 * Restaurar los totales originales (guardados al cargar la página)
 */
let totalesOriginales = null;

function guardarTotalesOriginales() {
    const totalRow = document.querySelector('.data-table tfoot .total-row');
    if (!totalRow) return;

    const cells = totalRow.querySelectorAll('td strong');
    totalesOriginales = {
        totalDocentes: cells[1] ? cells[1].textContent : '',
        totalHombres: cells[3] ? cells[3].textContent : '',
        porcentajeHombres: cells[4] ? cells[4].textContent : '',
        totalMujeres: cells[5] ? cells[5].textContent : '',
        porcentajeMujeres: cells[6] ? cells[6].textContent : ''
    };
}

function restaurarTotalesOriginales() {
    if (!totalesOriginales) return;

    const totalRow = document.querySelector('.data-table tfoot .total-row');
    if (!totalRow) return;

    const cells = totalRow.querySelectorAll('td strong');
    if (cells[1]) cells[1].textContent = totalesOriginales.totalDocentes;
    if (cells[3]) cells[3].textContent = totalesOriginales.totalHombres;
    if (cells[4]) cells[4].textContent = totalesOriginales.porcentajeHombres;
    if (cells[5]) cells[5].textContent = totalesOriginales.totalMujeres;
    if (cells[6]) cells[6].textContent = totalesOriginales.porcentajeMujeres;
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
    console.log(`Aplicando filtro: ${tipo}`);

    const barrasNivel = document.querySelectorAll('.level-bar');

    // Guardar el tipo de filtro actual
    window.tipoFiltroActual = tipo;
    
    barrasNivel.forEach(bar => {
        const nombreNivel = bar.querySelector('.level-name').textContent.trim();
        const docentesCount = bar.querySelector('.escuelas-count');
        const levelFill = bar.querySelector('.level-fill');
        const levelPercent = bar.querySelector('.level-percent');
        
        console.log(`Procesando nivel: ${nombreNivel}`);
        
        try {
            // Verificar si tenemos datos de sostenimiento para este nivel
            if (tipo === 'total') {
                // Si es total, restauramos los valores originales guardados
                if (docentesCount && valoresOriginalesDocentes[nombreNivel]) {
                    docentesCount.textContent = valoresOriginalesDocentes[nombreNivel].cantidad;
                    console.log(`✓ Restaurado ${nombreNivel}: ${valoresOriginalesDocentes[nombreNivel].cantidad} docentes`);
                } else {
                    console.warn(`✗ No se pudo restaurar la cantidad para ${nombreNivel}`);
                }

                if (levelFill && valoresOriginalesDocentes[nombreNivel]) {
                    levelFill.style.width = valoresOriginalesDocentes[nombreNivel].porcentaje;
                }

                if (levelPercent && valoresOriginalesDocentes[nombreNivel]) {
                    levelPercent.textContent = valoresOriginalesDocentes[nombreNivel].porcentaje;
                }
            } else {
                // Buscamos datos específicos de sostenimiento
                const nivelData = buscarDatosDocentesSostenimiento(nombreNivel);
                
                if (nivelData) {
                    let cantidad = 0;
                    let porcentaje = 0;
                    let totalReferencia = 0;
                    
                    if (tipo === 'publico') {
                        cantidad = nivelData.publicos || 0;
                        totalReferencia = docentesPublicos;
                        console.log(`✓ Encontrado ${nombreNivel}: ${cantidad} docentes públicos`);
                    } else if (tipo === 'privado') {
                        cantidad = nivelData.privados || 0;
                        totalReferencia = docentesPrivados;
                        console.log(`✓ Encontrado ${nombreNivel}: ${cantidad} docentes privados`);
                    }
                    
                    // Calcular porcentaje si hay un total de referencia válido
                    if (totalReferencia > 0) {
                        porcentaje = Math.round((cantidad / totalReferencia) * 100);
                    } else {
                        console.warn(`Total de referencia inválido para ${nombreNivel}: ${totalReferencia}`);
                    }
                    
                    // Actualizar la interfaz
                    if (docentesCount) {
                        docentesCount.textContent = cantidad;
                    }
                    if (levelFill) {
                        levelFill.style.width = porcentaje + '%';
                    }
                    if (levelPercent) {
                        levelPercent.textContent = porcentaje + '%';
                    }

                    console.log(`✓ Actualizado ${nombreNivel}: ${cantidad} docentes (${porcentaje}%)`);
                } else {
                    console.warn(`✗ No se encontraron datos para el nivel "${nombreNivel}" con filtro "${tipo}"`);
                    // Se podría considerar mostrar un valor de cero o un mensaje de "N/A"
                    if (docentesCount) docentesCount.textContent = '0';
                    if (levelFill) levelFill.style.width = '0%';
                    if (levelPercent) levelPercent.textContent = '0%';
                }
            }
        } catch (error) {
            console.error(`Error al procesar el nivel ${nombreNivel}:`, error);
            // Intentamos restaurar los valores originales en caso de error
            resetearFiltrosDocentes();
        }
    });

    // Redibujar el gráfico con los datos filtrados si está visible
    if (document.getElementById('vista-grafico').style.display !== 'none') {
        setTimeout(() => {
            drawDocentesNivelChart();
        }, 100);
    }

    console.log(`Filtro "${tipo}" aplicado correctamente a todos los niveles`);
}

function buscarDatosDocentesSostenimiento(nombreNivel) {
    if (typeof docentesNivelSostenimiento === 'undefined') {
        return null;
    }
    
    // Mapeo de nombres exactamente igual que en escuelas
    const mapaCoincidencias = {
        'Inicial E': 'Inicial (Escolarizado)',
        'Inicial NE': 'Inicial (No Escolarizado)',
        'Inicial (E)': 'Inicial (Escolarizado)',
        'Inicial (NE)': 'Inicial (No Escolarizado)',
        'Especial': 'Especial (CAM)',
        'Media Sup.': 'Media Superior',
        'Preescolar': 'Preescolar',
        'Primaria': 'Primaria',
        'Secundaria': 'Secundaria',
        'Superior': 'Superior'
    };
    
    // 1. Intentar encontrar la coincidencia en nuestro mapa
    if (mapaCoincidencias[nombreNivel] && docentesNivelSostenimiento[mapaCoincidencias[nombreNivel]]) {
        return docentesNivelSostenimiento[mapaCoincidencias[nombreNivel]];
    }
    
    // 2. Intentar coincidencia directa
    if (docentesNivelSostenimiento[nombreNivel]) {
        return docentesNivelSostenimiento[nombreNivel];
    }
    
    // 3. Buscar por coincidencia normalizada (sin espacios, sin case)
    const nombreNormalizado = nombreNivel.toLowerCase().trim();
    for (let clave in docentesNivelSostenimiento) {
        const claveNormalizada = clave.toLowerCase().trim();
        if (claveNormalizada.includes(nombreNormalizado) || nombreNormalizado.includes(claveNormalizada)) {
            return docentesNivelSostenimiento[clave];
        }
    }
    
    return null;
}

/**
 * Función para resetear todos los filtros en caso de error
 */
function resetearFiltrosDocentes() {
    console.warn('Reseteando todos los filtros a valores iniciales...');
    
    // Marcar el botón total como activo
    const filterButtons = document.querySelectorAll('.sostenimiento-filters .filter-btn');
    filterButtons.forEach(btn => {
        if (btn.getAttribute('data-filter') === 'total') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Restaurar todos los valores originales
    const barrasNivel = document.querySelectorAll('.level-bar');
    barrasNivel.forEach(bar => {
        const nombreNivel = bar.querySelector('.level-name').textContent.trim();
        const docentesCount = bar.querySelector('.escuelas-count');
        const levelFill = bar.querySelector('.level-fill');
        const levelPercent = bar.querySelector('.level-percent');

        if (valoresOriginalesDocentes[nombreNivel]) {
            if (docentesCount) docentesCount.textContent = valoresOriginalesDocentes[nombreNivel].cantidad;
            if (levelFill) levelFill.style.width = valoresOriginalesDocentes[nombreNivel].porcentaje;
            if (levelPercent) levelPercent.textContent = valoresOriginalesDocentes[nombreNivel].porcentaje;
        }
    });

    // Resetear tipo de filtro
    window.tipoFiltroActual = 'total';

    // Redibujar el gráfico si está visible
    if (document.getElementById('vista-grafico').style.display !== 'none') {
        setTimeout(() => {
            drawDocentesNivelChart();
        }, 100);
    }

    console.log('Filtros reseteados correctamente');
}

/**
 * =============================================
 * SISTEMA DE CAMBIO DE VISTA BARRAS/GRÁFICO
 * =============================================
 */

/**
 * Inicializar funcionalidad de toggle entre vista barras y gráfico
 */
function initializeViewToggle() {
    const toggleButtons = document.querySelectorAll('.view-toggle-btn');

    if (toggleButtons.length === 0) {
        console.warn('No se encontraron botones de toggle de vista');
        return;
    }

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');

            // Remover clase activa de todos los botones
            toggleButtons.forEach(btn => btn.classList.remove('active'));

            // Agregar clase activa al botón clickeado
            this.classList.add('active');

            // Cambiar vista
            switchView(viewType);
        });
    });

    console.log('Sistema de toggle de vista inicializado correctamente');
}

/**
 * Cambiar entre vista de barras y gráfico
 */
function switchView(viewType) {
    const vistaBarras = document.getElementById('vista-barras');
    const vistaGrafico = document.getElementById('vista-grafico');

    if (!vistaBarras || !vistaGrafico) {
        console.error('No se encontraron los contenedores de vista');
        return;
    }

    if (viewType === 'barras') {
        vistaBarras.style.display = 'block';
        vistaGrafico.style.display = 'none';
        console.log('Cambiado a vista de barras');
    } else if (viewType === 'grafico') {
        vistaBarras.style.display = 'none';
        vistaGrafico.style.display = 'block';

        // Redibujar el gráfico si es necesario
        if (typeof drawDocentesNivelChart === 'function') {
            setTimeout(() => {
                drawDocentesNivelChart();
            }, 100);
        }
        console.log('Cambiado a vista de gráfico');
    }
}

/**
 * =============================================
 * GRÁFICOS DE GOOGLE CHARTS
 * =============================================
 */

/**
 * Inicializar Google Charts
 */
function initializeGoogleCharts() {
    if (typeof google === 'undefined') {
        console.error('Google Charts no está disponible');
        return;
    }

    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(setupChartsDocentes);
}

/**
 * Configurar gráficos de docentes
 */
function setupChartsDocentes() {
    // Verificar si tenemos los datos necesarios
    if (typeof docentesPorNivel === 'undefined') {
        console.error('Los datos de docentes por nivel no están disponibles');
        return;
    }

    console.log('Configurando gráficos de docentes...');

    // Configurar el gráfico inicial si es necesario
    window.docentesChart = true;

    // Dibujar el gráfico de anillo automáticamente si la vista de gráfico está visible
    const vistaGrafico = document.getElementById('vista-grafico');
    if (vistaGrafico && vistaGrafico.style.display !== 'none') {
        setTimeout(() => {
            drawDocentesNivelChart();
        }, 200);
    }
}

/**
 * Dibujar gráfico circular de distribución por nivel
 */
function drawDocentesNivelChart() {
    if (typeof google === 'undefined') {
        console.error('Google Charts no está disponible');
        return;
    }

    const tipoFiltro = window.tipoFiltroActual || 'total';
    console.log(`Creando gráfico de docentes con filtro: ${tipoFiltro}`);

    // Preparar datos para el gráfico
    const datosGrafico = prepararDatosGraficoDocentes(tipoFiltro);

    if (!datosGrafico || datosGrafico.length <= 1) {
        document.getElementById('pie-chart-nivel').innerHTML =
            '<div style="text-align: center; padding: 50px; color: #666;">No hay datos disponibles para mostrar</div>';
        return;
    }

    // Dibujar el gráfico
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(function() {
        const data = google.visualization.arrayToDataTable(datosGrafico);

        const options = {
            title: getTituloGraficoDocentes(tipoFiltro),
            titleTextStyle: {
                fontSize: 18,
                fontName: 'Inter, sans-serif',
                bold: true,
                color: '#2c3e50'
            },
            pieHole: 0.4,
            colors: [
                '#3498db', '#2ecc71', '#e74c3c', '#f39c12',
                '#9b59b6', '#1abc9c', '#e67e22', '#34495e'
            ],
            backgroundColor: 'transparent',
            legend: {
                position: 'bottom',
                alignment: 'center',
                textStyle: {
                    fontSize: 12,
                    fontName: 'Inter, sans-serif'
                }
            },
            chartArea: {
                left: 10,
                top: 50,
                width: '90%',
                height: '75%'
            },
            tooltip: {
                textStyle: {
                    fontSize: 13,
                    fontName: 'Inter, sans-serif'
                },
                showColorCode: true
            },
            animation: {
                startup: true,
                duration: 1000,
                easing: 'out'
            }
        };

        const chart = new google.visualization.PieChart(document.getElementById('pie-chart-nivel'));
        chart.draw(data, options);
    });
}

// Función auxiliar para preparar datos según el filtro
function prepararDatosGraficoDocentes(tipo) {
    const datos = [['Nivel', 'Docentes']];

    if (tipo === 'total') {
        // Para total, usar los datos originales que ya funcionan
        if (typeof docentesPorNivel !== 'undefined') {
            for (const nivel in docentesPorNivel) {
                if (docentesPorNivel[nivel] > 0) {
                    datos.push([nivel, docentesPorNivel[nivel]]);
                }
            }
        }
    } else {
        // Para público/privado, leer directamente de las barras actuales
        const barrasNivel = document.querySelectorAll('.level-bar');
        barrasNivel.forEach(bar => {
            const nombreNivel = bar.querySelector('.level-name').textContent.trim();
            const cantidadTexto = bar.querySelector('.escuelas-count').textContent;
            const cantidad = parseInt(cantidadTexto.replace(/,/g, ''));

            if (cantidad > 0) {
                datos.push([nombreNivel, cantidad]);
            }
        });
    }

    return datos;
}

// Función auxiliar para obtener el título del gráfico según el filtro
function getTituloGraficoDocentes(tipo) {
    switch(tipo) {
        case 'publico':
            return 'Distribución de Docentes Públicos por Nivel Educativo';
        case 'privado':
            return 'Distribución de Docentes Privados por Nivel Educativo';
        default:
            return 'Distribución de Docentes por Nivel Educativo';
    }
}
