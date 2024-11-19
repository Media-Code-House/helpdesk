<?php
// Iniciar la sesión solo si no ha sido iniciada previamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir a la autenticación de 2FA o al login si no está autenticado
if (isset($_SESSION['id_usuario'])) {
    if (!isset($_SESSION['2fa_verified'])) {
        header("Location: /views/auth/verify.php");
        exit;
    }
} else {
    header("Location: /views/auth/login.php");
    exit;
}

// Configuración de variables de rutas
$base_url = '/public/';
$view = '/views/';

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';


$notificaciones = [];
if (isset($_SESSION['id_usuario'])) {
    $query = "SELECT mensaje, fecha_notificacion FROM notificaciones 
              WHERE id_usuario = :id_usuario 
              ORDER BY fecha_notificacion DESC 
              LIMIT 3";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $_SESSION['id_usuario']);
    $stmt->execute();
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Code House</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css">
    <style>
        /* Reduce padding in container on small screens */
        @media (max-width: 600px) {
            .container {
                padding: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>

<body>
    <!-- Encabezado y menú de navegación -->
    <nav>
        <div class="nav-wrapper blue darken-3">
            <a href="<?php echo $view ?>dashboard.php" class="brand-logo left">Media Code House</a>
            <a href="#" data-target="mobile-menu" class="sidenav-trigger right"><i class="bx bx-menu"></i></a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li><a href="<?php echo $view; ?>dashboard.php">Inicio</a></li>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a href="<?php echo $view; ?>/calendario/calendario.php">Calendario</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a class="dropdown-trigger" href="#!" data-target="dropdown-clientes">Clientes<i class="bx bx-chevron-down right"></i></a></li>
                    <ul id="dropdown-clientes" class="dropdown-content">
                        <li><a href="<?php echo $view; ?>clientes/nuevo.php">Agregar Cliente o Prospecto</a></li>
                        <li><a href="<?php echo $view; ?>clientes/list_prospecto.php">Lista de Prospecto</a></li>
                        <li><a href="<?php echo $view; ?>clientes/list_cliente.php">Lista de Clientes</a></li>
                    </ul>
                <?php endif; ?>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a class="dropdown-trigger" href="#!" data-target="dropdown-objetivos">Objetivos<i class="bx bx-chevron-down right"></i></a></li>
                    <ul id="dropdown-objetivos" class="dropdown-content">
                        <li><a href="<?php echo $view; ?>objetivo/objetivos_list.php">lista de objetivos</a></li>
                        <li><a href="<?php echo $view; ?>objetivo/objetivos.php">agregar objetivos</a></li>
                        
                    </ul>
                <?php endif; ?>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a class="dropdown-trigger" href="#!" data-target="dropdown-cuentas">Gestionar cuentas<i class="bx bx-chevron-down right"></i></a></li>
                    <ul id="dropdown-cuentas" class="dropdown-content">
                        <li><a href="<?php echo $view; ?>cuentas/cuentas_graficos.php">Dashboard</a></li>
                        <li><a href="<?php echo $view; ?>cuentas/cuentas_principal.php">Cuentas</a></li>
                    </ul>
                <?php endif; ?>

                <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'soporte'): ?>
                    <li><a class="dropdown-trigger" href="#!" data-target="dropdown-incidencias">Gestionar Incidencias<i class="bx bx-chevron-down right"></i></a></li>
                    <ul id="dropdown-incidencias" class="dropdown-content">
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <li><a href="<?php echo $view; ?>incidencias/create.php">Crear Incidencia</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $view; ?>incidencias/list.php">Gestionar Incidencias</a></li>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <li><a href="<?php echo $view; ?>incidencias/notificaciones.php">Notificaciones</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a class="dropdown-trigger" href="#!" data-target="dropdown-requisitos">requisitos  <i class="bx bx-chevron-down right"></i></a></li>
                    <ul id="dropdown-requisitos" class="dropdown-content">
                        <li><a href="<?php echo $view; ?>requisitos/requerimientos.php">crear requisito</a></li>
                        <li><a href="<?php echo $view; ?>requisitos/requerimientos_list.php">lista de requisitos</a></li>
                        
                        
                    </ul>
                <?php endif; ?>

                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a class="dropdown-trigger" href="#!" data-target="dropdown-chats">Gestionar Chats<i class="bx bx-chevron-down right"></i></a></li>
                    <ul id="dropdown-chats" class="dropdown-content">
                        <li><a href="<?php echo $view; ?>incidencias/list_chats.php">Lista de Chats</a></li>
                        <li><a href="<?php echo $view; ?>incidencias/user_chat.php">Chat con Admin</a></li>
                    </ul>

                    <li><a class="dropdown-trigger" href="#!" data-target="dropdown-usuarios">Gestionar Usuarios<i class="bx bx-chevron-down right"></i></a></li>
                    <ul id="dropdown-usuarios" class="dropdown-content">
                        <li><a href="<?php echo $view; ?>users/list_users.php">Roles</a></li>
                        <li><a href="<?php echo $view; ?>auth/register.php">Registrar Nuevo Usuario</a></li>
                    </ul>
                <?php endif; ?>

                <?php if ($_SESSION['rol'] === 'soporte' || $_SESSION['rol'] === 'usuario'): ?>
                    <li><a href="<?php echo $view; ?>incidencias/user_chat.php">Chat con Admin</a></li>
                <?php endif; ?>

                <li><a href="<?php echo $view; ?>auth/logout.php">Cerrar</a></li>
            </ul>
        </div>
    </nav>

    <!-- Sidenav para dispositivos móviles -->
    <!-- Sidenav para dispositivos móviles con campo de búsqueda y lista completa -->
    <ul class="sidenav" id="mobile-menu">
        <li>
            <div class="input-field" style="padding-left: 10px; padding-right: 10px;">
                <input type="text" id="search-menu" placeholder="Buscar..." onkeyup="filterMenu()">
            </div>
        </li>
        <li><a class="menu-item" href="<?php echo $base_url; ?>index.php">Inicio</a></li>

        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'soporte'): ?>
            <li><a class="menu-item" href="<?php echo $view; ?>incidencias/list.php">Gestionar Incidencias</a></li>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <li><a class="menu-item" href="<?php echo $view; ?>incidencias/create.php">Crear Incidencia</a></li>
                <li><a class="menu-item" href="<?php echo $view; ?>incidencias/notificaciones.php">Notificaciones</a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <li><a class="menu-item" href="<?php echo $view; ?>clientes/nuevo.php">Agregar Cliente o Prospecto</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>clientes/list_prospecto.php">Lista de Prospecto</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>clientes/list_cliente.php">Lista de Clientes</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>objetivo/objetivos_list.php">Lista de Objetivos</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>cuentas/cuentas_graficos.php">Dashboard</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>cuentas/cuentas_principal.php">Cuentas</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>incidencias/list_chats.php">Lista de Chats</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>incidencias/user_chat.php">Chat con Admin</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>users/list_users.php">Roles</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>auth/register.php">Registrar Nuevo Usuario</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>requisitos/requerimientos.php">crear requisito</a></li>
            <li><a class="menu-item" href="<?php echo $view; ?>requisitos/requerimientos_list.php">lista de requisitos</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['rol'] === 'soporte' || $_SESSION['rol'] === 'usuario'): ?>
            <li><a class="menu-item" href="<?php echo $view; ?>incidencias/user_chat.php">Chat con Admin</a></li>
        <?php endif; ?>

        <li><a class="menu-item" href="<?php echo $view; ?>auth/logout.php">Cerrar</a></li>
    </ul>



    <!-- Scripts de Materialize -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dropdowns = document.querySelectorAll('.dropdown-trigger');
            M.Dropdown.init(dropdowns, {
                coverTrigger: false,
                constrainWidth: false
            });

            var sidenav = document.querySelectorAll('.sidenav');
            M.Sidenav.init(sidenav);

            var collapsibles = document.querySelectorAll('.collapsible');
            M.Collapsible.init(collapsibles);
        });

        function filterMenu() {
            let input = document.getElementById('search-menu').value.toLowerCase();
            let menuItems = document.querySelectorAll('.menu-item');

            menuItems.forEach(function(item) {
                let text = item.textContent.toLowerCase();
                if (text.includes(input)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>

</body>

</html>