<?php
session_start();
require_once '../../config/db.php';






include '../header.php';
// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_incidencia = isset($_GET['id']) ? $_GET['id'] : null;
$id_usuario = $_SESSION['id_usuario'];

// Obtener los mensajes de la incidencia
$query = "SELECT m.mensaje, m.fecha_envio, u.nombre AS usuario_nombre
          FROM mensajes m
          JOIN usuarios u ON m.id_usuario = u.id_usuario
          WHERE m.id_incidencia = :id_incidencia
          ORDER BY m.fecha_envio ASC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_incidencia', $id_incidencia);
$stmt->execute();
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar el envío de un mensaje
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $insert_query = "INSERT INTO mensajes (id_incidencia, id_usuario, mensaje) 
                     VALUES (:id_incidencia, :id_usuario, :mensaje)";
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->bindParam(':id_incidencia', $id_incidencia);
    $insert_stmt->bindParam(':id_usuario', $id_usuario);
    $insert_stmt->bindParam(':mensaje', $mensaje);
    $insert_stmt->execute();
    
    // Recargar la página para mostrar el nuevo mensaje
    header("Location: chat.php?id=$id_incidencia");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat de Incidencia - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .chat-messages p {
            margin-bottom: 5px;
        }
        .chat-messages .fecha {
            font-size: 0.8rem;
            color: gray;
            margin-top: -8px;
            margin-bottom: 12px;
        }
        .btn-back {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container chat-container">
        <h2 class="center-align">Chat de Incidencia ID: <?php echo htmlspecialchars($id_incidencia); ?></h2>

        <!-- Contenedor de Mensajes -->
        <div class="chat-messages">
            <?php foreach ($mensajes as $mensaje): ?>
                <p><strong><?php echo htmlspecialchars($mensaje['usuario_nombre']); ?>:</strong> <?php echo htmlspecialchars($mensaje['mensaje']); ?></p>
                <p class="fecha"><?php echo htmlspecialchars($mensaje['fecha_envio']); ?></p>
            <?php endforeach; ?>
        </div>

        <!-- Formulario de Envío de Mensaje -->
        <form method="POST" action="">
            <div class="input-field">
                <textarea id="mensaje" name="mensaje" class="materialize-textarea" placeholder="Escribe tu mensaje aquí..." required></textarea>
                <label for="mensaje">Mensaje</label>
            </div>
            <button type="submit" class="btn blue">Enviar</button>
        </form>

        <!-- Enlace de Regreso a Detalles de Incidencia -->
        <div class="center-align btn-back">
            <a href="details.php?id=<?php echo $id_incidencia; ?>" class="btn grey lighten-1">Volver a Detalles de Incidencia</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
