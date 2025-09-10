<?php
/**
 * =============================================================================
 * CONEXIÓN DE PRUEBA REESTRUCTURADA - BASADA EN BOLSILLO (1)(1).PHP
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Este archivo replica exactamente la estructura y consultas del archivo bolsillo
 * pero con parámetros seguros y estructura moderna para consultas dinámicas
 * por municipio.
 * 
 * @author Sistema SEDEQ
 * @version 2.0.0
 * @since 2025
 */


define('CICLO_ESCOLAR_ACTUAL', '24');

/**
 * Función para obtener el ciclo escolar actual
 * Punto centralizado para toda la aplicación
 */
function obtenerCicloEscolarActual()
{
    return CICLO_ESCOLAR_ACTUAL;
}

/**
 * Función para obtener información del ciclo escolar
 */
function obtenerInfoCicloEscolar()
{
    $ciclo = CICLO_ESCOLAR_ACTUAL;
    $anio_inicio = 2000 + intval($ciclo);
    $anio_fin = $anio_inicio + 1;

    return [
        'ciclo_corto' => $ciclo,
        'ciclo_completo' => "$anio_inicio-$anio_fin",
        'esquema_bd' => "nonce_pano_$ciclo",
        'descripcion' => "Ciclo Escolar $anio_inicio-$anio_fin"
    ];
}

// =============================================================================
// CONFIGURACIÓN DE CONEXIÓN
// =============================================================================

function ConectarsePrueba()
{
    if (!function_exists('pg_connect')) {
        error_log('SEDEQ: Extensiones PostgreSQL no disponibles');
        return false;
    }

    try {
        $connectionString = "host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres options='--client_encoding=UTF8'";
        $conn = pg_connect($connectionString);

        if (!$conn) {
            error_log('SEDEQ: Error de conexión - ' . pg_last_error());
            return false;
        }

        // Establecer el encoding para manejar caracteres acentuados correctamente
        pg_set_client_encoding($conn, "UTF8");

        return $conn;
    } catch (Exception $e) {
        error_log('SEDEQ: Excepción en conexión: ' . $e->getMessage());
        return false;
    }
}

// Mantener compatibilidad con función original
function Conectarse()
{
    return ConectarsePrueba();
}

// =============================================================================
// MAPEO DE MUNICIPIOS (IGUAL QUE BOLSILLO)
// =============================================================================

/**
 * Mapeo exacto de municipios como en bolsillo - nombres con acentos correctos
 * @param string $num_munic Número del municipio (1-18)
 * @return string Nombre del municipio
 */
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

    return isset($nom_munic[$num_munic]) ? $nom_munic[$num_munic] : null;
}

/**
 * Obtiene todos los municipios usando el mapeo local (no la base de datos)
 * Esto evita problemas de encoding que teníamos obteniendo desde PostgreSQL
 * @return array Array con todos los municipios correctamente nombrados
 */
function obtenerMunicipiosPrueba2024()
{
    $municipios = [];
    for ($i = 1; $i <= 18; $i++) {
        $nombre = nombre_municipio((string) $i);
        if ($nombre) {
            $municipios[] = $nombre;
        }
    }
    return $municipios;
}

// =============================================================================
// SISTEMA DE CONSULTAS DINÁMICAS BASADO EN BOLSILLO
// =============================================================================

/**
 * Replica exactamente la función str_consulta de bolsillo
 * 
 * @param string $str_consulta Tipo de consulta
 * @param string $ini_ciclo Ciclo escolar
 * @param string $filtroMunicipio Filtro preparado para municipio
 * @return string|false SQL generado
 */
