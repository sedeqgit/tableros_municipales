<?php
/**
 * =============================================================================
 * CONEXIÓN DE PRUEBA PARA ESQUEMA 2024
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Este archivo contiene las consultas actualizadas al esquema 2024 para pruebas
 * específicas de consultas de docentes, escuelas, matrícula de alumnos y municipios.
 * 
 * @author Sistema SEDEQ
 * @version 1.1
 * @since 2024
 */

// =============================================================================
// CONFIGURACIÓN DE CONEXIÓN
// =============================================================================

/**
 * Establece conexión a PostgreSQL usando la misma configuración que conexion.php
 * 
 * @return resource|false Conexión a PostgreSQL o false en caso de error
 */
function ConectarsePrueba() 
{
    // Verificar si las funciones de PostgreSQL están disponibles
    if (!function_exists('pg_connect')) {
        error_log('SEDEQ Prueba: Extensiones PostgreSQL no disponibles en el servidor');
        return false;
    }
    
    try {
        // Usar la misma configuración que conexion.php
        $connectionString = "host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres options='--client_encoding=LATIN1'";
        
        $conn = pg_connect($connectionString);
        
        if (!$conn) {
            error_log('SEDEQ Prueba: Error de conexión a PostgreSQL - ' . pg_last_error());
            return false;
        }
        
        return $conn;
        
    } catch (Exception $e) {
        error_log('SEDEQ Prueba: Excepción en conexión: ' . $e->getMessage());
        return false;
    }
}

// =============================================================================
// CONSULTA DE DOCENTES - ESQUEMA 2024
// =============================================================================

/**
 * Obtiene datos consolidados de escuelas y alumnos usando la consulta proporcionada
 * 
 * @param string $municipio Nombre del municipio (opcional, por defecto 'CORREGIDORA')
 * @return array Información completa de escuelas y alumnos por tipo educativo
 */
function obtenerDatosEducativosPrueba2024($municipio = 'CORREGIDORA')
{
    $conn = ConectarsePrueba();
    if (!$conn) {
        return ['error' => 'No se pudo conectar a la base de datos'];
    }
    
    try {
        // Consulta exacta proporcionada por el usuario
        $query = "
        WITH datos_alumnos AS (
            -- INICIAL ESCOLARIZADO
            SELECT 'Inicial (Escolarizado)' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v390 + v406 + v394 + v410), 0) as alumnos
            FROM nonce_pano_24.ini_gral_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Inicial (Escolarizado)' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v183 + v184), 0) as alumnos
            FROM nonce_pano_24.ini_ind_24
            WHERE cv_estatus_captura = 0
                AND c_nom_mun = $1
            UNION ALL
            -- INICIAL NO ESCOLARIZADO
            SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v129 + v130), 0) as alumnos
            FROM nonce_pano_24.ini_ne_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Inicial (No Escolarizado)' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v79 + v80), 0) as alumnos
            FROM nonce_pano_24.ini_comuni_24
            WHERE cv_estatus_captura = 0
                AND c_nom_mun = $1
            UNION ALL
            -- CAM (ESPECIAL)
            SELECT 'Especial (CAM)' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v2264), 0) as alumnos
            FROM nonce_pano_24.esp_cam_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            -- PREESCOLAR
            SELECT 'Preescolar' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v177), 0) as alumnos
            FROM nonce_pano_24.pree_gral_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Preescolar' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v177), 0) as alumnos
            FROM nonce_pano_24.pree_ind_24
            WHERE cv_estatus_captura = 0
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Preescolar' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v97), 0) as alumnos
            FROM nonce_pano_24.pree_comuni_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            -- PRIMARIA
            SELECT 'Primaria' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v608), 0) as alumnos
            FROM nonce_pano_24.prim_gral_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Primaria' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v610), 0) as alumnos
            FROM nonce_pano_24.prim_ind_24
            WHERE cv_estatus_captura = 0
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Primaria' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v515), 0) as alumnos
            FROM nonce_pano_24.prim_comuni_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            -- SECUNDARIA
            SELECT 'Secundaria' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v340), 0) as alumnos
            FROM nonce_pano_24.sec_gral_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Secundaria' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v257), 0) as alumnos
            FROM nonce_pano_24.sec_comuni_24
            WHERE (
                    cv_estatus_captura = 0
                    OR cv_estatus_captura = 10
                )
                AND c_nom_mun = $1
            UNION ALL
            -- MEDIA SUPERIOR
            SELECT 'Media Superior' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v397), 0) as alumnos
            FROM nonce_pano_24.ms_gral_24
            WHERE c_nom_mun = $1
            UNION ALL
            SELECT 'Media Superior' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v472), 0) as alumnos
            FROM nonce_pano_24.ms_tecno_24
            WHERE c_nom_mun = $1
            UNION ALL
            -- SUPERIOR
            SELECT 'Superior' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v177), 0) as alumnos
            FROM nonce_pano_24.sup_carrera_24
            WHERE cv_motivo = 0
                AND c_nom_mun = $1
            UNION ALL
            SELECT 'Superior' as tipo_educativo,
                COUNT(DISTINCT cv_cct) as escuelas,
                COALESCE(SUM(v142), 0) as alumnos
            FROM nonce_pano_24.sup_posgrado_24
            WHERE cv_motivo = 0
                AND c_nom_mun = $1
        )
        SELECT tipo_educativo,
            SUM(escuelas) as escuelas,
            SUM(alumnos) as alumnos
        FROM datos_alumnos
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
            END";
        
        $result = pg_query_params($conn, $query, array($municipio));
        $datos = [];
        $totalEscuelas = 0;
        $totalAlumnos = 0;
        
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $datos[] = [
                    'tipo_educativo' => $row['tipo_educativo'],
                    'escuelas' => (int)$row['escuelas'],
                    'alumnos' => (int)$row['alumnos']
                ];
                $totalEscuelas += (int)$row['escuelas'];
                $totalAlumnos += (int)$row['alumnos'];
            }
            pg_free_result($result);
        }
        
        pg_close($conn);
        
        return [
            'municipio' => $municipio,
            'total_escuelas' => $totalEscuelas,
            'total_alumnos' => $totalAlumnos,
            'datos' => $datos,
            'fecha_consulta' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        pg_close($conn);
        return ['error' => 'Error en consulta de datos educativos: ' . $e->getMessage()];
    }
}

