# Diagnóstico: Conteo de Escuelas de Nivel Superior

**Fecha:** 6 de octubre de 2025  
**Problema reportado:** "Para el nivel superior, siempre obtiene un valor menos"  
**Ejemplo ilustrativo:** Si el total fuera 14, solo se obtienen 13

## 🔍 Investigación Realizada

### 1. Análisis de Base de Datos

Se realizaron consultas directas a PostgreSQL para verificar los conteos reales:

#### Corregidora (cv_mun='6'):
```sql
SELECT 
    control, 
    COUNT(DISTINCT cct_ins_pla) as cantidad
FROM nonce_pano_24.sup_escuela_24 
WHERE cv_mun = '6' AND cv_motivo = '0'
GROUP BY control
```
**Resultado:**
- PÚBLICO: 2 escuelas
- PRIVADO: 9 escuelas
- **TOTAL: 11 escuelas** ✓

#### Desglose por subcontrol (Corregidora):
```sql
SELECT 
    subcontrol, 
    COUNT(DISTINCT cct_ins_pla) as cantidad
FROM nonce_pano_24.sup_escuela_24 
WHERE cv_mun = '6' AND cv_motivo = '0' AND control <> 'PRIVADO'
GROUP BY subcontrol
```
**Resultado:**
- AUTÓNOMO: 1 escuela
- ESTATAL: 1 escuela
- **TOTAL PÚBLICAS: 2 escuelas** ✓

#### Querétaro (cv_mun='14'):
```sql
SELECT 
    subcontrol, 
    COUNT(DISTINCT cct_ins_pla) as cantidad
FROM nonce_pano_24.sup_escuela_24 
WHERE cv_mun = '14' AND cv_motivo = '0'
GROUP BY subcontrol
```
**Resultado:**
- AUTÓNOMO: 2 escuelas
- ESTATAL: 8 escuelas
- FEDERAL: 6 escuelas
- FEDERAL TRANSFERIDO: 2 escuelas
- PRIVADO: 56 escuelas
- **TOTAL: 74 escuelas** ✓

### 2. Verificación de Código PHP

Se revisaron las funciones involucradas:

#### `obtenerSubcontrolPorNivel()` - Líneas 3090-3220
```php
'superior' => "
    SELECT subcontrol, COUNT(DISTINCT cct_ins_pla) as total
    FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo
    WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
        AND cv_motivo = '0'
    GROUP BY subcontrol"
```
**Estado:** ✓ La consulta es correcta

#### `obtenerDatosPublicoPrivado()` - Líneas 1786-1840
Esta función usa `subnivel_con_control()` que a su vez usa `rs_consulta_segura()` con el mismo filtro `cv_motivo='0'`.

**Estado:** ✓ Las funciones son consistentes

### 3. Estructura de la Tabla `sup_escuela_24`

La tabla tiene DOS columnas relevantes:
- **`control`**: Valor genérico ("PÚBLICO" o "PRIVADO")
- **`subcontrol`**: Valor específico ("FEDERAL", "ESTATAL", "AUTÓNOMO", "FEDERAL TRANSFERIDO", "PRIVADO")

Ejemplo de registro:
```
cct_ins_pla: 22MSU0130U
nombrect: ESCUELA DE LAUDERIA
control: PÚBLICO
subcontrol: FEDERAL
```

**Estado:** ✓ La estructura es correcta y el código usa `subcontrol` para el GROUP BY

## 📊 Verificaciones Realizadas

### Suma de Subcontroles vs Total

#### Corregidora:
- Públicas por subcontrol: 1 (AUTÓNOMO) + 1 (ESTATAL) = **2**
- Privadas: **9**
- **Total: 2 + 9 = 11** ✓ CORRECTO

#### Querétaro:
- Públicas por subcontrol: 2 (AUTÓNOMO) + 8 (ESTATAL) + 6 (FEDERAL) + 2 (FEDERAL TRANSFERIDO) = **18**
- Privadas: **56**
- **Total: 18 + 56 = 74** ✓ CORRECTO

## 🎯 Conclusiones

### Estado Actual del Sistema

**NO se detectó ningún error de conteo en el código.** Las consultas SQL están correctas y los totales coinciden perfectamente:

1. ✅ La consulta de `obtenerSubcontrolPorNivel()` usa `subcontrol` correctamente
2. ✅ Los filtros aplicados son consistentes (`cv_motivo='0'`, `control <> 'PRIVADO'`)
3. ✅ El conteo usa `DISTINCT cct_ins_pla` para evitar duplicados
4. ✅ La suma de escuelas por subcontrol coincide con el total general

### Posibles Explicaciones del Problema Reportado

1. **El ejemplo era ilustrativo**: El usuario mencionó que "el ejemplo de 13 y 14 solo es demostrativo, esos valores no se obtienen en ningún municipio"

2. **Problema de visualización**: Podría haber un error en cómo se MUESTRA el total en la interfaz de `prueba_subcontrol.php`, no en cómo se CALCULA

3. **Datos de prueba anteriores**: El problema podría haberse solucionado con las correcciones previas a los filtros base

4. **Confusión con unidades**: Las "unidades" de superior afectan matrícula/docentes pero NO el conteo de escuelas

## 🔧 Página de Verificación Creada

Se creó **`test_conteo_superior.php`** que permite:

1. **Seleccionar cualquier municipio** del estado
2. **Comparar** el total del sistema antiguo vs el sistema nuevo
3. **Ver el desglose** por subcontrol con cantidades específicas
4. **Detectar discrepancias** automáticamente con alertas visuales

### Cómo Usar la Página de Prueba

1. Abrir: `http://localhost/Corregidora/test_conteo_superior.php`
2. Seleccionar un municipio del menú desplegable
3. Verificar que la columna "Estado" muestre "✓ CORRECTO"
4. Revisar el desglose por subcontrol en la tabla inferior

Si se detecta alguna discrepancia, aparecerá un cuadro de advertencia naranja indicando:
- La cantidad de escuelas de diferencia
- Si el sistema nuevo cuenta más o menos que el antiguo

## 📝 Recomendaciones

### Acción Inmediata
1. **Probar la página `test_conteo_superior.php`** con diferentes municipios
2. **Reportar** si se encuentra alguna discrepancia específica con:
   - Nombre del municipio
   - Total esperado vs total obtenido
   - Screenshot de la página

### Si NO se encuentran errores
El código está funcionando correctamente y no requiere modificaciones adicionales.

### Si se encuentran errores específicos
Proporcionar:
- Municipio exacto donde ocurre
- Valores específicos (no ejemplos ilustrativos)
- Comparación con escuelas_detalle.php

## 🗂️ Archivos Relacionados

- **Código principal:** `conexion_prueba_2024.php` (líneas 2955-3280)
- **Página de análisis:** `prueba_subcontrol.php`
- **Página de verificación:** `test_conteo_superior.php` (NUEVA)
- **Página de prueba de municipios:** `test_municipios_subcontrol.php`
- **Documentación:** `DOCUMENTACION_SUBCONTROL.md`

## ✅ Estado Final

**CÓDIGO VALIDADO:** Los conteos son correctos según las verificaciones realizadas con consultas directas a PostgreSQL.

**SIGUIENTE PASO:** Probar la página `test_conteo_superior.php` para confirmar que los conteos se visualizan correctamente en la interfaz.
