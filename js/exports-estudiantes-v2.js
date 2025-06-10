/**
 * NUEVO SISTEMA DE EXPORTACIÓN - VERSIÓN 2.0
 * Sistema completamente rediseñado para resolver problemas de PNG en blanco
 * 
 * SIMPLIFICADO Y ROBUSTO:
 * - Sin dependencias complejas de preparación
 * - Captura directa con configuración optimizada
 * - Múltiples métodos de fallback
 * - Validación exhaustiva en cada paso
 */

/**
 * Función principal que muestra el modal de exportación
 */
function exportarDatos() {
    mostrarModalExportacion();
}

/**
 * Muestra el modal de exportación con las opciones disponibles (sin PDF)
 */
function mostrarModalExportacion() {
    // Eliminar modal existente si hay uno
    const modalExistente = document.getElementById('export-modal');
    if (modalExistente) {
        modalExistente.remove();
    }

    const modalHTML = `
        <div id="export-modal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        ">
            <div style="
                background: white;
                padding: 40px;
                border-radius: 15px;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                width: 90%;
                animation: modalSlideIn 0.3s ease-out;
            ">
                <h3 style="
                    margin-bottom: 30px;
                    color: #2c3e50;
                    font-size: 24px;
                    font-weight: bold;
                ">
                    <i class="fas fa-download"></i>
                    Opciones de Exportación
                </h3>
                <p style="
                    margin-bottom: 30px;
                    color: #7f8c8d;
                    font-size: 16px;
                ">
                    Selecciona el formato de exportación que prefieras:
                </p>
                <div style="
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 15px;
                    margin-bottom: 20px;
                ">
                    <button id="export-png-btn" class="export-modal-button" style="
                        background: #3498db;
                        color: white;
                        border: none;
                        padding: 15px 20px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.3s ease;
                        font-weight: 500;
                    ">
                        <i class="fas fa-image"></i>
                        Gráfico como PNG
                    </button>
                    <button id="export-excel-btn" class="export-modal-button" style="
                        background: #27ae60;
                        color: white;
                        border: none;
                        padding: 15px 20px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: all 0.3s ease;
                        font-weight: 500;
                    ">
                        <i class="fas fa-file-excel"></i>
                        Datos como Excel
                    </button>
                </div>
                <button id="export-cancel-btn" class="export-modal-button" style="
                    background: #95a5a6;
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                    margin: 0 auto;
                ">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
            </div>
        </div>
        <style>
            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        </style>
    `;
    
    // Agregar el modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Agregar efectos hover a los botones
    const modalButtons = document.querySelectorAll('.export-modal-button');
    modalButtons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-2px)';
            button.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.15)';
        });
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0)';
            button.style.boxShadow = 'none';
        });
    });

    // Configurar eventos de los botones
    document.getElementById('export-png-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
        exportarGraficoPNG();
    });

    document.getElementById('export-excel-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
        exportarExcel();
    });

    document.getElementById('export-cancel-btn').addEventListener('click', () => {
        document.getElementById('export-modal').remove();
    });

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('export-modal').addEventListener('click', (e) => {
        if (e.target.id === 'export-modal') {
            e.target.remove();
        }
    });

    // Cerrar modal con tecla ESC
    const handleEscKey = (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('export-modal');
            if (modal) {
                modal.remove();
                document.removeEventListener('keydown', handleEscKey);
            }
        }
    };
    document.addEventListener('keydown', handleEscKey);
}

/**
 * NUEVA FUNCIÓN DE EXPORTACIÓN PNG - VERSIÓN 2.0
 * Completamente rediseñada para ser más robusta y directa
 */