function str_consulta_segura($str_consulta, $ini_ciclo, $filtro)
{
    // Replicar exactamente como bolsillo: filtro base + filtro
    $filtroBase = "(cv_estatus_captura = 0 OR cv_estatus_captura = 10)";

    switch ($str_consulta) {
        case 'gral_ini':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V398+V414) AS total_matricula, 6210 2584+3108
                        SUM(V390+V406) AS mat_hombres,
                        SUM(V394+V410) AS mat_mujeres,
                        SUM(V509+V516+V523+V511+V518+V525+V510+V517+V524+V512+V519+V526) AS total_docentes,
                        SUM(V509+V516+V523+V511+V518+V525) AS doc_hombres,
                        SUM(V510+V517+V524+V512+V519+V526) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V402+V418) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'gral_ini_dir_grp':
            return "SELECT CONCAT('GENERAL DIR CON GRUPO') AS titulo_fila, 
                        SUM(0) AS total_matricula,
                        SUM(0) AS mat_hombres,
                        SUM(0) AS mat_mujeres, 
                        SUM(v787) AS total_docentes,
                        SUM(v785) AS doc_hombres,
                        SUM(v786) AS doc_mujeres, 
                        SUM(0) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                    WHERE $filtroBase AND V478='0' $filtro";

        case 'ind_ini':
            return "SELECT CONCAT('INDIGENA') AS titulo_fila,
                        SUM(V183+V184) AS total_matricula,
                        SUM(V183) AS mat_hombres,
                        SUM(V184) AS mat_mujeres,
                        SUM(V291) AS total_docentes, 
                        SUM(V211) AS doc_hombres,
                        SUM(V212) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V100) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_ind_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'lact_ini':
            return "SELECT CONCAT('LACTANTE') AS titulo_fila,
                        SUM(V398) AS total_matricula,
                        SUM(V390) AS mat_hombres,
                        SUM(V394) AS mat_mujeres,
                        SUM(V509+V516+V523+V510+V517+V524) AS total_docentes,
                        SUM(V509+V516+V523) AS doc_hombres,
                        SUM(V510+V517+V524) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V402) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'mater_ini':
            return "SELECT CONCAT('MATERNAL') AS titulo_fila,
                        SUM(V414) AS total_matricula,
                        SUM(V406) AS mat_hombres,
                        SUM(V410) AS mat_mujeres,
                        SUM(V511+V518+V525+V512+V519+V526) AS total_docentes,
                        SUM(V511+V518+V525) AS doc_hombres,
                        SUM(V512+V519+V526) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V418) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'comuni_ini':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V81) AS total_matricula,
                        SUM(V79) AS mat_hombres,
                        SUM(V80) AS mat_mujeres,
                        SUM(V126) AS total_docentes,
                        SUM(V124) AS doc_hombres,
                        SUM(V125) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_comuni_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'ne_ini':
            return "SELECT CONCAT('NO ESCOLARIZADA') AS titulo_fila,
                        SUM(V129 + V130) AS total_matricula,
                        SUM(V129) AS mat_hombres,
                        SUM(V130) AS mat_mujeres,
                        SUM(V183 + V184) AS total_docentes,
                        SUM(V183) AS doc_hombres,
                        SUM(V184) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_ne_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'gral_pree':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        SUM(V165) AS mat_hombres,
                        SUM(V171) AS mat_mujeres,
                        SUM(V867+V868+V859+V860) AS total_docentes,
                        SUM(V859+V868) AS doc_hombres,
                        SUM(V860+V868) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V182) AS grupos 
                    FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'ind_pree':
            return "SELECT CONCAT('INDIGENA') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        SUM(V165) AS mat_hombres,
                        SUM(V171) AS mat_mujeres,
                        SUM(V795+V803+V796+V804) AS total_docentes,
                        SUM(V795+V803) AS doc_hombres,
                        SUM(V796+V804) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V182) AS grupos 
                    FROM nonce_pano_$ini_ciclo.pree_ind_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'comuni_pree':
            return "SELECT CONCAT('COMUNITARIO') AS titulo_fila,
                        SUM(V97) AS total_matricula,
                        SUM(V85) AS mat_hombres,
                        SUM(V91) AS mat_mujeres,
                        SUM(V151) AS total_docentes,
                        SUM(V149) AS doc_hombres,
                        SUM(V150) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        (COUNT(cv_cct)-SUM(V78)) AS grupos 
                    FROM nonce_pano_$ini_ciclo.pree_comuni_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'gral_prim':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V608) AS total_matricula,
                        SUM(V562+V573) AS mat_hombres,
                        SUM(V585+V596) AS mat_mujeres,
                         SUM(V1575+V1576+V1567+V1568+V1507+V1499+V1508+V1500+V583+V584) AS total_docentes
                        SUM(V1575+V1567) AS doc_hombres,
                        SUM(V1576+V1568) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V616) AS grupos 
                    FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'ind_prim':
            return "SELECT CONCAT('INDIGENA') AS titulo_fila,
                        SUM(V610) AS total_matricula,
                        SUM(V564+V575) AS mat_hombres,
                        SUM(V587+V598) AS mat_mujeres,
                        SUM(V1507+V1499+V1508+V1500) AS total_docentes,
                        SUM(V1507+V1499) AS doc_hombres,
                        SUM(V1508+V1500) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V1052) AS grupos 
                    FROM nonce_pano_$ini_ciclo.prim_ind_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'comuni_prim':
            return "SELECT CONCAT('COMUNITARIO') AS titulo_fila,
                        SUM(V515) AS total_matricula,
                        SUM(V469+V480) AS mat_hombres,
                        SUM(V492+V503) AS mat_mujeres,
                        SUM(V585) AS total_docentes,
                        SUM(V583) AS doc_hombres,
                        SUM(V584) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        COUNT(cv_cct) AS grupos 
                    FROM nonce_pano_$ini_ciclo.prim_comuni_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'gral_sec':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V340) AS total_matricula,
                        SUM(V306+V314) AS mat_hombres,
                        SUM(V323+V331) AS mat_mujeres,
                        SUM(V1401) AS total_docentes,
                        SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,
                        SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V341) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        case 'comuni_sec':
            return "SELECT CONCAT('COMUNITARIO') AS titulo_fila,
                        SUM(V257) AS total_matricula,
                        SUM(V223+V231) AS mat_hombres,
                        SUM(V240+V248) AS mat_mujeres,
                        SUM(V386) AS total_docentes,
                        SUM(V384) AS doc_hombres,
                        SUM(V385) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        COUNT(cv_cct) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sec_comuni_$ini_ciclo 
                    WHERE $filtroBase $filtro";

        // ===== CONSULTAS SECUNDARIA DETALLADAS =====
        case 'ini_1ro_pree':
            return "SELECT CONCAT('INI_1ro') AS titulo_fila,
                        SUM(V478) AS total_matricula,
                        SUM(V466) AS mat_hombres,
                        SUM(V472) AS mat_mujeres,
                        SUM(V787+V513+V520+V527+V514+V521+V528) AS total_docentes,
                        SUM(V785+V513+V520+V527) AS doc_hombres,
                        SUM(V786+V514+V521+V528) AS doc_mujeres,
                        SUM(0) AS escuelas,
                        SUM(V479) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro AND V478>'0'";

        case 'sec_gral_gral':
            return "SELECT CONCAT('GENERAL') AS titulo_fila,
                        SUM(V340) AS total_matricula,
                        SUM(V306+V314) AS mat_hombres,
                        SUM(V323+V331) AS mat_mujeres,
                        SUM(V1401) AS total_docentes,
                        SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,
                        SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V341) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro AND subnivel='GENERAL'";

        case 'sec_gral_tele':
            return "SELECT CONCAT('TELESECUNDARIA') AS titulo_fila,
                        SUM(V340) AS total_matricula,
                        SUM(V306+V314) AS mat_hombres,
                        SUM(V323+V331) AS mat_mujeres,
                        SUM(V1401) AS total_docentes,
                        SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,
                        SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V813) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro AND subnivel='TELESECUNDARIA'";

        case 'sec_gral_tec':
            return "SELECT CONCAT('TECNICA') AS titulo_fila,
                        SUM(V340) AS total_matricula,
                        SUM(V306+V314) AS mat_hombres,
                        SUM(V323+V331) AS mat_mujeres,
                        SUM(V1401) AS total_docentes,
                        SUM(V1297+V1303+V1307+V1309+V1311+V1313) AS doc_hombres,
                        SUM(V1298+V1304+V1308+V1310+V1312+V1314) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V341) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo 
                    WHERE $filtroBase $filtro AND subnivel<>'TELESECUNDARIA' AND subnivel<>'GENERAL'";

        // ===== CONSULTAS MEDIA SUPERIOR =====
        case 'bgral_msup':
            return "SELECT CONCAT('BACHILLERATO GENERAL') AS titulo_fila,
                        SUM(V397) AS total_matricula,
                        SUM(V395) AS mat_hombres,
                        SUM(V396) AS mat_mujeres,
                        SUM(V960) AS total_docentes,
                        SUM(V958) AS doc_hombres,
                        SUM(V959) AS doc_mujeres,
                        COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,
                        SUM(V401) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro";

        case 'btecno_msup':
            return "SELECT CONCAT('BACHILLERATO TECNOLOGICO') AS titulo_fila,
                        SUM(V472) AS total_matricula,
                        SUM(V470) AS mat_hombres,
                        SUM(V471) AS mat_mujeres,
                        SUM(V1059) AS total_docentes,
                        SUM(V1057) AS doc_hombres,
                        SUM(V1058) AS doc_mujeres,
                        COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,
                        SUM(V476) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro";

        case 'btecno_tecno_msup':
            return "SELECT CONCAT('BACHILLERATO TECNOLOGICO') AS titulo_fila,
                        SUM(V472) AS total_matricula,
                        SUM(V470) AS mat_hombres,
                        SUM(V471) AS mat_mujeres,
                        SUM(V1059) AS total_docentes,
                        SUM(V1057) AS doc_hombres,
                        SUM(V1058) AS doc_mujeres,
                        COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,
                        SUM(V476) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') AND cv_servicion3='2' $filtro";

        case 'btecno_pbach_msup':
            return "SELECT CONCAT('PROFESIONAL TECNICO BACHILLER') AS titulo_fila,
                        SUM(V472) AS total_matricula,
                        SUM(V470) AS mat_hombres,
                        SUM(V471) AS mat_mujeres,
                        SUM(V1059) AS total_docentes,
                        SUM(V1057) AS doc_hombres,
                        SUM(V1058) AS doc_mujeres,
                        COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,
                        SUM(V476) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') AND cv_servicion3='3' $filtro";

        case 'btecno_ptecno_msup':
            return "SELECT CONCAT('PROFESIONAL TECNICO') AS titulo_fila,
                        SUM(V472) AS total_matricula,
                        SUM(V470) AS mat_hombres,
                        SUM(V471) AS mat_mujeres,
                        SUM(V1059) AS total_docentes,
                        SUM(V1057) AS doc_hombres,
                        SUM(V1058) AS doc_mujeres,
                        COUNT(DISTINCT CONCAT(cct_ins_pla,'-',cv_cct,'-',c_turno)) AS escuelas,
                        SUM(V476) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') AND cv_servicion3='4' $filtro";

        case 'plant_doc_esc_msup':
            return "SELECT CONCAT('DOCENTES PLANTEL') AS titulo_fila,
                        SUM(0) AS total_matricula,
                        SUM(0) AS mat_hombres,
                        SUM(0) AS mat_mujeres,
                        SUM(V106+V101) AS total_docentes,
                        SUM(V104+V99) AS doc_hombres,
                        SUM(V105+V100) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.ms_plantel_$ini_ciclo 
                    WHERE cv_motivo = '0' $filtro";

        // ===== CONSULTAS SUPERIOR =====
        case 'carr_lic_sup':
            return "SELECT CONCAT('LICENCIATURA') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        SUM(V175) AS mat_hombres,
                        SUM(V176) AS mat_mujeres,
                        SUM(0) AS total_docentes,
                        SUM(0) AS doc_hombres,
                        SUM(0) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                    WHERE cv_motivo = '0' $filtro";

        case 'carr_normal_sup':
            return "SELECT CONCAT('NORMAL') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        SUM(V175) AS mat_hombres,
                        SUM(V176) AS mat_mujeres,
                        SUM(0) AS total_docentes,
                        SUM(0) AS doc_hombres,
                        SUM(0) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (subsistema_3 LIKE '%Normal%' OR subsistema_3 LIKE '%NORMAL%') $filtro";

        case 'carr_tecno_sup':
            return "SELECT CONCAT('UNIVERSITARIA Y TECNOLOGICA') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        SUM(V175) AS mat_hombres,
                        SUM(V176) AS mat_mujeres,
                        SUM(0) AS total_docentes,
                        SUM(0) AS doc_hombres,
                        SUM(0) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (subsistema_3 NOT LIKE '%Normal%' AND subsistema_3 NOT LIKE '%NORMAL%') $filtro";

        case 'posgr_sup':
            return "SELECT CONCAT('POSGRADO') AS titulo_fila,
                        SUM(V142) AS total_matricula,
                        SUM(V140) AS mat_hombres,
                        SUM(V141) AS mat_mujeres,
                        SUM(0) AS total_docentes,
                        SUM(0) AS doc_hombres,
                        SUM(0) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_posgrado_$ini_ciclo 
                    WHERE cv_motivo = '0' $filtro";

        case 'esc_lic_sup':
            return "SELECT CONCAT('ESCUELA LICENCIATURA') AS titulo_fila,
                        SUM(V214+V218) AS total_matricula,
                        SUM(0) AS mat_hombres,
                        SUM(0) AS mat_mujeres,
                        SUM(V944+V768) AS total_docentes,
                        SUM(V942+V766) AS doc_hombres,
                        SUM(V943+V767) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo 
                    WHERE cv_motivo = '0' AND (V944>'0' OR V768>'0') $filtro";

        case 'esc_docentes_sup':
            return "SELECT CONCAT('DOCENTES SUPERIOR') AS titulo_fila,
                        SUM(0) AS total_matricula,
                        SUM(0) AS mat_hombres,
                        SUM(0) AS mat_mujeres,
                        SUM(V83) AS total_docentes,
                        SUM(V81) AS doc_hombres,
                        SUM(V82) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo 
                    WHERE cv_motivo = '0' $filtro";

        case 'carr_usbq_tsu_sup':
            return "SELECT CONCAT('TECNICO SUPERIOR') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        SUM(V175) AS mat_hombres,
                        SUM(V176) AS mat_mujeres,
                        SUM(0) AS total_docentes,
                        SUM(0) AS doc_hombres,
                        SUM(0) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                    WHERE cv_motivo = '0' AND cv_carrera LIKE '4%' $filtro";

        case 'carr_usbq_lic_sup':
            return "SELECT CONCAT('LICENCIATURA USBQ') AS titulo_fila,
                        SUM(V177) AS total_matricula,
                        SUM(V175) AS mat_hombres,
                        SUM(V176) AS mat_mujeres,
                        SUM(0) AS total_docentes,
                        SUM(0) AS doc_hombres,
                        SUM(0) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                    WHERE cv_motivo = '0' AND cv_carrera LIKE '5%' $filtro";

        case 'unidades_sup':
            return "SELECT CONCAT('UNIDADES SUPERIOR') AS titulo_fila,
                        SUM(total_matricula) AS total_matricula,
                        SUM(mat_hombres) AS mat_hombres,
                        SUM(mat_mujeres) AS mat_mujeres,
                        SUM(total_docentes) AS total_docentes,
                        SUM(doc_hombres) AS doc_hombres,
                        SUM(doc_mujeres) AS doc_mujeres,
                        COUNT(DISTINCT cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.sup_unidades_$ini_ciclo 
                    WHERE 1=1 $filtro";

        // ===== CONSULTAS ESPECIALES =====
        case 'especial_tot':
            return "SELECT CONCAT('ESPECIAL (CAM)') AS titulo_fila,
                        SUM(V2257) AS total_matricula,
                        SUM(V2255) AS mat_hombres,
                        SUM(V2256) AS mat_mujeres,
                        SUM(V2496) AS total_docentes,
                        SUM(V2302) AS doc_hombres,
                        SUM(V2303) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V1343+V1418+V1511+V1586+V1765) AS grupos 
                    FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo 
                    WHERE cv_estatus_captura = 0 $filtro";

        case 'especial_ini':
            return "SELECT CONCAT('ESPECIAL INICIAL') AS titulo_fila,
                        SUM(V1338+V1340+V1339+V1341) AS total_matricula,
                        SUM(V1338+V1340) AS mat_hombres,
                        SUM(V1339+V1341) AS mat_mujeres,
                        SUM(V2496) AS total_docentes,
                        SUM(V2302) AS doc_hombres,
                        SUM(V2303) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V1343) AS grupos 
                    FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo 
                    WHERE cv_estatus_captura = 0 $filtro";

        case 'especial_pree':
            return "SELECT CONCAT('ESPECIAL PREESCOLAR') AS titulo_fila,
                        SUM(V1413+V1415+V1414+V1416) AS total_matricula,
                        SUM(V1413+V1415) AS mat_hombres,
                        SUM(V1414+V1416) AS mat_mujeres,
                        SUM(V2496) AS total_docentes,
                        SUM(V2302) AS doc_hombres,
                        SUM(V2303) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V1418) AS grupos 
                    FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo 
                    WHERE cv_estatus_captura = 0 $filtro";

        case 'especial_prim':
            return "SELECT CONCAT('ESPECIAL PRIMARIA') AS titulo_fila,
                        SUM(V1506+V1508+V1507+V1509) AS total_matricula,
                        SUM(V1506+V1508) AS mat_hombres,
                        SUM(V1507+V1509) AS mat_mujeres,
                        SUM(V2496) AS total_docentes,
                        SUM(V2302) AS doc_hombres,
                        SUM(V2303) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V1511) AS grupos 
                    FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo 
                    WHERE cv_estatus_captura = 0 $filtro";

        case 'especial_sec':
            return "SELECT CONCAT('ESPECIAL SECUNDARIA') AS titulo_fila,
                        SUM(V1581+V1583+V1582+V1584) AS total_matricula,
                        SUM(V1581+V1583) AS mat_hombres,
                        SUM(V1582+V1584) AS mat_mujeres,
                        SUM(V2496) AS total_docentes,
                        SUM(V2302) AS doc_hombres,
                        SUM(V2303) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V1586) AS grupos 
                    FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo 
                    WHERE cv_estatus_captura = 0 $filtro";

        case 'especial_usaer':
            return "SELECT CONCAT('ESPECIAL USAER') AS titulo_fila,
                        SUM(v2827) AS total_matricula,
                        SUM(V2814+V2816+V2818+V2820) AS mat_hombres,
                        SUM(V2815+V2817+V2819+V2821) AS mat_mujeres,
                        SUM(v2828+V2973+V2974) AS total_docentes,
                        SUM(V2973) AS doc_hombres,
                        SUM(V2974) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(0) AS grupos 
                    FROM nonce_pano_$ini_ciclo.esp_usaer_$ini_ciclo 
                    WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) $filtro";

        // CONSULTAS AGREGADAS (como en bolsillo para obtenerResumenMunicipioCompleto)
        case 'inicial_esc':
            return "SELECT 'INICIAL ESCOLARIZADA' AS titulo_fila,
                        SUM(V398+V414+V183+V184) AS total_matricula,
                        SUM(V390+V406+V183) AS mat_hombres,
                        SUM(V394+V410+V184) AS mat_mujeres,
                        SUM(V509+V516+V523+V511+V518+V525+V510+V517+V524+V512+V519+V526+V291+V787) AS total_docentes,
                        SUM(V509+V516+V523+V511+V518+V525+V211+V785) AS doc_hombres,
                        SUM(V510+V517+V524+V512+V519+V526+V212+V786) AS doc_mujeres,
                        COUNT(DISTINCT cv_cct) AS escuelas,
                        SUM(V402+V418+V100) AS grupos
                    FROM (
                        SELECT cv_cct, c_nom_mun, control, V398,V414,V390,V406,V394,V410,
                               V509,V516,V523,V511,V518,V525,V510,V517,V524,V512,V519,V526,
                               V402,V418, 0 as V183, 0 as V184, 0 as V291, 0 as V211, 0 as V212, 0 as V100,
                               V787,V785,V786
                        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
                               V183,V184,V291,V211,V212,V100,0,0,0
                        FROM nonce_pano_$ini_ciclo.ini_ind_$ini_ciclo 
                        WHERE $filtroBase $filtro
                    ) AS inicial_esc";

        case 'inicial_no_esc':
            return "SELECT 'INICIAL NO ESCOLARIZADA' AS titulo_fila,
                        SUM(V81+V129+V130) AS total_matricula,
                        SUM(V79+V129) AS mat_hombres,
                        SUM(V80+V130) AS mat_mujeres,
                        SUM(V126+V183+V184) AS total_docentes,
                        SUM(V124+V183) AS doc_hombres,
                        SUM(V125+V184) AS doc_mujeres,
                        COUNT(DISTINCT cv_cct) AS escuelas,
                        SUM(0) AS grupos
                    FROM (
                        SELECT cv_cct, c_nom_mun, control, V81,V79,V80,V126,V124,V125,
                               0 as V129, 0 as V130, 0 as V183, 0 as V184
                        FROM nonce_pano_$ini_ciclo.ini_comuni_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, 0,0,0,0,0,0,V129,V130,V183,V184
                        FROM nonce_pano_$ini_ciclo.ini_ne_$ini_ciclo 
                        WHERE $filtroBase $filtro
                    ) AS inicial_no_esc";

        case 'preescolar':
            return "SELECT 'PREESCOLAR' AS titulo_fila,
                        SUM(V177+V97+V478) AS total_matricula,
                        SUM(V165+V85+V466) AS mat_hombres,
                        SUM(V171+V91+V472) AS mat_mujeres,
                        SUM(V867+V868+V859+V860+V795+V803+V796+V804+V151+V513+V520+V527+V514+V521+V528) AS total_docentes,
                        SUM(V859+V868+V795+V803+V149+V513+V520+V527) AS doc_hombres,
                        SUM(V860+V868+V796+V804+V150+V514+V521+V528) AS doc_mujeres,
                        SUM(CASE WHEN es_ini_gral = 1 THEN 0 ELSE 1 END) AS escuelas,
                        SUM(V182+V479) AS grupos
                    FROM (
                        SELECT cv_cct, c_nom_mun, control, V177,V165,V171,V867,V868,V859,V860,V182,
                               0 as V97, 0 as V85, 0 as V91, 0 as V151, 0 as V149, 0 as V150,
                               0 as V795, 0 as V803, 0 as V796, 0 as V804,
                               0 as V478, 0 as V466, 0 as V472, 0 as V513, 0 as V520, 0 as V527,
                               0 as V514, 0 as V521, 0 as V528, 0 as V479, 0 as es_ini_gral
                        FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, V177,V165,V171,0,0,0,0,V182,
                               0,0,0,0,0,0,V795,V803,V796,V804,
                               0,0,0,0,0,0,0,0,0,0,0
                        FROM nonce_pano_$ini_ciclo.pree_ind_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, 0,0,0,0,0,0,0,0,V97,V85,V91,V151,V149,V150,0,0,0,0,
                               0,0,0,0,0,0,0,0,0,0,0
                        FROM nonce_pano_$ini_ciclo.pree_comuni_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
                               V478,V466,V472,V513,V520,V527,V514,V521,V528,V479,1 as es_ini_gral
                        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                        WHERE $filtroBase $filtro
                    ) AS preescolar";

        case 'primaria':
            return "SELECT 'PRIMARIA' AS titulo_fila,
                        SUM(V608+V610+V515) AS total_matricula,
                        SUM(V562+V573+V564+V575+V469+V480) AS mat_hombres,
                        SUM(V585+V596+V587+V598+V492+V503) AS mat_mujeres,
                        SUM(V1575+V1576+V1567+V1568+V1507+V1499+V1508+V1500+V583+V584) AS total_docentes,
                        SUM(V1575+V1567+V1507+V1499+V583) AS doc_hombres,
                        SUM(V1576+V1568+V1508+V1500+V584) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V616+V1052) AS grupos
                    FROM (
                        SELECT cv_cct, c_nom_mun, control, V608,V562,V573,V585,V596,V1575,V1576,V1567,V1568,V616,
                               0 as V610, 0 as V564, 0 as V575, 0 as V587, 0 as V598, 0 as V1507, 0 as V1499, 0 as V1508, 0 as V1500, 0 as V1052,
                               0 as V515, 0 as V469, 0 as V480, 0 as V492, 0 as V503, 0 as V585_com, 0 as V583, 0 as V584
                        FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, 0,0,0,0,0,0,0,0,0,0,V610,V564,V575,V587,V598,V1507,V1499,V1508,V1500,V1052,0,0,0,0,0,0,0,0
                        FROM nonce_pano_$ini_ciclo.prim_ind_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,V515,V469,V480,V492,V503,V585,V583,V584
                        FROM nonce_pano_$ini_ciclo.prim_comuni_$ini_ciclo 
                        WHERE $filtroBase $filtro
                    ) AS primaria";

        case 'secundaria':
            return "SELECT 'SECUNDARIA' AS titulo_fila,
                        SUM(V340+V257) AS total_matricula,
                        SUM(V306+V314+V223+V231) AS mat_hombres,
                        SUM(V323+V331+V240+V248) AS mat_mujeres,
                        SUM(V1401+V386) AS total_docentes,
                        SUM(V1297+V1303+V1307+V1309+V1311+V1313+V384) AS doc_hombres,
                        SUM(V1298+V1304+V1308+V1310+V1312+V1314+V385) AS doc_mujeres,
                        COUNT(cv_cct) AS escuelas,
                        SUM(V341) AS grupos
                    FROM (
                        SELECT cv_cct, c_nom_mun, control, V340,V306,V314,V323,V331,V1401,
                               V1297,V1303,V1307,V1309,V1311,V1313,V1298,V1304,V1308,V1310,V1312,V1314,V341,
                               0 as V257, 0 as V223, 0 as V231, 0 as V240, 0 as V248, 0 as V386, 0 as V384, 0 as V385
                        FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo 
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, c_nom_mun, control, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,V257,V223,V231,V240,V248,V386,V384,V385
                        FROM nonce_pano_$ini_ciclo.sec_comuni_$ini_ciclo 
                        WHERE $filtroBase $filtro
                    ) AS secundaria";

        case 'media_sup':
            return "SELECT 'MEDIA SUPERIOR' AS titulo_fila,
                        (SELECT SUM(V397+V472) FROM (
                            SELECT V397, 0 as V472 FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                            UNION ALL
                            SELECT 0, V472 FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                        ) AS matricula_ms) AS total_matricula,
                        (SELECT SUM(V395+V470) FROM (
                            SELECT V395, 0 as V470 FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                            UNION ALL
                            SELECT 0, V470 FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                        ) AS mat_h_ms) AS mat_hombres,
                        (SELECT SUM(V396+V471) FROM (
                            SELECT V396, 0 as V471 FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                            UNION ALL
                            SELECT 0, V471 FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                        ) AS mat_m_ms) AS mat_mujeres,
                        SUM(V106+V101) AS total_docentes,
                        SUM(V104+V99) AS doc_hombres,
                        SUM(V105+V100) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        (SELECT SUM(V401+V476) FROM (
                            SELECT V401, 0 as V476 FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                            UNION ALL
                            SELECT 0, V476 FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo 
                            WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro
                        ) AS grupos_ms) AS grupos
                    FROM nonce_pano_$ini_ciclo.ms_plantel_$ini_ciclo 
                    WHERE cv_motivo = '0' $filtro";

        case 'superior':
            return "SELECT 'SUPERIOR' AS titulo_fila,
                        (SELECT SUM(V177+V142) FROM (
                            SELECT V177, 0 as V142 FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                            WHERE cv_motivo = '0' $filtro
                            UNION ALL
                            SELECT 0, V142 FROM nonce_pano_$ini_ciclo.sup_posgrado_$ini_ciclo 
                            WHERE cv_motivo = '0' $filtro
                        ) AS matricula_sup) AS total_matricula,
                        (SELECT SUM(V175+V140) FROM (
                            SELECT V175, 0 as V140 FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                            WHERE cv_motivo = '0' $filtro
                            UNION ALL
                            SELECT 0, V140 FROM nonce_pano_$ini_ciclo.sup_posgrado_$ini_ciclo 
                            WHERE cv_motivo = '0' $filtro
                        ) AS mat_h_sup) AS mat_hombres,
                        (SELECT SUM(V176+V141) FROM (
                            SELECT V176, 0 as V141 FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo 
                            WHERE cv_motivo = '0' $filtro
                            UNION ALL
                            SELECT 0, V141 FROM nonce_pano_$ini_ciclo.sup_posgrado_$ini_ciclo 
                            WHERE cv_motivo = '0' $filtro
                        ) AS mat_m_sup) AS mat_mujeres,
                        SUM(V83) AS total_docentes,
                        SUM(V81) AS doc_hombres,
                        SUM(V82) AS doc_mujeres,
                        COUNT(cct_ins_pla) AS escuelas,
                        SUM(0) AS grupos
                    FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo 
                    WHERE cv_motivo = '0' $filtro";

        case 'superior_muni_unidades':
            // Este caso requiere procesamiento especial con acum_unidades()
            // No se puede resolver con una consulta SQL directa
            return 'SPECIAL_PROCESSING_REQUIRED';

        case 'especial_tot':
            // PRUEBA TEMPORAL: Forzar valor para verificar cache
            return "SELECT 'ESPECIAL (CAM)' AS titulo_fila,
                        999 AS total_matricula,
                        500 AS mat_hombres,
                        499 AS mat_mujeres,
                        50 AS total_docentes,
                        25 AS doc_hombres,
                        25 AS doc_mujeres,
                        10 AS escuelas,
                        100 AS grupos";

        default:
            return false;
    }
}

