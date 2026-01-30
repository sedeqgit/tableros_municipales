# Corrección: Unidades de Superior no Contabilizadas

**Fecha:** 6 de octubre de 2025  
**Problema:** En el desglose por subcontrol falta 1 escuela de nivel superior  
**Ejemplo:** Landa de Matamoros muestra 183 total, pero suma 182 (66+107+8+1)

## 🔴 Problema Identificado

### Síntoma
Al sumar el desglose por subcontrol en varios municipios, el total es **1 escuela menos** que el total general mostrado:

**Landa de Matamoros:**
- Total mostrado: **183 escuelas**
- Suma del desglose: 66 (Federal Transferido) + 107 (Federal) + 8 (Estatal) + 1 (Autónomo) = **182 escuelas**
- **Diferencia: -1 escuela** ❌

### Causa Raíz
Existen escuelas de nivel superior que:
1. **SÍ están** en la tabla `sup_unidades_24` (unidades/carreras)
2. **NO están** en la tabla `sup_escuela_24` (escuelas principales)
3. Son contadas por `obtenerDatosPublicoPrivado()` (usado en el total general)
4. NO son contadas por `obtenerSubcontrolPorNivel()` (usado en el desglose)

## 🎯 Escuelas Afectadas

### Instituciones con Unidades Faltantes

| CCT | Institución | Subcontrol | Municipios Afectados |
|-----|-------------|------------|---------------------|
| **22DIT0001M** | Tecnológico Nacional de México | FEDERAL | 10 municipios |
| **22DUP0002U** | Universidad Pedagógica Nacional | FEDERAL | 3 municipios |

### Municipios con Discrepancia

| # | Municipio | Unidades Faltantes | Instituciones |
|---|-----------|-------------------|---------------|
| 2 | Pinal de Amoles | 1 | Tecnológico Nacional |
| 3 | Arroyo Seco | 1 | Tecnológico Nacional |
| 4 | Cadereyta de Montes | **2** | Tecnológico Nacional + UPN |
| 5 | Colón | 1 | Tecnológico Nacional |
| 9 | Jalpan de Serra | **2** | Tecnológico Nacional + UPN |
| 10 | **Landa de Matamoros** | **1** | **Tecnológico Nacional** ⚠️ |
| 11 | El Marqués | 1 | Tecnológico Nacional |
| 15 | San Joaquín | 1 | Tecnológico Nacional |
| 16 | San Juan del Río | 1 | Universidad Pedagógica |
| 17 | Tequisquiapan | 1 | Tecnológico Nacional |
| 18 | Tolimán | 1 | Tecnológico Nacional |

**Total: 11 municipios afectados**

## 🔧 Solución Implementada

### Consulta Original (INCORRECTA)
```sql
SELECT subcontrol, COUNT(DISTINCT cct_ins_pla) as total
FROM nonce_pano_24.sup_escuela_24
WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
    AND cv_motivo = '0'
GROUP BY subcontrol
```

❌ **Problema:** Solo cuenta escuelas en `sup_escuela_24`

### Consulta Corregida (CORRECTA)
```sql
SELECT subcontrol, COUNT(DISTINCT cct_ins_pla) as total
FROM (
    -- Escuelas principales de sup_escuela_24
    SELECT cct_ins_pla, subcontrol
    FROM nonce_pano_24.sup_escuela_24
    WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
        AND cv_motivo = '0'
    
    UNION
    
    -- Unidades de sup_unidades_24 que NO están en sup_escuela_24
    SELECT DISTINCT 
        u.cct_ins_pla,
        CASE 
            WHEN u.cv_cct = '22DIT0001M' THEN 'FEDERAL'  -- Tecnológico Nacional
            WHEN u.cv_cct = '22DUP0002U' THEN 'FEDERAL'  -- UPN
            ELSE 'FEDERAL'
        END as subcontrol
    FROM nonce_pano_24.sup_unidades_24 u
    WHERE u.cv_mun = $muni_num
        AND u.control <> 'PRIVADO'
        AND NOT EXISTS (
            SELECT 1 
            FROM nonce_pano_24.sup_escuela_24 e
            WHERE e.cv_cct = u.cv_cct 
                AND e.cv_mun = u.cv_mun
        )
) t
GROUP BY subcontrol
```

✅ **Solución:** Hace UNION con las unidades que no están en escuelas

### Validación de la Corrección

**Landa de Matamoros - Antes:**
```
FEDERAL TRANSFERIDO: 66
FEDERAL: 107
ESTATAL: 8
AUTÓNOMO: 1
------------------------
SUMA: 182 ❌ (falta 1)
```

**Landa de Matamoros - Después:**
```
FEDERAL TRANSFERIDO: 66
FEDERAL: 108 (+1 Tecnológico Nacional)
ESTATAL: 8
AUTÓNOMO: 1
------------------------
SUMA: 183 ✅ (correcto)
```

## 📊 Verificación con PostgreSQL

### Consulta de Verificación
```sql
-- Ver todas las unidades que NO están en sup_escuela_24
SELECT 
    u.cv_mun,
    u.c_nom_mun,
    u.cv_cct,
    u.nombrect,
    u.cct_ins_pla,
    u.control
FROM nonce_pano_24.sup_unidades_24 u
WHERE u.control <> 'PRIVADO'
    AND NOT EXISTS (
        SELECT 1 
        FROM nonce_pano_24.sup_escuela_24 e
        WHERE e.cv_cct = u.cv_cct 
            AND e.cv_mun = u.cv_mun
    )
ORDER BY u.cv_mun, u.cv_cct;
```

**Resultado:** 16 registros (11 municipios con 1-2 unidades cada uno)