function exportarGraficoPNG() {
    console.log('🆕 NUEVA EXPORTACIÓN PNG v2.0 - Iniciando...');
    
    // PASO 1: Verificaciones básicas y inmediatas
    if (typeof html2canvas === 'undefined') {
        console.error('❌ html2canvas no disponible');
        mostrarMensajeError('La biblioteca html2canvas no está disponible. Recarga la página.');
        return;
    }

    const chartElement = document.getElementById('chart-matricula');
    if (!chartElement) {
        console.error('❌ Elemento del gráfico no encontrado');
        mostrarMensajeError('No se pudo encontrar el gráfico para exportar');
        return;
    }

    console.log('✅ Verificaciones básicas completadas');
    console.log('📊 Elemento del gráfico:', chartElement);
    console.log('📐 Dimensiones visibles:', chartElement.offsetWidth, 'x', chartElement.offsetHeight);

    // PASO 2: Determinar nombre del archivo
    let nombreArchivo = 'Grafico_Matricula.png';
    try {
        if (typeof añoSeleccionado !== 'undefined' && typeof nivelSeleccionado !== 'undefined') {
            if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
                nombreArchivo = 'Grafico_Matricula_Completo.png';
            } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
                nombreArchivo = `Grafico_Matricula_${añoSeleccionado}.png`;
            } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
                nombreArchivo = `Grafico_Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}.png`;
            } else {
                nombreArchivo = `Grafico_Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_${añoSeleccionado}.png`;
            }
        }
    } catch (error) {
        console.warn('⚠️ Error al determinar nombre de archivo, usando nombre por defecto');
    }

    console.log('📁 Nombre de archivo:', nombreArchivo);
    mostrarMensajeExito('Generando imagen PNG...');    // PASO 3: Ejecutar múltiples métodos de captura en secuencia
    console.log('🎯 Iniciando secuencia de métodos de captura...');
    
    // Función asíncrona para manejar la secuencia correctamente
    async function ejecutarSecuenciaCaptura() {
        try {
            // MÉTODO 1: Captura directa optimizada
            console.log('🔄 Intentando Método 1...');
            const exito1 = await ejecutarMetodo1(chartElement, nombreArchivo);
            if (exito1) {
                console.log('✅ Método 1 exitoso - Exportación completada');
                return true;
            }
            
            // MÉTODO 2: Captura con preparación DOM
            console.log('🔄 Método 1 falló, intentando Método 2...');
            const exito2 = await ejecutarMetodo2(chartElement, nombreArchivo);
            if (exito2) {
                console.log('✅ Método 2 exitoso - Exportación completada');
                return true;
            }
            
            // MÉTODO 3: Último recurso
            console.log('🔄 Método 2 falló, intentando Método 3 (último recurso)...');
            const exito3 = await ejecutarMetodo3(chartElement, nombreArchivo);
            if (exito3) {
                console.log('✅ Método 3 exitoso - Exportación completada');
                return true;
            }
            
            // Si llegamos aquí, todos los métodos fallaron
            console.error('❌ Todos los métodos fallaron');
            mostrarMensajeError('No se pudo generar la imagen PNG. Verifica que el gráfico esté completamente cargado.');
            return false;
            
        } catch (error) {
            console.error('❌ Error crítico en exportación:', error);
            mostrarMensajeError('Error crítico en la exportación. Recarga la página e intenta nuevamente.');
            return false;
        }
    }
    
    // Ejecutar la secuencia
    ejecutarSecuenciaCaptura();
}

/**
 * MÉTODO 1: Captura directa con configuración óptima
 */
function ejecutarMetodo1(chartElement, nombreArchivo) {
    return new Promise((resolve) => {
        console.log('🎯 Ejecutando Método 1: Captura directa optimizada');
        
        // NUEVO: Activar valores dinámicos antes de la captura
        const valoresActivados = activarValoresDinamicos();
        
        const configuracion = {
            backgroundColor: '#ffffff',
            scale: 2,
            logging: false,
            useCORS: true,
            allowTaint: true,
            removeContainer: false,
            foreignObjectRendering: true,
            imageTimeout: 10000,
            ignoreElements: function(element) {
                const ignorar = element.tagName === 'BUTTON' || 
                               element.classList.contains('btn') ||
                               element.id === 'export-btn' ||
                               element.classList.contains('chart-controls');
                
                if (ignorar) {
                    console.log('🚫 Ignorando elemento:', element.tagName, element.className || element.id);
                }
                return ignorar;
            }
        };

        html2canvas(chartElement, configuracion).then(canvas => {
            console.log('📸 Método 1 - Captura completada');
            console.log('📊 Dimensiones canvas:', canvas.width, 'x', canvas.height);
            
            // Restaurar estado original
            if (valoresActivados) {
                desactivarValoresDinamicos();
            }
              if (validarCanvas(canvas)) {
                descargarCanvas(canvas, nombreArchivo);
                console.log('✅ Método 1 - Descarga exitosa');
                mostrarMensajeExito('Imagen PNG de alta calidad descargada exitosamente');
                resolve(true);
            } else {
                console.log('❌ Método 1 - Canvas inválido o vacío');
                resolve(false);
            }
        }).catch(error => {
            console.error('❌ Método 1 - Error:', error);
            // Restaurar estado en caso de error
            if (valoresActivados) {
                desactivarValoresDinamicos();
            }
            resolve(false);
        });
    });
}

