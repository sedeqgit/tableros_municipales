# Revisión Completa del Sistema de Dashboard Estadístico SEDEQ
## Sistema de Estadística Educativa - Secretaría de Educación del Estado de Querétaro

---

## 📋 Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Análisis Detallado: conexion_prueba_2024.php](#análisis-detallado-conexion_prueba_2024php)
4. [Módulos Principales del Sistema](#módulos-principales-del-sistema)
5. [Base de Datos y Estructura](#base-de-datos-y-estructura)
6. [Archivos Auxiliares Importantes](#archivos-auxiliares-importantes)
7. [Frontend y Visualización](#frontend-y-visualización)
8. [Seguridad y Autenticación](#seguridad-y-autenticación)
9. [Análisis de Dependencias](#análisis-de-dependencias)
10. [Recomendaciones y Mejoras](#recomendaciones-y-mejoras)

---

## 1. Resumen Ejecutivo

### 🎯 Propósito del Sistema

El **Sistema de Dashboard Estadístico SEDEQ** es una aplicación web robusta diseñada para la **Secretaría de Educación del Estado de Querétaro (SEDEQ)** que permite visualizar, analizar y exportar datos estadísticos educativos del estado de Querétaro.

### 📊 Características Principales

- **Visualización de datos educativos** por municipio y nivel educativo
- **Dashboards interactivos** con gráficas y tablas dinámicas
- **Exportación de datos** en múltiples formatos (Excel, PDF)
- **Comparación entre municipios**
- **Directorio estatal de escuelas** con búsqueda avanzada
- **Mapas interactivos** de distribución educativa
- **Análisis de datos históricos**
- **Sistema de autenticación** con modo demo

### 🏗️ Stack Tecnológico

- **Backend**: PHP 7.4+
- **Base de Datos**: PostgreSQL (puerto 5433)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Librerías de Visualización**: Google Charts
- **Librerías de Exportación**: jsPDF, xlsx.js, html2canvas
- **Servidor**: Apache (XAMPP)

---

## 2. Arquitectura del Sistema

### 📁 Estructura de Directorios

```
Corregidora/
├── *.php                           # Archivos PHP principales
├── css/                            # Hojas de estilo modularizadas
│   ├── global.css                  # Estilos globales
│   ├── home.css                    # Estilos de página principal
│   ├── resumen.css                 # Estilos de dashboard
│   ├── sidebar.css                 # Estilos del menú lateral
│   ├── login.css                   # Estilos de login
│   └── [otros].css                 # Estilos específicos por módulo
├── js/                             # Scripts JavaScript
│   ├── animations_global.js        # Animaciones globales
│   ├── sidebar.js                  # Funcionalidad del menú
│   ├── home.js                     # Lógica de página principal
│   ├── export-utils.js             # Utilidades de exportación
│   └── [otros].js                  # Scripts específicos por módulo
└── img/                            # Recursos gráficos
    └── layout_set_logo.png         # Logo institucional
```

### 🔄 Flujo de Datos

```
Usuario → login.php → process_login.php → session_helper.php
    ↓
home.php (Centro de navegación)
    ↓
resumen.php?municipio=X → conexion_prueba_2024.php
    ↓
Base de Datos PostgreSQL (nonce_pano_24)
    ↓
Procesamiento de Datos (PHP)
    ↓
Visualización (Google Charts + HTML/CSS/JS)
```

---

## 3. Análisis Detallado: conexion_prueba_2024.php

### 🎯 Propósito del Archivo

`conexion_prueba_2024.php` es el **núcleo del sistema de consultas de datos educativos**. Este archivo:

1. Gestiona la conexión a la base de datos PostgreSQL
2. Define todas las consultas SQL para obtener estadísticas educativas
3. Proporciona funciones para procesar y agregar datos
4. Implementa la lógica de negocio para cálculos especiales

### 📦 Estructura del Archivo (aproximadamente 2000+ líneas)

```php
conexion_prueba_2024.php
│
├── Constantes y Configuración (líneas 1-50)
│   ├── CICLO_ESCOLAR_ACTUAL = '24'
│   └── Funciones de información del ciclo
│
├── Conexión a Base de Datos (líneas 48-78)
│   ├── ConectarsePrueba()
│   └── Conectarse() (alias)
│
├── Mapeo de Municipios (líneas 80-130)
│   ├── nombre_municipio($num_munic)
│   └── obtenerMunicipiosPrueba2024()
│
├── Sistema de Consultas SQL (líneas 144-1320)
│   ├── str_consulta_segura($tipo, $ciclo, $filtro)
│   └── +100 tipos de consultas diferentes
│
├── Funciones de Agregación (líneas 1320-1600)
│   ├── acum_unidades()
│   └── acum_unidades_superior()
│
├── Funciones de Procesamiento (líneas 1600-2000+)
│   ├── obtenerResumenMunicipioCompleto()
│   ├── obtenerResumenEstadoCompleto()
│   ├── rs_consulta_segura()
│   └── obtenerDatosPorNivel()
```

### 🔧 Componentes Clave

#### A. Gestión del Ciclo Escolar

```php
// Constante global para el ciclo escolar actual
define('CICLO_ESCOLAR_ACTUAL', '24');

/**
 * Función centralizada para obtener el ciclo escolar
 * Facilita cambios futuros de ciclo
 */
function obtenerCicloEscolarActual() {
    return CICLO_ESCOLAR_ACTUAL;
}

/**
 * Obtiene información completa del ciclo
 * Retorna: [
 *   'ciclo_corto' => '24',
 *   'ciclo_completo' => '2024-2025',
 *   'esquema_bd' => 'nonce_pano_24',
 *   'descripcion' => 'Ciclo Escolar 2024-2025'
 * ]
 */
function obtenerInfoCicloEscolar() {
    $ciclo = CICLO_ESCOLAR_ACTUAL;
    $anio_inicio = 2000 + intval($ciclo);
    $anio_fin = $anio_inicio + 1;

    return [
        'ciclo_corto' => $ciclo,
        'ciclo_completo' => "$anio_inicio-$anio_fin",
        'esquema_bd' => "nonce_pano_$ciclo",
        'descripcion' => "Ciclo Escolar $anio_inicio-$anio_fin"
    ];
}
```

**Ventajas de este diseño:**
- ✅ Centralización: Solo un lugar para cambiar el ciclo
- ✅ Consistencia: Todos los módulos usan la misma fuente
- ✅ Mantenibilidad: Fácil actualización anual

#### B. Conexión a Base de Datos

```php
function ConectarsePrueba() {
    // Verificar que las extensiones PostgreSQL estén disponibles
    if (!function_exists('pg_connect')) {
        error_log('SEDEQ: Extensiones PostgreSQL no disponibles');
        return false;
    }

    try {
        // Cadena de conexión con parámetros específicos
        $connectionString = "host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres options='--client_encoding=UTF8'";
        $conn = pg_connect($connectionString);

        if (!$conn) {
            error_log('SEDEQ: Error de conexión - ' . pg_last_error());
            return false;
        }

        // Configurar encoding UTF-8 para caracteres especiales
        pg_set_client_encoding($conn, "UTF8");

        return $conn;
    } catch (Exception $e) {
        error_log('SEDEQ: Excepción en conexión: ' . $e->getMessage());
        return false;
    }
}
```

**Características importantes:**
- ✅ **Puerto no estándar**: 5433 (PostgreSQL secundario)
- ✅ **Encoding UTF-8**: Para manejar acentos y caracteres especiales (Querétaro, Peñamiller, etc.)
- ✅ **Manejo de errores**: Logging de problemas
- ✅ **Validación**: Verifica extensiones antes de conectar

#### C. Mapeo de Municipios

```php
/**
 * Mapea números de municipio a nombres oficiales
 * Resuelve problemas de encoding desde la base de datos
 */
function nombre_municipio($num_munic) {
    $nom_munic = [
        "1" => "AMEALCO DE BONFIL",
        "2" => "PINAL DE AMOLES",
        "3" => "ARROYO SECO",
        "4" => "CADEREYTA DE MONTES",
        "5" => "COLÓN",
        "6" => "CORREGIDORA",
        "7" => "EZEQUIEL MONTES",
        "8" => "HUIMILPAN",
        "9" => "JALPAN DE SERRA",
        "10" => "LANDA DE MATAMOROS",
        "11" => "EL MARQUÉS",
        "12" => "PEDRO ESCOBEDO",
        "13" => "PEÑAMILLER",
        "14" => "QUERÉTARO",
        "15" => "SAN JOAQUÍN",
        "16" => "SAN JUAN DEL RÍO",
        "17" => "TEQUISQUIAPAN",
        "18" => "TOLIMÁN"
    ];

    return isset($nom_munic[$num_munic]) ? $nom_munic[$num_munic] : null;
}
```

**¿Por qué un mapeo local?**
- 🔧 **Problema**: La base de datos devuelve nombres con problemas de encoding (QUERÉTARO aparece mal)
- ✅ **Solución**: Mapeo hardcoded garantiza nombres correctos con acentos
- 📦 **18 municipios** del estado de Querétaro

#### D. Sistema de Consultas SQL Dinámicas

Esta es la **parte más compleja y crítica** del archivo. Contiene más de 100 tipos de consultas diferentes.

```php
/**
 * Genera consultas SQL dinámicas según el tipo solicitado
 *
 * @param string $str_consulta Tipo de consulta (ej: 'gral_ini', 'preescolar', etc.)
 * @param string $ini_ciclo Ciclo escolar ('24')
 * @param string $filtro Filtro SQL adicional (ej: " AND c_nom_mun='QUERÉTARO'")
 * @return string|false SQL generado o false si el tipo no existe
 */
function str_consulta_segura($str_consulta, $ini_ciclo, $filtro) {
    // Filtro base: solo registros con estatus válido
    $filtroBase = "(cv_estatus_captura = 0 OR cv_estatus_captura = 10)";

    switch ($str_consulta) {
        // ===== EDUCACIÓN INICIAL =====
        case 'gral_ini':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V398+V414) AS total_matricula,
                        SUM(V390+V406) AS mat_hombres,
                        SUM(V394+V410) AS mat_mujeres,
                        SUM(V509+V516+...+V526) AS total_docentes,
                        ...
                        COUNT(cv_cct) AS escuelas,
                        SUM(V402+V418) AS grupos
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        // ===== PREESCOLAR =====
        case 'gral_pree':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        ...
                    FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        // ===== PRIMARIA =====
        case 'gral_prim':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V608) AS total_matricula,
                        ...
                    FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        // ===== SECUNDARIA =====
        case 'gral_sec':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V340) AS total_matricula,
                        ...
                    FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        // ===== MEDIA SUPERIOR =====
        case 'bgral_msup':
            return "SELECT CONCAT('BACHILLERATO GENERAL') AS titulo_fila,
                        SUM(V397) AS total_matricula,
                        ...
                        COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,
                        ...
                    FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro";

        // ===== SUPERIOR =====
        case 'carr_lic_sup':
            return "SELECT CONCAT('LICENCIATURA') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        ...
                    FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo
                    WHERE cv_motivo = '0' $filtro";

        // ===== EDUCACIÓN ESPECIAL =====
        case 'especial_tot':
            return "SELECT CONCAT('ESPECIAL (CAM)') AS titulo_fila,
                        SUM(V2257) AS total_matricula,
                        ...
                    FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo
                    WHERE cv_estatus_captura = 0 $filtro";

        // ===== CONSULTAS AGREGADAS COMPLEJAS =====
        case 'preescolar':
            // UNION de múltiples tablas
            return "SELECT 'PREESCOLAR' AS titulo_fila,
                        SUM(V177+V97+V478) AS total_matricula,
                        ...
                    FROM (
                        SELECT ... FROM pree_gral_24
                        UNION ALL
                        SELECT ... FROM pree_ind_24
                        UNION ALL
                        SELECT ... FROM pree_comuni_24
                        UNION ALL
                        SELECT ... FROM ini_gral_24 WHERE V478 > 0
                    ) AS preescolar";

        default:
            return false;
    }
}
```

**Tipos de consultas soportadas:**

1. **Consultas por Nivel Educativo**:
   - Inicial (Escolarizada y No Escolarizada)
   - Preescolar (General, Indígena, Comunitaria)
   - Primaria (General, Indígena, Comunitaria)
   - Secundaria (General, Telesecundaria, Técnica, Comunitaria)
   - Media Superior (Bachillerato General, Tecnológico)
   - Superior (Licenciatura, Posgrado, Normal)
   - Especial (CAM, USAER)

2. **Consultas por Tipo de Control**:
   - Público
   - Privado

3. **Consultas Agregadas**:
   - Resumen completo por municipio
   - Resumen completo estatal
   - Totales por nivel

4. **Consultas para Directorio**:
   - Lista de escuelas individuales
   - Datos por plantel
   - Información de contacto

**Estructura de datos retornada:**

Todas las consultas devuelven una fila con la siguiente estructura:

```php
[
    'titulo_fila' => 'NIVEL EDUCATIVO',
    'total_matricula' => 12345,
    'mat_hombres' => 6000,
    'mat_mujeres' => 6345,
    'total_docentes' => 500,
    'doc_hombres' => 200,
    'doc_mujeres' => 300,
    'escuelas' => 50,
    'grupos' => 300
]
```

#### E. Funciones de Procesamiento de Alto Nivel

##### obtenerResumenMunicipioCompleto()

```php
/**
 * Obtiene un resumen completo de todos los niveles educativos para un municipio
 * Esta es la función MÁS IMPORTANTE del sistema
 *
 * Agrega datos de:
 * - Inicial (escolarizada y no escolarizada)
 * - Preescolar
 * - Primaria
 * - Secundaria
 * - Media Superior
 * - Superior
 * - Especial (CAM)
 * - USAER
 *
 * @param string $municipio Nombre del municipio en MAYÚSCULAS
 * @return array Datos agregados por nivel con desgloses público/privado
 */
function obtenerResumenMunicipioCompleto($municipio) {
    $conn = ConectarsePrueba();
    if (!$conn) {
        return false;
    }

    $ciclo = obtenerCicloEscolarActual();
    $municipio_escapado = pg_escape_string($conn, $municipio);
    $filtro_municipal = " AND c_nom_mun='$municipio_escapado'";
    $filtro_pub = " AND control<>'PRIVADO' ";
    $filtro_priv = " AND control='PRIVADO' ";

    $resultado = [];

    // 1. INICIAL ESCOLARIZADA
    $inicial_esc = rs_consulta_segura($conn, 'inicial_esc', $ciclo, $filtro_municipal);
    $resultado['inicial_esc'] = $inicial_esc ? $inicial_esc : array_fill_keys([...], 0);

    // 2. INICIAL NO ESCOLARIZADA
    $inicial_no_esc = rs_consulta_segura($conn, 'inicial_no_esc', $ciclo, $filtro_municipal);
    $resultado['inicial_no_esc'] = $inicial_no_esc ? $inicial_no_esc : array_fill_keys([...], 0);

    // 3. PREESCOLAR (agrega general, indígena, comunitaria + primer grado de inicial)
    $preescolar = rs_consulta_segura($conn, 'preescolar', $ciclo, $filtro_municipal);
    $resultado['preescolar'] = $preescolar ? $preescolar : array_fill_keys([...], 0);

    // 4. PRIMARIA
    $primaria = rs_consulta_segura($conn, 'primaria', $ciclo, $filtro_municipal);
    $resultado['primaria'] = $primaria ? $primaria : array_fill_keys([...], 0);

    // 5. SECUNDARIA
    $secundaria = rs_consulta_segura($conn, 'secundaria', $ciclo, $filtro_municipal);
    $resultado['secundaria'] = $secundaria ? $secundaria : array_fill_keys([...], 0);

    // 6. MEDIA SUPERIOR
    $media_sup = rs_consulta_segura($conn, 'media_sup', $ciclo, $filtro_municipal);
    $resultado['media_sup'] = $media_sup ? $media_sup : array_fill_keys([...], 0);

    // 7. SUPERIOR (con lógica especial para unidades)
    $superior = rs_consulta_segura($conn, 'superior', $ciclo, $filtro_municipal);
    $unidades = rs_consulta_segura($conn, 'unidades_sup', $ciclo, $filtro_municipal);

    // Aplicar acum_unidades para ajustar datos
    // Querétaro: RESTA unidades estatales (evita doble conteo)
    // Otros municipios: SUMA unidades municipales
    $superior_ajustado = acum_unidades($conn, $ciclo, $filtro_pub, $filtro_priv, $filtro_municipal, 'SUPERIOR', $superior, $unidades);
    $resultado['superior'] = $superior_ajustado;

    // 8. ESPECIAL (CAM)
    $especial = rs_consulta_segura($conn, 'especial_tot', $ciclo, $filtro_municipal);
    $resultado['especial'] = $especial ? $especial : array_fill_keys([...], 0);

    // 9. USAER (no se suma en totales, se presenta por separado)
    $usaer = rs_consulta_segura($conn, 'especial_usaer', $ciclo, $filtro_municipal);
    $resultado['usaer'] = $usaer ? $usaer : array_fill_keys([...], 0);

    // 10. CALCULAR TOTALES GENERALES
    $resultado['total_matricula'] =
        $resultado['inicial_esc']['tot_mat'] +
        $resultado['inicial_no_esc']['tot_mat'] +
        $resultado['preescolar']['tot_mat'] +
        $resultado['primaria']['tot_mat'] +
        $resultado['secundaria']['tot_mat'] +
        $resultado['media_sup']['tot_mat'] +
        $resultado['superior']['tot_mat'] +
        $resultado['especial']['tot_mat'];
        // NOTA: USAER NO se suma (ya está contado en los otros niveles)

    $resultado['total_docentes'] = /* suma similar */;
    $resultado['total_escuelas'] = /* suma similar */;
    $resultado['total_grupos'] = /* suma similar */;

    pg_close($conn);
    return $resultado;
}
```

**Complejidades importantes:**

1. **Manejo de Unidades Estatales (Superior)**:
   - Querétaro (municipio 14): **RESTA** unidades porque están contadas en institución central
   - Otros municipios: **SUMA** unidades para reflejar presencia local

2. **USAER (Unidades de Servicios de Apoyo a la Educación Regular)**:
   - Matricula **NO se suma** en totales generales
   - Estudiantes USAER **ya están contados** en sus niveles respectivos
   - Se presenta como información adicional

3. **Preescolar incluye primer grado de Inicial**:
   - Algunos centros de inicial tienen primer grado de preescolar
   - Se evita doble conteo con lógica especial

### 🗄️ Esquema de Base de Datos Utilizado

El sistema consulta múltiples tablas del esquema `nonce_pano_24`:

```
nonce_pano_24 (esquema)
├── ini_gral_24          # Inicial General
├── ini_ind_24           # Inicial Indígena
├── ini_comuni_24        # Inicial Comunitaria
├── ini_ne_24            # Inicial No Escolarizada
├── pree_gral_24         # Preescolar General
├── pree_ind_24          # Preescolar Indígena
├── pree_comuni_24       # Preescolar Comunitaria
├── prim_gral_24         # Primaria General
├── prim_ind_24          # Primaria Indígena
├── prim_comuni_24       # Primaria Comunitaria
├── sec_gral_24          # Secundaria General (incluye Telesecundaria y Técnica)
├── sec_comuni_24        # Secundaria Comunitaria
├── ms_gral_24           # Media Superior General
├── ms_tecno_24          # Media Superior Tecnológica
├── ms_plantel_24        # Media Superior - Datos de Plantel
├── sup_carrera_24       # Superior - Carreras
├── sup_posgrado_24      # Superior - Posgrados
├── sup_escuela_24       # Superior - Datos de Escuela
├── sup_unidades_24      # Superior - Unidades Académicas Distribuidas
├── esp_cam_24           # Educación Especial (CAM)
└── esp_usaer_24         # Educación Especial (USAER)
```

**Columnas importantes** (prefijo V seguido de número):

- `VXX`: Variables numéricas del formato 911 de la SEP
- `cv_cct`: Clave del Centro de Trabajo (identificador único de escuela)
- `cv_estatus_captura`: Estado del registro (0 y 10 = válidos)
- `c_nom_mun`: Nombre del municipio
- `control`: Tipo de control (PÚBLICO, PRIVADO, etc.)
- `turno`: Turno escolar (MATUTINO, VESPERTINO, TIEMPO COMPLETO)

---

## 4. Módulos Principales del Sistema

### 📱 A. home.php - Centro de Navegación

**Propósito**: Página principal del sistema, muestra todos los municipios de Querétaro con estadísticas resumidas.

**Funcionalidades**:
- ✅ Grid de 18 municipios con datos resumidos
- ✅ Estadísticas estatales agregadas
- ✅ Búsqueda y filtrado de municipios
- ✅ Selección de hasta 3 municipios para comparación
- ✅ Botón flotante de comparación
- ✅ Animaciones suaves de entrada

**Código clave**:
```php
// Obtener todos los municipios
$todosLosMunicipios = obtenerMunicipiosPrueba2024();

// Obtener datos estatales completos
$datosEstado = obtenerResumenEstadoCompleto();

// Para cada municipio, obtener datos
foreach ($primerosCuatroMunicipios as $municipio) {
    $datosMunicipio = obtenerDatosMunicipio($municipio);
    // Renderizar tarjeta con datos
}
```

### 📊 B. resumen.php - Dashboard Municipal

**Propósito**: Dashboard completo de estadísticas educativas por municipio.

**Secciones**:

1. **Resumen Ejecutivo**:
   - Total de escuelas
   - Total de matrícula
   - Total de docentes

2. **Tabla y Gráfica por Nivel Educativo**:
   - Visualización interactiva
   - Cambio entre gráfica de columnas, barras y pastel
   - Exportación a Excel y PDF

3. **Desglose Detallado**:
   - Tarjetas por nivel educativo
   - Datos desglosados por público/privado
   - Información por sexo

4. **Porcentajes Municipales**:
   - % que representa cada nivel del total municipal

5. **Sección USAER**:
   - Datos especiales de apoyo educativo

**Código clave**:
```php
// Obtener municipio desde URL
$municipioSeleccionado = $_GET['municipio'] ?? 'QUERÉTARO';

// Obtener datos completos
$datosCompletosMunicipio = obtenerResumenMunicipioCompleto($municipioSeleccionado);

// Procesar para visualización
if ($tieneDatos) {
    // Preparar arrays para Google Charts
    $datosEducativos = [
        ['Tipo Educativo', 'Escuelas', 'Alumnos']
    ];

    // Agregar cada nivel con datos
    if ($inicialEscMat > 0) {
        $datosEducativos[] = [
            'Inicial (Escolarizado)',
            $datosMunicipio['inicial_esc']['tot_esc'],
            $inicialEscMat
        ];
    }
    // ... más niveles
}
```

### 👨‍🎓 C. alumnos.php - Análisis de Estudiantes

**Propósito**: Análisis detallado de la matrícula estudiantil por nivel educativo.

**Funcionalidades**:
- Desglose por nivel educativo
- Distribución por sexo
- Gráficas comparativas
- Análisis de público vs privado

### 🏫 D. escuelas_detalle.php - Análisis de Escuelas

**Propósito**: Vista detallada de centros educativos.

**Funcionalidades**:
- Conteo de escuelas por nivel
- Distribución público/privado
- Análisis por localidad

### 👩‍🏫 E. docentes.php - Análisis de Personal Docente

**Propósito**: Estadísticas del personal docente.

**Funcionalidades**:
- Distribución por nivel educativo
- Análisis por sexo
- Comparativas público/privado

### 🗺️ F. mapas.php - Visualización Geográfica

**Propósito**: Mapas interactivos con distribución de escuelas.

**Funcionalidades**:
- Integración con Google Maps
- Marcadores por tipo de escuela
- Filtros por nivel educativo
- Vista de calor de concentración

### 🔍 G. directorio_estatal.php - Búsqueda de Escuelas

**Propósito**: Directorio completo de todas las escuelas del estado.

**Funcionalidades**:
- Búsqueda por CCT (Clave de Centro de Trabajo)
- Búsqueda por nombre
- Filtros por nivel, municipio, turno
- Exportación de resultados
- Paginación de resultados

### 📈 H. comparacion_municipios.php - Comparativa

**Propósito**: Comparar hasta 3 municipios lado a lado.

**Funcionalidades**:
- Selección de 2-3 municipios desde home.php
- Tablas comparativas
- Gráficas de barras paralelas
- Análisis de diferencias porcentuales

### 📚 I. historicos.php - Datos Históricos

**Propósito**: Análisis de tendencias a lo largo de ciclos escolares.

**Funcionalidades**:
- Comparación entre ciclos
- Gráficas de evolución temporal
- Análisis de crecimiento/decrecimiento

---

## 5. Base de Datos y Estructura

### 🗄️ PostgreSQL - Configuración

```ini
Host: localhost
Puerto: 5433 (no estándar, PostgreSQL secundario)
Base de Datos: bd_nonce
Esquema Principal: nonce_pano_24
Usuario: postgres
Password: postgres
Encoding: UTF-8
```

### 📊 Estructura de Tablas

Cada tabla sigue el patrón: `[nivel]_[tipo]_[ciclo]`

Ejemplo: `prim_gral_24` = Primaria General, Ciclo 2024-2025

**Campos comunes** en todas las tablas:

```sql
cv_cct              VARCHAR(10)   -- Clave del Centro de Trabajo (ID único)
nombrect            VARCHAR(200)  -- Nombre de la escuela
c_nom_mun           VARCHAR(50)   -- Nombre del municipio
c_nom_loc           VARCHAR(100)  -- Nombre de la localidad
control             VARCHAR(20)   -- PÚBLICO / PRIVADO / etc.
turno               VARCHAR(20)   -- MATUTINO / VESPERTINO / NOCTURNO / TIEMPO COMPLETO
cv_estatus_captura  INTEGER       -- Estado: 0 o 10 = válido, otros = inválido
cv_motivo           VARCHAR(2)    -- Motivo de baja (superior)
```

**Campos de datos (formato 911 SEP)**:

Las columnas VXX contienen datos estadísticos específicos:

```sql
-- Ejemplo: Primaria General
V608     INTEGER   -- Total de matrícula
V562     INTEGER   -- Hombres 1° grado
V573     INTEGER   -- Hombres 2° grado
V585     INTEGER   -- Mujeres 1° grado
V596     INTEGER   -- Mujeres 2° grado
V616     INTEGER   -- Total de grupos
V1575    INTEGER   -- Docentes hombres
V1576    INTEGER   -- Docentes mujeres
```

### 🔑 Lógica de Conteo Especial

#### Media Superior

```sql
-- En Media Superior se cuenta por PLANTEL, no por escuela
COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas
```

**Razón**: Un plantel puede tener varios turnos, cada uno cuenta como una "escuela"

#### Superior

```sql
-- En Superior se cuenta por INSTITUCIÓN
COUNT(cct_ins_pla) AS escuelas
```

**Razón**: Las instituciones tienen múltiples campus pero se cuentan una vez

**Problema de unidades estatales**:
- Universidad Pedagógica Nacional (22MSU0090J)
- Tecnológico Nacional de México (22MSU0024K)

Estas instituciones tienen:
- Sede central en Querétaro (municipio 14)
- Unidades académicas en otros municipios

**Solución implementada**:
- **Querétaro**: Se **RESTAN** las unidades estatales de la sede central
- **Otros municipios**: Se **SUMAN** las unidades que operan localmente

```php
if (strcmp($municipio, '14') == 0) {
    // Querétaro: RESTAR unidades
    $matricula_ajustada = $matricula_base - $matricula_unidades_estatales;
} else {
    // Otros municipios: SUMAR unidades
    $matricula_ajustada = $matricula_base + $matricula_unidades_municipales;
}
```

### 📈 Filtros de Validación

Todos los queries incluyen filtros para garantizar calidad de datos:

```sql
-- Educación Básica (Inicial, Preescolar, Primaria, Secundaria)
WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

-- Media Superior y Superior
WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2')

-- Educación Especial
WHERE cv_estatus_captura = 0
```

---

## 6. Archivos Auxiliares Importantes

### 🔐 A. session_helper.php - Gestión de Sesiones

**Propósito**: Centralizar la lógica de autenticación y sesiones.

**Características**:
- Modo producción con autenticación
- **Modo demo** para demostraciones
- **Bypass temporal** para desarrollo (ELIMINAR EN PRODUCCIÓN)

```php
function iniciarSesionDemo($requireAuth = true) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // BYPASS TEMPORAL - ELIMINAR EN PRODUCCIÓN
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'dev@sedeq.local';
        $_SESSION['fullname'] = 'Usuario Desarrollo';
        $_SESSION['role'] = 'Desarrollador';
        $_SESSION['bypass_mode'] = true;
    }

    /* CÓDIGO ORIGINAL COMENTADO
    if ($requireAuth && !isset($_SESSION['user_id']) && !isset($_GET['demo'])) {
        header("Location: login.php");
        exit;
    }
    */
}
```

⚠️ **IMPORTANTE**: El bypass debe ser eliminado antes de desplegar a producción.

### 🔑 B. login.php y process_login.php - Autenticación

**login.php**: Formulario de inicio de sesión con:
- Campos de usuario y contraseña
- Integración con reCAPTCHA de Google
- Opción "Recordar credenciales"
- Credenciales demo visibles: `practicas25.dppee@gmail.com / Balluff254`

**process_login.php**: Procesa la autenticación:
- Validación de credenciales
- Verificación de reCAPTCHA
- Creación de sesión
- Redirección a home.php

### 📄 C. bolsillo.php y bolsillo_unidades.php - Archivos Legacy

Estos archivos son **versiones anteriores** del sistema de conexión:

**bolsillo (1) (1).php**:
- Versión previa con encoding LATIN1
- Mismo sistema de consultas pero menos robusto
- Se mantiene para comparación y migración

**bolsillo_unidades.php**:
- Lógica original de manejo de unidades estatales
- Implementa `acum_unidades()` función
- Migrada a `conexion_prueba_2024.php`

**Diferencias principales**:
```php
// bolsillo.php (antiguo)
$link_conexion = pg_connect("... options='--client_encoding=LATIN1'");

// conexion_prueba_2024.php (nuevo)
$connectionString = "... options='--client_encoding=UTF8'";
```

---

## 7. Frontend y Visualización

### 🎨 A. Sistema de Estilos CSS Modular

El sistema usa CSS modularizado para mejor mantenibilidad:

```
css/
├── global.css              # Estilos globales, variables CSS, reset
├── home.css                # Página principal
├── resumen.css             # Dashboard de resumen
├── sidebar.css             # Menú lateral
├── login.css               # Página de login
├── alumnos.css             # Módulo de alumnos
├── docentes.css            # Módulo de docentes
├── escuelas_detalle.css    # Módulo de escuelas
├── mapas.css               # Módulo de mapas
├── historicos.css          # Módulo históricos
├── estudiantes.css         # Análisis de estudiantes
└── settings.css            # Configuración
```

**Variables CSS globales** (en `global.css`):

```css
:root {
    /* Colores institucionales SEDEQ */
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;

    /* Colores de estado */
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;

    /* Tipografía */
    --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    --font-size-base: 16px;

    /* Espaciado */
    --spacing-unit: 8px;
    --border-radius: 8px;

    /* Sombras */
    --shadow-light: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-medium: 0 4px 8px rgba(0,0,0,0.15);
    --shadow-heavy: 0 8px 16px rgba(0,0,0,0.2);
}
```

### 📊 B. Google Charts - Visualización de Datos

El sistema utiliza **Google Charts** para visualizaciones:

```javascript
// Cargar biblioteca
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    // Datos desde PHP
    var data = google.visualization.arrayToDataTable([
        ['Nivel Educativo', 'Escuelas', 'Alumnos'],
        ['Inicial', 45, 1234],
        ['Preescolar', 120, 5678],
        ['Primaria', 200, 15000]
    ]);

    // Opciones de visualización
    var options = {
        title: 'Estadísticas por Nivel Educativo',
        hAxis: {title: 'Nivel'},
        vAxis: {title: 'Cantidad'},
        seriesType: 'bars',
        series: {
            0: {color: '#3498db'},
            1: {color: '#e74c3c'}
        }
    };

    // Renderizar
    var chart = new google.visualization.ColumnChart(
        document.getElementById('chart_div')
    );
    chart.draw(data, options);
}
```

**Tipos de gráficas utilizadas**:
- ColumnChart (columnas verticales)
- BarChart (barras horizontales)
- PieChart (gráfica de pastel)
- LineChart (líneas - para históricos)

### 📦 C. Sistema de Exportación

El sistema soporta exportación a múltiples formatos:

#### Excel (XLSX)

```javascript
// Utiliza SheetJS (xlsx.js)
function exportToExcel() {
    // Obtener datos de la tabla
    var table = document.getElementById('dataTable');

    // Convertir a worksheet
    var ws = XLSX.utils.table_to_sheet(table);

    // Crear workbook
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Datos");

    // Descargar
    XLSX.writeFile(wb, `estadisticas_${municipio}_${fecha}.xlsx`);
}
```

#### PDF

```javascript
// Utiliza jsPDF + autoTable
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Título
    doc.setFontSize(16);
    doc.text('Estadísticas Educativas - ' + municipio, 14, 20);

    // Tabla
    doc.autoTable({
        html: '#dataTable',
        startY: 30,
        theme: 'grid',
        styles: {fontSize: 8}
    });

    // Descargar
    doc.save(`reporte_${municipio}.pdf`);
}
```

#### Captura de Gráficas (PNG)

```javascript
// Utiliza html2canvas
function exportChartAsPNG() {
    html2canvas(document.getElementById('chart_div')).then(canvas => {
        // Crear enlace de descarga
        var link = document.createElement('a');
        link.download = `grafica_${municipio}.png`;
        link.href = canvas.toDataURL();
        link.click();
    });
}
```

### 🎭 D. Animaciones y Efectos

El archivo `js/animations_global.js` proporciona animaciones suaves:

```javascript
// Observer para animaciones de entrada
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observar elementos con clase 'animate-fade'
document.querySelectorAll('.animate-fade, .animate-up, .animate-scale')
    .forEach(el => observer.observe(el));
```

**Clases de animación disponibles**:
- `.animate-fade` - Aparición gradual (opacity)
- `.animate-up` - Deslizamiento desde abajo
- `.animate-scale` - Escalado desde pequeño
- `.delay-1, .delay-2, .delay-3` - Delays escalonados

### 🔧 E. Menú Lateral Responsive (sidebar.js)

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    // Toggle menú en móviles
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    // Cerrar al hacer click en overlay
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Manejo de submenús
    document.querySelectorAll('.has-submenu').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu = item.nextElementSibling;
            submenu.classList.toggle('active');
        });
    });
});
```

---

## 8. Seguridad y Autenticación

### 🔒 A. Sistema de Sesiones

El sistema implementa control de acceso basado en sesiones PHP:

```php
// Verificación en cada página protegida
require_once 'session_helper.php';
iniciarSesionDemo();

// La sesión debe contener:
$_SESSION['user_id']      // ID del usuario
$_SESSION['username']     // Email/usuario
$_SESSION['fullname']     // Nombre completo
$_SESSION['role']         // Rol (Admin, Visualizador, etc.)
$_SESSION['login_time']   // Timestamp de login
```

### 🛡️ B. Validación de Entradas

**Sanitización de parámetros**:

```php
// Municipio desde URL
$municipioSeleccionado = isset($_GET['municipio'])
    ? strtoupper(trim($_GET['municipio']))
    : 'QUERÉTARO';

// Validar contra lista blanca
$municipiosValidos = obtenerMunicipiosPrueba2024();
if (!in_array($municipioSeleccionado, $municipiosValidos)) {
    $municipioSeleccionado = 'QUERÉTARO'; // Fallback seguro
}

// Escapar para SQL
$municipio_escapado = pg_escape_string($conn, $municipioSeleccionado);
```

**Protección contra SQL Injection**:

1. Uso de `pg_escape_string()` para todos los inputs
2. Consultas parametrizadas donde es posible
3. Validación de tipos de datos

### 🔐 C. reCAPTCHA en Login

```html
<!-- Widget de reCAPTCHA v2 -->
<div class="g-recaptcha" data-sitekey="6LfWfvwrAAAAAJPFlchZmy2JQl28qwFU7veRahpI"></div>
```

```php
// Verificación en process_login.php
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
$secret_key = "TU_SECRET_KEY_AQUI";

$verify = file_get_contents(
    "https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$recaptcha_response}"
);
$captcha_success = json_decode($verify);

if (!$captcha_success->success) {
    die("Verificación de CAPTCHA fallida");
}
```

### ⚠️ D. Problemas de Seguridad Identificados

#### 1. Bypass de Login Activo

```php
// ❌ CÓDIGO INSEGURO - ELIMINAR EN PRODUCCIÓN
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'dev@sedeq.local';
    $_SESSION['bypass_mode'] = true;
}
```

**Riesgo**: Cualquiera puede acceder sin autenticación.

**Solución**: Descomentar validación original:
```php
// ✅ CÓDIGO SEGURO
if ($requireAuth && !isset($_SESSION['user_id']) && !isset($_GET['demo'])) {
    header("Location: login.php");
    exit;
}
```

#### 2. Credenciales en Código

```php
// ❌ Hardcoded en conexion_prueba_2024.php
$connectionString = "host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres";
```

**Riesgo**: Contraseñas expuestas en repositorio.

**Solución**: Usar archivo de configuración externo:
```php
// config.php (fuera del repositorio)
return [
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_port' => getenv('DB_PORT') ?: '5433',
    'db_name' => getenv('DB_NAME') ?: 'bd_nonce',
    'db_user' => getenv('DB_USER') ?: 'postgres',
    'db_pass' => getenv('DB_PASS') ?: 'postgres'
];
```

#### 3. Falta de HTTPS

El sistema debería forzar HTTPS en producción:

```php
// Al inicio de cada página
if ($_SERVER['HTTPS'] != 'on' && !$_SERVER['REQUEST_URI'] == '/health') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
```

---

## 9. Análisis de Dependencias

### 📚 A. Librerías JavaScript

```json
{
  "dependencies": {
    "google-charts": "latest (CDN)",
    "font-awesome": "6.0.0 (CDN)",
    "xlsx": "0.18.5 (CDN - SheetJS)",
    "jspdf": "2.5.1 (CDN)",
    "jspdf-autotable": "3.5.25 (CDN)",
    "html2canvas": "1.4.1 (CDN)"
  }
}
```

**Todas las librerías se cargan desde CDN**, no hay gestión de paquetes npm/composer.

### 🐘 B. Extensiones PHP Requeridas

```ini
; Requeridas
extension=pgsql        ; Soporte PostgreSQL
extension=pg_pgsql     ; Driver PostgreSQL avanzado
extension=mbstring     ; Manejo de strings multibyte (UTF-8)
extension=json         ; Procesamiento JSON

