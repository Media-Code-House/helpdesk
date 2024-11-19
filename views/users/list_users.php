<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar que el usuario es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener la lista de usuarios
$query = "SELECT id_usuario, nombre, email, rol, estado FROM usuarios";
$stmt = $pdo->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .list-users-container {
            max-width: 1000px;
            margin: 50px auto;
        }
        .table-container {
            overflow-x: auto;
        }
        .btn-update-role {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container list-users-container">
        <h2 class="center-align">Gestión de Usuarios</h2>

        <!-- Tabla de Usuarios -->
        <?php if (count($usuarios) > 0): ?>
            <div class="table-container">
                <table class="striped centered responsive-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['estado']); ?></td>
                                <td>
                                    <!-- Formulario para Cambiar Rol -->
                                    <form action="assign_role.php" method="POST" style="display:inline;">
                                        <div class="input-field">
                                            <select name="nuevo_rol" required class="browser-default">
                                                <option value="" disabled selected>Cambiar Rol</option>
                                                <option value="admin" <?php if ($usuario['rol'] == 'admin') echo 'selected'; ?>>Admin</option>
                                                <option value="soporte" <?php if ($usuario['rol'] == 'soporte') echo 'selected'; ?>>Soporte</option>
                                                <option value="usuario" <?php if ($usuario['rol'] == 'usuario') echo 'selected'; ?>>Usuario</option>
                                                <option value="desarrollador" <?php if ($usuario['rol'] == 'desarrollador') echo 'selected'; ?>>Desarrollador</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                        <button type="submit" class="btn btn-small blue btn-update-role">Actualizar Rol</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="center-align">No hay usuarios registrados.</p>
        <?php endif; ?>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