/**
 * MÉTODO 2: Captura con preparación de DOM
 */
function ejecutarMetodo2(chartElement, nombreArchivo) {
    return new Promise((resolve) => {
        console.log('🎯 Ejecutando Método 2: Captura con preparación DOM');
        
        // NUEVO: Activar valores dinámicos antes de la captura
        const valoresActivados = activarValoresDinamicos();
        
        // Aplicar mejoras temporales al DOM
        const restaurar = aplicarMejorasDOM(chartElement);
        
        setTimeout(() => {
            const configuracion = {
                backgroundColor: '#ffffff',
                scale: 3,
                logging: false,
                useCORS: true,
                allowTaint: true,
                width: chartElement.offsetWidth + 100,
                height: chartElement.offsetHeight + 100,
                x: -50,
                y: -50,
                removeContainer: false,
                foreignObjectRendering: false, // Cambiar estrategia
                ignoreElements: function(element) {
                    return element.tagName === 'BUTTON' || 
                           element.classList.contains('btn') ||
                           element.id === 'export-btn';
                }
            };

            html2canvas(chartElement, configuracion).then(canvas => {
                restaurar(); // Restaurar DOM inmediatamente
                
                // Restaurar valores dinámicos
                if (valoresActivados) {
                    desactivarValoresDinamicos();
                }
                
                console.log('📸 Método 2 - Captura completada');
                console.log('📊 Dimensiones canvas:', canvas.width, 'x', canvas.height);
                  if (validarCanvas(canvas)) {
                    descargarCanvas(canvas, nombreArchivo);
                    console.log('✅ Método 2 - Descarga exitosa');
                    mostrarMensajeExito('Imagen PNG de alta resolución descargada exitosamente');
                    resolve(true);
                } else {
                    console.log('❌ Método 2 - Canvas inválido o vacío');
                    resolve(false);
                }
            }).catch(error => {
                restaurar(); // Restaurar DOM en caso de error
                
                // Restaurar valores dinámicos en caso de error
                if (valoresActivados) {
                    desactivarValoresDinamicos();
                }
                
                console.error('❌ Método 2 - Error:', error);
                resolve(false);
            });
        }, 300); // Dar tiempo para que las mejoras DOM se apliquen
    });
}

/**
 * MÉTODO 3: Último recurso - Captura simple con configuración mínima
 */
function ejecutarMetodo3(chartElement, nombreArchivo) {
    return new Promise((resolve) => {
        console.log('🎯 Ejecutando Método 3: Último recurso - configuración mínima');
        
        const configuracionMinima = {
            backgroundColor: '#ffffff',
            scale: 1, // Reducir escala para mayor compatibilidad
            logging: true, // Activar logging para debug
            useCORS: false,
            allowTaint: false,
            removeContainer: true,
            foreignObjectRendering: false
        };

        html2canvas(chartElement, configuracionMinima).then(canvas => {
            console.log('📸 Método 3 - Captura completada');
            console.log('📊 Dimensiones canvas:', canvas.width, 'x', canvas.height);
            
            if (canvas.width > 0 && canvas.height > 0) {
                descargarCanvas(canvas, nombreArchivo);
                console.log('✅ Método 3 - Descarga exitosa (configuración mínima)');
                mostrarMensajeExito('Imagen PNG descargada exitosamente (método simplificado)');
                resolve(true);
            } else {
                console.log('❌ Método 3 - Canvas con dimensiones cero');
                resolve(false);
            }
        }).catch(error => {
            console.error('❌ Método 3 - Error:', error);
            resolve(false);
        });
    });
}

/**
 * Valida que el canvas tenga contenido válido (versión mejorada)
 */
