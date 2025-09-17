<?php
/**
 * =============================================================================
 * PÁGINA PRINCIPAL DE MUNICIPIOS - SEDEQ CORREGIDORA
 * =============================================================================
 * 
 * Página que muestra todos los municipios de Querétaro con acceso a sus tableros
 * @author Sistema SEDEQ
 * @version 1.0.0
 * @since 2025
 */

// Incluir la conexión para obtener los municipios
require_once 'conexion_prueba_2024.php';

/**
 * Formatea nombres de municipios para display en formato título
 * Convierte de MAYÚSCULAS (nuestro formato interno) a Formato Título para mostrar
 */
function formatearNombreMunicipio($municipio)
{
    // Convertir de mayúsculas a formato título
    $formatted = mb_convert_case(strtolower($municipio), MB_CASE_TITLE, 'UTF-8');

    // Correcciones específicas para preposiciones y artículos
    $formatted = str_replace([' De ', ' Del ', ' El '], [' de ', ' del ', ' El '], $formatted);

    return $formatted;
}

// Obtener la lista de municipios
$municipios = [];
for ($i = 1; $i <= 18; $i++) {
    $nombre = nombre_municipio((string) $i);
    if ($nombre) {
        $municipios[] = [
            'id' => $i,
            'nombre' => $nombre,
            'nombre_formateado' => formatearNombreMunicipio($nombre)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipios de Querétaro - SEDEQ</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: var(--font-primary);
            background-color: var(--light-gray);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            background: var(--white);
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-sm);
        }

        .page-header h1 {
            color: var(--primary-blue);
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: var(--font-weight-bold);
            font-family: var(--font-heading);
            text-transform: uppercase;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .municipios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .municipio-card {
            background-color: var(--white);
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            position: relative;
            overflow: hidden;
        }

        .municipio-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .municipality-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(36, 43, 87, 0.2);
        }

        .municipio-info h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary-blue);
            font-weight: var(--font-weight-semibold);
        }

        .municipio-info p {
            color: var(--text-secondary);
            margin-bottom: 15px;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .municipality-link {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all var(--transition-speed);
            font-weight: var(--font-weight-medium);
        }

        .municipality-link:hover {
            background: linear-gradient(135deg, var(--secondary-blue), var(--accent-aqua));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(36, 43, 87, 0.25);
        }

        .page-footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .municipios-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="page-header">
            <h1><i class="fas fa-city"></i> Municipios de Querétaro</h1>
            <p>Sistema Estadístico de Educación - SEDEQ</p>
        </div>

        <div class="municipios-grid">
            <?php foreach ($municipios as $municipio): ?>
                <div class="municipio-card">
                    <div class="municipality-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="municipio-info">
                        <h3><?php echo htmlspecialchars($municipio['nombre_formateado'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p>Estadísticas educativas para el municipio de
                            <?php echo htmlspecialchars($municipio['nombre_formateado'], ENT_QUOTES, 'UTF-8'); ?>.
                        </p>
                    </div>
                    <a href="resumen.php?municipio=<?php echo urlencode($municipio['nombre']); ?>"
                        class="municipality-link">
                        <span>Acceder al Tablero</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="page-footer">
            <p>© 2025 SEDEQ - Sistema Educativo del Estado de Querétaro</p>
            <p>Ciclo Escolar <?php echo obtenerInfoCicloEscolar()['ciclo_completo']; ?></p>
        </div>
    </div>

    <script>
        // Animación de entrada para las tarjetas
        document.addEventListener('DOMContentLoaded', function () {
            const cards = document.querySelectorAll('.municipio-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>

</html>