; Opcionales
extension=gd           ; Procesamiento de imágenes
extension=curl         ; Peticiones HTTP (reCAPTCHA)
```

### 🗄️ C. Base de Datos

```
PostgreSQL 12+ (recomendado)
- Puerto: 5433
- Encoding: UTF-8
- Locale: es_MX.UTF-8
```

---

## 10. Recomendaciones y Mejoras

### 🚀 A. Mejoras de Rendimiento

#### 1. Implementar Caché

```php
// Ejemplo con APCu o Memcached
function obtenerResumenMunicipioCompleto_cached($municipio) {
    $cache_key = "resumen_municipio_{$municipio}_" . obtenerCicloEscolarActual();

    // Intentar obtener de caché
    if (extension_loaded('apcu')) {
        $cached = apcu_fetch($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }

    // Si no está en caché, calcular
    $datos = obtenerResumenMunicipioCompleto($municipio);

    // Guardar en caché por 1 hora
    if (extension_loaded('apcu') && $datos) {
        apcu_store($cache_key, $datos, 3600);
    }

    return $datos;
}
```

**Beneficios**:
- ⚡ Reducción de 90% en tiempo de respuesta
- 📉 Menor carga en PostgreSQL
- 🎯 Mejor experiencia de usuario

#### 2. Conexiones Persistentes

```php
function ConectarsePrueba() {
    static $persistent_conn = null;

    if ($persistent_conn && pg_connection_status($persistent_conn) === PGSQL_CONNECTION_OK) {
        return $persistent_conn;
    }

    $connectionString = "...";
    $persistent_conn = pg_pconnect($connectionString); // Persistente

    return $persistent_conn;
}
```

#### 3. Lazy Loading de Gráficas

```javascript
// Cargar gráficas solo cuando sean visibles
const chartObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            loadChart(entry.target.id);
            chartObserver.unobserve(entry.target);
        }
    });
});