function validarCanvas(canvas) {
    try {
        // Verificar dimensiones
        if (canvas.width === 0 || canvas.height === 0) {
            console.log('❌ Canvas con dimensiones inválidas:', canvas.width, 'x', canvas.height);
            return false;
        }

        console.log(`🔍 Validando canvas de ${canvas.width}x${canvas.height}`);

        // Para canvas grandes de alta resolución, usar validación más permisiva
        const context = canvas.getContext('2d');
        
        // Estrategia inteligente basada en el tamaño del canvas
        const isHighRes = canvas.width > 1500 || canvas.height > 1000;
        const sampleSize = isHighRes ? 50 : 30; // Muestras más grandes para alta resolución
        
        // Tomar múltiples muestras estratégicas
        const sampleAreas = [
            { x: Math.floor(canvas.width * 0.15), y: Math.floor(canvas.height * 0.15), size: sampleSize }, // Superior izquierda
            { x: Math.floor(canvas.width * 0.5), y: Math.floor(canvas.height * 0.4), size: sampleSize },   // Centro-superior
            { x: Math.floor(canvas.width * 0.85), y: Math.floor(canvas.height * 0.25), size: sampleSize }, // Superior derecha
            { x: Math.floor(canvas.width * 0.3), y: Math.floor(canvas.height * 0.7), size: sampleSize },   // Inferior izquierda
            { x: Math.floor(canvas.width * 0.7), y: Math.floor(canvas.height * 0.7), size: sampleSize },   // Inferior derecha
            { x: Math.floor(canvas.width * 0.1), y: Math.floor(canvas.height * 0.5), size: sampleSize },   // Centro izquierda (eje Y)
            { x: Math.floor(canvas.width * 0.5), y: Math.floor(canvas.height * 0.9), size: sampleSize }    // Inferior centro (eje X)
        ];

        let totalPixelsChecked = 0;
        let coloredPixels = 0;
        let validAreas = 0;
        let significantColorPixels = 0; // Píxeles con colores definitivamente no blancos

        for (const area of sampleAreas) {
            try {
                const maxX = Math.min(area.x + area.size, canvas.width);
                const maxY = Math.min(area.y + area.size, canvas.height);
                const width = maxX - area.x;
                const height = maxY - area.y;
                
                if (width <= 0 || height <= 0) continue;

                const imageData = context.getImageData(area.x, area.y, width, height);
                let areaHasContent = false;
                let areaSignificantContent = false;
                
                for (let i = 0; i < imageData.data.length; i += 4) {
                    const r = imageData.data[i];
                    const g = imageData.data[i + 1];
                    const b = imageData.data[i + 2];
                    const a = imageData.data[i + 3];
                    
                    totalPixelsChecked++;
                    
                    // Detección más flexible para alta resolución
                    const threshold = isHighRes ? 240 : 250; // Umbral más bajo para alta resolución
                    const alphaThreshold = isHighRes ? 5 : 10; // Umbral alpha más bajo
                    
                    if (a > alphaThreshold && (r < threshold || g < threshold || b < threshold)) {
                        coloredPixels++;
                        areaHasContent = true;
                        
                        // Detectar colores definitivamente significativos (no blancos/grises claros)
                        if (r < 200 || g < 200 || b < 200) {
                            significantColorPixels++;
                            areaSignificantContent = true;
                        }
                    }
                }
                
                if (areaHasContent) {
                    validAreas++;
                }
            } catch (sampleError) {
                console.log(`⚠️ Error al muestrear área ${area.x},${area.y}:`, sampleError.message);
            }
        }

        const contentRatio = totalPixelsChecked > 0 ? (coloredPixels / totalPixelsChecked) : 0;
        const significantRatio = totalPixelsChecked > 0 ? (significantColorPixels / totalPixelsChecked) : 0;
        
        console.log(`📊 Análisis detallado de canvas:`);
        console.log(`   - Resolución: ${isHighRes ? 'ALTA' : 'NORMAL'} (${canvas.width}x${canvas.height})`);
        console.log(`   - Píxeles analizados: ${totalPixelsChecked}`);
        console.log(`   - Píxeles con contenido: ${coloredPixels}`);
        console.log(`   - Píxeles significativos: ${significantColorPixels}`);
        console.log(`   - Ratio de contenido: ${(contentRatio * 100).toFixed(2)}%`);
        console.log(`   - Ratio significativo: ${(significantRatio * 100).toFixed(2)}%`);
        console.log(`   - Áreas válidas: ${validAreas}/${sampleAreas.length}`);

        // Criterios de validación adaptados por resolución
        let isValid = false;
        
        if (isHighRes) {
            // Para alta resolución: criterios más permisivos
            isValid = validAreas >= 3 ||                    // Al menos 3 áreas con contenido
                     contentRatio > 0.002 ||               // Ratio mínimo 0.2%
                     significantColorPixels > 30 ||         // Al menos 30 píxeles significativos
                     coloredPixels > 100;                   // O más de 100 píxeles con contenido
        } else {
            // Para resolución normal: criterios estándar
            isValid = validAreas >= 2 ||                    // Al menos 2 áreas con contenido
                     contentRatio > 0.005 ||               // Ratio mínimo 0.5%
                     significantColorPixels > 20 ||         // Al menos 20 píxeles significativos
                     coloredPixels > 50;                    // O más de 50 píxeles con contenido
        }

        if (isValid) {
            console.log(`✅ Canvas validado correctamente (${isHighRes ? 'alta resolución' : 'resolución normal'})`);
            return true;
        } else {
            console.log(`❌ Canvas parece estar vacío - Motivos:`);
            console.log(`   - Áreas válidas insuficientes: ${validAreas} (mín: ${isHighRes ? 3 : 2})`);
            console.log(`   - Ratio contenido bajo: ${(contentRatio * 100).toFixed(2)}% (mín: ${isHighRes ? '0.2' : '0.5'}%)`);
            console.log(`   - Píxeles significativos: ${significantColorPixels} (mín: ${isHighRes ? 30 : 20})`);
            return false;
        }
        
    } catch (error) {
        console.error('❌ Error al validar canvas:', error);
        // En caso de error, para alta resolución ser más permisivo
        const isHighRes = canvas.width > 1500 || canvas.height > 1000;
        const valid = canvas.width > 0 && canvas.height > 0;
        console.log(`⚠️ Validación por error - Canvas ${isHighRes ? 'alta resolución' : 'normal'}: ${valid ? 'VÁLIDO' : 'INVÁLIDO'}`);
        return valid;
    }
}

