<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/db.php';

// Incluye los archivos necesarios de la librería Google Authenticator
require_once '../../vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
require_once '../../vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
require_once '../../vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
require_once '../../vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $rol = 'usuario'; // Rol por defecto

    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $query = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Este correo ya está registrado.";
        } else {
            // Encriptar la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Generar clave secreta de Google Authenticator
            $gAuth = new GoogleAuthenticator();
            $secret = $gAuth->generateSecret();

            // Insertar el usuario y la clave secreta en la base de datos
            $insert_query = "INSERT INTO usuarios (nombre, email, password, rol, google_auth_secret) VALUES (:nombre, :email, :password, :rol, :secret)";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':nombre', $nombre);
            $insert_stmt->bindParam(':email', $email);
            $insert_stmt->bindParam(':password', $hashed_password);
            $insert_stmt->bindParam(':rol', $rol);
            $insert_stmt->bindParam(':secret', $secret);

            if ($insert_stmt->execute()) {
                // Generar el código QR para Google Authenticator
                $qrUrl = GoogleQrUrl::generate($email, $secret, 'HelpDesk');
                $_SESSION['qr_url'] = $qrUrl; // Guardar en la sesión para mostrar después
                if (!headers_sent()) {
                    header("Location: /views/auth/show_qr.php");
                    exit;
                }
            } else {
                $error = "Ocurrió un error al registrar la cuenta.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }

        .error {
            color: red;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container register-container z-depth-3">
        <h2 class="center-align">Registro de Usuario</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="nombre">Nombre Completo</label>
            <div class="input-field">
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <label for="email">Correo Electrónico</label>
            <div class="input-field">
                <input type="email" id="email" name="email" required>
            </div>
            <label for="password">Contraseña</label>
            <div class="input-field">
                <input type="password" id="password" name="password" required>
            </div>
            <label for="confirm_password">Confirmar Contraseña</label>
            <div class="input-field">
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="login-link">

        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
            <button type="submit" class="btn blue btn-large waves-effect waves-light" style="width: 100%;">Registrarse</button>
        </form>
    </div>
    

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>

</html>