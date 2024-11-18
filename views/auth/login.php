<?php
session_start();
require_once '../../config/db.php';

require_once '../../vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
require_once '../../vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
require_once '../../vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
require_once '../../vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Verificar las credenciales
    $query = "SELECT * FROM usuarios WHERE email = :email AND estado = 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            // Guardar datos en la sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['google_auth_secret'] = $user['google_auth_secret'];

            // Redirigir a la página de verificación de Google Authenticator
            header("Location: verify.php");
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No se encontró una cuenta con ese correo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - HelpDesk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        .login-container {
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
    <div class="container login-container z-depth-3">
        <h2 class="center-align">Iniciar Sesión</h2>

        <!-- Mensaje de Error -->
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Formulario de Inicio de Sesión -->
        <form method="POST" action="">
            <label for="email">Correo Electrónico</label>
            <div class="input-field">
                <input type="email" id="email" name="email" required>
            </div>
            <label for="password">Contraseña</label>
            <div class="input-field">
                <input type="password" id="password" name="password" required>
            </div>
            <div class="login-link">
        <p>¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>

    </div>
            <button type="submit" class="btn blue btn-large waves-effect waves-light" style="width: 100%;">Ingresar</button>
        </form>
        
    </div>
    

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>

</html>