// =============================================================================
// CONSULTA DE ESCUELAS - ESQUEMA 2024
// =============================================================================

/**
 * Obtiene la matrícula total de alumnos por municipio usando el esquema 2024
 * Basado en las consultas reales del archivo conexion.php
 * 
 * @param string $municipio Nombre del municipio (opcional, por defecto 'CORREGIDORA')
 * @return array Información de matrícula por nivel educativo
 */
function obtenerMatriculaPrueba2024($municipio = 'CORREGIDORA')
{
    $conn = ConectarsePrueba();
    if (!$conn) {
        return ['error' => 'No se pudo conectar a la base de datos'];
    }
    
    try {
        // Consulta basada exactamente en las columnas v* de conexion.php
        $query = "
        WITH datos_alumnos AS (
            -- INICIAL ESCOLARIZADO
            SELECT 'Inicial' as nivel,
                COALESCE(SUM(v390 + v406 + v394 + v410), 0) as total_alumnos
            FROM nonce_pano_24.ini_gral_24 
            WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10) 
                AND c_nom_mun = $1
            
            UNION ALL
            
            SELECT 'Inicial' as nivel,
                COALESCE(SUM(v183 + v184), 0) as total_alumnos
            FROM nonce_pano_24.ini_ind_24
            WHERE cv_estatus_captura = 0
                AND c_nom_mun = $1

            UNION ALL

            -- PREESCOLAR
            SELECT 'Preescolar' as nivel,
                COALESCE(SUM(v177), 0) as total_alumnos
            FROM nonce_pano_24.pree_gral_24
            WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                AND c_nom_mun = $1

            UNION ALL

            SELECT 'Preescolar' as nivel,
                COALESCE(SUM(v177), 0) as total_alumnos
            FROM nonce_pano_24.pree_ind_24
            WHERE cv_estatus_captura = 0
                AND c_nom_mun = $1

            UNION ALL

            SELECT 'Preescolar' as nivel,
                COALESCE(SUM(v97), 0) as total_alumnos
            FROM nonce_pano_24.pree_comuni_24
            WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                AND c_nom_mun = $1

            UNION ALL

            -- PRIMARIA
            SELECT 'Primaria' as nivel,
                COALESCE(SUM(v608), 0) as total_alumnos
            FROM nonce_pano_24.prim_gral_24
            WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                AND c_nom_mun = $1

            UNION ALL

            SELECT 'Primaria' as nivel,
                COALESCE(SUM(v610), 0) as total_alumnos
            FROM nonce_pano_24.prim_ind_24
            WHERE cv_estatus_captura = 0
                AND c_nom_mun = $1

            UNION ALL

            SELECT 'Primaria' as nivel,
                COALESCE(SUM(v384), 0) as total_alumnos
            FROM nonce_pano_24.prim_comuni_24
            WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                AND c_nom_mun = $1

            UNION ALL

            -- SECUNDARIA
            SELECT 'Secundaria' as nivel,
                COALESCE(SUM(v340), 0) as total_alumnos
            FROM nonce_pano_24.sec_gral_24
            WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                AND c_nom_mun = $1

            UNION ALL

            SELECT 'Secundaria' as nivel,
                COALESCE(SUM(v257), 0) as total_alumnos
            FROM nonce_pano_24.sec_comuni_24
            WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                AND c_nom_mun = $1

            UNION ALL

            -- MEDIA SUPERIOR
            SELECT 'Media Superior' as nivel,
                COALESCE(SUM(v397), 0) as total_alumnos
            FROM nonce_pano_24.ms_gral_24
            WHERE c_nom_mun = $1

            UNION ALL

            SELECT 'Media Superior' as nivel,
                COALESCE(SUM(v472), 0) as total_alumnos
            FROM nonce_pano_24.ms_tecno_24
            WHERE c_nom_mun = $1

            UNION ALL

            -- SUPERIOR
            SELECT 'Superior' as nivel,
                COALESCE(SUM(v177), 0) as total_alumnos
            FROM nonce_pano_24.sup_carrera_24
            WHERE cv_motivo = 0
                AND c_nom_mun = $1

            UNION ALL

            SELECT 'Superior' as nivel,
                COALESCE(SUM(v142), 0) as total_alumnos
            FROM nonce_pano_24.sup_posgrado_24
            WHERE cv_motivo = 0
                AND c_nom_mun = $1
        )
        SELECT 
            nivel,
            SUM(total_alumnos) as total_alumnos
        FROM datos_alumnos
        GROUP BY nivel
        ORDER BY 
            CASE nivel 
                WHEN 'Inicial' THEN 1
                WHEN 'Preescolar' THEN 2  
                WHEN 'Primaria' THEN 3
                WHEN 'Secundaria' THEN 4
                WHEN 'Media Superior' THEN 5
                WHEN 'Superior' THEN 6
                ELSE 7
            END";
        
        $result = pg_query_params($conn, $query, array($municipio));
        $matricula = [];
        $totalGeneral = 0;
        
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $matricula[] = [
                    'nivel' => $row['nivel'],
                    'total_alumnos' => (int)$row['total_alumnos']
                ];
                $totalGeneral += (int)$row['total_alumnos'];
            }
            pg_free_result($result);
        }
        
        pg_close($conn);
        
        return [
            'municipio' => $municipio,
            'total_general' => $totalGeneral,
            'por_nivel' => $matricula,
            'fecha_consulta' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        pg_close($conn);
        return ['error' => 'Error en consulta de matrícula: ' . $e->getMessage()];
    }
}

