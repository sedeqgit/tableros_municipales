-- =====================================================================================
-- CONSULTAS SQL PARA ALUMNOS CON DISCAPACIDADES EN EL MUNICIPIO DE CORREGIDORA
-- Ciclo Escolar 2024-2025 (Esquema: nonce_pano_24)
-- Basado en análisis de archivos legacy y campos validados
-- =====================================================================================
-- =====================================================================================
-- 1. PRIMARIA - Alumnos con discapacidad
-- =====================================================================================
SELECT 'PRIMARIA' as nivel_educativo,
    'GENERAL' as subnivel,
    CASE
        WHEN control = 'PÚBLICO' THEN 'PUBLICO'
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(v1083) as total_discapacidad,
    SUM(v1081) as hombres_discapacidad,
    SUM(v1082) as mujeres_discapacidad
FROM nonce_pano_24.prim_gral_24
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control
UNION ALL
SELECT 'PRIMARIA' as nivel_educativo,
    'INDIGENA' as subnivel,
    'PUBLICO' as control,
    SUM(v1016) as total_discapacidad,
    SUM(v1014) as hombres_discapacidad,
    SUM(v1015) as mujeres_discapacidad
FROM nonce_pano_24.prim_ind_24
WHERE cv_estatus_captura = 0
    AND c_nom_mun ILIKE '%CORREGIDORA%'
UNION ALL
SELECT 'PRIMARIA' as nivel_educativo,
    'COMUNITARIA' as subnivel,
    'PUBLICO' as control,
    SUM(v582) as total_discapacidad,
    SUM(v580) as hombres_discapacidad,
    SUM(v581) as mujeres_discapacidad
FROM nonce_pano_24.prim_comuni_24
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND c_nom_mun ILIKE '%CORREGIDORA%' -- =====================================================================================
    -- 2. PREESCOLAR - Alumnos con discapacidad
    -- =====================================================================================
UNION ALL
SELECT 'PREESCOLAR' as nivel_educativo,
    'GENERAL' as subnivel,
    CASE
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(v423) as total_discapacidad,
    SUM(v421) as hombres_discapacidad,
    SUM(v422) as mujeres_discapacidad
FROM nonce_pano_24.pree_gral_24
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control
UNION ALL
SELECT 'PREESCOLAR' as nivel_educativo,
    'INDIGENA' as subnivel,
    'PUBLICO' as control,
    SUM(v386) as total_discapacidad,
    SUM(v384) as hombres_discapacidad,
    SUM(v385) as mujeres_discapacidad
FROM nonce_pano_24.pree_ind_24
WHERE cv_estatus_captura = 0
    AND c_nom_mun ILIKE '%CORREGIDORA%'
UNION ALL
SELECT 'PREESCOLAR' as nivel_educativo,
    'COMUNITARIA' as subnivel,
    'PUBLICO' as control,
    SUM(v148) as total_discapacidad,
    SUM(v146) as hombres_discapacidad,
    SUM(v147) as mujeres_discapacidad
FROM nonce_pano_24.pree_comuni_24
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND c_nom_mun ILIKE '%CORREGIDORA%' -- =====================================================================================
    -- 3. SECUNDARIA - Alumnos con discapacidad
    -- =====================================================================================
UNION ALL
SELECT 'SECUNDARIA' as nivel_educativo,
    'GENERAL' as subnivel,
    CASE
        WHEN control = 'PÚBLICO' THEN 'PUBLICO'
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(v582) as total_discapacidad,
    SUM(v580) as hombres_discapacidad,
    SUM(v581) as mujeres_discapacidad
FROM nonce_pano_24.sec_gral_24
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control
UNION ALL
SELECT 'SECUNDARIA' as nivel_educativo,
    'COMUNITARIA' as subnivel,
    'PUBLICO' as control,
    SUM(v311) as total_discapacidad,
    SUM(v309) as hombres_discapacidad,
    SUM(v310) as mujeres_discapacidad
FROM nonce_pano_24.sec_comuni_24
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND c_nom_mun ILIKE '%CORREGIDORA%' -- =====================================================================================
    -- 4. MEDIA SUPERIOR - Alumnos con discapacidad
    -- =====================================================================================
UNION ALL
SELECT 'MEDIA SUPERIOR' as nivel_educativo,
    'GENERAL' as subnivel,
    CASE
        WHEN control = 'PÚBLICO' THEN 'PUBLICO'
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(v939) as total_discapacidad,
    SUM(v937) as hombres_discapacidad,
    SUM(v938) as mujeres_discapacidad
FROM nonce_pano_24.ms_gral_24
WHERE c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control
UNION ALL
SELECT 'MEDIA SUPERIOR' as nivel_educativo,
    'TECNOLOGICO' as subnivel,
    CASE
        WHEN control = 'PÚBLICO' THEN 'PUBLICO'
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(v1038) as total_discapacidad,
    SUM(v1036) as hombres_discapacidad,
    SUM(v1037) as mujeres_discapacidad