document.querySelectorAll('.chart-container').forEach(chart => {
    chartObserver.observe(chart);
});
```

### 🔒 B. Mejoras de Seguridad

#### 1. Prepared Statements

```php
// ❌ Actual (vulnerable)
$query = "SELECT * FROM tabla WHERE municipio='$municipio'";

// ✅ Recomendado
$query = "SELECT * FROM tabla WHERE municipio=$1";
$result = pg_query_params($conn, $query, array($municipio));
```

#### 2. Content Security Policy

```php
// Agregar header en todas las páginas
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.gstatic.com https://www.google.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com;");
```

#### 3. Rate Limiting para Login

```php
// Limitar intentos de login
$max_attempts = 5;
$lockout_time = 300; // 5 minutos

$attempts_key = "login_attempts_" . $_SERVER['REMOTE_ADDR'];
$attempts = apcu_fetch($attempts_key) ?: 0;

if ($attempts >= $max_attempts) {
    die("Demasiados intentos. Espere 5 minutos.");
}

// Si falla login
apcu_store($attempts_key, $attempts + 1, $lockout_time);
```

### 📊 C. Mejoras de Arquitectura

#### 1. Separar Lógica de Presentación (MVC)

```
Corregidora/
├── controllers/          # Lógica de negocio
│   ├── ResumenController.php
│   ├── AlumnosController.php
│   └── ...
├── models/              # Acceso a datos
│   ├── MunicipioModel.php
│   ├── EstadisticasModel.php
│   └── ...
├── views/               # Presentación
│   ├── resumen.php
│   ├── alumnos.php
│   └── ...
└── core/                # Framework básico
    ├── Router.php
    ├── Database.php
    └── ...
