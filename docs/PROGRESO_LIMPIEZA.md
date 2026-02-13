# 📋 Progreso de Limpieza — Tableros Municipales SEDEQ

**Documento de seguimiento entre sesiones**  
**Última actualización:** 13 de febrero de 2026  
**Análisis base:** [`docs/ANALISIS_CODEBASE_EXHAUSTIVO.md`](ANALISIS_CODEBASE_EXHAUSTIVO.md)

---

## Estado General

| Fase | Descripción | Estado | Progreso |
|------|-------------|--------|----------|
| **Fase 1** | Corrección de bugs críticos | ✅ **Completada** | 4/4 |
| **Fase 2** | Eliminación de código muerto (archivos) | 🔲 Pendiente | 0/4 |
| **Fase 3** | Eliminación de duplicación PHP | 🟡 Parcial | 5/6 |
| **Fase 4** | Corrección de inconsistencias | 🔲 Pendiente | 0/6 |
| **Fase 5** | Migración a snake_case | 🔲 Pendiente | 0/3 |
| **Fase 6** | Refactorización estructural | 🔲 Pendiente | 0/6 |
| **Extra** | Eliminación del sistema de sesiones | ✅ **Completada** | 7/7 |

---

## Fase 1 — Corrección de Bugs Críticos ✅

> Todos los bugs críticos identificados fueron corregidos.

| # | Tarea | Estado | Detalle |
|---|-------|--------|---------|
| 1 | Eliminar case duplicado `especial_tot` en `conexion.php` | ✅ | Estaba en ~línea 898, devolvía valores de prueba `999`/`888` que sobreescribían el case real de línea 617. **Eliminado.** |
| 2 | Eliminar función rota `arreglos_datos_segura()` en `conexion.php` | ✅ | Llamaba a `subnivel_seguro()` que no existe. Fatal error si se invocaba. **Eliminada.** |
| 3 | Eliminar credenciales hardcodeadas de `js/login.js` | ✅ | Contenía `practicas25.dppee@gmail.com` / `Balluff254` en texto plano. Reescrito para validar vía `process_login.php`. |
| 4 | Eliminar bypass de sesión en `session_helper.php` | ✅ | Se restauró la validación real. Posteriormente el archivo completo fue eliminado (ver sección Extra). |

### Archivos modificados en Fase 1
- `conexion.php` — 2 bloques eliminados (case duplicado + función rota + comentario inapropiado en L3905-3912)
- `js/login.js` — Reescrito handler de autenticación

---

## Fase 2 — Eliminación de Código Muerto (Archivos) 🔲

> Archivos que no están referenciados desde ningún PHP/HTML activo.

| # | Tarea | Estado | Archivo | Líneas | Notas |
|---|-------|--------|---------|--------|-------|
| 1 | Eliminar JS de demos y estudiantes | 🔲 | `js/exports-estudiantes-v2.js` | ~1290 | Exportaciones para estudiantes.js que ya no existe |
| 2 | Eliminar JS de demos y estudiantes | 🔲 | `js/export-sections.js` | ~557 | No referenciado, funcionalidad duplicada |
| 3 | Eliminar JS de demos y estudiantes | 🔲 | `js/export-manager-annotations.js` | ~567 | Solo usado por demos eliminados |
| 4 | Eliminar CSS muerto | 🔲 | `css/estudiantes.css` | 648 | No existe `estudiantes.php`; reemplazado por `alumnos.css` |
| 5 | Eliminar imagen no referenciada | 🔲 | `img/user-avatar.jpg` | — | Sin referencia en ningún archivo |

> **Nota:** Los archivos `js/demo-dashboard.js`, `js/demo-ventas.js`, `js/estudiantes.js`, `js/historicos.js` y `js/login.js` ya fueron eliminados en sesiones anteriores (no aparecen en el directorio actual).

