<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Dashboard - Sistema de Exportación</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="demo-dashboard.css" rel="stylesheet">

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="header-section">
                    <h1><i class="fas fa-chart-bar me-3"></i>Demo Dashboard - Estudiantes por Nivel</h1>
                    <p class="text-muted">Demostración del sistema centralizado de exportación</p>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtros</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="filtroAnio" class="form-label">Año:</label>
                                <select id="filtroAnio" class="form-select">
                                    <option value="">Todos los años</option>
                                    <option value="2020">2020</option>
                                    <option value="2021">2021</option>
                                    <option value="2022">2022</option>
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="filtroNivel" class="form-label">Nivel:</label>
                                <select id="filtroNivel" class="form-select">
                                    <option value="">Todos los niveles</option>
                                    <option value="Preescolar">Preescolar</option>
                                    <option value="Primaria">Primaria</option>
                                    <option value="Secundaria">Secundaria</option>
                                    <option value="Bachillerato">Bachillerato</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button id="btnAplicarFiltros" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Aplicar Filtros
                                </button>
                                <button id="btnLimpiarFiltros" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-eraser me-2"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-download me-2"></i>Exportar Datos</h5>
                        <div class="export-buttons">
                            <button id="btnExportPNG" class="btn btn-success me-2">
                                <i class="fas fa-image me-2"></i>Exportar PNG
                            </button>
                            <button id="btnExportExcel" class="btn btn-info">
                                <i class="fas fa-file-excel me-2"></i>Exportar Excel
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Los archivos se descargarán automáticamente
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-column me-2"></i>
                            Gráfico de Estudiantes por Nivel Educativo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="chart_div" style="height: 500px;"></div>
                        <div id="loading" class="text-center d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Generando gráfico...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 id="totalEstudiantes" class="stats-number">0</h3>
                        <p class="stats-label">Total Estudiantes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <h3 id="totalNiveles" class="stats-number">0</h3>
                        <p class="stats-label">Niveles Educativos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <h3 id="totalAnios" class="stats-number">0</h3>
                        <p class="stats-label">Años de Datos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 id="promedioAnual" class="stats-number">0</h3>
                        <p class="stats-label">Promedio Anual</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="footer-section">
                    <p class="text-center text-muted">
                        <i class="fas fa-flask me-2"></i>
                        Demo del Sistema de Exportación Centralizado - ExportManager
                    </p>
                </div>
            </div>
        </div>
    </div> <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SheetJS for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- html2canvas for PNG export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- Export Manager con Anotaciones -->
    <script src="js/export-manager-annotations.js"></script>
    <!-- Page specific JS -->
    <script src="demo-dashboard.js"></script>
</body>

</html>