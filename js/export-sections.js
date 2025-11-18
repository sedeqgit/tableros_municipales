/**
 * =============================================================================
 * MÓDULO DE EXPORTACIÓN DE SECCIONES HTML - SISTEMA SEDEQ
 * =============================================================================
 *
 * Módulo compartido para exportar secciones completas de HTML a PNG y PDF
 * manteniendo todos los estilos visuales.
 *
 * FUNCIONALIDADES:
 * - Exportación de secciones a PNG de alta calidad
 * - Exportación de secciones a PDF en formato landscape
 * - Preservación completa de estilos CSS, gradientes y colores
 * - Mensajes de carga y éxito animados
 * - Manejo robusto de errores
 *
 * USO:
 * 1. Incluir este archivo en el HTML:
 *    <script src="./js/export-sections.js"></script>
 * 2. Llamar a las funciones globales:
 *    exportarSeccionPNG('id-de-la-seccion')
 *    exportarSeccionPDF('id-de-la-seccion')
 *
 * REQUISITOS:
 * - html2canvas (https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js)
 * - jsPDF (https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js)
 *
 * @version 1.0
 * @package SEDEQ_Dashboard
 */

(function() {
    'use strict';

    // =============================================================================
    // FUNCIONES DE EXPORTACIÓN
    // =============================================================================

    /**
     * Exporta una sección completa como imagen PNG
     * @param {string} idSeccion - ID del elemento a exportar
     */
    function exportarSeccionPNG(idSeccion) {
        console.log(`📸 Exportando sección "${idSeccion}" a PNG...`);

        const elemento = document.getElementById(idSeccion);

        if (!elemento) {
            console.error(`❌ No se encontró el elemento con ID "${idSeccion}"`);
            mostrarError('No se pudo encontrar la sección a exportar');
            return;
        }

        // Mostrar mensaje de carga
        const mensaje = mostrarMensajeCarga('Generando imagen PNG...');

        // Guardar estilos originales para restaurar después
        const estilosOriginales = {
            transform: elemento.style.transform,
            transition: elemento.style.transition,
            animation: elemento.style.animation,
            position: elemento.style.position,
            width: elemento.style.width,
            overflow: elemento.style.overflow
        };

        // Preparar elemento para captura
        elemento.style.transform = 'none';
        elemento.style.transition = 'none';
        elemento.style.animation = 'none';
        elemento.style.overflow = 'visible';

        // Esperar un momento para que se apliquen los estilos
        setTimeout(() => {
            // Configuración de html2canvas para captura de alta calidad
            const opciones = {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: '#f8f9fa',
                logging: true,
                width: elemento.scrollWidth,
                height: elemento.scrollHeight,
                x: 0,
                y: 0,
                scrollY: -window.scrollY,
                scrollX: -window.scrollX,
                onclone: function(clonedDoc) {
                    const clonedElement = clonedDoc.getElementById(idSeccion);
                    if (clonedElement) {
                        // Asegurar visibilidad total
                        clonedElement.style.transform = 'none';
                        clonedElement.style.opacity = '1';
                        clonedElement.style.visibility = 'visible';
                        clonedElement.style.display = 'block';
                        clonedElement.style.position = 'relative';
                        clonedElement.style.overflow = 'visible';

                        // Remover cualquier clase de animación
                        clonedElement.classList.remove('animate-fade', 'animate-up', 'delay-1', 'delay-2', 'delay-3', 'delay-4');

                        // Asegurar que todos los hijos sean visibles
                        const todosLosElementos = clonedElement.querySelectorAll('*');
                        todosLosElementos.forEach(el => {
                            el.style.transform = 'none';
                            el.style.opacity = '1';
                        });

                        // Ocultar botones de exportación si existen
                        const exportBtns = clonedElement.querySelector('.export-buttons-section');
                        if (exportBtns) {
                            exportBtns.style.display = 'none';
                        }
                        const exportButtons = clonedElement.querySelector('.export-buttons');
                        if (exportButtons) {
                            exportButtons.style.display = 'none';
                        }
                    }
                }
            };

            // Capturar el elemento como canvas
            html2canvas(elemento, opciones).then(canvas => {
                try {
                    // Restaurar estilos originales
                    Object.keys(estilosOriginales).forEach(key => {
                        elemento.style[key] = estilosOriginales[key];
                    });

                    console.log('Canvas generado:', canvas.width, 'x', canvas.height);

                    // Verificar que el canvas no esté vacío
                    if (canvas.width === 0 || canvas.height === 0) {
                        throw new Error('Canvas vacío generado');
                    }

                    // Convertir canvas a blob y descargar
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            ocultarMensajeCarga(mensaje);
                            mostrarError('Error al generar la imagen');
                            return;
                        }

                        const url = URL.createObjectURL(blob);
                        const enlace = document.createElement('a');
                        const fecha = new Date().toISOString().slice(0, 10);
                        enlace.href = url;
                        enlace.download = `sedeq-${idSeccion}-${fecha}.png`;
                        document.body.appendChild(enlace);
                        enlace.click();
                        document.body.removeChild(enlace);

                        // Limpiar
                        setTimeout(() => URL.revokeObjectURL(url), 100);
                        ocultarMensajeCarga(mensaje);

                        console.log('✅ Exportación PNG completada');
                        mostrarMensajeExito('Imagen PNG descargada correctamente');
                    }, 'image/png', 1.0);
                } catch (error) {
                    console.error('❌ Error al exportar PNG:', error);
                    ocultarMensajeCarga(mensaje);
                    mostrarError('Error al generar la imagen PNG');

                    // Restaurar estilos originales
                    Object.keys(estilosOriginales).forEach(key => {
                        elemento.style[key] = estilosOriginales[key];
                    });
                }
            }).catch(error => {
                console.error('❌ Error en html2canvas:', error);
                ocultarMensajeCarga(mensaje);
                mostrarError('Error al capturar la sección: ' + error.message);

                // Restaurar estilos originales
                Object.keys(estilosOriginales).forEach(key => {
                    elemento.style[key] = estilosOriginales[key];
                });
            });
        }, 300);
    }

    /**
     * Exporta una sección completa como PDF
     * @param {string} idSeccion - ID del elemento a exportar
     */
    function exportarSeccionPDF(idSeccion) {
        console.log(`📄 Exportando sección "${idSeccion}" a PDF...`);

        const elemento = document.getElementById(idSeccion);

        if (!elemento) {
            console.error(`❌ No se encontró el elemento con ID "${idSeccion}"`);
            mostrarError('No se pudo encontrar la sección a exportar');
            return;
        }

        // Mostrar mensaje de carga
        const mensaje = mostrarMensajeCarga('Generando documento PDF...');

        // Guardar estilos originales
        const estilosOriginales = {
            transform: elemento.style.transform,
            transition: elemento.style.transition,
            animation: elemento.style.animation,
            boxShadow: elemento.style.boxShadow,
            overflow: elemento.style.overflow
        };

        // Preparar elemento para captura
        elemento.style.transform = 'none';
        elemento.style.transition = 'none';
        elemento.style.animation = 'none';
        elemento.style.overflow = 'visible';

        setTimeout(() => {
            // Configuración de html2canvas
            const opcionesCanvas = {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: '#ffffff',
                logging: true,
                width: elemento.scrollWidth,
                height: elemento.scrollHeight,
                x: 0,
                y: 0,
                scrollY: -window.scrollY,
                scrollX: -window.scrollX,
                onclone: function(clonedDoc) {
                    const clonedElement = clonedDoc.getElementById(idSeccion);
                    if (clonedElement) {
                        // Asegurar visibilidad total
                        clonedElement.style.transform = 'none';
                        clonedElement.style.opacity = '1';
                        clonedElement.style.visibility = 'visible';
                        clonedElement.style.display = 'block';
                        clonedElement.style.position = 'relative';
                        clonedElement.style.overflow = 'visible';
                        clonedElement.style.filter = 'none';

                        // Remover clases de animación
                        clonedElement.classList.remove('animate-fade', 'animate-up', 'delay-1', 'delay-2', 'delay-3', 'delay-4');

                        // Simplificar headers para mejor renderizado
                        const headers = clonedElement.querySelectorAll('.matricula-header, .panel-header');
                        headers.forEach(header => {
                            header.style.background = '#4996C4';
                            header.style.backgroundImage = 'none';
                        });

                        // Limpiar todos los elementos
                        const todosLosElementos = clonedElement.querySelectorAll('*');
                        todosLosElementos.forEach(el => {
                            el.style.transform = 'none';
                            el.style.opacity = '1';
                            el.style.filter = 'none';

                            // Simplificar sombras
                            if (el.classList.contains('total-municipal-card') ||
                                el.classList.contains('nivel-municipal-card') ||
                                el.classList.contains('stat-box')) {
                                el.style.boxShadow = '0 1px 3px rgba(0,0,0,0.12)';
                            }

                            // Convertir gradientes a colores sólidos
                            const computedStyle = window.getComputedStyle(el);
                            if (computedStyle.backgroundImage && computedStyle.backgroundImage.includes('gradient')) {
                                el.style.backgroundImage = 'none';
                                el.style.backgroundColor = '#4996C4';
                            }
                        });

                        // Ocultar botones de exportación en el PDF
                        const exportBtns = clonedElement.querySelector('.export-buttons-section');
                        if (exportBtns) {
                            exportBtns.style.display = 'none';
                        }
                        const exportButtons = clonedElement.querySelector('.export-buttons');
                        if (exportButtons) {
                            exportButtons.style.display = 'none';
                        }
                    }
                }
            };

            // Capturar con html2canvas
            html2canvas(elemento, opcionesCanvas).then(canvas => {
                try {
                    // Restaurar estilos originales
                    Object.keys(estilosOriginales).forEach(key => {
                        elemento.style[key] = estilosOriginales[key];
                    });

                    console.log('Canvas capturado para PDF:', canvas.width, 'x', canvas.height);

                    if (canvas.width === 0 || canvas.height === 0) {
                        throw new Error('Canvas vacío generado');
                    }

                    // Crear PDF con jsPDF directamente
                    const { jsPDF } = window.jspdf;

                    // Calcular dimensiones para landscape A4
                    const pdfWidth = 297; // mm (A4 landscape)
                    const pdfHeight = 210; // mm (A4 landscape)

                    // Calcular escala para ajustar la imagen
                    const canvasRatio = canvas.height / canvas.width;
                    let imgWidth = pdfWidth - 20; // Márgenes de 10mm a cada lado
                    let imgHeight = imgWidth * canvasRatio;

                    // Si la altura es mayor que el espacio disponible, ajustar
                    if (imgHeight > pdfHeight - 20) {
                        imgHeight = pdfHeight - 20;
                        imgWidth = imgHeight / canvasRatio;
                    }

                    const pdf = new jsPDF({
                        orientation: 'landscape',
                        unit: 'mm',
                        format: 'a4'
                    });

                    // Convertir canvas a imagen
                    const imgData = canvas.toDataURL('image/jpeg', 0.95);

                    // Agregar imagen al PDF centrada
                    const xOffset = (pdfWidth - imgWidth) / 2;
                    const yOffset = (pdfHeight - imgHeight) / 2;

                    pdf.addImage(imgData, 'JPEG', xOffset, yOffset, imgWidth, imgHeight);

                    // Descargar PDF
                    const fecha = new Date().toISOString().slice(0, 10);
                    pdf.save(`sedeq-${idSeccion}-${fecha}.pdf`);

                    ocultarMensajeCarga(mensaje);
                    console.log('✅ Exportación PDF completada');
                    mostrarMensajeExito('Documento PDF descargado correctamente');

                } catch (error) {
                    console.error('❌ Error al crear PDF:', error);

                    // Restaurar estilos
                    Object.keys(estilosOriginales).forEach(key => {
                        elemento.style[key] = estilosOriginales[key];
                    });

                    ocultarMensajeCarga(mensaje);
                    mostrarError('Error al generar el documento PDF: ' + error.message);
                }
            }).catch(error => {
                console.error('❌ Error en html2canvas:', error);

                // Restaurar estilos
                Object.keys(estilosOriginales).forEach(key => {
                    elemento.style[key] = estilosOriginales[key];
                });

                ocultarMensajeCarga(mensaje);
                mostrarError('Error al capturar la sección: ' + error.message);
            });
        }, 300);
    }

    // =============================================================================
    // FUNCIONES AUXILIARES DE UI
    // =============================================================================

    /**
     * Muestra un mensaje de carga
     */
    function mostrarMensajeCarga(texto) {
        const mensaje = document.createElement('div');
        mensaje.className = 'mensaje-carga-export';
        mensaje.innerHTML = `
            <div class="spinner-export"></div>
            <span>${texto}</span>
        `;
        mensaje.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            font-family: var(--font-primary);
            color: #242B57;
            font-size: 16px;
            font-weight: 500;
        `;

        // Agregar estilos para el spinner solo si no existen
        if (!document.getElementById('export-spinner-styles')) {
            const style = document.createElement('style');
            style.id = 'export-spinner-styles';
            style.textContent = `
                .spinner-export {
                    width: 40px;
                    height: 40px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #4996C4;
                    border-radius: 50%;
                    animation: spin-export 1s linear infinite;
                }

                @keyframes spin-export {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                .mensaje-carga-export {
                    animation: fadeInExport 0.3s ease;
                }

                @keyframes fadeInExport {
                    from { opacity: 0; transform: translate(-50%, -45%); }
                    to { opacity: 1; transform: translate(-50%, -50%); }
                }

                @keyframes fadeOutExport {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }

                @keyframes slideInRight {
                    from { transform: translateX(400px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }

                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(400px); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(mensaje);
        return mensaje;
    }

    /**
     * Oculta el mensaje de carga
     */
    function ocultarMensajeCarga(mensaje) {
        if (mensaje && mensaje.parentNode) {
            mensaje.style.animation = 'fadeOutExport 0.3s ease';
            setTimeout(() => {
                if (mensaje.parentNode) {
                    mensaje.parentNode.removeChild(mensaje);
                }
            }, 300);
        }
    }

    /**
     * Muestra un mensaje de éxito
     */
    function mostrarMensajeExito(texto) {
        const mensaje = document.createElement('div');
        mensaje.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${texto}</span>
        `;
        mensaje.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: var(--font-primary);
            font-size: 14px;
            font-weight: 500;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(mensaje);

        setTimeout(() => {
            mensaje.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (mensaje.parentNode) {
                    mensaje.parentNode.removeChild(mensaje);
                }
            }, 300);
        }, 3000);
    }

    /**
     * Muestra un mensaje de error
     */
    function mostrarError(mensaje) {
        console.error('Error:', mensaje);

        const notificacion = document.createElement('div');
        notificacion.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${mensaje}</span>
        `;
        notificacion.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: var(--font-primary);
            font-size: 14px;
            font-weight: 500;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(notificacion);

        setTimeout(() => {
            notificacion.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notificacion.parentNode) {
                    notificacion.parentNode.removeChild(notificacion);
                }
            }, 300);
        }, 5000);
    }

    // =============================================================================
    // EXPORTAR FUNCIONES AL SCOPE GLOBAL
    // =============================================================================

    // Hacer las funciones accesibles globalmente
    window.exportarSeccionPNG = exportarSeccionPNG;
    window.exportarSeccionPDF = exportarSeccionPDF;

    console.log('✅ Módulo export-sections.js cargado correctamente');

})();
