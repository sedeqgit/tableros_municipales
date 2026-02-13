# 🔍 Análisis Exhaustivo del Codebase — Tableros Municipales SEDEQ

**Fecha del análisis:** 13 de febrero de 2026  
**Versión del sistema:** Dashboard Estadístico SEDEQ v1.2.1  
**Archivos analizados:** 46 archivos (10 PHP, 22 JS, 10 CSS, 4 includes PHP, JSON, imágenes)

---

## 📁 Índice

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Archivos No Referenciados (Código Muerto)](#2-archivos-no-referenciados-código-muerto)
3. [Funciones Duplicadas entre Archivos](#3-funciones-duplicadas-entre-archivos)
4. [Código Muerto dentro de Archivos](#4-código-muerto-dentro-de-archivos)
5. [Bugs Críticos Encontrados](#5-bugs-críticos-encontrados)
6. [Problemas de Seguridad](#6-problemas-de-seguridad)
7. [Violaciones de Convención snake_case](#7-violaciones-de-convención-snake_case)
8. [Violaciones de Principios SOLID](#8-violaciones-de-principios-solid)
9. [Problemas de Clean Code](#9-problemas-de-clean-code)
10. [Inconsistencias en Includes](#10-inconsistencias-en-includes)
11. [Recomendaciones por Archivo](#11-recomendaciones-por-archivo)
12. [Plan de Limpieza](#12-plan-de-limpieza)

---

## 1. Resumen Ejecutivo

| Categoría | Hallazgos |
|-----------|-----------|
| 🔴 Archivos JS no referenciados | **8 de 22** (36%) — código muerto |
| 🔴 Archivos CSS no referenciados | **1 de 10** (estudiantes.css — 648 líneas) |
| 🔴 Imágenes no referenciadas | **1** (user-avatar.jpg) |
| 🔴 Funciones PHP duplicadas | **3 funciones** copiadas en **5-6 archivos** |
| 🔴 Bugs críticos | **4** (case duplicado, función inexistente, credenciales, submit doble) |
| 🟡 Funciones JS duplicadas | **7+ patrones** repetidos entre archivos |
| 🟡 Problemas de seguridad | **5** (credenciales, debug en prod, bypass login, SQL injection) |
| 🟡 Violaciones snake_case | **32+ funciones PHP**, **50+ funciones JS** |
| 🟡 Violaciones SOLID | **6** principales |
| 🟡 Código comentado/muerto | **10+ bloques** significativos |
| 🟢 Includes inconsistentes | **3** páginas con includes faltantes |

---

## 2. Archivos No Referenciados (Código Muerto)

### 2.1 Archivos JS sin referencia desde ningún PHP (8 archivos)

| Archivo | Tamaño | ¿Eliminar? | Justificación |
|---------|--------|------------|---------------|
| `js/demo-dashboard.js` | ~498 líneas | ✅ **Sí** | Archivo de demostración, no se usa en producción |
| `js/demo-ventas.js` | ~613 líneas | ✅ **Sí** | Archivo de demostración de ventas, no educativo |
| `js/estudiantes.js` | ~708 líneas | ✅ **Sí** | Reemplazado por `alumnos.js`, funcionalidad duplicada |
| `js/exports-estudiantes-v2.js` | ~1290 líneas | ✅ **Sí** | Exportaciones para `estudiantes.js` que ya no se usa |
| `js/export-sections.js` | ~557 líneas | ✅ **Sí** | No referenciado, funcionalidad duplicada en otros archivos |
| `js/export-manager-annotations.js` | ~567 líneas | ✅ **Sí** | Solo usado por los demos que tampoco se usan |
| `js/historicos.js` | ~950 líneas | ⚠️ **Evaluar** | Puede ser funcionalidad futura; mover a carpeta `deprecated/` |
| `js/login.js` | ~191 líneas | ⚠️ **Evaluar** | No hay `login.php` visible, pero tiene credenciales hardcodeadas que deben eliminarse |

**Total de código muerto en JS: ~5,374 líneas**

### 2.2 Archivo CSS no referenciado

| Archivo | Tamaño | ¿Eliminar? | Justificación |
|---------|--------|------------|---------------|
| `css/estudiantes.css` | 648 líneas | ✅ **Sí** | No existe `estudiantes.php`; reemplazado por `alumnos.css` |

### 2.3 Imagen no referenciada

| Archivo | ¿Eliminar? | Justificación |
|---------|------------|---------------|
| `img/user-avatar.jpg` | ✅ **Sí** | No referenciada en ningún archivo PHP, CSS o JS |

---

## 3. Funciones Duplicadas entre Archivos

### 3.1 PHP — `formatearNombreMunicipio()` (5 copias idénticas)

| Archivo | Línea |
|---------|-------|
| `resumen.php` | 213 |
| `alumnos.php` | 57 |
| `docentes.php` | 55 |
| `escuelas_detalle.php` | 68 |
| `home.php` | 101 |
| `comparacion_municipios.php` | 69 |
| `mapas.php` | 120 |

**Acción:** Extraer a `includes/helpers.php` — **Prioridad ALTA**

### 3.2 PHP — `formatPercent()` (3 copias con diferencias)

| Archivo | Línea | Separador miles |
|---------|-------|-----------------|
| `resumen.php` | 227 | `''` (ninguno) |
| `docentes.php` | 69 | `''` (ninguno) |
| `alumnos.php` | 68 | `','` (coma) ⚠️ |

**Acción:** Unificar en `includes/helpers.php` con separador consistente — **Prioridad ALTA**

### 3.3 PHP — `obtenerOrdenSubnivel()` (2 copias idénticas, ~60 líneas)

| Archivo | Línea |
|---------|-------|
| `alumnos.php` | 734 |
| `docentes.php` | 516 |

**Acción:** Extraer a `includes/helpers.php` — **Prioridad MEDIA**

### 3.4 JS — Funciones `debounce()` (3 copias)

| Archivo |
|---------|
| `js/docentes.js` |
| `js/historicos.js` |
| `js/directorio_estatal.js` |

### 3.5 JS — Sistema de notificaciones/toasts (7 implementaciones diferentes)

| Archivo | Implementación |
|---------|---------------|
| `js/export-utils.js` | `ExportNotifications` (la más completa) |
| `js/export-sections.js` | `mostrarMensajeExito()` / `mostrarError()` |
| `js/export-manager-annotations.js` | `showMessage()` |
| `js/estudiantes.js` | `mostrarMensajeExito()` / `mostrarMensajeError()` |
| `js/exports-estudiantes-v2.js` | `mostrarMensajeExito()` / `mostrarMensajeError()` |
| `js/historicos.js` | `showExportSuccess()` / `showExportError()` |
| `js/settings.js` | `NotificationSystem` (clase) |

**Acción:** Una vez eliminados los archivos muertos, quedan solo 3. Unificar — **Prioridad BAJA**

### 3.6 JS — `directorio_escuelas.js` ↔ `directorio_estatal.js` (~80% duplicado)

Funciones casi idénticas copiadas entre ambos archivos:
- `initOriginalTexts()`, `updateSchoolCount()`, `initFilters()`, `initSearch()`
- `filterByLevel()`, `searchSchools()`, `highlightSearchTerm()`, `showNoResultsMessage()`
- `updateStats()`, `sortTableByLevelAndStudents()`, `exportarDirectorio()`

**Acción:** Extraer base común a `js/directorio_base.js` — **Prioridad MEDIA**

### 3.7 JS — Patrón de filtro sostenimiento duplicado

| Archivo | Funciones |
|---------|-----------|
| `js/escuelas_publicas_privadas.js` | `aplicarFiltro()`, `resetearFiltros()`, `buscarDatosSostenimiento()` |
| `js/docentes.js` | `aplicarFiltroDocentes()`, `resetearFiltrosDocentes()`, `buscarDatosDocentesSostenimiento()` |

### 3.8 PHP — Duplicación masiva en `conexion.php`

| Patrón | Ocurrencias | Líneas |
|--------|-------------|--------|
| Array vacío `$datos_unidades` (24 claves) | 3 copias | ~1513, ~1633, ~1674 |
| `acum_unidades_superior()` vs `acum_unidades()` | Casi idénticas | 1383 vs 1486 |
| `obtenerDocentesPorNivelYSubnivel()` vs `obtenerAlumnosPorNivelYSubnivel()` | Misma estructura SQL | 2508 vs 2967 |
| `obtenerResumenMunicipioCompleto()` vs `obtenerResumenEstadoCompleto()` | Mismo patrón | 2400 vs 3608 |
| Mapeo de municipios (número↔nombre) | 3 definiciones | `nombre_municipio()`, `nombre_a_numero_municipio()`, `obtenerMunicipios()` |

---

## 4. Código Muerto dentro de Archivos

### 4.1 `conexion.php`

| Línea | Tipo | Descripción | ¿Eliminar? |
|-------|------|-------------|------------|
| 898-908 | Case duplicado | `case 'especial_tot'` con valores de prueba `999` que **sobreescribe** el case real de línea 617 | ✅ **Sí — BUG CRÍTICO** |
| 3567-3599 | Función rota | `arreglos_datos_segura()` llama a `subnivel_seguro()` que **no existe** | ✅ **Sí** |
| 3905-3912 | Comentario inapropiado | Bloque con letras de canción y texto no profesional | ✅ **Sí** |
| 91-94 | Wrapper inútil | `Conectarse()` solo llama a `ConectarsePrueba()` | ⚠️ Refactorizar |

### 4.2 `docentes.php`

| Línea | Tipo | Descripción | ¿Eliminar? |
|-------|------|-------------|------------|
| 99-145 | Función comentada | `normalizarTextoEducativo()` dentro de `/* ... */` — 46 líneas | ✅ **Sí** |
| 27-29 | Debug en producción | `ini_set('display_errors', 1)` | ✅ **Sí** |

### 4.3 `escuelas_detalle.php`

| Línea | Tipo | Descripción | ¿Eliminar? |
|-------|------|-------------|------------|
| 45-47 | Debug en producción | `ini_set('display_errors', 1)` | ✅ **Sí** |

### 4.4 `directorio_estatal.php`

| Línea | Tipo | Descripción | ¿Eliminar? |
|-------|------|-------------|------------|
| 32-34 | Debug en producción | `ini_set('display_errors', 1)` | ✅ **Sí** |

### 4.5 `resumen.php`

| Línea | Tipo | Descripción | ¿Eliminar? |
|-------|------|-------------|------------|
| 195-208 | Funciones engañosas | `calcularTotales()` y `calcularTotalesDocentes()` ignoran sus parámetros, usan `$GLOBALS` | ⚠️ Refactorizar |

### 4.6 `settings.php`

| Línea | Tipo | Descripción | ¿Eliminar? |
|-------|------|-------------|------------|
| 312-321 | Código HTML comentado | Botones deshabilitados "Cancelar" y "Guardar" | ✅ **Sí** |

### 4.7 JS — Código muerto en archivos activos

| Archivo | Problema |
|---------|----------|
| `js/alumnos.js` | `inicializarEventos()`, `inicializarAnimaciones()`, `inicializarExportacion()` definidas **2 veces** cada una — la primera definición es muerta |
| `js/login.js` | Primer handler de submit (L76-145) es muerto — el segundo (L153) lo sobreescribe |
| `js/script.js` | `exportarExcel()` y `exportarGraficoExcel()` — dos funciones de exportación; dark mode toggle vacío (L467-470); `exportarGraficoModal` referenciado pero **nunca definido** |
| `js/docentes.js` | `showDocenteDetails()` — stub que solo hace `console.log` |
| `js/back_to_top.js` | Código de Google Analytics comentado (L136-141) |
| `js/escuelas_publicas_privadas.js` | `diagnosticarDatos()` se auto-ejecuta en producción con timer de 2s |

---

## 5. Bugs Críticos Encontrados

### 🔴 BUG 1: Case duplicado en `conexion.php` (Línea 898)

```php
// Línea 617: case real con query correcta
case 'especial_tot':
    return "SELECT ... FROM est{$ini_ciclo}_{$filtro} ...";

// Línea 898: DUPLICADO con valores de prueba que SOBREESCRIBE el case real
case 'especial_tot':
    return "SELECT 999 AS total_matricula, 888 AS total_docentes ...";
```

**Impacto:** Los datos de educación especial siempre muestran `999` en lugar de datos reales.  
**Acción:** Eliminar el case duplicado de línea 898. **URGENTE.**

### 🔴 BUG 2: Función inexistente en `conexion.php` (Línea 3589)

```php
function arreglos_datos_segura($ini_ciclo, $str_consulta, $muni) {
    // ...
    $sub_pub = subnivel_seguro(...); // ❌ Esta función NO EXISTE
    $sub_priv = subnivel_seguro(...);
}
```

**Impacto:** Fatal error si se llama esta función.  
**Acción:** Corregir o eliminar la función.

### 🔴 BUG 3: Credenciales hardcodeadas en `js/login.js` (Línea 102)

```javascript
if (username === 'practicas25.dppee@gmail.com' && password === 'Balluff254') {
    // bypass login
}
```

**Impacto:** Credenciales expuestas públicamente en código JavaScript del lado del cliente.  
**Acción:** Eliminar inmediatamente.

### 🔴 BUG 4: Handler de submit duplicado en `js/login.js`

El evento `submit` del formulario de login se registra **dos veces** (líneas 76 y 153). El segundo sobreescribe al primero, pero en el catch del segundo se redirige a `home.php` incluso cuando falla la autenticación.

**Impacto:** Bypass de seguridad.

---

## 6. Problemas de Seguridad

| # | Severidad | Problema | Archivo | Acción |
|---|-----------|----------|---------|--------|
| 1 | 🔴 **Crítico** | Credenciales en código fuente cliente | `js/login.js:102` | Eliminar inmediatamente |
| 2 | 🔴 **Crítico** | Bypass de login en sesión | `session_helper.php:80-88` | Restaurar validación real; eliminar bypass de desarrollo |
| 3 | 🟡 **Alto** | Credenciales DB hardcodeadas | `conexion.php:72` (`password=postgres`) | Mover a variables de entorno |
| 4 | 🟡 **Alto** | `display_errors` en producción | `docentes.php`, `escuelas_detalle.php`, `directorio_estatal.php` | Eliminar |
| 5 | 🟡 **Medio** | SQL sin queries parametrizadas | `conexion.php` (interpolación directa) | Refactorizar a queries preparadas |
| 6 | 🟡 **Medio** | CSP desactivada | `headers/headers.php:35` (línea comentada) | Activar header CSP |
| 7 | 🟡 **Medio** | HTTPS redirect desactivado | `headers/headers.php:47-52` (comentado) | Activar en producción |

---

## 7. Violaciones de Convención snake_case

### 7.1 Funciones PHP (32+ violaciones)

El proyecto usa **camelCase** en todas sus funciones PHP. Para adoptar **snake_case**, las siguientes funciones necesitan renombramiento:

| Actual (camelCase) | Propuesto (snake_case) |
|---------------------|------------------------|
| `ConectarsePrueba()` | `conectarse_prueba()` |
| `Conectarse()` | `conectarse()` |
| `iniciarSesionDemo()` | `iniciar_sesion_demo()` |
| `fechaEnEspanol()` | `fecha_en_espanol()` |
| `obtenerCicloEscolarActual()` | `obtener_ciclo_escolar_actual()` |
| `obtenerInfoCicloEscolar()` | `obtener_info_ciclo_escolar()` |
| `tieneUnidades()` | `tiene_unidades()` |
| `obtenerMunicipiosPrueba2024()` | `obtener_municipios_prueba_2024()` |
| `obtenerResumenMunicipioCompleto()` | `obtener_resumen_municipio_completo()` |
| `obtenerResumenEstadoCompleto()` | `obtener_resumen_estado_completo()` |
| `obtenerDatosPublicoPrivado()` | `obtener_datos_publico_privado()` |
| `obtenerDatosUSAER()` | `obtener_datos_usaer()` |
| `obtenerDatosEducativosCompletos()` | `obtener_datos_educativos_completos()` |
| `obtenerDocentesPorNivelYSubnivel()` | `obtener_docentes_por_nivel_y_subnivel()` |
| `obtenerAlumnosPorNivelYSubnivel()` | `obtener_alumnos_por_nivel_y_subnivel()` |
| `obtenerDatosPorNivel()` | `obtener_datos_por_nivel()` |
| `obtenerMunicipios()` | `obtener_municipios()` |
| `obtenerDirectorioEscuelas()` | `obtener_directorio_escuelas()` |
| `obtenerEscuelasPorSubcontrolYNivel()` | `obtener_escuelas_por_subcontrol_y_nivel()` |
| `normalizarNombreMunicipio()` | `normalizar_nombre_municipio()` |
| `convertirParaConsultaDB()` | `convertir_para_consulta_db()` |
| `calcularPorcentajesMunicipioEstado()` | `calcular_porcentajes_municipio_estado()` |
| `formatearNombreMunicipio()` | `formatear_nombre_municipio()` |
| `formatPercent()` | `format_percent()` |
| `obtenerOrdenSubnivel()` | `obtener_orden_subnivel()` |
| `obtenerOrdenEducativo()` | `obtener_orden_educativo()` |
| `calcularTotales()` | `calcular_totales()` |
| `calcularTotalesDocentes()` | `calcular_totales_docentes()` |
| `datos_vacion()` | `datos_vacio()` (también corregir typo) |
| `extractMuniNumber()` | `extraer_numero_municipio()` (también traducir a español) |
| `aplicarAjusteUnidadesSuperior()` | `aplicar_ajuste_unidades_superior()` |
| `obtenerSubcontrolPorNivel()` | `obtener_subcontrol_por_nivel()` |

### 7.2 Variables PHP principales

| Actual | Propuesto |
|--------|-----------|
| `$municipioSeleccionado` | `$municipio_seleccionado` |
| `$datosCompletos` | `$datos_completos` |
| `$totalEscuelas` | `$total_escuelas` |
| `$totalAlumnos` | `$total_alumnos` |
| `$totalDocentes` | `$total_docentes` |
| `$filtroBase` | `$filtro_base` |
| `$nombreMunicipio` | `$nombre_municipio` |
| `$datosCompletosMunicipio` | `$datos_completos_municipio` |
| `$todosLosMunicipios` | `$todos_los_municipios` |
| `$infoCiclo` | `$info_ciclo` |
| `$currentCycle` | `$ciclo_actual` |
| `$nextCycleDisplay` | `$siguiente_ciclo_display` |
| `$preferencesMessage` | `$mensaje_preferencias` |

### 7.3 Funciones JS (nota)

En JavaScript el estándar es `camelCase`, pero si el proyecto requiere `snake_case`:
- Todas las funciones JS actuales usan camelCase (estándar de JS)
- Además hay mezcla de inglés y español en nombres
- **Recomendación:** Estandarizar a español con `snake_case` solo si es requisito explícito; de lo contrario, mantener `camelCase` en JS que es la convención del lenguaje

---

## 8. Violaciones de Principios SOLID

### 8.1 Single Responsibility Principle (SRP) — **Violación Severa**

**`conexion.php`** = 4,228 líneas con 37 funciones que manejan:
- Conexión a base de datos
- Construcción de queries SQL (~60 cases)
- Lógica de negocio (cálculos, porcentajes)
- Normalización de datos
- Mapeo de municipios
- Utilidades de formato

**Recomendación:** Dividir en módulos:
```
includes/
├── db_connection.php        (conexión y credenciales)
├── queries.php              (str_consulta_segura y SQL)
├── municipios.php           (mapeo y normalización de municipios)
├── datos_educativos.php     (funciones de obtención de datos)
├── helpers.php              (formateo, utilidades)
└── calculos.php             (porcentajes, totales)
```

### 8.2 Open/Closed Principle (OCP) — **Violación**

`str_consulta_segura()` tiene un switch con ~60 cases y 1,200 líneas. Agregar un nuevo tipo de consulta requiere modificar este switch. Debería usar un patrón de registro/estrategia.

### 8.3 DRY (Don't Repeat Yourself) — **Violación Masiva**

Ver sección 3 completa. Las funciones duplicadas entre archivos son el problema más urgente de mantener.

### 8.4 Interface Segregation — **Violación**

Cada página hace `require_once 'conexion.php'` cargando las 37 funciones cuando solo usa 2-3.

---

## 9. Problemas de Clean Code

### 9.1 Exceso de `console.log` en producción

| Archivo | Cantidad |
|---------|----------|
| `js/exports-estudiantes-v2.js` | 68 |
| `js/escuelas_publicas_privadas.js` | 34 |
| `js/alumnos.js` | 67 |
| `js/docentes.js` | 16 |
| `js/demo-dashboard.js` | 14 |
| `js/demo-ventas.js` | 16 |
| **Total** | **~230+** |

### 9.2 Exceso de `error_log` en PHP

| Archivo | Cantidad |
|---------|----------|
| `settings.php` | 21 |
| `conexion.php` | 15 |
| `escuelas_detalle.php` | 5 |

### 9.3 Typo en nombre de función

- `datos_vacion()` → debería ser `datos_vacio()` (con tilde: "vacío")

### 9.4 Comentario inapropiado

- `conexion.php:3905-3912` — Contiene texto informal y letras de canción

### 9.5 Función de diagnóstico auto-ejecutándose

- `js/escuelas_publicas_privadas.js:542` — `diagnosticarDatos()` se ejecuta automáticamente con un `setTimeout` de 2 segundos en producción

### 9.6 `error_log` excesivos en `settings.php`

21 llamadas a `error_log()` para debug de actualización de ciclo escolar. Deberían eliminarse o controlarse con un flag de debug.

---

## 10. Inconsistencias en Includes

### 10.1 Matriz de consistencia de includes

| Include | home | resumen | alumnos | docentes | escuelas_detalle | mapas | bibliotecas | comparacion | directorio_estatal | settings |
|---------|------|---------|---------|----------|-----------------|-------|-------------|-------------|-------------------|----------|
| `institutional_bar.php` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `header_logo.php` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `header_end.php` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |
| `footer.php` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `back_to_top.php` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| `session_helper.php` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |

**Problemas:**
1. **`directorio_estatal.php`** — No incluye `header_end.php` (falta menú hamburguesa móvil)
2. **`comparacion_municipios.php`** — No incluye `back_to_top.php`
3. **`comparacion_municipios.php`** — No usa `session_helper.php`, maneja sesión manualmente

### 10.2 Navegación inconsistente

Las páginas de "nivel superior" (home, directorio_estatal, bibliotecas, settings) usan una navegación diferente a las de "nivel municipio" (resumen, alumnos, docentes, escuelas_detalle, mapas). Esto está bien conceptualmente, pero la implementación del sidebar es inconsistente — a veces con `<ul>` y a veces con `<div>`.

---

## 11. Recomendaciones por Archivo

### `conexion.php` — 🔴 Necesita refactorización profunda

| # | Acción | Prioridad |
|---|--------|-----------|
| 1 | Eliminar case duplicado `especial_tot` (L898-908) | 🔴 URGENTE |
| 2 | Eliminar/corregir `arreglos_datos_segura()` (L3567-3599) | 🔴 URGENTE |
| 3 | Eliminar comentario inapropiado (L3905-3912) | 🟡 ALTA |
| 4 | Mover credenciales DB a archivo de configuración | 🟡 ALTA |
| 5 | Extraer mapeo de municipios a un solo lugar | 🟡 MEDIA |
| 6 | Renombrar `datos_vacion()` → `datos_vacio()` | 🟢 BAJA |
| 7 | Refactorizar a módulos (largo plazo) | 🟢 BAJA |

### `session_helper.php` — 🔴 Problema de seguridad

| # | Acción | Prioridad |
|---|--------|-----------|
| 1 | Eliminar bypass de desarrollo (L80-88) y restaurar código de producción (L90-102) | 🔴 URGENTE |

### Archivos PHP de páginas — 🟡 Limpieza de duplicación

| # | Acción | Prioridad |
|---|--------|-----------|
| 1 | Crear `includes/helpers.php` con funciones compartidas | 🟡 ALTA |
| 2 | Eliminar `ini_set('display_errors')` de 3 archivos | 🟡 ALTA |
| 3 | Eliminar función comentada en `docentes.php` (L99-145) | 🟢 BAJA |

### Archivos JS — 🔴 Limpieza de archivos muertos

| # | Acción | Prioridad |
|---|--------|-----------|
| 1 | Eliminar 6 archivos JS no referenciados (demos, estudiantes, exports) | 🟡 ALTA |
| 2 | Mover `historicos.js` a carpeta `deprecated/` | 🟡 MEDIA |
| 3 | Limpiar definiciones duplicadas en `alumnos.js` | 🟡 MEDIA |
| 4 | Eliminar `diagnosticarDatos()` auto-ejecutable | 🟡 MEDIA |
| 5 | Reducir `console.log` en archivos activos | 🟢 BAJA |

---

## 12. Plan de Limpieza

### Fase 1 — Corrección de Bugs Críticos (URGENTE)

1. ~~Eliminar~~ Eliminar case duplicado `especial_tot` en `conexion.php` (L898-908)
2. Eliminar/corregir función rota `arreglos_datos_segura()` en `conexion.php`
3. Eliminar credenciales hardcodeadas de `js/login.js`
4. Restaurar validación de sesión real en `session_helper.php`

### Fase 2 — Eliminación de Código Muerto (Alta Prioridad)

1. Eliminar archivos JS no referenciados:
   - `js/demo-dashboard.js`
   - `js/demo-ventas.js`
   - `js/estudiantes.js`
   - `js/exports-estudiantes-v2.js`
   - `js/export-sections.js`
   - `js/export-manager-annotations.js`
2. Eliminar `css/estudiantes.css`
3. Eliminar `img/user-avatar.jpg`
4. Mover `js/historicos.js` y `js/login.js` a carpeta `deprecated/`

### Fase 3 — Eliminación de Duplicación PHP (Alta Prioridad)

1. Crear archivo `includes/helpers.php` con:
   - `formatear_nombre_municipio()`
   - `format_percent()`
   - `obtener_orden_subnivel()`
2. Reemplazar las copias en los 7 archivos PHP por `require_once 'includes/helpers.php'`
3. Eliminar `ini_set('display_errors')` de `docentes.php`, `escuelas_detalle.php`, `directorio_estatal.php`
4. Eliminar función comentada `normalizarTextoEducativo()` de `docentes.php`
5. Eliminar comentario inapropiado de `conexion.php`
6. Eliminar `error_log` de debug excesivos en `settings.php`

### Fase 4 — Corrección de Inconsistencias (Media Prioridad)

1. Agregar `include 'includes/header_end.php'` a `directorio_estatal.php`
2. Agregar `include 'includes/back_to_top.php'` a `comparacion_municipios.php`
3. Agregar `require_once 'session_helper.php'` a `comparacion_municipios.php`
4. Eliminar definiciones duplicadas de funciones en `js/alumnos.js`
5. Eliminar `diagnosticarDatos()` auto-ejecutable de `js/escuelas_publicas_privadas.js`
6. Corregir typo `datos_vacion()` → `datos_vacio()`

### Fase 5 — Migración a snake_case (Prioridad Baja — Alto Riesgo)

> ⚠️ **ADVERTENCIA:** Esta fase debe realizarse con extrema precaución. Renombrar funciones afecta todos los archivos que las llaman. Se recomienda:
> 1. Hacerlo en una rama separada de Git
> 2. Usar buscar/reemplazar global por cada función
> 3. Probar exhaustivamente después de cada grupo de cambios

1. Renombrar funciones PHP en `conexion.php` y actualizar todas las llamadas
2. Renombrar funciones PHP en archivos de página
3. Renombrar variables PHP principales
4. (Opcional) Renombrar funciones JS si se decide adoptar snake_case en JS

### Fase 6 — Refactorización Estructural (Largo Plazo)

1. Dividir `conexion.php` en módulos por responsabilidad
2. Refactorizar `str_consulta_segura()` con patrón Strategy/Registry
3. Extraer lógica compartida de `directorio_escuelas.js` ↔ `directorio_estatal.js`
4. Unificar sistema de notificaciones JS
5. Implementar queries parametrizadas para prevenir SQL injection
6. Mover credenciales a variables de entorno

---

## Resumen de Impacto

| Fase | Archivos a modificar | Líneas eliminadas aprox. | Riesgo |
|------|---------------------|--------------------------|--------|
| Fase 1 | 3 | ~30 | **Bajo** (bugs obvios) |
| Fase 2 | 0 (solo eliminar) | **~5,400** | **Bajo** (archivos sin referencia) |
| Fase 3 | 8 | ~200 (neto) | **Bajo** (reemplazar por include) |
| Fase 4 | 5 | ~50 | **Bajo** |
| Fase 5 | 10+ | 0 (renombrar) | **Alto** (puede romper funcionalidad) |
| Fase 6 | 3+ | Variable | **Muy Alto** (reestructuración) |

**Total código muerto/duplicado identificado: ~6,000+ líneas**
