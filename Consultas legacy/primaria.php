<HTML>

<HEAD>
	<LINK REL="stylesheet" HREF="../css/estadistica.css">
	<TITLE>Primaria</TITLE>
	<style>
		.filtro-container {
			margin: 15px 0;
			padding: 10px;
			background-color: #f5f5f5;
			border: 1px solid #ddd;
			border-radius: 5px;
		}

		.filtro-container select {
			padding: 8px;
			font-size: 14px;
			border: 1px solid #ccc;
			border-radius: 4px;
			margin-right: 10px;
		}

		.filtro-container button {
			padding: 8px 15px;
			background-color: #4CAF50;
			color: white;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}

		.filtro-container button:hover {
			background-color: #45a049;
		}

		#columnaFilter {
			min-width: 250px;
			height: auto;
		}

		.filtro-container p {
			margin: 5px 0;
			font-size: 14px;
			color: #555;
		}
	</style>
</HEAD>

<BODY>
	<div class="filtro-container">
		<h3>Filtro por Municipio, Subnivel y Tipo de Dato:</h3>
		<select id="municipioFilter">
			<option value="todos">Todos los municipios</option>
			<?php
			$link_filtro = Conectarse();
			$qr_lista_muni_filtro = "SELECT DISTINCT c_nom_mun AS municipio
                                 FROM nonce_pano_23.prim_gral_23 
                                 WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) 
                                 ORDER BY c_nom_mun;";
			$rs_lista_muni_filtro = pg_query($link_filtro, $qr_lista_muni_filtro) or die('La consulta de filtro falló: ' . pg_last_error());
			while ($row_lista_muni_filtro = pg_fetch_assoc($rs_lista_muni_filtro)) {
				echo '<option value="' . $row_lista_muni_filtro["municipio"] . '">' . $row_lista_muni_filtro["municipio"] . '</option>';
			}
			pg_free_result($rs_lista_muni_filtro);
			?>
		</select>

		<select id="subnivelFilter">
			<option value="todos">Todos los subniveles</option>
			<option value="GRAL">General</option>
			<option value="COMU">Comunitaria</option>
			<option value="IND">Indígena</option>
			<option value="TOT">Total</option>
		</select>

		<select id="columnaFilter">
			<option value="todas">Todas las columnas</option>
			<option value="escuelas">Escuelas</option>
			<option value="alumnos">Alumnos</option>
			<option value="docentes">Docentes</option>
			<option value="grupos">Grupos</option>
			<option value="aulas">Aulas</option>
			<option value="discapacidad">Discapacidad</option>
			<option value="hl">Hablantes Lengua Indígena</option>
			<option value="primero">Primer Grado</option>
			<option value="segundo">Segundo Grado</option>
			<option value="tercero">Tercer Grado</option>
			<option value="cuarto">Cuarto Grado</option>
			<option value="quinto">Quinto Grado</option>
			<option value="sexto">Sexto Grado</option>
			<option value="egresados">Egresados</option>
			<option value="usaer">Apoyo USAER</option>
			<option value="nextj">Nacionalidad Extranjera</option>
			<option value="ormisma">Alumnos de Misma Entidad</option>
			<option value="orotra">Alumnos de Otra Entidad</option>
			<option value="orpais">Alumnos de Otro País</option>
			<option value="ortotal">Origen Total</option>
		</select>

		<button onclick="aplicarFiltros()">Aplicar Filtro</button>
		<button onclick="resetearFiltros()">Mostrar Todos</button>
	</div>
	<?php

	require_once("titulos_tabla.php");

	function Conectarse()
	{
		$link_conexion = pg_connect("host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres options='--client_encoding=LATIN1'")
			or die('No se ha podido conectar: ' . pg_last_error());
		//$link_conexion->set_charset("utf8");
		return $link_conexion;
	}
	$link = Conectarse();

	function filas_conteo($css, $arr_datos)
	{
		if ($arr_datos["pub"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["pub"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["priv"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["priv"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["tot"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["pub"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format((($arr_datos["pub"] / $arr_datos["tot"]) * 100), 1, '.', ',') . "</TD>";
		}
		if ($arr_datos["priv"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format((($arr_datos["priv"] / $arr_datos["tot"]) * 100), 1, '.', ',') . "</TD>";
		}
	}
	function filas_hm($css, $arr_datos)
	{
		if ($arr_datos["pub"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["pub"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["priv"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["priv"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["tot"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["pub"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format((($arr_datos["pub"] / $arr_datos["tot"]) * 100), 1, '.', ',') . "</TD>";
		}
		if ($arr_datos["priv"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format((($arr_datos["priv"] / $arr_datos["tot"]) * 100), 1, '.', ',') . "</TD>";
		}
		if ($arr_datos["h_tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["h_tot"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["m_tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["m_tot"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["tot"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["h_tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format((($arr_datos["h_tot"] / $arr_datos["tot"]) * 100), 1, '.', ',') . "</TD>";
		}
		if ($arr_datos["m_tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format((($arr_datos["m_tot"] / $arr_datos["tot"]) * 100), 1, '.', ',') . "</TD>";
		}
	}
	function filas_categoria($css, $arr_datos)
	{
		if ($arr_datos["cat1_tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["cat1_tot"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["cat2_tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["cat2_tot"], 0, '.', ',') . "</TD>";
		}
		if ($arr_datos["cat3_tot"] == 0) {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
		} else {
			echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["cat3_tot"], 0, '.', ',') . "</TD>";
		}
	}
	function sin_datos_hm($cv_mun, $municipio)
	{
		$primaria_detalle = [
			"cv_mun" => $cv_mun,
			"municipio" => $municipio,
			"priv" => 0,
			"pub" => 0,
			"tot" => 0,
			"priv_usbq" => 0,
			"pub_usbq" => 0,
			"usbq" => 0,
			"h_priv" => 0,
			"m_priv" => 0,
			"h_pub" => 0,
			"m_pub" => 0,
			"h_tot" => 0,
			"m_tot" => 0,
			"h_priv_usbq" => 0,
			"m_priv_usbq" => 0,
			"h_pub_usbq" => 0,
			"m_pub_usbq" => 0,
			"h_usbq" => 0,
			"m_usbq" => 0
		];
		//echo print_r ($primaria_detalle),"<BR>";
		return $primaria_detalle;
	}
	function nom_ciclo($ini_ciclo)
	{
		$nom_ciclo = "";
		if ((strcmp($ini_ciclo, "21")) == 0) {
			$nom_ciclo = "INICIO CICLO 2021-2022";
		}
		if ((strcmp($ini_ciclo, "22")) == 0) {
			$nom_ciclo = "INICIO CICLO 2022-2023";
		}
		if ((strcmp($ini_ciclo, "23")) == 0) {
			$nom_ciclo = "INICIO CICLO 2023-2024";
		}
		return $nom_ciclo;
	}
	function nom_subnivel($subnivel)
	{
		$nom_subnivel = "";
		if ((strcmp($subnivel, "GRAL")) == 0) {
			$nom_subnivel = "GENERAL";
		}
		if ((strcmp($subnivel, "IND")) == 0) {
			$nom_subnivel = "INDÍGENA";
		}
		if ((strcmp($subnivel, "COMU")) == 0) {
			$nom_subnivel = "COMUNITARIO";
		}
		if ((strcmp($subnivel, "TOT")) == 0) {
			$nom_subnivel = "TOTAL";
		}
		if ((strcmp($subnivel, "EDO")) == 0) {
			$nom_subnivel = "ESTADO";
		}
		return $nom_subnivel;
	}
	function mostrar_subnivel($css, $ini_ciclo, $cv_mun, $municipio, $subnivel, $esc, $alum, $doc, $grp, $aula, $disc, $hl, $g1ro, $g2do, $g3ro, $g4to, $g5to, $g6to, $egre, $usaer, $nextj, $ormisma, $orotra, $orpais, $ortotal)
	{
		echo "<TR class='fila-datos' data-municipio='" . $municipio . "' data-subnivel='" . $subnivel . "'>";
		echo "<TD VALIGN='center' CLASS='" . $css . "'>" . nom_ciclo($ini_ciclo) . "</TD>";
		echo "<TD VALIGN='center' CLASS='" . $css . "'>PRIMARIA</TD>";
		echo "<TD VALIGN='center' CLASS='" . $css . "'>" . nom_subnivel($subnivel) . "</TD>";
		echo "<TD VALIGN='center' CLASS='" . $css . "'>" . $municipio . "</TD>";

		filas_conteo($css, $esc);
		filas_hm($css, $alum);
		filas_hm($css, $doc);
		filas_conteo($css, $grp);
		filas_categoria($css, $aula);
		filas_hm($css, $disc);
		filas_hm($css, $hl);
		filas_hm($css, $g1ro);
		filas_hm($css, $g2do);
		filas_hm($css, $g3ro);
		filas_hm($css, $g4to);
		filas_hm($css, $g5to);
		filas_hm($css, $g6to);
		filas_hm($css, $egre);
		filas_hm($css, $usaer);
		filas_hm($css, $nextj);
		filas_hm($css, $ormisma);
		filas_hm($css, $orotra);
		filas_hm($css, $orpais);
		filas_hm($css, $ortotal);
		echo "</TR>";
	}

	function prim_qr($dato, $subnivel, $ini_ciclo, $cv_mun)
	{
		$qr_prim_hm = "";
		if ((strcmp($dato, "ESCUELA")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_esc;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_esc;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, COUNT(cv_cct)AS totales
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_esc;
			}
		}
		if ((strcmp($dato, "ALUMNO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v608)AS totales,
										SUM(v562+v573)AS h,SUM(v585+v596)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_alum;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v610)AS totales,
										SUM(v564+v575)AS h,SUM(v587+v598)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_alum;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v515)AS totales,
										SUM(v469+v480)AS h,SUM(v492+v503)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_alum;
			}
		}
		if ((strcmp($dato, "DOCENTE")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_doc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1676)AS totales,
											SUM(v1567+v1575)AS h,SUM(v1568+v1576)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_doc;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_doc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1060)AS totales,
											SUM(v1499+v1507+v1509)AS h,SUM(v1500+v1508+v1510)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
											WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_doc;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_doc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v585)AS totales,
											SUM(v583)AS h,SUM(v584)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_doc;
			}
		}
		if ((strcmp($dato, "GRUPO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_gpr = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v616)AS totales
											FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_gpr;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_gpr = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1052)AS totales
											FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
											WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_gpr;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_gpr = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,COUNT(cv_cct)AS totales
											FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_gpr;
			}
		}
		if ((strcmp($dato, "AULA")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_aula = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v1703)AS cat1,
											SUM(v1711)AS cat2,SUM(v1719)AS cat3
											FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_aula;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_aula = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1569)AS cat1,
											SUM(v1577)AS cat2,SUM(v1585)AS cat3
											FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
											WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_aula;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_aula = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v585)AS cat1,
											SUM(v585)AS cat2,SUM(v585)AS cat3
											FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_aula;
			}
		}
		if ((strcmp($dato, "DISCAP")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_disc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1083)AS totales,
											SUM(v1081)AS h,SUM(v1082)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_disc;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_disc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1016)AS totales,
											SUM(v1014)AS h,SUM(v1015)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
											WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_disc;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_disc = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control,SUM(v582)AS totales,
											SUM(v580)AS h,SUM(v581)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_disc;
			}
		}
		/**==========================================================================================  
																																																						  =========  HABLANTES DE LENGUA INDIGENA
																																																						  //en la comu -no hay datos de este tema
																																																						  ==========================================================================================*/
		if ((strcmp($dato, "HL")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_hl = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v647)AS totales,
											SUM(v645)AS h,SUM(v646)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_hl;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_hl = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1093+v1101+v1109+v1117+v1124) AS totales,
											SUM(0)AS h,SUM(0)AS m 		
											FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
											WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_hl;
			}
		}
		/**==========================================================================================  
																																																						  =========  GRADOS
																																																						  ==========================================================================================*/
		if ((strcmp($dato, "PRIMERO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v325)AS totales,
										SUM(v279+v290)AS h,SUM(v302+v313)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_alum;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v327)AS totales,
										SUM(v281+v292)AS h,SUM(v304+v315)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_alum;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v232)AS totales,
										SUM(v186+v197)AS h,SUM(v209+v220)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_alum;
			}
		}
		if ((strcmp($dato, "SEGUNDO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v380)AS totales,
										SUM(v336+v347)AS h,SUM(v358+v369)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_alum;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v382)AS totales,
										SUM(v338+v349)AS h,SUM(v360+v371)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_alum;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v287)AS totales,
										SUM(v243+v254)AS h,SUM(v265+v276)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_alum;
			}
		}
		if ((strcmp($dato, "TERCERO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v430)AS totales,
										SUM(v390+v400)AS h,SUM(v410+v420)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_alum;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v432)AS totales,
										SUM(v392+v402)AS h,SUM(v412+v422)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_alum;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v337)AS totales,
										SUM(v297+v307)AS h,SUM(v317+v327)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_alum;
			}
		}
		if ((strcmp($dato, "CUARTO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v475)AS totales,
										SUM(v439+v448)AS h,SUM(v457+v466)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_alum;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v477)AS totales,
										SUM(v441+v450)AS h,SUM(v459+v468)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_alum;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v382)AS totales,
										SUM(v346+v355)AS h,SUM(v364+v373)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_alum;
			}
		}
		if ((strcmp($dato, "QUINTO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v515)AS totales,
										SUM(v483+v491)AS h,SUM(v499+v507)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_alum;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v517)AS totales,
										SUM(v485+v493)AS h,SUM(v501+v509)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_alum;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v422)AS totales,
										SUM(v390+v398)AS h,SUM(v406+v414)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_alum;
			}
		}
		if ((strcmp($dato, "SEXTO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v550)AS totales,
										SUM(v522+v529)AS h,SUM(v536+v543)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_alum;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v552)AS totales,
										SUM(v524+v531)AS h,SUM(v538+v545)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_alum;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_alum = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v457)AS totales,
										SUM(v429+v436)AS h,SUM(v443+v450)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_alum;
			}
		}
		if ((strcmp($dato, "EGRESO")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_egre = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v1757+V1764)AS totales,
										SUM(v1757)AS h,SUM(v1764)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_egre;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_egre = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1614+V1621)AS totales,
										SUM(v1614)AS h,SUM(v1621)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_egre;
			}
			if ((strcmp($subnivel, "COMU")) == 0) {
				$qr_prim_comu_egre = "SELECT cv_mun,c_nom_mun AS MUNICIPIO,subcontrol,control, SUM(v592+V599)AS totales,
										SUM(v592)AS h,SUM(v599)AS m
										FROM nonce_pano_" . $ini_ciclo . ".prim_comuni_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_comu_egre;
			}
		}

		/**==========================================================================================  
																																																						  =========  USAER
																																																						  //en la ind -no hay datos de este tema
																																																						  //en la comu -no hay datos de este tema
																																																						  ==========================================================================================*/
		if ((strcmp($dato, "USAER")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_usaer = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v677)AS totales,
											SUM(v675)AS h,SUM(v676)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_usaer;
			}
		}
		/**==========================================================================================  
																																																						  =========  NACIDOS EN EL EXTRANJERO
																																																						  //en la ind -no hay datos de este tema
																																																						  //en la comu -no hay datos de este tema
																																																						  ==========================================================================================*/
		if ((strcmp($dato, "NEXTJ")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_nextj = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v674)AS totales,
											SUM(v672)AS h,SUM(v673)AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
											WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_nextj;
			}
		}
		/**==========================================================================================  
																																																						  =========  ALUMNOS QUE PROVIENEN DE OTRA ESCUELA 
																																																									  (NO TODA LA MATRICULA SOLO LOS QUE LLEGARON)
																																																						  //en la comu -no hay datos de este tema
																																																						  ==========================================================================================*/
		if ((strcmp($dato, "ORMISMA")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v224+V225)AS totales,
										SUM(v224)AS h,SUM(v225)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_orig;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v224+V225)AS totales,
										SUM(v224)AS h,SUM(v225)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_orig;
			}
		}
		if ((strcmp($dato, "OROTRA")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v226+V227)AS totales,
										SUM(v226)AS h,SUM(v226)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_orig;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v226+V227)AS totales,
										SUM(v226)AS h,SUM(v226)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_orig;
			}
		}
		if ((strcmp($dato, "ORPAIS")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v228+V229)AS totales,
										SUM(v228)AS h,SUM(v229)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_orig;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v228+V229)AS totales,
										SUM(v228)AS h,SUM(v229)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_orig;
			}
		}
		if ((strcmp($dato, "ORTOTAL")) == 0) {
			if ((strcmp($subnivel, "GRAL")) == 0) {
				$qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v230+V231)AS totales,
										SUM(v230)AS h,SUM(v231)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
										WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_gral_orig;
			}
			if ((strcmp($subnivel, "IND")) == 0) {
				$qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v230+V231)AS totales,
										SUM(v230)AS h,SUM(v231)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
										WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
				$qr_prim_hm = $qr_prim_ind_orig;
			}
		}
		return $qr_prim_hm;
	}
	function prim_hm($link, $dato, $subnivel, $ini_ciclo, $cv_mun, $municipio)
	{

		$priv = 0;
		$pub = 0;
		$tot = 0;
		$priv_usbq = 0;
		$pub_usbq = 0;
		$usbq = 0;
		$h_priv = 0;
		$m_priv = 0;
		$h_pub = 0;
		$m_pub = 0;
		$h_tot = 0;
		$m_tot = 0;
		$h_priv_usbq = 0;
		$m_priv_usbq = 0;
		$h_pub_usbq = 0;
		$m_pub_usbq = 0;
		$h_usbq = 0;
		$m_usbq = 0;
		$priv_nusbq = 0;
		$pub_nusbq = 0;

		$qr_prim_hm = prim_qr($dato, $subnivel, $ini_ciclo, $cv_mun);
		//echo $qr_prim_hm;
		$rs_prim_hm = pg_query($link, $qr_prim_hm) or die('La consulta qr_prim_hm: ' . pg_last_error());
		$cant_prim_hm = pg_num_rows($rs_prim_hm);
		if ($cant_prim_hm > 0) {

			while ($row_prim_hm = pg_fetch_assoc($rs_prim_hm)) {
				$cv_mun = $row_prim_hm["cv_mun"];
				$municipio = $row_prim_hm["municipio"];
				$control = $row_prim_hm["control"];
				$subcontrol = $row_prim_hm["subcontrol"];
				$totales = $row_prim_hm["totales"];
				$h = $row_prim_hm["h"];
				$m = $row_prim_hm["m"];

				if ((strcmp($control, "PRIVADO")) == 0) {
					$priv = $priv = +$totales;
					$h_priv = $h_priv + $h;
					$m_priv = $m_priv + $m;
					if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
						$priv_usbq = $priv_usbq + $totales;
						$h_priv_usbq = $h_priv_usbq + $h;
						$m_priv_usbq = $m_priv_usbq + $m;
					} else {
						$priv_nusbq = $priv_nusbq + $totales;
					}
				} else {
					$pub = $pub + $totales;
					$h_pub = $h_pub + $h;
					$m_pub = $m_pub + $m;
					if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
						$pub_usbq = $pub_usbq + $totales;
						$h_pub_usbq = $h_pub_usbq + $h;
						$m_pub_usbq = $m_pub_usbq + $m;
					} else {
						$pub_nusbq = $pub_nusbq + $totales;
					}
				}
				$tot = $priv + $pub;
				$usbq = $priv_usbq + $pub_usbq;
				$h_tot = $h_priv + $h_pub;
				$m_tot = $m_priv + $m_pub;
			}
		} else {
			$priv = 0;
			$pub = 0;
			$tot = 0;
			$priv_usbq = 0;
			$pub_usbq = 0;
			$usbq = 0;
			$h_priv = 0;
			$m_priv = 0;
			$h_pub = 0;
			$m_pub = 0;
			$h_tot = 0;
			$m_tot = 0;
			$h_priv_usbq = 0;
			$m_priv_usbq = 0;
			$h_pub_usbq = 0;
			$m_pub_usbq = 0;
			$h_usbq = 0;
			$m_usbq = 0;
		}

		$primaria_detalle = [
			"cv_mun" => $cv_mun,
			"municipio" => $municipio,
			"priv" => $priv,
			"pub" => $pub,
			"tot" => $tot,
			"priv_usbq" => $priv_usbq,
			"pub_usbq" => $pub_usbq,
			"usbq" => $usbq,
			"h_priv" => $h_priv,
			"m_priv" => $m_priv,
			"h_pub" => $h_pub,
			"m_pub" => $m_pub,
			"h_tot" => $h_tot,
			"m_tot" => $m_tot,
			"h_priv_usbq" => $h_priv_usbq,
			"m_priv_usbq" => $m_priv_usbq,
			"h_pub_usbq" => $h_pub_usbq,
			"m_pub_usbq" => $m_pub_usbq,
			"h_usbq" => $h_usbq,
			"m_usbq" => $m_usbq
		];
		//echo print_r ($primaria_detalle),"<BR>";
		return $primaria_detalle;
		pg_free_result($rs_prim_hm);
	}
	function prim_conteo($link, $dato, $subnivel, $ini_ciclo, $cv_mun, $municipio)
	{
		$priv = 0;
		$pub = 0;
		$tot = 0;
		$priv_usbq = 0;
		$pub_usbq = 0;
		$usbq = 0;
		$priv_nusbq = 0;
		$pub_nusbq = 0;

		$qr_prim_cont = prim_qr($dato, $subnivel, $ini_ciclo, $cv_mun);
		//echo $qr_prim_cont;
		$rs_prim_cont = pg_query($link, $qr_prim_cont) or die('La consulta qr_prim_cont: ' . pg_last_error());
		$cant_prim_cont = pg_num_rows($rs_prim_cont);
		if ($cant_prim_cont > 0) {

			while ($row_prim_cont = pg_fetch_assoc($rs_prim_cont)) {
				$cv_mun = $row_prim_cont["cv_mun"];
				$municipio = $row_prim_cont["municipio"];
				$control = $row_prim_cont["control"];
				$subcontrol = $row_prim_cont["subcontrol"];
				$totales = $row_prim_cont["totales"];

				if ((strcmp($control, "PRIVADO")) == 0) {
					$priv = $priv + $totales;
					if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
						$priv_usbq = $priv_usbq + $totales;
					} else {
						$priv_nusbq = $priv_nusbq + $totales;
					}
				} else {
					$pub = $pub + $totales;
					if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
						$pub_usbq = $pub_usbq + $totales;
					} else {
						$pub_nusbq = $pub_nusbq + $totales;
					}
				}
				$tot = $priv + $pub;
				$usbq = $priv_usbq + $pub_usbq;
			}
		} else {
			$priv = 0;
			$pub = 0;
			$tot = 0;
			$priv_usbq = 0;
			$pub_usbq = 0;
			$usbq = 0;
		}

		$prim_conteo_detalle = [
			"cv_mun" => $cv_mun,
			"municipio" => $municipio,
			"priv" => $priv,
			"pub" => $pub,
			"tot" => $tot,
			"priv_usbq" => $priv_usbq,
			"pub_usbq" => $pub_usbq,
			"usbq" => $usbq
		];
		//echo print_r ($prim_conteo_detalle),"<BR>";
		return $prim_conteo_detalle;
		pg_free_result($rs_prim_cont);
	}
	function prim_categ($link, $dato, $subnivel, $ini_ciclo, $cv_mun, $municipio)
	{

		$cat1_priv = 0;
		$cat2_priv = 0;
		$cat3_priv = 0;
		$cat1_pub = 0;
		$cat2_pub = 0;
		$cat3_pub = 0;
		$cat1_tot = 0;
		$cat2_tot = 0;
		$cat3_tot = 0;
		$cat1_priv_usbq = 0;
		$cat2_priv_usbq = 0;
		$cat3_priv_usbq = 0;
		$cat1_pub_usbq = 0;
		$cat2_pub_usbq = 0;
		$cat3_pub_usbq = 0;
		$cat1_usbq = 0;
		$cat2_usbq = 0;
		$cat3_usbq = 0;
		$nusbq = 0;


		$qr_prim_categ = prim_qr($dato, $subnivel, $ini_ciclo, $cv_mun);
		//echo $qr_prim_categ;
		$rs_prim_categ = pg_query($link, $qr_prim_categ) or die('La consulta qr_prim_categ: ' . pg_last_error());
		$cant_prim_categ = pg_num_rows($rs_prim_categ);
		if ($cant_prim_categ > 0) {

			while ($row_prim_categ = pg_fetch_assoc($rs_prim_categ)) {
				$cv_mun = $row_prim_categ["cv_mun"];
				$municipio = $row_prim_categ["municipio"];
				$control = $row_prim_categ["control"];
				$subcontrol = $row_prim_categ["subcontrol"];
				$cat1 = $row_prim_categ["cat1"];
				$cat2 = $row_prim_categ["cat2"];
				$cat3 = $row_prim_categ["cat3"];

				if ((strcmp($control, "PRIVADO")) == 0) {
					$cat1_priv = $cat1;
					$cat2_priv = $cat2;
					$cat3_priv = $cat3;
					if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
						$cat1_priv_usbq = $cat1;
						$cat2_priv_usbq = $cat2;
						$cat3_priv_usbq = $cat3;
					} else {
						$nusbq = $nusbq + $cat1;
					}
				} else {
					$cat1_pub = $cat1;
					$cat2_pub = $cat2;
					$cat3_pub = $cat3;
					if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
						$cat1_pub_usbq = $cat1;
						$cat2_pub_usbq = $cat2;
						$cat3_pub_usbq = $cat3;
					} else {
						$nusbq = $nusbq + $cat1;
					}
				}

				$cat1_tot = $cat1_priv + $cat1_pub;
				$cat2_tot = $cat2_priv + $cat2_pub;
				$cat3_tot = $cat3_priv + $cat3_pub;
				$cat1_usbq = $cat1_priv_usbq + $cat1_pub_usbq;
				$cat2_usbq = $cat2_priv_usbq + $cat2_pub_usbq;
				$cat3_usbq = $cat3_priv_usbq + $cat3_pub_usbq;
			}
		} else {
			$cat1_priv = 0;
			$cat2_priv = 0;
			$cat3_priv = 0;
			$cat1_pub = 0;
			$cat2_pub = 0;
			$cat3_pub = 0;
			$cat1_tot = 0;
			$cat2_tot = 0;
			$cat3_tot = 0;
			$cat1_priv_usbq = 0;
			$cat2_priv_usbq = 0;
			$cat3_priv_usbq = 0;
			$cat1_pub_usbq = 0;
			$cat2_pub_usbq = 0;
			$cat3_pub_usbq = 0;
			$cat1_usbq = 0;
			$cat2_usbq = 0;
			$cat3_usbq = 0;
		}

		$prim_cat_detalle = [
			"cv_mun" => $cv_mun,
			"municipio" => $municipio,
			"cat1_priv" => $cat1_priv,
			"cat2_priv" => $cat2_priv,
			"cat3_priv" => $cat3_priv,
			"cat1_pub" => $cat1_pub,
			"cat2_pub" => $cat2_pub,
			"cat3_pub" => $cat3_pub,
			"cat1_tot" => $cat1_tot,
			"cat2_tot" => $cat2_tot,
			"cat3_tot" => $cat3_tot,
			"cat1_priv_usbq" => $cat1_priv_usbq,
			"cat2_priv_usbq" => $cat2_priv_usbq,
			"cat3_priv_usbq" => $cat3_priv_usbq,
			"cat1_pub_usbq" => $cat1_pub_usbq,
			"cat2_pub_usbq" => $cat2_pub_usbq,
			"cat3_pub_usbq" => $cat3_pub_usbq,
			"cat1_usbq" => $cat1_usbq,
			"cat2_usbq" => $cat2_usbq,
			"cat3_usbq" => $cat3_usbq
		];
		//echo print_r ($prim_cat_detalle),"<BR>";
		return $prim_cat_detalle;
		pg_free_result($rs_prim_categ);
	}
	function prim_hm_muni($cv_mun, $municipio, $gral, $ind, $comu)
	{

		$priv_mun = $gral["priv"] + $ind["priv"] + $comu["priv"];
		$pub_mun = $gral["pub"] + $ind["pub"] + $comu["pub"];
		$tot_mun = $gral["tot"] + $ind["tot"] + $comu["tot"];
		$priv_usbq_mun = $gral["priv_usbq"] + $ind["priv_usbq"] + $comu["priv_usbq"];
		$pub_usbq_mun = $gral["pub_usbq"] + $ind["pub_usbq"] + $comu["pub_usbq"];
		$usbq_mun = $gral["usbq"] + $ind["usbq"] + $comu["usbq"];
		$h_priv_mun = $gral["h_priv"] + $ind["h_priv"] + $comu["h_priv"];
		$m_priv_mun = $gral["m_priv"] + $ind["m_priv"] + $comu["m_priv"];
		$h_pub_mun = $gral["h_pub"] + $ind["h_pub"] + $comu["h_pub"];
		$m_pub_mun = $gral["m_pub"] + $ind["m_pub"] + $comu["m_pub"];
		$h_tot_mun = $gral["h_tot"] + $ind["h_tot"] + $comu["h_tot"];
		$m_tot_mun = $gral["m_tot"] + $ind["m_tot"] + $comu["m_tot"];
		$h_priv_usbq_mun = $gral["h_priv_usbq"] + $ind["h_priv_usbq"] + $comu["h_priv_usbq"];
		$m_priv_usbq_mun = $gral["m_priv_usbq"] + $ind["m_priv_usbq"] + $comu["m_priv_usbq"];
		$h_pub_usbq_mun = $gral["h_pub_usbq"] + $ind["h_pub_usbq"] + $comu["h_pub_usbq"];
		$m_pub_usbq_mun = $gral["m_pub_usbq"] + $ind["m_pub_usbq"] + $comu["m_pub_usbq"];
		$h_usbq_mun = $gral["h_usbq"] + $ind["h_usbq"] + $comu["h_usbq"];
		$m_usbq_mun = $gral["m_usbq"] + $ind["m_usbq"] + $comu["m_usbq"];

		$primaria_det_mun = [
			"cv_mun" => $cv_mun,
			"municipio" => $municipio,
			"priv" => $priv_mun,
			"pub" => $pub_mun,
			"tot" => $tot_mun,
			"priv_usbq" => $priv_usbq_mun,
			"pub_usbq" => $pub_usbq_mun,
			"usbq" => $usbq_mun,
			"h_priv" => $h_priv_mun,
			"m_priv" => $m_priv_mun,
			"h_pub" => $h_pub_mun,
			"m_pub" => $m_pub_mun,
			"h_tot" => $h_tot_mun,
			"m_tot" => $m_tot_mun,
			"h_priv_usbq" => $h_priv_usbq_mun,
			"m_priv_usbq" => $m_priv_usbq_mun,
			"h_pub_usbq" => $h_pub_usbq_mun,
			"m_pub_usbq" => $m_pub_usbq_mun,
			"h_usbq" => $h_usbq_mun,
			"m_usbq" => $m_usbq_mun
		];
		//echo print_r ($primaria_det_mun),"<BR>";
		return $primaria_det_mun;
	}
	function prim_conteo_muni($cv_mun, $municipio, $gral, $ind, $comu)
	{

		$priv_mun = $gral["priv"] + $ind["priv"] + $comu["priv"];
		$pub_mun = $gral["pub"] + $ind["pub"] + $comu["pub"];
		$tot_mun = $gral["tot"] + $ind["tot"] + $comu["tot"];
		$priv_usbq_mun = $gral["priv_usbq"] + $ind["priv_usbq"] + $comu["priv_usbq"];
		$pub_usbq_mun = $gral["pub_usbq"] + $ind["pub_usbq"] + $comu["pub_usbq"];
		$usbq_mun = $gral["usbq"] + $ind["usbq"] + $comu["usbq"];

		$prim_conteo_muni = [
			"cv_mun" => $cv_mun,
			"municipio" => $municipio,
			"priv" => $priv_mun,
			"pub" => $pub_mun,
			"tot" => $tot_mun,
			"priv_usbq" => $priv_usbq_mun,
			"pub_usbq" => $pub_usbq_mun,
			"usbq" => $usbq_mun
		];
		//echo print_r ($prim_conteo_muni),"<BR>";
		return $prim_conteo_muni;
	}
	function prim_categ_muni($cv_mun, $municipio, $gral, $ind, $comu)
	{
		$cat1_priv = $gral["cat1_priv"] + $ind["cat1_priv"] + $comu["cat1_priv"];
		$cat2_priv = $gral["cat2_priv"] + $ind["cat2_priv"] + $comu["cat2_priv"];
		$cat3_priv = $gral["cat3_priv"] + $ind["cat3_priv"] + $comu["cat3_priv"];
		$cat1_pub = $gral["cat1_pub"] + $ind["cat1_pub"] + $comu["cat1_pub"];
		$cat2_pub = $gral["cat2_pub"] + $ind["cat2_pub"] + $comu["cat2_pub"];
		$cat3_pub = $gral["cat3_pub"] + $ind["cat3_pub"] + $comu["cat3_pub"];
		$cat1_tot = $gral["cat1_tot"] + $ind["cat1_tot"] + $comu["cat1_tot"];
		$cat2_tot = $gral["cat2_tot"] + $ind["cat2_tot"] + $comu["cat2_tot"];
		$cat3_tot = $gral["cat3_tot"] + $ind["cat3_tot"] + $comu["cat3_tot"];
		$cat1_priv_usbq = $gral["cat1_priv_usbq"] + $ind["cat1_priv_usbq"] + $comu["cat1_priv_usbq"];
		$cat2_priv_usbq = $gral["cat2_priv_usbq"] + $ind["cat2_priv_usbq"] + $comu["cat2_priv_usbq"];
		$cat3_priv_usbq = $gral["cat3_priv_usbq"] + $ind["cat3_priv_usbq"] + $comu["cat3_priv_usbq"];
		$cat1_pub_usbq = $gral["cat1_pub_usbq"] + $ind["cat1_pub_usbq"] + $comu["cat1_pub_usbq"];
		$cat2_pub_usbq = $gral["cat2_pub_usbq"] + $ind["cat2_pub_usbq"] + $comu["cat2_pub_usbq"];
		$cat3_pub_usbq = $gral["cat3_pub_usbq"] + $ind["cat3_pub_usbq"] + $comu["cat3_pub_usbq"];
		$cat1_usbq = $gral["cat1_usbq"] + $ind["cat1_usbq"] + $comu["cat1_usbq"];
		$cat2_usbq = $gral["cat2_usbq"] + $ind["cat2_usbq"] + $comu["cat2_usbq"];
		$cat3_usbq = $gral["cat3_usbq"] + $ind["cat3_usbq"] + $comu["cat3_usbq"];

		$prim_cat_detalle = [
			"cv_mun" => $cv_mun,
			"municipio" => $municipio,
			"cat1_priv" => $cat1_priv,
			"cat2_priv" => $cat2_priv,
			"cat3_priv" => $cat3_priv,
			"cat1_pub" => $cat1_pub,
			"cat2_pub" => $cat2_pub,
			"cat3_pub" => $cat3_pub,
			"cat1_tot" => $cat1_tot,
			"cat2_tot" => $cat2_tot,
			"cat3_tot" => $cat3_tot,
			"cat1_priv_usbq" => $cat1_priv_usbq,
			"cat2_priv_usbq" => $cat2_priv_usbq,
			"cat3_priv_usbq" => $cat3_priv_usbq,
			"cat1_pub_usbq" => $cat1_pub_usbq,
			"cat2_pub_usbq" => $cat2_pub_usbq,
			"cat3_pub_usbq" => $cat3_pub_usbq,
			"cat1_usbq" => $cat1_usbq,
			"cat2_usbq" => $cat2_usbq,
			"cat3_usbq" => $cat3_usbq
		];
		//echo print_r ($prim_cat_detalle),"<BR>";
		return $prim_cat_detalle;
	}
	function primaria_estadistica($link, $ini_ciclo)
	{

		$qr_lista_muni = "SELECT cv_mun,c_nom_mun AS municipio
									FROM nonce_pano_" . $ini_ciclo . ".prim_gral_" . $ini_ciclo . " 
									WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) 
									GROUP BY cv_mun,c_nom_mun
									ORDER BY cv_mun,c_nom_mun;";
		//echo $qr_lista_muni;
		$rs_lista_muni = pg_query($link, $qr_lista_muni) or die('La consulta qr_lista_muni: ' . pg_last_error());
		while ($row_lista_muni = pg_fetch_assoc($rs_lista_muni)) {
			$cv_mun = $row_lista_muni["cv_mun"];
			$municipio = $row_lista_muni["municipio"];

			$esc_gral = prim_conteo($link, "ESCUELA", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$alum_gral = prim_hm($link, "ALUMNO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$doc_gral = prim_hm($link, "DOCENTE", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$grp_gral = prim_conteo($link, "GRUPO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$aula_gral = prim_categ($link, "AULA", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$disc_gral = prim_hm($link, "DISCAP", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$hl_gral = prim_hm($link, "HL", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$g1ro_gral = prim_hm($link, "PRIMERO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$g2do_gral = prim_hm($link, "SEGUNDO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$g3ro_gral = prim_hm($link, "TERCERO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$g4to_gral = prim_hm($link, "CUARTO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$g5to_gral = prim_hm($link, "QUINTO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$g6to_gral = prim_hm($link, "SEXTO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$egre_gral = prim_hm($link, "EGRESO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$usaer_gral = prim_hm($link, "USAER", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$nextj_gral = prim_hm($link, "NEXTJ", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$ormisma_gral = prim_hm($link, "ORMISMA", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$orotra_gral = prim_hm($link, "OROTRA", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$orpais_gral = prim_hm($link, "ORPAIS", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			$ortotal_gral = prim_hm($link, "ORTOTAL", "GRAL", $ini_ciclo, $cv_mun, $municipio);
			mostrar_subnivel("datos_subnivel", $ini_ciclo, $cv_mun, $municipio, "GRAL", $esc_gral, $alum_gral, $doc_gral, $grp_gral, $aula_gral, $disc_gral, $hl_gral, $g1ro_gral, $g2do_gral, $g3ro_gral, $g4to_gral, $g5to_gral, $g6to_gral, $egre_gral, $usaer_gral, $nextj_gral, $ormisma_gral, $orotra_gral, $orpais_gral, $ortotal_gral);

			$esc_ind = prim_conteo($link, "ESCUELA", "IND", $ini_ciclo, $cv_mun, $municipio);
			$alum_ind = prim_hm($link, "ALUMNO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$doc_ind = prim_hm($link, "DOCENTE", "IND", $ini_ciclo, $cv_mun, $municipio);
			$grp_ind = prim_conteo($link, "GRUPO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$aula_ind = prim_categ($link, "AULA", "IND", $ini_ciclo, $cv_mun, $municipio);
			$disc_ind = prim_hm($link, "DISCAP", "IND", $ini_ciclo, $cv_mun, $municipio);
			$hl_ind = prim_hm($link, "HL", "IND", $ini_ciclo, $cv_mun, $municipio);
			$g1ro_ind = prim_hm($link, "PRIMERO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$g2do_ind = prim_hm($link, "SEGUNDO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$g3ro_ind = prim_hm($link, "TERCERO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$g4to_ind = prim_hm($link, "CUARTO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$g5to_ind = prim_hm($link, "QUINTO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$g6to_ind = prim_hm($link, "SEXTO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$egre_ind = prim_hm($link, "EGRESO", "IND", $ini_ciclo, $cv_mun, $municipio);
			$usaer_ind = sin_datos_hm($cv_mun, $municipio);
			$nextj_ind = sin_datos_hm($cv_mun, $municipio);
			$ormisma_ind = prim_hm($link, "ORMISMA", "IND", $ini_ciclo, $cv_mun, $municipio);
			$orotra_ind = prim_hm($link, "OROTRA", "IND", $ini_ciclo, $cv_mun, $municipio);
			$orpais_ind = prim_hm($link, "ORPAIS", "IND", $ini_ciclo, $cv_mun, $municipio);
			$ortotal_ind = prim_hm($link, "ORTOTAL", "IND", $ini_ciclo, $cv_mun, $municipio);
			mostrar_subnivel("datos_subnivel", $ini_ciclo, $cv_mun, $municipio, "IND", $esc_ind, $alum_ind, $doc_ind, $grp_ind, $aula_ind, $disc_ind, $hl_ind, $g1ro_ind, $g2do_ind, $g3ro_ind, $g4to_ind, $g5to_ind, $g6to_ind, $egre_ind, $usaer_ind, $nextj_ind, $ormisma_ind, $orotra_ind, $orpais_ind, $ortotal_ind);

			$esc_comu = prim_conteo($link, "ESCUELA", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$alum_comu = prim_hm($link, "ALUMNO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$doc_comu = prim_hm($link, "DOCENTE", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$grp_comu = prim_conteo($link, "GRUPO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$aula_comu = prim_categ($link, "AULA", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$disc_comu = prim_hm($link, "DISCAP", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$hl_comu = sin_datos_hm($cv_mun, $municipio);
			$g1ro_comu = prim_hm($link, "PRIMERO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$g2do_comu = prim_hm($link, "SEGUNDO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$g3ro_comu = prim_hm($link, "TERCERO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$g4to_comu = prim_hm($link, "CUARTO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$g5to_comu = prim_hm($link, "QUINTO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$g6to_comu = prim_hm($link, "SEXTO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$egre_comu = prim_hm($link, "EGRESO", "COMU", $ini_ciclo, $cv_mun, $municipio);
			$usaer_comu = sin_datos_hm($cv_mun, $municipio);
			$nextj_comu = sin_datos_hm($cv_mun, $municipio);
			$ormisma_comu = sin_datos_hm($cv_mun, $municipio);
			$orotra_comu = sin_datos_hm($cv_mun, $municipio);
			$orpais_comu = sin_datos_hm($cv_mun, $municipio);
			$ortotal_comu = sin_datos_hm($cv_mun, $municipio);
			mostrar_subnivel("datos_subnivel", $ini_ciclo, $cv_mun, $municipio, "COMU", $esc_comu, $alum_comu, $doc_comu, $grp_comu, $aula_comu, $disc_comu, $hl_comu, $g1ro_comu, $g2do_comu, $g3ro_comu, $g4to_comu, $g5to_comu, $g6to_comu, $egre_comu, $usaer_comu, $nextj_comu, $ormisma_comu, $orotra_comu, $orpais_comu, $ortotal_comu);

			$tot_esc_muni = prim_conteo_muni($cv_mun, $municipio, $esc_gral, $esc_ind, $esc_comu);
			$tot_alum_muni = prim_hm_muni($cv_mun, $municipio, $alum_gral, $alum_ind, $alum_comu);
			$tot_doc_muni = prim_hm_muni($cv_mun, $municipio, $doc_gral, $doc_ind, $doc_comu);
			$tot_grp_muni = prim_conteo_muni($cv_mun, $municipio, $grp_gral, $grp_ind, $grp_comu);
			$tot_aula_muni = prim_categ_muni($cv_mun, $municipio, $aula_gral, $aula_ind, $aula_comu);
			$tot_disc_muni = prim_hm_muni($cv_mun, $municipio, $disc_gral, $disc_ind, $disc_comu);
			$tot_hl_muni = prim_hm_muni($cv_mun, $municipio, $hl_gral, $hl_ind, $hl_comu);
			$tot_g1ro_muni = prim_hm_muni($cv_mun, $municipio, $g1ro_gral, $g1ro_ind, $g1ro_comu);
			$tot_g2do_muni = prim_hm_muni($cv_mun, $municipio, $g2do_gral, $g2do_ind, $g2do_comu);
			$tot_g3ro_muni = prim_hm_muni($cv_mun, $municipio, $g3ro_gral, $g3ro_ind, $g3ro_comu);
			$tot_g4to_muni = prim_hm_muni($cv_mun, $municipio, $g4to_gral, $g4to_ind, $g4to_comu);
			$tot_g5to_muni = prim_hm_muni($cv_mun, $municipio, $g5to_gral, $g5to_ind, $g5to_comu);
			$tot_g6to_muni = prim_hm_muni($cv_mun, $municipio, $g6to_gral, $g6to_ind, $g6to_comu);
			$tot_egre_muni = prim_hm_muni($cv_mun, $municipio, $egre_gral, $egre_ind, $egre_comu);
			$tot_usaer_muni = prim_hm_muni($cv_mun, $municipio, $usaer_gral, $usaer_ind, $usaer_comu);
			$tot_nextj_muni = prim_hm_muni($cv_mun, $municipio, $nextj_gral, $nextj_ind, $nextj_comu);
			$tot_ormisma_muni = prim_hm_muni($cv_mun, $municipio, $ormisma_gral, $ormisma_ind, $ormisma_comu);
			$tot_orotra_muni = prim_hm_muni($cv_mun, $municipio, $orotra_gral, $orotra_ind, $orotra_comu);
			$tot_orpais_muni = prim_hm_muni($cv_mun, $municipio, $orpais_gral, $orpais_ind, $orpais_comu);
			$tot_ortotal_muni = prim_hm_muni($cv_mun, $municipio, $ortotal_gral, $ortotal_ind, $ortotal_comu);
			mostrar_subnivel("total_muni", $ini_ciclo, $cv_mun, $municipio, "TOT", $tot_esc_muni, $tot_alum_muni, $tot_doc_muni, $tot_grp_muni, $tot_aula_muni, $tot_disc_muni, $tot_hl_muni, $tot_g1ro_muni, $tot_g2do_muni, $tot_g3ro_muni, $tot_g4to_muni, $tot_g5to_muni, $tot_g6to_muni, $tot_egre_muni, $tot_usaer_muni, $tot_nextj_muni, $tot_ormisma_muni, $tot_orotra_muni, $tot_orpais_muni, $tot_ortotal_muni);
		}
	}

	$ini_ciclo = "23";
	titulos_estado();

	$primaria_municipio = primaria_estadistica($link, $ini_ciclo);

	?>
	<script>
		// mapeo de columnas por tipo y categoria
		const columnasIndices = {
			'escuelas': { inicio: 4, fin: 8 },         // 5 columnas
			'alumnos': { inicio: 9, fin: 18 },         // 10 columnas
			'docentes': { inicio: 19, fin: 28 },       // 10 columnas
			'grupos': { inicio: 29, fin: 33 },         // 5 columnas
			'aulas': { inicio: 34, fin: 36 },          // 3 columnas
			'discapacidad': { inicio: 37, fin: 46 },   // 10 columnas
			'hl': { inicio: 47, fin: 56 },             // 10 columnas
			'primero': { inicio: 57, fin: 66 },        // 10 columnas
			'segundo': { inicio: 67, fin: 76 },        // 10 columnas
			'tercero': { inicio: 77, fin: 86 },        // 10 columnas
			'cuarto': { inicio: 87, fin: 96 },         // 10 columnas
			'quinto': { inicio: 97, fin: 106 },        // 10 columnas
			'sexto': { inicio: 107, fin: 116 },        // 10 columnas
			'egresados': { inicio: 117, fin: 126 },    // 10 columnas
			'usaer': { inicio: 127, fin: 136 },        // 10 columnas
			'nextj': { inicio: 137, fin: 146 },        // 10 columnas
			'ormisma': { inicio: 147, fin: 156 },      // 10 columnas
			'orotra': { inicio: 157, fin: 166 },       // 10 columnas
			'orpais': { inicio: 167, fin: 176 },       // 10 columnas
			'ortotal': { inicio: 177, fin: 186 }       // 10 columnas
		};

		function aplicarFiltros() {
			const municipioSeleccionado = document.getElementById('municipioFilter').value;
			const subnivelSeleccionado = document.getElementById('subnivelFilter').value;
			const columnaSeleccionada = document.getElementById('columnaFilter').value;

			// Filtrar filas por municipio y subnivel
			const filasDatos = document.querySelectorAll('.fila-datos');
			filasDatos.forEach(fila => {
				const municipioFila = fila.getAttribute('data-municipio');
				const subnivelFila = fila.getAttribute('data-subnivel');

				const cumpleMunicipio = municipioSeleccionado === 'todos' || municipioFila === municipioSeleccionado;
				const cumpleSubnivel = subnivelSeleccionado === 'todos' || subnivelFila === subnivelSeleccionado;

				if (cumpleMunicipio && cumpleSubnivel) {
					fila.style.display = '';
				} else {
					fila.style.display = 'none';
				}
			});

			// Filtrar columnas y cabeceras (th)
			const todasFilas = document.querySelectorAll('tr');
			const filasEncabezado = Array.from(todasFilas).filter(fila => fila.querySelector('th') || fila.querySelector('.titu_tabla'));

			// Procesamiento de las filas
			todasFilas.forEach((fila, filaIndex) => {
				// Determinar si la fila es un encabezado
				const esEncabezado = filasEncabezado.includes(fila);
				const celdas = esEncabezado ? fila.querySelectorAll('th, td.titu_tabla, td.subtitu_tabla') : fila.querySelectorAll('td');

				// Para cada celda en la fila
				for (let i = 0; i < celdas.length; i++) {
					const celda = celdas[i];

					// Las primeras 4 columnas siempre se muestran (ciclo, tipo educativo, subnivel, municipio)
					if (i <= 3) {
						celda.style.display = '';
						continue;
					}

					if (columnaSeleccionada === 'todas') {
						celda.style.display = '';
						continue;
					}

					// Para el resto de columnas, verificar si pertenecen a la seleccionada
					let mostrarColumna = false;
					const indices = columnasIndices[columnaSeleccionada];

					if (indices) {
						// Si es una celda de encabezado con colspan
						if (esEncabezado && celda.hasAttribute('colspan')) {
							const colspan = parseInt(celda.getAttribute('colspan'), 10);
							const inicioEncabezado = obtenerIndiceInicio(fila, celda);

							// Si el rango del encabezado se solapa con el rango de la columna seleccionada
							if (inicioEncabezado <= indices.fin && inicioEncabezado + colspan - 1 >= indices.inicio) {
								mostrarColumna = true;
							}
						} else {
							// Para celdas normales o de subencabezado
							mostrarColumna = (i >= indices.inicio && i <= indices.fin);
						}
					}

					celda.style.display = mostrarColumna ? '' : 'none';
				}
			});
		}

		// Función auxiliar para obtener el índice de inicio de una celda en una fila
		function obtenerIndiceInicio(fila, celda) {
			const celdas = fila.querySelectorAll('th, td');
			let indice = 0;
			let celdaActual = celdas[0];

			for (let i = 0; i < celdas.length; i++) {
				if (celdas[i] === celda) {
					return indice;
				}

				const colspan = parseInt(celdas[i].getAttribute('colspan'), 10) || 1;
				indice += colspan;
			}

			return -1; // No se encontró
		}

		function resetearFiltros() {
			document.getElementById('municipioFilter').value = 'todos';
			document.getElementById('subnivelFilter').value = 'todos';
			document.getElementById('columnaFilter').value = 'todas';

			// Restablecer todas las filas
			const filasDatos = document.querySelectorAll('.fila-datos');
			filasDatos.forEach(fila => {
				fila.style.display = '';
			});

			// Restablecer todas las celdas
			const celdas = document.querySelectorAll('th, td');
			celdas.forEach(celda => {
				celda.style.display = '';
			});
		}


		function ajustarEncabezados() {
			const columnaSeleccionada = document.getElementById('columnaFilter').value;
			if (columnaSeleccionada === 'todas') return;

			const filas = document.querySelectorAll('tr');
			const primeraFila = filas[0];
			const segundaFila = filas[1];

			// Obtener los encabezados que deben mostrarse
			const indices = columnasIndices[columnaSeleccionada];

			if (indices) {
				// Ajustar la primera fila de encabezados
				const encabezados = primeraFila.querySelectorAll('th, td.titu_tabla');
				encabezados.forEach((encabezado, i) => {
					// Las primeras 4 columnas siempre se muestran
					if (i <= 3) {
						encabezado.style.display = '';
					} else {
						// Verificar si este encabezado corresponde a la columna seleccionada
						const colspan = parseInt(encabezado.getAttribute('colspan'), 10) || 1;
						const inicioEncabezado = obtenerIndiceInicio(primeraFila, encabezado);

						if (inicioEncabezado <= indices.fin && inicioEncabezado + colspan - 1 >= indices.inicio) {
							encabezado.style.display = '';
						} else {
							encabezado.style.display = 'none';
						}
					}
				});

				// Ajustar la segunda fila de encabezados
				const subEncabezados = segundaFila.querySelectorAll('td.subtitu_tabla');
				subEncabezados.forEach((subEncabezado, i) => {
					if (i >= indices.inicio - 4 && i <= indices.fin - 4) {
						subEncabezado.style.display = '';
					} else {
						subEncabezado.style.display = 'none';
					}
				});
			}
		}

		document.addEventListener('DOMContentLoaded', function () {

			document.querySelector('button[onclick="aplicarFiltros()"]').addEventListener('click', function () {
				aplicarFiltros();
				ajustarEncabezados();
			});

			document.querySelector('button[onclick="resetearFiltros()"]').addEventListener('click', resetearFiltros);
		});
	</script>

</BODY>

</HTML>