// Variables globales como en bolsillo
$sin_filtro_extra = " ";
$filtro_pub = " AND control<>'PRIVADO' ";
$filtro_priv = " AND control='PRIVADO' ";

/**
 * Función para aplicar ajustes de unidades al nivel superior
 * Replica la lógica de acum_unidades de bolsillo_unidades.php
 * 
 * @param resource $link Conexión a la base de datos
 * @param string $ini_ciclo Ciclo escolar
 * @param string $filtro_pub Filtro para escuelas públicas
 * @param string $filtro_priv Filtro para escuelas privadas
 * @param string $filtro_extra Filtro municipal
 * @param array $arr_nivel1 Datos del nivel superior base
 * @param array $arr_nivel2 Datos de unidades
 * @return array Datos ajustados
 */
function acum_unidades_superior($link, $ini_ciclo, $filtro_pub, $filtro_priv, $filtro_extra, $arr_nivel1, $arr_nivel2)
{
    // Extraer el número de municipio del filtro
    $datos_filtro_s1 = explode('=', $filtro_extra);
    if (count($datos_filtro_s1) < 2) {
        return $arr_nivel1; // Si no hay filtro municipal, devolver datos base
    }

    $datos_filtro_s2 = explode("'", $datos_filtro_s1[1]);
    if (count($datos_filtro_s2) < 2) {
        return $arr_nivel1; // Si no se puede extraer el municipio, devolver datos base
    }

    $municipio = $datos_filtro_s2[1];

    if (strcmp($municipio, '14') == 0) {
        // Para Querétaro (municipio 14): RESTAR unidades estatales para evitar doble conteo
        // Obtener unidades estatales (sin filtro municipal)
        $consulta_unidades_estatales = str_consulta_segura('unidades_sup', $ini_ciclo, '');
        if (!$consulta_unidades_estatales) {
            return $arr_nivel1;
        }

        $rs_unidades = pg_query($link, $consulta_unidades_estatales);
        if (!$rs_unidades || pg_num_rows($rs_unidades) == 0) {
            return $arr_nivel1;
        }

        $unidades_estatales = pg_fetch_assoc($rs_unidades);

        return [
            "titulo_fila" => "SUPERIOR",
            "tot_mat" => $arr_nivel1['tot_mat'] - $unidades_estatales['total_matricula'],
            "tot_mat_pub" => $arr_nivel1['tot_mat_pub'] - $unidades_estatales['total_matricula'],
            "tot_mat_priv" => $arr_nivel1['tot_mat_priv'],
            "mat_h" => $arr_nivel1['mat_h'] - $unidades_estatales['mat_hombres'],
            "mat_h_pub" => $arr_nivel1['mat_h_pub'] - $unidades_estatales['mat_hombres'],
            "mat_h_priv" => $arr_nivel1['mat_h_priv'],
            "mat_m" => $arr_nivel1['mat_m'] - $unidades_estatales['mat_mujeres'],
            "mat_m_pub" => $arr_nivel1['mat_m_pub'] - $unidades_estatales['mat_mujeres'],
            "mat_m_priv" => $arr_nivel1['mat_m_priv'],
            "tot_doc" => $arr_nivel1['tot_doc'] - $unidades_estatales['total_docentes'],
            "tot_doc_pub" => $arr_nivel1['tot_doc_pub'] - $unidades_estatales['total_docentes'],
            "tot_doc_priv" => $arr_nivel1['tot_doc_priv'],
            "doc_h" => $arr_nivel1['doc_h'] - $unidades_estatales['doc_hombres'],
            "doc_h_pub" => $arr_nivel1['doc_h_pub'] - $unidades_estatales['doc_hombres'],
            "doc_h_priv" => $arr_nivel1['doc_h_priv'],
            "doc_m" => $arr_nivel1['doc_m'] - $unidades_estatales['doc_mujeres'],
            "doc_m_pub" => $arr_nivel1['doc_m_pub'] - $unidades_estatales['doc_mujeres'],
            "doc_m_priv" => $arr_nivel1['doc_m_priv'],
            "tot_esc" => $arr_nivel1['tot_esc'], // Las escuelas no se restan
            "tot_esc_pub" => $arr_nivel1['tot_esc_pub'],
            "tot_esc_priv" => $arr_nivel1['tot_esc_priv'],
            "tot_grp" => $arr_nivel1['tot_grp'] - $unidades_estatales['grupos'],
            "tot_grp_pub" => $arr_nivel1['tot_grp_pub'] - $unidades_estatales['grupos'],
            "tot_grp_priv" => $arr_nivel1['tot_grp_priv']
        ];
    } else {
        // Para otros municipios: SUMAR unidades municipales
        return [
            "titulo_fila" => "SUPERIOR",
            "tot_mat" => $arr_nivel1['tot_mat'] + $arr_nivel2['total_matricula'],
            "tot_mat_pub" => $arr_nivel1['tot_mat_pub'] + $arr_nivel2['total_matricula'],
            "tot_mat_priv" => $arr_nivel1['tot_mat_priv'],
            "mat_h" => $arr_nivel1['mat_h'] + $arr_nivel2['mat_hombres'],
            "mat_h_pub" => $arr_nivel1['mat_h_pub'] + $arr_nivel2['mat_hombres'],
            "mat_h_priv" => $arr_nivel1['mat_h_priv'],
            "mat_m" => $arr_nivel1['mat_m'] + $arr_nivel2['mat_mujeres'],
            "mat_m_pub" => $arr_nivel1['mat_m_pub'] + $arr_nivel2['mat_mujeres'],
            "mat_m_priv" => $arr_nivel1['mat_m_priv'],
            "tot_doc" => $arr_nivel1['tot_doc'] + $arr_nivel2['total_docentes'],
            "tot_doc_pub" => $arr_nivel1['tot_doc_pub'] + $arr_nivel2['total_docentes'],
            "tot_doc_priv" => $arr_nivel1['tot_doc_priv'],
            "doc_h" => $arr_nivel1['doc_h'] + $arr_nivel2['doc_hombres'],
            "doc_h_pub" => $arr_nivel1['doc_h_pub'] + $arr_nivel2['doc_hombres'],
            "doc_h_priv" => $arr_nivel1['doc_h_priv'],
            "doc_m" => $arr_nivel1['doc_m'] + $arr_nivel2['doc_mujeres'],
            "doc_m_pub" => $arr_nivel1['doc_m_pub'] + $arr_nivel2['doc_mujeres'],
            "doc_m_priv" => $arr_nivel1['doc_m_priv'],
            "tot_esc" => $arr_nivel1['tot_esc'] + $arr_nivel2['escuelas'],
            "tot_esc_pub" => $arr_nivel1['tot_esc_pub'] + $arr_nivel2['escuelas'],
            "tot_esc_priv" => $arr_nivel1['tot_esc_priv'],
            "tot_grp" => $arr_nivel1['tot_grp'] + $arr_nivel2['grupos'],
            "tot_grp_pub" => $arr_nivel1['tot_grp_pub'] + $arr_nivel2['grupos'],
            "tot_grp_priv" => $arr_nivel1['tot_grp_priv']
        ];
    }
}

