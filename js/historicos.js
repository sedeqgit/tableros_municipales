/**
 * =============================================================================
 * JAVASCRIPT PARA PÁGINA DE ANÁLISIS HISTÓRICOS - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Este archivo maneja toda la funcionalidad interactiva de la página de 
 * análisis históricos, incluyendo gráficos temporales, filtros dinámicos
 * y exportación de datos.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Gráficos de evolución temporal con Google Charts
 * - Filtros dinámicos por período y nivel educativo
 * - Comparativas público vs privado
 * - Tablas interactivas con filtrado
 * - Exportación de datos históricos
 * 
 * @package SEDEQ_Dashboard
 * @subpackage Historicos_JS
 * @version 2.0
 */

// =============================================================================
// VARIABLES GLOBALES Y CONFIGURACIÓN
// =============================================================================

let evolutionChart, levelsChart, comparisonChart;
let currentPeriod = 'all';
let currentLevel = 'all';
let currentView = 'absolute';

// Configuración de colores consistente con el sistema
const CHART_COLORS = {
    primary: '#242B57',
    secondary: '#4996C4',
    accent: '#7CC6D8',
    success: '#4CAF50',
    warning: '#FFC107',
    danger: '#F44336',
    magenta: '#E91E63',
    gray: '#9E9E9E'
};

const LEVEL_COLORS = {
    'Preescolar': '#FB8C00',
    'Primaria': '#E53935',
    'Secundaria': '#5E35B1',
    'Media Superior': '#43A047',
    'Superior': '#0288D1'
};

// =============================================================================
// INICIALIZACIÓN DE GOOGLE CHARTS
// =============================================================================

google.charts.load('current', {
    'packages': ['corechart', 'line', 'bar'],
    'language': 'es'
});

google.charts.setOnLoadCallback(initializeCharts);

function initializeCharts() {
    createEvolutionChart();
    createLevelsChart();
    createComparisonChart();
    setupEventListeners();
}

// =============================================================================
// GRÁFICO DE EVOLUCIÓN TEMPORAL
// =============================================================================

function createEvolutionChart() {
    const data = prepareEvolutionData();
    const options = {
        title: 'Evolución de la Matrícula Total por Ciclo Escolar',
        titleTextStyle: {
            fontSize: 16,
            fontName: 'Hanken Grotesk',
            color: CHART_COLORS.primary,
            bold: true
        },
        hAxes: {
            0: {
                title: 'Ciclo Escolar',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                }
            }
        },
        vAxes: {
            0: {
                title: 'Número de Estudiantes',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                },
                format: '#,###'
            }
        },
        series: {
            0: {
                color: CHART_COLORS.secondary,
                lineWidth: 3,
                pointSize: 6
            }
        },
        backgroundColor: 'white',
        chartArea: {
            left: 80,
            top: 60,
            width: '75%',
            height: '70%'
        },
        legend: {
            position: 'bottom',
            textStyle: {
                fontSize: 11,
                fontName: 'Hanken Grotesk'
            }
        },
        animation: {
            duration: 1000,
            easing: 'out',
            startup: true
        },
        curveType: 'function'
    };

    evolutionChart = new google.visualization.LineChart(
        document.getElementById('chart-evolution')
    );
    evolutionChart.draw(data, options);
}

function prepareEvolutionData() {
    const data = new google.visualization.DataTable();
    data.addColumn('string', 'Ciclo');
    data.addColumn('number', 'Total Estudiantes');

    // Filtrar datos según el período seleccionado
    const filteredData = getFilteredDataByPeriod();
    
    Object.keys(filteredData).forEach(ciclo => {
        const estudiantes = filteredData[ciclo].total_estudiantes;
        data.addRow([ciclo, estudiantes]);
    });

    return data;
}

// =============================================================================
// GRÁFICO DE EVOLUCIÓN POR NIVELES
// =============================================================================

