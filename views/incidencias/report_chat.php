<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario es administrador o soporte
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'soporte') {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener todos los chats activos por usuario
$query = "SELECT DISTINCT u.id_usuario, u.nombre AS usuario_nombre, m.fecha_mensaje 
          FROM mensajes_chat m
          JOIN usuarios u ON m.id_usuario = u.id_usuario
          ORDER BY m.fecha_mensaje DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$chats_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Chats Activos - HelpDesk</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
    <div class="chat-list-container">
        <h2>Lista de Chats Activos</h2>

        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Último Mensaje</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chats_usuarios as $chat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($chat['usuario_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($chat['fecha_mensaje']); ?></td>
                        <td><a href="user_chat.php?id_usuario=<?php echo $chat['id_usuario']; ?>">Ver Chat</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