FROM nonce_pano_24.ms_tecno_24
WHERE c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control -- =====================================================================================
    -- 5. SUPERIOR - Alumnos con discapacidad
    -- =====================================================================================
UNION ALL
SELECT 'SUPERIOR' as nivel_educativo,
    'LICENCIATURA' as subnivel,
    CASE
        WHEN control = 'PÚBLICO' THEN 'PUBLICO'
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(v337) as total_discapacidad,
    SUM(v335) as hombres_discapacidad,
    SUM(v336) as mujeres_discapacidad
FROM nonce_pano_24.sup_carrera_24
WHERE cv_motivo = 0
    AND c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control
UNION ALL
SELECT 'SUPERIOR' as nivel_educativo,
    'POSGRADO' as subnivel,
    CASE
        WHEN control = 'PÚBLICO' THEN 'PUBLICO'
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(v183) as total_discapacidad,
    SUM(v181) as hombres_discapacidad,
    SUM(v182) as mujeres_discapacidad
FROM nonce_pano_24.sup_posgrado_24
WHERE cv_motivo = 0
    AND c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control -- =====================================================================================
    -- 6. ESPECIAL CAM - Alumnos con discapacidad (usando campos específicos solicitados)
    -- =====================================================================================
UNION ALL
SELECT 'ESPECIAL CAM' as nivel_educativo,
    'CAM' as subnivel,
    CASE
        WHEN control = 'P?BLICO' THEN 'PUBLICO'
        ELSE control
    END as control,
    SUM(
        v1392 + v1467 + v1560 + v1635 + v1814 + v1940 + v1393 + v1468 + v1561 + v1635 + v1815 + v1941
    ) as total_discapacidad,
    SUM(v1392 + v1467 + v1560 + v1635 + v1814 + v1940) as hombres_discapacidad,
    SUM(v1393 + v1468 + v1561 + v1635 + v1815 + v1941) as mujeres_discapacidad
FROM nonce_pano_24.esp_cam_24
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND c_nom_mun ILIKE '%CORREGIDORA%'
GROUP BY control
ORDER BY nivel_educativo,
    subnivel,
    control;
-- =====================================================================================
-- CONSULTA CONSOLIDADA POR NIVEL EDUCATIVO
-- =====================================================================================
-- Resumen por nivel educativo
SELECT nivel_educativo,
    COUNT(DISTINCT subnivel) as modalidades,
    SUM(
        CASE
            WHEN control = 'PUBLICO' THEN total_discapacidad
            ELSE 0
        END
    ) as publico_discapacidad,
    SUM(
        CASE
            WHEN control = 'PRIVADO' THEN total_discapacidad
            ELSE 0
        END
    ) as privado_discapacidad,
    SUM(total_discapacidad) as total_discapacidad,
    SUM(hombres_discapacidad) as total_hombres,
    SUM(mujeres_discapacidad) as total_mujeres
FROM (
        -- Subquery con todos los datos de discapacidad...
        -- [Aquí iría la consulta completa anterior]
    ) as discapacidades
GROUP BY nivel_educativo
ORDER BY nivel_educativo;
-- =====================================================================================
-- NOTAS IMPORTANTES:
-- =====================================================================================
-- 1. CAMPOS UTILIZADOS POR NIVEL:
--    - PRIMARIA: v1083 (total), v1081 (hombres), v1082 (mujeres) - General
--                v1016 (total), v1014 (hombres), v1015 (mujeres) - Indígena  
--                v582 (total), v580 (hombres), v581 (mujeres) - Comunitaria
--    - PREESCOLAR: v423 (total), v421 (hombres), v422 (mujeres) - General
--                  v386 (total), v384 (hombres), v385 (mujeres) - Indígena
--                  v148 (total), v146 (hombres), v147 (mujeres) - Comunitaria
--    - SECUNDARIA: v582 (total), v580 (hombres), v581 (mujeres) - General
--                  v311 (total), v309 (hombres), v310 (mujeres) - Comunitaria
--    - MEDIA SUPERIOR: v939 (total), v937 (hombres), v938 (mujeres) - General
--                      v1038 (total), v1036 (hombres), v1037 (mujeres) - Tecnológico
--    - SUPERIOR: v337 (total), v335 (hombres), v336 (mujeres) - Licenciatura
--                v183 (total), v181 (hombres), v182 (mujeres) - Posgrado
--    - CAM: Campos específicos solicitados para hombres y mujeres
--
-- 2. FUENTES DE VALIDACIÓN:
--    - Archivos legacy: primaria.php, preescolar.php, secundaria.php, 
--      media_sup.php, superior.php
--    - Campos verificados contra esquema nonce_pano_24
--
-- 3. FILTROS APLICADOS:
--    - Primaria/Preescolar: (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
--    - Superior: cv_motivo = 0
--    - Todos: c_nom_mun ILIKE '%CORREGIDORA%'
--
-- =====================================================================================