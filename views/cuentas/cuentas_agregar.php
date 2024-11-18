<?php
session_start();
require_once '../../config/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener el nombre del usuario desde la sesión
$usuario = $_SESSION['nombre'];

// Procesar el formulario de agregar cuenta solo si el método es POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $banco = $_POST['banco'];
    $descripcion = $_POST['descripcion'];
    $valor = $_POST['valor'];
    $tipo_transaccion = $_POST['tipo_transaccion'];
    $categoria = $_POST['categoria'];
    $iva = $_POST['iva'] ?? null;
    $retefuente = $_POST['retefuente'] ?? null;
    $impuestos_varios = $_POST['impuestos_varios'] ?? null;
    $gastos_operacion = $_POST['gastos_operacion'] ?? null;

    // Insertar la transacción en la base de datos
    $query = "INSERT INTO cuenta (banco, descripcion, usuario, valor, tipo_transaccion, categoria, iva, retefuente, impuestos_varios, gastos_operacion) 
              VALUES (:banco, :descripcion, :usuario, :valor, :tipo_transaccion, :categoria, :iva, :retefuente, :impuestos_varios, :gastos_operacion)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':banco', $banco);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':usuario', $usuario); // Usuario de la sesión
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':tipo_transaccion', $tipo_transaccion);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':iva', $iva);
    $stmt->bindParam(':retefuente', $retefuente);
    $stmt->bindParam(':impuestos_varios', $impuestos_varios);
    $stmt->bindParam(':gastos_operacion', $gastos_operacion);

    if ($stmt->execute()) {
        header("Location: cuentas_principal.php");
        exit;
    } else {
        echo "Error al agregar la transacción.";
    }
}

// Incluir el header solo después de las redirecciones
include '../header.php';
?>