/**
 * Descarga el canvas como archivo PNG
 */
function descargarCanvas(canvas, nombreArchivo) {
    try {
        const dataURL = canvas.toDataURL('image/png', 1.0);
        
        // Verificar que el dataURL sea válido
        if (!dataURL || !dataURL.startsWith('data:image/png;base64,')) {
            console.error('❌ DataURL inválido');
            return false;
        }

        // Crear enlace de descarga
        const link = document.createElement('a');
        link.download = nombreArchivo;
        link.href = dataURL;
        link.style.display = 'none';

        // Descargar
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        console.log('✅ Descarga completada:', nombreArchivo);
        return true;
        
    } catch (error) {
        console.error('❌ Error al descargar canvas:', error);
        return false;
    }
}

/**
 * Aplica mejoras temporales al DOM para mejor captura
 */
function aplicarMejorasDOM(chartElement) {
    console.log('🎨 Aplicando mejoras temporales al DOM...');
    
    const cambios = [];
    
    // Mejorar el contenedor principal
    const container = chartElement.closest('.chart-container');
    if (container) {
        const originalStyle = container.style.cssText;
        cambios.push(() => container.style.cssText = originalStyle);
        
        container.style.padding = '30px';
        container.style.backgroundColor = '#ffffff';
        container.style.minHeight = '500px';
    }

    // Mejorar elementos de texto
    const textElements = chartElement.querySelectorAll('text');
    textElements.forEach(text => {
        const originalStyle = text.style.cssText;
        cambios.push(() => text.style.cssText = originalStyle);
        
        text.style.fontSize = '12px';
        text.style.fontFamily = 'Arial, sans-serif';
        text.style.fill = '#333';
    });

    console.log('✅ Mejoras DOM aplicadas');
    
    // Función para restaurar cambios
    return function restaurarMejoras() {
        console.log('🔄 Restaurando mejoras DOM...');
        cambios.forEach(restaurar => restaurar());
        console.log('✅ Mejoras DOM restauradas');
    };
}

/**
 * Exporta los datos a Excel según los filtros actuales
 */