// =============================================================================
// CONSULTA DE MATRÍCULA DE ALUMNOS - ESQUEMA 2024
// =============================================================================

/**
 * Obtiene datos reales de docentes usando la consulta de obtenerDocentesPorNivel
 * Basada en la función obtenerDocentesPorNivel del archivo conexion.php
 * 
 * @param string $municipio Nombre del municipio
 * @return array Información real de docentes por nivel educativo
 */
function obtenerDocentesPrueba2024($municipio = 'CORREGIDORA')
{
    $conn = ConectarsePrueba();
    if (!$conn) {
        return ['error' => 'No se pudo conectar a la base de datos'];
    }
    
    try {
        // Consulta exacta de la función obtenerDocentesPorNivel en conexion.php
        $query = "
        -- EDUCACIÓN INICIAL ESCOLARIZADA
        SELECT 
            'Inicial Escolarizada' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(V509+V516+V523+V511+V518+V525+V785+V510+V517+V524+V512+V519+V526+V786), 0) as total_docentes
        FROM nonce_pano_24.ini_gral_24 
        WHERE c_nom_mun = $1
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- EDUCACIÓN INICIAL NO ESCOLARIZADA
        SELECT 
            'Inicial No Escolarizada' as nivel_educativo,
            'Comunitario' as subnivel,
            COALESCE(SUM(v124 + V125), 0) as total_docentes
        FROM nonce_pano_24.ini_comuni_24 
        WHERE c_nom_mun = $1
          AND cv_estatus_captura = 0

        UNION ALL

        -- CAM (CENTRO DE ATENCIÓN MÚLTIPLE) - Dato fijo como en la consulta original
        SELECT 
            'CAM' as nivel_educativo,
            'Especial' as subnivel,
            22 as total_docentes

        UNION ALL

        -- PREESCOLAR GENERAL
        SELECT 
            'Preescolar' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(v909), 0) as total_docentes
        FROM nonce_pano_24.pree_gral_24 
        WHERE c_nom_mun = $1
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PREESCOLAR COMUNITARIO
        SELECT 
            'Preescolar' as nivel_educativo,
            'Comunitario' as subnivel,
            COALESCE(SUM(v151), 0) as total_docentes
        FROM nonce_pano_24.pree_comuni_24 
        WHERE c_nom_mun = $1
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA GENERAL
        SELECT 
            'Primaria' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(v1676), 0) as total_docentes
        FROM nonce_pano_24.prim_gral_24 
        WHERE c_nom_mun = $1
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA COMUNITARIO
        SELECT 
            'Primaria' as nivel_educativo,
            'Comunitario' as subnivel,
            COALESCE(SUM(v585), 0) as total_docentes
        FROM nonce_pano_24.prim_comuni_24 
        WHERE c_nom_mun = $1
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- SECUNDARIA
        SELECT 
            'Secundaria' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(v1401), 0) as total_docentes
        FROM nonce_pano_24.sec_gral_24 
        WHERE c_nom_mun = $1
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- MEDIA SUPERIOR
        SELECT 
            'Media Superior' as nivel_educativo,
            'Plantel' as subnivel,
            COALESCE(SUM(v169), 0) as total_docentes
        FROM nonce_pano_24.ms_plantel_24 
        WHERE c_nom_mun = $1
          AND cv_motivo = 0

        UNION ALL

        -- SUPERIOR
        SELECT 
            'Superior' as nivel_educativo,
            'Licenciatura' as subnivel,
            COALESCE(SUM(v83), 0) as total_docentes
        FROM nonce_pano_24.sup_escuela_24 
        WHERE c_nom_mun = $1
          AND cv_motivo = 0";
        
        $result = pg_query_params($conn, $query, array($municipio));
        $docentes = [];
        $totalGeneral = 0;
        
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $docentes[] = [
                    'nivel_educativo' => $row['nivel_educativo'],
                    'subnivel' => $row['subnivel'],
                    'total_docentes' => (int)$row['total_docentes']
                ];
                $totalGeneral += (int)$row['total_docentes'];
            }
            pg_free_result($result);
        }
        
        pg_close($conn);
        
        return [
            'municipio' => $municipio,
            'total_general' => $totalGeneral,
            'por_nivel' => $docentes,
            'fecha_consulta' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        pg_close($conn);
        return ['error' => 'Error en consulta de docentes: ' . $e->getMessage()];
    }
}