function createLevelsChart() {
    const data = prepareLevelsData();
    const options = {
        title: 'Evolución por Nivel Educativo',
        titleTextStyle: {
            fontSize: 16,
            fontName: 'Hanken Grotesk',
            color: CHART_COLORS.primary,
            bold: true
        },
        hAxes: {
            0: {
                title: 'Ciclo Escolar',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                }
            }
        },
        vAxes: {
            0: {
                title: 'Número de Estudiantes',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                },
                format: '#,###'
            }
        },
        series: getSeriesConfiguration(),
        backgroundColor: 'white',
        chartArea: {
            left: 80,
            top: 60,
            width: '70%',
            height: '65%'
        },
        legend: {
            position: 'right',
            textStyle: {
                fontSize: 10,
                fontName: 'Hanken Grotesk'
            }
        },
        animation: {
            duration: 1000,
            easing: 'out',
            startup: true
        },
        curveType: 'function'
    };

    levelsChart = new google.visualization.LineChart(
        document.getElementById('chart-levels')
    );
    levelsChart.draw(data, options);
}

function prepareLevelsData() {
    const data = new google.visualization.DataTable();
    data.addColumn('string', 'Ciclo');

    // Añadir columnas según el nivel seleccionado
    if (currentLevel === 'all') {
        Object.keys(LEVEL_COLORS).forEach(nivel => {
            data.addColumn('number', nivel);
        });
    } else {
        data.addColumn('number', currentLevel);
    }

    // Añadir datos
    const ciclos = Object.keys(evolucionPorNivel[Object.keys(evolucionPorNivel)[0]]);
    ciclos.forEach(ciclo => {
        const row = [ciclo];
        
        if (currentLevel === 'all') {
            Object.keys(LEVEL_COLORS).forEach(nivel => {
                const valor = evolucionPorNivel[nivel] && evolucionPorNivel[nivel][ciclo] 
                    ? evolucionPorNivel[nivel][ciclo] 
                    : 0;
                row.push(valor);
            });
        } else {
            const valor = evolucionPorNivel[currentLevel] && evolucionPorNivel[currentLevel][ciclo]
                ? evolucionPorNivel[currentLevel][ciclo]
                : 0;
            row.push(valor);
        }
        
        data.addRow(row);
    });

    return data;
}

function getSeriesConfiguration() {
    const series = {};
    
    if (currentLevel === 'all') {
        Object.keys(LEVEL_COLORS).forEach((nivel, index) => {
            series[index] = {
                color: LEVEL_COLORS[nivel],
                lineWidth: 2,
                pointSize: 4
            };
        });
    } else {
        series[0] = {
            color: LEVEL_COLORS[currentLevel] || CHART_COLORS.secondary,
            lineWidth: 3,
            pointSize: 6
        };
    }
    
    return series;
}

// =============================================================================
// GRÁFICO DE COMPARACIÓN PÚBLICO VS PRIVADO
// =============================================================================

function createComparisonChart() {
    const data = prepareComparisonData();
    const options = {
        title: 'Evolución Escuelas Públicas vs Privadas',
        titleTextStyle: {
            fontSize: 16,
            fontName: 'Hanken Grotesk',
            color: CHART_COLORS.primary,
            bold: true
        },
        hAxes: {
            0: {
                title: 'Ciclo Escolar',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                }
            }
        },
        vAxes: {
            0: {
                title: currentView === 'absolute' ? 'Número de Escuelas' : 'Porcentaje',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                },
                format: currentView === 'absolute' ? '#,###' : '#\'%\''
            }
        },
        series: {
            0: {
                color: CHART_COLORS.secondary,
                lineWidth: 3,
                pointSize: 6
            },
            1: {
                color: CHART_COLORS.gray,
                lineWidth: 3,
                pointSize: 6
            }
        },
        backgroundColor: 'white',
        chartArea: {
            left: 80,
            top: 60,
            width: '75%',
            height: '70%'
        },
        legend: {
            position: 'bottom',
            textStyle: {
                fontSize: 11,
                fontName: 'Hanken Grotesk'
            }
        },
        animation: {
            duration: 1000,
            easing: 'out',
            startup: true
        },
        curveType: 'function'
    };

    comparisonChart = new google.visualization.LineChart(
        document.getElementById('chart-comparison')
    );
    comparisonChart.draw(data, options);
}

function prepareComparisonData() {
    const data = new google.visualization.DataTable();
    data.addColumn('string', 'Ciclo');
    data.addColumn('number', 'Públicas');
    data.addColumn('number', 'Privadas');

    Object.keys(matriculaHistorica).forEach(ciclo => {
        const datos = matriculaHistorica[ciclo];
        let publicas, privadas;
        
        if (currentView === 'absolute') {
            publicas = datos.escuelas_publicas;
            privadas = datos.escuelas_privadas;
        } else {
            const total = datos.escuelas_publicas + datos.escuelas_privadas;
            publicas = Math.round((datos.escuelas_publicas / total) * 100);
            privadas = Math.round((datos.escuelas_privadas / total) * 100);
        }
        
        data.addRow([ciclo, publicas, privadas]);
    });

    return data;
}