### Archivos JS actualmente en `js/` (17 archivos)
```
js/
├── alumnos.js                      ← Activo (alumnos.php)
├── animations_global.js            ← Activo (todas las páginas)
├── back_to_top.js                  ← Activo (includes/back_to_top.php)
├── directorio_escuelas.js          ← Activo (escuelas_detalle.php)
├── directorio_estatal.js           ← Activo (directorio_estatal.php)
├── docentes.js                     ← Activo (docentes.php)
├── escuelas_diagram.js             ← Activo (escuelas_detalle.php)
├── escuelas_publicas_privadas.js   ← Activo (escuelas_detalle.php)
├── export-manager-annotations.js   ← 🔴 MUERTO — eliminar
├── export-sections.js              ← 🔴 MUERTO — eliminar
├── export-utils.js                 ← Activo (múltiples páginas)
├── exports-estudiantes-v2.js       ← 🔴 MUERTO — eliminar
├── home.js                         ← Activo (home.php)
├── mapas.js                        ← Activo (mapas.php)
├── script.js                       ← Activo (todas las páginas)
├── settings.js                     ← Activo (settings.php)
└── sidebar.js                      ← Activo (todas las páginas)
```

**Total de código muerto a eliminar en Fase 2: ~3,062 líneas**

---

## Fase 3 — Eliminación de Duplicación PHP 🟡

> Se creó `includes/helpers.php` como archivo centralizado de funciones compartidas.

| # | Tarea | Estado | Detalle |
|---|-------|--------|---------|
| 1 | Crear `includes/helpers.php` con funciones compartidas | ✅ | Contiene 4 funciones: `formatearNombreMunicipio()`, `formatPercent()`, `obtenerOrdenSubnivel()`, `fechaEnEspanol()` |
| 2 | Deduplicar `formatearNombreMunicipio()` (7 copias → 1) | ✅ | Eliminada de: alumnos, docentes, resumen, escuelas_detalle, home, comparacion_municipios, mapas |
| 3 | Deduplicar `formatPercent()` (3 copias → 1, unificada con coma) | ✅ | Eliminada de: alumnos, docentes, resumen. Separador de miles unificado a `,` |
| 4 | Deduplicar `obtenerOrdenSubnivel()` (2 copias → 1) | ✅ | Eliminada de: alumnos, docentes |
| 5 | Eliminar `ini_set('display_errors')` de archivos de producción | 🔲 | Queda en: `docentes.php` (L24-26), `escuelas_detalle.php` (L42-44). Ya se eliminó de `directorio_estatal.php`. |
| 6 | Eliminar función comentada `normalizarTextoEducativo()` de `docentes.php` | 🔲 | Está en L77+ (ya no está comentada, hay que verificar si se usa antes de eliminar) |

### Estado de `includes/helpers.php`
```php
// 4 funciones, ~148 líneas
formatearNombreMunicipio($municipio)   // Formato título para nombres de municipios
formatPercent($value, $decimals = 2)   // number_format con coma como separador de miles
obtenerOrdenSubnivel($nivel, $subnivel) // Orden de visualización 1-16 para subniveles educativos
fechaEnEspanol($formato, $timestamp)   // Traduce fechas de PHP a español (meses y días)
```

### Archivos que incluyen `helpers.php` (8 archivos)
```
alumnos.php             (L48)
bibliotecas.php         (L20)
comparacion_municipios.php (L61)
docentes.php            (L49)
escuelas_detalle.php    (L62)
home.php                (L90)
mapas.php               (L111)
resumen.php             (L205)
```

> **Archivos que NO incluyen `helpers.php`:** `directorio_estatal.php`, `settings.php` — actualmente no usan ninguna función de helpers.

---

## Fase 4 — Corrección de Inconsistencias 🔲

| # | Tarea | Estado | Detalle |
|---|-------|--------|---------|
| 1 | Agregar `header_end.php` a `directorio_estatal.php` | 🔲 | Falta menú hamburguesa móvil. Actualmente tiene `back_to_top.php` (L584) pero no `header_end.php`. |
| 2 | Agregar `back_to_top.php` a `comparacion_municipios.php` | 🔲 | Tiene `header_end.php` (L114) pero falta `back_to_top.php`. |
| 3 | Eliminar definiciones duplicadas de funciones en `js/alumnos.js` | 🔲 | `inicializarEventos()`, `inicializarAnimaciones()`, `inicializarExportacion()` definidas 2 veces cada una; la primera definición es muerta. |
| 4 | Eliminar `diagnosticarDatos()` auto-ejecutable de `js/escuelas_publicas_privadas.js` | 🔲 | Se auto-ejecuta con `setTimeout` de 2s en producción (L542). Herramienta de debug que no debería correr en producción. |
| 5 | Corregir typo `datos_vacion()` → `datos_vacio()` en `conexion.php` | 🔲 | Nombre incorrecto de función. |
| 6 | Limpiar `error_log` excesivos en `settings.php` | 🔲 | 21 llamadas a `error_log()` para debug de ciclo escolar. |

