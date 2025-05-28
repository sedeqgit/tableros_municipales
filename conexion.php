<?php
/**
 * Archivo de conexión a la base de datos PostgreSQL
 * Sistema de Dashboard Estadístico - SEDEQ
 */

/**
 * Función para establecer la conexión a la base de datos PostgreSQL
 * Verifica si las funciones de PostgreSQL están disponibles
 * 
 * @return resource|null Recurso de conexión a PostgreSQL o null si no están disponibles las funciones
 */
function Conectarse()
{
    // Verificar si las funciones de PostgreSQL están disponibles
    if (!function_exists('pg_connect')) {
        return null;
    }

    $link_conexion = pg_connect("host=localhost port=5433 dbname=bd_nonce user=postgres password=postgres options='--client_encoding=LATIN1'")
        or die('No se ha podido conectar: ' . pg_last_error());
    return $link_conexion;
}

/**
 * Función para obtener los datos educativos desde la base de datos
 * Si no hay conexión a la base de datos, devuelve datos de ejemplo
 * 
 * @return array Arreglo con los datos educativos organizados
 */
function obtenerDatosEducativos()
{    // Datos por defecto en caso de que no se pueda conectar a la BD
    $datosEducativos = array(
        array('Tipo Educativo', 'Escuelas', 'Alumnos'),
        array('Inicial (Escolarizado)', 5, 150),
        array('Inicial (No Escolarizado)', 8, 240),
        array('Especial (CAM)', 3, 120),
        array('Preescolar', 120, 12000),
        array('Primaria', 180, 45000),
        array('Secundaria', 95, 28000),
        array('Media Superior', 60, 19000),
        array('Superior', 25, 15000)
    );

    // Verificar si las funciones de PostgreSQL están disponibles
    if (!function_exists('pg_connect')) {
        return $datosEducativos;
    }

    // Establecer conexión a la BD
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


?>