<?php
session_start();
require_once '../../config/db.php';






include '../header.php';
// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id_usuario'];

// Construir la consulta con filtros y asignaciones (solo última asignación)
$query = "
    SELECT i.*, u_asignado.nombre AS asignado_nombre, u_asignado.rol AS asignado_rol, e.fecha_estimacion
    FROM incidencias i
    LEFT JOIN asignaciones a ON i.id_incidencia = a.id_incidencia
    AND a.id_asignacion = (
        SELECT id_asignacion
        FROM asignaciones
        WHERE id_incidencia = i.id_incidencia
        ORDER BY fecha_asignacion DESC
        LIMIT 1
    )
    LEFT JOIN usuarios u_asignado ON a.id_asignado = u_asignado.id_usuario
    LEFT JOIN estimaciones_incidencias e ON i.id_incidencia = e.id_incidencia
    WHERE 1=1
";



$params = [];

// Aplicar restricciones basadas en el rol
if ($rol === 'usuario') {
    // Usuarios finales solo ven sus propias incidencias
    $query .= " AND i.id_usuario = :id_usuario";
    $params[':id_usuario'] = $id_usuario;
} elseif ($rol === 'soporte' || $rol === 'desarrollador') {
    // Soporte y desarrolladores solo ven las incidencias asignadas a ellos
    $query .= " AND a.id_asignado = :id_usuario";
    $params[':id_usuario'] = $id_usuario;
}

