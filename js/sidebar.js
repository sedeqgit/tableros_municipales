/**
 * =============================================================================
 * CONTROLADOR DE NAVEGACIÓN LATERAL - SISTEMA SEDEQ
 * =============================================================================
 * 
 * Este módulo gestiona toda la funcionalidad del menú de navegación lateral
 * en el dashboard estadístico de SEDEQ, proporcionando una experiencia de
 * usuario consistente y responsiva en todas las páginas del sistema.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Apertura y cierre del menú lateral en dispositivos móviles
 * - Gestión de estados colapsado/expandido del sidebar
 * - Sistema de overlay para cerrar menú en dispositivos táctiles
 * - Adaptación automática según el tamaño de la ventana
 * - Preservación del icono hamburguesa para consistencia visual
 * 
 * COMPORTAMIENTOS RESPONSIVOS:
 * - <= 992px: Modo colapsado por defecto con overlay
 * - > 992px: Sidebar visible permanentemente
 * - Transiciones suaves para cambios de estado
 * - Eventos de resize para adaptación dinámica
 * 
 * ELEMENTOS DOM REQUERIDOS:
 * - #sidebarToggle: Botón para alternar estado del menú
 * - .sidebar: Contenedor principal del menú lateral
 * - .main-content: Área principal que se ajusta según el estado del sidebar
 * - .sidebar-overlay: Capa de overlay para cerrar el menú en móviles
 * 
 * @version 2.0
 * @requires Font Awesome para iconos
 */

// =============================================================================
// INICIALIZACIÓN DEL CONTROLADOR DE SIDEBAR
// =============================================================================

/**
 * Punto de entrada principal del sistema de navegación
 * 
 * Se ejecuta cuando el DOM está completamente cargado para garantizar que
 * todos los elementos estén disponibles antes de asignar event listeners.
 */
document.addEventListener('DOMContentLoaded', function() {    
    // Toggle para la barra lateral en dispositivos móviles
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Mostrar/ocultar overlay en dispositivos móviles
            if (window.innerWidth <= 992) {
                overlay.classList.toggle('active');
            }
              // Mantenemos el icono como hamburguesa siempre
            const icon = this.querySelector('i');
            if (icon) {
                // Aseguramos que siempre sea el icono de barras (hamburguesa)
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Cerrar menú al hacer clic en el overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                overlay.classList.remove('active');
                  // Aseguramos que el icono sea siempre hamburguesa
                const icon = sidebarToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
        
        // Ajustar menú según ancho de la ventana
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 992) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                overlay.classList.remove('active');
            }
        });
        
        // Inicialmente colapsar en dispositivos móviles
        if (window.innerWidth <= 992) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    }

    // =============================================================================
    // FUNCIONALIDAD DE SUBMENÚS
    // =============================================================================

    // Manejar submenús
    const submenuLinks = document.querySelectorAll(".has-submenu");

    submenuLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        // Solo prevenir navegación si es click en el propio enlace, no en subenlaces
        if (e.target === this || e.target.closest(".has-submenu") === this) {
          e.preventDefault();

          const submenu = this.parentNode.querySelector(".submenu");
          const arrow = this.querySelector(".submenu-arrow");

          if (submenu) {
            // Toggle del submenú
            submenu.classList.toggle("active");
            this.classList.toggle("expanded");

            // Rotación de la flecha
            if (this.classList.contains("expanded")) {
              arrow.style.transform = "translateY(-50%) rotate(180deg)";
            } else {
              arrow.style.transform = "translateY(-50%) rotate(0deg)";
            }
          }
        }
      });
    });

    // Scroll suave para enlaces del submenú
    const submenuLinksAll = document.querySelectorAll(".submenu-link");

    submenuLinksAll.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const targetId = this.getAttribute("href").substring(1);
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
          // Remover clase active de todos los enlaces del submenú
          submenuLinksAll.forEach((sLink) => sLink.classList.remove("active"));

          // Agregar clase active al enlace clickeado
          this.classList.add("active");

          // Scroll suave hacia la sección
          targetElement.scrollIntoView({
            behavior: "smooth",
            block: "start",
            inline: "nearest",
          });

          // En dispositivos móviles, cerrar el sidebar después de navegar
          if (window.innerWidth <= 992) {
            sidebar.classList.add("collapsed");
            mainContent.classList.add("expanded");
            overlay.classList.remove("active");
          }
        }
      });
    });

    // Detectar qué sección está visible para activar el enlace correspondiente
    // Soporta las secciones de: resumen.php, escuelas_detalle.php, alumnos.php y docentes.php
    const sections = document.querySelectorAll(
      '[id^="resumen-"], [id^="subcontrol-"], [id^="directorio-"], [id^="conclusiones"], [id^="distribucion-"], [id^="tabla-"], [id^="desglose-"], [id^="publico-"], [id^="totales-"], [id^="analisis-"]'
    );

    function updateActiveSubmenuLink() {
      let activeSection = null;
      let closestDistance = Infinity;
      
      // Usar un punto de referencia en la parte superior de la ventana
      const triggerPoint = window.innerHeight * 0.3; // 30% desde arriba de la ventana

      sections.forEach((section) => {
        const rect = section.getBoundingClientRect();
        const sectionTop = rect.top;
        const sectionBottom = rect.bottom;
        
        // La sección está visible si cualquier parte de ella está en el viewport
        if (sectionBottom > 0 && sectionTop < window.innerHeight) {
          // Calcular la distancia desde el punto de referencia
          const distance = Math.abs(sectionTop - triggerPoint);
          
          // Si esta sección está más cerca del punto de referencia, es la activa
          if (distance < closestDistance) {
            closestDistance = distance;
            activeSection = section;
          }
        }
      });

      // Actualizar enlaces del submenú
      if (activeSection) {
        submenuLinksAll.forEach((link) => link.classList.remove("active"));

        const activeLink = document.querySelector(`a[href="#${activeSection.id}"]`);
        if (activeLink && activeLink.classList.contains('submenu-link')) {
          activeLink.classList.add("active");
        }
      }
    }

    // Detectar scroll para actualizar enlace activo
    window.addEventListener("scroll", updateActiveSubmenuLink);

    // Inicializar al cargar la página
    updateActiveSubmenuLink();
});
