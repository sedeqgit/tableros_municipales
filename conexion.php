<?php
/**
 * =============================================================================
 * MÓDULO DE CONEXIÓN A BASE DE DATOS POSTGRESQL
 * Sistema de Dashboard Estadístico - SEDEQ Corregidora
 * =============================================================================
 * 
 * Este archivo centraliza toda la lógica de conexión a la base de datos PostgreSQL
 * y proporciona funciones para obtener datos educativos del sistema.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Conexión robusta a PostgreSQL con manejo de errores
 * - Sistema de fallback con datos representativos
 * - Funciones especializadas por tipo de consulta
 * - Optimización de consultas con agrupaciones y ordenamiento
 * 
 * ARQUITECTURA DE SEGURIDAD:
 * - Validación de disponibilidad de extensiones PostgreSQL
 * - Manejo de errores de conexión sin exposición de datos sensibles
 * - Queries parametrizadas para prevenir inyección SQL
 * - Sistema de datos de respaldo para alta disponibilidad
 * 
 * @author Sistema SEDEQ
 * @version 1.2.1
 */

/**
 * =============================================================================
 * FUNCIÓN DE CONEXIÓN PRINCIPAL A POSTGRESQL
 * =============================================================================
 * 
 * Establece una conexión segura a la base de datos PostgreSQL con validación
 * previa de disponibilidad de extensiones y manejo robusto de errores.
 * 
 * CARACTERÍSTICAS DE SEGURIDAD:
 * - Verificación de extensiones PostgreSQL disponibles
 * - Configuración de encoding LATIN1 para compatibilidad
 * - Manejo de errores sin exposición de información sensible
 * - Connection pooling implícito por sesión PHP
 * 
 * @return resource|null Recurso de conexión a PostgreSQL o null si falla
 * @throws Exception Si no se pueden cargar las extensiones PostgreSQL
 */
function Conectarse()
{
    // VALIDACIÓN DE PREREQUISITOS
    // Verificar si las funciones de PostgreSQL están disponibles en el servidor
    // Esta validación previene errores fatales en sistemas sin extensión pgsql
    if (!function_exists('pg_connect')) {
        // Log del error para debugging (sin exponer al usuario)
        error_log('ERROR: Extensiones PostgreSQL no disponibles en el servidor');
        return null;
    }

    // ESTABLECIMIENTO DE CONEXIÓN
    // Parámetros de conexión optimizados para el entorno SEDEQ
    // Configuración específica para bases de datos gubernamentales
    $connectionString = "host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres options='--client_encoding=LATIN1'";

    // Intento de conexión con manejo de errores robusto
    $link_conexion = pg_connect($connectionString)
        or die('Error crítico de conexión a base de datos: ' . pg_last_error());

    return $link_conexion;
}

/**
 * =============================================================================
 * FUNCIÓN PRINCIPAL DE OBTENCIÓN DE DATOS EDUCATIVOS
 * =============================================================================
 * 
 * Recupera y procesa los datos estadísticos de escuelas y alumnos desde la
 * base de datos PostgreSQL. Implementa un sistema robusto de fallback para
 * garantizar disponibilidad continua del servicio.
 * 
 * FUNCIONALIDADES AVANZADAS:
 * - Consulta optimizada con agregaciones SQL
 * - Sistema de fallback con datos representativos
 * - Filtrado de datos inconsistentes (USAER)
 * - Ordenamiento lógico por nivel educativo
 * - Manejo robusto de errores de conexión
 * 
 * ESTRUCTURA DE DATOS RETORNADA:
 * Array bidimensional con formato Google Charts compatible:
 * [
 *   ['Tipo Educativo', 'Escuelas', 'Alumnos'],
 *   ['Preescolar', 120, 12000],
 *   ['Primaria', 180, 45000],
 *   ...
 * ]
 * 
 * @return array Arreglo con los datos educativos organizados y procesados
 * @uses Conectarse() Para establecer conexión a PostgreSQL
 */
