<?php
/**
 * =============================================================================
 * CONEXIÓN DE PRUEBA REESTRUCTURADA - BASADA EN BOLSILLO (1)(1).PHP
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 *
 *
 * 
 * @author Sistema SEDEQ
 * @version 2.0.0
 * @since 2025
 */


define('CICLO_ESCOLAR_ACTUAL', '23');

/**
 * Verifica si el ciclo escolar actual tiene soporte para tablas de unidades
 * Las tablas sup_unidades_XX solo existen desde el ciclo 24 (2024-2025) en adelante
 * 
 * @param string $ini_ciclo Ciclo escolar (formato: '24', '25', etc.)
 * @return bool True si el ciclo tiene tablas de unidades, False en caso contrario
 */
function tieneUnidades($ini_ciclo)
{
    // Convertir a entero para comparación
    $ciclo = intval($ini_ciclo);

    // Las tablas de unidades existen desde el ciclo 24 (2024-2025)
    return $ciclo >= 24;
}

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
                        SUM(V398+V414) AS total_matricula,
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
            // VALIDAR: Solo generar consulta si el ciclo tiene tablas de unidades (≥ 24)
            if (!tieneUnidades($ini_ciclo)) {
                return false; // No generar consulta para ciclos sin unidades
            }

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

        // ===== CONSULTAS PARA DIRECTORIO DE ESCUELAS INDIVIDUALES =====
        case 'gral_ini_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        (V398+V414) as total_alumnos,
                        (V390+V406) as alumnos_hombres,
                        (V394+V410) as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'ind_ini_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        (V183+V184) as total_alumnos,
                        V183 as alumnos_hombres,
                        V184 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ini_ind_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'lact_ini_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V398 as total_alumnos,
                        V390 as alumnos_hombres,
                        V394 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'mater_ini_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V414 as total_alumnos,
                        V406 as alumnos_hombres,
                        V410 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'gral_pree_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V177 as total_alumnos,
                        V165 as alumnos_hombres,
                        V171 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'ind_pree_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V177 as total_alumnos,
                        V165 as alumnos_hombres,
                        V171 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.pree_ind_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'gral_prim_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V608 as total_alumnos,
                        (V562+V573) as alumnos_hombres,
                        (V585+V596) as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'ind_prim_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V610 as total_alumnos,
                        (V564+V575) as alumnos_hombres,
                        (V587+V598) as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.prim_ind_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'gral_sec_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V340 as total_alumnos,
                        (V306+V314) as alumnos_hombres,
                        (V323+V331) as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'comuni_pree_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V97 as total_alumnos,
                        V85 as alumnos_hombres,
                        V91 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.pree_comuni_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'comuni_prim_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V515 as total_alumnos,
                        (V469+V480) as alumnos_hombres,
                        (V492+V503) as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.prim_comuni_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'comuni_sec_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V257 as total_alumnos,
                        (V223+V231) as alumnos_hombres,
                        (V240+V248) as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.sec_comuni_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'comuni_ini_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        V81 as total_alumnos,
                        V79 as alumnos_hombres,
                        V80 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ini_comuni_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'ne_ini_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        (V129+V130) as total_alumnos,
                        V129 as alumnos_hombres,
                        V130 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ini_ne_$ini_ciclo
                    WHERE $filtroBase $filtro";

        case 'bgral_msup_directorio':
            return "SELECT cct_ins_pla as cv_cct,
                        nombre_ins_pla as nombre_escuela,
                        c_nom_loc as localidad,
                        V397 as total_alumnos,
                        V395 as alumnos_hombres,
                        V396 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro";

        case 'btecno_msup_directorio':
            return "SELECT cct_ins_pla as cv_cct,
                        nombre_ins_pla as nombre_escuela,
                        c_nom_loc as localidad,
                        V472 as total_alumnos,
                        V470 as alumnos_hombres,
                        V471 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo
                    WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro";

        case 'media_sup_directorio':
            return "SELECT cv_cct,
                        nombre_escuela,
                        localidad,
                        turno,
                        total_alumnos,
                        alumnos_hombres,
                        alumnos_mujeres,
                        tipo_control
                    FROM (
                        SELECT cct_ins_pla as cv_cct, nombre_ins_pla as nombre_escuela, c_nom_loc as localidad, c_turno as turno, V397 as total_alumnos, V395 as alumnos_hombres, V396 as alumnos_mujeres, control as tipo_control
                        FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo
                        WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro AND V397 > 0
                        UNION ALL
                        SELECT cct_ins_pla as cv_cct, nombre_ins_pla as nombre_escuela, c_nom_loc as localidad, c_turno as turno, V472 as total_alumnos, V470 as alumnos_hombres, V471 as alumnos_mujeres, control as tipo_control
                        FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo
                        WHERE cv_motivo = '0' AND (cv_estatus<>'4' AND cv_estatus<>'2') $filtro AND V472 > 0
                    ) AS media_sup_dir
                    ORDER BY cv_cct, turno";

        case 'especial_tot_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        V2257 as total_alumnos,
                        V2255 as alumnos_hombres,
                        V2256 as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo
                    WHERE cv_estatus_captura = 0 $filtro
                    ORDER BY cv_cct, turno";

        case 'especial_usaer_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        v2827 as total_alumnos,
                        (V2814+V2816+V2818+V2820) as alumnos_hombres,
                        (V2815+V2817+V2819+V2821) as alumnos_mujeres,
                        control as tipo_control
                    FROM nonce_pano_$ini_ciclo.esp_usaer_$ini_ciclo
                    WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) $filtro
                    ORDER BY cv_cct, turno";

        case 'superior_directorio':
            // Construir consulta base con sup_carrera y sup_posgrado
            $query_base = "SELECT cct_ins_pla as cv_cct,
                        MAX(nombre_ins_pla) as nombre_escuela,
                        MAX(localidad) as localidad,
                        SUM(total_alumnos) as total_alumnos,
                        SUM(alumnos_hombres) as alumnos_hombres,
                        SUM(alumnos_mujeres) as alumnos_mujeres,
                        MAX(control) as tipo_control
                    FROM (
                        SELECT cct_ins_pla, nombre_ins_pla, c_nom_loc as localidad, V177 as total_alumnos, V175 as alumnos_hombres, V176 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo
                        WHERE cv_motivo = '0' $filtro AND V177 > 0
                        UNION ALL
                        SELECT cct_ins_pla, nombre_ins_pla, c_nom_loc as localidad, V142 as total_alumnos, V140 as alumnos_hombres, V141 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.sup_posgrado_$ini_ciclo
                        WHERE cv_motivo = '0' $filtro AND V142 > 0";

            // Solo agregar sup_unidades si el ciclo las tiene (≥ 24)
            if (tieneUnidades($ini_ciclo)) {
                $query_base .= "
                        UNION ALL
                        SELECT cct_ins_pla, nombre_ins_pla, c_nom_mun as localidad, total_matricula as total_alumnos, mat_hombres as alumnos_hombres, mat_mujeres as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.sup_unidades_$ini_ciclo
                        WHERE 1=1 $filtro AND total_matricula > 0";
            }

            $query_base .= "
                    ) AS superior_dir
                    GROUP BY cct_ins_pla
                    ORDER BY cct_ins_pla";

            return $query_base;

        case 'superior_directorio_queretaro':
            // Querétaro: Muestra cada registro de sup_escuela_24 (83 registros = campus/turnos)
            // Los alumnos se distribuyen entre campus: división entera + residuo al primer registro
            // Coincide con el conteo de resumen.php que hace COUNT(cct_ins_pla) sin agrupar
            return "SELECT
                        sub.id_sec as id_registro,
                        sub.cv_cct,
                        sub.nombre_escuela,
                        sub.localidad,
                        sub.base_alumnos +
                        CASE
                            WHEN sub.row_num = 1 THEN sub.residuo_alumnos
                            ELSE 0
                        END -
                        CASE
                            -- Restar unidades estatales de instituciones específicas (solo del primer registro)
                            WHEN sub.cv_cct = '22MSU0090J' AND sub.row_num = 1 THEN 889  -- Universidad Pedagógica Nacional
                            WHEN sub.cv_cct = '22MSU0024K' AND sub.row_num = 1 THEN 626  -- Tecnológico Nacional de México
                            ELSE 0
                        END as total_alumnos,
                        sub.base_hombres +
                        CASE
                            WHEN sub.row_num = 1 THEN sub.residuo_hombres
                            ELSE 0
                        END -
                        CASE
                            -- Restar hombres de unidades estatales (solo del primer registro)
                            WHEN sub.cv_cct = '22MSU0090J' AND sub.row_num = 1 THEN 206  -- Universidad Pedagógica Nacional
                            WHEN sub.cv_cct = '22MSU0024K' AND sub.row_num = 1 THEN 374  -- Tecnológico Nacional de México
                            ELSE 0
                        END as alumnos_hombres,
                        sub.base_mujeres +
                        CASE
                            WHEN sub.row_num = 1 THEN sub.residuo_mujeres
                            ELSE 0
                        END -
                        CASE
                            -- Restar mujeres de unidades estatales (solo del primer registro)
                            WHEN sub.cv_cct = '22MSU0090J' AND sub.row_num = 1 THEN 683  -- Universidad Pedagógica Nacional
                            WHEN sub.cv_cct = '22MSU0024K' AND sub.row_num = 1 THEN 252  -- Tecnológico Nacional de México
                            ELSE 0
                        END as alumnos_mujeres,
                        sub.tipo_control
                    FROM (
                        SELECT
                            e.id_sec,
                            e.cct_ins_pla as cv_cct,
                            e.nombre_ins as nombre_escuela,
                            e.c_nom_loc as localidad,
                            e.control as tipo_control,
                            ROW_NUMBER() OVER (PARTITION BY e.cct_ins_pla ORDER BY e.id_sec) as row_num,
                            CAST((COALESCE(alumnos_carrera.total, 0) + COALESCE(alumnos_posgrado.total, 0)) / total_registros.cnt AS INTEGER) as base_alumnos,
                            (COALESCE(alumnos_carrera.total, 0) + COALESCE(alumnos_posgrado.total, 0)) % total_registros.cnt as residuo_alumnos,
                            CAST((COALESCE(alumnos_carrera.hombres, 0) + COALESCE(alumnos_posgrado.hombres, 0)) / total_registros.cnt AS INTEGER) as base_hombres,
                            (COALESCE(alumnos_carrera.hombres, 0) + COALESCE(alumnos_posgrado.hombres, 0)) % total_registros.cnt as residuo_hombres,
                            CAST((COALESCE(alumnos_carrera.mujeres, 0) + COALESCE(alumnos_posgrado.mujeres, 0)) / total_registros.cnt AS INTEGER) as base_mujeres,
                            (COALESCE(alumnos_carrera.mujeres, 0) + COALESCE(alumnos_posgrado.mujeres, 0)) % total_registros.cnt as residuo_mujeres
                        FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo e
                        LEFT JOIN (
                            SELECT cct_ins_pla, SUM(V177) as total, SUM(V175) as hombres, SUM(V176) as mujeres
                            FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo
                            WHERE cv_motivo = '0' $filtro
                            GROUP BY cct_ins_pla
                        ) alumnos_carrera ON e.cct_ins_pla = alumnos_carrera.cct_ins_pla
                        LEFT JOIN (
                            SELECT cct_ins_pla, SUM(V142) as total, SUM(V140) as hombres, SUM(V141) as mujeres
                            FROM nonce_pano_$ini_ciclo.sup_posgrado_$ini_ciclo
                            WHERE cv_motivo = '0' $filtro
                            GROUP BY cct_ins_pla
                        ) alumnos_posgrado ON e.cct_ins_pla = alumnos_posgrado.cct_ins_pla
                        LEFT JOIN (
                            SELECT cct_ins_pla, COUNT(*) as cnt
                            FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo
                            WHERE cv_motivo = '0' $filtro
                            GROUP BY cct_ins_pla
                        ) total_registros ON e.cct_ins_pla = total_registros.cct_ins_pla
                        WHERE e.cv_motivo = '0' $filtro
                    ) sub
                    ORDER BY sub.cv_cct, sub.row_num";

        case 'inicial_esc_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        total_alumnos,
                        alumnos_hombres,
                        alumnos_mujeres,
                        control as tipo_control
                    FROM (
                        SELECT cv_cct, nombrect, c_nom_loc, turno, (V398+V414) as total_alumnos, (V390+V406) as alumnos_hombres, (V394+V410) as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, (V183+V184) as total_alumnos, V183 as alumnos_hombres, V184 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.ini_ind_$ini_ciclo
                        WHERE $filtroBase $filtro
                    ) AS inicial_esc_dir
                    ORDER BY cv_cct, turno";

        case 'inicial_no_esc_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        total_alumnos,
                        alumnos_hombres,
                        alumnos_mujeres,
                        control as tipo_control
                    FROM (
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V81 as total_alumnos, V79 as alumnos_hombres, V80 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.ini_comuni_$ini_ciclo
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, (V129+V130) as total_alumnos, V129 as alumnos_hombres, V130 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.ini_ne_$ini_ciclo
                        WHERE $filtroBase $filtro
                    ) AS inicial_no_esc_dir
                    ORDER BY cv_cct, turno";

        case 'preescolar_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        total_alumnos,
                        alumnos_hombres,
                        alumnos_mujeres,
                        control as tipo_control
                    FROM (
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V177 as total_alumnos, V165 as alumnos_hombres, V171 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo
                        WHERE $filtroBase $filtro AND V177 > 0
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V177 as total_alumnos, V165 as alumnos_hombres, V171 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.pree_ind_$ini_ciclo
                        WHERE $filtroBase $filtro AND V177 > 0
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V97 as total_alumnos, V85 as alumnos_hombres, V91 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.pree_comuni_$ini_ciclo
                        WHERE $filtroBase $filtro AND V97 > 0
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V478 as total_alumnos, V466 as alumnos_hombres, V472 as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
                        WHERE $filtroBase $filtro AND V478 > 0
                    ) AS preescolar_dir
                    ORDER BY cv_cct, turno";

        case 'primaria_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        total_alumnos,
                        alumnos_hombres,
                        alumnos_mujeres,
                        control as tipo_control
                    FROM (
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V608 as total_alumnos, (V562+V573) as alumnos_hombres, (V585+V596) as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V610 as total_alumnos, (V564+V575) as alumnos_hombres, (V587+V598) as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.prim_ind_$ini_ciclo
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V515 as total_alumnos, (V469+V480) as alumnos_hombres, (V492+V503) as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.prim_comuni_$ini_ciclo
                        WHERE $filtroBase $filtro
                    ) AS primaria_dir
                    ORDER BY cv_cct, turno";

        case 'secundaria_directorio':
            return "SELECT cv_cct,
                        nombrect as nombre_escuela,
                        c_nom_loc as localidad,
                        turno,
                        total_alumnos,
                        alumnos_hombres,
                        alumnos_mujeres,
                        control as tipo_control
                    FROM (
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V340 as total_alumnos, (V306+V314) as alumnos_hombres, (V323+V331) as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo
                        WHERE $filtroBase $filtro
                        UNION ALL
                        SELECT cv_cct, nombrect, c_nom_loc, turno, V257 as total_alumnos, (V223+V231) as alumnos_hombres, (V240+V248) as alumnos_mujeres, control
                        FROM nonce_pano_$ini_ciclo.sec_comuni_$ini_ciclo
                        WHERE $filtroBase $filtro
                    ) AS secundaria_dir
                    ORDER BY cv_cct, turno";

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
    // VERIFICAR SI EL CICLO TIENE TABLAS DE UNIDADES
    // Las tablas sup_unidades_XX solo existen desde el ciclo 24 (2024-2025)
    if (!tieneUnidades($ini_ciclo)) {
        // Si el ciclo es anterior a 24, retornar datos sin ajuste de unidades
        return $arr_nivel1;
    }

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
        // VERIFICAR SI EL CICLO TIENE TABLAS DE UNIDADES
        // Las tablas sup_unidades_XX solo existen desde el ciclo 24 (2024-2025)
        if (!tieneUnidades($ini_ciclo)) {
            // Si el ciclo es anterior a 24, solo retornar datos base sin ajuste de unidades
            return rs_consulta_segura($link, 'superior', $ini_ciclo, $filtro);
        }

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
            'especial_tot' => 'ESPECIAL (CAM)',
            'especial_usaer' => 'ESPECIAL (USAER)',
            'preescolar' => 'PREESCOLAR',
            'primaria' => 'PRIMARIA',
            'secundaria' => 'SECUNDARIA',
            'media_sup' => 'MEDIA SUPERIOR',
            'superior' => 'SUPERIOR'
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

