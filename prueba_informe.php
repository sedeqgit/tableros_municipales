<?php
// ----------------------
// Datos simulados (PHP)
// ----------------------
$municipio = [
  "codigo" => "22014",
  "nombre" => "Querétaro",
  "entidad" => "Querétaro",
  "anio" => 2025,
  "poblacion_total" => 1186345,
  "poblacion_mujeres" => 604908,
  "poblacion_hombres" => 581345,
  "poblacion_0_14" => 276794,
  "poblacion_15_29" => 333629,
  "poblacion_65_mas" => 931,
  "pobreza_moderada" => 40.5,
  "pobreza_extrema" => 2.1,
  "vulnerables_carencia" => 29.7,
  "vulnerables_ingresos" => 9.7,
  "no_pobres" => 17.6,
  "gini" => 0.390,
  "ingreso_promedio" => 12974,
  "empleo_canasta" => 89.9,
  "rezago_social" => "Muy bajo",
  "lugar_rezago" => "2,402 de 2,469",
  "asignacion_faismum" => 185.832, // mdp
];

// Datos para pirámide (simulados, coincidiendo con el PDF)
$piramide_labels = ["0-4", "5-9", "10-14", "15-19", "20-24", "25-29", "30-34", "35-39", "40-44", "45-49", "50-54", "55-59", "60+"];
$hombres = [-54, -58, -55, -46, -37, -33, -32, -27, -25, -23, -22, -20, -19]; // negativos para la izquierda en la gráfica
$mujeres = [61, 39, 41, 42, 45, 51, 55, 54, 48, 42, 41, 37, 32];

// Datos FAISMUN (rubros y valores tomados de PDF como referencia)
$faismun_labels = ["Urbanización", "Mejoramiento de vivienda", "Infraestructura de salud", "Infraestructura educativa", "Electrificación", "Drenaje y letrinas", "Alcantarillado", "Agua potable"];
$faismun_values = [64.243, 62.634, 51.187, 45.710, 10.712, 43.236, 56.675, 68.860]; // mdp parciales (simulados, aparecen en PDF) :contentReference[oaicite:5]{index=5}

// Datos adicionales para gráficas
$pobreza_comp = [$municipio["pobreza_moderada"], $municipio["pobreza_extrema"], $municipio["vulnerables_carencia"], $municipio["no_pobres"]];
$pobreza_labels = ["Pobreza moderada", "Pobreza extrema", "Vulnerables por carencia", "No pobres"];