/**
 * Función para aplicar ajustes de unidades al nivel superior
 * Replica la lógica de acum_unidades de bolsillo_unidades.php
 * 
 * @param resource $link Conexión a la base de datos
 * @param string $ini_ciclo Ciclo escolar
 * @param string $filtro_pub Filtro para escuelas públicas
 * @param string $filtro_priv Filtro para escuelas privadas
 * @param string $filtro_extra Filtro municipal
 * @param string $titulo_fila Título de la fila
 * @param array $arr_nivel1 Datos del nivel superior base
 * @param array $arr_nivel2 Datos de unidades
 * @return array Datos ajustados
 */
function acum_unidades($link, $ini_ciclo, $filtro_pub, $filtro_priv, $filtro_extra, $titulo_fila, $arr_nivel1, $arr_nivel2)
{
    // Extraer el número de municipio del filtro
    $datos_filtro_s1 = explode('=', $filtro_extra);
    if (count($datos_filtro_s1) < 2) {
        return $arr_nivel1; // Si no hay filtro válido, retornar datos originales
    }

    $datos_filtro_s2 = explode('\'', $datos_filtro_s1[1]);
    if (count($datos_filtro_s2) < 2) {
        return $arr_nivel1; // Si no hay municipio válido, retornar datos originales
    }

    $municipio = $datos_filtro_s2[1];

    if (strcmp($municipio, '14') == 0) {
        // Para Querétaro (municipio 14): RESTAR unidades estatales
        $arr_nivel2_estatal = rs_consulta_segura($link, 'unidades_sup', $ini_ciclo, " ");

        if (!$arr_nivel2_estatal) {
            $arr_nivel2_estatal = [
                'tot_mat' => 0,
                'tot_mat_pub' => 0,
                'tot_mat_priv' => 0,
                'mat_h' => 0,
                'mat_h_pub' => 0,
                'mat_h_priv' => 0,
                'mat_m' => 0,
                'mat_m_pub' => 0,
                'mat_m_priv' => 0,
                'tot_doc' => 0,
                'tot_doc_pub' => 0,
                'tot_doc_priv' => 0,
                'doc_h' => 0,
                'doc_h_pub' => 0,
                'doc_h_priv' => 0,
                'doc_m' => 0,
                'doc_m_pub' => 0,
                'doc_m_priv' => 0,
                'tot_esc' => 0,
                'tot_esc_pub' => 0,
                'tot_esc_priv' => 0,
                'tot_grp' => 0,
                'tot_grp_pub' => 0,
                'tot_grp_priv' => 0
            ];
        }

        $acum_niveles = [
            "titulo_fila" => $titulo_fila,
            "tot_mat" => $arr_nivel1['tot_mat'] - $arr_nivel2_estatal['tot_mat'],
            "tot_mat_pub" => $arr_nivel1['tot_mat_pub'] - $arr_nivel2_estatal['tot_mat_pub'],
            "tot_mat_priv" => $arr_nivel1['tot_mat_priv'] - $arr_nivel2_estatal['tot_mat_priv'],
            "mat_h" => $arr_nivel1['mat_h'] - $arr_nivel2_estatal['mat_h'],
            "mat_h_pub" => $arr_nivel1['mat_h_pub'] - $arr_nivel2_estatal['mat_h_pub'],
            "mat_h_priv" => $arr_nivel1['mat_h_priv'] - $arr_nivel2_estatal['mat_h_priv'],
            "mat_m" => $arr_nivel1['mat_m'] - $arr_nivel2_estatal['mat_m'],
            "mat_m_pub" => $arr_nivel1['mat_m_pub'] - $arr_nivel2_estatal['mat_m_pub'],
            "mat_m_priv" => $arr_nivel1['mat_m_priv'] - $arr_nivel2_estatal['mat_m_priv'],
            "tot_doc" => $arr_nivel1['tot_doc'] - $arr_nivel2_estatal['tot_doc'],
            "tot_doc_pub" => $arr_nivel1['tot_doc_pub'] - $arr_nivel2_estatal['tot_doc_pub'],
            "tot_doc_priv" => $arr_nivel1['tot_doc_priv'] - $arr_nivel2_estatal['tot_doc_priv'],
            "doc_h" => $arr_nivel1['doc_h'] - $arr_nivel2_estatal['doc_h'],
            "doc_h_pub" => $arr_nivel1['doc_h_pub'] - $arr_nivel2_estatal['doc_h_pub'],
            "doc_h_priv" => $arr_nivel1['doc_h_priv'] - $arr_nivel2_estatal['doc_h_priv'],
            "doc_m" => $arr_nivel1['doc_m'] - $arr_nivel2_estatal['doc_m'],
            "doc_m_pub" => $arr_nivel1['doc_m_pub'] - $arr_nivel2_estatal['doc_m_pub'],
            "doc_m_priv" => $arr_nivel1['doc_m_priv'] - $arr_nivel2_estatal['doc_m_priv'],
            "tot_esc" => $arr_nivel1['tot_esc'], // Las escuelas no se restan
            "tot_esc_pub" => $arr_nivel1['tot_esc_pub'],
            "tot_esc_priv" => $arr_nivel1['tot_esc_priv'],
            "tot_grp" => $arr_nivel1['tot_grp'] - $arr_nivel2_estatal['tot_grp'],
            "tot_grp_pub" => $arr_nivel1['tot_grp_pub'] - $arr_nivel2_estatal['tot_grp_pub'],
            "tot_grp_priv" => $arr_nivel1['tot_grp_priv'] - $arr_nivel2_estatal['tot_grp_priv']
        ];
    } else {
        // Para otros municipios: SUMAR unidades municipales
        $acum_niveles = [
            "titulo_fila" => $titulo_fila,
            "tot_mat" => $arr_nivel1['tot_mat'] + $arr_nivel2['tot_mat'],
            "tot_mat_pub" => $arr_nivel1['tot_mat_pub'] + $arr_nivel2['tot_mat_pub'],
            "tot_mat_priv" => $arr_nivel1['tot_mat_priv'] + $arr_nivel2['tot_mat_priv'],
            "mat_h" => $arr_nivel1['mat_h'] + $arr_nivel2['mat_h'],
            "mat_h_pub" => $arr_nivel1['mat_h_pub'] + $arr_nivel2['mat_h_pub'],
            "mat_h_priv" => $arr_nivel1['mat_h_priv'] + $arr_nivel2['mat_h_priv'],
            "mat_m" => $arr_nivel1['mat_m'] + $arr_nivel2['mat_m'],
            "mat_m_pub" => $arr_nivel1['mat_m_pub'] + $arr_nivel2['mat_m_pub'],
            "mat_m_priv" => $arr_nivel1['mat_m_priv'] + $arr_nivel2['mat_m_priv'],
            "tot_doc" => $arr_nivel1['tot_doc'] + $arr_nivel2['tot_doc'],
            "tot_doc_pub" => $arr_nivel1['tot_doc_pub'] + $arr_nivel2['tot_doc_pub'],
            "tot_doc_priv" => $arr_nivel1['tot_doc_priv'] + $arr_nivel2['tot_doc_priv'],
            "doc_h" => $arr_nivel1['doc_h'] + $arr_nivel2['doc_h'],
            "doc_h_pub" => $arr_nivel1['doc_h_pub'] + $arr_nivel2['doc_h_pub'],
            "doc_h_priv" => $arr_nivel1['doc_h_priv'] + $arr_nivel2['doc_h_priv'],
            "doc_m" => $arr_nivel1['doc_m'] + $arr_nivel2['doc_m'],
            "doc_m_pub" => $arr_nivel1['doc_m_pub'] + $arr_nivel2['doc_m_pub'],
            "doc_m_priv" => $arr_nivel1['doc_m_priv'] + $arr_nivel2['doc_m_priv'],
            "tot_esc" => $arr_nivel1['tot_esc'] + $arr_nivel2['tot_esc'],
            "tot_esc_pub" => $arr_nivel1['tot_esc_pub'] + $arr_nivel2['tot_esc_pub'],
            "tot_esc_priv" => $arr_nivel1['tot_esc_priv'] + $arr_nivel2['tot_esc_priv'],
            "tot_grp" => $arr_nivel1['tot_grp'] + $arr_nivel2['tot_grp'],
            "tot_grp_pub" => $arr_nivel1['tot_grp_pub'] + $arr_nivel2['tot_grp_pub'],
            "tot_grp_priv" => $arr_nivel1['tot_grp_priv'] + $arr_nivel2['tot_grp_priv']
        ];
    }

    return $acum_niveles;
}

/**
 * Calcula superior_muni_unidades aplicando la lógica de acum_unidades
 * Replica exactamente el comportamiento de bolsillo_unidades.php
 * 
 * @param resource $link Conexión a la base de datos
 * @param string $ini_ciclo Ciclo escolar
 * @param string $filtro Filtro municipal
 * @return array|false Datos calculados o false en caso de error
 */
