-- EDUCACIÓN INICIAL ESCOLARIZADA
SELECT 'Inicial Escolarizada' as nivel_educativo,
    'General' as subnivel,
    COALESCE(
        SUM(
            V509 + V516 + V523 + V511 + V518 + V525 + V785 + V510 + V517 + V524 + V512 + V519 + V526 + V786
        ),
        0
    ) as total_docentes
FROM nonce_pano_24.ini_gral_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
UNION ALL
-- EDUCACIÓN INICIAL NO ESCOLARIZADA
SELECT 'Inicial No Escolarizada' as nivel_educativo,
    'Comunitario' as subnivel,
    COALESCE(SUM(v124 + V125), 0) as total_docentes
FROM nonce_pano_24.ini_comuni_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND cv_estatus_captura = 0
UNION ALL
-- CAM (CENTRO DE ATENCIÓN MÚLTIPLE)
SELECT 'CAM' as nivel_educativo,
    'Especial' as subnivel,
    22 as total_docentes
UNION ALL
-- PREESCOLAR GENERAL
SELECT 'Preescolar' as nivel_educativo,
    'General' as subnivel,
    COALESCE(SUM(v909), 0) as total_docentes
FROM nonce_pano_24.pree_gral_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
UNION ALL
-- PREESCOLAR COMUNITARIO
SELECT 'Preescolar' as nivel_educativo,
    'Comunitario' as subnivel,
    COALESCE(SUM(v151), 0) as total_docentes
FROM nonce_pano_24.pree_comuni_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
UNION ALL
-- PRIMARIA GENERAL
SELECT 'Primaria' as nivel_educativo,
    'General' as subnivel,
    COALESCE(SUM(v1676), 0) as total_docentes
FROM nonce_pano_24.prim_gral_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
UNION ALL
-- PRIMARIA COMUNITARIO
SELECT 'Primaria' as nivel_educativo,
    'Comunitario' as subnivel,
    COALESCE(SUM(v585), 0) as total_docentes
FROM nonce_pano_24.prim_comuni_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
UNION ALL
-- SECUNDARIA
SELECT 'Secundaria' as nivel_educativo,
    'General' as subnivel,
    COALESCE(SUM(v1401), 0) as total_docentes
FROM nonce_pano_24.sec_gral_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
UNION ALL
-- MEDIA SUPERIOR
SELECT 'Media Superior' as nivel_educativo,
    'Plantel' as subnivel,
    COALESCE(SUM(v169), 0) as total_docentes
FROM nonce_pano_24.ms_plantel_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND cv_motivo = 0
UNION ALL
-- SUPERIOR
SELECT 'Superior' as nivel_educativo,
    'Licenciatura' as subnivel,
    COALESCE(SUM(v83), 0) as total_docentes
FROM nonce_pano_24.sup_escuela_24
WHERE c_nom_mun = 'CORREGIDORA'
    AND cv_motivo = 0 // Consulta para obtener docentes por modalidad usando las consultas correctas $query = "
        -- INICIAL ESCOLARIZADA POR MODALIDAD
        SELECT 
            'Inicial Escolarizada' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(V509+V516+V523+V511+V518+V525+V785+V510+V517+V524+V512+V519+V526+V786) as docentes
        FROM nonce_pano_24.ini_gral_24 
        WHERE cv_mun = 14  AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- INICIAL NO ESCOLARIZADA
        SELECT 
            'Inicial No Escolarizada' as nivel,
            'publicos' as modalidad,
            SUM(v124 + v125) + 6 as docentes
        FROM nonce_pano_24.ini_comuni_24 
        WHERE cv_mun = 14  AND cv_estatus_captura = 0

        UNION ALL

        -- CAM (CENTRO DE ATENCIÓN MÚLTIPLE)
        SELECT 
            'CAM' as nivel,
            'publicos' as modalidad,
            152 as docentes

        UNION ALL

        -- USAER 
          SELECT 
          	'USAER' as nivel,
          	'publicos' as modalidad,
          	249 as docentes

        UNION ALL

        -- PREESCOLAR GENERAL POR MODALIDAD
        SELECT 
            'Preescolar' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v909) as docentes
        FROM nonce_pano_24.pree_gral_24 
        WHERE cv_mun = 14  AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- PREESCOLAR COMUNITARIO
        SELECT 
            'Preescolar' as nivel,
            'publicos' as modalidad,
            SUM(v151) as docentes
        FROM nonce_pano_23.pree_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA GENERAL POR MODALIDAD
        SELECT 
            'Primaria' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v1676) as docentes
        FROM nonce_pano_23.prim_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- PRIMARIA COMUNITARIO
        SELECT 
            'Primaria' as nivel,
            'publicos' as modalidad,
            SUM(v585) as docentes
        FROM nonce_pano_23.prim_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- SECUNDARIA GENERAL POR MODALIDAD
        SELECT 
            'Secundaria' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v1401) as docentes
        FROM nonce_pano_23.sec_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- MEDIA SUPERIOR POR MODALIDAD
        SELECT 
            'Media Superior' as nivel,
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v169) as docentes
        FROM nonce_pano_23.ms_plantel_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND cv_motivo = 0
        GROUP BY 
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END

        UNION ALL

        -- SUPERIOR POR MODALIDAD
        SELECT 
            'Superior' as nivel,
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v83) as docentes
        FROM nonce_pano_23.sup_escuela_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND cv_motivo = 0
        GROUP BY 
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END
        ";