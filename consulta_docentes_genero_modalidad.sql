-- CONSULTA CONSOLIDADA DE DOCENTES POR GÉNERO Y MODALIDAD
-- Basada en las tablas y columnas identificadas en los archivos de configuración
-- EDUCACIÓN SUPERIOR (con género y modalidad)
SELECT 'Superior' as nivel_educativo,
    control as modalidad,
    SUM(v83) as total_docentes,
    SUM(v81) as docentes_hombres,
    SUM(v82) as docentes_mujeres
FROM nonce_pano_23.sup_escuela_23
WHERE c_nom_mun = 'CORREGIDORA'
    AND cv_motivo = 0
GROUP BY control
UNION ALL
-- PREESCOLAR GENERAL (con género y modalidad)
SELECT 'Preescolar General' as nivel_educativo,
    control as modalidad,
    SUM(v909) as total_docentes,
    SUM(v81) as docentes_hombres,
    SUM(v82) as docentes_mujeres
FROM nonce_pano_23.pree_gral_23
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
GROUP BY control
UNION ALL
-- SECUNDARIA (con modalidad)
SELECT 'Secundaria' as nivel_educativo,
    control as modalidad,
    SUM(v1401) as total_docentes,
    0 as docentes_hombres,
    -- Por investigar columnas de género
    0 as docentes_mujeres
FROM nonce_pano_23.sec_gral_23
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
GROUP BY control
UNION ALL
-- PRIMARIA GENERAL (con modalidad)
SELECT 'Primaria General' as nivel_educativo,
    control as modalidad,
    SUM(v1676) as total_docentes,
    0 as docentes_hombres,
    -- Por investigar columnas de género
    0 as docentes_mujeres
FROM nonce_pano_23.prim_gral_23
WHERE c_nom_mun = 'CORREGIDORA'
    AND (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
GROUP BY control
ORDER BY nivel_educativo,
    modalidad;