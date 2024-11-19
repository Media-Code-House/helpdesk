<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener el rol del usuario y el ID de la incidencia desde la URL
$rol = $_SESSION['rol'];
$id_incidencia = isset($_GET['id']) ? $_GET['id'] : null;

// Verificar que se haya proporcionado un ID de incidencia
if (!$id_incidencia) {
    echo "ID de incidencia no proporcionado.";
    exit;
}

// Obtener los detalles de la incidencia desde la base de datos
$query = "SELECT * FROM incidencias WHERE id_incidencia = :id_incidencia";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_incidencia', $id_incidencia);
$stmt->execute();
$incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si la incidencia existe
if (!$incidencia) {
    echo "Incidencia no encontrada.";
    exit;
}

// Procesar cambios en el estado de la incidencia (solo para soporte y admin)
// Procesar cambios en el estado de la incidencia (solo para soporte y admin)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $rol !== 'usuario') {
    $nuevo_estado = $_POST['estado'];

    // Verificar si el estado realmente ha cambiado
    if ($incidencia['estado'] !== $nuevo_estado) {
        // Actualizar el estado de la incidencia en la base de datos
        $update_query = "UPDATE incidencias SET estado = :estado WHERE id_incidencia = :id_incidencia";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':estado', $nuevo_estado);
        $update_stmt->bindParam(':id_incidencia', $id_incidencia);

        if ($update_stmt->execute()) {
            // Registrar el cambio en el historial
            $comentario = "Estado actualizado de " . $incidencia['estado'] . " a " . $nuevo_estado;
            $insert_historial_query = "INSERT INTO historial_cambios (id_incidencia, id_usuario, estado_anterior, estado_nuevo, comentario, fecha_cambio) 
                                       VALUES (:id_incidencia, :id_usuario, :estado_anterior, :estado_nuevo, :comentario, NOW())";
            $historial_stmt = $pdo->prepare($insert_historial_query);
            $historial_stmt->bindParam(':id_incidencia', $id_incidencia);
            $historial_stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);
            $historial_stmt->bindParam(':estado_anterior', $incidencia['estado']);
            $historial_stmt->bindParam(':estado_nuevo', $nuevo_estado);
            $historial_stmt->bindParam(':comentario', $comentario);
            $historial_stmt->execute();

            // Registrar notificación
            $mensaje_notificacion = "El estado de la incidencia ID $id_incidencia cambió de " . $incidencia['estado'] . " a " . $nuevo_estado;
            $insert_notificacion_query = "INSERT INTO notificaciones (id_incidencia, id_usuario, mensaje) 
                                  VALUES (:id_incidencia, :id_usuario, :mensaje)";
            $notificacion_stmt = $pdo->prepare($insert_notificacion_query);
            $notificacion_stmt->bindParam(':id_incidencia', $id_incidencia);
            $notificacion_stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);
            $notificacion_stmt->bindParam(':mensaje', $mensaje_notificacion);
            $notificacion_stmt->execute();
        } else {
            $mensaje = "Error al actualizar el estado.";
        }
    } else {
        $mensaje = "No se realizaron cambios en el estado.";
    }
}


?>

<!DOCTYPE html>
<html lang="es">

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Incidencia - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
</head>

<body>
    <div class="container">
        <!-- Tarjeta de Detalles de Incidencia -->
        <div class="card">
            <div class="card-content">
                <span class="card-title">Detalles de la Incidencia</span>

                <?php if (!empty($mensaje)): ?>
                    <p class="mensaje red-text"><?php echo htmlspecialchars($mensaje); ?></p>
                <?php endif; ?>

                <p><strong>ID:</strong> <?php echo htmlspecialchars($incidencia['id_incidencia']); ?></p>
                <p><strong>Título:</strong> <?php echo htmlspecialchars($incidencia['titulo']); ?></p>
                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($incidencia['descripcion']); ?></p>
                <p><strong>Prioridad:</strong> <?php echo htmlspecialchars($incidencia['prioridad']); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($incidencia['estado']); ?></p>
                <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($incidencia['fecha_creacion']); ?></p>
                <p><strong>Complejidad:</strong> <?php echo htmlspecialchars($incidencia['complejidad']); ?></p>

                <?php if ($incidencia['archivo_adjunto']): ?>
                    <p><strong>Archivo Adjunto:</strong> <a href="../../<?php echo htmlspecialchars($incidencia['archivo_adjunto']); ?>" target="_blank" class="blue-text">Ver Archivo</a></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($rol !== 'usuario'): ?>
            <!-- Formulario para Actualizar Estado -->
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Actualizar Estado de la Incidencia</span>
                    <form method="POST" action="">
                    <label for="estado">Estado</label>
                        <div class="input-field">
                            <select id="estado" name="estado" required class="browser-default">
                                <option value="pendiente" <?php if ($incidencia['estado'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                                <option value="en progreso" <?php if ($incidencia['estado'] == 'en progreso') echo 'selected'; ?>>En Progreso</option>
                                <option value="en revision" <?php if ($incidencia['estado'] == 'en revision') echo 'selected'; ?>>En Revisión</option>
                                <option value="resuelto" <?php if ($incidencia['estado'] == 'resuelto') echo 'selected'; ?>>Resuelto</option>
                            </select>
                            
                        </div>
                        <button type="submit" class="btn blue">Actualizar Estado</button>
                    </form>
                </div>
                <div class="card-action">
                    <a href="<?php echo $incidencias; ?>chat.php?id=<?php echo $id_incidencia; ?>" class="blue-text">Abrir Chat de esta Incidencia</a>
                    <a href="historial.php?id=<?php echo $id_incidencia; ?>" class="blue-text">Ver Historial de Cambios</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
        });
    </script>
</body>
</html>


</html>