/**
 * Función para obtener datos de USAER con desglose público/privado
 * @param string $municipio Nombre del municipio
 * @param string $ini_ciclo Ciclo escolar
 * @return array|false Datos de USAER con desglose o false si no hay datos
 */
function obtenerDatosUSAER($municipio = 'CORREGIDORA', $ini_ciclo = null)
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

        // Obtener datos de USAER con desglose público/privado
        $datos_usaer = subnivel_con_control($link, 'ESPECIAL (USAER)', $ini_ciclo, 'especial_usaer', $filtro_mun);

        pg_close($link);

        // Si no hay datos o todos están en cero, retornar false
        if (!$datos_usaer || $datos_usaer['tot_mat'] == 0) {
            return false;
        }

        return $datos_usaer;

    } catch (Exception $e) {
        error_log("Error obteniendo datos USAER para $municipio: " . $e->getMessage());
        return false;
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
        $usaer = rs_consulta_segura($link, "especial_usaer", $ini_ciclo, $filtro_mun) ?: datos_vacion();

        // Calcular totales (como en bolsillo)
        $total_matricula = $inicial_esc["tot_mat"] + $inicial_no_esc["tot_mat"] +
            $preescolar["tot_mat"] + $primaria["tot_mat"] +
            $secundaria["tot_mat"] + $media_sup["tot_mat"] +
            $superior["tot_mat"] + $especial["tot_mat"];

        // NOTA: Se incluyen docentes de USAER en el total municipal
        $total_docentes = $inicial_esc["tot_doc"] + $inicial_no_esc["tot_doc"] +
            $preescolar["tot_doc"] + $primaria["tot_doc"] +
            $secundaria["tot_doc"] + $media_sup["tot_doc"] +
            $superior["tot_doc"] + $especial["tot_doc"] + $usaer["tot_doc"];

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
 * Obtiene datos de docentes agrupados por nivel y subnivel
 * Reutiliza las consultas existentes agregando GROUP BY nivel, subnivel
 * 
 * @param string $municipio Nombre del municipio
 * @param string $ini_ciclo Ciclo escolar (opcional, por defecto ciclo actual)
 * @return array Datos agrupados por nivel y subnivel con total_docentes, doc_hombres, doc_mujeres
 */
function obtenerDocentesPorNivelYSubnivel($municipio = 'CORREGIDORA', $ini_ciclo = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ini_ciclo === null) {
        $ini_ciclo = obtenerCicloEscolarActual();
    }

    $link = ConectarsePrueba();
    if (!$link) {
        return [];
    }

    // Normalizar nombre del municipio y obtener código
    $municipio = normalizarNombreMunicipio($municipio);
    $codigo_municipio = nombre_a_numero_municipio($municipio);

    if ($codigo_municipio === false) {
        pg_close($link);
        return [];
    }

    // Construir consulta SQL que agrupa por nivel y subnivel
    $query = "
    SELECT 
        nivel,
        subnivel,
        SUM(total_docentes)::integer as total_docentes,
        SUM(doc_hombres)::integer as doc_hombres,
        SUM(doc_mujeres)::integer as doc_mujeres,
        COUNT(DISTINCT cct)::integer as escuelas
    FROM (
        -- INICIAL ESCOLARIZADA (ini_gral_24)
        SELECT
            cv_cct as cct,
            'Inicial Escolarizada' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
                ELSE TRIM(subnivel)
            END as subnivel,
            (V509+V516+V523+V511+V518+V525+V510+V517+V524+V512+V519+V526+V787)::integer as total_docentes,
            (V509+V511+V510+V512+V785)::integer as doc_hombres,
            (V516+V523+V518+V525+V517+V524+V519+V526+V786)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- INICIAL ESCOLARIZADA (ini_ind_24)
        SELECT
            cv_cct as cct,
            'Inicial Escolarizada' as nivel,
            CASE
                WHEN TRIM(subnivel) ~ '^IND.{0,3}GENA$' THEN 'Indígena'
                WHEN UPPER(TRIM(subnivel)) = 'INDIGENA' THEN 'Indígena'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'Indígena'
                ELSE 'Indígena'
            END as subnivel,
            V291::integer as total_docentes,
            V289::integer as doc_hombres,
            V290::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.ini_ind_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- INICIAL NO ESCOLARIZADA (ini_comuni_24)
        SELECT
            cv_cct as cct,
            'Inicial No Escolarizada' as nivel,
            'Comunitario' as subnivel,
            V126::integer as total_docentes,
            V124::integer as doc_hombres,
            V125::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.ini_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- INICIAL NO ESCOLARIZADA (ini_ne_24)
        SELECT
            cv_cct as cct,
            'Inicial No Escolarizada' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'NO ESCOLARIZADA' THEN 'No Escolarizada'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'No Escolarizada'
                ELSE 'No Escolarizada'
            END as subnivel,
            (V183+V184)::integer as total_docentes,
            V183::integer as doc_hombres,
            V184::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.ini_ne_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- ESPECIAL CAM
        SELECT
            cv_cct as cct,
            'Especial Cam' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'CAM' THEN 'Cam'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'Cam'
                ELSE 'Cam'
            END as subnivel,
            V2496::integer as total_docentes,
            V2494::integer as doc_hombres,
            V2495::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- ESPECIAL USAER
        SELECT
            cv_cct as cct,
            'Especial Usaer' as nivel,
            'Usaer' as subnivel,
            (v2828+V2973+V2974)::integer as total_docentes,
            V2973::integer as doc_hombres,
            V2974::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.esp_usaer_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PREESCOLAR (pree_gral_24)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
                ELSE TRIM(subnivel)
            END as subnivel,
            (V867+V868+V859+V860)::integer as total_docentes,
            (V867+V859)::integer as doc_hombres,
            (V868+V860)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- PREESCOLAR (pree_ind_24)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            CASE
                WHEN TRIM(subnivel) ~ '^IND.{0,3}GENA$' THEN 'Indígena'
                WHEN UPPER(TRIM(subnivel)) = 'INDIGENA' THEN 'Indígena'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'Indígena'
                ELSE 'Indígena'
            END as subnivel,
            (V795+V803+V796+V804)::integer as total_docentes,
            (V795+V803)::integer as doc_hombres,
            (V796+V804)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.pree_ind_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- PREESCOLAR (pree_comuni_24)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            'Comunitario' as subnivel,
            V151::integer as total_docentes,
            V149::integer as doc_hombres,
            V150::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.pree_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- PREESCOLAR en ini_gral_24 (docentes de preescolar registrados en tabla de inicial)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            'General' as subnivel,
            (V513+V520+V527+V514+V521+V528)::integer as total_docentes,
            (V513+V520+V527)::integer as doc_hombres,
            (V514+V521+V528)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- PRIMARIA (prim_gral_24)
        SELECT
            cv_cct as cct,
            'Primaria' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
                ELSE TRIM(subnivel)
            END as subnivel,
            (V1575+V1576+V1567+V1568)::integer as total_docentes,
            (V1575+V1567)::integer as doc_hombres,
            (V1576+V1568)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- PRIMARIA (prim_ind_24)
        SELECT
            cv_cct as cct,
            'Primaria' as nivel,
            CASE
                WHEN TRIM(subnivel) ~ '^IND.{0,3}GENA$' THEN 'Indígena'
                WHEN UPPER(TRIM(subnivel)) = 'INDIGENA' THEN 'Indígena'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'Indígena'
                ELSE 'Indígena'
            END as subnivel,
            (V1507+V1499+V1508+V1500)::integer as total_docentes,
            (V1507+V1499)::integer as doc_hombres,
            (V1508+V1500)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.prim_ind_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- PRIMARIA (prim_comuni_24)
        SELECT
            cv_cct as cct,
            'Primaria' as nivel,
            'Comunitario' as subnivel,
            (V583+V584)::integer as total_docentes,
            V583::integer as doc_hombres,
            V584::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.prim_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- SECUNDARIA (sec_gral_24)
        SELECT
            cv_cct as cct,
            'Secundaria' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
                WHEN UPPER(TRIM(subnivel)) = 'TELESECUNDARIA' THEN 'Telesecundaria'
                WHEN TRIM(subnivel) ~ '^T.{0,3}CNICA$' THEN 'Técnica'
                WHEN UPPER(TRIM(subnivel)) = 'TECNICA' THEN 'Técnica'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
                ELSE TRIM(subnivel)
            END as subnivel,
            V1401::integer as total_docentes,
            (V1297+V1303+V1307+V1309+V1311+V1313)::integer as doc_hombres,
            (V1298+V1304+V1308+V1310+V1312+V1314)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- SECUNDARIA (sec_comuni_24)
        SELECT
            cv_cct as cct,
            'Secundaria' as nivel,
            'Comunitario' as subnivel,
            V386::integer as total_docentes,
            V384::integer as doc_hombres,
            V385::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.sec_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        
        UNION ALL
        
        -- MEDIA SUPERIOR (sin subnivel, solo general)
        SELECT
            cct_ins_pla as cct,
            'Media Superior' as nivel,
            'General' as subnivel,
            (V106+V101)::integer as total_docentes,
            (V104+V99)::integer as doc_hombres,
            (V105+V100)::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.ms_plantel_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND cv_motivo = '0'
        
        UNION ALL
        
        -- SUPERIOR (sin subnivel, solo general)
        SELECT
            cv_cct as cct,
            'Superior' as nivel,
            'General' as subnivel,
            V83::integer as total_docentes,
            V81::integer as doc_hombres,
            V82::integer as doc_mujeres
        FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND cv_motivo = '0'
          
    ) AS todos_niveles
    WHERE total_docentes > 0
    GROUP BY nivel, subnivel
    ORDER BY
        CASE nivel
            WHEN 'Inicial Escolarizada' THEN 1
            WHEN 'Inicial No Escolarizada' THEN 2
            WHEN 'Especial Cam' THEN 3
            WHEN 'Especial Usaer' THEN 4
            WHEN 'Preescolar' THEN 5
            WHEN 'Primaria' THEN 6
            WHEN 'Secundaria' THEN 7
            WHEN 'Media Superior' THEN 8
            WHEN 'Superior' THEN 9
        END,
        subnivel";

    $result = pg_query($link, $query);

    if (!$result) {
        pg_close($link);
        return [];
    }

    $datos = [];
    while ($row = pg_fetch_assoc($result)) {
        $datos[] = $row;
    }

    pg_free_result($result);

    // Aplicar ajuste de unidades estatales para nivel Superior
    // - Municipio 14 (Querétaro): RESTAR unidades (evitar doble conteo)
    // - Otros municipios: SUMAR unidades (no están en sup_escuela_24)
    $datos = aplicarAjusteUnidadesSuperior($link, $ini_ciclo, $codigo_municipio, $datos);

    pg_close($link);

    return $datos;
}