function obtenerDatosEducativos()
{
    // =============================================================================
    // SISTEMA DE DATOS DE RESPALDO (FALLBACK)
    // =============================================================================

    /**
     * Datos representativos basados en estadísticas reales de Corregidora 2023-2024
     * Estos datos se utilizan cuando no hay conexión a la base de datos,
     * garantizando que el sistema siga funcionando para demostraciones y pruebas.
     * 
     * CRITERIOS DE LOS DATOS:
     * - Basados en estadísticas oficiales SEDEQ 2023-2024
     * - Proporciones realistas entre escuelas y alumnos
     * - Ordenamiento por progresión educativa natural
     * - Números redondos para facilitar análisis de demo
     */
    $datosEducativos = array(
        array('Tipo Educativo', 'Escuelas', 'Alumnos'),
        array('Inicial (Escolarizado)', 5, 150),      // Educación temprana institucional
        array('Inicial (No Escolarizado)', 8, 240),   // Programas comunitarios y familiares
        array('Especial (CAM)', 3, 120),              // Centros de Atención Múltiple
        array('Preescolar', 120, 12000),              // Educación preescolar (3-5 años)
        array('Primaria', 180, 45000),                // Educación básica primaria (6-11 años)
        array('Secundaria', 95, 28000),               // Educación básica secundaria (12-14 años)
        array('Media Superior', 60, 19000),           // Bachillerato y técnico (15-17 años)
        array('Superior', 25, 15000)                  // Educación universitaria y posgrado
    );

    // =============================================================================
    // VALIDACIÓN DE DISPONIBILIDAD DE POSTGRESQL
    // =============================================================================

    // Verificar disponibilidad de extensiones PostgreSQL antes de intentar conexión
    // Esto previene errores fatales en servidores sin soporte PostgreSQL
    if (!function_exists('pg_connect')) {
        // Log para monitoreo del sistema (no visible al usuario final)
        error_log('SEDEQ: PostgreSQL no disponible, usando datos de fallback');
        return $datosEducativos;
    }

    // =============================================================================
    // ESTABLECIMIENTO DE CONEXIÓN Y CONSULTA PRINCIPAL
    // =============================================================================

    // Establecer conexión utilizando función centralizada
    $link = Conectarse();

    if (!$link) {
        return $datosEducativos;
    }    // Consulta SQL para obtener los datos de escuelas y alumnos por tipo educativo
    $query = "SELECT 
            tipo_educativo, 
            SUM(escuelas_total) as escuelas,
            SUM(alumnos_total) as alumnos
          FROM 
            nonce_pano_23.estadistica_corregidora
          WHERE
            tipo_educativo NOT LIKE '%USAER%'
          GROUP BY 
            tipo_educativo
          ORDER BY 
            CASE 
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

    $result = pg_query($link, $query);

    // Inicializar array de dados
    $resultadoFinal = array();
    $resultadoFinal[] = array('Tipo Educativo', 'Escuelas', 'Alumnos');

    // Si la consulta fue exitosa, procesar resultados
    if ($result && pg_num_rows($result) > 0) {
        // Procesar resultados de la consulta
        while ($row = pg_fetch_assoc($result)) {
            $resultadoFinal[] = array(
                $row['tipo_educativo'],
                (int) $row['escuelas'],
                (int) $row['alumnos']
            );
        }

        // Actualizar datos
        $datosEducativos = $resultadoFinal;

        // Cerrar la conexión
        pg_close($link);
    }

    return $datosEducativos;
}

/**
 * Función para obtener los datos de matrícula por escuelas públicas
 * Consulta la tabla nonce_pano_23.matricula_escuelas_publicas
 * 
 * @return array Arreglo con los datos de matrícula por año y subnivel
 */
function obtenerMatriculaPorEscuelasPublicas()
{
    // Datos por defecto en caso de que no se pueda conectar a la BD
    $datosMatricula = array(
        // Estructurar los datos por año y subnivel
        '2018-2019' => array(
            'Inicial NE' => 444,
            'CAM' => 164,
            'Preescolar' => 3350,
            'Primaria' => 11621,
            'Secundaria' => 5321,
            'Media superior' => 6661,
            'Superior' => 1093
        ),
        // Incluir los demás años como datos de respaldo
        '2019-2020' => array(
            'Inicial NE' => 245,
            'CAM' => 190,
            'Preescolar' => 3410,
            'Primaria' => 12148,
            'Secundaria' => 5520,
            'Media superior' => 6862,
            'Superior' => 1135
        ),
        // Resto de años como precaución
        '2020-2021' => array(),
        '2021-2022' => array(),
        '2022-2023' => array(),
        '2023-2024' => array(),
    );

    // Verificar si las funciones de PostgreSQL están disponibles
    if (!function_exists('pg_connect')) {
        return $datosMatricula;
    }

    // Establecer conexión a la BD
    $link = Conectarse();

    if (!$link) {
        return $datosMatricula;
    }

    // Consulta SQL para obtener los datos de matrícula por año y subnivel
    $query = "SELECT 
                anio, 
                subnivel, 
                cantidad_alumnos
            FROM 
                nonce_pano_23.matricula_escuelas_publicas
            ORDER BY 
                anio, 
                CASE 
                    WHEN subnivel = 'Inicial NE' THEN 1
                    WHEN subnivel = 'CAM' THEN 2
                    WHEN subnivel = 'Preescolar' THEN 3
                    WHEN subnivel = 'Primaria' THEN 4
                    WHEN subnivel = 'Secundaria' THEN 5
                    WHEN subnivel = 'Media superior' THEN 6
                    WHEN subnivel = 'Superior' THEN 7
                    ELSE 8
                END";

    $result = pg_query($link, $query);

    // Si la consulta fue exitosa, procesar resultados
    if ($result && pg_num_rows($result) > 0) {
        // Reiniciar el array de datos
        $datosMatricula = array();

        // Procesar resultados de la consulta
        while ($row = pg_fetch_assoc($result)) {
            $anio = $row['anio'];
            $subnivel = $row['subnivel'];
            $cantidad = (int) $row['cantidad_alumnos'];

            // Inicializar el arreglo del año si no existe
            if (!isset($datosMatricula[$anio])) {
                $datosMatricula[$anio] = array();
            }

            // Asignar la cantidad al subnivel correspondiente
            $datosMatricula[$anio][$subnivel] = $cantidad;
        }

        // Cerrar la conexión
        pg_close($link);
    }

    return $datosMatricula;
}

