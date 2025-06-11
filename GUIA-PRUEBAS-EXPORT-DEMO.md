# 🧪 GUÍA DE PRUEBAS - Sistema Demo ExportManager CON ANOTACIONES

## 📋 Descripción General

Este sistema demo valida el funcionamiento del **ExportManager con Anotaciones** centralizado con dos páginas diferentes que muestran distintos tipos de gráficos y configuraciones. **NUEVA VERSIÓN**: Las exportaciones PNG ahora incluyen valores como anotaciones sobre las barras del gráfico.

## 📁 Archivos Creados

### Demo Dashboard (Gráfico de Barras con Anotaciones)
- **`demo-dashboard.php`** - Página principal con gráfico de barras de estudiantes
- **`demo-dashboard.js`** - Lógica específica del dashboard (ACTUALIZADA para anotaciones)
- **`demo-dashboard.css`** - Estilos personalizados del dashboard

### Demo Ventas (Gráfico Circular con Anotaciones)  
- **`demo-ventas.php`** - Página de ventas con gráfico circular/dona
- **`demo-ventas.js`** - Lógica específica de ventas (ACTUALIZADA para anotaciones)
- **`demo-ventas.css`** - Estilos personalizados de ventas

### Archivo Central NUEVO CON ANOTACIONES
- **`js/export-manager-annotations.js`** - Sistema centralizado con anotaciones v4.0 (NUEVO)
- **`js/export-manager-simple.js`** - Sistema centralizado simplificado v3.1 (anterior)
- **`test-export-manager.html`** - Página de prueba rápida

## 🚀 Cómo Probar

### 0. Prueba Rápida del Sistema (NUEVO)

**Verificación Básica:**
```
http://localhost/Corregidora/test-export-manager.html
```
*Esta página verifica que todas las librerías estén cargadas y el ExportManager funcione básicamente.*

### 1. Acceder a las Páginas Demo

**Dashboard de Estudiantes:**
```
http://localhost/Corregidora/demo-dashboard.php
```

**Dashboard de Ventas:**
```
http://localhost/Corregidora/demo-ventas.php
```

### 2. Verificaciones Previas

Antes de probar las páginas demo, asegúrate de que:
- **XAMPP esté ejecutándose** (Apache activo)
- **Los archivos estén en las rutas correctas**
- **No hay errores en consola** (F12)

### 2. Funcionalidades a Validar

#### ✅ Demo Dashboard (Estudiantes)
- **Filtros disponibles:**
  - Filtro por año (2020-2024)
  - Filtro por nivel educativo
  - Auto-aplicación de filtros

- **Tipos de vista:**
  - Vista general: Gráfico de barras por años y niveles
  - Vista filtrada por año: Gráfico de barras por niveles

- **Exportaciones:**
  - PNG: Captura del gráfico actual
  - Excel: Datos tabulados con metadata

- **Estadísticas dinámicas:**
  - Total estudiantes, niveles, años, promedio anual

#### ✅ Demo Ventas (Categorías)
- **Filtros disponibles:**
  - Filtro por período (Q1-Q4 2024)
  - Filtro por venta mínima
  - Auto-aplicación de filtros

- **Configuraciones de gráfico:**
  - Tipo: Circular, Dona, Barras
  - Vista 3D (solo para circular)
  - Mostrar/ocultar porcentajes

- **Exportaciones:**
  - PNG: Captura del gráfico actual
  - Excel: Resumen + detalles opcionales

- **Características adicionales:**
  - Top 5 productos
  - Tabla detallada con participación
  - Estadísticas en tiempo real

## ✨ NUEVAS FUNCIONALIDADES CON ANOTACIONES (v4.0)

### 🎯 Mejoras en Exportación PNG

**ANTES:** Las exportaciones PNG incluían una tabla de valores debajo del gráfico.

**AHORA:** Las exportaciones PNG muestran los valores exactos como anotaciones directamente sobre las barras del gráfico, igual que en el sistema de estudiantes (`exports-estudiantes-v2.js`).

### 🔧 Tecnología Implementada

- **Sistema:** ExportManager con Anotaciones (v4.0)
- **Archivo:** `js/export-manager-annotations.js`
- **Inspirado en:** `js/exports-estudiantes-v2.js` línea 1206
- **Método:** Gráfico temporal con datos estructurados para anotaciones

### 📊 Tipos de Gráficos Soportados

| Tipo de Gráfico | Anotaciones | Estado |
|------------------|-------------|---------|
| Barras (Column) | ✅ Sobre barras | Implementado |
| Barras Horizontales | ✅ Sobre barras | Implementado |
| Circular (Pie) | ⚠️ Limitado | En desarrollo |
| Dona (Donut) | ⚠️ Limitado | En desarrollo |

### 🎨 Configuración de Anotaciones

```javascript
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
}
```

## 🧪 Casos de Prueba Específicos

### Caso 1: Exportación PNG
1. Abrir cualquier demo
2. Aplicar filtros diferentes
3. Hacer clic en "Exportar PNG"
4. **Resultado esperado:** Descarga automática del gráfico

### Caso 2: Exportación Excel
1. Abrir cualquier demo
2. Cambiar filtros y configuraciones
3. Hacer clic en "Exportar Excel"
4. **Resultado esperado:** Descarga de archivo .xlsx con datos estructurados

