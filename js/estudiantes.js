/**
 * =============================================================================
 * MÓDULO DE ANÁLISIS DE MATRÍCULA ESTUDIANTIL
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Este módulo maneja toda la funcionalidad relacionada con la visualización
 * y análisis de datos de matrícula estudiantil por niveles educativos.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Visualización interactiva con Google Charts API
 * - Sistema de filtrado dinámico por año escolar y nivel educativo
 * - Exportación de datos a múltiples formatos (PDF, Excel, CSV)
 * - Análisis comparativo y evolutivo entre ciclos escolares
 * - Gestión de paleta de colores profesional y accesible
 * 
 * ARQUITECTURA TÉCNICA:
 * - Patrón Observer para actualizaciones reactivas de la interfaz
 * - Sistema de caché en memoria para optimización de rendimiento
 * - Separación de responsabilidades mediante funciones especializadas
 * - Manejo de errores robusto con fallbacks y notificaciones al usuario
 * 
 * COMPONENTES PRINCIPALES:
 * - Renderizador de gráficos (Google Charts)
 * - Motor de filtrado y agrupación de datos
 * - Sistema de exportación multi-formato
 * - Gestor de eventos de interfaz de usuario
 * - Validador de integridad de datos
 * 
 * @version 2.0.1
 * @requires Google Charts API
 * @requires datosMatricula (variable global desde PHP)
 */

// =============================================================================
// CONFIGURACIÓN Y CARGA DE LIBRERÍAS EXTERNAS
// =============================================================================

/**
 * Configuración e inicialización de Google Charts
 * 
 * Se cargan los paquetes específicos necesarios para las visualizaciones:
 * - 'corechart': Proporciona gráficos básicos (columnas, barras, líneas, etc.)
 * - 'bar': Gráficos de barras horizontales con funcionalidades avanzadas
 * 
 * El callback 'inicializarGraficos' se ejecuta cuando las librerías están listas,
 * garantizando que todos los recursos estén disponibles antes de crear visualizaciones.
 */
google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(inicializarGraficos);

// =============================================================================
// VARIABLES GLOBALES Y ESTADO DE LA APLICACIÓN
// =============================================================================

/**
 * Cache de datos de matrícula organizados por diferentes tipos de vista
 * 
 * Esta estructura optimiza el rendimiento al pre-procesar los datos en diferentes
 * agrupaciones, evitando recálculos repetitivos durante la interacción del usuario.
 * 
 * @type {Object} datosMatriculaAgrupados
 * @property {Array} todos - Dataset completo sin filtros para vista panorámica
 * @property {Object} anual - Datos agrupados por año escolar para comparativas temporales
 * @property {Object} nivel - Datos agrupados por nivel educativo para análisis sectorial
 * 
 * Estructura interna:
 * {
 *   'todos': [
 *     ['Año', 'Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior'],
 *     ['2018-2019', 120, 45, 1250, 3400, 2100, 890, 245],
 *     // ... más filas de datos
 *   ],
 *   'anual': {
 *     '2023-2024': { 'Primaria': 3500, 'Secundaria': 2200, ... },
 *     // ... más años
 *   },
 *   'nivel': {
 *     'Primaria': { '2023-2024': 3500, '2022-2023': 3450, ... },
 *     // ... más niveles
 *   }
 * }
 */
let datosMatriculaAgrupados;

/**
 * Instancia del gráfico principal de Google Charts
 * 
 * Mantiene la referencia al objeto de visualización para permitir actualizaciones
 * dinámicas sin recrear el gráfico completo, optimizando el rendimiento.
 * 
 * @type {google.visualization.ColumnChart|null} chartMatricula
 */
let chartMatricula;

/**
 * Estado del filtro de año escolar actualmente seleccionado
 * 
 * Controla qué datos temporales se muestran en la visualización.
 * El valor 'todos' muestra datos de todos los años disponibles.
 * 
 * @type {string} añoSeleccionado
 * @default 'todos'
 * @enum {'todos'|'2018-2019'|'2019-2020'|'2020-2021'|'2021-2022'|'2022-2023'|'2023-2024'}
 */
let añoSeleccionado = 'todos';

/**
 * Estado del filtro de nivel educativo actualmente seleccionado
 * 
 * Determina qué niveles educativos se incluyen en la visualización.
 * El valor 'todos' incluye todos los niveles desde Inicial NE hasta Superior.
 * 
 * @type {string} nivelSeleccionado
 * @default 'todos'
 * @enum {'todos'|'Inicial E'|'Inicial NE'|'CAM'|'Preescolar'|'Primaria'|'Secundaria'|'Media superior'|'Superior'}
 */