function exportarExcel() {
    console.log('📊 Iniciando exportación Excel...');
    
    // Determinar qué datos exportar según los filtros seleccionados
    let datos;
    let nombreArchivo;
    
    try {
        if (typeof añoSeleccionado !== 'undefined' && typeof nivelSeleccionado !== 'undefined' && typeof datosMatriculaAgrupados !== 'undefined') {
            if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
                datos = datosMatriculaAgrupados.todos;
                nombreArchivo = 'Matricula_Todos_Los_Años.xlsx';
            } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
                datos = datosMatriculaAgrupados.anual[añoSeleccionado];
                nombreArchivo = `Matricula_${añoSeleccionado}.xlsx`;
            } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
                datos = datosMatriculaAgrupados.nivel[nivelSeleccionado];
                nombreArchivo = `Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_Todos_Los_Años.xlsx`;
            } else {
                // Un año y un nivel específicos
                datos = [['Nivel Educativo', 'Cantidad de Alumnos']];
                const valorNivel = datosMatricula[añoSeleccionado][nivelSeleccionado] || 0;
                datos.push([nivelSeleccionado, valorNivel]);
                nombreArchivo = `Matricula_${nivelSeleccionado.replace(/\s+/g, '_')}_${añoSeleccionado}.xlsx`;
            }
        } else {
            // Datos por defecto si las variables no están disponibles
            datos = [
                ['Información', 'Valor'],
                ['Error', 'Variables no disponibles'],
                ['Recarga la página', 'e intenta nuevamente']
            ];
            nombreArchivo = 'Matricula_Error.xlsx';
        }
        
        // Crear un libro de Excel
        const wb = XLSX.utils.book_new();
        
        // Crear una hoja de datos
        const ws = XLSX.utils.aoa_to_sheet(datos);
        
        // Añadir la hoja al libro
        XLSX.utils.book_append_sheet(wb, ws, "Matrícula Escolar");
        
        // Generar el archivo y descargarlo
        XLSX.writeFile(wb, nombreArchivo);
        
        console.log('✅ Excel generado exitosamente');
        mostrarMensajeExito('Archivo Excel generado exitosamente');
        
    } catch (error) {
        console.error('❌ Error al generar Excel:', error);
        mostrarMensajeError('Error al generar el archivo Excel');
    }
}

/**
 * Muestra un mensaje de éxito temporal
 */
function mostrarMensajeExito(mensaje) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #27ae60;
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 10001;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.3s ease-out;
        font-family: Arial, sans-serif;
        font-size: 14px;
    `;
    notification.innerHTML = `<i class="fas fa-check-circle"></i> ${mensaje}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

/**
 * Muestra un mensaje de error temporal
 */