```

#### 2. API REST para Datos

```php
// api/municipios.php
header('Content-Type: application/json');

$municipio = $_GET['municipio'] ?? null;

if (!$municipio) {
    http_response_code(400);
    echo json_encode(['error' => 'Municipio requerido']);
    exit;
}

$datos = obtenerResumenMunicipioCompleto($municipio);

echo json_encode([
    'success' => true,
    'data' => $datos,
    'timestamp' => time()
]);
```

**Beneficios**:
- 🔄 Reutilización de datos
- 📱 Posibilidad de app móvil
- 🧪 Facilita testing

#### 3. Migrar a Framework Moderno

Considerar migración a:
- **Laravel** (PHP): Framework robusto y moderno
- **Symfony** (PHP): Altamente modular
- **React + Node.js**: Stack JavaScript completo

### 📱 D. Mejoras de UX/UI

#### 1. Progressive Web App (PWA)

```javascript
// service-worker.js
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open('sedeq-v1').then((cache) => {
            return cache.addAll([
                '/',
                '/css/global.css',
                '/js/sidebar.js',
                '/img/layout_set_logo.png'
            ]);
        })
    );
});
```

#### 2. Modo Offline

```javascript
// Detectar conexión
window.addEventListener('online', () => {
    showNotification('Conexión restaurada', 'success');
    syncPendingData();
});

