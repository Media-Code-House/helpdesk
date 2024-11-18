<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario tiene rol de administrador
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'soporte') {
    header("Location: ../auth/login.php");
    exit;
}

// Consultar los usuarios que tienen chats iniciados
$query = "SELECT DISTINCT u.id_usuario, u.nombre 
          FROM mensajes_chat m
          JOIN usuarios u ON m.id_usuario = u.id_usuario
          ORDER BY u.nombre ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Chats - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .chat-list ul {
            margin-top: 20px;
        }
        .chat-list ul li {
            margin-bottom: 10px;
        }
        .no-chats {
            font-size: 1.2rem;
            color: gray;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="center-align">Chats Activos con Usuarios</h2>
        
        <!-- Lista de Chats Activos -->
        <div class="chat-list">
            <?php if (!empty($usuarios)): ?>
                <ul class="collection">
                    <?php foreach ($usuarios as $usuario): ?>
                        <li class="collection-item avatar">
                            <i class="material-icons circle blue">chat</i>
                            <span class="title">Chat con <?php echo htmlspecialchars($usuario['nombre']); ?></span>
                            <p>ID de usuario: <?php echo htmlspecialchars($usuario['id_usuario']); ?></p>
                            <a href="user_chat.php?id_usuario=<?php echo $usuario['id_usuario']; ?>" class="secondary-content">
                                <i class="material-icons">Ingresar</i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-chats center-align">No hay chats activos.</p>
            <?php endif; ?>
        </div>

        <!-- Enlace de Regreso al Dashboard -->
        <div class="center-align" style="margin-top: 20px;">
            <a href="../dashboard.php" class="btn grey lighten-1">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