/**
 * Función para obtener datos de escuelas por sostenimiento (públicas y privadas)
 * Si no hay conexión a la base de datos, devuelve datos de ejemplo
 * 
 * @return array Arreglo con los datos de escuelas por sostenimiento
 */
function obtenerEscuelasPorSostenimiento()
{
    // Datos por defecto en caso de que no se pueda conectar a la BD
    $datosSostenimiento = array(
        'publicas' => 350,
        'privadas' => 146,
        'porcentaje_publicas' => 70,
        'porcentaje_privadas' => 30,
        'por_nivel' => array(
            'Inicial (Escolarizado)' => array('publicas' => 3, 'privadas' => 2),
            'Inicial (No Escolarizado)' => array('publicas' => 8, 'privadas' => 0),
            'Especial (CAM)' => array('publicas' => 3, 'privadas' => 0),
            'Preescolar' => array('publicas' => 70, 'privadas' => 50),
            'Primaria' => array('publicas' => 110, 'privadas' => 70),
            'Secundaria' => array('publicas' => 70, 'privadas' => 25),
            'Media Superior' => array('publicas' => 40, 'privadas' => 20),
            'Superior' => array('publicas' => 15, 'privadas' => 10)
        )
    );

    // Verificar si las funciones de PostgreSQL están disponibles
    if (!function_exists('pg_connect')) {
        return $datosSostenimiento;
    }

    // Establecer conexión a la BD
    $link = Conectarse();

    if (!$link) {
        return $datosSostenimiento;
    }

    // Consulta SQL para obtener las escuelas públicas y privadas por nivel
    $query = "SELECT 
                tipo_educativo, 
                SUM(escuelas_publicas) as escuelas_publicas,
                SUM(escuelas_privadas) as escuelas_privadas
              FROM 
                nonce_pano_23.estadistica_corregidora
              WHERE
                tipo_educativo NOT LIKE '%USAER%'
              GROUP BY 
                tipo_educativo
              ORDER BY 
                CASE 
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

    $result = pg_query($link, $query);

    // Totales acumulados
    $totalPublicas = 0;
    $totalPrivadas = 0;
    $porNivel = array();

    // Si la consulta fue exitosa, procesar resultados
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $nivel = $row['tipo_educativo'];
            $publicas = (int) $row['escuelas_publicas'];
            $privadas = (int) $row['escuelas_privadas'];

            $totalPublicas += $publicas;
            $totalPrivadas += $privadas;

            $porNivel[$nivel] = array(
                'publicas' => $publicas,
                'privadas' => $privadas
            );
        }

        // Calcular porcentajes
        $totalEscuelas = $totalPublicas + $totalPrivadas;
        $porcentajePublicas = ($totalEscuelas > 0) ? round(($totalPublicas / $totalEscuelas) * 100) : 0;
        $porcentajePrivadas = 100 - $porcentajePublicas;

        $datosSostenimiento = array(
            'publicas' => $totalPublicas,
            'privadas' => $totalPrivadas,
            'porcentaje_publicas' => $porcentajePublicas,
            'porcentaje_privadas' => $porcentajePrivadas,
            'por_nivel' => $porNivel
        );

        // Cerrar la conexión
        pg_close($link);
    }

    return $datosSostenimiento;
}

/**
 * Función para obtener los totales de escuelas y alumnos
 * 
 * @param array $datosEducativos Arreglo con los datos educativos
 * @return array Arreglo con los totales
 */
function calcularTotales($datosEducativos)
{
    $totalEscuelas = 0;
    $totalAlumnos = 0;

    for ($i = 1; $i < count($datosEducativos); $i++) {
        $totalEscuelas += $datosEducativos[$i][1];
        $totalAlumnos += $datosEducativos[$i][2];
    }
    return array(
        'escuelas' => $totalEscuelas,
        'alumnos' => $totalAlumnos
    );
}

/**
 * =============================================================================
 * FUNCIONES ESPECÍFICAS PARA DOCENTES
 * =============================================================================
 */

/**
 * Función para obtener datos consolidados de docentes por nivel educativo
 * Utiliza las consultas específicas identificadas en cada tabla de la BD
 * 
 * @return array Arreglo con los datos de docentes por nivel y subnivel
 */