window.addEventListener('offline', () => {
    showNotification('Sin conexión. Datos en caché disponibles.', 'warning');
});
```

#### 3. Dashboard Personalizable

```javascript
// Permitir a usuarios reordenar widgets
const dashboard = new Sortable(document.getElementById('dashboard-grid'), {
    animation: 150,
    onEnd: function(evt) {
        // Guardar layout en localStorage
        saveLayoutPreference();
    }
});
```

### 📈 E. Monitoreo y Analytics

#### 1. Error Logging Estructurado

```php
function logError($message, $context = []) {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => 'ERROR',
        'message' => $message,
        'context' => $context,
        'user' => $_SESSION['user_id'] ?? 'guest',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'url' => $_SERVER['REQUEST_URI']
    ];

    error_log(json_encode($log, JSON_UNESCAPED_UNICODE), 3, 'logs/app.log');
}
```

#### 2. Performance Monitoring

```php
// Medir tiempo de ejecución de consultas
function executeTimedQuery($conn, $query) {
    $start = microtime(true);
    $result = pg_query($conn, $query);
    $duration = microtime(true) - $start;

    if ($duration > 1.0) { // Más de 1 segundo
        logError("Consulta lenta detectada", [
            'query' => substr($query, 0, 100),
            'duration' => $duration
        ]);
    }

    return $result;
}
```

#### 3. Google Analytics

```html
<!-- Agregar en todas las páginas -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-XXXXXXXX-X"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-XXXXXXXX-X');
</script>
```

---

## 📝 Conclusiones

### ✅ Fortalezas del Sistema

1. **Arquitectura Funcional**:
   - Sistema completo y funcional
   - Cobertura de todos los niveles educativos
   - Múltiples vistas y análisis

2. **Conexión Robusta**:
   - `conexion_prueba_2024.php` bien estructurado
   - Manejo de casos especiales (unidades estatales)
   - Sistema de consultas extenso

3. **Experiencia de Usuario**:
   - Interfaz limpia y moderna
   - Gráficas interactivas
   - Exportación múltiple
   - Responsive design

4. **Flexibilidad**:
   - Fácil cambio de ciclo escolar
   - Módulos independientes
   - Posibilidad de extensión

### ⚠️ Áreas de Mejora Críticas

1. **Seguridad**:
   - ❗ Eliminar bypass de login
   - ❗ Externalizar credenciales
   - ❗ Implementar HTTPS

2. **Rendimiento**:
   - ⚡ Implementar caché
   - ⚡ Optimizar consultas pesadas
   - ⚡ Lazy loading

3. **Mantenibilidad**:
   - 🔧 Separar lógica de presentación
   - 🔧 Documentación técnica
   - 🔧 Tests unitarios

4. **Escalabilidad**:
   - 📈 API REST
   - 📈 Microservicios
   - 📈 Load balancing

### 🎯 Roadmap Sugerido

**Fase 1 (Inmediato - 1 mes)**:
- Eliminar bypass de login
- Implementar caché básico
- Externalizar configuración

**Fase 2 (Corto plazo - 3 meses)**:
- API REST
- Refactorizar a MVC
- Tests unitarios

**Fase 3 (Mediano plazo - 6 meses)**:
- PWA
- Dashboard personalizable
- Migración a framework moderno

**Fase 4 (Largo plazo - 12 meses)**:
- App móvil nativa
- Machine Learning para predicciones
- Integración con otros sistemas SEP

---

## 📞 Contacto y Soporte

**Desarrollado para**:
Secretaría de Educación del Estado de Querétaro (SEDEQ)

**Versión del Sistema**: 2.0.0
**Ciclo Escolar**: 2024-2025
**Última Actualización**: Enero 2025

---

**Fin del Documento de Revisión**

*Este documento fue generado mediante análisis exhaustivo del código fuente del sistema SEDEQ.*