function calcular_superior_muni_unidades($link, $ini_ciclo, $filtro)
{
    global $filtro_pub, $filtro_priv;

    try {
        // 1. Obtener datos base del superior (equivalente a total_sedeq_sup)
        $datos_superior_base = rs_consulta_segura($link, 'superior', $ini_ciclo, $filtro);
        if (!$datos_superior_base) {
            return false;
        }

        // 2. Obtener datos de unidades
        $datos_unidades = rs_consulta_segura($link, 'unidades_sup', $ini_ciclo, $filtro);
        if (!$datos_unidades) {
            // Si no hay datos de unidades, inicializar array vacío
            $datos_unidades = [
                'tot_mat' => 0,
                'tot_mat_pub' => 0,
                'tot_mat_priv' => 0,
                'mat_h' => 0,
                'mat_h_pub' => 0,
                'mat_h_priv' => 0,
                'mat_m' => 0,
                'mat_m_pub' => 0,
                'mat_m_priv' => 0,
                'tot_doc' => 0,
                'tot_doc_pub' => 0,
                'tot_doc_priv' => 0,
                'doc_h' => 0,
                'doc_h_pub' => 0,
                'doc_h_priv' => 0,
                'doc_m' => 0,
                'doc_m_pub' => 0,
                'doc_m_priv' => 0,
                'tot_esc' => 0,
                'tot_esc_pub' => 0,
                'tot_esc_priv' => 0,
                'tot_grp' => 0,
                'tot_grp_pub' => 0,
                'tot_grp_priv' => 0
            ];
        }

        // 3. Verificar si hay datos válidos de unidades (lógica de bolsillo)
        $cantidad_vacios = 0;
        if (empty($datos_unidades['tot_mat']) || $datos_unidades['tot_mat'] == 0)
            $cantidad_vacios++;
        if (empty($datos_unidades['tot_doc']) || $datos_unidades['tot_doc'] == 0)
            $cantidad_vacios++;
        if (empty($datos_unidades['tot_esc']) || $datos_unidades['tot_esc'] == 0)
            $cantidad_vacios++;
        if (empty($datos_unidades['tot_grp']) || $datos_unidades['tot_grp'] == 0)
            $cantidad_vacios++;

        // Si hay más de 2 campos vacíos, usar datos cero
        if ($cantidad_vacios > 2) {
            $datos_unidades = [
                'tot_mat' => 0,
                'tot_mat_pub' => 0,
                'tot_mat_priv' => 0,
                'mat_h' => 0,
                'mat_h_pub' => 0,
                'mat_h_priv' => 0,
                'mat_m' => 0,
                'mat_m_pub' => 0,
                'mat_m_priv' => 0,
                'tot_doc' => 0,
                'tot_doc_pub' => 0,
                'tot_doc_priv' => 0,
                'doc_h' => 0,
                'doc_h_pub' => 0,
                'doc_h_priv' => 0,
                'doc_m' => 0,
                'doc_m_pub' => 0,
                'doc_m_priv' => 0,
                'tot_esc' => 0,
                'tot_esc_pub' => 0,
                'tot_esc_priv' => 0,
                'tot_grp' => 0,
                'tot_grp_pub' => 0,
                'tot_grp_priv' => 0
            ];
        }

        // 4. Aplicar la función acum_unidades
        $resultado = acum_unidades(
            $link,
            $ini_ciclo,
            $filtro_pub,
            $filtro_priv,
            $filtro,
            "EDUCACIÓN SUPERIOR UNIDADES",
            $datos_superior_base,
            $datos_unidades
        );

        return $resultado;

    } catch (Exception $e) {
        error_log("Error en calcular_superior_muni_unidades: " . $e->getMessage());
        return false;
    }
}

/**
 * Función rs_consulta adaptada de bolsillo
 */
function rs_consulta_segura($link, $str_consulta, $ini_ciclo, $filtro)
{
    // Caso especial para superior_muni_unidades
    if ($str_consulta === 'superior_muni_unidades') {
        return calcular_superior_muni_unidades($link, $ini_ciclo, $filtro);
    }

    $consulta = str_consulta_segura($str_consulta, $ini_ciclo, $filtro);

    if (!$consulta) {
        return false;
    }

    // Si es el indicador de procesamiento especial, retornar false
    if ($consulta === 'SPECIAL_PROCESSING_REQUIRED') {
        return false;
    }

    // DEBUG TEMPORAL: Log para especial_tot
    if ($str_consulta === 'especial_tot') {
        error_log("DEBUG ESPECIAL_TOT: " . substr($consulta, 0, 200) . "...");
    }

    $rs_nivel = pg_query($link, $consulta);
    if (!$rs_nivel) {
        error_log('Error en consulta ' . $str_consulta . ': ' . pg_last_error($link));
        return false;
    }

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

        // Aplicar ajuste de unidades para el nivel superior
        if ($str_consulta === 'superior' && $filtro != '') {
            // Obtener datos de unidades para el municipio específico
            $consulta_unidades = str_consulta_segura('unidades_sup', $ini_ciclo, $filtro);
            if ($consulta_unidades) {
                $rs_unidades = pg_query($link, $consulta_unidades);
                if ($rs_unidades && pg_num_rows($rs_unidades) > 0) {
                    $row_unidades = pg_fetch_assoc($rs_unidades);

                    // Crear arreglos para la función acum_unidades_superior
                    $arr_superior_base = [
                        "titulo_fila" => $titulo_fila,
                        "tot_mat" => $tot_mat_nivel,
                        "tot_mat_pub" => $tot_mat_nivel, // Asumiendo que todo es público para matricula
                        "tot_mat_priv" => 0,
                        "mat_h" => $mat_h_nivel,
                        "mat_h_pub" => $mat_h_nivel,
                        "mat_h_priv" => 0,
                        "mat_m" => $mat_m_nivel,
                        "mat_m_pub" => $mat_m_nivel,
                        "mat_m_priv" => 0,
                        "tot_doc" => $tot_doc_nivel,
                        "tot_doc_pub" => $tot_doc_nivel,
                        "tot_doc_priv" => 0,
                        "doc_h" => $doc_h_nivel,
                        "doc_h_pub" => $doc_h_nivel,
                        "doc_h_priv" => 0,
                        "doc_m" => $doc_m_nivel,
                        "doc_m_pub" => $doc_m_nivel,
                        "doc_m_priv" => 0,
                        "tot_esc" => $tot_esc_nivel,
                        "tot_esc_pub" => $tot_esc_nivel,
                        "tot_esc_priv" => 0,
                        "tot_grp" => $tot_grp_nivel,
                        "tot_grp_pub" => $tot_grp_nivel,
                        "tot_grp_priv" => 0
                    ];

                    // Aplicar ajuste de unidades
                    global $filtro_pub, $filtro_priv;
                    $datos_ajustados = acum_unidades_superior($link, $ini_ciclo, $filtro_pub, $filtro_priv, $filtro, $arr_superior_base, $row_unidades);

                    // Actualizar los valores con los datos ajustados
                    $tot_mat_nivel = $datos_ajustados['tot_mat'];
                    $mat_h_nivel = $datos_ajustados['mat_h'];
                    $mat_m_nivel = $datos_ajustados['mat_m'];
                    $tot_doc_nivel = $datos_ajustados['tot_doc'];
                    $doc_h_nivel = $datos_ajustados['doc_h'];
                    $doc_m_nivel = $datos_ajustados['doc_m'];
                    $tot_esc_nivel = $datos_ajustados['tot_esc'];
                    $tot_grp_nivel = $datos_ajustados['tot_grp'];

                    pg_free_result($rs_unidades);
                }
            }
        }

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

        pg_free_result($rs_nivel);
        return $nivel_detalle;
    }

    pg_free_result($rs_nivel);
    return false;
}

/**
 * Función subnivel adaptada de bolsillo - obtiene datos total, públicos y privados
 * 
 * @param resource $link Conexión a la base de datos
 * @param string $titulo_fila Título descriptivo
 * @param string $ini_ciclo Ciclo escolar
 * @param string $str_consulta Tipo de consulta
 * @param string $filtro_extra Filtro adicional (municipio)
 * @return array Datos consolidados con desglose público/privado
 */
function subnivel_con_control($link, $titulo_fila, $ini_ciclo, $str_consulta, $filtro_extra)
{
    global $filtro_pub, $filtro_priv;

    // 3 consultas: total, público, privado
    $subnivel_tot = rs_consulta_segura($link, $str_consulta, $ini_ciclo, $filtro_extra);
    $subnivel_pub = rs_consulta_segura($link, $str_consulta, $ini_ciclo, $filtro_pub . " " . $filtro_extra);
    $subnivel_priv = rs_consulta_segura($link, $str_consulta, $ini_ciclo, $filtro_priv . " " . $filtro_extra);

    // Si no hay datos, devolver estructura vacía
    if (!$subnivel_tot) {
        $subnivel_tot = subnivel_cero();
    }
    if (!$subnivel_pub) {
        $subnivel_pub = subnivel_cero();
    }
    if (!$subnivel_priv) {
        $subnivel_priv = subnivel_cero();
    }

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

    return $total_subnivel;
}

/**
 * Función subnivel_cero adaptada de bolsillo - devuelve estructura vacía
 */
function subnivel_cero()
{
    $total_subnivel_cero = [
        "titulo_fila" => "SIN DATOS",
        "tot_mat" => 0,
        "mat_h" => 0,
        "mat_m" => 0,
        "tot_doc" => 0,
        "doc_h" => 0,
        "doc_m" => 0,
        "tot_esc" => 0,
        "tot_grp" => 0
    ];

    return $total_subnivel_cero;
}

/**
 * Función que obtiene datos con desglose público/privado para TODOS los niveles educativos
 * @param string $municipio Nombre del municipio
 * @param string $ini_ciclo Ciclo escolar
 * @return array Datos consolidados con desglose público/privado por nivel
 */
function obtenerDatosPublicoPrivado($municipio = 'CORREGIDORA', $ini_ciclo = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ini_ciclo === null) {
        $ini_ciclo = obtenerCicloEscolarActual();
    }
    global $filtro_pub, $filtro_priv;

    try {
        $link = ConectarsePrueba();
        if (!$link) {
            throw new Exception("No se pudo conectar a la base de datos");
        }

        // Obtener número de municipio para el filtro
        $num_munic = nombre_a_numero_municipio($municipio);
        $filtro_mun = " AND cv_mun='" . $num_munic . "' ";

        $niveles_educativos = [
            'inicial_esc' => 'INICIAL ESCOLARIZADA',
            'inicial_no_esc' => 'INICIAL NO ESCOLARIZADA',
            'preescolar' => 'PREESCOLAR',
            'primaria' => 'PRIMARIA',
            'secundaria' => 'SECUNDARIA',
            'media_sup' => 'MEDIA SUPERIOR',
            'superior' => 'SUPERIOR',
            'especial_tot' => 'ESPECIAL TOTAL'
        ];
        $datos_consolidados = [];

        foreach ($niveles_educativos as $consulta => $nombre_nivel) {
            $datos_nivel = subnivel_con_control($link, $nombre_nivel, $ini_ciclo, $consulta, $filtro_mun);
            if ($datos_nivel) {
                $datos_consolidados[$consulta] = $datos_nivel;
            }
        }

        pg_close($link);
        return $datos_consolidados;

    } catch (Exception $e) {
        error_log("Error obteniendo datos público/privado para $municipio: " . $e->getMessage());
        return [];
    }
}

// =============================================================================
// MAPEO Y UTILIDADES DE MUNICIPIOS
// =============================================================================
// MAPEO Y UTILIDADES DE MUNICIPIOS
// =============================================================================

/**
 * Convierte nombre de municipio a número para filtro cv_mun
 */
function nombre_a_numero_municipio($nombre_municipio)
{
    $municipios = [
        "AMEALCO DE BONFIL" => "1",
        "PINAL DE AMOLES" => "2",
        "ARROYO SECO" => "3",
        "CADEREYTA DE MONTES" => "4",
        "COLÓN" => "5",
        "CORREGIDORA" => "6",
        "EZEQUIEL MONTES" => "7",
        "HUIMILPAN" => "8",
        "JALPAN DE SERRA" => "9",
        "LANDA DE MATAMOROS" => "10",
        "EL MARQUÉS" => "11",
        "PEDRO ESCOBEDO" => "12",
        "PEÑAMILLER" => "13",
        "QUERÉTARO" => "14",
        "SAN JOAQUÍN" => "15",
        "SAN JUAN DEL RÍO" => "16",
        "TEQUISQUIAPAN" => "17",
        "TOLIMÁN" => "18"
    ];

    $nombre_normalizado = strtoupper(trim($nombre_municipio));
    return isset($municipios[$nombre_normalizado]) ? $municipios[$nombre_normalizado] : "6"; // Default Corregidora
}

/**
 * Obtiene datos educativos consolidados replicando la lógica de bolsillo
 * 
 * @param string $municipio Municipio a consultar
 * @param string $ciclo_escolar Ciclo escolar
 * @return array Datos consolidados por nivel educativo
 */