// Filtrar si hay parámetros de búsqueda
if (!empty($_GET['titulo'])) {
    $query .= " AND i.titulo LIKE :titulo";
    $params[':titulo'] = '%' . $_GET['titulo'] . '%';
}
if (!empty($_GET['prioridad'])) {
    $query .= " AND i.prioridad = :prioridad";
    $params[':prioridad'] = $_GET['prioridad'];
}
if (!empty($_GET['estado'])) {
    $query .= " AND i.estado = :estado";
    $params[':estado'] = $_GET['estado'];
}
if (!empty($_GET['complejidad'])) {
    $query .= " AND i.complejidad = :complejidad";
    $params[':complejidad'] = $_GET['complejidad'];
}

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar la asignación de incidencias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_asignado'], $_POST['id_incidencia']) && $rol === 'admin') {
    $id_asignado = $_POST['id_asignado'];
    $id_incidencia = $_POST['id_incidencia'];

    // Verificar si la incidencia ya tiene una asignación
    $checkQuery = "SELECT id_asignacion FROM asignaciones WHERE id_incidencia = :id_incidencia";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':id_incidencia', $id_incidencia);
    $checkStmt->execute();
    $existingAssignment = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingAssignment) {
        // Actualizar asignación existente
        $updateQuery = "UPDATE asignaciones SET id_asignado = :id_asignado, fecha_asignacion = NOW() WHERE id_incidencia = :id_incidencia";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':id_asignado', $id_asignado);
        $updateStmt->bindParam(':id_incidencia', $id_incidencia);
        $updateStmt->execute();
    } else {
        // Crear nueva asignación si no existe
        $insertQuery = "INSERT INTO asignaciones (id_incidencia, id_asignado, fecha_asignacion) VALUES (:id_incidencia, :id_asignado, NOW())";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->bindParam(':id_incidencia', $id_incidencia);
        $insertStmt->bindParam(':id_asignado', $id_asignado);
        
        $insertStmt->execute();
    }

    // Redirigir a la misma página para evitar reenvío del formulario
    header("Location: list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Incidencias - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
</head>
<body>
    <div class="container">
        <h2 class="center-align">Listado de Incidencias</h2>

        <!-- Formulario de búsqueda y filtro -->
        <div class="card">
            <div class="card-content">
                <span class="card-title">Buscar Incidencias</span>
                <form method="GET" action="">
                    <div class="input-field">
                        <input type="text" name="titulo" placeholder="Buscar por título" value="<?php echo $_GET['titulo'] ?? ''; ?>">
                        <label for="titulo">Título</label>
                    </div>
                    <label for="prioridad">Prioridad</label>
                    <div class="input-field">
                        <select name="prioridad" class="browser-default">
                            <option value="" disabled selected>Todas las prioridades</option>
                            <option value="baja" <?php if (isset($_GET['prioridad']) && $_GET['prioridad'] == 'baja') echo 'selected'; ?>>Baja</option>
                            <option value="media" <?php if (isset($_GET['prioridad']) && $_GET['prioridad'] == 'media') echo 'selected'; ?>>Media</option>
                            <option value="alta" <?php if (isset($_GET['prioridad']) && $_GET['prioridad'] == 'alta') echo 'selected'; ?>>Alta</option>
                        </select>
                    </div>
                    <label for="estado">Estado</label>
                    <div class="input-field">
                        <select name="estado" class="browser-default">
                            <option value="" disabled selected>Todos los estados</option>
                            <option value="pendiente" <?php if (isset($_GET['estado']) && $_GET['estado'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="en progreso" <?php if (isset($_GET['estado']) && $_GET['estado'] == 'en progreso') echo 'selected'; ?>>En Progreso</option>
                            <option value="en revision" <?php if (isset($_GET['estado']) && $_GET['estado'] == 'en revision') echo 'selected'; ?>>En Revisión</option>
                            <option value="resuelto" <?php if (isset($_GET['estado']) && $_GET['estado'] == 'resuelto') echo 'selected'; ?>>Resuelto</option>
                        </select>
                    </div>
                    <label for="complejidad">Complejidad</label>
                    <div class="input-field">
                        <select name="complejidad" class="browser-default">
                            <option value="" disabled selected>Todas las complejidades</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if (isset($_GET['complejidad']) && $_GET['complejidad'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn blue">Buscar</button>
                </form>
            </div>
        </div>

        <!-- Tabla de Incidencias -->
        <?php if (count($incidencias) > 0): ?>
            <table class="striped centered responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha de Creación</th>
                        <th>Complejidad</th>
                        <th>Asignado a</th>
                        <th>Fecha de Estimación</th>
                        <th>Rol Asignado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidencias as $incidencia): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($incidencia['id_incidencia']); ?></td>
                            <td><?php echo htmlspecialchars($incidencia['titulo']); ?></td>
                            <td><?php echo htmlspecialchars(substr($incidencia['descripcion'], 0, 50)) . '...'; ?></td>
                            <td><?php echo htmlspecialchars($incidencia['prioridad']); ?></td>
                            <td><?php echo htmlspecialchars($incidencia['estado']); ?></td>
                            <td><?php echo htmlspecialchars($incidencia['fecha_creacion']); ?></td>
                            <td><?php echo htmlspecialchars($incidencia['complejidad']); ?></td>
                            <td><?php echo htmlspecialchars($incidencia['asignado_nombre'] ?? 'No asignado'); ?></td>
                            <td><?php echo htmlspecialchars($incidencia['fecha_estimacion'] ?? 'Sin estimación'); ?></td>
                            <td><?php echo htmlspecialchars($incidencia['asignado_rol'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="details.php?id=<?php echo $incidencia['id_incidencia']; ?>">Ver Detalles</a>
                                <?php if ($rol === 'admin'): ?>
                                    <a href="edit.php?id=<?php echo $incidencia['id_incidencia']; ?>">Editar</a>
                                    <form action="assign.php" method="POST" style="display:inline;">
                                        <select name="id_asignado" required>
                                            <option value="">Asignar a...</option>
                                            <?php
                                            // Obtener todos los usuarios que pueden recibir incidencias
                                            $userQuery = "SELECT id_usuario, nombre FROM usuarios WHERE rol = 'soporte' OR rol = 'desarrollador'";
                                            $userStmt = $pdo->prepare($userQuery);
                                            $userStmt->execute();
                                            $usuarios = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($usuarios as $usuario) {
                                                echo '<option value="' . $usuario['id_usuario'] . '">' . htmlspecialchars($usuario['nombre']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden" name="id_incidencia" value="<?php echo $incidencia['id_incidencia']; ?>">
                                        <button type="submit">Asignar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="red-text">No hay incidencias registradas.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
        });
    </script>
</body>
</html>