// =============================================================================
// MANEJO DE EVENTOS Y FILTROS
// =============================================================================

function setupEventListeners() {
    // Filtros de período temporal
    document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            updateEvolutionChart();
        });
    });

    // Filtros de nivel educativo
    document.querySelectorAll('.level-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.level-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentLevel = this.dataset.level;
            updateLevelsChart();
        });
    });

    // Radio buttons para vista de comparación
    document.querySelectorAll('input[name="comparison-view"]').forEach(radio => {
        radio.addEventListener('change', function() {
            currentView = this.value;
            updateComparisonChart();
        });
    });

    // Filtro de período para tabla
    const periodFilter = document.getElementById('period-filter');
    if (periodFilter) {
        periodFilter.addEventListener('change', function() {
            filterHistoricalTable(this.value);
        });
    }

    // Botón de exportación
    const exportBtn = document.getElementById('export-historical-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportHistoricalData();
        });
    }

    // Redimensionamiento de ventana
    window.addEventListener('resize', debounce(resizeCharts, 300));
}

// =============================================================================
// FUNCIONES DE ACTUALIZACIÓN DE GRÁFICOS
// =============================================================================

function updateEvolutionChart() {
    if (evolutionChart) {
        const data = prepareEvolutionData();
        evolutionChart.draw(data, getEvolutionOptions());
    }
}

function updateLevelsChart() {
    if (levelsChart) {
        const data = prepareLevelsData();
        const options = getLevelsOptions();
        levelsChart.draw(data, options);
    }
}

function updateComparisonChart() {
    if (comparisonChart) {
        const data = prepareComparisonData();
        const options = getComparisonOptions();
        comparisonChart.draw(data, options);
    }
}

function resizeCharts() {
    if (evolutionChart) updateEvolutionChart();
    if (levelsChart) updateLevelsChart();
    if (comparisonChart) updateComparisonChart();
}

// =============================================================================
// FUNCIONES DE FILTRADO DE DATOS
// =============================================================================

function getFilteredDataByPeriod() {
    switch (currentPeriod) {
        case 'recent':
            return getRecentYearsData();
        case 'pandemic':
            return getPandemicPeriodData();
        default:
            return matriculaHistorica;
    }
}

function getRecentYearsData() {
    const recentYears = {};
    const allYears = Object.keys(matriculaHistorica);
    const startIndex = Math.max(0, allYears.length - 5);
    
    for (let i = startIndex; i < allYears.length; i++) {
        const year = allYears[i];
        recentYears[year] = matriculaHistorica[year];
    }
    
    return recentYears;
}

function getPandemicPeriodData() {
    const pandemicYears = {};
    const relevantYears = ['2019-2020', '2020-2021', '2021-2022', '2022-2023', '2023-2024'];
    
    relevantYears.forEach(year => {
        if (matriculaHistorica[year]) {
            pandemicYears[year] = matriculaHistorica[year];
        }
    });
    
    return pandemicYears;
}

// =============================================================================
// FILTRADO DE TABLA HISTÓRICA
// =============================================================================

