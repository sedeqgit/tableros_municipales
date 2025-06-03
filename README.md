# 📊 Dashboard Estadístico Educativo Corregidora - SEDEQ

> **🚨 PROYECTO DEMO** - Dashboard interactivo para visualización de estadísticas educativas del municipio de Corregidora, Querétaro (Ciclo Escolar 2023-2024)

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white)

</div>

---

## 📋 Descripción General

**Corregidora Dashboard** es un sistema web interactivo desarrollado para la **Secretaría de Educación del Estado de Querétaro (SEDEQ)** que centraliza y visualiza las estadísticas educativas del municipio de Corregidora durante el ciclo escolar 2023-2024.

> **📌 Nota:** Este es un proyecto **DEMO** que muestra el potencial de visualización de datos educativos. Los datos utilizados pueden ser representativos o de ejemplo para fines demostrativos.

### 🎯 Objetivo Principal
Proporcionar una plataforma integral para el análisis y visualización de datos educativos, facilitando la toma de decisiones basada en información estadística precisa sobre escuelas, estudiantes y tendencias educativas. El sistema cuenta con herramientas optimizadas para la captura, exportación y visualización de información estadística en tiempo real.

---

## 🛠️ Stack Tecnológico

| Tecnología | Porcentaje | Uso Principal |
|------------|------------|---------------|
| **PHP** | 36.8% | Backend, conexión a BD, lógica de negocio |
| **JavaScript** | 31.9% | Interactividad, gráficos, visualizaciones |
| **CSS** | 31.3% | Diseño responsivo, animaciones, UX |

### 📚 Bibliotecas y Dependencias
- **Google Charts API v49** - Visualizaciones interactivas y exportación nativa
- **Font Awesome 6.4.0** - Iconografía y elementos visuales
- **SheetJS (XLSX) v0.18.5** - Exportación avanzada a Excel
- **jsPDF v2.5.1 + AutoTable v3.5.25** - Generación de reportes PDF profesionales
- **Html2Canvas v1.4.1** - Captura de gráficos como fallback
- **PostgreSQL 14.8** - Sistema de gestión de base de datos principal

---

## 🚀 Características y Funcionalidades

### 🔐 Sistema de Autenticación Simplificado
- **Modo Demo** integrado para acceso sin credenciales
- **Gestión de sesiones** con helper centralizado
- **Redirección automática** basada en estado de autenticación
- **Logout seguro** con limpieza de sesión

### 📊 Dashboard Principal (`dashboard_restructurado.php`)
- **📈 Resumen Ejecutivo** con métricas clave:
  - Total de alumnos: **48368** estudiantes
  - Total de escuelas: **315** instituciones educativas
  - Porcentaje de matrícula estatal: **7.98%**
- **📉 Análisis de Tendencias** automatizado
- **🎨 Visualizaciones Interactivas**:
  - Gráficos de columnas, barras y pastel
  - Filtros dinámicos por tipo de datos
  - Animaciones y transiciones fluidas

### 🏫 Gestión Educativa Detallada
- **Escuelas por Tipo Educativo**:
  - Inicial (Escolarizado/No Escolarizado)
  - Especial (CAM)
  - Preescolar, Primaria, Secundaria
  - Media Superior y Superior
- **Análisis por Sostenimiento** (Público/Privado)
- **Históricos de Matrícula** por ciclos escolares

### 📊 Sistema de Exportación Mejorado
- **📄 Exportación a PDF** con formato profesional y captura nativa de gráficos
- **📈 Exportación a Excel** con formato detallado y datos estructurados
- **🖨️ Reportes** listos para imprimir con calidad mejorada
- **🔄 Sistema dual** con método nativo de Google Charts y fallback a Html2Canvas
- **⚡ Optimización** de captura para evitar problemas de renderizado en SVG

### 🎨 Interfaz de Usuario Avanzada y Optimizada
- **Diseño Responsivo** para todos los dispositivos: Desktop, tablet y móvil
- **Sidebar Navegable** con menú colapsible y animaciones optimizadas
- **Animaciones CSS** para transiciones fluidas entre secciones
- **Tooltips Informativos** con datos adicionales y estadísticas
- **Mejoras Visuales** para presentación de gráficos y datos
- **Optimización de carga** para mejor rendimiento en todos los dispositivos
- **Modo nocturno** con detección automática de preferencias del sistema y ajuste inteligente de contraste para los gráficos y tablas

---

## 🗄️ Arquitectura de Base de Datos

### 📊 Esquema Principal: `nonce_pano_23`

> **💡 Tip:** El sistema utiliza PostgreSQL como motor de base de datos principal

**Tablas Principales:**
- `estadistica_corregidora` - Datos principales de escuelas y alumnos
- `matricula_escuelas_publicas` - Históricos de matrícula por año
- Soporte para datos de **sostenimiento** (público/privado)

