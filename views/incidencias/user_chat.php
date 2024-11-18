<?php
session_start();
require_once '../../config/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener el ID del usuario con el que el administrador quiere conversar
$id_usuario_chat = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : $_SESSION['id_usuario'];

// Procesar el envío de un mensaje
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $id_admin = ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'soporte') ? $_SESSION['id_usuario'] : null;

    $insert_query = "INSERT INTO mensajes_chat (id_usuario, id_admin, mensaje) VALUES (:id_usuario, :id_admin, :mensaje)";
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->bindParam(':id_usuario', $id_usuario_chat);
    $insert_stmt->bindParam(':id_admin', $id_admin);
    $insert_stmt->bindParam(':mensaje', $mensaje);
    $insert_stmt->execute();

    // Recargar la página para mostrar el nuevo mensaje
    header("Location: user_chat.php?id_usuario=$id_usuario_chat");
    exit;
}

include '../header.php'; // Mueve la inclusión de header.php aquí, después de las redirecciones.

// Obtener los mensajes específicos para el usuario actual o para el usuario seleccionado si es admin
$query = "SELECT m.mensaje, m.fecha_mensaje, 
                 CASE WHEN m.id_admin IS NULL THEN 'usuario' ELSE 'admin' END AS remitente 
          FROM mensajes_chat m
          WHERE m.id_usuario = :id_usuario 
          ORDER BY m.fecha_mensaje ASC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_usuario', $id_usuario_chat);
$stmt->execute();
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con Usuario - HelpDesk</title>
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
    </style>
</head>
<body>
    <div class="container chat-container">
        <h2 class="center-align">Chat con el Usuario</h2>

        <!-- Contenedor de Mensajes -->
        <div class="chat-messages">
            <?php foreach ($mensajes as $mensaje): ?>
                <p><strong><?php echo htmlspecialchars($mensaje['remitente']); ?>:</strong> <?php echo htmlspecialchars($mensaje['mensaje']); ?></p>
                <p class="fecha"><?php echo htmlspecialchars($mensaje['fecha_mensaje']); ?></p>
            <?php endforeach; ?>
        </div>

        <!-- Formulario para Enviar Mensaje -->
        <form method="POST" action="">
            <div class="input-field">
                <textarea id="mensaje" name="mensaje" class="materialize-textarea" placeholder="Escribe tu mensaje aquí..." required></textarea>
                <label for="mensaje">Mensaje</label>
            </div>
            <button type="submit" class="btn blue">Enviar</button>
        </form>

        <!-- Enlace de Regreso a la Lista de Chats -->
        <div class="center-align" style="margin-top: 20px;">
            <a href="list_chats.php" class="btn grey lighten-1">Volver a la Lista de Chats</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
