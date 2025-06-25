-- CONSULTA SIMPLE DE SUPERIOR CON GÉNERO Y MODALIDAD
SELECT 'Superior' as nivel_educativo,
    control as modalidad,
    SUM(v83) as total_docentes,
    SUM(v81) as docentes_hombres,
    SUM(v82) as docentes_mujeres
FROM nonce_pano_23.sup_escuela_23
WHERE c_nom_mun = 'CORREGIDORA'
    AND cv_motivo = 0
GROUP BY control
ORDER BY control;