let nivelSeleccionado = 'todos';

/**
 * =============================================================================
 * INICIALIZACIÓN Y CONFIGURACIÓN DEL MÓDULO
 * =============================================================================
 * 
 * Función principal que se ejecuta cuando Google Charts ha terminado de cargar.
 * Configura todos los event listeners y prepara los datos para visualización.
 * 
 * FLUJO DE INICIALIZACIÓN:
 * 1. Preparar datos desde formato PHP a Google Charts
 * 2. Crear instancia del gráfico de columnas
 * 3. Renderizar visualización inicial
 * 4. Configurar event listeners para filtros interactivos
 * 5. Configurar botón de exportación
 * 
 * @callback google.charts.setOnLoadCallback
 */
function inicializarGraficos() {
    // PASO 1: PREPARACIÓN DE DATOS
    // Convierte los datos desde el formato PHP global a estructura optimizada para Google Charts
    prepararDatosMatricula();
    
    // PASO 2: INICIALIZACIÓN DEL GRÁFICO
    // Crea una instancia del gráfico de columnas vinculada al elemento DOM
    chartMatricula = new google.visualization.ColumnChart(document.getElementById('chart-matricula'));
    
    // PASO 3: RENDERIZADO INICIAL
    // Muestra la visualización por defecto (todos los años, todos los niveles)
    actualizarVisualizacion();
    
    // PASO 4: CONFIGURACIÓN DE FILTROS INTERACTIVOS
    // Event listeners para selector de años escolares
    // Implementa patrón Observer para reactividad automática
    document.querySelectorAll('.year-option').forEach(option => {
        option.addEventListener('click', function() {
            // Limpiar selección anterior
            document.querySelectorAll('.year-option').forEach(opt => opt.classList.remove('active'));
            
            // Marcar nueva selección
            this.classList.add('active');
            
            // Actualizar estado global y re-renderizar
            añoSeleccionado = this.getAttribute('data-year');
            actualizarVisualizacion();
        });
    });
    
    // Event listeners para selector de niveles educativos
    // Similar implementación del patrón Observer para niveles
    document.querySelectorAll('.level-option').forEach(option => {
        option.addEventListener('click', function() {
            // Limpiar selección anterior
            document.querySelectorAll('.level-option').forEach(opt => opt.classList.remove('active'));
            
            // Marcar nueva selección
            this.classList.add('active');
            
            // Actualizar estado global y re-renderizar
            nivelSeleccionado = this.getAttribute('data-level');
            actualizarVisualizacion();
        });
    });
    
    // PASO 5: CONFIGURACIÓN DE EXPORTACIÓN
    // Vincula botón de exportación con función unificada de exportación
    document.getElementById('export-btn').addEventListener('click', exportarDatos);
}

/**
 * Prepara los datos de matrícula desde el formato PHP al formato requerido por Google Charts
 */
function prepararDatosMatricula() {
    datosMatriculaAgrupados = {
        'todos': prepararDatosTodos(),
        'anual': {},
        'nivel': {}
    };
    
    // Preparar datos por año
    for (const año in datosMatricula) {
        datosMatriculaAgrupados['anual'][año] = prepararDatosAño(año);
    }
    
    // Preparar datos por nivel
    const niveles = ['Inicial E', 'Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior'];
    niveles.forEach(nivel => {
        datosMatriculaAgrupados['nivel'][nivel] = prepararDatosNivel(nivel);
    });
}

/**
 * Prepara los datos para la visualización general (todos los años)
 */
function prepararDatosTodos() {
    // Crear encabezados de la tabla
    const data = [['Año Escolar', 'Inicial E', 'Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior', 'Total']];
    
    // Para cada año, agregar una fila con los datos de cada nivel
    for (const año in datosMatricula) {
        const fila = [año];
        let total = 0;
        
        // Para cada nivel, agregar el dato o 0 si no existe
        const niveles = ['Inicial E', 'Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior'];
        niveles.forEach(nivel => {
            const valor = datosMatricula[año][nivel] || 0;
            fila.push(valor);
            total += valor;
        });
        
        // Agregar el total por año
        fila.push(total);
        
        data.push(fila);
    }
    
    return data;
}

/**
 * Prepara los datos para un año específico
 */
