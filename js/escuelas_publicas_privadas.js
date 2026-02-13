/**
 * Archivo JavaScript para manejar la visualización de datos sobre escuelas públicas y privadas
 * Sistema de Dashboard Estadístico - SEDEQ
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Sistema de Filtrado de Escuelas por Sostenimiento ===');
    console.log('Inicializando componentes...');

    // Verificar datos disponibles
    console.log('✓ Datos cargados:');
    console.log('  - Total escuelas:', typeof totalEscuelas !== 'undefined' ? totalEscuelas : 'NO DEFINIDO');
    console.log('  - Escuelas públicas:', typeof escuelasPublicas !== 'undefined' ? escuelasPublicas : 'NO DEFINIDO');
    console.log('  - Escuelas privadas:', typeof escuelasPrivadas !== 'undefined' ? escuelasPrivadas : 'NO DEFINIDO');
    console.log('  - Datos por nivel:', typeof escuelasNivelSostenimiento !== 'undefined' ? 'DISPONIBLE' : 'NO DEFINIDO');

    if (typeof escuelasNivelSostenimiento !== 'undefined') {
        console.log('  - Niveles disponibles:', Object.keys(escuelasNivelSostenimiento));
    }

    // Variable para rastrear el filtro actual (para sincronizar con el gráfico)
    let filtroActual = 'total';
    
    // Almacenar los valores originales de cada nivel para restaurarlos
    const valoresOriginales = {};
    const barrasNivel = document.querySelectorAll('.level-bar');
    console.log(`Se encontraron ${barrasNivel.length} barras de nivel educativo`);

    // Guardar los valores iniciales de cada barra de nivel
    barrasNivel.forEach(bar => {
        const nombreNivel = bar.querySelector('.level-name').textContent.trim();
        const escuelasCount = bar.querySelector('.escuelas-count');
        const levelFill = bar.querySelector('.level-fill');
        const levelPercent = bar.querySelector('.level-percent');

        if (escuelasCount && levelFill && levelPercent) {
            // Extraer el ancho actual de la barra (del style inline)
            const anchoActual = levelFill.style.width || '0%';
            
            // Guardar los valores iniciales para poder restaurarlos luego
            valoresOriginales[nombreNivel] = {
                cantidad: escuelasCount.textContent.trim(),
                porcentaje: levelPercent.textContent.trim(),
                ancho: anchoActual
            };
            
            console.log(`  - ${nombreNivel}: ${escuelasCount.textContent.trim()} escuelas, ${anchoActual} ancho`);
        }
    });

    // Animación para la barra de progreso de sostenimiento
    setTimeout(function() {
        // Aplicar animación de entrada para las barras de progreso
        let publicBar = document.querySelector('.progress-fill.public');
        let privateBar = document.querySelector('.progress-fill.private');
        
        if (publicBar && privateBar) {
            // Efecto de entrada con retardo
            setTimeout(() => {
                publicBar.style.transition = 'width 1.5s ease-in-out';
                privateBar.style.transition = 'width 1.5s ease-in-out';
                
                // Aplicar el ancho basado en los datos
                publicBar.style.width = porcentajePublicas + '%';
                privateBar.style.width = porcentajePrivadas + '%';
            }, 500);
        }
    }, 1000);

    // Configurar los botones de filtro de sostenimiento
    const filterButtons = document.querySelectorAll('.sostenimiento-filters .filter-btn');
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Si es el botón de diagnóstico, ejecutar diagnóstico
                if (this.id === 'btn-diagnostico') {
                    diagnosticarDatos();
                    return;
                }
                
                // Cambiar clase activa
                filterButtons.forEach(btn => {
                    if (btn.id !== 'btn-diagnostico') {
                        btn.classList.remove('active');
                    }
                });
                this.classList.add('active');
                
                // Aplicar el filtro seleccionado
                const filterType = this.getAttribute('data-filter');
                filtroActual = filterType; // Guardar el filtro actual
                aplicarFiltro(filterType);
            });
        });
    }

    // Configurar los botones de toggle de visualización
    const viewToggleButtons = document.querySelectorAll('.view-toggle-btn');
    const vistaBarras = document.getElementById('vista-barras');
    const vistaGrafico = document.getElementById('vista-grafico');

    if (viewToggleButtons.length > 0) {
        viewToggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Cambiar clase activa
                viewToggleButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Cambiar vista
                const viewType = this.getAttribute('data-view');
                if (viewType === 'barras') {
                    vistaBarras.style.display = 'block';
                    vistaGrafico.style.display = 'none';
                } else if (viewType === 'grafico') {
                    vistaBarras.style.display = 'none';
                    vistaGrafico.style.display = 'block';
                    // Crear o actualizar el gráfico cuando se muestra
                    crearGraficoPieNivel(filtroActual);
                }
            });
        });
    }
    
    // Función para aplicar filtros de sostenimiento
    function aplicarFiltro(tipo) {
        console.log(`Aplicando filtro: ${tipo}`);
        
        // Verificar que los datos estén disponibles
        if (typeof escuelasNivelSostenimiento === 'undefined') {
            console.error('❌ Error: escuelasNivelSostenimiento no está definido');
            return;
        }
        
        console.log('Datos disponibles:', escuelasNivelSostenimiento);
        
        barrasNivel.forEach(bar => {
            const nombreNivel = bar.querySelector('.level-name').textContent.trim();
            const escuelasCount = bar.querySelector('.escuelas-count');
            const levelFill = bar.querySelector('.level-fill');
            const levelPercent = bar.querySelector('.level-percent');
            
            console.log(`Procesando nivel: ${nombreNivel}`);
            
            try {
                // Verificar si tenemos datos de sostenimiento para este nivel
                if (tipo === 'total') {
                    // Si es total, restauramos los valores originales guardados
                    if (escuelasCount && valoresOriginales[nombreNivel]) {
                        escuelasCount.textContent = valoresOriginales[nombreNivel].cantidad;
                        console.log(`✓ Restaurado ${nombreNivel}: ${valoresOriginales[nombreNivel].cantidad} escuelas`);
                    } else {
                        console.warn(`✗ No se pudo restaurar la cantidad para ${nombreNivel}`);
                    }
                    
                    if (levelFill && valoresOriginales[nombreNivel]) {
                        levelFill.style.width = valoresOriginales[nombreNivel].porcentaje;
                    }
                    
                    if (levelPercent && valoresOriginales[nombreNivel]) {
                        levelPercent.textContent = valoresOriginales[nombreNivel].porcentaje;
                    }
                } else {
                    // Buscamos datos específicos de sostenimiento
                    const nivelData = buscarDatosSostenimiento(nombreNivel);
                    
                    if (nivelData) {
                        let cantidad = 0;
                        let porcentaje = 0;
                        let totalReferencia = 0;
                        let maxEscuelas = 0; // Para calcular el ancho relativo de la barra
                        
                        if (tipo === 'publico') {
                            cantidad = nivelData.publicas || 0;
                            totalReferencia = escuelasPublicas;
                            // Encontrar el máximo de escuelas públicas en todos los niveles para la escala
                            maxEscuelas = Math.max(...Object.values(escuelasNivelSostenimiento).map(n => n.publicas || 0));
                            console.log(`✓ Encontrado ${nombreNivel}: ${cantidad} escuelas públicas`);
                        } else if (tipo === 'privado') {
                            cantidad = nivelData.privadas || 0;
                            totalReferencia = escuelasPrivadas;
                            // Encontrar el máximo de escuelas privadas en todos los niveles para la escala
                            maxEscuelas = Math.max(...Object.values(escuelasNivelSostenimiento).map(n => n.privadas || 0));
                            console.log(`✓ Encontrado ${nombreNivel}: ${cantidad} escuelas privadas`);
                        }
                        
                        // Calcular porcentaje basado en el total de referencia (para el texto)
                        if (totalReferencia > 0) {
                            porcentaje = Math.round((cantidad / totalReferencia) * 100);
                        } else {
                            console.warn(`Total de referencia inválido para ${nombreNivel}: ${totalReferencia}`);
                        }
                        
                        // Calcular ancho de barra basado en el máximo (para mantener proporciones visuales)
                        let anchoBarra = 0;
                        if (maxEscuelas > 0) {
                            anchoBarra = Math.round((cantidad / maxEscuelas) * 100);
                        }
                        
                        // Actualizar la interfaz
                        if (escuelasCount) {
                            escuelasCount.textContent = cantidad;
                        }
                        if (levelFill) {
                            // Usar anchoBarra para mantener proporciones visuales entre niveles
                            levelFill.style.width = anchoBarra + '%';
                        }
                        if (levelPercent) {
                            // Mostrar el porcentaje real del total
                            levelPercent.textContent = porcentaje + '%';
                        }
                        
                        console.log(`✓ Actualizado ${nombreNivel}: ${cantidad} escuelas (${porcentaje}% del total, ${anchoBarra}% ancho barra)`);
                    } else {
                        console.warn(`✗ No se encontraron datos para el nivel "${nombreNivel}" con filtro "${tipo}"`);
                        // Se podría considerar mostrar un valor de cero o un mensaje de "N/A"
                        if (escuelasCount) escuelasCount.textContent = '0';
                        if (levelFill) levelFill.style.width = '0%';
                        if (levelPercent) levelPercent.textContent = '0%';
                    }
                }
            } catch (error) {
                console.error(`Error al procesar el nivel ${nombreNivel}:`, error);
                // Intentamos restaurar los valores originales en caso de error
                resetearFiltros();
            }
        });

        console.log(`Filtro "${tipo}" aplicado correctamente a todos los niveles`);

        // Si la vista de gráfico está activa, actualizarla también
        if (vistaGrafico && vistaGrafico.style.display !== 'none') {
            crearGraficoPieNivel(tipo);
        }
    }
    
    // Función para resetear todos los filtros en caso de error
    function resetearFiltros() {
        console.warn('Reseteando todos los filtros a valores iniciales...');
        
        // Marcar el botón total como activo
        filterButtons.forEach(btn => {
            if (btn.getAttribute('data-filter') === 'total') {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Restaurar todos los valores originales
        barrasNivel.forEach(bar => {
            const nombreNivel = bar.querySelector('.level-name').textContent.trim();
            const escuelasCount = bar.querySelector('.escuelas-count');
            const levelFill = bar.querySelector('.level-fill');
            const levelPercent = bar.querySelector('.level-percent');
            
            if (valoresOriginales[nombreNivel]) {
                if (escuelasCount) escuelasCount.textContent = valoresOriginales[nombreNivel].cantidad;
                if (levelFill) levelFill.style.width = valoresOriginales[nombreNivel].porcentaje;
                if (levelPercent) levelPercent.textContent = valoresOriginales[nombreNivel].porcentaje;
            }
        });
        
        console.log('Filtros reseteados correctamente');
    }

    // Función auxiliar para buscar datos de sostenimiento por nivel
    function buscarDatosSostenimiento(nombreNivel) {
        // Mapa explícito de coincidencias entre nombres de UI y nombres en los datos
        const mapaCoincidencias = {
            // Mapeo para abreviaturas en UI
            'Inicial (E)': 'Inicial (Escolarizado)',
            'Inicial (NE)': 'Inicial (No Escolarizado)',
            'Especial (CAM)': 'Especial (CAM)',
            'Media Sup.': 'Media Superior',
            // Mantener también los nombres originales
            'Inicial (Escolarizado)': 'Inicial (Escolarizado)',
            'Inicial (No Escolarizado)': 'Inicial (No Escolarizado)',
            'Preescolar': 'Preescolar',
            'Primaria': 'Primaria',
            'Secundaria': 'Secundaria',
            'Media Superior': 'Media Superior',
            'Superior': 'Superior'
        };
        
        // 1. Intentar encontrar la coincidencia en nuestro mapa
        if (mapaCoincidencias[nombreNivel] && escuelasNivelSostenimiento[mapaCoincidencias[nombreNivel]]) {
            console.log(`Coincidencia encontrada: "${nombreNivel}" -> "${mapaCoincidencias[nombreNivel]}"`);
            return escuelasNivelSostenimiento[mapaCoincidencias[nombreNivel]];
        }
        
        // 2. Intentar coincidencia directa
        if (escuelasNivelSostenimiento[nombreNivel]) {
            return escuelasNivelSostenimiento[nombreNivel];
        }
        
        // 3. Buscar por coincidencia normalizada (sin espacios, sin case)
        const nombreNormalizado = nombreNivel.toLowerCase().trim();
        for (const nivel in escuelasNivelSostenimiento) {
            if (nivel.toLowerCase().trim() === nombreNormalizado) {
                return escuelasNivelSostenimiento[nivel];
            }
        }
        
        // 4. Buscar por nombres abreviados como último recurso
        for (const nivel in escuelasNivelSostenimiento) {
            const nivelNormalizado = nivel.toLowerCase().trim();
            if (nombreNormalizado.includes(nivelNormalizado) || nivelNormalizado.includes(nombreNormalizado)) {
                return escuelasNivelSostenimiento[nivel];
            }
        }
        
        // Si no se encuentra, devolver null
        console.log('No se encontraron datos para:', nombreNivel);
        return null;
    }

    // Instancia Chart.js (se destruye antes de redibujar)
    let pieChartInstance = null;

    // Plugin personalizado para etiquetas con líneas guía y anti-superposición
    const outsideLabelsPlugin = {
        id: 'outsideLabels',
        afterDraw: function(chart) {
            var ctx = chart.ctx;
            var meta = chart.getDatasetMeta(0);
            if (!meta || !meta.data || meta.data.length === 0) return;

            var dataset = chart.data.datasets[0];
            var total = dataset.data.reduce(function(a, b) { return a + b; }, 0);
            if (total === 0) return;

            var chartArea = chart.chartArea;
            var centerX = (chartArea.left + chartArea.right) / 2;
            var centerY = (chartArea.top + chartArea.bottom) / 2;
            var radius = Math.min(chartArea.right - chartArea.left, chartArea.bottom - chartArea.top) / 2;

            // Recopilar info de cada porción
            var items = [];
            for (var i = 0; i < meta.data.length; i++) {
                var arc = meta.data[i];
                var value = dataset.data[i];
                if (value <= 0) continue;
                var pct = ((value / total) * 100).toFixed(2);
                var midAngle = (arc.startAngle + arc.endAngle) / 2;
                var label = chart.data.labels[i] + ': ' + pct + '%';
                var color = dataset.backgroundColor[i];
                var isRight = Math.cos(midAngle) >= 0;

                // Medir texto
                ctx.font = 'bold 11px sans-serif';
                var textW = ctx.measureText(label).width;
                var boxW = textW + 16;
                var boxH = 22;

                // Punto en el borde del arco
                var edgeX = centerX + Math.cos(midAngle) * radius;
                var edgeY = centerY + Math.sin(midAngle) * radius;

                // Punto de la etiqueta (extendido)
                var lineLen = 30;
                var labelX = centerX + Math.cos(midAngle) * (radius + lineLen);
                var labelY = centerY + Math.sin(midAngle) * (radius + lineLen);

                items.push({
                    midAngle: midAngle,
                    edgeX: edgeX,
                    edgeY: edgeY,
                    labelX: labelX,
                    labelY: labelY,
                    label: label,
                    color: color,
                    isRight: isRight,
                    boxW: boxW,
                    boxH: boxH
                });
            }

            // Separar en dos columnas: izquierda y derecha
            var leftItems = items.filter(function(it) { return !it.isRight; });
            var rightItems = items.filter(function(it) { return it.isRight; });

            // Ordenar por posición Y
            leftItems.sort(function(a, b) { return a.labelY - b.labelY; });
            rightItems.sort(function(a, b) { return a.labelY - b.labelY; });

            // Resolver superposición vertical
            function resolveOverlap(arr, minGap) {
                for (var pass = 0; pass < 10; pass++) {
                    var changed = false;
                    for (var j = 1; j < arr.length; j++) {
                        var prev = arr[j - 1];
                        var curr = arr[j];
                        var overlap = (prev.labelY + prev.boxH / 2 + minGap) - (curr.labelY - curr.boxH / 2);
                        if (overlap > 0) {
                            prev.labelY -= overlap / 2;
                            curr.labelY += overlap / 2;
                            changed = true;
                        }
                    }
                    if (!changed) break;
                }
            }

            resolveOverlap(leftItems, 4);
            resolveOverlap(rightItems, 4);

            // Dibujar etiquetas y líneas
            var allItems = leftItems.concat(rightItems);
            for (var k = 0; k < allItems.length; k++) {
                var it = allItems[k];

                // Posición X final de la etiqueta (alineada al borde del canvas)
                var finalX;
                if (it.isRight) {
                    finalX = chartArea.right + 15;
                } else {
                    finalX = chartArea.left - 15 - it.boxW;
                }
                var finalY = it.labelY;

                // Punto de quiebre de la línea (codo)
                var elbowX = it.isRight ? finalX - 5 : finalX + it.boxW + 5;
                var elbowY = finalY;

                // Dibujar línea guía (del borde del arco al codo y al label)
                ctx.save();
                ctx.strokeStyle = it.color;
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.moveTo(it.edgeX, it.edgeY);
                ctx.lineTo(elbowX, elbowY);
                ctx.stroke();
                ctx.restore();

                // Dibujar caja con fondo de color
                ctx.save();
                ctx.fillStyle = it.color;
                var bx = finalX;
                var by = finalY - it.boxH / 2;
                var br = 4;
                ctx.beginPath();
                ctx.moveTo(bx + br, by);
                ctx.lineTo(bx + it.boxW - br, by);
                ctx.quadraticCurveTo(bx + it.boxW, by, bx + it.boxW, by + br);
                ctx.lineTo(bx + it.boxW, by + it.boxH - br);
                ctx.quadraticCurveTo(bx + it.boxW, by + it.boxH, bx + it.boxW - br, by + it.boxH);
                ctx.lineTo(bx + br, by + it.boxH);
                ctx.quadraticCurveTo(bx, by + it.boxH, bx, by + it.boxH - br);
                ctx.lineTo(bx, by + br);
                ctx.quadraticCurveTo(bx, by, bx + br, by);
                ctx.closePath();
                ctx.fill();
                ctx.restore();

                // Dibujar texto
                ctx.save();
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 11px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(it.label, bx + it.boxW / 2, finalY);
                ctx.restore();
            }
        }
    };

    // Función para crear el gráfico de pie por nivel educativo (Chart.js)
    function crearGraficoPieNivel(tipo = 'total') {
        console.log(`Creando gráfico de pie con filtro: ${tipo}`);

        const canvas = document.getElementById('pie-chart-nivel');
        if (!canvas) {
            console.error('Canvas pie-chart-nivel no encontrado');
            return;
        }

        // Destruir instancia anterior si existe (siempre, antes de evaluar datos)
        if (pieChartInstance) {
            pieChartInstance.destroy();
            pieChartInstance = null;
        }

        const datosGrafico = prepararDatosGrafico(tipo);

        // Manejar datos vacíos: dibujar mensaje en el canvas
        if (!datosGrafico || datosGrafico.length <= 1) {
            console.warn('Sin datos para el filtro:', tipo);
            const ctx2d = canvas.getContext('2d');
            ctx2d.clearRect(0, 0, canvas.width, canvas.height);
            ctx2d.save();
            ctx2d.textAlign = 'center';
            ctx2d.textBaseline = 'middle';
            ctx2d.fillStyle = '#aaa';
            ctx2d.font = '16px sans-serif';
            ctx2d.fillText('Sin datos disponibles para este filtro', canvas.width / 2, canvas.height / 2);
            ctx2d.restore();
            return;
        }

        // Mapeo de colores por nivel educativo (mismo orden y colores que resumen.php / script.js)
        const coloresPorNivel = {
            'Inicial (Escolarizado)': '#1A237E',
            'Inicial (No Escolarizado)': '#3949AB',
            'Especial (CAM)': '#00897B',
            'Especial CAM': '#00897B',
            'Especial (USAER)': '#FB8C00',
            'Especial USAER': '#FB8C00',
            'Preescolar': '#E53935',
            'Primaria': '#5E35B1',
            'Secundaria': '#43A047',
            'Media Superior': '#0288D1',
            'Superior': '#00ACC1'
        };

        // Transformar formato Google Charts → Chart.js
        const labels = [];
        const data = [];
        const backgroundColor = [];

        for (let i = 1; i < datosGrafico.length; i++) {
            const nivel = datosGrafico[i][0];
            labels.push(nivel);
            data.push(datosGrafico[i][1]);
            backgroundColor.push(coloresPorNivel[nivel] || '#6A1B9A');
        }

        const ctx = canvas.getContext('2d');

        pieChartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColor,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            plugins: [outsideLabelsPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: true,
                layout: { padding: { top: 30, bottom: 30, left: 200, right: 200 } },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 16,
                            font: { size: 12 }
                        }
                    },
                    datalabels: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                var dataset = tooltipItem.dataset;
                                var total = dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var value = dataset.data[tooltipItem.dataIndex];
                                var pct = ((value / total) * 100).toFixed(2);
                                return tooltipItem.label + ': ' + value + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Función auxiliar para preparar datos según el filtro
    function prepararDatosGrafico(tipo) {
        const datos = [['Nivel', 'Escuelas']];

        if (typeof escuelasNivelSostenimiento === 'undefined') {
            console.error('escuelasNivelSostenimiento no está definido');
            return null;
        }

        // Orden específico de niveles educativos (debe coincidir con resumen.php)
        const ordenNiveles = [
            'Inicial (Escolarizado)',
            'Inicial (No Escolarizado)',
            'Especial (CAM)',
            'Especial (USAER)',
            'Preescolar',
            'Primaria',
            'Secundaria',
            'Media Superior',
            'Superior'
        ];

        // Agregar datos en el orden especificado
        for (const nivel of ordenNiveles) {
            if (escuelasNivelSostenimiento[nivel]) {
                let cantidad = 0;

                if (tipo === 'total') {
                    cantidad = escuelasNivelSostenimiento[nivel].total || 0;
                } else if (tipo === 'publico') {
                    cantidad = escuelasNivelSostenimiento[nivel].publicas || 0;
                } else if (tipo === 'privado') {
                    cantidad = escuelasNivelSostenimiento[nivel].privadas || 0;
                }

                if (cantidad > 0) {
                    datos.push([nivel, cantidad]);
                }
            }
        }

        return datos;
    }

    // Función auxiliar para obtener el título del gráfico según el filtro
    function getTituloGrafico(tipo) {
        switch(tipo) {
            case 'publico':
                return 'Distribución de Escuelas Públicas por Nivel';
            case 'privado':
                return 'Distribución de Escuelas Privadas por Nivel';
            default:
                return 'Distribución Total de Escuelas por Nivel';
        }
    }

    // Código de depuración - muestra los datos en consola para verificar
    console.log('Datos originales guardados:', valoresOriginales);
    console.log('Datos por nivel:', escuelasPorNivel);
    console.log('Datos por sostenimiento:', escuelasNivelSostenimiento);

    // Función de diagnóstico - verificar datos y mapeos
    function diagnosticarDatos() {
        console.log('=== DIAGNÓSTICO DE DATOS ===');
        
        // 1. Verificar estructura de barras de nivel en HTML
        console.log('1. Barras de nivel en HTML:');
        barrasNivel.forEach(bar => {
            const nombreNivel = bar.querySelector('.level-name').textContent.trim();
            console.log(`   - ${nombreNivel}`);
        });
        
        // 2. Verificar datos de sostenimiento disponibles
        console.log('2. Datos de sostenimiento disponibles:');
        for (const nivel in escuelasNivelSostenimiento) {
            const datos = escuelasNivelSostenimiento[nivel];
            console.log(`   - ${nivel}: ${datos.publicas} públicas, ${datos.privadas} privadas`);
        }
        
        // 3. Probar mapeo para cada nivel
        console.log('3. Prueba de mapeo para cada nivel:');
        barrasNivel.forEach(bar => {
            const nombreNivel = bar.querySelector('.level-name').textContent.trim();
            const datosEncontrados = buscarDatosSostenimiento(nombreNivel);
            if (datosEncontrados) {
                console.log(`   ✓ ${nombreNivel} → Datos encontrados`);
            } else {
                console.log(`   ✗ ${nombreNivel} → SIN DATOS`);
            }
        });
        
        console.log('=== FIN DEL DIAGNÓSTICO ===');
    }
    
    // Ejecutar diagnóstico después de cargar la página
    setTimeout(diagnosticarDatos, 2000);
    
    // Inicializar el gráfico de pie al cargar la página (Chart.js)
    crearGraficoPieNivel(filtroActual);
});

// Agregar una función auxiliar para formatear números
function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
}
