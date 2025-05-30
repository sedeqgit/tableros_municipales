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
Proporcionar una plataforma integral para el análisis y visualización de datos educativos, facilitando la toma de decisiones basada en información estadística precisa sobre escuelas, estudiantes y tendencias educativas.

---

## 🛠️ Stack Tecnológico

| Tecnología | Porcentaje | Uso Principal |
|------------|------------|---------------|
| **PHP** | 36.8% | Backend, conexión a BD, lógica de negocio |
| **JavaScript** | 31.9% | Interactividad, gráficos, visualizaciones |
| **CSS** | 31.3% | Diseño responsivo, animaciones, UX |

### 📚 Bibliotecas y Dependencias
- **Google Charts** - Visualizaciones interactivas
- **Font Awesome 6.0** - Iconografía
- **SheetJS (XLSX)** - Exportación a Excel
- **jsPDF + AutoTable** - Generación de reportes PDF
- **PostgreSQL** - Base de datos principal

---

## 🚀 Características y Funcionalidades

### 🔐 Sistema de Autenticación Simplificado
- **Modo Demo** integrado para acceso sin credenciales
- **Gestión de sesiones** con helper centralizado
- **Redirección automática** basada en estado de autenticación
- **Logout seguro** con limpieza de sesión

### 📊 Dashboard Principal (`dashboard_restructurado.php`)
- **📈 Resumen Ejecutivo** con métricas clave:
  - Total de alumnos: **119,530** estudiantes
  - Total de escuelas: **496** instituciones educativas
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

### 📊 Sistema de Exportación
- **📄 Exportación a PDF** con formato profesional
- **📈 Exportación a Excel** para análisis adicional
- **🖨️ Reportes listos para imprimir**

### 🎨 Interfaz de Usuario Avanzada
- **Diseño Responsivo** para móviles y tablets
- **Sidebar Navegable** con menú colapsible
- **Animaciones CSS** profesionales
- **Tooltips Informativos** con datos adicionales

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

## 📁 Estructura del Proyecto

```
Corregidora/
├── 📂 css/                          # Estilos y animaciones
│   ├── styles.css                   # Estilos principales
│   ├── animations_global.css        # Animaciones globales
│   └── sidebar.css                  # Estilos del menú lateral
├── 📂 js/                           # Scripts y funcionalidades
│   ├── script.js                    # Lógica principal del dashboard
│   ├── animations_global.js         # Control de animaciones
│   └── sidebar.js                   # Funcionalidad del menú
├── 📂 img/                          # Recursos gráficos
│   └── layout_set_logo.png          # Logo SEDEQ
├── 🔧 conexion.php                  # Configuración de BD y funciones
├── 🔐 session_helper.php            # Gestión de sesiones demo
├── 🚪 login.php                     # Página de autenticación
├── ⚡ process_login.php             # Procesamiento de login
├── 🏠 home.php                      # Página principal
├── 📊 dashboard_restructurado.php   # Dashboard principal
├── 🏫 escuelas_detalle.php          # Gestión de escuelas
├── 👥 estudiantes.php               # Administración estudiantil
├── 📈 historicos.php                # Reportes históricos
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

## 💡 Tips de Uso y Optimización

### 🎨 Personalización Visual

> **🎨 Tip:** El sistema incluye múltiples opciones de visualización

- **Tipos de Gráfico**: Columnas, Barras, Pastel
- **Filtros de Datos**: Solo Escuelas, Solo Alumnos, Ambos
- **Animaciones**: Habilitadas por defecto para mejor UX

### 📊 Maximizando el Dashboard

> **📈 Tip:** Utiliza los tooltips para información adicional

- Hover sobre iconos `ℹ️` para ver detalles estadísticos
- Las métricas se actualizan automáticamente
- Los gráficos son interactivos y responsivos

### 🔄 Gestión de Datos

> **⚠️ Importante:** El sistema incluye datos de fallback automático

- Conexión automática a PostgreSQL
- Fallback a datos demo si no hay BD
- Validación automática de datos

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
- **496** instituciones educativas
- **119,530** estudiantes registrados
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

> **✅ Compatible:** Optimizado para navegadores modernos

| Navegador | Versión Mínima | Estado |
|-----------|----------------|---------|
| Chrome | 80+ | ✅ Completo |
| Firefox | 75+ | ✅ Completo |
| Safari | 13+ | ✅ Completo |
| Edge | 80+ | ✅ Completo |

### 📱 Responsive Design

> **📱 Responsivo:** Diseñado para todos los dispositivos

- **Desktop**: Experiencia completa con todas las funcionalidades
- **Tablet**: Menú adaptativo y gráficos optimizados
- **Mobile**: Interfaz compacta con navegación simplificada

---

## 🤝 Información del Desarrollador

**👨‍💻 Desarrollador:** Emiliano Ledesma  
**🔗 GitHub:** [@EmilianoLedesma](https://github.com/EmilianoLedesma)  
**📅 Última Actualización:** Mayo 2025  
**🏷️ Versión:** 1.0.0 (Demo)

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