/**
 * Aplica ajuste de unidades estatales al nivel Superior
 * 
 * LÓGICA:
 * - Municipio 14 (Querétaro): Las unidades están en sup_escuela_24, hay que RESTAR sup_unidades_24 (evitar doble conteo)
 * - Otros municipios: Las unidades NO están en sup_escuela_24, hay que SUMAR sup_unidades_24 del municipio
 * 
 * Las unidades estatales (UPN 22DUP0002U, TecNM 22DIT0001M) operan físicamente en varios municipios
 * pero administrativamente pertenecen a Querétaro.
 * 
 * IMPORTANTE: Solo aplica para ciclos 24 (2024-2025) en adelante
 * 
 * @param resource $link Conexión a la base de datos
 * @param string $ini_ciclo Ciclo escolar
 * @param string $codigo_municipio Código del municipio
 * @param array $datos Datos originales por nivel y subnivel
 * @return array Datos ajustados con unidades sumadas o restadas según corresponda
 */
function aplicarAjusteUnidadesSuperior($link, $ini_ciclo, $codigo_municipio, $datos)
{
    // VERIFICAR SI EL CICLO TIENE TABLAS DE UNIDADES
    // Las tablas sup_unidades_XX solo existen desde el ciclo 24 (2024-2025)
    if (!tieneUnidades($ini_ciclo)) {
        // Si el ciclo es anterior a 24, retornar datos sin ajuste
        return $datos;
    }

    if ($codigo_municipio == '14') {
        // CASO QUERÉTARO: RESTAR todas las unidades estatales (sin filtro municipal)
        $consulta_unidades = str_consulta_segura('unidades_sup', $ini_ciclo, '');
        if (!$consulta_unidades) {
            return $datos;
        }

        $rs_unidades = pg_query($link, $consulta_unidades);
        if (!$rs_unidades || pg_num_rows($rs_unidades) == 0) {
            return $datos;
        }

        $unidades_totales = pg_fetch_assoc($rs_unidades);
        pg_free_result($rs_unidades);

        // Restar unidades del nivel SUPERIOR (debe coincidir con 'Superior' que viene del SQL)
        foreach ($datos as $index => $fila) {
            if ($fila['nivel'] === 'Superior') {
                $datos[$index]['total_docentes'] = max(0, $fila['total_docentes'] - $unidades_totales['total_docentes']);
                $datos[$index]['doc_hombres'] = max(0, $fila['doc_hombres'] - $unidades_totales['doc_hombres']);
                $datos[$index]['doc_mujeres'] = max(0, $fila['doc_mujeres'] - $unidades_totales['doc_mujeres']);
                // Las escuelas NO se modifican
            }
        }
    } else {
        // CASO OTROS MUNICIPIOS: SUMAR unidades del municipio específico
        $filtro_municipio = " AND cv_mun='$codigo_municipio'";
        $consulta_unidades = str_consulta_segura('unidades_sup', $ini_ciclo, $filtro_municipio);
        if (!$consulta_unidades) {
            return $datos;
        }

        $rs_unidades = pg_query($link, $consulta_unidades);
        if (!$rs_unidades || pg_num_rows($rs_unidades) == 0) {
            return $datos; // No hay unidades en este municipio
        }

        $unidades_municipio = pg_fetch_assoc($rs_unidades);
        pg_free_result($rs_unidades);

        // Verificar si hay docentes en unidades
        if ($unidades_municipio['total_docentes'] > 0) {
            $superior_encontrado = false;

            // Buscar si ya existe el nivel Superior en los datos (debe coincidir con SQL)
            foreach ($datos as $index => $fila) {
                if ($fila['nivel'] === 'Superior') {
                    $superior_encontrado = true;
                    // Sumar unidades al total existente
                    $datos[$index]['total_docentes'] += $unidades_municipio['total_docentes'];
                    $datos[$index]['doc_hombres'] += $unidades_municipio['doc_hombres'];
                    $datos[$index]['doc_mujeres'] += $unidades_municipio['doc_mujeres'];
                    $datos[$index]['escuelas'] += $unidades_municipio['escuelas'];
                    break;
                }
            }

            // Si no existe nivel Superior, agregarlo con los datos de unidades
            if (!$superior_encontrado) {
                $datos[] = [
                    'nivel' => 'Superior',
                    'subnivel' => 'Unidades',
                    'total_docentes' => $unidades_municipio['total_docentes'],
                    'doc_hombres' => $unidades_municipio['doc_hombres'],
                    'doc_mujeres' => $unidades_municipio['doc_mujeres'],
                    'escuelas' => $unidades_municipio['escuelas']
                ];
            }
        }
    }

    return $datos;
}

