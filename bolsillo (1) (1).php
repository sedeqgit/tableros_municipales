<?php
function Conectarse()
{
	$link_conexion = pg_connect("host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres options='--client_encoding=LATIN1'")
		or die('No se ha podido conectar: ' . pg_last_error());
	//$link_conexion->set_charset("utf8");
	return $link_conexion;
}
$link = Conectarse();
$sin_filtro_extra = " ";
$filtro_pub = " AND control<>'PRIVADO' ";
$filtro_priv = " AND control='PRIVADO' ";

function str_consulta($str_consulta, $ini_ciclo, $filtro)
{
	$consulta = "";

	if ((strcmp($str_consulta, 'gral_ini')) == 0) {
		$qr_ini_gral = "SELECT CONCAT('GENERAL') AS titulo_fila,
							SUM(V398+V414) AS total_matricula,SUM(V390+V406) AS mat_hombres,SUM(V394+V410) AS mat_mujeres,
							SUM(V509+V516+V523+V511+V518+V525+V510+V517+V524+V512+V519+V526) AS total_docentes,SUM(V509+V516+V523+V511+V518+V525) AS doc_hombres,SUM(V510+V517+V524+V512+V519+V526) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V402+V418) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo "<BR>".$qr_ini_gral."<BR>";
		$consulta = $qr_ini_gral;
	}
	if ((strcmp($str_consulta, 'gral_ini_dir_grp')) == 0) {
		$qr_ini_gral_dir = "SELECT CONCAT('GENERAL DIR CON GRUPO') AS titulo_fila, 
							SUM(0) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) 
							AS mat_mujeres, SUM(v787) 
							AS total_docentes,SUM(v785) AS 
							doc_hombres,SUM(v786) AS doc_mujeres, 
							SUM(0) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND V478='0'" . $filtro . " ;";
		//echo $qr_ini_gral_dir;
		$consulta = $qr_ini_gral_dir;
	}
	if ((strcmp($str_consulta, 'ind_ini')) == 0) {
		$qr_ini_ind = "SELECT CONCAT('INDIGENA') AS titulo_fila,SUM(V183+V184) AS total_matricula,SUM(V183) AS 	
							mat_hombres,SUM(V184) AS mat_mujeres,SUM(V291) AS total_docentes,SUM(V211) AS doc_hombres,SUM(V212) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V100) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_ind_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_ini_ind;
		$consulta = $qr_ini_ind;
	}
	if ((strcmp($str_consulta, 'lact_ini')) == 0) {
		$qr_ini_lact = "SELECT CONCAT('LACTANTE') AS titulo_fila,
							SUM(V398) AS total_matricula,SUM(V390) AS mat_hombres,SUM(V394) AS mat_mujeres,
							SUM(V509+V516+V523+V510+V517+V524) AS total_docentes,SUM(V509+V516+V523) AS doc_hombres,SUM(V510+V517+V524) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V402) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_ini_lact;
		$consulta = $qr_ini_lact;
	}
	if ((strcmp($str_consulta, 'mater_ini')) == 0) {
		$qr_ini_mater = "SELECT CONCAT('MATERNAL') AS titulo_fila,
							SUM(V414) AS total_matricula,SUM(V406) AS mat_hombres,SUM(V410) AS mat_mujeres,
							SUM(V511+V518+V525+V512+V519+V526) AS total_docentes,SUM(V511+V518+V525) AS doc_hombres,SUM(V512+V519+V526) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V418) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_ini_mater;
		$consulta = $qr_ini_mater;
	}
	if ((strcmp($str_consulta, 'comuni_ini')) == 0) {
		$qr_ini_comuni = "SELECT CONCAT('GENERAL') AS titulo_fila,
							SUM(V81) AS total_matricula,SUM(V79) AS mat_hombres,SUM(V80) AS mat_mujeres,
							SUM(V126) AS total_docentes,SUM(V124) AS doc_hombres,SUM(V125) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_comuni_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_ini_comuni;
		$consulta = $qr_ini_comuni;
	}
	if ((strcmp($str_consulta, 'ne_ini')) == 0) {
		$qr_ini_ne = "SELECT CONCAT('NO ESCOLARIZADA') AS titulo_fila,SUM(V129 + V130) AS total_matricula,SUM(V129) AS 	
							mat_hombres,SUM(V130) AS mat_mujeres,SUM(V183 + V184) AS total_docentes,SUM(V183) AS doc_hombres,SUM(V184) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_ne_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_ini_ne;
		$consulta = $qr_ini_ne;
	}


	if ((strcmp($str_consulta, 'ini_1ro_pree')) == 0) {
		$qr_ini_1ro = "SELECT CONCAT('INI_1ro') AS titulo_fila,SUM(V478) AS total_matricula,SUM(V466) AS 	
							mat_hombres,SUM(V472) AS mat_mujeres,SUM(V787+V513+V520+V527+V514+V521+V528) AS total_docentes,SUM(V785+V513+V520+V527) AS doc_hombres,SUM(V786+V514+V521+V528) AS doc_mujeres,SUM(0) AS escuelas,SUM(V479) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ini_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND V478>'0' " . $filtro . " ;";
		//echo $qr_ini_1ro;
		$consulta = $qr_ini_1ro;
	}
	if ((strcmp($str_consulta, 'gral_pree')) == 0) {
		$qr_pree_gral = "SELECT CONCAT('GENERAL') AS titulo_fila,
							SUM(V177) AS total_matricula,SUM(V165) AS mat_hombres,SUM(V171) AS mat_mujeres,
							SUM(V867+V868+V859+V860) AS total_docentes,SUM(V859+V868) AS doc_hombres,SUM(V860+V868) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V182) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".pree_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_pree_gral;
		$consulta = $qr_pree_gral;
	}
	if ((strcmp($str_consulta, 'ind_pree')) == 0) {
		$qr_pree_ind = "SELECT CONCAT('INDIGENA') AS titulo_fila,SUM(V177) AS total_matricula,SUM(V165) AS 	
							mat_hombres,SUM(V171) AS mat_mujeres,SUM(V795+V803+V796+V804) AS total_docentes,SUM(V795+V803) AS doc_hombres,SUM(V796+V804) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V182) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".pree_ind_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_pree_ind;
		$consulta = $qr_pree_ind;
	}
	if ((strcmp($str_consulta, 'comuni_pree')) == 0) {
		$qr_pree_comuni = "SELECT CONCAT('COMUNITARIO') AS titulo_fila,
							SUM(V97) AS total_matricula,SUM(V85) AS mat_hombres,SUM(V91) AS mat_mujeres,
							SUM(V151) AS total_docentes,SUM(V149) AS doc_hombres,SUM(V150) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,(COUNT(cv_cct)-SUM(V78)) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".pree_comuni_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_pree_comuni;
		$consulta = $qr_pree_comuni;
	}


	if ((strcmp($str_consulta, 'gral_prim')) == 0) {
		$qr_prim_gral = "SELECT CONCAT('GENERAL') AS titulo_fila,SUM(V608) AS total_matricula,SUM(V562+V573) AS 	
							mat_hombres,SUM(V585+V596) AS mat_mujeres,SUM(V1575+V1576+V1567+V1568) AS total_docentes,SUM(V1575+V1567) AS doc_hombres,SUM(V1576+V1568) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V616) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_prim_gral;
		$consulta = $qr_prim_gral;
	}
	if ((strcmp($str_consulta, 'ind_prim')) == 0) {
		$qr_prim_ind = "SELECT CONCAT('INDIGENA') AS titulo_fila,SUM(V610) AS total_matricula,SUM(V564+V575) AS 	
							mat_hombres,SUM(V587+V598) AS mat_mujeres,SUM(V1507+V1499+V1508+V1500) AS total_docentes,SUM(V1507+V1499) AS doc_hombres,SUM(V1508+V1500) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V1052) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_prim_ind;
		$consulta = $qr_prim_ind;
	}
	if ((strcmp($str_consulta, 'comuni_prim')) == 0) {
		$qr_prim_comuni = "SELECT CONCAT('COMUNITARIO') AS titulo_fila,SUM(V515) AS total_matricula,SUM(V469+V480) AS 	
							mat_hombres,SUM(V492+V503) AS mat_mujeres,SUM(V585) AS total_docentes,SUM(V583) AS doc_hombres,SUM(V584) AS doc_mujeres,COUNT(cv_cct) AS escuelas,COUNT(cv_cct) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_prim_comuni;
		$consulta = $qr_prim_comuni;
	}


	if ((strcmp($str_consulta, 'gral_sec')) == 0) {
		$qr_sec_gral = "SELECT CONCAT('GENERAL') AS titulo_fila,SUM(V340) AS total_matricula,SUM(V306+V314) AS 	
							mat_hombres,SUM(V323+V331) AS mat_mujeres,SUM(V1401) AS total_docentes,SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V341) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sec_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_sec_gral;
		$consulta = $qr_sec_gral;
	}
	if ((strcmp($str_consulta, 'sec_gral_gral')) == 0) {
		$qr_sec_gral_gral = "SELECT CONCAT('GENERAL') AS titulo_fila,SUM(V340) AS total_matricula,SUM(V306+V314) AS 	
							mat_hombres,SUM(V323+V331) AS mat_mujeres,SUM(V1401) AS total_docentes,SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V341) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sec_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND subnivel='GENERAL' " . $filtro . " ;";
		//echo $qr_sec_gral_gral;
		$consulta = $qr_sec_gral_gral;
	}
	if ((strcmp($str_consulta, 'sec_gral_tele')) == 0) {
		$qr_sec_gral_tele = "SELECT CONCAT('TELESECUNDARIA') AS titulo_fila,SUM(V340) AS total_matricula,SUM(V306+V314) AS 	
							mat_hombres,SUM(V323+V331) AS mat_mujeres,SUM(V1401) AS total_docentes,SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V813) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sec_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND subnivel='TELESECUNDARIA' " . $filtro . " ;";
		//echo $qr_sec_gral_tele;
		$consulta = $qr_sec_gral_tele;
	}
	if ((strcmp($str_consulta, 'sec_gral_tec')) == 0) {
		$qr_sec_gral_tec = "SELECT CONCAT('TECNICA') AS titulo_fila,SUM(V340) AS total_matricula,SUM(V306+V314) AS 	
							mat_hombres,SUM(V323+V331) AS mat_mujeres,SUM(V1401) AS total_docentes,SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,COUNT(cv_cct) AS escuelas,SUM(V341) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sec_gral_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND subnivel<>'TELESECUNDARIA' AND subnivel<>'GENERAL' " . $filtro . " ;";
		//echo $qr_sec_gral_tec;
		$consulta = $qr_sec_gral_tec;
	}
	if ((strcmp($str_consulta, 'comuni_sec')) == 0) {
		$qr_sec_comuni = "SELECT CONCAT('COMUNITARIO') AS titulo_fila,SUM(V257) AS total_matricula,SUM(V223+V231) AS 	
							mat_hombres,SUM(V240+V248) AS mat_mujeres,SUM(V386) AS total_docentes,SUM(V384) AS doc_hombres,SUM(V385) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,COUNT(cv_cct) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sec_comuni_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_sec_comuni;
		$consulta = $qr_sec_comuni;
	}


	if ((strcmp($str_consulta, 'bgral_msup')) == 0) {
		$qr_msup_gral = "SELECT CONCAT('BACHILLERATO GENERAL') AS titulo_fila,
							SUM(V397) AS total_matricula,SUM(V395) AS mat_hombres,SUM(V396) AS mat_mujeres,
							SUM(V960) AS total_docentes,SUM(V958) AS doc_hombres,SUM(V959) AS doc_mujeres,
							COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,SUM(V401) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2')  " . $filtro . " ;";
		//echo $qr_msup_gral;
		$consulta = $qr_msup_gral;
	}
	if ((strcmp($str_consulta, 'btecno_msup')) == 0) {
		$qr_msup_tecno = "SELECT CONCAT('BACHILLERATO TECNOLOGICO') AS titulo_fila,SUM(V472) AS total_matricula,SUM(V470) AS 	
							mat_hombres,SUM(V471) AS mat_mujeres,SUM(V1059) AS total_docentes,SUM(V1057) AS doc_hombres,SUM(V1058) AS doc_mujeres,
							COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,SUM(V476) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') " . $filtro . " ;";
		//echo $qr_msup_tecno;
		$consulta = $qr_msup_tecno;
	}
	if ((strcmp($str_consulta, 'btecno_tecno_msup')) == 0) {
		$qr_msup_tecno_btecno = "SELECT CONCAT('BACHILLERATO TECNOLOGICO') AS titulo_fila,SUM(V472) AS total_matricula,SUM(V470) AS 	
							mat_hombres,SUM(V471) AS mat_mujeres,SUM(V1059) AS total_docentes,SUM(V1057) AS doc_hombres,SUM(V1058) AS doc_mujeres,
							COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,SUM(V476) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') AND cv_servicion3='2' " . $filtro . " ;";
		//echo $qr_msup_tecno_btecno;
		$consulta = $qr_msup_tecno_btecno;
	}
	if ((strcmp($str_consulta, 'btecno_pbach_msup')) == 0) {
		$qr_msup_tecno_pbach = "SELECT CONCAT('BACHILLERATO TECNOLOGICO') AS titulo_fila,SUM(V472) AS total_matricula,SUM(V470) AS 	
							mat_hombres,SUM(V471) AS mat_mujeres,SUM(V1059) AS total_docentes,SUM(V1057) AS doc_hombres,SUM(V1058) AS doc_mujeres,
							COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,SUM(V476) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
							WHERE cv_motivo = '0'  AND (cv_estatus<>'4' AND cv_estatus<>'2') AND cv_servicion3='3' " . $filtro . " ;";
		//echo $qr_msup_tecno_pbach;
		$consulta = $qr_msup_tecno_pbach;
	}
	if ((strcmp($str_consulta, 'btecno_ptecno_msup')) == 0) {
		$qr_msup_tecno_ptecno = "SELECT CONCAT('BACHILLERATO TECNOLOGICO') AS titulo_fila,SUM(V472) AS total_matricula,SUM(V470) AS 	
							mat_hombres,SUM(V471) AS mat_mujeres,SUM(V1059) AS total_docentes,SUM(V1057) AS doc_hombres,SUM(V1058) AS doc_mujeres,
							COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,SUM(V476) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
							WHERE cv_motivo = '0'  AND (cv_estatus<>'4' AND cv_estatus<>'2') AND cv_servicion3='4' " . $filtro . " ;";
		//echo $qr_msup_tecno_ptecno;
		$consulta = $qr_msup_tecno_ptecno;
	}
	if ((strcmp($str_consulta, 'plant_doc_esc_msup')) == 0) {
		$qr_doc_plant_msup = "SELECT CONCAT('DOCENTES PLANTEL') AS titulo_fila,
							SUM(0) AS total_matricula,SUM(0) AS 	
							mat_hombres,SUM(0) AS mat_mujeres,SUM(V106+V101) AS total_docentes,
							SUM(V104+V99) AS doc_hombres,SUM(V105+V100) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_plantel_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' " . $filtro . " ;";
		//echo $qr_doc_plant_msup;
		$consulta = $qr_doc_plant_msup;
	}
	if ((strcmp($str_consulta, 'bgral_escuelas_msup')) == 0) {
		$qr_cct_msup_gral = "SELECT CONCAT('ESCUELAS BACHILLERATO GENERAL') AS titulo_fila,
							SUM(0) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,
							COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') " . $filtro . " ;";
		//echo $qr_cct_msup_gral;
		$consulta = $qr_cct_msup_gral;
	}
	if ((strcmp($str_consulta, 'btecno_escuelas_msup')) == 0) {
		$qr_cct_msup_tecno = "SELECT CONCAT('BACHILLERATO TECNOLOGICO') AS titulo_fila,SUM(0) AS total_matricula,SUM(0) AS 	
							mat_hombres,SUM(0) AS mat_mujeres,SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,SUM(V476) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') " . $filtro . " ;";
		//echo $qr_cct_msup_tecno;
		$consulta = $qr_cct_msup_tecno;
	}

	if ((strcmp($str_consulta, 'carr_lic_sup')) == 0) {
		$qr_carr_sup = "SELECT CONCAT('LICENCIATURA') AS titulo_fila,
							SUM(V177) AS total_matricula,SUM(V175) AS mat_hombres,SUM(V176) AS mat_mujeres,
							SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_carrera_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' " . $filtro . " ;";
		//echo $qr_carr_sup;
		$consulta = $qr_carr_sup;
	}
	if ((strcmp($str_consulta, 'carr_normal_sup')) == 0) {
		$qr_carr_sup_normal = "SELECT CONCAT('LICENCIATURA') AS titulo_fila,
							SUM(V177) AS total_matricula,SUM(V175) AS mat_hombres,SUM(V176) AS mat_mujeres,
							SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_carrera_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (subsistema_3 LIKE '%Normal%' OR subsistema_3 LIKE '%NORMAL%') " . $filtro . " ;";
		//echo $qr_carr_sup_normal;
		$consulta = $qr_carr_sup_normal;
	}
	if ((strcmp($str_consulta, 'carr_tecno_sup')) == 0) {
		$qr_carr_sup_tecno = "SELECT CONCAT('LICENCIATURA') AS titulo_fila,
							SUM(V177) AS total_matricula,SUM(V175) AS mat_hombres,SUM(V176) AS mat_mujeres,
							SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_carrera_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (subsistema_3 NOT LIKE '%Normal%' AND subsistema_3 NOT LIKE '%NORMAL%') " . $filtro . " ;";
		//echo $qr_carr_sup_tecno;
		$consulta = $qr_carr_sup_tecno;
	}
	if ((strcmp($str_consulta, 'posgr_sup')) == 0) {
		$qr_posgr_sup = "SELECT CONCAT('POSGRADO') AS titulo_fila,
							SUM(V142) AS total_matricula,SUM(V140) AS mat_hombres,SUM(V141) AS mat_mujeres,
							SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_posgrado_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' " . $filtro . " ;";
		//echo $qr_posgr_sup;
		$consulta = $qr_posgr_sup;
	}
	if ((strcmp($str_consulta, 'esc_lic_sup')) == 0) {
		$qr_esc_sup_lic = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V214+V218) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V944+V768) AS total_docentes,SUM(V942+V766) AS doc_hombres,SUM(V943+V767) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V944>'0' OR V768>'0') " . $filtro . " ;";
		//echo $qr_esc_sup_lic;
		$consulta = $qr_esc_sup_lic;
	}
	if ((strcmp($str_consulta, 'esc_normal_sup')) == 0) {
		$qr_esc_sup_lic_normal = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V214+V218) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V944+V768) AS total_docentes,SUM(V942+V766) AS doc_hombres,SUM(V943+V767) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V944>'0' OR V768>'0') AND (nombre_ins LIKE '%NORMAL%' OR nombre_ins LIKE '%INSTITUTO LA PAZ%') " . $filtro . " ;";
		//echo $qr_esc_sup_lic_normal;
		$consulta = $qr_esc_sup_lic_normal;
	}
	if ((strcmp($str_consulta, 'esc_tecno_sup')) == 0) {
		$qr_esc_sup_lic_tecno = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V214+V218) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V944+V768) AS total_docentes,SUM(V942+V766) AS doc_hombres,SUM(V943+V767) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V944>'0' OR V768>'0') AND (nombre_ins NOT LIKE '%NORMAL%' AND nombre_ins NOT LIKE '%INSTITUTO LA PAZ%')  " . $filtro . " ;";
		//echo $qr_esc_sup_lic_tecno;
		$consulta = $qr_esc_sup_lic_tecno;
	}
	if ((strcmp($str_consulta, 'esc_posgr_sup')) == 0) {
		$qr_esc_sup_posgr = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V220+V222+V224) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V771) AS total_docentes,SUM(V769) AS doc_hombres,SUM(V770) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND V771>'0' " . $filtro . " ;";
		//echo $qr_esc_sup_posgr;
		$consulta = $qr_esc_sup_posgr;
	}
	if ((strcmp($str_consulta, 'esc_carr_doc_sup')) == 0) {
		$qr_escuela_sup = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V226) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V944+V768+V771) AS total_docentes,SUM(V942+V766+V769) AS doc_hombres,SUM(V943+V767+V770) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V944>'0' OR V768>'0' OR V771>'0') " . $filtro . " ;";
		//echo $qr_escuela_sup;
		$consulta = $qr_escuela_sup;
	}
	if ((strcmp($str_consulta, 'esc_docentes_sup')) == 0) {
		$qr_escuela_sup = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(0) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V83) AS total_docentes,SUM(V81) AS doc_hombres,SUM(V82) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' " . $filtro . " ;";
		//echo $qr_escuela_sup;
		$consulta = $qr_escuela_sup;
	}
	if ((strcmp($str_consulta, 'carr_usbq_tsu_sup')) == 0) {
		$carr_usbq_tsu_sup = "SELECT CONCAT('LICENCIATURA') AS titulo_fila,
							SUM(V177) AS total_matricula,SUM(V175) AS mat_hombres,SUM(V176) AS mat_mujeres,
							SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_carrera_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND cv_carrera LIKE '4%' " . $filtro . " ;";
		//echo $carr_usbq_tsu_sup;
		$consulta = $carr_usbq_tsu_sup;
	}
	if ((strcmp($str_consulta, 'carr_usbq_lic_sup')) == 0) {
		$carr_usbq_lic_sup = "SELECT CONCAT('LICENCIATURA') AS titulo_fila,
							SUM(V177) AS total_matricula,SUM(V175) AS mat_hombres,SUM(V176) AS mat_mujeres,
							SUM(0) AS total_docentes,SUM(0) AS doc_hombres,SUM(0) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_carrera_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND cv_carrera LIKE '5%' " . $filtro . " ;";
		//echo $carr_usbq_lic_sup;
		$consulta = $carr_usbq_lic_sup;
	}


	if ((strcmp($str_consulta, 'esc_nesc_lic_sup')) == 0) {
		$qr_esc_sup_lic_ne = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V468+V472) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V962+V799) AS total_docentes,SUM(V961+V798) AS doc_hombres,SUM(V960+V797) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V962>'0' OR V799>'0') " . $filtro . " ;";
		//echo $qr_esc_sup_lic_ne;
		$consulta = $qr_esc_sup_lic_ne;
	}
	if ((strcmp($str_consulta, 'esc_nesc_normal_sup')) == 0) {
		$qr_esc_sup_lic_normal_ne = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V468+V472) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V962+V799) AS total_docentes,SUM(V961+V798) AS doc_hombres,SUM(V960+V797) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V962>'0' OR V799>'0') AND (nombre_ins LIKE '%NORMAL%' OR nombre_ins LIKE '%INSTITUTO LA PAZ%') " . $filtro . " ;";
		//echo $qr_esc_sup_lic_normal_ne;
		$consulta = $qr_esc_sup_lic_normal_ne;
	}
	if ((strcmp($str_consulta, 'esc_nesc_tecno_sup')) == 0) {
		$qr_esc_sup_lic_tecno_ne = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V468+V472) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V962+V799) AS total_docentes,SUM(V961+V798) AS doc_hombres,SUM(V960+V797) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V962>'0' OR V799>'0') AND (nombre_ins NOT LIKE '%NORMAL%' AND nombre_ins NOT LIKE '%INSTITUTO LA PAZ%') " . $filtro . " ;";
		//echo $qr_esc_sup_lic_tecno_ne;
		$consulta = $qr_esc_sup_lic_tecno_ne;
	}
	if ((strcmp($str_consulta, 'esc_nesc_posgr_sup')) == 0) {
		$qr_esc_sup_posgr_ne = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V474+V478+V478) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V802) AS total_docentes,SUM(V800) AS doc_hombres,SUM(V801) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND V802>'0' " . $filtro . " ;";
		//echo $qr_esc_sup_posgr_ne;
		$consulta = $qr_esc_sup_posgr_ne;
	}
	if ((strcmp($str_consulta, 'esc_nesc_sup')) == 0) {
		//(V962>'0' OR V799>'0' OR V802>'0') para filtrar las escuelas en esa modalidad 
		$qr_escuela_sup_ne = "SELECT CONCAT('ESCUELA') AS titulo_fila,
							SUM(V480) AS total_matricula,SUM(0) AS mat_hombres,SUM(0) AS mat_mujeres,
							SUM(V962+V799+V802) AS total_docentes,SUM(V960+V797+V800) AS doc_hombres,SUM(V961+V798+V801) AS doc_mujeres,
							COUNT(cct_ins_pla) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".sup_escuela_" . $ini_ciclo . " 
							WHERE cv_motivo = '0' AND (V962>'0' OR V799>'0' OR V802>'0') " . $filtro . " ;";
		//echo $qr_escuela_sup_ne;
		$consulta = $qr_escuela_sup_ne;
	}


	if ((strcmp($str_consulta, 'especial_tot')) == 0) {
		$qr_especial_tot = "SELECT CONCAT('ESPECIAL_TOTAL') AS titulo_fila,
							SUM(V2257) AS total_matricula,SUM(V2255) AS mat_hombres,SUM(V2256) AS mat_mujeres,
							SUM(V2496) AS total_docentes,SUM(V2302) AS doc_hombres,SUM(V2303) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V1343+V1418+V1511+V1586+V1765) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_cam_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0) " . $filtro . " ;";
		//echo $qr_especial_tot;
		$consulta = $qr_especial_tot;
	}
	if ((strcmp($str_consulta, 'especial_ini')) == 0) {
		$qr_especial_ini = "SELECT CONCAT('ESPECIAL_INICIAL') AS titulo_fila,
							SUM(V1338+V1340+V1339+V1341) AS total_matricula,SUM(V1338+V1340) AS mat_hombres,SUM(V1339+V1341) AS mat_mujeres,
							SUM(V2496) AS total_docentes,SUM(V2302) AS doc_hombres,SUM(V2303) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V1343) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_cam_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0) " . $filtro . " ;";
		//echo $qr_especial_ini;
		$consulta = $qr_especial_ini;
	}
	if ((strcmp($str_consulta, 'especial_pree')) == 0) {
		$qr_especial_pree = "SELECT CONCAT('ESPECIAL_PREESCOLAR') AS titulo_fila,
							SUM(V1413+V1415+V1414+V1416) AS total_matricula,SUM(V1413+V1415) AS mat_hombres,SUM(V1414+V1416) AS mat_mujeres,
							SUM(V2496) AS total_docentes,SUM(V2302) AS doc_hombres,SUM(V2303) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V1418) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_cam_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0) " . $filtro . " ;";
		//echo $qr_especial_pree;
		$consulta = $qr_especial_pree;
	}
	if ((strcmp($str_consulta, 'especial_prim')) == 0) {
		$qr_especial_prim = "SELECT CONCAT('ESPECIAL_PRIMARIA') AS titulo_fila,
							SUM(V1506+V1508+V1507+V1509) AS total_matricula,SUM(V1506+V1508) AS mat_hombres,SUM(V1507+V1509) AS mat_mujeres,
							SUM(V2496) AS total_docentes,SUM(V2302) AS doc_hombres,SUM(V2303) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V1511) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_cam_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0) " . $filtro . " ;";
		//echo $qr_especial_prim;
		$consulta = $qr_especial_prim;
	}
	if ((strcmp($str_consulta, 'especial_sec')) == 0) {
		$qr_especial_sec = "SELECT CONCAT('ESPECIAL_SECUNDARIA') AS titulo_fila,
							SUM(V1581+V1583+V1582+V1584) AS total_matricula,SUM(V1581+V1583) AS mat_hombres,SUM(V1582+V1584) AS mat_mujeres,
							SUM(V2496) AS total_docentes,SUM(V2302) AS doc_hombres,SUM(V2303) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V1586) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_cam_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0) " . $filtro . " ;";
		//echo $qr_especial_sec;
		$consulta = $qr_especial_sec;
	}
	if ((strcmp($str_consulta, 'especial_ftrab')) == 0) {
		$qr_especial_ftrab = "SELECT CONCAT('ESPECIAL_FTRAB') AS titulo_fila,
							SUM(V1760+V1762+V1761+V1763) AS total_matricula,SUM(V1760+V1762) AS mat_hombres,SUM(V1761+V1763) AS mat_mujeres,
							SUM(V2496) AS total_docentes,SUM(V2302) AS doc_hombres,SUM(V2303) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(V1765) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_cam_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0) " . $filtro . " ;";
		//echo $qr_especial_ftrab;
		$consulta = $qr_especial_ftrab;
	}
	if ((strcmp($str_consulta, 'especial_apoyo')) == 0) {
		$qr_especial_apoyo = "SELECT CONCAT('ESPECIAL_APOYO') AS titulo_fila,
							SUM(V1887+V1889+V1888+V1890) AS total_matricula,SUM(V1887+V1889) AS mat_hombres,SUM(V1888+V1890) AS mat_mujeres,
							SUM(V2496) AS total_docentes,SUM(V2302) AS doc_hombres,SUM(V2303) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_cam_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0) " . $filtro . " ;";
		//echo $qr_especial_apoyo;
		$consulta = $qr_especial_apoyo;
	}
	if ((strcmp($str_consulta, 'especial_usaer')) == 0) {
		$qr_especial_usaer = "SELECT CONCAT('ESPECIAL_USAER') AS titulo_fila,
							SUM(v2827) AS total_matricula,SUM(V2814+V2816+V2818+V2820) AS mat_hombres,SUM(V2815+V2817+V2819+V2821) AS mat_mujeres,
							SUM(v2828+V2973+V2974) AS total_docentes,SUM(V2973) AS doc_hombres,SUM(V2974) AS doc_mujeres,
							COUNT(cv_cct) AS escuelas,SUM(0) AS grupos 
							FROM nonce_pano_" . $ini_ciclo . ".esp_usaer_" . $ini_ciclo . " 
							WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) " . $filtro . " ;";
		//echo $qr_especial_usaer;
		$consulta = $qr_especial_usaer;
	}

	return $consulta;
}
function rs_consulta($link, $str_consulta, $ini_ciclo, $filtro)
{
	$consulta = str_consulta($str_consulta, $ini_ciclo, $filtro);

	$rs_nivel = pg_query($link, $consulta) or die('La consulta' . $str_consulta . ': ' . pg_last_error());
	$cant_nivel = pg_num_rows($rs_nivel);
	if ($cant_nivel > 0) {
		$row_nivel = pg_fetch_assoc($rs_nivel);
		$titulo_fila = $row_nivel["titulo_fila"];
		$tot_mat_nivel = $row_nivel["total_matricula"];
		$mat_h_nivel = $row_nivel["mat_hombres"];
		$mat_m_nivel = $row_nivel["mat_mujeres"];
		$tot_doc_nivel = $row_nivel["total_docentes"];
		$doc_h_nivel = $row_nivel["doc_hombres"];
		$doc_m_nivel = $row_nivel["doc_mujeres"];
		$tot_esc_nivel = $row_nivel["escuelas"];
		$tot_grp_nivel = $row_nivel["grupos"];

		$nivel_detalle = [
			"titulo_fila" => $titulo_fila,
			"tot_mat" => $tot_mat_nivel,
			"mat_h" => $mat_h_nivel,
			"mat_m" => $mat_m_nivel,
			"tot_doc" => $tot_doc_nivel,
			"doc_h" => $doc_h_nivel,
			"doc_m" => $doc_m_nivel,
			"tot_esc" => $tot_esc_nivel,
			"tot_grp" => $tot_grp_nivel
		];
		//echo "<BR>";
		//echo print_r ($nivel_detalle),"<BR>";
	}
	return $nivel_detalle;
}
function subnivel_cero()
{

	$total_subnivel_cero = [
		"titulo_fila" => "SIN DATOS",
		"tot_mat" => 0,
		"tot_mat_pub" => 0,
		"tot_mat_priv" => 0,
		"mat_h" => 0,
		"mat_h_pub" => 0,
		"mat_h_priv" => 0,
		"mat_m" => 0,
		"mat_m_pub" => 0,
		"mat_m_priv" => 0,
		"tot_doc" => 0,
		"tot_doc_pub" => 0,
		"tot_doc_priv" => 0,
		"doc_h" => 0,
		"doc_h_pub" => 0,
		"doc_h_priv" => 0,
		"doc_m" => 0,
		"doc_m_pub" => 0,
		"doc_m_priv" => 0,
		"tot_esc" => 0,
		"tot_esc_pub" => 0,
		"tot_esc_priv" => 0,
		"tot_grp" => 0,
		"tot_grp_pub" => 0,
		"tot_grp_priv" => 0
	];
	//echo "<BR>";
	//echo print_r ($total_subnivel_cero),"<BR>";
	return $total_subnivel_cero;
}
function subnivel($link, $titulo_fila, $ini_ciclo, $str_consulta, $filtro_extra, $filtro_pub, $filtro_priv)
{

	$subnivel_tot = rs_consulta($link, $str_consulta, $ini_ciclo, $filtro_extra);
	$subnivel_pub = rs_consulta($link, $str_consulta, $ini_ciclo, $filtro_pub . " " . $filtro_extra);
	$subnivel_priv = rs_consulta($link, $str_consulta, $ini_ciclo, $filtro_priv . " " . $filtro_extra);

	$total_subnivel = [
		"titulo_fila" => $titulo_fila,
		"tot_mat" => $subnivel_tot["tot_mat"],
		"tot_mat_pub" => $subnivel_pub["tot_mat"],
		"tot_mat_priv" => $subnivel_priv["tot_mat"],
		"mat_h" => $subnivel_tot["mat_h"],
		"mat_h_pub" => $subnivel_pub["mat_h"],
		"mat_h_priv" => $subnivel_priv["mat_h"],
		"mat_m" => $subnivel_tot["mat_m"],
		"mat_m_pub" => $subnivel_pub["mat_m"],
		"mat_m_priv" => $subnivel_priv["mat_m"],
		"tot_doc" => $subnivel_tot["tot_doc"],
		"tot_doc_pub" => $subnivel_pub["tot_doc"],
		"tot_doc_priv" => $subnivel_priv["tot_doc"],
		"doc_h" => $subnivel_tot["doc_h"],
		"doc_h_pub" => $subnivel_pub["doc_h"],
		"doc_h_priv" => $subnivel_priv["doc_h"],
		"doc_m" => $subnivel_tot["doc_m"],
		"doc_m_pub" => $subnivel_pub["doc_m"],
		"doc_m_priv" => $subnivel_priv["doc_m"],
		"tot_esc" => $subnivel_tot["tot_esc"],
		"tot_esc_pub" => $subnivel_pub["tot_esc"],
		"tot_esc_priv" => $subnivel_priv["tot_esc"],
		"tot_grp" => $subnivel_tot["tot_grp"],
		"tot_grp_pub" => $subnivel_pub["tot_grp"],
		"tot_grp_priv" => $subnivel_priv["tot_grp"]
	];
	//echo "<BR>";
	//echo print_r ($total_subnivel),"<BR>";
	return $total_subnivel;
}
function mezcla_arreglos($tipo, $titulo_fila, $str_subnivel1, $str_subnivel2, $titulo_subnivel1, $titulo_subnivel2, $link, $ini_ciclo, $filtro, $filtro_pub, $filtro_priv)
{

	$arr_subnivel1 = subnivel($link, $titulo_subnivel1, $ini_ciclo, $str_subnivel1, $filtro, $filtro_pub . " " . $filtro, $filtro_priv . " " . $filtro);
	$arr_subnivel2 = subnivel($link, $titulo_subnivel2, $ini_ciclo, $str_subnivel2, " ", $filtro_pub, $filtro_priv);

	if ((strcmp($tipo, 'AMBOS')) == 0) {
		$subnivel_comb = [
			"titulo_fila" => $titulo_fila,
			"tot_mat" => $arr_subnivel1["tot_mat"],
			"tot_mat_pub" => $arr_subnivel1["tot_mat_pub"],
			"tot_mat_priv" => $arr_subnivel1["tot_mat_priv"],
			"mat_h" => $arr_subnivel1["mat_h"],
			"mat_h_pub" => $arr_subnivel1["mat_h_pub"],
			"mat_h_priv" => $arr_subnivel1["mat_h_priv"],
			"mat_m" => $arr_subnivel1["mat_m"],
			"mat_m_pub" => $arr_subnivel1["mat_m_pub"],
			"mat_m_priv" => $arr_subnivel1["mat_m_priv"],
			"tot_doc" => $arr_subnivel2["tot_doc"],
			"tot_doc_pub" => $arr_subnivel2["tot_doc_pub"],
			"tot_doc_priv" => $arr_subnivel2["tot_doc_priv"],
			"doc_h" => $arr_subnivel2["doc_h"],
			"doc_h_pub" => $arr_subnivel2["doc_h_pub"],
			"doc_h_priv" => $arr_subnivel2["doc_h_priv"],
			"doc_m" => $arr_subnivel2["doc_m"],
			"doc_m_pub" => $arr_subnivel2["doc_m_pub"],
			"doc_m_priv" => $arr_subnivel2["doc_m_priv"],
			"tot_esc" => $arr_subnivel2["tot_esc"],
			"tot_esc_pub" => $arr_subnivel2["tot_esc_pub"],
			"tot_esc_priv" => $arr_subnivel2["tot_esc_priv"],
			"tot_grp" => $arr_subnivel2["tot_grp"],
			"tot_grp_pub" => $arr_subnivel2["tot_grp_pub"],
			"tot_grp_priv" => $arr_subnivel2["tot_grp_priv"]
		];
	}
	if ((strcmp($tipo, 'ESCUELAS')) == 0) {
		$subnivel_comb = [
			"titulo_fila" => $titulo_fila,
			"tot_mat" => $arr_subnivel1["tot_mat"],
			"tot_mat_pub" => $arr_subnivel1["tot_mat_pub"],
			"tot_mat_priv" => $arr_subnivel1["tot_mat_priv"],
			"mat_h" => $arr_subnivel1["mat_h"],
			"mat_h_pub" => $arr_subnivel1["mat_h_pub"],
			"mat_h_priv" => $arr_subnivel1["mat_h_priv"],
			"mat_m" => $arr_subnivel1["mat_m"],
			"mat_m_pub" => $arr_subnivel1["mat_m_pub"],
			"mat_m_priv" => $arr_subnivel1["mat_m_priv"],
			"tot_doc" => $arr_subnivel1["tot_doc"],
			"tot_doc_pub" => $arr_subnivel1["tot_doc_pub"],
			"tot_doc_priv" => $arr_subnivel1["tot_doc_priv"],
			"doc_h" => $arr_subnivel1["doc_h"],
			"doc_h_pub" => $arr_subnivel1["doc_h_pub"],
			"doc_h_priv" => $arr_subnivel1["doc_h_priv"],
			"doc_m" => $arr_subnivel1["doc_m"],
			"doc_m_pub" => $arr_subnivel1["doc_m_pub"],
			"doc_m_priv" => $arr_subnivel1["doc_m_priv"],
			"tot_esc" => $arr_subnivel2["tot_esc"],
			"tot_esc_pub" => $arr_subnivel2["tot_esc_pub"],
			"tot_esc_priv" => $arr_subnivel2["tot_esc_priv"],
			"tot_grp" => $arr_subnivel2["tot_grp"],
			"tot_grp_pub" => $arr_subnivel2["tot_grp_pub"],
			"tot_grp_priv" => $arr_subnivel2["tot_grp_priv"]
		];
	}
	//echo "<BR>";
	//echo print_r ($subnivel_comb),"<BR>";
	return $subnivel_comb;
}
function acum_totales($tipo, $titulo_fila, $arr_nivel1, $arr_nivel2, $arr_nivel3, $arr_nivel4)
{

	if ((strcmp($tipo, 'DIF_AMBOS')) == 0) {
		$acum_niveles = [
			"titulo_fila" => $titulo_fila,
			"tot_mat" => $arr_nivel1['tot_mat'] + $arr_nivel2['tot_mat'] + $arr_nivel3['tot_mat'] + $arr_nivel4['tot_mat'],
			"tot_mat_pub" => $arr_nivel1['tot_mat_pub'] + $arr_nivel2['tot_mat_pub'] + $arr_nivel3['tot_mat_pub'] + $arr_nivel4['tot_mat_pub'],
			"tot_mat_priv" => $arr_nivel1['tot_mat_priv'] + $arr_nivel2['tot_mat_priv'] + $arr_nivel3['tot_mat_priv'] + $arr_nivel4['tot_mat_priv'],
			"mat_h" => $arr_nivel1['mat_h'] + $arr_nivel2['mat_h'] + $arr_nivel3['mat_h'] + $arr_nivel4['mat_h'],
			"mat_h_pub" => $arr_nivel1['mat_h_pub'] + $arr_nivel2['mat_h_pub'] + $arr_nivel3['mat_h_pub'] + $arr_nivel4['mat_h_pub'],
			"mat_h_priv" => $arr_nivel1['mat_h_priv'] + $arr_nivel2['mat_h_priv'] + $arr_nivel3['mat_h_priv'] + $arr_nivel4['mat_h_priv'],
			"mat_m" => $arr_nivel1['mat_m'] + $arr_nivel2['mat_m'] + $arr_nivel3['mat_m'] + $arr_nivel4['mat_m'],
			"mat_m_pub" => $arr_nivel1['mat_m_pub'] + $arr_nivel2['mat_m_pub'] + $arr_nivel3['mat_m_pub'] + $arr_nivel4['mat_m_pub'],
			"mat_m_priv" => $arr_nivel1['mat_m_priv'] + $arr_nivel2['mat_m_priv'] + $arr_nivel3['mat_m_priv'] + $arr_nivel4['mat_m_priv'],
			"tot_doc" => $arr_nivel1['tot_doc'],
			"tot_doc_pub" => $arr_nivel1['tot_doc_pub'],
			"tot_doc_priv" => $arr_nivel1['tot_doc_priv'],
			"doc_h" => $arr_nivel1['doc_h'],
			"doc_h_pub" => $arr_nivel1['doc_h_pub'],
			"doc_h_priv" => $arr_nivel1['doc_h_priv'],
			"doc_m" => $arr_nivel1['doc_m'],
			"doc_m_pub" => $arr_nivel1['doc_m_pub'],
			"doc_m_priv" => $arr_nivel1['doc_m_priv'],
			"tot_esc" => $arr_nivel1['tot_esc'],
			"tot_esc_pub" => $arr_nivel1['tot_esc_pub'],
			"tot_esc_priv" => $arr_nivel1['tot_esc_priv'],
			"tot_grp" => $arr_nivel1['tot_grp'],
			"tot_grp_pub" => $arr_nivel1['tot_grp_pub'],
			"tot_grp_priv" => $arr_nivel1['tot_grp_priv']
		];
	} else if ((strcmp($tipo, 'DIF_DOCENTES')) == 0) {
		$acum_niveles = [
			"titulo_fila" => $titulo_fila,
			"tot_mat" => $arr_nivel1['tot_mat'] + $arr_nivel2['tot_mat'] + $arr_nivel3['tot_mat'] + $arr_nivel4['tot_mat'],
			"tot_mat_pub" => $arr_nivel1['tot_mat_pub'] + $arr_nivel2['tot_mat_pub'] + $arr_nivel3['tot_mat_pub'] + $arr_nivel4['tot_mat_pub'],
			"tot_mat_priv" => $arr_nivel1['tot_mat_priv'] + $arr_nivel2['tot_mat_priv'] + $arr_nivel3['tot_mat_priv'] + $arr_nivel4['tot_mat_priv'],
			"mat_h" => $arr_nivel1['mat_h'] + $arr_nivel2['mat_h'] + $arr_nivel3['mat_h'] + $arr_nivel4['mat_h'],
			"mat_h_pub" => $arr_nivel1['mat_h_pub'] + $arr_nivel2['mat_h_pub'] + $arr_nivel3['mat_h_pub'] + $arr_nivel4['mat_h_pub'],
			"mat_h_priv" => $arr_nivel1['mat_h_priv'] + $arr_nivel2['mat_h_priv'] + $arr_nivel3['mat_h_priv'] + $arr_nivel4['mat_h_priv'],
			"mat_m" => $arr_nivel1['mat_m'] + $arr_nivel2['mat_m'] + $arr_nivel3['mat_m'] + $arr_nivel4['mat_m'],
			"mat_m_pub" => $arr_nivel1['mat_m_pub'] + $arr_nivel2['mat_m_pub'] + $arr_nivel3['mat_m_pub'] + $arr_nivel4['mat_m_pub'],
			"mat_m_priv" => $arr_nivel1['mat_m_priv'] + $arr_nivel2['mat_m_priv'] + $arr_nivel3['mat_m_priv'] + $arr_nivel4['mat_m_priv'],
			"tot_doc" => $arr_nivel1['tot_doc'],
			"tot_doc_pub" => $arr_nivel1['tot_doc_pub'],
			"tot_doc_priv" => $arr_nivel1['tot_doc_priv'],
			"doc_h" => $arr_nivel1['doc_h'],
			"doc_h_pub" => $arr_nivel1['doc_h_pub'],
			"doc_h_priv" => $arr_nivel1['doc_h_priv'],
			"doc_m" => $arr_nivel1['doc_m'],
			"doc_m_pub" => $arr_nivel1['doc_m_pub'],
			"doc_m_priv" => $arr_nivel1['doc_m_priv'],
			"tot_esc" => $arr_nivel1['tot_esc'] + $arr_nivel2['tot_esc'] + $arr_nivel3['tot_esc'] + $arr_nivel4['tot_esc'],
			"tot_esc_pub" => $arr_nivel1['tot_esc_pub'] + $arr_nivel2['tot_esc_pub'] + $arr_nivel3['tot_esc_pub'] + $arr_nivel4['tot_esc_pub'],
			"tot_esc_priv" => $arr_nivel1['tot_esc_priv'] + $arr_nivel2['tot_esc_priv'] + $arr_nivel3['tot_esc_priv'] + $arr_nivel4['tot_esc_priv'],
			"tot_grp" => $arr_nivel1['tot_grp'] + $arr_nivel2['tot_grp'] + $arr_nivel3['tot_grp'] + $arr_nivel4['tot_grp'],
			"tot_grp_pub" => $arr_nivel1['tot_grp_pub'] + $arr_nivel2['tot_grp_pub'] + $arr_nivel3['tot_grp_pub'] + $arr_nivel4['tot_grp_pub'],
			"tot_grp_priv" => $arr_nivel1['tot_grp_priv'] + $arr_nivel2['tot_grp_priv'] + $arr_nivel3['tot_grp_priv'] + $arr_nivel4['tot_grp_priv']
		];
	} else {
		$acum_niveles = [
			"titulo_fila" => $titulo_fila,
			"tot_mat" => $arr_nivel1['tot_mat'] + $arr_nivel2['tot_mat'] + $arr_nivel3['tot_mat'] + $arr_nivel4['tot_mat'],
			"tot_mat_pub" => $arr_nivel1['tot_mat_pub'] + $arr_nivel2['tot_mat_pub'] + $arr_nivel3['tot_mat_pub'] + $arr_nivel4['tot_mat_pub'],
			"tot_mat_priv" => $arr_nivel1['tot_mat_priv'] + $arr_nivel2['tot_mat_priv'] + $arr_nivel3['tot_mat_priv'] + $arr_nivel4['tot_mat_priv'],
			"mat_h" => $arr_nivel1['mat_h'] + $arr_nivel2['mat_h'] + $arr_nivel3['mat_h'] + $arr_nivel4['mat_h'],
			"mat_h_pub" => $arr_nivel1['mat_h_pub'] + $arr_nivel2['mat_h_pub'] + $arr_nivel3['mat_h_pub'] + $arr_nivel4['mat_h_pub'],
			"mat_h_priv" => $arr_nivel1['mat_h_priv'] + $arr_nivel2['mat_h_priv'] + $arr_nivel3['mat_h_priv'] + $arr_nivel4['mat_h_priv'],
			"mat_m" => $arr_nivel1['mat_m'] + $arr_nivel2['mat_m'] + $arr_nivel3['mat_m'] + $arr_nivel4['mat_m'],
			"mat_m_pub" => $arr_nivel1['mat_m_pub'] + $arr_nivel2['mat_m_pub'] + $arr_nivel3['mat_m_pub'] + $arr_nivel4['mat_m_pub'],
			"mat_m_priv" => $arr_nivel1['mat_m_priv'] + $arr_nivel2['mat_m_priv'] + $arr_nivel3['mat_m_priv'] + $arr_nivel4['mat_m_priv'],
			"tot_doc" => $arr_nivel1['tot_doc'] + $arr_nivel2['tot_doc'] + $arr_nivel3['tot_doc'] + $arr_nivel4['tot_doc'],
			"tot_doc_pub" => $arr_nivel1['tot_doc_pub'] + $arr_nivel2['tot_doc_pub'] + $arr_nivel3['tot_doc_pub'] + $arr_nivel4['tot_doc_pub'],
			"tot_doc_priv" => $arr_nivel1['tot_doc_priv'] + $arr_nivel2['tot_doc_priv'] + $arr_nivel3['tot_doc_priv'] + $arr_nivel4['tot_doc_priv'],
			"doc_h" => $arr_nivel1['doc_h'] + $arr_nivel2['doc_h'] + $arr_nivel3['doc_h'] + $arr_nivel4['doc_h'],
			"doc_h_pub" => $arr_nivel1['doc_h_pub'] + $arr_nivel2['doc_h_pub'] + $arr_nivel3['doc_h_pub'] + $arr_nivel4['doc_h_pub'],
			"doc_h_priv" => $arr_nivel1['doc_h_priv'] + $arr_nivel2['doc_h_priv'] + $arr_nivel3['doc_h_priv'] + $arr_nivel4['doc_h_priv'],
			"doc_m" => $arr_nivel1['doc_m'] + $arr_nivel2['doc_m'] + $arr_nivel3['doc_m'] + $arr_nivel4['doc_m'],
			"doc_m_pub" => $arr_nivel1['doc_m_pub'] + $arr_nivel2['doc_m_pub'] + $arr_nivel3['doc_m_pub'] + $arr_nivel4['doc_m_pub'],
			"doc_m_priv" => $arr_nivel1['doc_m_priv'] + $arr_nivel2['doc_m_priv'] + $arr_nivel3['doc_m_priv'] + $arr_nivel4['doc_m_priv'],
			"tot_esc" => $arr_nivel1['tot_esc'] + $arr_nivel2['tot_esc'] + $arr_nivel3['tot_esc'] + $arr_nivel4['tot_esc'],
			"tot_esc_pub" => $arr_nivel1['tot_esc_pub'] + $arr_nivel2['tot_esc_pub'] + $arr_nivel3['tot_esc_pub'] + $arr_nivel4['tot_esc_pub'],
			"tot_esc_priv" => $arr_nivel1['tot_esc_priv'] + $arr_nivel2['tot_esc_priv'] + $arr_nivel3['tot_esc_priv'] + $arr_nivel4['tot_esc_priv'],
			"tot_grp" => $arr_nivel1['tot_grp'] + $arr_nivel2['tot_grp'] + $arr_nivel3['tot_grp'] + $arr_nivel4['tot_grp'],
			"tot_grp_pub" => $arr_nivel1['tot_grp_pub'] + $arr_nivel2['tot_grp_pub'] + $arr_nivel3['tot_grp_pub'] + $arr_nivel4['tot_grp_pub'],
			"tot_grp_priv" => $arr_nivel1['tot_grp_priv'] + $arr_nivel2['tot_grp_priv'] + $arr_nivel3['tot_grp_priv'] + $arr_nivel4['tot_grp_priv']
		];
	}
	//echo "<BR>";
	//echo print_r ($acum_niveles),"<BR>";
	return $acum_niveles;
}
function arreglos_datos($tipo_tabla, $link, $ini_ciclo, $filtro_extra, $filtro_pub, $filtro_priv)
{

	//INICIAL (ESCOLARIZADA)
	$total_gral_alum_ini = subnivel($link, "GENERAL ALUMNOS", $ini_ciclo, "gral_ini", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_gral_dir_ini = subnivel($link, "GENERAL DIR CGRP", $ini_ciclo, "gral_ini_dir_grp", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_ind_ini = subnivel($link, "INDIGENA", $ini_ciclo, "ind_ini", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_lac_ini = subnivel($link, "LACTANTES", $ini_ciclo, "lact_ini", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_mat_ini = subnivel($link, "MATERNAL", $ini_ciclo, "mater_ini", $filtro_extra, $filtro_pub, $filtro_priv);

	//TOTALES (INICIAL)
	$total_gral_ini = acum_totales("NA", "GENERAL INICIAL", $total_gral_alum_ini, $total_gral_dir_ini, subnivel_cero(), subnivel_cero());
	$total_ini = acum_totales("NA", "EDUCACIÓN INICIAL", $total_gral_ini, $total_ind_ini, subnivel_cero(), subnivel_cero());

	//INICIAL (NO ESCOLARIZADA)
	if (((strcmp($ini_ciclo, '21')) == 0) || ((strcmp($ini_ciclo, '22')) == 0)) {
		echo "<BR>En este año no se contaba con inicial comunitaria";
		$total_sedeq_comuni_ini = subnivel_cero();
		$total_sedeq_ne_ini = subnivel_cero();
		$total_sedeq_nesc_ini = subnivel_cero();
	} else {
		$total_sedeq_comuni_ini = subnivel($link, "COMUNITARIO", $ini_ciclo, "comuni_ini", $filtro_extra, $filtro_pub, $filtro_priv);
		$total_sedeq_ne_ini = subnivel($link, "NE", $ini_ciclo, "ne_ini", $filtro_extra, $filtro_pub, $filtro_priv);
		$total_sedeq_nesc_ini = acum_totales("NA", "INICIAL NE", $total_sedeq_comuni_ini, $total_sedeq_ne_ini, subnivel_cero(), subnivel_cero());
	}

	//CAM
	$total_usbq_esp_ini = subnivel($link, "INICIAL", $ini_ciclo, "especial_ini", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_esp_pree = subnivel($link, "INICIAL", $ini_ciclo, "especial_pree", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_esp_prim = subnivel($link, "INICIAL", $ini_ciclo, "especial_prim", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_esp_sec = subnivel($link, "INICIAL", $ini_ciclo, "especial_sec", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_esp_ftrab = subnivel($link, "INICIAL", $ini_ciclo, "especial_ftrab", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_esp_apoyo = subnivel($link, "INICIAL", $ini_ciclo, "especial_apoyo", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_esp_total = subnivel($link, "INICIAL", $ini_ciclo, "especial_tot", $filtro_extra, $filtro_pub, $filtro_priv);

	//USAER
	$total_sedeq_esp_usaer = subnivel($link, "ESPECIAL (USAER)", $ini_ciclo, "especial_usaer", $filtro_extra, $filtro_pub, $filtro_priv);

	//PREESCOLAR
	$total_gral_pree = subnivel($link, "GENERAL", $ini_ciclo, "gral_pree", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_ini1ro = subnivel($link, "INI1ro", $ini_ciclo, "ini_1ro_pree", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_ind_pree = subnivel($link, "INDIGENA", $ini_ciclo, "ind_pree", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_comuni_pree = subnivel($link, "COMUNITARIO", $ini_ciclo, "comuni_pree", $filtro_extra, $filtro_pub, $filtro_priv);

	//TOTALES (PREESCOLAR)
	$total_pree_gral = acum_totales("NA", "GENERAL", $total_gral_pree, $total_ini1ro, subnivel_cero(), subnivel_cero());
	$total_pree = acum_totales("NA", "EDUCACIÓN PREESCOLAR", $total_gral_pree, $total_ind_pree, $total_comuni_pree, $total_ini1ro);

	//PRIMARIA
	$total_gral_prim = subnivel($link, "GENERAL", $ini_ciclo, "gral_prim", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_ind_prim = subnivel($link, "INDIGENA", $ini_ciclo, "ind_prim", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_comuni_prim = subnivel($link, "COMUNITARIO", $ini_ciclo, "comuni_prim", $filtro_extra, $filtro_pub, $filtro_priv);

	//TOTALES (PRIMARIA)
	$total_prim = acum_totales("NA", "EDUCACIÓN PRIMARIA", $total_gral_prim, $total_ind_prim, $total_comuni_prim, subnivel_cero());

	//SECUNDARIA
	$total_gral_sec = subnivel($link, "GENERAL", $ini_ciclo, "gral_sec", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_comuni_sec = subnivel($link, "COMUNITARIO", $ini_ciclo, "comuni_sec", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_subn_gral_sec = subnivel($link, "GENERAL", $ini_ciclo, "sec_gral_gral", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_tele_sec = subnivel($link, "TELESECUNDARIA", $ini_ciclo, "sec_gral_tele", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_tec_sec = subnivel($link, "TECNICA", $ini_ciclo, "sec_gral_tec", $filtro_extra, $filtro_pub, $filtro_priv);

	//TOTALES (SECUNDARIA)
	$total_sec_gral = acum_totales("NA", "GENERAL", $total_subn_gral_sec, $total_comuni_sec, subnivel_cero(), subnivel_cero());
	$total_sec = acum_totales("NA", "EDUCACIÓN SECUNDARIA", $total_gral_sec, $total_comuni_sec, subnivel_cero(), subnivel_cero());
	$total_usbq_sec = acum_totales("NA", "EDUCACIÓN SECUNDARIA", $total_subn_gral_sec, $total_tec_sec, $total_tele_sec, $total_comuni_sec);

	//MEDIA SUPERIOR 
	$mod_msup = " AND (c_modalidad='ESCOLARIZADA' OR c_modalidad='MIXTA') ";
	$mod_nesc_msup = " AND (c_modalidad<>'ESCOLARIZADA' AND c_modalidad<>'MIXTA') ";

	//ESCOLARIZADA (MEDIA SUPERIOR)
	$total_esc_btecno_msup = subnivel($link, "BACHILLERATO TECNOLOGICO TOT", $ini_ciclo, "btecno_msup", $mod_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_msup, $filtro_priv . " " . $mod_msup);
	$total_esc_bgral_msup = subnivel($link, "BACHILLERATO GENERAL", $ini_ciclo, "bgral_msup", $mod_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_msup, $filtro_priv . " " . $mod_msup);
	$total_esc_btecno_tecno_msup = subnivel($link, "BACHILLERATO TECNOLOGICO", $ini_ciclo, "btecno_tecno_msup", $mod_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_msup, $filtro_priv . " " . $mod_msup);
	$total_esc_pbach_msup = subnivel($link, "PROFESIONAL TECNICO BACHILLER", $ini_ciclo, "btecno_pbach_msup", $mod_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_msup, $filtro_priv . " " . $mod_msup);
	$total_esc_ptecno_msup = subnivel($link, "PROFESIONAL TECNICO", $ini_ciclo, "btecno_ptecno_msup", $mod_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_msup, $filtro_priv . " " . $mod_msup);
	$total_esc_msup = acum_totales("NA", "EDUCACIÓN MEDIA SUPERIOR", $total_esc_bgral_msup, $total_esc_btecno_msup, subnivel_cero(), subnivel_cero());
	$total_usbq_esc_btecno_msup = acum_totales("NA", "BACHILLERATO TECNOLOGICO", $total_esc_btecno_tecno_msup, $total_esc_pbach_msup, subnivel_cero(), subnivel_cero());

	//NO ESCOLARIZADA (MEDIA SUPERIOR)
	$total_nesc_btecno_msup = subnivel($link, "BACHILLERATO TECNOLOGICO TOT", $ini_ciclo, "btecno_msup", $mod_nesc_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_nesc_msup, $filtro_priv . " " . $mod_nesc_msup);
	$total_nesc_bgral_msup = subnivel($link, "BACHILLERATO GENERAL", $ini_ciclo, "bgral_msup", $mod_nesc_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_nesc_msup, $filtro_priv . " " . $mod_nesc_msup);
	$total_nesc_btecno_tecno_msup = subnivel($link, "BACHILLERATO TECNOLOGICO", $ini_ciclo, "btecno_tecno_msup", $mod_nesc_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_nesc_msup, $filtro_priv . " " . $mod_nesc_msup);
	$total_nesc_pbach_msup = subnivel($link, "PROFESIONAL TECNICO BACHILLER", $ini_ciclo, "btecno_pbach_msup", $mod_nesc_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_nesc_msup, $filtro_priv . " " . $mod_nesc_msup);
	$total_nesc_ptecno_msup = subnivel($link, "PROFESIONAL TECNICO", $ini_ciclo, "btecno_ptecno_msup", $mod_nesc_msup . " " . $filtro_extra, $filtro_pub . " " . $mod_nesc_msup, $filtro_priv . " " . $mod_nesc_msup);
	$total_nesc_msup = acum_totales("NA", "EDUCACIÓN MEDIA SUPERIOR", $total_nesc_bgral_msup, $total_nesc_btecno_msup, subnivel_cero(), subnivel_cero());
	$total_usbq_nesc_btecno_msup = acum_totales("NA", "BACHILLERATO TECNOLOGICO", $total_nesc_btecno_tecno_msup, $total_nesc_pbach_msup, subnivel_cero(), subnivel_cero());


	// TOTALES (MEDIA SUPERIOR)
	$total_msup = acum_totales("NA", "EDUCACIÓN MEDIA SUPERIOR", $total_esc_msup, $total_nesc_msup, subnivel_cero(), subnivel_cero());
	$total_bgral_msup = acum_totales("NA", "BACHILLERATO GENERAL", $total_esc_bgral_msup, $total_nesc_bgral_msup, subnivel_cero(), subnivel_cero());
	$total_btecno_tecno_msup = acum_totales("NA", "BACHILLERATO TECNOLOGICO", $total_esc_btecno_tecno_msup, $total_nesc_btecno_tecno_msup, subnivel_cero(), subnivel_cero());
	$total_pbach_msup = acum_totales("NA", "PROFESIONAL TECNICO BACHILLER", $total_esc_pbach_msup, $total_nesc_pbach_msup, subnivel_cero(), subnivel_cero());
	$total_ptecno_msup = acum_totales("NA", "PROFESIONAL TECNICO", $total_esc_ptecno_msup, $total_nesc_ptecno_msup, subnivel_cero(), subnivel_cero());
	$total_usbq_btecno_msup = acum_totales("NA", "BACHILLERATO TECNOLOGICO", $total_usbq_esc_btecno_msup, $total_usbq_nesc_btecno_msup, subnivel_cero(), subnivel_cero());
	$total_usbq_escuelas_msup = subnivel($link, "ESCUELAS MEDIA SUPERIOR", $ini_ciclo, "plant_doc_esc_msup", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_msup = acum_totales("DIF_DOCENTES", "EDUCACIÓN MEDIA SUPERIOR", $total_usbq_escuelas_msup, $total_msup, subnivel_cero(), subnivel_cero());
	$sedeq_doc_plant_msup = subnivel($link, "DOCENTES MSUP", $ini_ciclo, "plant_doc_esc_msup", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_sedeq_msup = juntar_arreglos("DOCENTES MSUP", $total_msup, $sedeq_doc_plant_msup);



	$total_btecno_msup = acum_totales("NA", "BACHILLERATO TECNOLOGICO TOT", $total_esc_btecno_msup, $total_nesc_btecno_msup, subnivel_cero(), subnivel_cero());


	//SUPERIOR 
	$mod_sup = " AND c_modalidad='ESCOLARIZADA' ";
	$mod_nesc_sup = " AND c_modalidad<>'ESCOLARIZADA' ";

	//ESCOLARIZADA (SUPERIOR)
	$total_esc_lic_sup = mezcla_arreglos("AMBOS", "LICENCIATURA", "carr_lic_sup", "esc_lic_sup", "LICENCIATURA", "LICENCIATURA2", $link, $ini_ciclo, $mod_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_esc_normal_sup = mezcla_arreglos("AMBOS", "NORMAL", "carr_normal_sup", "esc_normal_sup", "NORMAL", "NORMAL2", $link, $ini_ciclo, $mod_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_esc_tecno_sup = mezcla_arreglos("AMBOS", "UNIVERSITARIA Y TECNOLOGICA", "carr_tecno_sup", "esc_tecno_sup", "UNIVERSITARIA Y TECNOLOGICA", "UNIVERSITARIA Y TECNOLOGICA2", $link, $ini_ciclo, $mod_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_esc_posg_sup = mezcla_arreglos("AMBOS", "POSGRADO", "posgr_sup", "esc_posgr_sup", "POSGRADO", "POSGRADO2", $link, $ini_ciclo, $mod_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_esc_comb_sup = mezcla_arreglos("AMBOS", "EDUCACIÓN SUPERIOR", "carr_lic_sup", "esc_carr_doc_sup", "SUPERIOR", "SUPERIOR2", $link, $ini_ciclo, $mod_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_esc_sup = acum_totales("DIF_AMBOS", "EDUCACIÓN SUPERIOR", $total_esc_comb_sup, $total_esc_posg_sup, subnivel_cero(), subnivel_cero());

	//NO ESCOLARIZADA (SUPERIOR)
	$total_nesc_lic_sup = mezcla_arreglos("AMBOS", "LICENCIATURA", "carr_lic_sup", "esc_nesc_lic_sup", "LICENCIATURA", "LICENCIATURA2", $link, $ini_ciclo, $mod_nesc_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_nesc_normal_sup = mezcla_arreglos("AMBOS", "NORMAL", "carr_normal_sup", "esc_nesc_normal_sup", "NORMAL", "NORMAL2", $link, $ini_ciclo, $mod_nesc_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_nesc_tecno_sup = mezcla_arreglos("AMBOS", "UNIVERSITARIA Y TECNOLOGICA", "carr_tecno_sup", "esc_nesc_tecno_sup", "UNIVERSITARIA Y TECNOLOGICA", "UNIVERSITARIA Y TECNOLOGICA2", $link, $ini_ciclo, $mod_nesc_sup . " " . $filtro_extra, $filtro_pub, $filtro_priv);
	$total_nesc_posg_sup = mezcla_arreglos("AMBOS", "POSGRADO", "posgr_sup", "esc_nesc_posgr_sup", "POSGRADO", "POSGRADO2", $link, $ini_ciclo, $mod_nesc_sup . " " . $filtro_extra, $filtro_pub . " " . $filtro_extra, $filtro_priv);
	$total_nesc_comb_sup = mezcla_arreglos("AMBOS", "EDUCACIÓN SUPERIOR", "carr_lic_sup", "esc_nesc_sup", "SUPERIOR", "SUPERIOR2", $link, $ini_ciclo, $mod_nesc_sup . " " . $filtro_extra, $filtro_pub . " " . $filtro_extra, $filtro_priv);
	$total_nesc_sup = acum_totales("DIF_AMBOS", "EDUCACIÓN SUPERIOR", $total_nesc_comb_sup, $total_nesc_posg_sup, subnivel_cero(), subnivel_cero());

	//TOTALES (SUPERIOR)
	$total_lic_sup = acum_totales("NA", "LICENCIATURA", $total_esc_lic_sup, $total_nesc_lic_sup, subnivel_cero(), subnivel_cero());
	$total_normal_sup = acum_totales("NA", "NORMAL", $total_esc_normal_sup, $total_nesc_normal_sup, subnivel_cero(), subnivel_cero());
	$total_tecno_sup = acum_totales("NA", "UNIVERSITARIA Y TECNOLOGICA", $total_esc_tecno_sup, $total_nesc_tecno_sup, subnivel_cero(), subnivel_cero());
	$total_posg_sup = acum_totales("NA", "POSGRADO", $total_esc_posg_sup, $total_nesc_posg_sup, subnivel_cero(), subnivel_cero());
	$total_sup = acum_totales("NA", "EDUCACIÓN SUPERIOR", $total_esc_sup, $total_nesc_sup, subnivel_cero(), subnivel_cero());

	$total_usbq_tsu_sup = mezcla_arreglos("AMBOS", "TECNICO SUPERIOR", "carr_usbq_tsu_sup", "esc_lic_sup", "LICENCIATURA", "LICENCIATURA2", $link, $ini_ciclo, $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_lic_sup = mezcla_arreglos("AMBOS", "TECNICO SUPERIOR", "carr_usbq_lic_sup", "esc_lic_sup", "LICENCIATURA", "LICENCIATURA2", $link, $ini_ciclo, $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_docentes_sup = subnivel($link, "DOCENTES", $ini_ciclo, "esc_docentes_sup", $filtro_extra, $filtro_pub, $filtro_priv);
	$total_usbq_sup = acum_totales("DIF_AMBOS", "EDUCACIÓN SUPERIOR", $total_usbq_docentes_sup, $total_sup, subnivel_cero(), subnivel_cero());


	$total_comb_sup = acum_totales("NA", "EDUCACIÓN SUPERIOR", $total_esc_comb_sup, $total_nesc_comb_sup, subnivel_cero(), subnivel_cero());

	//TOTALES SISTEMA

	//ACUM TABLA CIFRAS
	$total_basica = acum_totales("NA", "EDUCACIÓN BASICA", $total_ini, $total_pree, $total_prim, $total_sec);
	$total_escolarizada = acum_totales("NA", "MODALIDAD ESCOLARIZADA", $total_basica, $total_esc_msup, $total_esc_sup, subnivel_cero());
	$total_nesc = acum_totales("NA", "MODALIDAD NO ESCOLARIZADA", $total_nesc_msup, $total_nesc_sup, subnivel_cero(), subnivel_cero());
	$total_sistema = acum_totales("NA", "TOTAL DEL SISTEMA EDUCATIVO", $total_escolarizada, $total_nesc, subnivel_cero(), subnivel_cero());

	//ACUM TABLA USBQ
	$total_usbq_basica = acum_totales("NA", "EDUCACIÓN BASICA", $total_ini, $total_pree, $total_prim, $total_usbq_sec);
	$total_usbq_sistema = acum_totales("NA", "TOTAL SISTEMA", $total_usbq_esp_total, $total_usbq_basica, $total_usbq_msup, $total_usbq_sup);

	$sedeq_basica_s1 = acum_totales("NA", "INICIAL Y CAM", $total_ini, $total_sedeq_nesc_ini, $total_usbq_esp_total, subnivel_cero());
	$total_sedeq_basica = acum_totales("NA", "EDUCACIÓN BASICA", $sedeq_basica_s1, $total_pree, $total_prim, $total_sec);
	$total_sedeq_sistema = acum_totales("NA", "EDUCACIÓN BASICA", $total_sedeq_basica, $total_sedeq_msup, $total_sup, subnivel_cero());


	if ((strcmp($tipo_tabla, 'CIFRAS')) == 0) {
		$estadisticas = [
			"total_sistema" => $total_sistema,
			"total_basica" => $total_basica,
			"total_ini" => $total_ini,
			"total_gral_ini" => $total_gral_ini,
			"total_ind_ini" => $total_ind_ini,
			"total_pree" => $total_pree,
			"total_pree_gral" => $total_pree_gral,
			"total_ind_pree" => $total_ind_pree,
			"total_comuni_pree" => $total_comuni_pree,
			"total_prim" => $total_prim,
			"total_gral_prim" => $total_gral_prim,
			"total_ind_prim" => $total_ind_prim,
			"total_comuni_prim" => $total_comuni_prim,
			"total_sec" => $total_sec,
			"total_sec_gral" => $total_sec_gral,
			"total_tele_sec" => $total_tele_sec,
			"total_tec_sec" => $total_tec_sec,
			"total_msup" => $total_msup,
			"total_bgral_msup" => $total_bgral_msup,
			"total_btecno_tecno_msup" => $total_btecno_tecno_msup,
			"total_pbach_msup" => $total_pbach_msup,
			"total_ptecno_msup" => $total_ptecno_msup,
			"total_sup" => $total_sup,
			"total_lic_sup" => $total_lic_sup,
			"total_normal_sup" => $total_normal_sup,
			"total_tecno_sup" => $total_tecno_sup,
			"total_posg_sup" => $total_posg_sup,
			"total_escolarizada" => $total_escolarizada,
			"total_esc_bgral_msup" => $total_esc_bgral_msup,
			"total_esc_btecno_tecno_msup" => $total_esc_btecno_tecno_msup,
			"total_esc_pbach_msup" => $total_esc_pbach_msup,
			"total_esc_ptecno_msup" => $total_esc_ptecno_msup,
			"total_esc_msup" => $total_esc_msup,
			"total_esc_lic_sup" => $total_esc_lic_sup,
			"total_esc_normal_sup" => $total_esc_normal_sup,
			"total_esc_tecno_sup" => $total_esc_tecno_sup,
			"total_esc_posg_sup" => $total_esc_posg_sup,
			"total_esc_sup" => $total_esc_sup,
			"total_nesc" => $total_nesc,
			"total_nesc_bgral_msup" => $total_nesc_bgral_msup,
			"total_nesc_btecno_tecno_msup" => $total_nesc_btecno_tecno_msup,
			"total_nesc_pbach_msup" => $total_nesc_pbach_msup,
			"total_nesc_ptecno_msup" => $total_nesc_ptecno_msup,
			"total_nesc_msup" => $total_nesc_msup,
			"total_nesc_lic_sup" => $total_nesc_lic_sup,
			"total_nesc_normal_sup" => $total_nesc_normal_sup,
			"total_nesc_tecno_sup" => $total_nesc_tecno_sup,
			"total_nesc_posg_sup" => $total_nesc_posg_sup,
			"total_nesc_sup" => $total_nesc_sup
		];
	}
	if ((strcmp($tipo_tabla, 'USBQ_SERVICIO')) == 0) {
		$estadisticas = [
			"total_usbq_esp_total" => $total_usbq_esp_total,
			"total_usbq_esp_ini" => $total_usbq_esp_ini,
			"total_usbq_esp_pree" => $total_usbq_esp_pree,
			"total_usbq_esp_prim" => $total_usbq_esp_prim,
			"total_usbq_esp_sec" => $total_usbq_esp_sec,
			"total_usbq_esp_ftrab" => $total_usbq_esp_ftrab,
			"total_usbq_esp_apoyo" => $total_usbq_esp_apoyo,
			"total_usbq_basica" => $total_usbq_basica,
			"total_ini" => $total_ini,
			"total_usbq_lac_ini" => $total_usbq_lac_ini,
			"total_usbq_mat_ini" => $total_usbq_mat_ini,
			"total_ind_ini" => $total_ind_ini,
			"total_pree" => $total_pree,
			"total_pree_gral" => $total_pree_gral,
			"total_ind_pree" => $total_ind_pree,
			"total_comuni_pree" => $total_comuni_pree,
			"total_prim" => $total_prim,
			"total_gral_prim" => $total_gral_prim,
			"total_ind_prim" => $total_ind_prim,
			"total_comuni_prim" => $total_comuni_prim,
			"total_usbq_sec" => $total_usbq_sec,
			"total_subn_gral_sec" => $total_subn_gral_sec,
			"total_tec_sec" => $total_tec_sec,
			"total_tele_sec" => $total_tele_sec,
			"total_comuni_sec" => $total_comuni_sec,
			"total_usbq_msup" => $total_usbq_msup,
			"total_bgral_msup" => $total_bgral_msup,
			"total_usbq_btecno_msup" => $total_usbq_btecno_msup,
			"total_ptecno_msup" => $total_ptecno_msup,
			"total_usbq_sup" => $total_usbq_sup,
			"total_usbq_tsu_sup" => $total_usbq_tsu_sup,
			"total_usbq_lic_sup" => $total_usbq_lic_sup,
			"total_posg_sup" => $total_posg_sup,
			"total_usbq_sistema" => $total_usbq_sistema,
		];

	}
	if ((strcmp($tipo_tabla, 'USBQ_MUNICIPIO')) == 0) {
		$estadisticas = [
			"total_usbq_esp_total" => $total_usbq_esp_total,
			"total_ini" => $total_ini,
			"total_pree" => $total_pree,
			"total_prim" => $total_prim,
			"total_usbq_sec" => $total_usbq_sec,
			"total_usbq_msup" => $total_usbq_msup,
			"total_usbq_sup" => $total_usbq_sup,
			"total_usbq_sistema" => $total_usbq_sistema,
		];
		/* echo "<BR>";
		echo print_r ($estadisticas),"<BR>"; */
	}
	if ((strcmp($tipo_tabla, 'NIVEL_PANO')) == 0) {
		$estadisticas = [
			"total_ini" => $total_ini,
			"total_gral_ini" => $total_gral_ini,
			"total_ind_ini" => $total_ind_ini,
			"total_sedeq_nesc_ini" => $total_sedeq_nesc_ini,
			"total_sedeq_comuni_ini" => $total_sedeq_comuni_ini,
			"total_sedeq_ne_ini" => $total_sedeq_ne_ini,
			"total_usbq_esp_total" => $total_usbq_esp_total,
			"total_sedeq_esp_usaer" => $total_sedeq_esp_usaer,
			"total_pree" => $total_pree,
			"total_pree_gral" => $total_pree_gral,
			"total_comuni_pree" => $total_comuni_pree,
			"total_ind_pree" => $total_ind_pree,
			"total_prim" => $total_prim,
			"total_gral_prim" => $total_gral_prim,
			"total_comuni_prim" => $total_comuni_prim,
			"total_ind_prim" => $total_ind_prim,
			"total_sec" => $total_sec,
			"total_subn_gral_sec" => $total_subn_gral_sec,
			"total_comuni_sec" => $total_comuni_sec,
			"total_tec_sec" => $total_tec_sec,
			"total_tele_sec" => $total_tele_sec,
			"total_sedeq_basica" => $total_sedeq_basica,
			"total_sedeq_msup" => $total_sedeq_msup,
			"total_bgral_msup" => $total_bgral_msup,
			"total_btecno_msup" => $total_btecno_msup,
			"total_lic_sup" => $total_lic_sup,
			"total_usbq_tsu_sup" => $total_usbq_tsu_sup,
			"total_usbq_lic_sup" => $total_usbq_lic_sup,
			"total_posg_sup" => $total_posg_sup,
			"total_usbq_sup" => $total_usbq_sup,
			"total_sedeq_sistema" => $total_sedeq_sistema,
			"sedeq_doc_plant_msup" => $sedeq_doc_plant_msup
		];
	}
	return $estadisticas;
}

//CIFRAS DE BOLSILLO
function encabezado_tabla_cifras($ini_ciclo)
{
	echo "<BR>";
	echo "<BR>";
	echo "CIFRAS DE BOLSILLO";
	echo "<BR>20" . $ini_ciclo . "-20" . ($ini_ciclo + 1) . "<BR>";
	echo "ESTADISTICA EDUCATIVA";
	echo "<TABLE BORDER='1' width='100%' CLASS='tb_borde'>";
	echo "<TR>";
	echo "<TD ROWSPAN='2'>TIPO/MODALIDAD/NIVEL</TD>";
	echo "<TD COLSPAN='3'>ALUMNOS</TD>";
	echo "<TD ROWSPAN='2'>DOCENTES</TD>";
	echo "<TD ROWSPAN='2'>ESCUELAS</TD>";
	echo "</TR>";
	echo "<TR>";
	echo "<TD>TOTAL</TD>";
	echo "<TD>MUJERES</TD>";
	echo "<TD>HOMBRES</TD>";
	echo "</TR>";
}
function fila_subnivel($arr_subnivel)
{
	echo "<TR>";
	if ($arr_subnivel['titulo_fila'] == 0) {
		echo "<TD VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD VALIGN='CENTER' CLASS='num_estadis'>" . $arr_subnivel['titulo_fila'] . "</TD>";
	}
	if ($arr_subnivel['tot_mat'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_subnivel['tot_mat'], 0, '.', ',') . "</TD>";
	}
	if ($arr_subnivel['mat_m'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_subnivel['mat_m'], 0, '.', ',') . "</TD>";
	}
	if ($arr_subnivel['mat_h'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_subnivel['mat_h'], 0, '.', ',') . "</TD>";
	}
	if ($arr_subnivel['tot_doc'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_subnivel['tot_doc'], 0, '.', ',') . "</TD>";
	}
	if ($arr_subnivel['tot_esc'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_subnivel['tot_esc'], 0, '.', ',') . "</TD>";
	}
	echo "</TR>";
}
function fila_control($arr_nivel)
{
	echo "<TR>";
	echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>PÚBLICO</TD>";
	if ($arr_nivel['tot_mat_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_mat_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_m_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_m_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_h_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_h_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_doc_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_doc_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_esc_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_esc_pub'], 0, '.', ',') . "</TD>";
	}
	echo "</TR>";
	echo "<TR>";
	echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>PRIVADO</TD>";
	if ($arr_nivel['tot_mat_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_mat_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_m_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_m_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_h_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_h_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_doc_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_doc_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_esc_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_esc_priv'], 0, '.', ',') . "</TD>";
	}
	echo "</TR>";
}
function mostrar_tabla_cifras($datos_estadisticos)
{

	fila_subnivel($datos_estadisticos["total_sistema"]);
	fila_control($datos_estadisticos["total_sistema"]);

	fila_subnivel($datos_estadisticos["total_basica"]);
	fila_control($datos_estadisticos["total_basica"]);

	fila_subnivel($datos_estadisticos["total_ini"]);
	fila_subnivel($datos_estadisticos["total_gral_ini"]);
	fila_subnivel($datos_estadisticos["total_ind_ini"]);
	fila_control($datos_estadisticos["total_ini"]);

	fila_subnivel($datos_estadisticos["total_pree"]);
	fila_subnivel($datos_estadisticos["total_pree_gral"]);
	fila_subnivel($datos_estadisticos["total_ind_pree"]);
	fila_subnivel($datos_estadisticos["total_comuni_pree"]);
	fila_control($datos_estadisticos["total_pree"]);

	fila_subnivel($datos_estadisticos["total_prim"]);
	fila_subnivel($datos_estadisticos["total_gral_prim"]);
	fila_subnivel($datos_estadisticos["total_ind_prim"]);
	fila_subnivel($datos_estadisticos["total_comuni_prim"]);
	fila_control($datos_estadisticos["total_prim"]);

	fila_subnivel($datos_estadisticos["total_sec"]);
	fila_subnivel($datos_estadisticos["total_sec_gral"]);
	fila_subnivel($datos_estadisticos["total_tele_sec"]);
	fila_subnivel($datos_estadisticos["total_tec_sec"]);
	fila_control($datos_estadisticos["total_sec"]);

	fila_subnivel($datos_estadisticos["total_msup"]);
	fila_subnivel($datos_estadisticos["total_bgral_msup"]);
	fila_subnivel($datos_estadisticos["total_btecno_tecno_msup"]);
	fila_subnivel($datos_estadisticos["total_pbach_msup"]);
	fila_subnivel($datos_estadisticos["total_ptecno_msup"]);
	fila_control($datos_estadisticos["total_msup"]);

	fila_subnivel($datos_estadisticos["total_sup"]);
	fila_subnivel($datos_estadisticos["total_lic_sup"]);
	fila_subnivel($datos_estadisticos["total_normal_sup"]);
	fila_subnivel($datos_estadisticos["total_tecno_sup"]);
	fila_subnivel($datos_estadisticos["total_posg_sup"]);
	fila_control($datos_estadisticos["total_sup"]);

	fila_subnivel($datos_estadisticos["total_escolarizada"]);
	fila_control($datos_estadisticos["total_escolarizada"]);

	fila_subnivel($datos_estadisticos["total_esc_msup"]);
	fila_subnivel($datos_estadisticos["total_esc_bgral_msup"]);
	fila_subnivel($datos_estadisticos["total_esc_btecno_tecno_msup"]);
	fila_subnivel($datos_estadisticos["total_esc_pbach_msup"]);
	fila_subnivel($datos_estadisticos["total_esc_ptecno_msup"]);
	fila_control($datos_estadisticos["total_esc_msup"]);

	fila_subnivel($datos_estadisticos["total_esc_sup"]);
	fila_subnivel($datos_estadisticos["total_esc_lic_sup"]);
	fila_subnivel($datos_estadisticos["total_esc_normal_sup"]);
	fila_subnivel($datos_estadisticos["total_esc_tecno_sup"]);
	fila_subnivel($datos_estadisticos["total_esc_posg_sup"]);
	fila_control($datos_estadisticos["total_esc_sup"]);

	fila_subnivel($datos_estadisticos["total_nesc"]);
	fila_control($datos_estadisticos["total_nesc"]);

	fila_subnivel($datos_estadisticos["total_nesc_msup"]);
	fila_subnivel($datos_estadisticos["total_nesc_bgral_msup"]);
	fila_subnivel($datos_estadisticos["total_nesc_btecno_tecno_msup"]);
	fila_subnivel($datos_estadisticos["total_nesc_pbach_msup"]);
	fila_subnivel($datos_estadisticos["total_nesc_ptecno_msup"]);
	fila_control($datos_estadisticos["total_nesc_msup"]);

	fila_subnivel($datos_estadisticos["total_nesc_sup"]);
	fila_subnivel($datos_estadisticos["total_nesc_lic_sup"]);
	fila_subnivel($datos_estadisticos["total_nesc_normal_sup"]);
	fila_subnivel($datos_estadisticos["total_nesc_tecno_sup"]);
	fila_subnivel($datos_estadisticos["total_nesc_posg_sup"]);
	fila_control($datos_estadisticos["total_nesc_sup"]);

	echo "</TABLE>";
}

//USEBEQ

//USEBEQ (SERVICIO)
function encabezado_tablas_usbq($titulo, $ini_ciclo)
{
	echo "<BR>";
	echo "<BR>";
	echo $titulo;
	echo "<BR>20" . $ini_ciclo . "-20" . ($ini_ciclo + 1);
	echo "<BR>(USEBEQ)";
	echo "<TABLE BORDER='1' width='100%' CLASS='tb_borde'>";
	echo "<TR>";
	echo "<TD ROWSPAN='2'>NIVEL / SERVICIO</TD>";
	echo "<TD COLSPAN='3'>ALUMNOS</TD>";
	echo "<TD ROWSPAN='2'>GRUPOS</TD>";
	echo "<TD ROWSPAN='2'>DOCENTES</TD>";
	echo "<TD ROWSPAN='2'>ESCUELAS</TD>";
	echo "</TR>";

	echo "<TR>";
	echo "<TD>TOTAL</TD>";
	echo "<TD>HOMBRES</TD>";
	echo "<TD>MUJERES</TD>";
	echo "</TR>";
}
function fila_servicio($control, $titulo_fila, $arr_servicio)
{

	if ((strcmp($control, 'PUB')) == 0) {
		echo "<TR>";
		echo "<TD>" . $titulo_fila . "</TD>";
		if ($arr_servicio['tot_mat_pub'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_mat_pub'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['mat_h_pub'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['mat_h_pub'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['mat_m_pub'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['mat_m_pub'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_grp_pub'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_grp_pub'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_doc_pub'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_doc_pub'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_esc_pub'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_esc_pub'], 0, '.', ',') . "</TD>";
		}
		echo "</TR>";
	}
	if ((strcmp($control, 'PRIV')) == 0) {
		echo "<TR>";
		echo "<TD>" . $titulo_fila . "</TD>";
		if ($arr_servicio['tot_mat_priv'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_mat_priv'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['mat_h_priv'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['mat_h_priv'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['mat_m_priv'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['mat_m_priv'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_grp_priv'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_grp_priv'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_doc_priv'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_doc_priv'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_esc_priv'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_esc_priv'], 0, '.', ',') . "</TD>";
		}
		echo "</TR>";
	}
	if ((strcmp($control, 'AMBOS')) == 0) {
		echo "<TR>";
		echo "<TD>" . $titulo_fila . "</TD>";
		if ($arr_servicio['tot_mat'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_mat'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['mat_h'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['mat_h'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['mat_m'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['mat_m'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_grp'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_grp'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_doc'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_doc'], 0, '.', ',') . "</TD>";
		}
		if ($arr_servicio['tot_esc'] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_servicio['tot_esc'], 0, '.', ',') . "</TD>";
		}
		echo "</TR>";
	}
}
function mostrar_tabla_servicio_usbq($link, $datos_estadisticos, $filtro_pub, $filtro_priv)
{

	fila_servicio("AMBOS", "ESPECIAL-CAM", $datos_estadisticos["total_usbq_esp_total"]);
	fila_servicio("AMBOS", "INICIAL", $datos_estadisticos["total_usbq_esp_ini"]);
	fila_servicio("AMBOS", "PREESCOLAR", $datos_estadisticos["total_usbq_esp_pree"]);
	fila_servicio("AMBOS", "PRIMARIA", $datos_estadisticos["total_usbq_esp_prim"]);
	fila_servicio("AMBOS", "SECUNDARIA", $datos_estadisticos["total_usbq_esp_sec"]);
	fila_servicio("AMBOS", "FORMACION PARA EL TRABAJO", $datos_estadisticos["total_usbq_esp_ftrab"]);
	fila_servicio("AMBOS", "APOYO COMPLEMENTARIO", $datos_estadisticos["total_usbq_esp_apoyo"]);

	fila_servicio("AMBOS", "BASICA", $datos_estadisticos["total_usbq_basica"]);

	fila_servicio("AMBOS", "INICIAL", $datos_estadisticos["total_ini"]);
	fila_servicio("AMBOS", "LACTANTES", $datos_estadisticos["total_usbq_lac_ini"]);
	fila_servicio("AMBOS", "MATERNAL", $datos_estadisticos["total_usbq_mat_ini"]);
	fila_servicio("AMBOS", "INDIGENA", $datos_estadisticos["total_ind_ini"]);

	fila_servicio("AMBOS", "PREESCOLAR", $datos_estadisticos["total_pree"]);
	fila_servicio("AMBOS", "GENERAL", $datos_estadisticos["total_pree_gral"]);
	fila_servicio("AMBOS", "INDIGENA", $datos_estadisticos["total_ind_pree"]);
	fila_servicio("AMBOS", "COMUNITARIO", $datos_estadisticos["total_comuni_pree"]);

	fila_servicio("AMBOS", "PRIMARIA", $datos_estadisticos["total_prim"]);
	fila_servicio("AMBOS", "GENERAL", $datos_estadisticos["total_gral_prim"]);
	fila_servicio("AMBOS", "INDIGENA", $datos_estadisticos["total_ind_prim"]);
	fila_servicio("AMBOS", "COMUNITARIO", $datos_estadisticos["total_comuni_prim"]);

	fila_servicio("AMBOS", "SECUNDARIA", $datos_estadisticos["total_usbq_sec"]);
	fila_servicio("AMBOS", "GENERAL", $datos_estadisticos["total_subn_gral_sec"]);
	fila_servicio("AMBOS", "TECNICA", $datos_estadisticos["total_tec_sec"]);
	fila_servicio("AMBOS", "TELESECUNDARIA", $datos_estadisticos["total_tele_sec"]);
	fila_servicio("AMBOS", "COMUNITARIO", $datos_estadisticos["total_comuni_sec"]);

	fila_servicio("AMBOS", "MEDIA SUPERIOR", $datos_estadisticos["total_usbq_msup"]);
	fila_servicio("AMBOS", "BACHILLERATO GENERAL", $datos_estadisticos["total_bgral_msup"]);
	fila_servicio("AMBOS", "BACHILLERATO TECNOLOGICO", $datos_estadisticos["total_usbq_btecno_msup"]);
	fila_servicio("AMBOS", "PROFESIONAL TECNICO", $datos_estadisticos["total_ptecno_msup"]);

	fila_servicio("AMBOS", "SUPERIOR", $datos_estadisticos["total_usbq_sup"]);
	fila_servicio("AMBOS", "TECNICO SUPERIOR", $datos_estadisticos["total_usbq_tsu_sup"]);
	fila_servicio("AMBOS", "LICENCIATURA", $datos_estadisticos["total_usbq_lic_sup"]);
	fila_servicio("AMBOS", "POSGRADO", $datos_estadisticos["total_posg_sup"]);

	fila_servicio("AMBOS", "TOTAL SISTEMA", $datos_estadisticos["total_usbq_sistema"]);

	echo "</TABLE>";
}
//USEBEQ (NIVEL)
function mostrar_tabla_nivel_usbq($link, $control, $datos_estadisticos, $filtro_pub, $filtro_priv)
{

	fila_servicio($control, "ESPECIAL-CAM", $datos_estadisticos["total_usbq_esp_total"]);
	fila_servicio($control, "BASICA", $datos_estadisticos["total_usbq_basica"]);
	fila_servicio($control, "INICIAL", $datos_estadisticos["total_ini"]);
	fila_servicio($control, "PREESCOLAR", $datos_estadisticos["total_pree"]);
	fila_servicio($control, "PRIMARIA", $datos_estadisticos["total_prim"]);
	fila_servicio($control, "SECUNDARIA", $datos_estadisticos["total_usbq_sec"]);
	fila_servicio($control, "MEDIA SUPERIOR", $datos_estadisticos["total_usbq_msup"]);
	fila_servicio($control, "BACHILLERATO GENERAL", $datos_estadisticos["total_bgral_msup"]);
	fila_servicio($control, "BACHILLERATO TECNOLOGICO", $datos_estadisticos["total_usbq_btecno_msup"]);
	fila_servicio($control, "PROFESIONAL TECNICO", $datos_estadisticos["total_ptecno_msup"]);
	fila_servicio($control, "SUPERIOR", $datos_estadisticos["total_usbq_sup"]);
	fila_servicio($control, "TECNICO SUPERIOR", $datos_estadisticos["total_usbq_tsu_sup"]);
	fila_servicio($control, "LICENCIATURA", $datos_estadisticos["total_usbq_lic_sup"]);
	fila_servicio($control, "POSGRADO", $datos_estadisticos["total_posg_sup"]);
	fila_servicio($control, "TOTAL SISTEMA", $datos_estadisticos["total_usbq_sistema"]);

	echo "</TABLE>";
}
//USEBEQ (MUNICIPIO)
function nombre_municipio($num_munic)
{
	$nom_munic = [
		"1" => "AMEALCO DE BONFIL",
		"2" => "PINAL DE AMOLES",
		"3" => "ARROYO SECO",
		"4" => "CADEREYTA DE MONTES",
		"5" => "COLÓN",
		"6" => "CORREGIDORA",
		"7" => "EZEQUIEL MONTES",
		"8" => "HUIMILPAN",
		"9" => "JALPAN DE SERRA",
		"10" => "LANDA DE MATAMOROS",
		"11" => "EL MARQUÉS",
		"12" => "PEDRO ESCOBEDO",
		"13" => "PEÑAMILLER",
		"14" => "QUERÉTARO",
		"15" => "SAN JOAQUÍN",
		"16" => "SAN JUAN DEL RÍO",
		"17" => "TEQUISQUIAPAN",
		"18" => "TOLIMÁN"
	];

	return $nom_munic[$num_munic];
}
function armar_tabla_munic_usbq($link, $nombre_munic, $control, $datos_estadisticos, $filtro_pub, $filtro_priv)
{

	fila_servicio($control, $nombre_munic, $datos_estadisticos["total_usbq_sistema"]);
	fila_servicio($control, "ESPECIAL-CAM", $datos_estadisticos["total_usbq_esp_total"]);
	fila_servicio($control, "INICIAL", $datos_estadisticos["total_ini"]);
	fila_servicio($control, "PREESCOLAR", $datos_estadisticos["total_pree"]);
	fila_servicio($control, "PRIMARIA", $datos_estadisticos["total_prim"]);
	fila_servicio($control, "SECUNDARIA", $datos_estadisticos["total_usbq_sec"]);
	fila_servicio($control, "MEDIA SUPERIOR", $datos_estadisticos["total_usbq_msup"]);
	fila_servicio($control, "SUPERIOR", $datos_estadisticos["total_usbq_sup"]);

}
function mostrar_tabla_munic_usbq($control, $link, $ini_ciclo, $filtro_pub, $filtro_priv)
{

	for ($num_munic = 1; $num_munic <= 18; $num_munic++) {

		$filtro_mun = " AND cv_mun='" . $num_munic . "' ";
		$nombre_munic = nombre_municipio($num_munic);
		//echo $nombre_munic;
		armar_tabla_munic_usbq($link, $nombre_munic, $control, arreglos_datos("USBQ_MUNICIPIO", $link, $ini_ciclo, $filtro_mun, $filtro_pub, $filtro_priv), $filtro_pub, $filtro_priv);
	}
	echo "</TABLE>";
}

function encabezado_pan_dip10($ini_ciclo)
{
	echo "<BR>";
	echo "<BR>";
	echo "PANORAMA SEDEQ";
	echo "<BR>";
	echo "ESTADÍSTICA DE INICIO DE CICLO ESCOLAR";
	echo "<BR>20" . $ini_ciclo . "-20" . ($ini_ciclo + 1);
	echo "<TABLE BORDER='1' width='100%' CLASS='tb_borde'>";
	echo "<TR>";
	echo "<TD ROWSPAN='2'>TIPO EDUCATIVO</TD>";
	echo "<TD COLSPAN='3'>ALUMNOS</TD>";
	echo "<TD COLSPAN='3'>DOCENTES</TD>";
	echo "<TD COLSPAN='3'>ESCUELAS</TD>";
	echo "<TD COLSPAN='7'>ALUMNOS</TD>";
	echo "<TD COLSPAN='7'>DOCENTES</TD>";


	echo "</TR>";
	echo "<TR>";
	echo "<TD>TOTAL</TD>";
	echo "<TD>HOMBRES</TD>";
	echo "<TD>MUJERES</TD>";
	echo "<TD>TOTAL</TD>";
	echo "<TD>HOMBRES</TD>";
	echo "<TD>MUJERES</TD>";
	echo "<TD>TOTAL</TD>";
	echo "<TD>PÚBLICAS</TD>";
	echo "<TD>PRIVADAS</TD>";
	echo "<TD>TOTAL</TD>";
	echo "<TD>PÚBLICAS</TD>";
	echo "<TD>PÚB H</TD>";
	echo "<TD>PÚB M</TD>";
	echo "<TD>PRIVADAS</TD>";
	echo "<TD>PRIV H</TD>";
	echo "<TD>PRIV M</TD>";
	echo "<TD>TOTAL</TD>";
	echo "<TD>PÚBLICAS</TD>";
	echo "<TD>PÚB H</TD>";
	echo "<TD>PÚB M</TD>";
	echo "<TD>PRIVADAS</TD>";
	echo "<TD>PRIV H</TD>";
	echo "<TD>PRIV M</TD>";

	echo "</TR>";
}
function fila_pan_dip10($titulo_fila, $nivel, $arr_datos)
{
	$arr_nivel = $arr_datos[$nivel];
	echo "<TR>";
	echo "<TD>" . $titulo_fila . "</TD>";
	if ($arr_nivel['tot_mat'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_mat'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_h'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_h'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_m'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_m'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_doc'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_doc'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['doc_h'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['doc_h'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['doc_m'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['doc_m'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_esc'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_esc'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_esc_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_esc_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_esc_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_esc_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_mat'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_mat'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_mat_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_mat_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_h_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_h_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_m_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_m_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_mat_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_mat_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_h_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_h_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['mat_m_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['mat_m_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_doc'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_doc'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_doc_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_doc_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['doc_h_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['doc_h_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['doc_m_pub'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['doc_m_pub'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['tot_doc_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['tot_doc_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['doc_h_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['doc_h_priv'], 0, '.', ',') . "</TD>";
	}
	if ($arr_nivel['doc_m_priv'] == 0) {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>-</TD>";
	} else {
		echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='num_estadis'>" . number_format($arr_nivel['doc_m_priv'], 0, '.', ',') . "</TD>";
	}

	echo "</TR>";
}
function juntar_arreglos($titulo_fila, $arr_subnivel1, $arr_subnivel2)
{

	$subnivel_comb = [
		"titulo_fila" => $titulo_fila,
		"tot_mat" => $arr_subnivel1["tot_mat"],
		"tot_mat_pub" => $arr_subnivel1["tot_mat_pub"],
		"tot_mat_priv" => $arr_subnivel1["tot_mat_priv"],
		"mat_h" => $arr_subnivel1["mat_h"],
		"mat_h_pub" => $arr_subnivel1["mat_h_pub"],
		"mat_h_priv" => $arr_subnivel1["mat_h_priv"],
		"mat_m" => $arr_subnivel1["mat_m"],
		"mat_m_pub" => $arr_subnivel1["mat_m_pub"],
		"mat_m_priv" => $arr_subnivel1["mat_m_priv"],
		"tot_doc" => $arr_subnivel2["tot_doc"],
		"tot_doc_pub" => $arr_subnivel2["tot_doc_pub"],
		"tot_doc_priv" => $arr_subnivel2["tot_doc_priv"],
		"doc_h" => $arr_subnivel2["doc_h"],
		"doc_h_pub" => $arr_subnivel2["doc_h_pub"],
		"doc_h_priv" => $arr_subnivel2["doc_h_priv"],
		"doc_m" => $arr_subnivel2["doc_m"],
		"doc_m_pub" => $arr_subnivel2["doc_m_pub"],
		"doc_m_priv" => $arr_subnivel2["doc_m_priv"],
		"tot_esc" => $arr_subnivel2["tot_esc"],
		"tot_esc_pub" => $arr_subnivel2["tot_esc_pub"],
		"tot_esc_priv" => $arr_subnivel2["tot_esc_priv"],
		"tot_grp" => $arr_subnivel2["tot_grp"],
		"tot_grp_pub" => $arr_subnivel2["tot_grp_pub"],
		"tot_grp_priv" => $arr_subnivel2["tot_grp_priv"]
	];
	//echo "<BR>";
	//echo print_r ($subnivel_comb),"<BR>";
	return $subnivel_comb;
}
function panorama_dip10($link, $ini_ciclo, $sin_filtro_extra, $filtro_pub, $filtro_priv)
{

	$arr_datos_est = arreglos_datos("NIVEL_PANO", $link, $ini_ciclo, $sin_filtro_extra, $filtro_pub, $filtro_priv);
	fila_pan_dip10("INICIAL(Escolarizado)", "total_ini", $arr_datos_est);
	fila_pan_dip10("GENERAL", "total_gral_ini", $arr_datos_est);
	fila_pan_dip10("INDIGENA", "total_ind_ini", $arr_datos_est);
	fila_pan_dip10("INICIAL(No Escolarizado)", "total_sedeq_nesc_ini", $arr_datos_est);
	fila_pan_dip10("COMUNITARIO", "total_sedeq_comuni_ini", $arr_datos_est);
	fila_pan_dip10("NO ESCOLARIZADO", "total_sedeq_ne_ini", $arr_datos_est);
	fila_pan_dip10("ESPECIAL (CAM)", "total_usbq_esp_total", $arr_datos_est);
	fila_pan_dip10("ESPECIAL (USAER)", "total_sedeq_esp_usaer", $arr_datos_est);
	fila_pan_dip10("PREESCOLAR", "total_pree", $arr_datos_est);
	fila_pan_dip10("GENERAL", "total_pree_gral", $arr_datos_est);
	fila_pan_dip10("COMUNITARIO", "total_comuni_pree", $arr_datos_est);
	fila_pan_dip10("INDIGENA", "total_ind_pree", $arr_datos_est);
	fila_pan_dip10("PRIMARIA", "total_prim", $arr_datos_est);
	fila_pan_dip10("GENERAL", "total_gral_prim", $arr_datos_est);
	fila_pan_dip10("COMUNITARIO", "total_comuni_prim", $arr_datos_est);
	fila_pan_dip10("INDIGENA", "total_ind_prim", $arr_datos_est);
	fila_pan_dip10("SECUNDARIA", "total_sec", $arr_datos_est);
	fila_pan_dip10("GENERAL", "total_subn_gral_sec", $arr_datos_est);
	fila_pan_dip10("COMUNITARIO", "total_comuni_sec", $arr_datos_est);
	fila_pan_dip10("TECNICA", "total_tec_sec", $arr_datos_est);
	fila_pan_dip10("TELESECUNDARIA", "total_tele_sec", $arr_datos_est);
	fila_pan_dip10("BASICA", "total_sedeq_basica", $arr_datos_est);
	fila_pan_dip10("MEDIA SUPERIOR", "total_sedeq_msup", $arr_datos_est);
	fila_pan_dip10("BACHILLERATO GENERAL", "total_bgral_msup", $arr_datos_est);
	fila_pan_dip10("BACHILLERATO TECNOLOGICO", "total_btecno_msup", $arr_datos_est);
	fila_pan_dip10("Licenciatura (TSU y Lic)", "total_lic_sup", $arr_datos_est);
	fila_pan_dip10("TSU", "total_usbq_tsu_sup", $arr_datos_est);
	fila_pan_dip10("LIC", "total_usbq_lic_sup", $arr_datos_est);
	fila_pan_dip10("Posgrado", "total_posg_sup", $arr_datos_est);
	fila_pan_dip10("SUPERIOR", "total_usbq_sup", $arr_datos_est);
	fila_pan_dip10("SISTEMA", "total_sedeq_sistema", $arr_datos_est);
	//echo "</TABLE>";
}
?>
<HTML>

<HEAD>
	<LINK REL="stylesheet" HREF="./css/redes.css">
</HEAD>

<BODY>
	<?php


	/* encabezado_tabla_cifras("23");
	mostrar_tabla_cifras(arreglos_datos("CIFRAS",$link,"23",$sin_filtro_extra,$filtro_pub,$filtro_priv)); */

	/* encabezado_tablas_usbq("ESTADÍSTICA POR NIVEL EDUCATIVO / SERVICIO","23");		
	mostrar_tabla_servicio_usbq($link,arreglos_datos("USBQ_SERVICIO",$link,"23",$sin_filtro_extra,$filtro_pub,$filtro_priv),$filtro_pub,$filtro_priv); */

	/* encabezado_tablas_usbq("ESTADÍSTICA POR NIVEL EDUCATIVO","23");	
	mostrar_tabla_nivel_usbq($link,"AMBOS",arreglos_datos("USBQ_SERVICIO",$link,"23",$sin_filtro_extra,$filtro_pub,$filtro_priv),$filtro_pub,$filtro_priv);

	encabezado_tablas_usbq("ESCUELAS PUBLICAS","23");	
	mostrar_tabla_nivel_usbq($link,"PUB",arreglos_datos("USBQ_SERVICIO",$link,"23",$sin_filtro_extra,$filtro_pub,$filtro_priv),$filtro_pub,$filtro_priv);

	encabezado_tablas_usbq("ESCUELAS PRIVADAS","23");	
	mostrar_tabla_nivel_usbq($link,"PRIV",arreglos_datos("USBQ_SERVICIO",$link,"23",$sin_filtro_extra,$filtro_pub,$filtro_priv),$filtro_pub,$filtro_priv); */

	/* encabezado_tablas_usbq("ESTADÍSTICA POR NIVEL EDUCATIVO / MUNICIPIO","24");
	mostrar_tabla_munic_usbq("AMBOS",$link,"24",$filtro_pub,$filtro_priv); 

	encabezado_tablas_usbq("ESTADÍSTICA POR NIVEL EDUCATIVO / MUNICIPIO","24");
	mostrar_tabla_munic_usbq("PUB",$link,"24",$filtro_pub,$filtro_priv); 

	encabezado_tablas_usbq("ESTADÍSTICA POR NIVEL EDUCATIVO / MUNICIPIO","24");
	mostrar_tabla_munic_usbq("PRIV",$link,"24",$filtro_pub,$filtro_priv); */

	encabezado_pan_dip10("24");
	for ($num_munic = 1; $num_munic <= 18; $num_munic++) {

		$filtro_mun = " AND cv_mun='" . $num_munic . "' ";
		$nombre_munic = nombre_municipio($num_munic);
		//echo $nombre_munic;
		fila_pan_dip10($nombre_munic, "total_sedeq_sistema", arreglos_datos("NIVEL_PANO", $link, "24", $filtro_mun, $filtro_pub, $filtro_priv));
		panorama_dip10($link, "24", $filtro_mun, $filtro_pub, $filtro_priv);

	}
	echo "</TABLE>";
	?>
</BODY>

</HTML>