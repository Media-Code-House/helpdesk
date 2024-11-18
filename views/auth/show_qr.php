<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Verificar si el QR está en la sesión
if (!isset($_SESSION['qr_url'])) {
    header("Location: register.php");
    exit;
}

$qrUrl = $_SESSION['qr_url'];
unset($_SESSION['qr_url']); // Limpiar el QR después de mostrarlo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escanea el Código QR - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
</head>
<body>
    <div class="container center-align" style="margin-top: 50px;">
        <h5>Escanea este código QR con Google Authenticator</h5>
        <img src="<?php echo $qrUrl; ?>" alt="Código QR">
        <p>Este código te permitirá activar la autenticación de dos factores en tu cuenta.</p>
        <a href="/views/users/list_users.php" class="btn blue">Continuar</a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
