# 📋 Guía de Implementación - Submenú Dinámico del Sidebar

## 🎯 Descripción General

Esta guía explica cómo implementar un sistema de submenú dinámico en el sidebar del dashboard SEDEQ, que permite navegar directamente a secciones específicas de las páginas sin necesidad de hacer scroll manual.

## ✨ Características Implementadas

- **Submenú desplegable** con navegación directa a secciones
- **Scroll suave** automático hacia las secciones objetivo
- **Resaltado automático** del enlace activo según la sección visible
- **Diseño elegante** sin fondos oscuros, integrado al estilo del sidebar
- **Responsive** - Se adapta correctamente en dispositivos móviles
- **Animaciones suaves** con efectos hover y transiciones

## 🏗️ Estructura de Archivos Modificados

```
Corregidora_Static/
├── css/
│   └── sidebar.css          # Estilos del submenú agregados
├── js/
│   └── sidebar.js           # Funcionalidad del submenú
└── escuelas_detalle.php     # Página con implementación ejemplo
```

## 🔧 Implementación Paso a Paso

### 1. Estructura HTML del Submenú

Reemplaza el enlace simple de "Escuelas" por esta estructura:

```html
<div class="sidebar-link-with-submenu">
  <a href="escuelas_detalle.php" class="sidebar-link active has-submenu">
    <i class="fas fa-school"></i>
    <span>Escuelas</span>
    <i class="fas fa-chevron-down submenu-arrow"></i>
  </a>
  <div class="submenu active">
    <a href="#resumen-escuelas" class="submenu-link">
      <i class="fas fa-chart-pie"></i>
      <span>Resumen General</span>
    </a>
    <a href="#subcontrol-educativo" class="submenu-link">
      <i class="fas fa-building"></i>
      <span>Subcontrol Educativo</span>
    </a>
    <a href="#directorio-publicas" class="submenu-link">
      <i class="fas fa-landmark"></i>
      <span>Escuelas Públicas</span>
    </a>
    <a href="#directorio-privadas" class="submenu-link">
      <i class="fas fa-building"></i>
      <span>Escuelas Privadas</span>
    </a>
    <a href="#conclusiones" class="submenu-link">
      <i class="fas fa-clipboard-check"></i>
      <span>Conclusiones</span>
    </a>
  </div>
</div>
```

### 2. IDs en las Secciones de Contenido

Agrega IDs únicos a cada sección que quieras que sea accesible desde el submenú:

```html
<!-- Panel de resumen de escuelas -->
<div id="resumen-escuelas" class="panel animate-up">
  <!-- contenido -->
</div>

<!-- Panel de distribución por subcontrol educativo -->
<div id="subcontrol-educativo" class="panel animate-up delay-1">
  <!-- contenido -->
</div>

<!-- Panel de Directorio de Escuelas Públicas -->
<div id="directorio-publicas" class="matricula-panel animate-fade delay-4">
  <!-- contenido -->
</div>

<!-- Panel de Directorio de Escuelas Privadas -->
<div id="directorio-privadas" class="matricula-panel animate-fade delay-5">
  <!-- contenido -->
</div>

<!-- Panel de conclusiones -->
<div id="conclusiones" class="panel animate-up delay-4">
  <!-- contenido -->
</div>
```

### 3. Estilos CSS del Submenú

Agrega estos estilos al final de `css/sidebar.css`:

```css
/* === ESTILOS PARA SUBMENÚS === */

/* Contenedor de enlace con submenú */
.sidebar-link-with-submenu {
  position: relative;
}

/* Enlace principal con indicador de submenú */
.sidebar-link.has-submenu {
  position: relative;
  padding-right: 45px;
}

.sidebar-link .submenu-arrow {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.8rem;
  transition: transform var(--transition-speed);
}

.sidebar-link.has-submenu.expanded .submenu-arrow {
  transform: translateY(-50%) rotate(180deg);
}

/* Contenedor del submenú */
.submenu {
  background-color: transparent;
  border-left: 2px solid rgba(255, 255, 255, 0.2);
  margin-left: 15px;
  max-height: 0;
  overflow: hidden;
  transition: max-height var(--transition-speed) ease-out;
}

.submenu.active {
  max-height: 300px;
  transition: max-height var(--transition-speed) ease-in;
}

/* Enlaces del submenú */
.submenu-link {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  padding: 8px 15px 8px 25px;
  display: flex;
  align-items: center;
  font-size: 0.9rem;
  transition: all var(--transition-speed);
  border-radius: 0 15px 15px 0;
  margin-right: 15px;
  position: relative;
}

.submenu-link i {
  margin-right: 8px;
  width: 14px;
  text-align: center;
  font-size: 0.85rem;
  opacity: 0.8;
}

.submenu-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: var(--white);
  transform: translateX(5px);
  border-left: 3px solid var(--accent-gold);
}

.submenu-link:hover i {
  opacity: 1;
  color: var(--accent-gold);
}

.submenu-link.active {
  background-color: rgba(255, 255, 255, 0.15);
  color: var(--white);
  font-weight: 500;
  border-left: 3px solid var(--accent-gold);
}

.submenu-link.active i {
  color: var(--accent-gold);
  opacity: 1;
}

/* Responsive para submenús */
@media (max-width: 992px) {
  .sidebar.collapsed .submenu {
    display: none;
  }

  .sidebar.collapsed .sidebar-link .submenu-arrow {
    display: none;
  }
}
```

