<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar el archivo CSV
$file = 'corrected_ingresos_egresos_detalles.csv';
if (!file_exists($file)) {
    die('Error: El archivo CSV no se encuentra.');
}

// Leer todo el contenido del archivo y convertirlo a UTF-8
$file_content = file_get_contents($file);
$file_content_utf8 = mb_convert_encoding($file_content, 'UTF-8', 'auto');
$temp_file = tempnam(sys_get_temp_dir(), 'csv');
file_put_contents($temp_file, $file_content_utf8);

// Procesar el archivo convertido
$data = array_map('str_getcsv', file($temp_file));

$start_row = 3; // Fila donde comienzan los datos relevantes
$headers = $data[$start_row]; // Obtener encabezados reales
$normalized_headers = array_map(function($header) {
    return strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9 ]/', '', $header)));
}, $headers);

// Obtener las filas relevantes
$data = array_slice($data, $start_row + 1);

// Función para generar enlace del PDF
define('BASE_URL', 'https://transparencia.grupoarcii.com/archivos/1736573711690-1/');
function generate_pdf_link($uuid) {
    return BASE_URL . $uuid . '.pdf';
}
?>
<!DOCTYPE html>
<html lang="es">
    
    <style>
        .navbar {
            background-color: rgb(140, 68, 84);
            color: white;
        }
        .navbar a {
            color: white;
        }
        .navbar-brand {
            color: white !important;
        }
        .btn-custom {
            background-color: rgb(140, 68, 84);
            color: white;
        }
    </style>
    
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <span class="navbar-brand">Bienvenido al sistema de transparencia regulatoria V. 0.0.1</span>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="https://transparencia.grupoarcii.com/">
                        <i class="bi bi-file-text"></i> Facturas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="estadisticas_egresos.php">
                        <i class="bi bi-bar-chart"></i> Estadísticas de Egresos
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>.:: PORTAL DE TRANSPARENCIA ARCII MEXICO ::.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Facturas</h1>
    <div class="row">
        <?php foreach ($data as $row): ?>
            <?php
            // Mapear datos por nombre
            if (count($normalized_headers) !== count($row)) {
                continue; // Saltar filas con columnas inconsistentes
            }
            $factura = array_combine($normalized_headers, $row);
            $uuid = $factura['uuid'] ?? '';
            ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">RFC: <?php echo htmlspecialchars($factura['rfc'] ?? 'N/A'); ?></h5>
                        <p class="card-text">
                            <strong>Razón Social:</strong> <?php echo htmlspecialchars($factura['razn_social_emisor'] ?? 'N/A'); ?><br>
                            <strong>Fecha Timbrado:</strong> <?php echo htmlspecialchars($factura['fecha_timbrado'] ?? 'N/A'); ?><br>
                            <strong>Total:</strong> $<?php echo htmlspecialchars($factura['total'] ?? '0.00'); ?><br>
                            <strong>Pagado:</strong> <?php echo htmlspecialchars($factura['pagado'] ?? 'N/A'); ?>
                        </p>
                        <?php if ($uuid): ?>
                            <a href="<?php echo generate_pdf_link($uuid); ?>" class="btn btn-primary" target="_blank">Descargar PDF</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>No disponible</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