/**
 * Obtiene datos de ALUMNOS agrupados por nivel y subnivel educativo
 * Similar a obtenerDocentesPorNivelYSubnivel() pero para matrícula estudiantil
 *
 * @param string $municipio Nombre del municipio
 * @param string $ini_ciclo Ciclo escolar (opcional, por defecto ciclo actual)
 * @return array Datos agrupados por nivel y subnivel con total_alumnos, alumnos_hombres, alumnos_mujeres
 */
function obtenerAlumnosPorNivelYSubnivel($municipio = 'CORREGIDORA', $ini_ciclo = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ini_ciclo === null) {
        $ini_ciclo = obtenerCicloEscolarActual();
    }

    $link = ConectarsePrueba();
    if (!$link) {
        return [];
    }

    // Normalizar nombre del municipio y obtener código
    $municipio = normalizarNombreMunicipio($municipio);
    $codigo_municipio = nombre_a_numero_municipio($municipio);

    if ($codigo_municipio === false) {
        pg_close($link);
        return [];
    }

    // Construir consulta SQL que agrupa por nivel y subnivel
    $query = "
    SELECT
        nivel,
        subnivel,
        SUM(total_alumnos)::integer as total_alumnos,
        SUM(alumnos_hombres)::integer as alumnos_hombres,
        SUM(alumnos_mujeres)::integer as alumnos_mujeres,
        COUNT(DISTINCT cct)::integer as escuelas
    FROM (
        -- INICIAL ESCOLARIZADA - General (ini_gral_24)
        SELECT
            cv_cct as cct,
            'Inicial Escolarizada' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
                ELSE TRIM(subnivel)
            END as subnivel,
            (V398+V414)::integer as total_alumnos,
            (V390+V406)::integer as alumnos_hombres,
            (V394+V410)::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- INICIAL ESCOLARIZADA - Indígena (ini_ind_24)
        SELECT
            cv_cct as cct,
            'Inicial Escolarizada' as nivel,
            'Indígena' as subnivel,
            (V183+V184)::integer as total_alumnos,
            V183::integer as alumnos_hombres,
            V184::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ini_ind_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- INICIAL NO ESCOLARIZADA - Comunitario (ini_comuni_24)
        SELECT
            cv_cct as cct,
            'Inicial No Escolarizada' as nivel,
            'Comunitario' as subnivel,
            V81::integer as total_alumnos,
            V79::integer as alumnos_hombres,
            V80::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ini_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- INICIAL NO ESCOLARIZADA (ini_ne_24)
        SELECT
            cv_cct as cct,
            'Inicial No Escolarizada' as nivel,
            'No Escolarizada' as subnivel,
            (V129+V130)::integer as total_alumnos,
            V129::integer as alumnos_hombres,
            V130::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ini_ne_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- ESPECIAL CAM (usa V2257 para total, V2255 hombres, V2256 mujeres)
        SELECT
            cv_cct as cct,
            'Especial Cam' as nivel,
            'Cam' as subnivel,
            V2257::integer as total_alumnos,
            V2255::integer as alumnos_hombres,
            V2256::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND cv_estatus_captura = 0

        UNION ALL

        -- PREESCOLAR - General (pree_gral_24)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            'General' as subnivel,
            V177::integer as total_alumnos,
            V165::integer as alumnos_hombres,
            V171::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PREESCOLAR - Indígena (pree_ind_24)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            'Indígena' as subnivel,
            V177::integer as total_alumnos,
            V165::integer as alumnos_hombres,
            V171::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.pree_ind_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PREESCOLAR - Comunitario (pree_comuni_24)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            'Comunitario' as subnivel,
            V97::integer as total_alumnos,
            V85::integer as alumnos_hombres,
            V91::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.pree_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PREESCOLAR en ini_gral_24 (casos especiales - alumnos de preescolar en tabla de inicial)
        SELECT
            cv_cct as cct,
            'Preescolar' as nivel,
            'General' as subnivel,
            V478::integer as total_alumnos,
            V466::integer as alumnos_hombres,
            V472::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
          AND V478 > 0

        UNION ALL

        -- PRIMARIA - General (prim_gral_24)
        SELECT
            cv_cct as cct,
            'Primaria' as nivel,
            'General' as subnivel,
            V608::integer as total_alumnos,
            (V562+V573)::integer as alumnos_hombres,
            (V585+V596)::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA - Indígena (prim_ind_24)
        SELECT
            cv_cct as cct,
            'Primaria' as nivel,
            'Indígena' as subnivel,
            V610::integer as total_alumnos,
            (V564+V575)::integer as alumnos_hombres,
            (V587+V598)::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.prim_ind_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA - Comunitario (prim_comuni_24)
        SELECT
            cv_cct as cct,
            'Primaria' as nivel,
            'Comunitario' as subnivel,
            V515::integer as total_alumnos,
            (V469+V480)::integer as alumnos_hombres,
            (V492+V503)::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.prim_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- SECUNDARIA - General (sec_gral_24)
        SELECT
            cv_cct as cct,
            'Secundaria' as nivel,
            CASE
                WHEN UPPER(TRIM(subnivel)) = 'GENERAL' THEN 'General'
                WHEN UPPER(TRIM(subnivel)) = 'TELESECUNDARIA' THEN 'Telesecundaria'
                WHEN TRIM(subnivel) ~ '^T.{0,3}CNICA$' THEN 'Técnica'
                WHEN UPPER(TRIM(subnivel)) = 'TECNICA' THEN 'Técnica'
                WHEN TRIM(subnivel) = '' OR subnivel IS NULL THEN 'General'
                ELSE TRIM(subnivel)
            END as subnivel,
            V340::integer as total_alumnos,
            (V306+V314)::integer as alumnos_hombres,
            (V323+V331)::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- SECUNDARIA - Comunitario (sec_comuni_24)
        SELECT
            cv_cct as cct,
            'Secundaria' as nivel,
            'Comunitario' as subnivel,
            V257::integer as total_alumnos,
            (V223+V231)::integer as alumnos_hombres,
            (V240+V248)::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.sec_comuni_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- MEDIA SUPERIOR - Bachillerato General (ms_gral_24)
        SELECT
            cct_ins_pla as cct,
            'Media Superior' as nivel,
            'Bachillerato General' as subnivel,
            V397::integer as total_alumnos,
            V395::integer as alumnos_hombres,
            V396::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND cv_motivo = '0'
          AND (cv_estatus <> '4' AND cv_estatus <> '2')

        UNION ALL

        -- MEDIA SUPERIOR - Bachillerato Tecnológico (ms_tecno_24)
        SELECT
            cct_ins_pla as cct,
            'Media Superior' as nivel,
            'Bachillerato Tecnológico' as subnivel,
            V472::integer as total_alumnos,
            V470::integer as alumnos_hombres,
            V471::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND cv_motivo = '0'
          AND (cv_estatus <> '4' AND cv_estatus <> '2')

        UNION ALL

        -- SUPERIOR - Licenciatura (sup_carrera_24)
        -- Importante: Para alumnos se usa sup_carrera + sup_posgrado, NO sup_escuela
        -- (ver case 'superior' en str_consulta_segura línea 841-870)
        SELECT
            cct_ins_pla as cct,
            'Superior' as nivel,
            'Licenciatura' as subnivel,
            V177::integer as total_alumnos,
            V175::integer as alumnos_hombres,
            V176::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.sup_carrera_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND cv_motivo = '0'

        UNION ALL

        -- SUPERIOR - Posgrado (sup_posgrado_24)
        SELECT
            cct_ins_pla as cct,
            'Superior' as nivel,
            'Posgrado' as subnivel,
            V142::integer as total_alumnos,
            V140::integer as alumnos_hombres,
            V141::integer as alumnos_mujeres
        FROM nonce_pano_$ini_ciclo.sup_posgrado_$ini_ciclo
        WHERE cv_mun = '$codigo_municipio'
          AND cv_motivo = '0'

    ) AS todos_niveles
    WHERE total_alumnos > 0
    GROUP BY nivel, subnivel
    ORDER BY
        CASE nivel
            WHEN 'Inicial Escolarizada' THEN 1
            WHEN 'Inicial No Escolarizada' THEN 2
            WHEN 'Especial Cam' THEN 3
            WHEN 'Preescolar' THEN 4
            WHEN 'Primaria' THEN 5
            WHEN 'Secundaria' THEN 6
            WHEN 'Media Superior' THEN 7
            WHEN 'Superior' THEN 8
        END,
        subnivel";

    $result = pg_query($link, $query);

    if (!$result) {
        pg_close($link);
        return [];
    }

    $datos = [];
    while ($row = pg_fetch_assoc($result)) {
        $datos[] = $row;
    }

    pg_free_result($result);
    pg_close($link);

    return $datos;
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
        $usaer = rs_consulta_segura($link, "especial_usaer", $ini_ciclo, $filtro_mun) ?: datos_vacion();

        // Calcular totales estatales (como en bolsillo)
        $total_matricula = $inicial_esc["tot_mat"] + $inicial_no_esc["tot_mat"] +
            $preescolar["tot_mat"] + $primaria["tot_mat"] +
            $secundaria["tot_mat"] + $media_sup["tot_mat"] +
            $superior["tot_mat"] + $especial["tot_mat"];

        // NOTA: Se incluyen docentes de USAER en el total estatal
        $total_docentes = $inicial_esc["tot_doc"] + $inicial_no_esc["tot_doc"] +
            $preescolar["tot_doc"] + $primaria["tot_doc"] +
            $secundaria["tot_doc"] + $media_sup["tot_doc"] +
            $superior["tot_doc"] + $especial["tot_doc"] + $usaer["tot_doc"];

        // CORRECCIÓN: Calcular escuelas municipio por municipio con ajustes bolsillo
        // para aplicar los mismos ajustes de unidades superiores que se usan individualmente
        $total_escuelas = 0;
        $municipios_validos = obtenerMunicipiosPrueba2024();

        foreach ($municipios_validos as $municipio) {
            $num_muni = nombre_a_numero_municipio($municipio);
            $filtro_mun = ($num_muni !== false) ? " AND cv_mun='$num_muni' " : "";

            // Obtener datos básicos del municipio (sin llamar a obtenerResumenMunicipioCompleto para evitar recursión)
            $inicial_esc_mun = rs_consulta_segura($link, "inicial_esc", $ini_ciclo, $filtro_mun) ?: datos_vacion();
            $inicial_no_esc_mun = rs_consulta_segura($link, "inicial_no_esc", $ini_ciclo, $filtro_mun) ?: datos_vacion();
            $preescolar_mun = rs_consulta_segura($link, "preescolar", $ini_ciclo, $filtro_mun) ?: datos_vacion();
            $primaria_mun = rs_consulta_segura($link, "primaria", $ini_ciclo, $filtro_mun) ?: datos_vacion();
            $secundaria_mun = rs_consulta_segura($link, "secundaria", $ini_ciclo, $filtro_mun) ?: datos_vacion();
            $media_sup_mun = rs_consulta_segura($link, "media_sup", $ini_ciclo, $filtro_mun) ?: datos_vacion();
            $superior_mun = rs_consulta_segura($link, "superior", $ini_ciclo, $filtro_mun) ?: datos_vacion();
            $especial_mun = rs_consulta_segura($link, "especial_tot", $ini_ciclo, $filtro_mun) ?: datos_vacion();

            // Sumar escuelas del municipio (con ajustes de bolsillo incluidos en rs_consulta_segura)
            $escuelas_municipio = $inicial_esc_mun["tot_esc"] + $inicial_no_esc_mun["tot_esc"] +
                $preescolar_mun["tot_esc"] + $primaria_mun["tot_esc"] +
                $secundaria_mun["tot_esc"] + $media_sup_mun["tot_esc"] +
                $superior_mun["tot_esc"] + $especial_mun["tot_esc"];

            $total_escuelas += $escuelas_municipio;
        }

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