### 4. Funcionalidad JavaScript

Agrega esta funcionalidad al final de `js/sidebar.js`, antes del cierre del `DOMContentLoaded`:

```javascript
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
const sections = document.querySelectorAll(
  '[id^="resumen-"], [id^="subcontrol-"], [id^="directorio-"], [id^="conclusiones"]'
);

function updateActiveSubmenuLink() {
  let activeSection = null;
  const scrollPosition = window.scrollY + 100; // Offset para mejor detección

  sections.forEach((section) => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.offsetHeight;

    if (
      scrollPosition >= sectionTop &&
      scrollPosition < sectionTop + sectionHeight
    ) {
      activeSection = section;
    }
  });

  // Actualizar enlaces del submenú
  if (activeSection) {
    submenuLinksAll.forEach((link) => link.classList.remove("active"));

    const activeLink = document.querySelector(`a[href="#${activeSection.id}"]`);
    if (activeLink) {
      activeLink.classList.add("active");
    }
  }
}

// Detectar scroll para actualizar enlace activo
window.addEventListener("scroll", updateActiveSubmenuLink);

// Inicializar al cargar la página
updateActiveSubmenuLink();
```

## 🔄 Cómo Replicar en Otras Páginas

### Para páginas que NO son la página de escuelas:

1. **En el HTML del sidebar**, usa esta estructura para enlazar a la página de escuelas:

```html
<div class="sidebar-link-with-submenu">
  <a href="escuelas_detalle.php" class="sidebar-link has-submenu">
    <i class="fas fa-school"></i>
    <span>Escuelas</span>
    <i class="fas fa-chevron-down submenu-arrow"></i>
  </a>
  <div class="submenu">
    <a href="escuelas_detalle.php#resumen-escuelas" class="submenu-link">
      <i class="fas fa-chart-pie"></i>
      <span>Resumen General</span>
    </a>
    <!-- resto de enlaces con la URL completa -->
  </div>
</div>
```

### Para crear submenús en otras páginas (ej: Estudiantes):

1. **Identifica las secciones principales** de la página
2. **Agrega IDs únicos** a cada sección
3. **Modifica el selector** en JavaScript para detectar las secciones correctas:

```javascript
// Cambiar esto:
const sections = document.querySelectorAll(
  '[id^="resumen-"], [id^="subcontrol-"], [id^="directorio-"], [id^="conclusiones"]'
);

// Por esto (ejemplo para página de estudiantes):
const sections = document.querySelectorAll(
  '[id^="matricula-"], [id^="desercion-"], [id^="rendimiento-"]'
);
```

## 📱 Comportamiento Responsive

- **Desktop (> 992px)**: Submenú siempre visible cuando está activo
- **Tablet/Mobile (≤ 992px)**: Submenú se oculta cuando sidebar está colapsado
- **Navegación móvil**: Sidebar se cierra automáticamente después de navegar

## 🎨 Características de Diseño

- **Sin fondos oscuros**: Submenú transparente integrado al sidebar
- **Línea sutil**: Borde izquierdo discreto para separar visualmente
- **Animaciones suaves**: Transiciones en hover y navegación
- **Iconos dorados**: Resaltado con color institucional
- **Bordes redondeados**: Estilo moderno y elegante

## 🚀 Funcionalidades Avanzadas

1. **Auto-detección de sección activa**: El enlace se resalta automáticamente según la sección visible
2. **Scroll inteligente**: Navegación suave con offset para mejor visualización
3. **Estado persistente**: El submenú mantiene su estado expandido en la página activa
4. **Optimización móvil**: Comportamiento adaptativo según el dispositivo

## 🔍 Personalización

### Para cambiar los colores:

```css
/* Color del borde del submenú */
.submenu {
  border-left: 2px solid rgba(255, 255, 255, 0.2); /* Cambiar aquí */
}

/* Color de resaltado */
.submenu-link:hover,
.submenu-link.active {
  border-left-color: var(--accent-gold); /* Cambiar aquí */
}
```

### Para ajustar las animaciones:

```css
/* Velocidad de expansión */
.submenu {
  transition: max-height 0.3s ease-out; /* Cambiar duración */
}

/* Efecto hover */
.submenu-link:hover {
  transform: translateX(5px); /* Cambiar distancia */
}
```

## 📋 Checklist de Implementación

- [ ] Estructura HTML del submenú agregada
- [ ] IDs únicos agregados a las secciones
- [ ] Estilos CSS del submenú implementados
- [ ] JavaScript funcional agregado
- [ ] Pruebas en desktop realizadas
- [ ] Pruebas en dispositivos móviles realizadas
- [ ] Navegación entre secciones verificada
- [ ] Auto-detección de sección activa funcionando

---

**📝 Nota**: Esta implementación está optimizada para el sistema SEDEQ y utiliza las variables CSS y estructura existente del proyecto.

**🔗 Autor**: Implementado para el Dashboard Estadístico SEDEQ  
**📅 Fecha**: 2025  
**🏷️ Versión**: 1.0