function obtenerDatosEducativosCompletos($municipio = 'CORREGIDORA', $ciclo_escolar = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ciclo_escolar === null) {
        $ciclo_escolar = obtenerCicloEscolarActual();
    }
    $link = ConectarsePrueba();
    if (!$link) {
        return [
            'error' => 'No se pudo conectar a la base de datos',
            'municipio' => $municipio,
            'datos' => []
        ];
    }

    $municipio = normalizarNombreMunicipio($municipio);

    try {
        $datos_consolidados = [];

        // EDUCACIÓN INICIAL
        $inicial_general = rs_consulta_segura($link, 'gral_ini', $ciclo_escolar, $municipio);
        if ($inicial_general) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Inicial',
                'subnivel' => 'General',
                'tipo_educativo' => 'Inicial General',
                'total_escuelas' => $inicial_general['tot_esc'],
                'total_alumnos' => $inicial_general['tot_mat'],
                'alumnos_hombres' => $inicial_general['mat_h'],
                'alumnos_mujeres' => $inicial_general['mat_m'],
                'total_docentes' => $inicial_general['tot_doc'],
                'docentes_hombres' => $inicial_general['doc_h'],
                'docentes_mujeres' => $inicial_general['doc_m'],
                'total_grupos' => $inicial_general['tot_grp']
            ];
        }

        $inicial_indigena = rs_consulta_segura($link, 'ind_ini', $ciclo_escolar, $municipio);
        if ($inicial_indigena) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Inicial',
                'subnivel' => 'Indígena',
                'tipo_educativo' => 'Inicial Indígena',
                'total_escuelas' => $inicial_indigena['tot_esc'],
                'total_alumnos' => $inicial_indigena['tot_mat'],
                'alumnos_hombres' => $inicial_indigena['mat_h'],
                'alumnos_mujeres' => $inicial_indigena['mat_m'],
                'total_docentes' => $inicial_indigena['tot_doc'],
                'docentes_hombres' => $inicial_indigena['doc_h'],
                'docentes_mujeres' => $inicial_indigena['doc_m'],
                'total_grupos' => $inicial_indigena['tot_grp']
            ];
        }

        $inicial_comunitario = rs_consulta_segura($link, 'comuni_ini', $ciclo_escolar, $municipio);
        if ($inicial_comunitario) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Inicial',
                'subnivel' => 'Comunitario',
                'tipo_educativo' => 'Inicial Comunitario',
                'total_escuelas' => $inicial_comunitario['tot_esc'],
                'total_alumnos' => $inicial_comunitario['tot_mat'],
                'alumnos_hombres' => $inicial_comunitario['mat_h'],
                'alumnos_mujeres' => $inicial_comunitario['mat_m'],
                'total_docentes' => $inicial_comunitario['tot_doc'],
                'docentes_hombres' => $inicial_comunitario['doc_h'],
                'docentes_mujeres' => $inicial_comunitario['doc_m'],
                'total_grupos' => $inicial_comunitario['tot_grp']
            ];
        }

        $inicial_no_escolar = rs_consulta_segura($link, 'ne_ini', $ciclo_escolar, $municipio);
        if ($inicial_no_escolar) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Inicial',
                'subnivel' => 'No Escolarizada',
                'tipo_educativo' => 'Inicial No Escolarizada',
                'total_escuelas' => $inicial_no_escolar['tot_esc'],
                'total_alumnos' => $inicial_no_escolar['tot_mat'],
                'alumnos_hombres' => $inicial_no_escolar['mat_h'],
                'alumnos_mujeres' => $inicial_no_escolar['mat_m'],
                'total_docentes' => $inicial_no_escolar['tot_doc'],
                'docentes_hombres' => $inicial_no_escolar['doc_h'],
                'docentes_mujeres' => $inicial_no_escolar['doc_m'],
                'total_grupos' => $inicial_no_escolar['tot_grp']
            ];
        }

        // EDUCACIÓN PREESCOLAR
        $preescolar_general = rs_consulta_segura($link, 'gral_pree', $ciclo_escolar, $municipio);
        if ($preescolar_general) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Preescolar',
                'subnivel' => 'General',
                'tipo_educativo' => 'Preescolar General',
                'total_escuelas' => $preescolar_general['tot_esc'],
                'total_alumnos' => $preescolar_general['tot_mat'],
                'alumnos_hombres' => $preescolar_general['mat_h'],
                'alumnos_mujeres' => $preescolar_general['mat_m'],
                'total_docentes' => $preescolar_general['tot_doc'],
                'docentes_hombres' => $preescolar_general['doc_h'],
                'docentes_mujeres' => $preescolar_general['doc_m'],
                'total_grupos' => $preescolar_general['tot_grp']
            ];
        }

        $preescolar_indigena = rs_consulta_segura($link, 'ind_pree', $ciclo_escolar, $municipio);
        if ($preescolar_indigena) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Preescolar',
                'subnivel' => 'Indígena',
                'tipo_educativo' => 'Preescolar Indígena',
                'total_escuelas' => $preescolar_indigena['tot_esc'],
                'total_alumnos' => $preescolar_indigena['tot_mat'],
                'alumnos_hombres' => $preescolar_indigena['mat_h'],
                'alumnos_mujeres' => $preescolar_indigena['mat_m'],
                'total_docentes' => $preescolar_indigena['tot_doc'],
                'docentes_hombres' => $preescolar_indigena['doc_h'],
                'docentes_mujeres' => $preescolar_indigena['doc_m'],
                'total_grupos' => $preescolar_indigena['tot_grp']
            ];
        }

        $preescolar_comunitario = rs_consulta_segura($link, 'comuni_pree', $ciclo_escolar, $municipio);
        if ($preescolar_comunitario) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Preescolar',
                'subnivel' => 'Comunitario',
                'tipo_educativo' => 'Preescolar Comunitario',
                'total_escuelas' => $preescolar_comunitario['tot_esc'],
                'total_alumnos' => $preescolar_comunitario['tot_mat'],
                'alumnos_hombres' => $preescolar_comunitario['mat_h'],
                'alumnos_mujeres' => $preescolar_comunitario['mat_m'],
                'total_docentes' => $preescolar_comunitario['tot_doc'],
                'docentes_hombres' => $preescolar_comunitario['doc_h'],
                'docentes_mujeres' => $preescolar_comunitario['doc_m'],
                'total_grupos' => $preescolar_comunitario['tot_grp']
            ];
        }

        // EDUCACIÓN PRIMARIA
        $primaria_general = rs_consulta_segura($link, 'gral_prim', $ciclo_escolar, $municipio);
        if ($primaria_general) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Primaria',
                'subnivel' => 'General',
                'tipo_educativo' => 'Primaria General',
                'total_escuelas' => $primaria_general['tot_esc'],
                'total_alumnos' => $primaria_general['tot_mat'],
                'alumnos_hombres' => $primaria_general['mat_h'],
                'alumnos_mujeres' => $primaria_general['mat_m'],
                'total_docentes' => $primaria_general['tot_doc'],
                'docentes_hombres' => $primaria_general['doc_h'],
                'docentes_mujeres' => $primaria_general['doc_m'],
                'total_grupos' => $primaria_general['tot_grp']
            ];
        }

        $primaria_indigena = rs_consulta_segura($link, 'ind_prim', $ciclo_escolar, $municipio);
        if ($primaria_indigena) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Primaria',
                'subnivel' => 'Indígena',
                'tipo_educativo' => 'Primaria Indígena',
                'total_escuelas' => $primaria_indigena['tot_esc'],
                'total_alumnos' => $primaria_indigena['tot_mat'],
                'alumnos_hombres' => $primaria_indigena['mat_h'],
                'alumnos_mujeres' => $primaria_indigena['mat_m'],
                'total_docentes' => $primaria_indigena['tot_doc'],
                'docentes_hombres' => $primaria_indigena['doc_h'],
                'docentes_mujeres' => $primaria_indigena['doc_m'],
                'total_grupos' => $primaria_indigena['tot_grp']
            ];
        }

        $primaria_comunitaria = rs_consulta_segura($link, 'comuni_prim', $ciclo_escolar, $municipio);
        if ($primaria_comunitaria) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Primaria',
                'subnivel' => 'Comunitaria',
                'tipo_educativo' => 'Primaria Comunitaria',
                'total_escuelas' => $primaria_comunitaria['tot_esc'],
                'total_alumnos' => $primaria_comunitaria['tot_mat'],
                'alumnos_hombres' => $primaria_comunitaria['mat_h'],
                'alumnos_mujeres' => $primaria_comunitaria['mat_m'],
                'total_docentes' => $primaria_comunitaria['tot_doc'],
                'docentes_hombres' => $primaria_comunitaria['doc_h'],
                'docentes_mujeres' => $primaria_comunitaria['doc_m'],
                'total_grupos' => $primaria_comunitaria['tot_grp']
            ];
        }

        // EDUCACIÓN SECUNDARIA
        $secundaria_general = rs_consulta_segura($link, 'gral_sec', $ciclo_escolar, $municipio);
        if ($secundaria_general) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Secundaria',
                'subnivel' => 'General',
                'tipo_educativo' => 'Secundaria General',
                'total_escuelas' => $secundaria_general['tot_esc'],
                'total_alumnos' => $secundaria_general['tot_mat'],
                'alumnos_hombres' => $secundaria_general['mat_h'],
                'alumnos_mujeres' => $secundaria_general['mat_m'],
                'total_docentes' => $secundaria_general['tot_doc'],
                'docentes_hombres' => $secundaria_general['doc_h'],
                'docentes_mujeres' => $secundaria_general['doc_m'],
                'total_grupos' => $secundaria_general['tot_grp']
            ];
        }

        $secundaria_comunitaria = rs_consulta_segura($link, 'comuni_sec', $ciclo_escolar, $municipio);
        if ($secundaria_comunitaria) {
            $datos_consolidados[] = [
                'nivel_educativo' => 'Secundaria',
                'subnivel' => 'Comunitaria',
                'tipo_educativo' => 'Secundaria Comunitaria',
                'total_escuelas' => $secundaria_comunitaria['tot_esc'],
                'total_alumnos' => $secundaria_comunitaria['tot_mat'],
                'alumnos_hombres' => $secundaria_comunitaria['mat_h'],
                'alumnos_mujeres' => $secundaria_comunitaria['mat_m'],
                'total_docentes' => $secundaria_comunitaria['tot_doc'],
                'docentes_hombres' => $secundaria_comunitaria['doc_h'],
                'docentes_mujeres' => $secundaria_comunitaria['doc_m'],
                'total_grupos' => $secundaria_comunitaria['tot_grp']
            ];
        }

        pg_close($link);

        // Calcular totales
        $total_escuelas = array_sum(array_column($datos_consolidados, 'total_escuelas'));
        $total_alumnos = array_sum(array_column($datos_consolidados, 'total_alumnos'));
        $total_docentes = array_sum(array_column($datos_consolidados, 'total_docentes'));

        return [
            'municipio' => $municipio,
            'ciclo_escolar' => $ciclo_escolar,
            'datos' => $datos_consolidados,
            'totales' => [
                'total_escuelas' => $total_escuelas,
                'total_alumnos' => $total_alumnos,
                'total_docentes' => $total_docentes
            ],
            'fecha_consulta' => date('Y-m-d H:i:s')
        ];

    } catch (Exception $e) {
        pg_close($link);
        error_log('Error en obtenerDatosEducativosCompletos: ' . $e->getMessage());

        return [
            'error' => 'Error al procesar datos educativos',
            'municipio' => $municipio,
            'datos' => []
        ];
    }
}

/**
 * Función para datos vacíos (cuando no hay información)
 */
function datos_vacion()
{
    return [
        "titulo_fila" => "",
        "tot_mat" => 0,
        "mat_h" => 0,
        "mat_m" => 0,
        "tot_doc" => 0,
        "doc_h" => 0,
        "doc_m" => 0,
        "tot_esc" => 0,
        "tot_grp" => 0
    ];
}

/**
 * Función para obtener datos de un nivel con separación público/privado
 * Adaptada de la función subnivel de bolsillo
 */
function subnivel($titulo_fila, $ini_ciclo, $str_consulta, $municipio)
{
    $link = ConectarsePrueba();
    if (!$link) {
        return datos_vacion();
    }

    $resultado = subnivel_con_control($link, $titulo_fila, $ini_ciclo, $str_consulta, $municipio);
    pg_close($link);

    return $resultado;
}

/**
 * Función principal para obtener todos los niveles educativos
 * Adaptada de la función nivel() de bolsillo
 */
