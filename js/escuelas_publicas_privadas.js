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

    // Función para crear el gráfico de pie por nivel educativo
    function crearGraficoPieNivel(tipo = 'total') {
        console.log(`Creando gráfico de pie con filtro: ${tipo}`);

        // Preparar datos para el gráfico
        const datosGrafico = prepararDatosGrafico(tipo);

        if (!datosGrafico || datosGrafico.length === 0) {
            console.error('No hay datos disponibles para el gráfico');
            return;
        }

        // Cargar Google Charts si no está cargado
        if (typeof google === 'undefined' || typeof google.charts === 'undefined') {
            console.error('Google Charts no está disponible');
            return;
        }

        // Dibujar el gráfico
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(function() {
            const data = google.visualization.arrayToDataTable(datosGrafico);

            // Mapeo de colores por nivel educativo (debe coincidir exactamente con resumen.php)
            const coloresPorNivel = {
                'Inicial (Escolarizado)': '#1A237E',
                'Inicial (No Escolarizado)': '#3949AB',
                'Especial (CAM)': '#00897B',
                'Especial (USAER)': '#FB8C00',
                'Preescolar': '#E53935',
                'Primaria': '#5E35B1',
                'Secundaria': '#43A047',
                'Media Superior': '#0288D1',
                'Superior': '#00ACC1'
            };

            // Construir array de colores en el orden de los datos
            const coloresOrdenados = [];
            for (let i = 1; i < datosGrafico.length; i++) {
                const nivel = datosGrafico[i][0];
                coloresOrdenados.push(coloresPorNivel[nivel] || '#6A1B9A'); // Color por defecto si no se encuentra
            }

            const options = {
                pieHole: 0.4, // 0 para pie normal, 0.4 para donut
                colors: coloresOrdenados,
                legend: {
                    position: 'right',
                    textStyle: {
                        fontSize: 14
                    }
                },
                pieSliceText: 'percentage',
                pieSliceTextStyle: {
                    color: 'white',
                    fontSize: 14,
                    bold: true
                },
                tooltip: {
                    text: 'both',
                    showColorCode: true
                },
                chartArea: {
                    width: '90%',
                    height: '85%'
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
    
    // Inicializar el gráfico de pie al cargar la página (vista por defecto)
    // Se ejecuta después de que Google Charts se haya cargado
    if (typeof google !== 'undefined' && google.charts) {
        google.charts.setOnLoadCallback(function() {
            crearGraficoPieNivel(filtroActual);
        });
    } else {
        // Si Google Charts no está cargado aún, esperar un momento
        setTimeout(function() {
            if (typeof google !== 'undefined' && google.charts) {
                google.charts.setOnLoadCallback(function() {
                    crearGraficoPieNivel(filtroActual);
                });
            }
        }, 500);
    }
});

// Agregar una función auxiliar para formatear números
function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
}