function prepararDatosAño(año) {
    // Crear encabezados de la tabla - cada nivel educativo será una serie separada
    const data = [['Categoría', 'Inicial E', 'Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior']];
    
    // Crear una sola fila con todos los niveles como series separadas
    const fila = ['Matrícula ' + año];
    const niveles = ['Inicial E', 'Inicial NE', 'CAM', 'Preescolar', 'Primaria', 'Secundaria', 'Media superior', 'Superior'];
    niveles.forEach(nivel => {
        const valor = datosMatricula[año][nivel] || 0;
        fila.push(valor);
    });
    
    data.push(fila);
    return data;
}

/**
 * Prepara los datos para un nivel específico a lo largo de los años
 */
function prepararDatosNivel(nivel) {
    // Crear encabezados de la tabla
    const data = [['Año Escolar', 'Cantidad de Alumnos']];
    
    // Para cada año, agregar el dato del nivel
    for (const año in datosMatricula) {
        const valor = datosMatricula[año][nivel] || 0;
        data.push([año, valor]);
    }
    
    return data;
}

/**
 * FUNCIÓN CENTRAL DEL SISTEMA DE VISUALIZACIÓN
 * Actualiza la visualización según las opciones seleccionadas por el usuario
 * 
 * Esta función es el núcleo del sistema de filtrado dinámico que permite
 * 4 combinaciones diferentes de visualización:
 * 1. Vista general: Todos los años + Todos los niveles
 * 2. Vista anual: Un año específico + Todos los niveles  
 * 3. Vista evolutiva: Todos los años + Un nivel específico
 * 4. Vista puntual: Un año específico + Un nivel específico
 * 
 * @requires datosMatriculaAgrupados - Objeto con datos pre-procesados
 * @requires añoSeleccionado - Variable global con el año filtrado
 * @requires nivelSeleccionado - Variable global con el nivel filtrado
 */
function actualizarVisualizacion() {
    let datos;
    let titulo = '';
    
    // LÓGICA DE FILTRADO MATRICIAL
    // Determinar qué conjunto de datos usar según los filtros seleccionados
    // Esta matriz de decisión permite 4 tipos de visualización diferentes
    
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        // CASO 1: VISTA PANORÁMICA COMPLETA
        // Muestra evolución histórica de todos los niveles educativos
        // Útil para análisis comparativo global y tendencias generales
        datos = datosMatriculaAgrupados.todos;
        titulo = 'Matrícula por Nivel Educativo y Año Escolar';
        
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        // CASO 2: ANÁLISIS TRANSVERSAL POR AÑO
        // Compara todos los niveles educativos en un período específico
        // Ideal para evaluación de distribución educativa anual
        datos = datosMatriculaAgrupados.anual[añoSeleccionado];
        titulo = 'Matrícula por Nivel Educativo - ' + añoSeleccionado;
        
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        // CASO 3: ANÁLISIS LONGITUDINAL POR NIVEL
        // Muestra evolución temporal de un nivel educativo específico
        // Perfecto para identificar crecimiento/decrecimiento sectorial
        datos = datosMatriculaAgrupados.nivel[nivelSeleccionado];
        titulo = 'Evolución de Matrícula en ' + nivelSeleccionado + ' (2018-2024)';
        
    } else {
        // CASO 4: ANÁLISIS PUNTUAL ESPECÍFICO
        // Datos granulares de un nivel en un año determinado
        // Útil para análisis detallado y comparaciones precisas
        datos = [['Nivel Educativo', 'Cantidad de Alumnos']];
        const valorNivel = datosMatricula[añoSeleccionado][nivelSeleccionado] || 0;
        datos.push([nivelSeleccionado, valorNivel]);
        titulo = 'Matrícula en ' + nivelSeleccionado + ' - ' + añoSeleccionado;
    }
    
    // Convertir los datos a un objeto DataTable de Google
    const dataTable = google.visualization.arrayToDataTable(datos);      // Opciones para el gráfico optimizadas para exportación
    let opciones = {
        title: titulo,
        titleTextStyle: {
            fontSize: 18,
            bold: true,
            color: '#333',
            fontName: 'Arial'
        },
        height: 450,
        chartArea: {
            width: '85%',
            height: '70%',
            left: '10%',
            top: '10%'
        },
        animation: {
            duration: 1200,
            easing: 'out',
            startup: true
        },
        legend: { position: 'none' },
        colors: getColoresGrafica(),
        // Configuraciones específicas para mejorar exportación
        forceIFrame: false,
        // Configurar para usar Canvas en lugar de SVG cuando sea posible
        allowHtml: true,
        enableInteractivity: true,hAxis: {
            title: getEtiquetaEjeX(),
            gridlines: {color: '#f5f5f5'},
            textStyle: {
                fontSize: 11, // Reducido ligeramente para mejor ajuste
                color: '#555',
                fontName: 'Arial'
            },
            slantedText: false, // Mantener texto horizontal siempre
            maxTextLines: 2, // Permitir hasta 2 líneas para años largos
            showTextEvery: 1, // Mostrar todas las etiquetas
            minTextSpacing: 0 // Permitir que se ajusten automáticamente
        },
        vAxis: {
            title: 'Cantidad de Alumnos',
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
        tooltip: { 
            showColorCode: true, 
            textStyle: {
                fontName: 'Arial',
                fontSize: 13
            }
        },
        focusTarget: 'category',
        backgroundColor: {
            fill: '#ffffff',
            stroke: '#f5f5f5',
            strokeWidth: 1
        }
    };
    
    // Dibujar el gráfico
    chartMatricula.draw(dataTable, opciones);
      // Actualizar la tabla de datos
    actualizarTablaDeMatricula(datos);
    
    // Actualizar visibilidad de la leyenda de colores
    actualizarVisibilidadLeyenda();
}

