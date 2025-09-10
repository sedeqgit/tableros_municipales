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
    AND cv_motivo = 0