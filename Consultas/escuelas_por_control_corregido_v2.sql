WITH datos_alumnos AS (
    -- INICIAL ESCOLARIZADO
    SELECT 'Inicial (Escolarizado)' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v390 + v406 + v394 + v410), 0) as alumnos
    FROM nonce_pano_24.ini_gral_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Inicial (Escolarizado)' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v183 + v184), 0) as alumnos
    FROM nonce_pano_24.ini_ind_24
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    -- INICIAL NO ESCOLARIZADO
    SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v129 + v130), 0) as alumnos
    FROM nonce_pano_24.ini_ne_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v79 + v80), 0) as alumnos
    FROM nonce_pano_24.ini_comuni_24
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    -- CAM (ESPECIAL)
    SELECT 'Especial (CAM)' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v2264), 0) as alumnos
    FROM nonce_pano_24.esp_cam_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    -- PREESCOLAR
    SELECT 'Preescolar' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v177), 0) as alumnos
    FROM nonce_pano_24.pree_gral_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Preescolar' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v177), 0) as alumnos
    FROM nonce_pano_24.pree_ind_24
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Preescolar' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v97), 0) as alumnos
    FROM nonce_pano_24.pree_comuni_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    -- PRIMARIA
    SELECT 'Primaria' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v608), 0) as alumnos
    FROM nonce_pano_24.prim_gral_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Primaria' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v610), 0) as alumnos
    FROM nonce_pano_24.prim_ind_24
    WHERE cv_estatus_captura = 0
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Primaria' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v515), 0) as alumnos
    FROM nonce_pano_24.prim_comuni_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    -- SECUNDARIA
    SELECT 'Secundaria' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v340), 0) as alumnos
    FROM nonce_pano_24.sec_gral_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Secundaria' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v257), 0) as alumnos
    FROM nonce_pano_24.sec_comuni_24
    WHERE (
            cv_estatus_captura = 0
            OR cv_estatus_captura = 10
        )
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    -- MEDIA SUPERIOR
    SELECT 'Media Superior' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v397), 0) as alumnos
    FROM nonce_pano_24.ms_gral_24
    WHERE c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Media Superior' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v472), 0) as alumnos
    FROM nonce_pano_24.ms_tecno_24
    WHERE c_nom_mun = 'CORREGIDORA'
    UNION ALL
    -- SUPERIOR
    SELECT 'Superior' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v177), 0) as alumnos
    FROM nonce_pano_24.sup_carrera_24
    WHERE cv_motivo = 0
        AND c_nom_mun = 'CORREGIDORA'
    UNION ALL
    SELECT 'Superior' as tipo_educativo,
        COUNT(DISTINCT cv_cct) as escuelas,
        COALESCE(SUM(v142), 0) as alumnos
    FROM nonce_pano_24.sup_posgrado_24
    WHERE cv_motivo = 0
        AND c_nom_mun = 'CORREGIDORA'
)
SELECT tipo_educativo,
    SUM(escuelas) as escuelas,
    SUM(alumnos) as alumnos
FROM datos_alumnos
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
    END