// =============================================================================
// FUNCIÓN CONSOLIDADA PARA OBTENER TODOS LOS DATOS
// =============================================================================

/**
 * Obtiene todos los datos educativos consolidados para un municipio
 * 
 * @param string $municipio Nombre del municipio
 * @return array Datos completos del municipio (docentes, escuelas, matrícula)
 */
function obtenerDatosCompletos2024($municipio = 'CORREGIDORA')
{
    // Obtener datos educativos consolidados usando la consulta exacta
    $datosEducativos = obtenerDatosEducativosPrueba2024($municipio);
    
    return [
        'municipio' => $municipio,
        'docentes' => obtenerDocentesPrueba2024($municipio),
        'datos_educativos' => $datosEducativos,
        'fecha_consulta' => date('Y-m-d H:i:s')
    ];
}

// =============================================================================
// CONSULTA DE MUNICIPIOS - ESQUEMA 2024
// =============================================================================

/**
 * Obtiene la lista de municipios disponibles - Copiado de conexion.php
 * 
 * @return array Lista de municipios normalizados
 */
function obtenerMunicipiosPrueba2024()
{
    // Lista de fallback en caso de problemas de conexión
    $municipiosFallback = ['CORREGIDORA', 'QUERÉTARO', 'EL MARQUÉS', 'SAN JUAN DEL RÍO'];
    
    // Verificar disponibilidad de PostgreSQL
    if (!function_exists('pg_connect')) {
        error_log('SEDEQ Prueba: PostgreSQL no disponible para consulta de municipios, usando datos de fallback');
        return $municipiosFallback;
    }

    try {
        // Establecer conexión usando la función de prueba
        $conn = ConectarsePrueba();
        
        if (!$conn) {
            error_log('SEDEQ Prueba: Error al conectar con PostgreSQL para municipios');
            return $municipiosFallback;
        }
        
        // Consulta dinámica copiada exactamente de conexion.php
        $query = "SELECT DISTINCT TRIM(UPPER(c_nom_mun)) AS municipio
                  FROM (
                      SELECT c_nom_mun FROM nonce_pano_24.ini_gral_24 
                      WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                      UNION
                      SELECT c_nom_mun FROM nonce_pano_24.ini_ind_24 
                      WHERE cv_estatus_captura = 0
                      UNION
                      SELECT c_nom_mun FROM nonce_pano_24.pree_gral_24 
                      WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                      UNION
                      SELECT c_nom_mun FROM nonce_pano_24.prim_gral_24 
                      WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                      UNION
                      SELECT c_nom_mun FROM nonce_pano_24.sec_gral_24 
                      WHERE (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
                      UNION
                      SELECT c_nom_mun FROM nonce_pano_24.ms_gral_24
                      UNION
                      SELECT c_nom_mun FROM nonce_pano_24.sup_carrera_24 
                      WHERE cv_motivo = 0
                  ) AS municipios
                  WHERE TRIM(c_nom_mun) != '' AND c_nom_mun IS NOT NULL
                  ORDER BY municipio;";
        
        $result = pg_query($conn, $query);
        $municipios = [];
        
        if ($result) {
            $municipiosUnicos = []; // Array para evitar duplicados
            while ($row = pg_fetch_assoc($result)) {
                // Obtener el municipio y normalizarlo para display
                $municipioNormalizado = normalizarNombreMunicipioPrueba($row['municipio']);
                if (!empty($municipioNormalizado) && !in_array($municipioNormalizado, $municipiosUnicos)) {
                    $municipiosUnicos[] = $municipioNormalizado;
                    $municipios[] = $municipioNormalizado;
                }
            }
            pg_free_result($result);
        }
        
        pg_close($conn);
        
        // Asegurar que tenemos los 18 municipios oficiales de Querétaro
        $municipiosOficiales = [
            'AMEALCO DE BONFIL', 'ARROYO SECO', 'CADEREYTA DE MONTES', 'COLÓN', 
            'CORREGIDORA', 'EL MARQUÉS', 'EZEQUIEL MONTES', 'HUIMILPAN', 
            'JALPAN DE SERRA', 'LANDA DE MATAMOROS', 'PEÑAMILLER', 'PEDRO ESCOBEDO', 
            'PINAL DE AMOLES', 'QUERÉTARO', 'SAN JOAQUÍN', 'SAN JUAN DEL RÍO', 
            'TEQUISQUIAPAN', 'TOLIMÁN'
        ];
        
        // Agregar municipios faltantes que no estén en los datos
        foreach ($municipiosOficiales as $oficial) {
            if (!in_array($oficial, $municipios)) {
                $municipios[] = $oficial;
            }
        }
        
        // Ordenar alfabéticamente
        sort($municipios);
        
        // Si no se encontraron municipios, usar fallback
        return empty($municipios) ? $municipiosFallback : $municipios;
        
    } catch (Exception $e) {
        // Log del error para debugging
        error_log('SEDEQ Prueba: Error en consulta de municipios: ' . $e->getMessage());
        return $municipiosFallback;
    }
}

/**
 * Normaliza nombres de municipios - Copiado de conexion.php
 * 
 * @param string $nombreMunicipio Nombre del municipio desde la base de datos
 * @return string Nombre normalizado del municipio
 */
function normalizarNombreMunicipioPrueba($nombreMunicipio) {
    // Eliminar espacios extra y convertir a string
    $nombre = trim((string)$nombreMunicipio);
    
    // Si está vacío, retornar vacío
    if (empty($nombre)) {
        return '';
    }
    
    // Lista oficial de los 18 municipios de Querétaro con sus variantes
    $municipiosQueretaro = [
        // Patrones principales - sin acentos para matching
        'AMEALCO DE BONFIL' => 'AMEALCO DE BONFIL',
        'ARROYO SECO' => 'ARROYO SECO',
        'CADEREYTA DE MONTES' => 'CADEREYTA DE MONTES',
        'COLON' => 'COLÓN',
        'CORREGIDORA' => 'CORREGIDORA',
        'EL MARQUES' => 'EL MARQUÉS',
        'EZEQUIEL MONTES' => 'EZEQUIEL MONTES',
        'HUIMILPAN' => 'HUIMILPAN',
        'JALPAN DE SERRA' => 'JALPAN DE SERRA',
        'LANDA DE MATAMOROS' => 'LANDA DE MATAMOROS',
        'PENAMILLER' => 'PEÑAMILLER',
        'PEDRO ESCOBEDO' => 'PEDRO ESCOBEDO',
        'PINAL DE AMOLES' => 'PINAL DE AMOLES',
        'QUERETARO' => 'QUERÉTARO',
        'SAN JOAQUIN' => 'SAN JOAQUÍN',
        'SAN JUAN DEL RIO' => 'SAN JUAN DEL RÍO',
        'TEQUISQUIAPAN' => 'TEQUISQUIAPAN',
        'TOLIMAN' => 'TOLIMÁN',
        
        // Variantes con caracteres problemáticos
        'SAN JOAQUN' => 'SAN JOAQUÍN',
        'SAN JUAN DEL RO' => 'SAN JUAN DEL RÍO',
        'PEAMILLER' => 'PEÑAMILLER'
    ];
    
    // Limpiar caracteres problemáticos pero preservar estructura
    $nombreLimpio = strtoupper(trim($nombre));
    $nombreLimpio = preg_replace('/[^\w\s]/u', '', $nombreLimpio);
    $nombreLimpio = preg_replace('/\s+/', ' ', $nombreLimpio);
    
    // Buscar coincidencia directa
    if (isset($municipiosQueretaro[$nombreLimpio])) {
        return $municipiosQueretaro[$nombreLimpio];
    }
    
    // Buscar por similitud (para casos de caracteres perdidos)
    foreach ($municipiosQueretaro as $patron => $oficial) {
        if (levenshtein($nombreLimpio, $patron) <= 2) {
            return $oficial;
        }
    }
    
    // Verificar si contiene palabras clave de municipios conocidos
    if (strpos($nombreLimpio, 'JOAQU') !== false) return 'SAN JOAQUÍN';
    if (strpos($nombreLimpio, 'JUAN') !== false && strpos($nombreLimpio, 'DEL') !== false) return 'SAN JUAN DEL RÍO';
    if (strpos($nombreLimpio, 'MILLER') !== false) return 'PEÑAMILLER';
    if (strpos($nombreLimpio, 'TOLIM') !== false) return 'TOLIMÁN';
    
    // Como último recurso, retornar el nombre limpio
    return $nombreLimpio;
}

?>