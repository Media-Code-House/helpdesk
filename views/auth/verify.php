<?php
session_start();

// Ajusta la ruta usando __DIR__ para evitar problemas de ubicación de archivos
require_once __DIR__ . '/../../vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
require_once __DIR__ . '/../../vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
require_once __DIR__ . '/../../vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
require_once __DIR__ . '/../../vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';


use Sonata\GoogleAuthenticator\GoogleAuthenticator;

// Verificar si el usuario tiene un secreto guardado en la sesión
if (!isset($_SESSION['google_auth_secret'])) {
    header("Location: /views/auth/login.php");
    exit;
}

$error = "";
$gAuth = new GoogleAuthenticator();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = trim($_POST['verification_code']);

    // Validar el código ingresado usando el secreto de Google Authenticator
    if ($gAuth->checkCode($_SESSION['google_auth_secret'], $input_code)) {
        // Código correcto - autenticación completa
        $_SESSION['2fa_verified'] = true; // Establece la marca de verificación para mantener la sesión iniciada
        unset($_SESSION['google_auth_secret']); // Elimina el secreto de la sesión por seguridad

        // Redirige al dashboard o a la página que corresponda tras la autenticación
        header("Location: ../dashboard.php");
        exit;
    } else {
        $error = "Código de verificación incorrecto. Por favor intente nuevamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Google Authenticator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .verify-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }
        .error {
            color: red;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container verify-container z-depth-3">
        <h4 class="center-align">Verificación de Dos Factores</h4>

        <!-- Mensaje de Error -->
        <?php if (!empty($error)): ?>
            <div class="card-panel red lighten-4">
                <span class="error"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulario de Verificación de Código -->
        <form method="POST" action="">
            <label for="verification_code">Código de Verificación</label>
            <div class="input-field">
                <input type="text" id="verification_code" name="verification_code" required>
            </div>

            <button type="submit" class="btn blue btn-large waves-effect waves-light" style="width: 100%;">Verificar</button>
        </form>
    </div>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