function filterHistoricalTable(period) {
    const table = document.getElementById('historical-data-table');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const ciclo = row.querySelector('.period-cell').textContent;
        let show = true;
        
        switch (period) {
            case '2020-2024':
                show = ciclo >= '2020-2021';
                break;
            case '2015-2019':
                show = ciclo <= '2019-2020';
                break;
            default:
                show = true;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// =============================================================================
// EXPORTACIÓN DE DATOS
// =============================================================================

function exportHistoricalData() {
    try {
        // Preparar datos para exportación
        const exportData = prepareExportData();
        
        // Crear libro de Excel
        const wb = XLSX.utils.book_new();
        
        // Hoja 1: Datos generales
        const generalData = prepareGeneralDataSheet();
        const ws1 = XLSX.utils.aoa_to_sheet(generalData);
        XLSX.utils.book_append_sheet(wb, ws1, "Datos Generales");
        
        // Hoja 2: Evolución por nivel
        const levelData = prepareLevelDataSheet();
        const ws2 = XLSX.utils.aoa_to_sheet(levelData);
        XLSX.utils.book_append_sheet(wb, ws2, "Por Nivel Educativo");
        
        // Hoja 3: Indicadores
        const indicatorData = prepareIndicatorSheet();
        const ws3 = XLSX.utils.aoa_to_sheet(indicatorData);
        XLSX.utils.book_append_sheet(wb, ws3, "Indicadores");
        
        // Descargar archivo
        const fecha = new Date().toISOString().split('T')[0];
        const filename = `Historicos_Educativos_Corregidora_${fecha}.xlsx`;
        XLSX.writeFile(wb, filename);
        
        // Mostrar mensaje de éxito
        showExportSuccess();
        
    } catch (error) {
        console.error('Error al exportar datos históricos:', error);
        showExportError();
    }
}

function prepareGeneralDataSheet() {
    const headers = [
        'Ciclo Escolar',
        'Total Estudiantes',
        'Total Escuelas',
        'Total Docentes',
        'Escuelas Públicas',
        'Escuelas Privadas',
        'Variación Anual (%)',
        'Ratio Estudiante/Docente'
    ];
    
    const data = [headers];
    
    const ciclos = Object.keys(matriculaHistorica);
    ciclos.forEach((ciclo, index) => {
        const datos = matriculaHistorica[ciclo];
        
        // Calcular variación anual
        let variacion = 0;
        if (index > 0) {
            const cicloAnterior = ciclos[index - 1];
            const estudiantesActuales = datos.total_estudiantes;
            const estudiantesAnteriores = matriculaHistorica[cicloAnterior].total_estudiantes;
            variacion = ((estudiantesActuales - estudiantesAnteriores) / estudiantesAnteriores) * 100;
        }
        
        const ratio = Math.round(datos.total_estudiantes / datos.docentes * 10) / 10;
        
        data.push([
            ciclo,
            datos.total_estudiantes,
            datos.total_escuelas,
            datos.docentes,
            datos.escuelas_publicas,
            datos.escuelas_privadas,
            Math.round(variacion * 10) / 10,
            ratio
        ]);
    });
    
    return data;
}

function prepareLevelDataSheet() {
    const headers = ['Ciclo Escolar'];
    const niveles = Object.keys(evolucionPorNivel);
    headers.push(...niveles);
    
    const data = [headers];
    
    // Obtener todos los ciclos disponibles
    const ciclos = Object.keys(evolucionPorNivel[niveles[0]]);
    
    ciclos.forEach(ciclo => {
        const row = [ciclo];
        niveles.forEach(nivel => {
            const valor = evolucionPorNivel[nivel][ciclo] || 0;
            row.push(valor);
        });
        data.push(row);
    });
    
    return data;
}

function prepareIndicatorSheet() {
    const data = [
        ['Indicador', 'Valor', 'Unidad'],
        ['Crecimiento Promedio Anual', indicadoresHistoricos.crecimiento_promedio_anual, '%'],
        ['Variación 2020-2021 (Pandemia)', indicadoresHistoricos.variacion_2020_2021, '%'],
        ['Recuperación 2021-2024', indicadoresHistoricos.recuperacion_2021_2024, '%'],
        ['Tasa Cobertura Preescolar', indicadoresHistoricos.tasa_cobertura_preescolar, '%'],
        ['Tasa Cobertura Primaria', indicadoresHistoricos.tasa_cobertura_primaria, '%'],
        ['Tasa Cobertura Secundaria', indicadoresHistoricos.tasa_cobertura_secundaria, '%'],
        ['Ratio Estudiantes/Docente', indicadoresHistoricos.ratio_estudiantes_docente, 'Estudiantes por docente'],
        ['Crecimiento Escuelas Privadas', indicadoresHistoricos.crecimiento_escuelas_privadas, '%']
    ];
    
    return data;
}

// =============================================================================
// FUNCIONES DE UTILIDAD
// =============================================================================

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

function showExportSuccess() {
    // Crear notificación de éxito
    const notification = document.createElement('div');
    notification.className = 'export-notification success';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>Datos históricos exportados exitosamente</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function showExportError() {
    // Crear notificación de error
    const notification = document.createElement('div');
    notification.className = 'export-notification error';
    notification.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>Error al exportar los datos. Inténtelo de nuevo.</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 4000);
}

// =============================================================================
// ESTILOS DINÁMICOS PARA NOTIFICACIONES
// =============================================================================

function addNotificationStyles() {
    const style = document.createElement('style');
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
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 14px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 10000;
            border-left: 4px solid transparent;
        }
        
        .export-notification.success {
            border-left-color: #4CAF50;
        }
        
        .export-notification.success i {
            color: #4CAF50;
        }
        
        .export-notification.error {
            border-left-color: #F44336;
        }
        
        .export-notification.error i {
            color: #F44336;
        }
        
        .export-notification.show {
            transform: translateX(0);
        }
    `;
    document.head.appendChild(style);
}

// Añadir estilos de notificación al cargar la página
document.addEventListener('DOMContentLoaded', addNotificationStyles);

// =============================================================================
// OBTENER OPCIONES DE GRÁFICOS (FUNCIONES AUXILIARES)
// =============================================================================

function getEvolutionOptions() {
    return {
        title: 'Evolución de la Matrícula Total por Ciclo Escolar',
        titleTextStyle: {
            fontSize: 16,
            fontName: 'Hanken Grotesk',
            color: CHART_COLORS.primary,
            bold: true
        },
        hAxes: {
            0: {
                title: 'Ciclo Escolar',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                }
            }
        },
        vAxes: {
            0: {
                title: 'Número de Estudiantes',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                },
                format: '#,###'
            }
        },
        series: {
            0: {
                color: CHART_COLORS.secondary,
                lineWidth: 3,
                pointSize: 6
            }
        },
        backgroundColor: 'white',
        chartArea: {
            left: 80,
            top: 60,
            width: '75%',
            height: '70%'
        },
        legend: {
            position: 'bottom',
            textStyle: {
                fontSize: 11,
                fontName: 'Hanken Grotesk'
            }
        },
        animation: {
            duration: 1000,
            easing: 'out'
        },
        curveType: 'function'
    };
}

function getLevelsOptions() {
    return {
        title: currentLevel === 'all' ? 'Evolución por Nivel Educativo' : `Evolución - ${currentLevel}`,
        titleTextStyle: {
            fontSize: 16,
            fontName: 'Hanken Grotesk',
            color: CHART_COLORS.primary,
            bold: true
        },
        hAxes: {
            0: {
                title: 'Ciclo Escolar',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                }
            }
        },
        vAxes: {
            0: {
                title: 'Número de Estudiantes',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                },
                format: '#,###'
            }
        },
        series: getSeriesConfiguration(),
        backgroundColor: 'white',
        chartArea: {
            left: 80,
            top: 60,
            width: '70%',
            height: '65%'
        },
        legend: {
            position: 'right',
            textStyle: {
                fontSize: 10,
                fontName: 'Hanken Grotesk'
            }
        },
        animation: {
            duration: 1000,
            easing: 'out'
        },
        curveType: 'function'
    };
}

function getComparisonOptions() {
    return {
        title: 'Evolución Escuelas Públicas vs Privadas',
        titleTextStyle: {
            fontSize: 16,
            fontName: 'Hanken Grotesk',
            color: CHART_COLORS.primary,
            bold: true
        },
        hAxes: {
            0: {
                title: 'Ciclo Escolar',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                }
            }
        },
        vAxes: {
            0: {
                title: currentView === 'absolute' ? 'Número de Escuelas' : 'Porcentaje',
                titleTextStyle: {
                    fontSize: 12,
                    fontName: 'Hanken Grotesk',
                    color: CHART_COLORS.primary
                },
                textStyle: {
                    fontSize: 11,
                    fontName: 'Hanken Grotesk'
                },
                format: currentView === 'absolute' ? '#,###' : '#\'%\''
            }
        },
        series: {
            0: {
                color: CHART_COLORS.secondary,
                lineWidth: 3,
                pointSize: 6
            },
            1: {
                color: CHART_COLORS.gray,
                lineWidth: 3,
                pointSize: 6
            }
        },
        backgroundColor: 'white',
        chartArea: {
            left: 80,
            top: 60,
            width: '75%',
            height: '70%'
        },
        legend: {
            position: 'bottom',
            textStyle: {
                fontSize: 11,
                fontName: 'Hanken Grotesk'
            }
        },
        animation: {
            duration: 1000,
            easing: 'out'
        },
        curveType: 'function'
    };
}