### 🔄 Sistema de Fallback
```php
// Datos de respaldo integrados en caso de falta de conexión
$datosEducativos = array(
    array('Tipo Educativo', 'Escuelas', 'Alumnos'),
    array('Primaria', 180, 45000),
    // ... más datos de ejemplo
);
```

---

## 📁 Estructura del Proyecto Actualizada

```bash
Corregidora/
├── 📂 css/                          # Estilos y animaciones
│   ├── styles.css                   # Estilos principales
│   ├── global.css                   # Estilos globales compartidos
│   ├── escuelas_detalle.css         # Estilos para módulo de escuelas
│   ├── estudiantes.css              # Estilos para módulo de estudiantes
│   ├── home.css                     # Estilos para página principal
│   ├── login.css                    # Estilos para autenticación
│   ├── settings.css                 # Estilos de configuraciones
│   └── sidebar.css                  # Estilos del menú lateral
├── 📂 js/                           # Scripts y funcionalidades
│   ├── script.js                    # Lógica principal del dashboard
│   ├── animations_global.js         # Control de animaciones
│   ├── chart-capture-improvements.js # Mejoras de captura de gráficos
│   ├── escuelas_detalle.js          # Funcionalidad de escuelas
│   ├── escuelas_diagram.js          # Diagramas de escuelas
│   ├── escuelas_publicas_privadas.js # Análisis por sostenimiento
│   ├── estudiantes.js               # Gestión de estudiantes
│   ├── export-graficos-mejorado.js  # Sistema de exportación optimizado
│   ├── historicos.js                # Análisis de datos históricos
│   ├── home.js                      # Lógica de página principal
│   ├── login.js                     # Funcionalidad de autenticación
│   ├── settings.js                  # Gestión de configuraciones
│   └── sidebar.js                   # Funcionalidad del menú
├── 📂 img/                          # Recursos gráficos
│   ├── layout_set_logo.png          # Logo SEDEQ
│   └── user-avatar.jpg              # Avatar de usuario
├── 🔧 conexion.php                  # Configuración de BD y funciones
├── 🔐 session_helper.php            # Gestión de sesiones demo
├── 🚪 login.php                     # Página de autenticación
├── 👋 logout.php                    # Cierre de sesión seguro
├── ⚡ process_login.php             # Procesamiento de login
├── 🏠 home.php                      # Página principal
├── 📊 dashboard_restructurado.php   # Dashboard principal
├── 🏫 escuelas_detalle.php          # Gestión de escuelas
├── 👥 estudiantes.php               # Administración estudiantil
└── ⚙️ settings.php                  # Configuraciones del sistema
```

---

## 🔧 Configuración y Uso

### 🚀 Acceso Rápido al Demo

> **⚡ Tip:** Para acceder al demo sin configuración adicional, usa el parámetro `?demo=true`

```
http://tu-servidor/dashboard_restructurado.php?demo=true
```

### 🐘 Configuración de PostgreSQL

> **📝 Nota:** El sistema está configurado para PostgreSQL por defecto

```php
// Configuración en conexion.php
$connection = pg_connect("host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres");
```

### 🎯 Flujo de Navegación

1. **🔑 Autenticación** → `login.php` o modo demo
2. **🏠 Inicio** → `home.php` (página de bienvenida)
3. **📊 Dashboard** → `dashboard_restructurado.php` (visualizaciones)
4. **🏫 Gestión** → `escuelas_detalle.php` / `estudiantes.php`
5. **📈 Reportes** → `historicos.php`

---

## 💡 Tips de Uso y Optimización Avanzada

### 🎨 Personalización Visual

> **🎨 Tip:** El sistema incluye múltiples opciones de visualización mejoradas

- **Tipos de Gráfico**: Columnas, Barras, Pastel, Líneas y Áreas
- **Filtros de Datos**: Filtrado múltiple por nivel, sostenimiento y periodo
- **Animaciones**: Sistema optimizado con transiciones fluidas
- **Temas de color**: Paletas predefinidas para mejor visualización de datos

### 📊 Maximizando el Dashboard

> **📈 Tip:** Sistema de visualización avanzada con múltiples opciones

- Hover sobre iconos `ℹ️` para ver detalles estadísticos completos
- Las métricas se actualizan automáticamente en tiempo real
- Los gráficos son totalmente interactivos con animación optimizada
- Sistema de exportación mejorado con captura nativa de gráficos
- Visualizaciones sincronizadas para análisis comparativo

### 🔄 Gestión de Datos y Performance

> **⚠️ Importante:** Sistema robusto con múltiples capas de seguridad