function obtenerNiveles($municipio, $ini_ciclo, $con_detalle = true, $con_especial = true)
{
    $link = ConectarsePrueba();
    if (!$link) {
        return [];
    }

    // Niveles principales adaptados de bolsillo
    $inicial_esc = rs_consulta_segura($link, "inicial_esc", $ini_ciclo, $municipio);
    $inicial_no_esc = rs_consulta_segura($link, "inicial_no_esc", $ini_ciclo, $municipio);
    $preescolar = rs_consulta_segura($link, "preescolar", $ini_ciclo, $municipio);
    $primaria = rs_consulta_segura($link, "primaria", $ini_ciclo, $municipio);
    $secundaria = rs_consulta_segura($link, "secundaria", $ini_ciclo, $municipio);
    $media_sup = rs_consulta_segura($link, "media_sup", $ini_ciclo, $municipio);
    $superior = rs_consulta_segura($link, "superior", $ini_ciclo, $municipio);

    // Especial si se requiere
    $especial = null;
    if ($con_especial) {
        $especial = rs_consulta_segura($link, "especial_tot", $ini_ciclo, $municipio);
    }

    $niveles = [
        "inicial_esc" => $inicial_esc ?: datos_vacion(),
        "inicial_no_esc" => $inicial_no_esc ?: datos_vacion(),
        "preescolar" => $preescolar ?: datos_vacion(),
        "primaria" => $primaria ?: datos_vacion(),
        "secundaria" => $secundaria ?: datos_vacion(),
        "media_sup" => $media_sup ?: datos_vacion(),
        "superior" => $superior ?: datos_vacion()
    ];

    if ($especial) {
        $niveles["especial"] = $especial;
    }

    pg_close($link);
    return $niveles;
}

/**
 * Función principal para obtener resumen completo de municipio
 * Adaptada de la función obtenerDatosMunicipio de bolsillo
 */
