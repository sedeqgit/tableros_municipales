// Funciones básicas para la visualización del diagrama de flujo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Google Charts
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(inicializarPagina);
    
    // Configurar botones de vista
    const radioButtons = document.querySelectorAll('input[name="view-type"]');
    if (radioButtons.length > 0) {
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'diagram') {
                    document.getElementById('efficiency-diagram-view').style.display = 'block';
                    document.getElementById('efficiency-chart-view').style.display = 'none';
                } else {
                    document.getElementById('efficiency-diagram-view').style.display = 'none';
                    document.getElementById('efficiency-chart-view').style.display = 'block';
                    dibujarGrafico();
                }
            });
        });
    }
      // Configurar pestañas de análisis
    const tabs = document.querySelectorAll('.tab');
    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Quitar estado activo de todas las pestañas
                tabs.forEach(t => t.classList.remove('active'));
                // Agregar estado activo a la pestaña actual
                this.classList.add('active');
                
                // Ocultar todos los contenidos
                const contents = document.querySelectorAll('.tab-content');
                contents.forEach(c => c.style.display = 'none');
                
                // Mostrar contenido correspondiente
                const contentId = this.getAttribute('data-tab');
                if (document.getElementById(contentId)) {
                    document.getElementById(contentId).style.display = 'block';
                }
            });
        });
    }
    
    // Modo oscuro
    const darkModeButton = document.getElementById('darkModeButton');
    if (darkModeButton) {
        darkModeButton.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
        });
    }
});

function inicializarPagina() {
    try {
        crearDiagramaFlujo();
        dibujarGrafico();
    } catch (e) {
        console.error("Error al inicializar la página:", e);
    }
}

function crearDiagramaFlujo() {
    const container = document.getElementById('flow-container');
    if (!container) {
        console.error('No se encontró el contenedor del diagrama de flujo');
        return;
    }
    
    container.innerHTML = '';
    
    // Crear nodos del diagrama
    const nodos = [
        { id: 'node-primaria-in', x: 50, y: 120, titulo: 'Primaria 1°', alumnos: datosEficiencia.primaria.ingreso, ciclo: datosEficiencia.primaria.cicloIngreso },
        { id: 'node-primaria-out', x: 200, y: 120, titulo: 'Primaria 6°', alumnos: datosEficiencia.primaria.egreso, ciclo: datosEficiencia.primaria.cicloEgreso },
        { id: 'node-secundaria-in', x: 350, y: 120, titulo: 'Secundaria 1°', alumnos: datosEficiencia.secundaria.ingreso, ciclo: datosEficiencia.secundaria.cicloIngreso },
        { id: 'node-secundaria-out', x: 500, y: 120, titulo: 'Secundaria 3°', alumnos: datosEficiencia.secundaria.egreso, ciclo: datosEficiencia.secundaria.cicloEgreso },
        { id: 'node-bachillerato-in', x: 650, y: 120, titulo: 'Bachillerato 1°', alumnos: datosEficiencia.bachillerato.ingreso, ciclo: datosEficiencia.bachillerato.cicloIngreso },
        { id: 'node-bachillerato-out', x: 800, y: 120, titulo: 'Bachillerato 3°', alumnos: datosEficiencia.bachillerato.egreso, ciclo: datosEficiencia.bachillerato.cicloEgreso },
        { id: 'node-superior-in', x: 950, y: 120, titulo: 'Superior 1°', alumnos: datosEficiencia.superior.ingreso, ciclo: datosEficiencia.superior.cicloIngreso },
        { id: 'node-superior-out', x: 1100, y: 120, titulo: 'Graduados', alumnos: datosEficiencia.superior.egreso, ciclo: datosEficiencia.superior.cicloEgreso }
    ];
    
    // Crear conectores
    const conectores = [
        { desde: 'node-primaria-in', hasta: 'node-primaria-out', diferencia: datosEficiencia.primaria.diferencia },
        { desde: 'node-primaria-out', hasta: 'node-secundaria-in', diferencia: datosEficiencia.transiciones.primaria_secundaria },
        { desde: 'node-secundaria-in', hasta: 'node-secundaria-out', diferencia: datosEficiencia.secundaria.diferencia },
        { desde: 'node-secundaria-out', hasta: 'node-bachillerato-in', diferencia: datosEficiencia.transiciones.secundaria_bachillerato },
        { desde: 'node-bachillerato-in', hasta: 'node-bachillerato-out', diferencia: datosEficiencia.bachillerato.diferencia },
        { desde: 'node-bachillerato-out', hasta: 'node-superior-in', diferencia: datosEficiencia.transiciones.bachillerato_superior },
        { desde: 'node-superior-in', hasta: 'node-superior-out', diferencia: datosEficiencia.superior.diferencia }
    ];
    
    // Crear nodos en el DOM
    nodos.forEach(nodo => {
        const elemento = document.createElement('div');
        elemento.className = 'flow-node';
        elemento.id = nodo.id;
        elemento.style.left = nodo.x + 'px';
        elemento.style.top = nodo.y + 'px';
        elemento.innerHTML = `
            <h4>${nodo.titulo}</h4>
            <p>${nodo.alumnos} alumnos</p>
            <small>${nodo.ciclo}</small>
        `;
        container.appendChild(elemento);
    });
    
    // Crear conectores en el DOM
    conectores.forEach(conector => {
        const desde = document.getElementById(conector.desde);
        const hasta = document.getElementById(conector.hasta);
        
        const desdeX = parseInt(desde.style.left) + 120;
        const desdeY = parseInt(desde.style.top) + 30;
        const hastaX = parseInt(hasta.style.left);
        const hastaY = parseInt(hasta.style.top) + 30;
        
        // Crear línea conector
        const linea = document.createElement('div');
        linea.className = 'flow-connector';
        linea.style.left = desdeX + 'px';
        linea.style.top = desdeY + 'px';
        linea.style.width = (hastaX - desdeX) + 'px';
        container.appendChild(linea);
        
        // Crear etiqueta diferencia
        const diff = document.createElement('div');
        diff.className = `flow-diff ${conector.diferencia >= 0 ? 'positive' : 'negative'}`;
        diff.textContent = conector.diferencia >= 0 ? '+' + conector.diferencia : conector.diferencia;
        diff.style.left = (desdeX + (hastaX - desdeX)/2 - 16) + 'px';
        diff.style.top = (desdeY - 40) + 'px';
        container.appendChild(diff);
    });
}

function dibujarGrafico() {
    const chartElement = document.getElementById('efficiency-chart');
    if (!chartElement) return;
    
    const data = google.visualization.arrayToDataTable([
        ['Nivel', 'Ingreso', 'Egreso'],
        ['Primaria', datosEficiencia.primaria.ingreso, datosEficiencia.primaria.egreso],
        ['Secundaria', datosEficiencia.secundaria.ingreso, datosEficiencia.secundaria.egreso],
        ['Bachillerato', datosEficiencia.bachillerato.ingreso, datosEficiencia.bachillerato.egreso],
        ['Superior', datosEficiencia.superior.ingreso, datosEficiencia.superior.egreso]
    ]);
    
    const options = {
        title: 'Ingreso vs Egreso por Nivel Educativo',
        chartArea: { width: '60%' },
        colors: ['#1976d2', '#2e7d32'],
        hAxis: { title: 'Estudiantes' },
        vAxis: { title: 'Nivel Educativo' }
    };
    
    const chart = new google.visualization.BarChart(chartElement);
    chart.draw(data, options);
}