/**
 * Obtiene los colores para el gráfico según los filtros actuales
 */
function getColoresGrafica() {
    // Colores profesionales más armónicos y con mejor contraste
    const coloresBase = {
        'Inicial E': '#1A237E',       // Azul índigo profundo para Inicial Escolarizada
        'Inicial NE': '#3949AB',      // Azul más profundo
        'CAM': '#00897B',             // Verde azulado más saturado
        'Preescolar': '#FB8C00',      // Naranja más cálido
        'Primaria': '#E53935',        // Rojo más profesional
        'Secundaria': '#5E35B1',      // Púrpura más elegante
        'Media superior': '#43A047',  // Verde más claro y elegante
        'Superior': '#0288D1',        // Azul claro más profesional
        'Total': '#546E7A'            // Gris azulado más elegante
    };
    
    // Cuando se filtra por nivel, usar un degradado de tonos del mismo color base
    if (nivelSeleccionado !== 'todos') {
        // Si estamos viendo la evolución por años de un solo nivel
        if (añoSeleccionado === 'todos') {
            // Crear un degradado del color base para mostrar evolución temporal
            const colorBase = coloresBase[nivelSeleccionado];
            return generarDegradadoColor(colorBase, 6);
        } else {
            // Para un único dato de un nivel y año específico
            return [coloresBase[nivelSeleccionado]];
        }
    }    // Determinar qué colores devolver según el contexto
    if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        // Filtro por año específico - colores para cada nivel educativo como series separadas
        return [
            coloresBase['Inicial E'],
            coloresBase['Inicial NE'],
            coloresBase['CAM'],
            coloresBase['Preescolar'],
            coloresBase['Primaria'],
            coloresBase['Secundaria'],
            coloresBase['Media superior'],
            coloresBase['Superior']
        ];
    }
    
    // Array de colores para todos los niveles (cuando no hay filtro de nivel)
    return [
        coloresBase['Inicial E'],
        coloresBase['Inicial NE'],
        coloresBase['CAM'],
        coloresBase['Preescolar'],
        coloresBase['Primaria'],
        coloresBase['Secundaria'],
        coloresBase['Media superior'],
        coloresBase['Superior'],
        coloresBase['Total']
    ];
}

/**
 * Genera un degradado de colores basado en un color base
 * @param {string} colorBase - El color base en formato hexadecimal
 * @param {number} cantidad - La cantidad de colores a generar
 * @returns {array} - Array de colores en degradado
 */
function generarDegradadoColor(colorBase, cantidad) {
    const resultado = [];
    
    // Convertir el color hexadecimal a RGB
    const r = parseInt(colorBase.substring(1, 3), 16);
    const g = parseInt(colorBase.substring(3, 5), 16);
    const b = parseInt(colorBase.substring(5, 7), 16);
    
    // Crear un degradado del color base, variando la luminosidad
    for (let i = 0; i < cantidad; i++) {
        // Calcular el factor de ajuste para hacer un degradado
        const factor = 0.8 + (i * 0.05);
        
        // Aplicar el factor a los componentes RGB, manteniendo el tono
        let nuevoR = Math.min(255, Math.floor(r * factor));
        let nuevoG = Math.min(255, Math.floor(g * factor));
        let nuevoB = Math.min(255, Math.floor(b * factor));
        
        // Convertir de vuelta a hexadecimal
        const nuevoColor = '#' + 
            nuevoR.toString(16).padStart(2, '0') + 
            nuevoG.toString(16).padStart(2, '0') + 
            nuevoB.toString(16).padStart(2, '0');
            
        resultado.push(nuevoColor);
    }
    
    return resultado;
}