$trend_years = [2022, 2023, 2024];
$trend_values = [170, 180, 185.832]; // mdp asignados FAISMUN (simulado para tendencia)
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Informe Municipal — <?php echo htmlspecialchars($municipio['nombre']); ?> <?php echo $municipio['anio']; ?>
  </title>

  <!-- Chart.js y html2pdf desde CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <!-- Estilos (intentos de replicar el PDF con más diseño) -->
  <style>
    :root {
      --primary: #004C97;
      --muted: #6b7280;
      --light: #e6eef8;
      --card-bg: #ffffff;
      --page-bg: #f3f5f7;
      --accent: #B3D1F0;
      --shadow: rgba(6, 21, 34, 0.08);
    }

    body {
      font-family: "Inter", "Arial", sans-serif;
      background: var(--page-bg);
      margin: 0;
      padding: 24px;
      color: #222;
    }

    .wrap {
      max-width: 1100px;
      margin: auto;
    }

    .toolbar {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-bottom: 12px;
    }

    button.cta {
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      box-shadow: 0 6px 18px var(--shadow);
    }

    button.cta:active {
      transform: translateY(1px);
    }

    /* Informe */
    #informe {
      background: var(--card-bg);
      padding: 36px;
      border-radius: 10px;
      box-shadow: 0 6px 30px var(--shadow);
      position: relative;
      overflow: hidden;
    }

    /* Marca de agua (escudo) */
    #informe::before {
      content: "";
      position: absolute;
      inset: 0;
      background: url('https://upload.wikimedia.org/wikipedia/commons/0/0d/Escudo_Nacional_de_M%C3%A9xico.svg') center/240px no-repeat;
      opacity: 0.05;
      pointer-events: none;
    }

    header.report-header {
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 4px solid var(--primary);
      padding-bottom: 12px;
      margin-bottom: 18px;
      position: relative;
      z-index: 2;
    }

    header img.logo {
      height: 56px;
    }

    header .title {
      flex: 1;
      text-align: center;
    }

    header h1 {
      color: var(--primary);
      margin: 0;
      font-size: 20px;
      letter-spacing: 0.2px;
    }

    header p.sub {
      margin: 2px 0 0;
      font-size: 12px;
      color: var(--muted);
    }

    .meta {
      display: flex;
      gap: 12px;
      justify-content: center;
      margin-top: 10px;
      z-index: 2;
      position: relative;
    }

    .kpis {
      display: flex;
      gap: 12px;
      margin-top: 18px;
      flex-wrap: wrap;
      z-index: 2;
    }

    .kpi {
      flex: 1;
      min-width: 180px;
      background: linear-gradient(180deg, #fff, #fbfdff);
      border-radius: 8px;
      padding: 12px;
      border: 1px solid #eef3fb;
      box-shadow: 0 6px 16px rgba(2, 6, 23, 0.03);
    }

    .kpi .label {
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 6px;
    }

    .kpi .value {
      font-size: 18px;
      font-weight: 700;
      color: var(--primary);
    }

    /* Layout main */
    .main {
      display: flex;
      gap: 22px;
      margin-top: 18px;
      z-index: 2;
    }

    .col-left {
      width: 58%;
    }

    .col-right {
      width: 42%;
    }

    .card {
      background: transparent;
      border-radius: 8px;
      margin-bottom: 14px;
    }

    .card .card-body {
      background: #fff;
      padding: 14px;
      border-radius: 8px;
      border: 1px solid #eef3fb;
      box-shadow: 0 6px 14px rgba(3, 12, 34, 0.03);
    }

    table.ind {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    table.ind th,
    table.ind td {
      padding: 8px;
      border-bottom: 1px dashed #e9eef6;
      text-align: left;
    }

    table.ind th {
      background: var(--light);
      color: var(--primary);
      font-weight: 600;
      padding-left: 10px;
    }

    .small {
      font-size: 12px;
      color: var(--muted);
    }

    /* FAISMUN bar list */
    .faism-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .faism-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }

    .bar {
      height: 10px;
      background: #e9f2ff;
      border-radius: 6px;
      flex: 1;
      overflow: hidden;
      margin-left: 12px
    }

    .bar>i {
      display: block;
      height: 100%;
      background: var(--primary);
      border-radius: 6px;
    }

    /* footer */
    .report-footer {
      margin-top: 22px;
      border-top: 1px solid #e6eef5;
      padding-top: 12px;
      color: var(--muted);
      font-size: 12px;
      text-align: center;
    }

    /* responsive */
    @media(max-width:880px) {
      .main {
        flex-direction: column;
      }

      .col-left,
      .col-right {
        width: 100%;
      }

      .kpis {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="toolbar">
      <button class="cta" id="btnPDF">Descargar informe (PDF)</button>
    </div>

    <div id="informe">
      <header class="report-header">
        <img class="logo" src="https://upload.wikimedia.org/wikipedia/commons/1/1f/Gobierno_de_México_logo.svg"
          alt="Logo">
        <div class="title">
          <h1>INFORME ANUAL SOBRE LA SITUACIÓN DE POBREZA Y REZAGO SOCIAL <?php echo $municipio['anio']; ?></h1>
          <p class="sub">Dirección General de Planeación y Análisis — Secretaría de Bienestar</p>
        </div>
        <div style="width:120px;text-align:right;">
          <div style="font-size:12px;color:#666;">Código</div>
          <div style="font-weight:700;color:var(--primary);font-size:16px;"><?php echo $municipio['codigo']; ?></div>
        </div>
      </header>

      <div class="meta">
        <div class="small"><strong>Municipio:</strong> <?php echo htmlspecialchars($municipio['nombre']); ?></div>
        <div class="small"><strong>Entidad:</strong> <?php echo htmlspecialchars($municipio['entidad']); ?></div>
        <div class="small"><strong>Población (2024):</strong>
          <?php echo number_format($municipio['poblacion_total']); ?></div>
      </div>

      <!-- KPI cards -->
      <div class="kpis">
        <div class="kpi">
          <div class="label">Pobreza moderada</div>
          <div class="value"><?php echo $municipio['pobreza_moderada']; ?> %</div>
          <div class="small">Porcentaje de población en pobreza moderada</div>
        </div>
        <div class="kpi">
          <div class="label">Pobreza extrema</div>
          <div class="value"><?php echo $municipio['pobreza_extrema']; ?> %</div>
          <div class="small">Porcentaje de población en pobreza extrema</div>
        </div>
        <div class="kpi">
          <div class="label">Ingreso laboral promedio</div>
          <div class="value">$ <?php echo number_format($municipio['ingreso_promedio']); ?></div>
          <div class="small">Ingreso mensual (promedio)</div>
        </div>
        <div class="kpi">
          <div class="label">Coeficiente de Gini</div>
          <div class="value"><?php echo number_format($municipio['gini'], 3); ?></div>
          <div class="small">Medida de desigualdad</div>
        </div>
      </div>

      <!-- main layout -->
      <div class="main">
        <div class="col-left">
          <!-- PIRÁMIDE -->
          <div class="card">
            <div class="card-body">
              <h3 style="margin:0 0 8px; color:var(--primary)">Pirámide de población</h3>
              <div style="display:flex;gap:12px;align-items:center;">
                <canvas id="piramide" style="width:100%;height:320px"></canvas>
              </div>
              <div class="small" style="margin-top:6px;">Pirámide poblacional del municipio o demarcación (2024).
                Referencia visual tomada del informe original del municipio :contentReference[oaicite:6]{index=6}.</div>
            </div>
          </div>

          <!-- Indicadores tabla -->
          <div class="card">
            <div class="card-body">
              <h3 style="margin:0 0 8px; color:var(--primary)">Indicadores de pobreza y empleo</h3>
              <table class="ind">
                <tr>
                  <th>Indicador</th>
                  <th>Municipio</th>
                  <th>Entidad (ej.)</th>
                </tr>
                <tr>
                  <td>Población con empleo que accede a canasta básica</td>
                  <td><?php echo $municipio['empleo_canasta']; ?> %</td>
                  <td>89.9 %</td>
                </tr>
                <tr>
                  <td>Vulnerables por carencia social</td>
                  <td><?php echo $municipio['vulnerables_carencia']; ?> %</td>
                  <td>—</td>
                </tr>
                <tr>
                  <td>Vulnerables por ingresos</td>
                  <td><?php echo $municipio['vulnerables_ingresos']; ?> %</td>
                  <td>—</td>
                </tr>
                <tr>
                  <td>No pobres y no vulnerables</td>
                  <td><?php echo $municipio['no_pobres']; ?> %</td>
                  <td>—</td>
                </tr>
              </table>
              <div class="small" style="margin-top:8px;">Notas: Cálculos y definiciones basadas en el formato oficial
                del Informe Anual (ver original) :contentReference[oaicite:7]{index=7}.</div>
            </div>
          </div>

        </div>

        <div class="col-right">
          <!-- DONUT pobreza -->
          <div class="card">
            <div class="card-body">
              <h3 style="margin:0 0 8px; color:var(--primary)">Composición de la pobreza</h3>
              <canvas id="donut" style="width:100%;height:220px"></canvas>
              <div class="small" style="margin-top:8px;">Distribución porcentual entre pobreza moderada, extrema,
                vulnerables y no pobres.</div>
            </div>
          </div>

          <!-- FAISMUN -->
          <div class="card">
            <div class="card-body">
              <h3 style="margin:0 0 8px; color:var(--primary)">Planeación FAISMUN (Asignación 2024)</h3>
              <div style="display:flex;gap:12px;align-items:center;">
                <canvas id="faismBar" style="width:100%;height:160px"></canvas>
              </div>
              <div style="margin-top:10px;" class="faism-list">
                <?php
                $total = array_sum($faismun_values);
                for ($i = 0; $i < count($faismun_labels); $i++) {
                  $label = $faismun_labels[$i];
                  $val = $faismun_values[$i];
                  $pct = round(($val / $total) * 100, 1);
                  echo '<div class="faism-item"><div style="font-size:13px;">' . $label . ' <span class="small">(' . number_format($val, 3) . ' mdp)</span></div><div style="display:flex;align-items:center;gap:8px;"><div style="font-weight:700;color:var(--primary);">' . $pct . '%</div><div class="bar"><i style="width:' . max($pct, 1) . '%"></i></div></div></div>';
                }
                ?>
              </div>
              <div class="small" style="margin-top:8px;">Asignación tomada del informe municipal (valores ilustrativos)
                :contentReference[oaicite:8]{index=8}.</div>
            </div>
          </div>

          <!-- Tendencia FAISMUN -->
          <div class="card">
            <div class="card-body">
              <h3 style="margin:0 0 8px; color:var(--primary)">Tendencia gasto FAISMUN (2022-2024)</h3>
              <canvas id="trend" style="width:100%;height:120px"></canvas>
              <div class="small" style="margin-top:8px;">Evolución de la asignación presupuestal (millones de pesos).
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="report-footer">
        Fuente: Informe Anual sobre la Situación de Pobreza y Rezago Social — Simulación basada en el formato oficial
        (2025). Para detalles y anexos consultar el documento original. :contentReference[oaicite:9]{index=9}
      </div>

    </div>
  </div>

  <script>
    // ---------- Datos JS (desde PHP) ----------
    const piramideLabels = <?php echo json_encode($piramide_labels); ?>;
    const hombres = <?php echo json_encode($hombres); ?>;
    const mujeres = <?php echo json_encode($mujeres); ?>;

    const pobrezaComp = <?php echo json_encode($pobreza_comp); ?>;
    const pobrezaLabels = <?php echo json_encode($pobreza_labels); ?>;

    const faismLabels = <?php echo json_encode($faismun_labels); ?>;
    const faismValues = <?php echo json_encode($faismun_values); ?>;

    const trendYears = <?php echo json_encode($trend_years); ?>;
    const trendValues = <?php echo json_encode($trend_values); ?>;

    // ---------- Chart: Pirámide ----------
    const ctxP = document.getElementById('piramide').getContext('2d');
    new Chart(ctxP, {
      type: 'bar',
      data: {
        labels: piramideLabels,
        datasets: [
          { label: 'Hombres', data: hombres, backgroundColor: '#004C97' },
          { label: 'Mujeres', data: mujeres, backgroundColor: '#B3D1F0' }
        ]
      },
      options: {
        indexAxis: 'y',
        scales: {
          x: {
            stacked: true,
            ticks: {
              callback: (v) => Math.abs(v)
            }
          },
          y: { stacked: true }
        },
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: ctx => `${ctx.dataset.label}: ${Math.abs(ctx.raw)} %`
            }
          }
        }
      }
    });

    // ---------- Chart: Donut pobreza ----------
    const ctxD = document.getElementById('donut').getContext('2d');
    new Chart(ctxD, {
      type: 'doughnut',
      data: {
        labels: pobrezaLabels,
        datasets: [{
          data: pobrezaComp,
          backgroundColor: ['#004C97', '#173A5E', '#F59E0B', '#A3A3A3']
        }]
      },
      options: {
        plugins: { legend: { position: 'bottom' } },
        cutout: '55%'
      }
    });

    // ---------- Chart: FAISMUN barras horizontales ----------
    const ctxF = document.getElementById('faismBar').getContext('2d');
    new Chart(ctxF, {
      type: 'bar',
      data: {
        labels: faismLabels,
        datasets: [{
          label: 'Asignación (mdp)',
          data: faismValues,
          backgroundColor: faismValues.map((v, i) => i % 2 ? '#B3D1F0' : '#004C97')
        }]
      },
      options: {
        indexAxis: 'y',
        scales: { x: { beginAtZero: true } },
        plugins: { legend: { display: false } }
      }
    });

    // ---------- Chart: Trend ----------
    const ctxT = document.getElementById('trend').getContext('2d');
    new Chart(ctxT, {
      type: 'line',
      data: {
        labels: trendYears,
        datasets: [{
          label: 'Asignación FAISMUN (mdp)',
          data: trendValues,
          borderColor: '#004C97',
          backgroundColor: 'rgba(0,76,151,0.08)',
          fill: true,
          tension: 0.25,
          pointRadius: 4
        }]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false } } }
    });

    // ---------- Export to PDF (html2pdf) ----------
    document.getElementById('btnPDF').addEventListener('click', () => {
      const element = document.getElementById('informe');
      // Ajustes: margen, calidad, formato carta
      const opt = {
        margin: [8, 8, 8, 8],
        filename: 'informe_<?php echo strtolower($municipio["nombre"]); ?>_<?php echo $municipio["anio"]; ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, allowTaint: true },
        jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' }
      };
      // Antes de exportar, forzamos redraw de charts (evita lienzos vacíos en PDF)
      // (Chart.js actualiza automáticamente, pero dejamos un pequeño timeout)
      setTimeout(() => html2pdf().set(opt).from(element).save(), 250);
    });
  </script>
</body>

</html>