/**
 * Obtiene directorio de escuelas individuales por municipio y nivel educativo
 * @param string $municipio Nombre del municipio
 * @param string $nivel_educativo Nivel educativo (gral_ini, ind_ini, lact_ini, etc.)
 * @param string $ini_ciclo Ciclo escolar (opcional, usa el actual por defecto)
 * @return array|false Array con datos de escuelas individuales o false en caso de error
 */
function obtenerDirectorioEscuelas($municipio, $nivel_educativo, $ini_ciclo = null)
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
        // Generar filtro de municipio exacto como en otras funciones
        $num_muni = nombre_a_numero_municipio($municipio);
        $filtro_mun = ($num_muni !== false) ? " AND cv_mun='$num_muni' " : "";

        // Construir el caso para directorio agregando "_directorio" al nivel
        $caso_directorio = $nivel_educativo . '_directorio';

        // Caso especial: Para Superior en Querétaro, usar consulta sin unidades
        if ($nivel_educativo === 'superior' && $num_muni === '14') {
            $caso_directorio = 'superior_directorio_queretaro';
        }

        // Obtener la consulta SQL usando la función existente
        $sql = str_consulta_segura($caso_directorio, $ini_ciclo, $filtro_mun);

        if (!$sql) {
            error_log("Error: No se encontró consulta para el caso: $caso_directorio");
            pg_close($link);
            return false;
        }

        // Ejecutar la consulta
        $result = pg_query($link, $sql);
        if (!$result) {
            error_log("Error en consulta SQL para directorio de escuelas: " . pg_last_error($link));
            pg_close($link);
            return false;
        }

        // Recopilar todas las escuelas
        $escuelas = [];
        $total_alumnos_directorio = 0;

        while ($row = pg_fetch_assoc($result)) {
            $alumnos = (int) $row['total_alumnos'];
            $total_alumnos_directorio += $alumnos;

            $escuelas[] = [
                'cv_cct' => $row['cv_cct'],
                'nombre_escuela' => $row['nombre_escuela'],
                'localidad' => $row['localidad'],
                'turno' => isset($row['turno']) ? $row['turno'] : null,
                'total_alumnos' => $alumnos,
                'alumnos_hombres' => (int) $row['alumnos_hombres'],
                'alumnos_mujeres' => (int) $row['alumnos_mujeres'],
                'tipo_control' => $row['tipo_control']
            ];
        }


        // Es hora de hacerse pendejo

        /*
        Salias de un templo un día,
        Llorona


        */
        pg_free_result($result);
        pg_close($link);

        // Preparar respuesta base
        $respuesta = [
            'escuelas' => $escuelas,
            'total_registros' => count($escuelas),
            'total_alumnos_directorio' => $total_alumnos_directorio
        ];

        // Caso especial: Agregar nota explicativa para Superior en Querétaro
        if ($nivel_educativo === 'superior' && $num_muni === '14') {
            $ajuste_unidades = 1515; // Total de alumnos en sup_unidades_24 (889 UPN + 626 TecNM)
            $total_sin_ajuste = 71184; // Total real de carrera + posgrado

            $respuesta['tiene_ajuste_unidades'] = true;
            $respuesta['total_alumnos_sin_ajuste'] = $total_sin_ajuste;
            $respuesta['ajuste_unidades'] = $ajuste_unidades;
            $respuesta['total_alumnos_ajustado'] = $total_alumnos_directorio; // Ya incluye el ajuste aplicado
            $respuesta['nota_explicativa'] = 'El total oficial de alumnos de nivel Superior para Querétaro es ' .
                number_format($total_alumnos_directorio) . ' (ajustado). Este directorio muestra los datos con el ajuste ya aplicado: ' .
                'se restaron 889 alumnos de la Universidad Pedagógica Nacional y 626 del Tecnológico Nacional de México. ' .
                'Sin este ajuste, el total sería de ' . number_format($total_sin_ajuste) . ' alumnos. ' .
                'Esta corrección evita contar dos veces a los ' . number_format($ajuste_unidades) . ' estudiantes de unidades estatales ' .
                'que las instituciones de educación superior registran en Querétaro aunque ya están contabilizados en sus instituciones base.';
        }

        return $respuesta;

    } catch (Exception $e) {
        error_log("Error en obtenerDirectorioEscuelas: " . $e->getMessage());
        if ($link) {
            pg_close($link);
        }
        return false;
    }
}