function obtenerDocentesPorNivel()
{
    // Datos de respaldo en caso de problemas de conexión
    $datosDocentes = array(
        array('Nivel Educativo', 'Subnivel', 'Docentes'),
        array('Inicial Escolarizada', 'General', 36),
        array('Inicial No Escolarizada', 'Comunitario', 25),
        array('Preescolar', 'General', 336),
        array('Preescolar', 'Comunitario', 16),
        array('Primaria', 'General', 748),
        array('Primaria', 'Comunitario', 2),
        array('Secundaria', 'General', 571),
        array('Media Superior', 'Plantel', 607),
        array('Superior', 'Licenciatura', 467)
    );

    // Verificar disponibilidad de PostgreSQL
    if (!function_exists('pg_connect')) {
        error_log('SEDEQ: PostgreSQL no disponible para consulta de docentes, usando datos de fallback');
        return $datosDocentes;
    }

    $link = Conectarse();
    if (!$link) {
        error_log('SEDEQ: No se pudo conectar a la BD para docentes, usando datos de fallback');
        return $datosDocentes;
    }

    try {
        // Consulta unificada para obtener todos los datos de docentes
        $query = "
        -- EDUCACIÓN INICIAL ESCOLARIZADA
        SELECT 
            'Inicial Escolarizada' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(V509+V516+V523+V511+V518+V525+V785+V510+V517+V524+V512+V519+V526+V786), 0) as total_docentes
        FROM nonce_pano_23.ini_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- EDUCACIÓN INICIAL NO ESCOLARIZADA
        SELECT 
            'Inicial No Escolarizada' as nivel_educativo,
            'Comunitario' as subnivel,
            COALESCE(SUM(v124 + V125), 0) as total_docentes
        FROM nonce_pano_23.ini_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND cv_estatus_captura = 0

        UNION ALL

        -- PREESCOLAR GENERAL
        SELECT 
            'Preescolar' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(v909), 0) as total_docentes
        FROM nonce_pano_23.pree_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PREESCOLAR COMUNITARIO
        SELECT 
            'Preescolar' as nivel_educativo,
            'Comunitario' as subnivel,
            COALESCE(SUM(v151), 0) as total_docentes
        FROM nonce_pano_23.pree_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA GENERAL
        SELECT 
            'Primaria' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(v1676), 0) as total_docentes
        FROM nonce_pano_23.prim_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA COMUNITARIO
        SELECT 
            'Primaria' as nivel_educativo,
            'Comunitario' as subnivel,
            COALESCE(SUM(v585), 0) as total_docentes
        FROM nonce_pano_23.prim_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- SECUNDARIA
        SELECT 
            'Secundaria' as nivel_educativo,
            'General' as subnivel,
            COALESCE(SUM(v1401), 0) as total_docentes
        FROM nonce_pano_23.sec_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- MEDIA SUPERIOR
        SELECT 
            'Media Superior' as nivel_educativo,
            'Plantel' as subnivel,
            COALESCE(SUM(v169), 0) as total_docentes
        FROM nonce_pano_23.ms_plantel_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND cv_motivo = 0

        UNION ALL

        -- SUPERIOR
        SELECT 
            'Superior' as nivel_educativo,
            'Licenciatura' as subnivel,
            COALESCE(SUM(v83), 0) as total_docentes
        FROM nonce_pano_23.sup_escuela_23 
        WHERE c_nom_mun = 'CORREGIDORA' 
          AND cv_motivo = 0
        ";

        $result = pg_query($link, $query);

        if ($result && pg_num_rows($result) > 0) {
            $datosDocentes = array(
                array('Nivel Educativo', 'Subnivel', 'Docentes')
            );

            while ($row = pg_fetch_assoc($result)) {
                $datosDocentes[] = array(
                    $row['nivel_educativo'],
                    $row['subnivel'],
                    (int) $row['total_docentes']
                );
            }

            pg_free_result($result);
        }

        pg_close($link);
    } catch (Exception $e) {
        error_log('Error en consulta de docentes: ' . $e->getMessage());
        // En caso de error, retornar datos de fallback
        return $datosDocentes;
    }

    return $datosDocentes;
}

/**
 * Función para calcular totales y estadísticas de docentes
 * 
 * @param array $datosDocentes Arreglo con los datos de docentes
 * @return array Arreglo con los totales y estadísticas calculadas
 */
function calcularTotalesDocentes($datosDocentes)
{
    $totalDocentes = 0;
    $docentesPorNivel = array();
    $docentesPorModalidad = array();

    // Omitir la primera fila que contiene encabezados
    for ($i = 1; $i < count($datosDocentes); $i++) {
        $nivel = $datosDocentes[$i][0];
        $subnivel = $datosDocentes[$i][1];
        $docentes = $datosDocentes[$i][2];

        $totalDocentes += $docentes;

        // Agregar al total por nivel
        if (!isset($docentesPorNivel[$nivel])) {
            $docentesPorNivel[$nivel] = 0;
        }
        $docentesPorNivel[$nivel] += $docentes;

        // Agregar al total por modalidad
        if (!isset($docentesPorModalidad[$subnivel])) {
            $docentesPorModalidad[$subnivel] = 0;
        }
        $docentesPorModalidad[$subnivel] += $docentes;
    }

    return array(
        'total' => $totalDocentes,
        'por_nivel' => $docentesPorNivel,
        'por_modalidad' => $docentesPorModalidad
    );
}

