-- Consultas para obtener alumnos de todos los niveles educativos, recuerda que es el ciclo 2023-2024 y el municipio de corregidora, además deberás de sumar las diferentes consultas por nivel educativo para obtener los valores correctos
--Inicial Escolarizada
if ((strcmp($dato, "ALUMNO")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(V398 + V414)AS totales,
SUM(v390 + v406) AS h,
SUM(v394 + v410) AS m
FROM nonce_pano_ ".$ini_ciclo. ".ini_gral_ ".$ini_ciclo. "
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, " IND ")) == 0) { $qr_prim_ind_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(V183 + V184) AS totales,
    SUM(v183) AS h,
    SUM(v184) AS m
FROM nonce_pano_ ".$ini_ciclo. ".ini_ind_ ".$ini_ciclo. "
WHERE cv_estatus_captura = 0
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_ind_alum;
} } -- Inicial no escolarizada
if ((strcmp($dato, " ALUMNO ")) == 0) { if ((strcmp($subnivel, " GRAL ")) == 0) { $qr_prim_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(V129 + V130) AS totales,
    SUM(v129) AS h,
    SUM(v130) AS m
FROM nonce_pano_ ".$ini_ciclo. ".ini_ne_ ".$ini_ciclo. "
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, " IND ")) == 0) { $qr_prim_ind_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(V79 + V80) AS totales,
    SUM(v79) AS h,
    SUM(v80) AS m
FROM nonce_pano_ ".$ini_ciclo. ".ini_comuni_ ".$ini_ciclo. "
WHERE cv_estatus_captura = 0
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_ind_alum;
} } -- Preescolar
if ((strcmp($dato, " ALUMNO ")) == 0) { if ((strcmp($subnivel, " GRAL ")) == 0) { $qr_prim_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(v177) AS totales,
    SUM(v165) AS h,
    SUM(v171) AS m
FROM nonce_pano_ ".$ini_ciclo. ".pree_gral_ ".$ini_ciclo. "
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, " IND ")) == 0) { $qr_prim_ind_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(v177) AS totales,
    SUM(v165) AS h,
    SUM(v171) AS m
FROM nonce_pano_ ".$ini_ciclo. ".pree_ind_ ".$ini_ciclo. "
WHERE cv_estatus_captura = 0
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_ind_alum;
} if ((strcmp($subnivel, " COMU ")) == 0) { $qr_prim_comu_alum = "
SELECT cv_mun,
    c_nom_mun AS MUNICIPIO,
    subcontrol,
    control,
    SUM(v97) AS totales,
    SUM(v85) AS h,
    SUM(v91) AS m
FROM nonce_pano_ ".$ini_ciclo. ".pree_comuni_ ".$ini_ciclo. "
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_comu_alum;
} } -- Primaria
if ((strcmp($dato, " ALUMNO ")) == 0) { if ((strcmp($subnivel, " GRAL ")) == 0) { $qr_prim_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(v608) AS totales,
    SUM(v562 + v573) AS h,
    SUM(v585 + v596) AS m
FROM nonce_pano_ ".$ini_ciclo. ".prim_gral_ ".$ini_ciclo. "
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v610)AS totales,
										SUM(v564+v575)AS h,SUM(v587+v598)AS m
										FROM nonce_pano_".$ini_ciclo. ".prim_ind_".$ini_ciclo. " 
										WHERE cv_estatus_captura = 0 AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_alum;
} if ((strcmp($subnivel, "COMU")) == 0) { $qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v515)AS totales,
										SUM(v469+v480)AS h,SUM(v492+v503)AS m
										FROM nonce_pano_".$ini_ciclo. ".prim_comuni_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_comu_alum;
} } -- Secundaria
if ((strcmp($dato, " ALUMNO ")) == 0) { if ((strcmp($subnivel, " GRAL ")) == 0) { $qr_prim_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(v340) AS totales,
    SUM(v306 + v314) AS h,
    SUM(v323 + v331) AS m
FROM nonce_pano_ ".$ini_ciclo. ".sec_gral_ ".$ini_ciclo. "
WHERE (
        cv_estatus_captura = 0
        OR cv_estatus_captura = 10
    )
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, "COMU")) == 0) { $qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v257)AS totales,
										SUM(v223+v231)AS h,SUM(v240+v248)AS m
										FROM nonce_pano_".$ini_ciclo. ".sec_comuni_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_comu_alum;
} } -- Media superior
if ((strcmp($dato, " ALUMNO ")) == 0) { if ((strcmp($subnivel, " GRAL ")) == 0) { $qr_prim_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(v397) AS totales,
    SUM(v395) AS h,
    SUM(v396) AS m
FROM nonce_pano_ ".$ini_ciclo. ".ms_gral_ ".$ini_ciclo. "
WHERE cv_motivo = 0
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v472)AS totales,
										SUM(v470)AS h,SUM(v471)AS m
										FROM nonce_pano_".$ini_ciclo. ".ms_tecno_".$ini_ciclo. " 
										WHERE cv_motivo = 0 AND cv_mun='".$cv_mun. "'  
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_alum;
} } -- Superior
if ((strcmp($dato, " ALUMNO ")) == 0) { if ((strcmp($subnivel, " GRAL ")) == 0) { $qr_prim_alum = "
SELECT cv_mun,
    c_nom_mun AS municipio,
    subcontrol,
    control,
    SUM(v177) AS totales,
    SUM(v175) AS h,
    SUM(v176) AS m
FROM nonce_pano_ ".$ini_ciclo. ".sup_carrera_ ".$ini_ciclo. "
WHERE cv_motivo = 0
    AND cv_mun = '".$cv_mun. "'
GROUP BY cv_mun,
    c_nom_mun,
    subcontrol,
    control
ORDER BY cv_mun,
    c_nom_mun,
    subcontrol,
    control;
";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v142)AS totales,
										SUM(v140)AS h,SUM(v141)AS m
										FROM nonce_pano_".$ini_ciclo.".sup_posgrado_".$ini_ciclo." 
										WHERE cv_motivo = 0 AND cv_mun='".$cv_mun."' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_alum;
} }