<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario está autenticado y tiene permisos de edición
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] == 'usuario')) {
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

// Obtener los detalles de la incidencia desde la base de datos
$query = "
    SELECT i.*, e.fecha_estimacion 
    FROM incidencias i
    LEFT JOIN estimaciones_incidencias e ON i.id_incidencia = e.id_incidencia
    WHERE i.id_incidencia = :id_incidencia
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_incidencia', $id_incidencia);
$stmt->execute();
$incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si la incidencia existe
if (!$incidencia) {
    echo "Incidencia no encontrada.";
    exit;
}

// Procesar el formulario de edición cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $prioridad = $_POST['prioridad'];
    $estado = $_POST['estado'];
    $complejidad = $_POST['complejidad'];
    $fecha_estimacion = $_POST['fecha_estimacion'];


    // Actualizar la incidencia en la base de datos
    $update_query = "UPDATE incidencias SET titulo = :titulo, descripcion = :descripcion, prioridad = :prioridad, estado = :estado, complejidad = :complejidad WHERE id_incidencia = :id_incidencia";
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->bindParam(':titulo', $titulo);
    $update_stmt->bindParam(':descripcion', $descripcion);
    $update_stmt->bindParam(':prioridad', $prioridad);
    $update_stmt->bindParam(':estado', $estado);
    $update_stmt->bindParam(':complejidad', $complejidad);
    $update_stmt->bindParam(':id_incidencia', $id_incidencia);

    if ($update_stmt->execute()) {
        $mensaje = "Incidencia actualizada con éxito.";

        // Verificar que el usuario tenga un id_usuario en sesión
        if (isset($_SESSION['admin']) && !empty($_SESSION['admin'])) {
            $id_usuario = $_SESSION['id_usuario'];
            $mensaje_notificacion = "La incidencia ID $id_incidencia fue actualizada. Nuevo estado: $estado, Prioridad: $prioridad, Complejidad: $complejidad";

            // Registro de notificación
            $insert_notificacion_query = "INSERT INTO notificaciones (id_incidencia, id_usuario, mensaje) 
                                          VALUES (:id_incidencia, :id_usuario, :mensaje)";
            $notificacion_stmt = $pdo->prepare($insert_notificacion_query);
            $notificacion_stmt->bindParam(':id_incidencia', $id_incidencia);
            $notificacion_stmt->bindParam(':id_usuario', $id_usuario);
            $notificacion_stmt->bindParam(':mensaje', $mensaje_notificacion);
            $notificacion_stmt->execute();
        } else {
            echo "Error: Usuario no autenticado.";
        }

        // Actualizar los datos de la incidencia en la variable para reflejar los cambios en la página
        $incidencia['titulo'] = $titulo;
        $incidencia['descripcion'] = $descripcion;
        $incidencia['prioridad'] = $prioridad;
        $incidencia['estado'] = $estado;
        $incidencia['complejidad'] = $complejidad;
    } else {
        $mensaje = "Error al actualizar la incidencia.";
    }
    // Comprobar si ya existe una fecha de estimación para esta incidencia
    $check_query = "SELECT id_estimacion FROM estimaciones_incidencias WHERE id_incidencia = :id_incidencia";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':id_incidencia', $id_incidencia);
    $check_stmt->execute();
    $existing_estimacion = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_estimacion) {
        // Si existe, actualizar la fecha de estimación
        $update_estimacion_query = "UPDATE estimaciones_incidencias SET fecha_estimacion = :fecha_estimacion WHERE id_incidencia = :id_incidencia";
        $update_estimacion_stmt = $pdo->prepare($update_estimacion_query);
        $update_estimacion_stmt->bindParam(':fecha_estimacion', $fecha_estimacion);
        $update_estimacion_stmt->bindParam(':id_incidencia', $id_incidencia);
        $update_estimacion_stmt->execute();
    } else {
        // Si no existe, insertar una nueva fecha de estimación
        $insert_estimacion_query = "INSERT INTO estimaciones_incidencias (id_incidencia, fecha_estimacion) VALUES (:id_incidencia, :fecha_estimacion)";
        $insert_estimacion_stmt = $pdo->prepare($insert_estimacion_query);
        $insert_estimacion_stmt->bindParam(':id_incidencia', $id_incidencia);
        $insert_estimacion_stmt->bindParam(':fecha_estimacion', $fecha_estimacion);
        $insert_estimacion_stmt->execute();
    }
    

    // Registrar en el historial si el estado cambió
    if ($incidencia['estado'] !== $estado) {
        $comentario = "Cambio realizado en la incidencia";
        $insert_historial_query = "INSERT INTO historial_cambios (id_incidencia, id_usuario, estado_anterior, estado_nuevo, comentario) 
                                   VALUES (:id_incidencia, :id_usuario, :estado_anterior, :estado_nuevo, :comentario)";
        $historial_stmt = $pdo->prepare($insert_historial_query);
        $historial_stmt->bindParam(':id_incidencia', $id_incidencia);
        $historial_stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);
        $historial_stmt->bindParam(':estado_anterior', $incidencia['estado']);
        $historial_stmt->bindParam(':estado_nuevo', $estado);
        $historial_stmt->bindParam(':comentario', $comentario);
        $historial_stmt->execute();
    }
}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Incidencia - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .edit-incidencia-container {
            max-width: 800px;
            margin: 20px auto;
        }

        .mensaje {
            font-size: 1.2rem;
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container edit-incidencia-container">
        <h2 class="center-align">Editar Incidencia</h2>

        <!-- Mensaje de éxito o error -->
        <?php if (!empty($mensaje)): ?>
            <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <!-- Formulario de Edición de Incidencia -->
        <div class="card">
            <div class="card-content">
                <form method="POST" action="">
                    <div class="input-field">
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($incidencia['titulo']); ?>" required>
                    </div>

                    <div class="input-field">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="materialize-textarea" required><?php echo htmlspecialchars($incidencia['descripcion']); ?></textarea>
                    </div>
                    <label for="prioridad">Prioridad</label>
                    <div class="input-field">
                        <select id="prioridad" name="prioridad" required class="browser-default">
                            <option value="" disabled selected>Selecciona Prioridad</option>
                            <option value="baja" <?php if ($incidencia['prioridad'] == 'baja') echo 'selected'; ?>>Baja</option>
                            <option value="media" <?php if ($incidencia['prioridad'] == 'media') echo 'selected'; ?>>Media</option>
                            <option value="alta" <?php if ($incidencia['prioridad'] == 'alta') echo 'selected'; ?>>Alta</option>
                        </select>

                    </div>
                    <label for="estado">Estado</label>
                    <div class="input-field">
                        <select id="estado" name="estado" required class="browser-default">
                            <option value="" disabled selected>Selecciona Estado</option>
                            <option value="pendiente" <?php if ($incidencia['estado'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="en progreso" <?php if ($incidencia['estado'] == 'en progreso') echo 'selected'; ?>>En Progreso</option>
                            <option value="en revision" <?php if ($incidencia['estado'] == 'en revision') echo 'selected'; ?>>En Revisión</option>
                            <option value="resuelto" <?php if ($incidencia['estado'] == 'resuelto') echo 'selected'; ?>>Resuelto</option>
                        </select>

                    </div>
                  
                    <label for="complejidad">Complejidad</label>
                    <div class="input-field">
                        <select name="complejidad" id="complejidad" class="browser-default">
                            <option value="" disabled selected>Selecciona Complejidad</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if ($incidencia['complejidad'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>

                    </div>
                    <label for="fecha_estimacion">Fecha de Estimación</label>
                    <div class="input-field">

                        <input type="date" id="fecha_estimacion" name="fecha_estimacion" value="<?php echo htmlspecialchars($incidencia['fecha_estimacion'] ?? ''); ?>" required>
                    </div>


                    <button type="submit" class="btn blue">Actualizar Incidencia</button>
                </form>
            </div>
        </div>

        <!-- Enlace de Regreso al Listado de Incidencias -->
        <div class="center-align" style="margin-top: 20px;">
            <a href="list.php" class="btn grey lighten-1">Volver al listado de incidencias</a>
        </div>
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