function mostrarMensajeError(mensaje) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #e74c3c;
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 10001;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.3s ease-out;
        font-family: Arial, sans-serif;
        font-size: 14px;
    `;
    notification.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${mensaje}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 4000);
}

// Agregar estilos para las animaciones
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(styleSheet);

/**
 * =====================================================
 * FUNCIONES PARA VALORES DINÁMICOS EN EXPORTACIÓN PNG
 * =====================================================
 */

/**
 * Activa la visualización de valores dinámicos en las barras del gráfico
 * para mejorar la calidad de la exportación PNG
 * @returns {boolean} True si se activaron correctamente los valores
 */
function activarValoresDinamicos() {
    try {
        console.log('🎨 Activando valores dinámicos en las barras...');
        
        // Verificar si Google Charts está disponible y el gráfico existe
        if (typeof google === 'undefined' || !google.charts || !chartMatricula) {
            console.warn('⚠️ Google Charts o chartMatricula no disponible');
            return false;
        }

        // Obtener los datos actuales del gráfico
        let datos;
        try {
            if (typeof añoSeleccionado !== 'undefined' && typeof nivelSeleccionado !== 'undefined' && typeof datosMatriculaAgrupados !== 'undefined') {
                if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
                    datos = datosMatriculaAgrupados.todos;
                } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
                    datos = datosMatriculaAgrupados.anual[añoSeleccionado];
                } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
                    datos = datosMatriculaAgrupados.nivel[nivelSeleccionado];
                } else {
                    // Caso específico: un año y un nivel
                    datos = [['Nivel Educativo', 'Cantidad de Alumnos']];
                    const valorNivel = datosMatricula[añoSeleccionado][nivelSeleccionado] || 0;
                    datos.push([nivelSeleccionado, valorNivel]);
                }
            } else {
                console.warn('⚠️ Variables de filtros no disponibles');
                return false;
            }
        } catch (error) {
            console.warn('⚠️ Error al obtener datos para valores dinámicos:', error);
            return false;
        }

        // Crear una copia de los datos con anotaciones para valores
        const datosConValores = agregarAnotacionesParaExportacion(datos);
        
        if (!datosConValores) {
            console.warn('⚠️ No se pudieron agregar anotaciones');
            return false;
        }

        // Crear DataTable con los valores
        const dataTableConValores = google.visualization.arrayToDataTable(datosConValores);
        
        // Obtener las opciones actuales y añadir configuraciones para mostrar valores
        const opciones = obtenerOpcionesConValores();
        
        // Redibujar el gráfico con valores visibles
        chartMatricula.draw(dataTableConValores, opciones);
        
        console.log('✅ Valores dinámicos activados correctamente');
        return true;
        
    } catch (error) {
        console.error('❌ Error al activar valores dinámicos:', error);
        return false;
    }
}

/**
 * Desactiva los valores dinámicos y restaura el gráfico original
 */
function desactivarValoresDinamicos() {
    try {
        console.log('🔄 Restaurando gráfico original...');
        
        // Verificar disponibilidad
        if (typeof google === 'undefined' || !google.charts || !chartMatricula) {
            console.warn('⚠️ Google Charts o chartMatricula no disponible para restaurar');
            return;
        }

        // Llamar a la función de actualización normal para restaurar el estado original
        if (typeof actualizarVisualizacion === 'function') {
            actualizarVisualizacion();
            console.log('✅ Gráfico original restaurado');
        } else {
            console.warn('⚠️ Función actualizarVisualizacion no disponible');
        }
        
    } catch (error) {
        console.error('❌ Error al restaurar gráfico original:', error);
    }
}

/**
 * Agrega anotaciones de valores a los datos para exportación
 * @param {Array} datos - Datos originales del gráfico
 * @returns {Array|null} Datos con anotaciones o null si falla
 */
function agregarAnotacionesParaExportacion(datos) {
    try {
        if (!datos || datos.length < 2) {
            console.warn('⚠️ Datos insuficientes para agregar anotaciones');
            return null;
        }

        console.log('📝 Agregando anotaciones a los datos...');
        
        // Crear nueva estructura de datos con anotaciones
        const datosConAnotaciones = [];
        
        // Procesar encabezados
        const encabezadosOriginales = datos[0];
        const nuevosEncabezados = [];
        
        // El primer elemento es siempre la etiqueta (Año o Nivel)
        nuevosEncabezados.push(encabezadosOriginales[0]);
        
        // Para cada columna de datos (excluyendo la primera que es la etiqueta y la última que puede ser "Total")
        for (let i = 1; i < encabezadosOriginales.length; i++) {
            const columna = encabezadosOriginales[i];
            
            // No procesar la columna "Total" para evitar el error de 17 vs 15 columnas
            if (columna && columna.toString().toLowerCase() !== 'total') {
                nuevosEncabezados.push(columna); // Valor de la columna
                nuevosEncabezados.push({type: 'string', role: 'annotation'}); // Anotación para mostrar el valor
            }
        }
        
        datosConAnotaciones.push(nuevosEncabezados);
        
        // Procesar filas de datos
        for (let i = 1; i < datos.length; i++) {
            const filaOriginal = datos[i];
            const nuevaFila = [];
            
            // Agregar la etiqueta (primer elemento)
            nuevaFila.push(filaOriginal[0]);
            
            // Procesar cada valor de datos (excluyendo etiqueta y total)
            for (let j = 1; j < filaOriginal.length; j++) {
                const valor = filaOriginal[j];
                const columna = encabezadosOriginales[j];
                
                // No procesar la columna "Total"
                if (columna && columna.toString().toLowerCase() !== 'total') {
                    nuevaFila.push(valor); // Valor numérico
                    
                    // Agregar anotación solo para valores > 0
                    if (valor && valor > 0) {
                        nuevaFila.push(valor.toString()); // Anotación con el valor
                    } else {
                        nuevaFila.push(''); // Anotación vacía para valores 0
                    }
                }
            }
            
            datosConAnotaciones.push(nuevaFila);
        }
        
        console.log('✅ Anotaciones agregadas correctamente');
        console.log('📊 Datos originales:', datos.length, 'x', datos[0].length);
        console.log('📊 Datos con anotaciones:', datosConAnotaciones.length, 'x', datosConAnotaciones[0].length);
        
        return datosConAnotaciones;
        
    } catch (error) {
        console.error('❌ Error al agregar anotaciones:', error);
        return null;
    }
}

/**
 * Obtiene las opciones del gráfico optimizadas para mostrar valores en las barras
 * @returns {Object} Opciones configuradas para exportación con valores
 */
function obtenerOpcionesConValores() {
    const opciones = {
        title: obtenerTituloGrafico(),
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
        legend: { position: 'none' },
        colors: obtenerColoresGrafico(),
        hAxis: {
            title: obtenerEtiquetaEjeX(),
            gridlines: {color: '#f5f5f5'},
            textStyle: {
                fontSize: 11,
                color: '#555',
                fontName: 'Arial'
            },
            slantedText: false,
            maxTextLines: 2,
            showTextEvery: 1,
            minTextSpacing: 0
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
            }
        },
        bar: { groupWidth: '75%' },
        backgroundColor: {
            fill: '#ffffff',
            stroke: '#f5f5f5',
            strokeWidth: 1
        },
        // CONFIGURACIÓN ESPECÍFICA PARA MOSTRAR VALORES EN LAS BARRAS
        annotations: {
            alwaysOutside: true,
            textStyle: {
                fontSize: 11,
                color: '#333',
                fontName: 'Arial',
                bold: true
            },
            stemColor: 'transparent', // Ocultar líneas de conexión
            stemLength: 0
        },
        // Mejorar la calidad para exportación
        forceIFrame: false,
        allowHtml: true,
        enableInteractivity: false // Desactivar interactividad para mejor exportación
    };
    
    return opciones;
}

/**
 * Funciones auxiliares para obtener información del gráfico actual
 */
function obtenerTituloGrafico() {
    try {
        if (typeof añoSeleccionado !== 'undefined' && typeof nivelSeleccionado !== 'undefined') {
            if (añoSeleccionado === 'todos' && nivelSeleccionado === 'todos') {
                return 'Matrícula por Nivel Educativo (Todos los Años)';
            } else if (añoSeleccionado !== 'todos' && nivelSeleccionado === 'todos') {
                return `Matrícula por Nivel Educativo - ${añoSeleccionado}`;
            } else if (añoSeleccionado === 'todos' && nivelSeleccionado !== 'todos') {
                return `${nivelSeleccionado} - Todos los Años`;
            } else {
                return `${nivelSeleccionado} - ${añoSeleccionado}`;
            }
        }
    } catch (error) {
        console.warn('⚠️ Error al obtener título:', error);
    }
    return 'Matrícula Estudiantil';
}

function obtenerEtiquetaEjeX() {
    try {
        if (typeof añoSeleccionado !== 'undefined' && typeof nivelSeleccionado !== 'undefined') {
            if (añoSeleccionado === 'todos') {
                return 'Año Escolar';
            } else if (nivelSeleccionado === 'todos') {
                return 'Nivel Educativo';
            }
        }
    } catch (error) {
        console.warn('⚠️ Error al obtener etiqueta eje X:', error);
    }
    return 'Categoría';
}

function obtenerColoresGrafico() {
    // Colores consistentes con el gráfico original
    const coloresBase = {
        'Inicial NE': '#3949AB',
        'CAM': '#00897B',
        'Preescolar': '#FB8C00',
        'Primaria': '#E53935',
        'Secundaria': '#5E35B1',
        'Media superior': '#43A047',
        'Superior': '#8E24AA'
    };
    
    try {
        if (typeof nivelSeleccionado !== 'undefined' && nivelSeleccionado !== 'todos') {
            return [coloresBase[nivelSeleccionado] || '#1f77b4'];
        }
    } catch (error) {
        console.warn('⚠️ Error al obtener colores:', error);
    }
    
    return Object.values(coloresBase);
}

// ...existing code...
