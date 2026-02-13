<?php
/**
 * =============================================================================
 * FUNCIONES HELPER COMPARTIDAS - SISTEMA SEDEQ
 * =============================================================================
 *
 * Este archivo centraliza funciones utilitarias usadas en múltiples páginas
 * del sistema para evitar duplicación de código.
 *
 * @package SEDEQ_Core
 * @subpackage Helpers
 * @version 1.0
 */

/**
 * Formatea nombres de municipios para display en formato título.
 * Convierte de MAYÚSCULAS (formato interno) a Formato Título para mostrar.
 *
 * @param string $municipio Nombre del municipio en mayúsculas
 * @return string Nombre formateado en formato título
 */
function formatearNombreMunicipio($municipio)
{
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');
    $formatted = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $formatted);
    return $formatted;
}

/**
 * Formatea porcentajes con un número fijo de decimales.
 * Usa coma como separador de miles.
 *
 * @param float $value Valor numérico a formatear
 * @param int $decimals Número de decimales (por defecto 2)
 * @return string Valor formateado
 */
function formatPercent($value, $decimals = 2)
{
    return number_format((float) $value, $decimals, '.', ',');
}

/**
 * Determina el orden de visualización de subniveles educativos.
 * Usado para ordenar tablas de docentes y alumnos por nivel/subnivel.
 *
 * @param string $nivel Nivel educativo
 * @param string $subnivel Subnivel educativo
 * @return int Número de orden (1-16)
 */
function obtenerOrdenSubnivel($nivel, $subnivel)
{
    $nivel = strtolower($nivel);
    $subnivel = strtolower($subnivel);

    // INICIAL ESCOLARIZADA
    if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'escolarizada') !== false)
        return 1;

    // INICIAL NO ESCOLARIZADA
    if (strpos($nivel, 'inicial') !== false && strpos($nivel, 'no') !== false)
        return 2;

    // ESPECIAL / CAM
    if (strpos($nivel, 'especial') !== false || strpos($nivel, 'cam') !== false)
        return 3;

    // PREESCOLAR
    if (strpos($nivel, 'preescolar') !== false) {
        if (strpos($subnivel, 'general') !== false)
            return 4;
        if (strpos($subnivel, 'comunitario') !== false)
            return 5;
        if (strpos($subnivel, 'indígena') !== false || strpos($subnivel, 'indigena') !== false)
            return 6;
    }

    // PRIMARIA
    if (strpos($nivel, 'primaria') !== false) {
        if (strpos($subnivel, 'general') !== false)
            return 7;
        if (strpos($subnivel, 'comunitario') !== false)
            return 8;
        if (strpos($subnivel, 'indígena') !== false || strpos($subnivel, 'indigena') !== false)
            return 9;
    }

    // SECUNDARIA
    if (strpos($nivel, 'secundaria') !== false) {
        if (strpos($subnivel, 'comunitario') !== false)
            return 10;
        if (strpos($subnivel, 'general') !== false)
            return 11;
        if (strpos($subnivel, 'técnica') !== false || strpos($subnivel, 'tecnica') !== false)
            return 12;
        if (strpos($subnivel, 'telesecundaria') !== false)
            return 13;
    }

    // MEDIA SUPERIOR
    if (strpos($nivel, 'media') !== false || strpos($nivel, 'medio') !== false)
        return 14;

    // SUPERIOR
    if (strpos($nivel, 'superior') !== false)
        return 15;

    return 16;
}

/**
 * Formatea fechas en español.
 *
 * @param string $formato Formato de fecha PHP (por defecto 'd \d\e F \d\e Y')
 * @param int|null $timestamp Timestamp Unix opcional (por defecto fecha actual)
 * @return string Fecha formateada en español
 */
function fechaEnEspanol($formato = 'd \d\e F \d\e Y', $timestamp = null)
{
    if ($timestamp === null) {
        $timestamp = time();
    }

    $meses_espanol = array(
        'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo',
        'April' => 'abril', 'May' => 'mayo', 'June' => 'junio',
        'July' => 'julio', 'August' => 'agosto', 'September' => 'septiembre',
        'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
    );

    $dias_espanol = array(
        'Monday' => 'lunes', 'Tuesday' => 'martes', 'Wednesday' => 'miércoles',
        'Thursday' => 'jueves', 'Friday' => 'viernes', 'Saturday' => 'sábado',
        'Sunday' => 'domingo'
    );

    $fecha = date($formato, $timestamp);

    foreach ($meses_espanol as $ingles => $espanol) {
        $fecha = str_replace($ingles, $espanol, $fecha);
    }

    foreach ($dias_espanol as $ingles => $espanol) {
        $fecha = str_replace($ingles, $espanol, $fecha);
    }

    return $fecha;
}
