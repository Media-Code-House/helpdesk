<?php
session_start();
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';






include '../header.php';

$id_objetivo = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Obtener la información completa del objetivo
    $stmt = $pdo->prepare("SELECT * FROM objetivos_empresariales WHERE id_objetivo = ?");
    $stmt->execute([$id_objetivo]);
    $objetivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$objetivo) {
        echo "<div class='container'><p>Objetivo no encontrado.</p></div>";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<div class="container">
    <h4 class="center-align">Detalles del Objetivo: <?php echo htmlspecialchars($objetivo['nombre']); ?></h4>
    <table class="striped responsive-table">
        <tr>
            <th>ID del Objetivo</th>
            <td><?php echo htmlspecialchars($objetivo['id_objetivo']); ?></td>
        </tr>
        <tr>
            <th>Nombre</th>
            <td><?php echo htmlspecialchars($objetivo['nombre']); ?></td>
        </tr>
        <tr>
            <th>Descripción</th>
            <td><?php echo htmlspecialchars($objetivo['descripcion']); ?></td>
        </tr>
        <tr>
            <th>Área</th>
            <td><?php echo htmlspecialchars($objetivo['area']); ?></td>
        </tr>
        <tr>
            <th>KPI</th>
            <td><?php echo htmlspecialchars($objetivo['kpi']); ?></td>
        </tr>
        <tr>
            <th>Progreso Actual</th>
            <td><?php echo htmlspecialchars($objetivo['progreso_actual']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Inicio</th>
            <td><?php echo htmlspecialchars($objetivo['fecha_inicio']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Fin</th>
            <td><?php echo htmlspecialchars($objetivo['fecha_fin']); ?></td>
        </tr>
        <tr>
            <th>Estado</th>
            <td><?php echo htmlspecialchars($objetivo['estado']); ?></td>
        </tr>
        <tr>
            <th>Responsable</th>
            <td><?php echo htmlspecialchars($objetivo['responsable']); ?></td>
        </tr>
        <tr>
            <th>Observaciones</th>
            <td><?php echo htmlspecialchars($objetivo['observaciones']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Creación</th>
            <td><?php echo htmlspecialchars($objetivo['fecha_creacion']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Actualización</th>
            <td><?php echo htmlspecialchars($objetivo['fecha_actualizacion']); ?></td>
        </tr>
    </table>
    <div class="center-align">
        <a href="objetivos_list.php" class="btn waves-effect waves-light grey">Volver a la Lista</a>
    </div>
</div>
