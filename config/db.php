<?php
$host = 'localhost';
$db_name = 'helpdesk';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Detiene el script en caso de error sin salida directa
    exit("Error de conexión"); 
}