function obtenerResumenMunicipioCompleto($municipio, $ini_ciclo = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ini_ciclo === null) {
        $ini_ciclo = obtenerCicloEscolarActual();
    }

    $link = ConectarsePrueba();
    if (!$link) {
        return false;
    }

    try {
        // Generar filtro de municipio exacto como bolsillo
        $num_muni = nombre_a_numero_municipio($municipio);
        $filtro_mun = ($num_muni !== false) ? " AND cv_mun='$num_muni' " : "";

        // Obtener totales básicos (replicando bolsillo exactamente)
        $inicial_esc = rs_consulta_segura($link, "inicial_esc", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $inicial_no_esc = rs_consulta_segura($link, "inicial_no_esc", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $preescolar = rs_consulta_segura($link, "preescolar", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $primaria = rs_consulta_segura($link, "primaria", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $secundaria = rs_consulta_segura($link, "secundaria", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $media_sup = rs_consulta_segura($link, "media_sup", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $superior = rs_consulta_segura($link, "superior", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $especial = rs_consulta_segura($link, "especial_tot", $ini_ciclo, $filtro_mun) ?: datos_vacion();

        // Calcular totales (como en bolsillo)
        $total_matricula = $inicial_esc["tot_mat"] + $inicial_no_esc["tot_mat"] +
            $preescolar["tot_mat"] + $primaria["tot_mat"] +
            $secundaria["tot_mat"] + $media_sup["tot_mat"] +
            $superior["tot_mat"] + $especial["tot_mat"];

        $total_docentes = $inicial_esc["tot_doc"] + $inicial_no_esc["tot_doc"] +
            $preescolar["tot_doc"] + $primaria["tot_doc"] +
            $secundaria["tot_doc"] + $media_sup["tot_doc"] +
            $superior["tot_doc"] + $especial["tot_doc"];

        $total_escuelas = $inicial_esc["tot_esc"] + $inicial_no_esc["tot_esc"] +
            $preescolar["tot_esc"] + $primaria["tot_esc"] +
            $secundaria["tot_esc"] + $media_sup["tot_esc"] +
            $superior["tot_esc"] + $especial["tot_esc"];

        $resumen = [
            "municipio" => $municipio,
            "total_matricula" => $total_matricula,
            "total_docentes" => $total_docentes,
            "total_escuelas" => $total_escuelas,
            "inicial_esc" => $inicial_esc,
            "inicial_no_esc" => $inicial_no_esc,
            "preescolar" => $preescolar,
            "primaria" => $primaria,
            "secundaria" => $secundaria,
            "media_sup" => $media_sup,
            "superior" => $superior,
            "especial" => $especial
        ];

        pg_close($link);
        return $resumen;

    } catch (Exception $e) {
        error_log("Error en obtenerResumenMunicipioCompleto: " . $e->getMessage());
        if ($link)
            pg_close($link);
        return false;
    }
}

/**
 * Obtiene resumen consolidado para tarjetas
 */
function obtenerResumenMunicipio($municipio = 'CORREGIDORA', $ciclo_escolar = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ciclo_escolar === null) {
        $ciclo_escolar = obtenerCicloEscolarActual();
    }
    $datos_completos = obtenerDatosEducativosCompletos($municipio, $ciclo_escolar);

    if (isset($datos_completos['error'])) {
        return [
            'escuelas' => 0,
            'alumnos' => 0,
            'docentes' => 0,
            'error' => $datos_completos['error']
        ];
    }

    return [
        'escuelas' => $datos_completos['totales']['total_escuelas'],
        'alumnos' => $datos_completos['totales']['total_alumnos'],
        'docentes' => $datos_completos['totales']['total_docentes'],
        'municipio' => $municipio,
        'ciclo_escolar' => $ciclo_escolar
    ];
}

/**
 * Obtiene datos agrupados por nivel educativo principal
 */
function obtenerDatosPorNivel($municipio = 'CORREGIDORA', $ciclo_escolar = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ciclo_escolar === null) {
        $ciclo_escolar = obtenerCicloEscolarActual();
    }
    $link = ConectarsePrueba();
    if (!$link) {
        return [
            'error' => 'No se pudo conectar a la base de datos',
            'niveles' => []
        ];
    }

    $municipio = normalizarNombreMunicipio($municipio);
    $niveles_bolsillo = [];

    try {
        // EDUCACIÓN INICIAL - Replicando exactamente como bolsillo
        $inicial_general = rs_consulta_segura($link, 'gral_ini', $ciclo_escolar, $municipio);
        if ($inicial_general && $inicial_general['tot_mat'] > 0) {
            $niveles_bolsillo[] = $inicial_general;
        }

        $inicial_indigena = rs_consulta_segura($link, 'ind_ini', $ciclo_escolar, $municipio);
        if ($inicial_indigena && $inicial_indigena['tot_mat'] > 0) {
            $niveles_bolsillo[] = $inicial_indigena;
        }

        $inicial_lactante = rs_consulta_segura($link, 'lact_ini', $ciclo_escolar, $municipio);
        if ($inicial_lactante && $inicial_lactante['tot_mat'] > 0) {
            $niveles_bolsillo[] = $inicial_lactante;
        }

        $inicial_maternal = rs_consulta_segura($link, 'mater_ini', $ciclo_escolar, $municipio);
        if ($inicial_maternal && $inicial_maternal['tot_mat'] > 0) {
            $niveles_bolsillo[] = $inicial_maternal;
        }

        // EDUCACIÓN PREESCOLAR
        $preescolar_general = rs_consulta_segura($link, 'gral_pree', $ciclo_escolar, $municipio);
        if ($preescolar_general && $preescolar_general['tot_mat'] > 0) {
            $niveles_bolsillo[] = $preescolar_general;
        }

        $preescolar_indigena = rs_consulta_segura($link, 'ind_pree', $ciclo_escolar, $municipio);
        if ($preescolar_indigena && $preescolar_indigena['tot_mat'] > 0) {
            $niveles_bolsillo[] = $preescolar_indigena;
        }

        $preescolar_comunitario = rs_consulta_segura($link, 'comuni_pree', $ciclo_escolar, $municipio);
        if ($preescolar_comunitario && $preescolar_comunitario['tot_mat'] > 0) {
            $niveles_bolsillo[] = $preescolar_comunitario;
        }

        // EDUCACIÓN PRIMARIA
        $primaria_general = rs_consulta_segura($link, 'gral_prim', $ciclo_escolar, $municipio);
        if ($primaria_general && $primaria_general['tot_mat'] > 0) {
            $niveles_bolsillo[] = $primaria_general;
        }

        $primaria_indigena = rs_consulta_segura($link, 'ind_prim', $ciclo_escolar, $municipio);
        if ($primaria_indigena && $primaria_indigena['tot_mat'] > 0) {
            $niveles_bolsillo[] = $primaria_indigena;
        }

        $primaria_comunitario = rs_consulta_segura($link, 'comuni_prim', $ciclo_escolar, $municipio);
        if ($primaria_comunitario && $primaria_comunitario['tot_mat'] > 0) {
            $niveles_bolsillo[] = $primaria_comunitario;
        }

        // EDUCACIÓN SECUNDARIA
        $secundaria_general = rs_consulta_segura($link, 'sec_gral_gral', $ciclo_escolar, $municipio);
        if ($secundaria_general && $secundaria_general['tot_mat'] > 0) {
            $niveles_bolsillo[] = $secundaria_general;
        }

        $secundaria_telesecundaria = rs_consulta_segura($link, 'sec_gral_tele', $ciclo_escolar, $municipio);
        if ($secundaria_telesecundaria && $secundaria_telesecundaria['tot_mat'] > 0) {
            $niveles_bolsillo[] = $secundaria_telesecundaria;
        }

        $secundaria_tecnica = rs_consulta_segura($link, 'sec_gral_tec', $ciclo_escolar, $municipio);
        if ($secundaria_tecnica && $secundaria_tecnica['tot_mat'] > 0) {
            $niveles_bolsillo[] = $secundaria_tecnica;
        }

        $secundaria_comunitario = rs_consulta_segura($link, 'comuni_sec', $ciclo_escolar, $municipio);
        if ($secundaria_comunitario && $secundaria_comunitario['tot_mat'] > 0) {
            $niveles_bolsillo[] = $secundaria_comunitario;
        }

        // EDUCACIÓN MEDIA SUPERIOR
        $bachillerato_general = rs_consulta_segura($link, 'bgral_msup', $ciclo_escolar, $municipio);
        if ($bachillerato_general && $bachillerato_general['tot_mat'] > 0) {
            $niveles_bolsillo[] = $bachillerato_general;
        }

        $bachillerato_tecnologico = rs_consulta_segura($link, 'btecno_msup', $ciclo_escolar, $municipio);
        if ($bachillerato_tecnologico && $bachillerato_tecnologico['tot_mat'] > 0) {
            $niveles_bolsillo[] = $bachillerato_tecnologico;
        }

        // EDUCACIÓN SUPERIOR
        $licenciatura = rs_consulta_segura($link, 'carr_lic_sup', $ciclo_escolar, $municipio);
        if ($licenciatura && $licenciatura['tot_mat'] > 0) {
            $niveles_bolsillo[] = $licenciatura;
        }

        $posgrado = rs_consulta_segura($link, 'posgr_sup', $ciclo_escolar, $municipio);
        if ($posgrado && $posgrado['tot_mat'] > 0) {
            $niveles_bolsillo[] = $posgrado;
        }

        pg_close($link);

        return [
            'municipio' => $municipio,
            'ciclo_escolar' => $ciclo_escolar,
            'niveles' => $niveles_bolsillo,
            'fecha_consulta' => date('Y-m-d H:i:s')
        ];

    } catch (Exception $e) {
        error_log("Error obteniendo datos por nivel para $municipio: " . $e->getMessage());
        if ($link)
            pg_close($link);

        return [
            'error' => 'Error al obtener datos por nivel',
            'municipio' => $municipio,
            'niveles' => []
        ];
    }
}

/**
 * Obtiene lista de municipios disponibles
 */
function obtenerMunicipios()
{
    $municipiosQueretaro = [
        'AMEALCO DE BONFIL',
        'ARROYO SECO',
        'CADEREYTA DE MONTES',
        'COLÓN',
        'CORREGIDORA',
        'EL MARQUÉS',
        'EZEQUIEL MONTES',
        'HUIMILPAN',
        'JALPAN DE SERRA',
        'LANDA DE MATAMOROS',
        'PEÑAMILLER',
        'PEDRO ESCOBEDO',
        'PINAL DE AMOLES',
        'QUERÉTARO',
        'SAN JOAQUÍN',
        'SAN JUAN DEL RÍO',
        'TEQUISQUIAPAN',
        'TOLIMÁN'
    ];

    $link = ConectarsePrueba();
    if (!$link) {
        return $municipiosQueretaro;
    }

    try {
        $query = "SELECT DISTINCT TRIM(UPPER(c_nom_mun)) AS municipio
                  FROM nonce_pano_24.ini_gral_24 
                  WHERE c_nom_mun IS NOT NULL
                  UNION
                  SELECT DISTINCT TRIM(UPPER(c_nom_mun)) AS municipio
                  FROM nonce_pano_24.pree_gral_24 
                  WHERE c_nom_mun IS NOT NULL
                  ORDER BY municipio";

        $result = pg_query($link, $query);

        if ($result) {
            $municipios = [];
            while ($row = pg_fetch_assoc($result)) {
                $municipio_normalizado = normalizarNombreMunicipio($row['municipio']);
                if ($municipio_normalizado && !in_array($municipio_normalizado, $municipios)) {
                    $municipios[] = $municipio_normalizado;
                }
            }
            pg_free_result($result);
            pg_close($link);

            // Agregar municipios faltantes
            foreach ($municipiosQueretaro as $oficial) {
                if (!in_array($oficial, $municipios)) {
                    $municipios[] = $oficial;
                }
            }

            sort($municipios);
            return $municipios;
        }

    } catch (Exception $e) {
        error_log('Error obteniendo municipios: ' . $e->getMessage());
    }

    if ($link)
        pg_close($link);
    return $municipiosQueretaro;
}

/**
 * Normaliza nombres de municipios para consistencia
 */
function normalizarNombreMunicipio($nombreMunicipio)
{
    $nombre = trim(strtoupper((string) $nombreMunicipio));

    $normalizaciones = [
        'QUER?TARO' => 'QUERÉTARO',
        'QUERETARO' => 'QUERÉTARO',
        'EL MARQU?S' => 'EL MARQUÉS',
        'EL MARQUES' => 'EL MARQUÉS',
        'SAN JUAN DEL R??O' => 'SAN JUAN DEL RÍO',
        'SAN JUAN DEL RIO' => 'SAN JUAN DEL RÍO',
        'SAN JUAN DEL R?O' => 'SAN JUAN DEL RÍO',
        'SAN JOAQU?N' => 'SAN JOAQUÍN',
        'SAN JOAQUIN' => 'SAN JOAQUÍN',
        'PE?AMILLER' => 'PEÑAMILLER',
        'PENAMILLER' => 'PEÑAMILLER',
        'TOLIM?N' => 'TOLIMÁN',
        'TOLIMAN' => 'TOLIMÁN',
        'COL?N' => 'COLÓN',
        'COLON' => 'COLÓN'
    ];

    return isset($normalizaciones[$nombre]) ? $normalizaciones[$nombre] : $nombre;
}

/**
 * Convierte nombres normalizados a formato de base de datos para consultas
 */
function convertirParaConsultaDB($nombreMunicipio)
{
    $nombre = trim(strtoupper((string) $nombreMunicipio));

    // Mapeo inverso: de nombres limpios a como están en la DB
    $mapeoInverso = [
        'QUERÉTARO' => 'QUER?TARO',
        'EL MARQUÉS' => 'EL MARQU?S',
        'SAN JUAN DEL RÍO' => 'SAN JUAN DEL R??O',
        'SAN JOAQUÍN' => 'SAN JOAQU?N',
        'PEÑAMILLER' => 'PE?AMILLER',
        'TOLIMÁN' => 'TOLIM?N',
        'COLÓN' => 'COL?N'
    ];

    return isset($mapeoInverso[$nombre]) ? $mapeoInverso[$nombre] : $nombre;
}

/**
 * Función principal de arreglos_datos replicando exactamente bolsillo
 * @param string $ini_ciclo 
 * @param string $str_consulta
 * @param string $muni
 * @return array|false
 */
function arreglos_datos_segura($ini_ciclo, $str_consulta, $muni)
{
    global $sin_filtro_extra, $filtro_pub, $filtro_priv;

    // Generar filtro de municipio como bolsillo
    $num_muni = nombre_a_numero_municipio($muni);
    $filtro = ($num_muni !== false) ? " AND cv_mun='$num_muni' " : "";

    // Obtener conexión
    $link = ConectarsePrueba();
    if (!$link)
        return false;

    // Base de datos - usar función rs_consulta_segura con argumentos correctos
    $c = rs_consulta_segura($link, $str_consulta, $ini_ciclo, $filtro);
    if (!$c) {
        pg_close($link);
        return false;
    }

    // Aplica subniveles como bolsillo con argumentos correctos  
    $resultado = array();
    $c_pub = subnivel_seguro($link, $str_consulta, $ini_ciclo, $filtro . $filtro_pub, "", "", "");
    $c_priv = subnivel_seguro($link, $str_consulta, $ini_ciclo, $filtro . $filtro_priv, "", "", "");

    // Estructura de resultado igual que bolsillo
    $resultado['total'] = $c;
    $resultado['publico'] = $c_pub;
    $resultado['privado'] = $c_priv;

    pg_close($link);
    return $resultado;
}

/**
 * Obtiene el resumen completo de todos los municipios del estado (totales estatales)
 * Para calcular porcentajes municipio vs estado
 * 
 * @param string $ini_ciclo Ciclo escolar (opcional, usa el actual por defecto)
 * @return array|false Datos totales del estado o false en caso de error
 */
function obtenerResumenEstadoCompleto($ini_ciclo = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ini_ciclo === null) {
        $ini_ciclo = obtenerCicloEscolarActual();
    }

    $link = ConectarsePrueba();
    if (!$link) {
        return false;
    }

    try {
        // Sin filtro de municipio para obtener datos de todo el estado
        $filtro_mun = "";

        // Obtener totales por nivel (igual que obtenerResumenMunicipioCompleto pero sin filtro)
        $inicial_esc = rs_consulta_segura($link, "inicial_esc", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $inicial_no_esc = rs_consulta_segura($link, "inicial_no_esc", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $preescolar = rs_consulta_segura($link, "preescolar", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $primaria = rs_consulta_segura($link, "primaria", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $secundaria = rs_consulta_segura($link, "secundaria", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $media_sup = rs_consulta_segura($link, "media_sup", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $superior = rs_consulta_segura($link, "superior", $ini_ciclo, $filtro_mun) ?: datos_vacion();
        $especial = rs_consulta_segura($link, "especial_tot", $ini_ciclo, $filtro_mun) ?: datos_vacion();

        // Calcular totales estatales (como en bolsillo)
        $total_matricula = $inicial_esc["tot_mat"] + $inicial_no_esc["tot_mat"] +
            $preescolar["tot_mat"] + $primaria["tot_mat"] +
            $secundaria["tot_mat"] + $media_sup["tot_mat"] +
            $superior["tot_mat"] + $especial["tot_mat"];

        $total_docentes = $inicial_esc["tot_doc"] + $inicial_no_esc["tot_doc"] +
            $preescolar["tot_doc"] + $primaria["tot_doc"] +
            $secundaria["tot_doc"] + $media_sup["tot_doc"] +
            $superior["tot_doc"] + $especial["tot_doc"];

        $total_escuelas = $inicial_esc["tot_esc"] + $inicial_no_esc["tot_esc"] +
            $preescolar["tot_esc"] + $primaria["tot_esc"] +
            $secundaria["tot_esc"] + $media_sup["tot_esc"] +
            $superior["tot_esc"] + $especial["tot_esc"];

        $resultado = [
            'total_matricula' => $total_matricula,
            'total_docentes' => $total_docentes,
            'total_escuelas' => $total_escuelas,
            'niveles' => [
                'inicial_esc' => [
                    'titulo_fila' => 'INICIAL ESCOLARIZADA',
                    'tot_mat' => $inicial_esc["tot_mat"],
                    'tot_doc' => $inicial_esc["tot_doc"],
                    'tot_esc' => $inicial_esc["tot_esc"]
                ],
                'inicial_no_esc' => [
                    'titulo_fila' => 'INICIAL NO ESCOLARIZADA',
                    'tot_mat' => $inicial_no_esc["tot_mat"],
                    'tot_doc' => $inicial_no_esc["tot_doc"],
                    'tot_esc' => $inicial_no_esc["tot_esc"]
                ],
                'preescolar' => [
                    'titulo_fila' => 'PREESCOLAR',
                    'tot_mat' => $preescolar["tot_mat"],
                    'tot_doc' => $preescolar["tot_doc"],
                    'tot_esc' => $preescolar["tot_esc"]
                ],
                'primaria' => [
                    'titulo_fila' => 'PRIMARIA',
                    'tot_mat' => $primaria["tot_mat"],
                    'tot_doc' => $primaria["tot_doc"],
                    'tot_esc' => $primaria["tot_esc"]
                ],
                'secundaria' => [
                    'titulo_fila' => 'SECUNDARIA',
                    'tot_mat' => $secundaria["tot_mat"],
                    'tot_doc' => $secundaria["tot_doc"],
                    'tot_esc' => $secundaria["tot_esc"]
                ],
                'media_superior' => [
                    'titulo_fila' => 'MEDIA SUPERIOR',
                    'tot_mat' => $media_sup["tot_mat"],
                    'tot_doc' => $media_sup["tot_doc"],
                    'tot_esc' => $media_sup["tot_esc"]
                ],
                'superior' => [
                    'titulo_fila' => 'SUPERIOR',
                    'tot_mat' => $superior["tot_mat"],
                    'tot_doc' => $superior["tot_doc"],
                    'tot_esc' => $superior["tot_esc"]
                ],
                'especial_tot' => [
                    'titulo_fila' => 'ESPECIAL TOTAL',
                    'tot_mat' => $especial["tot_mat"],
                    'tot_doc' => $especial["tot_doc"],
                    'tot_esc' => $especial["tot_esc"]
                ]
            ]
        ];

        pg_close($link);
        return $resultado;

    } catch (Exception $e) {
        error_log("Error en obtenerResumenEstadoCompleto: " . $e->getMessage());
        if ($link)
            pg_close($link);
        return false;
    }
}/**
 * Calcula los porcentajes de un municipio respecto al estado
 * 
 * @param array $datosMunicipio Datos del municipio
 * @param array $datosEstado Datos del estado completo
 * @return array Datos con porcentajes calculados
 */
function calcularPorcentajesMunicipioEstado($datosMunicipio, $datosEstado)
{
    if (
        !$datosMunicipio || !$datosEstado ||
        !isset($datosMunicipio['datos_completos']) ||
        !isset($datosEstado['niveles'])
    ) {
        return [];
    }

    $resultado = [
        'porcentajes_totales' => [
            'matricula' => 0,
            'docentes' => 0,
            'escuelas' => 0
        ],
        'porcentajes_por_nivel' => []
    ];

    // Calcular porcentajes totales
    if ($datosEstado['total_matricula'] > 0) {
        $resultado['porcentajes_totales']['matricula'] = round(
            ($datosMunicipio['datos_completos']['totales']['total_alumnos'] / $datosEstado['total_matricula']) * 100,
            2
        );
    }

    if ($datosEstado['total_docentes'] > 0) {
        $resultado['porcentajes_totales']['docentes'] = round(
            ($datosMunicipio['datos_completos']['totales']['total_docentes'] / $datosEstado['total_docentes']) * 100,
            2
        );
    }

    if ($datosEstado['total_escuelas'] > 0) {
        $resultado['porcentajes_totales']['escuelas'] = round(
            ($datosMunicipio['datos_completos']['totales']['total_escuelas'] / $datosEstado['total_escuelas']) * 100,
            2
        );
    }

    // Calcular porcentajes por nivel educativo
    if (isset($datosMunicipio['datos_por_nivel']['niveles'])) {
        foreach ($datosMunicipio['datos_por_nivel']['niveles'] as $nivel) {
            $titulo = $nivel['titulo_fila'];

            // Buscar el nivel correspondiente en datos del estado
            foreach ($datosEstado['niveles'] as $codigo => $nivelEstado) {
                if ($nivelEstado['titulo_fila'] === $titulo) {
                    $porcentajeMatricula = 0;
                    $porcentajeDocentes = 0;
                    $porcentajeEscuelas = 0;

                    if ($nivelEstado['tot_mat'] > 0) {
                        $porcentajeMatricula = round(($nivel['tot_mat'] / $nivelEstado['tot_mat']) * 100, 2);
                    }
                    if ($nivelEstado['tot_doc'] > 0) {
                        $porcentajeDocentes = round(($nivel['tot_doc'] / $nivelEstado['tot_doc']) * 100, 2);
                    }
                    if ($nivelEstado['tot_esc'] > 0) {
                        $porcentajeEscuelas = round(($nivel['tot_esc'] / $nivelEstado['tot_esc']) * 100, 2);
                    }

                    $resultado['porcentajes_por_nivel'][] = [
                        'titulo_fila' => $titulo,
                        'porcentaje_matricula' => $porcentajeMatricula,
                        'porcentaje_docentes' => $porcentajeDocentes,
                        'porcentaje_escuelas' => $porcentajeEscuelas,
                        'datos_municipio' => [
                            'matricula' => $nivel['tot_mat'],
                            'docentes' => $nivel['tot_doc'],
                            'escuelas' => $nivel['tot_esc']
                        ],
                        'datos_estado' => [
                            'matricula' => $nivelEstado['tot_mat'],
                            'docentes' => $nivelEstado['tot_doc'],
                            'escuelas' => $nivelEstado['tot_esc']
                        ]
                    ];
                    break;
                }
            }
        }
    }

    return $resultado;
}
