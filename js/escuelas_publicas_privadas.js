/**
 * Archivo JavaScript para manejar la visualización de datos sobre escuelas públicas y privadas
 * Sistema de Dashboard Estadístico - SEDEQ
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Sistema de Filtrado de Escuelas por Sostenimiento ===');
    console.log('Inicializando componentes...');
    
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
            // Guardar los valores iniciales para poder restaurarlos luego
            valoresOriginales[nombreNivel] = {
                cantidad: escuelasCount.textContent,
                porcentaje: levelPercent.textContent,
                ancho: levelPercent.textContent // Si no tiene ancho inicial, usar el porcentaje
            };
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
                aplicarFiltro(filterType);
            });
        });
    }    // Función para aplicar filtros de sostenimiento
    function aplicarFiltro(tipo) {
        console.log(`Aplicando filtro: ${tipo}`);
        
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
                        
                        if (tipo === 'publico') {
                            cantidad = nivelData.publicas || 0;
                            totalReferencia = escuelasPublicas;
                            console.log(`✓ Encontrado ${nombreNivel}: ${cantidad} escuelas públicas`);
                        } else if (tipo === 'privado') {
                            cantidad = nivelData.privadas || 0;
                            totalReferencia = escuelasPrivadas;
                            console.log(`✓ Encontrado ${nombreNivel}: ${cantidad} escuelas privadas`);
                        }
                        
                        // Calcular porcentaje si hay un total de referencia válido
                        if (totalReferencia > 0) {
                            porcentaje = Math.round((cantidad / totalReferencia) * 100);
                        } else {
                            console.warn(`Total de referencia inválido para ${nombreNivel}: ${totalReferencia}`);
                        }
                        
                        // Actualizar la interfaz
                        if (escuelasCount) {
                            escuelasCount.textContent = cantidad;
                        }
                        if (levelFill) {
                            levelFill.style.width = porcentaje + '%';
                        }
                        if (levelPercent) {
                            levelPercent.textContent = porcentaje + '%';
                        }
                        
                        console.log(`✓ Actualizado ${nombreNivel}: ${cantidad} escuelas (${porcentaje}%)`);
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
    function buscarDatosSostenimiento(nombreNivel) {        // Mapa explícito de coincidencias entre nombres de UI y nombres en los datos
        const mapaCoincidencias = {
            // Mapeo para abreviaturas en UI
            'Inicial E': 'Inicial (Escolarizado)',
            'Inicial NE': 'Inicial (No Escolarizado)',
            'Inicial (E)': 'Inicial (Escolarizado)',
            'Inicial (NE)': 'Inicial (No Escolarizado)',
            'Especial': 'Especial (CAM)',
            'Media Sup.': 'Media Superior',
            // Mantener también los nombres originales
            'Preescolar': 'Preescolar',
            'Primaria': 'Primaria',
            'Secundaria': 'Secundaria',
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

    // Función para crear gráficos de pastel por nivel educativo
    // Esta función puede expandirse en el futuro para mostrar gráficos más detallados
    function crearGraficosPorNivel() {
        // Código para gráficos Google Charts puede implementarse aquí
        // cuando se requiera visualización adicional
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
});

// Agregar una función auxiliar para formatear números
function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
}
