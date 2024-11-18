

<?php
session_start();
require_once '../../config/db.php';






include '../header.php';

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Variables de filtro
$idFiltro = $_GET['id'] ?? '';
$bancoFiltro = $_GET['banco'] ?? '';
$usuarioFiltro = $_GET['usuario'] ?? '';

// Construir la consulta con filtros dinámicos
$query = "SELECT * FROM cuenta WHERE 1=1";
$params = [];

if ($idFiltro) {
    $query .= " AND id = :id";
    $params[':id'] = $idFiltro;
}

if ($bancoFiltro) {
    $query .= " AND banco = :banco";
    $params[':banco'] = $bancoFiltro;
}

if ($usuarioFiltro) {
    $query .= " AND usuario LIKE :usuario";
    $params[':usuario'] = "%$usuarioFiltro%";
}

$query .= " ORDER BY fecha DESC";
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal - Cuentas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
</head>

<body>
    <div class="container">
        <h2 class="center-align">Cuentas - Movimientos y Transacciones</h2>

        <!-- Formulario de Filtros -->
        <div class="card">
            <div class="card-content">
                <form method="GET" action="">
                    <div class="row">
                        <div class="input-field col s4">
                            <label>ID</label>
                            <input type="number" name="id" placeholder="ID" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                            </ placeholder="ID" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                        </div>
                        <div class="input-field col s4">
                            <div>
                                <label>Banco</label>
                                <select name="banco" class="browser-default">
                                    <option value="" selected>Todos los bancos</option>
                                    <option value="Bancolombia" <?php if (isset($_GET['banco']) && $_GET['banco'] == 'Bancolombia') echo 'selected'; ?>>Bancolombia</option>
                                    <option value="Nu" <?php if (isset($_GET['banco']) && $_GET['banco'] == 'Nu') echo 'selected'; ?>>Nu</option>
                                    <option value="Nequi" <?php if (isset($_GET['banco']) && $_GET['banco'] == 'Nequi') echo 'selected'; ?>>Nequi</option>
                                    <option value="Efectivo" <?php if (isset($_GET['banco']) && $_GET['banco'] == 'Efectivo') echo 'selected'; ?>>Efectivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-field col s4">
                            <input type="text" name="usuario" placeholder="Usuario" value="<?php echo htmlspecialchars($_GET['usuario'] ?? ''); ?>">
                            <label>Usuario</label>
                        </div>
                    </div>
                    <div>
                    <button type="submit" class="btn blue">Filtrar</button>

                    
                        <a class="right-align btn modal-trigger" href="#modalAgregar">Agregar Transacción</a>
                    
                    </div>
                </form>
            </div>
        </div>

        <div id="modalAgregar" class="modal">
            <div class="modal-content">
                <h4>Agregar Nueva Transacción</h4>
                <form id="formAgregar" action="cuentas_agregar.php" method="POST">
                <label>Banco</label>
                    <div class="input-field">
                        <select name="banco" required>
                            <option value="" disabled selected>Selecciona el Banco</option>
                            <option value="Bancolombia">Bancolombia</option>
                            <option value="Nu">Nu</option>
                            <option value="Nequi">Nequi</option>
                            <option value="Efectivo">Efectivo</option>
                        </select>
                       
                    </div><label>Descripción</label>
                    <div class="input-field">
                        <input type="text" name="descripcion" required>
                        
                    </div>
                    <label>Valor</label>
                    <div class="input-field">
                        <input type="number" step="0.01" name="valor" required>
                        
                    </div>
                    <label>Tipo de Transacción</label>
                    <div class="input-field">
                        <select name="tipo_transaccion" required>
                            <option value="" disabled selected>Tipo de Transacción</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                        
                    </div>
                    <label>Categoría</label>
                    <div class="input-field">
                        <select name="categoria" required>
                            <option value="" disabled selected>Categoría</option>
                            <option value="marketing">Marketing</option>
                            <option value="operaciones">Operaciones</option>
                            <option value="personal">Personal</option>
                            <option value="tecnología">Tecnología</option>
                        </select>
                      
                    </div>
                    <button type="submit" class="btn">Guardar</button>
                </form>
            </div>
        </div>

        <!-- Tabla de Transacciones -->
        <?php if (count($transacciones) > 0): ?>
            <table class="striped centered responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Banco</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                        <th>Valor</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>IVA</th>
                        <th>ReteFuente</th>
                        <th>Impuestos Varios</th>
                        <th>Gastos Operación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transacciones as $transaccion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaccion['id']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['banco']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['valor']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['tipo_transaccion']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['categoria']); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['iva'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['retefuente'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['impuestos_varios'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($transaccion['gastos_operacion'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="#modalEditar" class="btn modal-trigger" onclick="abrirModalEditar(<?php echo $transaccion['id']; ?>)">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="red-text">No hay transacciones registradas para los filtros seleccionados.</p>
        <?php endif; ?>
    </div>

    <!-- Modal de Editar Transacción -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h4>Editar Transacción</h4>
            <div id="contenido-modal"></div>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
            const modals = document.querySelectorAll('.modal');
            M.Modal.init(modals);
        });

        function abrirModalEditar(id) {

            const modalAgregarInstance = M.Modal.getInstance(document.getElementById('modalAgregar'));
            if (modalAgregarInstance.isOpen) modalAgregarInstance.close();

            
            const modalContent = document.getElementById('contenido-modal');
            fetch(`cuentas_editar.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                    const modal = M.Modal.getInstance(document.getElementById('modalEditar'));
                    modal.open();
                })
                .catch(error => {
                    console.error('Error cargando el contenido:', error);
                });
        }
    </script>
</body>

</html>