- Conexión optimizada a PostgreSQL con pooling y cache
- Sistema de fallback inteligente con datos representativos
- Validación y sanitización automática de entrada de datos
- Compresión de datos para mejor rendimiento
- Sistema de logs para monitoreo de actividad

---

## ⚠️ Notas Importantes del Demo

### 🎭 Características del Modo Demo

> **🚨 Advertencia:** Este es un proyecto demostrativo con datos representativos

- **Datos Educativos**: Basados en estadísticas reales de Corregidora 2023-2024
- **Funcionalidad Completa**: Todas las características están operativas
- **Sin Persistencia**: Los cambios no se guardan permanentemente en modo demo

### 🏛️ Contexto Oficial

> **🏛️ Información:** Desarrollado para SEDEQ - Gobierno del Estado de Querétaro

- **Municipio**: Corregidora, Querétaro
- **Ciclo Escolar**: 2023-2024
- **Organismo**: Secretaría de Educación del Estado de Querétaro
- **Niveles Educativos**: Desde Inicial hasta Superior

### 📊 Datos Estadísticos Reales

> **📊 Datos:** Basado en información oficial del sistema educativo

- **7.98%** de la matrícula estatal total
- **315** instituciones educativas
- **48684** estudiantes registrados
- **8** niveles educativos diferentes

---

## 🔒 Consideraciones de Seguridad

### 🛡️ Implementación de Seguridad

> **🔐 Seguridad:** El sistema incluye medidas básicas de protección

- Validación de sesiones activas
- Sanitización de datos de entrada
- Protección contra acceso no autorizado
- Timeouts de sesión configurables

---

## 📱 Compatibilidad y Rendimiento

### 🌐 Compatibilidad de Navegadores

> **✅ Compatible:** Optimizado para navegadores modernos con soporte completo de características

| Navegador | Versión Mínima | Estado |
|-----------|----------------|---------|
| Chrome | 80+ | ✅ Completo |
| Firefox | 75+ | ✅ Completo |
| Safari | 13+ | ✅ Completo |
| Edge | 80+ | ✅ Completo |

### 📱 Responsive Design

> **📱 Responsivo:** Diseñado para todos los dispositivos con experiencia optimizada

- **Desktop** (1200px+): Experiencia completa con todas las funcionalidades y visualizaciones expandidas
- **Tablet** (768px - 1199px): Menú adaptativo, gráficos redimensionados y navegación optimizada
- **Mobile** (320px - 767px): Interfaz compacta, menú colapsable, gráficos adaptados y navegación simplificada
- **Soporte para orientación**: Detección y optimización automática para vistas vertical/horizontal
- **Optimización de interacción táctil**: Controles más grandes y espaciados para uso con pantallas táctiles

---

## 🤝 Información del Desarrollador

**👨‍💻 Desarrollador:** Emiliano Ledesma  
**🔗 GitHub:** [@EmilianoLedesma](https://github.com/EmilianoLedesma)  
**📅 Última Actualización:** Junio 2025  
**🏷️ Versión:** 1.2.0 (Demo)

---

## 📄 Licencia y Derechos

> **©️ Derechos:** Proyecto privado con fines demostrativos

**© 2025 Secretaría de Educación del Estado de Querétaro**  
Todos los derechos reservados. Este proyecto es de carácter demostrativo.

---

<div align="center">

**🎓 Dashboard Educativo Corregidora - Transformando datos en conocimiento**

![Estadísticas](https://img.shields.io/badge/Estudiantes-119%2C530-blue?style=flat-square)
![Instituciones](https://img.shields.io/badge/Escuelas-496-green?style=flat-square)
![Cobertura](https://img.shields.io/badge/Cobertura%20Estatal-7.98%25-orange?style=flat-square)

</div>

---

## 📝 Registro de Cambios Recientes

### Versión 1.2.0 (Junio 2025)

- **✨ Nuevas Características**
  - Sistema de exportación dual con método nativo y fallback
  - Modo oscuro con detección automática de preferencias del sistema
  - Optimización de captura de gráficos para exportación de alta calidad
  - Soporte mejorado para dispositivos móviles con diferentes orientaciones

- **🔧 Mejoras Técnicas**
  - Actualización de Google Charts API a v49
  - Actualización de Font Awesome a versión 6.4.0
  - Implementación de jsPDF v2.5.1 + AutoTable v3.5.25
  - Integración de Html2Canvas v1.4.1 como sistema de respaldo
  - Actualización de SheetJS (XLSX) a v0.18.5
  - Compatibilidad con PostgreSQL 14.8

- **🐛 Correcciones**
  - Solución a problemas de renderizado SVG en exportaciones
  - Mejoras en la visualización de gráficos en dispositivos móviles
  - Corrección de problemas de contraste en modo oscuro
  - Optimización de velocidad de carga en conexiones lentas

---