---

## Fase 5 — Migración a snake_case 🔲

> ⚠️ **ALTO RIESGO** — Renombrar funciones afecta todos los archivos que las llaman.
> Recomendación: rama separada de Git, buscar/reemplazar global, probar exhaustivamente.

| # | Tarea | Estado | Detalle |
|---|-------|--------|---------|
| 1 | Renombrar funciones PHP en `conexion.php` y actualizar llamadas | 🔲 | 32+ funciones camelCase → snake_case (ver tabla en análisis §7.1) |
| 2 | Renombrar funciones PHP en archivos de página | 🔲 | Variables como `$municipioSeleccionado`, `$datosCompletos`, etc. |
| 3 | Renombrar funciones en `includes/helpers.php` | 🔲 | `formatearNombreMunicipio` → `formatear_nombre_municipio`, `formatPercent` → `format_percent`, `obtenerOrdenSubnivel` → `obtener_orden_subnivel`, `fechaEnEspanol` → `fecha_en_espanol` |

> **Nota sobre JS:** En JavaScript el estándar es `camelCase`. Mantener camelCase en JS es la convención del lenguaje. Solo migrar PHP.

---

## Fase 6 — Refactorización Estructural 🔲

> Largo plazo. `conexion.php` es un "God file" de 4,228 líneas con 37 funciones.

| # | Tarea | Estado | Detalle |
|---|-------|--------|---------|
| 1 | Dividir `conexion.php` en módulos por responsabilidad | 🔲 | Propuesta: `db_connection.php`, `queries.php`, `municipios.php`, `datos_educativos.php`, `calculos.php` |
| 2 | Refactorizar `str_consulta_segura()` (~60 cases, ~1200 líneas) | 🔲 | Usar patrón Strategy/Registry en lugar de switch monolítico |
| 3 | Extraer lógica compartida `directorio_escuelas.js` ↔ `directorio_estatal.js` | 🔲 | ~80% de código duplicado entre ambos; extraer a `directorio_base.js` |
| 4 | Unificar sistema de notificaciones/toasts en JS | 🔲 | 3 implementaciones activas tras eliminar archivos muertos |
| 5 | Implementar queries parametrizadas (prevenir SQL injection) | 🔲 | `conexion.php` usa interpolación directa en SQL |
| 6 | Mover credenciales DB a variables de entorno | 🔲 | `conexion.php:72` tiene `password=postgres` hardcodeado |

---

## Extra — Eliminación del Sistema de Sesiones ✅

> El usuario decidió que el sitio debe ser siempre público, sin autenticación.

| # | Tarea | Estado | Detalle |
|---|-------|--------|---------|
| 1 | Migrar `fechaEnEspanol()` a `includes/helpers.php` | ✅ | Función movida y verificada |
| 2 | Eliminar `require_once 'session_helper.php'` + `iniciarSesionDemo()` de 9 archivos | ✅ | alumnos, bibliotecas, directorio_estatal, docentes, home, escuelas_detalle, mapas, settings, resumen |
| 3 | Eliminar bloque `session_start()` de `comparacion_municipios.php` | ✅ | Era un bloque manual `if (!isset($_SESSION)) { session_start(); }` en L19-22 |
| 4 | Reemplazar `$_SESSION` en `settings.php` con valores estáticos | ✅ | `$userFullname='Usuario SEDEQ'`, `$userEmail='usuario@sedeq.gob.mx'`, `$userRole='Analista de Datos'` |
| 5 | Agregar `require_once 'includes/helpers.php'` a `bibliotecas.php` | ✅ | Necesitaba `fechaEnEspanol()` pero no tenía el require |
| 6 | Eliminar archivo `session_helper.php` | ✅ | Archivo borrado del proyecto |
| 7 | Verificar 0 referencias a sesiones en todo el proyecto | ✅ | grep de `session_helper`, `iniciarSesionDemo`, `session_start`, `$_SESSION` = 0 resultados |

---

## Resumen de Archivos Modificados (Acumulado)

