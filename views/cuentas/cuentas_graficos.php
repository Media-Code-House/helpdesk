<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Consultas SQL para obtener los datos totales de ingresos, egresos y saldo
$queryTotalIngresos = "SELECT SUM(valor) AS total FROM cuenta WHERE tipo_transaccion = 'ingreso'";
$queryTotalEgresos = "SELECT SUM(valor) AS total FROM cuenta WHERE tipo_transaccion = 'egreso'";

$totalIngresos = $pdo->query($queryTotalIngresos)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$totalEgresos = $pdo->query($queryTotalEgresos)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$totalCaja = $totalIngresos - $totalEgresos;

// Consultas para los gráficos
$queryIngresos = "SELECT banco, SUM(valor) AS total FROM cuenta WHERE tipo_transaccion = 'ingreso' GROUP BY banco";
$queryEgresos = "SELECT banco, SUM(valor) AS total FROM cuenta WHERE tipo_transaccion = 'egreso' GROUP BY banco";
$queryIngresosMensuales = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(valor) AS total FROM cuenta WHERE tipo_transaccion = 'ingreso' GROUP BY mes ORDER BY mes";
$queryEgresosMensuales = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(valor) AS total FROM cuenta WHERE tipo_transaccion = 'egreso' GROUP BY mes ORDER BY mes";

$ingresos = $pdo->query($queryIngresos)->fetchAll(PDO::FETCH_ASSOC);
$egresos = $pdo->query($queryEgresos)->fetchAll(PDO::FETCH_ASSOC);
$ingresosMensuales = $pdo->query($queryIngresosMensuales)->fetchAll(PDO::FETCH_ASSOC);
$egresosMensuales = $pdo->query($queryEgresosMensuales)->fetchAll(PDO::FETCH_ASSOC);

$ingresosJson = json_encode($ingresos);
$egresosJson = json_encode($egresos);
$ingresosMensualesJson = json_encode($ingresosMensuales);
$egresosMensualesJson = json_encode($egresosMensuales);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos - Cuentas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h2 class="center-align">Resumen de Cuentas</h2>

        <!-- Resumen de Totales -->
        <div class="row">
            <div class="col s12 m4">
                <div class="card green lighten-4">
                    <div class="card-content">
                        <span class="card-title">Total Ingresos</span>
                        <h5 class="green-text"><?php echo number_format($totalIngresos, 2); ?> COP</h5>
                    </div>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card red lighten-4">
                    <div class="card-content">
                        <span class="card-title">Total Egresos</span>
                        <h5 class="red-text"><?php echo number_format($totalEgresos, 2); ?> COP</h5>
                    </div>
                </div>
            </div>
            <div class="col s12 m4">
                <div class="card blue lighten-4">
                    <div class="card-content">
                        <span class="card-title">Total en Caja</span>
                        <h5 class="blue-text"><?php echo number_format($totalCaja, 2); ?> COP</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de ingresos y egresos por banco -->
        <h3 class="center-align">Gráficos de Ingresos y Egresos</h3>
        <div class="row">
            <div class="col s12 m6">
                <h5 class="center-align">Ingresos por Banco</h5>
                <canvas id="ingresosBancoChart"></canvas>
            </div>
            <div class="col s12 m6">
                <h5 class="center-align">Egresos por Banco</h5>
                <canvas id="egresosBancoChart"></canvas>
            </div>
        </div>

        <!-- Gráficos mensuales de ingresos y egresos -->
        <div class="row">
            <div class="col s12 m6">
                <h5 class="center-align">Ingresos Mensuales</h5>
                <canvas id="ingresosMensualesChart"></canvas>
            </div>
            <div class="col s12 m6">
                <h5 class="center-align">Egresos Mensuales</h5>
                <canvas id="egresosMensualesChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ingresosBanco = <?php echo $ingresosJson; ?>;
        const egresosBanco = <?php echo $egresosJson; ?>;
        const ingresosMensuales = <?php echo $ingresosMensualesJson; ?>;
        const egresosMensuales = <?php echo $egresosMensualesJson; ?>;

        // Gráfico de Ingresos por Banco
        const ctxIngresosBanco = document.getElementById('ingresosBancoChart').getContext('2d');
        new Chart(ctxIngresosBanco, {
            type: 'bar',
            data: {
                labels: ingresosBanco.map(data => data.banco),
                datasets: [{ label: 'Total Ingresos', data: ingresosBanco.map(data => data.total), backgroundColor: 'rgba(75, 192, 192, 0.6)' }]
            }
        });

        // Gráfico de Egresos por Banco
        const ctxEgresosBanco = document.getElementById('egresosBancoChart').getContext('2d');
        new Chart(ctxEgresosBanco, {
            type: 'bar',
            data: {
                labels: egresosBanco.map(data => data.banco),
                datasets: [{ label: 'Total Egresos', data: egresosBanco.map(data => data.total), backgroundColor: 'rgba(255, 99, 132, 0.6)' }]
            }
        });

        // Gráfico de Ingresos Mensuales
        const ctxIngresosMensuales = document.getElementById('ingresosMensualesChart').getContext('2d');
        new Chart(ctxIngresosMensuales, {
            type: 'line',
            data: {
                labels: ingresosMensuales.map(data => data.mes),
                datasets: [{ label: 'Ingresos Mensuales', data: ingresosMensuales.map(data => data.total), borderColor: 'rgba(75, 192, 192, 0.8)', fill: false }]
            }
        });

        // Gráfico de Egresos Mensuales
        const ctxEgresosMensuales = document.getElementById('egresosMensualesChart').getContext('2d');
        new Chart(ctxEgresosMensuales, {
            type: 'line',
            data: {
                labels: egresosMensuales.map(data => data.mes),
                datasets: [{ label: 'Egresos Mensuales', data: egresosMensuales.map(data => data.total), borderColor: 'rgba(255, 99, 132, 0.8)', fill: false }]
            }
        });
    </script>
</body>
</html>