### Caso 3: Prueba de Anotaciones PNG (NUEVO)

**Objetivo:** Validar que las exportaciones PNG muestren valores sobre las barras

1. Abrir **Demo Dashboard** (gráfico de barras)
2. Seleccionar un año específico (ej: 2024)
3. Hacer clic en "Exportar PNG"
4. **Resultado esperado:**
   - ✅ Descarga automática de archivo PNG
   - ✅ Valores numéricos visibles SOBRE cada barra
   - ✅ Sin tabla de valores debajo del gráfico
   - ✅ Formato similar a `exports-estudiantes-v2.js`

**Comparación Visual:**
- **Antes:** Gráfico + tabla debajo
- **Ahora:** Gráfico con anotaciones integradas

### Caso 4: Configuraciones Específicas
**En Demo Dashboard:**
- Filtrar por año específico → Debe cambiar estructura del gráfico
- Limpiar filtros → Debe mostrar vista general

**En Demo Ventas:**
- Cambiar tipo de gráfico → Debe re-renderizar correctamente
- Activar 3D → Solo funciona con gráfico circular
- Filtro venta mínima → Debe filtrar categorías

### Caso 5: Responsividad
1. Cambiar tamaño de ventana
2. Probar en móvil/tablet
3. **Resultado esperado:** Interfaz adaptada correctamente

## 🔍 Validaciones del ExportManager

### ✅ Configuración por Página
Cada demo tiene su propia configuración:

```javascript
// Demo Dashboard
const exportConfig = {
    pageId: 'demo-dashboard',
    title: 'Dashboard Demo - Estudiantes por Nivel',
    // ... configuración específica
};

// Demo Ventas  
const exportConfig = {
    pageId: 'demo-ventas',
    title: 'Demo Ventas - Análisis por Categoría',
    // ... configuración específica
};
```

### ✅ Datos Dinámicos
- Los datos para exportación se generan dinámicamente
- Incluyen metadata (filtros, fecha, etc.)
- Se adaptan al estado actual de cada página

### ✅ Estilos Personalizados
- Cada página puede definir estilos específicos para Excel
- Colores de encabezado diferentes por página
- Formateo adaptado al tipo de datos

## 🐛 Debugging

### Funciones de Debug Disponibles
En consola del navegador:

```javascript
// Para Dashboard
debugDashboard();

// Para Ventas
debugVentas();

// Para ExportManager
ExportManager.debug();
```

### Posibles Problemas

1. **ExportManager no encontrado**
   - Verificar que `js/export-manager.js` existe
   - Comprobar ruta en HTML

2. **Error en exportación PNG**
   - Verificar que el gráfico está completamente cargado
   - Comprobar consola para errores de Google Charts

3. **Error en exportación Excel**
   - Verificar que SheetJS está cargado
   - Comprobar función `getCurrentExportData()`

## 📊 Datos Demo

### Dashboard Estudiantes
- **Períodos:** 2020-2024 (5 años)
- **Niveles:** Preescolar, Primaria, Secundaria, Bachillerato
- **Rango:** 1,200 - 4,500 estudiantes por nivel/año

### Dashboard Ventas
- **Períodos:** Q1-Q4 2024 (4 trimestres)
- **Categorías:** 6 categorías de productos
- **Rango:** $28,000 - $195,000 por categoría/trimestre

## ✅ Criterios de Éxito

El sistema estará funcionando correctamente si:

1. **✅ Ambas páginas cargan sin errores**
2. **✅ Los gráficos se renderizan correctamente**
3. **✅ Los filtros modifican los datos dinámicamente**
4. **✅ Las exportaciones PNG funcionan**
5. **✅ Las exportaciones Excel contienen datos correctos**
6. **✅ Las exportaciones PNG incluyen anotaciones sobre las barras (NUEVO)**
7. **✅ Las configuraciones específicas de cada página funcionan**
8. **✅ No hay errores en consola del navegador**

## 🆕 NUEVAS VALIDACIONES CON ANOTACIONES

### Validación Visual PNG

**Archivo generado debe tener:**
- ✅ Gráfico de barras con valores numéricos sobre cada barra
- ✅ Anotaciones legibles (fuente Arial, tamaño 11px, color #333)
- ✅ Sin líneas de conexión entre anotaciones y barras
- ✅ Información de exportación en la parte inferior
- ❌ **NO debe tener** tabla de valores separada

### Comparación con Sistema Original

**Para validar implementación correcta:**
1. Abrir `estudiantes.php` en el sistema principal
2. Exportar PNG desde estudiantes
3. Abrir `demo-dashboard.php` 
4. Exportar PNG desde demo
5. **Comparar:** Ambos deben tener estilo similar de anotaciones

## 🔄 Próximos Pasos

Una vez validado el sistema demo:

1. **Implementar en páginas reales** (estudiantes.php, etc.)
2. **Migrar configuraciones** existentes al ExportManager
3. **Reemplazar** archivos individuales de exportación
4. **Optimizar** y agregar funcionalidades adicionales

---

## 📞 Soporte

Si encuentras algún problema:
1. Revisa la consola del navegador (F12)
2. Utiliza las funciones de debug disponibles
3. Verifica que todos los archivos están en las rutas correctas
