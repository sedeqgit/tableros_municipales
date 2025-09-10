-- CONSULTA COMPLETA UNIFICADA PARA OBTENER DATOS EDUCATIVOS DIRECTAMENTE DE TABLAS ORIGINALES
-- Ciclo 2023-2024, Municipio de Corregidora - VERSIÓN FINAL AGREGADA
-- Sin usar tabla auxiliar estadistica_corregidora
WITH datos_unificados AS (
  -- INICIAL ESCOLARIZADO GENERAL
  SELECT 'Inicial (Escolarizado)' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(V398 + V414), 0) as alumnos
  FROM nonce_pano_23.ini_gral_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- INICIAL ESCOLARIZADO INDÍGENA
  SELECT 'Inicial (Escolarizado)' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(V183 + V184), 0) as alumnos
  FROM nonce_pano_23.ini_ind_23
  WHERE cv_estatus_captura = 0
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- INICIAL NO ESCOLARIZADO GENERAL
  SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(V129 + V130), 0) as alumnos
  FROM nonce_pano_23.ini_ne_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- INICIAL NO ESCOLARIZADO COMUNITARIO
  SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(V79 + V80), 0) as alumnos
  FROM nonce_pano_23.ini_comuni_23
  WHERE cv_estatus_captura = 0
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- ESPECIAL (CAM)
  SELECT 'Especial (CAM)' as tipo_educativo,
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
  UNION ALL
  -- PREESCOLAR GENERAL
  SELECT 'Preescolar' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v177), 0) as alumnos
  FROM nonce_pano_23.pree_gral_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- PREESCOLAR INDÍGENA
  SELECT 'Preescolar' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v177), 0) as alumnos
  FROM nonce_pano_23.pree_ind_23
  WHERE cv_estatus_captura = 0
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- PREESCOLAR COMUNITARIO
  SELECT 'Preescolar' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v97), 0) as alumnos
  FROM nonce_pano_23.pree_comuni_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- PREESCOLAR ADICIONAL (v478 de inicial general)
  -- Estos son alumnos de preescolar registrados en la tabla de inicial general
  SELECT 'Preescolar' as tipo_educativo,
    0 as escuelas,
    -- No contar escuelas duplicadas
    COALESCE(SUM(v478), 0) as alumnos
  FROM nonce_pano_23.ini_gral_23
  WHERE cv_mun = 6
    AND (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
  UNION ALL
  -- PRIMARIA GENERAL
  SELECT 'Primaria' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v608), 0) as alumnos
  FROM nonce_pano_23.prim_gral_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- PRIMARIA INDÍGENA
  SELECT 'Primaria' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v610), 0) as alumnos
  FROM nonce_pano_23.prim_ind_23
  WHERE cv_estatus_captura = 0
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- PRIMARIA COMUNITARIA
  SELECT 'Primaria' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v515), 0) as alumnos
  FROM nonce_pano_23.prim_comuni_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- SECUNDARIA GENERAL
  SELECT 'Secundaria' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v340), 0) as alumnos
  FROM nonce_pano_23.sec_gral_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- SECUNDARIA COMUNITARIA
  SELECT 'Secundaria' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v257), 0) as alumnos
  FROM nonce_pano_23.sec_comuni_23
  WHERE (
      cv_estatus_captura = 0
      OR cv_estatus_captura = 10
    )
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- MEDIA SUPERIOR GENERAL
  SELECT 'Media Superior' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v397), 0) as alumnos
  FROM nonce_pano_23.ms_gral_23
  WHERE cv_motivo = 0
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- MEDIA SUPERIOR TECNOLÓGICO
  SELECT 'Media Superior' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v472), 0) as alumnos
  FROM nonce_pano_23.ms_tecno_23
  WHERE cv_motivo = 0
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- SUPERIOR CARRERA
  SELECT 'Superior' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v177), 0) as alumnos
  FROM nonce_pano_23.sup_carrera_23
  WHERE cv_motivo = 0
    AND c_nom_mun = 'CORREGIDORA'
  UNION ALL
  -- SUPERIOR POSGRADO
  SELECT 'Superior' as tipo_educativo,
    COUNT(DISTINCT cv_cct) as escuelas,
    COALESCE(SUM(v142), 0) as alumnos
  FROM nonce_pano_23.sup_posgrado_23
  WHERE cv_motivo = 0
    AND c_nom_mun = 'CORREGIDORA'
) -- RESULTADO FINAL AGREGADO POR TIPO EDUCATIVO
SELECT tipo_educativo,
  SUM(escuelas) as escuelas,
  SUM(alumnos) as alumnos
FROM datos_unificados
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