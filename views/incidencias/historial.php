<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener el ID de la incidencia desde la URL
$id_incidencia = isset($_GET['id']) ? $_GET['id'] : null;

// Verificar que se haya proporcionado un ID de incidencia
if (!$id_incidencia) {
    echo "ID de incidencia no proporcionado.";
    exit;
}

// Obtener el historial de cambios para la incidencia
$query = "SELECT h.fecha_cambio, u.nombre AS usuario_nombre, h.estado_anterior, h.estado_nuevo, h.comentario
          FROM historial_cambios h
          JOIN usuarios u ON h.id_usuario = u.id_usuario
          WHERE h.id_incidencia = :id_incidencia
          ORDER BY h.fecha_cambio DESC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_incidencia', $id_incidencia);
$stmt->execute();
$cambios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cambios - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .historial-cambios-container {
            max-width: 800px;
            margin: 20px auto;
        }
        .no-cambios {
            font-size: 1.2rem;
            color: gray;
            text-align: center;
            margin-top: 20px;
        }
        .btn-back {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container historial-cambios-container">
        <h2 class="center-align">Historial de Cambios de la Incidencia ID: <?php echo htmlspecialchars($id_incidencia); ?></h2>

        <!-- Tabla de Historial de Cambios -->
        <?php if (count($cambios) > 0): ?>
            <table class="striped centered responsive-table">
                <thead>
                    <tr>
                        <th>Fecha de Cambio</th>
                        <th>Usuario</th>
                        <th>Estado Anterior</th>
                        <th>Estado Nuevo</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cambios as $cambio): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cambio['fecha_cambio']); ?></td>
                            <td><?php echo htmlspecialchars($cambio['usuario_nombre']); ?></td>
                            <td><strong><?php echo htmlspecialchars($cambio['estado_anterior']); ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($cambio['estado_nuevo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cambio['comentario']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-cambios">No hay cambios registrados para esta incidencia.</p>
        <?php endif; ?>

        <!-- Enlace de Regreso a Detalles de la Incidencia -->
        <div class="center-align btn-back">
            <a href="details.php?id=<?php echo $id_incidencia; ?>" class="btn grey lighten-1">Volver a los detalles de la incidencia</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
