-- CONSULTA PARA OBTENER ESCUELAS POR CONTROL (PÚBLICO/PRIVADO) Y TIPO EDUCATIVO
-- Ciclo 2023-2024, Municipio de Corregidora - VERSIÓN CORREGIDA
-- Basada en consulta unificada sin tablas auxiliares
WITH datos_por_control AS (
    -- INICIAL ESCOLARIZADO GENERAL
    SELECT 'Inicial (Escolarizado)' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(V398 + V414), 0) as alumnos
    FROM nonce_pano_23.ini_gral_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- INICIAL ESCOLARIZADO INDÍGENA
    SELECT 'Inicial (Escolarizado)' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(V183 + V184), 0) as alumnos
    FROM nonce_pano_23.ini_ind_23
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- INICIAL NO ESCOLARIZADO GENERAL
    SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(V129 + V130), 0) as alumnos
    FROM nonce_pano_23.ini_ne_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- INICIAL NO ESCOLARIZADO COMUNITARIO
    SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(V79 + V80), 0) as alumnos
    FROM nonce_pano_23.ini_comuni_23
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- ESPECIAL (CAM)
    SELECT 'Especial (CAM)' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(
            SUM(
                v1392 + v1393 + v1467 + v1468 + v1560 + v1561 + v1635 + v1636 + v1814 + v1815 + v1940 + v1941
            ),
            0
        ) as alumnos
    FROM nonce_pano_23.esp_cam_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- PREESCOLAR GENERAL
    SELECT 'Preescolar' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v177), 0) as alumnos
    FROM nonce_pano_23.pree_gral_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- PREESCOLAR INDÍGENA
    SELECT 'Preescolar' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v177), 0) as alumnos
    FROM nonce_pano_23.pree_ind_23
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- PREESCOLAR COMUNITARIO
    SELECT 'Preescolar' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v97), 0) as alumnos
    FROM nonce_pano_23.pree_comuni_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- PRIMARIA GENERAL
    SELECT 'Primaria' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v608), 0) as alumnos
    FROM nonce_pano_23.prim_gral_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- PRIMARIA INDÍGENA
    SELECT 'Primaria' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v610), 0) as alumnos
    FROM nonce_pano_23.prim_ind_23
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- PRIMARIA COMUNITARIA
    SELECT 'Primaria' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v515), 0) as alumnos
    FROM nonce_pano_23.prim_comuni_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- SECUNDARIA GENERAL
    SELECT 'Secundaria' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v340), 0) as alumnos
    FROM nonce_pano_23.sec_gral_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- SECUNDARIA COMUNITARIA
    SELECT 'Secundaria' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v257), 0) as alumnos
    FROM nonce_pano_23.sec_comuni_23
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- MEDIA SUPERIOR GENERAL
    SELECT 'Media Superior' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v397), 0) as alumnos
    FROM nonce_pano_23.ms_gral_23
    WHERE cv_motivo = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- MEDIA SUPERIOR TECNOLÓGICO
    SELECT 'Media Superior' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v472), 0) as alumnos
    FROM nonce_pano_23.ms_tecno_23
    WHERE cv_motivo = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- SUPERIOR CARRERA
    SELECT 'Superior' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v177), 0) as alumnos
    FROM nonce_pano_23.sup_carrera_23
    WHERE cv_motivo = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
    UNION ALL
    -- SUPERIOR POSGRADO
    SELECT 'Superior' as tipo_educativo,
        CASE
            WHEN control ILIKE '%PRIVADO%' THEN 'Privado'
            ELSE 'Público'
        END as sostenimiento,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v142), 0) as alumnos
    FROM nonce_pano_23.sup_posgrado_23
    WHERE cv_motivo = 0
        AND c_nom_mun = 'CORREGIDORA'
    GROUP BY control
) -- RESULTADO FINAL EN FORMATO PIVOT (COLUMNAS PARA PÚBLICO/PRIVADO)
SELECT tipo_educativo,
    SUM(
        CASE
            WHEN sostenimiento = 'Público' THEN escuelas
            ELSE 0
        END
    ) as escuelas_publicas,
    SUM(
        CASE
            WHEN sostenimiento = 'Privado' THEN escuelas
            ELSE 0
        END
    ) as escuelas_privadas,
    SUM(escuelas) as total_escuelas,
    SUM(
        CASE
            WHEN sostenimiento = 'Público' THEN alumnos
            ELSE 0
        END
    ) as alumnos_publicos,
    SUM(
        CASE
            WHEN sostenimiento = 'Privado' THEN alumnos
            ELSE 0
        END
    ) as alumnos_privados,
    SUM(alumnos) as total_alumnos
FROM datos_por_control
GROUP BY tipo_educativo
ORDER BY CASE
        WHEN tipo_educativo = 'Inicial (Escolarizado)' THEN 1
        WHEN tipo_educativo = 'Inicial (No Escolarizado)' THEN 2
        WHEN tipo_educativo = 'Especial (CAM)' THEN 3
        WHEN tipo_educativo = 'Preescolar' THEN 4
        WHEN tipo_educativo = 'Primaria' THEN 5
        WHEN tipo_educativo = 'Secundaria' THEN 6
        WHEN tipo_educativo = 'Media Superior' THEN 7
        WHEN tipo_educativo = 'Superior' THEN 8
        ELSE 9
    END;