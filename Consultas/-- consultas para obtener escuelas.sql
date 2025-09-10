-- Inicial Escolarizada
if ((strcmp($dato, "ESCUELA")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".ini_gral_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
$qr_prim_hm = $qr_prim_gral_esc;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".ini_ind_".$ini_ciclo. " 
										WHERE cv_estatus_captura = 0 AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_esc;
} } -- Inicial No Escolarizada
if ((strcmp($dato, "ESCUELA")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".ini_ne_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
$qr_prim_hm = $qr_prim_gral_esc;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".ini_comuni_".$ini_ciclo. " 
										WHERE cv_estatus_captura = 0 AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_esc;
} } if ((strcmp($dato, "ALUMNO")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(V129 + V130)AS totales,
											SUM(v129)AS h,SUM(v130)AS m
										FROM nonce_pano_".$ini_ciclo. ".ini_ne_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_alum;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(V79 + V80)AS totales,
											SUM(v79)AS h,SUM(v80)AS m
										FROM nonce_pano_".$ini_ciclo. ".ini_comuni_".$ini_ciclo. " 
										WHERE cv_estatus_captura = 0 AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_alum;
} } -- Preescolar
if ((strcmp($dato, "ESCUELA")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".pree_gral_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
$qr_prim_hm = $qr_prim_gral_esc;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".pree_ind_".$ini_ciclo. " 
										WHERE cv_estatus_captura = 0 AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_esc;
} if ((strcmp($subnivel, "COMU")) == 0) { $qr_prim_comu_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".pree_comuni_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_comu_esc;
} } -- Primaria
if ((strcmp($dato, "ESCUELA")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".prim_gral_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
$qr_prim_hm = $qr_prim_gral_esc;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".prim_ind_".$ini_ciclo. " 
										WHERE cv_estatus_captura = 0 AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_esc;
} if ((strcmp($subnivel, "COMU")) == 0) { $qr_prim_comu_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".prim_comuni_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_comu_esc;
} } -- Secundaria
if ((strcmp($dato, "ESCUELA")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".sec_gral_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
$qr_prim_hm = $qr_prim_gral_esc;
} if ((strcmp($subnivel, "COMU")) == 0) { $qr_prim_comu_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_".$ini_ciclo. ".sec_comuni_".$ini_ciclo. " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_comu_esc;
} } -- Media superior
if ((strcmp($dato, "ESCUELA")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cct_ins_pla)AS totales
										FROM nonce_pano_".$ini_ciclo. ".ms_plantel_".$ini_ciclo. " 
										WHERE cv_motivo = 0  AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
$qr_prim_hm = $qr_prim_gral_esc;
} // Ind es tecnológico if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, 0 AS totales
										FROM nonce_pano_".$ini_ciclo. ".ms_tecno_".$ini_ciclo. " 
										WHERE cv_motivo = 0 AND cv_mun='".$cv_mun. "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_esc;
} } -- Superior
if ((strcmp($dato, "ESCUELA")) == 0) { if ((strcmp($subnivel, "GRAL")) == 0) { $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cv_cct) AS totales
										FROM nonce_pano_".$ini_ciclo.".sup_escuela_".$ini_ciclo." 
										WHERE cv_motivo = 0 AND cv_mun='".$cv_mun."' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
$qr_prim_hm = $qr_prim_gral_esc;
} if ((strcmp($subnivel, "IND")) == 0) { $qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, count (distinct cv_cct) AS totales
										FROM nonce_pano_".$ini_ciclo.".sup_posgrado_".$ini_ciclo." 
										WHERE cv_motivo = 0 AND cv_mun='".$cv_mun."'  
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
$qr_prim_hm = $qr_prim_ind_esc;
} }