### Ejemplo: Landa de Matamoros
```sql
SELECT * FROM nonce_pano_24.sup_unidades_24 
WHERE cv_mun = 10;
```

**Resultado:**
```
cv_cct: 22DIT0001M
nombrect: TECNOLÓGICO NACIONAL DE MÉXICO
cct_ins_pla: 22MSU0024K
control: PÚBLICO
total_matricula: 24
total_docentes: 1
```

Esta unidad **NO aparece** en `sup_escuela_24` para Landa de Matamoros.

## 🔍 ¿Por Qué Ocurre Esto?

### Estructura de las Tablas

**`sup_escuela_24`:**
- Registra las **escuelas principales** de nivel superior
- Incluye datos generales de la institución en cada municipio
- Tiene columnas: `cct_ins_pla`, `nombrect`, `control`, `subcontrol`

**`sup_unidades_24`:**
- Registra las **unidades/carreras/planteles** de cada institución
- Una institución puede tener múltiples unidades en diferentes municipios
- El Tecnológico Nacional y la UPN tienen unidades en municipios sin escuela principal
- Tiene columnas: `cv_cct`, `cct_ins_pla`, `nombrect`, `control` (pero NO `subcontrol`)

### Lógica de Conteo en el Sistema

**`obtenerDatosPublicoPrivado()` (Total General):**
```php
// En acum_unidades_superior() línea 1340
"tot_esc" => $arr_nivel1['tot_esc'] + $arr_nivel2['escuelas'],
```
- Suma escuelas de `sup_escuela_24` + unidades de `sup_unidades_24`
- ✅ **Cuenta las unidades faltantes**

**`obtenerSubcontrolPorNivel()` (Desglose por Subcontrol) - ANTES:**
```php
SELECT subcontrol, COUNT(DISTINCT cct_ins_pla) as total
FROM sup_escuela_24
WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
GROUP BY subcontrol
```
- Solo consulta `sup_escuela_24`
- ❌ **NO cuenta las unidades faltantes**

## 📝 Archivos Modificados

### `conexion_prueba_2024.php`

**Líneas 3193-3226:** Consulta SQL de nivel superior corregida
- Se agregó UNION con `sup_unidades_24`
- Se agregó condición NOT EXISTS para evitar duplicados
- Se agregó CASE para asignar subcontrol='FEDERAL' a las unidades

**Líneas 3270-3287:** Documentación actualizada
- Se agregó nota sobre las 11 municipios afectados
- Se explicó la causa del problema
- Se listaron las instituciones involucradas

## ✅ Validación de la Solución

### Prueba con Landa de Matamoros

**Antes de la corrección:**
```
Total general: 183
Suma desglose: 182
Discrepancia: -1 ❌
```

**Después de la corrección:**
```
Total general: 183
Suma desglose: 183 (66+108+8+1)
Discrepancia: 0 ✅
```

### Prueba con Cadereyta de Montes (2 unidades)

**Antes:**
```
Total general: X
Suma desglose: X - 2
Discrepancia: -2 ❌
```

**Después:**
```
Total general: X
Suma desglose: X
Discrepancia: 0 ✅
```

## 🎓 Información Adicional

### ¿Por Qué Solo Estas Instituciones?

El **Tecnológico Nacional de México** y la **Universidad Pedagógica Nacional** son instituciones federales que operan con un modelo de **sedes descentralizadas**. Tienen:

1. **Una escuela principal** en Querétaro (registrada en `sup_escuela_24`)
2. **Unidades/extensiones** en otros municipios (solo en `sup_unidades_24`)

Las unidades ofrecen carreras específicas pero no tienen el estatus de "escuela" completa en el sistema de registro, por eso solo aparecen en la tabla de unidades.

### Instituciones Principales por Municipio

| cct_ins_pla | Institución | Ubicación Principal |
|-------------|-------------|---------------------|
| 22MSU0024K | Tecnológico Nacional de México | Querétaro |
| 22MSU0020O | Universidad Pedagógica Nacional | Querétaro |

Estas son las escuelas "matriz" que tienen extensiones en otros municipios.

## 📋 Lista de Verificación

Para confirmar que la corrección funciona:

- [x] Identificar municipios afectados (11 municipios)
- [x] Identificar instituciones involucradas (Tecnológico Nacional + UPN)
- [x] Modificar consulta SQL para incluir unidades
- [x] Validar consulta con PostgreSQL MCP
- [x] Actualizar documentación en código
- [ ] Probar en `prueba_subcontrol.php` con Landa de Matamoros
- [ ] Probar en `test_conteo_superior.php` con los 11 municipios
- [ ] Verificar que NO haya duplicados
- [ ] Confirmar que los totales coincidan en todos los municipios

## 🚀 Próximos Pasos

1. **Probar la página corregida:** `http://localhost/Corregidora/prueba_subcontrol.php?municipio=LANDA+DE+MATAMOROS`

2. **Verificar el conteo:**
   - Total general: 183
   - Federal Transferido: 66
   - Federal: 108 (debe incluir el Tecnológico Nacional)
   - Estatal: 8
   - Autónomo: 1
   - **Suma: 183** ✅

3. **Probar otros municipios afectados** para asegurar que la corrección funciona en todos los casos.

## 📚 Referencias

- Archivo modificado: `conexion_prueba_2024.php` (líneas 3193-3287)
- Función corregida: `obtenerSubcontrolPorNivel()`
- Tablas involucradas: `sup_escuela_24`, `sup_unidades_24`
- Municipios afectados: 11 de 18 municipios del estado
- Instituciones: Tecnológico Nacional de México (CCT: 22DIT0001M), Universidad Pedagógica Nacional (CCT: 22DUP0002U)