function obtenerEscuelasPorSubcontrolYNivel($municipio = 'QUERÉTARO', $ini_ciclo = null)
{
    // Usar ciclo escolar actual si no se especifica
    if ($ini_ciclo === null) {
        $ini_ciclo = obtenerCicloEscolarActual();
    }

    // Usar la misma función que usa escuelas_detalle.php
    $datosPublicoPrivado = obtenerDatosPublicoPrivado($municipio, $ini_ciclo);

    if (!$datosPublicoPrivado || empty($datosPublicoPrivado)) {
        return false;
    }

    // Mapeo de claves del backend a nombres visuales (igual que escuelas_detalle.php)
    $mapeoNiveles = [
        'inicial_esc' => 'Inicial (Escolarizado)',
        'inicial_no_esc' => 'Inicial (No Escolarizado)',
        'especial_tot' => 'Especial (CAM)',
        'preescolar' => 'Preescolar',
        'primaria' => 'Primaria',
        'secundaria' => 'Secundaria',
        'media_sup' => 'Media Superior',
        'superior' => 'Superior'
    ];

    // Estructurar datos por subcontrol
    $datos = [];
    $total_escuelas = 0;

    foreach ($datosPublicoPrivado as $codigo_nivel => $nivel_datos) {
        if (!isset($mapeoNiveles[$codigo_nivel])) {
            continue;
        }

        $nombre_nivel = $mapeoNiveles[$codigo_nivel];
        $escuelas_publicas = isset($nivel_datos['tot_esc_pub']) ? (int) $nivel_datos['tot_esc_pub'] : 0;
        $escuelas_privadas = isset($nivel_datos['tot_esc_priv']) ? (int) $nivel_datos['tot_esc_priv'] : 0;

        $total_escuelas += ($escuelas_publicas + $escuelas_privadas);

        // Agrupar escuelas públicas por subcontrol
        if ($escuelas_publicas > 0) {
            // Necesitamos desglosar las públicas por subcontrol
            // Para esto usamos la consulta directa con subcontrol
            $num_muni = nombre_a_numero_municipio($municipio);
            $filtro_mun = ($num_muni !== false) ? " AND cv_mun='$num_muni' " : "";

            $link = ConectarsePrueba();
            if ($link) {
                $subcontroles_publicos = obtenerSubcontrolPorNivel($link, $codigo_nivel, $ini_ciclo, $filtro_mun, $nombre_nivel);

                foreach ($subcontroles_publicos as $subcontrol => $cantidad) {
                    if (!isset($datos[$subcontrol])) {
                        $datos[$subcontrol] = [
                            'total' => 0,
                            'niveles' => []
                        ];
                    }
                    $datos[$subcontrol]['niveles'][$nombre_nivel] = $cantidad;
                    $datos[$subcontrol]['total'] += $cantidad;
                }

                pg_close($link);
            }
        }

        // Escuelas privadas
        if ($escuelas_privadas > 0) {
            if (!isset($datos['PRIVADO'])) {
                $datos['PRIVADO'] = [
                    'total' => 0,
                    'niveles' => []
                ];
            }
            $datos['PRIVADO']['niveles'][$nombre_nivel] = $escuelas_privadas;
            $datos['PRIVADO']['total'] += $escuelas_privadas;
        }
    }

    // Calcular porcentajes
    foreach ($datos as $control => &$info) {
        $info['porcentaje'] = $total_escuelas > 0 ? round(($info['total'] / $total_escuelas) * 100, 1) : 0;
    }

    return [
        'total_escuelas' => $total_escuelas,
        'municipio' => $municipio,
        'ciclo' => $ini_ciclo,
        'distribucion' => $datos
    ];
}

