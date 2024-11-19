<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define una función personalizada para manejar redirecciones
if (!function_exists('custom_redirect')) {
    function custom_redirect($url)
    {
        if (defined('TEST_ENVIRONMENT')) {
            throw new Exception("Redirect to: $url");
        }
        header("Location: $url");
        exit;
    }
}

// Conexión a la base de datos y encabezado solo en producción
if (!defined('TEST_ENVIRONMENT')) {
    require_once __DIR__ . '/../config/db.php';
    include __DIR__ . '/header.php';
}

// Verificar si el usuario está autenticado y es administrador
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header('Location: /../views/incidencias/user_chat.php');
}

// Valores por defecto para el entorno de prueba
if (!isset($incidencias_por_estado)) {
    $incidencias_por_estado = [['estado' => 'pendiente', 'total' => 0]];
}

if (!isset($incidencias_por_prioridad)) {
    $incidencias_por_prioridad = [['prioridad' => 'baja', 'total' => 0]];
}

if (!isset($tiempo_promedio_resolucion)) {
    $tiempo_promedio_resolucion = 0;
}

// Consultas reales solo en producción
if (!defined('TEST_ENVIRONMENT')) {
    $query_estado = "SELECT estado, COUNT(*) as total FROM incidencias GROUP BY estado";
    $stmt_estado = $pdo->prepare($query_estado);
    $stmt_estado->execute();
    $incidencias_por_estado = $stmt_estado->fetchAll(PDO::FETCH_ASSOC);

    $query_prioridad = "SELECT prioridad, COUNT(*) as total FROM incidencias GROUP BY prioridad";
    $stmt_prioridad = $pdo->prepare($query_prioridad);
    $stmt_prioridad->execute();
    $incidencias_por_prioridad = $stmt_prioridad->fetchAll(PDO::FETCH_ASSOC);

    $query_tiempo_resolucion = "SELECT AVG(TIMESTAMPDIFF(HOUR, fecha_creacion, fecha_actualizacion)) as promedio_horas FROM incidencias WHERE estado = 'resuelto' AND fecha_actualizacion IS NOT NULL";
    $stmt_tiempo_resolucion = $pdo->prepare($query_tiempo_resolucion);
    $stmt_tiempo_resolucion->execute();
    $tiempo_promedio_resolucion = $stmt_tiempo_resolucion->fetch(PDO::FETCH_ASSOC)['promedio_horas'];
}
?>





<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Estadísticas de Incidencias</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            var dataEstado = google.visualization.arrayToDataTable([
                ['Estado', 'Cantidad'],
                <?php foreach ($incidencias_por_estado as $fila) {
                    echo "['" . $fila['estado'] . "', " . $fila['total'] . "],";
                } ?>
            ]);
            var optionsEstado = {
                title: 'Incidencias por Estado'
            };
            var chartEstado = new google.visualization.PieChart(document.getElementById('chart_estado'));
            chartEstado.draw(dataEstado, optionsEstado);

            var dataPrioridad = google.visualization.arrayToDataTable([
                ['Prioridad', 'Cantidad'],
                <?php foreach ($incidencias_por_prioridad as $fila) {
                    echo "['" . $fila['prioridad'] . "', " . $fila['total'] . "],";
                } ?>
            ]);
            var optionsPrioridad = {
                title: 'Incidencias por Prioridad'
            };
            var chartPrioridad = new google.visualization.PieChart(document.getElementById('chart_prioridad'));
            chartPrioridad.draw(dataPrioridad, optionsPrioridad);

            var dataTiempo = google.visualization.arrayToDataTable([
                ['Métrica', 'Horas'],
                ['Promedio de Horas para Resolución', <?php echo $tiempo_promedio_resolucion ?: 0; ?>]
            ]);
            var optionsTiempo = {
                title: 'Tiempo Promedio de Resolución (en horas)',
                legend: {
                    position: 'none'
                }
            };
            var chartTiempo = new google.visualization.ColumnChart(document.getElementById('chart_tiempo'));
            chartTiempo.draw(dataTiempo, optionsTiempo);
        }
    </script>
</head>

<body>
    <div class="container">
        <!-- Filtros de Reporte -->
        <div class="card">
            <div class="card-content">
                <span class="card-title">Generar Reporte de Incidencias</span>
                <form action="../../reports/pdf/reporte_incidencias.php" method="GET">
                    <label for="estado">Estado:</label>
                    <div class="input-field">

                        <select name="estado" id="estado" class="browser-default">
                            <option value="" disabled selected>Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en progreso">En Progreso</option>
                            <option value="en revision">En Revisión</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                    </div>
                    <label for="prioridad">Prioridad:</label>
                    <div class="input-field">

                        <select name="prioridad" id="prioridad" class="browser-default">
                            <option value="" disabled selected>Todas</option>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <div class="input-field">

                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="datepicker">
                    </div>
                    <label for="fecha_fin">Fecha de Fin:</label>
                    <div class="input-field">

                        <input type="date" name="fecha_fin" id="fecha_fin" class="datepicker">
                    </div>
                    <button type="submit" class="btn blue">Generar Reporte</button>
                </form>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Incidencias por Estado</span>
                        <div id="chart_estado" style="width: 100%; height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Incidencias por Prioridad</span>
                        <div id="chart_prioridad" style="width: 100%; height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col s12 m6">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Tiempo Promedio de Resolución (en horas)</span>
                        <div id="chart_tiempo" style="width: 100%; height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
        });
    </script>
</body>

</html>

</html>