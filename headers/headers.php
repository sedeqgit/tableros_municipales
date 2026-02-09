<?php
// headers.php — Configuración de encabezados de seguridad

// Evitar salida previa
if (headers_sent()) {
    die("Error: Los encabezados ya fueron enviados.");
}

// Tipo de contenido por defecto (puedes cambiarlo según el caso)
header("Content-Type: text/html; charset=UTF-8");

// Seguridad contra ataques de clickjacking
header("X-Frame-Options: DENY");

// Evitar que el navegador intente adivinar el tipo de contenido
header("X-Content-Type-Options: nosniff");

// Activar protección básica contra XSS en navegadores antiguos
header("X-XSS-Protection: 1; mode=block");

// =============================================================================
// POLÍTICA DE SEGURIDAD DE CONTENIDO (CSP)
// =============================================================================
// Esta política define qué recursos (scripts, estilos, fuentes, etc.)
// tiene permitido cargar el navegador. Es una capa de seguridad crucial.

$csp = "default-src 'self'; " . // Por defecto, solo permitir recursos del mismo dominio.
    "script-src 'self' 'unsafe-inline' https://www.gstatic.com https://cdnjs.cloudflare.com; " . // Scripts: locales, inline, Google Charts y cdnjs.
    "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.cdnfonts.com; " . // Estilos: locales, inline, FontAwesome y Google/CDN Fonts.
    "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://fonts.cdnfonts.com; " . // Fuentes: locales, Google Fonts, FontAwesome y CDN Fonts.
    "img-src 'self' data: https://queretaro.gob.mx; " . // Imágenes: locales, data URIs y del dominio queretaro.gob.mx.
    "frame-src 'self' https://www.google.com; " . // Iframes: locales y de Google Maps.
    "frame-ancestors 'none'; " . // Evita que el sitio sea embebido en iframes (protección contra clickjacking).
    "form-action 'self';"; // Los formularios solo pueden enviar datos al mismo dominio.
#header("Content-Security-Policy: " . $csp);

// Política de permisos (deshabilita APIs innecesarias)
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

header("Server: SecureApp");
// Control de caché para datos sensibles
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Forzar HTTPS si tu sitio lo soporta
/*if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    // Redirigir a HTTPS
    $httpsUrl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $httpsUrl", true, 301);
    exit;
}*/
