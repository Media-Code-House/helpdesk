<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario está autenticado y tiene los permisos adecuados
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'soporte' && $_SESSION['rol'] !== 'desarrollador')) {
    header("Location: ../auth/login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$rol = $_SESSION['rol'];

// Ajustar la consulta según el rol
if ($rol === 'admin') {
    // Si el usuario es admin, ver todas las notificaciones
    $query = "SELECT n.mensaje, n.fecha_notificacion, i.titulo AS incidencia_titulo
              FROM notificaciones n
              JOIN incidencias i ON n.id_incidencia = i.id_incidencia
              ORDER BY n.fecha_notificacion DESC";
    $stmt = $pdo->prepare($query);
} else {
    // Si el usuario es soporte o desarrollador, ver solo las notificaciones propias
    $query = "SELECT n.mensaje, n.fecha_notificacion, i.titulo AS incidencia_titulo
              FROM notificaciones n
              JOIN incidencias i ON n.id_incidencia = i.id_incidencia
              WHERE n.id_usuario = :id_usuario
              ORDER BY n.fecha_notificacion DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
}

// Ejecutar la consulta
$stmt->execute();
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si la consulta trajo resultados
if (!$notificaciones) {
    echo "No se encontraron notificaciones para este usuario.";
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
</head>
<body>
    <div class="container">
        <h2 class="center-align">Historial de Notificaciones</h2>

        <!-- Tabla de Notificaciones -->
        <?php if (!empty($notificaciones)): ?>
            <table class="striped centered responsive-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Incidencia</th>
                        <th>Mensaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notificaciones as $notificacion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($notificacion['fecha_notificacion']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['incidencia_titulo']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['mensaje']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="red-text center-align">No se encontraron notificaciones para este usuario.</p>
        <?php endif; ?>

        <!-- Enlace de Regreso al Dashboard -->
        <div class="center-align" style="margin-top: 20px;">
            <a href="../dashboard.php" class="btn blue">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