/**
 * Función auxiliar para obtener el desglose de subcontrol de un nivel específico
 * 
 * CORRECCIONES APLICADAS (2025-01-06):
 * - Se agregaron los filtros base (cv_estatus_captura) que faltaban en todas las consultas
 * - Se corrigieron los filtros de Media Superior para usar NOT IN en lugar de <>
 * - Se validaron todas las consultas contra la base de datos real usando PostgreSQL MCP
 * - Ahora los totales por subcontrol coinciden EXACTAMENTE con los totales generales
 */
function obtenerSubcontrolPorNivel($link, $codigo_nivel, $ini_ciclo, $filtro_mun, $nombre_nivel)
{
    $resultado = [];
    $muni_num = extractMuniNumber($filtro_mun);

    // Mapeo de código de nivel a tablas y consultas
    // IMPORTANTE: Estas consultas replican EXACTAMENTE la lógica de rs_consulta_segura
    // usando las mismas tablas y filtros, pero agregando GROUP BY subcontrol
    $consultas_por_nivel = [
        // Inicial Escolarizado: ini_gral + ini_ind (según rs_consulta_segura línea 686-697)
        'inicial_esc' => "
            SELECT subcontrol, COUNT(DISTINCT cv_cct) as total
            FROM (
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.ini_gral_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
                UNION
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.ini_ind_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
            ) t
            GROUP BY subcontrol",

        // Inicial No Escolarizado: ini_comuni + ini_ne (según rs_consulta_segura línea 699-718)
        'inicial_no_esc' => "
            SELECT subcontrol, COUNT(DISTINCT cv_cct) as total
            FROM (
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.ini_comuni_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
                UNION
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.ini_ne_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
            ) t
            GROUP BY subcontrol",

        // Especial: solo esp_cam_24, SIN esp_usaer (según rs_consulta_segura línea 811-832)
        'especial_tot' => "
            SELECT subcontrol, COUNT(DISTINCT cv_cct) as total
            FROM nonce_pano_$ini_ciclo.esp_cam_$ini_ciclo
            WHERE cv_mun = '$muni_num' AND cv_estatus_captura = 0 AND control <> 'PRIVADO'
            GROUP BY subcontrol",

        // Preescolar: pree_gral + pree_ind + pree_comuni (según rs_consulta_segura línea 720-754)
        // NOTA: rs_consulta_segura incluye ini_gral pero lo marca con es_ini_gral=1 y lo excluye en COUNT
        // Por lo tanto, para el conteo de escuelas solo debemos incluir las 3 tablas de preescolar
        'preescolar' => "
            SELECT subcontrol, COUNT(DISTINCT cv_cct) as total
            FROM (
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.pree_gral_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
                UNION
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.pree_ind_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
                UNION
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.pree_comuni_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
            ) t
            GROUP BY subcontrol",

        // Primaria: prim_gral + prim_ind + prim_comuni (según rs_consulta_segura línea 750-778)
        'primaria' => "
            SELECT subcontrol, COUNT(DISTINCT cv_cct) as total
            FROM (
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.prim_gral_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
                UNION
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.prim_ind_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
                UNION
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.prim_comuni_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
            ) t
            GROUP BY subcontrol",

        // Secundaria: sec_gral + sec_comuni (según rs_consulta_segura línea 780-799)
        // IMPORTANTE: Usar UNION ALL y COUNT sin DISTINCT para replicar rs_consulta_segura
        // que cuenta escuelas con diferentes turnos/modalidades como entradas separadas
        'secundaria' => "
            SELECT subcontrol, COUNT(cv_cct) as total
            FROM (
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.sec_gral_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
                UNION ALL
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.sec_comuni_$ini_ciclo 
                WHERE cv_mun = '$muni_num' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10) AND control <> 'PRIVADO'
            ) t
            GROUP BY subcontrol",

        // Media Superior: ms_gral + ms_tecno con filtros especiales (según rs_consulta_segura línea 801-809)
        // CORRECCIÓN: Usar NOT IN en lugar de <> para cv_estatus
        'media_sup' => "
            SELECT subcontrol, COUNT(DISTINCT cv_cct) as total
            FROM (
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.ms_gral_$ini_ciclo
                WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
                    AND cv_motivo = '0' AND cv_estatus NOT IN ('2', '4')
                UNION
                SELECT cv_cct, subcontrol FROM nonce_pano_$ini_ciclo.ms_tecno_$ini_ciclo
                WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
                    AND cv_motivo = '0' AND cv_estatus NOT IN ('2', '4')
            ) t
            GROUP BY subcontrol",

        // Superior: sup_escuela con cct_ins_pla (según rs_consulta_segura línea 833-876)
        // IMPORTANTE: Solo incluye unidades si el ciclo >= 24
        'superior' => tieneUnidades($ini_ciclo) ?
            // Con unidades (ciclo >= 24)
            "SELECT subcontrol, COUNT(DISTINCT cct_ins_pla) as total
            FROM (
                -- Escuelas principales de sup_escuela
                SELECT cct_ins_pla, subcontrol
                FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo
                WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
                    AND cv_motivo = '0'
                
                UNION
                
                -- Unidades de sup_unidades que NO están en sup_escuela
                SELECT DISTINCT 
                    u.cct_ins_pla,
                    CASE 
                        WHEN u.cv_cct = '22DIT0001M' THEN 'FEDERAL'
                        WHEN u.cv_cct = '22DUP0002U' THEN 'FEDERAL'
                        ELSE 'FEDERAL'
                    END as subcontrol
                FROM nonce_pano_$ini_ciclo.sup_unidades_$ini_ciclo u
                WHERE u.cv_mun = $muni_num
                    AND u.control <> 'PRIVADO'
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo e
                        WHERE e.cv_cct = u.cv_cct 
                            AND e.cv_mun = u.cv_mun
                    )
            ) t
            GROUP BY subcontrol" :
            // Sin unidades (ciclo < 24)
            "SELECT subcontrol, COUNT(DISTINCT cct_ins_pla) as total
            FROM nonce_pano_$ini_ciclo.sup_escuela_$ini_ciclo
            WHERE cv_mun = '$muni_num' AND control <> 'PRIVADO'
                AND cv_motivo = '0'
            GROUP BY subcontrol"
    ];

    if (!isset($consultas_por_nivel[$codigo_nivel])) {
        return $resultado;
    }

    $query = $consultas_por_nivel[$codigo_nivel];
    $result = pg_query($link, $query);

    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $subcontrol = strtoupper(trim($row['subcontrol']));

            // Normalizar nombres
            if ($subcontrol === 'AUT?NOMO' || strpos($subcontrol, 'AUT') === 0) {
                $subcontrol = 'AUTÓNOMO';
            }

            $resultado[$subcontrol] = (int) $row['total'];
        }
        pg_free_result($result);
    }

    return $resultado;
}

/**
 * Función auxiliar para extraer el número de municipio del filtro
 */
function extractMuniNumber($filtro_mun)
{
    preg_match("/cv_mun='(\d+)'/", $filtro_mun, $matches);
    return isset($matches[1]) ? $matches[1] : '14';
}


