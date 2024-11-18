<?php
session_start();
require_once '../../config/db.php';






include '../header.php';
// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    echo "Acceso no autorizado.";
    exit;
}

// Verificar que se ha proporcionado un ID válido de transacción
if (!isset($_GET['id'])) {
    echo "ID de transacción no proporcionado.";
    exit;
}

$id = $_GET['id'];

// Obtener los datos actuales de la transacción
$query = "SELECT * FROM cuenta WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$transaccion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaccion) {
    echo "Transacción no encontrada.";
    exit;
}

// Procesar la actualización de la transacción cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $banco = $_POST['banco'];
    $descripcion = $_POST['descripcion'];
    $valor = $_POST['valor'];
    $tipo_transaccion = $_POST['tipo_transaccion'];
    $categoria = $_POST['categoria'];
    $iva = $_POST['iva'] ?? null;
    $retefuente = $_POST['retefuente'] ?? null;
    $impuestos_varios = $_POST['impuestos_varios'] ?? null;
    $gastos_operacion = $_POST['gastos_operacion'] ?? null;
    $modificado_por = $_SESSION['id_usuario']; // ID del usuario que realiza la modificación

    // Consulta de actualización
    $updateQuery = "UPDATE cuenta SET 
                    banco = :banco, 
                    descripcion = :descripcion, 
                    valor = :valor, 
                    tipo_transaccion = :tipo_transaccion, 
                    categoria = :categoria, 
                    iva = :iva, 
                    retefuente = :retefuente, 
                    impuestos_varios = :impuestos_varios, 
                    gastos_operacion = :gastos_operacion, 
                    modificado_por = :modificado_por, 
                    fecha_modificacion = NOW() 
                    WHERE id = :id";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':banco', $banco);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':tipo_transaccion', $tipo_transaccion);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':iva', $iva);
    $stmt->bindParam(':retefuente', $retefuente);
    $stmt->bindParam(':impuestos_varios', $impuestos_varios);
    $stmt->bindParam(':gastos_operacion', $gastos_operacion);
    $stmt->bindParam(':modificado_por', $modificado_por);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>
                alert('Transacción actualizada con éxito');
                window.location.href = 'cuentas_principal.php';
              </script>";
    } else {
        echo "<p class='red-text'>Error al actualizar la transacción.</p>";
    }
    exit;
}
?>

<!-- Formulario de edición de transacción -->
<div class="container">
    <form action="cuentas_editar.php?id=<?php echo $id; ?>" method="POST">
        <label>Banco</label>
        <div class="input-field">
            <select name="banco" required>
                <option value="Bancolombia" <?php echo ($transaccion['banco'] == 'Bancolombia') ? 'selected' : ''; ?>>Bancolombia</option>
                <option value="Nu" <?php echo ($transaccion['banco'] == 'Nu') ? 'selected' : ''; ?>>Nu</option>
                <option value="Nequi" <?php echo ($transaccion['banco'] == 'Nequi') ? 'selected' : ''; ?>>Nequi</option>
                <option value="Efectivo" <?php echo ($transaccion['banco'] == 'Efectivo') ? 'selected' : ''; ?>>Efectivo</option>
            </select>

        </div>
        <label>Descripción</label>
        <div class="input-field">
            <input type="text" name="descripcion" value="<?php echo htmlspecialchars($transaccion['descripcion']); ?>" required>

        </div>
        <label>Valor</label>
        <div class="input-field">
            <input type="number" step="0.01" name="valor" value="<?php echo $transaccion['valor']; ?>" required>

        </div>
        <label>Tipo de Transacción</label>
        <div class="input-field">
            <select name="tipo_transaccion" required>
                <option value="ingreso" <?php echo ($transaccion['tipo_transaccion'] == 'ingreso') ? 'selected' : ''; ?>>Ingreso</option>
                <option value="egreso" <?php echo ($transaccion['tipo_transaccion'] == 'egreso') ? 'selected' : ''; ?>>Egreso</option>
            </select>

        </div>
        <label>Categoría</label>
        <div class="input-field">
            <select name="categoria" required>
                <option value="marketing" <?php echo ($transaccion['categoria'] == 'marketing') ? 'selected' : ''; ?>>Marketing</option>
                <option value="operaciones" <?php echo ($transaccion['categoria'] == 'operaciones') ? 'selected' : ''; ?>>Operaciones</option>
                <option value="personal" <?php echo ($transaccion['categoria'] == 'personal') ? 'selected' : ''; ?>>Personal</option>
                <option value="tecnología" <?php echo ($transaccion['categoria'] == 'tecnología') ? 'selected' : ''; ?>>Tecnología</option>
            </select>

        </div>
        <label>IVA</label>
        <div class="input-field">
            <input type="number" step="0.01" name="iva" value="<?php echo $transaccion['iva']; ?>">

        </div>
        <label>ReteFuente</label>
        <div class="input-field">
            <input type="number" step="0.01" name="retefuente" value="<?php echo $transaccion['retefuente']; ?>">

        </div>
        <label>Impuestos Varios</label>
        <div class="input-field">
            <input type="number" step="0.01" name="impuestos_varios" value="<?php echo $transaccion['impuestos_varios']; ?>">

        </div>
        <label>Gastos Operación</label>
        <div class="input-field">
            <input type="number" step="0.01" name="gastos_operacion" value="<?php echo $transaccion['gastos_operacion']; ?>">

        </div>
        <button type="submit" class="btn">Actualizar</button>
    </form>
</div>