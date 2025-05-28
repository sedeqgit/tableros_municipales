// Esperar a que el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar las gráficas de Google Charts
    google.charts.load('current', {'packages':['corechart', 'sankey']});
    google.charts.setOnLoadCallback(inicializarVisualizaciones);    // Configurar los botones de pestañas en el análisis
    setupTabs();
    
    // Configurar los botones de opciones de visualización
    setupViewOptions();
    

});


// Función para inicializar las visualizaciones
function inicializarVisualizaciones() {
    // Crear el gráfico de barras de eficiencia (para la vista de datos)
    dibujarGraficoEficiencia();
}

// Configurar pestañas
function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase activa de todos los botones
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Añadir clase activa al botón actual
            this.classList.add('active');
            
            // Ocultar todos los contenidos de pestañas
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Mostrar el contenido relacionado con esta pestaña
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
}

// Configurar opciones de visualización
function setupViewOptions() {
    const viewOptions = document.querySelectorAll('input[name="efficiency-view"]');
    
    viewOptions.forEach(option => {
        option.addEventListener('change', function() {
            const visualView = document.getElementById('efficiency-visual-view');
            const dataView = document.getElementById('efficiency-data-view');
            
            if (this.value === 'visual') {
                visualView.classList.add('active-view');
                visualView.classList.remove('inactive-view');
                dataView.classList.add('inactive-view');
                dataView.classList.remove('active-view');
            } else {
                visualView.classList.add('inactive-view');
                visualView.classList.remove('active-view');
                dataView.classList.add('active-view');
                dataView.classList.remove('inactive-view');
            }
        });
    });
    
    // También configurar los botones de la cabecera de la tarjeta
    document.getElementById('mostrarDiagrama').addEventListener('click', function() {
        document.querySelector('input[value="visual"]').checked = true;
        document.querySelector('input[value="visual"]').dispatchEvent(new Event('change'));
        document.getElementById('mostrarDiagrama').classList.add('active');
        document.getElementById('mostrarGrafica').classList.remove('active');
    });
    
    document.getElementById('mostrarGrafica').addEventListener('click', function() {        document.querySelector('input[value="data"]').checked = true;
        document.querySelector('input[value="data"]').dispatchEvent(new Event('change'));
        document.getElementById('mostrarGrafica').classList.add('active');
        document.getElementById('mostrarDiagrama').classList.remove('active');
    });
}

// Inicializar diagrama de flujo educativo
function inicializarDiagramaFlujo() {
    const flowContainer = document.getElementById('flow-container');
    
    // Limpiar contenedor
    flowContainer.innerHTML = '';
    
    // Definir las etapas educativas
    const etapas = [
        { id: 'primaria-inicio', label: 'Primaria 1°', estudiantes: datosEficiencia.primaria.ingreso, ciclo: datosEficiencia.primaria.cicloIngreso, x: 10, y: 140 },
        { id: 'primaria-fin', label: 'Primaria 6°', estudiantes: datosEficiencia.primaria.egreso, ciclo: datosEficiencia.primaria.cicloEgreso, x: 180, y: 140 },
        { id: 'secundaria-inicio', label: 'Secundaria 1°', estudiantes: datosEficiencia.secundaria.ingreso, ciclo: datosEficiencia.secundaria.cicloIngreso, x: 350, y: 140 },
        { id: 'secundaria-fin', label: 'Secundaria 3°', estudiantes: datosEficiencia.secundaria.egreso, ciclo: datosEficiencia.secundaria.cicloEgreso, x: 520, y: 140 },
        { id: 'bachillerato-inicio', label: 'Bachillerato 1°', estudiantes: datosEficiencia.bachillerato.ingreso, ciclo: datosEficiencia.bachillerato.cicloIngreso, x: 690, y: 140 },
        { id: 'bachillerato-fin', label: 'Bachillerato 3°', estudiantes: datosEficiencia.bachillerato.egreso, ciclo: datosEficiencia.bachillerato.cicloEgreso, x: 860, y: 140 },
        { id: 'superior-inicio', label: 'Superior 1°', estudiantes: datosEficiencia.superior.ingreso, ciclo: datosEficiencia.superior.cicloIngreso, x: 1030, y: 140 },
        { id: 'superior-fin', label: 'Graduados', estudiantes: datosEficiencia.superior.egreso, ciclo: datosEficiencia.superior.cicloEgreso, x: 1200, y: 140 }
    ];
    
    // Crear elementos HTML para cada etapa
    etapas.forEach(etapa => {
        const etapaElement = document.createElement('div');
        etapaElement.className = 'education-level';
        etapaElement.id = etapa.id;
        etapaElement.style.left = etapa.x + 'px';
        etapaElement.style.top = etapa.y + 'px';
        etapaElement.innerHTML = `
            <h4>${etapa.label}</h4>
            <p>${etapa.estudiantes} alumnos</p>
            <span class="etapa-ciclo">${etapa.ciclo}</span>
        `;
        flowContainer.appendChild(etapaElement);
    });
    
    // Definir las flechas entre etapas
    const flechas = [
        { desde: 'primaria-inicio', hasta: 'primaria-fin', diferencia: datosEficiencia.primaria.diferencia },
        { desde: 'primaria-fin', hasta: 'secundaria-inicio', diferencia: datosEficiencia.transiciones.primaria_secundaria },
        { desde: 'secundaria-inicio', hasta: 'secundaria-fin', diferencia: datosEficiencia.secundaria.diferencia },
        { desde: 'secundaria-fin', hasta: 'bachillerato-inicio', diferencia: datosEficiencia.transiciones.secundaria_bachillerato },
        { desde: 'bachillerato-inicio', hasta: 'bachillerato-fin', diferencia: datosEficiencia.bachillerato.diferencia },
        { desde: 'bachillerato-fin', hasta: 'superior-inicio', diferencia: datosEficiencia.transiciones.bachillerato_superior },
        { desde: 'superior-inicio', hasta: 'superior-fin', diferencia: datosEficiencia.superior.diferencia }
    ];
    
    // Crear las flechas y etiquetas
    flechas.forEach(flecha => {
        const desdeElement = document.getElementById(flecha.desde);
        const hastaElement = document.getElementById(flecha.hasta);
        
        // Calcular posiciones
        const desdeRect = desdeElement.getBoundingClientRect();
        const hastaRect = hastaElement.getBoundingClientRect();
        
        // Ajustar las coordenadas al contenedor
        const desde = {
            x: parseInt(desdeElement.style.left) + 120,
            y: parseInt(desdeElement.style.top) + 30
        };
        
        const hasta = {
            x: parseInt(hastaElement.style.left),
            y: parseInt(hastaElement.style.top) + 30
        };
        
        // Calcular longitud y ubicación de la flecha
        const length = hasta.x - desde.x;
        
        // Crear elemento de flecha
        const flechaElement = document.createElement('div');
        flechaElement.className = 'flow-arrow';
        flechaElement.style.width = length + 'px';
        flechaElement.style.left = desde.x + 'px';
        flechaElement.style.top = desde.y + 'px';
        
        // Añadir etiqueta con la diferencia
        const labelElement = document.createElement('div');
        labelElement.className = 'arrow-label ' + (flecha.diferencia >= 0 ? 'positive' : 'negative');
        labelElement.innerHTML = flecha.diferencia >= 0 ? '+' + flecha.diferencia : flecha.diferencia;
        labelElement.style.left = (desde.x + (length / 2) - 15) + 'px';
        labelElement.style.top = (desde.y - 40) + 'px';
        
        // Añadir elementos al contenedor
        flowContainer.appendChild(flechaElement);
        flowContainer.appendChild(labelElement);
    });
    
    // Hacer el diagrama responsive (desplazamiento horizontal)
    const diagram = document.querySelector('.educational-flow-diagram');
    let isMouseDown = false;
    let startX, scrollLeft;
    
    diagram.addEventListener('mousedown', (e) => {
        isMouseDown = true;
        diagram.style.cursor = 'grabbing';
        startX = e.pageX - diagram.offsetLeft;
        scrollLeft = diagram.scrollLeft;
    });
    
    diagram.addEventListener('mouseup', () => {
        isMouseDown = false;
        diagram.style.cursor = 'grab';
    });
    
    diagram.addEventListener('mouseleave', () => {
        isMouseDown = false;
        diagram.style.cursor = 'grab';
    });
    
    diagram.addEventListener('mousemove', (e) => {
        if (!isMouseDown) return;
        e.preventDefault();
        const x = e.pageX - diagram.offsetLeft;
        const walk = (x - startX) * 2; // Velocidad de desplazamiento
        diagram.scrollLeft = scrollLeft - walk;
    });
    
    // Configurar desplazamiento inicial
    diagram.scrollLeft = 100; // Desplazar un poco para que se vea mejor
}