| Archivo | Cambios realizados |
|---------|-------------------|
| `conexion.php` | Eliminado: case duplicado `especial_tot` (~L898), función rota `arreglos_datos_segura()`, comentario inapropiado (L3905-3912) |
| `js/login.js` | Reescrito: eliminadas credenciales hardcodeadas, auth redirigida a server-side |
| `includes/helpers.php` | **CREADO**: 4 funciones compartidas (148 líneas) |
| `session_helper.php` | **ELIMINADO**: ya no existe en el proyecto |
| `alumnos.php` | Eliminadas: 3 funciones locales → `require helpers.php`; eliminado session_helper |
| `docentes.php` | Eliminadas: 3 funciones locales → `require helpers.php`; eliminado session_helper |
| `resumen.php` | Eliminadas: 2 funciones locales → `require helpers.php`; eliminado session_helper |
| `escuelas_detalle.php` | Eliminada: 1 función local → `require helpers.php`; eliminado session_helper |
| `home.php` | Eliminada: 1 función local → `require helpers.php`; eliminado session_helper |
| `comparacion_municipios.php` | Eliminada: 1 función local → `require helpers.php`; eliminado session_start |
| `mapas.php` | Eliminada: 1 función local → `require helpers.php`; eliminado session_helper |
| `bibliotecas.php` | Agregado: `require helpers.php`; eliminado session_helper |
| `directorio_estatal.php` | Eliminado: session_helper + ini_set display_errors |
| `settings.php` | Eliminado: session_helper; $_SESSION → valores estáticos |

---

## Problemas de Seguridad Pendientes

| # | Severidad | Problema | Archivo | Fase |
|---|-----------|----------|---------|------|
| 1 | 🟡 Alto | Credenciales DB hardcodeadas (`password=postgres`) | `conexion.php:72` | Fase 6 |
| 2 | 🟡 Alto | `display_errors` activo en producción | `docentes.php`, `escuelas_detalle.php` | Fase 3 |
| 3 | 🟡 Medio | SQL sin queries parametrizadas | `conexion.php` (interpolación directa) | Fase 6 |
| 4 | 🟡 Medio | CSP header desactivada | `headers/headers.php:35` (comentada) | Fase 6 |
| 5 | 🟡 Medio | HTTPS redirect desactivado | `headers/headers.php:47-52` (comentado) | Fase 6 |

---

## Próximos Pasos Recomendados

1. **Fase 2** (bajo riesgo): Eliminar 3 archivos JS muertos + 1 CSS + 1 imagen (~3,062 líneas)
2. **Fase 3 restante** (bajo riesgo): Eliminar `ini_set display_errors` de 2 archivos, verificar `normalizarTextoEducativo`
3. **Fase 4** (bajo riesgo): Agregar includes faltantes, limpiar JS duplicado, corregir typo
4. **Fase 5** (alto riesgo): Migración snake_case — hacerlo en rama Git separada
5. **Fase 6** (muy alto riesgo): Reestructuración de conexion.php — planificar con cuidado

---

## Arquitectura Actual del Proyecto

```
Tableros_Municipales/
├── conexion.php              ← God file: 4228 líneas, 37 funciones, conexión + SQL + lógica
├── includes/
│   ├── helpers.php           ← NUEVO: funciones compartidas (4 funciones)
│   ├── institutional_bar.php ← Barra institucional (carga headers/headers.php)
│   ├── header_logo.php       ← Logo y navegación superior
│   ├── header_end.php        ← Cierre de header + menú móvil
│   ├── footer.php            ← Footer institucional
│   └── back_to_top.php       ← Botón scroll-to-top
├── headers/
│   └── headers.php           ← Headers HTTP de seguridad
├── Páginas nivel superior (sin sidebar de municipio):
│   ├── home.php              ← Dashboard principal
│   ├── directorio_estatal.php
│   ├── bibliotecas.php
│   └── settings.php          ← Configuración de ciclo escolar
├── Páginas nivel municipio (con sidebar):
│   ├── resumen.php
│   ├── alumnos.php
│   ├── docentes.php
│   ├── escuelas_detalle.php
│   ├── mapas.php
│   └── comparacion_municipios.php
├── js/                       ← 17 archivos (14 activos + 3 muertos)
├── css/                      ← 10 archivos (9 activos + 1 muerto)
├── data/                     ← Archivos JSON de datos
├── img/                      ← Imágenes (1 sin usar: user-avatar.jpg)
└── docs/
    ├── ANALISIS_CODEBASE_EXHAUSTIVO.md
    └── PROGRESO_LIMPIEZA.md   ← Este archivo
```
