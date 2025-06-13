<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Ventas - Sistema de Exportación</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="./css/demo-ventas.css" rel="stylesheet">

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="header-section">
                    <h1><i class="fas fa-chart-pie me-3"></i>Demo Ventas - Análisis por Categoría</h1>
                    <p class="text-muted">Demostración del sistema centralizado con gráfico circular</p>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtros</h5>
                        <div class="mb-3">
                            <label for="filtroPeriodo" class="form-label">Período:</label>
                            <select id="filtroPeriodo" class="form-select">
                                <option value="">Todos los períodos</option>
                                <option value="Q1-2024">Q1 2024</option>
                                <option value="Q2-2024">Q2 2024</option>
                                <option value="Q3-2024">Q3 2024</option>
                                <option value="Q4-2024">Q4 2024</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filtroMinVenta" class="form-label">Venta mínima:</label>
                            <input type="number" id="filtroMinVenta" class="form-control" placeholder="0" min="0"
                                step="1000">
                        </div>
                        <button id="btnAplicarFiltros" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Aplicar
                        </button>
                        <button id="btnLimpiarFiltros" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-eraser me-2"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-cog me-2"></i>Configuración</h5>
                        <div class="mb-3">
                            <label for="tipoGrafico" class="form-label">Tipo de gráfico:</label>
                            <select id="tipoGrafico" class="form-select">
                                <option value="pie">Gráfico Circular</option>
                                <option value="donut">Gráfico Dona</option>
                                <option value="bar">Gráfico de Barras</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="mostrar3D">
                            <label class="form-check-label" for="mostrar3D">
                                Vista 3D
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="mostrarPorcentajes" checked>
                            <label class="form-check-label" for="mostrarPorcentajes">
                                Mostrar porcentajes
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-download me-2"></i>Exportar</h5>
                        <div class="export-buttons mb-3">
                            <button id="btnExportPNG" class="btn btn-success">
                                <i class="fas fa-image me-2"></i>PNG
                            </button>
                            <button id="btnExportExcel" class="btn btn-info">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </button>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incluirDetalles" checked>
                            <label class="form-check-label" for="incluirDetalles">
                                Incluir detalles en Excel
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>
                            Ventas por Categoría de Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="chart_div" style="height: 450px;"></div>
                        <div id="loading" class="text-center d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Actualizando gráfico...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Top Productos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="topProductos" class="top-productos-list">
                            <!-- Se llena dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Stats -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>
                            Detalle de Ventas por Categoría
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaDetalle" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Ventas ($)</th>
                                        <th>Participación (%)</th>
                                        <th>Productos</th>
                                        <th>Promedio/Producto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 id="totalVentas" class="stats-number">$0</h3>
                        <p class="stats-label">Ventas Totales</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3 id="totalCategorias" class="stats-number">0</h3>
                        <p class="stats-label">Categorías</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h3 id="categoriaTop" class="stats-number">-</h3>
                        <p class="stats-label">Categoría Líder</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 id="promedioCategoria" class="stats-number">$0</h3>
                        <p class="stats-label">Promedio/Categoría</p>
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
                        Demo Ventas - Validación ExportManager con gráfico circular
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
    <script src="./js/demo-ventas.js"></script>
</body>

</html>