// Función para dibujar el gráfico de barras de eficiencia
function dibujarGraficoEficiencia() {
    const chartDiv = document.getElementById('efficiency-chart');
    
    // Preparar los datos para el gráfico
    const data = google.visualization.arrayToDataTable([
        ['Nivel Educativo', 'Ingreso', 'Egreso', { role: 'annotation' }],
        ['Primaria', datosEficiencia.primaria.ingreso, datosEficiencia.primaria.egreso, '+' + datosEficiencia.primaria.diferencia],
        ['Secundaria', datosEficiencia.secundaria.ingreso, datosEficiencia.secundaria.egreso, '+' + datosEficiencia.secundaria.diferencia],
        ['Bachillerato', datosEficiencia.bachillerato.ingreso, datosEficiencia.bachillerato.egreso, datosEficiencia.bachillerato.diferencia],
        ['Educación Superior', datosEficiencia.superior.ingreso, datosEficiencia.superior.egreso, '+' + datosEficiencia.superior.diferencia]
    ]);
    
    // Definir opciones del gráfico
    const isDarkMode = document.body.classList.contains('dark-mode');
    
    const options = {
        title: 'Ingreso vs Egreso por Nivel Educativo',
        titleTextStyle: {
            color: isDarkMode ? '#e0e0e0' : '#333',
            fontSize: 16,
            bold: true
        },
        height: 350,
        legend: { position: 'top', textStyle: { color: isDarkMode ? '#e0e0e0' : '#333' } },
        bar: { groupWidth: '70%' },
        backgroundColor: isDarkMode ? '#2a2a2a' : '#ffffff',
        chartArea: { width: '80%', height: '70%' },
        colors: ['#4285F4', '#34A853'],
        hAxis: {
            title: 'Nivel Educativo',
            titleTextStyle: { color: isDarkMode ? '#e0e0e0' : '#333' },
            textStyle: { color: isDarkMode ? '#e0e0e0' : '#333' }
        },
        vAxis: {
            title: 'Número de Estudiantes',
            titleTextStyle: { color: isDarkMode ? '#e0e0e0' : '#333' },
            textStyle: { color: isDarkMode ? '#e0e0e0' : '#333' },
            minValue: 0
        },
        annotations: {
            textStyle: {
                color: '#d32f2f',
                fontSize: 13,
                bold: true
            }
        }
    };
    
    // Crear y dibujar el gráfico
    const chart = new google.visualization.ColumnChart(chartDiv);
    chart.draw(data, options);
    
    // Hacer el gráfico responsivo
    window.addEventListener('resize', function() {
        chart.draw(data, options);
    });
}
