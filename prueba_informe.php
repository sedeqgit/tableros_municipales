<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// ======================================================
// 🔹 DATOS SIMULADOS (como si vinieran de la base de datos)
// ======================================================
$data = [
    "municipio" => "Querétaro",
    "entidad" => "Querétaro",
    "anio" => 2025,
    "poblacion_total" => 1186345,
    "poblacion_mujeres" => 604908,
    "poblacion_hombres" => 581345,
    "poblacion_0_14" => 276794,
    "poblacion_15_29" => 333629,
    "poblacion_65_mas" => 931,
    "pobreza_moderada" => "40.5%",
    "pobreza_extrema" => "2.1%",
    "vulnerables_carencia" => "29.7%",
    "vulnerables_ingresos" => "9.7%",
    "no_pobres" => "17.6%",
    "gini" => "0.390",
    "ingreso_promedio" => "$12,974",
    "empleo_canasta" => "89.9%",
    "rezago_social" => "Muy bajo",
    "lugar_rezago" => "2,402 de 2,469",
    "asignacion_faismum" => "$185.832 mdp"
];

// ======================================================
// 🔹 GRÁFICO BASE64 (pirámide poblacional simulada)
// ======================================================
$chart = file_get_contents("https://quickchart.io/chart?c={
  type:'bar',
  data:{
    labels:['0-4','5-9','10-14','15-19','20-24','25-29','30-34','35-39','40-44','45-49','50-54','55-59','60+'],
    datasets:[
      {label:'Hombres',backgroundColor:'#004C97',data:[54,58,55,46,37,33,32,27,25,23,22,20,19]},
      {label:'Mujeres',backgroundColor:'#B3D1F0',data:[61,39,41,42,45,51,55,54,48,42,41,37,32]}
    ]
  },
  options:{
    plugins:{legend:{position:'bottom'}},
    scales:{x:{stacked:true},y:{stacked:true}}
  }
}");
$chart_base64 = 'data:image/png;base64,' . base64_encode($chart);

// ======================================================
// 🔹 CONFIGURACIÓN DOMPDF
// ======================================================
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// ======================================================
// 🔹 PLANTILLA HTML COMPLETA
// ======================================================
$html = '
<html>
<head>
<meta charset="utf-8">
<style>
body {
  font-family: Arial, sans-serif;
  margin: 40px;
  color: #222;
}
.header {
  border-bottom: 3px solid #004C97;
  padding-bottom: 5px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.header img {
  height: 50px;
}
h1 {
  font-size: 18px;
  color: #004C97;
  text-align: center;
  margin: 5px 0;
}
h2 {
  font-size: 14px;
  color: #004C97;
  border-bottom: 1px solid #004C97;
  margin-top: 25px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 8px;
  font-size: 11px;
}
th, td {
  border: 1px solid #ccc;
  padding: 6px 8px;
}
th {
  background-color: #e5eef7;
}
.columns {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}
.column {
  width: 48%;
}
.badge {
  background: #004C97;
  color: white;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
}
.footer {
  border-top: 1px solid #ccc;
  text-align: center;
  margin-top: 30px;
  padding-top: 5px;
  font-size: 9px;
  color: #555;
}
img.chart {
  display: block;
  margin: 10px auto;
  max-width: 95%;
}
</style>
</head>
<body>

<!-- ENCABEZADO -->
<div class="header">
  <img src="https://upload.wikimedia.org/wikipedia/commons/1/1f/Gobierno_de_México_logo.svg" alt="Gobierno de México">
  <div style="flex:1; text-align:center;">
    <h1>INFORME ANUAL SOBRE LA SITUACIÓN DE POBREZA Y REZAGO SOCIAL ' . $data["anio"] . '</h1>
    <p style="font-size:12px;">Secretaría de Bienestar · Dirección General de Planeación y Análisis</p>
  </div>
</div>

<p style="text-align:center; font-size:13px;">
  <strong>Municipio:</strong> ' . $data["municipio"] . ' &nbsp;&nbsp; | &nbsp;&nbsp; 
  <strong>Entidad Federativa:</strong> ' . $data["entidad"] . '
</p>

<!-- SECCIÓN I -->
<h2>I. Información sociodemográfica y económica</h2>

<div class="columns">
  <div class="column">
    <table>
      <tr><th>Indicador</th><th>Valor</th></tr>
      <tr><td>Población total</td><td>' . number_format($data["poblacion_total"]) . '</td></tr>
      <tr><td>Mujeres</td><td>' . number_format($data["poblacion_mujeres"]) . '</td></tr>
      <tr><td>Hombres</td><td>' . number_format($data["poblacion_hombres"]) . '</td></tr>
      <tr><td>Niñas y niños (0-14 años)</td><td>' . number_format($data["poblacion_0_14"]) . '</td></tr>
      <tr><td>Jóvenes (15-29 años)</td><td>' . number_format($data["poblacion_15_29"]) . '</td></tr>
      <tr><td>Adultos mayores (65+)</td><td>' . number_format($data["poblacion_65_mas"]) . '</td></tr>
    </table>
  </div>
  <div class="column">
    <img class="chart" src="' . $chart_base64 . '" alt="Pirámide poblacional">
    <p style="text-align:center; font-size:10px;">Pirámide poblacional simulada 2024</p>
  </div>
</div>

<!-- SECCIÓN II -->
<h2>II. Indicadores de pobreza</h2>
<table>
<tr><th>Indicador</th><th>Valor</th></tr>
<tr><td>Pobreza moderada</td><td>' . $data["pobreza_moderada"] . '</td></tr>
<tr><td>Pobreza extrema</td><td>' . $data["pobreza_extrema"] . '</td></tr>
<tr><td>Vulnerables por carencia social</td><td>' . $data["vulnerables_carencia"] . '</td></tr>
<tr><td>Vulnerables por ingresos</td><td>' . $data["vulnerables_ingresos"] . '</td></tr>
<tr><td>No pobres y no vulnerables</td><td>' . $data["no_pobres"] . '</td></tr>
<tr><td>Coeficiente de Gini</td><td>' . $data["gini"] . '</td></tr>
<tr><td>Ingreso laboral promedio mensual</td><td>' . $data["ingreso_promedio"] . '</td></tr>
<tr><td>Población con empleo que accede a canasta básica</td><td>' . $data["empleo_canasta"] . '</td></tr>
</table>

<!-- SECCIÓN III -->
<h2>III. Indicadores de rezago social</h2>
<table>
<tr><th>Indicador</th><th>Valor</th></tr>
<tr><td>Grado de rezago social</td><td><span class="badge">' . $data["rezago_social"] . '</span></td></tr>
<tr><td>Lugar nacional</td><td>' . $data["lugar_rezago"] . '</td></tr>
</table>

<!-- SECCIÓN IV -->
<h2>IV. Planeación FAISMUN ' . $data["anio"] . '</h2>
<p>Asignación presupuestal del municipio: <strong>' . $data["asignacion_faismum"] . '</strong></p>

<!-- PIE -->
<div class="footer">
  Fuente: Simulación basada en el formato oficial de la Secretaría de Bienestar (' . $data["anio"] . ').<br>
  Generado automáticamente por el sistema de informes municipales.
</div>

</body>
</html>
';

// ======================================================
// 🔹 GENERAR PDF
// ======================================================
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream("informe_pobreza_full.pdf", ["Attachment" => false]);
?>