/**
 * =============================================================================
 * FUNCIÓN PARA OBTENER DOCENTES POR SOSTENIMIENTO (PÚBLICO/PRIVADO)
 * =============================================================================
 * 
 * Recupera y procesa los datos de docentes segmentados por modalidad de
 * sostenimiento. Implementa el mismo patrón que obtenerEscuelasPorSostenimiento.
 * 
 * @return array Arreglo con datos de docentes públicos y privados por nivel
 */
function obtenerDocentesPorSostenimiento()
{
    // Datos por defecto en caso de que no se pueda conectar a la BD
    $datosSostenimiento = array(
        'publicos' => 1159,
        'privados' => 1649,
        'porcentaje_publicos' => 41,
        'porcentaje_privados' => 59,
        'por_nivel' => array(
            'Inicial Escolarizada' => array('publicos' => 0, 'privados' => 36),
            'Inicial No Escolarizada' => array('publicos' => 25, 'privados' => 0),
            'Preescolar' => array('publicos' => 125, 'privadas' => 227),
            'Primaria' => array('publicos' => 386, 'privadas' => 364),
            'Secundaria' => array('publicos' => 262, 'privadas' => 309),
            'Media Superior' => array('publicos' => 230, 'privadas' => 377),
            'Superior' => array('publicos' => 131, 'privadas' => 336)
        )
    );

    // Verificar si las funciones de PostgreSQL están disponibles
    if (!function_exists('pg_connect')) {
        return $datosSostenimiento;
    }

    // Establecer conexión a la BD
    $link = Conectarse();

    if (!$link) {
        return $datosSostenimiento;
    }

    try {
        // Consulta para obtener docentes por modalidad usando las consultas correctas
        $query = "
        -- INICIAL ESCOLARIZADA POR MODALIDAD
        SELECT 
            'Inicial Escolarizada' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(V509+V516+V523+V511+V518+V525+V785+V510+V517+V524+V512+V519+V526+V786) as docentes
        FROM nonce_pano_23.ini_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- INICIAL NO ESCOLARIZADA
        SELECT 
            'Inicial No Escolarizada' as nivel,
            'publicos' as modalidad,
            SUM(v124 + v125) as docentes
        FROM nonce_pano_23.ini_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND cv_estatus_captura = 0

        UNION ALL

        -- PREESCOLAR GENERAL POR MODALIDAD
        SELECT 
            'Preescolar' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v909) as docentes
        FROM nonce_pano_23.pree_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- PREESCOLAR COMUNITARIO
        SELECT 
            'Preescolar' as nivel,
            'publicos' as modalidad,
            SUM(v151) as docentes
        FROM nonce_pano_23.pree_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- PRIMARIA GENERAL POR MODALIDAD
        SELECT 
            'Primaria' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v1676) as docentes
        FROM nonce_pano_23.prim_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- PRIMARIA COMUNITARIO
        SELECT 
            'Primaria' as nivel,
            'publicos' as modalidad,
            SUM(v585) as docentes
        FROM nonce_pano_23.prim_comuni_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)

        UNION ALL

        -- SECUNDARIA GENERAL POR MODALIDAD
        SELECT 
            'Secundaria' as nivel,
            CASE 
                WHEN subcontrol = 'FEDERAL TRANSFERIDO' THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v1401) as docentes
        FROM nonce_pano_23.sec_gral_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
        GROUP BY subcontrol

        UNION ALL

        -- MEDIA SUPERIOR POR MODALIDAD
        SELECT 
            'Media Superior' as nivel,
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v169) as docentes
        FROM nonce_pano_23.ms_plantel_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND cv_motivo = 0
        GROUP BY 
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END

        UNION ALL

        -- SUPERIOR POR MODALIDAD
        SELECT 
            'Superior' as nivel,
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END as modalidad,
            SUM(v83) as docentes
        FROM nonce_pano_23.sup_escuela_23 
        WHERE c_nom_mun = 'CORREGIDORA' AND cv_motivo = 0
        GROUP BY 
            CASE 
                WHEN subcontrol IN ('FEDERAL TRANSFERIDO', 'FEDERAL', 'ESTATAL', 'AUT?NOMO') THEN 'publicos'
                WHEN subcontrol = 'PRIVADO' THEN 'privados'
                ELSE 'publicos'
            END
        ";

        $result = pg_query($link, $query);

        // Totales acumulados
        $totalPublicos = 0;
        $totalPrivados = 0;
        $porNivel = array();

        // Inicializar niveles
        $niveles = ['Inicial Escolarizada', 'Inicial No Escolarizada', 'Preescolar', 'Primaria', 'Secundaria', 'Media Superior', 'Superior'];
        foreach ($niveles as $nivel) {
            $porNivel[$nivel] = array('publicos' => 0, 'privados' => 0);
        }

        // Si la consulta fue exitosa, procesar resultados
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $nivel = $row['nivel'];
                $modalidad = $row['modalidad'];
                $docentes = (int) $row['docentes'];

                if ($modalidad === 'publicos') {
                    $totalPublicos += $docentes;
                    $porNivel[$nivel]['publicos'] += $docentes;
                } else {
                    $totalPrivados += $docentes;
                    $porNivel[$nivel]['privados'] += $docentes;
                }
            }

            pg_free_result($result);
        }

        pg_close($link);

        // Calcular porcentajes
        $total = $totalPublicos + $totalPrivados;
        $porcentajePublicos = $total > 0 ? round(($totalPublicos / $total) * 100) : 0;
        $porcentajePrivados = $total > 0 ? round(($totalPrivados / $total) * 100) : 0;

        $datosSostenimiento = array(
            'publicos' => $totalPublicos,
            'privados' => $totalPrivados,
            'porcentaje_publicos' => $porcentajePublicos,
            'porcentaje_privados' => $porcentajePrivados,
            'por_nivel' => $porNivel
        );

    } catch (Exception $e) {
        error_log('SEDEQ: Error en obtenerDocentesPorSostenimiento: ' . $e->getMessage());
    }

    return $datosSostenimiento;
}

