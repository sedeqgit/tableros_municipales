/**
 * =============================================================================
 * CONTROLADOR DE NAVEGACIÓN - SISTEMA SEDEQ
 * =============================================================================
 *
 * Este módulo gestiona toda la funcionalidad del sistema de navegación
 * en el dashboard estadístico de SEDEQ, basado en el diseño del portal
 * del Gobierno de Querétaro.
 *
 * FUNCIONALIDADES PRINCIPALES:
 * - Apertura y cierre del menú lateral desde el lado derecho
 * - Sistema de búsqueda expandible en el header
 * - Gestión de overlay para cerrar menú en dispositivos táctiles
 * - Animaciones y transiciones suaves
 *
 * COMPORTAMIENTOS:
 * - Sidebar se despliega desde la derecha al hacer clic en el menú hamburguesa
 * - Barra de búsqueda se expande al hacer clic en el botón de búsqueda
 * - Overlay se activa para cerrar el menú tocando fuera
 *
 * ELEMENTOS DOM REQUERIDOS:
 * - #sidebarToggle: Botón hamburguesa para abrir/cerrar menú
 * - #searchToggle: Botón para expandir/colapsar búsqueda
 * - #searchClose: Botón para cerrar barra de búsqueda
 * - .sidebar: Contenedor del menú lateral
 * - .sidebar-overlay: Capa de overlay
 * - .search-bar-expanded: Barra de búsqueda expandible
 *
 * @version 3.0
 * @requires Font Awesome para iconos
 */

// =============================================================================
// INICIALIZACIÓN DEL CONTROLADOR DE NAVEGACIÓN
// =============================================================================

/**
 * Punto de entrada principal del sistema de navegación
 */
document.addEventListener('DOMContentLoaded', function() {
    // =============================================================================
    // ELEMENTOS DOM
    // =============================================================================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const searchToggle = document.getElementById('searchToggle');
    const searchClose = document.getElementById('searchClose');
    const searchBarExpanded = document.getElementById('searchBarExpanded');

    // =============================================================================
    // FUNCIONALIDAD DEL SIDEBAR (MENÚ LATERAL)
    // =============================================================================

    if (sidebarToggle && sidebar) {
        // Abrir/cerrar sidebar al hacer clic en el botón hamburguesa
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');

            // Cambiar icono del botón hamburguesa
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Cerrar sidebar al hacer clic en el overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');

                // Restaurar icono del botón hamburguesa
                const icon = sidebarToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }

        // Cerrar sidebar al hacer clic en un enlace (solo en móviles)
        const sidebarLinks = sidebar.querySelectorAll('a:not(.has-submenu)');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');

                    // Restaurar icono del botón hamburguesa
                    const icon = sidebarToggle.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            });
        });
    }

    // =============================================================================
    // FUNCIONALIDAD DE BÚSQUEDA EXPANDIBLE
    // =============================================================================

    if (searchToggle && searchBarExpanded) {
        // Expandir barra de búsqueda
        searchToggle.addEventListener('click', function() {
            searchBarExpanded.classList.add('active');
            // Enfocar el input de búsqueda
            const searchInput = searchBarExpanded.querySelector('.search-input');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 300);
            }
        });
    }

    if (searchClose && searchBarExpanded) {
        // Cerrar barra de búsqueda
        searchClose.addEventListener('click', function() {
            searchBarExpanded.classList.remove('active');
        });
    }

    // Cerrar búsqueda y sidebar con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (searchBarExpanded && searchBarExpanded.classList.contains('active')) {
                searchBarExpanded.classList.remove('active');
            }
            if (sidebar && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');

                // Restaurar icono del botón hamburguesa
                if (sidebarToggle) {
                    const icon = sidebarToggle.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            }
        }
    });

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
