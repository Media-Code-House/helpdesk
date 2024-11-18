<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $prioridad = $_POST['prioridad'];
    $complejidad = $_POST['complejidad']; // Asegurarse de que se captura el valor de complejidad
    $id_usuario = $_SESSION['id_usuario'];
    $fecha_estimacion = $_POST['fecha_estimacion'];


    // Manejar la subida de archivo
    $archivo = null;
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $archivo = 'uploads/incidencias/' . uniqid() . '_' . $_FILES['archivo']['name'];
        move_uploaded_file($_FILES['archivo']['tmp_name'], '../../' . $archivo);
    }

    // Insertar la incidencia en la base de datos, incluyendo el campo 'complejidad'
    $query = "INSERT INTO incidencias (titulo, descripcion, id_usuario, prioridad, archivo_adjunto, complejidad) 
    VALUES (:titulo, :descripcion, :id_usuario, :prioridad, :archivo, :complejidad)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':prioridad', $prioridad);
    $stmt->bindParam(':complejidad', $complejidad);
    $stmt->bindParam(':archivo', $archivo);

    if ($stmt->execute()) {
        $id_incidencia = $pdo->lastInsertId(); // Obtiene el ID de la incidencia recién insertada
    } else {
        $error = "Hubo un problema al reportar la incidencia.";
    }
    if (isset($id_incidencia)) {
        $insert_estimacion_query = "INSERT INTO estimaciones_incidencias (id_incidencia, fecha_estimacion) 
                                    VALUES (:id_incidencia, :fecha_estimacion)";
        $estimacion_stmt = $pdo->prepare($insert_estimacion_query);
        $estimacion_stmt->bindParam(':id_incidencia', $id_incidencia);
        $estimacion_stmt->bindParam(':fecha_estimacion', $fecha_estimacion);
        if ($estimacion_stmt->execute()) {
            $success = "La incidencia fue reportada exitosamente con fecha de estimación.";
        } else {
            $error = "Hubo un problema al guardar la fecha de estimación.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Incidencia - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        /* Estilos para el header fijo */
        .header {
            background-color: #1976d2;
            /* Color de fondo */
            color: white;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            /* Asegura que el header esté delante del contenido */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            /* Sombra para el header */
        }

        .container {
            margin-top: 80px;
            /* Deja espacio para el header */
        }
    </style>
</head>

<body>
    <!-- Encabezado fijo -->


    <div class="container">
        <h2 class="center-align">Reportar Nueva Incidencia</h2>

        <!-- Mensajes de éxito o error -->
        <?php if (!empty($success)): ?>
            <p class="green-text"><?php echo htmlspecialchars($success); ?></p>
        <?php elseif (!empty($error)): ?>
            <p class="red-text"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Formulario de Reporte de Incidencia -->
        <div class="card">
            <div class="card-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="input-field">
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" required>
                    </div>
                    <label for="descripcion">Descripción</label>
                    <div class="input-field">

                        <textarea id="descripcion" name="descripcion" class="materialize-textarea" required></textarea>
                    </div>
                    <label for="prioridad">Prioridad</label>
                    <div class="input-field">
                        <select id="prioridad" name="prioridad" required class="browser-default">
                            <option value="" disabled selected>Selecciona Prioridad</option>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>

                    </div>
                    <label for="complejidad">Complejidad</label>
                    <div class="input-field">
                        <select name="complejidad" id="complejidad" class="browser-default">
                            <option value="" disabled selected>Selecciona Complejidad</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>

                    </div>
                    <label for="fecha_estimacion">Fecha de Estimación</label>
                    <div class="input-field">

                        <input type="date" id="fecha_estimacion" name="fecha_estimacion" required>
                    </div>



                    <div class="file-field input-field">
                        <div class="btn">
                            <span>Archivo</span>
                            <input type="file" id="archivo" name="archivo">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text" placeholder="Adjuntar Archivo (opcional)">
                        </div>
                    </div>

                    <button type="submit" class="btn blue">Reportar Incidencia</button>
                </form>
            </div>
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