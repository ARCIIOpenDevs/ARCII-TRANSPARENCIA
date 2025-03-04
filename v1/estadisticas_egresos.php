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

// Calcular estadísticas de egresos
$total_egresos = 0;
$total_pagado = 0;
$total_pendiente = 0;
foreach ($data as $row) {
    $factura = array_combine($normalized_headers, $row);
    $total = floatval(str_replace([',', '$'], '', $factura['total'] ?? '0'));
    $pagado = strtolower($factura['pagado'] ?? '') === 'si';

    $total_egresos += $total;
    if ($pagado) {
        $total_pagado += $total;
    } else {
        $total_pendiente += $total;
    }
}

$porcentaje_pagado = $total_pagado / $total_egresos * 100;
$porcentaje_pendiente = $total_pendiente / $total_egresos * 100;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Egresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </style>
</head>
<body>
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

<div class="container py-4">
    <h1 class="mb-4">Estadísticas de Egresos</h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Total Egresos</th>
            <th>Total Pagado</th>
            <th>Total Pendiente</th>
            <th>Porcentaje Pagado</th>
            <th>Porcentaje Pendiente</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>$<?php echo number_format($total_egresos, 2); ?></td>
            <td>$<?php echo number_format($total_pagado, 2); ?></td>
            <td>$<?php echo number_format($total_pendiente, 2); ?></td>
            <td><?php echo number_format($porcentaje_pagado, 2); ?>%</td>
            <td><?php echo number_format($porcentaje_pendiente, 2); ?>%</td>
        </tr>
        </tbody>
    </table>

    <h3>Gráficas</h3>
    <div class="row">
        <div class="col-md-6">
            <canvas id="chartPie"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="chartBar"></canvas>
        </div>
    </div>
</div>

<script>
    const ctxPie = document.getElementById('chartPie').getContext('2d');
    const chartPie = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Pagado', 'Pendiente'],
            datasets: [{
                data: [<?php echo $total_pagado; ?>, <?php echo $total_pendiente; ?>],
                backgroundColor: ['#4CAF50', '#F44336']
            }]
        }
    });

    const ctxBar = document.getElementById('chartBar').getContext('2d');
    const chartBar = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Pagado', 'Pendiente'],
            datasets: [{
                label: 'Egresos',
                data: [<?php echo $total_pagado; ?>, <?php echo $total_pendiente; ?>],
                backgroundColor: ['#4CAF50', '#F44336']
            }]
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>
</html>