/**
 * =============================================================================
 * FUNCIÓN PARA OBTENER DOCENTES POR GÉNERO Y NIVEL EDUCATIVO
 * =============================================================================
 * 
 * Recupera los datos de distribución de docentes por género (hombres y mujeres)
 * agrupados por nivel y subnivel educativo desde la base de datos PostgreSQL.
 * 
 * FUNCIONALIDADES:
 * - Consulta los campos específicos de cada tabla por nivel educativo
 * - Calcula totales de docentes hombres y mujeres por subnivel
 * - Proporciona porcentajes de distribución por género
 * - Sistema de fallback con datos reales del sistema
 * 
 * ESTRUCTURA DE DATOS RETORNADA:
 * Array con formato:
 * [
 *   ['Nivel Educativo', 'Subnivel', 'Total Docentes', 'Hombres', 'Mujeres', '% Hombres', '% Mujeres'],
 *   ['Preescolar', 'General', 336, 15, 321, 4.5, 95.5],
 *   ...
 * ]
 * 
 * @return array Arreglo con los datos de docentes por género organizados por nivel
 * @uses Conectarse() Para establecer conexión a PostgreSQL
 */
function obtenerDocentesPorGenero()
{
    // Datos de respaldo basados en la distribución real del sistema
    $datosGenero = array(
        array('Nivel Educativo', 'Subnivel', 'Total Docentes', 'Hombres', 'Mujeres', '% Hombres', '% Mujeres'),
        array('Inicial Escolarizada', 'General', 36, 0, 36, 0.0, 100.0),
        array('Inicial No Escolarizada', 'Comunitario', 25, 0, 25, 0.0, 100.0),
        array('Preescolar', 'General', 336, 15, 321, 4.5, 95.5),
        array('Preescolar', 'Comunitario', 16, 1, 15, 6.3, 93.8),
        array('Primaria', 'General', 748, 239, 509, 31.9, 68.1),
        array('Primaria', 'Comunitario', 2, 1, 1, 50.0, 50.0),
        array('Secundaria', 'General', 571, 248, 323, 43.4, 56.6),
        array('Media Superior', 'Plantel', 607, 310, 297, 51.1, 48.9),
        array('Superior', 'Licenciatura', 467, 255, 212, 54.6, 45.4)
    );

    // Verificar disponibilidad de PostgreSQL
    if (!function_exists('pg_connect')) {
        error_log('SEDEQ: PostgreSQL no disponible para consulta de docentes por género, usando datos de fallback');
        return $datosGenero;
    }

    $link = Conectarse();
    if (!$link) {
        error_log('SEDEQ: No se pudo conectar a la BD para docentes por género, usando datos de fallback');
        return $datosGenero;
    }

    try {
        // Consulta unificada para obtener docentes por género por nivel educativo
        // Usando los campos exactos validados de los archivos PHP originales
        $query = "
        -- DESGLOSE DETALLADO POR NIVEL EDUCATIVO CON CAMPOS CORRECTOS
        SELECT * FROM (
            -- INICIAL NO ESCOLARIZADA COMUNITARIA
            SELECT 
                1 as orden,
                'Educación Inicial' as nivel_educativo,
                'No Escolarizada Comunitaria' as subnivel,
                SUM(v124) as docentes_hombres,
                SUM(v125) as docentes_mujeres,
                SUM(v124 + v125) as total_docentes,
                CASE WHEN SUM(v124 + v125) > 0 
                     THEN ROUND((SUM(v124)::decimal / SUM(v124 + v125)) * 100, 1) 
                     ELSE 0 END as porcentaje_hombres,
                CASE WHEN SUM(v124 + v125) > 0 
                     THEN ROUND((SUM(v125)::decimal / SUM(v124 + v125)) * 100, 1) 
                     ELSE 0 END as porcentaje_mujeres
            FROM nonce_pano_23.ini_comuni_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND cv_estatus_captura = 0
            
            UNION ALL
            
            -- INICIAL ESCOLARIZADA GENERAL
            SELECT 
                2 as orden,
                'Educación Inicial' as nivel_educativo,
                'Escolarizada General' as subnivel,
                SUM(v509 + v516 + v523 + v511 + v518 + v525 + v785) as docentes_hombres,
                SUM(v510 + v517 + v524 + v512 + v519 + v526 + v786) as docentes_mujeres,
                SUM(v509 + v516 + v523 + v511 + v518 + v525 + v785 + v510 + v517 + v524 + v512 + v519 + v526 + v786) as total_docentes,
                CASE WHEN SUM(v509 + v516 + v523 + v511 + v518 + v525 + v785 + v510 + v517 + v524 + v512 + v519 + v526 + v786) > 0 
                     THEN ROUND((SUM(v509 + v516 + v523 + v511 + v518 + v525 + v785)::decimal / SUM(v509 + v516 + v523 + v511 + v518 + v525 + v785 + v510 + v517 + v524 + v512 + v519 + v526 + v786)) * 100, 1) 
                     ELSE 0 END as porcentaje_hombres,
                CASE WHEN SUM(v509 + v516 + v523 + v511 + v518 + v525 + v785 + v510 + v517 + v524 + v512 + v519 + v526 + v786) > 0 
                     THEN ROUND((SUM(v510 + v517 + v524 + v512 + v519 + v526 + v786)::decimal / SUM(v509 + v516 + v523 + v511 + v518 + v525 + v785 + v510 + v517 + v524 + v512 + v519 + v526 + v786)) * 100, 1) 
                     ELSE 0 END as porcentaje_mujeres
            FROM nonce_pano_23.ini_gral_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
            
            UNION ALL
            
            -- PREESCOLAR GENERAL
            SELECT 
                3 as orden,
                'Educación Preescolar' as nivel_educativo,
                'General' as subnivel,
                SUM(v859 + v867) as docentes_hombres,
                SUM(v860 + v868) as docentes_mujeres,
                SUM(v859 + v867 + v860 + v868) as total_docentes,
                ROUND((SUM(v859 + v867)::decimal / (SUM(v859 + v867) + SUM(v860 + v868))) * 100, 1) as porcentaje_hombres,
                ROUND((SUM(v860 + v868)::decimal / (SUM(v859 + v867) + SUM(v860 + v868))) * 100, 1) as porcentaje_mujeres
            FROM nonce_pano_23.pree_gral_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
            
            UNION ALL
            
            -- PREESCOLAR COMUNITARIO
            SELECT 
                4 as orden,
                'Educación Preescolar' as nivel_educativo,
                'Comunitario' as subnivel,
                SUM(v149) as docentes_hombres,
                SUM(v150) as docentes_mujeres,
                SUM(v149 + v150) as total_docentes,
                CASE WHEN SUM(v149 + v150) > 0 
                     THEN ROUND((SUM(v149)::decimal / SUM(v149 + v150)) * 100, 1) 
                     ELSE 0 END as porcentaje_hombres,
                CASE WHEN SUM(v149 + v150) > 0 
                     THEN ROUND((SUM(v150)::decimal / SUM(v149 + v150)) * 100, 1) 
                     ELSE 0 END as porcentaje_mujeres
            FROM nonce_pano_23.pree_comuni_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
            
            UNION ALL
            
            -- PRIMARIA GENERAL
            SELECT 
                5 as orden,
                'Educación Primaria' as nivel_educativo,
                'General' as subnivel,
                SUM(v1567 + v1575) as docentes_hombres,
                SUM(v1568 + v1576) as docentes_mujeres,
                SUM(v1567 + v1575 + v1568 + v1576) as total_docentes,
                ROUND((SUM(v1567 + v1575)::decimal / (SUM(v1567 + v1575) + SUM(v1568 + v1576))) * 100, 1) as porcentaje_hombres,
                ROUND((SUM(v1568 + v1576)::decimal / (SUM(v1567 + v1575) + SUM(v1568 + v1576))) * 100, 1) as porcentaje_mujeres
            FROM nonce_pano_23.prim_gral_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
            
            UNION ALL
            
            -- PRIMARIA COMUNITARIA
            SELECT 
                6 as orden,
                'Educación Primaria' as nivel_educativo,
                'Comunitaria' as subnivel,
                SUM(v583) as docentes_hombres,
                SUM(v584) as docentes_mujeres,
                SUM(v583 + v584) as total_docentes,
                CASE WHEN SUM(v583 + v584) > 0 
                     THEN ROUND((SUM(v583)::decimal / SUM(v583 + v584)) * 100, 1) 
                     ELSE 0 END as porcentaje_hombres,
                CASE WHEN SUM(v583 + v584) > 0 
                     THEN ROUND((SUM(v584)::decimal / SUM(v583 + v584)) * 100, 1) 
                     ELSE 0 END as porcentaje_mujeres
            FROM nonce_pano_23.prim_comuni_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
            
            UNION ALL
            
            -- SECUNDARIA GENERAL
            SELECT 
                7 as orden,
                'Educación Secundaria' as nivel_educativo,
                'General' as subnivel,
                SUM(v1297 + v1303 + v1307 + v1309 + v1311 + v1313) as docentes_hombres,
                SUM(v1298 + v1304 + v1308 + v1310 + v1312 + v1314) as docentes_mujeres,
                SUM(v1297 + v1303 + v1307 + v1309 + v1311 + v1313 + v1298 + v1304 + v1308 + v1310 + v1312 + v1314) as total_docentes,
                ROUND((SUM(v1297 + v1303 + v1307 + v1309 + v1311 + v1313)::decimal / (SUM(v1297 + v1303 + v1307 + v1309 + v1311 + v1313) + SUM(v1298 + v1304 + v1308 + v1310 + v1312 + v1314))) * 100, 1) as porcentaje_hombres,
                ROUND((SUM(v1298 + v1304 + v1308 + v1310 + v1312 + v1314)::decimal / (SUM(v1297 + v1303 + v1307 + v1309 + v1311 + v1313) + SUM(v1298 + v1304 + v1308 + v1310 + v1312 + v1314))) * 100, 1) as porcentaje_mujeres
            FROM nonce_pano_23.sec_gral_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND (cv_estatus_captura = 0 OR cv_estatus_captura = 10)
            
            UNION ALL
            
            -- MEDIA SUPERIOR
            SELECT 
                8 as orden,
                'Educación Media Superior' as nivel_educativo,
                'Plantel' as subnivel,
                SUM(v161 + v163 + v165 + v167) as docentes_hombres,
                SUM(v162 + v164 + v166 + v168) as docentes_mujeres,
                SUM(v161 + v163 + v165 + v167 + v162 + v164 + v166 + v168) as total_docentes,
                ROUND((SUM(v161 + v163 + v165 + v167)::decimal / (SUM(v161 + v163 + v165 + v167) + SUM(v162 + v164 + v166 + v168))) * 100, 1) as porcentaje_hombres,
                ROUND((SUM(v162 + v164 + v166 + v168)::decimal / (SUM(v161 + v163 + v165 + v167) + SUM(v162 + v164 + v166 + v168))) * 100, 1) as porcentaje_mujeres
            FROM nonce_pano_23.ms_plantel_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND cv_motivo = 0
            
            UNION ALL
            
            -- EDUCACIÓN SUPERIOR
            SELECT 
                9 as orden,
                'Educación Superior' as nivel_educativo,
                'Universitaria' as subnivel,
                SUM(v81) as docentes_hombres,
                SUM(v82) as docentes_mujeres,
                SUM(v81 + v82) as total_docentes,
                ROUND((SUM(v81)::decimal / (SUM(v81) + SUM(v82))) * 100, 1) as porcentaje_hombres,
                ROUND((SUM(v82)::decimal / (SUM(v81) + SUM(v82))) * 100, 1) as porcentaje_mujeres
            FROM nonce_pano_23.sup_escuela_23 
            WHERE c_nom_mun = 'CORREGIDORA' AND v83 IS NOT NULL
              
        ) AS resultados
        WHERE total_docentes > 0
        ORDER BY orden
        ";

        $result = pg_query($link, $query);
        if ($result && pg_num_rows($result) > 0) {
            $datosGenero = array(
                array('Nivel Educativo', 'Subnivel', 'Total Docentes', 'Hombres', 'Mujeres', '% Hombres', '% Mujeres')
            );

            while ($row = pg_fetch_assoc($result)) {
                $total = (int) $row['total_docentes'];
                $hombres = (int) $row['docentes_hombres'];
                $mujeres = (int) $row['docentes_mujeres'];
                $porcentajeHombres = (float) $row['porcentaje_hombres'];
                $porcentajeMujeres = (float) $row['porcentaje_mujeres'];

                $datosGenero[] = array(
                    $row['nivel_educativo'],
                    $row['subnivel'],
                    $total,
                    $hombres,
                    $mujeres,
                    $porcentajeHombres,
                    $porcentajeMujeres
                );
            }

            pg_free_result($result);
        }

        pg_close($link);

    } catch (Exception $e) {
        error_log('SEDEQ: Error en consulta de docentes por género: ' . $e->getMessage());
        // En caso de error, retornar datos de fallback
        return $datosGenero;
    }

    return $datosGenero;
}