/**
 * Obtiene la etiqueta para el eje X según los filtros actuales
 */
function getEtiquetaEjeX() {
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        return 'Año Escolar';
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        return 'Nivel Educativo';
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        return 'Año Escolar';
    } else {
        return 'Nivel Educativo';
    }
}

/**
 * Actualiza la tabla de datos debajo del gráfico
 */
function actualizarTablaDeMatricula(datos) {
    const tabla = document.getElementById('tabla-matricula');
    
    // Limpiar la tabla existente
    tabla.innerHTML = '';
    
    // Crear encabezado
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    
    datos[0].forEach(col => {
        const th = document.createElement('th');
        th.textContent = col;
        headerRow.appendChild(th);
    });
    
    thead.appendChild(headerRow);
    tabla.appendChild(thead);
    
    // Crear cuerpo de la tabla
    const tbody = document.createElement('tbody');
    
    for (let i = 1; i < datos.length; i++) {
        const row = document.createElement('tr');
        
        datos[i].forEach(val => {
            const td = document.createElement('td');
            
            // Formatear números con separadores de miles
            if (typeof val === 'number') {
                td.textContent = val.toLocaleString();
            } else {
                td.textContent = val;
            }
            
            row.appendChild(td);
        });
        
        tbody.appendChild(row);
    }
    
    tabla.appendChild(tbody);
}

/**
 * Restaura el estado original del botón de exportar
 */
function restaurarBotonExport(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
}

/**
 * Muestra un mensaje de éxito temporal
 */
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

/**
 * Muestra un mensaje de error temporal
 */
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
    }, 5000);
}

/**
 * Obtiene los datos filtrados según la selección actual
 */
function obtenerDatosFiltrados() {
    let datos;
    
    if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
        // Mostrar todos los datos por año
        datos = datosMatriculaAgrupados['todos'];
    } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
        // Mostrar datos de un año específico por nivel
        datos = datosMatriculaAgrupados['anual'][añoSeleccionado];
    } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
        // Mostrar evolución de un nivel específico por años
        datos = datosMatriculaAgrupados['nivel'][nivelSeleccionado];
    } else {
        // Mostrar datos específicos de un año y nivel
        datos = prepararDatosEspecificos(añoSeleccionado, nivelSeleccionado);
    }
    
    return datos;
}

/**
 * Prepara datos específicos para un año y nivel determinado
 */
function prepararDatosEspecificos(año, nivel) {
    const datos = [['Nivel Educativo', 'Cantidad de Alumnos']];
    const valorNivel = datosMatricula[año][nivel] || 0;
    datos.push([nivel, valorNivel]);
    return datos;
}

/**
 * Actualiza la visibilidad de la leyenda de colores según los filtros seleccionados
 */
function actualizarVisibilidadLeyenda() {
    const leyenda = document.querySelector('.chart-legend');
    const items = document.querySelectorAll('.legend-item');
    
    // Si es un único nivel y un único año, ocultar la leyenda
    if (nivelSeleccionado !== 'todos' && añoSeleccionado !== 'todos') {
        leyenda.style.display = 'none';
        return;
    }
    
    // Mostramos la leyenda
    leyenda.style.display = 'flex';
    
    // Si es un solo nivel con todos los años, mostramos solo ese nivel
    if (nivelSeleccionado !== 'todos' && añoSeleccionado === 'todos') {
        items.forEach(item => {
            const textoNivel = item.querySelector('span').textContent;
            if (textoNivel === nivelSeleccionado) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
        return;
    }
    
    // Si son todos los niveles, mostramos todos
    items.forEach(item => {
        item.style.display = 'flex';
    });
}

/**
 * Función que se ejecuta cuando la ventana se ha cargado completamente
 */
window.addEventListener('load', function() {
    // Animar la aparición del contenido
    const paneles = document.querySelectorAll('.matricula-panel');
    paneles.forEach(panel => {
        panel.classList.add('animate-fade');    });
    
    // Activa las animaciones globales si existen
    if (typeof activarAnimacionesGlobales === 'function') {
        activarAnimacionesGlobales();
    }
    
    // Activar clase para animar el crecimiento del gráfico
    setTimeout(() => {
        document.getElementById('chart-matricula-container').classList.add('chart-grow');
    }, 300);
});
