<HTML>

<HEAD>
    <LINK REL="stylesheet" HREF="../css/estadistica.css">
    <TITLE>Media Superior</TITLE>
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
            <option value="IND">Tecnológico</option>
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


    function sin_datos_categ($cv_mun, $municipio)
    {
        $prim_cat_detalle = [
            "cv_mun" => $cv_mun,
            "municipio" => $municipio,
            "cat1_priv" => 0,
            "cat2_priv" => 0,
            "cat3_priv" => 0,
            "cat4_priv" => 0, // Nueva categoría para aulas en desuso
            "cat1_pub" => 0,
            "cat2_pub" => 0,
            "cat3_pub" => 0,
            "cat4_pub" => 0, // Nueva categoría para aulas en desuso
            "cat1_tot" => 0,
            "cat2_tot" => 0,
            "cat3_tot" => 0,
            "cat4_tot" => 0, // Nueva categoría para aulas en desuso
            "cat1_priv_usbq" => 0,
            "cat2_priv_usbq" => 0,
            "cat3_priv_usbq" => 0,
            "cat4_priv_usbq" => 0, // Nueva categoría para aulas en desuso
            "cat1_pub_usbq" => 0,
            "cat2_pub_usbq" => 0,
            "cat3_pub_usbq" => 0,
            "cat4_pub_usbq" => 0, // Nueva categoría para aulas en desuso
            "cat1_usbq" => 0,
            "cat2_usbq" => 0,
            "cat3_usbq" => 0,
            "cat4_usbq" => 0 // Nueva categoría para aulas en desuso
        ];
        return $prim_cat_detalle;
    }
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
        // Agregar nueva categoría para aulas en desuso
        if ($arr_datos["cat4_tot"] == 0) {
            echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>-</TD>";
        } else {
            echo "<TD ALIGN='CENTER' VALIGN='CENTER' CLASS='" . $css . "'>" . number_format($arr_datos["cat4_tot"], 0, '.', ',') . "</TD>";
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
            $nom_subnivel = "TECNOLOGICO";
        }
        if ((strcmp($subnivel, "TOT")) == 0) {
            $nom_subnivel = "TOTAL";
        }
        if ((strcmp($subnivel, "EDO")) == 0) {
            $nom_subnivel = "ESTADO";
        }
        return $nom_subnivel;
    }
    function mostrar_subnivel($css, $ini_ciclo, $cv_mun, $municipio, $subnivel, $esc, $alum, $doc, $grp, $aula, $disc, $hl, $g1ro, $g2do, $g3ro, $g4to, $egre, $usaer, $nextj, $ormisma, $orotra, $orpais, $ortotal)
    {
        echo "<TR class='fila-datos' data-municipio='" . $municipio . "' data-subnivel='" . $subnivel . "'>";
        echo "<TD VALIGN='center' CLASS='" . $css . "'>" . nom_ciclo($ini_ciclo) . "</TD>";
        echo "<TD VALIGN='center' CLASS='" . $css . "'>MEDIA SUPERIOR</TD>";
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
        // Se eliminan las llamadas para g5to y g6to
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
                $qr_prim_gral_esc = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, COUNT(cct_ins_pla)AS totales
										FROM nonce_pano_" . $ini_ciclo . ".ms_plantel_" . $ini_ciclo . " 
										WHERE cv_motivo = 0  AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun, subcontrol,control
										ORDER BY cv_mun,c_nom_mun, subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_esc;
            }
            //Ind es tecnológico
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_esc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control, 0 AS totales
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_esc;
            }
        }
        if ((strcmp($dato, "ALUMNO")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v397)AS totales,
										SUM(v395)AS h,SUM(v396)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_alum;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v472)AS totales,
										SUM(v470)AS h,SUM(v471)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'  
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_alum;
            }
        }
        if ((strcmp($dato, "DOCENTE")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_doc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v169)AS totales,
											SUM(V161+V163+V165+V167)AS h,SUM(V163+V164+v166+v168)AS m
											FROM nonce_pano_" . $ini_ciclo . ".ms_plantel_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'  
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_doc;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_doc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,0 AS totales,
											0 AS h,0 AS m
											FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
											WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_doc;
            }
        }
        if ((strcmp($dato, "GRUPO")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_gpr = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v401)AS totales
											FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'  
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_gpr;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_gpr = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v476)AS totales
											FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_gpr;
            }
        }
        if ((strcmp($dato, "AULA")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_aula = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v404)AS cat1,
                                    SUM(v406)AS cat2,SUM(v407)AS cat3,SUM(v405)AS cat4
                                    FROM nonce_pano_" . $ini_ciclo . ".ms_plantel_" . $ini_ciclo . " 
                                    WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
                                    GROUP BY cv_mun,c_nom_mun,subcontrol,control
                                    ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_aula;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_aula = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,0 AS cat1,
                                    0 AS cat2,0 AS cat3,0 AS cat4
                                    FROM nonce_pano_" . $ini_ciclo . ".prim_ind_" . $ini_ciclo . " 
                                    WHERE cv_estatus_captura = 0 AND cv_mun='" . $cv_mun . "' 
                                    GROUP BY cv_mun,c_nom_mun,subcontrol,control
                                    ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_aula;
            }
        }
        if ((strcmp($dato, "DISCAP")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_disc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v939)AS totales,
											SUM(v937)AS h,SUM(v938)AS m
											FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'  
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_disc;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_disc = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v1038)AS totales,
											SUM(v1036)AS h,SUM(v1037)AS m
											FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_disc;
            }
        }
        /**==========================================================================================  
                                                                                                                                                                                                                          =========  HABLANTES DE LENGUA INDIGENA
                                                                                                                                                                                                                          //en la comu -no hay datos de este tema
                                                                                                                                                                                                                          ==========================================================================================*/
        if ((strcmp($dato, "HL")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_hl = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v761)AS totales,
											SUM(v759)AS h,SUM(v760)AS m
											FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
											GROUP BY cv_mun,c_nom_mun,subcontrol,control
											ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_hl;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_hl = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v893) AS totales,
											SUM(v891)AS h,SUM(v892)AS m 		
											FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
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
                $qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v464)AS totales,
										SUM(v414+v426)AS h,SUM(v439+v451)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_alum;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v539)AS totales,
										SUM(v489+v501)AS h,SUM(v514+v526)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_alum;
            }
        }
        if ((strcmp($dato, "SEGUNDO")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v524)AS totales,
										SUM(v476+v488)AS h,SUM(v500+v512)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_alum;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v599)AS totales,
										SUM(v551+v563)AS h,SUM(v575+v587)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_alum;
            }
        }
        if ((strcmp($dato, "TERCERO")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v579)AS totales,
										SUM(v535+v546)AS h,SUM(v557+v568)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_alum;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v654)AS totales,
										SUM(v610+v621)AS h,SUM(v632+v643)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_alum;
            }
        }
        if ((strcmp($dato, "CUARTO")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_alum = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, SUM(v629)AS totales,
										SUM(v589+v599)AS h,SUM(v609+v619)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_alum;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_alum = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v704)AS totales,
										SUM(v664+v674)AS h,SUM(v684+v694)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_alum;
            }
        }
        if ((strcmp($dato, "EGRESO")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_egre = "SELECT cv_mun,c_nom_mun AS municipio, subcontrol,control, 0 AS totales,
										0 AS h,0 AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'  
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_egre;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_egre = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(0)AS totales,
										SUM(0)AS h,SUM(0)AS m
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_egre;
            }
        }

        /**==========================================================================================  
                                                                                                                                                                                                                          =========  USAER
                                                                                                                                                                                                                          //en la ind -no hay datos de este tema
                                                                                                                                                                                                                          //en la comu -no hay datos de este tema
                                                                                                                                                                                                                          ==========================================================================================*/
        if ((strcmp($dato, "USAER")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_usaer = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(0)AS totales,
											SUM(0)AS h,SUM(0)AS m
											FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
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
                $qr_prim_gral_nextj = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v797)AS totales,
											SUM(v795)AS h,SUM(v796)AS m
											FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
											WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "' 
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
                $qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v350)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_orig;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v418)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_orig;
            }
        }
        if ((strcmp($dato, "OROTRA")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v351)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_orig;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v419)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_orig;
            }
        }
        if ((strcmp($dato, "ORPAIS")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v353)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_orig;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v421)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_ind_orig;
            }
        }
        if ((strcmp($dato, "ORTOTAL")) == 0) {
            if ((strcmp($subnivel, "GRAL")) == 0) {
                $qr_prim_gral_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v354)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_gral_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
										GROUP BY cv_mun,c_nom_mun,subcontrol,control
										ORDER BY cv_mun,c_nom_mun,subcontrol,control;";
                $qr_prim_hm = $qr_prim_gral_orig;
            }
            if ((strcmp($subnivel, "IND")) == 0) {
                $qr_prim_ind_orig = "SELECT cv_mun,c_nom_mun AS municipio,subcontrol,control,SUM(v422)AS totales,
										SUM(0)AS h,SUM(0)AS m 
										FROM nonce_pano_" . $ini_ciclo . ".ms_tecno_" . $ini_ciclo . " 
										WHERE cv_motivo = 0 AND cv_mun='" . $cv_mun . "'
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
        $cat4_priv = 0; // Nueva categoría para aulas en desuso
        $cat1_pub = 0;
        $cat2_pub = 0;
        $cat3_pub = 0;
        $cat4_pub = 0; // Nueva categoría para aulas en desuso
        $cat1_tot = 0;
        $cat2_tot = 0;
        $cat3_tot = 0;
        $cat4_tot = 0; // Nueva categoría para aulas en desuso
        $cat1_priv_usbq = 0;
        $cat2_priv_usbq = 0;
        $cat3_priv_usbq = 0;
        $cat4_priv_usbq = 0; // Nueva categoría para aulas en desuso
        $cat1_pub_usbq = 0;
        $cat2_pub_usbq = 0;
        $cat3_pub_usbq = 0;
        $cat4_pub_usbq = 0; // Nueva categoría para aulas en desuso
        $cat1_usbq = 0;
        $cat2_usbq = 0;
        $cat3_usbq = 0;
        $cat4_usbq = 0; // Nueva categoría para aulas en desuso
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
                $cat4 = $row_prim_categ["cat4"]; // Nueva categoría para aulas en desuso
    
                if ((strcmp($control, "PRIVADO")) == 0) {
                    $cat1_priv = $cat1;
                    $cat2_priv = $cat2;
                    $cat3_priv = $cat3;
                    $cat4_priv = $cat4; // Nueva categoría para aulas en desuso
                    if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
                        $cat1_priv_usbq = $cat1;
                        $cat2_priv_usbq = $cat2;
                        $cat3_priv_usbq = $cat3;
                        $cat4_priv_usbq = $cat4; // Nueva categoría para aulas en desuso
                    } else {
                        $nusbq = $nusbq + $cat1;
                    }
                } else {
                    $cat1_pub = $cat1;
                    $cat2_pub = $cat2;
                    $cat3_pub = $cat3;
                    $cat4_pub = $cat4; // Nueva categoría para aulas en desuso
                    if ((strcmp($subcontrol, "FEDERAL TRANSFERIDO")) == 0) {
                        $cat1_pub_usbq = $cat1;
                        $cat2_pub_usbq = $cat2;
                        $cat3_pub_usbq = $cat3;
                        $cat4_pub_usbq = $cat4; // Nueva categoría para aulas en desuso
                    } else {
                        $nusbq = $nusbq + $cat1;
                    }
                }

                $cat1_tot = $cat1_priv + $cat1_pub;
                $cat2_tot = $cat2_priv + $cat2_pub;
                $cat3_tot = $cat3_priv + $cat3_pub;
                $cat4_tot = $cat4_priv + $cat4_pub; // Nueva categoría para aulas en desuso
                $cat1_usbq = $cat1_priv_usbq + $cat1_pub_usbq;
                $cat2_usbq = $cat2_priv_usbq + $cat2_pub_usbq;
                $cat3_usbq = $cat3_priv_usbq + $cat3_pub_usbq;
                $cat4_usbq = $cat4_priv_usbq + $cat4_pub_usbq; // Nueva categoría para aulas en desuso
            }
        } else {
            $cat1_priv = 0;
            $cat2_priv = 0;
            $cat3_priv = 0;
            $cat4_priv = 0; // Nueva categoría para aulas en desuso
            $cat1_pub = 0;
            $cat2_pub = 0;
            $cat3_pub = 0;
            $cat4_pub = 0; // Nueva categoría para aulas en desuso
            $cat1_tot = 0;
            $cat2_tot = 0;
            $cat3_tot = 0;
            $cat4_tot = 0; // Nueva categoría para aulas en desuso
            $cat1_priv_usbq = 0;
            $cat2_priv_usbq = 0;
            $cat3_priv_usbq = 0;
            $cat4_priv_usbq = 0; // Nueva categoría para aulas en desuso
            $cat1_pub_usbq = 0;
            $cat2_pub_usbq = 0;
            $cat3_pub_usbq = 0;
            $cat4_pub_usbq = 0; // Nueva categoría para aulas en desuso
            $cat1_usbq = 0;
            $cat2_usbq = 0;
            $cat3_usbq = 0;
            $cat4_usbq = 0; // Nueva categoría para aulas en desuso
        }

        $prim_cat_detalle = [
            "cv_mun" => $cv_mun,
            "municipio" => $municipio,
            "cat1_priv" => $cat1_priv,
            "cat2_priv" => $cat2_priv,
            "cat3_priv" => $cat3_priv,
            "cat4_priv" => $cat4_priv, // Nueva categoría para aulas en desuso
            "cat1_pub" => $cat1_pub,
            "cat2_pub" => $cat2_pub,
            "cat3_pub" => $cat3_pub,
            "cat4_pub" => $cat4_pub, // Nueva categoría para aulas en desuso
            "cat1_tot" => $cat1_tot,
            "cat2_tot" => $cat2_tot,
            "cat3_tot" => $cat3_tot,
            "cat4_tot" => $cat4_tot, // Nueva categoría para aulas en desuso
            "cat1_priv_usbq" => $cat1_priv_usbq,
            "cat2_priv_usbq" => $cat2_priv_usbq,
            "cat3_priv_usbq" => $cat3_priv_usbq,
            "cat4_priv_usbq" => $cat4_priv_usbq, // Nueva categoría para aulas en desuso
            "cat1_pub_usbq" => $cat1_pub_usbq,
            "cat2_pub_usbq" => $cat2_pub_usbq,
            "cat3_pub_usbq" => $cat3_pub_usbq,
            "cat4_pub_usbq" => $cat4_pub_usbq, // Nueva categoría para aulas en desuso
            "cat1_usbq" => $cat1_usbq,
            "cat2_usbq" => $cat2_usbq,
            "cat3_usbq" => $cat3_usbq,
            "cat4_usbq" => $cat4_usbq // Nueva categoría para aulas en desuso
        ];
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
        $cat4_priv = $gral["cat4_priv"] + $ind["cat4_priv"] + $comu["cat4_priv"]; // Nueva categoría
        $cat1_pub = $gral["cat1_pub"] + $ind["cat1_pub"] + $comu["cat1_pub"];
        $cat2_pub = $gral["cat2_pub"] + $ind["cat2_pub"] + $comu["cat2_pub"];
        $cat3_pub = $gral["cat3_pub"] + $ind["cat3_pub"] + $comu["cat3_pub"];
        $cat4_pub = $gral["cat4_pub"] + $ind["cat4_pub"] + $comu["cat4_pub"]; // Nueva categoría
        $cat1_tot = $gral["cat1_tot"] + $ind["cat1_tot"] + $comu["cat1_tot"];
        $cat2_tot = $gral["cat2_tot"] + $ind["cat2_tot"] + $comu["cat2_tot"];
        $cat3_tot = $gral["cat3_tot"] + $ind["cat3_tot"] + $comu["cat3_tot"];
        $cat4_tot = $gral["cat4_tot"] + $ind["cat4_tot"] + $comu["cat4_tot"]; // Nueva categoría
        $cat1_priv_usbq = $gral["cat1_priv_usbq"] + $ind["cat1_priv_usbq"] + $comu["cat1_priv_usbq"];
        $cat2_priv_usbq = $gral["cat2_priv_usbq"] + $ind["cat2_priv_usbq"] + $comu["cat2_priv_usbq"];
        $cat3_priv_usbq = $gral["cat3_priv_usbq"] + $ind["cat3_priv_usbq"] + $comu["cat3_priv_usbq"];
        $cat4_priv_usbq = $gral["cat4_priv_usbq"] + $ind["cat4_priv_usbq"] + $comu["cat4_priv_usbq"]; // Nueva categoría
        $cat1_pub_usbq = $gral["cat1_pub_usbq"] + $ind["cat1_pub_usbq"] + $comu["cat1_pub_usbq"];
        $cat2_pub_usbq = $gral["cat2_pub_usbq"] + $ind["cat2_pub_usbq"] + $comu["cat2_pub_usbq"];
        $cat3_pub_usbq = $gral["cat3_pub_usbq"] + $ind["cat3_pub_usbq"] + $comu["cat3_pub_usbq"];
        $cat4_pub_usbq = $gral["cat4_pub_usbq"] + $ind["cat4_pub_usbq"] + $comu["cat4_pub_usbq"]; // Nueva categoría
        $cat1_usbq = $gral["cat1_usbq"] + $ind["cat1_usbq"] + $comu["cat1_usbq"];
        $cat2_usbq = $gral["cat2_usbq"] + $ind["cat2_usbq"] + $comu["cat2_usbq"];
        $cat3_usbq = $gral["cat3_usbq"] + $ind["cat3_usbq"] + $comu["cat3_usbq"];
        $cat4_usbq = $gral["cat4_usbq"] + $ind["cat4_usbq"] + $comu["cat4_usbq"]; // Nueva categoría
    
        $prim_cat_detalle = [
            "cv_mun" => $cv_mun,
            "municipio" => $municipio,
            "cat1_priv" => $cat1_priv,
            "cat2_priv" => $cat2_priv,
            "cat3_priv" => $cat3_priv,
            "cat4_priv" => $cat4_priv, // Nueva categoría
            "cat1_pub" => $cat1_pub,
            "cat2_pub" => $cat2_pub,
            "cat3_pub" => $cat3_pub,
            "cat4_pub" => $cat4_pub, // Nueva categoría
            "cat1_tot" => $cat1_tot,
            "cat2_tot" => $cat2_tot,
            "cat3_tot" => $cat3_tot,
            "cat4_tot" => $cat4_tot, // Nueva categoría
            "cat1_priv_usbq" => $cat1_priv_usbq,
            "cat2_priv_usbq" => $cat2_priv_usbq,
            "cat3_priv_usbq" => $cat3_priv_usbq,
            "cat4_priv_usbq" => $cat4_priv_usbq, // Nueva categoría
            "cat1_pub_usbq" => $cat1_pub_usbq,
            "cat2_pub_usbq" => $cat2_pub_usbq,
            "cat3_pub_usbq" => $cat3_pub_usbq,
            "cat4_pub_usbq" => $cat4_pub_usbq, // Nueva categoría
            "cat1_usbq" => $cat1_usbq,
            "cat2_usbq" => $cat2_usbq,
            "cat3_usbq" => $cat3_usbq,
            "cat4_usbq" => $cat4_usbq // Nueva categoría
        ];
        return $prim_cat_detalle;
    }
    function primaria_estadistica($link, $ini_ciclo)
    {
        $qr_lista_muni = "SELECT cv_mun, c_nom_mun AS municipio
                         FROM nonce_pano_" . $ini_ciclo . ".ms_plantel_" . $ini_ciclo . " 
                         WHERE cv_motivo = 0
                         GROUP BY cv_mun, c_nom_mun
                         ORDER BY cv_mun, c_nom_mun;";

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
            // Se eliminan g5to_gral y g6to_gral
            $egre_gral = prim_hm($link, "EGRESO", "GRAL", $ini_ciclo, $cv_mun, $municipio);
            $usaer_gral = prim_hm($link, "USAER", "GRAL", $ini_ciclo, $cv_mun, $municipio);
            $nextj_gral = prim_hm($link, "NEXTJ", "GRAL", $ini_ciclo, $cv_mun, $municipio);
            $ormisma_gral = prim_hm($link, "ORMISMA", "GRAL", $ini_ciclo, $cv_mun, $municipio);
            $orotra_gral = prim_hm($link, "OROTRA", "GRAL", $ini_ciclo, $cv_mun, $municipio);
            $orpais_gral = prim_hm($link, "ORPAIS", "GRAL", $ini_ciclo, $cv_mun, $municipio);
            $ortotal_gral = prim_hm($link, "ORTOTAL", "GRAL", $ini_ciclo, $cv_mun, $municipio);
            mostrar_subnivel("datos_subnivel", $ini_ciclo, $cv_mun, $municipio, "GRAL", $esc_gral, $alum_gral, $doc_gral, $grp_gral, $aula_gral, $disc_gral, $hl_gral, $g1ro_gral, $g2do_gral, $g3ro_gral, $g4to_gral, $egre_gral, $usaer_gral, $nextj_gral, $ormisma_gral, $orotra_gral, $orpais_gral, $ortotal_gral);

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
            // Se eliminan g5to_ind y g6to_ind
            $egre_ind = prim_hm($link, "EGRESO", "IND", $ini_ciclo, $cv_mun, $municipio);
            $usaer_ind = sin_datos_hm($cv_mun, $municipio);
            $nextj_ind = sin_datos_hm($cv_mun, $municipio);
            $ormisma_ind = prim_hm($link, "ORMISMA", "IND", $ini_ciclo, $cv_mun, $municipio);
            $orotra_ind = prim_hm($link, "OROTRA", "IND", $ini_ciclo, $cv_mun, $municipio);
            $orpais_ind = prim_hm($link, "ORPAIS", "IND", $ini_ciclo, $cv_mun, $municipio);
            $ortotal_ind = prim_hm($link, "ORTOTAL", "IND", $ini_ciclo, $cv_mun, $municipio);
            mostrar_subnivel("datos_subnivel", $ini_ciclo, $cv_mun, $municipio, "IND", $esc_ind, $alum_ind, $doc_ind, $grp_ind, $aula_ind, $disc_ind, $hl_ind, $g1ro_ind, $g2do_ind, $g3ro_ind, $g4to_ind, $egre_ind, $usaer_ind, $nextj_ind, $ormisma_ind, $orotra_ind, $orpais_ind, $ortotal_ind);

            // Se elimina todo el bloque para el subnivel comunitario (COMU)
    
            // Calcular totales solo con GRAL e IND
            $tot_esc_muni = prim_conteo_muni($cv_mun, $municipio, $esc_gral, $esc_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_alum_muni = prim_hm_muni($cv_mun, $municipio, $alum_gral, $alum_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_doc_muni = prim_hm_muni($cv_mun, $municipio, $doc_gral, $doc_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_grp_muni = prim_conteo_muni($cv_mun, $municipio, $grp_gral, $grp_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_aula_muni = prim_categ_muni($cv_mun, $municipio, $aula_gral, $aula_ind, sin_datos_categ($cv_mun, $municipio)); // Usar sin_datos_categ aquí
            $tot_disc_muni = prim_hm_muni($cv_mun, $municipio, $disc_gral, $disc_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_hl_muni = prim_hm_muni($cv_mun, $municipio, $hl_gral, $hl_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_g1ro_muni = prim_hm_muni($cv_mun, $municipio, $g1ro_gral, $g1ro_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_g2do_muni = prim_hm_muni($cv_mun, $municipio, $g2do_gral, $g2do_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_g3ro_muni = prim_hm_muni($cv_mun, $municipio, $g3ro_gral, $g3ro_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_g4to_muni = prim_hm_muni($cv_mun, $municipio, $g4to_gral, $g4to_ind, sin_datos_hm($cv_mun, $municipio));
            // Se eliminan tot_g5to_muni y tot_g6to_muni
            $tot_egre_muni = prim_hm_muni($cv_mun, $municipio, $egre_gral, $egre_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_usaer_muni = prim_hm_muni($cv_mun, $municipio, $usaer_gral, $usaer_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_nextj_muni = prim_hm_muni($cv_mun, $municipio, $nextj_gral, $nextj_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_ormisma_muni = prim_hm_muni($cv_mun, $municipio, $ormisma_gral, $ormisma_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_orotra_muni = prim_hm_muni($cv_mun, $municipio, $orotra_gral, $orotra_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_orpais_muni = prim_hm_muni($cv_mun, $municipio, $orpais_gral, $orpais_ind, sin_datos_hm($cv_mun, $municipio));
            $tot_ortotal_muni = prim_hm_muni($cv_mun, $municipio, $ortotal_gral, $ortotal_ind, sin_datos_hm($cv_mun, $municipio));
            mostrar_subnivel("total_muni", $ini_ciclo, $cv_mun, $municipio, "TOT", $tot_esc_muni, $tot_alum_muni, $tot_doc_muni, $tot_grp_muni, $tot_aula_muni, $tot_disc_muni, $tot_hl_muni, $tot_g1ro_muni, $tot_g2do_muni, $tot_g3ro_muni, $tot_g4to_muni, $tot_egre_muni, $tot_usaer_muni, $tot_nextj_muni, $tot_ormisma_muni, $tot_orotra_muni, $tot_orpais_muni, $tot_ortotal_muni);
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
            'aulas': { inicio: 34, fin: 37 },          // 4 columnas (antes eran 3)
            // Actualizar los índices de inicio para las siguientes categorías
            'discapacidad': { inicio: 38, fin: 47 },   // 10 columnas 
            'hl': { inicio: 48, fin: 57 },             // 10 columnas
            'primero': { inicio: 58, fin: 67 },        // 10 columnas
            'segundo': { inicio: 68, fin: 77 },        // 10 columnas
            'tercero': { inicio: 78, fin: 87 },        // 10 columnas
            'cuarto': { inicio: 88, fin: 97 },         // 10 columnas
            'egresados': { inicio: 98, fin: 107 },     // 10 columnas
            'usaer': { inicio: 108, fin: 117 },        // 10 columnas
            'nextj': { inicio: 118, fin: 127 },        // 10 columnas
            'ormisma': { inicio: 128, fin: 137 },      // 10 columnas
            'orotra': { inicio: 138, fin: 147 },       // 10 columnas
            'orpais': { inicio: 148, fin: 157 },       // 10 columnas
            'ortotal': { inicio: 158, fin: 167 }       // 10 columnas
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