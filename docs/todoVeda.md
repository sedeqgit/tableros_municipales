 Contexto

 El sistema SEDEQ usa una paleta institucional azul-marino en css/global.css con variables CSS bien definidas. se desea agregar una segunda paleta basada en los grises PANTONE 7544 
  C ("Periodo de Veda") y colores tierra como acentos. El cambio debe hacerse desde settings.php y persistir entre sesiones.

  Lo que se implementará:

  - 5 archivos modificados, no se crean archivos nuevos:
    - css/global.css → bloque [data-theme="veda"] con todas las variables mapeadas + variables de gráficas
    - css/settings.css → estilos del selector visual de paleta
    - js/settings.js → módulo ThemeSwitcher con localStorage
    - settings.php → nuevo panel "Apariencia del Sistema" con tarjetas visuales
    - includes/institutional_bar.php → script anti-FOUC de 3 líneas
  - Paleta Veda usa PANTONE 7544 C (#718594) como color base estructural y los colores tierra (tan, teal, ámbar, coral, periwinkle) como acentos y para gráficas
  - El cambio es instantáneo (CSS variables), persiste entre páginas (localStorage) y no hay flash al recargar

 Archivos a modificar
 ┌────────────────────────────────┬─────────────────────────────────────────────────────────────────────┐
 │            Archivo             │                               Cambio                                │
 ├────────────────────────────────┼─────────────────────────────────────────────────────────────────────┤
 │ css/global.css                 │ Agregar bloque [data-theme="veda"] con overrides + chart color vars │
 ├────────────────────────────────┼─────────────────────────────────────────────────────────────────────┤
 │ css/settings.css               │ Agregar estilos para las tarjetas de selección de tema              │
 ├────────────────────────────────┼─────────────────────────────────────────────────────────────────────┤
 │ js/settings.js                 │ Agregar lógica de theme switching con localStorage                  │
 ├────────────────────────────────┼─────────────────────────────────────────────────────────────────────┤
 │ settings.php                   │ Agregar panel "Apariencia del Sistema" con picker visual            │
 ├────────────────────────────────┼─────────────────────────────────────────────────────────────────────┤
 │ includes/institutional_bar.php │ Agregar script inline de carga temprana (anti-FOUC)                 │
 └────────────────────────────────┴─────────────────────────────────────────────────────────────────────┘
 ---
 Paso 1 — CSS: Definir nueva paleta en global.css

 Agregar al final de global.css un bloque de overrides para el tema Veda.

 Mapeo de variables (actual → veda)

 Actuales (:root)                    Veda ([data-theme="veda"])
 --------------------------------    --------------------------------
 --primary-blue:    #242B57          → #384050  (PANTONE 6214 C – azul oscuro frío)
 --secondary-blue:  #4996C4          → #718594  (PANTONE 7544 C – gris azulado principal)
 --tertiary-gray:   #707F8F          → #8D9EAB  (PANTONE 7544 C 80%)
 --accent-aqua:     #7CC6D8          → #48B89A  (PANTONE 563 C – teal)
 --accent-magenta:  #FF3E8D          → #E87878  (PANTONE 2438 C – coral/salmón)
 --accent-red:      #9D2449          → #8C96B5  (PANTONE 6087 C – periwinkle)
 --accent-gold:     #D4AF37          → #E8BE60  (PANTONE 6001 C – ámbar)
 --text-primary:    #242B57          → #384050
 --text-secondary:  #707F8F          → #718594
 --text-accent:     #4996C4          → #8C96B5  (PANTONE 6087 C)
 --light-blue:      rgba(76,150,196,.10) → rgba(113,133,148,0.10)
 --light-aqua:      rgba(124,198,216,.15) → rgba(72,184,154,0.12)
 --light-magenta:   rgba(255,62,141,.10) → rgba(232,120,120,0.10)

 Nuevas variables de colores para gráficas

 Agregar en :root (para ambos temas):
 /* Chart colors — tema default (azules institucionales) */
 --chart-1: #4996C4; --chart-2: #242B57; --chart-3: #7CC6D8;
 --chart-4: #9D2449; --chart-5: #D4AF37; --chart-6: #707F8F;
 --chart-7: #FF3E8D; --chart-8: #17a2b8;

 /* Veda override (tierras y neutros) */
 [data-theme="veda"] --chart-1: #D4A679; --chart-2: #9C9080; ...

 Overrides de colores fijos en footer/barra institucional

 [data-theme="veda"] .top-institutional-bar { background: #718594; }
 [data-theme="veda"] .top-footer2          { background-color: #718594; }
 [data-theme="veda"] .bottom-footer2       { background-color: #384050; }

 Transición suave al cambiar tema

 html { transition: filter 0.3s ease; }
 body, .main-header, .top-institutional-bar { transition: background-color 0.4s ease, color 0.4s ease; }

 ---
 Paso 2 — CSS: Estilos del picker en settings.css

 Agregar sección /* === TEMA: Selector de paleta */ con:
 - .theme-picker — grid 2 columnas
 - .theme-card — tarjeta con 5 swatches de color, nombre, descripción
 - .theme-card.active — borde azul/teal + checkmark badge
 - .theme-swatch-strip — fila de 5 círculos de color (preview)
 - .theme-card:hover — elevación suave

 ---
 Paso 3 — JS: Lógica de cambio en settings.js

 Agregar clase/módulo ThemeSwitcher al final del archivo:

 const ThemeSwitcher = {
   STORAGE_KEY: 'sedeq_theme',
   DEFAULT: 'default',

   apply(theme) {
     document.documentElement.dataset.theme = (theme === 'veda') ? 'veda' : '';
     localStorage.setItem(this.STORAGE_KEY, theme);
     this.updateUI(theme);
   },

   load() {
     const saved = localStorage.getItem(this.STORAGE_KEY) || this.DEFAULT;
     this.apply(saved);
   },

   updateUI(theme) {
     document.querySelectorAll('.theme-card').forEach(card => {
       card.classList.toggle('active', card.dataset.theme === theme);
     });
   },

   init() {
     this.load();
     document.querySelectorAll('.theme-card').forEach(card => {
       card.addEventListener('click', () => this.apply(card.dataset.theme));
     });
   }
 };

 document.addEventListener('DOMContentLoaded', () => ThemeSwitcher.init());

 ---
 Paso 4 — PHP: Panel UI en settings.php

 Insertar un nuevo <div class="settings-panel"> antes del panel de Preferencias existente, con:

 <div class="settings-panel animate-up delay-1">
   <h2 class="settings-title"><i class="fas fa-palette"></i> Apariencia del Sistema</h2>
   <div class="settings-content">
     <p class="form-text text-muted">Selecciona la paleta de colores del sistema.</p>
     <div class="theme-picker">

       <!-- Tarjeta: Institucional (default) -->
       <div class="theme-card active" data-theme="default">
         <div class="theme-swatch-strip">
           <span style="background:#242B57"></span>
           <span style="background:#4996C4"></span>
           <span style="background:#7CC6D8"></span>
           <span style="background:#9D2449"></span>
           <span style="background:#D4AF37"></span>
         </div>
         <div class="theme-card-info">
           <strong>Institucional</strong>
           <small>Paleta oficial SEDEQ</small>
         </div>
         <span class="theme-check"><i class="fas fa-check-circle"></i></span>
       </div>

       <!-- Tarjeta: Periodo de Veda -->
       <div class="theme-card" data-theme="veda">
         <div class="theme-swatch-strip">
           <span style="background:#384050"></span>
           <span style="background:#718594"></span>
           <span style="background:#A9B8C2"></span>
           <span style="background:#48B89A"></span>
           <span style="background:#E8BE60"></span>
         </div>
         <div class="theme-card-info">
           <strong>Periodo de Veda</strong>
           <small>Paleta PANTONE 7544 C</small>
         </div>
         <span class="theme-check"><i class="fas fa-check-circle"></i></span>
       </div>

     </div>
   </div>
 </div>

 ---
 Paso 5 — Anti-FOUC en includes/institutional_bar.php

 Agregar al inicio del archivo (antes de cualquier HTML):

 <script>
   (function(){
     var t = localStorage.getItem('sedeq_theme');
     if(t === 'veda') document.documentElement.dataset.theme = 'veda';
   })();
 </script>

 Este script es síncrono y se ejecuta antes de que el browser pinte, evitando el flash del tema incorrecto.

 ---
 Paleta completa "Veda" (referencia)
 ┌──────────────────┬─────────┬──────────────────────┐
 │   Variable CSS   │  Valor  │       PANTONE        │
 ├──────────────────┼─────────┼──────────────────────┤
 │ --primary-blue   │ #384050 │ 6214 C               │
 ├──────────────────┼─────────┼──────────────────────┤
 │ --secondary-blue │ #718594 │ 7544 C 100%          │
 ├──────────────────┼─────────┼──────────────────────┤
 │ --tertiary-gray  │ #8D9EAB │ 7544 C 80%           │
 ├──────────────────┼─────────┼──────────────────────┤
 │ --accent-aqua    │ #48B89A │ 563 C (teal)         │
 ├──────────────────┼─────────┼──────────────────────┤
 │ --accent-magenta │ #E87878 │ 2438 C (coral)       │
 ├──────────────────┼─────────┼──────────────────────┤
 │ --accent-red     │ #8C96B5 │ 6087 C (periwinkle)  │
 ├──────────────────┼─────────┼──────────────────────┤
 │ --accent-gold    │ #E8BE60 │ 6001 C (ámbar)       │
 ├──────────────────┼─────────┼──────────────────────┤
 │ Chart 1          │ #D4A679 │ 727 C (tan cálido)   │
 ├──────────────────┼─────────┼──────────────────────┤
 │ Chart 2          │ #9C9080 │ 2331 C (taupe)       │
 ├──────────────────┼─────────┼──────────────────────┤
 │ Chart 3          │ #C4B08C │ 4267 C (khaki)       │
 ├──────────────────┼─────────┼──────────────────────┤
 │ Chart 4          │ #D4A8B0 │ 501 C (rosa polvoso) │
 ├──────────────────┼─────────┼──────────────────────┤
 │ Chart 5          │ #B8C54E │ 2289 C (lima)        │
 ├──────────────────┼─────────┼──────────────────────┤
 │ Chart 6          │ #718594 │ 7544 C               │
 └──────────────────┴─────────┴──────────────────────┘
 ---
 Verificación

 1. Abrir settings.php — ver el nuevo panel "Apariencia"
 2. Hacer clic en "Periodo de Veda" — colores cambian suavemente
 3. Navegar a home.php — el tema persiste (localStorage)
 4. Recargar la página — no hay flash del tema incorrecto
 5. Hacer clic en "Institucional" — vuelve a colores azules
 6. Verificar que la barra institucional y footer también cambian de color