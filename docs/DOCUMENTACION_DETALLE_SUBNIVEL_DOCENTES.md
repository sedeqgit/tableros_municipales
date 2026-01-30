# 📚 Documentación: Tabla de Detalle por Subnivel Educativo (Docentes)

## 📋 Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Backend (PHP)](#backend-php)
4. [Frontend (HTML)](#frontend-html)
5. [Flujo de Datos](#flujo-de-datos)
6. [Guía de Implementación para Alumnos](#guía-de-implementación-para-alumnos)

---

## 🎯 Visión General

El sistema de **Tabla de Detalle por Subnivel Educativo** muestra un desglose completo de docentes organizado por:

- **Nivel educativo** (Inicial, Preescolar, Primaria, etc.)
- **Subnivel educativo** (General, Comunitario, Indígena, Técnica, etc.)
- **Género** (Hombres y Mujeres con porcentajes)
- **Porcentaje del total general**

### 🌟 Características Principales

- ✅ Consulta SQL optimizada con UNION ALL para múltiples tablas
- ✅ Normalización de datos directamente en SQL
- ✅ Desglose completo por nivel y subnivel
- ✅ Cálculos automáticos de porcentajes por género
- ✅ Ordenamiento personalizado de niveles educativos
- ✅ Tabla HTML con totales generales en footer
- ✅ Datos organizados jerárquicamente
- ✅ Filas clickeables para interactividad

---

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    ARQUITECTURA GENERAL                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐      ┌──────────────┐      ┌───────────┐  │
│  │   Base de    │ ───> │   Backend    │ ───> │ Frontend  │  │
│  │    Datos     │      │    (PHP)     │      │(HTML/JS)  │  │
│  │  PostgreSQL  │      │              │      │           │  │
│  └──────────────┘      └──────────────┘      └───────────┘  │
│         │                      │                    │       │
│         │                      │                    │       │
│    [Múltiples                [Procesa              [Muestra │
│     Tablas]                  y Agrupa]             Datos]   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 Backend (PHP)

### 1. Función Principal: `obtenerDocentesPorNivelYSubnivel()`

**Ubicación:** `conexion_prueba_2024.php` (líneas ~2317-2655)

**Propósito:** Obtener todos los docentes agrupados por nivel y subnivel educativo con desglose por género.

#### 📝 Firma de la Función
```php
function obtenerDocentesPorNivelYSubnivel($municipio = 'CORREGIDORA', $ini_ciclo = null)
```

#### 📊 Estructura de Retorno
```php
[
    [
        'nivel' => 'Preescolar',
        'subnivel' => 'General',
        'total_docentes' => 1624,
        'doc_hombres' => 15,
        'doc_mujeres' => 1609,
        'escuelas' => 390
    ],
    [
        'nivel' => 'Preescolar',
        'subnivel' => 'Comunitario',
        'total_docentes' => 27,
        'doc_hombres' => 0,
        'doc_mujeres' => 27,
        'escuelas' => 22
    ],
    // ... más registros
]
```

### 2. Consulta SQL Completa

La función construye una consulta SQL masiva usando **UNION ALL** para combinar datos de múltiples tablas:

#### 🗃️ Tablas Consultadas

```sql
-- INICIAL ESCOLARIZADA
├── ini_gral_24      (V509, V516, V523, V511, V518, V525, V510, V517, V524, V512, V519, V526, V787)
├── ini_ind_24       (V291, V289, V290)

-- INICIAL NO ESCOLARIZADA  
├── ini_comuni_24    (V126, V124, V125)
└── ini_ne_24        (V183, V184)

-- ESPECIAL CAM
└── esp_cam_24       (V2496, V2494, V2495)

-- PREESCOLAR
├── pree_gral_24     (V867, V868, V859, V860)
├── pree_ind_24      (V795, V803, V796, V804)
├── pree_comuni_24   (V151, V149, V150)
└── ini_gral_24      (V513, V520, V527, V514, V521, V528) <- Preescolar en tabla inicial

-- PRIMARIA
├── prim_gral_24     (V1575, V1576, V1567, V1568)
├── prim_ind_24      (V1507, V1499, V1508, V1500)
└── prim_comuni_24   (V583, V584)

-- SECUNDARIA
├── sec_gral_24      (V1401, V1297-V1314)
└── sec_comuni_24    (V386, V384, V385)

-- MEDIA SUPERIOR
└── ms_plantel_24    (V106, V101, V104, V99, V105, V100)

-- SUPERIOR
└── sup_escuela_24   (V83, V81, V82)
```

#### 🎯 Ejemplo de UNION para Preescolar

```sql
-- PREESCOLAR General
SELECT
    cv_cct as cct,
    'Preescolar' as nivel,
    'General' as subnivel,
    (V867+V868+V859+V860)::integer as total_docentes,
    (V867+V859)::integer as doc_hombres,
    (V868+V860)::integer as doc_mujeres
FROM nonce_pano_24.pree_gral_24
WHERE cv_mun = '14' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

UNION ALL

-- PREESCOLAR Indígena
SELECT
    cv_cct as cct,
    'Preescolar' as nivel,
    'Indígena' as subnivel,
    (V795+V803+V796+V804)::integer as total_docentes,
    (V795+V803)::integer as doc_hombres,
    (V796+V804)::integer as doc_mujeres
FROM nonce_pano_24.pree_ind_24
WHERE cv_mun = '14' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

UNION ALL

-- PREESCOLAR Comunitario
SELECT
    cv_cct as cct,
    'Preescolar' as nivel,
    'Comunitario' as subnivel,
    V151::integer as total_docentes,
    V149::integer as doc_hombres,
    V150::integer as doc_mujeres
FROM nonce_pano_24.pree_comuni_24
WHERE cv_mun = '14' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

UNION ALL

-- PREESCOLAR en tabla de Inicial (casos especiales)
SELECT
    cv_cct as cct,
    'Preescolar' as nivel,
    'General' as subnivel,
    (V513+V520+V527+V514+V521+V528)::integer as total_docentes,
    (V513+V520+V527)::integer as doc_hombres,
    (V514+V521+V528)::integer as doc_mujeres
FROM nonce_pano_24.ini_gral_24
WHERE cv_mun = '14' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
```

### 3. Normalización de Subniveles en SQL

La normalización se hace directamente en la consulta SQL usando **CASE**:

```sql
SELECT
    cv_cct as cct,
    'Preescolar' as nivel,
    CASE
        WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
        WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
        ELSE TRIM(subnivel)
    END as subnivel,
    -- ... columnas de docentes
```

### 4. Agrupación Final

```sql
-- Al final de todos los UNION ALL
WHERE total_docentes > 0
GROUP BY nivel, subnivel
ORDER BY
    CASE nivel
        WHEN 'Inicial Escolarizada' THEN 1
        WHEN 'Inicial No Escolarizada' THEN 2
        WHEN 'Especial Cam' THEN 3
        WHEN 'Preescolar' THEN 4
        WHEN 'Primaria' THEN 5
        WHEN 'Secundaria' THEN 6
        WHEN 'Media Superior' THEN 7
        WHEN 'Superior' THEN 8
    END,
    subnivel
```

### 5. Ajustes Post-Consulta

#### 🔧 Ajuste de Unidades Estatales (Superior)

```php
// Aplicar ajuste de unidades estatales para nivel Superior
// - Municipio 14 (Querétaro): RESTAR unidades (evitar doble conteo)
// - Otros municipios: SUMAR unidades (no están en sup_escuela_24)
$datos = aplicarAjusteUnidadesSuperior($link, $ini_ciclo, $codigo_municipio, $datos);
```

**Función:** `aplicarAjusteUnidadesSuperior()`

```php
if ($codigo_municipio == '14') {
    // CASO QUERÉTARO: RESTAR todas las unidades estatales
    foreach ($datos as $index => $fila) {
        if ($fila['nivel'] === 'Superior') {
            $datos[$index]['total_docentes'] = max(0, $fila['total_docentes'] - 74);
            $datos[$index]['doc_hombres'] = max(0, $fila['doc_hombres'] - 33);
            $datos[$index]['doc_mujeres'] = max(0, $fila['doc_mujeres'] - 41);
        }
    }
} else {
    // CASO OTROS MUNICIPIOS: SUMAR unidades del municipio
    // ...
}
```

### 6. Procesamiento en docentes.php

```php
// Obtener datos de docentes por nivel y subnivel
$datosDocentesPorSubnivel = obtenerDocentesPorNivelYSubnivel($municipioSeleccionado);

// Inicializar arrays
$datosDocentesGenero = array();
$datosDocentesGenero[] = array('Nivel Educativo', 'Subnivel', 'Total Docentes', 
                                'Hombres', 'Mujeres', '% Hombres', '% Mujeres');

$docentesPorNivel = array(); // Total por nivel principal
$totalDocentes = 0;

// Procesar cada registro
if ($datosDocentesPorSubnivel && is_array($datosDocentesPorSubnivel)) {
    foreach ($datosDocentesPorSubnivel as $fila) {
        $nivelPrincipal = $fila['nivel'];
        $nombreSubnivel = $fila['subnivel'];
        $docentes = intval($fila['total_docentes']);
        $docentesH = intval($fila['doc_hombres']);
        $docentesM = intval($fila['doc_mujeres']);

        // Calcular porcentajes de género
        $porcH = $docentes > 0 ? round(($docentesH / $docentes) * 100, 1) : 0;
        $porcM = $docentes > 0 ? round(($docentesM / $docentes) * 100, 1) : 0;

        // Agregar a datos de género
        $datosDocentesGenero[] = array(
            $nivelPrincipal, $nombreSubnivel, $docentes, 
            $docentesH, $docentesM, $porcH, $porcM
        );

        // Acumular por nivel principal
        if (!isset($docentesPorNivel[$nivelPrincipal])) {
            $docentesPorNivel[$nivelPrincipal] = 0;
        }
        $docentesPorNivel[$nivelPrincipal] += $docentes;
        $totalDocentes += $docentes;
    }
}
```

---

## 🎨 Frontend (HTML)

### 1. Tabla de Detalle por Subnivel

**Ubicación:** `docentes.php` (líneas ~408-570)

**Propósito:** Mostrar una tabla HTML con el desglose completo de docentes por nivel y subnivel educativo.

```html
<div id="tabla-detallada" class="detailed-table animate-fade delay-4">
    <h4>Detalle por Subnivel Educativo</h4>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nivel Educativo</th>
                    <th>Subnivel</th>
                    <th>Total Docentes</th>
                    <th>% del Total General</th>
                    <th>Docentes Hombres</th>
                    <th>% Hombres</th>
                    <th>Docentes Mujeres</th>
                    <th>% Mujeres</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Función de ordenamiento personalizado
                function obtenerOrdenSubnivel($nivel, $subnivel) {
                    // ... lógica de ordenamiento
                }

                // Crear array temporal para ordenar
                $datosOrdenados = array();
                for ($i = 1; $i < count($datosDocentesGenero); $i++) {
                    $datosOrdenados[] = array(
                        'nivel' => $datosDocentesGenero[$i][0],
                        'subnivel' => $datosDocentesGenero[$i][1],
                        'total' => $datosDocentesGenero[$i][2],
                        'hombres' => $datosDocentesGenero[$i][3],
                        'mujeres' => $datosDocentesGenero[$i][4],
                        'porcentaje_hombres' => $datosDocentesGenero[$i][5],
                        'porcentaje_mujeres' => $datosDocentesGenero[$i][6],
                        'orden' => obtenerOrdenSubnivel(
                            $datosDocentesGenero[$i][0], 
                            $datosDocentesGenero[$i][1]
                        )
                    );
                }

                // Ordenar por el campo orden
                usort($datosOrdenados, function ($a, $b) {
                    return $a['orden'] - $b['orden'];
                });

                // Mostrar datos ordenados
                foreach ($datosOrdenados as $fila):
                    $porcentajeDelTotal = round(($fila['total'] / $totalDocentes) * 100, 2);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nivel']); ?></td>
                    <td><?php echo htmlspecialchars($fila['subnivel']); ?></td>
                    <td class="text-center"><?php echo number_format($fila['total']); ?></td>
                    <td class="text-center"><?php echo $porcentajeDelTotal; ?>%</td>
                    <td class="text-center"><?php echo number_format($fila['hombres']); ?></td>
                    <td class="text-center"><?php echo $fila['porcentaje_hombres']; ?>%</td>
                    <td class="text-center"><?php echo number_format($fila['mujeres']); ?></td>
                    <td class="text-center"><?php echo $fila['porcentaje_mujeres']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                    <td class="text-center">
                        <strong><?php echo number_format($totalDocentes); ?></strong>
                    </td>
                    <td class="text-center"><strong>100.0%</strong></td>
                    <!-- ... totales de género -->
                </tr>
            </tfoot>
        </table>
    </div>
</div>
```

### 2. Función de Ordenamiento de Subniveles

```php
function obtenerOrdenSubnivel($nivel, $subnivel) {
    $nivel = strtolower($nivel);
    $subnivel = strtolower($subnivel);

    // INICIAL ESCOLARIZADA
    if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'escolarizada') !== false)
        return 1;

    // INICIAL NO ESCOLARIZADA
    if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'no') !== false)
        return 2;

    // ESPECIAL / CAM
    if (strpos($nivel, 'especial') !== false || strpos($nivel, 'cam') !== false)
        return 3;

    // PREESCOLAR
    if (strpos($nivel, 'preescolar') !== false) {
        if (strpos($subnivel, 'general') !== false) return 4;
        if (strpos($subnivel, 'comunitario') !== false) return 5;
        if (strpos($subnivel, 'indígena') !== false) return 6;
    }

    // PRIMARIA
    if (strpos($nivel, 'primaria') !== false) {
        if (strpos($subnivel, 'general') !== false) return 7;
        if (strpos($subnivel, 'comunitario') !== false) return 8;
        if (strpos($subnivel, 'indígena') !== false) return 9;
    }

    // SECUNDARIA
    if (strpos($nivel, 'secundaria') !== false) {
        if (strpos($subnivel, 'comunitario') !== false) return 10;
        if (strpos($subnivel, 'general') !== false) return 11;
        if (strpos($subnivel, 'técnica') !== false) return 12;
        if (strpos($subnivel, 'telesecundaria') !== false) return 13;
    }

    // MEDIA SUPERIOR
    if (strpos($nivel, 'media') !== false) return 14;

    // SUPERIOR
    if (strpos($nivel, 'superior') !== false) return 15;

    return 16; // No reconocidos
}
```

### 3. JavaScript para Interactividad

**Archivo:** `docentes.js`

#### 🎯 Inicialización

```javascript
document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeTooltips();
    initializeInteractiveElements();
    initializeSostenimientoFilters();
    initializeViewToggle();
    initializeGoogleCharts();
});
```

#### 📊 Sistema de Filtrado por Sostenimiento

```javascript
function initializeSostenimientoFilters() {
    // Almacenar valores originales
    const barrasNivel = document.querySelectorAll('.level-bar');
    
    barrasNivel.forEach(bar => {
        const nombreNivel = bar.querySelector('.level-name').textContent.trim();
        valoresOriginalesDocentes[nombreNivel] = {
            cantidad: bar.querySelector('.escuelas-count').textContent,
            porcentaje: bar.querySelector('.level-percent').textContent,
            ancho: bar.querySelector('.level-percent').textContent
        };
    });

    // Configurar botones de filtro
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter');
            aplicarFiltroDocentes(filterType);
        });
    });
}
```

#### 🔄 Aplicar Filtros

```javascript
function aplicarFiltroDocentes(tipo) {
    const barrasNivel = document.querySelectorAll('.level-bar');
    
    barrasNivel.forEach(bar => {
        const nombreNivel = bar.querySelector('.level-name').textContent.trim();
        
        if (tipo === 'total') {
            // Restaurar valores originales
            bar.querySelector('.escuelas-count').textContent = 
                valoresOriginalesDocentes[nombreNivel].cantidad;
        } else if (tipo === 'publico' || tipo === 'privado') {
            // Aplicar filtro
            const datosNivel = buscarDatosSostenimiento(nombreNivel);
            if (datosNivel) {
                const valor = tipo === 'publico' ? 
                    datosNivel.publicos : datosNivel.privados;
                bar.querySelector('.escuelas-count').textContent = valor;
            }
        }
    });

    // Redibujar gráfico si está visible
    if (document.getElementById('vista-grafico').style.display !== 'none') {
        drawDocentesNivelChart();
    }
}
```

#### 📈 Visualización con Google Charts

```javascript
function drawDocentesNivelChart() {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(function() {
        const datos = prepararDatosGraficoDocentes(window.tipoFiltroActual);
        const data = google.visualization.arrayToDataTable(datos);

        const options = {
            title: getTituloGraficoDocentes(window.tipoFiltroActual),
            pieHole: 0.4,
            colors: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
                     '#9b59b6', '#1abc9c', '#e67e22', '#34495e'],
            legend: { position: 'bottom' },
            chartArea: { width: '90%', height: '75%' },
            animation: { startup: true, duration: 1000 }
        };

        const chart = new google.visualization.PieChart(
            document.getElementById('pie-chart-nivel')
        );
        chart.draw(data, options);
    });
}
```

---

## 🔄 Flujo de Datos

```
┌─────────────────────────────────────────────────────────────────┐
│                     FLUJO COMPLETO DE DATOS                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  1️⃣ USUARIO ACCEDE                                               │
│     └─> docentes.php?municipio=QUERETARO                        │
│                                                                   │
│  2️⃣ BACKEND CONSULTA                                             │
│     └─> obtenerDocentesPorNivelYSubnivel('QUERETARO')           │
│         ├─> Construye consulta SQL con UNION ALL                │
│         ├─> Normaliza subniveles en SQL (CASE)                  │
│         ├─> Agrupa por nivel y subnivel (GROUP BY)              │
│         ├─> Ordena resultados (ORDER BY)                        │
│         └─> Aplica ajustes (Superior - unidades estatales)      │
│                                                                   │
│  3️⃣ PROCESAMIENTO PHP                                            │
│     └─> docentes.php                                             │
│         ├─> Recibe array de resultados                          │
│         ├─> Calcula porcentajes de género                       │
│         ├─> Acumula totales por nivel                           │
│         └─> Ordena para display (obtenerOrdenSubnivel)          │
│                                                                   │
│  4️⃣ RENDERIZADO HTML                                             │
│     └─> Genera tabla con:                                        │
│         ├─> Columnas: Nivel, Subnivel, Total, %, H, M           │
│         ├─> Filas ordenadas por jerarquía educativa             │
│         └─> Fila de totales en <tfoot>                          │
│                                                                   │
│  5️⃣ JAVASCRIPT INTERACTIVO                                       │
│     └─> docentes.js                                              │
│         ├─> Inicializa filtros de sostenimiento                 │
│         ├─> Guarda valores originales                           │
│         ├─> Aplica filtros al hacer clic                        │
│         ├─> Actualiza barras de progreso                        │
│         └─> Redibuja gráficos (Google Charts)                   │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🚀 Guía de Implementación para Alumnos

### Paso 1: Crear Función Backend

**Archivo:** `conexion_prueba_2024.php`

```php
/**
 * Obtiene datos de ALUMNOS agrupados por nivel y subnivel
 * 
 * @param string $municipio Nombre del municipio
 * @param string $ini_ciclo Ciclo escolar (opcional)
 * @return array Datos agrupados por nivel y subnivel
 */
function obtenerAlumnosPorNivelYSubnivel($municipio = 'CORREGIDORA', $ini_ciclo = null)
{
    if ($ini_ciclo === null) {
        $ini_ciclo = obtenerCicloEscolarActual();
    }

    $link = ConectarsePrueba();
    if (!$link) {
        return [];
    }

    $municipio = normalizarNombreMunicipio($municipio);
    $codigo_municipio = nombre_a_numero_municipio($municipio);

    if ($codigo_municipio === false) {
        pg_close($link);
        return [];
    }

    // Construir consulta SQL similar a docentes
    $query = "
    SELECT 
        nivel,
        subnivel,
        SUM(total_alumnos)::integer as total_alumnos,
        SUM(alumnos_hombres)::integer as alumnos_hombres,
        SUM(alumnos_mujeres)::integer as alumnos_mujeres,
        COUNT(DISTINCT cct)::integer as escuelas
    FROM (
        -- INICIAL ESCOLARIZADA (ini_gral_24)
        SELECT
            cv_cct as cct,
            'Inicial Escolarizada' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
                ELSE TRIM(subnivel)
            END as subnivel,
            (V398+V414)::integer as total_alumnos,
            (V390+V406)::integer as alumnos_hombres,
            (V394+V410)::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- PREESCOLAR (pree_gral_24)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            'General' as subnivel,
            V177::integer as total_alumnos,
            V165::integer as alumnos_hombres,
            V171::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        -- ... AGREGAR MÁS NIVELES AQUÍ
        
    ) AS todos_niveles
    WHERE total_alumnos > 0
    GROUP BY nivel, subnivel
    ORDER BY
        CASE nivel
            WHEN 'Inicial Escolarizada' THEN 1
            WHEN 'Inicial No Escolarizada' THEN 2
            WHEN 'Especial Cam' THEN 3
            WHEN 'Preescolar' THEN 4
            WHEN 'Primaria' THEN 5
            WHEN 'Secundaria' THEN 6
            WHEN 'Media Superior' THEN 7
            WHEN 'Superior' THEN 8
        END,
        subnivel";

    $result = pg_query($link, $query);

    if (!$result) {
        pg_close($link);
        return [];
    }

    $datos = [];
    while ($row = pg_fetch_assoc($result)) {
        $datos[] = $row;
    }

    pg_free_result($result);
    pg_close($link);

    return $datos;
}
```

### Paso 2: Procesar Datos en alumnos.php

```php
// Obtener datos de alumnos por nivel y subnivel
$datosAlumnosPorSubnivel = obtenerAlumnosPorNivelYSubnivel($municipioSeleccionado);

// Procesar datos
$datosAlumnosGenero = array();
$datosAlumnosGenero[] = array('Nivel Educativo', 'Subnivel', 'Total Alumnos', 
                               'Hombres', 'Mujeres', '% Hombres', '% Mujeres');

$alumnosPorNivel = array();
$totalAlumnos = 0;

if ($datosAlumnosPorSubnivel && is_array($datosAlumnosPorSubnivel)) {
    foreach ($datosAlumnosPorSubnivel as $fila) {
        $nivelPrincipal = $fila['nivel'];
        $nombreSubnivel = $fila['subnivel'];
        $alumnos = intval($fila['total_alumnos']);
        $alumnosH = intval($fila['alumnos_hombres']);
        $alumnosM = intval($fila['alumnos_mujeres']);

        // Calcular porcentajes
        $porcH = $alumnos > 0 ? round(($alumnosH / $alumnos) * 100, 1) : 0;
        $porcM = $alumnos > 0 ? round(($alumnosM / $alumnos) * 100, 1) : 0;

        $datosAlumnosGenero[] = array(
            $nivelPrincipal, $nombreSubnivel, $alumnos, 
            $alumnosH, $alumnosM, $porcH, $porcM
        );

        if (!isset($alumnosPorNivel[$nivelPrincipal])) {
            $alumnosPorNivel[$nivelPrincipal] = 0;
        }
        $alumnosPorNivel[$nivelPrincipal] += $alumnos;
        $totalAlumnos += $alumnos;
    }
}
```

### Paso 3: Crear Tabla HTML

```html
<div id="tabla-detallada-alumnos" class="detailed-table">
    <h4>Detalle por Subnivel Educativo - Alumnos</h4>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nivel Educativo</th>
                    <th>Subnivel</th>
                    <th>Total Alumnos</th>
                    <th>% del Total</th>
                    <th>Alumnos Hombres</th>
                    <th>% Hombres</th>
                    <th>Alumnas Mujeres</th>
                    <th>% Mujeres</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reutilizar función obtenerOrdenSubnivel
                $datosOrdenados = array();
                for ($i = 1; $i < count($datosAlumnosGenero); $i++) {
                    $datosOrdenados[] = array(
                        'nivel' => $datosAlumnosGenero[$i][0],
                        'subnivel' => $datosAlumnosGenero[$i][1],
                        'total' => $datosAlumnosGenero[$i][2],
                        'hombres' => $datosAlumnosGenero[$i][3],
                        'mujeres' => $datosAlumnosGenero[$i][4],
                        'porcentaje_hombres' => $datosAlumnosGenero[$i][5],
                        'porcentaje_mujeres' => $datosAlumnosGenero[$i][6],
                        'orden' => obtenerOrdenSubnivel(
                            $datosAlumnosGenero[$i][0], 
                            $datosAlumnosGenero[$i][1]
                        )
                    );
                }

                usort($datosOrdenados, function ($a, $b) {
                    return $a['orden'] - $b['orden'];
                });

                foreach ($datosOrdenados as $fila):
                    $porcentajeDelTotal = round(($fila['total'] / $totalAlumnos) * 100, 2);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nivel']); ?></td>
                    <td><?php echo htmlspecialchars($fila['subnivel']); ?></td>
                    <td class="text-center"><?php echo number_format($fila['total']); ?></td>
                    <td class="text-center"><?php echo $porcentajeDelTotal; ?>%</td>
                    <td class="text-center"><?php echo number_format($fila['hombres']); ?></td>
                    <td class="text-center"><?php echo $fila['porcentaje_hombres']; ?>%</td>
                    <td class="text-center"><?php echo number_format($fila['mujeres']); ?></td>
                    <td class="text-center"><?php echo $fila['porcentaje_mujeres']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                    <td class="text-center">
                        <strong><?php echo number_format($totalAlumnos); ?></strong>
                    </td>
                    <td class="text-center"><strong>100.0%</strong></td>
                    <?php
                    $totalHombres = 0;
                    $totalMujeres = 0;
                    for ($i = 1; $i < count($datosAlumnosGenero); $i++) {
                        $totalHombres += $datosAlumnosGenero[$i][3];
                        $totalMujeres += $datosAlumnosGenero[$i][4];
                    }
                    $porcentajeTotalH = round(($totalHombres / $totalAlumnos) * 100, 1);
                    $porcentajeTotalM = round(($totalMujeres / $totalAlumnos) * 100, 1);
                    ?>
                    <td class="text-center">
                        <strong><?php echo number_format($totalHombres); ?></strong>
                    </td>
                    <td class="text-center"><strong><?php echo $porcentajeTotalH; ?>%</strong></td>
                    <td class="text-center">
                        <strong><?php echo number_format($totalMujeres); ?></strong>
                    </td>
                    <td class="text-center"><strong><?php echo $porcentajeTotalM; ?>%</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
```

### Paso 4: Crear JavaScript (alumnos.js)

```javascript
// Similar a docentes.js pero adaptado para alumnos
document.addEventListener('DOMContentLoaded', function() {
    initializeAlumnosView();
});

function initializeAlumnosView() {
    // Inicializar filtros
    initializeSostenimientoFiltersAlumnos();
    
    // Inicializar gráficos
    initializeGoogleChartsAlumnos();
    
    // Guardar totales originales
    setTimeout(guardarTotalesOriginalesAlumnos, 500);
}

function aplicarFiltroAlumnos(tipo) {
    // Similar a aplicarFiltroDocentes pero con datos de alumnos
    // ...
}

function drawAlumnosNivelChart() {
    // Similar a drawDocentesNivelChart pero con datos de alumnos
    // ...
}
```

---

## 📌 Puntos Clave para Alumnos

### ✅ Diferencias Importantes

| Aspecto | Docentes | Alumnos |
|---------|----------|---------|
| **Columnas principales** | V509, V787, etc. | V398, V177, etc. |
| **Variable JavaScript** | `docentesPorNivel` | `alumnosPorNivel` |
| **Función backend** | `obtenerDocentesPorNivelYSubnivel()` | `obtenerAlumnosPorNivelYSubnivel()` |
| **Archivo JS** | `docentes.js` | `alumnos.js` |
| **Terminología** | Docentes/Maestros | Alumnos/Estudiantes |

### 🎯 Columnas de Base de Datos para Alumnos

```sql
-- INICIAL ESCOLARIZADA
V398, V414  -- Total alumnos
V390, V406  -- Hombres
V394, V410  -- Mujeres

-- PREESCOLAR
V177  -- Total
V165  -- Hombres
V171  -- Mujeres

-- PRIMARIA
V608, V610  -- Total
V562-V575   -- Hombres
V585-V598   -- Mujeres

-- SECUNDARIA
V726, V739  -- Total
V719, V732  -- Hombres
V722, V735  -- Mujeres

-- MEDIA SUPERIOR
V77, V50    -- Total
V75, V48    -- Hombres
V76, V49    -- Mujeres

-- SUPERIOR
V177, V142  -- Total (carrera + posgrado)
V175, V140  -- Hombres
V176, V141  -- Mujeres
```

### ⚠️ Consideraciones Especiales

1. **Ajustes de unidades estatales**: Aplican también para Superior
2. **Normalización de texto**: Usar mismas funciones CASE
3. **Ordenamiento**: Reutilizar función `obtenerOrdenSubnivel()`
4. **Filtros de sostenimiento**: Necesitas función `obtenerDatosPublicoPrivadoAlumnos()`

---

## 📚 Referencias

- **Archivo principal**: `docentes.php`
- **Backend**: `conexion_prueba_2024.php` (función línea ~2317)
- **JavaScript**: `js/docentes.js`
- **CSS**: `css/docentes.css`
- **Documentación relacionada**:
  - `CORRECCION-FINAL-CONTROL-FLUJO.md`
  - `MIGRACION_PROGRESO.md`

---

## 🎓 Conclusión

Este sistema proporciona un desglose completo y detallado de docentes por nivel y subnivel educativo, con capacidades de:
- ✅ Consulta optimizada con múltiples tablas
- ✅ Normalización automática de datos
- ✅ Visualización interactiva
- ✅ Filtrado por tipo de sostenimiento
- ✅ Exportación de datos
- ✅ Gráficos dinámicos

La implementación para alumnos sigue exactamente el mismo patrón, solo cambiando:
1. Los campos de la base de datos (columnas V)
2. Los nombres de variables y funciones
3. La terminología (docentes → alumnos)

**¡Buena suerte con la implementación! 🚀**
